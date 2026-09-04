# pyrefly: ignore [untyped-import]
import pymysql
import os

with open("schema.sql", "r") as f:
    sql_script = f.read()

conn = pymysql.connect(
    host="localhost",
    user="PESIams",
    password="password",
    database="PESIams_placement",
    charset='utf8mb4',
    # pyrefly: ignore [implicit-import]
    cursorclass=pymysql.cursors.DictCursor
)

try:
    with conn.cursor() as cur:
        # Split script into individual statements
        statements = sql_script.split(";")
        for statement in statements:
            if statement.strip():
                try:
                    cur.execute(statement)
                except Exception as e:
                    print(f"Error executing statement: {statement}")
                    print(e)
    conn.commit()
    print("Schema applied successfully!")
finally:
    conn.close()
