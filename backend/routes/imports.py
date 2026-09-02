import json
from flask import Blueprint, request, jsonify

from database import get_cursor, commit, rollback
from routes.auth import token_required
from import_utils import read_tabular_file, build_preview

imports_bp = Blueprint("imports", __name__)

ALLOWED_EXT = {"xlsx", "xls", "csv", "docx", "pdf"}


def _validate_upload():
    if "file" not in request.files:
        return None, (jsonify({"error": "No file uploaded"}), 400)
    f = request.files["file"]
    filename = f.filename or ""
    if filename == "":
        return None, (jsonify({"error": "Empty filename"}), 400)
    ext = filename.rsplit(".", 1)[-1].lower() if "." in filename else ""
    if ext not in ALLOWED_EXT:
        return None, (jsonify({"error": f"Unsupported file type .{ext}. Allowed: {sorted(ALLOWED_EXT)}"}), 400)
    return f, None


@imports_bp.route("/students/preview", methods=["POST"])
@token_required(roles=["hr", "faculty", "admin"])
def preview_students():
    f, err = _validate_upload()
    if err or f is None:
        return err or (jsonify({"error": "No file uploaded"}), 400)
    filename = f.filename or "uploaded_file"
    try:
        df = read_tabular_file(f, filename)
    except Exception as e:
        return jsonify({"error": f"Could not read file: {e}"}), 400

    preview = build_preview(df, "student")

    # Mark DB-level duplicates (existing register numbers)
    reg_numbers = [r["data"].get("register_number") for r in preview["rows"] if r["data"].get("register_number")]
    existing = set()
    if reg_numbers:
        cur = get_cursor()
        placeholders = ", ".join(["%s"] * len(reg_numbers))
        cur.execute(f"SELECT register_number FROM students WHERE register_number IN ({placeholders})", tuple(reg_numbers))
        existing = {row["register_number"] for row in cur.fetchall()}

    for r in preview["rows"]:
        reg = r["data"].get("register_number")
        r["exists_in_db"] = reg in existing if reg else False
        r["action"] = "skip" if r["errors"] else ("update" if r["exists_in_db"] else "insert")

    preview["summary"] = {
        "to_insert": sum(1 for r in preview["rows"] if r["action"] == "insert"),
        "to_update": sum(1 for r in preview["rows"] if r["action"] == "update"),
        "to_skip": sum(1 for r in preview["rows"] if r["action"] == "skip"),
    }
    return jsonify(preview)


@imports_bp.route("/students/commit", methods=["POST"])
@token_required(roles=["hr", "faculty", "admin"])
def commit_students():
    """
    Body: { "rows": [ {data: {...}, action: "insert"|"update"|"skip"}, ... ], "file_name": "..." }
    The client sends back the (possibly corrected) preview rows for final commit.
    """
    body = request.get_json(force=True) or {}
    rows = body.get("rows", [])
    file_name = body.get("file_name", "unknown")

    inserted = updated = skipped = errors = 0
    error_log = []
    cur = get_cursor()

    dept_cache = {}

    def dept_id(name):
        if not name:
            return None
        name = name.strip()
        if name in dept_cache:
            return dept_cache[name]
        cur.execute("SELECT id FROM departments WHERE LOWER(name) = LOWER(%s)", (name,))
        row = cur.fetchone()
        if row:
            dept_cache[name] = row["id"]
        else:
            # Dynamically create the department
            cur.execute("INSERT INTO departments (name) VALUES (%s) RETURNING id", (name,))
            dept_cache[name] = cur.fetchone()["id"]
        return dept_cache[name]

    for r in rows:
        data = r.get("data", {})
        action = r.get("action", "skip")
        if action == "skip" or not data.get("register_number"):
            skipped += 1
            continue
        try:
            fields = {
                "register_number": data.get("register_number"),
                "name": data.get("name"),
                "department_id": dept_id(data.get("department")),
                "section": data.get("section"),
                "academic_year": data.get("academic_year"),
                "gender": data.get("gender"),
                "date_of_birth": data.get("date_of_birth"),
                "mobile_number": data.get("mobile_number"),
                "email": data.get("email"),
                "address": data.get("address"),
                "cgpa": data.get("cgpa"),
                "percentage": data.get("percentage"),
                "backlogs": data.get("backlogs") or 0,
                "skills": data.get("skills"),
                "resume_link": data.get("resume_link"),
                "placement_status": data.get("placement_status") or "not_placed",
                "eligible_status": True if data.get("eligible_status") in (None, "", "yes", "Yes", True) else False,
            }
            if action == "insert":
                cols = ", ".join(fields.keys())
                placeholders = ", ".join(["%s"] * len(fields))
                cur.execute(
                    f"INSERT INTO students ({cols}) VALUES ({placeholders}) "
                    f"ON CONFLICT (register_number) DO NOTHING",
                    list(fields.values()),
                )
                inserted += 1
            elif action == "update":
                set_clause = ", ".join(f"{k} = %s" for k in fields if k != "register_number")
                values = [v for k, v in fields.items() if k != "register_number"]
                values.append(fields["register_number"])
                cur.execute(
                    f"UPDATE students SET {set_clause}, updated_at = NOW() WHERE register_number = %s",
                    values,
                )
                updated += 1

            # Auto-link company placement if present
            company_name = data.get("company")
            if company_name and fields["placement_status"] in ("selected", "joined"):
                # Get or create company
                cur.execute("SELECT id FROM companies WHERE LOWER(name) = LOWER(%s)", (company_name.strip(),))
                crow = cur.fetchone()
                if crow:
                    company_id = crow["id"]
                else:
                    cur.execute("INSERT INTO companies (name, industry) VALUES (%s, 'Others') RETURNING id", (company_name.strip(),))
                    company_id = cur.fetchone()["id"]
                
                # Get student ID
                cur.execute("SELECT id FROM students WHERE register_number = %s", (fields["register_number"],))
                srow = cur.fetchone()
                if srow:
                    student_id = srow["id"]
                    cur.execute(
                        "INSERT INTO placements (student_id, company_id, offer_status, current_stage) "
                        "VALUES (%s, %s, 'accepted', 'selected') "
                        "ON CONFLICT (student_id, company_id) DO NOTHING",
                        (student_id, company_id)
                    )
        except Exception as e:
            errors += 1
            error_log.append({"register_number": data.get("register_number"), "error": str(e)})

    cur.execute(
        "INSERT INTO import_history (imported_by, import_type, file_name, total_rows, "
        "inserted_count, updated_count, skipped_count, error_count, error_log) "
        "VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)",
        (getattr(request, "user", {})["user_id"], "student", file_name, len(rows), inserted, updated, skipped, errors, json.dumps(error_log)),
    )
    commit()

    return jsonify({
        "inserted": inserted, "updated": updated, "skipped": skipped, "errors": errors, "error_log": error_log,
    })


@imports_bp.route("/companies/preview", methods=["POST"])
@token_required(roles=["hr", "faculty", "admin"])
def preview_companies():
    f, err = _validate_upload()
    if err or f is None:
        return err or (jsonify({"error": "No file uploaded"}), 400)
    filename = f.filename or "uploaded_file"
    try:
        df = read_tabular_file(f, filename)
    except Exception as e:
        return jsonify({"error": f"Could not read file: {e}"}), 400

    preview = build_preview(df, "company")

    names = [r["data"].get("name") for r in preview["rows"] if r["data"].get("name")]
    existing = set()
    if names:
        cur = get_cursor()
        placeholders = ", ".join(["%s"] * len(names))
        cur.execute(f"SELECT name FROM companies WHERE name IN ({placeholders})", tuple(names))
        existing = {row["name"] for row in cur.fetchall()}

    for r in preview["rows"]:
        name = r["data"].get("name")
        r["exists_in_db"] = name in existing if name else False
        r["action"] = "skip" if r["errors"] else ("update" if r["exists_in_db"] else "insert")

    preview["summary"] = {
        "to_insert": sum(1 for r in preview["rows"] if r["action"] == "insert"),
        "to_update": sum(1 for r in preview["rows"] if r["action"] == "update"),
        "to_skip": sum(1 for r in preview["rows"] if r["action"] == "skip"),
    }
    return jsonify(preview)


@imports_bp.route("/companies/commit", methods=["POST"])
@token_required(roles=["hr", "faculty", "admin"])
def commit_companies():
    """
    Same shape as commit_students, but also auto-maps any
    'students_selected' register numbers to placements + pipeline stages.
    """
    body = request.get_json(force=True) or {}
    rows = body.get("rows", [])
    file_name = body.get("file_name", "unknown")

    inserted = updated = skipped = errors = 0
    error_log = []
    students_mapped = 0
    cur = get_cursor()

    for r in rows:
        data = r.get("data", {})
        action = r.get("action", "skip")
        if action == "skip" or not data.get("name"):
            skipped += 1
            continue
        try:
            fields = {
                "name": data.get("name"),
                "industry": data.get("industry"),
                "state": data.get("state"),
                "location": data.get("location"),
                "hr_name": data.get("hr_name"),
                "hr_email": data.get("hr_email"),
                "hr_contact_number": data.get("hr_contact_number"),
                "visit_date": data.get("visit_date"),
                "package_amount": data.get("package_amount"),
                "min_package": data.get("min_package"),
                "max_package": data.get("max_package"),
                "avg_package": data.get("avg_package"),
                "eligible_departments": data.get("eligible_departments"),
                "min_cgpa": data.get("min_cgpa"),
                "allowed_backlogs": data.get("allowed_backlogs") or 0,
                "hiring_count": data.get("hiring_count") or 0,
            }
            if action == "insert":
                cols = ", ".join(fields.keys())
                placeholders = ", ".join(["%s"] * len(fields))
                cur.execute(
                    f"INSERT INTO companies ({cols}) VALUES ({placeholders}) RETURNING id",
                    list(fields.values()),
                )
                company_id = cur.fetchone()["id"]
                inserted += 1
            else:
                set_clause = ", ".join(f"{k} = %s" for k in fields if k != "name")
                values = [v for k, v in fields.items() if k != "name"]
                cur.execute(
                    f"UPDATE companies SET {set_clause}, updated_at = NOW() WHERE name = %s RETURNING id",
                    values + [fields["name"]],
                )
                row = cur.fetchone()
                company_id = row["id"] if row else None
                updated += 1

            # --- Auto-map selected students by register number ---
            selected_raw = data.get("students_selected")
            if company_id and selected_raw:
                reg_numbers = [x.strip() for x in re_split(selected_raw) if x.strip()]
                package = fields.get("package_amount")
                for reg in reg_numbers:
                    cur.execute("SELECT id FROM students WHERE register_number = %s", (reg,))
                    srow = cur.fetchone()
                    if not srow:
                        error_log.append({"company": fields["name"], "register_number": reg, "error": "Student not found"})
                        continue
                    student_id = srow["id"]
                    cur.execute(
                        "INSERT INTO placements (student_id, company_id, package_amount, selection_date, "
                        "offer_status, current_stage) VALUES (%s,%s,%s, CURRENT_DATE, 'offered', 'selected') "
                        "ON CONFLICT (student_id, company_id) DO UPDATE SET "
                        "package_amount = EXCLUDED.package_amount, offer_status = 'offered', "
                        "current_stage = 'selected', updated_at = NOW() RETURNING id",
                        (student_id, company_id, package),
                    )
                    placement_id = cur.fetchone()["id"]
                    cur.execute(
                        "INSERT INTO pipeline_stages (placement_id, stage, status, stage_date) "
                        "VALUES (%s, 'selected', 'completed', CURRENT_DATE)",
                        (placement_id,),
                    )
                    cur.execute(
                        "UPDATE students SET placement_status = 'selected', updated_at = NOW() WHERE id = %s",
                        (student_id,),
                    )
                    students_mapped += 1
        except Exception as e:
            errors += 1
            error_log.append({"company": data.get("name"), "error": str(e)})

    cur.execute(
        "INSERT INTO import_history (imported_by, import_type, file_name, total_rows, "
        "inserted_count, updated_count, skipped_count, error_count, error_log) "
        "VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)",
        (getattr(request, "user", {})["user_id"], "company", file_name, len(rows), inserted, updated, skipped, errors, json.dumps(error_log)),
    )
    commit()

    return jsonify({
        "inserted": inserted, "updated": updated, "skipped": skipped, "errors": errors,
        "students_mapped": students_mapped, "error_log": error_log,
    })


def re_split(raw):
    """Split a 'students_selected' cell on common delimiters (comma, semicolon, newline)."""
    import re
    return re.split(r"[,;\n]+", str(raw))


@imports_bp.route("/history", methods=["GET"])
@token_required(roles=["hr", "faculty", "admin"])
def import_history():
    cur = get_cursor()
    cur.execute(
        "SELECT ih.*, u.name AS imported_by_name FROM import_history ih "
        "LEFT JOIN users u ON u.id = ih.imported_by ORDER BY ih.created_at DESC LIMIT 50"
    )
    return jsonify(cur.fetchall())
