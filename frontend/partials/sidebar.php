<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside id="sidebar">
  <div>
    <!-- Logo Area -->
    <div class="sidebar-logo">
      <div class="brand-title">
        <i class="fa-solid fa-graduation-cap text-primary" style="color: var(--pp-primary) !important;"></i>
        Placement Pro
      </div>
      <div class="brand-subtitle">Admin Portal</div>
    </div>

    <!-- Navigation Links -->
    <ul class="sidebar-nav">
      <li>
        <a href="dashboard.php" class="nav-item-link <?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>">
          <i class="fa-solid fa-chart-pie"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <li>
        <a href="students.php" class="nav-item-link <?php echo ($currentPage == 'students.php') ? 'active' : ''; ?>">
          <i class="fa-solid fa-user-graduate"></i>
          <span>Students</span>
        </a>
      </li>
      <li>
        <a href="companies.php" class="nav-item-link <?php echo ($currentPage == 'companies.php') ? 'active' : ''; ?>">
          <i class="fa-solid fa-building"></i>
          <span>Companies</span>
        </a>
      </li>
      <li>
        <a href="push.php" class="nav-item-link <?php echo ($currentPage == 'push.php') ? 'active' : ''; ?>">
          <i class="fa-solid fa-paper-plane"></i>
          <span>Push to Company</span>
        </a>
      </li>
      <li>
        <a href="import.php" class="nav-item-link <?php echo ($currentPage == 'import.php') ? 'active' : ''; ?>">
          <i class="fa-solid fa-cloud-arrow-up"></i>
          <span>Import</span>
        </a>
      </li>
      <li>
        <a href="sections.php" class="nav-item-link <?php echo ($currentPage == 'sections.php') ? 'active' : ''; ?>">
          <i class="fa-solid fa-layer-group"></i>
          <span>Sections</span>
        </a>
      </li>
      <li>
        <a href="reports.php" class="nav-item-link <?php echo ($currentPage == 'reports.php') ? 'active' : ''; ?>">
          <i class="fa-solid fa-chart-line"></i>
          <span>Reports</span>
        </a>
      </li>
      <li>
        <a href="skill_gap.php" class="nav-item-link <?php echo ($currentPage == 'skill_gap.php') ? 'active' : ''; ?>">
          <i class="fa-solid fa-magnifying-glass-chart"></i>
          <span>Skill Gap</span>
        </a>
      </li>
      <li>
        <a href="ai_hub.php" class="nav-item-link <?php echo ($currentPage == 'ai_hub.php') ? 'active' : ''; ?>">
          <i class="fa-solid fa-robot"></i>
          <span>AI Hub</span>
        </a>
      </li>
      <li>
        <a href="settings.php" class="nav-item-link <?php echo ($currentPage == 'settings.php') ? 'active' : ''; ?>">
          <i class="fa-solid fa-gear"></i>
          <span>Settings</span>
        </a>
      </li>
    </ul>
  </div>

  <!-- Sidebar Footer CTA -->
  <div class="sidebar-footer">
    <button class="btn-post-job" data-bs-toggle="modal" data-bs-target="#postJobModal">
      <i class="fa-solid fa-plus"></i>
      <span>+ Post New Job</span>
    </button>
  </div>
</aside>
