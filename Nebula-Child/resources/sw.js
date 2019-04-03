//BEGIN automated edits. These will be automatically overwritten.
var THEME_NAME = 'nebula-child';
var NEBULA_VERSION = 'v6.11.2.9671'; //Wednesday, April 3, 2019 8:48:39 AM
var OFFLINE_URL = 'https://gearside.com/nebula/offline/';
var OFFLINE_IMG = 'https://gearside.com/nebula/wp-content/themes/Nebula-master/assets/img/offline.svg';
var OFFLINE_GA_DIMENSION = 'cd2';
var META_ICON = 'https://gearside.com/nebula/wp-content/themes/Nebula-master/assets/img/meta/android-chrome-512x512.png';
var MANIFEST = 'https://gearside.com/nebula/wp-content/themes/Nebula-master/inc/manifest.json';
var HOME_URL = 'https://gearside.com/nebula/';
var START_URL = 'https://gearside.com/nebula/?utm_source=homescreen';
//END automated edits

importScripts('https://storage.googleapis.com/workbox-cdn/releases/4.1.1/workbox-sw.js'); //https://developers.google.com/web/tools/workbox/guides/get-started
workbox.setConfig({debug: false}); //https://developers.google.com/web/tools/workbox/guides/troubleshoot-and-debug

//@todo "Nebula" 0: If ?debug is present in the URL on load, dump the entire cache and unregister (or update) the SW completely

workbox.core.skipWaiting();
workbox.core.clientsClaim();
workbox.precaching.cleanupOutdatedCaches(); //This listens for "-precache-" in the cache name

//Cache names
workbox.core.setCacheNameDetails({
	prefix: THEME_NAME,
	suffix: NEBULA_VERSION,
	precache: 'precache',
	runtime: 'runtime',
	googleAnalytics: 'ga'
});

//Precache files on SW install
workbox.precaching.precacheAndRoute([
	OFFLINE_URL,
	OFFLINE_IMG,
	META_ICON,
	MANIFEST,
	//HOME_URL, //This is breaking the service worker AGAIN. Why does this problem keep happening after I think its fixed?????? //https://github.com/chrisblakley/Nebula/issues/1709
	START_URL
]);

//Do not cache certain requests
workbox.routing.registerRoute(
	needNetworkRetrieval, //Workbox RegExp() does not allow partial matches on cross-domain requests, so we'll use our own function instead.
	new workbox.strategies.NetworkOnly()
);

//Check if we need to force network retrieval for specific resources (true = network, false = allow caching)
function needNetworkRetrieval(event){
	if ( event.request.method !== 'GET' ){ //Prevent cache for POST and AJAX requests (Workbox may already handle this, but just to be safe)
		return true;
	}

	if ( /\/chrome-extension:\/\/|\/wp-login.php|\/wp-admin|analytics|hubspot|hs-scripts|customize.php|customize_|no-cache|admin-ajax|gutenberg\//.test(event.url.href) ){
		return true;
	}

	if ( /\.(?:pdf|docx?|xlsx?|pptx?|zipx?|rar|tar|txt|rtf|ics|vcard)/.test(event.url.href) ){
		return true;
	}

	return false;
}

//Data Feeds
workbox.routing.registerRoute(
	new RegExp('/\.(?:json|xml|yaml|csv)/'),
	new workbox.strategies.NetworkFirst()
);

//Images
workbox.routing.registerRoute(
	new RegExp('/\.(?:png|jpg|jpeg|svg|gif)/'),
	new workbox.strategies.StaleWhileRevalidate({
		cacheName: 'images', //This cache name is used for the offline fallback and expiration plugin
		plugins: [
			new workbox.expiration.Plugin({
				maxAgeSeconds: 7 * 24 * 60 * 60, //Cache for a maximum of a week
			})
		]
	})
);

//Everything Else
workbox.routing.setDefaultHandler(
	new workbox.strategies.StaleWhileRevalidate({
		cacheName: 'default', //Cache name is required for expiration plugin
		plugins: [
			new workbox.expiration.Plugin({
				maxAgeSeconds: 7 * 24 * 60 * 60, //Cache for a maximum of a week
			})
		]
	})
);

//Offline response
workbox.routing.setCatchHandler(function(params){
	if ( params.event.request.mode === 'navigate' ){
		return caches.match(workbox.precaching.getCacheKeyForURL(OFFLINE_URL));
	}

	if ( params.event.request.destination === 'image' ){
		return caches.match(workbox.precaching.getCacheKeyForURL(OFFLINE_IMG));
	}

	return Response.error(); //If we don't have a fallback, just return an error response.
});

//Offline Google Analytics: https://developers.google.com/web/tools/workbox/modules/workbox-google-analytics
workbox.googleAnalytics.initialize({
	parameterOverrides: {
		[OFFLINE_GA_DIMENSION]: 'offline', //Set a custom dimension to note offline hits (if set in Nebula Options)
	},
});