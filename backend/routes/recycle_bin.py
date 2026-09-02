import json
from flask import Blueprint, request, jsonify
from database import get_cursor, commit, rollback
from routes.auth import token_required

recycle_bin_bp = Blueprint("recycle_bin", __name__)


@recycle_bin_bp.route("", methods=["GET"])
@token_required()
def list_trash():
    """Retrieve all deleted items currently stored in the recycle bin."""
    cur = get_cursor()
    cur.execute(
        "SELECT id, entity_type, original_id, name, deleted_at FROM recycle_bin ORDER BY deleted_at DESC"
    )
    return jsonify(cur.fetchall())


@recycle_bin_bp.route("/reset", methods=["POST"])
@token_required(roles=["hr", "faculty", "admin"])
def reset_data():
    """Move data to recycle bin (soft reset) based on type: 'all' | 'students' | 'companies'."""
    data = request.get_json(force=True) or {}
    reset_type = data.get("type", "all")

    if reset_type not in ("all", "students", "companies"):
        return jsonify({"error": "Invalid reset type. Use 'all', 'students', or 'companies'"}), 400

    cur = get_cursor()
    students_moved = 0
    companies_moved = 0

    try:
        # Move Students
        if reset_type in ("all", "students"):
            cur.execute("""
                SELECT s.id, s.register_number, s.name, s.department_id, s.course_id, s.section, 
                       s.academic_year, s.gender, s.date_of_birth, s.mobile_number, s.email, 
                       s.address, s.cgpa, s.percentage, s.backlogs, s.skills, s.resume_link, 
                       s.placement_status, s.eligible_status
                FROM students s
            """)
            students = cur.fetchall()
            for s in students:
                # Fetch placements & pipeline stages for this student
                cur.execute("SELECT * FROM placements WHERE student_id = %s", (s["id"],))
                placements = cur.fetchall()
                
                placements_data = []
                for p in placements:
                    cur.execute("SELECT * FROM pipeline_stages WHERE placement_id = %s", (p["id"],))
                    stages = cur.fetchall()
                    # Convert dates in stages to string
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

                # Insert into recycle_bin
                cur.execute(
                    "INSERT INTO recycle_bin (entity_type, original_id, name, data) VALUES (%s, %s, %s, %s)",
                    ("student", s["id"], s["name"], json.dumps(payload))
                )
                students_moved += 1

            # Delete all students (cascades to placements & pipeline_stages)
            if students:
                cur.execute("DELETE FROM students")

        # Move Companies
        if reset_type in ("all", "companies"):
            cur.execute("""
                SELECT c.id, c.name, c.industry, c.state, c.location, c.hr_name, c.hr_email, 
                       c.hr_contact_number, c.visit_date, c.package_amount, c.min_package, 
                       c.max_package, c.avg_package, c.eligible_departments, c.min_cgpa, 
                       c.allowed_backlogs, c.hiring_count, c.logo_url
                FROM companies c
            """)
            companies = cur.fetchall()
            for c in companies:
                # Fetch placements & pipeline stages for this company
                cur.execute("SELECT * FROM placements WHERE company_id = %s", (c["id"],))
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

                c_copy = dict(c)
                if c_copy.get("visit_date"):
                    c_copy["visit_date"] = str(c_copy["visit_date"])

                payload = {
                    "company_record": c_copy,
                    "placements": placements_data
                }

                # Insert into recycle_bin
                cur.execute(
                    "INSERT INTO recycle_bin (entity_type, original_id, name, data) VALUES (%s, %s, %s, %s)",
                    ("company", c["id"], c["name"], json.dumps(payload))
                )
                companies_moved += 1

            # Delete all companies
            if companies:
                cur.execute("DELETE FROM companies")

        commit()
        return jsonify({
            "message": "Reset completed successfully",
            "students_moved": students_moved,
            "companies_moved": companies_moved
        }), 200

    except Exception as e:
        rollback()
        return jsonify({"error": f"Reset failed: {e}"}), 500


@recycle_bin_bp.route("/restore/<int:trash_id>", methods=["POST"])
@token_required(roles=["hr", "faculty", "admin"])
def restore_record(trash_id):
    """Restore a soft-deleted record back to its original table."""
    cur = get_cursor()
    cur.execute("SELECT * FROM recycle_bin WHERE id = %s", (trash_id,))
    trash = cur.fetchone()
    if not trash:
        return jsonify({"error": "Trash record not found"}), 404

    payload = json.loads(trash["data"])
    entity_type = trash["entity_type"]

    try:
        if entity_type == "student":
            s = payload["student_record"]
            # Insert student back (override auto-increment ID to maintain exact mappings)
            cur.execute("""
                INSERT INTO students (id, register_number, name, department_id, course_id, section, 
                                      academic_year, gender, date_of_birth, mobile_number, email, 
                                      address, cgpa, percentage, backlogs, skills, resume_link, 
                                      placement_status, eligible_status)
                VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)
                ON CONFLICT (id) DO UPDATE SET register_number=EXCLUDED.register_number
            """, (
                s["id"], s["register_number"], s["name"], s["department_id"], s["course_id"], s["section"],
                s["academic_year"], s["gender"], s["date_of_birth"], s["mobile_number"], s["email"],
                s["address"], s["cgpa"], s["percentage"], s["backlogs"], s["skills"], s["resume_link"],
                s["placement_status"], s["eligible_status"]
            ))

            # Restore placements and stages
            for p in payload.get("placements", []):
                cur.execute("""
                    INSERT INTO placements (id, student_id, company_id, package_amount, selection_date, 
                                            offer_status, offer_letter_date, joining_date, current_stage)
                    VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)
                    ON CONFLICT (id) DO NOTHING
                """, (
                    p["id"], p["student_id"], p["company_id"], p["package_amount"], p["selection_date"],
                    p["offer_status"], p["offer_letter_date"], p["joining_date"], p["current_stage"]
                ))
                for stage in p.get("stages", []):
                    cur.execute("""
                        INSERT INTO pipeline_stages (id, placement_id, stage, status, stage_date, remarks)
                        VALUES (%s,%s,%s,%s,%s,%s)
                        ON CONFLICT (id) DO NOTHING
                    """, (
                        stage["id"], stage["placement_id"], stage["stage"], stage["status"], stage["stage_date"], stage["remarks"]
                    ))

        elif entity_type == "company":
            c = payload["company_record"]
            cur.execute("""
                INSERT INTO companies (id, name, industry, state, location, hr_name, hr_email, 
                                       hr_contact_number, visit_date, package_amount, min_package, 
                                       max_package, avg_package, eligible_departments, min_cgpa, 
                                       allowed_backlogs, hiring_count, logo_url)
                VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)
                ON CONFLICT (id) DO UPDATE SET name=EXCLUDED.name
            """, (
                c["id"], c["name"], c["industry"], c["state"], c["location"], c["hr_name"], c["hr_email"],
                c["hr_contact_number"], c["visit_date"], c["package_amount"], c["min_package"],
                c["max_package"], c["avg_package"], c["eligible_departments"], c["min_cgpa"],
                c["allowed_backlogs"], c["hiring_count"], c["logo_url"]
            ))

            # Restore placements and stages
            for p in payload.get("placements", []):
                cur.execute("""
                    INSERT INTO placements (id, student_id, company_id, package_amount, selection_date, 
                                            offer_status, offer_letter_date, joining_date, current_stage)
                    VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)
                    ON CONFLICT (id) DO NOTHING
                """, (
                    p["id"], p["student_id"], p["company_id"], p["package_amount"], p["selection_date"],
                    p["offer_status"], p["offer_letter_date"], p["joining_date"], p["current_stage"]
                ))
                for stage in p.get("stages", []):
                    cur.execute("""
                        INSERT INTO pipeline_stages (id, placement_id, stage, status, stage_date, remarks)
                        VALUES (%s,%s,%s,%s,%s,%s)
                        ON CONFLICT (id) DO NOTHING
                    """, (
                        stage["id"], stage["placement_id"], stage["stage"], stage["status"], stage["stage_date"], stage["remarks"]
                    ))

        # Delete from recycle_bin
        cur.execute("DELETE FROM recycle_bin WHERE id = %s", (trash_id,))
        commit()
        return jsonify({"message": f"Successfully restored {entity_type} record"}), 200

    except Exception as e:
        rollback()
        return jsonify({"error": f"Restore failed: {e}"}), 500


@recycle_bin_bp.route("/<int:trash_id>", methods=["DELETE"])
@token_required(roles=["hr", "faculty", "admin"])
def delete_trash_permanently(trash_id):
    """Permanently delete an item from the recycle bin."""
    cur = get_cursor()
    cur.execute("DELETE FROM recycle_bin WHERE id = %s", (trash_id,))
    commit()
    return jsonify({"message": "Record permanently deleted from trash"}), 200


@recycle_bin_bp.route("/empty", methods=["DELETE"])
@token_required(roles=["hr", "faculty", "admin"])
def empty_trash():
    """Permanently clear all items from the recycle bin."""
    cur = get_cursor()
    cur.execute("DELETE FROM recycle_bin")
    commit()
    return jsonify({"message": "Recycle bin emptied successfully"}), 200


@recycle_bin_bp.route("/hard-reset", methods=["POST"])
@token_required(roles=["hr", "faculty", "admin"])
def hard_reset():
    """Permanently delete all students, companies, placements, pipeline stages, and empty recycle bin."""
    cur = get_cursor()
    try:
        cur.execute("DELETE FROM pipeline_stages")
        cur.execute("DELETE FROM placements")
        cur.execute("DELETE FROM student_documents")
        cur.execute("DELETE FROM students")
        cur.execute("DELETE FROM companies")
        cur.execute("DELETE FROM recycle_bin")
        cur.execute("DELETE FROM import_history")
        commit()
        return jsonify({"message": "Hard reset completed. All data and recycle bin emptied successfully"}), 200
    except Exception as e:
        rollback()
        return jsonify({"error": f"Hard reset failed: {e}"}), 500

