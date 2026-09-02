# Execution Task List

- [x] Database Schema & Setup Updates
  - [x] Update `schema.sql` with new `companies` columns and `notifications` table
  - [x] Update `backend/setup_db.py` to auto-apply migrations and seed notifications
- [x] Backend Services Implementation
  - [x] Add `google-generativeai` to `backend/requirements.txt`
  - [x] Create `backend/routes/notifications.py` for alerts CRUD
  - [x] Add bulk push/delete endpoints in `backend/routes/students.py`
  - [x] Create `backend/routes/ai.py` for AI Chatbot, Resume scoring, Recommender, and Prep
  - [x] Integrate blueprints in `backend/app.py`
- [x] Frontend Portal Integration
  - [x] Add "AI Hub" link to `frontend/partials/sidebar.php`
  - [x] Integrate interactive notifications dropdown in `frontend/partials/header.php`
  - [x] Implement checkboxes, bulk actions, and the "Push to Company" modal in `frontend/students.php`
  - [x] Create `frontend/ai_hub.php` with AI Chat, Resume analyzer, Drive Recommender, and Interview Prep
- [x] Verification & Handover
  - [x] Static syntax check completed
  - [x] Documented in `walkthrough.md`
