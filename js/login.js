jQuery.noConflict();

jQuery(document).ready(function() {	
	
	jQuery('.forgetmenot, .submit').addClass('clearfix');
	jQuery('#loginform').append('<div class="centertext ipcon"><p>Your IP Address: <span class="theIP">' + clientinfo['remote_addr'] + '</span></p></div>');
		
	if ( jQuery('#login_error').text().indexOf('password') > -1 ) {
		var userError = jQuery('#login_error strong:nth-of-type(2)').text();
		ga('send', 'event', 'Login Error', 'User: ' + userError, 'From: ' + clientinfo['remote_addr']);
		Gumby.log('Sending GA event: ' + 'Login Error', 'User: ' + userError, 'From: ' + clientinfo['remote_addr']);
	}
	
	jQuery('#lostpasswordform').submit(function(){
		var resetUser = jQuery('#user_login').val();
		ga('send', 'event', 'Password Reset', 'User: ' + resetUser);
		Gumby.log('Sending GA event: ' + 'Password Reset', 'User: ' + resetUser);
	});
	
	if ( jQuery('.flag').is('*') ) {
		Modernizr.load(bloginfo['template_directory'] + '/css/flags.css');
	}
	
}); //End Document Ready


jQuery(window).on('load', function() {	

	//Don't animate the modal on error/message pages
	if( jQuery('#login_error').length || jQuery('#login .message').length ) {
		jQuery('#login').css('display', 'block');
	} else {
		jQuery('#login').fadeIn(750);
	}
	
}); //End Window Load