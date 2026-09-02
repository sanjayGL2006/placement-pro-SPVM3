<?php require_once 'config.php'; require_login(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Analytics & Reports — Placement Pro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

  <?php include 'partials/nav.php'; ?>

  <main id="main-wrapper">

    <!-- Header Area & Dropdowns -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="h3 font-weight-800 mb-1">Analytics & Reports</h2>
        <p class="text-muted small mb-0">Generate, export, and build institutional placement audit reports</p>
      </div>

      <div class="d-flex gap-2">
        <select class="form-select-pp py-1 px-3 small" id="reportDateRange" name="report_date_range" aria-label="Select date range">
          <option>Last 30 Days</option>
          <option>Last Quarter</option>
          <option>Full Academic Year 2023-2024</option>
        </select>
        <select class="form-select-pp py-1 px-3 small" id="reportDeptFilter" name="report_dept_filter" aria-label="Select department filter">
          <option>All Departments</option>
          <option>Computer Science</option>
          <option>Electronics & Comm.</option>
          <option>Information Tech</option>
        </select>
      </div>
    </div>

    <!-- Top Row Cards -->
    <div class="row g-4 mb-4">
      <!-- 1. Placement Summary 2026 -->
      <div class="col-12 col-lg-6">
        <div class="pp-card h-100 d-flex flex-column justify-content-between">
          <div>
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="h6 font-weight-800 text-dark mb-0">Placement Summary 2026</h5>
              <span class="badge-pill-success"><i class="fa-solid fa-check"></i> Audit Ready</span>
            </div>
 
            <!-- Primary Metrics Grid -->
            <div class="row g-3 my-2">
              <div class="col-4 text-center border-end">
                <div class="text-muted small font-weight-600">Total Placed</div>
                <div class="font-weight-800 text-dark" style="font-size: 1.5rem;" id="rptTotalPlaced">0</div>
              </div>
              <div class="col-4 text-center border-end">
                <div class="text-muted small font-weight-600">Avg. Package</div>
                <div class="font-weight-800 text-primary" style="font-size: 1.5rem; color: var(--pp-primary) !important;" id="rptAvgPackage">$0 LPA</div>
              </div>
              <div class="col-4 text-center">
                <div class="text-muted small font-weight-600">Total Offers</div>
                <div class="font-weight-800 text-dark" style="font-size: 1.5rem;" id="rptTotalOffers">0</div>
              </div>
            </div>
          </div>
 
          <!-- Action Buttons -->
          <div class="pt-3 border-top d-flex gap-2">
            <a href="download.php?type=pdf" class="btn btn-pp-primary flex-grow-1 justify-content-center text-white text-decoration-none">
              <i class="fa-solid fa-file-pdf"></i> Download PDF
            </a>
            <a href="download.php?type=excel" class="btn btn-pp-outline flex-grow-1 justify-content-center text-decoration-none">
              <i class="fa-solid fa-file-excel text-success"></i> Export Excel
            </a>
          </div>
        </div>
      </div>
 
      <!-- 2. Student Eligibility Card -->
      <div class="col-12 col-lg-6">
        <div class="pp-card h-100 d-flex flex-column justify-content-between">
          <div>
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="h6 font-weight-800 text-dark mb-0">Student Eligibility</h5>
              <span class="badge-pill-info" id="rptEligibilityBadge">0 / 00 Candidates</span>
            </div>
 
            <p class="text-muted small mb-3" id="rptEligibilityText">0% of total registered students meet baseline academic and attendance criteria for corporate hiring drives.</p>
 
            <!-- Thick Horizontal Progress Bar -->
            <div class="my-3">
              <div class="d-flex justify-content-between small font-weight-600 text-dark mb-1">
                <span id="rptEligibleLabel">Eligible Candidates (0%)</span>
                <span id="rptIneligibleLabel">Ineligible (0%)</span>
              </div>
              <div class="progress" style="height: 14px; border-radius: 999px; background-color: #E2E8F0;">
                <div class="progress-bar" style="width: 0%; background-color: var(--pp-primary);" id="rptEligibilityBar"></div>
              </div>
            </div>
          </div>
 
          <!-- Action Button -->
          <div class="pt-3 border-top">
            <button class="btn btn-pp-outline w-100 justify-content-center" onclick="showToast('Generating full eligibility breakdown PDF...');">
              <i class="fa-solid fa-file-lines me-1"></i> Get Full Report
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Middle Row: Advanced Analysis (3 Identical Metric Cards) -->
    <div class="row g-4 mb-4">
      <!-- 1. Company Performance -->
      <div class="col-12 col-md-4">
        <div class="pp-card h-100 d-flex flex-column justify-content-between">
          <div>
            <h5 class="h6 font-weight-800 text-dark mb-2">Company Performance</h5>
            <p class="text-muted small mb-3">Breakdown of recruiter offer ratios, compensation brackets, and retention statistics.</p>
          </div>
          <div class="pt-3 border-top d-flex gap-2">
            <button class="btn btn-pp-outline btn-sm flex-grow-1 justify-content-center" onclick="showToast('Exporting Company Performance PDF');"><i class="fa-regular fa-file-pdf text-danger"></i> PDF</button>
            <button class="btn btn-pp-outline btn-sm flex-grow-1 justify-content-center" onclick="showToast('Exporting Company Performance XLS');"><i class="fa-regular fa-file-excel text-success"></i> XLS</button>
          </div>
        </div>
      </div>

      <!-- 2. Sector Wise Growth -->
      <div class="col-12 col-md-4">
        <div class="pp-card h-100 d-flex flex-column justify-content-between">
          <div>
            <h5 class="h6 font-weight-800 text-dark mb-2">Sector Wise Growth</h5>
            <p class="text-muted small mb-3">Year-over-year comparison across Product, Service, Semiconductor, and Consulting domains.</p>
          </div>
          <div class="pt-3 border-top d-flex gap-2">
            <button class="btn btn-pp-outline btn-sm flex-grow-1 justify-content-center" onclick="showToast('Exporting Sector Growth PDF');"><i class="fa-regular fa-file-pdf text-danger"></i> PDF</button>
            <button class="btn btn-pp-outline btn-sm flex-grow-1 justify-content-center" onclick="showToast('Exporting Sector Growth XLS');"><i class="fa-regular fa-file-excel text-success"></i> XLS</button>
          </div>
        </div>
      </div>

      <!-- 3. Skill Gap Analysis -->
      <div class="col-12 col-md-4">
        <div class="pp-card h-100 d-flex flex-column justify-content-between">
          <div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h5 class="h6 font-weight-800 text-dark mb-0">Skill Gap Analysis</h5>
              <span class="badge-pill-warning"><i class="fa-solid fa-magnifying-glass-chart"></i> Deep Dive</span>
            </div>
            <p class="text-muted small mb-3">Recruiter-demanded skills vs. student body prevalence — identify critical gaps and plan targeted workshops.</p>
          </div>
          <div class="pt-3 border-top">
            <a href="skill_gap.php" class="btn btn-pp-primary w-100 justify-content-center text-white text-decoration-none">
              <i class="fa-solid fa-arrow-right me-1"></i> View Full Analysis
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom Row: Diversity Dashboard & Custom Report Builder Promo Card -->
    <div class="row g-4">
      <!-- Left: Diversity Dashboard Card -->
      <div class="col-12 col-lg-6">
        <div class="pp-card h-100 d-flex flex-column justify-content-between">
          <div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h5 class="h6 font-weight-800 text-dark mb-0">Diversity Dashboard</h5>
              <span class="badge-pill-success">Equal Opportunity</span>
            </div>
            <p class="text-muted small mb-3">Gender balance and inclusion metrics across engineering streams and compensation tiers.</p>
          </div>
          <div class="pt-3 border-top d-flex gap-2">
            <button class="btn btn-pp-outline btn-sm flex-grow-1 justify-content-center" onclick="showToast('Exporting Diversity PDF');"><i class="fa-regular fa-file-pdf text-danger"></i> PDF</button>
            <button class="btn btn-pp-outline btn-sm flex-grow-1 justify-content-center" onclick="showToast('Exporting Diversity XLS');"><i class="fa-regular fa-file-excel text-success"></i> XLS</button>
          </div>
        </div>
      </div>

      <!-- Right: Massive Solid Purple Custom Report Builder Promo Card -->
      <div class="col-12 col-lg-6">
        <div class="promo-purple-card h-100 d-flex flex-column justify-content-between cursor-pointer" onclick="showToast('Launching Custom Report Builder Studio...');">
          <div>
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span class="badge bg-white text-dark font-weight-700 px-3 py-1 rounded-pill small">PRO FEATURE</span>
              <i class="fa-solid fa-arrow-up-right-from-square text-white" style="font-size: 1.25rem;"></i>
            </div>
            <h4 class="h5 font-weight-800 mb-2">Custom Report Builder</h4>
            <p class="text-white-50 small mb-0" style="max-width: 440px;">
              Create tailor-made analytical reports with drag-and-drop metric aggregators, custom date ranges, and automated email scheduling.
            </p>
          </div>
          <div class="mt-4 pt-3 border-top border-white-50 d-flex align-items-center gap-2 font-weight-700">
            <span>Build New Custom Audit Report</span>
            <i class="fa-solid fa-arrow-right"></i>
          </div>
        </div>
      </div>
    </div>

  </main>

  <script>window.API_BASE = '<?php echo API_BASE; ?>'; window.API_TOKEN = '<?php echo $_SESSION['token'] ?? ""; ?>';</script>
  <script src="assets/js/api.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    async function loadReportStats() {
      try {
        const stats = await API.get('/dashboard/stats');
        
        // 1. Update Placement Summary
        document.getElementById('rptTotalPlaced').innerText = (stats.students_selected || 0).toLocaleString();
        document.getElementById('rptAvgPackage').innerText = stats.average_package ? `$${stats.average_package} LPA` : '$0 LPA';
        document.getElementById('rptTotalOffers').innerText = (stats.total_offer_letters || 0).toLocaleString();
        
        // 2. Update Student Eligibility
        const total = stats.total_students || 0;
        const eligible = stats.eligible_students || 0;
        const pct = total ? Math.round((eligible / total) * 100) : 0;
        const ineligiblePct = total ? (100 - pct) : 0;
        
        document.getElementById('rptEligibilityBadge').innerText = `${eligible.toLocaleString()} / ${total.toLocaleString()} Candidates`;
        document.getElementById('rptEligibilityText').innerText = `${pct}% of total registered students meet baseline academic and attendance criteria for corporate hiring drives.`;
        document.getElementById('rptEligibleLabel').innerText = `Eligible Candidates (${pct}%)`;
        document.getElementById('rptIneligibleLabel').innerText = `Ineligible (${ineligiblePct}%)`;
        document.getElementById('rptEligibilityBar').style.width = pct + '%';
        
      } catch (err) {
        console.error('Failed to load report stats:', err);
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      loadReportStats();
    });
  </script>
</body>
</html>
