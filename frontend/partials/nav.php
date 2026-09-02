<?php
require_once __DIR__ . '/sidebar.php';
require_once __DIR__ . '/header.php';
?>

<!-- SweetAlert2 for beautiful popups -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Global Post New Job Modal -->
<div class="modal fade" id="postJobModal" tabindex="-1" aria-labelledby="postJobModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
      <div class="modal-header border-0 pb-0 px-4 pt-4">
        <h5 class="modal-title font-weight-bold" id="postJobModalLabel">
          <i class="fa-solid fa-briefcase text-primary me-2" style="color: var(--pp-primary) !important;"></i> Post New Job Drive
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="postJobForm">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="jobCompName" class="form-label font-weight-600 text-muted small">COMPANY NAME</label>
              <input type="text" class="form-control form-control-pp" id="jobCompName" name="company_name" placeholder="e.g. Wipro" required>
            </div>
            <div class="col-md-6">
              <label for="jobRole" class="form-label font-weight-600 text-muted small">ROLE / POSITION</label>
              <input type="text" class="form-control form-control-pp" id="jobRole" name="role" placeholder="e.g. Software Development Engineer" required>
            </div>
            <div class="col-md-6">
              <label for="jobPackage" class="form-label font-weight-600 text-muted small">PACKAGE (LPA)</label>
              <input type="number" step="0.01" class="form-control form-control-pp" id="jobPackage" name="package_lpa" placeholder="e.g. 14.5" required>
            </div>
            <div class="col-md-6">
              <label for="jobDate" class="form-label font-weight-600 text-muted small">DRIVE DATE (INTERVIEW DATE)</label>
              <input type="date" class="form-control form-control-pp" id="jobDate" name="drive_date" required>
            </div>
            <div class="col-md-4">
              <label for="jobMinCgpa" class="form-label font-weight-600 text-muted small">MINIMUM CGPA</label>
              <input type="number" step="0.1" min="0" max="10" class="form-control form-control-pp" id="jobMinCgpa" name="min_cgpa" value="6.0" required>
            </div>
            <div class="col-md-4">
              <label for="jobMaxBacklogs" class="form-label font-weight-600 text-muted small">MAX ALLOWED BACKLOGS</label>
              <input type="number" min="0" class="form-control form-control-pp" id="jobMaxBacklogs" name="max_backlogs" value="0" required>
            </div>
            <div class="col-md-4">
              <label for="jobDeadline" class="form-label font-weight-600 text-muted small">REGISTRATION DEADLINE</label>
              <input type="date" class="form-control form-control-pp" id="jobDeadline" name="registration_deadline" required>
            </div>
            <div class="col-md-12">
              <label for="deptBCA" class="form-label font-weight-600 text-muted small">ELIGIBLE DEPARTMENTS</label>
              <div class="d-flex flex-wrap gap-3">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" checked id="deptBCA" name="dept_bca">
                  <label class="form-check-label" for="deptBCA">BCA</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" checked id="deptBSc" name="dept_bsc">
                  <label class="form-check-label" for="deptBSc">B.Sc</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="deptBCom" name="dept_bcom">
                  <label class="form-check-label" for="deptBCom">B.Com</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="deptBBA" name="dept_bba">
                  <label class="form-check-label" for="deptBBA">BBA</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="deptBBAH" name="dept_bbah">
                  <label class="form-check-label" for="deptBBAH">BBA - Hospitality</label>
                </div>
              </div>
            </div>
            <div class="col-md-12">
              <label for="jobDesc" class="form-label font-weight-600 text-muted small">JOB DESCRIPTION & REQUIREMENTS</label>
              <textarea class="form-control form-control-pp" id="jobDesc" name="job_description" rows="3" placeholder="Enter drive details..."></textarea>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4 pt-2 border-top">
            <button type="button" class="btn btn-pp-outline" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-pp-primary"><i class="fa-solid fa-paper-plane me-1"></i> Publish Drive</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  window.API_BASE = '<?php echo API_BASE; ?>';
  window.API_TOKEN = '<?php echo $_SESSION['token'] ?? ""; ?>';
</script>
<script src="assets/js/api.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('postJobForm');
  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const selectedDepts = [];
      if (document.getElementById('deptBCA').checked) selectedDepts.push('BCA');
      if (document.getElementById('deptBSc').checked) selectedDepts.push('B.Sc');
      if (document.getElementById('deptBCom').checked) selectedDepts.push('B.Com');
      if (document.getElementById('deptBBA').checked) selectedDepts.push('BBA');
      if (document.getElementById('deptBBAH').checked) selectedDepts.push('BBA - Hospitality & Hotel Management');

      const payload = {
        name: document.getElementById('jobCompName').value,
        job_role: document.getElementById('jobRole').value,
        package_amount: parseFloat(document.getElementById('jobPackage').value) || 0,
        visit_date: document.getElementById('jobDate').value || null,
        last_date: document.getElementById('jobDeadline').value || null,
        min_cgpa: parseFloat(document.getElementById('jobMinCgpa').value) || 0.0,
        allowed_backlogs: parseInt(document.getElementById('jobMaxBacklogs').value) || 0,
        eligible_departments: selectedDepts.join(','),
        industry: 'IT Services',
        description: document.getElementById('jobDesc').value || ''
      };

      try {
        await API.post('/companies', payload);
        
        Swal.fire({
          title: 'Success!',
          text: 'Job drive posted successfully to PostgreSQL database.',
          icon: 'success',
          confirmButtonColor: 'var(--pp-primary)'
        }).then(() => {
          const modalEl = document.getElementById('postJobModal');
          const modalInstance = bootstrap.Modal.getInstance(modalEl);
          if (modalInstance) modalInstance.hide();
          form.reset();
          
          if (typeof loadCompanies === 'function') {
            loadCompanies();
          } else {
            window.location.reload();
          }
        });
      } catch (err) {
        Swal.fire({
          title: 'Error!',
          text: 'Failed to publish drive: ' + err.message,
          icon: 'error',
          confirmButtonColor: 'var(--pp-primary)'
        });
      }
    });
  }
});
</script>

