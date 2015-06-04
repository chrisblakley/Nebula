<?php

//Log template direct access attempts
add_action('wp_loaded', 'nebula_log_direct_access_attempts');
function nebula_log_direct_access_attempts(){
	if ( array_key_exists('ndaat', $_GET) ) {
		ga_send_event('Security Precaution', 'Direct Template Access Prevention', 'Template: ' . $_GET['ndaat']);
		header('Location: ' . home_url('/'));
		die('Error 403: Forbidden.');
	}
}

//Prevent known bot/brute-force query strings.
//This is less for security and more for preventing garbage data in Google Analytics reports.
add_action('wp_loaded', 'nebula_prevent_bad_query_strings');
function nebula_prevent_bad_query_strings(){
	if ( array_key_exists('modTest', $_GET) ) {
		header("HTTP/1.1 403 Unauthorized");
		die('Error 403: Forbidden.');
	}
}

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
//Traffic will be sent a 403 Forbidden error and never be able to see the site!
//Be sure to enable Bot Filtering in your Google Analytics account (GA Admin > View Settings > Bot Filtering).
//Sometimes spambots target sites without event visiting. Discovering these and filtering them using GA is important too!
//Learn more: http://gearside.com/stop-spambots-like-semalt-buttons-website-darodar-others/
add_action('wp_loaded', 'nebula_spambot_prevention');
function nebula_spambot_prevention(){
	$cached_spambots = get_template_directory() . '/includes/cache/common_referral_spambots.txt';

	if ( nebula_need_updated_cache($cached_spambots) ) {
		$common_referral_spambots = file_get_contents('https://gist.githubusercontent.com/chrisblakley/e31a07380131e726d4b5/raw/common_referral_spambots.txt');
		$cached_spambots_static = fopen($cached_spambots, 'w');
		fwrite($cached_spambots_static, $common_referral_spambots);
		fclose($cached_spambots_static);
	} else {
		//This file is updated automatically, but it doesn't hurt to replace it with the txt file here: http://gearside.com/common-referral-spambots/
		$common_referral_spambots = file_get_contents(realpath(dirname(__FILE__) . '/..') . '/includes/cache/common_referral_spambots.txt');
	}

	if ( strlen($common_referral_spambots) > 0 ) {
		$GLOBALS['spambot_domains'] = array();
		foreach(explode("\n", $common_referral_spambots) as $line) {
			$GLOBALS['spambot_domains'][] = $line;
		}

		if ( count($GLOBALS['spambot_domains']) > 1 ) {
			if ( isset($_SERVER['HTTP_REFERER']) && contains(strtolower($_SERVER['HTTP_REFERER']), $GLOBALS['spambot_domains']) ) {
				ga_send_event('Security Precaution', 'Spambot Prevention', 'Referring Domain: ' . $_SERVER['HTTP_REFERER'] . ' (Bot IP: ' . $_SERVER['REMOTE_ADDR'] . ')');
				header('HTTP/1.1 403 Forbidden');
				die;
			}

			if ( isset($_SERVER['REMOTE_HOST']) && contains(strtolower($_SERVER['REMOTE_HOST']), $GLOBALS['spambot_domains']) ) {
				ga_send_event('Security Precaution', 'Spambot Prevention', 'Hostname: ' . $_SERVER['REMOTE_HOST'] . ' (Bot IP: ' . $_SERVER['REMOTE_ADDR'] . ')');
				header('HTTP/1.1 403 Forbidden');
				die;
			}

			if ( isset($_SERVER['SERVER_NAME']) && contains(strtolower($_SERVER['SERVER_NAME']), $GLOBALS['spambot_domains']) ) {
				ga_send_event('Security Precaution', 'Spambot Prevention', 'Server Name: ' . $_SERVER['SERVER_NAME'] . ' (Bot IP: ' . $_SERVER['REMOTE_ADDR'] . ')');
				header('HTTP/1.1 403 Forbidden');
				die;
			}
		} else {
			ga_send_event('Security Precaution', 'Error', 'common_referral_spambots.txt has no entries!');
		}

		//Use this to generate a regex string of common referral spambots (or a custom passes array of strings). Unfortunately Google Analytics limits filters to 255 characters.
		function nebula_spambot_regex($domains=null){
			$domains = ( $domains ) ? $domains : $GLOBALS['spambot_domains'];
			$domains = str_replace(array('.', '-'), array('\.', '\-'), $domains);
			return implode("|", $domains);
		}
	} else {
		ga_send_event('Security Precaution', 'Error', 'common_referral_spambots.txt is not a file!');
	}
}

//Valid Hostname Regex
function nebula_valid_hostname_regex($domains=null){
	$domains = ( $domains ) ? $domains : array(nebula_url_components('domain'));
	$settingsdomains = ( get_option('nebula_hostnames') ) ? explode(',', get_option('nebula_hostnames')) : array(nebula_url_components('domain'));
	$fulldomains = array_merge($domains, $settingsdomains, array('translate.googleusercontent.com', 'webcache.googleusercontent.com', 'youtube.com', 'paypal.com'));
	$fulldomains = str_replace(array(' ', '.', '-'), array('', '\.', '\-'), $fulldomains);
	$fulldomains = array_unique($fulldomains);
	return implode("|", $fulldomains);
}