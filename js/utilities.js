jQuery.noConflict();

/*==========================
 DOM Ready
 ===========================*/

jQuery(document).ready(function(){

	getQueryStrings();
	if ( get('killall') || get('kill') || get('die') ){
		throw new Error('(Manually terminated utilities.js)');
	} else if ( get('layout') ){
		[].forEach.call(jQuery("*"),function(a){a.style.outline="1px solid #"+(~~(Math.random()*(1<<24))).toString(16)});
	}

	globalVariables();
	initSessionInfo();

	facebookSDK();
	facebookConnect();

	windowTypeDetection();
	conditionalJSLoading();


	if ( !nebula.dom.html.hasClass('lte-ie8') ){ //@TODO "Nebula" 0: This breaks in IE8. This conditional should only be a temporary fix.
		viewport = updateViewportDimensions();
	}

}); //End Document Ready


/*==========================
 Window Load
 ===========================*/

jQuery(window).on('load', function(){

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


}); //End Window Load


/*==========================
 Window Resize
 ===========================*/

jQuery(window).on('resize', function(){
	debounce(function(){

		nebulaEqualize();

		//Track size change
    	if ( !nebula.dom.html.hasClass('lte-ie8') ){ //@TODO "Nebula" 0: This breaks in IE8. This conditional should only be a temporary fix.
	    	viewportResized = updateViewportDimensions();
	    	if ( viewport.width > viewportResized.width ){
	    		ga('set', gaCustomDimensions['timestamp'], localTimestamp());
	    		ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Reduced Window Width'));
	    		ga('send', 'event', 'Window Resize', 'Smaller', viewport.width + 'px to ' + viewportResized.width + 'px');
	    	} else if ( viewport.width < viewportResized.width ){
	    		ga('set', gaCustomDimensions['timestamp'], localTimestamp());
	    		ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Enlarged Window Width'));
	    		ga('send', 'event', 'Window Resize', 'Bigger', viewport.width + 'px to ' + viewportResized.width + 'px');
	    	}
	    	viewport = updateViewportDimensions();
    	}

	}, 500);
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
		if ( document.referrer && document.referrer.indexOf(nebula.site.domain) < 0 ){ //If user navigated away and came back.
			nebula.session.history.push('---Returned from: ' + document.referrer.replace(/"|%22/g, ''));
		}

		if ( window.location.href != nebula.session.history[nebula.session.history.length-1] ){ //Disregard page refreshes
			nebula.session.history.push(window.location.href.replace(/"|%22/g, ''));
		}
	}
	createCookie('nebulaSession', JSON.stringify(nebula.session));
}

//Fill debugInfo field with browser information (to send with forms).
function debugInfo(){
	var debugInfoVal = '';

	formID = jQuery('div.wpcf7').attr('id');
	if ( typeof nebulaTimings !== 'undefined' && typeof nebulaTimings[formID] !== 'undefined' ){
		debugInfoVal += 'Field Timings:\n';
		debugInfoVal += 'http://jsonprettyprint.com/\n';
		debugInfoVal += JSON.stringify(nebulaTimings[formID], ['lap', 'name', 'duration', 'cumulative', 'total']);
		debugInfoVal += '\n\n';
	}

	if ( typeof navigator !== 'undefined' ){
		debugInfoVal += 'User Agent: ' + navigator.userAgent + '\n';
		debugInfoVal += 'http://udger.com/resources/online-parser\n\n';
	} else {
		debugInfoVal += 'User Agent: ' + nebula.user.client.userAgent + '\n';
		debugInfoVal += 'http://udger.com/resources/online-parser\n\n';
	}

	if ( typeof nebula.user.client !== 'undefined' ){
		var fullDevice = ( nebula.user.client.device.full.trim().length )? ' (' + nebula.user.client.device.full + ')' : ''; //@TODO "Nebula" 0: Verify this conditional is good for IE8
		debugInfoVal += 'Device: ' + nebula.user.client.device.type + fullDevice + '\n';
		debugInfoVal += 'Operating System: ' + nebula.user.client.os.full + '\n';
		debugInfoVal += 'Browser: ' + nebula.user.client.browser.full + ' (' + nebula.user.client.browser.engine + ')\n';
	}

	debugInfoVal += 'HTML Classes: ' + nebula.dom.html.attr('class').split(' ').sort().join(', ') + '\n\n';
	debugInfoVal += 'Body Classes: ' + nebula.dom.body.attr('class').split(' ').sort().join(', ') + '\n\n';
	debugInfoVal += 'Viewport Size: ' + nebula.dom.window.width() + 'px x ' + nebula.dom.window.height() + 'px ' + '\n\n';

	if ( 1===1 ){ //@TODO "Nebula" 0: Only need to run this group once per page.
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

		if ( typeof sessionStorage['sessionNotes'] !== 'undefined' && sessionStorage['sessionNotes'].length ){
			debugInfoVal += 'Session Notes: ' + sessionNote('return') + '\n';
		}

		if ( typeof adBlockUser !== 'undefined' ){
			debugInfoVal += 'Ads: ' + adBlockUser + '\n';
		}

		if ( typeof nebula.user.client.businessopen !== 'undefined' ){
			debugInfoVal += ( nebula.user.client.businessopen )? 'During Business Hours\n\n' : 'Non-Business Hours\n\n';
		}

		debugInfoOnceFlag = true;
	}

	if ( typeof nebula.user !== 'undefined' ){
		debugInfoVal += 'User: ';
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
				channelUrl: nebula.site.template_directory + '/includes/channel.php',
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

//Convert Twitter usernames, hashtags, and URLs to links.
function tweetLinks(tweet){
	var newString = tweet.replace(/(http(\S)*)/g, '<a href="' + "$1" + '" target="_blank">' + "$1" + '</a>'); //Links that begin with "http"
	newString = newString.replace(/#(([a-zA-Z0-9_])*)/g, '<a href="https://twitter.com/hashtag/' + "$1" + '" target="_blank">#' + "$1" + '</a>'); //Link hashtags
	newString = newString.replace(/@(([a-zA-Z0-9_])*)/g, '<a href="https://twitter.com/' + "$1" + '" target="_blank">@' + "$1" + '</a>'); //Link @username mentions
	return newString;
}


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
	}
}


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
        jQuery.getScript(nebula.site.template_directory + '/js/libs/jquery.highlight-5.closure.js').fail(function(){
            ga('set', gaCustomDimensions['timestamp'], localTimestamp());
            ga('set', gaCustomDimensions['sessionNotes'], sessionNote('JS Resource Load Error'));
            ga('send', 'event', 'Error', 'JS Error', 'jquery.highlight-5.closure.js could not be loaded.', {'nonInteraction': 1});
        });
    }

	if ( jQuery('pre.nebula-code').is('*') || jQuery('pre.nebula-pre').is('*') ){
		nebulaLoadCSS(nebula.site.template_directory + '/stylesheets/css/pre.css');
		nebula_pre();
	}

	if ( jQuery('.flag').is('*') ){
		nebulaLoadCSS(nebula.site.template_directory + '/stylesheets/css/flags.css');
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
    var nav = null;
    if (nav === null){
        nav = window.navigator;
    }
    var geoloc = nav.geolocation;
    if (geoloc != null){
        geoloc.getCurrentPosition(successCallback, errorCallback, {enableHighAccuracy: true}); //One-time location poll
        //geoloc.watchPosition(successCallback, errorCallback, {enableHighAccuracy: true}); //Continuous location poll (This will update the nebula.session.geolocation object regularly, but be careful sending events to GA- may result in TONS of events)
    }
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






//Functionality for selecting and copying text using Nebula Pre tags.
function nebula_pre(){
	if ( Modernizr.awesomeNewFeature ){
		//do something
	}

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
	if ( typeof pattern === 'undefined' ){
		pattern = [100, 200, 100, 100, 75, 25, 100, 200, 100, 500, 100, 200, 100, 500];
	} else if ( typeof pattern !== 'object' ){
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


/*==========================
 Extension Functions
 ===========================*/

//Custom css expression for a case-insensitive contains(). Source: https://css-tricks.com/snippets/jquery/make-jquery-contains-case-insensitive/
//Call it with :Contains() - Ex: ...find("*:Contains(" + jQuery('.something').val() + ")")... -or- use the nebula function: keywordSearch(container, parent, value);
jQuery.expr[":"].Contains=function(e,n,t){return(e.textContent||e.innerText||"").toUpperCase().indexOf(t[3].toUpperCase())>=0};

//Parse dates (equivalent of PHP function). Source: https://raw.githubusercontent.com/kvz/phpjs/master/functions/datetime/strtotime.js
function strtotime(e,t){function a(e,t,a){var n,r=c[t];"undefined"!=typeof r&&(n=r-w.getDay(),0===n?n=7*a:n>0&&"last"===e?n-=7:0>n&&"next"===e&&(n+=7),w.setDate(w.getDate()+n))}function n(e){var t=e.split(" "),n=t[0],r=t[1].substring(0,3),s=/\d+/.test(n),u="ago"===t[2],i=("last"===n?-1:1)*(u?-1:1);if(s&&(i*=parseInt(n,10)),o.hasOwnProperty(r)&&!t[1].match(/^mon(day|\.)?$/i))return w["set"+o[r]](w["get"+o[r]]()+i);if("wee"===r)return w.setDate(w.getDate()+7*i);if("next"===n||"last"===n)a(n,r,i);else if(!s)return!1;return!0}var r,s,u,i,w,c,o,d,D,f,g,l=!1;if(!e)return l;if(e=e.replace(/^\s+|\s+$/g,"").replace(/\s{2,}/g," ").replace(/[\t\r\n]/g,"").toLowerCase(),s=e.match(/^(\d{1,4})([\-\.\/\:])(\d{1,2})([\-\.\/\:])(\d{1,4})(?:\s(\d{1,2}):(\d{2})?:?(\d{2})?)?(?:\s([A-Z]+)?)?$/),s&&s[2]===s[4])if(s[1]>1901)switch(s[2]){case"-":return s[3]>12||s[5]>31?l:new Date(s[1],parseInt(s[3],10)-1,s[5],s[6]||0,s[7]||0,s[8]||0,s[9]||0)/1e3;case".":return l;case"/":return s[3]>12||s[5]>31?l:new Date(s[1],parseInt(s[3],10)-1,s[5],s[6]||0,s[7]||0,s[8]||0,s[9]||0)/1e3}else if(s[5]>1901)switch(s[2]){case"-":return s[3]>12||s[1]>31?l:new Date(s[5],parseInt(s[3],10)-1,s[1],s[6]||0,s[7]||0,s[8]||0,s[9]||0)/1e3;case".":return s[3]>12||s[1]>31?l:new Date(s[5],parseInt(s[3],10)-1,s[1],s[6]||0,s[7]||0,s[8]||0,s[9]||0)/1e3;case"/":return s[1]>12||s[3]>31?l:new Date(s[5],parseInt(s[1],10)-1,s[3],s[6]||0,s[7]||0,s[8]||0,s[9]||0)/1e3}else switch(s[2]){case"-":return s[3]>12||s[5]>31||s[1]<70&&s[1]>38?l:(i=s[1]>=0&&s[1]<=38?+s[1]+2e3:s[1],new Date(i,parseInt(s[3],10)-1,s[5],s[6]||0,s[7]||0,s[8]||0,s[9]||0)/1e3);case".":return s[5]>=70?s[3]>12||s[1]>31?l:new Date(s[5],parseInt(s[3],10)-1,s[1],s[6]||0,s[7]||0,s[8]||0,s[9]||0)/1e3:s[5]<60&&!s[6]?s[1]>23||s[3]>59?l:(u=new Date,new Date(u.getFullYear(),u.getMonth(),u.getDate(),s[1]||0,s[3]||0,s[5]||0,s[9]||0)/1e3):l;case"/":return s[1]>12||s[3]>31||s[5]<70&&s[5]>38?l:(i=s[5]>=0&&s[5]<=38?+s[5]+2e3:s[5],new Date(i,parseInt(s[1],10)-1,s[3],s[6]||0,s[7]||0,s[8]||0,s[9]||0)/1e3);case":":return s[1]>23||s[3]>59||s[5]>59?l:(u=new Date,new Date(u.getFullYear(),u.getMonth(),u.getDate(),s[1]||0,s[3]||0,s[5]||0)/1e3)}if("now"===e)return null===t||isNaN(t)?(new Date).getTime()/1e3|0:0|t;if(!isNaN(r=Date.parse(e)))return r/1e3|0;if(w=t?new Date(1e3*t):new Date,c={sun:0,mon:1,tue:2,wed:3,thu:4,fri:5,sat:6},o={yea:"FullYear",mon:"Month",day:"Date",hou:"Hours",min:"Minutes",sec:"Seconds"},D="(years?|months?|weeks?|days?|hours?|minutes?|min|seconds?|sec|sunday|sun\\.?|monday|mon\\.?|tuesday|tue\\.?|wednesday|wed\\.?|thursday|thu\\.?|friday|fri\\.?|saturday|sat\\.?)",f="([+-]?\\d+\\s"+D+"|(last|next)\\s"+D+")(\\sago)?",s=e.match(new RegExp(f,"gi")),!s)return l;for(g=0,d=s.length;d>g;g++)if(!n(s[g]))return l;return w.getTime()/1e3}