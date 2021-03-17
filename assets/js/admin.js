'use strict';

window.performance.mark('(Nebula) Inside admin.js');
jQuery.noConflict();

/*==========================
 Import Modules
 ===========================*/

import './modules/utilities.js';
import './modules/extensions.js';
import './modules/search.js';
import './modules/optimization.js';
import './modules/forms.js';
import './admin-modules/helpers.js'; //Admin module

/*==========================
 DOM Ready
 ===========================*/

jQuery(function(){
	nebula.cacheSelectors();

	if ( nebula.screen.base === 'profile' ){ //Only needed on Users' profile page
		import('./admin-modules/users.js').then(function(module){
			nebula.userHeadshotFields();
		});
	}

	nebula.initializationStuff();

	jQuery('#post textarea').allowTabChar();

	if ( !jQuery('li#menu-comments').is(':visible') ){
		jQuery('#dashboard_right_now .main').append('Comments are disabled <small>(via <a href="themes.php?page=nebula_options&tab=functions&option=comments">Nebula Options</a>)</small>.');
	}

	//If Nebula Options Page
	if ( nebula.screen.base === 'appearance_page_nebula_options' ){
		import('./admin-modules/options.js').then(function(module){
			nebula.optionsInit();
		});
	}

	//Remove Sass render trigger query
	if ( nebula.get('sass') && !nebula.get('persistent') ){
		window.history.replaceState({}, document.title, nebula.removeQueryParameter('sass', window.location.href));
	}
});

/*==========================
 Window Load
 ===========================*/

jQuery(window).on('load', function(){
	nebula.cacheSelectors();
	nebula.uniqueSlugChecker();

	if ( nebula.screen.base === 'dashboard' ){ //Only needed on Dashboard page
		import('./admin-modules/dashboard.js').then(function(module){
			nebula.developerMetaboxes();
		});
	}

	//Force disable the WordPress core fullscreen editor for all users.
	try {
		if ( wp.data.select('core/edit-post').isFeatureActive('fullscreenMode') ){
			wp.data.dispatch('core/edit-post').toggleFeature('fullscreenMode');
		}
	} catch {
		//Ignore errors
	}
});

//Initialization alerts
nebula.initializationStuff = function(){
	//Initialize confirm dialog.
	jQuery('#run-nebula-initialization').on('click', function(){
		if ( !confirm('This will reset some WordPress settings, all Nebula options, and reset the homepage content! Are you sure you want to initialize?') ){
			return false;
		}
	});

	//Remove query string once initialized.
	if ( window.location.href.includes('?nebula-initialization=true') ){
		var cleanURL = window.location.href.split('?');
		history.replaceState(null, document.title, cleanURL[0]);
	}
};