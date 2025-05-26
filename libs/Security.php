<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Security') ){
	trait Security {
		public function hooks(){
			add_action('wp_loaded', array($this, 'bad_access_prevention'));
			add_action('init', array($this, 'block_obvious_bad_requests'));
			add_filter('wp_headers', array($this, 'remove_x_pingback'), 11, 2);
			add_filter('bloginfo_url', array($this, 'hijack_pingback_url'), 11, 2);
			add_action('send_headers', array($this, 'security_headers'));
			remove_action('wp_head', 'wlwmanifest_link');
			add_filter('login_errors', array($this, 'login_errors'));
			add_action('wp_login', array($this, 'track_login_success'), 10, 2);
			add_action('wp_login_failed', array($this, 'track_login_failure'));
			add_filter('the_generator', '__return_empty_string'); //Remove Wordpress version info from head and feeds
			add_action('check_comment_flood', array($this, 'check_referrer'));
			add_action('wp_footer', array($this, 'track_notable_bots'));
			add_action('get_header', array($this, 'redirect_author_template'));
			add_filter('xmlrpc_enabled', '__return_false'); //Disable XML-RPC that require authentication
			add_filter('xmlrpc_methods', array($this, 'xmlrpc_methods'), 5, 1); //Disable all XML-RPC requests with a high priority
			add_filter('rest_endpoints', array($this, 'rest_endpoints_security'));
			add_action('wp_footer', array($this, 'cookie_notification'));

			if ( !is_user_logged_in() ){
				add_action('template_redirect', array($this, 'spam_domain_prevention'), 10); //This must be *after* the session data is initialized because it updates the cookie (which is why this is triggered on the template_redirect hook and not init)
			}

			if ( is_plugin_active('contact-form-7/wp-contact-form-7.php') ){
				add_filter('wpcf7_validate_email', array($this, 'ignore_invalid_email_addresses'), 10, 2);
				add_filter('wpcf7_validate_email*', array($this, 'ignore_invalid_email_addresses'), 10, 2);
				add_filter('wpcf7_spam', array($this, 'nebula_cf7_spam_detection_agent'), 10, 2);
			}

			//Disable the file editor for non-developers
			if ( !defined('DISALLOW_FILE_EDIT') && !$this->is_dev() ){
				define('DISALLOW_FILE_EDIT', true);
			}
		}

		//Additional security headers
		//Test with https://securityheaders.io/ and/or https://developer.mozilla.org/en-US/observatory
		public function security_headers(){
			//header('x-frame-options: SAMEORIGIN'); //This controls if/where we allow our website to be embedded in iframes
			header('X-XSS-Protection: 1; mode=block');
			header('X-Content-Type-Options: nosniff'); //Ensure MIME types match expected
			header('Access-Control-Allow-Headers: X-WP-Nonce'); //Allow this header for WP Block Editor compatibility with CSP
			header('Developed-with-Nebula: https://nebula.gearside.com'); //Nebula header
			header('Cross-Origin-Resource-Policy: cross-origin'); //Allow resources to be loaded cross-origin
			header('Cross-Origin-Embedder-Policy: unsafe-none'); //Eventually consider upgrading this to require-corp
			header('Cross-Origin-Opener-Policy: same-origin-allow-popups');

			if ( is_ssl() ){
				header('Strict-Transport-Security: max-age=' . YEAR_IN_SECONDS . '; includeSubDomains; preload'); //https://scotthelme.co.uk/hsts-the-missing-link-in-tls/ and consider submitting to https://hstspreload.org/
				header('Referrer-Policy: strict-origin-when-cross-origin'); //https://scotthelme.co.uk/a-new-security-header-referrer-policy/

				//Content Security Policy (CSP) and Feature Policy should be set by the child theme. Nebula cannot predict what endpoints or what features will be used, so setting these security policies in the parent theme (outside the control of developers) would be far too restrictive.
				$csp = apply_filters('nebula_csp', "default-src * https: data: blob: 'unsafe-inline' 'unsafe-hashes'; object-src 'self'; base-uri 'self'"); //Allow all hosts, require https, data, or blob protocols, and ignore (allow) inline scripts by default. This is a flexible default CSP, but it is recommended to strengthen it via the child theme! WP filter to allow others to hook in to modify the default CSP.
				header('Content-Security-Policy-Report-Only: ' . $csp); //Nebula only reports to the console without enforcing

				//Permissions Policy: https://scotthelme.co.uk/goodbye-feature-policy-and-hello-permissions-policy/ and https://caniuse.com/#feat=feature-policy and https://caniuse.com/permissions-policy
				$pp = apply_filters('nebula_pp', " accelerometer=(), camera=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()"); //Block usage of all atypical permissions. WP filter to allow others to hook in to modify the default Permissions Policy.
				//header('Permissions-Policy: ' . $pp); //This is commented out for now
			}
		}

		//Log direct access to templates and prevent certain query strings
		public function bad_access_prevention(){
			if ( $this->is_minimal_mode() ){return null;}

			//Log template direct access attempts
			if ( array_key_exists('ndaat', $this->super->get) ){
				$this->ga_send_exception('(Security) Direct Template Access Prevention on ' . $this->super->get['ndaat'], false, array('security_note' => 'Direct Template Access Attempt'));
				header('Location: ' . home_url('/'));
				exit;
			}

			//Prevent known bot/brute-force query strings.
			if ( array_key_exists('modTest', $this->super->get) ){
				header('HTTP/1.1 403 Forbidden (Err: NBAPQ)');
				wp_die(
					'Access forbidden.', //Message
					'403 Forbidden', //Title
					array(
						'response' => 403, //HTTP status code
						'back_link' => false //Remove the back link
					)
				);
			}
		}

		//Block common and obvious methods from accessing anything
		//Remember: This only blocks the most obvious access attempts. Use more sophisticated security systems for broader coverage.
		public function block_obvious_bad_requests(){
			if ( $this->is_minimal_mode() ){return null;}
			$this->timer('Block Obvious Bad Requests', 'start', '[Nebula] Security');

			if ( $this->is_cli() ){
				return null;
			}

			if ( $this->get_option('block_obvious_bad_requests') ){ //Only when Nebula option is enabled
				//Check User Agents
				$banned_user_agents = apply_filters('nebula_banned_user_agents', array(
					'python-requests',
					'go-http-client',
					'httpclient',
					'scrapy',
					'guzzlehttp',
				));

				//Get the User-Agent (default to empty string if not set)
				$user_agent = '';
				if ( isset($_SERVER['HTTP_USER_AGENT']) ){
					$user_agent = $_SERVER['HTTP_USER_AGENT'];
				}

				//Check if the User-Agent matches any in the list (case-insensitive)
				foreach( $banned_user_agents as $banned_ua ){
					if ( stripos(strtolower($user_agent), strtolower($banned_ua)) !== false ){
						header('HTTP/1.1 403 Forbidden (Err: NBOBRUA)');
						wp_die(
							'Access forbidden.', //Message
							'403 Forbidden', //Title
							array(
								'response' => 403, //HTTP status code
								'back_link' => false //Remove the back link
							)
						);
					}
				}

				//Check endpoints
				$blocked_endpoints = apply_filters('nebula_blocked_endpoints', array(
					'.env',
					'.aws',
				));

				$url = strtolower($this->requested_url());
				foreach ( $blocked_endpoints as $blocked_endpoint ){
					if ( str_contains($url, $blocked_endpoint) ){
						header('HTTP/1.1 403 Forbidden (Err: NBOBRE)');
						wp_die(
							'Access forbidden.', //Message
							'403 Forbidden', //Title
							array(
								'response' => 403, //HTTP status code
								'back_link' => false //Remove the back link
							)
						);
					}
				}
			}

			$this->timer('Block Obvious Bad Requests', 'end');
		}

		//Disable Pingbacks to prevent security issues
		public function remove_x_pingback($headers){
			$override = apply_filters('pre_nebula_remove_x_pingback', null, $headers);
			if ( isset($override) ){return $override;}

			if ( isset($headers['X-Pingback']) ){
				unset($headers['X-Pingback']);
			}

			return $headers;
		}

		//Hijack pingback_url for get_bloginfo (<link rel="pingback" />).
		public function hijack_pingback_url($output, $property){
			$override = apply_filters('pre_nebula_hijack_pingback_url', null, $output, $property);
			if ( isset($override) ){return $override;}

			return ( $property === 'pingback_url' )? null : $output;
		}

		//Prevent login error messages from giving too much information
		public function login_errors($error){
			$override = apply_filters('pre_nebula_login_errors', null, $error);
			if ( isset($override) ){return $override;}

			if ( !empty($error) ){ //If an error exists
				$event_properties = array('page_location'=> wp_login_url(), 'page_title' => 'Log In');

				if ( $this->contains($error, array('The password you entered for the username')) ){
					$incorrect_username_start = strpos($error, 'for the username ')+17;
					$incorrect_username_stop = strpos($error, ' is incorrect')-$incorrect_username_start;
					$incorrect_username = strip_tags(substr($error, $incorrect_username_start, $incorrect_username_stop));
					$this->ga_send_exception('(Security) Login error (incorrect password) for user ' . $incorrect_username, false, $event_properties);
				} else {
					if ( $this->contains($error, array('Invalid username')) ){ //If no username was entered, tag the user as a potential bot
						$event_properties['security_note'] = 'Possible Bot';
					}
					$this->ga_send_exception('(Security) Login error: ' . strip_tags($error), false, $event_properties);
				}

				if ( !$this->is_bot() ){ //If it is a human show a generic error message (otherwise if it is a bot we will show an empty string below)
					return 'Login Error.';
				}

				return '';
			}

			return $error;
		}

		//Count login success and failures
		public function track_login_success($user_login, $user){
			$this->increment_login_stat('success', $user_login);
		}
		public function track_login_failure($username){
			$this->increment_login_stat('fail', $username);
		}
		public function increment_login_stat($type, $username=''){
			if ( $type !== 'success' && $type !== 'fail' ){
				return;
			}

			$today = date('Y-m-d');
			$stats = get_transient('nebula_analytics_logins');

			if ( !is_array($stats) ){
				$stats = array();
			}

			if ( !isset($stats[$today]) || !is_array($stats[$today]) ){
				$stats[$today] = array();
			}

			if ( !isset($stats[$today][$type]) || !is_array($stats[$today][$type]) ){
				$stats[$today][$type] = array('count' => 0, 'usernames' => array());
			}

			$stats[$today][$type]['count']++;

			if ( $username ){
				$stats[$today][$type]['usernames'][] = $username;
			}

			//Prune to a true rolling 7-day window
			$cutoff = strtotime('-6 days'); //Include today, so 7 total
			foreach ( $stats as $date => $counts ){
				if ( strtotime($date) < $cutoff ){
					unset($stats[$date]);
				}
			}

			set_transient('nebula_analytics_logins', $stats, DAY_IN_SECONDS*8); //Slightly over 7 days for buffer
		}

		// Get a count of successful or failed logins and optionally return an array of the associated usernames
		public function get_login_counts($type, $usernames=false){
			if ( $type !== 'success' && $type !== 'fail' ){
				return ( $usernames )? array() : 0;
			}

			$stats = get_transient('nebula_analytics_logins');

			if ( !is_array($stats) ){
				return ( $usernames )? array() : 0;
			}

			if ( $usernames ){
				$all_usernames = array();

				// Loop through the stats to collect all usernames
				foreach ( $stats as $day ){
					// Ensure we're accessing the 'fail' or 'success' type correctly
					if ( isset($day[$type]) && is_array($day[$type]) ){
						// Access the usernames under the 'fail' or 'success' type
						if ( isset($day[$type]['usernames']) && is_array($day[$type]['usernames']) ){
							// Add usernames to the list based on their frequency (count)
							foreach ( $day[$type]['usernames'] as $username ){
								// Add the username for each failed login (no need to multiply by count since usernames are listed directly)
								$all_usernames[] = $username;
							}
						}
					}
				}

				// Sort usernames by frequency (descending)
				arsort($all_usernames);
				return $all_usernames;
			}

			$total = 0;

			// Count the total successes/fails
			foreach ( $stats as $day ){
				if ( isset($day[$type]['count']) ){
					$total += $day[$type]['count'];
				}
			}

			return $total;
		}

		//Check referrer in order to comment
		public function check_referrer(){
			if ( !isset($this->super->server['HTTP_REFERER']) || empty($this->super->server['HTTP_REFERER']) ){
				wp_die('Please do not access this file directly.');
			}
		}

		//Disable author archives to prevent ?author=1 from showing usernames.
		public function redirect_author_template(){
			if ( (isset($this->super->get['author']) || basename($this->current_theme_template) == 'author.php') && !$this->get_option('author_bios') ){
				wp_redirect(apply_filters('nebula_no_author_redirect', home_url('/') . '?s=about'));
				exit;
			}
		}

		//Disable all XML-RPC requests
		public function xmlrpc_methods($methods){
			$override = apply_filters('pre_nebula_xmlrpc_methods', null, $methods);
			if ( isset($override) ){return $override;}
			return array(); //Empty the array of methods
		}

		//Manage what is exposed in the REST API
		public function rest_endpoints_security($endpoints){
			//Disable the /users endpoint if author bios is disabled and the user is not logged in
			if ( !is_user_logged_in() && !$this->get_option('author_bios') ){
				if ( isset($endpoints['/wp/v2/users']) ){
					unset($endpoints['/wp/v2/users']);
				}

				if ( isset($endpoints['/wp/v2/users/(?P<id>[\d]+)']) ){
					unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
				}
			}

			return $endpoints;
		}

		//Track Notable Bots
		//This data can indicate when the website was shared on certain platforms. That is why we are only tracking bots that are sent by a user action (and not search indexing bots, for example).
		function track_notable_bots(){
			if ( $this->is_minimal_mode() ){return null;}
			$override = apply_filters('pre_track_notable_bots', null);
			if ( isset($override) ){return;}

			//Ignore logged-in users
			if ( is_user_logged_in() ){
				return null;
			}

			$this->timer('Notable Bot Tracking', 'start', '[Nebula] Security');

			if ( isset($this->super->server['HTTP_USER_AGENT']) ){
				$user_agent = str_replace(' ', '_', strtolower($this->super->server['HTTP_USER_AGENT'])); //Normalize the user agent for matching against

				//Lighthouse (Ex: web.dev) (Formerly Google Page Speed Insights) - Ignore Nebula Dashboard tests (?noga)
				if ( !isset($this->super->get['noga']) && str_contains($user_agent, 'chrome-lighthouse') ){
					if ( $this->url_components('extension') !== 'js' ){
						$this->ga_send_data($this->ga_build_event('notable_bot', array('bot' => 'Chrome Lighthouse')));
					}
				}

				//W3C Validators
				if ( str_contains($user_agent, 'w3c_validator') || str_contains($user_agent, 'w3c_css_validator') ){
					$this->ga_send_data($this->ga_build_event('notable_bot', array('bot' => 'W3C Validator')));
				}

				//Redditbot
				if ( str_contains($user_agent, 'redditbot') ){
					$this->ga_send_data($this->ga_build_event('notable_bot', array('bot' => 'Redditbot')));
				}

				//OpenAI GPT Bot
				if ( str_contains($user_agent, 'gptbot') ){
					$this->ga_send_data($this->ga_build_event('notable_bot', array('bot' => 'GPTBot')));
				}

				//Slackbot
				if ( $this->is_slackbot() ){
					$this->ga_send_data($this->ga_build_event('notable_bot', array('bot' => 'Slackbot')));
				}

				//Discordbot
				if ( str_contains($user_agent, 'discordbot') ){
					$this->ga_send_data($this->ga_build_event('notable_bot', array('bot' => 'Discordbot')));
				}

				//Screaming Frog SEO Spider
				if ( str_contains($user_agent, 'screaming_frog') ){
					$this->ga_send_data($this->ga_build_event('notable_bot', array('bot' => 'Screaming Frog SEO Spider')));
				}

				//Internet Archive Wayback Machine
				if ( str_contains($user_agent, 'archive.org_bot') || str_contains($user_agent, 'wayback_save_page') ){
					$this->ga_send_data($this->ga_build_event('notable_bot', array('bot' => 'Internet Archive Wayback Machine')));
				}
			}

			//Other Notable Bots (these have been manually normalized to match above):
				//bingbot
				//seositecheckup
				//gtmetrix
				//pingdompagespeed or pingbot
				//twitterbot
				//semrushbot
				//ahrefsbot
				//facebookexternalhit
				//microsoft_office
				//google-structured-data-testing-tool

			$this->timer('Notable Bot Tracking', 'end');
		}

		//Check referrer for known spam domains
		public function spam_domain_prevention(){
			if ( $this->is_minimal_mode() ){return null;}
			$this->timer('Spam Domain Prevention', 'start', '[Nebula] Security');

			//Use session cookie array
			$session_cookie_data = $this->prep_new_session_cookie();

			if ( isset($_COOKIE['session']) ){
				$session_cookie_data = json_decode(stripslashes($_COOKIE['session']), true);

				if ( !is_array($session_cookie_data) ){
					$session_cookie_data = $this->prep_new_session_cookie();
				}
			}

			//Skip if already marked or user is logged in
			if ( (isset($session_cookie_data['spam_domain']) && $session_cookie_data['spam_domain'] === false) || is_user_logged_in() ){
				return null;
			}

			if ( $this->get_option('spam_domain_prevention') ){
				$spam_domain_array = $this->get_spam_domain_list();
				$ip_address = $this->get_ip_address();

				if ( count($spam_domain_array) > 1 ){
					//Referrer
					if ( isset($this->super->server['HTTP_REFERER']) && $this->contains(strtolower($this->super->server['HTTP_REFERER']), $spam_domain_array) ){
						$this->ga_send_exception('(Security) Spam domain prevented. Referrer: ' . $this->super->server['HTTP_REFERER'], true, array('security_note' => 'Spam Referrer'));
						do_action('nebula_spambot_prevention');
						header('HTTP/1.1 403 Forbidden (Err: NSDPR)');
						wp_die('Access forbidden.', '403 Forbidden', array('response' => 403, 'back_link' => false));
					}

					//Remote Host
					if ( isset($this->super->server['REMOTE_HOST']) && $this->contains(strtolower($this->super->server['REMOTE_HOST']), $spam_domain_array) ){
						$this->ga_send_exception('(Security) Spam domain prevented. Hostname: ' . $this->super->server['REMOTE_HOST'], true, array('security_note' => 'Spam Hostname'));
						do_action('nebula_spambot_prevention');
						header('HTTP/1.1 403 Forbidden (Err: NSDPH)');
						wp_die('Access forbidden.', '403 Forbidden', array('response' => 403, 'back_link' => false));
					}

					//Server Name
					if ( isset($this->super->server['SERVER_NAME']) && $this->contains(strtolower($this->super->server['SERVER_NAME']), $spam_domain_array) ){
						$this->ga_send_exception('(Security) Spam domain prevented. Server Name: ' . $this->super->server['SERVER_NAME'], true, array('security_note' => 'Spam Server Name'));
						do_action('nebula_spambot_prevention');
						header('HTTP/1.1 403 Forbidden (Err: NSDPS)');
						wp_die('Access forbidden.', '403 Forbidden', array('response' => 403, 'back_link' => false));
					}

					//Network Hostname
					if ( isset($ip_address) && $this->contains(strtolower(gethostbyaddr($ip_address)), $spam_domain_array) ){
						$this->ga_send_exception('(Security) Spam domain prevented. Network Hostname: ' . $ip_address, true, array('security_note' => 'Spam Network Hostname'));
						do_action('nebula_spambot_prevention');
						header('HTTP/1.1 403 Forbidden (Err: NSDPN)');
						wp_die('Access forbidden.', '403 Forbidden', array('response' => 403, 'back_link' => false));
					}

					//Requested URL
					if ( isset($this->super->server['REQUEST_URI']) && $this->contains(strtolower($this->super->server['REQUEST_URI']), $spam_domain_array) ){
						$this->ga_send_exception('(Security) Spam domain prevented. URL: ' . $this->super->server['REQUEST_URI'], true, array('security_note' => 'Spam domain in the requested URL'));
						do_action('nebula_spambot_prevention');
						header('HTTP/1.1 403 Forbidden (Err: NSDPU)');
						wp_die('Access forbidden.', '403 Forbidden', array('response' => 403, 'back_link' => false));
					}
				} else {
					$this->ga_send_exception('(Security) spammers.txt has no entries!', false);
				}

				//Mark as checked in session cookie
				$session_cookie_data['spam_domain'] = false;
				$this->set_cookie('session', json_encode($session_cookie_data), time()+HOUR_IN_SECONDS*4, false); //Needs to be able to be read by JavaScript
			}

			do_action('qm/info', 'Spam Domain Check Performed');
			$this->timer('Spam Domain Prevention', 'end');
		}

		//Return an array of spam domains
		public function get_spam_domain_list(){
			if ( $this->is_minimal_mode() ){return null;}
			$this->timer('Get Spam Domain List', 'start', '[Nebula] Security');

			//First get the latest spam domain list maintained by Matomo or Nebula's cache of the Matomo list
			$spam_domain_public_list = false; //Default

			if ( $this->is_transients_enabled() ){
				$spam_domain_public_file = get_template_directory() . '/inc/data/spam_domain_list.txt'; //Eventually change this to "spam_domain_public_list.txt"
				$spam_domain_public_list = nebula()->transient('nebula_spam_domain_public_list', function($data){
					$response = $this->remote_get('https://raw.githubusercontent.com/matomo-org/referrer-spam-list/master/spammers.txt'); //Watch for this to change from "master" to "main" (if ever)
					if ( !is_wp_error($response) ){
						$spam_domain_public_list = $response['body'];
					}

					//If there was an error or empty response, try my GitHub repo
					if ( is_wp_error($response) || empty($spam_domain_public_list) ){ //This does not check availability because it is the same hostname as above.
						$response = $this->remote_get('https://raw.githubusercontent.com/chrisblakley/Nebula/main/inc/data/spam_domain_list.txt'); //Eventually change this to "spam_domain_public_list.txt"
						if ( !is_wp_error($response) ){
							$spam_domain_public_list = $response['body'];
						}
					}

					//If either of the above remote requests received data, update the local file and store the data in a transient for 36 hours
					if ( !is_wp_error($response) && !empty($spam_domain_public_list) ){
						WP_Filesystem();
						global $wp_filesystem;
						$wp_filesystem->put_contents($data['spam_domain_public_file'], $spam_domain_public_list);

						return $spam_domain_public_list;
					}
				}, array('spam_domain_public_file' => $spam_domain_public_file), HOUR_IN_SECONDS*36);
			}

			//If neither remote resource worked, get the local file
			if ( empty($spam_domain_public_list) ){
				WP_Filesystem();
				global $wp_filesystem;
				$spam_domain_public_list = $wp_filesystem->get_contents($spam_domain_public_file);
			}

			//If one of the above methods worked, parse the data.
			if ( !empty($spam_domain_public_list) ){
				$spam_domain_array = array();
				foreach( explode("\n", $spam_domain_public_list) as $line ){
					if ( !empty($line) ){
						$spam_domain_array[] = $line;
					}
				}
			} else {
				$this->ga_send_exception('(Security) Public spammers.txt was not available!', false);
			}

			//Next get the latest spam domain "manual" list maintained by Nebula or the local cache of the Nebula list
			$spam_domain_private_list = false; //Default

			if ( $this->is_transients_enabled() ){
				$spam_domain_private_file = get_template_directory() . '/inc/data/spam_domain_private_list.txt';
				$spam_domain_private_list = nebula()->transient('nebula_spam_domain_private_list', function($data){
					$response = $this->remote_get('https://raw.githubusercontent.com/chrisblakley/Nebula/main/inc/data/spam_domain_private_list.txt');
					if ( !is_wp_error($response) ){
						$spam_domain_private_list = $response['body'];
					}

					//If the above remote request received data, update the local file and store the data in a transient for 36 hours
					if ( !is_wp_error($response) && !empty($spam_domain_private_list) ){
						WP_Filesystem();
						global $wp_filesystem;
						$wp_filesystem->put_contents($data['spam_domain_private_file'], $spam_domain_private_list);

						return $spam_domain_private_list;
					}
				}, array('spam_domain_private_file' => $spam_domain_private_file), HOUR_IN_SECONDS*36);
			}

			//If neither remote resource worked, get the local file
			if ( empty($spam_domain_private_list) ){
				WP_Filesystem();
				global $wp_filesystem;
				$spam_domain_private_list = $wp_filesystem->get_contents($spam_domain_private_file);
			}

			if ( !empty($spam_domain_private_list) ){
				$spam_domain_array = array();
				foreach( explode("\n", $spam_domain_private_list) as $line ){
					if ( !empty($line) ){
						$spam_domain_array[] = $line;
					}
				}
			} else {
				$this->ga_send_exception('(Security) Private spam_domain_private_list.txt was not available!', false);
			}

			//Add manual and user-added spam domains
			$manual_nebula_spam_domains = array(
				'bitcoinpile.com',
				'84lv.com', //2024
				'16lv.com', //2024
				'1-88.vip', //2024
				'top8.co', //2024
				'tip8.co', //2024
				'1-88.live', //2024
			);
			$all_spam_domains = apply_filters('nebula_spam_domains', $manual_nebula_spam_domains);

			$this->timer('Get Spam Domain List');

			return array_merge($spam_domain_array, $all_spam_domains);
		}

		//Add custom validation for CF7 form fields
		//https://contactform7.com/2015/03/28/custom-validation/
		function ignore_invalid_email_addresses($result, $tag){ //$result is an instance of WPCF7_Validation class that manages a sequence of validation processes. $tag is an instance of WPCF7_FormTag.
			//$input_type = $tag['type']; //The type of tag (either "email" or "email*") - this function only runs on email types so this is not needed
			$field_name = $tag->name; //The name of this input itself by the form creator (such as "email" or "your-email")
			$field_value = $this->super->post[$field_name]; //The value of this input field provided by the user (the actual email address)

			if ( empty($field_value) ){
				return $result; //Exit as we are missing data
			}

			$bad_domain_array = $this->get_bad_email_domains_list();

			if ( count($bad_domain_array) > 1 ){
				if ( $this->contains(strtolower(trim($field_value)), $bad_domain_array) ){
					$result->invalidate($tag, 'Please enter a valid email address.');
				}
			}

			return $result;
		}

		//Return an array of bad email domains from Hubspot (or the latest Nebula on GitHub)
		public function get_bad_email_domains_list(){
			if ( $this->is_minimal_mode() ){return null;}
			$this->timer('Get Bad Email Domains List', 'start', '[Nebula] Security');

			$bad_email_domains_list = false; //Default

			if ( $this->is_transients_enabled() ){
				$bad_email_domains_file = get_template_directory() . '/inc/data/bad_email_domains.csv';
				$bad_email_domains_list = nebula()->transient('nebula_bad_email_domains', function($data){
					$response = $this->remote_get('https://f.hubspotusercontent40.net/hubfs/2832391/Marketing/Lead-Capture/free-domains-2.csv'); //Originall found here: https://knowledge.hubspot.com/forms/what-domains-are-blocked-when-using-the-forms-email-domains-to-block-feature
					if ( !is_wp_error($response) ){
						$bad_email_domains_list = $response['body'];
					}

					//If there was an error or empty response, try my GitHub repo
					if ( is_wp_error($response) || empty($bad_email_domains_list) ){ //This does not check availability because it is the same hostname as above.
						$response = $this->remote_get('https://raw.githubusercontent.com/chrisblakley/Nebula/main/inc/data/bad_email_domains.csv');
						if ( !is_wp_error($response) ){
							$bad_email_domains_list = $response['body'];
						}
					}

					//If either of the above remote requests received data, update the local file and store the data in a transient for 36 hours
					if ( !is_wp_error($response) && !empty($bad_email_domains_list) ){
						WP_Filesystem();
						global $wp_filesystem;
						$wp_filesystem->put_contents($data['bad_email_domains_file'], $bad_email_domains_list);

						return $bad_email_domains_list;
					}
				}, array('bad_email_domains_file' => $bad_email_domains_file), HOUR_IN_SECONDS*36);
			}

			//If neither remote resource worked, get the local file
			if ( empty($bad_email_domains_list) ){
				WP_Filesystem();
				global $wp_filesystem;
				$bad_email_domains_list = $wp_filesystem->get_contents($bad_email_domains_file);
			}

			//If one of the above methods worked, parse the data.
			if ( !empty($bad_email_domains_list) ){
				$bad_email_domain_array = array();
				foreach( explode("\n", $bad_email_domains_list) as $line ){
					if ( !empty($line) ){
						$bad_email_domain_array[] = $line;
					}
				}
			} else {
				$this->ga_send_exception('(Security) Hubspot bad email domains CSV was not available!', false);
			}

			//Add manual and user-added spam domains
			//Could add domains like testing.com or test.com but that would make debugging more difficult and we don't see this used enough to warrant preventing it.
			$manual_nebula_bad_email_domains = array(
				'mailinator.com',
			);
			$all_bad_email_domains = apply_filters('nebula_bad_email_domains', $manual_nebula_bad_email_domains);

			$this->timer('Get Bad Email Domains List', 'end');
			return array_merge($bad_email_domain_array, $all_bad_email_domains);
		}

		//Cookie Notification HTML that appears in the footer
		public function cookie_notification(){
			if ( ($this->option('cookie_notification') || $this->option('ga_require_consent')) && empty($this->super->cookie['acceptcookies']) ){ //Show if the Cookie Notification or Require Consent Nebula option is enabled
				?>
				<div id="nebula-cookie-notification" role="region" aria-label="Accept Cookies">
					<p><?php echo $this->option('cookie_notification'); ?></p>
					<div class="links">
						<?php if ( get_privacy_policy_url() ): ?>
							<a href="<?php echo get_privacy_policy_url(); ?>" target="_blank"><?php _e('Privacy Policy', 'nebula'); ?></a>
						<?php endif; ?>

						<a id="nebula-cookie-accept" href="#"><?php _e('Accept', 'nebula'); ?></a>
					</div>
				</div>
				<?php
			}
		}

		//Nebula can check for spam form submissions
		public function nebula_cf7_spam_detection_agent($is_spam, $submission=null){
			if ( $this->is_minimal_mode() ){return null;}

			if ( $is_spam ) { //If the submission was already detected as spam, don't check further details
				return $is_spam;
			}

			//Only use Nebula detection to block submissions if the Nebula Option is enabled
			if ( $this->get_option('cf7_spam_detection_agent') ){
				$cf7_form = WPCF7_ContactForm::get_current();
				$form_tags = $cf7_form->scan_form_tags();

				foreach ( $form_tags as $tag ){
					$value = isset($this->super->post[$tag->name])? $this->super->post[$tag->name] : '';

					if ( $this->is_spam_field_data_detected($value) ){
						$is_spam = true;

						if ( $submission ){
							$submission->add_spam_log(array(
								'agent' => 'nebula',
								'reason' => sprintf(
									__('Nebula detected a form field (%1$s) that contained an HTML hyperlink.', 'nebula'),
									$tag->name
								),
							));
						}

						return $is_spam;
					}
				}
			}

			return $is_spam;
		}

		//This actually runs the detection
		public function is_spam_field_data_detected($value){
			//If the value is an array, loop through each entry
			if ( is_array($value) ){
				foreach ( $value as $this_value ){
					if ( $this->is_spam_field_data_detected($this_value) ){
						return true;
					}
				}

				return false;
			}

			if ( is_string($value) ){
				//If a value contains an HTML <a> tag
				if ( preg_match("/<a.*href=.*>/i", $value) ){
					return true;
				}

				//If a value contains a script or iframe tag
				if ( preg_match('/<(script|iframe)[^>]*>/i', $value) ){
					return true;
				}

				//Detect excessive use of URLs
				//if ( preg_match_all('/https?:\/\//i', $value, $matches) && count($matches[0]) >= 5 ){
				//	return true;
				//}

				//Very long words without spaces
				//if ( preg_match('/[a-zA-Z0-9]{50,}/i', $value) ){
				//	return true;
				//}

				//A single character repeated many times
				//if ( preg_match('/(\S)\1{14,}/i', $value) ){
				//	return true;
				//}
			}

			return false;
		}
	}
}