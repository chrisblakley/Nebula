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
			//add_action('wp_footer', array($this, 'track_notable_bots')); //Disabled for now. Not super useful.
			add_action('get_header', array($this, 'redirect_author_template'));
			add_filter('xmlrpc_enabled', '__return_false'); //Disable XML-RPC that require authentication
			add_filter('xmlrpc_methods', function(){return array();}, PHP_INT_MAX); //Disable all XML-RPC requests with the highest priority
			add_filter('rest_endpoints', array($this, 'rest_endpoints_security'));
			add_action('wp_footer', array($this, 'cookie_notification'));

			if ( !is_user_logged_in() ){
				add_action('wp_loaded', array($this, 'spam_domain_prevention'));
			}

			//Disable the file editor for non-developers
			if ( !$this->is_dev() && !defined('DISALLOW_FILE_EDIT') ){
				define('DISALLOW_FILE_EDIT', true);
			}
		}

		//Additional security headers
		//Test with https://securityheaders.io/
		public function security_headers(){
			header('x-frame-options: SAMEORIGIN');
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
				$csp = apply_filters('nebula_csp', "default-src https: 'unsafe-inline';"); //Require https and ignore (allow) inline scripts by default, allow others to hook in to modify the default CSP
				header('Content-Security-Policy-Report-Only: ' . $csp); //Nebula only reports to the console

				//Permissions Policy: https://scotthelme.co.uk/goodbye-feature-policy-and-hello-permissions-policy/ and https://caniuse.com/#feat=feature-policy and https://caniuse.com/permissions-policy
			}
		}

		//Log direct access to templates and prevent certain query strings
		public function bad_access_prevention(){
			//Log template direct access attempts
			if ( array_key_exists('ndaat', $this->super->get) ){
				$this->ga_send_exception('(Security) Direct Template Access Prevention on ' . $this->super->get['ndaat'], 0, array('cd' . $this->ga_definition_index($this->get_option('cd_securitynote')) => 'Direct Template Access Attempt'));
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

			if ( !empty($error) ){
				$dimensions = array('dl'=> wp_login_url(), 'dt' => 'Log In');

				if ( $this->contains($error, array('The password you entered for the username')) ){
					$incorrect_username_start = strpos($error, 'for the username ')+17;
					$incorrect_username_stop = strpos($error, ' is incorrect')-$incorrect_username_start;
					$incorrect_username = strip_tags(substr($error, $incorrect_username_start, $incorrect_username_stop));
					$this->ga_send_exception('(Security) Login error (incorrect password) for user ' . $incorrect_username, 0, $dimensions);
				} else {
					if ( $this->contains($error, array('Invalid username')) && $this->get_option('cd_securitynote') ){ //If no username was entered, tag the user as a potential bot
						$dimensions['cd' . $this->ga_definition_index($this->get_option('cd_securitynote'))] = 'Possible Bot';
					}
					$this->ga_send_exception('(Security) Login error: ' . strip_tags($error), 0, $dimensions);
				}

				if ( !$this->is_bot() ){
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
		function track_notable_bots(){
			$override = apply_filters('pre_track_notable_bots', null);
			if ( isset($override) ){return;}

			//Ignore users who have already been checked, or are logged in.
			if ( is_user_logged_in() ){
				return false;
			}

			//Google Page Speed
			if ( strpos($this->super->server['HTTP_USER_AGENT'], 'Google Page Speed') !== false ){
				if ( $this->url_components('extension') !== 'js' ){
					global $post;
				}
			}

			//Internet Archive Wayback Machine
			if ( strpos($this->super->server['HTTP_USER_AGENT'], 'archive.org_bot') !== false || strpos($this->super->server['HTTP_USER_AGENT'], 'Wayback Save Page') !== false ){
				global $post;
			}
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
						$this->ga_send_exception('(Security) Spam domain prevented. Referrer: ' . $this->super->server['HTTP_REFERER'], 1, array('cd' . $this->ga_definition_index($this->get_option('cd_securitynote')) => 'Spam Referrer'));
						do_action('nebula_spambot_prevention');
						header('HTTP/1.1 403 Forbidden');
						wp_die();
					}

					if ( isset($this->super->server['REMOTE_HOST']) && $this->contains(strtolower($this->super->server['REMOTE_HOST']), $spam_domain_array) ){
						$this->ga_send_exception('(Security) Spam domain prevented. Hostname: ' . $this->super->server['REMOTE_HOST'], 1, array('cd' . $this->ga_definition_index($this->get_option('cd_securitynote')) => 'Spam Hostname'));
						do_action('nebula_spambot_prevention');
						header('HTTP/1.1 403 Forbidden');
						wp_die();
					}

					if ( isset($this->super->server['SERVER_NAME']) && $this->contains(strtolower($this->super->server['SERVER_NAME']), $spam_domain_array) ){
						$this->ga_send_exception('(Security) Spam domain prevented. Server Name: ' . $this->super->server['SERVER_NAME'], 1, array('cd' . $this->ga_definition_index($this->get_option('cd_securitynote')) => 'Spam Server Name'));
						do_action('nebula_spambot_prevention');
						header('HTTP/1.1 403 Forbidden');
						wp_die();
					}

					if ( isset($ip_address) && $this->contains(strtolower(gethostbyaddr($ip_address)), $spam_domain_array) ){
						$this->ga_send_exception('(Security) Spam domain prevented. Network Hostname: ' . $ip_address, 1, array('cd' . $this->ga_definition_index($this->get_option('cd_securitynote')) => 'Spam Network Hostname'));
						do_action('nebula_spambot_prevention');
						header('HTTP/1.1 403 Forbidden');
						wp_die();
					}
				} else {
					$this->ga_send_exception('(Security) spammers.txt has no entries!', 0);
				}

				$this->set_cookie('spam_domain', false);
			}

			$this->timer('Spam Domain Prevention', 'end');
		}

		//Return an array of spam domains from Matomo (or the latest Nebula on GitHub)
		public function get_spam_domain_list(){
			$spam_domain_json_file = get_template_directory() . '/inc/data/spam_domain_list.txt';

			//Potential candidate for new Nebula transient() function
			$spam_domain_list = get_transient('nebula_spam_domain_list');
			if ( empty($spam_domain_list) || $this->is_debug() ){ //If transient expired or is debug
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
					$wp_filesystem->put_contents($spam_domain_json_file, $spam_domain_list);
					set_transient('nebula_spam_domain_list', $spam_domain_list, HOUR_IN_SECONDS*36);
				}
			}

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
				$this->ga_send_exception('(Security) spammers.txt was not available!', 0);
			}

			//Add manual and user-added spam domains
			$manual_nebula_spam_domains = array(
				'bitcoinpile.com',
			);
			$all_spam_domains = apply_filters('nebula_spam_domains', $manual_nebula_spam_domains);
			return array_merge($spam_domain_array, $all_spam_domains);
		}

		//Cookie Notification HTML that appears in the footer
		public function cookie_notification(){
			if ( $this->option('cookie_notification') && empty($this->super->cookie['acceptcookies']) ){
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