# pyrefly: ignore [missing-import]
import json
# pyrefly: ignore [missing-import]
from flask import Blueprint, request, jsonify

# pyrefly: ignore [missing-import]
from database import get_cursor, commit, rollback
# pyrefly: ignore [missing-import]
from routes.auth import token_required
# pyrefly: ignore [missing-import]
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
        name = str(name).strip()
        if name in dept_cache:
            return dept_cache[name]
        cur.execute("SELECT id FROM departments WHERE LOWER(name) = LOWER(%s)", (name,))
        row = cur.fetchone()
        if row:
            dept_cache[name] = row["id"]
        else:
            cur.execute("INSERT INTO departments (name) VALUES (%s)", (name,))
            cur.execute("SELECT id FROM departments WHERE LOWER(name) = LOWER(%s)", (name,))
            r_new = cur.fetchone()
            dept_cache[name] = r_new["id"] if r_new else cur.lastrowid
        return dept_cache[name]

    def normalize_row_data(d):
        if not isinstance(d, dict):
            return {}
        lookup = {str(k).strip().lower().replace(" ", "_").replace(".", "").replace("-", "_"): v for k, v in d.items() if v is not None}
        def pick(*keys, default=None):
            for k in keys:
                nk = k.lower().replace(" ", "_").replace(".", "").replace("-", "_")
                if nk in lookup and str(lookup[nk]).strip() != "":
                    return lookup[nk]
            return default

        reg_no = pick("register_number", "register_no", "reg_number", "reg_no", "regno", "usn", "roll_no", "roll_number", "id")
        name = pick("name", "student_name", "full_name", "candidate_name", default="Candidate")
        dept = pick("department", "department_name", "dept", "dept_name", "course", "branch", default="BCA")
        section = pick("section", "sec", default="Section A")
        academic_year = pick("academic_year", "batch", "batch_year", "year", default="2023-2026")
        gender = pick("gender", "sex", default="Other")
        dob = pick("date_of_birth", "dob", default=None)
        phone = pick("mobile_number", "mobile", "phone", "contact", default="")
        email = pick("email", "email_address", "mail", default="")
        address = pick("address", default="")
        cgpa = pick("cgpa", "gpa", default=None)
        percentage = pick("percentage", "percent", "marks", default=None)
        backlogs = pick("backlogs", "active_backlogs", "arrears", default=0)
        skills = pick("skills", "skill_set", "technical_skills", default="")
        resume = pick("resume_link", "resume", "cv", default="")
        status = pick("placement_status", "status", default="not_placed")
        eligible = pick("eligible_status", "eligible", default=True)
        company = pick("company", "company_name", "placed_company", default=None)
        package = pick("package", "package_amount", "package_lpa", "ctc", default=None)

        try:
            cgpa_val = float(cgpa) if cgpa is not None and str(cgpa).strip() != "" else None
        except (ValueError, TypeError):
            cgpa_val = None

        try:
            backlogs_val = int(backlogs) if backlogs is not None and str(backlogs).strip() != "" else 0
        except (ValueError, TypeError):
            backlogs_val = 0

        status_norm = str(status).strip().lower() if status else "not_placed"
        if status_norm in ("placed", "selected", "offer"):
            status_norm = "selected"
        elif status_norm in ("applied", "in-process", "in_process"):
            status_norm = "applied"
        else:
            status_norm = "not_placed"

        return {
            "register_number": str(reg_no).strip() if reg_no else None,
            "name": str(name).strip() if name else "Candidate",
            "department": str(dept).strip(),
            "section": str(section).strip(),
            "academic_year": str(academic_year).strip(),
            "gender": str(gender).strip(),
            "date_of_birth": dob,
            "mobile_number": str(phone).strip(),
            "email": str(email).strip(),
            "address": str(address).strip(),
            "cgpa": cgpa_val,
            "percentage": percentage,
            "backlogs": backlogs_val,
            "skills": str(skills).strip() if skills else "",
            "resume_link": str(resume).strip() if resume else "",
            "placement_status": status_norm,
            "eligible_status": True if str(eligible).strip().lower() in ("true", "1", "yes", "y", "eligible") else False,
            "company": str(company).strip() if company else None,
            "package": package
        }

    for r in rows:
        raw_data = r.get("data", {})
        norm = normalize_row_data(raw_data)
        action = r.get("action")
        if not action or action not in ("insert", "update", "skip"):
            action = "insert"

        if action == "skip" or not norm.get("register_number"):
            if not norm.get("register_number"):
                error_log.append({"row": r, "error": "Missing register/roll number"})
                errors += 1
            else:
                skipped += 1
            continue

        try:
            reg_no = norm["register_number"]
            d_id = dept_id(norm["department"])
            fields = {
                "register_number": reg_no,
                "name": norm["name"],
                "department_id": d_id,
                "section": norm["section"],
                "academic_year": norm["academic_year"],
                "gender": norm["gender"],
                "date_of_birth": norm["date_of_birth"],
                "mobile_number": norm["mobile_number"],
                "email": norm["email"],
                "address": norm["address"],
                "cgpa": norm["cgpa"],
                "percentage": norm["percentage"],
                "backlogs": norm["backlogs"],
                "skills": norm["skills"],
                "resume_link": norm["resume_link"],
                "placement_status": norm["placement_status"],
                "eligible_status": norm["eligible_status"],
            }

            cur.execute("SELECT id FROM students WHERE register_number = %s", (reg_no,))
            existing_student = cur.fetchone()

            if action == "insert" and not existing_student:
                cols = ", ".join(fields.keys())
                placeholders = ", ".join(["%s"] * len(fields))
                cur.execute(
                    f"INSERT INTO students ({cols}) VALUES ({placeholders})",
                    list(fields.values()),
                )
                inserted += 1
            elif action == "update" or existing_student:
                set_clause = ", ".join(f"{k} = %s" for k in fields if k != "register_number")
                values = [v for k, v in fields.items() if k != "register_number"]
                values.append(reg_no)
                cur.execute(
                    f"UPDATE students SET {set_clause}, updated_at = NOW() WHERE register_number = %s",
                    values,
                )
                updated += 1

            # Auto-link company placement if present
            company_name = norm.get("company")
            if company_name and norm["placement_status"] in ("selected", "joined"):
                cur.execute("SELECT id FROM companies WHERE LOWER(name) = LOWER(%s)", (company_name.strip(),))
                crow = cur.fetchone()
                if crow:
                    company_id = crow["id"]
                else:
                    cur.execute("INSERT INTO companies (name, industry) VALUES (%s, 'Others')", (company_name.strip(),))
                    cur.execute("SELECT id FROM companies WHERE LOWER(name) = LOWER(%s)", (company_name.strip(),))
                    c_created = cur.fetchone()
                    company_id = c_created["id"] if c_created else cur.lastrowid

                cur.execute("SELECT id FROM students WHERE register_number = %s", (reg_no,))
                srow = cur.fetchone()
                if srow and company_id:
                    student_id = srow["id"]
                    cur.execute("SELECT id FROM placements WHERE student_id = %s AND company_id = %s", (student_id, company_id))
                    p_existing = cur.fetchone()
                    if not p_existing:
                        cur.execute(
                            "INSERT INTO placements (student_id, company_id, offer_status, current_stage) "
                            "VALUES (%s, %s, 'accepted', 'selected')",
                            (student_id, company_id)
                        )
        except Exception as e:
            errors += 1
            error_log.append({"register_number": norm.get("register_number"), "error": str(e)})

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
    # pyrefly: ignore [missing-import]
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
