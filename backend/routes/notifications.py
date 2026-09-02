from flask import Blueprint, request, jsonify
from database import get_cursor, commit
from routes.auth import token_required

notifications_bp = Blueprint("notifications", __name__)


def add_notification(title, message, n_type="info"):
    """Helper to write system alerts to the database."""
    try:
        cur = get_cursor()
        cur.execute(
            "INSERT INTO notifications (title, message, type, is_read) VALUES (%s, %s, %s, FALSE)",
            (title, message, n_type),
        )
        commit()
        return True
    except Exception as e:
        print(f"Failed to save notification: {e}")
        return False


@notifications_bp.route("", methods=["GET"])
@token_required()
def list_notifications():
    cur = get_cursor()
    cur.execute(
        "SELECT * FROM notifications ORDER BY created_at DESC LIMIT 30"
    )
    rows = cur.fetchall()
    
    # Get unread count
    cur.execute("SELECT COUNT(*) AS unread_cnt FROM notifications WHERE is_read = FALSE")
    unread_count = cur.fetchone()["unread_cnt"]
    
    return jsonify({
        "notifications": rows,
        "unread_count": unread_count
    })


@notifications_bp.route("/mark-read", methods=["POST"])
@token_required()
def mark_read():
    cur = get_cursor()
    cur.execute("UPDATE notifications SET is_read = TRUE WHERE is_read = FALSE")
    commit()
    return jsonify({"success": True})
