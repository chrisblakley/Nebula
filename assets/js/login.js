jQuery.noConflict();

//Document Ready
jQuery(function(){
	//Check for SVG support (without Modernizr)
	if ( typeof SVGRect !== 'undefined' ){
		jQuery('html').addClass('svg');
	}

	jQuery('#lostpasswordform').submit(function(){
		ga('send', 'exception', {'exDescription': '(Security) Password reset for ' + jQuery('#user_login').val(), 'exFatal': false});
	});
}); //End Document Ready