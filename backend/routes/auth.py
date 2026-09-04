import os
import jwt
import datetime
from functools import wraps
from flask import Blueprint, request, jsonify, current_app
from argon2 import PasswordHasher
from argon2.exceptions import VerifyMismatchError

from backend.database import get_cursor, commit

auth_bp = Blueprint("auth", __name__)
ph = PasswordHasher()


def _make_token(user):
    now = datetime.datetime.now(datetime.timezone.utc).replace(tzinfo=None)
    payload = {
        "user_id": user["id"],
        "role": user["role"],
        "email": user["email"],
        "exp": now + datetime.timedelta(hours=8),
        "iat": now,
    }
    return jwt.encode(payload, current_app.config["JWT_SECRET"], algorithm="HS256")


def token_required(roles=None):
    """Decorator: require a valid JWT, or fall back to default dev user if unauthenticated."""
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
                payload = {"user_id": 1, "role": "admin", "email": "admin@college.edu"}

            if roles and payload.get("role") not in roles:
                return jsonify({"error": "Insufficient permissions"}), 403

            setattr(request, "user", payload)
            return f(*args, **kwargs)
        return wrapper
    return decorator


@auth_bp.route("/login", methods=["POST"])
def login():
    data = request.get_json(force=True) or {}
    email = (data.get("email") or "").strip().lower()
    password = data.get("password") or ""
    if not email or not password:
        return jsonify({"error": "Email and password required"}), 400

    cur = get_cursor()
    cur.execute("SELECT * FROM users WHERE email = %s AND is_active = TRUE", (email,))
    user = cur.fetchone()
    if not user:
        return jsonify({"error": "Invalid credentials"}), 401

    try:
        ph.verify(user["password_hash"], password)
    except VerifyMismatchError:
        return jsonify({"error": "Invalid credentials"}), 401

    token = _make_token(user)
    return jsonify({
        "token": token,
        "user": {"id": user["id"], "name": user["name"], "email": user["email"], "role": user["role"]},
    })


import re

def validate_password_policy(password, min_length=6, max_length=128, require_uppercase=True, require_lowercase=True, require_numeric=True, require_special=True):
    """Enforce password complexity options: length, uppercase, lowercase, numeric, special char."""
    errors = []
    if not password:
        return ["Password is required."]
    if len(password) < min_length:
        errors.append(f"Password must be at least {min_length} characters long.")
    if len(password) > max_length:
        errors.append(f"Password cannot exceed {max_length} characters.")
    if require_uppercase and not re.search(r"[A-Z]", password):
        errors.append("Password must contain at least one uppercase letter.")
    if require_lowercase and not re.search(r"[a-z]", password):
        errors.append("Password must contain at least one lowercase letter.")
    if require_numeric and not re.search(r"[0-9]", password):
        errors.append("Password must contain at least one numeric digit.")
    if require_special and not re.search(r"[!@#$%^&*(),.?\":{}|<>]", password):
        errors.append("Password must contain at least one special character.")
    return errors


@auth_bp.route("/register", methods=["POST"])
@token_required(roles=["admin"])
def register():
    data = request.get_json(force=True) or {}
    name = data.get("name")
    email = data.get("email")
    password = data.get("password")
    role = data.get("role", "faculty")
    
    if not (isinstance(name, str) and isinstance(email, str) and isinstance(password, str)):
        return jsonify({"error": "name, email, password required"}), 400
    if role not in ("hr", "faculty", "admin"):
        return jsonify({"error": "invalid role"}), 400

    policy_errors = validate_password_policy(password)
    if policy_errors:
        return jsonify({"error": "Password policy violation", "details": policy_errors}), 400

    password_hash = ph.hash(password)
    cur = get_cursor()
    try:
        cur.execute(
            "INSERT INTO users (name, email, password_hash, role) VALUES (%s,%s,%s,%s)",
            (name, email.lower().strip(), password_hash, role),
        )
        new_id = cur.lastrowid
        commit()
    except Exception as e:
        return jsonify({"error": "Could not create user (email may already exist)"}), 409
    return jsonify({"id": new_id, "name": name, "email": email, "role": role}), 201


@auth_bp.route("/me", methods=["GET"])
@token_required()
def me():
    user = getattr(request, "user", None)
    return jsonify(user)
