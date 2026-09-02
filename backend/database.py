import sqlite3
import os
from flask import g

DB_PATH = os.environ.get("DATABASE_PATH") or os.path.join(os.path.dirname(__file__), "placement_pro.db")


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


def init_sqlite_db():
    schema_path = os.path.join(os.path.dirname(__file__), "schema_sqlite.sql")
    os.makedirs(os.path.dirname(os.path.abspath(DB_PATH)), exist_ok=True)
    conn = sqlite3.connect(DB_PATH)
    with open(schema_path, "r") as f:
        conn.executescript(f.read())
    conn.commit()
    conn.close()


def init_db_pool(app):
    init_sqlite_db()


def close_db_pool(exc=None):
    conn = g.pop("db_conn", None)
    if conn is not None:
        conn.close()


def get_conn():
    if "db_conn" not in g:
        raw_conn = sqlite3.connect(DB_PATH, check_same_thread=False)
        raw_conn.row_factory = sqlite3.Row
        g.db_conn = DictConnection(raw_conn)
    return g.db_conn


def get_cursor():
    return get_conn().cursor()


def commit():
    get_conn().commit()


def rollback():
    get_conn().rollback()
