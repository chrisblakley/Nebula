<?php
	/*
		GA Parameter Guide: https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters?hl=en
		GA Hit Builder: https://ga-dev-tools.appspot.com/hit-builder/
	*/

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Analytics') ){
	trait Analytics {
		public function hooks(){
			if ( !$this->is_background_request() && !is_customize_preview() ){
				add_filter('the_permalink_rss', array($this, 'add_utm_to_feeds'), 100);
				add_filter('the_excerpt_rss', array($this, 'add_utm_to_feeds_content_links'), 200);
				add_filter('the_content_feed', array($this, 'add_utm_to_feeds_content_links'), 200);
			}

			register_shutdown_function(array($this, 'ga_log_fatal_php_errors'));
		}

		public function google_analytics_url(){
			if ( !empty($this->get_option('ga_property_id')) ){
				return 'https://analytics.google.com/analytics/web/?hl=en&pli=1#/p' . $this->get_option('ga_property_id') . '/reports/intelligenthome'; //Link right to this GA property
			}

			return 'https://analytics.google.com/analytics/web/'; //Just link to Google Analytics
		}

		//If analytics should be allowed.
		//Note: be careful using this conditional for AJAX analytics as the request is made by the server IP.
		public function is_analytics_allowed(){
			if ( $this->option('observe_dnt') && $this->is_do_not_track() ){
				return false;
			}

			if ( isset($this->super->get['noga']) || is_customize_preview() ){ //Disable analytics for ?noga query string
				return false;
			}

			if ( $this->get_ip_address() === wp_privacy_anonymize_ip($this->super->server['SERVER_ADDR']) ){ //Disable analytics for self-requests by the server
				return false;
			}

			return true; //Analytics is allowed
		}

		//If the "Do Not Track" browser setting is enabled
		//True = DNT, False = tracking allowed
		public function is_do_not_track(){
			if ( isset($this->super->server['HTTP_DNT']) && $this->super->server['HTTP_DNT'] == 1 ){
				return true;
			}

			return false;
		}

		//Handle the parsing of the _ga cookie or setting it to a unique identifier
		public function ga_parse_cookie(){
			$override = apply_filters('pre_ga_parse_cookie', null);
			if ( isset($override) ){return $override;}

			$cid = $this->generate_UUID(); //Start with a local (non-Google) unique ID
			if ( isset($this->super->cookie['_ga']) ){
				list($version, $domainDepth, $cid1, $cid2) = explode('.', $this->super->cookie["_ga"], 4);
				$contents = array('version' => $version, 'domainDepth' => $domainDepth, 'cid' => $cid1 . '.' . $cid2);
				$cid = $contents['cid'];
			}

			return sanitize_text_field(esc_html($cid));
		}

		//Generate UUID v4 function (needed to generate a CID when one isn't available)
		public function generate_UUID(){
			$override = apply_filters('pre_generate_UUID', null);
			if ( isset($override) ){return $override;}

			return sprintf(
				'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
				random_int(0, 0xffff), random_int(0, 0xffff), //32 bits for "time_low"
				random_int(0, 0xffff), //16 bits for "time_mid"
				random_int(0, 0x0fff) | 0x4000, //16 bits for "time_hi_and_version", Four most significant bits holds version number 4
				random_int(0, 0x3fff) | 0x8000, //16 bits, 8 bits for "clk_seq_hi_res", 8 bits for "clk_seq_low", Two most significant bits holds zero and one for variant DCE1.1
				random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff) //48 bits for "node"
			);
		}

		//Nebula usage data
		public function usage($name='usage_data', $event_parameters=array()){
			$date = new DateTime('now', new DateTimeZone('America/New_York'));

			$php_version_parts = explode('.', PHP_VERSION);
			$major_php_version = $php_version_parts[0] . '.' . $php_version_parts[1]; //Limit version numbers to only major.minor

			$data = array(
				'client_id' => $this->ga_parse_cookie(), //Can just use the one generated for this user
				'non_personalized_ads' => true, //We do not need this data for Nebula usage
				'events' => array(array(
					'name' => $name,
					'params' => array(
						'user_agent' => rawurlencode($this->super->server['HTTP_USER_AGENT']),
						'hostname' => ( function_exists('gethostname') )? gethostname() : '',
						'page_location' => $this->requested_url(),
						'website_name' => get_bloginfo('name'),
						'home_url' => home_url('/'),
						'unix_timestamp' => time(),
						'eastern_timestamp' => $date->format('F j, Y, g:ia'),
						'wordpress_version' => get_bloginfo('version'),
						'nebula_version' => $this->version('raw'),
						'site_description' => get_bloginfo('description'),
						'site_url' => site_url(),
						'client_id' => $this->ga_parse_cookie(),
						'theme_type' => ( is_child_theme() )? 'Child' : 'Parent',
						'wp_user_id' => get_current_user_id(),
						'php_version' => $major_php_version,
						'mysql_version' => mysqli_get_client_version(),
						'transport_method' => 'Server-Side',
					)
				))
			);

			if ( $name === 'theme_activation' ){
				$data['events'][0]['params']['activation_type'] = ( $this->get_data('first_activation') && time()-$this->get_data('first_activation') > 60 )? 'Reactivated - First at ' . $this->get_data('first_activation') . ' (' . human_time_diff($this->get_data('first_activation')) . ' ago)' : 'Initial Activation';
			}

			$data['events'][0]['params'] = array_merge($data['events'][0]['params'], $event_parameters); //Add passed parameters

			$response = wp_remote_post('https://www.google-analytics.com/mp/collect?measurement_id=G-79YGGYLVJK&api_secret=rRFT9IynSg-DEo5t7j1mqw', array(
				'body' => wp_json_encode($data),
				'method' => 'POST',
			));
		}

		//Log fatal errors in Google Analytics as crashes
		public function ga_log_fatal_php_errors(){
			$error = error_get_last();
			if ( isset($error) && $error['type'] === E_ERROR ){
				$message = str_replace(WP_CONTENT_DIR, '', strstr($error['message'], ' in /', true)); //Remove high-level directories to reduce clutter and prevent PII
				$file = str_replace(WP_CONTENT_DIR, '', strstr($error['file'], 'wp-content')); //Remove high-level directories to reduce clutter and prevent PII
				$this->ga_send_exception('(PHP) ' . $message . ' on line ' . $error['line'] . ' in .../' . $file, true); //Send it to this site's analytics

				if ( preg_match('/themes\/Nebula-?(main|master|parent|\d+\.\d+)?\//i', $file) && !strpos(strtolower($file), 'scssphp') ){ //If the error is in Nebula parent (and not a Sass compile error) log it for continued improvement of Nebula itself //Remove "master" after a period of time (Maybe January 2021) //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
					$this->usage('exception', array(
						'event_category' => 'PHP Fatal Error',
						'message' => '(PHP) ' . $message . ' on line ' . $error['line'] . ' in ' . $file,
						'fatal' => true,
						'permalink' => (( isset($this->super->server['HTTPS']) )? 'https' : 'http') . '://' . $this->super->server['HTTP_HOST'] . $this->super->server['REQUEST_URI']
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

			$event_parameters = array(
				'message' => $message,
				'fatal' => $fatal,
				'page_title' => 'Page Not Found'
			);

			$data = array_merge($event_parameters, $array); //Add passed parameters
			$data = $this->ga_build_event('exception', $data);
			$this->ga_send_data($data);
		}

		//Send Data to Google Analytics
		//https://developers.google.com/analytics/devguides/collection/protocol/v1/devguide#event
		public function ga_send_data($data){
			$override = apply_filters('pre_ga_send_data', null, $data);
			if ( isset($override) ){return;}

			//The GA Measurement Protocol requires a Measurement ID and an API Secret key
			if ( $this->get_option('ga_api_secret') && $this->get_option('ga_measurement_id') ){
				$response = wp_remote_post('https://www.google-analytics.com/mp/collect?measurement_id=' . $this->get_option('ga_measurement_id') . '&api_secret=' . $this->get_option('ga_api_secret'), array(
					'body' => wp_json_encode($data),
					'method' => 'POST',
				));
				return $response;
			}

			return false;
		}

		//Add measurement protocol parameters for custom definitions
		public function ga_build_event($event_name='', $event_parameters=array(), $user_properties=array()){
			if ( empty($event_name) ){
				return false;
			}

			$default_common_parameters = array(
				'client_id' => $this->ga_parse_cookie(),
				'non_personalized_ads' => false,
				'user_properties' => array(
					'client_id' => array('value' => $this->ga_parse_cookie())
				),
				'events' => array(array(
					'name' => 'nmp_' . $event_name, //mp_ indicates Nebula Measurement Protocol
					'params' => array(
						'user_agent' => ( !empty($this->super->server['HTTP_USER_AGENT']) )? rawurlencode($this->super->server['HTTP_USER_AGENT']): '',
						'language' => ( class_exists('Locale') && !empty($this->super->server['HTTP_ACCEPT_LANGUAGE']) )? locale_accept_from_http($this->super->server['HTTP_ACCEPT_LANGUAGE']) : '',
						'page_referrer' => ( isset($this->super->server['HTTP_REFERER']) )? $this->super->server['HTTP_REFERER'] : '',
						'page_location' => $this->requested_url(), //Likely "admin-ajax.php" until overwritten
						'page_title' => ( get_the_title() )? get_the_title() : '', //Likely empty until overwritten
						'nebula_session_id' => $this->nebula_session_id(),
						'transport_method' => 'Server-Side'
					)
				))
			);

			//User ID
			if ( is_user_logged_in() ){
				$default_common_parameters['user_properties']['wp_id'] = array('value' => get_current_user_id()); //User ID
			}

			//Transport Method
			$default_common_parameters['events'][0]['params']['transport_method'] = 'Server-Side';

			//Merge with provided data
			$default_common_parameters['user_properties'] = array_merge($default_common_parameters['user_properties'], $user_properties); //Add passed user properties
			$default_common_parameters['events'][0]['params'] = array_merge($default_common_parameters['events'][0]['params'], $event_parameters); //Add passed event parameters

			$common_parameters = apply_filters('nebula_measurement_protocol_custom_definitions', $default_common_parameters);

			return $common_parameters;
		}

		//Add Google Analytics UTM parameters to RSS (and other feed) links
		//Manually control UTM parameters by adding them to the feed URL itself: https://example.com/feed?utm_campaign=summer+sale&utm_source=newsletter&utm_medium=email
		public function add_utm_to_feeds($link){
			$utm_query = array();

			//Set the utm_campaign parameter
			if ( isset($this->super->get['utm_campaign']) ){
				$utm_query[] = 'utm_campaign=' . $this->super->get['utm_campaign'];
			}

			//Set the utm_source parameter
			$utm_source = $this->url_components('hostname', site_url()); //Default to the hostname of the site
			if ( isset($this->super->get['utm_source']) ){
				$utm_source = esc_attr($this->super->get['utm_source']);
			}
			$utm_query[] = 'utm_source=' . $utm_source;

			//Set the utm_medium parameter
			$utm_medium = 'feed';
			if ( isset($this->super->get['utm_medium']) ){
				$utm_medium = esc_attr($this->super->get['utm_medium']);
			}
			$utm_query[] = 'utm_medium=' . $utm_medium;

			//Set the utm_content parameter
			if ( isset($this->super->get['utm_content']) ){
				$utm_query[] = 'utm_content=' . $this->super->get['utm_content'];
			}

			$url = explode('?', $link);

			//Merge query parameters
			$utm_query_str = implode('&amp;', $utm_query);
			$url_query = $utm_query_str;
			if ( count($url) > 1 ){
				$url_query = $url[1] . '&amp;' . $utm_query_str;
			}

			$tracking_link = $url[0] . '?' . $url_query;
			return $tracking_link;
		}

		//Add tracking to self-referencing links in the excerpt and content
		public function add_utm_to_feeds_content_links($content){
			$link = get_permalink(get_the_ID());
			return str_replace($link, $this->add_utm_to_feeds($link), $content);
		}
	}
}