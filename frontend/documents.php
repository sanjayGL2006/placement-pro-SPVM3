<?php 
require_once 'config.php'; 
require_login(); 

$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$student_id) {
    die("Student ID required");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Documents — Placement Pro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

  <?php include 'partials/nav.php'; ?>

  <main id="main-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="h3 font-weight-800 mb-1" id="pageTitle">Student Documents</h2>
        <p class="text-muted small mb-0">Manage resumes and certificates for this candidate</p>
      </div>
      <div>
        <a href="students.php" class="btn btn-pp-outline">
          <i class="fa-solid fa-arrow-left"></i> Back to Directory
        </a>
      </div>
    </div>

    <div class="row">
      <div class="col-md-4">
        <!-- Upload Form -->
        <div class="pp-card p-4 mb-4">
          <h5 class="font-weight-bold mb-3"><i class="fa-solid fa-upload text-primary me-2"></i> Upload Document</h5>
          <form id="uploadDocForm">
            <input type="hidden" id="docStudentId" value="<?php echo $student_id; ?>">
            <div class="mb-3">
              <label for="docType" class="form-label small font-weight-600 text-muted">DOCUMENT TYPE</label>
              <select class="form-select-pp w-100" id="docType" name="doc_type" required>
                <option value="RESUME">Resume</option>
                <option value="COVER_LETTER">Cover Letter</option>
                <option value="CERTIFICATE">Certificate</option>
                <option value="OTHER">Other</option>
              </select>
            </div>
            
            <div class="mb-3">
              <label for="docPlacementId" class="form-label small font-weight-600 text-muted">LINK TO SPECIFIC DRIVE (Optional)</label>
              <select class="form-select-pp w-100" id="docPlacementId" name="doc_placement_id">
                <option value="">-- General Document --</option>
                <!-- Filled via JS -->
              </select>
            </div>
            
            <div class="mb-3">
              <label for="docFile" class="form-label small font-weight-600 text-muted">FILE</label>
              <input type="file" class="form-control" id="docFile" name="doc_file" required accept=".pdf,.doc,.docx,.jpg,.png">
              <div class="form-text small text-muted">Max 20MB. PDF, Word, JPG, PNG allowed.</div>
            </div>
            
            <button type="submit" class="btn btn-pp-primary w-100 mt-2" id="btnUpload">
              <i class="fa-solid fa-cloud-arrow-up me-1"></i> Upload
            </button>
          </form>
        </div>
      </div>
      
      <div class="col-md-8">
        <!-- Documents List -->
        <div class="pp-card p-0 overflow-hidden">
          <div class="table-responsive">
            <table class="pp-table">
              <thead>
                <tr>
                  <th style="padding-left: 1.5rem;">DOCUMENT NAME</th>
                  <th>TYPE</th>
                  <th>LINKED DRIVE</th>
                  <th>DATE</th>
                  <th>SIZE</th>
                  <th class="text-end" style="padding-right: 1.5rem;">ACTIONS</th>
                </tr>
              </thead>
              <tbody id="docsTableBody">
                <tr><td colspan="6" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div> Loading documents...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    window.API_BASE = '<?php echo API_BASE; ?>';
    window.API_TOKEN = '<?php echo $_SESSION['token'] ?? ""; ?>';
  </script>
  <script src="assets/js/api.js"></script>
  <script>
    const studentId = <?php echo $student_id; ?>;
    
    async function loadStudentInfo() {
        try {
            const student = await API.get(`/students/${studentId}`);
            document.getElementById('pageTitle').innerText = `Documents: ${student.name} (${student.register_number})`;
            
            // Populate drives dropdown
            const driveSelect = document.getElementById('docPlacementId');
            if (student.placements && student.placements.length > 0) {
                student.placements.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = p.company_name;
                    driveSelect.appendChild(opt);
                });
            }
        } catch (err) {
            showToast("Failed to load student info", "danger");
        }
    }
    
    async function loadDocuments() {
        try {
            const data = await API.get(`/documents/student/${studentId}`);
            const tbody = document.getElementById('docsTableBody');
            
            if (!data.documents || data.documents.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted small"><i class="fa-regular fa-folder-open me-2" style="font-size: 1.5rem;"></i> No documents uploaded yet.</td></tr>`;
                return;
            }
            
            tbody.innerHTML = data.documents.map(d => {
                const sizeKb = (d.file_size_bytes / 1024).toFixed(1);
                const date = new Date(d.uploaded_at).toLocaleDateString();
                const icon = d.original_name.endsWith('.pdf') ? 'fa-file-pdf text-danger' : 
                             (d.original_name.match(/\.(jpg|jpeg|png)$/i) ? 'fa-file-image text-success' : 'fa-file-word text-primary');
                
                return `
                  <tr>
                    <td style="padding-left: 1.5rem;">
                        <div class="d-flex align-items-center">
                            <i class="fa-solid ${icon} me-2 fs-5"></i>
                            <span class="font-weight-600">${d.original_name}</span>
                        </div>
                    </td>
                    <td><span class="badge bg-secondary">${d.doc_type}</span></td>
                    <td class="text-muted small">${d.company_name || '--'}</td>
                    <td class="text-muted small">${date}</td>
                    <td class="text-muted small">${sizeKb} KB</td>
                    <td class="text-end" style="padding-right: 1.5rem;">
                        <a href="${API.base}/documents/${d.id}/download" class="btn btn-sm btn-pp-outline py-1 px-2" target="_blank" download>
                            <i class="fa-solid fa-download"></i>
                        </a>
                        <button class="btn btn-sm btn-outline-danger py-1 px-2 ms-1" onclick="deleteDoc(${d.id})">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                  </tr>
                `;
            }).join('');
            
        } catch (err) {
            showToast("Failed to load documents: " + err.message, "danger");
        }
    }
    
    async function deleteDoc(docId) {
        if (!confirm("Are you sure you want to delete this document?")) return;
        try {
            await API.del(`/documents/${docId}`);
            showToast("Document deleted successfully");
            loadDocuments();
        } catch (err) {
            showToast("Delete failed: " + err.message, "danger");
        }
    }
    
    document.getElementById('uploadDocForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('btnUpload');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Uploading...';
        
        try {
            const fileInput = document.getElementById('docFile');
            if (fileInput.files.length === 0) throw new Error("Please select a file");
            
            const form = new FormData();
            form.append('file', fileInput.files[0]);
            form.append('student_id', studentId);
            form.append('doc_type', document.getElementById('docType').value);
            
            const pId = document.getElementById('docPlacementId').value;
            if (pId) form.append('placement_id', pId);
            
            // Using fetch directly since API wrapper doesn't handle form data with specific fields perfectly
            const headers = {};
            if (API.token) headers['Authorization'] = `Bearer ${API.token}`;
            
            const res = await fetch(API.base + '/documents/upload', {
                method: 'POST',
                headers,
                body: form
            });
            
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || "Upload failed");
            
            showToast("Document uploaded successfully");
            document.getElementById('uploadDocForm').reset();
            loadDocuments();
        } catch (err) {
            Swal.fire('Error', err.message, 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up me-1"></i> Upload';
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        loadStudentInfo();
        loadDocuments();
    });
  </script>
</body>
</html>
