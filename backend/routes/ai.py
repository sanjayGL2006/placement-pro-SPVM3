"""AI routes module providing resume analysis and placement utilities.
"""
# pyrefly: ignore [parse-error, unknown-name, untyped-import]
import concurrent.futures
# pyrefly: ignore [untyped-import]
import dateutil.parser.isoparser
# pyrefly: ignore [parse-error, unknown-name]
"""
Provides several endpoints under the 'ai' Blueprint.
"""
import os
import re
import json
from flask import Blueprint, request, jsonify
from ..database import get_cursor
from .auth import token_required

ai_bp = Blueprint("ai", __name__)

# Gemini API disabled - using Request URL
# pyrefly: ignore [parse-error, unknown-name]
# Local rule-based NLP and database SQL engine
gemini_model = None


def extract_skills_from_text(text):
    """
    Return a JSON object with this exact structure:
    {{
      "section1_ats": {{
        "ats_score": <int 0-100>,
        "keyword_optimization": [
          # pyrefly: ignore [parse-error]
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
    # pyrefly: ignore [unbound-name]
    {text}

    # pyrefly: ignore [invalid-syntax, parse-error, unknown-name]
    Strictly return JSON only, no markdown wrappers, no backticks.
    """


    # 1. Extract skills


    # 4. ATS Score calculation


# pyrefly: ignore [parse-error, unknown-name]
@ai_bp.route("/interview-prep", methods=["POST"])
# pyrefly: ignore [unknown-name]
@token_required()
def interview_prep():
    """AI Interview Prep: generate technical and HR interview questions based on candidate profile."""
    # pyrefly: ignore [unknown-name]
    data = request.get_json(force=True) or {}
    student_id = data.get("student_id")
    company_id = data.get("company_id")
    job_role = data.get("job_role", "Software Engineer")
    
    cur = get_cursor()
    student_name = "Candidate"
    skills_list = []
    
    if student_id:
        # pyrefly: ignore [missing-attribute]
        cur.execute("SELECT name, skills FROM students WHERE id = %s", (student_id,))
        # pyrefly: ignore [missing-attribute]
        s = cur.fetchone()
        if s:
            student_name = s["name"]
            skills_list = [sk.strip() for sk in (s["skills"] or "").split(",") if sk.strip()]
            
    company_name = "Interviewer"
    if company_id:
        # pyrefly: ignore [missing-attribute]
        cur.execute("SELECT name, job_role FROM companies WHERE id = %s", (company_id,))
        # pyrefly: ignore [missing-attribute]
        c = cur.fetchone()
        if c:
            company_name = c["name"]
            if c["job_role"]:
                job_role = c["job_role"]
                
    skills_str = ", ".join(skills_list) if skills_list else "General IT, Coding, Web Development"
    
    # 1. Try Gemini
    # pyrefly: ignore [unknown-name]
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
            # pyrefly: ignore [unknown-name]
            response = gemini_model.generate_content(prompt)
            clean_json = response.text.replace("```json", "").replace("```", "").strip()
            # pyrefly: ignore [unknown-name]
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
    
    # pyrefly: ignore [unknown-name]
    return jsonify({
        "role": job_role,
        "technical_questions": tech_questions[:3],
        "hr_questions": hr_questions
    })


# pyrefly: ignore [unknown-name]
@ai_bp.route("/chatbot", methods=["POST"])
# pyrefly: ignore [unknown-name]
@token_required()
def chatbot():
    """AI Chatbot: answers questions about placements, students, and eligibility."""
    # pyrefly: ignore [unknown-name]
    data = request.get_json(force=True) or {}
    query = data.get("query", "").strip()
    if not query:
        # pyrefly: ignore [unknown-name]
        return jsonify({"error": "query required"}), 400

    cur = get_cursor()
    query_lower = query.lower()
    
    # NLP parser: detect if query seeks specific live database metrics
    db_response = None
    
    try:
        # Query 1: Highest package
        if "highest package" in query_lower or "max package" in query_lower:
            # pyrefly: ignore [missing-attribute]
            cur.execute(
                """SELECT MAX(p.package_amount) AS max_pkg, comp.name AS company_name, s.name AS student_name
                   FROM placements p
                   JOIN companies comp ON comp.id = p.company_id
                   JOIN students s ON s.id = p.student_id
                   WHERE p.package_amount IS NOT NULL
                   GROUP BY comp.name, s.name
                   ORDER BY max_pkg DESC LIMIT 1"""
            )
            # pyrefly: ignore [missing-attribute]
            row = cur.fetchone()
            if row:
                db_response = f"The highest package recorded is **₹{float(row['max_pkg'])} LPA**, offered by **{row['company_name']}** to **{row['student_name']}**."
            else:
                db_response = "No placement packages have been recorded in the database yet."

        # Query 2: Placement percentage
        elif "placement percentage" in query_lower or "placement rate" in query_lower:
            # pyrefly: ignore [missing-attribute]
            cur.execute("SELECT COUNT(*) AS total FROM students")
            # pyrefly: ignore [missing-attribute]
            total = cur.fetchone()["total"]
            # pyrefly: ignore [missing-attribute]
            cur.execute("SELECT COUNT(*) AS placed FROM students WHERE placement_status IN ('selected', 'joined')")
            # pyrefly: ignore [missing-attribute]
            placed = cur.fetchone()["placed"]
            
            if total > 0:
                pct = round((placed / total) * 100, 2)
                db_response = f"The current institutional placement rate is **{pct}%** ({placed} out of {total} registered students are placed)."
            else:
                db_response = "There are no student records in the database to calculate placement rates."

        # Query 3: Placed count / Unplaced count
        elif "how many students" in query_lower or "placed count" in query_lower:
            if "unplaced" in query_lower or "not placed" in query_lower:
                # pyrefly: ignore [missing-attribute]
                cur.execute("SELECT COUNT(*) AS n FROM students WHERE placement_status = 'not_placed'")
                # pyrefly: ignore [missing-attribute]
                n = cur.fetchone()["n"]
                db_response = f"There are currently **{n} unplaced students** registered in the database."
            elif "placed" in query_lower or "selected" in query_lower:
                # pyrefly: ignore [missing-attribute]
                cur.execute("SELECT COUNT(*) AS n FROM students WHERE placement_status IN ('selected', 'joined')")
                # pyrefly: ignore [missing-attribute]
                n = cur.fetchone()["n"]
                db_response = f"A total of **{n} students have been successfully placed** this session."
            else:
                # pyrefly: ignore [missing-attribute]
                cur.execute("SELECT COUNT(*) AS n FROM students")
                # pyrefly: ignore [missing-attribute]
                n = cur.fetchone()["n"]
                db_response = f"There is a total of **{n} students** registered in the placement portal."

        # Query 4: Total Companies
        elif "total companies" in query_lower or "how many companies" in query_lower:
            # pyrefly: ignore [missing-attribute]
            cur.execute("SELECT COUNT(*) AS n FROM companies")
            # pyrefly: ignore [missing-attribute]
            n = cur.fetchone()["n"]
            db_response = f"There are **{n} recruiting companies** listed in the directory."

        # Query 5: Students with specific skills (e.g. Java, Python)
        elif "students with" in query_lower or "who knows" in query_lower or "skill" in query_lower:
            # Try to extract skill
            # pyrefly: ignore [unknown-name]
            # pyrefly: ignore [unknown-name]
            # pyrefly: ignore [unknown-name]
            match = re.search(r"(?:with|knows|know|skills?)\s+([a-zA-Z\+\#\s\-\.\/]+)", query_lower)
            if match:
                target_skill = match.group(1).strip()
                # pyrefly: ignore [missing-attribute]
                cur.execute(
                    "SELECT name, register_number, skills FROM students WHERE skills ILIKE %s LIMIT 5",
                    (f"%{target_skill}%",)
                )
                # pyrefly: ignore [missing-attribute]
                rows = cur.fetchall()
                if rows:
                    list_str = "\n".join([f"- **{r['name']}** ({r['register_number']}) — *Skills: {r['skills']}*" for r in rows])
                    db_response = f"Here are some students matching the skill '**{target_skill}**':\n{list_str}"
                else:
                    db_response = f"I could not find any students with the skill '**{target_skill}**' in their profiles."

        # Query 6: Check eligibility for a company (e.g., "eligible for Wipro")
        elif "eligible for" in query_lower:
            # pyrefly: ignore [unknown-name]
            match = re.search(r"eligible for\s+([a-zA-Z0-9\s]+)", query_lower)
            if match:
                company_name = match.group(1).strip()
                # pyrefly: ignore [missing-attribute]
                cur.execute("SELECT id, name, min_cgpa, allowed_backlogs FROM companies WHERE name ILIKE %s LIMIT 1", (f"%{company_name}%",))
                # pyrefly: ignore [missing-attribute]
                comp = cur.fetchone()
                if comp:
                    min_cgpa = float(comp["min_cgpa"]) if comp["min_cgpa"] else 0.0
                    backlogs = int(comp["allowed_backlogs"]) if comp["allowed_backlogs"] is not None else 99
                    # pyrefly: ignore [missing-attribute]
                    cur.execute(
                        "SELECT COUNT(*) AS n FROM students WHERE cgpa >= %s AND backlogs <= %s",
                        (min_cgpa, backlogs)
                    )
                    # pyrefly: ignore [missing-attribute]
                    cnt = cur.fetchone()["n"]
                    db_response = f"There are **{cnt} students eligible** for **{comp['name']}** drive (requires CGPA >= {min_cgpa} and backlogs <= {backlogs})."
                else:
                    db_response = f"I could not find any active company named '**{company_name}**' in our records."
                    
    except Exception as e:
        print(f"Chatbot database lookup failed: {e}")
        db_response = f"Error querying placement records: {e}"

    # If the user asked a factual question that we answered from the DB, return it!
    if db_response:
        # pyrefly: ignore [unknown-name]
        return jsonify({
            "response": db_response,
            "source": "database"
        })

    # If it is a generic query and Gemini is available, use LLM
    class gemini_model:
        pass
    # pyrefly: ignore [redundant-condition]
    if gemini_model:
        try:
            # Let's retrieve simple placement summary counts to give context to Gemini
            # pyrefly: ignore [missing-attribute]
            cur.execute("SELECT COUNT(*) AS total FROM students")
            # pyrefly: ignore [missing-attribute]
            tot = cur.fetchone()["total"]
            # pyrefly: ignore [missing-attribute]
            cur.execute("SELECT COUNT(*) AS placed FROM students WHERE placement_status IN ('selected','joined')")
            # pyrefly: ignore [missing-attribute]
            pl = cur.fetchone()["placed"]
            # pyrefly: ignore [missing-attribute]
            cur.execute("SELECT COUNT(*) AS comp FROM companies")
            # pyrefly: ignore [missing-attribute]
            co = cur.fetchone()["comp"]
            
            context = f"""
            You are 'Placement Pro AI Assistant', a helpful portal assistant.
            Portal Stats: Total Students = {tot}, Placed Students = {pl}, Active Recruiting Partners = {co}.
            
            Answer the user's question concisely in markdown. If the question is about statistics, refer to the Portal Stats.
            Keep responses professional, polite, and placement-focused.
            """
            
            # pyrefly: ignore [missing-attribute]
            response = gemini_model.generate_content([
                {"role": "user", "parts": [f"System Context: {context}\n\nUser Question: {query}"]}
            ])
            # pyrefly: ignore [unknown-name]
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
        
    # pyrefly: ignore [unknown-name]
    return jsonify({
        "response": response_text,
        "source": "fallback"
    })
