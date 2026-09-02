# Placement Pro — Smart College Placement Management System

> **Developed by SPVM3 Tech Solution by Sanjay G L**

Placement Pro is a full-stack, enterprise-grade Placement & Campus Recruitment Management System built for colleges and universities.

---

## Key Features

- 📅 **Dynamic Placement Calendar**: Interactive month-by-month calendar displaying scheduled company visit dates, placement drives, and drive package details.
- ⚡ **Live Auto-Update Sync**: Real-time background sync for dashboard stat cards, repeat shortlist alerts, and drive events with configurable refresh intervals (15s, 30s, 60s, 5m).
- 🗑️ **System Reset & Recycle Bin**:
  - **Soft Reset**: Safely move student or company records to the Recycle Bin for easy restoration.
  - **Hard Reset**: Perform a complete data wipe across all places and empty the Recycle Bin.
- 📥 **Smart File Import Pipeline**: Upload Excel (`.xlsx`, `.xls`), CSV, Word (`.docx`), and PDF (`.pdf`) files with automatic column fuzzy-matching, validation previews, and auto-mapping.
- 📊 **Analytics & Reports**: Export student directories, company rosters, and placement summaries to PDF, Excel, and CSV formats.
- 🔐 **Role-Based Authentication**: Secure JWT authentication with Argon2id password hashing supporting Admin, Faculty, and HR roles.

---

## Tech Stack

- **Frontend**: PHP 8.2 + Bootstrap 5 + Vanilla JS (AJAX API integration)
- **Backend API**: Python 3.12 + Flask REST API + Gunicorn WSGI
- **Database**: SQLite (Development/Container default) / PostgreSQL / PyMySQL
- **Containerization**: Docker, Docker Compose, Kubernetes (K8s)

---

## Project Structure

```text
placement-pro/
├── backend/
│   ├── app.py                  # Flask application factory
│   ├── database.py             # SQLite / database connector & schema initializer
│   ├── import_utils.py         # File parsing, column-mapping & validation
│   ├── requirements.txt        # Python package dependencies
│   ├── schema_sqlite.sql       # SQLite database schema & seed data
│   ├── Dockerfile              # Production Python 3.12 Dockerfile
│   └── routes/                 # Modular API blueprints
│       ├── auth.py             # Login, register, JWT decorator
│       ├── students.py         # Student directory CRUD
│       ├── companies.py        # Company directory CRUD
│       ├── drives.py           # Placement drive management
│       ├── dashboard.py        # Real-time statistics
│       ├── recycle_bin.py      # Soft reset & hard wipe management
│       ├── notifications.py    # Notification hub & alerts
│       ├── imports.py          # Smart file import preview & commit
│       ├── reports.py          # PDF/Excel/CSV generator
│       └── documents.py        # Student document management
├── frontend/
│   ├── config.php              # Shared environment configuration & API_BASE
│   ├── login.php               # Login page
│   ├── dashboard.php           # Real-time dashboard & placement calendar
│   ├── students.php            # Filterable student directory
│   ├── companies.php           # Company management directory
│   ├── import.php              # Smart import drag-and-drop tool
│   ├── settings.php            # System settings & notification hub
│   ├── Dockerfile              # Production PHP 8.2 Apache Dockerfile
│   └── partials/               # Reusable headers and sidebar navigation
├── k8s/                        # Kubernetes orchestration manifests
│   ├── namespace.yaml
│   ├── configmap.yaml
│   ├── secret.yaml
│   ├── pvc.yaml
│   ├── backend-deployment.yaml
│   ├── frontend-deployment.yaml
│   ├── ingress.yaml
│   └── kustomization.yaml
├── docker-compose.yml          # Multi-container local/prod orchestrator
└── requirements.txt            # Root dependencies for container builds
```

---

## Local Setup & Development

### 1. Backend Setup (Windows PowerShell)

```powershell
cd backend
python -m venv venv
.\venv\Scripts\Activate.ps1
pip install -r requirements.txt
python app.py
```
*Backend API runs on `http://localhost:5500`*

### 2. Frontend Setup (Windows PowerShell)

```powershell
cd frontend
$env:PLACEMENT_API_BASE="http://localhost:5500/api"
php -S localhost:7500
```
*Frontend application runs on `http://localhost:7500/login.php`*

---

## Deployment Options

### 1. Docker Compose Deployment

Run the complete full-stack application using Docker Compose:

```bash
docker-compose up --build
```

- **Frontend Application**: `http://localhost:7500`
- **Backend REST API**: `http://localhost:5500/api`

---

### 2. Kubernetes Deployment (k8s)

Deploy onto any Kubernetes cluster using Kustomize:

```bash
kubectl apply -k k8s/
```

Verify deployment status in the `placement-pro` namespace:

```bash
kubectl get pods,svc,pvc,ingress -n placement-pro
```

Access via NodePort `http://<node-ip>:30750` or configured NGINX Ingress domain.

---

### 3. Koyeb Cloud Deployment

Deploy full-stack services using Koyeb Docker deployment:

1. **Backend Web Service**:
   - **Repository**: Connect `sanjayGL2006/placement-pro-SPVM3`
   - **Builder**: `Dockerfile`
   - **Dockerfile Location**: `backend/Dockerfile`
   - **Exposed Port**: `5500`
   - **Health Check**: `/api/health`
   - **Environment Variables**:
     - `SECRET_KEY` = `placement-pro-secret-key-prod-2026`
     - `JWT_SECRET` = `placement-pro-jwt-secret-key-prod-2026`
     - `CORS_ORIGINS` = `*`

2. **Frontend Web Service**:
   - **Builder**: `Dockerfile`
   - **Dockerfile Location**: `frontend/Dockerfile`
   - **Exposed Port**: `80`
   - **Environment Variable**: `PLACEMENT_API_BASE` = `https://<your-koyeb-backend-app>.koyeb.app/api`


---

## Core API Endpoints

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/api/auth/login` | Authenticate user and issue JWT token |
| `GET` | `/api/dashboard/stats` | Retrieve live dashboard statistics |
| `GET` | `/api/students` | Get paginated student directory |
| `GET` | `/api/companies` | Get company directory & visit dates |
| `POST` | `/api/imports/students/preview` | Upload and preview student file |
| `POST` | `/api/imports/students/commit` | Commit approved student records |
| `POST` | `/api/recycle-bin/reset` | Soft reset student/company records |
| `POST` | `/api/recycle-bin/hard-reset` | Hard wipe all database data and empty trash |
| `GET` | `/api/reports/students/pdf` | Download student report in PDF format |

---

## Author & Credits

Developed by **SPVM3 Tech Solution by Sanjay G L**.
