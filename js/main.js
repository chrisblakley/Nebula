jQuery.noConflict();

jQuery(document).ready(function() {

	getQueryStrings();
	if ( GET('killall') || GET('kill') || GET('die') ) {
		throw ' (Manually terminated main.js)';
	} else if ( GET('layout') ) {
		console.log('Visualizing layout...');
		[].forEach.call(jQuery("*"),function(a){a.style.outline="1px solid #"+(~~(Math.random()*(1<<24))).toString(16)});
	}

	facebookSDK();
	conditionalJSLoading();

	//Init Custom Functions
	gaCustomDimensions();
	gaEventTracking();

	helperFunctions();
	socialSharing();
	dropdownWidthController();
	overflowDetector();
	subnavExpanders();
	nebulaFixeder();

	/* Choose whether to use mmenu or doubletaptogo for mobile device navigation */
	mmenus();
	//jQuery('#primarynav .menu-item-has-children').doubleTapToGo();

	powerFooterWidthDist();
	menuSearchReplacement();
	searchValidator();
	searchTermHighlighter();
	singleResultDrawer();
	pageVisibility();
	errorLogAndFallback();
	WPcomments();
	contactBackup();


	//Detect if loaded in an iframe
	if ( window != window.parent ) {
		jQuery('html').addClass('in-iframe');
		nebula_event('Iframe', 'Requested page: ' + window.location, 'Loaded within: ' + window.parent.location, {'nonInteraction': 1});
	}

	if ( jQuery('body').hasClass('search-no-results') || jQuery('body').hasClass('error404') ) {
		pageSuggestion();
	}

	if ( cookieAuthorName ) {
		prefillCommentAuthorCookieFields(cookieAuthorName, cookieAuthorEmail);
	}

	vimeoControls();

	mapInfo = [];
	getAllLocations();
	mapActions();

	//Fix for <p> tags wrapping my pre spans in the WYSIWYG
	jQuery('span.nebula-code').parent('p').css('margin-bottom', '0px');

	//viewport = updateViewportDimensions(); //@TODO: This breaks in IE8
	//console.debug(viewport);
	jQuery(window).resize(function() {
		waitForFinalEvent(function(){

	    	//Window resize functions here.
	    	powerFooterWidthDist();

	    	//Track size change
	    	/* viewportResized = updateViewportDimensions();  //@TODO: This breaks in IE8
	    	if ( viewport.width > viewportResized.width ) {
	    		nebula_event('Window Resize', 'Smaller', viewport.width + 'px to ' + viewportResized.width + 'px');
	    	} else if ( viewport.width < viewportResized.width ) {
	    		nebula_event('Window Resize', 'Bigger', viewport.width + 'px to ' + viewportResized.width + 'px');
	    	}
	    	viewport = updateViewportDimensions();
	    	//console.debug(viewport); */

		}, 500, "unique resize ID 1");
	});


}); //End Document Ready




jQuery(window).on('load', function() {

	detectIconFonts();

	jQuery('a, li, tr').removeClass('hover');
	jQuery('html').addClass('loaded');
	jQuery('.unhideonload').removeClass('hidden');

	setTimeout(function(){
		emphasizeSearchTerms();
	}, 1000);

}); //End Window Load


/*==========================

 Functions

 ===========================*/

//Zebra-striper, First-child/Last-child, Hover helper functions, add "external" rel to outbound links
function helperFunctions(){
	jQuery('li:even, tr:even').addClass('even');
	jQuery('li:odd, tr:odd').addClass('odd');
	jQuery('ul:first-child, li:first-child, tr:first-child').addClass('first-child');
	jQuery('li:last-child, tr:last-child').addClass('last-child');
	jQuery('.column:first-child, .columns:first-child').addClass('first-child');
	jQuery('a:hover, li:hover, tr:hover').addClass('hover');
	jQuery('a').each(function(){
		var a = new RegExp('/' + window.location.host + '/');
		if( !a.test(this.href) ) {
			var rel = ( typeof jQuery(this).attr('rel') !== 'undefined' ? jQuery(this).attr('rel') + ' ' : '' );
			jQuery(this).attr('rel', rel + 'external');
		}
	});
	jQuery('.lte-ie9 .nebulashadow.inner-bottom, .lte-ie9 .nebulashadow.above').hide(); //@TODO: Anything we can do here to alleviate the issue? May need to just hide
} //end helperFunctions()


//Create Facebook functions
function facebookSDK() {
	window.fbAsyncInit = function() { //This is called once the Facebook SDK is initialized (from the footer)
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
		FB.Event.subscribe('edge.create', function(href, widget) {
			var currentPage = jQuery(document).attr('title');
			ga('send', {
				'hitType': 'social',
				'socialNetwork': 'Facebook',
				'socialAction': 'Like',
				'socialTarget': href,
				'page': currentPage
			});
			nebula_event('Social', 'Facebook Like', currentPage, {
				'dimension1': 'Like'
			});
		});

		//Facebook Unlikes
		FB.Event.subscribe('edge.remove', function(href, widget) {
			var currentPage = jQuery(document).attr('title');
			ga('send', {
				'hitType': 'social',
				'socialNetwork': 'Facebook',
				'socialAction': 'Unlike',
				'socialTarget': href,
				'page': currentPage
			});
			nebula_event('Social', 'Facebook Unlike', currentPage, {
				'dimension1': 'Unlike'
			});
		});

		//Facebook Send/Share
		FB.Event.subscribe('message.send', function(href, widget) {
			var currentPage = jQuery(document).attr('title');
			ga('send', {
				'hitType': 'social',
				'socialNetwork': 'Facebook',
				'socialAction': 'Send',
				'socialTarget': href,
				'page': currentPage
			});
			nebula_event('Social', 'Facebook Share', currentPage, {
				'dimension1': 'Share'
			});
		});

		//Facebook Comments
		FB.Event.subscribe('comment.create', function(href, widget) {
			var currentPage = jQuery(document).attr('title');
			ga('send', {
				'hitType': 'social',
				'socialNetwork': 'Facebook',
				'socialAction': 'Comment',
				'socialTarget': href,
				'page': currentPage
			});
			nebula_event('Social', 'Facebook Comment', currentPage, {
				'dimension1': 'Comment'
			});
		});
	};

	jQuery(document).on('click', '.facebook-connect', function(){
		facebookLoginLogout();
		return false;
	});

	//Load the SDK asynchronously
	(function(d, s, id) {
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) return;
		js = d.createElement(s); js.id = id;
		js.src = "//connect.facebook.net/en_GB/all.js";
		fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));
}

//Connect to Facebook without using Facebook Login button
function facebookLoginLogout() {
	if ( !FBstatus ) {
		FB.login(function(response) {
			if (response.authResponse) {
				checkFacebookStatus();
				nebula_event('Social', 'Facebook Connect', FBuser.name);
			} else {
				Gumby.log('User did not accept permissions.');
				checkFacebookStatus();
			}
		}, {scope:'public_profile,email'});
	} else {
		FB.logout(function(response) {
			Gumby.log('User has logged out.');
			checkFacebookStatus();
			prefillFacebookFields();
		});
	}
	return false;
}

//Fetch Facebook user information
function checkFacebookStatus() {
	FB.getLoginStatus(function(response) {
		if ( response.status === 'connected' ) { //User is logged into Facebook and is connected to this app.
			FBstatus = true;
			FB.api('/me', function(response) {
				FBuser = response;
				Gumby.log(response.name + ' has connected with this app.');
				prefillFacebookFields(response);
				jQuery('.facebook-connect-con a').text('Logout').removeClass('disconnected').addClass('connected');

				ga('send', 'pageview', {
					'dimension1': 'Connected' //@TODO: Is this how we want to do this?
				});

				jQuery('#facebook-connect p strong').text('You have been connected to Facebook, ' + response.first_name + '.'); //Example page. @TODO: Get this out of main.js somehow!
				jQuery('.fbpicture').attr('src', 'https://graph.facebook.com/' + response.id + '/picture?width=100&height=100'); //Example page. @TODO: Get this out of main.js somehow!
			});

			jQuery('#facebook-connect p strong').text('You have been connected to Facebook...'); //For Example page. @TODO: Get this out of main.js somehow!
		} else if (response.status === 'not_authorized') { //User is logged into Facebook, but has not connected to this app.
			Gumby.log('User is logged into Facebook, but has not connected to this app.');
			FBstatus = false;
			jQuery('.facebook-connect-con a').text('Connect with Facebook').removeClass('connected').addClass('disconnected');

			jQuery('#facebook-connect p strong').text('Please connect to this site by logging in below:'); //For Example page. @TODO: Get this out of main.js somehow!
		} else { //User is not logged into Facebook.
			Gumby.log('User is not logged into Facebook.');
			FBstatus = false;
			jQuery('.facebook-connect-con a').text('Connect with Facebook').removeClass('connected').addClass('disconnected');

			jQuery('#facebook-connect p strong').text('You are not logged into Facebook. Log in below:'); //For Example page. @TODO: Get this out of main.js somehow!
		}
	});
}

//Fill or clear form inputs with Facebook data
function prefillFacebookFields(response) {
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
	} else {
		jQuery('.fb-form-name, .comment-form-author input, .cform7-name, .fb-form-email, .comment-form-email input, input[type="email"]').each(function(){
			jQuery(this).val('').trigger('keyup');
		});
	}
}

function prefillCommentAuthorCookieFields(name, email) {
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
    var title = jQuery(document).attr('title');
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
			if ( jQuery(this).children().width() > bigWidth ) {
				bigWidth = jQuery(this).children().width();
			}
		jQuery(this).css('width', bigWidth+15 + 'px');
	});
} //end dropdownWidthController()


//Sub-menu viewport overflow detector
function overflowDetector() {
    jQuery('#primarynav .menu > .menu-item').hover(function(){
    	var viewportWidth = jQuery(window).width();
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
function subnavExpanders() {
    jQuery('.xoxo .menu li.menu-item:has(ul)').append('<a class="toplevelvert_expander plus" href="#"><i class="fa fa-caret-left"></i></a>');
    jQuery('.toplevelvert_expander').parent().children('.sub-menu').hide();
    jQuery(document).on('click', '.toplevelvert_expander', function(){
        jQuery(this).toggleClass('plus').parent().children('.sub-menu').slideToggle();
        return false;
    });
    //Automatically expand subnav to show current page
    jQuery('.current-menu-ancestor').children('.toplevelvert_expander').click();
    jQuery('.current-menu-item').children('.toplevelvert_expander').click();
} //end subnavExpanders()


//Show fixed bar when scrolling passed the header
function nebulaFixeder() {
	jQuery(window).on('scroll resize', function() {
		if ( !jQuery('.mobilenavcon').is(':visible') && !jQuery('.nobar').is('*') ) {
			var fixedBarBottom = jQuery('#logonavcon img').position().top + jQuery('#logonavcon img').outerHeight();
	        var windowBottom = jQuery(window).scrollTop();

	        if( windowBottom > fixedBarBottom ){
	        	if ( !jQuery('.fixedbar').hasClass('active') ) {
		        	jQuery('.fixedbar').addClass('active');
				}
	        } else {
	        	if ( !jQuery('.fixedbar').hasClass('hidden') ) {
		        	jQuery('.fixedbar').removeClass('active');
	        	}
	        }
		}
	});
} //end nebulaFixeder()


//Google Analytics Custom Dimensions
function gaCustomDimensions(){
	/*
		Custom Dimensions Index:
			Dimension 1 = Facebook Interaction (Like, Unlike, Comment, Share)
			Dimension 2 = Device Form Factor (Tablet, Mobile, Desktop) //@TODO: Do we really need this? GA has this standardized already...
			Dimension 3 = Is Mobile (True/False) //@TODO: Do we really need this? GA has this standardized already...

		Custom Dimension Ideas:
			- When location is available (through Zip Code, or IP geolocation), set dimension to user's local weather conditions. (Dimension is current condition, metric is current temperature). Maybe an event too?
			- Age Group / Gender / Etc. of user
	*/

	/*
ga('send', 'pageview', {
		'dimension2': deviceinfo['form_factor'],
		'dimension3': deviceinfo['is_mobile']
	});
*/
}


//Google Analytics Universal Analytics Event Trackers
function gaEventTracking(){
	if ( typeof nebula_event !== 'undefined' /* || typeof ga !== 'undefined' */ ) {

		//Example Event Tracker (Category and Action are required. If including a Value, it should be a rational number and not a string. Value could be an object of parameters like {'nonInteraction': 1, 'dimension1': 'Something', 'metric1': 82} Use deferred selectors.)
		//jQuery(document).on('click', '.selector', function() {
		//	nebula_event('Category', 'Action', 'Label', Value);
		//});


		//External links
		jQuery(document).on('click', "a[rel*='external']", function(){
			var linkText = jQuery(this).text();
			var destinationURL = jQuery(this).attr('href');
			nebula_event('External Link', linkText, destinationURL);
		});

		//PDF View/Download
		jQuery(document).on('click', "a[href$='.pdf']", function(){
			var title= jQuery('title').text()
			var linkText = jQuery(this).text();
			var fileName = jQuery(this).attr('href');
			fileName = fileName.substr(fileName.lastIndexOf("/")+1);
			if ( linkText == '' || linkText == 'Download') {
				nebula_event('PDF View', 'From Page: ' + title, 'File: ' + fileName);
			} else {
				nebula_event('PDF View', 'From Page: ' + title, 'Text: ' + linkText);
			}
		});

		//Contact Form Submissions
		jQuery(document).on('submit', '.wpcf7-form', function() {
			var currentPage = jQuery(document).attr('title');
			nebula_event('Contact', 'Submit', 'Contact Form Submission on ' + currentPage);
		});

		//Generic Interal Search Tracking
		jQuery(document).on('submit', '.search', function(){
			var searchQuery = jQuery(this).find('input[name="s"]').val();
			nebula_event('Internal Search', 'Submit', searchQuery);
		});

		//Mailto link tracking
		jQuery(document).on('click', 'a[href^="mailto"]', function(){
			var emailAddress = jQuery(this).attr('href');
			emailAddress = emailAddress.replace('mailto:', '');
			nebula_event('Mailto', 'Email: ' + emailAddress);
		});

		//Telephone link tracking
		jQuery(document).on('click', 'a[href^="tel"]', function(){
			var phoneNumber = jQuery(this).attr('href');
			phoneNumber = phoneNumber.replace('tel:+', '');
			nebula_event('Click-to-Call', 'Phone Number: ' + phoneNumber);
		});

		//SMS link tracking
		jQuery(document).on('click', 'a[href^="sms"]', function(){
			var phoneNumber = jQuery(this).attr('href');
			phoneNumber = phoneNumber.replace('sms:+', '');
			nebula_event('Click-to-Call', 'SMS to: ' + phoneNumber);
		});

		//Comment tracking @TODO: This might not be working.
		jQuery(document).on('submit', '#commentform', function(){
			if ( !jQuery(this).find('#submit').hasClass('disabled') ) {
				var currentPage = jQuery(document).attr('title');
				if ( jQuery('#reply-title').is('*') ) {
					var replyTo = jQuery('#reply-title').children('a').text();
					var commentID = jQuery('#reply-title').children('a').attr('href').replace('comment-', '');
					nebula_event('Comment', currentPage, 'Reply to: ' + replyTo + ' (' + commentID + ')');
				} else {
					nebula_event('Comment', currentPage, 'Top Level');
				}
			}
		});

		//Word copy tracking
		var copyCount = 0;
		var copyOver = 0;
		jQuery(document).on('cut copy', function(){
			copyCount++;
			var currentPage = jQuery(document).attr('title');
			var words = [];
			var selection = window.getSelection() + '';
			words = selection.split(' ');
			wordsLength = words.length;

			if ( copyCount < 13 ) {
				if (words.length > 8) {
					words = words.slice(0, 8).join(' ');
					nebula_event('Copied Text', currentPage, words + '... [' + wordsLength + ' words]');
				} else {
					if ( selection == '' || selection == ' ' ) {
						nebula_event('Copied Text', currentPage, '[0 words]');
					} else {
						nebula_event('Copied Text', currentPage, selection);
					}
				}
			} else {
				if ( copyOver == 0 ) {
					nebula_event('Copied Text', currentPage, '[Copy limit reached]');
				}
				copyOver = 1;
			}
		});

		//AJAX Errors
		jQuery(document).ajaxError(function(e, request, settings) {
			nebula_event('Error', 'AJAX Error', e.result + ' on: ' + settings.url, {'nonInteraction': 1});
			ga('send', 'exception', e.result, true);
		});


		//Capture Print Intent
		printed = 0;
		var afterPrint = function() {
			if ( printed == 0 ) {
				printed = 1;
				nebula_event('Print (Intent)', document.location.pathname);
			}
		};
		if ( window.matchMedia ) {
			var mediaQueryList = window.matchMedia('print');
			if ( mediaQueryList.addListener ) {
				mediaQueryList.addListener(function(mql) {
					if ( !mql.matches ) {
						afterPrint();
					}
				});
			}
		}
		window.onafterprint = afterPrint;
	}

} //End gaEventTracking()


//Google AdWords conversion tracking for AJAX
function conversionTracker(conversionpage) {
	if ( typeof conversionpage == 'undefined' ) {
		conversionpage = 'thanks.html';
	}

	var  iframe = document.createElement('iframe');
	iframe.style.width = '0px';
	iframe.style.height = '0px';
	document.body.appendChild(iframe);
	iframe.src = bloginfo['template_directory'] + '/includes/conversion/' + conversionpage;
};


function googlePlusCallback(jsonParam) {
	var currentPage = jQuery(document).attr('title');
	if ( jsonParam.state == 'on' ) {
		nebula_event('Social', 'Google+ Like', currentPage);
	} else if ( jsonParam.state == 'off' ) {
		nebula_event('Social', 'Google+ Unlike', currentPage);
	} else {
		nebula_event('Social', 'Google+ [JSON Unavailable]', currentPage);
	}
}

function mmenus() {
	if ( 'mmenu' in jQuery ) {
		jQuery("#mobilenav").mmenu({
		    //Options
		    offCanvas: {
			    zposition: 'back' //'back' (default), 'front', 'next'
		    },
		    searchfield: { //This is for searching through the menu itself (NOT for site search)
		    	add: true,
		    	search: true,
		    	placeholder: 'Search',
		    	noResults: 'No navigation items found.',
		    	showLinksOnly: false //"true" searches only <a> links, "false" includes spans in search results
		    },
		    counters: true, //Display count of sub-menus
		    classes: "mm-light mm-slide" //Theming and open effects
		}, {
			//Configuration
		}).on('opened.mm', function(){
			history.replaceState(null, document.title, location);
			history.pushState(null, document.title, location);
		});

		jQuery("#mobilecontact").mmenu({
			//Options
		    offCanvas: {
			    position: 'right', //'left' (default), 'right', 'top' (must use zposition 'front'), 'bottom' (must use zposition 'front')
			    zposition: 'back' //'back' (default), 'front', 'next'
		    },
		    classes: "mm-light", //Theming and open effects
		    header: {
				add: true,
				update: true, //Change the header text when navigating to sub-menus
				title: 'Contact Us'
			}
		}, {
			//Configuration
		}).on('opened.mm', function(){
			history.replaceState(null, document.title, location);
			history.pushState(null, document.title, location);
		});

		jQuery('.mm-search input').wrap('<form method="get" action="' + bloginfo['home_url'] + '"></form>').attr('name', 's');
		jQuery('.mm-search input').on('keyup', function(){
			if ( jQuery(this).val().length > 0 ) {
				jQuery('.clearsearch').removeClass('hidden');
			} else {
				jQuery('.clearsearch').addClass('hidden');
			}
		});
		jQuery('.mm-panel').append('<div class="clearsearch hidden"><strong class="doasitesearch">Press enter to search the site!</strong><br/><a href="#"><i class="fa fa-times-circle"></i>Reset Search</a></div>');
		jQuery(document).on('click', '.clearsearch a', function(){
			jQuery('.mm-search input').val('').keyup();
			jQuery('.clearsearch').addClass('hidden');
			return false;
		});

		//Close mmenu on back button click
		if (window.history && window.history.pushState) {
			window.addEventListener("popstate", function(e) {
				if ( jQuery('html.mm-opened').is('*') ) {
					jQuery(".mm-menu").trigger("close.mm");
					e.stopPropagation();
				}
			}, false);
		}

	}
} //end mmenus()

//Power Footer Width Distributor
function powerFooterWidthDist() {
	var powerFooterWidth = jQuery('#powerfooter').width();
	var topLevelFooterItems = 0;
	jQuery('#powerfooter ul.menu > li').each(function(){
		topLevelFooterItems = topLevelFooterItems+1;
	});
	var footerItemWidth = powerFooterWidth/topLevelFooterItems-8;
	if ( topLevelFooterItems == 0 ) {
		jQuery('.powerfootercon').addClass('hidden');
	} else {
		jQuery('#powerfooter ul.menu > li').css('width', footerItemWidth);
	}
} //end PowerFooterWidthDist


//Menu Search Replacement
function menuSearchReplacement(){
	jQuery('li.nebula-search').html('<form class="search" method="get" action="' + bloginfo['home_url'] + '/"><input type="search" class="input search" name="s" placeholder="Search" x-webkit-speech/></form>');
	jQuery('li.nebula-search input, input.nebula-search').on('focus', function(){
		jQuery(this).addClass('focus active');
	});
	jQuery('li.nebula-search input, input.nebula-search').on('blur', function(){
		if ( jQuery(this).val() == '' || jQuery(this).val().trim().length === 0 ) {
			jQuery(this).removeClass('focus active focusError').attr('placeholder', 'Search');

		} else {
			jQuery(this).removeClass('active');
		}
	});
}


//Search Validator
function searchValidator() {
	jQuery('.lt-ie9 form.search .btn.submit').val('Search');
	jQuery('.input.search').each(function(){
		if ( jQuery(this).val() == '' || jQuery(this).val().trim().length === 0 ) {
			jQuery(this).parent().children('.btn.submit').addClass('disallowed');
		} else {
			jQuery(this).parent().children('.btn.submit').removeClass('disallowed').val('Search');
			jQuery(this).parent().find('.input.search').removeClass('focusError');
		}
	});
	jQuery('.input.search').on('focus blur change keyup paste cut',function(e){
		if ( jQuery(this).val() == '' || jQuery(this).val().trim().length === 0 ) {
			jQuery(this).parent().children('.btn.submit').addClass('disallowed');
			jQuery(this).parent().find('.btn.submit').val('Go');
		} else {
			jQuery(this).parent().children('.btn.submit').removeClass('disallowed');
			jQuery(this).parent().find('.input.search').removeClass('focusError').prop('title', '').attr('placeholder', 'Search');
			jQuery(this).parent().find('.btn.submit').prop('title', '').removeClass('notallowed').val('Search');
		}
		if(e.type == 'paste'){
			jQuery(this).parent().children('.btn.submit').removeClass('disallowed');
			jQuery(this).parent().find('.input.search').prop('title', '').attr('placeholder', 'Search').removeClass('focusError');
			jQuery(this).parent().find('.btn.submit').prop('title', '').removeClass('notallowed').val('Search');
		}
	})
	jQuery('form.search').submit(function(){
		if ( jQuery(this).find('.input.search').val() == '' || jQuery(this).find('.input.search').val().trim().length === 0 ) {
			jQuery(this).parent().find('.input.search').prop('title', 'Enter a valid search term.').attr('placeholder', 'Enter a valid search term').addClass('focusError').focus().attr('value', '');
			jQuery(this).parent().find('.btn.submit').prop('title', 'Enter a valid search term.').addClass('notallowed');
			return false;
		} else {
			return true;
		}
	});
} //End searchValidator


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
	function preg_quote(str) {
		return (str + '').replace(/([\\\.\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:])/g, "\\$1");
	}
}


//Emphasize the search Terms
function emphasizeSearchTerms() {
	var theSearchTerm = document.URL.split('?s=')[1];
	if ( typeof theSearchTerm != 'undefined' ) {
		var origBGColor = jQuery('.searchresultword').css('background-color');
		jQuery('.searchresultword').each(function(i) {
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
	var theSearchTerm = document.URL.split('?rs=')[1]; //This is not needed if Search Everything can fix the "?s=" issue.
	if ( typeof theSearchTerm != 'undefined' ) {
		theSearchTerm = theSearchTerm.replace(/\+/g, ' ').replace(/\%20/g, ' ').replace(/\%22/g, ''); //This is not needed if Search Everything can fix the "?s=" issue.
		jQuery('#searchform input#s').val(theSearchTerm); //This is not needed if Search Everything can fix the "?s=" issue.
	}

	jQuery(document).on('click', '.headerdrawer .close', function(){
		var permalink = jQuery(this).attr('href');
		history.replaceState(null, document.title, permalink);
		jQuery('.headerdrawercon').slideUp();
		return false;
	});
}

//Suggestions for 404 page
function pageSuggestion(){
	if ( nebula_settings["nebula_cse_id"] != '' && nebula_settings["nebula_cse_api_key"] != '' ) {
		if ( GET().length ) {
			var queryStrings = GET();
		} else {
			var queryStrings = [''];
		}
		var path = window.location.pathname;
		var phrase = decodeURIComponent(path.replace(/\/+/g, ' ').trim()) + ' ' + decodeURIComponent(queryStrings[0].replace(/\+/g, ' ').trim());
		trySearch(phrase);

		jQuery(document).on('click', 'a.suggestion', function(){
			var suggestedPage = jQuery(this).text();
			nebula_event('Page Suggestion', 'Clicked', 'Suggested Page: ' + suggestedPage);
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
	jQuery.getJSON(API_URL, queryParams, function(response) {
		if (response.items && response.items.length) {
			nebula_event('Page Suggestion', 'Suggested Page: ' + response.items[0].title, 'Requested URL: ' + window.location, {'nonInteraction': 1});
			showSuggestedPage(response.items[0].title, response.items[0].link);
		} else {
			nebula_event('Page Suggestion', 'No Suggestions Found', 'Requested URL: ' + window.location, {'nonInteraction': 1});
		}
	});
}

function showSuggestedPage(title, url){
	var hostname = new RegExp(location.host);
	if ( hostname.test(url) ) {
		jQuery('.suggestion').text(title);
		jQuery('.suggestion').attr('href', url);
		jQuery('#suggestedpage').slideDown();
	}
}

//Page Visibility
function pageVisibility(){
	visFirstHidden = 0;
	visibilityChangeActions();
	jQuery(document).on('visibilitychange', function(){
		visibilityChangeActions();
	});

	function visibilityChangeActions(){
		if ( document.visibilityState == 'prerender' ) { //Page was prerendered
			var pageTitle = jQuery(document).attr('title');
			nebula_event('Page Visibility', 'Prerendered', pageTitle, {'nonInteraction': 1});
			//@TODO: prevent autoplay of videos
		}

		if ( getPageVisibility() ) { //Page is hidden
			//@TODO: pause youtube
			//@TODO: pause vimeo
			visFirstHidden = 1;
			visTimerBefore = (new Date()).getTime();
			var pageTitle = jQuery(document).attr('title');
			//nebula_event('Page Visibility', 'Hidden', pageTitle, {'nonInteraction': 1}); //@TODO: Page Visibility Hidden event tracking is off by default. Uncomment to enable.
		} else { //Page is visible
			//@TODO: resume autoplay of videos
			if ( visFirstHidden == 1 ) {
				var visTimerAfter = (new Date()).getTime();
				var visTimerResult = (visTimerAfter - visTimerBefore)/1000;
				var pageTitle = jQuery(document).attr('title');
				//nebula_event('Page Visibility', 'Visible', pageTitle + ' (Hidden for: ' + visTimerResult + 's)', {'nonInteraction': 1}); //@TODO: Page Visibility Visible event tracking is off by default. Uncomment to enable.
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

//Contact form pre-validator
function cFormPreValidator() {
	jQuery('.cform7-text').keyup(function(){
		if ( jQuery(this).val() == '' ) {
			jQuery(this).parent().parent().removeClass('danger').removeClass('success');
			jQuery(this).removeClass('wpcf7-not-valid');
		} else if ( jQuery(this).val().length && jQuery(this).val().trim().length === 0 ) {
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
		} else if ( jQuery(this).val().length && jQuery(this).val().trim().length === 0 ) {
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
		} else if ( jQuery(this).val().trim().length === 0 || jQuery(this).val().indexOf(' ') > 0 ) {
			jQuery(this).parent().parent().removeClass('success').removeClass('warning').addClass('danger');
		} else if ( jQuery(this).val().length && jQuery(this).val().indexOf('@') != 1 && jQuery(this).val().indexOf('.') < 0 ) {
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

		if ( jQuery(this).val().length && jQuery(this).val().indexOf('@') != 1 && jQuery(this).val().indexOf('.') < 0 ) {
			jQuery(this).parent().parent().removeClass('success').removeClass('warning').addClass('danger');
		}
	});

	if ( jQuery('.cform7-phone').is('*') || jQuery('.cform7-bday').is('*') ) {
		jQuery('.cform7-phone').mask("(999) 999-9999? x99999");
		jQuery('.cform7-phone').keyup(function(){
			if ( jQuery(this).val().replace(/\D/g,'').length >= 10 ) {
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
			if ( jQuery(this).val().replace(/\D/g,'').length === 8 ) {
				jQuery(this).parent().parent().addClass('success');
			} else {
				jQuery(this).parent().parent().removeClass('success');
			}
			var checkMonth = jQuery(this).val().substr(0, 2);
			var checkDay = jQuery(this).val().substr(3, 2);
			var checkYear = jQuery(this).val().substr(jQuery(this).val().length - 4);
			if ( checkYear != '____' ) {
				if ( checkYear < 1900 || checkYear > currentYear) {
					jQuery(this).parent().parent().removeClass('success').addClass('badyear');
				} else {
					jQuery(this).parent().parent().removeClass('badyear');
				}
			}
			if ( checkMonth != '__' ) {
				if ( checkMonth < 1 || checkMonth > 12) {
					jQuery(this).parent().parent().removeClass('success').addClass('badmonth');
				} else {
					jQuery(this).parent().parent().removeClass('badmonth');
				}
			}
			if ( checkDay != '__' ) {
				if ( checkDay < 1 || checkDay > 31) {
					jQuery(this).parent().parent().removeClass('success').addClass('badday');
				} else {
					jQuery(this).parent().parent().removeClass('badday');
				}
				//We could add specific checks for each individual month using checkMonth vs. checkDay.
			}
			if ( checkYear == '____' && checkMonth == '__' && checkDay == '__' ) {
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
		} else if ( jQuery(this).val().length && jQuery(this).val().trim().length === 0 ) {
			jQuery(this).parent().parent().addClass('warning');
		} else {
			jQuery(this).parent().parent().removeClass('danger');
			jQuery(this).parent().parent().removeClass('warning');
			jQuery(this).removeClass('wpcf7-not-valid');
			jQuery(this).attr('placeholder', 'Enter your message here.*');
		}
	});
	jQuery('.cform7-message').blur(function(){
		if ( jQuery(this).val().length && jQuery(this).val().trim().length === 0 ) {
			jQuery(this).parent().parent().removeClass('warning').addClass('danger');
		} else if ( jQuery(this).val() == '' ) {
			jQuery(this).parent().parent().removeClass('danger').removeClass('success').removeClass('warning');
		} else {
			jQuery(this).parent().parent().removeClass('danger').addClass('success');
		}
	});
	jQuery('.cform7-message').focus(function(){
		if ( jQuery(this).val().length && jQuery(this).val().trim().length === 0 ) {
			jQuery(this).parent().parent().removeClass('danger').addClass('warning');
		} else {
			jQuery(this).parent().parent().removeClass('danger').removeClass('warning').removeClass('success');
		}
	});
	var reqFieldsEmpty = 0;
	jQuery('.wpcf7-validates-as-required').each(function() {
		if ( jQuery(this).val() == '' ) {
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
		jQuery("#cform7-container li.danger").each(function() {
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
			if ( jQuery('input').hasClass('wpcf7-not-valid') ) {
				clearInterval(intervalID);
				jQuery('.wpcf7-not-valid').parent().parent().addClass('danger');
				jQuery('#cform7-container').parent().find('.wpcf7-submit').addClass('notallowed');
				if ( jQuery('.cform7-name.wpcf7-not-valid').val() == '' ) {
					jQuery('.cform7-name').attr('placeholder', 'Your name is required.');
				}
				if ( jQuery('.cform7-email.wpcf7-not-valid').val() == '' ) {
					jQuery('.cform7-email').attr('placeholder', 'Your email is required.');
				}
				if ( jQuery('.cform7-message.wpcf7-not-valid').val() == '' ) {
					jQuery('.cform7-message').attr('placeholder', 'Your message is required.');
				}
			} else {
				jQuery('.wpcf7-not-valid').parent().parent().removeClass('danger');
			}
        }, 100);
	});
} //end cFormPreValidator()

//CForm7 submit success callback
//Add on_sent_ok: "cFormSuccess();" to Additional Settings in WP Admin.
function cFormSuccess(){
    //Contact Form 7 Submit Success actions here. Could pass a parameter if needed.
    //conversionTracker(); //Call conversion tracker if contact is a conversion goal.
}

//Allows only numerical input on specified inputs. Call this on keyUp? @TODO: Make the selector into oThis and pass that to the function from above.
//The nice thing about this is that it shows the number being taken away so it is more user-friendly than a validation option.
function onlyNumbers() {
	jQuery(".leftcolumn input[type='text']").each(function(){
		this.value = this.value.replace(/[^0-9\.]/g, '');
	});
}

function checkCommentVal(oThis) {
	//@TODO: Count how many required fields there are. If any of them don't have value, then trigger disabled
	if ( jQuery(oThis).val() != '' ) {
		jQuery(oThis).parents('form').find('input[type="submit"], button[type="submit"]').removeClass('disabled');
	} else {
		jQuery(oThis).parents('form').find('input[type="submit"], button[type="submit"]').addClass('disabled');
	}
}

function WPcomments() {
	checkCommentVal('.comment-form-comment #comment');
	jQuery('.comment-form-comment #comment').on('keyup focus blur', function(){
		checkCommentVal(this);
	});

	jQuery(document).on('click', 'disabled', function(){
		return false;
	});

	jQuery('p.comment-form-comment textarea').on('focus', function(){
		jQuery(this).stop().animate({minHeight: 150}, 1000, "easeInOutCubic");
	});
	jQuery('p.comment-form-comment textarea').on('blur', function(){
		if ( jQuery(this).val() == '' ) {
			jQuery(this).stop().animate({minHeight: 42, height: 42}, 250, "easeInOutCubic").css('height', 'auto');
		}
	});
}

function scrollTo() {
	jQuery(document).on('click', 'a[href*=#]:not([href=#])', function() {
		if ( location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname ) {
			var target = jQuery(this.hash);
			target = target.length ? target : jQuery('[name=' + this.hash.slice(1) +']');
			if ( target.length ) {
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

function contactBackup() {
	checkCommentVal('.contact-form-message textarea');
	jQuery('.contact-form-message textarea').on('keyup focus blur', function(){
		checkCommentVal(this);
	});

	jQuery(document).on('click', 'disabled', function(){
		return false;
	});

	jQuery(document).on('submit', '.contact-form-backup', function(e){
		var contactData = [{
			'name': jQuery(".contact-form-name input").val(),
			'email': jQuery(".contact-form-email input").val(),
			'message': jQuery(".contact-form-message textarea").val()
		}];
		jQuery.ajax({
			type: "POST",
			url: bloginfo["admin_ajax"],
			data: {
				action: 'nebula_backup_contact_send',
				data: contactData,
			},
			success: function(response){
				jQuery('.contact-form-backup input:not(#contact-submit), .contact-form-backup textarea').val('');
				//Collapse the contact form and replace with sent notification
				//call google adwords conversion tracker
				//remove the contact form
				nebula_event('Contact', 'Submit', 'Backup Form Submission');
			},
			error: function(MLHttpRequest, textStatus, errorThrown){
				nebula_event('Contact', 'Error', 'Backup Form AJAX Error');
			},
			timeout: 60000
		});
		e.preventDefault();
		return false;
	});
}


//Create desktop notifications
function desktopNotification(title, message, clickCallback, closeCallback, showCallback, errorCallback) {
	if ( checkNotificationPermission() ) {
		//Set defaults
		var defaults = {
			dir: "auto", //Direction ["auto", "ltr", "rtl"] (optional)
			lang: "en-US", //Language (optional)
			body: "", //Body message (optional)
			tag: Math.floor(Math.random()*10000)+1, //Unique tag for notification. Prevents repeat notifications of the same tag. (optional)
			icon: bloginfo['template_directory'] + "/images/og-thumb.png" //Thumbnail Icon (optional)
		}

		if ( typeof message === "undefined" ) {
			message = defaults;
			Gumby.warn('Warning: message is undefined, using defaults.');
		} else if ( typeof message === "string" ) {
			body = message;
			message = defaults;
			message.body = body;
			Gumby.log('Note: message is a string, using defaults.');
		} else {
			if ( typeof message.dir === "undefined" ) {
				message.dir = defaults.dir;
			}
			if ( typeof message.lang === "undefined" ) {
				message.lang = defaults.lang;
			}
			if ( typeof message.body === "undefined" ) {
				message.body = defaults.lang;
				Gumby.warn('Warning: No message body.');
			}
			if ( typeof message.tag === "undefined" ) {
				message.tag = defaults.tag;
			}
			if ( typeof message.icon === "undefined" ) {
				message.icon = defaults.icon;
			}
		}

		instance = new Notification(title, message); //Trigger the notification

		if ( typeof clickCallback !== "undefined" ) {
			instance.onclick = function() {
				clickCallback();
			};
		}
		if ( typeof closeCallback !== "undefined" ) {
			instance.onclose = function() {
				closeCallback();
			};
		}
		if ( typeof showCallback !== "undefined" ) {
			instance.onshow = function() {
				showCallback();
			};
		}
		if ( typeof errorCallback !== "undefined" ) {
			instance.onerror = function() {
				errorCallback();
			};
		}
	}
	return false;
}

function checkNotificationPermission() {
	Notification = window.Notification || window.mozNotification || window.webkitNotification;
	if ( !(Notification) ) {
		Gumby.warn("This browser does not support desktop notifications.");
		return false;
	} else if ( Notification.permission === "granted" ) {
		return true;
	} else if ( Notification.permission !== 'denied' ) {
		Notification.requestPermission(function (permission) {
			if( !('permission' in Notification) ) {
				Notification.permission = permission;
			}
			if (permission === "granted") {
				return true;
			}
		});
	}
	return false;
}

function nebulaVibrate(pattern) {
	if ( typeof pattern === 'undefined' ) {
		Gumby.warn('Vibration pattern was not provided. Using default.');
		pattern = [100, 200, 100, 100, 75, 25, 100, 200, 100, 500, 100, 200, 100, 500];
	} else if ( typeof pattern !== 'object' ) {
		Gumby.warn('Vibration pattern is not an object. Using default.');
		pattern = [100, 200, 100, 100, 75, 25, 100, 200, 100, 500, 100, 200, 100, 500];
	}
	if ( checkVibration() ) {
		navigator.vibrate(pattern);
	}
	return false;
}

function checkVibration() {
	if ( !jQuery('body').hasClass('mobile') ) {
		Gumby.warn("This is not a mobile device, so vibration may not work (even if it declares support).");
	}

	Vibration = navigator.vibrate || navigator.webkitVibrate || navigator.mozVibrate || navigator.msVibrate;
	if ( !(Vibration) ) {
		Gumby.warn("This browser does not support vibration.");
		return false;
	} else {
		return true;
	}
}

//Detect and log errors, and fallback fixes
function errorLogAndFallback() {
	//Check if Contact Form 7 is active and if the selected form ID exists
	if ( jQuery('.cform-disabled').is('*') ) {
		var currentPage = jQuery(document).attr('title');
		nebula_event('Error', 'Contact Form 7 Disabled', currentPage, {'nonInteraction': 1});
		Gumby.warn('Warning: Contact Form 7 is disabled! Reverting to mailto link.');
	} else if ( jQuery('#cform7-container:contains("Not Found")').length > 0 ) {
		jQuery('#cform7-container').text('').append('<li><div class="medium primary btn icon-left entypo fa fa-envelope"><a class="cform-not-found" href="mailto:' + bloginfo['admin_email'] + '?subject=Email%20submission%20from%20' + document.URL + '" target="_blank">Email Us</a></div><!--/button--></li>');
		nebula_event('Error', 'Contact Form 7 Form Not Found', currentPage, {'nonInteraction': 1});
		Gumby.warn('Warning: Contact Form 7 form is not found! Reverting to mailto link.');
		jQuery(document).on('click', '.cform-not-found', function(){
			nebula_event('Contact', 'Submit (Intent)', 'Backup Mailto Intent');
		});
	}
}

//Waits until event (generally resize) finishes before triggering. Call with waitForFinalEvent();
var waitForFinalEvent = (function () {
	var timers = {};
	return function (callback, ms, uniqueId) {
		if (!uniqueId) {
			uniqueId = "Don't call this twice without a uniqueId";
		}
		if (timers[uniqueId]) {
			clearTimeout (timers[uniqueId]);
		}
		timers[uniqueId] = setTimeout(callback, ms);
	};
})(); //end waitForFinalEvent()


//Conditional JS Library Loading
//This could be done better I think (also, it runs too late in the stack).
function conditionalJSLoading() {

	detectIconFonts();

	//Only load Twitter if Twitter wrapper exists.
	if ( jQuery('#twittercon').is('*') ) {
		jQuery.getScript(bloginfo['template_directory'] + '/js/libs/twitter.js').done(function(){
			twitterFeed();
		}).fail(function(){
			jQuery('#twittercon').css('border', '1px solid red').addClass('hidden');
			nebula_event('Error', 'JS Error', 'twitter.js could not be loaded.', {'nonInteraction': 1});
		});
	}

	//Only load bxslider library on a page that calls bxslider.
	if ( jQuery('.bxslider').is('*') ) {
		jQuery.getScript(bloginfo['template_directory'] + '/js/libs/jquery.bxslider.min.js').done(function(){
			bxSlider();
		}).fail(function(){
			nebula_event('Error', 'JS Error', 'bxSlider could not be loaded.', {'nonInteraction': 1});
		});
		Modernizr.load(bloginfo['template_directory'] + '/css/jquery.bxslider.css');
	}

	//Only load maskedinput.js library if phone or bday field exists.
	if ( jQuery('.cform7-phone').is('*') || jQuery('.cform7-bday').is('*') ) {
		jQuery.getScript(bloginfo['template_directory'] + '/js/libs/jquery.maskedinput.js').done(function(){
			cFormPreValidator();
		}).fail(function(){
			nebula_event('Error', 'JS Error', 'jquery.maskedinput.js could not be loaded.', {'nonInteraction': 1});
		});
	} else {
		cFormPreValidator();
	}

	//Only load dataTables library if dataTables table exists.
	if ( jQuery('.dataTables_wrapper').is('*') ) {
		jQuery.getScript(bloginfo['template_directory'] + '/js/libs/jquery.dataTables.min.js').done(function(){ //@TODO: Use CDN?
			dataTablesActions();
		}).fail(function(){
			nebula_event('Error', 'JS Error', 'jquery.dataTables.min.js could not be loaded', {'nonInteraction': 1});
		});
		Modernizr.load(bloginfo['template_directory'] + '/css/jquery.dataTables.css');

		jQuery.getScript(bloginfo['template_directory'] + '/js/libs/jquery.highlight-4.closure.js').done(function(){
			//Do something
		}).fail(function(){
			nebula_event('Error', 'JS Error', 'jquery.highlight-4.closure.js could not be loaded.', {'nonInteraction': 1});
		});
	}

	if ( jQuery('.flag').is('*') ) {
		Modernizr.load(bloginfo['template_directory'] + '/css/flags.css');
	}

} //end conditionalJSLoading()


//These detect Font Awesom and Entypo usage via classes. This will not detect usage with font-family CSS (only known detection method is resource-heavy).
loadedFonts = [];
loadedFonts['Entypo'] = 0;
loadedFonts['FontAwesome'] = 1;
function detectIconFonts(){
	if ( jQuery('i.fa').is('*') && loadedFonts['FontAwesome'] == 0 ) {
		Modernizr.load('//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.1.0/css/font-awesome.min.css');
		loadedFonts['FontAwesome'] = 1;
	}

	if ( jQuery('div[class^="icon-"], div[class*=" icon-"]').is('*') && loadedFonts['Entypo'] == 0 ) {
		Modernizr.load(bloginfo['template_directory'] + '/css/entypo.css'); //Note this is supplemental to gumby.css
		loadedFonts['Entypo'] = 1;
	}
}


function dataTablesActions(){
	jQuery(document).on('keyup', '.dataTables_wrapper .dataTables_filter input', function() { //@TODO: Something here is eating the first letter after a few have been typed... lol
	    console.log('keyup: ' + jQuery(this).val());
	    jQuery('.dataTables_wrapper').removeHighlight();
	    jQuery('.dataTables_wrapper').highlight(jQuery(this).val());
	});
}

//Twitter Feed integration
function twitterFeed() {
    if ( typeof JQTWEET !== 'undefined' ) {
        JQTWEET = JQTWEET || {};
        //JQTWEET.search = '#hashtag';
        JQTWEET.user = 'pinckneyhugo';
        JQTWEET.numTweets = 3;
        JQTWEET.template = '<div class="row tweetcon"><div class="four columns"><div class="twittericon">{AVA}</div></div><div class="twelve columns"><div class="twitteruser"><a href="{URL}" target="_blank">@{USER}</a></div><div class="twittertweet">{TEXT} <a class="twitterago" href="{URL}" target="_blank">{AGO}</a></div></div></div>',
        JQTWEET.appendTo = '#twitter_update_list';
        JQTWEET.loadTweets();

        console.log('tweets loaded.');
        console.debug(JQTWEET);
    }
} //end twitterFeed()

//Place all bxSlider events inside this function!
function bxSlider() {
	if ( typeof bxSlider !== 'undefined' ) {
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

function vimeoControls() {
	if ( jQuery('.vimeoplayer').is('*') ) {
        jQuery.getScript(bloginfo['template_directory'] + '/js/libs/froogaloop.min.js').done(function(){
			createVimeoPlayers();
		}).fail(function(){
			Gumby.warn('froogaloop.js could not be loaded.');
		});
	}

	function createVimeoPlayers() {
		var player = new Array();
	    jQuery('iframe.vimeoplayer').each(function(i){
			var vimeoiframeClass = jQuery(this).attr('id');
			player[i] = $f(vimeoiframeClass);
			player[i].addEvent('ready', function() {
		    	Gumby.log('player is ready');
			    player[i].addEvent('play', onPlay);
			    player[i].addEvent('pause', onPause);
			    player[i].addEvent('seek', onSeek);
			    player[i].addEvent('finish', onFinish);
			    player[i].addEvent('playProgress', onPlayProgress);
			});
		});
	}

	function onPlay(id) {
	    var videoTitle = id.replace(/-/g, ' ');
	    nebula_event('Videos', 'Play', videoTitle);
	}

	function onPause(id) {
	    var videoTitle = id.replace(/-/g, ' ');
	    nebula_event('Videos', 'Pause', videoTitle);
	}

	function onSeek(data, id) {
	    var videoTitle = id.replace(/-/g, ' ');
	    nebula_event('Videos', 'Seek', videoTitle + ' [to: ' + data.seconds + ']');
	}

	function onFinish(id) {
		var videoTitle = id.replace(/-/g, ' ');
		nebula_event('Videos', 'Finished', videoTitle, {'nonInteraction': 1});
	}

	function onPlayProgress(data, id) {
		//Gumby.log(data.seconds + 's played');
	}
}



function cookieActions() {

	/*
		createCookie('example', 'true', 30);

		if ( readCookie('example') ) {
			//Stuff here if cookie exists
		}

		eraseCookie('example');
	*/

	//Cookie actions here


} //end cookieActions()

//Cookie Management
function createCookie(name, value, days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires=" + date.toGMTString();
	} else {
		var expires = "";
	}
	document.cookie = name + "=" + value + expires + "; path=/";
	Gumby.log('Created cookie: ' + name + ', with the value: ' + value + expires);
}
function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for (var i=0; i<ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) == ' ') {
			c = c.substring(1, c.length);
			if (c.indexOf(nameEQ) == 0) {
				Gumby.log('Cookie "' + name + '" exists.');
				return c.substring(nameEQ.length, c.length);
			}
		}
	}
	return null;
}
function eraseCookie(name) {
	createCookie(name,"",-1);
	Gumby.warn('Erased cookie: ' + name);
}



/* ==========================================================================
   Google Maps API v3 Functions
   ========================================================================== */

//Interactive Functions of the Google Map
function mapActions() {
	originalWeatherText = jQuery('.mapweather').text();
	jQuery(document).on('click', '.mapweather', function(){
		if ( mapInfo['weather'] == 1 ) {
			mapInfo['weather'] = 0;
			jQuery('.mapweather').removeClass('active').addClass('inactive').text(originalWeatherText);
			jQuery('.mapweather-icon').removeClass('active').addClass('inactive');
			Gumby.log('Disabling weather layer.');
		} else {
			mapInfo['weather'] = 1;
			jQuery('.mapweather').addClass('active').removeClass('inactive').text('Disable Weather');
			jQuery('.mapweather-icon').addClass('active').removeClass('inactive');
			Gumby.log('Enabling weather layer.');
		}
		renderMap(mapInfo);
		return false;
	});

	originalTrafficText = jQuery('.maptraffic').text();
	jQuery(document).on('click', '.maptraffic', function(){
		if ( mapInfo['traffic'] == 1 ) {
			mapInfo['traffic'] = 0;
			jQuery('.maptraffic').removeClass('active').addClass('inactive').text(originalTrafficText);
			jQuery('.maptraffic-icon').removeClass('active').addClass('inactive');
			Gumby.log('Disabling traffic layer.');
		} else {
			mapInfo['traffic'] = 1;
			jQuery('.maptraffic').addClass('active').removeClass('inactive').text('Disable Traffic');
			jQuery('.maptraffic-icon').addClass('active').removeClass('inactive');
			Gumby.log('Enabling traffic layer.');
		}
		renderMap(mapInfo);
		return false;
	});

	jQuery(document).on('click', '.mapgeolocation', function(){
		if ( typeof mapInfo['detectLoc'] === 'undefined' || mapInfo['detectLoc'][0] == 0 ) {
			Gumby.log('Enabling location detection.');
			jQuery('.mapgeolocation-icon').removeClass('inactive fa-location-arrow').addClass('fa-spinner fa-spin');
			jQuery('.mapgeolocation').removeClass('inactive').attr('title', 'Requesting location...').text('Detecting Location...');
			requestPosition();
		} else {
			Gumby.log('Removing detected location.');
			jQuery('.mapgeolocation-icon').removeClass('fa-spinner fa-ban success error').addClass('inactive fa-location-arrow');
			jQuery(this).removeClass('active success failure').text('Detect Location').addClass('inactive').attr('title', 'Detect current location').css('color', '');
			mapInfo['detectLoc'] = new Array(0, 0);
			renderMap(mapInfo);
		}
		return false;
	});

	jQuery('.mapgeolocation').hover(function(){
		if ( jQuery(this).hasClass('active') ) {
			jQuery('.mapgeolocation-icon').removeClass('fa-location-arrow').addClass('fa-ban');
		}
	}, function(){
		if ( jQuery(this).hasClass('active') ) {
			jQuery('.mapgeolocation-icon').removeClass('fa-ban').addClass('fa-location-arrow');
		}
	});

	originalRefreshText = jQuery('.maprefresh').text();
	pleaseWait = 0;
	jQuery(document).on('click', '.maprefresh', function(){
		if ( !jQuery(this).hasClass('timeout') ) {
			pleaseWait = 0;
			Gumby.log('Refreshing the map.');
			renderMap(mapInfo);
			jQuery('.maprefresh').addClass('timeout', function(){
				jQuery('.maprefresh').text('Refreshing...');
				jQuery('.maprefresh-icon').removeClass('inactive').addClass('fa-spin');
			});
		} else {
			pleaseWait++;
			if ( pleaseWait < 10 ) {
				jQuery('.maprefresh').text('Please wait...');
			} else {
				jQuery('.maprefresh').text('Hold your horses!');
			}
		}
		return false;
	});

	//Event Listeners

	//Refresh listener
	jQuery(document).on('mapRendered', function(){
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
	jQuery(document).on('geolocationSuccess', function(){
		jQuery('.mapgeolocation').text('Location Accuracy: ').append('<span>' + mapInfo['detectLoc']['accMiles'] + ' miles <small>(' + mapInfo['detectLoc']['accMeters'].toFixed(2) + ' meters)</small></span>').find('span').css('color', mapInfo['detectLoc']['accColor']);
		setTimeout(function(){
			jQuery('.mapgeolocation').addClass('active').attr('title', '');
			jQuery('.mapgeolocation-icon').removeClass('fa-spinner fa-spin inactive').addClass('fa-location-arrow');
		}, 500);
	});

	//Geolocation Error listener
	jQuery(document).on('geolocationError', function(){
		jQuery('.mapgeolocation').removeClass('success').text(geolocationErrorMessage);
		setTimeout(function(){
			jQuery('.mapgeolocation').attr('title', '');
			jQuery('.mapgeolocation-icon').removeClass('fa-spinner fa-spin').addClass('fa-location-arrow error');
		}, 500);
	});
} //End mapActions()

//Request Geolocation
function requestPosition() {
	Gumby.log('Requesting location... May need to be accepted.');
    var nav = null;
    if (nav == null) {
        nav = window.navigator;
    }
    var geoloc = nav.geolocation;
    if (geoloc != null) {
        geoloc.getCurrentPosition(successCallback, errorCallback, {enableHighAccuracy: true});
    }
}

//Geolocation Success
function successCallback(position) {
	jQuery('.mapgeolocation').removeClass('failure').addClass('success');

	mapInfo['detectLoc'] = [];
	mapInfo['detectLoc'][0] = position.coords.latitude;
	mapInfo['detectLoc'][1] = position.coords.longitude;
	mapInfo['detectLoc']['accMeters'] = position.coords.accuracy;
	mapInfo['detectLoc']['alt'] = position.coords.altitude;
	mapInfo['detectLoc']['speed'] = position.coords.speed;

	if ( ( mapInfo['detectLoc']['accMeters'] <= 25 ) ) {
		mapInfo['detectLoc']['accColor'] = '#00bb00';
	} else if ( mapInfo['detectLoc']['accMeters'] > 25 && mapInfo['detectLoc']['accMeters'] <= 50 ) {
		mapInfo['detectLoc']['accColor'] = '#46d100';
	} else if ( mapInfo['detectLoc']['accMeters'] > 51 && mapInfo['detectLoc']['accMeters'] <= 150 ) {
		mapInfo['detectLoc']['accColor'] = '#a4ed00';
	} else if ( mapInfo['detectLoc']['accMeters'] > 151 && mapInfo['detectLoc']['accMeters'] <= 400 ) {
		mapInfo['detectLoc']['accColor'] = '#f2ee00';
	} else if ( mapInfo['detectLoc']['accMeters'] > 401 && mapInfo['detectLoc']['accMeters'] <= 800 ) {
		mapInfo['detectLoc']['accColor'] = '#ffc600';
	} else if ( mapInfo['detectLoc']['accMeters'] > 801 && mapInfo['detectLoc']['accMeters'] <= 1500 ) {
		mapInfo['detectLoc']['accColor'] = '#ff6f00';
	} else if ( mapInfo['detectLoc']['accMeters'] > 1501 && mapInfo['detectLoc']['accMeters'] <= 3000 ) {
		mapInfo['detectLoc']['accColor'] = '#ff1900';
	} else if ( mapInfo['detectLoc']['accMeters'] > 3001 ) {
		mapInfo['detectLoc']['accColor'] = '#ff0000';
	} else {
		mapInfo['detectLoc']['accColor'] = '#ff0000';
	}
	renderMap(mapInfo);

	mapInfo['detectLoc']['accMiles'] = (mapInfo['detectLoc']['accMeters']*0.000621371).toFixed(2);

	if ( mapInfo['detectLoc']['accMeters'] > 400 ) {
		lowAccText = 'Your location accuracy is ' + mapInfo['detectLoc']['accMiles'] + ' miles (as shown by the colored radius).';
		Gumby.warn('Poor location accuracy: ' + mapInfo['detectLoc']['accMiles'] + ' miles (as shown by the colored radius).');
		//Some kind of notification here...
	}

	jQuery(document).trigger('geolocationSuccess');
	nebula_event('Geolocation', 'Location: ' + mapInfo['detectLoc'][0] + ', ' + mapInfo['detectLoc'][1], 'Accuracy (Miles): ' + mapInfo['detectLoc']['accMiles']); //@TODO: Add a GA dimension and metric to get weather from this location.
}

//Geolocation Error
function errorCallback(error) {
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
    Gumby.warn(geolocationErrorMessage);
    jQuery(document).trigger('geolocationError');
    nebula_event('Geolocation', 'Error', geolocationErrorMessage, {'nonInteraction': 1});
}

//Retreive Lat/Lng locations
function getAllLocations() {
	mapInfo['markers'] = [];
	jQuery('.latlngcon').each(function(i){
		var alat = jQuery(this).find('.lat').text();
		var alng = jQuery(this).find('.lng').text();
		Gumby.log(i + ': found location! lat: ' + alat + ', lng: ' + alng);
		mapInfo['markers'][i] = [alat, alng];
	});
	renderMap(mapInfo);
}

//Render the Google Map
function renderMap(mapInfo) {
    Gumby.log('Rendering Google Map');

    if ( typeof google === 'undefined' ) {
    	Gumby.log('google is not defined. Likely the Google Maps script is not being seen.');
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

		if ( typeof mapInfo['traffic'] !== 'undefined' ) {
			if ( mapInfo['traffic'] == 1 ) {
				Gumby.log('Traffic is enabled.');
				var trafficLayer = new google.maps.TrafficLayer();
				trafficLayer.setMap(map);
			}
		}

		//Map weather
		if ( typeof mapInfo['weather'] !== 'undefined' ) {
			if ( mapInfo['weather'] == 1 ) {
				Gumby.log('Weather is enabled.');
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
		if ( typeof mapInfo['markers'] !== 'undefined' ) {
			var marker, i;
		    for (i = 0; i < mapInfo['markers'].length; i++) {
		        var pos = new google.maps.LatLng(mapInfo['markers'][i][0], mapInfo['markers'][i][1]);
		        bounds.extend(pos);
		        marker = new google.maps.Marker({
		            position: pos,
		            //icon:'../../wp-content/themes/gearside2014/images/map-icon-marker.png', //@TODO: It would be cool if these were specific icons for each location. Pull from frontend w/ var?
		            clickable: false,
		            map: map
		        });
		        Gumby.log('Marker created for: ' + mapInfo['markers'][i][0] + ', ' + mapInfo['markers'][i][1]);
		    }(marker, i);
	    }

		//Detected Location Marker
		if ( typeof mapInfo['detectLoc'] !== 'undefined' ) {
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
				Gumby.log('Marker created for detected location: ' + mapInfo['detectLoc'][0] + ', ' + mapInfo['detectLoc'][1]);

				//var detectbounds = new google.maps.LatLngBounds();
				bounds.extend(detectLoc);
				//map.fitBounds(detectbounds); //Use this instead of the one below to center on detected location only (ignoring other markers)
			}
		}

		map.fitBounds(bounds);
		google.maps.event.trigger(map, "resize");

		jQuery(document).trigger('mapRendered');
	}
}