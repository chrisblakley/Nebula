'use strict';

jQuery.noConflict();

/*==========================
 DOM Ready
 ===========================*/

jQuery(function(){
	//Check for SVG support
	if ( typeof SVGRect !== 'undefined' ){
		jQuery('html').addClass('svg');
	}

	jQuery(document).on('submit', '#lostpasswordform', function(){
		ga('send', 'event', 'Login', 'Password Reset', 'Password reset for ' + jQuery('#user_login').val());
	});
});