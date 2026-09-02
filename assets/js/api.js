// api.js — thin wrapper around the Flask REST API with static preview fallback
var API = window.API || {
  base: window.API_BASE || 'http://localhost:5500/api',
  token: window.API_TOKEN || null,

  async request(path, { method = 'GET', body = null, isForm = false } = {}) {
    const headers = {};
    if (this.token) headers['Authorization'] = `Bearer ${this.token}`;
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
      console.warn('API fetch warning:', err.message);
      if (path.includes('/dashboard/stats')) {
        return {
          total_students: 120,
          placed_students: 95,
          companies_visited: 28,
          average_package: 14.5,
          highest_package: 42.0,
          placement_rate: 79.1
        };
      }
      if (path.includes('/companies')) {
        return [
          { id: 1, name: 'Google', visit_date: '2026-09-15', package_offered: 28.5, status: 'Upcoming' },
          { id: 2, name: 'Microsoft', visit_date: '2026-09-20', package_offered: 26.0, status: 'Upcoming' }
        ];
      }
      throw err;
    }
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

function showToast(message, type = 'success') {
  const el = document.createElement('div');
  el.className = `toast align-items-center text-white bg-${type} border-0 position-fixed bottom-0 end-0 m-3`;
  el.style.zIndex = 2000;
  el.innerHTML = `<div class="d-flex"><div class="toast-body">${message}</div>
    <button class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
  document.body.appendChild(el);
  const toast = new bootstrap.Toast(el, { delay: 4000 });
  toast.show();
  el.addEventListener('hidden.bs.toast', () => el.remove());
}
