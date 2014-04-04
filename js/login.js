jQuery.noConflict();

jQuery(document).ready(function() {	
	
	jQuery('.forgetmenot, .submit').addClass('clearfix');
	jQuery('#loginform').append('<div class="centertext ipcon"><p>Your IP Address: <span class="theIP">' + window.userIP + '</span></p></div>');
		
	//Set these CSS properties with JS so login is still possible with JS disabled.
	jQuery('#login').css('display', 'none');
	
}); //End Document Ready


jQuery(window).on('load', function() {	

	//Don't animate the modal on error/message pages
	if( jQuery('#login_error').length || jQuery('#login .message').length ) {
		jQuery('#login').css('display', 'block');
	} else {
		jQuery('#login').fadeIn(750);
	}
	
}); //End Window Load