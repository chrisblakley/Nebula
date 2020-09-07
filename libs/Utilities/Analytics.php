<?php
	/*
		GA Parameter Guide: https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters?hl=en
		GA Hit Builder: https://ga-dev-tools.appspot.com/hit-builder/
	*/

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Analytics') ){
	trait Analytics {
		public function hooks(){
			add_filter('nebula_brain', array($this, 'ga_definitions'));

			add_filter('the_permalink_rss', array($this, 'add_utm_to_feeds'), 100);
			add_filter('the_excerpt_rss', array($this, 'add_utm_to_feeds_content_links'), 200);
			add_filter('the_content_feed', array($this, 'add_utm_to_feeds_content_links'), 200);

			add_action('nebula_head_open', array($this, 'google_optimize_tag'));

			register_shutdown_function(array($this, 'ga_log_fatal_errors'));
		}

		//If analytics should be allowed.
		//Note: be careful using this conditional for AJAX analytics as the request is made by the server IP.
		public function is_analytics_allowed(){
			if ( $this->option('observe_dnt') && $this->is_do_not_track() ){
				return false;
			}

			if ( isset($_GET['noga']) || is_customize_preview() ){ //Disable analytics for ?noga query string
				return false;
			}

			if ( $this->get_ip_address() === wp_privacy_anonymize_ip($_SERVER['SERVER_ADDR']) ){ //Disable analytics for self-requests by the server
				return false;
			}

			return true;
		}

		//If the "Do Not Track" browser setting is enabled
		//True = DNT, False = tracking allowed
		public function is_do_not_track(){
			if ( isset($_SERVER['HTTP_DNT']) && $_SERVER['HTTP_DNT'] == 1 ){
				return true;
			}

			return false;
		}

		//Handle the parsing of the _ga cookie or setting it to a unique identifier
		public function ga_parse_cookie(){
			$override = apply_filters('pre_ga_parse_cookie', null);
			if ( isset($override) ){return $override;}

			$cid = $this->generate_UUID();
			if ( isset($_COOKIE['_ga']) ){
				list($version, $domainDepth, $cid1, $cid2) = explode('.', $_COOKIE["_ga"], 4);
				$contents = array('version' => $version, 'domainDepth' => $domainDepth, 'cid' => $cid1 . '.' . $cid2);
				$cid = $contents['cid'];
			}

			return esc_html($cid);
		}

		//Google Optimize Tag
		//Note: Experiment ID and Variant are core dimensions in Google Analytics!
		public function google_optimize_tag(){
			if ( $this->get_option('google_optimize_id') ){ ?>
				<script src="https://www.googleoptimize.com/optimize.js?id=<?php echo $this->get_option('google_optimize_id'); ?>"></script>
			<?php }
		}

		//Generate UUID v4 function (needed to generate a CID when one isn't available)
		public function generate_UUID(){
			$override = apply_filters('pre_generate_UUID', null);
			if ( isset($override) ){return $override;}

			return sprintf(
				'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
				mt_rand(0, 0xffff), mt_rand(0, 0xffff), //32 bits for "time_low"
				mt_rand(0, 0xffff), //16 bits for "time_mid"
				mt_rand(0, 0x0fff) | 0x4000, //16 bits for "time_hi_and_version", Four most significant bits holds version number 4
				mt_rand(0, 0x3fff) | 0x8000, //16 bits, 8 bits for "clk_seq_hi_res", 8 bits for "clk_seq_low", Two most significant bits holds zero and one for variant DCE1.1
				mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff) //48 bits for "node"
			);
		}

		//Return the index of the custom dimension or metric
		public function ga_definition_index($definition){
			return str_replace(array('dimension', 'metric'), '', esc_html($definition));
		}

		//Store analytics and custom dimensions/metrics into the Nebula data object
		public function ga_definitions($brain){
			$brain['analytics'] = array( //Set this even if analytics is not enabled
				'isReady' => false,
				'trackingID' => esc_html($this->get_option('ga_tracking_id')),
				'dimensions' => array(
					'gaCID' => esc_html($this->get_option('cd_gacid')),
					'hitID' => esc_html($this->get_option('cd_hitid')),
					'hitTime' => esc_html($this->get_option('cd_hittime')),
					'hitType' => esc_html($this->get_option('cd_hittype')),
					'hitInteractivity' => esc_html($this->get_option('cd_hitinteractivity')),
					'hitMethod' => esc_html($this->get_option('cd_hitmethod')),
					'saveData' => esc_html($this->get_option('cd_savedata')),
					'reducedMotion' => esc_html($this->get_option('cd_reducedmotion')),
					'offline' => esc_html($this->get_option('cd_offline')),
					'deviceMemory' => esc_html($this->get_option('cd_devicememory')),
					'batteryMode' => esc_html($this->get_option('cd_batterymode')),
					'batteryPercent' => esc_html($this->get_option('cd_batterypercent')),
					'network' => esc_html($this->get_option('cd_network')),
					'referrer' => esc_html($this->get_option('cd_referrer')),
					'navigationtype' => esc_html($this->get_option('cd_navigationtype')),
					'redirectcount' => esc_html($this->get_option('cd_redirectcount')),
					'author' => esc_html($this->get_option('cd_author')),
					'businessHours' => esc_html($this->get_option('cd_businesshours')),
					'categories' => esc_html($this->get_option('cd_categories')),
					'tags' => esc_html($this->get_option('cd_tags')),
					'contactMethod' => esc_html($this->get_option('cd_contactmethod')),
					'formTiming' => esc_html($this->get_option('cd_formtiming')),
					'formFlow' => esc_html($this->get_option('cd_formflow')),
					'windowType' => esc_html($this->get_option('cd_windowtype')),
					'geolocation' => esc_html($this->get_option('cd_geolocation')),
					'geoAccuracy' => esc_html($this->get_option('cd_geoaccuracy')),
					'geoName' => esc_html($this->get_option('cd_geoname')),
					'wpmlLang' => esc_html($this->get_option('cd_wpmllang')),
					'relativeTime' => esc_html($this->get_option('cd_relativetime')),
					'sessionID' => esc_html($this->get_option('cd_sessionid')),
					'securityNote' => esc_html($this->get_option('cd_securitynote')),
					'poi' => esc_html($this->get_option('cd_notablepoi')),
					'role' => esc_html($this->get_option('cd_role')),
					'timestamp' => esc_html($this->get_option('cd_timestamp')),
					'userID' => esc_html($this->get_option('cd_userid')),
					'fbID' => esc_html($this->get_option('cd_fbid')),
					'videoWatcher' => esc_html($this->get_option('cd_videowatcher')),
					'eventIntent' => esc_html($this->get_option('cd_eventintent')),
					'wordCount' => esc_html($this->get_option('cd_wordcount')),
					'weather' => esc_html($this->get_option('cd_weather')),
					'temperature' => esc_html($this->get_option('cd_temperature')),
					'publishDate' => esc_html($this->get_option('cd_publishdate')),
					'blocker' => esc_html($this->get_option('cd_blocker')),
					'queryString' => esc_html($this->get_option('cd_querystring')),
					'textFragment' => esc_html($this->get_option('cd_textfragment')),
					'mqBreakpoint' => esc_html($this->get_option('cd_mqbreakpoint')),
					'mqResolution' => esc_html($this->get_option('cd_mqresolution')),
					'mqOrientation' => esc_html($this->get_option('cd_mqorientation')),
					'visibilityState' => esc_html($this->get_option('cd_visibilitystate')),
				),
				'metrics' => array(
					'serverResponseTime' => esc_html($this->get_option('cm_serverresponsetime')),
					'domReadyTime' => esc_html($this->get_option('cm_domreadytime')),
					'windowLoadedTime' => esc_html($this->get_option('cm_windowloadedtime')),
					'batteryLevel' => esc_html($this->get_option('cm_batterylevel')),
					'formImpressions' => esc_html($this->get_option('cm_formimpressions')),
					'formStarts' => esc_html($this->get_option('cm_formstarts')),
					'formSubmissions' => esc_html($this->get_option('cm_formsubmissions')),
					'notableDownloads' => esc_html($this->get_option('cm_notabledownloads')),
					'engagedReaders' => esc_html($this->get_option('cm_engagedreaders')),
					'pageVisible' => esc_html($this->get_option('cm_pagevisible')),
					'pageHidden' => esc_html($this->get_option('cm_pagehidden')),
					'videoStarts' => esc_html($this->get_option('cm_videostarts')),
					'videoPlaytime' => esc_html($this->get_option('cm_videoplaytime')),
					'videoCompletions' => esc_html($this->get_option('cm_videocompletions')),
					'autocompleteSearches' => esc_html($this->get_option('cm_autocompletesearches')),
					'autocompleteSearchClicks' => esc_html($this->get_option('cm_autocompletesearchclicks')),
					'wordCount' => esc_html($this->get_option('cm_wordcount')),
					'maxScroll' => esc_html($this->get_option('cm_maxscroll')),
				),
			);

			return $brain;
		}

		//Nebula usage data
		public function usage($action, $data=array()){
			$date = new DateTime("now", new DateTimeZone('America/New_York'));
			$defaults = array(
				'v' => 1,
				't' => 'pageview',
				'tid' => 'UA-36461517-5',
				'cid' => $this->ga_parse_cookie(),
				'ua' => rawurlencode($_SERVER['HTTP_USER_AGENT']),
				'uip' => $this->get_ip_address(),
				'dh' => ( function_exists('gethostname') )? gethostname() : '',
				'dl' => $action,
				'dt' => get_bloginfo('name'), //Consider urlencode() here
				'cd1' => home_url('/'),
				'cd2' => time(),
				'cd8' => $date->format('F j, Y, g:ia'),
				'cd3' => get_bloginfo('version'),
				'cd6' => $this->version('raw'),
				'cd4' => get_bloginfo('description'),
				'cd5' => site_url(),
				'cd7' => $this->ga_parse_cookie(),
				'cd9' => ( is_child_theme() )? 'Child' : 'Parent',
				'cd13' => get_current_user_id(),
				'cd14' => PHP_VERSION,
				'cn' => 'Nebula Usage',
				'cs' => home_url('/'),
				'cm' => 'WordPress'
			);

			if ( strtolower($action) === 'theme activation' ){
				$defaults['cd10'] = ( $this->get_data('first_activation') && time()-$this->get_data('first_activation') > 60 )? 'Reactivated - First at ' . $this->get_data('first_activation') . ' (' . human_time_diff($this->get_data('first_activation')) . ' ago)' : 'Initial Activation';
			}

			$data = array_merge($defaults, $data); //Add passed parameters
			$this->ga_send_data($data, true);
		}

		//Log fatal errors in Google Analytics as crashes
		public function ga_log_fatal_errors(){
			$error = error_get_last();
			if ( isset($error) && $error['type'] === E_ERROR ){
				$message = str_replace(WP_CONTENT_DIR, '', strstr($error['message'], ' in /', true)); //Remove high-level directories to reduce clutter and prevent PII
				$file = str_replace(WP_CONTENT_DIR, '', strstr($error['file'], 'wp-content')); //Remove high-level directories to reduce clutter and prevent PII
				$this->ga_send_exception('(PHP) ' . $message . ' on line ' . $error['line'] . ' in .../' . $file, 1);

				if ( preg_match('/themes\/Nebula-?(master|parent|\d+\.\d+)?\//i', $file) && !strpos(strtolower($file), 'scssphp') ){ //If the error is in Nebula parent (and not a Sass compile error) log it for continued improvement of Nebula itself
					$this->usage('PHP Fatal Error', array(
						't' => 'exception',
						'exd' => $message . ' on line ' . $error['line'] . ' in ' . $file,
						'exf' => true,
						'cd12' => (( isset($_SERVER['HTTPS']) )? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
					));
				}
			}
		}

		//Track Sever-Side Exceptions
		//Note this ignores the "Server-Side Fallback" analytics Nebula option
		//Also note that this does create a session in GA, so when initial requests 404, there will be (not set) Landing Pages and more users/sessions than pageviews. This can compound for things like metadata images where devices request many in batches and don't actually render anything beyond the response code (so a 404 will still cause this server-side GA payload, but will not have a JavaScript pageview).
		public function ga_send_exception($message=null, $fatal=1, $array=array()){
			$override = apply_filters('pre_ga_send_exception', null, $message, $fatal, $array);
			if ( isset($override) ){return;}

			$data = array(
				't' => 'exception',
				'exd' => $message,
				'exf' => $fatal,
				'dt' => 'Page Not Found'
			);

			$data = array_merge($this->ga_common_parameters(), $data); //Add custom definition parameters
			$data = array_merge($data, $array); //Add passed parameters
			$this->ga_send_data($data, false); //Disabling force parameter here in an attempt to reduce or eliminate "(not set)" landing page data in Google Analytics.
		}

		//Send Data to Google Analytics
		//https://developers.google.com/analytics/devguides/collection/protocol/v1/devguide#event
		public function ga_send_data($data, $force=false){
			$override = apply_filters('pre_ga_send_data', null, $data);
			if ( isset($override) ){return;}

			if ( $force || $this->get_option('ga_server_side_fallback') ){
				$response = wp_remote_get('https://www.google-analytics.com/collect?payload_data&' . http_build_query($data)); //https://ga-dev-tools.appspot.com/hit-builder/
				return $response;
			}

			return false;
		}

		//Add measurement protocol parameters for custom definitions
		public function ga_common_parameters($parameters=array()){
			$default_common_parameters = array(
				'v' => 1, //Protocol Version
				'tid' => $this->get_option('ga_tracking_id'), //Tracking ID
				'cid' => $this->ga_parse_cookie(), //Client ID
				'ua' => rawurlencode($_SERVER['HTTP_USER_AGENT']), //User Agent
				'uip' => $this->get_ip_address(), //Anonymized IP Address
				'ul' => ( class_exists('Locale') )? locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']) : '', //User Language
				'dr' => ( isset($_SERVER['HTTP_REFERER']) )? $_SERVER['HTTP_REFERER'] : '', //Referrer
				'dl' => $this->requested_url(), //Likely "admin-ajax.php" until overwritten
				'dt' => ( get_the_title() )? get_the_title() : '', //Likely empty until overwritten
			);

			//User ID
			if ( is_user_logged_in() ){
				$default_common_parameters['uid'] = get_current_user_id(); //User ID
				if ( $this->get_option('cd_userid') ){
					$default_common_parameters['cd' . $this->ga_definition_index($this->get_option('cd_userid'))] = get_current_user_id();
				}
			}

			//Session ID
			if ( $this->get_option('cd_sessionid') ){
				$default_common_parameters['cd' . $this->ga_definition_index($this->get_option('cd_sessionid'))] = $this->nebula_session_id();
			}

			//Transport method
			if ( $this->get_option('cd_hitmethod') ){
				$default_common_parameters['cd' . $this->ga_definition_index($this->get_option('cd_hitmethod'))] = 'Server-Side';
			}

			//Anonymize IP
			if ( $this->get_option('ga_anonymize_ip') ){
				$default_common_parameters['aip'] = 1;
			}

			$common_parameters = array_merge($default_common_parameters, $parameters); //Add passed parameters
			$common_parameters = apply_filters('nebula_measurement_protocol_custom_definitions', $common_parameters);

			return $common_parameters;
		}

		//Add Google Analytics UTM parameters to RSS (and other feed) links
		//Manually control UTM parameters by adding them to the feed URL itself: https://example.com/feed?utm_campaign=summer+sale&utm_source=newsletter&utm_medium=email
		public function add_utm_to_feeds($link){
			$utm_query = array();

			//Set the utm_campaign parameter
			if ( isset($_GET['utm_campaign']) ){
				$utm_query[] = 'utm_campaign=' . $_GET['utm_campaign'];
			}

			//Set the utm_source parameter
			$utm_source = $this->url_components('hostname', site_url()); //Default to the hostname of the site
			if ( isset($_GET['utm_source']) ){
				$utm_source = esc_attr($_GET['utm_source']);
			}
			$utm_query[] = 'utm_source=' . $utm_source;

			//Set the utm_medium parameter
			$utm_medium = 'feed';
			if ( isset($_GET['utm_medium']) ){
				$utm_medium = esc_attr($_GET['utm_medium']);
			}
			$utm_query[] = 'utm_medium=' . $utm_medium;

			//Set the utm_content parameter
			if ( isset($_GET['utm_content']) ){
				$utm_query[] = 'utm_content=' . $_GET['utm_content'];
			}

			$url = explode('?', $link);

			//Merge query parameters
			$utm_query_str = implode('&amp;', $utm_query);
			$url_query = $utm_query_str;
			if ( count($url) > 1 ){
				$url_query = "${url[1]}&amp;{$utm_query_str}";
			}

			$tracking_link = "${url[0]}?{$url_query}";
			return $tracking_link;
		}

		//Add tracking to self-referencing links in the excerpt and content
		public function add_utm_to_feeds_content_links($content){
			$link = get_permalink(get_the_ID());
			return str_replace($link, $this->add_utm_to_feeds($link), $content);
		}
	}
}