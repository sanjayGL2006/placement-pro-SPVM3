import os
import logging

logger = logging.getLogger("placement_pro.firebase")

FIREBASE_ADMIN_AVAILABLE = False
try:
    import firebase_admin
    from firebase_admin import credentials, messaging
    FIREBASE_ADMIN_AVAILABLE = True
except ImportError:
    FIREBASE_ADMIN_AVAILABLE = False


def init_firebase_admin(app=None):
    """Initialize Firebase Admin SDK for project pes-iams-placement."""
    if not FIREBASE_ADMIN_AVAILABLE:
        logger.info("firebase_admin Python package not installed. Using local notification fallback.")
        return False

    try:
        if not firebase_admin._apps:
            cred_path = os.environ.get("FIREBASE_CREDENTIALS_PATH") or os.path.join(
                os.path.dirname(__file__), "serviceAccountKey.json"
            )
            if os.path.exists(cred_path):
                cred = credentials.Certificate(cred_path)
                firebase_admin.initialize_app(cred, {
                    "projectId": os.environ.get("FIREBASE_PROJECT_ID", "pes-iams-placement"),
                    "serviceAccountId": "firebase-adminsdk-fbsvc@pes-iams-placement.iam.gserviceaccount.com"
                })
                logger.info("Firebase Admin SDK initialized successfully with service account.")
            else:
                try:
                    firebase_admin.initialize_app(options={
                        "projectId": os.environ.get("FIREBASE_PROJECT_ID", "pes-iams-placement")
                    })
                    logger.info("Firebase Admin SDK initialized with project default options.")
                except Exception as default_err:
                    logger.warning(f"Service account certificate ({cred_path}) not found. ({default_err})")
                    return False
        return True
    except Exception as e:
        logger.error(f"Failed to initialize Firebase Admin SDK: {e}")
        return False


def send_fcm_notification(target_token, title, body, data=None):
    """Send FCM Cloud Messaging V1 push notification to a device token."""
    if not FIREBASE_ADMIN_AVAILABLE or not firebase_admin._apps:
        return False, "Firebase Admin SDK not initialized"

    try:
        message = messaging.Message(
            notification=messaging.Notification(
                title=title,
                body=body,
            ),
            data=data or {},
            token=target_token,
        )
        response = messaging.send(message)
        logger.info(f"FCM push notification sent. Message ID: {response}")
        return True, response
    except Exception as e:
        logger.error(f"FCM push notification error: {e}")
        return False, str(e)
