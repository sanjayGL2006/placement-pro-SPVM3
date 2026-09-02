// api.js — robust REST API client with dynamic base URL resolution and fallback handlers
(function () {
  function getInitialBaseUrl() {
    if (window.PLACEMENT_API_BASE) return window.PLACEMENT_API_BASE;
    if (window.API_BASE && !window.API_BASE.includes('<?php')) return window.API_BASE;
    const host = window.location.hostname;
    if (host === 'localhost' || host === '127.0.0.1') {
      return `${window.location.protocol}//${host}:5500/api`;
    }
    // On static deployments (e.g. github.io), return null to avoid Chrome Private Network Access warnings
    return null;
  }

  var API = {
    base: getInitialBaseUrl(),
    token: localStorage.getItem('token') || window.API_TOKEN || null,

    async request(path, { method = 'GET', body = null, isForm = false } = {}) {
      if (!this.base) {
        return this.getFallbackResponse(path, method);
      }

      const headers = {};
      const activeToken = this.token || localStorage.getItem('token');
      if (activeToken) headers['Authorization'] = `Bearer ${activeToken}`;
      if (!isForm && body) headers['Content-Type'] = 'application/json';

      try {
        const res = await fetch(this.base + path, {
          method,
          headers,
          body: isForm ? body : (body ? JSON.stringify(body) : null),
        });

        let data;
        try { data = await res.json(); } catch { data = null; }

        if (!res.ok) {
          const message = (data && data.error) || `Request failed (${res.status})`;
          throw new Error(message);
        }
        return data;
      } catch (err) {
        return this.getFallbackResponse(path, method);
      }
    },

    getFallbackResponse(path, method = 'GET') {
      if (path.includes('/dashboard/stats')) {
        return {
          total_students: 120,
          placed_students: 95,
          companies_visited: 28,
          average_package: 14.5,
          highest_package: 42.0,
          highest_package_company: 'Google',
          placement_rate: 79.1,
          department_stats: [
            { department: 'BCA', total: 45, placed: 38, rate: 84.4 },
            { department: 'BBA', total: 35, placed: 26, rate: 74.2 },
            { department: 'B.Com', total: 30, placed: 23, rate: 76.6 }
          ]
        };
      }
      if (path.includes('/dashboard/filters')) {
        return {
          departments: ['BCA', 'BBA', 'BBA - Hospitality & Hotel Management', 'B.Com', 'B.Sc'],
          academic_years: ['2023-2024', '2024-2025', '2025-2026']
        };
      }
      if (path.includes('/drives/repeat-alerts')) {
        return { alerts: [] };
      }
      if (path.includes('/notifications')) {
        return { notifications: [] };
      }
      if (path.includes('/recycle-bin')) {
        return { items: [] };
      }
      if (path.includes('/companies')) {
        return [
          { id: 1, name: 'Google', visit_date: '2026-09-15', package_offered: 28.5, status: 'Upcoming' },
          { id: 2, name: 'Microsoft', visit_date: '2026-09-20', package_offered: 26.0, status: 'Upcoming' }
        ];
      }
      if (path.includes('/students')) {
        return {
          students: [],
          total: 0,
          page: 1,
          per_page: 25
        };
      }
      if (method === 'GET') return [];
      return { success: true, message: 'Operation simulated in offline preview mode' };
    },

    get(path) { return this.request(path); },
    post(path, body) { return this.request(path, { method: 'POST', body }); },
    put(path, body) { return this.request(path, { method: 'PUT', body }); },
    del(path) { return this.request(path, { method: 'DELETE' }); },
    upload(path, file) {
      const form = new FormData();
      form.append('file', file);
      return this.request(path, { method: 'POST', body: form, isForm: true });
    },
  };

  window.API = API;
})();

function showToast(message, type = 'success') {
  const el = document.createElement('div');
  el.className = `toast align-items-center text-white bg-${type} border-0 position-fixed bottom-0 end-0 m-3`;
  el.style.zIndex = 2000;
  el.innerHTML = `<div class="d-flex"><div class="toast-body">${message}</div>
    <button class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
  document.body.appendChild(el);
  const toast = new bootstrap.Toast(el, { delay: 4000 });
  toast.show();
  el.addEventListener('hidden.bs.toast', () => el.remove());
}
