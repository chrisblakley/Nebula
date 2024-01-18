window.performance.mark('(Nebula) Inside analytics.js (module)');

//Set a custom dimension in both GA and MS Clarity (used in /inc/analytics.php)
nebula.setDimension = function(name, value){ //Does not technically need to be exported anymore as it is only now used here in this file
	//Google Analytics
	if ( typeof gtag === 'function' && name ){
		gtag('set', 'user_properties', {
			[name]: value
		});
	}

	//Microsoft Clarity
	if ( typeof clarity === 'function' ){
		clarity('set', name, value);
	}

	//Others
	document.dispatchEvent(new CustomEvent('nebula_dimension', {detail: {'name': name, 'value': value}})); //Allow this dimension to be sent to other platforms from outside Nebula
};

//Prep an object of dimensions that can be included in any subsequent event sends to GA
nebula.allHitDimensions = function(){
	let dimensions = {};

	//If the initial pageview was set as debug_mode, then use that for all events too
	if ( nebula?.pageviewProperties?.debug_mode ){
		dimensions.debug_mode = true;
	}

	// dimensions.query_string = window.location.search;
	// dimensions.network_connection = ( navigator.onLine )? 'Online' : 'Offline';
	// dimensions.visibility_state = document.visibilityState;
	// dimensions.local_timestamp = nebula.localTimestamp();
	// dimensions.hit_time = String(new Date);
	// dimensions.hit_id = nebula.uniqueId(); //Give each hit a unique ID

	//Bootstrap Breakpoint
	// if ( window.matchMedia("(min-width: 2048px)").matches ){
	// 	dimensions.mq_breakpoint = 'uw';
	// } else if ( window.matchMedia("(min-width: 1400px)").matches ){
	// 	dimensions.mq_breakpoint = 'xxl';
	// } else if ( window.matchMedia("(min-width: 1200px)").matches ){
	// 	dimensions.mq_breakpoint = 'xl';
	// } else if ( window.matchMedia("(min-width: 992px)").matches ){
	// 	dimensions.mq_breakpoint = 'lg';
	// } else if ( window.matchMedia("(min-width: 768px)").matches ){
	// 	dimensions.mq_breakpoint = 'md';
	// } else if ( window.matchMedia("(min-width: 544px)").matches ){
	// 	dimensions.mq_breakpoint = 'sm';
	// } else {
	// 	dimensions.mq_breakpoint = 'sm';
	// }

	//Screen Resolution
	// if ( window.matchMedia("(min-resolution: 192dpi)").matches ){
	// 	dimensions.screen_resolution = '2x';
	// } else if ( window.matchMedia("(min-resolution: 144dpi)").matches ){
	// 	dimensions.screen_resolution = '1.5x';
	// } else {
	// 	dimensions.screen_resolution = '1x';
	// }

	//Screen Orientation
	// if ( window.matchMedia("(orientation: portrait)").matches ){
	// 	dimensions.screen_orientation = 'Portrait';
	// } else if ( window.matchMedia("(orientation: landscape)").matches ){
	// 	dimensions.screen_orientation = 'Landscape';
	// }

	//Device Memory
	// if ( 'deviceMemory' in navigator ){ //Chrome 64+
	// 	let deviceMemoryLevel = ( navigator.deviceMemory < 1 )? 'Lite' : 'Full';
	// 	dimensions.device_memory = navigator.deviceMemory + '(' + deviceMemoryLevel + ')';
	// } else {
	// 	dimensions.device_memory = 'Unavailable';
	// }

	return dimensions;
};

//Prep an event object to send to Google Analytics
//Note: this process destructively modifies the passed object. That object cannot easily be cloned because it often contains nested objects.
nebula.gaEventObject = function(eventObject){
	if ( !eventObject['event_name'] ){
		console.warn('[Nebula Help] GA4 requires an event name! This event does not have an event_name:', eventObject);
		eventObject['event_name'] = 'unnamed_event';
	}

	//Removing nested objects so we can clone it to prevent further altering the original object
	for ( var key in eventObject ){
		if ( (typeof eventObject[key] === 'object' || typeof eventObject[key] === 'function') && eventObject[key] !== null ){
			delete eventObject[key]; //Delete the nested object property
		}
	}

	try {
		var clonedEventObject = structuredClone(eventObject); //Has to be var to be available outside of the try
	} catch(e){
		var clonedEventObject = eventObject; //If the clone fails, just use the provided object directly
	}

	if ( nebula.user.staff && clonedEventObject['event_name'].length > 40 ){ //If the event name is longer than 40 characters
		console.warn('[Nebula Help] The GA4 event name "' + clonedEventObject['event_name'] + '" is too long (' + clonedEventObject['event_name'].length + ' characters). Event names must be 40 characters or less.');
	}

	//Remember: the following modifies the original object! So it cannot be used again without re-adding these back in elsewhere!
	delete clonedEventObject['e']; //Remove the DOM Event key
	delete clonedEventObject['event']; //Remove the DOM Event key
	delete clonedEventObject['event_name']; //Name is sent separately outside of the object parameter, so remove it here. We don't want to "use up" additional parameters on this.
	delete clonedEventObject['email_address'];

	// for ( var key of Object.keys(clonedEventObject) ){
	// 	if ( typeof clonedEventObject[key] === 'object' || typeof clonedEventObject[key] === 'function' ){
	// 		delete clonedEventObject[key]; //Remove any objects or functions
	// 	}
	// }

	//Add contextual parameters to the event object
	let fullContextObject = Object.assign(nebula.allHitDimensions(), clonedEventObject);

	return fullContextObject;
};

//Google Analytics Universal Analytics Event Trackers
nebula.eventTracking = async function(){
	if ( nebula.isDoNotTrack() ){
		return false;
	}

	nebula.cacheSelectors(); //Just to be safe (this is no longer called from anywhere besides nebula.js so this should never be needed)

	//Check for Topics API support @todo "Nebula" 0: when it is better supported update this further
	if ( 'browsingTopics' in document && document.featurePolicy.allowsFeature('browsing-topics') ){ //Seems to be available in Chrome 117+ (at least in Canary)
		try {
			document.browsingTopics().then(function(topics){
				//console.log('Interest Topics:', topics);

				if ( topics && topics.length ){ //If the topics array is not empty
					gtag('event', 'topics_api', {
						event_category: 'Topics API',
						event_action: 'Interests',
						event_label: topics.join(', '),
						interests: topics.join(', '),
						non_interaction: true
					});
				}
			});
		} catch {
			//Ignore errors
		}
	}

	nebula.once(function(){
		window.dataLayer = window.dataLayer || []; //Prevent overwriting an existing GTM Data Layer array
		window.hj = window.hj || function(){(hj.q=hj.q||[]).push(arguments);}; //Ensure Hotjar is initialized

		nebula.dom.document.trigger('nebula_event_tracking');

		//Output Nebula event triggers to the console during GTM debug mode
		if ( nebula.get('gtm_debug') ){
			jQuery(document).on('nebula_event', function(event, nebula_event){ //event is the DOM event, nebula_event is the Nebula data
				console.log('[Nebula Event] ', nebula_event.event_name, nebula_event);
			});
		}

		if ( nebula?.user?.cid ){
			window.dataLayer.push(Object.assign({'client-id': nebula.user.cid}));
		} else if ( nebula?.analytics?.measurementID && typeof window.gtag === 'function' ){
			gtag('get', nebula.analytics.measurementID, 'client_id', function(clientId){
				nebula.user.id = clientId;
				window.dataLayer.push(Object.assign({'client-id': clientId}));
			});
		}

		nebula.attributionTracking();

		//When the page is restored from BFCache (which means it is not fully reloaded)
		window.addEventListener('pageshow', function(event){
			if ( event.persisted === true ){
				//Send another pageview if the page is restored from bfcache
				gtag('event', 'page_view', {
					type: 'bfcache'
				});
			}
		});

		//Back/Forward
		if ( performance.navigation.type === 2 ){ //If the user arrived at this page via the back/forward button
			let previousPage = '(Unknown)';
			let quickBack = false;
			if ( 'localStorage' in window ){
				let prev = JSON.parse(localStorage.getItem('prev')); //Get the previous page from localstorage
				previousPage = prev.path; //Get the previous page from localstorage
				quickBack = prev.quick || false;
			}

			gtag('event', 'browser_navigation', {
				event_category: 'Browser Navigation',
				event_action: 'Back/Forward',
				type: 'Back/Forward',
				event_label: 'From: ' + previousPage,
				previous_page: previousPage,
				non_interaction: true
			});

			if ( quickBack && previousPage !== document.location.pathname ){ //If the previous page was viewed for a very short time and is different than the current page
				gtag('event', 'quick_back', { //Technically this could be a "quick forward" too, but less likely
					event_category: 'Quick Back',
					event_action: 'Quickly left from: ' + previousPage,
					event_label: 'Back to: ' + document.location.pathname,
					back_from: previousPage,
					back_to: document.location.pathname,
					non_interaction: true
				});
			}
		}

		//Reloads
		if ( performance.navigation.type === 1 ){ //If the user reloaded the page
			gtag('event', 'browser_navigation', {
				event_category: 'Browser Navigation',
				event_action: 'Reload',
				type: 'Reload',
				event_label: document.location.pathname,
				path: document.location.pathname,
				non_interaction: true
			});
		}

		//Prep page info and detect quick unloads
		if ( 'localStorage' in window ){
			let prev = {
				path: document.location.pathname, //Prep the "previous page" to this page for future use.
				quick: true //Set this to true initially until it is not longer considered a quick back
			};

			localStorage.setItem('prev', JSON.stringify(prev)); //Store them in localstorage

			//After 4 seconds change quick to false so it is no longer considered a quick back
			setTimeout(function(){
				prev.quick = false;
				localStorage.setItem('prev', JSON.stringify(prev));
			}, 4000);
		}

		//When the page becomes frozen/unfrozen by the browser Lifecycle API
		// document.addEventListener('freeze', function(event){
		// 	gtag('event', 'page_lifecycle_frozen', { //Note that "frozen" does not indicate an error. The browser has preserved its state as inactive.
		// 		event_category: 'Page Lifecycle',
		// 		event_action: 'Frozen',
		// 		state: 'Frozen',
		// 		non_interaction: true
		// 	});
		// });
		// document.addEventListener('resume', function(event){
		// 	gtag('event', 'page_lifecycle_resumed', { //This may happen when it is unfrozen from a frozen state, or from BFCache.
		// 		event_category: 'Page Lifecycle',
		// 		event_action: 'Resumed',
		// 		state: 'Resumed',
		// 		non_interaction: true
		// 	});
		// });

		//Button Clicks
		let nebulaButtonSelector = wp.hooks.applyFilters('nebulaButtonSelectors', 'button, .button, .btn, [role="button"], a.wp-block-button__link, .wp-element-button, .woocommerce-button, .hs-button'); //Allow child theme or plugins to add button selectors without needing to override/duplicate this function
		nebula.dom.document.on('pointerdown', nebulaButtonSelector, function(e){ //Use "pointerdown" here so certain buttons with "onclick" functionality don't ignore this tracking (like Woocommerce buttons)
			let thisEvent = {
				event: e,
				event_name: 'button_click',
				event_category: 'Button Click',
				event_action: (jQuery(this).val() || jQuery(this).attr('value') || jQuery(this).text() || jQuery(this).attr('title') || '(Unknown)').trimAll(),
				event_label: jQuery(this).attr('href') || jQuery(this).attr('title') || '(Unknown)',
				text: (jQuery(this).val() || jQuery(this).attr('value') || jQuery(this).text() || jQuery(this).attr('title') || '(Unknown)').trimAll(),
				link: jQuery(this).attr('href') || jQuery(this).attr('title') || '(Unknown)'
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_button_click'}));
		});

		//Linked Image Clicks
		nebula.dom.document.on('click', 'a img', function(e){
			let thisEvent = {
				event: e,
				event_name: 'image_click',
				event_category: 'Image Click',
				event_action: jQuery(this).attr('alt') || jQuery(this).attr('src'),
				alt: jQuery(this).attr('alt'),
				src: jQuery(this).attr('src'),
				event_label: jQuery(this).parents('a').attr('href'),
				link: jQuery(this).parents('a').attr('href')
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_image_click'}));
		});

		//Bootstrap "Collapse" Accordions
		nebula.dom.document.on('shown.bs.collapse', function(e){
			let thisEvent = {
				event: e,
				event_name: 'accordion_toggle',
				event_category: 'Accordion',
				event_action: 'Shown',
				event_label: jQuery('[data-bs-target="#' + e.target.id + '"]').text().trimAll() || e.target.id,
				type: 'Accordion',
				state: 'Shown',
				id: jQuery('[data-bs-target="#' + e.target.id + '"]').text().trimAll() || e.target.id,
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_accordion_shown'}));
		});
		nebula.dom.document.on('hidden.bs.collapse', function(e){
			let thisEvent = {
				event: e,
				event_name: 'accordion_toggle',
				event_category: 'Accordion',
				event_action: 'Hidden',
				event_label: jQuery('[data-bs-target="#' + e.target.id + '"]').text().trimAll() || e.target.id,
				type: 'Accordion',
				state: 'Hidden',
				id: jQuery('[data-bs-target="#' + e.target.id + '"]').text().trimAll() || e.target.id,
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_accordion_hidden'}));
		});

		//Bootstrap Modals
		nebula.dom.document.on('shown.bs.modal', function(e){
			let thisEvent = {
				event: e,
				event_name: 'modal_toggle',
				event_category: 'Modal',
				event_action: 'Shown',
				event_label: jQuery('#' + e.target.id + ' .modal-title').text().trimAll() || e.target.id,
				type: 'Modal',
				state: 'Shown',
				id: jQuery('#' + e.target.id + ' .modal-title').text().trimAll() || e.target.id,
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_modal_shown'}));
		});
		nebula.dom.document.on('hidden.bs.modal', function(e){
			let thisEvent = {
				event: e,
				event_name: 'modal_toggle',
				event_category: 'Modal',
				event_action: 'Hidden',
				event_label: jQuery('#' + e.target.id + ' .modal-title').text().trimAll() || e.target.id,
				type: 'Modal',
				state: 'Hidden',
				id: jQuery('#' + e.target.id + ' .modal-title').text().trimAll() || e.target.id,
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_modal_hidden'}));
		});

		//Bootstrap Carousels (Sliders)
		nebula.dom.document.on('slide.bs.carousel', function(e){
			if ( window.event ){ //Only if sliding manually
				let thisEvent = {
					event: e,
					event_name: 'carousel_slide',
					event_category: 'Carousel',
					event_action: e.target.id || e.target.title || e.target.className.replaceAll(/\s/g, '.'),
					event_label: 'Label',
					type: 'Carousel',
					id: e.target.id || e.target.title || e.target.className.replaceAll(/\s/g, '.'),
					from: e.from,
					to: e.to,
				};

				thisEvent.activeSlide = jQuery(e.target).find('.carousel-item').eq(e.to);
				thisEvent.activeSlideName = thisEvent.activeSlide.attr('id') || thisEvent.activeSlide.attr('title') || 'Unnamed';
				thisEvent.prevSlide = jQuery(e.target).find('.carousel-item').eq(e.from);
				thisEvent.prevSlideName = thisEvent.prevSlide.attr('id') || thisEvent.prevSlide.attr('title') || 'Unnamed';
				thisEvent.event_label = 'Slide to ' + thisEvent.to + ' (' + thisEvent.activeSlideName + ') from ' + thisEvent.from + ' (' + thisEvent.prevSlideName + ')';
				thisEvent.description = 'Slide to ' + thisEvent.to + ' (' + thisEvent.activeSlideName + ') from ' + thisEvent.from + ' (' + thisEvent.prevSlideName + ')';

				nebula.dom.document.trigger('nebula_event', thisEvent);
				gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_carousel_slide'}));
			}
		});

		//Generic Form Submissions
		//This event will be a duplicate if proper event tracking is setup on each form, but serves as a safety net.
		//It is not recommended to use this event for goal tracking unless absolutely necessary (this event does not check for submission success)!
		nebula.dom.document.on('submit', 'form', function(e){
			let thisEvent = {
				event: e,
				event_name: 'generic_form_submit', //How to differentiate this from conversions?
				event_category: 'Generic Form',
				event_action: 'Submit',
				event_label: e.target.id || 'form.' + e.target.className.replaceAll(/\s/g, '.'),
				form_id: e.target.id || 'form.' + e.target.className.replaceAll(/\s/g, '.'),
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.event_category, thisEvent.event_action);}
			nebula.hj('event', thisEvent.event_name);
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_generic_form'}));
		});

		//Notable File Downloads
		let notableFileExtensions = wp.hooks.applyFilters('nebulaNotableFiles', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv', 'zip', 'zipx', 'rar', 'gz', 'tar', 'txt', 'rtf', 'ics', 'vcard']);
		jQuery.each(notableFileExtensions, function(index, extension){
			jQuery("a[href$='." + extension + "' i]").on('click', function(e){ //Cannot defer case insensitive attribute selectors in jQuery (or else you will get an "unrecognized expression" error)
				let thisEvent = {
					event: e,
					event_name: 'nebula_file_download', //Note: This is a default GA4 event
					event_category: 'File Download',
					event_action: extension,
					event_label: jQuery(this).attr('href').substr(jQuery(this).attr('href').lastIndexOf('/')+1),
					text: jQuery(this).text().trimAll(),
					file_extension: extension,
					file_name: jQuery(this).attr('href').substr(jQuery(this).attr('href').lastIndexOf('/')+1),
				};

				nebula.dom.document.trigger('nebula_event', thisEvent);
				gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_file_download'}));
				nebula.fbq('track', 'ViewContent', {content_name: thisEvent.file_name});
				nebula.clarity('set', thisEvent.event_category, thisEvent.file_name);
				nebula.hj('event', thisEvent.event_name);
				nebula.crm('event', 'File Download');
			});
		});

		//Notable Downloads
		nebula.dom.document.on('click', '.notable a, a.notable', function(e){
			let thisEvent = {
				event: e,
				event_name: 'notable_file_download',
				event_category: 'File Download',
				event_label: jQuery(this).attr('href').trimAll(),
				event_action: 'Notable',
				file_path: jQuery(this).attr('href').trimAll(),
				text: jQuery(this).text(),
				link: jQuery(this).attr('href')
			};

			if ( thisEvent.file_path.length && thisEvent.file_path !== '#' ){
				thisEvent.file_name = file_path.substr(file_path.lastIndexOf('/')+1);
				nebula.dom.document.trigger('nebula_event', thisEvent);
				gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'notable_file_download'}));
				nebula.fbq('track', 'ViewContent', {content_name: thisEvent.file_name});
				nebula.clarity('set', thisEvent.event_category, thisEvent.file_name);
				nebula.crm('event', 'Notable File Download');
			}
		});

		//Generic Internal Search Tracking
		//This event will need to correspond to the GA4 event name "search" and use "search_term" as a parameter: https://support.google.com/analytics/answer/9267735
		let internalSearchInputSelector = wp.hooks.applyFilters('nebulaInternalSearchInputs', '#s, input.search');
		nebula.dom.document.on('submit', internalSearchInputSelector, function(){
			let thisEvent = {
				event: e,
				event_name: 'search',
				event_category: 'Internal Search',
				event_action: 'Search Query',
				event_label: jQuery(this).find('input[name="s"]').val().toLowerCase().trimAll(),
				type: 'Internal Search',
				event_action: 'Submit',
				query: jQuery(this).find('input[name="s"]').val().toLowerCase().trimAll()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_internal_search'}));
			nebula.fbq('track', 'Search', {search_string: thisEvent.query});
			nebula.clarity('set', thisEvent.event_category, thisEvent.query);
			nebula.hj('event', thisEvent.event_name);
			nebula.crm('identify', {internal_search: thisEvent.query});
		});

		//Search results link clicks
		nebula.dom.document.on('click', '#searchresults a', function(e){
			let thisEvent = {
				event: e,
				event_name: 'serp_click',
				event_category: 'Internal Search',
				event_action: 'SERP Click',
				event_label: jQuery(this).attr('href'),
				text: jQuery(this).text(),
				link: jQuery(this).attr('href'),
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			nebula.crm('event', 'Page Suggestion Click');
		});

		//No search results
		if ( jQuery('.no-search-results').length ){ //This relies on this class existing when no search results are shown
			let thisEvent = {
				event_name: 'no_search_results',
				event_category: 'Internal Search',
				event_action: 'No Search Results',
				event_label: nebula.get('s'),
				query: nebula.get('s'),
				non_interaction: true //This happens immediately on load and should not affect bounce rate
			};

			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			nebula.crm('event', 'No Search Results');
		};

		//Suggested pages on 404 results
		nebula.dom.document.on('click', 'a.internal-suggestion', function(e){
			let thisEvent = {
				event: e,
				event_name: 'page_suggestion_click',
				event_category: 'Page Suggestion Click',
				event_action: 'Internal',
				event_label: jQuery(this).text(),
				type: 'Internal',
				text: jQuery(this).text(),
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			nebula.crm('event', 'Page Suggestion Click');
		});

		//Keyboard Shortcut (Non-interaction because they are not taking explicit action with the webpage)
		nebula.dom.document.on('keydown', function(e){
			window.modifiedZoomLevel = window.modifiedZoomLevel || 0; //Scope to window so it is not reset every event. Note: This is just how it was modified and not the actual zoom level! Zoom level is saved between pageloads so it may have started at non-zero!

			//Ctrl+ (Zoom In)
			if ( (e.ctrlKey || e.metaKey) && (e.keyCode === 187 || e.keyCode === 107) ){ //187 is plus (and equal), 107 is plus on the numpad
				modifiedZoomLevel++; //Increment the zoom level iterator

				let thisEvent = {
					event: e,
					event_name: 'zoom_change',
					event_category: 'Keyboard Shortcut',
					event_action: 'Zoom In (Ctrl+)',
					event_label: modifiedZoomLevel,
					modified_zoom_level: modifiedZoomLevel,
					non_interaction: true
				};

				thisEvent.event_label = 'Modified Zoom Level: ' + thisEvent.modified_zoom_level;

				nebula.dom.document.trigger('nebula_event', thisEvent);
				gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_zoom_in'}));
			}

			//Ctrl- (Zoom Out)
			if ( (e.ctrlKey || e.metaKey) && (e.keyCode === 189 || e.keyCode === 109) ){ //189 is minus, 109 is minus on the numpad
				modifiedZoomLevel--; //Decrement the zoom level iterator

				let thisEvent = {
					event: e,
					event_name: 'zoom_change',
					event_category: 'Keyboard Shortcut',
					event_action: 'Zoom Out (Ctrl-)',
					event_label: modifiedZoomLevel,
					modified_zoom_level: modifiedZoomLevel,
					non_interaction: true
				};

				thisEvent.event_label = 'Modified Zoom Level: ' + thisEvent.modified_zoom_level;

				nebula.dom.document.trigger('nebula_event', thisEvent);
				gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_zoom_out'}));
			}

			//Ctrl+0 (Reset Zoom)
			if ( (e.ctrlKey || e.metaKey) && (e.keyCode === 48 || e.keyCode === 0 || e.keyCode === 96) ){ //48 is 0 (Mac), 0 is Windows 0, and 96 is Windows numpad
				modifiedZoomLevel = 0; //Reset the zoom level iterator

				let thisEvent = {
					event: e,
					event_name: 'zoom_change',
					event_category: 'Keyboard Shortcut',
					event_action: 'Reset Zoom (Ctrl+0)',
					event_label: modifiedZoomLevel,
					modified_zoom_level: modifiedZoomLevel,
					non_interaction: true
				};

				thisEvent.event_label = 'Modified Zoom Level: ' + thisEvent.modified_zoom_level;

				nebula.dom.document.trigger('nebula_event', thisEvent);
				gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_zoom_reset'}));
			}

			//Ctrl+F or Cmd+F (Find)
			if ( (e.ctrlKey || e.metaKey) && e.keyCode === 70 ){
				let thisEvent = {
					event: e,
					event_name: 'find_on_page', //We will not have a "search_term" parameter. Make sure we do have something to note that this is a Find On Page
					event_category: 'Keyboard Shortcut',
					event_action: 'Find on Page (Ctrl+F)',
					event_label: window.getSelection().toString().trimAll() || '(No highlighted text when initiating find)',
					highlighted_ext: window.getSelection().toString().trimAll() || '(No highlighted text when initiating find)',
					non_interaction: true
				};

				nebula.dom.document.trigger('nebula_event', thisEvent);
				gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_find_on_page'}));
			}

			//Ctrl+D or Cmd+D (Bookmark)
			if ( (e.ctrlKey || e.metaKey) && e.keyCode === 68 ){ //Ctrl+D
				let thisEvent = {
					event: e,
					event_name: 'bookmark',
					event_category: 'Keyboard Shortcut',
					event_action: 'Bookmark (Ctrl+D)',
					event_label: 'User bookmarked the page (with keyboard shortcut)',
					description: 'User bookmarked the page (with keyboard shortcut)',
					non_interaction: true
				};

				nebula.removeQueryParameter(['utm_campaign', 'utm_medium', 'utm_source', 'utm_content', 'utm_term'], window.location.href); //Remove existing UTM parameters
				history.replaceState(null, document.title, window.location.href + '?utm_source=bookmark');
				nebula.dom.document.trigger('nebula_event', thisEvent);
				gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_bookmark'}));
			}
		});

		//Mailto link tracking
		nebula.dom.document.on('click', 'a[href^="mailto"]', function(e){
			let emailAddress = jQuery(this).attr('href').replace('mailto:', '');
			let emailDomain = emailAddress.split('@')[1]; //Get everything after the @
			let anonymizedEmail = nebula.anonymizeEmail(emailAddress); //Mask the email with asterisks

			let thisEvent = {
				event: e,
				event_name: 'mailto',
				event_category: 'Contact',
				event_action: 'Mailto',
				type: 'Mailto',
				event_label: ( emailAddress.toLowerCase().includes(window.location.hostname) )? emailAddress : anonymizedEmail, //If the email matches the website use it, otherwise use the anonymized email
				email_address: ( emailAddress.toLowerCase().includes(window.location.hostname) )? emailAddress : anonymizedEmail,
				email_domain: emailDomain,
			};

			gtag('set', 'user_properties', {
				contact_method : 'Email'
			});

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_mailto'}));
			nebula.fbq('track', 'Lead', {content_name: thisEvent.event_action});
			nebula.clarity('set', thisEvent.event_category, thisEvent.event_action);
			nebula.hj('event', thisEvent.event_name);
			nebula.crm('event', thisEvent.event_action);
			nebula.crm('identify', {mailto_contacted: thisEvent.emailAddress});
		});

		//Telephone link tracking
		nebula.dom.document.on('click', 'a[href^="tel"]', function(e){
			let thisEvent = {
				event: e,
				event_name: 'click_to_call',
				event_category: 'Contact',
				event_action: 'Click-to-Call',
				event_label: jQuery(this).attr('href').replace('tel:', ''),
				type: 'Click-to-Call',
				phone_number: jQuery(this).attr('href').replace('tel:', '')
			};

			gtag('set', 'user_properties', {
				contact_method : 'Phone'
			});

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_click_to_call'}));
			nebula.fbq('track', 'Lead', {content_name: thisEvent.event_action});
			nebula.clarity('set', thisEvent.event_category, thisEvent.event_action);
			nebula.hj('event', thisEvent.event_name);
			nebula.crm('event', thisEvent.event_action);
			nebula.crm('identify', {phone_contacted: thisEvent.phoneNumber});
		});

		//SMS link tracking
		nebula.dom.document.on('click', 'a[href^="sms"]', function(e){
			let thisEvent = {
				event: e,
				event_name: 'sms',
				event_category: 'Contact',
				event_action: 'SMS',
				event_label: jQuery(this).attr('href').replace('sms:', ''),
				type: 'SMS',
				phone_number: jQuery(this).attr('href').replace('sms:', '')
			};

			gtag('set', 'user_properties', {
				contact_method : 'SMS'
			});

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_sms'}));
			nebula.fbq('track', 'Lead', {content_name: thisEvent.event_action});
			nebula.clarity('set', thisEvent.event_category, thisEvent.event_action);
			nebula.crm('event', thisEvent.event_action);
			nebula.crm('identify', {phone_contacted: thisEvent.phoneNumber});
		});

		//Street Address click //@todo "Nebula" 0: How to detect when a user clicks an address that is not linked, but mobile opens the map anyway? What about when it *is* linked?

		//Utility Navigation Menu
		nebula.dom.document.on('click', '#utility-nav ul.menu a', function(e){
			let thisEvent = {
				event: e,
				event_name: 'menu_click',
				event_category: 'Navigation Menu',
				event_action: 'Utility Menu',
				event_label: jQuery(this).text().trimAll(),
				menu: 'Utility Menu',
				text: jQuery(this).text().trimAll()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_navigation_menu_click'}));
		});

		//Primary Navigation Menu
		nebula.dom.document.on('click', '#primary-nav ul.menu a', function(e){
			let thisEvent = {
				event: e,
				event_name: 'menu_click',
				event_category: 'Navigation Menu',
				event_action: 'Primary Menu',
				event_label: jQuery(this).text().trimAll(),
				menu: 'Primary Menu',
				text: jQuery(this).text().trimAll()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_navigation_menu_click'}));
		});

		//Offcanvas Menu Open
		nebula.dom.document.on('show.bs.offcanvas', function(e){
			let thisEvent = {
				event: e,
				event_name: 'offcanvas_menu_toggle',
				event_category: 'Navigation Menu',
				event_action: 'Offcanvas Menu (' + e.target.id + ')',
				event_label: 'Opened',
				menu: 'Offcanvas Menu (' + e.target.id + ')',
				state: 'Opened',
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_offcanvas_menu_shown'}));

			nebula.timer('(Nebula) Offcanvas Menu', 'start');
		});

		//Offcanvas Menu Close
		nebula.dom.document.on('hide.bs.offcanvas', function(e){
			let thisEvent = {
				event: e,
				event_name: 'offcanvas_menu_toggle',
				event_category: 'Navigation Menu',
				event_action: 'Offcanvas Menu (' + e.target.id + ')',
				event_label: 'Closed (without Navigation)',
				menu: 'Offcanvas Menu (' + e.target.id + ')',
				state: 'Closed (without Navigation)',
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_offcanvas_menu_closed'}));
		});

		//Offcanvas Navigation Link
		nebula.dom.document.on('click', '.offcanvas-body a', function(e){
			let thisEvent = {
				event: e,
				event_name: 'menu_click',
				event_category: 'Navigation Menu',
				event_action: 'Offcanvas Menu',
				event_label: jQuery(this).text().trimAll(),
				menu: 'Offcanvas Menu (' + e.target.id + ')',
				text: jQuery(this).text().trimAll()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_navigation_menu_click'}));

			gtag('event', 'timing_complete', {
				name: 'Navigated',
				value: Math.round(nebula.timer('(Nebula) Offcanvas Menu', 'lap')),
				menu: 'Offcanvas Menu',
				description: 'From opening offcanvas menu until navigation',
			});
		});

		//Breadcrumb Navigation
		nebula.dom.document.on('click', 'ol.nebula-breadcrumbs a', function(e){
			let thisEvent = {
				event: e,
				event_name: 'menu_click',
				event_category: 'Navigation Menu',
				event_action: 'Breadcrumbs',
				event_label: jQuery(this).text().trimAll(),
				menu: 'Breadcrumbs',
				text: jQuery(this).text().trimAll()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_navigation_menu_click'}));
		});

		//Sidebar Navigation Menu
		nebula.dom.document.on('click', '#sidebar-section ul.menu a', function(e){
			let thisEvent = {
				event: e,
				event_name: 'menu_click',
				event_category: 'Navigation Menu',
				event_action: 'Sidebar Menu',
				event_label: jQuery(this).text().trimAll(),
				menu: 'Sidebar Menu',
				text: jQuery(this).text().trimAll()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_navigation_menu_click'}));
		});

		//Footer Navigation Menu
		nebula.dom.document.on('click', '#powerfooter a', function(e){
			let thisEvent = {
				event: e,
				event_name: 'menu_click',
				event_category: 'Navigation Menu',
				event_action: 'Footer Menu',
				event_label: jQuery(this).text().trimAll(),
				menu: 'Footer Menu',
				text: jQuery(this).text().trimAll()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_navigation_menu_click'}));
		});

		//Outbound links (do not use jQuery click listener here)
		document.body.addEventListener('click', function(e){
			let $oThis = jQuery(e.target); //Convert the JS event to a jQuery object

			let linkElement = false; //Assume the element is not a link first
			if ( $oThis.is('a') ){ //If this element is an <a> tag, use it
				linkElement = $oThis;
			} else if ( $oThis.parents('a').length ){ //If the clicked element is not an <a> tag, check parent elements to an <a> tag
				linkElement = $oThis.parents('a'); //Use the parent <a> as the target element
			}

			if ( linkElement ){ //If we ended up with a link after all
				let href = linkElement.attr('href');

				if ( href ){ //href may be undefined in special circumstances so we can ignore those
					let domain = nebula?.site?.domain || window.location.hostname;

					let excludedDomain = false;
					let excludeDomains = wp.hooks.applyFilters('excludeDomains', []); //Don't log these domains/subdomains as outbound links
					jQuery.each(excludeDomains, function(index, excludeDomain){
						if ( href.includes(excludeDomain) ){
							excludedDomain = true;
						}
					});

					if ( href.includes('http') ){ //If the link contains "http"
						if ( !href.includes(domain) || href.includes('.' + domain) ){ //If the link does not contain "example.com" -or- if the link does contain a subdomain like "something.example.com"
							if ( !excludedDomain && !href.includes('//www.' + domain) ){ //Exclude the "www" subdomain and other defined excluded domains (above)
								let thisEvent = {
									event: e,
									event_name: 'nebula_outbound', //Purposefully different from the GA4 Enhanced Measurement to prevent inflated metrics
									event_category: 'Outbound Link',
									event_action: 'Click',
									event_label: href,
									outbound: true,
									subdomain: href.includes('.' + domain), //Boolean if this is a subdomain of the primary domain
									text: linkElement.text().trimAll(),
									link: href
								};

								nebula.dom.document.trigger('nebula_event', thisEvent);
								gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
								nebula.hj('event', thisEvent.event_name);
								window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_outbound_link_click'}));
							}
						}
					}
				}
			}
		}, false);

		//Nebula Cookie Notification link clicks
		nebula.dom.document.on('click', '#nebula-cookie-notification a', function(e){
			let thisEvent = {
				event: e,
				event_name: 'cookie_notification',
				event_category: 'Cookie Notification',
				event_action: 'Click',
				event_label: jQuery(this).attr('href'),
				text: jQuery(this).text().trimAll(),
				link: jQuery(this).attr('href'),
				non_interaction: true //Non-interaction because the user is not interacting with any content yet so this should not influence the bounce rate
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_cookie_notification_click'}));
		});

		//History Popstate (dynamic URL changes via the History API when "states" are pushed into the browser history) //@todo "Nebula" 0: Update this to Navigation API when it is supported
		if ( typeof history.pushState === 'function' ){
			nebula.dom.window.on('popstate', function(e){ //When a state that was previously pushed is used, or "popped". This *only* triggers when a pushed state is popped!
				let thisEvent = {
					event: e,
					event_name: 'history_popstate',
					event_category: 'History Popstate',
					event_label: document.location,
					event_action: document.title,
					location: document.location,
					state: JSON.stringify(e.state)
				};

				gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			});
		}

		//High Redirect Counts
		if ( window.performance && performance.navigation.redirectCount >= 3 ){ //If the browser redirected 3+ times
			let previousPage = nebula.session.referrer || document.referrer || '(Unknown Previous Page)';

			let thisEvent = {
				event_name: 'high_redirect_count',
				event_category: 'High Redirect Count',
				event_action: performance.navigation.redirectCount + ' Redirects',
				event_label: 'Previous Page: ' + previousPage,
				previous_page: previousPage,
				redirect_count: performance.navigation.redirectCount,
				non_interaction: true //Non-interaction because this happens on load
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_high_redirect_count'}));
			nebula.hj('event', thisEvent.event_name);
			nebula.crm('event', thisEvent.event_category);
		}

		//Dead Clicks (Non-Linked Click Attempts)
		nebula.dom.document.on('click', 'img', function(e){ //Clicks on images (Remember to never return false inside of these functions!)
			if ( !jQuery(this).parents('a, button').length && !jQuery(this).is('[data-bs-toggle]') ){ //If it is not inside of a button and not used as a Bootstrap trigger
				let thisEvent = {
					event: e,
					event_name: 'dead_click',
					event_category: 'Dead Click',
					event_action: 'Image',
					event_label: jQuery(this).attr('src'),
					type: 'Image',
					element: 'Image',
					src: jQuery(this).attr('src'),
					non_interaction: true //Non-interaction because if the user leaves due to this it should be considered a bounce
				};

				nebula.dom.document.trigger('nebula_event', thisEvent);
				gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_dead_click'}));
				nebula.crm('event', thisEvent.event_category);
			}
		});
		nebula.dom.document.on('click', function(e){ //Check for clicks on unlinked underlined text
			if ( jQuery(e.target).css('text-decoration').includes('underline') || jQuery(e.target).is('u') ){ //Do not use jQuery(this) to avoid issues with the document (depending on what was actually clicked)
				if ( !jQuery(e.target).is('a, button') && !jQuery(e.target).parents('a, button').length && !jQuery(e.target).find('a, button').length ){ //Check if this element is an <a> tag or if parents or children are
					let thisEvent = {
						event: e,
						event_name: 'dead_click',
						event_category: 'Dead Click',
						event_action: 'Underlined Text',
						event_label: jQuery(e.target).text().trimAll(),
						type: 'Underlined Text',
						element: 'Text',
						click_text: jQuery(e.target).text().trimAll(),
						non_interaction: true //Non-interaction because if the user leaves due to this it should be considered a bounce
					};

					nebula.dom.document.trigger('nebula_event', thisEvent);
					gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
					window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_dead_click'}));
					nebula.crm('event', thisEvent.event_category);
				}
			}
		});

		//Detect "Rage Clicks"
		let clickEvents = [];
		nebula.dom.document.on('click', 'body', function(e){
			//Ignore clicks on certain elements that typically incur many clicks
			if ( jQuery(this).is('input[type="number"]') ){
				return null;
			}

			clickEvents.push({
				event: e,
				time: new Date()
			});

			//Keep only required number of click events
			if ( clickEvents.length > 5 ){ //If there are more than 5 click events
				clickEvents.splice(0, clickEvents.length - 5); //Remove everything except the latest 5
			}

			//Detect 3 clicks in 5 seconds
			if ( clickEvents.length >= 5 ){
				const numberOfClicks = 5; //Number of clicks to detect within the period
				const period = 3; //The period to listen for the number of clicks

				let last = clickEvents.length - 1; //The latest click event
				let timeDiff = (clickEvents[last].time.getTime() - clickEvents[last - numberOfClicks + 1].time.getTime()) / 1000; //Time between the last click and previous click

				//Ignore event periods longer than desired
				if ( timeDiff > period ){
					return null; //Return null because false will prevent regular clicks!
				}

				//Loop through the last number of click events to check the distance between them
				let maxDistance = 0;
				for ( let i = last - numberOfClicks+1; i < last; i++ ){ //Consider for... of loop here?
					for ( let j = i+1; j <= last; j++ ){ //Consider for... of loop here?
						let distance = Math.round(Math.sqrt(Math.pow(clickEvents[i].event.clientX - clickEvents[j].event.clientX, 2) + Math.pow(clickEvents[i].event.clientY - clickEvents[j].event.clientY, 2)));
						if ( distance > maxDistance ){
							maxDistance = distance;
						}

						//Ignore if distance is outside 100px radius
						if ( distance > 100 ){
							return null; //Return null because false will prevent regular clicks!
						}
					}
				}

				//If we have not returned null by now, we have a set of rage clicks
				let thisEvent = {
					event: e,
					event_name: 'rage_clicks',
					event_category: 'Rage Clicks',
					event_action: numberOfClicks + ' clicks in ' + timeDiff + ' seconds detected within ' + maxDistance + 'px',
					event_label: nebula.domTreeToString(e.target),
					description: numberOfClicks + ' clicks in ' + timeDiff + ' seconds detected within ' + maxDistance + 'px',
					number_of_clicks: numberOfClicks,
					time_period: timeDiff,
					selector: nebula.domTreeToString(e.target),
					non_interaction: true //Non-interaction because if the user exits due to this it should be considered a bounce
				};

				nebula.dom.document.trigger('nebula_event', thisEvent);
				gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_rage_clicks'}));

				clickEvents.splice(clickEvents.length-5, 5); //Remove unused click points
			}
		});

		//Focus on Skip to Content and other screen reader links (which indicate screenreader software is being used in this session)
		nebula.dom.document.on('focus', '#skip-to-content-link, .visually-hidden, .visually-hidden-focusable', function(e){
			let thisEvent = {
				event: e,
				event_name: 'accessibility_links',
				event_category: 'Accessibility Links',
				event_action: 'Focus',
				event_label: jQuery(this).text().trimAll(),
				state: 'Focus',
				text: jQuery(this).text().trimAll(),
				non_interaction: true //Non-interaction because they are not actually taking action and these links do not indicate engagement
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.event_category, thisEvent.event_action);}
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_accessibility_link'}));
		});

		//Clicks on Skip to Content and other screen reader links (which indicate screenreader software is being used in this session)
		nebula.dom.document.on('click', '#skip-to-content-link, .visually-hidden, .visually-hidden-focusable', function(e){
			let thisEvent = {
				event: e,
				event_name: 'accessibility_links',
				event_category: 'Accessibility Links',
				event_action: 'Click',
				event_label: jQuery(this).text().trimAll(),
				state: 'Click',
				text: jQuery(this).text().trimAll(),
				non_interaction: true //Non-interaction because these links do not indicate engagement
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.event_category, thisEvent.event_action);}
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_accessibility_link'}));
		});

		//Video Enter Picture-in-Picture //https://caniuse.com/#feat=picture-in-picture
		nebula.dom.document.on('enterpictureinpicture', 'video', function(e){
			let thisEvent = {
				event: e,
				event_name: 'video_pip',
				event_category: 'Videos',
				event_action: 'Enter Picture-in-Picture',
				event_label: e.target.id,
				state: 'Enter Picture-in-Picture',
				videoID: e.target.id,
				non_interaction: true //Non-interaction because this may not be triggered by the user
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
		});

		//Video Leave Picture-in-Picture
		nebula.dom.document.on('leavepictureinpicture', 'video', function(e){
			let thisEvent = {
				event: e,
				event_name: 'video_pip',
				event_category: 'Videos',
				event_action: 'Leave Picture-in-Picture',
				event_label: e.target.id,
				state: 'Leave Picture-in-Picture',
				videoID: e.target.id,
				non_interaction: true //Non-interaction because this may not be triggered by the user
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
		});

		//Page Visibility
// 		nebula.dom.document.on('visibilitychange', function(e){
// 			let thisEvent = {
// 				event: e,
// 				event_name: 'visibility_change',
// 				event_category: 'Visibility Change',
// 				event_action: document.visibilityState,
// 				event_label: 'The state of the visibility of this page has changed.',
// 				state: document.visibilityState, //Hidden, Visible, Prerender, or Unloaded
// 				description: 'The state of the visibility of this page has changed.',
// 				non_interaction: true //Non-interaction because these are not interactions with the website itself
// 			};
//
// 			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
// 		});

		//Word copy tracking
		let copyCount = 0;
		nebula.dom.document.on('cut copy', function(e){
			//Ignore clipboard events that occur within form inputs or on Woocommerce checkout/confirmation pages
			if ( jQuery(e.target).is('input, textarea') || jQuery(e.target).parents('form').length || jQuery('body.woocommerce-checkout').length || jQuery('body.woocommerce-order-received').length ){
				return false;
			}

			let selection = window.getSelection().toString().trimAll();

			if ( selection ){
				let words = selection.split(' ');
				let wordsLength = words.length;

				//Track Email or Phone copies as contact intent.
				if ( nebula.regex.email.test(selection) ){
					let thisEvent = {
						event_name: 'mailto',
						event_category: 'Contact',
						event_action: 'Email (Copy)',
						event_label: nebula.anonymizeEmail(selection),
						type: 'Email (Copy)',
						email_address: nebula.anonymizeEmail(selection), //Mask the email with asterisks,
						words: words,
						word_count: wordsLength
					};

					gtag('set', 'user_properties', {
						contact_method : 'Email'
					});

					nebula.dom.document.trigger('nebula_event', thisEvent);
					gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
					window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_mailto'}));
					nebula.crm('event', 'Email Address Copied');
					nebula.crm('identify', {mailto_contacted: thisEvent.emailAddress});
				} else if ( nebula.regex.address.test(selection) ){
					let thisEvent = {
						event_name: 'address_copy', //Probably could be a better name
						event_category: 'Contact',
						event_action: 'Street Address (Copy)',
						event_label: selection,
						type: 'Street Address (Copy)',
						address: selection,
						words: words,
						word_count: wordsLength
					};

					nebula.dom.document.trigger('nebula_event', thisEvent);
					gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
					window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_copied_address'}));
					nebula.crm('event', 'Street Address Copied');
				} else {
					let alphanumPhone = selection.replaceAll(/\W/g, ''); //Keep only alphanumeric characters
					let firstFourNumbers = parseInt(alphanumPhone.substring(0, 4)); //Store the first four numbers as an integer

					//If the first three/four chars are numbers and the full string is either 10 or 11 characters (to capture numbers with words) -or- if it matches the phone RegEx pattern
					if ( (!isNaN(firstFourNumbers) && firstFourNumbers.toString().length >= 3 && (alphanumPhone.length === 10 || alphanumPhone.length === 11)) || nebula.regex.phone.test(selection) ){
						let thisEvent = {
							event_name: 'click_to_call',
							event_category: 'Contact',
							event_action: 'Phone (Copy)',
							event_label: selection,
							type: 'Phone (Copy)',
							phone_number: selection,
							words: words,
							word_count: wordsLength
						};

						gtag('set', 'user_properties', {
							contact_method : 'Phone'
						});

						nebula.dom.document.trigger('nebula_event', thisEvent);
						gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
						window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_click_to_call'}));
						nebula.crm('event', 'Phone Number Copied');
						nebula.crm('identify', {phone_contacted: thisEvent.phoneNumber});
					}
				}

				//Send the regular copied text event since it does not contain contact information
				let thisEvent = {
					event_name: 'copy_text',
					event_category: 'Copied Text',
					event_action: 'Copy',
					selection: selection,
					words: words,
					word_count: wordsLength
				};

				if ( selection.length > 150 ){
					thisEvent.selection = thisEvent.selection.substring(0, 150) + '...'; //Max character length for GA event is 256
				} else if ( thisEvent.word_count >= 10 ){
					thisEvent.words = thisEvent.words.slice(0, 10).join(' ') + '... [' + thisEvent.word_count + ' Words]';
				} else if ( selection.trimAll() === '' ){
					thisEvent.words = '[0 words]';
				}
				if ( thisEvent.words.length > 150 ){
					thisEvent.words = thisEvent.words.substring(0, 150) + '...'; //Max character length for GA event is 256
				}

				nebula.dom.document.trigger('nebula_event', thisEvent);

				if ( copyCount < 5 ){ //If fewer than 5 copies have happened in this page view
					thisEvent.label = thisEvent.words;

					gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
					window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_copied_text'}));
					nebula.crm('event', 'Text Copied');
				}

				copyCount++;
			}
		});

		//AJAX Errors
		nebula.dom.document.ajaxError(function(e, jqXHR, settings, thrownError){
			let errorMessage = thrownError;
			if ( jqXHR.status === 0 ){ //A status of 0 means the error is unknown. Possible network connection issue (like a blocked request).
				errorMessage = 'Unknown error';
			}

			gtag('event', 'exception', {
				xhr_status: jqXHR.status,
				message: errorMessage,
				url: settings.url,
				description: '(JS) AJAX Error (' + jqXHR.status + '): ' + errorMessage + ' on ' + settings.url,
				fatal: true
			});
			window.dataLayer.push({'event': 'nebula_ajax_error', 'error': errorMessage});
			nebula.hj('event', thisEvent.event_name);
			nebula.crm('event', 'AJAX Error');
		});

		//Note: Window errors are detected in usage.js for better visibility

		//Reporting Observer deprecations and interventions
// 		try {
// 			if ( 'ReportingObserver' in window ){ //Chrome 68+
// 				let nebulaReportingObserver = new ReportingObserver(function(reports, observer){
// 					for ( let report of reports ){
// 						if ( report?.body?.sourceFile && !['extension', 'about:blank'].some((item) => report.body.sourceFile.includes(item)) ){ //Ignore certain files
// 							gtag('event', 'exception', {
// 								report_type: report.type,
// 								message: report.body.message,
// 								source_file: report.body.sourceFile,
// 								line_number: report.body.lineNumber,
// 								description: '(JS) Reporting Observer [' + report.type + ']: ' + report.body.message + ' in ' + report.body.sourceFile + ' on line ' + report.body.lineNumber,
// 								fatal: false
// 							});
// 						}
// 					}
// 				}, {buffered: true}); //Buffer to capture reports that happened prior to the observer being created
//
// 				nebulaReportingObserver.observe();
// 			}
// 		} catch {
// 			//Ignore errors
// 		}

		//Content Editable element changes
		nebula.dom.document.on('focus', '[contenteditable]', function(){
			if ( !jQuery(this).is('[data-original-text]') ){ //If it does not already have this attribute (only want to capture the first focus)
				jQuery(this).attr('data-original-text', jQuery(this).text()); //Store the original text for content editable elements
			}
		});
		nebula.dom.document.on('blur', '[contenteditable]', function(){
			if ( jQuery(this).attr('data-original-text') && jQuery(this).text() !== jQuery(this).attr('data-original-text') ){
				let thisEvent = {
					event_name: 'contenteditable',
					event_category: 'Content Editable',
					event_action: 'Text Changed',
					event_label: '"' + jQuery(this).attr('data-original-text') + '" changed to: ' + jQuery(this).text(),
					original_text: jQuery(this).attr('data-original-text'),
					new_text: jQuery(this).text()
				};

				nebula.dom.document.trigger('nebula_event', thisEvent);
				gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_print'}));
				if ( typeof clarity === 'function' ){clarity('set', thisEvent.event_category, thisEvent.event_action);}
				nebula.crm('event', thisEvent.event_category);
			}
		});

		//Capture Print Intent
		function sendPrintEvent(action, trigger){
			let thisEvent = {
				event_name: 'print',
				event_category: 'Category',
				event_label: 'User triggered print via ' + trigger,
				event_action: action,
				description: 'User triggered print via ' + trigger,
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_print'}));
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.event_category, thisEvent.event_action);}
			nebula.hj('event', thisEvent.event_name);
			nebula.crm('event', thisEvent.event_category);
		}

		//Note: This sends 2 events per print (beforeprint and afterprint). If one occurs more than the other we can remove one.
		window.matchMedia('print').addListener(function(mql){
			if ( mql.matches ){
				sendPrintEvent('Before Print', 'mql.matches');
			} else {
				sendPrintEvent('After Print', '!mql.matches');
			}
		});

		//DataTables Filter
		nebula.dom.document.on('keyup', '.dataTables_filter input', function(e){
			let $oThis = jQuery(this);
			let thisEvent = {
				event: e,
				event_name: 'datatables_filter',
				event_category: 'Datatables',
				event_label: $oThis.val().toLowerCase().trimAll(),
				event_action: 'Search Filter',
				query: $oThis.val().toLowerCase().trimAll()
			};

			nebula.debounce(function(){
				nebula.dom.document.trigger('nebula_event', thisEvent);
				gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_datatables'}));
			}, 1000, 'datatables_search_filter');
		});

		//DataTables Sorting
		nebula.dom.document.on('click', 'th.sorting', function(e){
			let thisEvent = {
				event: e,
				event_name: 'datatables_sort',
				event_category: 'DataTables',
				event_label: jQuery(this).text(),
				event_action: 'Sort',
				heading: jQuery(this).text()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_datatables'}));
		});

		//DataTables Pagination
		nebula.dom.document.on('click', 'a.paginate_button ', function(e){
			let thisEvent = {
				event: e,
				event_name: 'datatables_paginate',
				event_category: 'DataTables',
				event_label: jQuery(this).text(),
				event_action: 'Paginate',
				page: jQuery(this).text()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_datatables'}));
		});

		//DataTables Show Entries
		nebula.dom.document.on('change', '.dataTables_length select', function(e){
			let thisEvent = {
				event: e,
				event_name: 'datatables_length',
				event_category: 'DataTables',
				event_action: 'Shown Entries Change',
				event_label: jQuery(this).val(),
				event_action: 'Shown Entries Change', //Number of visible rows select dropdown
				selected: jQuery(this).val()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_datatables'}));
		});

		nebula.scrollDepth();
		nebula.ecommerceTracking();
	}, 'nebula event tracking');
};

//Ecommerce event tracking
//Note: These supplement the server-side event tracking
nebula.ecommerceTracking = async function(){
	if ( nebula.site?.ecommerce ){
		//Note the following only work when the add to cart with AJAX Woocommerce setting is enabled
		// //Add to Cart
		// nebula.dom.body.on('adding_to_cart', function(button, data){
		// 	//Do stuff
		// });
		// //Remove from Cart
		// nebula.dom.body.on('removed_from_cart', function(response, cart_hash, button){
		// 	//Do Stuff
		// });

		//Add to Cart clicks
		nebula.dom.document.on('pointerdown', 'button[name="add-to-cart"], a.add_to_cart, .single_add_to_cart_button', function(e){ //@todo "Nebula" 0: is there a trigger from WooCommerce this can listen for?
			let thisEvent = {
				event: e,
				event_name: 'add_to_cart',
				event_category: 'Ecommerce',
				event_action: 'Add to Cart',
				item_id: jQuery(this).attr('value') || nebula.post.id,
				event_label: jQuery(this).attr('data-product_id') || nebula.post.id,
				product: jQuery(this).attr('data-product_id') || nebula.post.id
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_add_to_cart'}));
			nebula.fbq('track', 'AddToCart');
			nebula.clarity('set', thisEvent.event_category, thisEvent.event_action);
			nebula.hj('event', thisEvent.event_name);
			nebula.crm('event', 'Ecommerce Add to Cart');
		});

		//Update cart clicks
		nebula.dom.document.on('pointerdown', 'button[name="update_cart"]', function(e){
			let thisEvent = {
				event: e,
				event_name: 'update_cart',
				event_category: 'Ecommerce',
				event_action: 'Update Cart Button',
				event_label: 'Update Cart button click'
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_update_cart'}));
			nebula.hj('event', thisEvent.event_name);
			nebula.crm('event', 'Ecommerce Update Cart');
		});

		//Product Remove buttons
		nebula.dom.document.on('pointerdown', '.product-remove a.remove', function(e){
			let thisEvent = {
				event: e,
				event_name: 'remove_from_cart',
				event_category: 'Ecommerce',
				event_action: 'Remove This Item',
				event_label: jQuery(this).attr('data-product_id'),
				item_id: jQuery(this).attr('data-product_id'),
				product: jQuery(this).attr('data-product_id')
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_remove_item'}));
			nebula.hj('event', thisEvent.event_name);
			nebula.crm('event', 'Ecommerce Remove From Cart');
		});

		//Proceed to Checkout
		nebula.dom.document.on('pointerdown', '.wc-proceed-to-checkout .checkout-button', function(e){
			let thisEvent = {
				event: e,
				event_name: 'begin_checkout',
				event_category: 'Ecommerce',
				event_action: 'Proceed to Checkout Button',
				event_label: 'Proceed to Checkout button click'
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_proceed_to_checkout'}));
			nebula.fbq('track', 'InitiateCheckout');
			nebula.clarity('set', thisEvent.event_category, thisEvent.event_action);
			nebula.hj('event', thisEvent.event_name);
			nebula.crm('event', 'Ecommerce Proceed to Checkout');
		});

		//Checkout form timing
		nebula.dom.document.on('click focus', '#billing_first_name', function(e){
			nebula.timer('(Nebula) Ecommerce Checkout', 'start');

			let thisEvent = {
				event: e,
				event_name: 'checkout_progress',
				event_category: 'Ecommerce',
				event_action: 'Started Checkout Form',
				event_label: 'Began filling out the checkout form (Billing First Name)'
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_started_checkout_form'}));
			nebula.hj('event', thisEvent.event_name);
			nebula.crm('event', 'Ecommerce Started Checkout Form');
		});

		//Place order button
		nebula.dom.document.on('pointerdown', '#place_order', function(e){
			let thisEvent = {
				event: e,
				event_name: 'purchase', //@todo "Nebula" 0: If/when adding a plugin for tracking purchases in GA4, rename this event to "place_order_click"
				event_category: 'Ecommerce',
				event_action: 'Place Order Button',
				event_label: 'Place Order button click'
				//@todo "Nebula" 0: Somehow detect pricing information for revenue reports...? or do it elsewhere... maybe even a WP plugin?
			};

			gtag('event', 'timing_complete', {
				name: 'Checkout Form',
				value: Math.round(nebula.timer('(Nebula) Ecommerce Checkout', 'end')),
				event_category: 'Ecommerce',
				event_label: 'Billing details start to Place Order button click',
			});

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula_place_order_button'}));
			nebula.fbq('track', 'Purchase');
			nebula.clarity('set', thisEvent.event_category, thisEvent.event_action);
			nebula.hj('event', thisEvent.event_name);
			nebula.crm('event', 'Ecommerce Placed Order');
			nebula.crm('identify', {hs_lifecyclestage_customer_date: 1}); //@todo "Nebula" 0: What kind of date format does Hubspot expect here?
		});
	}
};

//Detect scroll depth
//Note: This is a default GA4 event and is not needed to be tracked in Nebula. Consider deleting entirely.
nebula.scrollDepth = async function(){
// 	if ( window.performance ){ //Safari 11+
// 		let scrollReady = performance.now();
// 		let reachedBottom = false; //Flag for optimization after detection is finished
// 		let excessiveScrolling = false; //Flag for optimization after detection is finished
// 		let lastScrollCheckpoint = nebula.dom.window.scrollTop(); //Set a checkpoint of the current scroll distance to subtract against later
// 		let totalScrollDistance = 0; //Down and up distance
// 		let excessiveScrollThreshold = nebula.dom.document.height()*2; //Set the threshold for an excessive scroll distance
//
// 		nebula.maxScrollDepth = 0; //This needs to be accessed from multiple other functions later
// 		nebula.updateMaxScrollDepth(); //Update it first right away on load (the rest will be throttled)
//
// 		let scrollDepthHandler = function(){
// 			//Only check for initial scroll once
// 			nebula.once(function(){
// 				nebula.scrollBegin = performance.now()-scrollReady; //Calculate when the first scroll happens
// 				if ( nebula.scrollBegin > 250 ){ //Try to avoid autoscrolls on pageload
// 					let thisEvent = {
// 						event_name: 'scroll',
// 						event_category: 'Scroll Depth',
// 						event_action: 'Began Scrolling',
// 						event_label: nebula.dom.window.scrollTop() + 'px',
// 						scroll_start: nebula.dom.window.scrollTop() + 'px',
// 						time_before_scroll_start: Math.round(nebula.scrollBegin),
// 						non_interaction: true
// 					};
// 					thisEvent.event_label = 'Initial scroll started at ' + thisEvent.scroll_start;
// 					nebula.dom.document.trigger('nebula_event', thisEvent);
// 					gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
// 				}
// 			}, 'begin scrolling');
//
// 			//Check scroll distance periodically
// 			nebula.throttle(function(){
// 				//Total Scroll Distance
// 				if ( !excessiveScrolling ){
// 					totalScrollDistance += Math.abs(nebula.dom.window.scrollTop()-lastScrollCheckpoint); //Increase the total scroll distance (always positive regardless of scroll direction)
// 					lastScrollCheckpoint = nebula.dom.window.scrollTop(); //Update the checkpoint
// 					if ( totalScrollDistance >= excessiveScrollThreshold ){
// 						excessiveScrolling = true; //Set to true to disable excessive scroll tracking after it is detected
//
// 						nebula.once(function(){
// 							let thisEvent = {
// 								event_name: 'excessive_scrolling',
// 								event_category: 'Scroll Depth',
// 								event_action: 'Excessive Scrolling',
// 								event_label: 'User scrolled ' + excessiveScrollThreshold + 'px (or more) on this page.',
// 								description: 'User scrolled ' + excessiveScrollThreshold + 'px (or more) on this page.',
// 								non_interaction: true
// 							};
// 							nebula.dom.document.trigger('nebula_event', thisEvent);
// 							gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
// 						}, 'excessive scrolling');
// 					}
// 				}
//
// 				nebula.updateMaxScrollDepth();
//
// 				//When user reaches the bottom of the page
// 				if ( !reachedBottom ){
// 					if ( (nebula.dom.window.height()+nebula.dom.window.scrollTop()) >= nebula.dom.document.height() ){ //If user has reached the bottom of the page
// 						reachedBottom = true;
//
// 						nebula.once(function(){
// 							let thisEvent = {
// 								event_name: 'scroll',
// 								event_category: 'Scroll Depth',
// 								event_action: 'Entire Page',
// 								event_label: nebula.dom.document.height(),
// 								distance: nebula.dom.document.height(),
// 								scroll_end: performance.now()-(nebula.scrollBegin+scrollReady),
// 								non_interaction: true
// 							};
//
// 							thisEvent.timetoScrollEnd = Math.round(thisEvent.scrollEnd);
//
// 							nebula.dom.document.trigger('nebula_event', thisEvent);
// 							gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
// 							window.removeEventListener('scroll', scrollDepthHandler);
// 						}, 'end scrolling');
// 					}
// 				}
//
// 				//Stop listening to scroll after no longer necessary
// 				if ( reachedBottom && excessiveScrolling ){
// 					window.removeEventListener('scroll', scrollDepthHandler); //Stop watching scrolling– no longer needed if all detections are true
// 				}
// 			}, 1000, 'scroll depth');
// 		};
//
// 		window.addEventListener('scroll', scrollDepthHandler); //Watch for scrolling ("scroll" is passive by default)
//
// 		//Track when the user reaches the end of the content
// 		if ( jQuery('#footer-section').length ){
// 			let footerObserver = new IntersectionObserver(function(entries){
// 				entries.forEach(function(entry){
// 					if ( entry.intersectionRatio > 0 ){
// 						let thisEvent = {
// 							event_name: 'scroll',
// 							event_category: 'Scroll Depth',
// 							event_action: 'Reached Footer',
// 							event_label: 'The footer of the page scrolled into the viewport',
//							description: 'The footer of the page scrolled into the viewport',
// 							non_interaction: true
// 						};
//
// 						nebula.dom.document.trigger('nebula_event', thisEvent);
// 						gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
//
// 						nebula.updateMaxScrollDepth();
// 						footerObserver.unobserve(entry.target); //Stop observing the element
// 					}
// 				});
// 			}, {
// 				rootMargin: '0px', //0px uses the actual viewport bounds, 100% is double the viewport
// 				threshold: 0.1 //How much of the element needs to be in view before this is triggered (this is a percentage between 0 and 1)
// 			});
//
// 			//Observe the pre-footer section (or whatever element is after the main content area)
// 			let preFooterSelector = wp.hooks.applyFilters('nebulaPreFooterSelector', '#footer-section'); //This should be the first section after the "content"
// 			footerObserver.observe(jQuery(preFooterSelector)[0]); //Observe the element
// 		}
// 	}
};

//Track campaigns that attributed to returning visitor conversions
nebula.attributionTracking = function(){
	if ( nebula.site.options.attribution_tracking ){ //If the Nebula Option is enabled
		if ( nebula.isDoNotTrack() ){
			return false;
		}

		//Check if relevant query parameters exist in the URL
		//This overwrites anytime there is a UTM tag, so it would be considered "last-non-organic" attribution
		const queryParams = new URLSearchParams(window.location.search);

		if ( queryParams.has('utm_source') ){ //Check for the only required UTM tag (since .has() cannot do partial matches)
			//Loop through the query string to capture just the UTM parameters
			let utmParameters = {}; //Prep an object to fill
			for ( const [key, value] of queryParams.entries() ){
				if ( key.includes('utm_') ){ //If this key is a UTM parameter
					utmParameters[key] = value;
				}
			}

			nebula.createCookie('attribution', JSON.stringify(utmParameters)); //Store the UTM parameters in a cookie
		} else if ( !nebula.readCookie('attribution') ){ //If no UTMs and the cookie does not already exist, check for other notable tracking parameters
			let trackingParameters = {}; //Prep an object to fill

			//Loop through notable tracking parameters to store in the attribution cookie
			let notableQueryParameters = {
				google_ads_click: 'gclid', //Google Ads Click ID
				google_ads_source: 'gclsrc', //Google Ads Click Source
				google_ads_gbraid: 'gbraid',
				google_ads_wbraid: 'wbraid',
				doubleclick: 'dclid', //DoubleClick Click ID (typically offline tracking)
				facebook: 'fbclid',
				linkedin: 'li_',
				hubspot: 'hsa_',
				mailchimp: 'mc_eid',
				vero: 'vero_id',
				marketo: 'mkt_tok'
			};

			jQuery.each(notableQueryParameters, function(platform, parameter){
				if ( queryParams.has(parameter) ){ //If this parameter exists
					trackingParameters[parameter] = queryParams.get(parameter); //Store it in the object
				}
			});

			if ( trackingParameters ){ //If we ended up with a non-empty object
				nebula.createCookie('attribution', JSON.stringify(trackingParameters)); //Store the other notable parameters in a cookie
			}
		}

		//Now check if the cookie exists
		if ( nebula.readCookie('attribution') && jQuery('input.attribution').length ){ //If our attribution cookie exists and we have an input to use
			jQuery('input.attribution').val(nebula.readCookie('attribution')); //Fill the designated form field(s)
		}
	}
};

//Update the max scroll depth
nebula.updateMaxScrollDepth = function(){
	if ( nebula.maxScrollDepth < 100 ){
		if ( nebula.dom.window.scrollTop() > nebula.maxScrollDepth ){
			nebula.maxScrollDepth = nebula.dom.window.scrollTop();
		}
	}
};

//Facebook conversion tracking
nebula.fbq = function(type='track', eventName='', parameters={}){
	if ( typeof fbq === 'function' ){
		fbq(type, eventName, parameters);
	}
};

//Microsoft Clarity tracking
nebula.clarity = function(type='set', key='', value=''){
	if ( typeof clarity === 'function' ){
		clarity(type, key, value);
	}
};

//Hotjar tracking
//https://help.hotjar.com/hc/en-us/articles/4412561401111
nebula.hj = function(type='event', value=''){
	if ( typeof hj === 'function' && value ){
		hj(type, value);
	}
};

//Send data to the CRM
nebula.crm = async function(action, data, sendNow = true){
	if ( nebula.isDoNotTrack() ){
		return false;
	}

	if ( typeof _hsq === 'undefined' ){
		return false;
	}

	if ( !action || !data || typeof data == 'function' ){
		nebula.help('Action and Data Object are both required in nebula.crm().', '/functions/crm/');
		return false; //Action and Data are both required.
	}

	if ( action === 'identify' ){
		_hsq.push(['identify', data]);

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
			if ( !nebula.user.known && nebula.regex.email.test(data.email) ){
				nebula.dom.document.trigger('nebula_crm_identification', {email: nebula.regex.email.test(data.email), data: data});
				gtag('event', 'crm_contact_identified', {
					event_category: 'CRM',
					event_label: "A contact's email address in the CRM has been identified.",
					non_interaction: true
				});
				nebula.user.known = true;
			}
		} else {
			nebula.dom.document.trigger('nebula_crm_details', {data: data});
			gtag('event', 'crm_supporting_information', {
				event_category: 'CRM',
				event_label: 'Information associated with this user has been identified.',
				non_interaction: true
			});
		}
	}

	if ( action === 'event' ){
		//Hubspot events are only available with an Enterprise Marketing subscription
		//Refer to this documentation for event names and IDs: https://developers.hubspot.com/docs/methods/tracking_code_api/tracking_code_overview#idsandnames
		_hsq.push(['trackEvent', data]);

		_hsq.push(['setPath', window.location.href.replace(nebula.site.directory.root, '') + '#virtual-pageview/' + data]);
		let oldTitle = document.title;
		document.title += ' (Virtual)'; //Append to the title
		_hsq.push(['trackPageView']);
		document.title = oldTitle;
	}

	nebula.dom.document.trigger('crm_data', data);
};

//Easily send form data to nebula.crm() with crm-* classes
//Add a class to the input field with the category to use. Ex: crm-firstname or crm-email or crm-fullname
//Call this function before sending a gtm() event because it sets dimensions too
nebula.crmForm = async function(formID){
	let crmFormObj = {};

	if ( formID ){
		crmFormObj.form_contacted = 'CF7 (' + formID + ') Submit Attempt'; //This is triggered on submission attempt, so it may capture abandoned forms due to validation errors.
	}

	jQuery('form [class*="crm-"]').each(function(){
		if ( jQuery(this).val().trimAll().length ){
			if ( jQuery(this).attr('class').includes('crm-notable_poi') ){
				nebula.setDimension('notable_poi', jQuery('.notable-poi').val());
			}

			let cat = (/crm-([a-z\_]+)/g).exec(jQuery(this).attr('class'));
			if ( cat ){
				let thisCat = cat[1];
				crmFormObj[thisCat] = jQuery(this).val();
			}
		}
	});

	if ( Object.keys(crmFormObj).length ){
		nebula.crm('identify', crmFormObj);
	}
};