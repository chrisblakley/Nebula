jQuery.noConflict();
jQuery(document).on('ready', function(){

	jQuery('.component-comment-toggle').on('click tap touch', function(){
		jQuery(this).toggleClass('active');
		jQuery(this).parents('.fpo-component-con').find('.component-comment-drawer').slideToggle();
		return false;
	});

}); //End Document Ready