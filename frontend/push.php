<?php require_once 'config.php'; require_login(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Push to Company — Placement Pro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="assets/css/style.css" rel="stylesheet">
  <style>
    .drive-info-card {
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
      border: 1px solid #e2e8f0;
      border-radius: 12px;
    }
    .badge-dept {
      background-color: var(--pp-primary-light);
      color: var(--pp-primary-dark);
      padding: 0.25rem 0.5rem;
      border-radius: 6px;
      font-size: 0.75rem;
      font-weight: 600;
    }
    .skill-pill {
      background-color: #f1f5f9;
      color: #475569;
      padding: 0.15rem 0.4rem;
      border-radius: 4px;
      font-size: 0.7rem;
      font-weight: 500;
      display: inline-block;
      margin: 0.1rem;
    }
    .fab-btn {
      position: fixed;
      bottom: 2rem;
      right: 2rem;
      background: var(--pp-primary);
      color: white;
      border: none;
      box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);
      z-index: 1000;
      transition: all 0.2s;
      width: auto;
      padding: 0 1.5rem;
      border-radius: 99px;
      height: 48px;
    }
    .fab-btn:hover {
      background: var(--pp-primary-dark);
      transform: translateY(-2px);
      box-shadow: 0 12px 20px -3px rgba(79, 70, 229, 0.5);
    }
  </style>
</head>
<body>

  <?php include 'partials/nav.php'; ?>

  <main id="main-wrapper">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="h3 font-weight-800 mb-1">Student Selection & Push</h2>
        <p class="text-muted small mb-0">Select eligible candidates and assign them to partner company placement drives</p>
      </div>
    </div>

    <!-- Drive Selector Panel -->
    <div class="pp-card mb-4 p-3">
      <div class="row align-items-center g-3">
        <div class="col-md-6 col-12">
          <label for="driveSelect" class="form-label font-weight-700 text-muted small mb-1">SELECT TARGET PLACEMENT DRIVE</label>
          <select class="form-select-pp w-100" id="driveSelect" name="drive_id" aria-label="Select target placement drive" onchange="onDriveChange()">
            <option value="">Choose active recruitment drive...</option>
          </select>
        </div>
        <div class="col-md-6 col-12 text-md-end text-start">
          <span class="text-muted small" id="driveSelectHint">Select a drive to load eligible students.</span>
        </div>
      </div>
    </div>

    <!-- Drive Requirements Details Card (Shown after selection) -->
    <div class="drive-info-card p-4 mb-4 d-none" id="driveRequirementsCard">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3 pb-3 border-bottom">
        <div>
          <span class="badge-pill-success mb-2 d-inline-block"><i class="fa-solid fa-briefcase me-1"></i> Active Drive</span>
          <h3 class="h4 font-weight-800 text-dark mb-1" id="reqCompanyName">Company Name</h3>
          <p class="text-muted mb-0" id="reqJobRole">Job Role: Software Engineer</p>
        </div>
        <div class="text-md-end text-start">
          <div class="text-muted small">Package Offer</div>
          <div class="h3 font-weight-800 text-primary" id="reqPackage">0 LPA</div>
        </div>
      </div>
      <div class="row g-4">
        <div class="col-12 col-md-3">
          <div class="text-muted small font-weight-600 mb-1">MINIMUM CGPA</div>
          <div class="font-weight-800 text-dark h5 mb-0" id="reqMinCgpa">0.0</div>
        </div>
        <div class="col-12 col-md-3">
          <div class="text-muted small font-weight-600 mb-1">ALLOWED BACKLOGS</div>
          <div class="font-weight-800 text-dark h5 mb-0" id="reqMaxBacklogs">0</div>
        </div>
        <div class="col-12 col-md-6">
          <div class="text-muted small font-weight-600 mb-1">ELIGIBLE DEPARTMENTS</div>
          <div class="d-flex flex-wrap gap-1 mt-1" id="reqEligibleDepts">
            <!-- Badges -->
          </div>
        </div>
      </div>
    </div>

    <!-- Eligible Candidates Roster (Shown after selection) -->
    <div id="rosterContainer" class="d-none">
      <!-- Roster Header Filters -->
      <div class="pp-card mb-4 p-3">
        <div class="row g-3 align-items-center">
          <!-- Search input -->
          <div class="col-12 col-md-3">
            <div class="position-relative">
              <i class="fa-solid fa-magnifying-glass position-absolute text-muted" style="left: 0.85rem; top: 50%; transform: translateY(-50%); font-size: 0.85rem;"></i>
              <input type="text" class="form-control-pp w-100 ps-5" id="rosterSearch" placeholder="Search name or ID..." onkeyup="loadEligibleStudents(1)">
            </div>
          </div>

          <!-- Department filter -->
          <div class="col-6 col-md-2">
            <select class="form-select-pp w-100" id="rosterDept" onchange="loadEligibleStudents(1)">
              <option value="">All Departments</option>
            </select>
          </div>

          <!-- Section filter -->
          <div class="col-6 col-md-2">
            <select class="form-select-pp w-100" id="rosterSection" onchange="loadEligibleStudents(1)">
              <option value="">All Sections</option>
            </select>
          </div>

          <!-- Skills search -->
          <div class="col-6 col-md-2">
            <input type="text" class="form-control-pp w-100" id="rosterSkills" placeholder="Filter by skill..." onkeyup="loadEligibleStudents(1)">
          </div>

          <!-- Sorting -->
          <div class="col-6 col-md-2">
            <select class="form-select-pp w-100" id="rosterSort" onchange="loadEligibleStudents(1)">
              <option value="name_asc" selected>Sort by Name: A-Z</option>
              <option value="name_desc">Sort by Name: Z-A</option>
              <option value="reg_asc">Reg Number: Asc</option>
              <option value="reg_desc">Reg Number: Desc</option>
            </select>
          </div>

          <!-- Count info -->
          <div class="col-12 col-md-1 text-md-end text-start">
            <span class="text-muted small font-weight-600" id="showingCount">0 of 0</span>
          </div>
        </div>
      </div>

      <!-- Responsive Table Card -->
      <div class="pp-card p-0 overflow-hidden mb-4">
        <div class="table-responsive">
          <table class="pp-table">
            <thead>
              <tr>
                <th style="width: 40px; padding-left: 1.5rem;">
                  <input type="checkbox" class="form-check-input" id="selectAllCheckbox" style="cursor: pointer;">
                </th>
                <th>REGISTER NO</th>
                <th>CANDIDATE</th>
                <th>DEPT & SECTION</th>
                <th>YEAR</th>
                <th>CGPA</th>
                <th>SKILLS</th>
                <th>STATUS</th>
                <th>CURRENT JOB</th>
                <th class="text-end">ACTION</th>
              </tr>
            </thead>
            <tbody id="rosterTableBody">
              <!-- Rendered dynamically -->
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
          <span class="text-muted small">Rows per page:</span>
          <select class="form-select-pp py-1 px-2 small" style="width: 70px;" id="perPageSelect" onchange="loadEligibleStudents(1)">
            <option value="10">10</option>
            <option value="25" selected>25</option>
            <option value="50">50</option>
          </select>
        </div>
        <nav>
          <ul class="pagination pagination-sm mb-0" id="rosterPagination">
            <!-- Rendered dynamically -->
          </ul>
        </nav>
      </div>
    </div>

    <!-- Empty/Prompt Slate -->
    <div class="pp-card text-center py-5" id="emptySlate">
      <div class="d-inline-flex align-items-center justify-content-center bg-light text-primary rounded-circle mb-3" style="width: 64px; height: 64px;">
        <i class="fa-solid fa-paper-plane h3 mb-0"></i>
      </div>
      <h4 class="h5 font-weight-700 text-dark mb-1">No Recruitment Drive Selected</h4>
      <p class="text-muted small mx-auto" style="max-width: 400px;">Please choose an active campus placement drive from the dropdown above to retrieve, filter, and assign eligible student candidates.</p>
    </div>

    <!-- Floating Action Button (FAB) -->
    <button class="fab-btn d-none px-4 py-2" id="pushToCompanyFab" onclick="openPushModal()">
      <i class="fa-solid fa-paper-plane me-2"></i> Push to Company (<span id="selectedCountText">0</span>)
    </button>

    <!-- Push To Company Modal -->
    <div class="modal fade" id="pushConfirmModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
          <div class="modal-header border-0 pb-0 px-4 pt-4">
            <h5 class="modal-title font-weight-bold"><i class="fa-solid fa-paper-plane text-primary me-2"></i> Push Students to Company</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <form id="pushConfirmForm" onsubmit="submitPush(event)">
              <div class="mb-3 text-center bg-light p-3 rounded-3 border">
                <span class="text-muted small d-block mb-1">SELECTED ELIGIBLE CANDIDATES</span>
                <span class="font-weight-800 text-dark h4" id="modalStudentCount">0</span>
              </div>
              
              <div class="mb-3">
                <label for="modalCompanyName" class="form-label font-weight-600 small text-muted">TARGET RECRUITER</label>
                <input type="text" class="form-control form-control-pp bg-light" id="modalCompanyName" name="company_name" readonly>
              </div>

              <div class="mb-3">
                <label for="modalDriveName" class="form-label font-weight-600 small text-muted">PLACEMENT DRIVE</label>
                <input type="text" class="form-control form-control-pp bg-light" id="modalDriveName" name="drive_name" readonly>
              </div>

              <div class="mb-4">
                <label for="pushNotes" class="form-label font-weight-600 small text-muted">DRIVE NOTES / SPECIAL REMARKS (OPTIONAL)</label>
                <textarea class="form-control form-control-pp" id="pushNotes" name="push_notes" rows="3" placeholder="Enter instructions or remarks..."></textarea>
              </div>
              
              <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <button type="button" class="btn btn-pp-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-pp-primary"><i class="fa-solid fa-check me-1"></i> Assign Students</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Student Profile details view modal (View Stage timelines) -->
    <div class="modal fade" id="studentDetailModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
          <div class="modal-header border-0 pb-0 px-4 pt-4">
            <h5 class="modal-title font-weight-bold" id="detailModalTitle">Student Profile</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4" id="detailModalBody">
            <!-- Loaded dynamically -->
          </div>
        </div>
      </div>
    </div>

  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Selected student list
    let selectedStudentIds = new Set();
    let eligibleStudentsList = [];

    // Load active drives
    async function loadActiveDrives() {
      try {
        const companies = await API.get('/companies');
        const select = document.getElementById('driveSelect');
        select.innerHTML = '<option value="">Choose active recruitment drive...</option>';
        
        companies.forEach(c => {
          const pkg = c.package_amount ? `${c.package_amount} LPA` : (c.avg_package ? `${c.avg_package} LPA` : '0 LPA');
          const role = c.job_role || 'General SDE';
          select.innerHTML += `<option value="${c.id}">${c.name} — ${role} (${pkg})</option>`;
        });
      } catch (err) {
        showToast('Failed to load companies: ' + err.message, 'danger');
      }
    }

    // Populate Filters
    async function initFilters() {
      try {
        const filters = await API.get('/dashboard/filters');
        
        const deptSelect = document.getElementById('rosterDept');
        if (deptSelect) {
          deptSelect.innerHTML = '<option value="">All Departments</option>';
          if (filters && Array.isArray(filters.departments)) {
            filters.departments.forEach(d => {
              deptSelect.innerHTML += `<option value="${d}">${d}</option>`;
            });
          }
        }

        const sectionSelect = document.getElementById('rosterSection');
        if (sectionSelect) {
          sectionSelect.innerHTML = '<option value="">All Sections</option>';
          if (filters && Array.isArray(filters.sections)) {
            filters.sections.forEach(s => {
              sectionSelect.innerHTML += `<option value="${s}">Section ${s}</option>`;
            });
          }
        }
      } catch (err) {
        console.error('Failed to load filter options:', err);
      }
    }


    // On selector change
    function onDriveChange() {
      const driveId = document.getElementById('driveSelect').value;
      
      if (!driveId) {
        document.getElementById('driveRequirementsCard').classList.add('d-none');
        document.getElementById('rosterContainer').classList.add('d-none');
        document.getElementById('emptySlate').classList.remove('d-none');
        document.getElementById('pushToCompanyFab').classList.add('d-none');
        selectedStudentIds.clear();
        return;
      }

      document.getElementById('emptySlate').classList.add('d-none');
      document.getElementById('rosterContainer').classList.remove('d-none');
      document.getElementById('driveRequirementsCard').classList.remove('d-none');
      
      selectedStudentIds.clear();
      updateSelectedFAB();
      loadEligibleStudents(1);
    }

    // Load eligible students for selection
    async function loadEligibleStudents(page = 1) {
      const driveId = document.getElementById('driveSelect').value;
      if (!driveId) return;

      const tbody = document.getElementById('rosterTableBody');
      tbody.innerHTML = `<tr><td colspan="10" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div> Loading eligible roster...</td></tr>`;

      try {
        const search = document.getElementById('rosterSearch').value;
        const dept = document.getElementById('rosterDept').value;
        const section = document.getElementById('rosterSection').value;
        const skills = document.getElementById('rosterSkills').value;
        const sortVal = document.getElementById('rosterSort').value;
        const perPage = document.getElementById('perPageSelect').value || 25;

        // Parse sort
        let sort_by = 'name';
        let sort_order = 'asc';
        if (sortVal === 'name_desc') { sort_by = 'name'; sort_order = 'desc'; }
        else if (sortVal === 'reg_asc') { sort_by = 'register_number'; sort_order = 'asc'; }
        else if (sortVal === 'reg_desc') { sort_by = 'register_number'; sort_order = 'desc'; }

        const res = await API.get(`/eligible-for/${driveId}?page=${page}&per_page=${perPage}&search=${search}&department=${dept}&section=${section}&skills=${skills}&sort_by=${sort_by}&sort_order=${sort_order}`);
        
        eligibleStudentsList = res.students;
        renderRosterTable(res.students, res.total, page, perPage);
        renderRequirements(res.company);
        
      } catch (err) {
        tbody.innerHTML = `<tr><td colspan="10" class="text-center text-danger py-4">Error: ${err.message}</td></tr>`;
      }
    }

    // Render Company requirements card
    function renderRequirements(c) {
      document.getElementById('reqCompanyName').innerText = c.name;
      document.getElementById('reqJobRole').innerText = `Job Role: ${c.job_role || 'SDE'}`;
      document.getElementById('reqPackage').innerText = c.package_amount ? `${c.package_amount} LPA` : 'TBD';
      document.getElementById('reqMinCgpa').innerText = c.min_cgpa ? c.min_cgpa.toFixed(2) : '0.00';
      document.getElementById('reqMaxBacklogs').innerText = c.allowed_backlogs ?? '0';

      const deptsContainer = document.getElementById('reqEligibleDepts');
      if (c.eligible_departments) {
        deptsContainer.innerHTML = c.eligible_departments.split(',').map(d => `<span class="badge-dept">${d.trim()}</span>`).join('');
      } else {
        deptsContainer.innerHTML = `<span class="badge-dept">All Departments</span>`;
      }
    }

    // Render Candidates Table
    function renderRosterTable(list, total, page, perPage) {
      const tbody = document.getElementById('rosterTableBody');
      const start = (page - 1) * perPage + 1;
      const end = Math.min(page * perPage, total);
      document.getElementById('showingCount').innerText = `${start}-${end} of ${total}`;

      if (list.length === 0) {
        tbody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-muted small"><i class="fa-regular fa-user me-1"></i> No eligible students found.</td></tr>`;
        document.getElementById('rosterPagination').innerHTML = '';
        return;
      }

      tbody.innerHTML = list.map(s => {
        const skillsArray = Array.isArray(s.skills) ? s.skills : (typeof s.skills === 'string' ? s.skills.split(',') : []);
        const skillsHtml = skillsArray.length 
          ? skillsArray.slice(0, 3).map(sk => `<span class="skill-pill">${String(sk).trim()}</span>`).join('')
          : '<span class="text-muted small">-</span>';

        const statusBadge = s.placement_status === 'selected' || s.placement_status === 'joined'
          ? `<span class="badge-pill-success"><i class="fa-solid fa-circle" style="font-size: 0.4rem;"></i> Placed</span>`
          : (s.placement_status === 'applied'
            ? `<span class="badge-pill-warning"><i class="fa-solid fa-circle" style="font-size: 0.4rem;"></i> In-process</span>`
            : `<span class="badge-pill-danger"><i class="fa-solid fa-circle" style="font-size: 0.4rem;"></i> Unplaced</span>`);

        const isChecked = selectedStudentIds.has(s.id) || s.is_assigned ? 'checked' : '';
        const isDisabled = s.is_assigned ? 'disabled' : '';

        return `
          <tr>
            <td style="padding-left: 1.5rem; vertical-align: middle;">
              <input type="checkbox" class="form-check-input roster-checkbox" id="roster_student_${s.id}" name="selected_students[]" value="${s.id}" ${isChecked} ${isDisabled} onchange="toggleSelectStudent(${s.id}, this)" style="cursor: pointer;">
            </td>
            <td class="font-weight-700 text-dark small">${s.register_number || '-'}</td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(s.name)}&background=4F46E5&color=fff" class="rounded-circle" width="30" height="30">
                <div class="font-weight-700 text-dark">${s.name}</div>
              </div>
            </td>
            <td>
              <div class="font-weight-600 text-dark" style="font-size: 0.85rem;">${s.department_name}</div>
              <div class="text-muted small">Section ${s.section || 'A'}</div>
            </td>
            <td class="text-muted small">${s.academic_year || '2026'}</td>
            <td class="font-weight-700 text-dark">${s.cgpa ? parseFloat(s.cgpa).toFixed(2) : '-'}</td>
            <td style="max-width: 150px;">${skillsHtml}</td>
            <td>${statusBadge}</td>
            <td>
              <span class="small font-weight-600 text-dark">
                ${s.is_assigned ? `<span class="text-primary font-weight-700">Assigned here</span>` : (s.current_company || '-')}
              </span>
            </td>
            <td class="text-end">
              <button class="btn btn-sm btn-pp-outline py-1 px-2" onclick="viewStudentDetails(${s.id})"><i class="fa-regular fa-eye"></i> View</button>
            </td>
          </tr>
        `;
      }).join('');

      // Build Pagination
      const totalPages = Math.ceil(total / perPage);
      let pagHtml = '';
      for (let i = 1; i <= totalPages; i++) {
        pagHtml += `<li class="page-item ${i === page ? 'active' : ''}"><a class="page-link" href="#" onclick="event.preventDefault(); loadEligibleStudents(${i})">${i}</a></li>`;
      }
      document.getElementById('rosterPagination').innerHTML = pagHtml;

      // Handle Select All check state
      updateSelectAllState();
    }

    // Select/Deselect Student Checkbox
    function toggleSelectStudent(id, element) {
      if (element.checked) {
        selectedStudentIds.add(id);
      } else {
        selectedStudentIds.delete(id);
      }
      updateSelectedFAB();
    }

    // Select all checkboxes
    document.getElementById('selectAllCheckbox').addEventListener('change', function() {
      const checkboxes = document.querySelectorAll('.roster-checkbox:not(:disabled)');
      checkboxes.forEach(cb => {
        cb.checked = this.checked;
        const id = parseInt(cb.value);
        if (this.checked) {
          selectedStudentIds.add(id);
        } else {
          selectedStudentIds.delete(id);
        }
      });
      updateSelectedFAB();
    });

    // Check if selectAll checkbox should be checked
    function updateSelectAllState() {
      const checkboxes = document.querySelectorAll('.roster-checkbox:not(:disabled)');
      if (checkboxes.length === 0) {
        document.getElementById('selectAllCheckbox').checked = false;
        return;
      }
      const allChecked = Array.from(checkboxes).every(cb => cb.checked);
      document.getElementById('selectAllCheckbox').checked = allChecked;
    }

    // Update Floating Action Button visibility
    function updateSelectedFAB() {
      const fab = document.getElementById('pushToCompanyFab');
      const text = document.getElementById('selectedCountText');
      
      text.innerText = selectedStudentIds.size;
      if (selectedStudentIds.size > 0) {
        fab.classList.remove('d-none');
      } else {
        fab.classList.add('d-none');
      }
    }

    // Open Push confirmation Modal
    function openPushModal() {
      const driveSelect = document.getElementById('driveSelect');
      const compName = driveSelect.options[driveSelect.selectedIndex].text.split('—')[0].trim();
      const driveName = driveSelect.options[driveSelect.selectedIndex].text.split('—')[1].trim();

      document.getElementById('modalStudentCount').innerText = selectedStudentIds.size;
      document.getElementById('modalCompanyName').value = compName;
      document.getElementById('modalDriveName').value = driveName;
      document.getElementById('pushNotes').value = '';

      const modal = new bootstrap.Modal(document.getElementById('pushConfirmModal'));
      modal.show();
    }

    // Submit assignment
    async function submitPush(e) {
      e.preventDefault();
      const driveId = document.getElementById('driveSelect').value;
      const compName = document.getElementById('modalCompanyName').value;

      // Close current modal
      bootstrap.Modal.getInstance(document.getElementById('pushConfirmModal')).hide();

      // Show SweetAlert2 confirmation
      const confirm = await Swal.fire({
        title: 'Confirm Assignment',
        text: `Are you sure you want to assign these ${selectedStudentIds.size} selected students to ${compName} drive?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'var(--pp-primary)',
        cancelButtonColor: '#cbd5e1',
        confirmButtonText: 'Yes, Assign Them'
      });

      if (!confirm.isConfirmed) return;

      // Show loading spinner
      Swal.fire({
        title: 'Processing Assignments',
        text: 'Please wait while we log roster credentials in PostgreSQL...',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      try {
        const payload = {
          student_ids: Array.from(selectedStudentIds),
          company_id: parseInt(driveId),
          stage: 'applied'
        };

        const result = await API.post('/students/bulk-push', payload);

        Swal.fire({
          title: '✅ Successfully Assigned',
          text: `${result.pushed_count} students have been successfully assigned to ${compName} drive.`,
          icon: 'success',
          showCancelButton: true,
          confirmButtonColor: 'var(--pp-primary)',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'View Company Dashboard',
          cancelButtonText: 'Dismiss'
        }).then((choice) => {
          if (choice.isConfirmed) {
            window.location.href = `company_dashboard.php?id=${driveId}`;
          } else {
            // Reload roster
            selectedStudentIds.clear();
            updateSelectedFAB();
            loadEligibleStudents(1);
          }
        });
      } catch (err) {
        Swal.fire({
          title: 'Assignment Failed',
          text: err.message || 'Something went wrong.',
          icon: 'error',
          confirmButtonColor: 'var(--pp-primary)'
        });
      }
    }

    // View Student Details popup (Stage history timeline)
    async function viewStudentDetails(id) {
      const modalBody = document.getElementById('detailModalBody');
      modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div> Loading student timeline history...</div>`;
      
      const modal = new bootstrap.Modal(document.getElementById('studentDetailModal'));
      modal.show();

      try {
        const s = await API.get(`/students/${id}`);
        document.getElementById('detailModalTitle').innerText = `${s.name} — Profile Tracker`;

        const modalSkills = Array.isArray(s.skills) ? s.skills : (typeof s.skills === 'string' ? s.skills.split(',') : []);
        const skillsHtml = modalSkills.length 
          ? modalSkills.map(sk => `<span class="skill-pill">${String(sk).trim()}</span>`).join('')
          : '<span class="text-muted small">-</span>';

        let historyHtml = '<p class="text-muted small">No active recruitment drive history recorded.</p>';
        if (s.placements && s.placements.length > 0) {
          historyHtml = s.placements.map(p => {
            const timelineEvents = p.timeline.map(t => {
              const dateStr = new Date(t.created_at).toLocaleDateString(undefined, {month: 'short', day: 'numeric', year: 'numeric'});
              return `
                <div class="d-flex gap-3 mb-2 small">
                  <div class="text-muted" style="min-width: 100px;">${dateStr}</div>
                  <div>
                    <span class="font-weight-700 text-dark">${t.stage.toUpperCase()}</span>
                    <span class="badge bg-${t.status === 'completed' ? 'success' : 'warning'} text-white rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">${t.status}</span>
                    <div class="text-muted mt-0.5">${t.remarks || 'No remarks'}</div>
                  </div>
                </div>
              `;
            }).join('');

            return `
              <div class="border rounded-3 p-3 mb-3 bg-light">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                  <span class="font-weight-800 text-dark h6 mb-0">${p.company_name}</span>
                  <span class="badge-pill-info font-weight-700">Stage: ${p.current_stage || 'Applied'}</span>
                </div>
                <div>${timelineEvents}</div>
              </div>
            `;
          }).join('');
        }

        modalBody.innerHTML = `
          <div class="row g-4 mb-4 pb-4 border-bottom">
            <div class="col-md-6 border-end">
              <div class="text-muted small">REGISTER NUMBER</div>
              <div class="font-weight-800 text-dark mb-3">${s.register_number || '-'}</div>
              <div class="text-muted small">DEPARTMENT & SECTION</div>
              <div class="font-weight-700 text-dark mb-3">${s.department_name} — Section ${s.section || 'A'}</div>
              <div class="text-muted small">ACADEMIC YEAR</div>
              <div class="font-weight-700 text-dark mb-3">${s.academic_year || '2026'}</div>
            </div>
            <div class="col-md-6">
              <div class="text-muted small">CGPA / STANDING</div>
              <div class="font-weight-800 text-primary h5 mb-3">${s.cgpa ? parseFloat(s.cgpa).toFixed(2) : '0.00'}</div>
              <div class="text-muted small">ACTIVE BACKLOGS</div>
              <div class="font-weight-700 text-dark mb-3">${s.backlogs ?? 0}</div>
              <div class="text-muted small">TECHNICAL SKILLS</div>
              <div class="mt-1">${skillsHtml}</div>
            </div>
          </div>
          <div>
            <h6 class="font-weight-800 text-dark mb-3"><i class="fa-solid fa-clock-rotate-left me-1"></i> Placement Drive Pipeline Timeline</h6>
            ${historyHtml}
          </div>
        `;
      } catch (err) {
        modalBody.innerHTML = `<div class="text-center text-danger py-4">Error loading details: ${err.message}</div>`;
      }
    }

    // Initialize Page
    document.addEventListener('DOMContentLoaded', async () => {
      await initFilters();
      await loadActiveDrives();
    });
  </script>
</body>
</html>
