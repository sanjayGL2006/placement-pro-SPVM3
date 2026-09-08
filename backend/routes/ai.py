"""AI routes module providing resume analysis, placement statistics chatbot,
interview preparation, and drive recommendation utilities.
"""

# pyrefly: ignore [missing-import]
import os
# pyrefly: ignore [missing-import]
import re
# pyrefly: ignore [missing-import]
import json
# pyrefly: ignore [missing-import]
from flask import Blueprint, request, jsonify

try:
    # pyrefly: ignore [missing-import]
    from database import get_cursor
except ImportError:
    # pyrefly: ignore [missing-import]
    from ..database import get_cursor

# pyrefly: ignore [missing-import]
from .auth import token_required

ai_bp = Blueprint("ai", __name__)

# Optional Google Gemini Generative AI initialization
gemini_model = None
try:
    api_key = os.environ.get("GEMINI_API_KEY") or os.environ.get("GOOGLE_API_KEY")
    if api_key:
        # pyrefly: ignore [missing-import]
        import google.generativeai as genai
        genai.configure(api_key=api_key)
        gemini_model = genai.GenerativeModel("gemini-1.5-flash")
except Exception as _e:
    gemini_model = None


# Technical skills dictionary by category
SKILL_CATEGORIES = {
    "Programming Languages": ["python", "java", "c++", "c", "javascript", "typescript", "c#", "php", "ruby", "go", "rust", "kotlin", "swift"],
    "Web & Frameworks": ["react", "node.js", "express", "django", "flask", "angular", "vue", "html", "css", "bootstrap", "tailwind", "spring boot", "fastapi"],
    "Databases & Cloud": ["sql", "mysql", "postgresql", "mongodb", "sqlite", "aws", "azure", "gcp", "docker", "kubernetes", "firebase", "redis"],
    "Tools & Concepts": ["git", "github", "rest api", "graphql", "agile", "scrum", "data structures", "algorithms", "oop", "linux", "ci/cd", "unit testing"]
}

AI_BUZZWORDS = [
    "delve", "testament", "tapestry", "multifaceted", "spearhead", "synergy",
    "foster", "leverage", "seamlessly", "pivotal", "elevate", "realm",
    "beacon", "transformative", "harnessing", "dynamic landscape", "paramount",
    "holistic approach", "cutting-edge", "game-changer"
]


def analyze_resume_text(text, target_role="Software Engineer"):
    """Perform deterministic ATS scoring, AI generation probability detection,
    and recruiter summary extraction on resume text.
    """
    text_clean = text or ""
    words = re.findall(r"\b[A-Za-z0-9+#.-]+\b", text_clean)
    word_count = len(words)
    text_lower = text_clean.lower()

    # 1. Header & Section Checks
    has_education = bool(re.search(r"\b(education|degree|bca|b\.sc|bba|b\.com|university|college|gpa|cgpa|percentage)\b", text_lower))
    has_experience = bool(re.search(r"\b(experience|work history|employment|internship|project|projects|contributions)\b", text_lower))
    has_skills = bool(re.search(r"\b(skills|technical skills|technologies|proficiencies|competencies)\b", text_lower))
    has_email = bool(re.search(r"[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+", text_clean))
    has_phone = bool(re.search(r"(\+?\d{1,3}[-.\s]?)?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}", text_clean))

    # 2. Action Verbs & Metrics
    action_verbs = [
        "built", "developed", "designed", "implemented", "created", "led", "managed",
        "engineered", "optimized", "integrated", "automated", "resolved", "delivered",
        "achieved", "reduced", "increased", "collaborated"
    ]
    found_actions = [v for v in action_verbs if re.search(r"\b" + v + r"\b", text_lower)]
    metric_matches = re.findall(r"\b(\d+%\s*|\$\d+|\₹\d+|\d+\s*(?:users|clients|records|projects|seconds|ms|queries))\b", text_lower)

    # 3. Detect Skills across categories
    detected_skills = []
    keyword_optimization = []
    for cat, skills in SKILL_CATEGORIES.items():
        found_in_cat = []
        for s in skills:
            pattern = r"\b" + re.escape(s) + r"\b"
            if re.search(pattern, text_lower):
                found_in_cat.append(s.title())
                if s.title() not in detected_skills:
                    detected_skills.append(s.title())
        keyword_optimization.append({
            "category": cat,
            "found": len(found_in_cat),
            "total": len(skills),
            "keywords": found_in_cat
        })

    # 4. ATS Score Calculation (0-100)
    score = 0
    # Core Sections (up to 30)
    if has_education: score += 8
    if has_experience: score += 10
    if has_skills: score += 8
    if has_email and has_phone: score += 4
    elif has_email or has_phone: score += 2

    # Skills breadth (up to 30)
    skill_pts = min(len(detected_skills) * 3, 30)
    score += skill_pts

    # Action verbs & impact metrics (up to 25)
    action_pts = min(len(found_actions) * 3, 15)
    metric_pts = min(len(metric_matches) * 5, 10)
    score += action_pts + metric_pts

    # Word count / formatting length (up to 15)
    if 250 <= word_count <= 900:
        score += 15
    elif 150 <= word_count < 250 or 900 < word_count <= 1400:
        score += 8
    else:
        score += 3

    score = min(max(score, 20), 98)

    # Formatting Checks
    format_checks = [
        {
            "item": "Contact Information",
            "status": "pass" if (has_email and has_phone) else ("warn" if (has_email or has_phone) else "fail"),
            "note": "Email and phone contact detected" if (has_email and has_phone) else "Missing email or phone number"
        },
        {
            "item": "Education Section",
            "status": "pass" if has_education else "fail",
            "note": "Education / academic credentials recognized" if has_education else "Standard Education section heading not found"
        },
        {
            "item": "Technical Skills Section",
            "status": "pass" if has_skills else "warn",
            "note": f"{len(detected_skills)} skills identified" if detected_skills else "No distinct Skills header located"
        },
        {
            "item": "Action-Driven Project Bullet Points",
            "status": "pass" if len(found_actions) >= 3 else "warn",
            "note": f"{len(found_actions)} strong action verbs found" if found_actions else "Use strong action verbs like Designed, Built, Optimized"
        },
        {
            "item": "Measurable Impact & Metrics",
            "status": "pass" if len(metric_matches) >= 2 else "warn",
            "note": f"{len(metric_matches)} quantified achievements detected" if metric_matches else "Add quantifiable numbers, percentages, or scale"
        }
    ]
    overall_format = "pass" if sum(1 for c in format_checks if c["status"] == "pass") >= 3 else "fail"

    critical_fixes = []
    if not has_email or not has_phone:
        critical_fixes.append("Ensure your full email address and reachable telephone number are prominent in the header.")
    if len(detected_skills) < 5:
        critical_fixes.append("List at least 6-8 core technical skills, programming languages, and industry frameworks.")
    if len(metric_matches) == 0:
        critical_fixes.append("Quantify your project outcomes (e.g. 'boosted efficiency by 25%', 'served 500+ daily queries').")
    if word_count < 200:
        critical_fixes.append("Resume appears too short; elaborate on projects, academic achievements, and internships.")
    if not critical_fixes:
        critical_fixes.append("Keep your resume updated with the latest cloud deployment and full-stack project links.")

    # 5. AI Content Detection
    found_buzzwords = [w for w in AI_BUZZWORDS if re.search(r"\b" + w + r"\b", text_lower)]
    sentences = [s.strip() for s in re.split(r"[.!?]+", text_clean) if len(s.strip().split()) > 3]
    if sentences:
        lengths = [len(s.split()) for s in sentences]
        avg_len = sum(lengths) / len(lengths)
        variance = sum((l - avg_len) ** 2 for l in lengths) / len(lengths)
    else:
        variance = 20

    # Lower variance and high buzzword count correlates with AI generation
    ai_pct = 10 + (len(found_buzzwords) * 12)
    if variance < 8:
        ai_pct += 25
    elif variance > 30:
        ai_pct -= 10
    ai_pct = min(max(ai_pct, 5), 92)
    human_pct = 100 - ai_pct

    tone = "Natural & Human-Authored" if ai_pct < 35 else ("Moderately AI-Assisted" if ai_pct < 65 else "Heavily AI-Generated / Templated")
    phrases_to_rewrite = []
    for bw in found_buzzwords[:3]:
        phrases_to_rewrite.append({
            "original": f"Used generic phrase: '{bw}'",
            "suggested_rewrite": f"Replace with concrete action: 'engineered', 'spearheaded' -> 'led team of 4 to deliver', or state exact result."
        })

    # 6. Recruiter Summary
    if score >= 75:
        verdict = "Ready to submit"
        readability = "High (Clean, scannable, impact-oriented)"
    elif score >= 50:
        verdict = "Needs minor tweaks"
        readability = "Moderate (Good fundamentals, needs more metrics & skill keywords)"
    else:
        verdict = "High risk of rejection"
        readability = "Needs Work (Missing critical sections or quantified results)"

    return {
        "section1_ats": {
            "ats_score": score,
            "keyword_optimization": keyword_optimization,
            "formatting_check": {
                "overall": overall_format,
                "checks": format_checks
            },
            "critical_fixes": critical_fixes,
            "detected_skills": detected_skills
        },
        "section2_ai": {
            "ai_content_pct": ai_pct,
            "human_content_pct": human_pct,
            "tone_analysis": f"{tone}. Resume shows {len(found_buzzwords)} standard generative clichés and {'varied' if variance >= 15 else 'repetitive'} sentence structures.",
            "phrases_to_rewrite": phrases_to_rewrite
        },
        "section3_recruiter": {
            "readability_impact": readability,
            "final_verdict": verdict,
            "key_strengths": [
                f"Identified {len(detected_skills)} relevant technical competencies",
                "Clean structural sections with recognizable headings",
                f"{len(found_actions)} actionable project bullet points"
            ],
            "areas_for_improvement": critical_fixes[:2]
        }
    }


@ai_bp.route("/analyze-resume", methods=["POST"])
@token_required()
def analyze_resume():
    """AI Resume Analyzer & ATS Compatibility Audit."""
    data = request.get_json(silent=True) or {}
    text = data.get("resume_text", "")
    target_role = data.get("job_role", "Software Engineer")

    # Support multipart file upload as well
    if not text and "resume_file" in request.files:
        f = request.files["resume_file"]
        try:
            raw_bytes = f.read()
            text = raw_bytes.decode("utf-8", errors="ignore")
        except Exception:
            text = ""

    if not text or len(text.strip()) < 20:
        return jsonify({"error": "Valid resume text or file is required (minimum 20 characters)"}), 400

    result = analyze_resume_text(text, target_role)
    return jsonify(result)


@ai_bp.route("/chatbot", methods=["POST"])
@token_required()
def chatbot():
    """AI Chatbot: answers natural language queries about placements, students, and eligibility."""
    data = request.get_json(force=True) or {}
    query = data.get("query", "").strip()
    if not query:
        return jsonify({"error": "query required"}), 400

    cur = get_cursor()
    q_lower = query.lower()
    db_response = None

    try:
        # 1. Highest Package / Top Offer
        if any(k in q_lower for k in ["highest package", "max package", "top package", "highest offer", "best offer", "top company"]):
            cur.execute("""
                SELECT p.package_amount, comp.name AS company_name, s.name AS student_name, d.name AS department_name
                FROM placements p
                JOIN companies comp ON comp.id = p.company_id
                JOIN students s ON s.id = p.student_id
                LEFT JOIN departments d ON s.department_id = d.id
                WHERE p.package_amount IS NOT NULL
                ORDER BY p.package_amount DESC
                LIMIT 1
            """)
            row = cur.fetchone()
            if row and row["package_amount"]:
                dept = f" ({row['department_name']})" if row.get("department_name") else ""
                db_response = f"The highest package recorded is **₹{float(row['package_amount']):.2f} LPA**, offered by **{row['company_name']}** to **{row['student_name']}**{dept}."
            else:
                db_response = "No placement packages have been recorded in the database yet."

        # 2. Average Package / Median
        elif any(k in q_lower for k in ["average package", "avg package", "mean package", "average salary", "avg salary"]):
            cur.execute("""
                SELECT AVG(p.package_amount) AS avg_pkg, COUNT(p.id) AS placed_cnt
                FROM placements p
                WHERE p.package_amount IS NOT NULL AND p.package_amount > 0
            """)
            row = cur.fetchone()
            if row and row["avg_pkg"]:
                db_response = f"The overall average placement package across all placed candidates is **₹{float(row['avg_pkg']):.2f} LPA** (based on {row['placed_cnt']} verified offers)."
            else:
                db_response = "Not enough offer data recorded yet to compute an average package."

        # 3. Placement Percentage / Rate
        elif any(k in q_lower for k in ["placement percentage", "placement rate", "percentage placed", "ratio"]):
            cur.execute("SELECT COUNT(*) AS total FROM students")
            total = cur.fetchone()["total"]
            cur.execute("SELECT COUNT(*) AS placed FROM students WHERE placement_status IN ('selected', 'joined')")
            placed = cur.fetchone()["placed"]
            if total > 0:
                pct = round((placed / total) * 100, 1)
                db_response = f"The current institutional placement rate is **{pct}%** ({placed} of {total} registered students placed)."
            else:
                db_response = "No students are currently registered in the database."

        # 4. Department-Wise Stats (BCA, B.Sc, BBA, B.Com)
        elif any(dept in q_lower for dept in ["bca", "b.sc", "bsc", "bba", "b.com", "bcom"]):
            target_dept = "BCA"
            if "b.sc" in q_lower or "bsc" in q_lower: target_dept = "B.Sc"
            elif "bba" in q_lower: target_dept = "BBA"
            elif "b.com" in q_lower or "bcom" in q_lower: target_dept = "B.Com"

            cur.execute("""
                SELECT 
                    COUNT(s.id) AS total_stu,
                    SUM(CASE WHEN s.placement_status IN ('selected', 'joined') THEN 1 ELSE 0 END) AS placed_stu,
                    AVG(CASE WHEN p.package_amount IS NOT NULL THEN p.package_amount ELSE NULL END) AS avg_pkg
                FROM students s
                LEFT JOIN departments d ON s.department_id = d.id
                LEFT JOIN placements p ON p.student_id = s.id AND p.current_stage IN ('selected', 'joined')
                WHERE LOWER(d.name) LIKE LOWER(%s)
            """, (f"%{target_dept}%",))
            res = cur.fetchone()
            tot = res["total_stu"] or 0
            plc = res["placed_stu"] or 0
            avg_p = float(res["avg_pkg"]) if res["avg_pkg"] else 0.0
            pct = round((plc / tot) * 100, 1) if tot > 0 else 0.0

            db_response = (
                f"**{target_dept} Placement Statistics:**\n"
                f"- Total Students: **{tot}**\n"
                f"- Placed Students: **{plc}** ({pct}%)\n"
                f"- Average Compensation: **₹{avg_p:.2f} LPA**" if avg_p > 0 else
                f"**{target_dept} Placement Statistics:**\n"
                f"- Total Students: **{tot}**\n"
                f"- Placed Students: **{plc}** ({pct}%)"
            )

        # 5. Placed vs Unplaced Count
        elif any(k in q_lower for k in ["how many students", "placed count", "unplaced count", "total students"]):
            if "unplaced" in q_lower or "not placed" in q_lower:
                cur.execute("SELECT COUNT(*) AS n FROM students WHERE placement_status = 'not_placed'")
                n = cur.fetchone()["n"]
                db_response = f"There are currently **{n} unplaced students** in the roster awaiting upcoming drives."
            elif "placed" in q_lower or "selected" in q_lower:
                cur.execute("SELECT COUNT(*) AS n FROM students WHERE placement_status IN ('selected', 'joined')")
                n = cur.fetchone()["n"]
                db_response = f"A total of **{n} students** have received verified placement offers."
            else:
                cur.execute("SELECT COUNT(*) AS n FROM students")
                n = cur.fetchone()["n"]
                db_response = f"There is a total of **{n} students** registered in Placement Pro."

        # 6. Total Companies / Active Drives
        elif any(k in q_lower for k in ["total companies", "how many companies", "which companies", "companies visited", "company list", "recruiters"]):
            cur.execute("SELECT COUNT(*) AS total_comp FROM companies")
            total_comp = cur.fetchone()["total_comp"]
            cur.execute("SELECT name, job_role, package_amount FROM companies ORDER BY id DESC LIMIT 5")
            recent = cur.fetchall()
            rec_str = ", ".join([f"**{c['name']}** ({c['package_amount'] or 'N/A'} LPA)" for c in recent])
            db_response = f"There are **{total_comp} partner companies** registered in our recruitment network. Recent drives include: {rec_str}."

        # 7. Students with specific skills (e.g. Python, React, Java)
        elif any(k in q_lower for k in ["students with", "who knows", "who have", "knows", "skill"]):
            m = re.search(r"(?:with|knows|know|have|skills?)\s+([a-zA-Z0-9+#.-]+)", q_lower)
            target_skill = m.group(1).strip() if m else "python"
            cur.execute(
                "SELECT name, register_number, skills FROM students WHERE LOWER(skills) LIKE LOWER(%s) LIMIT 5",
                (f"%{target_skill}%",)
            )
            rows = cur.fetchall()
            if rows:
                lines = [f"- **{r['name']}** ({r['register_number']}) — *Skills: {r['skills']}*" for r in rows]
                db_response = f"Here are students proficient in '**{target_skill.title()}**':\n" + "\n".join(lines)
            else:
                db_response = f"No students currently list '**{target_skill}**' in their verified skill profile."

        # 8. Eligibility Check for Company
        elif "eligible for" in q_lower:
            m = re.search(r"eligible for\s+([a-zA-Z0-9\s.-]+)", q_lower)
            comp_name = m.group(1).strip() if m else ""
            cur.execute(
                "SELECT id, name, min_cgpa, allowed_backlogs FROM companies WHERE LOWER(name) LIKE LOWER(%s) LIMIT 1",
                (f"%{comp_name}%",)
            )
            comp = cur.fetchone()
            if comp:
                min_cgpa = float(comp["min_cgpa"]) if comp["min_cgpa"] else 0.0
                backlogs = int(comp["allowed_backlogs"]) if comp["allowed_backlogs"] is not None else 99
                cur.execute(
                    "SELECT COUNT(*) AS n FROM students WHERE cgpa >= %s AND backlogs <= %s",
                    (min_cgpa, backlogs)
                )
                cnt = cur.fetchone()["n"]
                db_response = f"There are **{cnt} candidates eligible** for **{comp['name']}** (Requirements: CGPA >= {min_cgpa}, Max Backlogs <= {backlogs})."
            else:
                db_response = f"Could not find an active recruiter named '**{comp_name}**'."

        # 9. Greetings & Polite Assistance
        elif any(k in q_lower for k in ["hello", "hi", "hey", "greetings", "good morning", "good afternoon"]):
            db_response = "Hello! I am your Placement Pro AI Assistant. How can I assist you with candidate rosters, drive eligibility, or placement performance metrics today?"

        elif any(k in q_lower for k in ["help", "what can you do", "commands"]):
            db_response = (
                "You can query me on:\n"
                "- **Placement Metrics:** *What is the placement percentage?* or *Who got the highest package?*\n"
                "- **Department Breakdowns:** *How are BCA placements?* or *B.Sc stats*\n"
                "- **Candidate Search:** *Show me students with React skills*\n"
                "- **Company Eligibility:** *How many students are eligible for Wipro?*"
            )

    except Exception as e:
        print(f"Chatbot query error: {e}")
        db_response = None

    if db_response:
        return jsonify({
            "response": db_response,
            "source": "database"
        })

    # Optional Gemini LLM Fallback with placement system context
    if gemini_model:
        try:
            cur.execute("SELECT COUNT(*) AS tot FROM students")
            tot = cur.fetchone()["tot"]
            cur.execute("SELECT COUNT(*) AS plc FROM students WHERE placement_status IN ('selected','joined')")
            plc = cur.fetchone()["plc"]
            cur.execute("SELECT COUNT(*) AS co FROM companies")
            co = cur.fetchone()["co"]

            system_ctx = (
                f"You are Placement Pro AI Assistant for PESIAMS college portal. "
                f"Portal stats: Total students = {tot}, Placed = {plc}, Companies = {co}. "
                f"Answer politely and succinctly in Markdown."
            )
            resp = gemini_model.generate_content([
                {"role": "user", "parts": [f"{system_ctx}\n\nUser Question: {query}"]}
            ])
            return jsonify({
                "response": resp.text.strip(),
                "source": "gemini"
            })
        except Exception as _e:
            pass

    # Generic Fallback
    return jsonify({
        "response": "I didn't quite catch that. Try asking about **placement percentage**, **highest package**, **department placements (e.g. BCA)**, or search for students with specific skills (e.g. *show me students with Python*).",
        "source": "fallback"
    })


@ai_bp.route("/interview-prep", methods=["POST"])
@token_required()
def interview_prep():
    """AI Interview Prep: tailored technical and HR interview questions."""
    data = request.get_json(force=True) or {}
    student_id = data.get("student_id")
    company_id = data.get("company_id")
    job_role = data.get("job_role", "Software Engineer")

    cur = get_cursor()
    student_name = "Candidate"
    skills_list = []

    if student_id:
        cur.execute("SELECT name, skills FROM students WHERE id = %s", (student_id,))
        s = cur.fetchone()
        if s:
            student_name = s["name"]
            skills_list = [sk.strip() for sk in (s["skills"] or "").split(",") if sk.strip()]

    company_name = "Recruiter Drive"
    if company_id:
        cur.execute("SELECT name, job_role FROM companies WHERE id = %s", (company_id,))
        c = cur.fetchone()
        if c:
            company_name = c["name"]
            if c.get("job_role"):
                job_role = c["job_role"]

    tech_questions = [
        {
            "question": f"Explain your experience building scalable solutions relevant to the {job_role} role at {company_name}.",
            "suggested_answer": "Structure your answer using the STAR method: describe a core project architecture, tradeoffs chosen, and the tangible outcome."
        },
        {
            "question": "How do you ensure data consistency and handle database indexing in high-throughput applications?",
            "suggested_answer": "Explain database normalization, ACID transactions, and how B-Tree indexes speed up lookups at the cost of slight write overhead."
        },
        {
            "question": "What is the difference between synchronous and asynchronous processing, and when would you use a message queue?",
            "suggested_answer": "Synchronous blocks execution until complete. Asynchronous delegates tasks (like email or video encoding) via workers to keep user response times minimal."
        }
    ]

    hr_questions = [
        {
            "question": f"Why do you want to join {company_name} specifically as a {job_role}?",
            "tip": f"Highlight {company_name}'s recent technical milestones, culture, and explain how your academic background directly contributes to their team."
        },
        {
            "question": "Describe a challenging situation in a team project and how you resolved the conflict.",
            "tip": "Focus on active listening, objective data evaluation, and delivering the milestone on schedule without personal friction."
        }
    ]

    return jsonify({
        "role": job_role,
        "technical_questions": tech_questions,
        "hr_questions": hr_questions
    })


@ai_bp.route("/recommend-drives", methods=["POST"])
@token_required()
def recommend_drives():
    """Recommend best matched campus drives for a student based on CGPA and skills."""
    data = request.get_json(force=True) or {}
    student_id = data.get("student_id")
    cur = get_cursor()

    if not student_id:
        return jsonify({"error": "student_id required"}), 400

    cur.execute("SELECT * FROM students WHERE id = %s", (student_id,))
    student = cur.fetchone()
    if not student:
        return jsonify({"error": "Student not found"}), 404

    s_cgpa = float(student["cgpa"]) if student["cgpa"] else 0.0
    s_backlogs = int(student["backlogs"]) if student["backlogs"] is not None else 0
    s_skills = [sk.strip().lower() for sk in (student["skills"] or "").split(",") if sk.strip()]

    cur.execute("SELECT * FROM companies WHERE status = 'Active' OR status IS NULL")
    companies = cur.fetchall()

    recommendations = []
    for c in companies:
        min_cgpa = float(c["min_cgpa"]) if c["min_cgpa"] else 0.0
        allowed_backlogs = int(c["allowed_backlogs"]) if c["allowed_backlogs"] is not None else 99

        # Check hard eligibility
        is_eligible = (s_cgpa >= min_cgpa) and (s_backlogs <= allowed_backlogs)
        match_score = 60 if is_eligible else 30

        if s_cgpa > min_cgpa + 1.0: match_score += 15
        if s_backlogs == 0: match_score += 10
        if any(s in (c.get("job_role") or "").lower() for s in s_skills): match_score += 15

        recommendations.append({
            "company_id": c["id"],
            "company_name": c["name"],
            "job_role": c["job_role"] or "Graduate Trainee",
            "package_amount": float(c["package_amount"]) if c["package_amount"] else 0.0,
            "min_cgpa": min_cgpa,
            "is_eligible": is_eligible,
            "match_score": min(match_score, 99)
        })

    recommendations.sort(key=lambda x: x["match_score"], reverse=True)
    return jsonify({"recommendations": recommendations[:6]})
