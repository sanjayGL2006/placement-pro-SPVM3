# pyrefly: ignore [missing-import]
import sqlite3
# pyrefly: ignore [missing-import]
from argon2 import PasswordHasher

ph = PasswordHasher()

conn = sqlite3.connect('backend/placement_pro.db')
cur = conn.cursor()

# 1. Add department_id to users if not present
cur.execute("PRAGMA table_info(users)")
cols = [c[1] for c in cur.fetchall()]
if 'department_id' not in cols:
    cur.execute("ALTER TABLE users ADD COLUMN department_id INTEGER REFERENCES departments(id)")
    print("Added department_id to users table.")

# 2. Create department_access_codes table
cur.execute("""
CREATE TABLE IF NOT EXISTS department_access_codes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    department_id INTEGER UNIQUE NOT NULL,
    access_code TEXT NOT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_by INTEGER DEFAULT 1,
    FOREIGN KEY (department_id) REFERENCES departments(id)
)
""")
print("Ensured department_access_codes table exists.")

# 3. Ensure departments
depts = [
    (1, 'BCA'),
    (2, 'BBA'),
    (3, 'BBA - Hospitality & Hotel Management'),
    (4, 'B.Com'),
    (5, 'B.Sc')
]
for did, dname in depts:
    cur.execute("INSERT OR IGNORE INTO departments (id, name) VALUES (?, ?)", (did, dname))

# 4. Seed access codes
codes = [
    (1, 'PES-BCA-2026'),
    (2, 'PES-BBA-2026'),
    (3, 'PES-BHM-2026'),
    (4, 'PES-BCOM-2026'),
    (5, 'PES-BSC-2026'),
]
for did, code in codes:
    cur.execute("""
        INSERT INTO department_access_codes (department_id, access_code) 
        VALUES (?, ?)
        ON CONFLICT(department_id) DO UPDATE SET access_code = excluded.access_code
    """, (did, code))

# 5. Seed authorized institutional accounts
seed_users = [
    ("Dr. Principal", "principal@pesiams.edu.in", "Principal@2026", "principal", None),
    ("Placement Coordinator", "coordinator@pesiams.edu.in", "Coordinator@2026", "coordinator", None),
    ("BCA Department Staff", "staff.bca@pesiams.edu.in", "Staff@2026", "staff", 1),
    ("B.Sc Department Staff", "staff.bsc@pesiams.edu.in", "Staff@2026", "staff", 5),
    ("SPVM3 Tech Solution by Sanjay G L", "admin@college.edu", "Coordinator@2026", "coordinator", None)
]

for name, email, raw_pwd, role, dept_id in seed_users:
    cur.execute("SELECT id FROM users WHERE LOWER(email) = LOWER(?)", (email,))
    row = cur.fetchone()
    pwd_hash = ph.hash(raw_pwd)
    if row:
        cur.execute("""
            UPDATE users 
            SET name = ?, password_hash = ?, role = ?, department_id = ?, is_active = 1
            WHERE id = ?
        """, (name, pwd_hash, role, dept_id, row[0]))
        print(f"Updated account: {email}")
    else:
        cur.execute("""
            INSERT INTO users (name, email, password_hash, role, department_id, is_active)
            VALUES (?, ?, ?, ?, ?, 1)
        """, (name, email, pwd_hash, role, dept_id))
        print(f"Created account: {email}")

conn.commit()
conn.close()
print("RBAC Migration completed successfully.")
