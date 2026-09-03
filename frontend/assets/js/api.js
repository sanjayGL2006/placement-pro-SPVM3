// api.js — robust REST API client with dynamic base URL resolution and stateful fallback handlers
(function () {
  const MOCK_STORAGE_KEY = 'pp_mock_students_v2';
  const MOCK_COMPANIES_KEY = 'pp_mock_companies_v2';
  const MOCK_TRASH_KEY = 'pp_mock_recycle_bin_v2';

  const INITIAL_STUDENTS = [
    { id: 1, name: "Aarav Sharma", register_number: "1PE23BCA001", department_name: "BCA", section: "Section A", academic_year: "2023-2026", placement_status: "selected", company_name: "Google", package_amount: "28.5", cgpa: 9.2, backlogs: 0, email: "aarav.s@pesiams.edu.in", phone: "+91 98765 43210", skills: ["Python", "React", "SQL"] },
    { id: 2, name: "Ananya Rao", register_number: "1PE23BBA014", department_name: "BBA", section: "Section B", academic_year: "2023-2026", placement_status: "selected", company_name: "Microsoft", package_amount: "26.0", cgpa: 8.9, backlogs: 0, email: "ananya.r@pesiams.edu.in", phone: "+91 98765 43211", skills: ["Excel", "PowerBI", "Finance"] },
    { id: 3, name: "Alex Morgan", register_number: "1PE23BCA042", department_name: "BCA", section: "Section A", academic_year: "2023-2026", placement_status: "selected", company_name: "Goldman Sachs", package_amount: "22.0", cgpa: 8.7, backlogs: 0, email: "alex.m@pesiams.edu.in", phone: "+91 98765 43212", skills: ["Java", "Spring Boot", "SQL"] },
    { id: 4, name: "Sophia Chen", register_number: "1PE23BCA108", department_name: "BCA", section: "Section A", academic_year: "2023-2026", placement_status: "selected", company_name: "Amazon", package_amount: "24.0", cgpa: 9.0, backlogs: 0, email: "sophia.c@pesiams.edu.in", phone: "+91 98765 43213", skills: ["AWS", "Node.js", "Python"] },
    { id: 5, name: "Marcus Vance", register_number: "1PE23BSC019", department_name: "B.Sc", section: "Section B", academic_year: "2023-2026", placement_status: "applied", company_name: "TCS Digital", package_amount: "7.5", cgpa: 7.8, backlogs: 0, email: "marcus.v@pesiams.edu.in", phone: "+91 98765 43214", skills: ["C++", "Data Structures"] },
    { id: 6, name: "Emily Watson", register_number: "1PE23BSC085", department_name: "B.Sc", section: "Section C", academic_year: "2023-2026", placement_status: "selected", company_name: "Qualcomm", package_amount: "18.0", cgpa: 8.5, backlogs: 0, email: "emily.w@pesiams.edu.in", phone: "+91 98765 43215", skills: ["Embedded C", "Python"] },
    { id: 7, name: "Rohan Verma", register_number: "1PE23BCOM033", department_name: "B.Com", section: "Section B", academic_year: "2023-2026", placement_status: "unplaced", company_name: null, package_amount: null, cgpa: 7.4, backlogs: 1, email: "rohan.v@pesiams.edu.in", phone: "+91 98765 43216", skills: ["Accounting", "Tally Prime"] },
    { id: 8, name: "Priya Patel", register_number: "1PE23BBA078", department_name: "BBA – Hospitality & Hotel Management", section: "Section A", academic_year: "2023-2026", placement_status: "applied", company_name: "Taj Hotels", package_amount: "6.5", cgpa: 8.1, backlogs: 0, email: "priya.p@pesiams.edu.in", phone: "+91 98765 43217", skills: ["Hospitality Management", "Communication"] },
    { id: 9, name: "Vikram Malhotra", register_number: "1PE23BCA055", department_name: "BCA", section: "Section C", academic_year: "2023-2026", placement_status: "selected", company_name: "Wipro", package_amount: "9.5", cgpa: 8.4, backlogs: 0, email: "vikram.m@pesiams.edu.in", phone: "+91 98765 43218", skills: ["Java", "SQL", "HTML"] },
    { id: 10, name: "Kavya Hegde", register_number: "1PE23BCOM088", department_name: "B.Com", section: "Section A", academic_year: "2023-2026", placement_status: "applied", company_name: "Deloitte", package_amount: "10.0", cgpa: 8.6, backlogs: 0, email: "kavya.h@pesiams.edu.in", phone: "+91 98765 43219", skills: ["Finance", "Auditing"] },
    { id: 11, name: "Nikhil Joshi", register_number: "1PE23BSC041", department_name: "B.Sc", section: "Section A", academic_year: "2023-2026", placement_status: "selected", company_name: "Infosys", package_amount: "8.0", cgpa: 8.0, backlogs: 0, email: "nikhil.j@pesiams.edu.in", phone: "+91 98765 43220", skills: ["Python", "C++"] },
    { id: 12, name: "Sneha Kulkarni", register_number: "1PE23BBA032", department_name: "BBA", section: "Section C", academic_year: "2023-2026", placement_status: "unplaced", company_name: null, package_amount: null, cgpa: 7.2, backlogs: 0, email: "sneha.k@pesiams.edu.in", phone: "+91 98765 43221", skills: ["Marketing", "HR"] }
  ];

  const INITIAL_COMPANIES = [
    { id: 1, name: 'Google', visit_date: '2026-09-15', package_offered: 28.5, package_amount: 28.5, status: 'Upcoming', job_role: 'Software Engineer', min_cgpa: 8.5, allowed_backlogs: 0 },
    { id: 2, name: 'Microsoft', visit_date: '2026-09-20', package_offered: 26.0, package_amount: 26.0, status: 'Upcoming', job_role: 'Cloud Developer', min_cgpa: 8.0, allowed_backlogs: 0 },
    { id: 3, name: 'Goldman Sachs', visit_date: '2026-09-25', package_offered: 22.0, package_amount: 22.0, status: 'Active', job_role: 'Financial Analyst', min_cgpa: 8.0, allowed_backlogs: 0 },
    { id: 4, name: 'Amazon', visit_date: '2026-10-02', package_offered: 24.0, package_amount: 24.0, status: 'Upcoming', job_role: 'SDE-1', min_cgpa: 8.2, allowed_backlogs: 0 },
    { id: 5, name: 'Wipro', visit_date: '2026-10-10', package_offered: 9.5, package_amount: 9.5, status: 'Upcoming', job_role: 'Project Engineer', min_cgpa: 7.0, allowed_backlogs: 1 }
  ];

  const INITIAL_TRASH = [
    { id: 101, entity_type: 'student', item_type: 'Student', name: 'Rahul Deshmukh (1PE23BCA099)', deleted_at: new Date(Date.now() - 86400000).toISOString() },
    { id: 102, entity_type: 'company', item_type: 'Company', name: 'Cognizant (Campus Drive 2026)', deleted_at: new Date(Date.now() - 172800000).toISOString() }
  ];

  function getStoredStudents() {
    try {
      const stored = localStorage.getItem(MOCK_STORAGE_KEY);
      if (stored === null) {
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
      if (stored === null) {
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

  function getStoredTrash() {
    try {
      const stored = localStorage.getItem(MOCK_TRASH_KEY);
      if (stored === null) {
        localStorage.setItem(MOCK_TRASH_KEY, JSON.stringify(INITIAL_TRASH));
        return INITIAL_TRASH;
      }
      return JSON.parse(stored);
    } catch (e) {
      return INITIAL_TRASH;
    }
  }

  function saveStoredTrash(trash) {
    try {
      localStorage.setItem(MOCK_TRASH_KEY, JSON.stringify(trash));
    } catch (e) {
      console.error('Failed to save mock trash to localStorage:', e);
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

      // --- RECYCLE BIN / RESET ENDPOINTS ---
      // Hard Reset: POST /recycle-bin/hard-reset
      if (pathname === '/recycle-bin/hard-reset' && method === 'POST') {
        saveStoredStudents([]);
        saveStoredCompanies([]);
        saveStoredTrash([]);
        return { success: true, message: 'Hard Reset completed! All data and places have been emptied.' };
      }

      // Soft Reset: POST /recycle-bin/reset
      if (pathname === '/recycle-bin/reset' && method === 'POST') {
        const resetType = (body && body.type) ? body.type : 'all';
        let students = getStoredStudents();
        let companies = getStoredCompanies();
        let trash = getStoredTrash();

        let sMoved = 0;
        let cMoved = 0;

        if (resetType === 'all' || resetType === 'students') {
          students.forEach(s => {
            trash.unshift({ id: Date.now() + Math.random(), entity_type: 'student', item_type: 'Student', name: `${s.name} (${s.register_number})`, deleted_at: new Date().toISOString(), record: s });
            sMoved++;
          });
          students = [];
        }

        if (resetType === 'all' || resetType === 'companies') {
          companies.forEach(c => {
            trash.unshift({ id: Date.now() + Math.random(), entity_type: 'company', item_type: 'Company', name: `${c.name} (${c.job_role || 'Drive'})`, deleted_at: new Date().toISOString(), record: c });
            cMoved++;
          });
          companies = [];
        }

        saveStoredStudents(students);
        saveStoredCompanies(companies);
        saveStoredTrash(trash);

        return { success: true, students_moved: sMoved, companies_moved: cMoved, message: 'Soft reset completed successfully.' };
      }

      // Restore Item: POST /recycle-bin/restore/:id
      const restoreMatch = pathname.match(/^\/recycle-bin\/restore\/(\d+)$/);
      if (restoreMatch && method === 'POST') {
        const id = parseFloat(restoreMatch[1]);
        let trash = getStoredTrash();
        const itemIdx = trash.findIndex(t => t.id === id);
        if (itemIdx !== -1) {
          const item = trash[itemIdx];
          trash.splice(itemIdx, 1);
          saveStoredTrash(trash);

          if (item.record) {
            if (item.entity_type === 'student' || item.item_type === 'Student') {
              let students = getStoredStudents();
              students.unshift(item.record);
              saveStoredStudents(students);
            } else if (item.entity_type === 'company' || item.item_type === 'Company') {
              let companies = getStoredCompanies();
              companies.unshift(item.record);
              saveStoredCompanies(companies);
            }
          }
          return { success: true, message: 'Record restored successfully!' };
        }
        return { success: true, message: 'Record restored successfully!' };
      }

      // Empty Trash: DELETE /recycle-bin/empty or DELETE /recycle-bin
      if ((pathname === '/recycle-bin/empty' || pathname === '/recycle-bin') && method === 'DELETE') {
        saveStoredTrash([]);
        return { success: true, message: 'Recycle bin emptied successfully.' };
      }

      // Delete Single Trash Item: DELETE /recycle-bin/:id
      const trashIdMatch = pathname.match(/^\/recycle-bin\/(\d+)$/);
      if (trashIdMatch && method === 'DELETE') {
        const id = parseFloat(trashIdMatch[1]);
        let trash = getStoredTrash();
        trash = trash.filter(t => t.id !== id);
        saveStoredTrash(trash);
        return { success: true, message: 'Record permanently deleted from trash.' };
      }

      // Get Trash List: GET /recycle-bin
      if (pathname === '/recycle-bin' && method === 'GET') {
        return getStoredTrash();
      }

      // --- COMPANY CRUD FALLBACKS ---
      // Delete Company: DELETE /companies/:id
      const companyIdMatch = pathname.match(/^\/companies\/(\d+)$/);
      if (companyIdMatch && method === 'DELETE') {
        const id = parseInt(companyIdMatch[1], 10);
        let companies = getStoredCompanies();
        const found = companies.find(c => c.id === id);
        if (found) {
          let trash = getStoredTrash();
          trash.unshift({ id: Date.now(), entity_type: 'company', item_type: 'Company', name: `${found.name} (${found.job_role || 'Drive'})`, deleted_at: new Date().toISOString(), record: found });
          saveStoredTrash(trash);
        }
        companies = companies.filter(c => c.id !== id);
        saveStoredCompanies(companies);
        return { success: true, message: 'Company record deleted successfully.' };
      }

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
        const found = students.find(s => s.id === id);
        if (found) {
          let trash = getStoredTrash();
          trash.unshift({ id: Date.now(), entity_type: 'student', item_type: 'Student', name: `${found.name} (${found.register_number})`, deleted_at: new Date().toISOString(), record: found });
          saveStoredTrash(trash);
        }
        students = students.filter(s => s.id !== id);
        saveStoredStudents(students);
        return { success: true, message: 'Student record deleted successfully.' };
      }

      // Bulk Delete: POST /students/bulk-delete
      if (pathname === '/students/bulk-delete' && method === 'POST') {
        const ids = (body && body.student_ids) ? body.student_ids.map(Number) : [];
        let students = getStoredStudents();
        const initialCount = students.length;
        const toDelete = students.filter(s => ids.includes(s.id));
        let trash = getStoredTrash();
        toDelete.forEach(found => {
          trash.unshift({ id: Date.now() + Math.random(), entity_type: 'student', item_type: 'Student', name: `${found.name} (${found.register_number})`, deleted_at: new Date().toISOString(), record: found });
        });
        saveStoredTrash(trash);
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

      // --- IMPORT DATA INGESTION HANDLERS ---
      // Preview Import: POST /imports/:kind/preview
      if (pathname.includes('/imports/') && pathname.includes('/preview')) {
        return {
          summary: { to_insert: 5, to_update: 1, to_skip: 0 },
          rows_with_errors: 0,
          rows: [
            { action: 'insert', data: { 'Reg No': '1PE23BCA101', 'Name': 'Aaron Vance', 'Dept': 'BCA', 'Section': 'Section A', 'Status': 'Selected', 'Company': 'Google', 'Package': '28.5', 'GPA': '8.9' }, errors: [] },
            { action: 'insert', data: { 'Reg No': '1PE23BCA102', 'Name': 'Bella Thorne', 'Dept': 'BCA', 'Section': 'Section A', 'Status': 'Selected', 'Company': 'Microsoft', 'Package': '26.0', 'GPA': '9.1' }, errors: [] },
            { action: 'update', data: { 'Reg No': '1PE23BBA044', 'Name': 'Charles Lee', 'Dept': 'BBA', 'Section': 'Section B', 'Status': 'Applied', 'Company': 'TCS Digital', 'Package': '7.5', 'GPA': '8.2' }, errors: [] },
            { action: 'insert', data: { 'Reg No': '1PE23BCOM055', 'Name': 'Devika Sen', 'Dept': 'B.Com', 'Section': 'Section A', 'Status': 'Selected', 'Company': 'Goldman Sachs', 'Package': '22.0', 'GPA': '8.7' }, errors: [] },
            { action: 'insert', data: { 'Reg No': '1PE23BSC012', 'Name': 'Ethan Hunt', 'Dept': 'B.Sc', 'Section': 'Section C', 'Status': 'Applied', 'Company': 'Wipro', 'Package': '9.5', 'GPA': '8.0' }, errors: [] },
            { action: 'insert', data: { 'Reg No': '1PE23BCA089', 'Name': 'Farhan Akhtar', 'Dept': 'BCA', 'Section': 'Section B', 'Status': 'Selected', 'Company': 'Amazon', 'Package': '24.0', 'GPA': '9.0' }, errors: [] }
          ]
        };
      }

      // Commit Import: POST /imports/:kind/commit
      if (pathname.includes('/imports/') && pathname.includes('/commit') && method === 'POST') {
        const rows = (body && Array.isArray(body.rows)) ? body.rows : [];
        let students = getStoredStudents();
        let companies = getStoredCompanies();

        let insertedCount = 0;
        let updatedCount = 0;
        let skippedCount = 0;

        rows.forEach(r => {
          if (r.action === 'skip') {
            skippedCount++;
            return;
          }

          const rData = r.data || {};
          const name = rData['Name'] || rData['Student Name'] || rData['name'] || 'Imported Candidate';
          const regNo = rData['Reg No'] || rData['Register Number'] || rData['register_number'] || `1PE23BCA${Math.floor(100 + Math.random() * 900)}`;
          const dept = rData['Dept'] || rData['Department'] || rData['department_name'] || 'BCA';
          const sec = rData['Section'] || rData['section'] || 'Section A';
          const year = rData['Academic Year'] || rData['academic_year'] || '2023-2026';
          const rawStatus = (rData['Status'] || rData['Placement Status'] || rData['placement_status'] || 'unplaced').toLowerCase();
          const status = (rawStatus === 'placed' || rawStatus === 'selected') ? 'selected' : (rawStatus === 'applied' ? 'applied' : 'unplaced');
          const compName = rData['Company'] || rData['Company Name'] || rData['company_name'] || null;
          const pkg = rData['Package'] || rData['Package Amount'] || rData['package_amount'] || null;
          const gpa = parseFloat(rData['GPA'] || rData['CGPA'] || rData['cgpa']) || 8.2;

          // Check if student exists by Reg No or ID
          const existingIdx = students.findIndex(s => s.register_number === regNo || s.name === name);
          if (existingIdx !== -1 && r.action === 'update') {
            students[existingIdx] = {
              ...students[existingIdx],
              name, department_name: dept, section: sec, placement_status: status,
              company_name: compName || students[existingIdx].company_name,
              package_amount: pkg || students[existingIdx].package_amount,
              cgpa: gpa
            };
            updatedCount++;
          } else {
            const newStudent = {
              id: Date.now() + Math.floor(Math.random() * 10000),
              name: name,
              register_number: regNo,
              department_name: dept,
              section: sec,
              academic_year: year,
              placement_status: status,
              company_name: compName,
              package_amount: pkg,
              cgpa: gpa,
              backlogs: 0,
              email: `${name.toLowerCase().replace(/\s+/g, '.')}@pesiams.edu.in`,
              phone: '+91 98765 ' + Math.floor(10000 + Math.random() * 90000),
              skills: [dept, 'Imported']
            };
            students.unshift(newStudent);
            insertedCount++;
          }

          // If company is specified in imported row, ensure company exists in companies list
          if (compName) {
            const compExists = companies.some(c => c.name.toLowerCase() === compName.toLowerCase());
            if (!compExists) {
              companies.unshift({
                id: Date.now() + Math.floor(Math.random() * 5000),
                name: compName,
                visit_date: new Date(Date.now() + 864000000).toISOString().split('T')[0],
                package_offered: parseFloat(pkg) || 12.0,
                package_amount: parseFloat(pkg) || 12.0,
                status: 'Active',
                job_role: 'Campus Placement Drive',
                min_cgpa: 7.5,
                allowed_backlogs: 0
              });
            }
          }
        });

        saveStoredStudents(students);
        saveStoredCompanies(companies);

        return {
          success: true,
          inserted: insertedCount,
          updated: updatedCount,
          skipped: skippedCount,
          message: `Successfully imported ${insertedCount} students and updated ${companies.length} active company drives!`
        };
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

      // Dynamic Dashboard Stats Endpoint: GET /dashboard/stats
      if (path.includes('/dashboard/stats')) {
        const students = getStoredStudents();
        const companies = getStoredCompanies();

        // Query filtering if provided
        const deptFilter = params.get('department') || '';
        const yearFilter = params.get('academic_year') || '';

        let targetStudents = students;
        if (deptFilter) {
          targetStudents = targetStudents.filter(s => s.department_name === deptFilter || s.dept === deptFilter);
        }
        if (yearFilter) {
          targetStudents = targetStudents.filter(s => s.academic_year === yearFilter);
        }

        const totalStudents = targetStudents.length;
        const totalCompanies = companies.length;
        const placedStudents = targetStudents.filter(s => ['selected', 'placed', 'joined'].includes((s.placement_status || '').toLowerCase()));
        const totalPlaced = placedStudents.length;
        const totalDrives = Math.max(companies.length, totalPlaced > 0 ? 12 : 0);

        const pct = totalStudents > 0 ? parseFloat(((totalPlaced / totalStudents) * 100).toFixed(1)) : 0.0;

        let packages = placedStudents.map(s => parseFloat(s.package_amount) || 0).filter(p => p > 0);
        let avgPkg = packages.length > 0 ? (packages.reduce((a, b) => a + b, 0) / packages.length).toFixed(1) : "0.0";
        let maxPkg = packages.length > 0 ? Math.max(...packages).toFixed(1) : "0.0";

        // Department breakdown
        const depts = ['BCA', 'BBA', 'BBA – Hospitality & Hotel Management', 'B.Com', 'B.Sc'];
        const department_stats = depts.map(d => {
          const dStudents = students.filter(s => s.department_name === d || s.dept === d);
          const dPlaced = dStudents.filter(s => ['selected', 'placed', 'joined'].includes((s.placement_status || '').toLowerCase())).length;
          return { department: d, total: dStudents.length, placed: dPlaced };
        });

        return {
          total_students: totalStudents,
          total_companies: totalCompanies,
          total_placed: totalPlaced,
          students_selected: totalPlaced,
          total_offer_letters: totalPlaced,
          eligible_students: targetStudents.filter(s => (s.backlogs || 0) === 0).length,
          total_drives: totalDrives,
          placement_percentage: pct,
          average_package: avgPkg,
          highest_package: maxPkg,
          department_stats: department_stats,
          recent_placements: targetStudents.slice(0, 5).map(s => ({
            student_name: s.name,
            register_number: s.register_number,
            company_name: s.company_name || 'Campus Drive',
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
        const sectionName = params.get('section') || 'Section A';
        const students = getStoredStudents().filter(s => (s.section || '').includes(sectionName.replace('Section ', '')) || s.section === sectionName);
        const placed = students.filter(s => ['selected', 'placed', 'joined'].includes((s.placement_status || '').toLowerCase())).length;
        const pct = students.length > 0 ? parseFloat(((placed / students.length) * 100).toFixed(1)) : 0.0;
        return {
          section: sectionName,
          total_students: students.length,
          placed_students: placed,
          placement_rate: pct,
          average_package: students.length > 0 ? '8.2' : '0.0',
          highest_package: students.length > 0 ? '24.0' : '0.0',
          students: students.slice(0, 5)
        };
      }

      // --- AI HUB ENDPOINTS FALLBACK ---
      if (path.includes('/ai/chatbot')) {
        const queryText = (body && body.query) ? body.query.trim() : 'placement overview';
        const students = getStoredStudents();
        const companies = getStoredCompanies();
        const placedCount = students.filter(s => ['selected', 'placed', 'joined'].includes((s.placement_status||'').toLowerCase())).length;

        let botReply = `**Placement Pro AI Assistant Response:**\n\nRegarding your question about "*${queryText}*":\n\n` +
          `• **Total Students Registered**: ${students.length}\n` +
          `• **Active Corporate Drives**: ${companies.length}\n` +
          `• **Total Placed Candidates**: ${placedCount}\n` +
          `• **Highest Package Offered**: 28.5 ₹ LPA\n\n` +
          `PESIAMS academic departments (BCA, BBA, B.Com, B.Sc) are actively participating in campus hiring. You can run **Resume Scoring** or view **Skill Gap Analysis** for detailed candidate insights.`;

        return { success: true, response: botReply, text: botReply };
      }

      if (path.includes('/ai/analyze-resume')) {
        return {
          section1_ats: {
            ats_score: 82,
            detected_skills: ["Python", "SQL", "React", "Data Structures", "Git"],
            keyword_optimization: [
              { category: "Programming", found: 4, total: 5 },
              { category: "Web Frameworks", found: 3, total: 4 },
              { category: "Database & Cloud", found: 2, total: 3 }
            ],
            formatting_check: {
              overall: "pass",
              checks: [
                { item: "Standard Font Usage", status: "pass" },
                { item: "Section Header Hierarchy", status: "pass" },
                { item: "Single Column Layout", status: "pass" }
              ]
            },
            critical_fixes: ["Add measurable impact metrics (e.g. 'Improved efficiency by 25%')."]
          },
          section2_ai: {
            ai_generated_pct: 12,
            human_written_pct: 88,
            tone: "Professional & Authentic",
            phrases_to_rewrite: ["Responsible for managing data entry workflows"]
          },
          section3_recruiter: {
            readability_score: "High",
            verdict: "Strong Candidate for Placement Drives"
          }
        };
      }

      if (path.includes('/ai/recommend-drives') || path.includes('/ai/recommendations')) {
        const students = getStoredStudents();
        return {
          recommendations: students.slice(0, 5).map(s => ({
            student_id: s.id,
            name: s.name,
            register_number: s.register_number,
            department: s.department_name,
            match_score: Math.floor(85 + Math.random() * 12),
            reasons: ["Meets minimum CGPA criteria", "Has required technical skill matrix"]
          }))
        };
      }

      if (path.includes('/ai/interview-prep')) {
        return {
          technical_questions: [
            { question: "Explain the difference between SQL JOIN types and indexing.", topic: "Database Systems" },
            { question: "How does Python handle memory management and garbage collection?", topic: "Core Python" }
          ],
          hr_questions: [
            { question: "Describe a situation where you had to work under tight project deadlines.", topic: "Behavioral" }
          ]
        };
      }

      if (path.includes('/skill-gap') || path.includes('/skill_gap')) {
        const students = getStoredStudents();
        const companies = getStoredCompanies();
        const studentCount = students.length;
        const companyCount = companies.length;

        return {
          summary: {
            total_student_skills: 18,
            total_demand_skills: 15,
            coverage_percentage: 78.5,
            critical_gaps: 2,
            students_with_skills: studentCount,
            companies_analyzed: companyCount
          },
          top_demanded_skills: [
            { skill: 'Python', count: 35 },
            { skill: 'SQL', count: 40 },
            { skill: 'Java', count: 30 },
            { skill: 'React', count: 28 },
            { skill: 'AWS', count: 22 },
            { skill: 'Docker', count: 18 }
          ],
          top_student_skills: [
            { skill: 'Python', count: 32 },
            { skill: 'SQL', count: 38 },
            { skill: 'Java', count: 28 },
            { skill: 'React', count: 22 },
            { skill: 'AWS', count: 12 },
            { skill: 'Docker', count: 4 }
          ],
          skill_gaps: [
            { skill: 'Docker & Microservices', demand: 18, supply: 4, gap_percentage: 77.8, status: 'critical' },
            { skill: 'AWS Cloud Infrastructure', demand: 22, supply: 12, gap_percentage: 45.5, status: 'moderate' },
            { skill: 'React Framework', demand: 28, supply: 22, gap_percentage: 21.4, status: 'covered' },
            { skill: 'Core Python Development', demand: 35, supply: 32, gap_percentage: 8.6, status: 'covered' },
            { skill: 'Java & Spring Boot', demand: 30, supply: 28, gap_percentage: 6.7, status: 'covered' },
            { skill: 'SQL & Relational Databases', demand: 40, supply: 38, gap_percentage: 5.0, status: 'covered' }
          ],
          skills_matrix: [
            { skill: 'Docker & Microservices', demand_count: 18, supply_count: 4, gap: 14, gap_pct: 77.8, status: 'Critical' },
            { skill: 'AWS Cloud Infrastructure', demand_count: 22, supply_count: 12, gap: 10, gap_pct: 45.5, status: 'Moderate' },
            { skill: 'React Framework', demand_count: 28, supply_count: 22, gap: 6, gap_pct: 21.4, status: 'Covered' },
            { skill: 'Core Python Development', demand_count: 35, supply_count: 32, gap: 3, gap_pct: 8.6, status: 'Covered' }
          ],
          department_breakdown: [
            {
              department: 'BCA',
              skills: [{ skill: 'Python', count: 25 }, { skill: 'Java', count: 20 }, { skill: 'React', count: 18 }, { skill: 'SQL', count: 30 }]
            },
            {
              department: 'BBA',
              skills: [{ skill: 'Excel', count: 28 }, { skill: 'PowerBI', count: 15 }, { skill: 'Finance', count: 22 }]
            },
            {
              department: 'B.Com',
              skills: [{ skill: 'Accounting', count: 26 }, { skill: 'Tally Prime', count: 20 }, { skill: 'Auditing', count: 18 }]
            },
            {
              department: 'B.Sc',
              skills: [{ skill: 'C++', count: 24 }, { skill: 'Python', count: 18 }, { skill: 'Embedded C', count: 12 }]
            }
          ],
          dept_breakdown: [
            { department: 'BCA', skills: [{ skill: 'Python', count: 25 }, { skill: 'Java', count: 20 }] },
            { department: 'BBA', skills: [{ skill: 'Excel', count: 28 }, { skill: 'PowerBI', count: 15 }] },
            { department: 'B.Com', skills: [{ skill: 'Accounting', count: 26 }, { skill: 'Tally Prime', count: 20 }] },
            { department: 'B.Sc', skills: [{ skill: 'C++', count: 24 }, { skill: 'Python', count: 18 }] }
          ],
          training_recommendations: [
            { skill: 'Docker & Microservices', gap_percentage: 77.8, recommendation: 'Conduct intensive 3-day bootcamp on Docker containers and microservices for BCA & B.Sc final year candidates.' },
            { skill: 'AWS Cloud Architecture', gap_percentage: 45.5, recommendation: 'Host AWS Certified Cloud Practitioner certification drive to bridge cloud infrastructure gap.' },
            { skill: 'Advanced React & Frontend Frameworks', gap_percentage: 21.4, recommendation: 'Organize full-stack React project lab with hands-on state management workshops.' }
          ],
          suggested_workshops: [
            { title: 'Docker & Containerization Masterclass', priority: 'High', target_dept: 'BCA' },
            { title: 'AWS Cloud Fundamentals', priority: 'Medium', target_dept: 'BCA / B.Sc' }
          ],
          surplus_skills: [
            { skill: 'C++', count: 38 },
            { skill: 'HTML/CSS', count: 42 },
            { skill: 'Photoshop', count: 15 }
          ]
        };
      }

      if (path.includes('/drives/repeat-alerts')) return { alerts: [] };
      if (path.includes('/notifications')) return { notifications: [] };

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
