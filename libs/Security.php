<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Security') ){
	trait Security {
		public function hooks(){
			//Log template direct access attempts
			add_action('wp_loaded', array($this, 'log_direct_access_attempts'));

			//Prevent known bot/brute-force query strings.
			//This is less for security and more for preventing garbage data in Google Analytics reports.
			add_action('wp_loaded', array($this, 'prevent_bad_query_strings'));

			//Disable Pingbacks to prevent security issues
			//Disable X-Pingback HTTP Header.
			add_filter('wp_headers', array($this, 'remove_x_pingback'), 11, 2);

			//Hijack pingback_url for get_bloginfo (<link rel="pingback" />).
			add_filter('bloginfo_url', array($this, 'hijack_pingback_url'), 11, 2);

			//Disable XMLRPC
			add_filter('xmlrpc_enabled', '__return_false');
			remove_action('wp_head', 'rsd_link');
			remove_action('wp_head', 'wlwmanifest_link');
			add_filter('pre_option_enable_xmlrpc', array($this, 'disable_xmlrpc'));

			//Remove rsd_link from filters (<link rel="EditURI" />).
			add_action('wp', array($this, 'remove_rsd_link'), 9);

			//Prevent login error messages from giving too much information
			add_filter('login_errors', array($this, 'login_errors'));

			//Remove Wordpress version info from head and feeds
			add_filter('the_generator', array($this, 'complete_version_removal'));

			//Remove WordPress version from any enqueued scripts
			add_filter('style_loader_src', array($this, 'at_remove_wp_ver_css_js' ), 9999);
			add_filter('script_loader_src', array($this, 'at_remove_wp_ver_css_js' ), 9999);

			//Check referrer in order to comment
			add_action('check_comment_flood', array($this, 'check_referrer'));

			//Track Notable Bots
			add_action('wp_footer', array($this, 'track_notable_bots'));

			//Check referrer for known spambots and blacklisted domains
			//Traffic will be sent a 403 Forbidden error and never be able to see the site.
			//Be sure to enable Bot Filtering in your Google Analytics account (GA Admin > View Settings > Bot Filtering).
			//Sometimes spambots target sites without actually visiting. Discovering these and filtering them using GA is important too!
			//Learn more: http://gearside.com/stop-spambots-like-semalt-buttons-website-darodar-others/
			add_action('wp_loaded', array($this, 'domain_prevention'));

			//Disable the file editor for non-developers
			if ( !$this->is_dev() ){
				define('DISALLOW_FILE_EDIT', true);
			}
		}

		//Log template direct access attempts
		public function log_direct_access_attempts(){
			if ( array_key_exists('ndaat', $_GET) ){
				$this->ga_send_event('Security Precaution', 'Direct Template Access Prevention', 'Template: ' . $_GET['ndaat']);
				header('Location: ' . home_url('/'));
				die('Error 403: Forbidden.');
			}
		}

		//Prevent known bot/brute-force query strings.
		public function prevent_bad_query_strings(){
			if ( array_key_exists('modTest', $_GET) ){
				header("HTTP/1.1 403 Unauthorized");
				die('Error 403: Forbidden.');
			}
		}

		//Disable Pingbacks to prevent security issues
		public function remove_x_pingback($headers){
			$override = do_action('pre_nebula_remove_x_pingback', $headers);
			if ( !empty($override) ){return $override;}

			if ( isset($headers['X-Pingback']) ){
				unset($headers['X-Pingback']);
			}
			return $headers;
		}

		//Hijack pingback_url for get_bloginfo (<link rel="pingback" />).
		public function hijack_pingback_url($output, $property){
			$override = do_action('pre_nebula_hijack_pingback_url', $output, $property);
			if ( !empty($override) ){return $override;}

			return ( $property == 'pingback_url' )? null : $output;
		}

		//Disable XMLRPC
		public function disable_xmlrpc($state){
			return false;
		}

		//Remove rsd_link from filters (<link rel="EditURI" />).
		public function remove_rsd_link(){
			remove_action('wp_head', 'rsd_link');
		}

		//Prevent login error messages from giving too much information
		public function login_errors($error){
			$override = do_action('pre_nebula_login_errors', $error);
			if ( !empty($override) ){return $override;}

			if ( !$this->is_bot() ){
				$incorrect_username = '';
				if ( $this->contains($error, array('The password you entered for the username')) ){
					$incorrect_username_start = strpos($error, 'for the username ')+17;
					$incorrect_username_stop = strpos($error, ' is incorrect')-$incorrect_username_start;
					$incorrect_username = strip_tags(substr($error, $incorrect_username_start, $incorrect_username_stop));
				}

				if ( !empty($incorrect_username) ){
					$this->ga_send_event('Login Error', 'Attempted User: ' . $incorrect_username, 'IP: ' . $_SERVER['REMOTE_ADDR']);
				} else {
					$this->ga_send_event('Login Error', strip_tags($error), 'IP: ' . $_SERVER['REMOTE_ADDR']);
				}

				$error = 'Login Error.';
				return $error;
			}
		}

		//Remove Wordpress version info from head and feeds
		public function complete_version_removal(){
			return '';
		}

		//Remove WordPress version from any enqueued scripts
		public function at_remove_wp_ver_css_js($src){
			$override = do_action('pre_at_remove_wp_ver_css_js', $src);
			if ( !empty($override) ){return $override;}

			if ( strpos($src, 'ver=') ){
				$src = remove_query_arg('ver', $src);
			}

			return $src;
		}

		//Check referrer in order to comment
		public function check_referrer(){
			if ( !isset($_SERVER['HTTP_REFERER']) || empty($_SERVER['HTTP_REFERER']) ){
				wp_die('Please do not access this file directly.');
			}
		}

		//Track Notable Bots
		function track_notable_bots(){
			$override = do_action('pre_track_notable_bots');
			if ( !empty($override) ){return;}

			//Google Page Speed
			if ( strpos($_SERVER['HTTP_USER_AGENT'], 'Google Page Speed') !== false ){
				if ( $this->url_components('extension') != 'js' ){
					global $post;
					$this->ga_send_event('Notable Bot Visit', 'Google Page Speed', get_the_title($post->ID), null, 0);
				}
			}

			//Internet Archive Wayback Machine
			if ( strpos($_SERVER['HTTP_USER_AGENT'], 'archive.org_bot') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'Wayback Save Page') !== false ){
				global $post;
				$this->ga_send_event('Notable Bot Visit', 'Internet Archive Wayback Machine', get_the_title($post->ID), null, 0);
			}
		}

		//Check referrer for known spambots and blacklisted domains
		public function domain_prevention(){
			if ( isset($_SESSION['blacklisted']) && $_SESSION['blacklisted'] == false ){
				return false;
			}

			if ( $this->get_option('domain_blacklisting') ){
				$blacklisted_domains = $this->get_domain_blacklist();

				if ( count($blacklisted_domains) > 1 ){
					if ( isset($_SERVER['HTTP_REFERER']) && $this->contains(strtolower($_SERVER['HTTP_REFERER']), $blacklisted_domains) ){
						$this->ga_send_event('Security Precaution', 'Blacklisted Domain Prevented', 'Referring Domain: ' . $_SERVER['HTTP_REFERER'] . ' (IP: ' . $_SERVER['REMOTE_ADDR'] . ')');
						do_action('nebula_spambot_prevention');
						header('HTTP/1.1 403 Forbidden');
						die;
					}
					if ( isset($_SERVER['REMOTE_HOST']) && $this->contains(strtolower($_SERVER['REMOTE_HOST']), $blacklisted_domains) ){
						$this->ga_send_event('Security Precaution', 'Blacklisted Domain Prevented', 'Hostname: ' . $_SERVER['REMOTE_HOST'] . ' (IP: ' . $_SERVER['REMOTE_ADDR'] . ')');
						do_action('nebula_spambot_prevention');
						header('HTTP/1.1 403 Forbidden');
						die;
					}
					if ( isset($_SERVER['SERVER_NAME']) && $this->contains(strtolower($_SERVER['SERVER_NAME']), $blacklisted_domains) ){
						$this->ga_send_event('Security Precaution', 'Blacklisted Domain Prevented', 'Server Name: ' . $_SERVER['SERVER_NAME'] . ' (IP: ' . $_SERVER['REMOTE_ADDR'] . ')');
						do_action('nebula_spambot_prevention');
						header('HTTP/1.1 403 Forbidden');
						die;
					}
					if ( isset($_SERVER['REMOTE_ADDR']) && $this->contains(strtolower(gethostbyaddr($_SERVER['REMOTE_ADDR'])), $blacklisted_domains) ){
						$this->ga_send_event('Security Precaution', 'Blacklisted Domain Prevented', 'Network Hostname: ' . $_SERVER['SERVER_NAME'] . ' (IP: ' . $_SERVER['REMOTE_ADDR'] . ')');
						do_action('nebula_spambot_prevention');
						header('HTTP/1.1 403 Forbidden');
						die;
					}
				} else {
					$this->ga_send_event('Security Precaution', 'Error', 'spammers.txt has no entries!');
				}

				$this->set_global_session_cookie('blacklist', false, array('session'));
			}
		}

		//Return an array of blacklisted domains from Piwik (or the latest Nebula on Github)
		public function get_domain_blacklist(){
			$domain_blacklist_json_file = get_template_directory() . '/inc/data/domain_blacklist.txt';
			$domain_blacklist = get_transient('nebula_domain_blacklist');
			if ( empty($domain_blacklist) || $this->is_debug() ){ //If transient expired or is debug
				$response = $this->remote_get('https://raw.githubusercontent.com/piwik/referrer-spam-blacklist/master/spammers.txt');
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
				$this->ga_send_event('Security Precaution', 'Error', 'spammers.txt was not available!');
			}

			//Add manual and user-added blacklisted domains
			$manual_nebula_blacklisted_domains = array(
				'bitcoinpile.com',
			);
			$additional_blacklisted_domains = apply_filters('nebula_blacklisted_domains', array());
			$all_blacklisted_domains = array_merge($manual_nebula_blacklisted_domains, $additional_blacklisted_domains);

			return array_merge($blacklisted_domains, $all_blacklisted_domains);
		}
	}
}