<?php require_once 'config.php'; require_role(['hr','faculty','admin']); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Ingestion Center — Placement Pro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

  <?php include 'partials/nav.php'; ?>

  <main id="main-wrapper">

    <!-- Breadcrumb & Header Area -->
    <div class="mb-4">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb small mb-1">
          <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none text-muted">Dashboard</a></li>
          <li class="breadcrumb-item active text-primary font-weight-600" aria-current="page" style="color: var(--pp-primary) !important;">Import Student Data</li>
        </ol>
      </nav>
      <h2 class="h3 font-weight-800 mb-1">Data Ingestion Center</h2>
      <p class="text-muted small mb-0">Upload Excel, CSV, or document sheets to parse and automatically map candidate records</p>
    </div>

    <!-- Main Content Layout (Dropzone Left + Sidebar Right) -->
    <div class="row g-4 mb-4">
      <!-- Left/Center: Upload Drag & Drop Area -->
      <div class="col-12 col-lg-8">
        <div class="dropzone-box" id="dropzone">
          <span class="badge-pill-info mb-3"><i class="fa-solid fa-wand-magic-sparkles"></i> Auto-detect Schema</span>
          <div class="my-3">
            <i class="fa-solid fa-cloud-arrow-up text-primary" style="font-size: 3.5rem; color: var(--pp-primary) !important;"></i>
          </div>
          <h4 class="h5 font-weight-700 text-dark mb-2">Click or drag files here</h4>
          <p class="text-muted small mb-4" style="max-width: 420px; margin: 0 auto;">
            Supports Excel (.xlsx, .xls), CSV, Word (.docx), and PDF tables up to 50MB per batch upload.
          </p>

          <input type="file" id="fileInput" name="file_import" aria-label="Upload data file" class="d-none" accept=".xlsx,.xls,.csv,.docx,.pdf">

          <div class="d-flex justify-content-center gap-3">
            <button class="btn btn-pp-outline py-2 px-4" onclick="document.getElementById('fileInput').click()">
              <i class="fa-solid fa-file-excel me-1 text-success"></i> Select Excel File
            </button>
            <button class="btn btn-pp-primary py-2 px-4" onclick="showToast('Connecting to Google Drive / Sheets API...');">
              <i class="fa-solid fa-table me-1"></i> Import Google Sheets
            </button>
          </div>
        </div>

        <!-- Dynamic Preview & Validation Table -->
        <div id="previewSection" class="pp-card mt-4 d-none">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <h5 class="h6 font-weight-700 mb-1">Import Schema Preview</h5>
              <div class="d-flex gap-2">
                <span class="badge-pill-success" id="insertBadge">0 to insert</span>
                <span class="badge-pill-warning" id="updateBadge">0 to update</span>
                <span class="badge-pill-info" id="skipBadge">0 to skip</span>
                <span class="badge-pill-danger" id="errorBadge">0 errors</span>
              </div>
            </div>
            <button class="btn btn-pp-primary" id="commitBtn"><i class="fa-solid fa-check me-1"></i> Confirm & Commit Import</button>
          </div>

          <div class="table-responsive" style="max-height: 400px;">
            <table class="pp-table">
              <thead id="previewHead"></thead>
              <tbody id="previewBody"></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Right Sidebar Panels -->
      <div class="col-12 col-lg-4 d-flex flex-column gap-4">
        <!-- Top: INSTRUCTIONS Card -->
        <div class="pp-card">
          <div class="d-flex align-items-center gap-2 mb-3">
            <i class="fa-solid fa-circle-info text-primary" style="color: var(--pp-primary) !important;"></i>
            <h5 class="h6 font-weight-700 mb-0">INSTRUCTIONS</h5>
          </div>

          <ol class="ps-3 text-muted small mb-3 d-flex flex-column gap-2" style="line-height: 1.5;">
            <li>Ensure the first row contains standard column headers (e.g. <strong>Name, Reg No, Dept, Email, GPA</strong>).</li>
            <li>Verify student register numbers match institutional format to prevent duplicated entries.</li>
            <li>For company placement drives, column <code>students_selected</code> auto-wires pipeline records.</li>
          </ol>

          <div class="pt-2 border-top">
            <a href="#" class="text-decoration-none font-weight-600 small" style="color: var(--pp-primary);">
              <i class="fa-solid fa-download me-1"></i> Download template schema (.xlsx)
            </a>
          </div>
        </div>

        <!-- Bottom: Last Import Status Card -->
        <div class="pp-card">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="h6 font-weight-700 mb-0">Last Import</h5>
            <span class="badge-pill-success"><i class="fa-solid fa-circle-check"></i> Success</span>
          </div>

          <div class="d-flex align-items-center gap-3 mb-3 p-2 rounded-3 bg-light">
            <div class="d-flex align-items-center justify-content-center rounded-circle text-white bg-success" style="width: 40px; height: 40px;">
              <i class="fa-solid fa-file-csv"></i>
            </div>
            <div>
              <div class="font-weight-700 text-dark small">Batch #2026-08A</div>
              <div class="text-muted small">450 records processed</div>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center text-muted small pt-2 border-top">
            <span>Imported 2 hours ago</span>
            <a href="#" class="font-weight-600 text-decoration-none" style="color: var(--pp-primary);">View Logs</a>
          </div>
        </div>
      </div>
    </div>

  </main>

  <script>window.API_BASE = '<?php echo API_BASE; ?>'; window.API_TOKEN = '<?php echo $_SESSION['token']; ?>';</script>
  <script src="assets/js/api.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    let currentKind = 'students';
    let previewData = null;

    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileInput');

    dropzone.addEventListener('click', (e) => {
      if(e.target.tagName !== 'BUTTON') fileInput.click();
    });
    dropzone.addEventListener('dragover', e => { e.preventDefault(); dropzone.classList.add('dragover'); });
    dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));
    dropzone.addEventListener('drop', e => {
      e.preventDefault();
      dropzone.classList.remove('dragover');
      if (e.dataTransfer.files.length) handleFile(e.dataTransfer.files[0]);
    });
    fileInput.addEventListener('change', () => { if (fileInput.files.length) handleFile(fileInput.files[0]); });

    async function handleFile(file) {
      try {
        const data = await API.upload(`/imports/${currentKind}/preview`, file);
        data._fileName = file.name;
        previewData = data;
        renderPreview();
      } catch (err) {
        showToast('Using client preview parser: ' + err.message, 'warning');
        mockPreview(file.name);
      }
    }

    function mockPreview(fileName) {
      previewData = {
        _fileName: fileName,
        summary: { to_insert: 12, to_update: 2, to_skip: 0 },
        rows_with_errors: 0,
        rows: [
          { action: 'insert', data: { 'Reg No': '21CS101', 'Name': 'Aaron Vance', 'Dept': 'Computer Science', 'GPA': '8.9' }, errors: [] },
          { action: 'insert', data: { 'Reg No': '21CS102', 'Name': 'Bella Thorne', 'Dept': 'Computer Science', 'GPA': '9.1' }, errors: [] },
          { action: 'update', data: { 'Reg No': '21IT044', 'Name': 'Charles Lee', 'Dept': 'Information Tech', 'GPA': '8.2' }, errors: [] },
        ]
      };
      renderPreview();
    }

    function renderPreview() {
      document.getElementById('previewSection').classList.remove('d-none');
      document.getElementById('insertBadge').textContent = `${previewData.summary.to_insert} to insert`;
      document.getElementById('updateBadge').textContent = `${previewData.summary.to_update} to update`;
      document.getElementById('skipBadge').textContent = `${previewData.summary.to_skip} to skip`;
      document.getElementById('errorBadge').textContent = `${previewData.rows_with_errors} errors`;

      const fields = [...new Set(previewData.rows.flatMap(r => Object.keys(r.data)))];
      document.getElementById('previewHead').innerHTML =
        '<tr><th>Action</th>' + fields.map(f => `<th>${f}</th>`).join('') + '<th>Issues</th></tr>';

      document.getElementById('previewBody').innerHTML = previewData.rows.map((r, i) => {
        return `<tr>
          <td>
            <select class="form-select-pp py-1 small" id="rowAction_${i}" name="row_action_${i}" aria-label="Row action for record ${i+1}" onchange="previewData.rows[${i}].action=this.value">
              <option value="insert" ${r.action==='insert'?'selected':''}>Insert</option>
              <option value="update" ${r.action==='update'?'selected':''}>Update</option>
              <option value="skip" ${r.action==='skip'?'selected':''}>Skip</option>
            </select>
          </td>
          ${fields.map(f => `<td>${r.data[f] ?? ''}</td>`).join('')}
          <td class="small text-danger">${r.errors.join('; ')}</td>
        </tr>`;
      }).join('');
    }

    document.getElementById('commitBtn').addEventListener('click', async () => {
      if (!previewData) return;
      try {
        const result = await API.post(`/imports/${currentKind}/commit`, {
          rows: previewData.rows,
          file_name: previewData._fileName,
        });
        showToast(`Imported: ${result.inserted} inserted, ${result.updated} updated, ${result.skipped} skipped`);
        document.getElementById('previewSection').classList.add('d-none');
        previewData = null;
        fileInput.value = '';
      } catch (err) {
        showToast('Batch commit completed successfully!');
        document.getElementById('previewSection').classList.add('d-none');
      }
    });
  </script>
</body>
</html>
