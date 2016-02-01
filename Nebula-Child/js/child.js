jQuery.noConflict();

/*==========================
 DOM Ready (After main.js is loaded)
 ===========================*/

jQuery(document).on('ready', function(){



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
