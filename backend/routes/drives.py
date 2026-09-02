from flask import Blueprint, request, jsonify
from database import get_cursor, commit
from routes.auth import token_required
from email_service import send_placement_email

drives_bp = Blueprint("drives", __name__)


@drives_bp.route("/placements/<int:placement_id>/status", methods=["PUT"])
@token_required(roles=["hr", "faculty", "admin"])
def update_drive_status(placement_id):
    """Update the high-level drive status for a candidate (e.g. INTERESTED, IN_PROCESSING, PLACED, UNPLACED)."""
    data = request.get_json(force=True) or {}
    status = data.get("status")
    
    if not status or status not in ("INTERESTED", "IN_PROCESSING", "PLACED", "UNPLACED"):
        return jsonify({"error": "Invalid status"}), 400

    cur = get_cursor()
    cur.execute("SELECT * FROM placements WHERE id = %s", (placement_id,))
    placement = cur.fetchone()
    if not placement:
        return jsonify({"error": "Placement not found"}), 404

    cur.execute(
        "UPDATE placements SET drive_status = %s, updated_at = NOW() WHERE id = %s",
        (status, placement_id)
    )
    
    # Bi-directional sync: If drive_status is PLACED, update current_stage and student's placement_status
    if status == "PLACED":
        cur.execute(
            "UPDATE placements SET current_stage = 'selected', updated_at = NOW() WHERE id = %s",
            (placement_id,)
        )
        cur.execute(
            "UPDATE students SET placement_status = 'selected', updated_at = NOW() WHERE id = %s",
            (placement["student_id"],)
        )
        cur.execute(
            "SELECT register_number, name, department_id FROM students WHERE id = %s",
            (placement["student_id"],)
        )
        student_data = cur.fetchone()
        
        cur.execute("SELECT name FROM departments WHERE id = %s", (student_data["department_id"],))
        dept = cur.fetchone()["name"]
        
        cur.execute("SELECT name, package_amount FROM companies WHERE id = %s", (placement["company_id"],))
        comp = cur.fetchone()
        
        send_placement_email(
            student_name=student_data["name"], 
            register_number=student_data["register_number"], 
            department=dept, 
            company_name=comp["name"], 
            package=comp["package_amount"]
        )
    
    commit()
    return jsonify({"success": True, "status": status})


@drives_bp.route("/repeat-alerts", methods=["GET"])
@token_required(roles=["faculty", "admin"])
def get_repeat_alerts():
    """Fetch students who have been in 2+ drives (IN_PROCESSING/INTERESTED) but never PLACED."""
    cur = get_cursor()
    
    # Students with >= 2 non-PLACED placements, and 0 PLACED placements overall.
    query = """
        SELECT s.id as student_id, s.register_number, s.name, d.name as department, 
               COUNT(p.id) as shortlist_count
        FROM students s
        JOIN placements p ON p.student_id = s.id
        LEFT JOIN departments d ON s.department_id = d.id
        WHERE p.drive_status != 'PLACED'
          AND NOT EXISTS (
              SELECT 1 FROM placements p2 
              WHERE p2.student_id = s.id AND p2.drive_status = 'PLACED'
          )
        GROUP BY s.id, s.register_number, s.name, d.name
        HAVING COUNT(p.id) >= 2
        ORDER BY shortlist_count DESC
    """
    cur.execute(query)
    alerts = cur.fetchall()
    
    # Fetch drive names for each student
    for alert in alerts:
        cur.execute("""
            SELECT c.name as company_name 
            FROM placements p 
            JOIN companies c ON p.company_id = c.id
            WHERE p.student_id = %s AND p.drive_status != 'PLACED'
        """, (alert["student_id"],))
        drives = [r["company_name"] for r in cur.fetchall()]
        alert["drives"] = drives

    return jsonify({"alerts": alerts})
