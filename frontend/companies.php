<?php require_once 'config.php'; require_login(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Companies Directory — Placement Pro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

  <?php include 'partials/nav.php'; ?>

  <main id="main-wrapper">

    <!-- Header Area -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="h3 font-weight-800 mb-1">Companies Directory</h2>
        <p class="text-muted small mb-0">Partner corporate organizations and active campus recruitment drives</p>
      </div>

      <div class="d-flex gap-2">
        <button class="btn btn-pp-outline">
          <i class="fa-solid fa-filter"></i> Filter
        </button>
        <a href="#" onclick="event.preventDefault(); showToast('Exporting Company Directory (Excel)...', 'info');" class="btn btn-pp-primary text-white text-decoration-none">
          <i class="fa-solid fa-file-excel"></i> Export List
        </a>

      </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="pp-card mb-4 p-3">
      <div class="row g-3 align-items-center">
        <!-- Search Input -->
        <div class="col-12 col-md-5">
          <div class="position-relative">
            <i class="fa-solid fa-magnifying-glass position-absolute text-muted" style="left: 0.85rem; top: 50%; transform: translateY(-50%); font-size: 0.85rem;"></i>
            <input type="text" class="form-control-pp w-100 ps-5" id="companySearch" placeholder="Search company by name or tech stack...">
          </div>
        </div>

        <!-- Industry Dropdown -->
        <div class="col-6 col-md-3">
          <select class="form-select-pp w-100" id="industryFilter">
            <option value="">All Industries</option>
            <option value="Fintech">Financial Tech & Banking</option>
            <option value="Cloud">E-Commerce & Cloud Services</option>
            <option value="IT Services">IT Services & Consulting</option>
            <option value="Hardware">Semiconductors & Embedded</option>
          </select>
        </div>

        <!-- Sort Dropdown -->
        <div class="col-6 col-md-4">
          <select class="form-select-pp w-100" id="sortFilter">
            <option value="package_desc" selected>Package: High to Low</option>
            <option value="package_asc">Package: Low to High</option>
            <option value="hiring_date">Next Hiring Date</option>
            <option value="selected_count">Selected Students</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Company Grid -->
    <div class="row g-4" id="companyGrid">
      <div class="col-12 text-center py-5 text-muted small">Loading company directory...</div>
    </div>

  </main>

  <script>window.API_BASE = '<?php echo API_BASE; ?>'; window.API_TOKEN = '<?php echo $_SESSION['token'] ?? ""; ?>';</script>
  <script src="assets/js/api.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const MOCK_COMPANIES = [
      { id: 1, name: "Goldman Sachs", description: "Global investment banking, securities, and investment management firm hiring for Software Engineering roles.", selected_count: 18, avg_package: 42.0, visit_date: "2026-08-14", status: "Live", statusClass: "success", initials: "GS", color: "var(--pp-primary)" },
      { id: 2, name: "Amazon", description: "Multinational technology company focusing on e-commerce, cloud computing, online advertising, and digital streaming.", selected_count: 65, avg_package: 31.5, visit_date: "2026-08-18", status: "Live", statusClass: "success", initials: "AM", color: "#F59E0B" },
      { id: 3, name: "TCS Digital", description: "Leading IT services, consulting and business solutions organization offering digital innovation roles.", selected_count: 120, avg_package: 7.5, visit_date: "2026-09-02", status: "Pending", statusClass: "warning", initials: "TC", color: "#10B981" },
      { id: 4, name: "Microsoft", description: "Global leader in software, cloud computing (Azure), consumer electronics, and personal computer services.", selected_count: 24, avg_package: 45.0, visit_date: "Drive Closed", status: "Closed", statusClass: "danger", initials: "MS", color: "#EF4444" },
      { id: 5, name: "Qualcomm", description: "Semiconductor and wireless telecommunications company creating intellectual property, semiconductors, and software.", selected_count: 15, avg_package: 22.0, visit_date: "2026-08-22", status: "Live", statusClass: "success", initials: "QC", color: "#4F46E5" }
    ];

    async function loadCompanies() {
      try {
        const data = await API.get('/companies');
        if (data) {
          renderCompanies(data);
          return;
        }
      } catch (err) {
        console.log('Using mock companies directory:', err.message);
      }
      renderCompanies(MOCK_COMPANIES);
    }

    function renderCompanies(list) {
      const container = document.getElementById('companyGrid');
      
      const cardsHtml = list.map(c => {
        const statusVal = c.status || 'Live';
        const statusClass = c.statusClass || 'success';
        const initials = c.initials || c.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
        const color = c.color || '#4F46E5';
        
        let visitStr = 'TBD';
        if (c.visit_date) {
          if (c.visit_date === 'Drive Closed') {
            visitStr = 'Drive Closed';
          } else {
            visitStr = new Date(c.visit_date).toLocaleDateString(undefined, {month: 'short', day: 'numeric', year: 'numeric'});
          }
        }
        
        const avgPkg = c.avg_package ? `${c.avg_package} LPA` : (c.package_amount ? `${c.package_amount} LPA` : '0 LPA');
        
        return `
          <div class="col-12 col-md-6 col-lg-4">
            <div class="pp-card h-100 d-flex flex-column justify-content-between">
              <div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <div class="rounded-3 border p-1 text-white d-flex align-items-center justify-content-center font-weight-700" style="width: 44px; height: 44px; background-color: ${color} !important;">${initials}</div>
                  <span class="badge-pill-${statusClass}"><i class="fa-solid fa-circle" style="font-size: 0.4rem;"></i> ${statusVal}</span>
                </div>
                <h5 class="h6 font-weight-800 text-dark mb-2">${c.name}</h5>
                <p class="text-muted small mb-3">${c.description || c.industry || 'No description available.'}</p>
                <div class="row g-2 p-2 rounded-3 bg-light text-center mb-3">
                  <div class="col-6 border-end">
                    <div class="text-muted small font-weight-600">Selected Students</div>
                    <div class="font-weight-800 text-dark" style="font-size: 1.15rem;">${c.selected_count || 0}</div>
                  </div>
                  <div class="col-6">
                    <div class="text-muted small font-weight-600">Avg. Package</div>
                    <div class="font-weight-800 text-primary" style="font-size: 1.15rem; color: var(--pp-primary) !important;">${avgPkg}</div>
                  </div>
                </div>
              </div>
              <div class="pt-3 border-top">
                <div class="d-flex justify-content-between align-items-center small text-muted mb-2">
                  <span>Next Hiring:</span>
                  <span class="font-weight-700 text-dark">${visitStr}</span>
                </div>
                <a href="company_dashboard.php?id=${c.id}" class="btn btn-pp-outline w-100 justify-content-center text-decoration-none">View Details</a>
              </div>
            </div>
          </div>
        `;
      }).join('');

      const addCardHtml = `
        <div class="col-12 col-md-6 col-lg-4">
          <div class="h-100 rounded-4 d-flex flex-column align-items-center justify-content-center p-4 text-center cursor-pointer" 
               style="border: 2px dashed #CBD5E1; background: #FFFFFF; min-height: 320px;" 
               data-bs-toggle="modal" data-bs-target="#postJobModal">
            <div class="d-flex align-items-center justify-content-center rounded-circle bg-light text-primary mb-3" style="width: 56px; height: 56px; color: var(--pp-primary) !important;">
              <i class="fa-solid fa-plus" style="font-size: 1.5rem;"></i>
            </div>
            <h5 class="h6 font-weight-700 text-dark mb-1">Add Company</h5>
            <p class="text-muted small mb-0">Register a new recruitment partner or schedule drive</p>
          </div>
        </div>
      `;

      container.innerHTML = cardsHtml + addCardHtml;
    }

    loadCompanies();
  </script>
</body>
</html>
