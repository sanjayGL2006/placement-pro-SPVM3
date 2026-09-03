import unittest
import json
import os
import sys

# Add backend dir to sys.path
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from app import create_app
from database import init_sqlite_db, get_conn, commit

class PlacementProTestCase(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        # Initialize test app
        cls.app = create_app()
        cls.client = cls.app.test_client()
        init_sqlite_db()

    def test_01_health_check(self):
        """Unit Test: Health Check Endpoint"""
        response = self.client.get('/api/health')
        self.assertEqual(response.status_code, 200)
        data = response.get_json()
        self.assertEqual(data.get('status'), 'ok')

    def test_02_dashboard_stats(self):
        """API Test: Dashboard Stats"""
        response = self.client.get('/api/dashboard/stats')
        self.assertEqual(response.status_code, 200)
        data = response.get_json()
        self.assertIn('total_students', data)
        self.assertIn('total_companies', data)

    def test_03_dashboard_filters(self):
        """API Test: Dashboard Filters"""
        response = self.client.get('/api/dashboard/filters')
        self.assertEqual(response.status_code, 200)
        data = response.get_json()
        self.assertIn('departments', data)
        self.assertIn('academic_years', data)

    def test_04_dashboard_sections(self):
        """API Test: Section Stats Query"""
        response = self.client.get('/api/dashboard/sections?section=Section%20A')
        self.assertEqual(response.status_code, 200)
        data = response.get_json()
        self.assertIn('total_students', data)

    def test_05_students_list(self):
        """API Test: List Students"""
        response = self.client.get('/api/students?page=1&per_page=10')
        self.assertEqual(response.status_code, 200)
        data = response.get_json()
        self.assertIn('students', data)
        self.assertIn('total', data)

    def test_06_companies_list(self):
        """API Test: List Companies"""
        response = self.client.get('/api/companies')
        self.assertEqual(response.status_code, 200)
        data = response.get_json()
        self.assertIsInstance(data, list)

    def test_07_repeat_alerts(self):
        """API Test: Drive Repeat Alerts"""
        response = self.client.get('/api/drives/repeat-alerts')
        self.assertEqual(response.status_code, 200)
        data = response.get_json()
        self.assertIn('alerts', data)

    def test_08_notifications_list(self):
        """API Test: Notifications Endpoint"""
        response = self.client.get('/api/notifications')
        self.assertEqual(response.status_code, 200)
        data = response.get_json()
        self.assertIn('notifications', data)

    def test_09_reports_summary(self):
        """API Test: Reports Endpoint"""
        response = self.client.get('/api/reports/students?format=csv')
        self.assertEqual(response.status_code, 200)

    def test_10_recycle_bin(self):
        """API Test: Recycle Bin Listing"""
        response = self.client.get('/api/recycle-bin')
        self.assertEqual(response.status_code, 200)

    def test_11_bulk_push_skips_ineligible_students(self):
        """Bulk push should process valid students in a mixed batch without aborting on one invalid row."""
        init_sqlite_db()
        response = self.client.post('/api/students/bulk-push', json={
            'student_ids': [1, 8],
            'company_id': 5,
            'stage': 'applied'
        })
        self.assertEqual(response.status_code, 200, response.get_data(as_text=True))
        data = response.get_json()
        self.assertEqual(data['pushed_count'], 1)
        self.assertIn('skipped', data)

    def test_12_auth_me_endpoint(self):
        """API Test: Current User Endpoint (/api/auth/me)"""
        response = self.client.get('/api/auth/me')
        self.assertEqual(response.status_code, 200)
        data = response.get_json()
        self.assertIn('email', data)
        self.assertIn('role', data)

    def test_13_auth_login_invalid(self):
        """API Test: Auth Login with Invalid Credentials"""
        response = self.client.post('/api/auth/login', json={
            'email': 'nonexistent@pesiams.edu.in',
            'password': 'WrongPassword123!'
        })
        self.assertEqual(response.status_code, 401)
        data = response.get_json()
        self.assertIn('error', data)

    def test_14_recycle_bin_soft_reset(self):
        """API Test: Soft Reset Data to Recycle Bin"""
        response = self.client.post('/api/recycle-bin/reset', json={'type': 'all'})
        self.assertEqual(response.status_code, 200)
        data = response.get_json()
        self.assertIn('students_moved', data)

    def test_15_recycle_bin_hard_reset(self):
        """API Test: Hard Reset Database Data"""
        response = self.client.post('/api/recycle-bin/hard-reset')
        self.assertEqual(response.status_code, 200)
        data = response.get_json()
        self.assertIn('message', data)
        # Re-initialize DB seed for subsequent tests or clean state
        init_sqlite_db()


if __name__ == '__main__':
    unittest.main()
