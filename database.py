"""Top-level shim for database utilities.

This module re-exports functions from the actual backend implementation at
`backend/database.py`. It allows route modules to use a simple import:

    from database import get_cursor, commit, rollback

without needing to know the internal package structure.
"""

from backend.database import get_cursor, commit, rollback, init_db_pool, close_db_pool, get_conn

__all__ = ["get_cursor", "commit", "rollback", "init_db_pool", "close_db_pool", "get_conn"]
