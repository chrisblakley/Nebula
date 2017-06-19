<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Analytics') ){
	trait Analytics {
		public function hooks(){
			//Load abandonment tracking
			add_action('nebula_head_open', array($this, 'ga_track_load_abandons')); //This is the earliest anything can be placed in the <head>

			//Sends events to Google Analytics via AJAX (used if GA is blocked via JavaScript)
			add_action('wp_ajax_nebula_ga_ajax', array($this, 'ga_ajax'));
			add_action('wp_ajax_nopriv_nebula_ga_ajax', array($this, 'ga_ajax'));
		}

		//Handle the parsing of the _ga cookie or setting it to a unique identifier
		public function ga_parse_cookie(){
			$override = do_action('pre_ga_parse_cookie');
			if ( !empty($override) ){return $override;}

			if ( isset($_COOKIE['_ga']) ){
				list($version, $domainDepth, $cid1, $cid2) = explode('.', $_COOKIE["_ga"], 4);
				$contents = array('version' => $version, 'domainDepth' => $domainDepth, 'cid' => $cid1 . '.' . $cid2);
				$cid = $contents['cid'];
			} else {
				$cid = $this->ga_generate_UUID();
			}
			return $cid;
		}

		//Generate UUID v4 function (needed to generate a CID when one isn't available)
		public function ga_generate_UUID(){
			$override = do_action('pre_ga_generate_UUID');
			if ( !empty($override) ){return $override;}

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
		public function ga_generate_domain_hash($domain){
			$override = do_action('pre_ga_generate_domain_hash', $domain);
			if ( !empty($override) ){return $override;}

			if ( empty($domain) ){
				$domain = nebula()->url_components('domain');
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
		public function ga_UTM_gif($user_cookies=array(), $user_parameters=array()){
			$override = do_action('pre_ga_UTM_gif', $user_cookies, $user_parameters);
			if ( !empty($override) ){return $override;}

			//@TODO "Nebula" 0: Make an AJAX function in Nebula (plugin) to accept a form for each parameter then renders the __utm.gif pixel.

			$domain = nebula()->url_components('domain');
			$cookies = array(
				'utma' => $this->generate_domain_hash($domain) . '.' . mt_rand(1000000000, 9999999999) . '.' . time() . '.' . time() . '.' . time() . '.1', //Domain Hash . Random ID . Time of First Visit . Time of Last Visit . Time of Current Visit . Session Counter ***Absolutely Required***
				'utmz' => $this->generate_domain_hash($domain) . '.' . time() . '.1.1.', //Campaign Data (Domain Hash . Time . Counter . Counter)
				'utmcsr' => '-', //Campaign Source "google"
				'utmccn' => '-', //Campaign Name "(organic)"
				'utmcmd' => '-', //Campaign Medium "organic"
				'utmctr' => '-', //Campaign Terms (for paid search)
				'utmcct' => '-', //Campaign Content Description
			);
			$cookies = array_merge($cookies, $user_cookies);

			$data = array(
				'utmwv' => '5.3.8', //Tracking code version *** REQUIRED ***
				'utmac' => nebula()->get_option('ga_tracking_id'), //Account string, appears on all requests *** REQUIRED ***
				'utmdt' => get_the_title(), //Page title, which is a URL-encoded string *** REQUIRED ***
				'utmp' => nebula()->url_components('filepath'), //Page request of the current page (current path) *** REQUIRED ***
				'utmcc' => '__utma=' . $cookies['utma'] . ';+', //Cookie values. This request parameter sends all the cookies requested from the page. *** REQUIRED ***

				'utmhn' => nebula()->url_components('hostname'), //Host name, which is a URL-encoded string
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

		//Send Pageview Function for Server-Side Google Analytics
		public function ga_send_pageview($location=null, $title=null, $array=array()){
			$override = do_action('pre_ga_send_pageview', $location, $title, $array);
			if ( !empty($override) ){return $override;}

			if ( empty($location) ){
				$location = nebula()->requested_url();
			}

			if ( empty($title) ){
				$title = ( get_the_title() )? get_the_title() : '';
			}

			//GA Parameter Guide: https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters?hl=en
			//GA Hit Builder: https://ga-dev-tools.appspot.com/hit-builder/
			$data = array(
				'v' => 1,
				'tid' => nebula()->get_option('ga_tracking_id'),
				'cid' => $this->ga_parse_cookie(),
				't' => 'pageview',
				'dl' => $location,
				'dt' => $title,
				'dr' => ( isset($_SERVER['HTTP_REFERER']) )? $_SERVER['HTTP_REFERER'] : '',
				'ua' => rawurlencode($_SERVER['HTTP_USER_AGENT']) //User Agent
			);

			$data = array_merge($data, $array);
			$this->ga_send_data($data);
		}

		//Send Event Function for Server-Side Google Analytics
		public function ga_send_event($category=null, $action=null, $label=null, $value=0, $ni=1, $array=array()){
			$override = do_action('pre_ga_send_event', $category, $action, $label, $value, $ni, $array);
			if ( !empty($override) ){return $override;}

			if ( empty($value) ){
				$value = 0;
			}

			//GA Parameter Guide: https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters?hl=en
			//GA Hit Builder: https://ga-dev-tools.appspot.com/hit-builder/
			$data = array(
				'v' => 1,
				'tid' => nebula()->get_option('ga_tracking_id'),
				'cid' => $this->ga_parse_cookie(),
				't' => 'event',
				'ec' => $category, //Category (Required)
				'ea' => $action, //Action (Required)
				'el' => $label, //Label
				'ev' => $value, //Value
				'ni' => $ni, //Non-Interaction
				'dl' => nebula()->requested_url(),
				'dt' => ( get_the_title() )? get_the_title() : '',
				'ua' => rawurlencode($_SERVER['HTTP_USER_AGENT']) //User Agent
			);

			$data = array_merge($data, $array);
			$this->ga_send_data($data);
		}

		//Send custom data to Google Analytics. Must pass an array of data to this function:
		//ga_send_custom(array('t' => 'event', 'ec' => 'Category Here', 'ea' => 'Action Here', 'el' => 'Label Here'));
		//https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters
		public function ga_send_custom($array=array()){ //@TODO "Nebula" 0: Add additional parameters to this function too (like above)!
			$override = do_action('pre_ga_send_custom', $array);
			if ( !empty($override) ){return $override;}

			//GA Parameter Guide: https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters?hl=en
			//GA Hit Builder: https://ga-dev-tools.appspot.com/hit-builder/
			$defaults = array(
				'v' => 1,
				'tid' => nebula()->get_option('ga_tracking_id'),
				'cid' => $this->ga_parse_cookie(),
				't' => '',
				'ni' => 1,
				'dl' => nebula()->requested_url(),
				'dt' => ( get_the_title() )? get_the_title() : '',
				'ua' => rawurlencode($_SERVER['HTTP_USER_AGENT']) //User Agent
			);

			$data = array_merge($defaults, $array);

			if ( !empty($data['t']) ){
				$this->ga_send_data($data);
			} else {
				trigger_error("ga_send_custom() requires an array of values. A Hit Type ('t') is required! See documentation here for accepted parameters: https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters", E_USER_ERROR);
				return;
			}
		}

		//Send Pageview Function for Server-Side Google Analytics
		public function ga_send_exception($message=null, $fatal=1, $array=array()){
			$override = do_action('pre_ga_send_exception', $message, $fatal, $array);
			if ( !empty($override) ){return $override;}

			//GA Parameter Guide: https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters?hl=en
			//GA Hit Builder: https://ga-dev-tools.appspot.com/hit-builder/
			$data = array(
				'v' => 1,
				'tid' => nebula()->get_option('ga_tracking_id'),
				'cid' => $this->ga_parse_cookie(),
				't' => 'exception',
				'exd' => $message,
				'exf' => $title,
				'dl' => nebula()->requested_url(),
				'dt' => ( get_the_title() )? get_the_title() : '',
				'dr' => ( isset($_SERVER['HTTP_REFERER']) )? $_SERVER['HTTP_REFERER'] : '',
				'ua' => rawurlencode($_SERVER['HTTP_USER_AGENT']) //User Agent
			);

			$data = array_merge($data, $array);
			$this->ga_send_data($data);
		}

		public function ga_ajax(){
			if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }
			if ( !nebula()->is_bot() ){
				echo 'inside ga ajax';

				//Location and Title
				$additional_fields = array(
					'dl' => sanitize_text_field($_POST['fields']['location']),
					'dt' => sanitize_text_field($_POST['fields']['title']),
				);

				//User Agent
				if ( !empty($_POST['fields']['ua']) ){
					$additional_fields['ua'] = $_POST['fields']['ua'];
				}

				//Custom Dimension
				if ( nebula()->get_option('cd_blocker') ){
					$additional_fields['cd' . str_replace('dimension', '', nebula()->get_option('cd_blocker'))] = 'Google Analytics Blocker';
				}

				//Pageview
				if ( $_POST['fields']['hitType'] === 'pageview' ){
					$this->ga_send_pageview(null, null, $additional_fields);
				}

				//Event
				if ( $_POST['fields']['hitType'] === 'event' ){
					$this->ga_send_event(
						sanitize_text_field($_POST['fields']['category']),
						sanitize_text_field($_POST['fields']['action']),
						sanitize_text_field($_POST['fields']['label']),
						sanitize_text_field($_POST['fields']['value']),
						sanitize_text_field($_POST['fields']['ni']),
						$additional_fields
					);
				}
			}

			wp_die();
		}

		//Send Data to Google Analytics
		//https://developers.google.com/analytics/devguides/collection/protocol/v1/devguide#event
		public function ga_send_data($data){
			$override = do_action('pre_ga_send_data', $data);
			if ( !empty($override) ){return $override;}

			//https://ga-dev-tools.appspot.com/hit-builder/

			//echo '<p>' . http_build_query($data) . '</p>';

			$response = wp_remote_get('https://www.google-analytics.com/collect?payload_data&' . http_build_query($data));
			return $response;
		}

		//Load abandonment tracking
		public function ga_track_load_abandons(){
			if ( $this->is_bot() ){
				return false;
			}

			$custom_metric_hitID = ( nebula()->get_option('cd_hitid') )? "'cd" . str_replace('dimension', '', nebula()->get_option('cd_hitid')) . "=" . nebula()->ga_generate_UUID() . "'," : ''; //Create the Measurement Protocol parameter for cd

			?>
			<script>
				document.addEventListener('visibilitychange', loadAbandonTracking);
				window.onbeforeunload = loadAbandonTracking;

				function loadAbandonTracking(e){
					if ( e.type == 'visibilitychange' && document.visibilityState == 'visible' ){
						return false;
					}

					//Remove listeners so this can only trigger once
					document.removeEventListener('visibilitychange', loadAbandonTracking);
					window.onbeforeunload = null;

					var loadAbandonLevel = 'Hard (Unload)';
					if ( e.type == 'visibilitychange' ){
						loadAbandonLevel = 'Soft (Visibility Change)';
					}

					//Grab the Google Analytics CID from the cookie (if it exists)
					var gaCID = document.cookie.replace(/(?:(?:^|.*;)\s*_ga\s*\=\s*(?:\w+\.\d\.)([^;]*).*$)|^.*$/, '$1');
					var newReturning = 'Returning visitor or multiple pageview session';
					if ( !gaCID ){
						gaCID = (Math.random()*Math.pow(2, 52));
						newReturning = 'New user or blocking Google Analytics cookie';
					}

					navigator.sendBeacon && navigator.sendBeacon('https://www.google-analytics.com/collect', [
						'tid=<?php echo nebula()->get_option('ga_tracking_id'); ?>', //Tracking ID
						'cid=' + gaCID, //Client ID
						'v=1', //Protocol Version
						't=event', //Hit Type
						'ec=Load Abandon', //Event Category
						'ea=' + loadAbandonLevel, //Event Action
						'el=' + newReturning, //Event Label
						'ev=' + Math.round(performance.now()), //Event Value
						'ni=1', //Non-Interaction Hit
						'dr=<?php echo ( isset($_SERVER['HTTP_REFERER']) )? $_SERVER['HTTP_REFERER'] : ''; ?>', //Document Referrer
						'dl=' + window.location.href, //Document Location URL
						'dt=' + document.title, //Document Title
						<?php echo $custom_metric_hitID; //Unique Hit ID ?>
					].join('&'));

					//User Timing
					navigator.sendBeacon && navigator.sendBeacon('https://www.google-analytics.com/collect', [
						'tid=<?php echo nebula()->get_option('ga_tracking_id'); ?>', //Tracking ID
						'cid=' + gaCID, //Client ID
						'v=1', //Protocol Version
						't=timing', //Hit Type
						'utc=Load Abandon', //Timing Category
						'utv=' + loadAbandonLevel, //Timing Variable Name
						'utt=' + Math.round(performance.now()), //Timing Time (milliseconds)
						'utl=' + newReturning, //Timing Label
						'dl=' + window.location.href, //Document Location URL
						'dt=' + document.title, //Document Title
						<?php echo $custom_metric_hitID; //Unique Hit ID ?>
					].join('&'));
				}

				//Remove abandonment listeners on window load
				window.addEventListener('load', function(){
					document.removeEventListener('visibilitychange', loadAbandonTracking);
					if ( window.onbeforeunload === loadAbandonTracking ){
						window.onbeforeunload = null;
					}
				});
			</script>
			<?php
		}
	}
}