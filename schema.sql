-- ============================================================
-- Placement Pro — MySQL Schema
-- ============================================================

CREATE TABLE IF NOT EXISTS users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(120) NOT NULL,
    email           VARCHAR(150) UNIQUE NOT NULL,
    password_hash   VARCHAR(255) NOT NULL,
    role            VARCHAR(20) NOT NULL DEFAULT 'faculty', -- 'hr' | 'faculty' | 'admin'
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS departments (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(150) UNIQUE NOT NULL   -- e.g. 'BCA', 'BBA', 'BBA - Hospitality & Hotel Management'
);

CREATE TABLE IF NOT EXISTS courses (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    department_id   INT,
    name            VARCHAR(150) NOT NULL,          -- e.g. 'B.Sc - Computer Science'
    stream          VARCHAR(100),                   -- e.g. 'Computer Science', 'Physics', 'Chemistry'
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS students (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    register_number     VARCHAR(50) UNIQUE NOT NULL,
    name                VARCHAR(150) NOT NULL,
    department_id       INT,
    course_id           INT,
    section             VARCHAR(10),
    academic_year       VARCHAR(20),
    gender              VARCHAR(20),
    date_of_birth       DATE,
    mobile_number       VARCHAR(20),
    email               VARCHAR(150),
    address             TEXT,
    cgpa                DECIMAL(4,2),
    percentage          DECIMAL(5,2),
    backlogs            INT DEFAULT 0,
    skills              TEXT,                       -- comma-separated
    resume_link         TEXT,
    placement_status    VARCHAR(30) DEFAULT 'not_placed', -- not_placed | applied | selected | joined
    eligible_status     BOOLEAN DEFAULT TRUE,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (course_id) REFERENCES courses(id),
    INDEX idx_students_register_number (register_number),
    INDEX idx_students_department (department_id),
    INDEX idx_students_placement_status (placement_status)
);

CREATE TABLE IF NOT EXISTS companies (
    id                    INT AUTO_INCREMENT PRIMARY KEY,
    name                  VARCHAR(150) NOT NULL,
    industry              VARCHAR(100),
    state                 VARCHAR(100),
    location              VARCHAR(150),
    hr_name               VARCHAR(150),
    hr_email              VARCHAR(150),
    hr_contact_number     VARCHAR(20),
    visit_date            DATE,
    package_amount        DECIMAL(12,2),
    min_package           DECIMAL(12,2),
    max_package           DECIMAL(12,2),
    avg_package           DECIMAL(12,2),
    eligible_departments  TEXT,                     -- comma-separated department names
    min_cgpa              DECIMAL(4,2),
    allowed_backlogs      INT DEFAULT 0,
    hiring_count          INT DEFAULT 0,
    logo_url              TEXT,
    job_role              VARCHAR(150),
    venue                 VARCHAR(150),
    time                  TIME,
    last_date             DATE,
    reminder_sent         BOOLEAN DEFAULT FALSE,
    created_at            DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at            DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE (name, visit_date),
    INDEX idx_companies_name (name)
);

-- Links a student to a company they were selected by / applied to
CREATE TABLE IF NOT EXISTS placements (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    student_id          INT,
    company_id          INT,
    package_amount      DECIMAL(12,2),
    selection_date      DATE,
    offer_status        VARCHAR(30) DEFAULT 'pending', -- pending | offered | accepted | declined
    offer_letter_date   DATE,
    joining_date        DATE,
    current_stage       VARCHAR(40) DEFAULT 'registered',
    drive_status        VARCHAR(20) DEFAULT 'INTERESTED', -- INTERESTED | IN_PROCESSING | PLACED | UNPLACED
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE (student_id, company_id),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_placements_student (student_id),
    INDEX idx_placements_company (company_id)
);

-- Placement pipeline timeline events per student/company
CREATE TABLE IF NOT EXISTS pipeline_stages (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    placement_id    INT,
    stage           VARCHAR(40) NOT NULL,   -- registration|eligibility_verification|applied|aptitude_test|
                                             -- technical_test|group_discussion|hr_interview|selected|
                                             -- offer_letter_received|joined_company
    status          VARCHAR(20) DEFAULT 'pending', -- pending | in_progress | completed | failed
    stage_date      DATE,
    remarks         TEXT,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (placement_id) REFERENCES placements(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS import_history (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    imported_by     INT,
    import_type     VARCHAR(20) NOT NULL,   -- 'student' | 'company'
    file_name       VARCHAR(255),
    total_rows      INT DEFAULT 0,
    inserted_count  INT DEFAULT 0,
    updated_count   INT DEFAULT 0,
    skipped_count   INT DEFAULT 0,
    error_count     INT DEFAULT 0,
    error_log       JSON,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (imported_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS audit_logs (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT,
    action          VARCHAR(100) NOT NULL,
    entity_type     VARCHAR(50),
    entity_id       INT,
    details         JSON,
    ip_address      VARCHAR(50),
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS notifications (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(150) NOT NULL,
    message         TEXT NOT NULL,
    type            VARCHAR(30) DEFAULT 'info', -- 'info' | 'success' | 'warning' | 'danger'
    is_read         BOOLEAN DEFAULT FALSE,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_notifications_created_at (created_at)
);

CREATE TABLE IF NOT EXISTS student_documents (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    student_id      INT,
    placement_id    INT,
    doc_type        VARCHAR(50) DEFAULT 'OTHER', -- RESUME | COVER_LETTER | CERTIFICATE | OTHER
    filename        VARCHAR(255) NOT NULL,
    original_name   VARCHAR(255) NOT NULL,
    file_size_bytes INT NOT NULL,
    uploaded_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (placement_id) REFERENCES placements(id) ON DELETE SET NULL
);

-- Seed departments/courses
INSERT IGNORE INTO departments (name) VALUES
    ('BCA'), ('BBA'), ('BBA - Hospitality & Hotel Management'), ('B.Com'), ('B.Sc');

INSERT IGNORE INTO courses (department_id, name, stream)
SELECT d.id, 'B.Sc - Computer Science', 'Computer Science' FROM departments d WHERE d.name = 'B.Sc';

INSERT IGNORE INTO courses (department_id, name, stream)
SELECT d.id, 'B.Sc - Physics', 'Physics' FROM departments d WHERE d.name = 'B.Sc';

INSERT IGNORE INTO courses (department_id, name, stream)
SELECT d.id, 'B.Sc - Chemistry', 'Chemistry' FROM departments d WHERE d.name = 'B.Sc';
