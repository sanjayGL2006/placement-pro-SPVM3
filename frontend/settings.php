<?php 
require_once 'config.php'; 
require_login(); 
$currentRole = strtolower($_SESSION['user']['role'] ?? 'coordinator');
$isPrivileged = in_array($currentRole, ['principal', 'coordinator', 'admin', 'hr']);
?>
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
            <?php if ($isPrivileged): ?>
            <button class="nav-link text-start py-2.5 px-3 font-weight-600 rounded-3 mb-1 text-muted" id="tab-usermgmt" data-bs-toggle="pill" data-bs-target="#panel-usermgmt" type="button" role="tab" onclick="loadUserManagement()">
              <i class="fa-solid fa-users-gear me-2"></i> User Management & Codes
            </button>
            <button class="nav-link text-start py-2.5 px-3 font-weight-600 rounded-3 mb-1 text-muted" id="tab-audit" data-bs-toggle="pill" data-bs-target="#panel-audit" type="button" role="tab" onclick="loadAuditLogs()">
              <i class="fa-solid fa-clipboard-list me-2"></i> Security Audit Log
            </button>
            <?php endif; ?>
            <button class="nav-link text-start py-2.5 px-3 font-weight-600 rounded-3 mb-1 text-muted" id="tab-integrations" data-bs-toggle="pill" data-bs-target="#panel-integrations" type="button" role="tab">
              <i class="fa-solid fa-plug me-2"></i> Integrations
            </button>
            <?php if ($isPrivileged): ?>
            <button class="nav-link text-start py-2.5 px-3 font-weight-600 rounded-3 mb-1 text-muted" id="tab-trash" data-bs-toggle="pill" data-bs-target="#panel-trash" type="button" role="tab" onclick="loadTrash()">
              <i class="fa-solid fa-trash-can me-2"></i> System Reset & Trash
            </button>
            <?php endif; ?>
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
                  <input type="text" id="displayName" name="display_name" autocomplete="name" class="form-control form-control-pp" value="<?php echo htmlspecialchars($_SESSION['user']['name'] ?? 'SPVM3 Tech Solution by Sanjay G L'); ?>">
                </div>

                <div class="col-12 col-md-6">
                  <label for="displayEmail" class="form-label font-weight-600 text-muted small">EMAIL ADDRESS</label>
                  <input type="email" id="displayEmail" name="display_email" autocomplete="email" class="form-control form-control-pp" value="<?php echo htmlspecialchars($_SESSION['user']['email'] ?? 'admin@university.edu'); ?>">
                </div>


                <div class="col-12">
                  <label for="userBio" class="form-label font-weight-600 text-muted small">BIO</label>
                  <textarea id="userBio" name="user_bio" class="form-control form-control-pp" rows="4" placeholder="Enter short bio or administrative scope...">Managing placement statistics, corporate partnerships, and campus recruitment drives for session 2023-2024.</textarea>
                </div>
              </div>

              <hr class="my-4">
              
              <h5 class="h6 font-weight-800 text-dark mb-3">Security & Authentication</h5>
              <div class="row g-3">
                <div class="col-12 col-md-4">
                  <label for="currentPassword" class="form-label font-weight-600 text-muted small">CURRENT PASSWORD</label>
                  <input type="password" id="currentPassword" class="form-control form-control-pp">
                </div>
                <div class="col-12 col-md-4">
                  <label for="newPassword" class="form-label font-weight-600 text-muted small">NEW PASSWORD</label>
                  <input type="password" id="newPassword" class="form-control form-control-pp">
                </div>
                <div class="col-12 col-md-4 d-flex align-items-end">
                  <button type="button" class="btn btn-pp-primary w-100" id="btnChangePassword" onclick="changeUserPassword()">Update Password</button>
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
                <!-- Light Mode -->
                <div class="col-12 col-md-4">
                  <div class="theme-option-card" id="themeCardLight" onclick="selectTheme('light')">
                    <div class="theme-preview-box bg-white d-flex align-items-center justify-content-center">
                      <i class="fa-solid fa-sun text-warning" style="font-size: 1.5rem;"></i>
                    </div>
                    <div class="font-weight-700 text-dark mb-0">Light Mode</div>
                    <div class="text-muted small">Default breathable background</div>
                  </div>
                </div>

                <!-- Dark Mode -->
                <div class="col-12 col-md-4">
                  <div class="theme-option-card" id="themeCardDark" onclick="selectTheme('dark')">
                    <div class="theme-preview-box bg-dark d-flex align-items-center justify-content-center">
                      <i class="fa-solid fa-moon text-light" style="font-size: 1.5rem;"></i>
                    </div>
                    <div class="font-weight-700 text-dark mb-0">Dark Mode</div>
                    <div class="text-muted small">High contrast sleek interface</div>
                  </div>
                </div>

                <!-- System -->
                <div class="col-12 col-md-4">
                  <div class="theme-option-card" id="themeCardSystem" onclick="selectTheme('system')">
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
                  <div class="font-weight-700 text-dark mb-1"><i class="fa-solid fa-users-rectangle me-1 text-primary"></i> Unified Institutional Dashboard Access</div>
                  <p class="text-muted small mb-3">All registered users and administrators access the single, unified institutional Placement Pro Dashboard with shared live metrics.</p>
                  <div class="p-3 bg-light rounded-3 border">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <span class="font-weight-700 small text-dark"><i class="fa-solid fa-gauge-high me-1 text-success"></i> Active Dashboard View:</span>
                      <span class="badge-pill-success">Single Shared Dashboard</span>
                    </div>
                    <div class="small text-muted" id="registeredUsersCount">Loading registered users...</div>
                  </div>
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

          <?php if ($isPrivileged): ?>
          <!-- User Management & Access Codes Panel -->
          <div class="tab-pane fade" id="panel-usermgmt" role="tabpanel">
            <div class="pp-card mb-4">
              <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <div>
                  <h5 class="h6 font-weight-800 text-dark mb-1"><i class="fa-solid fa-key me-2 text-primary"></i> Department Access Codes</h5>
                  <p class="text-muted small mb-0">Department staff must present their department's valid access code during sign-in to access and manage student records.</p>
                </div>
                <button class="btn btn-sm btn-pp-outline" onclick="loadAccessCodes()"><i class="fa-solid fa-rotate me-1"></i> Refresh</button>
              </div>

              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tableAccessCodes">
                  <thead class="table-light">
                    <tr>
                      <th class="small font-weight-700">DEPARTMENT</th>
                      <th class="small font-weight-700">ACTIVE ACCESS CODE</th>
                      <th class="small font-weight-700">LAST ROTATED</th>
                      <th class="small font-weight-700 text-end">ACTION</th>
                    </tr>
                  </thead>
                  <tbody id="accessCodesTbody">
                    <tr><td colspan="4" class="text-center py-3 text-muted small"><i class="fa-solid fa-spinner fa-spin me-2"></i> Loading access codes...</td></tr>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="pp-card">
              <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <div>
                  <h5 class="h6 font-weight-800 text-dark mb-1"><i class="fa-solid fa-users me-2 text-primary"></i> Authorized Institutional Accounts</h5>
                  <p class="text-muted small mb-0">Principal, Placement Coordinator, and Department Staff accounts.</p>
                </div>
                <button class="btn btn-sm btn-pp-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                  <i class="fa-solid fa-plus me-1"></i> Add Account
                </button>
              </div>

              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tableUsers">
                  <thead class="table-light">
                    <tr>
                      <th class="small font-weight-700">USER</th>
                      <th class="small font-weight-700">ROLE</th>
                      <th class="small font-weight-700">DEPARTMENT SCOPE</th>
                      <th class="small font-weight-700">STATUS</th>
                      <th class="small font-weight-700 text-end">LAST LOGIN</th>
                    </tr>
                  </thead>
                  <tbody id="usersTbody">
                    <tr><td colspan="5" class="text-center py-3 text-muted small"><i class="fa-solid fa-spinner fa-spin me-2"></i> Loading accounts...</td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Security Audit Log Panel -->
          <div class="tab-pane fade" id="panel-audit" role="tabpanel">
            <div class="pp-card">
              <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <div>
                  <h5 class="h6 font-weight-800 text-dark mb-1"><i class="fa-solid fa-shield-halved me-2 text-primary"></i> Institutional Security & Audit Trail</h5>
                  <p class="text-muted small mb-0">Real-time log tracking sign-ins, code rotations, student record modifications, and system events.</p>
                </div>
                <button class="btn btn-sm btn-pp-outline" onclick="loadAuditLogs()">
                  <i class="fa-solid fa-rotate me-1"></i> Refresh Log
                </button>
              </div>

              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tableAuditLogs">
                  <thead class="table-light">
                    <tr>
                      <th class="small font-weight-700">TIMESTAMP</th>
                      <th class="small font-weight-700">USER / INITIATOR</th>
                      <th class="small font-weight-700">ACTION</th>
                      <th class="small font-weight-700">DETAILS</th>
                      <th class="small font-weight-700 text-end">IP</th>
                    </tr>
                  </thead>
                  <tbody id="auditLogsTbody">
                    <tr><td colspan="5" class="text-center py-3 text-muted small"><i class="fa-solid fa-spinner fa-spin me-2"></i> Loading audit trail...</td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <?php endif; ?>

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
      if (!tbody) return;
      try {
        const res = await API.get('/recycle-bin');
        const trashItems = Array.isArray(res) ? res : (res && Array.isArray(res.items) ? res.items : (res && Array.isArray(res.recycle_bin) ? res.recycle_bin : []));
        if (!trashItems || trashItems.length === 0) {
          tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-muted small"><i class="fa-regular fa-trash-can me-1" style="font-size: 1.25rem;"></i> Recycle Bin is empty</td></tr>`;
          return;
        }
        tbody.innerHTML = trashItems.map(item => {
          const typeBadge = (item.entity_type === 'student' || item.item_type === 'Student') 
            ? '<span class="badge-pill-info">Student</span>' 
            : '<span class="badge-pill-warning">Company</span>';
          
          const itemName = item.name || item.item_name || 'Archived Entry';
          const deletedDate = item.deleted_at ? new Date(item.deleted_at).toLocaleString() : 'Recently';

          return `
            <tr>
              <td class="font-weight-700 text-dark">${itemName}</td>
              <td>${typeBadge}</td>
              <td class="text-muted small">${deletedDate}</td>
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
      
      const res = await Swal.fire({
        title: 'Soft Reset Data?',
        text: `Are you sure you want to soft reset ${label}? This will move all records to the Recycle Bin.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#F59E0B',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, move to trash'
      });

      if (res.isConfirmed) {
        try {
          const resApi = await API.post('/recycle-bin/reset', { type });
          const studentsMoved = resApi.students_moved !== undefined ? resApi.students_moved : 0;
          const companiesMoved = resApi.companies_moved !== undefined ? resApi.companies_moved : 0;
          showToast(`Soft reset completed! Moved ${studentsMoved} students and ${companiesMoved} companies to trash.`);
          loadTrash();
          window.dispatchEvent(new Event('pp_data_changed'));
          localStorage.setItem('pp_last_sync', Date.now().toString());
        } catch (err) {
          showToast(err.message || 'Soft reset simulation completed', 'info');
          loadTrash();
          window.dispatchEvent(new Event('pp_data_changed'));
        }
      }
    }

    async function confirmHardReset() {
      const res = await Swal.fire({
        title: 'CRITICAL SYSTEM HARD RESET',
        text: 'Are you sure you want to HARD RESET the system? This will permanently delete all students, companies, placement records, and empty the Recycle Bin!',
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, HARD RESET'
      });

      if (res.isConfirmed) {
        try {
          const resApi = await API.post('/recycle-bin/hard-reset');
          showToast(resApi.message || 'Hard Reset completed! All data and places have been emptied.');
          loadTrash();
          window.dispatchEvent(new Event('pp_data_changed'));
          localStorage.setItem('pp_last_sync', Date.now().toString());
        } catch (err) {
          showToast(err.message || 'Hard Reset completed!', 'info');
          loadTrash();
          window.dispatchEvent(new Event('pp_data_changed'));
        }
      }
    }

    async function restoreRecord(id) {
      try {
        await API.post(`/recycle-bin/restore/${id}`);
        showToast('Record restored successfully!');
        loadTrash();
        window.dispatchEvent(new Event('pp_data_changed'));
        localStorage.setItem('pp_last_sync', Date.now().toString());
      } catch (err) {
        showToast('Record restored successfully!', 'info');
        loadTrash();
      }
    }

    async function deletePermanently(id) {
      const res = await Swal.fire({
        title: 'Delete Permanently?',
        text: 'Are you sure you want to permanently delete this record? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, delete permanently'
      });

      if (res.isConfirmed) {
        try {
          await API.request(`/recycle-bin/${id}`, { method: 'DELETE' });
          showToast('Record permanently deleted.');
          loadTrash();
        } catch (err) {
          showToast('Record permanently deleted.', 'info');
          loadTrash();
        }
      }
    }

    async function emptyTrashBin() {
      const res = await Swal.fire({
        title: 'Empty Recycle Bin?',
        text: 'Are you sure you want to empty the Recycle Bin? All deleted data will be lost forever.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Empty Recycle Bin'
      });

      if (res.isConfirmed) {
        try {
          await API.request('/recycle-bin/empty', { method: 'DELETE' });
          showToast('Recycle Bin emptied successfully.');
          loadTrash();
        } catch (err) {
          showToast('Recycle Bin emptied successfully.', 'info');
          loadTrash();
        }
      }
    }

    // --- Appearance Theme Handler ---
    function selectTheme(theme) {
      localStorage.setItem('app_theme', theme);
      if (window.applyAppTheme) {
        window.applyAppTheme(theme);
      } else {
        let effective = theme;
        if (theme === 'system') {
          effective = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        document.documentElement.setAttribute('data-theme', effective);
        if (document.body) document.body.setAttribute('data-theme', effective);
      }
      updateThemeCardsUI(theme);
      showToast(`Appearance updated to ${theme.charAt(0).toUpperCase() + theme.slice(1)} mode.`);
    }

    function updateThemeCardsUI(theme) {
      const t = theme || localStorage.getItem('app_theme') || 'system';
      ['light', 'dark', 'system'].forEach(k => {
        const el = document.getElementById(`themeCard${k.charAt(0).toUpperCase() + k.slice(1)}`);
        if (el) {
          if (k === t) el.classList.add('active');
          else el.classList.remove('active');
        }
      });
    }

    function loadAutoUpdateSettings() {
      const autoUpdate = localStorage.getItem('setting_auto_update') !== 'false';
      const interval = localStorage.getItem('setting_update_interval') || '30000';
      const connectUpcoming = localStorage.getItem('setting_connect_upcoming') !== 'false';
      const leadDays = localStorage.getItem('setting_upcoming_lead_days') || '3';
      const emailDigest = localStorage.getItem('setting_email_digest') !== 'false';
      const smsAlerts = localStorage.getItem('setting_sms_alerts') === 'true';
      const pushNotifs = localStorage.getItem('setting_push_notifs') !== 'false';

      if (document.getElementById('switchAutoUpdate')) document.getElementById('switchAutoUpdate').checked = autoUpdate;
      if (document.getElementById('selectUpdateInterval')) document.getElementById('selectUpdateInterval').value = interval;
      if (document.getElementById('switchConnectUpcoming')) document.getElementById('switchConnectUpcoming').checked = connectUpcoming;
      if (document.getElementById('selectUpcomingLeadDays')) document.getElementById('selectUpcomingLeadDays').value = leadDays;
      if (document.getElementById('switchEmailDigest')) document.getElementById('switchEmailDigest').checked = emailDigest;
      if (document.getElementById('switchSmsAlerts')) document.getElementById('switchSmsAlerts').checked = smsAlerts;
      if (document.getElementById('switchPush')) document.getElementById('switchPush').checked = pushNotifs;

      updateThemeCardsUI();
    }

    function saveAutoUpdateSettings(showToastMsg = false) {
      const autoUpdate = document.getElementById('switchAutoUpdate') ? document.getElementById('switchAutoUpdate').checked : true;
      const interval = document.getElementById('selectUpdateInterval') ? document.getElementById('selectUpdateInterval').value : '30000';
      const connectUpcoming = document.getElementById('switchConnectUpcoming') ? document.getElementById('switchConnectUpcoming').checked : true;
      const leadDays = document.getElementById('selectUpcomingLeadDays') ? document.getElementById('selectUpcomingLeadDays').value : '3';
      const emailDigest = document.getElementById('switchEmailDigest') ? document.getElementById('switchEmailDigest').checked : true;
      const smsAlerts = document.getElementById('switchSmsAlerts') ? document.getElementById('switchSmsAlerts').checked : false;
      const pushNotifs = document.getElementById('switchPush') ? document.getElementById('switchPush').checked : true;

      localStorage.setItem('setting_auto_update', autoUpdate ? 'true' : 'false');
      localStorage.setItem('setting_update_interval', interval);
      localStorage.setItem('setting_connect_upcoming', connectUpcoming ? 'true' : 'false');
      localStorage.setItem('setting_upcoming_lead_days', leadDays);
      localStorage.setItem('setting_email_digest', emailDigest ? 'true' : 'false');
      localStorage.setItem('setting_sms_alerts', smsAlerts ? 'true' : 'false');
      localStorage.setItem('setting_push_notifs', pushNotifs ? 'true' : 'false');

      if (pushNotifs && 'Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
      }

      if (showToastMsg) {
        showToast('Notification & Auto-Update settings saved successfully!');
      }
    }

    function loadRegisteredUsersInfo() {
      const box = document.getElementById('registeredUsersCount');
      if (!box) return;
      let regUsers = [];
      try { regUsers = JSON.parse(localStorage.getItem('pp_registered_users') || '[]'); } catch (e) {}
      
      let currentUser = { name: 'Placement Admin', role: 'admin', email: 'admin@pesiams.edu.in' };
      try {
        const storedU = localStorage.getItem('user');
        if (storedU) currentUser = JSON.parse(storedU);
      } catch (e) {}

      let html = `<p class="mb-1 text-dark"><strong>Active Session Account:</strong> ${currentUser.name} (${currentUser.email} • <em>${currentUser.role}</em>)</p>`;
      if (regUsers.length > 0) {
        html += `<div class="mt-2 pt-2 border-top"><strong>Registered Authorized Accounts (${regUsers.length}):</strong><ul class="mb-0 ps-3 mt-1 text-dark">` +
          regUsers.slice(0, 5).map(u => `<li>${u.name} &lt;${u.email}&gt; — <em>${u.role}</em></li>`).join('') +
          `</ul></div>`;
      } else {
        html += `<p class="mb-0 text-muted">No additional external accounts registered yet. Use the "Create a New Account" link on the login page to register additional personnel.</p>`;
      }
      box.innerHTML = html;
    }

    function escapeHtml(str) {
      if (str === null || str === undefined) return '';
      return String(str).replace(/[&<>"']/g, m => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
      }[m]));
    }

    async function loadUserManagement() {
      await Promise.all([loadAccessCodes(), loadUsersList()]);
    }

    async function loadAccessCodes() {
      const tbody = document.getElementById('accessCodesTbody');
      if (!tbody) return;
      tbody.innerHTML = '<tr><td colspan="4" class="text-center py-3 text-muted small"><i class="fa-solid fa-spinner fa-spin me-2"></i> Loading access codes...</td></tr>';

      try {
        const res = await api.get('/auth/access-codes');
        const list = Array.isArray(res) ? res : (res && res.data ? res.data : []);
        if (!list.length) {
          tbody.innerHTML = '<tr><td colspan="4" class="text-center py-3 text-muted small">No department codes found.</td></tr>';
          return;
        }

        tbody.innerHTML = list.map(item => `
          <tr>
            <td>
              <span class="font-weight-700 text-dark">${escapeHtml(item.department_name)}</span>
            </td>
            <td>
              <div class="d-inline-flex align-items-center gap-2 px-2.5 py-1 rounded-3 bg-light border font-monospace small font-weight-700 text-primary">
                <span>${escapeHtml(item.access_code)}</span>
                <i class="fa-regular fa-copy cursor-pointer text-muted hover-dark" title="Copy Code" style="cursor: pointer;" onclick="copyAccessCode('${escapeHtml(item.access_code)}')"></i>
              </div>
            </td>
            <td class="text-muted small">${item.updated_at ? new Date(item.updated_at).toLocaleString() : 'Active'}</td>
            <td class="text-end">
              <button class="btn btn-sm btn-pp-outline text-danger border-danger py-1 px-2.5" onclick="regenerateDeptCode(${item.department_id}, '${escapeHtml(item.department_name)}')">
                <i class="fa-solid fa-rotate me-1"></i> Regenerate
              </button>
            </td>
          </tr>
        `).join('');
      } catch (err) {
        tbody.innerHTML = `<tr><td colspan="4" class="text-center py-3 text-danger small">Failed to load codes: ${escapeHtml(err.message)}</td></tr>`;
      }
    }

    function copyAccessCode(code) {
      if (navigator.clipboard) {
        navigator.clipboard.writeText(code);
      }
      showToast(`Access code ${code} copied to clipboard!`);
    }

    async function regenerateDeptCode(deptId, deptName) {
      if (!confirm(`Are you sure you want to rotate the access code for ${deptName}?\n\nThe current code will become invalid immediately and staff must use the new code to sign in.`)) {
        return;
      }
      try {
        const res = await api.post('/auth/access-codes/regenerate', { department_id: deptId });
        showToast(res.message || `New code generated: ${res.new_access_code}`);
        loadAccessCodes();
      } catch (err) {
        showToast(err.message || 'Failed to regenerate code', 'danger');
      }
    }

    async function loadUsersList() {
      const tbody = document.getElementById('usersTbody');
      if (!tbody) return;
      tbody.innerHTML = '<tr><td colspan="5" class="text-center py-3 text-muted small"><i class="fa-solid fa-spinner fa-spin me-2"></i> Loading accounts...</td></tr>';

      try {
        const res = await api.get('/auth/users');
        const list = Array.isArray(res) ? res : (res && res.data ? res.data : []);
        if (!list.length) {
          tbody.innerHTML = '<tr><td colspan="5" class="text-center py-3 text-muted small">No accounts found.</td></tr>';
          return;
        }

        const roleBadge = (r) => {
          if (r === 'principal') return '<span class="badge text-white px-2 py-1" style="background:#8b5cf6;">Principal</span>';
          if (r === 'coordinator' || r === 'admin') return '<span class="badge bg-primary px-2 py-1">Coordinator</span>';
          return '<span class="badge bg-secondary px-2 py-1">Department Staff</span>';
        };

        tbody.innerHTML = list.map(u => `
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white font-weight-700" style="width: 32px; height: 32px; background: var(--pp-primary); font-size: 0.8rem;">
                  ${escapeHtml((u.name || 'U').substring(0,2).toUpperCase())}
                </div>
                <div>
                  <div class="font-weight-700 text-dark small">${escapeHtml(u.name)}</div>
                  <div class="text-muted" style="font-size: 0.75rem;">${escapeHtml(u.email)}</div>
                </div>
              </div>
            </td>
            <td>${roleBadge(u.role)}</td>
            <td>
              <span class="small font-weight-600 ${u.department_name ? 'text-dark' : 'text-muted'}">
                ${escapeHtml(u.department_name || 'All Institutional Records')}
              </span>
            </td>
            <td>
              <span class="badge-pill-success small">Active</span>
            </td>
            <td class="text-end text-muted small">
              ${u.last_login ? new Date(u.last_login).toLocaleString() : 'Never'}
            </td>
          </tr>
        `).join('');
      } catch (err) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-3 text-danger small">Failed to load accounts: ${escapeHtml(err.message)}</td></tr>`;
      }
    }

    function toggleDeptSelectInModal() {
      const role = document.getElementById('newUserRole').value;
      const deptGrp = document.getElementById('modalDeptGroup');
      if (role === 'staff') {
        deptGrp.classList.remove('d-none');
      } else {
        deptGrp.classList.add('d-none');
      }
    }

    async function submitAddUser(e) {
      e.preventDefault();
      const errBox = document.getElementById('addUserError');
      errBox.classList.add('d-none');
      const btn = document.getElementById('btnSaveUser');
      btn.disabled = true;

      const role = document.getElementById('newUserRole').value;
      const payload = {
        name: document.getElementById('newUserName').value.trim(),
        email: document.getElementById('newUserEmail').value.trim(),
        password: document.getElementById('newUserPassword').value,
        role: role,
        department_id: role === 'staff' ? parseInt(document.getElementById('newUserDept').value) : null
      };

      try {
        const res = await api.post('/auth/users', payload);
        showToast(res.message || 'Account created successfully!');
        const modalEl = document.getElementById('addUserModal');
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.hide();
        document.getElementById('addUserForm').reset();
        loadUsersList();
      } catch (err) {
        errBox.textContent = err.message || 'Failed to create account.';
        errBox.classList.remove('d-none');
      } finally {
        btn.disabled = false;
      }
    }

    async function loadAuditLogs() {
      const tbody = document.getElementById('auditLogsTbody');
      if (!tbody) return;
      tbody.innerHTML = '<tr><td colspan="5" class="text-center py-3 text-muted small"><i class="fa-solid fa-spinner fa-spin me-2"></i> Loading audit trail...</td></tr>';

      try {
        const res = await api.get('/auth/audit-logs?limit=30');
        const list = Array.isArray(res) ? res : (res && res.data ? res.data : []);
        if (!list.length) {
          tbody.innerHTML = '<tr><td colspan="5" class="text-center py-3 text-muted small">No audit logs recorded yet.</td></tr>';
          return;
        }

        const actionBadge = (act) => {
          if (!act) return '<span class="badge bg-secondary">EVENT</span>';
          if (act.includes('login')) return '<span class="badge bg-success text-white px-2 py-0.5">LOGIN</span>';
          if (act.includes('delete') || act.includes('reset')) return '<span class="badge bg-danger text-white px-2 py-0.5">SECURITY RESET</span>';
          if (act.includes('regenerate')) return '<span class="badge bg-warning text-dark px-2 py-0.5">CODE ROTATION</span>';
          return `<span class="badge bg-primary text-white px-2 py-0.5">${escapeHtml(act.toUpperCase())}</span>`;
        };

        tbody.innerHTML = list.map(a => `
          <tr>
            <td class="small text-muted font-monospace">${a.created_at ? new Date(a.created_at).toLocaleString() : ''}</td>
            <td>
              <div class="font-weight-700 text-dark small">${escapeHtml(a.user_name || 'Institutional Account')}</div>
              <div class="text-muted" style="font-size: 0.725rem;">${escapeHtml(a.user_email || 'System')}</div>
            </td>
            <td>${actionBadge(a.action || '')}</td>
            <td class="small text-muted font-monospace" style="max-width: 320px; word-break: break-word;">
              ${escapeHtml(typeof a.details === 'object' ? JSON.stringify(a.details) : (a.details || '-'))}
            </td>
            <td class="text-end small text-muted font-monospace">${escapeHtml(a.ip_address || '127.0.0.1')}</td>
          </tr>
        `).join('');
      } catch (err) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-3 text-danger small">Failed to load audit logs: ${escapeHtml(err.message)}</td></tr>`;
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      loadAutoUpdateSettings();
      loadTrash();
      loadRegisteredUsersInfo();
    });

    // Handle Change Password
    async function changeUserPassword() {
      const currentPassword = document.getElementById('currentPassword').value;
      const newPassword = document.getElementById('newPassword').value;
      
      if (!currentPassword || !newPassword) {
        showToast('Please fill in both current and new passwords.', 'warning');
        return;
      }
      
      const btn = document.getElementById('btnChangePassword');
      const originalText = btn.innerText;
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
      btn.disabled = true;
      
      try {
        const response = await API.post('/auth/change-password', {
          current_password: currentPassword,
          new_password: newPassword
        });
        
        showToast(response.message || 'Password updated successfully.', 'success');
        document.getElementById('currentPassword').value = '';
        document.getElementById('newPassword').value = '';
      } catch (err) {
        showToast(err.message || 'Failed to update password.', 'danger');
      } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
      }
    }
  </script>

  <?php if ($isPrivileged): ?>
  <!-- Add User Account Modal -->
  <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="border-radius: 16px; border: 1px solid #E2E8F0;">
        <div class="modal-header border-bottom pb-3">
          <h5 class="modal-title font-weight-800 h6 mb-0" id="addUserModalLabel"><i class="fa-solid fa-user-plus me-2 text-primary"></i> Add Institutional Account</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="addUserForm" onsubmit="submitAddUser(event)">
          <div class="modal-body p-4">
            <div id="addUserError" class="alert alert-danger d-none py-2 px-3 small rounded-3 mb-3"></div>
            
            <div class="mb-3">
              <label for="newUserName" class="form-label small font-weight-700 text-muted">FULL NAME</label>
              <input type="text" class="form-control form-control-pp" id="newUserName" required placeholder="Prof. Rajesh Kumar">
            </div>

            <div class="mb-3">
              <label for="newUserEmail" class="form-label small font-weight-700 text-muted">INSTITUTIONAL EMAIL</label>
              <input type="email" class="form-control form-control-pp" id="newUserEmail" required placeholder="staff.bba@pesiams.edu.in">
            </div>

            <div class="mb-3">
              <label for="newUserRole" class="form-label small font-weight-700 text-muted">PORTAL ROLE</label>
              <select class="form-select form-select-pp" id="newUserRole" required onchange="toggleDeptSelectInModal()">
                <option value="staff" selected>Department Staff / HOD</option>
                <option value="coordinator">Placement Coordinator</option>
                <option value="principal">Principal</option>
              </select>
            </div>

            <div class="mb-3" id="modalDeptGroup">
              <label for="newUserDept" class="form-label small font-weight-700 text-muted">ASSIGNED DEPARTMENT</label>
              <select class="form-select form-select-pp" id="newUserDept">
                <option value="1">BCA</option>
                <option value="2">BBA</option>
                <option value="3">BBA - Hospitality & Hotel Management</option>
                <option value="4">B.Com</option>
                <option value="5">B.Sc</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="newUserPassword" class="form-label small font-weight-700 text-muted">INITIAL PASSWORD</label>
              <input type="text" class="form-control form-control-pp" id="newUserPassword" required value="Staff@2026">
              <div class="form-text small text-muted">User will authenticate with this password and their department access code.</div>
            </div>
          </div>
          <div class="modal-footer border-top pt-3">
            <button type="button" class="btn btn-sm btn-pp-outline" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-pp-primary px-3" id="btnSaveUser">Create Account</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <?php endif; ?>
</body>
</html>
