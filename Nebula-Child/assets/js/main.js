window.performance.mark('(Child) Inside main.js');
jQuery.noConflict();

/*==========================
 DOM Ready (After nebula.js is loaded)
 ===========================*/

jQuery(function(){
	window.performance.mark('(Child) DOM Ready [Start]');

	nebula.cacheSelectors();
	supplementalEventTracking();

	window.performance.mark('(Child) DOM Ready [End]');
	window.performance.measure('(Child) DOM Ready Functions', '(Child) DOM Ready [Start]', '(Child) DOM Ready [End]');
}); //End Document Ready

/*==========================
 Window Load
 ===========================*/

jQuery(window).on('load', function(){
	window.performance.mark('(Child) Window Load [Start]');

	//Window load functions here

	window.performance.mark('(Child) Window Load [End]');
	window.performance.measure('(Child) Window Load Functions', '(Child) Window Load [Start]', '(Child) Window Load [End]');
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