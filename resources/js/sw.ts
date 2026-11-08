/// <reference lib="webworker" />
import { cleanupOutdatedCaches, precacheAndRoute } from "workbox-precaching"
import { registerRoute } from "workbox-routing"
import { CacheFirst, NetworkFirst } from "workbox-strategies"
import { ExpirationPlugin } from "workbox-expiration"

declare const self: ServiceWorkerGlobalScope

// ─── Precache Vite build assets ───────────────────────────────────────────────
// The build manifest is injected here by vite-plugin-pwa at build time.
precacheAndRoute(self.__WB_MANIFEST)
cleanupOutdatedCaches()

// Wait for the "Refresh" click on the new version toast in app.tsx instead of
// activating immediately, so a deploy doesn't disrupt an open session.
self.addEventListener("message", (event) => {
	if (event.data?.type === "SKIP_WAITING") {
		self.skipWaiting()
	}
})

// Drops the caches of the hand-written service worker this one replaced
// ("tree-black-v1"), and cached pages, which link the previous build's assets
// that this version's precache no longer holds.
self.addEventListener("activate", (event) => {
	event.waitUntil(
		caches
			.keys()
			.then((keys) =>
				Promise.all(
					keys
						.filter((key) => key.startsWith("tree-black-") || key === "navigation")
						.map((key) => caches.delete(key))
				)
			)
			.then(() => self.clients.claim())
	)
})

// ─── Caching strategies ───────────────────────────────────────────────────────
// API, XHR and Inertia visits match no route, so they always go to the network.

// Favicons and PWA icons: cache-first, valid for 30 days.
registerRoute(
	({ url }) =>
		url.origin === self.location.origin &&
		/\.(png|ico|svg)$/.test(url.pathname),
	new CacheFirst({
		cacheName: "static-icons",
		plugins: [
			new ExpirationPlugin({
				maxEntries: 20,
				maxAgeSeconds: 30 * 24 * 60 * 60,
			}),
		],
	})
)

// Web app manifest: network-first, so a rename or new icon shows up on the
// next visit.
registerRoute(
	({ url }) =>
		url.origin === self.location.origin &&
		url.pathname.endsWith(".webmanifest"),
	new NetworkFirst({ cacheName: "webmanifest" })
)

// ─── Page navigations ─────────────────────────────────────────────────────────
// Each page is rendered by Inertia on the server, so pages are cached per URL
// and kept for a month so a visited page still opens while offline. After the
// phone has been idle the first request often stalls instead of failing,
// which left a blank screen until a reload, so the cached page is used
// whenever the network hasn't answered within 3 seconds.
registerRoute(
	({ request }) => request.mode === "navigate",
	new NetworkFirst({
		cacheName: "navigation",
		networkTimeoutSeconds: 3,
		plugins: [
			new ExpirationPlugin({ maxEntries: 10, maxAgeSeconds: 30 * 24 * 60 * 60 }),
		],
	})
)
