import pymysql
import os

host = os.environ.get("DB_HOST", "localhost")
port = int(os.environ.get("DB_PORT", 3306))
user = os.environ.get("DB_USER", "PESIams")
password = os.environ.get("DB_PASSWORD", "password")
db_name = "PESIams placement"

print(f"Connecting to MySQL on {host}:{port} as {user}...")

# First connect without database to create it if it doesn't exist
conn = pymysql.connect(
    host=host, port=port, user=user, password=password, charset='utf8mb4'
)
try:
    with conn.cursor() as cur:
        # Create DB if not exists (using backticks for spaces in name)
        cur.execute(f"CREATE DATABASE IF NOT EXISTS `{db_name}`")
    conn.commit()
finally:
    conn.close()

# Now connect to the database to apply schema
print(f"Applying schema to database `{db_name}`...")
conn = pymysql.connect(
    host=host, port=port, user=user, password=password, database=db_name, charset='utf8mb4'
)

with open("schema.sql", "r") as f:
    sql_script = f.read()

try:
    with conn.cursor() as cur:
        # Split script into individual statements
        statements = sql_script.split(";")
        for statement in statements:
            if statement.strip():
                try:
                    cur.execute(statement)
                except Exception as e:
                    print(f"Error executing statement:\n{statement}")
                    print(f"Error: {e}")
    conn.commit()
    print("Schema applied successfully!")
finally:
    conn.close()
