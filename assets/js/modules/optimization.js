window.performance.mark('(Nebula) Inside optimization.js (module)');

//Cache DOM selectors
nebula.cacheSelectors = function(){
	if ( nebula?.dom && window.dataLayer ){
		return false; //Already cached them
	}

	nebula.dom = nebula?.dom || {
		document: jQuery(document),
		window: jQuery(window),
		html: jQuery('html'),
		body: jQuery('body'),
	};

	window.dataLayer = window.dataLayer || []; //Prevent overwriting an existing GTM Data Layer array
};

//Record performance timing
nebula.performanceMetrics = async function(){
	nebula.outputTimings();

	if ( window.performance?.timing && typeof window.requestIdleCallback === 'function' ){ //@todo "Nebula" 0: Remove the requestIdleCallback condition when Safari supports it)
		window.requestIdleCallback(function(){
			nebula.once(function(){ //Just to be safe that this only triggers once per page load
				window.performance.mark('(Nebula) CPU Idle');
				window.performance.measure('(Nebula) Until CPU Idle', 'navigationStart', '(Nebula) CPU Idle', {
					detail: {
						devtools: {
							dataType: 'track-entry',
							track: 'Nebula',
						}
					}
				});

				let timingCalcuations = {};

				//Check additional timings and output to the console when requested or for developers
				if ( nebula.get('timings') || nebula.user?.staff === 'developer' ){ //Only available to Developers or with ?timings
					//Add custom JS measurements too
					performance.getEntriesByType('mark').forEach(function(mark){
						timingCalcuations[mark.name] = {
							type: 'Mark',
							start: Math.round(mark.startTime),
							duration: false
						};
					});
					performance.getEntriesByType('measure').forEach(function(measurement){
						timingCalcuations[measurement.name] = {
							type: 'Measurement',
							start: Math.round(measurement.startTime),
							duration: Math.round(measurement.duration)
						};
					});

					let clientTimings = {};
					jQuery.each(timingCalcuations, function(name, timings){ //This is an object (not an array)
						if ( !isNaN(timings.start) && timings.start > -2 ){
							clientTimings[name] = {
								type: timings.type,
								start: timings.start,
								duration: ( timings.duration )? timings.duration : -1 //If we have a duration, use it
							};
						}
					});

					delete nebula.site.timings.categories; //Remove the categories from the timings object

					console.groupCollapsed('Performance');
					console.groupCollapsed('Marks & Measurements');
					console.table(jQuery.extend(nebula.site.timings, clientTimings)); //Performance Timings
					console.groupEnd(); //End Measurements

					console.groupCollapsed('Resources');
					let resourceCalcuations = {};
					let renderBlockingResourceCount = 0;
					performance.getEntriesByType('resource').forEach(function(resource){
						resourceCalcuations[resource.name] = {
							type: resource.initiatorType, //Ex: link, fetch, script, css, img, other
							protocol: resource.nextHopProtocol, //Ex: h2
							start: Math.round(resource.fetchStart), //How many ms elapsed before this resource request started
							duration: Math.round(resource.duration), //How many ms did it take to load this resource
							renderBlocking: null, //Start empty and we will fill it below
						};

						//Check if this resource is render blocking
						if ( resource?.renderBlockingStatus ){ //Chrome 107+
							resourceCalcuations[resource.name].renderBlocking = 'Blocking';
							renderBlockingResourceCount++; //Increment the total count
						}
					});
					console.table(resourceCalcuations); //Resource Timings
					if ( renderBlockingResourceCount >= 10 ){
						console.warn('Many render blocking resources:', renderBlockingResourceCount, 'https://web.dev/render-blocking-resources/');
					}
					console.groupEnd(); //End Resources

					//Monitor Cumulative Layout Shift (CLS) with the Layout Instability API
					//This runs after the initial task has finished- which means it outputs after the Performance console group has closed... This is also an observer, so will log to the console anytime a layout shift happens.
					if ( 'PerformanceObserver' in window ){
						let cls = 0;
						let clsCalculations = {};
						new PerformanceObserver(async function(list){
							for ( let entry of list.getEntries() ){
								//await nebula.yield();

								if ( !entry.hadRecentInput ){
									cls += entry.value;

									for ( let source of entry.sources ){
										if ( source?.node ){
											if ( !jQuery(source.node.parentElement).parents('#wpadminbar').length && !jQuery(source.node.parentElement).parents('#audit-results').length ){ //Ignore WP admin bar and Nebula audit results section
												var node = ( source.node )? nebula.domTreeToString(jQuery(source.node.parentElement)) : 'Unknown (' + Math.floor(Math.random()*99999)+10000 + ')'; //Sometimes the parentElement is null

												clsCalculations[node] = {
													node: source.node,
													parent: source.node?.parentElement,
													entryStart: Math.round(entry.startTime),
													entryCLS: entry.value,
													totalCLS: cls,
												};
											}
										}
									}
								}
							}

							//Only output this once on load to avoid cluttering the console
							nebula.once(function(){
								console.groupCollapsed('Cumulative Layout Shift (CLS)');
								console.table(clsCalculations); //CLS Values
								console.groupEnd(); //End CLS
							}, 'cls console table');

							//Log the total if it is less than nominal
							if ( nebula.screen.isFrontend && cls > 0.1 ){ //Anything over 0.1 needs improvement
								console.warn('Significant Cumulative Layout Shift (CLS):', cls, 'https://web.dev/cls/');
							}
						}).observe({type: 'layout-shift', buffered: true});
					}

					console.groupEnd(); //End Performance (Parent Group)
				}

				//Report certain timings to Google Analytics
				let navigationPerformanceEntry = performance.getEntriesByType('navigation')[0]; //There is typically only ever 1 in this, but we always just want the first one
				if ( navigationPerformanceEntry ){
					if ( navigationPerformanceEntry.duration <= 50 || navigationPerformanceEntry.duration > 15_000 ){ //Ignore extreme values to prevent skewing the average in GA4
						return false;
					}

					let pageWeightData = nebula.getPageWeightData();

					//Last chance to check if any critical metrics would be empty and skew results. If so, exit without proceeding further.
					if ( !pageWeightData || pageWeightData?.total_kb <= 1 || !navigationPerformanceEntry?.responseStart ){
						return false;
					}

					//Provide a "rating" of load time based on DOM Ready timing
					let loadSpeedRating = '';
					if ( navigationPerformanceEntry.duration <= 700 ){ //Use window load for this one
						loadSpeedRating = 'instant'; //The load time is so fast that it is unnoticeable
					} else if ( navigationPerformanceEntry.domComplete <= 1500 ){
						loadSpeedRating = 'fast';
					} else if ( navigationPerformanceEntry.domComplete <= 3500 ){
						loadSpeedRating = 'moderate';
					} else {
						loadSpeedRating = 'slow';
					}

					let loadTimingData = { //These are sent in seconds (not milliseconds) so create Custom Metrics with the appropriate units
						timed_views: 1, //Use this as a custom metric (count) for the calculated metrics for the average times. Ex: {DOM Complete Time}/{Timed Views}
						session_page_type: ( nebula.isLandingPage() )? 'Landing Page' : 'Subsequent Page', //Dimension
						load_speed_rating: loadSpeedRating, //Dimension
						initial_visibility: document?.visibilityState, //Dimension
						effective_network_type: navigator?.connection?.effectiveType || 'Unknown', //Dimension
						largest_asset_name: pageWeightData.largest_asset_url.split('/').pop().split('?')[0], //Dimension (just the file name without query string to limit unique values)
						largest_asset_size: pageWeightData.largest_asset_kb, //Metric (Compressed asset size in KB before lazy loading)
						total_file_size: pageWeightData.total_kb, //Metric (Compressed Page Weight in KB before lazy loading)
						server_response: (navigationPerformanceEntry.responseStart/1000).toFixed(3), //Metric
						dom_interactive: (navigationPerformanceEntry.domInteractive/1000).toFixed(3), //Metric
						dom_complete: (navigationPerformanceEntry.domComplete/1000).toFixed(3), //Metric
						window_loaded: (navigationPerformanceEntry.duration/1000).toFixed(3), //Metric
						link_url: window.location.href, //Using "link_url" so additional custom dimensions are not needed
						non_interaction: true
					};

					gtag('event', 'load_timings', loadTimingData);
				}
			}, 'performance idle');
		});
	}
};

//Get the total (compressed) file size of all loaded resources
nebula.getPageWeightData = function(){
	let resources = performance.getEntriesByType('resource');
	let navEntry = performance.getEntriesByType('navigation')[0];

	let totalBytes = 0;
	let largestAssetSize = 0;
	let largestAssetUrl = '';

	for ( let resource of resources ){
		let size = ( resource.transferSize > 0 )? resource.transferSize : resource.encodedBodySize || 0;

		totalBytes += size;

		if ( size > largestAssetSize ){
			largestAssetSize = size;
			largestAssetUrl = resource.name;
		}
	}

	if ( navEntry?.transferSize ){
		totalBytes += navEntry.transferSize;
	}

	return {
		total_kb: totalBytes/1024,
		largest_asset_kb: largestAssetSize/1024,
		largest_asset_url: largestAssetUrl
	};
};

//Use Workbox Window to register and communicate with the service worker
//https://developers.google.com/web/tools/workbox/modules/workbox-window
//https://developers.google.com/web/tools/workbox/reference-docs/latest/module-workbox-window.Workbox
nebula.workbox = async function(){
	jQuery('.nebula-sw-install-button').addClass('inactive'); //If manually placing this button, start with this inactive class to prevent CLS

	if ( nebula.site?.options?.sw ){ //If Service Worker is enabled in Nebula Options
		if ( 'serviceWorker' in navigator ){ //If Service Worker is supported (Firefox 44+, Chrome 45+, Edge 17+, Safari 12+)
			//When debugging unregister SW and clear caches
			if ( nebula.site?.options?.bypass_cache || nebula.get('debug') || nebula.get('audit') || nebula.dom.html.hasClass('debug') ){
				nebula.unregisterServiceWorker(); //Unregister the ServiceWorker
				nebula.emptyCaches(); //Clear the caches
				return false;
			}

			window.performance.mark('(Nebula) SW Registration [Start]');

			//Dynamically import Workbox-Window
			import('https://cdn.jsdelivr.net/npm/workbox-window@7.1.0/build/workbox-window.prod.mjs').then(function(module){
				const Workbox = module.Workbox;
				const workbox = new Workbox(nebula.site.sw_url);

				//Listen for Service Worker installation (this is different than PWA installation)
				workbox.addEventListener('installed', function(event){
					//Skip waiting
					workbox.messageSkipWaiting(); //Will probably end up using this, but try it without if first- it was not in the tutorial

					if ( !event.isUpdate ){
						//Service worker installed for the first time
					}
				});

				//Activate the service worker
				workbox.addEventListener('activated', async function(event){
					//Send the Workbox service worker router a list of resources to cache
					workbox.messageSW({
						type: 'CACHE_URLS', //This message type is handled in Workbox
						payload: {
							urlsToCache: [
								location.href, //Current page
								...performance.getEntriesByType('resource').map(function(resource){ //Get all of the resources used by this page
									return resource.name;
								}),
							]
						},
					});

					//Now we can send messages back and forth
					nebula.dom.document.trigger('nebula_workbox_active', workbox); //Allow others to interactive with Workbox (Ex: send messages with workbox.messageSW)
					//const swVersion = await workbox.messageSW({type: 'GET_VERSION'}); //The message type here must match what the SW expects

					//event.isUpdate will be true if another version of the service worker was controlling the page when this version was registered. It will be false on the very first installation
					if ( !event.isUpdate ){
						//If your service worker is configured to precache assets, those assets should all be available now.
						nebula.dom.document.trigger('nebula_sw_first_activation');
					}
				});

				//When the service worker begins controlling
				workbox.addEventListener('controlling', function(event){
					//Service worker is now controlling
				});

				//Waiting to activate the new service worker until all tabs running the current version have fully unloaded
				workbox.addEventListener('waiting', function(event){
					//A new service worker has installed, but it cannot activate until all tabs running the current version have fully unloaded.
					//Create an update button to reload the page
					jQuery('<button id="nebula-sw-update"><i class="fa-solid fa-fw fa-sync-alt"></i> Update available. Click to reload.</button>').appendTo('body').on('click', function(){
						window.location.reload();
						nebula.animate('#nebula-sw-update', 'nebula-zoom-out');
						return false;
					});

					//Show the button
					//window.requestIdleCallback(function(){ //when Safari supports requestIdleCallback
						window.requestAnimationFrame(function(){
							//jQuery('#nebula-sw-update').addClass('active'); //Not showing to users as of Feb 2021. Need to make sure this reload method works (and with multiple tabs)
						});
					//});
				});

				//If the service worker becomes redundant
				workbox.addEventListener('redundant', function(event){
					gtag('event', 'exception', {
						message: '(JS) The installed service worker became redundant.',
						fatal: false
					});
				});

				//Notify the user of cache updates (this is from documentation, so test this thoroughly)
				workbox.addEventListener('message', function(event){
					if ( event.data.type === 'CACHE_UPDATED' ){
						const updatedURL = event.data.payload;
					}

					nebula.dom.document.trigger('nebula_sw_message', event.data);
				});

				//Register the service worker after above workbox event listeners have been added
				workbox.register().then(function(){
					window.performance.mark('(Nebula) SW Registration [End]');
					window.performance.measure('(Nebula) SW Registration', '(Nebula) SW Registration [Start]', '(Nebula) SW Registration [End]');
				}).catch(function(error){
					gtag('event', 'exception', {
						message: '(JS) ServiceWorker registration failed: ' + error,
						fatal: false
					});
				});
			});

			nebula.pwa();
		}
	} else {
		nebula.unregisterServiceWorker();
	}
};

//Force unregister all existing service workers
nebula.unregisterServiceWorker = function(){
	if ( 'serviceWorker' in navigator ){
		navigator.serviceWorker.getRegistrations().then(async function(registrations){
			for ( let registration of registrations ){
				await nebula.yield();
				registration.unregister();
			}
		});
	}
};

//Clear the caches
nebula.emptyCaches = async function(){
	if ( 'caches' in window ){
		const names = await caches.keys();
		for ( let name of names ){
			await nebula.yield();
			await caches.delete(name);
		}
	}
};

//Progressive Web App functions (when the user installs the PWA onto their device)
nebula.pwa = function(){
	let installPromptEvent; //Scope it to this level

	//Listen for ability to show SW install prompt
	window.addEventListener('beforeinstallprompt', function(event){
		event.preventDefault(); //Prevent Chrome <= 67 from automatically showing the prompt
		installPromptEvent = event; //Stash the event so it can be triggered later.
		jQuery('.nebula-sw-install-button').removeClass('inactive').addClass('ready'); //Show the Nebula install button if it is present.
	});

	//Trigger the SW install prompt and handle user choice
	nebula.dom.document.on('click', '.nebula-sw-install-button', function(){
		if ( typeof installPromptEvent !== 'undefined' ){ //If the install event has been stashed for manual trigger
			jQuery('.nebula-sw-install-button').removeClass('ready').addClass('prompted');

			installPromptEvent.prompt(); //Show the modal add to home screen dialog

			let thisEvent = {
				event_name: 'pwa_install',
				event_category: 'Progressive Web App',
				event_action: 'Install Prompt Shown',
				event_label: 'The PWA install prompt was shown to the user',
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));

			//Wait for the user to respond to the prompt
			installPromptEvent.userChoice.then(function(result){
				jQuery('.nebula-sw-install-button').removeClass('prompted').addClass('ready');

				let thisEvent = {
					event_name: 'pwa_install',
					event_category: 'Progressive Web App',
					event_action: 'Install Prompt User Choice',
					result: result,
					outcome: result.outcome,
				};

				nebula.dom.document.trigger('nebula_event', thisEvent);
				gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
				nebula.crm('event', 'Install Prompt ' + thisEvent.outcome);
			});
		} else {
			jQuery('.nebula-sw-install-button').removeClass('ready').addClass('inactive');
		}

		return false;
	});

	//PWA installed to the device
	window.addEventListener('appinstalled', function(){
		jQuery('.nebula-sw-install-button').removeClass('ready').addClass('success');

		let thisEvent = {
			event_name: 'pwa_install',
			event_category: 'Progressive Web App',
			event_action: 'App Installed',
			event_label: 'The PWA has been installed',
		};

		nebula.dom.document.trigger('nebula_event', thisEvent);
		gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
	});
};

//Detections for events specific to predicting the next pageview.
nebula.predictiveCacheListeners = async function(){
	//If Save Data is supported and Save Data is requested do not bother with predictive listeners
	if ( navigator.connection?.saveData ){
		return false;
	}

	//Any post listing page
	if ( jQuery('.first-post .entry-title a').length ){
		nebula.prefetch(jQuery('.first-post .entry-title a').attr('href'));
	}

	//Internal link hovers
	let predictiveHoverTimeout;
	jQuery('a').on('mouseenter', async function(){
		//await nebula.yield();

		let $oThis = jQuery(this);
		let url = $oThis.attr('href');

		if ( url && !predictiveHoverTimeout ){
			predictiveHoverTimeout = window.setTimeout(function(){
				predictiveHoverTimeout = null; //Reset the timer
				nebula.prefetch(url); //Attempt to prefetch
			}, 250);
		}
	}).on('mouseleave', function(){
		if ( predictiveHoverTimeout ){
			window.clearTimeout(predictiveHoverTimeout);
			predictiveHoverTimeout = null;
		}
	});
};

//Prefetch a resource
nebula.prefetch = async function(url = '', callback, element){
	if ( url && url.length > 1 && url.indexOf('#') !== 0 && typeof window.requestIdleCallback === 'function' ){ //If the URL exists, is longer than 1 character and does not begin with # (waiting for Safari to support requestIdleCallback)
		//If network connection is 2G don't prefetch
		if ( navigator.connection?.effectiveType.toString().includes('2g') ){ //'slow-2g', '2g', '3g', or '4g'
			return false;
		}

		//If Save Data is supported and Save Data is requested don't prefetch
		if ( navigator.connection?.saveData ){
			return false;
		}

		//Ignore request to prefetch the current page
		if ( url === window.location.href || url === nebula.post?.permalink ){
			return false;
		}

		//Ignore links with certain attributes and classes (if the element itself was passed by reference)
		if ( element && (jQuery(element).is('[download]') || jQuery(element).hasClass('no-prefetch') || jQuery(element).parents('.no-prefetch').length) ){
			return false;
		}

		//Only https protocol (ignore "mailto", "tel", etc.)
		if ( !url.startsWith('https') ){
			return false;
		}

		//Ignore certain files
		if ( (/\.(?:pdf|docx?|xlsx?|pptx?|zipx?|rar|tar|txt|rtf|ics|vcard)/).test(url) ){
			return false;
		}

		//Strip out unnecessary parts of the URL
		url = url.split('#')[0]; //Remove hashes

		//Ignore blocklisted terms (logout, 1-click purchase buttons, etc.)
		let prefetchBlocklist = wp.hooks.applyFilters('nebulaPrefetchBlocklist', ['logout', 'wp-admin']);

		jQuery.each(prefetchBlocklist, function(index, value){
			if ( url.includes(value) ){
				url = ''; //Empty the URL so it will fail the next condition
				return false; //This just breaks out of the loop (does not stop the function)
			}
		});

		window.requestIdleCallback(function(){ //Wait until the browser is idle before prefetching
			if ( url.length && !jQuery('link[rel="prefetch"][href="' + url + '"]').length ){ //If prefetch link for this URL has not yet been added to the DOM
				jQuery('<link rel="prefetch" href="' + url + '">').on('load', callback).appendTo('head'); //Append a prefetch link element for this URL to the DOM
			}
		});
	}
};

//Lazy load images, styles, and JavaScript assets
nebula.lazyLoadAssets = async function(){
	nebula.site.resources.lazy.promises = {};

	//Detect if Bootstrap JS is needed and load it
	//A wildcard attribute name selector would be super useful here, but does not exist. Something like [data-bs-*] would be perfect...
	//That being said, the Offcanvas component will be used on 95% of Nebula sites, so this will likely load on every page regardless.
	if ( typeof bootstrap === 'undefined' ){ //If Bootstrap JS has not already been initialized
		if ( jQuery('.offcanvas, .accordion, .alert, .carousel, .collapse, .dropdown-menu, .modal, .nav-tabs, .nav-pills, [data-bs-toggle]').length ){
			nebula.loadJS(nebula.site.resources.scripts['nebula_bootstrap']).then(function(){ //Load Bootstrap JS
				nebula.initBootstrapFunctions(); //Initialize Nebula Bootstrap helper functionality now that Bootstrap JS has finished loading here
			});
		}
	} else {
		nebula.initBootstrapFunctions(); //Initialize Nebula Bootstrap helper functionality immediately since Bootstrap JS has already been loaded somewhere else
	}

	//Lazy load elements as they scroll into viewport
	try {
		//Observe the entries that are identified and added later (below)
		let lazyObserver = new IntersectionObserver(function(entries){
			entries.forEach(async function(entry){
				//await nebula.yield();

				if ( entry.intersectionRatio > 0 ){
					nebula.loadElement(jQuery(entry.target));
					lazyObserver.unobserve(entry.target); //Stop observing the element
				}
			});
		}, {
			rootMargin: '50%', //Extend the area of the observer (100% = Double the Viewport). Try to prevent visible loading of elements by triggering the load much earlier than actually needed.
			threshold: 0.1
		});

		//Create the entries and add them to the observer
		jQuery('.nebula-lazy-position, .lazy-load, .nebula-lazy').each(function(){
			lazyObserver.observe(jQuery(this)[0]); //Observe the element
		});

		//When scroll reaches the bottom, ensure everything has loaded at this point
		//Only when IntersectionObserver exists because otherwise everything is immediately loaded anyway
		let lazyLoadScrollBottom = function(){
			if( nebula.dom.window.scrollTop()+nebula.dom.window.height() > nebula.dom.document.height()-500 ){ //When the scroll position reaches 500px above the bottom
				nebula.loadEverything();
				window.removeEventListener('scroll', lazyLoadScrollBottom); //Stop listening for this scroll event
			}
		};
		window.addEventListener('scroll', lazyLoadScrollBottom); //Scroll is passive by default
	} catch(error){
		nebula.loadEverything(); //If any error, load everything immediately
		nebula.help('Lazy Load Observer: ' + error + '. All assets have been loaded immediately.', '/functions/lazyloadassets/', true);
	}

	//Load all lazy elements at once if requested
	nebula.dom.window.on('nebula_load', function(){
		if ( typeof window.requestIdleCallback === 'function' ){ //If requestIdleCallback exists, use it. Remove this check when Safari supports it
			window.requestIdleCallback(function(){
				nebula.loadEverything();
			});
		} else { //Otherwise, just run immediately
			nebula.loadEverything();
		}
	});

	//Lazy load CSS assets
	//Listen for requestIdleCallback here when Safari supports it
	jQuery.each(nebula.site.resources.lazy.styles, function(handle, condition){
		if ( condition === 'all' || jQuery(condition).length ){
			if ( nebula.site.resources.styles[handle.replaceAll('-', '_')] ){ //If that handle exists in the registered styles
				nebula.loadCSS(nebula.site.resources.styles[handle.replaceAll('-', '_')]);
			}
		}
	});

	//Lazy load JS assets
	//Listen for requestIdleCallback here when Safari supports it
	jQuery.each(nebula.site.resources.lazy.scripts, function(handle, condition){
		if ( condition === 'all' || jQuery(condition).length ){
			if ( nebula.site.resources.scripts[handle.replaceAll('-', '_')] ){ //If that handle exists in the registered scripts
				nebula.loadJS(nebula.site.resources.scripts[handle.replaceAll('-', '_')], handle); //Load it (with a Promise)
			}
		}
	});

	//Load the Google Maps API if 'googlemap' class exists
	if ( jQuery('.googlemap').length ){
		if ( typeof google == 'undefined' || typeof google.maps == 'undefined' ){ //If the API has not already been called
			nebula.loadJS('https://www.google.com/jsapi?key=' + nebula.site.options.nebula_google_browser_api_key, 'google-maps').then(function(){ //May not need key here, but just to be safe.
				google.load('maps', '3', {
					other_params: 'libraries=places&key=' + nebula.site.options.nebula_google_browser_api_key,
					callback: function(){
						nebula.dom.document.trigger('nebula_google_maps_api_loaded');
					}
				});
			});
		} else {
			nebula.dom.document.trigger('nebula_google_maps_api_loaded'); //Already loaded
		}
	}

	if ( jQuery('pre.nebula-code, pre.nebula-code').length ){
		nebula.loadCSS(nebula.site.resources.styles.nebula_pre);
		nebula.pre();
	}
};

//When necessary, load any element that is meant to be lazy loaded immediately
//Either call this directly, or trigger 'nebula_load' on the window
nebula.loadEverything = async function(){
	//Listen for requestIdleCallback here when Safari supports it
	jQuery('.nebula-lazy-position, .lazy-load, .nebula-lazy').each(async function(){
		//await nebula.yield();
		nebula.loadElement(jQuery(this)); //Load the element immediately
	});
};

//Load the Nebula lazy load element
nebula.loadElement = async function(element){
	//await nebula.yield();

	//Lazy elements using <samp> positioning
	if ( element.is('samp') ){
		let lazyElement = element.next('noscript.nebula-lazy');
		element.remove(); //Remove the positioning element

		//The actual lazy loaded element as a jQuery object
		let thisContent = jQuery(lazyElement.text()).on('load loadeddata', function(){ //Warning: DOM text is reinterpreted as HTML without escaping meta-characters. Not sure how to sanitize this?
			//If the lazy content is a video (or potentially a video iframe) re-kick the video tracking
			if ( jQuery(thisContent[0]).is('video, iframe') && typeof nebula.lazyVideoAPI === 'function' ){ //This function is likely defined after this JS module is loaded, but if so, the APIs would not need to be re-kicked anyway
				nebula.lazyVideoAPI(jQuery(thisContent[0]));
			}
		});

		lazyElement.replaceWith(thisContent); //Remove the <noscript> tag to reveal the img/iframe tag
		nebula.svgImgs(); //Convert certain <img> elements that use SVG into SVG elements
	}

	//Background images
	if ( element.hasClass('lazy-load') || element.hasClass('nebula-lazy') ){
		element.removeClass('lazy-load nebula-lazy').addClass('lazy-loaded');
	}
};

//Load a JavaScript resource
//This returns a promise, but the callback parameter could also be used
nebula.loadJS = async function(url, handle, callback=false){
	//await nebula.yield();

	nebula.site.resources.lazy.promises = nebula.site.resources.lazy.promises || {}; //Ensure this exists

	//Listen for requestIdleCallback when Safari supports it
	if ( typeof url === 'string' ){
		if ( !handle ){
			handle = url.split('\\').pop().split('/').pop().split('?')[0]; //Get the filename from the URL and remove query strings
		}

		//Look into import() here instead of generating a <script> element to append. The import() function does work with third-party endpoints.

		//Store the promise so it can be listened for elsewhere if necessary
		nebula.site.resources.lazy.promises[handle] = new Promise(function(resolve, reject){
			var lazyScriptElement = document.createElement('script');
			lazyScriptElement.src = url;
			lazyScriptElement.onload = resolve;
			lazyScriptElement.onerror = reject;

			document.body.appendChild(lazyScriptElement);
		}).then(function(){
			//Trigger an event if that is an option to listen for as well
			nebula.dom.document.trigger('nebula_loadjs_' + handle.replaceAll(/[^a-zA-Z]/gi, '')); //This one is specific to the handle being loaded. Ex: 'nebula_loadjs_bootstrapbundleminjs'
			nebula.dom.document.trigger('nebula_loadjs', handle); //This one is a generic one that passes the handle name

			if ( callback ){ //Callback just in case it is preferred instead of the returned promise.
				return callback(); //Should this have a return on it? That would break the promise, but if a callback is specified it would be expected...
			}
		});

		return nebula.site.resources.lazy.promises[handle];
	}

	nebula.help('nebula.loadJS() requires a valid URL. The requested URL is invalid: ' + url, '/functions/loadjs/');
};

//Dynamically load CSS files using JS
//If JavaScript is disabled, these are loaded via <noscript> tags
nebula.loadCSS = async function(url){
	//await nebula.yield();

	if ( typeof url === 'string' ){
		jQuery('head').append('<link rel="stylesheet" href="' + url + '" type="text/css" media="screen">');
	} else {
		nebula.help('nebula.loadCSS() requires a valid URL string. The requested URL is invalid: ' + url, '/functions/loadcss/');
	}
};

//Output timings to respective locations
nebula.outputTimings = function(){
	//Update any location displaying the server response time
	if ( nebula?.post?.ttfb ){
		let ttfbClass = '';
		if ( nebula.post.ttfb >= 2 ){
			ttfbClass = 'essential text-danger';
		} else if ( nebula.post.ttfb >= 1 ){
			ttfbClass = 'essential text-caution';
		}

		jQuery('.nebula-ttfb-time').text(nebula.post.ttfb.toFixed(2)).addClass('updated');
		jQuery('#nebula_ataglance .nebula-ttfb-time').parent().addClass(ttfbClass);
	}

	//If we have a timings object
	var parentId = 'wp-admin-bar-nebula-timing-categories'; //ID of the parent node created in PHP

	if ( nebula?.site?.timings ){
		//If that object contains categories
		if ( nebula?.site?.timings?.categories ){
			//Add timings to the Admin Bar
			if ( jQuery('#' + parentId).length && !jQuery('#' + parentId).hasClass('updated') ){
				jQuery.each(nebula.site.timings.categories, function(label, timing){
					let timingClass = '';
					if ( timing >= 1 ){
						timingClass = 'danger';
					} else if ( timing >= 0.5 ){
						timingClass = 'warning';
					} else if ( timing < 0.1 ){
						timingClass = 'ignorable';
					}

					let labelOutput = label.replace(/^(\[[^\]\s]+\])/, '<span class="group-name">$1</span>'); //If square brackets exist at the beginning of the string, wrap it in a span

					//Add a sub-node
					jQuery('#' + parentId).find('ul.ab-submenu').append('<li id="wp-admin-bar-nebula-timing-category-' + nebula.sanitizeClassName(label) + '" class="nebula-timing-category-item ' + timingClass + '" role="group"><div class="ab-item ab-empty-item" role="menuitem">' + labelOutput + ': <strong>' + timing.toFixed(3) + ' seconds</strong></div></li>');
				});
			}
		} else {
			jQuery('#' + parentId).remove(); //Remove the parent node if no categories exist
		}
	} else {
		jQuery('#' + parentId).remove(); //Remove the parent node if no categories exist
	}
};