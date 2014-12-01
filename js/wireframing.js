jQuery.noConflict();

jQuery(document).ready(function() {

	jQuery('.component-comment-toggle').on('click', function(){
		jQuery(this).toggleClass('active');
		jQuery(this).parents('.fpo-component-con').find('.component-comment-drawer').slideToggle();
		return false;
	});

}); //End Document Ready


jQuery(window).on('load', function() {



}); //End Window Load