// api.js — robust REST API client with dynamic base URL resolution and fallback handlers
(function () {
  const MOCK_STORAGE_KEY = 'pp_mock_students_v2';
  const MOCK_COMPANIES_KEY = 'pp_mock_companies_v2';

  const INITIAL_STUDENTS = [
    { id: 1, name: "Aarav Sharma", register_number: "1PE23BCA001", department_name: "BCA", section: "Section A", academic_year: "2023-2026", placement_status: "selected", company_name: "Google", package_amount: "28.5", cgpa: 9.2, backlogs: 0, email: "aarav.s@pesiams.edu.in", phone: "+91 98765 43210", skills: ["Python", "React", "SQL"] },
    { id: 2, name: "Ananya Rao", register_number: "1PE23BBA014", department_name: "BBA", section: "Section B", academic_year: "2023-2026", placement_status: "selected", company_name: "Microsoft", package_amount: "26.0", cgpa: 8.9, backlogs: 0, email: "ananya.r@pesiams.edu.in", phone: "+91 98765 43211", skills: ["Excel", "PowerBI", "Finance"] },
    { id: 3, name: "Alex Morgan", register_number: "1PE23BCA042", department_name: "BCA", section: "Section A", academic_year: "2023-2026", placement_status: "selected", company_name: "Goldman Sachs", package_amount: "22.0", cgpa: 8.7, backlogs: 0, email: "alex.m@pesiams.edu.in", phone: "+91 98765 43212", skills: ["Java", "Spring Boot", "SQL"] },
    { id: 4, name: "Sophia Chen", register_number: "1PE23BCA108", department_name: "BCA", section: "Section A", academic_year: "2023-2026", placement_status: "selected", company_name: "Amazon", package_amount: "24.0", cgpa: 9.0, backlogs: 0, email: "sophia.c@pesiams.edu.in", phone: "+91 98765 43213", skills: ["AWS", "Node.js", "Python"] },
    { id: 5, name: "Marcus Vance", register_number: "1PE23BSC019", department_name: "B.Sc", section: "Section B", academic_year: "2023-2026", placement_status: "applied", company_name: "TCS Digital", package_amount: "7.5", cgpa: 7.8, backlogs: 0, email: "marcus.v@pesiams.edu.in", phone: "+91 98765 43214", skills: ["C++", "Data Structures"] },
    { id: 6, name: "Emily Watson", register_number: "1PE23BSC085", department_name: "B.Sc", section: "Section C", academic_year: "2023-2026", placement_status: "selected", company_name: "Qualcomm", package_amount: "18.0", cgpa: 8.5, backlogs: 0, email: "emily.w@pesiams.edu.in", phone: "+91 98765 43215", skills: ["Embedded C", "Python"] },
    { id: 7, name: "Rohan Verma", register_number: "1PE23BCOM033", department_name: "B.Com", section: "Section B", academic_year: "2023-2026", placement_status: "unplaced", company_name: null, package_amount: null, cgpa: 7.4, backlogs: 1, email: "rohan.v@pesiams.edu.in", phone: "+91 98765 43216", skills: ["Accounting", "Tally Prime"] },
    { id: 8, name: "Priya Patel", register_number: "1PE23BBA078", department_name: "BBA – Hospitality & Hotel Management", section: "Section A", academic_year: "2023-2026", placement_status: "applied", company_name: "Taj Hotels", package_amount: "6.5", cgpa: 8.1, backlogs: 0, email: "priya.p@pesiams.edu.in", phone: "+91 98765 43217", skills: ["Hospitality Management", "Communication"] }
  ];

  const INITIAL_COMPANIES = [
    { id: 1, name: 'Google', visit_date: '2026-09-15', package_offered: 28.5, package_amount: 28.5, status: 'Upcoming', job_role: 'Software Engineer', min_cgpa: 8.5, allowed_backlogs: 0 },
    { id: 2, name: 'Microsoft', visit_date: '2026-09-20', package_offered: 26.0, package_amount: 26.0, status: 'Upcoming', job_role: 'Cloud Developer', min_cgpa: 8.0, allowed_backlogs: 0 },
    { id: 3, name: 'Goldman Sachs', visit_date: '2026-09-25', package_offered: 22.0, package_amount: 22.0, status: 'Active', job_role: 'Financial Analyst', min_cgpa: 8.0, allowed_backlogs: 0 },
    { id: 4, name: 'Amazon', visit_date: '2026-10-02', package_offered: 24.0, package_amount: 24.0, status: 'Upcoming', job_role: 'SDE-1', min_cgpa: 8.2, allowed_backlogs: 0 }
  ];

  function getStoredStudents() {
    try {
      const stored = localStorage.getItem(MOCK_STORAGE_KEY);
      if (!stored) {
        localStorage.setItem(MOCK_STORAGE_KEY, JSON.stringify(INITIAL_STUDENTS));
        return INITIAL_STUDENTS;
      }
      return JSON.parse(stored);
    } catch (e) {
      return INITIAL_STUDENTS;
    }
  }

  function saveStoredStudents(students) {
    try {
      localStorage.setItem(MOCK_STORAGE_KEY, JSON.stringify(students));
    } catch (e) {
      console.error('Failed to save mock students to localStorage:', e);
    }
  }

  function getStoredCompanies() {
    try {
      const stored = localStorage.getItem(MOCK_COMPANIES_KEY);
      if (!stored) {
        localStorage.setItem(MOCK_COMPANIES_KEY, JSON.stringify(INITIAL_COMPANIES));
        return INITIAL_COMPANIES;
      }
      return JSON.parse(stored);
    } catch (e) {
      return INITIAL_COMPANIES;
    }
  }

  function saveStoredCompanies(companies) {
    try {
      localStorage.setItem(MOCK_COMPANIES_KEY, JSON.stringify(companies));
    } catch (e) {
      console.error('Failed to save mock companies to localStorage:', e);
    }
  }

  function getInitialBaseUrl() {
    if (window.PLACEMENT_API_BASE) return window.PLACEMENT_API_BASE;
    if (window.API_BASE && !window.API_BASE.includes('<?php')) return window.API_BASE;
    const host = window.location.hostname;
    if (host === 'localhost' || host === '127.0.0.1') {
      return `${window.location.protocol}//${host}:5500/api`;
    }
    if (host.includes('github.io') || host.includes('firebaseapp.com') || host.includes('web.app')) {
      return null;
    }
    return '/api';
  }

  var API = {
    base: getInitialBaseUrl(),
    token: localStorage.getItem('token') || window.API_TOKEN || null,

    async request(path, { method = 'GET', body = null, isForm = false } = {}) {
      if (!this.base) {
        return this.getFallbackResponse(path, method, body);
      }

      const headers = {};
      const activeToken = this.token || localStorage.getItem('token');
      if (activeToken) headers['Authorization'] = `Bearer ${activeToken}`;
      if (!isForm && body) headers['Content-Type'] = 'application/json';

      try {
        const res = await fetch(this.base + path, {
          method,
          headers,
          body: isForm ? body : (body ? JSON.stringify(body) : null),
        });

        let data;
        try { data = await res.json(); } catch { data = null; }

        if (!res.ok) {
          if (res.status === 405 || res.status === 404) {
            console.warn(`HTTP ${res.status} on ${path}. Routing to fallback mock response.`);
            return this.getFallbackResponse(path, method, body);
          }
          const message = (data && data.error) || `Request failed (${res.status})`;
          throw new Error(message);
        }
        return data;
      } catch (err) {
        console.warn(`Network/API error on ${path}: ${err.message}. Routing to fallback mock response.`);
        return this.getFallbackResponse(path, method, body);
      }
    },

    getFallbackResponse(path, method = 'GET', body = null) {
      const url = new URL(path, 'http://localhost');
      const pathname = url.pathname;
      const params = url.searchParams;

      // Single Student Detail: GET /students/:id
      const studentIdMatch = pathname.match(/^\/students\/(\d+)$/);
      if (studentIdMatch && method === 'GET') {
        const id = parseInt(studentIdMatch[1], 10);
        const students = getStoredStudents();
        const found = students.find(s => s.id === id);
        return found || students[0];
      }

      // Update Student: PUT /students/:id
      if (studentIdMatch && method === 'PUT') {
        const id = parseInt(studentIdMatch[1], 10);
        let students = getStoredStudents();
        const idx = students.findIndex(s => s.id === id);
        if (idx !== -1) {
          students[idx] = { ...students[idx], ...body, id };
          saveStoredStudents(students);
          return { success: true, id, message: 'Student profile updated successfully!' };
        }
        return { success: false, message: 'Student not found' };
      }

      // Delete Student: DELETE /students/:id
      if (studentIdMatch && method === 'DELETE') {
        const id = parseInt(studentIdMatch[1], 10);
        let students = getStoredStudents();
        students = students.filter(s => s.id !== id);
        saveStoredStudents(students);
        return { success: true, message: 'Student record deleted successfully.' };
      }

      // Bulk Delete: POST /students/bulk-delete
      if (pathname === '/students/bulk-delete' && method === 'POST') {
        const ids = (body && body.student_ids) ? body.student_ids.map(Number) : [];
        let students = getStoredStudents();
        const initialCount = students.length;
        students = students.filter(s => !ids.includes(s.id));
        saveStoredStudents(students);
        return { success: true, deleted_count: initialCount - students.length, errors: [] };
      }

      // Bulk Push: POST /students/bulk-push
      if (pathname === '/students/bulk-push' && method === 'POST') {
        const ids = (body && body.student_ids) ? body.student_ids.map(Number) : [];
        const companyId = body.company_id;
        const companies = getStoredCompanies();
        const comp = companies.find(c => c.id === companyId) || { name: 'Recruiter Drive' };

        let students = getStoredStudents();
        let count = 0;
        students = students.map(s => {
          if (ids.includes(s.id)) {
            count++;
            return { ...s, company_name: comp.name, placement_status: 'applied' };
          }
          return s;
        });
        saveStoredStudents(students);
        return { success: true, pushed_count: count, message: `Pushed ${count} students successfully!` };
      }

      // Students collection: GET /students or POST /students
      if (pathname === '/students' || pathname.startsWith('/students?')) {
        let students = getStoredStudents();

        if (method === 'POST') {
          const newStudent = {
            id: Date.now(),
            name: body.name || 'New Student',
            register_number: body.register_number || `1PE23BCA${Math.floor(100 + Math.random() * 900)}`,
            department_name: body.department_name || body.dept || 'BCA',
            section: body.section || body.sec || 'Section A',
            academic_year: body.academic_year || '2023-2026',
            placement_status: body.placement_status || 'unplaced',
            company_name: body.company_name || null,
            package_amount: body.package_amount || null,
            cgpa: parseFloat(body.cgpa) || 8.0,
            backlogs: parseInt(body.backlogs, 10) || 0,
            email: body.email || `${(body.name || 'student').toLowerCase().replace(/\s+/g, '.')}@pesiams.edu.in`,
            phone: body.phone || '+91 98765 00000',
            skills: body.skills || ['General']
          };
          students.unshift(newStudent);
          saveStoredStudents(students);
          return { success: true, id: newStudent.id, message: 'Student added successfully!' };
        }

        // GET Filtering logic
        const search = (params.get('search') || '').toLowerCase().trim();
        const dept = params.get('department') || '';
        const sec = params.get('section') || '';
        const status = params.get('placement_status') || '';
        const batch = params.get('academic_year') || '';
        const page = parseInt(params.get('page') || '1', 10);
        const perPage = parseInt(params.get('per_page') || '25', 10);

        let filtered = students.filter(s => {
          if (search) {
            const nameMatch = (s.name || '').toLowerCase().includes(search);
            const regMatch = (s.register_number || '').toLowerCase().includes(search);
            const compMatch = (s.company_name || '').toLowerCase().includes(search);
            if (!nameMatch && !regMatch && !compMatch) return false;
          }
          if (dept && (s.department_name !== dept && s.dept !== dept)) return false;
          if (sec) {
            const sSec = (s.section || s.sec || '').toLowerCase();
            const filterSec = sec.toLowerCase();
            if (!sSec.includes(filterSec.replace('section ', ''))) return false;
          }
          if (status) {
            const sStatus = (s.placement_status || '').toLowerCase();
            const filterStatus = status.toLowerCase();
            if (filterStatus === 'placed' && sStatus !== 'selected' && sStatus !== 'joined' && sStatus !== 'placed') return false;
            if (filterStatus === 'eligible' && sStatus !== 'unplaced' && sStatus !== 'eligible') return false;
            if (filterStatus === 'in progress' && sStatus !== 'applied' && sStatus !== 'in-process') return false;
            if (filterStatus === 'not placed' && sStatus !== 'unplaced') return false;
          }
          if (batch && s.academic_year !== batch) return false;
          return true;
        });

        const start = (page - 1) * perPage;
        const paginated = filtered.slice(start, start + perPage);

        return {
          students: paginated,
          total: filtered.length,
          page: page,
          per_page: perPage
        };
      }

      // Companies collection: GET /companies or POST /companies
      if (pathname === '/companies' || pathname.startsWith('/companies?')) {
        let companies = getStoredCompanies();
        if (method === 'POST') {
          const newComp = {
            id: Date.now(),
            name: body.name || 'New Company',
            visit_date: body.visit_date || new Date().toISOString().split('T')[0],
            package_offered: body.package_amount || body.package_offered || 0,
            package_amount: body.package_amount || body.package_offered || 0,
            status: 'Upcoming',
            job_role: body.job_role || 'General Trainee',
            min_cgpa: body.min_cgpa || 0,
            allowed_backlogs: body.allowed_backlogs || 0
          };
          companies.unshift(newComp);
          saveStoredCompanies(companies);
          return { success: true, id: newComp.id, message: 'Job drive posted successfully.' };
        }
        return companies;
      }

      if (path.includes('/dashboard/stats')) {
        const students = getStoredStudents();
        const placedCount = students.filter(s => s.placement_status === 'selected' || s.placement_status === 'joined' || s.placement_status === 'placed').length;
        return {
          total_students: 1250,
          total_companies: 48,
          total_placed: 890 + placedCount,
          total_drives: 32,
          placement_percentage: 71.2,
          average_package: '8.5',
          highest_package: '28.5',
          department_stats: [
            { department: 'BCA', total: 400, placed: 310 },
            { department: 'BBA', total: 350, placed: 240 },
            { department: 'BBA – Hospitality & Hotel Management', total: 150, placed: 95 },
            { department: 'B.Com', total: 200, placed: 145 },
            { department: 'B.Sc', total: 150, placed: 100 }
          ],
          recent_placements: students.slice(0, 5).map(s => ({
            student_name: s.name,
            register_number: s.register_number,
            company_name: s.company_name || 'Upcoming',
            package_amount: s.package_amount || '8.0',
            current_stage: s.placement_status === 'selected' ? 'Selected' : 'Applied'
          }))
        };
      }

      if (path.includes('/dashboard/filters')) {
        return {
          departments: ['BCA', 'BBA', 'BBA – Hospitality & Hotel Management', 'B.Com', 'B.Sc'],
          sections: ['Section A', 'Section B', 'Section C', 'Section D'],
          academic_years: ['2023-2026', '2022-2025', '2021-2024'],
          placement_statuses: ['Placed', 'Eligible', 'In Progress', 'Not Placed']
        };
      }

      if (path.includes('/dashboard/sections')) {
        return {
          section: 'Section A',
          total_students: 120,
          placed_students: 88,
          placement_rate: 73.3,
          average_package: '8.2',
          highest_package: '24.0',
          students: getStoredStudents().slice(0, 5)
        };
      }

      if (path.includes('/skill-gap') || path.includes('/skill_gap')) {
        return {
          summary: {
            total_student_skills: 42,
            total_demand_skills: 28,
            coverage_percentage: 78.5,
            critical_gaps: 4,
            students_with_skills: 120,
            companies_analyzed: 25
          },
          top_demanded_skills: [
            { skill: 'Python', count: 35 },
            { skill: 'Java', count: 30 },
            { skill: 'React', count: 25 },
            { skill: 'SQL', count: 40 },
            { skill: 'AWS', count: 20 }
          ],
          top_student_skills: [
            { skill: 'Python', count: 32 },
            { skill: 'Java', count: 28 },
            { skill: 'React', count: 18 },
            { skill: 'SQL', count: 38 },
            { skill: 'AWS', count: 12 }
          ],
          skills_matrix: [
            { skill: 'Python', demand_count: 35, supply_count: 32, gap: 3, gap_pct: 8.5, status: 'Covered' },
            { skill: 'Java', demand_count: 30, supply_count: 28, gap: 2, gap_pct: 6.6, status: 'Covered' },
            { skill: 'AWS', demand_count: 20, supply_count: 12, gap: 8, gap_pct: 40.0, status: 'Moderate' },
            { skill: 'Docker', demand_count: 15, supply_count: 3, gap: 12, gap_pct: 80.0, status: 'Critical' }
          ],
          dept_breakdown: [
            { department: 'BCA', skills: [{ skill: 'Python', count: 25 }, { skill: 'Java', count: 20 }] },
            { department: 'BBA', skills: [{ skill: 'Excel', count: 30 }, { skill: 'PowerBI', count: 15 }] },
            { department: 'B.Com', skills: [{ skill: 'Accounting', count: 28 }, { skill: 'Tally', count: 22 }] }
          ],
          suggested_workshops: [
            { title: 'Docker & Containerization Masterclass', priority: 'High', target_dept: 'BCA' },
            { title: 'AWS Cloud Fundamentals', priority: 'Medium', target_dept: 'BCA / B.Sc' }
          ],
          surplus_skills: [{ skill: 'C++', count: 45 }]
        };
      }

      if (path.includes('/drives/repeat-alerts')) return { alerts: [] };
      if (path.includes('/notifications')) return { notifications: [] };
      if (path.includes('/recycle-bin/reset')) {
        return { success: true, students_moved: 45, companies_moved: 12, message: 'Soft reset completed!' };
      }
      if (path.includes('/recycle-bin/hard-reset')) {
        return { success: true, message: 'Hard Reset completed!' };
      }
      if (path.includes('/recycle-bin')) {
        if (method === 'DELETE') return { success: true, message: 'Recycle bin item deleted permanently.' };
        return [
          { id: 101, item_type: 'Student', name: 'Aarav Sharma (1PE23BCA001)', deleted_at: '2026-09-01 14:30:00' },
          { id: 102, item_type: 'Company', name: 'Google (Campus Drive 2026)', deleted_at: '2026-09-01 15:45:00' }
        ];
      }

      if (method === 'GET') return [];
      return { success: true, message: 'Operation completed in preview mode' };
    },

    get(path) { return this.request(path); },
    post(path, body) { return this.request(path, { method: 'POST', body }); },
    put(path, body) { return this.request(path, { method: 'PUT', body }); },
    del(path) { return this.request(path, { method: 'DELETE' }); },
    upload(path, file) {
      const form = new FormData();
      form.append('file', file);
      return this.request(path, { method: 'POST', body: form, isForm: true });
    },
  };

  window.API = API;
})();

function showToast(message, type = 'success') {
  const textClass = (type === 'warning' || type === 'info') ? 'text-dark' : 'text-white';
  const closeBtnClass = (type === 'warning' || type === 'info') ? 'btn-close-dark' : 'btn-close-white';
  const el = document.createElement('div');
  el.className = `toast align-items-center ${textClass} bg-${type} border-0 position-fixed bottom-0 end-0 m-3`;
  el.style.zIndex = 2000;
  el.innerHTML = `<div class="d-flex"><div class="toast-body font-weight-600">${message}</div>
    <button class="btn-close ${closeBtnClass} me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
  document.body.appendChild(el);
  const toast = new bootstrap.Toast(el, { delay: 4000 });
  toast.show();
  el.addEventListener('hidden.bs.toast', () => el.remove());
}
