//BEGIN Automated edits. These will be automatically overwritten.
var CACHE_NAME = 'nebula-nebula-child-52197';
var OFFLINE_URL = 'https://gearside.com/nebula/offline/';
var OFFLINE_IMG = 'https://gearside.com/nebula/wp-content/themes/Nebula-master/assets/img/offline.jpg'; //@todo: Make an SVG for this image
var META_ICON = 'https://gearside.com/nebula/wp-content/themes/Nebula-master/assets/img/meta/android-chrome-512x512.png'; //@todo: this needs to be the child theme. Might need to write this one with PHP (or cache it on window load in main.js)
var MANIFEST = 'https://gearside.com/nebula/wp-content/themes/Nebula-master/inc/manifest.json';
var HOME_URL = 'https://gearside.com/nebula/';
var START_URL = 'https://gearside.com/nebula/?utm_source=homescreen'; //Mobile homescreen start_url
//END Automated edits

var CACHE_FILES = [
	OFFLINE_URL,
	OFFLINE_IMG,
	META_ICON,
	MANIFEST,
	START_URL,
	HOME_URL,
];

//@todo: How do other people deal with trailing slashes?

//Install
self.addEventListener('install', function(event){
	//console.log('[SW] Using the cache', CACHE_NAME);

	event.waitUntil(
		caches.open(CACHE_NAME).then(function(cache){
			//Map the files and cache them each individually. If any file fails, it doesn't affect the rest.
			Promise.all(CACHE_FILES.map(function(url){
				cache.add(url);
			}));

		}).then(function(){
			self.skipWaiting(); //Activate worker immediately (Warning: older sw versions may be running on other tabs at the same time).
		})
	);
});

//Activate
self.addEventListener('activate', function(event){
	//@todo: clean up cache here, too (no /wp-admin, no query strings except homescreen)

	event.waitUntil(
		caches.keys().then(function(keyList){
			Promise.all(keyList.map(function(key){ //Run everything in parallel using Promise.all()
				//If key doesn't matches with present key
				if ( key !== CACHE_NAME ){
					//console.log('[SW] Deleting old cache ', key);
					return caches.delete(key);
				}
			}));
		}).then(function(){
			self.clients.claim(); //Become available to all pages.
		})
	);
});


//Fetch
self.addEventListener('fetch', function(event){
	//Force network fetch for certain requests
	if ( needNetworkRetrieval(event.request.url) ){
    	//console.log('[SW] Forcing network retrieval for', event.request.url);

    	event.respondWith(
    		fetch(event.request).catch(function(){
	    		//We are offline... Look for analytics. @TODO: This will be exactly the same as below, so turn the offline stuff into a function and call it here too.
	    		//console.log('[SW] Offline for the forced network retrieval', event.request.url);
	    		return;
    		})
    	);

    	return;
	}

	event.respondWith(
		caches.open(CACHE_NAME).then(function(cache){
			return cache.match(event.request).then(function(response){
				if ( response ){
					// ******************
					// The resource exists in the cache
					// ******************

					//console.log('[SW] Responding from the cache for', event.request.url);

					//Stale-while-revalidate (respond from cache then update cache from the network afterwords)
					var fetchPromise = fetch(event.request).then(function(networkResponse){
						//console.log('[SW] Fetch complete for updating the cache for', event.request.url);
						cache.put(event.request, networkResponse.clone());
						return networkResponse;
					}).catch(function(){
						//The fetch failed (maybe we're offline). NBD since we responded with the cached resource anyway. We'll get 'em next time.
						//console.log('[SW] The fetch failed for stale-while-revalidate. Maybe we are offline or something. whatever.');
						return response;
					});

					return response || fetchPromise; //Return cache (response) or network response (fetchPromise)
				} else {
					// ******************
					// The resource does not exist in the cache, need to request it from the network
					// ******************

					//console.log('[SW] This resource does not exist in the cache', event.request.url);

					return fetch(event.request).then(function(networkResponse){
						//console.log('[SW] Got it from the network. Now putting it in the cache.', event.request.url);
						cache.put(event.request, networkResponse.clone()); //Respond from the network and then update the cache for next time.
						return networkResponse;
					}).catch(function(){
						// ******************
						// The fetch failed. Indicates the network being offline.
						// ******************

						//console.log('[SW] We are offline. Cannot retrieve', event.request.url);

						if ( event.request.mode === 'navigate' ){ //If the resource is an HTML page
							return cache.match(OFFLINE_URL); //Fallback to the offline page
						} else { //The resource is not an HTML page
							//Fallback logic for non-HTML requests

							if ( /google-analytics\.com\/collect$/i.test(event.request.url) ){ //@todo: double check this regex (or find a better way)
								//console.log('[SW] Analytics request! Store it in the outbox until we have connection again!');
								//@TODO: If the fetch is for google analytics, add the request to the background sync outbox and send it when connection is available again: https://github.com/WICG/BackgroundSync/blob/master/explainer.md
							}

							//If it is an image format requested, respond with the offline image.
							if ( /\.(svg|png|jpe?g|gif)$/i.test(event.request.url) ){ //Is there a better way to detect this than regex?
								return cache.match(OFFLINE_IMG);
							}

							return;
						}
					});
				}
			})
		})
	);
});

//Prevent caching certain resources
function needNetworkRetrieval(url){
	var substrings = ['chrome-extension://', '/wp-login.php', '/wp-admin', 'analytics', 'collect'];
	var length = substrings.length;

	//Force network retrieval for any resource that contains the above strings
	while ( length-- ){
		if ( url.indexOf(substrings[length]) !== -1 ){
			return true; //Yes, need network retrieval
		}
	}

	//Force network retrieval for all resources with query strings except start_url
	if ( url.indexOf('?') !== -1 ){
		if ( url.indexOf('?utm_source=homescreen') !== -1 || url.indexOf('fontawesome-webfont') !== -1 ){ //Allow start_url and Font Awesome woff to be cached
			return false; //No, do not need network retrieval (allow cache)
		}

		return true; //Yes, need network retrieval
	}

	return false; //No, do not need network retrieval (allow cache)
}



//Tell all clients we are offline
/*
function networkPostMessage(availability='online'){
	console.log('[SW] We are ' + availability + '. Sending message to client...');

	self.clients.matchAll().then(function(clientList){
		clientList.forEach(function(client){
			client.postMessage({message: '[SW] You are currently ' + availability + '!'});
		});
	});
}
*/



//Listen for message events from the client
self.addEventListener('message', function(event){
	//console.log('[SW] "message" event triggered: ' + event.data);
	//console.debug(event);

	//navigator.serviceWorker.controller.postMessage("Client 1 says '" + event.data + "'"); //controller is undefined here

	//event.ports[0].postMessage('SW Says Hello back!');
});








//Push notifications
//Still need to do the main.js part: https://serviceworke.rs/push-rich_index_doc.html
self.addEventListener('push', function(event){
	event.waitUntil(
		self.registration.showNotification('Nebula', {
			lang: 'en',
			body: 'This is a test',
			icon: META_ICON,
			vibrate: [500, 100, 500],
		})
	);
});







