// Basic Service Worker for Placement Pro
const CACHE_NAME = 'placement-pro-cache-v7';

// Force immediate activation — replace old broken service worker
self.addEventListener('install', event => {
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  // Claim all clients immediately and delete old caches
  event.waitUntil(
    caches.keys().then(names =>
      Promise.all(
        names.filter(n => n !== CACHE_NAME).map(n => caches.delete(n))
      )
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', event => {
  // Only intercept GET requests
  if (event.request.method !== 'GET') {
    return;
  }

  // Don't intercept navigation requests — let them go to the server
  if (event.request.mode === 'navigate') {
    return;
  }

  let url;
  try {
    url = new URL(event.request.url);
  } catch (err) {
    return;
  }

  // Ignore chrome-extension://, moz-extension://, blob:, data:, etc.
  if (url.protocol !== 'http:' && url.protocol !== 'https:') {
    return;
  }

  // Do not cache API requests
  if (url.pathname.includes('/api/')) {
    return;
  }

  // Only cache static assets (CSS, JS, images, fonts)
  const isStaticAsset = /\.(css|js|png|jpg|jpeg|gif|svg|woff2?|ttf|eot|ico)$/i.test(url.pathname);
  if (!isStaticAsset) {
    return;
  }

  // Network-first for static assets
  event.respondWith(
    fetch(event.request)
      .then(response => {
        if (response && response.ok && (response.type === 'basic' || response.type === 'cors')) {
          const clone = response.clone();
          caches.open(CACHE_NAME).then(cache => {
            cache.put(event.request, clone).catch(err => {
              // Ignore any unsupported put operations silently
            });
          }).catch(() => {});
        }
        return response;
      })
      .catch(() => caches.match(event.request))
  );
});
