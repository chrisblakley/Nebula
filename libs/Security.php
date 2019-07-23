<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Security') ){
	trait Security {
		public function hooks(){
			add_action('wp_loaded', array($this, 'bad_access_prevention'));
			add_filter('wp_headers', array($this, 'remove_x_pingback'), 11, 2);
			add_filter('bloginfo_url', array($this, 'hijack_pingback_url'), 11, 2);
			add_action('wp_head', array($this, 'security_headers')); //@todo "Nebula" 0: try using 'send_headers' hook instead?
			remove_action('wp_head', 'wlwmanifest_link');
			add_filter('login_errors', array($this, 'login_errors'));
			add_filter('the_generator', '__return_empty_string'); //Remove Wordpress version info from head and feeds
			add_filter('style_loader_src', array($this, 'at_remove_wp_ver_css_js'), 9999);
			add_filter('script_loader_src', array($this, 'at_remove_wp_ver_css_js'), 9999);
			add_action('check_comment_flood', array($this, 'check_referrer'));
			//add_action('wp_footer', array($this, 'track_notable_bots')); //Disabled for now. Not super useful.
			add_action('wp_loaded', array($this, 'domain_prevention'));
			add_action('get_header', array($this, 'redirect_author_template'));
			add_filter('rest_endpoints', array($this, 'rest_endpoints_security'));

			//Disable the file editor for non-developers
			if ( !$this->is_dev() ){
				define('DISALLOW_FILE_EDIT', true);
			}
		}

		//Additional security headers.
		//Test with https://securityheaders.io/
		public function security_headers(){
			header('x-frame-options: SAMEORIGIN');
			header('X-XSS-Protection: 1; mode=block');
			header('X-Content-Type-Options: nosniff');

			if ( is_ssl() ){
				header('Strict-Transport-Security: max-age=' . YEAR_IN_SECONDS . '; includeSubDomains; preload'); //https://scotthelme.co.uk/hsts-the-missing-link-in-tls/
				header('Referrer-Policy: no-referrer-when-downgrade'); //https://scotthelme.co.uk/a-new-security-header-referrer-policy/

				//https://scotthelme.co.uk/hardening-your-http-response-headers/
				//header(''); //@TODO "Nebula" 0: Upcoming spec - https://scotthelme.co.uk/a-new-security-header-expect-ct/
			}
		}

		//Log direct access to templates and prevent certain query strings
		public function bad_access_prevention(){
			//Log template direct access attempts
			if ( array_key_exists('ndaat', $_GET) ){
				$this->ga_send_exception('(Security) Direct Template Access Prevention on ' . $_GET['ndaat'], 0, array('cd' . $this->ga_definition_index($this->get_option('cd_securitynote')) => 'Direct Template Access Attempt'));
				header('Location: ' . home_url('/'));
				exit;
			}

			//Prevent known bot/brute-force query strings.
			if ( array_key_exists('modTest', $_GET) ){
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

		//Remove WordPress version from any enqueued scripts
		public function at_remove_wp_ver_css_js($src){
			$override = apply_filters('pre_at_remove_wp_ver_css_js', null, $src);
			if ( isset($override) ){return $override;}

/*
			if ( strpos($src, 'ver=') ){
				$src = remove_query_arg('ver', $src);
			}
*/

			return $src;
		}

		//Check referrer in order to comment
		public function check_referrer(){
			if ( !isset($_SERVER['HTTP_REFERER']) || empty($_SERVER['HTTP_REFERER']) ){
				wp_die('Please do not access this file directly.');
			}
		}

		//Disable author archives to prevent ?author=1 from showing usernames.
		public function redirect_author_template(){
			if ( (isset($_GET['author']) || basename($this->current_theme_template) == 'author.php') && !nebula()->get_option('author_bios') ){
				wp_redirect(apply_filters('nebula_no_author_redirect', home_url('/') . '?s=about'));
				exit;
			}
		}

		//Manage what is exposed in the REST API
		public function rest_endpoints_security($endpoints){
			//Disable the /users endpoint if author bios is disabled
			if ( !$this->get_option('author_bios') ){
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
			if ( strpos($_SERVER['HTTP_USER_AGENT'], 'Google Page Speed') !== false ){
				if ( $this->url_components('extension') !== 'js' ){
					global $post;
					//$this->ga_send_event('Notable Bot Visit', 'Google Page Speed', get_the_title($post->ID), null, 0);
				}
			}

			//Internet Archive Wayback Machine
			if ( strpos($_SERVER['HTTP_USER_AGENT'], 'archive.org_bot') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'Wayback Save Page') !== false ){
				global $post;
				//$this->ga_send_event('Notable Bot Visit', 'Internet Archive Wayback Machine', get_the_title($post->ID), null, 0);
			}
		}

		//Check referrer for known spambots and blacklisted domains
		public function domain_prevention(){
			$this->timer('Domain Blacklist');

			//Skip lookups if user has already been checked or for logged in users.
			if ( (isset($_SESSION['blacklisted']) && $_SESSION['blacklisted'] === false) || is_user_logged_in() ){
				return false;
			}

			if ( $this->get_option('domain_blacklisting') ){
				$blacklisted_domains = $this->get_domain_blacklist();
				$ip_address = $this->get_ip_address();

				if ( count($blacklisted_domains) > 1 ){
					if ( isset($_SERVER['HTTP_REFERER']) && $this->contains(strtolower($_SERVER['HTTP_REFERER']), $blacklisted_domains) ){
						$this->ga_send_exception('(Security) Blacklisted domain prevented. Referrer: ' . $_SERVER['HTTP_REFERER'], 1, array('cd' . $this->ga_definition_index($this->get_option('cd_securitynote')) => 'Blacklisted Referrer'));
						do_action('nebula_spambot_prevention');
						header('HTTP/1.1 403 Forbidden');
						wp_die();
					}

					if ( isset($_SERVER['REMOTE_HOST']) && $this->contains(strtolower($_SERVER['REMOTE_HOST']), $blacklisted_domains) ){
						$this->ga_send_exception('(Security) Blacklisted domain prevented. Hostname: ' . $_SERVER['REMOTE_HOST'], 1, array('cd' . $this->ga_definition_index($this->get_option('cd_securitynote')) => 'Blacklisted Hostname'));
						do_action('nebula_spambot_prevention');
						header('HTTP/1.1 403 Forbidden');
						wp_die();
					}

					if ( isset($_SERVER['SERVER_NAME']) && $this->contains(strtolower($_SERVER['SERVER_NAME']), $blacklisted_domains) ){
						$this->ga_send_exception('(Security) Blacklisted domain prevented. Server Name: ' . $_SERVER['SERVER_NAME'], 1, array('cd' . $this->ga_definition_index($this->get_option('cd_securitynote')) => 'Blacklisted Server Name'));
						do_action('nebula_spambot_prevention');
						header('HTTP/1.1 403 Forbidden');
						wp_die();
					}

					if ( isset($ip_address) && $this->contains(strtolower(gethostbyaddr($ip_address)), $blacklisted_domains) ){
						$this->ga_send_exception('(Security) Blacklisted domain prevented. Network Hostname: ' . $ip_address, 1, array('cd' . $this->ga_definition_index($this->get_option('cd_securitynote')) => 'Blacklisted Network Hostname'));
						do_action('nebula_spambot_prevention');
						header('HTTP/1.1 403 Forbidden');
						wp_die();
					}
				} else {
					$this->ga_send_exception('(Security) spammers.txt has no entries!', 0);
				}

				$this->set_global_session_cookie('blacklist', false, array('session'));
			}

			$this->timer('Domain Blacklist', 'end');
		}

		//Return an array of blacklisted domains from Matomo (or the latest Nebula on Github)
		public function get_domain_blacklist(){
			$domain_blacklist_json_file = get_template_directory() . '/inc/data/domain_blacklist.txt';
			$domain_blacklist = get_transient('nebula_domain_blacklist');
			if ( empty($domain_blacklist) || $this->is_debug() ){ //If transient expired or is debug
				$response = $this->remote_get('https://raw.githubusercontent.com/matomo-org/referrer-spam-blacklist/master/spammers.txt');
				if ( !is_wp_error($response) ){
					$domain_blacklist = $response['body'];
				}

				//If there was an error or empty response, try my Github repo
				if ( is_wp_error($response) || empty($domain_blacklist) ){ //This does not check availability because it is the same hostname as above.
					$response = $this->remote_get('https://raw.githubusercontent.com/chrisblakley/Nebula/master/inc/data/domain_blacklist.txt');
					if ( !is_wp_error($response) ){
						$domain_blacklist = $response['body'];
					}
				}

				//If either of the above remote requests received data, update the local file and store the data in a transient for 24 hours
				if ( !is_wp_error($response) && !empty($domain_blacklist) ){
					WP_Filesystem();
					global $wp_filesystem;
					$wp_filesystem->put_contents($domain_blacklist_json_file, $domain_blacklist);
					set_transient('nebula_domain_blacklist', $domain_blacklist, HOUR_IN_SECONDS*36);
				}
			}

			//If neither remote resource worked, get the local file
			if ( empty($domain_blacklist) ){
				WP_Filesystem();
				global $wp_filesystem;
				$domain_blacklist = $wp_filesystem->get_contents($domain_blacklist_json_file);
			}

			//If one of the above methods worked, parse the data.
			if ( !empty($domain_blacklist) ){
				$blacklisted_domains = array();
				foreach( explode("\n", $domain_blacklist) as $line ){
					if ( !empty($line) ){
						$blacklisted_domains[] = $line;
					}
				}
			} else {
				$this->ga_send_exception('(Security) spammers.txt was not available!', 0);
			}

			//Add manual and user-added blacklisted domains
			$manual_nebula_blacklisted_domains = array(
				'bitcoinpile.com',
			);
			$all_blacklisted_domains = apply_filters('nebula_blacklisted_domains', $manual_nebula_blacklisted_domains);
			return array_merge($blacklisted_domains, $all_blacklisted_domains);
		}
	}
}