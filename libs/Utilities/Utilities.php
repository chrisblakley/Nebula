<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Utilities') ){
	require_once get_template_directory() . '/libs/Utilities/Logs.php';
	require_once get_template_directory() . '/libs/Utilities/Analytics.php';
	require_once get_template_directory() . '/libs/Utilities/Device.php';
	require_once get_template_directory() . '/libs/Utilities/Sass.php';
	require_once get_template_directory() . '/libs/Utilities/Warnings.php';

	trait Utilities {
		use Logs { Logs::hooks as LogsHooks;}
		use Analytics { Analytics::hooks as AnalyticsHooks;}
		use Device { Device::hooks as DeviceHooks;}
		use Sass { Sass::hooks as SassHooks;}
		use Warnings { Warnings::hooks as WarningsHooks;}

		public $server_timings = array();

		public function hooks(){
			add_filter('posts_where', array($this, 'fuzzy_posts_where'));
			$this->LogsHooks(); //Register Logs hooks
			$this->AnalyticsHooks(); //Register Analytics hooks
			$this->DeviceHooks(); //Register Device hooks
			$this->SassHooks(); //Register Sass hooks

			if ( (is_user_logged_in() || $this->is_auditing()) && !$this->is_background_request() && !is_customize_preview() ){
				$this->WarningsHooks(); //Register Warnings hooks
			}

			//Update the child theme version number at various points
			if ( is_user_logged_in() ){
				add_action('save_post', array($this, 'update_child_version_number')); //When a post is created or updated
				add_action('nebula_options_saved', array($this, 'update_child_version_number')); //Nebula ptions save
				add_action('upgrader_process_complete', array($this, 'update_child_version_number')); //WordPress Core, theme, or plugin updates
				add_action('nebula_scss_post_compile_once', array($this, 'update_child_version_number'));
			}
		}

		//Attempt to get the most accurate IP address from the visitor
		public function get_ip_address($anonymize=true){
			$ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
			foreach ( $ip_keys as $key ){
				if ( array_key_exists($key, $this->super->server) === true ){
					foreach ( explode(',', $this->super->server[$key]) as $ip ){
						$ip = trim($ip);

						if ( filter_var($ip, FILTER_VALIDATE_IP) ){ //Validate IP
							return ( !empty($anonymize) )? wp_privacy_anonymize_ip($ip) : $ip; //Return the exact or anonymized IP address
						}
					}
				}
			}

			return false;
		}

		//Check if Nebula is the active (parent) theme
		public function is_nebula(){
			if ( (wp_get_theme()->get('Name') === 'Nebula' || wp_get_theme()->get('Name') === 'Nebula Child') && class_exists('Nebula') ){
				return true;
			}

			$parent_theme = wp_get_theme()->parent();
			if ( !empty($parent_theme) && $parent_theme->get('Name') === 'Nebula' && class_exists('Nebula') ){
				return true;
			}

			return false;
		}

		//Generate Nebula Session ID
		public function nebula_session_id(){
			$cache_group = uniqid(); //Each "user" gets its own group so it persists without interfering with each other

			//Check object cache first
			$session_id = wp_cache_get('nebula_session_id', $cache_group); //If session_id() is not available, it will re-generate the Nebula session ID
			if ( !empty($session_id) ){
				return sanitize_text_field($session_id);
			}

			$timer_name = $this->timer('Session ID');
			$session_data = array();

			//Time
			$session_data['t'] = time();

			//Debug
			if ( $this->is_debug() ){
				$session_data['d'] = true;
			}

			//Client/Developer
			if ( $this->is_client() ){
				$session_data['cli'] = true;
			}
			if ( $this->is_dev() ){
				$session_data['dev'] = true;
			}

			//Logged in user role
			if ( is_user_logged_in() ){
				$user_info = get_userdata(get_current_user_id());

				$session_data['r'] = 'unknown';
				if ( !empty($user_info->roles) ){
					$session_data['r'] = $user_info->roles[0];
				}

				$session_data['uid'] = get_current_user_id();
			}

			//Bot detection
			if ( $this->is_bot() ){
				$session_data['bot'] = true;
			}

			//Site Live
			if ( !$this->is_site_live() ){
				$session_data['l'] = false;
			}

			//Session ID
			$session_data['s'] = $cache_group; //Use the unique ID that determines the group as the main ID

			//Google Analytics CID
			$session_data['cid'] = $this->ga_parse_cookie();

			//Additional session information
			$all_session_data = apply_filters('nebula_session_id', $session_data);

			//Convert to a string
			$session_id = '';
			foreach ( $all_session_data as $key => $value ){
				$session_id .= $key . ':' . $value . ';';
			}

			//do_action('qm/info', 'Nebula Session ID: ' . $session_id);
			wp_cache_set('nebula_session_id', $session_id, $cache_group); //Store in object cache grouped by the unique ID to prevent interference
			$this->timer($timer_name, 'end');
			return sanitize_text_field($session_id);
		}

		//Check if currently viewing an admin page (or the Customizer)
		public function is_admin_page($include_customizer=false, $include_login=false){
			if ( is_admin() ){
				return true;
			}

			if ( $include_customizer && is_customize_preview() ){
				return true;
			}

			if ( $include_login && $this->is_login_page() ){
				return true;
			}

			return false;
		}

		//Check if viewing the login page.
		public function is_login_page(){
			if ( in_array($this->super->globals['pagenow'], array('wp-login.php', 'wp-register.php')) || $this->super->server['PHP_SELF'] == '/wp-login.php' ){
				return true;
			}

			$abspath = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, ABSPATH);
			$included_files = get_included_files();
			if ( in_array($abspath . 'wp-login.php', $included_files) || in_array($abspath . 'wp-register.php', $included_files) ){
				return true;
			}

			return false;
		}

		//Format phone numbers into the preferred (315) 478-6700 format.
		public function phone_format($number=false){
			if ( !empty($number) ){
				return preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3', $number);
			}
			return $number;
		}

		//Check if the current IP address matches any of the dev IP address from Nebula Options
		//Passing $strict bypasses IP check, so user must be a dev and logged in.
		//Note: This should not be used for security purposes since IP addresses can be spoofed.
		public function is_dev($strict=false){
			$override = apply_filters('pre_is_dev', null, $strict);
			if ( isset($override) ){return $override;}

			if ( empty($strict) ){
				$dev_ips = ( !empty($this->get_option('dev_ip')) )? $this->get_option('dev_ip') : '';
				$dev_ips = explode(',', $dev_ips);
				if ( !empty($dev_ips) ){
					foreach ( $dev_ips as $dev_ip ){
						$dev_ip = wp_privacy_anonymize_ip(trim($dev_ip));

						if ( !empty($dev_ip) && $dev_ip[0] !== '/' && $dev_ip === $this->get_ip_address() ){
							return true;
						}

						if ( !empty($dev_ip) && $dev_ip[0] === '/' && preg_match($dev_ip, $this->get_ip_address()) ){
							return true;
						}
					}
				}
			}

			//Check if the current user's email domain matches any of the dev email domains from Nebula Options
			if ( is_user_logged_in() ){
				$current_user = wp_get_current_user();
				if ( !empty($current_user->user_email) ){
					$current_user_domain = explode('@', $current_user->user_email)[1];

					$dev_email_domains = ( $this->get_option('dev_email_domain') )? $this->get_option('dev_email_domain') : ''; //Ensure correct type
					$dev_email_domains = explode(',', $dev_email_domains);
					foreach ( $dev_email_domains as $dev_email_domain ){
						if ( trim($dev_email_domain) === $current_user_domain ){
							return true;
						}
					}
				}
			}

			return false;
		}

		//Check if the current IP address matches any of the client IP address from Nebula Options
		//Passing $strict bypasses IP check, so user must be a client and logged in.
		//Note: This should not be used for security purposes since IP addresses can be spoofed.
		public function is_client($strict=false){
			$override = apply_filters('pre_is_client', null, $strict);
			if ( isset($override) ){return $override;}

			if ( empty($strict) ){
				$client_ips = ( !empty($this->get_option('client_ip')) )? $this->get_option('client_ip') : '';
				$client_ips = explode(',', $client_ips);
				if ( !empty($client_ips) ){
					foreach ( $client_ips as $client_ip ){
						$client_ip = wp_privacy_anonymize_ip(trim($client_ip));

						if ( !empty($client_ip) && $client_ip[0] !== '/' && $client_ip === $this->get_ip_address() ){
							return true;
						}

						if ( !empty($client_ip) && $client_ip[0] === '/' && preg_match($client_ip, $this->get_ip_address()) ){
							return true;
						}
					}
				}
			}

			//Check if the current user's email domain matches any of the client email domains from Nebula Options
			if ( is_user_logged_in() ){
				$current_user = wp_get_current_user();
				if ( !empty($current_user->user_email) ){
					$current_user_domain = explode('@', $current_user->user_email)[1];

					$client_email_domains = ( $this->get_option('client_email_domain') )? $this->get_option('client_email_domain') : ''; //Ensure correct type
					$client_email_domains = explode(',', $client_email_domains);
					foreach ( $client_email_domains as $client_email_domain ){
						if ( trim($client_email_domain) === $current_user_domain ){
							return true;
						}
					}
				}
			}

			return false;
		}

		//Get the role (and dev/client designation)
		public function user_role($staff_info=true){
			$usertype = '';
			if ( is_user_logged_in() ){
				$user_info = get_userdata(get_current_user_id());
				$usertype = 'Unknown';
				if ( !empty($user_info->roles) ){
					$usertype = ( is_multisite() && is_super_admin() )? 'Super Admin' : ucwords($user_info->roles[0]);
				}
			}

			$staff = '';
			if ( $staff_info ){
				if ( $this->is_dev() ){
					$staff = ' (Developer)';
				} elseif ( $this->is_client() ){
					$staff = ' (Client)';
				}
			}

			return $usertype . $staff;
		}

		//Check if the current IP address or logged-in user is a developer or client.
		//Note: This does not account for user role (An admin could return false here). Check role separately.
		public function is_staff($strict=false){
			if ( $this->is_dev($strict) || $this->is_client($strict) ){
				return true;
			}

			return false;
		}

		//Check if user is using the debug query string.
		//$strict requires the user to be a developer or client. Passing 2 to $strict requires the dev or client to be logged in too.
		public function is_debug($strict=false){
			$override = apply_filters('pre_is_debug', null, $strict);
			if ( isset($override) ){return $override;}

			$very_strict = ( $strict > 1 )? $strict : false;
			if ( array_key_exists('debug', $this->super->get) ){
				if ( !empty($strict) ){
					if ( $this->is_dev($very_strict) || $this->is_client($very_strict) ){
						return true;
					}
					return false;
				}
				return true;
			}
			return false;
		}

		//If Nebula Safe Mode is currently active
		public function is_safe_mode(){
			//Check if nebula-safe-mode.php is active
			if ( file_exists(WPMU_PLUGIN_DIR . '/nebula-safe-mode.php') ){
				return true;
			}

			return false;
		}

		//If the current pageload is requested with more advanced detections
		public function is_auditing(){
			if ( is_customize_preview() ){
				return false;
			}

			if ( $this->is_admin_page() ){
				return false;
			}

			if ( $this->get_option('audit_mode') || isset($this->super->get['audit']) ){
				if ( current_user_can('manage_options') || $this->is_dev() || $this->is_client() ){
					return true;
				}
			}

			return false;
		}

		//If we should bypass the caches (if/when possible)
		public function is_bypass_cache(){
			//During debug, auditing, or when Customizer is saved/closed (yes, this global exists when closing Customizer without saving too)
			if ( $this->is_debug() || $this->is_auditing() || !empty($this->super->globals['wp_customize']) || isset($this->super->get['nocache']) ){
				return true;
			}

			if ( defined('DONOTCACHEPAGE') && !empty(DONOTCACHEPAGE) ){
				return true;
			}

			return false;
		}

		//Check if the current site is live to the public.
		//Note: This checks if the hostname of the home URL matches any of the valid hostnames.
		//If the Valid Hostnames option is empty, this will return true as it is unknown.
		public function is_site_live(){
			$override = apply_filters('pre_is_site_live', null);
			if ( isset($override) ){return $override;}

			if ( $this->get_option('hostnames') ){
				if ( strpos($this->get_option('hostnames'), $this->url_components('hostname', home_url())) >= 0 ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
					return true;
				}
				return false;
			}
			return true;
		}

		//Check if the current page loaded is a tagged campaign (UTMs, etc.)
		public function is_campaign_page($url=false){
			$query_string = ( !empty($url) )? $url : $this->url_components('query'); //Use the provided URL otherwise check just the query string
			$notable_tags = array('utm_', 'fbclid', 'gclid', 'gclsrc', 'dclid', 'gbraid', 'wbraid', 'mc_eid', '_hsenc', 'vero_id', 'mkt_tok');

			foreach ( $notable_tags as $tag ){
				if ( strpos(strtolower($query_string), $tag) > -1 ){ //If UTM parameters exist //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
					return true; //This is a tagged campaign page (return true as soon as any match)
				}
			}

			return false; //This is not a tagged campaign page
		}

		//Check if the site is an ecommerce website
		public function is_ecommerce(){
			if ( is_plugin_active('woocommerce/woocommerce.php') ){
				return true;
			}

			return false;
		}

		//Valid Hostname Regex
		//Enter ONLY the domain and TLD. The wildcard subdomain regex is automatically added.
		public function valid_hostname_regex($domains=null){
			$domains = ( !empty($domains) && is_array($domains) )? $domains : array($this->url_components('domain')); //If a domain is not passed, use the current domain

			//Add hostnames from Nebula Options
			if ( $this->get_option('hostnames') ){
				$domains = array_merge($domains, explode(',', str_replace(' ', '', esc_html($this->get_option('hostnames')))));
			}

			$domains = array_merge($domains, array('googleusercontent.com', 'googleweblight.com')); //Add default safe valid domains to the list
			$all_domains = apply_filters('hostname_regex', $domains); //Allow other functions/plugins to add/remove domains from the array

			$all_domains = preg_filter('/^/', '.*', $all_domains); //Add wildcard prefix to all domains
			$all_domains = str_replace(array(' ', '.', '-'), array('', '\.', '\-'), $all_domains); //Final regex should be: \.*gearside\.com|\.*gearsidecreative\.com
			$all_domains = array_unique($all_domains); //De-dupe the domain list

			return implode("|", $all_domains); //Return a valid hostname regex string
		}

		//Get the full URL. Not intended for secure use ($_SERVER var can be manipulated by client/server).
		public function requested_url($host="HTTP_HOST"){ //Can use "SERVER_NAME" as an alternative to "HTTP_HOST".
			$override = apply_filters('pre_nebula_requested_url', null, $host);
			if ( isset($override) ){return $override;}

			$protocol = ( is_ssl() )? 'https' : 'http';
			$full_url = $protocol . '://' . $this->super->server["$host"] . $this->super->server["REQUEST_URI"];

			return esc_url($full_url);
		}

		//Separate a URL into it's components.
		public function url_components($segment="all", $url=null){
			$override = apply_filters('pre_nebula_url_components', null, $segment, $url);
			if ( isset($override) ){return $override;}

			//If URL is not passed, get the current page URL.
			//@todo "Nebula" 0: Use null coalescing operator here
			if ( !$url ){
				$url = $this->requested_url();
			}

			//If it is not a valid URL, treat it as a relative path
			$relative = false;
			if ( !filter_var($url, FILTER_VALIDATE_URL) ){
				$relative = true;
				$url = 'http://example.com' . $url; //Prepend it with a temporary protocol, SLD, and TLD so it can be parsed (and removed later).
			}

			$url_components = parse_url(html_entity_decode($url));

			if ( empty($url_components['host']) ){
				return;
			}
			$host = explode('.', $url_components['host']);

			//Best way to get the domain so far. Probably a better way by checking against all known TLDs.
			preg_match("/[a-z0-9\-]{1,63}\.[a-z\.]{2,6}$/", parse_url($url, PHP_URL_HOST), $domain);

			if ( !empty($domain) ){
				$sld = substr($domain[0], 0, strpos($domain[0], '.'));
				$tld = substr($domain[0], strpos($domain[0], '.'));
			}

			switch ($segment){
				case ('all'):
				case ('href'):
					return str_replace('http://example.com', '', $url);

				case ('protocol'): //Protocol and Scheme are aliases and return the same value.
				case ('scheme'): //Protocol and Scheme are aliases and return the same value.
				case ('schema'):
					if ( $relative ){
						return false;
					}

					if ( isset($url_components['scheme']) ){
						return $url_components['scheme'];
					}

					return false;

				case ('port'):
					if ( $relative ){
						return false;
					}

					if ( isset($url_components['port']) ){
						return $url_components['port'];
					}

					switch( $url_components['scheme'] ){
						case ('http'):
							return 80; //Default for http
						case ('https'):
							return 443; //Default for https
						case ('ftp'):
							return 21; //Default for ftp
						case ('ftps'):
							return 990; //Default for ftps
						default:
							return false;
					}

				case ('user'): //Returns the username from this type of syntax: https://username:password@gearside.com/
				case ('username'):
					if ( $relative ){
						return false;
					}

					if ( isset($url_components['user']) ){
						return $url_components['user'];
					}

					return false;

				case ('pass'): //Returns the password from this type of syntax: https://username:password@gearside.com/
				case ('password'):
					if ( $relative ){
						return false;
					}

					if ( isset($url_components['pass']) ){
						return $url_components['pass'];
					}

					return false;

				case ('authority'):
					if ( $relative ){
						return false;
					}

					if ( isset($url_components['user'], $url_components['pass']) ){
						return $url_components['user'] . ':' . $url_components['pass'] . '@' . $url_components['host'] . ':' . $this->url_components('port', $url);
					}

					return false;

				case ('host'): //In http://something.example.com the host is "something.example.com"
				case ('hostname'):
					if ( $relative ){
						return false;
					}

					if ( isset($url_components['host']) ){
						return $url_components['host'];
					}

					return false;

				case ('www') :
					if ( $relative ){
						return false;
					}

					if ( $host[0] === 'www' ){
						return 'www';
					}

					return false;

				case ('subdomain'):
				case ('sub_domain'):
					if ( $relative ){
						return false;
					}

					if ( $host[0] !== 'www' && $host[0] !== $sld ){
						return $host[0];
					}

					return false;

				case ('domain') : //In http://example.com the domain is "example.com"
					if ( $relative ){
						return false;
					}

					if ( isset($domain[0]) ){
						return $domain[0];
					}

					return false;

				case ('basedomain'): //In http://example.com/something the basedomain is "http://example.com"
				case ('base_domain'):
				case ('origin') :
					if ( $relative ){
						return false;
					}

					if ( isset($url_components['scheme']) ){
						return $url_components['scheme'] . '://' . $domain[0];
					}

					return false;

				case ('sld') : //In example.com the sld is "example"
				case ('second_level_domain'):
				case ('second-level_domain'):
					if ( $relative ){
						return false;
					}

					return $sld;

				case ('tld') : //In example.com the tld is ".com"
				case ('top_level_domain'):
				case ('top-level_domain'):
					if ( $relative ){
						return false;
					}

					return $tld;

				case ('filepath'): //Filepath will be both path and file/extension
				case ('pathname'):
					if ( isset($url_components['path']) ){
						return $url_components['path'];
					}

					return false;

				case ('file'): //Filename will be just the filename/extension.
				case ('filename'):
					if ( strpos(basename($url_components['path']), '.') !== false ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
						return basename($url_components['path']);
					}

					return false;

				case ('type'):
				case ('filetype'):
				case ('extension'): //Only the extension (without ".")
					if ( strpos(basename($url_components['path']), '.') !== false ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
						$file_parts = explode('.', $url_components['path']);
						return $file_parts[count($file_parts)-1];
					}

					return false;

				case ('path'): //Path should be just the path without the filename/extension.
					if ( strpos(basename($url_components['path']), '.') !== false ){ //@TODO "Nebula" 0: This will possibly give bad data if the directory name has a "." in it //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
						return str_replace(basename($url_components['path']), '', $url_components['path']);
					}

					return $url_components['path'];

				case ('query'):
				case ('queries'):
				case ('search'):
					if ( isset($url_components['query']) ){
						return $url_components['query'];
					}

					return false;

				case ('fragment'):
				case ('fragments'):
				case ('anchor'):
				case ('hash') :
				case ('hashtag'):
				case ('id'):
					if ( isset($url_components['fragment']) ){
						return $url_components['fragment'];
					}

					return false;

				default :
					return $url;
			}
		}

		//Store initial UTM tags through each session
		//This is very similar to the JavaScript method of attribution tracking. That JS method works with third-party CRMs, where this is useful locally. Both are included in CF7 debuginfo (which may be interesting to compare)
		public function utms(){
			if ( !$this->is_analytics_allowed() ){ //Do nothing if analytics is not allowed
				return '';
			}

			if ( $this->get_option('attribution_tracking') ){ //This functionality requires the Attribution Tracking Nebula Option because it adds tracking cookies
				//Check the cookie first
				if ( !empty($this->super->cookie['nebula_utms']) ){
					return sanitize_text_field(htmlspecialchars($this->super->cookie['nebula_utms']));
				}

				//Otherwise check for UTM parameters
				$query_string = $this->url_components('query');
				$notable_tags = array('utm_', 'fbclid', 'gclid', 'gclsrc', 'dclid', 'gbraid', 'wbraid', 'mc_eid', '_hsenc', 'vero_id', 'mkt_tok');

				foreach ( $notable_tags as $tag ){
					if ( strpos(strtolower($query_string), $tag) > -1 ){ //If UTM parameters exist //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
						$this->set_cookie('nebula_utms', $this->url_components('all'), strtotime('+14 months')); //Set/update the cookie and store the entire LP URL
						return sanitize_text_field($this->url_components('all')); //Return the entire landing page URL with full query string sanitized
					}
				}
			}

			return '';
		}

		//Handle the caching of the transient and object cache simultaneously
		//This is best used when assigning a variable from an "expensive" output
		//When passing parameters they must ALWAYS be passed as an array!
		public function transient($name, $function, $parameters=array(), $expiration=null, $fresh=false){
			//If the parameters variable is not passed at all, use it as the expiration. This could be avoided by listing the parameters by name when calling this function...
			if ( is_int($parameters) ){
				$fresh = $expiration;
				$expiration = $parameters;
				$parameters = array(); //And reset the $parameters variable to be an empty array
			}

			$data = get_transient($name);
			if ( !empty($fresh) || empty($data) || $this->is_debug() ){
				$data = wp_cache_get($name);
				if ( empty($data) ){ //This does not get a "fresh" option because we always only want it to run once per load
					if ( is_string($function) ){
						$data = call_user_func($function, $parameters); //If the function name is passed as a string, call it
					} else {
						$data = $function($parameters); //Otherwise, assume the function is passed as an actual function
					}

					if ( is_null($data) ){
						return false; //If the function does not return, do not store anything in the cache
					}

					wp_cache_set($name, $data); //Set the object cache (memory for multiple calls during this current load)
				}

				do_action('qm/info', 'Transient Updated: ' . $name);
				set_transient($name, $data, $expiration); //Set the transient (DB to speed up future loads)
			}

			return $data;
		}

		//Check if transients are not being suspended
		public function is_transients_enabled(){
			if ( class_exists('AM_Transients_Manager') && get_option('pw_tm_suspend') ){
				return false; //Transients are suspended
			}

			return true; //Transients are enabled
		}

		//Create a session and cookie
		public function set_cookie($name, $value, $expiration){
			$string_value = (string) $value;
			if ( empty($string_value) ){
				$string_value = 'false';
			}

			if ( empty($expiration) ){
				$expiration = strtotime('January 1, 2035');
			}

			$this->super->cookie[$name] = $string_value;
			if ( !headers_sent() ){
				setcookie(
					$name,
					$string_value,
					$expiration, //Note: Do not let this cookie expire past 2038 or it instantly expires. http://en.wikipedia.org/wiki/Year_2038_problem
					COOKIEPATH,
					COOKIE_DOMAIN,
					is_ssl(), //Secure (HTTPS)
					true //HTTP only (not available in JS)
				);
			}
		}

		//Fuzzy meta sub key finder (Used to query ACF nested repeater fields).
		//Example: 'key' => 'dates_%_start_date' (repeater > field)
		//Example: 'key' => 'dish_%_ingredients_%_ingredient' (repeater > repeater > field) Only the first repeater needs the _%_ but others won't hurt
		public function fuzzy_posts_where($where){
			$override = apply_filters('pre_nebula_fuzzy_posts_where', null, $where);
			if ( isset($override) ){return $override;}

			global $wpdb;

			if ( strpos($wpdb->remove_placeholder_escape($where), '_%_') > -1 ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
				$where = preg_replace(
					"/meta_key = ([\'\"])(.+)_%_/",
					"meta_key LIKE $1$2_%_",
					$wpdb->remove_placeholder_escape($where)
				);
			}

			return $where;
		}

		//Text limiter by words
		public function string_limit_words($string, $word_limit){
			$override = apply_filters('pre_string_limit_words', null, $string, $word_limit);
			if ( isset($override) ){return $override;}

			$limited['text'] = $string;
			$limited['is_limited'] = false;

			$words = array_slice(array_filter(explode(' ', trim($string))), 0, $word_limit); //Create an array of words after trimming whitespace and removing empty array items. Then keep only the first words up to the requested limit.

			if ( count($words) >= $word_limit ){
				$limited['text'] = implode(' ', $words);
				$limited['is_limited'] = true;
			}

			return $limited;
		}

		//String limiter by characters
		public function string_limit_chars($string, $char_limit){
			$override = apply_filters('pre_string_limit_chars', null, $string, $char_limit);
			if ( isset($override) ){return $override;}

			$limited['text'] = trim(strip_tags($string));
			$limited['is_limited'] = false;

			if ( strlen($limited['text']) <= $char_limit ){
				return $limited;
			}

			$limited['text'] = substr($limited['text'], 0, ($char_limit+1));
			$limited['is_limited'] = true;

			return $limited;
		}

		//Traverse multidimensional arrays
		public function contains($haystack, $needles){return $this->in_array_r($haystack, $needles, 'contains');}
		public function in_array_r($haystack, $needles, $strict=true){
			$override = apply_filters('pre_in_array_r', null, $haystack, $needles, $strict);
			if ( isset($override) ){return $override;}

			foreach ( $needles as $needle ){
				if ( $strict === true ){ //If strict, match the type and the value
					if ( $needle === $haystack ){
						return true;
					}
				} else {
					if ( $strict === 'contains' ){ //If strict is 'contains', check if the item contains the needle
						if ( stripos($haystack, $needle) !== false ){
							return true;
						}
					} elseif ( $$needle === $haystack ){ //Otherwise check if the item matches the needle (regardless of type)
						return true;
					}
				}

				if ( is_array($needle) && in_array_r($haystack, $needle, $strict) ){ //If the item is an array, recursively check that array
					return true;
				}
			}

			return false;
		}

		//Check if an array contains anything from another array
		public function in_array_any($needles, $haystack){
			if ( is_string($haystack) ){
				$haystack = array($haystack); //Convert to an array if a string is provided
			}

			return (bool) array_intersect($needles, $haystack);
		}

		//Return a singular or plural label string based on the value
		public function singular_plural($value, $singular, $plural=''){
			if ( $value == 1 ){
				return $singular;
			}

			if ( empty($plural) ){
				$plural = $singular + 's'; //Append an "s" to the singular label to simplify calling the function most of the time
			}

			return $plural;
		}

		//Recursive Glob
		public function glob_r($pattern, $flags=0){
			$override = apply_filters('pre_glob_r', null, $pattern, $flags);
			if ( isset($override) ){return $override;}

			$files = glob($pattern, $flags);
			foreach ( glob(dirname($pattern) . '/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir ){
				$files = array_merge($files, $this->glob_r($dir . '/' . basename($pattern), $flags));
			}

			return $files;
		}

		//Get all PHP log files
		public function get_log_files($requested_type='all', $fresh=false){
			$timer_name = $this->timer('Get Log Files');

			//Use the transient so we avoid scanning multiple times in short periods of time
			$all_log_files = nebula()->transient('nebula_all_log_files', function(){
				$file_names = array(
					'php' => 'error_log',
					'wordpress' => 'debug.log',
					'nebula' => 'nebula.log'
				);

				//Prepare the list
				$all_log_files = array(
					'php' => array(),
					'wordpress' => array(),
					'nebula' => array()
				);

				//Check all theme files
				foreach ( $this->glob_r(WP_CONTENT_DIR . '/themes/*') as $file ){
					if ( $this->contains($file, array('/twenty')) ){ //Ignore certain strings (this can be anything in the filepath)
						continue; //Move on to the next file
					}

					foreach ( $file_names as $type => $name ){
						if ( strpos($file, '/' . $name) !== false ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
							$all_log_files[$type][] = array(
								'type' => $type,
								'path' => $file, //Full file path
								'shortpath' => '/' . str_replace(ABSPATH, '', $file), //Relative to the root WP directory
								'name' => $name,
								'bytes' => filesize($file),
							);

							break 2; //Move on to the next file
						}
					}
				}

				//Check for the content directory debug.log file (this is separate to reduce the glob process because it is a single file in a set location)
				$wp_content_debug_log = WP_CONTENT_DIR . '/debug.log';
				if ( file_exists($wp_content_debug_log) ){
					$all_log_files['wordpress'][] = array(
						'type' => 'wordpress',
						'path' => $wp_content_debug_log,
						'shortpath' => '/' . str_replace(ABSPATH, '', $wp_content_debug_log), //Relative to the root WP directory
						'name' => 'debug.log',
						'bytes' => filesize($wp_content_debug_log),
					);
				}

				return $all_log_files;
			}, MINUTE_IN_SECONDS*15);

			$this->timer($timer_name, 'end');

			if ( $requested_type === 'all' ){
				return $all_log_files;
			}

			switch ( str_replace(array('_', ',', '-'), '', $requested_type) ){
				case 'php':
				case 'errorlog':
				case 'errors':
					return $all_log_files['php'];
				case 'wordpress':
				case 'wp':
				case 'debuglog':
				case 'debug':
					return $all_log_files['wordpress'];
				case 'nebula':
				case 'nebulalog':
					return $all_log_files['nebula'];
				default:
					return $all_log_files;
			}
		}

		//Add up the filesizes (in bytes) of files in a directory (and it's sub-directories)
		public function foldersize($path){
			$override = apply_filters('pre_foldersize', null, $path);
			if ( isset($override) ){return $override;}

			$total_size = 0;
			$files = scandir($path);
			$cleanPath = rtrim($path, '/') . '/';
			foreach ( $files as $file ){
				if ( $file <> '.' && $file <> '..'){
					$currentFile = $cleanPath . $file;
					if ( is_dir($currentFile) ){
						$size = $this->foldersize($currentFile);
						$total_size += $size;
					} else {
						$size = filesize($currentFile);
						$total_size += $size;
					}
				}
			}

			return $total_size; //Return total size in bytes
		}

		//Format a filesize to an appropriate unit
		public function format_bytes($bytes, $precision=1){
			$units = array('b', 'kb', 'mb', 'gb', 'tb');
			$bytes = max($bytes, 0);
			$base = ( $bytes )? log($bytes) : 0;
			$pow = floor($base/log(1024));
			$pow = min($pow, count($units)-1);
			$bytes = $bytes/pow(1024, $pow);
			return round($bytes, $precision) . $units[$pow];
		}

		//Recursively copy files/directories
		public function xcopy($source, $destination, $permissions = 0755){
			//Check for symlinks
			if ( is_link($source) ){
				return symlink(readlink($source), $destination);
			}

			//Simple copy for a file
			if ( is_file($source) ){
				return copy($source, $destination);
			}

			//Make destination directory
			if ( !is_dir($destination) ){
				mkdir($destination, $permissions);
			}

			//Loop through the folder
			$directory = dir($source);
			while ( false !== ($entry = $directory->read()) ){ //Assign the $entry variable to the next directory entry (which will be false if it does not exist)
				//Skip pointers
				if ( $entry == '.' || $entry == '..' ){
					continue;
				}

				$this->xcopy($source . '/' . $entry, $destination . '/' . $entry, $permissions); //Deep copy directories
			}

			$directory->close();
			return true;
		}

		//Check if a value is a UTC Timestamp
		//This function only validates UTC timestamps between April 26, 1970 and May 18, 2033 to avoid conflicts (like phone numbers).
		public function is_utc_timestamp($timestamp){
			//If the timestamp contains any non-digit
			if ( preg_match('/\D/i', $timestamp) ){
				return false;
			}

			//If the timestamp is greater than May 18, 2033 (This function only supports up to this date to avoid conflicts with phone numbers. We'll have to figure out a new solution then.)
			if ( strlen($timestamp) === 10 && substr($timestamp, 0, 1) > 1 ){
				return false;
			}

			//If the timestamp has between 8 and 10 characters.
			if ( strlen($timestamp) >= 8 && strlen($timestamp) <= 10 ){
				$timestamp = intval($timestamp);
				if ( ctype_digit($timestamp) && strtotime(date('d-m-Y H:i:s', $timestamp)) === $timestamp ){
					return true;
				}
			}

			return false;
		}

		//Check if a website or resource is available
		public function is_available($url=null, $allow_cache=true, $allow_remote_request=true, $args=array()){
			$override = apply_filters('pre_nebula_is_available', null, $url, $allow_cache, $allow_remote_request);
			if ( isset($override) ){return $override;}

			//Make sure the URL is valid
			if ( empty($url) || strpos($url, 'http') !== 0 ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
				trigger_error('Error: Requested URL is either empty or missing acceptable protocol.', E_USER_ERROR);
				return false;
			}

			$timer_name = $this->timer('Is Available (' . $url . ')', 'start', 'Is Available');
			$hostname = str_replace('.', '_', $this->url_components('hostname', $url)); //The hostname label for transients

			if ( $this->is_transients_enabled() ){
				//Check transient first
				$site_available_buffer = get_transient('nebula_site_available_' . $hostname);
				if ( !empty($site_available_buffer) && $allow_cache ){ //If this hostname was found in a transient and specifically allowing a cached response.
					if ( $site_available_buffer === 'Available' ){
						set_transient('nebula_site_available_' . $hostname, 'Available', MINUTE_IN_SECONDS*30); //Re-up the transient with a 30 minute expiration
						$this->timer($timer_name, 'end');
						return true; //This hostname has worked within the last 30 minutes
					}

					set_transient('nebula_site_available_' . $hostname, 'Unavailable', MINUTE_IN_SECONDS*15); //15 minute expiration
					$this->timer($timer_name, 'end');
					return false; //This hostname has not worked within the last 15 minutes
				}
			} else {
				$allow_remote_request = false; //If transients are being suspended, don't pre-check if URLs are available
			}

			//Make an actual request to the URL if: the transient was empty or specifically requested a non-cached response, and specifically allowing a lookup
			//Ex: remote_get() prevents this from running the actual request to avoid multiple requests. It handles the transient itself, so just looking up if a request has failed previously.
			if ( $allow_remote_request ){ //If we are actively allowed to make the request to check if the endpoint is actually available
				//Combine default args with passed args. Args docs: https://developer.wordpress.org/reference/classes/WP_Http/request/
				$all_args = array_merge(array(
					'redirection' => 5, //Follow 5 redirects before quitting
				), $args);

				//Only get the head data for slight speed improvement.
				$response = wp_remote_head($url, $all_args);
				if ( !is_wp_error($response) && $response['response']['code'] === 200 ){ //If the remote request was successful
					set_transient('nebula_site_available_' . $hostname, 'Available', MINUTE_IN_SECONDS*20); //20 minute expiration
					$this->timer($timer_name, 'end');
					return true;
				}
			}

			if ( !$allow_remote_request ){ //Otherwise, do not actively check and just report that the site was not reportedly down prior
				$this->timer($timer_name, 'end');
				return true; //Resource may not actually be available, but was asked specifically not to check.
			}

			//Finally, if none of the previous checks were true, the site must be unavailable
			set_transient('nebula_site_available_' . $hostname, 'Unavailable', MINUTE_IN_SECONDS*10); //10 minute expiration
			$this->timer($timer_name, 'end');
			return false;
		}

		//Get a remote resource and if unavailable, don't re-check the resource for 5 minutes.
		//Args docs: https://developer.wordpress.org/reference/classes/WP_Http/request/
		public function remote_get($url, $args=null, $ignore_cache=false){
			if ( apply_filters('disable_nebula_remote_get', false, $url) ){ //Consider a Nebula Option here as well?
				return new WP_Error('disabled', 'Nebula remote_get has been disabled (for this or all requests).');
			}

			//Must be a valid URL
			if ( empty($url) || strpos($url, 'http') !== 0 ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
				return new WP_Error('broke', 'Requested URL is either empty or missing acceptable protocol.');
			}

			$timer_name = $this->timer('Remote Get (' . $url . ')', 'start', 'Remote Get');
			$hostname = str_replace('.', '_', $this->url_components('hostname', $url));

			//Check if the resource was unavailable in the last 10 minutes
			if ( !$ignore_cache ){ //This is useful for debugging– it will always make the remote request regardless of availability of the endpoint
				if ( !$this->is_available($url, true, false) ){ //We do not want to make 2 requests from this, so we tell is_available() to not make its own request (third parameter is "false")– note that this function also updates the "nebula_site_available..." transient if a problem arises– which will then be seen by is_available().
					$this->timer($timer_name, 'end');
					return new WP_Error('unavailable', 'This resource was unavailable within the last 15 minutes.');
				}
			}

			//Get the remote resource
			$response = wp_safe_remote_get($url, $args);
			do_action('qm/info', 'Nebula Remote Get: ' . $url);
			if ( is_wp_error($response) ){
				set_transient('nebula_site_available_' . $hostname, 'Unavailable', MINUTE_IN_SECONDS*10); //10 minute expiration
			}

			//Return the response. Do not set a transient here because failed requests still return here.
			$this->timer($timer_name, 'end');
			return $response;
		}

		//If the request was made via AJAX
		public function is_ajax(){return $this->is_ajax_request();} //Alias
		public function is_ajax_request(){
			if ( wp_doing_ajax() || (!empty($this->super->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->super->server['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ){
				return true;
			}

			return false;
		}

		//If this request is using AJAX, REST API, CRON, or some other type of background request. This can be used to ignore non-essential/visual functionality to speed up those requests.
		public function is_background_request(){
			//Check for AJAX
			if ( $this->is_ajax_request() ){
				return true;
			}

			//Check for the REST API
			if ( (defined('REST_REQUEST') && REST_REQUEST) || isset($this->super->get['rest_route']) ){
				return true;
			}

			//Check if a CRON is running
			if ( defined('DOING_CRON') && DOING_CRON ){
				return true;
			}

			//Check if it is an XMLRPC request
			if ( defined('XMLRPC_REQUEST') && XMLRPC_REQUEST ){
				return true;
			}

			//Check if WP is installing
			if ( defined('WP_INSTALLING') && WP_INSTALLING ){
				return true;
			}

			//If autosaving
			if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ){
				return false;
			}

			return false;
		}

		//Calculate the contrast ratio between two colors.
		public function contrast($front, $back){
			$backLum = $this->luminance($back) + 0.05;
			$foreLum = $this->luminance($front) + 0.05;

			return max(array($backLum, $foreLum)) / min(array($backLum, $foreLum));
		}

		//Calculate the luminance for a color  (0-255).
		public function color_brightness($color){return $this->luminance($color);}
		public function luminance($color){
			$rgb = $this->hex2rgb($color);

			if ( is_array($rgb) ){
				$red = $this->linear_channel($rgb['r'] + 1);
				$green = $this->linear_channel($rgb['g'] + 1);
				$blue = $this->linear_channel($rgb['b'] + 1);

				return 0.2126 * $red + 0.7152 * $green + 0.0722 * $blue;
			}

			return 0;
		}

		//Calculate the linear channel of a color
		public function linear_channel($color){
			$color = $color/255;

			if ( $color < 0.03928 ){
				return $color/12.92;
			}

			return pow(($color + 0.055)/1.055, 2.4);
		}

		//Automatically convert HEX colors to RGB.
		public function hex2rgb($color){
			$override = apply_filters('pre_hex2rgb', false, $color);
			if ( $override !== false ){return $override;}

			if ( $color[0] == '#' ){
				$color = substr($color, 1);
			}

			if ( strlen($color) == 6 ){
				list($r, $g, $b) = array($color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]);
			} elseif ( strlen($color) == 3 ){
				list($r, $g, $b) = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
			} else {
				return false;
			}

			$r = hexdec($r);
			$g = hexdec($g);
			$b = hexdec($b);

			return array('r' => $r, 'g' => $g, 'b' => $b);
		}

		//Add server timings to an array
		//To add time to an entry, simply use the action 'end' on the same unique_id again
		public function timer($unique_id, $action='start', $category=false){
			//Unique ID is required
			if ( empty($unique_id) || in_array(strtolower($unique_id), array('start', 'stop', 'end')) ){
				return false;
			}

			if ( $action === 'start' || $action === 'mark' || $action === 'once' ){
				//Prevent duplicates by appending a random number to the ID (only when duplicate)
				if ( !empty($this->server_timings[$unique_id]) ){
					$unique_id .= '_d' . random_int(100000, 999999);
				}

				do_action('qm/start', $unique_id); //Inform Query Monitor as well

				$this->server_timings[$unique_id] = array(
					'start' => microtime(true),
					'active' => true,
					'category' => $category
				);

				//Immediately stop one-off timing marks
				if ( $action === 'mark' || $action === 'once' ){
					//Start and stop the Query Monitor timing so it appears without error
					do_action('qm/stop', $unique_id); //Immediately end the Query Monitor timer so it appears without error

					$this->server_timings[$unique_id]['end'] = $this->server_timings[$unique_id]['start']+0.001;
					$this->server_timings[$unique_id]['time'] = 0.001; //Force non-empty time of 1 millisecond
					$this->server_timings[$unique_id]['active'] = false;

					//Add to array of this category (if categorization is used)
					if ( !empty($category) ){
						$this->server_timings['categories'][$category][] = 0.001; //Force non-empty time of 1 millisecond
					}
				}

				return $unique_id; //Return the unique ID in case it was changed so that the 'end' call can know what to use
			} elseif ( in_array(strtolower($action), array('stop', 'end')) ){
				do_action('qm/stop', $unique_id); //Inform Query Monitor as well

				if ( !empty($this->server_timings[$unique_id]['start']) ){ //Make sure this timer has started
					$this->server_timings[$unique_id]['end'] = microtime(true);
					$this->server_timings[$unique_id]['time'] = $this->server_timings[$unique_id]['end'] - $this->server_timings[$unique_id]['start'];
					$this->server_timings[$unique_id]['active'] = false;

					//Add to array of this category (if categorization is used)
					$this_category = $this->server_timings[$unique_id]['category'];
					if ( !empty($this_category) ){
						$this->server_timings['categories'][$this_category][] = $this->server_timings[$unique_id]['time'];
					}

					return true;
				}
			}

			return false;
		}

		//Add category timings together, and add more times to the server timings array
		public function finalize_timings(){
			//Add category times together
			if ( !empty($this->server_timings['categories']) ){
				foreach ( $this->server_timings['categories'] as $category => $times ){
					if ( count($times) > 1 ){
						$this->server_timings[$category . ' [Total]'] = array('time' => array_sum($times));
					}
				}
			}

			//Pre-Nebula WordPress Core Time
			$this->server_timings['WordPress Core'] = array(
				'start' => WP_START_TIMESTAMP,
				'end' => $this->time_before_nebula,
				'time' => $this->time_before_nebula-WP_START_TIMESTAMP
			);

			//Database Queries
			global $wpdb;
			$total_query_time = 0;
			if ( !empty($wpdb->queries) ){
				foreach ( $wpdb->queries as $query ){
					$total_query_time += $query[1];
				}
				$this->server_timings['DB Queries [Total]'] = array('time' => $total_query_time);
			}

			//Resource Usage
			$resource_usage = getrusage();

			//System resource usage timing
			$this->server_timings['PHP System'] = array(
				'start' => $this->super->server['REQUEST_TIME_FLOAT'],
				'end' => $this->super->server['REQUEST_TIME_FLOAT']+($resource_usage['ru_stime.tv_usec']),
				'time' => $resource_usage['ru_stime.tv_usec']/1000000 //PHP 7.4 use numeric separators here
			);

			//User resource usage timing
			$this->server_timings['PHP User'] = array(
				'start' => $this->super->server['REQUEST_TIME_FLOAT'],
				'end' => $this->super->server['REQUEST_TIME_FLOAT']+($resource_usage['ru_utime.tv_usec']),
				'time' => $resource_usage['ru_utime.tv_usec']/1000000 //PHP 7.4 use numeric separators here
			);

			//Add Nebula total
			$this->server_timings['Nebula [Total]'] = array(
				'start' => $this->time_before_nebula,
				'end' => microtime(true),
				'time' => microtime(true)-$this->time_before_nebula
			);

			//Total PHP execution time
			$this->server_timings['PHP [Total]'] = array(
				'start' => $this->super->server['REQUEST_TIME_FLOAT'],
				'end' => microtime(true),
				'time' => microtime(true)-$this->super->server['REQUEST_TIME_FLOAT']
			);

			return apply_filters('nebula_finalize_timings', $this->server_timings); //Allow functions/plugins to add/modifiy timings
		}

		//Get Nebula version information
		public function version($return=false){
			$override = apply_filters('pre_nebula_version', null, $return);
			if ( isset($override) ){return $override;}

			$appended_version = apply_filters('nebula_version_appended', ''); //Allow others to append an additional version number at the end of Nebula's. This would assist in clearing caches in the parent theme in certain circumstances.
			$appended_version_number = '';
			if ( !empty($appended_version) && substr($appended_version, 0, 1) !== '.' ){ //If it does not start with a dot, add one. //@todo "Nebula" 0: In PHP8 use str_starts_with here
				$appended_version_number .= '.' . $appended_version;
			}

			$return = str_replace(array(' ', '_', '-'), '', strtolower($return));

			if ( $return === 'child' && is_child_theme() ){
				return $this->child_version(); //This version gets appended on its own
			}

			//Parse the actual Nebula style.scss file which is closer to real-time than using wp_get_theme() below (which is sufficient for most uses), but is a little more intensive
			if ( $return === 'realtime' ){
				WP_Filesystem();
				global $wp_filesystem;
				$style_scss = $wp_filesystem->get_contents(get_template_directory() . '/assets/scss/style.scss');
				if ( !empty($style_scss) ){
					preg_match("/(?:Version: )(?<number>\d+?\.\d+?\.\d+?\.\d+?)$/m", $style_scss, $realtime_version_number);
					return $realtime_version_number['number']; //Appended version number is not applied to the realtime version
				}
			}

			$nebula_theme_info = ( is_child_theme() )? wp_get_theme(str_replace('-child', '', get_template())) : wp_get_theme(); //Get the parent theme (regardless of if child theme is active)

			if ( $return === 'raw' ){ //Check this first to prevent needing to RegEx altogether
				return $nebula_theme_info->get('Version') . $appended_version_number; //Ex: 7.2.23.8475
			}

			preg_match('/(?<primary>(?<major>\d+)\.(?<minor>\d+)\.(?<patch>\d+[a-z]?))\.?(?<build>\d+)?/i', $nebula_theme_info->get('Version'), $nebula_version);

			//If the preg_match fails, exit early here
			if ( empty($nebula_version) ){
				return 0; //May need to return different types based on what $return value is expected... Trying an int for now.
			}

			$nebula_version['patch'] = preg_replace('/\D/', '', $nebula_version['patch']); //Remove letters from patch number

			$nebula_version_year = ( $nebula_version['minor'] >= 8 )? 2012+$nebula_version['major']+1 : 2012+$nebula_version['major'];
			$nebula_months = array('May', 'June', 'July', 'August', 'September', 'October', 'November', 'December', 'January', 'February', 'March', 'April');
			$nebula_version_month = $nebula_months[$nebula_version['minor']%12]; //Modulo 12 so the version can go beyond 11 (and still match the appropriate month)
			$nebula_version_day = ( empty($nebula_version['patch']) )? '' : $nebula_version['patch'];
			$nebula_version_day_formated = ( empty($nebula_version['patch']) )? ' ' : ' ' . $nebula_version['patch'] . ', ';

			$nebula_version_info = array(
				'full' => $nebula_version[0],
				'primary' => $nebula_version['primary'],
				'major' => $nebula_version['major'],
				'minor' => $nebula_version['minor'],
				'patch' => $nebula_version['patch'],
				'build' => ( isset($nebula_version['build']) )? $nebula_version['build'] : false,
				'appended' =>  $appended_version,
				'utc' => strtotime($nebula_version_month . $nebula_version_day_formated . $nebula_version_year),
				'date' => $nebula_version_month . $nebula_version_day_formated . $nebula_version_year,
				'year' => $nebula_version_year,
				'month' => $nebula_version_month,
				'day' => $nebula_version_day,
			);

			switch ( $return ){
				case ('raw'): //Should not ever reach this. See early return above.
				case ('realtime'): //Probably would not reach this unless Sass is disabled and requesting parent theme realtime version
					return $nebula_theme_info->get('Version'); //Ex: 7.2.19.8475
				case ('version'):
				case ('full'):
					return $nebula_version_info['full'] . $appended_version; //Ex: 7.2.23.8475 (plus any appended number)
				case ('primary'):
					return $nebula_version_info['primary']; //Ex: 7.2.23
				case ('date'):
					return $nebula_version_info['date']; //Ex: July 23, 2019
				case ('time'):
				case ('utc'):
					return $nebula_version_info['utc']; //Ex: 1559275200
				default:
					return $nebula_version_info;
			}
		}

		//Get the child theme version information (falls back to "full" Nebula version if not a child theme)
		public function child_version(){
			$override = apply_filters('pre_nebula_child_version', null);
			if ( isset($override) ){return $override;}

			if ( !is_child_theme() ){
				return $this->version('full'); //Return the parent theme version if child theme is not active
			}

			$appended_version = apply_filters('nebula_version_appended', ''); //Allow others to append an additional version number at the end of Nebula's. This would assist in clearing caches in the parent theme in certain circumstances.
			$appended_version_number = '';
			if ( !empty($appended_version) && substr($appended_version, 0, 1) !== '.' ){ //If it does not start with a dot, add one. //@todo "Nebula" 0: In PHP8 use str_starts_with here
				$appended_version_number .= '.' . $appended_version;
			}

			//Get the version number from the child theme stylesheet
			$child_theme_info = wp_get_theme();
			return $child_theme_info->get('Version') . $appended_version_number;
		}

		//Update the child theme version whenever Sass is re-processed
		public function update_child_version_number(){
			$override = apply_filters('pre_nebula_update_child_version', null);
			if ( isset($override) ){return $override;}

			if ( is_child_theme() ){
				WP_Filesystem();
				global $wp_filesystem;

				//Use the Sass file if enabled, otherwise edit the style.css directly
				$child_stylesheet_location = get_stylesheet_directory() . '/style.css';
				if ( $this->get_option('scss') ){
					$child_stylesheet_location = get_stylesheet_directory() . '/assets/scss/style.scss';
				}

				$child_stylesheet = $wp_filesystem->get_contents($child_stylesheet_location);
				if ( !empty($child_stylesheet) ){
					$child_stylesheet = preg_replace_callback("/(Version: \d+?\.\d+?\.)(\d+)$/m", function($matches){
						return $matches[1] . (intval($matches[2])+1); //Add one to the security digit
					}, $child_stylesheet);

					$wp_filesystem->put_contents($child_stylesheet_location, $child_stylesheet); //Update the file
				}
			}
		}

		//Create Custom Properties
		public function create_hubspot_properties(){
			if ( $this->get_option('hubspot_portal') ){
				if ( $this->get_option('hubspot_api') ){
					//Get an array of all existing Hubspot CRM contact properties
					$existing_nebula_properties = $this->get_nebula_hubspot_properties();

					if ( empty($existing_nebula_properties) ){
						//Create the Nebula group of properties
						$content = '{
							"name": "nebula",
							"displayName": "Nebula",
							"displayOrder": 5
						}';

						$this->hubspot_curl('http://api.hubapi.com/contacts/v2/groups?portalId=' . $this->get_option('hubspot_portal'), $content);
					}

					$custom_nebula_properties = array();

					$custom_nebula_properties[] = array(
						'name' => 'full_name',
						'label' => 'Full Name',
						'description' => "The full name of the contact",
					);

					$custom_nebula_properties[] = array(
						'name' => 'user_agent',
						'label' => 'User Agent',
						'description' => "The user agent of the contact's device/browser",
					);

					$custom_nebula_properties[] = array(
						'name' => 'session_id',
						'label' => 'Session ID',
						'description' => 'The Nebula Session ID given to each session',
					);

					$custom_nebula_properties[] = array(
						'name' => 'wordpress_id',
						'label' => 'WordPress User ID',
						'description' => 'The WordPress User ID of logged in users',
					);

					$custom_nebula_properties[] = array(
						'name' => 'username',
						'label' => 'Username',
						'description' => 'The WordPress username of logged in users',
					);

					$custom_nebula_properties[] = array(
						'name' => 'role',
						'label' => 'Role',
						'description' => 'The WordPress role of this user (and any staff notations)',
					);

					$custom_nebula_properties[] = array(
						'name' => 'gender',
						'label' => 'Gender',
						'description' => "The user's gender",
					);

					$custom_nebula_properties[] = array(
						'name' => 'about',
						'label' => 'About',
						'description' => "A short bio about the user",
					);

					$custom_nebula_properties[] = array(
						'name' => 'birthday',
						'label' => 'Birthday',
						'description' => "The user's birthday",
					);

					$custom_nebula_properties[] = array(
						'name' => 'cookies',
						'label' => 'Cookies',
						'description' => 'Whether this user is allowing/blocking cookies',
					);

					$custom_nebula_properties[] = array(
						'name' => 'screen',
						'label' => 'Screen',
						'description' => "The screen dimensions (and color depth) of the user's device",
					);

					$custom_nebula_properties[] = array(
						'name' => 'device',
						'label' => 'Device',
						'description' => 'The device being used',
					);

					$custom_nebula_properties[] = array(
						'name' => 'os',
						'label' => 'Operating System',
						'description' => "The operating system of the user's device",
					);

					$custom_nebula_properties[] = array(
						'name' => 'browser',
						'label' => 'Browser',
						'description' => 'The browser used by this visitor',
					);

					$custom_nebula_properties[] = array(
						'name' => 'bot',
						'label' => 'Bot',
						'description' => 'Whether this user was detected as a bot',
					);

					$custom_nebula_properties[] = array(
						'name' => 'ga_cid',
						'label' => 'Google Analytics CID',
						'description' => 'The Google Analytics Client ID to identify this user in GA',
					);

					$custom_nebula_properties[] = array(
						'name' => 'facebook_id',
						'label' => 'Facebook ID',
						'description' => "The ID of the user's Facebook profile",
					);

					$custom_nebula_properties[] = array(
						'name' => 'profile_photo',
						'label' => 'Photo',
						'description' => "The user's profile photo",
					);

					$custom_nebula_properties[] = array(
						'name' => 'image',
						'label' => 'Image',
						'description' => "An image associated with the user",
					);

					$custom_nebula_properties[] = array(
						'name' => 'internal_search',
						'label' => 'Internal Search',
						'description' => 'Keywords from the user internally searching the website',
					);

					$custom_nebula_properties[] = array(
						'name' => 'mailto_contacted',
						'label' => 'Mailto Contacted',
						'description' => 'The email address this user contacted via mailto link',
					);

					$custom_nebula_properties[] = array(
						'name' => 'phone_contacted',
						'label' => 'Phone Contacted',
						'description' => 'The phone number this user contacted via click-to-call link',
					);

					$custom_nebula_properties[] = array(
						'name' => 'form_contacted',
						'label' => 'Form Contacted',
						'description' => 'The form(s) this user filled out and their success with it',
					);

					$custom_nebula_properties[] = array(
						'name' => 'geolocation',
						'label' => 'Geolocation',
						'description' => "The latitude/longitude of this user's geolocation (and accuracy)",
					);

					$custom_nebula_properties[] = array(
						'name' => 'address_lookup',
						'label' => 'Address Lookup',
						'description' => 'An address looked up by the user (may not be their own address)',
					);

					$custom_nebula_properties[] = array(
						'name' => 'utm_campaign',
						'label' => 'UTM Campaign',
						'description' => 'The UTM-tagged campaign name.',
					);

					$custom_nebula_properties[] = array(
						'name' => 'utm_source',
						'label' => 'UTM Source',
						'description' => 'The UTM-tagged source.',
					);

					$custom_nebula_properties[] = array(
						'name' => 'utm_medium',
						'label' => 'UTM Medium',
						'description' => 'The UTM-tagged medium.',
					);

					$custom_nebula_properties[] = array(
						'name' => 'utm_content',
						'label' => 'UTM Content',
						'description' => 'The UTM-tagged content.',
					);

					$custom_nebula_properties[] = array(
						'name' => 'utm_term',
						'label' => 'UTM Term',
						'description' => 'The UTM-tagged term.',
					);

					$properties_created = array();
					foreach ( $custom_nebula_properties as $value ){
						if ( !in_array($value['name'], $existing_nebula_properties) ){
							$content = '{
								"name": "' . $value['name'] . '",
								"label": "' . $value['label'] . '",
								"description": "' . $value['description'] . '",
								"groupName": "nebula",
								"type": "string",
								"fieldType": "text",
								"formField": true,
								"displayOrder": 6,
								"options": []
							}';

							$response = $this->hubspot_curl('https://api.hubapi.com/contacts/v2/properties', $content);
							$properties_created[] = $value['name'];
						}
					}

					if ( !empty($properties_created) ){
						?>
						<div class="updated notice notice-warning">
							<p><strong>Nebula Hubspot properties created!</strong> <?php echo count($properties_created); ?> contact properties were created in Hubspot. Be sure to <a href="https://app.hubspot.com/property-settings/<?php echo $this->get_option('hubspot_portal'); ?>/contact" target="_blank">manually create any needed properties</a> specific to this website.</p>
							<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
						</div>
						<?php
					}
				} else {
					?>
					<div class="updated notice notice-warning">
						<p><strong>Hubspot API Key Missing!</strong> <a href="https://app.hubspot.com/hapikeys">Get your API Key</a> then <a href="themes.php?page=nebula_options&tab=apis&option=hubspot_api">enter it here</a> and re-save Nebula Options, or <a href="https://app.hubspot.com/property-settings/<?php echo $this->get_option('hubspot_portal'); ?>/contact" target="_blank">manually create contact properties</a>.</p>
						<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
					</div>

					<?php
					//Should also have a note somewhere that custom identifications would need to be created manually (and implemented manually too). Recommend using a different property group than Nebula.
				}
			}
		}

		//Get all existing Hubspot CRM contact properties in the Nebula group
		public function get_nebula_hubspot_properties(){
			$all_hubspot_properties = $this->hubspot_curl('https://api.hubapi.com/contacts/v2/properties');
			$all_hubspot_properties = json_decode($all_hubspot_properties, true);

			$existing_nebula_properties = array();
			foreach ( $all_hubspot_properties as $property ){
				if ( $property['groupName'] == 'nebula' ){
					$existing_nebula_properties[] = $property['name'];
				}
			}

			return $existing_nebula_properties;
		}

		//Send data to Hubspot CRM via PHP curl
		public function hubspot_curl($url, $content=null){
			$sep = ( strpos($url, '?') === false )? '?' : '&'; //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
			$get_url = $url . $sep . 'hapikey=' . $this->get_option('hubspot_api');

			if ( !empty($content) ){
				$response = wp_remote_post($get_url, array(
					'headers' => array('Content-Type' => 'application/json'),
					'body' => $content,
				));
			} else {
				$response = wp_remote_get($get_url); //Change this to nebula remote_get?
				do_action('qm/info', 'Hubspot Remote Get: ' . $get_url); //Remove this if changing to nebula remote_get
			}

			if ( !is_wp_error($response) ){
				return $response['body'];
			}

			return false;
		}
	}
}