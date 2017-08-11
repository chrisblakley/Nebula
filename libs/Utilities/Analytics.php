<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Analytics') ){
	trait Analytics {
		public function hooks(){
			add_action('nebula_head_open', array($this, 'ga_track_load_abandons')); //This is the earliest anything can be placed in the <head>
			add_action('wp_footer', array($this, 'visualize_scroll_percent'));
			add_action('wp_ajax_nebula_ga_ajax', array($this, 'ga_ajax'));
			add_action('wp_ajax_nopriv_nebula_ga_ajax', array($this, 'ga_ajax'));
		}

		//Handle the parsing of the _ga cookie or setting it to a unique identifier
		public function ga_parse_cookie(){
			$override = apply_filters('pre_ga_parse_cookie', null);
			if ( isset($override) ){return;}

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
			$override = apply_filters('pre_ga_generate_UUID', null);
			if ( isset($override) ){return;}

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
			$override = apply_filters('pre_ga_generate_domain_hash', null, $domain);
			if ( isset($override) ){return;}

			if ( empty($domain) ){
				$domain = $this->url_components('domain');
			}

			$a = 0;
			for ( $i = strlen($domain)-1; $i >= 0; $i-- ){
				$ascii = ord($domain[$i]);
				$a = (($a<<6)&268435455)+$ascii+($ascii<<14);
				$c = $a&266338304;
				$a = ( $c !== 0 )? $a^($c>>21) : $a;
			}
			return $a;
		}

		//Generate the full path of a Google Analytics __utm.gif with necessary parameters.
		//https://developers.google.com/analytics/resources/articles/gaTrackingTroubleshooting?csw=1#gifParameters
		public function ga_UTM_gif($user_cookies=array(), $user_parameters=array()){
			$override = apply_filters('pre_ga_UTM_gif', null, $user_cookies, $user_parameters);
			if ( isset($override) ){return;}

			//@TODO "Nebula" 0: Make an AJAX function in Nebula (plugin) to accept a form for each parameter then renders the __utm.gif pixel.

			$domain = $this->url_components('domain');
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
				'utmac' => $this->get_option('ga_tracking_id'), //Account string, appears on all requests *** REQUIRED ***
				'utmdt' => get_the_title(), //Page title, which is a URL-encoded string *** REQUIRED ***
				'utmp' => $this->url_components('filepath'), //Page request of the current page (current path) *** REQUIRED ***
				'utmcc' => '__utma=' . $cookies['utma'] . ';+', //Cookie values. This request parameter sends all the cookies requested from the page. *** REQUIRED ***

				'utmhn' => $this->url_components('hostname'), //Host name, which is a URL-encoded string
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

			return 'https://www.google-analytics.com/__utm.gif?' . str_replace('+', '%20', http_build_query($data));
		}

		//Send Pageview Function for Server-Side Google Analytics
		public function ga_send_pageview($location=null, $title=null, $array=array()){
			$override = apply_filters('pre_ga_send_pageview', null, $location, $title, $array);
			if ( isset($override) ){return;}

			if ( empty($location) ){
				$location = $this->requested_url();
			}

			if ( empty($title) ){
				$title = ( get_the_title() )? get_the_title() : '';
			}

			//GA Parameter Guide: https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters?hl=en
			//GA Hit Builder: https://ga-dev-tools.appspot.com/hit-builder/
			$data = array(
				'v' => 1,
				'tid' => $this->get_option('ga_tracking_id'),
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
			$override = apply_filters('pre_ga_send_event', null, $category, $action, $label, $value, $ni, $array);
			if ( isset($override) ){return;}

			if ( empty($value) ){
				$value = 0;
			}

			//GA Parameter Guide: https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters?hl=en
			//GA Hit Builder: https://ga-dev-tools.appspot.com/hit-builder/
			$data = array(
				'v' => 1,
				'tid' => $this->get_option('ga_tracking_id'),
				'cid' => $this->ga_parse_cookie(),
				't' => 'event',
				'ec' => $category, //Category (Required)
				'ea' => $action, //Action (Required)
				'el' => $label, //Label
				'ev' => $value, //Value
				'ni' => $ni, //Non-Interaction
				'dl' => $this->requested_url(),
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
			$override = apply_filters('pre_ga_send_custom', null, $array);
			if ( isset($override) ){return;}

			//GA Parameter Guide: https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters?hl=en
			//GA Hit Builder: https://ga-dev-tools.appspot.com/hit-builder/
			$defaults = array(
				'v' => 1,
				'tid' => $this->get_option('ga_tracking_id'),
				'cid' => $this->ga_parse_cookie(),
				't' => '',
				'ni' => 1,
				'dl' => $this->requested_url(),
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
			$override = apply_filters('pre_ga_send_exception', null, $message, $fatal, $array);
			if ( isset($override) ){return;}

			//GA Parameter Guide: https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters?hl=en
			//GA Hit Builder: https://ga-dev-tools.appspot.com/hit-builder/
			$data = array(
				'v' => 1,
				'tid' => $this->get_option('ga_tracking_id'),
				'cid' => $this->ga_parse_cookie(),
				't' => 'exception',
				'exd' => $message,
				'exf' => $fatal,
				'dl' => $this->requested_url(),
				'dt' => ( get_the_title() )? get_the_title() : '',
				'dr' => ( isset($_SERVER['HTTP_REFERER']) )? $_SERVER['HTTP_REFERER'] : '',
				'ua' => rawurlencode($_SERVER['HTTP_USER_AGENT']) //User Agent
			);

			$data = array_merge($data, $array);
			$this->ga_send_data($data);
		}

		public function ga_ajax(){
			wp_die(); //@todo "Nebula" 0: Disabling this functionality until (not set) landing pages are under control

			if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }
			if ( !$this->is_bot() ){
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
				if ( $this->get_option('cd_blocker') ){
					$additional_fields['cd' . str_replace('dimension', '', $this->get_option('cd_blocker'))] = 'Google Analytics Blocker';
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
			return false; //@todo "Nebula" 0: Disabling temporarily until (not set) landing pages are under control

			$override = apply_filters('pre_ga_send_data', null, $data);
			if ( isset($override) ){return;}

			//https://ga-dev-tools.appspot.com/hit-builder/

			$response = wp_remote_get('https://www.google-analytics.com/collect?payload_data&' . http_build_query($data));
			return $response;
		}

		//Load abandonment tracking
		public function ga_track_load_abandons(){
			if ( $this->get_option('ga_load_abandon') && !$this->is_bot() && !is_customize_preview() ){
				$custom_metric_hitID = ( $this->get_option('cd_hitid') )? "'cd" . str_replace('dimension', '', $this->get_option('cd_hitid')) . "=" . $this->ga_generate_UUID() . "'," : ''; //Create the Measurement Protocol parameter for cd
				?>
				<script>
					document.addEventListener('visibilitychange', loadAbandonTracking);
					window.onbeforeunload = loadAbandonTracking;

					function loadAbandonTracking(e){
						if ( e.type === 'visibilitychange' && document.visibilityState === 'visible' ){
							return false;
						}

						//Remove listeners so this can only trigger once
						document.removeEventListener('visibilitychange', loadAbandonTracking);
						window.onbeforeunload = null;

						var loadAbandonLevel = 'Unload'; //Typically only desktop browsers trigger this event (sometimes)
						if ( e.type === 'visibilitychange' ){
							loadAbandonLevel = 'Visibility Change'; //This more accurately captures mobile browsers and the majority of abandon types
						}

						//Grab the Google Analytics CID from the cookie (if it exists)
						var gaCID = document.cookie.replace(/(?:(?:^|.*;)\s*_ga\s*\=\s*(?:\w+\.\d\.)([^;]*).*$)|^.*$/, '$1');
						var newReturning = 'Returning visitor or multiple pageview session';
						if ( !gaCID ){
							gaCID = (Math.random()*Math.pow(2, 52));
							newReturning = 'New user or blocking Google Analytics cookie';
						}

						navigator.sendBeacon && navigator.sendBeacon('https://www.google-analytics.com/collect', [
							'tid=<?php echo $this->get_option('ga_tracking_id'); ?>', //Tracking ID
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
							'tid=<?php echo $this->get_option('ga_tracking_id'); ?>', //Tracking ID
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

		//Visualize max scroll percent by adding ?max=16.12 to the URL
		public function visualize_scroll_percent(){
			if ( $this->is_staff() ){
				?>
					<script>
						jQuery(window).on('load', function(){
							if ( <?php echo ( isset($_GET['scroll_max'] )? 'true' : 'false'); ?> ){
								setTimeout(function(){
									scrollTop = jQuery(window).scrollTop();
									pageHeight = jQuery(document).height();
									viewportHeight = jQuery(window).height();
									var percentTop = ((pageHeight-viewportHeight)*<?php echo $_GET['scroll_max']; ?>)/100;
									var divHeight = pageHeight-percentTop;

									jQuery(window).on('scroll', function(){
										scrollTop = jQuery(window).scrollTop();
										var currentScrollPercent = ((scrollTop/(pageHeight-viewportHeight))*100).toFixed(2);
									    console.log('Current Scroll Percent: ' + currentScrollPercent + '%');
									});

									jQuery('<div style="display: none; position: absolute; top: ' + percentTop + 'px; left: 0; width: 100%; height: ' + divHeight + 'px; border-top: 2px solid red; background: linear-gradient(to bottom, rgba(0, 0, 0, 0.1) 0%, rgba(0, 0, 0, 0.8) ' + viewportHeight + 'px); z-index: 999999; pointer-events: none;"></div>').appendTo('body').fadeIn();
								}, 500);
							}
						});
					</script>
				<?php
			}
		}

		//Log fatal errors in Google Analytics as crashes
		public function ga_log_fatal_errors(){
			$error = error_get_last();
			if ( $error['type'] === E_ERROR ){
				$message = strstr($error["message"], ' in /', true);
				$file = strstr($error["file"], 'wp-content'); //Remove high-level directories to reduce clutter and prevent PII

				$this->ga_send_exception('(PHP) ' . $message . ' on line ' . $error["line"] . ' in ' . $file, 1);
			}
		}
	}
}