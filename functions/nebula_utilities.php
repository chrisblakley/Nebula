<?php

//Used to detect if plugins are active. Enables use of is_plugin_active($plugin)
require_once(ABSPATH . 'wp-admin/includes/plugin.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');

//Generate Session ID
function nebula_session_id(){
	$session_info = ( is_debug() )? 'dbg.' : '';
	$session_info .= ( nebula_option('prototype_mode', 'enabled') )? 'prt.' : '';

	if ( is_client() ){
		$session_info .= 'cli.';
	} elseif ( is_dev() ){
		$session_info .= 'dev.';
	}

	if ( is_user_logged_in() ){
		$user_info = get_userdata(get_current_user_id());
		$role_abv = 'ukn';
		if ( !empty($user_info->roles) ){
			$role_abv = substr($user_info->roles[0], 0, 3);
		}
		$session_info .= 'u:' . get_current_user_id() . '.r:' . $role_abv . '.';
	}

	$session_info .= ( nebula_is_bot() )? 'bot.' : '';

	$wp_session_id = ( session_id() )? session_id() : '!' . uniqid();
	$ga_cid = ga_parse_cookie();

	$site_live = '';
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
		'utmac' => nebula_option('ga_tracking_id'), //Account string, appears on all requests *** REQUIRED ***
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

	$result = wp_remote_get('https://ssl.google-analytics.com/collect?payload_data&' . http_build_query($data));
	return $result;
}

//Send Pageview Function for Server-Side Google Analytics
function ga_send_pageview($hostname=null, $path=null, $title=null, $array=array()){
	$override = apply_filters('pre_ga_send_pageview', false, $hostname, $path, $title, $array);
	if ( $override !== false ){return $override;}

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
		'v' => 1,
		'tid' => nebula_option('ga_tracking_id'),
		'cid' => ga_parse_cookie(),
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
function ga_send_event($category=null, $action=null, $label=null, $value=null, $ni=1, $array=array()){
	$override = apply_filters('pre_ga_send_event', false, $category, $action, $label, $value, $ni, $array);
	if ( $override !== false ){return $override;}

	//GA Parameter Guide: https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters?hl=en
	//GA Hit Builder: https://ga-dev-tools.appspot.com/hit-builder/
	$data = array(
		'v' => 1,
		'tid' => nebula_option('ga_tracking_id'),
		'cid' => ga_parse_cookie(),
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
		'v' => 1,
		'tid' => nebula_option('ga_tracking_id'),
		'cid' => ga_parse_cookie(),
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
	if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }
	if ( !nebula_is_bot() ){ //Is this conditional preventing this from working at times?
		ga_send_event(sanitize_text_field($_POST['data'][0]['category']), sanitize_text_field($_POST['data'][0]['action']), sanitize_text_field($_POST['data'][0]['label']), sanitize_text_field($_POST['data'][0]['value']), sanitize_text_field($_POST['data'][0]['ni']));
	}
	wp_die();
}

/*==========================
	Nebula Visitor Data
 ===========================*/

//Create Users Table with minimal default columns.
add_action('admin_init', 'nebula_vdb_create_tables');
function nebula_vdb_create_tables(){
	if ( is_admin_page() && isset($_GET['settings-updated']) && is_staff() ){ //Only trigger this in admin when Nebula Options are saved (by a staff member)
		global $wpdb;

		$visitors_table = $wpdb->query("SHOW TABLES LIKE 'nebula_visitors'"); //DB Query here
		if ( empty($visitors_table) ){
			$create_primary_table = $wpdb->query("CREATE TABLE nebula_visitors (
				id INT(11) NOT NULL AUTO_INCREMENT,
				nebula_id VARCHAR(255),
				ga_cid TINYTEXT NOT NULL,
				ip_address TINYTEXT NOT NULL,
				user_agent TEXT NOT NULL,
				fingerprint LONGTEXT,
				nebula_session_id TEXT NOT NULL,
				notable_poi TINYTEXT,
				email_address TINYTEXT,
				is_known BOOLEAN NOT NULL,
				last_seen_on INT(11) NOT NULL,
				last_modified_on INT(11) NOT NULL,
				most_identifiable TINYTEXT,
				lead_score INT(6),
				notes LONGTEXT,
				PRIMARY KEY (id),
				UNIQUE (nebula_id)
			) ENGINE = InnoDB;"); //DB Query here

			//Create the data table
				//Must have unique combination of nebula_id and label
				//References the nebula_id from the primary table, so if a row is deleted from there it deletes all rows with the same nebula_id here.
			$create_data_table = $wpdb->query("CREATE TABLE nebula_visitors_data (
				id INT(11) NOT NULL AUTO_INCREMENT,
				nebula_id VARCHAR(255),
				label VARCHAR(255),
				value LONGTEXT,
				PRIMARY KEY (id),
				UNIQUE (nebula_id, label),
				CONSTRAINT fk_nebula_visitors_nebula_id FOREIGN KEY (nebula_id) REFERENCES nebula_visitors (nebula_id) ON DELETE CASCADE
			)
			ENGINE = InnoDB;"); //DB Query here
		}
	}
}

//The controller for Nebula Visitors DB process.
//Triggering at get_header allows for template_redirects to happen before fingerprinting (prevents false multipageviews)
add_action('get_header', 'nebula_vdb_controller', 11);
function nebula_vdb_controller(){
	$override = apply_filters('pre_nebula_vdb_controller', false);
	if ( $override !== false ){return $override;}

	if ( !nebula_option('visitors_db') || nebula_is_ajax_request() || nebula_is_bot() || strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'wordpress') !== false ){
		return false; //Don't add bots to the DB
	}

	//Only run on front-end
	if ( !is_admin_page() && !is_login_page() ){ //get_header does not trigger on admin pages, but just to be safe.
		$returning_visitor = nebula_vdb_is_returning_visitor();
		$nebula_id = nebula_vdb_get_appropriate_nebula_id($returning_visitor);

		$treat_as_new_visitor = true; //Prep this until detected as returning
		if ( $returning_visitor ){ //Returning Visitor
			$treat_as_new_visitor = false;

			$all_visitor_data = nebula_vdb_get_all_visitor_data('fresh');
			if ( !empty($all_visitor_data) ){
				nebula_vdb_returning_visitor();
			} else {
				$treat_as_new_visitor = true; //Run failsafe because that visitor doesn't have a DB row (even though they are returning)
			}
		}

		if ( $treat_as_new_visitor ){ //New User (or returning with no DB row)
			$built_visitor_data = nebula_vdb_build_new_visitor_data_object(); //Run procedure for new user.

			if ( $returning_visitor ){ //This is true if visitor is returning, but did not have a DB row (so must be inserted as new).
				$built_visitor_data['is_first_session'] = 0;
				$built_visitor_data['is_new_user'] = 0;
				$built_visitor_data['is_returning_user'] = 1;
			}

			nebula_vdb_insert_visitor($built_visitor_data);
		}

		//Check if this visitor is similar to any known visitors (if not already)
		if ( !nebula_vdb_get_visitor_datapoint('similar_to_known_ip_ua') ){
			$similar_ip_known_visitor = nebula_vdb_similar_to_known(); //If current visitor IP is similar to a known visitor
			if ( !empty($similar_ip_known_visitor) ){
				$similar_ip_ua_known_visitor = nebula_vdb_similar_to_known(true); //If current visitor IP + User Agent is similar to a known visitor
				if ( !empty($similar_ip_ua_known_visitor) ){
					nebula_vdb_update_visitor_data(array('similar_to_known_ip_ua' => $similar_ip_ua_known_visitor), false);
				}

				nebula_vdb_update_visitor_data(array('similar_to_known_ip' => $similar_ip_known_visitor));
			}
		}

		nebula_vdb_remove_expired();
	}
}

//Check if a certain label is protected from manual changes
//Add more labels using this example hook:
/*
	add_filter('nebula_vdb_protected_labels', 'project_protected_labels');
	function project_protected_labels($array){
	    array_push($array, 'my_label_1', 'another_example_label', 'and_so_on'));
	    return $array;
	}
*/
function nebula_vdb_is_protected_label($label){
	$default_protected_labels = array('id', 'nebula_id', 'ga_cid', 'lead_score', 'demographic_score', 'behavior_score');

	$additional_protected_labels = apply_filters('nebula_vdb_protected_labels', array());
	$all_protected_labels = array_merge($default_protected_labels, $additional_protected_labels);

	if ( in_array($label, $all_protected_labels) ){
		return true;
	}

	return false;
}

//Procedure for new or returning visitors
function nebula_vdb_returning_visitor(){
	if ( nebula_vdb_is_same_session() ){ //Same Session
		$session_start_time = nebula_vdb_get_visitor_datapoint('current_session_on');

		$batch_update_data = nebula_vdb_update_visitor_data(array(
			'current_session_duration' => ( !empty($session_start_time) )? round(time()/60)*60-$session_start_time : 0,
			'last_seen_on' => round(time()/60)*60,
			'is_non_bounce' => '1',
		), false);

		if ( nebula_vdb_is_page_refresh() ){
			$batch_update_data = nebula_vdb_increment_visitor_data('total_refreshes', false);
		} else {
			$batch_update_data = nebula_vdb_increment_visitor_data(array('current_session_pageviews', 'total_pageviews'), false);

			$batch_update_data = nebula_vdb_append_visitor_data(array(
				'last_page_viewed' => nebula_url_components('all'),
				'all_ip_addresses' => sanitize_text_field($_SERVER['REMOTE_ADDR']),
				'all_notable_pois' => nebula_poi(),
				'acquisition_channel' => nebula_vdb_detect_acquisition_channel(),
				'referrer' => ( nebula_vdb_is_external_referrer() )? $_SERVER['HTTP_REFERER'] : '',
			), false);
		}

		nebula_vdb_send_all_to_cache_and_db($batch_update_data); //Update everything as batch here
	} else { //New Session
		//Calculate time since last session
		$last_session_date = nebula_vdb_get_visitor_datapoint('prev_sessions');
		$time_since_last_session = time()-$last_session_date;
		$batch_update_data = nebula_vdb_update_visitor_data(array(
			'current_session_pageviews' => 0,
			'is_first_session' => 0,
			'is_new_user' => 0,
			'is_returning_user' => 1,
			'time_since_last_session' => $time_since_last_session,
			'last_seen_on' => round(time()/60)*60,
			'acquisition_keywords' => get_referrer_search_terms(),
			'is_homescreen_app' => ( isset($_GET['hs']) )? '1' : false, //Don't set false condition to prevent overwriting.
		), false);
		$batch_update_data = nebula_vdb_append_visitor_data(array(
			'prev_session_on' => round(time()/60)*60, //Rounded to the nearest minute
			'acquisition_channel' => nebula_vdb_detect_acquisition_channel(),
			'referrer' => ( nebula_vdb_is_external_referrer() )? $_SERVER['HTTP_REFERER'] : '',
		), false);
		$batch_update_data = nebula_vdb_increment_visitor_data('session_count', false);

		nebula_vdb_send_all_to_cache_and_db($batch_update_data); //Update everything as batch here
	}
}

//Detect acquisition method
function nebula_vdb_detect_acquisition_channel(){
	//Check for campaign URL
	if ( isset($_GET['utm_campaign']) ){ //utm_campaign
		return 'Campaign (' . $_GET['utm_campaign'] . ')';
	}

	if ( nebula_vdb_is_external_referrer() ){
		$hostname = nebula_url_components('host', $_SERVER['HTTP_REFERER']);

		//Check Email
		if ( nebula_vdb_is_email_referrer($_SERVER['HTTP_REFERER']) ){
			return 'Email';
		}

		//Check social
		if ( nebula_vdb_is_social_network($_SERVER['HTTP_REFERER']) ){
			return 'Social (' . $hostname . ')';
		}

		//Check search
		if ( nebula_vdb_is_search_engine($_SERVER['HTTP_REFERER']) ){
			return 'Search (' . $hostname . ')';
		}

		return 'Referral (' . $hostname . ')';
	}

	return 'Direct (or Unknown)';
}

//Check if referrer is an email app
function nebula_vdb_is_email_referrer($referrer=false){
	if ( nebula_url_components('protocol', $referrer) == 'android-app' ){ //Gmail App on Android
		return true;
	}

	if ( isset($_GET['utm_medium']) && strtolower($_GET['utm_medium']) == 'email' ){ //Email campaigns
		return true;
	}

	return false;
}

//Check if hostname is a social network
function nebula_vdb_is_social_network($url){
	$sld = nebula_url_components('sld', $url);

	$social_hostnames = array('facebook.com', 'twitter.com', 't.co', 'linkedin.com', 'reddit.com', 'plus.google.com', 'pinterest.com', 'tumblr.com', 'digg.com', 'stumbleupon.com', 'yelp.com');
    foreach ( $social_hostnames as $social_hostname ){
		if ( preg_match('/^' . $sld . '\./', $social_hostname) ){
			return true;
		}
	}

	return false;
}

//Check if hostname is a seach engine
function nebula_vdb_is_search_engine($url){
	$sld = nebula_url_components('sld', $url);

	$search_engine_hostnames = array('google.com', 'bing.com', 'yahoo.com', 'baidu.com', 'aol.com', 'ask.com', 'excite.com', 'duckduckgo.com', 'yandex.com', 'lycos.com', 'chacha.com');
    foreach ( $search_engine_hostnames as $search_engine_hostname ){
		if ( preg_match('/^' . $sld . '\./', $search_engine_hostname) ){
			return true;
		}
	}

	return false;
}

//Check for external referrer
function nebula_vdb_is_external_referrer(){
	if ( !empty($_SERVER['HTTP_REFERER']) && nebula_url_components('domain', $_SERVER['HTTP_REFERER']) != nebula_url_components('domain') ){
		return true;
	}

	return false;
}

//Figure out which nebula ID to use in priority order
function nebula_vdb_get_appropriate_nebula_id($check_db=true){
	//From global variable
	if ( !empty($GLOBALS['nebula_id']) ){
		return $GLOBALS['nebula_id'];
	}

	//From Session
	if ( isset($_SESSION) && !empty($_SESSION['nebula_id']) ){
		nebula_vdb_force_nebula_id_to_visitor($_SESSION['nebula_id']);
		return $_SESSION['nebula_id'];
	}

	//From Cookie
	$nebula_id_from_cookie = nebula_vdb_get_nebula_id_from_cookie();
	if ( !empty($nebula_id_from_cookie) ){
		nebula_vdb_force_nebula_id_to_visitor($nebula_id_from_cookie);
		return $nebula_id_from_cookie;
	}

	//From DB (by matching GA CID)
	if ( $check_db && nebula_option('visitors_db') ){ //Only run if confirmed returning visitor
		$nebula_id_from_ga_cid = nebula_vdb_get_previous_nebula_id_by_ga_cid();
		if ( !empty($nebula_id_from_ga_cid) ){
			nebula_vdb_force_nebula_id_to_visitor($nebula_id_from_ga_cid);
			return $nebula_id_from_ga_cid;
		}
	}

	//Generate a new Nebula ID
	$generated_nebula_id = nebula_vdb_generate_nebula_id();
	nebula_vdb_force_nebula_id_to_visitor($generated_nebula_id);
	return $generated_nebula_id;
}

//Check if the visitor is returning
function nebula_vdb_is_returning_visitor(){
	//From global variable
	if ( !empty($GLOBALS['nebula_id']) ){
		return true;
	}

	//From Session
	if ( isset($_SESSION) && !empty($_SESSION['nebula_id']) ){
		return true;
	}

	//From Cookie
	$nebula_id_from_cookie = nebula_vdb_get_nebula_id_from_cookie();
	if ( !empty($nebula_id_from_cookie) ){
		return true;
	}

	//From DB
	if ( nebula_option('visitors_db') ){
		global $wpdb;

		//Check for an old visitor with the same fingerprint
		//@TODO "Nebula" 0: I don't think the server-side fingerprint alone is unique enough to push this live... Really needs the JS detections to work, but I can't think of a way that isn't AJAX JS or a second pageview to match against it...
		$unique_new_visitor = $wpdb->get_results($wpdb->prepare("SELECT ga_cid FROM nebula_visitors WHERE (ip_address = '" . sanitize_text_field($_SERVER['REMOTE_ADDR']) . "' AND user_agent = '" . sanitize_text_field($_SERVER['HTTP_USER_AGENT']) . "') OR fingerprint LIKE '%" . sanitize_text_field(nebula_vdb_fingerprint()) . "%'")); //DB Query here
		if ( !empty($unique_new_visitor) ){

			//@TODO "Nebula" 0: This uses the first result... We want to find a user that is known, or that has a GA CID, else highest score.
			$unique_new_visitor = (array) $unique_new_visitor[0];

			nebula_vdb_force_nebula_id_to_visitor($unique_new_visitor['nebula_id']); //Give this user the same Nebula ID
			nebula_vdb_append_visitor_data(array('notices' => 'This user was tracked by fingerprint.'), false);
			return true;
		}
	}

	return false;
}

//Check if the GA CID was generated by Nebula (instead of Google Analytics)
function nebula_vdb_is_nebula_generated_cid(){
	if ( strpos(ga_parse_cookie(), '-') !== false ){
		return true;
	}

	return false;
}

//Store the Nebula ID in a cookie
function nebula_vdb_force_nebula_id_to_visitor($forced_nebula_id){
	//Global
	$GLOBALS['nebula_id'] = $forced_nebula_id;

	//Session
	$_SESSION['nebula_id'] = $forced_nebula_id;

	//Cookie
	$_COOKIE['nid'] = $forced_nebula_id;
	$nid_expiration = strtotime('January 1, 2035'); //Note: Do not let this cookie expire past 2038 or it instantly expires.
	if ( !headers_sent() ){
		setcookie('nid', $forced_nebula_id, $nid_expiration, COOKIEPATH, COOKIE_DOMAIN); //Store the Nebula ID as a cookie called "nid".
	}

	return true;
}

//Generate a new Nebula ID
function nebula_vdb_generate_nebula_id(){
	return nebula_version('full') . '.' . bin2hex(openssl_random_pseudo_bytes(5)) . '.' . uniqid(); //Update to random_bytes() when moving to PHP7
}

//Check if the ga_cid exists, and if so use THAT nebula_id again
function nebula_vdb_get_previous_nebula_id_by_ga_cid(){
	if ( nebula_option('visitors_db') ){
		global $wpdb;
		$nebula_id_from_matching_ga_cid = $wpdb->get_results($wpdb->prepare("SELECT nebula_id FROM nebula_visitors WHERE ga_cid = '%s'", ga_parse_cookie())); //DB Query here.
		if ( !empty($nebula_id_from_matching_ga_cid) ){
			return reset($nebula_id_from_matching_ga_cid[0]);
		}
	}

	return false;
}

//Return the Nebula ID (or false)
function nebula_vdb_get_nebula_id_from_cookie(){
	if ( isset($_COOKIE['nid']) ){
		return htmlentities(preg_replace('/[^a-zA-Z0-9\.]+/', '', $_COOKIE['nid']));
	}

	return false;
}

//Get all the visitor data at once as an object from the DB
function nebula_vdb_get_all_visitor_data($storage_type=false, $alt_nebula_id=false){
	if ( nebula_option('visitors_db') ){
		//Get data from cache
		if ( empty($alt_nebula_id) ){
			$cached_visitor_data = wp_cache_get('nebula_visitor');
			if ( $cached_visitor_data && $storage_type != 'fresh' ){ //If the data cache exists and not forcing fresh data
				return $cached_visitor_data;
			}
		}

		//Get data from Database
		if ( $storage_type != 'cache' ){
			$nebula_id = ( !empty($alt_nebula_id) )? $alt_nebula_id : nebula_vdb_get_appropriate_nebula_id();

			global $wpdb;
			$all_visitor_db_data = $wpdb->get_results("SELECT id, label, value FROM nebula_visitors_data WHERE nebula_id = '" . sanitize_text_field($nebula_id) . "' ORDER BY id"); //DB Query here

			if ( $all_visitor_db_data ){
				//Re-organize the data
				$organized_data = array();
				foreach ( $all_visitor_db_data as $index => $value ){
					$label = $all_visitor_db_data[$index]->label;

					$unserialized_value = $all_visitor_db_data[$index]->value;
					if ( is_serialized($unserialized_value) ){
						$unserialized_value = unserialize($unserialized_value);
					}
					$organized_data[$label] = $unserialized_value;
				}

				if ( empty($alt_nebula_id) ){
					wp_cache_set('nebula_visitor', $organized_data); //Cache the result (but not for other alternate Nebula IDs)
					wp_cache_set('nebula_visitor_old', $organized_data);
				}

				return $organized_data;
			}
		}
	}

	return false;
}

//Check if continuing the same session
function nebula_vdb_is_same_session(){
	//Check for external referrer
	if ( nebula_vdb_is_external_referrer() && !nebula_vdb_is_page_refresh() ){
		return false;
	}

	//Check if last session ID matches current session ID
	$last_session_id = nebula_vdb_get_visitor_datapoint('last_session_id');
	if ( $last_session_id == session_id() ){
		return true;
	}

	return false;
}

//Check if the page was refreshed
function nebula_vdb_is_page_refresh(){
	$last_page_viewed = nebula_vdb_get_visitor_datapoint('last_page_viewed');
	if ( $last_page_viewed == nebula_url_components('all') ){
		return true;
	}

	return false;
}

//Retrieve User Data
add_action('wp_ajax_nebula_vdb_ajax_get_visitor_data', 'nebula_vdb_ajax_get_visitor_data');
add_action('wp_ajax_nopriv_nebula_vdb_ajax_get_visitor_data', 'nebula_vdb_ajax_get_visitor_data');
function nebula_vdb_ajax_get_visitor_data(){
	if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') || !nebula_option('visitors_db') ){ die('Permission Denied.'); }
	$key = sanitize_text_field($_POST['data']);
	echo json_encode(nebula_vdb_get_visitor_datapoint($key));
	wp_die();
}

//Get a single datapoint from the Nebula Visitors DB
function nebula_vdb_get_visitor_datapoint($key, $return_all=false, $alt_nebula_id=false){
	if ( nebula_option('visitors_db') ){
		if ( $key == 'notes' ){
			return false;
		}

		$all_visitor_data = nebula_vdb_get_all_visitor_data('any', $alt_nebula_id);
		if ( isset($all_visitor_data) && !empty($all_visitor_data[$key]) ){
			if ( is_array($all_visitor_data[$key]) ){ //If this datapoint is an array (for appended data)
				if ( $return_all ){ //If requesting all datapoints
					return $all_visitor_data[$key];
				}

				return end($all_visitor_data[$key]); //Otherwise, return only the last datapoint
			}

			return $all_visitor_data[$key];
		}
	}

	return false;
}

//Update Visitor Data
add_action('wp_ajax_nebula_vdb_ajax_update_visitor', 'nebula_vdb_ajax_update_visitor');
add_action('wp_ajax_nopriv_nebula_vdb_ajax_update_visitor', 'nebula_vdb_ajax_update_visitor');
function nebula_vdb_ajax_update_visitor(){
	if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') || !nebula_option('visitors_db') ){ die('Permission Denied.'); }
	$data = $_POST['data'];
	echo json_encode(nebula_vdb_update_visitor_data($data));
	wp_die();
}

//Update the Nebula visitor data
function nebula_vdb_update_visitor_data($data=array(), $update_db=true, $alt_nebula_id=false){
	if ( nebula_option('visitors_db') ){
		if ( is_string($data) ){
			$data = array($data);
		}

		if ( empty($alt_nebula_id) ){
			$all_data = nebula_vdb_update_data_everytime(nebula_vdb_get_all_visitor_data());
		} else {
			$all_data = nebula_vdb_get_all_visitor_data('any', $alt_nebula_id);
			$all_data['most_identifiable'] = nebula_vdb_most_identifiable_label($all_data, false, $alt_nebula_id);
		}

		if ( is_array($data) ){
			foreach ( $data as $key => $value ){
				$value = str_replace(array("\r", "\n"), ' ', $value);
				$all_data[$key] = $value;
			}

			$all_data['last_modified_on'] = time();
		}

		if ( empty($alt_nebula_id) ){
			wp_cache_set('nebula_visitor', $all_data); //Cache the result
		} else {
			$scores = nebula_vdb_calculate_scores($all_data);
			$all_data['behavior_score'] = $scores['behavior'];
			$all_data['demographic_score'] = $scores['demographic'];
			$all_data['lead_score'] = $scores['lead'];
		}

		//Update the database (else presume it will update in batch later)
		if ( $update_db ){
			$rows_updated = nebula_vdb_send_all_to_cache_and_db($all_data, $alt_nebula_id);
		}

		if ( !empty($alt_nebula_id) ){
			return $rows_updated;
		}

		return $all_data;
	}

	return false;
}

//Actually send the updated data to the database
function nebula_vdb_send_all_to_cache_and_db($all_data, $alt_nebula_id=false){ //This does like 4 db queries...
	if ( nebula_option('visitors_db') ){
		if ( empty($all_data) ){
			trigger_error('nebula_vdb_send_all_to_cache_and_db() requires all data to be passed as a parameter!', E_USER_ERROR);
			return false;
		}

		if ( empty($alt_nebula_id) ){
			wp_cache_set('nebula_visitor', $all_data); //Cache the result
		}

		$nebula_id = ( !empty($alt_nebula_id) )? $alt_nebula_id : nebula_vdb_get_appropriate_nebula_id();

		global $wpdb;

		//Update Primary table first
		$updated_primary_table = $wpdb->update('nebula_visitors', nebula_vdb_prep_primary_table_for_db($all_data), array('nebula_id' => $nebula_id)); //DB Query here

		//Insert new rows first
		$old_visitor_data = wp_cache_get('nebula_visitor_old');
		if ( empty($alt_nebula_id) && !empty($old_visitor_data) ){
			//Diff against old data
			$old_labels = array_keys($old_visitor_data);
			$new_labels = array_keys($all_data);
			$non_existing_labels = array_diff($new_labels, $old_labels);

			if ( !empty($non_existing_labels) ){
				nebula_vdb_insert_visitor($all_data, $alt_nebula_id);
				wp_cache_set('nebula_visitor_old', $all_data);
			}
		} else {
			//Just insert everything
			nebula_vdb_insert_visitor($all_data, $alt_nebula_id);
		}

		$updated_data = array();
		foreach ( $all_data as $label => $value ){
			$value = str_replace(array("\r", "\n"), ' ', $value);

			if ( is_null($value) || (is_string($value) && trim($value) == '') ){ //Don't use empty() here because we want to include data set to false
				continue;
			}

			if ( is_array($value) ){
				$value = serialize($value);
			}

			if ( !empty($old_visitor_data) ){
				if ( !empty($old_visitor_data[$label]) && $old_visitor_data[$label] == $value ){
					continue;
				}
			}

			$updated_data[$label] = $value;
		}

		//Update existing rows
		$update_query = "UPDATE nebula_visitors_data SET value = CASE";
		foreach ( $updated_data as $label => $value ){
			$update_query .= " WHEN label = '" . $label . "' THEN '" . $value . "'";
		}
		$update_query .= " END WHERE label IN (";

		foreach ( $updated_data as $label => $value ){
			$update_query .= "'" . $label . "',";
		}
		$update_query = rtrim($update_query, ',');
		$update_query .= ") AND nebula_id = '" . $nebula_id . "';";

		$updated_visitor = $wpdb->query($update_query); //DB Query here
		if ( $updated_visitor !== false ){
			if ( $updated_visitor > 0 ){ //If 1 or more rows were updated
				if ( nebula_vdb_is_known($all_data, $alt_nebula_id) ){
					//@TODO "Nebula" 0: Run known visitor procedure... What is it going to be? Maybe a do_action (with parameter of all data)?
				}
			}
		}

		return $updated_visitor; //Return how many rows updated or false if error
	}

	return false;
}

//Append to Visitor Data
add_action('wp_ajax_nebula_vdb_ajax_append_visitor', 'nebula_vdb_ajax_append_visitor');
add_action('wp_ajax_nopriv_nebula_vdb_ajax_append_visitor', 'nebula_vdb_ajax_append_visitor');
function nebula_vdb_ajax_append_visitor(){
	if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') || !nebula_option('visitors_db') ){ die('Permission Denied.'); }
	$data = $_POST['data']; //json_decode(stripslashes()); but its already an array... why?

	echo json_encode(nebula_vdb_append_visitor_data($data));
	wp_die();
}

//Append data to any existing data in the Nebula Visitor DB
function nebula_vdb_append_visitor_data($data=array(), $update_db=true){
	if ( nebula_option('visitors_db') ){
		if ( !is_array($data) ){
			trigger_error("nebula_vdb_append_visitor_data() expects data to be passed as an array of key => value pairs.", E_USER_ERROR);
			return false;
		}

		$all_data = nebula_vdb_update_data_everytime(nebula_vdb_get_all_visitor_data());

		//Add data here to append everytime (if different than prior)
		//IP Geolocation
		$ip_geolocation = nebula_ip_location('all');
		if ( !empty($ip_geolocation) ){
			$data['ip_geo'] = sanitize_text_field($ip_geolocation->city) . ', ' . sanitize_text_field($ip_geolocation->region_name) . ' ' . sanitize_text_field($ip_geolocation->zip_code) . ', ' . sanitize_text_field($ip_geolocation->country_name) . ' (' . sanitize_text_field($_SERVER['REMOTE_ADDR']) . ')';
		}

		foreach ( $data as $key => $value ){
			$value = str_replace(array("\r", "\n"), ' ', $value);

			if ( !empty($value) ){ //Skip empty values
				if ( !empty($all_data[$key]) ){ //The key exists
					if ( !is_array($all_data[$key]) ){
						$all_data[$key] = array($all_data[$key]); //Existing value is a string. Convert it to an array.
					}

					//If next value is one letter more than the previous value, replace the previous with the new.
					if ( end($all_data[$key]) == substr($value, 0, -1) ){
						array_pop($all_data[$key]);
						array_push($all_data[$key], $value);
					}

					if ( in_array($value, $all_data[$key]) ){ //Value already exists in the array
						$existing_index = array_search($value, $all_data[$key]);
						unset($all_data[$key][$existing_index]); //Remove the old data from the array.
						array_push($all_data[$key], $value); //Append the data to the end of the array.
						$all_data[$key] = array_values($all_data[$key]); //Rebase the indexes of the array after unsetting (so it doesn't skip numbers)
					} else {
						array_push($all_data[$key], $value); //Append the data to the end of the array since it wasn't in there yet.
					}

					//If this datapoint has an array longer than 20, remove oldest entries
					$datapoint_size = count($all_data[$key]);
					if ( $datapoint_size > 20 ){
						$all_data[$key] = array_slice($all_data[$key], ($datapoint_size-20)); //Keep last 20 indexes
					}
				} else { //Key does not exist
					$all_data[$key] = $value; //Add value as a string
				}
			}
		}

		wp_cache_set('nebula_visitor', $all_data); //Cache the result

		if ( $update_db ){
			nebula_vdb_send_all_to_cache_and_db($all_data);
		}

		return $all_data;
	}

	return false;
}

//Remove data from the Nebula Visitor DB
add_action('wp_ajax_nebula_vdb_ajax_remove_datapoint', 'nebula_vdb_ajax_remove_datapoint');
add_action('wp_ajax_nopriv_nebula_vdb_ajax_remove_datapoint', 'nebula_vdb_ajax_remove_datapoint');
function nebula_vdb_ajax_remove_datapoint(){
	if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') || !nebula_option('visitors_db') ){ die('Permission Denied.'); }

	nebula_vdb_remove_visitor_data($_POST['data']);
	wp_die();
}

//Remove data from the Nebula Visitor DB
function nebula_vdb_remove_visitor_data($data, $update_db=true){
	if ( nebula_option('visitors_db') ){
		if ( is_string($data) ){
			nebula_vdb_update_visitor_data(array($data => false), false); //Remove the entire label
		} else {
			foreach ( $data as $label => $value ){
				if ( !is_string($label) ){
					$batch_update_data = nebula_vdb_update_visitor_data(array($value => false), false); //Remove the entire label
				} else {
					$db_datapoint = nebula_vdb_get_visitor_datapoint($label, true);

					if ( is_string($db_datapoint) ){
						if ( $db_datapoint == $value ){
							$batch_update_data = nebula_vdb_update_visitor_data(array($value => false), false); //Remove the entire label
						}
					} else {
						$matched_index = array_search($value, $db_datapoint);

						if ( $matched_index !== false ){
							unset($db_datapoint[$matched_index]); //Remove just this index
							$batch_update_data = nebula_vdb_update_visitor_data(array($label => $db_datapoint), false);
						}
					}
				}
			}
		}

		return nebula_vdb_update_visitor_data($batch_update_data, $update_db);
	}

	return false;
}

//Increment Visitor Data
add_action('wp_ajax_nebula_vdb_ajax_increment_visitor', 'nebula_vdb_ajax_increment_visitor');
add_action('wp_ajax_nopriv_nebula_vdb_ajax_increment_visitor', 'nebula_vdb_ajax_increment_visitor');
function nebula_vdb_ajax_increment_visitor(){
	if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') || !nebula_option('visitors_db') ){ die('Permission Denied.'); }
	$data = $_POST['data'];
	echo json_encode(nebula_vdb_increment_visitor_data($data));
	wp_die();
}

//Increment a datapoint in the Nebula Visitor DB
function nebula_vdb_increment_visitor_data($datapoints=array(), $update_db=true){
	if ( nebula_option('visitors_db') ){
		if ( is_string($datapoints) ){
			$datapoints = array($datapoints);
		}

		$all_data = nebula_vdb_update_data_everytime(nebula_vdb_get_all_visitor_data());

		foreach ( $datapoints as $key ){
			if ( array_key_exists($key, $all_data) && !empty($all_data[$key]) ){
				if ( !is_int($all_data[$key]) ){ //Data is not an integer. Try to parse it...
					$all_data[$key] = intval($all_data[$key]);
				}

				if ( is_int($all_data[$key]) ){ //If it is an integer, increment it.
					$all_data[$key]++;
				}
			} else { //Current number either doesn't exist or is not an integer
				$all_data = nebula_vdb_update_visitor_data(array($key => 1), $update_db);
			}
		}

		wp_cache_set('nebula_visitor', $all_data); //Cache the result
		$updated_visitor_data = nebula_vdb_update_visitor_data($all_data, $update_db);

		return $updated_visitor_data;
	}

	return false;
}

//Build a set of data for a brand new visitor
function nebula_vdb_build_new_visitor_data_object($new_data=array()){
	if ( nebula_option('visitors_db') ){
		$defaults = array(
			'nebula_id' => nebula_vdb_get_appropriate_nebula_id(),
			'ga_cid' => ga_parse_cookie(), //Will be UUID on first visit then followed up with actual GA CID via AJAX (if available)
			'is_known' => '0',
			'ip_address' => sanitize_text_field($_SERVER['REMOTE_ADDR']),
			'all_ip_addresses' => sanitize_text_field($_SERVER['REMOTE_ADDR']),
			'notable_poi' => nebula_poi(),
			'all_notable_pois' => nebula_poi(),
			'created_on' => time(),
			'lead_score' => 0,
			'demographic_score' => 0,
			'behavior_score' => 0,
			'score_mod' => 0,
			'referrer' => ( nebula_vdb_is_external_referrer() )? $_SERVER['HTTP_REFERER'] : '',
			'acquisition_channel' => nebula_vdb_detect_acquisition_channel(),
			'acquisition_keywords' => get_referrer_search_terms(),
			'is_homescreen_app' => ( isset($_GET['hs']) )? '1' : false,
			'is_first_session' => '1',
			'is_new_user' => '1',
			'is_returning_user' => '0',
			'session_count' => 1,
			'prev_session_on' => round(time()/60)*60, //Rounded to the nearest minute
			'last_session_id' => session_id(),
			'last_seen_on' => round(time()/60)*60, //Rounded to the nearest minute
			'nebula_session_id' => nebula_session_id(),
			'current_session_on' => round(time()/60)*60, //Rounded to the nearest minute
			'current_session_duration' => 0,
			'current_session_pageviews' => 1,
			'total_pageviews' => 1,
			'landing_page' => nebula_url_components('all'),
			'last_page_viewed' => nebula_url_components('all'),
			'notes' => '',
			'notices' => '',
		);

		//Attempt to detect IP Geolocation data using https://freegeoip.net/
		$ip_geolocation = nebula_ip_location('all');
		if ( !empty($ip_geolocation) ){
			$defaults['ip_country'] = sanitize_text_field($ip_geolocation->country_name);
			$defaults['ip_region'] = sanitize_text_field($ip_geolocation->region_name);
			$defaults['ip_city'] = sanitize_text_field($ip_geolocation->city);
			$defaults['ip_zip'] = sanitize_text_field($ip_geolocation->zip_code);
			$defaults['ip_time_zone'] = sanitize_text_field($ip_geolocation->time_zone);
			$defaults['ip_geo'] = sanitize_text_field($ip_geolocation->city) . ', ' . sanitize_text_field($ip_geolocation->region_name) . ' ' . sanitize_text_field($ip_geolocation->zip_code) . ', ' . sanitize_text_field($ip_geolocation->country_name) . ' (' . sanitize_text_field($_SERVER['REMOTE_ADDR']) . ')';

			$local_time = new DateTime('now', new DateTimeZone($ip_geolocation->time_zone));
			if ( !empty($local_time) ){
				$defaults['ip_time_zone_offset'] = $local_time->format('P');
				$defaults['ip_local_time'] = $local_time->format('l, F j, Y @ g:ia');
			}
		}

		$all_data = nebula_vdb_update_data_everytime($defaults); //Add any passed data

		wp_cache_set('nebula_visitor', $all_data); //Cache the result
		return $all_data;
	}

	return false;
}

//Pull search queries from referrer
function get_referrer_search_terms(){
	if ( nebula_vdb_is_external_referrer() && nebula_vdb_is_search_engine($_SERVER['HTTP_REFERER']) ){
		//Google
		if ( strpos($_SERVER['HTTP_REFERER'], 'google') && strpos($_SERVER['HTTP_REFERER'], 'q=') !== false ){
			$search_term = substr($_SERVER['HTTP_REFERER'], strpos($_SERVER['HTTP_REFERER'], 'q=')); //Remove everything before q=
			$search_term = substr($search_term, 2); //Remove q=

			//Remove everything after next &
			if ( strpos($search_term, '&') ){
				$search_term = substr($search_term, 0, strpos($search_term, '&'));
			}

			return urldecode($search_term);
		}
	}

	return false;
}

//Prep data for the primary table
function nebula_vdb_prep_primary_table_for_db($all_data){
	if ( empty($all_data) ){
		trigger_error('nebula_vdb_prep_primary_table_for_db() requires all data to be passed as a parameter!', E_USER_ERROR);
		return false;
	}

	return array(
		'nebula_id' => $all_data['nebula_id'],
		'ga_cid' => $all_data['ga_cid'],
		'ip_address' => $all_data['ip_address'],
		'user_agent' => $all_data['user_agent'],
		'fingerprint' => $all_data['fingerprint'],
		'nebula_session_id' => $all_data['nebula_session_id'],
		'notable_poi' => $all_data['notable_poi'],
		'email_address' => $all_data['email_address'],
		'is_known' => $all_data['is_known'],
		'last_seen_on' => $all_data['last_seen_on'],
		'last_modified_on' => $all_data['last_modified_on'],
		'most_identifiable' => $all_data['most_identifiable'],
		'lead_score' => $all_data['lead_score'],
		'notes' => $all_data['notes'],
	);
}

//Insert visitor into table with all default detections
function nebula_vdb_insert_visitor($data, $alt_nebula_id=false){
	if ( nebula_option('visitors_db') && !empty($data) ){
		//Primary table prep
		$prepped_primary_data = nebula_vdb_prep_primary_table_for_db($data);

		global $wpdb;
		$inserted_primary_table = $wpdb->replace('nebula_visitors', $prepped_primary_data); //Insert a row with all the default (and passed) sanitized values. DB Query here

		//Data table prep
		$nebula_id = ( !empty($alt_nebula_id) )? $alt_nebula_id : nebula_vdb_get_appropriate_nebula_id();

		//Build columns
		foreach ( $data as $label => $value ){
			if ( is_array($value) ){
				$value = serialize($value);
			}

			$built_values[] = array(
				"'" . $nebula_id . "'",
				"'" . $label . "'",
				"'" . $value . "'"
			);
		}

		//Build rows
		$built_insert_query = "INSERT IGNORE INTO nebula_visitors_data (nebula_id, label, value) VALUES";
		foreach ( $built_values as $row ){
			$built_insert_query .= " (" . implode(',', $row) . "),";
		}
		$built_insert_query = rtrim($built_insert_query, ',') . ';';

		$inserted_visitor_data = $wpdb->query($built_insert_query); //DB Query here

		return true;
	}

	return false;
}

//Update certain visitor data everytime.
//Pass the existing data array to this to append to it (otherwise a new array is created)... So probably don't want to call this without passing the parameter.
function nebula_vdb_update_data_everytime($defaults=array()){
	if ( nebula_option('visitors_db') ){
		//Avoid ternary operators to prevent overwriting existing data (like manual DB entries)

		$cached_visitor_data = nebula_vdb_get_all_visitor_data('cache'); //@todo "Nebula" 0: could we just use $defaults instead of the cache?
		$latest_data = ( !empty($cached_visitor_data) )? $cached_visitor_data : $defaults;
		if ( !empty($latest_data) ){ //Only update these in the cache to prevent unnecessary DB queries
			//Detect Google Analytics blocking
			if ( !nebula_vdb_is_nebula_generated_cid() ){ //GA is not blocked if GA generated the CID
				$defaults['is_ga_blocked'] = '0';
			} else {
				$total_pageviews = ( array_key_exists('total_pageviews', $latest_data) )? $latest_data['total_pageviews'] : 0;
				if ( $total_pageviews > 1 ){
					$defaults['is_multipage_visitor'] = '1';
				}
				$total_refreshes = ( array_key_exists('total_refreshes', $latest_data) )? $latest_data['total_refreshes'] : 0;
				if ( nebula_option('ga_tracking_id') && ($total_pageviews > 1 || $total_refreshes > 1) ){ //If GA is enabled and if more than 1 pageview then it's blocked
					$defaults['is_ga_blocked'] = '1';
				}
			}

			//Update session duration
			$session_start_time = $latest_data['current_session_on'];
			$defaults['current_session_duration'] = round(time()/60)*60-$session_start_time; //Rounded to the nearest minute
			$defaults['is_known'] = ( nebula_vdb_is_known($defaults) )? '1' : '0';
		}

		$defaults['ga_cid'] = ga_parse_cookie();
		$defaults['notable_poi'] = nebula_poi();
		$defaults['last_modified_on'] = time();
		$defaults['nebula_session_id'] = nebula_session_id();

		if ( is_staff() ){
			$defaults['is_staff'] = '1';
		}

		//Check for nv_ query parameters like ?nv_first_name=john
		$query_strings = parse_str($_SERVER['QUERY_STRING']);
		if ( !empty($query_strings) ){
			foreach ( $query_strings as $key => $value ){
				if ( strpos($key, 'nv_') === 0 ){
					if ( empty($value) ){
						$value = '1';
					}
					$defaults[sanitize_key(substr($key, 3))] = sanitize_text_field(str_replace('+', ' ', urldecode($value)));
				}
			}
		}

		//Logged-in User Data
		if ( is_user_logged_in() ){
			$defaults['wp_user_id'] = get_current_user_id();

			$user = get_userdata(get_current_user_id());
			if ( !empty($user) ){ //WordPress user data exists
				//Default WordPress user info
				if ( !empty($user->roles) ){
					$defaults['wp_role'] = sanitize_text_field($user->roles[0]);
				}
				if ( !empty($user->user_firstname) ){
					$defaults['first_name'] = sanitize_text_field($user->user_firstname);
				}
				if ( !empty($user->user_lastname) ){
					$defaults['last_name'] = sanitize_text_field($user->user_lastname);
				}
				if ( !empty($user->user_firstname) && !empty($user->user_lastname) ){
					$defaults['full_name'] = sanitize_text_field($user->user_firstname . ' ' . $user->user_lastname);
				}
				if ( !empty($user->user_email) ){
					$defaults['email_address'] = sanitize_text_field($user->user_email);
				}
				if ( !empty($user->user_login) ){
					$defaults['username'] = sanitize_text_field($user->user_login);
				}

				//Custom user fields
				if ( get_user_meta($user->ID, 'headshot_url', true) ){
					$defaults['photo'] = sanitize_text_field(get_user_meta($user->ID, 'headshot_url', true));
				}
				if ( get_user_meta($user->ID, 'jobtitle', true) ){
					$defaults['job_title'] = sanitize_text_field(get_user_meta($user->ID, 'jobtitle', true));
				}
				if ( get_user_meta($user->ID, 'jobcompany', true) ){
					$defaults['company'] = sanitize_text_field(get_user_meta($user->ID, 'jobcompany', true));
				}
				if ( get_user_meta($user->ID, 'jobcompanywebsite', true) ){
					$defaults['company_website'] = sanitize_text_field(get_user_meta($user->ID, 'jobcompanywebsite', true));
				}
				if ( get_user_meta($user->ID, 'phonenumber', true) ){
					$defaults['phone_number'] = sanitize_text_field(get_user_meta($user->ID, 'phonenumber', true));
				}
				if ( get_user_meta($user->ID, 'usercity', true) ){
					$defaults['city'] = sanitize_text_field(get_user_meta($user->ID, 'usercity', true));
				}
				if ( get_user_meta($user->ID, 'userstate', true) ){
					$defaults['state_name'] = sanitize_text_field(get_user_meta($user->ID, 'userstate', true));
				}
			}
		}

		//Campaign Data
		if ( isset($_GET['utm_campaign']) ){
			$defaults['utm_campaign'] = sanitize_text_field($_GET['utm_campaign']);
		}
		if ( isset($_GET['utm_medium']) ){
			$defaults['utm_medium'] = sanitize_text_field($_GET['utm_medium']);
		}
		if ( isset($_GET['utm_source']) ){
			$defaults['utm_source'] = sanitize_text_field($_GET['utm_source']);
		}
		if ( isset($_GET['utm_content']) ){
			$defaults['utm_content'] = sanitize_text_field($_GET['utm_content']);
		}
		if ( isset($_GET['utm_term']) ){
			$defaults['utm_term'] = sanitize_text_field($_GET['utm_term']);
		}

		//Request information
		if ( !nebula_is_ajax_request() ){
			$defaults['http_accept'] = $_SERVER['HTTP_ACCEPT'];
			$defaults['http_encoding'] = $_SERVER['HTTP_ACCEPT_ENCODING'];
			$defaults['http_language'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		}

		//Device information
		$defaults['user_agent'] = sanitize_text_field($_SERVER['HTTP_USER_AGENT']);
		$defaults['device_form_factor'] = ( nebula_get_device('formfactor') )? nebula_get_device('formfactor') : 'Unknown';
		$defaults['device_full'] = ( nebula_get_device('full') )? nebula_get_device('full') : 'Unknown';
		$defaults['device_brand'] = ( nebula_get_device('brand') )? nebula_get_device('brand') : 'Unknown';
		$defaults['device_model'] = ( nebula_get_device('model') )? nebula_get_device('model') : 'Unknown';
		$defaults['device_type'] = ( nebula_get_device('type') )? nebula_get_device('type') : 'Unknown';
		$defaults['os_full'] = ( nebula_get_os('full') )? nebula_get_os('full') : 'Unknown';
		$defaults['os_name'] = ( nebula_get_os('name') )? nebula_get_os('name') : 'Unknown';
		$defaults['os_version'] = ( nebula_get_os('version') )? nebula_get_os('version') : 'Unknown';
		$defaults['browser_full'] = ( nebula_get_browser('full') )? nebula_get_browser('full') : 'Unknown';
		$defaults['browser_name'] = ( nebula_get_browser('name') )? nebula_get_browser('name') : 'Unknown';
		$defaults['browser_version'] = ( nebula_get_browser('version') )? nebula_get_browser('version') : 'Unknown';
		$defaults['browser_engine'] = ( nebula_get_browser('engine') )? nebula_get_browser('engine') : 'Unknown';
		$defaults['browser_type'] = ( nebula_get_browser('type') )? nebula_get_browser('type') : 'Unknown';

		//Information based on other visitor data
		$defaults['most_identifiable'] = nebula_vdb_most_identifiable_label($defaults, false);
		$defaults['fingerprint'] = nebula_vdb_fingerprint($defaults);

		$scores = nebula_vdb_calculate_scores($defaults);
		$defaults['behavior_score'] = $scores['behavior'];
		$defaults['demographic_score'] = $scores['demographic'];
		$defaults['lead_score'] = $scores['lead'];

		return $defaults;
	}

	return false;
}

//Generate a fingerprint for thie visitor
function nebula_vdb_fingerprint($data=null){
	//Server-side detections
	$fingerprint = 's:';
	if ( empty($data) ){ //If additional data is unavailable, return only server-side fingerprint
		$fingerprint .= ( !empty($_SERVER['HTTP_USER_AGENT']) )? nebula_smash_text($_SERVER['HTTP_USER_AGENT']) : '';
		$fingerprint .= ( !empty($_SERVER['HTTP_ACCEPT']) )? nebula_smash_text($_SERVER['HTTP_ACCEPT']) : '';
		$fingerprint .= ( !empty($_SERVER['HTTP_ACCEPT_ENCODING']) )? nebula_smash_text($_SERVER['HTTP_ACCEPT_ENCODING']) : '';
		$fingerprint .= ( !empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) )? nebula_smash_text($_SERVER['HTTP_ACCEPT_LANGUAGE']) : '';
		$fingerprint .= nebula_ip_location('timezone');

		return $fingerprint;
	} else { //Use provided $data (so AJAX doesn't alter server-side detections)
		$fingerprint .= ( !empty($data['user_agent']) )? nebula_smash_text($data['user_agent']) : '';
		$fingerprint .= ( !empty($data['http_accept']) )? nebula_smash_text($data['http_accept']) : '';
		$fingerprint .= ( !empty($data['http_encoding']) )? nebula_smash_text($data['http_encoding']) : '';
		$fingerprint .= ( !empty($data['http_language']) )? nebula_smash_text($data['http_language']) : '';
		$fingerprint .= ( !empty($data['ip_time_zone']) )? nebula_smash_text($data['ip_time_zone']) : '';
	}

	//Client-side detections
	$fingerprint .= '.c:';
	$fingerprint .= ( !empty($data['screen']) )? nebula_smash_text($data['screen']) : '';
	$fingerprint .= ( !empty($data['ip_time_zone']) )? nebula_smash_text($data['ip_time_zone']) : '';
	$fingerprint .= ( !empty($data['plugins']) )? nebula_smash_text($data['plugins']) : '';
	$fingerprint .= ( !empty($data['cookies']) )? nebula_smash_text($data['cookies']) : '';

	return $fingerprint;
}

//Nebula smash encode text
function nebula_smash_text($string){
	return str_rot13(trim(preg_replace('/ +/', '', preg_replace('/[^A-Za-z0-9]/', '', urldecode(html_entity_decode(strip_tags(strtolower($string))))))));
}


//Calculate lead score
function nebula_vdb_calculate_scores($data=null, $storage_type=false, $alt_nebula_id=false){
	if ( nebula_option('visitors_db') ){
		if ( !empty($data) ){
			$all_visitor_data = $data;
		} else {
			$all_visitor_data = nebula_vdb_get_all_visitor_data($storage_type, $alt_nebula_id);
		}

		$demographic_score = nebula_vdb_calculate_demographic_score($all_visitor_data);
		$behavior_score = nebula_vdb_calculate_behavior_score($all_visitor_data);
		$score_modifier = intval($all_visitor_data['score_mod']);
		$lead_score = $demographic_score+$behavior_score+$score_modifier;

		return array(
			'demographic' => $demographic_score,
			'behavior' => $behavior_score,
			'modifier' => $score_modifier,
			'lead' => $lead_score,
		);
	}
}

//Calculate demographic score (Name, job title, etc.)
//Hook into this with add_filter to use your own values.
/*
	add_filter('nebula_vdb_demographic_points', 'project_demo_points');
	function project_demo_points($array){
	    $array['city'] = array('/syracuse/', 10); //If city is Syracuse
	    $array['email_address'] = 500;
	    return $array;
	}
*/
function nebula_vdb_calculate_demographic_score($data){
	if ( !empty($data) ){
		//label => points (If label exists in DB its value is not empty)
		//label => array(regex => points)
		$default_demographic_points = array(
			'notable_poi' => 100,
			'full_name' => 75,
			'email_address' => 100,
			'is_known' => 100,
			'street_full' => 50,
			'geo_latitude' => 75,
			'geo_accuracy' => array('/^(\d{5})/', -35),
			'photo' => 100,
			'ip_city' => 5,
			'company' => 25,
			'job_title' => 5,
			'job_title' => array('/(senior|^C[A-Z]{2}$|President|Chief)/i', 25),
			'phone_number' => 75,
			'notes' => 5,
			'referrer' => 10,
		);

		$additional_demographic_points = apply_filters('nebula_vdb_demographic_points', array());
		$demographic_point_values = array_merge($default_demographic_points, $additional_demographic_points);

		return nebula_vdb_points_adder($data, $demographic_point_values);
	}
}

//Calculate behavior score (Events, actions, etc.)
//Hook into this with add_filter to use your own values or add new ones.
/*
	add_filter('nebula_vdb_behavior_points', 'project_behavior_points');
	function project_behavior_points($array){
	    $array['last_page_viewed'] = array('/sign-up/', 20); //Visited sign-up page
	    $array['ordered_warranty'] = 500;
	    return $array;
	}
*/
function nebula_vdb_calculate_behavior_score($data){
	if ( !empty($data) ){
		//label => points (if label exists and value is not empty)
		//label => array(regex => points)
		$default_behavior_points = array(
			'acquisition_keywords' => 5,
			'utm_campaign' => 5,
			'notable_download' => 5,
			'pdf_view' => 5,
			'outbound_links' => 1,
			'internal_search' => 15,
			'contact_method' => 75,
			'ecommerce_addtocart' => 35,
			'ecommerce_checkout' => 50,
			'is_customer' => 100,
			'engaged_reader' => 5,
			'contact_funnel' => 10,
			'copied_text' => 3,
			'print' => 25,
			'video_play' => 10,
			'video_engaged' => 15,
			'video_finished' => 25,
			'fb_like' => 10,
			'fb_share' => 15,
			'twitter_share' => 15,
			'gplus_share' => 15,
			'li_share' => 15,
			'pin_share' => 15,
			'email_share' => 15,
			'is_returning_user' => 1,
			'is_multipage_visitor' => 3,
			'is_ga_blocked' => -10,
			'current_session_duration' => array('0', -10), //Bounced visitors
		);

		$additional_behavior_points = apply_filters('nebula_vdb_behavior_points', array());
		$behavior_point_values = array_merge($default_behavior_points, $additional_behavior_points);

		$behavior_score = nebula_vdb_points_adder($data, $behavior_point_values);
		$behavior_score = floor($behavior_score-(floor((time()-$data['last_seen_on'])/DAY_IN_SECONDS)*0.33)); //Remove .33 points per day since last seen (rounded down)

		return $behavior_score;
	}
}

//Adds up point totals from provided data and points
function nebula_vdb_points_adder($data, $points){
	if ( empty($data) || empty($points) ){
		return false; //This function requires data and points.
	}

	$score = 0;
	foreach ( $data as $label => $value ){
		if ( array_key_exists($label, $points) ){ //If this visitor has this data
			if ( is_array($points[$label]) ){ //If the score has an condition array to match
				if ( is_array($value) ){ //If the visitor data is also an array
					$value = implode(',', $value); //Convert it to a single comma-separated string
				}

				if ( strtolower(trim($value)) == strtolower($points[$label][0]) || (strpos($points[$label][0], '/') === 0 && preg_match(strtolower($points[$label][0]), strtolower(trim($value)))) ){ //Check for an exact match before RegEx, or if is RegEx (begins with "/" and RegEx matches
					$score += $points[$label][1];
				}
			} elseif ( !empty($value) ){ //Just checking if the data exists
				$score += $points[$label];
			}
		}
	}

	return $score;
}

//Determine the most identifiable characteristic of this visitor
function nebula_vdb_most_identifiable_label($data=false, $storage_type=false, $alt_nebula_id=false){
	if ( nebula_option('visitors_db') ){
		if ( !empty($data) ){
			$all_visitor_data = $data;
		} else {
			$all_visitor_data = nebula_vdb_get_all_visitor_data($storage_type, $alt_nebula_id);
		}

		//These are in specific order!

		//Facebook Connect
		if ( !empty($all_visitor_data['facebook_connect']) ){
			return 'Facebook Connect';
		}

		//Customer
		if ( !empty($all_visitor_data['ecommerce_customer']) ){
			return 'Ecommerce Customer';
		}

		//Name
		if ( !empty($all_visitor_data['full_name']) || !empty($all_visitor_data['first_name']) || !empty($all_visitor_data['name']) ){
			return 'Name';
		}

		//Email Address
		if ( !empty($all_visitor_data['email_address']) ){
			return 'Email Address';
		}

		//Username
		if ( !empty($all_visitor_data['username']) ){
			return 'Username';
		}

		//Geolocation
		if ( !empty($all_visitor_data['geo_latitude']) ){
			return 'Geolocation';
		}

		//Similar to Known Visitor
		if ( !empty($all_visitor_data['similar_to_known']) ){
			return 'Similar to Known';
		}

		//Notable POI
		if ( !empty($all_visitor_data['notable_poi']) ){
			return 'Notable POI';
		}

		//Staff
		if ( !empty($all_visitor_data['is_staff']) ){
			return 'Staff';
		}

		//Photo
		if ( !empty($all_visitor_data['photo']) ){
			return 'Photo';
		}

		//Address
		if ( !empty($all_visitor_data['street_full']) ){
			return 'Address';
		}

		//Autocomplete Address
		if ( !empty($all_visitor_data['autocomplete_street_full']) ){
			return 'Autocomplete Address';
		}

		//Address Lookup
		if ( !empty($all_visitor_data['address_lookup']) ){
			return 'Address Lookup';
		}

		//Contact Form Submission
		if ( !empty($all_visitor_data['contact_funnel_submit_success']) ){
			return 'Contact Form Submission';
		}

		//Contact Method
		if ( !empty($all_visitor_data['contact_method']) ){
			return 'Contact Method';
		}

		//Contact Form Submission Attempt
		if ( !empty($all_visitor_data['form_submission_error']) ){
			return 'Contact Form Submission Attempt';
		}

		//Contact Funnel Started
		if ( !empty($all_visitor_data['contact_funnel_started']) ){
			return 'Contact Funnel Started';
		}

		//City
		if ( !empty($all_visitor_data['ip_city']) ){
			return 'IP Geo';
		}

		//Referrer
		if ( !empty($all_visitor_data['referrer']) ){
			return 'Referrer';
		}

		//Country
		if ( !empty($all_visitor_data['ip_country']) ){
			return 'IP Country';
		}

		//Watched Video
		if ( !empty($all_visitor_data['video_play']) ){
			return 'Watched Video';
		}

		//Engaged Reader
		if ( !empty($all_visitor_data['engaged_reader']) ){
			return 'Engaged Reader';
		}

		//IP Address
		if ( !empty($all_visitor_data['ip_address']) ){
			return 'IP Address';
		}

		//User Agent
		if ( !empty($all_visitor_data['user_agent']) ){
			return 'User Agent';
		}
	}

	return false;
}

//Check if this visitor is similar to a known visitor
function nebula_vdb_similar_to_known($specific=false, $storage_type=false, $alt_nebula_id=false){
	if ( nebula_option('visitors_db') ){
		$all_visitor_data = nebula_vdb_get_all_visitor_data($storage_type, $alt_nebula_id);

		$query = "SELECT DISTINCT(nebula_visitors.nebula_id), nebula_visitors.* FROM nebula_visitors_data JOIN nebula_visitors ON nebula_visitors.nebula_id = nebula_visitors_data.nebula_id WHERE nebula_visitors.is_known = '1' AND ((nebula_visitors_data.label = 'ip_address' AND nebula_visitors_data.value = '" . sanitize_text_field($all_visitor_data['ip_address']) . "') OR (nebula_visitors_data.label = 'all_ip_addresses' AND nebula_visitors_data.value = '" . sanitize_text_field($all_visitor_data['ip_address']) . "'))";
		if ( !empty($specific) ){
			$query = "SELECT DISTINCT(nebula_visitors.nebula_id), nebula_visitors.* FROM nebula_visitors_data JOIN nebula_visitors ON nebula_visitors.nebula_id = nebula_visitors_data.nebula_id WHERE nebula_visitors.is_known = '1' AND ((nebula_visitors_data.label = 'ip_address' AND nebula_visitors_data.value = '" . sanitize_text_field($all_visitor_data['ip_address']) . "') OR (nebula_visitors_data.label = 'all_ip_addresses' AND nebula_visitors_data.value = '" . sanitize_text_field($all_visitor_data['ip_address']) . "')) AND nebula_visitors.user_agent = '" . sanitize_text_field($all_visitor_data['user_agent']) . "'";
		}

		global $wpdb;
		$similar_known_visitors = $wpdb->get_results($query); //DB Query here

		if ( empty($similar_known_visitors) ){
			return false;
		} else { //This visitor is similar to a known visitor
			if ( $similar_known_visitors[0]->nebula_id == $all_visitor_data['nebula_id'] ){
				return false;
			}

			return $similar_known_visitors[0]->nebula_id;
		}
	}
}

//Remove expired visitors from the DB
//This is only ran when Nebula Options are saved, and when *new* visitors are inserted.
function nebula_vdb_remove_expired($force=false){
	if ( nebula_option('visitors_db') ){

		$nebula_visitor_remove_expired = get_transient('nebula_visitor_db_remove_expired');
		if ( empty($nebula_visitor_remove_expired) || !empty($force) || is_debug() ){
			global $wpdb;

			//Remove visitors who haven't been modified in the last 90 days and are not known
			$expiration_length = time()-(MONTH_IN_SECONDS*6); //6 months ago
			$removed_visitors = $wpdb->query($wpdb->prepare("DELETE FROM nebula_visitors WHERE last_modified_on < %d AND is_known = %s AND lead_score < %d", $expiration_length, '0', 100)); //DB Query here

			//How to recalculate scores for all users without looping through every single one?

			set_transient('nebula_visitor_db_remove_expired', time(), MONTH_IN_SECONDS); //@TODO "Nebula" 0: change this to run daily?
			return $removed_visitors;
		}
	}

	return false;
}

//Lookup if this visitor is known
function nebula_vdb_is_known($data=false, $alt_nebula_id=false){
	if ( nebula_option('visitors_db') ){
		//Allow cached results by double-checking Nebula ID
		if ( $alt_nebula_id == nebula_vdb_get_appropriate_nebula_id() ){
			$alt_nebula_id = false;
		}

		if ( !empty($data) ){ //If data is passed to this function
			if ( !empty($data['known']) || !empty($data['email_address']) || !empty($data['hubspot_vid']) ){
				return true;
			}
		} else { //Otherwise, go get the data
			$known = nebula_vdb_get_visitor_datapoint('is_known', false, $alt_nebula_id);
			if ( !empty($known) ){
				return true;
			}

			$email = nebula_vdb_get_visitor_datapoint('email_address', false, $alt_nebula_id);
			if ( !empty($email) ){
				return true;
			}

			$hubspot_vid = nebula_vdb_get_visitor_datapoint('hubspot_vid', false, $alt_nebula_id);
			if ( !empty($hubspot_vid) ){
				return true;
			}
		}
	}

	return false;
}

//Detect Notable POI
function nebula_poi($ip=null){
	if ( empty($ip) ){
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	if ( nebula_option('notableiplist') ){
		$notable_ip_lines = explode("\n", nebula_option('notableiplist'));
		foreach ( $notable_ip_lines as $line ){
			$ip_info = explode(' ', strip_tags($line), 2); //0 = IP Address or RegEx pattern, 1 = Name
			if ( ($ip_info[0][0] === '/' && preg_match($ip_info[0], $_SERVER['REMOTE_ADDR'])) || $ip_info[0] == $_SERVER['REMOTE_ADDR'] ){ //If regex pattern and matches IP, or if direct match
				return str_replace(array("\r\n", "\r", "\n"), '', $ip_info[1]);
				break;
			}
		}
	} elseif ( isset($_GET['poi']) ){ //If POI query string exists
		return str_replace(array('%20', '+'), ' ', $_GET['poi']);
	}

	return false;
}

//Look up a notable POI for a specific IP address
function get_nebula_poi($ip=false){
	if ( !empty($ip) ){
		return nebula_poi($ip);
	}

	return false;
}

//Check if a user has been online in the last 10 minutes
function nebula_is_user_online($id){
	$override = apply_filters('pre_nebula_is_user_online', false, $id);
	if ( $override !== false ){return $override;}

	$logged_in_users = nebula_data('users_status');
	return isset($logged_in_users[$id]['last']) && $logged_in_users[$id]['last'] > time()-600; //10 Minutes
}

//Check when a user was last online.
function nebula_user_last_online($id){
	$override = apply_filters('pre_nebula_user_last_online', false, $id);
	if ( $override !== false ){return $override;}

	$logged_in_users = nebula_data('users_status');
	if ( isset($logged_in_users[$id]['last']) ){
		return $logged_in_users[$id]['last'];
	}
	return false;
}

//Get a count of online users, or an array of online user IDs.
function nebula_online_users($return='count'){
	$override = apply_filters('pre_nebula_online_users', false, $return);
	if ( $override !== false ){return $override;}

	$logged_in_users = nebula_data('users_status');
	if ( empty($logged_in_users) || !is_array($logged_in_users) ){
		return ( strtolower($return) == 'count' )? 0 : false; //If this happens it indicates an error.
	}

	$user_online_count = 0;
	$online_users = array();
	foreach ( $logged_in_users as $user ){
		if ( !empty($user['username']) && isset($user['last']) && $user['last'] > time()-600 ){
			$online_users[] = $user;
			$user_online_count++;
		}
	}

	return ( strtolower($return) == 'count' )? $user_online_count : $online_users;
}

//Check how many locations a single user is logged in from.
function nebula_user_single_concurrent($id){
	$override = apply_filters('pre_nebula_user_single_concurrent', false, $id);
	if ( $override !== false ){return $override;}

	$logged_in_users = nebula_data('users_status');
	if ( isset($logged_in_users[$id]['unique']) ){
		return count($logged_in_users[$id]['unique']);
	}
	return 0;
}

//Alias for a less confusing is_admin() function to try to prevent security issues
function is_admin_page(){
	return is_admin();
}

//Check if viewing the login page.
function is_login_page(){
    return in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'));
}

//Format phone numbers into the preferred (315) 478-6700 format.
function nebula_phone_format($number=false){
	if ( !empty($number) ){
		return preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3', $number);
	}
	return $number;
}

//Check if the current IP address matches any of the dev IP address from Nebula Options
//Passing $strict bypasses IP check, so user must be a dev and logged in.
//Note: This should not be used for security purposes since IP addresses can be spoofed.
function is_dev($strict=false){
	$override = apply_filters('pre_is_dev', false, $strict);
	if ( $override !== false ){return $override;}

	if ( empty($strict) ){
		$devIPs = explode(',', nebula_option('dev_ip'));
		if ( !empty($devIPs) ){
			foreach ( $devIPs as $devIP ){
				$devIP = trim($devIP);

				if ( !empty($devIP) && $devIP[0] != '/' && $devIP == $_SERVER['REMOTE_ADDR'] ){
					return true;
				}

				if ( !empty($devIP) && $devIP[0] === '/' && preg_match($devIP, $_SERVER['REMOTE_ADDR']) ){
					return true;
				}
			}
		}
	}

	//Check if the current user's email domain matches any of the dev email domains from Nebula Options
	if ( is_user_logged_in() ){
		$current_user = wp_get_current_user();
		if ( !empty($current_user->user_email) ){
			list($current_user_email, $current_user_domain) = explode('@', $current_user->user_email);

			$devEmails = explode(',', nebula_option('dev_email_domain'));
			foreach ( $devEmails as $devEmail ){
				if ( trim($devEmail) == $current_user_domain ){
					return true;
				}
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
		$clientIPs = explode(',', nebula_option('client_ip'));
		if ( !empty($clientIPs) ){
			foreach ( $clientIPs as $clientIP ){
				$clientIP = trim($clientIP);

				if ( !empty($clientIP) && $clientIP[0] != '/' && $clientIP == $_SERVER['REMOTE_ADDR'] ){
					return true;
				}

				if ( !empty($clientIP) && $clientIP[0] === '/' && preg_match($clientIP, $_SERVER['REMOTE_ADDR']) ){
					return true;
				}
			}
		}
	}

	if ( is_user_logged_in() ){
		$current_user = wp_get_current_user();
		if ( !empty($current_user->user_email) ){
			list($current_user_email, $current_user_domain) = explode('@', $current_user->user_email);

			//Check if the current user's email domain matches any of the client email domains from Nebula Options
			$clientEmails = explode(',', nebula_option('client_email_domain'));
			foreach ( $clientEmails as $clientEmail ){
				if ( trim($clientEmail) == $current_user_domain ){
					return true;
				}
			}
		}
	}

	return false;
}

//Check if the current IP address or logged-in user is a developer or client.
//Note: This does not account for user role (An admin could return false here). Check role separately.
function is_staff($strict=false){
	if ( is_dev($strict) || is_client($strict) ){
		return true;
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
			}
			return false;
		}
		return true;
	}
	return false;
}

//Check if the current site is live to the public.
//Note: This checks if the hostname of the home URL matches any of the valid hostnames.
//If the Valid Hostnames option is empty, this will return true as it is unknown.
function is_site_live(){
	$override = apply_filters('pre_is_site_live', false);
	if ( $override !== false ){return $override;}

	if ( nebula_option('hostnames') ){
		if ( strpos(nebula_option('hostnames'), nebula_url_components('hostname', home_url())) >= 0 ){
			return true;
		}
		return false;
	}
	return true;
}

//If the request was made via AJAX
function nebula_is_ajax_request(){
	if ( !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ){
		return true;
	}

	return false;
}

//Valid Hostname Regex
function nebula_valid_hostname_regex($domains=null){
	$domains = ( $domains )? $domains : array(nebula_url_components('domain'));
	$settingsdomains = ( nebula_option('hostnames') )? explode(',', nebula_option('hostnames')) : array(nebula_url_components('domain'));
	$fulldomains = array_merge($domains, $settingsdomains, array('googleusercontent.com')); //Enter ONLY the domain and TLD. The wildcard subdomain regex is automatically added.
	$fulldomains = preg_filter('/^/', '.*', $fulldomains);
	$fulldomains = str_replace(array(' ', '.', '-'), array('', '\.', '\-'), $fulldomains); //@TODO "Nebula" 0: Add a * to capture subdomains. Final regex should be: \.*gearside\.com|\.*gearsidecreative\.com
	$fulldomains = array_unique($fulldomains);
	return implode("|", $fulldomains);
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

	$url_components = parse_url($url);
	if ( empty($url_components['host']) ){
		return;
	}
	$host = explode('.', $url_components['host']);

	//Best way to get the domain so far. Probably a better way by checking against all known TLDs.
	preg_match("/[a-z0-9\-]{1,63}\.[a-z\.]{2,6}$/", parse_url($url, PHP_URL_HOST), $domain);

	if ( !empty($domain) ){
		$sld = substr($domain[0], 0, strpos($domain[0], '.'));
		$tld = substr($domain[0], strpos($domain[0], '.'));
	}

	switch ($segment){
		case ('all'):
		case ('href'):
			return $url;
			break;

		case ('protocol'): //Protocol and Scheme are aliases and return the same value.
		case ('scheme'): //Protocol and Scheme are aliases and return the same value.
		case ('schema'):
			if ( isset($url_components['scheme']) ){
				return $url_components['scheme'];
			} else {
				return false;
			}
			break;

		case ('port'):
			if ( isset($url_components['port']) ){
				return $url_components['port'];
			} else {
				switch( $url_components['scheme'] ){
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
			if ( isset($url_components['user']) ){
				return $url_components['user'];
			} else {
				return false;
			}
			break;

		case ('pass'): //Returns the password from this type of syntax: https://username:password@gearside.com/
		case ('password'):
			if ( isset($url_components['pass']) ){
				return $url_components['pass'];
			} else {
				return false;
			}
			break;

		case ('authority'):
			if ( isset($url_components['user']) && isset($url_components['pass']) ){
				return $url_components['user'] . ':' . $url_components['pass'] . '@' . $url_components['host'] . ':' . nebula_url_components('port', $url);
			} else {
				return false;
			}
			break;

		case ('host'): //In http://something.example.com the host is "something.example.com"
		case ('hostname'):
			if ( isset($url_components['host']) ){
				return $url_components['host'];
			}
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
			if ( isset($url_components['scheme']) ){
				return $url_components['scheme'] . '://' . $domain[0];
			}
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
			if ( isset($url_components['path']) ){
				return $url_components['path'];
			}
			break;

		case ('file'): //Filename will be just the filename/extension.
		case ('filename'):
			if ( contains(basename($url_components['path']), array('.')) ){
				return basename($url_components['path']);
			} else {
				return false;
			}
			break;

		case ('extension'): //The extension only (without ".")
			if ( contains(basename($url_components['path']), array('.')) ){
				$file_parts = explode('.', $url_components['path']);
				return $file_parts[1];
			} else {
				return false;
			}
			break;

		case ('path'): //Path should be just the path without the filename/extension.
			if ( contains(basename($url_components['path']), array('.')) ){ //@TODO "Nebula" 0: This will possibly give bad data if the directory name has a "." in it
				return str_replace(basename($url_components['path']), '', $url_components['path']);
			} else {
				return $url_components['path'];
			}
			break;

		case ('query'):
		case ('queries'):
		case ('search'):
			if ( isset($url_components['query']) ){
				return $url_components['query'];
			}
			break;

		case ('fragment'):
		case ('fragments'):
		case ('anchor'):
		case ('hash') :
		case ('hashtag'):
		case ('id'):
			if ( isset($url_components['fragment']) ){
				return $url_components['fragment'];
			}
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

//Text limiter by words
function string_limit_words($string, $word_limit){
	$override = apply_filters('pre_string_limit_words', false, $string, $word_limit);
	if ( $override !== false ){return $override;}

	$limited['text'] = $string;
	$limited['is_limited'] = false;
	$words = explode(' ', $string, ($word_limit+1));
	if ( count($words) > $word_limit ){
		array_pop($words);
		$limited['text'] = implode(' ', $words);
		$limited['is_limited'] = true;
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
function in_array_r($needle, $haystack, $strict=true){
	$override = apply_filters('pre_in_array_r', false, $needle, $haystack, $strict);
	if ( $override !== false ){return $override;}

	foreach ( $haystack as $item ){
		if ( ($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict)) ){
			return true;
		}
	}
	return false;
}

//Recursive Glob
function glob_r($pattern, $flags=0){
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
	foreach ( $files as $file ){
		if ( $file <> "." && $file <> ".."){
			$currentFile = $cleanPath . $file;
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

//Check if a value is a UTC Timestamp
function is_utc_timestamp($timestamp){
	if ( strlen($timestamp) == 10 ){
		$timestamp = intval($timestamp);
		if ( ctype_digit($timestamp) && strtotime(date('d-m-Y H:i:s', $timestamp)) === $timestamp ){
			return true;
		}
	}
	return false;
}

//Check if a website or resource is available
function nebula_is_available($url=null, $nocache=false){
	$override = apply_filters('pre_nebula_is_available', false, $url);
	if ( $override !== false ){return $override;}

	if ( empty($url) || strpos($url, 'http') !== 0 ){
		trigger_error('Error: Requested URL is either empty or missing acceptable protocol.', E_USER_ERROR);
		return false;
	}

	$hostname = str_replace('.', '_', nebula_url_components('hostname', $url));
	$site_available_buffer = get_transient('nebula_site_available_' . $hostname);
	if ( !empty($site_available_buffer) && !$nocache ){
		if ( $site_available_buffer === 'Available' ){
			return true;
		}

		set_transient('nebula_site_available_' . $hostname, 'Unavailable', MINUTE_IN_SECONDS*5);
		return false;
	}

	if ( empty($site_available_buffer) || $nocache ){
		$response = wp_remote_get($url);
		if ( !is_wp_error($response) && $response['response']['code'] === 200 ){
			set_transient('nebula_site_available_' . $hostname, 'Available', MINUTE_IN_SECONDS*5);
			return true;
		}
	}

	set_transient('nebula_site_available_' . $hostname, 'Unavailable', MINUTE_IN_SECONDS*5); //5 minute expiration
	return false;
}

//Check the brightness of a color. 0=darkest, 255=lightest, 256=false
function nebula_color_brightness($hex){
	$override = apply_filters('pre_nebula_color_brightness', false, $hex);
	if ( $override !== false ){return $override;}

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
	}
	return 256;
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

	$nebula_version_year = ( $nebula_version['medium'] >= 8 )? 2012+$nebula_version['large']+1 : 2012+$nebula_version['large'];
	$nebula_months = array('May', 'June', 'July', 'August', 'September', 'October', 'November', 'December', 'January', 'February', 'March', 'April');
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
		case ('raw'):
			return $nebula_theme_info->get('Version');
			break;
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
	Nebula Sass Compiling

	Add directories to be checked for .scss files by using the filter "nebula_scss_locations". Example:
	add_filter('nebula_scss_locations', 'my_plugin_scss_files');
	function my_plugin_scss_files($scss_locations){
	    $scss_locations['my_plugin'] = array(
			'directory' => plugin_dir_path(__FILE__),
			'uri' => plugin_dir_url(__FILE__),
			'imports' => plugin_dir_path(__FILE__) . '/scss/partials/'
		);
	    return $scss_locations;
	}
 ===========================*/

add_action('init', 'nebula_scss_controller');
function nebula_scss_controller(){
	if ( !is_writable(get_template_directory()) ){
		trigger_error('The template directory is not writable. Can not compile Sass files!', E_USER_NOTICE);
		return false;
	}

	if ( nebula_option('scss', 'enabled') ){
		$scss_locations = array(
			'parent' => array(
				'directory' => get_template_directory(),
				'uri' => get_template_directory_uri(),
				'imports' => get_template_directory() . '/stylesheets/scss/partials/'
			)
		);

		if ( is_child_theme() ){
			$scss_locations['child'] = array(
				'directory' => get_stylesheet_directory(),
				'uri' => get_stylesheet_directory_uri(),
				'imports' => get_stylesheet_directory() . '/stylesheets/scss/partials/'
			);
		}

		//Allow for additional Sass locations to be included. Imports can be an array of directories.
		$additional_scss_loactions = apply_filters('nebula_scss_locations', array());
		$all_scss_locations = array_merge($scss_locations, $additional_scss_loactions);

		//Check if all Sass files should be rendered
		$force_all = false;
		if ( (isset($_GET['sass']) || isset($_GET['scss']) || isset($_GET['settings-updated'])) && is_staff() ){
			$force_all = true;
		}

		//Find and render .scss files at each location
		foreach ( $all_scss_locations as $scss_location_name => $scss_location_paths ){
			nebula_render_scss($scss_location_name, $scss_location_paths, $force_all);
		}

		//If SCSS has not been rendered in 1 month, disable the option.
		if ( time()-nebula_data('scss_last_processed') >= 2592000 ){
			nebula_update_option('scss', 'disabled');
		}
	} elseif ( is_dev() && !is_admin_page() && (isset($_GET['sass']) || isset($_GET['scss'])) ){
		trigger_error('Sass can not compile because it is disabled in Nebula Functions.', E_USER_NOTICE);
	}
}

function nebula_render_scss($location_name=false, $location_paths=false, $force_all=false){
	$override = apply_filters('pre_nebula_render_scss', false, $location_name, $location_paths, $force_all);
	if ( $override !== false ){return $override;}

	if ( nebula_option('scss', 'enabled') && !empty($location_name) && !empty($location_paths) ){
		//Require SCSSPHP
		require_once(get_template_directory() . '/includes/libs/scssphp/scss.inc.php'); //SCSSPHP is a compiler for SCSS 3.x
		$scss = new \Leafo\ScssPhp\Compiler();

		//Register import directories
		if ( !is_array($location_paths['imports']) ){
			$location_paths['imports'] = array($location_paths['imports']);
		}
		foreach ( $location_paths['imports'] as $imports_directory ){
			$scss->addImportPath($imports_directory);
		}

		//Set compiling options
		if ( nebula_option('minify_css', 'enabled') && !is_debug() ){
			$scss->setFormatter('Leafo\ScssPhp\Formatter\Compressed'); //Minify CSS (while leaving "/*!" comments for WordPress).
		} else {
			$scss->setFormatter('Leafo\ScssPhp\Formatter\Compact'); //Compact, but readable, CSS lines
			if ( is_debug() ){
				$scss->setLineNumberStyle(\Leafo\ScssPhp\Compiler::LINE_COMMENTS); //Adds line number reference comments in the rendered CSS file for debugging.
			}
		}

		//Variables
		$nebula_scss_variables = array(
			'template_directory' => '"' . get_template_directory_uri() . '"',
			'stylesheet_directory' => '"' . get_stylesheet_directory_uri() . '"',
			'this_directory' => '"' . $location_paths['uri'] . '"',
			'primary_color' => get_theme_mod('nebula_primary_color', '#0098d7'), //From Customizer
			'secondary_color' => get_theme_mod('nebula_secondary_color', '#95d600'), //From Customizer
			'background_color' => get_theme_mod('nebula_background_color', '#f6f6f6'), //From Customizer
		);
		$additional_scss_variables = apply_filters('nebula_scss_variables', array());
		$all_scss_variables = array_merge($nebula_scss_variables, $additional_scss_variables);
		$scss->setVariables($nebula_scss_variables);

		//Imports/Partials (find the last modified time)
		$latest_import = 0;
		foreach ( glob($imports_directory . '*') as $import_file ){
			if ( filemtime($import_file) > $latest_import ){
				$latest_import = filemtime($import_file);
			}
		}

		//Combine Developer Stylesheets
		if ( nebula_option('dev_stylesheets', 'enabled') ){
			nebula_combine_dev_stylesheets($location_paths['directory'] . '/stylesheets', $location_paths['uri'] . '/stylesheets');
		}

		//Compile each SCSS file
		foreach ( glob($location_paths['directory'] . '/stylesheets/scss/*.scss') as $file ){ //@TODO "Nebula" 0: Change to glob_r() but will need to create subdirectories if they don't exist.
			$file_path_info = pathinfo($file);

			//Skip file conditions
			$is_wireframing_file = $file_path_info['filename'] == 'wireframing' && nebula_option('prototype_mode', 'disabled'); //If file is wireframing.scss but wireframing functionality is disabled, skip file.
			$is_dev_file = $file_path_info['filename'] == 'dev' && nebula_option('dev_stylesheets', 'disabled'); //If file is dev.scss but dev stylesheets functionality is disabled, skip file.
			$is_admin_file = !is_admin_page() && in_array($file_path_info['filename'], array('login', 'admin', 'tinymce')); //If viewing front-end, skip WP admin files.
			if ( $is_wireframing_file || $is_dev_file || $is_admin_file ){
				continue;
			}

			//If file exists, and has .scss extension, and doesn't begin with "_".
			if ( is_file($file) && $file_path_info['extension'] == 'scss' && $file_path_info['filename'][0] != '_' ){
				$css_filepath = ( $file_path_info['filename'] == 'style' )? $location_paths['directory'] . '/style.css': $location_paths['directory'] . '/stylesheets/css/' . $file_path_info['filename'] . '.css'; //style.css to the root directory. All others to the /css directory in the /stylesheets directory.
				wp_mkdir_p($location_paths['directory'] . '/stylesheets/css'); //Create the /css directory (in case it doesn't exist already).

				//If style.css has been edited after style.scss, save backup but continue compiling SCSS
				if ( (is_child_theme() && $location_name != 'parent' ) && ($file_path_info['filename'] == 'style' && file_exists($css_filepath) && nebula_data('scss_last_processed') != '0' && nebula_data('scss_last_processed')-filemtime($css_filepath) < -30) ){
					copy($css_filepath, $css_filepath . '.bak'); //Backup the style.css file to style.css.bak
					if ( is_dev() || current_user_can('manage_options') ){
						global $scss_debug_ref;
						$scss_debug_ref = $location_name . ':';
						$scss_debug_ref .= (nebula_data('scss_last_processed')-filemtime($css_filepath));
						add_action('wp_head', 'nebula_scss_console_warning'); //Call the console error note
					}
				}

				//If .css file doesn't exist, or is older than .scss file (or any partial), or is debug mode, or forced
				if ( !file_exists($css_filepath) || filemtime($file) > filemtime($css_filepath) || $latest_import > filemtime($css_filepath) || is_debug() || $force_all ){
					ini_set('memory_limit', '512M'); //Increase memory limit for this script. //@TODO "Nebula" 0: Is this the best thing to do here? Other options?
					WP_Filesystem();
					global $wp_filesystem;
					$existing_css_contents = ( file_exists($css_filepath) )? $wp_filesystem->get_contents($css_filepath) : '';

					//If the correlating .css file doesn't contain a comment to prevent overwriting
					if ( !strpos(strtolower($existing_css_contents), 'scss disabled') ){
						$this_scss_contents = $wp_filesystem->get_contents($file); //Copy SCSS file contents
						$compiled_css = $scss->compile($this_scss_contents); //Compile the SCSS
						$enhanced_css = nebula_scss_post_compile($compiled_css); //Compile server-side variables into SCSS
						$wp_filesystem->put_contents($css_filepath, $enhanced_css); //Save the rendered CSS.
						nebula_update_data('scss_last_processed', time());
					}
				}
			}
		}
	}
}

//Log Sass .bak note in the browser console
function nebula_scss_console_warning(){
	global $scss_debug_ref;
	echo '<script>console.warn("Warning: Sass compiling is enabled, but it appears that style.css has been manually updated (Reference: ' . $scss_debug_ref . 's)! A style.css.bak backup has been made. If not using Sass, disable it in Nebula Options. Otherwise, make all edits in style.scss in the /stylesheets/scss directory!");</script>';
}

//Combine developer stylesheets
function nebula_combine_dev_stylesheets($directory=null, $directory_uri=null){
	$override = apply_filters('pre_nebula_combine_dev_stylesheets', false, $directory, $directory_uri);
	if ( $override !== false ){return $override;}

	if ( empty($directory) ){
		trigger_error('Dev stylesheet directories must be specified for files to be combined.', E_USER_NOTICE);
		return false;
	}

	WP_Filesystem();
	global $wp_filesystem;

	$file_counter = 0;
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

			//Include partials in dev.scss //@todo "Nebula" 0: Find a way to prevent hard-coding these partial files. Maybe tap into the $location_paths['imports'] from above (need a specific order other than alphabetical?)?
			if ( $file_counter == 1 ){
				$import_partials = '';
				$import_partials .= "@import '../../../../Nebula-master/stylesheets/scss/partials/variables';\r\n";
				$import_partials .= "@import '../partials/variables';\r\n";
				$import_partials .= "@import '../../../../Nebula-master/stylesheets/scss/partials/mixins';\r\n";
				$import_partials .= "@import '../../../../Nebula-master/stylesheets/scss/partials/helpers';\r\n";
				$wp_filesystem->put_contents($dev_scss_file, $automation_warning . $import_partials . "\r\n");
			}

			$this_scss_contents = $wp_filesystem->get_contents($file); //Copy file contents
			$empty_scss = ( $this_scss_contents == '' )? ' (empty)' : '';
			$dev_scss_contents = $wp_filesystem->get_contents($directory . '/scss/dev.scss');

			$dev_scss_contents .= "\r\n\r\n\r\n/*! ==========================================================================\r\n   " . 'File #' . $file_counter . ': ' . $directory_uri . "/scss/dev/" . $file_path_info['filename'] . '.' . $file_path_info['extension'] . $empty_scss . "\r\n   ========================================================================== */\r\n\r\n" . $this_scss_contents . "\r\n\r\n/* End of " . $file_path_info['filename'] . '.' . $file_path_info['extension'] . " */\r\n\r\n\r\n";

			$wp_filesystem->put_contents($directory . '/scss/dev.scss', $dev_scss_contents);
		}
	}
	if ( $file_counter > 0 ){
		add_action('wp_enqueue_scripts', function(){
			wp_enqueue_style('nebula-dev_styles-parent', get_template_directory_uri() . '/stylesheets/css/dev.css?c=' . rand(1, 99999), array('nebula-main'), null);
			wp_enqueue_style('nebula-dev_styles-child', get_stylesheet_directory_uri() . '/stylesheets/css/dev.css?c=' . rand(1, 99999), array('nebula-main'), null);
		});
	}
}

//Compile server-side variables into SCSS
function nebula_scss_post_compile($scss){
	$override = apply_filters('pre_nebula_scss_post_compile', false, $scss);
	if ( $override !== false ){return $override;}

	$scss = preg_replace("(" . str_replace('/', '\/', get_template_directory()) . ")", '', $scss); //Reduce theme path for SCSSPHP debug line comments
	$scss = preg_replace("(" . str_replace('/', '\/', get_stylesheet_directory()) . ")", '', $scss); //Reduce theme path for SCSSPHP debug line comments (For child themes)
	do_action('nebula_scss_post_compile');
	$scss .= "\r\n/* Processed on " . date('l, F j, Y \a\t g:ia', time()) . ' */';
	nebula_update_data('scss_last_processed', time());

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
	if ( empty($scss_variables) || is_debug() ){
		$variables_file = $stylesheets_directory . '/scss/partials/_variables.scss';
		if ( !file_exists($variables_file) ){
			return false;
		}

		WP_Filesystem();
		global $wp_filesystem;
		$scss_variables = $wp_filesystem->get_contents($variables_file);
		set_transient($transient_name, $scss_variables, HOUR_IN_SECONDS*12);
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

//Device Detection - https://github.com/piwik/device-detector
//Be careful when updating this library. DeviceDetector.php requires modification to work without Composer!
use DeviceDetector\DeviceDetector;
add_action('init', 'nebula_device_detection');
function nebula_device_detection(){
	if ( nebula_option('device_detection') ){
		require_once(get_template_directory() . '/includes/libs/device-detector/DeviceDetector.php');
		$GLOBALS["device_detect"] = new DeviceDetector($_SERVER['HTTP_USER_AGENT']);
		$GLOBALS["device_detect"]->discardBotInformation(); //If called, getBot() will only return true if a bot was detected (speeds up detection a bit)
		$GLOBALS["device_detect"]->parse();
	}
}

//Boolean return if the user's device is mobile.
function nebula_is_mobile(){
	$override = apply_filters('pre_nebula_is_mobile', false);
	if ( $override !== false ){return $override;}

	if ( nebula_option('device_detection') ){
		if ( $GLOBALS["device_detect"]->isMobile() ){
			return true;
		}
	}

	global $is_iphone;
	if ( $is_iphone ){
		return true;
	}

	return false;
}

//Boolean return if the user's device is a tablet.
function nebula_is_tablet(){
	$override = apply_filters('pre_nebula_is_tablet', false);
	if ( $override !== false ){return $override;}

	if ( nebula_option('device_detection') ){
		if ( $GLOBALS["device_detect"]->isTablet() ){
			return true;
		}
	}

	return false;
}

//Boolean return if the user's device is a desktop.
function nebula_is_desktop(){
	$override = apply_filters('pre_nebula_is_desktop', false);
	if ( $override !== false ){return $override;}

	if ( nebula_option('device_detection') ){
		if ( $GLOBALS["device_detect"]->isDesktop() ){
			return true;
		}
	}

	return false;
}

//Returns the requested information of the operating system of the user's device.
function nebula_get_os($info='full'){
	$override = apply_filters('pre_nebula_get_os', false, $info);
	if ( $override !== false ){return $override;}

	if ( nebula_option('device_detection') ){
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

	global $is_iphone;
	switch ( strtolower($info) ){
		case 'full':
		case 'name':
			if ( $is_iphone ){
				return 'ios';
			}
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

	if ( nebula_option('device_detection') ){
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
	}

	return false;
}

//Returns the requested information of the model of the user's device.
function nebula_get_device($info='model'){
	$override = apply_filters('pre_nebula_get_device', false, $info);
	if ( $override !== false ){return $override;}

	if ( nebula_option('device_detection') ){
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

	global $is_iphone;
	$info = str_replace(' ', '', $info);
	switch ( strtolower($info) ){
		case 'brand':
		case 'brandname':
		case 'make':
		case 'model':
		case 'name':
		case 'type':
			if ( $is_iphone ){
				return 'iphone';
			}
			break;
		case 'formfactor':
			if ( $is_iphone ){
				return 'mobile';
			}
			break;
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

	if ( nebula_option('device_detection') ){
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

	global $is_gecko, $is_IE, $is_opera, $is_safari, $is_chrome;
	switch ( strtolower($info) ){
		case 'full':
		case 'name':
		case 'browser':
		case 'client':
			if ( $is_IE ){return 'internet explorer';}
			elseif ( $is_opera ){return 'opera';}
			elseif ( $is_safari ){return 'safari';}
			elseif ( $is_chrome ){return 'chrome';}
			break;
		case 'engine':
			if ( $is_gecko ){return 'gecko';}
			elseif ( $is_safari ){return 'webkit';}
			elseif ( $is_IE ){return 'trident';}
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

	if ( nebula_option('device_detection') ){
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
					if ( !empty($version_parts[1]) ){ //If minor version exists and is not 0
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
	}

	return false;
}

//Check to see if the rendering engine matches a passed parameter.
function nebula_is_engine($engine=null){
	$override = apply_filters('pre_nebula_is_engine', false, $engine);
	if ( $override !== false ){return $override;}

	if ( nebula_option('device_detection') ){
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
	}

	return false;
}

//Check for bot/crawler traffic
//UA lookup: http://www.useragentstring.com/pages/Crawlerlist/
function nebula_is_bot(){
	$override = apply_filters('pre_nebula_is_bot', false);
	if ( $override !== false ){return $override;}

	if ( nebula_option('device_detection') ){
		if ( $GLOBALS["device_detect"]->isBot() ){
			return true;
		}
	}

	$bots = array('bot', 'crawl', 'spider', 'feed', 'slurp', 'tracker', 'http', 'favicon');
	foreach( $bots as $bot ){
		if ( strpos(strtolower($_SERVER['HTTP_USER_AGENT']), $bot) !== false ){
			return true;
			break;
		}
	}

	return false;
}