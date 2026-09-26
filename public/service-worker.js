self.addEventListener('install', (event) => {
    event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        (async () => {
            const keys = await caches.keys();
            await Promise.all(keys.map((key) => caches.delete(key)));
            await self.clients.claim();
        })()
    );
});

self.addEventListener('fetch', () => {
    // Intentionally no-op: this worker only clears old caches.
});
