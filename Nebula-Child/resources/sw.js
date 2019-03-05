//BEGIN Automated edits. These will be automatically overwritten.
var THEME_NAME = 'nebula-child';
.5.6.7890'; //Saturday, March 2, 2019 10:29:08 AM
var OFFLINE_URL = 'https://gearside.com/nebula/offline/';
var OFFLINE_IMG = 'https://gearside.com/nebula/wp-content/themes/Nebula-master/assets/img/offline.svg';
var META_ICON = 'https://gearside.com/nebula/wp-content/themes/Nebula-master/assets/img/meta/android-chrome-512x512.png';
var MANIFEST = 'https://gearside.com/nebula/wp-content/themes/Nebula-master/inc/manifest.json';
var HOME_URL = 'https://gearside.com/nebula/';
var START_URL = 'https://gearside.com/nebula/?utm_source=homescreen';
//END Automated edits







importScripts('https://storage.googleapis.com/workbox-cdn/releases/4.0.0/workbox-sw.js');

if ( workbox ){
	console.log(`Yay! Workbox is loaded 🎉`);
} else {
	console.log(`Boo! Workbox didn't load 😬`);
}

//https://developers.google.com/web/tools/workbox/guides/troubleshoot-and-debug
workbox.setConfig({debug: true});

console.log('workbox: ', workbox);
console.log('core: ', workbox.core);
console.log('log levels: ', workbox.core.LOG_LEVELS);

//workbox.core.setLogLevel(workbox.core.LOG_LEVELS.debug); //Not working... (cannot read debug of undefined)

//Try this (untested yet):
//workbox.skipWaiting();
//workbox.clientsClaim();



//Cache names
/*
workbox.core.setCacheNameDetails({
	prefix: 'nebula',
	suffix: 'v6.7.8.9', //@todo "Nebula" 0: Make this update w/ Nebula version
	precache: 'custom-precache-name',
	runtime: 'custom-runtime-name'
});
*/




workbox.precaching.precacheAndRoute([
	OFFLINE_URL,
	OFFLINE_IMG,
	META_ICON,
	MANIFEST,
	HOME_URL,
	START_URL
]);






/*
	@todo "Nebula" 0: Need to figure out how nebula.js will get the cache name for the predictive cache...

	//This could work right from the nebula.js file: https://developers.google.com/web/tools/workbox/guides/advanced-recipes
	self.addEventListener('install', (event) => {
		const urls = ['one', 'two'];
		const cacheName = workbox.core.cacheNames.runtime;
		event.waitUntil(caches.open(cacheName).then((cache) => cache.addAll(urls)));
	});


workbox.precaching.precacheAndRoute([
	'/styles/index.0c9a31.css',
	'/scripts/main.0d5770.js',
	{url: '/index.html', revision: '383676'},
]);
*/



/*
	Cache notes:
	- NetworkFirst() and StaleWhileRevalidate() work with "opaque" responses, so maybe CDN requests can use those?
	- See warning at bottom of this page: https://developers.google.com/web/tools/workbox/guides/handle-third-party-requests
*/


/*
	Recipes:
	- https://developers.google.com/web/tools/workbox/guides/common-recipes
	- https://developers.google.com/web/tools/workbox/guides/advanced-recipes //See example for fallback responses for images
	- https://gist.github.com/addyosmani/0e1cfeeccad94edc2f0985a15adefe54 (note: v3 syntax here)
*/

//Do not cache these requests
workbox.routing.registerRoute(
	needNetworkRetrieval, //Workbox RegExp() does not allow partial matches on cross-domain requests, so we'll use our own function instead.
	new workbox.strategies.NetworkOnly()
);

function needNetworkRetrieval(event){
	if ( event.request.method !== 'GET' ){ //Prevent cache for POST and AJAX requests
		return true;
	}

	if ( /\/chrome-extension:\/\/|\/wp-login.php|\/wp-admin|analytics|hubspot|hs-scripts|customize.php|customize_|no-cache|admin-ajax|gutenberg\//.test(event.url.href) ){
		return true;
	}

	if ( /\.(?:pdf|docx?|xlsx?|pptx?|zipx?|rar|tar|txt|rtf|ics|vcard|json)/.test(event.url.href) ){
		return true;
	}

	return false;
}

//Images
workbox.routing.registerRoute(
	new RegExp('/\.(?:png|jpg|jpeg|svg|gif)/'),
	new workbox.strategies.StaleWhileRevalidate({
		cacheName: 'images',
		plugins: [
			new workbox.expiration.Plugin({
				maxAgeSeconds: 7 * 24 * 60 * 60, // Cache for a maximum of a week
			})
		]
	})
);

//Everything Else
workbox.routing.setDefaultHandler(
	new workbox.strategies.StaleWhileRevalidate({
		plugins: [
			new workbox.expiration.Plugin({
				maxAgeSeconds: 7 * 24 * 60 * 60, // Cache for a maximum of a week
			})
		]
	})
);

//Offline response
workbox.routing.setCatchHandler(function(event){
	// The FALLBACK_URL entries must be added to the cache ahead of time, either via runtime or precaching.
	// If they are precached, then call workbox.precaching.getCacheKeyForURL(FALLBACK_URL) to get the correct cache key to pass in to caches.match().
	// Use event, request, and url to figure out how to respond. One approach would be to use request.destination, see https://medium.com/dev-channel/service-worker-caching-strategies-based-on-request-types-57411dd7652c

	if ( event.request.mode === 'navigate' ){
		return caches.match('https://gearside.com/nebula/offline/');
	}

	if ( event.request.destination === 'image' ){
		return caches.match('https://gearside.com/nebula/wp-content/themes/Nebula-master/assets/img/offline.svg');
	}

	return Response.error(); //If we don't have a fallback, just return an error response.
});




//Offline Google Analytics: https://developers.google.com/web/tools/workbox/modules/workbox-google-analytics
//workbox.googleAnalytics.initialize();

