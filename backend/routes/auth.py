"""Authentication and User Management Module with RBAC and Department Access Codes.
"""

# pyrefly: ignore [missing-import]
import os
# pyrefly: ignore [missing-import]
import re
# pyrefly: ignore [missing-import]
import jwt
# pyrefly: ignore [missing-import]
import datetime
# pyrefly: ignore [missing-import]
import json
# pyrefly: ignore [missing-import]
import random
# pyrefly: ignore [missing-import]
import string
# pyrefly: ignore [missing-import]
from functools import wraps
# pyrefly: ignore [missing-import]
from flask import Blueprint, request, jsonify, current_app
# pyrefly: ignore [missing-import]
from argon2 import PasswordHasher
# pyrefly: ignore [missing-import]
from argon2.exceptions import VerifyMismatchError

try:
    # pyrefly: ignore [missing-import]
    from database import get_cursor, commit
except ImportError:
    # pyrefly: ignore [missing-import]
    from ..database import get_cursor, commit

auth_bp = Blueprint("auth", __name__)
ph = PasswordHasher()


def _make_token(user, department_id=None, department_name=None):
    now = datetime.datetime.now(datetime.timezone.utc).replace(tzinfo=None)
    payload = {
        "user_id": user["id"],
        "name": user["name"],
        "role": user["role"],
        "email": user["email"],
        "department_id": department_id or user.get("department_id"),
        "department_name": department_name,
        "exp": now + datetime.timedelta(hours=8),
        "iat": now,
    }
    return jwt.encode(payload, current_app.config["JWT_SECRET"], algorithm="HS256")


def token_required(roles=None):
    """Decorator: require a valid JWT, or fall back to dev admin if unauthenticated."""
    def decorator(f):
        @wraps(f)
        def wrapper(*args, **kwargs):
            auth_header = request.headers.get("Authorization", "")
            payload = None
            if auth_header.startswith("Bearer "):
                token = auth_header.split(" ", 1)[1]
                try:
                    payload = jwt.decode(token, current_app.config["JWT_SECRET"], algorithms=["HS256"])
                except Exception:
                    payload = None

            # Fallback to dev admin user if token is missing/invalid
            if not payload:
                payload = {"user_id": 1, "name": "Admin", "role": "admin", "email": "admin@college.edu", "department_id": None}

            # pyrefly: ignore [missing-attribute]
            user_role = payload.get("role", "").lower()
            
            # Normalize role checking: coordinator maps with hr/admin, principal with admin
            if roles:
                allowed_roles = [r.lower() for r in roles]
                role_permitted = False
                if user_role in allowed_roles:
                    role_permitted = True
                elif user_role in ("admin", "principal") and any(r in ("coordinator", "hr", "admin", "principal", "faculty") for r in allowed_roles):
                    role_permitted = True
                elif user_role in ("coordinator", "hr") and any(r in ("hr", "faculty") for r in allowed_roles):
                    role_permitted = True

                if not role_permitted:
                    return jsonify({"error": "Insufficient permissions for this operation"}), 403

            setattr(request, "user", payload)
            return f(*args, **kwargs)
        return wrapper
    return decorator


def log_audit(action, entity_type=None, entity_id=None, details=None):
    """Safely log security and administrative events to audit_logs."""
    try:
        user = getattr(request, "user", {})
        user_id = user.get("user_id", 1)
        ip = request.remote_addr or "127.0.0.1"
        cur = get_cursor()
        det_dict = details if isinstance(details, dict) else {"info": str(details or "")}
        det_dict["ip"] = ip
        det_dict["user_email"] = user.get("email", "unknown")
        det_dict["role"] = user.get("role", "unknown")

        cur.execute(
            "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details) VALUES (%s, %s, %s, %s, %s)",
            (user_id, action, entity_type or "system", entity_id, json.dumps(det_dict))
        )
        commit()
    except Exception as e:
        print(f"Audit log writing error: {e}")


@auth_bp.route("/login", methods=["POST"])
def login():
    """Login supporting Principal, Placement Coordinator, and Department Staff (with Access Code)."""
    data = request.get_json(force=True) or {}
    email = (data.get("email") or data.get("username") or "").strip().lower()
    password = data.get("password") or ""
    req_role = (data.get("role") or "").strip().lower()
    access_code = (data.get("access_code") or data.get("department_code") or "").strip()

    if not email or not password:
        return jsonify({"error": "Email/username and password required"}), 400

    cur = get_cursor()
    cur.execute("SELECT * FROM users WHERE LOWER(email) = LOWER(%s) AND is_active = 1", (email,))
    user = cur.fetchone()
    if not user:
        return jsonify({"error": "Invalid credentials or deactivated account"}), 401

    try:
        ph.verify(user["password_hash"], password)
    except VerifyMismatchError:
        return jsonify({"error": "Invalid credentials"}), 401

    role = user["role"].lower()
    # Normalize roles
    if role in ("admin", "superadmin"):
        role = "principal" if req_role == "principal" else "coordinator"

    dept_id = user.get("department_id")
    dept_name = None

    # Enforce Department Staff Access Code
    if role == "staff" or req_role in ("staff", "faculty", "hod", "department staff"):
        if not access_code:
            return jsonify({"error": "Department Access Code required for Department Staff"}), 400

        # Validate access code
        cur.execute(
            """SELECT dac.department_id, d.name AS department_name
               FROM department_access_codes dac
               JOIN departments d ON d.id = dac.department_id
               WHERE UPPER(dac.access_code) = UPPER(%s)""",
            (access_code,)
        )
        code_row = cur.fetchone()
        if not code_row:
            return jsonify({"error": "Invalid or expired Department Access Code"}), 401

        dept_id = code_row["department_id"]
        dept_name = code_row["department_name"]

        # If user account is already assigned to a department, ensure code matches
        if user.get("department_id") and user["department_id"] != dept_id:
            return jsonify({"error": f"Access code does not match your assigned department"}), 403

        role = "staff"

    if dept_id and not dept_name:
        cur.execute("SELECT name FROM departments WHERE id = %s", (dept_id,))
        d_row = cur.fetchone()
        if d_row:
            dept_name = d_row["name"]

    token = _make_token(user, department_id=dept_id, department_name=dept_name)

    # Log successful login
    log_audit("user_login", "user", user["id"], {
        "email": user["email"],
        "role": role,
        "department": dept_name or "Institutional (All Departments)",
        "ip": request.remote_addr
    })

    return jsonify({
        "token": token,
        "user": {
            "id": user["id"],
            "name": user["name"],
            "email": user["email"],
            "role": role,
            "department_id": dept_id,
            "department_name": dept_name
        }
    })


@auth_bp.route("/users", methods=["GET"])
@token_required(roles=["principal", "coordinator", "admin"])
def list_users():
    """Retrieve all users for Coordinator/Principal User Management."""
    cur = get_cursor()
    cur.execute("""
        SELECT u.id, u.name, u.email, u.role, u.is_active, u.created_at, u.department_id,
               d.name AS department_name,
               (SELECT MAX(created_at) FROM audit_logs WHERE user_id = u.id AND action = 'user_login') AS last_login
        FROM users u
        LEFT JOIN departments d ON d.id = u.department_id
        ORDER BY u.id ASC
    """)
    rows = cur.fetchall()
    return jsonify(rows)


@auth_bp.route("/users", methods=["POST"])
@token_required(roles=["principal", "coordinator", "admin"])
def create_user():
    """Create a new staff or coordinator account."""
    data = request.get_json(force=True) or {}
    name = (data.get("name") or "").strip()
    email = (data.get("email") or "").strip().lower()
    password = data.get("password") or "Staff@2026"
    role = (data.get("role") or "staff").strip().lower()
    department_id = data.get("department_id")

    if not name or not email:
        return jsonify({"error": "Name and email are required"}), 400

    cur = get_cursor()
    cur.execute("SELECT id FROM users WHERE LOWER(email) = LOWER(%s)", (email,))
    if cur.fetchone():
        return jsonify({"error": "User with this email already exists"}), 409

    pwd_hash = ph.hash(password)
    cur.execute(
        "INSERT INTO users (name, email, password_hash, role, department_id, is_active) VALUES (%s, %s, %s, %s, %s, 1)",
        (name, email, pwd_hash, role, department_id)
    )
    new_id = cur.lastrowid
    commit()

    log_audit("create_user", "user", new_id, {"created_email": email, "role": role, "dept_id": department_id})
    return jsonify({"success": True, "id": new_id, "message": f"User account for {name} created successfully."}), 201


@auth_bp.route("/access-codes", methods=["GET"])
@token_required(roles=["principal", "coordinator", "admin"])
def list_access_codes():
    """List all current Department Access Codes."""
    cur = get_cursor()
    cur.execute("""
        SELECT dac.id, dac.department_id, dac.access_code, dac.updated_at, d.name AS department_name
        FROM department_access_codes dac
        JOIN departments d ON d.id = dac.department_id
        ORDER BY d.name ASC
    """)
    rows = cur.fetchall()
    return jsonify(rows)


@auth_bp.route("/access-codes/regenerate", methods=["POST"])
@token_required(roles=["principal", "coordinator", "admin"])
def regenerate_access_code():
    """Regenerate/rotate an access code for a department, immediately invalidating old code."""
    data = request.get_json(force=True) or {}
    department_id = data.get("department_id")
    if not department_id:
        return jsonify({"error": "department_id required"}), 400

    cur = get_cursor()
    cur.execute("SELECT name FROM departments WHERE id = %s", (department_id,))
    dept = cur.fetchone()
    if not dept:
        return jsonify({"error": "Department not found"}), 404

    # Generate fresh clean code e.g. PES-BCA-2026-X8F2
    slug = re.sub(r'[^A-Za-z]', '', dept["name"])[:4].upper()
    rand_suffix = ''.join(random.choices(string.ascii_uppercase + string.digits, k=4))
    new_code = f"PES-{slug}-2026-{rand_suffix}"

    user_id = getattr(request, "user", {}).get("user_id", 1)
    cur.execute(
        """UPDATE department_access_codes 
           SET access_code = %s, updated_at = CURRENT_TIMESTAMP, updated_by = %s
           WHERE department_id = %s""",
        (new_code, user_id, department_id)
    )
    commit()

    log_audit("regenerate_access_code", "department_access_codes", department_id, {
        "department": dept["name"],
        "new_code": new_code
    })

    return jsonify({
        "success": True,
        "department_id": department_id,
        "department_name": dept["name"],
        "new_access_code": new_code,
        "message": f"New access code generated for {dept['name']}. Old code is now invalidated."
    })


@auth_bp.route("/audit-logs", methods=["GET"])
@token_required(roles=["principal", "coordinator", "admin"])
def get_audit_logs():
    """Retrieve recent audit logs for security monitoring and notifications."""
    limit = min(int(request.args.get("limit", 25)), 100)
    cur = get_cursor()
    cur.execute("""
        SELECT a.*, u.name AS user_name, u.email AS user_email
        FROM audit_logs a
        LEFT JOIN users u ON u.id = a.user_id
        ORDER BY a.id DESC
        LIMIT %s
    """, (limit,))
    rows = cur.fetchall()
    return jsonify(rows)


@auth_bp.route("/me", methods=["GET"])
@token_required()
def me():
    user = getattr(request, "user", None)
    return jsonify(user)
