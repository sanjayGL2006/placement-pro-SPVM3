// auth.js — shared authentication & base configuration script for Placement Pro
(function() {
  var host = window.location.hostname;
  window.API_BASE = (host === 'localhost' || host === '127.0.0.1')
    ? `${window.location.protocol}//${host}:5500/api`
    : null;
  window.API_TOKEN = localStorage.getItem('token') || 'demo_admin_token';
})();
