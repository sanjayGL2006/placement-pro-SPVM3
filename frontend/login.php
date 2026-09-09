<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Institutional Login — Placement Pro Portal</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="icon" type="image/svg+xml" href="favicon.svg">
  <link rel="alternate icon" href="favicon.ico">
  <link href="assets/css/style.css" rel="stylesheet">
  <style>
    .role-select-box {
      border: 1.5px solid #E2E8F0;
      border-radius: 12px;
      padding: 0.65rem 1rem;
      font-size: 0.9rem;
      transition: all 0.2s;
    }
    .role-select-box:focus {
      border-color: var(--pp-primary);
      box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
    }
    .access-code-badge {
      background-color: #FEF3C7;
      color: #92400E;
      font-size: 0.75rem;
      font-weight: 700;
      padding: 0.25rem 0.65rem;
      border-radius: 20px;
    }
  </style>
</head>
<body class="login-bg">

  <div class="auth-card" style="max-width: 440px;">
    <!-- Header -->
    <div class="text-center mb-4">
      <div class="d-inline-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px; background-color: var(--pp-primary-light); border-radius: 14px;">
        <i class="fa-solid fa-graduation-cap text-primary" style="font-size: 1.75rem; color: var(--pp-primary) !important;"></i>
      </div>
      <h2 class="h4 font-weight-800 text-dark mb-1">Placement Pro</h2>
      <p class="text-muted small mb-0">Institutional Administration & Campus Recruitment Portal</p>
      <div class="text-muted" style="font-size: 0.75rem; margin-top: 0.25rem;">PES Institute of Advanced Management Studies</div>
    </div>

    <div id="error" class="alert alert-danger d-none py-2 px-3 small rounded-3 mb-3"></div>
    <div id="info" class="alert alert-info d-none py-2 px-3 small rounded-3 mb-3"></div>

    <!-- Login Form -->
    <form id="loginForm">


      <!-- Email Input -->
      <div class="mb-3">
        <label for="email" class="form-label font-weight-600 text-uppercase text-muted" style="font-size: 0.725rem; letter-spacing: 0.05em;">INSTITUTIONAL EMAIL</label>
        <div class="position-relative">
          <i class="fa-regular fa-envelope position-absolute text-muted" style="left: 1rem; top: 50%; transform: translateY(-50%);"></i>
          <input type="email" class="form-control form-control-pp ps-5" id="email" name="email" autocomplete="username" placeholder="coordinator@pesiams.edu.in" required>
        </div>
      </div>

      <!-- Password Input -->
      <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <label for="password" class="form-label font-weight-600 text-uppercase text-muted mb-0" style="font-size: 0.725rem; letter-spacing: 0.05em;">PASSWORD</label>
          <a href="#" class="text-decoration-none small font-weight-600" style="color: var(--pp-primary); font-size: 0.75rem;" onclick="event.preventDefault(); alert('Please contact the Placement Cell Administrator to reset institutional credentials.');">Forgot Password?</a>
        </div>
        <div class="position-relative">
          <i class="fa-solid fa-lock position-absolute text-muted" style="left: 1rem; top: 50%; transform: translateY(-50%);"></i>
          <input type="password" class="form-control form-control-pp ps-5 pe-5" id="password" name="password" autocomplete="current-password" placeholder="••••••••••••" required>
          <i class="fa-regular fa-eye position-absolute text-muted cursor-pointer" id="togglePassword" style="right: 1rem; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
        </div>
      </div>

      <!-- Stay logged in -->
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" id="stayLoggedIn" name="stay_logged_in" checked>
        <label class="form-check-label text-muted small" for="stayLoggedIn">
          Stay logged in on this institutional device
        </label>
      </div>

      <!-- Submit CTA -->
      <button class="btn btn-pp-primary w-100 py-2.5 font-weight-600 justify-content-center mb-3" type="submit" id="btnSubmitLogin" style="font-size: 0.95rem;">
        <i class="fa-solid fa-arrow-right-to-bracket me-2"></i> Sign In to Portal
      </button>

      <!-- Institutional Security Notice -->
      <div class="p-2.5 rounded-3 bg-light text-center border">
        <div class="text-muted small" style="font-size: 0.75rem;">
          <i class="fa-solid fa-lock me-1 text-secondary"></i>
          Authorized PESIAMS Personnel Only. All logins & data changes are recorded in the institutional audit log.
        </div>
      </div>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Password toggle
    document.getElementById('togglePassword').addEventListener('click', function () {
      const passwordInput = document.getElementById('password');
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      this.classList.toggle('fa-eye');
      this.classList.toggle('fa-eye-slash');
    });

    // Standard Institutional Login Submission
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const errorBox = document.getElementById('error');
      errorBox.classList.add('d-none');
      const btn = document.getElementById('btnSubmitLogin');
      const originalHtml = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Verifying Credentials...';

      const emailVal = document.getElementById('email')?.value.trim() ?? '';
      const passwordVal = document.getElementById('password')?.value ?? '';
      const roleVal = document.getElementById('portalRole')?.value ?? 'coordinator';
      const accessCodeVal = document.getElementById('deptAccessCode')?.value.trim() ?? '';

      const apiBase = '<?php echo API_BASE; ?>' || 'http://localhost:5500/api';

      try {
        const res = await fetch(`${apiBase}/auth/login`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            email: emailVal,
            password: passwordVal,
            role: roleVal,
            access_code: accessCodeVal
          })
        });

        const data = await res.json();
        if (!res.ok) {
          throw new Error(data.error || 'Authentication failed. Please check credentials.');
        }

        // Store session in localStorage
        localStorage.setItem('token', data.token);
        localStorage.setItem('user', JSON.stringify(data.user));

        // Store session in PHP session_store.php
        await fetch('session_store.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data)
        });

        window.location.href = 'dashboard.php';
      } catch (err) {
        errorBox.textContent = err.message;
        errorBox.classList.remove('d-none');
      } finally {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
      }
    });
  </script>
</body>
</html>
