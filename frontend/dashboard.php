<?php require_once 'config.php'; require_login(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Academic Overview — Placement Pro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

  <!-- Navigation Partial (Sidebar + Top Header) -->
  <?php include 'partials/nav.php'; ?>

  <!-- Main Content Wrapper -->
  <main id="main-wrapper">

    <!-- Header Area -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="h3 font-weight-800 mb-1">Academic Overview</h2>
        <p class="text-muted small mb-0">Placement Session 2023-2024</p>
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-pp-outline">
          <i class="fa-solid fa-filter"></i> Filter
        </button>
        <a href="download.php?type=pdf" class="btn btn-pp-primary text-white text-decoration-none">
          <i class="fa-solid fa-file-pdf"></i> Export PDF
        </a>
      </div>
    </div>

    <!-- Top KPI Cards (Row of 6) -->
    <div class="row g-3 mb-4">
      <!-- Total Students -->
      <div class="col-12 col-sm-6 col-lg-2">
        <div class="pp-card kpi-card">
          <div class="kpi-header">
            <span class="kpi-title">Total Students</span>
            <span class="badge-pill-success">0</span>
          </div>
          <div class="kpi-value" id="valTotalStudents">0</div>
          <div class="kpi-subtext">Registered for session</div>
        </div>
      </div>

      <!-- Total Companies -->
      <div class="col-12 col-sm-6 col-lg-2">
        <div class="pp-card kpi-card">
          <div class="kpi-header">
            <span class="kpi-title">Total Companies</span>
            <span class="badge-pill-info">New</span>
          </div>
          <div class="kpi-value" id="valTotalCompanies">0</div>
          <div class="kpi-subtext">Participating drives</div>
        </div>
      </div>

      <!-- Total Selected -->
      <div class="col-12 col-sm-6 col-lg-2">
        <div class="pp-card kpi-card">
          <div class="kpi-header">
            <span class="kpi-title">Total Selected</span>
            <span class="text-muted small font-weight-600">0%</span>
          </div>
          <div class="kpi-value" id="valTotalSelected">0</div>
          <div class="progress mt-2" style="height: 6px; border-radius: 999px;">
            <div class="progress-bar bg-success" style="width: 0%;"></div>
          </div>
        </div>
      </div>

      <!-- Placement % -->
      <div class="col-12 col-sm-6 col-lg-2">
        <div class="pp-card kpi-card">
          <div class="kpi-header">
            <span class="kpi-title">Placement %</span>
            <i class="fa-solid fa-chart-line text-primary" style="color: var(--pp-primary) !important;"></i>
          </div>
          <div class="kpi-value" id="valPlacementRate">0%</div>
          <div class="kpi-subtext">Batch conversion rate</div>
        </div>
      </div>

      <!-- Highest Package -->
      <div class="col-12 col-sm-6 col-lg-2">
        <div class="pp-card kpi-card">
          <div class="kpi-header">
            <span class="kpi-title">Highest Package</span>
            <i class="fa-solid fa-trophy text-warning"></i>
          </div>
          <div class="kpi-value" id="valHighestPackage">0</div>
          <div class="kpi-subtext font-weight-600 text-dark">Goldman Sachs</div>
        </div>
      </div>

      <!-- Average Package -->
      <div class="col-12 col-sm-6 col-lg-2">
        <div class="pp-card kpi-card">
          <div class="kpi-header">
            <span class="kpi-title">Avg Package</span>
            <span class="badge-pill-success">0</span>
          </div>
          <div class="kpi-value" id="valAvgPackage">0</div>
          <div class="kpi-subtext">Overall batch average</div>
        </div>
      </div>
    </div>

    <!-- Main Content - Middle Row -->
    <div class="row g-4 mb-4">
      <!-- Left: Company-wise Hiring Bar Chart -->
      <div class="col-12 col-lg-8">
        <div class="pp-card h-100">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <h5 class="h6 font-weight-700 mb-0">Company-wise Hiring</h5>
              <p class="text-muted small mb-0">Hiring offers distribution by top recruiters</p>
            </div>
            <select class="form-select-pp py-1 px-3 small" id="companyHiringTimeframe" name="company_hiring_timeframe">
              <option>Last 6 Months</option>
              <option>Last 3 Months</option>
              <option>Full Academic Year</option>
            </select>
          </div>
          <div style="height: 280px;">
            <canvas id="companyHiringChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Right: Section-wise Donut Chart -->
      <div class="col-12 col-lg-4">
        <div class="pp-card h-100">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="h6 font-weight-700 mb-0">Section-wise</h5>
            <span class="badge-pill-info">1.8k Placed</span>
          </div>
          <div class="position-relative d-flex justify-content-center align-items-center" style="height: 200px;">
            <canvas id="sectionDonutChart"></canvas>
            <div class="position-absolute text-center">
              <div class="h4 font-weight-800 mb-0">1.8k</div>
              <div class="text-muted small">Placed</div>
            </div>
          </div>
          <div class="mt-3 pt-3 border-top d-flex justify-content-around text-center">
            <div>
              <div class="small text-muted mb-1"><i class="fa-solid fa-circle me-1" style="color: #4F46E5;"></i> CS/IT</div>
              <div class="font-weight-700">950</div>
            </div>
            <div>
              <div class="small text-muted mb-1"><i class="fa-solid fa-circle me-1" style="color: #10B981;"></i> Electronics</div>
              <div class="font-weight-700">520</div>
            </div>
            <div>
              <div class="small text-muted mb-1"><i class="fa-solid fa-circle me-1" style="color: #F59E0B;"></i> Mechanical</div>
              <div class="font-weight-700">380</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content - Bottom Row -->
    <div class="row g-4">
      <!-- Left: Recent Activities Timeline -->
      <div class="col-12 col-lg-7">
        <div class="pp-card h-100">
          <h5 class="h6 font-weight-700 mb-3">Recent Activities</h5>
          <div class="d-flex flex-column gap-3">
            <div class="d-flex align-items-start gap-3 pb-3 border-bottom">
              <div class="d-flex align-items-center justify-content-center rounded-circle text-white bg-primary p-2" style="width: 36px; height: 36px; background-color: var(--pp-primary) !important;">
                <i class="fa-solid fa-building" style="font-size: 0.85rem;"></i>
              </div>
              <div class="flex-grow-1">
                <div class="font-weight-600 text-dark">TCS started recruitment drive</div>
                <div class="text-muted small">Phase 1 Online Assessment scheduled for 250 CS & IT students.</div>
              </div>
              <div class="text-muted small">10 mins ago</div>
            </div>

            <div class="d-flex align-items-start gap-3 pb-3 border-bottom">
              <div class="d-flex align-items-center justify-content-center rounded-circle text-white bg-success p-2" style="width: 36px; height: 36px;">
                <i class="fa-solid fa-check" style="font-size: 0.85rem;"></i>
              </div>
              <div class="flex-grow-1">
                <div class="font-weight-600 text-dark">Goldman Sachs published 18 final selections</div>
                <div class="text-muted small">Average package offered $42 LPA. Offer letters dispatched.</div>
              </div>
              <div class="text-muted small">2 hours ago</div>
            </div>

            <div class="d-flex align-items-start gap-3">
              <div class="d-flex align-items-center justify-content-center rounded-circle text-white bg-warning p-2" style="width: 36px; height: 36px;">
                <i class="fa-solid fa-file-invoice" style="font-size: 0.85rem;"></i>
              </div>
              <div class="flex-grow-1">
                <div class="font-weight-600 text-dark">New Batch Data Imported</div>
                <div class="text-muted small">Batch 2024 dataset imported containing 450 new eligible candidates.</div>
              </div>
              <div class="text-muted small">Yesterday</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Calendar Widget & Urgent Alerts -->
      <div class="col-12 col-lg-5 d-flex flex-column gap-3">
        <!-- Interactive Placement Calendar Widget -->
        <div class="pp-card">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="h6 font-weight-700 mb-0">Placement Calendar</h5>
            <div class="d-flex align-items-center gap-1">
              <button class="btn btn-sm btn-light border py-0 px-2 text-muted" title="Previous Month" onclick="changeCalendarMonth(-1)">
                <i class="fa-solid fa-chevron-left text-xs"></i>
              </button>
              <span class="text-muted small font-weight-700 mx-1" id="calendarMonthYearLabel">September 2026</span>
              <button class="btn btn-sm btn-light border py-0 px-2 text-muted" title="Next Month" onclick="changeCalendarMonth(1)">
                <i class="fa-solid fa-chevron-right text-xs"></i>
              </button>
            </div>
          </div>
          <!-- Dynamic Calendar Grid -->
          <div id="calendarGrid" class="d-grid gap-1" style="grid-template-columns: repeat(7, 1fr); text-align: center; font-size: 0.8rem;">
            <!-- Rendered dynamically via JS -->
          </div>
          <!-- Upcoming / Selected Event Details Box -->
          <div class="mt-3 pt-2 border-top d-flex align-items-start gap-2" id="calendarEventBox">
            <i class="fa-solid fa-calendar-day text-primary mt-1"></i>
            <div class="small w-100" id="calendarEventContent">
              <span class="font-weight-700 text-dark">Upcoming Events:</span> Loading placement schedule...
            </div>
          </div>
        </div>

        <!-- Urgent Alerts Box -->
        <div class="urgent-alert-box" id="repeatAlertsBox" style="display: none;">
          <i class="fa-solid fa-triangle-exclamation text-danger" style="font-size: 1.25rem;"></i>
          <div class="w-100">
            <div class="font-weight-700 mb-0 d-flex justify-content-between align-items-center" style="font-size: 0.9rem;">
              Repeat Shortlist Alerts
              <span class="badge bg-danger rounded-pill" id="repeatAlertsCount">0</span>
            </div>
            <div class="small mt-1 text-muted" id="repeatAlertsContent">
              <!-- Filled via JS -->
            </div>
          </div>
        </div>
      </div>
    </div>

  </main>

  <script>window.API_BASE = '<?php echo API_BASE; ?>'; window.API_TOKEN = '<?php echo $_SESSION['token']; ?>';</script>
  <script src="assets/js/api.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Initialize Company Hiring Bar Chart
    const ctxBar = document.getElementById('companyHiringChart').getContext('2d');
    new Chart(ctxBar, {
      type: 'bar',
      data: {
        labels: ['Goldman Sachs', 'Amazon', 'TCS Digital', 'Infosys', 'Wipro', 'Accenture'],
        datasets: [{
          label: 'Offers Made',
          data: [42, 65, 120, 180, 140, 210],
          backgroundColor: '#4F46E5',
          borderRadius: 6,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { grid: { color: '#F3F4F6' }, ticks: { color: '#6B7280' } },
          x: { grid: { display: false }, ticks: { color: '#6B7280' } }
        }
      }
    });

    // Initialize Section Donut Chart
    const ctxDonut = document.getElementById('sectionDonutChart').getContext('2d');
    new Chart(ctxDonut, {
      type: 'doughnut',
      data: {
        labels: ['CS/IT', 'Electronics', 'Mechanical'],
        datasets: [{
          data: [950, 520, 380],
          backgroundColor: ['#4F46E5', '#10B981', '#F59E0B'],
          borderWidth: 0,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '75%',
        plugins: { legend: { display: false } }
      }
    });

    // Fetch API Stats if backend available
    async function fetchStats() {
      try {
        const stats = await API.get('/dashboard/stats');
        if(stats.total_students) document.getElementById('valTotalStudents').innerText = stats.total_students.toLocaleString();
        if(stats.total_companies) document.getElementById('valTotalCompanies').innerText = stats.total_companies;
        if(stats.students_selected) document.getElementById('valTotalSelected').innerText = stats.students_selected.toLocaleString();
        if(stats.placement_percentage) document.getElementById('valPlacementRate').innerText = stats.placement_percentage + '%';
        if(stats.highest_package) document.getElementById('valHighestPackage').innerText = '₹' + Number(stats.highest_package).toLocaleString();
        if(stats.average_package) document.getElementById('valAvgPackage').innerText = '₹' + Number(stats.average_package).toLocaleString();
      } catch (err) {
        console.log('Using mock dashboard specs:', err.message);
      }
    }
    
    // Fetch Repeat Shortlist Alerts
    async function fetchAlerts() {
      try {
        const data = await API.get('/drives/repeat-alerts');
        if (data.alerts && data.alerts.length > 0) {
          const box = document.getElementById('repeatAlertsBox');
          const content = document.getElementById('repeatAlertsContent');
          const count = document.getElementById('repeatAlertsCount');
          
          box.style.display = 'flex';
          count.innerText = data.alerts.length;
          
          let html = '';
          data.alerts.slice(0, 3).forEach(a => {
            html += `<div class="mb-1 border-bottom pb-1">
                      <strong>${a.name}</strong> (${a.department}) — Shortlisted ${a.shortlist_count} times but not placed.
                      <br><span class="text-xs">Drives: ${a.drives.join(', ')}</span>
                     </div>`;
          });
          if (data.alerts.length > 3) {
            html += `<div class="text-center mt-2"><a href="students.php" class="text-decoration-none">View all in directory</a></div>`;
          }
          content.innerHTML = html;
        }
      } catch (err) {
        console.log('Failed to load alerts:', err.message);
      }
    }

    // Dynamic Placement Calendar Logic
    let calCurrentDate = new Date();
    let calCompanyEvents = [];
    let calSelectedDay = null;

    async function initCalendar() {
      try {
        calCompanyEvents = await API.get('/companies') || [];
      } catch (err) {
        console.warn('Failed to load company visit dates:', err.message);
        calCompanyEvents = [];
      }
      renderCalendar();
    }

    function changeCalendarMonth(delta) {
      calCurrentDate.setMonth(calCurrentDate.getMonth() + delta);
      calSelectedDay = null;
      renderCalendar();
    }

    function renderCalendar() {
      const year = calCurrentDate.getFullYear();
      const month = calCurrentDate.getMonth();

      const monthNames = ["January", "February", "March", "April", "May", "June", 
                          "July", "August", "September", "October", "November", "December"];

      const label = document.getElementById('calendarMonthYearLabel');
      if (label) label.textContent = `${monthNames[month]} ${year}`;

      const grid = document.getElementById('calendarGrid');
      if (!grid) return;
      grid.innerHTML = '';

      // Weekday headers
      const dayHeaders = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];
      dayHeaders.forEach(dh => {
        const headerEl = document.createElement('div');
        headerEl.className = 'font-weight-600 text-muted py-1';
        headerEl.style.fontSize = '0.75rem';
        headerEl.textContent = dh;
        grid.appendChild(headerEl);
      });

      const firstDay = new Date(year, month, 1).getDay();
      const daysInMonth = new Date(year, month + 1, 0).getDate();

      // Leading empty cells
      for (let i = 0; i < firstDay; i++) {
        const emptyCell = document.createElement('div');
        emptyCell.className = 'p-1 text-muted opacity-25';
        grid.appendChild(emptyCell);
      }

      const today = new Date();
      const isCurrentMonthYear = (today.getFullYear() === year && today.getMonth() === month);
      const todayDate = today.getDate();

      // Group events by day of month
      const eventsByDay = {};
      calCompanyEvents.forEach(c => {
        if (c.visit_date) {
          const parts = c.visit_date.split('-');
          if (parts.length === 3) {
            const vYear = parseInt(parts[0], 10);
            const vMonth = parseInt(parts[1], 10) - 1;
            const vDay = parseInt(parts[2], 10);

            if (vYear === year && vMonth === month) {
              if (!eventsByDay[vDay]) eventsByDay[vDay] = [];
              eventsByDay[vDay].push(c);
            }
          }
        }
      });

      for (let d = 1; d <= daysInMonth; d++) {
        const dayCell = document.createElement('div');
        dayCell.className = 'p-1 rounded-circle position-relative font-weight-600 transition-all cursor-pointer d-flex flex-column align-items-center justify-content-center';
        dayCell.style.height = '30px';
        dayCell.style.width = '30px';
        dayCell.style.margin = '0 auto';
        dayCell.style.userSelect = 'none';
        dayCell.style.fontSize = '0.8rem';
        dayCell.textContent = d;

        const hasEvents = eventsByDay[d] && eventsByDay[d].length > 0;
        const isToday = isCurrentMonthYear && (d === todayDate);
        const isSelected = calSelectedDay === d;

        if (isSelected) {
          dayCell.style.backgroundColor = '#312E81';
          dayCell.style.color = '#ffffff';
          dayCell.style.boxShadow = '0 2px 4px rgba(0,0,0,0.2)';
        } else if (isToday) {
          dayCell.style.backgroundColor = 'var(--pp-primary)';
          dayCell.style.color = '#ffffff';
          dayCell.style.fontWeight = '700';
        } else if (hasEvents) {
          dayCell.style.backgroundColor = '#EEF2FF';
          dayCell.style.color = '#4F46E5';
          dayCell.style.border = '1px solid #C7D2FE';
          dayCell.style.fontWeight = '700';
        } else {
          dayCell.style.color = '#374151';
        }

        if (hasEvents && !isToday && !isSelected) {
          const dot = document.createElement('span');
          dot.style.cssText = 'width: 4px; height: 4px; background-color: #4F46E5; border-radius: 50%; margin-top: 1px;';
          dayCell.appendChild(dot);
        }

        dayCell.onclick = () => {
          calSelectedDay = d;
          renderCalendar();
          updateEventDetails(d, eventsByDay[d], monthNames[month], year);
        };

        grid.appendChild(dayCell);
      }

      // Update details panel
      if (calSelectedDay && eventsByDay[calSelectedDay]) {
        updateEventDetails(calSelectedDay, eventsByDay[calSelectedDay], monthNames[month], year);
      } else {
        const totalEvents = Object.keys(eventsByDay).length;
        const content = document.getElementById('calendarEventContent');
        if (content) {
          if (totalEvents > 0) {
            content.innerHTML = `<span class="font-weight-700 text-dark">${totalEvents} Placement Drive(s)</span> scheduled in ${monthNames[month]} ${year}. Click a highlighted date to view details.`;
          } else {
            content.innerHTML = `<span class="font-weight-700 text-dark">No drives scheduled</span> for ${monthNames[month]} ${year}.`;
          }
        }
      }
    }

    function updateEventDetails(day, events, monthName, year) {
      const content = document.getElementById('calendarEventContent');
      if (!content) return;

      if (!events || events.length === 0) {
        content.innerHTML = `<span class="font-weight-700 text-dark">${monthName} ${day}, ${year}:</span> No events scheduled for this date.`;
        return;
      }

      const listHtml = events.map(e => {
        const role = e.job_role ? ` — ${e.job_role}` : '';
        const pkg = e.package_amount ? ` (Package: ₹${e.package_amount} LPA)` : '';
        return `<div><strong class="text-primary">${e.name}</strong>${role}${pkg}</div>`;
      }).join('');

      content.innerHTML = `<div><span class="font-weight-700 text-dark">${monthName} ${day}, ${year}:</span></div>${listHtml}`;
    }

    // Configurable Auto-Update Loop from Settings
    let autoUpdateTimer = null;

    function setupAutoUpdateLoop() {
      const enabled = localStorage.getItem('setting_auto_update') !== 'false';
      const intervalMs = parseInt(localStorage.getItem('setting_update_interval') || '30000', 10);

      if (autoUpdateTimer) clearInterval(autoUpdateTimer);

      if (enabled) {
        autoUpdateTimer = setInterval(() => {
          fetchStats();
          fetchAlerts();
          initCalendar();
        }, intervalMs);
      }
    }

    fetchStats();
    fetchAlerts();
    initCalendar();
    setupAutoUpdateLoop();
  </script>
</body>
</html>
