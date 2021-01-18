'use strict';

window.performance.mark('(Nebula) Inside nebula.js');
jQuery.noConflict();

/*==========================
 DOM Ready
 ===========================*/

jQuery(function(){
	window.performance.mark('(Nebula) DOM Ready [Start]');

	//Utilities
	nebula.cacheSelectors();
	nebula.initBootstrapFunctions(); //Must be in DOM ready
	nebula.helpers();
	nebula.svgImgs();
	nebula.errorMitigation();

	//Navigation
	nebula.subnavExpanders();
	nebula.menuSearchReplacement();

	//Search
	nebula.singleResultDrawer();
	nebula.pageSuggestion();

	//Forms
	nebula.liveValidator();
	nebula.cf7Functions();
	nebula.cf7LocalStorage();

	//Interaction
	nebula.socialSharing();
	nebula.animationTriggers();
	nebula.scrollTo();

	nebula.visibilityChangeActions();
	nebula.dom.document.on('visibilitychange', function(){
		nebula.visibilityChangeActions();
	});

	nebula.eventTracking();

	window.performance.mark('(Nebula) DOM Ready [End]');
	window.performance.measure('(Nebula) DOM Ready Functions', '(Nebula) DOM Ready [Start]', '(Nebula) DOM Ready [End]');
});

/*==========================
 Window Load
 ===========================*/

jQuery(window).on('load', function(){
	window.performance.mark('(Nebula) Window Load [Start]');

	nebula.cacheSelectors();
	nebula.performanceMetrics();
	nebula.lazyLoadAssets(); //Move to (or use) requestIdleCallback after end of IE11 support
	nebula.initVideoTracking(); //Move to (or use) requestIdleCallback after end of IE11 support?

	//Navigation
	nebula.overflowDetector(); //Move to (or use) requestIdleCallback after end of IE11 support?

	//Search (several of these could use requestIdleCallback)
	nebula.wpSearchInput();
	nebula.mobileSearchPlaceholder();
	nebula.autocompleteSearchListeners();
	nebula.searchValidator();
	nebula.searchTermHighlighter(); //Move to (or use) requestIdleCallback after end of IE11 support? Already is requesting animation frame

	//Forms
	nebula.addressAutocomplete('#address-autocomplete', 'nebulaGlobalAddressAutocomplete');

	nebula.facebookSDK();

	nebula.networkConnection();

	nebula.lastWindowWidth = nebula.dom.window.width(); //Prep resize detection (Is this causing a forced reflow?)
	jQuery('a, li, tr').removeClass('hover');
	nebula.dom.html.addClass('loaded');

	nebula.registerServiceWorker();
	nebula.predictiveCacheListeners(); //Move to (or use) requestIdleCallback after end of IE11 support

	nebula.networkAvailable(); //Call it once on load, then listen for changes
	nebula.dom.window.on('offline online', function(){
		nebula.networkAvailable();
	});

	nebula.cookieNotification();

	window.performance.mark('(Nebula) Window Load [End]');
	window.performance.measure('(Nebula) Window Load Functions', '(Nebula) Window Load [Start]', '(Nebula) Window Load [End]');
	window.performance.measure('(Nebula) Window Loaded', 'navigationStart', '(Nebula) Window Load [End]');
});

/*==========================
 Window Resize
 ===========================*/

window.addEventListener('resize', function(){
	nebula.debounce(function(){ //Must use debounce here (not throttle) so it always runs after the resize finishes (throttle does not always run at the end)
		if ( typeof nebula.lastWindowWidth !== 'undefined' && nebula.dom.window.width() != nebula.lastWindowWidth ){ //If the width actually changed
			nebula.lastWindowWidth = nebula.dom.window.width();
			nebula.mobileSearchPlaceholder();
			nebula.initMmenu(); //If Mmenu has not been initialized, it may need to be if the screen size has reduced
		}
	}, 250, 'window resize');
}); //End Window Resize

/*==========================
 Additional Settings
 ===========================*/

nebula.regex = {
	email: /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/i, //From JS Lint: Expected ']' and instead saw '['.
	phone: /^(?:(?:\+?1\s*(?:[.-]\s*)?)?(?:\(\s*([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9])\s*\)|([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9]))\s*(?:[.-]\s*)?)?([2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)?([0-9]{4})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?$/, //To allow letters, you'll need to convert them to their corresponding number before matching this RegEx.
	address: /^\d{1,6}\s+.{2,25}\b(avenue|ave|court|ct|street|st|drive|dr|lane|ln|road|rd|blvd|plaza|parkway|pkwy)[.,]?[^a-z]/i, //Street address
	date: {
		mdy: /^((((0[13578])|([13578])|(1[02]))[.\/-](([1-9])|([0-2][0-9])|(3[01])))|(((0[469])|([469])|(11))[.\/-](([1-9])|([0-2][0-9])|(30)))|((2|02)[.\/-](([1-9])|([0-2][0-9]))))[.\/-]((1|2)\d{3}|\d{2})$/,
		ymd: /^((1|2)\d{3}|\d{2})[.\/-]((((0[13578])|([13578])|(1[02]))[.\/-](([1-9])|([0-2][0-9])|(3[01])))|(((0[469])|([469])|(11))[.\/-](([1-9])|([0-2][0-9])|(30)))|((2|02)[.\/-](([1-9])|([0-2][0-9]))))$/,
	},
	hex: /^#?([a-f0-9]{6}|[a-f0-9]{3})$/,
	ip: /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/,
	url: /(\(?(?:(http|https|ftp):\/\/)?(?:((?:[^\W\s]|\.|-|[:]{1})+)@{1})?((?:www.)?(?:[^\W\s]|\.|-)+[\.][^\W\s]{2,4}|localhost(?=\/)|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})(?::(\d*))?([\/]?[^\s\?]*[\/]{1})*(?:\/?([^\s\n\?\[\]\{\}\#]*(?:(?=\.)){1}|[^\s\n\?\[\]\{\}\.\#]*)?([\.]{1}[^\s\?\#]*)?)?(?:\?{1}([^\s\n\#\[\]]*))?([\#][^\s\n]*)?\)?)/i,
};

nebula.timings = [];
nebula.videos = {};
nebula.scroll = {
	offset: 0, //Used for global scroll offsets (when not able to modify certain links or to save redundant parameters)
	speed: 500
};

/*==========================
 Optimization Functions
 ===========================*/

//Cache DOM selectors
nebula.cacheSelectors = function(){
	nebula.dom = {
		document: jQuery(document),
		window: jQuery(window),
		html: jQuery('html'),
		body: jQuery('body'),
	};
};

//Nebula Service Worker
//@TODO "Nebula" 0: Consider using workbox-window here to tie into the Workbox sw.js file (after IE11): https://developers.google.com/web/tools/workbox/modules/workbox-window
nebula.registerServiceWorker = function(){
	jQuery('.nebula-sw-install-button').addClass('inactive');

	if ( nebula.site.options.sw && 'serviceWorker' in navigator ){ //Firefox 44+, Chrome 45+, Edge 17+, Safari 12+ //@todo "Nebula" 0: Use optional chaining
		window.performance.mark('(Nebula) SW Registration [Start]');

		//navigator.serviceWorker.register(nebula.site.sw_url, {cache: 'max-age=0'}).then(function(registration){
		navigator.serviceWorker.register(nebula.site.sw_url).then(function(registration){
			//console.log('ServiceWorker registration successful with scope: ', registration.scope);
			//console.debug(registration);

			window.performance.mark('(Nebula) SW Registration [End]');
			window.performance.measure('(Nebula) SW Registration', '(Nebula) SW Registration [Start]', '(Nebula) SW Registration [End]');

			//Unregister the ServiceWorker on ?debug
			if ( nebula.dom.html.hasClass('debug') ){
				registration.unregister();
				return false;
			}

			//Listen for an updated SW
			registration.addEventListener('updatefound', function(){ //Triggered if sw.js changes. This event implies that registration.installing is set; see https://w3c.github.io/ServiceWorker/#service-worker-registration-updatefound-event
				registration.installing.addEventListener('statechange', function(){
					if ( registration.installing ){
						switch ( registration.installing.state ){
							case 'installing':
								break;
							case 'installed': //A new service worker is available, inform the user
								//Create an update button to reload the page
								// jQuery('<button id="nebula-sw-update">Update available. Click to reload.</button>').appendTo('body').on('click', function(){
								// 	window.location.reload();
								// 	nebula.animate('#nebula-sw-update', 'nebula-zoom-out');
								// 	return false;
								// });

								//Show the button
								// window.requestIdleCallback(function(){
								// 	window.requestAnimationFrame(function(){
								// 		jQuery('#nebula-sw-update').addClass('active');
								// 	});
								// });

								break;
							case 'redundant':
								ga('send', 'exception', {'exDescription': '(JS) The installing service worker became redundant.', 'exFatal': false});
								break;
						}
					}
				});
			});

			//Listen for messages from the Service Worker
			navigator.serviceWorker.addEventListener('message', function(event){
				nebula.dom.document.trigger('nebula_sw_message', event.data);
			});

			return navigator.serviceWorker.ready; //This can be listened for elsewhere with navigator.serviceWorker.ready.then(function(){ ... });
		}).catch(function(error){
			ga('send', 'exception', {'exDescription': '(JS) ServiceWorker registration failed: ' + error, 'exFatal': false});
		});

		//Listen for ability to show SW install prompt
		window.addEventListener('beforeinstallprompt', function(event){
			event.preventDefault(); //Prevent Chrome <= 67 from automatically showing the prompt
			var installPromptEvent = event; //Stash the event so it can be triggered later.
			jQuery('.nebula-sw-install-button').removeClass('inactive').addClass('ready'); //Show the Nebula install button if it is present.
		});

		//Trigger the SW install prompt and handle user choice
		nebula.dom.document.on('click', '.nebula-sw-install-button', function(){
			if ( typeof installPromptEvent !== 'undefined' ){ //If the install event has been stashed for manual trigger
				jQuery('.nebula-sw-install-button').removeClass('ready').addClass('prompted');

				installPromptEvent.prompt(); //Show the modal add to home screen dialog

				var thisEvent = {
					category: 'Progressive Web App',
					action: 'Install Prompt Shown',
					label: event.platforms.join(', '),
				};

				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);

				//Wait for the user to respond to the prompt
				installPromptEvent.userChoice.then(function(result){
					jQuery('.nebula-sw-install-button').removeClass('prompted').addClass('ready');

					var thisEvent = {
						category: 'Progressive Web App',
						action: 'Install Prompt User Choice',
						result: result,
						outcome: result.outcome,
					};

					nebula.dom.document.trigger('nebula_event', thisEvent);
					ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.outcome);
					nebula.crm('event', 'Install Prompt ' + thisEvent.outcome);
				});
			} else {
				jQuery('.nebula-sw-install-button').removeClass('ready').addClass('inactive');
			}

			return false;
		});

		//PWA Installed
		window.addEventListener('appinstalled', function(){
			jQuery('.nebula-sw-install-button').removeClass('ready').addClass('success');

			var thisEvent = {
				category: 'Progressive Web App',
				action: 'App Installed',
				label: 'The app has been installed',
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
		});

		//Clear the caches with ?debug query string
		if ( nebula.get('debug') ){
			if ( 'caches' in window ){
				caches.keys().then(function(names){
					for ( var i = 0; i < names.length; i++ ){ //Change this back to: for ( let name of names ){ when we stop supporting IE11
						caches.delete(names[i]);
					}
				});
			}
		}
	}
};

//Detections for events specific to predicting the next pageview.
nebula.predictiveCacheListeners = function(){
	//If Save Data is supported and Save Data is requested do not bother with predictive listeners
	if ( nebula.has(navigator, 'connection.saveData') ){ //@todo "Nebula" 0: Replace with optional chaining
		if ( navigator.connection.saveData ){ //@todo "Nebula" 0: Use optional chaining
			return false;
		}
	}

	//Any post listing page
	if ( jQuery('.first-post .entry-title a').length ){
		nebula.prefetch(jQuery('.first-post .entry-title a').attr('href'));
	}

	//Internal link hovers
	var predictiveHoverTimeout;
	jQuery('a').on('mouseenter', function(){
		var oThis = jQuery(this);
		var url = oThis.attr('href');

		if ( url && !predictiveHoverTimeout ){
			predictiveHoverTimeout = window.setTimeout(function(){
				predictiveHoverTimeout = null; //Reset the timer
				nebula.prefetch(url); //Attempt to prefetch
			}, 250);
		}
	}, function(){
		if ( predictiveHoverTimeout ){
			window.clearTimeout(predictiveHoverTimeout);
			predictiveHoverTimeout = null;
		}
	});

	//Once idle, prefetch the top-level nav items and buttons
	if ( typeof window.requestIdleCallback === 'function' ){
		//Prefetch certain elements on window idle
		window.requestIdleCallback(function(){
			//Top-level primary nav links
			jQuery('ul#menu-primary > li.menu-item > a').each(function(){
				nebula.prefetch(jQuery(this).attr('href'), false, jQuery(this));
			});

			//First 5 buttons
			jQuery('a.btn, a.wp-block-button__link').slice(0, 4).each(function(){
				nebula.prefetch(jQuery(this).attr('href'), false, jQuery(this));
			});
		});
	}
};

//Prefetch a resource
nebula.prefetch = function(url, callback, element){
	if ( url && url.length > 1 && url.indexOf('#') !== 0 && typeof window.requestIdleCallback === 'function' ){ //If the URL exists, is longer than 1 character and does not begin with #
		//If network connection is 2G don't prefetch
		if ( nebula.has(navigator, 'connection.effectiveType') && navigator.connection.effectiveType.toString().indexOf('2g') >= 0 ){ //'slow-2g', '2g', '3g', or '4g' //@todo "Nebula" 0: Replace with optional chaining
			return false;
		}

		//If Save Data is supported and Save Data is requested don't prefetch
		if ( nebula.has(navigator, 'connection.saveData') ){ //@todo "Nebula" 0: Replace with optional chaining
			if ( navigator.connection.saveData ){ //@todo "Nebula" 0: Use optional chaining
				return false;
			}
		}

		//Ignore request to prefetch the current page
		if ( url === window.location.href ){ //Add || url === nebula?.post?.permalink when optional chaining can be used
			return false;
		}

		//Ignore links with certain attributes and classes (if the element itself was passed by reference)
		if ( element && (jQuery(element).is('[download]') || jQuery(element).hasClass('no-prefetch') || jQuery(element).parents('.no-prefetch').length) ){
			return false;
		}

		//Only https protocol (ignore "mailto", "tel", etc.)
		if ( !/^https/.test(url) ){ //Change this to .startsWith() when we use ES6
			return false;
		}

		//Ignore certain files
		if ( /\.(?:pdf|docx?|xlsx?|pptx?|zipx?|rar|tar|txt|rtf|ics|vcard)/.test(url) ){
			return false;
		}

		//Strip out unnecessary parts of the URL
		url = url.split('#')[0]; //Remove hashes

		//Ignore blocklisted terms (logout, 1-click purchase buttons, etc.)
		var prefetchBlocklist = ['logout'];

		//@todo "Nebula" 0: Allow other JS to add to the blocklist here... https://core.trac.wordpress.org/changeset/41375

		jQuery.each(prefetchBlocklist, function(index, value){
			if ( url.indexOf(value) != -1 ){
				return false;
			}
		});

		window.requestIdleCallback(function(){ //Wait until the browser is idle before prefetching
			if ( !jQuery('link[rel="prefetch"][href="' + url + '"]').length ){ //If prefetch link for this URL has not yet been added to the DOM
				jQuery('<link rel="prefetch" href="' + url + '">').on('load', callback).appendTo('head'); //Append a prefetch link element for this URL to the DOM
			}
		});
	}
};

//Send data to other tabs/windows using the Service Worker
nebula.postMessage = function(data){
	if ( navigator.serviceWorker && navigator.serviceWorker.controller ){ //@todo "Nebula" 0: Replace with optional chaining
		navigator.serviceWorker.controller.postMessage(data);
	}
};

/*==========================
 Detection Functions
 ===========================*/

//Cookie notification
nebula.cookieNotification = function(){
	if ( jQuery('#nebula-cookie-notification').length && !nebula.readCookie('acceptcookies') ){
		//Show the notice as soon as it will not interfere with loading nor become laggy
		window.requestAnimationFrame(function(){ //Change to requestIdleCallback when we stop supporting IE11 (still not supported in Safari either)
			jQuery('#nebula-cookie-notification').addClass('active');

			if ( !nebula.dom.body.hasClass('desktop') ){ //Desktop users (as detected by DeviceDetector or WordPress core) can use fixed positioning, but mobile must use absolute positioning
				jQuery('#nebula-cookie-notification').css({
					position: 'absolute',
					bottom: 'auto',
					top: window.innerHeight-jQuery('#nebula-cookie-notification').outerHeight()-40, //Window height - height of prompt - 40px spacing
				});
			}
		});

		//Hide the interface upon acceptance
		nebula.dom.document.on('click', '#nebula-cookie-accept', function(){
			nebula.createCookie('acceptcookies', true);

			window.requestAnimationFrame(function(){
				jQuery('#nebula-cookie-notification').removeClass('active');

				//Remove the entire element after the animation completes
				setTimeout(function(){
					jQuery('#nebula-cookie-notification').remove();
				}, 1000); //The animation is set to 750ms
			});

			return false;
		});
	}
};

//Check (or set) network availability (online/offline)
nebula.networkAvailable = function(){
	if ( navigator.onLine ){
		nebula.dom.body.removeClass('offline');

		if ( 'localStorage' in window ){
			localStorage.setItem('network_connection', 'online');
		}
	} else {
		nebula.dom.body.addClass('offline');

		if ( 'localStorage' in window ){
			localStorage.setItem('network_connection', 'offline');
		}
	}

	nebula.dom.document.trigger('nebula_network_change');
};

//Page Visibility
nebula.visibilityChangeActions = function(){
	if ( document.visibilityState === 'prerender' ){ //Page was prerendered
		ga('send', 'event', 'Page Visibility', 'Prerendered', 'Page loaded before tab/window was visible', {'nonInteraction': true});
		nebula.pauseAllVideos(false);
	}

	if ( document.visibilitystate === 'hidden' ){ //Page is hidden
		nebula.dom.document.trigger('nebula_page_hidden');
		nebula.dom.body.addClass('page-visibility-hidden');
		nebula.pauseAllVideos(false);
	} else { //Page is visible
		nebula.networkAvailable();
		nebula.dom.document.trigger('nebula_page_visible');
		nebula.dom.body.removeClass('page-visibility-hidden');
	}
};

//Record performance timing
nebula.performanceMetrics = function(){
	if ( (nebula.get('timings') || (nebula.has(nebula, 'user.staff') && nebula.user.staff === 'developer')) ){ //Only available to Developers or with ?timings //@todo "Nebula" 0: Replace with optional chaining
		if ( window.performance && window.performance.timing && typeof window.requestIdleCallback === 'function' ){ //Safari 11+ and no IE11 //@todo "Nebula" 0: Use optional chaining (and remove the idlecallback condition after IE11)

			window.requestIdleCallback(function(){
				window.performance.mark('(Nebula) CPU Idle');
				window.performance.measure('(Nebula) Until CPU Idle', 'navigationStart', '(Nebula) CPU Idle');

				var timingCalcuations = {
					'Redirect': {start: Math.round(performance.timing.redirectStart - performance.timing.navigationStart), duration: Math.round(performance.timing.redirectEnd - performance.timing.redirectStart)},
					'Unload': {start: Math.round(performance.timing.unloadStart - performance.timing.navigationStart), duration: Math.round(performance.timing.unloadEnd - performance.timing.unloadStart)},
					'App Cache': {start: Math.round(performance.timing.fetchStart - performance.timing.navigationStart), duration: Math.round(performance.timing.domainLookupStart - performance.timing.fetchStart)},
					'DNS': {start: Math.round(performance.timing.domainLookupStart - performance.timing.navigationStart), duration: Math.round(performance.timing.domainLookupEnd - performance.timing.domainLookupStart)},
					'TCP': {start: Math.round(performance.timing.connectStart - performance.timing.navigationStart), duration: Math.round(performance.timing.connectEnd - performance.timing.connectStart)},
					'Request': {start: Math.round(performance.timing.requestStart - performance.timing.navigationStart), duration: Math.round(performance.timing.responseStart - performance.timing.requestStart)},
					'Response': {start: Math.round(performance.timing.responseStart - performance.timing.navigationStart), duration: Math.round(performance.timing.responseEnd - performance.timing.responseStart)},
					'Processing': {start: Math.round(performance.timing.domLoading - performance.timing.navigationStart), duration: Math.round(performance.timing.loadEventStart - performance.timing.domLoading)},
					'onLoad': {start: Math.round(performance.timing.loadEventStart - performance.timing.navigationStart), duration: Math.round(performance.timing.loadEventEnd - performance.timing.loadEventStart)},
					'DOM Ready': {start: 0, duration: Math.round(performance.timing.domComplete - performance.timing.navigationStart)},
					'Total Load': {start: 0, duration: Math.round(performance.timing.loadEventEnd - performance.timing.navigationStart)},
					'CPU Idle': {start: 0, duration: Math.round(Date.now() - performance.timing.navigationStart)}
				};

				if ( typeof console.table === 'function' ){ //Remove condition after IE11 support
					var clientTimings = {};
					jQuery.each(timingCalcuations, function(name, timings){
						if ( !isNaN(timings.duration) && timings.duration > 0 && timings.duration < 6000000 ){ //Ignore empty values
							clientTimings[name] = {
								start: timings.start,
								duration: timings.duration,
								elapsed: timings.start + timings.duration
							};
						}
					});

					console.groupCollapsed('Performance');
					console.table(jQuery.extend(nebula.site.timings, clientTimings));
					console.groupEnd();
				}

				if ( timingCalcuations['Processing'] && timingCalcuations['DOM Ready'] && timingCalcuations['Total Load'] ){
					ga('set', nebula.analytics.metrics.serverResponseTime, timingCalcuations['Processing'].start);
					ga('set', nebula.analytics.metrics.domReadyTime, timingCalcuations['DOM Ready'].duration);
					ga('set', nebula.analytics.metrics.windowLoadedTime, timingCalcuations['Total Load'].duration);
					ga('send', 'event', 'Performance Timing', 'track', 'Used to deliver performance metrics to Google Analytics', {'nonInteraction': true});

					//Send as User Timings as well
					ga('send', 'timing', 'Performance Timing', 'Server Response', timingCalcuations['Processing'].start, 'Navigation start until server response finishes (includes PHP execution time)');
					ga('send', 'timing', 'Performance Timing', 'DOM Ready', timingCalcuations['DOM Ready'].duration, 'Navigation start until DOM ready');
					ga('send', 'timing', 'Performance Timing', 'Window Load', timingCalcuations['Total Load'].duration, 'Navigation start until window load');
					ga('send', 'timing', 'Performance Timing', 'CPU Idle', timingCalcuations['CPU Idle'].duration, 'Navigation start until CPU idle');
				}
			});
		}
	}
};

//Sub-menu viewport overflow detector
nebula.overflowDetector = function(){
	if ( jQuery('.sub-menu').length ){ //Only add the event listener if sub-menus actually exist
		jQuery('.menu li.menu-item').on({
			'mouseenter focus focusin': function(){
				if ( jQuery(this).children('.sub-menu').length ){ //Check if this menu has sub-menus
					var submenuLeft = jQuery(this).children('.sub-menu').offset().left; //Left side of the sub-menu
					var submenuRight = submenuLeft+jQuery(this).children('.sub-menu').width(); //Right side of the sub-menu

					if ( submenuRight > nebula.dom.window.width() ){ //If the right side is greater than the width of the viewport
						jQuery(this).children('.sub-menu').addClass('overflowing overflowing-left');
					} else if (submenuLeft > nebula.dom.window.width() ) {
						jQuery(this).children('.sub-menu').addClass('overflowing overflowing-right');
					} else {
						jQuery(this).children('.sub-menu').removeClass('overflowing overflowing-left overflowing-right');
					}
				}
			},
			'mouseleave': function(){
				jQuery(this).children('.sub-menu').removeClass('overflowing');
			}
		});
	}
};

//Check if the user has enabled DNT (if supported in their browser)
nebula.isDoNotTrack = function(){
	//Use server-side header detection first
	if ( nebula.has(nebula, 'user.dnt') ){ //@todo "Nebula" 0: Replace with optional chaining
		if ( nebula.user.dnt == 1 ){ //@todo "Nebula" 0: Use optional chaining and combine with above
			return true; //This user prefers not to be tracked
		} else {
			return false; //This user is allowing tracking.
		}
	}

	//Otherwise, check if the browser supports DNT
	if ( window.doNotTrack || navigator.doNotTrack || navigator.msDoNotTrack || 'msTrackingProtectionEnabled' in window.external ){
		//Check if DNT is enabled
		if ( window.doNotTrack == "1" || navigator.doNotTrack == "yes" || navigator.doNotTrack == "1" || navigator.msDoNotTrack == "1" || window.external.msTrackingProtectionEnabled() ){
			return true; //This user prefers not to be tracked
		} else {
			return false; //This user is allowing tracking.
		}
	}

	return false; //The browser does not support DNT
};

//Detect Network Connection
nebula.networkConnection = function(){
	var connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection || false;
	if ( connection ){
		nebula.user.client.device.connection = {
			type: connection.type,
			metered: connection.metered,
			bandwidth: connection.bandwidth,
		};
	}
};

/*==========================
 Social Functions
 ===========================*/

//Load the SDK asynchronously
nebula.facebookSDK = function(){
	if ( jQuery('[class*="fb-"]:not(.fb-root)').length || jQuery('.require-fbsdk').length ){ //Only load the Facebook SDK when needed
		(function(d, s, id) {
			var js, fjs = d.getElementsByTagName(s)[0];
			if (d.getElementById(id)) return;
			js = d.createElement(s); js.id = id;
			js.src = 'https://connect.facebook.net/' + nebula.site.charset + '/all.js#xfbml=1&version=v3.0';
			fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));
	}
};


//Social sharing buttons
nebula.socialSharing = function(){
	if ( jQuery('.fbshare, a.nebula-share.facebook, .twshare, a.nebula-share-btn.twitter, .lishare, a.nebula-share-btn.linkedin, .pinshare, a.nebula-share-btn.pinterest, .emshare, a.nebula-share-btn.email, a.nebula-share.webshare, a.nebula-share.shareapi').length ){ //If any of the Nebula sharing classes are used
		var encloc = encodeURIComponent(window.location.href);
		var enctitle = encodeURIComponent(document.title);
		var popupTop = nebula.dom.window.height()/2-275;
		var popupLeft = nebula.dom.window.width()/2-225;
		var popupAttrs = 'top=' + popupTop + ', left=' + popupLeft + ', toolbar=0, location=0, menubar=0, directories=0, scrollbars=0, chrome=yes, personalbar=0';

		//These event will need to correspond to the GA4 event name "share" and use "content_type" and "item_id" as parameters: https://support.google.com/analytics/answer/9267735

		//Facebook
		jQuery('.fbshare, a.nebula-share.facebook').attr('href', 'http://www.facebook.com/sharer.php?u=' + encloc + '&t=' + enctitle).attr({'target': '_blank', 'rel': 'noopener'}).on('click', function(e){
			var thisEvent = {
				event: e,
				category: 'Social',
				action: 'Share', //GA4 Name: "share"
				intent: 'Intent',
				network: 'Facebook',
				url: window.location.href,
				title: document.title
			};

			ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.network);
			nebula.crm('event', thisEvent.network + ' ' + thisEvent.action);

			if ( nebula.dom.body.hasClass('desktop') ){
				window.open(jQuery(this).attr('href'), 'facebookShareWindow', 'width=550, height=450, ' + popupAttrs);
				return false;
			}
		});

		//Twitter
		jQuery('.twshare, a.nebula-share-btn.twitter').attr('href', 'https://twitter.com/intent/tweet?url=' + encloc + '&text=' + enctitle).attr({'target': '_blank', 'rel': 'noopener'}).on('click', function(e){
			var thisEvent = {
				event: e,
				category: 'Social',
				action: 'Share', //GA4 Name: "share"
				intent: 'Intent',
				network: 'Twitter',
				url: window.location.href,
				title: document.title
			};

			ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.network);
			nebula.crm('event', thisEvent.network + ' ' + thisEvent.action);

			if ( nebula.dom.body.hasClass('desktop') ){
				window.open(jQuery(this).attr('href'), 'twitterShareWindow', 'width=600, height=254, ' + popupAttrs);
				return false;
			}
		});

		//LinkedIn
		jQuery('.lishare, a.nebula-share-btn.linkedin').attr('href', 'http://www.linkedin.com/shareArticle?mini=true&url=' + encloc + '&title=' + enctitle).attr({'target': '_blank', 'rel': 'noopener'}).on('click', function(e){
			var thisEvent = {
				event: e,
				category: 'Social',
				action: 'Share', //GA4 Name: "share"
				intent: 'Intent',
				network: 'LinkedIn',
				url: window.location.href,
				title: document.title
			};

			ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.network);
			nebula.crm('event', thisEvent.network + ' ' + thisEvent.action);

			if ( nebula.dom.body.hasClass('desktop') ){
				window.open(jQuery(this).attr('href'), 'linkedinShareWindow', 'width=600, height=473, ' + popupAttrs);
				return false;
			}
		});

		//Pinterest
		jQuery('.pinshare, a.nebula-share-btn.pinterest').attr('href', 'http://pinterest.com/pin/create/button/?url=' + encloc).attr({'target': '_blank', 'rel': 'noopener'}).on('click', function(e){
			var thisEvent = {
				event: e,
				category: 'Social',
				action: 'Share', //GA4 Name: "share"
				intent: 'Intent',
				network: 'Pinterest',
				url: window.location.href,
				title: document.title
			};

			ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.network);
			nebula.crm('event', thisEvent.network + ' ' + thisEvent.action);

			if ( nebula.dom.body.hasClass('desktop') ){
				window.open(jQuery(this).attr('href'), 'pinterestShareWindow', 'width=600, height=450, ' + popupAttrs);
				return false;
			}
		});

		//Email
		jQuery('.emshare, a.nebula-share-btn.email').attr('href', 'mailto:?subject=' + enctitle + '&body=' + encloc).attr({'target': '_blank', 'rel': 'noopener'}).on('click', function(e){
			var thisEvent = {
				event: e,
				category: 'Social',
				action: 'Share', //GA4 Name: "share"
				intent: 'Intent',
				network: 'Email',
				url: window.location.href,
				title: document.title
			};

			ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.network);
			nebula.crm('event', thisEvent.network + ' ' + thisEvent.action);
		});

		//Web Share API: https://caniuse.com/mdn-api_navigator_share
		if ( 'share' in navigator && !nebula.dom.body.hasClass('desktop') ){ //Chrome 61+
			nebula.dom.document.on('click', 'a.nebula-share.webshare, a.nebula-share.shareapi', function(){
				var oThis = jQuery(this);
				var originalText = oThis.html();

				navigator.share({
					title: document.title,
					text: nebula.post.excerpt,
					url: window.location.href
				}).then(function(){
					var thisEvent = {
						event: e,
						category: 'Social',
						action: 'Share', //GA4 Name: "share"
						intent: 'Intent',
						network: 'Web Share API',
						url: window.location.href,
						title: document.title,
					};

					nebula.dom.document.trigger('nebula_event', thisEvent);
					ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.network);
					nebula.crm('event', thisEvent.network);
					oThis.addClass('success');
					nebula.createCookie('shareapi', true);
				}).catch(function(error){ //This can happen on iOS when the user closes the drawer without sharing
					ga('send', 'exception', {'exDescription': '(JS) Share API Error: ' + error, 'exFatal': false});
					oThis.addClass('error').html(originalText);
					nebula.createCookie('shareapi', false);
				});

				return false;
			});

			nebula.createCookie('shareapi', true); //Set a cookie to speed up future page loads by not loading third-party share buttons.
		} else {
			jQuery('a.nebula-share.webshare, a.nebula-share.shareapi').addClass('hidden');
		}
	}
};

/*==========================
 Analytics Functions
 ===========================*/

//Google Analytics Universal Analytics Event Trackers
nebula.eventTracking = function(){
	if ( nebula.isDoNotTrack() ){
		return false;
	}

	nebula.cacheSelectors(); //If event tracking is initialized by the async GA callback, selectors won't be cached yet

	if ( typeof window.ga === 'function' ){
		window.ga(function(tracker){
			nebula.dom.document.trigger('nebula_ga_tracker', tracker);
			nebula.user.cid = tracker.get('clientId');
		});
	}

	nebula.dom.document.trigger('nebula_event_tracking');

	nebula.once(function(){
		window.dataLayer = window.dataLayer || []; //Prevent overwriting an existing GTM Data Layer array

		//Btn Clicks
		nebula.dom.document.on('mousedown', "button, .btn, [role='button'], a.wp-block-button__link, .hs-button", function(e){
			var thisEvent = {
				event: e,
				category: 'Button',
				action: 'Click', //GA4 Name: "button_click"?
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				text: jQuery(this).val() || jQuery(this).text() || '(Unknown)',
				link: jQuery(this).attr('href')
			};

			ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', 'Button Click', thisEvent.text.trim(), thisEvent.link);
			window.dataLayer.push({'event': 'nebula-button-click', 'nebula-event': thisEvent});
		});

		//Bootstrap "Collapse" Accordions
		nebula.dom.document.on('shown.bs.collapse', function(e){
			var thisEvent = {
				event: e,
				category: 'Accordion',
				action: 'Shown', //GA4 Name: "accordion_toggle"?
				label: jQuery('[data-target="#' + e.target.id + '"]').text().trim() || e.target.id,
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
			window.dataLayer.push({'event': 'nebula-accordion-shown', 'nebula-event': thisEvent});
		});
		nebula.dom.document.on('hidden.bs.collapse', function(e){
			var thisEvent = {
				event: e,
				category: 'Accordion',
				action: 'Hidden', //GA4 Name: "accordion_toggle"?
				label: jQuery('[data-target="#' + e.target.id + '"]').text().trim() || e.target.id,
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
			window.dataLayer.push({'event': 'nebula-accordion-hidden', 'nebula-event': thisEvent});
		});

		//Bootstrap Modals
		nebula.dom.document.on('shown.bs.modal', function(e){
			var thisEvent = {
				event: e,
				category: 'Modal',
				action: 'Shown', //GA4 Name: "modal_toggle"?
				label: jQuery('#' + e.target.id + ' .modal-title').text().trim() || e.target.id,
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
			window.dataLayer.push({'event': 'nebula-modal-shown', 'nebula-event': thisEvent});
		});
		nebula.dom.document.on('hidden.bs.modal', function(e){
			var thisEvent = {
				event: e,
				category: 'Modal',
				action: 'Hidden', //GA4 Name: "modal_toggle"?
				label: jQuery('#' + e.target.id + ' .modal-title').text().trim() || e.target.id,
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
			window.dataLayer.push({'event': 'nebula-modal-hidden', 'nebula-event': thisEvent});
		});

		//Bootstrap Carousels (Sliders)
		nebula.dom.document.on('slide.bs.carousel', function(e){
			if ( window.event ){ //Only if sliding manually
				var thisEvent = {
					event: e,
					category: 'Carousel',
					action: e.target.id || e.target.title || e.target.className.replace(/\s/g, '.'), //GA4 Name: "carousel_slide"?
					from: e.from,
					to: e.to,
				};

				thisEvent.activeSlide = jQuery(e.target).find('.carousel-item').eq(e.to);
				thisEvent.activeSlideName = thisEvent.activeSlide.attr('id') || thisEvent.activeSlide.attr('title') || 'Unnamed';
				thisEvent.prevSlide = jQuery(e.target).find('.carousel-item').eq(e.from);
				thisEvent.prevSlideName = thisEvent.prevSlide.attr('id') || thisEvent.prevSlide.attr('title') || 'Unnamed';
				thisEvent.label = 'Slide to ' + thisEvent.to + ' (' + thisEvent.activeSlideName + ') from ' + thisEvent.from + ' (' + thisEvent.prevSlideName + ')';

				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
				window.dataLayer.push({'event': 'nebula-carousel-slide', 'nebula-event': thisEvent});
			}
		});

		//Generic Form Submissions
		//This event will be a duplicate if proper event tracking is setup on each form, but serves as a safety net.
		//It is not recommended to use this event for goal tracking unless absolutely necessary (this event does not check for submission success)!
		nebula.dom.document.on('submit', 'form', function(e){
			var thisEvent = {
				event: e,
				category: 'Generic Form',
				action: 'Submit', //GA4 Name: "form_submit"? How to differentiate it from conversions?
				formID: e.target.id || 'form.' + e.target.className.replace(/\s/g, '.'),
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.formID);
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.action);}
			window.dataLayer.push({'event': 'nebula-generic-form', 'nebula-event': thisEvent});
		});

		//Notable File Downloads
		jQuery.each(['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv', 'zip', 'zipx', 'rar', 'gz', 'tar', 'txt', 'rtf', 'ics', 'vcard'], function(index, extension){
			nebula.dom.document.on('mousedown', "a[href$='." + extension + "'], a[href$='." + extension.toUpperCase() + "']", function(e){ //Make this a case insensitive attribute selector after IE11 support ends: a[href$='.pdf' i]
				var thisEvent = {
					event: e,
					category: 'Download',
					action: extension, //GA4 Name: "file_download" Note: This is a default GA4 event and is not needed to be tracked in Nebula. Consider deleting entirely.
					intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
					extension: extension,
					fileName: jQuery(this).attr('href').substr(jQuery(this).attr('href').lastIndexOf("/")+1),
				};

				ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.fileName);
				window.dataLayer.push({'event': 'nebula-download', 'nebula-event': thisEvent});
				if ( typeof fbq === 'function' ){fbq('track', 'ViewContent', {content_name: thisEvent.fileName});}
				if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.fileName);}
				nebula.crm('event', 'File Download');
			});
		});

		//Notable Downloads
		nebula.dom.document.on('mousedown', ".notable a, a.notable", function(e){
			var thisEvent = {
				event: e,
				category: 'Download',
				action: 'Notable', //GA4 Name: "file_download"
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				filePath: jQuery(this).attr('href').trim(),
				linkText: jQuery(this).text()
			};

			if ( thisEvent.filePath.length && thisEvent.filePath !== '#' ){
				thisEvent.fileName = filePath.substr(filePath.lastIndexOf("/")+1);
				ga('set', nebula.analytics.metrics.notableDownloads, 1);
				nebula.dom.document.trigger('nebula_event', thisEvent);

				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.fileName);
				window.dataLayer.push({'event': 'nebula-download', 'nebula-event': thisEvent});
				if ( typeof fbq === 'function' ){fbq('track', 'ViewContent', {content_name: thisEvent.fileName});}
				if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.fileName);}
				nebula.crm('event', 'Notable File Download');
			}
		});

		//Generic Internal Search Tracking
		//This event will need to correspond to the GA4 event name "search" and use "search_term" as a parameter: https://support.google.com/analytics/answer/9267735
		nebula.dom.document.on('submit', '#s, input.search', function(){
			var thisEvent = {
				event: e,
				category: 'Internal Search',
				action: 'Submit', //GA4 Name: "search"
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				query: jQuery(this).find('input[name="s"]').val().toLowerCase().trim()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.query);
			window.dataLayer.push({'event': 'nebula-internal-search', 'nebula-event': thisEvent});
			if ( typeof fbq === 'function' ){fbq('track', 'Search', {search_string: thisEvent.query});}
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.query);}
			nebula.crm('identify', {internal_search: thisEvent.query});
		});

		//Keyboard Shortcut (Non-interaction because they are not taking explicit action with the webpage)
		nebula.dom.document.on('keydown', function(e){
			window.modifiedZoomLevel = window.modifiedZoomLevel || 0; //Scope to window so it is not reset every event. Note: This is just how it was modified and not the actual zoom level! Zoom level is saved between pageloads so it may have started at non-zero!

			//Ctrl+ (Zoom In)
			if ( (e.ctrlKey || e.metaKey) && (e.which === 187 || e.which === 107) ){ //187 is plus (and equal), 107 is plus on the numpad
				modifiedZoomLevel++; //Increment the zoom level iterator

				var thisEvent = {
					event: e,
					category: 'Keyboard Shortcut',
					action: 'Zoom In (Ctrl+)', //GA4 Name: "zoom_change"?
					modifiedZoomLevel: modifiedZoomLevel
				};

				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, 'Modified Zoom Level: ' + thisEvent.modifiedZoomLevel, {'nonInteraction': true});
				window.dataLayer.push({'event': 'nebula-keyboard-shortcut', 'nebula-event': thisEvent});
			}

			//Ctrl- (Zoom Out)
			if ( (e.ctrlKey || e.metaKey) && (e.which === 189 || e.which === 109) ){ //189 is minus, 109 is minus on the numpad
				modifiedZoomLevel--; //Decrement the zoom level iterator

				var thisEvent = {
					event: e,
					category: 'Keyboard Shortcut',
					action: 'Zoom Out (Ctrl-)', //GA4 Name: "zoom_change"?
					modifiedZoomLevel: modifiedZoomLevel
				};

				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, 'Modified Zoom Level: ' + thisEvent.modifiedZoomLevel, {'nonInteraction': true});
				window.dataLayer.push({'event': 'nebula-keyboard-shortcut', 'nebula-event': thisEvent});
			}

			//Ctrl+0 (Reset Zoom)
			if ( (e.ctrlKey || e.metaKey) && (e.which === 48 || e.which === 0 || e.which === 96) ){ //48 is 0 (Mac), 0 is Windows 0, and 96 is Windows numpad
				modifiedZoomLevel = 0; //Reset the zoom level iterator

				var thisEvent = {
					event: e,
					category: 'Keyboard Shortcut',
					action: 'Reset Zoom (Ctrl+0)', //GA4 Name: "zoom_change"?
					modifiedZoomLevel: modifiedZoomLevel
				};

				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, 'Modified Zoom Level: ' + thisEvent.modifiedZoomLevel, {'nonInteraction': true});
				window.dataLayer.push({'event': 'nebula-keyboard-shortcut', 'nebula-event': thisEvent});
			}

			//Ctrl+F or Cmd+F (Find)
			if ( (e.ctrlKey || e.metaKey) && e.which === 70 ){
				var thisEvent = {
					event: e,
					category: 'Keyboard Shortcut',
					action: 'Find on Page (Ctrl+F)', //GA4 Name: "search" but we will not have a "search_term" parameter. Make sure we do have something to note that this is a Find On Page
					highlightedText: window.getSelection().toString().trim() || '(No highlighted text when initiating find)'
				};

				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.highlightedText, {'nonInteraction': true});
				window.dataLayer.push({'event': 'nebula-keyboard-shortcut', 'nebula-event': thisEvent});
			}

			//Ctrl+D or Cmd+D (Bookmark)
			if ( (e.ctrlKey || e.metaKey) && e.which === 68 ){ //Ctrl+D
				var thisEvent = {
					event: e,
					category: 'Keyboard Shortcut',
					action: 'Bookmark (Ctrl+D)', //GA4 Name: "bookmark"?
					label: 'User bookmarked the page (with keyboard shortcut)'
				};

				nebula.removeQueryParameter(['utm_campaign', 'utm_medium', 'utm_source', 'utm_content', 'utm_term'], window.location.href); //Remove existing UTM parameters
				history.replaceState(null, document.title, window.location.href + '?utm_source=bookmark');
				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label, {'nonInteraction': true});
				window.dataLayer.push({'event': 'nebula-keyboard-shortcut', 'nebula-event': thisEvent});
			}
		});

		//Mailto link tracking
		nebula.dom.document.on('mousedown', 'a[href^="mailto"]', function(e){
			var thisEvent = {
				event: e,
				category: 'Contact',
				action: 'Mailto', //GA4 Name: "mailto"?
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				emailAddress: jQuery(this).attr('href').replace('mailto:', '')
			};

			ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
			ga('set', nebula.analytics.dimensions.contactMethod, thisEvent.action);
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.emailAddress);
			window.dataLayer.push({'event': 'nebula-mailto', 'nebula-event': thisEvent});
			if ( typeof fbq === 'function' ){fbq('track', 'Lead', {content_name: thisEvent.action});}
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.action);}
			nebula.crm('event', thisEvent.action);
			nebula.crm('identify', {mailto_contacted: thisEvent.emailAddress});
		});

		//Telephone link tracking
		nebula.dom.document.on('mousedown', 'a[href^="tel"]', function(e){
			var thisEvent = {
				event: e,
				category: 'Contact',
				action: 'Click-to-Call', //GA4 Name: "click_to_call"?
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				phoneNumber: jQuery(this).attr('href').replace('tel:', '')
			};

			ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
			ga('set', nebula.analytics.dimensions.contactMethod, thisEvent.action);
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.phoneNumber);
			window.dataLayer.push({'event': 'nebula-click-to-call', 'nebula-event': thisEvent});
			if ( typeof fbq === 'function' ){fbq('track', 'Lead', {content_name: thisEvent.action});}
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.action);}
			nebula.crm('event', thisEvent.action);
			nebula.crm('identify', {phone_contacted: thisEvent.phoneNumber});
		});

		//SMS link tracking
		nebula.dom.document.on('mousedown', 'a[href^="sms"]', function(e){
			var thisEvent = {
				event: e,
				category: 'Contact',
				action: 'SMS', //GA4 Name: "sms"?
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				phoneNumber: jQuery(this).attr('href').replace('tel:', '')
			};

			ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
			ga('set', nebula.analytics.dimensions.contactMethod, thisEvent.action);
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.phoneNumber);
			window.dataLayer.push({'event': 'nebula-sms', 'nebula-event': thisEvent});
			if ( typeof fbq === 'function' ){fbq('track', 'Lead', {content_name: thisEvent.action});}
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.action);}
			nebula.crm('event', thisEvent.action);
			nebula.crm('identify', {phone_contacted: thisEvent.phoneNumber});
		});

		//Street Address click //@todo "Nebula" 0: How to detect when a user clicks an address that is not linked, but mobile opens the map anyway? What about when it *is* linked?

		//Utility Navigation Menu
		nebula.dom.document.on('mousedown', '#utility-nav ul.menu a', function(e){
			var thisEvent = {
				event: e,
				category: 'Navigation Menu',
				action: 'Utility Menu', //GA4 Name: "menu_click"?
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				linkText: jQuery(this).text().trim()
			};

			ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.linkText);
			window.dataLayer.push({'event': 'nebula-navigation-menu-click', 'nebula-event': thisEvent});
		});

		//Primary Navigation Menu
		nebula.dom.document.on('mousedown', '#primary-nav ul.menu a', function(e){
			var thisEvent = {
				event: e,
				category: 'Navigation Menu',
				action: 'Primary Menu', //GA4 Name: "menu_click"?
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				linkText: jQuery(this).text().trim()
			};

			ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.linkText);
			window.dataLayer.push({'event': 'nebula-navigation-menu-click', 'nebula-event': thisEvent});
		});

		//Mobile Navigation Menu
		nebula.dom.document.on('mousedown', '#mobilenav ul.menu a.mm-listitem__text', function(e){
			var thisEvent = {
				event: e,
				category: 'Navigation Menu',
				action: 'Mobile Menu', //GA4 Name: "menu_click"?
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				linkText: jQuery(this).text().trim()
			};

			ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.linkText);
			window.dataLayer.push({'event': 'nebula-navigation-menu-click', 'nebula-event': thisEvent});
		});

		//Breadcrumb Navigation
		nebula.dom.document.on('mousedown', 'ol.nebula-breadcrumbs a', function(e){
			var thisEvent = {
				event: e,
				category: 'Navigation Menu',
				action: 'Breadcrumbs', //GA4 Name: "menu_click"?
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				linkText: jQuery(this).text().trim()
			};

			ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.linkText);
			window.dataLayer.push({'event': 'nebula-navigation-menu-click', 'nebula-event': thisEvent});
		});

		//Sidebar Navigation Menu
		nebula.dom.document.on('mousedown', '#sidebar-section ul.menu a', function(e){
			var thisEvent = {
				event: e,
				category: 'Navigation Menu',
				action: 'Sidebar Menu', //GA4 Name: "menu_click"?
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				linkText: jQuery(this).text().trim()
			};

			ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.linkText);
			window.dataLayer.push({'event': 'nebula-navigation-menu-click', 'nebula-event': thisEvent});
		});

		//Footer Navigation Menu
		nebula.dom.document.on('mousedown', '#powerfooter ul.menu a', function(e){
			var thisEvent = {
				event: e,
				category: 'Navigation Menu',
				action: 'Footer Menu', //GA4 Name: "menu_click"?
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				linkText: jQuery(this).text().trim()
			};

			ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.linkText);
			window.dataLayer.push({'event': 'nebula-navigation-menu-click', 'nebula-event': thisEvent});
		});

		//Nebula Cookie Notification link clicks
		nebula.dom.document.on('mousedown', '#nebula-cookie-notification a', function(e){
			var thisEvent = {
				event: e,
				category: 'Cookie Notification',
				action: 'Click', //GA4 Name: "cookie_notification"?
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				text: jQuery(this).text(),
				link: jQuery(this).attr('href')
			};

			ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.text.trim(), thisEvent.link, {'nonInteraction': true}); //Non-interaction because the user is not interacting with any content yet so this should not influence the bounce rate
			window.dataLayer.push({'event': 'nebula-cookie-notification-click', 'nebula-event': thisEvent});
		});

		//History Popstate (dynamic URL changes via the History API when "states" are pushed into the browser history)
		if ( typeof history.pushState === 'function' ){
			nebula.dom.window.on('popstate', function(e){ //When a state that was previously pushed is used, or "popped". This *only* triggers when a pushed state is popped!
				var thisEvent = {
					event: e,
					category: 'History Popstate',
					action: document.title,
					location: document.location,
					state: JSON.stringify(e.state)
				};

				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.location);
			});
		}

		//Dead Clicks (Non-Linked Click Attempts)
		nebula.dom.document.on('click', 'img', function(e){
			if ( !jQuery(this).parents('a, button').length ){
				var thisEvent = {
					event: e,
					category: 'Dead Click',
					action: 'Image', //GA4 Name: "dead_click"?
					element: 'Image',
					src: jQuery(this).attr('src')
				};

				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.src, {'nonInteraction': true}); //Non-interaction because if the user leaves due to this it should be considered a bounce
				window.dataLayer.push({'event': 'nebula-dead-click', 'nebula-event': thisEvent});
				nebula.crm('event', thisEvent.category);
			}
		});

		//Detect "Rage Clicks"
		var clickEvents = [];
		nebula.dom.document.on('click', 'body', function(e){
			//Ignore clicks on certain elements that typically incur many clicks
			if ( jQuery(this).is('input[type="number"]') ){
				return null;
			}

			clickEvents.push({
				event: e,
				time: new Date()
			});

			//Keep only required number of click events and remove left of them.
			if ( clickEvents.length > 5 ){
				clickEvents.splice(0, clickEvents.length - 5);
			}

			//Detect 3 clicks in 5 seconds
			if ( clickEvents.length >= 5 ){
				var numberOfClicks = 5; //Number of clicks to detect within the period
				var period = 3; //The period to listen for the number of clicks

				var last = clickEvents.length - 1;
				var timeDiff = (clickEvents[last].time.getTime() - clickEvents[last - numberOfClicks + 1].time.getTime()) / 1000;

				//Ignore event periods longer than desired
				if ( timeDiff > period ){
					return null; //Return null because false will prevent regular clicks!
				}

				//Loop through the last number of click events to check the distance between them
				var max_distance = 0;
				for ( var i = last - numberOfClicks+1; i < last; i++ ){
					for ( var j = i+1; j <= last; j++ ){
						var distance = Math.round(Math.sqrt(Math.pow(clickEvents[i].event.clientX - clickEvents[j].event.clientX, 2) + Math.pow(clickEvents[i].event.clientY - clickEvents[j].event.clientY, 2)));
						if ( distance > max_distance ){
							max_distance = distance;
						}

						//Ignore if distance is outside 100px radius
						if ( distance > 100 ){
							return null; //Return null because false will prevent regular clicks!
						}
					}
				}

				var thisEvent = {
					event: e,
					category: 'Rage Clicks',
					action: 'Detected', //GA4 Name: "rage_clicks"?
					clicks: numberOfClicks,
					period: timeDiff,
					selector: nebula.domTreeToString(e.target),
				};

				thisEvent.description = numberOfClicks + ' clicks in ' + timeDiff + ' seconds detected within ' + max_distance + 'px of ' + thisEvent.selector;

				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.description, {'nonInteraction': true}); //Non-interaction because if the user exits due to this it should be considered a bounce
				window.dataLayer.push({'event': 'nebula-rage-click', 'nebula-event': thisEvent});

				clickEvents.splice(clickEvents.length-5, 5); //Remove unused click points
			}
		});

		//Skip to Content and other screen reader links Focus/Clicks
		nebula.dom.document.on('focus', '.sr-only', function(e){
			var thisEvent = {
				event: e,
				category: 'Accessibility Links',
				action: 'Focus', //GA4 Name: "accessibility_links"?
				linkText: jQuery(this).text().trim()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.linkText);
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.action);}
			window.dataLayer.push({'event': 'nebula-accessibility-link', 'nebula-event': thisEvent});
		});

		//Screenreader Links
		nebula.dom.document.on('click', '.sr-only', function(e){
			var thisEvent = {
				event: e,
				category: 'Accessibility Links',
				action: 'Click', //GA4 Name: "accessibility_links"?
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				linkText: jQuery(this).text().trim()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.linkText);
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.action);}
			window.dataLayer.push({'event': 'nebula-accessibility-link', 'nebula-event': thisEvent});
		});

		//Video Enter Picture-in-Picture //https://caniuse.com/#feat=picture-in-picture
		nebula.dom.document.on('enterpictureinpicture', 'video', function(e){
			var thisEvent = {
				event: e,
				category: 'Videos',
				action: 'Enter Picture-in-Picture',  //GA4 Name: "video_pip"?
				videoID: e.target.id
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.videoID, {'nonInteraction': true}); //Non-interaction because this may not be triggered by the user.
		});

		//Video Leave Picture-in-Picture
		nebula.dom.document.on('leavepictureinpicture', 'video', function(e){
			var thisEvent = {
				event: e,
				category: 'Videos',
				action: 'Leave Picture-in-Picture', //GA4 Name: "video_pip"?
				videoID: e.target.id
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.videoID, {'nonInteraction': true}); //Non-interaction because this may not be triggered by the user.
		});

		//Word copy tracking
		var copyCount = 0;
		nebula.dom.document.on('cut copy', function(){
			var selection = window.getSelection().toString();
			var words = selection.split(' ');
			var wordsLength = words.length;

			//Track Email or Phone copies as contact intent.
			var emailPhoneAddress = words.join(' ').trim();
			if ( nebula.regex.email.test(emailPhoneAddress) ){
				var thisEvent = {
					category: 'Contact',
					action: 'Email (Copy)', //GA4 Name: "mailto"?
					intent: 'Intent',
					emailAddress: emailPhoneAddress,
					selection: selection,
					words: words,
					wordcount: wordsLength
				};

				ga('set', nebula.analytics.dimensions.contactMethod, 'Mailto');
				ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.emailAddress);
				nebula.crm('event', 'Email Address Copied');
				nebula.crm('identify', {mailto_contacted: thisEvent.emailAddress});
			} else if ( nebula.regex.address.test(emailPhoneAddress) ){
				var thisEvent = {
					category: 'Contact',
					action: 'Street Address (Copy)',
					intent: 'Intent',
					address: emailPhoneAddress,
					selection: selection,
					words: words,
					wordcount: wordsLength
				};

				ga('set', nebula.analytics.dimensions.contactMethod, 'Street Address');
				ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.address);
				nebula.crm('event', 'Street Address Copied');
			} else {
				var alphanumPhone = emailPhoneAddress.replace(/\W/g, ''); //Keep only alphanumeric characters
				var firstFourNumbers = parseInt(alphanumPhone.substring(0, 4)); //Store the first four numbers as an integer

				//If the first three/four chars are numbers and the full string is either 10 or 11 characters (to capture numbers with words) -or- if it matches the phone RegEx pattern
				if ( (!isNaN(firstFourNumbers) && firstFourNumbers.toString().length >= 3 && (alphanumPhone.length === 10 || alphanumPhone.length === 11)) || nebula.regex.phone.test(emailPhoneAddress) ){
					var thisEvent = {
						category: 'Contact',
						action: 'Phone (Copy)', //GA4 Name: "click_to_call"?
						intent: 'Intent',
						phoneNumber: emailPhoneAddress,
						selection: selection,
						words: words,
						wordcount: wordsLength
					};

					ga('set', nebula.analytics.dimensions.contactMethod, 'Click-to-Call');
					ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
					nebula.dom.document.trigger('nebula_event', thisEvent);
					ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.phoneNumber);
					nebula.crm('event', 'Phone Number Copied');
					nebula.crm('identify', {phone_contacted: thisEvent.phoneNumber});
				}
			}

			var thisEvent = {
				category: 'Copied Text',
				action: 'Copy', //This is not used for the below events //GA4 Name: "copy_text"?
				intent: 'Intent',
				phoneNumber: emailPhoneAddress,
				selection: selection,
				words: words,
				wordcount: wordsLength
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);

			if ( copyCount < 5 ){
				if ( words.length > 8 ){
					words = words.slice(0, 8).join(' ');
					ga('send', 'event', thisEvent.category, words.length + ' words', words + '... [' + wordsLength + ' words]'); //GA4: This will need to change significantly. Event Name: "copy_text"?
				} else {
					if ( selection.trim() === '' ){
						ga('send', 'event', thisEvent.category, '[0 words]'); //GA4: This will need to change significantly. Event Name: "copy_text"?
					} else {
						ga('send', 'event', thisEvent.category, words.length + ' words', selection, words.length); //GA4: This will need to change significantly. Event Name: "copy_text"?
					}
				}

				ga('send', 'event', thisEvent.category, words.length + ' words', words + '... [' + wordsLength + ' words]'); //GA4: This will need to change significantly. Event Name: "copy_text"?
				window.dataLayer.push({'event': 'nebula-copied-text', 'nebula-event': thisEvent});
				nebula.crm('event', 'Text Copied');
			}

			copyCount++;
		});

		//AJAX Errors
		nebula.dom.document.ajaxError(function(e, jqXHR, settings, thrownError){
			var errorMessage = thrownError;
			if ( jqXHR.status === 0 ){ //A status of 0 means the error is unknown. Possible network connection issue (like a blocked request).
				errorMessage = 'Unknown error';
			}

			ga('send', 'exception', {'exDescription': '(JS) AJAX Error (' + jqXHR.status + '): ' + errorMessage + ' on ' + settings.url, 'exFatal': true});
			window.dataLayer.push({'event': 'nebula-ajax-error', 'nebula-event': errorMessage});
			nebula.crm('event', 'AJAX Error');
		});

		//Window Errors
		window.addEventListener('error', function(error){
			var errorMessage = error.message + ' at ' + error.lineno + ' of ' + error.filename;
			if ( error.message.toLowerCase().indexOf('script error') > -1 ){ //If it is a script error
				errorMessage = 'Script error (An error occurred in a script hosted on a different domain)'; //No additional information is available because of the browser's same-origin policy. Use CORS when possible to get additional information.
			}

			ga('send', 'exception', {'exDescription': '(JS) ' + errorMessage, 'exFatal': false}); //Is there a better way to detect fatal vs non-fatal errors?
			window.dataLayer.push({'event': 'nebula-window-error', 'nebula-event': errorMessage});
			nebula.crm('event', 'JavaScript Error');
			nebula.usage(error);
		});

		//Reporting Observer deprecations and interventions
		//@todo Nebula 0: This may be causing "aw snap" errors in Chrome. Disabling for now until the feature is more stable.
		//https://caniuse.com/#feat=mdn-api_reportingobserver
	/*
		if ( typeof window.ReportingObserver !== 'undefined' ){ //Chrome 68+
			var nebulaReportingObserver = new ReportingObserver(function(reports, observer){
				for ( report of reports ){
					if ( report.body.sourceFile.indexOf('extension') < 0 ){ //Ignore browser extensions
						ga('send', 'exception', {'exDescription': '(JS) Reporting Observer [' + report.type + ']: ' + report.body.message + ' in ' + report.body.sourceFile + ' on line ' + report.body.lineNumber, 'exFatal': false});
					}
				}
			}, {buffered: true});
			nebulaReportingObserver.observe();
		}
	*/

		//Capture Print Intent
		//Note: This sends 2 events per print (beforeprint and afterprint). If one occurs more than the other we can remove one.
		if ( 'matchMedia' in window ){ //IE10+
			var mediaQueryList = window.matchMedia('print');
			mediaQueryList.addListener(function(mql){
				if ( mql.matches ){
					sendPrintEvent('Before Print', 'mql.matches');
				} else {
					sendPrintEvent('After Print', '!mql.matches');
				}
			});
		} else {
			window.onbeforeprint = sendPrintEvent('Before Print', 'onbeforeprint');
			window.onafterprint = sendPrintEvent('After Print', 'onafterprint');
		}
		function sendPrintEvent(action, trigger){
			var thisEvent = {
				category: 'Print',
				action: action, //GA4 Name: "print"?
				label: 'User triggered print via ' + trigger,
				intent: 'Intent'
			};

			ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.action);}
			window.dataLayer.push({'event': 'nebula-print', 'nebula-event': thisEvent});
			nebula.crm('event', thisEvent.category);
		}

		//Detect Adblock
		if ( nebula.user.client.bot === false && nebula.site.options.adblock_detect ){
			window.performance.mark('(Nebula) Detect AdBlock [Start]');
			jQuery.ajax({ //Eventually update this to fetch with ES6
				type: 'GET',
				url: nebula.site.directory.template.uri + '/assets/js/vendor/show_ads.js',
				dataType: 'script',
				cache: true,
				timeout: 5000
			}).done(function(){
				nebula.session.flags.adblock = false;
			}).fail(function(){
				nebula.dom.html.addClass('ad-blocker');
				ga('set', nebula.analytics.dimensions.blocker, 'Ad Blocker');
				if ( nebula.session.flags.adblock != true ){
					ga('send', 'event', 'Ad Blocker', 'Blocked', 'This user is using ad blocking software.', {'nonInteraction': true}); //Uses an event because it is asynchronous!
					window.dataLayer.push({'event': 'nebula-adblock-detected', 'nebula-event': thisEvent});
					nebula.session.flags.adblock = true;
				}
			}).always(function(){
				window.performance.mark('(Nebula) Detect AdBlock [End]');
				window.performance.measure('(Nebula) Detect AdBlock', '(Nebula) Detect AdBlock [Start]', 'Detect AdBlock [End]');
			});
		}

		//DataTables Filter
		nebula.dom.document.on('keyup', '.dataTables_filter input', function(e){
			var oThis = jQuery(this);
			var thisEvent = {
				event: e,
				category: 'DataTables',
				action: 'Search Filter', //GA4 Name: "search"?
				query: oThis.val().toLowerCase().trim()
			};

			nebula.debounce(function(){
				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.query);
				window.dataLayer.push({'event': 'nebula-datatables', 'nebula-event': thisEvent});
			}, 1000, 'datatables_search_filter');
		});

		//DataTables Sorting
		nebula.dom.document.on('click', 'th.sorting', function(e){
			var thisEvent = {
				event: e,
				category: 'DataTables',
				action: 'Sort', //GA4 Name: "datatables_sort"?
				heading: jQuery(this).text()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.heading);
			window.dataLayer.push({'event': 'nebula-datables', 'nebula-event': thisEvent});
		});

		//DataTables Pagination
		nebula.dom.document.on('click', 'a.paginate_button ', function(e){
			var thisEvent = {
				event: e,
				category: 'DataTables',
				action: 'Paginate', //GA4 Name: "datatables_paginate"?
				page: jQuery(this).text()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.page);
			window.dataLayer.push({'event': 'nebula-datatables', 'nebula-event': thisEvent});
		});

		//DataTables Show Entries
		nebula.dom.document.on('change', '.dataTables_length select', function(e){
			var thisEvent = {
				event: e,
				category: 'DataTables',
				action: 'Shown Entries Change', //Number of visible rows select dropdown
				selected: jQuery(this).val()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.selected);
			window.dataLayer.push({'event': 'nebula-datatables', 'nebula-event': thisEvent});
		});

		nebula.scrollDepth();
		nebula.ecommerceTracking();
	}, 'nebula event tracking');
};

//Ecommerce event tracking
//Note: These supplement the plugin Enhanced Ecommerce for WooCommerce
nebula.ecommerceTracking = function(){
	if ( nebula.has(nebula, 'site.ecommerce') && nebula.site.ecommerce ){ //@todo "Nebula" 0: Replace with optional chaining
		//Add to Cart clicks
		nebula.dom.document.on('click', 'a.add_to_cart, .single_add_to_cart_button', function(e){ //@todo "Nebula" 0: is there a trigger from WooCommerce this can listen for?
			var thisEvent = {
				event: e,
				category: 'Ecommerce',
				action: 'Add to Cart', //GA4 Name: "add_to_cart"
				product: jQuery(this).attr('data-product_id')
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.product);
			window.dataLayer.push({'event': 'nebula-add-to-cart', 'nebula-event': thisEvent});
			if ( typeof fbq === 'function' ){fbq('track', 'AddToCart');}
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.action);}
			nebula.crm('event', 'Ecommerce Add to Cart');
		});

		//Update cart clicks
		nebula.dom.document.on('click', '.button[name="update_cart"]', function(e){
			var thisEvent = {
				event: e,
				category: 'Ecommerce',
				action: 'Update Cart Button',
				label: 'Update Cart button click'
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
			window.dataLayer.push({'event': 'nebula-update-cart', 'nebula-event': thisEvent});
			nebula.crm('event', 'Ecommerce Update Cart');
		});

		//Product Remove buttons
		nebula.dom.document.on('click', '.product-remove a.remove', function(e){
			var thisEvent = {
				event: e,
				category: 'Ecommerce',
				action: 'Remove This Item', //GA4 Name: "remove_from_cart"
				product: jQuery(this).attr('data-product_id')
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.product);
			window.dataLayer.push({'event': 'nebula-remove-item', 'nebula-event': thisEvent});
			nebula.crm('event', 'Ecommerce Remove From Cart');
		});

		//Proceed to Checkout
		nebula.dom.document.on('click', '.wc-proceed-to-checkout .checkout-button', function(e){
			var thisEvent = {
				event: e,
				category: 'Ecommerce',
				action: 'Proceed to Checkout Button', //GA4 Name: "begin_checkout"
				label: 'Proceed to Checkout button click'
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
			window.dataLayer.push({'event': 'nebula-proceed-to-checkout', 'nebula-event': thisEvent});
			if ( typeof fbq === 'function' ){fbq('track', 'InitiateCheckout');}
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.action);}
			nebula.crm('event', 'Ecommerce Proceed to Checkout');
		});

		//Checkout form timing
		nebula.dom.document.on('click focus', '#billing_first_name', function(e){
			nebula.timer('(Nebula) Ecommerce Checkout', 'start');

			var thisEvent = {
				event: e,
				category: 'Ecommerce',
				action: 'Started Checkout Form', //GA4 Name: "checkout_progress"?
				label: 'Began filling out the checkout form (Billing First Name)'
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
			window.dataLayer.push({'event': 'nebula-started-checkout-form', 'nebula-event': thisEvent});
			nebula.crm('event', 'Ecommerce Started Checkout Form');
		});

		//Place order button
		nebula.dom.document.on('click', '#place_order', function(e){
			var thisEvent = {
				event: e,
				category: 'Ecommerce',
				action: 'Place Order Button', //GA4 Name: "purchase"
				label: 'Place Order button click'
			};

			ga('send', 'timing', 'Ecommerce', 'Checkout Form', Math.round(nebula.timer('(Nebula) Ecommerce Checkout', 'end')), 'Billing details start to Place Order button click');
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
			window.dataLayer.push({'event': 'nebula-place-order-button', 'nebula-event': thisEvent});
			if ( typeof fbq === 'function' ){fbq('track', 'Purchase');}
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.action);}
			nebula.crm('event', 'Ecommerce Placed Order');
			nebula.crm('identify', {hs_lifecyclestage_customer_date: 1}); //@todo "Nebula" 0: What kind of date format does Hubspot expect here?
		});
	}
};

//Track Nebula framework errors for quality assurance. This will need to be updated for GA4 most likely.
nebula.usage = function(error){
	if ( error.filename.match(/themes\/Nebula-?(main|master|parent|\d+\.\d+)?\//i) ){ //If the error is in a Nebula parent file (remove master from RegEx in January 2021)
		var errorMessage = '(JS) ' + error.message + ' at ' + error.lineno + ' of ' + error.filename;
		navigator.sendBeacon && navigator.sendBeacon('https://www.google-analytics.com/collect', [
			'v=1', //Protocol Version
			'tid=UA-36461517-5', //Tracking ID
			'cid=' + nebula.user.cid,
			'ua=' + nebula.user.client.user_agent, //User Agent
			'dl=' + window.location.href, //Page
			'dt=' + document.title, //Title
			't=exception', //Hit Type
			'exd=' + errorMessage, //Exception Detail
			'exf=1', //Fatal Exception?
			'cd1=' + nebula.site.home_url, //Homepage URL
			'cd2=' + Date.now(), //UNIX Time
			'cd6=' + nebula.version.number, //Nebula version
			'cd5=' + nebula.site.directory.root, //Site_URL
			'cd7=' + nebula.user.client.user_agent, //GA CID
			'cd9=' + nebula.site.is_child, //Is child theme?
			'cd12=' + window.location.href, //Permalink
			'cn=Nebula Usage', //Campaign
			'cs=' + nebula.site.home_url, //Source
			'cm=WordPress', //Medium
		].join('&'));
	}
}

//Detect scroll depth
//Note: This is a default GA4 event and is not needed to be tracked in Nebula. Consider deleting entirely.
nebula.scrollDepth = function(){
	if ( window.performance ){ //Safari 11+
		var scrollReady = performance.now();
		var reachedBottom = false; //Flag for optimization after detection is finished
		var excessiveScrolling = false; //Flag for optimization after detection is finished
		var lastScrollCheckpoint = nebula.dom.window.scrollTop(); //Set a checkpoint of the current scroll distance to subtract against later
		var totalScrollDistance = 0;
		var excessiveScrollThreshold = nebula.dom.document.height()*2; //Set the threshold for an excessive scroll distance

		var scrollDepthHandler = function(){
			//Only check for initial scroll once
			nebula.once(function(){
				nebula.scrollBegin = performance.now()-scrollReady; //Calculate when the first scroll happens
				if ( nebula.scrollBegin > 250 ){ //Try to avoid autoscrolls on pageload
					var thisEvent = {
						category: 'Scroll Depth',
						action: 'Began Scrolling',
						scrollStart: nebula.dom.body.scrollTop() + 'px',
						timeBeforeScrollStart: Math.round(nebula.scrollBegin)
					};
					thisEvent.label = 'Initial scroll started at ' + thisEvent.scrollStart;
					nebula.dom.document.trigger('nebula_event', thisEvent);
					ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.scrollStart, thisEvent.scrollStartTime, {'nonInteraction': true}); //Event value is time until scrolling.
				}
			}, 'begin scrolling');

			//Check scroll distance periodically
			nebula.throttle(function(){
				//Total Scroll Distance
				if ( !excessiveScrolling ){
					totalScrollDistance += Math.abs(nebula.dom.window.scrollTop() - lastScrollCheckpoint); //Increase the total scroll distance (always positive regardless of scroll direction)
					lastScrollCheckpoint = nebula.dom.window.scrollTop(); //Update the checkpoint
					if ( totalScrollDistance >= excessiveScrollThreshold ){
						excessiveScrolling = true; //Set to true to disable excessive scroll tracking after it is detected

						nebula.once(function(){
							var thisEvent = {
								category: 'Scroll Depth',
								action: 'Excessive Scrolling',
								label: 'User scrolled ' + excessiveScrollThreshold + 'px (or more) on this page.',
							};
							nebula.dom.document.trigger('nebula_event', thisEvent);
							ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label, {'nonInteraction': true});
						}, 'excessive scrolling');
					}
				}

				//When user reaches the bottom of the page
				if ( !reachedBottom ){
					if ( (nebula.dom.window.height()+nebula.dom.window.scrollTop()) >= nebula.dom.document.height() ){ //If user has reached the bottom of the page
						reachedBottom = true;

						nebula.once(function(){
							var thisEvent = {
								category: 'Scroll Depth',
								action: 'Entire Page',
								distance: nebula.dom.document.height(),
								scrollEnd: performance.now()-(nebula.scrollBegin+scrollReady),
							};

							thisEvent.timetoScrollEnd = Math.round(thisEvent.scrollEnd);

							nebula.dom.document.trigger('nebula_event', thisEvent);
							ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.distance, thisEvent.timetoScrollEnd, {'nonInteraction': true}); //Event value is time to reach end
							window.removeEventListener('scroll', scrollDepthHandler);
						}, 'end scrolling');
					}
				}

				//Stop listening to scroll after no longer necessary
				if ( reachedBottom && excessiveScrolling ){
					window.removeEventListener('scroll', scrollDepthHandler); //Stop watching scrolling– no longer needed if all detections are true
				}
			}, 1000, 'scroll depth');
		};

		window.addEventListener('scroll', scrollDepthHandler); //Watch for scrolling ("scroll" is passive by default)
	}
};

//Check if an element is within the viewport
nebula.isInViewport = function(element, offset){return nebula.isInView(element, offset);};
nebula.isInView = function(element, offset){
	if ( element ){
		if ( typeof element === 'string' ){
			element = jQuery(element);
		}

		if ( !offset ){
			offset = 1;
		}

		var elementTop = element.offset().top;
		var elementBottom = element.offset().top+element.innerHeight();

		var windowTop = nebula.dom.document.scrollTop();
		var windowBottom = nebula.dom.document.scrollTop()+nebula.dom.window.height()*offset;

		if ( !nebula.dom.body.hasClass('page-visibility-hidden') && ((elementTop >= windowTop && elementTop < windowBottom) || (elementBottom >= windowTop && elementBottom < windowBottom) || (elementTop < windowTop && elementBottom > windowBottom)) ){
			return true;
		}
	}

	return false;
};

//Send data to the CRM
nebula.crm = function(action, data, sendNow){
	if ( nebula.isDoNotTrack() ){
		return false;
	}

	if ( typeof _hsq === 'undefined' ){
		return false;
	}

	if ( !sendNow ){ //Set the default value for sendNow if not included
		var sendNow = true;
	}

	if ( !action || !data || typeof data == 'function' ){
		console.error('Action and Data Object are both required.');
		ga('send', 'exception', {'exDescription': '(JS) Action and Data Object are both required in nebula.crm()', 'exFatal': false});
		return false; //Action and Data are both required.
	}

	if ( action === 'identify' ){
		_hsq.push(["identify", data]);

		jQuery.each(data, function(key, value){
			nebula.user[key] = value;
		});

		if ( sendNow ){
			//Send a virtual pageview because event data doesn't work with free Hubspot accounts (and the identification needs a transport method)
			_hsq.push(['setPath', window.location.href.replace(nebula.site.directory.root, '') + '#virtual-pageview/identify']);
			_hsq.push(['trackPageView']);
		}
		//_hsq.push(["trackEvent", data]); //If using an Enterprise Marketing subscription, use this method instead of the trackPageView above

		//Check if email was identified or just supporting data
		if ( 'email' in data ){
			if ( !nebula.user.known && nebula.regex.email.test(data['email']) ){
				nebula.dom.document.trigger('nebula_crm_identification', {email: nebula.regex.email.test(data['email']), data: data});
				ga('send', 'event', 'CRM', 'Contact Identified', "A contact's email address in the CRM has been identified.");
				nebula.user.known = true;
			}
		} else {
			nebula.dom.document.trigger('nebula_crm_details', {data: data});
			ga('send', 'event', 'CRM', 'Supporting Information', 'Information associated with this user has been identified.');
		}
	}

	if ( action === 'event' ){
		//Hubspot events are only available with an Enterprise Marketing subscription
		//Refer to this documentation for event names and IDs: https://developers.hubspot.com/docs/methods/tracking_code_api/tracking_code_overview#idsandnames
		_hsq.push(["trackEvent", data]);

		_hsq.push(['setPath', window.location.href.replace(nebula.site.directory.root, '') + '#virtual-pageview/' + data]);
		var oldTitle = document.title;
		document.title = document.title + ' (Virtual)';
		_hsq.push(['trackPageView']);
		document.title = oldTitle;
	}

	nebula.dom.document.trigger('crm_data', data);
};

//Easily send form data to nebula.crm() with crm-* classes
//Add a class to the input field with the category to use. Ex: crm-firstname or crm-email or crm-fullname
//Call this function before sending a ga() event because it sets dimensions too
nebula.crmForm = function(formID){
	var crmFormObj = {};

	if ( formID ){
		crmFormObj['form_contacted'] = 'CF7 (' + formID + ') Submit Attempt'; //This is triggered on submission attempt, so it may capture abandoned forms due to validation errors.
	}

	jQuery('form [class*="crm-"]').each(function(){
		if ( jQuery(this).val().trim().length ){
			if ( jQuery(this).attr('class').indexOf('crm-notable_poi') >= 0 ){
				ga('set', nebula.analytics.dimensions.poi, jQuery('.notable-poi').val());
			}

			var cat = /crm-([a-z\_]+)/g.exec(jQuery(this).attr('class'));
			if ( cat ){
				var thisCat = cat[1];
				crmFormObj[thisCat] = jQuery(this).val();
			}
		}
	});

	if ( Object.keys(crmFormObj).length ){
		nebula.crm('identify', crmFormObj);
	}
};

/*==========================
 Search Functions
 ===========================*/

//Keyword Filter
nebula.keywordSearch = function(container, parent, values, filteredClass, operator){ //Alias for old function name
	nebula.keywordFilter(container, parent, values, filteredClass, operator);
};
nebula.keywordFilter = function(container, parent, values, filteredClass, operator){
	if ( typeof values === 'string' ){
		values = [values];
	}
	values = values.filter(String); //Remove any empty values from the array

	if ( !filteredClass ){
		var filteredClass = 'filtereditem';
	}

	jQuery(container).find(parent + '.' + filteredClass).removeClass(filteredClass); //Reset everything for a new search filter

	if ( values.length ){
		//If a regex pattern is specified
		if ( values.length === 1 && values[0].length > 2 && values[0].charAt(0) === '/' && values[0].slice(-1) === '/' ){
			var regex = new RegExp(values[0].substring(1).slice(0, -1), 'i'); //Convert the string to RegEx after removing the first and last /
			jQuery(container).find(parent).each(function(){ //Loop through each element to check against the regex pattern
				var elementText = jQuery(this).text().trim().replace(/\s\s+/g, ' '); //Combine all interior text of the element into a single line and remove extra whitespace
				jQuery(this).addClass(filteredClass);
				if ( regex.test(elementText) ){
					jQuery(this).removeClass(filteredClass);
				}
			});
		} else {
			if ( !operator || operator === 'and' || operator === 'all' ){ //Match only elements that contain all keywords (Default operator is And if not provided)
				jQuery.each(values, function(index, value){ //Loop through the values to search for
					if ( value && value.trim().length ){ //If the value exists and is not empty
						var regex = new RegExp(value, 'i');

						jQuery(container).find(parent).not('.' + filteredClass).each(function(){ //Now check elements that have not yet been filtered for this value
							var elementText = jQuery(this).text().trim().replace(/\s\s+/g, ' '); //Combine all interior text of the element into a single line and remove extra whitespace
							if ( !regex.test(elementText) ){
								jQuery(this).addClass(filteredClass);
							}
						});
					}
				});
			} else { //Match elements that contains any keyword
				var pattern = '';
				jQuery.each(values, function(index, value){
					if ( value.trim().length ){ //If the value is not empty, add it to the pattern
						pattern += value + '|';
					}
				});
				pattern = pattern.slice(0, -1); //Remove the last | character
				var regex = new RegExp(pattern, 'i');
				jQuery(container).find(parent).each(function(){ //Loop through each element to check against the regex pattern
					var elementText = jQuery(this).text().trim().replace(/\s\s+/g, ' '); //Combine all interior text of the element into a single line and remove extra whitespace
					jQuery(this).addClass(filteredClass);
					if ( regex.test(elementText) ){
						jQuery(this).removeClass(filteredClass);
					}
				});
			}
		}
	}
};

//Menu Search Replacement
nebula.menuSearchReplacement = function(){
	if ( jQuery('.nebula-search').length ){
		jQuery('.menu .nebula-search').each(function(){
			var randomMenuSearchID = Math.floor((Math.random()*100)+1);
			jQuery(this).html('<form class="wp-menu-nebula-search nebula-search search footer-search" method="get" action="' + nebula.site.home_url + '/"><div class="input-group"><i class="fas fa-search"></i><label class="sr-only" for="nebula-menu-search-' + randomMenuSearchID + '">Search</label><input type="search" id="nebula-menu-search-' + randomMenuSearchID + '" class="nebula-search input search" name="s" placeholder="Search" autocomplete="off" x-webkit-speech /></div></form>');
		});

		jQuery('.nebula-search input').on('focus', function(){
			jQuery(this).addClass('focus active');
		});

		jQuery('.nebula-search input').on('blur', function(){
			if ( jQuery(this).val() === '' || jQuery(this).val().trim().length === 0 ){
				jQuery(this).removeClass('focus active focusError').attr('placeholder', jQuery(this).attr('placeholder'));
			} else {
				jQuery(this).removeClass('active');
			}
		});
	}
};

//Only allow alphanumeric (and some special keys) to return true
//Use inside of a keydown function, and pass the event data.
nebula.searchTriggerOnlyChars = function(e){
	//@TODO "Nebula" 0: This still allows shortcuts like "cmd+a" to return true.
	var spinnerRegex = new RegExp("^[a-zA-Z0-9]+$");
	var allowedKeys = [8, 46];
	var searchChar = String.fromCharCode(!e.charCode ? e.which : e.charCode);

	if ( spinnerRegex.test(searchChar) || allowedKeys.indexOf(e.which) > -1 ){
		return true;
	} else {
		return false;
	}
};

//Enable autocomplete search on WordPress core selectors
nebula.autocompleteSearchListeners = function(){
	//Wrap this in requestIdleCallback once it is supported (IE11 and Safari)
	if ( jQuery('.nebula-search input, input#s, input.search').length ){
		nebula.loadJS(nebula.site.resources.scripts.nebula_jquery_ui, function(){
			nebula.dom.document.on('blur', '.nebula-search input', function(){
				jQuery('.nebula-search').removeClass('searching').removeClass('autocompleted');
			});

			jQuery('input#s, input.search').on('keyup paste change', function(e){
				if ( !jQuery(this).hasClass('no-autocomplete') && jQuery(this).val().trim().length && nebula.searchTriggerOnlyChars(e) ){
					nebula.autocompleteSearch(jQuery(this));
				}
			});
		});
		nebula.loadCSS(nebula.site.resources.styles.nebula_jquery_ui);
	}
};

//Run an autocomplete search on a passed element.
nebula.autocompleteSearch = function(element, types){
	if ( typeof element === 'string' ){
		element = jQuery(element);
	}

	if ( types && !Array.isArray(types) ){
		console.error('nebula.autocompleteSearch requires 2nd parameter to be an array.');
		ga('send', 'exception', {'exDescription': '(JS) nebula.autocompleteSearch requires 2nd parameter to be an array', 'exFatal': false});
		return false;
	}

	nebula.dom.document.trigger('nebula_autocomplete_search_start', element);
	nebula.timer('(Nebula) Autocomplete Search [Start]', 'start');
	nebula.timer('(Nebula) Autocomplete Response [Start]', 'start');

	if ( element.val().trim().length ){
		if ( element.val().trim().length >= 2 ){ //This checks the length for animation but the minlength (below) handles it for autocomplete
			//Add "searching" class for custom Nebula styled forms
			element.closest('form').addClass('searching');
			setTimeout(function(){
				element.closest('form').removeClass('searching');
			}, 10000);

			//Swap magnifying glass on Bootstrap input-group
			element.closest('.input-group').find('.fa-search').removeClass('fa-search').addClass('fa-spin fa-spinner');
		} else {
			element.closest('form').removeClass('searching');
			element.closest('.input-group').find('.fa-spin').removeClass('fa-spin fa-spinner').addClass('fa-search');
		}

		var postTypes = '';
		if ( types ){
			postTypes = '&types=' + JSON.stringify(types);
		}

		element.autocomplete({ //jQuery UI dependent
			position: {
				my: "left top-2px",
				at: "left bottom",
				collision: "flip",
			},
			source: function(request, response){
				jQuery.get({ //Eventually update this to fetch with ES6
					url: nebula.site.home_url + '/wp-json/nebula/v2/autocomplete_search?term=' + request.term + postTypes,
					success: function(data){
						nebula.dom.document.trigger('nebula_autocomplete_search_success', data);
						ga('set', nebula.analytics.metrics.autocompleteSearches, 1);

						if ( data ){
							nebula.dom.document.trigger('nebula_autocomplete_search_results', data);
							nebula.prefetch(data[0].link);

							jQuery.each(data, function(index, value){
								value.label = value.label.replace(/&#038;/g, "\&");
							});

							var noSearchResults = '';
						} else {
							nebula.dom.document.trigger('nebula_autocomplete_search_no_results');
							var noSearchResults = ' (No Results)';
						}

						nebula.debounce(function(){
							var thisEvent = {
								category: 'Internal Search',
								action: 'Autocomplete Search' + noSearchResults, //GA4 name: "search"
								request: request,
								term: request.term.toLowerCase(),
								noResults: ( noSearchResults )? true : false,
							};

							nebula.dom.document.trigger('nebula_event', thisEvent);
							ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.term);
							if ( typeof fbq === 'function' ){fbq('track', 'Search', {search_string: thisEvent.term});}
							if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.term);}
							nebula.crm('identify', {internal_search: thisEvent.term});
						}, 1500, 'autocomplete success buffer');

						ga('send', 'timing', 'Autocomplete Search', 'Server Response', Math.round(nebula.timer('(Nebula) Autocomplete Search', 'lap')), 'Each search until server results');
						response(data);
						element.closest('form').removeClass('searching').addClass('autocompleted');
						element.closest('.input-group').find('.fa-spin').removeClass('fa-spin fa-spinner').addClass('fa-search');
					},
					error: function(XMLHttpRequest, textStatus, errorThrown){
						nebula.dom.document.trigger('nebula_autocomplete_search_error', request.term);
						nebula.debounce(function(){
							ga('send', 'exception', {'exDescription': '(JS) Autocomplete AJAX error: ' + textStatus, 'exFatal': false});
							nebula.crm('event', 'Autocomplete Search AJAX Error');
						}, 1500, 'autocomplete error buffer');
						element.closest('form').removeClass('searching');
						element.closest('.input-group').find('.fa-spin').removeClass('fa-spin fa-spinner').addClass('fa-search');
					},
					timeout: 60000
				});
			},
			focus: function(event, ui){
				event.preventDefault(); //Prevent input value from changing.
			},
			select: function(event, ui){
				var thisEvent = {
					category: 'Internal Search',
					action: 'Autocomplete Click', //GA4 name: "select_content"
					ui: ui,
					label: ui.item.label,
					external: ( typeof ui.item.external !== 'undefined' )? true : false,
				};

				ga('set', nebula.analytics.metrics.autocompleteSearchClicks, 1);
				nebula.dom.document.trigger('nebula_autocomplete_search_selected', thisEvent.ui);
				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
				ga('send', 'timing', 'Autocomplete Search', 'Until Navigation', Math.round(nebula.timer('(Nebula) Autocomplete Search', 'end')), 'From first initial search until navigation');

				if ( thisEvent.external ){
					window.open(ui.item.link, '_blank');
				} else {
					window.location.href = ui.item.link;
				}
			},
			open: function(){
				element.closest('form').addClass('autocompleted');
				var heroAutoCompleteDropdown = jQuery('.form-identifier-nebula-hero-search');
				heroAutoCompleteDropdown.css('max-width', element.outerWidth());
			},
			close: function(){
				element.closest('form').removeClass('autocompleted');
			},
			minLength: 3, //Require at least 3 characters
		}).data('ui-autocomplete')._renderItem = function(ul, item){
			var thisSimilarity = ( typeof item.similarity !== 'undefined' )? item.similarity.toFixed(1) + '% Match' : '';
			var listItem = jQuery("<li class='" + item.classes + "' title='" + thisSimilarity + "'></li>").data("item.autocomplete", item).append("<a href='" + item.link + "'> " + item.label.replace(/\\/g, '') + "</a>").appendTo(ul);
			return listItem;
		};
		var thisFormIdentifier = element.closest('form').attr('id') || element.closest('form').attr('name') || element.closest('form').attr('class');
		element.autocomplete("widget").addClass("form-identifier-" + thisFormIdentifier);
	}
};

nebula.wpSearchInput = function(){
	jQuery('#post-0 #s, #nebula-drawer #s, .search-results #s').trigger('focus'); //Automatically focus on specific search inputs

	//Set search value as placeholder
	var searchVal = nebula.get('s') || jQuery('#s').val();
	if ( searchVal ){
		jQuery('#s, .nebula-search input').attr('placeholder', searchVal.replace(/\+/g, ' '));
	}
};

//Mobile search placeholder toggle
nebula.mobileSearchPlaceholder = function(){
	var mobileHeaderSearchInput = jQuery('#mobileheadersearch input');
	var searchPlaceholder = 'What are you looking for?';
	if ( window.matchMedia && window.matchMedia("(max-width: 410px)").matches ){ //@todo "Nebula" 0: Use optional chaining?
		searchPlaceholder = 'Search';
	}
	mobileHeaderSearchInput.attr('placeholder', searchPlaceholder);
};

//Search Validator
nebula.searchValidator = function(){
	//Wrap in requestIdleCallback once fully supported (IE11 and Safari)
	if ( jQuery('.input.search').length ){
		jQuery('.input.search').each(function(){
			if ( jQuery(this).val() === '' || jQuery(this).val().trim().length === 0 ){
				jQuery(this).parent().children('.btn.submit').addClass('disallowed');
			} else {
				jQuery(this).parent().children('.btn.submit').removeClass('disallowed').val('Search');
				jQuery(this).parent().find('.input.search').removeClass('focusError');
			}
		});

		jQuery('.input.search').on('focus blur change keyup paste cut',function(e){
			var thisPlaceholder = ( jQuery(this).attr('data-prev-placeholder') !== 'undefined' )? jQuery(this).attr('data-prev-placeholder') : 'Search';
			if ( jQuery(this).val() === '' || jQuery(this).val().trim().length === 0 ){
				jQuery(this).parent().children('.btn.submit').addClass('disallowed');
				jQuery(this).parent().find('.btn.submit').val('Go');
			} else {
				jQuery(this).parent().children('.btn.submit').removeClass('disallowed');
				jQuery(this).parent().find('.input.search').removeClass('focusError').prop('title', '').attr('placeholder', thisPlaceholder);
				jQuery(this).parent().find('.btn.submit').prop('title', '').removeClass('notallowed').val('Search');
			}
			if ( e.type === 'paste' ){
				jQuery(this).parent().children('.btn.submit').removeClass('disallowed');
				jQuery(this).parent().find('.input.search').prop('title', '').attr('placeholder', 'Search').removeClass('focusError');
				jQuery(this).parent().find('.btn.submit').prop('title', '').removeClass('notallowed').val('Search');
			}
		});

		jQuery('form.search').on('submit', function(){
			if ( jQuery(this).find('.input.search').val() === '' || jQuery(this).find('.input.search').val().trim().length === 0 ){
				jQuery(this).parent().find('.input.search').prop('title', 'Enter a valid search term.').attr('data-prev-placeholder', jQuery(this).attr('placeholder')).attr('placeholder', 'Enter a valid search term').addClass('focusError').trigger('focus').attr('value', '');
				jQuery(this).parent().find('.btn.submit').prop('title', 'Enter a valid search term.').addClass('notallowed');
				return false;
			} else {
				return true;
			}
		});
	}
};

//Highlight search terms
nebula.searchTermHighlighter = function(){
	window.requestAnimationFrame(function(){
		var searchTerm = document.URL.split('?s=')[1];
		if ( typeof searchTerm !== 'undefined' ){
			var reg = new RegExp("(?![^<]+>)(" + preg_quote(searchTerm.replace(/(\+|%22|%20)/g, ' ')) + ")", "ig");
			jQuery('article .entry-title a, article .entry-summary').each(function(i){
				jQuery(this).html(function(i, html){
					return html.replace(reg, '<mark class="searchresultword">$1</mark>');
				});
			});

			nebula.emphasizeSearchTerms();
		}
	});
};

//Emphasize the search Terms
nebula.emphasizeSearchTerms = function(){
	window.requestAnimationFrame(function(){
		var origBGColor = jQuery('.searchresultword').css('background-color');
		jQuery('.searchresultword').each(function(i){
		 	var stallFor = 150 * parseInt(i);
			jQuery(this).delay(stallFor).animate({
				backgroundColor: 'rgba(255, 255, 0, 0.5)',
				borderColor: 'rgba(255, 255, 0, 1)',
			}, 500, 'swing', function(){
				jQuery(this).delay(1000).animate({
					backgroundColor: origBGColor,
				}, 1000, 'swing', function(){
					jQuery(this).addClass('transitionable');
				});
			});
		});
	});
};

//Single search result redirection drawer
nebula.singleResultDrawer = function(){
	var searchTerm = nebula.get('rs');
	if ( searchTerm ){
		searchTerm = searchTerm.replace(/\%20|\+/g, ' ').replace(/\%22|"|'/g, '');
		jQuery('#searchform input#s').val(searchTerm);

		nebula.dom.document.on('click', '#nebula-drawer .close', function(){
			var permalink = jQuery(this).attr('href');
			history.replaceState(null, document.title, permalink);
			jQuery('#nebula-drawer').slideUp();
			return false;
		});
	}
};

//Page Suggestions for 404 or no search results pages using Google Custom Search Engine
nebula.pageSuggestion = function(){
	if ( nebula.dom.body.hasClass('search-no-results') || nebula.dom.body.hasClass('error404') ){
		if ( nebula.has(nebula, 'site.options') && nebula.site.options.nebula_cse_id !== '' && nebula.site.options.nebula_google_browser_api_key !== '' ){ //@todo "Nebula" 0: Replace with optional chaining
			if ( nebula.get().length ){
				var queryStrings = nebula.get();
			} else {
				var queryStrings = [''];
			}
			var path = window.location.pathname;
			var phrase = decodeURIComponent(path.replace(/\/+/g, ' ')).trim() + ' ' + decodeURIComponent(queryStrings[0].replace(/\+/g, ' ')).trim();
			nebula.tryGCSESearch(phrase);
		}

		nebula.dom.document.on('mousedown', 'a.gcse-suggestion, a.internal-suggestion', function(e){
			var thisEvent = {
				event: e,
				category: 'Page Suggestion',
				action: ( jQuery(this).hasClass('internal-suggestion') )? 'Internal' : 'GCSE', //GA4 name: "select_content"
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				suggestion: jQuery(this).text(),
			};

			ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.suggestion);
			nebula.crm('event', 'Page Suggestion Click');
		});
	}
};

nebula.tryGCSESearch = function(phrase){
	if ( nebula.site.options.nebula_cse_id.length && nebula.site.options.nebula_google_browser_api_key.length ){
		var queryParams = {
			cx: nebula.site.options.nebula_cse_id,
			key: nebula.site.options.nebula_google_browser_api_key,
			num: 10,
			q: phrase,
			alt: 'JSON'
		};
		var API_URL = 'https://www.googleapis.com/customsearch/v1?';

		// Send the request to the custom search API
		jQuery.getJSON(API_URL, queryParams, function(response){
			if ( response.items && response.items.length ){ //@todo "Nebula" 0: Use optional chaining
				if ( response.items[0].link !== window.location.href ){
					nebula.showSuggestedGCSEPage(response.items[0].title, response.items[0].link);
				}
			}
		});
	}
};

nebula.showSuggestedGCSEPage = function(title, url){
	var hostname = new RegExp(location.host);
	if ( hostname.test(url) ){
		jQuery('.gcse-suggestion').attr('href', url).text(title);
		jQuery('#nebula-drawer.suggestedpage').slideDown();
		nebula.prefetch(url);
	}
};

/*==========================
 Contact Form Functions
 ===========================*/

nebula.cf7Functions = function(){
	if ( !jQuery('.wpcf7-form').length ){
		return false;
	}

	jQuery('.wpcf7-form p:empty').remove(); //Remove empty <p> tags within CF7 forms

	var formStarted = {};

	//Replace submit input with a button so a spinner icon can be used instead of the CF7 spin gif (unless it has the class "no-button")
	jQuery('.wpcf7-form input[type=submit]').each(function(){
		if ( !jQuery(this).hasClass('no-button') ){
			jQuery(this).replaceWith('<button id="submit" type="submit" class="' + nebula.sanitize(jQuery(this).attr('class')) + '">' + nebula.sanitize(jQuery(this).val()) + '</button>'); //Sanitized to prevent XSS
		}
	});

	//Track CF7 forms when they scroll into view (Autotrack). Currently not possible to change category/action/label for just these impressions.
	jQuery('.wpcf7-form').each(function(){
		var thisEvent = {
			category: 'CF7 Form',
			action: 'Impression', //GA4 Name: "form_impression"?
			formID: jQuery(this).closest('.wpcf7').attr('id') || jQuery(this).attr('id'),
		};

		ga('impressionTracker:observeElements', [{
			'id': thisEvent.formID,
			'threshold': 0.25,
			'fieldsObj': { //@todo "Nebula" 0: The fieldsObj doesn't appear to be supported in programmatic impression tracking via Autotrack
				'eventCategory': thisEvent.category, //This doesn't do anything right now. There is a task that is modifying the category in inc/analytics.php (but I'd prefer it be here instead)
			},
		}]);
	});

	//Re-init forms inside Bootstrap modals (to enable AJAX submission) when needed
	nebula.dom.document.on('shown.bs.modal', function(e){
		if ( typeof wpcf7.initForm === 'function' && jQuery(e.target).find('.wpcf7-form').length && !jQuery(e.target).find('.ajax-loader').length  ){ //If initForm function exists, and a form is inside the modal, and if it has not yet been initialized (The initForm function adds the ".ajax-loader" span)
			wpcf7.initForm(jQuery(e.target).find('.wpcf7-form'));
		}
	});

	//Form starts and field focuses
	nebula.dom.document.on('focus', '.wpcf7-form input, .wpcf7-form button, .wpcf7-form textarea', function(e){
		var formID = jQuery(this).closest('div.wpcf7').attr('id');

		var thisField = e.target.name || jQuery(this).closest('.form-group').find('label').text() || e.target.id || 'Unknown';
		var fieldInfo = '';
		if ( jQuery(this).attr('type') === 'checkbox' || jQuery(this).attr('type') === 'radio' ){
			fieldInfo = jQuery(this).attr('value');
		}

		if ( !jQuery(this).hasClass('.ignore-form') && !jQuery(this).find('.ignore-form').length && !jQuery(this).parents('.ignore-form').length ){
			var thisEvent = {
				event: e,
				category: 'CF7 Form',
				action: 'Started Form (Focus)',  //GA4 Name: "form_start"?
				formID: formID,
				field: thisField,
				fieldInfo: fieldInfo
			};

			//Form starts
			if ( typeof formStarted[formID] === 'undefined' || !formStarted[formID] ){
				thisEvent.label = 'Began filling out form ID: ' + thisEvent.formID + ' (' + thisEvent.field + ')';

				ga('set', nebula.analytics.metrics.formStarts, 1);
				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
				nebula.crm('identify', {'form_contacted': 'CF7 (' + thisEvent.formID + ') Started'}, false);
				nebula.crm('event', 'Contact Form (' + thisEvent.formID + ') Started (' + thisEvent.field + ')');
				formStarted[formID] = true;
			}

			nebula.updateFormFlow(thisEvent.formID, thisEvent.field, thisEvent.fieldInfo);

			//Track each individual field focuses
			if ( !jQuery(this).is('button') ){
				thisEvent.action = 'Individual Field Focused';
				thisEvent.label = 'Focus into ' + thisEvent.field + ' (Form ID: ' + thisEvent.formID + ')';

				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
				window.dataLayer.push({'event': 'nebula-form-started', 'nebula-event': thisEvent});
			}
		}

		nebula.timer(formID, 'start', thisField);

		//Individual form field timings
		if ( nebula && nebula.timings && typeof nebula.timings[formID] !== 'undefined' && typeof nebula.timings[formID].lap[nebula.timings[formID].laps-1] !== 'undefined' ){ //@todo "Nebula" 0: Use optional chaining
			var labelText = '';
			if ( jQuery(this).parent('.label') ){
				labelText = jQuery(this).parent('.label').text();
			} else if ( jQuery('label[for="' + jQuery(this).attr('id') + '"]').length ){
				labelText = jQuery('label[for="' + jQuery(this).attr('id') + '"]').text();
			} else if ( jQuery(this).attr('placeholder').length ){
				labelText = ' "' + jQuery(this).attr('placeholder') + '"';
			}
			ga('send', 'timing', 'CF7 Form', nebula.timings[formID].lap[nebula.timings[formID].laps-1].name + labelText + ' (Form ID: ' + formID + ')', Math.round(nebula.timings[formID].lap[nebula.timings[formID].laps-1].duration), 'Amount of time on this input field (until next focus or submit).');
		}
	});

	//CF7 before submission
	nebula.dom.document.on('wpcf7beforesubmit', function(e){
		jQuery(e.target).find('button#submit').addClass('active');
	});

	//CF7 Invalid (CF7 AJAX response after invalid form)
	nebula.dom.document.on('wpcf7invalid', function(e){
		var thisEvent = {
			event: e,
			category: 'CF7 Form',
			action: 'Submit (Invalid)', //GA4 Name: "form_invalid"?
			formID: e.detail.id,
		};

		//If timing data exists
		if ( nebula && nebula.timings && typeof nebula.timings[e.detail.id] !== 'undefined' ){ //@todo "Nebula" 0: Use optional chaining
			thisEvent.formTime = nebula.timer(e.detail.id, 'lap', 'wpcf7-submit-invalid');
			thisEvent.inputs = nebula.timings[e.detail.id].laps + ' inputs';
		}

		thisEvent.label = 'Form validation errors occurred on form ID: ' + thisEvent.formID;

		//Apply Bootstrap validation classes to invalid fields
		jQuery('.wpcf7-not-valid').each(function(){
			jQuery(this).addClass('is-invalid');
		});

		nebula.updateFormFlow(thisEvent.formID, '[Invalid]');
		ga('set', nebula.analytics.dimensions.contactMethod, 'CF7 Form (Invalid)');
		ga('set', nebula.analytics.dimensions.formTiming, nebula.millisecondsToString(thisEvent.formTime) + 'ms (' + thisEvent.inputs + ')');
		nebula.dom.document.trigger('nebula_event', thisEvent);
		ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
		window.dataLayer.push({'event': 'nebula-form-invalid', 'nebula-event': thisEvent});
		ga('send', 'exception', {'exDescription': '(JS) Invalid form submission for form ID ' + thisEvent.formID, 'exFatal': false});
		nebula.scrollTo(jQuery(".wpcf7-not-valid").first()); //Scroll to the first invalid input
		nebula.crm('identify', {'form_contacted': 'CF7 (' + thisEvent.formID + ') Invalid'}, false);
		nebula.crm('event', 'Contact Form (' + thisEvent.formID + ') Invalid');
	});

	//General HTML5 validation errors
	jQuery('.wpcf7-form input').on('invalid', function(e){ //Would it be more useful to capture all inputs (rather than just CF7)? How would we categorize this in GA?
		nebula.debounce(function(){
			var thisEvent = {
				event: e,
				category: 'CF7 Form',
				action: 'Submit (Invalid)', //GA4 Name: "form_invalid"?
				label: 'General HTML5 validation error',
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
			window.dataLayer.push({'event': 'nebula-form-invalid', 'nebula-event': thisEvent});
			nebula.crm('identify', {'form_contacted': 'CF7 HTML5 Validation Error'});
		}, 50, 'invalid form');
	});

	//CF7 Spam (CF7 AJAX response after spam detection)
	nebula.dom.document.on('wpcf7spam', function(e){
		var formInputs = 'Unknown';
		if ( nebula.timings && nebula.timings[e.detail.id] && nebula.timings[e.detail.id].laps ){ //Use optional chaining here
			formInputs = nebula.timings[e.detail.id].laps + ' inputs';
		}

		var thisEvent = {
			event: e,
			category: 'CF7 Form',
			action: 'Submit (Spam)', //GA4 Name: "form_spam"?
			formID: e.detail.id,
			formTime: nebula.timer(e.detail.id, 'end'),
			inputs: formInputs
		};

		thisEvent.label = 'Form submission failed spam tests on form ID: ' + thisEvent.formID;

		nebula.updateFormFlow(thisEvent.formID, '[Spam]');
		ga('set', nebula.analytics.dimensions.contactMethod, 'CF7 Form (Spam)');
		ga('set', nebula.analytics.dimensions.formTiming, nebula.millisecondsToString(thisEvent.formTime) + 'ms (' + thisEvent.inputs + ')');
		nebula.dom.document.trigger('nebula_event', thisEvent);
		ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
		window.dataLayer.push({'event': 'nebula-form-spam', 'nebula-event': thisEvent});
		ga('send', 'exception', {'exDescription': '(JS) Spam form submission for form ID ' + thisEvent.formID, 'exFatal': false});
		nebula.crm('identify', {'form_contacted': 'CF7 (' + thisEvent.formID + ') Submit Spam'}, false);
		nebula.crm('event', 'Contact Form (' + thisEvent.formID + ') Spam');
	});

	//CF7 Mail Send Failure (CF7 AJAX response after mail failure)
	nebula.dom.document.on('wpcf7mailfailed', function(e){
		var thisEvent = {
			event: e,
			category: 'CF7 Form',
			action: 'Submit (Mail Failed)', //GA4 Name: "form_failed"?
			formID: e.detail.id,
			formTime: nebula.timer(e.detail.id, 'end'),
			inputs: nebula.timings[e.detail.id].laps + ' inputs'
		};

		thisEvent.label = 'Form submission email send failed for form ID: ' + thisEvent.formID;

		nebula.updateFormFlow(thisEvent.formID, '[Failed]');
		ga('set', nebula.analytics.dimensions.contactMethod, 'CF7 Form (Failed)');
		ga('set', nebula.analytics.dimensions.formTiming, nebula.millisecondsToString(thisEvent.formTime) + 'ms (' + thisEvent.inputs + ')');
		nebula.dom.document.trigger('nebula_event', thisEvent);
		ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
		window.dataLayer.push({'event': 'nebula-form-failed', 'nebula-event': thisEvent});
		ga('send', 'exception', {'exDescription': '(JS) Mail failed to send for form ID ' + thisEvent.formID, 'exFatal': true});
		nebula.crm('identify', {'form_contacted': 'CF7 (' + thisEvent.formID + ') Submit Failed'}, false);
		nebula.crm('event', 'Contact Form (' + thisEvent.formID + ') Failed');
	});

	//CF7 Mail Sent Success (CF7 AJAX response after submit success)
	nebula.dom.document.on('wpcf7mailsent', function(e){
		formStarted[e.detail.id] = false; //Reset abandonment tracker for this form.

		var formInputs = 'Unknown';
		if ( nebula.timings && nebula.timings[e.detail.id] && nebula.timings[e.detail.id].laps ){ //Use optional chaining here
			formInputs = nebula.timings[e.detail.id].laps + ' inputs';
		}

		//These event may want to correspond to the GA4 event name "generate_lead" and use "value" and "currency" as parameters: https://support.google.com/analytics/answer/9267735 (or consider multiple events?)

		var thisEvent = {
			event: e,
			category: 'CF7 Form',
			action: 'Submit (Success)', //GA4 Name: "form_submit" (and also somehow "generate_lead"?)
			formID: e.detail.id,
			formTime: nebula.timer(e.detail.id, 'end'),
			inputs: formInputs
		};

		thisEvent.label = 'Form ID: ' + thisEvent.formID;

		nebula.updateFormFlow(thisEvent.formID, '[Success]');
		if ( !jQuery('#' + e.detail.id).hasClass('.ignore-form') && !jQuery('#' + e.detail.id).find('.ignore-form').length && !jQuery('#' + e.detail.id).parents('.ignore-form').length ){
			ga('set', nebula.analytics.metrics.formSubmissions, 1);
		}
		ga('set', nebula.analytics.dimensions.contactMethod, 'CF7 Form (Success)');
		ga('set', nebula.analytics.dimensions.formTiming, nebula.millisecondsToString(thisEvent.formTime) + 'ms (' + thisEvent.inputs + ')');
		ga('send', 'timing', thisEvent.category, 'Form Completion (ID: ' + thisEvent.formID + ')', Math.round(thisEvent.formTime), 'Initial form focus until valid submit');
		nebula.dom.document.trigger('nebula_event', thisEvent);
		ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
		window.dataLayer.push({'event': 'nebula-form-submit-success', 'nebula-event': thisEvent});
		if ( typeof fbq === 'function' ){fbq('track', 'Lead', {content_name: 'Form Submit (Success)'});}
		if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.action);}
		nebula.crm('identify', {'form_contacted': 'CF7 (' + thisEvent.formID + ') Submit Success'}, false);
		nebula.crm('event', 'Contact Form (' + thisEvent.formID + ') Submit Success');

		//Clear localstorage on submit success
		jQuery('#' + e.detail.id + ' .wpcf7-textarea, #' + e.detail.id + ' .wpcf7-text').each(function(){
			jQuery(this).trigger('keyup'); //Clear validation
			localStorage.removeItem('cf7_' + jQuery(this).attr('name'));
		});

		jQuery('#' + e.detail.id).find('.is-valid, .is-invalid').removeClass('is-valid is-invalid'); //Clear all validation classes
	});

	//CF7 Submit (CF7 AJAX response after any submit attempt). This triggers after the other submit triggers.
	nebula.dom.document.on('wpcf7submit', function(e){
		var thisEvent = {
			event: e,
			category: 'CF7 Form',
			action: 'Submit (Attempt)', //GA4 Name: "form_attempt"?
			formID: e.detail.id,
		};

		//If timing data exists
		if ( nebula && nebula.timings && typeof nebula.timings[e.detail.id] !== 'undefined' ){ //@todo "Nebula" 0: Use optional chaining
			thisEvent.formTime = nebula.timer(e.detail.id, 'lap', 'wpcf7-submit-attempt');
			thisEvent.inputs = nebula.timings[e.detail.id].laps + ' inputs';
		}

		thisEvent.label = 'Submission attempt for form ID: ' + thisEvent.formID;

		nebula.crmForm(thisEvent.formID); //nebula.crmForm() here because it triggers after all others. No nebula.crm() here so it doesn't overwrite the other (more valuable) data.

		ga('set', nebula.analytics.dimensions.contactMethod, 'CF7 Form (Attempt)');
		ga('set', nebula.analytics.dimensions.formTiming, nebula.millisecondsToString(thisEvent.formTime) + 'ms (' + thisEvent.inputs + ')');
		nebula.dom.document.trigger('nebula_event', thisEvent);
		ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label); //This event is required for the notable form metric!
		window.dataLayer.push({'event': 'nebula-form-submit-attempt', 'nebula-event': thisEvent});
		if ( typeof fbq === 'function' ){fbq('track', 'Lead', {content_name: 'Form Submit (Attempt)'});}
		if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.action);}

		jQuery('#' + e.detail.id).find('button#submit').removeClass('active');
		jQuery('.invalid-feedback').addClass('hidden');
	});
};

nebula.updateFormFlow = function(formID, field, info){
	if ( typeof formFlow === 'undefined' ){
		var formFlow = {};
	}

	if ( !info ){
		info = '';
	} else {
		if ( info.length > 25 ){
			info = info.substring(0, 25) + '...'; //Truncate long info text
		}

		info = ' (' + info + ')';
	}

	if ( !formFlow[formID] ){
		formFlow[formID] = formID + ': ' + field + info; //Start a new form flow string beginning with the form ID
	} else {
		formFlow[formID] += ' > ' + field + info;
	}

	ga('set', nebula.analytics.dimensions.formFlow, formFlow[formID]); //Update form field history. scope: session
};

//Enable localstorage on CF7 text inputs and textareas
nebula.cf7LocalStorage = function(){
	if ( !jQuery('.wpcf7-form').length || jQuery('.ie, .internet_explorer').length ){
		return false;
	}

	jQuery('.wpcf7-textarea, .wpcf7-text').each(function(){
		var thisLocalStorageVal = localStorage.getItem('cf7_' + jQuery(this).attr('name'));

		//Fill textareas with localstorage data on load
		if ( !jQuery(this).hasClass('do-not-store') && !jQuery(this).hasClass('.wpcf7-captchar') && thisLocalStorageVal && thisLocalStorageVal !== 'undefined' && thisLocalStorageVal !== '' ){
			if ( jQuery(this).val() === '' ){ //Don't overwrite a field that already has text in it!
				jQuery(this).val(thisLocalStorageVal).trigger('keyup');
			}
			jQuery(this).blur();
		} else {
			localStorage.removeItem('cf7_' + jQuery(this).attr('name')); //Remove localstorage if it is undefined or inelligible
		}

		//Update localstorage data
		jQuery(this).on('keyup blur', function(){
			if ( !jQuery(this).hasClass('do-not-store') && !jQuery(this).hasClass('.wpcf7-captchar') ){
				localStorage.setItem('cf7_' + jQuery(this).attr('name'), jQuery(this).val());
			}
		});
	});

	//Update matching form fields on other windows/tabs
	nebula.dom.window.on('storage', function(e){ //This causes an infinite loop in IE11
		jQuery('.wpcf7-textarea, .wpcf7-text').each(function(){
			if ( !jQuery(this).hasClass('do-not-store') && !jQuery(this).hasClass('.wpcf7-captchar') ){
				jQuery(this).val(localStorage.getItem('cf7_' + jQuery(this).attr('name'))).trigger('keyup');
			}
		});
	});

	//Clear localstorage when AJAX submit fails (but submit still succeeds)
	if ( window.location.hash.indexOf('wpcf7') > 0 ){
		if ( jQuery(escape(window.location.hash) + ' .wpcf7-mail-sent-ok').length ){
			jQuery(escape(window.location.hash) + ' .wpcf7-textarea, ' + escape(window.location.hash) + ' .wpcf7-text').each(function(){
				localStorage.removeItem('cf7_' + jQuery(this).attr('name'));
				jQuery(this).val('').trigger('keyup');
			});
		}
	}
};

//Form live (soft) validator
nebula.liveValidator = function(){
	//Standard text inputs and select menus
	nebula.dom.document.on('keyup change blur', '.nebula-validate-text, .nebula-validate-textarea, .nebula-validate-select', function(e){
		if ( jQuery(this).val() === '' ){
			nebula.applyValidationClasses(jQuery(this), 'reset', false);
		} else if ( jQuery(this).val().trim().length ){
			nebula.applyValidationClasses(jQuery(this), 'valid', false);
		} else {
			nebula.applyValidationClasses(jQuery(this), 'invalid', ( e.type !== 'keyup' ));
		}
	});

	//RegEx input
	nebula.dom.document.on('keyup change blur', '.nebula-validate-regex', function(e){
		var pattern = new RegExp(jQuery(this).attr('pattern'), 'i');

		if ( jQuery(this).val() === '' ){
			nebula.applyValidationClasses(jQuery(this), 'reset', false);
		} else if ( pattern.test(jQuery(this).val()) ){
			nebula.applyValidationClasses(jQuery(this), 'valid', false);
		} else {
			nebula.applyValidationClasses(jQuery(this), 'invalid', ( e.type !== 'keyup' ));
		}
	});

	//URL inputs
	nebula.dom.document.on('keyup change blur', '.nebula-validate-url', function(e){
		if ( jQuery(this).val() === '' ){
			nebula.applyValidationClasses(jQuery(this), 'reset', false);
		} else if ( nebula.regex.url.test(jQuery(this).val()) ){
			nebula.applyValidationClasses(jQuery(this), 'valid', false);
		} else {
			nebula.applyValidationClasses(jQuery(this), 'invalid', ( e.type !== 'keyup' ));
		}
	});

	//Email address inputs
	nebula.dom.document.on('keyup change blur', '.nebula-validate-email', function(e){
		if ( jQuery(this).val() === '' ){
			nebula.applyValidationClasses(jQuery(this), 'reset', false);
		} else if ( nebula.regex.email.test(jQuery(this).val()) ){
			nebula.applyValidationClasses(jQuery(this), 'valid', false);
		} else {
			nebula.applyValidationClasses(jQuery(this), 'invalid', ( e.type !== 'keyup' ));
		}
	});

	//Phone number inputs
	nebula.dom.document.on('keyup change blur', '.nebula-validate-phone', function(e){
		if ( jQuery(this).val() === '' ){
			nebula.applyValidationClasses(jQuery(this), 'reset', false);
		} else if ( nebula.regex.phone.test(jQuery(this).val()) ){
			nebula.applyValidationClasses(jQuery(this), 'valid', false);
		} else {
			nebula.applyValidationClasses(jQuery(this), 'invalid', ( e.type !== 'keyup' ));
		}
	});

	//Date inputs
	nebula.dom.document.on('keyup change blur', '.nebula-validate-date', function(e){
		//Used to use moment.js to validate the date and check that it was between 1800-2999. Now just check that it is not empty.

		if ( jQuery(this).val() === '' ){
			nebula.applyValidationClasses(jQuery(this), 'reset', false);
		} else {
			nebula.applyValidationClasses(jQuery(this), 'invalid', ( e.type !== 'keyup' ));
		}
	});

	//Checkbox and Radio
	nebula.dom.document.on('change blur', '.nebula-validate-checkbox, .nebula-validate-radio', function(e){
		if ( jQuery(this).closest('.form-group').find('input:checked').length ){
			nebula.applyValidationClasses(jQuery(this), 'reset', false);
		} else {
			nebula.applyValidationClasses(jQuery(this), 'invalid', true);
		}
	});

	//Highlight empty required fields when focusing/hovering on submit button
	nebula.dom.document.on('mouseover focus', 'form [type="submit"], form #submit', function(){ //Must be deferred because Nebula replaces CF7 submit inputs with buttons
		var invalidCount = 0;

		jQuery(this).closest('form').find('[required], .wpcf7-validates-as-required').each(function(){
			//Look for checked checkboxes or radio buttons
			if ( jQuery(this).find('input:checked').length ){
				return; //Continue
			}

			//Look for empty fields
			if ( jQuery(this).val().trim().length == 0 ){
				jQuery(this).addClass('nebula-empty-required');
				invalidCount++;
			}
		});

		if ( invalidCount > 0 ){
			var invalidCountText = ( invalidCount === 1 )? ' invalid field remains' : ' invalid fields remain';
			jQuery('form [type="submit"], form #submit').attr('title', invalidCount + invalidCountText);
		}
	});
	nebula.dom.document.on('mouseout blur', 'form [type="submit"], form #submit', function(){ //Must be deferred because Nebula replaces CF7 submit inputs with buttons
		jQuery(this).closest('form').find('.nebula-empty-required').removeClass('nebula-empty-required');
		jQuery('form [type="submit"], form #submit').removeAttr('title');
	});
};

//Apply Bootstrap and CF7 appropriate validation classes to appropriate elements
nebula.applyValidationClasses = function(element, validation, showFeedback){
	if ( typeof element === 'string' ){
		element = jQuery(element);
	} else if ( typeof element !== 'object' ) {
		return false;
	}

	if ( validation === 'success' || validation === 'valid' ){
		element.removeClass('wpcf7-not-valid is-invalid').addClass('is-valid').parent().find('.wpcf7-not-valid-tip').remove();
	} else if ( validation === 'danger' || validation === 'error' || validation === 'invalid' ){
		element.removeClass('wpcf7-not-valid is-valid').addClass('is-invalid');
	} else if ( validation === 'reset' || validation === 'remove' ){
		element.removeClass('wpcf7-not-valid is-invalid is-valid').parent().find('.wpcf7-not-valid-tip').remove();
	}

	//Find the invalid feedback element (if it exists)
	var feedbackElement = false;
	if ( element.parent().find('.invalid-feedback').length ){
		feedbackElement = element.parent().find('.invalid-feedback');
	} else if ( element.closest('.form-group').find('.invalid-feedback').length ){
		feedbackElement = element.closest('.form-group').find('.invalid-feedback');
	}

	if ( feedbackElement ){
		if ( validation === 'feedback' || showFeedback ){
			feedbackElement.removeClass('hidden').show();
		} else {
			feedbackElement.addClass('hidden').hide();
			//element.removeClass('wpcf7-not-valid is-invalid is-valid').parent().find('.wpcf7-not-valid-tip').remove(); //What was this doing?
		}
	}
};

/*==========================
 Optimization Functions
 ===========================*/

//Lazy load images, styles, and JavaScript assets
//Can this be made asynchronous as a whole?
nebula.lazyLoadAssets = function(){
	//Lazy load elements as they scroll into viewport
	if ( 'IntersectionObserver' in window ){ //Only if Intersection Observer API is available. https://caniuse.com/#feat=intersectionobserver
		//Observe the entries that are identified and added later (below)
		var lazyObserver = new IntersectionObserver(function(entries){
			entries.forEach(function(entry){
				if ( entry.intersectionRatio > 0 ){
					nebula.loadElement(jQuery(entry.target));
					lazyObserver.unobserve(entry.target); //Stop observing the element
				}
			});
		}, {
			rootMargin: '100%', //Extend the area of the observer by the size of the viewport (essentially doubling the height of the detection area). This prevents visible loading of elements by triggering the load much earlier than actually needed.
			threshold: 0.1
		});

		//Create the entries and add them to the observer
		jQuery('.nebula-lazy-position, .lazy-load').each(function(){
			lazyObserver.observe(jQuery(this)[0]); //Observe the element
		});

		//When scroll reaches the bottom, ensure everything has loaded at this point
		//Only when IntersectionObserver exists because otherwise everything is immediately loaded anyway
		var lazyLoadScrollBottom = function(){
			if( nebula.dom.window.scrollTop()+nebula.dom.window.height() > nebula.dom.document.height()-500 ){ //When the scroll position reaches 500px above the bottom
				nebula.loadEverything();
				window.removeEventListener('scroll', lazyLoadScrollBottom); //Stop listening for this scroll event
			}
		};
		window.addEventListener('scroll', lazyLoadScrollBottom);
	} else {
		nebula.loadEverything(); //If IntersectionObserver is not available, load everything immediately
	}

	//Load all lazy elements at once if requested
	nebula.dom.window.on('nebula_load', function(){
		if ( typeof window.requestIdleCallback === 'function' ){ //If requestIdleCallback exists, use it
				window.requestIdleCallback(function(){
					nebula.loadEverything();
				});
		} else { //Otherwise, just run immediately
			nebula.loadEverything();
		}
	});

	//Lazy load CSS assets
	//@todo "Nebula" 0: listen for requestIdleCallback here after IE11 no longer supported (still need conditional per Safari): https://caniuse.com/#feat=requestidlecallback
	jQuery.each(nebula.site.resources.lazy.styles, function(handle, condition){
		if ( condition === 'all' || jQuery(condition).length ){
			if ( nebula.site.resources.styles[handle.replace(/-/g, '_')] ){ //If that handle exists in the registered styles
				nebula.loadCSS(nebula.site.resources.styles[handle.replace(/-/g, '_')]);
			}
		}
	});

	//Lazy load JS assets
	//@todo "Nebula" 0: listen for requestIdleCallback here after IE11 no longer supported (still need conditional per Safari): https://caniuse.com/#feat=requestidlecallback
	jQuery.each(nebula.site.resources.lazy.scripts, function(handle, condition){
		if ( condition === 'all' || jQuery(condition).length ){
			if ( nebula.site.resources.scripts[handle.replace(/-/g, '_')] ){ //If that handle exists in the registered scripts
				nebula.loadJS(nebula.site.resources.scripts[handle.replace(/-/g, '_')]);
			}
		}
	});

	nebula.initMmenu(); //Mmenu lazy load happens in its own function

	//Load the Google Maps API if 'googlemap' class exists
	if ( jQuery('.googlemap').length ){
		if ( typeof google == "undefined" || !nebula.has(google, 'maps') ){ //If the API has not already been called //@todo "Nebula" 0: Replace with optional chaining
			nebula.loadJS('https://www.google.com/jsapi?key=' + nebula.site.options.nebula_google_browser_api_key, function(){ //May not need key here, but just to be safe.
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
nebula.loadEverything = function(){
	//@todo "Nebula" 0: listen for requestIdleCallback here after IE11 no longer supported (still need conditional per Safari): https://caniuse.com/#feat=requestidlecallback
	jQuery('.nebula-lazy-position, .lazy-load, .nebula-lazy').each(function(){
		nebula.loadElement(jQuery(this)); //Load the element immediately
	});
};

//Load the Nebula lazy load element
nebula.loadElement = function(element){
	//Lazy elements using <samp> positioning
	if ( element.is('samp') ){
		var lazyElement = element.next('noscript.nebula-lazy');
		element.remove(); //Remove the positioning element

		//The actual lazy loaded element as a jQuery object
		var thisContent = jQuery(lazyElement.text()).on('load loadeddata', function(){ //Warning: DOM text is reinterpreted as HTML without escaping meta-characters. Not sure how to sanitize this?
			nebula.lazyVideoTracking(lazyElement);
		});

		lazyElement.replaceWith(thisContent); //Remove the <noscript> tag to reveal the img/iframe tag
		nebula.svgImgs(); //Convert certain <img> elements that use SVG into SVG elements
	}

	//Background images
	if ( element.hasClass('lazy-load') ){
		element.removeClass('lazy-load').addClass('lazy-loaded');
	}
};

//Load a JavaScript resource (and cache it)
nebula.loadJS = function(url, callback){
	//@todo "Nebula" 0: listen for requestIdleCallback after IE11 no longer supported (still need conditional per Safari): https://caniuse.com/#feat=requestidlecallback
	if ( typeof url === 'string' ){
		var lazyScriptElement = document.createElement('script'); //Create a script element
		lazyScriptElement.src = url; //Set the script src to the URL

		//Callback when element has loaded
		if ( callback ){
			lazyScriptElement.onload = callback;
		}

		//Track exceptions
		lazyScriptElement.onerror = function(){
			ga('send', 'exception', {'exDescription': '(JS) ' + url + ' could not be loaded', 'exFatal': false});
			nebula.crm('event', 'JavaScript resource could not be dynamically loaded');
		};

		document.body.appendChild(lazyScriptElement); //Add the new script to the DOM
	} else {
		console.error('nebula.loadJS() requires a valid URL.');
	}
};

//Dynamically load CSS files using JS
//If JavaScript is disabled, these are loaded via <noscript> tags
nebula.loadCSS = function(url){
	if ( typeof url === 'string' ){
		jQuery('head').append('<link rel="stylesheet" href="' + url + '" type="text/css" media="screen">');
	} else {
		console.error('nebula.loadCSS() requires a valid URL. The requested URL is invalid:', url);
	}
};

/* ==========================================================================
   Google Maps Functions
   ========================================================================== */

//Places - Address Autocomplete
//This uses the Google Maps Geocoding API
//The passed selector must be an input element
nebula.addressAutocomplete = function(autocompleteInput, uniqueID){
	if ( jQuery(autocompleteInput).length && jQuery(autocompleteInput).is('input') ){ //If the addressAutocomplete ID exists
		if ( !uniqueID ){
			uniqueID = 'unnamed';
		}

		if ( typeof google !== "undefined" && nebula.has(google, 'maps') ){ //@todo "Nebula" 0: Replace with optional chaining
			nebula.googleAddressAutocompleteCallback(autocompleteInput, uniqueID);
		} else {
			//Log all instances to be called after the maps JS library is loaded. This prevents the library from being loaded multiple times.
			if ( typeof autocompleteInputs === 'undefined' ){
				var autocompleteInputs = {};
			}
			autocompleteInputs[uniqueID] = autocompleteInput;

			nebula.debounce(function(){
				nebula.loadJS('https://www.google.com/jsapi?key=' + nebula.site.options.nebula_google_browser_api_key, function(){ //May not need key here, but just to be safe.
					google.load('maps', '3', {
						other_params: 'libraries=places&key=' + nebula.site.options.nebula_google_browser_api_key,
						callback: function(){
							jQuery.each(autocompleteInputs, function(uniqueID, input){
								nebula.googleAddressAutocompleteCallback(input, uniqueID);
							});
						}
					});
				});
			}, 100, 'google maps script load');
		}
	}
};

nebula.googleAddressAutocompleteCallback = function(autocompleteInput, uniqueID){
	if ( typeof uniqueID === 'undefined' || uniqueID === 'undefined' ){
		uniqueID = 'unnamed';
	}

	window[uniqueID] = new google.maps.places.Autocomplete(
		jQuery(autocompleteInput)[0],
		{types: ['geocode']} //Restrict the search to geographical location types
	);

	google.maps.event.addListener(window[uniqueID], 'place_changed', function(){ //When the user selects an address from the dropdown, populate the address fields in the form.
		var place = window[uniqueID].getPlace(); //Get the place details from the window[uniqueID] object.
		var simplePlace = nebula.sanitizeGooglePlaceData(place, uniqueID);

		var thisEvent = {
			category: 'Contact',
			action: 'Autocomplete Address',
			intent: 'Intent',
			place: place,
			simplePlace: simplePlace
		};

		nebula.dom.document.trigger('nebula_address_selected', [place, simplePlace, jQuery(autocompleteInput)]);
		nebula.dom.document.trigger('nebula_event', thisEvent);
		ga('set', nebula.analytics.dimensions.contactMethod, thisEvent.action);
		ga('send', 'event', 'Contact', 'Autocomplete Address', simplePlace.city + ', ' + simplePlace.state.abbr + ' ' + simplePlace.zip.code);

		nebula.crm('identify', {
			'street_number': simplePlace.street.number,
			'street_name': simplePlace.street.name,
			'street_full': simplePlace.street.full,
			'city': simplePlace.city,
			'county': simplePlace.county,
			'state': simplePlace.state.name,
			'country': simplePlace.country.name,
			'zip': simplePlace.zip.code,
			'address': simplePlace.street.full + ', ' + simplePlace.city + ', ' + simplePlace.state.abbr + ' ' + simplePlace.zip.code
		});
	});

	jQuery(autocompleteInput).on('focus', function(){
		if ( nebula.site.protocol === 'https' && navigator.geolocation ){
			navigator.geolocation.getCurrentPosition(function(position){ //Bias to the user's geographical location.
				var geolocation = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
				var circle = new google.maps.Circle({
					center: geolocation,
					radius: position.coords.accuracy
				});
				window[uniqueID].setBounds(circle.getBounds());
			});
		}
	}).on('keydown', function(e){
		if ( e.which === 13 && jQuery('.pac-container:visible').length ){ //Prevent form submission when enter key is pressed while the "Places Autocomplete" container is visbile
			return false;
		}
	});

	if ( autocompleteInput === '#address-autocomplete' ){
		nebula.dom.document.on('nebula_address_selected', function(){
			//do any default stuff here.
		});
	}
};

//Organize the Google Place data into an organized (and named) object
//Use uniqueID to name places like "home", "mailing", "billing", etc.
nebula.sanitizeGooglePlaceData = function(place, uniqueID){
	if ( !place ){
		console.error('Place data is required for sanitization.');
		return false;
	}

	if ( !uniqueID ){
		uniqueID = 'unnamed';
	}

	if ( typeof nebula.user.address === 'undefined' ){
		nebula.user.address = {};
	}

	if ( !Array.isArray(nebula.user.address) ){
		nebula.user.address = [];
	}

	nebula.user.address[uniqueID] = {
		street: {
			number: null,
			name: null
		},
		city: null,
		county: null,
		state: {
			name: null,
			abbr: null
		},
		country: {
			name: null,
			abbr: null
		},
		zip: {
			code: null,
			suffix: null
		}
	};

	for ( var i = 0; i < place.address_components.length; i++ ){
		//Lots of different address types. This function uses only the common ones: https://developers.google.com/maps/documentation/geocoding/#Types
		switch ( place.address_components[i].types[0] ){
			case "street_number":
				nebula.user.address[uniqueID].street.number = place.address_components[i].short_name; //123
				break;
			case "route":
				nebula.user.address[uniqueID].street.name = place.address_components[i].long_name; //Street Name Rd.
				break;
			case "locality":
				nebula.user.address[uniqueID].city = place.address_components[i].long_name; //Liverpool
				break;
			case "administrative_area_level_2":
				nebula.user.address[uniqueID].county = place.address_components[i].long_name; //Onondaga County
				break;
			case "administrative_area_level_1":
				nebula.user.address[uniqueID].state.name = place.address_components[i].long_name; //New York
				nebula.user.address[uniqueID].state.abbr = place.address_components[i].short_name; //NY
				break;
			case "country":
				nebula.user.address[uniqueID].country.name = place.address_components[i].long_name; //United States
				nebula.user.address[uniqueID].country.abbr = place.address_components[i].short_name; //US
				break;
			case "postal_code":
				nebula.user.address[uniqueID].zip.code = place.address_components[i].short_name; //13088
				break;
			case "postal_code_suffix":
				nebula.user.address[uniqueID].zip.suffix = place.address_components[i].short_name; //4725
				break;
			default:
				//console.log('Address component ' + place.address_components[i].types[0] + ' not used.');
		}
	}

	if ( nebula.user.address[uniqueID].street.number && nebula.user.address[uniqueID].street.name ){
		nebula.user.address[uniqueID].street.full = nebula.user.address[uniqueID].street.number + ' ' + nebula.user.address[uniqueID].street.name;
	}

	if ( nebula.user.address[uniqueID].zip.code && nebula.user.address[uniqueID].zip.suffix ){
		nebula.user.address[uniqueID].zip.full = nebula.user.address[uniqueID].zip.code + '-' + nebula.user.address[uniqueID].zip.suffix;
	}

	return nebula.user.address[uniqueID];
};

//Request Geolocation
function requestPosition(){
	if ( typeof google !== 'undefined' && nebula.has(google, 'maps') ){ //@todo "Nebula" 0: Replace with optional chaining
		nebula.loadJS('https://www.google.com/jsapi?key=' + nebula.site.options.nebula_google_browser_api_key, function(){ //May not need key here, but just to be safe.
			google.load('maps', '3', {
				other_params: 'libraries=placeskey=' + nebula.site.options.nebula_google_browser_api_key,
				callback: function(){
					nebula.getCurrentPosition();
				}
			});
		});
	} else {
		getCurrentPosition();
	}
}

nebula.getCurrentPosition = function(){
	var nav = null;
	if (nav === null){
		nav = window.navigator;
	}
	var geolocation = nav.geolocation;
	if ( geolocation != null ){
		geolocation.getCurrentPosition(geoSuccessCallback, geoErrorCallback, {enableHighAccuracy: true}); //One-time location poll
		//geoloc.watchPosition(successCallback, errorCallback, {enableHighAccuracy: true}); //Continuous location poll (This will update the nebula.session.geolocation object regularly, but be careful sending events to GA- may result in TONS of events)
	}
};

//Geolocation Success
function geoSuccessCallback(position){
	nebula.session.geolocation = {
		error: false,
		coordinates: { //A value in decimal degrees to an precision of 4 decimal places is precise to 11.132 meters at the equator. A value in decimal degrees to 5 decimal places is precise to 1.1132 meter at the equator.
			latitude: position.coords.latitude,
			longitude: position.coords.longitude
		},
		accuracy: {
			meters: position.coords.accuracy,
			miles: (position.coords.accuracy*0.000621371).toFixed(2),
		},
		altitude: { //Above the mean sea level
			meters: position.coords.altitude,
			miles: (position.coords.altitude*0.000621371).toFixed(2),
			accuracy: position.coords.altitudeAccuracy,
		},
		speed: {
			mps: position.coords.speed,
			kph: (position.coords.speed*3.6).toFixed(2),
			mph: (position.coords.speed*2.23694).toFixed(2),
		},
		heading: position.coords.heading, //Degrees clockwise from North
		address: false
	};

	if ( nebula.session.geolocation.accuracy.meters < 50 ){
		nebula.session.geolocation.accuracy.color = '#00bb00';
		ga('set', nebula.analytics.dimensions.geoAccuracy, 'Excellent (<50m)');
	} else if ( nebula.session.geolocation.accuracy.meters > 50 && nebula.session.geolocation.accuracy.meters < 300 ){
		nebula.session.geolocation.accuracy.color = '#a4ed00';
		ga('set', nebula.analytics.dimensions.geoAccuracy, 'Good (50m - 300m)');
	} else if ( nebula.session.geolocation.accuracy.meters > 300 && nebula.session.geolocation.accuracy.meters < 1500 ){
		nebula.session.geolocation.accuracy.color = '#ffc600';
		ga('set', nebula.analytics.dimensions.geoAccuracy, 'Poor (300m - 1500m)');
	} else {
		nebula.session.geolocation.accuracy.color = '#ff1900';
		ga('set', nebula.analytics.dimensions.geoAccuracy, 'Very Poor (>1500m)');
	}

	nebula.addressLookup(position.coords.latitude, position.coords.longitude);

	sessionStorage['nebulaSession'] = JSON.stringify(nebula.session);
	nebula.dom.document.trigger('geolocationSuccess', nebula.session.geolocation);
	nebula.dom.body.addClass('geo-latlng-' + nebula.session.geolocation.coordinates.latitude.toFixed(4).replace('.', '_') + '_' + nebula.session.geolocation.coordinates.longitude.toFixed(4).replace('.', '_') + ' geo-acc-' + nebula.session.geolocation.accuracy.meters.toFixed(0).replace('.', ''));
	ga('set', nebula.analytics.dimensions.geolocation, nebula.session.geolocation.coordinates.latitude.toFixed(4) + ', ' + nebula.session.geolocation.coordinates.longitude.toFixed(4));
	ga('send', 'event', 'Geolocation', nebula.session.geolocation.coordinates.latitude.toFixed(4) + ', ' + nebula.session.geolocation.coordinates.longitude.toFixed(4), 'Accuracy: ' + nebula.session.geolocation.accuracy.meters.toFixed(2) + ' meters');
	nebula.crm('identify', {'geolocation': nebula.session.geolocation.coordinates.latitude.toFixed(4) + ', ' + nebula.session.geolocation.coordinates.longitude.toFixed(4) + ' (Accuracy: ' + nebula.session.geolocation.accuracy.meters.toFixed(2) + ' meters'});
}

//Geolocation Error
function geoErrorCallback(error){
	switch (error.code){
		case error.PERMISSION_DENIED:
			var geolocationErrorMessage = 'Access to your location is turned off. Change your settings to report location data.';
			var geoErrorNote = 'Denied';
			break;
		case error.POSITION_UNAVAILABLE:
			var geolocationErrorMessage = "Data from location services is currently unavailable.";
			var geoErrorNote = 'Unavailable';
			break;
		case error.TIMEOUT:
			var geolocationErrorMessage = "Location could not be determined within a specified timeout period.";
			var geoErrorNote = 'Timeout';
			break;
		default:
			var geolocationErrorMessage = "An unknown error has occurred.";
			var geoErrorNote = 'Error';
			break;
	}

	nebula.session.geolocation = {
		error: {
			code: error.code,
			description: geolocationErrorMessage
		}
	};

	nebula.dom.document.trigger('geolocationError');
	nebula.dom.body.addClass('geo-error');
	ga('set', nebula.analytics.dimensions.geolocation, geolocationErrorMessage);
	ga('send', 'exception', {'exDescription': '(JS) Geolocation error: ' + geolocationErrorMessage, 'exFatal': false});
	nebula.crm('event', 'Geolocation Error');
}


//Rough address Lookup
//If needing to look up an address that isn't the user's geolocation based on lat/long, consider a different function. This one stores user data.
nebula.addressLookup = function(lat, lng){
	var geocoder = new google.maps.Geocoder();
	var latlng = new google.maps.LatLng(lat, lng); //lat, lng
	geocoder.geocode({'latLng': latlng}, function(results, status){
		if ( status === google.maps.GeocoderStatus.OK ){
			if ( results ){
				nebula.session.geolocation.address = {
					number: nebula.extractFromAddress(results[0].address_components, "street_number"),
					street: nebula.extractFromAddress(results[0].address_components, "route"),
					city: nebula.extractFromAddress(results[0].address_components, "locality"),
					town: nebula.extractFromAddress(results[0].address_components, "administrative_area_level_3"),
					county: nebula.extractFromAddress(results[0].address_components, "administrative_area_level_2"),
					state: nebula.extractFromAddress(results[0].address_components, "administrative_area_level_1"),
					country: nebula.extractFromAddress(results[0].address_components, "country"),
					zip: nebula.extractFromAddress(results[0].address_components, "postal_code"),
					formatted: results[0].formatted_address,
					place: {
						id: results[0].place_id,
					},
				};
				nebula.crm('identify', {'address_lookup': results[0].formatted_address});

				sessionStorage['nebulaSession'] = JSON.stringify(nebula.session);
				nebula.dom.document.trigger('addressSuccess');
				if ( nebula.session.geolocation.accuracy.meters < 100 ){
					nebula.placeLookup(results[0].place_id);
				}
			}
		}
	});
};

//Extract address components from Google Maps Geocoder
nebula.extractFromAddress = function(components, type){
	for ( var i = 0; i < components.length; i++ ){
		for ( var j = 0; j < components[i].types.length; j++ ){
			if ( components[i].types[j] === type ){
				return components[i].long_name;
			}
		}
	}

	return '';
};

//Lookup place information
nebula.placeLookup = function(placeID){
	if ( nebula.has(google, 'maps.places') ){ //@todo "Nebula" 0: Replace with optional chaining
		var service = new google.maps.places.PlacesService(jQuery('<div></div>').get(0));
		service.getDetails({
			placeId: placeID
		}, function(place, status){
			if ( status === google.maps.places.PlacesServiceStatus.OK ){
				if ( typeof place.name !== 'undefined' ){
					nebula.session.geolocation.address.place = {
						id: placeID,
						name: place.name,
						url: place.url,
						website: place.website,
						phone: place.formatted_phone_number,
						ratings: {
							rating: place.rating,
							total: place.user_ratings_total,
							reviews: ( typeof place.reviews !== 'undefined' )? place.reviews.length : 0,
						},
						utc_offset: place.utc_offset,
					};

					sessionStorage['nebulaSession'] = JSON.stringify(nebula.session);
					nebula.dom.document.trigger('placeSuccess');
				}
			}
		});
	}
};

/*==========================
 Helper Functions
 These functions enhance other aspects of the site like HTML/CSS.
 ===========================*/

//Miscellaneous helper classes and functions
nebula.helpers = function(){
	//Remove Sass render trigger query
	if ( nebula.get('sass') && !nebula.get('persistent') && window.history.replaceState ){ //IE10+
		window.history.replaceState({}, document.title, nebula.removeQueryParameter('sass', window.location.href));
	}

	nebula.dom.html.removeClass('no-js').addClass('js');
	jQuery("a[href^='http']:not([href*='" + nebula.site.domain + "'])").attr('rel', 'nofollow external noopener'); //Add rel attributes to external links. Although search crawlers do use JavaScript, don't rely on this line to instruct them. Use standard HTML attributes whenever possible.

	if ( 'deviceMemory' in navigator ){ //Device Memory - Chrome 64+
		var deviceMemoryLevel = navigator.deviceMemory < 1 ? 'lite' : 'full';
		nebula.dom.html.addClass('device-memory-' + deviceMemoryLevel);
	}

	//Remove filetype icons from images within <a> tags and buttons.
	jQuery('a img').closest('a').addClass('no-icon');
	jQuery('.no-icon:not(a)').find('a').addClass('no-icon');

	jQuery('span.nebula-code').parent('p').css('margin-bottom', '0px'); //Fix for <p> tags wrapping Nebula pre spans in the WYSIWYG

	//Maintain tab navigability on hashchange (and when loaded with a hash). This also helps accessibility for things like skip to content links
	if ( document.location.hash ){
		nebula.focusOnElement(jQuery(document.location.hash));
	}

	//If the hash has been changed (activation of an in-page link)
	nebula.dom.window.on('hashchange', function(){
		var hash = window.location.hash.replace(/^#/, '');
		if ( hash ){ //If the hash is not empty (like when clicking on an href="#" link)
			nebula.focusOnElement(jQuery('#' + hash));
		}
	});

	//Change the Bootstrap label for custom file upload inputs on upload
	jQuery('input[type="file"].custom-file-input').on('change', function(){
		if ( jQuery(this).parents('.custom-file').find('.custom-file-label').length ){
			var fileName = jQuery(this).val().split('\\').pop(); //Get the filename without the full path
			jQuery(this).parents('.custom-file').find('.custom-file-label').text(fileName);
		}
	});

	nebula.dragDropUpload();
};

//Enable drag and drop uploading for Contact Form 7 file inputs
nebula.dragDropUpload = function(){
	if ( jQuery('.nebula-drop-area').length ){
		//Activate drag and drop listeners for each drop area class on the page
		document.querySelectorAll('.nebula-drop-area').forEach(function(dropArea){
			var thisEvent = {
				category: 'Drag and Drop File Upload',
				formID: jQuery(dropArea).closest('form').attr('id') || 'form.' + jQuery(dropArea).closest('form').attr('class').replace(/\s/g, '.'),
				fileInputID: jQuery(dropArea).find('input[type="file"]').attr('id'),
			};

			//Drag over
			dropArea.addEventListener('dragover', function(e){ //This gets called every frame of the hover... Can we throttle it without causing a problem?
				e.stopPropagation();
				e.preventDefault();

				jQuery(dropArea).addClass('dragover');
				e.dataTransfer.dropEffect = 'copy'; //Visualize to the user the "copy" cursor

				nebula.debounce(function(){
					thisEvent.action = 'Drag Over';
					nebula.dom.document.trigger('nebula_event', thisEvent);
					ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.fileInputID);
				}, 500, 'file drag over');
			});

			//Drag out
			dropArea.addEventListener('dragleave', function(e){
				jQuery(dropArea).addClass('dragover');

				thisEvent.action = 'Drag Leave';
				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.fileInputID);
			});

			//Drop
			dropArea.addEventListener('drop', function(e){
				e.stopPropagation();
				e.preventDefault();

				jQuery(dropArea).removeClass('dragover');

				var fileInput = dropArea.querySelectorAll('input[type="file"]')[0]; //Find the file input field within this drop area
				var acceptedFiles = jQuery(fileInput).attr('accept').replace(/\s?\./g, '').split(',');
				var thisFileType = e.dataTransfer.files[0].type.replace(/\S+\//, '');

				thisEvent.fileType = thisFileType;
				thisEvent.file = e.dataTransfer.files[0];

				if ( !jQuery(fileInput).attr('accept').length || (e.dataTransfer.files.length === 1 && acceptedFiles.indexOf(thisFileType) != -1) ){ //If the uploader does not restrict file types, or if only one file was uploaded and that filetype is accepted
					jQuery(dropArea).addClass('dropped is-valid');

					fileInput.files = e.dataTransfer.files; //Fill the file upload input with the uploaded file
					jQuery(fileInput).parents('.custom-file').find('.custom-file-label').text(e.dataTransfer.files[0].name); //Update the Bootstrap label to show the filename

					thisEvent.action = 'Dropped (Accepted)';
					nebula.dom.document.trigger('nebula_event', thisEvent);
					ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.fileType);

				} else {
					nebula.temporaryClass(jQuery(dropArea), 'rejected', '', 1500);
					nebula.applyValidationClasses(jQuery(fileInput), 'invalid', true); //Show the invalid message

					thisEvent.action = 'Dropped (Rejected)';
					nebula.dom.document.trigger('nebula_event', thisEvent);
					ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.fileType);
				}
			});
		});
	}
};

nebula.initBootstrapFunctions = function(){
	if ( typeof bootstrap !== 'undefined' ){
		//Tooltips
		if ( jQuery('[data-toggle="tooltip"]').length ){
			jQuery('[data-toggle="tooltip"]').tooltip();
		}

		//Popovers
		if ( jQuery('[data-toggle="popover"]').length ){
			jQuery('[data-toggle="popover"]').popover({'trigger': 'hover'});
		}

		nebula.checkBootstrapToggleButtons();
		jQuery('[data-toggle=buttons] input').on('change', function(){
			nebula.checkBootstrapToggleButtons();
		});

		//Carousels - Override this to customize options
		if ( jQuery('.carousel').length ){
			jQuery('.carousel').each(function(){
				if ( jQuery(this).hasClass('auto-indicators') ){
					var carouselID = jQuery(this).attr('id');
					var slideCount = jQuery(this).find('.carousel-item').length;

					var i = 0;
					var markup = '<ol class="carousel-indicators">'; //@TODO "Nebula" 0: Why is there no space between indicators when using this auto-indicators?
					while ( i < slideCount ){
						var active = ( i === 0 )? 'class="active"' : '';
						markup += '<li data-target="#' + carouselID + '" data-slide-to="' + i + '" ' + active + '></li>';
						i++;
					}
					markup += '</ol>';
					jQuery(this).prepend(markup);
					jQuery(this).find('.carousel-item').first().addClass('active');

					if ( !jQuery(this).find('.carousel-inner').length ){
						jQuery(this).find('.carousel-item').wrapAll('<div class="carousel-inner">');
					}
				}

				jQuery(this).carousel();
			});
		}

		//Allow Bootstrap modals to use Nebula animation transitions
		//Place the data-animation attribue on the .modal div (which is what e.target is)
		nebula.dom.document.on('show.bs.modal', function(e){
			if ( jQuery(e.target).attr('data-animation-in') || jQuery(e.target).attr('data-animation') || jQuery(e.target).attr('data-animation-out') ){ //If there is any Nebula animation attribute
				var anim = jQuery(e.target).attr('data-animation-in') || jQuery(e.target).attr('data-animation') || '';

				if ( !jQuery('#' + e.target.id + ' .modal-dialog').attr('data-original-classes') ){
					jQuery('#' + e.target.id + ' .modal-dialog').attr('data-original-classes', jQuery('#' + e.target.id + ' .modal-dialog').attr('class')); //Store the original classes in a data-attribute to use later
				}

				if ( anim ){
					var originalClasses = jQuery('#' + e.target.id + ' .modal-dialog').attr('data-original-classes');
					jQuery('#' + e.target.id + ' .modal-dialog').attr('class', originalClasses + ' ' + anim + ' animate'); //Replace classes each time for re-animation.
				}
			}
		});
		nebula.dom.document.on('hide.bs.modal', function(e){
			if ( jQuery(e.target).attr('data-animation-in') || jQuery(e.target).attr('data-animation') || jQuery(e.target).attr('data-animation-out') ){ //If there is any Nebula animation attribute
				var anim = jQuery(e.target).attr('data-animation-out') || '';

				if ( anim ){
					var originalClasses = jQuery('#' + e.target.id + ' .modal-dialog').attr('data-original-classes');
					jQuery('#' + e.target.id + ' .modal-dialog').attr('class', originalClasses + ' ' + anim + ' animate'); //Replace classes each time for re-animation.
				}
			}
		});
	}
};

//Add an "inactive" class to toggle buttons when one is checked to allow for additional styling options
nebula.checkBootstrapToggleButtons = function(){
	jQuery('[data-toggle=buttons]').each(function(){
		if ( jQuery(this).find('input:checked').length ){
			jQuery(this).find('input').each(function(){
				if ( jQuery(this).is(':checked') ){
					jQuery(this).closest('.btn').removeClass('inactive');
				} else {
					jQuery(this).closest('.btn').addClass('inactive');
				}
			});
		}
	});
};

//Try to fix some errors automatically
nebula.errorMitigation = function(){
	//Try to fall back to .png on .svg errors. Else log the broken image.
	jQuery('img').on('error', function(){
		var thisImage = jQuery(this);
		var imagePath = thisImage.attr('src');
		if ( imagePath.split('.').pop() === 'svg' ){
			var fallbackPNG = imagePath.replace('.svg', '.png');
			jQuery.get(fallbackPNG).done(function(){
				thisImage.prop('src', fallbackPNG);
				thisImage.removeClass('svg');
			}).fail(function() {
				ga('send', 'exception', {'exDescription': '(JS) Broken Image: ' + imagePath, 'exFatal': false});
				nebula.crm('event', 'Broken Image');
			});
		} else {
			ga('send', 'exception', {'exDescription': '(JS) Broken Image: ' + imagePath, 'exFatal': false});
			nebula.crm('event', 'Broken Image');
		}
	});
};

//Convert img tags with class .svg to raw SVG elements
nebula.svgImgs = function(){
	jQuery('img.svg').each(function(){
		var oThis = jQuery(this);

		if ( oThis.attr('src').indexOf('.svg') >= 1 ){
			jQuery.get(oThis.attr('src'), function(data){
				var theSVG = jQuery(data).find('svg'); //Get the SVG tag, ignore the rest
				theSVG = theSVG.attr('id', oThis.attr('id')); //Add replaced image's ID to the new SVG
				theSVG = theSVG.attr('class', oThis.attr('class') + ' replaced-svg'); //Add replaced image's classes to the new SVG
				theSVG = theSVG.attr('role', 'img');
				theSVG = theSVG.attr('data-original-src', oThis.attr('src')); //Add an attribute of the original SVG location
				theSVG = theSVG.removeAttr('xmlns:a'); //Remove invalid XML tags

				oThis.replaceWith(theSVG); //Replace image with new SVG

				//Move alt attribute to title element within the SVG
				if ( oThis.attr('alt') ){
					theSVG.prepend('<title>' + nebula.sanitize(oThis.attr('alt')) + '</title>'); //Sanitized to prevent XSS
				}

				//Move the title attribute to the description element within the SVG
				if ( oThis.attr('title') ){
					theSVG.prepend('<description>' + nebula.sanitize(oThis.attr('title')) + '</description>'); //Sanitized to prevent XSS
				}
			}, 'xml');
		}
	});
};

//Scroll an element into view
//This can eventually be replaced with scrollIntoView() native JS function, but until it has a timing feature it is not as robust. Also smooth scroll-behavior in CSS interferes with this.
//Note: Offset must be an integer
nebula.scrollTo = function(element, scrollSpeed, offset, onlyWhenBelow, callback){
	if ( nebula.dom.html.css('scroll-behavior') !== 'smooth' ){ //If the html has smooth scroll-behavior, use that instead of this.
		if ( !offset ){
			var offset = nebula.scroll.offset || 0; //Note: This selector should be the height of the fixed header, or a hard-coded offset.
		}

		//Account for the scroll-padding-top CSS property on the body element
		var scrollPaddingTop = parseInt(nebula.dom.body.css('scroll-padding-top'), 10); //Parse the CSS value as a base-10 integer
		if ( !isNaN(scrollPaddingTop) ){
			offset = offset + scrollPaddingTop;
		}

		//Call this function with a jQuery object to trigger scroll to an element (not just a selector string).
		if ( element ){
			if ( typeof element === 'string' ){
				element = jQuery(element);
			}

			if ( element.length ){
				var willScroll = true;
				if ( onlyWhenBelow ){
					var elementTop = element.offset().top-offset;
					var viewportTop = nebula.dom.document.scrollTop();
					if ( viewportTop-elementTop <= 0 ){
						willScroll = false;
					}
				}

				if ( willScroll ){
					if ( !scrollSpeed ){
						var scrollSpeed = nebula.scroll.speed || 500;
					}

					jQuery('html, body').animate({
						scrollTop: element.offset().top-offset
					}, scrollSpeed, function(){
						nebula.focusOnElement(element);

						if ( callback ){
							callback();
						}
					});
				}
			}

			return false;
		}

		nebula.dom.document.on('click keyup', 'a[href^="#"]:not([href="#"])', function(e){ //An href starts with a hash ID but is not only a hash ("#content" but not "#"). Do not use *="#" to prevent conflicts with other libraries who are linking to separate pages with an anchor on the destination.
			if ( e.type === 'click' || (e.type === 'keyup' && (e.keyCode === 32 || e.keyCode === 13)) ){ //Spacebar or Enter
				var avoid = '.no-scroll, .mm-menu, .carousel, .tab-content, .modal, [data-toggle], #wpadminbar, #query-monitor';
				if ( !jQuery(this).is(avoid) && !jQuery(this).parents(avoid).length ){
					if ( location.pathname.replace(/^\//, '') === this.pathname.replace(/^\//, '') && location.hostname === this.hostname ){ //Ensure the link does not have a protocol and is internal
						var thisHash = this.hash;
						var target = jQuery(thisHash) || jQuery('[name=' + thisHash.slice(1) +']'); //Determine the target
						if ( target.length ){ //If target exists
							var pOffset = ( jQuery(this).attr('data-offset') )? parseFloat(jQuery(this).attr('data-offset')) : nebula.scroll.offset; //Determine the offset
							var nOffset = Math.floor(target.offset().top-offset+pOffset) + jQuery('body').scrollTop();
							scrollSpeed = nebula.scroll.speed || 500;

							//Eventually replace with target.scrollIntoView({behavior: 'smooth'});

							jQuery('html, body').animate({
								scrollTop: nOffset
							}, scrollSpeed, function(){
								nebula.focusOnElement(target);
								history.replaceState({}, '', thisHash); //Add the hash to the URL so it can be refreshed, copied, links, etc. ReplaceState does this without affecting the back button.
							}); //Speed is hard-coded, but could look for an HTML attribute if desired
							return false;
						}
					}
				}
			}
		});

		nebula.dom.document.on('click keyup', '.nebula-scrollto', function(e){ //Using the nebula-scrollto class with scrollto attribute.
			if ( e.type === 'click' || (e.type === 'keyup' && (e.keyCode === 32 || e.keyCode === 13)) ){ //Spacebar or Enter
				var pOffset = ( jQuery(this).attr('offset') )? parseFloat(jQuery(this).attr('offset')) : nebula.scroll.offset;

				if ( jQuery(this).attr('scrollto') ){
					var scrollElement = jQuery(this).attr('scroll-to');
					if ( scrollElement !== '' ){
						scrollSpeed = nebula.scroll.speed || 500;

						//Eventually replace with scrollElement.scrollIntoView({behavior: 'smooth'});

						jQuery('html, body').animate({
							scrollTop: Math.floor(jQuery(scrollElement).offset().top-offset+pOffset)
						}, scrollSpeed, function(){
							nebula.focusOnElement(scrollElement);
						});
					}
				}

				return false;
			}
		});
	}
};

//Temporarily change a Font Awesome icon and then change back after a period of time
nebula.temporaryClass = function(element, activeClass, inactiveClass, period){
	if ( element && activeClass ){
		if ( typeof element === 'string' ){
			element = jQuery(element);
		}

		if ( !inactiveClass ){
			if ( element.is('fa, fas, far, fab, fad') ){
				inactiveClass = /fa-(?!fw)\S+/i.test(element.attr('class')); //Match the first Font Awesome icon class that is the actual icon
			} else {
				inactiveClass = ''; //Set to an empty string to only use a temporary active class
			}
		}

		if ( !period ){
			period = 1500;
		}

		element.removeClass(inactiveClass).addClass(activeClass + ' temporary-status-active'); //Remove the inactive class and add the active class
		setTimeout(function(){
			element.removeClass(activeClass + ' temporary-status-active').addClass(inactiveClass); //After the period of time, revert back to the inactive class
		}, period);
	}

	return false;
};

/*==========================
 Utility Functions
 These functions simplify and enhance other JavaScript functions
 ===========================*/

//Focus on an element
nebula.focusOnElement = function(element){
	if ( !element.length ){
		return;
	}

	//If the element is not focusable itself, add tabindex to make focusable and remove again
	if ( !element.is(':focusable') ){ //Uses custom expression defined at the bottom of this file
		element.attr('tabindex', -1).on('blur focusout', function(){
			jQuery(this).removeAttr('tabindex');
		});
	}

	element.trigger('focus'); //Focus on the element
};

//Get query string parameters
nebula.getQueryStrings = function(url, format){
	if ( !url ){
		url = document.URL;
	}

	if ( !format ){
		format = 'object';
	}

	var queryString = url.split('?')[1];

	if ( queryString ){
		if ( format === 'string' ){
			return '?' + queryString;
		}

		var queries = {};
		var queryStrings = queryString.split('&');
		for ( var i = 0; i < queryStrings.length; i++ ){
			var hash = queryStrings[i].split('=');
			if ( hash[1] ){
				queries[hash[0]] = hash[1];
			} else {
				queries[hash[0]] = true;
			}
		}

		return queries;
	}

	if ( format === 'string' ){
		return '';
	}

	return false;
};

//Search query strings for the passed parameter
nebula.get = function(parameter, url){
	var queries = nebula.getQueryStrings(url);

	if ( !parameter ){
		return queries;
	}

	return queries[parameter] || false;
};

//Remove an array of parameters from the query string.
//@todo "Nebula" 0: In the future (once IE is dead), consider using the URL API instead. https://caniuse.com/#feat=url
nebula.removeQueryParameter = function(keys, sourceURL){
	if ( typeof keys === 'string' ){
		keys = [keys];
	}

	var newURL; //Establish the scope

	jQuery.each(keys, function(index, item){
		var url = sourceURL;

		if ( typeof newURL !== 'undefined' ){
			url = newURL;
		}

		var baseURL = url.split('?')[0];
		var param;
		var params_arr = [];
		var queryString = ( url.indexOf('?') !== -1 )? url.split('?')[1] : '';

		if ( queryString !== '' ){
			params_arr = queryString.split('&');
			for ( var i = params_arr.length-1; i >= 0; i -= 1 ){
				param = params_arr[i].split('=')[0];
				if ( param === item ){
					params_arr.splice(i, 1);
				}
			}

			newURL = baseURL + '?' + params_arr.join('&');
		}
	});

	//Check if it is empty after parameter removal
	if ( typeof newURL !== 'undefined' && newURL.split('?')[1] === '' ){
		return newURL.split("?")[0]; //Return the URL without a query
	}

	return newURL;
};

//Trigger a reflow on an element.
//This is useful for repeating animations.
nebula.reflow = function(selector){
	if ( typeof selector === 'string' ){
		var element = jQuery(selector);
	} else if ( typeof selector === 'object' ) {
		var element = selector;
	} else {
		return false;
	}

	element.width();
};

//Handle repeated animations in a single function.
nebula.animate = function(selector, newAnimationClasses, oldAnimationClasses){
	if ( typeof selector === 'string' ){
		var element = jQuery(selector);
	} else if ( typeof selector === 'object' ) {
		var element = selector;
	} else {
		return false;
	}

	newAnimationClasses += ' animate';
	element.removeClass(newAnimationClasses); //Remove classes first so they can be re-added.

	if ( oldAnimationClasses ){
		element.removeClass(oldAnimationClasses); //Remove conflicting animation classes.
	}

	nebula.reflow(element); //Refresh the element so it can be animated again.
	element.addClass(newAnimationClasses); //Animate the element.
};

//Helpful animation event listeners
nebula.animationTriggers = function(){
	//On document ready
	jQuery('.ready').each(function(){
		nebula.loadAnimate(jQuery(this));
	});

	//On window load
	nebula.dom.window.on('load', function(){
		jQuery('.load').each(function(){
			nebula.loadAnimate(jQuery(this));
		});
	});

	//On click
	nebula.dom.document.on('click', '.click, [nebula-click]', function(){
		var animationClass = jQuery(this).attr('nebula-click') || '';
		nebula.animate(jQuery(this), animationClass);
	});
};

nebula.loadAnimate = function(oThis){
	var animationDelay = oThis.attr('nebula-delay');
	if ( typeof animationDelay === 'undefined' || animationDelay === 0 ){
		nebula.animate(oThis, 'load-animate');
	} else {
		setTimeout(function(){
			nebula.animate(oThis, 'load-animate');
		}, animationDelay);
	}
};

//Allows something to be called once per pageload.
//Call without self-executing parenthesis in the parameter! Ex: nebula.once(customFunction, 'test example');
//To add parameters, use an array as the 2nd parameter. Ex: nebula.once(customFunction, ['parameter1', 'parameter2'], 'test example');
//Can be used for boolean. Ex: nebula.once('boolean test');
nebula.once = function(fn, args, unique){
	if ( typeof nebula.onces === 'undefined' ){
		nebula.onces = {};
	}

	if ( typeof args === 'string' ){ //If no parameters
		unique = args;
		args = [];
	}

	//Reset all
	if ( fn === 'clear' || fn === 'reset' ){
		nebula.onces = {};
	}

	//Remove a single entry
	if ( fn === 'remove' ){
		delete nebula.onces[unique];
	}

	if ( typeof fn === 'function' ){ //If the first parameter is a function
		if ( typeof nebula.onces[unique] === 'undefined' || !nebula.onces[unique] ){ //@todo "Nebula" 0: Use optional chaining?
			nebula.onces[unique] = true;
			return fn.apply(this, args);
		}
	} else { //Else return boolean
		unique = fn; //If only one parameter is passed
		if ( typeof nebula.onces[unique] === 'undefined' || !nebula.onces[unique] ){ //@todo "Nebula" 0: Use optional chaining?
			nebula.onces[unique] = true;
			return true;
		} else {
			return false;
		}
	}
};

//Waits for events to finish before triggering
//Passing immediate triggers the function on the leading edge (instead of the trailing edge).
nebula.debounce = function(callback, wait, uniqueID, immediate){
	if ( !callback ){
		console.error('nebula.debounce() requires a callback function.');
		return false;
	}

	if ( typeof nebula.debounceTimers === "undefined" ){
		nebula.debounceTimers = {};
	}

	if ( !wait ){
		wait = 1000; //If no wait time is supplied, use 1 second
	}

	if ( !uniqueID ){
		uniqueID = "Don't call this twice without a uniqueID";
	}

	var context = this;
	var args = arguments;
	var later = function(){
		nebula.debounceTimers[uniqueID] = null;
		if ( !immediate ){
			callback.apply(context, args);
		}
	};
	var callNow = immediate && !nebula.debounceTimers[uniqueID];

	clearTimeout(nebula.debounceTimers[uniqueID]); //Clear the timeout on every event. Once events stop the timeout is allowed to complete.
	nebula.debounceTimers[uniqueID] = setTimeout(later, wait);
	if ( callNow ){
		callback.apply(context, args);
	}
};

//Limit functionality to only run once per specified time period
nebula.throttle = function(callback, cooldown, uniqueID){
	if ( typeof nebula.throttleTimers === "undefined" ){
		nebula.throttleTimers = {};
	}

	if ( !uniqueID ){
		uniqueID = "Don't call this twice without a uniqueID";
	}

	var context = this;
	var args = arguments;
	var later = function(){
		if ( typeof nebula.throttleTimers[uniqueID] === 'undefined' ){ //If we're not waiting
			window.requestAnimationFrame(function(){
				callback.apply(context, args); //Execute callback function

				nebula.throttleTimers[uniqueID] = 'waiting'; //Prevent future invocations

				//After the cooldown period, allow future invocations
				setTimeout(function(){
					nebula.throttleTimers[uniqueID] = undefined; //Allow future invocations (undefined means it is not waiting)
				}, cooldown);
			});
		}
	};

    return later();
};

//Cookie Management
nebula.createCookie = function(name, value, days){
	if ( !days ){
		var days = 3650; //10 years
	}

	if ( days ){
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires=" + date.toGMTString(); //Note: Do not let this cookie expire past 2038 or it instantly expires. http://en.wikipedia.org/wiki/Year_2038_problem
	} else {
		var expires = "";
	}
	document.cookie = name + "=" + value + expires + "; path=/;SameSite=Lax";
};

nebula.readCookie = function(name){
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for ( var i = 0; i < ca.length; i++ ){
		var c = ca[i];
		while ( c.charAt(0) === ' ' ){
			c = c.substring(1, c.length);
		}
		if ( c.indexOf(nameEQ) === 0 ){
			return c.substring(nameEQ.length, c.length);
		}
	}
	return null;
};

nebula.eraseCookie = function(name){
	nebula.createCookie(name, "", -1);
};

//Time specific events. Unique ID is required. Returns time in milliseconds.
//Data can be accessed outside of this function via nebula.timings array.
nebula.timer = function(uniqueID, action, name){
	if ( !window.performance ){ //Safari 11+
		return false;
	}

	if ( typeof nebula.timings === 'undefined' ){
		nebula.timings = [];
	}

	//uniqueID is required
	if ( !uniqueID || uniqueID === 'start' || uniqueID === 'lap' || uniqueID === 'end' ){
		return false;
	}

	if ( !action ){
		if ( typeof nebula.timings[uniqueID] === 'undefined' ){
			action = 'start';
		} else {
			action = 'lap';
		}
	}

	//Can not lap or end a timing that has not started.
	if ( action !== 'start' && typeof nebula.timings[uniqueID] === 'undefined' ){
		return false;
	}

	//Can not modify a timer once it has ended.
	if ( typeof nebula.timings[uniqueID] !== 'undefined' && nebula.timings[uniqueID].total > 0 ){ //@todo "Nebula" 0: Use optional chaining
		return nebula.timings[uniqueID].total;
	}

	//Update the timing data!
	var currentTime = performance.now();

	if ( action === 'start' && typeof nebula.timings[uniqueID] === 'undefined' ){
		nebula.timings[uniqueID] = {};
		nebula.timings[uniqueID].started = currentTime;
		nebula.timings[uniqueID].cumulative = 0;
		nebula.timings[uniqueID].total = 0;
		nebula.timings[uniqueID].lap = [];
		nebula.timings[uniqueID].laps = 0;

		var thisLap = {
			name: false,
			started: currentTime,
			stopped: 0,
			duration: 0,
			progress: 0,
		};
		nebula.timings[uniqueID].lap.push(thisLap);

		if ( typeof name !== 'undefined' ){
			nebula.timings[uniqueID].lap[0].name = name;
		}

		//Add the time to User Timing API (if supported)
		if ( typeof performance.measure !== 'undefined' ){
			performance.mark(uniqueID + ' [Start]');
		}
	} else {
		var lapNumber = nebula.timings[uniqueID].lap.length;

		//Finalize the times for the previous lap
		nebula.timings[uniqueID].lap[lapNumber-1].stopped = currentTime;
		nebula.timings[uniqueID].lap[lapNumber-1].duration = currentTime-nebula.timings[uniqueID].lap[lapNumber-1].started;
		nebula.timings[uniqueID].lap[lapNumber-1].progress = currentTime-nebula.timings[uniqueID].started;
		nebula.timings[uniqueID].cumulative = currentTime-nebula.timings[uniqueID].started;

		//An "out" lap means the timing for this lap may not be associated directly with the action (Usually resetting for the next actual timed lap).
		if ( action === 'start' ){
			nebula.timings[uniqueID].lap[lapNumber-1].out = true; //If another 'start' was sent, then the previous lap was an out lap
		} else {
			nebula.timings[uniqueID].lap[lapNumber-1].out = false;
		}

		//Prepare the current lap
		if ( action !== 'end' ){
			nebula.timings[uniqueID].laps++;
			if ( lapNumber > 0 ){
				nebula.timings[uniqueID].lap[lapNumber] = {};
				nebula.timings[uniqueID].lap[lapNumber].started = nebula.timings[uniqueID].lap[lapNumber-1].stopped;
			}

			if ( typeof name !== 'undefined' ){
				nebula.timings[uniqueID].lap[lapNumber].name = name;
			}

			//Add the time to User Timing API (if supported)
			if ( typeof performance.measure !== 'undefined' ){
				var lapID = name || lapNumber;
				performance.mark(uniqueID + ' [Lap ' + lapID + ']');
			}
		}

		//Return individual lap times unless 'end' is passed- then return total duration. Note: 'end' can not be updated more than once per uniqueID! Subsequent calls will return the total duration from first call.
		if ( action === 'end' ){
			//Add the time to User Timing API (if supported)
			if ( typeof performance.measure !== 'undefined' ){
				performance.mark(uniqueID + ' [End]');

				if ( performance.getEntriesByName(uniqueID + ' [Start]', 'mark') ){ //Make sure the start mark exists
					performance.measure(uniqueID, uniqueID + ' [Start]', uniqueID + ' [End]');
				}
			}

			nebula.timings[uniqueID].stopped = currentTime;
			nebula.timings[uniqueID].total = currentTime-nebula.timings[uniqueID].started;
			//@todo "Nebula" 0: Add all hot laps together (any non-"out" laps)
			return nebula.timings[uniqueID].total;
		} else {
			if ( !nebula.timings[uniqueID].lap[lapNumber-1].out ){
				return nebula.timings[uniqueID].lap[lapNumber-1].duration;
			}
		}
	}
};

//Convert milliseconds into separate hours, minutes, and seconds string (Ex: "3h 14m 35.2s").
nebula.millisecondsToString = function(ms){
	var milliseconds = parseInt((ms%1000)/100);
	var seconds = parseInt((ms/1000)%60);
	var minutes = parseInt((ms/(1000*60))%60);
	var hours = parseInt((ms/(1000*60*60))%24);

	var timeString = '';
	if ( hours > 0 ){
		timeString += hours + 'h ';
	}
	if ( minutes > 0 ){
		timeString += minutes + 'm ';
	}
	if ( seconds > 0 || milliseconds > 0 ){
		timeString += seconds;

		if ( milliseconds > 0 ){
			timeString += '.' + milliseconds;
		}

		timeString += 's';
	}
	return timeString;
};

//Convert time to relative.
//For cross-browser support, timestamp must be passed as a string (not a Date object) in the format: Fri Mar 27 21:40:02 +0000 2016
nebula.timeAgo = function(timestamp, raw){ //http://af-design.com/blog/2009/02/10/twitter-like-timestamps/
	if ( typeof timestamp === 'object' ){
		console.warn('Pass date as string in the format: Fri Mar 27 21:40:02 +0000 2016');
	}

	var postDate = new Date(timestamp);
	var currentTime = new Date();

	//Browser sanitation
	if ( jQuery('.ie, .internet_explorer, .microsoft_edge').length ){
		postDate = Date.parse(timestamp.replace(/( \+)/, ' UTC$1'));
	}

	var diff = Math.floor((currentTime-postDate)/1000);

	if ( raw ){
		return diff;
	}

	if ( diff <= 1 ){ return "just now"; }
	if ( diff < 20 ){ return diff + " seconds ago"; }
	if ( diff < 60 ){ return "less than a minute ago"; }
	if ( diff <= 90 ){ return "one minute ago"; }
	if ( diff <= 3540 ){ return Math.round(diff/60) + " minutes ago"; }
	if ( diff <= 5400 ){ return "1 hour ago"; }
	if ( diff <= 86400 ){ return Math.round(diff/3600) + " hours ago"; }
	if ( diff <= 129600 ){ return "1 day ago"; }
	if ( diff < 604800 ){ return Math.round(diff/86400) + " days ago"; }
	if ( diff <= 777600 ){ return "1 week ago"; }

	return "on " + timestamp;
};

//Check nested objects (boolean). Note: This function can not check if the object itself exists.
//nebula.has(nebula, 'user.client.remote_addr'); //Ex: object nebula must exist first (check for it separately)
//Note: May not need after optional chaining becomes standard
nebula.has = function(obj, prop){
	var parts = prop.split('.');
	for ( var i = 0, l = parts.length; i < l; i++ ){
		var part = parts[i];
		if ( obj !== null && typeof obj === "object" && part in obj ){
			obj = obj[part];
		} else {
			return false;
		}
	}

	return true;
};

/*==========================
 Miscellaneous Functions
 ===========================*/

//Functionality for selecting and copying text using Nebula Pre tags.
nebula.pre = function(){
	//Format non-shortcode pre tags to be styled properly
	jQuery('pre.nebula-code').each(function(){
		if ( !jQuery(this).parent('.nebula-code-con').length ){
			var lang = jQuery(this).attr('class').replace('nebula-code', '').trim();
			jQuery(this).addClass(lang.toLowerCase()).wrap('<div class="nebula-code-con clearfix ' + lang.toLowerCase() + '"></div>');
			jQuery(this).closest('.nebula-code-con').prepend('<span class="nebula-code codetitle ' + lang.toLowerCase() + '">' + lang + '</span>');
		}
	});

	//Manage copying snippets to clipboard
	if ( 'clipboard' in navigator ){
		jQuery('.nebula-code-con').each(function(){
			jQuery(this).append('<a href="#" class="nebula-selectcopy-code">Copy to Clipboard</a>');
			jQuery(this).find('p:empty').remove(); //Sometimes WordPress adds extra/empty <p> tags. These mess with spacing, so we remove them.
		});

		nebula.dom.document.on('click', '.nebula-selectcopy-code', function(){
			var oThis = jQuery(this);
			if ( oThis.hasClass('error') ){ //If we already errored, stop trying
				return false;
			}

			var text = jQuery(this).closest('.nebula-code-con').find('pre').text();

			navigator.clipboard.writeText(text).then(function(){
				oThis.text('Copied!').removeClass('error').addClass('success');
				setTimeout(function(){
					oThis.text('Copy to clipboard').removeClass('success');
				}, 1500);
			}).catch(function(e){ //This can happen if the user denies clipboard permissions
				ga('send', 'exception', {'exDescription': '(JS) Clipboard API error: ' + e.data, 'exFatal': false});
				oThis.text('Unable to copy.').addClass('error');
			});

			return false;
		});
	}
};

//Sanitize text
nebula.sanitize = function(text){
	return document.createElement('div').appendChild(document.createTextNode(text)).parentNode.innerHTML;
};

//Nebula preferred default Chosen.js options
nebula.defaultChosenOptions = {
	disable_search_threshold: 5,
	search_contains: true,
	no_results_text: "No results found.",
	allow_single_deselect: true,
	width: "100%"
};

//Initialize Video Functionality and Tracking
nebula.initVideoTracking = function(){
	if ( typeof nebula.videos === 'undefined' ){
		nebula.videos = {};
	}

	nebula.HTML5VideoTracking();
	nebula.youtubeTracking();
	nebula.vimeoTracking();
};

//Track lazy-loaded videos
//Note: element can be anything! Don't assume it is a video.
nebula.lazyVideoTracking = function(element){
	//Re-kick the API for lazy-loaded Youtube and Vimeo videos, and enable tracking for lazy-loaded HTML5 videos.
	if ( element.is('iframe[src*="youtube"]') ){
		nebula.addYoutubePlayer(element.attr('id'), element);
	} else if ( element.is('iframe[src*="vimeo"]') ){
		nebula.createVimeoPlayers();
	} else if ( element.is('video') ){
		nebula.addHTML5VideoPlayer(element.attr('id'), element);
	}
};

//Native HTML5 Videos
nebula.HTML5VideoTracking = function(){
	jQuery('video').each(function(){
		var id = jQuery(this).attr('id'); //An ID is required so HTML5 videos can be properly identified by Nebula and child themes

		if ( typeof nebula.videos[id] === 'object' ){ //If this video is already being tracked ignore it
			return false;
		}

		nebula.addHTML5VideoPlayer(id, jQuery(this));
	});
};

nebula.addHTML5VideoPlayer = function(id, element){
	var videoTitle = element.attr('title') || id || false;
	if ( !videoTitle ){ //An ID or title is required to track HTML5 videos
		return false;
	}

	nebula.videos[id] = {};
	nebula.videos[id].platform = 'html5'; //The platform the video is hosted using.
	nebula.videos[id].player = id; //The player ID of this video. Can access the API here.
	nebula.videos[id].title = videoTitle;
	nebula.videos[id].id = id;
	nebula.videos[id].element = element;
	nebula.videos[id].autoplay = ( element.attr('autoplay') )? true : false;
	nebula.videos[id].percent = 0; //The decimal percent of the current position. Multiply by 100 for actual percent.
	nebula.videos[id].seeker = false; //Whether the viewer has seeked through the video at least once.
	nebula.videos[id].seen = []; //An array of percentages seen by the viewer. This is to roughly estimate how much was watched.
	nebula.videos[id].watched = 0; //Amount of time watching the video (regardless of seeking). Accurate to 1% of video duration. Units: Seconds
	nebula.videos[id].watchedPercent = 0; //The decimal percent of the video watched. Multiply by 100 for actual percent.
	nebula.videos[id].pausedYet = false; //If this video has been paused yet by the user.
	nebula.videos[id].current = 0; //The current position of the video. Units: Seconds

	element.on('loadedmetadata', function(){
		nebula.videos[id].current = this.currentTime;
		nebula.videos[id].duration = this.duration; //The total duration of the video. Units: Seconds
	});

	element.on('play', function(){
		var thisVideo = nebula.videos[id];

		if ( 'mediaSession' in navigator && element.attr('title') ){ //Android Chrome 55+ only
			navigator.mediaSession.metadata = new MediaMetadata({
				title: element.attr('title'),
				artist: element.attr('artist') || '',
				album: element.attr('album') || '',
/*
				artwork: [{
					src: 'https://dummyimage.com/512x512',
					sizes: '512x512',
					type: 'image/png'
				}]
*/
			});
		}

		element.addClass('playing');

		//Only report to GA for non-autoplay videos
		if ( !element.is('[autoplay]') ){
			var thisEvent = {
				category: 'Videos',
				action: ( nebula.isInView(element) )? 'Play' : 'Play (Not In View)',
				title: thisVideo.title,
				autoplay: thisVideo.autoplay
			};

			ga('set', nebula.analytics.metrics.videoStarts, 1);
			ga('set', nebula.analytics.dimensions.videoWatcher, 'Started');
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.title, {'nonInteraction': thisEvent.autoplay});
			if ( !thisVideo.autoplay ){
				nebula.crm('event', 'Video Play Began: ' + thisVideo.title);
			}
		}

		nebula.dom.document.trigger('nebula_playing_video', thisVideo);
	});

	element.on('timeupdate', function(){
		var thisVideo = nebula.videos[id];

		thisVideo.current = this.currentTime; //@todo "Nebula" 0: Still getting NaN on HTML5 autoplay videos sometimes. I think the video begins playing before the metadata is ready...
		thisVideo.percent = thisVideo.current*100/thisVideo.duration; //Determine watched percent by adding current percents to an array, then count the array!
		var nowSeen = Math.ceil(thisVideo.percent);
		if ( thisVideo.seen.indexOf(nowSeen) < 0 ){
			thisVideo.seen.push(nowSeen);
		}

		thisVideo.watchedPercent = thisVideo.seen.length;
		thisVideo.watched = (thisVideo.seen.length/100)*thisVideo.duration; //Roughly calculate time watched based on percent seen

		if ( thisVideo.watchedPercent > 25 && !thisVideo.engaged ){
			if ( nebula.isInView(element) ){
				var thisEvent = {
					category: 'Videos',
					action: ( thisVideo.autoplay )? 'Engaged' : 'Engaged (Autoplay)',
					title: thisVideo.title,
					autoplay: thisVideo.autoplay
				};

				ga('set', nebula.analytics.dimensions.videoWatcher, thisEvent.action);
				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.title, {'nonInteraction': true});
				nebula.crm('event', 'Video Engagement: ' + thisEvent.title);
				thisVideo.engaged = true;
				nebula.dom.document.trigger('nebula_engaged_video', thisVideo);
			}
		}
	});

	element.on('pause', function(){
		var thisVideo = nebula.videos[id];
		element.removeClass('playing');

		var thisEvent = {
			category: 'Videos',
			action: 'Paused',
			playTime: Math.round(thisVideo.watched),
			percent: Math.round(thisVideo.percent*100),
			progress:  Math.round(thisVideo.current*1000),
			title: thisVideo.title,
			autoplay: thisVideo.autoplay
		};

		ga('set', nebula.analytics.dimensions.videoWatcher, thisEvent.action);
		ga('set', nebula.analytics.metrics.videoPlaytime, thisEvent.playTime);
		ga('set', nebula.analytics.dimensions.videoPercentage, thisEvent.percent);

		if ( !thisVideo.pausedYet ){
			ga('send', 'event', thisEvent.category, 'First Pause', thisEvent.title);
			thisVideo.pausedYet = true;
		}

		nebula.dom.document.trigger('nebula_event', thisEvent);
		ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.title);
		ga('send', 'timing', thisEvent.category, thisEvent.action, thisEvent.progress, thisEvent.title);
		nebula.crm('event', 'Video Paused: ' + thisEvent.title);
		nebula.dom.document.trigger('nebula_paused_video', thisVideo);
	});

	element.on('seeked', function(){
		var thisVideo = nebula.videos[id];

		if ( thisVideo.current == 0 && element.is('[loop]') ){ //If the video is set to loop and is starting again
			//If it is an autoplay video without controls, don't log loops
			if ( element.is('[autoplay]') && !element.is('[controls]') ){
				return false;
			}

			var thisEvent = {
				category: 'Videos',
				action: ( nebula.isInView(element) )? 'Ended (Looped)' : 'Ended (Looped) (Not In View)',
				title: thisVideo.title,
				autoplay: thisVideo.autoplay
			};

			if ( thisVideo.autoplay ){
				thisEvent.action += ' (Autoplay)';
			}

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.title, {'nonInteraction': true});
		} else { //Otherwise, the user seeked
			nebula.debounce(function(){
				var thisEvent = {
					category: 'Videos',
					action: 'Seek',
					position: thisVideo.current.toFixed(0),
					title: thisVideo.title,
					autoplay: thisVideo.autoplay
				};

				ga('set', nebula.analytics.dimensions.videoWatcher, thisEvent.action);
				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.title + ' [to: ' + thisEvent.position + ']');
				nebula.crm('event', 'Video Seek: ' + thisEvent.title);
				thisVideo.seeker = true;
				nebula.dom.document.trigger('nebula_seeked_video', thisVideo);
			}, 250, 'video seeking');
		}
	});

	element.on('volumechange', function(){
		var thisVideo = nebula.videos[id];
		//console.debug(this);
	});

	element.on('ended', function(){
		var thisVideo = nebula.videos[id];
		element.removeClass('playing');

		var thisEvent = {
			category: 'Videos',
			action: ( nebula.isInView(element) )? 'Ended' : 'Ended (Not In View)',
			title: thisVideo.title,
			playTime: Math.round(thisVideo.watched),
			progress: Math.round(thisVideo.current*1000),
			autoplay: thisVideo.autoplay
		};

		if ( thisVideo.autoplay ){
			thisEvent.action += ' (Autoplay)';
		}

		ga('set', nebula.analytics.metrics.videoCompletions, 1);
		ga('set', nebula.analytics.metrics.videoPlaytime, thisEvent.playTime);
		ga('set', nebula.analytics.dimensions.videoWatcher, thisEvent.action);

		nebula.dom.document.trigger('nebula_event', thisEvent);
		ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.title, {'nonInteraction': true});
		ga('send', 'timing', thisEvent.category, thisEvent.action, thisEvent.progress, thisEvent.title);
		nebula.crm('event', 'Video Ended: ' + thisEvent.title);
		nebula.dom.document.trigger('nebula_ended_video', thisVideo);
	});
};


//Prepare Youtube Iframe API
nebula.youtubeTracking = function(){
	nebula.once(function(){
		if ( jQuery('iframe[src*="youtube"], .lazy-youtube').length ){
			//Load the Youtube iframe API script
			var tag = document.createElement('script');
			tag.src = "https://www.youtube.com/iframe_api";
			var firstScriptTag = document.getElementsByTagName('script')[0];
			firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
		}
	}, 'nebula youtube api');
};

function onYouTubeIframeAPIReady(e){
	window.performance.mark('(Nebula) Loading Youtube Videos [Start]');
	jQuery('iframe[src*="youtube"]').each(function(i){
		if ( !jQuery(this).hasClass('ignore') ){ //Use this class to ignore certain videos from tracking
			//If this iframe is using a data-src, make sure the src matches
			if ( jQuery(this).attr('src').indexOf('youtube') < 0 ){ //If the src does not contain "youtube"
				jQuery(this).attr('src', jQuery(this).attr('data-src')); //Update the src to match the data-src attribute. Note: I cannot think of a better way to do this that actually works with the Youtube Iframe API
			}

			var id = jQuery(this).attr('id');
			if ( !id ){
				id = jQuery(this).attr('src').split('?')[0].split('/').pop();
				jQuery(this).attr('id', id);
			}

			if ( jQuery(this).attr('src').indexOf('enablejsapi=1') > 0 ){ //If the iframe src already has the API enabled
				nebula.addYoutubePlayer(id, jQuery(this));
				nebula.dom.document.trigger('nebula_youtube_players_created', nebula.videos[id]);
			} else {
				console.warn('The enablejsapi parameter was not found for this Youtube iframe. It has been reloaded to enable it. For better optimization, and more accurate analytics, add it to the iframe.');

				//JS API not enabled for this video. Reload the iframe with the correct parameter.
				var delimiter = ( jQuery(this).attr('src').indexOf('?') > 0 )? '&' : '?';
				jQuery(this).attr('src', jQuery(this).attr('src') + delimiter + 'enablejsapi=1').on('load', function(){
					nebula.addYoutubePlayer(id, jQuery(this));
					nebula.dom.document.trigger('nebula_youtube_players_created', nebula.videos[id]);
				});
			}
		}
	});
	window.performance.mark('(Nebula) Loading Youtube Videos [End]');
	window.performance.measure('(Nebula) Loading Youtube Videos', '(Nebula) Loading Youtube Videos [Start]', '(Nebula) Loading Youtube Videos [End]');

	var pauseFlag = false;
}

nebula.addYoutubePlayer = function(id, element){
	if ( !id ){
		return false; //A Youtube ID is required to add player
	}

	if ( typeof YT !== 'undefined' ){
		nebula.videos[id] = {
			player: new YT.Player(id, { //YT.Player parameter must match the iframe ID!
				events: { //If these events are only showing up as "true", try removing the &origin= parameter from the Youtube iframe src.
					'onReady': nebulaYoutubeReady,
					'onStateChange': nebulaYoutubeStateChange,
					'onError': nebulaYoutubeError
				}
			}),
			platform: 'youtube', //The platform the video is hosted using.
			element: element, //The player iframe.
			autoplay: element.attr('src').indexOf('autoplay=1') > 0, //Look for the autoplay parameter in the ifrom src.
			id: id,
			engaged: false, //Whether the viewer has watched enough of the video to be considered engaged.
			watched: 0, //Amount of time watching the video (regardless of seeking). Accurate to half a second. Units: Seconds
			watchedPercent: 0, //The decimal percentage of the video watched. Multiply by 100 for actual percent.
			pausedYet: 0, //If this video has been paused yet by the user.
		};
	}
};

function nebulaYoutubeReady(e){
	if ( typeof videoProgress === 'undefined' ){
		var videoProgress = {};
	}

	var id = nebula.getYoutubeID(e.target);
	if ( id && !nebula.videos[id] ){ //If the video object doesn't use the Youtube video ID, make a new one by duplicating from the Iframe ID
		nebula.videos[id] = nebula.videos[jQuery(e.target.getIframe()).attr('id')];
	}

	nebula.videos[id].title = nebula.getYoutubeTitle(e.target); //Use Nullish coalescing here (after ie11?)
	nebula.videos[id].duration = e.target.getDuration(); //The total duration of the video. Unit: Seconds
	nebula.videos[id].current = e.target.getCurrentTime(); //The current position of the video. Units: Seconds
	nebula.videos[id].percent = e.target.getCurrentTime()/e.target.getDuration(); //The percent of the current position. Multiply by 100 for actual percent.
}

function nebulaYoutubeStateChange(e){
	var thisVideo = nebula.videos[nebula.getYoutubeID(e.target)];
	thisVideo.title = nebula.getYoutubeTitle(e.target); //Use Nullish coalescing here (after ie11?)

	//Playing
	if ( e.data === YT.PlayerState.PLAYING ){
		var thisEvent = {
			category: 'Videos',
			action: ( nebula.isInView(jQuery(thisVideo.element)) )? 'Play' : 'Play (Not In View)',
			title: thisVideo.title,
			autoplay: thisVideo.autoplay
		};

		ga('set', nebula.analytics.metrics.videoStarts, 1);
		ga('set', nebula.analytics.dimensions.videoWatcher, 'Started');

		if ( thisVideo.autoplay ){
			thisEvent.action += ' (Autoplay)';
		} else {
			jQuery(thisVideo.element).addClass('playing');
		}

		nebula.dom.document.trigger('nebula_event', thisEvent);
		ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.title);
		nebula.crm('event', 'Video Play Began: ' + thisEvent.title);
		nebula.dom.document.trigger('nebula_playing_video', thisVideo);
		var pauseFlag = true;
		var updateInterval = 500;

		try {
			thisVideo.current = e.target.getCurrentTime();
			thisVideo.percent = thisVideo.current/thisVideo.duration;

			var youtubePlayProgress = setInterval(function(){
				thisVideo.current = e.target.getCurrentTime();
				thisVideo.percent = thisVideo.current/thisVideo.duration;
				thisVideo.watched = thisVideo.watched+(updateInterval/1000);
				thisVideo.watchedPercent = (thisVideo.watched)/thisVideo.duration;

				if ( thisVideo.watchedPercent > 0.25 && !thisVideo.engaged ){
					if ( nebula.isInView(jQuery(thisVideo.element)) ){
						var thisEvent = {
							category: 'Videos',
							action: ( thisVideo.autoplay )? 'Engaged' : 'Engaged (Autoplay)',
							title: thisVideo.title,
							autoplay: thisVideo.autoplay
						};

						ga('set', nebula.analytics.dimensions.videoWatcher, thisEvent.action);
						nebula.dom.document.trigger('nebula_event', thisEvent); //@todo "Nebula" 0: This needs the new nebula_event trigger with thisEvent object
						ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.title, {'nonInteraction': true});
						nebula.crm('event', 'Video Engaged: ' + thisEvent.title);
						thisVideo.engaged = true;
						nebula.dom.document.trigger('nebula_engaged_video', thisVideo);
					}
				}
			}, updateInterval);
		} catch(e){
			//Video progress tracking was unsuccessful. Failing gracefully.
		}
	}

	//Ended
	if ( e.data === YT.PlayerState.ENDED ){
		jQuery(thisVideo.element).removeClass('playing');
		clearInterval(youtubePlayProgress);

		var thisEvent = {
			category: 'Videos',
			action: ( nebula.isInView(jQuery(thisVideo.element)) )? 'Ended' : 'Ended (Not In View)',
			title: thisVideo.title,
			playTime: Math.round(thisVideo.watched/1000),
			progress: thisVideo.current*1000,
			autoplay: thisVideo.autoplay
		};

		if ( thisVideo.autoplay ){
			thisEvent.action += ' (Autoplay)';
		}

		ga('set', nebula.analytics.metrics.videoCompletions, 1);
		ga('set', nebula.analytics.metrics.videoPlaytime, thisEvent.playTime);
		ga('set', nebula.analytics.dimensions.videoWatcher, thisEvent.action);

		nebula.dom.document.trigger('nebula_event', thisEvent);
		ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.title, {'nonInteraction': true});
		ga('send', 'timing', thisEvent.category, thisEvent.action, thisEvent.progress, thisEvent.title);
		nebula.crm('event', 'Video Ended: ' + thisEvent.title);
		nebula.dom.document.trigger('nebula_ended_video', thisVideo);

	//Paused
	} else {
		setTimeout(function(){ //Wait 1 second because seeking will always pause and automatically resume, so check if it is still playing a second from now
			try {
				if ( e.target.getPlayerState() == 2 && pauseFlag ){ //This must use getPlayerState() since e.data is not actually "current" inside of this setTimeout(). Paused = 2
					jQuery(thisVideo.element).removeClass('playing');
					clearInterval(youtubePlayProgress);

					var thisEvent = {
						category: 'Videos',
						action: 'Paused',
						playTime: Math.round(thisVideo.watched),
						percent: Math.round(thisVideo.percent*100),
						progress:  thisVideo.current*1000,
						title: thisVideo.title,
						autoplay: thisVideo.autoplay
					};

					ga('set', nebula.analytics.metrics.videoPlaytime, Math.round(thisVideo.watched));
					ga('set', nebula.analytics.dimensions.videoPercentage, Math.round(thisVideo.percent*100));
					ga('set', nebula.analytics.dimensions.videoWatcher, thisEvent.action);

					if ( !thisVideo.pausedYet ){
						ga('send', 'event', thisEvent.category, 'First Pause', thisEvent.title);
						thisVideo.pausedYet = true;
					}

					nebula.dom.document.trigger('nebula_event', thisEvent);
					ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.title);
					ga('send', 'timing', thisEvent.category, thisEvent.action, thisEvent.progress, thisEvent.title);
					nebula.crm('event', 'Video Paused: ' + thisEvent.title);
					nebula.dom.document.trigger('nebula_paused_video', thisVideo);
					pauseFlag = false;
				}
			} catch(e){
				//getPlayerState() is probably undefined... Failing gracefully
			}
		}, 1000);
	}
}

function nebulaYoutubeError(e){
	var thisVideo = nebula.videos[nebula.getYoutubeID(e.target)];
	thisVideo.title = nebula.getYoutubeTitle(e.target); //Use Nullish coalescing here (after ie11?)

	ga('send', 'exception', {'exDescription': '(JS) Youtube API error for ' + thisVideo.title + ': ' + e.data, 'exFatal': false});
	nebula.crm('event', 'Youtube API Error');
}

//Get the ID of the Youtube video (or use best fallback possible)
nebula.getYoutubeID = function(target){
	var id;

	//If getVideoData is available in the API
	if ( target.getVideoData ){
		id = target.getVideoData().id || target.getVideoData().video_id;
	}

	//Make sure the ID was available within the getVideoData() otherwise use alternate methods
	if ( !id ){
		if ( target.getDebugText ){
			id = JSON.parse(target.getDebugText()).debug_videoId;
		} else {
			if ( typeof target.getVideoUrl === 'function' ){
				id = nebula.get('v', target.getVideoUrl()); //Parse the video URL for the ID or use the iframe ID
			} else {
				id = jQuery(target.getIframe()).attr('src').split('?')[0].split('/').pop() || jQuery(target.getIframe()).attr('id'); //Parse the video URL for the ID or use the iframe ID
			}

		}
	}

	return id;
};

//Get the title of a Youtube video (or use best fallback possible)
nebula.getYoutubeTitle = function(target){
	var videoTitle;

	//If getVideoData is available in the API
	if ( target.getVideoData ){
		videoTitle = target.getVideoData().title;
	}

	return videoTitle || jQuery(target.getIframe()).attr('title') || nebula.getYoutubeID(target) || false;
};

//Prepare Vimeo API
nebula.vimeoTracking = function(){
	//Load the Vimeo API script (player.js) remotely (with local backup)
	if ( jQuery('iframe[src*="vimeo"], .lazy-vimeo').length ){
		nebula.loadJS(nebula.site.resources.scripts.nebula_vimeo, function(){
			nebula.createVimeoPlayers();
		});
	}
};

//To trigger events on these videos, use the syntax: nebula.videos['208432684'].player.play();
nebula.createVimeoPlayers = function(){
	jQuery('iframe[src*="vimeo"]').each(function(i){ //This is not finding lazy loaded videos
		if ( !jQuery(this).hasClass('ignore') ){ //Use this class to ignore certain videos from tracking
			var id = jQuery(this).attr('data-video-id') || jQuery(this).attr('data-vimeo-id') || jQuery(this).attr('id') || false;
			if ( !id ){
				if ( jQuery(this).attr('src').indexOf('player_id') > -1 ){
					id = jQuery(this).attr('src').split('player_id=').pop().split('&')[0]; //Use the player_id parameter. Note: This is no longer used by the Vimeo API!
				} else {
					id = jQuery(this).attr('src').split('/').pop().split('?')[0]; //Grab the ID off the end of the URL (ignoring query parameters)
				}

				if ( id && !parseInt(id) ){ //If the ID is a not number try to find a number in the iframe src
					id = /\d{6,}/g.exec(jQuery(this).attr('src'))[0];
				}

				jQuery(this).attr('id', id);
			}

			if ( typeof nebula.videos[id] === 'object' ){ //If this video is already being tracked ignore it
				return; //Continue the loop
			}

			//Fill in the data object here
			nebula.videos[id] = {
				player: new Vimeo.Player(jQuery(this)),
				element: jQuery(this),
				platform: 'vimeo', //The platform the video is hosted using.
				autoplay: jQuery(this).attr('src').indexOf('autoplay=1') > 0, //Look for the autoplay parameter in the iframe src.
				id: id,
				current: 0, //The current position of the video. Units: Seconds
				percent: 0, //The percent of the current position. Multiply by 100 for actual percent.
				engaged: false, //Whether the viewer has watched enough of the video to be considered engaged.
				seeker: false, //Whether the viewer has seeked through the video at least once.
				seen: [], //An array of percentages seen by the viewer. This is to roughly estimate how much was watched.
				watched: 0, //Amount of time watching the video (regardless of seeking). Accurate to 1% of video duration. Units: Seconds
				watchedPercent: 0, //The decimal percentage of the video watched. Multiply by 100 for actual percent.
				pausedYet: false, //If this video has been paused yet by the user.
			};

			//Title
			nebula.videos[id].player.getVideoTitle().then(function(title){
				nebula.videos[id].title = title; //The title of the video
			});

			//Duration
			nebula.videos[id].player.getDuration().then(function(duration){
				nebula.videos[id].duration = duration; //The total duration of the video. Units: Seconds
			});

			//Play
			nebula.videos[id].player.on('play', function(e){
				var thisEvent = {
					category: 'Videos',
					action: ( nebula.isInView(jQuery(nebula.videos[id].element)) )? 'Play' : 'Play (Not In View)',
					title: nebula.videos[id].title,
					autoplay: nebula.videos[id].autoplay
				};

				ga('set', nebula.analytics.metrics.videoStarts, 1);
				ga('set', nebula.analytics.dimensions.videoWatcher, 'Started');

				if ( nebula.videos[id].autoplay ){
					thisEvent.action += ' (Autoplay)';
				} else {
					nebula.videos[id].element.addClass('playing');
				}

				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.title);
				nebula.crm('event', 'Video Play Began: ' + thisEvent.title);
				nebula.dom.document.trigger('nebula_playing_video', nebula.videos[id].title);
			});

			//Time Update
			nebula.videos[id].player.on('timeupdate', function(e){
				nebula.videos[id].duration = e.duration;
				nebula.videos[id].current = e.seconds;
				nebula.videos[id].percent = e.percent;

				//Determine watched percent by adding current percents to an array, then count the array!
				nowSeen = Math.ceil(nebula.videos[id].percent*100);
				if ( nebula.videos[id].seen.indexOf(nowSeen) < 0 ){
					nebula.videos[id].seen.push(nowSeen);
				}
				nebula.videos[id].watchedPercent = nebula.videos[id].seen.length;
				nebula.videos[id].watched = (nebula.videos[id].seen.length/100)*nebula.videos[id].duration; //Roughly calculate time watched based on percent seen

				if ( nebula.videos[id].watchedPercent > 25 && !nebula.videos[id].engaged ){
					if ( nebula.isInView(jQuery(nebula.videos[id].element)) ){
						var thisEvent = {
							category: 'Videos',
							action: ( nebula.videos[id].autoplay )? 'Engaged' : 'Engaged (Autoplay)',
							title: nebula.videos[id].title,
							autoplay: nebula.videos[id].autoplay
						};

						ga('set', nebula.analytics.dimensions.videoWatcher, thisEvent.action);
						nebula.dom.document.trigger('nebula_event', thisEvent);
						ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.title, {'nonInteraction': true});
						nebula.crm('event', 'Video Engaged: ' + thisEvent.title);
						nebula.videos[id].engaged = true;
						nebula.dom.document.trigger('nebula_engaged_video', nebula.videos[id].title);
					}
				}
			});

			//Pause
			nebula.videos[id].player.on('pause', function(e){
				jQuery(this).removeClass('playing');

				var thisEvent = {
					category: 'Videos',
					action: 'Paused',
					playTime: Math.round(nebula.videos[id].watched),
					percent: Math.round(e.percent*100),
					title: nebula.videos[id].title,
					autoplay: nebula.videos[id].autoplay
				};

				ga('set', nebula.analytics.dimensions.videoWatcher, thisEvent.action);
				ga('set', nebula.analytics.metrics.videoPlaytime, thisEvent.playTime);
				ga('set', nebula.analytics.dimensions.videoPercentage, thisEvent.percent);

				if ( !nebula.videos[id].pausedYet && !nebula.videos[id].seeker ){ //Only capture first pause if they didn't seek
					ga('send', 'event', thisEvent.category, 'First Pause', thisEvent.title);
					nebula.videos[id].pausedYet = true;
				}

				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.title);
				ga('send', 'timing', thisEvent.category, thisEvent.action, Math.round(e.seconds*1000), thisEvent.title);
				nebula.crm('event', 'Video Paused: ' + thisEvent.title);
				nebula.dom.document.trigger('nebula_paused_video', nebula.videos[id]);
			});

			//Seeked
			nebula.videos[id].player.on('seeked', function(e){
				var thisEvent = {
					category: 'Videos',
					action: 'Seek',
					position: e.seconds,
					title: nebula.videos[id].title,
					autoplay: nebula.videos[id].autoplay
				};

				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('set', nebula.analytics.dimensions.videoWatcher, thisEvent.action);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.title + ' [to: ' + thisEvent.position + ']');
				nebula.crm('event', 'Video Seeked: ' + thisEvent.title);
				nebula.videos[id].seeker = true;
				nebula.dom.document.trigger('nebula_seeked_video', nebula.videos[id]);
			});

			//Ended
			nebula.videos[id].player.on('ended', function(e){
				jQuery(this).removeClass('playing');

				var thisEvent = {
					category: 'Videos',
					action: ( nebula.isInView(jQuery(nebula.videos[id].element)) )? 'Ended' : 'Ended (Not In View)',
					title: nebula.videos[id].title,
					playTime: Math.round(nebula.videos[id].watched),
					progress: Math.round(nebula.videos[id].watched*1000),
					autoplay: nebula.videos[id].autoplay
				};

				ga('set', nebula.analytics.metrics.videoCompletions, 1);
				ga('set', nebula.analytics.metrics.videoPlaytime, thisEvent.playTime);
				ga('set', nebula.analytics.dimensions.videoWatcher, thisEvent.action);

				if ( nebula.videos[id].autoplay ){
					thisEvent.action += ' (Autoplay)';
				}

				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.title, {'nonInteraction': true});
				ga('send', 'timing', thisEvent.category, thisEvent.action, thisEvent.progress, thisEvent.title); //Roughly amount of time watched (Can not be over 100% for Vimeo)
				nebula.crm('event', 'Video Ended: ' + thisEvent.title);
				nebula.dom.document.trigger('nebula_ended_video', nebula.videos[id]);
			});

			nebula.dom.document.trigger('nebula_vimeo_player_created', nebula.videos[id]);
		}
	});

	if ( typeof videoProgress === 'undefined' ){
		var videoProgress = {};
	}
};

//Pause all videos
//Use class "ignore-visibility" on iframes to allow specific videos to continue playing regardless of page visibility
//Pass force as true to pause no matter what.
nebula.pauseAllVideos = function(force){
	if ( typeof nebula.videos === 'undefined' ){
		return false; //If videos don't exist, then no need to pause
	}

	if ( force === 'null' ){
		force = false;
	}

	jQuery.each(nebula.videos, function(){
		if ( this.platform === 'html5' ){
			if ( (force || !jQuery(this.element).hasClass('ignore-visibility')) ){
				jQuery(this.element)[0].pause(); //Pause HTML5 Videos
			}
		}

		if ( this.platform === 'youtube' ){
			if ( (force || !jQuery(this.element).hasClass('ignore-visibility')) ){
				this.player.pauseVideo(); //Pause Youtube Videos
			}
		}

		if ( this.platform === 'vimeo' ){
			if ( (force || !jQuery(this.element).hasClass('ignore-visibility')) ){
				this.player.pause(); //Pause Vimeo Videos
			}
		}
	});
};

//Create desktop notifications
nebula.desktopNotification = function(title, message, clickCallback, showCallback, closeCallback, errorCallback){
	if ( nebula.checkNotificationPermission() ){
		//Set defaults
		var defaults = {
			dir: "auto", //Direction ["auto", "ltr", "rtl"] (optional)
			lang: "en-US", //Language (optional)
			body: "", //Body message (optional)
			tag: Math.floor(Math.random()*10000)+1, //Unique tag for notification. Prevents repeat notifications of the same tag. (optional)
			icon: nebula.site.directory.template.uri + "/assets/img/meta/android-chrome-192x192.png" //Thumbnail Icon (optional)
		};

		if ( typeof message === "undefined" ){
			var message = defaults;
		} else if ( typeof message === "string" ){
			var body = message;
			message = defaults;
			message.body = body;
		} else {
			if ( typeof message.dir === "undefined" ){
				message.dir = defaults.dir;
			}
			if ( typeof message.lang === "undefined" ){
				message.lang = defaults.lang;
			}
			if ( typeof message.body === "undefined" ){
				message.body = defaults.lang;
			}
			if ( typeof message.tag === "undefined" ){
				message.tag = defaults.tag;
			}
			if ( typeof message.icon === "undefined" ){
				message.icon = defaults.icon;
			}
		}

		var instance = new Notification(title, message); //Trigger the notification

		if ( clickCallback ){
			instance.onclick = function(){
				clickCallback();
			};
		}
		if ( showCallback ){
			instance.onshow = function(e){
				showCallback();
			};
		} else {
			instance.onshow = function(e){
				setTimeout(function(){
					instance.close();
				}, 20000);
			};
		}
		if ( closeCallback ){
			instance.onclose = function(){
				closeCallback();
			};
		}
		if ( errorCallback ){
			instance.onerror = function(){
				ga('send', 'exception', {'exDescription': '(JS) Desktop Notification error', 'exFatal': false});
				errorCallback();
			};
		}
	}

	return false;
};

nebula.checkNotificationPermission = function(){
	Notification = window.Notification || window.mozNotification || window.webkitNotification;
	if ( !(Notification) ){
		return false;
	} else if ( Notification.permission === "granted" ){
		return true;
	} else if ( Notification.permission !== 'denied' ){
		Notification.requestPermission(function(permission){
			if( !('permission' in Notification) ){ //Firefox and Chrome only
				Notification.permission = permission;
			}
			if ( permission === 'granted' ){
				return true;
			}
		});
	}
	return false;
};

nebula.vibrate = function(pattern){
	if ( typeof pattern !== 'object' ){
		pattern = [100, 200, 100, 100, 75, 25, 100, 200, 100, 500, 100, 200, 100, 500];
	}
	if ( navigator.vibrate || navigator.webkitVibrate || navigator.mozVibrate || navigator.msVibrate ){
		navigator.vibrate(pattern);
	}
	return false;
};

//Lazy load and initialize Mmenu separately because it has additional conditions
nebula.initMmenu = function(){
	if ( jQuery('#mobilenav').length ){
		var isMobileBarVisible = ( jQuery('#mobilebarcon').css('border-left-color') === 'rgba(255, 255, 255, 0)' )? true : false; //This is used to determine if the element is actually visible (because even though its visibility is hidden, JS cannot detect that.
		if ( isMobileBarVisible ){
			nebula.once(function(){
				nebula.loadJS(nebula.site.resources.scripts.nebula_mmenu, function(){ //Detected as a long task per Chrome DevTools
					nebula.mmenus();
				});
				nebula.loadCSS(nebula.site.resources.styles.nebula_mmenu);
			}, 'Mmenu init');
		}
	}
}

nebula.mmenus = function(){
	//@todo "Nebula" 0: Between Mmenu and jQuery, 2 console violations are being triggered: "Added non-passive event listener to a scroll-blocking 'touchmove' event."
		//This happens whether this function is triggered on DOM ready or Window load

	if ( 'mmenu' in jQuery ){ //Ensure Mmenu library has loaded
		var mobileNav = jQuery('#mobilenav'); //This is essential, so check it here in case this function is called manually

		if ( mobileNav.length ){
			//Navigation Panels
			var navPanels = {};
			var currentPageClass = 'current-menu-item'; //This is a temporary fix until we upgrade to Mmenu 8!

			if ( jQuery('#utility-panel').length && (!jQuery('#utility-nav').hasClass('no-mobile') && !jQuery('#utility-panel').parents('.no-mobile').length) ){ //If the utility menu exists and is not manually disabled from the mobile menu via a class
				navPanels = {
					position: "top",
					type: "tabs",
					content: [
						"<a href='#main-panel'>Main Menu</a>",
						"<a href='#utility-panel'>Other Links</a>"
					]
				};

				currentPageClass = 'this-intentionally-left-blank'; //This is a temporary fix until we upgrade to Mmenu 8! This prevents utility nav pages from using the current page class. (This must be a non-empty string in order to not error)
			}

			//Add social links to footer of Mmenu
			var footerIconLinks = {};
			if ( nebula.has(nebula, 'site.options') ){ //@todo "Nebula" 0: Replace with optional chaining (may not need this conditional)
				footerIconLinks = {
					position: "bottom",
					content: []
				};
				if ( nebula.site.options.facebook_url ){ //@todo "Nebula" 0: Use optional chaining
					footerIconLinks.content.push('<a href="' + nebula.site.options.facebook_url + '" target="_blank" rel="noopener"><i class="fab fa-facebook"></i></a>');
				}

				if ( nebula.site.options.twitter_url ){ //@todo "Nebula" 0: Use optional chaining
					footerIconLinks.content.push('<a href="' + nebula.site.options.twitter_url + '" target="_blank" rel="noopener"><i class="fab fa-twitter"></i></a>');
				}

				if ( nebula.site.options.instagram_url ){ //@todo "Nebula" 0: Use optional chaining
					footerIconLinks.content.push('<a href="' + nebula.site.options.instagram + '" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>');
				}

				if ( nebula.site.options.linkedin_url ){ //@todo "Nebula" 0: Use optional chaining
					footerIconLinks.content.push('<a href="' + nebula.site.options.linkedin_url + '" target="_blank" rel="noopener"><i class="fab fa-linkedin"></i></a>');
				}

				if ( nebula.site.options.youtube_url ){ //@todo "Nebula" 0: Use optional chaining
					footerIconLinks.content.push('<a href="' + nebula.site.options.youtube_url + '" target="_blank" rel="noopener"><i class="fab fa-youtube"></i></a>');
				}

				if ( nebula.site.options.pinterest_url ){ //@todo "Nebula" 0: Use optional chaining
					footerIconLinks.content.push('<a href="' + nebula.site.options.pinterest_url + '" target="_blank" rel="noopener"><i class="fab fa-pinterest"></i></a>');
				}

				if ( footerIconLinks.content.length > 0 ){
					footerIconLinks.content.splice(0, 0, '<a href="' + nebula.site.home_url + '"><i class="fas fa-home"></i></a>'); //Insert into beginning of array
				}
			}

			//Initialize Mmenu options and configuration
			mobileNav.mmenu({
				//Options
				navbars: [{
					position: "top",
					content: ["searchfield"],
				},
				navPanels,
				footerIconLinks
				],
				searchfield: {
					add: true,
					search: true,
					placeholder: 'Search',
					noResults: "No navigation items found.",
					showSubPanels: false,
					showTextItems: false,
					resultsPanel: true,
				},
				counters: true, //Display count of sub-menus
				iconPanels: true, //Layer panels on top of each other
				backButton: {
					close: true //Close the Mmenu on back button click
				},
				extensions: [
					"position-back", //Push the page content
					"position-left", //Left side of page
					"theme-light", //Light background
					//"fx-listitems-slide", //Animated list items //@todo "Nebula" 0: Test if this is is laggy on mobile devices
					"shadow-page", //Add shadow to the page
					"shadow-panels", //Add shadow to menu panels
					"listview-huge", //Larger list items
					"multiline" //Wrap long titles
				],
			}, {
				//Configuration
				offCanvas: {
					page: {
						selector: "#body-wrapper"
					}
				},
				classNames: {
					selected: currentPageClass //This breaks when viewing a utility nav subpage– the panel gets stuck open. So we only use it when no utility nav is not being used.
				},
				searchfield: {
					clear: true,
					form: {
						method: "get",
						action: nebula.site.home_url,
					},
					input: {
						name: "s",
					}
				}
			}); //Initialize Mmenu

			if ( mobileNav.length ){
				var mobileNavTriggerIcon = jQuery('a.mobilenavtrigger i');

				mobileNav.data('mmenu').bind('open:start', function($panel){
					//When mmenu has started opening
					mobileNavTriggerIcon.removeClass('fa-bars').addClass('fa-times');

					if ( typeof jQuery.tooltip !== 'undefined' ){
						jQuery('[data-toggle="tooltip"]').tooltip('hide');
					}

					nebula.timer('(Nebula) Mmenu', 'start');
				}).bind('close:start', function($panel){
					//When mmenu has started closing
					mobileNavTriggerIcon.removeClass('fa-times').addClass('fa-bars');
					ga('send', 'timing', 'Mmenu', 'Closed', Math.round(nebula.timer('(Nebula) Mmenu', 'lap')), 'From opening mmenu until closing mmenu');
				});
			}

			nebula.dom.document.on('click', '.mm-menu li a:not(.mm-next)', function(){
				ga('send', 'timing', 'Mmenu', 'Navigated', Math.round(nebula.timer('(Nebula) Mmenu', 'lap')), 'From opening mmenu until navigation');
			});
		}
	}
};

//Vertical subnav expanders
nebula.subnavExpanders = function(){
	if ( nebula.site.options.sidebar_expanders && jQuery('#sidebar-section .menu').length ){ //@todo "Nebula" 0: Use optional chaining
		jQuery('#sidebar-section .menu li.menu-item:has(ul)').addClass('has-expander').append('<a class="toplevelvert_expander closed" href="#"><i class="fas fa-caret-left"></i> <span class="sr-only">Expand</span></a>');
		jQuery('.toplevelvert_expander').parent().children('.sub-menu').hide();
		nebula.dom.document.on('click', '.toplevelvert_expander', function(){
			jQuery(this).toggleClass('closed open').parent().children('.sub-menu').slideToggle();
			return false;
		});
		//Automatically expand subnav to show current page
		jQuery('.current-menu-ancestor').children('.toplevelvert_expander').click();
		jQuery('.current-menu-item').children('.toplevelvert_expander').click();
	}
};

//Convert DOM elements into a tree string
nebula.domTreeToString = function(element){
	//If the element is a selector, convert to a jQuery object
	if ( typeof element === 'string' ){
		element = jQuery(element);
	}

	//If the element is a native JS object, convert to jQuery
	if ( element.nodeType ){
		element = jQuery(element);
	}

	//Map the parent elements into an array and concatenate together
	var selector = element.parents().map(function(){
		var parentTag = this.tagName.toLowerCase();

		//Append the ID if a parent element has one
		var parentID = jQuery(this).attr('id');
		if ( parentID ){
			parentTag += '#' + parentID;
		}

		return parentTag;
	}).get().reverse().concat([this.nodeName]).join(' > ');

	selector += element.get(0).tagName.toLowerCase();

	//Append the ID to the last element
	var id = element.attr('id');
	if ( id ){
		selector += '#' + id;
	}

	//Add the classnames to the last element
	var classNames = element.attr('class');
	if ( classNames ){
		selector += '.' + classNames.trim().replace(/\s/gi, '.');
	}

	return selector;
};

/*==========================
 Legacy Functions
 These will live for short periods after a major change to prevent Nebula auto-update from breaking child themes.
 ===========================*/

//This is an old function that checked if GA was ready.
function isGoogleAnalyticsReady(){
	return true;
}

/*==========================
 Extension Functions
 ===========================*/

//Custom CSS expression for a case-insensitive contains(). Source: https://css-tricks.com/snippets/jquery/make-jquery-contains-case-insensitive/
//Call it with :Contains() - Ex: ...find("*:Contains(" + jQuery('.something').val() + ")")... -or- use the nebula function: nebula.keywordSearch(container, parent, value);
jQuery.expr.pseudos.Contains = function(element, index, match){
	return (element.textContent || element.innerText || '').toUpperCase().indexOf(match[3].toUpperCase()) >= 0;
};

//Custom expression for any element that can be focused. Source: https://github.com/selfthinker/dokuwiki_template_writr/blob/master/js/skip-link-focus-fix.js
//Call it with :focusable() or .is(':focusable')
jQuery.expr.pseudos.focusable = function(element, index, match){
	return jQuery(element).is(':input:enabled, a[href], area[href], object, [tabindex]') && !jQuery(element).is(':hidden');
};

//Escape required characters from a provided string. https://github.com/kvz/locutus
function preg_quote(str, delimiter){return (str + '').replace(new RegExp('[.\\\\+*?\\[\\^\\]$(){}=!<>|:\\' + (delimiter || '') + '-]', 'g'), '\\$&');}

//Parse dates (equivalent of PHP strtotime function). https://github.com/kvz/locutus
function strtotime(e,t){var a,n,r,s,u,i,o,w,c,d,D,g=!1;if(!e)return g;e=e.replace(/^\s+|\s+$/g,"").replace(/\s{2,}/g," ").replace(/[\t\r\n]/g,"").toLowerCase();var l=new RegExp(["^(\\d{1,4})","([\\-\\.\\/:])","(\\d{1,2})","([\\-\\.\\/:])","(\\d{1,4})","(?:\\s(\\d{1,2}):(\\d{2})?:?(\\d{2})?)?","(?:\\s([A-Z]+)?)?$"].join(""));if((n=e.match(l))&&n[2]===n[4])if(n[1]>1901)switch(n[2]){case"-":return n[3]>12||n[5]>31?g:new Date(n[1],parseInt(n[3],10)-1,n[5],n[6]||0,n[7]||0,n[8]||0,n[9]||0)/1e3;case".":return g;case"/":return n[3]>12||n[5]>31?g:new Date(n[1],parseInt(n[3],10)-1,n[5],n[6]||0,n[7]||0,n[8]||0,n[9]||0)/1e3}else if(n[5]>1901)switch(n[2]){case"-":case".":return n[3]>12||n[1]>31?g:new Date(n[5],parseInt(n[3],10)-1,n[1],n[6]||0,n[7]||0,n[8]||0,n[9]||0)/1e3;case"/":return n[1]>12||n[3]>31?g:new Date(n[5],parseInt(n[1],10)-1,n[3],n[6]||0,n[7]||0,n[8]||0,n[9]||0)/1e3}else switch(n[2]){case"-":return n[3]>12||n[5]>31||n[1]<70&&n[1]>38?g:(s=n[1]>=0&&n[1]<=38?+n[1]+2e3:n[1],new Date(s,parseInt(n[3],10)-1,n[5],n[6]||0,n[7]||0,n[8]||0,n[9]||0)/1e3);case".":return n[5]>=70?n[3]>12||n[1]>31?g:new Date(n[5],parseInt(n[3],10)-1,n[1],n[6]||0,n[7]||0,n[8]||0,n[9]||0)/1e3:n[5]<60&&!n[6]?n[1]>23||n[3]>59?g:(r=new Date,new Date(r.getFullYear(),r.getMonth(),r.getDate(),n[1]||0,n[3]||0,n[5]||0,n[9]||0)/1e3):g;case"/":return n[1]>12||n[3]>31||n[5]<70&&n[5]>38?g:(s=n[5]>=0&&n[5]<=38?+n[5]+2e3:n[5],new Date(s,parseInt(n[1],10)-1,n[3],n[6]||0,n[7]||0,n[8]||0,n[9]||0)/1e3);case":":return n[1]>23||n[3]>59||n[5]>59?g:(r=new Date,new Date(r.getFullYear(),r.getMonth(),r.getDate(),n[1]||0,n[3]||0,n[5]||0)/1e3)}if("now"===e)return null===t||isNaN(t)?(new Date).getTime()/1e3|0:0|t;if(!isNaN(a=Date.parse(e)))return a/1e3|0;if(l=new RegExp(["^([0-9]{4}-[0-9]{2}-[0-9]{2})","[ t]","([0-9]{2}:[0-9]{2}:[0-9]{2}(\\.[0-9]+)?)","([\\+-][0-9]{2}(:[0-9]{2})?|z)"].join("")),(n=e.match(l))&&("z"===n[4]?n[4]="Z":n[4].match(/^([+-][0-9]{2})$/)&&(n[4]=n[4]+":00"),!isNaN(a=Date.parse(n[1]+"T"+n[2]+n[4]))))return a/1e3|0;function f(e){var t,a,n,r,s=e.split(" "),w=s[0],c=s[1].substring(0,3),d=/\d+/.test(w),D="ago"===s[2],g=("last"===w?-1:1)*(D?-1:1);if(d&&(g*=parseInt(w,10)),o.hasOwnProperty(c)&&!s[1].match(/^mon(day|\.)?$/i))return u["set"+o[c]](u["get"+o[c]]()+g);if("wee"===c)return u.setDate(u.getDate()+7*g);if("next"===w||"last"===w)t=w,a=g,void 0!==(r=i[c])&&(0==(n=r-u.getDay())?n=7*a:n>0&&"last"===t?n-=7:n<0&&"next"===t&&(n+=7),u.setDate(u.getDate()+n));else if(!d)return!1;return!0}if(u=t?new Date(1e3*t):new Date,i={sun:0,mon:1,tue:2,wed:3,thu:4,fri:5,sat:6},o={yea:"FullYear",mon:"Month",day:"Date",hou:"Hours",min:"Minutes",sec:"Seconds"},d="([+-]?\\d+\\s"+(c="(years?|months?|weeks?|days?|hours?|minutes?|min|seconds?|sec|sunday|sun\\.?|monday|mon\\.?|tuesday|tue\\.?|wednesday|wed\\.?|thursday|thu\\.?|friday|fri\\.?|saturday|sat\\.?)")+"|(last|next)\\s"+c+")(\\sago)?",!(n=e.match(new RegExp(d,"gi"))))return g;for(D=0,w=n.length;D<w;D++)if(!f(n[D]))return g;return u.getTime()/1e3;}