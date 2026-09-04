<?php
$userName = isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'SPVM3 Tech Solution by Sanjay G L';
$userRole = isset($_SESSION['user']['role']) ? ucfirst($_SESSION['user']['role']) : 'Administrator';
?>
<header id="top-header">
  <div class="d-flex align-items-center gap-2">
    <button class="btn btn-sm btn-light border d-md-none me-1" id="mobileMenuToggle" aria-label="Toggle Navigation Sidebar">
      <i class="fa-solid fa-bars"></i>
    </button>
    <!-- Search Bar -->
    <div class="header-search-container">
      <i class="fa-solid fa-magnifying-glass search-icon"></i>
      <input type="text" placeholder="Search students, companies, records..." id="globalSearchInput" name="global_search" autocomplete="off" aria-label="Search students, companies, records">
    </div>
  </div>

  <!-- Header Right Actions -->
  <div class="header-actions">
    <!-- PWA Installation Button (Android & Windows) -->
    <button class="btn-pwa-install d-none me-1" id="pwaHeaderInstallBtn" title="Install Placement Pro on Android or Windows" aria-label="Install Application">
      <i class="fa-solid fa-download"></i> <span class="d-none d-sm-inline">Install App</span>
    </button>

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

  const menuToggle = document.getElementById('mobileMenuToggle');
  const sidebar = document.getElementById('sidebar');
  if (menuToggle && sidebar) {
    menuToggle.addEventListener('click', () => {
      sidebar.classList.toggle('show-mobile');
    });
    document.addEventListener('click', (e) => {
      if (sidebar.classList.contains('show-mobile') && !sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
        sidebar.classList.remove('show-mobile');
      }
    });
  }

  async function loadNotifications() {
    try {
      if (typeof API === 'undefined') return;
      const data = await API.get('/notifications');
      const list = (data && Array.isArray(data.notifications)) ? data.notifications : (Array.isArray(data) ? data : []);
      const unread = (data && typeof data.unread_count === 'number') ? data.unread_count : 0;

      if (badge) {
        if (unread > 0) {
          badge.classList.remove('d-none');
        } else {
          badge.classList.add('d-none');
        }
      }

      if (container) {
        if (list.length === 0) {
          container.innerHTML = `<li class="p-3 text-center text-muted small">No notifications</li>`;
        } else {
          container.innerHTML = list.map(n => {
            const timeStr = n.created_at ? new Date(n.created_at).toLocaleTimeString(undefined, {hour: '2-digit', minute:'2-digit'}) : '';
            const dateStr = n.created_at ? new Date(n.created_at).toLocaleDateString(undefined, {month: 'short', day: 'numeric'}) : '';
            
            let icon = '<i class="fa-solid fa-circle-info text-info"></i>';
            if (n.type === 'success') icon = '<i class="fa-solid fa-circle-check text-success"></i>';
            else if (n.type === 'warning') icon = '<i class="fa-solid fa-circle-exclamation text-warning"></i>';
            else if (n.type === 'danger') icon = '<i class="fa-solid fa-triangle-exclamation text-danger"></i>';
            
            const bgClass = n.is_read ? '' : 'bg-light';
            
            return `
              <li class="p-3 border-bottom ${bgClass} d-flex align-items-start gap-3">
                <div style="font-size: 1.15rem; margin-top: 0.15rem;">${icon}</div>
                <div class="flex-grow-1">
                  <div class="font-weight-700 text-dark small" style="line-height: 1.25;">${n.title || 'Notification'}</div>
                  <div class="text-muted small mt-1" style="font-size: 0.775rem; line-height: 1.35;">${n.message || ''}</div>
                  <div class="text-muted mt-1" style="font-size: 0.7rem;">${dateStr} ${timeStr ? 'at ' + timeStr : ''}</div>
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

// --- Global Theme & Mode Initializer ---
(function() {
  function applyTheme(theme) {
    let effective = theme;
    if (theme === 'system' || !theme) {
      effective = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    document.documentElement.setAttribute('data-theme', effective);
    if (document.body) document.body.setAttribute('data-theme', effective);
  }
  const savedTheme = localStorage.getItem('app_theme') || 'system';
  applyTheme(savedTheme);

  if (window.matchMedia) {
    try {
      window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        const current = localStorage.getItem('app_theme') || 'system';
        if (current === 'system') applyTheme('system');
      });
    } catch(e) {}
  }
  window.applyAppTheme = applyTheme;
})();

// --- PWA Installation Logic (Android & Windows) ---
(function() {
  const manifestLink = document.createElement('link');
  manifestLink.rel = 'manifest';
  manifestLink.href = 'manifest.json';
  document.head.appendChild(manifestLink);

  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('service-worker.js')
        .then(reg => console.log('PWA Service Worker active', reg))
        .catch(err => console.info('Service Worker note:', err.message));
    });
  }

  let deferredPrompt = null;
  const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
  const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

  window.addEventListener('DOMContentLoaded', () => {
    const headerBtn = document.getElementById('pwaHeaderInstallBtn');
    const pwaBanner = document.createElement('div');
    pwaBanner.id = 'pwaInstallBanner';
    pwaBanner.className = 'fixed-bottom bg-primary text-white p-3 shadow d-none d-flex justify-content-between align-items-center';
    pwaBanner.style.zIndex = '9999';
    pwaBanner.innerHTML = `
      <div class="d-flex align-items-center gap-3">
        <i class="fa-solid fa-cloud-arrow-down" style="font-size: 1.75rem;"></i>
        <div>
          <strong class="d-block" style="font-size: 0.95rem;">Install Placement Pro App</strong>
          <p class="mb-0 small text-white-50" id="pwaInstruction">Install as a desktop or mobile application for faster access.</p>
        </div>
      </div>
      <div class="d-flex align-items-center gap-2">
        <button id="pwaInstallBtn" class="btn btn-light btn-sm font-weight-bold px-3 py-1.5"><i class="fa-solid fa-download me-1"></i> Install</button>
        <button id="pwaDismissBtn" class="btn btn-outline-light btn-sm"><i class="fa-solid fa-xmark"></i></button>
      </div>
    `;
    document.body.appendChild(pwaBanner);

    const btnInstall = document.getElementById('pwaInstallBtn');
    const btnDismiss = document.getElementById('pwaDismissBtn');

    if (btnDismiss) {
      btnDismiss.addEventListener('click', () => {
        pwaBanner.classList.add('d-none');
        localStorage.setItem('pwaDismissed', 'true');
      });
    }

    async function triggerInstall() {
      if (deferredPrompt) {
        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        if (outcome === 'accepted') {
          pwaBanner.classList.add('d-none');
          if (headerBtn) headerBtn.classList.add('d-none');
        }
        deferredPrompt = null;
      } else if (isIOS) {
        alert('To install Placement Pro on iOS: Tap the Share button in Safari, then select "Add to Home Screen".');
      } else {
        alert('To install Placement Pro: Click the install icon in your browser address bar (top-right) or use Chrome/Edge Menu > Install Placement Pro.');
      }
    }

    if (btnInstall) btnInstall.addEventListener('click', triggerInstall);
    if (headerBtn) headerBtn.addEventListener('click', triggerInstall);

    if (!isStandalone) {
      if (isIOS) {
        if (headerBtn) headerBtn.classList.remove('d-none');
        if (localStorage.getItem('pwaDismissed') !== 'true') {
          pwaBanner.classList.remove('d-none');
          if (btnInstall) btnInstall.innerHTML = '<i class="fa-solid fa-info me-1"></i> How to Install';
          document.getElementById('pwaInstruction').innerText = 'Tap Share button in Safari, then "Add to Home Screen".';
        }
      } else {
        window.addEventListener('beforeinstallprompt', (e) => {
          e.preventDefault();
          deferredPrompt = e;
          if (headerBtn) headerBtn.classList.remove('d-none');
          if (localStorage.getItem('pwaDismissed') !== 'true') {
            pwaBanner.classList.remove('d-none');
          }
        });
      }
    }
  });
})();
</script>
