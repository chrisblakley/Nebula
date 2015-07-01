jQuery.noConflict();

jQuery(document).ready(function(){

	//Assign common global variables
	pageWindow = jQuery(window);
	pageDocument = jQuery(document);
	pageHTML = jQuery('html');
	pageBody = jQuery('body');

	getQueryStrings();
	if ( GET('killall') || GET('kill') || GET('die') ) {
		throw ' (Manually terminated main.js)';
	} else if ( GET('layout') ) {
		[].forEach.call(jQuery("*"),function(a){a.style.outline="1px solid #"+(~~(Math.random()*(1<<24))).toString(16)});
	}

	facebookSDK();
	//facebookConnect(); //@TODO "Social" 1: Uncomment here to enable Facebook Connect.
	socialSharing();

	/* Choose whether to use mmenu or doubletaptogo for mobile device navigation */
	mmenus();
	//jQuery('#primarynav .menu-item-has-children').doubleTapToGo();

	conditionalJSLoading();
	gaEventTracking();
	helperFunctions();
	dropdownWidthController();
	overflowDetector();
	subnavExpanders();
	//nebulaFixeder();
	nebulaEqualize();
	powerFooterWidthDist();
	menuSearchReplacement();
	mobileSearchPlaceholder();
	autocompleteSearch();
	advancedSearchTriggers();
	searchValidator();
	searchTermHighlighter();
	singleResultDrawer();
	pageVisibility();
	cFormLocalStorage();


	//Detect if loaded in an iframe
	if ( window != window.parent ){
		pageHTML.addClass('in-iframe');
		if ( window.parent.location.toString().indexOf('wp-admin') == -1 ) {
			ga('send', 'event', 'Iframe', 'Loaded within: ' + window.parent.location, {'nonInteraction': 1});
		}
		jQuery('a').each(function(){
			if ( jQuery(this).attr('href') != '#' ) {
				jQuery(this).attr('target', '_parent');
			}
		});
	}

	if ( pageBody.hasClass('search-no-results') || pageBody.hasClass('error404') ) {
		pageSuggestion();
	}

	if ( cookieAuthorName ) {
		prefillCommentAuthorCookieFields(cookieAuthorName, cookieAuthorEmail);
	}

	vimeoControls();

	mapInfo = [];
	getAllLocations();
	mapActions();

	//Fix for <p> tags wrapping Nebula pre spans in the WYSIWYG
	jQuery('span.nebula-code').parent('p').css('margin-bottom', '0px');

	jQuery('.wpcf7-captchar').attr('title', 'Not case-sensitive');

	if ( !pageHTML.hasClass('lte-ie8') ) { //@TODO "Nebula" 0: This breaks in IE8. This conditional should only be a temporary fix.
		viewport = updateViewportDimensions();
	}

}); //End Document Ready


jQuery(window).on('load', function(){

	jQuery('a, li, tr').removeClass('hover');
	jQuery('html').addClass('loaded');

	//nebulaFixeder();
	checkCformLocalStorage();
	browserInfo();

	jQuery('#nebula-hero-search input').focus();

	setTimeout(function(){
		emphasizeSearchTerms();
	}, 1000);

}); //End Window Load


jQuery(window).on('resize', function(){
	debounce(function(){
    	powerFooterWidthDist();
		nebulaEqualize();
		mobileSearchPlaceholder();

    	//Track size change
    	if ( !pageHTML.hasClass('lte-ie8') ){ //@TODO "Nebula" 0: This breaks in IE8. This conditional should only be a temporary fix.
	    	viewportResized = updateViewportDimensions();
	    	if ( viewport.width > viewportResized.width ){
	    		ga('send', 'event', 'Window Resize', 'Smaller', viewport.width + 'px to ' + viewportResized.width + 'px');
	    	} else if ( viewport.width < viewportResized.width ){
	    		ga('send', 'event', 'Window Resize', 'Bigger', viewport.width + 'px to ' + viewportResized.width + 'px');
	    	}
	    	viewport = updateViewportDimensions();
    	}
	}, 500);
}); //End Window Resize


/*==========================

 Functions

 ===========================*/

//Custom css expression for a case-insensitive contains(). Call it with :Contains() - Ex: ...find("*:Contains(" + jQuery('.something').val() + ")")...
jQuery.expr[':'].Contains = function(a, i, m){
    return (a.textContent || a.innerText || "").toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
};

//Zebra-striper, First-child/Last-child, Hover helper functions, add "external" rel to outbound links
function helperFunctions(){
	jQuery('li:even, tr:even').not('.dataTables_wrapper tr').addClass('even');
	jQuery('li:odd, tr:odd').not('.dataTables_wrapper tr').addClass('odd');
	jQuery('ul:first-child, li:first-child, tr:first-child').addClass('first-child');
	jQuery('li:last-child, tr:last-child').addClass('last-child');
	jQuery('.column:first-child, .columns:first-child').addClass('first-child');
	jQuery('a:hover, li:hover, tr:hover').addClass('hover');
	jQuery('a').each(function(){
	    var a = new RegExp('/' + window.location.host + '/');
	    if ( !a.test(this.href) ){
	        if ( this.href.indexOf('http') !== -1 ){ //excludes all non-http link (ex: mailto: and tel:)
	            var rel = ( typeof jQuery(this).attr('rel') !== 'undefined' ? jQuery(this).attr('rel') + ' ' : '' );
	            jQuery(this).attr('rel', rel + 'external');
	        }
	    }
	});
} //end helperFunctions


//Load the SDK asynchronously
function facebookSDK(){
	(function(d, s, id){
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) return;
		js = d.createElement(s); js.id = id;
		js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&version=v2.3";
		fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));
}

//Facebook Connect functions
function facebookConnect(){
	if ( social['facebook_app_id'] ) {
		window.fbAsyncInit = function(){
			FB.init({
				appId: social['facebook_app_id'],
				channelUrl: bloginfo['template_directory'] + '/includes/channel.php',
				status: true,
				xfbml: true
			});

			window.FBuser = '';
			window.FBstatus = false;
			checkFacebookStatus();

			//Facebook Likes
			FB.Event.subscribe('edge.create', function(href, widget){
				var currentPage = pageDocument.attr('title');
				ga('send', {
					'hitType': 'social',
					'socialNetwork': 'Facebook',
					'socialAction': 'Like',
					'socialTarget': href,
					'page': currentPage
				});
				ga('send', 'event', 'Social', 'Facebook Like', {
					'dimension1': 'Like'
				});
			});

			//Facebook Unlikes
			FB.Event.subscribe('edge.remove', function(href, widget){
				var currentPage = pageDocument.attr('title');
				ga('send', {
					'hitType': 'social',
					'socialNetwork': 'Facebook',
					'socialAction': 'Unlike',
					'socialTarget': href,
					'page': currentPage
				});
				ga('send', 'event', 'Social', 'Facebook Unlike', {
					'dimension1': 'Unlike'
				});
			});

			//Facebook Send/Share
			FB.Event.subscribe('message.send', function(href, widget){
				var currentPage = pageDocument.attr('title');
				ga('send', {
					'hitType': 'social',
					'socialNetwork': 'Facebook',
					'socialAction': 'Send',
					'socialTarget': href,
					'page': currentPage
				});
				ga('send', 'event', 'Social', 'Facebook Share', {
					'dimension1': 'Share'
				});
			});

			//Facebook Comments
			FB.Event.subscribe('comment.create', function(href, widget){
				var currentPage = pageDocument.attr('title');
				ga('send', {
					'hitType': 'social',
					'socialNetwork': 'Facebook',
					'socialAction': 'Comment',
					'socialTarget': href,
					'page': currentPage
				});
				ga('send', 'event', 'Social', 'Facebook Comment', {
					'dimension1': 'Comment'
				});
			});
		};

		jQuery(document).on('click touch tap', '.facebook-connect', function(){
			facebookLoginLogout();
			return false;
		});
	} else {
		jQuery('.facebook-connect').remove();
	}
}

//Connect to Facebook without using Facebook Login button
function facebookLoginLogout(){
	if ( !FBstatus ) {
		FB.login(function(response){
			if (response.authResponse){
				checkFacebookStatus();
				ga('send', 'event', 'Social', 'Facebook Connect', FBuser.name);
			} else {
				if ( typeof Gumby != 'undefined' ) { Gumby.log('User did not accept permissions.'); }
				checkFacebookStatus();
			}
		}, {scope:'public_profile,email'});
	} else {
		FB.logout(function(response){
			if ( typeof Gumby != 'undefined' ) { Gumby.log('User has logged out.'); }
			checkFacebookStatus();
			prefillFacebookFields();
		});
	}
	return false;
}

//Fetch Facebook user information
function checkFacebookStatus() {
	FB.getLoginStatus(function(response){
		var facebookConnectPStrong = jQuery('#facebook-connect p strong');
		var facebookConnectLink = jQuery('.facebook-connect-con a');
		if ( response.status === 'connected' ){ //User is logged into Facebook and is connected to this app.
			FBstatus = true;
			FB.api('/me', function(response){
				FBuser = response;
				if ( typeof Gumby != 'undefined' ){ Gumby.log(response.name + ' has connected with this app.'); }
				fbNameClass = response.name.replace(' ', '_');
				pageBody.removeClass('fb-disconnected').addClass('fb-connected fb-user-' + fbNameClass);
				prefillFacebookFields(response);
				facebookConnectLink.text('Logout').removeClass('disconnected').addClass('connected');

				facebookConnectPStrong.text('You have been connected to Facebook, ' + response.first_name + '.'); //@TODO "Example" 2: This is an example- remove this line.
				jQuery('.fbpicture').attr('src', 'https://graph.facebook.com/' + response.id + '/picture?width=100&height=100'); //@TODO "Example" 2: This is an example- remove this line.
			});

			facebookConnectPStrong.text('You have been connected to Facebook...'); //@TODO "Example" 2: This is an example- remove this line.
		} else if (response.status === 'not_authorized') { //User is logged into Facebook, but has not connected to this app.
			if ( typeof Gumby != 'undefined' ) { Gumby.log('User is logged into Facebook, but has not connected to this app.'); }
			pageBody.removeClass('fb-connected').addClass('fb-disconnected');
			FBstatus = false;
			facebookConnectLink.text('Connect with Facebook').removeClass('connected').addClass('disconnected');

			facebookConnectPStrong.text('Please connect to this site by logging in below:'); //@TODO "Example" 2: This is an example- remove this line.
		} else { //User is not logged into Facebook.
			if ( typeof Gumby != 'undefined' ){ Gumby.log('User is not logged into Facebook.'); }
			pageBody.removeClass('fb-connected fb-disconnected');
			FBstatus = false;
			facebookConnectLink.text('Connect with Facebook').removeClass('connected').addClass('disconnected');

			facebookConnectPStrong.text('You are not logged into Facebook. Log in below:'); //@TODO "Example" 2: This is an example- remove this line.
		}
	});
}

//Fill or clear form inputs with Facebook data
function prefillFacebookFields(response){
	if ( response ) {
		jQuery('.fb-form-name, .comment-form-author input, .cform7-name, input.name').each(function(){
			jQuery(this).val(response.first_name + ' ' + response.last_name).trigger('keyup');
		});
		jQuery('.fb-form-first-name, .cform7-first-name, input.first-name').each(function(){
			jQuery(this).val(response.first_name).trigger('keyup');
		});
		jQuery('.fb-form-last-name, .cform7-last-name, input.last-name').each(function(){
			jQuery(this).val(response.last_name).trigger('keyup');
		});
		jQuery('.fb-form-email, .comment-form-email input, .cform7-email, input[type="email"]').each(function(){
			jQuery(this).val(response.email).trigger('keyup');
		});
		browserInfo();
	} else {
		jQuery('.fb-form-name, .comment-form-author input, .cform7-name, .fb-form-email, .comment-form-email input, input[type="email"]').each(function(){
			jQuery(this).val('').trigger('keyup');
		});
	}
}

function prefillCommentAuthorCookieFields(name, email){
	jQuery('.fb-form-name, .comment-form-author input, .cform7-name, input.name').each(function(){
		jQuery(this).val(name).trigger('keyup');
	});
	jQuery('.fb-form-email, .comment-form-email input, .cform7-email, input[type="email"]').each(function(){
		jQuery(this).val(email).trigger('keyup');
	});
}


//Social sharing buttons
function socialSharing() {
    var loc = window.location;
    var title = pageDocument.attr('title');
    var encloc = encodeURI(loc);
    var enctitle = encodeURI(title);
    jQuery('.fbshare').attr('href', 'http://www.facebook.com/sharer.php?u=' + encloc + '&t=' + enctitle).attr('target', '_blank');
    jQuery('.twshare').attr('href', 'https://twitter.com/intent/tweet?text=' + enctitle + '&url=' + encloc).attr('target', '_blank');
    jQuery('.gshare').attr('href', 'https://plus.google.com/share?url=' + encloc).attr('target', '_blank');
    jQuery('.lishare').attr('href', 'http://www.linkedin.com/shareArticle?mini=true&url=' + encloc + '&title=' + enctitle).attr('target', '_blank');
    jQuery('.emshare').attr('href', 'mailto:?subject=' + title + '&body=' + loc).attr('target', '_blank');
} //end socialSharing()


//Create an object of the viewport dimensions
function updateViewportDimensions() {
	var w=window, d=document, e=d.documentElement, g=d.getElementsByTagName('body')[0];

	if ( typeof viewport === 'undefined' ) {
		var viewportHistory = 0;
		//console.log('creating viewport History: ' + viewportHistory);
	} else {
		var viewportHistory = viewport.history+1;
		viewport.prevWidth = viewport.width; //Not pushing to the object...
		viewport.prevHeight = viewport.height; //Not pushing to the object...
		//console.log('increasing viewport History: ' + viewportHistory); //Triggering twice on window resize...
	}

	var x = w.innerWidth || e.clientWidth || g.clientWidth;
	var y = w.innerHeight || e.clientHeight || g.clientHeight;

	if ( viewportHistory == 0 ) {
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


//Main dropdown nav dynamic width controller
function dropdownWidthController() {
	jQuery('#primarynav .sub-menu').each(function(){
		var bigWidth = 100;
			if ( jQuery(this).children().width() > bigWidth ){
				bigWidth = jQuery(this).children().width();
			}
		jQuery(this).css('width', bigWidth+15 + 'px');
	});
} //end dropdownWidthController()


//Sub-menu viewport overflow detector
function overflowDetector() {
    jQuery('#primarynav .menu > .menu-item').hover(function(){
    	var viewportWidth = pageWindow.width();
    	var submenuLeft = jQuery(this).offset().left;
    	var submenuRight = submenuLeft+jQuery(this).children('.sub-menu').width();
    	if (submenuRight > viewportWidth) {
			jQuery(this).children('.sub-menu').css('left', 'auto').css('right', '0');
    	} else {
			jQuery(this).children('.sub-menu').css('left', '0').css('right', 'auto');
    	}
    }, function(){
	    	jQuery(this).children('.sub-menu').css('left', '-9999px').css('right', 'auto');
    });
} //end overflowDetector()


//Vertical subnav expanders
function subnavExpanders(){
    jQuery('.xoxo .menu li.menu-item:has(ul)').append('<a class="toplevelvert_expander plus" href="#"><i class="fa fa-caret-left"></i></a>');
    jQuery('.toplevelvert_expander').parent().children('.sub-menu').hide();
    pageDocument.on('click touch tap', '.toplevelvert_expander', function(){
        jQuery(this).toggleClass('plus').parent().children('.sub-menu').slideToggle();
        return false;
    });
    //Automatically expand subnav to show current page
    jQuery('.current-menu-ancestor').children('.toplevelvert_expander').click();
    jQuery('.current-menu-item').children('.toplevelvert_expander').click();
} //end subnavExpanders()


//Affix the logo/navigation when scrolling passed it
//@TODO "Nebula" 0: Ugh I don't really like this... It's almost fine, but the fixeElement.outerHeight() is before it shrinks, so there is 1 pixel or so where it puts the topbar beneath the fixed nav. Then, if you reload the page after scrolling down it does nothing until you scroll then it kicks in and animates the shrink all at once. Feels clunky as hell.
function nebulaFixeder(){
	var fixedElement = jQuery('#logonavcon'); //@TODO "Header" 3: Verify this selector is correct to trigger the fixed header.
	var fullBodyWrapper = jQuery('#fullbodywrapper');
	if ( fixedElement.is('*') && pageWindow.width() > 767 ){
		fixedDistance = fixedElement.position().top;

		pageWindow.on('scroll resize', function(){
			if ( pageWindow.scrollTop() >= fixedDistance ){
				fixedElement.addClass('fixed');
				fullBodyWrapper.css('padding-top', fixedElement.outerHeight());
			} else {
				fixedElement.removeClass('fixed');
				fullBodyWrapper.css('padding-top', '0');
			}
		});
	} else {
		fixedElement.removeClass('fixed');
		fullBodyWrapper.css('padding-top', '0');
	}
}


//Google Analytics Universal Analytics Event Trackers
function gaEventTracking(){
	//Example Event Tracker (Category and Action are required. If including a Value, it should be a rational number and not a string. Value could be an object of parameters like {'nonInteraction': 1, 'dimension1': 'Something', 'metric1': 82} Use deferred selectors.)
	//pageDocument.on('mousedown', '.selector', function(e) {
	//  var intent = ( e.which >= 2 ) ? ' (Intent)' : '';
	//	ga('send', 'event', 'Category', 'Action', 'Label', Value, {'object_name_here': object_value_here}); //Object names include 'hitCallback', 'nonInteraction', and others
	//});

	//External links
	pageDocument.on('mousedown touch tap', "a[rel*='external']", function(e){
		var intent = ( e.which >= 2 ) ? ' (Intent)' : '';
		var linkText = jQuery(this).text();
		var destinationURL = jQuery(this).attr('href');
		ga('send', 'event', 'External Link', linkText + intent, destinationURL);
	});

	//PDF View/Download
	pageDocument.on('mousedown touch tap', "a[href$='.pdf']", function(e){
		var intent = ( e.which >= 2 ) ? ' (Intent)' : '';
		var linkText = jQuery(this).text();
		var fileName = jQuery(this).attr('href');
		fileName = fileName.substr(fileName.lastIndexOf("/")+1);
		if ( linkText == '' || linkText == 'Download') {
			ga('send', 'event', 'PDF View', 'File: ' + fileName + intent);
		} else {
			ga('send', 'event', 'PDF View', 'Text: ' + linkText + intent);
		}
	});

	//Contact Form Submissions
	//@TODO "Contact" 4: This event doesn't give the best information. It is advised to replace it by calling the cformSuccess() function on successful submission (In the Contact Form 7 Settings for each form).
	pageDocument.on('submit', '.wpcf7-form', function(){
		ga('send', 'event', 'Contact', 'Submit Attempt', 'The submit button was clicked.');
	});

	//Generic Interal Search Tracking
	pageDocument.on('submit', '.search', function(){
		var searchQuery = jQuery(this).find('input[name="s"]').val();
		ga('send', 'event', 'Internal Search', 'Submit', searchQuery);
	});

	//Mailto link tracking
	pageDocument.on('mousedown touch tap', 'a[href^="mailto"]', function(){
		var intent = ( e.which >= 2 ) ? ' (Intent)' : '';
		var emailAddress = jQuery(this).attr('href').replace('mailto:', '');
		ga('send', 'event', 'Mailto', 'Email: ' + emailAddress + intent);
	});

	//Telephone link tracking
	pageDocument.on('mousedown touch tap', 'a[href^="tel"]', function(){
		var intent = ( e.which >= 2 ) ? ' (Intent)' : '';
		var phoneNumber = jQuery(this).attr('href');
		phoneNumber = phoneNumber.replace('tel:+', '');
		ga('send', 'event', 'Click-to-Call', 'Phone Number: ' + phoneNumber + intent);
	});

	//SMS link tracking
	pageDocument.on('mousedown touch tap', 'a[href^="sms"]', function(){
		var intent = ( e.which >= 2 ) ? ' (Intent)' : '';
		var phoneNumber = jQuery(this).attr('href');
		phoneNumber = phoneNumber.replace('sms:+', '');
		ga('send', 'event', 'Click-to-Call', 'SMS to: ' + phoneNumber + intent);
	});

	//Non-Linked Image Clicks
	jQuery('img').on('click tap touch', function(){
		if ( !jQuery(this).parents('a').length ) {
			ga('send', 'event', 'Non-Linked Click Attempt', 'Image', jQuery(this).attr('src'));
		}
	});

	//Word copy tracking
	var copyCount = 0;
	var copyOver = 0;
	pageDocument.on('cut copy', function(){
		copyCount++;
		var words = [];
		var selection = window.getSelection() + '';
		words = selection.split(' ');
		wordsLength = words.length;

		//Track Email or Phone copies as contact intent.
		var emailPattern = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		var phonePattern = /^(?:(?:\+?1\s*(?:[.-]\s*)?)?(?:\(\s*([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9])\s*\)|([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9]))\s*(?:[.-]\s*)?)?([2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)?([0-9]{4})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?$/;
		emailPhone = jQuery.trim(words.join(' '));
		if ( emailPattern.test(emailPhone) ){
			ga('send', 'event', 'Contact', 'Copied email: ' + emailPhone + ' (Intent)');
		} else if ( phonePattern.test(emailPhone) ){
			ga('send', 'event', 'Click-to-Call', 'Copied phone: ' + emailPhone + ' (Intent)');
		}

		if ( copyCount < 13 ) {
			if (words.length > 8) {
				words = words.slice(0, 8).join(' ');
				ga('send', 'event', 'Copied Text', words + '... [' + wordsLength + ' words]');
			} else {
				if ( selection == '' || selection == ' ' ) {
					ga('send', 'event', 'Copied Text', '[0 words]');
				} else {
					ga('send', 'event', 'Copied Text', selection);
				}
			}
		} else {
			if ( copyOver == 0 ) {
				ga('send', 'event', 'Copied Text', '[Copy limit reached]');
			}
			copyOver = 1;
		}
	});

	//AJAX Errors
	pageDocument.ajaxError(function(e, request, settings){
		ga('send', 'event', 'Error', 'AJAX Error', e.result + ' on: ' + settings.url, {'nonInteraction': 1});
		ga('send', 'exception', e.result, true);
	});

	//Capture Print Intent
	printed = 0;
	var afterPrint = function(){
		if ( printed == 0 ) {
			printed = 1;
			ga('send', 'event', 'Print (Intent)');
		}
	};
	if ( window.matchMedia ) {
		var mediaQueryList = window.matchMedia('print');
		if ( mediaQueryList.addListener ){
			mediaQueryList.addListener(function(mql) {
				if ( !mql.matches ){
					afterPrint();
				}
			});
		}
	}
	window.onafterprint = afterPrint;


}




function googlePlusCallback(jsonParam){
	if ( jsonParam.state == 'on' ){
		ga('send', 'event', 'Social', 'Google+ Like');
	} else if ( jsonParam.state == 'off' ) {
		ga('send', 'event', 'Social', 'Google+ Unlike');
	} else {
		ga('send', 'event', 'Social', 'Google+ [JSON Unavailable]');
	}
}

function mmenus() {
	if ( 'mmenu' in jQuery ){
		var mobileNav = jQuery('#mobilenav');
		var mobileNavTriggerIcon = jQuery('a.mobilenavtrigger i');

		if ( mobileNav.is('*') ){
			mobileNav.mmenu({
			    //Options
			    "offCanvas": {
				    "zposition": "back", //"back" (default), "front", "next"
				    "position": "left" //"left" (default), "right", "top", "bottom"
			    },
			    "searchfield": { //This is for searching through the menu itself (NOT for site search, but Nebula enables site search capabilities for this input)
			    	"add": true,
			    	"search": true,
			    	"placeholder": 'Search',
			    	"noResults": "No navigation items found.",
			    	"showLinksOnly": false //"true" searches only <a> links, "false" includes spans in search results. //@TODO "Nebula" 0: The option "searchfield.showLinksOnly" is deprecated as of version 5.0, use "!searchfield.showTextItems" instead.
			    },
			    "counters": true, //Display count of sub-menus
			    "extensions": ["theme-light", "effect-slide-menu", "pageshadow"] //Theming, effects, and other extensions
			}, {
				//Configuration
				"classNames": {
					"selected": "current-menu-item"
				}
			});

			if ( mobileNav.length ){
				mobileNav.data('mmenu').bind('opening', function(){
					//When mmenu has started opening
					mobileNavTriggerIcon.removeClass('fa-bars').addClass('fa-times').parents('.mobilenavtrigger').addClass('active');
				}).bind('opened', function(){
					//After mmenu has finished opening
					history.replaceState(null, document.title, location);
					history.pushState(null, document.title, location);
				}).bind('closing', function(){
					//When mmenu has started closing
					mobileNavTriggerIcon.removeClass('fa-times').addClass('fa-bars').parents('.mobilenavtrigger').removeClass('active');
				}).bind('closed', function(){
					//After mmenu has finished closing
				});
			}

			var mmenuSearchInput = jQuery('.mm-search input');
			mmenuSearchInput.wrap('<form method="get" action="' + bloginfo['home_url'] + '"></form>').attr('name', 's');
			mmenuSearchInput.on('keyup', function(){
				if ( jQuery(this).val().length > 0 ) {
					jQuery('.clearsearch').removeClass('hidden');
				} else {
					jQuery('.clearsearch').addClass('hidden');
				}
			});
			jQuery('.mm-panel').append('<div class="clearsearch hidden"><strong class="doasitesearch">Press enter to search the site!</strong><br/><a href="#"><i class="fa fa-times-circle"></i>Reset Search</a></div>');
			pageDocument.on('click touch tap', '.clearsearch a', function(){
				mmenuSearchInput.val('').keyup();
				jQuery('.clearsearch').addClass('hidden');
				return false;
			});

			//Close mmenu on back button click
			if (window.history && window.history.pushState){
				window.addEventListener("popstate", function(e){
					if ( jQuery('html.mm-opened').is('*') ) {
						jQuery(".mm-menu").trigger("close.mm");
						e.stopPropagation();
					}
				}, false);
			}
		}
	}
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
	if ( topLevelFooterItems == 0 ){
		jQuery('.powerfootercon').addClass('hidden');
	} else {
		powerFooterTopLIs.css('width', footerItemWidth);
	}
}


//Column height equalizer
function nebulaEqualize(){
	jQuery('.row.equalize').each(function(){
		var oThis = jQuery(this);
		tallestColumn = 0;
		oThis.find('.columns').css('min-height', '0');
		oThis.find('.columns').each(function(){
			if ( !oThis.hasClass('no-equalize') ){
				columnHeight = oThis.height();
				if ( columnHeight > tallestColumn ){
					tallestColumn = columnHeight;
				}
			}
		});
		oThis.find('.columns').css('min-height', tallestColumn);
	});
}

//Menu Search Replacement
function menuSearchReplacement(){
	jQuery('li.nebula-search').html('<form class="wp-menu-nebula-search search nebula-search-iconable" method="get" action="' + bloginfo['home_url'] + '/"><input type="search" class="nebula-search input search" name="s" placeholder="Search" autocomplete="off" x-webkit-speech /></form>');
	jQuery('li.nebula-search input, input.nebula-search').on('focus', function(){
		jQuery(this).addClass('focus active');
	});
	jQuery('li.nebula-search input, input.nebula-search').on('blur', function(){
		if ( jQuery(this).val() == '' || jQuery(this).val().trim().length === 0 ) {
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
	pageDocument.on('blur', ".nebula-search-iconable input", function(){
		jQuery('.nebula-search-iconable').removeClass('searching').removeClass('autocompleted');
	});

	jQuery("#s, input.search").on('keypress paste', function(e){
		thisSearchInput = jQuery(this);
		if ( !thisSearchInput.hasClass('no-autocomplete') && !pageHTML.hasClass('lte-ie8') && thisSearchInput.val().trim().length ){
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
						url: bloginfo["admin_ajax"],
						data: {
							action: 'nebula_autocomplete_search',
							data: request,
						},
						success: function(data){
							if ( data ) {
								jQuery.each(data, function(index, value) {
									value.label = value.label.replace(/&#038;/g, "\&");
								});
								ga('send', 'event', 'Internal Search', 'Autocomplete Search', request.term);
							} else {
								ga('send', 'event', 'Internal Search', 'Autocomplete Search (No Results)', request.term);
							}
							response(data);
							thisSearchInput.parents('form').removeClass('searching').addClass('autocompleted');
						},
						error: function(MLHttpRequest, textStatus, errorThrown){
							ga('send', 'event', 'Internal Search', 'Autcomplete Error', request.term);
							thisSearchInput.parents('form').removeClass('searching');
						},
						timeout: 60000
					});
				},
				focus: function(event, ui){
					event.preventDefault(); //Prevent input value from changing.
				},
				select: function(event, ui){
					ga('send', 'event', 'Internal Search', 'Autocomplete Click', ui.item.label);
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
			    thisSimilarity = ( typeof item.similarity !== 'undefined' ) ? item.similarity.toFixed(1) + '% Match' : '';
			    thisLabel = ( item.label.length > 50 ) ? item.label.substring(0, 50) + '...' : item.label;
			    var listItem = jQuery("<li class='" + item.classes + "' title='" + thisSimilarity + "'></li>").data("item.autocomplete", item).append("<a> " + thisLabel.replace(/\\/g, '') + "</a>").appendTo(ul);
			    return listItem;
			};
			var thisFormIdentifier = thisSearchInput.parents('form').attr('id') || thisSearchInput.parents('form').attr('name') || thisSearchInput.parents('form').attr('class');
			thisSearchInput.autocomplete("widget").addClass("form-identifier-" + thisFormIdentifier);
	    }
	});
}

//Advanced Search
function advancedSearchTriggers(){
	jQuery('#s').keyup(function(e){
		if ( searchTriggerOnlyChars(e) ) {
			advancedSearchWaiting();
			debounce(function(){
				if ( jQuery('#s').val().trim().length >= 3 ){
					advancedSearch();
					ga('send', 'event', 'Internal Search', 'Advanced', '"' + jQuery('#s').val().trim() + '"');
				} else {
					//console.log('value is less than 3 characters');
				}
			}, 1000, 'advanced internal search');
		}
	});

	pageDocument.on('change', '.advanced-post-type', function(){
		advancedSearchWaiting();
		debounce(function(){
			if ( jQuery('#s').val().trim() != '' || jQuery('.advanced-catstags').val() != '' ){ //@TODO: Something is up here.
				advancedSearch();
			}
		}, 1000, 'advanced post-type');
	});

	pageDocument.on('change', '.advanced-catstags', function(){
		advancedSearchWaiting();
		debounce(function(){
			advancedSearch();
		}, 1000, 'advanced catstags');
	});

	pageDocument.on('change', '.advanced-author', function(){
		advancedSearchWaiting();
		debounce(function(){
			advancedSearch();
		}, 1000, 'advanced author');
	});
}

function advancedSearchWaiting(){
	//console.log('showing typing icon and waiting for the last event...');
	jQuery('#advanced-search-results').slideUp();
	//@TODO: Show typing icon
	jQuery('#advanced-search-indicator').removeClass().addClass('fa fa-keyboard-o').addClass('active');
	setTimeout(function(){
		jQuery('#advanced-search-indicator').removeClass('active');
	}, 1000);
}

function advancedSearch(){
	if ( 1==1 ) { //@TODO: If all fields are not empty
		var advancedSearchIndicator = jQuery('#advanced-search-indicator');
		var advancedSearchForm = jQuery('#advanced-search-form');
		advancedSearchIndicator.removeClass().addClass('fa fa-spin fa-spinner').addClass('active');
		advancedSearchForm.addClass('inactive');

		jQuery.ajax({
			type: "POST",
			url: bloginfo["admin_ajax"],
			data: {
				action: 'nebula_advanced_search',
				data: {
					'term': jQuery('#s').val(),
					'author': jQuery('.advanced-author').val(),
					'posttype': jQuery('.advanced-post-type').val(),
					'catstags': jQuery('.advanced-catstags').val(),
					'datefrom': jQuery('.advanced-date-from').val(),
					'dateto': jQuery('.advanced-date-to').val(),
				},
			},
			success: function(data){
				jQuery('#advanced-search-results').html(data).slideDown();
				//console.log('success!');
				advancedSearchIndicator.removeClass().addClass('fa fa-check-circle success').addClass('active');
				advancedSearchForm.removeClass('inactive');
				setTimeout(function(){
					advancedSearchIndicator.removeClass('active');
				}, 5000);
			},
			error: function(MLHttpRequest, textStatus, errorThrown){
				ga('send', 'event', 'Contact', 'Error', 'Advanced Search');
				//console.log('ajax error :(');
				advancedSearchIndicator.removeClass().addClass('fa fa-times-circle error').addClass('active');
				advancedSearchForm.removeClass('inactive');
				setTimeout(function(){
					advancedSearchIndicator.removeClass('active');
				}, 5000);
			},
			timeout: 60000
		});
	}
}

//Mobile search placeholder toggle
function mobileSearchPlaceholder(){
	if ( !pageHTML.hasClass('lte-ie8') ){
		var mobileHeaderSearchInput = jQuery('#mobileheadersearch input');
		viewport = updateViewportDimensions();
		if ( viewport.width <= 410 ) {
			mobileHeaderSearchInput.attr('placeholder', 'I\'m looking for...');
		} else {
			mobileHeaderSearchInput.attr('placeholder', 'What are you looking for?');
		}
	}
}


//Search Validator
function searchValidator(){
	if ( !pageHTML.hasClass('lte-ie8') ){
		jQuery('.lt-ie9 form.search .btn.submit').val('Search');
		jQuery('.input.search').each(function(){
			if ( jQuery(this).val() == '' || jQuery(this).val().trim().length === 0 ){
				jQuery(this).parent().children('.btn.submit').addClass('disallowed');
			} else {
				jQuery(this).parent().children('.btn.submit').removeClass('disallowed').val('Search');
				jQuery(this).parent().find('.input.search').removeClass('focusError');
			}
		});
		jQuery('.input.search').on('focus blur change keyup paste cut',function(e){
			thisPlaceholder = ( jQuery(this).attr('data-prev-placeholder') !== 'undefined' ) ? jQuery(this).attr('data-prev-placeholder') : 'Search';
			if ( jQuery(this).val() == '' || jQuery(this).val().trim().length === 0 ){
				jQuery(this).parent().children('.btn.submit').addClass('disallowed');
				jQuery(this).parent().find('.btn.submit').val('Go');
			} else {
				jQuery(this).parent().children('.btn.submit').removeClass('disallowed');
				jQuery(this).parent().find('.input.search').removeClass('focusError').prop('title', '').attr('placeholder', thisPlaceholder);
				jQuery(this).parent().find('.btn.submit').prop('title', '').removeClass('notallowed').val('Search');
			}
			if ( e.type == 'paste' ){
				jQuery(this).parent().children('.btn.submit').removeClass('disallowed');
				jQuery(this).parent().find('.input.search').prop('title', '').attr('placeholder', 'Search').removeClass('focusError');
				jQuery(this).parent().find('.btn.submit').prop('title', '').removeClass('notallowed').val('Search');
			}
		})
		jQuery('form.search').submit(function(){
			if ( jQuery(this).find('.input.search').val() == '' || jQuery(this).find('.input.search').val().trim().length === 0 ){
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
	if ( typeof theSearchTerm != 'undefined' ) {
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
	var theSearchTerm = document.URL.split('?s=')[1];
	if ( typeof theSearchTerm != 'undefined' ){
		var origBGColor = jQuery('.searchresultword').css('background-color');
		jQuery('.searchresultword').each(function(i){
	    	var stallFor = 150 * parseInt(i);
			jQuery(this).delay(stallFor).animate({
			    backgroundColor: 'rgba(255, 255, 0, 0.5)',
			    borderColor: 'rgba(255, 255, 0, 1)',
			}, 500, 'swing', function() {
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
	var theSearchTerm = document.URL.split('?rs=')[1];
	if ( typeof theSearchTerm != 'undefined' ) {
		theSearchTerm = theSearchTerm.replace(/\+/g, ' ').replace(/\%20/g, ' ').replace(/\%22/g, ''); //@TODO "Nebula" 0: Combine into a single regex replace.
		jQuery('#searchform input#s').val(theSearchTerm);
	}

	pageDocument.on('click touch tap', '.headerdrawer .close', function(){
		var permalink = jQuery(this).attr('href');
		history.replaceState(null, document.title, permalink);
		jQuery('.headerdrawercon').slideUp();
		return false;
	});
}

//Suggestions for 404 or no search results pages
function pageSuggestion(){
	if ( nebula_settings["nebula_cse_id"] != '' && nebula_settings["nebula_cse_api_key"] != '' ){
		if ( GET().length ) {
			var queryStrings = GET();
		} else {
			var queryStrings = [''];
		}
		var path = window.location.pathname;
		var phrase = decodeURIComponent(path.replace(/\/+/g, ' ').trim()) + ' ' + decodeURIComponent(queryStrings[0].replace(/\+/g, ' ').trim());
		trySearch(phrase);

		pageDocument.on('mousedown touch tap', 'a.suggestion', function(e){
			var intent = ( e.which >= 2 ) ? ' (Intent)' : '';
			var suggestedPage = jQuery(this).text();
			ga('send', 'event', 'Page Suggestion', 'Click', 'Suggested Page: ' + suggestedPage + intent);
		});
	}
}

function trySearch(phrase){
	var queryParams = {
		cx: nebula_settings["nebula_cse_id"],
		key: nebula_settings["nebula_cse_api_key"],
		num: 10,
		q: phrase,
		alt: 'JSON'
	}
	var API_URL = 'https://www.googleapis.com/customsearch/v1?';

	// Send the request to the custom search API
	jQuery.getJSON(API_URL, queryParams, function(response){
		if (response.items && response.items.length) {
			ga('send', 'event', 'Page Suggestion', 'Suggested Page: ' + response.items[0].title, 'Requested URL: ' + window.location, {'nonInteraction': 1});
			showSuggestedPage(response.items[0].title, response.items[0].link);
		} else {
			ga('send', 'event', 'Page Suggestion', 'No Suggestions Found', 'Requested URL: ' + window.location, {'nonInteraction': 1});
		}
	});
}

function showSuggestedPage(title, url){
	var hostname = new RegExp(location.host);
	if ( hostname.test(url) ) {
		jQuery('.suggestion').attr('href', url).text(title);
		jQuery('#suggestedpage').slideDown();
	}
}

//Page Visibility
function pageVisibility(){
	visFirstHidden = 0;
	visibilityChangeActions();
	pageDocument.on('visibilitychange', function(){
		visibilityChangeActions();
	});

	function visibilityChangeActions(){
		if ( document.visibilityState == 'prerender' ) { //Page was prerendered
			var pageTitle = pageDocument.attr('title');
			ga('send', 'event', 'Page Visibility', 'Prerendered', pageTitle, {'nonInteraction': 1});
			//@TODO "Nebula" 0: pause youtube
			//@TODO "Nebula" 0: pause vimeo
		}

		if ( getPageVisibility() ) { //Page is hidden
			//@TODO "Nebula" 0: pause youtube
			//@TODO "Nebula" 0: pause vimeo
			visFirstHidden = 1;
			visTimerBefore = (new Date()).getTime();
			var pageTitle = pageDocument.attr('title');
			//ga('send', 'event', 'Page Visibility', 'Hidden', pageTitle, {'nonInteraction': 1}); //@TODO: Page Visibility Hidden event tracking is off by default. Uncomment to enable.
		} else { //Page is visible
			if ( visFirstHidden == 1 ) {
				var visTimerAfter = (new Date()).getTime();
				var visTimerResult = (visTimerAfter - visTimerBefore)/1000;
				var pageTitle = pageDocument.attr('title');
				//ga('send', 'event', 'Page Visibility', 'Visible', pageTitle + ' (Hidden for: ' + visTimerResult + 's)', {'nonInteraction': 1}); //@TODO "Nebula" 0: Page Visibility Visible event tracking is off by default. Uncomment to enable.
			}
		}
	}

	function getPageVisibility(){
		if ( typeof document.hidden != "undefined" ) {
			return document.hidden;
		} else {
			return false;
		}
	}
}

function cFormLocalStorage(){
	var cForm7Message = jQuery('.cform7-message');
	if ( cForm7Message.length == 1 ){
		cForm7Message.on('keyup', function(){
	    	localStorage.setItem('global_cform_message', cForm7Message.val());
			cForm7Message.val(localStorage.getItem('global_cform_message'));
	    });

	    pageWindow.bind('storage',function(e){
	    	cForm7Message.val(localStorage.getItem('global_cform_message'));
	    });

		jQuery('form.wpcf7-form').submit(function(){
			localStorage.removeItem('global_cform_message');
		});
	}
}

function checkCformLocalStorage(){
	var cForm7Message = jQuery('.cform7-message');
	if ( typeof localStorage.getItem('global_cform_message') !== 'undefined' && localStorage.getItem('global_cform_message') != 'undefined' ) {
		if ( cForm7Message.val() != '' ) {
			localStorage.setItem('global_cform_message', cForm7Message.val());
			cForm7Message.val(localStorage.getItem('global_cform_message'));
		} else {
			cForm7Message.val(localStorage.getItem('global_cform_message'));
		}
	} else {
		localStorage.removeItem('global_cform_message');
	}
}

//Contact form pre-validator
//@TODO "Nebula" 0: This should be optimized or (better yet) use a 3rd party library. Must validate in real-time.
function cFormPreValidator(){
	jQuery('.cform7-text').keyup(function(){
		if ( jQuery(this).val() == '' ) {
			jQuery(this).parent().parent().removeClass('danger').removeClass('success');
			jQuery(this).removeClass('wpcf7-not-valid');
		} else if ( jQuery(this).val().length && jQuery(this).val().trim().length === 0 ){
			jQuery(this).parent().parent().removeClass('success').addClass('danger');
		} else {
			jQuery(this).parent().parent().removeClass('danger').addClass('success');
			jQuery(this).removeClass('wpcf7-not-valid');
		}
	});
	jQuery('.cform7-name').keyup(function(){
		if ( jQuery(this).val() == '' ) {
			jQuery(this).parent().parent().removeClass('danger').removeClass('success');
			jQuery(this).removeClass('wpcf7-not-valid').attr('placeholder', 'Your Name*');
		} else if ( jQuery(this).val().length && jQuery(this).val().trim().length === 0 ){
			jQuery(this).parent().parent().removeClass('success').addClass('danger');
		} else {
			jQuery(this).parent().parent().removeClass('danger').addClass('success');
			jQuery(this).removeClass('wpcf7-not-valid');
		}
	});
	jQuery('.cform7-email').keyup(function(){
		if ( jQuery(this).val() == '' ) {
			jQuery(this).parent().parent().removeClass('danger').removeClass('success').removeClass('warning');
			jQuery(this).removeClass('wpcf7-not-valid');
			jQuery(this).attr('placeholder', 'Email Address*');
		} else if ( jQuery(this).val().trim().length === 0 || jQuery(this).val().indexOf(' ') > 0 ){
			jQuery(this).parent().parent().removeClass('success').removeClass('warning').addClass('danger');
		} else if ( jQuery(this).val().length && jQuery(this).val().indexOf('@') != 1 && jQuery(this).val().indexOf('.') < 0 ){
			jQuery(this).parent().parent().removeClass('success').removeClass('danger').addClass('warning');
			jQuery(this).removeClass('wpcf7-not-valid');
			jQuery(this).attr('placeholder', 'Email Address*');
		} else {
				jQuery(this).parent().parent().addClass('success');
				jQuery(this).parent().parent().removeClass('danger');
				jQuery(this).removeClass('wpcf7-not-valid');
				jQuery(this).parent().parent().removeClass('warning');
				jQuery(this).attr('placeholder', 'Email Address*');
		}
	});
	jQuery('.cform7-email').blur(function(){ //NOT WORKING YET - Want to remove spaces from the input on blur (the val doesnt have spaces, but the input does...?)
		var removeSpace = jQuery(this).val();
		//console.log('before trimming: ', removeSpace);
		removeSpace = removeSpace.replace(/ /g, '_');
		jQuery(this).val(removeSpace);
		//console.log('after trimming: ', removeSpace);

		if ( jQuery(this).val().length && jQuery(this).val().indexOf('@') != 1 && jQuery(this).val().indexOf('.') < 0 ){
			jQuery(this).parent().parent().removeClass('success').removeClass('warning').addClass('danger');
		}
	});

	if ( jQuery('.cform7-phone').is('*') || jQuery('.cform7-bday').is('*') ){
		jQuery('.cform7-phone').mask("(999) 999-9999? x99999");
		jQuery('.cform7-phone').keyup(function(){
			if ( jQuery(this).val().replace(/\D/g,'').length >= 10 ){
				jQuery(this).parent().parent().addClass('success');
			} else {
				jQuery(this).parent().parent().removeClass('success');
			}
		});
		jQuery.mask.definitions['m'] = "[0-1]";
		jQuery.mask.definitions['d'] = "[0-3]";
		jQuery.mask.definitions['y'] = "[1-2]";
		jQuery('.cform7-bday').mask("m9/d9/y999");
		currentYear = (new Date).getFullYear();
		jQuery('.cform7-bday').keyup(function(){
			if ( jQuery(this).val().replace(/\D/g,'').length === 8 ){
				jQuery(this).parent().parent().addClass('success');
			} else {
				jQuery(this).parent().parent().removeClass('success');
			}
			var checkMonth = jQuery(this).val().substr(0, 2);
			var checkDay = jQuery(this).val().substr(3, 2);
			var checkYear = jQuery(this).val().substr(jQuery(this).val().length - 4);
			if ( checkYear != '____' ) {
				if ( checkYear < 1900 || checkYear > currentYear){
					jQuery(this).parent().parent().removeClass('success').addClass('badyear');
				} else {
					jQuery(this).parent().parent().removeClass('badyear');
				}
			}
			if ( checkMonth != '__' ) {
				if ( checkMonth < 1 || checkMonth > 12){
					jQuery(this).parent().parent().removeClass('success').addClass('badmonth');
				} else {
					jQuery(this).parent().parent().removeClass('badmonth');
				}
			}
			if ( checkDay != '__' ) {
				if ( checkDay < 1 || checkDay > 31){
					jQuery(this).parent().parent().removeClass('success').addClass('badday');
				} else {
					jQuery(this).parent().parent().removeClass('badday');
				}
				//We could add specific checks for each individual month using checkMonth vs. checkDay.
			}
			if ( checkYear == '____' && checkMonth == '__' && checkDay == '__' ){
				jQuery(this).parent().parent().removeClass('success').removeClass('danger').removeClass('badyear').removeClass('badmonth').removeClass('badday');
			}
			if ( jQuery(this).parent().parent().hasClass('badmonth') ) {
				jQuery(this).parent().parent().removeClass('success').addClass('danger');
			} else if ( jQuery(this).parent().parent().hasClass('badday') ) {
				jQuery(this).parent().parent().removeClass('success').addClass('danger');
			} else if ( jQuery(this).parent().parent().hasClass('badyear') ) {
				jQuery(this).parent().parent().removeClass('success').addClass('danger');
			} else {
				jQuery(this).parent().parent().removeClass('danger');
			}
		});
	} //Close of if phone or bday input exists
	jQuery('.cform7-message').keyup(function(){
		if ( jQuery(this).val() == '' ) {
			jQuery(this).parent().parent().removeClass('danger');
			jQuery(this).parent().parent().removeClass('warning');
			jQuery(this).removeClass('wpcf7-not-valid');
			jQuery(this).attr('placeholder', 'Enter your message here.*');
		} else if ( jQuery(this).val().length && jQuery(this).val().trim().length === 0 ){
			jQuery(this).parent().parent().addClass('warning');
		} else {
			jQuery(this).parent().parent().removeClass('danger');
			jQuery(this).parent().parent().removeClass('warning');
			jQuery(this).removeClass('wpcf7-not-valid');
			jQuery(this).attr('placeholder', 'Enter your message here.*');
		}
	});
	jQuery('.cform7-message').blur(function(){
		if ( jQuery(this).val().length && jQuery(this).val().trim().length === 0 ){
			jQuery(this).parent().parent().removeClass('warning').addClass('danger');
		} else if ( jQuery(this).val() == '' ) {
			jQuery(this).parent().parent().removeClass('danger').removeClass('success').removeClass('warning');
		} else {
			jQuery(this).parent().parent().removeClass('danger').addClass('success');
		}
	});
	jQuery('.cform7-message').focus(function(){
		if ( jQuery(this).val().length && jQuery(this).val().trim().length === 0 ){
			jQuery(this).parent().parent().removeClass('danger').addClass('warning');
		} else {
			jQuery(this).parent().parent().removeClass('danger').removeClass('warning').removeClass('success');
		}
	});
	var reqFieldsEmpty = 0;
	jQuery('.wpcf7-validates-as-required').each(function(){
		if ( jQuery(this).val() == '' ){
			reqFieldsEmpty++;
		}
	});
	if ( reqFieldsEmpty > 0 ) {
		jQuery('#cform7-container').parent().find('.wpcf7-submit').addClass('disallowed');
	} else {
		jQuery('#cform7-container').parent().find('.wpcf7-submit').removeClass('disallowed');
	}
	jQuery('#cform7-container').keyup(function(){
		var obj = {};
		var dangers = 0;
		jQuery("#cform7-container li.danger").each(function(){
		var cl = jQuery(this).attr("class");
			if(!obj[cl]) {
				obj[cl] = {};
				dangers++;
				}
			});
		if ( dangers > 0 ) {
			jQuery(this).parent().find('.wpcf7-submit').addClass('disallowed').addClass('notallowed');
		} else {
			jQuery(this).parent().find('.wpcf7-submit').removeClass('disallowed').removeClass('notallowed');
		}
	});
	jQuery('.wpcf7-form').submit(function(){
		var intervalID = setInterval(function(){
			if ( jQuery('input').hasClass('wpcf7-not-valid') ){
				clearInterval(intervalID);
				jQuery('.wpcf7-not-valid').parent().parent().addClass('danger');
				jQuery('#cform7-container').parent().find('.wpcf7-submit').addClass('notallowed');
				if ( jQuery('.cform7-name.wpcf7-not-valid').val() == '' ){
					jQuery('.cform7-name').attr('placeholder', 'Your name is required.');
				}
				if ( jQuery('.cform7-email.wpcf7-not-valid').val() == '' ){
					jQuery('.cform7-email').attr('placeholder', 'Your email is required.');
				}
				if ( jQuery('.cform7-message.wpcf7-not-valid').val() == '' ){
					jQuery('.cform7-message').attr('placeholder', 'Your message is required.');
				}
			} else {
				jQuery('.wpcf7-not-valid').parent().parent().removeClass('danger');
			}
        }, 100);
	});
} //end cFormPreValidator()


//CForm7 submit success callback
//Add on_sent_ok: "cFormSuccess('Form Name Here');" to Additional Settings
//First parameter should be the name of the form to send to Google Analytics (Default: "(not set)").
//Second parameter should be either boolean (to use thanks.html) or string of another conversion page to use (Default: false).
//This can be customized and duplicated as needed.
function cFormSuccess(form, thanks){
	//Enter Additional on_sent_ok functionality here since it can only be used once per contact form.

	if ( form ){
		ga('send', 'event', 'Contact', 'Submit Success', form);
	} else {
		ga('send', 'event', 'Contact', 'Submit Success', '(not set)');
	}

    if ( thanks ){
    	conversionTracker(thanks); //Call conversion tracker if contact is a conversion goal.
	}
}

//Google AdWords conversion tracking for AJAX
function conversionTracker(conversionpage){
	if ( typeof conversionpage !== 'string' || conversionpage.indexOf('.') <= 0 ){
		conversionpage = 'thanks.html';
	}

	var iframe = document.createElement('iframe');
	iframe.style.width = '0px';
	iframe.style.height = '0px';
	document.body.appendChild(iframe);
	iframe.src = bloginfo['template_directory'] + '/includes/conversion/' + conversionpage;
};

//Allows only numerical input on specified inputs. Call this on keyUp? @TODO "Nebula" 0: Make the selector into oThis and pass that to the function from above.
//The nice thing about this is that it shows the number being taken away so it is more user-friendly than a validation option.
function onlyNumbers(){
	jQuery(".leftcolumn input[type='text']").each(function(){
		this.value = this.value.replace(/[^0-9\.]/g, '');
	});
}

function checkCommentVal(oThis){
	//@TODO "Nebula" 0: Count how many required fields there are. If any of them don't have value, then trigger disabled
	if ( jQuery(oThis).val() != '' ){
		jQuery(oThis).parents('form').find('input[type="submit"], button[type="submit"]').removeClass('disabled');
	} else {
		jQuery(oThis).parents('form').find('input[type="submit"], button[type="submit"]').addClass('disabled');
	}
}

function scrollTo() {
	pageDocument.on('click touch tap', 'a[href*=#]:not([href=#])', function(){
		if ( location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname ){
			var target = jQuery(this.hash);
			target = target.length ? target : jQuery('[name=' + this.hash.slice(1) +']');
			if ( target.length ){
				var headerHtOffset = jQuery('#topbarcon').height(); //Note: This selector should be the height of the fixed header, or a hard-coded offset.
				var nOffset = Math.floor(target.offset().top - headerHtOffset);
				jQuery('html, body').animate({
					scrollTop: nOffset
				}, 500);
				return false;
			}
		}
	});
}


//Fill browserinfo field with browser information (to send with forms).
function browserInfo(){
	var browserInfoVal = '';

	if ( typeof navigator != 'undefined' ){
		browserInfoVal += 'User Agent: ' + navigator.userAgent + '\n';
		browserInfoVal += 'UA Lookup: http://udger.com/resources/online-parser\n\n';
	}

	browserInfoVal += 'HTML Classes: ' + pageHTML.attr('class').split(' ').sort().join(', ') + '\n\n';
	browserInfoVal += 'Body Classes: ' + pageBody.attr('class').split(' ').sort().join(', ') + '\n\n';
	browserInfoVal += 'Viewport Size: ' + pageWindow.width() + 'px x ' + pageWindow.height() + 'px ' + '\n\n';

	if ( typeof performance != 'undefined' ){
		browserInfoVal += 'Redirects: ' + performance.navigation.redirectCount + '\n';
		var pageLoadTime = (performance.timing.loadEventStart-performance.timing.navigationStart)/1000;
		browserInfoVal += 'Page Loading Time: ' + pageLoadTime + 's' + '\n\n';
	}

	if ( typeof performance != 'undefined' ){
		browserInfoVal += 'Referrer: ' + document.referrer + '\n';
	} else {
		browserInfoVal += 'Referrer: None (or Unknown)\n';
	}

	if ( typeof window.history != 'undefined' ){
		browserInfoVal += 'History Depth: ' + window.history.length + '\n\n';
	}

	browserInfoVal += 'IP Address: ' + clientinfo['remote_addr'] + '\n';
	browserInfoVal += 'IP Lookup: http://whatismyipaddress.com/ip/' + clientinfo['remote_addr'];

	jQuery('textarea.browserinfo').addClass('hidden').css('display', 'none').val(browserInfoVal);
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
			icon: bloginfo['template_directory'] + "/images/meta/favicon-160x160.png" //Thumbnail Icon (optional)
		}

		if ( typeof message === "undefined" ){
			message = defaults;
			if ( typeof Gumby != 'undefined' ){ Gumby.warn('Warning: message is undefined, using defaults.'); }
		} else if ( typeof message === "string" ){
			body = message;
			message = defaults;
			message.body = body;
			if ( typeof Gumby != 'undefined' ){ Gumby.log('Note: message is a string, using defaults.'); }
		} else {
			if ( typeof message.dir === "undefined" ){
				message.dir = defaults.dir;
			}
			if ( typeof message.lang === "undefined" ){
				message.lang = defaults.lang;
			}
			if ( typeof message.body === "undefined" ){
				message.body = defaults.lang;
				if ( typeof Gumby != 'undefined' ) { Gumby.warn('Warning: No message body.'); }
			}
			if ( typeof message.tag === "undefined" ){
				message.tag = defaults.tag;
			}
			if ( typeof message.icon === "undefined" ){
				message.icon = defaults.icon;
			}
		}

		instance = new Notification(title, message); //Trigger the notification

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
		if ( typeof Gumby != 'undefined' ){ Gumby.warn("This browser does not support desktop notifications."); }
		return false;
	} else if ( Notification.permission === "granted" ){
		return true;
	} else if ( Notification.permission !== 'denied' ){
		Notification.requestPermission(function (permission){
			if( !('permission' in Notification) ){
				Notification.permission = permission;
			}
			if (permission === "granted"){
				return true;
			}
		});
	}
	return false;
}

function nebulaVibrate(pattern) {
	if ( typeof pattern === 'undefined' ){
		if ( typeof Gumby != 'undefined' ){ Gumby.warn('Vibration pattern was not provided. Using default.'); }
		pattern = [100, 200, 100, 100, 75, 25, 100, 200, 100, 500, 100, 200, 100, 500];
	} else if ( typeof pattern !== 'object' ){
		if ( typeof Gumby != 'undefined' ){ Gumby.warn('Vibration pattern is not an object. Using default.'); }
		pattern = [100, 200, 100, 100, 75, 25, 100, 200, 100, 500, 100, 200, 100, 500];
	}
	if ( checkVibration() ){
		navigator.vibrate(pattern);
	}
	return false;
}

function checkVibration() {
	if ( !jQuery('body').hasClass('mobile') ){
		if ( typeof Gumby != 'undefined' ){ Gumby.warn("This is not a mobile device, so vibration may not work (even if it declares support)."); }
	}

	Vibration = navigator.vibrate || navigator.webkitVibrate || navigator.mozVibrate || navigator.msVibrate;
	if ( !(Vibration) ){
		Gumby.warn("This browser does not support vibration.");
		return false;
	} else {
		return true;
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
        if ( !immediate ) {
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



//Conditional JS Library Loading
//This could be done better I think (also, it runs too late in the stack).
function conditionalJSLoading(){

	//Only load bxslider library on a page that calls bxslider.
	if ( jQuery('.bxslider').is('*') ) {
		jQuery.getScript(bloginfo['template_directory'] + '/js/libs/jquery.bxslider.min.js').done(function(){
			bxSlider();
		}).fail(function(){
			ga('send', 'event', 'Error', 'JS Error', 'bxSlider could not be loaded.', {'nonInteraction': 1});
		});
		nebulaLoadCSS(bloginfo['template_directory'] + '/css/jquery.bxslider.css');
	}

	//Only load maskedinput.js library if phone or bday field exists.
	if ( jQuery('.cform7-phone').is('*') || jQuery('.cform7-bday').is('*') ) {
		jQuery.getScript('//cdnjs.cloudflare.com/ajax/libs/jquery.maskedinput/1.3.1/jquery.maskedinput.min.js').done(function(){
			cFormPreValidator();
		}).fail(function(){
			ga('send', 'event', 'Error', 'JS Error', 'jquery.maskedinput.js could not be loaded.', {'nonInteraction': 1});
		});
	} else {
		cFormPreValidator();
	}

	//Only load Chosen library if 'chosen-select' class exists.
	if ( jQuery('.chosen-select').is('*') ) {
		jQuery.getScript('//cdnjs.cloudflare.com/ajax/libs/chosen/1.4.2/chosen.jquery.min.js').done(function(){
			chosenSelectOptions();
		}).fail(function(){
			ga('send', 'event', 'Error', 'JS Error', 'chosen.jquery.min.js could not be loaded.', {'nonInteraction': 1});
		});
		nebulaLoadCSS('//cdnjs.cloudflare.com/ajax/libs/chosen/1.4.2/chosen.min.css');
	}

	//Only load dataTables library if dataTables table exists.
    if ( jQuery('.dataTables_wrapper').is('*') ) {
        jQuery.getScript('//cdnjs.cloudflare.com/ajax/libs/datatables/1.10.7/js/jquery.dataTables.min.js').done(function(){
            jQuery.getScript('//cdn.datatables.net/responsive/1.0.6/js/dataTables.responsive.js').fail(function(){ //@TODO "Nebula" 0: Keep watching cdnjs for DataTables responsive support...
                ga('send', 'event', 'Error', 'JS Error', 'dataTables.responsive.js could not be loaded', {'nonInteraction': 1});
            });
            nebulaLoadCSS('//cdnjs.cloudflare.com/ajax/libs/datatables/1.10.7/css/jquery.dataTables.min.css');
			nebulaLoadCSS('//cdn.datatables.net/responsive/1.0.6/css/dataTables.responsive.css'); //@TODO "Nebula" 0: Keep watching cdnjs for DataTables responsive support...
			dataTablesActions();
        }).fail(function(){
            ga('send', 'event', 'Error', 'JS Error', 'jquery.dataTables.min.js could not be loaded', {'nonInteraction': 1});
        });

		//Only load Highlight if dataTables table exists.
        jQuery.getScript(bloginfo['template_directory'] + '/js/libs/jquery.highlight-5.closure.js').fail(function(){
            ga('send', 'event', 'Error', 'JS Error', 'jquery.highlight-5.closure.js could not be loaded.', {'nonInteraction': 1});
        });
    }

	if ( jQuery('pre.nebula-code').is('*') ){
		nebula_pre();
	}

	if ( jQuery('.flag').is('*') ){
		nebulaLoadCSS(bloginfo['template_directory'] + '/css/flags.css');
	}
} //end conditionalJSLoading()


//Dynamically load CSS files using JS
function nebulaLoadCSS(url){
	if ( document.createStyleSheet ){
	    try { document.createStyleSheet(url); } catch(e){
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

function chosenSelectOptions(){
	jQuery('.chosen-select').chosen({
		'disable_search_threshold': 5,
		'search_contains': true,
		'no_results_text': "No results found.",
		'allow_single_deselect': true,
		'width': "100%"
	});
}

function dataTablesActions(){
	pageDocument.on('keyup', '.dataTables_wrapper .dataTables_filter input', function(){ //@TODO "Nebula" 0: Something here is eating the first letter after a few have been typed... lol
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

function vimeoControls(){
	if ( jQuery('.vimeoplayer').is('*') ) {
        jQuery.getScript(bloginfo['template_directory'] + '/js/libs/froogaloop.min.js').done(function(){
			createVimeoPlayers();
		}).fail(function(){
			if ( typeof Gumby != 'undefined' ){ Gumby.warn('froogaloop.js could not be loaded.'); }
		});
	}

	function createVimeoPlayers(){
		//To trigger events on these videos, use the syntax: player[0].api("play");
		player = new Array();
	    jQuery('iframe.vimeoplayer').each(function(i){
			var vimeoiframeClass = jQuery(this).attr('id');
			player[i] = $f(vimeoiframeClass);
			//@TODO "Nebula" 0: Add a named index to this array so it can be called by the video ID instead of the array index number
			player[i].addEvent('ready', function(){
		    	if ( typeof Gumby != 'undefined' ){ Gumby.log('player is ready'); }
			    player[i].addEvent('play', onPlay);
			    player[i].addEvent('pause', onPause);
			    player[i].addEvent('seek', onSeek);
			    player[i].addEvent('finish', onFinish);
			    player[i].addEvent('playProgress', onPlayProgress);
			});
		});
	}

	function onPlay(id){
	    var videoTitle = id.replace(/-/g, ' ');
	    ga('send', 'event', 'Videos', 'Play', videoTitle);
	}

	function onPause(id){
	    var videoTitle = id.replace(/-/g, ' ');
	    ga('send', 'event', 'Videos', 'Pause', videoTitle);
	}

	function onSeek(data, id){
	    var videoTitle = id.replace(/-/g, ' ');
	    ga('send', 'event', 'Videos', 'Seek', videoTitle + ' [to: ' + data.seconds + ']');
	}

	function onFinish(id){
		var videoTitle = id.replace(/-/g, ' ');
		ga('send', 'event', 'Videos', 'Finished', videoTitle, {'nonInteraction': 1});
	}

	function onPlayProgress(data, id){
		//if ( typeof Gumby != 'undefined' ) { Gumby.log(data.seconds + 's played'); }
	}
}

//Cookie Management
function createCookie(name, value, days){
	if ( days ){
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires=" + date.toGMTString();
	} else {
		var expires = "";
	}
	document.cookie = name + "=" + value + expires + "; path=/";
	if ( typeof Gumby != 'undefined' ){
		Gumby.log('Created cookie: ' + name + ', with the value: ' + value + expires);
	}
}
function readCookie(name){
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for ( var i = 0; i < ca.length; i++ ){
		var c = ca[i];
		while (c.charAt(0) == ' ') {
			c = c.substring(1, c.length);
			if ( c.indexOf(nameEQ) == 0 ){
				if ( typeof Gumby != 'undefined' ) { Gumby.log('Cookie "' + name + '" exists.'); }
				return c.substring(nameEQ.length, c.length);
			}
		}
	}
	return null;
}
function eraseCookie(name){
	createCookie(name, "", -1);
	if ( typeof Gumby != 'undefined' ){
		Gumby.warn('Erased cookie: ' + name);
	}
}


//Convert Twitter usernames, hashtags, and URLs to links.
function tweetLinks(tweet){
	var newString = tweet.replace(/(http(\S)*)/g, '<a href="' + "$1" + '" target="_blank">' + "$1" + '</a>'); //Links that begin with "http"
	newString = newString.replace(/#(([a-zA-Z0-9_])*)/g, '<a href="https://twitter.com/hashtag/' + "$1" + '" target="_blank">#' + "$1" + '</a>'); //Link hashtags
	newString = newString.replace(/@(([a-zA-Z0-9_])*)/g, '<a href="https://twitter.com/' + "$1" + '" target="_blank">@' + "$1" + '</a>'); //Link @username mentions
	return newString;
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
	try {
		if ( document.queryCommandEnabled("SelectAll") ){ //@TODO "Nebula" 0: If using document.queryCommandSupported("copy") it always returns false (even though it does actually work when execCommand('copy') is called.
			var selectCopyText = 'Copy to clipboard';
		} else if ( document.body.createTextRange || window.getSelection ){
			var selectCopyText = 'Select All';
		} else {
			return false;
		}
	} catch(err){
		if ( document.body.createTextRange || window.getSelection ){
			var selectCopyText = 'Select All';
		} else {
			return false;
		}
	}

	jQuery('.nebula-pre-con').each(function(){
		jQuery(this).append('<a href="#" class="nebula-selectcopy-code">' + selectCopyText + '</a>');
	});

	pageDocument.on('click touch tap', '.nebula-selectcopy-code', function(){
	    oThis = jQuery(this);

	    if ( jQuery(this).text() == 'Copy to clipboard' ) {
		    selectText(jQuery(this).parents('.nebula-pre-con').find('pre'), 'copy', function(success){
			    if ( success ) {
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
			    if ( success ) {
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


/* ==========================================================================
   Google Maps API v3 Functions
   ========================================================================== */

//Interactive Functions of the Google Map
function mapActions(){
	originalWeatherText = jQuery('.mapweather').text();
	pageDocument.on('click touch tap', '.mapweather', function(){
		if ( mapInfo['weather'] == 1 ){
			mapInfo['weather'] = 0;
			jQuery('.mapweather').removeClass('active').addClass('inactive').text(originalWeatherText);
			jQuery('.mapweather-icon').removeClass('active').addClass('inactive');
			if ( typeof Gumby != 'undefined' ){ Gumby.log('Disabling weather layer.'); }
		} else {
			mapInfo['weather'] = 1;
			jQuery('.mapweather').addClass('active').removeClass('inactive').text('Disable Weather');
			jQuery('.mapweather-icon').addClass('active').removeClass('inactive');
			if ( typeof Gumby != 'undefined' ){ Gumby.log('Enabling weather layer.'); }
		}
		renderMap(mapInfo);
		return false;
	});

	originalTrafficText = jQuery('.maptraffic').text();
	pageDocument.on('click touch tap', '.maptraffic', function(){
		if ( mapInfo['traffic'] == 1 ){
			mapInfo['traffic'] = 0;
			jQuery('.maptraffic').removeClass('active').addClass('inactive').text(originalTrafficText);
			jQuery('.maptraffic-icon').removeClass('active').addClass('inactive');
			if ( typeof Gumby != 'undefined' ){ Gumby.log('Disabling traffic layer.'); }
		} else {
			mapInfo['traffic'] = 1;
			jQuery('.maptraffic').addClass('active').removeClass('inactive').text('Disable Traffic');
			jQuery('.maptraffic-icon').addClass('active').removeClass('inactive');
			if ( typeof Gumby != 'undefined' ){ Gumby.log('Enabling traffic layer.'); }
		}
		renderMap(mapInfo);
		return false;
	});

	pageDocument.on('click touch tap', '.mapgeolocation', function(){
		if ( typeof mapInfo['detectLoc'] === 'undefined' || mapInfo['detectLoc'][0] == 0 ){
			if ( typeof Gumby != 'undefined' ){ Gumby.log('Enabling location detection.'); }
			jQuery('.mapgeolocation-icon').removeClass('inactive fa-location-arrow').addClass('fa-spinner fa-spin');
			jQuery('.mapgeolocation').removeClass('inactive').attr('title', 'Requesting location...').text('Detecting Location...');
			requestPosition();
		} else {
			if ( typeof Gumby != 'undefined' ){ Gumby.log('Removing detected location.'); }
			jQuery('.mapgeolocation-icon').removeClass('fa-spinner fa-ban success error').addClass('inactive fa-location-arrow');
			jQuery(this).removeClass('active success failure').text('Detect Location').addClass('inactive').attr('title', 'Detect current location').css('color', '');
			mapInfo['detectLoc'] = new Array(0, 0);
			renderMap(mapInfo);
		}
		return false;
	});

	jQuery('.mapgeolocation').hover(function(){
		if ( jQuery(this).hasClass('active') ){
			jQuery('.mapgeolocation-icon').removeClass('fa-location-arrow').addClass('fa-ban');
		}
	}, function(){
		if ( jQuery(this).hasClass('active') ){
			jQuery('.mapgeolocation-icon').removeClass('fa-ban').addClass('fa-location-arrow');
		}
	});

	originalRefreshText = jQuery('.maprefresh').text();
	pleaseWait = 0;
	pageDocument.on('click touch tap', '.maprefresh', function(){
		if ( !jQuery(this).hasClass('timeout') ){
			pleaseWait = 0;
			if ( typeof Gumby != 'undefined' ){ Gumby.log('Refreshing the map.'); }
			renderMap(mapInfo);
			jQuery('.maprefresh').addClass('timeout', function(){
				jQuery('.maprefresh').text('Refreshing...');
				jQuery('.maprefresh-icon').removeClass('inactive').addClass('fa-spin');
			});
		} else {
			pleaseWait++;
			if ( pleaseWait < 10 ){
				jQuery('.maprefresh').text('Please wait...');
			} else {
				jQuery('.maprefresh').text('Hold your horses!');
			}
		}
		return false;
	});

	//Event Listeners

	//Refresh listener
	pageDocument.on('mapRendered', function(){
		setTimeout(function(){
			jQuery('.maprefresh').addClass('timeout').text('Refreshed!');
			jQuery('.maprefresh-icon').removeClass('fa-refresh fa-spin inactive').addClass('fa-check-circle success');
		}, 500);

		setTimeout(function(){ //Hide the refresh button to prevent spamming it
			jQuery('.maprefresh').removeClass('timeout').text(originalRefreshText);
			jQuery('.maprefresh-icon').removeClass('fa-check-circle success').addClass('fa-refresh inactive');
		}, 10000);
	});

	//Geolocation Success listener
	pageDocument.on('geolocationSuccess', function(){
		jQuery('.mapgeolocation').text('Location Accuracy: ').append('<span>' + mapInfo['detectLoc']['accMiles'] + ' miles <small>(' + mapInfo['detectLoc']['accMeters'].toFixed(2) + ' meters)</small></span>').find('span').css('color', mapInfo['detectLoc']['accColor']);
		setTimeout(function(){
			jQuery('.mapgeolocation').addClass('active').attr('title', '');
			jQuery('.mapgeolocation-icon').removeClass('fa-spinner fa-spin inactive').addClass('fa-location-arrow');
		}, 500);
	});

	//Geolocation Error listener
	pageDocument.on('geolocationError', function(){
		jQuery('.mapgeolocation').removeClass('success').text(geolocationErrorMessage);
		setTimeout(function(){
			jQuery('.mapgeolocation').attr('title', '');
			jQuery('.mapgeolocation-icon').removeClass('fa-spinner fa-spin').addClass('fa-location-arrow error');
		}, 500);
	});
} //End mapActions()

//Request Geolocation
function requestPosition(){
	if ( typeof Gumby != 'undefined' ){ Gumby.log('Requesting location... May need to be accepted.'); }
    var nav = null;
    if (nav == null){
        nav = window.navigator;
    }
    var geoloc = nav.geolocation;
    if (geoloc != null){
        geoloc.getCurrentPosition(successCallback, errorCallback, {enableHighAccuracy: true});
    }
}

//Geolocation Success
function successCallback(position){
	jQuery('.mapgeolocation').removeClass('failure').addClass('success');

	mapInfo['detectLoc'] = [];
	mapInfo['detectLoc'][0] = position.coords.latitude;
	mapInfo['detectLoc'][1] = position.coords.longitude;
	mapInfo['detectLoc']['accMeters'] = position.coords.accuracy;
	mapInfo['detectLoc']['alt'] = position.coords.altitude;
	mapInfo['detectLoc']['speed'] = position.coords.speed;

	if ( ( mapInfo['detectLoc']['accMeters'] <= 25 ) ){
		mapInfo['detectLoc']['accColor'] = '#00bb00';
	} else if ( mapInfo['detectLoc']['accMeters'] > 25 && mapInfo['detectLoc']['accMeters'] <= 50 ){
		mapInfo['detectLoc']['accColor'] = '#46d100';
	} else if ( mapInfo['detectLoc']['accMeters'] > 51 && mapInfo['detectLoc']['accMeters'] <= 150 ){
		mapInfo['detectLoc']['accColor'] = '#a4ed00';
	} else if ( mapInfo['detectLoc']['accMeters'] > 151 && mapInfo['detectLoc']['accMeters'] <= 400 ){
		mapInfo['detectLoc']['accColor'] = '#f2ee00';
	} else if ( mapInfo['detectLoc']['accMeters'] > 401 && mapInfo['detectLoc']['accMeters'] <= 800 ){
		mapInfo['detectLoc']['accColor'] = '#ffc600';
	} else if ( mapInfo['detectLoc']['accMeters'] > 801 && mapInfo['detectLoc']['accMeters'] <= 1500 ){
		mapInfo['detectLoc']['accColor'] = '#ff6f00';
	} else if ( mapInfo['detectLoc']['accMeters'] > 1501 && mapInfo['detectLoc']['accMeters'] <= 3000 ){
		mapInfo['detectLoc']['accColor'] = '#ff1900';
	} else if ( mapInfo['detectLoc']['accMeters'] > 3001 ){
		mapInfo['detectLoc']['accColor'] = '#ff0000';
	} else {
		mapInfo['detectLoc']['accColor'] = '#ff0000';
	}
	renderMap(mapInfo);

	mapInfo['detectLoc']['accMiles'] = (mapInfo['detectLoc']['accMeters']*0.000621371).toFixed(2);

	if ( mapInfo['detectLoc']['accMeters'] > 400 ){
		lowAccText = 'Your location accuracy is ' + mapInfo['detectLoc']['accMiles'] + ' miles (as shown by the colored radius).';
		if ( typeof Gumby != 'undefined' ){ Gumby.warn('Poor location accuracy: ' + mapInfo['detectLoc']['accMiles'] + ' miles (as shown by the colored radius).'); }
		//Some kind of notification here...
	}

	pageDocument.trigger('geolocationSuccess');
	//A value in decimal degrees to an precision of 4 decimal places is precise to 11.132 meters at the equator. A value in decimal degrees to 5 decimal places is precise to 1.1132 meter at the equator.

	pageBody.addClass('geo-latlng-' + mapInfo['detectLoc'][0] + '_' + mapInfo['detectLoc'][1] + ' geo-acc-' + mapInfo['detectLoc']['accMeters']);
	browserInfo();

	ga('send', 'event', 'Geolocation', mapInfo['detectLoc'][0].toFixed(4) + ', ' + mapInfo['detectLoc'][1].toFixed(4), 'Accuracy: ' + mapInfo['detectLoc']['accMiles'] + ' meters'); //@TODO "Nebula" 0: Add in actual location detection (from either gearside.com/ip, or Nebula's environment detection example and move this GA reporting to that (with business names in ga action). Maybe consider the Actions to be something like: "LAT, LNG (Business Name, City, State)"
}

//Geolocation Error
function errorCallback(error){
    geolocationErrorMessage = "";
    // Check for known errors
    switch (error.code) {
        case error.PERMISSION_DENIED:
            geolocationErrorMessage = 'Access to your location is turned off. Change your settings to report location data.';
            break;
        case error.POSITION_UNAVAILABLE:
            geolocationErrorMessage = "Data from location services is currently unavailable.";
            break;
        case error.TIMEOUT:
            geolocationErrorMessage = "Location could not be determined within a specified timeout period.";
            break;
        default:
        	geolocationErrorMessage = "An unknown error has occurred.";
            break;
    }
    if ( typeof Gumby != 'undefined' ){ Gumby.warn(geolocationErrorMessage); }
    pageDocument.trigger('geolocationError');
    pageBody.addClass('geo-error');
	browserInfo();
    ga('send', 'event', 'Geolocation', 'Error', geolocationErrorMessage, {'nonInteraction': 1});
}

//Retreive Lat/Lng locations
function getAllLocations(){
	mapInfo['markers'] = [];
	jQuery('.latlngcon').each(function(i){
		var alat = jQuery(this).find('.lat').text();
		var alng = jQuery(this).find('.lng').text();
		if ( typeof Gumby != 'undefined' ){ Gumby.log(i + ': found location! lat: ' + alat + ', lng: ' + alng); }
		mapInfo['markers'][i] = [alat, alng];
	});
	renderMap(mapInfo);
}

//Render the Google Map
function renderMap(mapInfo){
    if ( typeof Gumby != 'undefined' ){ Gumby.log('Rendering Google Map'); }

    if ( typeof google === 'undefined' ){
    	if ( typeof Gumby != 'undefined' ){ Gumby.log('google is not defined. Likely the Google Maps script is not being seen.'); }
    	return false;
    } else {
    	var myOptions = {
			zoom: 11,
			scrollwheel: false,
			zoomControl: true,
			scaleControl: true,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		}
	    map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
	    var bounds = new google.maps.LatLngBounds();

		if ( typeof mapInfo['traffic'] !== 'undefined' ){
			if ( mapInfo['traffic'] == 1 ) {
				if ( typeof Gumby != 'undefined' ){ Gumby.log('Traffic is enabled.'); }
				var trafficLayer = new google.maps.TrafficLayer();
				trafficLayer.setMap(map);
			}
		}

		//Map weather
		if ( typeof mapInfo['weather'] !== 'undefined' ){
			if ( mapInfo['weather'] == 1 ) {
				if ( typeof Gumby != 'undefined' ){ Gumby.log('Weather is enabled.'); }
				var weatherLayer = new google.maps.weather.WeatherLayer({
					temperatureUnits: google.maps.weather.TemperatureUnit.FAHRENHEIT
				});
				weatherLayer.setMap(map);

				var cloudLayer = new google.maps.weather.CloudLayer();
				cloudLayer.setMap(map);
			}
		}


	   	//Hard-Coded Custom Marker
		//http://mt.google.com/vt/icon?psize=27&font=fonts/Roboto-Bold.ttf&color=ff135C13&name=icons/spotlight/spotlight-waypoint-a.png&ax=43&ay=50&text=%E2%80%A2&scale=1
		var phg = new google.maps.LatLng('43.0536608', '-76.1656');
		bounds.extend(phg);
		marker = new google.maps.Marker({
	        position: phg,
	        icon: 'http://mt.google.com/vt/icon?psize=10&font=fonts/Roboto-Bold.ttf&color=ff135C13&name=icons/spotlight/spotlight-waypoint-a.png&ax=43&ay=50&text=PHG&scale=1',
	        clickable: false,
	        map: map
	    });


		//Dynamic Markers (passed from getAllLocations()
		if ( typeof mapInfo['markers'] !== 'undefined' ){
			var marker, i;
		    for (i = 0; i < mapInfo['markers'].length; i++){
		        var pos = new google.maps.LatLng(mapInfo['markers'][i][0], mapInfo['markers'][i][1]);
		        bounds.extend(pos);
		        marker = new google.maps.Marker({
		            position: pos,
		            //icon:'../../wp-content/themes/gearside2014/images/map-icon-marker.png', //@TODO "Nebula" 0: It would be cool if these were specific icons for each location. Pull from frontend w/ var?
		            clickable: false,
		            map: map
		        });
		        if ( typeof Gumby != 'undefined' ){ Gumby.log('Marker created for: ' + mapInfo['markers'][i][0] + ', ' + mapInfo['markers'][i][1]); }
		    }(marker, i);
	    }

		//Detected Location Marker
		if ( typeof mapInfo['detectLoc'] !== 'undefined' ){
			if ( mapInfo['detectLoc'][0] != 0 ) { //Detected location is set
				var detectLoc = new google.maps.LatLng(mapInfo['detectLoc'][0], mapInfo['detectLoc'][1]);
				marker = new google.maps.Marker({
			        position: detectLoc,
			        icon: 'http://mt.google.com/vt/icon?psize=10&font=fonts/Roboto-Bold.ttf&color=ff135C13&name=icons/spotlight/spotlight-waypoint-a.png&ax=43&ay=50&text=%E2%80%A2&scale=1',
			        //animation: google.maps.Animation.DROP,
			        clickable: false,
			        map: map
			    });
			    var circle = new google.maps.Circle({
					strokeColor: mapInfo['detectLoc']['accColor'],
					strokeOpacity: 0.7,
					strokeWeight: 1,
					fillColor: mapInfo['detectLoc']['accColor'],
					fillOpacity: 0.15,
					map: map,
					radius: mapInfo['detectLoc']['accMeters']
				});
				circle.bindTo('center', marker, 'position');
				if ( typeof Gumby != 'undefined' ){ Gumby.log('Marker created for detected location: ' + mapInfo['detectLoc'][0] + ', ' + mapInfo['detectLoc'][1]); }

				//var detectbounds = new google.maps.LatLngBounds();
				bounds.extend(detectLoc);
				//map.fitBounds(detectbounds); //Use this instead of the one below to center on detected location only (ignoring other markers)
			}
		}

		map.fitBounds(bounds);
		google.maps.event.trigger(map, "resize");

		pageDocument.trigger('mapRendered');
	}
}