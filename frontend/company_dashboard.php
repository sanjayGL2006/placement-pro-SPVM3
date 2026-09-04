<?php require_once 'config.php'; require_login(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Company Drive Dashboard — Placement Pro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="assets/css/style.css" rel="stylesheet">
  <style>
    .kpi-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 1rem;
    }
    .kpi-box {
      background: white;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 1.25rem;
      text-align: center;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .kpi-box:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    }
    .kpi-num {
      font-size: 1.75rem;
      font-weight: 800;
      margin-top: 0.5rem;
      margin-bottom: 0.25rem;
    }
    .company-profile-card {
      background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
      color: white;
      border: none;
      border-radius: 16px;
    }
  </style>
</head>
<body>

  <?php include 'partials/nav.php'; ?>

  <main id="main-wrapper">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="h3 font-weight-800 mb-1" id="dashHeaderTitle">Recruitment Drive Dashboard</h2>
        <p class="text-muted small mb-0">Monitor hiring stats, pipeline rounds, and select candidates</p>
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-outline-danger" onclick="deleteCurrentCompany()">
          <i class="fa-solid fa-trash me-1"></i> Delete Company
        </button>
        <a href="companies.php" class="btn btn-pp-outline">
          <i class="fa-solid fa-arrow-left"></i> Back to Directory
        </a>
      </div>
    </div>

    <!-- Company Profile Details -->
    <div class="company-profile-card p-4 mb-4 shadow-sm">
      <div class="row g-4 align-items-center">
        <div class="col-auto">
          <div class="rounded-circle bg-light text-primary d-flex align-items-center justify-content-center font-weight-800 text-uppercase" id="compInitials" style="width: 72px; height: 72px; font-size: 1.75rem; background-color: var(--pp-primary-light) !important; color: var(--pp-primary) !important;">
            CP
          </div>
        </div>
        <div class="col">
          <h3 class="h4 font-weight-800 mb-1" id="compName">Loading Recruiter...</h3>
          <p class="mb-2 opacity-75" id="compIndustry">IT Services & Consulting</p>
          <div class="d-flex flex-wrap gap-3 small opacity-90">
            <span><i class="fa-solid fa-location-dot me-1"></i> <span id="compLocation">Location</span></span>
            <span><i class="fa-solid fa-envelope me-1"></i> <span id="compHrEmail">hr@company.com</span></span>
            <span><i class="fa-solid fa-phone me-1"></i> <span id="compHrPhone">+91-9876543210</span></span>
          </div>
        </div>
        <div class="col-12 col-lg-4 text-lg-end text-start border-lg-start border-secondary pt-3 pt-lg-0">
          <div class="row g-2 text-center text-white">
            <div class="col-6 border-end border-secondary">
              <div class="small opacity-75">Package Offer</div>
              <div class="font-weight-800 h5 mb-0" id="compPackage">0 LPA</div>
            </div>
            <div class="col-6">
              <div class="small opacity-75">Hiring Date</div>
              <div class="font-weight-800 h5 mb-0" id="compVisitDate">TBD</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Placement Stage KPI Counters -->
    <h5 class="font-weight-800 text-dark mb-3"><i class="fa-solid fa-chart-bar me-1"></i> Placement Drive Funnel Stats</h5>
    <div class="kpi-grid mb-5">
      <!-- 1. Interested Students -->
      <div class="kpi-box">
        <div class="small text-muted font-weight-700">Interested Students</div>
        <div class="kpi-num text-dark" id="statInterested">0</div>
        <div class="progress" style="height: 4px;"><div class="progress-bar bg-secondary" style="width: 100%"></div></div>
      </div>
      <!-- 2. Assigned Students -->
      <div class="kpi-box">
        <div class="small text-muted font-weight-700">Assigned Students</div>
        <div class="kpi-num text-primary" id="statAssigned">0</div>
        <div class="progress" style="height: 4px;"><div class="progress-bar bg-primary" style="width: 100%"></div></div>
      </div>
      <!-- 3. Aptitude Attended -->
      <div class="kpi-box">
        <div class="small text-muted font-weight-700">Aptitude Attended</div>
        <div class="kpi-num text-warning" id="statAptitude">0</div>
        <div class="progress" style="height: 4px;"><div class="progress-bar bg-warning" style="width: 100%"></div></div>
      </div>
      <!-- 4. Technical Round -->
      <div class="kpi-box">
        <div class="small text-muted font-weight-700">Technical Round</div>
        <div class="kpi-num text-info" id="statTechnical">0</div>
        <div class="progress" style="height: 4px;"><div class="progress-bar bg-info" style="width: 100%"></div></div>
      </div>
      <!-- 5. HR Round -->
      <div class="kpi-box">
        <div class="small text-muted font-weight-700">HR Round</div>
        <div class="kpi-num text-dark" id="statHR">0</div>
        <div class="progress" style="height: 4px;"><div class="progress-bar bg-dark" style="width: 100%"></div></div>
      </div>
      <!-- 6. Selected -->
      <div class="kpi-box" style="border-color: #a7f3d0; background-color: #f6fdfa;">
        <div class="small text-success font-weight-700">Selected</div>
        <div class="kpi-num text-success" id="statSelected">0</div>
        <div class="progress" style="height: 4px;"><div class="progress-bar bg-success" style="width: 100%"></div></div>
      </div>
      <!-- 7. Rejected -->
      <div class="kpi-box" style="border-color: #fecaca; background-color: #fffbbfb;">
        <div class="small text-danger font-weight-700">Rejected</div>
        <div class="kpi-num text-danger" id="statRejected">0</div>
        <div class="progress" style="height: 4px;"><div class="progress-bar bg-danger" style="width: 100%"></div></div>
      </div>
      <!-- 8. Offer Letters -->
      <div class="kpi-box">
        <div class="small text-muted font-weight-700">Offer Letters</div>
        <div class="kpi-num text-primary" id="statOffers">0</div>
        <div class="progress" style="height: 4px;"><div class="progress-bar bg-primary" style="width: 100%"></div></div>
      </div>
      <!-- 9. Joined -->
      <div class="kpi-box">
        <div class="small text-muted font-weight-700">Joined</div>
        <div class="kpi-num text-success" id="statJoined">0</div>
        <div class="progress" style="height: 4px;"><div class="progress-bar bg-success" style="width: 100%"></div></div>
      </div>
    </div>

    <!-- Candidate Pipeline Table -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="font-weight-800 text-dark mb-0"><i class="fa-solid fa-user-graduate me-1"></i> Candidate Pipeline Roster</h5>
      <a href="push.php" class="btn btn-pp-primary btn-sm"><i class="fa-solid fa-plus me-1"></i> Assign New Candidates</a>
    </div>

    <div class="pp-card p-0 overflow-hidden mb-4">
      <div class="table-responsive">
        <table class="pp-table">
          <thead>
            <tr>
              <th style="padding-left: 1.5rem;">REGISTER NUMBER</th>
              <th>CANDIDATE NAME</th>
              <th>EMAIL</th>
              <th>PACKAGE AMOUNT</th>
              <th>HIRING STAGE</th>
              <th>DRIVE STATUS</th>
              <th>OFFER STATUS</th>
              <th class="text-end" style="padding-right: 1.5rem;">PIPELINE ACTION</th>
            </tr>
          </thead>
          <tbody id="rosterTableBody">
            <tr><td colspan="7" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div> Loading student roster...</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Update Stage Modal -->
    <div class="modal fade" id="updateStageModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
          <div class="modal-header border-0 pb-0 px-4 pt-4">
            <h5 class="modal-title font-weight-bold"><i class="fa-solid fa-user-gear text-primary me-2"></i> Update Candidate Hiring Stage</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <form id="updateStageForm" onsubmit="submitStageUpdate(event)">
              <input type="hidden" id="updateStudentId">
              
              <div class="mb-3">
                <label for="updateStudentName" class="form-label font-weight-600 small text-muted">CANDIDATE NAME</label>
                <input type="text" class="form-control form-control-pp bg-light" id="updateStudentName" name="student_name" readonly>
              </div>

              <div class="mb-3">
                <label for="updateStageSelect" class="form-label font-weight-600 small text-muted">CURRENT HIRING STAGE</label>
                <select class="form-select-pp w-100" id="updateStageSelect" name="stage" required>
                  <option value="applied">Applied (Initial Registration)</option>
                  <option value="aptitude_test">Aptitude Test</option>
                  <option value="technical_test">Technical Interview</option>
                  <option value="group_discussion">Group Discussion</option>
                  <option value="hr_interview">HR Interview</option>
                  <option value="selected">Selected (Hired)</option>
                  <option value="offer_letter_received">Offer Letter Received</option>
                  <option value="joined_company">Joined Company</option>
                </select>
              </div>

              <div class="mb-3">
                <label for="updateStatusSelect" class="form-label font-weight-600 small text-muted">ROUND STATUS</label>
                <select class="form-select-pp w-100" id="updateStatusSelect" name="status" required>
                  <option value="completed">Completed / Passed Round</option>
                  <option value="pending">Pending Review</option>
                  <option value="in_progress">In Progress</option>
                  <option value="failed">Failed / Rejected</option>
                </select>
              </div>

              <div class="mb-4">
                <label for="updateRemarks" class="form-label font-weight-600 small text-muted">REMARKS / NOTES</label>
                <textarea class="form-control form-control-pp" id="updateRemarks" name="remarks" rows="2" placeholder="e.g. Cleared technical interview with good feedback..."></textarea>
              </div>
              
              <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <button type="button" class="btn btn-pp-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-pp-primary"><i class="fa-solid fa-check me-1"></i> Save Changes</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Get query params
    const urlParams = new URLSearchParams(window.location.search);
    const companyId = urlParams.get('id');

    if (!companyId) {
      alert('Error: Company ID is required.');
      window.location.href = 'companies.php';
    }

    async function deleteCurrentCompany() {
      const compName = document.getElementById('compName').innerText || 'this company';
      if (confirm(`Are you sure you want to delete "${compName}"? All related placement records for this drive will be moved to the Recycle Bin.`)) {
        try {
          await API.del(`/companies/${companyId}`);
          showToast(`Company "${compName}" deleted successfully.`);
          setTimeout(() => {
            window.location.href = 'companies.php';
          }, 800);
        } catch (err) {
          showToast('Delete failed: ' + err.message, 'danger');
        }
      }
    }

    // Load company details & roster
    async function loadCompanyDetails() {
      try {
        const c = await API.get(`/companies/${companyId}`);
        if (!c) throw new Error('Company details not found');
        const compName = c.name || 'Campus Recruiter';
        document.getElementById('dashHeaderTitle').innerText = `${compName} — Recruitment Drive Dashboard`;
        document.getElementById('compName').innerText = compName;
        document.getElementById('compIndustry').innerText = c.industry || 'IT Services';
        document.getElementById('compLocation').innerText = c.location || 'TBD';
        document.getElementById('compHrEmail').innerText = c.hr_email || 'hr@company.com';
        document.getElementById('compHrPhone').innerText = c.hr_contact_number || '+91-9876543210';
        document.getElementById('compPackage').innerText = c.package_amount ? `${c.package_amount} LPA` : (c.avg_package ? `${c.avg_package} LPA` : '0 LPA');
        document.getElementById('compVisitDate').innerText = c.visit_date ? new Date(c.visit_date).toLocaleDateString(undefined, {month: 'short', day: 'numeric', year: 'numeric'}) : 'TBD';

        const initials = compName ? compName.split(' ').map(n => n[0]).filter(Boolean).join('').substring(0, 2).toUpperCase() : 'CD';
        document.getElementById('compInitials').innerText = initials || 'CD';

        // Render Roster
        renderRoster(c.selected_students || []);
      } catch (err) {
        showToast('Failed to load drive details: ' + err.message, 'danger');
      }
    }

    // Load company Stats
    async function loadCompanyStats() {
      try {
        const stats = await API.get(`/companies/${companyId}/stats`);
        document.getElementById('statInterested').innerText = stats.interested_students;
        document.getElementById('statAssigned').innerText = stats.assigned_students;
        document.getElementById('statAptitude').innerText = stats.aptitude_attended;
        document.getElementById('statTechnical').innerText = stats.technical_round;
        document.getElementById('statHR').innerText = stats.hr_round;
        document.getElementById('statSelected').innerText = stats.selected;
        document.getElementById('statRejected').innerText = stats.rejected;
        document.getElementById('statOffers').innerText = stats.offer_letters;
        document.getElementById('statJoined').innerText = stats.joined;
      } catch (err) {
        console.error('Failed to load stats:', err);
      }
    }

    // Render roster table
    function renderRoster(students) {
      const tbody = document.getElementById('rosterTableBody');
      if (!students || students.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted small"><i class="fa-regular fa-user me-1" style="font-size: 1.5rem;"></i> No candidates assigned to this drive yet.</td></tr>`;
        return;
      }

      tbody.innerHTML = students.map(s => {
        const pkg = s.package_amount ? `₹${parseFloat(s.package_amount).toFixed(2)} LPA` : '-';
        
        let statusBadge = `<span class="badge bg-secondary rounded-pill px-2 py-1 small">${s.offer_status || 'pending'}</span>`;
        if (s.offer_status === 'offered') statusBadge = `<span class="badge bg-success rounded-pill px-2 py-1 small">Offered</span>`;
        else if (s.offer_status === 'accepted') statusBadge = `<span class="badge bg-primary rounded-pill px-2 py-1 small">Accepted</span>`;
        else if (s.offer_status === 'declined') statusBadge = `<span class="badge bg-danger rounded-pill px-2 py-1 small">Declined</span>`;

        return `
          <tr>
            <td style="padding-left: 1.5rem;" class="font-weight-700 text-dark small">${s.register_number}</td>
            <td class="font-weight-700 text-dark">${s.name}</td>
            <td class="text-muted small">${s.email || '-'}</td>
            <td class="font-weight-700 text-dark">${pkg}</td>
            <td>
              <span class="badge bg-secondary rounded-pill px-2.5 py-1 text-uppercase font-weight-700" style="font-size: 0.7rem;">${s.current_stage || 'applied'}</span>
            </td>
            <td>
              <span class="badge bg-info rounded-pill px-2.5 py-1 text-uppercase font-weight-700" style="font-size: 0.7rem;">${s.drive_status || 'INTERESTED'}</span>
            </td>
            <td>${statusBadge}</td>
            <td class="text-end" style="padding-right: 1.5rem;">
              <a href="documents.php?id=${s.id}" class="btn btn-sm btn-outline-info py-1 px-2 me-1" title="Documents"><i class="fa-solid fa-folder-open"></i></a>
              <button class="btn btn-sm btn-pp-primary py-1 px-2.5" onclick="openUpdateStageModal(${s.id}, '${s.name.replace(/'/g, "\\'")}', '${s.current_stage || 'applied'}')">
                <i class="fa-solid fa-user-pen"></i> Update Stage
              </button>
            </td>
          </tr>
        `;
      }).join('');
    }

    // Open Update Stage modal
    function openUpdateStageModal(studentId, studentName, currentStage) {
      document.getElementById('updateStudentId').value = studentId;
      document.getElementById('updateStudentName').value = studentName;
      document.getElementById('updateStageSelect').value = currentStage;
      document.getElementById('updateStatusSelect').value = 'completed';
      document.getElementById('updateRemarks').value = '';

      const modal = new bootstrap.Modal(document.getElementById('updateStageModal'));
      modal.show();
    }

    // Submit stage update
    async function submitStageUpdate(e) {
      e.preventDefault();
      const studentId = document.getElementById('updateStudentId').value;
      const stage = document.getElementById('updateStageSelect').value;
      const status = document.getElementById('updateStatusSelect').value;
      const remarks = document.getElementById('updateRemarks').value;

      try {
        await API.post(`/students/${studentId}/pipeline`, {
          company_id: parseInt(companyId),
          stage: stage,
          status: status,
          remarks: remarks
        });

        // If it is 'selected' or 'joined_company', let's also update placement offer_status/student placement_status
        if (stage === 'selected' || stage === 'offer_letter_received') {
          // Update student placement status
          await API.put(`/students/${studentId}`, {
            placement_status: 'selected'
          });
        } else if (stage === 'joined_company') {
          await API.put(`/students/${studentId}`, {
            placement_status: 'joined'
          });
        }

        // Show SweetAlert2 Success
        Swal.fire({
          title: 'Stage Updated!',
          text: 'Candidate hiring stage has been successfully updated.',
          icon: 'success',
          confirmButtonColor: 'var(--pp-primary)'
        });

        // Hide Modal
        bootstrap.Modal.getInstance(document.getElementById('updateStageModal')).hide();

        // Refresh stats & details
        loadCompanyDetails();
        loadCompanyStats();

      } catch (err) {
        Swal.fire({
          title: 'Update Failed',
          text: err.message,
          icon: 'error',
          confirmButtonColor: 'var(--pp-primary)'
        });
      }
    }

    // Initial Load
    document.addEventListener('DOMContentLoaded', () => {
      loadCompanyDetails();
      loadCompanyStats();
    });
  </script>
</body>
</html>
