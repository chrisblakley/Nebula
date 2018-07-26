jQuery.noConflict();

//Prevent child theme events from sending before the pageview. Do not add custom event tracking here- add it where noted below!
jQuery(document).on('nebula_event_tracking', function(){
	supplementalEventTracking();
});

/*==========================
 DOM Ready (After nebula.js is loaded)
 ===========================*/

jQuery(function(){
	cacheSelectors();
}); //End Document Ready


/*==========================
 Window Load
 ===========================*/

jQuery(window).on('load', function(){

}); //End Window Load


/*==========================
 Window Resize
 ===========================*/

jQuery(window).on('resize', function(){
	debounce(function(){

	}, 500);
}); //End Window Resize



/*==========================
 Child Functions
 To override a parent function, simply redefine it here.
 ===========================*/

//Child theme event tracking. Do not rename this function!
function supplementalEventTracking(){
	cacheSelectors();

	if ( nebula.user.dnt ){
		return false;
	}

	//Simple example:
	//nebula.dom.document.on('click touch tap', '.selector', function(){
	//	ga('send', 'event', 'Category', 'Action', 'Label');
	//});

	//Add your custom event tracking here!
}