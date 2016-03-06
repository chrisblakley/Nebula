jQuery.noConflict();

/*==========================
 DOM Ready
 ===========================*/

jQuery(document).ready(function(){
	getQueryStrings();
	if ( get('killall') || get('kill') || get('die') ){
		throw new Error('(Manually terminated main.js)');
	} else if ( get('layout') ){
		[].forEach.call(jQuery("*"),function(a){a.style.outline="1px solid #"+(~~(Math.random()*(1<<24))).toString(16)});
	}

	globalVariables();
	gaBlockDetect();
	initSessionInfo();

	//Social
	facebookSDK();
	facebookConnect();
	prefillFacebookFields();
	socialSharing();

	//Navigation
	mmenus();
	//jQuery('#primarynav .menu-item-has-children').doubleTapToGo(); //@TODO "Mobile": Either use mmenu or uncomment this line for mobile navigation.
	dropdownWidthController();
	overflowDetector();
	subnavExpanders();
	nebulaPrerenderListeners();
	menuSearchReplacement();

	//Search
	wpSearchInput();
	mobileSearchPlaceholder();
	autocompleteSearch();
	advancedSearchTriggers();
	searchValidator();
	searchTermHighlighter();
	singleResultDrawer();
	pageSuggestion();

	//Forms
	cf7Functions();
	cf7LiveValidator();
	cf7LocalStorage();
	prefillCommentAuthorCookieFields(cookieAuthorName, cookieAuthorEmail);
	nebulaAddressAutocomplete('#address-autocomplete');

	//Helpers
	addHelperClasses();
	errorMitigation();
	powerFooterWidthDist();
	nebulaEqualize();
	nebulaScrollTo();
	svgImgs();

	//Interaction
	windowTypeDetection();
	gaEventTracking();
	scrollDepth();
	pageVisibility();
	checkForYoutubeVideos();
	vimeoControls();
	animationTriggers();

	conditionalJSLoading();

	if ( jQuery('.home.page').is('*') ){
		initHeadroom(jQuery('#heroslidercon'));
	} else {
		initHeadroom();
	}

	jQuery('span.nebula-code').parent('p').css('margin-bottom', '0px'); //Fix for <p> tags wrapping Nebula pre spans in the WYSIWYG
	jQuery('.wpcf7-captchar').attr('title', 'Not case-sensitive');
	window.lastWindowWidth = nebula.dom.window.width();
	createCookie('nebulaUser', JSON.stringify(nebula.user));
}); //End Document Ready


/*==========================
 Window Load
 ===========================*/

jQuery(window).on('load', function(){
	//Focus on hero search field on load and hover.
	jQuery('#nebula-hero-search input').focus().on('mouseover', function(){
		if ( !jQuery('input:focus').is('*') ){
			jQuery(this).focus();
		}
	});

	jQuery('a, li, tr').removeClass('hover');
	nebula.dom.html.addClass('loaded');

	if ( typeof performance !== 'undefined' ){
		setTimeout(function(){
			var perceivedLoad = performance.timing.loadEventEnd-performance.timing.navigationStart;
			var actualLoad = performance.timing.loadEventEnd-performance.timing.responseEnd;
			ga('send', 'timing', 'Performance Timing', 'Perceived Load', Math.round(perceivedLoad), 'Navigation start to window load');
			ga('send', 'timing', 'Performance Timing', 'Actual Load', Math.round(actualLoad), 'Server response until window load');

			nebula.dom.html.addClass('lt-per_' + perceivedLoad + 'ms');
			nebula.dom.html.addClass('lt-act_' + actualLoad + 'ms');
			debugInfo();
		}, 0);
	} else {
		nebula.dom.html.addClass('lt_unavailable');
		debugInfo();
	}

	setTimeout(function(){
		emphasizeSearchTerms();
	}, 1000);
}); //End Window Load


/*==========================
 Window Resize
 ===========================*/

jQuery(window).on('resize', function(){
	debounce(function(){
    	powerFooterWidthDist();
		nebulaEqualize();
		mobileSearchPlaceholder();

		if ( jQuery('.home.page').is('*') ){
			initHeadroom(jQuery('#heroslidercon'));
		} else {
			initHeadroom();
		}

    	//Track size change
    	if ( window.lastWindowWidth > nebula.dom.window.width() ){
    		ga('set', gaCustomDimensions['timestamp'], localTimestamp());
    		ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Reduced Window Width'));
    		ga('send', 'event', 'Window Resize', 'Smaller', window.lastWindowWidth + 'px to ' + nebula.dom.window.width() + 'px');
    	} else if ( window.lastWindowWidth < nebula.dom.window.width() ){
    		ga('set', gaCustomDimensions['timestamp'], localTimestamp());
    		ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Enlarged Window Width'));
    		ga('send', 'event', 'Window Resize', 'Bigger', window.lastWindowWidth + 'px to ' + nebula.dom.window.width() + 'px');
    	}
    	window.lastWindowWidth = nebula.dom.window.width();
	}, 500, 'window resize');
}); //End Window Resize

//Cache common selectors and set consistent regex patterns
function globalVariables(){
	//Selectors
	nebula.dom = {
		window: jQuery(window),
		document: jQuery(document),
		html: jQuery('html'),
		body: jQuery('body')
	};

	//Regex Patterns
	//Test with: if ( regexPattern.email.test(jQuery('input').val()) ){ ... }
	window.regexPattern = {
		email: /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/, //From JS Lint: Expected ']' and instead saw '['.
		phone: /^(?:(?:\+?1\s*(?:[.-]\s*)?)?(?:\(\s*([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9])\s*\)|([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9]))\s*(?:[.-]\s*)?)?([2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)?([0-9]{4})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?$/,
		date: {
			mdy: /^((((0[13578])|([13578])|(1[02]))[.\/-](([1-9])|([0-2][0-9])|(3[01])))|(((0[469])|([469])|(11))[.\/-](([1-9])|([0-2][0-9])|(30)))|((2|02)[.\/-](([1-9])|([0-2][0-9]))))[.\/-](\d{4}|\d{2})$/,
			ymd: /^(\d{4}|\d{2})[.\/-]((((0[13578])|([13578])|(1[02]))[.\/-](([1-9])|([0-2][0-9])|(3[01])))|(((0[469])|([469])|(11))[.\/-](([1-9])|([0-2][0-9])|(30)))|((2|02)[.\/-](([1-9])|([0-2][0-9]))))$/,
		},
		hex: /^#?([a-f0-9]{6}|[a-f0-9]{3})$/,
		ip: /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/,
	};
}


/*==========================
 Detection Functions
 ===========================*/

//Detect notable aspects of the way the site was loaded.
function windowTypeDetection(){
	//Detect if loaded in an iframe
	if ( window != window.parent ){
		nebula.dom.html.addClass('in-iframe');
		if ( window.parent.location.toString().indexOf('wp-admin') === -1 ){
			ga('set', gaCustomDimensions['timestamp'], localTimestamp());
			ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Iframe'));
			ga('send', 'event', 'Iframe', 'Loaded within: ' + window.parent.location, {'nonInteraction': 1});
		}
		//Break out of the iframe when link is clicked.
		jQuery('a').each(function(){
			if ( jQuery(this).attr('href') != '#' ){
				jQuery(this).attr('target', '_parent');
			}
		});
	}

	//Detect if loaded from the homescreen ("installed" as an app)
	if ( navigator.standalone || get('hs') ){
		//alert('loaded from hs'); //@TODO "Nebula" 0: Query string (in manifest) is not working, so this detection method doesn't work.
		ga('set', gaCustomDimensions['timestamp'], localTimestamp());
		ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Homescreen App'));
		ga('send', 'event', 'Standalone', 'Loaded as a standalone app from the home screen.', {'nonInteraction': 1});
	}
}

//Create an object of the viewport dimensions
function updateViewportDimensions(){
	if ( typeof viewport === 'undefined' ){
		var viewportHistory = 0;
		//console.log('creating viewport History: ' + viewportHistory);
	} else {
		var viewportHistory = viewport.history+1;
		viewport.prevWidth = viewport.width; //Not pushing to the object...
		viewport.prevHeight = viewport.height; //Not pushing to the object...
		//console.log('increasing viewport History: ' + viewportHistory); //Triggering twice on window resize...
	}

	var x = nebula.dom.window.innerWidth || nebula.dom.document.documentElement.clientWidth || nebula.dom.body.clientWidth;
	var y = nebula.dom.window.innerHeight || nebula.dom.document.documentElement.clientHeight || nebula.dom.body.clientHeight;

	if ( viewportHistory === 0 ){
		var viewportObject = {
			initialWidth: x,
			initialHeight: y,
			width: x,
			height: y,
			history: viewportHistory
		};
	} else {
		viewportObject = {
		    initialWidth: viewport.initialWidth,
			initialHeight: viewport.initialHeight,
		    width: x,
		    height: y,
		    history: viewportHistory
		};
	}
	return viewportObject;
}

//Detect user flow around website.
function initSessionInfo(){
	if ( typeof sessionStorage['nebulaSession'] === 'undefined' ){
		nebula.session.referrer = document.referrer.replace(/"|%22/g, '');
		nebula.session.history = [window.location.href.replace(/"|%22/g, '')];
	} else {
		nebula.session = JSON.parse(sessionStorage['nebulaSession']);

		if ( nebula.session.history && document.referrer && document.referrer.indexOf(nebula.site.domain) < 0 ){ //If user navigated away and came back.
			nebula.session.history.push('---Returned from: ' + document.referrer.replace(/"|%22/g, ''));
		}

		if ( nebula.session.history && window.location.href != nebula.session.history[nebula.session.history.length-1] ){ //Disregard page refreshes
			nebula.session.history.push(window.location.href.replace(/"|%22/g, ''));
		}
	}

	sessionStorage['nebulaSession'] = JSON.stringify(nebula.session);
}

//Fill debugInfo field with browser information (to send with forms).
function debugInfo(){
	var debugInfoVal = '';

	formID = jQuery('div.wpcf7').attr('id');
	if ( typeof nebulaTimings !== 'undefined' && typeof nebulaTimings[formID] !== 'undefined' ){
		debugInfoVal += 'Field Timings:\nhttp://jsonprettyprint.com/\n';
		debugInfoVal += JSON.stringify(nebulaTimings[formID], ['lap', 'name', 'duration', 'cumulative', 'total']) + '\n\n';
	}

	if ( typeof navigator !== 'undefined' ){
		debugInfoVal += 'User Agent: ' + navigator.userAgent + '\n';
		debugInfoVal += 'http://udger.com/resources/online-parser\n\n';
	} else {
		debugInfoVal += 'User Agent: ' + nebula.user.client.userAgent + '\n';
		debugInfoVal += 'http://udger.com/resources/online-parser\n\n';
	}

	if ( typeof nebula.user.client !== 'undefined' ){
		var fullDevice = '';
		if ( nebula.user.client.device.full.trim().length ){
			var fullDevice = ' (' + nebula.user.client.device.full + ')';
		}
		debugInfoVal += 'Device: ' + nebula.user.client.device.type + fullDevice + '\n';
		debugInfoVal += 'Operating System: ' + nebula.user.client.os.full + '\n';
		debugInfoVal += 'Browser: ' + nebula.user.client.browser.full + ' (' + nebula.user.client.browser.engine + ')\n';
	}

	debugInfoVal += 'HTML Classes: ' + nebula.dom.html.attr('class').split(' ').sort().join(', ') + '\n\n';
	debugInfoVal += 'Body Classes: ' + nebula.dom.body.attr('class').split(' ').sort().join(', ') + '\n\n';
	debugInfoVal += 'Viewport Size: ' + nebula.dom.window.width() + 'px x ' + nebula.dom.window.height() + 'px ' + '\n\n';

	if ( once('debug info') ){
		if ( typeof performance !== 'undefined' ){
			debugInfoVal += 'Redirects: ' + performance.navigation.redirectCount + '\n';
			var perceivedLoadTime = (performance.timing.loadEventEnd-performance.timing.navigationStart)/1000;
			var actualLoadTime = (performance.timing.loadEventEnd-performance.timing.responseEnd)/1000;
			debugInfoVal += 'Perceived Load Time: ' + perceivedLoadTime + 's' + '\n';
			debugInfoVal += 'Actual Page Load Time: ' + actualLoadTime + 's' + '\n\n';
		} else {
			debugInfoVal += 'Page load time not available.\n\n';
		}

		if ( nebula.session.id ){
			debugInfoVal += 'Current Session ID: ' + nebula.session.id + '\n';
		}
		if ( nebula.session.referrer.length ){
			debugInfoVal += 'Original Referrer: ' + nebula.session.referrer + '\n';
		} else {
			debugInfoVal += 'Original Referrer: (Direct or Unknown)\n';
		}

		if ( typeof window.history !== 'undefined' ){
			debugInfoVal += 'History Depth: ' + window.history.length + '\n';
		}

		if ( nebula.session.history ){
			jQuery.each(nebula.session.history, function(i){
				if ( nebula.session.history.length > 10 && i < 10 ){
					return true;
				}
				debugInfoVal += (i+1) + '.) ' + nebula.session.history[i] + '\n';
			});
			debugInfoVal += '\n';
		}

		if ( typeof sessionStorage['nebulaSession'] !== 'undefined' && sessionStorage['nebulaSession'].length ){
			debugInfoVal += 'Session Notes: ' + sessionNote('return') + '\n';
		}

		if ( typeof adBlockUser !== 'undefined' ){
			debugInfoVal += 'Ads: ' + adBlockUser + '\n';
		}

		if ( typeof nebula.user.client.businessopen !== 'undefined' ){
			debugInfoVal += ( nebula.user.client.businessopen )? 'During Business Hours\n\n' : 'Non-Business Hours\n\n';
		}
	}

	if ( typeof nebula.user !== 'undefined' ){
		debugInfoVal += 'User:\n';
		debugInfoVal += JSON.stringify(nebula.user);
		debugInfoVal += '\n\n';
	}

	if ( typeof nebula.session.geolocation !== 'undefined' && nebula.session.geolocation != '' ){
		if ( !nebula.session.geolocation.error ){
			debugInfoVal += 'Geolocation: ' + nebula.session.geolocation.coordinates.latitude + ', ' + nebula.session.geolocation.coordinates.longitude + '\n';
			debugInfoVal += 'Accuracy: ' + nebula.session.geolocation.accuracy.meters + ' meters (' + nebula.session.geolocation.accuracy.miles + ' miles)\n';
			debugInfoVal += 'https://www.google.com/maps/place/' + nebula.session.geolocation.coordinates.latitude + ',' + nebula.session.geolocation.coordinates.longitude + '\n\n';
		} else {
			debugInfoVal += 'Geolocation Error: ' + nebula.session.geolocation.error.description + '\n\n';
		}
	}

	debugInfoVal += 'IP Address: ' + nebula.user.client.remote_addr + '\n';
	debugInfoVal += 'http://whatismyipaddress.com/ip/' + nebula.user.client.remote_addr + '\n\n';

	jQuery('textarea.debuginfo').addClass('hidden').css('display', 'none').val(debugInfoVal); //Store the data into the debug textarea
}

//Page Visibility
function pageVisibility(){
	visFirstHidden = false;
	visibilityChangeActions();
	nebula.dom.document.on('visibilitychange', function(){
		visibilityChangeActions();
	});

	function visibilityChangeActions(){
		var pageTitle = nebula.dom.document.attr('title');

		if ( document.visibilityState === 'prerender' ){ //Page was prerendered/prefetched
			ga('set', gaCustomDimensions['timestamp'], localTimestamp());
			ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Prerendered'));
			ga('send', 'event', 'Page Visibility', 'Prerendered', pageTitle, {'nonInteraction': 1});
			pauseAllVideos(false);
		}

		if ( getPageVisibility() ){ //Page is hidden
			nebula.dom.document.trigger('nebula_page_hidden');
			nebula.dom.body.addClass('page-visibility-hidden');
			nebulaTimer('pageVisibilityHidden', 'start');
			ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Page Visibility'));
			ga('send', 'event', 'Page Visibility', 'Hidden', pageTitle, {'nonInteraction': 1});
			pauseAllVideos(false);
			visFirstHidden = true;
		} else { //Page is visible
			if ( visFirstHidden ){
				nebula.dom.document.trigger('nebula_page_visible');
				nebula.dom.body.removeClass('page-visibility-hidden');
				//ga('send', 'timing', 'Page Visibility', 'Tab Hidden', Math.round(nebulaTimer('pageVisibilityHidden', 'lap')), 'Page in background tab for this time'); //Uncomment if this timing data is useful. GA limits timings, so disabled in favor of more important default timings.
				ga('send', 'event', 'Page Visibility', 'Visibile', pageTitle, {'nonInteraction': 1});
			}
		}
	}

	function getPageVisibility(){
		if ( typeof document.hidden !== "undefined" ){
			return document.hidden;
		} else {
			return false;
		}
	}
}

//Sub-menu viewport overflow detector
function overflowDetector(){
    jQuery('#primarynav .menu > .menu-item').hover(function(){
    	var viewportWidth = nebula.dom.window.width();
    	var submenuLeft = jQuery(this).offset().left;
    	var submenuRight = submenuLeft+jQuery(this).children('.sub-menu').width();
    	if ( submenuRight > viewportWidth ){
			jQuery(this).children('.sub-menu').css('left', 'auto').css('right', '0');
    	} else {
			jQuery(this).children('.sub-menu').css('left', '0').css('right', 'auto');
    	}
    }, function(){
	    	jQuery(this).children('.sub-menu').css('left', '-9999px').css('right', 'auto');
    });
}

//Detect GA blocking
function gaBlockDetect(){
	if ( !nebula.user.client.bot ){
		gablocked = true;
		ga(function(){
			gablocked = false;
		});

		setTimeout(function(){
			if ( gablocked ){
				nebula.user.client.capabilities.gablock = true;
				createCookie('nebulaUser', JSON.stringify(nebula.user));
				jQuery('html').addClass('no-gajs');
				jQuery.ajax({
					type: "POST",
					url: nebula.site.ajax.url,
					data: {
						nonce: nebula.site.ajax.nonce,
						action: 'nebula_ga_blocked',
						data: [{
							id: ( nebula.post )? nebula.post.id : false,
						}],
					}
				});

				function ga(send, event, category, action, label, value, misc){
					if ( send === 'send' && event === 'event' ){
						var ni = 0;
						if ( misc && misc.nonInteraction === 1 ){
							ni = 1;
						}

						jQuery.ajax({ //test success
							type: "POST",
							url: nebula.site.ajax.url,
							data: {
								nonce: nebula.site.ajax.nonce,
								action: 'nebula_ga_event_ajax',
								data: [{
									id: ( nebula.post )? nebula.post.id : false,
									category: category,
									action: action,
									label: label,
									value: value,
									ni: ni,
								}],
							}
						});
					}
				}
			}
		}, 1000);
	}
}


/*==========================
 Social Functions
 ===========================*/

//Load the SDK asynchronously
function facebookSDK(){
	(function(d, s, id){
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) return;
		js = d.createElement(s); js.id = id;
		js.src = "//connect.facebook.net/en_US/all.js";
		fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));
}

//Facebook Connect functions
function facebookConnect(){
	window.fbConnectFlag = false;
	if ( nebula.site.options.facebook_app_id ){
		window.fbAsyncInit = function(){
			FB.init({
				appId: nebula.site.options.facebook_app_id,
				channelUrl: nebula.site.directory.template.uri + '/includes/channel.php',
				status: true,
				xfbml: true
			});

			checkFacebookStatus();
			FB.Event.subscribe('edge.create', function(href, widget){ //Facebook Likes
				ga('set', gaCustomDimensions['timestamp'], localTimestamp());
				ga('set', gaCustomDimensions['sessionNotes'], sessionNote('FB Liked'));
				ga('send', {'hitType': 'social', 'socialNetwork': 'Facebook', 'socialAction': 'Like', 'socialTarget': href, 'page': nebula.dom.document.attr('title')});
				ga('send', 'event', 'Social', 'Facebook Like');
				nebulaConversion('facebook', 'like');
			});

			FB.Event.subscribe('edge.remove', function(href, widget){ //Facebook Unlikes
				ga('set', gaCustomDimensions['timestamp'], localTimestamp());
				ga('set', gaCustomDimensions['sessionNotes'], sessionNote('FB Unliked'));
				ga('send', {'hitType': 'social', 'socialNetwork': 'Facebook', 'socialAction': 'Unlike', 'socialTarget': href, 'page': nebula.dom.document.attr('title')});
				ga('send', 'event', 'Social', 'Facebook Unlike');
				nebulaConversion('facebook', 'like', 'remove');
			});

			FB.Event.subscribe('message.send', function(href, widget){ //Facebook Send/Share
				ga('set', gaCustomDimensions['timestamp'], localTimestamp());
				ga('set', gaCustomDimensions['sessionNotes'], sessionNote('FB Share'));
				ga('send', {'hitType': 'social', 'socialNetwork': 'Facebook', 'socialAction': 'Send', 'socialTarget': href, 'page': nebula.dom.document.attr('title')});
				ga('send', 'event', 'Social', 'Facebook Share');
				nebulaConversion('facebook', 'share');
			});

			FB.Event.subscribe('comment.create', function(href, widget){ //Facebook Comments
				ga('set', gaCustomDimensions['timestamp'], localTimestamp());
				ga('set', gaCustomDimensions['sessionNotes'], sessionNote('FB Comment'));
				ga('send', {'hitType': 'social', 'socialNetwork': 'Facebook', 'socialAction': 'Comment', 'socialTarget': href, 'page': nebula.dom.document.attr('title')});
				ga('send', 'event', 'Social', 'Facebook Comment');
				nebulaConversion('facebook', 'comment');
			});
		};

		nebula.dom.document.on('click touch tap', '.facebook-connect', function(){
			facebookLoginLogout();
			return false;
		});
	} else {
		jQuery('.facebook-connect').remove();
	}
}

//Connect to Facebook without using Facebook Login button
function facebookLoginLogout(){
	if ( !nebula.user.facebook.status ){
		FB.login(function(response){
			checkFacebookStatus();
		}, {scope:'public_profile,email'});
	} else {
		FB.logout(function(response){
			checkFacebookStatus();
		});
	}
	return false;
}

//Fetch Facebook user information
function checkFacebookStatus(){
	FB.getLoginStatus(function(response){
		nebula.user.facebook = {'status': response.status}
		if ( nebula.user.facebook.status === 'connected' ){ //User is logged into Facebook and is connected to this app.
			FB.api('/me', function(response){
				//Update the Nebula User Facebook Object
				nebula.user.facebook = {
					id: response.id,
					name: {
						first: response.first_name,
						last: response.last_name,
						full: response.name,
					},
					gender: response.gender,
					email: response.email,
					image: {
						base: 'https://graph.facebook.com/' + response.id + '/picture',
						thumbnail: 'https://graph.facebook.com/' + response.id + '/picture?width=100&height=100',
						large: 'https://graph.facebook.com/' + response.id + '/picture?width=1000&height=1000',
					},
					url: response.link,
					location: {
						locale: response.locale,
						timezone: response.timezone,
					},
					verified: response.verified,
				}
				nebulaConversion('facebook', 'connect');

				//Update Nebula User Object
				nebula.user.name = {
					first: response.first_name,
					last: response.last_name,
					full: response.name,
				};
				nebula.user.gender = response.gender;
				nebula.user.email = response.email;
				nebula.user.location = {
					locale: response.locale,
					timezone: response.timezone,
				}

				ga('set', gaCustomDimensions['timestamp'], localTimestamp());
				ga('set', gaCustomDimensions['sessionNotes'], sessionNote('FB Connect'));
				ga('send', 'event', 'Social', 'Facebook Connect', nebula.user.facebook.id);
				nebula.dom.body.removeClass('fb-disconnected').addClass('fb-connected fb-' + nebula.user.facebook.id);
				createCookie('nebulaUser', JSON.stringify(nebula.user));
				nebula.dom.document.trigger('fbConnected');
			});
		} else if ( nebula.user.facebook.status === 'not_authorized' ){ //User is logged into Facebook, but has not connected to this app.
			nebulaConversion('facebook', 'connect', 'remove');
			nebula.dom.body.removeClass('fb-connected').addClass('fb-not_authorized');
			nebula.dom.document.trigger('fbNotAuthorized');
		} else { //User is not logged into Facebook.
			nebulaConversion('facebook', 'connect', 'remove');
			nebula.dom.body.removeClass('fb-connected').addClass('fb-disconnected');
			nebula.dom.document.trigger('fbDisconnected');
		}
	});
}

//Fill or clear form inputs with Facebook data
function prefillFacebookFields(){
	nebula.dom.document.on('fbConnected', function(){
		fbConnectFlag = true;

		jQuery('.fb-name, .comment-form-author input, input.name').each(function(){
			jQuery(this).val(nebula.user.facebook.name.full).trigger('keyup');
		});
		jQuery('.fb-first-name, input.first-name').each(function(){
			jQuery(this).val(nebula.user.facebook.name.first).trigger('keyup');
		});
		jQuery('.fb-last-name, input.last-name').each(function(){
			jQuery(this).val(nebula.user.facebook.name.last).trigger('keyup');
		});
		jQuery('.fb-email, .comment-form-email input, .wpcf7-email, input.email').each(function(){
			jQuery(this).val(nebula.user.facebook.email).trigger('keyup');
		});
		debugInfo();
	});

	nebula.dom.document.on('fbNotAuthorized fbDisconnected', function(){
		if ( fbConnectFlag ){ //If FB was actually logged in at some point.
			jQuery('.fb-form-name, .comment-form-author input, .cform7-name, .fb-form-email, .comment-form-email input, input[type="email"]').each(function(){
				jQuery(this).val('').trigger('keyup');
			});
		}
	});
}

function prefillCommentAuthorCookieFields(name, email){
	if ( cookieAuthorName ){
		jQuery('.fb-name, .comment-form-author input, input.name').each(function(){
			jQuery(this).val(name).trigger('keyup');
		});
		jQuery('.fb-email, .comment-form-email input, .wpcf7-email, input.email').each(function(){
			jQuery(this).val(email).trigger('keyup');
		});
	}
}

//Convert Twitter usernames, hashtags, and URLs to links.
function tweetLinks(tweet){
	var newString = tweet.replace(/(http(\S)*)/g, '<a href="' + "$1" + '" target="_blank">' + "$1" + '</a>'); //Links that begin with "http"
	newString = newString.replace(/#(([a-zA-Z0-9_])*)/g, '<a href="https://twitter.com/hashtag/' + "$1" + '" target="_blank">#' + "$1" + '</a>'); //Link hashtags
	newString = newString.replace(/@(([a-zA-Z0-9_])*)/g, '<a href="https://twitter.com/' + "$1" + '" target="_blank">@' + "$1" + '</a>'); //Link @username mentions
	return newString;
}

function googlePlusCallback(jsonParam){
	if ( jsonParam.state === 'on' ){
		nebulaConversion('google_plus', 'like');
		ga('set', gaCustomDimensions['timestamp'], localTimestamp());
		ga('set', gaCustomDimensions['sessionNotes'], sessionNote('G+ Liked'));
		ga('send', 'event', 'Social', 'Google+ Like');
	} else if ( jsonParam.state === 'off' ){
		nebulaConversion('google_plus', 'like', 'remove');
		ga('set', gaCustomDimensions['timestamp'], localTimestamp());
		ga('set', gaCustomDimensions['sessionNotes'], sessionNote('G+ Unliked'));
		ga('send', 'event', 'Social', 'Google+ Unlike');
	} else {
		ga('set', gaCustomDimensions['timestamp'], localTimestamp());
		ga('send', 'event', 'Social', 'Google+ [JSON Unavailable]');
	}
}

//Social sharing buttons
function socialSharing(){
    var loc = window.location;
    var title = nebula.dom.document.attr('title');
    var encloc = encodeURI(loc);
    var enctitle = encodeURI(title);
    jQuery('.fbshare').attr('href', 'http://www.facebook.com/sharer.php?u=' + encloc + '&t=' + enctitle).attr('target', '_blank').on('click tap touch', function(){
	    ga('set', gaCustomDimensions['eventIntent'], 'Intent');
	    ga('send', 'event', 'Social', 'Share', 'Facebook');
    });
    jQuery('.twshare').attr('href', 'https://twitter.com/intent/tweet?text=' + enctitle + '&url=' + encloc).attr('target', '_blank').on('click tap touch', function(){
	    ga('set', gaCustomDimensions['eventIntent'], 'Intent');
	    ga('send', 'event', 'Social', 'Share', 'Twitter');
    });
    jQuery('.gshare').attr('href', 'https://plus.google.com/share?url=' + encloc).attr('target', '_blank').on('click tap touch', function(){
	    ga('set', gaCustomDimensions['eventIntent'], 'Intent');
	    ga('send', 'event', 'Social', 'Share', 'Google+');
    });
    jQuery('.lishare').attr('href', 'http://www.linkedin.com/shareArticle?mini=true&url=' + encloc + '&title=' + enctitle).attr('target', '_blank').on('click tap touch', function(){
	    ga('set', gaCustomDimensions['eventIntent'], 'Intent');
	    ga('send', 'event', 'Social', 'Share', 'LinkedIn');
    });
    jQuery('.emshare').attr('href', 'mailto:?subject=' + title + '&body=' + loc).attr('target', '_blank').on('click tap touch', function(){
	    ga('set', gaCustomDimensions['eventIntent'], 'Intent');
	    ga('send', 'event', 'Social', 'Share', 'Email');
    });
}


/*==========================
 Analytics Functions
 ===========================*/

//Google Analytics Universal Analytics Event Trackers
function gaEventTracking(){
	//Example Event Tracker (Category and Action are required. If including a Value, it should be a rational number and not a string. Value could be an object of parameters like {'nonInteraction': 1, 'dimension1': 'Something', 'metric1': 82} Use deferred selectors.)
	//nebula.dom.document.on('mousedown', '.selector', function(e){
	//  eventIntent = ( e.which >= 2 )? 'Intent' : 'Explicit';
	//	ga('set', gaCustomDimensions['eventIntent'], eventIntent);
	//	ga('set', gaCustomDimensions['timestamp'], localTimestamp());
	//	ga('send', 'event', 'Category', 'Action', 'Label', Value, {'object_name_here': object_value_here}); //Object names include 'hitCallback', 'nonInteraction', and others
	//});

	//External links
	nebula.dom.document.on('mousedown touch tap', "a[rel*='external']", function(e){
		eventIntent = ( e.which >= 2 )? 'Intent' : 'Explicit';
		ga('set', gaCustomDimensions['eventIntent'], eventIntent);

		var linkText = jQuery(this).text();
		if ( linkText.trim() === '' ){
			if ( jQuery(this).find('img').attr('alt') ){
				linkText = jQuery(this).find('img').attr('alt');
			} else if ( jQuery(this).find('img').is('*') ){
				var filePath = jQuery(this).attr('src');
				linkText = jQuery(this).find('img').attr('src').substr(filePath.lastIndexOf("/")+1);
			} else if ( jQuery(this).find('img').attr('title') ){
				linkText = jQuery(this).find('img').attr('title');
			} else {
				linkText = '(unknown)';
			}
		}

		var destinationURL = jQuery(this).attr('href');
		ga('set', gaCustomDimensions['timestamp'], localTimestamp());
		ga('set', gaCustomDimensions['sessionNotes'], sessionNote('External Link'));
		ga('send', 'event', 'External Link', linkText, destinationURL);
	});

	//PDF View/Download
	nebula.dom.document.on('mousedown touch tap', "a[href$='.pdf']", function(e){
		eventIntent = ( e.which >= 2 )? 'Intent' : 'Explicit';
		ga('set', gaCustomDimensions['eventIntent'], eventIntent);
		var linkText = jQuery(this).text();
		var filePath = jQuery(this).attr('href');
		var fileName = filePath.substr(filePath.lastIndexOf("/")+1);
		ga('set', gaCustomDimensions['timestamp'], localTimestamp());
		ga('set', gaCustomDimensions['sessionNotes'], sessionNote('PDF View'));
		if ( linkText === '' || linkText.toLowerCase() === 'download' ){
			ga('send', 'event', 'PDF View', 'File: ' + fileName);
		} else {
			ga('send', 'event', 'PDF View', 'Text: ' + linkText);
		}
		if ( typeof fbq === 'function' ){fbq('track', 'ViewContent', {content_name: fileName});}
		nebulaConversion('pdf', fileName);
	});

	//Notable Downloads
	nebula.dom.document.on('mousedown touch tap', ".notable a, a.notable", function(e){
		var filePath = jQuery(this).attr('href');
		if ( filePath != '#' ){
			eventIntent = ( e.which >= 2 )? 'Intent' : 'Explicit';
			ga('set', gaCustomMetrics['notableDownloads'], 1);
			ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Notable Download'));
			var linkText = jQuery(this).text();
			var fileName = filePath.substr(filePath.lastIndexOf("/")+1);
			if ( linkText === '' || linkText.toLowerCase() === 'download' ){
				ga('send', 'event', 'Notable Download', 'File: ' + fileName);
			} else {
				ga('send', 'event', 'Notable Download', 'Text: ' + linkText);
			}
			if ( typeof fbq === 'function' ){fbq('track', 'ViewContent', {content_name: fileName});}
			nebulaConversion('download', fileName);
		}
	});

	//Generic Interal Search Tracking
	if ( get('s') || get('rs') ){
		var searchQuery = get('s') || get('rs');
		nebulaConversion('keywords', searchQuery);
	}
	nebula.dom.document.on('submit', '.search', function(){
		var searchQuery = jQuery(this).find('input[name="s"]').val();
		ga('set', gaCustomDimensions['timestamp'], localTimestamp());
		ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Internal Search'));
		ga('send', 'event', 'Internal Search', 'Submit', searchQuery);
		if ( typeof fbq === 'function' ){fbq('track', 'Search', {search_string: searchQuery});}
		nebulaConversion('keywords', searchQuery);
	});

	//Mailto link tracking
	nebula.dom.document.on('mousedown touch tap', 'a[href^="mailto"]', function(e){
		eventIntent = ( e.which >= 2 )? 'Intent' : 'Explicit';
		ga('set', gaCustomDimensions['eventIntent'], eventIntent);
		var emailAddress = jQuery(this).attr('href').replace('mailto:', '');
		ga('set', gaCustomDimensions['contactMethod'], 'Mailto');
		ga('set', gaCustomDimensions['timestamp'], localTimestamp());
		ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Mailto'));
		ga('send', 'event', 'Mailto', 'Email: ' + emailAddress);
		if ( typeof fbq === 'function' ){if ( typeof fbq === 'function' ){fbq('track', 'Lead', {content_name: 'Mailto',});}}
		nebulaConversion('contact', 'Email: ' + emailAddress);
	});

	//Telephone link tracking
	nebula.dom.document.on('mousedown touch tap', 'a[href^="tel"]', function(e){
		eventIntent = ( e.which >= 2 )? 'Intent' : 'Explicit';
		ga('set', gaCustomDimensions['eventIntent'], eventIntent);
		var phoneNumber = jQuery(this).attr('href');
		phoneNumber = phoneNumber.replace('tel:+', '');
		ga('set', gaCustomDimensions['contactMethod'], 'Click-to-Call');
		ga('set', gaCustomDimensions['timestamp'], localTimestamp());
		ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Click-to-Call'));
		ga('send', 'event', 'Click-to-Call', 'Phone Number: ' + phoneNumber);
		if ( typeof fbq === 'function' ){if ( typeof fbq === 'function' ){fbq('track', 'Lead', {content_name: 'Click-to-Call',});}}
		nebulaConversion('contact', 'Phone: ' + phoneNumber);
	});

	//SMS link tracking
	nebula.dom.document.on('mousedown touch tap', 'a[href^="sms"]', function(e){
		eventIntent = ( e.which >= 2 )? 'Intent' : 'Explicit';
		ga('set', gaCustomDimensions['eventIntent'], eventIntent);
		var phoneNumber = jQuery(this).attr('href');
		phoneNumber = phoneNumber.replace('sms:+', '');
		ga('set', gaCustomDimensions['contactMethod'], 'SMS');
		ga('set', gaCustomDimensions['timestamp'], localTimestamp());
		ga('set', gaCustomDimensions['sessionNotes'], sessionNote('SMS'));
		ga('send', 'event', 'Click-to-Call', 'SMS to: ' + phoneNumber);
		if ( typeof fbq === 'function' ){if ( typeof fbq === 'function' ){fbq('track', 'Lead', {content_name: 'SMS',});}}
		nebulaConversion('contact', 'SMS: ' + phoneNumber);
	});

	//Non-Linked Click Attempts
	jQuery('img').on('click tap touch', function(){
		if ( !jQuery(this).parents('a').length ){
			ga('set', gaCustomDimensions['timestamp'], localTimestamp());
			ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Non-Linked Click Attempt'));
			ga('send', 'event', 'Non-Linked Click Attempt', 'Image', jQuery(this).attr('src'));
		}
	});
	jQuery('.btn:not(input)').on('click tap touch', function(e){
		if ( e.target != this ){
			return; //Only continue if the button is clicked, but not the <a> link.
		}
		ga('set', gaCustomDimensions['timestamp'], localTimestamp());
		ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Non-Linked Click Attempt'));
		if ( jQuery(this).find('a').is('*') ){
			ga('send', 'event', 'Non-Linked Click Attempt', 'Button', jQuery(this).find('a').text());
		} else {
			ga('send', 'event', 'Non-Linked Click Attempt', 'Button', '(no <a> tag) ' + jQuery(this).text());
		}
	});

	//Word copy tracking
	var copyCount = 0;
	var copyOver = 0;
	nebula.dom.document.on('cut copy', function(){
		copyCount++;
		var words = [];
		var selection = window.getSelection() + '';
		words = selection.split(' ');
		wordsLength = words.length;
		ga('set', gaCustomDimensions['timestamp'], localTimestamp());

		//Track Email or Phone copies as contact intent.
		emailPhone = jQuery.trim(words.join(' '));
		if ( regexPattern.email.test(emailPhone) ){
			ga('set', gaCustomDimensions['contactMethod'], 'Mailto');
			ga('set', gaCustomDimensions['eventIntent'], 'Intent');
			ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Mailto'));
			ga('send', 'event', 'Contact', 'Copied email: ' + emailPhone);
			nebulaConversion('contact', 'Email (copied): ' + emailPhone);
		} else if ( regexPattern.phone.test(emailPhone) ){
			ga('set', gaCustomDimensions['contactMethod'], 'Click-to-Call');
			ga('set', gaCustomDimensions['eventIntent'], 'Intent');
			ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Click-to-Call'));
			ga('send', 'event', 'Click-to-Call', 'Copied phone: ' + emailPhone);
			nebulaConversion('contact', 'Phone (copied): ' + emailPhone);
		}

		if ( copyCount < 13 ){
			if ( words.length > 8 ){
				words = words.slice(0, 8).join(' ');
				ga('send', 'event', 'Copied Text', words + '... [' + wordsLength + ' words]');
			} else {
				if ( selection === '' || selection === ' ' ){
					ga('send', 'event', 'Copied Text', '[0 words]');
				} else {
					ga('send', 'event', 'Copied Text', selection);
					ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Copied Text'));
				}
			}
		} else {
			if ( copyOver === 0 ){
				ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Many Copies'));
				ga('send', 'event', 'Copied Text', '[Copy limit reached]');
			}
			copyOver = 1;
		}
	});

	//High amount of clicks
	var clickCount = 0;
	jQuery(document).on('click', function(e){
		if ( clickCount === 15 ){
			var elementID = e.target.id;
			var elementClasses = e.target.className.replace(/ /g, '.');
			var elementTag = e.target.tagName.toLowerCase();
			if ( elementID ){
				var selector = elementTag + '#' + elementID;
			} else if ( elementClasses ){
				var selector = elementTag + '.' + elementClasses;
			} else {
				var selector = elementTag;
			}

			ga('set', gaCustomDimensions['sessionNotes'], sessionNote('High Clicks'));
			ga('send', 'event', 'High Click Count', 'Clicked 15 (or more) times.', selector);
		}
		clickCount++;
	});

	//AJAX Errors
	nebula.dom.document.ajaxError(function(e, request, settings){
		ga('set', gaCustomDimensions['timestamp'], localTimestamp());
		ga('set', gaCustomDimensions['sessionNotes'], sessionNote('General AJAX Error'));
		ga('send', 'event', 'Error', 'AJAX Error', e.result + ' on: ' + settings.url, {'nonInteraction': 1});
		ga('send', 'exception', e.result, true);
	});

	//Capture Print Intent
	printed = 0;
	var afterPrint = function(){
		if ( printed === 0 ){
			printed = 1;
			ga('set', gaCustomDimensions['timestamp'], localTimestamp());
			ga('set', gaCustomDimensions['eventIntent'], 'Intent');
			ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Printed'));
			ga('send', 'event', 'Print', 'Print');
			nebulaConversion('print', true);
		}
	};
	if ( window.matchMedia ){
		var mediaQueryList = window.matchMedia('print');
		if ( mediaQueryList.addListener ){
			mediaQueryList.addListener(function(mql){
				if ( !mql.matches ){
					afterPrint();
				}
			});
		}
	}
	window.onafterprint = afterPrint;

	//Detect clicks on pinned header
	if ( typeof headroom !== 'undefined' && headroom.classes.pinned && headroom.classes.notTop ){
		nebula.dom.document.on('click tap touch', '.' + headroom.classes.pinned + '.' + headroom.classes.notTop + ' a', function(){
			ga('send', 'event', 'Pinned Header', 'Click', 'Used pinned header (after scrolling) to navigate');
		});
	}
}

//Detect scroll depth for engagement and more accurate bounce rate
function scrollDepth(){
	var headerHeight = ( jQuery('#header').is('*') )? jQuery('#header').height() : 250;
	var entryContent = jQuery('.entry-content');

	//Flags
	var timer = 0;
	var maxScroll = -1;
	var isScroller = false;
	var isReader = false;
	var endContent = false;
	var endPage = false;

	//Reading time calculations
	var startTime = new Date();
	var beginning = startTime.getTime();
	var totalTime = 0;

	nebula.dom.window.on('scroll', function(){
		if ( !isScroller ){
			currentTime = new Date();
			initialScroll = currentTime.getTime();
			delayBeforeInitial = (initialScroll-beginning)/1000;

			ga('send', 'timing', 'Scroll Depth', 'Initial scroll', Math.round(delayBeforeInitial*1000), 'Delay after pageload until initial scroll');
			isScroller = true;
		}

		//Calculate max scroll percent
		scrollPercent = Math.round((nebula.dom.window.scrollTop()/(nebula.dom.document.height()-nebula.dom.window.height()))*100);
		if ( scrollPercent > maxScroll ){
			maxScroll = scrollPercent;
			ga('set', gaCustomDimensions['maxScroll'], maxScroll + '%'); //Don't send an event here- this is only needed when another event is triggered.
		}

		if ( timer ){
			clearTimeout(timer);
		}
		timer = setTimeout(scrollLocation, 100); //Use a buffer so we don't call scrollLocation too often.
	});

	//Check the scroll location
	function scrollLocation(){
		viewportBottom = nebula.dom.window.height()+nebula.dom.window.scrollTop();
		documentHeight = nebula.dom.document.height();

		//When the user scrolls past the header
		var becomesReaderAt = ( entryContent.is('*') )? entryContent.offset().top : headerHeight;
		if ( viewportBottom >= becomesReaderAt && !isReader ){
			currentTime = new Date();
			readStartTime = currentTime.getTime();
			timeToScroll = (readStartTime-initialScroll)/1000;

			ga('set', gaCustomDimensions['timestamp'], localTimestamp());

			//This next line (event) is the line that alters Bounce Rate.
			//This line allows bounce rate to be calculated for individual page engagement.
			//To use the more traditional definition of bounce rate as a "Page Depth" engagement metric remove this line (or add a non-interaction object).
			ga('send', 'event', 'Scroll Depth', 'Began reading', Math.round(timeToScroll) + ' seconds (since initial scroll) [Signifies non-bounce visit]'); //This line alters bounce rate in Google Analytics.
			ga('send', 'timing', 'Scroll Depth', 'Began reading', Math.round(timeToScroll*1000), 'Scrolled from top of page to top of entry-content'); //Unless there is a giant header, this timing will likely be 0 on most sites.
			isReader = true;
		}

		//When the reader reaches the end of the entry-content
		if ( entryContent.is('*') ){
			if ( viewportBottom >= entryContent.offset().top+entryContent.innerHeight() && !endContent ){
				currentTime = new Date();
				readEndTime = currentTime.getTime();
				readTime = (readEndTime-readStartTime)/1000;

				//Set Custom Dimensions
				if ( gaCustomDimensions['scrollDepth'] ){
					if ( readTime < 10 ){
						ga('set', gaCustomDimensions['scrollDepth'], 'Previewer');
					} else if ( readTime < 60 ){
						ga('set', gaCustomDimensions['scrollDepth'], 'Scanner');
					} else {
						ga('set', gaCustomMetrics['engagedReaders'], 1);
						ga('set', gaCustomDimensions['scrollDepth'], 'Reader');
						nebulaConversion('engaged', nebula.post.title);
					}
				}

				ga('set', gaCustomDimensions['timestamp'], localTimestamp());
				ga('send', 'event', 'Scroll Depth', 'Finished reading', Math.round(readTime) + ' seconds (since reading began)');
				ga('send', 'timing', 'Scroll Depth', 'Finished reading', Math.round(readTime*1000), 'Scrolled from top of entry-content to bottom');
				endContent = true;
			}
		}

		//If user has hit the bottom of the page
		if ( viewportBottom >= documentHeight && !endPage ){
			currentTime = new Date();
			endTime = currentTime.getTime();
			totalTime = (endTime-readStartTime)/1000;

			ga('set', gaCustomDimensions['timestamp'], localTimestamp());
			ga('send', 'event', 'Scroll Depth', 'Reached bottom of page', Math.round(totalTime) + ' seconds (since pageload)');
			ga('send', 'timing', 'Scroll Depth', 'Reached bottom of page', Math.round(totalTime*1000), 'Scrolled from top of page to bottom');
			endPage = true;
		}
	}
}

/*==========================
 Search Functions
 ===========================*/

//Search Keywords
function keywordSearch(container, parent, value, filteredClass){
	if ( !filteredClass ){
		var filteredClass = 'filtereditem';
	}
	jQuery(container).find("*:not(:Contains(" + value + "))").parents(parent).addClass(filteredClass);
	jQuery(container).find("*:Contains(" + value + ")").parents(parent).removeClass(filteredClass);
}

//Menu Search Replacement
function menuSearchReplacement(){
	jQuery('li.nebula-search').html('<form class="wp-menu-nebula-search search nebula-search-iconable" method="get" action="' + nebula.site.home_url + '/"><input type="search" class="nebula-search input search" name="s" placeholder="Search" autocomplete="off" x-webkit-speech /></form>');
	jQuery('li.nebula-search input, input.nebula-search').on('focus', function(){
		jQuery(this).addClass('focus active');
	});
	jQuery('li.nebula-search input, input.nebula-search').on('blur', function(){
		if ( jQuery(this).val() === '' || jQuery(this).val().trim().length === 0 ){
			jQuery(this).removeClass('focus active focusError').attr('placeholder', jQuery(this).attr('placeholder'));
		} else {
			jQuery(this).removeClass('active');
		}
	});
}

//Only allow alphanumeric (and some special keys) to return true
//Use inside of a keydown function, and pass the event data.
function searchTriggerOnlyChars(e){
	//@TODO "Nebula" 0: This still allows shortcuts like "cmd+a" to return true.
	var spinnerRegex = new RegExp("^[a-zA-Z0-9]+$");
	var allowedKeys = [8, 46];
	var searchChar = String.fromCharCode(!e.charCode ? e.which : e.charCode);

	if ( spinnerRegex.test(searchChar) || allowedKeys.indexOf(e.which) > -1 ){
		return true;
	} else {
		return false;
	}
}

//Search autocomplete
function autocompleteSearch(){
	nebula.dom.document.on('blur', ".nebula-search-iconable input", function(){
		jQuery('.nebula-search-iconable').removeClass('searching').removeClass('autocompleted');
	});

	jQuery("input#s, input.search").on('keypress paste', function(e){
		thisSearchInput = jQuery(this);
		nebulaTimer('autocompleteSearch', 'start');
		nebulaTimer('autocompleteResponse', 'start');
		if ( !thisSearchInput.hasClass('no-autocomplete') && !nebula.dom.html.hasClass('lte-ie8') && thisSearchInput.val().trim().length ){
			if ( thisSearchInput.parents('form').hasClass('nebula-search-iconable') && thisSearchInput.val().trim().length >= 2 && searchTriggerOnlyChars(e) ){
				thisSearchInput.parents('form').addClass('searching');
				setTimeout(function(){
					thisSearchInput.parents('form').removeClass('searching');
				}, 10000);
			} else {
				thisSearchInput.parents('form').removeClass('searching');
			}

			thisSearchInput.autocomplete({
				position: {
					my: "left top-2px",
					at: "left bottom",
					collision: "flip",
				},
				source: function(request, response){
					jQuery.ajax({
						dataType: 'json',
						type: "POST",
						url: nebula.site.ajax.url,
						data: {
							nonce: nebula.site.ajax.nonce,
							action: 'nebula_autocomplete_search',
							data: request,
						},
						success: function(data){
							ga('set', gaCustomMetrics['autocompleteSearches'], 1);
							ga('set', gaCustomDimensions['timestamp'], localTimestamp());
							ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Internal Search'));
							if ( data ){
								nebulaPrerender(data[0].link);
								jQuery.each(data, function(index, value){
									value.label = value.label.replace(/&#038;/g, "\&");
								});
								noSearchResults = '';
							} else {
								noSearchResults = ' (No Results)';
								ga('set', gaCustomDimensions['sessionNotes'], sessionNote('No Search Results'));
							}
							debounce(function(){
								ga('send', 'event', 'Internal Search', 'Autocomplete Search' + noSearchResults, request.term);
								if ( typeof fbq === 'function' ){fbq('track', 'Search', {search_string: request.term});}
								nebulaConversion('keywords', request.term);
							}, 500, 'autocomplete success buffer');
							ga('send', 'timing', 'Autocomplete Search', 'Server Response', Math.round(nebulaTimer('autocompleteSearch', 'lap')), 'Each search until server results');
							response(data);
							thisSearchInput.parents('form').removeClass('searching').addClass('autocompleted');
						},
						error: function(MLHttpRequest, textStatus, errorThrown){
							ga('set', gaCustomDimensions['timestamp'], localTimestamp());
							debounce(function(){
								ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Autocomplete Search Error'));
								ga('send', 'event', 'Internal Search', 'Autcomplete Error', request.term);
							}, 500, 'autocomplete error buffer');
							thisSearchInput.parents('form').removeClass('searching');
						},
						timeout: 60000
					});
				},
				focus: function(event, ui){
					event.preventDefault(); //Prevent input value from changing.
				},
				select: function(event, ui){
					ga('set', gaCustomMetrics['autocompleteSearchClicks'], 1);
					ga('set', gaCustomDimensions['timestamp'], localTimestamp());
					ga('send', 'event', 'Internal Search', 'Autocomplete Click', ui.item.label);
		            ga('send', 'timing', 'Autocomplete Search', 'Until Navigation', Math.round(nebulaTimer('autocompleteSearch', 'end')), 'From first initial search until navigation');
		            if ( typeof ui.item.external !== 'undefined' ){
						window.open(ui.item.link, '_blank');
		            } else {
			            window.location.href = ui.item.link;
		            }
		        },
		        open: function(){
			        thisSearchInput.parents('form').addClass('autocompleted');
			        var heroAutoCompleteDropdown = jQuery('.form-identifier-nebula-hero-search');
					heroAutoCompleteDropdown.css('max-width', thisSearchInput.outerWidth());
		        },
		        close: function(){
					thisSearchInput.parents('form').removeClass('autocompleted');
		        },
		        minLength: 3,
		    }).data("ui-autocomplete")._renderItem = function(ul, item){
			    thisSimilarity = ( typeof item.similarity !== 'undefined' )? item.similarity.toFixed(1) + '% Match' : '';
			    var listItem = jQuery("<li class='" + item.classes + "' title='" + thisSimilarity + "'></li>").data("item.autocomplete", item).append("<a> " + item.label.replace(/\\/g, '') + "</a>").appendTo(ul);
			    return listItem;
			};
			var thisFormIdentifier = thisSearchInput.parents('form').attr('id') || thisSearchInput.parents('form').attr('name') || thisSearchInput.parents('form').attr('class');
			thisSearchInput.autocomplete("widget").addClass("form-identifier-" + thisFormIdentifier);
	    }
	});
}

//Advanced Search
//@TODO "Nebula" 0: Advanced Search functionality is still in development.
function advancedSearchTriggers(){
	var advancedSearchForm = jQuery('#advanced-search-form');
	haveAllEvents = 0;

	jQuery('a#metatoggle').on('click touch tap', function(){
		jQuery('#advanced-search-meta').toggleClass('active', function(){
			if ( jQuery('#advanced-search-meta').hasClass('active') ){
				setTimeout(function(){
					jQuery('#advanced-search-meta').addClass('finished');
				}, 500);
			} else {
				jQuery('#advanced-search-meta').removeClass('finished');
			}
		});
		return false;
	});

	jQuery('#s').keyup(function(e){
		advancedSearchPrep('Typing...');
		debounce(function(){
			if ( jQuery('#s').val() ){
				ga('set', gaCustomDimensions['timestamp'], localTimestamp());
				ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Internal Search'));
				ga('send', 'event', 'Internal Search', 'Advanced Search', jQuery('#s').val());
				nebulaConversion('keywords', jQuery('#s').val());
			}
		}, 1500);
	});

	nebula.dom.document.on('change', '#advanced-search-type, #advanced-search-catstags, #advanced-search-author, #advanced-search-date-start, #advanced-search-date-end', function(){
		advancedSearchPrep();
		if ( jQuery('#advanced-search-date-start') ){
			jQuery('#date-end-con').removeClass('hidden');
		} else { //@TODO: Not working...
			jQuery('#date-end-con').val('').addClass('hidden');
		}
	});

	//jQueryUI Datepicker
	jQuery('#advanced-search-date-start').datepicker({
		dateFormat: "MM d, yy",
		altField: "#advanced-search-date-start-alt",
		altFormat: "@",
		onSelect: function(){
			advancedSearchPrep();
			if ( jQuery('#advanced-search-date-start') ){
				jQuery('#date-end-con').removeClass('hidden');
			} else {
				jQuery('#date-end-con').val('').addClass('hidden');
			}
		}
	});
	jQuery('#advanced-search-date-end').datepicker({
		dateFormat: "MM d, yy",
		altField: "#advanced-search-date-end-alt",
		altFormat: "@",
		onSelect: function(){
			advancedSearchPrep();
		}
	});

	//Reset form
	jQuery('.resetfilters').on('click tap touch', function(){
		advancedSearchForm[0].reset();
		//@TODO "Nebula" 0: Chosen.js fields need to be reset manually... or something?
		jQuery(this).removeClass('active');
		advancedSearchPrep();
		return false;
	});

	loadMoreEvents = 0;
	jQuery('#load-more-events').on('click tap touch', function(){
		if ( typeof globalEventObject === 'undefined' ){
			advancedSearchPrep(10);

			loadMoreEvents = 10;

			jQuery('html, body').animate({
				scrollTop: advancedSearchForm.offset().top-10
			}, 500);

			return false;
		}

		if ( !jQuery(this).hasClass('all-events-loaded') ){
			loadMoreEvents = loadMoreEvents+10;
			advancedSearch(loadMoreEvents);

			jQuery('html, body').animate({
				scrollTop: advancedSearchForm.offset().top-10
			}, 500);
		}

		return false;
	});

	//Load Prev Events
	//@TODO "Nebula" 0: there is a bug here... i think?
	jQuery('#load-prev-events').on('click tap touch', function(){
		if ( !jQuery(this).hasClass('no-prev-events') ){
			jQuery('html, body').animate({
				scrollTop: advancedSearchForm.offset().top-10
			}, 500);

			loadMoreEvents = loadMoreEvents-10;
			advancedSearch(loadMoreEvents);
		}

		return false;
	});
}

//Either AJAX for all posts, or search immediately (if in memory)
function advancedSearchPrep(startingAt, waitingText){
	var advancedSearchIndicator = jQuery('#advanced-search-indicator');
	if ( !startingAt || typeof startingAt === 'string' ){
		waitingText = startingAt;
		startingAt = 0;
	}
	if ( haveAllEvents === 0 ){
		if ( !waitingText ){
			waitingText = 'Waiting for filters...';
		}
		advancedSearchIndicator.html('<i class="fa fa-fw fa-keyboard-o"></i> ' + waitingText);
		debounce(function(){
			advancedSearchIndicator.html('<i class="fa fa-fw fa-spin fa-spinner"></i> Loading posts...');
			jQuery.ajax({
				type: "POST",
				url: nebula.site.ajax.url,
				data: {
					nonce: nebula.site.ajax.nonce,
					action: 'nebula_advanced_search',
				},
				success: function(response){
					haveAllEvents = 1;
					advancedSearch(startingAt, response);
				},
				error: function(MLHttpRequest, textStatus, errorThrown){
					jQuery('#advanced-search-results').text('Error: ' + MLHttpRequest + ', ' + textStatus + ', ' + errorThrown);
					haveAllEvents = 0;
					ga('set', gaCustomDimensions['timestamp'], localTimestamp());
					ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Advanced Search AJAX Error'));
					ga('send', 'event', 'Error', 'AJAX Error', 'Advanced Search AJAX');
				},
				timeout: 60000
			});
		}, 1500, 'ajax search debounce');
	} else {
		advancedSearch(startingAt);
	}
}

function advancedSearch(start, eventObject){
	var advancedSearchIndicator = jQuery('#advanced-search-indicator');

	if ( eventObject ){
		globalEventObject = jQuery.parseJSON(eventObject);
	}

	//Search events object
	filteredPostsObject = postSearch(globalEventObject);

	jQuery('#advanced-search-results').html('');
	i = ( start )? parseFloat(start) : 0;
	if ( start != 0 ){
		jQuery('#load-prev-events').removeClass('no-prev-events');
	} else {
		jQuery('#load-prev-events').addClass('no-prev-events');
	}
	if ( start+10 >= filteredPostsObject.length ){
		var end = filteredPostsObject.length;
		moreEvents(0);
	} else {
		var end = start+10;
		moreEvents(1);
	}

	if ( filteredPostsObject.length > 0 ){
		advancedSearchIndicator.html('<i class="fa fa-fw fa-calendar"></i> Showing <strong>' + (start+1) + '-' + end + '</strong> of <strong>' + filteredPostsObject.length + '</strong> results:');
	} else {
		advancedSearchIndicator.html('<i class="fa fa-fw fa-times-circle"></i> <strong>No pages found</strong> that match your filters.');
		if ( jQuery('#s').val() ){
			ga('set', gaCustomDimensions['timestamp'], localTimestamp());
			ga('set', gaCustomDimensions['sessionNotes'], sessionNote('No Search Results'));
			ga('send', 'event', 'Internal Search', 'Advanced No Results', jQuery('#s').val());
		}
		moreEvents(0);
		return false;
	}

	while ( i <= end-1 ){
		if ( !filteredPostsObject[i] || typeof filteredPostsObject[i].posted === 'undefined' ){
			moreEvents(0);
			return;
		}

		//Date and Time
		var postDate = new Date(filteredPostsObject[i].posted * 1000);
		var year = postDate.getFullYear();
		var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
		var month = months[postDate.getMonth()];
		var weekdays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
		var weekday = weekdays[postDate.getDay()];
		var day = postDate.getDate();
		var hour = postDate.getHours();
		var ampm = ( hour >= 12 )? 'pm' : 'am';
		if ( hour > 12 ){
			hour -= 12;
		} else if ( hour === 0 ){
			hour = 12;
		}
		var minute = (( postDate.getMinutes() <= 9 )? '0' : '') + postDate.getMinutes();

		//Categories
		var postCats = '';
		if ( filteredPostsObject[i].categories.length ){
			eventCats = '<span class="post-cats"><i class="fa fa-fw fa-bookmark"></i> ' + filteredPostsObject[i].categories.join(', ') + '</span>';
		}

		//Tags
		var postTags = '';
		if ( filteredPostsObject[i].tags.length ){
			eventTags = '<span class="post-tags"><i class="fa fa-fw fa-tags"></i> ' + filteredPostsObject[i].tags.join(', ') + '</span>';
		}

		//Description
		var shortDescription = '';
		if ( filteredPostsObject[i].description ){
			shortDescription = ( filteredPostsObject[i].description.length > 200 )? filteredPostsObject[i].description.substring(0, 200) + '...' : filteredPostsObject[i].description;
		}

		var markUp = '<div class="advanced-search-result">' +
				'<h3><a href="' + filteredPostsObject[i].url + '">' + filteredPostsObject[i].title + '</a></h3>' +
				'<p class="post-date-time">' + month + ' ' + day + ', ' + year +
				'<p class="post-meta-tags">' + postCats + postTags + '</p>' +
				'<p class="post-meta-description">' + shortDescription + '</p>' +
				'<div class="hidden" style="display: none; visibility: hidden; pointer-events: none;">' + filteredPostsObject[i].custom.nebula_internal_search_keywords + '</div>' +
			'</div>';
		jQuery('#advanced-search-results').append(markUp);
		i++;
	}
}

function postSearch(posts){
	var tempFilteringObject = JSON.parse(JSON.stringify(posts)); //Duplicate the object in memory
	jQuery(tempFilteringObject).each(function(i){
		var thisPost = this;

		//Search Dates
		if ( jQuery('#advanced-search-date-start-alt').val() ){
			var postDate = new Date(thisPost.posted*1000);
			var postDateStamp = postDate.getFullYear() + '-' + postDate.getMonth() + '-' + postDate.getDate();
			var searchDateStart = new Date(parseInt(jQuery('#advanced-search-date-start-alt').val()));
			var searchDateStartStamp = searchDateStart.getFullYear() + '-' + searchDateStart.getMonth() + '-' + searchDateStart.getDate();

			if ( jQuery('#advanced-search-date-end-alt').val() ){
				var searchDateEnd = new Date(parseInt(jQuery('#advanced-search-date-end-alt').val()));
				if ( postDate < searchDateStart || postDate > searchDateEnd ){
					delete tempFilteringObject[i]; //Date is not in the range
					return;
				}
			} else {
				if ( postDateStamp != searchDateStartStamp ){
					delete tempFilteringObject[i]; //Date does not match exactly
					return;
				}
			}
		}

		//Search Categories and Tags
		if ( jQuery('#advanced-search-catstags').val() ){
			if ( thisPost.categories || thisPost.tags ){
				jQuery.each(jQuery('#advanced-search-catstags').val(), function(key, value){
					thisCatTag = value.split('__');
					if ( thisCatTag[0] === 'category' ){
						var categoryText = thisPost.categories.join(', ').toLowerCase().replace(/&amp;/g, '&');
						if ( categoryText.indexOf(thisCatTag[1].toLowerCase()) < 0 ){
							delete tempFilteringObject[i]; //Category does not match
						}
					} else {
						var tagText = thisPost.tags.join(', ').toLowerCase().replace(/&amp;/g, '&');
						if ( tagText.indexOf(thisCatTag[1].toLowerCase()) < 0 ){
							delete tempFilteringObject[i]; //Tag does not match
						}
					}
				});
			} else {
				delete tempFilteringObject[i]; //Post does not have categories or tags
				return;
			}
		}

		//Search Post Types (This is an inclusive filter)
		if ( jQuery('#advanced-search-type').val() ){
			var requestedPostType = jQuery('#advanced-search-type').val().join(', ').toLowerCase();
			if ( requestedPostType.indexOf(thisPost.type.toLowerCase()) < 0 ){
				delete tempFilteringObject[i]; //Post Type does not match
			}
		}

		//Search Author
		if ( jQuery('#advanced-search-author').val() != '' ){
			if ( thisPost.author.id != jQuery('#advanced-search-author').val() ){
				delete tempFilteringObject[i]; //Author ID does not match
				return;
			}
		}

		//Keyword Filter
		if ( jQuery('#s').val() != '' ){
			thisEventString = JSON.stringify(thisPost).toLowerCase();
			thisEventString += '';
			if ( thisEventString.indexOf(jQuery('#s').val().toLowerCase()) < 0 ){
				delete tempFilteringObject[i]; //Keyword not found
				return;
			}
		}
	});

	tempFilteringObject = tempFilteringObject.filter(function(){return true;});
	eventFormNeedReset();
	return tempFilteringObject;
}

function wpSearchInput(){
	jQuery('#post-0 #s, .headerdrawer #s, .search-results #s').focus(); //Automatically focus on specific search inputs

	//Set search value as placeholder
	var searchVal = get('s') || jQuery('#s').val();
	if ( searchVal ){
		jQuery('#s').attr('placeholder', searchVal);
		jQuery('.nebula-search').attr('placeholder', searchVal);
	}
}


//Mobile search placeholder toggle
function mobileSearchPlaceholder(){
	var mobileHeaderSearchInput = jQuery('#mobileheadersearch input');
	if ( nebula.dom.window.width() <= 410 ){
		mobileHeaderSearchInput.attr('placeholder', 'Search');
	} else {
		mobileHeaderSearchInput.attr('placeholder', 'What are you looking for?');
	}
}


//Search Validator
function searchValidator(){
	if ( !nebula.dom.html.hasClass('lte-ie8') ){
		jQuery('.lt-ie9 form.search .btn.submit').val('Search');
		jQuery('.input.search').each(function(){
			if ( jQuery(this).val() === '' || jQuery(this).val().trim().length === 0 ){
				jQuery(this).parent().children('.btn.submit').addClass('disallowed');
			} else {
				jQuery(this).parent().children('.btn.submit').removeClass('disallowed').val('Search');
				jQuery(this).parent().find('.input.search').removeClass('focusError');
			}
		});
		jQuery('.input.search').on('focus blur change keyup paste cut',function(e){
			thisPlaceholder = ( jQuery(this).attr('data-prev-placeholder') !== 'undefined' )? jQuery(this).attr('data-prev-placeholder') : 'Search';
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
		})
		jQuery('form.search').submit(function(){
			if ( jQuery(this).find('.input.search').val() === '' || jQuery(this).find('.input.search').val().trim().length === 0 ){
				jQuery(this).parent().find('.input.search').prop('title', 'Enter a valid search term.').attr('data-prev-placeholder', jQuery(this).attr('placeholder')).attr('placeholder', 'Enter a valid search term').addClass('focusError').focus().attr('value', '');
				jQuery(this).parent().find('.btn.submit').prop('title', 'Enter a valid search term.').addClass('notallowed');
				return false;
			} else {
				return true;
			}
		});
	}
}

//Highlight search terms
function searchTermHighlighter(){
	var theSearchTerm = document.URL.split('?s=')[1];
	if ( typeof theSearchTerm !== 'undefined' ){
		theSearchTerm = theSearchTerm.replace(/\+/g, ' ').replace(/\%20/g, ' ').replace(/\%22/g, '');
		jQuery('article .entry-title a, article .entry-summary').each(function(i){
			var searchFinder = jQuery(this).text().replace(new RegExp( '(' + preg_quote(theSearchTerm) + ')' , 'gi' ), '<span class="searchresultword">$1</span>');
			jQuery(this).html(searchFinder);
		});
	}
	function preg_quote(str){
		return (str + '').replace(/([\\\.\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:])/g, "\\$1");
	}
}

//Emphasize the search Terms
function emphasizeSearchTerms(){
	var theSearchTerm = get('s');
	if ( typeof theSearchTerm !== 'undefined' ){
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
	}
}

//Single search result redirection drawer
function singleResultDrawer(){
	var theSearchTerm = get('rs');
	if ( typeof theSearchTerm !== 'undefined' ){
		theSearchTerm = theSearchTerm.replace(/\%20|\+/g, ' ').replace(/\%22|"|'/g, '');
		jQuery('#searchform input#s').val(theSearchTerm);
	}

	nebula.dom.document.on('click touch tap', '.headerdrawer .close', function(){
		var permalink = jQuery(this).attr('href');
		history.replaceState(null, document.title, permalink);
		jQuery('.headerdrawercon').slideUp();
		return false;
	});
}

//Page Suggestions for 404 or no search results pages using Google Custom Search Engine
function pageSuggestion(){
	if ( nebula.dom.body.hasClass('search-no-results') || nebula.dom.body.hasClass('error404') ){
		if ( nebula.site.options.nebula_cse_id != '' && nebula.site.options.nebula_google_browser_api_key != '' ){
			if ( get().length ){
				var queryStrings = get();
			} else {
				var queryStrings = [''];
			}
			var path = window.location.pathname;
			var phrase = decodeURIComponent(path.replace(/\/+/g, ' ').trim()) + ' ' + decodeURIComponent(queryStrings[0].replace(/\+/g, ' ').trim());
			trySearch(phrase);

			nebula.dom.document.on('mousedown touch tap', 'a.suggestion', function(e){
				eventIntent = ( e.which >= 2 )? 'Intent' : 'Explicit';
				var suggestedPage = jQuery(this).text();

				ga('set', gaCustomDimensions['eventIntent'], eventIntent);
				ga('set', gaCustomMetrics['pageSuggestionsAccepted'], 1);
				ga('set', gaCustomDimensions['timestamp'], localTimestamp());
				ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Page Suggestion Accepted'));
				ga('send', 'event', 'Page Suggestion', 'Click', 'Suggested Page: ' + suggestedPage);
			});
		}
	}
}

function trySearch(phrase){
	var queryParams = {
		cx: nebula.site.options.nebula_cse_id,
		key: nebula.site.options.nebula_google_browser_api_key,
		num: 10,
		q: phrase,
		alt: 'JSON'
	}
	var API_URL = 'https://www.googleapis.com/customsearch/v1?';

	// Send the request to the custom search API
	jQuery.getJSON(API_URL, queryParams, function(response){
		ga('set', gaCustomDimensions['timestamp'], localTimestamp());
		if ( response.items && response.items.length ){
			ga('set', gaCustomMetrics['pageSuggestions'], 1);
			ga('send', 'event', 'Page Suggestion', 'Suggested Page: ' + response.items[0].title, 'Requested URL: ' + window.location, {'nonInteraction': 1});
			showSuggestedPage(response.items[0].title, response.items[0].link);
		} else {
			ga('send', 'event', 'Page Suggestion', 'No Suggestions Found', 'Requested URL: ' + window.location, {'nonInteraction': 1});
		}
	});
}

function showSuggestedPage(title, url){
	var hostname = new RegExp(location.host);
	if ( hostname.test(url) ){
		jQuery('.suggestion').attr('href', url).text(title);
		jQuery('#suggestedpage').slideDown();
		nebulaPrerender(url);
	}
}

//Detections for events specific to predicting the next pageview.
function nebulaPrerenderListeners(){
	//Any post listing page
	if ( jQuery('.first-post').is('*') ){
		nebulaPrerender(jQuery('.first-post').find('.entry-title a').attr('href'));
	}

	//Internal link hovers
	jQuery('a').hover(function(){
		var oThis = jQuery(this);
		if ( oThis.attr('href') != jQuery('link#prerender').attr('href') && oThis.attr('target') != '_blank' ){
			var hoverLength = 500;
			if ( jQuery('link#prerender').is('*') ){ //If prerender already exists, extend the hover time needed to update
				hoverLength = 1000;
			}

			hoverTimer = setTimeout(function(){
				if ( oThis.is(":hover") ){
					nebulaPrerender(oThis.attr('href'));
				}
			}, hoverLength);
		}
	}, function(){
		if ( typeof hoverTimer !== 'undefined' ){
			clearTimeout(hoverTimer);
		}
	});

	//Store correct/inccorect predictions in Google Analytics.
	//This Nebula option must be used for data to be reported.
	/*
		Use Top Events report to find data, then secondary dimensions for comparisons.
		Page = the current page
		Prerender Link = the predicted page that was prerendered
		Event label = the URL that was actually clicked by the user.
	*/
	if ( gaCustomDimensions['prerenderedLink'] && jQuery('link#prerender').is('*') ){ //If custom dimension is enabled, and prerender exists
		jQuery(document).on('click touch tap', 'a', function(){
			if ( jQuery(this).attr('target') != '_blank' && jQuery(this).attr('href') != '#' && jQuery(this).attr('href') != window.location.href ){ //If link is eligible to have been prerendered
				var clickedLink =  jQuery(this).attr('href');
				var prerenderedLink = jQuery('link#prerender').attr('href');

				ga('set', gaCustomDimensions['prerenderedLink'], prerenderedLink);

				if ( prerenderedLink.indexOf('//') === 0 ){
					clickedLink = clickedLink.replace(/https?:/g, '');
				}

				var predictionResult = ( clickedLink == prerenderedLink )? 'Correct' : 'Incorrect';
				ga('send', 'event', 'Prerender Prediction', predictionResult, clickedLink, {'nonInteraction': 1});
			}
		});
	}
}

//Actually prerender a URL
function nebulaPrerender(url){
	if ( url ){
		if ( jQuery('link#prerender').is('*') ){
			jQuery('link#prerender').attr('href', url); //Update prerender link
		} else {
			jQuery('head').append('<link id="prerender" rel="prerender prefetch" href="' + url + '>'); //Create new prerender link
		}
	}
}

/*==========================
 Contact Form Functions
 ===========================*/

function cf7Functions(){
	if ( !jQuery('.wpcf7-form').length ){
		return false;
	}

	formStarted = [];
	jQuery('.wpcf7-form input, .wpcf7-form textarea').on('focus', function(){
		formID = jQuery(this).parents('div.wpcf7').attr('id');

		if ( !jQuery('form').hasClass('.ignore-form') && !jQuery('form').find('.ignore-form').length && (typeof formStarted[formID] === 'undefined' || !formStarted[formID]) ){
			ga('set', gaCustomMetrics['formStarts'], 1);
			formStarted[formID] = true;
		}

		nebulaTimer(formID, 'start', jQuery(this).attr('name'));
		nebulaConversion('abandoned_form', 'Form ID: ' + formID);

		//Individual form field timings
		if ( typeof nebulaTimings[formID].lap[nebulaTimings[formID].laps-1] !== 'undefined' ){
			ga('send', 'timing', 'Contact', nebulaTimings[formID].lap[nebulaTimings[formID].laps-1].name, Math.round(nebulaTimings[formID].lap[nebulaTimings[formID].laps-1].duration), 'Amount of time on this input field (until next focus or submit).');
		}
	});

	jQuery('.wpcf7-form input, .wpcf7-form textarea').on('blur', function(){
		debugInfo();
	});

	//CF7 Submit (CF7 AJAX response after any submit attempt)
	nebula.dom.document.on('wpcf7:submit', function(e){
		//@TODO "Nebula" 0: This is still called after data is already pulled (just like a regular 'submit' listener), so this debugInfo does not make it into the email... Until CF7 updates, this is how it will be.
		nebulaTimer(e.target.id, 'lap', 'wpcf7-submit-attempt');
		debugInfo();

		ga('set', gaCustomDimensions['contactMethod'], 'Contact Form (Attempt)');
		ga('set', gaCustomDimensions['timestamp'], localTimestamp());
		ga('send', 'event', 'Contact', 'Submit (Attempt)', 'Submission attempt for form ID: ' + e.target.id); //This event is required for the notable form metric!
		if ( typeof fbq === 'function' ){fbq('track', 'Lead', {content_name: 'Form Submit (Attempt)',});}
	});

	//CF7 Invalid (CF7 AJAX response after invalid form)
	nebula.dom.document.on('wpcf7:invalid', function(e){
		ga('set', gaCustomDimensions['contactMethod'], 'Contact Form (Invalid)');
		ga('send', 'event', 'Contact', 'Submit (Invalid)', 'Form validation errors occurred on form ID: ' + e.target.id);
		nebulaScrollTo(jQuery(".wpcf7-not-valid").first()); //Scroll to the first invalid input
	});

	//CF7 Spam (CF7 AJAX response after spam detection)
	nebula.dom.document.on('wpcf7:spam', function(e){
		ga('set', gaCustomDimensions['contactMethod'], 'Contact Form (Spam)');
		ga('send', 'event', 'Contact', 'Submit (Spam)', 'Form submission failed spam tests on form ID: ' + e.target.id);
	});

	//CF7 Mail Send Failure (CF7 AJAX response after mail failure)
	nebula.dom.document.on('wpcf7:mailfailed', function(e){
		ga('set', gaCustomDimensions['contactMethod'], 'Contact Form (Failed)');
		ga('send', 'event', 'Contact', 'Submit (Failed)', 'Form submission email send failed for form ID: ' + e.target.id);
	});

	//CF7 Mail Sent Success (CF7 AJAX response after submit success)
	nebula.dom.document.on('wpcf7:mailsent', function(e){
		if ( !jQuery('#' + e.target.id).hasClass('.ignore-form') && !jQuery('#' + e.target.id).find('.ignore-form').length ){
			ga('set', gaCustomMetrics['formSubmissions'], 1);
		}
		ga('set', gaCustomDimensions['contactMethod'], 'Contact Form (Success)');
		ga('set', gaCustomDimensions['timestamp'], localTimestamp());
		ga('send', 'timing', 'Contact', 'Form Completion', Math.round(nebulaTimer(e.target.id, 'end')), 'Initial form focus until valid submit');
		ga('send', 'event', 'Contact', 'Submit (Success)', 'Form ID: ' + e.target.id + ' (Completed in: ' + millisecondsToString(nebulaTimer(e.target.id, 'end')));
		if ( typeof fbq === 'function' ){fbq('track', 'Lead', {content_name: 'Form Submit (Success)',});}
		nebulaConversion('contact', 'Form ID: ' + e.target.id);
		nebulaConversion('abandoned_form', 'Form ID: ' + e.target.id, 'remove');

		//Clear localstorage on submit success
		jQuery('#' + e.target.id + ' .wpcf7-textarea, #' + e.target.id + ' .wpcf7-text').each(function(){
			localStorage.removeItem('cf7_' + jQuery(this).attr('name'));
		});
	});
}

//Enable localstorage on CF7 text inputs and textareas
function cf7LocalStorage(){
	if ( !jQuery('.wpcf7-form').length ){
		return false;
	}

	jQuery('.wpcf7-textarea, .wpcf7-text').each(function(){
		var thisLocalStorageVal = localStorage.getItem('cf7_' + jQuery(this).attr('name'));

		//Fill textareas with localstorage data on load
		if ( !jQuery(this).hasClass('no-storage') && !jQuery(this).hasClass('.wpcf7-captchar') && thisLocalStorageVal && thisLocalStorageVal != 'undefined' && thisLocalStorageVal != '' ){
			if ( jQuery(this).val() === '' ){ //Don't overwrite a field that already has text in it!
				jQuery(this).val(thisLocalStorageVal);
			}
			jQuery(this).blur();
		} else {
			localStorage.removeItem('cf7_' + jQuery(this).attr('name')); //Remove localstorage if it is undefined or inelligible
		}

		//Update localstorage data
		jQuery(this).on('keyup blur', function(){
			if ( !jQuery(this).hasClass('no-storage') && !jQuery(this).hasClass('.wpcf7-captchar') ){
				localStorage.setItem('cf7_' + jQuery(this).attr('name'), jQuery(this).val());
			}
		});
	});

	//Update matching form fields on other windows/tabs
	nebula.dom.window.on('storage', function(e){
    	jQuery('.wpcf7-textarea, .wpcf7-text').each(function(){
	    	if ( !jQuery(this).hasClass('no-storage') && !jQuery(this).hasClass('.wpcf7-captchar') ){
				jQuery(this).val(localStorage.getItem('cf7_' + jQuery(this).attr('name')));
			}
	    });
    });
}

//CF7 live (soft) validator
function cf7LiveValidator(){
	if ( !jQuery('.wpcf7-form').length ){
		return false;
	}

	//Standard text inputs
	jQuery('.wpcf7-text').on('keyup blur', function(e){
		if ( jQuery(this).val().trim() === '' ){
			jQuery(this).removeClass('wpcf7-not-valid').parents('.field').removeClass('danger warning success');
		} else {
			jQuery(this).parents('.field').removeClass('danger warning').addClass('success');
		}
	});

	//Email address inputs
	jQuery('.wpcf7-email').on('keyup blur', function(e){
		if ( jQuery(this).val() === '' ){
			jQuery(this).removeClass('wpcf7-not-valid').parents('.field').removeClass('success danger warning');
		} else if ( regexPattern.email.test(jQuery(this).val()) ){
			jQuery(this).removeClass('wpcf7-not-valid').parents('.field').removeClass('warning danger').addClass('success');
		} else {
			var warnDanger = ( e.type === 'keyup' )? 'warning' : 'danger';
			jQuery(this).parents('.field').removeClass('success warning danger').addClass(warnDanger);
		}
	});

	//Phone number inputs
	jQuery('.wpcf7-text.phone').on('keyup blur', function(e){
		if ( jQuery(this).val() === '' ){
			jQuery(this).removeClass('wpcf7-not-valid').parents('.field').removeClass('success danger warning');
		} else if ( regexPattern.phone.test(jQuery(this).val()) ){
			jQuery(this).removeClass('wpcf7-not-valid').parents('.field').removeClass('warning danger').addClass('success');
		} else {
			jQuery(this).parents('.field').removeClass('success').addClass('warning');
		}
	});

	//Date inputs
	jQuery('.wpcf7-text.date').on('keyup blur', function(e){
		if ( jQuery(this).val() === '' ){
			jQuery(this).removeClass('wpcf7-not-valid').parents('.field').removeClass('success danger warning');
		} else if ( regexPattern.date.mdy.test(jQuery(this).val()) ){ //Check for MM/DD/YYYY (and flexible variations)
			jQuery(this).removeClass('wpcf7-not-valid').parents('.field').removeClass('warning danger').addClass('success');
		} else if ( regexPattern.date.ymd.test(jQuery(this).val()) ){ //Check for YYYY/MM/DD (and flexible variations)
			jQuery(this).removeClass('wpcf7-not-valid').parents('.field').removeClass('warning danger').addClass('success');
		} else if ( strtotime(jQuery(this).val()) && strtotime(jQuery(this).val()) > -2208988800 ){ //Check for textual dates (after 1900) //@TODO "Nebula" 0: The JS version of strtotime() isn't the most accurate function...
			jQuery(this).removeClass('wpcf7-not-valid').parents('.field').removeClass('warning danger').addClass('success');
		} else {
			jQuery(this).parents('.field').removeClass('success').addClass('warning');
		}
	});

	//Message textarea
	jQuery('.wpcf7-textarea').on('keyup blur', function(e){
		if ( jQuery(this).val().trim() === '' ){
			jQuery(this).removeClass('wpcf7-not-valid').parents('.field').removeClass('danger warning success');
		} else {
			if ( e.type === 'blur' ){
				jQuery(this).removeClass('wpcf7-not-valid').parents('.field').removeClass('danger warning').addClass('success');
			} else {
				jQuery(this).removeClass('wpcf7-not-valid').parents('.field').removeClass('danger warning success'); //Remove green while typing
			}
		}
	});

	//CAPTCHA
	jQuery('.wpcf7-captchar').on('keyup blur', function(e){
		jQuery(this).removeClass('wpcf7-not-valid');
		if ( jQuery(this).val().length > 4 ){
			jQuery(this).parents('.field').addClass('warning');
		} else {
			jQuery(this).parents('.field').removeClass('warning');
		}
	});
}

//Google AdWords conversion tracking for AJAX forms
//Contact Form 7 - Add on_sent_ok: "conversionTracker();" to Additional Settings
//Parameter should be either boolean (to use thanks.html) or string of another conversion page to use (Default: false).
function conversionTracker(conversionpage){
	if ( typeof conversionpage !== 'string' || conversionpage.indexOf('.') <= 0 ){
		conversionpage = 'thanks.html';
	}

	var iframe = document.createElement('iframe');
	iframe.style.width = '0px';
	iframe.style.height = '0px';
	document.body.appendChild(iframe);
	iframe.src = nebula.site.directory.template.uri + '/includes/conversion/' + conversionpage;
};


/*==========================
 Optimization Functions
 ===========================*/

//Conditional JS Library Loading
//This could be done better I think (also, it runs too late in the stack).
function conditionalJSLoading(){

	//Only load bxslider library on a page that calls bxslider.
	if ( jQuery('.bxslider').is('*') ){
		jQuery.getScript('https://cdnjs.cloudflare.com/ajax/libs/bxslider/4.2.5/jquery.bxslider.min.js').done(function(){
			bxSlider();
		}).fail(function(){
			ga('set', gaCustomDimensions['timestamp'], localTimestamp());
			ga('set', gaCustomDimensions['sessionNotes'], sessionNote('JS Resource Load Error'));
			ga('send', 'event', 'Error', 'JS Error', 'bxSlider could not be loaded.', {'nonInteraction': 1});
		});
		nebulaLoadCSS('https://cdnjs.cloudflare.com/ajax/libs/bxslider/4.2.5/jquery.bxslider.min.css');
	}

	//Only load Chosen library if 'chosen-select' class exists.
	if ( jQuery('.chosen-select').is('*') ){
		jQuery.getScript('https://cdnjs.cloudflare.com/ajax/libs/chosen/1.4.2/chosen.jquery.min.js').done(function(){
			chosenSelectOptions();
		}).fail(function(){
			ga('set', gaCustomDimensions['timestamp'], localTimestamp());
			ga('set', gaCustomDimensions['sessionNotes'], sessionNote('JS Resource Load Error'));
			ga('send', 'event', 'Error', 'JS Error', 'chosen.jquery.min.js could not be loaded.', {'nonInteraction': 1});
		});
		nebulaLoadCSS('https://cdnjs.cloudflare.com/ajax/libs/chosen/1.4.2/chosen.min.css');
	}

	//Only load dataTables library if dataTables table exists.
    if ( jQuery('.dataTables_wrapper').is('*') ){
        jQuery.getScript('https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.10/js/jquery.dataTables.min.js').done(function(){
            nebulaLoadCSS('https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.10/css/jquery.dataTables.min.css');
			dataTablesActions();
        }).fail(function(){
            ga('set', gaCustomDimensions['timestamp'], localTimestamp());
            ga('set', gaCustomDimensions['sessionNotes'], sessionNote('JS Resource Load Error'));
            ga('send', 'event', 'Error', 'JS Error', 'jquery.dataTables.min.js could not be loaded', {'nonInteraction': 1});
        });

		//Only load Highlight if dataTables table exists.
        jQuery.getScript(nebula.site.directory.template.uri + '/js/libs/jquery.highlight-5.closure.js').fail(function(){
            ga('set', gaCustomDimensions['timestamp'], localTimestamp());
            ga('set', gaCustomDimensions['sessionNotes'], sessionNote('JS Resource Load Error'));
            ga('send', 'event', 'Error', 'JS Error', 'jquery.highlight-5.closure.js could not be loaded.', {'nonInteraction': 1});
        });
    }

	if ( jQuery('pre.nebula-code').is('*') || jQuery('pre.nebula-pre').is('*') ){
		nebulaLoadCSS(nebula.site.directory.template.uri + '/stylesheets/css/pre.css');
		nebula_pre();
	}

	if ( jQuery('.flag').is('*') ){
		nebulaLoadCSS(nebula.site.directory.template.uri + '/stylesheets/css/flags.css');
	}
}

//Dynamically load CSS files using JS
function nebulaLoadCSS(url){
	if ( document.createStyleSheet ){
	    try {
		    document.createStyleSheet(url);
	    } catch(e){
		    ga('set', gaCustomDimensions['timestamp'], localTimestamp());
		    ga('set', gaCustomDimensions['sessionNotes'], sessionNote('CSS Resource Load Error'));
		    ga('send', 'event', 'Error', 'CSS Error', url + ' could not be loaded', {'nonInteraction': 1});
	    }
	} else {
	    var css;
	    css = document.createElement('link');
	    css.rel = 'stylesheet';
	    css.type = 'text/css';
	    css.media = "all";
	    css.href = url;
	    document.getElementsByTagName("head")[0].appendChild(css);
	}
}


/* ==========================================================================
   Google Maps Functions
   ========================================================================== */

//Places - Address Autocomplete
function nebulaAddressAutocomplete(autocompleteInput){
	if ( jQuery(autocompleteInput).is('*') ){ //If the addressAutocomplete ID exists
		jQuery.getScript('https://www.google.com/jsapi', function(){
		    google.load('maps', '3', {
			    other_params: 'libraries=places',
			    callback: function(){
					addressAutocomplete = new google.maps.places.Autocomplete(
						jQuery(autocompleteInput)[0],
						{types: ['geocode']} //Restrict the search to geographical location types
					);

					google.maps.event.addListener(addressAutocomplete, 'place_changed', function(){ //When the user selects an address from the dropdown, populate the address fields in the form.
						place = addressAutocomplete.getPlace(); //Get the place details from the addressAutocomplete object.

						nebula.user.address = {
							street: {
								number: null,
								name: null,
								full: null,
							},
							city: null,
							county: null,
							state: {
								name: null,
								abbreviation: null,
							},
							country: {
								name: null,
								abbreviation: null,
							},
							zip: {
								code: null,
								suffix: null,
								full: null,
							},
						};

						for ( var i = 0; i < place.address_components.length; i++ ){
							//Lots of different address types. This function uses only the common ones: https://developers.google.com/maps/documentation/geocoding/#Types
							switch ( place.address_components[i].types[0] ){
								case "street_number":
									nebula.user.address.street.number = place.address_components[i].short_name; //123
									break;
								case "route":
									nebula.user.address.street.name = place.address_components[i].long_name; //Street Name Rd.
									break;
								case "locality":
									nebula.user.address.city = place.address_components[i].long_name; //Liverpool
									break;
								case "administrative_area_level_2":
									nebula.user.address.county = place.address_components[i].long_name; //Onondaga County
									break;
								case "administrative_area_level_1":
									nebula.user.address.state.name = place.address_components[i].long_name; //New York
									nebula.user.address.state.abbreviation = place.address_components[i].short_name; //NY
									break;
								case "country":
									nebula.user.address.country.name = place.address_components[i].long_name; //United States
									nebula.user.address.country.abbreviation = place.address_components[i].short_name; //US
									break;
								case "postal_code":
									nebula.user.address.zip.code = place.address_components[i].short_name; //13088
									break;
								case "postal_code_suffix":
									nebula.user.address.zip.suffix = place.address_components[i].short_name; //4725
									break;
								default:
									//console.log('Address component ' + place.address_components[i].types[0] + ' not used.');
							}
						}
						if ( nebula.user.address.street.number && nebula.user.address.street.name ){
							nebula.user.address.street.full = nebula.user.address.street.number + ' ' + nebula.user.address.street.name;
						}
						if ( nebula.user.address.zip.code && nebula.user.address.zip.suffix ){
							nebula.user.address.zip.full = nebula.user.address.zip.code + '-' + nebula.user.address.zip.suffix;
						}

						nebula.dom.document.trigger('nebula_address_selected');
						ga('set', gaCustomDimensions['contactMethod'], 'Autocomplete Address');
						ga('set', gaCustomDimensions['timestamp'], localTimestamp());
						ga('send', 'event', 'Contact', 'Autocomplete Address', nebula.user.address.city + ', ' + nebula.user.address.state.abbreviation + ' ' + nebula.user.address.zip.code);
						createCookie('nebulaUser', JSON.stringify(nebula.user));
					});

					jQuery(autocompleteInput).on('focus', function(){
						if ( navigator.geolocation ){
							navigator.geolocation.getCurrentPosition(function(position){ //Bias to the user's geographical location.
								var geolocation = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
								var circle = new google.maps.Circle({
									center: geolocation,
									radius: position.coords.accuracy
								});
								addressAutocomplete.setBounds(circle.getBounds());
							});
						}
					}).on('keydown', function(e){
						if ( e.which === 13 && jQuery('.pac-container:visible').is('*') ){ //Prevent form submission when enter key is pressed while the "Places Autocomplete" container is visbile
							return false;
						}
					});

					if ( autocompleteInput === '#address-autocomplete' ){
						nebula.dom.document.on('nebula_address_selected', function(){
							//do any default stuff here.
						});
					}
		    	} //End Google Maps callback
		    }); //End Google Maps load
		}).fail(function(){
			ga('set', gaCustomDimensions['timestamp'], localTimestamp());
			ga('set', gaCustomDimensions['sessionNotes'], sessionNote('JS Resource Load Error'));
			ga('send', 'event', 'Error', 'JS Error', 'Google Maps Places script could not be loaded.', {'nonInteraction': 1});
		});
	}
}

//Request Geolocation
function requestPosition(){
    jQuery.getScript('https://www.google.com/jsapi', function(){
	    google.load('maps', '3', {
		    other_params: 'libraries=places',
		    callback: function(){
		        var nav = null;
			    if (nav === null){
			        nav = window.navigator;
			    }
			    var geolocation = nav.geolocation;
			    if ( geolocation != null ){
			        geolocation.getCurrentPosition(successCallback, errorCallback, {enableHighAccuracy: true}); //One-time location poll
			        //geoloc.watchPosition(successCallback, errorCallback, {enableHighAccuracy: true}); //Continuous location poll (This will update the nebula.session.geolocation object regularly, but be careful sending events to GA- may result in TONS of events)
			    }
			} //End Google Maps callback
	    }); //End Google Maps load
	}).fail(function(){
		ga('send', 'event', 'Error', 'JS Error', 'Google Maps Places script could not be loaded.', {'nonInteraction': 1});
	});
}

//Geolocation Success
function successCallback(position){
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
    }

	if ( nebula.session.geolocation.accuracy.meters < 50 ){
		nebula.session.geolocation.accuracy.color = '#00bb00';
        ga('set', gaCustomDimensions['geoAccuracy'], 'Excellent (<50m)');
    } else if ( nebula.session.geolocation.accuracy.meters > 50 && nebula.session.geolocation.accuracy.meters < 300 ){
        nebula.session.geolocation.accuracy.color = '#a4ed00';
        ga('set', gaCustomDimensions['geoAccuracy'], 'Good (50m - 300m)');
    } else if ( nebula.session.geolocation.accuracy.meters > 300 && nebula.session.geolocation.accuracy.meters < 1500 ){
        nebula.session.geolocation.accuracy.color = '#ffc600';
        ga('set', gaCustomDimensions['geoAccuracy'], 'Poor (300m - 1500m)');
    } else {
        nebula.session.geolocation.accuracy.color = '#ff1900';
        ga('set', gaCustomDimensions['geoAccuracy'], 'Very Poor (>1500m)');
    }

	addressLookup(position.coords.latitude, position.coords.longitude);

	sessionStorage['nebulaSession'] = JSON.stringify(nebula.session);
	nebula.dom.document.trigger('geolocationSuccess');
	nebula.dom.body.addClass('geo-latlng-' + nebula.session.geolocation.coordinates.latitude.toFixed(4).replace('.', '_') + '_' + nebula.session.geolocation.coordinates.longitude.toFixed(4).replace('.', '_') + ' geo-acc-' + nebula.session.geolocation.accuracy.meters.toFixed(0).replace('.', ''));
	debugInfo();
	ga('set', gaCustomDimensions['geolocation'], nebula.session.geolocation.coordinates.latitude.toFixed(4) + ', ' + nebula.session.geolocation.coordinates.longitude.toFixed(4));
	ga('set', gaCustomDimensions['timestamp'], localTimestamp());
	ga('send', 'event', 'Geolocation', nebula.session.geolocation.coordinates.latitude.toFixed(4) + ', ' + nebula.session.geolocation.coordinates.longitude.toFixed(4), 'Accuracy: ' + nebula.session.geolocation.accuracy.meters.toFixed(2) + ' meters');
}

//Geolocation Error
function errorCallback(error){
    switch (error.code){
        case error.PERMISSION_DENIED:
            geolocationErrorMessage = 'Access to your location is turned off. Change your settings to report location data.';
            var geoErrorNote = 'Denied';
            break;
        case error.POSITION_UNAVAILABLE:
            geolocationErrorMessage = "Data from location services is currently unavailable.";
            var geoErrorNote = 'Unavailable';
            break;
        case error.TIMEOUT:
            geolocationErrorMessage = "Location could not be determined within a specified timeout period.";
            var geoErrorNote = 'Timeout';
            break;
        default:
        	geolocationErrorMessage = "An unknown error has occurred.";
        	var geoErrorNote = 'Error';
            break;
    }

    nebula.session.geolocation = {
	    error: {
		    code: error.code,
			description: geolocationErrorMessage
	    }
    }

    nebula.dom.document.trigger('geolocationError');
    nebula.dom.body.addClass('geo-error');
	debugInfo();
    ga('set', gaCustomDimensions['geolocation'], geolocationErrorMessage);
    ga('set', gaCustomDimensions['timestamp'], localTimestamp());
    ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Geolocation Error (' + geoErrorNote + ')'));
    ga('send', 'event', 'Geolocation', 'Error', geolocationErrorMessage, {'nonInteraction': 1});
}

//Rough address Lookup
//If needing to look up an address that isn't the user's geolocation based on lat/long, consider a different function. This one stores user data.
function addressLookup(lat, lng){
	geocoder = new google.maps.Geocoder();
	latlng = new google.maps.LatLng(lat, lng); //lat, lng
	geocoder.geocode({'latLng': latlng}, function(results, status){
		if ( status == google.maps.GeocoderStatus.OK ){
			if ( results ){
				nebula.session.geolocation.address = {
					number: results[0].address_components[0].long_name,
					street: results[0].address_components[1].long_name,
					city: results[0].address_components[2].long_name,
					town: results[0].address_components[3].long_name,
					county: results[0].address_components[4].long_name,
					state: results[0].address_components[5].long_name,
					country: results[0].address_components[6].long_name,
					zip: results[0].address_components[7].long_name,
					formatted: results[0].formatted_address,
					place: {
						id: results[0].place_id,
					},
				};

				sessionStorage['nebulaSession'] = JSON.stringify(nebula.session);
				nebula.dom.document.trigger('addressSuccess');
				if ( nebula.session.geolocation.accuracy.meters < 100 ){
					placeLookup(results[0].place_id);
				}
			}
		}
	});
}

//Lookup place information
function placeLookup(placeID){
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
						reviews: place.reviews.length
					},
					utc_offset: place.utc_offset,
				}

				sessionStorage['nebulaSession'] = JSON.stringify(nebula.session);
				nebula.dom.document.trigger('placeSuccess');
			}
		}
	});
}

/*==========================
 Helper Functions
 These functions enhance other aspects of the site like HTML/CSS.
 ===========================*/

//Zebra-striper, First-child/Last-child, Hover helper functions, add "external" rel to outbound links
function addHelperClasses(){
	jQuery('li:even, tr:even').not('.dataTables_wrapper tr').addClass('even'); //IE8 support
	jQuery('li:odd, tr:odd').not('.dataTables_wrapper tr').addClass('odd'); //IE8 support
	jQuery('ul:first-child, li:first-child, tr:first-child').addClass('first-child'); //IE6 support
	jQuery('li:last-child, tr:last-child').addClass('last-child'); //IE8 support
	jQuery('.column:first-child, .columns:first-child').addClass('first-child'); //IE6 support
	jQuery('a:hover, li:hover, tr:hover').addClass('hover'); //IE8 support
	jQuery('a').each(function(){
		var a = new RegExp('/' + window.location.host + '/');
		if ( !a.test(this.href) ){
			if ( this.href.indexOf('http') !== -1 ){ //excludes all non-http link (ex: mailto: and tel:)
				var rel = ( typeof jQuery(this).attr('rel') !== 'undefined' ? jQuery(this).attr('rel') + ' ' : '' );
				jQuery(this).attr('rel', rel + 'external');
			}
		}
	});
	jQuery('a.icon img, li.icon a img').each(function(){
		jQuery(this).parent('a').removeClass('icon').addClass('no-icon'); //Remove filetype icons from images within <a> tags.
	});
}

//Try to fix some errors automatically
function errorMitigation(){
	//Try to fall back to .png on .svg errors. Else log the broken image.
	jQuery('img').on('error', function(){
		thisImage = jQuery(this);
		imagePath = thisImage.attr('src');
		if ( imagePath.split('.').pop() === 'svg' ){
			fallbackPNG = imagePath.replace('.svg', '.png');
			jQuery.get(fallbackPNG).done(function(){
				thisImage.prop('src', fallbackPNG);
				thisImage.removeClass('svg');
			}).fail(function() {
				ga('set', gaCustomDimensions['sessionNotes'], sessionNote('SVG Migitation Error'));
				ga('send', 'event', 'Error', 'Broken Image', imagePath, {'nonInteraction': 1});
			});
		} else {
			ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Broken Image'));
			ga('send', 'event', 'Error', 'Broken Image', imagePath, {'nonInteraction': 1});
		}
	});
}

//Convert img tags with class .svg to raw SVG elements
function svgImgs(){
	jQuery('img.svg').each(function(){
        var oThis = jQuery(this);

		if ( oThis.attr('src').indexOf('.svg') >= 1 ){
	        jQuery.get(oThis.attr('src'), function(data){
	            var theSVG = jQuery(data).find('svg'); //Get the SVG tag, ignore the rest
	            theSVG = theSVG.attr('id', oThis.attr('id')); //Add replaced image's ID to the new SVG
	            theSVG = theSVG.attr('class', oThis.attr('class') + ' replaced-svg'); //Add replaced image's classes to the new SVG
	            theSVG = theSVG.removeAttr('xmlns:a'); //Remove invalid XML tags
	            oThis.replaceWith(theSVG); //Replace image with new SVG
	        }, 'xml');
        }
    });
}

//Column height equalizer
function nebulaEqualize(){
	jQuery('.row.equalize').each(function(){
		var oThis = jQuery(this);
		tallestColumn = 0;
		oThis.children('.columns').css('min-height', '0').each(function(i){
			if ( !jQuery(this).hasClass('no-equalize') ){
				columnHeight = jQuery(this).outerHeight();
				if ( columnHeight > tallestColumn ){
					tallestColumn = columnHeight;
				}
			}
		});
		oThis.find('.columns').css('min-height', tallestColumn);
	});

	nebula.dom.document.on('nebula_infinite_finish', function(){
		nebulaEqualize();
	});
}

//Power Footer Width Distributor
function powerFooterWidthDist(){
	var powerFooterWidth = jQuery('#powerfooter').width();
	var powerFooterTopLIs = jQuery('#powerfooter ul.menu > li');
	var topLevelFooterItems = 0;
	powerFooterTopLIs.each(function(){
		topLevelFooterItems = topLevelFooterItems+1;
	});
	var footerItemWidth = powerFooterWidth/topLevelFooterItems-8;
	if ( topLevelFooterItems === 0 ){
		jQuery('.powerfootercon').addClass('hidden');
	} else {
		powerFooterTopLIs.css('width', footerItemWidth);
	}
}

function nebulaScrollTo(element, milliseconds){
	var headerHtOffset = ( jQuery('.headroom').length )? jQuery('.headroom').outerHeight() : 0; //Note: This selector should be the height of the fixed header, or a hard-coded offset.

	//Call this function with a selector to trigger scroll to an element (note: not a selector).
	if ( element ){
		if ( !milliseconds ){
			var milliseconds = 1000;
		}
		jQuery('html, body').animate({
			scrollTop: element.offset().top-headerHtOffset
		}, milliseconds);
		return false;
	}

	nebula.dom.document.on('click touch tap', 'a[href^=#]:not([href=#])', function(){ //Using an ID as the href
		if ( jQuery(this).parents('.mm-menu').is('*') ){
			return false;
		}

		pOffset = ( jQuery(this).attr('offset') )? parseFloat(jQuery(this).attr('offset')) : 0;
		if ( location.pathname.replace(/^\//, '') === this.pathname.replace(/^\//, '') && location.hostname === this.hostname ){
			var target = jQuery(this.hash);
			target = ( target.length )? target : jQuery('[name=' + this.hash.slice(1) +']');
			if ( target.length ){ //If target exists
				var nOffset = Math.floor(target.offset().top-headerHtOffset+pOffset);
				jQuery('html, body').animate({
					scrollTop: nOffset
				}, 500);
				return false;
			}
		}
	});

	nebula.dom.document.on('click tap touch', '.nebula-scrollto', function(){ //Using the nebula-scrollto class with scrollto attribute.
		pOffset = ( jQuery(this).attr('offset') )? parseFloat(jQuery(this).attr('offset')) : 0;
		if ( jQuery(this).attr('scrollto') ){
			var scrollElement = jQuery(this).attr('scrollto');
			if ( scrollElement != '' ){
				jQuery('html, body').animate({
					scrollTop: Math.floor(jQuery(scrollElement).offset().top-headerHtOffset+pOffset)
				}, 500);
			}
		}
		return false;
	});
}


/*==========================
 Utility Functions
 These functions simplify and enhance other JavaScript functions
 ===========================*/

//Get query string parameters
function getQueryStrings(){
	queries = {};
    var q = document.URL.split('?')[1];
    if ( q ){
        q = q.split('&');
        for ( var i = 0; i < q.length; i++ ){
            hash = q[i].split('=');
            if ( hash[1] ){
	            queries[hash[0]] = hash[1];
            } else {
	            queries[hash[0]] = true;
            }
        }
	}
}

//Search query strings for the passed parameter
function get(query){
	if ( !query ){
		return queries;
	} else {
		return queries[query];
	}
	return false;
}

//Trigger a reflow on an element.
//This is useful for repeating animations.
function reflow(selector){
	if ( typeof selector === 'string' ){
		var element = jQuery(selector);
	} else if ( typeof selector === 'object' ) {
		var element = selector;
	} else {
		return false;
	}

	jQuery(selector).width();
}

//Allows something to be called once per pageload.
//Call without self-executing parenthesis in the parameter! Ex: once(customFunction, 'test example');
//To add parameters, use an array as the 2nd parameter. Ex: once(customFunction, ['parameter1', 'parameter2'], 'test example');
//Can be used for boolean. Ex: once('boolean test');
function once(fn, args, unique){
	if ( typeof onces === 'undefined' ){
		onces = {};
	}

	if ( typeof fn === 'function' ){ //If the first parameter is a function
		if ( typeof args === 'string' ){ //If no parameters
			args = [];
			unique = args;
		}

		if ( typeof onces[unique] === 'undefined' || !onces[unique] ){
			onces[unique] = true;
			return fn.apply(this, args);
		}
	} else { //Else return boolean
		unique = fn; //If only one parameter is passed
		if ( typeof onces[unique] === 'undefined' || !onces[unique] ){
			onces[unique] = true;
			return true;
		} else {
			return false;
		}
	}
}


//Waits for events to finish before triggering
//Passing immediate triggers the function on the leading edge (instead of the trailing edge).
var debounceTimers = {};
function debounce(callback, wait, uniqueId, immediate){
    if ( !uniqueId ){
		uniqueId = "Don't call this twice without a uniqueId";
	}

    var context = this, args = arguments;
    var later = function(){
        debounceTimers[uniqueId] = null;
        if ( !immediate ){
	        callback.apply(context, args);
	    }
    };
    var callNow = immediate && !debounceTimers[uniqueId];

    clearTimeout(debounceTimers[uniqueId]);
    debounceTimers[uniqueId] = setTimeout(later, wait);
    if ( callNow ){
	    callback.apply(context, args);
	}
};

//Cookie Management
function createCookie(name, value, days){
	if ( !days ){
		var days = 3650; //10 years
	}

	if ( days ){
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires=" + date.toGMTString();
	} else {
		var expires = "";
	}
	document.cookie = name + "=" + value + expires + "; path=/";
}
function readCookie(name){
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for ( var i = 0; i < ca.length; i++ ){
		var c = ca[i];
		while ( c.charAt(0) === ' ' ){
			c = c.substring(1, c.length);
			if ( c.indexOf(nameEQ) === 0 ){
				return c.substring(nameEQ.length, c.length);
			}
		}
	}
	return null;
}
function eraseCookie(name){
	createCookie(name, "", -1);
}

//Add data to dynamic conversion object
function nebulaConversion(category, data, action){
	if ( !action ){
		var action = 'add';
	}

	if ( action === 'remove' ){
		if ( typeof nebula.user.conversions[category] !== 'undefined' ){
			jQuery.each(nebula.user.conversions[category], function(i){
				if ( nebula.user.conversions[category][i] === data ){
					nebula.user.conversions[category].splice(i, 1);
				}

				if ( nebula.user.conversions[category].length <= 0 ){
					delete nebula.user.conversions[category];
				}
			});
		}
		createCookie('nebulaUser', JSON.stringify(nebula.user));
	} else {
		if ( !data ){
			var data = 'true';
		}

		nebula.user.conversions = nebula.user.conversions || {};
		nebula.user.conversions[category] = nebula.user.conversions[category] || [];

		if ( nebula.user.conversions[category].indexOf(data.replace(/"|%22/g, '')) < 0 ){ //If not already in the array
			nebula.user.conversions[category].push(data.replace(/"|%22/g, ''));
			createCookie('nebulaUser', JSON.stringify(nebula.user));
		}
	}

	if ( typeof debugInfo === 'function' ){
		debugInfo();
	}
}

//Time specific events. Unique ID is required. Returns time in milliseconds.
//Data can be accessed outside of this function via nebulaTimings array.
function nebulaTimer(uniqueID, action, name){
	if ( typeof window.nebulaTimings === 'undefined' ){
		window.nebulaTimings = [];
	}

	//uniqueID is required
	if ( !uniqueID || uniqueID === 'start' || uniqueID === 'lap' || uniqueID === 'end' ){
		return false;
	}

	if ( !action ){
		if ( typeof nebulaTimings[uniqueID] === 'undefined' ){
			action = 'start';
		} else {
			action = 'lap';
		}
	}

	//Can not lap or end a timing that has not started.
	if ( action != 'start' && typeof nebulaTimings[uniqueID] === 'undefined' ){
		return false;
	}

	//Can not modify a timer once it has ended.
	if ( typeof nebulaTimings[uniqueID] !== 'undefined' && nebulaTimings[uniqueID].total > 0 ){
		return nebulaTimings[uniqueID].total;
	}

	//Update the timing data!
	currentDate = new Date();
	currentTime = currentDate.getTime();

	if ( action === 'start' && typeof nebulaTimings[uniqueID] === 'undefined' ){
		nebulaTimings[uniqueID] = {};
		nebulaTimings[uniqueID].started = currentTime;
		nebulaTimings[uniqueID].cumulative = 0;
		nebulaTimings[uniqueID].total = 0;
		nebulaTimings[uniqueID].lap = [];
		nebulaTimings[uniqueID].laps = 0;

		thisLap = {
			name: false,
			started: currentTime,
			stopped: 0,
			duration: 0,
			progress: 0,
		};
		nebulaTimings[uniqueID].lap.push(thisLap);

		if ( typeof name !== 'undefined' ){
			nebulaTimings[uniqueID].lap[0].name = name;
		}
	} else {
		lapNumber = nebulaTimings[uniqueID].lap.length;

		//Finalize the times for the previous lap
		nebulaTimings[uniqueID].lap[lapNumber-1].stopped = currentTime;
		nebulaTimings[uniqueID].lap[lapNumber-1].duration = currentTime-nebulaTimings[uniqueID].lap[lapNumber-1].started;
		nebulaTimings[uniqueID].lap[lapNumber-1].progress = currentTime-nebulaTimings[uniqueID].started;
		nebulaTimings[uniqueID].cumulative = currentTime-nebulaTimings[uniqueID].started;

		//An "out" lap means the timing for this lap may not be associated directly with the action (Usually resetting for the next actual timed lap).
		if ( action === 'start' ){
			nebulaTimings[uniqueID].lap[lapNumber-1].out = true;
		} else {
			nebulaTimings[uniqueID].lap[lapNumber-1].out = false;
		}

		//Prepare the current lap
		if ( action != 'end' ){
			nebulaTimings[uniqueID].laps++;
			if ( lapNumber > 0 ){
				nebulaTimings[uniqueID].lap[lapNumber] = {};
				nebulaTimings[uniqueID].lap[lapNumber].started = nebulaTimings[uniqueID].lap[lapNumber-1].stopped;
			}

			if ( typeof name !== 'undefined' ){
				nebulaTimings[uniqueID].lap[lapNumber].name = name;
			}
		}

		//Return individual lap times unless 'end' is passed- then return total duration. Note: 'end' can not be updated more than once per uniqueID! Subsequent calls will return the total duration from first call.
		if ( action === 'end' ){
			nebulaTimings[uniqueID].stopped = currentTime;
			nebulaTimings[uniqueID].total = currentTime-nebulaTimings[uniqueID].started;
			return nebulaTimings[uniqueID].total;
		} else {
			if ( !nebulaTimings[uniqueID].lap[lapNumber-1].out ){
				return nebulaTimings[uniqueID].lap[lapNumber-1].duration;
			}
		}
	}
}

//Convert milliseconds into separate hours, minutes, and seconds string (Ex: "3h 14m 35.2s").
function millisecondsToString(ms){
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
}

//Convert time to relative.
function timeAgo(time){ //http://af-design.com/blog/2009/02/10/twitter-like-timestamps/
	var system_date = new Date(time);
	var user_date = new Date();
	var diff = Math.floor((user_date-system_date)/1000);
	if (diff <= 1) return "just now";
	if (diff < 20) return diff + " seconds ago";
	if (diff < 60) return "less than a minute ago";
	if (diff <= 90) return "one minute ago";
	if (diff <= 3540) return Math.round(diff/60) + " minutes ago";
	if (diff <= 5400) return "1 hour ago";
	if (diff <= 86400) return Math.round(diff/3600) + " hours ago";
	if (diff <= 129600) return "1 day ago";
	if (diff < 604800) return Math.round(diff/86400) + " days ago";
	if (diff <= 777600) return "1 week ago";
	return "on " + system_date;
}


/*==========================
 Miscellaneous Functions
 ===========================*/

//Functionality for selecting and copying text using Nebula Pre tags.
function nebula_pre(){
	try { //@TODO "Nebula" 0: Use Modernizr check here instead.
		if ( document.queryCommandEnabled("SelectAll") ){ //@TODO "Nebula" 0: If using document.queryCommandSupported("copy") it always returns false (even though it does actually work when execCommand('copy') is called.
			var selectCopyText = 'Copy to clipboard';
			nebula.user.client.capabilities.clipboard.copy = true;
			nebula.user.client.capabilities.select_text = true;
		} else if ( document.body.createTextRange || window.getSelection ){
			var selectCopyText = 'Select All';
			nebula.user.client.capabilities.clipboard.copy = false;
			nebula.user.client.capabilities.clipboard.paste = false;
			nebula.user.client.capabilities.select_text = true;
		} else {
			nebula.user.client.capabilities.clipboard.copy = false;
			nebula.user.client.capabilities.clipboard.paste = false;
			nebula.user.client.capabilities.select_text = false;
			return false;
		}
	} catch(err){
		if ( document.body.createTextRange || window.getSelection ){
			var selectCopyText = 'Select All';
			nebula.user.client.capabilities.clipboard.copy = false;
			nebula.user.client.capabilities.clipboard.paste = false;
			nebula.user.client.capabilities.select_text = true;
		} else {
			nebula.user.client.capabilities.clipboard.copy = false;
			nebula.user.client.capabilities.clipboard.paste = false;
			nebula.user.client.capabilities.select_text = false;
			return false;
		}
	}
	createCookie('nebulaUser', JSON.stringify(nebula.user));

	//Format non-shortcode pre tags to be styled properly
	jQuery('pre.nebula-code').each(function(){
		if ( !jQuery(this).parent('.nebula-pre-con').is('*') ){
			lang = jQuery(this).attr('class').replace('nebula-code', '').trim();
			jQuery(this).addClass(lang.toLowerCase()).wrap('<div class="nebula-pre-con clearfix ' + lang.toLowerCase() + '"></div>');
			jQuery(this).parents('.nebula-pre-con').prepend('<span class="nebula-pre nebula-code codetitle ' + lang.toLowerCase() + '">' + lang + '</span>');
		}
	});

	jQuery('.nebula-pre-con').each(function(){
		jQuery(this).append('<a href="#" class="nebula-selectcopy-code">' + selectCopyText + '</a>');
	});

	nebula.dom.document.on('click touch tap', '.nebula-selectcopy-code', function(){
	    oThis = jQuery(this);

	    if ( jQuery(this).text() === 'Copy to clipboard' ){
		    selectText(jQuery(this).parents('.nebula-pre-con').find('pre'), 'copy', function(success){
			    if ( success ){
				    oThis.text('Copied!').removeClass('error').addClass('success');
				    setTimeout(function(){
					    oThis.text('Copy to clipboard').removeClass('success');
				    }, 1500);
			    } else {
				    jQuery('.nebula-selectcopy-code').each(function(){
					    jQuery(this).text('Select All');
				    });
				    oThis.text('Unable to copy.').addClass('error');
				    setTimeout(function(){
					    oThis.text('Select All').removeClass('error');
				    }, 3500);
			    }
		    });
	    } else {
		    selectText(jQuery(this).parents('.nebula-pre-con').find('pre'), function(success){
			    if ( success ){
				    oThis.text('Selected!').removeClass('error').addClass('success');
				    setTimeout(function(){
					    oThis.text('Select All').removeClass('success');
				    }, 1500);
			    } else {
				    jQuery('.nebula-selectcopy-code').each(function(){
					    jQuery(this).hide();
				    });
				    oThis.text('Unable to select.').addClass('error');
			    }
		    });
	    }
		return false;
	});
}

//Select (and optionally copy) text
function selectText(element, copy, callback){
	if ( typeof element === 'string' ){
		element = jQuery(element)[0];
	} else if ( typeof element === 'object' && element.nodeType !== 1 ){
		element = element[0];
	}

	if ( typeof copy === 'function' ){
		callback = copy;
		copy = null;
	}

	try {
		if ( document.body.createTextRange ){
			var range = document.body.createTextRange();
			range.moveToElementText(element);
			range.select();
			if ( !copy && callback ){
				callback(true);
				return false;
			}
		} else if ( window.getSelection ){
			var selection = window.getSelection();
			var range = document.createRange();
			range.selectNodeContents(element);
			selection.removeAllRanges();
			selection.addRange(range);
			if ( !copy && callback ){
				callback(true);
				return false;
			}
		}
	} catch(err){
		if ( callback ){
			callback(false);
			return false;
		}
	}

	if ( copy ){
		try {
			var success = document.execCommand('copy');
			if ( callback ){
				callback(success);
				return false;
			}
		} catch(err){
			if ( callback ){
				callback(false);
				return false;
			}
		}
	}

	if ( callback ){
		callback(false);
	}
	return false;
}

function chosenSelectOptions(){
	jQuery('.chosen-select').chosen({
		disable_search_threshold: 5,
		search_contains: true,
		no_results_text: "No results found.",
		allow_single_deselect: true,
		width: "100%"
	});
}

function dataTablesActions(){
	nebula.dom.document.on('keyup', '.dataTables_wrapper .dataTables_filter input', function(){ //@TODO "Nebula" 0: Something here is eating the first letter after a few have been typed... lol
	    //console.log('keyup: ' + jQuery(this).val());
	    //jQuery('.dataTables_wrapper').removeHighlight();
	    //jQuery('.dataTables_wrapper').highlight(jQuery(this).val());
	});
}


//Place all bxSlider events inside this function!
function bxSlider(){
	if ( typeof bxSlider !== 'undefined' ){
		jQuery('.exampleslider').bxSlider({
			mode: 'horizontal', //'horizontal', 'vertical', 'fade'
			speed: 800,
			captions: false,
			auto: true,
			pause: 6000,
			autoHover: true,
			adaptiveHeight: true,
			useCSS: false,
			easing: 'easeInOutCubic',
			controls: false
		});

		jQuery('.heroslider').bxSlider({
			mode: 'fade',
			speed: 800,
			captions: false,
			pager: false,
			auto: false,
			pause: 10000,
			autoHover: true,
			adaptiveHeight: true,
			useCSS: true,
			controls: true
		});
	}
}


//Check for Youtube Videos
function checkForYoutubeVideos(){
	if ( jQuery('.youtubeplayer').length ){
		var tag = document.createElement('script');
		tag.src = "https://www.youtube.com/iframe_api";
		var firstScriptTag = document.getElementsByTagName('script')[0];
		firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
	}
}
function onYouTubeIframeAPIReady(e){
	if ( typeof players === 'undefined' ){
		players = {
			youtube: {},
			vimeo: {},
		};
		videoData = {};
	}
	jQuery('iframe.youtubeplayer').each(function(i){
		var youtubeiframeID = jQuery(this).attr('id');
		players.youtube[youtubeiframeID] = new YT.Player(youtubeiframeID, {
			events: {
				onReady: onPlayerReady,
				onStateChange: onPlayerStateChange,
				onError: onPlayerError
			}
		});
	});
	pauseFlag = false;
}
function onPlayerError(e){
	var videoTitle = e.target.H.videoData.title;
	ga('set', gaCustomDimensions['timestamp'], localTimestamp());
	ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Youtube Error'));
	ga('send', 'event', 'Error', 'Youtube API', videoTitle + ' (Code: ' + e.data + ')', {'nonInteraction': 1});
}
function onPlayerReady(e){
	if ( typeof videoProgress === 'undefined' ){
		videoProgress = {};
	}

	var id = e.target.f.id;
	videoData[id] = {
		platform: 'youtube', //The platform the video is hosted using.
		player: players.youtube[id], //The player ID of this video. Can access the API here.
		duration: e.target.H.duration, //The total duration of the video. Unit: Seconds
		current: e.target.H.currentTime, //The current position of the video. Units: Seconds
		percent: e.target.H.currentTime/e.target.H.duration, //The percent of the current position. Multiply by 100 for actual percent.
		engaged: false, //Whether the viewer has watched enough of the video to be considered engaged.
		watched: 0, //Amount of time watching the video (regardless of seeking). Accurate to half a second. Units: Seconds
		watchedPercent: 0, //The decimal percentage of the video watched. Multiply by 100 for actual percent.
	};
}
function onPlayerStateChange(e){
	var videoTitle = e.target.H.videoData.title;
	var id = e.target.f.id;

	videoData[id].current = e.target.H.currentTime;
	videoData[id].percent = e.target.H.currentTime/e.target.H.duration;

    if ( e.data === YT.PlayerState.PLAYING ){
	    ga('set', gaCustomMetrics['videoStarts'], 1);
        ga('set', gaCustomDimensions['videoWatcher'], 'Started');
        ga('set', gaCustomDimensions['timestamp'], localTimestamp());
        ga('send', 'event', 'Videos', 'Play', videoTitle);
        nebulaConversion('videos', 'Youtube Played: ' + videoTitle);
        pauseFlag = true;
		updateInterval = 500;

		youtubePlayProgress = setInterval(function(){
			videoData[id].current = e.target.H.currentTime;
			videoData[id].percent = e.target.H.currentTime/e.target.H.duration;
			videoData[id].watched = videoData[id].watched+(updateInterval/1000);
			videoData[id].watchedPercent = (videoData[id].watched)/e.target.H.duration;

			if ( videoData[id].watchedPercent > 0.25 && !videoData[id].engaged ){
				ga('set', gaCustomDimensions['videoWatcher'], 'Engaged');
				ga('send', 'event', 'Videos', 'Engaged', videoTitle, {'nonInteraction': 1});
				nebulaConversion('videos', 'Youtube Engaged: ' + videoTitle);
				videoData[id].engaged = true;
			}
		}, updateInterval);
    }
    if ( e.data === YT.PlayerState.ENDED ){
        clearTimeout(youtubePlayProgress);
        ga('set', gaCustomMetrics['videoCompletions'], 1);
        ga('set', gaCustomMetrics['videoPlaytime'], Math.round(videoData[id].watched/1000));
        ga('set', gaCustomDimensions['videoWatcher'], 'Finished');
        ga('set', gaCustomDimensions['timestamp'], localTimestamp());
        ga('send', 'event', 'Videos', 'Finished', videoTitle, {'nonInteraction': 1});
        ga('send', 'timing', 'Videos', 'Finished', videoData[id].watched*1000, videoTitle); //Amount of time watched (can exceed video duration).
        nebulaConversion('videos', 'Youtube Finished: ' + videoTitle);
    } else if ( e.data === YT.PlayerState.PAUSED && pauseFlag ){
        clearTimeout(youtubePlayProgress);
        ga('set', gaCustomMetrics['videoPlaytime'], Math.round(videoData[id].watched));
        ga('set', gaCustomDimensions['videoPercentage'], Math.round(videoData[id].percent*100));
        ga('set', gaCustomDimensions['videoWatcher'], 'Paused');
        ga('set', gaCustomDimensions['timestamp'], localTimestamp());
        ga('send', 'event', 'Videos', 'Pause', videoTitle);
        ga('send', 'timing', 'Videos', 'Paused (Watched)', videoData[id].watched*1000, videoTitle); //Amount of time watched, not the timestamp of when paused!
        nebulaConversion('videos', 'Youtube Paused: ' + videoTitle);
        pauseFlag = false;
    }
}

function vimeoControls(){
	//Load the Vimeo API script (froogaloop) remotely (with local backup)
	if ( jQuery('.vimeoplayer').is('*') ){
        jQuery.getScript('https://f.vimeocdn.com/js/froogaloop2.min.js').done(function(){
			createVimeoPlayers();
		}).fail(function(){
			ga('set', gaCustomDimensions['sessionNotes'], sessionNote('JS Resource Load Error'));
			ga('send', 'event', 'Error', 'JS Error', 'froogaloop (remote) could not be loaded.', {'nonInteraction': 1});
			jQuery.getScript(nebula.site.directory.template.uri + '/js/libs/froogaloop.min.js').done(function(){
				createVimeoPlayers();
			}).fail(function(){
				ga('set', gaCustomDimensions['sessionNotes'], sessionNote('JS Resource Load Error'));
				ga('send', 'event', 'Error', 'JS Error', 'froogaloop (local) could not be loaded.', {'nonInteraction': 1});
			});
		});
	}

	//To trigger events on these videos, use the syntax: players['PHG-Overview-Video'].api("play");
	function createVimeoPlayers(){
	    if ( typeof players === 'undefined' ){
			players = {
				youtube: {},
				vimeo: {},
			};
			videoData = {};
		}
	    jQuery('iframe.vimeoplayer').each(function(i){
			var vimeoiframeID = jQuery(this).attr('id');
			players.vimeo[vimeoiframeID] = $f(vimeoiframeID);
			players.vimeo[vimeoiframeID].addEvent('ready', function(id){
			    players.vimeo[id].addEvent('play', vimeoPlay);
			    players.vimeo[id].addEvent('pause', vimeoPause);
			    players.vimeo[id].addEvent('seek', vimeoSeek);
			    players.vimeo[id].addEvent('finish', vimeoFinish);
			    players.vimeo[id].addEvent('playProgress', vimeoPlayProgress);
			});
		});

		if ( typeof videoProgress === 'undefined' ){
			videoProgress = {};
		}
	}

	function vimeoPlay(id){
	    var videoTitle = id.replace(/-/g, ' ');
	    ga('set', gaCustomMetrics['videoStarts'], 1);
	    ga('set', gaCustomDimensions['videoWatcher'], 'Started');
	    ga('set', gaCustomDimensions['timestamp'], localTimestamp());
	    ga('send', 'event', 'Videos', 'Play', videoTitle);
	    nebulaConversion('videos', 'Vimeo Played: ' + videoTitle);
	}

	function vimeoPlayProgress(data, id){
		var videoTitle = id.replace(/-/g, ' ');

		if ( typeof videoData[id] === 'undefined' ){
		    videoData[id] = {
				platform: 'vimeo', //The platform the video is hosted using.
				player: players.vimeo[id], //The player ID of this video. Can access the API here. Units: Seconds
				duration: data.duration, //The total duration of the video. Units: Seconds
				current: data.seconds, //The current position of the video. Units: Seconds
				percent: data.percent, //The percent of the current position. Multiply by 100 for actual percent.
				engaged: false, //Whether the viewer has watched enough of the video to be considered engaged.
				seeker: false, //Whether the viewer has seeked through the video at least once.
				seen: [], //An array of percentages seen by the viewer. This is to roughly estimate how much was watched.
				watched: 0, //Amount of time watching the video (regardless of seeking). Accurate to 1% of video duration. Units: Seconds
				watchedPercent: 0, //The decimal percentage of the video watched. Multiply by 100 for actual percent.
			};
	    } else {
			videoData[id].duration = data.duration;
			videoData[id].current = data.seconds;
			videoData[id].percent = data.percent;

			//Determine watched percent by adding current percents to an array, then count the array!
			nowSeen = Math.ceil(data.percent*100);
			if ( videoData[id].seen.indexOf(nowSeen) < 0 ){
				videoData[id].seen.push(nowSeen);
			}
			videoData[id].watchedPercent = videoData[id].seen.length;
			videoData[id].watched = (videoData[id].seen.length/100)*videoData[id].duration; //Roughly calculate time watched based on percent seen
	    }

		if ( videoData[id].watchedPercent > 25 && !videoData[id].engaged ){
			ga('set', gaCustomDimensions['videoWatcher'], 'Engaged');
			ga('send', 'event', 'Videos', 'Engaged', videoTitle, {'nonInteraction': 1});
			nebulaConversion('videos', 'Vimeo Engaged: ' + videoTitle);
			videoData[id].engaged = true;
		}
	}

	function vimeoPause(id){
		var videoTitle = id.replace(/-/g, ' ');
		ga('set', gaCustomDimensions['videoWatcher'], 'Paused');
		ga('set', gaCustomMetrics['videoPlaytime'], Math.round(videoData[id].watched));
		ga('set', gaCustomDimensions['videoPercentage'], Math.round(videoData[id].percent*100));
		ga('set', gaCustomDimensions['timestamp'], localTimestamp());
		ga('send', 'event', 'Videos', 'Pause', videoTitle);
		ga('send', 'timing', 'Videos', 'Paused (Watched)', Math.round(videoData[id].watched*1000), videoTitle); //Roughly amount of time watched, not the timestamp of when paused!
		nebulaConversion('videos', 'Vimeo Paused: ' + videoTitle);
	}

	function vimeoSeek(data, id){
	    var videoTitle = id.replace(/-/g, ' ');
	    ga('set', gaCustomDimensions['videoWatcher'], 'Seeker');
	    ga('send', 'event', 'Videos', 'Seek', videoTitle + ' [to: ' + data.seconds + ']');
	    nebulaConversion('videos', 'Vimeo Seeked: ' + videoTitle);
	    videoData[id].seeker = true;
	}

	function vimeoFinish(id){
		var videoTitle = id.replace(/-/g, ' ');
		ga('set', gaCustomMetrics['videoCompletions'], 1);
		ga('set', gaCustomMetrics['videoPlaytime'], Math.round(videoData[id].watched));
		ga('set', gaCustomDimensions['videoWatcher'], 'Finished');
		ga('set', gaCustomDimensions['timestamp'], localTimestamp());
		ga('send', 'event', 'Videos', 'Finished', videoTitle, {'nonInteraction': 1});
		ga('send', 'timing', 'Videos', 'Finished', Math.round(videoData[id].watched*1000), videoTitle); //Roughly amount of time watched (Can not be over 100% for Vimeo)
		nebulaConversion('videos', 'Vimeo Finished: ' + videoTitle);
	}
}

//Pause all videos
//Use class "ignore-visibility" on iframes to allow specific videos to continue playing regardless of page visibility
//Pass force as true to pause no matter what.
function pauseAllVideos(force){
	if ( typeof players === 'undefined' ){
		return false; //If videos don't exist, then no need to pause
	}

	if ( typeof force === 'null' ){
		force = false;
	}

	//Pause Youtube Videos
	jQuery('iframe.youtubeplayer').each(function(){
		youtubeiframeID = jQuery(this).attr('id');
		if ( (force || !jQuery(this).hasClass('ignore-visibility')) && players.youtube[youtubeiframeID].getPlayerState() === 1 ){
			players.youtube[youtubeiframeID].pauseVideo();
		}
	});

	//Pause Vimeo Videos
	jQuery('iframe.vimeoplayer').each(function(){
		vimeoiframeID = jQuery(this).attr('id');
		if ( (force || !jQuery(this).hasClass('ignore-visibility')) ){
			players.vimeo[vimeoiframeID].api('pause');
		}
	});
}

//Helpful animation event listeners
function animationTriggers(){
	nebula.dom.document.on('click tap touch', '.nebula-push.click', function(){
		jQuery(this).removeClass('active');
		reflow(this);
		jQuery(this).addClass('active');
	});
}

//Create desktop notifications
function desktopNotification(title, message, clickCallback, showCallback, closeCallback, errorCallback){
	if ( checkNotificationPermission() ){
		//Set defaults
		var defaults = {
			dir: "auto", //Direction ["auto", "ltr", "rtl"] (optional)
			lang: "en-US", //Language (optional)
			body: "", //Body message (optional)
			tag: Math.floor(Math.random()*10000)+1, //Unique tag for notification. Prevents repeat notifications of the same tag. (optional)
			icon: nebula.site.directory.template.uri + "/images/meta/favicon-160x160.png" //Thumbnail Icon (optional)
		}

		if ( typeof message === "undefined" ){
			message = defaults;
		} else if ( typeof message === "string" ){
			body = message;
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

		instance = new Notification(title, message); //Trigger the notification //@TODO "Nebula" 0: This will be deprecated soon. Update to the service worker.

		if ( typeof clickCallback !== "undefined" ){
			instance.onclick = function(){
				clickCallback();
			};
		}
		if ( typeof showCallback !== "undefined" ){
            instance.onshow = function(e){
                showCallback();
            };
        } else {
            instance.onshow = function(e){
                setTimeout(function(){
                    instance.close();
                }, 20000);
            }
        }
		if ( typeof closeCallback !== "undefined" ){
			instance.onclose = function(){
				closeCallback();
			};
		}
		if ( typeof errorCallback !== "undefined" ){
			instance.onerror = function(){
				errorCallback();
			};
		}
	}
	return false;
}

function checkNotificationPermission(){
	Notification = window.Notification || window.mozNotification || window.webkitNotification;
	if ( !(Notification) ){
		return false;
	} else if ( Notification.permission === "granted" ){
		return true;
	} else if ( Notification.permission !== 'denied' ){
		Notification.requestPermission(function (permission){
			if( !('permission' in Notification) ){
				Notification.permission = permission;
			}
			if ( permission === "granted" ){
				return true;
			}
		});
	}
	return false;
}

function nebulaVibrate(pattern){
	if ( typeof pattern !== 'object' ){
		pattern = [100, 200, 100, 100, 75, 25, 100, 200, 100, 500, 100, 200, 100, 500];
	}
	if ( checkVibration() ){
		navigator.vibrate(pattern);
	}
	return false;
}

function checkVibration(){
	Vibration = navigator.vibrate || navigator.webkitVibrate || navigator.mozVibrate || navigator.msVibrate;
	if ( !(Vibration) ){
		return false;
	} else {
		return true;
	}
}

function moreEvents(bool){
	if ( !bool ){
		jQuery('#load-more-events').addClass('all-events-loaded');
	} else {
		jQuery('#load-more-events').removeClass('all-events-loaded');
	}
}

//Show/Hide the reset button
function eventFormNeedReset(){
	hasValue = false;

	//Check the category select dropdown
/*
	jQuery('#advanced-search-form select').each(function(){
		if ( jQuery(this).find('option:selected').val() && jQuery(this).find('option:selected').val() != '' ){
			jQuery('.resetfilters').addClass('active');
			hasValue = true;
			return false;
		}
	});
*/

	//@TODO "Nebula" 0: This is not disappearing when reset link itself is clicked.
	//Check all other inputs
	jQuery('#advanced-search-form input').each(function(){
		if ( (jQuery(this).attr('type') != 'checkbox' && jQuery(this).val() != '') || jQuery(this).prop("checked") ){
			jQuery('.resetfilters').addClass('active');
			hasValue = true;
			return false;
		}
	});

	if ( !hasValue ){
		jQuery('.resetfilters').removeClass('active');
	}
}

function mmenus(){
	if ( 'mmenu' in jQuery ){
		var mobileNav = jQuery('#mobilenav');
		var mobileNavTriggerIcon = jQuery('a.mobilenavtrigger i');

		if ( mobileNav.is('*') ){
			mobileNav.mmenu({
				//Options
				offCanvas: {
				    position: "left", //"left" (default), "right", "top", "bottom"
				    zposition: "back", //"back" (default), "front", "next"
			    },
				navbars: [{
					position: "top",
					content: ["searchfield"]
				}, {
					position: "bottom",
					content: ["<span>" + nebula.site.name + "</span>"]
				}],
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
			    iconPanels: false, //Layer panels on top of each other
			    extensions: ["theme-light", "effect-slide-menu", "pageshadow"] //Theming, effects, and other extensions
			}, {
				//Configuration
				classNames: {
					selected: "current-menu-item"
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
			});

			if ( mobileNav.length ){
				mobileNav.data('mmenu').bind('opening', function(){
					//When mmenu has started opening
					mobileNavTriggerIcon.removeClass('fa-bars').addClass('fa-times').parents('.mobilenavtrigger').addClass('active');
					nebulaTimer('mmenu', 'start');
				}).bind('opened', function(){
					//After mmenu has finished opening
					history.replaceState(null, document.title, location);
					history.pushState(null, document.title, location);
				}).bind('closing', function(){
					//When mmenu has started closing
					mobileNavTriggerIcon.removeClass('fa-times').addClass('fa-bars').parents('.mobilenavtrigger').removeClass('active');
					ga('send', 'timing', 'Mmenu', 'Closed', Math.round(nebulaTimer('mmenu', 'lap')), 'From opening mmenu until closing mmenu');
				}).bind('closed', function(){
					//After mmenu has finished closing
				});
			}

			nebula.dom.document.on('click tap touch', '.mm-menu li a:not(.mm-next)', function(){
				ga('send', 'timing', 'Mmenu', 'Navigated', Math.round(nebulaTimer('mmenu', 'lap')), 'From opening mmenu until navigation');
			});

			//Close mmenu on back button click
			if (window.history && window.history.pushState){
				window.addEventListener("popstate", function(e){
					if ( jQuery('html.mm-opened').is('*') ){
						mobileNav.data('mmenu').close();
						e.stopPropagation();
					}
				}, false);
			}
		}
	}
}

//Main dropdown nav dynamic width controller
function dropdownWidthController(){
	jQuery('#primarynav .sub-menu').each(function(){
		var bigWidth = 100;
			if ( jQuery(this).children().width() > bigWidth ){
				bigWidth = jQuery(this).children().width();
			}
		jQuery(this).css('width', bigWidth+15 + 'px');
	});
} //end dropdownWidthController()




//Vertical subnav expanders
function subnavExpanders(){
    jQuery('.xoxo .menu li.menu-item:has(ul)').addClass('has-expander').append('<a class="toplevelvert_expander closed" href="#"><i class="fa fa-caret-left"></i></a>');
    jQuery('.toplevelvert_expander').parent().children('.sub-menu').hide();
    nebula.dom.document.on('click touch tap', '.toplevelvert_expander', function(){
        jQuery(this).toggleClass('closed open').parent().children('.sub-menu').slideToggle();
        return false;
    });
    //Automatically expand subnav to show current page
    jQuery('.current-menu-ancestor').children('.toplevelvert_expander').click();
    jQuery('.current-menu-item').children('.toplevelvert_expander').click();
} //end subnavExpanders()

//Affix the logo/navigation when scrolling passed it
function initHeadroom(headerElement, footerElement, fixedElement){
	if ( !headerElement ){
		var headerElement = jQuery('#header');
	}

	if ( !footerElement ){
		var footerElement = jQuery('#footer');
	}

	if ( !fixedElement ){
		var fixedElement = jQuery('#logonavcon');
	}

	if ( once('headroom padding') ){
		needHeadroomPadding = ( fixedElement.css('position') === 'relative' )? true : false; //If positioned relative, then padding is needed.
	}

	if ( typeof fixedElement === 'undefined' || !fixedElement.is('*') ){
		return false;
	}

	if ( typeof headerElement === 'undefined' || !headerElement.is('*') ){
		headerElement = nebula.dom.body; //@TODO: If this fallback happens, the padding would need to move to the top.
	}

	if ( typeof headroom !== 'undefined' || !window.matchMedia("(min-width: 767px)").matches ){ //If headroom needs to be re-init or if tablet or mobile
		if ( !window.matchMedia("(min-width: 767px)").matches ){
			return false;
		}

		headroom.destroy();
	}

	var clonedFixedElement = fixedElement.clone().addClass('headroom--not-top').css({position: "absolute", left: "-10000px"}).appendTo('body'); //See the future: Get final height of fixedElement with unknown CSS properties
	var finalBufferSize = clonedFixedElement.outerHeight();
	clonedFixedElement.remove();

	window.headroom = new Headroom(fixedElement[0], {
		offset: fixedElement.offset().top, //Vertical offset in px before element is first unpinned
		tolerance: 3, //Scroll tolerance in px before state changes
		classes: {
			initial: "headroom", //When element is initialised
			pinned: "headroom--pinned", //When scrolling up
			unpinned: "headroom--unpinned", //When scrolling down
			top: "headroom--top", //When above offset
			notTop: "headroom--not-top" //When below offset
		},
		onPin: function(){ //Callback when pinned, 'this' is headroom object
			nebula.dom.document.removeClass('headroom--unpinned').addClass('headroom--pinned');
		},
		onUnpin: function(){ //Callback when unpinned, 'this' is headroom object
			nebula.dom.document.removeClass('headroom--pinned').addClass('headroom--unpinned');
		},
		onTop: function(){ //Callback when above offset, 'this' is headroom object
			nebula.dom.document.removeClass('headroom--not-top').addClass('headroom--top');
			if ( needHeadroomPadding ){
				headerElement.css('padding-bottom', '0');
			}
		},
		onNotTop: function(){ //Callback when below offset, 'this' is headroom object
			nebula.dom.document.removeClass('headroom--top').addClass('headroom--not-top');
			if ( needHeadroomPadding ){
				headerElement.css('padding-bottom', fixedElement.outerHeight()).stop().animate({paddingBottom: finalBufferSize}, 400, "linear"); //Add padding buffer to header and animate (slightly faster than CSS) to finalBufferSize
			}
		},
	});
	headroom.init();

	//Custom Nebula Headroom extensions
	nebula.dom.window.on('scroll', function(){
		var viewportBottom = nebula.dom.window.height()+nebula.dom.window.scrollTop();
		var documentHeight = nebula.dom.document.height();
		var scrollDistance = nebula.dom.document.scrollTop();

		//Add .headroom--below //@TODO "Nebula" 0: Could this be moved into onNotTop?
		if ( nebula.dom.document.scrollTop() > headerElement.offset().top+headerElement.outerHeight() ){
			fixedElement.addClass('headroom--below');
		} else if ( fixedElement.hasClass('headroom--below') ){
			fixedElement.removeClass('headroom--below');
		}

		//Add .headroom-bottom
		if ( viewportBottom >= documentHeight-(footerElement.outerHeight()/2) ){
			fixedElement.addClass('headroom--bottom');
		} else if ( fixedElement.hasClass('headroom--bottom') ){
			fixedElement.removeClass('headroom--bottom');
		}
	});
}



/*==========================
 Extension Functions
 ===========================*/

//Custom CSS expression for a case-insensitive contains(). Source: https://css-tricks.com/snippets/jquery/make-jquery-contains-case-insensitive/
//Call it with :Contains() - Ex: ...find("*:Contains(" + jQuery('.something').val() + ")")... -or- use the nebula function: keywordSearch(container, parent, value);
jQuery.expr[":"].Contains=function(e,n,t){return(e.textContent||e.innerText||"").toUpperCase().indexOf(t[3].toUpperCase())>=0};

//Parse dates (equivalent of PHP function). Source: https://raw.githubusercontent.com/kvz/phpjs/master/functions/datetime/strtotime.js
function strtotime(e,t){function a(e,t,a){var n,r=c[t];"undefined"!=typeof r&&(n=r-w.getDay(),0===n?n=7*a:n>0&&"last"===e?n-=7:0>n&&"next"===e&&(n+=7),w.setDate(w.getDate()+n))}function n(e){var t=e.split(" "),n=t[0],r=t[1].substring(0,3),s=/\d+/.test(n),u="ago"===t[2],i=("last"===n?-1:1)*(u?-1:1);if(s&&(i*=parseInt(n,10)),o.hasOwnProperty(r)&&!t[1].match(/^mon(day|\.)?$/i))return w["set"+o[r]](w["get"+o[r]]()+i);if("wee"===r)return w.setDate(w.getDate()+7*i);if("next"===n||"last"===n)a(n,r,i);else if(!s)return!1;return!0}var r,s,u,i,w,c,o,d,D,f,g,l=!1;if(!e)return l;if(e=e.replace(/^\s+|\s+$/g,"").replace(/\s{2,}/g," ").replace(/[\t\r\n]/g,"").toLowerCase(),s=e.match(/^(\d{1,4})([\-\.\/\:])(\d{1,2})([\-\.\/\:])(\d{1,4})(?:\s(\d{1,2}):(\d{2})?:?(\d{2})?)?(?:\s([A-Z]+)?)?$/),s&&s[2]===s[4])if(s[1]>1901)switch(s[2]){case"-":return s[3]>12||s[5]>31?l:new Date(s[1],parseInt(s[3],10)-1,s[5],s[6]||0,s[7]||0,s[8]||0,s[9]||0)/1e3;case".":return l;case"/":return s[3]>12||s[5]>31?l:new Date(s[1],parseInt(s[3],10)-1,s[5],s[6]||0,s[7]||0,s[8]||0,s[9]||0)/1e3}else if(s[5]>1901)switch(s[2]){case"-":return s[3]>12||s[1]>31?l:new Date(s[5],parseInt(s[3],10)-1,s[1],s[6]||0,s[7]||0,s[8]||0,s[9]||0)/1e3;case".":return s[3]>12||s[1]>31?l:new Date(s[5],parseInt(s[3],10)-1,s[1],s[6]||0,s[7]||0,s[8]||0,s[9]||0)/1e3;case"/":return s[1]>12||s[3]>31?l:new Date(s[5],parseInt(s[1],10)-1,s[3],s[6]||0,s[7]||0,s[8]||0,s[9]||0)/1e3}else switch(s[2]){case"-":return s[3]>12||s[5]>31||s[1]<70&&s[1]>38?l:(i=s[1]>=0&&s[1]<=38?+s[1]+2e3:s[1],new Date(i,parseInt(s[3],10)-1,s[5],s[6]||0,s[7]||0,s[8]||0,s[9]||0)/1e3);case".":return s[5]>=70?s[3]>12||s[1]>31?l:new Date(s[5],parseInt(s[3],10)-1,s[1],s[6]||0,s[7]||0,s[8]||0,s[9]||0)/1e3:s[5]<60&&!s[6]?s[1]>23||s[3]>59?l:(u=new Date,new Date(u.getFullYear(),u.getMonth(),u.getDate(),s[1]||0,s[3]||0,s[5]||0,s[9]||0)/1e3):l;case"/":return s[1]>12||s[3]>31||s[5]<70&&s[5]>38?l:(i=s[5]>=0&&s[5]<=38?+s[5]+2e3:s[5],new Date(i,parseInt(s[1],10)-1,s[3],s[6]||0,s[7]||0,s[8]||0,s[9]||0)/1e3);case":":return s[1]>23||s[3]>59||s[5]>59?l:(u=new Date,new Date(u.getFullYear(),u.getMonth(),u.getDate(),s[1]||0,s[3]||0,s[5]||0)/1e3)}if("now"===e)return null===t||isNaN(t)?(new Date).getTime()/1e3|0:0|t;if(!isNaN(r=Date.parse(e)))return r/1e3|0;if(w=t?new Date(1e3*t):new Date,c={sun:0,mon:1,tue:2,wed:3,thu:4,fri:5,sat:6},o={yea:"FullYear",mon:"Month",day:"Date",hou:"Hours",min:"Minutes",sec:"Seconds"},D="(years?|months?|weeks?|days?|hours?|minutes?|min|seconds?|sec|sunday|sun\\.?|monday|mon\\.?|tuesday|tue\\.?|wednesday|wed\\.?|thursday|thu\\.?|friday|fri\\.?|saturday|sat\\.?)",f="([+-]?\\d+\\s"+D+"|(last|next)\\s"+D+")(\\sago)?",s=e.match(new RegExp(f,"gi")),!s)return l;for(g=0,d=s.length;d>g;g++)if(!n(s[g]))return l;return w.getTime()/1e3}