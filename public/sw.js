const CACHE_NAME = 'enasave-cache-v1';
const urlsToCache = [
  '/',
  '/css/app.css',
  '/js/app.js',
  '/img/icons/64.png',
  '/img/icons/128.png',
  '/img/icons/192.png',
  '/img/icons/256.png',
  '/img/icons/512.png'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache);
      })
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        return response || fetch(event.request);
      })
  );
});
