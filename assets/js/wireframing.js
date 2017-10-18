jQuery.noConflict();

//Document Ready
jQuery(function(){
	jQuery('.component-comment-toggle').on('click', function(){
		jQuery(this).toggleClass('active');
		jQuery(this).parents('.fpo-component-con').find('.component-comment-drawer').slideToggle();
		return false;
	});
}); //End Document Ready

jQuery(window).on('load', function(){
	//Fix issue when already on a "sticky" query that it appends a duplicate (instead of changing)
	setTimeout(function(){
		jQuery('#wp-admin-bar-nebula-prototype-default li a').each(function(){
			var permalink = jQuery(this).attr('href');
			if ( permalink.indexOf('?phase=') > 0 && permalink.indexOf('&phase=') > 0 ){
				permalink = permalink.replace(/(&phase=[a-z]+)/, '');
				jQuery(this).attr('href', permalink);
			}
		});
	}, 1);
});