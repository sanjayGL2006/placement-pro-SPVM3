<?php
$userName = isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'SPVM3 Tech Solution by Sanjay G L';
$userRole = isset($_SESSION['user']['role']) ? ucfirst($_SESSION['user']['role']) : 'Administrator';
?>
<header id="top-header">
  <!-- Search Bar -->
  <div class="header-search-container">
    <i class="fa-solid fa-magnifying-glass search-icon"></i>
    <input type="text" placeholder="Search students, companies, records..." id="globalSearchInput">
  </div>

  <!-- Header Right Actions -->
  <div class="header-actions">
    <!-- Help / Question Mark -->
    <button class="header-icon-btn" title="Help & Documentation">
      <i class="fa-regular fa-circle-question"></i>
    </button>

    <!-- Notification Bell -->
    <div class="dropdown">
      <button class="header-icon-btn position-relative" title="Notifications" data-bs-toggle="dropdown" aria-expanded="false" id="notifDropdownBtn">
        <i class="fa-regular fa-bell"></i>
        <span class="notification-dot d-none" id="notifBadge" style="width: 8px; height: 8px; background-color: var(--pp-danger-dot) !important; border-radius: 50%; position: absolute; top: 8px; right: 8px;"></span>
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 mt-2 p-0" style="width: 320px; max-height: 400px; overflow-y: auto;" id="notifDropdownMenu">
        <li class="p-3 border-bottom d-flex justify-content-between align-items-center">
          <span class="font-weight-700 text-dark">Notifications</span>
          <a href="#" class="small text-decoration-none" style="color: var(--pp-primary);" id="notifMarkAllRead">Mark all read</a>
        </li>
        <div id="notifListContainer">
          <li class="p-3 text-center text-muted small">No new notifications</li>
        </div>
      </ul>
    </div>

    <!-- User Profile Component -->
    <div class="dropdown">
      <div class="user-profile-badge cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
        <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=120" alt="Avatar" class="user-avatar">
        <div class="user-info">
          <div class="user-name"><?php echo htmlspecialchars($userName); ?></div>
          <div class="user-role"><?php echo htmlspecialchars($userRole); ?></div>
        </div>
        <i class="fa-solid fa-chevron-down ms-1 text-muted" style="font-size: 0.75rem;"></i>
      </div>
      <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-2">
        <li><a class="dropdown-item py-2" href="settings.php"><i class="fa-solid fa-user me-2 text-muted"></i> Edit Profile</a></li>
        <li><a class="dropdown-item py-2" href="settings.php"><i class="fa-solid fa-gear me-2 text-muted"></i> Account Settings</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i> Sign Out</a></li>
      </ul>
    </div>
  </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const badge = document.getElementById('notifBadge');
  const container = document.getElementById('notifListContainer');
  const markAllBtn = document.getElementById('notifMarkAllRead');

  async function loadNotifications() {
    try {
      if (typeof API === 'undefined') return;
      const data = await API.get('/notifications');
      if (data && data.notifications) {
        if (data.unread_count > 0) {
          badge.classList.remove('d-none');
        } else {
          badge.classList.add('d-none');
        }

        if (data.notifications.length === 0) {
          container.innerHTML = `<li class="p-3 text-center text-muted small">No notifications</li>`;
        } else {
          container.innerHTML = data.notifications.map(n => {
            const timeStr = new Date(n.created_at).toLocaleTimeString(undefined, {hour: '2-digit', minute:'2-digit'});
            const dateStr = new Date(n.created_at).toLocaleDateString(undefined, {month: 'short', day: 'numeric'});
            
            let icon = '<i class="fa-solid fa-circle-info text-info"></i>';
            if (n.type === 'success') icon = '<i class="fa-solid fa-circle-check text-success"></i>';
            else if (n.type === 'warning') icon = '<i class="fa-solid fa-circle-exclamation text-warning"></i>';
            else if (n.type === 'danger') icon = '<i class="fa-solid fa-triangle-exclamation text-danger"></i>';
            
            const bgClass = n.is_read ? '' : 'bg-light';
            
            return `
              <li class="p-3 border-bottom ${bgClass} d-flex align-items-start gap-3">
                <div style="font-size: 1.15rem; margin-top: 0.15rem;">${icon}</div>
                <div class="flex-grow-1">
                  <div class="font-weight-700 text-dark small" style="line-height: 1.25;">${n.title}</div>
                  <div class="text-muted small mt-1" style="font-size: 0.775rem; line-height: 1.35;">${n.message}</div>
                  <div class="text-muted mt-1" style="font-size: 0.7rem;">${dateStr} at ${timeStr}</div>
                </div>
              </li>
            `;
          }).join('');
        }
      }
    } catch (err) {
      console.log('Failed to fetch notifications:', err.message);
    }
  }

  markAllBtn.addEventListener('click', async (e) => {
    e.preventDefault();
    try {
      await API.post('/notifications/mark-read');
      badge.classList.add('d-none');
      loadNotifications();
      showToast('All notifications marked as read.');
    } catch (err) {
      console.error(err);
    }
  });

    // Load once
  loadNotifications();
  // Poll every 30 seconds
  setInterval(loadNotifications, 30000);
});

// --- PWA Installation Logic ---
(function() {
  // Inject manifest dynamically
  const manifestLink = document.createElement('link');
  manifestLink.rel = 'manifest';
  manifestLink.href = 'manifest.json';
  document.head.appendChild(manifestLink);

  // Register Service Worker
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('service-worker.js')
        .then(reg => console.log('Service Worker registered', reg))
        .catch(err => console.error('Service Worker registration failed', err));
    });
  }

  // Handle Install Prompt
  let deferredPrompt;
  const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;

  window.addEventListener('DOMContentLoaded', () => {
    const pwaBanner = document.createElement('div');
    pwaBanner.id = 'pwaInstallBanner';
    pwaBanner.className = 'fixed-bottom bg-primary text-white p-3 shadow d-none d-flex justify-content-between align-items-center';
    pwaBanner.style.zIndex = '9999';
    pwaBanner.innerHTML = `
      <div>
        <strong>Install Placement Pro App</strong>
        <p class="mb-0 small" id="pwaInstruction">Add to your home screen for a better experience.</p>
      </div>
      <div>
        <button id="pwaInstallBtn" class="btn btn-light btn-sm font-weight-bold me-2">Install</button>
        <button id="pwaDismissBtn" class="btn btn-outline-light btn-sm"><i class="fa-solid fa-times"></i></button>
      </div>
    `;
    document.body.appendChild(pwaBanner);

    const btnInstall = document.getElementById('pwaInstallBtn');
    const btnDismiss = document.getElementById('pwaDismissBtn');

    btnDismiss.addEventListener('click', () => {
      pwaBanner.classList.add('d-none');
      localStorage.setItem('pwaDismissed', 'true');
    });

    if (localStorage.getItem('pwaDismissed') !== 'true') {
      if (isIOS) {
        // iOS doesn't support beforeinstallprompt, show instructional banner
        const isStandalone = window.navigator.standalone === true;
        if (!isStandalone) {
          pwaBanner.classList.remove('d-none');
          btnInstall.style.display = 'none';
          document.getElementById('pwaInstruction').innerText = 'Tap Share button below, then "Add to Home Screen".';
        }
      } else {
        window.addEventListener('beforeinstallprompt', (e) => {
          e.preventDefault();
          deferredPrompt = e;
          pwaBanner.classList.remove('d-none');
        });

        btnInstall.addEventListener('click', async () => {
          if (deferredPrompt) {
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            if (outcome === 'accepted') {
              console.log('User accepted PWA install');
            }
            deferredPrompt = null;
            pwaBanner.classList.add('d-none');
          }
        });
      }
    }
  });
})();
</script>
