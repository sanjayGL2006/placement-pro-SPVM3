<?php require_once 'config.php';
require_login(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Skill Gap Analysis — Placement Pro</title>
  <meta name="description"
    content="Skill Gap Analysis report showing recruiter-demanded skills versus student body skills to design targeted training workshops.">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>

  <?php include 'partials/nav.php'; ?>

  <main id="main-wrapper">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
      <div>
        <div class="d-flex align-items-center gap-2 mb-1">
          <span class="sg-page-icon"><i class="fa-solid fa-magnifying-glass-chart"></i></span>
          <h2 class="h3 font-weight-800 mb-0">Skill Gap Analysis</h2>
        </div>
        <p class="text-muted small mb-0">Recruiter-demanded skills vs. student body prevalence — identify gaps and plan
          targeted workshops</p>
      </div>

      <div class="d-flex gap-2 align-items-center flex-wrap">
        <select class="form-select-pp py-1 px-3 small" id="sgDeptFilter">
          <option value="">All Departments</option>
        </select>
        <button class="btn btn-pp-outline btn-sm" onclick="exportSkillGap('pdf')">
          <i class="fa-solid fa-file-pdf text-danger"></i> PDF
        </button>
        <button class="btn btn-pp-outline btn-sm" onclick="exportSkillGap('excel')">
          <i class="fa-solid fa-file-excel text-success"></i> Excel
        </button>
      </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row g-4 mb-4" id="sgKpiRow">
      <!-- 1. Total Skills Tracked -->
      <div class="col-6 col-lg-3">
        <div class="pp-card kpi-card sg-kpi-card">
          <div class="kpi-header">
            <span class="kpi-title">Student Skills</span>
            <span class="sg-kpi-icon sg-kpi-icon--indigo"><i class="fa-solid fa-layer-group"></i></span>
          </div>
          <div class="kpi-value" id="sgTotalStudentSkills">—</div>
          <div class="kpi-subtext">unique skills in student pool</div>
        </div>
      </div>
      <!-- 2. Recruiter Demand -->
      <div class="col-6 col-lg-3">
        <div class="pp-card kpi-card sg-kpi-card">
          <div class="kpi-header">
            <span class="kpi-title">Recruiter Demand</span>
            <span class="sg-kpi-icon sg-kpi-icon--purple"><i class="fa-solid fa-bullseye"></i></span>
          </div>
          <div class="kpi-value" id="sgTotalDemandSkills">—</div>
          <div class="kpi-subtext">skills recruiters are hiring for</div>
        </div>
      </div>
      <!-- 3. Coverage Rate -->
      <div class="col-6 col-lg-3">
        <div class="pp-card kpi-card sg-kpi-card">
          <div class="kpi-header">
            <span class="kpi-title">Coverage Rate</span>
            <span class="sg-kpi-icon sg-kpi-icon--emerald"><i class="fa-solid fa-shield-halved"></i></span>
          </div>
          <div class="d-flex align-items-end gap-2">
            <div class="kpi-value" id="sgCoveragePct">—</div>
            <span class="text-muted small mb-1">%</span>
          </div>
          <div class="progress mt-1" style="height: 6px; border-radius: 999px; background: #E5E7EB;">
            <div class="progress-bar" id="sgCoverageBar"
              style="width: 0%; background: #10B981; border-radius: 999px; transition: width 1s ease;"></div>
          </div>
        </div>
      </div>
      <!-- 4. Critical Gaps -->
      <div class="col-6 col-lg-3">
        <div class="pp-card kpi-card sg-kpi-card sg-kpi-card--danger">
          <div class="kpi-header">
            <span class="kpi-title">Critical Gaps</span>
            <span class="sg-kpi-icon sg-kpi-icon--red"><i class="fa-solid fa-triangle-exclamation"></i></span>
          </div>
          <div class="kpi-value" id="sgCriticalGaps">—</div>
          <div class="kpi-subtext">skills with &gt;70% gap</div>
        </div>
      </div>
    </div>

    <!-- Demand vs Supply Bar Chart + Top Skills Lists -->
    <div class="row g-4 mb-4">
      <!-- Dual Horizontal Bar Chart -->
      <div class="col-12 col-xl-8">
        <div class="pp-card h-100">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <h5 class="h6 font-weight-800 text-dark mb-0">Demand vs. Supply</h5>
              <p class="text-muted small mb-0">Top skills comparison — recruiter need vs. student availability</p>
            </div>
            <div class="d-flex gap-3 align-items-center">
              <span class="sg-legend-dot" style="--dot-color: var(--pp-primary);"></span>
              <span class="small font-weight-600 text-muted">Demand</span>
              <span class="sg-legend-dot" style="--dot-color: #10B981;"></span>
              <span class="small font-weight-600 text-muted">Supply</span>
            </div>
          </div>
          <div id="sgBarChart" class="sg-bar-chart">
            <div class="text-center text-muted py-5"><i class="fa-solid fa-spinner fa-spin me-2"></i>Loading chart...
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Info Summary Panel -->
      <div class="col-12 col-xl-4">
        <div class="pp-card h-100 d-flex flex-column gap-3">
          <div>
            <h5 class="h6 font-weight-800 text-dark mb-2">Analysis Overview</h5>
            <p class="text-muted small mb-0">How the gap score is calculated</p>
          </div>
          <div class="sg-info-block">
            <div class="sg-info-formula">
              <span class="font-weight-700" style="color: var(--pp-primary);">Gap %</span> =
              <span class="font-weight-600">(Demand − Supply) / Demand × 100</span>
            </div>
          </div>
          <div class="sg-status-legend">
            <div class="sg-status-row">
              <span class="sg-status-dot sg-status-dot--critical"></span>
              <span class="small"><strong>Critical</strong> — Gap &gt; 70%</span>
            </div>
            <div class="sg-status-row">
              <span class="sg-status-dot sg-status-dot--moderate"></span>
              <span class="small"><strong>Moderate</strong> — Gap 30–70%</span>
            </div>
            <div class="sg-status-row">
              <span class="sg-status-dot sg-status-dot--covered"></span>
              <span class="small"><strong>Covered</strong> — Gap &lt; 30%</span>
            </div>
          </div>
          <div class="mt-auto pt-3 border-top">
            <div class="d-flex justify-content-between small text-muted font-weight-600 mb-1">
              <span>Students with skills data</span>
              <span id="sgStudentsCount" class="font-weight-700 text-dark">—</span>
            </div>
            <div class="d-flex justify-content-between small text-muted font-weight-600">
              <span>Companies analyzed</span>
              <span id="sgCompaniesCount" class="font-weight-700 text-dark">—</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Skill Gap Table -->
    <div class="pp-card mb-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h5 class="h6 font-weight-800 text-dark mb-0">Skill Gap Breakdown</h5>
          <p class="text-muted small mb-0">All skills ranked by gap severity</p>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-pp-outline btn-sm sg-filter-btn active" data-filter="all"
            onclick="filterGapTable('all', this)">All</button>
          <button class="btn btn-pp-outline btn-sm sg-filter-btn" data-filter="critical"
            onclick="filterGapTable('critical', this)">
            <span class="sg-status-dot sg-status-dot--critical" style="width:8px;height:8px;"></span> Critical
          </button>
          <button class="btn btn-pp-outline btn-sm sg-filter-btn" data-filter="moderate"
            onclick="filterGapTable('moderate', this)">
            <span class="sg-status-dot sg-status-dot--moderate" style="width:8px;height:8px;"></span> Moderate
          </button>
          <button class="btn btn-pp-outline btn-sm sg-filter-btn" data-filter="covered"
            onclick="filterGapTable('covered', this)">
            <span class="sg-status-dot sg-status-dot--covered" style="width:8px;height:8px;"></span> Covered
          </button>
        </div>
      </div>
      <div class="table-responsive">
        <table class="pp-table" id="sgGapTable">
          <thead>
            <tr>
              <th style="cursor:pointer;" onclick="sortGapTable('skill')">Skill <i
                  class="fa-solid fa-sort fa-xs text-muted"></i></th>
              <th style="cursor:pointer;width:120px;" onclick="sortGapTable('demand')">Demand <i
                  class="fa-solid fa-sort fa-xs text-muted"></i></th>
              <th style="cursor:pointer;width:120px;" onclick="sortGapTable('supply')">Supply <i
                  class="fa-solid fa-sort fa-xs text-muted"></i></th>
              <th style="cursor:pointer;width:180px;" onclick="sortGapTable('gap_percentage')">Gap <i
                  class="fa-solid fa-sort fa-xs text-muted"></i></th>
              <th style="width:120px;">Status</th>
            </tr>
          </thead>
          <tbody id="sgGapTableBody">
            <tr>
              <td colspan="5" class="text-center text-muted py-4"><i
                  class="fa-solid fa-spinner fa-spin me-2"></i>Loading...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Bottom Row: Department Breakdown + Training Recommendations -->
    <div class="row g-4 mb-4">
      <!-- Department Breakdown -->
      <div class="col-12 col-lg-7">
        <div class="pp-card h-100">
          <h5 class="h6 font-weight-800 text-dark mb-1">Department Skill Distribution</h5>
          <p class="text-muted small mb-3">Top skills per department in the student body</p>
          <div id="sgDeptBreakdown">
            <div class="text-center text-muted py-4"><i class="fa-solid fa-spinner fa-spin me-2"></i>Loading...</div>
          </div>
        </div>
      </div>

      <!-- Training Recommendations -->
      <div class="col-12 col-lg-5">
        <div class="sg-training-card h-100">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <h5 class="h6 font-weight-800 mb-1" style="color: #FFFFFF;">Suggested Workshops</h5>
              <p class="small mb-0" style="color: rgba(255,255,255,0.6);">Based on the highest demand-supply gaps</p>
            </div>
            <span class="badge bg-white text-dark font-weight-700 px-3 py-1 rounded-pill small">TOP 5</span>
          </div>
          <div id="sgTrainingList">
            <div class="text-center py-4" style="color:rgba(255,255,255,0.5);"><i
                class="fa-solid fa-spinner fa-spin me-2"></i>Loading...</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Surplus Skills -->
    <div class="pp-card mb-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h5 class="h6 font-weight-800 text-dark mb-0">Surplus Skills</h5>
          <p class="text-muted small mb-0">Skills prevalent among students but not currently demanded by recruiters</p>
        </div>
        <span class="badge-pill-info"><i class="fa-solid fa-info-circle me-1"></i> Low Priority</span>
      </div>
      <div id="sgSurplusList" class="d-flex flex-wrap gap-2">
        <span class="text-muted small"><i class="fa-solid fa-spinner fa-spin me-1"></i>Loading...</span>
      </div>
    </div>

  </main>

  <script>window.API_BASE = '<?php echo API_BASE; ?>'; window.API_TOKEN = '<?php echo $_SESSION['token'] ?? ""; ?>';</script>
  <script src="assets/js/api.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    let sgData = null;
    let gapSortField = 'gap_percentage';
    let gapSortAsc = false;
    let gapFilterStatus = 'all';

    // ---- Load data ----
    async function loadSkillGap() {
      try {
        const dept = document.getElementById('sgDeptFilter').value;
        const qs = dept ? `?department=${encodeURIComponent(dept)}` : '';
        sgData = await API.get('/reports/skill-gap' + qs);
        renderAll();
      } catch (err) {
        console.error('Failed to load skill gap data:', err);
        Swal.fire({ title: 'Error', text: 'Could not load Skill Gap data: ' + err.message, icon: 'error' });
      }
    }

    function renderAll() {
      if (!sgData) return;
      renderKPIs();
      renderBarChart();
      renderGapTable();
      renderDeptBreakdown();
      renderTraining();
      renderSurplus();
    }

    // ---- KPI Cards ----
    function renderKPIs() {
      const s = sgData.summary;
      document.getElementById('sgTotalStudentSkills').textContent = s.total_student_skills;
      document.getElementById('sgTotalDemandSkills').textContent = s.total_demand_skills;
      document.getElementById('sgCoveragePct').textContent = s.coverage_percentage;
      document.getElementById('sgCoverageBar').style.width = s.coverage_percentage + '%';
      document.getElementById('sgCriticalGaps').textContent = s.critical_gaps;
      document.getElementById('sgStudentsCount').textContent = s.students_with_skills;
      document.getElementById('sgCompaniesCount').textContent = s.companies_analyzed;
    }

    // ---- Dual Bar Chart ----
    function renderBarChart() {
      const demanded = sgData.top_demanded_skills || [];
      const studentMap = {};
      (sgData.top_student_skills || []).forEach(s => studentMap[s.skill] = s.count);

      const skills = demanded.slice(0, 12);
      if (skills.length === 0) {
        document.getElementById('sgBarChart').innerHTML = '<div class="text-center text-muted py-5">No data available — add companies with job roles and students with skills.</div>';
        return;
      }

      const maxVal = Math.max(...skills.map(s => Math.max(s.count, studentMap[s.skill] || 0)), 1);
      let html = '';
      skills.forEach(s => {
        const supply = studentMap[s.skill] || 0;
        const demandPct = Math.max((s.count / maxVal) * 100, 3);
        const supplyPct = Math.max((supply / maxVal) * 100, 3);
        html += `
          <div class="sg-bar-row">
            <div class="sg-bar-label">${s.skill}</div>
            <div class="sg-bar-tracks">
              <div class="sg-bar-track-pair">
                <div class="sg-bar-fill sg-bar-demand" style="width:${demandPct}%;">
                  <span class="sg-bar-count">${s.count}</span>
                </div>
                <div class="sg-bar-fill sg-bar-supply" style="width:${supplyPct}%;">
                  <span class="sg-bar-count">${supply}</span>
                </div>
              </div>
            </div>
          </div>`;
      });
      document.getElementById('sgBarChart').innerHTML = html;

      requestAnimationFrame(() => {
        document.querySelectorAll('.sg-bar-fill').forEach(el => el.classList.add('sg-bar-animate'));
      });
    }

    // ---- Gap Table ----
    function renderGapTable() {
      let gaps = [...(sgData.skill_gaps || [])];

      if (gapFilterStatus !== 'all') {
        gaps = gaps.filter(g => g.status === gapFilterStatus);
      }

      gaps.sort((a, b) => {
        let av = a[gapSortField], bv = b[gapSortField];
        if (typeof av === 'string') { av = av.toLowerCase(); bv = bv.toLowerCase(); }
        if (av < bv) return gapSortAsc ? -1 : 1;
        if (av > bv) return gapSortAsc ? 1 : -1;
        return 0;
      });

      if (gaps.length === 0) {
        document.getElementById('sgGapTableBody').innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No skills match the selected filter.</td></tr>';
        return;
      }

      let html = '';
      gaps.forEach(g => {
        const statusBadge = g.status === 'critical'
          ? '<span class="sg-gap-badge sg-gap-badge--critical"><i class="fa-solid fa-circle-exclamation"></i> Critical</span>'
          : g.status === 'moderate'
            ? '<span class="sg-gap-badge sg-gap-badge--moderate"><i class="fa-solid fa-circle-minus"></i> Moderate</span>'
            : '<span class="sg-gap-badge sg-gap-badge--covered"><i class="fa-solid fa-circle-check"></i> Covered</span>';

        const barColor = g.status === 'critical' ? '#EF4444' : (g.status === 'moderate' ? '#F59E0B' : '#10B981');
        html += `
          <tr data-status="${g.status}">
            <td><span class="font-weight-600">${g.skill}</span></td>
            <td><span class="font-weight-700" style="color:var(--pp-primary);">${g.demand}</span> <span class="text-muted small">cos.</span></td>
            <td><span class="font-weight-700" style="color:#10B981;">${g.supply}</span> <span class="text-muted small">stu.</span></td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <div class="sg-gap-bar-track">
                  <div class="sg-gap-bar-fill" style="width:${g.gap_percentage}%; background:${barColor};"></div>
                </div>
                <span class="font-weight-700 small" style="min-width:42px;">${g.gap_percentage}%</span>
              </div>
            </td>
            <td>${statusBadge}</td>
          </tr>`;
      });
      document.getElementById('sgGapTableBody').innerHTML = html;
    }

    function sortGapTable(field) {
      if (gapSortField === field) { gapSortAsc = !gapSortAsc; } else { gapSortField = field; gapSortAsc = field === 'skill'; }
      renderGapTable();
    }

    function filterGapTable(status, btn) {
      gapFilterStatus = status;
      document.querySelectorAll('.sg-filter-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      renderGapTable();
    }

    // ---- Department Breakdown ----
    function renderDeptBreakdown() {
      const depts = sgData.department_breakdown || [];
      if (depts.length === 0) {
        document.getElementById('sgDeptBreakdown').innerHTML = '<div class="text-muted small">No department data available.</div>';
        return;
      }

      const colors = ['#4F46E5', '#7C3AED', '#EC4899', '#F59E0B', '#10B981', '#06B6D4', '#8B5CF6', '#F97316'];
      let html = '';
      depts.forEach((d, di) => {
        const color = colors[di % colors.length];
        html += `
          <div class="sg-dept-block">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="font-weight-700 small text-dark">${d.department}</span>
              <span class="text-muted small font-weight-600">${d.skills.length} skills</span>
            </div>
            <div class="d-flex flex-wrap gap-1">
              ${d.skills.map(s => `<span class="sg-skill-pill" style="--pill-color:${color};">${s.skill} <span class="sg-pill-count">${s.count}</span></span>`).join('')}
            </div>
          </div>`;
      });
      document.getElementById('sgDeptBreakdown').innerHTML = html;
    }

    // ---- Training Recommendations ----
    function renderTraining() {
      const recs = sgData.training_recommendations || [];
      if (recs.length === 0) {
        document.getElementById('sgTrainingList').innerHTML = '<div style="color:rgba(255,255,255,0.5);" class="small">No training recommendations available.</div>';
        return;
      }

      let html = '';
      recs.forEach((r, i) => {
        html += `
          <div class="sg-training-item">
            <div class="sg-training-number">${i + 1}</div>
            <div class="flex-grow-1">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="font-weight-700 small" style="color:#FFFFFF;">${r.skill}</span>
                <span class="sg-training-gap">${r.gap_percentage}% gap</span>
              </div>
              <p class="small mb-0" style="color:rgba(255,255,255,0.6); line-height:1.4;">${r.recommendation}</p>
            </div>
          </div>`;
      });
      document.getElementById('sgTrainingList').innerHTML = html;
    }

    // ---- Surplus Skills ----
    function renderSurplus() {
      const surplus = sgData.surplus_skills || [];
      if (surplus.length === 0) {
        document.getElementById('sgSurplusList').innerHTML = '<span class="text-muted small">No surplus skills detected — all student skills are in demand!</span>';
        return;
      }
      let html = '';
      surplus.forEach(s => {
        html += `<span class="sg-surplus-pill">${s.skill} <span class="sg-pill-count">${s.count}</span></span>`;
      });
      document.getElementById('sgSurplusList').innerHTML = html;
    }

    // ---- Export ----
    function exportSkillGap(format) {
      showToast(`Generating ${format.toUpperCase()} export...`, 'info');
      window.open(API.base + `/reports/students?format=${format}&token=${window.API_TOKEN}`, '_blank');
    }

    // ---- Load departments for filter ----
    async function loadDeptFilter() {
      try {
        const data = await API.get('/dashboard/filters');
        const sel = document.getElementById('sgDeptFilter');
        (data.departments || []).forEach(d => {
          const opt = document.createElement('option');
          opt.value = d;
          opt.textContent = d;
          sel.appendChild(opt);
        });
      } catch (e) { console.warn('Could not load departments', e); }
    }

    // ---- Init ----
    document.addEventListener('DOMContentLoaded', async () => {
      await loadDeptFilter();
      await loadSkillGap();
      document.getElementById('sgDeptFilter').addEventListener('change', loadSkillGap);
    });
  </script>
</body>

</html>