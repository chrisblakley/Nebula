'use strict';

window.performance.mark('(Nebula) Inside admin.js');
jQuery.noConflict();

/*==========================
 Import Modules
 ===========================*/

import './modules/optimization.js';
import './modules/utilities.js';
import './modules/extensions.js';

/*==========================
 DOM Ready
 ===========================*/

jQuery(async function(){
	window.performance.mark('(Nebula) DOM Ready [Start]');

	nebula.cacheSelectors();

	if ( nebula.screen.base === 'profile' ){ //Only needed on Users profile page
		import('./admin-modules/users.js').then(function(module){
			nebula.userHeadshotFields();
		});
	}

	nebula.initializationStuff();
	nebula.cf7SubmissionsOrganization();

	if ( !jQuery('li#menu-comments').is(':visible') ){
		jQuery('#dashboard_right_now .main').append('Comments are disabled <small>(via <a href="themes.php?page=nebula_options&tab=functions&option=comments">Nebula Options</a>)</small>.');
	}

	//If Nebula Options Page
	if ( nebula.screen.base === 'appearance_page_nebula_options' ){
		await import('./modules/search.js'); //Only really need the keywordFilter from here...
		await import('./modules/forms.js'); //Only really need the liveValidator from here...
		await import('./admin-modules/options.js');
		nebula.optionsInit();
	}

	//Remove Sass render trigger query
	if ( nebula.get('sass') && !nebula.get('persistent') ){
		window.history.replaceState({}, document.title, nebula.removeQueryParameter('sass', window.location.href));
	}

	window.performance.mark('(Nebula) DOM Ready [End]');
	window.performance.measure('(Nebula) DOM Ready Functions', '(Nebula) DOM Ready [Start]', '(Nebula) DOM Ready [End]');
});

/*==========================
 Window Load
 ===========================*/

jQuery(window).on('load', function(){
	window.performance.mark('(Nebula) Window Load [Start]');

	nebula.cacheSelectors();

	if ( nebula.screen.base === 'post' || jQuery('#sass-cooldown, #post textarea').length ){
		import('./admin-modules/helpers.js').then(function(module){
			jQuery('#post textarea').allowTabChar();
			nebula.sassCooldown(); //Needed on every page
			nebula.uniqueSlugChecker(); //Only needed on edit post pages
		});
	}

	if ( nebula.screen.base === 'dashboard' ){ //Only needed on Dashboard page
		import('./admin-modules/dashboard.js').then(function(module){
			nebula.developerMetaboxes();
		});
	}

	if ( nebula.screen.base === 'nav-menus' ){
		jQuery('.menu-delete').html('<i class="fa-solid fa-triangle-exclamation"></i> Delete the <strong>Entire</strong> Menu'); //Update the Delete Menu text to be more explicit
	}

	//Force disable the WordPress core fullscreen editor for all users.
	try {
		if ( wp.data.select('core/edit-post').isFeatureActive('fullscreenMode') ){
			wp.data.dispatch('core/edit-post').toggleFeature('fullscreenMode');
		}
	} catch {
		//Ignore errors
	}

	//Remove this once QM allows sortable Timings table
	if ( jQuery('#qm-timing').length ){
		import('./modules/helpers.js').then(function(module){ //Front-end helpers JS
			nebula.qmSortableHelper(); //Temporary QM helper.
		});
	}

	window.performance.mark('(Nebula) Window Load [End]');
	window.performance.measure('(Nebula) Window Load Functions', '(Nebula) Window Load [Start]', '(Nebula) Window Load [End]');
	window.performance.measure('(Nebula) Window Loaded', 'navigationStart', '(Nebula) Window Load [End]');
	nebula.performanceMetrics();
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

//Re-organize the CF7 submission details screen
nebula.cf7SubmissionsOrganization = function(){
	//Submission listing page
	if ( jQuery('.post-type-nebula_cf7_submits .wp-list-table').length ){
		jQuery('.cf7-note-caution').each(function(){
			jQuery(this).parents('tr').addClass('caution-row');
		});

		jQuery('.cf7-note-invalid').each(function(){
			jQuery(this).parents('tr').addClass('invalid-row');
		});

		jQuery('.cf7-note-failed').each(function(){
			jQuery(this).parents('tr').addClass('failed-row');
		});

		jQuery('.cf7-note-internal').each(function(){
			jQuery(this).parents('tr').addClass('internal-row');
		});
	}

	//Submission details page
	if ( jQuery('.post-type-nebula_cf7_submits').length ){
		jQuery('#title').attr('disabled', 'disabled'); //Prevent the submission title from being modified

		jQuery('#save-post').val('Save').insertBefore('#publish').css('float', 'right');
		jQuery('#publish').remove();
		jQuery('#minor-publishing-actions').remove();
		jQuery('#save-action .spinner').remove();
	}
};