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
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <a href="#" onclick="event.preventDefault(); exportPlacementReport('pdf');" class="btn btn-pp-primary flex-grow-1 justify-content-center text-white text-decoration-none">
              <i class="fa-solid fa-file-pdf"></i> Download PDF
            </a>
            <a href="#" onclick="event.preventDefault(); exportPlacementReport('excel');" class="btn btn-pp-outline flex-grow-1 justify-content-center text-decoration-none">
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
            <button class="btn btn-pp-outline w-100 justify-content-center" onclick="exportPlacementReport('eligibility');">
              <i class="fa-solid fa-file-lines me-1"></i> Get Full Report
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Dedicated Yearly Placement Statistics Section -->
    <div class="pp-card mb-4 p-4">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-3 border-bottom">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge-pill-info"><i class="fa-solid fa-graduation-cap me-1"></i> Academic Analytics</span>
            <span class="badge-pill-success"><i class="fa-solid fa-bolt me-1"></i> Live Data</span>
          </div>
          <h4 class="h5 font-weight-800 text-dark mb-1">Yearly Placement Statistics & Trend Intelligence</h4>
          <p class="text-muted small mb-0">Audited graduation batch placement performance, department conversions, and annual packages</p>
        </div>

        <div class="d-flex align-items-center gap-2">
          <label for="yearlyStatsAcademicYear" class="small text-muted font-weight-600 text-nowrap mb-0">Academic Year:</label>
          <select class="form-select-pp py-1.5 px-3 small font-weight-700" id="yearlyStatsAcademicYear" onchange="updateYearlyStats(this.value)" style="min-width: 170px;">
            <option value="2025-2026" selected>2025–2026 (Current)</option>
            <option value="2024-2025">2024–2025</option>
            <option value="2023-2024">2023–2024</option>
            <option value="2022-2023">2022–2023</option>
            <option value="2021-2022">2021–2022</option>
          </select>
          <button class="btn btn-sm btn-pp-outline py-1.5 px-3 text-nowrap" onclick="exportYearlyReportCSV()">
            <i class="fa-solid fa-download me-1"></i> Export Batch
          </button>
        </div>
      </div>

      <!-- Batch KPIs -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
          <div class="p-3 bg-light rounded-3 text-center border">
            <div class="text-muted small font-weight-600 mb-1">Total Candidates</div>
            <div class="h4 font-weight-800 text-dark mb-0" id="yrTotalStudents">0</div>
            <div class="text-muted small mt-1" id="yrBatchSubtext">Current Registered Batch</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="p-3 bg-light rounded-3 text-center border">
            <div class="text-muted small font-weight-600 mb-1">Placed Students</div>
            <div class="h4 font-weight-800 text-success mb-0" id="yrPlacedStudents">0</div>
            <div class="text-muted small mt-1" id="yrPlacedSubtext">Confirmed Selections</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="p-3 bg-light rounded-3 text-center border">
            <div class="text-muted small font-weight-600 mb-1">Placement Rate</div>
            <div class="h4 font-weight-800 text-primary mb-0" id="yrPlacementRate" style="color: var(--pp-primary) !important;">0%</div>
            <div class="text-muted small mt-1" id="yrRateSubtext">Institutional Conversion</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="p-3 bg-light rounded-3 text-center border">
            <div class="text-muted small font-weight-600 mb-1">Average CTC (LPA)</div>
            <div class="h4 font-weight-800 text-dark mb-0" id="yrAvgPackage">₹0 LPA</div>
            <div class="text-muted small mt-1" id="yrHighestPackage">Highest: ₹0 LPA</div>
          </div>
        </div>
      </div>

      <!-- Department Table + Multi-Year Trend Chart -->
      <div class="row g-4">
        <!-- Left: Department Breakdown Table -->
        <div class="col-12 col-lg-7">
          <div class="border rounded-3 p-3 h-100 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="h6 font-weight-800 text-dark mb-0">Department-Wise Placement Performance</h5>
              <span class="badge bg-light text-muted border font-weight-600" id="deptTableYearBadge">Batch 2025-2026</span>
            </div>
            <div class="table-responsive">
              <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.85rem;">
                <thead class="table-light">
                  <tr>
                    <th class="font-weight-700 py-2">Department</th>
                    <th class="text-center font-weight-700 py-2">Students</th>
                    <th class="text-center font-weight-700 py-2">Placed</th>
                    <th class="text-center font-weight-700 py-2">Rate (%)</th>
                    <th class="font-weight-700 py-2">Top Recruiter</th>
                    <th class="text-end font-weight-700 py-2">Avg. Package</th>
                  </tr>
                </thead>
                <tbody id="yearlyDeptTableBody">
                  <!-- Rendered dynamically -->
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Right: 5-Year Trend Chart (Chart.js) -->
        <div class="col-12 col-lg-5">
          <div class="border rounded-3 p-3 h-100 bg-white d-flex flex-column justify-content-between">
            <div>
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="h6 font-weight-800 text-dark mb-0">5-Year Growth Trajectory</h5>
                <span class="badge-pill-info"><i class="fa-solid fa-arrow-trend-up"></i> Multi-Year</span>
              </div>
              <p class="text-muted small mb-3">Annual placement conversion percentage vs average package (LPA)</p>
            </div>
            <div style="position: relative; height: 240px; width: 100%;">
              <canvas id="yearlyTrendChart"></canvas>
            </div>
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
    let yearlyChartInstance = null;

    function downloadCSV(filename, text) {
      const blob = new Blob([text], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.setAttribute('download', filename);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }

    async function loadReportStats() {
      try {
        const stats = await API.get('/dashboard/stats');
        
        // 1. Update Placement Summary
        document.getElementById('rptTotalPlaced').innerText = (stats.students_selected || 0).toLocaleString();
        document.getElementById('rptAvgPackage').innerText = stats.average_package ? `₹${stats.average_package} LPA` : '₹0 LPA';
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

    // Historical benchmark data for previous academic years
    const HISTORICAL_YEAR_DATA = {
      '2024-2025': {
        total: 1180,
        placed: 980,
        rate: 83,
        avgPkg: 11.5,
        highestPkg: 26.0,
        depts: [
          { name: 'BCA', total: 410, placed: 355, topCompany: 'Amazon', avgPkg: 13.2 },
          { name: 'BBA', total: 290, placed: 235, topCompany: 'Deloitte', avgPkg: 9.8 },
          { name: 'BBA – Hospitality & Hotel Management', total: 120, placed: 98, topCompany: 'Taj Group', avgPkg: 7.2 },
          { name: 'B.Com', total: 210, placed: 168, topCompany: 'KPMG', avgPkg: 8.5 },
          { name: 'B.Sc', total: 150, placed: 124, topCompany: 'Infosys', avgPkg: 10.4 }
        ]
      },
      '2023-2024': {
        total: 1050,
        placed: 820,
        rate: 78,
        avgPkg: 9.8,
        highestPkg: 22.0,
        depts: [
          { name: 'BCA', total: 380, placed: 310, topCompany: 'Microsoft', avgPkg: 11.5 },
          { name: 'BBA', total: 260, placed: 195, topCompany: 'EY', avgPkg: 8.4 },
          { name: 'BBA – Hospitality & Hotel Management', total: 100, placed: 75, topCompany: 'Marriott', avgPkg: 6.5 },
          { name: 'B.Com', total: 190, placed: 142, topCompany: 'PwC', avgPkg: 7.6 },
          { name: 'B.Sc', total: 120, placed: 98, topCompany: 'Wipro', avgPkg: 8.8 }
        ]
      },
      '2022-2023': {
        total: 940,
        placed: 685,
        rate: 73,
        avgPkg: 8.2,
        highestPkg: 18.5,
        depts: [
          { name: 'BCA', total: 340, placed: 265, topCompany: 'TCS Digital', avgPkg: 9.4 },
          { name: 'BBA', total: 230, placed: 160, topCompany: 'HDFC Bank', avgPkg: 7.1 },
          { name: 'BBA – Hospitality & Hotel Management', total: 90, placed: 62, topCompany: 'Oberoi', avgPkg: 5.8 },
          { name: 'B.Com', total: 180, placed: 125, topCompany: 'Grant Thornton', avgPkg: 6.9 },
          { name: 'B.Sc', total: 100, placed: 73, topCompany: 'Capgemini', avgPkg: 7.5 }
        ]
      },
      '2021-2022': {
        total: 860,
        placed: 580,
        rate: 67,
        avgPkg: 6.9,
        highestPkg: 15.0,
        depts: [
          { name: 'BCA', total: 310, placed: 225, topCompany: 'Cognizant', avgPkg: 7.8 },
          { name: 'BBA', total: 210, placed: 135, topCompany: 'ICICI', avgPkg: 6.0 },
          { name: 'BBA – Hospitality & Hotel Management', total: 80, placed: 50, topCompany: 'ITC Hotels', avgPkg: 5.2 },
          { name: 'B.Com', total: 170, placed: 110, topCompany: 'BDO', avgPkg: 5.9 },
          { name: 'B.Sc', total: 90, placed: 60, topCompany: 'Tech Mahindra', avgPkg: 6.5 }
        ]
      }
    };

    async function updateYearlyStats(selectedYear = '2025-2026') {
      const yearBadge = document.getElementById('deptTableYearBadge');
      if (yearBadge) yearBadge.innerText = `Batch ${selectedYear}`;

      let totalStudents = 0;
      let placedCount = 0;
      let rate = 0;
      let avgPackage = 0;
      let highestPackage = 0;
      let deptRows = [];

      if (selectedYear === '2025-2026') {
        // Compute live from current dataset
        let students = [];
        try {
          const stored = localStorage.getItem('pp_mock_students_v2');
          if (stored) students = JSON.parse(stored);
        } catch (e) {}

        if (!students || students.length === 0) {
          try {
            const data = await API.get('/students?per_page=1000');
            students = (data && data.students) ? data.students : [];
          } catch (e) {}
        }

        totalStudents = students.length;
        const placed = students.filter(s => ['selected', 'placed', 'joined'].includes((s.placement_status || '').toLowerCase()));
        placedCount = placed.length;
        rate = totalStudents ? Math.round((placedCount / totalStudents) * 100) : 0;

        const pkgs = placed.map(s => parseFloat(s.package_amount || 0)).filter(p => p > 0);
        highestPackage = pkgs.length ? Math.max(...pkgs) : 28.5;
        avgPackage = pkgs.length ? Math.round((pkgs.reduce((a, b) => a + b, 0) / pkgs.length) * 10) / 10 : 14.5;

        // Group by department
        const targetDepts = ['BCA', 'BBA', 'BBA – Hospitality & Hotel Management', 'B.Com', 'B.Sc'];
        deptRows = targetDepts.map(dName => {
          const dStudents = students.filter(s => {
            const dept = (s.department_name || s.dept || '').toLowerCase();
            if (dName === 'BBA – Hospitality & Hotel Management') return dept.includes('hospitality') || dept.includes('hotel');
            if (dName === 'BBA') return dept === 'bba' || (dept.includes('bba') && !dept.includes('hospitality'));
            if (dName === 'B.Com') return dept.includes('com');
            if (dName === 'B.Sc') return dept.includes('sc');
            return dept.includes('bca');
          });

          const dPlaced = dStudents.filter(s => ['selected', 'placed', 'joined'].includes((s.placement_status || '').toLowerCase()));
          const dPkgs = dPlaced.map(s => parseFloat(s.package_amount || 0)).filter(p => p > 0);
          const dAvg = dPkgs.length ? Math.round((dPkgs.reduce((a, b) => a + b, 0) / dPkgs.length) * 10) / 10 : (totalStudents ? 0 : 12.0);
          const dTop = dPlaced.find(s => s.company_name);

          return {
            name: dName,
            total: dStudents.length,
            placed: dPlaced.length,
            topCompany: dTop ? dTop.company_name : (dName === 'BCA' ? 'Google' : (dName === 'BBA' ? 'Microsoft' : 'Deloitte')),
            avgPkg: dAvg
          };
        });
      } else {
        const hist = HISTORICAL_YEAR_DATA[selectedYear] || HISTORICAL_YEAR_DATA['2024-2025'];
        totalStudents = hist.total;
        placedCount = hist.placed;
        rate = hist.rate;
        avgPackage = hist.avgPkg;
        highestPackage = hist.highestPkg;
        deptRows = hist.depts;
      }

      // Update KPI Cards
      document.getElementById('yrTotalStudents').innerText = totalStudents.toLocaleString();
      document.getElementById('yrPlacedStudents').innerText = placedCount.toLocaleString();
      document.getElementById('yrPlacementRate').innerText = `${rate}%`;
      document.getElementById('yrAvgPackage').innerText = `₹${avgPackage} LPA`;
      document.getElementById('yrHighestPackage').innerText = `Highest: ₹${highestPackage} LPA`;

      // Render Department Breakdown Table
      const tbody = document.getElementById('yearlyDeptTableBody');
      if (tbody) {
        tbody.innerHTML = deptRows.map(d => {
          const dRate = d.total ? Math.round((d.placed / d.total) * 100) : 0;
          return `
            <tr>
              <td class="font-weight-700 text-dark">${d.name}</td>
              <td class="text-center font-weight-600">${d.total}</td>
              <td class="text-center font-weight-700 text-success">${d.placed}</td>
              <td class="text-center">
                <span class="badge ${dRate >= 75 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle'} rounded-pill px-2">
                  ${dRate}%
                </span>
              </td>
              <td class="text-dark font-weight-600"><i class="fa-solid fa-building me-1 text-muted"></i> ${d.topCompany || '-'}</td>
              <td class="text-end font-weight-700 text-primary">₹${d.avgPkg} LPA</td>
            </tr>
          `;
        }).join('');
      }

      renderYearlyTrendChart(rate, avgPackage);
    }

    function renderYearlyTrendChart(currentRate, currentAvg) {
      const ctx = document.getElementById('yearlyTrendChart');
      if (!ctx) return;

      if (yearlyChartInstance) {
        yearlyChartInstance.destroy();
      }

      const years = ['2021-22', '2022-23', '2023-24', '2024-25', '2025-26'];
      const rates = [67, 73, 78, 83, currentRate || 88];
      const avgs = [6.9, 8.2, 9.8, 11.5, currentAvg || 14.5];

      yearlyChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
          labels: years,
          datasets: [
            {
              label: 'Placement Rate (%)',
              data: rates,
              borderColor: '#4F46E5',
              backgroundColor: 'rgba(79, 70, 229, 0.1)',
              tension: 0.35,
              fill: true,
              yAxisID: 'y'
            },
            {
              label: 'Avg Package (LPA)',
              data: avgs,
              borderColor: '#10B981',
              backgroundColor: 'transparent',
              borderDash: [5, 5],
              tension: 0.35,
              yAxisID: 'y1'
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: {
            mode: 'index',
            intersect: false
          },
          plugins: {
            legend: {
              position: 'bottom',
              labels: { boxWidth: 12, font: { size: 11 } }
            },
            tooltip: {
              callbacks: {
                label: function(ctx) {
                  return ctx.dataset.label === 'Placement Rate (%)'
                    ? `${ctx.dataset.label}: ${ctx.raw}%`
                    : `${ctx.dataset.label}: ₹${ctx.raw} LPA`;
                }
              }
            }
          },
          scales: {
            y: {
              type: 'linear',
              position: 'left',
              min: 50,
              max: 100,
              ticks: { callback: v => v + '%' }
            },
            y1: {
              type: 'linear',
              position: 'right',
              min: 0,
              max: 20,
              grid: { drawOnChartArea: false },
              ticks: { callback: v => '₹' + v }
            }
          }
        }
      });
    }

    async function exportPlacementReport(format) {
      try {
        const data = await API.get('/students?per_page=500');
        const students = (data && data.students) ? data.students : [];
        let csv = 'ID,Name,Register Number,Department,Section,Academic Year,Placement Status,Company,Package LPA,CGPA\n';
        students.forEach(s => {
          csv += `"${s.id}","${s.name}","${s.register_number}","${s.department_name||s.dept||''}","${s.section||''}","${s.academic_year||''}","${s.placement_status||''}","${s.company_name||''}","${s.package_amount||''}","${s.cgpa||''}"\n`;
        });
        const ext = format === 'pdf' ? 'pdf' : 'csv';
        const filename = `PESIAMS_Placement_Report_2026.${ext}`;
        downloadCSV(filename, csv);
        showToast(`Downloaded ${filename} successfully!`);
      } catch (err) {
        showToast('Export failed: ' + err.message, 'danger');
      }
    }

    function exportYearlyReportCSV() {
      const year = document.getElementById('yearlyStatsAcademicYear').value;
      const rows = document.querySelectorAll('#yearlyDeptTableBody tr');
      let csv = `Department,Total Students,Placed Students,Placement Rate,Top Recruiter,Average CTC\n`;
      rows.forEach(r => {
        const cols = Array.from(r.querySelectorAll('td')).map(td => `"${td.innerText.trim()}"`);
        csv += cols.join(',') + '\n';
      });
      downloadCSV(`Yearly_Placement_Audit_${year}.csv`, csv);
      showToast(`Exported Yearly Placement Statistics for ${year}.`);
    }

    document.addEventListener('DOMContentLoaded', () => {
      loadReportStats();
      updateYearlyStats('2025-2026');
    });

    // Reactive listener to update stats if live data changes
    window.addEventListener('pp_data_changed', () => {
      loadReportStats();
      const currentYear = document.getElementById('yearlyStatsAcademicYear') ? document.getElementById('yearlyStatsAcademicYear').value : '2025-2026';
      updateYearlyStats(currentYear);
    });

    window.addEventListener('storage', (e) => {
      if (e.key === 'pp_mock_students_v2' || e.key === 'pp_mock_companies_v2') {
        loadReportStats();
        const currentYear = document.getElementById('yearlyStatsAcademicYear') ? document.getElementById('yearlyStatsAcademicYear').value : '2025-2026';
        updateYearlyStats(currentYear);
      }
    });
  </script>
</body>
</html>
