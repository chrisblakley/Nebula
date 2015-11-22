jQuery.noConflict();

/*==========================
 DOM Ready (After main.js is loaded)
 ===========================*/

jQuery(document).on('ready', function(){

	//Cache common global selectors. Defined in main.js, so these could be deleted here.
	thisPage = {
        window: jQuery(window),
        document: jQuery(document),
        html: jQuery('html'),
        body: jQuery('body')
    }

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