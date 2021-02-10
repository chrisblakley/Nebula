'use strict';

window.performance.mark('(Nebula) Inside nebula.js');
jQuery.noConflict();

/*==========================
 Import Modules
 ===========================*/

import './modules/optimization.js';
import './modules/utilities.js';
import './modules/helpers.js';
import './modules/analytics.js';
import './modules/forms.js';
import './modules/search.js';
import './modules/social.js';
import './modules/video.js';
import './modules/location.js';
import './modules/legacy.js';
import './modules/extensions.js';

/*==========================
 DOM Ready
 ===========================*/

jQuery(function(){
	window.performance.mark('(Nebula) DOM Ready [Start]');

	//Utilities
	nebula.cacheSelectors();
	nebula.initBootstrapFunctions(); //Must be in DOM ready
	nebula.helpers();
	nebula.svgImgs();
	nebula.errorMitigation();

	//Navigation
	nebula.subnavExpanders();
	nebula.menuSearchReplacement();

	//Search
	nebula.singleResultDrawer();
	nebula.pageSuggestion();

	//Forms
	nebula.liveValidator();
	nebula.cf7Functions();
	nebula.cf7LocalStorage();

	//Interaction
	nebula.socialSharing();
	nebula.animationTriggers();
	nebula.scrollToListeners();

	nebula.visibilityChangeActions();
	nebula.dom.document.on('visibilitychange', function(){
		nebula.visibilityChangeActions();
	});

	nebula.eventTracking();

	window.performance.mark('(Nebula) DOM Ready [End]');
	window.performance.measure('(Nebula) DOM Ready Functions', '(Nebula) DOM Ready [Start]', '(Nebula) DOM Ready [End]');
});

/*==========================
 Window Load
 ===========================*/

jQuery(window).on('load', function(){
	window.performance.mark('(Nebula) Window Load [Start]');

	nebula.cacheSelectors();
	nebula.performanceMetrics();
	nebula.lazyLoadAssets(); //Move to (or use) requestIdleCallback when Safari supports it
	nebula.initVideoTracking(); //Move to (or use) requestIdleCallback when Safari supports it?

	//Navigation
	nebula.overflowDetector(); //Move to (or use) requestIdleCallback when Safari supports it?

	//Search (several of these could use requestIdleCallback when Safari supports it)
	nebula.wpSearchInput();
	nebula.mobileSearchPlaceholder();
	nebula.autocompleteSearchListeners();
	nebula.searchValidator();
	nebula.searchTermHighlighter(); //Move to (or use) requestIdleCallback when Safari supports it? Already is requesting animation frame

	//Forms
	nebula.addressAutocomplete('#address-autocomplete', 'nebulaGlobalAddressAutocomplete');

	nebula.facebookSDK();

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
});

/*==========================
 Window Resize
 ===========================*/

window.addEventListener('resize', function(){
	nebula.debounce(function(){ //Must use debounce here (not throttle) so it always runs after the resize finishes (throttle does not always run at the end)
		if ( typeof nebula.lastWindowWidth !== 'undefined' && nebula.dom.window.width() !== nebula.lastWindowWidth ){ //If the width actually changed
			nebula.lastWindowWidth = nebula.dom.window.width();
			nebula.mobileSearchPlaceholder();
			nebula.initMmenu(); //If Mmenu has not been initialized, it may need to be if the screen size has reduced
		}
	}, 250, 'window resize');
}, {passive: true}); //End Window Resize