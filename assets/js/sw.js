//BEGIN Automated edits. These will be automatically overwritten.
var CACHE_NAME = 'nebula-nebula-child-58093'; //Saturday, May 19, 2018 12:18:29 PM
var OFFLINE_URL = 'https://gearside.com/nebula/offline/';
var OFFLINE_IMG = 'https://gearside.com/nebula/wp-content/themes/Nebula-master/assets/img/offline.svg';
var META_ICON = 'https://gearside.com/nebula/wp-content/themes/Nebula-master/assets/img/meta/android-chrome-512x512.png';
var MANIFEST = 'https://gearside.com/nebula/wp-content/themes/Nebula-master/inc/manifest.json';
var HOME_URL = 'https://gearside.com/nebula/';
//END Automated edits

var CACHE_FILES = [
	OFFLINE_URL,
	OFFLINE_IMG,
	META_ICON,
	MANIFEST,
	HOME_URL,
];

//Install
self.addEventListener('install', function(event){
	//console.log('[SW] Inside Install event');
	//console.log('[SW] Using the cache', CACHE_NAME);

	event.waitUntil(
		caches.open(CACHE_NAME).then(function(cache){
			//Map the files and cache them each individually. If any file fails, it doesn't affect the rest.
			Promise.all(CACHE_FILES.map(function(url){
				cache.add(url);
			}));
		}).then(function(){
			//console.log('[SW] Skip waiting on install (activate immediately)');
			self.skipWaiting(); //Activate worker immediately (Warning: older sw versions may be running on other tabs at the same time).
		})
	);
});

//Activate
self.addEventListener('activate', function(event){
	//console.log('[SW] Inside Activate event');

	//@todo "Nebula" 0: clean up cache here, too (no /wp-admin, no query strings except homescreen)

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
			//console.log('[SW] Claiming clients (should be available to all pages now)');
			self.clients.claim(); //Become available to all pages.
		})
	);
});


//Fetch
self.addEventListener('fetch', function(event){
	var thisRequest = event.request; //Do not alter the event. //Breaks Font Awesome fonts (sometimes)
	//var thisRequest = new Request(event.request.url, {mode: 'cors'}); //Allow cross-origin requests //Breaks Google Analytics and Font Awesome fonts
	//var thisRequest = new Request(event.request.url, {mode: 'no-cors'}); //Disallow cross-origin requests //Breaks Font Awesome fonts (sometimes)

	//console.log('[SW] We got a fetch request (' + thisRequest.mode + ') for:', thisRequest.url);
	//console.log('[SW] Fetch request:', thisRequest);

	if ( needNetworkRetrieval(thisRequest) ){
		// ******************
		// Force network retrieval for certain requests
		// ******************

		//console.log('[SW] Forcing network retrieval by JUST IGNORING IT for', thisRequest.url);
		return false; //This isn't really by the book... I'm not in love with this.

    	//console.log('[SW] Forcing network retrieval (' + thisRequest.mode + ') for', thisRequest.url);
    	//console.debug(thisRequest);

    	event.respondWith(
    		fetch(thisRequest).catch(function(){
	    		//console.log('[SW] Offline for the forced network retrieval', thisRequest.url);

	    		// ******************
				// The fetch failed. Indicates the network being offline.
				// ******************

				return caches.open(CACHE_NAME).then(function(cache){ //Open the cache so we can respond with the offline resources if needed
					return offlineRequest(thisRequest, cache);
				});
    		})
    	);

    	return; //How do we "return false" inside of respondWith() when it expects a response object? Should I just fake an empty response object?
	} else {
		// ******************
		// Allow response from the cache (if available)
		// ******************

		event.respondWith(
			caches.open(CACHE_NAME).then(function(cache){
				return cache.match(thisRequest).then(function(response){
					if ( response ){
						// ******************
						// The resource exists in the cache
						// ******************

						//console.log('[SW] Responding from the cache for', thisRequest.url);

						//Stale-while-revalidate (respond from cache then update cache from the network afterwords)
						var fetchPromise = fetch(thisRequest).then(function(networkResponse){
							//console.log('[SW] Fetch complete for updating the cache for', thisRequest.url);
							cache.put(thisRequest, networkResponse.clone());
							return networkResponse;
						}).catch(function(){
							//The fetch failed (maybe we're offline). NBD since we responded with the cached resource anyway. We'll get 'em next time.
							//console.log('[SW] The fetch failed for stale-while-revalidate. Maybe we are offline or something. whatever.', thisRequest.url);
							return response;
						});

						return response || fetchPromise; //Return cache (response) or network response (fetchPromise)
					} else {
						// ******************
						// The resource does not exist in the cache, need to request it from the network
						// ******************

						//console.log('[SW] This resource does not exist in the cache', event.request.url);

						return fetch(thisRequest).then(function(networkResponse){
							//console.log('[SW] Got it from the network. Now putting it in the cache.', event.request.url);
							cache.put(thisRequest, networkResponse.clone()); //Respond from the network and then update the cache for next time.
							return networkResponse;
						}).catch(function(){
							// ******************
							// The fetch failed. Indicates the network being offline.
							// ******************

							return offlineRequest(thisRequest, cache);
						});
					}
				})
			})
		);
	}
});

//Prevent caching certain resources
function needNetworkRetrieval(request){
	if ( request.method !== 'GET' ){ //Prevent cache for POST and AJAX requests
		//console.log('[SW] Need network retreival because not a GET request for: ' + request.url);
		return true; //Yes, need network retreival
	}

	var substrings = ['chrome-extension://', '/wp-login.php', '/wp-admin', 'analytics', 'hubspot', 'hs-scripts', 'customize.php', 'customize_', 'no-cache', 'admin-ajax'];
	var length = substrings.length;

	//Force network retrieval for any resource that contains the above strings
	while ( length-- ){
		if ( request.url.indexOf(substrings[length]) !== -1 ){
			//console.log('[SW] Need network retreival because matches a string: ' + request.url);
			return true; //Yes, need network retrieval
		}
	}

	//Force network retrieval for all requests with query strings
	if ( request.url.indexOf('?') !== -1 ){
		if ( request.mode === 'navigate' || request.url.indexOf('?utm_') !== -1 || request.url.indexOf('fontawesome-webfont') !== -1 ){ //Allow Page requests, UTM parameters, and Font Awesome woff to be cached
			//console.log('[SW] Allow cached response for', request.url);
			return false; //No, do not need network retrieval (allow cache)
		}

		//console.log('[SW] Need network retreival because of query string: ' + request.url);
		return true; //Yes, need network retrieval
	}

	//Force network retrieval for HTML files older than 20 hours (this is to maintain fresh nonces) yolo
		//if file does not have an extension (or ends in HTML or PHP) and is older than 20 hours return true

	//console.log('[SW] Allow from cache for: ' + request.url);
	return false; //No, do not need network retrieval (allow cache)
}

//Offline request logic
function offlineRequest(request, cache){
	//console.log('[SW] We are offline (inside offline function). Cannot retrieve', event.request.url);

	if ( request.mode === 'navigate' ){ //If the resource is an HTML page
		//If the URL does not already end in a trailing slash check the cache for a match with one
		if ( request.url.slice(-1) !== '/' ){
			//console.log('[SW] This page request did not have a trailing slash. Checking the cache for one added.', event.request.url);

			return cache.match(request.url + '/').then(function(response){
				if ( response ){
					//console.log('[SW] Found a match with an added trailing slash. Responding with that from cache!', event.request.url);
					return response;
				} else {
					//console.log('[SW] Did not find a match with a trailing slash in the cache. Responding with offline URL.', event.request.url);
					return cache.match(OFFLINE_URL); //Fallback to the offline page
				}
			});
		} else {
			//console.log('[SW] Simply falling back to offline page.', event.request.url);
			return cache.match(OFFLINE_URL); //Fallback to the offline page
		}
	} else { //The resource is not an HTML page
		//Fallback logic for non-HTML requests

		if ( /google-analytics\.com\/collect$/i.test(request.url) ){ //@todo "Nebula" 0: double check this regex (or find a better way)
			//console.log('[SW] Analytics request! Store it in the outbox until we have connection again!', event.request.url);
			//@TODO "Nebula" 0: If the fetch is for google analytics, add the request to the background sync outbox and send it when connection is available again: https://github.com/WICG/BackgroundSync/blob/master/explainer.md
		}

		//If it is an image format requested, respond with the offline image.
		if ( /\.(svg|png|jpe?g|gif)$/i.test(request.url) ){ //Is there a better way to detect this than regex?
			//console.log('[SW] Image format request. Respond with offline.jpg for', event.request.url);
			return cache.match(OFFLINE_IMG);
		}

		return;
	}
}

//Listen for message events from the client
self.addEventListener('message', function(event){
	//console.log('[SW] "message" event triggered: ' + event.data);

	clients.matchAll().then(function(clients){
		//console.group();

		// Loop over all available clients
		clients.forEach(function(client){
			//No need to update the tab/window (client) that sent the data
			if ( client.id !== event.source.id ){
				//console.log('[SW] posting message back to *other* clients...', client.id);

				client.postMessage(event.data); //Post data to a specific client
			} else {
				//console.warn('[SW] Skipping message for THIS client id', client.id);
			}
		});

		//console.groupEnd();
	});
});

//Push notifications
//Still need to do the main.js part: https://serviceworke.rs/push-rich_index_doc.html
self.addEventListener('push', function(event){
	//console.log('[SW] Push event...');

	event.waitUntil(
		self.registration.showNotification('Nebula', {
			lang: 'en',
			body: 'This is a test',
			icon: META_ICON,
			vibrate: [500, 100, 500],
		})
	);
});