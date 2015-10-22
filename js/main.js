jQuery.noConflict();
jQuery(document).on('ready', function(){

	getQueryStrings();
	if ( get('killall') || get('kill') || get('die') ){
		throw new Error('(Manually terminated inject.js)');
	} else if ( get('layout') ){
		[].forEach.call(jQuery("*"),function(a){a.style.outline="1px solid #"+(~~(Math.random()*(1<<24))).toString(16)});
	}

	//Assign common global variables
	thisPage = {
        'window': jQuery(window),
        'document': jQuery(document),
        'html': jQuery('html'),
        'body': jQuery('body')
    }

	//Social
	facebookSDK();
	facebookConnect();
	prefillFacebookFields();
	socialSharing();

	//Navigation
	mmenus();
	//jQuery('#primarynav .menu-item-has-children').doubleTapToGo(); //@TODO: Either use mmenu or uncomment this line for mobile navigation.
	dropdownWidthController();
	overflowDetector();
	//nebulaFixeder();
	menuSearchReplacement();
	subnavExpanders();

	//Search
	mobileSearchPlaceholder();
	autocompleteSearch();
	advancedSearchTriggers();
	searchValidator();
	searchTermHighlighter();
	singleResultDrawer();
	pageSuggestion();

	//Forms
	cFormLocalStorage();
	prefillCommentAuthorCookieFields(cookieAuthorName, cookieAuthorEmail);
	nebulaAddressAutocomplete('#address-autocomplete');

	//Helpers
	helperFunctions();
	powerFooterWidthDist();
	nebulaEqualize();
	nebulaScrollTo();

	//Interaction
	gaEventTracking();
	pageVisibility();
	vimeoControls();

	conditionalJSLoading();

	//Detect if loaded in an iframe
	if ( window != window.parent ){
		thisPage.html.addClass('in-iframe');
		if ( window.parent.location.toString().indexOf('wp-admin') == -1 ){
			ga('send', 'event', 'Iframe', 'Loaded within: ' + window.parent.location, {'nonInteraction': 1});
		}
		jQuery('a').each(function(){
			if ( jQuery(this).attr('href') != '#' ){
				jQuery(this).attr('target', '_parent');
			}
		});
	}

	jQuery('span.nebula-code').parent('p').css('margin-bottom', '0px'); //Fix for <p> tags wrapping Nebula pre spans in the WYSIWYG
	jQuery('.wpcf7-captchar').attr('title', 'Not case-sensitive');
	if ( !thisPage.html.hasClass('lte-ie8') ){ //@TODO "Nebula" 0: This breaks in IE8. This conditional should only be a temporary fix.
		viewport = updateViewportDimensions();
	}

}); //End Document Ready


jQuery(window).on('load', function(){
	//nebulaFixeder();
	checkCformLocalStorage();

	jQuery('#nebula-hero-search input').focus().on('mouseover', function(){
		if ( !jQuery('input:focus').is('*') ){
			jQuery(this).focus();
		}
	});

	jQuery('a, li, tr').removeClass('hover');
	jQuery('html').addClass('loaded');

	if ( typeof performance !== 'undefined' ){
		setTimeout(function(){
			var perceivedLoad = performance.timing.loadEventEnd-performance.timing.navigationStart;
			var actualLoad = performance.timing.loadEventEnd-performance.timing.responseEnd;
			jQuery('html').addClass('lt-per_' + perceivedLoad + 'ms');
			jQuery('html').addClass('lt-act_' + actualLoad + 'ms');
			browserInfo();
		}, 0);
	} else {
		jQuery('html').addClass('lt_unavailable');
		browserInfo();
	}

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
    	if ( !thisPage.html.hasClass('lte-ie8') ){ //@TODO "Nebula" 0: This breaks in IE8. This conditional should only be a temporary fix.
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

//Zebra-striper, First-child/Last-child, Hover helper functions, add "external" rel to outbound links
function helperFunctions(){
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
	if ( social['facebook_app_id'] ){
		window.fbAsyncInit = function(){
			FB.init({
				appId: social['facebook_app_id'],
				channelUrl: bloginfo['template_directory'] + '/includes/channel.php',
				status: true,
				xfbml: true
			});

			checkFacebookStatus();
			FB.Event.subscribe('edge.create', function(href, widget){ //Facebook Likes
				ga('send', {'hitType': 'social', 'socialNetwork': 'Facebook', 'socialAction': 'Like', 'socialTarget': href, 'page': thisPage.document.attr('title')});
				ga('send', 'event', 'Social', 'Facebook Like');
			});

			FB.Event.subscribe('edge.remove', function(href, widget){ //Facebook Unlikes
				ga('send', {'hitType': 'social', 'socialNetwork': 'Facebook', 'socialAction': 'Unlike', 'socialTarget': href, 'page': thisPage.document.attr('title')});
				ga('send', 'event', 'Social', 'Facebook Unlike');
			});

			FB.Event.subscribe('message.send', function(href, widget){ //Facebook Send/Share
				ga('send', {'hitType': 'social', 'socialNetwork': 'Facebook', 'socialAction': 'Send', 'socialTarget': href, 'page': thisPage.document.attr('title')});
				ga('send', 'event', 'Social', 'Facebook Share');
			});

			FB.Event.subscribe('comment.create', function(href, widget){ //Facebook Comments
				ga('send', {'hitType': 'social', 'socialNetwork': 'Facebook', 'socialAction': 'Comment', 'socialTarget': href, 'page': thisPage.document.attr('title')});
				ga('send', 'event', 'Social', 'Facebook Comment');
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
	if ( !nebulaFacebook.status ){
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
		nebulaFacebook = {'status': response.status}
		if ( nebulaFacebook.status == 'connected' ){ //User is logged into Facebook and is connected to this app.
			FB.api('/me', function(response){
				nebulaFacebook = {
					'id': response.id,
					'name': {
						'first': response.first_name,
						'last': response.last_name,
						'full': response.name,
					},
					'gender': response.gender,
					'email': response.email,
					'image': {
						'base': 'https://graph.facebook.com/' + response.id + '/picture',
						'thumbnail': 'https://graph.facebook.com/' + response.id + '/picture?width=100&height=100',
						'large': 'https://graph.facebook.com/' + response.id + '/picture?width=1000&height=1000',
					},
					'url': response.link,
					'location': {
						'locale': response.locale,
						'timezone': response.timezone,
					},
					'verified': response.verified,
				}
				ga('send', 'event', 'Social', 'Facebook Connect', nebulaFacebook.id);
				thisPage.body.removeClass('fb-disconnected').addClass('fb-connected fb-' + nebulaFacebook.id);
				thisPage.document.trigger('fbConnected');
			});
		} else if ( nebulaFacebook.status == 'not_authorized' ){ //User is logged into Facebook, but has not connected to this app.
			thisPage.body.removeClass('fb-connected').addClass('fb-not_authorized');
			thisPage.document.trigger('fbNotAuthorized');
		} else { //User is not logged into Facebook.
			thisPage.body.removeClass('fb-connected').addClass('fb-disconnected');
			thisPage.document.trigger('fbDisconnected');
		}
	});
}

//Fill or clear form inputs with Facebook data
function prefillFacebookFields(){
	jQuery(document).on('fbConnected', function(){
		jQuery('.fb-form-name, .comment-form-author input, .cform7-name, input.name').each(function(){
			jQuery(this).val(nebulaFacebook.name.full).trigger('keyup');
		});
		jQuery('.fb-form-first-name, .cform7-first-name, input.first-name').each(function(){
			jQuery(this).val(nebulaFacebook.name.first).trigger('keyup');
		});
		jQuery('.fb-form-last-name, .cform7-last-name, input.last-name').each(function(){
			jQuery(this).val(nebulaFacebook.name.last).trigger('keyup');
		});
		jQuery('.fb-form-email, .comment-form-email input, .cform7-email, input[type="email"]').each(function(){
			jQuery(this).val(nebulaFacebook.email).trigger('keyup');
		});
		browserInfo();
	});

	jQuery(document).on('fbNotAuthorized fbDisconnected', function(){
		jQuery('.fb-form-name, .comment-form-author input, .cform7-name, .fb-form-email, .comment-form-email input, input[type="email"]').each(function(){
			jQuery(this).val('').trigger('keyup');
		});
	});
}

function prefillCommentAuthorCookieFields(name, email){
	if ( cookieAuthorName ){
		jQuery('.fb-form-name, .comment-form-author input, .cform7-name, input.name').each(function(){
			jQuery(this).val(name).trigger('keyup');
		});
		jQuery('.fb-form-email, .comment-form-email input, .cform7-email, input[type="email"]').each(function(){
			jQuery(this).val(email).trigger('keyup');
		});
	}
}


//Social sharing buttons
function socialSharing(){
    var loc = window.location;
    var title = thisPage.document.attr('title');
    var encloc = encodeURI(loc);
    var enctitle = encodeURI(title);
    jQuery('.fbshare').attr('href', 'http://www.facebook.com/sharer.php?u=' + encloc + '&t=' + enctitle).attr('target', '_blank');
    jQuery('.twshare').attr('href', 'https://twitter.com/intent/tweet?text=' + enctitle + '&url=' + encloc).attr('target', '_blank');
    jQuery('.gshare').attr('href', 'https://plus.google.com/share?url=' + encloc).attr('target', '_blank');
    jQuery('.lishare').attr('href', 'http://www.linkedin.com/shareArticle?mini=true&url=' + encloc + '&title=' + enctitle).attr('target', '_blank');
    jQuery('.emshare').attr('href', 'mailto:?subject=' + title + '&body=' + loc).attr('target', '_blank');
} //end socialSharing()


//Create an object of the viewport dimensions
function updateViewportDimensions(){
	var w=window, d=document, e=d.documentElement, g=d.getElementsByTagName('body')[0];

	if ( typeof viewport === 'undefined' ){
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

	if ( viewportHistory == 0 ){
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
function dropdownWidthController(){
	jQuery('#primarynav .sub-menu').each(function(){
		var bigWidth = 100;
			if ( jQuery(this).children().width() > bigWidth ){
				bigWidth = jQuery(this).children().width();
			}
		jQuery(this).css('width', bigWidth+15 + 'px');
	});
} //end dropdownWidthController()


//Sub-menu viewport overflow detector
function overflowDetector(){
    jQuery('#primarynav .menu > .menu-item').hover(function(){
    	var viewportWidth = thisPage.window.width();
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
} //end overflowDetector()


//Vertical subnav expanders
function subnavExpanders(){
    jQuery('.xoxo .menu li.menu-item:has(ul)').append('<a class="toplevelvert_expander plus" href="#"><i class="fa fa-caret-left"></i></a>');
    jQuery('.toplevelvert_expander').parent().children('.sub-menu').hide();
    thisPage.document.on('click touch tap', '.toplevelvert_expander', function(){
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
	if ( fixedElement.is('*') && thisPage.window.width() > 767 ){
		fixedDistance = fixedElement.position().top;

		thisPage.window.on('scroll resize', function(){
			if ( thisPage.window.scrollTop() >= fixedDistance ){
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
	//thisPage.document.on('mousedown', '.selector', function(e){
	//  var intent = ( e.which >= 2 )? ' (Intent)' : '';
	//	ga('send', 'event', 'Category', 'Action', 'Label', Value, {'object_name_here': object_value_here}); //Object names include 'hitCallback', 'nonInteraction', and others
	//});

	//External links
	thisPage.document.on('mousedown touch tap', "a[rel*='external']", function(e){
		var intent = ( e.which >= 2 )? ' (Intent)' : '';

		var linkText = jQuery(this).text();
		if ( linkText.trim() == '' ){
			if ( jQuery(this).find('img').attr('alt') ){
				linkText = jQuery(this).find('img').attr('alt');
			} else if ( jQuery(this).find('img').is('*') ){
				linkText = jQuery(this).find('img').attr('src').substr(fileName.lastIndexOf("/")+1);
			} else if ( jQuery(this).find('img').attr('title') ){
				linkText = jQuery(this).find('img').attr('title');
			} else {
				linkText = '(unknown)';
			}
		}

		var destinationURL = jQuery(this).attr('href');
		ga('send', 'event', 'External Link', linkText + intent, destinationURL);
	});

	//PDF View/Download
	thisPage.document.on('mousedown touch tap', "a[href$='.pdf']", function(e){
		var intent = ( e.which >= 2 )? ' (Intent)' : '';
		var linkText = jQuery(this).text();
		var fileName = jQuery(this).attr('href').substr(fileName.lastIndexOf("/")+1);
		if ( linkText == '' || linkText == 'Download' ){
			ga('send', 'event', 'PDF View', 'File: ' + fileName + intent);
		} else {
			ga('send', 'event', 'PDF View', 'Text: ' + linkText + intent);
		}
		if ( typeof fbq == 'function' ){ fbq('track', 'ViewContent'); }
	});

	//Contact Form Submissions
	//@TODO "Contact" 4: This event doesn't give the best information. It is advised to replace it by calling the cformSuccess() function on successful submission (In the Contact Form 7 Settings for each form).
	thisPage.document.on('submit', '.wpcf7-form', function(){
		ga('send', 'event', 'Contact', 'Submit Attempt', 'The submit button was clicked.');
		if ( typeof fbq == 'function' ){ fbq('track', 'Lead'); }
	});

	//Generic Interal Search Tracking
	thisPage.document.on('submit', '.search', function(){
		var searchQuery = jQuery(this).find('input[name="s"]').val();
		ga('send', 'event', 'Internal Search', 'Submit', searchQuery);
		if ( typeof fbq == 'function' ){ fbq('track', 'Search'); }
	});

	//Mailto link tracking
	thisPage.document.on('mousedown touch tap', 'a[href^="mailto"]', function(e){
		var intent = ( e.which >= 2 )? ' (Intent)' : '';
		var emailAddress = jQuery(this).attr('href').replace('mailto:', '');
		ga('send', 'event', 'Mailto', 'Email: ' + emailAddress + intent);
		if ( typeof fbq == 'function' ){ fbq('track', 'Lead'); }
	});

	//Telephone link tracking
	thisPage.document.on('mousedown touch tap', 'a[href^="tel"]', function(e){
		var intent = ( e.which >= 2 )? ' (Intent)' : '';
		var phoneNumber = jQuery(this).attr('href');
		phoneNumber = phoneNumber.replace('tel:+', '');
		ga('send', 'event', 'Click-to-Call', 'Phone Number: ' + phoneNumber + intent);
		if ( typeof fbq == 'function' ){ fbq('track', 'Lead'); }
	});

	//SMS link tracking
	thisPage.document.on('mousedown touch tap', 'a[href^="sms"]', function(e){
		var intent = ( e.which >= 2 )? ' (Intent)' : '';
		var phoneNumber = jQuery(this).attr('href');
		phoneNumber = phoneNumber.replace('sms:+', '');
		ga('send', 'event', 'Click-to-Call', 'SMS to: ' + phoneNumber + intent);
		if ( typeof fbq == 'function' ){ fbq('track', 'Lead'); }
	});

	//Non-Linked Click Attempts
	jQuery('img').on('click tap touch', function(){
		if ( !jQuery(this).parents('a').length ){
			ga('send', 'event', 'Non-Linked Click Attempt', 'Image', jQuery(this).attr('src'));
		}
	});
	jQuery('.btn').on('click tap touch', function(e){
		if ( e.target != this ){
			return; //Only continue if the button is clicked, but not the <a> link.
		}
		if ( jQuery(this).find('a').is('*') ){
			ga('send', 'event', 'Non-Linked Click Attempt', 'Button', jQuery(this).find('a').text());
		} else {
			ga('send', 'event', 'Non-Linked Click Attempt', 'Button', '(no <a> tag) ' + jQuery(this).text());
		}
	});

	//Word copy tracking
	var copyCount = 0;
	var copyOver = 0;
	thisPage.document.on('cut copy', function(){
		copyCount++;
		var words = [];
		var selection = window.getSelection() + '';
		words = selection.split(' ');
		wordsLength = words.length;

		//Track Email or Phone copies as contact intent.
		var emailPattern = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/; //From JS Lint: Expected ']' and instead saw '['.
		var phonePattern = /^(?:(?:\+?1\s*(?:[.-]\s*)?)?(?:\(\s*([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9])\s*\)|([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9]))\s*(?:[.-]\s*)?)?([2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)?([0-9]{4})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?$/;
		emailPhone = jQuery.trim(words.join(' '));
		if ( emailPattern.test(emailPhone) ){
			ga('send', 'event', 'Contact', 'Copied email: ' + emailPhone + ' (Intent)');
		} else if ( phonePattern.test(emailPhone) ){
			ga('send', 'event', 'Click-to-Call', 'Copied phone: ' + emailPhone + ' (Intent)');
		}

		if ( copyCount < 13 ){
			if ( words.length > 8 ){
				words = words.slice(0, 8).join(' ');
				ga('send', 'event', 'Copied Text', words + '... [' + wordsLength + ' words]');
			} else {
				if ( selection == '' || selection == ' ' ){
					ga('send', 'event', 'Copied Text', '[0 words]');
				} else {
					ga('send', 'event', 'Copied Text', selection);
				}
			}
		} else {
			if ( copyOver == 0 ){
				ga('send', 'event', 'Copied Text', '[Copy limit reached]');
			}
			copyOver = 1;
		}
	});

	//AJAX Errors
	thisPage.document.ajaxError(function(e, request, settings){
		ga('send', 'event', 'Error', 'AJAX Error', e.result + ' on: ' + settings.url, {'nonInteraction': 1});
		ga('send', 'exception', e.result, true);
	});

	//Capture Print Intent
	printed = 0;
	var afterPrint = function(){
		if ( printed == 0 ){
			printed = 1;
			ga('send', 'event', 'Print (Intent)', 'Print');
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
}

function googlePlusCallback(jsonParam){
	if ( jsonParam.state == 'on' ){
		ga('send', 'event', 'Social', 'Google+ Like');
	} else if ( jsonParam.state == 'off' ){
		ga('send', 'event', 'Social', 'Google+ Unlike');
	} else {
		ga('send', 'event', 'Social', 'Google+ [JSON Unavailable]');
	}
}

function mmenus(){
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
			    "iconPanels": true, //Layer panels on top of each other
				"navbar": {
					"title": "Menu"
				},
				"navbars": [{
					"position": "bottom",
					"content": [
						"<span>" + bloginfo['name'] + "</span>"
					]
				}],
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
				if ( jQuery(this).val().length > 0 ){
					jQuery('.clearsearch').removeClass('hidden');
				} else {
					jQuery('.clearsearch').addClass('hidden');
				}
			});
			jQuery('.mm-panel').append('<div class="clearsearch hidden"><strong class="doasitesearch">Press enter to search the site!</strong><br /><a href="#"><i class="fa fa-times-circle"></i>Reset Search</a></div>');
			thisPage.document.on('click touch tap', '.clearsearch a', function(){
				mmenuSearchInput.val('').keyup();
				jQuery('.clearsearch').addClass('hidden');
				return false;
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


//Search Keywords
function keywordSearch(container, parent, value){
	jQuery(container).find("*:not(:Contains(" + value + "))").parents(parent).addClass('filtereditem');
	jQuery(container).find("*:Contains(" + value + ")").parents(parent).removeClass('filtereditem');
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
		oThis.find('.columns').each(function(i){
			if ( !jQuery(this).hasClass('no-equalize') ){
				columnHeight = jQuery(this).height();
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
		if ( jQuery(this).val() == '' || jQuery(this).val().trim().length === 0 ){
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
	thisPage.document.on('blur', ".nebula-search-iconable input", function(){
		jQuery('.nebula-search-iconable').removeClass('searching').removeClass('autocompleted');
	});

	jQuery("input#s, input.search").on('keypress paste', function(e){
		thisSearchInput = jQuery(this);
		if ( !thisSearchInput.hasClass('no-autocomplete') && !thisPage.html.hasClass('lte-ie8') && thisSearchInput.val().trim().length ){
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
						url: bloginfo["ajax_url"],
						data: {
							nonce: bloginfo["ajax_nonce"],
							action: 'nebula_autocomplete_search',
							data: request,
						},
						success: function(data){
							if ( data ){
								jQuery.each(data, function(index, value){
									value.label = value.label.replace(/&#038;/g, "\&");
								});
								ga('send', 'event', 'Internal Search', 'Autocomplete Search', request.term);
							} else {
								ga('send', 'event', 'Internal Search', 'Autocomplete Search (No Results)', request.term);
							}
							response(data);
							thisSearchInput.parents('form').removeClass('searching').addClass('autocompleted');
							if ( typeof fbq == 'function' ){ fbq('track', 'Search'); }
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
				ga('send', 'event', 'Internal Search', 'Advanced Search', jQuery('#s').val());
			}
		}, 1500);
	});

	thisPage.document.on('change', '#advanced-search-type, #advanced-search-catstags, #advanced-search-author, #advanced-search-date-start, #advanced-search-date-end', function(){
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
		//@TODO: Chosen.js fields need to be reset manually... or something?
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
	//@todo: there is a bug here... i think?
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
	if ( haveAllEvents == 0 ){
		if ( !waitingText ){
			waitingText = 'Waiting for filters...';
		}
		advancedSearchIndicator.html('<i class="fa fa-fw fa-keyboard-o"></i> ' + waitingText);
		debounce(function(){
			advancedSearchIndicator.html('<i class="fa fa-fw fa-spin fa-spinner"></i> Loading posts...');
			jQuery.ajax({
				type: "POST",
				url: bloginfo["ajax_url"],
				//@TODO "Nebula" 0: Add bloginfo["ajax_nonce"] here!
				data: {
					action: 'nebula_advanced_search',
				},
				success: function(response){
					haveAllEvents = 1;
					advancedSearch(startingAt, response);
				},
				error: function(MLHttpRequest, textStatus, errorThrown){
					jQuery('#advanced-search-results').text('Error: ' + MLHttpRequest + ', ' + textStatus + ', ' + errorThrown);
					haveAllEvents = 0;
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
					if ( thisCatTag[0] == 'category' ){
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

	//@TODO: This is not disappearing when reset link itself is clicked.
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
//End Advanced Search functions


//Mobile search placeholder toggle
function mobileSearchPlaceholder(){
	if ( !thisPage.html.hasClass('lte-ie8') ){
		var mobileHeaderSearchInput = jQuery('#mobileheadersearch input');
		viewport = updateViewportDimensions();
		if ( viewport.width <= 410 ){
			mobileHeaderSearchInput.attr('placeholder', 'I\'m looking for...');
		} else {
			mobileHeaderSearchInput.attr('placeholder', 'What are you looking for?');
		}
	}
}


//Search Validator
function searchValidator(){
	if ( !thisPage.html.hasClass('lte-ie8') ){
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
			thisPlaceholder = ( jQuery(this).attr('data-prev-placeholder') !== 'undefined' )? jQuery(this).attr('data-prev-placeholder') : 'Search';
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
	var theSearchTerm = document.URL.split('?s=')[1];
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
	var theSearchTerm = document.URL.split('?rs=')[1];
	if ( typeof theSearchTerm !== 'undefined' ){
		theSearchTerm = theSearchTerm.replace(/\+/g, ' ').replace(/\%20/g, ' ').replace(/\%22/g, ''); //@TODO "Nebula" 0: Combine into a single regex replace.
		jQuery('#searchform input#s').val(theSearchTerm);
	}

	thisPage.document.on('click touch tap', '.headerdrawer .close', function(){
		var permalink = jQuery(this).attr('href');
		history.replaceState(null, document.title, permalink);
		jQuery('.headerdrawercon').slideUp();
		return false;
	});
}

//Page Suggestions for 404 or no search results pages using Google Custom Search Engine
function pageSuggestion(){
	if ( thisPage.body.hasClass('search-no-results') || thisPage.body.hasClass('error404') ){
		if ( nebula_options["nebula_cse_id"] != '' && nebula_options["nebula_google_browser_api_key"] != '' ){
			if ( get().length ){
				var queryStrings = get();
			} else {
				var queryStrings = [''];
			}
			var path = window.location.pathname;
			var phrase = decodeURIComponent(path.replace(/\/+/g, ' ').trim()) + ' ' + decodeURIComponent(queryStrings[0].replace(/\+/g, ' ').trim());
			trySearch(phrase);

			thisPage.document.on('mousedown touch tap', 'a.suggestion', function(e){
				var intent = ( e.which >= 2 )? ' (Intent)' : '';
				var suggestedPage = jQuery(this).text();
				ga('send', 'event', 'Page Suggestion', 'Click', 'Suggested Page: ' + suggestedPage + intent);
			});
		}
	}
}

function trySearch(phrase){
	var queryParams = {
		cx: nebula_options["nebula_cse_id"],
		key: nebula_options["nebula_google_browser_api_key"],
		num: 10,
		q: phrase,
		alt: 'JSON'
	}
	var API_URL = 'https://www.googleapis.com/customsearch/v1?';

	// Send the request to the custom search API
	jQuery.getJSON(API_URL, queryParams, function(response){
		if ( response.items && response.items.length ){
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

//Page Visibility
function pageVisibility(){
	visFirstHidden = 0;
	visibilityChangeActions();
	thisPage.document.on('visibilitychange', function(){
		visibilityChangeActions();
	});

	function visibilityChangeActions(){
		if ( document.visibilityState == 'prerender' ){ //Page was prerendered
			var pageTitle = thisPage.document.attr('title');
			ga('send', 'event', 'Page Visibility', 'Prerendered', pageTitle, {'nonInteraction': 1});

			jQuery('iframe.youtubeplayer').each(function(){
				if ( !jQuery(this).hasClass('ignore-visibility') ){
					jQuery(this)[0].contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*'); //Pause Youtube Videos
				}
			});

			//@TODO "Nebula" 0: pause vimeo
		}

		if ( getPageVisibility() ){ //Page is hidden
			jQuery(document).trigger('nebula_page_hidden');
			jQuery('body').addClass('page-visibility-hidden');
			jQuery('iframe.youtubeplayer').each(function(){
				if ( !jQuery(this).hasClass('ignore-visibility') ){
					jQuery(this)[0].contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*'); //Pause Youtube Videos
				}
			});
			//@TODO "Nebula" 0: pause vimeo
			visFirstHidden = 1;
			visTimerBefore = (new Date()).getTime();
			var pageTitle = thisPage.document.attr('title');
			//ga('send', 'event', 'Page Visibility', 'Hidden', pageTitle, {'nonInteraction': 1}); //@TODO: Page Visibility Hidden event tracking is off by default. Uncomment to enable.
		} else { //Page is visible
			if ( visFirstHidden == 1 ){
				jQuery(document).trigger('nebula_page_visible');
				jQuery('body').removeClass('page-visibility-hidden');
				var visTimerAfter = (new Date()).getTime();
				var visTimerResult = (visTimerAfter - visTimerBefore)/1000;
				var pageTitle = thisPage.document.attr('title');
				//ga('send', 'event', 'Page Visibility', 'Visible', pageTitle + ' (Hidden for: ' + visTimerResult + 's)', {'nonInteraction': 1}); //@TODO "Nebula" 0: Page Visibility Visible event tracking is off by default. Uncomment to enable.
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

function cFormLocalStorage(){
	var cForm7Message = jQuery('.cform7-message');
	if ( cForm7Message.length == 1 ){
		cForm7Message.on('keyup', function(){
	    	localStorage.setItem('global_cform_message', cForm7Message.val());
			cForm7Message.val(localStorage.getItem('global_cform_message'));
	    });

	    thisPage.window.bind('storage',function(e){
	    	cForm7Message.val(localStorage.getItem('global_cform_message'));
	    });

		jQuery('form.wpcf7-form').submit(function(){
			localStorage.removeItem('global_cform_message');
		});
	}
}

function checkCformLocalStorage(){
	var cForm7Message = jQuery('.cform7-message');
	if ( typeof localStorage.getItem('global_cform_message') !== 'undefined' && typeof localStorage.getItem('global_cform_message') !== 'undefined' ){
		if ( cForm7Message.val() != '' ){
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
		if ( jQuery(this).val() == '' ){
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
		if ( jQuery(this).val() == '' ){
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
		if ( jQuery(this).val() == '' ){
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
			if ( checkYear != '____' ){
				if ( checkYear < 1900 || checkYear > currentYear){
					jQuery(this).parent().parent().removeClass('success').addClass('badyear');
				} else {
					jQuery(this).parent().parent().removeClass('badyear');
				}
			}
			if ( checkMonth != '__' ){
				if ( checkMonth < 1 || checkMonth > 12){
					jQuery(this).parent().parent().removeClass('success').addClass('badmonth');
				} else {
					jQuery(this).parent().parent().removeClass('badmonth');
				}
			}
			if ( checkDay != '__' ){
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
			if ( jQuery(this).parent().parent().hasClass('badmonth') ){
				jQuery(this).parent().parent().removeClass('success').addClass('danger');
			} else if ( jQuery(this).parent().parent().hasClass('badday') ){
				jQuery(this).parent().parent().removeClass('success').addClass('danger');
			} else if ( jQuery(this).parent().parent().hasClass('badyear') ){
				jQuery(this).parent().parent().removeClass('success').addClass('danger');
			} else {
				jQuery(this).parent().parent().removeClass('danger');
			}
		});
	} //Close of if phone or bday input exists
	jQuery('.cform7-message').keyup(function(){
		if ( jQuery(this).val() == '' ){
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
		} else if ( jQuery(this).val() == '' ){
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
	if ( reqFieldsEmpty > 0 ){
		jQuery('#cform7-container').parent().find('.wpcf7-submit').addClass('disallowed');
	} else {
		jQuery('#cform7-container').parent().find('.wpcf7-submit').removeClass('disallowed');
	}
	jQuery('#cform7-container').keyup(function(){
		var obj = {};
		var dangers = 0;
		jQuery("#cform7-container li.danger").each(function(){
		var cl = jQuery(this).attr("class");
			if ( !obj[cl] ){
				obj[cl] = {};
				dangers++;
				}
			});
		if ( dangers > 0 ){
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

function nebulaScrollTo(){
	var headerHtOffset = jQuery('#topbarcon').height(); //Note: This selector should be the height of the fixed header, or a hard-coded offset.
	thisPage.document.on('click touch tap', 'a[href^=#]:not([href=#])', function(){ //Using an ID as the href
		pOffset = ( jQuery(this).attr('offset') )? parseFloat(jQuery(this).attr('offset')) : 0;
		if ( location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname ){
			var target = jQuery(this.hash);
			target = ( target.length )? target : jQuery('[name=' + this.hash.slice(1) +']');
			if ( target.length ){
				var nOffset = Math.floor(target.offset().top-headerHtOffset+pOffset);
				jQuery('html, body').animate({
					scrollTop: nOffset
				}, 500);
				return false;
			}
		}
	});

	thisPage.document.on('click tap touch', '.nebula-scrollto', function(){ //Using the nebula-scrollto class with scrollto attribute.
		pOffset = ( jQuery(this).attr('offset') )? parseFloat(jQuery(this).attr('offset')) : 0;
		if ( jQuery(this).attr('scrollto') ){
			var scrollElement = jQuery(this).attr('scrollto');
			jQuery('html, body').animate({
				scrollTop: Math.floor(jQuery(scrollElement).offset().top-headerHtOffset+pOffset)
			}, 500);
		}
		return false;
	});
}


//Fill browserinfo field with browser information (to send with forms).
function browserInfo(){
	var browserInfoVal = '';

	if ( typeof navigator !== 'undefined' ){
		browserInfoVal += 'User Agent: ' + navigator.userAgent + '\n';
		browserInfoVal += 'http://udger.com/resources/online-parser\n\n';
	}

	if ( typeof clientinfo !== 'undefined' ){
		var fullDevice = ( clientinfo.device.full.trim().length )? ' (' + clientinfo.device.full + ')' : ''; //@TODO "Nebula" 0: Verify this conditional is good for IE8
		browserInfoVal += 'Device: ' + clientinfo.device.type + fullDevice + '\n';
		browserInfoVal += 'Operating System: ' + clientinfo.os.full + '\n';
		browserInfoVal += 'Browser: ' + clientinfo.browser.full + ' (' + clientinfo.browser.engine + ')\n\n';
	}

	browserInfoVal += 'HTML Classes: ' + thisPage.html.attr('class').split(' ').sort().join(', ') + '\n\n';
	browserInfoVal += 'Body Classes: ' + thisPage.body.attr('class').split(' ').sort().join(', ') + '\n\n';
	browserInfoVal += 'Viewport Size: ' + thisPage.window.width() + 'px x ' + thisPage.window.height() + 'px ' + '\n\n';

	if ( typeof performance !== 'undefined' ){
		browserInfoVal += 'Redirects: ' + performance.navigation.redirectCount + '\n';
		var perceivedLoadTime = (performance.timing.loadEventEnd-performance.timing.navigationStart)/1000;
		var actualLoadTime = (performance.timing.loadEventEnd-performance.timing.responseEnd)/1000;
		browserInfoVal += 'Perceived Load Time: ' + perceivedLoadTime + 's' + '\n';
		browserInfoVal += 'Actual Page Load Time: ' + actualLoadTime + 's' + '\n\n';
		browserInfoVal += 'Referrer: ' + document.referrer + '\n';
	} else {
		browserInfoVal += 'Page load time not available.\n\n';
		browserInfoVal += 'Referrer not available.\n';
	}

	if ( typeof window.history !== 'undefined' ){
		browserInfoVal += 'History Depth: ' + window.history.length + '\n\n';
	}

	if ( typeof nebulaLocation !== 'undefined' ){
		if ( !nebulaLocation.error ){
			browserInfoVal += 'Geolocation: ' + nebulaLocation.coordinates.latitude + ', ' + nebulaLocation.coordinates.longitude + '\n';
			browserInfoVal += 'Accuracy: ' + nebulaLocation.accuracy.meters + ' meters (' + nebulaLocation.accuracy.miles + ' miles)\n';
			browserInfoVal += 'https://www.google.com/maps/place/' + nebulaLocation.coordinates.latitude + ',' + nebulaLocation.coordinates.longitude + '\n\n';
		} else {
			browserInfoVal += 'Geolocation Error: ' + nebulaLocation.error.description + '\n\n';
		}
	}

	browserInfoVal += 'IP Address: ' + clientinfo['remote_addr'] + '\n';
	browserInfoVal += 'http://whatismyipaddress.com/ip/' + clientinfo['remote_addr'];

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
			if (permission === "granted"){
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



//Conditional JS Library Loading
//This could be done better I think (also, it runs too late in the stack).
function conditionalJSLoading(){

	//Only load bxslider library on a page that calls bxslider.
	if ( jQuery('.bxslider').is('*') ){
		jQuery.getScript('https://cdnjs.cloudflare.com/ajax/libs/bxslider/4.2.5/jquery.bxslider.min.js').done(function(){
			bxSlider();
		}).fail(function(){
			ga('send', 'event', 'Error', 'JS Error', 'bxSlider could not be loaded.', {'nonInteraction': 1});
		});
		nebulaLoadCSS('https://cdnjs.cloudflare.com/ajax/libs/bxslider/4.2.5/jquery.bxslider.min.css');
	}

	//Only load maskedinput.js library if phone or bday field exists.
	if ( jQuery('.cform7-phone').is('*') || jQuery('.cform7-bday').is('*') ){
		jQuery.getScript('https://cdnjs.cloudflare.com/ajax/libs/jquery.maskedinput/1.3.1/jquery.maskedinput.min.js').done(function(){
			cFormPreValidator();
		}).fail(function(){
			ga('send', 'event', 'Error', 'JS Error', 'jquery.maskedinput.js could not be loaded.', {'nonInteraction': 1});
		});
	} else {
		cFormPreValidator();
	}

	//Only load Chosen library if 'chosen-select' class exists.
	if ( jQuery('.chosen-select').is('*') ){
		jQuery.getScript('https://cdnjs.cloudflare.com/ajax/libs/chosen/1.4.2/chosen.jquery.min.js').done(function(){
			chosenSelectOptions();
		}).fail(function(){
			ga('send', 'event', 'Error', 'JS Error', 'chosen.jquery.min.js could not be loaded.', {'nonInteraction': 1});
		});
		nebulaLoadCSS('https://cdnjs.cloudflare.com/ajax/libs/chosen/1.4.2/chosen.min.css');
	}

	//Only load dataTables library if dataTables table exists.
    if ( jQuery('.dataTables_wrapper').is('*') ){
        jQuery.getScript('https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.7/js/jquery.dataTables.min.js').done(function(){
            jQuery.getScript('https://cdn.datatables.net/responsive/1.0.6/js/dataTables.responsive.js').fail(function(){ //@TODO "Nebula" 0: Keep watching cdnjs for DataTables responsive support...
                ga('send', 'event', 'Error', 'JS Error', 'dataTables.responsive.js could not be loaded', {'nonInteraction': 1});
            });
            nebulaLoadCSS('https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.7/css/jquery.dataTables.min.css');
			nebulaLoadCSS('https://cdn.datatables.net/responsive/1.0.6/css/dataTables.responsive.css'); //@TODO "Nebula" 0: Keep watching cdnjs for DataTables responsive support...
			dataTablesActions();
        }).fail(function(){
            ga('send', 'event', 'Error', 'JS Error', 'jquery.dataTables.min.js could not be loaded', {'nonInteraction': 1});
        });

		//Only load Highlight if dataTables table exists.
        jQuery.getScript(bloginfo['template_directory'] + '/js/libs/jquery.highlight-5.closure.js').fail(function(){
            ga('send', 'event', 'Error', 'JS Error', 'jquery.highlight-5.closure.js could not be loaded.', {'nonInteraction': 1});
        });
    }

	if ( jQuery('pre.nebula-code').is('*') || jQuery('pre.nebula-pre').is('*') ){
		nebulaLoadCSS(bloginfo['template_directory'] + '/stylesheets/css/pre.css');
		nebula_pre();
	}

	if ( jQuery('.flag').is('*') ){
		nebulaLoadCSS(bloginfo['template_directory'] + '/stylesheets/css/flags.css');
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
	thisPage.document.on('keyup', '.dataTables_wrapper .dataTables_filter input', function(){ //@TODO "Nebula" 0: Something here is eating the first letter after a few have been typed... lol
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
	if ( jQuery('.vimeoplayer').is('*') ){
        jQuery.getScript(bloginfo['template_directory'] + '/js/libs/froogaloop.min.js').done(function(){
			createVimeoPlayers();
		}).fail(function(){
			//do nothing
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
		//data.seconds played
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
}
function readCookie(name){
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for ( var i = 0; i < ca.length; i++ ){
		var c = ca[i];
		while ( c.charAt(0) == ' ' ){
			c = c.substring(1, c.length);
			if ( c.indexOf(nameEQ) == 0 ){
				return c.substring(nameEQ.length, c.length);
			}
		}
	}
	return null;
}
function eraseCookie(name){
	createCookie(name, "", -1);
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

	thisPage.document.on('click touch tap', '.nebula-selectcopy-code', function(){
	    oThis = jQuery(this);

	    if ( jQuery(this).text() == 'Copy to clipboard' ){
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


/* ==========================================================================
   Google Maps API v3 Functions
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

						//Come up with a way so that everything doesn't need to be defined before detecting if it exists. Like "pushing" to the object.
						addressComponents = {
							'street': {
								'number': null,
								'name': null,
								'full': null,
							},
							'city': null,
							'county': null,
							'state': {
								'name': null,
								'abbreviation': null,
							},
							'country': {
								'name': null,
								'abbreviation': null,
							},
							'zip': {
								'code': null,
								'suffix': null,
								'full': null,
							},
						};

						for ( var i = 0; i < place.address_components.length; i++ ){
							//Lots of different address types. This function uses only the common ones: https://developers.google.com/maps/documentation/geocoding/#Types
							switch ( place.address_components[i].types[0] ){
								case "street_number":
									addressComponents.street.number = place.address_components[i].short_name; //123
									break;
								case "route":
									addressComponents.street.name = place.address_components[i].long_name; //Street Name Rd.
									break;
								case "locality":
									addressComponents.city = place.address_components[i].long_name; //Liverpool
									break;
								case "administrative_area_level_2":
									addressComponents.county = place.address_components[i].long_name; //Onondaga County
									break;
								case "administrative_area_level_1":
									addressComponents.state.name = place.address_components[i].long_name; //New York
									addressComponents.state.abbreviation = place.address_components[i].short_name; //NY
									break;
								case "country":
									addressComponents.country.name = place.address_components[i].long_name; //United States
									addressComponents.country.abbreviation = place.address_components[i].short_name; //US
									break;
								case "postal_code":
									addressComponents.zip.code = place.address_components[i].short_name; //13088
									break;
								case "postal_code_suffix":
									addressComponents.zip.suffix = place.address_components[i].short_name; //4725
									break;
								default:
									//console.log('Address component ' + place.address_components[i].types[0] + ' not used.');
							}
						}
						if ( addressComponents.street.number && addressComponents.street.name ){
							addressComponents.street.full = addressComponents.street.number + ' ' + addressComponents.street.name;
						}
						if ( addressComponents.zip.code && addressComponents.zip.suffix ){
							addressComponents.zip.full = addressComponents.zip.code + '-' + addressComponents.zip.suffix;
						}

						jQuery(document).trigger('nebula_address_selected');
						ga('send', 'event', 'Contact', 'Autocomplete Address', addressComponents.city + ', ' + addressComponents.state.abbreviation + ' ' + addressComponents.zip.code);
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
						if ( e.which == 13 && jQuery('.pac-container:visible').is('*') ){ //Prevent form submission when enter key is pressed while the "Places Autocomplete" container is visbile
							return false;
						}
					});

					if ( autocompleteInput == '#address-autocomplete' ){
						jQuery(document).on('nebula_address_selected', function(){
							//do any default stuff here.
						});
					}
		    	} //End Google Maps callback
		    }); //End Google Maps load
		}).fail(function(){
			ga('send', 'event', 'Error', 'JS Error', 'Google Maps Places script could not be loaded.', {'nonInteraction': 1});
		});
	}
} //END nebulaAddressAutocomplete()


//Request Geolocation
function requestPosition(){
    var nav = null;
    if (nav == null){
        nav = window.navigator;
    }
    var geoloc = nav.geolocation;
    if (geoloc != null){
        geoloc.getCurrentPosition(successCallback, errorCallback, {enableHighAccuracy: true}); //One-time location poll
        //geoloc.watchPosition(successCallback, errorCallback, {enableHighAccuracy: true}); //Continuous location poll (This will update the nebulaLocation object regularly, but be careful sending events to GA- may result in TONS of events)
    }
}

//Geolocation Success
function successCallback(position){
	nebulaLocation = {
        'error': false,
        'coordinates': { //A value in decimal degrees to an precision of 4 decimal places is precise to 11.132 meters at the equator. A value in decimal degrees to 5 decimal places is precise to 1.1132 meter at the equator.
            'latitude': position.coords.latitude,
            'longitude': position.coords.longitude
        },
        'accuracy': {
            'meters': position.coords.accuracy,
            'miles': (position.coords.accuracy*0.000621371).toFixed(2),
        },
        'altitude': { //Above the mean sea level
	        'meters': position.coords.altitude,
	        'miles': (position.coords.altitude*0.000621371).toFixed(2),
	        'accuracy': position.coords.altitudeAccuracy,
        },
        'speed': {
	        'mps': position.coords.speed,
	        'kph': (position.coords.speed*3.6).toFixed(2),
	        'mph': (position.coords.speed*2.23694).toFixed(2),
        },
        'heading': position.coords.heading, //Degrees clockwise from North
    }

	if ( nebulaLocation.accuracy.meters <= 25 ){
		nebulaLocation.accuracy.color = '#00bb00';
	} else if ( nebulaLocation.accuracy.meters > 25 && nebulaLocation.accuracy.meters <= 50 ){
		nebulaLocation.accuracy.color = '#46d100';
	} else if ( nebulaLocation.accuracy.meters > 51 && nebulaLocation.accuracy.meters <= 150 ){
		nebulaLocation.accuracy.color = '#a4ed00';
	} else if ( nebulaLocation.accuracy.meters > 151 && nebulaLocation.accuracy.meters <= 400 ){
		nebulaLocation.accuracy.color = '#f2ee00';
	} else if ( nebulaLocation.accuracy.meters > 401 && nebulaLocation.accuracy.meters <= 800 ){
		nebulaLocation.accuracy.color = '#ffc600';
	} else if ( nebulaLocation.accuracy.meters > 801 && nebulaLocation.accuracy.meters <= 1500 ){
		nebulaLocation.accuracy.color = '#ff6f00';
	} else if ( nebulaLocation.accuracy.meters > 1501 && nebulaLocation.accuracy.meters <= 3000 ){
		nebulaLocation.accuracy.color = '#ff1900';
	} else {
		nebulaLocation.accuracy.color = '#ff0000';
	}

	thisPage.document.trigger('geolocationSuccess');
	thisPage.body.addClass('geo-latlng-' + nebulaLocation.coordinates.latitude.toFixed(4).replace('.', '_') + '_' + nebulaLocation.coordinates.longitude.toFixed(4).replace('.', '_') + ' geo-acc-' + nebulaLocation.accuracy.meters.toFixed(0).replace('.', ''));
	browserInfo();
	ga('send', 'event', 'Geolocation', nebulaLocation.coordinates.latitude.toFixed(4) + ', ' + nebulaLocation.coordinates.longitude.toFixed(4), 'Accuracy: ' + nebulaLocation.accuracy.meters.toFixed(2) + ' meters');
}

//Geolocation Error
function errorCallback(error){
    switch (error.code){
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

    nebulaLocation = {
	    'error': {
		    'code': error.code,
			'description': geolocationErrorMessage
	    }
    }

    thisPage.document.trigger('geolocationError');
    thisPage.body.addClass('geo-error');
	browserInfo();
    ga('send', 'event', 'Geolocation', 'Error', geolocationErrorMessage, {'nonInteraction': 1});
}


/*==========================
 Utilities
 ===========================*/

//Custom css expression for a case-insensitive contains(). Source: https://css-tricks.com/snippets/jquery/make-jquery-contains-case-insensitive/
//Call it with :Contains() - Ex: ...find("*:Contains(" + jQuery('.something').val() + ")")... -or- use the nebula function: keywordSearch(container, parent, value);
jQuery.expr[":"].Contains=function(e,n,t){return(e.textContent||e.innerText||"").toUpperCase().indexOf(t[3].toUpperCase())>=0};

//Parse dates (equivalent of PHP function). Source: https://github.com/kvz/phpjs/blob/1eaab15dc4e07c1bbded346e2cf187fbc8838562/functions/datetime/strtotime.js
function strtotime(e,t){function a(e,t,a){var n,r=c[t];"undefined"!=typeof r&&(n=r-w.getDay(),0===n?n=7*a:n>0&&"last"===e?n-=7:0>n&&"next"===e&&(n+=7),w.setDate(w.getDate()+n))}function n(e){var t=e.split(" "),n=t[0],r=t[1].substring(0,3),s=/\d+/.test(n),u="ago"===t[2],i=("last"===n?-1:1)*(u?-1:1);if(s&&(i*=parseInt(n,10)),o.hasOwnProperty(r)&&!t[1].match(/^mon(day|\.)?$/i))return w["set"+o[r]](w["get"+o[r]]()+i);if("wee"===r)return w.setDate(w.getDate()+7*i);if("next"===n||"last"===n)a(n,r,i);else if(!s)return!1;return!0}var r,s,u,i,w,c,o,d,D,f,g,l=!1;if(!e)return l;if(e=e.replace(/^\s+|\s+$/g,"").replace(/\s{2,}/g," ").replace(/[\t\r\n]/g,"").toLowerCase(),s=e.match(/^(\d{1,4})([\-\.\/\:])(\d{1,2})([\-\.\/\:])(\d{1,4})(?:\s(\d{1,2}):(\d{2})?:?(\d{2})?)?(?:\s([A-Z]+)?)?$/),s&&s[2]===s[4])if(s[1]>1901)switch(s[2]){case"-":return s[3]>12||s[5]>31?l:new Date(s[1],parseInt(s[3],10)-1,s[5],s[6]||0,s[7]||0,s[8]||0,s[9]||0)/1e3;case".":return l;case"/":return s[3]>12||s[5]>31?l:new Date(s[1],parseInt(s[3],10)-1,s[5],s[6]||0,s[7]||0,s[8]||0,s[9]||0)/1e3}else if(s[5]>1901)switch(s[2]){case"-":return s[3]>12||s[1]>31?l:new Date(s[5],parseInt(s[3],10)-1,s[1],s[6]||0,s[7]||0,s[8]||0,s[9]||0)/1e3;case".":return s[3]>12||s[1]>31?l:new Date(s[5],parseInt(s[3],10)-1,s[1],s[6]||0,s[7]||0,s[8]||0,s[9]||0)/1e3;case"/":return s[1]>12||s[3]>31?l:new Date(s[5],parseInt(s[1],10)-1,s[3],s[6]||0,s[7]||0,s[8]||0,s[9]||0)/1e3}else switch(s[2]){case"-":return s[3]>12||s[5]>31||s[1]<70&&s[1]>38?l:(i=s[1]>=0&&s[1]<=38?+s[1]+2e3:s[1],new Date(i,parseInt(s[3],10)-1,s[5],s[6]||0,s[7]||0,s[8]||0,s[9]||0)/1e3);case".":return s[5]>=70?s[3]>12||s[1]>31?l:new Date(s[5],parseInt(s[3],10)-1,s[1],s[6]||0,s[7]||0,s[8]||0,s[9]||0)/1e3:s[5]<60&&!s[6]?s[1]>23||s[3]>59?l:(u=new Date,new Date(u.getFullYear(),u.getMonth(),u.getDate(),s[1]||0,s[3]||0,s[5]||0,s[9]||0)/1e3):l;case"/":return s[1]>12||s[3]>31||s[5]<70&&s[5]>38?l:(i=s[5]>=0&&s[5]<=38?+s[5]+2e3:s[5],new Date(i,parseInt(s[1],10)-1,s[3],s[6]||0,s[7]||0,s[8]||0,s[9]||0)/1e3);case":":return s[1]>23||s[3]>59||s[5]>59?l:(u=new Date,new Date(u.getFullYear(),u.getMonth(),u.getDate(),s[1]||0,s[3]||0,s[5]||0)/1e3)}if("now"===e)return null===t||isNaN(t)?(new Date).getTime()/1e3|0:0|t;if(!isNaN(r=Date.parse(e)))return r/1e3|0;if(w=t?new Date(1e3*t):new Date,c={sun:0,mon:1,tue:2,wed:3,thu:4,fri:5,sat:6},o={yea:"FullYear",mon:"Month",day:"Date",hou:"Hours",min:"Minutes",sec:"Seconds"},D="(years?|months?|weeks?|days?|hours?|minutes?|min|seconds?|sec|sunday|sun\\.?|monday|mon\\.?|tuesday|tue\\.?|wednesday|wed\\.?|thursday|thu\\.?|friday|fri\\.?|saturday|sat\\.?)",f="([+-]?\\d+\\s"+D+"|(last|next)\\s"+D+")(\\sago)?",s=e.match(new RegExp(f,"gi")),!s)return l;for(g=0,d=s.length;d>g;g++)if(!n(s[g]))return l;return w.getTime()/1e3}