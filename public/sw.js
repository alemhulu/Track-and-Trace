// Service worker for the offline field app. Only the field page and static assets are
// cached; every other page of the system still needs a connection.
const CACHE = 'tt-field-v1';
const FIELD_PAGE = '/field';
const STATIC = ['/js/field-app.js', '/js/qr-scanner.js', '/js/vendor/html5-qrcode.min.js', '/manifest.webmanifest', '/icons/icon-192.png'];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(CACHE).then((cache) => Promise.all(STATIC.map((url) => cache.add(url).catch(() => null)))));
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(caches.keys()
        .then((keys) => Promise.all(keys.filter((key) => key !== CACHE).map((key) => caches.delete(key))))
        .then(() => self.clients.claim()));
});

const isStatic = (url) => /^\/(build|js|css|webfonts|icons)\//.test(url.pathname) || url.pathname === '/manifest.webmanifest';

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);
    if (request.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }

    // The field page: always the newest copy when online, the saved copy when not.
    // A redirect (to the sign-in page) is never saved in place of the app.
    if (request.mode === 'navigate' && url.pathname === FIELD_PAGE) {
        event.respondWith(fetch(request)
            .then((response) => {
                if (response.ok && !response.redirected) {
                    const copy = response.clone();
                    caches.open(CACHE).then((cache) => cache.put(FIELD_PAGE, copy));
                }
                return response;
            })
            .catch(() => caches.match(FIELD_PAGE).then((cached) => cached || new Response(
                '<p style="font-family:sans-serif;padding:16px">Open the Field App once while online to use it offline.</p>',
                { headers: { 'Content-Type': 'text/html' } }
            ))));
        return;
    }

    // Scripts, styles and fonts: saved copy first, refreshed in the background
    if (isStatic(url)) {
        event.respondWith(caches.open(CACHE).then((cache) => cache.match(request).then((cached) => {
            const fresh = fetch(request).then((response) => {
                if (response.ok) {
                    cache.put(request, response.clone());
                }
                return response;
            }).catch(() => cached);
            return cached || fresh;
        })));
    }
});
