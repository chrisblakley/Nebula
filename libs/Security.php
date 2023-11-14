<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Security') ){
	trait Security {
		public function hooks(){
			add_action('wp_loaded', array($this, 'bad_access_prevention'));
			add_filter('wp_headers', array($this, 'remove_x_pingback'), 11, 2);
			add_filter('bloginfo_url', array($this, 'hijack_pingback_url'), 11, 2);
			add_action('send_headers', array($this, 'security_headers'));
			remove_action('wp_head', 'wlwmanifest_link');
			add_filter('login_errors', array($this, 'login_errors'));
			add_filter('the_generator', '__return_empty_string'); //Remove Wordpress version info from head and feeds
			add_action('check_comment_flood', array($this, 'check_referrer'));
			add_action('wp_footer', array($this, 'track_notable_bots'));
			add_action('get_header', array($this, 'redirect_author_template'));
			add_filter('xmlrpc_enabled', '__return_false'); //Disable XML-RPC that require authentication
			add_filter('xmlrpc_methods', array($this, 'xmlrpc_methods'), 5, 1); //Disable all XML-RPC requests with a high priority
			add_filter('rest_endpoints', array($this, 'rest_endpoints_security'));
			add_action('wp_footer', array($this, 'cookie_notification'));

			if ( !is_user_logged_in() ){
				add_action('wp_loaded', array($this, 'spam_domain_prevention'));
			}

			if ( is_plugin_active('contact-form-7/wp-contact-form-7.php') ){
				add_filter('wpcf7_validate_email', array($this, 'ignore_invalid_email_addresses'), 10, 2);
				add_filter('wpcf7_validate_email*', array($this, 'ignore_invalid_email_addresses'), 10, 2);
			}

			//Disable the file editor for non-developers
			if ( !defined('DISALLOW_FILE_EDIT') && !$this->is_dev() ){
				define('DISALLOW_FILE_EDIT', true);
			}
		}

		//Additional security headers
		//Test with https://securityheaders.io/
		public function security_headers(){
			//header('x-frame-options: SAMEORIGIN'); //This controls if/where we allow our website to be embedded in iframes
			header('X-XSS-Protection: 1; mode=block');
			header('X-Content-Type-Options: nosniff'); //Ensure MIME types match expected
			header('Access-Control-Allow-Headers: X-WP-Nonce'); //Allow this header for WP Block Editor compatibility with CSP
			header('Developed-with-Nebula: https://nebula.gearside.com'); //Nebula header
			header('Cross-Origin-Resource-Policy: cross-origin;'); //Allow resources to be loaded cross-origin
			header('Cross-Origin-Embedder-Policy: unsafe-none;'); //Eventually consider upgrading this to require-corp
			header('Cross-Origin-Opener-Policy: same-origin-allow-popups;');

			if ( is_ssl() ){
				header('Strict-Transport-Security: max-age=' . YEAR_IN_SECONDS . '; includeSubDomains; preload'); //https://scotthelme.co.uk/hsts-the-missing-link-in-tls/ and consider submitting to https://hstspreload.org/
				header('Referrer-Policy: no-referrer-when-downgrade'); //https://scotthelme.co.uk/a-new-security-header-referrer-policy/

				//Content Security Policy (CSP) and Feature Policy should be set by the child theme. Nebula cannot predict what endpoints or what features will be used, so setting these security policies in the parent theme (outside the control of developers) would be far too restrictive.
				$csp = apply_filters('nebula_csp', "default-src * https: data: blob: 'unsafe-inline' 'unsafe-hashes';"); //Allow all hosts, require https, data, or blob protocols, and ignore (allow) inline scripts by default. WP filter to allow others to hook in to modify the default CSP.
				header('Content-Security-Policy-Report-Only: ' . $csp); //Nebula only reports to the console

				//Permissions Policy: https://scotthelme.co.uk/goodbye-feature-policy-and-hello-permissions-policy/ and https://caniuse.com/#feat=feature-policy and https://caniuse.com/permissions-policy
				$pp = apply_filters('nebula_pp', " accelerometer=(), camera=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()"); //Block usage of all atypical permissions. WP filter to allow others to hook in to modify the default Permissions Policy.
				//header('Permissions-Policy: ' . $pp); //This is commented out for now
			}
		}

		//Log direct access to templates and prevent certain query strings
		public function bad_access_prevention(){
			//Log template direct access attempts
			if ( array_key_exists('ndaat', $this->super->get) ){
				$this->ga_send_exception('(Security) Direct Template Access Prevention on ' . $this->super->get['ndaat'], false, array('security_note' => 'Direct Template Access Attempt'));
				header('Location: ' . home_url('/'));
				exit;
			}

			//Prevent known bot/brute-force query strings.
			if ( array_key_exists('modTest', $this->super->get) ){
				header("HTTP/1.1 403 Unauthorized");
				exit;
			}
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
			$override = apply_filters('pre_track_notable_bots', null);
			if ( isset($override) ){return;}

			//Ignore logged-in users
			if ( is_user_logged_in() ){
				return false;
			}

			if ( isset($this->super->server['HTTP_USER_AGENT']) ){
				$user_agent = str_replace(' ', '_', strtolower($this->super->server['HTTP_USER_AGENT'])); //Normalize the user agent for matching against

				//Lighthouse (Ex: web.dev) (Formerly Google Page Speed Insights) - Ignore Nebula Dashboard tests (?noga)
				if ( !isset($this->super->get['noga']) && strpos($user_agent, 'chrome-lighthouse') !== false ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
					if ( $this->url_components('extension') !== 'js' ){
						$this->ga_send_data($this->ga_build_event('notable_bot', array('bot' => 'Chrome Lighthouse')));
					}
				}

				//W3C Validators
				if ( strpos($user_agent, 'w3c_validator') !== false || strpos($user_agent, 'w3c_css_validator') !== false ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
					$this->ga_send_data($this->ga_build_event('notable_bot', array('bot' => 'W3C Validator')));
				}

				//Redditbot
				if ( strpos($user_agent, 'redditbot') !== false ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
					$this->ga_send_data($this->ga_build_event('notable_bot', array('bot' => 'Redditbot')));
				}

				//OpenAI GPT Bot
				if ( strpos($user_agent, 'gptbot') !== false ){
					$this->ga_send_data($this->ga_build_event('notable_bot', array('bot' => 'GPTBot')));
				}

				//Slackbot
				if ( $this->is_slackbot() ){
					$this->ga_send_data($this->ga_build_event('notable_bot', array('bot' => 'Slackbot')));
				}

				//Discordbot
				if ( strpos($user_agent, 'discordbot') !== false ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
					$this->ga_send_data($this->ga_build_event('notable_bot', array('bot' => 'Discordbot')));
				}

				//Screaming Frog SEO Spider
				if ( strpos($user_agent, 'screaming_frog') !== false ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
					$this->ga_send_data($this->ga_build_event('notable_bot', array('bot' => 'Screaming Frog SEO Spider')));
				}

				//Internet Archive Wayback Machine
				if ( strpos($user_agent, 'archive.org_bot') !== false || strpos($user_agent, 'wayback_save_page') !== false ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
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
		}

		//Check referrer for known spam domains
		public function spam_domain_prevention(){
			$this->timer('Spam Domain Prevention');

			//Skip lookups if user has already been checked or for logged in users.
			if ( (isset($this->super->cookie['spam_domain']) && $this->super->cookie['spam_domain'] === false) || is_user_logged_in() ){
				return false;
			}

			if ( $this->get_option('spam_domain_prevention') ){
				$spam_domain_array = $this->get_spam_domain_list();
				$ip_address = $this->get_ip_address();

				if ( count($spam_domain_array) > 1 ){
					if ( isset($this->super->server['HTTP_REFERER']) && $this->contains(strtolower($this->super->server['HTTP_REFERER']), $spam_domain_array) ){
						$this->ga_send_exception('(Security) Spam domain prevented. Referrer: ' . $this->super->server['HTTP_REFERER'], true, array('security_note' => 'Spam Referrer'));
						do_action('nebula_spambot_prevention');
						header('HTTP/1.1 403 Forbidden');
						wp_die();
					}

					if ( isset($this->super->server['REMOTE_HOST']) && $this->contains(strtolower($this->super->server['REMOTE_HOST']), $spam_domain_array) ){
						$this->ga_send_exception('(Security) Spam domain prevented. Hostname: ' . $this->super->server['REMOTE_HOST'], true, array('security_note' => 'Spam Hostname'));
						do_action('nebula_spambot_prevention');
						header('HTTP/1.1 403 Forbidden');
						wp_die();
					}

					if ( isset($this->super->server['SERVER_NAME']) && $this->contains(strtolower($this->super->server['SERVER_NAME']), $spam_domain_array) ){
						$this->ga_send_exception('(Security) Spam domain prevented. Server Name: ' . $this->super->server['SERVER_NAME'], true, array('security_note' => 'Spam Server Name'));
						do_action('nebula_spambot_prevention');
						header('HTTP/1.1 403 Forbidden');
						wp_die();
					}

					if ( isset($ip_address) && $this->contains(strtolower(gethostbyaddr($ip_address)), $spam_domain_array) ){
						$this->ga_send_exception('(Security) Spam domain prevented. Network Hostname: ' . $ip_address, true, array('security_note' => 'Spam Network Hostname'));
						do_action('nebula_spambot_prevention');
						header('HTTP/1.1 403 Forbidden');
						wp_die();
					}
				} else {
					$this->ga_send_exception('(Security) spammers.txt has no entries!', false);
				}

				$this->set_cookie('spam_domain', false);
			}

			do_action('qm/info', 'Spam Domain Check Performed');
			$this->timer('Spam Domain Prevention', 'end');
		}

		//Return an array of spam domains from Matomo (or the latest Nebula on GitHub)
		public function get_spam_domain_list(){
			$spam_domain_json_file = get_template_directory() . '/inc/data/spam_domain_list.txt';

			$spam_domain_list = nebula()->transient('nebula_spam_domain_list', function($data){
				$response = $this->remote_get('https://raw.githubusercontent.com/matomo-org/referrer-spam-list/master/spammers.txt'); //Watch for this to change from "master" to "main" (if ever)
				if ( !is_wp_error($response) ){
					$spam_domain_list = $response['body'];
				}

				//If there was an error or empty response, try my GitHub repo
				if ( is_wp_error($response) || empty($spam_domain_list) ){ //This does not check availability because it is the same hostname as above.
					$response = $this->remote_get('https://raw.githubusercontent.com/chrisblakley/Nebula/main/inc/data/spam_domain_list.txt');
					if ( !is_wp_error($response) ){
						$spam_domain_list = $response['body'];
					}
				}

				//If either of the above remote requests received data, update the local file and store the data in a transient for 36 hours
				if ( !is_wp_error($response) && !empty($spam_domain_list) ){
					WP_Filesystem();
					global $wp_filesystem;
					$wp_filesystem->put_contents($data['spam_domain_json_file'], $spam_domain_list);

					return $spam_domain_list;
				}
			}, array('spam_domain_json_file' => $spam_domain_json_file), HOUR_IN_SECONDS*36);

			//If neither remote resource worked, get the local file
			if ( empty($spam_domain_list) ){
				WP_Filesystem();
				global $wp_filesystem;
				$spam_domain_list = $wp_filesystem->get_contents($spam_domain_json_file);
			}

			//If one of the above methods worked, parse the data.
			if ( !empty($spam_domain_list) ){
				$spam_domain_array = array();
				foreach( explode("\n", $spam_domain_list) as $line ){
					if ( !empty($line) ){
						$spam_domain_array[] = $line;
					}
				}
			} else {
				$this->ga_send_exception('(Security) spammers.txt was not available!', false);
			}

			//Add manual and user-added spam domains
			$manual_nebula_spam_domains = array(
				'bitcoinpile.com',
			);
			$all_spam_domains = apply_filters('nebula_spam_domains', $manual_nebula_spam_domains);

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
	}
}