import os
import uuid
from flask import Blueprint, request, jsonify, send_file, current_app
from database import get_cursor, commit
from routes.auth import token_required
from werkzeug.utils import secure_filename

documents_bp = Blueprint("documents", __name__)

ALLOWED_EXTENSIONS = {"pdf", "jpg", "jpeg", "png", "doc", "docx"}

def allowed_file(filename):
    return '.' in filename and filename.rsplit('.', 1)[1].lower() in ALLOWED_EXTENSIONS

@documents_bp.route("/upload", methods=["POST"])
@token_required(roles=["student", "hr", "faculty", "admin"])
def upload_document():
    if "file" not in request.files:
        return jsonify({"error": "No file part"}), 400
    
    file = request.files["file"]
    if file.filename == "":
        return jsonify({"error": "No selected file"}), 400

    if not allowed_file(file.filename):
        return jsonify({"error": "File type not allowed"}), 400

    student_id = request.form.get("student_id")
    if not student_id:
        return jsonify({"error": "student_id required"}), 400

    placement_id = request.form.get("placement_id") or None
    doc_type = request.form.get("doc_type", "OTHER")

    # Generate random filename to prevent collisions and path traversal
    ext = file.filename.rsplit('.', 1)[1].lower()
    random_filename = f"{uuid.uuid4().hex}.{ext}"
    original_name = secure_filename(file.filename)
    
    # Ensure upload directory exists
    docs_dir = os.path.join(current_app.config["UPLOAD_FOLDER"], "documents")
    os.makedirs(docs_dir, exist_ok=True)
    
    filepath = os.path.join(docs_dir, random_filename)
    file.save(filepath)
    file_size = os.path.getsize(filepath)

    cur = get_cursor()
    cur.execute(
        """INSERT INTO student_documents 
           (student_id, placement_id, doc_type, filename, original_name, file_size_bytes)
           VALUES (%s, %s, %s, %s, %s, %s)""",
        (student_id, placement_id, doc_type, random_filename, original_name, file_size)
    )
    new_id = cur.lastrowid
    commit()

    return jsonify({"success": True, "id": new_id, "message": "Document uploaded successfully"})


@documents_bp.route("/student/<int:student_id>", methods=["GET"])
@token_required()
def list_student_documents(student_id):
    cur = get_cursor()
    cur.execute("""
        SELECT sd.*, c.name as company_name 
        FROM student_documents sd
        LEFT JOIN placements p ON sd.placement_id = p.id
        LEFT JOIN companies c ON p.company_id = c.id
        WHERE sd.student_id = %s
        ORDER BY sd.uploaded_at DESC
    """, (student_id,))
    docs = cur.fetchall()
    return jsonify({"documents": docs})


@documents_bp.route("/<int:doc_id>/download", methods=["GET"])
@token_required()
def download_document(doc_id):
    cur = get_cursor()
    cur.execute("SELECT * FROM student_documents WHERE id = %s", (doc_id,))
    doc = cur.fetchone()
    
    if not doc:
        return jsonify({"error": "Document not found"}), 404
        
    docs_dir = os.path.join(current_app.config["UPLOAD_FOLDER"], "documents")
    filepath = os.path.join(docs_dir, doc["filename"])
    
    if not os.path.exists(filepath):
        return jsonify({"error": "File missing on server"}), 404
        
    return send_file(filepath, download_name=doc["original_name"], as_attachment=True)


@documents_bp.route("/<int:doc_id>", methods=["DELETE"])
@token_required(roles=["student", "faculty", "admin"])
def delete_document(doc_id):
    cur = get_cursor()
    cur.execute("SELECT * FROM student_documents WHERE id = %s", (doc_id,))
    doc = cur.fetchone()
    
    if not doc:
        return jsonify({"error": "Document not found"}), 404
        
    cur.execute("DELETE FROM student_documents WHERE id = %s", (doc_id,))
    commit()
    
    docs_dir = os.path.join(current_app.config["UPLOAD_FOLDER"], "documents")
    filepath = os.path.join(docs_dir, doc["filename"])
    if os.path.exists(filepath):
        os.remove(filepath)
        
    return jsonify({"success": True, "message": "Document deleted"})
