window.performance.mark('nebula_inside_nebulajs');
jQuery.noConflict();

/*==========================
 Import Modules
 ===========================*/

import * as regex from './modules/regex.js';
import * as optimization from './modules/optimization.js';
import * as detection from './modules/detection.js';
import * as social from './modules/social.js';
import * as analytics from './modules/analytics.js';
import * as search from './modules/search.js';
import * as forms from './modules/forms.js';
import * as maps from './modules/maps.js';
import * as helpers from './modules/helpers.js';
import * as utilities from './modules/utilities.js';
import * as miscellaneous from './modules/miscellaneous.js'; //Better name...?
import * as video from './modules/video.js';
import * as legacy from './modules/legacy.js';
import * as extensions from './modules/extensions.js';

/*==========================
 DOM Ready
 ===========================*/

jQuery(function(){
	window.performance.mark('nebula_dom_ready_start');

	//Do Stuff
	nebula.example();

	window.performance.mark('nebula_dom_ready_end');
	window.performance.measure('nebula_dom_ready_functions', 'nebula_dom_ready_start', 'nebula_dom_ready_end');
});

/*==========================
 Window Load
 ===========================*/

jQuery(window).on('load', function(){
	window.performance.mark('nebula_window_load_start');

	//Do Stuff

	window.performance.mark('nebula_window_load_end');
	window.performance.measure('nebula_window_load_functions', 'nebula_window_load_start', 'nebula_window_load_end');
	window.performance.measure('nebula_fully_loaded', 'navigationStart', 'nebula_window_load_end');
});

/*==========================
 Window Resize
 ===========================*/

jQuery(window).on('resize', function(){
	nebula.debounce(function(){
		//Do Stuff
	}, 500, 'window resize');
});