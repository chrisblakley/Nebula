window.performance.mark('(Nebula) Inside analytics.js (module)');

//Generate a unique ID for hits and windows (used in /inc/analytics.php)
export function uuid(a){
	return a ? (a^Math.random()*16 >> a/4).toString(16) : ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, uuid);
}

//Get local time string with timezone offset (used in /inc/analytics.php)
export function localTimestamp(){
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
export function setDimension(name, value, index){
	//Google Analytics
	if ( typeof ga === 'function' && index ){
		ga('set', index, value);
	}

	//Microsoft Clarity
	if ( typeof clarity === 'function' ){
		clarity('set', name, value);
	}

	//Others
	document.dispatchEvent(new CustomEvent('nebula_dimension', {detail: {'name': name, 'value': value}})); //Allow this dimension to be sent to other platforms from outside Nebula
}

//Google Analytics Universal Analytics Event Trackers
nebula.eventTracking = async function(){
	if ( nebula.isDoNotTrack() ){
		return false;
	}

	nebula.cacheSelectors(); //Just to be safe (this is no longer called from anywhere besides nebula.js so this should never be needed)

	nebula.once(function(){
		window.dataLayer = window.dataLayer || []; //Prevent overwriting an existing GTM Data Layer array

		nebula.dom.document.trigger('nebula_event_tracking');

		if ( typeof window.ga === 'function' ){
			window.ga(function(tracker){
				nebula.dom.document.trigger('nebula_ga_tracker', tracker);
				nebula.user.cid = tracker.get('clientId');
				window.dataLayer.push(Object.assign({'event': 'nebula-ga-tracker', 'client-id': nebula.user.cid}));
			});
		}

		//Back/Forward
		if ( performance.navigation.type === 2 ){ //If the user arrived at this page via the back/forward button
			let previousPage = '(Unknown)';
			let quickBack = false;
			if ( 'localStorage' in window ){
				let prev = JSON.parse(localStorage.getItem('prev')); //Get the previous page from localstorage
				previousPage = prev.path; //Get the previous page from localstorage
				quickBack = prev.quick || false;
			}

			ga('send', 'event', 'Browser Navigation', 'Back/Forward', 'From: ' + previousPage, {'nonInteraction': true});

			if ( quickBack && previousPage !== document.location.pathname ){ //If the previous page was viewed for a very short time and is different than the current page
				ga('send', 'event', 'Quick Back', 'Quickly left from: ' + previousPage, 'Back to: ' + document.location.pathname, {'nonInteraction': true}); //Technically this could be a "quick forward" too, but less likely
			}
		}

		//Reloads
		if ( performance.navigation.type === 1 ){ //If the user reloaded the page
			ga('send', 'event', 'Browser Navigation', 'Reload', document.location.pathname, {'nonInteraction': true});
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

		//Button Clicks
		let nebulaButtonSelector = wp.hooks.applyFilters('nebulaButtonSelectors', 'button, .button, .btn, [role="button"], a.wp-block-button__link, .hs-button'); //Allow child theme or plugins to add button selectors without needing to override/duplicate this function
		nebula.dom.document.on('mousedown', nebulaButtonSelector, function(e){
			let thisEvent = {
				event: e,
				category: 'Button',
				action: 'Click', //GA4 Name: "button_click"?
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				text: jQuery(this).val() || jQuery(this).attr('value') || jQuery(this).text() || jQuery(this).attr('title') || '(Unknown)',
				link: jQuery(this).attr('href') || jQuery(this).attr('title') || '(Unknown)'
			};

			ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', 'Button Click', thisEvent.text.trim(), thisEvent.link);
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-button-click'}));
		});

		//Linked Image Clicks
		nebula.dom.document.on('click', 'a img', function(e){
			let thisEvent = {
				event: e,
				category: 'Image Click',
				action: jQuery(this).attr('alt') || jQuery(this).attr('src'),
				label: jQuery(this).parents('a').attr('href'),
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-image-click'}));
		});

		//Bootstrap "Collapse" Accordions
		nebula.dom.document.on('shown.bs.collapse', function(e){
			let thisEvent = {
				event: e,
				category: 'Accordion',
				action: 'Shown', //GA4 Name: "accordion_toggle"?
				label: jQuery('[data-bs-target="#' + e.target.id + '"]').text().trim() || e.target.id,
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-accordion-shown'}));
		});
		nebula.dom.document.on('hidden.bs.collapse', function(e){
			let thisEvent = {
				event: e,
				category: 'Accordion',
				action: 'Hidden', //GA4 Name: "accordion_toggle"?
				label: jQuery('[data-bs-target="#' + e.target.id + '"]').text().trim() || e.target.id,
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-accordion-hidden'}));
		});

		//Bootstrap Modals
		nebula.dom.document.on('shown.bs.modal', function(e){
			let thisEvent = {
				event: e,
				category: 'Modal',
				action: 'Shown', //GA4 Name: "modal_toggle"?
				label: jQuery('#' + e.target.id + ' .modal-title').text().trim() || e.target.id,
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-modal-shown'}));
		});
		nebula.dom.document.on('hidden.bs.modal', function(e){
			let thisEvent = {
				event: e,
				category: 'Modal',
				action: 'Hidden', //GA4 Name: "modal_toggle"?
				label: jQuery('#' + e.target.id + ' .modal-title').text().trim() || e.target.id,
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-modal-hidden'}));
		});

		//Bootstrap Carousels (Sliders)
		nebula.dom.document.on('slide.bs.carousel', function(e){
			if ( window.event ){ //Only if sliding manually
				let thisEvent = {
					event: e,
					category: 'Carousel',
					action: e.target.id || e.target.title || e.target.className.replaceAll(/\s/g, '.'), //GA4 Name: "carousel_slide"?
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
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-carousel-slide'}));
			}
		});

		//Generic Form Submissions
		//This event will be a duplicate if proper event tracking is setup on each form, but serves as a safety net.
		//It is not recommended to use this event for goal tracking unless absolutely necessary (this event does not check for submission success)!
		nebula.dom.document.on('submit', 'form', function(e){
			let thisEvent = {
				event: e,
				category: 'Generic Form',
				action: 'Submit', //GA4 Name: "form_submit"? How to differentiate it from conversions?
				formID: e.target.id || 'form.' + e.target.className.replaceAll(/\s/g, '.'),
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.formID);
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.action);}
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-generic-form'}));
		});

		//Notable File Downloads
		let notableFileExtensions = wp.hooks.applyFilters('nebulaNotableFiles', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv', 'zip', 'zipx', 'rar', 'gz', 'tar', 'txt', 'rtf', 'ics', 'vcard']);
		jQuery.each(notableFileExtensions, function(index, extension){
			jQuery("a[href$='." + extension + "' i]").on('mousedown', function(e){ //Cannot defer case insensitive attribute selectors in jQuery (or else you will get an "unrecognized expression" error)
				let thisEvent = {
					event: e,
					category: 'Download',
					action: extension, //GA4 Name: "file_download" Note: This is a default GA4 event and is not needed to be tracked in Nebula. Consider deleting entirely.
					intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
					extension: extension,
					fileName: jQuery(this).attr('href').substr(jQuery(this).attr('href').lastIndexOf('/')+1),
				};

				ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.fileName);
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-download'}));
				if ( typeof fbq === 'function' ){fbq('track', 'ViewContent', {content_name: thisEvent.fileName});}
				if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.fileName);}
				nebula.crm('event', 'File Download');
			});
		});

		//Notable Downloads
		nebula.dom.document.on('mousedown', '.notable a, a.notable', function(e){
			let thisEvent = {
				event: e,
				category: 'Download',
				action: 'Notable', //GA4 Name: "file_download"
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				filePath: jQuery(this).attr('href').trim(),
				linkText: jQuery(this).text()
			};

			if ( thisEvent.filePath.length && thisEvent.filePath !== '#' ){
				thisEvent.fileName = filePath.substr(filePath.lastIndexOf('/')+1);
				ga('set', nebula.analytics.metrics.notableDownloads, 1);
				nebula.dom.document.trigger('nebula_event', thisEvent);

				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.fileName);
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-download'}));
				if ( typeof fbq === 'function' ){fbq('track', 'ViewContent', {content_name: thisEvent.fileName});}
				if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.fileName);}
				nebula.crm('event', 'Notable File Download');
			}
		});

		//Generic Internal Search Tracking
		//This event will need to correspond to the GA4 event name "search" and use "search_term" as a parameter: https://support.google.com/analytics/answer/9267735
		let internalSearchInputSelector = wp.hooks.applyFilters('nebulaInternalSearchInputs', '#s, input.search');
		nebula.dom.document.on('submit', internalSearchInputSelector, function(){
			let thisEvent = {
				event: e,
				category: 'Internal Search',
				action: 'Submit', //GA4 Name: "search"
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				query: jQuery(this).find('input[name="s"]').val().toLowerCase().trim()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.query);
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-internal-search'}));
			if ( typeof fbq === 'function' ){fbq('track', 'Search', {search_string: thisEvent.query});}
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.query);}
			nebula.crm('identify', {internal_search: thisEvent.query});
		});

		//Suggested pages on 404 results
		nebula.dom.document.on('mousedown', 'a.internal-suggestion', function(e){
			let thisEvent = {
				event: e,
				category: 'Page Suggestion',
				action: 'Internal', //GA4 name: "select_content"
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				suggestion: jQuery(this).text(),
			};

			ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.suggestion);
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
					category: 'Keyboard Shortcut',
					action: 'Zoom In (Ctrl+)', //GA4 Name: "zoom_change"?
					modifiedZoomLevel: modifiedZoomLevel
				};

				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, 'Modified Zoom Level: ' + thisEvent.modifiedZoomLevel, {'nonInteraction': true});
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-keyboard-shortcut'}));
			}

			//Ctrl- (Zoom Out)
			if ( (e.ctrlKey || e.metaKey) && (e.keyCode === 189 || e.keyCode === 109) ){ //189 is minus, 109 is minus on the numpad
				modifiedZoomLevel--; //Decrement the zoom level iterator

				let thisEvent = {
					event: e,
					category: 'Keyboard Shortcut',
					action: 'Zoom Out (Ctrl-)', //GA4 Name: "zoom_change"?
					modifiedZoomLevel: modifiedZoomLevel
				};

				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, 'Modified Zoom Level: ' + thisEvent.modifiedZoomLevel, {'nonInteraction': true});
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-keyboard-shortcut'}));
			}

			//Ctrl+0 (Reset Zoom)
			if ( (e.ctrlKey || e.metaKey) && (e.keyCode === 48 || e.keyCode === 0 || e.keyCode === 96) ){ //48 is 0 (Mac), 0 is Windows 0, and 96 is Windows numpad
				modifiedZoomLevel = 0; //Reset the zoom level iterator

				let thisEvent = {
					event: e,
					category: 'Keyboard Shortcut',
					action: 'Reset Zoom (Ctrl+0)', //GA4 Name: "zoom_change"?
					modifiedZoomLevel: modifiedZoomLevel
				};

				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, 'Modified Zoom Level: ' + thisEvent.modifiedZoomLevel, {'nonInteraction': true});
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-keyboard-shortcut'}));
			}

			//Ctrl+F or Cmd+F (Find)
			if ( (e.ctrlKey || e.metaKey) && e.keyCode === 70 ){
				let thisEvent = {
					event: e,
					category: 'Keyboard Shortcut',
					action: 'Find on Page (Ctrl+F)', //GA4 Name: "search" but we will not have a "search_term" parameter. Make sure we do have something to note that this is a Find On Page
					highlightedText: window.getSelection().toString().trim() || '(No highlighted text when initiating find)'
				};

				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.highlightedText, {'nonInteraction': true});
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-keyboard-shortcut'}));
			}

			//Ctrl+D or Cmd+D (Bookmark)
			if ( (e.ctrlKey || e.metaKey) && e.keyCode === 68 ){ //Ctrl+D
				let thisEvent = {
					event: e,
					category: 'Keyboard Shortcut',
					action: 'Bookmark (Ctrl+D)', //GA4 Name: "bookmark"?
					label: 'User bookmarked the page (with keyboard shortcut)'
				};

				nebula.removeQueryParameter(['utm_campaign', 'utm_medium', 'utm_source', 'utm_content', 'utm_term'], window.location.href); //Remove existing UTM parameters
				history.replaceState(null, document.title, window.location.href + '?utm_source=bookmark');
				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label, {'nonInteraction': true});
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-keyboard-shortcut'}));
			}
		});

		//Mailto link tracking
		nebula.dom.document.on('mousedown', 'a[href^="mailto"]', function(e){
			let thisEvent = {
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
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-mailto'}));
			if ( typeof fbq === 'function' ){fbq('track', 'Lead', {content_name: thisEvent.action});}
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.action);}
			nebula.crm('event', thisEvent.action);
			nebula.crm('identify', {mailto_contacted: thisEvent.emailAddress});
		});

		//Telephone link tracking
		nebula.dom.document.on('mousedown', 'a[href^="tel"]', function(e){
			let thisEvent = {
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
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-click-to-call'}));
			if ( typeof fbq === 'function' ){fbq('track', 'Lead', {content_name: thisEvent.action});}
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.action);}
			nebula.crm('event', thisEvent.action);
			nebula.crm('identify', {phone_contacted: thisEvent.phoneNumber});
		});

		//SMS link tracking
		nebula.dom.document.on('mousedown', 'a[href^="sms"]', function(e){
			let thisEvent = {
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
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-sms'}));
			if ( typeof fbq === 'function' ){fbq('track', 'Lead', {content_name: thisEvent.action});}
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.action);}
			nebula.crm('event', thisEvent.action);
			nebula.crm('identify', {phone_contacted: thisEvent.phoneNumber});
		});

		//Street Address click //@todo "Nebula" 0: How to detect when a user clicks an address that is not linked, but mobile opens the map anyway? What about when it *is* linked?

		//Utility Navigation Menu
		nebula.dom.document.on('mousedown', '#utility-nav ul.menu a', function(e){
			let thisEvent = {
				event: e,
				category: 'Navigation Menu',
				action: 'Utility Menu', //GA4 Name: "menu_click"?
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				linkText: jQuery(this).text().trim()
			};

			ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.linkText);
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-navigation-menu-click'}));
		});

		//Primary Navigation Menu
		nebula.dom.document.on('mousedown', '#primary-nav ul.menu a', function(e){
			let thisEvent = {
				event: e,
				category: 'Navigation Menu',
				action: 'Primary Menu', //GA4 Name: "menu_click"?
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				linkText: jQuery(this).text().trim()
			};

			ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.linkText);
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-navigation-menu-click'}));
		});

		//Offcanvas Menu Open
		nebula.dom.document.on('show.bs.offcanvas', function(e){
			let thisEvent = {
				event: e,
				category: 'Navigation Menu',
				action: 'Offcanvas Menu (' + e.target.id + ')',
				label: 'Opened',
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-offcanvas-shown'}));

			nebula.timer('(Nebula) Offcanvas Menu', 'start');
		});

		//Offcanvas Menu Close
		nebula.dom.document.on('hide.bs.offcanvas', function(e){
			let thisEvent = {
				event: e,
				category: 'Navigation Menu',
				action: 'Offcanvas Menu (' + e.target.id + ')',
				label: 'Closed (without Navigation)',
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-offcanvas-closed'}));
		});

		//Offcanvas Navigation Link
		nebula.dom.document.on('mousedown', '.offcanvas-body a', function(e){
			let thisEvent = {
				event: e,
				category: 'Navigation Menu',
				action: 'Offcanvas Menu (' + e.target.id + ')', //GA4 Name: "menu_click"?
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				linkText: jQuery(this).text().trim()
			};

			ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.linkText);
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-navigation-menu-click'}));

			ga('send', 'timing', 'Offcanvas Menu', 'Navigated', Math.round(nebula.timer('(Nebula) Offcanvas Menu', 'lap')), 'From opening offcanvas menu until navigation');
		});

		//Breadcrumb Navigation
		nebula.dom.document.on('mousedown', 'ol.nebula-breadcrumbs a', function(e){
			let thisEvent = {
				event: e,
				category: 'Navigation Menu',
				action: 'Breadcrumbs', //GA4 Name: "menu_click"?
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				linkText: jQuery(this).text().trim()
			};

			ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.linkText);
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-navigation-menu-click'}));
		});

		//Sidebar Navigation Menu
		nebula.dom.document.on('mousedown', '#sidebar-section ul.menu a', function(e){
			let thisEvent = {
				event: e,
				category: 'Navigation Menu',
				action: 'Sidebar Menu', //GA4 Name: "menu_click"?
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				linkText: jQuery(this).text().trim()
			};

			ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.linkText);
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-navigation-menu-click'}));
		});

		//Footer Navigation Menu
		nebula.dom.document.on('mousedown', '#powerfooter a', function(e){
			let thisEvent = {
				event: e,
				category: 'Navigation Menu',
				action: 'Footer Menu', //GA4 Name: "menu_click"?
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				linkText: jQuery(this).text().trim()
			};

			ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.linkText);
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-navigation-menu-click'}));
		});

		//Outbound links (do not use jQuery click listener here)
		document.body.addEventListener('click', function(e){
			let oThis = jQuery(e.target); //Convert the JS event to a jQuery object

			let linkElement = false; //Assume the element is not a link first
			if ( oThis.is('a') ){ //If this element is an <a> tag, use it
				linkElement = oThis;
			} else { //If the clicked element is not an <a> tag
				if ( oThis.parents('a').length ){ //Check parent elements to an <a> tag
					linkElement = oThis.parents('a'); //Use the parent <a> as the target element
				}
			}

			if ( linkElement ){ //If we ended up with a link after all
				let href = linkElement.attr('href');
				if ( href ){ //href may be undefined in special circumstances so we can ignore those
					let domain = nebula.site.domain;

					if ( href.includes('http') ){ //If the link contains "http"
						if ( !href.includes(domain) || href.includes('.' + domain) ){ //If the link does not contain "example.com" -or- if the link does contain a subdomain like "something.example.com"
							if ( !href.includes('//www.' + domain) ){ //Exclude the "www" subdomain
								let thisEvent = {
									event: e,
									category: 'Outbound Link',
									action: 'Click',
									linkText: linkElement.text().trim(),
									intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
									href: href
								};

								ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
								nebula.dom.document.trigger('nebula_event', thisEvent);
								ga('send', 'event', thisEvent.category, thisEvent.linkText, thisEvent.href);
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
				category: 'Cookie Notification',
				action: 'Click', //GA4 Name: "cookie_notification"?
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				text: jQuery(this).text(),
				link: jQuery(this).attr('href')
			};

			ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.text.trim(), thisEvent.link, {'nonInteraction': true}); //Non-interaction because the user is not interacting with any content yet so this should not influence the bounce rate
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-cookie-notification-click'}));
		});

		//History Popstate (dynamic URL changes via the History API when "states" are pushed into the browser history)
		if ( typeof history.pushState === 'function' ){
			nebula.dom.window.on('popstate', function(e){ //When a state that was previously pushed is used, or "popped". This *only* triggers when a pushed state is popped!
				let thisEvent = {
					event: e,
					category: 'History Popstate',
					action: document.title,
					location: document.location,
					state: JSON.stringify(e.state)
				};

				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.location);
			});
		}

		//High Redirect Counts
		if ( window.performance && performance.navigation.redirectCount >= 3 ){ //If the browser redirected 3+ times
			let previousPage = nebula.session.referrer || document.referrer || '(Unknown Previous Page)';

			let thisEvent = {
				category: 'High Redirect Count',
				action: performance.navigation.redirectCount + ' Redirects',
				label: 'Previous Page: ' + previousPage,
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', category, action, label, {'nonInteraction': true}); //Non-interaction because this happens on load
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-high-redirect-count'}));
			nebula.crm('event', thisEvent.category);
		}

		//Dead Clicks (Non-Linked Click Attempts)
		nebula.dom.document.on('click', 'img', function(e){
			if ( !jQuery(this).parents('a, button').length ){
				let thisEvent = {
					event: e,
					category: 'Dead Click',
					action: 'Image', //GA4 Name: "dead_click"?
					element: 'Image',
					src: jQuery(this).attr('src')
				};

				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.src, {'nonInteraction': true}); //Non-interaction because if the user leaves due to this it should be considered a bounce
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-dead-click'}));
				nebula.crm('event', thisEvent.category);
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
					category: 'Rage Clicks',
					action: 'Detected', //GA4 Name: "rage_clicks"?
					clicks: numberOfClicks,
					period: timeDiff,
					selector: nebula.domTreeToString(e.target),
				};

				thisEvent.description = numberOfClicks + ' clicks in ' + timeDiff + ' seconds detected within ' + maxDistance + 'px of ' + thisEvent.selector;

				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.description, {'nonInteraction': true}); //Non-interaction because if the user exits due to this it should be considered a bounce
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-rage-clicks'}));

				clickEvents.splice(clickEvents.length-5, 5); //Remove unused click points
			}
		});

		//Focus on Skip to Content and other screen reader links (which indicate screenreader software is being used in this session)
		nebula.dom.document.on('focus', '#skip-to-content-link, .visually-hidden, .visually-hidden-focusable', function(e){
			let thisEvent = {
				event: e,
				category: 'Accessibility Links',
				action: 'Focus', //GA4 Name: "accessibility_links"?
				linkText: jQuery(this).text().trim()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.linkText, {'nonInteraction': true}); //Non-interaction because they are not actually taking action and these links do not indicate engagement
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.action);}
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-accessibility-link'}));
		});

		//Clicks on Skip to Content and other screen reader links (which indicate screenreader software is being used in this session)
		nebula.dom.document.on('click', '#skip-to-content-link, .visually-hidden, .visually-hidden-focusable', function(e){
			let thisEvent = {
				event: e,
				category: 'Accessibility Links',
				action: 'Click', //GA4 Name: "accessibility_links"?
				intent: ( e.which >= 2 )? 'Intent' : 'Explicit',
				linkText: jQuery(this).text().trim()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.linkText, {'nonInteraction': true}); //Non-interaction because these links do not indicate engagement
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.action);}
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-accessibility-link'}));
		});

		//Video Enter Picture-in-Picture //https://caniuse.com/#feat=picture-in-picture
		nebula.dom.document.on('enterpictureinpicture', 'video', function(e){
			let thisEvent = {
				event: e,
				category: 'Videos',
				action: 'Enter Picture-in-Picture', //GA4 Name: "video_pip"?
				videoID: e.target.id
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.videoID, {'nonInteraction': true}); //Non-interaction because this may not be triggered by the user.
		});

		//Video Leave Picture-in-Picture
		nebula.dom.document.on('leavepictureinpicture', 'video', function(e){
			let thisEvent = {
				event: e,
				category: 'Videos',
				action: 'Leave Picture-in-Picture', //GA4 Name: "video_pip"?
				videoID: e.target.id
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.videoID, {'nonInteraction': true}); //Non-interaction because this may not be triggered by the user.
		});

		//Page Visibility
		nebula.dom.document.on('visibilitychange', function(e){
			let thisEvent = {
				event: e,
				category: 'Visibility Change',
				action: document.visibilityState, //Hidden, Visible, Prerender, or Unloaded
				label: 'The state of the visibility of this page has changed.'
			};

			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label, {'nonInteraction': true}); //Non-interaction because these are not interactions with the website itself
		});

		//Word copy tracking
		let copyCount = 0;
		nebula.dom.document.on('cut copy', function(){
			let selection = window.getSelection().toString().trim();

			if ( selection ){
				let words = selection.split(' ');
				let wordsLength = words.length;

				//Track Email or Phone copies as contact intent.
				if ( nebula.regex.email.test(selection) ){
					let thisEvent = {
						category: 'Contact',
						action: 'Email (Copy)', //GA4 Name: "mailto"?
						intent: 'Intent',
						emailAddress: selection,
						words: words,
						wordcount: wordsLength
					};

					ga('set', nebula.analytics.dimensions.contactMethod, 'Mailto');
					ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
					nebula.dom.document.trigger('nebula_event', thisEvent);
					ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.emailAddress);
					window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-copied-email'}));
					nebula.crm('event', 'Email Address Copied');
					nebula.crm('identify', {mailto_contacted: thisEvent.emailAddress});
				} else if ( nebula.regex.address.test(selection) ){
					let thisEvent = {
						category: 'Contact',
						action: 'Street Address (Copy)',
						intent: 'Intent',
						address: selection,
						words: words,
						wordcount: wordsLength
					};

					ga('set', nebula.analytics.dimensions.contactMethod, 'Street Address');
					ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
					nebula.dom.document.trigger('nebula_event', thisEvent);
					ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.address);
					window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-copied-address'}));
					nebula.crm('event', 'Street Address Copied');
				} else {
					let alphanumPhone = selection.replaceAll(/\W/g, ''); //Keep only alphanumeric characters
					let firstFourNumbers = parseInt(alphanumPhone.substring(0, 4)); //Store the first four numbers as an integer

					//If the first three/four chars are numbers and the full string is either 10 or 11 characters (to capture numbers with words) -or- if it matches the phone RegEx pattern
					if ( (!isNaN(firstFourNumbers) && firstFourNumbers.toString().length >= 3 && (alphanumPhone.length === 10 || alphanumPhone.length === 11)) || nebula.regex.phone.test(selection) ){
						let thisEvent = {
							category: 'Contact',
							action: 'Phone (Copy)', //GA4 Name: "click_to_call"?
							intent: 'Intent',
							phoneNumber: selection,
							words: words,
							wordcount: wordsLength
						};

						ga('set', nebula.analytics.dimensions.contactMethod, 'Click-to-Call');
						ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
						nebula.dom.document.trigger('nebula_event', thisEvent);
						ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.phoneNumber);
						window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-copied-phone'}));
						nebula.crm('event', 'Phone Number Copied');
						nebula.crm('identify', {phone_contacted: thisEvent.phoneNumber});
					}
				}

				//Send the regular copied text event since it does not contain contact information
				let thisEvent = {
					category: 'Copied Text',
					action: 'Copy', //This is not used for the below events //GA4 Name: "copy_text"?
					intent: 'Intent',
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

			ga('send', 'exception', {'exDescription': '(JS) AJAX Error (' + jqXHR.status + '): ' + errorMessage + ' on ' + settings.url, 'exFatal': true});
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
							ga('send', 'exception', {'exDescription': '(JS) Reporting Observer [' + report.type + ']: ' + report.body.message + ' in ' + report.body.sourceFile + ' on line ' + report.body.lineNumber, 'exFatal': false});
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
				category: 'Print',
				action: action, //GA4 Name: "print"?
				label: 'User triggered print via ' + trigger,
				intent: 'Intent'
			};

			ga('set', nebula.analytics.dimensions.eventIntent, thisEvent.intent);
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-print'}));
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.action);}
			nebula.crm('event', thisEvent.category);
		}

		//Note: This sends 2 events per print (beforeprint and afterprint). If one occurs more than the other we can remove one.
		window.matchMedia('print').addListener(function(mql){
			if ( mql.matches ){
				sendPrintEvent('Before Print', 'mql.matches');
			} else {
				sendPrintEvent('After Print', '!mql.matches');
			}
		});

		//Detect Adblock
		if ( nebula.user.client.bot === false && nebula.site.options.adblock_detect ){ //If not a bot and adblock detection is active
			window.performance.mark('(Nebula) Detect AdBlock [Start]');

			//Attempt to retrieve a fake ad file
			fetch(nebula.site.directory.template.uri + '/assets/js/vendor/autotrack.js', { //This is not the real autotrack library
				importance: 'low',
				cache: 'force-cache'
			}).then(function(response){
				nebula.session.flags.adblock = false;
			}).catch(function(error){
				nebula.dom.html.addClass('ad-blocker');
				ga('set', nebula.analytics.dimensions.blocker, 'Ad Blocker');
				if ( nebula.session.flags.adblock !== true ){ //If this is the first time blocking it, log it
					ga('send', 'event', 'Ad Blocker', 'Blocked', 'This user is using ad blocking software.', {'nonInteraction': true}); //Uses an event because it is asynchronous!
					window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-adblock-detected'}));
					nebula.session.flags.adblock = true;
				}
			}).finally(function(){
				window.performance.mark('(Nebula) Detect AdBlock [End]');
				window.performance.measure('(Nebula) Detect AdBlock', '(Nebula) Detect AdBlock [Start]', '(Nebula) Detect AdBlock [End]');
			});
		}

		//DataTables Filter
		nebula.dom.document.on('keyup', '.dataTables_filter input', function(e){
			let oThis = jQuery(this);
			let thisEvent = {
				event: e,
				category: 'DataTables',
				action: 'Search Filter', //GA4 Name: "search"?
				query: oThis.val().toLowerCase().trim()
			};

			nebula.debounce(function(){
				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.query);
				window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-datatables'}));
			}, 1000, 'datatables_search_filter');
		});

		//DataTables Sorting
		nebula.dom.document.on('click', 'th.sorting', function(e){
			let thisEvent = {
				event: e,
				category: 'DataTables',
				action: 'Sort', //GA4 Name: "datatables_sort"?
				heading: jQuery(this).text()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.heading);
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-datatables'}));
		});

		//DataTables Pagination
		nebula.dom.document.on('click', 'a.paginate_button ', function(e){
			let thisEvent = {
				event: e,
				category: 'DataTables',
				action: 'Paginate', //GA4 Name: "datatables_paginate"?
				page: jQuery(this).text()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.page);
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-datatables'}));
		});

		//DataTables Show Entries
		nebula.dom.document.on('change', '.dataTables_length select', function(e){
			let thisEvent = {
				event: e,
				category: 'DataTables',
				action: 'Shown Entries Change', //Number of visible rows select dropdown
				selected: jQuery(this).val()
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.selected);
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
				category: 'Ecommerce',
				action: 'Add to Cart', //GA4 Name: "add_to_cart"
				product: jQuery(this).attr('data-product_id')
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.product);
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-add-to-cart'}));
			if ( typeof fbq === 'function' ){fbq('track', 'AddToCart');}
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.action);}
			nebula.crm('event', 'Ecommerce Add to Cart');
		});

		//Update cart clicks
		nebula.dom.document.on('click', '.button[name="update_cart"]', function(e){
			let thisEvent = {
				event: e,
				category: 'Ecommerce',
				action: 'Update Cart Button',
				label: 'Update Cart button click'
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-update-cart'}));
			nebula.crm('event', 'Ecommerce Update Cart');
		});

		//Product Remove buttons
		nebula.dom.document.on('click', '.product-remove a.remove', function(e){
			let thisEvent = {
				event: e,
				category: 'Ecommerce',
				action: 'Remove This Item', //GA4 Name: "remove_from_cart"
				product: jQuery(this).attr('data-product_id')
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.product);
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-remove-item'}));
			nebula.crm('event', 'Ecommerce Remove From Cart');
		});

		//Proceed to Checkout
		nebula.dom.document.on('click', '.wc-proceed-to-checkout .checkout-button', function(e){
			let thisEvent = {
				event: e,
				category: 'Ecommerce',
				action: 'Proceed to Checkout Button', //GA4 Name: "begin_checkout"
				label: 'Proceed to Checkout button click'
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-proceed-to-checkout'}));
			if ( typeof fbq === 'function' ){fbq('track', 'InitiateCheckout');}
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.action);}
			nebula.crm('event', 'Ecommerce Proceed to Checkout');
		});

		//Checkout form timing
		nebula.dom.document.on('click focus', '#billing_first_name', function(e){
			nebula.timer('(Nebula) Ecommerce Checkout', 'start');

			let thisEvent = {
				event: e,
				category: 'Ecommerce',
				action: 'Started Checkout Form', //GA4 Name: "checkout_progress"?
				label: 'Began filling out the checkout form (Billing First Name)'
			};

			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-started-checkout-form'}));
			nebula.crm('event', 'Ecommerce Started Checkout Form');
		});

		//Place order button
		nebula.dom.document.on('click', '#place_order', function(e){
			let thisEvent = {
				event: e,
				category: 'Ecommerce',
				action: 'Place Order Button', //GA4 Name: "purchase"
				label: 'Place Order button click'
			};

			ga('send', 'timing', 'Ecommerce', 'Checkout Form', Math.round(nebula.timer('(Nebula) Ecommerce Checkout', 'end')), 'Billing details start to Place Order button click');
			nebula.dom.document.trigger('nebula_event', thisEvent);
			ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label);
			window.dataLayer.push(Object.assign(thisEvent, {'event': 'nebula-place-order-button'}));
			if ( typeof fbq === 'function' ){fbq('track', 'Purchase');}
			if ( typeof clarity === 'function' ){clarity('set', thisEvent.category, thisEvent.action);}
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
						category: 'Scroll Depth',
						action: 'Began Scrolling',
						scrollStart: nebula.dom.window.scrollTop() + 'px',
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
							let thisEvent = {
								category: 'Scroll Depth',
								action: 'Excessive Scrolling',
								label: 'User scrolled ' + excessiveScrollThreshold + 'px (or more) on this page.',
							};
							nebula.dom.document.trigger('nebula_event', thisEvent);
							ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label, {'nonInteraction': true});
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

		//Track when the user reaches the end of the content
		if ( jQuery('#footer-section').length ){
			let footerObserver = new IntersectionObserver(function(entries){
				entries.forEach(function(entry){
					if ( entry.intersectionRatio > 0 ){
						let thisEvent = {
							category: 'Scroll Depth',
							action: 'Reached Footer',
							label: 'The footer of the page scrolled into the viewport'
						};

						nebula.dom.document.trigger('nebula_event', thisEvent);
						ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.label, {'nonInteraction': true});

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

		//if ( nebula.analytics.metrics.maxScroll ){ //Trying this for all visitors, but may limit to this custom metric if too many events...
			window.addEventListener('beforeunload', function(e){ //Watch for the unload to send max scroll depth to GA (to avoid tons of events). Note: this event listener invalidates BFCache in Firefox...
				nebula.updateMaxScrollDepth(); //Check one last time

				let thisEvent = {
					category: 'Scroll Depth',
					action: 'Max Scroll Depth',
					maxScrollPixels: nebula.maxScrollDepth,
					maxScrollPercent: Math.round(100*(nebula.maxScrollDepth/(nebula.dom.document.height()-window.innerHeight))) //Round to the nearest percent
				};

				thisEvent.description = 'The user reached a maximum scroll depth of ' + thisEvent.maxScrollPercent + '% (' + thisEvent.maxScrollPixels + 'px) when the page was unloaded.';

				nebula.dom.document.trigger('nebula_event', thisEvent);
				ga('set', nebula.analytics.metrics.maxScroll, thisEvent.maxScrollPercent); //Set the custom metric to the max percent
				ga('send', 'event', thisEvent.category, thisEvent.action, thisEvent.description, {'nonInteraction': true}); //Ideally this would send only once per page per session– and only if that page hadn't reached 100% previously in the session... Consider localstorage
			});
		//}
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
		_hsq.push(['trackEvent', data]);

		_hsq.push(['setPath', window.location.href.replace(nebula.site.directory.root, '') + '#virtual-pageview/' + data]);
		let oldTitle = document.title;
		document.title = document.title + ' (Virtual)';
		_hsq.push(['trackPageView']);
		document.title = oldTitle;
	}

	nebula.dom.document.trigger('crm_data', data);
};

//Easily send form data to nebula.crm() with crm-* classes
//Add a class to the input field with the category to use. Ex: crm-firstname or crm-email or crm-fullname
//Call this function before sending a ga() event because it sets dimensions too
nebula.crmForm = async function(formID){
	let crmFormObj = {};

	if ( formID ){
		crmFormObj['form_contacted'] = 'CF7 (' + formID + ') Submit Attempt'; //This is triggered on submission attempt, so it may capture abandoned forms due to validation errors.
	}

	jQuery('form [class*="crm-"]').each(function(){
		if ( jQuery(this).val().trim().length ){
			if ( jQuery(this).attr('class').includes('crm-notable_poi') ){
				ga('set', nebula.analytics.dimensions.poi, jQuery('.notable-poi').val());
			}

			let cat = /crm-([a-z\_]+)/g.exec(jQuery(this).attr('class'));
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