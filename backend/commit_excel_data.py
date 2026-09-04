#!/usr/bin/env python3
"""Bulk import student data from Excel or CSV files.

Usage:
    python commit_excel_data.py --file path/to/data.xlsx [--batch-size 100]

The script reads the given file, builds a preview using the existing
`build_preview` helper, and inserts/updates student records in a single
transaction. It supports Excel (`.xlsx`, `.xls`) and CSV (`.csv`) formats.
If the file contains more than ``batch_size`` rows, the data are processed
in chunks to avoid excessively large statements while still committing
once per chunk.
"""

import sys
import os
import argparse
from pathlib import Path

import pandas as pd
# pyrefly: ignore [untyped-import]
import psycopg2
# pyrefly: ignore [untyped-import]
from psycopg2.extras import RealDictCursor, execute_batch

# Add backend to sys.path for local imports
sys.path.append(r"c:\Users\Sanjay G L\Desktop\placement-pro\backend")

from import_utils import build_preview

def parse_args():
    parser = argparse.ArgumentParser(description="Import students from Excel or CSV.")
    parser.add_argument(
        "--file",
        required=False,
        default=r"C:\Users\Sanjay G L\OneDrive\Documents\Book26.xlsx",
        help="Path to the Excel or CSV file containing student data.",
    )
    parser.add_argument(
        "--batch-size",
        type=int,
        default=100,
        help="Maximum number of rows to process per DB transaction.",
    )
    return parser.parse_args()

def load_dataframe(file_path: str) -> pd.DataFrame:
    """Load an Excel or CSV file into a pandas DataFrame.

    The function determines the file type based on the extension.
    """
    ext = Path(file_path).suffix.lower()
    if ext in {".xlsx", ".xls"}:
        return pd.read_excel(file_path)
    elif ext == ".csv":
        return pd.read_csv(file_path)
    else:
        raise ValueError(f"Unsupported file extension: {ext}. Use .xlsx, .xls, or .csv")

def main():
    args = parse_args()
    file_path = Path(args.file)
    if not file_path.is_file():
        print(f"Error: File not found - {file_path}")
        sys.exit(1)
    df = load_dataframe(str(file_path))

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

    inserted = updated = skipped = errors = 0
    batch = []
    batch_actions = []  # parallel list of "insert" or "update"

    for r in rows:
        data = r["data"]
        reg = data.get("register_number")
        if not reg:
            skipped += 1
            continue

        # Determine whether we need to insert or update
        cur.execute("SELECT id FROM students WHERE register_number = %s", (reg,))
        exists = cur.fetchone()
        action = "update" if exists else "insert"

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
            "eligible_status": True,
        }
        batch.append((fields, action))

        if len(batch) >= args.batch_size:
            # Process current batch
            for fields, action in batch:
                try:
                    if action == "insert":
                        cols = ", ".join(fields.keys())
                        placeholders = ", ".join(["%s"] * len(fields))
                        cur.execute(
                            f"INSERT INTO students ({cols}) VALUES ({placeholders}) ON CONFLICT (register_number) DO NOTHING",
                            list(fields.values()),
                        )
                        inserted += 1
                    else:
                        set_clause = ", ".join(f"{k} = %s" for k in fields if k != "register_number")
                        values = [v for k, v in fields.items() if k != "register_number"]
                        values.append(fields["register_number"])
                        cur.execute(
                            f"UPDATE students SET {set_clause}, updated_at = NOW() WHERE register_number = %s",
                            values,
                        )
                        updated += 1
                    # Auto-link company placement if present
                    company_name = data.get("company")
                    if company_name and fields["placement_status"] in ("selected", "joined"):
                        cur.execute(
                            "SELECT id FROM companies WHERE LOWER(name) = LOWER(%s)",
                            (company_name.strip(),),
                        )
                        crow = cur.fetchone()
                        if crow:
                            company_id = crow["id"]
                        else:
                            cur.execute(
                                "INSERT INTO companies (name, industry) VALUES (%s, 'Others') RETURNING id",
                                (company_name.strip(),),
                            )
                            company_id = cur.fetchone()["id"]

                        cur.execute(
                            "SELECT id FROM students WHERE register_number = %s",
                            (reg,),
                        )
                        srow = cur.fetchone()
                        if srow:
                            student_id = srow["id"]
                            cur.execute(
                                "INSERT INTO placements (student_id, company_id, offer_status, current_stage) "
                                "VALUES (%s, %s, 'accepted', 'selected') "
                                "ON CONFLICT (student_id, company_id) DO NOTHING",
                                (student_id, company_id),
                            )
                except Exception as e:
                    conn.rollback()
                    errors += 1
                    print(f"Error on {reg}: {e}")
            conn.commit()
            batch.clear()

    # Process any remaining rows
    if batch:
        for fields, action in batch:
            try:
                if action == "insert":
                    cols = ", ".join(fields.keys())
                    placeholders = ", ".join(["%s"] * len(fields))
                    cur.execute(
                        f"INSERT INTO students ({cols}) VALUES ({placeholders}) ON CONFLICT (register_number) DO NOTHING",
                        list(fields.values()),
                    )
                    inserted += 1
                else:
                    set_clause = ", ".join(f"{k} = %s" for k in fields if k != "register_number")
                    values = [v for k, v in fields.items() if k != "register_number"]
                    values.append(fields["register_number"])
                    cur.execute(
                        f"UPDATE students SET {set_clause}, updated_at = NOW() WHERE register_number = %s",
                        values,
                    )
                    updated += 1
                # Auto-link company placement if present (same logic as above)
                # pyrefly: ignore [unbound-name]
                company_name = data.get("company")
                if company_name and fields["placement_status"] in ("selected", "joined"):
                    cur.execute(
                        "SELECT id FROM companies WHERE LOWER(name) = LOWER(%s)",
                        (company_name.strip(),),
                    )
                    crow = cur.fetchone()
                    if crow:
                        company_id = crow["id"]
                    else:
                        cur.execute(
                            "INSERT INTO companies (name, industry) VALUES (%s, 'Others') RETURNING id",
                            (company_name.strip(),),
                        )
                        company_id = cur.fetchone()["id"]

                    cur.execute(
                        "SELECT id FROM students WHERE register_number = %s",
                        # pyrefly: ignore [unbound-name]
                        (reg,),
                    )
                    srow = cur.fetchone()
                    if srow:
                        student_id = srow["id"]
                        cur.execute(
                            "INSERT INTO placements (student_id, company_id, offer_status, current_stage) "
                            "VALUES (%s, %s, 'accepted', 'selected') "
                            "ON CONFLICT (student_id, company_id) DO NOTHING",
                            (student_id, company_id),
                        )
            except Exception as e:
                conn.rollback()
                errors += 1
                # pyrefly: ignore [unbound-name]
                print(f"Error on {reg}: {e}")
        conn.commit()

    print("Successfully processed", Path(args.file).name + ":")
    print(f"Inserted: {inserted}")
    print(f"Updated: {updated}")
    print(f"Skipped (no register_number): {skipped}")
    print(f"Errors: {errors}")

if __name__ == "__main__":
    main()
