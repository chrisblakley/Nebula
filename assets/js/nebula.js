'use strict';

window.performance.mark('(Nebula) Inside nebula.js');
jQuery.noConflict();

/*==========================
 Import Modules
 ===========================*/

//How can we append the '?ver=' + nebula.version.number to these without erroring or causing a race condition with DOM ready? These also need to be available for child themes ASAP. Using the import() function or await causes race conditions, and we cannot concatenate as-is with variables.
import './modules/optimization.js';
import './modules/utilities.js';
import './modules/helpers.js';
import './modules/extensions.js';

/*==========================
 DOM Ready
 ===========================*/

jQuery(function(){
	window.performance.mark('(Nebula) DOM Ready [Start]');

	nebula.cacheSelectors(); //Always do this first
	nebula.addExpressions();
	nebula.initBootstrapFunctions(); //Must be in DOM ready
	nebula.helpers();
	nebula.svgImgs();
	nebula.subnavExpanders();

	//Search
	if ( jQuery('input[type="search"], input[name="s"], [class*="search"]').length || nebula.get('s') ){
		import('./modules/search.js?ver=' + nebula.version.number).then(function(module){
			nebula.initSearchFunctions();
		});
	}

	//Forms
	if ( jQuery('form:not([role="search"]):not(#adminbarsearch), input:not([type="search"]):not([name="s"]):not([type="submit"]), .wpcf7, #nebula-feedback-system').length ){ //If non-search forms/inputs exist
		import('./modules/forms.js?ver=' + nebula.version.number).then(function(module){
			nebula.liveValidator();
			nebula.cf7Functions();
			nebula.cf7LocalStorage();
			nebula.initFeedbackSystem();
		});
	}

	//Interaction
	nebula.animationTriggers();
	nebula.scrollToListeners();

	nebula.visibilityChangeActions();
	nebula.dom.document.on('visibilitychange', function(){
		nebula.visibilityChangeActions();
	});

	//Analytics
	if ( !nebula.isDoNotTrack() ){
		import('./modules/analytics.js?ver=' + nebula.version.number).then(function(module){
			nebula.eventTracking();
		});
	}

	window.performance.mark('(Nebula) DOM Ready [End]');
	window.performance.measure('(Nebula) DOM Ready Functions', '(Nebula) DOM Ready [Start]', '(Nebula) DOM Ready [End]');
});

/*==========================
 Window Load
 ===========================*/

jQuery(window).on('load', function(){
	window.performance.mark('(Nebula) Window Load [Start]');

	nebula.cacheSelectors(); //Just to make sure
	nebula.lazyLoadAssets(); //Move to (or use) requestIdleCallback when Safari supports it
	nebula.errorMitigation();

	//Navigation
	nebula.overflowDetector(); //Move to (or use) requestIdleCallback when Safari supports it?

	//Videos
	if ( jQuery('video, iframe[src*="vimeo"], iframe[src*="youtube"]').length ){
		import('./modules/video.js?ver=' + nebula.version.number).then(function(module){
			nebula.initVideoTracking(); //Move to (or use) requestIdleCallback when Safari supports it?
		});
	}

	//Location
	if ( jQuery('#address-autocomplete').length ){
		import('./modules/location.js?ver=' + nebula.version.number).then(function(module){
			nebula.addressAutocomplete('#address-autocomplete', 'nebulaGlobalAddressAutocomplete');
		});
	}

	//Social
	if ( jQuery('[class*="fb"], [class*="share"]').length ){
		import('./modules/social.js?ver=' + nebula.version.number).then(function(module){
			nebula.facebookSDK();
			nebula.socialSharing();
		});
	}

	nebula.networkConnection();

	nebula.lastWindowWidth = nebula.dom.window.width(); //Prep resize detection (Is this causing a forced reflow?)
	jQuery('a, li, tr').removeClass('hover');
	nebula.dom.html.addClass('loaded');

	nebula.workbox();
	nebula.predictiveCacheListeners(); //Move to (or use) requestIdleCallback when Safari supports it

	nebula.networkAvailable(); //Call it once on load, then listen for changes
	nebula.dom.window.on('offline online', function(){
		nebula.networkAvailable();
	});

	nebula.cookieNotification();

	window.performance.mark('(Nebula) Window Load [End]');
	window.performance.measure('(Nebula) Window Load Functions', '(Nebula) Window Load [Start]', '(Nebula) Window Load [End]');
	window.performance.measure('(Nebula) Window Loaded', 'navigationStart', '(Nebula) Window Load [End]');
	nebula.performanceMetrics();
});

/*==========================
 Window Resize
 ===========================*/

window.addEventListener('resize', function(){
	nebula.debounce(function(){ //Must use debounce here (not throttle) so it always runs after the resize finishes (throttle does not always run at the end)
		if ( typeof nebula.lastWindowWidth !== 'undefined' && nebula.dom.window.width() !== nebula.lastWindowWidth ){ //If the width actually changed
			nebula.lastWindowWidth = nebula.dom.window.width();
		}
	}, 250, 'nebula window resize');
}, {passive: true}); //End Window Resize