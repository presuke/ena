const CACHE_NAME = 'enasave-cache-v1';
const urlsToCache = [
  '/',
  '/image/icons/64.png',
  '/image/icons/128.png',
  '/image/icons/192.png',
  '/image/icons/256.png',
  '/image/icons/512.png'
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
