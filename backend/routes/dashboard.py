from flask import Blueprint, jsonify, request
from database import get_cursor
from routes.auth import token_required

dashboard_bp = Blueprint("dashboard", __name__)


@dashboard_bp.route("/stats", methods=["GET"])
@token_required()
def stats():
    cur = get_cursor()

    cur.execute("SELECT COUNT(*) AS n FROM students")
    total_students = cur.fetchone()["n"]

    cur.execute("SELECT COUNT(*) AS n FROM companies")
    total_companies = cur.fetchone()["n"]

    cur.execute("SELECT COUNT(*) AS n FROM students WHERE eligible_status = TRUE")
    eligible_students = cur.fetchone()["n"]

    cur.execute("SELECT COUNT(DISTINCT student_id) AS n FROM placements")
    applied_students = cur.fetchone()["n"]

    cur.execute(
        "SELECT COUNT(DISTINCT student_id) AS n FROM placements WHERE current_stage NOT IN ('registered')"
    )
    students_attended = cur.fetchone()["n"]

    cur.execute(
        "SELECT COUNT(*) AS n FROM students WHERE placement_status IN ('selected','joined')"
    )
    students_selected = cur.fetchone()["n"]

    cur.execute(
        "SELECT COUNT(*) AS n FROM placements WHERE offer_status = 'offered' OR offer_letter_date IS NOT NULL"
    )
    total_offer_letters = cur.fetchone()["n"]

    cur.execute("SELECT COUNT(*) AS n FROM students WHERE placement_status = 'joined'")
    students_joined = cur.fetchone()["n"]

    cur.execute(
        "SELECT MAX(package_amount) AS hi, MIN(package_amount) AS lo, AVG(package_amount) AS avg "
        "FROM placements WHERE package_amount IS NOT NULL"
    )
    pkg = cur.fetchone()

    placement_pct = round((students_selected / total_students) * 100, 2) if total_students else 0.0

    return jsonify({
        "total_students": total_students,
        "total_companies": total_companies,
        "eligible_students": eligible_students,
        "applied_students": applied_students,
        "students_attended": students_attended,
        "students_selected": students_selected,
        "placement_percentage": placement_pct,
        "highest_package": float(pkg["hi"]) if pkg["hi"] else 0,
        "average_package": round(float(pkg["avg"]), 2) if pkg["avg"] else 0,
        "minimum_package": float(pkg["lo"]) if pkg["lo"] else 0,
        "total_offer_letters": total_offer_letters,
        "students_joined": students_joined,
    })


@dashboard_bp.route("/filters", methods=["GET"])
@token_required()
def filter_options():
    cur = get_cursor()
    cur.execute("SELECT name FROM departments ORDER BY name")
    departments = [r["name"] for r in cur.fetchall()]
    cur.execute("SELECT DISTINCT section FROM students WHERE section IS NOT NULL ORDER BY section")
    sections = [r["section"] for r in cur.fetchall()]
    cur.execute("SELECT DISTINCT academic_year FROM students WHERE academic_year IS NOT NULL ORDER BY academic_year")
    years = [r["academic_year"] for r in cur.fetchall()]
    return jsonify({"departments": departments, "sections": sections, "academic_years": years})


@dashboard_bp.route("/sections", methods=["GET"])
@token_required()
def section_stats():
    section_param = request.args.get("section", "Section A").strip()
    section_letter = section_param[-1] if section_param else "A"
    
    cur = get_cursor()
    
    # 1. Total students
    cur.execute(
        "SELECT COUNT(*) AS n FROM students WHERE (section ILIKE %s OR RIGHT(TRIM(section), 1) ILIKE %s)",
        (section_param, section_letter)
    )
    total_students = cur.fetchone()["n"]
    
    # 2. Selected students
    cur.execute(
        "SELECT COUNT(*) AS n FROM students WHERE (section ILIKE %s OR RIGHT(TRIM(section), 1) ILIKE %s) AND placement_status IN ('selected', 'joined')",
        (section_param, section_letter)
    )
    students_selected = cur.fetchone()["n"]
    
    # 3. Average & highest package
    cur.execute(
        "SELECT COALESCE(AVG(p.package_amount), 0) AS avg_pkg, COALESCE(MAX(p.package_amount), 0) AS max_pkg "
        "FROM placements p "
        "JOIN students s ON p.student_id = s.id "
        "WHERE (s.section ILIKE %s OR RIGHT(TRIM(s.section), 1) ILIKE %s) AND p.offer_status IN ('offered', 'accepted')",
        (section_param, section_letter)
    )
    pkg = cur.fetchone()
    # Handle Decimal or float types conversion
    avg_package = round(float(pkg["avg_pkg"]), 2) if pkg["avg_pkg"] else 0.0
    highest_package = float(pkg["max_pkg"]) if pkg["max_pkg"] else 0.0
    
    # 4. Company distribution
    dist = {"Product": 0, "Service": 0, "Fintech": 0, "Others": 0}
    cur.execute(
        "SELECT c.industry, COUNT(DISTINCT s.id) AS count "
        "FROM placements p "
        "JOIN students s ON p.student_id = s.id "
        "JOIN companies c ON p.company_id = c.id "
        "WHERE (s.section ILIKE %s OR RIGHT(TRIM(s.section), 1) ILIKE %s) AND s.placement_status IN ('selected', 'joined') "
        "GROUP BY c.industry",
        (section_param, section_letter)
    )
    rows = cur.fetchall()
    total_placed = sum(r["count"] for r in rows)
    if total_placed > 0:
        for r in rows:
            ind = (r["industry"] or "").strip().lower()
            cnt = r["count"]
            if "product" in ind:
                dist["Product"] += cnt
            elif "service" in ind:
                dist["Service"] += cnt
            elif "fintech" in ind or "finance" in ind or "bank" in ind:
                dist["Fintech"] += cnt
            else:
                dist["Others"] += cnt
        # Convert to percentages
        for k in dist:
            dist[k] = round((dist[k] / total_placed) * 100)
            
    # 5. Department Analytics
    cur.execute(
        "SELECT d.name, "
        "COUNT(s.id) AS total, "
        "COUNT(CASE WHEN s.placement_status IN ('selected', 'joined') THEN 1 END) AS placed "
        "FROM students s "
        "JOIN departments d ON s.department_id = d.id "
        "WHERE (s.section ILIKE %s OR RIGHT(TRIM(s.section), 1) ILIKE %s) "
        "GROUP BY d.name "
        "ORDER BY d.name",
        (section_param, section_letter)
    )
    dept_rows = cur.fetchall()
    departments = []
    for r in dept_rows:
        pct = round((r["placed"] / r["total"]) * 100) if r["total"] else 0
        departments.append({
            "name": r["name"],
            "total": r["total"],
            "placed": r["placed"],
            "percentage": pct
        })
        
    # 6. Placement Pipeline Funnel
    cur.execute(
        "SELECT COUNT(*) AS n FROM students WHERE (section ILIKE %s OR RIGHT(TRIM(section), 1) ILIKE %s) AND eligible_status = TRUE",
        (section_param, section_letter)
    )
    eligible = cur.fetchone()["n"]
    
    cur.execute(
        "SELECT COUNT(DISTINCT s.id) AS n "
        "FROM pipeline_stages ps "
        "JOIN placements p ON ps.placement_id = p.id "
        "JOIN students s ON p.student_id = s.id "
        "WHERE (s.section ILIKE %s OR RIGHT(TRIM(s.section), 1) ILIKE %s) AND ps.stage = 'aptitude_test' AND ps.status = 'completed'",
        (section_param, section_letter)
    )
    aptitude = cur.fetchone()["n"]
    
    cur.execute(
        "SELECT COUNT(DISTINCT s.id) AS n "
        "FROM pipeline_stages ps "
        "JOIN placements p ON ps.placement_id = p.id "
        "JOIN students s ON p.student_id = s.id "
        "WHERE (s.section ILIKE %s OR RIGHT(TRIM(s.section), 1) ILIKE %s) AND ps.stage = 'technical_test' AND ps.status = 'completed'",
        (section_param, section_letter)
    )
    technical = cur.fetchone()["n"]
    
    placement_pct = round((students_selected / total_students) * 100, 1) if total_students else 0.0
    
    return jsonify({
        "section": section_param,
        "total_students": total_students,
        "students_selected": students_selected,
        "placement_percentage": placement_pct,
        "average_package": avg_package,
        "highest_package": highest_package,
        "company_distribution": dist,
        "departments": departments,
        "funnel": {
            "eligible": eligible,
            "aptitude": aptitude,
            "technical": technical,
            "selected": students_selected
        }
    })
