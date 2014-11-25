<?php



//Disable Pingbacks to prevent security issues
add_filter('xmlrpc_methods', disable_pingbacks($methods));
function disable_pingbacks($methods) {
   unset($methods['pingback.ping']);
   return $methods;
};


//Prevent login error messages from giving too much information
add_filter('login_errors', 'nebula_login_errors');
function nebula_login_errors($error) {
    $error = '';
    return $error;
}


//Check for direct access redirects, log them, then redirect without queries.
add_action('init', 'check_direct_access');
function check_direct_access() {
	if ( isset($_GET['directaccess']) || array_key_exists('directaccess', $_GET) ) {
		$attempted = ( $_GET['directaccess'] != '' ) ? $_GET['directaccess'] : 'Unknown' ;
		$data = array(
			'v' => 1,
			'tid' => $GLOBALS['ga'],
			'cid' => gaParseCookie(),
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


