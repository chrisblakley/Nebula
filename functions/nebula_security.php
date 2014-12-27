<?php



//Disable Pingbacks to prevent security issues
add_filter('xmlrpc_methods', disable_pingbacks($methods));
add_filter('wp_xmlrpc_server_class', disable_pingbacks($methods));
function disable_pingbacks($methods) {
   //unset($methods['pingback.ping']);
   //return $methods;
   return false;
};

//Remove xpingback header
add_filter('wp_headers', 'remove_x_pingback');
function remove_x_pingback($headers) {
    unset($headers['X-Pingback']);
    return $headers;
}

//Prevent login error messages from giving too much information
/*
	@TODO "Security" 4: It is advised to create a Custom Alert in Google Analytics with the following settings:
	Name: Possible Brute Force Attack
	Check both send an email and send a text if possible.
	Period: Day
	Alert Conditions:
		This applies to: Event Action, Contains, Attempted User
		Alert me when: Total Events, Is greater than, 5 //May need to adjust this number to account for more actual users (depending on how many true logins are expected per day).
*/
add_filter('login_errors', 'nebula_login_errors');
function nebula_login_errors($error) {

	$incorrect_username = '';
	if ( contains($error, array('The password you entered for the username')) ) {
		$incorrect_username_start = strpos($error, "for the username ")+17;
		$incorrect_username_stop = strpos($error, " is incorrect")-$incorrect_username_start;
		$incorrect_username = strip_tags(substr($error, $incorrect_username_start, $incorrect_username_stop));
	}

	if ( $incorrect_username != '' ) {
		ga_send_event('Login Error', 'Attempted User: ' . $incorrect_username, 'IP: ' . $_SERVER['REMOTE_ADDR']);
	} else {
		ga_send_event('Login Error', strip_tags($error), 'IP: ' . $_SERVER['REMOTE_ADDR']);
	}

    $error = 'Login Error.';
    return $error;
}


//Disable the file editor
define('DISALLOW_FILE_EDIT', true);

//Remove Wordpress version info from head and feeds
add_filter('the_generator', 'complete_version_removal');
function complete_version_removal() {
	return '';
}


//Remove WordPress version from any enqueued scripts
add_filter('style_loader_src', 'at_remove_wp_ver_css_js', 9999);
add_filter('script_loader_src', 'at_remove_wp_ver_css_js', 9999);
function at_remove_wp_ver_css_js($src) {
    if ( strpos($src, 'ver=') )
        $src = remove_query_arg('ver', $src);
    return $src;
}


//Check referrer in order to comment
add_action('check_comment_flood', 'check_referrer');
function check_referrer() {
	if ( !isset($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER'] == '' ) {
		wp_die('Please do not access this file directly.');
	}
}


//Check referrer for known spambots
//Be absolutely sure before adding a domain to the array in this function. Traffic will be sent a 403 Forbidden error and never be able to see the site!
add_action('init', 'nebula_spambot_prevention');
function nebula_spambot_prevention(){
	$known_spambots = array('semalt.', 'ilovevitaly.', 'darodar.', 'econom.', 'makemoneyonline.', 'buttons-for-website.', 'myftpupload.'); //The dots allow for any TLD to trigger with fewer false-positives.
	if ( isset($_SERVER['HTTP_REFERER']) && contains($_SERVER['HTTP_REFERER'], $known_spambots) ) {
		ga_send_event('Security Measure', 'Spambot Prevention', 'Referring Domain: ' . $_SERVER['HTTP_REFERER'] . ' (Bot IP: ' . $_SERVER['REMOTE_ADDR'] . ')');
		header('HTTP/1.1 403 Forbidden');
		die;
	}
}
