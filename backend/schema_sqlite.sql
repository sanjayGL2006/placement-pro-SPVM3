-- ============================================================
-- Placement Pro — SQLite Schema
-- ============================================================

CREATE TABLE IF NOT EXISTS users (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    name            TEXT NOT NULL,
    email           TEXT UNIQUE NOT NULL,
    password_hash   TEXT NOT NULL,
    role            TEXT NOT NULL DEFAULT 'faculty',
    is_active       BOOLEAN NOT NULL DEFAULT 1,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS departments (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    name            TEXT UNIQUE NOT NULL
);

CREATE TABLE IF NOT EXISTS courses (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    department_id   INTEGER,
    name            TEXT NOT NULL,
    stream          TEXT,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS students (
    id                  INTEGER PRIMARY KEY AUTOINCREMENT,
    register_number     TEXT UNIQUE NOT NULL,
    name                TEXT NOT NULL,
    department_id       INTEGER,
    course_id           INTEGER,
    section             TEXT,
    academic_year       TEXT,
    gender              TEXT,
    date_of_birth       DATE,
    mobile_number       TEXT,
    email               TEXT,
    address             TEXT,
    cgpa                REAL,
    percentage          REAL,
    backlogs            INTEGER DEFAULT 0,
    skills              TEXT,
    resume_link         TEXT,
    placement_status    TEXT DEFAULT 'not_placed',
    eligible_status     BOOLEAN DEFAULT 1,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

CREATE TABLE IF NOT EXISTS companies (
    id                    INTEGER PRIMARY KEY AUTOINCREMENT,
    name                  TEXT NOT NULL,
    industry              TEXT,
    state                 TEXT,
    location              TEXT,
    hr_name               TEXT,
    hr_email              TEXT,
    hr_contact_number     TEXT,
    visit_date            DATE,
    package_amount        REAL,
    min_package           REAL,
    max_package           REAL,
    avg_package           REAL,
    eligible_departments  TEXT,
    min_cgpa              REAL,
    allowed_backlogs      INTEGER DEFAULT 0,
    hiring_count          INTEGER DEFAULT 0,
    logo_url              TEXT,
    job_role              TEXT,
    venue                 TEXT,
    time                  TEXT,
    last_date             DATE,
    reminder_sent         BOOLEAN DEFAULT 0,
    created_at            DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at            DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (name, visit_date)
);

CREATE TABLE IF NOT EXISTS placements (
    id                  INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id          INTEGER,
    company_id          INTEGER,
    package_amount      REAL,
    selection_date      DATE,
    offer_status        TEXT DEFAULT 'pending',
    offer_letter_date   DATE,
    joining_date        DATE,
    current_stage       TEXT DEFAULT 'registered',
    drive_status        TEXT DEFAULT 'INTERESTED',
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (student_id, company_id),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS pipeline_stages (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    placement_id    INTEGER,
    stage           TEXT NOT NULL,
    status          TEXT DEFAULT 'pending',
    stage_date      DATE,
    remarks         TEXT,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (placement_id) REFERENCES placements(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS import_history (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    imported_by     INTEGER,
    import_type     TEXT NOT NULL,
    file_name       TEXT,
    total_rows      INTEGER DEFAULT 0,
    inserted_count  INTEGER DEFAULT 0,
    updated_count   INTEGER DEFAULT 0,
    skipped_count   INTEGER DEFAULT 0,
    error_count     INTEGER DEFAULT 0,
    error_log       TEXT,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (imported_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS audit_logs (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id         INTEGER,
    action          TEXT NOT NULL,
    entity_type     TEXT,
    entity_id       INTEGER,
    details         TEXT,
    ip_address      TEXT,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS notifications (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    title           TEXT NOT NULL,
    message         TEXT NOT NULL,
    type            TEXT DEFAULT 'info',
    is_read         BOOLEAN DEFAULT 0,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS student_documents (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id      INTEGER,
    placement_id    INTEGER,
    doc_type        TEXT DEFAULT 'OTHER',
    filename        TEXT NOT NULL,
    original_name   TEXT NOT NULL,
    file_size_bytes INTEGER NOT NULL,
    uploaded_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (placement_id) REFERENCES placements(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS recycle_bin (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    entity_type     TEXT NOT NULL,
    original_id     INTEGER NOT NULL,
    name            TEXT NOT NULL,
    data            TEXT NOT NULL,
    deleted_at      DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Seed departments
INSERT OR IGNORE INTO departments (id, name) VALUES
    (1, 'BCA'), (2, 'BBA'), (3, 'BBA - Hospitality & Hotel Management'), (4, 'B.Com'), (5, 'B.Sc');

INSERT OR IGNORE INTO courses (id, department_id, name, stream) VALUES
    (1, 5, 'B.Sc - Computer Science', 'Computer Science'),
    (2, 5, 'B.Sc - Physics', 'Physics'),
    (3, 5, 'B.Sc - Chemistry', 'Chemistry');

-- Seed Admin User (password: admin123)
INSERT OR IGNORE INTO users (id, name, email, password_hash, role, is_active) VALUES
    (1, 'SPVM3 Tech Solution by Sanjay G L', 'admin@college.edu', '$argon2id$v=19$m=65536,t=3,p=4$oMT4cuTV6jXvmErYy9EMVw$U/B45qAVcz3xQvuz5+0yydtJQXN3eLe8ab70IXtkcsE', 'admin', 1);


-- Seed Companies
INSERT OR IGNORE INTO companies (id, name, industry, location, hr_name, hr_email, hr_contact_number, visit_date, package_amount, min_package, max_package, avg_package, eligible_departments, min_cgpa, allowed_backlogs, hiring_count, logo_url, job_role) VALUES
    (1, 'Goldman Sachs', 'Financial Services', 'Bengaluru', 'Sarah Jenkins', 's.jenkins@gs.com', '+91 9876543210', '2026-09-15', 24.00, 20.00, 28.00, 24.00, 'BCA, B.Sc', 8.0, 0, 15, 'https://logo.clearbit.com/goldmansachs.com', 'Software Engineer'),
    (2, 'Amazon', 'E-Commerce / Cloud', 'Hyderabad', 'Rajesh Sharma', 'rsharma@amazon.com', '+91 9876543211', '2026-09-20', 18.50, 16.00, 22.00, 18.50, 'BCA, B.Sc, BBA', 7.5, 0, 25, 'https://logo.clearbit.com/amazon.com', 'Systems Analyst'),
    (3, 'TCS Digital', 'IT Services', 'Chennai', 'Anita Roy', 'anita.r@tcs.com', '+91 9876543212', '2026-10-01', 7.50, 7.00, 9.00, 7.50, 'BCA, B.Sc, B.Com, BBA', 6.5, 1, 50, 'https://logo.clearbit.com/tcs.com', 'Digital Associate'),
    (4, 'Qualcomm', 'Semiconductors', 'Bengaluru', 'David Miller', 'dmiller@qualcomm.com', '+91 9876543213', '2026-10-10', 16.00, 14.00, 18.00, 16.00, 'B.Sc', 8.0, 0, 10, 'https://logo.clearbit.com/qualcomm.com', 'Hardware Engineer'),
    (5, 'Microsoft', 'Software', 'Hyderabad', 'Karen Gill', 'kgill@microsoft.com', '+91 9876543214', '2026-10-15', 22.00, 18.00, 25.00, 22.00, 'BCA, B.Sc', 8.5, 0, 12, 'https://logo.clearbit.com/microsoft.com', 'Software Engineer'),
    (6, 'Texas Instruments', 'Electronics', 'Bengaluru', 'Arun V', 'arunv@ti.com', '+91 9876543215', '2026-10-22', 15.00, 13.00, 17.00, 15.00, 'B.Sc', 7.5, 0, 8, 'https://logo.clearbit.com/ti.com', 'Embedded Engineer');

-- Seed Students
INSERT OR IGNORE INTO students (id, register_number, name, department_id, course_id, section, academic_year, gender, date_of_birth, mobile_number, email, address, cgpa, percentage, backlogs, skills, placement_status, eligible_status) VALUES
    (1, '21CS042', 'Alex Morgan', 1, 1, 'Section A', '2023-2024', 'Female', '2002-05-14', '9876543201', 'alex.morgan@student.edu', '123 College Rd, City', 9.2, 92.0, 0, 'Python, Java, React', 'selected', 1),
    (2, '21CS108', 'Sophia Chen', 1, 1, 'Section A', '2023-2024', 'Female', '2002-08-22', '9876543202', 'sophia.chen@student.edu', '456 Tech Park, City', 8.9, 89.0, 0, 'C++, Python, AWS', 'selected', 1),
    (3, '21IT019', 'Marcus Vance', 5, 1, 'Section B', '2023-2024', 'Male', '2001-11-03', '9876543203', 'marcus.vance@student.edu', '789 Main St, City', 7.4, 74.0, 1, 'Java, SQL, HTML/CSS', 'applied', 1),
    (4, '21EC085', 'Emily Watson', 5, 2, 'Section C', '2023-2024', 'Female', '2002-02-18', '9876543204', 'emily.watson@student.edu', '321 Elm St, City', 8.7, 87.0, 0, 'Verilog, C, Embedded Systems', 'selected', 1),
    (5, '21CS144', 'David Kim', 1, 1, 'Section B', '2023-2024', 'Male', '2002-07-30', '9876543205', 'david.kim@student.edu', '654 Pine St, City', 6.8, 68.0, 0, 'Python, JavaScript', 'not_placed', 1),
    (6, '21IT072', 'Jessica Taylor', 5, 1, 'Section A', '2023-2024', 'Female', '2001-09-12', '9876543206', 'jessica.taylor@student.edu', '987 Oak Ave, City', 9.1, 91.0, 0, 'Python, ML, Data Science', 'selected', 1),
    (7, '21EC012', 'Ryan Reynolds', 5, 2, 'Section B', '2023-2024', 'Male', '2002-04-05', '9876543207', 'ryan.reynolds@student.edu', '159 Maple Dr, City', 7.8, 78.0, 0, 'C++, MATLAB', 'applied', 1),
    (8, '21CS009', 'Hannah Abbott', 1, 1, 'Section C', '2023-2024', 'Female', '2002-01-25', '9876543208', 'hannah.abbott@student.edu', '753 Cedar Rd, City', 6.5, 65.0, 2, 'HTML, CSS, PHP', 'not_placed', 1),
    (9, '21BCA001', 'Rahul Sharma', 1, 1, 'Section A', '2023-2024', 'Male', '2002-03-15', '9876543209', 'rahul.sharma@student.edu', '852 Birch Ct, City', 8.4, 84.0, 0, 'Java, Spring Boot, MySQL', 'selected', 1),
    (10, '21BBA005', 'Ananya Roy', 2, NULL, 'Section A', '2023-2024', 'Female', '2002-10-10', '9876543210', 'ananya.roy@student.edu', '963 Walnut St, City', 8.1, 81.0, 0, 'Marketing, Analytics, Excel', 'applied', 1);

-- Seed Placements
INSERT OR IGNORE INTO placements (id, student_id, company_id, package_amount, selection_date, offer_status, current_stage, drive_status) VALUES
    (1, 1, 1, 24.00, '2026-08-01', 'accepted', 'joined', 'PLACED'),
    (2, 2, 2, 18.50, '2026-08-02', 'offered', 'selected', 'PLACED'),
    (3, 3, 3, 7.50, NULL, 'pending', 'registered', 'IN_PROCESSING'),
    (4, 4, 4, 16.00, '2026-08-05', 'accepted', 'joined', 'PLACED'),
    (5, 6, 5, 22.00, '2026-08-06', 'offered', 'selected', 'PLACED'),
    (6, 7, 6, 15.00, NULL, 'pending', 'registered', 'IN_PROCESSING'),
    (7, 9, 3, 7.50, '2026-08-08', 'accepted', 'joined', 'PLACED'),
    (8, 10, 2, 18.50, NULL, 'pending', 'registered', 'IN_PROCESSING');

