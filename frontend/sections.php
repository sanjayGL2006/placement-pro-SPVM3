<?php require_once 'config.php'; require_login(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sections Overview — Placement Pro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

  <!-- Layout Framing -->
  <?php include 'partials/nav.php'; ?>

  <main id="main-wrapper">

    <!-- Header Area & Segmented Tabs -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="h3 font-weight-800 mb-1">Sections Overview</h2>
        <p class="text-muted small mb-0">Cohort performance analysis across section divisions</p>
      </div>

      <!-- Segmented Control Tabs -->
      <div class="bg-white p-1 rounded-3 border d-flex gap-1 shadow-sm">
        <button class="btn btn-sm px-3 font-weight-600 rounded-2" style="background-color: var(--pp-primary-light); color: var(--pp-primary-dark); border: 1px solid var(--pp-primary);" onclick="switchSection(this, 'Section A')">Section A</button>
        <button class="btn btn-sm px-3 font-weight-600 text-muted rounded-2 border-0" onclick="switchSection(this, 'Section B')">Section B</button>
        <button class="btn btn-sm px-3 font-weight-600 text-muted rounded-2 border-0" onclick="switchSection(this, 'Section C')">Section C</button>
      </div>
    </div>

    <!-- Section KPI Cards -->
    <div class="row g-3 mb-4">
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="pp-card kpi-card">
          <div class="kpi-header">
            <span class="kpi-title">Total Students</span>
            <i class="fa-solid fa-users text-primary"></i>
          </div>
          <div class="kpi-value" id="secTotal">0</div>
          <div class="kpi-subtext">Section cohort count</div>
        </div>
      </div>

      <div class="col-12 col-sm-6 col-lg-3">
        <div class="pp-card kpi-card">
          <div class="kpi-header">
            <span class="kpi-title">Selected Students</span>
            <span class="text-muted small font-weight-600" id="secSelectedPctText">0%</span>
          </div>
          <div class="kpi-value" id="secSelected">0</div>
          <div class="progress mt-2" style="height: 6px; border-radius: 999px;">
            <div class="progress-bar bg-success" style="width: 0%;" id="secSelectedProgressBar"></div>
          </div>
        </div>
      </div>

      <div class="col-12 col-sm-6 col-lg-3">
        <div class="pp-card kpi-card">
          <div class="kpi-header">
            <span class="kpi-title">Placement %</span>
            <span class="badge-pill-success animate-pulse" id="secPctBadge">Target 0% Exceeded</span>
          </div>
          <div class="kpi-value" id="secPct">0%</div>
          <div class="kpi-subtext">Conversion rate</div>
        </div>
      </div>

      <div class="col-12 col-sm-6 col-lg-3">
        <div class="pp-card kpi-card">
          <div class="kpi-header">
            <span class="kpi-title">Avg. Package</span>
            <span class="badge-pill-info" id="secAvgBadge">Top 0% Batch</span>
          </div>
          <div class="kpi-value" id="secAvg">$0LPA</div>
          <div class="kpi-subtext" id="secHighestPackageText">Highest $0 LPA</div>
        </div>
      </div>
    </div>

    <!-- Widgets Row -->
    <div class="row g-4 mb-4">
      <!-- Company Distribution Donut Chart -->
      <div class="col-12 col-lg-6">
        <div class="pp-card h-100">
          <h5 class="h6 font-weight-700 mb-3">Company Distribution</h5>
          <div style="height: 220px;" class="position-relative d-flex justify-content-center">
            <canvas id="companyDistChart"></canvas>
          </div>
          <div class="row mt-3 pt-3 border-top text-center">
            <div class="col-3">
              <div class="small text-muted mb-1"><i class="fa-solid fa-circle me-1" style="color: #4F46E5;"></i> Product</div>
              <div class="font-weight-700" id="distProductPct">0%</div>
            </div>
            <div class="col-3">
              <div class="small text-muted mb-1"><i class="fa-solid fa-circle me-1" style="color: #10B981;"></i> Service</div>
              <div class="font-weight-700" id="distServicePct">0%</div>
            </div>
            <div class="col-3">
              <div class="small text-muted mb-1"><i class="fa-solid fa-circle me-1" style="color: #F59E0B;"></i> Fintech</div>
              <div class="font-weight-700" id="distFintechPct">0%</div>
            </div>
            <div class="col-3">
              <div class="small text-muted mb-1"><i class="fa-solid fa-circle me-1" style="color: #64748B;"></i> Others</div>
              <div class="font-weight-700" id="distOthersPct">0%</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Department Analytics Horizontal Progress Bars -->
      <div class="col-12 col-lg-6">
        <div class="pp-card h-100">
          <h5 class="h6 font-weight-700 mb-3">Department Analytics</h5>
          <div class="d-flex flex-column gap-3" id="deptAnalyticsContainer">
            <div>
              <div class="d-flex justify-content-between mb-1">
                <span class="font-weight-600 text-dark small">BCA</span>
                <span class="font-weight-700 text-primary small" style="color: var(--pp-primary) !important;">84.4% Placed (38 / 45)</span>
              </div>
              <div class="progress" style="height: 8px; border-radius: 999px;">
                <div class="progress-bar" style="width: 84%; background-color: var(--pp-primary);"></div>
              </div>
            </div>

            <div>
              <div class="d-flex justify-content-between mb-1">
                <span class="font-weight-600 text-dark small">BBA</span>
                <span class="font-weight-700 text-success small">74.2% Placed (26 / 35)</span>
              </div>
              <div class="progress" style="height: 8px; border-radius: 999px;">
                <div class="progress-bar bg-success" style="width: 74%;"></div>
              </div>
            </div>

            <div>
              <div class="d-flex justify-content-between mb-1">
                <span class="font-weight-600 text-dark small">B.Com</span>
                <span class="font-weight-700 text-warning small">76.6% Placed (23 / 30)</span>
              </div>
              <div class="progress" style="height: 8px; border-radius: 999px;">
                <div class="progress-bar bg-warning" style="width: 76%;"></div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

    <!-- Placement Pipeline Step Tracker (Bottom) -->
    <div class="pp-card">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h5 class="h6 font-weight-700 mb-0">Placement Pipeline Funnel</h5>
          <p class="text-muted small mb-0">Student progression & drop-off metrics through selection rounds</p>
        </div>
        <span class="badge-pill-success">Final Stage Complete</span>
      </div>

      <div class="pipeline-tracker my-3">
        <!-- Connecting line -->
        <div class="pipeline-connector">
          <div class="pipeline-connector-progress"></div>
        </div>

        <!-- Step 1: Eligible -->
        <div class="pipeline-step active">
          <div class="icon-circle">
            <i class="fa-solid fa-user-check"></i>
          </div>
          <div class="font-weight-700 text-dark">Eligible</div>
          <div class="text-muted small" id="funnelEligibleCount">0 Students</div>
          <div class="badge-pill-info mt-1" style="font-size: 0.7rem;" id="funnelEligibleBadge">0% Start</div>
        </div>

        <!-- Step 2: Aptitude -->
        <div class="pipeline-step active">
          <div class="icon-circle">
            <i class="fa-solid fa-laptop-code"></i>
          </div>
          <div class="font-weight-700 text-dark">Aptitude</div>
          <div class="text-muted small" id="funnelAptitudeCount">0 Passed</div>
          <div class="badge-pill-warning mt-1" style="font-size: 0.7rem;" id="funnelAptitudeBadge">-0 Drop-off</div>
        </div>

        <!-- Step 3: Technical -->
        <div class="pipeline-step active">
          <div class="icon-circle">
            <i class="fa-solid fa-code-branch"></i>
          </div>
          <div class="font-weight-700 text-dark">Technical</div>
          <div class="text-muted small" id="funnelTechnicalCount">0 Cleared</div>
          <div class="badge-pill-warning mt-1" style="font-size: 0.7rem;" id="funnelTechnicalBadge">-0 Drop-off</div>
        </div>

        <!-- Step 4: Selected -->
        <div class="pipeline-step active">
          <div class="icon-circle" style="background: #10B981;">
            <i class="fa-solid fa-trophy"></i>
          </div>
          <div class="font-weight-700 text-dark">Selected</div>
          <div class="text-muted small" id="funnelSelectedCount">0 Offers</div>
          <div class="badge-pill-success mt-1" style="font-size: 0.7rem;" id="funnelSelectedBadge">0% Overall</div>
        </div>
      </div>
    </div>

  </main>

  <script>window.API_BASE = '<?php echo API_BASE; ?>'; window.API_TOKEN = '<?php echo $_SESSION['token'] ?? ""; ?>';</script>
  <script src="assets/js/api.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    let companyDistChart = null;

    async function loadSectionStats(sectionName) {
      try {
        const stats = await API.get('/dashboard/sections?section=' + encodeURIComponent(sectionName));
        
        // 1. Update KPI Cards
        document.getElementById('secTotal').innerText = stats.total_students.toLocaleString();
        
        const total = stats.total_students || 0;
        const placed = stats.students_selected || 0;
        const selPct = stats.placement_percentage || 0;

        document.getElementById('secTotal').innerText = total.toLocaleString();
        document.getElementById('secSelectedPctText').innerText = selPct + '%';
        document.getElementById('secSelected').innerText = placed.toLocaleString();
        
        const secProgress = document.getElementById('secSelectedProgressBar');
        if (secProgress) {
          secProgress.style.width = selPct + '%';
        }
        
        const pctBadge = document.getElementById('secPctBadge');
        if (selPct >= 80) {
          pctBadge.className = 'badge-pill-success';
          pctBadge.innerText = 'Target 80% Exceeded';
        } else {
          pctBadge.className = 'badge-pill-warning';
          pctBadge.innerText = `Target ${selPct}% Exceeded`;
        }
        document.getElementById('secPct').innerText = selPct + '%';
        
        const topBatchPct = selPct > 0 ? 5 : 0;
        document.getElementById('secAvgBadge').innerText = `Top ${topBatchPct}% Batch`;
        document.getElementById('secAvg').innerText = `₹${(stats.average_package || 0).toLocaleString()} LPA`;
        document.getElementById('secHighestPackageText').innerText = `Highest ₹${(stats.highest_package || 0).toLocaleString()} LPA`;
        
        // 2. Update Company Distribution
        const dist = stats.company_distribution || { Product: 40, Service: 35, Fintech: 15, Others: 10 };
        document.getElementById('distProductPct').innerText = dist.Product + '%';
        document.getElementById('distServicePct').innerText = dist.Service + '%';
        document.getElementById('distFintechPct').innerText = dist.Fintech + '%';
        document.getElementById('distOthersPct').innerText = dist.Others + '%';
        
        // Rebuild Chart.js Donut
        if (companyDistChart) {
          companyDistChart.destroy();
        }
        
        const ctxDist = document.getElementById('companyDistChart').getContext('2d');
        companyDistChart = new Chart(ctxDist, {
          type: 'doughnut',
          data: {
            labels: ['Product', 'Service', 'Fintech', 'Others'],
            datasets: [{
              data: [dist.Product, dist.Service, dist.Fintech, dist.Others],
              backgroundColor: ['#4F46E5', '#10B981', '#F59E0B', '#64748B'],
              borderWidth: 0,
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: { legend: { display: false } }
          }
        });
        
        // 3. Update Department Analytics
        const deptContainer = document.getElementById('deptAnalyticsContainer');
        if (stats.departments && stats.departments.length > 0) {
          deptContainer.innerHTML = '';
          stats.departments.forEach(dept => {
            const div = document.createElement('div');
            let textClass = 'text-primary';
            let progressClass = 'bg-primary';
            if (dept.name.includes('Electronics') || dept.name.includes('Comm')) {
              textClass = 'text-success';
              progressClass = 'bg-success';
            } else if (dept.name.includes('Info') || dept.name.includes('Tech')) {
              textClass = 'text-warning';
              progressClass = 'bg-warning';
            }
            
            div.innerHTML = `
              <div class="d-flex justify-content-between mb-1">
                <span class="font-weight-600 text-dark small">${dept.name}</span>
                <span class="font-weight-700 ${textClass} small" style="${textClass === 'text-primary' ? 'color: var(--pp-primary) !important;' : ''}">${dept.percentage}% Placed (${dept.placed} / ${dept.total})</span>
              </div>
              <div class="progress" style="height: 8px; border-radius: 999px;">
                <div class="progress-bar ${progressClass}" style="width: ${dept.percentage}%; ${progressClass === 'bg-primary' ? 'background-color: var(--pp-primary);' : ''}"></div>
              </div>
            `;
            deptContainer.appendChild(div);
          });
        } else {
          // If no database data is found for this section yet, keep the requested department stats
          deptContainer.innerHTML = `
            <div>
              <div class="d-flex justify-content-between mb-1">
                <span class="font-weight-600 text-dark small">Computer Science</span>
                <span class="font-weight-700 text-primary small" style="color: var(--pp-primary) !important;">92% Placed (368 / 400)</span>
              </div>
              <div class="progress" style="height: 8px; border-radius: 999px;">
                <div class="progress-bar" style="width: 92%; background-color: var(--pp-primary);"></div>
              </div>
            </div>
            <div>
              <div class="d-flex justify-content-between mb-1">
                <span class="font-weight-600 text-dark small">Electronics & Comm.</span>
                <span class="font-weight-700 text-success small">78% Placed (195 / 250)</span>
              </div>
              <div class="progress" style="height: 8px; border-radius: 999px;">
                <div class="progress-bar bg-success" style="width: 78%;"></div>
              </div>
            </div>
            <div>
              <div class="d-flex justify-content-between mb-1">
                <span class="font-weight-600 text-dark small">Information Tech</span>
                <span class="font-weight-700 text-warning small">72% Placed (108 / 150)</span>
              </div>
              <div class="progress" style="height: 8px; border-radius: 999px;">
                <div class="progress-bar bg-warning" style="width: 72%;"></div>
              </div>
            </div>
          `;
        }
        
        // 4. Update Funnel
        const f = stats.funnel;
        document.getElementById('funnelEligibleCount').innerText = f.eligible + ' Students';
        document.getElementById('funnelEligibleBadge').innerText = selPct + '% Start';
        
        document.getElementById('funnelAptitudeCount').innerText = f.aptitude + ' Passed';
        const aptDrop = f.eligible - f.aptitude;
        document.getElementById('funnelAptitudeBadge').innerText = `-${aptDrop} Drop-off`;
        
        document.getElementById('funnelTechnicalCount').innerText = f.technical + ' Cleared';
        const techDrop = f.aptitude - f.technical;
        document.getElementById('funnelTechnicalBadge').innerText = `-${techDrop} Drop-off`;
        
        document.getElementById('funnelSelectedCount').innerText = f.selected + ' Offers';
        document.getElementById('funnelSelectedBadge').innerText = selPct + '% Overall';

        const connector = document.querySelector('.pipeline-connector-progress');
        if (connector) {
          let progressVal = 0;
          if (f.eligible > 0) {
             if (f.selected > 0) progressVal = 100;
             else if (f.technical > 0) progressVal = 66;
             else if (f.aptitude > 0) progressVal = 33;
          }
          connector.style.width = progressVal + '%';
        }
        
      } catch (err) {
        console.error('Failed to load section stats:', err);
      }
    }

    function switchSection(btn, secName) {
      document.querySelectorAll('.bg-white.p-1.rounded-3 button').forEach(b => {
        b.style.backgroundColor = 'transparent';
        b.style.color = '#6B7280';
        b.style.border = 'none';
        b.classList.remove('font-weight-700');
      });
      btn.style.backgroundColor = 'var(--pp-primary-light)';
      btn.style.color = 'var(--pp-primary-dark)';
      btn.style.border = '1px solid var(--pp-primary)';
      btn.classList.add('font-weight-700');
      
      loadSectionStats(secName);
    }

    // Initialize with Section A on load
    document.addEventListener('DOMContentLoaded', () => {
      loadSectionStats('Section A');
    });
  </script>
</body>
</html>
