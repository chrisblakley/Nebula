<?php



//Disable Pingbacks to prevent security issues
add_filter('xmlrpc_methods', disable_pingbacks($methods));
function disable_pingbacks($methods) {
   unset($methods['pingback.ping']);
   return $methods;
};


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
		$data = array(
			'v' => $_GLOBALS['ga_v'],
			'tid' => $GLOBALS['ga'],
			'cid' => $_GLOBALS['ga_cid'],
			't' => 'event',
			'ec' => 'Login Error', //Category (Required)
			'ea' => 'Attempted User: ' . $incorrect_username, //Action (Required)
			'el' => 'IP: ' . $_SERVER['REMOTE_ADDR'] //Label
		);
		gaSendData($data);
	} else {
		echo 'some other error';
		$data = array(
			'v' => $_GLOBALS['ga_v'],
			'tid' => $GLOBALS['ga'],
			'cid' => $_GLOBALS['ga_cid'],
			't' => 'event',
			'ec' => 'Login Error', //Category (Required)
			'ea' => strip_tags($error), //Action (Required)
			'el' => 'IP: ' . $_SERVER['REMOTE_ADDR'] //Label
		);
		gaSendData($data);
	}

    $error = 'Login Error.';
    return $error;
}


//Check for direct access redirects, log them, then redirect without queries.
add_action('init', 'check_direct_access');
function check_direct_access() {
	if ( isset($_GET['directaccess']) || array_key_exists('directaccess', $_GET) ) {
		$attempted = ( $_GET['directaccess'] != '' ) ? $_GET['directaccess'] : 'Unknown' ;
		$data = array(
			'v' => $_GLOBALS['ga_v'],
			'tid' => $GLOBALS['ga'],
			'cid' => $_GLOBALS['ga_cid'],
			't' => 'event',
			'ec' => 'Direct Template Access', //Category
			'ea' => $attempted, //Action
			'el' => '' //Label
		);
		gaSendData($data);
		header('Location: ' . home_url('/'));
	}
}


//Remove Wordpress version info from head and feeds
add_filter('the_generator', 'complete_version_removal');
function complete_version_removal() {
	return '';
}


//Check referrer in order to comment
add_action('check_comment_flood', 'check_referrer');
function check_referrer() {
	if ( !isset($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER'] == '' ) {
		wp_die('Please do not access this file directly.');
	}
}


