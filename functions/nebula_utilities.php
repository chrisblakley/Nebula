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
	exit;
}

/*==========================
	Nebula Visitor Data
 ===========================*/

//Create Users Table with minimal default columns.
add_action('init', 'nebula_create_visitors_table', 2); //Using init instead of admin_init so this triggers before check_nebula_id (below)
function nebula_create_visitors_table(){
	if ( is_admin_page() && nebula_option('visitors_db') && isset($_GET['settings-updated']) && is_staff() ){ //Only trigger this in admin when Nebula Options are saved.
		global $wpdb;

		$visitors_table = $wpdb->query("SHOW TABLES LIKE 'nebula_visitors'");
		if ( empty($visitors_table) ){
			$created = $wpdb->query("CREATE TABLE nebula_visitors (
				id INT(11) NOT NULL AUTO_INCREMENT,
				nebula_id TEXT NOT NULL,
				known BOOLEAN NOT NULL DEFAULT FALSE,
				email_address TEXT NOT NULL,
				hubspot_vid INT(11) NOT NULL,
				last_modified_date INT(12) NOT NULL DEFAULT 0,
				last_session_id TEXT NOT NULL,
				notable_poi TEXT NOT NULL,
				score INT(5) NOT NULL DEFAULT 0,
				score_mod INT(5) NOT NULL DEFAULT 0,
				PRIMARY KEY (id)
			) ENGINE = MyISAM;"); //Try InnoDB as engine? and add ROW_FORMAT=COMPRESSED ?
		} else {
			nebula_remove_expired_visitors();
		}
	}
}

//Check if the Nebula ID exists on load and generate/store a new one if it does not.
add_action('init', 'check_nebula_id', 11);
function check_nebula_id(){
	if ( nebula_is_bot() || strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'wordpress') !== false ){
		return false; //Don't add bots to the DB
	}

	$nebula_id = get_nebula_id();

	if ( empty($nebula_id) ){ //If new user
		generate_nebula_id();
	}

	new_or_returning_visitor();
}

//Check if this visitor is new or returning using several factors
function new_or_returning_visitor(){
	if ( nebula_option('visitors_db') ){
		$last_session_id = nebula_get_visitor_data('last_session_id'); //Check if this returning visitor exists in the DB (in case they were removed)
		if ( empty($last_session_id) ){ //If the nebula_id is not in the DB already, treat it as a new user
			//Prevent duplicates for users blocking cookies or Google Analytics
			if ( strpos(ga_parse_cookie(), '-') !== false ){ //If GA CID was generated by Nebula
				global $wpdb;
				$unique_new_visitor = $wpdb->get_results("SELECT * FROM nebula_visitors WHERE ip_address = '" . sanitize_text_field($_SERVER['REMOTE_ADDR']) . "' AND user_agent = '" . sanitize_text_field($_SERVER['HTTP_USER_AGENT']) . "'");
				if ( !empty($unique_new_visitor) ){
					$unique_new_visitor = (array) $unique_new_visitor[0];
					if ( strpos($unique_new_visitor['ga_cid'], '-') !== false ){ //If that GA CID was also generated by Nebula
						generate_nebula_id($unique_new_visitor['nebula_id']); //Give this user the same Nebula ID
						//Update that row instead of inserting a new visitor.
						nebula_update_visitor(array(
							'first_session' => '0',
							'notes' => 'This user tracked by IP and User Agent.',
						));
					} else {
						nebula_insert_visitor(array('first_session' => '0')); //The matching visitor row had a GA CID assigned (no dashes)
					}
				} else {
					nebula_insert_visitor(array('first_session' => '0')); //No matching IP Address with same User Agent
				}
			} else {
				nebula_insert_visitor(array('first_session' => '0'));
			}
		} else {
			//Check if new session or another pageview in same session
			if ( session_id() != $last_session_id ){ //New session
				$update_data = array(
					'first_session' => '0',
					'current_session' => time(),
					'current_session_pageviews' => '1',
					'last_modified_date' => time(),
				);
				nebula_increment_visitor(array('session_count'));
			} else { //Same session
				$update_data = array(
					'current_session' => time(),
					'last_modified_date' => time(),
				);
				nebula_increment_visitor(array('current_session_pageviews'));
			}

			nebula_update_visitor($update_data);
		}
	}
}

//Return the Nebula ID (or false)
function get_nebula_id(){
	if ( isset($_COOKIE['nid']) ){
		return htmlentities(preg_replace('/[^a-zA-Z0-9\.]+/', '', $_COOKIE['nid']));
	}

	return false;
}

//Generate a unique Nebula ID and store it in a cookie and insert into DB
//Or force a specific Nebula ID
function generate_nebula_id($force=null){
	if ( !empty($force) ){
		$_COOKIE['nid'] = $force;
	} else {
		$_COOKIE['nid'] = nebula_version('full') . '.' . bin2hex(openssl_random_pseudo_bytes(5)) . '.' . uniqid(); //Update to random_bytes() when moving to PHP7
	}

	$nid_expiration = strtotime('January 1, 2035'); //Note: Do not let this cookie expire past 2038 or it instantly expires.
	setcookie('nid', $_COOKIE['nid'], $nid_expiration, COOKIEPATH, COOKIE_DOMAIN); //Store the Nebula ID as a cookie called "nid".

	if ( nebula_option('visitors_db') ){
		if ( empty($force) ){
			global $wpdb;
			$nebula_id_from_matching_ga_cid = $wpdb->get_results($wpdb->prepare("SELECT nebula_id FROM nebula_visitors WHERE ga_cid = '%s'", ga_parse_cookie())); //Check if the ga_cid exists, and if so use THAT nebula_id again
			if ( !empty($nebula_id_from_matching_ga_cid) ){
				$_COOKIE['nid'] = reset($nebula_id_from_matching_ga_cid[0]);
				setcookie('nid', $_COOKIE['nid'], $nid_expiration, COOKIEPATH, COOKIE_DOMAIN); //Update the Nebula ID cookie
			}
		}
	}

	return $_COOKIE['nid'];
}

//Create necessary columns by comparing passed data to existing columns
function nebula_visitors_create_missing_columns($all_data){
	if ( nebula_option('visitors_db') ){
		$existing_columns = nebula_visitors_existing_columns(); //Returns an array of current table column names

		$needed_columns = array();
		foreach ( $all_data as $column => $value ){
			if ( is_null($value) ){
				$all_data[$column] = ''; //Convert null values to empty strings.
			}

			if ( !in_array($column, $existing_columns) ){ //If the column does not exist, add it to an array
				$needed_columns[] = $column;
			}
		}

		if ( !empty($needed_columns) ){
			global $wpdb;

			$alter_query = "ALTER TABLE nebula_visitors ";
			foreach ( $needed_columns as $column_name ){
				$column_name = sanitize_key($column_name);

				$sample_value = $all_data[$column_name];
				$data_type = 'TEXT'; //Default data type
				if ( is_int($sample_value) ){
					$data_type = 'INT(12)';
				} elseif ( strlen($sample_value) == 1 && ($sample_value === '1' || $sample_value === '0') ){
					$data_type = 'INT(7)';
				}

				$alter_query .= "ADD " . $column_name . " " . $data_type . " NOT NULL, "; //Prep each needed column into a query
			}

			$create_columns = $wpdb->query(rtrim($alter_query, ', ')); //Create the needed columns
		}
	}
}

//Retrieve User Data
add_action('wp_ajax_nebula_ajax_get_visitor_data', 'nebula_ajax_get_visitor_data');
add_action('wp_ajax_nopriv_nebula_ajax_get_visitor_data', 'nebula_ajax_get_visitor_data');
function nebula_ajax_get_visitor_data(){
	if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }
	$column = sanitize_text_field($_POST['data']);
	echo nebula_get_visitor_data($column);
	exit;
}
function nebula_get_visitor_data($column){ //@TODO "Nebula" 0: Update to allow multiple datapoints to be accessed in one query.
	if ( nebula_option('visitors_db') ){
		$column = sanitize_key($column);

		if ( $column == 'notes' ){
			return false;
		}

		$nebula_id = get_nebula_id();
		if ( !empty($nebula_id) && !empty($column) ){
			global $wpdb;
			$requested_data = $wpdb->get_results($wpdb->prepare("SELECT " . $column . " FROM nebula_visitors WHERE nebula_id = '%s'", $nebula_id));

			if ( !empty($requested_data) && !empty($requested_data[0]) && strtolower(reset($requested_data[0])) != 'null' ){
				return reset($requested_data[0]); //@TODO "Nebula" 0: update so this could return multiple values
			}
		}
	}

	return false;
}

//Vague Data - Only update if it doesn't already exist in the DB
add_action('wp_ajax_nebula_ajax_vague_visitor', 'nebula_ajax_vague_visitor');
add_action('wp_ajax_nopriv_nebula_ajax_vague_visitor', 'nebula_ajax_vague_visitor');
function nebula_ajax_low_visitor(){
	if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }
	$data = $_POST['data'];
	echo nebula_vague_visitor($data);
	exit;
}
function nebula_vague_visitor($data=array()){
	if ( nebula_option('visitors_db') ){
		$data_to_send = array();
		foreach ( $data as $column => $value ){
			$existing_value = nebula_get_visitor_data($column);
			if ( empty($existing_value) ){ //If the requested data is empty/null, then update.
				$data_to_send[$column] = $value;
			}
		}

		if ( !empty($data_to_send) ){
			nebula_update_visitor($data_to_send);
		}
	}
	return false;
}

//Update Visitor Data
add_action('wp_ajax_nebula_ajax_update_visitor', 'nebula_ajax_update_visitor');
add_action('wp_ajax_nopriv_nebula_ajax_update_visitor', 'nebula_ajax_update_visitor');
function nebula_ajax_update_visitor(){
	if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }
	$data = $_POST['data'];
	echo nebula_update_visitor($data);
	exit;
}
function nebula_update_visitor($data=array(), $send_to_hubspot=true){ //$data is going to be array(column => value) or an array of arrays
	if ( nebula_option('visitors_db') ){
		global $wpdb;
		$nebula_id = get_nebula_id();

		if ( !empty($nebula_id) ){
			$update_every_time = nebula_visitor_data_update_everytime();
			$all_data = array_merge($update_every_time, $data); //Add any passed data

			//Check if the data should be sent to Hubspot
			if ( !empty($send_to_hubspot) ){
				$need_to_resend = false;
				$non_priority_columns = array('id', 'known', 'create_date', 'last_modified_date', 'initial_session', 'first_session', 'last_session_id', 'previous_session', 'current_session', 'current_session_pageviews', 'session_count', 'hubspot_vid', 'bot', 'expiration', 'score', 'ga_block', 'js_block', 'ad_block', 'nebula_session_id'); //These columns should not facilitate a Hubspot send by themselves.
				foreach ( $all_data as $column => $value ){
					if ( !in_array($column, $non_priority_columns) ){
						$need_to_resend = true;
					}
				}

				if ( empty($need_to_resend) ){
					$send_to_hubspot = false;
				}
			}

			nebula_visitors_create_missing_columns($all_data);
			$all_strings = array_fill(0, count($all_data), '%s'); //Create sanitization array

			//Update the visitor row
			$updated_visitor = $wpdb->update(
				'nebula_visitors',
				$all_data,
				array('nebula_id' => $nebula_id),
				$all_strings,
				array('%s')
			);

			if ( $updated_visitor === false ){ //If visitor does not exist in the table, create it with defaults and current data... might need to be true if its int(0) too, so maybe go back to empty($updated_visitor)
				nebula_insert_visitor($all_data, $send_to_hubspot);
			} else {
				check_if_known($send_to_hubspot);
			}

			return true;
		}
	}

	return false;
}

//Append to Visitor Data
add_action('wp_ajax_nebula_ajax_append_visitor', 'nebula_ajax_append_visitor');
add_action('wp_ajax_nopriv_nebula_ajax_append_visitor', 'nebula_ajax_append_visitor');
function nebula_ajax_append_visitor(){
	if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }
	$data = $_POST['data']; //json_decode(stripslashes()); but its already an array... why?
	echo nebula_append_visitor($data);
	exit;
}
function nebula_append_visitor($data=array(), $send_to_hubspot=true){ //$data is going to be array(column => value) or an array of arrays
	if ( nebula_option('visitors_db') ){
		global $wpdb;
		$nebula_id = get_nebula_id();

		if ( !empty($nebula_id) && !empty($data) ){
			nebula_update_visitor(array('last_modified_date' => time()));
			nebula_visitors_create_missing_columns($data);

			$append_query = "UPDATE nebula_visitors ";
			foreach ( $data as $column => $value ){
				$column = sanitize_key($column);

				$value = sanitize_text_field($value);
				$append_query .= "SET " . $column . " = CONCAT_WS(',', NULLIF(" . $column . ", ''), '" . $value . "'),"; //how to further prepare/sanitize this value? not sure how many %s are needed...
			}
			$append_query = rtrim($append_query, ', ');
			$append_query .= "WHERE nebula_id = '" . $nebula_id . "'";

			if ( strpos(str_replace(' ', '', $append_query), 'nebula_visitorsWHERE') === false ){
				$appended_visitor = $wpdb->query($append_query); //currently working on this

				if ( $appended_visitor === false ){ //If visitor does not exist in the table, create it with defaults and current data. might need to be true if its int(0) too, so maybe go back to empty($updated_visitor)
					nebula_insert_visitor($data, $send_to_hubspot);
				} else {
					check_if_known($send_to_hubspot);
				}

				return true;
			}
		}
	}

	return false;
}

//Increment Visitor Data
add_action('wp_ajax_nebula_ajax_increment_visitor', 'nebula_ajax_increment_visitor');
add_action('wp_ajax_nopriv_nebula_ajax_increment_visitor', 'nebula_ajax_increment_visitor');
function nebula_ajax_increment_visitor(){
	if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }
	$data = $_POST['data'];
	echo nebula_increment_visitor($data);
	exit;
}
function nebula_increment_visitor($data){ //Data should be an array of columns to increment
	if ( nebula_option('visitors_db') ){
		global $wpdb;
		$nebula_id = get_nebula_id();

		if ( is_string($data) ){
			$data = array($data);
		}

		//@TODO "Nebula" 0: echo here to see if this gets triggered multiple times (when it should only get triggered once)

		if ( !empty($nebula_id) && !empty($data) ){
			$increment_query = "UPDATE nebula_visitors ";
			foreach ( $data as $column ){
				$column = sanitize_key($column);
				$increment_query .= "SET " . $column . " = " . $column . "+1, ";
			}
			$increment_query = rtrim($increment_query, ', ') . ' ';
			$increment_query .= "WHERE nebula_id = '" . $nebula_id . "'";

			if ( strpos(str_replace(' ', '', $increment_query), 'nebula_visitorsWHERE') === false ){
				$incremented_visitor = $wpdb->query($increment_query);
				if ( $incremented_visitor === false ){ //If visitor does not exist in the table, create it with defaults and current data... might need to be true if its int(0) too, so maybe go back to empty($updated_visitor)
					return false;
				}

				return true;
			}
		}
	}

	return false;
}

//Insert visitor into table with all default detections
function nebula_insert_visitor($data=array(), $send_to_hubspot=true){
	if ( nebula_option('visitors_db') ){
		$nebula_id = get_nebula_id();
		if ( !empty($nebula_id) ){
			global $wpdb;

			$defaults = array(
				'nebula_id' => $nebula_id,
				'ga_cid' => ga_parse_cookie(), //Will be UUID on first visit then followed up with actual GA CID via AJAX (if available)
				'known' => '0',
				'ip_address' => sanitize_text_field($_SERVER['REMOTE_ADDR']),
				'create_date' => time(),
				'hubspot_vid' => false,
				'score' => '0',
				'notes' => '',
				'referer' => ( isset($_SERVER['HTTP_REFERER']) )? sanitize_text_field($_SERVER['HTTP_REFERER']) : '',
				'first_session' => '1',
				'session_count' => '1',
				'prev_session' => '0',
				'last_session_id' => session_id(),
				'nebula_session_id' => nebula_session_id(),
				'current_session' => time(),
				'current_session_pageviews' => '1',
				'prerendered' => '0',
				'page_visibility_hidden' => '0',
				'page_visibility_visible' => '0',
				'ga_block' => '0',
				'page_not_found' => '0',
				'no_search_results' => '0',
				'external_links' => '0',
				'non_linked_click' => '0',
				'copied_text' => '0',
				'html_errors' => '0',
				'js_errors' => '0',
				'css_errors' => '0',
				'ajax_errors' => '0',
				'page_suggestion_clicks' => '0',
				'infinite_query_loads' => '0',
				'score_mod' => '0',
			);

			//Attempt to detect IP Geolocation data using https://freegeoip.net/
			if ( nebula_option('ip_geolocation') ){
				WP_Filesystem();
				global $wp_filesystem;
				$ip_geo_data = $wp_filesystem->get_contents('http://freegeoip.net/json/' . $_SERVER['REMOTE_ADDR']);
				$ip_geo_data = json_decode($ip_geo_data);
				if ( !empty($ip_geo_data) ){
					$defaults['ip_country'] = sanitize_text_field($ip_geo_data->country_name);
					$defaults['ip_region'] = sanitize_text_field($ip_geo_data->region_name);
					$defaults['ip_city'] = sanitize_text_field($ip_geo_data->city);
					$defaults['ip_zip'] = sanitize_text_field($ip_geo_data->zip_code);
				}
			}

			$defaults = nebula_visitor_data_update_everytime($defaults);

			$all_data = array_merge($defaults, $data); //Add any passed data
			nebula_visitors_create_missing_columns($all_data);
			$all_strings = array_fill(0, count($all_data), '%s'); //Create sanitization array

			$wpdb->insert('nebula_visitors', $all_data, $all_strings); //Insert a row with all the default (and passed) sanitized values.

			check_if_known($send_to_hubspot);

			nebula_remove_expired_visitors();
			return true;
		}
	}

	return false;
}

//Update certain visitor data everytime.
//Pass the existing data array to this to append to it (otherwise a new array is created)!
function nebula_visitor_data_update_everytime($defaults=array()){
	$defaults['ga_cid'] = ga_parse_cookie();
	$defaults['notable_poi'] = nebula_poi();
	$defaults['last_modified_date'] = time();
	$defaults['nebula_session_id'] = nebula_session_id();
	$defaults['score'] = nebula_calculate_visitor_score(); //Try to limit this (without doing a DB query to check)

	//Avoid ternary operators to prevent overwriting existing data (like manual DB entries)

	//Check for nv_ query parameters
	if ( !empty($_SERVER['QUERY_STRING']) ){
		foreach ( parse_str($_SERVER['QUERY_STRING']) as $key => $value ){
			if ( strpos($key, 'nv_') === 0 ){
				if ( empty($value) ){
					$value = 'true';
				}
				$defaults[sanitize_key(substr($key, 3))] = sanitize_text_field(str_replace('+', ' ', urldecode($value)));
			}
		}
	}

	//Logged-in User Data
	if ( is_user_logged_in() ){
		$defaults['wp_user_id'] = get_current_user_id();

		$user = get_userdata(get_current_user_id());
		if ( !empty($user) ){
			//Default WordPress user info
			if ( !empty($user->roles[0]) ){
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

	//Device information
	$defaults['user_agent'] = sanitize_text_field($_SERVER['HTTP_USER_AGENT']);
	$defaults['device_form_factor'] = nebula_get_device('formfactor');
	$defaults['device_full'] = nebula_get_device('full');
	$defaults['device_brand'] = nebula_get_device('brand');
	$defaults['device_model'] = nebula_get_device('model');
	$defaults['device_type'] = nebula_get_device('type');
	$defaults['os_full'] = nebula_get_os('full');
	$defaults['os_name'] = nebula_get_os('name');
	$defaults['os_version'] = nebula_get_os('version');
	$defaults['browser_full'] = nebula_get_browser('full');
	$defaults['browser_name'] = nebula_get_browser('name');
	$defaults['browser_version'] = nebula_get_browser('version');
	$defaults['browser_engine'] = nebula_get_browser('engine');
	$defaults['browser_type'] = nebula_get_browser('type');

	return $defaults;
}

//Calculate and update the visitor with a score
function nebula_calculate_visitor_score($id=null){
	if ( nebula_option('visitors_db') ){
		global $wpdb;

		//Which visitor data to calculate for?
		if ( !empty($id) && current_user_can('manage_options') ){ //If an ID is passed and the user is an admin, use the passed visitor ID (Note: ID is from the visitor DB- not WordPress ID).
			$this_visitor = $wpdb->get_results("SELECT * FROM nebula_visitors WHERE id = '" . $id . "'");
		} else { //Else, use the current visitor
			$nebula_id = get_nebula_id();
			if ( !empty($nebula_id) ){
				$this_visitor = $wpdb->get_results("SELECT * FROM nebula_visitors WHERE nebula_id = '" . $nebula_id . "'");
			}
		}

		if ( $this_visitor ){
			$this_visitor = (array) $this_visitor[0];

			//A score of 100+ will prevent deletion
			$point_values = array(
				'notable_poi' => 100,
				'known' => 100,
				'hubspot_vid' => 100,
				'notes' => 25,
				'notable_download' => 10,
				'pdf_view' => 10,
				'internal_search' => 20,
				'contact_method' => 75,
				'ecommerce_addtocart' => 75,
				'ecommerce_checkout' => 75,
				'engaged_reader' => 10,
				'contact_funnel' => 25,
				'street_full' => 75,
				'geo_latitude' => 75,
				'video_play' => 10,
				'video_engaged' => 25,
				'video_finished' => 25,
				'fb_like' => 20,
				'fb_share' => 20,
				'score_mod' => $this_visitor['score_mod'],
			);

			$score = ( $this_visitor['session_count'] > 1 )? $this_visitor['session_count'] : 0; //Start the score at the session count if higher than 1
			foreach ( $this_visitor as $column => $value ){
				if ( array_key_exists($column, $point_values) && !empty($value) ){
					if ( $column == 'notes' && $value == 'This user tracked by IP and User Agent.' ){
						continue;
					}
					$score += $point_values[$column];
				}
			}

			return $score;
		}
	}

	return false;
}

//Remove expired visitors from the DB
//This is only ran when Nebula Options are saved, and when *new* visitors are inserted.
function nebula_remove_expired_visitors(){
	if ( nebula_option('visitors_db') ){
		global $wpdb;
		$expiration_length = time()-2592000; //30 days
		$wpdb->query($wpdb->prepare("DELETE FROM nebula_visitors WHERE last_modified_date < %d AND known = %d AND score < %d", $expiration_length, 0, 100));
	}
}

//Look up what columns currently exist in the nebula_visitors table.
function nebula_visitors_existing_columns(){
	global $wpdb;
	return $wpdb->get_col("SHOW COLUMNS FROM nebula_visitors", 0); //Returns an array of current table column names
}

//Query email address or Hubspot VID to see if the user is known
function check_if_known($send_to_hubspot=true){
	if ( nebula_option('visitors_db') ){
		global $wpdb;
		$nebula_id = get_nebula_id();

		$known_visitor = $wpdb->get_results("SELECT * FROM nebula_visitors WHERE nebula_id LIKE '" . $nebula_id . "' AND (email_address REGEXP '.+@.+\..+' OR hubspot_vid REGEXP '^\d+$')");
		if ( !empty($known_visitor) ){
			if ( !is_known() ){
				nebula_update_visitor(array('known' => '1')); //Update to known visitor (if previously unknown)
			}

			$known_visitor_data = (array) $known_visitor[0];
			if ( !empty($send_to_hubspot) ){
				nebula_prep_data_for_hubspot_crm_delivery($known_visitor_data);
			}
			return true;
		}
	}

	return false;
}

//Lookup if this visitor is known
function is_known(){
	$known = nebula_get_visitor_data('known');
	if ( !empty($known) && ($known == 1 || $known == '1') ){ //@TODO "Nebula" 0: Figure out which of these is best
		return true;
	}

	return false;
}

//Prepare Nebula Visitor data to be sent to Hubspot CRM
//This includes skipping empty fields, ignoring certain fields, and renaming others.
function nebula_prep_data_for_hubspot_crm_delivery($data){
	if ( nebula_option('hubspot_api') ){
		$data_for_hubspot = array();
		if ( !empty($data['hubspot_vid']) ){
			$data_for_hubspot['hubspot_vid'] = $data['hubspot_vid'];
		}
		if ( !empty($data['email_address']) ){
			$data_for_hubspot['email_address'] = $data['email_address'];
		}
		$data_for_hubspot['properties'] = array();

		$ignore_columns = array('id', 'known', 'last_modified_date', 'first_session', 'prev_session', 'last_session_id', 'current_session', 'hubspot_vid', 'bot', 'current_session_pageviews', 'score', 'expiration', 'street_number', 'street_name', 'zip_suffix', 'zip_full');
		$rename_columns = array(
			'ip_address' => 'nebula_ip',
			'ip_city' => 'nebula_ip_city',
			'ip_region' => 'nebula_ip_region',
			'ip_country' => 'nebula_ip_country',
			'street_full' => 'address',
			'state_abbr' => 'state',
			'country_name' => 'country',
			'zip_code' => 'zip',
			'first_name' => 'firstname',
			'last_name' => 'lastname',
			'email_address' => 'email',
			'phone_number' => 'phone',
		);

		foreach ( $data as $column => $value ){
			//Skip empty column values
			if ( empty($value) ){
				continue;
			}

			//Ignore unnecessary columns
			if ( in_array($column, $ignore_columns) ){
				continue;
			}

			//Rename certain columns to Hubspot CRM notation
			if ( array_key_exists($column, $rename_columns) ){
				$column = $rename_columns[$column];
			}

			//Add the column/value to the Hubspot data array
			$data_for_hubspot['properties'][] = array(
				'property' => $column,
				'value' => $value
			);
		}

		nebula_send_to_hubspot($data_for_hubspot);
	}
}

/*==========================
	Hubspot CRM Integration Functions
 ===========================*/

//Send data to Hubspot CRM via PHP curl
function nebula_hubspot_curl($url, $content=null){
	$sep = ( strpos($url, '?') === false )? '?' : '&';
	$curl = curl_init($url . $sep . 'hapikey=' . nebula_option('hubspot_api'));
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	if ( !empty($content) ){
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
	}

	return curl_exec($curl);
}

//Get all existing Hubspot CRM contact properties in the Nebula group
function get_nebula_hubspot_properties(){
	$all_hubspot_properties = nebula_hubspot_curl('https://api.hubapi.com/contacts/v2/properties');
	$all_hubspot_properties = json_decode($all_hubspot_properties, true);

	$existing_nebula_properties = array();
	foreach ( $all_hubspot_properties as $property ){
		if ( $property['groupName'] == 'nebula' ){
			$existing_nebula_properties[] = $property['name'];
		}
	}

	return $existing_nebula_properties;
}

//Create Custom Properties
function nebula_create_hubspot_properties($columns=null){
	if ( nebula_option('hubspot_portal') ){
		//Create the Nebula group of properties
		$content = '{
	        "name": "nebula",
	        "displayName": "Nebula",
	        "displayOrder": 5
	    }';
		nebula_hubspot_curl('http://api.hubapi.com/contacts/v2/groups?portalId=' . nebula_option('hubspot_portal'), $content);

		//Get an array of all existing Hubspot CRM contact properties
		$existing_nebula_properties = get_nebula_hubspot_properties();

		//Create Nebula IP custom property within the Nebula group
		if ( !in_array('nebula_ip', $existing_nebula_properties) ){
			$content = '{
				"name": "nebula_ip",
				"label": "IP Address (Nebula)",
				"description": "The IP address.",
				"groupName": "nebula",
				"type": "string",
				"fieldType": "text",
				"formField": true,
				"displayOrder": 6,
				"options": []
			}';
			nebula_hubspot_curl('https://api.hubapi.com/contacts/v2/properties', $content);
		}

		//Create a property from the passed array
		//@todo "Nebula" 0: This loop is accounting for 1 second of server time (about 0.14s per call)! Need to find a way to optimize...
		if ( !empty($columns) ){
			if ( is_string($columns) ){
				$columns = array($columns);
			}
			foreach ( $columns as $column ){
				$column_label = ucwords(str_replace('_', ' ', $column));
				if ( !in_array($column, $existing_nebula_properties) ){
					//Create Nebula ID custom property within the Nebula group
					$content = '{
						"name": "' . $column . '",
						"label": "' . $column_label . '",
						"description": "' . $column_label . ' (Parsed from Visitor DB).",
						"groupName": "nebula",
						"type": "string",
						"fieldType": "text",
						"formField": true,
						"displayOrder": 6,
						"options": []
					}';
					nebula_hubspot_curl('https://api.hubapi.com/contacts/v2/properties', $content);
				}
			}
		}
	}
}

//Create/Update Contact in Hubspot CRM
add_action('wp_ajax_nebula_ajax_send_to_hubspot', 'nebula_ajax_send_to_hubspot');
add_action('wp_ajax_nopriv_nebula_ajax_send_to_hubspot', 'nebula_ajax_send_to_hubspot');
function nebula_ajax_send_to_hubspot(){
	if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }

	$data = array(
		'properties' => $_POST['properties'],
	);

	echo nebula_send_to_hubspot($data);
	exit;
}
function nebula_send_to_hubspot($data=array()){
	if ( nebula_option('hubspot_api') ){
		//Determine if we'll be using email_address or hubspot_vid for our send. Prefer VID.
		if ( empty($data['hubspot_vid']) && empty($data['email_address']) ){ //If calling from AJAX we must lookup VID or Email Address
			global $wpdb;
			$nebula_id = get_nebula_id();
			$vid_and_email = $wpdb->get_results("SELECT hubspot_vid, email_address FROM nebula_visitors WHERE nebula_id LIKE '" . $nebula_id . "' AND email_address <> '' OR hubspot_vid <> ''"); //here
			$vid_and_email = (array) $vid_and_email[0];
			$hubspot_vid = $vid_and_email['hubspot_vid'];
			$email_address = $vid_and_email['email_address'];
		} else { //Calling directly from another PHP function
			if ( !empty($data['hubspot_vid']) ){
				$hubspot_vid = $data['hubspot_vid'];
			}
			if ( !empty($data['email_address']) ){
				$email_address = $data['email_address'];
			}
		}

		if ( !empty($hubspot_vid) || !empty($email_address) ){ //If visitor has hubspot_vid or email_address
			//Create the properties array
			$content = array('properties' => array());
			$needed_properties = array();

			//Loop through provided properties
			foreach ( $data['properties'] as $group ){
				$needed_properties[] = $group['property'];

				$content['properties'][] = array(
					'property' => $group['property'],
					'value' => $group['value']
				);
			}

			nebula_create_hubspot_properties($needed_properties); //Check and create existing properties

			if ( !empty($hubspot_vid) ){
				$response = nebula_hubspot_curl('https://api.hubapi.com/contacts/v1/contact/vid/' . $hubspot_vid . '/profile', json_encode($content)); //Update the existing contact using their VID

				if ( strpos($response, 'error') != false ){ //There was an error
					return false;
				} elseif ( is_string($response) && $response == '' ){ //This API reponse is simply a 200 when it succeeds (empty string).
					nebula_update_visitor(array('hubspot_vid' => $hubspot_vid), false); //Update visitor withour re-sending to Hubspot CRM
					return $hubspot_vid;
				}
			} else {
				$response = nebula_hubspot_curl('https://api.hubapi.com/contacts/v1/contact/createOrUpdate/email/' . $email_address . '/', json_encode($content)); //Create or update the contact using their Email
				if ( strpos($response, 'error') != false ){ //There was an error
					return false;
				} else {
					$response = (array) json_decode($response);
					nebula_update_visitor(array('hubspot_vid' => $response['vid']), false); //Update visitor withour re-sending to Hubspot CRM
					return $response['vid'];
				}
			}
		}
	}

	return false;
}

//Get contact data from Hubspot
//Set this to a variable to avoid multiple calls, then parse it like this: $hubspot_data['properties']['firstname']['value']
function nebula_get_hubspot_contact($vid=null, $property=''){
	global $nebula;
	if ( empty($vid) ){
		$vid = nebula_get_visitor_data('hubspot_vid');
		if ( empty($vid) ){
			return false;
		}
	}

	if ( !empty($property) ){
		$property = '&property=' . $property;
	}

	WP_Filesystem();
	global $wp_filesystem;
	$contact_data = $wp_filesystem->get_contents('https://api.hubapi.com/contacts/v1/contact/vid/' . $vid . '/profile?hapikey=' . nebula_option('hubspot_api') . $property);

	return json_decode($contact_data, true);
}

//Detect Notable POI
function nebula_poi(){
	if ( nebula_option('notableiplist') ){
		$notable_ip_lines = explode("\n", nebula_option('notableiplist'));
		foreach ( $notable_ip_lines as $line ){
			$ip_info = explode(' ', strip_tags($line), 2); //0 = IP Address or RegEx pattern, 1 = Name
			if ( ($ip_info[0][0] === '/' && preg_match($ip_info[0], $_SERVER['REMOTE_ADDR'])) || $ip_info[0] == $_SERVER['REMOTE_ADDR'] ){ //If regex pattern and matches IP, or if direct match
				return str_replace(array("\r\n", "\r", "\n"), '', $ip_info[1]);
				break;
			}
		}
	} elseif ( isset($_GET['poi']) ){ //If POI query string exists //@TODO "Nebula" 0: in main.js strip this query string off the URL somehow?
		return str_replace(array('%20', '+'), ' ', $_GET['poi']);
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
		return ( $return == 'count' )? 0 : false; //If this happens it indicates an error.
	}

	$user_online_count = 0;
	$online_users = array();
	foreach ( $logged_in_users as $user ){
		if ( !empty($user['username']) && isset($user['last']) && $user['last'] > time()-600 ){
			$online_users[] = $user;
			$user_online_count++;
		}
	}

	return ( $return == 'count' )? $user_online_count : $online_users;
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
		list($current_user_email, $current_user_domain) = explode('@', $current_user->user_email); //@TODO "Nebula" 0: If $current_user->user_email is not empty?

		$devEmails = explode(',', nebula_option('dev_email_domain'));
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
		list($current_user_email, $current_user_domain) = explode('@', $current_user->user_email); //@TODO "Nebula" 0: If $current_user->user_email is not empty?

		//Check if the current user's email domain matches any of the client email domains from Nebula Options
		$clientEmails = explode(',', nebula_option('client_email_domain'));
		foreach ( $clientEmails as $clientEmail ){
			if ( trim($clientEmail) == $current_user_domain ){
				return true;
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
	} else {
		return false;
	}
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
			if ( $url_components['scheme'] != '' ){
				return $url_components['scheme'];
			} else {
				return false;
			}
			break;

		case ('port'):
			if ( $url_components['port'] ){
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
			if ( $url_components['user'] ){
				return $url_components['user'];
			} else {
				return false;
			}
			break;

		case ('pass'): //Returns the password from this type of syntax: https://username:password@gearside.com/
		case ('password'):
			if ( $url_components['pass'] ){
				return $url_components['pass'];
			} else {
				return false;
			}
			break;

		case ('authority'):
			if ( $url_components['user'] && $url_components['pass'] ){
				return $url_components['user'] . ':' . $url_components['pass'] . '@' . $url_components['host'] . ':' . nebula_url_components('port', $url);
			} else {
				return false;
			}
			break;

		case ('host'): //In http://something.example.com the host is "something.example.com"
		case ('hostname'):
			return $url_components['host'];
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
			return $url_components['scheme'] . '://' . $domain[0];
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
			return $url_components['path'];
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
			return $url_components['query'];
			break;

		case ('fragment'):
		case ('fragments'):
		case ('anchor'):
		case ('hash') :
		case ('hashtag'):
		case ('id'):
			return $url_components['fragment'];
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
	}
	return get_template_directory_uri() . '/images/x.png'; //Placehold.it is not available.
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

//Prefer a child theme directory or file. Not declaring a directory will return the theme directory.
//nebula_prefer_child_directory('/images/logo.png');
function nebula_prefer_child_directory($directory='', $uri=true){
	if ( $directory[0] != '/' ){
		$directory = '/' . $directory;
	}

	if ( file_exists(get_stylesheet_directory() . $directory) ){
		if ( $uri ){
			return get_stylesheet_directory_uri() . $directory;
		}
		return get_stylesheet_directory() . $directory;
	}

	if ( $uri ){
		return get_template_directory_uri() . $directory;
	}
	return get_template_directory() . $directory;
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
	SCSS Compiling
 ===========================*/

if ( nebula_option('scss', 'enabled') ){
	if ( is_writable(get_template_directory()) ){
		add_action('init', 'nebula_render_scss');
	}
}
function nebula_render_scss($child=false){
	$override = apply_filters('pre_nebula_render_scss', false, $child);
	if ( $override !== false ){return $override;}

	if ( nebula_option('scss', 'enabled') ){
		$compile_all = false;
		if ( isset($_GET['sass']) || isset($_GET['scss']) || isset($_GET['settings-updated']) && is_staff() ){
			$compile_all = true;
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

		if ( nebula_option('minify_css', 'enabled') && !is_debug() ){
			$scss->setFormatter('Leafo\ScssPhp\Formatter\Compressed'); //Minify CSS (while leaving "/*!" comments for WordPress).
		} else {
			$scss->setFormatter('Leafo\ScssPhp\Formatter\Compact'); //Compact, but readable, CSS lines
			if ( is_debug() ){
				$scss->setLineNumberStyle(\Leafo\ScssPhp\Compiler::LINE_COMMENTS); //Adds line number reference comments in the rendered CSS file for debugging.
			}
		}

		//Variables
		$scss->setVariables(array(
			'template_directory' => '"' . get_template_directory_uri() . '"',
			'stylesheet_directory' => '"' . get_stylesheet_directory_uri() . '"',
			'__utm-gif' => '"' . ga_UTM_gif() . '"',
			//Primary Color?
			//Secondary Color?
			//Tertiary Color?
		));

		//Partials
		$latest_partial = 0;
		foreach ( glob($stylesheets_directory . '/scss/partials/*') as $partial_file ){
			if ( filemtime($partial_file) > $latest_partial ){
				$latest_partial = filemtime($partial_file);
			}
		}

		//Combine Developer Stylesheets
		if ( nebula_option('dev_stylesheets', 'enabled') ){
			nebula_combine_dev_stylesheets($stylesheets_directory, $stylesheets_directory_uri);
		}

		//Compile each SCSS file
		foreach ( glob($stylesheets_directory . '/scss/*.scss') as $file ){ //@TODO "Nebula" 0: Change to glob_r() but will need to create subdirectories if they don't exist.
			$file_path_info = pathinfo($file);

			if ( $file_path_info['filename'] == 'wireframing' && nebula_option('prototype_mode', 'disabled') ){ //If file is wireframing.scss but wireframing functionality is disabled, skip file.
				continue;
			}
			if ( $file_path_info['filename'] == 'dev' && nebula_option('dev_stylesheets', 'disabled') ){ //If file is dev.scss but dev stylesheets functionality is disabled, skip file.
				continue;
			}
			if ( !is_admin_page() && in_array($file_path_info['filename'], array('login', 'admin', 'tinymce')) ){ //If viewing front-end, skip WP admin files.
				continue;
			}

			if ( is_file($file) && $file_path_info['extension'] == 'scss' && $file_path_info['filename'][0] != '_' ){ //If file exists, and has .scss extension, and doesn't begin with "_".
				$css_filepath = ( $file_path_info['filename'] == 'style' )? $theme_directory . '/style.css': $stylesheets_directory . '/css/' . $file_path_info['filename'] . '.css';
				wp_mkdir_p($stylesheets_directory . '/css'); //Create the /css directory (in case it doesn't exist already).

				//If style.css has been edited after style.scss, save backup but continue compiling SCSS
				if ( ($file_path_info['filename'] == 'style' && file_exists($css_filepath) && nebula_data('scss_last_processed') != '0' && nebula_data('scss_last_processed')-filemtime($css_filepath) < 0) ){ //@todo "Nebula" 0: Getting a lot of false positives here
					copy($css_filepath, $css_filepath . '.bak'); //Backup the style.css file to style.css.bak
					if ( is_dev() || current_user_can('manage_options') ){
						global $scss_debug_ref;
						$scss_debug_ref = ( $child )? 'C' : 'P';
						$scss_debug_ref .= (nebula_data('scss_last_processed')-filemtime($css_filepath));
						add_action('wp_head', 'nebula_scss_console_warning'); //Call the console error note
					}
				}

				if ( !file_exists($css_filepath) || filemtime($file) > filemtime($css_filepath) || $latest_partial > filemtime($css_filepath) || is_debug() || $compile_all ){ //If .css file doesn't exist, or is older than .scss file (or any partial), or is debug mode, or forced
					ini_set('memory_limit', '512M'); //Increase memory limit for this script. //@TODO "Nebula" 0: Is this the best thing to do here? Other options?
					WP_Filesystem();
					global $wp_filesystem;
					$existing_css_contents = ( file_exists($css_filepath) )? $wp_filesystem->get_contents($css_filepath) : '';

					if ( !strpos(strtolower($existing_css_contents), 'scss disabled') ){ //If the correlating .css file doesn't contain a comment to prevent overwriting
						$this_scss_contents = $wp_filesystem->get_contents($file); //Copy SCSS file contents
						$compiled_css = $scss->compile($this_scss_contents); //Compile the SCSS
						$enhanced_css = nebula_scss_post_compile($compiled_css); //Compile server-side variables into SCSS
						$wp_filesystem->put_contents($css_filepath, $enhanced_css); //Save the rendered CSS.
						nebula_update_data('scss_last_processed', time());
					}
				}
			}
		}

		if ( !$child && is_child_theme() ){ //If not in the second (child) pass, and is a child theme.
			nebula_render_scss(true); //Re-run on child theme stylesheets
		}

		//If SCSS has not been rendered in 1 month, disable the option.
		if ( time()-nebula_data('scss_last_processed') >= 2592000 ){
			nebula_update_option('scss', 'disabled');
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
		$directory = get_template_directory() . '/stylesheets';
		$directory_uri = get_template_directory_uri() . "/stylesheets";
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

			//Include partials in dev.scss
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
	if ( nebula_option('device_detection') ){
		$override = apply_filters('pre_nebula_is_mobile', false);
		if ( $override !== false ){return $override;}

		if ( $GLOBALS["device_detect"]->isMobile() ){
			return true;
		}
	}

	return false;
}

//Boolean return if the user's device is a tablet.
function nebula_is_tablet(){
	if ( nebula_option('device_detection') ){
		$override = apply_filters('pre_nebula_is_tablet', false);
		if ( $override !== false ){return $override;}

		if ( $GLOBALS["device_detect"]->isTablet() ){
			return true;
		}
	}

	return false;
}

//Boolean return if the user's device is a desktop.
function nebula_is_desktop(){
	if ( nebula_option('device_detection') ){
		$override = apply_filters('pre_nebula_is_desktop', false);
		if ( $override !== false ){return $override;}

		if ( $GLOBALS["device_detect"]->isDesktop() ){
			return true;
		}
	}

	return false;
}

//Returns the requested information of the operating system of the user's device.
function nebula_get_os($info='full'){
	if ( nebula_option('device_detection') ){
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
}

//Check to see how the operating system version of the user's device compares to a passed version number.
function nebula_is_os($os=null, $version=null, $comparison='=='){
	if ( nebula_option('device_detection') ){
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
	}

	return false;
}

//Returns the requested information of the model of the user's device.
function nebula_get_device($info='model'){
	if ( nebula_option('device_detection') ){
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
}

//Returns the requested information of the browser being used.
function nebula_get_client($info){ return get_browser($info); }
function nebula_get_browser($info='name'){
	if ( nebula_option('device_detection') ){
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
}

//Check to see how the browser version compares to a passed version number.
function nebula_is_browser($browser=null, $version=null, $comparison='=='){
	if ( nebula_option('device_detection') ){
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
	if ( nebula_option('device_detection') ){
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
	}

	return false;
}

//Check for bot/crawler traffic
//UA lookup: http://www.useragentstring.com/pages/Crawlerlist/
function nebula_is_bot(){
	if ( nebula_option('device_detection') ){
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
	}

	return false;
}