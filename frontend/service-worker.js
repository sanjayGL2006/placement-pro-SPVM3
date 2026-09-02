// Basic Service Worker for Placement Pro
const CACHE_NAME = 'placement-pro-cache-v2';
const urlsToCache = [
  '/assets/css/style.css',
  '/assets/js/api.js'
];

// Force immediate activation — replace old broken service worker
self.addEventListener('install', event => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return Promise.allSettled(
          urlsToCache.map(url => cache.add(url).catch(() => {
            console.warn('SW: failed to cache', url);
          }))
        );
      })
  );
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
  // Don't intercept navigation requests (PHP pages) — let them go to the server
  if (event.request.mode === 'navigate') {
    return;
  }

  // Only cache static assets (CSS, JS, images, fonts)
  const url = new URL(event.request.url);
  const isStaticAsset = /\.(css|js|png|jpg|jpeg|gif|svg|woff2?|ttf|eot|ico)$/i.test(url.pathname);

  if (!isStaticAsset) {
    return;
  }

  // Network-first for static assets
  event.respondWith(
    fetch(event.request)
      .then(response => {
        // Cache successful responses
        if (response.ok) {
          const clone = response.clone();
          caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
        }
        return response;
      })
      .catch(() => caches.match(event.request))
  );
});
