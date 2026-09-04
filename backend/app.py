"""
Placement Pro — Flask REST API
Run: python app.py  (dev)  |  gunicorn app:create_app()  (prod)
"""
import os
import hashlib

# Patch hashlib.md5 for OpenSSL / Python 3.8 compatibility with ReportLab
_orig_md5 = hashlib.md5

def _safe_md5(*args, **kwargs):
    kwargs.pop("usedforsecurity", None)
    return _orig_md5(*args, **kwargs)

hashlib.md5 = _safe_md5

from flask import Flask, jsonify
from flask_cors import CORS  # type: ignore

from database import init_db_pool, close_db_pool
from routes.auth import auth_bp
from routes.students import students_bp
from routes.companies import companies_bp
from routes.imports import imports_bp
from routes.dashboard import dashboard_bp
from routes.reports import reports_bp
from routes.recycle_bin import recycle_bin_bp
from routes.notifications import notifications_bp
from routes.ai import ai_bp
from routes.drives import drives_bp
from routes.documents import documents_bp
from scheduler import run_scheduler


def create_app():
    """Create and configure the Flask application."""
    app = Flask(__name__)
    app.config["SECRET_KEY"] = os.getenv("SECRET_KEY", "change-me-in-prod")
    app.config["JWT_SECRET"] = os.getenv("JWT_SECRET", "change-me-too")
    app.config["MAX_CONTENT_LENGTH"] = 20 * 1024 * 1024  # 20MB upload cap

    # Configure upload folder
    import tempfile
    default_upload = os.path.join(tempfile.gettempdir(), "placement_uploads")
    app.config["UPLOAD_FOLDER"] = os.getenv("UPLOAD_FOLDER", default_upload)
    os.makedirs(app.config["UPLOAD_FOLDER"], exist_ok=True)

    # CORS configuration – allow only specified origins (default to Firebase domain)
    allowed_origins = os.getenv(
        "ALLOWED_ORIGINS", "https://spvm3-placement.firebaseapp.com"
    ).split(",")
    CORS(app, resources={r"/api/*": {"origins": allowed_origins}})

    # Initialise database connection pool
    init_db_pool(app)

    # Initialise Firebase Admin SDK (if needed)
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
        import traceback
        print("Unhandled Exception:", traceback.format_exc())
        response = jsonify({"error": str(e) or "Internal server error"})
        response.status_code = 500
        return response

    # After request processing – ensure CORS headers (handled by Flask-CORS but kept for legacy support)
    @app.after_request
    def after_request(response):
        response.headers["Access-Control-Allow-Origin"] = ",".join(allowed_origins)
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
