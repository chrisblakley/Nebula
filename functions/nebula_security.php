<?php

//Log template direct access attempts
add_action('wp_loaded', 'nebula_log_direct_access_attempts');
function nebula_log_direct_access_attempts(){
	if ( array_key_exists('ndaat', $_GET) ){
		ga_send_event('Security Precaution', 'Direct Template Access Prevention', 'Template: ' . $_GET['ndaat']);
		header('Location: ' . home_url('/'));
		die('Error 403: Forbidden.');
	}
}

//Prevent known bot/brute-force query strings.
//This is less for security and more for preventing garbage data in Google Analytics reports.
add_action('wp_loaded', 'nebula_prevent_bad_query_strings');
function nebula_prevent_bad_query_strings(){
	if ( array_key_exists('modTest', $_GET) ){
		header("HTTP/1.1 403 Unauthorized");
		die('Error 403: Forbidden.');
	}
}

//Disable Pingbacks to prevent security issues
//Disable X-Pingback HTTP Header.
add_filter('wp_headers', 'nebula_remove_x_pingback', 11, 2);
function nebula_remove_x_pingback($headers){
	if ( isset($headers['X-Pingback']) ){
		unset($headers['X-Pingback']);
	}
	return $headers;
}

//Disable XMLRPC by hijacking and blocking the option.
add_filter('pre_option_enable_xmlrpc', 'nebula_disable_xmlrpc');
function nebula_disable_xmlrpc($state){
	return false; //To leave XMLRPC intact and drop just Pingback: return $state;
}

//Remove rsd_link from filters (<link rel="EditURI" />).
add_action('wp', 'nebula_remove_rsd_link', 9);
function nebula_remove_rsd_link(){
	remove_action('wp_head', 'rsd_link');
}

//Hijack pingback_url for get_bloginfo (<link rel="pingback" />).
add_filter('bloginfo_url', 'nebula_hijack_pingback_url', 11, 2);
function nebula_hijack_pingback_url($output, $property){
	return ( $property == 'pingback_url' )? null : $output;
}



/*
	@TODO "Security" 4: It is advised to create a Custom Alert in Google Analytics with the following settings:
	Name: Possible Brute Force Attack
	Check both send an email and send a text if possible.
	Period: Day
	Alert Conditions:
		This applies to: Event Action, Contains, Attempted User
		Alert me when: Total Events, Is greater than, 5 //May need to adjust this number to account for more actual users (depending on how many true logins are expected per day).
*/
//Prevent login error messages from giving too much information
add_filter('login_errors', 'nebula_login_errors');
function nebula_login_errors($error){
	if ( !nebula_is_bot() ){
		$incorrect_username = '';
		if ( contains($error, array('The password you entered for the username')) ){
			$incorrect_username_start = strpos($error, "for the username ")+17;
			$incorrect_username_stop = strpos($error, " is incorrect")-$incorrect_username_start;
			$incorrect_username = strip_tags(substr($error, $incorrect_username_start, $incorrect_username_stop));
		}

		if ( $incorrect_username != '' ){
			ga_send_event('Login Error', 'Attempted User: ' . $incorrect_username, 'IP: ' . $_SERVER['REMOTE_ADDR']);
		} else {
			ga_send_event('Login Error', strip_tags($error), 'IP: ' . $_SERVER['REMOTE_ADDR']);
		}

	    $error = 'Login Error.';
	    return $error;
    }
}

//Disable the file editor
define('DISALLOW_FILE_EDIT', true);

//Remove Wordpress version info from head and feeds
add_filter('the_generator', 'complete_version_removal');
function complete_version_removal(){
	return '';
}

//Remove WordPress version from any enqueued scripts
add_filter('style_loader_src', 'at_remove_wp_ver_css_js', 9999);
add_filter('script_loader_src', 'at_remove_wp_ver_css_js', 9999);
function at_remove_wp_ver_css_js($src){
    if ( strpos($src, 'ver=') )
        $src = remove_query_arg('ver', $src);
    return $src;
}

//Check referrer in order to comment
add_action('check_comment_flood', 'check_referrer');
function check_referrer(){
	if ( !isset($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER'] == '' ){
		wp_die('Please do not access this file directly.');
	}
}

//Track Notable Bots
add_action('wp_footer', 'track_notable_bots');
function track_notable_bots(){
	//Google Page Speed
	if ( strpos($_SERVER['HTTP_USER_AGENT'], 'Google Page Speed') !== false ){
		$protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'https://' : 'http://';
		$currentURL = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		if ( strpos($currentURL, ".js") !== false ){
			exit();
		}
		global $post;
		ga_send_event('Notable Bot Visit', 'Google Page Speed', get_the_title($post->ID), null, 0, $custom_dimension);
	}

	//Internet Archive Wayback Machine
	if ( strpos($_SERVER['HTTP_USER_AGENT'], 'archive.org_bot') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'Wayback Save Page') !== false ){
		global $post;
		ga_send_event('Notable Bot Visit', 'Internet Archive Wayback Machine', get_the_title($post->ID), null, 0);
	}
}

//Check referrer for known spambots and blacklisted domains
//Traffic will be sent a 403 Forbidden error and never be able to see the site.
//Be sure to enable Bot Filtering in your Google Analytics account (GA Admin > View Settings > Bot Filtering).
//Sometimes spambots target sites without actually visiting. Discovering these and filtering them using GA is important too!
//Learn more: http://gearside.com/stop-spambots-like-semalt-buttons-website-darodar-others/
add_action('wp_loaded', 'nebula_domain_prevention');
function nebula_domain_prevention(){
	$domain_blacklist_json_file = get_template_directory() . '/includes/data/domain_blacklist.txt';
	$domain_blacklist = get_transient('nebula_domain_blacklist');
	if ( empty($domain_blacklist) || is_debug() ){
		$domain_blacklist = @file_get_contents('https://raw.githubusercontent.com/piwik/referrer-spam-blacklist/master/spammers.txt'); //@TODO "Nebula" 0: Consider using: FILE_SKIP_EMPTY_LINES (works with file() dunno about file_get_contents())
		if ( empty($domain_blacklist) ){
			$domain_blacklist = @file_get_contents('https://raw.githubusercontent.com/chrisblakley/Nebula/master/includes/data/domain_blacklist.txt'); //In case piwik is not available (or changes).
		}

		if ( !empty($domain_blacklist) ){
			if ( is_writable(get_template_directory()) ){
				file_put_contents($domain_blacklist_json_file, $domain_blacklist); //Store it locally.
			}
			set_transient('nebula_domain_blacklist', $domain_blacklist, 60*60); //1 hour cache
		} else {
			$domain_blacklist = file_get_contents($domain_blacklist_json_file);
		}
	}

	if ( !empty($domain_blacklist) ){
		$GLOBALS['domain_blacklist'] = array();
		foreach( explode("\n", $domain_blacklist) as $line ){ //@TODO "Nebula" 0: continue; if empty line.
			$GLOBALS['domain_blacklist'][] = $line;
		}

		//Additional blacklisted domains
		$additional_blacklisted_domains = array(
			//'secureserver.net',
		);
		$GLOBALS['domain_blacklist'] = array_merge($GLOBALS['domain_blacklist'], $additional_blacklisted_domains);

		if ( count($GLOBALS['domain_blacklist']) > 1 ){
			if ( isset($_SERVER['HTTP_REFERER']) && contains(strtolower($_SERVER['HTTP_REFERER']), $GLOBALS['domain_blacklist']) ){
				ga_send_event('Security Precaution', 'Blacklisted Domain Prevented', 'Referring Domain: ' . $_SERVER['HTTP_REFERER'] . ' (IP: ' . $_SERVER['REMOTE_ADDR'] . ')');
				header('HTTP/1.1 403 Forbidden');
				die;
			}

			if ( isset($_SERVER['REMOTE_HOST']) && contains(strtolower($_SERVER['REMOTE_HOST']), $GLOBALS['domain_blacklist']) ){
				ga_send_event('Security Precaution', 'Blacklisted Domain Prevented', 'Hostname: ' . $_SERVER['REMOTE_HOST'] . ' (IP: ' . $_SERVER['REMOTE_ADDR'] . ')');
				header('HTTP/1.1 403 Forbidden');
				die;
			}

			if ( isset($_SERVER['SERVER_NAME']) && contains(strtolower($_SERVER['SERVER_NAME']), $GLOBALS['domain_blacklist']) ){
				ga_send_event('Security Precaution', 'Blacklisted Domain Prevented', 'Server Name: ' . $_SERVER['SERVER_NAME'] . ' (IP: ' . $_SERVER['REMOTE_ADDR'] . ')');
				header('HTTP/1.1 403 Forbidden');
				die;
			}

			if ( isset($_SERVER['REMOTE_ADDR']) && contains(strtolower(gethostbyaddr($_SERVER['REMOTE_ADDR'])), $GLOBALS['domain_blacklist']) ){
				ga_send_event('Security Precaution', 'Blacklisted Domain Prevented', 'Network Hostname: ' . $_SERVER['SERVER_NAME'] . ' (IP: ' . $_SERVER['REMOTE_ADDR'] . ')');
				header('HTTP/1.1 403 Forbidden');
				die;
			}
		} else {
			ga_send_event('Security Precaution', 'Error', 'spammers.txt has no entries!');
		}

		//Use this to generate a regex string of common referral spambots (or a custom passes array of strings). Unfortunately Google Analytics limits filters to 255 characters.
		function nebula_spambot_regex($domains=null){
			$domains = ( $domains )? $domains : $GLOBALS['domain_blacklist'];
			$domains = str_replace(array('.', '-'), array('\.', '\-'), $domains);
			return implode("|", $domains);
		}
	} else {
		ga_send_event('Security Precaution', 'Error', 'spammers.txt was not available!');
	}
}

//Valid Hostname Regex
function nebula_valid_hostname_regex($domains=null){
	$domains = ( $domains )? $domains : array(nebula_url_components('domain'));
	$settingsdomains = ( get_option('nebula_hostnames') )? explode(',', get_option('nebula_hostnames')) : array(nebula_url_components('domain'));
	$fulldomains = array_merge($domains, $settingsdomains, array('googleusercontent.com', 'youtube.com', 'paypal.com')); //Enter ONLY the domain and TLD. The wildcard subdomain regex is automatically added.
	$fulldomains = preg_filter('/^/', '.*', $fulldomains);
	$fulldomains = str_replace(array(' ', '.', '-'), array('', '\.', '\-'), $fulldomains); //@TODO "Nebula" 0: Add a * to capture subdomains. Final regex should be: \.*gearside\.com|\.*gearsidecreative\.com
	$fulldomains = array_unique($fulldomains);
	return implode("|", $fulldomains);
}