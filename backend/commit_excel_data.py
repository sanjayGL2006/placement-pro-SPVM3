import sys
import os
import pandas as pd
import psycopg2
from psycopg2.extras import RealDictCursor

# Add backend to sys.path
sys.path.append(r"c:\Users\Sanjay G L\Desktop\placement-pro\backend")

from import_utils import build_preview

excel_path = r"C:\Users\Sanjay G L\OneDrive\Documents\Book26.xlsx"
df = pd.read_excel(excel_path)

preview = build_preview(df, "student")
rows = preview["rows"]

conn = psycopg2.connect("dbname=placement_pro user=postgres password=postgres host=localhost")
cur = conn.cursor(cursor_factory=RealDictCursor)

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
        cur.execute("INSERT INTO departments (name) VALUES (%s) RETURNING id", (name,))
        dept_cache[name] = cur.fetchone()["id"]
    return dept_cache[name]

inserted = 0
updated = 0
skipped = 0
errors = 0

for r in rows:
    data = r["data"]
    reg = data.get("register_number")
    if not reg:
        skipped += 1
        continue
    
    cur.execute("SELECT id FROM students WHERE register_number = %s", (reg,))
    exists = cur.fetchone()
    action = "update" if exists else "insert"
    
    try:
        fields = {
            "register_number": reg,
            "name": data.get("name"),
            "department_id": dept_id(data.get("department")),
            "section": data.get("section"),
            "academic_year": data.get("academic_year") or "2023-2024",
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
            "placement_status": data.get("placement_status") or "unplaced",
            "eligible_status": True
        }
        
        if action == "insert":
            cols = ", ".join(fields.keys())
            placeholders = ", ".join(["%s"] * len(fields))
            cur.execute(
                f"INSERT INTO students ({cols}) VALUES ({placeholders}) ON CONFLICT (register_number) DO NOTHING",
                list(fields.values())
            )
            inserted += 1
        else:
            set_clause = ", ".join(f"{k} = %s" for k in fields if k != "register_number")
            values = [v for k, v in fields.items() if k != "register_number"]
            values.append(fields["register_number"])
            cur.execute(
                f"UPDATE students SET {set_clause}, updated_at = NOW() WHERE register_number = %s",
                values
            )
            updated += 1
            
        # Auto-link company placement if present
        company_name = data.get("company")
        if company_name and fields["placement_status"] in ("selected", "joined"):
            cur.execute("SELECT id FROM companies WHERE LOWER(name) = LOWER(%s)", (company_name.strip(),))
            crow = cur.fetchone()
            if crow:
                company_id = crow["id"]
            else:
                cur.execute("INSERT INTO companies (name, industry) VALUES (%s, 'Others') RETURNING id", (company_name.strip(),))
                company_id = cur.fetchone()["id"]
            
            cur.execute("SELECT id FROM students WHERE register_number = %s", (reg,))
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
        conn.rollback()
        errors += 1
        print(f"Error on {reg}: {e}")

conn.commit()
print(f"Successfully processed Book26.xlsx:")
print(f"Inserted: {inserted}")
print(f"Updated: {updated}")
print(f"Skipped: {skipped}")
print(f"Errors: {errors}")
