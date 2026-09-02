import os
import smtplib
import logging
from email.message import EmailMessage
from database import get_cursor, commit

logger = logging.getLogger("placement_pro.email")


def get_smtp_config():
    """Retrieve SMTP configuration from environment variables or defaults."""
    return {
        "sender_address": os.environ.get("SMTP_SENDER_ADDRESS", "support@yourdomain.com"),
        "sender_name": os.environ.get("SMTP_SENDER_NAME", "Placement Pro Notification System"),
        "host": os.environ.get("SMTP_HOST", "smtp.host.com"),
        "port": int(os.environ.get("SMTP_PORT", 587)),
        "username": os.environ.get("SMTP_USERNAME", "username"),
        "password": os.environ.get("SMTP_PASSWORD", "password"),
        "security_mode": os.environ.get("SMTP_SECURITY_MODE", "STARTTLS").upper(),  # STARTTLS | SSL | TLS | NONE
    }


def send_email_smtp(to_email, subject, body_text, body_html=None):
    """Send an email using standard Python smtplib with SMTP/STARTTLS/SSL configuration."""
    config = get_smtp_config()
    
    msg = EmailMessage()
    msg["Subject"] = subject
    msg["From"] = f"{config['sender_name']} <{config['sender_address']}>"
    msg["To"] = to_email
    msg.set_content(body_text)

    if body_html:
        msg.add_alternative(body_html, subtype="html")

    host = config["host"]
    port = config["port"]
    username = config["username"]
    password = config["password"]
    security_mode = config["security_mode"]

    try:
        if security_mode in ("SSL", "TLS") or port == 465:
            server = smtplib.SMTP_SSL(host, port, timeout=10)
        else:
            server = smtplib.SMTP(host, port, timeout=10)
            if security_mode == "STARTTLS":
                server.starttls()

        if username and password and username != "username":
            server.login(username, password)

        server.send_message(msg)
        server.quit()
        logger.info(f"Email successfully sent to {to_email} via SMTP ({host}:{port})")
        return True, "Email sent successfully"

    except Exception as e:
        logger.warning(f"SMTP send failed ({e}). Simulating fallback notification dispatch for {to_email}.")
        # Log to in-app notifications as fallback so system retains record
        try:
            cur = get_cursor()
            cur.execute(
                "INSERT INTO notifications (title, message, type) VALUES (%s, %s, 'info')",
                (f"Email Dispatch: {subject}", f"Sent to {to_email} (Preview Mode / SMTP configured for {host}:{port})")
            )
            commit()
        except Exception as db_err:
            logger.error(f"Failed to record email log notification: {db_err}")

        return False, f"SMTP dispatch error: {str(e)}"


def send_verification_email(to_email, display_name="User", action_link="https://pes-iams-placement.firebaseapp.com/__/auth/action?mode=action&oobCode=code", app_name="Placement Pro"):
    """Send Email Address Verification template email."""
    subject = f"Verify your email for {app_name}"
    body = f"""Hello {display_name},

Follow this link to verify your email address.

{action_link}

If you didn’t ask to verify this address, you can ignore this email.

Thanks,
Your {app_name} team
"""
    return send_email_smtp(to_email, subject, body)


def send_password_reset_email(to_email, display_name="User", action_link="https://pes-iams-placement.firebaseapp.com/__/auth/action?mode=action&oobCode=code", app_name="Placement Pro"):
    """Send Password Reset template email."""
    subject = f"Reset your password for {app_name}"
    body = f"""Hello {display_name},

Follow this link to reset your password.

{action_link}

If you didn’t request a password reset, you can ignore this email.

Thanks,
Your {app_name} team
"""
    return send_email_smtp(to_email, subject, body)


def send_email_change_notification(to_email, new_email, display_name="User", action_link="https://pes-iams-placement.firebaseapp.com/__/auth/action?mode=action&oobCode=code", app_name="Placement Pro"):
    """Send Email Address Change alert template email."""
    subject = f"Your sign-in email was changed for {app_name}"
    body = f"""Hello {display_name},

Your sign-in email for {app_name} was changed to {new_email}.

If you didn’t ask to change your email, follow this link to reset your sign-in email.

{action_link}

Thanks,
Your {app_name} team
"""
    return send_email_smtp(to_email, subject, body)


def send_mfa_notification(to_email, second_factor="Authenticator App", display_name="User", action_link="https://pes-iams-placement.firebaseapp.com/__/auth/action?mode=action&oobCode=code", app_name="Placement Pro"):
    """Send Multi-Factor Enrollment Notification template email."""
    subject = f"You've added 2 step verification to your {app_name} account."
    body = f"""Hello {display_name},

Your account in {app_name} has been updated with {second_factor} for 2-step verification.

If you didn't add this 2-step verification, click the link below to remove it.

{action_link}

Thanks,
Your {app_name} team
"""
    return send_email_smtp(to_email, subject, body)


def send_placement_email(student_name, register_number, department, company_name, package):
    """Send automated placement alert email via SMTP to placement distribution list."""
    distribution_list = os.environ.get('PLACEMENT_EMAIL_LIST', 'hod@college.edu,principal@college.edu')
    recipients = [email.strip() for email in distribution_list.split(',')]
    
    subject = f"New Placement Alert: {student_name} placed at {company_name}!"
    body = f"""Dear Placement Committee,

We are delighted to inform you of a new placement!

Student Name: {student_name}
Register Number: {register_number}
Department: {department}

Company: {company_name}
Package Offered: {package} LPA

Best Regards,
Placement Pro Automated System
"""
    success_count = 0
    for r in recipients:
        ok, _ = send_email_smtp(r, subject, body)
        if ok:
            success_count += 1
            
    return success_count > 0
