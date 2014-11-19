jQuery.noConflict();

jQuery(document).ready(function() {

	//Pull query strings from URL
	queries = new Array();
    var q = document.URL.split('?')[1];
    if ( q != undefined ){
        q = q.split('&');
        for ( var i = 0; i < q.length; i++ ){
            hash = q[i].split('=');
            queries.push(hash[1]);
            queries[hash[0]] = hash[1];
        }
	}

	//Search query strings for the passed parameter
	function GET(query) {
		if ( typeof query === 'undefined' ) {
			return queries;
		}

		if ( typeof queries[query] !== 'undefined' ) {
			return queries[query];
		} else if ( queries.hasOwnProperty(query) ) {
			return query;
		}
		return false;
	}

	if ( GET('killall') || GET('kill') || GET('die') ) {
		throw ' (Manually terminated login.js)';
	}

	jQuery('.forgetmenot, .submit').addClass('clearfix');
	jQuery('#loginform').append('<div class="centertext ipcon"><p>Your IP Address: <span class="theIP">' + clientinfo['remote_addr'] + '</span></p></div>');

	if ( jQuery('#login_error').text().indexOf('password') > -1 ) {
		var userError = jQuery('#login_error strong:nth-of-type(2)').text();
		ga('send', 'event', 'Login Error', 'User: ' + userError, 'From: ' + clientinfo['remote_addr']);
		if ( typeof Gumby != 'undefined' ) { Gumby.log('Sending GA event: ' + 'Login Error', 'User: ' + userError, 'From: ' + clientinfo['remote_addr']); }
	}

	jQuery('#lostpasswordform').submit(function(){
		var resetUser = jQuery('#user_login').val();
		ga('send', 'event', 'Password Reset', 'User: ' + resetUser);
		if ( typeof Gumby != 'undefined' ) { Gumby.log('Sending GA event: ' + 'Password Reset', 'User: ' + resetUser); }
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