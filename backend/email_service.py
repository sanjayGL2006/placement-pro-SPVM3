import os
import base64
from email.message import EmailMessage
import logging
from database import get_cursor, commit

# Optional imports for Gmail API - requires google-api-python-client, google-auth-httplib2, google-auth-oauthlib
try:
    from google.auth.transport.requests import Request
    from google.oauth2.credentials import Credentials
    from google_auth_oauthlib.flow import InstalledAppFlow
    from googleapiclient.discovery import build
    GMAIL_API_AVAILABLE = True
except ImportError:
    GMAIL_API_AVAILABLE = False

logger = logging.getLogger("placement_pro.email")

SCOPES = ['https://www.googleapis.com/auth/gmail.send']

def get_gmail_service():
    """Authenticate and return the Gmail API service."""
    creds = None
    token_path = os.environ.get('GMAIL_TOKEN_PATH', 'token.json')
    creds_path = os.environ.get('GMAIL_CREDS_PATH', 'credentials.json')

    if os.path.exists(token_path):
        creds = Credentials.from_authorized_user_file(token_path, SCOPES)
    
    if not creds or not creds.valid:
        if creds and creds.expired and creds.refresh_token:
            creds.refresh(Request())
        else:
            if not os.path.exists(creds_path):
                logger.warning(f"Gmail credentials file {creds_path} not found. Cannot send email via API.")
                return None
            flow = InstalledAppFlow.from_client_secrets_file(creds_path, SCOPES)
            creds = flow.run_local_server(port=0)
        with open(token_path, 'w') as token:
            token.write(creds.to_json())

    return build('gmail', 'v1', credentials=creds)


def send_placement_email(student_name, register_number, department, company_name, package):
    """Send an automated placement email via Gmail API to a predefined distribution list."""
    if not GMAIL_API_AVAILABLE:
        logger.warning("Gmail API libraries not installed. Run: pip install google-api-python-client google-auth-httplib2 google-auth-oauthlib")
        return False
        
    try:
        service = get_gmail_service()
        if not service:
            return False

        # Get recipient list (hardcoded for now, could be fetched from DB)
        # E.g. HOD, Principal, Placement Committee
        distribution_list = os.environ.get('PLACEMENT_EMAIL_LIST', 'hod@college.edu,principal@college.edu')
        recipients = [email.strip() for email in distribution_list.split(',')]

        message = EmailMessage()
        
        content = f"""
        Dear Placement Committee,

        We are delighted to inform you of a new placement!

        Student Name: {student_name}
        Register Number: {register_number}
        Department: {department}
        
        Company: {company_name}
        Package Offered: {package} LPA

        Best Regards,
        Placement Pro Automated System
        """
        
        message.set_content(content)
        message['To'] = ", ".join(recipients)
        message['From'] = 'placement-pro@college.edu'
        message['Subject'] = f"New Placement Alert: {student_name} placed at {company_name}!"

        encoded_message = base64.urlsafe_b64encode(message.as_bytes()).decode()
        create_message = {'raw': encoded_message}

        send_message = (service.users().messages().send(userId="me", body=create_message).execute())
        
        logger.info(f"Placement email sent successfully. Message Id: {send_message['id']}")
        
        # Log to DB (if we had an email_logs table, for now just use notifications)
        cur = get_cursor()
        cur.execute(
            "INSERT INTO notifications (title, message, type) VALUES (%s, %s, 'success')",
            ("Email Notification Sent", f"Sent placement email for {student_name} to {len(recipients)} recipients.")
        )
        commit()
        
        return True

    except Exception as error:
        logger.error(f"An error occurred sending placement email: {error}")
        return False
