"""
Placement Pro — Flask REST API
Run: python app.py  (dev)  |  gunicorn app:create_app()  (prod)
"""
# pyrefly: ignore [missing-import]
import os
# pyrefly: ignore [missing-import]
import hashlib

# Patch hashlib.md5 for OpenSSL / Python 3.8 compatibility with ReportLab
_orig_md5 = hashlib.md5

def _safe_md5(*args, **kwargs):
    kwargs.pop("usedforsecurity", None)
    return _orig_md5(*args, **kwargs)

hashlib.md5 = _safe_md5

# pyrefly: ignore [missing-import]
from flask import Flask, jsonify, request
# pyrefly: ignore [missing-import]
from flask_cors import CORS  # type: ignore

# pyrefly: ignore [missing-import]
from database import init_db_pool, close_db_pool
# pyrefly: ignore [missing-import]
from routes.auth import auth_bp
# pyrefly: ignore [missing-import]
from routes.students import students_bp
# pyrefly: ignore [missing-import]
from routes.companies import companies_bp
# pyrefly: ignore [missing-import]
from routes.imports import imports_bp
# pyrefly: ignore [missing-import]
from routes.dashboard import dashboard_bp
# pyrefly: ignore [missing-import]
from routes.reports import reports_bp
# pyrefly: ignore [missing-import]
from routes.recycle_bin import recycle_bin_bp
# pyrefly: ignore [missing-import]
from routes.notifications import notifications_bp
# pyrefly: ignore [missing-import]
from routes.ai import ai_bp
# pyrefly: ignore [missing-import]
from routes.drives import drives_bp
# pyrefly: ignore [missing-import]
from routes.documents import documents_bp
# pyrefly: ignore [missing-import]
from scheduler import run_scheduler


def create_app():
    """Create and configure the Flask application."""
    app = Flask(__name__)
    app.config["SECRET_KEY"] = os.getenv("SECRET_KEY", "change-me-in-prod")
    app.config["JWT_SECRET"] = os.getenv("JWT_SECRET", "change-me-too")
    app.config["MAX_CONTENT_LENGTH"] = 20 * 1024 * 1024  # 20MB upload cap

    # Configure upload folder
    # pyrefly: ignore [missing-import]
    import tempfile
    default_upload = os.path.join(tempfile.gettempdir(), "placement_uploads")
    app.config["UPLOAD_FOLDER"] = os.getenv("UPLOAD_FOLDER", default_upload)
    os.makedirs(app.config["UPLOAD_FOLDER"], exist_ok=True)

    # CORS configuration – allow specified origins or common development origins
    allowed_origins_env = os.getenv("ALLOWED_ORIGINS")
    if allowed_origins_env:
        allowed_origins = [o.strip() for o in allowed_origins_env.split(",") if o.strip()]
    else:
        allowed_origins = [
            "https://spvm3-placement.firebaseapp.com",
            "http://localhost:7500",
            "http://127.0.0.1:7500",
            "http://localhost:5500",
            "http://127.0.0.1:5500",
        ]
    CORS(app, resources={r"/api/*": {"origins": "*" if "*" in allowed_origins else allowed_origins}})

    # Initialise database connection pool
    init_db_pool(app)

    # Initialise Firebase Admin SDK (if needed)
    # pyrefly: ignore [missing-import]
    from firebase_config import init_firebase_admin
    init_firebase_admin(app)

    # Register blueprints
    app.register_blueprint(auth_bp, url_prefix="/api/auth")
    app.register_blueprint(students_bp, url_prefix="/api/students")
    app.register_blueprint(companies_bp, url_prefix="/api/companies")
    app.register_blueprint(imports_bp, url_prefix="/api/imports")
    app.register_blueprint(dashboard_bp, url_prefix="/api/dashboard")
    app.register_blueprint(reports_bp, url_prefix="/api/reports")
    app.register_blueprint(recycle_bin_bp, url_prefix="/api/recycle-bin")
    app.register_blueprint(notifications_bp, url_prefix="/api/notifications")
    app.register_blueprint(ai_bp, url_prefix="/api/ai")
    app.register_blueprint(drives_bp, url_prefix="/api/drives")
    app.register_blueprint(documents_bp, url_prefix="/api/documents")

    # Direct alias for eligible-for endpoint
    # pyrefly: ignore [missing-import]
    from routes.students import get_eligible_students
    app.add_url_rule(
        "/api/eligible-for/<int:company_id>",
        view_func=get_eligible_students,
        methods=["GET"]
    )

    # Preflight handler to ensure all OPTIONS requests receive a clean 200 response
    @app.before_request
    def handle_preflight():
        if request.method == "OPTIONS":
            return app.make_default_options_response()

    # Simple health check endpoint
    @app.route("/api/health")
    def health():
        return jsonify({"status": "ok"})

    # Error handlers
    @app.errorhandler(404)
    def not_found(e):
        return jsonify({"error": "Not found"}), 404

    @app.errorhandler(413)
    def too_large(e):
        return jsonify({"error": "File too large (max 20MB)"}), 413

    @app.errorhandler(Exception)
    def handle_exception(e):
        # pyrefly: ignore [missing-import]
        import traceback
        print("Unhandled Exception:", traceback.format_exc())
        response = jsonify({"error": str(e) or "Internal server error"})
        response.status_code = 500
        return response

    # After request processing – ensure CORS headers (handled by Flask-CORS but kept for legacy support)
    @app.after_request
    def after_request(response):
        origin = request.headers.get("Origin")
        if origin and (origin in allowed_origins or "*" in allowed_origins):
            response.headers["Access-Control-Allow-Origin"] = origin
        elif allowed_origins and "*" not in allowed_origins:
            response.headers["Access-Control-Allow-Origin"] = allowed_origins[0]
        else:
            response.headers["Access-Control-Allow-Origin"] = "*"
        response.headers["Access-Control-Allow-Headers"] = (
            "Content-Type, Authorization, X-Requested-With"
        )
        response.headers["Access-Control-Allow-Methods"] = (
            "GET, POST, PUT, DELETE, OPTIONS"
        )
        response.headers["Access-Control-Allow-Private-Network"] = "true"
        return response

    # Start background scheduler
    run_scheduler(app)

    # Teardown: close DB pool
    @app.teardown_appcontext
    def _close(exc):
        close_db_pool(exc)

    return app

# Application instance
app = create_app()

if __name__ == "__main__":
    app.run(host="0.0.0.0", debug=True, port=5500)
