"""
Background scheduler for Placement Pro (SQLite version).
Handles drive reminders and email notifications.
"""
import os
import time
import threading
from datetime import datetime, timedelta
import logging
import sqlite3

logger = logging.getLogger("placement_pro.scheduler")
logging.basicConfig(level=logging.INFO)

DB_PATH = os.path.join(os.path.dirname(__file__), "placement_pro.db")

def send_drive_reminders():
    """Send reminders for upcoming drives (within 48 hours) that haven't been reminded yet."""
    conn = sqlite3.connect(DB_PATH)
    conn.row_factory = sqlite3.Row
    cur = conn.cursor()
    try:
        now = datetime.now()
        upcoming = (now + timedelta(days=2)).strftime("%Y-%m-%d")
        today = now.strftime("%Y-%m-%d")
        
        cur.execute("""
            SELECT id, name, visit_date 
            FROM companies 
            WHERE visit_date IS NOT NULL 
              AND visit_date >= ?
              AND visit_date <= ?
              AND (reminder_sent = 0 OR reminder_sent IS NULL)
        """, (today, upcoming))
        
        drives = cur.fetchall()
        for drive in drives:
            cur.execute("""
                INSERT INTO notifications (title, message, type)
                VALUES (?, ?, 'warning')
            """, (
                "Upcoming Drive Reminder",
                f"Reminder: {drive['name']} placement drive is scheduled for {drive['visit_date']}.",
            ))
            cur.execute("UPDATE companies SET reminder_sent = 1 WHERE id = ?", (drive['id'],))
            
        if drives:
            conn.commit()
            logger.info(f"Sent reminders for {len(drives)} upcoming drives.")
            
    except Exception as e:
        logger.error(f"Scheduler error: {e}")
        conn.rollback()
    finally:
        cur.close()
        conn.close()


def run_scheduler(app):
    """Run background tasks continuously in a separate thread."""
    def loop():
        while True:
            try:
                send_drive_reminders()
            except Exception as e:
                logger.error(f"Scheduler loop error: {e}")
                
            time.sleep(3600)  # run every hour

    thread = threading.Thread(target=loop, daemon=True)
    thread.start()
