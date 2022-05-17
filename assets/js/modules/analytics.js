window.performance.mark('(Nebula) Inside analytics.js (module)');

//Generate a unique ID for hits and windows (used in /inc/analytics.php)
export function uuid(a){ //Does not technically need to be exported anymore as it is only now used here in this file
	return a ? (a^Math.random()*16 >> a/4).toString(16) : ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, uuid);
}

//Get local time string with timezone offset (used in /inc/analytics.php)
export function localTimestamp(){ //Does not technically need to be exported anymore as it is only now used here in this file
	var now = new Date();
	var tzo = -now.getTimezoneOffset();
	var dif = ( tzo >= 0 )? '+' : '-';
	var pad = function(num){
		var norm = Math.abs(Math.floor(num));
		return (( norm < 10 )? '0' : '') + norm;
	};

	return Math.round(now/1000) + ' (' + now.getFullYear() + '-' + pad(now.getMonth()+1) + '-' + pad(now.getDate()) + ' ' + pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds()) + '.' + pad(now.getMilliseconds()) + ' UTC' + dif + pad(tzo/60) + ':' + pad(tzo%60) + ')';
}

//Set a custom dimension in both GA and MS Clarity (used in /inc/analytics.php)
export function setDimension(name, value){ //Does not technically need to be exported anymore as it is only now used here in this file
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
}

//Prep an object of dimensions that can be included in any subsequent event sends to GA
nebula.allHitDimensions = function(){
	let dimensions = {};

	dimensions.query_string = window.location.search;
	dimensions.network_connection = ( navigator.onLine )? 'Online' : 'Offline';
	dimensions.visibility_state = document.visibilityState;
	dimensions.local_timestamp = localTimestamp();
	dimensions.hit_time = String(new Date);
	dimensions.hit_id = uuid(); //Give each hit a unique ID

	//Bootstrap Breakpoint
	if ( window.matchMedia("(min-width: 2048px)").matches ){
		dimensions.mq_breakpoint = 'uw';
	} else if ( window.matchMedia("(min-width: 1400px)").matches ){
		dimensions.mq_breakpoint = 'xxl';
	} else if ( window.matchMedia("(min-width: 1200px)").matches ){
		dimensions.mq_breakpoint = 'xl';
	} else if ( window.matchMedia("(min-width: 992px)").matches ){
		dimensions.mq_breakpoint = 'lg';
	} else if ( window.matchMedia("(min-width: 768px)").matches ){
		dimensions.mq_breakpoint = 'md';
	} else if ( window.matchMedia("(min-width: 544px)").matches ){
		dimensions.mq_breakpoint = 'sm';
	} else {
		dimensions.mq_breakpoint = 'sm';
	}

	//Screen Resolution
	if ( window.matchMedia("(min-resolution: 192dpi)").matches ){
		dimensions.screen_resolution = '2x';
	} else if ( window.matchMedia("(min-resolution: 144dpi)").matches ){
		dimensions.screen_resolution = '1.5x';
	} else {
		dimensions.screen_resolution = '1x';
	}

	//Screen Orientation
	if ( window.matchMedia("(orientation: portrait)").matches ){
		dimensions.screen_orientation = 'Portrait';
	} else if ( window.matchMedia("(orientation: landscape)").matches ){
		dimensions.screen_orientation = 'Landscape';
	}

	//Device Memory
	if ( 'deviceMemory' in navigator ){ //Chrome 64+
		let deviceMemoryLevel = ( navigator.deviceMemory < 1 )? 'Lite' : 'Full';
		dimensions.device_memory = navigator.deviceMemory + '(' + deviceMemoryLevel + ')';
	} else {
		dimensions.device_memory = 'Unavailable';
	}

	return dimensions;
};

//Prep an event object to send to Google Analytics
nebula.gaEventObject = function(eventObject){
	delete eventObject['e']; //Remove the DOM Event key
	delete eventObject['event']; //Remove the DOM Event key
	delete eventObject['event_name']; //Name is sent separately outside of the object parameter, so remove it here
	delete eventObject['email_address'];

	for ( var key of Object.keys(eventObject) ){
		if ( typeof eventObject[key] === 'object' || typeof eventObject[key] === 'function' ){
			delete eventObject[key]; //Remove any objects or functions
		}
	}

	//Add contextual parameters to the event object
	let fullContextObject = Object.assign(nebula.allHitDimensions(), eventObject);

	return fullContextObject;
};

//Google Analytics Universal Analytics Event Trackers
nebula.eventTracking = async function(){
	if ( nebula.isDoNotTrack() ){
		return false;
	}

	nebula.cacheSelectors(); //Just to be safe (this is no longer called from anywhere besides nebula.js so this should never be needed)

	//Check for Topics API support @todo "Nebula" 0: when it is better supported update this further
	if ( 'browsingTopics' in document && document.featurePolicy.allowsFeature('browsing-topics') ){
		//console.log('Topics API is available on this page', document.browsingTopics());

		gtag('event', 'browser_navigation', {
			event_category: 'Topics API',
			event_action: 'Available',
			non_interaction: true
		});
	}

	nebula.once(function(){
		window.dataLayer = window.dataLayer || []; //Prevent overwriting an existing GTM Data Layer array

		nebula.dom.document.trigger('nebula_event_tracking');

		if ( nebula?.user?.cid ){
			window.dataLayer.push(Object.assign({'client-id': nebula.user.cid}));
		} else if ( nebula?.analytics?.measurementID && typeof window.gtag === 'function' ){
			gtag('get', nebula.analytics.measurementID, 'client_id', function(clientId){
				nebula.user.id = clientId;
				window.dataLayer.push(Object.assign({'client-id': clientId}));
			});
		}

		//When the page is restored from BFCache (which means it is not fully reloaded)
		window.addEventListener('pageshow', function(event){
			if ( event.persisted === true ){
				gtag('event', 'page_view'); //Send another pageview if the page is restored from bfcache
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
				event_label: document.location.pathname,
				non_interaction: true
			});
		}

		//Prep page info and detect quick unloads
		if ( 'localStorage' in window ){
			let prev = {
				'path': document.location.pathname, //Prep the "previous page" to this page for future use.
				'quick': true //Set this to true initially until it is not longer considered a quick back
			};

			localStorage.setItem('prev', JSON.stringify(prev)); //Store them in localstorage

			//After 4 seconds change quick to false so it is no longer considered a quick back
			setTimeout(function(){
				prev.quick = false;
				localStorage.setItem('prev', JSON.stringify(prev));
			}, 4000);
		}

		//When the page becomes frozen/unfrozen by the browser Lifecycle API
		document.addEventListener('freeze', function(event){
			gtag('event', 'page_lifecycle_frozen', { //Note that "frozen" does not indicate an error. The browser has preserved its state as inactive.
				event_category: 'Page Lifecycle',
				event_action: 'Frozen',
				non_interaction: true
			});
		});
		document.addEventListener('resume', function(event){
			gtag('event', 'page_lifecycle_resumed', { //This may happen when it is unfrozen from a frozen state, or from BFCache.
				event_category: 'Page Lifecycle',
				event_action: 'Resumed',
				non_interaction: true
			});
		});

		//Button Clicks
		let nebulaButtonSelector = wp.hooks.applyFilters('nebulaButtonSelectors', 'button, .button, .btn, [role="button"], a.wp-block-button__link, .hs-button'); //Allow child theme or plugins to add button selectors without needing to override/duplicate this function
		nebula.dom.document.on('mousedown', nebulaButtonSelector, function(e){
			let thisEvent = {
				event: e,
				event_name: 'button_click',
				event_category: 'Button',
				event_action: 'Click',
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				text: jQuery(this).val() || jQuery(this).attr('value') || jQuery(this).text() || jQuery(this).attr('title') || '(Unknown)',
				link: jQuery(this).attr('href') || jQuery(this).attr('title') || '(Unknown)'
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-button-click'}));
		});

		//Linked Image Clicks
		nebula.dom.document.on('click', 'a img', function(e){
			let thisEvent = {
				event: e,
				event_name: 'image_click',
				event_category: 'Image Click',
				event_action: jQuery(this).attr('alt') || jQuery(this).attr('src'),
				event_label: jQuery(this).parents('a').attr('href')
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-image-click'}));
		});

		//Bootstrap "Collapse" Accordions
		nebula.dom.document.on('shown.bs.collapse', function(e){
			let thisEvent = {
				event: e,
				event_name: 'accordion_toggle',
				event_category: 'Accordion',
				event_action: 'Shown',
				event_label: jQuery('[data-bs-target="#' + e.target.id + '"]').text().trim() || e.target.id,
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-accordion-shown'}));
		});
		nebula.dom.document.on('hidden.bs.collapse', function(e){
			let thisEvent = {
				event: e,
				event_name: 'accordion_toggle',
				event_category: 'Accordion',
				event_action: 'Hidden',
				event_label: jQuery('[data-bs-target="#' + e.target.id + '"]').text().trim() || e.target.id,
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-accordion-hidden'}));
		});

		//Bootstrap Modals
		nebula.dom.document.on('shown.bs.modal', function(e){
			let thisEvent = {
				event: e,
				event_name: 'modal_toggle',
				event_category: 'Modal',
				event_action: 'Shown',
				event_label: jQuery('#' + e.target.id + ' .modal-title').text().trim() || e.target.id,
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-modal-shown'}));
		});
		nebula.dom.document.on('hidden.bs.modal', function(e){
			let thisEvent = {
				event: e,
				event_name: 'modal_toggle',
				event_category: 'Modal',
				event_action: 'Hidden',
				event_label: jQuery('#' + e.target.id + ' .modal-title').text().trim() || e.target.id,
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-modal-hidden'}));
		});

		//Bootstrap Carousels (Sliders)
		nebula.dom.document.on('slide.bs.carousel', function(e){
			if ( window.event ){ //Only if sliding manually
				let thisEvent = {
					event: e,
					event_name: 'carousel_slide',
					event_category: 'Carousel',
					event_action: e.target.id || e.target.title || e.target.className.replaceAll(/\s/g, '.'),
					from: e.from,
					to: e.to,
				};

				thisEvent.activeSlide = jQuery(e.target).find('.carousel-item').eq(e.to);
				thisEvent.activeSlideName = thisEvent.activeSlide.attr('id') || thisEvent.activeSlide.attr('title') || 'Unnamed';
				thisEvent.prevSlide = jQuery(e.target).find('.carousel-item').eq(e.from);
				thisEvent.prevSlideName = thisEvent.prevSlide.attr('id') || thisEvent.prevSlide.attr('title') || 'Unnamed';
				thisEvent.event_label = 'Slide to ' + thisEvent.to + ' (' + thisEvent.activeSlideName + ') from ' + thisEvent.from + ' (' + thisEvent.prevSlideName + ')';

				nebula.dom.document.trigger('nebula_event', thisEvent);
				gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-carousel-slide'}));
			}
		});

		//Generic Form Submissions
		//This event will be a duplicate if proper event tracking is setup on each form, but serves as a safety net.
		//It is not recommended to use this event for goal tracking unless absolutely necessary (this event does not check for submission success)!
		nebula.dom.document.on('submit', 'form', function(e){
			let thisEvent = {
				event: e,
				event_name: 'form_submit', //How to differentiate this from conversions?
				event_category: 'Generic Form',
				event_action: 'Submit',
				formID: e.target.id || 'form.' + e.target.className.replaceAll(/\s/g, '.'),
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.event_category, thisEvent.event_action);}
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-generic-form'}));
		});

		//Notable File Downloads
		let notableFileExtensions = wp.hooks.applyFilters('nebulaNotableFiles', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv', 'zip', 'zipx', 'rar', 'gz', 'tar', 'txt', 'rtf', 'ics', 'vcard']);
		jQuery.each(notableFileExtensions, function(index, extension){
			jQuery("a[href$='." + extension + "' i]").on('mousedown', function(e){ //Cannot defer case insensitive attribute selectors in jQuery (or else you will get an "unrecognized expression" error)
				let thisEvent = {
					event: e,
					event_name: 'file_download', //Note: This is a default GA4 event and is not needed to be tracked in Nebula. Consider deleting entirely.
					event_category: 'Download',
					event_action: extension,
					intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
					file_extension: extension,
					file_name: jQuery(this).attr('href').substr(jQuery(this).attr('href').lastIndexOf('/')+1),
				};

				nebula.dom.document.trigger('nebula_event', thisEvent);
				gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-download'}));
				nebula.fbq('track', 'ViewContent', {content_name: thisEvent.file_name});
				nebula.clarity('set', thisEvent.event_category, thisEvent.file_name);
				nebula.crm('event', 'File Download');
			});
		});

		//Notable Downloads
		nebula.dom.document.on('mousedown', '.notable a, a.notable', function(e){
			let thisEvent = {
				event: e,
				event_name: 'file_download',
				event_category: 'Download',
				event_action: 'Notable',
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				file_path: jQuery(this).attr('href').trim(),
				link_text: jQuery(this).text()
			};

			if ( thisEvent.file_path.length && thisEvent.file_path !== '#' ){
				thisEvent.file_name = file_path.substr(file_path.lastIndexOf('/')+1);
				nebula.dom.document.trigger('nebula_event', thisEvent);
				gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-download'}));
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
				event_action: 'Submit',
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				query: jQuery(this).find('input[name="s"]').val().toLowerCase().trim()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-internal-search'}));
			nebula.fbq('track', 'Search', {search_string: thisEvent.query});
			nebula.clarity('set', thisEvent.event_category, thisEvent.query);
			nebula.crm('identify', {internal_search: thisEvent.query});
		});

		//Suggested pages on 404 results
		nebula.dom.document.on('mousedown', 'a.internal-suggestion', function(e){
			let thisEvent = {
				event: e,
				event_name: 'select_content',
				event_category: 'Page Suggestion',
				event_action: 'Internal',
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				suggestion: jQuery(this).text(),
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
					modified_zoom_level: modifiedZoomLevel,
					non_interaction: true
				};

				thisEvent.event_label = 'Modified Zoom Level: ' + thisEvent.modified_zoom_level;

				nebula.dom.document.trigger('nebula_event', thisEvent);
				gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-keyboard-shortcut'}));
			}

			//Ctrl- (Zoom Out)
			if ( (e.ctrlKey || e.metaKey) && (e.keyCode === 189 || e.keyCode === 109) ){ //189 is minus, 109 is minus on the numpad
				modifiedZoomLevel--; //Decrement the zoom level iterator

				let thisEvent = {
					event: e,
					event_name: 'zoom_change',
					event_category: 'Keyboard Shortcut',
					event_action: 'Zoom Out (Ctrl-)',
					modified_zoom_level: modifiedZoomLevel,
					non_interaction: true
				};

				thisEvent.event_label = 'Modified Zoom Level: ' + thisEvent.modified_zoom_level;

				nebula.dom.document.trigger('nebula_event', thisEvent);
				gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-keyboard-shortcut'}));
			}

			//Ctrl+0 (Reset Zoom)
			if ( (e.ctrlKey || e.metaKey) && (e.keyCode === 48 || e.keyCode === 0 || e.keyCode === 96) ){ //48 is 0 (Mac), 0 is Windows 0, and 96 is Windows numpad
				modifiedZoomLevel = 0; //Reset the zoom level iterator

				let thisEvent = {
					event: e,
					event_name: 'zoom_change',
					event_category: 'Keyboard Shortcut',
					event_action: 'Reset Zoom (Ctrl+0)',
					modified_zoom_level: modifiedZoomLevel,
					non_interaction: true
				};

				thisEvent.event_label = 'Modified Zoom Level: ' + thisEvent.modified_zoom_level;

				nebula.dom.document.trigger('nebula_event', thisEvent);
				gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-keyboard-shortcut'}));
			}

			//Ctrl+F or Cmd+F (Find)
			if ( (e.ctrlKey || e.metaKey) && e.keyCode === 70 ){
				let thisEvent = {
					event: e,
					event_name: 'search', //We will not have a "search_term" parameter. Make sure we do have something to note that this is a Find On Page
					event_category: 'Keyboard Shortcut',
					event_action: 'Find on Page (Ctrl+F)',
					highlighted_ext: window.getSelection().toString().trim() || '(No highlighted text when initiating find)',
					non_interaction: true
				};

				nebula.dom.document.trigger('nebula_event', thisEvent);
				gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-keyboard-shortcut'}));
			}

			//Ctrl+D or Cmd+D (Bookmark)
			if ( (e.ctrlKey || e.metaKey) && e.keyCode === 68 ){ //Ctrl+D
				let thisEvent = {
					event: e,
					event_name: 'bookmark',
					event_category: 'Keyboard Shortcut',
					event_action: 'Bookmark (Ctrl+D)',
					event_label: 'User bookmarked the page (with keyboard shortcut)',
					non_interaction: true
				};

				nebula.removeQueryParameter(['utm_campaign', 'utm_medium', 'utm_source', 'utm_content', 'utm_term'], window.location.href); //Remove existing UTM parameters
				history.replaceState(null, document.title, window.location.href + '?utm_source=bookmark');
				nebula.dom.document.trigger('nebula_event', thisEvent);
				gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-keyboard-shortcut'}));
			}
		});

		//Mailto link tracking
		nebula.dom.document.on('mousedown', 'a[href^="mailto"]', function(e){
			let emailAddress = jQuery(this).attr('href').replace('mailto:', '');
			let emailDomain = emailAddress.split('@')[1]; //Get everything after the @
			let anonymizedEmail = nebula.anonymizeEmail(emailAddress); //Mask the email with asterisks

			let thisEvent = {
				event: e,
				event_name: 'mailto',
				event_category: 'Contact',
				event_action: 'Mailto',
				event_label: ( emailAddress.toLowerCase().includes(window.location.hostname) )? emailAddress : anonymizedEmail, //If the email matches the website use it, otherwise use the anonymized email
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				email_address: emailAddress,
				email_domain: emailDomain,
				anonymized_email: anonymizedEmail
			};

			gtag('set', 'user_properties', {
				contact_method : 'Email'
			});

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-mailto'}));
			nebula.fbq('track', 'Lead', {content_name: thisEvent.event_action});
			nebula.clarity('set', thisEvent.event_category, thisEvent.event_action);
			nebula.crm('event', thisEvent.event_action);
			nebula.crm('identify', {mailto_contacted: thisEvent.emailAddress});
		});

		//Telephone link tracking
		nebula.dom.document.on('mousedown', 'a[href^="tel"]', function(e){
			let thisEvent = {
				event: e,
				event_name: 'click_to_call',
				event_category: 'Contact',
				event_action: 'Click-to-Call',
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				phone_umber: jQuery(this).attr('href').replace('tel:', '')
			};

			gtag('set', 'user_properties', {
				contact_method : 'Phone'
			});

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-click-to-call'}));
			nebula.fbq('track', 'Lead', {content_name: thisEvent.event_action});
			nebula.clarity('set', thisEvent.event_category, thisEvent.event_action);
			nebula.crm('event', thisEvent.event_action);
			nebula.crm('identify', {phone_contacted: thisEvent.phoneNumber});
		});

		//SMS link tracking
		nebula.dom.document.on('mousedown', 'a[href^="sms"]', function(e){
			let thisEvent = {
				event: e,
				event_name: 'sms',
				event_category: 'Contact',
				event_action: 'SMS',
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				phone_number: jQuery(this).attr('href').replace('tel:', '')
			};

			gtag('set', 'user_properties', {
				contact_method : 'SMS'
			});

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-sms'}));
			nebula.fbq('track', 'Lead', {content_name: thisEvent.event_action});
			nebula.clarity('set', thisEvent.event_category, thisEvent.event_action);
			nebula.crm('event', thisEvent.event_action);
			nebula.crm('identify', {phone_contacted: thisEvent.phoneNumber});
		});

		//Street Address click //@todo "Nebula" 0: How to detect when a user clicks an address that is not linked, but mobile opens the map anyway? What about when it *is* linked?

		//Utility Navigation Menu
		nebula.dom.document.on('mousedown', '#utility-nav ul.menu a', function(e){
			let thisEvent = {
				event: e,
				event_name: 'menu_click',
				event_category: 'Navigation Menu',
				event_action: 'Utility Menu',
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				link_text: jQuery(this).text().trim()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-navigation-menu-click'}));
		});

		//Primary Navigation Menu
		nebula.dom.document.on('mousedown', '#primary-nav ul.menu a', function(e){
			let thisEvent = {
				event: e,
				event_name: 'menu_click',
				event_category: 'Navigation Menu',
				event_action: 'Primary Menu',
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				link_text: jQuery(this).text().trim()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-navigation-menu-click'}));
		});

		//Offcanvas Menu Open
		nebula.dom.document.on('show.bs.offcanvas', function(e){
			let thisEvent = {
				event: e,
				event_name: 'menu_toggle',
				event_category: 'Navigation Menu',
				event_action: 'Offcanvas Menu (' + e.target.id + ')',
				event_label: 'Opened',
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-offcanvas-shown'}));

			nebula.timer('(Nebula) Offcanvas Menu', 'start');
		});

		//Offcanvas Menu Close
		nebula.dom.document.on('hide.bs.offcanvas', function(e){
			let thisEvent = {
				event: e,
				event_name: 'menu_toggle',
				event_category: 'Navigation Menu',
				event_action: 'Offcanvas Menu (' + e.target.id + ')',
				event_label: 'Closed (without Navigation)',
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-offcanvas-closed'}));
		});

		//Offcanvas Navigation Link
		nebula.dom.document.on('mousedown', '.offcanvas-body a', function(e){
			let thisEvent = {
				event: e,
				event_name: 'menu_click',
				event_category: 'Navigation Menu',
				event_action: 'Offcanvas Menu (' + e.target.id + ')',
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				link_text: jQuery(this).text().trim()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-navigation-menu-click'}));

			gtag('event', 'timing_complete', {
				name: 'Navigated',
				value: Math.round(nebula.timer('(Nebula) Offcanvas Menu', 'lap')),
				event_category: 'Offcanvas Menu',
				event_label: 'From opening offcanvas menu until navigation',
			});
		});

		//Breadcrumb Navigation
		nebula.dom.document.on('mousedown', 'ol.nebula-breadcrumbs a', function(e){
			let thisEvent = {
				event: e,
				event_name: 'menu_click',
				event_category: 'Navigation Menu',
				event_action: 'Breadcrumbs',
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				link_text: jQuery(this).text().trim()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-navigation-menu-click'}));
		});

		//Sidebar Navigation Menu
		nebula.dom.document.on('mousedown', '#sidebar-section ul.menu a', function(e){
			let thisEvent = {
				event: e,
				event_name: 'menu_click',
				event_category: 'Navigation Menu',
				event_action: 'Sidebar Menu',
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				link_text: jQuery(this).text().trim()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-navigation-menu-click'}));
		});

		//Footer Navigation Menu
		nebula.dom.document.on('mousedown', '#powerfooter a', function(e){
			let thisEvent = {
				event: e,
				event_name: 'menu_click',
				event_category: 'Navigation Menu',
				event_action: 'Footer Menu',
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				link_text: jQuery(this).text().trim()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-navigation-menu-click'}));
		});

		//Outbound links (do not use jQuery click listener here)
		document.body.addEventListener('click', function(e){
			let oThis = jQuery(e.target); //Convert the JS event to a jQuery object

			let linkElement = false; //Assume the element is not a link first
			if ( oThis.is('a') ){ //If this element is an <a> tag, use it
				linkElement = oThis;
			} else if ( oThis.parents('a').length ){ //If the clicked element is not an <a> tag, check parent elements to an <a> tag
				linkElement = oThis.parents('a'); //Use the parent <a> as the target element
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
									event_name: 'outbound_link',
									event_category: 'Outbound Link',
									event_action: 'Click',
									outbound: true,
									subdomain: href.includes('.' + domain), //Boolean if this is a subdomain of the primary domain
									link_text: linkElement.text().trim(),
									intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
									href: href
								};

								nebula.dom.document.trigger('nebula_event', thisEvent);
								gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
								window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-outbound-link-click'}));
							}
						}
					}
				}
			}
		}, false);

		//Nebula Cookie Notification link clicks
		nebula.dom.document.on('mousedown', '#nebula-cookie-notification a', function(e){
			let thisEvent = {
				event: e,
				event_name: 'cookie_notification',
				event_category: 'Cookie Notification',
				event_action: 'Click',
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				text: jQuery(this).text().trim(),
				link: jQuery(this).attr('href'),
				non_interaction: true //Non-interaction because the user is not interacting with any content yet so this should not influence the bounce rate
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-cookie-notification-click'}));
		});

		//History Popstate (dynamic URL changes via the History API when "states" are pushed into the browser history)
		if ( typeof history.pushState === 'function' ){
			nebula.dom.window.on('popstate', function(e){ //When a state that was previously pushed is used, or "popped". This *only* triggers when a pushed state is popped!
				let thisEvent = {
					event: e,
					event_name: 'history_popstate',
					event_category: 'History Popstate',
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
				redirect_count: performance.navigation.redirectCount,
				non_interaction: true //Non-interaction because this happens on load
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-high-redirect-count'}));
			nebula.crm('event', thisEvent.event_category);
		}

		//Dead Clicks (Non-Linked Click Attempts)
		nebula.dom.document.on('click', 'img', function(e){ //Clicks on images
			if ( !jQuery(this).parents('a, button').length ){
				let thisEvent = {
					event: e,
					event_name: 'dead_click',
					event_category: 'Dead Click',
					event_action: 'Image',
					element: 'Image',
					src: jQuery(this).attr('src'),
					non_interaction: true //Non-interaction because if the user leaves due to this it should be considered a bounce
				};

				nebula.dom.document.trigger('nebula_event', thisEvent);
				gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-dead-click'}));
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
						element: 'Text',
						text: jQuery(e.target).text().trim(),
						non_interaction: true //Non-interaction because if the user leaves due to this it should be considered a bounce
					};

					nebula.dom.document.trigger('nebula_event', thisEvent);
					gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
					window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-dead-click'}));
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
					clicks: numberOfClicks,
					period: timeDiff,
					selector: nebula.domTreeToString(e.target),
					non_interaction: true //Non-interaction because if the user exits due to this it should be considered a bounce
				};

				nebula.dom.document.trigger('nebula_event', thisEvent);
				gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-rage-clicks'}));

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
				link_text: jQuery(this).text().trim(),
				non_interaction: true //Non-interaction because they are not actually taking action and these links do not indicate engagement
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.event_category, thisEvent.event_action);}
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-accessibility-link'}));
		});

		//Clicks on Skip to Content and other screen reader links (which indicate screenreader software is being used in this session)
		nebula.dom.document.on('click', '#skip-to-content-link, .visually-hidden, .visually-hidden-focusable', function(e){
			let thisEvent = {
				event: e,
				event_name: 'accessibility_links',
				event_category: 'Accessibility Links',
				event_action: 'Click',
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				link_text: jQuery(this).text().trim(),
				non_interaction: true //Non-interaction because these links do not indicate engagement
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.event_category, thisEvent.event_action);}
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-accessibility-link'}));
		});

		//Video Enter Picture-in-Picture //https://caniuse.com/#feat=picture-in-picture
		nebula.dom.document.on('enterpictureinpicture', 'video', function(e){
			let thisEvent = {
				event: e,
				event_name: 'video_pip',
				event_category: 'Videos',
				event_action: 'Enter Picture-in-Picture',
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
				videoID: e.target.id,
				non_interaction: true //Non-interaction because this may not be triggered by the user
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
		});

		//Page Visibility
		nebula.dom.document.on('visibilitychange', function(e){
			let thisEvent = {
				event: e,
				event_name: 'visibility_change',
				event_category: 'Visibility Change',
				event_action: document.visibilityState, //Hidden, Visible, Prerender, or Unloaded
				event_label: 'The state of the visibility of this page has changed.',
				non_interaction: true //Non-interaction because these are not interactions with the website itself
			};

			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
		});

		//Word copy tracking
		let copyCount = 0;
		nebula.dom.document.on('cut copy', function(e){
			//Ignore clipboard events that occur within form inputs or on Woocommerce checkout/confirmation pages
			if ( jQuery(e.target).is('input, textarea') || jQuery(e.target).parents('form').length || jQuery('body.woocommerce-checkout').length || jQuery('body.woocommerce-order-received').length ){
				return false;
			}

			let selection = window.getSelection().toString().trim();

			if ( selection ){
				let words = selection.split(' ');
				let wordsLength = words.length;

				//Track Email or Phone copies as contact intent.
				if ( nebula.regex.email.test(selection) ){
					let thisEvent = {
						event_name: 'mailto',
						event_category: 'Contact',
						event_action: 'Email (Copy)',
						intent: 'Intent',
						email_address: nebula.anonymizeEmail(selection), //Mask the email with asterisks,
						words: words,
						word_count: wordsLength
					};

					gtag('set', 'user_properties', {
						contact_method : 'Email'
					});

					nebula.dom.document.trigger('nebula_event', thisEvent);
					gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
					window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-copied-email'}));
					nebula.crm('event', 'Email Address Copied');
					nebula.crm('identify', {mailto_contacted: thisEvent.emailAddress});
				} else if ( nebula.regex.address.test(selection) ){
					let thisEvent = {
						event_name: 'address_copy', //Probably could be a better name
						event_category: 'Contact',
						event_action: 'Street Address (Copy)',
						intent: 'Intent',
						address: selection,
						words: words,
						word_count: wordsLength
					};

					nebula.dom.document.trigger('nebula_event', thisEvent);
					gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
					window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-copied-address'}));
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
							intent: 'Intent',
							phone_number: selection,
							words: words,
							word_count: wordsLength
						};

						gtag('set', 'user_properties', {
							contact_method : 'Phone'
						});

						nebula.dom.document.trigger('nebula_event', thisEvent);
						gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
						window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-copied-phone'}));
						nebula.crm('event', 'Phone Number Copied');
						nebula.crm('identify', {phone_contacted: thisEvent.phoneNumber});
					}
				}

				//Send the regular copied text event since it does not contain contact information
				let thisEvent = {
					event_name: 'copy_text',
					event_category: 'Copied Text',
					event_action: 'Copy',
					intent: 'Intent',
					selection: selection,
					words: words,
					word_count: wordsLength
				};

				if ( selection.length > 150 ){
					thisEvent.selection = thisEvent.selection.substring(0, 150) + '...'; //Max character length for GA event is 256
				} else if ( thisEvent.word_count >= 10 ){
					thisEvent.words = thisEvent.words.slice(0, 10).join(' ') + '... [' + thisEvent.word_count + ' Words]';
				} else if ( selection.trim() === '' ){
					thisEvent.words = '[0 words]';
				}
				if ( thisEvent.words.length > 150 ){
					thisEvent.words = thisEvent.words.substring(0, 150) + '...'; //Max character length for GA event is 256
				}

				nebula.dom.document.trigger('nebula_event', thisEvent);

				if ( copyCount < 5 ){ //If fewer than 5 copies have happened in this page view
					gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
					window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-copied-text'}));
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
				error_message: errorMessage,
				url: settings.url,
				description: '(JS) AJAX Error (' + jqXHR.status + '): ' + errorMessage + ' on ' + settings.url,
				fatal: true
			});
			window.dataLayer.push({'event': 'nebula-ajax-error', 'error': errorMessage});
			nebula.crm('event', 'AJAX Error');
		});

		//Note: Window errors are detected in usage.js for better visibility

		//Reporting Observer deprecations and interventions
		try {
			if ( 'ReportingObserver' in window ){ //Chrome 68+
				let nebulaReportingObserver = new ReportingObserver(function(reports, observer){
					for ( let report of reports ){
						if ( report?.body?.sourceFile && !['extension', 'about:blank'].some((item) => report.body.sourceFile.includes(item)) ){ //Ignore certain files
							gtag('event', 'exception', {
								report_type: report.type,
								report_message: report.body.message,
								source_file: report.body.sourceFile,
								line_number: report.body.lineNumber,
								description: '(JS) Reporting Observer [' + report.type + ']: ' + report.body.message + ' in ' + report.body.sourceFile + ' on line ' + report.body.lineNumber,
								fatal: false
							});
						}
					}
				}, {buffered: true}); //Buffer to capture reports that happened prior to the observer being created

				nebulaReportingObserver.observe();
			}
		} catch {
			//Ignore errors
		}

		//Capture Print Intent
		function sendPrintEvent(action, trigger){
			let thisEvent = {
				event_name: 'print',
				event_category: 'Print',
				event_action: action,
				event_label: 'User triggered print via ' + trigger,
				intent: 'Intent'
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-print'}));
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.event_category, thisEvent.event_action);}
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
			let oThis = jQuery(this);
			let thisEvent = {
				event: e,
				event_name: 'search',
				event_category: 'DataTables',
				event_action: 'Search Filter',
				query: oThis.val().toLowerCase().trim()
			};

			nebula.debounce(function(){
				nebula.dom.document.trigger('nebula_event', thisEvent);
				gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-datatables'}));
			}, 1000, 'datatables_search_filter');
		});

		//DataTables Sorting
		nebula.dom.document.on('click', 'th.sorting', function(e){
			let thisEvent = {
				event: e,
				event_name: 'datatables_sort',
				event_category: 'DataTables',
				event_action: 'Sort',
				heading: jQuery(this).text()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-datatables'}));
		});

		//DataTables Pagination
		nebula.dom.document.on('click', 'a.paginate_button ', function(e){
			let thisEvent = {
				event: e,
				event_name: 'datatables_paginate',
				event_category: 'DataTables',
				event_action: 'Paginate',
				page: jQuery(this).text()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-datatables'}));
		});

		//DataTables Show Entries
		nebula.dom.document.on('change', '.dataTables_length select', function(e){
			let thisEvent = {
				event: e,
				event_name: 'datatables_length',
				event_category: 'DataTables',
				event_action: 'Shown Entries Change', //Number of visible rows select dropdown
				selected: jQuery(this).val()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-datatables'}));
		});

		nebula.scrollDepth();
		nebula.ecommerceTracking();
	}, 'nebula event tracking');
};

//Ecommerce event tracking
//Note: These supplement the plugin Enhanced Ecommerce for WooCommerce
nebula.ecommerceTracking = async function(){
	if ( nebula.site?.ecommerce ){
		//Add to Cart clicks
		nebula.dom.document.on('click', 'a.add_to_cart, .single_add_to_cart_button', function(e){ //@todo "Nebula" 0: is there a trigger from WooCommerce this can listen for?
			let thisEvent = {
				event: e,
				event_name: 'add_to_cart',
				event_category: 'Ecommerce',
				event_action: 'Add to Cart',
				product: jQuery(this).attr('data-product_id')
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-add-to-cart'}));
			nebula.fbq('track', 'AddToCart');
			nebula.clarity('set', thisEvent.event_category, thisEvent.event_action);
			nebula.crm('event', 'Ecommerce Add to Cart');
		});

		//Update cart clicks
		nebula.dom.document.on('click', '.button[name="update_cart"]', function(e){
			let thisEvent = {
				event: e,
				event_name: 'update_cart',
				event_category: 'Ecommerce',
				event_action: 'Update Cart Button',
				event_label: 'Update Cart button click'
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-update-cart'}));
			nebula.crm('event', 'Ecommerce Update Cart');
		});

		//Product Remove buttons
		nebula.dom.document.on('click', '.product-remove a.remove', function(e){
			let thisEvent = {
				event: e,
				event_name: 'remove_from_cart',
				event_category: 'Ecommerce',
				event_action: 'Remove This Item',
				product: jQuery(this).attr('data-product_id')
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-remove-item'}));
			nebula.crm('event', 'Ecommerce Remove From Cart');
		});

		//Proceed to Checkout
		nebula.dom.document.on('click', '.wc-proceed-to-checkout .checkout-button', function(e){
			let thisEvent = {
				event: e,
				event_name: 'begin_checkout',
				event_category: 'Ecommerce',
				event_action: 'Proceed to Checkout Button',
				event_label: 'Proceed to Checkout button click'
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-proceed-to-checkout'}));
			nebula.fbq('track', 'InitiateCheckout');
			nebula.clarity('set', thisEvent.event_category, thisEvent.event_action);
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
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-started-checkout-form'}));
			nebula.crm('event', 'Ecommerce Started Checkout Form');
		});

		//Place order button
		nebula.dom.document.on('click', '#place_order', function(e){
			let thisEvent = {
				event: e,
				event_name: 'purchase',
				event_category: 'Ecommerce',
				event_action: 'Place Order Button',
				event_label: 'Place Order button click'
			};

			gtag('event', 'timing_complete', {
				name: 'Checkout Form',
				value: Math.round(nebula.timer('(Nebula) Ecommerce Checkout', 'end')),
				event_category: 'Ecommerce',
				event_label: 'Billing details start to Place Order button click',
			});

			nebula.dom.document.trigger('nebula_event', thisEvent);
			gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-place-order-button'}));
			nebula.fbq('track', 'Purchase');
			nebula.clarity('set', thisEvent.event_category, thisEvent.event_action);
			nebula.crm('event', 'Ecommerce Placed Order');
			nebula.crm('identify', {hs_lifecyclestage_customer_date: 1}); //@todo "Nebula" 0: What kind of date format does Hubspot expect here?
		});
	}
};

//Detect scroll depth
//Note: This is a default GA4 event and is not needed to be tracked in Nebula. Consider deleting entirely.
nebula.scrollDepth = async function(){
	if ( window.performance ){ //Safari 11+
		let scrollReady = performance.now();
		let reachedBottom = false; //Flag for optimization after detection is finished
		let excessiveScrolling = false; //Flag for optimization after detection is finished
		let lastScrollCheckpoint = nebula.dom.window.scrollTop(); //Set a checkpoint of the current scroll distance to subtract against later
		let totalScrollDistance = 0; //Down and up distance
		let excessiveScrollThreshold = nebula.dom.document.height()*2; //Set the threshold for an excessive scroll distance

		nebula.maxScrollDepth = 0; //This needs to be accessed from multiple other functions later
		nebula.updateMaxScrollDepth(); //Update it first right away on load (the rest will be throttled)

		let scrollDepthHandler = function(){
			//Only check for initial scroll once
			nebula.once(function(){
				nebula.scrollBegin = performance.now()-scrollReady; //Calculate when the first scroll happens
				if ( nebula.scrollBegin > 250 ){ //Try to avoid autoscrolls on pageload
					let thisEvent = {
						event_name: 'scroll',
						event_category: 'Scroll Depth',
						event_action: 'Began Scrolling',
						scroll_start: nebula.dom.window.scrollTop() + 'px',
						time_before_scroll_start: Math.round(nebula.scrollBegin),
						non_interaction: true
					};
					thisEvent.event_label = 'Initial scroll started at ' + thisEvent.scrollStart;
					nebula.dom.document.trigger('nebula_event', thisEvent);
					gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
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
							let thisEvent = {
								event_name: 'excessive_scrolling',
								event_category: 'Scroll Depth',
								event_action: 'Excessive Scrolling',
								event_label: 'User scrolled ' + excessiveScrollThreshold + 'px (or more) on this page.',
								non_interaction: true
							};
							nebula.dom.document.trigger('nebula_event', thisEvent);
							gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
						}, 'excessive scrolling');
					}
				}

				nebula.updateMaxScrollDepth();

				//When user reaches the bottom of the page
				if ( !reachedBottom ){
					if ( (nebula.dom.window.height()+nebula.dom.window.scrollTop()) >= nebula.dom.document.height() ){ //If user has reached the bottom of the page
						reachedBottom = true;

						nebula.once(function(){
							let thisEvent = {
								event_name: 'scroll',
								event_category: 'Scroll Depth',
								event_action: 'Entire Page',
								distance: nebula.dom.document.height(),
								scroll_end: performance.now()-(nebula.scrollBegin+scrollReady),
								non_interaction: true
							};

							thisEvent.timetoScrollEnd = Math.round(thisEvent.scrollEnd);

							nebula.dom.document.trigger('nebula_event', thisEvent);
							gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));
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

		//Track when the user reaches the end of the content
		if ( jQuery('#footer-section').length ){
			let footerObserver = new IntersectionObserver(function(entries){
				entries.forEach(function(entry){
					if ( entry.intersectionRatio > 0 ){
						let thisEvent = {
							event_name: 'scroll',
							event_category: 'Scroll Depth',
							event_action: 'Reached Footer',
							event_label: 'The footer of the page scrolled into the viewport',
							non_interaction: true
						};

						nebula.dom.document.trigger('nebula_event', thisEvent);
						gtag('event', thisEvent.event_name, nebula.gaEventObject(thisEvent));

						nebula.updateMaxScrollDepth();
						footerObserver.unobserve(entry.target); //Stop observing the element
					}
				});
			}, {
				rootMargin: '0px', //0px uses the actual viewport bounds, 100% is double the viewport
				threshold: 0.1 //How much of the element needs to be in view before this is triggered (this is a percentage between 0 and 1)
			});

			//Observe the pre-footer section (or whatever element is after the main content area)
			let preFooterSelector = wp.hooks.applyFilters('nebulaPreFooterSelector', '#footer-section'); //This should be the first section after the "content"
			footerObserver.observe(jQuery(preFooterSelector)[0]); //Observe the element
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
				gtag('event', 'Contact Identified', {
					event_category: 'CRM',
					event_label: "A contact's email address in the CRM has been identified.",
					non_interaction: true
				});
				nebula.user.known = true;
			}
		} else {
			nebula.dom.document.trigger('nebula_crm_details', {data: data});
			gtag('event', 'Supporting Information', {
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
		if ( jQuery(this).val().trim().length ){
			if ( jQuery(this).attr('class').includes('crm-notable_poi') ){
				setDimension('notable_poi', jQuery('.notable-poi').val());
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