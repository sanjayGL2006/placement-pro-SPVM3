# Placement Pro — Smart College Placement Management System

<div align="center">

![Placement Pro Banner](https://img.shields.io/badge/Placement%20Pro-SPVM3-4F46E5?style=for-the-badge&logo=graduation-cap)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15+-316192?style=flat-square&logo=postgresql&logoColor=white)
![Python](https://img.shields.io/badge/Python-3.12-3776AB?style=flat-square&logo=python&logoColor=white)
![spaCy](https://img.shields.io/badge/spaCy-3.8-09A3D5?style=flat-square&logo=spacy&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)

**Developed by SPVM3 Tech Solution — Sanjay G L**

[🔥 Live Firebase Preview](https://spvm3-placement.web.app/) • [📄 GitHub Pages Preview](https://sanjaygl2006.github.io/placement-pro-SPVM3/)

</div>

---

## Overview

**Placement Pro** is a full-stack, enterprise-grade Placement & Campus Recruitment Management System built for **PESIAMS** (PES Institute of Advanced Management Studies) and modern colleges & universities.

It features a **PHP/PostgreSQL backend**, a **dynamic PHP frontend**, **AI/ML-powered resume NER**, and a **Progressive Web App (PWA)** — providing a complete end-to-end placement office solution.

> **Mock Mode**: The system automatically detects if PostgreSQL is unavailable and runs with realistic mock data — no database setup required for demos!

---

## ✨ Key Features

| Feature | Description |
|---|---|
| 📅 **Placement Calendar** | Interactive month-by-month calendar with company visits, drives & compensation |
| ⚡ **Live Auto-Update** | Real-time sync for stats, alerts & events (15s / 30s / 60s / 5m intervals) |
| 🎓 **PESIAMS Departments** | BCA, BBA, BBA Hospitality, B.Com, B.Sc CS, B.Sc Physics, B.Sc Chemistry |
| 🎯 **Skill Gap Analysis** | Recruiter demand vs. student skill prevalence with workshop suggestions |
| 🗑️ **Recycle Bin** | Soft reset (restore) & Hard reset (full wipe) for students & companies |
| 📥 **Smart Import** | Upload `.xlsx`, `.csv`, `.docx`, `.pdf` with auto column-matching & preview |
| 📊 **Reports & Export** | PDF, Excel, CSV exports for students, companies, placement summaries |
| 🔐 **Role-Based Access** | Granular RBAC for Principal, HOD, Coordinator, Faculty, Student roles |
| 🤖 **AI/ML Resume NER** | spaCy NER model trained on 2,400+ resumes to extract Skills, Degree, Location |
| 📱 **PWA** | Installable app with offline caching for static assets |
| 🏫 **Company Portal** | Separate HR dashboard for company users to browse student profiles |
| 📄 **Documents Module** | Upload, preview & manage placement-related documents |
| 🔔 **Push Notifications** | Send placement alerts & drive announcements to students |

---

## 🔐 Role-Based Access Control

| Role | Dashboard | Students | Companies | Import Data | Settings | AI Hub |
|---|:---:|:---:|:---:|:---:|:---:|:---:|
| **Principal** | ✅ | ✅ Full | ✅ | ✅ Yes | ✅ | ✅ |
| **HOD** | ✅ | ✅ Full | ✅ | ✅ Yes | ✅ | ✅ |
| **Placement Coordinator** | ✅ | ✅ Full | ✅ | ✅ Yes | ✅ | ✅ |
| **Admin** | ✅ | ✅ Full | ✅ | ✅ Yes | ✅ | ✅ |
| **Faculty / Staff** | ✅ Read | ✅ Read | ✅ Read | ❌ No | ❌ No | ✅ |
| **Student** | ✅ Read | ❌ No | ❌ No | ❌ No | ❌ No | ✅ |

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| **Frontend** | PHP 8.2+ (template rendering), HTML5, CSS3, Vanilla JS, Bootstrap 5.3, Chart.js |
| **Backend API** | PHP 8.2+ OOP MVC, PDO, RESTful JSON API |
| **Database** | PostgreSQL 15+ |
| **AI/ML** | Python 3.12, spaCy 3.8, HuggingFace Transformers, PyTorch, scikit-learn |
| **Auth** | PHP Sessions + JWT tokens, Argon2id password hashing |
| **PWA** | Service Worker, Web App Manifest (offline-first) |
| **Containerization** | Docker, Docker Compose, Kubernetes (K8s) |
| **Cloud** | Firebase Hosting, GitHub Pages (static previews), Koyeb |

---

## 📁 Project Structure

```text
placement-pro-SPVM3/
│
├── php_backend/                    # Backend REST API (PHP OOP MVC)
│   ├── .env                        # Environment config (DB credentials, secrets)
│   ├── database/
│   │   └── migrations/
│   │       └── 001_initial_schema.sql  # PostgreSQL schema
│   ├── public/
│   │   ├── index.php               # API router + mock mode auto-detection
│   │   └── api/                    # Individual API endpoint files
│   └── src/
│       ├── Config/Database.php     # PDO singleton connection
│       ├── Controllers/            # Auth, Student, Company, User controllers
│       ├── Middleware/             # JWT auth, CSRF, RBAC middleware
│       └── Models/                 # Student, Company models
│
├── frontend/                       # PHP frontend application
│   ├── login.php                   # Login (email + password, no Firebase)
│   ├── dashboard.php               # KPI dashboard with live charts
│   ├── students.php                # Student directory & drive tracking
│   ├── companies.php               # Company management & calendar
│   ├── import.php                  # Data import (Principal/HOD/Coordinator only)
│   ├── reports.php                 # Analytics & PDF/Excel export
│   ├── settings.php                # User & system settings
│   ├── ai_hub.php                  # AI tools (Resume Analyzer, Q&A Generator)
│   ├── skill_gap.php               # Skill gap analysis
│   ├── push.php                    # Push notifications to students
│   ├── documents.php               # Document upload & management
│   ├── company_dashboard.php       # Company HR portal
│   ├── config.php                  # Shared PHP config (API base, session, RBAC)
│   ├── session_store.php           # Stores JWT in PHP session after login
│   ├── service-worker.js           # PWA service worker (network-first)
│   ├── manifest.json               # PWA manifest
│   ├── partials/
│   │   ├── header.php              # Top navigation bar
│   │   ├── sidebar.php             # Role-aware sidebar (hides items by role)
│   │   └── nav.php                 # Global modals & shared scripts
│   ├── assets/
│   │   ├── css/style.css           # Global design system
│   │   └── js/
│   │       ├── api.js              # Axios-style API client with mock fallback
│   │       ├── auth.js             # Session validation & logout
│   │       └── firebase-init.js    # Stub (Firebase removed — local auth only)
│   └── previews/                   # Auto-generated static HTML for GitHub Pages
│
├── backend/                        # Python AI/ML training & FastAPI inference
│   ├── train_ner.py                # Resume NER training (spaCy)
│   ├── train_qa.py                 # HR Q&A fine-tuning (HuggingFace)
│   ├── train_classifier.py         # Resume image classifier (CNN/PyTorch)
│   ├── app.py                      # FastAPI inference server
│   └── test_app.py                 # Pytest suite (15 tests — all passing ✅)
│
├── models/
│   └── ner_model/                  # Trained spaCy NER model output
│
├── dataset/
│   ├── archive/                    # Resume image dataset (classifier)
│   ├── archive (1)/                # HR interview Q&A pairs (5,000+)
│   └── archive (2)/                # Resume NER dataset (2,400+ annotated)
│
├── k8s/                            # Kubernetes manifests
├── docker-compose.yml              # Multi-container orchestration
├── render_previews.py              # PHP → static HTML converter
├── generate_ppt.py                 # Auto-generates PowerPoint presentation
├── Placement-Pro.pptx              # Generated project presentation
├── user&pass.txt                   # Default test login credentials
├── QA_REPORT.md                    # System QA audit matrix
├── TESTING_TYPES.md                # 32 testing types catalog
└── README.md                       # This file
```

---

## 🚀 Local Setup & Development

### Prerequisites

- PHP 8.2+ (with `pdo_pgsql` extension — *optional, mock mode works without it*)
- PostgreSQL 15+ (*optional for demo*)
- Python 3.12+

---

### 1. Database Setup *(Optional — skip for mock mode)*

```powershell
# Create the database
createdb placement_pro

# Run the schema migration
psql -d placement_pro -f php_backend/database/migrations/001_initial_schema.sql
```

---

### 2. Start the Backend API

```powershell
# Navigate to the public directory
cd php_backend/public

# Start backend server (auto-detects DB; uses mock data if unavailable)
php -S localhost:5500 index.php
```

✅ Backend API: `http://localhost:5500/api`
> If PostgreSQL is not running, the backend automatically serves **realistic mock data** for all endpoints.

---

### 3. Start the Frontend

```powershell
# From project root, navigate to frontend
cd frontend

# Start frontend server
php -S localhost:7555
```

✅ Frontend: `http://localhost:7555`

---

### 4. Login with Test Credentials

| Role | Email | Password |
|---|---|---|
| Placement Coordinator | `coordinator@pesiams.edu.in` | `Coordinator@2026` |
| Principal | `principal@pesiams.edu.in` | `Principal@2026` |
| HOD | `hod@pesiams.edu.in` | `HOD@2026` |
| Admin | `admin@pesiams.edu.in` | `Admin@2026` |
| Faculty | `staff.bca@pesiams.edu.in` | `Staff@2026` |
| Student | `student@pesiams.edu.in` | `Student@2026` |


> Full credentials also in [`user&pass.txt`](./user&pass.txt)

---

### 5. Python / AI-ML Setup

```powershell
# Create virtual environment (from project ROOT)
python -m venv .venv
.\.venv\Scripts\Activate.ps1

# Install dependencies
.\.venv\Scripts\pip install spacy torch transformers datasets scikit-learn pillow python-pptx
```

> ⚠️ **Always run AI scripts from the project ROOT, not from inside `backend/`**

---

### 6. Train AI/ML Models *(from project root)*

```powershell
# Resume NER model — extracts Skills, Degree, College, Location from resumes
.\.venv\Scripts\python.exe backend/train_ner.py

# HR Q&A Generator — fine-tune on interview questions (requires GPU)
.\.venv\Scripts\python.exe backend/train_qa.py

# Resume Image Classifier (requires GPU)
.\.venv\Scripts\python.exe backend/train_classifier.py
```

**NER Training Results:**

| Iteration | Loss |
|---|---|
| 1 | ~15,000 |
| 5 | ~3,460 |
| 10 | **~2,725** ✅ |

Trained model saved to: `models/ner_model/`

---

### 7. Run Automated Tests

```powershell
cd backend
.\venv\Scripts\python.exe -m pytest
# Expected: 15 passed ✅
```

---

### 8. Regenerate Static HTML Previews

```powershell
# From project root
python render_previews.py
```

Converts all PHP templates → static HTML files in `frontend/previews/` for GitHub Pages.

---

### 9. Generate PowerPoint Presentation

```powershell
.\.venv\Scripts\python.exe generate_ppt.py
# Output: Placement-Pro.pptx
```

To open the generated file:
```powershell
Start-Process ".\Placement-Pro.pptx"
```

---

## 🐳 Deployment Options

### Docker Compose

```bash
docker-compose up --build
```

| Service | URL |
|---|---|
| Frontend | `http://localhost:7500` |
| Backend API | `http://localhost:5500/api` |

---

### Kubernetes (K8s)

```bash
kubectl apply -k k8s/
kubectl get pods,svc,pvc,ingress -n placement-pro
```

Access via: `http://<node-ip>:30750`

---

### Koyeb Cloud

| Service | Setting |
|---|---|
| Backend Dockerfile | `php_backend/Dockerfile` |
| Backend Port | `5500` |
| Backend Health Check | `/api/health` |
| Frontend Dockerfile | `frontend/Dockerfile` |
| Frontend Port | `80` |
| Env var | `PLACEMENT_API_BASE=https://<your-backend>.koyeb.app/api` |

---

## 📡 Core API Endpoints

| Method | Endpoint | Auth Required | Description |
|:---|:---|:---:|:---|
| `POST` | `/api/auth/login` | ❌ | Authenticate & get session/token |
| `POST` | `/api/auth/logout` | ✅ | Invalidate session |
| `GET` | `/api/health` | ❌ | Health check + DB status |
| `GET` | `/api/dashboard/stats` | ✅ | Live placement KPIs |
| `GET` | `/api/dashboard/filters` | ✅ | Department/year filter options |
| `GET` | `/api/students` | ✅ | Paginated student directory |
| `GET` | `/api/companies` | ✅ | Company directory & drives |
| `GET` | `/api/skill-gap/analysis` | ✅ | Skill demand vs. prevalence |
| `GET` | `/api/drives/repeat-alerts` | ✅ | Repeat shortlist alerts |
| `GET` | `/api/notifications` | ✅ | User notifications |
| `POST` | `/api/imports/students/preview` | ✅ | Upload & preview student file |
| `POST` | `/api/imports/students/commit` | ✅ | Commit approved records |
| `POST` | `/api/recycle-bin/reset` | ✅ | Soft reset (move to trash) |
| `POST` | `/api/recycle-bin/hard-reset` | ✅ | Hard wipe all data |
| `GET` | `/api/reports/students/pdf` | ✅ | Download student report PDF |

> When PostgreSQL is not available, all endpoints return **realistic mock data** automatically.

---

## 🐛 Known Issues & Fixes Applied

| Issue | Fix Applied |
|---|---|
| CORS blocked from non-7500 ports | Dynamic origin matching — all `localhost:*` ports allowed |
| 401 on API calls (no DB) | Mock mode auto-detection in `index.php` |
| Firebase `400 Bad Request` errors | Firebase completely removed — local PHP session auth only |
| `login.php` null reference on `portalRole` | Optional chaining (`?.value ?? default`) added |
| Role check case-sensitivity (403) | `strtolower()` normalization in `require_role()` |
| Service worker caching `chrome-extension://` | Protocol check added — only caches `http/https` |
| `train_ner.py` wrong dataset path | `os.path.abspath(__file__)` for script-relative path |
| `generate_ppt.py` slide layout KeyError | `try/except` with textbox fallback for missing placeholder |

---

## 👨‍💻 Author & Credits

Developed by **SPVM3 Tech Solution — Sanjay G L**

[![GitHub](https://img.shields.io/badge/GitHub-sanjayGL2006-181717?style=flat-square&logo=github)](https://github.com/sanjayGL2006)
[![Firebase](https://img.shields.io/badge/Firebase-Live%20Preview-FFCA28?style=flat-square&logo=firebase&logoColor=black)](https://spvm3-placement.web.app/)
[![Pages](https://img.shields.io/badge/GitHub%20Pages-Static%20Preview-222222?style=flat-square&logo=github-pages)](https://sanjaygl2006.github.io/placement-pro-SPVM3/)
