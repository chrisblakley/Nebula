jQuery.noConflict();

//Document Ready
jQuery(function(){
	//Check for SVG support (without Modernizr)
	if ( typeof SVGRect !== 'undefined' ){
		jQuery('html').addClass('svg');
	}

	jQuery('#lostpasswordform').submit(function(){
		var resetUser = jQuery('#user_login').val();
		ga('send', 'event', 'Password Reset', 'User: ' + resetUser);
	});
}); //End Document Ready