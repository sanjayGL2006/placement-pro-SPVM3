import os
import re
import json
from flask import Blueprint, request, jsonify
from database import get_cursor
from routes.auth import token_required

ai_bp = Blueprint("ai", __name__)

# Gemini API disabled - using local rule-based NLP and database SQL engine
gemini_model = None


def extract_skills_from_text(text):
    """Comprehensive parser to extract matching technical skills from text."""
    known_skills = [
        # Programming Languages
        "python", "java", "javascript", "typescript", "c++", "c#", "c",
        "ruby", "php", "swift", "kotlin", "rust", "go", "scala", "perl",
        "r", "matlab", "dart", "lua", "haskell", "objective-c",
        # Web Frameworks & Libraries
        "react", "angular", "vue", "next.js", "nuxt", "svelte",
        "django", "flask", "fastapi", "express", "spring boot", "laravel",
        "rails", "asp.net", "node", "node.js", "bootstrap", "tailwind",
        "jquery", "redux", "graphql",
        # Databases
        "sql", "postgresql", "mysql", "mongodb", "redis", "cassandra",
        "elasticsearch", "dynamodb", "sqlite", "oracle", "firebase",
        # Cloud & DevOps
        "aws", "azure", "gcp", "docker", "kubernetes", "terraform",
        "jenkins", "ci/cd", "ansible", "nginx", "linux",
        # Data & AI/ML
        "machine learning", "deep learning", "nlp", "tensorflow",
        "pytorch", "pandas", "numpy", "scikit-learn", "opencv",
        "data science", "big data", "spark", "hadoop", "tableau",
        "power bi", "data analysis",
        # Tools & Others
        "git", "github", "gitlab", "jira", "figma", "postman",
        "rest api", "microservices", "agile", "scrum", "excel",
        "html", "css", "sass", "webpack",
    ]
    found = []
    text_lower = text.lower()
    for skill in known_skills:
        pattern = r"\b" + re.escape(skill) + r"\b"
        if re.search(pattern, text_lower):
            # Normalize display name
            if len(skill) <= 3 or skill in ("c++", "c#", "ci/cd"):
                found.append(skill.upper())
            elif "." in skill or "/" in skill:
                found.append(skill)
            else:
                found.append(skill.capitalize())
    return list(dict.fromkeys(found))  # deduplicate preserving order


# AI-sounding cliché phrases commonly flagged by AI detectors
AI_CLICHES = [
    ("leveraged cutting-edge", "Used modern"),
    ("spearheaded initiatives", "Led the project"),
    ("results-driven professional", "Professional with measurable outcomes"),
    ("synergized cross-functional teams", "Collaborated across departments"),
    ("passionate about delivering excellence", "Committed to high-quality work"),
    ("proven track record of success", "Consistently met project goals"),
    ("dynamic and innovative leader", "Team lead with hands-on experience"),
    ("orchestrated seamless integration", "Integrated systems smoothly"),
    ("utilized best-in-class methodologies", "Applied industry-standard methods"),
    ("adept at navigating complex challenges", "Experienced in solving technical problems"),
    ("robust and scalable solutions", "Reliable, scalable software"),
    ("leveraged", "Used"),
    ("spearheaded", "Led"),
    ("synergized", "Coordinated"),
    ("orchestrated", "Managed"),
    ("best-in-class", "industry-standard"),
    ("cutting-edge", "modern"),
    ("results-driven", "effective"),
    ("holistic approach", "comprehensive method"),
    ("paradigm shift", "major change"),
    ("deep dive into", "detailed analysis of"),
    ("streamlined operations", "improved workflows"),
    ("fostered a culture of", "encouraged"),
    ("strategically aligned", "planned to match"),
]

PASSIVE_PATTERNS = [
    r"\bwas\s+\w+ed\b",
    r"\bwere\s+\w+ed\b",
    r"\bbeen\s+\w+ed\b",
    r"\bbeing\s+\w+ed\b",
    r"\bis\s+\w+ed\b",
    r"\bare\s+\w+ed\b",
]

STRONG_ACTION_VERBS = [
    "built", "designed", "developed", "implemented", "created", "launched",
    "reduced", "increased", "improved", "optimized", "automated", "delivered",
    "architected", "deployed", "migrated", "refactored", "resolved", "achieved",
    "managed", "led", "mentored", "trained", "analyzed", "integrated",
    "configured", "tested", "debugged", "scaled", "wrote", "published",
]


def _detect_ai_content(text):
    """Heuristic AI-content detection based on cliché density and sentence patterns."""
    text_lower = text.lower()
    sentences = re.split(r'[.!?]+', text)
    sentences = [s.strip() for s in sentences if len(s.strip()) > 10]
    total_sentences = max(len(sentences), 1)

    # 1. Cliché hits
    cliche_hits = []
    for phrase, rewrite in AI_CLICHES:
        if phrase.lower() in text_lower:
            cliche_hits.append({"original": phrase, "suggested_rewrite": rewrite})

    # 2. Passive voice ratio
    passive_count = sum(1 for p in PASSIVE_PATTERNS for _ in re.finditer(p, text_lower))
    passive_ratio = passive_count / total_sentences

    # 3. Average sentence length (AI text tends to be uniformly long)
    word_counts = [len(s.split()) for s in sentences]
    avg_words = sum(word_counts) / total_sentences if word_counts else 0
    # Low variance in sentence length suggests AI
    variance = sum((w - avg_words) ** 2 for w in word_counts) / total_sentences if word_counts else 0
    uniformity_score = max(0, 1 - (variance / 100))  # 0-1, higher = more uniform = more AI-like

    # 4. Overuse of adjectives/adverbs (AI padding)
    filler_words = ["highly", "extremely", "significantly", "effectively", "efficiently",
                    "seamlessly", "strategically", "proactively", "exceptionally",
                    "comprehensively", "meticulously", "diligently"]
    filler_count = sum(1 for w in filler_words if w in text_lower)

    # Calculate AI percentage (heuristic blend)
    cliche_factor = min(len(cliche_hits) * 8, 40)       # up to 40%
    passive_factor = min(passive_ratio * 30, 20)          # up to 20%
    uniformity_factor = uniformity_score * 20             # up to 20%
    filler_factor = min(filler_count * 4, 20)             # up to 20%

    ai_pct = int(min(cliche_factor + passive_factor + uniformity_factor + filler_factor, 95))
    ai_pct = max(ai_pct, 5)  # floor at 5%

    # Tone analysis
    if ai_pct >= 60:
        tone = "Overly polished and robotic. Heavy use of generic corporate language that AI detectors will flag."
    elif ai_pct >= 35:
        tone = "Moderately generic. Some phrases sound templated, but core content appears authentic."
    else:
        tone = "Authentic and natural. The writing style has personal voice and specificity."

    return {
        "ai_content_pct": ai_pct,
        "human_content_pct": 100 - ai_pct,
        "tone_analysis": tone,
        "phrases_to_rewrite": cliche_hits[:5],  # top 5
    }


def _check_formatting(text):
    """Check resume formatting and structure signals."""
    text_lower = text.lower()
    checks = []
    passed = True

    # Section headers check
    essential_sections = ["education", "experience", "skills", "project"]
    found_sections = [s for s in essential_sections if s in text_lower]
    missing_sections = [s for s in essential_sections if s not in text_lower]

    if len(found_sections) >= 3:
        checks.append({"item": "Section Headers", "status": "pass",
                        "note": f"Found {len(found_sections)}/4 key sections: {', '.join(s.title() for s in found_sections)}"})
    else:
        passed = False
        checks.append({"item": "Section Headers", "status": "fail",
                        "note": f"Missing critical sections: {', '.join(s.title() for s in missing_sections)}"})

    # Bullet points / list items
    bullet_count = len(re.findall(r'(?m)^[\s]*[•\-\*\→\►]', text))
    if bullet_count >= 5:
        checks.append({"item": "Bullet Points", "status": "pass",
                        "note": f"{bullet_count} bullet items detected — good scanability"})
    else:
        passed = False
        checks.append({"item": "Bullet Points", "status": "fail",
                        "note": "Few or no bullet points. ATS parsers and recruiters prefer bulleted achievements."})

    # Contact info
    has_email = bool(re.search(r'[\w.+-]+@[\w-]+\.[\w.]+', text))
    has_phone = bool(re.search(r'[\+]?[\d\s\-\(\)]{7,15}', text))
    if has_email and has_phone:
        checks.append({"item": "Contact Information", "status": "pass",
                        "note": "Email and phone number detected"})
    elif has_email or has_phone:
        checks.append({"item": "Contact Information", "status": "warn",
                        "note": "Only partial contact info found — ensure both email and phone are listed"})
    else:
        passed = False
        checks.append({"item": "Contact Information", "status": "fail",
                        "note": "No email or phone detected. This is critical for ATS parsing."})

    # Length check
    words = len(text.split())
    if 200 <= words <= 900:
        checks.append({"item": "Resume Length", "status": "pass",
                        "note": f"{words} words — within ideal range (200-900)"})
    elif words < 200:
        passed = False
        checks.append({"item": "Resume Length", "status": "fail",
                        "note": f"Only {words} words — too brief. Expand project descriptions and achievements."})
    else:
        checks.append({"item": "Resume Length", "status": "warn",
                        "note": f"{words} words — consider trimming to keep under 2 pages"})

    return {
        "overall": "pass" if passed else "fail",
        "checks": checks,
    }


def _build_keyword_optimization(extracted_skills, text):
    """Build keyword optimization breakdown by category."""
    text_lower = text.lower()
    categories = {
        "Programming Languages": ["python", "java", "javascript", "typescript", "c++", "c#", "c", "ruby", "go", "rust", "kotlin", "swift"],
        "Web & Frameworks": ["react", "angular", "vue", "django", "flask", "node", "spring boot", "express", "next.js", "bootstrap"],
        "Databases": ["sql", "postgresql", "mysql", "mongodb", "redis", "firebase", "dynamodb", "sqlite"],
        "Cloud & DevOps": ["aws", "azure", "gcp", "docker", "kubernetes", "jenkins", "ci/cd", "linux", "terraform"],
        "Data & AI/ML": ["machine learning", "deep learning", "nlp", "tensorflow", "pytorch", "pandas", "data science", "tableau"],
        "Tools & Methods": ["git", "agile", "scrum", "jira", "postman", "figma", "rest api", "microservices"],
    }

    breakdown = []
    for cat_name, cat_skills in categories.items():
        found_in_cat = []
        for skill in cat_skills:
            pattern = r"\b" + re.escape(skill) + r"\b"
            if re.search(pattern, text_lower):
                found_in_cat.append(skill)
        breakdown.append({
            "category": cat_name,
            "found": len(found_in_cat),
            "total": len(cat_skills),
            "keywords": found_in_cat,
        })

    return breakdown


@ai_bp.route("/analyze-resume", methods=["POST"])
@token_required()
def analyze_resume():
    """Comprehensive ATS Resume Audit: 3-section analysis covering ATS compatibility,
    AI content detection, and recruiter quick-check summary."""
    data = request.get_json(force=True) or {}
    text = data.get("resume_text", "").strip()
    if not text:
        return jsonify({"error": "resume_text required"}), 400

    # --- Try Gemini for full audit ---
    if gemini_model:
        try:
            prompt = f"""
            You are an expert ATS (Applicant Tracking System) Specialist, Technical Recruiter,
            and AI Detection Specialist. Perform a comprehensive, dual-layer audit of this resume.

            Return a JSON object with this exact structure:
            {{
              "section1_ats": {{
                "ats_score": <int 0-100>,
                "keyword_optimization": [
                  {{"category": "...", "found": <int>, "total": <int>, "keywords": [...]}}
                ],
                "formatting_check": {{
                  "overall": "pass"|"fail",
                  "checks": [{{"item": "...", "status": "pass"|"fail"|"warn", "note": "..."}}]
                }},
                "critical_fixes": ["...", "...", "..."],
                "detected_skills": ["..."]
              }},
              "section2_ai": {{
                "ai_content_pct": <int 0-100>,
                "human_content_pct": <int 0-100>,
                "tone_analysis": "...",
                "phrases_to_rewrite": [
                  {{"original": "...", "suggested_rewrite": "..."}}
                ]
              }},
              "section3_recruiter": {{
                "readability_impact": "...",
                "final_verdict": "Ready to submit"|"Needs minor tweaks"|"High risk of rejection"
              }}
            }}

            Resume text:
            {text}

            Strictly return JSON only, no markdown wrappers, no backticks.
            """
            response = gemini_model.generate_content(prompt)
            clean_json = response.text.replace("```json", "").replace("```", "").strip()
            result = json.loads(clean_json)
            return jsonify(result)
        except Exception as e:
            print(f"Gemini resume audit failed, falling back to heuristic engine: {e}")

    # --- Heuristic Engine (Fallback) ---
    text_lower = text.lower()
    words = text.split()
    word_count = len(words)

    # 1. Extract skills
    extracted_skills = extract_skills_from_text(text)

    # 2. Keyword optimization breakdown
    keyword_optimization = _build_keyword_optimization(extracted_skills, text)

    # 3. Formatting & structure check
    formatting = _check_formatting(text)

    # 4. ATS Score calculation
    ats_score = 30  # baseline

    # Skills contribution (up to +30)
    ats_score += min(len(extracted_skills) * 4, 30)

    # Section headers (up to +15)
    essential_sections = ["education", "experience", "skills", "project", "certification", "summary", "objective"]
    detected_sections = [s for s in essential_sections if s in text_lower]
    ats_score += min(len(detected_sections) * 3, 15)

    # Length sweetspot (up to +10)
    if 250 <= word_count <= 800:
        ats_score += 10
    elif 150 <= word_count < 250 or 800 < word_count <= 1200:
        ats_score += 5

    # Quantified achievements — numbers, percentages, metrics (up to +10)
    quant_matches = re.findall(r'\b\d+[%+]?\b', text)
    quant_count = len([m for m in quant_matches if len(m) > 1])  # filter single digits
    ats_score += min(quant_count * 2, 10)

    # Action verbs (up to +5)
    action_count = sum(1 for v in STRONG_ACTION_VERBS if re.search(r'\b' + v + r'\b', text_lower))
    ats_score += min(action_count * 1, 5)

    ats_score = max(min(ats_score, 100), 0)

    # 5. Critical fixes
    critical_fixes = []
    if len(extracted_skills) < 5:
        critical_fixes.append("Add more technical keywords: Include specific programming languages, frameworks, databases, and cloud platforms you've used. ATS filters scan for exact keyword matches.")
    if "experience" not in text_lower and "internship" not in text_lower:
        critical_fixes.append("Add a Work Experience or Internship section: Even academic or freelance experience counts. ATS systems heavily weight professional experience sections.")
    if quant_count < 3:
        critical_fixes.append("Quantify your achievements: Replace vague statements with numbers (e.g., 'Reduced load time by 40%', 'Managed team of 5', 'Processed 10K+ records daily').")
    if len(detected_sections) < 3:
        critical_fixes.append("Include standard resume sections: Education, Skills, Experience, and Projects are essential headers that ATS parsers look for.")
    if action_count < 3:
        critical_fixes.append("Start bullet points with strong action verbs: Use words like 'Built', 'Designed', 'Implemented', 'Reduced', 'Deployed' instead of passive descriptions.")
    if not re.search(r'[\w.+-]+@[\w-]+\.[\w.]+', text):
        critical_fixes.append("Add a professional email address: ATS systems extract contact info automatically — missing email means your application may be discarded.")
    # Limit to top 3
    critical_fixes = critical_fixes[:3]

    # 6. AI Content Detection
    ai_detection = _detect_ai_content(text)

    # 7. Recruiter Quick-Check
    # Action verb strength
    if action_count >= 8:
        verb_note = "Excellent use of strong action verbs — resume has high impact language."
    elif action_count >= 4:
        verb_note = "Decent action verb usage. Consider replacing passive descriptions with verbs like 'Built', 'Deployed', 'Optimized'."
    else:
        verb_note = "Weak action verb presence. Bullet points lack impact — rewrite with strong verbs to grab recruiter attention in the 6-second scan."

    # Visual hierarchy
    if len(detected_sections) >= 4 and re.findall(r'(?m)^[\s]*[•\-\*]', text):
        hierarchy_note = "Clear visual hierarchy with well-defined sections and bullet points."
    elif len(detected_sections) >= 2:
        hierarchy_note = "Partial structure detected — add more section headers and bullet formatting for faster scanning."
    else:
        hierarchy_note = "Poor visual hierarchy. Resume lacks clear sections and structured formatting, making it hard to scan."

    readability_impact = f"{hierarchy_note} {verb_note}"

    if ats_score >= 75 and ai_detection["ai_content_pct"] < 40:
        final_verdict = "Ready to submit"
    elif ats_score >= 55:
        final_verdict = "Needs minor tweaks"
    else:
        final_verdict = "High risk of rejection"

    return jsonify({
        "section1_ats": {
            "ats_score": ats_score,
            "keyword_optimization": keyword_optimization,
            "formatting_check": formatting,
            "critical_fixes": critical_fixes,
            "detected_skills": extracted_skills,
        },
        "section2_ai": ai_detection,
        "section3_recruiter": {
            "readability_impact": readability_impact,
            "final_verdict": final_verdict,
        },
    })


@ai_bp.route("/eligibility-recommendation", methods=["GET"])
@token_required()
def eligibility_recommendation():
    """AI Recommendation Engine: rank students for a specific company's requirements."""
    company_id = request.args.get("company_id")
    if not company_id:
        return jsonify({"error": "company_id query parameter required"}), 400

    cur = get_cursor()
    
    # 1. Fetch Company Drive details
    cur.execute("SELECT * FROM companies WHERE id = %s", (company_id,))
    company = cur.fetchone()
    if not company:
        return jsonify({"error": "Company not found"}), 404

    min_cgpa = float(company["min_cgpa"]) if company.get("min_cgpa") else 0.0
    allowed_backlogs = int(company["allowed_backlogs"]) if company.get("allowed_backlogs") is not None else 99
    
    eligible_depts = []
    if company.get("eligible_departments"):
        eligible_depts = [d.strip().lower() for d in company["eligible_departments"].split(",") if d.strip()]

    # 2. Fetch all students
    cur.execute(
        """SELECT s.*, d.name AS department_name 
           FROM students s 
           LEFT JOIN departments d ON s.department_id = d.id"""
    )
    all_students = cur.fetchall()
    
    recommended = []
    
    # Build company keywords
    company_text = f"{company.get('name', '')} {company.get('job_role', '')} {company.get('industry', '')}".lower()
    company_keywords = extract_skills_from_text(company_text)
    if not company_keywords:
        # Default keywords based on industry/name
        if "goldman" in company_text or "fintech" in company_text:
            company_keywords = ["Java", "C++", "SQL", "Python"]
        elif "amazon" in company_text or "microsoft" in company_text:
            company_keywords = ["Java", "Python", "Data Structures", "AWS", "SQL"]
        else:
            company_keywords = ["Java", "SQL", "HTML", "CSS", "Python"]

    for s in all_students:
        s_cgpa = float(s["cgpa"]) if s["cgpa"] else 0.0
        s_backlogs = s["backlogs"] if s["backlogs"] is not None else 0
        s_dept = (s["department_name"] or "").strip().lower()
        
        # Check basic baseline eligibility
        is_dept_eligible = True
        if eligible_depts:
            is_dept_eligible = any(d in s_dept for d in eligible_depts)
            
        cgpa_ok = s_cgpa >= min_cgpa
        backlogs_ok = s_backlogs <= allowed_backlogs
        
        is_eligible = is_dept_eligible and cgpa_ok and backlogs_ok
        
        # Parse student skills
        s_skills = [sk.strip() for sk in (s["skills"] or "").split(",") if sk.strip()]
        
        # Calculate Match / Fit Score
        fit_score = 50  # baseline if eligible
        
        # CGPA factor (up to +20 points for high CGPA)
        if s_cgpa > min_cgpa:
            fit_score += min(int((s_cgpa - min_cgpa) * 10), 20)
            
        # Backlog penalty
        if s_backlogs > 0:
            fit_score -= (s_backlogs * 15)
            
        # Skills match (up to +30 points)
        matched_skills = []
        missing_skills = []
        
        for kw in company_keywords:
            has_skill = False
            for sk in s_skills:
                if kw.lower() in sk.lower() or sk.lower() in kw.lower():
                    has_skill = True
                    break
            if has_skill:
                matched_skills.append(kw)
            else:
                missing_skills.append(kw)
                
        if company_keywords:
            skills_pct = len(matched_skills) / len(company_keywords)
            fit_score += int(skills_pct * 30)
            
        fit_score = max(min(fit_score, 100), 0)
        
        recommended.append({
            "student_id": s["id"],
            "name": s["name"],
            "register_number": s["register_number"],
            "department": s["department_name"],
            "section": s["section"],
            "cgpa": s_cgpa,
            "backlogs": s_backlogs,
            "skills": s_skills,
            "is_eligible": is_eligible,
            "fit_score": fit_score,
            "matched_skills": matched_skills,
            "missing_skills": missing_skills,
            "eligibility_reasons": {
                "department_ok": is_dept_eligible,
                "cgpa_ok": cgpa_ok,
                "backlogs_ok": backlogs_ok
            }
        })
        
    # Sort recommendations by eligibility first, then by fit_score descending
    recommended.sort(key=lambda x: (1 if x["is_eligible"] else 0, x["fit_score"]), reverse=True)
    
    return jsonify({
        "company_name": company["name"],
        "required_cgpa": min_cgpa,
        "allowed_backlogs": allowed_backlogs,
        "company_skills": company_keywords,
        "recommendations": recommended
    })


@ai_bp.route("/interview-prep", methods=["POST"])
@token_required()
def interview_prep():
    """AI Interview Prep: generate technical and HR interview questions based on candidate profile."""
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
            
    company_name = "Interviewer"
    if company_id:
        cur.execute("SELECT name, job_role FROM companies WHERE id = %s", (company_id,))
        c = cur.fetchone()
        if c:
            company_name = c["name"]
            if c["job_role"]:
                job_role = c["job_role"]
                
    skills_str = ", ".join(skills_list) if skills_list else "General IT, Coding, Web Development"
    
    # 1. Try Gemini
    if gemini_model:
        try:
            prompt = f"""
            You are a senior technical interviewer at {company_name} hiring for a '{job_role}' role.
            Generate a set of interview prep questions for {student_name} who lists these skills: {skills_str}.
            
            Return a JSON object with:
            1. "role" (string: the job role)
            2. "technical_questions" (list of objects with "question" and "suggested_answer" keys)
            3. "hr_questions" (list of objects with "question" and "tip" keys)
            
            Ensure questions are highly tailored. Limit to 3 technical and 2 HR/Behavioral questions.
            Strictly return JSON only.
            """
            response = gemini_model.generate_content(prompt)
            clean_json = response.text.replace("```json", "").replace("```", "").strip()
            return jsonify(json.loads(clean_json))
        except Exception as e:
            print(f"Gemini prep questions failed, falling back: {e}")

    # 2. Fallback Question Generator
    # Default library
    tech_questions = []
    if any(sk.lower() in ["python", "django", "flask"] for sk in skills_list):
        tech_questions.append({
            "question": "What is the difference between list and tuple in Python, and when would you use a generator?",
            "suggested_answer": "Lists are mutable, whereas tuples are immutable. Generators are memory-efficient because they yield items one at a time using 'yield' rather than loading the entire sequence into RAM."
        })
    if any(sk.lower() in ["javascript", "react", "vue"] for sk in skills_list):
        tech_questions.append({
            "question": "Explain the concept of closures in JavaScript and how closures are utilized in React hook dependency arrays.",
            "suggested_answer": "A closure is a function that remembers its outer variables even after the outer function has finished executing. In React, handlers capture state variables; if they aren't listed in useEffect dependencies, they form stale closures."
        })
    if any(sk.lower() in ["sql", "postgresql", "mysql"] for sk in skills_list):
        tech_questions.append({
            "question": "What is database normalization, and when would you choose to denormalize tables in PostgreSQL?",
            "suggested_answer": "Normalization organizes tables to reduce redundancy and dependencies. Denormalization is done for performance reasons (like fast reads) in highly read-intensive analytics operations, reducing JOIN costs."
        })
        
    # Standard fallback questions if none matched
    if len(tech_questions) < 3:
        tech_questions.append({
            "question": "Can you explain the difference between a REST API and GraphQL, and how you handle HTTP session authentication?",
            "suggested_answer": "REST uses distinct URLs for resources and returns fixed structures. GraphQL has a single endpoint and allows clients to query exact structures. Authentication is typically handled via HTTP-only cookies or Bearer JWT tokens."
        })
    if len(tech_questions) < 3:
        tech_questions.append({
            "question": "What is time complexity, and how would you optimize an O(N^2) double-loop search algorithm to O(N)?",
            "suggested_answer": "Time complexity measures execution growth. An O(N^2) double loop can often be optimized to O(N) by using a hash map or hash set to search items in O(1) constant time, trading space complexity for speed."
        })
        
    hr_questions = [
        {
            "question": f"Why do you want to join {company_name} specifically, and how do you align with our core values?",
            "tip": f"Research {company_name}'s recent projects, tech stack, and company culture. Discuss how your student projects show active learning and accountability."
        },
        {
            "question": "Tell me about a time you faced a technical disagreement in a project group. How did you resolve it?",
            "tip": "Use the STAR method (Situation, Task, Action, Result). Focus on data-driven decisions, compromise, and keeping team velocity high."
        }
    ]
    
    return jsonify({
        "role": job_role,
        "technical_questions": tech_questions[:3],
        "hr_questions": hr_questions
    })


@ai_bp.route("/chatbot", methods=["POST"])
@token_required()
def chatbot():
    """AI Chatbot: answers questions about placements, students, and eligibility."""
    data = request.get_json(force=True) or {}
    query = data.get("query", "").strip()
    if not query:
        return jsonify({"error": "query required"}), 400

    cur = get_cursor()
    query_lower = query.lower()
    
    # NLP parser: detect if query seeks specific live database metrics
    db_response = None
    
    try:
        # Query 1: Highest package
        if "highest package" in query_lower or "max package" in query_lower:
            cur.execute(
                """SELECT MAX(p.package_amount) AS max_pkg, comp.name AS company_name, s.name AS student_name
                   FROM placements p
                   JOIN companies comp ON comp.id = p.company_id
                   JOIN students s ON s.id = p.student_id
                   WHERE p.package_amount IS NOT NULL
                   GROUP BY comp.name, s.name
                   ORDER BY max_pkg DESC LIMIT 1"""
            )
            row = cur.fetchone()
            if row:
                db_response = f"The highest package recorded is **₹{float(row['max_pkg'])} LPA**, offered by **{row['company_name']}** to **{row['student_name']}**."
            else:
                db_response = "No placement packages have been recorded in the database yet."

        # Query 2: Placement percentage
        elif "placement percentage" in query_lower or "placement rate" in query_lower:
            cur.execute("SELECT COUNT(*) AS total FROM students")
            total = cur.fetchone()["total"]
            cur.execute("SELECT COUNT(*) AS placed FROM students WHERE placement_status IN ('selected', 'joined')")
            placed = cur.fetchone()["placed"]
            
            if total > 0:
                pct = round((placed / total) * 100, 2)
                db_response = f"The current institutional placement rate is **{pct}%** ({placed} out of {total} registered students are placed)."
            else:
                db_response = "There are no student records in the database to calculate placement rates."

        # Query 3: Placed count / Unplaced count
        elif "how many students" in query_lower or "placed count" in query_lower:
            if "unplaced" in query_lower or "not placed" in query_lower:
                cur.execute("SELECT COUNT(*) AS n FROM students WHERE placement_status = 'not_placed'")
                n = cur.fetchone()["n"]
                db_response = f"There are currently **{n} unplaced students** registered in the database."
            elif "placed" in query_lower or "selected" in query_lower:
                cur.execute("SELECT COUNT(*) AS n FROM students WHERE placement_status IN ('selected', 'joined')")
                n = cur.fetchone()["n"]
                db_response = f"A total of **{n} students have been successfully placed** this session."
            else:
                cur.execute("SELECT COUNT(*) AS n FROM students")
                n = cur.fetchone()["n"]
                db_response = f"There is a total of **{n} students** registered in the placement portal."

        # Query 4: Total Companies
        elif "total companies" in query_lower or "how many companies" in query_lower:
            cur.execute("SELECT COUNT(*) AS n FROM companies")
            n = cur.fetchone()["n"]
            db_response = f"There are **{n} recruiting companies** listed in the directory."

        # Query 5: Students with specific skills (e.g. Java, Python)
        elif "students with" in query_lower or "who knows" in query_lower or "skill" in query_lower:
            # Try to extract skill
            match = re.search(r"(?:with|knows|know|skills?)\s+([a-zA-Z\+\#\s\-\.\/]+)", query_lower)
            if match:
                target_skill = match.group(1).strip()
                cur.execute(
                    "SELECT name, register_number, skills FROM students WHERE skills ILIKE %s LIMIT 5",
                    (f"%{target_skill}%",)
                )
                rows = cur.fetchall()
                if rows:
                    list_str = "\n".join([f"- **{r['name']}** ({r['register_number']}) — *Skills: {r['skills']}*" for r in rows])
                    db_response = f"Here are some students matching the skill '**{target_skill}**':\n{list_str}"
                else:
                    db_response = f"I could not find any students with the skill '**{target_skill}**' in their profiles."

        # Query 6: Check eligibility for a company (e.g., "eligible for Wipro")
        elif "eligible for" in query_lower:
            match = re.search(r"eligible for\s+([a-zA-Z0-9\s]+)", query_lower)
            if match:
                company_name = match.group(1).strip()
                cur.execute("SELECT id, name, min_cgpa, allowed_backlogs FROM companies WHERE name ILIKE %s LIMIT 1", (f"%{company_name}%",))
                comp = cur.fetchone()
                if comp:
                    min_cgpa = float(comp["min_cgpa"]) if comp["min_cgpa"] else 0.0
                    backlogs = int(comp["allowed_backlogs"]) if comp["allowed_backlogs"] is not None else 99
                    cur.execute(
                        "SELECT COUNT(*) AS n FROM students WHERE cgpa >= %s AND backlogs <= %s",
                        (min_cgpa, backlogs)
                    )
                    cnt = cur.fetchone()["n"]
                    db_response = f"There are **{cnt} students eligible** for **{comp['name']}** drive (requires CGPA >= {min_cgpa} and backlogs <= {backlogs})."
                else:
                    db_response = f"I could not find any active company named '**{company_name}**' in our records."
                    
    except Exception as e:
        print(f"Chatbot database lookup failed: {e}")
        db_response = f"Error querying placement records: {e}"

    # If the user asked a factual question that we answered from the DB, return it!
    if db_response:
        return jsonify({
            "response": db_response,
            "source": "database"
        })

    # If it is a generic query and Gemini is available, use LLM
    if gemini_model:
        try:
            # Let's retrieve simple placement summary counts to give context to Gemini
            cur.execute("SELECT COUNT(*) AS total FROM students")
            tot = cur.fetchone()["total"]
            cur.execute("SELECT COUNT(*) AS placed FROM students WHERE placement_status IN ('selected','joined')")
            pl = cur.fetchone()["placed"]
            cur.execute("SELECT COUNT(*) AS comp FROM companies")
            co = cur.fetchone()["comp"]
            
            context = f"""
            You are 'Placement Pro AI Assistant', a helpful portal assistant.
            Portal Stats: Total Students = {tot}, Placed Students = {pl}, Active Recruiting Partners = {co}.
            
            Answer the user's question concisely in markdown. If the question is about statistics, refer to the Portal Stats.
            Keep responses professional, polite, and placement-focused.
            """
            
            response = gemini_model.generate_content([
                {"role": "user", "parts": [f"System Context: {context}\n\nUser Question: {query}"]}
            ])
            return jsonify({
                "response": response.text.strip(),
                "source": "gemini"
            })
        except Exception as e:
            print(f"Gemini chat failed, using fallback responses: {e}")

    # Generic Fallback Answers
    response_text = "I am the Placement Pro Portal AI Assistant. "
    if "hello" in query_lower or "hi" in query_lower:
        response_text += "Hello! How can I assist you with student directory metrics, eligibility calculations, or placement reports today?"
    elif "help" in query_lower:
        response_text += "You can ask me questions like:\n- *What is the current placement percentage?*\n- *Who has the highest package?*\n- *How many students are placed?*\n- *Show me students with Python skills*"
    else:
        response_text += "I didn't quite catch that. Try asking about **placement percentage**, **highest package**, or search for students with specific skills (e.g. *show me students with React skills*)."
        
    return jsonify({
        "response": response_text,
        "source": "fallback"
    })
