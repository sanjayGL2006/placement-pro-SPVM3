# Walkthrough — Placement Management System Overhaul

I have implemented the complete set of enterprise features, bulk workflows, notification services, and AI portal capabilities. Below is an overview of what was added and instructions on how you can run and verify these features on your local machine.

---

## 1. Database Migrations
- **File modified:** [schema.sql](file:///c:/Users/Sanjay%20G%20L/Desktop/placement-pro/schema.sql)
  - Added new company drive columns: `job_role`, `venue`, `time`, and `last_date` to the `companies` table.
  - Defined the `notifications` table to store system-wide events and read states.
  - Created a database index on `notifications(created_at)` for high performance.
- **File modified:** [setup_db.py](file:///c:/Users/Sanjay%20G%20L/Desktop/placement-pro/backend/setup_db.py)
  - Integrated dynamic migrations. When the initialization script runs, it automatically issues `ALTER TABLE` commands to add the new company columns if they are not already present in your database, preventing existing data loss.

---

## 2. Backend API Services
- **File modified:** [requirements.txt](file:///c:/Users/Sanjay%20G%20L/Desktop/placement-pro/backend/requirements.txt)
  - Added the `google-generativeai` library to enable LLM processing.
- **File modified:** [app.py](file:///c:/Users/Sanjay%20G%20L/Desktop/placement-pro/backend/app.py)
  - Registered two new Flask blueprints: `notifications_bp` under `/api/notifications` and `ai_bp` under `/api/ai`.
- **File modified:** [students.py](file:///c:/Users/Sanjay%20G%20L/Desktop/placement-pro/backend/routes/students.py)
  - **`POST /api/students/bulk-push`:** Handles student cohort registration for recruitment drives. It creates placement records, tracks stage updates, creates system alerts, and registers administrative action audit logs.
  - **`POST /api/students/bulk-delete`:** Enables administrative cleanup of multiple candidate records.
- **File created:** [notifications.py](file:///c:/Users/Sanjay%20G%20L/Desktop/placement-pro/backend/routes/notifications.py)
  - Implements retrieval of unread count and logs, and supports marking alerts as read.
- **File created:** [ai.py](file:///c:/Users/Sanjay%20G%20L/Desktop/placement-pro/backend/routes/ai.py)
  - Implements the complete **Dual-Mode AI Engine** (uses Gemini when `GEMINI_API_KEY` is present, and falls back to a smart local NLP / SQL engine when keyless):
    - **`POST /api/ai/chatbot`:** Answers user queries. Evaluates keywords to query database stats (counts, placement rate, highest package, etc.) returning live data.
    - **`POST /api/ai/analyze-resume`:** Calculates ATS scores, extracts skill keywords, and lists strengths and improvements.
    - **`GET /api/ai/eligibility-recommendation`:** Evaluates CGPA baseline parameters, tracks skill-similarity, and sorts top matching students.
    - **`POST /api/ai/interview-prep`:** Generates technical and behavioral mock interview prep questions.

---

## 3. Frontend Portal Integration
- **File modified:** [sidebar.php](file:///c:/Users/Sanjay%20G%20L/Desktop/placement-pro/frontend/partials/sidebar.php)
  - Added a navigation tab for the **AI Hub** (using a robot icon).
- **File modified:** [header.php](file:///c:/Users/Sanjay%20G%20L/Desktop/placement-pro/frontend/partials/header.php)
  - Converted the static notification bell into an interactive Bootstrap dropdown.
  - Embedded a script that polls the notifications API every 30 seconds, increments an alert badge, and displays alerts with custom type indicators (success checkmarks, alert warnings, etc.).
- **File modified:** [students.php](file:///c:/Users/Sanjay%20G%20L/Desktop/placement-pro/frontend/students.php)
  - Added checkboxes to both **List** and **Grid** views.
  - Added a master **Select All** check control in the header.
  - Configured the **Bulk Actions** dropdown (Push to Company, Bulk Delete, Export selected to CSV) to show/hide dynamically.
  - Added the **Push to Company Modal** that loads company options dynamically and submits the bulk push.
- **File created:** [ai_hub.php](file:///c:/Users/Sanjay%20G%20L/Desktop/placement-pro/frontend/ai_hub.php)
  - Built a premium dashboard page with 4 functional AI tabs (Chatbot, Resume Analyzer, Drive Recommender, and Interview Prep) matching the portal's design theme.

---

## Verification Guide (How to run locally)

Since local command execution and browser subagents are restricted in this environment, please run the following steps on your local system to spin up and verify the application:

### 1. Build and Start the Docker containers
From the root workspace directory, run:
```bash
docker-compose down
docker-compose up --build
```
*Note: This builds both frontend and backend and initializes the database migrations.*

### 2. Verify the Student Interest Workflow ("Push to Company")
1. Open `http://localhost:8000/login.php` in your browser.
2. Sign in using the default admin credentials:
   - **Email:** `admin@college.edu`
   - **Password:** `admin123`
3. Click on the **Students** tab in the sidebar.
4. Tick the checkboxes next to multiple students. The **Bulk Actions** dropdown will slide into view.
5. Click **Bulk Actions** -> **Push to Company**.
6. Select a recruiter (e.g. Wipro or TCS) in the dropdown modal and click **Push to Company**.
7. Confirm that:
   - A success toast is displayed.
   - The selected students' status changes to **In-process (Applied)**.
   - Pushed students' timelines now list the active drive registration.

### 3. Verify System Notifications
1. Click the notification bell icon in the top header.
2. Verify that the alert logs show the push notification: *"X students successfully pushed to [Company Name]"*.
3. Click **Mark all read** to check if the notification badge resets.

### 4. Verify the AI Hub
1. Click on the **AI Hub** tab in the sidebar.
2. **AI Chatbot:** Type *"What is the highest package?"* or *"How many students are registered?"*. Confirm that the chatbot returns live database stats.
3. **Resume Analyzer:** Paste some sample text (e.g., listing projects and skills like *"Python, SQL, HTML"*) and click Analyze. Verify the ATS score and keyword chips.
4. **Drive Recommender:** Choose a company drive and check if eligible candidates are ranked by fit score. Try clicking the **Push** button next to a recommended candidate.
5. **Interview Prep:** Choose a student and company drive, click generate, and check if technical and HR questions are generated.
