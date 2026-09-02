import io
import re
import hashlib
import pandas as pd

# Patch hashlib.md5 for OpenSSL / Python 3.8 compatibility with ReportLab
_orig_md5 = hashlib.md5
def _safe_md5(*args, **kwargs):
    kwargs.pop("usedforsecurity", None)
    return _orig_md5(*args, **kwargs)
hashlib.md5 = _safe_md5

from flask import Blueprint, request, jsonify, send_file
from database import get_cursor
from routes.auth import token_required

reports_bp = Blueprint("reports", __name__)

# ---------------------------------------------------------------------------
# Job-role → required-skills mapping
# Used by the Skill Gap Analysis endpoint to derive recruiter demand from
# the companies.job_role column (no schema change required).
# ---------------------------------------------------------------------------
JOB_ROLE_SKILLS = {
    # Software / Engineering
    "software developer":        ["Python", "Java", "SQL", "Git", "Data Structures", "Problem Solving"],
    "software engineer":         ["Python", "Java", "SQL", "Git", "Data Structures", "Problem Solving", "System Design"],
    "full stack developer":      ["JavaScript", "React", "Node.js", "SQL", "HTML/CSS", "Git", "REST APIs"],
    "frontend developer":        ["JavaScript", "React", "HTML/CSS", "TypeScript", "Git", "UI/UX Design"],
    "backend developer":         ["Python", "Java", "Node.js", "SQL", "REST APIs", "Git", "Docker"],
    "web developer":             ["HTML/CSS", "JavaScript", "React", "SQL", "Git", "REST APIs"],
    "mobile developer":          ["Java", "Kotlin", "Swift", "React Native", "Git", "REST APIs"],
    "android developer":         ["Java", "Kotlin", "Android SDK", "Git", "REST APIs", "Firebase"],
    "ios developer":             ["Swift", "Objective-C", "Xcode", "Git", "REST APIs"],
    "devops engineer":           ["Docker", "Kubernetes", "AWS", "Linux", "CI/CD", "Git", "Python"],
    "cloud engineer":            ["AWS", "Azure", "Docker", "Kubernetes", "Linux", "Python", "Terraform"],
    "sre":                       ["Linux", "Docker", "Kubernetes", "Python", "Monitoring", "CI/CD"],
    "qa engineer":               ["Selenium", "Python", "SQL", "Manual Testing", "Automation Testing", "Git"],
    "test engineer":             ["Selenium", "Python", "SQL", "Manual Testing", "Automation Testing"],
    "embedded engineer":         ["C", "C++", "Embedded Systems", "RTOS", "Microcontrollers"],

    # Data & AI
    "data scientist":            ["Python", "Machine Learning", "SQL", "Statistics", "TensorFlow", "Data Visualization"],
    "data analyst":              ["SQL", "Excel", "Python", "Data Visualization", "Statistics", "Power BI"],
    "data engineer":             ["Python", "SQL", "Spark", "Hadoop", "ETL", "AWS", "Data Modeling"],
    "ml engineer":               ["Python", "Machine Learning", "TensorFlow", "PyTorch", "SQL", "Docker"],
    "ai engineer":               ["Python", "Machine Learning", "Deep Learning", "NLP", "TensorFlow", "Computer Vision"],
    "business analyst":          ["SQL", "Excel", "Data Visualization", "Communication", "Requirements Gathering", "Power BI"],
    "bi analyst":                ["SQL", "Power BI", "Tableau", "Excel", "Data Visualization", "ETL"],

    # Cybersecurity
    "cybersecurity analyst":     ["Network Security", "Linux", "Python", "SIEM", "Incident Response", "Firewalls"],
    "security engineer":         ["Network Security", "Linux", "Python", "Penetration Testing", "AWS", "Encryption"],
    "soc analyst":               ["SIEM", "Network Security", "Linux", "Incident Response", "Threat Intelligence"],

    # IT / Support / Admin
    "system administrator":      ["Linux", "Windows Server", "Networking", "Scripting", "Active Directory"],
    "network engineer":          ["Networking", "Cisco", "Firewalls", "TCP/IP", "Linux", "Troubleshooting"],
    "it support":                ["Troubleshooting", "Windows", "Networking", "Communication", "Active Directory"],
    "technical support":         ["Troubleshooting", "Communication", "Networking", "Windows", "Linux"],

    # Management / Business
    "project manager":           ["Project Management", "Agile", "Communication", "Leadership", "MS Office"],
    "product manager":           ["Product Management", "Agile", "Communication", "Data Analysis", "UX Research"],
    "hr executive":              ["Communication", "MS Office", "Recruitment", "Employee Relations", "Leadership"],
    "marketing executive":       ["Communication", "Digital Marketing", "Social Media", "Content Writing", "SEO"],
    "sales executive":           ["Communication", "Negotiation", "CRM", "Presentation", "Sales Strategy"],
    "operations executive":      ["MS Office", "Communication", "Logistics", "Process Improvement", "Leadership"],
    "management trainee":        ["Communication", "Leadership", "MS Office", "Problem Solving", "Teamwork"],
    "business development":      ["Communication", "Negotiation", "CRM", "Sales Strategy", "Presentation"],

    # Finance / Accounting
    "accountant":                ["Accounting", "Tally", "Excel", "GST", "Financial Reporting", "Communication"],
    "financial analyst":         ["Excel", "Financial Modeling", "SQL", "Accounting", "Data Visualization"],
    "auditor":                   ["Accounting", "Auditing", "Excel", "Compliance", "Communication"],

    # Design / Creative
    "ui/ux designer":            ["Figma", "UI/UX Design", "Adobe XD", "HTML/CSS", "User Research", "Prototyping"],
    "graphic designer":          ["Photoshop", "Illustrator", "Canva", "Creativity", "Communication"],

    # Hospitality
    "hotel manager":             ["Hospitality Management", "Communication", "Leadership", "Customer Service", "Event Management"],
    "front desk executive":      ["Communication", "Customer Service", "MS Office", "Hospitality Management"],
    "event coordinator":         ["Event Management", "Communication", "Negotiation", "Teamwork", "Creativity"],
    "food and beverage manager": ["F&B Management", "Communication", "Leadership", "Customer Service", "Inventory Management"],

    # Consulting
    "consultant":                ["Communication", "Problem Solving", "Data Analysis", "Presentation", "MS Office"],
    "associate consultant":      ["Communication", "Problem Solving", "SQL", "Excel", "Teamwork"],

    # Catch-all / Generic
    "trainee":                   ["Communication", "MS Office", "Teamwork", "Problem Solving"],
    "intern":                    ["Communication", "MS Office", "Teamwork", "Problem Solving", "Adaptability"],
    "associate":                 ["Communication", "MS Office", "Teamwork", "Problem Solving", "SQL"],
}

# Suggested training descriptions for common skills
SKILL_TRAINING_MAP = {
    "Python":             "Intensive Python programming bootcamp with DSA focus",
    "Java":               "Core Java & OOP workshop with hands-on projects",
    "JavaScript":         "Modern JavaScript (ES6+) and DOM manipulation",
    "React":              "React.js fundamentals with real-world project building",
    "Node.js":            "Server-side JavaScript with Express & REST API design",
    "SQL":                "Database design, querying, and optimization workshop",
    "HTML/CSS":           "Responsive web design with modern CSS techniques",
    "Git":                "Version control mastery with Git & GitHub workflows",
    "Docker":             "Containerization and DevOps fundamentals",
    "AWS":                "Cloud computing essentials with AWS certifications prep",
    "Machine Learning":   "ML foundations: supervised & unsupervised learning",
    "Data Structures":    "DSA problem-solving bootcamp for placements",
    "Communication":      "Professional communication & soft skills training",
    "Problem Solving":    "Analytical thinking and aptitude training sessions",
    "Leadership":         "Leadership development & team management workshop",
    "Excel":              "Advanced Excel: formulas, pivot tables, macros",
    "Data Visualization": "Dashboard building with Power BI / Tableau",
    "REST APIs":          "API design and integration best practices",
    "System Design":      "System design interview preparation workshop",
    "Agile":              "Agile & Scrum methodology certification prep",
    "Linux":              "Linux administration and shell scripting",
    "Networking":         "Computer networking fundamentals (CCNA-level)",
    "Selenium":           "Test automation with Selenium & frameworks",
    "TypeScript":         "TypeScript for scalable application development",
    "Power BI":           "Business intelligence dashboards with Power BI",
}


def _normalize_skill(s):
    """Lowercase, strip whitespace, collapse multiple spaces."""
    return re.sub(r'\s+', ' ', s.strip()).title()


def _match_role_skills(job_role):
    """Return the skill list for a job role using fuzzy prefix matching."""
    if not job_role:
        return []
    role_lower = job_role.strip().lower()
    # Exact match first
    if role_lower in JOB_ROLE_SKILLS:
        return JOB_ROLE_SKILLS[role_lower]
    # Substring match — pick the first key that appears in the role
    for key, skills in JOB_ROLE_SKILLS.items():
        if key in role_lower or role_lower in key:
            return skills
    # Fallback: generic trainee skills
    return JOB_ROLE_SKILLS.get("trainee", [])


@reports_bp.route("/skill-gap", methods=["GET"])
@token_required()
def skill_gap_analysis():
    """Compute skill gap: recruiter demand vs student supply."""
    cur = get_cursor()
    department_filter = request.args.get("department", "").strip()

    # ---- 1. Gather student skills ----
    if department_filter:
        cur.execute(
            "SELECT s.skills, d.name AS department FROM students s "
            "LEFT JOIN departments d ON d.id = s.department_id "
            "WHERE s.skills IS NOT NULL AND s.skills != '' AND d.name = ?",
            (department_filter,),
        )
    else:
        cur.execute(
            "SELECT s.skills, d.name AS department FROM students s "
            "LEFT JOIN departments d ON d.id = s.department_id "
            "WHERE s.skills IS NOT NULL AND s.skills != ''"
        )
    student_rows = cur.fetchall()

    student_skill_counts = {}   # skill -> count of students
    dept_skill_map = {}         # dept -> {skill -> count}
    total_students_with_skills = len(student_rows)

    for row in student_rows:
        dept = row.get("department") or "Unknown"
        raw_skills = row["skills"] or ""
        seen_skills = set()
        for part in raw_skills.split(","):
            skill = _normalize_skill(part)
            if not skill:
                continue
            if skill not in seen_skills:
                student_skill_counts[skill] = student_skill_counts.get(skill, 0) + 1
                seen_skills.add(skill)
                dept_skill_map.setdefault(dept, {})
                dept_skill_map[dept][skill] = dept_skill_map[dept].get(skill, 0) + 1

    # ---- 2. Gather recruiter demand from company job roles ----
    cur.execute("SELECT job_role, name FROM companies WHERE job_role IS NOT NULL AND job_role != ''")
    company_rows = cur.fetchall()

    demand_skill_counts = {}   # skill -> number of companies demanding it
    for row in company_rows:
        required = _match_role_skills(row["job_role"])
        for skill in required:
            norm = _normalize_skill(skill)
            demand_skill_counts[norm] = demand_skill_counts.get(norm, 0) + 1

    # ---- 3. Compute gap analysis ----
    all_skills = set(list(demand_skill_counts.keys()) + list(student_skill_counts.keys()))
    gaps = []
    for skill in all_skills:
        demand = demand_skill_counts.get(skill, 0)
        supply = student_skill_counts.get(skill, 0)
        if demand > 0:
            gap_pct = max(0, round(((demand - supply) / demand) * 100, 1)) if demand > supply else 0
        else:
            gap_pct = 0
        status = "critical" if gap_pct > 70 else ("moderate" if gap_pct > 30 else "covered")
        gaps.append({
            "skill": skill,
            "demand": demand,
            "supply": supply,
            "gap_percentage": gap_pct,
            "status": status,
        })

    # Sort by gap_percentage desc
    gaps.sort(key=lambda x: (-x["gap_percentage"], -x["demand"]))

    # Top demanded / top student skills
    top_demanded = sorted(demand_skill_counts.items(), key=lambda x: -x[1])[:15]
    top_student = sorted(student_skill_counts.items(), key=lambda x: -x[1])[:15]

    # Surplus: skills students have but recruiters don't demand
    surplus = [
        {"skill": s, "count": c}
        for s, c in sorted(student_skill_counts.items(), key=lambda x: -x[1])
        if s not in demand_skill_counts
    ][:10]

    # Department breakdown: top 8 skills per department
    dept_breakdown = []
    for dept, skills_map in sorted(dept_skill_map.items()):
        top = sorted(skills_map.items(), key=lambda x: -x[1])[:8]
        dept_breakdown.append({
            "department": dept,
            "skills": [{"skill": s, "count": c} for s, c in top],
        })

    # Training recommendations: top 5 gap skills with training info
    training_recs = []
    for g in gaps[:5]:
        sk = g["skill"]
        training_recs.append({
            "skill": sk,
            "gap_percentage": g["gap_percentage"],
            "demand": g["demand"],
            "supply": g["supply"],
            # pyrefly: ignore [no-matching-overload]
            "recommendation": SKILL_TRAINING_MAP.get(sk, f"Organize a targeted {sk} workshop for students"),
        })

    # Summary stats
    demanded_set = set(demand_skill_counts.keys())
    student_set = set(student_skill_counts.keys())
    covered_count = len(demanded_set & student_set)
    coverage_pct = round((covered_count / len(demanded_set)) * 100, 1) if demanded_set else 0
    critical_count = sum(1 for g in gaps if g["status"] == "critical")

    return jsonify({
        "summary": {
            "total_student_skills": len(student_set),
            "total_demand_skills": len(demanded_set),
            "coverage_percentage": coverage_pct,
            "critical_gaps": critical_count,
            "students_with_skills": total_students_with_skills,
            "companies_analyzed": len(company_rows),
        },
        "top_demanded_skills": [{"skill": s, "count": c} for s, c in top_demanded],
        "top_student_skills": [{"skill": s, "count": c} for s, c in top_student],
        "skill_gaps": gaps,
        "surplus_skills": surplus,
        "department_breakdown": dept_breakdown,
        "training_recommendations": training_recs,
    })


def _students_df(filters):
    where, params = [], []
    if filters.get("department"):
        where.append("d.name = %s"); params.append(filters["department"])
    if filters.get("academic_year"):
        where.append("s.academic_year = %s"); params.append(filters["academic_year"])
    if filters.get("placement_status"):
        where.append("s.placement_status = %s"); params.append(filters["placement_status"])
    where_clause = f"WHERE {' AND '.join(where)}" if where else ""

    cur = get_cursor()
    cur.execute(
        f"""SELECT s.register_number, s.name, d.name AS department, s.section, s.academic_year,
                   s.cgpa, s.backlogs, s.placement_status, comp.name AS company_name, p.package_amount
            FROM students s
            LEFT JOIN departments d ON d.id = s.department_id
            LEFT JOIN placements p ON p.student_id = s.id AND p.current_stage IN ('selected','joined')
            LEFT JOIN companies comp ON comp.id = p.company_id
            {where_clause}
            ORDER BY s.name""",
        params,
    )
    return pd.DataFrame(cur.fetchall())


def _companies_df():
    cur = get_cursor()
    cur.execute(
        """SELECT c.name, c.industry, c.state, c.visit_date, c.package_amount,
                  (SELECT COUNT(*) FROM placements p WHERE p.company_id = c.id) AS applications,
                  (SELECT COUNT(*) FROM placements p WHERE p.company_id = c.id AND p.current_stage IN ('selected','joined')) AS selected
           FROM companies c ORDER BY c.visit_date DESC NULLS LAST"""
    )
    return pd.DataFrame(cur.fetchall())


def _df_to_response(df, fmt, base_name):
    if fmt == "csv":
        buf = io.StringIO()
        df.to_csv(buf, index=False)
        mem = io.BytesIO(buf.getvalue().encode("utf-8"))
        return send_file(mem, mimetype="text/csv", as_attachment=True, download_name=f"{base_name}.csv")

    if fmt == "excel":
        mem = io.BytesIO()
        # pyrefly: ignore
        with pd.ExcelWriter(mem, engine="openpyxl") as writer:
            # pyrefly: ignore
            df.to_excel(writer, index=False, sheet_name="Report")
        mem.seek(0)
        return send_file(
            mem,
            mimetype="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            as_attachment=True,
            download_name=f"{base_name}.xlsx",
        )

    if fmt == "pdf":
        # pyrefly: ignore [untyped-import]
        from reportlab.lib import colors
        # pyrefly: ignore [untyped-import]
        from reportlab.lib.pagesizes import landscape, A4
        # pyrefly: ignore [untyped-import]
        from reportlab.platypus import SimpleDocTemplate, Table, TableStyle
        mem = io.BytesIO()
        doc = SimpleDocTemplate(mem, pagesize=landscape(A4))
        if df.empty or len(df.columns) == 0:
            data = [["No records found for the specified filters"]]
            repeat_rows = 0
        else:
            data = [list(df.columns)] + df.astype(str).values.tolist()
            repeat_rows = 1 if len(data) > 1 else 0
        table = Table(data, repeatRows=repeat_rows)
        table.setStyle(TableStyle([
            ("BACKGROUND", (0, 0), (-1, 0), colors.HexColor("#1f2937")),
            ("TEXTCOLOR", (0, 0), (-1, 0), colors.white),
            ("FONTSIZE", (0, 0), (-1, -1), 7),
            ("GRID", (0, 0), (-1, -1), 0.5, colors.grey),
        ]))
        doc.build([table])
        mem.seek(0)
        return send_file(mem, mimetype="application/pdf", as_attachment=True, download_name=f"{base_name}.pdf")

    return jsonify({"error": "Unsupported format. Use csv, excel, or pdf"}), 400


@reports_bp.route("/students", methods=["GET"])
@token_required()
def student_report():
    fmt = request.args.get("format", "csv")
    filters = {
        "department": request.args.get("department"),
        "academic_year": request.args.get("academic_year"),
        "placement_status": request.args.get("placement_status"),
    }
    df = _students_df(filters)
    return _df_to_response(df, fmt, "student_report")


@reports_bp.route("/companies", methods=["GET"])
@token_required()
def company_report():
    fmt = request.args.get("format", "csv")
    df = _companies_df()
    return _df_to_response(df, fmt, "company_report")


@reports_bp.route("/placement-summary", methods=["GET"])
@token_required()
def placement_summary():
    fmt = request.args.get("format", "csv")
    cur = get_cursor()
    cur.execute(
        """SELECT d.name AS department, COUNT(s.id) AS total_students,
                  COUNT(*) FILTER (WHERE s.placement_status IN ('selected','joined')) AS placed,
                  ROUND(AVG(p.package_amount)::numeric, 2) AS avg_package
           FROM students s
           LEFT JOIN departments d ON d.id = s.department_id
           LEFT JOIN placements p ON p.student_id = s.id AND p.current_stage IN ('selected','joined')
           GROUP BY d.name ORDER BY d.name"""
    )
    df = pd.DataFrame(cur.fetchall())
    return _df_to_response(df, fmt, "placement_summary")
