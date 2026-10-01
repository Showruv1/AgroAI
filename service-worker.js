const CACHE_NAME = "agroai-shell-v1";
const APP_ROOT = new URL("./", self.location);
const ROOT_URL = new URL("./", APP_ROOT).href;
const INDEX_URL = new URL("./index.html", APP_ROOT).href;
const SHELL_URLS = [
    ROOT_URL,
    INDEX_URL,
    new URL("./style.css", APP_ROOT).href,
    new URL("./manifest.json", APP_ROOT).href,
    new URL("./assets/agroai-logo.jpeg", APP_ROOT).href
];
const SHELL_URL_SET = new Set(SHELL_URLS);

self.addEventListener("install", event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(SHELL_URLS))
    );
});

self.addEventListener("activate", event => {
    event.waitUntil(
        caches.keys()
            .then(cacheNames => Promise.all(
                cacheNames
                    .filter(name => name.startsWith("agroai-shell-") && name !== CACHE_NAME)
                    .map(name => caches.delete(name))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener("fetch", event => {
    const request = event.request;
    const requestUrl = new URL(request.url);

    if (request.method !== "GET" || requestUrl.origin !== self.location.origin) {
        return;
    }

    const isAppShellNavigation = request.mode === "navigate" &&
        (requestUrl.pathname === new URL(ROOT_URL).pathname ||
            requestUrl.pathname === new URL(INDEX_URL).pathname);

    if (isAppShellNavigation) {
        event.respondWith(
            fetch(request)
                .then(response => {
                    if (response.ok) {
                        const responseUrl = requestUrl.pathname.endsWith("index.html")
                            ? INDEX_URL
                            : ROOT_URL;
                        caches.open(CACHE_NAME)
                            .then(cache => cache.put(responseUrl, response.clone()));
                    }
                    return response;
                })
                .catch(async () => {
                    return (await caches.match(request)) || caches.match(ROOT_URL);
                })
        );
        return;
    }

    if (SHELL_URL_SET.has(request.url)) {
        event.respondWith(
            caches.match(request).then(cachedResponse => cachedResponse || fetch(request))
        );
    }
});
