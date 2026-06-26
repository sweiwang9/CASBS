/* Service worker for The Tyler Collection PWA.
   Bump SHELL_VERSION whenever index.html / books.json / three.min.js change
   so returning visitors get the new version instead of a stale cache. */
const SHELL_VERSION = 'tyler-shell-v4';
const COVERS_CACHE  = 'tyler-covers-v1';   // covers are immutable by filename

const SHELL = [
  './',
  'index.html',
  'books.json',
  'manifest.webmanifest',
  'assets/three.min.js',
  'assets/aerial.webp',
  'assets/icons/icon-192.png',
  'assets/icons/icon-512.png',
  'assets/icons/maskable-512.png',
  'assets/icons/apple-touch-icon.png'
];

self.addEventListener('install', e => {
  e.waitUntil(
    caches.open(SHELL_VERSION).then(c => c.addAll(SHELL)).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys().then(keys => Promise.all(
      keys.filter(k => k !== SHELL_VERSION && k !== COVERS_CACHE).map(k => caches.delete(k))
    )).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', e => {
  const req = e.request;
  if (req.method !== 'GET') return;
  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;  // let cross-origin pass through

  // Navigations: try network, fall back to the cached app shell (offline).
  if (req.mode === 'navigate') {
    e.respondWith(
      fetch(req).catch(() => caches.match('index.html'))
    );
    return;
  }

  // Cover scans: cache-first (they never change for a given filename).
  if (url.pathname.includes('/assets/covers/')) {
    e.respondWith(
      caches.open(COVERS_CACHE).then(c =>
        c.match(req).then(hit => hit || fetch(req).then(res => {
          if (res.ok) c.put(req, res.clone());
          return res;
        }).catch(() => hit))
      )
    );
    return;
  }

  // book data: network-first so edits show up, fall back to cache offline.
  if (url.pathname.endsWith('books.json')) {
    e.respondWith(
      fetch(req).then(res => {
        const copy = res.clone();
        caches.open(SHELL_VERSION).then(c => c.put('books.json', copy));
        return res;
      }).catch(() => caches.match('books.json'))
    );
    return;
  }

  // Everything else (shell assets): cache-first, fall back to network.
  e.respondWith(
    caches.match(req).then(hit => hit || fetch(req))
  );
});
