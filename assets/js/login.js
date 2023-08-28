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
		gtag('event', 'password_reset', {
			event_category: 'Login',
			event_action: 'Password Reset',
			event_label: 'Password reset for ' + jQuery('#user_login').val(),
			username: jQuery('#user_login').val()
		});
	});
});