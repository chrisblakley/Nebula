jQuery.noConflict();

jQuery(document).ready(function() {	

	mapInfo = [];
	getAllLocations();
	mapEnhancements();

	/* To be vetted. Turn these into functions.
	
		//Pull query strings from URL
		var queries = new Array(); 
	    var q = document.URL.split('?')[1];
	    if(q != undefined){
	        q = q.split('&');
	        for(var i = 0; i < q.length; i++){
	            hash = q[i].split('=');
	            queries.push(hash[1]);
	            queries[hash[0]] = hash[1];
	        }
		} //End pull query strings from URL

	
		//Search term highlighter
		var theSearchTerm = document.URL.split('?s=')[1];
		if (typeof theSearchTerm != 'undefined' ) {
			theSearchTerm = theSearchTerm.replace(/\+/g, ' ').replace(/\%20/g, ' ').replace(/\%22/g, '');
			//console.log('the search is: ' + theSearchTerm);
			jQuery('.searchcon .entry-title a, .searchcon .entry-summary').each(function(i){
				var searchFinder = jQuery(this).text().replace( new RegExp( '(' + preg_quote( theSearchTerm ) + ')' , 'gi' ), '<span class="searchresultword">$1</span>' );
				jQuery(this).html(searchFinder);
			});
		}
		function preg_quote( str ) {
			return (str+'').replace(/([\\\.\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:])/g, "\\$1");
		} //End Search term highlighter
	
	/*



	//Init Custom Functions
	gaEventTracking();
	
	helperFunctions();
	socialSharing();
	dropdownWidthController();
	overflowDetector();
	subnavExpanders();	
	nebulaFixeder();
	
	/* Choose whether to use mmenu or doubletaptogo for mobile device navigation */
	mmenu();
	//jQuery('#primarynav .menu-item-has-children').doubleTapToGo();
	
	powerFooterWidthDist();
	searchValidator();
	errorLogAndFallback();
	
	viewport = updateViewportDimensions();
	//console.debug(viewport);
	jQuery(window).resize(function() {
		waitForFinalEvent(function(){
		
	    	//Window resize functions here.
	    	powerFooterWidthDist();
	    	
	    	//Track size change
	    	viewportResized = updateViewportDimensions();
	    	if ( viewport.width > viewportResized.width ) {
	    		ga('send', 'event', 'Window Resize', 'Smaller', viewport.width + 'px to ' + viewportResized.width + 'px');
	    	} else if ( viewport.width < viewportResized.width ) {
	    		ga('send', 'event', 'Window Resize', 'Bigger', viewport.width + 'px to ' + viewportResized.width + 'px');
	    	}
	    	viewport = updateViewportDimensions();
	    	//console.debug(viewport);
		}, 500, "unique resize ID 1");
	});
	
	
}); //End Document Ready




jQuery(window).on('load', function() {
	
	conditionalJSLoading();
	
	jQuery('a, li, tr').removeClass('hover');
	jQuery('html').addClass('loaded');
	jQuery('.unhideonload').removeClass('hidden');
		
}); //End Window Load




/*==========================
 
 Functions
 
 ===========================*/

//Zebra-striper, First-child/Last-child, Hover helper functions
function helperFunctions(){
	jQuery('li:even, tr:even').addClass('even');
	jQuery('li:odd, tr:odd').addClass('odd');
	jQuery('ul:first-child, li:first-child, tr:first-child').addClass('first-child');
	jQuery('li:last-child, tr:last-child').addClass('last-child');
	jQuery('.column:first-child, .columns:first-child').addClass('first-child');
	jQuery('a:hover, li:hover, tr:hover').addClass('hover');
} //end helperFunctions()


//Social sharing buttons
function socialSharing() {
    var loc = window.location;
    var title = jQuery(document).attr('title');
    var encloc = encodeURI(loc);
    var enctitle = encodeURI(title);
    jQuery('.fbshare').attr('href', 'http://www.facebook.com/sharer.php?u=' + encloc + '&t=' + enctitle).attr('target', '_blank');
    jQuery('.twshare').attr('href', 'https://twitter.com/intent/tweet?text=' + enctitle + '&url=' + encloc).attr('target', '_blank');
    jQuery('.lishare').attr('href', 'http://www.linkedin.com/shareArticle?mini=true&url=' + encloc + '&title=' + enctitle).attr('target', '_blank');
    jQuery('.gshare').attr('href', 'https://plus.google.com/share?url=' + encloc).attr('target', '_blank');
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
    jQuery('.xoxo .menu li.menu-item:has(ul)').append('<a class="toplevelvert_expander plus" href="#"><i class="icon-left-dir"></i></a>');
    jQuery('.toplevelvert_expander').parent().children('.sub-menu').hide();
    jQuery('.toplevelvert_expander').on('click', function(){
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
		if ( !jQuery('.mobilenavcon').is(':visible') && !jQuery('.nobar').length ) {
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


//Google Analytics Universal Analytics Event Trackers
function gaEventTracking(){
	//Example Event Tracker (Category and Action are required. If including a Value, it should be a rational number and not a string.)
	//jQuery('.selector').on('click', function() {
	//	ga('send', 'event', 'Category', 'Action', 'Label', Value;
	//});
	
	//External links
	jQuery("a[rel*='external']").on('click', function(){
		var linkText = jQuery(this).text();
		ga('send', 'event', 'External Link', linkText);
		Gumby.log('Sending GA event: ' + 'External Link', linkText);
	});
	
	//PDF View/Download
	jQuery("a[href$='.pdf']").on('click', function(){
		var title= jQuery('title').text()
		var linkText = jQuery(this).text();
		var fileName = jQuery(this).attr('href');
		fileName = fileName.substr(fileName.lastIndexOf("/")+1);
		if ( linkText == '' || linkText == 'Download') {
			ga('send', 'event', 'PDF View', 'From Page: ' + title, 'File: ' + fileName);
			Gumby.log('Sending GA event: ' + 'PDF View', 'From Page: ' + title, 'File: ' + fileName);
		} else {
			ga('send', 'event', 'PDF View', 'From Page: ' + title, 'Text: ' + linkText);
			Gumby.log('Sending GA event: ' + 'PDF View', 'From Page: ' + title, 'Text: ' + linkText);
		}
	});
	
	//Contact Form Submissions
	jQuery('.wpcf7-form').on('submit', function() {
		var currentPage = jQuery(document).attr('title');
		ga('send', 'event', 'Contact', 'Submit', 'Contact Form Submission on ' + currentPage);
		Gumby.log('Sending GA event: ' + 'Contact', 'Submit', 'Contact Form Submission on ' + currentPage);
	});
	
	//Generic Interal Search Tracking
	jQuery('.search').on('submit', function(){
		var searchQuery = jQuery(this).find('input[name="s"]').val();
		ga('send', 'event', 'Internal Search', 'Submit', searchQuery);
		Gumby.log('Sending GA event: ' + 'Internal Search', 'Submit', searchQuery);
	});
	
	//Mailto link tracking
	jQuery('a[href^="mailto"]').on('click', function(){
		var emailAddress = jQuery(this).attr('href');
		emailAddress = emailAddress.replace('mailto:', '');
		ga('send', 'event', 'Contact Us', 'Email: ' + emailAddress);
		Gumby.log('Sending GA event: ' + 'Contact Us', 'Email: ' + emailAddress);
	});
	
	//Telephone link tracking
	jQuery('a[href^="tel"]').on('click', function(){
		var phoneNumber = jQuery(this).attr('href');
		phoneNumber = phoneNumber.replace('tel:+', '');
		ga('send', 'event', 'Click-to-Call', 'Phone Number: ' + phoneNumber);
		Gumby.log('Sending GA event: ' + 'Click-to-Call', 'Phone Number: ' + phoneNumber);
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
				ga('send', 'event', 'Copied Text', currentPage, words + '... [' + wordsLength + ' words]');
				Gumby.log('Sending GA event: ' + 'Copied Text', currentPage, words + '... [' + wordsLength + ' words]');
			} else {
				if ( selection == '' || selection == ' ' ) {
					ga('send', 'event', 'Copied Text', currentPage, '[0 words]');
					Gumby.log('Sending GA event: ' + 'Copied Text', currentPage, '[0 words]');
				} else {
					ga('send', 'event', 'Copied Text', currentPage, selection);
					Gumby.log('Sending GA event: ' + 'Copied Text', currentPage, selection);
				}
			}
		} else {
			if ( copyOver == 0 ) {
				ga('send', 'event', 'Copied Text', currentPage, '[Copy limit reached]');
				Gumby.log('Sending GA event: ' + 'Copied Text', currentPage, '[Copy limit reached]');
			}
			copyOver = 1;
		}
	});
		
} //End gaEventTracking()


function googlePlusCallback(jsonParam) {
	var currentPage = jQuery(document).attr('title');
	if ( jsonParam.state == 'on' ) {
		ga('send', 'event', 'Social', 'Google+ Like', currentPage);
		Gumby.log('Sending GA event: ' + 'Social', 'Google+ Like', currentPage);
	} else if ( jsonParam.state == 'off' ) {
		ga('send', 'event', 'Social', 'Google+ Unlike', currentPage);
		Gumby.log('Sending GA event: ' + 'Social', 'Google+ Unlike', currentPage);
	} else {
		ga('send', 'event', 'Social', 'Google+ [JSON Unavailable]', currentPage);
		Gumby.log('Sending GA event: ' + 'Social', 'Google+ [JSON Unavailable]', currentPage);
	}
}

//Detect and log errors, and fallback fixes
function errorLogAndFallback() {
	
	//Check if Contact Form 7 is active and if the selected form ID exists
	if ( jQuery('.cform-disabled').length ) {
		var currentPage = jQuery(document).attr('title');
		ga('send', 'event', 'Error', 'Contact Form 7 Disabled', currentPage);
		Gumby.warn('Warning: Contact Form 7 is disabled! Reverting to mailto link.');
	} else if ( jQuery('#cform7-container:contains("Not Found")').length > 0 ) {
		jQuery('#cform7-container').text('').append('<li><div class="medium primary btn icon-left entypo icon-mail"><a class="cform-disabled" href="mailto:' + bloginfo['admin_email'] + '?subject=Email%20submission%20from%20' + document.URL + '" target="_blank">Email Us</a></div><!--/button--></li>');
		ga('send', 'event', 'Error', 'Contact Form 7 Not Found', currentPage);
		Gumby.warn('Warning: Contact Form 7 is not found! Reverting to mailto link.');
	}
}

function mmenu() {
	jQuery("#mobilenav").mmenu({
	    //Options
	    searchfield: { //This is for searching through the menu itself (NOT for site search)
	    	add: true,
	    	search: true,
	    	placeholder: 'Search',
	    	noResults: 'No navigation items found.',
	    	showLinksOnly: false //"true" searches only <a> links, "false" includes spans in search results
	    },
	    counters: true, //Display count of sub-menus
	    classes: "mm-light"
	}, {
		//Configuration
	});
	
	jQuery("#mobilecontact").mmenu({
		//Options
	    position: 'right',
	    classes: "mm-light",
	    header: {
			add: true,
			update: true, //Change the header text when navigating to sub-menus
			title: 'Contact Us'
		}
	}, {
		//Configuration
	});
	
	jQuery('.mm-search input').wrap('<form method="get" action="' + bloginfo['home_url'] + '"></form>').attr('name', 's');
	jQuery('.mm-search input').on('keyup', function(){
		if ( jQuery(this).val().length > 0 ) {
			jQuery('.clearsearch').removeClass('hidden');
		} else {
			jQuery('.clearsearch').addClass('hidden');
		}
	});
	jQuery('.mm-panel').append('<div class="clearsearch hidden"><strong class="doasitesearch">Press enter to search the site!</strong><br/><a href="#"><i class="icon-cancel-circled"></i>Reset Search</a></div>');
	jQuery('.clearsearch a').on('click', function(){
		jQuery('.mm-search input').val('').keyup();
		jQuery('.clearsearch').addClass('hidden');
		return false;
	});
} //end mmenu()

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
	
	if ( jQuery('.cform7-phone').length || jQuery('.cform7-bday').length ) {
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
//Add on_sent_ok: "cFormSuccess('EnterTheFormNameHere');" to Additional Settings in WP Admin.
function cFormSuccess(formName){
    //Contact Form 7 Submit Success actions here.
}

//Allows only numerical input on specified inputs. Call this on keyUp? @TODO: Make the selector into oThis and pass that to the function from above.
//The nice thing about this is that it shows the number being taken away so it is more user-friendly than a validation option.
function onlyNumbers() {
	jQuery(".leftcolumn input[type='text']").each(function(){
		this.value = this.value.replace(/[^0-9\.]/g,'');
	});
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
function conditionalJSLoading() {
	//Only load Twitter if Twitter wrapper exists.
	if ( jQuery('#twittercon').length ) {
		jQuery.getScript(bloginfo['template_directory'] + '/js/libs/twitter.js').done(function(){
			twitterFeed();
		}).fail(function(){
			console.log('twitter.js could not be loaded.');
			jQuery('#twittercon').css('border', '1px solid red').addClass('hidden');
		});
	}
	//Only load maskedinput.js library if phone or bday field exists.
	if ( jQuery('.cform7-phone').length || jQuery('.cform7-bday').length ) {
		jQuery.getScript(bloginfo['template_directory'] + '/js/libs/jquery.maskedinput.js').done(function(){
			cFormPreValidator();
		}).fail(function(){
			console.log('jquery.maskedinput.js could not be loaded.');
		});
	} else {
		cFormPreValidator();
	}
	//Only load dataTables library if dataTables table exists.
	if ( jQuery('.dataTables_wrapper').length ) {
		
	jQuery.getScript(bloginfo['template_directory'] + '/js/libs/jquery.dataTables.min.js').done(function(){
			cFormPreValidator();
		}).fail(function(){
			console.log('jquery.dataTables.min.js could not be loaded.');
		});
		Modernizr.load(bloginfo['template_directory'] + '/css/jquery.dataTables.css');
	}
	
	//Load Gumby UI scripts as needed
	//THIS IS STILL IN THE TESTING PHASE!
		//WE NEED TO DETERMINE: Does this work? Is it easier than uncommenting <script> calls in the footer? Is it slower than using links?
	if ( jQuery('.tab-nav').length ) {
		jQuery.getScript(bloginfo['template_directory'] + '/js/libs/ui/gumby.tabs.js').done(function(){
			//Success
		}).fail(function(){
			console.log('gumby.tabs.js could not be loaded.');
		});
	}
} //end conditionalJSLoading()


//Twitter Feed integration
function twitterFeed() {
    if(jQuery('.twitter-feed').length){
        JQTWEET = JQTWEET || {};
        //JQTWEET.search = '#hashtag';
        JQTWEET.user = 'pinckneyhugo';
        JQTWEET.numTweets = 3;
        JQTWEET.template = '<div class="row tweetcon"><div class="four columns"><div class="twittericon">{AVA}</div></div><div class="twelve columns"><div class="twitteruser"><a href="{URL}" target="_blank">@{USER}</a></div><div class="twittertweet">{TEXT} <a class="twitterago" href="{URL}" target="_blank">{AGO}</a></div></div></div>',
        JQTWEET.appendTo = '#twitter_update_list';
        JQTWEET.loadTweets();
    }
} //end twitterFeed()






function mapEnhancements() {
	jQuery('.mapweather').on('click', function(){
		if ( mapInfo['weather'] == 1 ) {
			mapInfo['weather'] = 0;
			console.log('DISABLING WEATHER: ' + mapInfo['weather']);
			jQuery(this).find('i').removeClass('icon-cloud-thunder').addClass('icon-cloud').attr('title', 'Show weather');
		} else {
			mapInfo['weather'] = 1;
			console.log('WEATHER BACK ON GOOD FOR YOU: ' + mapInfo['weather']);
			jQuery(this).find('i').removeClass('icon-cloud').addClass('icon-cloud-thunder').attr('title', 'Hide weather');
		}
		renderMap(mapInfo);
		return false;
	});
	
	jQuery('.detectlocation').on('click', function(){
		if ( typeof mapInfo['detectLoc'] === 'undefined' || mapInfo['detectLoc'][0] == 0 ) {
			console.log('detecting location');
			jQuery('.detectlocation').removeClass('inactive').append('<span class="currentlydetecting">...</span>').attr('title', 'Requesting location...');			
			requestPosition();
		} else {
			console.log('removing detected location');
			jQuery(this).removeClass('success failure').addClass('inactive').attr('title', 'Detect current location').css('color', '');
			mapInfo['detectLoc'] = new Array(0, 0);
			renderMap(mapInfo);
		}
		return false; //Not returning false :C
	});
	
	jQuery('.refreshmap').on('click', function(){
		console.log('refreshing the map');
		renderMap(mapInfo);
		jQuery('.refreshmapicon').addClass('fa-spin');
		jQuery('.refreshmap').addClass('timeout', function(){ //Hide the refresh button for 20 seconds to prevent spamming it
			setTimeout(function(){
				jQuery('.refreshmap').removeClass('timeout');
				jQuery('.refreshmapicon').removeClass('fa-spin');
			}, 5000);
		});
		return false;
	});
}





function requestPosition() {
	console.log('now requesting location... this is where you come in, bro...');
    var nav = null;
    if (nav == null) {
        nav = window.navigator;
    }
    var geoloc = nav.geolocation;
    if (geoloc != null) {
        geoloc.getCurrentPosition(successCallback, errorCallback, {enableHighAccuracy: true});
    }
}

function successCallback(position) {
	console.log('success! got your location data.');
	
	jQuery('.detectlocation').removeClass('failure').addClass('success');
	jQuery('.currentlydetecting').remove();
	
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
		//Some kind of notification here...
	}
	
	jQuery('.detectlocation').css('color', mapInfo['detectLoc']['accColor']).attr('title', 'Accuracy: ' + mapInfo['detectLoc']['accMiles'] + ' miles');
}

function errorCallback(error) {
	console.log('failure. not sure if it was you or me... lets find out...');
    var strMessage = "";
    // Check for known errors
    switch (error.code) {
        case error.PERMISSION_DENIED:
            strMessage = 'Access to your location is turned off. Change your settings to report location data.';
            break;
        case error.POSITION_UNAVAILABLE:
            strMessage = "Data from location services is currently unavailable.";
            break;
        case error.TIMEOUT:
            strMessage = "Location could not be determined within a specified timeout period.";
            break;
        default:
            break;
    }
    console.log(strMessage);
    jQuery('.detectlocation').removeClass('success').addClass('failure inactive').attr('title', strMessage);
}



//Google Maps
function getAllLocations() {
	mapInfo['markers'] = [];
	jQuery('.latlngcon').each(function(i){
		var alat = jQuery(this).find('.lat').text();
		var alng = jQuery(this).find('.lng').text();
		//console.log(i + ': found location! lat: ' + alat + ', lng: ' + alng);
		mapInfo['markers'][i] = [alat, alng];
	});
	renderMap(mapInfo);
}


function renderMap(mapInfo) {
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
		console.log('weather is: ' + mapInfo['traffic']);
		var trafficLayer = new google.maps.TrafficLayer();
		trafficLayer.setMap(map);
	}
	
	//Map weather
	if ( typeof mapInfo['weather'] !== 'undefined' ) {
		console.log('weather is: ' + mapInfo['weather']);
		if ( mapInfo['weather'] == 1 ) {
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
	    }(marker, i);
    }

   
	//Detected Location Marker
	if ( typeof mapInfo['detectLoc'] !== 'undefined' ) {
		if ( mapInfo['detectLoc'][0] != 0 ) { //Detected location is set
			console.log('marking detected location.');
			//Detected location marker
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
			var detectbounds = new google.maps.LatLngBounds();
			detectbounds.extend(phg);
			detectbounds.extend(detectLoc);
			map.fitBounds(detectbounds);
		}
	}

	google.maps.event.trigger(map, "resize");
    map.fitBounds(bounds);
	
}
