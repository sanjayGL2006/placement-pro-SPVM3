from flask import Blueprint, request, jsonify
from database import get_cursor, commit
from routes.auth import token_required
from email_service import send_placement_email

students_bp = Blueprint("students", __name__)


@students_bp.route("", methods=["GET"])
@token_required()
def list_students():
    dept = request.args.get("department")
    section = request.args.get("section")
    year = request.args.get("academic_year")
    status = request.args.get("placement_status")
    search = request.args.get("search")
    page = max(int(request.args.get("page", 1)), 1)
    per_page = min(int(request.args.get("per_page", 25)), 200)

    where, params = [], []
    if dept:
        where.append("d.name = %s"); params.append(dept)
    if section:
        sec_letter = section[-1] if section else "A"
        where.append("(s.section LIKE %s OR RIGHT(TRIM(s.section), 1) LIKE %s)")
        params.extend([section, sec_letter])
    if year:
        where.append("s.academic_year = %s"); params.append(year)
    if status:
        where.append("s.placement_status = %s"); params.append(status)
    if search:
        where.append("(s.name LIKE %s OR s.register_number LIKE %s)")
        params.extend([f"%{search}%", f"%{search}%"])

    where_clause = f"WHERE {' AND '.join(where)}" if where else ""
    cur = get_cursor()
    cur.execute(f"SELECT COUNT(*) AS total FROM students s LEFT JOIN departments d ON d.id = s.department_id {where_clause}", params)
    total = cur.fetchone()["total"]

    cur.execute(
        f"""SELECT s.*, d.name AS department_name, c.name AS course_name,
                   (SELECT comp.name FROM placements p JOIN companies comp ON comp.id = p.company_id WHERE p.student_id = s.id AND (p.current_stage IN ('selected','joined') OR p.offer_status IN ('offered','accepted')) ORDER BY p.id DESC LIMIT 1) AS company_name
            FROM students s
            LEFT JOIN departments d ON d.id = s.department_id
            LEFT JOIN courses c ON c.id = s.course_id
            {where_clause}
            ORDER BY s.name
            LIMIT %s OFFSET %s""",
        params + [per_page, (page - 1) * per_page],
    )
    rows = cur.fetchall()
    return jsonify({"total": total, "page": page, "per_page": per_page, "students": rows})


@students_bp.route("/<int:student_id>", methods=["GET"])
@token_required()
def get_student(student_id):
    cur = get_cursor()
    cur.execute(
        """SELECT s.*, d.name AS department_name, c.name AS course_name
           FROM students s
           LEFT JOIN departments d ON d.id = s.department_id
           LEFT JOIN courses c ON c.id = s.course_id
           WHERE s.id = %s""",
        (student_id,),
    )
    student = cur.fetchone()
    if not student:
        return jsonify({"error": "Not found"}), 404

    cur.execute(
        """SELECT p.*, comp.name AS company_name FROM placements p
           JOIN companies comp ON comp.id = p.company_id
           WHERE p.student_id = %s ORDER BY p.created_at DESC""",
        (student_id,),
    )
    placements = cur.fetchall()

    for p in placements:
        cur.execute("SELECT * FROM pipeline_stages WHERE placement_id = %s ORDER BY created_at", (p["id"],))
        p["timeline"] = cur.fetchall()

    student["placements"] = placements
    return jsonify(student)


@students_bp.route("/<int:student_id>", methods=["PUT"])
@token_required(roles=["hr", "faculty", "admin"])
def update_student(student_id):
    data = request.get_json(force=True) or {}
    allowed = {
        "name", "section", "academic_year", "gender", "mobile_number", "email", "address",
        "cgpa", "percentage", "backlogs", "skills", "resume_link", "placement_status", "eligible_status",
    }
    fields = {k: v for k, v in data.items() if k in allowed}
    if not fields:
        return jsonify({"error": "No valid fields to update"}), 400

    set_clause = ", ".join(f"{k} = %s" for k in fields)
    cur = get_cursor()
    cur.execute(
        f"UPDATE students SET {set_clause}, updated_at = NOW() WHERE id = %s",
        list(fields.values()) + [student_id],
    )
    if cur.rowcount == 0:
        return jsonify({"error": "Student not found"}), 404
    commit()
    return jsonify({"updated": True})


def _archive_student_to_recycle_bin(cur, student_id):
    import json
    cur.execute("SELECT * FROM students WHERE id = %s", (student_id,))
    s = cur.fetchone()
    if not s:
        return None
    cur.execute("SELECT * FROM placements WHERE student_id = %s", (student_id,))
    placements = cur.fetchall()
    placements_data = []
    for p in placements:
        cur.execute("SELECT * FROM pipeline_stages WHERE placement_id = %s", (p["id"],))
        stages = cur.fetchall()
        for stage in stages:
            if stage.get("stage_date"):
                stage["stage_date"] = str(stage["stage_date"])
        p_copy = dict(p)
        if p_copy.get("selection_date"):
            p_copy["selection_date"] = str(p_copy["selection_date"])
        if p_copy.get("offer_letter_date"):
            p_copy["offer_letter_date"] = str(p_copy["offer_letter_date"])
        if p_copy.get("joining_date"):
            p_copy["joining_date"] = str(p_copy["joining_date"])
        p_copy["stages"] = stages
        placements_data.append(p_copy)
    s_copy = dict(s)
    if s_copy.get("date_of_birth"):
        s_copy["date_of_birth"] = str(s_copy["date_of_birth"])
    payload = {
        "student_record": s_copy,
        "placements": placements_data
    }
    cur.execute(
        "INSERT INTO recycle_bin (entity_type, original_id, name, data) VALUES (%s, %s, %s, %s)",
        ("student", s["id"], s["name"], json.dumps(payload))
    )
    return s


@students_bp.route("/<int:student_id>", methods=["DELETE"])
@token_required(roles=["hr", "faculty", "admin"])
def delete_student(student_id):
    cur = get_cursor()
    student = _archive_student_to_recycle_bin(cur, student_id)
    if not student:
        return jsonify({"error": "Student not found"}), 404

    cur.execute("DELETE FROM students WHERE id = %s", (student_id,))
    
    user_id = getattr(request, "user", {}).get("user_id")
    import json
    cur.execute(
        "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details) "
        "VALUES (%s, %s, %s, %s, %s)",
        (user_id, "delete_student", "student", student_id, json.dumps({"student_name": student["name"], "register_number": student["register_number"]})),
    )
    commit()
    return jsonify({"deleted": True})



@students_bp.route("/<int:student_id>/pipeline", methods=["POST"])
@token_required(roles=["hr", "faculty", "admin"])
def add_pipeline_stage(student_id):
    """Manually add/update a pipeline stage for a student's active placement."""
    data = request.get_json(force=True) or {}
    company_id = data.get("company_id")
    stage = data.get("stage")
    status = data.get("status", "completed")
    remarks = data.get("remarks")
    if not company_id or not stage:
        return jsonify({"error": "company_id and stage required"}), 400

    cur = get_cursor()
    cur.execute(
        "SELECT id FROM placements WHERE student_id = %s AND company_id = %s",
        (student_id, company_id),
    )
    row = cur.fetchone()
    if not row:
        cur.execute(
            "INSERT INTO placements (student_id, company_id, current_stage) VALUES (%s,%s,%s)",
            (student_id, company_id, "registered"),
        )
        placement_id = cur.lastrowid
    else:
        placement_id = row["id"]

    cur.execute(
        "INSERT INTO pipeline_stages (placement_id, stage, status, stage_date, remarks) "
        "VALUES (%s,%s,%s, CURRENT_DATE, %s)",
        (placement_id, stage, status, remarks),
    )
    
    # Optional bidirectional sync for drive_status based on stage
    # 'applied', 'aptitude_test', 'technical_test', 'hr_interview', 'group_discussion' -> IN_PROCESSING
    # 'selected', 'joined_company', 'offer_letter_received' -> PLACED
    new_drive_status = "IN_PROCESSING"
    if stage in ('selected', 'joined_company', 'offer_letter_received'):
        new_drive_status = "PLACED"

    cur.execute(
        "UPDATE placements SET current_stage = %s, drive_status = %s, updated_at = NOW() WHERE id = %s",
        (stage, new_drive_status, placement_id),
    )
    
    if new_drive_status == "PLACED":
        # Get student and company details
        cur.execute("""
            SELECT s.register_number, s.name as student_name, d.name as dept_name, c.name as comp_name, c.package_amount 
            FROM placements p
            JOIN students s ON p.student_id = s.id
            JOIN departments d ON s.department_id = d.id
            JOIN companies c ON p.company_id = c.id
            WHERE p.id = %s
        """, (placement_id,))
        info = cur.fetchone()
        if info:
            send_placement_email(
                student_name=info["student_name"],
                register_number=info["register_number"],
                department=info["dept_name"],
                company_name=info["comp_name"],
                package=info["package_amount"]
            )
    
    commit()
    return jsonify({"added": True, "placement_id": placement_id})


@students_bp.route("/bulk-push", methods=["POST"])
@token_required(roles=["hr", "faculty", "admin"])
def bulk_push():
    """Bulk register students for a company placement drive with transaction safety and eligibility checks."""
    from routes.notifications import add_notification
    from database import rollback
    data = request.get_json(force=True) or {}
    raw_student_ids = data.get("student_ids")
    company_id = data.get("company_id")
    stage = data.get("stage", "applied")

    if not raw_student_ids or not isinstance(raw_student_ids, list):
        return jsonify({"error": "student_ids list required"}), 400
    if company_id is None:
        return jsonify({"error": "company_id required"}), 400

    try:
        company_id = int(company_id)
    except (TypeError, ValueError):
        return jsonify({"error": "company_id must be an integer"}), 400

    normalized_student_ids = []
    seen_ids = set()
    for raw_id in raw_student_ids:
        try:
            student_id = int(raw_id)
        except (TypeError, ValueError):
            continue
        if student_id not in seen_ids:
            seen_ids.add(student_id)
            normalized_student_ids.append(student_id)

    if not normalized_student_ids:
        return jsonify({"error": "student_ids list must contain valid student IDs"}), 400

    cur = get_cursor()
    cur.execute("SELECT * FROM companies WHERE id = %s", (company_id,))
    comp = cur.fetchone()
    if not comp:
        return jsonify({"error": "Company not found"}), 404
    company_name = comp["name"]

    pushed_count = 0
    skipped = []

    # Pre-parse company eligibility criteria
    min_cgpa = float(comp["min_cgpa"]) if comp["min_cgpa"] else 0.0
    allowed_backlogs = int(comp["allowed_backlogs"]) if comp["allowed_backlogs"] is not None else 99
    eligible_depts = [d.strip().lower() for d in (comp["eligible_departments"] or "").split(",") if d.strip()]

    try:
        for s_id in normalized_student_ids:
            cur.execute(
                """SELECT s.*, d.name AS department_name 
                   FROM students s 
                   LEFT JOIN departments d ON s.department_id = d.id 
                   WHERE s.id = %s""",
                (s_id,)
            )
            student = cur.fetchone()
            if not student:
                skipped.append({"student_id": s_id, "error": f"Student ID {s_id} not found"})
                continue

            cur.execute(
                "SELECT id FROM placements WHERE student_id = %s AND company_id = %s",
                (s_id, company_id),
            )
            if cur.fetchone():
                skipped.append({"student_id": s_id, "error": f"Student '{student['name']}' is already assigned to {company_name}."})
                continue

            student_cgpa = float(student["cgpa"]) if student["cgpa"] else 0.0
            if student_cgpa < min_cgpa:
                skipped.append({"student_id": s_id, "error": f"Student '{student['name']}' does not meet the minimum CGPA requirement of {min_cgpa}."})
                continue

            student_backlogs = int(student["backlogs"]) if student["backlogs"] is not None else 0
            if student_backlogs > allowed_backlogs:
                skipped.append({"student_id": s_id, "error": f"Student '{student['name']}' has {student_backlogs} backlogs, which exceeds the limit of {allowed_backlogs}."})
                continue

            if eligible_depts:
                student_dept = (student["department_name"] or "").strip().lower()
                if not any(d in student_dept for d in eligible_depts):
                    skipped.append({"student_id": s_id, "error": f"Student '{student['name']}' belongs to department '{student['department_name']}', which is not eligible for this drive."})
                    continue

            cur.execute(
                "INSERT INTO placements (student_id, company_id, current_stage, drive_status) "
                "VALUES (%s, %s, %s, 'INTERESTED')",
                (s_id, company_id, "registered")
            )
            placement_id = cur.lastrowid

            cur.execute(
                "INSERT INTO pipeline_stages (placement_id, stage, status, stage_date, remarks) "
                "VALUES (%s, %s, 'completed', CURRENT_DATE, %s)",
                (placement_id, stage, f"Pushed bulk registration to {company_name}"),
            )

            cur.execute(
                "UPDATE students SET placement_status = %s, updated_at = NOW() WHERE id = %s",
                ("applied", s_id),
            )

            user_id = getattr(request, "user", {}).get("user_id")
            import json
            cur.execute(
                "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details) "
                "VALUES (%s, %s, %s, %s, %s)",
                (user_id, "bulk_push_company", "placement", placement_id, json.dumps({"student_name": student["name"], "company_name": company_name})),
            )

            pushed_count += 1

        commit()

        if pushed_count > 0:
            add_notification(
                title="Students Registered",
                message=f"Placement Coordinator assigned {pushed_count} students to {company_name} Placement Drive.",
                n_type="success",
            )

        if pushed_count == 0:
            return jsonify({
                "success": False,
                "pushed_count": 0,
                "skipped_count": len(skipped),
                "skipped": skipped,
                "error": "No eligible students were registered for this company drive."
            }), 400

        return jsonify({
            "success": True,
            "pushed_count": pushed_count,
            "skipped_count": len(skipped),
            "skipped": skipped
        })

    except Exception as e:
        rollback()
        return jsonify({"error": f"Failed to complete bulk registration: {str(e)}"}), 500


@students_bp.route("/eligible-for/<int:company_id>", methods=["GET"])
@token_required()
def get_eligible_students(company_id):
    """Retrieve students eligible for a target company drive."""
    cur = get_cursor()
    # 1. Fetch Company Drive details
    cur.execute("SELECT * FROM companies WHERE id = %s", (company_id,))
    company = cur.fetchone()
    if not company:
        return jsonify({"error": "Company not found"}), 404

    # Extract drive eligibility criteria
    min_cgpa = float(company["min_cgpa"]) if company["min_cgpa"] else 0.0
    allowed_backlogs = int(company["allowed_backlogs"]) if company["allowed_backlogs"] is not None else 99
    eligible_depts = [d.strip().lower() for d in (company["eligible_departments"] or "").split(",") if d.strip()]

    # 2. Build filter conditions
    where = []
    params: list = []

    # Enforce basic eligibility criteria from the drive
    if min_cgpa > 0:
        where.append("s.cgpa >= %s")
        params.append(min_cgpa)
    
    where.append("s.backlogs <= %s")
    params.append(allowed_backlogs)

    if eligible_depts:
        dept_placeholders = ", ".join(["%s"] * len(eligible_depts))
        where.append(f"LOWER(d.name) IN ({dept_placeholders})")
        params.extend(eligible_depts)

    # UI Filters
    search = request.args.get("search")
    dept_filter = request.args.get("department")
    section_filter = request.args.get("section")
    skills_filter = request.args.get("skills")
    
    if search:
        where.append("(s.name LIKE %s OR s.register_number LIKE %s)")
        params.extend([f"%{search}%", f"%{search}%"])
    if dept_filter:
        where.append("d.name = %s")
        params.append(dept_filter)
    if section_filter:
        where.append("s.section = %s")
        params.append(section_filter)
    if skills_filter:
        where.append("s.skills LIKE %s")
        params.append(f"%{skills_filter}%")

    # Construct the WHERE clause
    where_clause = f"WHERE {' AND '.join(where)}" if where else ""

    # Sort parameter
    sort_by = request.args.get("sort_by", "name")
    sort_order = request.args.get("sort_order", "asc").upper()
    if sort_by not in ("name", "register_number"):
        sort_by = "name"
    if sort_order not in ("ASC", "DESC"):
        sort_order = "ASC"

    # Count query
    count_params = [company_id] + params
    count_query = f"""
        SELECT COUNT(*) AS total
        FROM students s
        LEFT JOIN departments d ON d.id = s.department_id
        LEFT JOIN placements p ON p.student_id = s.id AND p.company_id = %s
        {where_clause}
    """
    cur.execute(count_query, count_params)
    total = cur.fetchone()["total"]

    # Pagination
    page = max(int(request.args.get("page", 1)), 1)
    per_page = min(int(request.args.get("per_page", 25)), 200)

    # Select query
    select_params = [company_id] + params + [per_page, (page - 1) * per_page]
    select_query = f"""
        SELECT s.*, d.name AS department_name, c.name AS course_name,
               CASE WHEN p.id IS NOT NULL THEN TRUE ELSE FALSE END AS is_assigned,
               p.current_stage AS current_placement_stage,
               p.drive_status AS drive_status,
               comp.name AS current_company,
               p.id AS placement_id
        FROM students s
        LEFT JOIN departments d ON d.id = s.department_id
        LEFT JOIN courses c ON c.id = s.course_id
        LEFT JOIN placements p ON p.student_id = s.id AND p.company_id = %s
        LEFT JOIN placements p_active ON p_active.student_id = s.id AND p_active.current_stage IN ('selected','joined')
        LEFT JOIN companies comp ON comp.id = p_active.company_id
        {where_clause}
        ORDER BY s.{sort_by} {sort_order}
        LIMIT %s OFFSET %s
    """
    cur.execute(select_query, select_params)
    students = cur.fetchall()

    return jsonify({
        "total": total,
        "page": page,
        "per_page": per_page,
        "students": students,
        "company": {
            "name": company["name"],
            "job_role": company["job_role"],
            "package_amount": float(company["package_amount"]) if company["package_amount"] else 0,
            "min_cgpa": float(company["min_cgpa"]) if company["min_cgpa"] else 0.0,
            "allowed_backlogs": company["allowed_backlogs"],
            "eligible_departments": company["eligible_departments"]
        }
    })



@students_bp.route("/bulk-delete", methods=["POST"])
@token_required(roles=["hr", "faculty", "admin"])
def bulk_delete():
    """Bulk delete selected student records."""
    data = request.get_json(force=True) or {}
    student_ids = data.get("student_ids")
    if not student_ids or not isinstance(student_ids, list):
        return jsonify({"error": "student_ids list required"}), 400

    cur = get_cursor()
    deleted_count = 0
    errors = []
    
    for s_id in student_ids:
        try:
            student = _archive_student_to_recycle_bin(cur, s_id)
            if not student:
                continue
                
            cur.execute("DELETE FROM students WHERE id = %s", (s_id,))
            if cur.rowcount > 0:
                deleted_count += 1
                user_id = getattr(request, "user", {}).get("user_id")
                import json
                cur.execute(
                    "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details) "
                    "VALUES (%s, %s, %s, %s, %s)",
                    (user_id, "bulk_delete_student", "student", s_id, json.dumps({"student_name": student["name"], "register_number": student["register_number"]})),
                )
        except Exception as e:
            errors.append(f"Failed to delete {s_id}: {str(e)}")
            
    commit()
    
    from routes.notifications import add_notification
    if deleted_count > 0:
        add_notification(
            title="Student Roster Cleanup",
            message=f"Admin deleted {deleted_count} student records from the database.",
            n_type="warning",
        )

    return jsonify({
        "success": True,
        "deleted_count": deleted_count,
        "errors": errors
    })

