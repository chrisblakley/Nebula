window.performance.mark('child_inside_mainjs');
jQuery.noConflict();

/*==========================
 DOM Ready (After nebula.js is loaded)
 ===========================*/

jQuery(function(){
	window.performance.mark('child_dom_ready_start');

	nebula.cacheSelectors();
	supplementalEventTracking();

	window.performance.mark('child_dom_ready_end');
	window.performance.measure('child_dom_ready_functions', 'child_dom_ready_start', 'child_dom_ready_end');
}); //End Document Ready

/*==========================
 Window Load
 ===========================*/

jQuery(window).on('load', function(){
	window.performance.mark('child_window_load_start');

	//Window load functions here

	window.performance.mark('child_window_load_end');
	window.performance.measure('child_window_load_functions', 'child_window_load_start', 'child_window_load_end');
}); //End Window Load

/*==========================
 Window Resize
 ===========================*/

/*
jQuery(window).on('resize', function(){
	nebula.debounce(function(){

	}, 500);
}); //End Window Resize
*/

/*==========================
 Child Functions
 To override a parent function, simply redefine it here.
 ===========================*/

//Child theme event tracking. Do not rename this function!
function supplementalEventTracking(){
	nebula.cacheSelectors();

	if ( nebula.isDoNotTrack() ){
		return false;
	}

	//Simple example:
	//nebula.dom.document.on('click touch tap', '.selector', function(){
	//	ga('send', 'event', 'Category', 'Action', 'Label');
	//});

	//Add your custom event tracking here!
}