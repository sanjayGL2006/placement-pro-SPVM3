<?php require_once 'config.php';
require_login(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Students Directory — Placement Pro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>

  <?php include 'partials/nav.php'; ?>

  <main id="main-wrapper">

    <!-- Header Area & View Toggle -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="h3 font-weight-800 mb-1">Students Directory</h2>
        <p class="text-muted small mb-0">Manage registered candidates and placement track status</p>
      </div>

      <!-- Bulk Actions & List vs Grid Toggle Switch -->
      <div class="d-flex align-items-center gap-2">
        <div class="dropdown d-none" id="bulkActionsDropdown">
          <button class="btn btn-pp-outline btn-sm dropdown-toggle font-weight-700 py-1.5 px-3 rounded-3" type="button"
            data-bs-toggle="dropdown" aria-expanded="false"
            style="color: var(--pp-primary) !important; border-color: var(--pp-primary) !important;">
            <i class="fa-solid fa-square-check me-1"></i> Bulk Actions (<span id="selectedCountText">0</span>)
          </button>
          <ul class="dropdown-menu shadow-sm border-0 rounded-3 mt-1">
            <li><a class="dropdown-item py-2" href="#" onclick="event.preventDefault(); openBulkPushModal();"><i
                  class="fa-solid fa-paper-plane me-2 text-primary"></i> Push to Company</a></li>
            <li><a class="dropdown-item py-2 text-danger" href="#"
                onclick="event.preventDefault(); triggerBulkDelete();"><i class="fa-solid fa-trash me-2"></i> Bulk
                Delete</a></li>
            <li><a class="dropdown-item py-2" href="#" onclick="event.preventDefault(); exportSelectedStudents();"><i
                  class="fa-solid fa-file-excel me-2 text-success"></i> Export Selected</a></li>
          </ul>
        </div>

        <div class="bg-white p-1 rounded-3 border d-flex gap-1 shadow-sm">
          <button class="btn btn-sm px-3 font-weight-600 rounded-2 active" id="btnListView"
            style="background-color: var(--pp-primary-light); color: var(--pp-primary-dark); border: 1px solid var(--pp-primary);"
            onclick="toggleView('list')">
            <i class="fa-solid fa-list me-1"></i> List
          </button>
          <button class="btn btn-sm px-3 font-weight-600 text-muted rounded-2 border-0" id="btnGridView"
            onclick="toggleView('grid')">
            <i class="fa-solid fa-border-all me-1"></i> Grid
          </button>
        </div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="pp-card mb-4 p-3">
      <div class="row g-3 align-items-center">
        <!-- Search Input -->
        <div class="col-12 col-md-3">
          <div class="position-relative">
            <i class="fa-solid fa-magnifying-glass position-absolute text-muted"
              style="left: 0.85rem; top: 50%; transform: translateY(-50%); font-size: 0.85rem;"></i>
            <input type="text" class="form-control-pp w-100 ps-5" id="search" placeholder="Search name or ID..."
              onkeyup="loadStudents(1)">
          </div>
        </div>

        <!-- Department Dropdown -->
        <div class="col-6 col-md-2">
          <select class="form-select-pp w-100" id="deptFilter" onchange="loadStudents(1)">
            <option value="">Department</option>
          </select>
        </div>

        <!-- Section Dropdown -->
        <div class="col-6 col-md-2">
          <select class="form-select-pp w-100" id="sectionFilter" onchange="loadStudents(1)">
            <option value="">Section</option>
          </select>
        </div>

        <!-- Status Dropdown -->
        <div class="col-6 col-md-2">
          <select class="form-select-pp w-100" id="statusFilter" onchange="loadStudents(1)">
            <option value="">All Statuses</option>
            <option value="selected">Placed</option>
            <option value="not_placed">Unplaced</option>
            <option value="applied">In-process</option>
          </select>
        </div>

        <!-- Batch Year Dropdown -->
        <div class="col-6 col-md-1">
          <select class="form-select-pp w-100" id="batchFilter" onchange="loadStudents(1)">
            <option value="">Batch Year</option>
          </select>
        </div>

        <!-- Showing Count Text -->
        <div class="col-12 col-md-2 text-md-end">
          <span class="text-muted small font-weight-600" id="showingText">Showing 0 of 0 students</span>
        </div>
      </div>
    </div>

    <!-- Data Table Container -->
    <div class="pp-card p-0 overflow-hidden mb-4" id="listViewContainer">
      <div class="table-responsive">
        <table class="pp-table">
          <thead>
            <tr>
              <th style="width: 40px; padding-left: 1.5rem;"><input type="checkbox" class="form-check-input"
                  id="selectAllStudents" style="cursor: pointer;"></th>
              <th>STUDENT</th>
              <th>DEPARTMENT & SECTION</th>
              <th>COMPANY</th>
              <th>STATUS</th>
              <th class="text-end">ACTIONS</th>
            </tr>
          </thead>
          <tbody id="studentTableBody">
            <!-- Populated dynamically or with fallback -->
          </tbody>
        </table>
      </div>
    </div>

    <!-- Grid View Container (Hidden by default) -->
    <div class="row g-3 d-none mb-4" id="gridViewContainer">
      <!-- Grid items render here -->
    </div>

    <!-- Table Footer Controls -->
    <div class="d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-2">
        <span class="text-muted small">Rows per page:</span>
        <select class="form-select-pp py-1 px-2 small" style="width: 70px;" id="perPageSelect"
          onchange="loadStudents(1)">
          <option value="10">10</option>
          <option value="25" selected>25</option>
          <option value="50">50</option>
        </select>
      </div>

      <nav>
        <ul class="pagination pagination-sm mb-0" id="pagination">
          <!-- Rendered dynamically -->
        </ul>
      </nav>
    </div>

  </main>

  <!-- Floating Action Button (FAB) -->
  <button class="fab-btn" title="Add New Student" data-bs-toggle="modal" data-bs-target="#addStudentModal">
    <i class="fa-solid fa-user-plus"></i>
  </button>

  <!-- Add Student Modal -->
  <div class="modal fade" id="addStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
        <div class="modal-header border-0 pb-0 px-4 pt-4">
          <h5 class="modal-title font-weight-bold"><i class="fa-solid fa-user-plus text-primary me-2"></i> Add New
            Student</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <form id="addStudentForm"
            onsubmit="event.preventDefault(); showToast('Student added successfully!'); bootstrap.Modal.getInstance(document.getElementById('addStudentModal')).hide();">
            <div class="mb-3">
              <label for="addStudentName" class="form-label font-weight-600 small text-muted">FULL NAME</label>
              <input type="text" id="addStudentName" name="name" class="form-control form-control-pp"
                placeholder="e.g. Alex Morgan" required autocomplete="name">
            </div>
            <div class="mb-3">
              <label for="addStudentReg" class="form-label font-weight-600 small text-muted">REGISTER / ROLL
                NUMBER</label>
              <input type="text" id="addStudentReg" name="register_number" class="form-control form-control-pp"
                placeholder="e.g. 21CS042" required autocomplete="off">
            </div>
            <div class="row g-2 mb-3">
              <div class="col-6">
                <label for="addStudentDept" class="form-label font-weight-600 small text-muted">DEPARTMENT</label>
                <select id="addStudentDept" name="dept" class="form-select-pp w-100" required>
                  <option value="BCA">BCA</option>
                  <option value="BBA">BBA</option>
                  <option value="BBA – Hospitality & Hotel Management">BBA – Hospitality & Hotel Management</option>
                  <option value="B.Com">B.Com</option>
                  <option value="B.Sc">B.Sc</option>
                </select>

              </div>
              <div class="col-6">
                <label for="addStudentSection" class="form-label font-weight-600 small text-muted">SECTION</label>
                <select id="addStudentSection" name="section" class="form-select-pp w-100" required>
                  <option>Section A</option>
                  <option>Section B</option>
                  <option>Section C</option>
                </select>
              </div>
            </div>
            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
              <button type="button" class="btn btn-pp-outline" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-pp-primary">Add Student</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Student Modal -->
  <div class="modal fade" id="editStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
        <div class="modal-header border-0 pb-0 px-4 pt-4">
          <h5 class="modal-title font-weight-bold"><i class="fa-solid fa-user-pen text-primary me-2"></i> Edit Student
            Profile</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <form id="editStudentForm" onsubmit="saveStudentChanges(event)">
            <input type="hidden" id="editStudentId" name="student_id">
            <div class="mb-3">
              <label for="editName" class="form-label font-weight-600 small text-muted">FULL NAME</label>
              <input type="text" id="editName" name="name" class="form-control form-control-pp" required>
            </div>
            <div class="mb-3">
              <label for="editRegister" class="form-label font-weight-600 small text-muted">REGISTER / ROLL
                NUMBER</label>
              <input type="text" id="editRegister" name="register_number" class="form-control form-control-pp" readonly
                disabled>
            </div>
            <div class="row g-2 mb-3">
              <div class="col-6">
                <label for="editDept" class="form-label font-weight-600 small text-muted">DEPARTMENT</label>
                <select id="editDept" name="dept" class="form-select-pp w-100" required>
                  <!-- Loaded dynamically -->
                </select>
              </div>
              <div class="col-6">
                <label for="editSection" class="form-label font-weight-600 small text-muted">SECTION</label>
                <input type="text" id="editSection" name="section" class="form-control form-control-pp"
                  placeholder="e.g. Section A" required>
              </div>
            </div>
            <div class="row g-2 mb-3">
              <div class="col-6">
                <label for="editYear" class="form-label font-weight-600 small text-muted">ACADEMIC YEAR</label>
                <input type="text" id="editYear" name="year" class="form-control form-control-pp"
                  placeholder="e.g. 2023-2024" required>
              </div>
              <div class="col-6">
                <label for="editPlacementStatus" class="form-label font-weight-600 small text-muted">PLACEMENT
                  STATUS</label>
                <select id="editPlacementStatus" name="placement_status" class="form-select-pp w-100" required>
                  <option value="unplaced">Unplaced</option>
                  <option value="applied">Applied</option>
                  <option value="selected">Selected</option>
                  <option value="joined">Joined</option>
                </select>
              </div>
            </div>
            <div class="row g-2 mb-3">
              <div class="col-6">
                <label for="editCgpa" class="form-label font-weight-600 small text-muted">CGPA</label>
                <input type="number" step="0.01" min="0" max="10" id="editCgpa" name="cgpa"
                  class="form-control form-control-pp" placeholder="e.g. 8.5">
              </div>
              <div class="col-6">
                <label for="editBacklogs" class="form-label font-weight-600 small text-muted">BACKLOGS</label>
                <input type="number" min="0" id="editBacklogs" name="backlogs" class="form-control form-control-pp"
                  placeholder="e.g. 0">
              </div>
            </div>
            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
              <button type="button" class="btn btn-outline-danger" onclick="deleteFromEditModal()"><i class="fa-solid fa-trash me-1"></i> Delete Student</button>
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-pp-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-pp-primary">Save Changes</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <script>window.API_BASE = '<?php echo API_BASE; ?>'; window.API_TOKEN = '<?php echo $_SESSION['token']; ?>';</script>
  <script src="assets/js/api.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    const MOCK_STUDENTS = [
      { id: 1, name: "Alex Morgan", register_number: "21CS042", department_name: "BCA", section: "Section A", company_name: "Goldman Sachs", placement_status: "selected" },
      { id: 2, name: "Sophia Chen", register_number: "21CS108", department_name: "BCA", section: "Section A", company_name: "Amazon", placement_status: "selected" },
      { id: 3, name: "Marcus Vance", register_number: "21IT019", department_name: "B.Sc", section: "Section B", company_name: "TCS Digital", placement_status: "applied" },
      { id: 4, name: "Emily Watson", register_number: "21EC085", department_name: "B.Sc", section: "Section C", company_name: "Qualcomm", placement_status: "selected" },
      { id: 5, name: "David Kim", register_number: "21CS144", department_name: "BCA", section: "Section B", company_name: "-", placement_status: "not_placed" },
      { id: 6, name: "Jessica Taylor", register_number: "21IT072", department_name: "B.Sc", section: "Section A", company_name: "Microsoft", placement_status: "selected" },
      { id: 7, name: "Ryan Reynolds", register_number: "21EC012", department_name: "B.Sc", section: "Section B", company_name: "Texas Instruments", placement_status: "applied" },
      { id: 8, name: "Hannah Abbott", register_number: "21CS009", department_name: "BCA", section: "Section C", company_name: "-", placement_status: "not_placed" }
    ];

    // Helper to format section nicely (e.g., "section C" -> "Section C")
    function formatSection(sec) {
      if (!sec) return 'Section A';
      sec = sec.trim();
      const match = sec.match(/^section\s+([a-zA-Z])$/i);
      if (match) {
        return 'Section ' + match[1].toUpperCase();
      }
      if (sec.length === 1 && /[a-zA-Z]/.test(sec)) {
        return 'Section ' + sec.toUpperCase();
      }
      return sec;
    }

    // Helper to format department nicely (e.g., "bca" -> "BCA")
    function formatDept(dept) {
      if (!dept) return 'BCA';

      dept = dept.trim();
      const lower = dept.toLowerCase();
      if (lower === 'bca') return 'BCA';
      if (lower === 'bba') return 'BBA';
      if (lower === 'b.com') return 'B.Com';
      if (lower === 'b.sc') return 'B.Sc';
      // Capitalize first letters of words
      return dept.replace(/\b\w/g, c => c.toUpperCase());
    }

    let currentPage = 1;

    // Dynamic Filter Loader
    async function initFilters() {
      try {
        const filters = await API.get('/dashboard/filters');
        if (!filters) return;

        // 1. Populate Department filter
        const deptSelect = document.getElementById('deptFilter');
        if (deptSelect && filters.departments) {
          deptSelect.innerHTML = '<option value="">Department</option>';
          filters.departments.forEach(d => {
            deptSelect.innerHTML += `<option value="${d}">${formatDept(d)}</option>`;
          });
        }

        // 2. Populate editDept dropdown
        const editDeptSelect = document.getElementById('editDept');
        if (editDeptSelect && filters.departments) {
          editDeptSelect.innerHTML = '';
          filters.departments.forEach(d => {
            editDeptSelect.innerHTML += `<option value="${d}">${formatDept(d)}</option>`;
          });
        }

        // 3. Populate Section filter
        const sectionSelect = document.getElementById('sectionFilter');
        if (sectionSelect && filters.sections) {
          sectionSelect.innerHTML = '<option value="">Section</option>';
          filters.sections.forEach(s => {
            sectionSelect.innerHTML += `<option value="${s}">${formatSection(s)}</option>`;
          });
        }

        // 4. Populate Batch Year filter
        const batchSelect = document.getElementById('batchFilter');
        if (batchSelect && filters.academic_years) {
          batchSelect.innerHTML = '<option value="">Batch Year</option>';
          filters.academic_years.forEach(y => {
            batchSelect.innerHTML += `<option value="${y}">${y}</option>`;
          });
        }
      } catch (err) {
        console.error('Failed to load filter options:', err);
      }
    }

    async function loadStudents(page = 1) {
      currentPage = page;
      const tbody = document.getElementById('studentTableBody');
      const gridContainer = document.getElementById('gridViewContainer');


      try {
        const searchInput = document.getElementById('search');
        const deptInput = document.getElementById('deptFilter');
        const sectionInput = document.getElementById('sectionFilter');
        const statusInput = document.getElementById('statusFilter');
        const batchInput = document.getElementById('batchFilter');
        const perPageSelect = document.getElementById('perPageSelect');

        const search = searchInput ? searchInput.value : '';
        const dept = deptInput ? deptInput.value : '';
        const section = sectionInput ? sectionInput.value : '';
        const status = statusInput ? statusInput.value : '';
        const batch = batchInput ? batchInput.value : '';
        const perPage = perPageSelect ? perPageSelect.value : 25;

        const data = await API.get(`/students?page=${page}&per_page=${perPage}&search=${search}&department=${dept}&section=${section}&placement_status=${status}&academic_year=${batch}`);
        if (data && Array.isArray(data.students)) {
          renderStudents(data.students, data.total, page, perPage);
          return;
        }
      } catch (err) {
        console.error('Students Directory load error:', err);
        showToast('Error loading students: ' + err.message, 'danger');
        renderErrorState(err.message);
        return;
      }

      renderStudents(MOCK_STUDENTS, MOCK_STUDENTS.length, page, 25);
    }

    function renderErrorState(msg) {
      document.getElementById('showingText').innerText = `Error loading students`;
      const errorHtml = `
        <tr>
          <td colspan="6" class="text-center py-5">
            <div class="text-danger mb-2 font-weight-700">
              <i class="fa-solid fa-triangle-exclamation me-2" style="font-size: 1.5rem;"></i>
              Failed to load Students Directory
            </div>
            <p class="text-muted small mb-3">${msg}</p>
            <button class="btn btn-sm btn-pp-primary" onclick="loadStudents(1)">
              <i class="fa-solid fa-rotate-right me-1"></i> Retry Loading
            </button>
          </td>
        </tr>
      `;
      document.getElementById('studentTableBody').innerHTML = errorHtml;

      const gridErrorHtml = `
        <div class="col-12 text-center py-5">
          <div class="text-danger mb-2 font-weight-700">
            <i class="fa-solid fa-triangle-exclamation me-2" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
            Failed to load Students Directory
          </div>
          <p class="text-muted small mb-3">${msg}</p>
          <button class="btn btn-sm btn-pp-primary" onclick="loadStudents(1)">
            <i class="fa-solid fa-rotate-right me-1"></i> Retry Loading
          </button>
        </div>
      `;
      document.getElementById('gridViewContainer').innerHTML = gridErrorHtml;
      document.getElementById('pagination').innerHTML = '';
    }

    function renderStudents(list, total, page, perPage) {
      // Clear selected list on refresh
      document.getElementById('selectAllStudents').checked = false;
      document.getElementById('bulkActionsDropdown').classList.add('d-none');

      if (!list || list.length === 0) {
        document.getElementById('showingText').innerText = `Showing 0 of 0 students`;
        document.getElementById('studentTableBody').innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted small"><i class="fa-regular fa-user me-1" style="font-size: 1.25rem;"></i> No students found</td></tr>`;
        document.getElementById('gridViewContainer').innerHTML = `<div class="col-12 text-center py-5 text-muted small"><i class="fa-regular fa-user me-1" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i> No students found</div>`;
        document.getElementById('pagination').innerHTML = '';
        return;
      }

      const start = (page - 1) * perPage + 1;
      const end = Math.min(page * perPage, total);
      document.getElementById('showingText').innerText = `Showing ${start}-${end} of ${total.toLocaleString()} students`;

      // Table View
      document.getElementById('studentTableBody').innerHTML = list.map(s => {
        const statusBadge = s.statusType === 'success' || s.placement_status === 'selected' || s.placement_status === 'joined'
          ? `<span class="badge-pill-success"><i class="fa-solid fa-circle" style="font-size: 0.4rem;"></i> Placed</span>`
          : (s.statusType === 'warning' || s.placement_status === 'applied'
            ? `<span class="badge-pill-warning"><i class="fa-solid fa-circle" style="font-size: 0.4rem;"></i> In-process</span>`
            : `<span class="badge-pill-danger"><i class="fa-solid fa-circle" style="font-size: 0.4rem;"></i> Unplaced</span>`);

        const companyLogo = s.logo
          ? `<img src="${s.logo}" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3135/3135715.png'" width="20" height="20" class="rounded me-2">`
          : `<i class="fa-solid fa-building me-2 text-muted"></i>`;

        return `
          <tr>
            <td style="padding-left: 1.5rem; vertical-align: middle;">
              <input type="checkbox" class="form-check-input student-select" value="${s.id}" data-name="${s.name}" onchange="updateSelectedCount()" style="cursor: pointer;">
            </td>
            <td>
              <div class="d-flex align-items-center gap-3">
                <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(s.name || 'Student')}&background=4F46E5&color=fff" class="rounded-circle" width="36" height="36">
                <div>
                  <div class="font-weight-700 text-dark">${s.name}</div>
                  <div class="text-muted small">${s.register_number || s.id || '21CS000'}</div>
                </div>
              </div>
            </td>
            <td>
              <div class="font-weight-600 text-dark">${formatDept(s.department_name || s.dept)}</div>
              <div class="text-muted small">${formatSection(s.section || s.sec)}</div>
            </td>
            <td>
              <div class="d-flex align-items-center font-weight-600 text-dark">
                ${companyLogo} ${s.company_name || s.company || '-'}
              </div>
            </td>
            <td>${statusBadge}</td>
            <td class="text-end">
              <button class="btn btn-sm btn-pp-outline py-1 px-2" onclick="showToast('Loading profile for ${s.name}...')"><i class="fa-regular fa-eye me-1"></i> View</button>
              <a href="documents.php?id=${s.id}" class="btn btn-sm btn-outline-info py-1 px-2 ms-1"><i class="fa-solid fa-folder-open"></i> Docs</a>
              <button class="btn btn-sm btn-pp-primary py-1 px-2 ms-1" onclick="openEditModal(${s.id})"><i class="fa-solid fa-user-pen"></i> Edit</button>
              <button class="btn btn-sm btn-outline-danger py-1 px-2 ms-1" title="Delete Student" onclick="deleteSingleStudent(${s.id}, '${escapeJsQuotes(s.name)}')"><i class="fa-solid fa-trash"></i> Delete</button>
            </td>
          </tr>
        `;
      }).join('');

      // Grid View
      document.getElementById('gridViewContainer').innerHTML = list.map(s => {
        const isPlaced = s.placement_status === 'selected' || s.placement_status === 'joined';
        const badge = isPlaced
          ? `<span class="badge-pill-success">Placed</span>`
          : (s.placement_status === 'applied' ? `<span class="badge-pill-warning">In-process</span>` : `<span class="badge-pill-danger">Unplaced</span>`);

        return `
          <div class="col-12 col-sm-6 col-lg-3">
            <div class="pp-card h-100 position-relative">
              <div class="position-absolute" style="top: 1rem; right: 1rem; z-index: 10;">
                <input type="checkbox" class="form-check-input student-select" value="${s.id}" data-name="${s.name}" onchange="updateSelectedCount()" style="cursor: pointer;">
              </div>
              <div class="d-flex align-items-center gap-3 mb-3">
                <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(s.name || 'Student')}&background=4F46E5&color=fff" class="rounded-circle" width="48" height="48">
                <div>
                  <div class="font-weight-700 text-dark mb-0">${s.name}</div>
                  <div class="text-muted small">${s.register_number || s.id || '21CS000'}</div>
                </div>
              </div>
              <div class="small mb-2"><span class="text-muted">Dept & Sec:</span> <span class="font-weight-600">${formatDept(s.department_name || s.dept)} (${formatSection(s.section || s.sec)})</span></div>
              <div class="small mb-3"><span class="text-muted">Company:</span> <span class="font-weight-600">${s.company_name || s.company || '-'}</span></div>
              <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                ${badge}
                <div class="d-flex gap-1">
                  <button class="btn btn-sm btn-pp-outline py-1 px-2" onclick="showToast('Loading profile for ${s.name}...')"><i class="fa-regular fa-eye"></i> View</button>
                  <a href="documents.php?id=${s.id}" class="btn btn-sm btn-outline-info py-1 px-2"><i class="fa-solid fa-folder-open"></i> Docs</a>
                  <button class="btn btn-sm btn-pp-primary py-1 px-2" onclick="openEditModal(${s.id})"><i class="fa-solid fa-user-pen"></i> Edit</button>
                  <button class="btn btn-sm btn-outline-danger py-1 px-2" title="Delete Student" onclick="deleteSingleStudent(${s.id}, '${escapeJsQuotes(s.name)}')"><i class="fa-solid fa-trash"></i> Delete</button>
                </div>
              </div>
            </div>
          </div>
        `;
      }).join('');

      // Render Pagination Links
      const totalPages = Math.ceil(total / perPage);
      let paginationHtml = '';

      // Prev Button
      paginationHtml += `<li class="page-item ${page === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="event.preventDefault(); ${page > 1 ? `loadStudents(${page - 1})` : ''}">Prev</a>
      </li>`;

      // Page numbers (simplified display)
      for (let i = 1; i <= Math.min(totalPages, 5); i++) {
        paginationHtml += `<li class="page-item ${page === i ? 'active' : ''}">
          <a class="page-link" href="#" onclick="event.preventDefault(); loadStudents(${i})" ${page === i ? 'style="background-color: var(--pp-primary); border-color: var(--pp-primary);"' : ''}>${i}</a>
        </li>`;
      }
      if (totalPages > 5) {
        paginationHtml += `<li class="page-item disabled"><a class="page-link" href="#">...</a></li>`;
        paginationHtml += `<li class="page-item ${page === totalPages ? 'active' : ''}">
          <a class="page-link" href="#" onclick="event.preventDefault(); loadStudents(${totalPages})" ${page === totalPages ? 'style="background-color: var(--pp-primary); border-color: var(--pp-primary);"' : ''}>${totalPages}</a>
        </li>`;
      }

      // Next Button
      paginationHtml += `<li class="page-item ${page === totalPages || totalPages === 0 ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="event.preventDefault(); ${page < totalPages ? `loadStudents(${page + 1})` : ''}">Next</a>
      </li>`;

      document.getElementById('pagination').innerHTML = paginationHtml;
    }

    // Modal Operations
    async function openEditModal(studentId) {
      try {
        const student = await API.get(`/students/${studentId}`);
        if (student) {
          document.getElementById('editStudentId').value = student.id;
          document.getElementById('editName').value = student.name || '';
          document.getElementById('editRegister').value = student.register_number || '';
          document.getElementById('editDept').value = student.department_name || '';
          document.getElementById('editSection').value = formatSection(student.section || '');
          document.getElementById('editYear').value = student.academic_year || '2023-2024';
          document.getElementById('editPlacementStatus').value = student.placement_status || 'unplaced';
          document.getElementById('editCgpa').value = student.cgpa || '';
          document.getElementById('editBacklogs').value = student.backlogs || 0;

          const modalEl = document.getElementById('editStudentModal');
          const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
          modalInstance.show();
        }
      } catch (err) {
        showToast('Error loading student details: ' + err.message, 'danger');
      }
    }

    async function saveStudentChanges(e) {
      e.preventDefault();
      const studentId = document.getElementById('editStudentId').value;
      const payload = {
        name: document.getElementById('editName').value,
        section: document.getElementById('editSection').value,
        academic_year: document.getElementById('editYear').value,
        placement_status: document.getElementById('editPlacementStatus').value,
        cgpa: document.getElementById('editCgpa').value ? parseFloat(document.getElementById('editCgpa').value) : null,
        backlogs: document.getElementById('editBacklogs').value ? parseInt(document.getElementById('editBacklogs').value) : 0
      };

      try {
        await API.put(`/students/${studentId}`, payload);
        showToast('Student profile updated successfully!');

        const modalEl = document.getElementById('editStudentModal');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();

        loadStudents(1);
      } catch (err) {
        showToast('Failed to save changes: ' + err.message, 'danger');
      }
    }

    function toggleView(mode) {
      if (mode === 'list') {
        document.getElementById('listViewContainer').classList.remove('d-none');
        document.getElementById('gridViewContainer').classList.add('d-none');
        document.getElementById('btnListView').style.backgroundColor = 'var(--pp-primary-light)';
        document.getElementById('btnListView').style.color = 'var(--pp-primary-dark)';
        document.getElementById('btnListView').style.border = '1px solid var(--pp-primary)';
        document.getElementById('btnGridView').style.backgroundColor = 'transparent';
        document.getElementById('btnGridView').style.color = '#6B7280';
        document.getElementById('btnGridView').style.border = 'none';
      } else {
        document.getElementById('listViewContainer').classList.add('d-none');
        document.getElementById('gridViewContainer').classList.remove('d-none');
        document.getElementById('btnGridView').style.backgroundColor = 'var(--pp-primary-light)';
        document.getElementById('btnGridView').style.color = 'var(--pp-primary-dark)';
        document.getElementById('btnGridView').style.border = '1px solid var(--pp-primary)';
        document.getElementById('btnListView').style.backgroundColor = 'transparent';
        document.getElementById('btnListView').style.color = '#6B7280';
        document.getElementById('btnListView').style.border = 'none';
      }
    }

    // Initialize dropdowns and load first page on load
    document.addEventListener('DOMContentLoaded', async () => {
      await initFilters();
      loadStudents(1);
    });

    // Bulk selection count and dropdown control
    function updateSelectedCount() {
      const checkboxes = document.querySelectorAll('.student-select:checked');
      const dropdown = document.getElementById('bulkActionsDropdown');
      const text = document.getElementById('selectedCountText');

      text.innerText = checkboxes.length;
      if (checkboxes.length > 0) {
        dropdown.classList.remove('d-none');
      } else {
        dropdown.classList.add('d-none');
      }
    }

    // Select-all checkbox listener
    document.getElementById('selectAllStudents').addEventListener('change', function () {
      const checkboxes = document.querySelectorAll('.student-select');
      checkboxes.forEach(cb => cb.checked = this.checked);
      updateSelectedCount();
    });

    function escapeJsQuotes(str) {
      if (!str) return '';
      return str.replace(/'/g, "\\'").replace(/"/g, '&quot;');
    }

    // Delete Single Student
    async function deleteSingleStudent(studentId, studentName) {
      if (!studentId) return;
      const displayTitle = studentName ? `student "${studentName}"` : 'this student record';
      if (confirm(`Are you sure you want to delete ${displayTitle}? This action cannot be undone.`)) {
        try {
          await API.del(`/students/${studentId}`);
          showToast(`Student record deleted successfully.`);
          loadStudents(currentPage || 1);
        } catch (err) {
          showToast('Delete failed: ' + err.message, 'danger');
        }
      }
    }

    // Delete student from Edit Modal
    async function deleteFromEditModal() {
      const studentId = document.getElementById('editStudentId').value;
      const studentName = document.getElementById('editName').value;
      if (!studentId) return;

      if (confirm(`Are you sure you want to delete student "${studentName || 'Record'}"? This action is irreversible.`)) {
        try {
          await API.del(`/students/${studentId}`);
          showToast(`Student record deleted successfully.`);

          if (document.activeElement) document.activeElement.blur();
          const modalEl = document.getElementById('editStudentModal');
          const modalInstance = bootstrap.Modal.getInstance(modalEl);
          if (modalInstance) modalInstance.hide();

          loadStudents(currentPage || 1);
        } catch (err) {
          showToast('Delete failed: ' + err.message, 'danger');
        }
      }
    }

    // Global listener to blur active element before modal hides (prevents WAI-ARIA aria-hidden focus error)
    document.addEventListener('hide.bs.modal', function (e) {
      if (document.activeElement && e.target.contains(document.activeElement)) {
        document.activeElement.blur();
      }
    });

    // Open Bulk Push Modal
    async function openBulkPushModal() {
      const checkboxes = document.querySelectorAll('.student-select:checked');
      if (checkboxes.length === 0) {
        showToast('Please select at least one student to push to recruiter drive.', 'warning');
        return;
      }
      document.getElementById('bulkPushCount').innerText = checkboxes.length;

      try {
        const companies = await API.get('/companies');
        const select = document.getElementById('bulkPushCompanySelect');
        select.innerHTML = '<option value="">Choose Company Drive...</option>';
        companies.forEach(c => {
          const pkg = c.package_amount ? `${c.package_amount} LPA` : (c.avg_package ? `${c.avg_package} LPA` : '0 LPA');
          select.innerHTML += `<option value="${c.id}">${c.name} (${pkg})</option>`;
        });

        const modal = new bootstrap.Modal(document.getElementById('bulkPushModal'));
        modal.show();
      } catch (err) {
        showToast('Failed to load companies: ' + err.message, 'danger');
      }
    }

    // Submit Bulk Push
    async function submitBulkPush(e) {
      e.preventDefault();
      const companySelect = document.getElementById('bulkPushCompanySelect');
      const companyIdVal = companySelect ? companySelect.value : '';
      const companyId = parseInt(companyIdVal, 10);
      const checkboxes = document.querySelectorAll('.student-select:checked');
      const studentIds = Array.from(checkboxes).map(cb => parseInt(cb.value, 10)).filter(id => !isNaN(id));

      if (!studentIds.length) {
        showToast('Please select at least one student.', 'warning');
        return;
      }
      if (!companyIdVal || isNaN(companyId)) {
        showToast('Please select a target recruiter drive.', 'warning');
        return;
      }

      const submitBtn = e.target.querySelector('button[type="submit"]');
      const originalHtml = submitBtn ? submitBtn.innerHTML : '';
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Processing...';
      }

      try {
        const result = await API.post('/students/bulk-push', {
          student_ids: studentIds,
          company_id: companyId,
          stage: 'applied'
        });
        showToast(`Registered ${result.pushed_count} students successfully!`);

        if (document.activeElement) document.activeElement.blur();
        const modalEl = document.getElementById('bulkPushModal');
        const modalInst = bootstrap.Modal.getInstance(modalEl);
        if (modalInst) modalInst.hide();

        loadStudents(1);
      } catch (err) {
        showToast('Registration failed: ' + err.message, 'danger');
      } finally {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalHtml;
        }
      }
    }

    // Trigger Bulk Delete
    async function triggerBulkDelete() {
      const checkboxes = document.querySelectorAll('.student-select:checked');
      const studentIds = Array.from(checkboxes).map(cb => parseInt(cb.value));

      if (confirm(`Are you sure you want to delete the selected ${studentIds.length} student records? This action is irreversible.`)) {
        try {
          const result = await API.post('/students/bulk-delete', {
            student_ids: studentIds
          });
          showToast(`Deleted ${result.deleted_count} student records.`);
          loadStudents(1);
        } catch (err) {
          showToast('Delete failed: ' + err.message, 'danger');
        }
      }
    }

    // Client-side CSV exporter for selected students
    function exportSelectedStudents() {
      const checkboxes = document.querySelectorAll('.student-select:checked');

      let csvContent = "data:text/csv;charset=utf-8,ID,Name,Register Number,Department,Section,Status\n";

      const tableRows = document.querySelectorAll('#studentTableBody tr');
      tableRows.forEach(tr => {
        const cb = tr.querySelector('.student-select');
        if (cb && cb.checked) {
          const name = cb.getAttribute('data-name');
          const reg = tr.querySelector('td:nth-child(2) .text-muted').innerText.trim();
          const dept = tr.querySelector('td:nth-child(3) .font-weight-600').innerText.trim();
          const sec = tr.querySelector('td:nth-child(3) .text-muted').innerText.trim();
          const status = tr.querySelector('td:nth-child(5)').innerText.trim();
          csvContent += `"${cb.value}","${name}","${reg}","${dept}","${sec}","${status}"\n`;
        }
      });

      const encodedUri = encodeURI(csvContent);
      const link = document.createElement("a");
      link.setAttribute("href", encodedUri);
      link.setAttribute("download", `selected_students_${new Date().toISOString().slice(0, 10)}.csv`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      showToast("Exported selected student records.");
    }
  </script>

  <!-- Bulk Push to Company Modal -->
  <div class="modal fade" id="bulkPushModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
        <div class="modal-header border-0 pb-0 px-4 pt-4">
          <h5 class="modal-title font-weight-bold"><i class="fa-solid fa-paper-plane text-primary me-2"></i> Push to
            Company Drive</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <form id="bulkPushForm" onsubmit="submitBulkPush(event)">
            <div class="mb-3 text-center bg-light p-3 rounded-3">
              <span class="text-muted small d-block mb-1">SELECTED STUDENTS</span>
              <span class="font-weight-800 text-dark h4" id="bulkPushCount">0</span>
            </div>

            <div class="mb-4">
              <label class="form-label font-weight-600 small text-muted">SELECT TARGET RECRUITER</label>
              <select id="bulkPushCompanySelect" class="form-select-pp w-100" required>
                <option value="">Choose Company Drive...</option>
                <!-- Loaded dynamically -->
              </select>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
              <button type="button" class="btn btn-pp-outline" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-pp-primary"><i class="fa-solid fa-check me-1"></i> Push to
                Company</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>

</html>