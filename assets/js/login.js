jQuery.noConflict();

//Document Ready
jQuery(function(){
	//Check for SVG support (without Modernizr)
	if ( typeof SVGRect !== 'undefined' ){
		jQuery('html').addClass('svg');
	}

	jQuery('#lostpasswordform').submit(function(){
		ga('send', 'event', 'Security Precaution', 'Password Reset', jQuery('#user_login').val());
	});
}); //End Document Ready