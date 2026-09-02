<?php require_once 'config.php'; require_login(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Global Settings — Placement Pro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body style="padding-bottom: 80px;">

  <?php include 'partials/nav.php'; ?>

  <main id="main-wrapper">

    <!-- Header Area -->
    <div class="mb-4">
      <h2 class="h3 font-weight-800 mb-1">Global Settings</h2>
      <p class="text-muted small mb-0">Manage account details, application appearance, security, and integrations</p>
    </div>

    <!-- Two-Column Layout -->
    <div class="row g-4">
      <!-- Left Column: Inner Navigation Menu -->
      <div class="col-12 col-md-3">
        <div class="pp-card p-2">
          <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
            <button class="nav-link text-start py-2.5 px-3 font-weight-600 active rounded-3 mb-1" id="tab-profile" data-bs-toggle="pill" data-bs-target="#panel-profile" type="button" role="tab">
              <i class="fa-solid fa-user me-2"></i> User Profile
            </button>
            <button class="nav-link text-start py-2.5 px-3 font-weight-600 rounded-3 mb-1 text-muted" id="tab-appearance" data-bs-toggle="pill" data-bs-target="#panel-appearance" type="button" role="tab">
              <i class="fa-solid fa-palette me-2"></i> Appearance
            </button>
            <button class="nav-link text-start py-2.5 px-3 font-weight-600 rounded-3 mb-1 text-muted" id="tab-notifications" data-bs-toggle="pill" data-bs-target="#panel-notifications" type="button" role="tab">
              <i class="fa-solid fa-bell me-2"></i> Notifications
            </button>
            <button class="nav-link text-start py-2.5 px-3 font-weight-600 rounded-3 mb-1 text-muted" id="tab-security" data-bs-toggle="pill" data-bs-target="#panel-security" type="button" role="tab">
              <i class="fa-solid fa-shield-halved me-2"></i> Security & Access
            </button>
            <button class="nav-link text-start py-2.5 px-3 font-weight-600 rounded-3 mb-1 text-muted" id="tab-integrations" data-bs-toggle="pill" data-bs-target="#panel-integrations" type="button" role="tab">
              <i class="fa-solid fa-plug me-2"></i> Integrations
            </button>
            <button class="nav-link text-start py-2.5 px-3 font-weight-600 rounded-3 mb-1 text-muted" id="tab-trash" data-bs-toggle="pill" data-bs-target="#panel-trash" type="button" role="tab" onclick="loadTrash()">
              <i class="fa-solid fa-trash-can me-2"></i> System Reset & Trash
            </button>
          </div>
        </div>
      </div>

      <!-- Right Column: Settings Panels -->
      <div class="col-12 col-md-9">
        <div class="tab-content" id="v-pills-tabContent">

          <!-- 1. User Profile Panel -->
          <div class="tab-pane fade show active" id="panel-profile" role="tabpanel">
            <div class="pp-card">
              <h5 class="h6 font-weight-800 text-dark mb-4 pb-2 border-bottom">User Profile</h5>

              <!-- Profile Picture with Edit Pencil Badge -->
              <div class="d-flex align-items-center gap-4 mb-4">
                <div class="position-relative">
                  <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=200" alt="Profile" class="rounded-circle border" width="96" height="96" style="object-fit: cover;">
                  <button class="btn btn-sm btn-pp-primary rounded-circle position-absolute bottom-0 end-0 p-0 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Edit Avatar">
                    <i class="fa-solid fa-pencil" style="font-size: 0.75rem;"></i>
                  </button>
                </div>
                <div>
                  <h6 class="font-weight-700 mb-1"><?php echo htmlspecialchars($_SESSION['user']['name'] ?? 'SPVM3 Tech Solution by Sanjay G L'); ?></h6>
                  <p class="text-muted small mb-2">Head Placement Officer • Administrator</p>
                  <button class="btn btn-sm btn-pp-outline">Change Photo</button>
                </div>
              </div>

              <!-- Form Inputs -->
              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <label for="displayName" class="form-label font-weight-600 text-muted small">DISPLAY NAME</label>
                  <input type="text" id="displayName" name="display_name" class="form-control form-control-pp" value="<?php echo htmlspecialchars($_SESSION['user']['name'] ?? 'SPVM3 Tech Solution by Sanjay G L'); ?>">

                </div>

                <div class="col-12 col-md-6">
                  <label for="displayEmail" class="form-label font-weight-600 text-muted small">EMAIL ADDRESS</label>
                  <input type="email" id="displayEmail" name="display_email" class="form-control form-control-pp" value="<?php echo htmlspecialchars($_SESSION['user']['email'] ?? 'admin@university.edu'); ?>">
                </div>

                <div class="col-12">
                  <label for="userBio" class="form-label font-weight-600 text-muted small">BIO</label>
                  <textarea id="userBio" name="user_bio" class="form-control form-control-pp" rows="4" placeholder="Enter short bio or administrative scope...">Managing placement statistics, corporate partnerships, and campus recruitment drives for session 2023-2024.</textarea>
                </div>
              </div>
            </div>
          </div>

          <!-- 2. Interface Appearance Panel -->
          <div class="tab-pane fade" id="panel-appearance" role="tabpanel">
            <div class="pp-card">
              <h5 class="h6 font-weight-800 text-dark mb-2">Interface Appearance</h5>
              <p class="text-muted small mb-4">Choose your preferred visual theme for the Placement Pro dashboard.</p>

              <!-- Graphical Selectable Buttons -->
              <div class="row g-3">
                <!-- Light Mode (Active) -->
                <div class="col-12 col-md-4">
                  <div class="theme-option-card active" onclick="selectTheme(this)">
                    <div class="theme-preview-box bg-white d-flex align-items-center justify-content-center">
                      <i class="fa-solid fa-sun text-warning" style="font-size: 1.5rem;"></i>
                    </div>
                    <div class="font-weight-700 text-dark mb-0">Light Mode</div>
                    <div class="text-muted small">Default breathable background</div>
                  </div>
                </div>

                <!-- Dark Mode -->
                <div class="col-12 col-md-4">
                  <div class="theme-option-card" onclick="selectTheme(this)">
                    <div class="theme-preview-box bg-dark d-flex align-items-center justify-content-center">
                      <i class="fa-solid fa-moon text-light" style="font-size: 1.5rem;"></i>
                    </div>
                    <div class="font-weight-700 text-dark mb-0">Dark Mode</div>
                    <div class="text-muted small">High contrast sleek interface</div>
                  </div>
                </div>

                <!-- System -->
                <div class="col-12 col-md-4">
                  <div class="theme-option-card" onclick="selectTheme(this)">
                    <div class="theme-preview-box bg-light d-flex align-items-center justify-content-center">
                      <i class="fa-solid fa-desktop text-primary" style="font-size: 1.5rem;"></i>
                    </div>
                    <div class="font-weight-700 text-dark mb-0">System Preference</div>
                    <div class="text-muted small">Matches OS system setting</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- 3. Notification & Auto-Update Hub Panel -->
          <div class="tab-pane fade" id="panel-notifications" role="tabpanel">
            <div class="pp-card">
              <h5 class="h6 font-weight-800 text-dark mb-4 pb-2 border-bottom">Notification Hub & Auto-Update Settings</h5>

              <div class="d-flex flex-column gap-3">
                <!-- Auto-Update Switch & Interval -->
                <div class="p-3 bg-light rounded-3 border mb-2">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                      <div class="font-weight-700 text-dark">Live Auto-Update (Dashboard & Calendar)</div>
                      <div class="text-muted small">Automatically refresh live placement stats, repeat shortlist alerts, and calendar drives in real time.</div>
                    </div>
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" role="switch" id="switchAutoUpdate" name="switch_auto_update" checked style="width: 44px; height: 24px;" onchange="saveAutoUpdateSettings()">
                    </div>
                  </div>
                  <div class="row align-items-center mt-3 pt-2 border-top">
                    <div class="col-12 col-md-6">
                      <label for="selectUpdateInterval" class="form-label font-weight-600 text-muted small mb-1">AUTO-UPDATE INTERVAL</label>
                      <select class="form-select form-select-sm" id="selectUpdateInterval" onchange="saveAutoUpdateSettings()">
                        <option value="15000">Every 15 Seconds (Fast)</option>
                        <option value="30000" selected>Every 30 Seconds (Default)</option>
                        <option value="60000">Every 60 Seconds</option>
                        <option value="300000">Every 5 Minutes</option>
                      </select>
                    </div>
                  </div>
                </div>

                <!-- Connect Upcoming Drives to Calendar -->
                <div class="p-3 bg-light rounded-3 border mb-2">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                      <div class="font-weight-700 text-dark">Connect Upcoming Placement Drives</div>
                      <div class="text-muted small">Automatically connect company visit dates to Placement Calendar and broadcast upcoming drive alerts.</div>
                    </div>
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" role="switch" id="switchConnectUpcoming" name="switch_connect_upcoming" checked style="width: 44px; height: 24px;" onchange="saveAutoUpdateSettings()">
                    </div>
                  </div>
                  <div class="row align-items-center mt-3 pt-2 border-top">
                    <div class="col-12 col-md-6">
                      <label for="selectUpcomingLeadDays" class="form-label font-weight-600 text-muted small mb-1">UPCOMING DRIVE ALERT LEAD TIME</label>
                      <select class="form-select form-select-sm" id="selectUpcomingLeadDays" onchange="saveAutoUpdateSettings()">
                        <option value="1">1 Day Before Drive</option>
                        <option value="3" selected>3 Days Before Drive (Default)</option>
                        <option value="7">7 Days Before Drive</option>
                        <option value="14">14 Days Before Drive</option>
                      </select>
                    </div>
                  </div>
                </div>

                <!-- Email Digest -->
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom">
                  <div>
                    <div class="font-weight-700 text-dark">Email Digest</div>
                    <div class="text-muted small">Receive daily summary emails of student selections and drive updates.</div>
                  </div>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="switchEmailDigest" name="switch_email_digest" checked style="width: 44px; height: 24px;" onchange="saveAutoUpdateSettings()">
                  </div>
                </div>

                <!-- SMS Alerts -->
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom">
                  <div>
                    <div class="font-weight-700 text-dark">SMS Alerts</div>
                    <div class="text-muted small">Get urgent SMS notifications when critical document conflicts arise.</div>
                  </div>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="switchSmsAlerts" name="switch_sms_alerts" style="width: 44px; height: 24px;" onchange="saveAutoUpdateSettings()">
                  </div>
                </div>

                <!-- Push Notifications -->
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom">
                  <div>
                    <div class="font-weight-700 text-dark">Push Notifications</div>
                    <div class="text-muted small">Receive real-time in-app browser popups for company announcements.</div>
                  </div>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="switchPush" name="switch_push" checked style="width: 44px; height: 24px;" onchange="saveAutoUpdateSettings()">
                  </div>
                </div>

                <div class="mt-2">
                  <button class="btn btn-pp-primary py-2 px-4" onclick="saveAutoUpdateSettings(true)">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Save Notification & Auto-Update Settings
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- 4. Security & Access Panel -->
          <div class="tab-pane fade" id="panel-security" role="tabpanel">
            <div class="pp-card">
              <h5 class="h6 font-weight-800 text-dark mb-4 pb-2 border-bottom">Security & Access</h5>

              <div class="d-flex flex-column gap-4">
                <div>
                  <div class="font-weight-700 text-dark mb-1">Two-Factor Authentication (2FA)</div>
                  <p class="text-muted small mb-3">Add an extra layer of security using Google Authenticator or TOTP app.</p>
                  <button class="btn btn-pp-outline" onclick="showToast('2FA setup QR modal initiated...');">
                    <i class="fa-solid fa-lock me-1"></i> Configure 2FA
                  </button>
                </div>

                <div class="pt-3 border-top">
                  <div class="font-weight-700 text-dark mb-1">Active Sessions</div>
                  <p class="text-muted small mb-3">Sign out from all other browser sessions across desktop and mobile.</p>
                  <button class="btn btn-pp-outline text-danger border-danger" onclick="showToast('Signed out all active sessions!', 'warning');">
                    <i class="fa-solid fa-right-from-bracket me-1"></i> Sign out all sessions
                  </button>
                </div>

                <div class="pt-3 border-top">
                  <a href="#" class="font-weight-600 text-decoration-none" style="color: var(--pp-primary);" onclick="event.preventDefault(); showToast('Password reset link sent to email.');">
                    <i class="fa-solid fa-key me-1"></i> Change Password
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- 5. Platform Integrations Panel -->
          <div class="tab-pane fade" id="panel-integrations" role="tabpanel">
            <div class="pp-card">
              <h5 class="h6 font-weight-800 text-dark mb-4 pb-2 border-bottom">Platform Integrations</h5>

              <div class="row g-3">
                <!-- LinkedIn Recruiter -->
                <div class="col-12 col-md-6">
                  <div class="pp-card p-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                      <i class="fa-brands fa-linkedin text-primary" style="font-size: 2rem;"></i>
                      <div>
                        <div class="font-weight-700 text-dark">LinkedIn Recruiter</div>
                        <div class="text-muted small">Connected • Auto-sync</div>
                      </div>
                    </div>
                    <button class="btn btn-sm btn-pp-outline p-2" title="Integration Settings"><i class="fa-solid fa-gear"></i></button>
                  </div>
                </div>

                <!-- Slack Notifications -->
                <div class="col-12 col-md-6">
                  <div class="pp-card p-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                      <i class="fa-brands fa-slack text-danger" style="font-size: 2rem;"></i>
                      <div>
                        <div class="font-weight-700 text-dark">Slack Notifications</div>
                        <div class="text-muted small">Connected • #placements</div>
                      </div>
                    </div>
                    <button class="btn btn-sm btn-pp-outline p-2" title="Integration Settings"><i class="fa-solid fa-gear"></i></button>
                  </div>
                </div>

                <!-- Add Integration Card -->
                <div class="col-12 col-md-6">
                  <div class="pp-card p-3 d-flex align-items-center justify-content-center text-center cursor-pointer" style="border: 2px dashed #CBD5E1; min-height: 80px;" onclick="showToast('Opening Integration Marketplace...');">
                    <div class="font-weight-700 text-primary" style="color: var(--pp-primary) !important;">
                      <i class="fa-solid fa-plus me-1"></i> Add Integration
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- 6. System Reset & Trash Panel -->
          <div class="tab-pane fade" id="panel-trash" role="tabpanel">
            <div class="pp-card mb-4">
              <h5 class="h6 font-weight-800 text-dark mb-2 pb-2 border-bottom">System Reset & Data Wipe</h5>
              <p class="text-muted small mb-4">Temporarily wipe system records safely to the Recycle Bin for restoration, or perform a hard reset to empty all data across all places.</p>
              
              <div class="d-flex flex-wrap gap-3">
                <button class="btn btn-pp-outline py-2 px-3" onclick="confirmReset('students')">
                  <i class="fa-solid fa-user-slash me-1 text-secondary"></i> Reset Student Data Only
                </button>
                <button class="btn btn-pp-outline py-2 px-3" onclick="confirmReset('companies')">
                  <i class="fa-solid fa-building-circle-xmark me-1 text-secondary"></i> Reset Company Data Only
                </button>
                <button class="btn btn-warning py-2 px-3 border-0 text-white font-weight-600 rounded-3" style="background-color: #f59e0b !important;" onclick="confirmReset('all')">
                  <i class="fa-solid fa-box-archive me-1"></i> Soft Reset All Data (Move to Trash)
                </button>
                <button class="btn btn-danger py-2 px-3 border-0 bg-danger text-white font-weight-600 rounded-3" style="background-color: var(--pp-danger-dot) !important;" onclick="confirmHardReset()">
                  <i class="fa-solid fa-triangle-exclamation me-1"></i> Hard Reset (Empty All Data & Trash)
                </button>
              </div>
            </div>

            <div class="pp-card">
              <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <div>
                  <h5 class="h6 font-weight-800 text-dark mb-1">Recycle Bin (Trash)</h5>
                  <p class="text-muted small mb-0">Restore deleted candidates, placement records, and corporate profiles.</p>
                </div>
                <button class="btn btn-sm btn-pp-outline text-danger border-danger py-1" onclick="emptyTrashBin()">
                  <i class="fa-solid fa-trash-arrow-up me-1"></i> Empty Recycle Bin
                </button>
              </div>

              <div class="table-responsive" style="max-height: 350px;">
                <table class="pp-table mb-0">
                  <thead>
                    <tr>
                      <th>ITEM NAME</th>
                      <th>TYPE</th>
                      <th>DELETED AT</th>
                      <th class="text-end">ACTIONS</th>
                    </tr>
                  </thead>
                  <tbody id="trashTableBody">
                    <tr>
                      <td colspan="4" class="text-center py-4 text-muted small">Loading recycle bin data...</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

  </main>

  <script>window.API_BASE = '<?php echo API_BASE; ?>'; window.API_TOKEN = '<?php echo $_SESSION['token'] ?? ""; ?>';</script>
  <script src="assets/js/api.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    function selectTheme(card) {
      document.querySelectorAll('.theme-option-card').forEach(c => c.classList.remove('active'));
      card.classList.add('active');
      showToast('Theme preference updated!');
    }

    async function loadTrash() {
      const tbody = document.getElementById('trashTableBody');
      try {
        const trashItems = await API.get('/recycle-bin');
        if (!trashItems || trashItems.length === 0) {
          tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-muted small"><i class="fa-regular fa-trash-can me-1" style="font-size: 1.25rem;"></i> Recycle Bin is empty</td></tr>`;
          return;
        }
        tbody.innerHTML = trashItems.map(item => {
          const typeBadge = item.entity_type === 'student' 
            ? '<span class="badge-pill-info">Student</span>' 
            : '<span class="badge-pill-warning">Company</span>';
          
          return `
            <tr>
              <td class="font-weight-700 text-dark">${item.name}</td>
              <td>${typeBadge}</td>
              <td class="text-muted small">${new Date(item.deleted_at).toLocaleString()}</td>
              <td class="text-end">
                <button class="btn btn-sm btn-pp-primary py-1 px-2 me-1" onclick="restoreRecord(${item.id})">
                  <i class="fa-solid fa-arrow-rotate-left"></i> Restore
                </button>
                <button class="btn btn-sm btn-pp-outline text-danger border-danger py-1 px-2" onclick="deletePermanently(${item.id})">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </td>
            </tr>
          `;
        }).join('');
      } catch (err) {
        tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-danger small">Error loading trash: ${err.message}</td></tr>`;
      }
    }

    async function confirmReset(type) {
      const label = type === 'all' ? 'All Data (Students & Companies)' : (type === 'students' ? 'Student Data' : 'Company Data');
      if (confirm(`Are you absolutely sure you want to soft reset ${label}?\nThis will move all records to the Recycle Bin.`)) {
        try {
          const res = await API.post('/recycle-bin/reset', { type });
          showToast(`Soft reset completed! Moved ${res.students_moved || 0} students and ${res.companies_moved || 0} companies to trash.`);
          loadTrash();
        } catch (err) {
          showToast(err.message, 'danger');
        }
      }
    }

    async function confirmHardReset() {
      if (confirm('CRITICAL WARNING: Are you sure you want to HARD RESET the system?\nThis will permanently delete all students, companies, placement records, documents, and empty the Recycle Bin across all places!')) {
        try {
          const res = await API.post('/recycle-bin/hard-reset');
          showToast(res.message || 'Hard Reset completed! All data and places have been emptied.');
          loadTrash();
        } catch (err) {
          showToast(err.message, 'danger');
        }
      }
    }

    async function restoreRecord(id) {
      try {
        await API.post(`/recycle-bin/restore/${id}`);
        showToast('Record restored successfully!');
        loadTrash();
      } catch (err) {
        showToast(err.message, 'danger');
      }
    }

    async function deletePermanently(id) {
      if (confirm('Are you sure you want to permanently delete this record? This action cannot be undone.')) {
        try {
          await API.request(`/recycle-bin/${id}`, { method: 'DELETE' });
          showToast('Record permanently deleted.');
          loadTrash();
        } catch (err) {
          showToast(err.message, 'danger');
        }
      }
    }

    async function emptyTrashBin() {
      if (confirm('Are you sure you want to empty the Recycle Bin? All deleted data will be lost forever.')) {
        try {
          await API.request('/recycle-bin/empty', { method: 'DELETE' });
          showToast('Recycle Bin emptied successfully.');
          loadTrash();
        } catch (err) {
          showToast(err.message, 'danger');
        }
      }
    }

    function loadAutoUpdateSettings() {

      const autoUpdate = localStorage.getItem('setting_auto_update') !== 'false';

      const interval = localStorage.getItem('setting_update_interval') || '30000';
      const connectUpcoming = localStorage.getItem('setting_connect_upcoming') !== 'false';
      const leadDays = localStorage.getItem('setting_upcoming_lead_days') || '3';

      if (document.getElementById('switchAutoUpdate')) document.getElementById('switchAutoUpdate').checked = autoUpdate;
      if (document.getElementById('selectUpdateInterval')) document.getElementById('selectUpdateInterval').value = interval;
      if (document.getElementById('switchConnectUpcoming')) document.getElementById('switchConnectUpcoming').checked = connectUpcoming;
      if (document.getElementById('selectUpcomingLeadDays')) document.getElementById('selectUpcomingLeadDays').value = leadDays;
    }

    function saveAutoUpdateSettings(showToastMsg = false) {
      const autoUpdate = document.getElementById('switchAutoUpdate') ? document.getElementById('switchAutoUpdate').checked : true;
      const interval = document.getElementById('selectUpdateInterval') ? document.getElementById('selectUpdateInterval').value : '30000';
      const connectUpcoming = document.getElementById('switchConnectUpcoming') ? document.getElementById('switchConnectUpcoming').checked : true;
      const leadDays = document.getElementById('selectUpcomingLeadDays') ? document.getElementById('selectUpcomingLeadDays').value : '3';

      localStorage.setItem('setting_auto_update', autoUpdate ? 'true' : 'false');
      localStorage.setItem('setting_update_interval', interval);
      localStorage.setItem('setting_connect_upcoming', connectUpcoming ? 'true' : 'false');
      localStorage.setItem('setting_upcoming_lead_days', leadDays);

      if (showToastMsg) {
        showToast('Notification & Auto-Update settings saved successfully!');
      }
    }

    document.addEventListener('DOMContentLoaded', loadAutoUpdateSettings);
  </script>
</body>
</html>

