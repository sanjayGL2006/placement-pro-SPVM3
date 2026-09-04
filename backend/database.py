import os
import tempfile
from flask import g
import sqlite3
# PostgreSQL support
try:
    # pyrefly: ignore [untyped-import]
    import psycopg2
    # pyrefly: ignore [untyped-import]
    from psycopg2 import pool as pg_pool
    # pyrefly: ignore [untyped-import]
    from psycopg2 import extras
    # pyrefly: ignore [untyped-import]
    from psycopg2.extras import RealDictCursor
except ImportError:
    pg_pool = None  # psycopg2 not installed, will fallback to SQLite


def _is_serverless():
    return bool(
        os.environ.get("VERCEL")
        or os.environ.get("AWS_LAMBDA_FUNCTION_NAME")
        or os.environ.get("LAMBDA_TASK_ROOT")
    )


def _default_db_dir():
    if _is_serverless():
        return tempfile.gettempdir()
    local_dir = os.path.dirname(os.path.abspath(__file__))
    try:
        probe = os.path.join(local_dir, ".db_write_probe")
        with open(probe, "w") as fh:
            fh.write("ok")
        os.remove(probe)
        return local_dir
    except OSError:
        return tempfile.gettempdir()


DB_PATH = os.environ.get("DATABASE_PATH") or os.path.join(_default_db_dir(), "placement_pro.db")

# PostgreSQL connection URL (e.g., postgres://user:pass@host:5432/dbname)
POSTGRES_URL = os.getenv("POSTGRES_URL")


def _schema_path():
    candidates = [
        os.path.join(os.path.dirname(os.path.abspath(__file__)), "schema_sqlite.sql"),
        os.path.join(os.getcwd(), "backend", "schema_sqlite.sql"),
        os.path.join(os.getcwd(), "schema_sqlite.sql"),
    ]
    for path in candidates:
        if os.path.isfile(path):
            return path
    return candidates[0]


class DictCursor:
    """Wrapper around sqlite3.Cursor to return dicts instead of tuples/Rows."""
    def __init__(self, cursor):
        self._cursor = cursor

    def execute(self, sql, params=()):
        # Handle NOW() / CURRENT_DATE / %s / ILIKE / RIGHT / NULLS LAST for SQLite
        sql = (
            sql.replace("NOW()", "CURRENT_TIMESTAMP")
               .replace("CURRENT_DATE", "DATE('now')")
               .replace("is_active = TRUE", "is_active = 1")
               .replace("eligible_status = TRUE", "eligible_status = 1")
               .replace("%s", "?")
               .replace("ILIKE", "LIKE")
               .replace("ilike", "like")
               .replace("RIGHT(TRIM(section), 1)", "SUBSTR(TRIM(section), -1)")
               .replace("RIGHT(TRIM(s.section), 1)", "SUBSTR(TRIM(s.section), -1)")
               .replace("NULLS LAST", "")
        )
        self._cursor.execute(sql, params)
        return self

    def fetchone(self):
        row = self._cursor.fetchone()
        if row is None:
            return None
        return dict(row)

    def fetchall(self):
        rows = self._cursor.fetchall()
        return [dict(r) for r in rows]

    @property
    def lastrowid(self):
        return self._cursor.lastrowid

    @property
    def rowcount(self):
        return self._cursor.rowcount

    def close(self):
        self._cursor.close()


class DictConnection:
    """Wrapper around sqlite3.Connection."""
    def __init__(self, conn):
        self._conn = conn

    def cursor(self, *args, **kwargs):
        return DictCursor(self._conn.cursor())

    def commit(self):
        self._conn.commit()

    def rollback(self):
        self._conn.rollback()

    def close(self):
        self._conn.close()


def init_sqlite_db(force=False):
    schema_path = _schema_path()
    os.makedirs(os.path.dirname(os.path.abspath(DB_PATH)) or ".", exist_ok=True)
    keep_existing = (
        os.path.exists(DB_PATH)
        and os.path.getsize(DB_PATH) > 0
        and not force
        and _is_serverless()
    )
    if keep_existing:
        return
    if os.path.exists(DB_PATH):
        try:
            os.remove(DB_PATH)
        except OSError:
            pass
    if not os.path.isfile(schema_path):
        raise FileNotFoundError(f"SQLite schema not found: {schema_path}")
    conn = sqlite3.connect(DB_PATH)
    with open(schema_path, "r", encoding="utf-8") as f:
        conn.executescript(f.read())
    conn.commit()
    conn.close()


def init_db_pool(app):
    init_sqlite_db()


def close_db_pool(exc=None):
    conn = g.pop("db_conn", None)
    if conn is not None:
        # Return PostgreSQL connection to pool if applicable
        if POSTGRES_URL and pg_pool and hasattr(g, "pg_pool"):
            g.pg_pool.putconn(conn._conn)
        else:
            conn.close()


def get_conn():
    if "db_conn" not in g:
        # Use PostgreSQL if URL is provided and psycopg2 is available
        if POSTGRES_URL and pg_pool:
            if not hasattr(g, "pg_pool"):
                # Initialize a threaded connection pool (min 1, max 10)
                g.pg_pool = pg_pool.ThreadedConnectionPool(1, 10, dsn=POSTGRES_URL)
            conn = g.pg_pool.getconn()
            # psycopg2 returns connection with dict cursor support via RealDictCursor
            # We'll wrap it in a simple adapter for compatibility
            class PGDictConnection:
                def __init__(self, conn):
                    self._conn = conn
                def cursor(self, *args, **kwargs):
                     return conn.cursor(cursor_factory=RealDictCursor)
                def commit(self):
                    self._conn.commit()
                def rollback(self):
                    self._conn.rollback()
                def close(self):
                    self._conn.close()
            g.db_conn = PGDictConnection(conn)
        else:
            # Fallback to SQLite
            if not os.path.exists(DB_PATH) or os.path.getsize(DB_PATH) == 0:
                init_sqlite_db()
            # pyrefly: ignore [unknown-name]
            raw_conn = sqlite3.connect(DB_PATH, check_same_thread=False)
            raw_conn.execute("PRAGMA foreign_keys = ON;")
            raw_conn.row_factory = sqlite3.Row
            g.db_conn = DictConnection(raw_conn)
    return g.db_conn


def get_cursor():
    return get_conn().cursor()


def commit():
    get_conn().commit()


def rollback():
    get_conn().rollback()
