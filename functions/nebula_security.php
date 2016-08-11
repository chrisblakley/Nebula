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
	$override = apply_filters('pre_nebula_remove_x_pingback', false, $headers);
	if ( $override !== false ){return $override;}

	if ( isset($headers['X-Pingback']) ){
		unset($headers['X-Pingback']);
	}
	return $headers;
}

//Hijack pingback_url for get_bloginfo (<link rel="pingback" />).
add_filter('bloginfo_url', 'nebula_hijack_pingback_url', 11, 2);
function nebula_hijack_pingback_url($output, $property){
	$override = apply_filters('pre_nebula_hijack_pingback_url', false, $output, $property);
	if ( $override !== false ){return $override;}

	return ( $property == 'pingback_url' )? null : $output;
}

//Disable XMLRPC
add_filter('xmlrpc_enabled', '__return_false');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
add_filter('pre_option_enable_xmlrpc', 'nebula_disable_xmlrpc');
function nebula_disable_xmlrpc($state){
	return false;
}

//Remove rsd_link from filters (<link rel="EditURI" />).
add_action('wp', 'nebula_remove_rsd_link', 9);
function nebula_remove_rsd_link(){
	remove_action('wp_head', 'rsd_link');
}

/*
	@TODO "Security" 4: It is advised to create an Intelligence Alert in Google Analytics with the following settings:
	Name: Possible Brute Force Attack
	Check both send an email and send a text if possible.
	Period: Day
	Alert Conditions:
		This applies to: Event Action, Contains, Attempted User
		Alert me when: Total Events, Is greater than, 20 //May need to adjust this number to account for more actual users (depending on how many true logins are expected per day).
*/
//Prevent login error messages from giving too much information
add_filter('login_errors', 'nebula_login_errors');
function nebula_login_errors($error){
	$override = apply_filters('pre_nebula_login_errors', false, $error);
	if ( $override !== false ){return $override;}

	if ( !nebula_is_bot() ){
		$incorrect_username = '';
		if ( contains($error, array('The password you entered for the username')) ){
			$incorrect_username_start = strpos($error, 'for the username ')+17;
			$incorrect_username_stop = strpos($error, ' is incorrect')-$incorrect_username_start;
			$incorrect_username = strip_tags(substr($error, $incorrect_username_start, $incorrect_username_stop));
		}

		if ( !empty($incorrect_username) ){
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
    $override = apply_filters('pre_at_remove_wp_ver_css_js', false, $src);
	if ( $override !== false ){return $override;}

    if ( strpos($src, 'ver=') ){
        $src = remove_query_arg('ver', $src);
    }

    return $src;
}

//Check referrer in order to comment
add_action('check_comment_flood', 'check_referrer');
function check_referrer(){
	if ( !isset($_SERVER['HTTP_REFERER']) || empty($_SERVER['HTTP_REFERER']) ){
		wp_die('Please do not access this file directly.');
	}
}

//Track Notable Bots
add_action('wp_footer', 'track_notable_bots');
function track_notable_bots(){
	$override = apply_filters('pre_track_notable_bots', false);
	if ( $override !== false ){return;}

	//Google Page Speed
	if ( strpos($_SERVER['HTTP_USER_AGENT'], 'Google Page Speed') !== false ){
		if ( nebula_url_components('extension') != 'js' ){
			global $post;
			ga_send_event('Notable Bot Visit', 'Google Page Speed', get_the_title($post->ID), null, 0);
		}
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
	if ( nebula_option('domain_blacklisting') ){
		$blacklisted_domains = nebula_get_domain_blacklist();

		if ( count($blacklisted_domains) > 1 ){
			if ( isset($_SERVER['HTTP_REFERER']) && contains(strtolower($_SERVER['HTTP_REFERER']), $blacklisted_domains) ){
				ga_send_event('Security Precaution', 'Blacklisted Domain Prevented', 'Referring Domain: ' . $_SERVER['HTTP_REFERER'] . ' (IP: ' . $_SERVER['REMOTE_ADDR'] . ')');
				do_action('nebula_spambot_prevention');
				header('HTTP/1.1 403 Forbidden');
				die;
			}

			if ( isset($_SERVER['REMOTE_HOST']) && contains(strtolower($_SERVER['REMOTE_HOST']), $blacklisted_domains) ){
				ga_send_event('Security Precaution', 'Blacklisted Domain Prevented', 'Hostname: ' . $_SERVER['REMOTE_HOST'] . ' (IP: ' . $_SERVER['REMOTE_ADDR'] . ')');
				do_action('nebula_spambot_prevention');
				header('HTTP/1.1 403 Forbidden');
				die;
			}

			if ( isset($_SERVER['SERVER_NAME']) && contains(strtolower($_SERVER['SERVER_NAME']), $blacklisted_domains) ){
				ga_send_event('Security Precaution', 'Blacklisted Domain Prevented', 'Server Name: ' . $_SERVER['SERVER_NAME'] . ' (IP: ' . $_SERVER['REMOTE_ADDR'] . ')');
				do_action('nebula_spambot_prevention');
				header('HTTP/1.1 403 Forbidden');
				die;
			}

			if ( isset($_SERVER['REMOTE_ADDR']) && contains(strtolower(gethostbyaddr($_SERVER['REMOTE_ADDR'])), $blacklisted_domains) ){
				ga_send_event('Security Precaution', 'Blacklisted Domain Prevented', 'Network Hostname: ' . $_SERVER['SERVER_NAME'] . ' (IP: ' . $_SERVER['REMOTE_ADDR'] . ')');
				do_action('nebula_spambot_prevention');
				header('HTTP/1.1 403 Forbidden');
				die;
			}
		} else {
			ga_send_event('Security Precaution', 'Error', 'spammers.txt has no entries!');
		}
	}
}

//Return an array of blacklisted domains from Piwik (or the latest Nebula on Github)
function nebula_get_domain_blacklist(){
	$domain_blacklist_json_file = get_template_directory() . '/includes/data/domain_blacklist.txt';
	$domain_blacklist = get_transient('nebula_domain_blacklist');
	if ( empty($domain_blacklist) || is_debug() ){
		WP_Filesystem();
		global $wp_filesystem;
		$domain_blacklist = $wp_filesystem->get_contents('https://raw.githubusercontent.com/piwik/referrer-spam-blacklist/master/spammers.txt'); //@TODO "Nebula" 0: Consider using: FILE_SKIP_EMPTY_LINES (works with file() dunno about get_contents())

		if ( empty($domain_blacklist) ){
			$domain_blacklist = $wp_filesystem->get_contents('https://raw.githubusercontent.com/chrisblakley/Nebula/master/includes/data/domain_blacklist.txt'); //In case piwik is not available (or changes locations).
		}

		if ( !empty($domain_blacklist) ){
			$wp_filesystem->put_contents($domain_blacklist_json_file, $domain_blacklist);
			set_transient('nebula_domain_blacklist', $domain_blacklist, 60*60); //1 hour cache
		} else {
			$domain_blacklist = $wp_filesystem->get_contents($domain_blacklist_json_file);
		}
	}

	if ( !empty($domain_blacklist) ){
		$blacklisted_domains = array();
		foreach( explode("\n", $domain_blacklist) as $line ){
			if ( !empty($line) ){
				$blacklisted_domains[] = $line;
			}
		}

		//Additional blacklisted domains //@TODO "Nebula" 0: Make this an add_filter so extra domains can be passed to it.
		$additional_blacklisted_domains = array(
			//'secureserver.net',
		);

		return array_merge($blacklisted_domains, $additional_blacklisted_domains);
	}

	ga_send_event('Security Precaution', 'Error', 'spammers.txt was not available!');
	return false;
}