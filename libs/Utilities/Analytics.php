<?php
	/*
		GA Parameter Guide: https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters?hl=en
		GA Hit Builder: https://ga-dev-tools.appspot.com/hit-builder/
	*/

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Analytics') ){
	trait Analytics {
		public function hooks(){
			if ( !$this->is_background_request() && !$this->is_cli() && !is_customize_preview() && !$this->is_non_page_request() ){
				add_action('template_redirect', array($this, 'attribution_tracking'));

				add_filter('the_permalink_rss', array($this, 'add_utm_to_feeds'), 100);
				add_filter('the_excerpt_rss', array($this, 'add_utm_to_feeds_content_links'), 200);
				add_filter('the_content_feed', array($this, 'add_utm_to_feeds_content_links'), 200);
				add_action('template_redirect', array($this, 'count_post_type_views'), 90);
				add_action('template_redirect', array($this, 'count_404_views'));
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
			if ( $this->is_minimal_mode() ){return null;}

			if ( $this->option('observe_dnt') && $this->is_do_not_track() ){
				$this->once('is_analytics_allowed', function(){
					do_action('qm/info', 'Observing "Do Not Track" requests');
				});
				return false;
			}

			if ( $this->is_non_page_request() ){ //Should I include $this->is_background_request() here too?
				return false;
			}

			if ( isset($this->super->get['noga']) || is_customize_preview() ){ //Disable analytics for ?noga query string
				return false;
			}

			if ( isset($this->super->server['SERVER_ADDR']) && $this->get_ip_address() === wp_privacy_anonymize_ip($this->super->server['SERVER_ADDR']) ){ //Disable analytics for self-requests by the server
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

		//If the "Do Not Sell"/"Do Not Share" browser setting is enabled.
		//https://globalprivacycontrol.org/
		public function is_gpc_enabled(){
			if ( isset($this->super->server['HTTP_SEC_GPC']) && $this->super->server['HTTP_SEC_GPC'] == 1 ){
				return true;
			}

			return false;
		}

		//Handle the parsing of the _ga cookie or setting it to a unique identifier
		public function ga_parse_cookie(){
			$override = apply_filters('pre_ga_parse_cookie', null);
			if ( isset($override) ){return $override;}

			$cid = $this->default_cid; //Start with a local (non-Google) unique ID (via Assets.php)
			if ( isset($this->super->cookie['_ga']) ){
				list($version, $domainDepth, $cid1, $cid2) = explode('.', $this->super->cookie["_ga"], 4);
				$contents = array('version' => $version, 'domainDepth' => $domainDepth, 'cid' => $cid1 . '.' . $cid2);
				$cid = $contents['cid'];
			}

			$parsed_cookie_value = sanitize_text_field(esc_html($cid));

			$this->once('is_analytics_allowed', function($parsed_cookie_value){
				do_action('qm/info', 'Parsed GA cookie: ' . $parsed_cookie_value);
			}, $parsed_cookie_value);

			return $parsed_cookie_value;
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

		//Alias for getting the platform explanation from a query string parameter
		public function lookup_query_parameter_definition($url=''){
			if ( !empty($url) ){
				return $this->attribution_tracking($url);
			}
		}

		//Track campaigns that attributed to returning visitor conversions
		//Pass a string of the query parameter to $get_definition_only to return the platform definition only (without processing the entire attribution)
		public function attribution_tracking($get_definitions_only=false){
			if ( !$this->get_option('attribution_tracking') ){
				return;
			}

			if ( $this->is_do_not_track() ){
				return;
			}

			//Allow others to modify/add to the list of notable tracking parameters
			$notable_tracking_parameters = apply_filters('nebula_notable_tracking_parameters', array(
				'utm_source' => 'various platforms',
				'utm_campaign' => 'various platforms',
				'utm_medium' => 'various platforms',
				'utm_content' => 'various platforms',
				'utm_term' => 'various platforms',
				'gclid' => 'Google Ads (Click ID)', //Google Ads Click ID
				'gclsrc' => 'Google Ads (Click Source)', //Google Ads Click Source
				'gbraid' => 'Google Ads', //Google Ads
				'wbraid' => 'Google Ads', //Google Ads
				'gad_source' => 'Google Ads', //Google Ads
				'gad_campaignid' => 'Google Ads', //Google Ads
				'gad_adgroupid' => 'Google Ads', //Google Ads
				'gad_creativeid' => 'Google Ads', //Google Ads
				'gad_network' => 'Google Ads', //Google Ads
				'gad_matchtype' => 'Google Ads', //Google Ads
				'gad_keyword' => 'Google Ads', //Google Ads
				'gad_placement' => 'Google Ads Display Network', //Google Ads Display Network
				'dclid' => 'DoubleClick (Click ID, typically offline tracking)', //DoubleClick Click ID (typically offline tracking)
				'msclkid' => 'Microsoft Click ID', //Microsoft Click ID
				'fbc' => 'Facebook (Click ID)', //Facebook Click ID
				'fbclid' => 'Facebook (Click ID)', //Facebook Click ID
				'li_' => 'LinkedIn', //LinkedIn
				'tclid' => 'Twitter (Click ID)', //Twitter Click ID
				'ttclid' => 'TikTok (Click ID)', //TikTok Click ID
				'hsa_' => 'Hubspot', //Hubspot
				'mc_eid' => 'Mailchimp', //Mailchimp
				'vero_id' => 'Vero', //Vero
				'mkt_tok' => 'Marketo', //Marketo
				'email_id' => 'email marketing', //Email
				'campaign_id' => 'email marketing', //Email
				'subscriber_id' => 'email marketing', //Email
				'mail_id' => 'email marketing', //Email
				'keap' => 'Keap email marketing', //Keap Email
				'srsltid' => 'Google Merchant Center', //Google Merchant Center
				'affiliate_id' => 'Affiliate Marketing', //Affiliates
				'coupon' => 'affiliate marketing', //Affiliates
				'promo' => 'affiliate marketing', //Affiliates
				'partner_id' => 'affiliate marketing', //Affiliates
				'partner' => 'affiliate marketing', //Affiliates
				'eloqua' => 'Eloqua marketing automation', //Eloqua
				'pardot' => 'Pardot CRM', //Pardot
				'sfdc_id' => 'Salesforce CRM', //Salesforce
			));

			//If we are only getting definitions, check it now
			if ( !empty($get_definitions_only) ){
				$string_to_check = $get_definitions_only; //Update the variable name to be more clear
				$explanations = array();

				foreach ( $notable_tracking_parameters as $param => $definition ){
					if ( str_contains($string_to_check, $param) ){
						$explanations[] = $param . ' is used by ' . $definition;
					}
				}

				return $explanations;
			}

			//If we are processing the attribution, prep memoization
			static $cache = null;
			if ( isset($cache) ){
				return $cache;
			}

			$found_parameters = array();

			foreach ( $this->super->get as $key => $value ){ //Loop through the URL query parameters
				foreach ( $notable_tracking_parameters as $tracking_parameter => $definition ){ //Check against our list of notable tracking parameters
					if ( str_contains($key, $tracking_parameter) ){
						$found_parameters[$key] = sanitize_text_field($value);
						break;
					}
				}
			}

			//If we found a tracking parameter
			if ( !empty($found_parameters) ){
				$found_parameters['path'] = strtok($_SERVER['REQUEST_URI'], '?'); //Include the page path to the entry (and ensure query parameters are excluded)
				$found_parameters['date'] = date('Y-m-d\TH:i:s'); //Associate the date/time with the attribution

				//If we already have the attribution cookie, update it
				if ( isset($this->super->cookie['attribution']) ){
					$attribution_data = json_decode(wp_unslash($this->super->cookie['attribution']), true);

					if ( !is_array($attribution_data) ){
						$attribution_data = array();
					}

					$attribution_data['last'] = $found_parameters; //Always overwrite the last touch attribution

					//If we are missing the first-touch attribution, use this one now
					if ( empty($attribution_data['first']) ){
						$attribution_data['first'] = $found_parameters;
					}

					//Now prepare to store multi-touch attributions
					if ( empty($attribution_data['multi']) || !is_array($attribution_data['multi']) ){
						$attribution_data['multi'] = array();
					}

					if ( !empty($attribution_data['multi']) ){ //If we have a "multi" entry already, compare and update it
						$last_entry = end($attribution_data['multi']);

						//Ignore "path" and "date" when comparing for uniqueness
						$temp_last = $last_entry;
						$temp_found = $found_parameters;
						unset($temp_last['path'], $temp_last['date']);
						unset($temp_found['path'], $temp_found['date']);
						ksort($temp_last);
						ksort($temp_found);

						if ( json_encode($temp_last) !== json_encode($temp_found) ){ //If the current entry is different from the previous entry
							$attribution_data['multi'][] = $found_parameters; //Push this tracking data to the multi-touch array

							//Check if cookie size exceeds ~3800 bytes (leave headroom for name, headers, etc.)
							$encoded_attribution_data = wp_json_encode($attribution_data);
							while ( strlen($encoded_attribution_data) > 3800 && count($attribution_data['multi']) > 1 ){ //If it exceeds 3.8kb and has more than 1 multi entry
								array_shift($attribution_data['multi']); //Remove oldest entry
								$encoded_attribution_data = wp_json_encode($attribution_data); //Re-encode after trimming to test again
							}
						}
					} else { //Otherwise, it is the first entry into multi
						$attribution_data['multi'][] = $found_parameters;
					}
				} else { //Otherwise, create the cookie from scratch
					$attribution_data = array(
						'first' => $found_parameters //We add "last" and "multi" only when the second attribution visit is detected
					);
				}

				$this->set_cookie('attribution', wp_json_encode($attribution_data), time()+YEAR_IN_SECONDS, false); //Needs to be able to be read by JavaScript

				$cache = true;
				return $cache;
			}

			$cache = false;
			return $cache;
		}

		//Nebula usage data
		public function usage($name='usage_data', $event_parameters=array()){
			if ( $this->is_minimal_mode() ){return null;}

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
						'mysql_version' => function_exists('mysqli_get_client_version') ? mysqli_get_client_version() : 'Unknown',
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
			if ( $this->is_minimal_mode() ){return null;}

			$error = error_get_last();
			if ( isset($error) && $error['type'] === E_ERROR ){
				$message = str_replace(WP_CONTENT_DIR, '', strstr($error['message'], ' in /', true)); //Remove high-level directories to reduce clutter and prevent PII
				$file = str_replace(WP_CONTENT_DIR, '', strstr($error['file'], 'wp-content')); //Remove high-level directories to reduce clutter and prevent PII
				$this->ga_send_exception('(PHP) ' . $message . ' on line ' . $error['line'] . ' in .../' . $file, true); //Send it to this site's analytics

				if ( preg_match('/themes\/Nebula-?(main|parent|\d+\.\d+)?\//i', $file) && !str_contains(strtolower($file), 'scssphp') ){ //If the error is in Nebula parent (and not a Sass compile error) log it for continued improvement of Nebula itself
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
			if ( $this->is_minimal_mode() ){return null;}
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
			if ( $this->is_minimal_mode() ){return null;}
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

			return null;
		}

		//Add measurement protocol parameters for custom definitions
		public function ga_build_event($event_name='', $event_parameters=array(), $user_properties=array()){
			if ( $this->is_minimal_mode() ){return null;}
			if ( empty($event_name) ){
				return null;
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
			if ( $this->is_minimal_mode() ){return $link;}

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

		//Track server-side post type views over the last week
		public function count_post_type_views(){
			if ( $this->is_minimal_mode() ){return null;}

			if ( !is_singular() ){
				return null;
			}

			if ( $this->is_bot() || !$this->is_analytics_allowed() || $this->is_admin_page() ){
				return null;
			}

			$post_type = get_post_type();
			if ( !$post_type ){
				return null;
			}

			$today = date('Y-m-d');
			$stats = get_transient('nebula_analytics_post_type_views');

			if ( !is_array($stats) ){
				$stats = array(); //Create stats array if it doesn't exist
			}

			if ( !isset($stats[$today]) ){
				$stats[$today] = array(); //Create today's array if it doesn't exist
			}

			if ( !isset($stats[$today][$post_type]) ){
				$stats[$today][$post_type] = 0;
			}

			$stats[$today][$post_type]++;

			//Prune entries older than 7 days
			$cutoff = strtotime('-7 days');
			foreach ( $stats as $date => $day_stats ){
				if ( strtotime($date) < $cutoff ){
					unset($stats[$date]);
				}
			}

			set_transient('nebula_analytics_post_type_views', $stats, DAY_IN_SECONDS*8); //Buffer to avoid edge expiry
		}

		//Get the weekly post type counts
		public function get_post_type_view_totals(){
			if ( $this->is_minimal_mode() ){return array();}

			$raw_stats = get_transient('nebula_analytics_post_type_views');
			$totals = array();
			$days = array();

			if ( !is_array($raw_stats) ){
				return array('totals' => $totals, 'days' => 0);
			}

			foreach ( $raw_stats as $date => $day_stats ){
				$days[] = $date;
				foreach ( $day_stats as $post_type => $count ){
					if ( !isset($totals[$post_type]) ){
						$totals[$post_type] = 0;
					}
					$totals[$post_type] += $count;
				}
			}

			return array(
				'totals' => $totals,
				'days' => count($days),
			);
		}

		//Count the number of times human visitors reached a 404 page
		public function count_404_views(){
			if ( $this->is_minimal_mode() ){return null;}

			if ( !is_404() ){
				return null;
			}

			//Ignore staff/developer traffic
			if ( $this->is_staff() ){
				return null;
			}

			//This 404 tracker only cares about human-traffic. Use the "Redirection" plugin (or other tool) to track all 404s.
			if ( $this->is_bot() || !$this->is_analytics_allowed() || $this->is_admin_page() ){
				return null;
			}

			$now = time();
			$path = esc_url_raw($_SERVER['REQUEST_URI']);
			$stats = get_transient('nebula_analytics_404_views');

			if ( !is_array($stats) ){
				$stats = array(); //Format: array(timestamp => array(paths))
			}

			//Record this exact time and path
			if ( !isset($stats[$now]) ){
				$stats[$now] = array();
			}

			$stats[$now][] = $path;

			//Prune anything older than 24 hours
			$cutoff = $now-DAY_IN_SECONDS;
			foreach ( $stats as $timestamp => $paths ){
				if ( $timestamp < $cutoff ){
					unset($stats[$timestamp]);
				}
			}

			set_transient('nebula_analytics_404_views', $stats, DAY_IN_SECONDS+300); //Small buffer to avoid edge expiry
		}

		//Get the number of 404s from the last 24-hours
		//Remember: this attempts to only track "human traffic" 404s
		public function get_404_count(){
			$stats = get_transient('nebula_analytics_404_views');

			if ( !$this->is_transients_enabled() ){
				return null;
			}

			if ( !is_array($stats) ){ //If it is empty
				return 0;
			}

			$cutoff = time()-DAY_IN_SECONDS;
			$total = 0;

			foreach ( $stats as $timestamp => $paths ){
				if ( $timestamp >= $cutoff && is_array($paths) ){
					$total += count($paths);
				}
			}

			return $total;
		}
	}
}