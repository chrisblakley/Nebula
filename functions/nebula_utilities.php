<?php

//Generate Session ID
function nebula_session_id(){
	$session_info = ( is_debug() )? 'dbg.' : '';
	$session_info .= ( nebula_option('nebula_wireframing', 'enabled') )? 'wrf.' : '';

	if ( is_client() ){
		$session_info .= 'cli.';
	} elseif ( is_dev() ){
		$session_info .= 'dev.';
	}

	if ( is_user_logged_in() ){
		$user_info = get_userdata(get_current_user_id());
		$role_abv = substr($user_info->roles[0], 0, 3);
		$session_info .= 'u:' . get_current_user_id() . '.r:' . $role_abv . '.';
	}

	$session_info .= ( nebula_is_bot() )? 'bot.' : '';

	$wp_session_id = ( session_id() )? session_id() : '!' . uniqid();
	$ga_cid = ( isset($nebula['user']['cid']) )? $nebula['user']['cid'] : ga_parse_cookie();

	if ( !is_site_live() ){
		$site_live = '.n';
	}

	return time() . '.' . $session_info . 's:' . $wp_session_id . '.c:' . $ga_cid . $site_live;
}

//Handle the parsing of the _ga cookie or setting it to a unique identifier
function ga_parse_cookie(){
	$override = apply_filters('pre_ga_parse_cookie', false);
	if ( $override !== false ){return $override;}

	if ( isset($_COOKIE['_ga']) ){
		list($version, $domainDepth, $cid1, $cid2) = explode('.', $_COOKIE["_ga"], 4);
		$contents = array('version' => $version, 'domainDepth' => $domainDepth, 'cid' => $cid1 . '.' . $cid2);
		$cid = $contents['cid'];
	} else {
		$cid = ga_generate_UUID();
	}
	return $cid;
}

$GLOBALS['ga_v'] = 1; //Version
$GLOBALS['ga_cid'] = ga_parse_cookie(); //Anonymous Client ID

//Generate UUID v4 function (needed to generate a CID when one isn't available)
function ga_generate_UUID(){
	$override = apply_filters('pre_ga_generate_UUID', false);
	if ( $override !== false ){return $override;}

	return sprintf(
		'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		mt_rand(0, 0xffff), mt_rand(0, 0xffff), //32 bits for "time_low"
		mt_rand(0, 0xffff), //16 bits for "time_mid"
		mt_rand(0, 0x0fff) | 0x4000, //16 bits for "time_hi_and_version", Four most significant bits holds version number 4
		mt_rand(0, 0x3fff) | 0x8000, //16 bits, 8 bits for "clk_seq_hi_res", 8 bits for "clk_seq_low", Two most significant bits holds zero and one for variant DCE1.1
		mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff) //48 bits for "node"
	);
}

//Generate Domain Hash
function ga_generate_domain_hash($domain){
	$override = apply_filters('pre_ga_generate_domain_hash', false, $domain);
	if ( $override !== false ){return $override;}

	if ( empty($domain) ){
		$domain = nebula_url_components('domain');
	}

	$a = 0;
	for ( $i = strlen($domain)-1; $i >= 0; $i-- ){
		$ascii = ord($domain[$i]);
		$a = (($a<<6)&268435455)+$ascii+($ascii<<14);
		$c = $a&266338304;
		$a = ( $c != 0 )? $a^($c>>21) : $a;
	}
	return $a;
}

//Generate the full path of a Google Analytics __utm.gif with necessary parameters.
//https://developers.google.com/analytics/resources/articles/gaTrackingTroubleshooting?csw=1#gifParameters
function ga_UTM_gif($user_cookies=array(), $user_parameters=array()){
	$override = apply_filters('pre_ga_UTM_gif', false, $user_cookies, $user_parameters);
	if ( $override !== false ){return $override;}

	//@TODO "Nebula" 0: Make an AJAX function in Nebula (plugin) to accept a form for each parameter then renders the __utm.gif pixel.

	$domain = nebula_url_components('domain');
	$cookies = array(
		'utma' => ga_generate_domain_hash($domain) . '.' . mt_rand(1000000000, 9999999999) . '.' . time() . '.' . time() . '.' . time() . '.1', //Domain Hash . Random ID . Time of First Visit . Time of Last Visit . Time of Current Visit . Session Counter ***Absolutely Required***
		'utmz' => ga_generate_domain_hash($domain) . '.' . time() . '.1.1.', //Campaign Data (Domain Hash . Time . Counter . Counter)
		'utmcsr' => '-', //Campaign Source "google"
		'utmccn' => '-', //Campaign Name "(organic)"
		'utmcmd' => '-', //Campaign Medium "organic"
		'utmctr' => '-', //Campaign Terms (for paid search)
		'utmcct' => '-', //Campaign Content Description
	);
	$cookies = array_merge($cookies, $user_cookies);

	$data = array(
		'utmwv' => '5.3.8', //Tracking code version *** REQUIRED ***
		'utmac' => $GLOBALS['ga'], //Account string, appears on all requests *** REQUIRED ***
		'utmdt' => get_the_title(), //Page title, which is a URL-encoded string *** REQUIRED ***
		'utmp' => nebula_url_components('filepath'), //Page request of the current page (current path) *** REQUIRED ***
		'utmcc' => '__utma=' . $cookies['utma'] . ';+', //Cookie values. This request parameter sends all the cookies requested from the page. *** REQUIRED ***

		'utmhn' => nebula_url_components('hostname'), //Host name, which is a URL-encoded string
		'utmn' => rand(pow(10, 10-1), pow(10, 10)-1), //Unique ID generated for each GIF request to prevent caching of the GIF image
		'utms' => '1', //Session requests. Updates every time a __utm.gif request is made. Stops incrementing at 500 (max number of GIF requests per session).
		'utmul' => str_replace('-', '_', get_bloginfo('language')), //Language encoding for the browser. Some browsers don’t set this, in which case it is set to '-'
		'utmje' => '0', //Indicates if browser is Java enabled. 1 is true.
		'utmhid' => mt_rand(1000000000, 9999999999), //A random number used to link the GA GIF request with AdSense
		'utmr' => ( isset($_SERVER['HTTP_REFERER']) )? $_SERVER['HTTP_REFERER'] : '-', //Referral, complete URL. If none, it is set to '-'
		'utmu' => 'q~', //This is a new parameter that contains some internal state that helps improve ga.js
	);
	$data = array_merge($data, $user_parameters);

	//Append Campaign Data to the Cookie parameter
	if ( !empty($cookies['utmcsr']) && !empty($cookies['utmcsr']) && !empty($cookies['utmcsr']) ){
		$data['utmcc'] = '__utma=' . $cookies['utma'] . ';+__utmz=' . $cookies['utmz'] . 'utmcsr=' . $cookies['utmcsr'] . '|utmccn=' . $cookies['utmccn'] . '|utmcmd=' . $cookies['utmcmd'] . '|utmctr=' . $cookies['utmctr'] . '|utmcct=' . $cookies['utmcct'] . ';+';
	}

	return 'https://ssl.google-analytics.com/__utm.gif?' . str_replace('+', '%20', http_build_query($data));
}

//Send Data to Google Analytics
//https://developers.google.com/analytics/devguides/collection/protocol/v1/devguide#event
function ga_send_data($data){
	$override = apply_filters('pre_ga_send_data', false, $data);
	if ( $override !== false ){return $override;}

	$getString = 'https://ssl.google-analytics.com/collect';
	$getString .= '?payload_data&';
	$getString .= http_build_query($data);
	$result = wp_remote_get($getString);
	return $result;
}

//Send Pageview Function for Server-Side Google Analytics
function ga_send_pageview($hostname=null, $path=null, $title=null, $array=array()){
	$override = apply_filters('pre_ga_send_pageview', false, $hostname, $path, $title, $array);
	if ( $override !== false ){return $override;}

	if ( empty($GLOBALS['ga_v']) ){
		$GLOBALS['ga_v'] = 1;
	}

	if ( empty($GLOBALS['ga_cid']) ){
		$GLOBALS['ga_cid'] = ga_parse_cookie();
	}

	if ( empty($hostname) ){
		$hostname = nebula_url_components('hostname');
	}

	if ( empty($path) ){
		$path = nebula_url_components('path');
	}

	if ( empty($title) ){
		$title = get_the_title();
	}

	//GA Parameter Guide: https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters?hl=en
	//GA Hit Builder: https://ga-dev-tools.appspot.com/hit-builder/
	$data = array(
		'v' => $GLOBALS['ga_v'],
		'tid' => $GLOBALS['ga'],
		'cid' => $GLOBALS['ga_cid'],
		't' => 'pageview',
		'dh' => $hostname, //Document Hostname "gearside.com"
		'dp' => $path, //Path "/something"
		'dt' => $title, //Title
		'ua' => rawurlencode($_SERVER['HTTP_USER_AGENT']) //User Agent
	);

	$data = array_merge($data, $array);
	ga_send_data($data);
}

//Send Event Function for Server-Side Google Analytics
//@TODO "Nebula" 0: "WordPress" is still appearing in Google Analytics browser reports for these events!
function ga_send_event($category=null, $action=null, $label=null, $value=null, $ni=1, $array=array()){
	$override = apply_filters('pre_ga_send_event', false, $category, $action, $label, $value, $ni, $array);
	if ( $override !== false ){return $override;}

	if ( empty($GLOBALS['ga_v']) ){
		$GLOBALS['ga_v'] = 1;
	}

	if ( empty($GLOBALS['ga_cid']) ){
		$GLOBALS['ga_cid'] = ga_parse_cookie();
	}

	//GA Parameter Guide: https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters?hl=en
	//GA Hit Builder: https://ga-dev-tools.appspot.com/hit-builder/
	$data = array(
		'v' => $GLOBALS['ga_v'],
		'tid' => $GLOBALS['ga'],
		'cid' => $GLOBALS['ga_cid'],
		't' => 'event',
		'ec' => $category, //Category (Required)
		'ea' => $action, //Action (Required)
		'el' => $label, //Label
		'ev' => $value, //Value
		'ni' => $ni, //Non-Interaction
		'dh' => nebula_url_components('hostname'), //Document Hostname "gearside.com"
		'dp' => nebula_url_components('path'),
		'ua' => rawurlencode($_SERVER['HTTP_USER_AGENT']) //User Agent
	);

	$data = array_merge($data, $array);
	ga_send_data($data);
}

//Send custom data to Google Analytics. Must pass an array of data to this function:
//ga_send_custom(array('t' => 'event', 'ec' => 'Category Here', 'ea' => 'Action Here', 'el' => 'Label Here'));
//https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters
function ga_send_custom($array=array()){ //@TODO "Nebula" 0: Add additional parameters to this function too (like above)!
	$override = apply_filters('pre_ga_send_custom', false, $array);
	if ( $override !== false ){return $override;}

	//GA Parameter Guide: https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters?hl=en
	//GA Hit Builder: https://ga-dev-tools.appspot.com/hit-builder/
	$defaults = array(
		'v' => $GLOBALS['ga_v'],
		'tid' => $GLOBALS['ga'],
		'cid' => $GLOBALS['ga_cid'],
		't' => '',
		'ni' => 1,
		'dh' => nebula_url_components('hostname'), //Document Hostname "gearside.com"
		'dp' => nebula_url_components('path'),
		'ua' => rawurlencode($_SERVER['HTTP_USER_AGENT']) //User Agent
	);

	$data = array_merge($defaults, $array);

	if ( !empty($data['t']) ){
		ga_send_data($data);
	} else {
		trigger_error("ga_send_custom() requires an array of values. A Hit Type ('t') is required! See documentation here for accepted parameters: https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters", E_USER_ERROR);
		return;
	}
}

//Sends events to Google Analytics via AJAX (used if GA is blocked via JavaScript)
add_action('wp_ajax_nebula_ga_event_ajax', 'nebula_ga_event_ajax');
add_action('wp_ajax_nopriv_nebula_ga_event_ajax', 'nebula_ga_event_ajax');
function nebula_ga_event_ajax(){
	if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce')){ die('Permission Denied.'); }
	if ( !nebula_is_bot() ){
		ga_send_event($_POST['data'][0]['category'], $_POST['data'][0]['action'], $_POST['data'][0]['label'], $_POST['data'][0]['value'], $_POST['data'][0]['ni']);
	}
	exit;
}

//Retarget users based on prior conversions/leads
function nebula_retarget($category=false, $data=null, $strict=true, $return=false){
	global $nebula;

	if ( empty($category) ){ //$category is required
		return false;
	}

	if ( is_bool($data) ){ //If data is boolean, then $strict is irrelevant, $data should be empty, and $return was meant to be boolean
		$return = $data;
		$data = null;
	}

	if ( !empty($nebula['user']['conversions']) ){ //If there are any conversions
		if ( !empty($data) ){ //If specific data is being requested
			if ( !empty($nebula['user']['conversions'][$category]) ){ //If the requested category exists
				if ( !$return ){ //If returning boolean
					if ( $strict ){ //If checking for exact match
						return in_array_r($data, $nebula['user']['conversions'][$category]);
					} else { //Else search for string position
						$data_string = implode(' ', $nebula['user']['conversions'][$category]);
						if ( strpos(strtolower($data_string), strtolower($data)) ){
							return true;
						}
					}
				} else { //Else returning the value
					if ( in_array_r($data, $nebula['user']['conversions'][$category]) ){
						return $nebula['user']['conversions'][$category][$data];
					}
				}
			}
		} else { //If no specific data is requested (check if the category itself exists)
			if ( !$return ){ //If returning boolean
				return array_key_exists($category, $nebula['user']['conversions']);
			} else { //Else returning the value
				if ( array_key_exists($category, $nebula['user']['conversions']) ){
					return $nebula['user']['conversions'][$category];
				}
			}
		}
	}
	return false;
}

//Check if a user has been online in the last 15 minutes
function nebula_is_user_online($id){
	$override = apply_filters('pre_nebula_is_user_online', false, $id);
	if ( $override !== false ){return $override;}

	$logged_in_users = get_transient('users_status');
	return isset($logged_in_users[$id]['last']) && $logged_in_users[$id]['last'] > time()-900; //15 Minutes
}

//Check when a user was last online.
function nebula_user_last_online($id){
	$override = apply_filters('pre_nebula_user_last_online', false, $id);
	if ( $override !== false ){return $override;}

	$logged_in_users = get_transient('users_status');
	if ( isset($logged_in_users[$id]['last']) ){
		return $logged_in_users[$id]['last'];
	} else {
		return false;
	}
}

//Get a count of online users, or an array of online user IDs.
function nebula_online_users($return='count'){
	$override = apply_filters('pre_nebula_online_users', false, $return);
	if ( $override !== false ){return $override;}

	$logged_in_users = get_transient('users_status');
	if ( empty($logged_in_users) ){
		return ( $return == 'count' )? 0 : false;
	}
	$user_online_count = 0;
	$online_users = array();

	foreach ( $logged_in_users as $user ){
		if ( !empty($user['username']) && isset($user['last']) && $user['last'] > time()-900 ){
			$online_users[] = $user;
			$user_online_count++;
		}
	}

	return ( $return == 'count' )? $user_online_count : $online_users;
}

function nebula_user_single_concurrent($id){
	$override = apply_filters('pre_nebula_user_single_concurrent', false, $id);
	if ( $override !== false ){return $override;}

	$logged_in_users = get_transient('users_status');
	if ( isset($logged_in_users[$id]['unique']) ){
		return count($logged_in_users[$id]['unique']);
	} else {
		return 0;
	}
}

//Check if the current IP address matches any of the dev IP address from Nebula Options
//Passing $strict bypasses IP check, so user must be a dev and logged in.
//Note: This should not be used for security purposes since IP addresses can be spoofed.
function is_dev($strict=false){
	$override = apply_filters('pre_is_dev', false, $strict);
	if ( $override !== false ){return $override;}

	if ( empty($strict) ){
		$devIPs = explode(',', get_option('nebula_dev_ip'));
		foreach ( $devIPs as $devIP ){
			if ( trim($devIP) == $_SERVER['REMOTE_ADDR'] ){
				return true;
			}
		}
	}

	//Check if the current user's email domain matches any of the dev email domains from Nebula Options
	if ( is_user_logged_in() ){
		$current_user = wp_get_current_user();
		list($current_user_email, $current_user_domain) = explode('@', $current_user->user_email); //@TODO "Nebula" 0: If $current_user->user_email is not empty?

		$devEmails = explode(',', get_option('nebula_dev_email_domain'));
		foreach ( $devEmails as $devEmail ){
			if ( trim($devEmail) == $current_user_domain ){
				return true;
			}
		}
	}

	return false;
}

//Check if the current IP address matches any of the client IP address from Nebula Options
//Passing $strict bypasses IP check, so user must be a client and logged in.
//Note: This should not be used for security purposes since IP addresses can be spoofed.
function is_client($strict=false){
	$override = apply_filters('pre_is_client', false, $strict);
	if ( $override !== false ){return $override;}

	if ( empty($strict) ){
		$clientIPs = explode(',', get_option('nebula_client_ip'));
		foreach ( $clientIPs as $clientIP ){
			if ( trim($clientIP) == $_SERVER['REMOTE_ADDR'] ){
				return true;
			}
		}
	}

	if ( is_user_logged_in() ){
		$current_user = wp_get_current_user();
		list($current_user_email, $current_user_domain) = explode('@', $current_user->user_email); //@TODO "Nebula" 0: If $current_user->user_email is not empty?

		//Check if the current user's email domain matches any of the client email domains from Nebula Options
		$clientEmails = explode(',', get_option('nebula_client_email_domain'));
		foreach ( $clientEmails as $clientEmail ){
			if ( trim($clientEmail) == $current_user_domain ){
				return true;
			}
		}
	}

	return false;
}

//Check if user is using the debug query string.
//$strict requires the user to be a developer or client. Passing 2 to $strict requires the dev or client to be logged in too.
function is_debug($strict=false){
	$override = apply_filters('pre_is_debug', false, $strict);
	if ( $override !== false ){return $override;}

	$very_strict = ( $strict > 1 )? $strict : false;
	if ( array_key_exists('debug', $_GET) ){
		if ( !empty($strict) ){
			if ( is_dev($very_strict) || is_client($very_strict) ){
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}
	return false;
}

//Check if the current site is live to the public.
//Note: This checks if the hostname of the home URL matches any of the valid hostnames.
//If the Valid Hostnames option is empty, this will return true as it is unknown.
function is_site_live(){
	$override = apply_filters('pre_is_site_live', false);
	if ( $override !== false ){return $override;}

	if ( nebula_option('nebula_hostnames') ){
		if ( strpos(get_option('nebula_hostnames'), nebula_url_components('hostname', home_url())) >= 0 ){
			return true;
		} else {
			return false;
		}
	} else {
		return true;
	}
}

//Get the full URL. Not intended for secure use ($_SERVER var can be manipulated by client/server).
function nebula_requested_url($host="HTTP_HOST"){ //Can use "SERVER_NAME" as an alternative to "HTTP_HOST".
	$override = apply_filters('pre_nebula_requested_url', false, $host);
	if ( $override !== false ){return $override;}

	$protocol = ( is_ssl() )? 'https' : 'http';
	$full_url = $protocol . '://' . $_SERVER["$host"] . $_SERVER["REQUEST_URI"];
	return $full_url;
}

//Separate a URL into it's components.
function nebula_url_components($segment="all", $url=null){
	$override = apply_filters('pre_nebula_url_components', false, $segment, $url);
	if ( $override !== false ){return $override;}

	if ( !$url ){
		$url = nebula_requested_url();
	}

	$url_compontents = parse_url($url);
	if ( empty($url_compontents['host']) ){
		return;
	}
	$host = explode('.', $url_compontents['host']);

	//Best way to get the domain so far. Probably a better way by checking against all known TLDs.
	preg_match("/[a-z0-9\-]{1,63}\.[a-z\.]{2,6}$/", parse_url($url, PHP_URL_HOST), $domain);
	$sld = substr($domain[0], 0, strpos($domain[0], '.'));
	$tld = substr($domain[0], strpos($domain[0], '.'));

	switch ($segment){
		case ('all'):
		case ('href'):
			return $url;
			break;

		case ('protocol'): //Protocol and Scheme are aliases and return the same value.
		case ('scheme'): //Protocol and Scheme are aliases and return the same value.
		case ('schema'):
			if ( $url_compontents['scheme'] != '' ){
				return $url_compontents['scheme'];
			} else {
				return false;
			}
			break;

		case ('port'):
			if ( $url_compontents['port'] ){
				return $url_compontents['port'];
			} else {
				switch( $url_compontents['scheme'] ){
	                case ('http'):
	                    return 80; //Default for http
	                    break;
	                case ('https'):
	                    return 443; //Default for https
	                    break;
	                case ('ftp'):
	                    return 21; //Default for ftp
	                    break;
	                case ('ftps'):
	                    return 990; //Default for ftps
	                    break;
	                default:
	                    return false;
	                    break;
	            }
			}
			break;

		case ('user'): //Returns the username from this type of syntax: https://username:password@gearside.com/
		case ('username'):
			if ( $url_compontents['user'] ){
				return $url_compontents['user'];
			} else {
				return false;
			}
			break;

		case ('pass'): //Returns the password from this type of syntax: https://username:password@gearside.com/
		case ('password'):
			if ( $url_compontents['pass'] ){
				return $url_compontents['pass'];
			} else {
				return false;
			}
			break;

		case ('authority'):
			if ( $url_compontents['user'] && $url_compontents['pass'] ){
				return $url_compontents['user'] . ':' . $url_compontents['pass'] . '@' . $url_compontents['host'] . ':' . nebula_url_components('port', $url);
			} else {
				return false;
			}
			break;

		case ('host'): //In http://something.example.com the host is "something.example.com"
		case ('hostname'):
			return $url_compontents['host'];
			break;

		case ('www') :
			if ( $host[0] == 'www' ){
				return 'www';
			} else {
				return false;
			}
			break;

		case ('subdomain'):
		case ('sub_domain'):
			if ( $host[0] != 'www' && $host[0] != $sld ){
				return $host[0];
			} else {
				return false;
			}
			break;

		case ('domain') : //In http://example.com the domain is "example.com"
			return $domain[0];
			break;

		case ('basedomain'): //In http://example.com/something the basedomain is "http://example.com"
		case ('base_domain'):
		case ('origin') :
			return $url_compontents['scheme'] . '://' . $domain[0];
			break;

		case ('sld') : //In example.com the sld is "example"
		case ('second_level_domain'):
		case ('second-level_domain'):
			return $sld;
			break;

		case ('tld') : //In example.com the tld is ".com"
		case ('top_level_domain'):
		case ('top-level_domain'):
			return $tld;
			break;

		case ('filepath'): //Filepath will be both path and file/extension
		case ('pathname'):
			return $url_compontents['path'];
			break;

		case ('file'): //Filename will be just the filename/extension.
		case ('filename'):
			if ( contains(basename($url_compontents['path']), array('.')) ){
				return basename($url_compontents['path']);
			} else {
				return false;
			}
			break;

		case ('extension'): //The extension only (without ".")
		    if ( contains(basename($url_compontents['path']), array('.')) ){
		        $file_parts = explode('.', $url_compontents['path']);
		        return $file_parts[1];
		    } else {
		        return false;
		    }
		    break;

		case ('path'): //Path should be just the path without the filename/extension.
			if ( contains(basename($url_compontents['path']), array('.')) ){ //@TODO "Nebula" 0: This will possibly give bad data if the directory name has a "." in it
				return str_replace(basename($url_compontents['path']), '', $url_compontents['path']);
			} else {
				return $url_compontents['path'];
			}
			break;

		case ('query'):
		case ('queries'):
		case ('search'):
			return $url_compontents['query'];
			break;

		case ('fragment'):
		case ('fragments'):
		case ('anchor'):
		case ('hash') :
		case ('hashtag'):
		case ('id'):
			return $url_compontents['fragment'];
			break;

		default :
			return $url;
			break;
	}
}

//Fuzzy meta sub key finder (Used to query ACF nested repeater fields).
//Example: 'key' => 'dates_%_start_date',
add_filter('posts_where' , 'nebula_fuzzy_posts_where');
function nebula_fuzzy_posts_where($where){
	$override = apply_filters('pre_nebula_fuzzy_posts_where', false, $where);
	if ( $override !== false ){return $override;}

	if ( strpos($where, '_%_') > -1 ){
		$where = preg_replace("/meta_key = ([\'\"])(.+)_%_/", "meta_key LIKE $1$2_%_", $where);
	}
	return $where;
}


//Use WordPress core browser detection
//@TODO "Nebula" 0: Look into using this in addition to a more powerful library.
function wp_browser_detect(){
	$override = apply_filters('pre_wp_browser_detect', false);
	if ( $override !== false ){return $override;}

	//Browsers
	global $is_lynx, $is_gecko, $is_IE, $is_opera, $is_NS4, $is_safari, $is_chrome, $is_iphone;

	//$browser = get_browser(null, true); //@TODO "Nebula" 0: Find a server this works on and then wrap in if $browser, then echo the version number too
	//@TODO "Nebula" 0: Also look into the function wp_check_browser_version().

    if ( $is_lynx ){
    	return 'Lynx';
    } elseif ( $is_gecko ){
    	return 'Gecko';
    } elseif ( $is_opera ){
    	return 'Opera';
    } elseif ( $is_NS4 ){
    	return 'NS4';
    } elseif ( $is_safari ){
    	return 'Safari';
    } elseif ( $is_chrome ){
    	return 'Chrome';
    } elseif ( $is_IE ){
    	return 'IE';
    } else {
    	return 'Unknown Browser';
    }
}

//Text limiter by words
function string_limit_words($string, $word_limit){
	$override = apply_filters('pre_string_limit_words', false, $string, $word_limit);
	if ( $override !== false ){return $override;}

	$limited[0] = $string;
	$limited[1] = 0;
	$words = explode(' ', $string, ($word_limit + 1));
	if(count($words) > $word_limit){
		array_pop($words);
		$limited[0] = implode(' ', $words);
		$limited[1] = 1;
	}
	return $limited;
}

//Word limiter by characters
function word_limit_chars($string, $charlimit, $continue=false){
	$override = apply_filters('pre_word_limit_chars', false, $string, $charlimit, $continue);
	if ( $override !== false ){return $override;}

	//1 = "Continue Reading", 2 = "Learn More"
	if ( strlen(strip_tags($string, '<p><span><a>')) <= $charlimit ){
		$newString = strip_tags($string, '<p><span><a>');
	} else {
		$newString = preg_replace('/\s+?(\S+)?$/', '', substr(strip_tags($string, '<p><span><a>'), 0, ($charlimit + 1)));
		if ( $continue == 1 ){
			$newString = $newString . '&hellip;' . ' <a class="continuereading" href="'. get_permalink() . '">Continue reading <span class="meta-nav">&rarr;</span></a>';
		} elseif( $continue == 2 ){
			$newString = $newString . '&hellip;' . ' <a class="continuereading" href="'. get_permalink() . '">Learn more &raquo;</a>';
		} else {
			$newString = $newString . '&hellip;';
		}
	}
	return $newString;
}

//Traverse multidimensional arrays
function in_array_r($needle, $haystack, $strict = true){
	$override = apply_filters('pre_in_array_r', false, $needle, $haystack, $strict);
	if ( $override !== false ){return $override;}

    foreach ($haystack as $item){
        if ( ($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict)) ){
            return true;
        }
    }
    return false;
}

//Recursive Glob
function glob_r($pattern, $flags = 0){
    $override = apply_filters('pre_glob_r', false, $pattern, $flags);
	if ( $override !== false ){return $override;}

    $files = glob($pattern, $flags);
    foreach ( glob(dirname($pattern) . '/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir ){
        $files = array_merge($files, glob_r($dir . '/' . basename($pattern), $flags));
    }
    return $files;
}

//Add up the filesizes of files in a directory (and it's sub-directories)
function foldersize($path){
	$override = apply_filters('pre_foldersize', false, $path);
	if ( $override !== false ){return $override;}

	$total_size = 0;
	$files = scandir($path);
	$cleanPath = rtrim($path, '/') . '/';
	foreach ( $files as $t ){
		if ( $t <> "." && $t <> ".."){
			$currentFile = $cleanPath . $t;
			if ( is_dir($currentFile) ){
				$size = foldersize($currentFile);
				$total_size += $size;
			} else {
				$size = filesize($currentFile);
				$total_size += $size;
			}
		}
	}
	return $total_size;
}

//Checks to see if an array contains a string.
function contains($str, array $arr){
	$override = apply_filters('pre_contains', false, $str, $arr);
	if ( $override !== false ){return $override;}

	foreach ( $arr as $a ){
		if ( stripos($str, $a) !== false ){
			return true;
		}
	}
	return false;
}

//Check if a website or resource is available
function nebula_is_available($domain=null){
	$override = apply_filters('pre_nebula_is_available', false, $domain);
	if ( $override !== false ){return $override;}

	if ( empty($domain) || strpos($domain, 'http') != 0 ){
		trigger_error('Error: Requested domain is either empty or missing acceptable protocol.', E_USER_ERROR);
		return false;
	}

	$curl_init = curl_init($domain);
	$curl_options = array(
		CURLOPT_RETURNTRANSFER => true, //Return web page
		CURLOPT_HEADER => true, //Return headers
		CURLOPT_FOLLOWLOCATION => true, //Follow redirects
		CURLOPT_CONNECTTIMEOUT => 10, //Timeout on connect
		CURLOPT_NOBODY => true,
		CURLOPT_MAXREDIRS => 10, //Stop after 10 redirects
		CURLOPT_SSL_VERIFYPEER => false, //Disabled SSL cert checks
	);
	curl_setopt_array($curl_init, $curl_options);
	$response = curl_exec($curl_init);
	$header = curl_getinfo($curl_init);
	$error = curl_errno($curl_init);
	$error_message = curl_error($curl_init);
	curl_close($curl_init);

	if ( $error == 35 ){ //If SSL certificate error, check non SSL domain
		$http_domain = str_replace('https://', 'http://', $domain);
		return nebula_is_available($http_domain);
	}

	if ( !empty($response) && !empty($header) && empty($error) && empty($error_message) ){
		if ( $header['http_code'] == 0 || $header['http_code'] >= 400 ){
			return false;
		}
		return true;
	}
	return false;
}

//Generate a random integer between two numbers with an exclusion array
//Call it like: random_number_between_but_not(1, 10, array(5, 6, 7, 8));
function random_number_between_but_not($min=null, $max=null, $butNot=null){
	$override = apply_filters('pre_random_number_between_but_not', false, $min, $max, $butNot);
	if ( $override !== false ){return $override;}

	if ( $min > $max ){ //If min is greater than max, swap variables
		$tmp = $min;
		$min = $max;
		$max = $tmp;
	}
	if ( gettype($butNot) == 'array' ){
		foreach( $butNot as $key => $skip ){
			if( $skip > $max || $skip < $min ){
				unset($butNot[$key]);
			}
		}
		if ( count($butNot) == $max-$min+1 ){
			trigger_error('Error: no number exists between ' . $min .' and ' . $max .'. Check exclusion parameter.', E_USER_ERROR);
			return false;
		}
		while ( in_array(($randnum = rand($min, $max)), $butNot));
	} else {
		while (($randnum = rand($min, $max)) == $butNot );
	}
	return $randnum;
}

//Call a placeholder image from Unsplash.it
function unsplash_it($width=800, $height=600, $raw=false, $specific=false){
	$override = apply_filters('pre_unsplash_it', false, $width, $height, $raw, $specific);
	if ( $override !== false ){return $override;}

	$unsplash_total = 920;
	$skip_list = array(31, 35, 224, 285, 312, 16, 403, 172, 268, 267, 349, 69, 103, 24, 140, 47, 219, 222, 184, 306, 70, 371, 385, 45, 211, 95, 83, 150, 233, 275, 343, 317, 278, 429, 383, 296, 292, 193, 299, 195, 298, 68, 148, 151, 129, 277, 333, 85, 48, 128, 365, 138, 155, 257, 37, 288, 407);
	if ( !is_int($specific) ){
		$randID = random_number_between_but_not(0, $unsplash_total, $skip_list); //Update the second number here periodically as more Unsplash.it photos become available.
	} else {
		$randID = $specific;
	}

	//Check if unsplash.it is online
	if ( !nebula_is_available('https://unsplash.it') ){
		ga_send_event('send', 'event', 'Error', 'Random Unsplash', 'Unsplash.it Not Available');
		if ( $raw ){
			return placehold_it($width, $height, 'Unsplash.it Unavailable', 'ca3838');
		} else {
			return placehold_it($width, $height, 'Unsplash.it Unavailable', 'ca3838') . '" title="Unsplash.it is not available.';
		}
	}

	$image_path = 'https://unsplash.it/' . $width . '/' . $height . '?image=' . $randID;
	$check_image = nebula_is_available($image_path); //Ignore errors (because that's what we're looking for)

	$i = 1;
	$attempts = '';
	while ( !$check_image ){
		$attempts = ' [Errors: ' . $i . ']';
		if ( $specific ){
			ga_send_event('send', 'event', 'Error', 'Random Unsplash', 'Image Not Found (ID: ' . $randID . ')');
			if ( $raw ){
				return placehold_it($width, $height, 'ID+' . $randID . '+Not+Found', 'f6b83f');
			} else {
				return placehold_it($width, $height, 'ID+' . $randID . '+Not+Found', 'f6b83f') . '" title="Unsplash image with ID ' . $randID . $attempts;
			}
		} elseif ( $i >= 5 ){
			ga_send_event('send', 'event', 'Error', 'Random Unsplash', 'Multiple Images Not Found');
			if ( $raw ){
				return placehold_it($width, $height, 'Unsplash.it Images Unavailable', 'f6773f');
			} else {
				return placehold_it($width, $height, 'Unsplash.it Images Unavailable', 'f6773f') . '" title="Unsplash.it Images Unavailable ' . $attempts;
			}
		}

	    $skip_list[] = $randID;
	    ga_send_event('send', 'event', 'Error', 'Random Unsplash', 'Image Not Found (ID: ' . $randID . ')' . $attempts);
	    $randID = random_number_between_but_not(0, $unsplash_total, $skip_list);
	    $image_path = 'https://unsplash.it/' . $width . '/' . $height . '?image=' . $randID;
	    $check_image = nebula_is_available($image_path);
	    $i++;
	}

	if ( $raw ){
		return $image_path;
	} else {
		return $image_path . '" title="Unsplash ID #' . $randID . $attempts;
	}
}

//Call a placeholder image from Placehold.it
function placehold_it($width=800, $height=600, $text=false, $color=false){
	$override = apply_filters('pre_placehold_it', false, $width, $height, $text, $color);
	if ( $override !== false ){return $override;}

	if ( nebula_is_available('https://placehold.it') ){
		$text = ( $text )? '?text=' . str_replace(' ', '+', $text) : '';
		$color = ( $color )? str_replace('#', '', $color) . '/' : '';
		return 'https://placehold.it/' . $width . 'x' . $height . '/' . $color . $text;
	} else {
		return get_template_directory_uri() . '/images/x.png'; //Placehold.it is not available.
	}
}

//Automatically convert HEX colors to RGB.
function hex2rgb($color){
	$override = apply_filters('pre_hex2rgb', false, $color);
	if ( $override !== false ){return $override;}

	if ( $color[0] == '#' ){
		$color = substr($color, 1);
	}
	if ( strlen($color) == 6 ){
		list($r, $g, $b) = array($color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]);
	} elseif ( strlen($color) == 3 ){
		list($r, $g, $b) = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
	} else {
		return false;
	}
	$r = hexdec($r);
	$g = hexdec($g);
	$b = hexdec($b);
	return array('r' => $r, 'g' => $g, 'b' => $b);
}

//Check the brightness of a color. 0=darkest, 255=lightest, 256=false
function nebula_color_brightness($hex){
	$override = apply_filters('pre_nebula_color_brightness', false, $hex);
	if ( $override !== false ){return $override;}

	//@TODO "Nebula" 0: If an rgb value is passed, (create then) run an rgb2hex() function
	if ( strpos($hex, '#') !== false ){
		preg_match("/#(?:[0-9a-fA-F]{3,6})/i", $hex, $hex_colors);

		if ( strlen($hex_colors[0]) == 4 ){
			$values = str_split($hex_colors[0]);
			$full_hex = '#' . $values[1] . $values[1] . $values[2] . $values[2] . $values[3] . $values[3];
		} else {
			$full_hex = $hex_colors[0];
		}

		$hex = str_replace('#', '', $full_hex);
		$hex_r = hexdec(substr($hex, 0, 2));
		$hex_g = hexdec(substr($hex, 2, 2));
		$hex_b = hexdec(substr($hex, 4, 2));

		return (($hex_r*299)+($hex_g*587)+($hex_b*114))/1000;
	} else {
		return 256;
	}
}

//Attempt to get WHOIS information from domain
function whois_info($data, $domain=''){
	$override = apply_filters('pre_whois_info', false, $data, $domain);
	if ( $override !== false ){return $override;}

	if ( $domain == '' ){
		$whois = getwhois(nebula_url_components('sld'), ltrim(nebula_url_components('tld'), '.'));
	} else {
		$whois = getwhois(nebula_url_components('sld', $domain), ltrim(nebula_url_components('tld', $domain), '.'));
		$whois = preg_replace('!\s+!', ' ', $whois);
	}

	switch ( $data ){
		case 'expiration':
		case 'expiration_date':
		case 'domain_expiration':
			if ( contains($whois, array('Registrar Registration Expiration Date: ')) ){
				return trim(substr($whois, strpos($whois, "Registrar Registration Expiration Date: ")+40, 10));
			} elseif ( contains($whois, array('Registry Expiry Date: ')) ){
				return trim(substr($whois, strpos($whois, "Registry Expiry Date: ")+22, 10));
			} elseif ( contains($whois, array('Relevant dates: ')) ){
				return trim(substr($whois, strpos($whois, "Expiry date:")+13, 11));
			} elseif ( contains($whois, array('Expiry date: ')) ){
				return trim(substr($whois, strpos($whois, "Expiry date:")+13, 10));
			} elseif ( contains($whois, array('Domain expires: ')) ){
				return trim(substr($whois, strpos($whois, "Domain expires: ")+16, 11));
			}
			return false;
			break;
		case 'registrar':
		case 'registrar_name':
			$domain_registrar_start = '';
			$domain_registrar_stop = '';
			if ( contains($whois, array('Registrar: ')) && contains($whois, array('Sponsoring Registrar IANA ID:')) ){
				$domain_registrar_start = strpos($whois, "Registrar: ")+11;
				$domain_registrar_stop = strpos($whois, "Sponsoring Registrar IANA ID:")-$domain_registrar_start;
				return trim(substr($whois, $domain_registrar_start, $domain_registrar_stop));
			} elseif ( contains($whois, array('Registrar: ')) && contains($whois, array('Registrar IANA ID: ')) ){
				$domain_registrar_start = strpos($whois, "Registrar: ")+11;
				$domain_registrar_stop = strpos($whois, "Registrar IANA ID: ")-$domain_registrar_start;
				return trim(substr($whois, $domain_registrar_start, $domain_registrar_stop));
			} elseif ( contains($whois, array('Registrar: ')) && contains($whois, array('Registrar IANA ID: ')) ){
				$domain_registrar_start = strpos($whois, "Registrar: ")+11;
				$domain_registrar_stop = strpos($whois, "Registrar IANA ID: ")-$domain_registrar_start;
				return trim(substr($whois, $domain_registrar_start, $domain_registrar_stop));
			} elseif ( contains($whois, array('Sponsoring Registrar:')) && contains($whois, array('Sponsoring Registrar IANA ID:')) ){
				$domain_registrar_start = strpos($whois, "Sponsoring Registrar:")+21;
				$domain_registrar_stop = strpos($whois, "Sponsoring Registrar IANA ID:")-$domain_registrar_start;
				return trim(substr($whois, $domain_registrar_start, $domain_registrar_stop));
			} elseif ( contains($whois, array('Registrar:')) && contains($whois, array('Number: ')) ){
				$domain_registrar_start = strpos($whois, "Registrar:")+17;
				$domain_registrar_stop = strpos($whois, "Number: ")-$domain_registrar_start;
				return trim(substr($whois, $domain_registrar_start, $domain_registrar_stop));
			} elseif ( contains($whois, array('Registrar:')) && contains($whois, array('URL:')) ){ //co.uk
				$domain_registrar_start = strpos($whois, "Registrar: ")+11;
				$domain_registrar_stop = strpos($whois, "URL: ")-$domain_registrar_start;
				return trim(substr($whois, $domain_registrar_start, $domain_registrar_stop));
			}
			return false;
			break;
		case 'registrar_url':
			if ( contains($whois, array('Registrar URL: ')) && contains($whois, array('Updated Date: ')) ){
				$domain_registrar_url_start = strpos($whois, "Registrar URL: ")+15;
				$domain_registrar_url_stop = strpos($whois, "Updated Date: ")-$domain_registrar_url_start;
				return trim(substr($whois, $domain_registrar_url_start, $domain_registrar_url_stop));
			} elseif ( contains($whois, array('Registrar URL: ')) && contains($whois, array('Update Date: ')) ){
				$domain_registrar_url_start = strpos($whois, "Registrar URL: ")+15;
				$domain_registrar_url_stop = strpos($whois, "Update Date: ")-$domain_registrar_url_start;
				return trim(substr($whois, $domain_registrar_url_start, $domain_registrar_url_stop));
			} elseif ( contains($whois, array('URL: ')) && contains($whois, array('Relevant dates:')) ){ //co.uk
				$domain_registrar_url_start = strpos($whois, "URL: ")+5;
				$domain_registrar_url_stop = strpos($whois, "Relevant dates: ")-$domain_registrar_url_start;
				return trim(substr($whois, $domain_registrar_url_start, $domain_registrar_url_stop));
			}
			return false;
			break;
		case 'reseller':
		case 'reseller_name':
			$domain_reseller = '';
			if ( contains($whois, array('Reseller: ')) && contains($whois, array('Domain Status: ')) ){
				$reseller1 = strpos($whois, 'Reseller: ');
				$reseller2 = strpos($whois, 'Reseller: ', $reseller1 + strlen('Reseller: '));
				if ( $reseller2 ){
					$domain_reseller_start = strpos($whois, "Reseller: ")+10;
					$domain_reseller_stop = $reseller2-$domain_reseller_start;
					return trim(substr($whois, $domain_reseller_start, $domain_reseller_stop));
				} else {
					$domain_reseller_start = strpos($whois, "Reseller: ")+10;
					$domain_reseller_stop = strpos($whois, "Domain Status: ")-$domain_reseller_start;
					return trim(substr($whois, $domain_reseller_start, $domain_reseller_stop));
				}
			}
			return false;
			break;
	}
}

//Returns WHOIS information from the passed domain.
function getwhois($domain, $tld){
	$override = apply_filters('pre_getwhois', false, $domain, $tld);
	if ( $override !== false ){return $override;}

	if ( empty($domain) ){
		$domain = nebula_url_components('sld'); //Default value is current domain
	}
	if ( empty($tld) ){
		$tld = nebula_url_components('tld'); //Default value is current domain
	}

	require_once(get_template_directory() . "/includes/libs/class-whois.php");
	$whois = new Whois();

	if( !$whois->ValidDomain($domain . '.' . $tld) ){
		return 'Sorry, "' . $domain . '.' . $tld . '" is not valid or not supported.';
	}

	if ( $whois->Lookup($domain . '.' . $tld) ){
		return $whois->GetData(1);
	} else {
		return 'A WHOIS error occurred.';
	}
}

//Compare values using passed parameters
function nebula_compare_operator($a=null, $b=null, $c='=='){
	$override = apply_filters('pre_nebula_compare_operator', false, $a, $b, $c);
	if ( $override !== false ){return $override;}

	if ( empty($a) || empty($b) ){
		trigger_error('nebula_compare_operator requires values to compare.');
		return false;
	}

	switch ( $c ){
        case "=":
        case "==":
        case "e":
        	return $a == $b;
        case ">=":
        case "=>":
        case "gte":
        case "ge":
        	return $a >= $b;
        case "<=":
        case "=<":
        case "lte":
        case "le":
        	return $a <= $b;
        case ">":
        case "gt":
        	return $a > $b;
        case "<":
        case "lt":
        	return $a < $b;
		default:
			trigger_error('nebula_compare_operator does not allow "' . $c . '".');
			return false;
    }
}

//Check the current (or passed) PHP version against the PHP support timeline.
function nebula_php_version_support($php_version=PHP_VERSION){
	$override = apply_filters('pre_nebula_php_version_support', false, $php_version);
	if ( $override !== false ){return $override;}

	$php_timeline_json_file = get_template_directory() . '/includes/data/php_timeline.json';
	$php_timeline = get_transient('nebula_php_timeline');
	if ( empty($php_timeline) || is_debug() ){

		WP_Filesystem();
		global $wp_filesystem;
		$php_timeline = $wp_filesystem->get_contents('https://raw.githubusercontent.com/chrisblakley/Nebula/master/includes/data/php_timeline.json');

		if ( !empty($php_timeline) ){
			$wp_filesystem->put_contents($php_timeline_json_file, $php_timeline); //Store it locally.
			set_transient('nebula_php_timeline', $php_timeline, 60*60*24*30); //1 month cache
		} else {
			$php_timeline = $wp_filesystem->get_contents($php_timeline_json_file);
		}
	}
	$php_timeline = json_decode($php_timeline);

	foreach ( $php_timeline[0] as $php_timeline_version => $php_timeline_dates ){
		if ( version_compare(PHP_VERSION, $php_timeline_version) >= 0 ){
			$output = array();
			if ( !empty($php_timeline_dates->security) && time() < strtotime($php_timeline_dates->security) ){
				$output['lifecycle'] = 'active';
			} elseif ( !empty($php_timeline_dates->security) && (time() >= strtotime($php_timeline_dates->security) && time() < strtotime($php_timeline_dates->end)) ){
				$output['lifecycle'] = 'security';
			} elseif ( time() >= strtotime($php_timeline_dates->end) ) {
				$output['lifecycle'] = 'end';
			} else {
				$output['lifecycle'] = 'unknown'; //An error of some kind has occurred.
			}
			$output['security'] = strtotime($php_timeline_dates->security);
			$output['end'] = strtotime($php_timeline_dates->end);
			return $output;
			break;
		}
	}
}

//Prefer a child theme directory or file. Not declaring a directory will return the theme directory.
function nebula_prefer_child_directory($directory='', $uri=true){
	if ( $directory[0] != '/' ){
		$directory = '/' . $directory;
	}

	if ( file_exists(get_stylesheet_directory() . $directory) ){
		if ( $uri ){
			return get_stylesheet_directory_uri() . $directory;
		} else {
			return get_stylesheet_directory() . $directory;
		}
	} else {
		if ( $uri ){
			return get_template_directory_uri() . $directory;
		} else {
			return get_template_directory() . $directory;
		}
	}
}


//Get Nebula version information
function nebula_version($return=false){
	$override = apply_filters('pre_nebula_version', false, $return);
	if ( $override !== false ){return $override;}

	$nebula_theme_info = ( is_child_theme() )? wp_get_theme(str_replace('-child', '', get_template())) : wp_get_theme();

	$nebula_version_split = explode('.', preg_replace('/[a-zA-Z]/', '', $nebula_theme_info->get('Version')));
	$nebula_version = array(
		'large' => $nebula_version_split[0],
		'medium' => $nebula_version_split[1],
		'small' => $nebula_version_split[2],
		'full' => $nebula_version_split[0] . '.' . $nebula_version_split[1] . '.' . $nebula_version_split[2]
	);

	/*
		May 2016	4.0.x
		June		4.1.x
		July		4.2.x
		August		4.3.x
		Sept		4.4.x
		Oct			4.5.x
		Nov			4.6.x
		Dec			4.7.x
		Jan	2017	4.8.x
		Feb			4.9.x
		Mar			4.10.x
		Apr			4.11.x
		x represents the day of the month.
	*/

	$nebula_version_year = ( $nebula_version['medium'] <= 5 )? 2012+$nebula_version['large'] : 2012+$nebula_version['large']+1;
	$nebula_months = array('July', 'August', 'September', 'October', 'November', 'December', 'January', 'February', 'March', 'April', 'May', 'June'); //Modify this array when 4.0 is released (May is first)
	$nebula_version_month = $nebula_months[$nebula_version['medium']];
	$nebula_version_day = ( empty($nebula_version['small']) )? '' : $nebula_version['small'];
	$nebula_version_day_formated = ( empty($nebula_version['small']) )? ' ' : ' ' . $nebula_version['small'] . ', ';

	$nebula_version_info = array(
		'full' => $nebula_version_split[0] . '.' . $nebula_version_split[1] . '.' . $nebula_version_split[2],
		'large' => $nebula_version_split[0],
		'medium' => $nebula_version_split[1],
		'small' => $nebula_version_split[2],
		'utc' => strtotime($nebula_version_month . $nebula_version_day_formated . $nebula_version_year),
		'date' => $nebula_version_month . $nebula_version_day_formated . $nebula_version_year,
		'year' => $nebula_version_year,
		'month' => $nebula_version_month,
		'day' => $nebula_version_day,
	);

	switch ( str_replace(array(' ', '_', '-'), '', strtolower($return)) ){
		case ('version'):
		case ('full'):
			return $nebula_version_info['full'];
			break;
		case ('date'):
			return $nebula_version_info['date'];
			break;
		case ('time'):
		case ('utc'):
			return $nebula_version_info['utc'];
			break;
		default:
			return $nebula_version_info;
			break;
	}
}

/*==========================
	SCSS Compiling
 ===========================*/

if ( nebula_option('nebula_scss') ){
	if ( is_writable(get_template_directory()) ){
		add_action('init', 'nebula_render_scss');
		add_action('admin_init', 'nebula_render_scss');
	}
}
function nebula_render_scss($specific_scss=null, $child=false){
	$override = apply_filters('pre_nebula_render_scss', false, $specific_scss, $child);
	if ( $override !== false ){return $override;}

	if ( nebula_option('nebula_scss', 'enabled') && (isset($_GET['sass']) || isset($_GET['scss']) || isset($_GET['settings-updated'])) && (is_dev() || is_client()) ){
		$specific_scss = 'all';
	}

	$theme_directory = get_template_directory();
	$theme_directory_uri = get_template_directory_uri();
	if ( $child ){
		$theme_directory = get_stylesheet_directory();
		$theme_directory_uri = get_stylesheet_directory_uri();
	}

	$stylesheets_directory = $theme_directory . '/stylesheets';
	$stylesheets_directory_uri = $theme_directory_uri . '/stylesheets';

	require_once(get_template_directory() . '/includes/libs/scssphp/scss.inc.php'); //SCSSPHP is a compiler for SCSS 3.x
	$scss = new \Leafo\ScssPhp\Compiler();
	$scss->addImportPath($stylesheets_directory . '/scss/partials/');

	if ( nebula_option('nebula_minify_css', 'enabled') && !is_debug() ){
		$scss->setFormatter('Leafo\ScssPhp\Formatter\Compressed'); //Minify CSS (while leaving "/*!" comments for WordPress).
	} else {
		$scss->setFormatter('Leafo\ScssPhp\Formatter\Compact'); //Compact, but readable, CSS lines
		if ( is_debug() ){
			$scss->setLineNumberStyle(\Leafo\ScssPhp\Compiler::LINE_COMMENTS); //Adds line number reference comments in the rendered CSS file for debugging.
		}
	}

	if ( empty($specific_scss) || $specific_scss == 'all' ){
		//Partials
		$latest_partial = 0;
		foreach ( glob($stylesheets_directory . '/scss/partials/*') as $partial_file ){
			if ( filemtime($partial_file) > $latest_partial ){
				$latest_partial = filemtime($partial_file);
			}
		}

		//Combine Developer Stylesheets
		if ( nebula_option('nebula_dev_stylesheets') ){
			nebula_combine_dev_stylesheets($stylesheets_directory, $stylesheets_directory_uri);
		}

		//Compile each SCSS file
		foreach ( glob($stylesheets_directory . '/scss/*.scss') as $file ){ //@TODO "Nebula" 0: Change to glob_r() but will need to create subdirectories if they don't exist.
			$file_path_info = pathinfo($file);

			if ( is_file($file) && $file_path_info['extension'] == 'scss' && $file_path_info['filename'][0] != '_' ){ //If file exists, and has .scss extension, and doesn't begin with "_".
				$css_filepath = ( $file_path_info['filename'] == 'style' )? $theme_directory . '/style.css': $stylesheets_directory . '/css/' . $file_path_info['filename'] . '.css';
				if ( !file_exists($css_filepath) || filemtime($file) > filemtime($css_filepath) || $latest_partial > filemtime($css_filepath) || is_debug() || $specific_scss == 'all' ){ //If .css file doesn't exist, or is older than .scss file (or any partial), or is debug mode, or forced
					ini_set('memory_limit', '512M'); //Increase memory limit for this script. //@TODO "Nebula" 0: Is this the best thing to do here? Other options?
					WP_Filesystem();
					global $wp_filesystem;
					$existing_css_contents = ( file_exists($css_filepath) )? $wp_filesystem->get_contents($css_filepath) : '';

					if ( !strpos(strtolower($existing_css_contents), 'scss disabled') ){ //If the correlating .css file doesn't contain a comment to prevent overwriting
						$this_scss_contents = $wp_filesystem->get_contents($file); //Copy SCSS file contents

						$compiled_css = $scss->compile($this_scss_contents); //Compile the SCSS
						$enhanced_css = nebula_scss_variables($compiled_css); //Compile server-side variables into SCSS

						$wp_filesystem->put_contents($css_filepath, $enhanced_css); //Save the rendered CSS.
					}
				}
			}
		}

		if ( !$child && is_child_theme() ){ //If not in the second (child) pass, and is a child theme.
			nebula_render_scss($specific_scss, true); //Re-run on child theme stylesheets
		}
	} else {
		if ( file_exists($specific_scss) ){ //If $specific_scss is a filepath
			WP_Filesystem();
			global $wp_filesystem;
			$scss_contents = $wp_filesystem->get_contents($specific_scss);

			$compiled_css = $scss->compile($scss_contents); //Compile the SCSS
			$enhanced_css = nebula_scss_variables($compiled_css); //Compile server-side variables into SCSS

			$wp_filesystem->put_contents(str_replace('.scss', '.css', $specific_scss), $enhanced_css); //Save the rendered CSS in the same directory.
		} else { //If $scss_file is raw SCSS string
			$compiled_css = $scss->compile($specific_scss);
			return nebula_scss_variables($compiled_css); //Return the rendered CSS
		}
	}
}

//Combine developer stylesheets
function nebula_combine_dev_stylesheets($directory=null, $directory_uri=null){
	$override = apply_filters('pre_nebula_combine_dev_stylesheets', false, $directory, $directory_uri);
	if ( $override !== false ){return $override;}

	if ( empty($directory) ){
		$directory = get_template_directory() . '/stylesheets';
		$directory_uri = get_template_directory_uri() . "/stylesheets";
	}

	WP_Filesystem();
	global $wp_filesystem;

	$file_counter = 0;
	$partials = array('variables', 'mixins', 'helpers');
	$automation_warning = "/**** Warning: This is an automated file! Anything added to this file manually will be removed! ****/\r\n\r\n";
	$dev_stylesheet_files = glob($directory . '/scss/dev/*css');
	$dev_scss_file = $directory . '/scss/dev.scss';

	if ( !empty($dev_stylesheet_files) || strlen($dev_scss_file) > strlen($automation_warning)+10 ){ //If there are dev SCSS (or CSS) files -or- if dev.scss needs to be reset
		$wp_filesystem->put_contents($directory . '/scss/dev.scss', $automation_warning); //Empty /stylesheets/scss/dev.scss
	}
	foreach ( $dev_stylesheet_files as $file ){
		$file_path_info = pathinfo($file);
		if ( is_file($file) && in_array($file_path_info['extension'], array('css', 'scss')) ){
			$file_counter++;

			//Include partials in dev.scss
			if ( $file_counter == 1 ){
				$import_partials = '';
				foreach ( $partials as $partial ){
					$import_partials .= "@import '" . $partial . "';\r\n";
				}

				$wp_filesystem->put_contents($dev_scss_file, $automation_warning . $import_partials . "\r\n");
			}

			$this_scss_contents = $wp_filesystem->get_contents($file); //Copy file contents
			$empty_scss = ( $this_scss_contents == '' )? ' (empty)' : '';
			$dev_scss_contents = $wp_filesystem->get_contents($directory . '/scss/dev.scss');

			$dev_scss_contents .= "\r\n/* ==========================================================================\r\n   " . 'File #' . $file_counter . ': ' . $directory_uri . "/scss/dev/" . $file_path_info['filename'] . '.' . $file_path_info['extension'] . $empty_scss . "\r\n   ========================================================================== */\r\n\r\n" . $this_scss_contents . "\r\n\r\n/* End of " . $file_path_info['filename'] . '.' . $file_path_info['extension'] . " */\r\n\r\n\r\n";

			$wp_filesystem->put_contents($directory . '/scss/dev.scss', $dev_scss_contents);
		}
	}
	if ( $file_counter > 0 ){
		add_action('wp_enqueue_scripts', function(){
			wp_enqueue_style('nebula-dev_styles', get_template_directory_uri() . '/stylesheets/css/dev.css?c=' . rand(1, 99999), array('nebula-main'), null);
		});
	}
}

//Compile server-side variables into SCSS
function nebula_scss_variables($scss){
	$override = apply_filters('pre_nebula_scss_variables', false, $scss);
	if ( $override !== false ){return $override;}

	$scss = preg_replace("(<%template_directory_uri%>)", get_template_directory_uri(), $scss); //Template Directory
	$scss = preg_replace("(<%stylesheet_directory_uri%>)", get_stylesheet_directory_uri(), $scss); //Stylesheet Directory (For child themes)
	$scss = preg_replace("(" . str_replace('/', '\/', get_template_directory()) . ")", '', $scss); //Reduce theme path for SCSSPHP debug line comments
	$scss = preg_replace("(" . str_replace('/', '\/', get_stylesheet_directory()) . ")", '', $scss); //Reduce theme path for SCSSPHP debug line comments (For child themes)
	$scss = preg_replace("<%__utm.gif%>", ga_UTM_gif(), $scss); //GA __utm.gif pixel with parameters for tracking via CSS
	do_action('nebula_scss_variables');
	$scss .= "\r\n/* Processed on " . date('l, F j, Y \a\t g:ia', time()) . ' */';
	update_option('nebula_scss_last_processed', time());
	return $scss;
}

//Pull certain colors from .../mixins/_variables.scss
function nebula_sass_color($color='primary', $theme='child'){
	$override = apply_filters('pre_nebula_sass_color', false, $color, $theme);
	if ( $override !== false ){return $override;}

	if ( is_child_theme() && $theme == 'child' ){
		$stylesheets_directory = get_stylesheet_directory() . '/stylesheets';
		$transient_name = 'nebula_scss_child_variables';
	} else {
		$stylesheets_directory = get_template_directory() . '/stylesheets';
		$transient_name = 'nebula_scss_variables';
	}

	$scss_variables = get_transient($transient_name);
	if ( empty($menus) || is_debug() ){
		$variables_file = $stylesheets_directory . '/scss/partials/_variables.scss';
		if ( !file_exists($variables_file) ){
			return false;
		}

		WP_Filesystem();
		global $wp_filesystem;
		$scss_variables = $wp_filesystem->get_contents($variables_file);
		set_transient($transient_name, $scss_variables, 60*60); //1 hour cache
	}

	switch ( str_replace(array('$', ' ', '_', '-'), '', $color) ){
		case 'primary':
		case 'primarycolor':
		case 'first':
		case 'main':
		case 'brand':
			$color_search = 'primary_color';
			break;
		case 'secondary':
		case 'secondarycolor':
		case 'second':
			$color_search = 'secondary_color';
			break;
		case 'tertiary':
		case 'tertiarycolor':
		case 'third':
			$color_search = 'tertiary_color';
			break;
		default:
			return false;
			break;
	}

	preg_match('/\$' . $color_search . ': (\S*)(;| !default;)/', $scss_variables, $matches);
	return $matches[1];
}

/*==========================
 User Agent Parsing Functions/Helpers
 ===========================*/

//Boolean return if the user's device is mobile.
function nebula_is_mobile(){
	$override = apply_filters('pre_nebula_is_mobile', false);
	if ( $override !== false ){return $override;}

	if ( $GLOBALS["device_detect"]->isMobile() ){
		return true;
	}
	return false;
}

//Boolean return if the user's device is a tablet.
function nebula_is_tablet(){
	$override = apply_filters('pre_nebula_is_tablet', false);
	if ( $override !== false ){return $override;}

	if ( $GLOBALS["device_detect"]->isTablet() ){
		return true;
	}
	return false;
}

//Boolean return if the user's device is a desktop.
function nebula_is_desktop(){
	$override = apply_filters('pre_nebula_is_desktop', false);
	if ( $override !== false ){return $override;}

	if ( $GLOBALS["device_detect"]->isDesktop() ){
		return true;
	}
	return false;
}

//Returns the requested information of the operating system of the user's device.
function nebula_get_os($info='full'){
	$override = apply_filters('pre_nebula_get_os', false, $info);
	if ( $override !== false ){return $override;}

	$os = $GLOBALS["device_detect"]->getOs();
	switch ( strtolower($info) ){
		case 'full':
			return $os['name'] . ' ' . $os['version'];
			break;
		case 'name':
			return $os['name'];
			break;
		case 'version':
			return $os['version'];
			break;
		default:
			return false;
			break;
	}
}

//Check to see how the operating system version of the user's device compares to a passed version number.
function nebula_is_os($os=null, $version=null, $comparison='=='){
	$override = apply_filters('pre_nebula_is_os', false, $os, $version, $comparison);
	if ( $override !== false ){return $override;}

	if ( empty($os) ){
		trigger_error('nebula_is_os requires a parameter of requested operating system.');
		return false;
	}

	switch ( strtolower($os) ){
		case 'macintosh':
			$os = 'mac';
			break;
		case 'win':
			$os = 'windows';
			break;
	}

	$actual_os = $GLOBALS["device_detect"]->getOs();
	$actual_version = explode('.', $actual_os['version']);
	$version_parts = explode('.', $version);
	if ( strpos(strtolower($actual_os['name']), strtolower($os)) !== false ){
		if ( !empty($version) ){
			if ( nebula_compare_operator($actual_version[0], $version_parts[0], $comparison) ){ //If major version matches
				if ( $version_parts[1] && $version_parts[1] != 0 ){ //If minor version exists and is not 0
					if ( nebula_compare_operator($actual_version[1], $version_parts[1], $comparison) ){ //If minor version matches
						return true;
					} else {
						return false;
					}
				} else {
					return true;
				}
			}
		} else {
			return true;
		}
	}
	return false;
}

//Returns the requested information of the model of the user's device.
function nebula_get_device($info='model'){
	$override = apply_filters('pre_nebula_get_device', false, $info);
	if ( $override !== false ){return $override;}

	$info = str_replace(' ', '', $info);
	switch ( strtolower($info) ){
		case 'full':
			return $GLOBALS["device_detect"]->getBrandName() . ' ' . $GLOBALS["device_detect"]->getModel();
			break;
		case 'brand':
		case 'brandname':
		case 'make':
			return $GLOBALS["device_detect"]->getBrandName();
			break;
		case 'model':
		case 'version':
		case 'name':
			return $GLOBALS["device_detect"]->getModel();
			break;
		case 'type':
			return $GLOBALS["device_detect"]->getDeviceName();
			break;
		case 'formfactor':
			if ( nebula_is_mobile() ){
				return 'mobile';
			} elseif ( nebula_is_tablet() ){
				return 'tablet';
			} else {
				return 'desktop';
			}
		default:
			return false;
			break;
	}
}

//Returns the requested information of the browser being used.
function nebula_get_client($info){ return get_browser($info); }
function nebula_get_browser($info='name'){
	$override = apply_filters('pre_nebula_get_browser', false, $info);
	if ( $override !== false ){return $override;}

	$client = $GLOBALS["device_detect"]->getClient();
	switch ( strtolower($info) ){
		case 'full':
			return $client['name'] . ' ' . $client['version'];
			break;
		case 'name':
		case 'browser':
		case 'client':
			return $client['name'];
			break;
		case 'version':
			return $client['version'];
			break;
		case 'engine':
			return $client['engine'];
			break;
		case 'type':
			return $client['type'];
			break;
		default:
			return false;
			break;
	}
}

//Check to see how the browser version compares to a passed version number.
function nebula_is_browser($browser=null, $version=null, $comparison='=='){
	$override = apply_filters('pre_nebula_is_browser', false, $browser, $version, $comparison);
	if ( $override !== false ){return $override;}

	if ( empty($browser) ){
		trigger_error('nebula_is_browser requires a parameter of requested browser.');
		return false;
	}

	switch ( strtolower($browser) ){
		case 'ie':
			$browser = 'internet explorer';
			break;
		case 'ie7':
			$browser = 'internet explorer';
			$version = '7';
			break;
		case 'ie8':
			$browser = 'internet explorer';
			$version = '8';
			break;
		case 'ie9':
			$browser = 'internet explorer';
			$version = '9';
			break;
		case 'ie10':
			$browser = 'internet explorer';
			$version = '10';
			break;
		case 'ie11':
			$browser = 'internet explorer';
			$version = '11';
			break;
	}

	$actual_browser = $GLOBALS["device_detect"]->getClient();
	$actual_version = explode('.', $actual_browser['version']);
	$version_parts = explode('.', $version);
	if ( strpos(strtolower($actual_browser['name']), strtolower($browser)) !== false ){
		if ( !empty($version) ){
			if ( nebula_compare_operator($actual_version[0], $version_parts[0], $comparison) ){ //Major version comparison
				if ( $version_parts[1] && $version_parts[1] != 0 ){ //If minor version exists and is not 0
					if ( nebula_compare_operator($actual_version[1], $version_parts[1], $comparison) ){ //Minor version comparison
						return true;
					} else {
						return false;
					}
				} else {
					return true;
				}
			}
		} else {
			return true;
		}
	}
	return false;
}

//Check to see if the rendering engine matches a passed parameter.
function nebula_is_engine($engine=null){
	$override = apply_filters('pre_nebula_is_engine', false, $engine);
	if ( $override !== false ){return $override;}

	if ( empty($engine) ){
		trigger_error('nebula_is_engine requires a parameter of requested engine.');
		return false;
	}

	switch ( strtolower($engine) ){
		case 'ie':
		case 'internet explorer':
			$engine = 'trident';
			break;
		case 'web kit':
			$engine = 'webkit';
			break;
	}

	$actual_engine = $GLOBALS["device_detect"]->getClient();
	if ( strpos(strtolower($actual_browser['engine']), strtolower($engine)) !== false ){
		return true;
	}
	return false;
}

//Check for bot/crawler traffic
//UA lookup: http://www.useragentstring.com/pages/Crawlerlist/
function nebula_is_bot(){
	$override = apply_filters('pre_nebula_is_bot', false);
	if ( $override !== false ){return $override;}

	$bots = array('bot', 'crawl', 'spider', 'feed', 'slurp', 'tracker', 'http');
	foreach( $bots as $bot ){
		if ( strpos(strtolower($_SERVER['HTTP_USER_AGENT']), $bot) !== false ){
			return true;
			break;
		}
	}

	if ( $GLOBALS["device_detect"]->isBot() ){ //This might work fine on it's own without the above foreach loop
		return true;
		break;
	}
	return false;
}

//Device Detection v3.3 - https://github.com/piwik/device-detector
//Be careful when updating this library. DeviceDetector.php requires modification to work without Composer!
require_once(get_template_directory() . '/includes/libs/device-detector/DeviceDetector.php');
use DeviceDetector\DeviceDetector;
$GLOBALS["device_detect"] = new DeviceDetector($_SERVER['HTTP_USER_AGENT']);
$GLOBALS["device_detect"]->discardBotInformation(); //If called, getBot() will only return true if a bot was detected (speeds up detection a bit)
$GLOBALS["device_detect"]->parse();