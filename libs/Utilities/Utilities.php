<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Utilities') ){
	require_once get_template_directory() . '/libs/Utilities/Analytics.php';
	require_once get_template_directory() . '/libs/Utilities/Device.php';
	require_once get_template_directory() . '/libs/Utilities/Sass.php';

	trait Utilities {
		use Analytics { Analytics::hooks as AnalyticsHooks;}
		use Device { Device::hooks as DeviceHooks;}
		use Sass { Sass::hooks as SassHooks;}

		public function hooks(){
			$this->server_timings = array();

			add_filter('posts_where', array($this, 'fuzzy_posts_where'));
			$this->AnalyticsHooks(); //Register Analytics hooks
			$this->DeviceHooks(); //Register Device hooks
			$this->SassHooks(); //Register Sass hooks
			register_shutdown_function(array($this, 'ga_log_fatal_errors'));
		}

		//Attempt to get the most accurate IP address from the visitor
		public function get_ip_address($force=false){
			//If this has already been ran once, return from object cache
			if ( !$force ){
				$ip = wp_cache_get('nebula_ip_address');
				if ( !empty($ip) ){
					return $ip;
				}
			}

			$ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
			foreach ( $ip_keys as $key ){
				if ( array_key_exists($key, $_SERVER) === true ){
					foreach ( explode(',', $_SERVER[$key]) as $ip ){
						$ip = trim($ip);

						if ( filter_var($ip, FILTER_VALIDATE_IP) ){ //Validate IP
							wp_cache_set('nebula_ip_address', $ip); //Store in object cache
							return $ip;
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
			$timer_name = $this->timer('Session ID');

			//Check object cache first
			$session_id = wp_cache_get('nebula_session_id');
			if ( !empty($session_id) ){
				return $session_id;
			}

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
			$session_data['s'] = ( session_id() )? session_id() : '!' . uniqid();

			//Google Analytics CID
			$session_data['cid'] = $this->ga_parse_cookie();

			//Additional session information
			$all_session_data = apply_filters('nebula_session_id', $session_data);

			//Convert to a string
			$session_id = '';
			foreach ( $all_session_data as $key => $value ){
				$session_id .= $key . ':' . $value . ';';
			}

			wp_cache_set('nebula_session_id', $session_id); //Store in object cache
			$this->timer($timer_name, 'end');
			return $session_id;
		}

		//Detect Notable POI
		public function poi($ip='detect'){
			$timer_name = $this->timer('POI Detection', 'start', 'Nebula POI');

			if ( is_null($ip) ){
				return false;
			}

			//Check object cache first
			$poi_match = wp_cache_get('nebula_poi_' . str_replace('.', '_', $ip));
			if ( !empty($poi_match) ){
				return $poi_match;
			}

			//Allow for other themes/plugins to provide additional detections
			$additional_detections = apply_filters('nebula_poi', $ip);
			if ( !empty($additional_detections) ){
				return $additional_detections;
			}

			if ( $this->get_option('notableiplist') ){
				if ( $ip === 'detect' ){
					$ip = $this->get_ip_address();
				}

				//Loop through Notable POIs saved in Nebula Options
				$notable_pois = array();
				$notable_ip_lines = explode("\n", esc_html($this->get_option('notableiplist')));

				if ( !empty($notable_ip_lines) ){
					foreach ( $notable_ip_lines as $line ){
						if ( !empty($line) ){
							$ip_info = explode(' ', strip_tags($line), 2); //0 = IP Address or RegEx pattern, 1 = Name
							$notable_pois[] = array(
								'ip' => $ip_info[0],
								'name' => $ip_info[1]
							);
						}
					}
				}

				$all_notable_pois = apply_filters('nebula_notable_pois', $notable_pois);
				$all_notable_pois = array_map("unserialize", array_unique(array_map("serialize", $all_notable_pois))); //De-dupe multidimensional array
				$all_notable_pois = array_filter($all_notable_pois); //Remove empty array elements

				//Finally, loop through all notable POIs to return a match
				if ( !empty($all_notable_pois) ){
					foreach ( $all_notable_pois as $notable_poi ){
						//Check for RegEx
						if ( $notable_poi['ip'][0] === '/' && preg_match($notable_poi['ip'], $ip) ){ //If first character of IP is "/" and the requested IP matches the pattern
							$poi_match = str_replace(array("\r\n", "\r", "\n"), '', $notable_poi['name']);
							wp_cache_set('nebula_poi_' . str_replace('.', '_', $ip), $poi_match); //Store in object cache
							$this->timer($timer_name, 'end');
							return $poi_match;
						}

						//Check direct match
						if ( $notable_poi['ip'] === $ip ){
							$poi_match = str_replace(array("\r\n", "\r", "\n"), '', $notable_poi['name']);
							wp_cache_set('nebula_poi_' . str_replace('.', '_', $ip), $poi_match); //Store in object cache
							$this->timer($timer_name, 'end');
							return $poi_match;
						}
					}

					wp_cache_set('nebula_poi_' . str_replace('.', '_', $ip), false); //Store in object cache
				}
			}

			$this->timer($timer_name, 'end');
			return false;
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
			if ( in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php')) || $_SERVER['PHP_SELF'] == '/wp-login.php' ){
				return true;
			}

			$abspath = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, ABSPATH);
			$included_files = get_included_files();
			if ( in_array($abspath . 'wp-login.php', $included_files) || in_array($abspath . 'wp-register.php', $included_files) ){
				return true;
			}

			return false;
		}

		//Check if the current page is not the first. (pagecount is incremented in nebula.php)
		public function is_after_first_pageview(){
			if ( isset($_SESSION['pagecount']) && $_SESSION['pagecount'] >= 2 ){
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
				$devIPs = explode(',', $this->get_option('dev_ip'));
				if ( !empty($devIPs) ){
					foreach ( $devIPs as $devIP ){
						$devIP = trim($devIP);

						if ( !empty($devIP) && $devIP[0] !== '/' && $devIP === $this->get_ip_address() ){
							return true;
						}

						if ( !empty($devIP) && $devIP[0] === '/' && preg_match($devIP, $this->get_ip_address()) ){
							return true;
						}
					}
				}
			}

			//Check if the current user's email domain matches any of the dev email domains from Nebula Options
			if ( is_user_logged_in() ){
				$current_user = wp_get_current_user();
				if ( !empty($current_user->user_email) ){
					list($current_user_email, $current_user_domain) = explode('@', $current_user->user_email);

					$devEmails = explode(',', $this->get_option('dev_email_domain'));
					foreach ( $devEmails as $devEmail ){
						if ( trim($devEmail) === $current_user_domain ){
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
				$clientIPs = explode(',', $this->get_option('client_ip'));
				if ( !empty($clientIPs) ){
					foreach ( $clientIPs as $clientIP ){
						$clientIP = trim($clientIP);

						if ( !empty($clientIP) && $clientIP[0] !== '/' && $clientIP === $this->get_ip_address() ){
							return true;
						}

						if ( !empty($clientIP) && $clientIP[0] === '/' && preg_match($clientIP, $this->get_ip_address()) ){
							return true;
						}
					}
				}
			}

			if ( is_user_logged_in() ){
				$current_user = wp_get_current_user();
				if ( !empty($current_user->user_email) ){
					list($current_user_email, $current_user_domain) = explode('@', $current_user->user_email);

					//Check if the current user's email domain matches any of the client email domains from Nebula Options
					$clientEmails = explode(',', $this->get_option('client_email_domain'));
					foreach ( $clientEmails as $clientEmail ){
						if ( trim($clientEmail) === $current_user_domain ){
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
				if ( nebula()->is_dev() ){
					$staff = ' (Developer)';
				} elseif ( nebula()->is_client() ){
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
			if ( array_key_exists('debug', $_GET) ){
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

		//Check if the current site is live to the public.
		//Note: This checks if the hostname of the home URL matches any of the valid hostnames.
		//If the Valid Hostnames option is empty, this will return true as it is unknown.
		public function is_site_live(){
			$override = apply_filters('pre_is_site_live', null);
			if ( isset($override) ){return $override;}

			if ( $this->get_option('hostnames') ){
				if ( strpos($this->get_option('hostnames'), $this->url_components('hostname', home_url())) >= 0 ){
					return true;
				}
				return false;
			}
			return true;
		}

		//Check if the site is an ecommerce website
		public function is_ecommerce(){
			if ( is_plugin_active('woocommerce/woocommerce.php') ){
				return true;
			}

			return false;
		}

		//If the request was made via AJAX
		public function is_ajax(){return $this->is_ajax_request();} //Alias
		public function is_ajax_request(){
			if ( !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' ){
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
			$full_url = $protocol . '://' . $_SERVER["$host"] . $_SERVER["REQUEST_URI"];

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

			$url_components = parse_url($url);
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
					break;

				case ('protocol'): //Protocol and Scheme are aliases and return the same value.
				case ('scheme'): //Protocol and Scheme are aliases and return the same value.
				case ('schema'):
					if ( $relative ){
						return false;
					}

					if ( isset($url_components['scheme']) ){
						return $url_components['scheme'];
					} else {
						return false;
					}
					break;

				case ('port'):
					if ( $relative ){
						return false;
					}

					if ( isset($url_components['port']) ){
						return $url_components['port'];
					} else {
						switch( $url_components['scheme'] ){
							case ('http'):
								return 80; //Default for http
								break;
							case ('https'):
								return 443; //Default for https
								break;
							case ('ftp'):
								return 21; //Default for ftp
								break;
							case ('ftps'):
								return 990; //Default for ftps
								break;
							default:
								return false;
								break;
						}
					}
					break;

				case ('user'): //Returns the username from this type of syntax: https://username:password@gearside.com/
				case ('username'):
					if ( $relative ){
						return false;
					}

					if ( isset($url_components['user']) ){
						return $url_components['user'];
					} else {
						return false;
					}
					break;

				case ('pass'): //Returns the password from this type of syntax: https://username:password@gearside.com/
				case ('password'):
					if ( $relative ){
						return false;
					}

					if ( isset($url_components['pass']) ){
						return $url_components['pass'];
					} else {
						return false;
					}
					break;

				case ('authority'):
					if ( $relative ){
						return false;
					}

					if ( isset($url_components['user'], $url_components['pass']) ){
						return $url_components['user'] . ':' . $url_components['pass'] . '@' . $url_components['host'] . ':' . $this->url_components('port', $url);
					} else {
						return false;
					}
					break;

				case ('host'): //In http://something.example.com the host is "something.example.com"
				case ('hostname'):
					if ( $relative ){
						return false;
					}

					if ( isset($url_components['host']) ){
						return $url_components['host'];
					}
					break;

				case ('www') :
					if ( $relative ){
						return false;
					}

					if ( $host[0] === 'www' ){
						return 'www';
					} else {
						return false;
					}
					break;

				case ('subdomain'):
				case ('sub_domain'):
					if ( $relative ){
						return false;
					}

					if ( $host[0] !== 'www' && $host[0] !== $sld ){
						return $host[0];
					} else {
						return false;
					}
					break;

				case ('domain') : //In http://example.com the domain is "example.com"
					if ( $relative ){
						return false;
					}

					if ( isset($domain[0]) ){
						return $domain[0];
					}
					break;

				case ('basedomain'): //In http://example.com/something the basedomain is "http://example.com"
				case ('base_domain'):
				case ('origin') :
					if ( $relative ){
						return false;
					}

					if ( isset($url_components['scheme']) ){
						return $url_components['scheme'] . '://' . $domain[0];
					}
					break;

				case ('sld') : //In example.com the sld is "example"
				case ('second_level_domain'):
				case ('second-level_domain'):
					if ( $relative ){
						return false;
					}

					return $sld;
					break;

				case ('tld') : //In example.com the tld is ".com"
				case ('top_level_domain'):
				case ('top-level_domain'):
					if ( $relative ){
						return false;
					}

					return $tld;
					break;

				case ('filepath'): //Filepath will be both path and file/extension
				case ('pathname'):
					if ( isset($url_components['path']) ){
						return $url_components['path'];
					}
					break;

				case ('file'): //Filename will be just the filename/extension.
				case ('filename'):
					if ( strpos(basename($url_components['path']), '.') !== false ){
						return basename($url_components['path']);
					} else {
						return false;
					}
					break;

				case ('type'):
				case ('filetype'):
				case ('extension'): //Only the extension (without ".")
					if ( strpos(basename($url_components['path']), '.') !== false ){
						$file_parts = explode('.', $url_components['path']);
						return $file_parts[count($file_parts)-1];
					} else {
						return false;
					}
					break;

				case ('path'): //Path should be just the path without the filename/extension.
					if ( strpos(basename($url_components['path']), '.') !== false ){ //@TODO "Nebula" 0: This will possibly give bad data if the directory name has a "." in it
						return str_replace(basename($url_components['path']), '', $url_components['path']);
					} else {
						return $url_components['path'];
					}
					break;

				case ('query'):
				case ('queries'):
				case ('search'):
					if ( isset($url_components['query']) ){
						return $url_components['query'];
					}
					break;

				case ('fragment'):
				case ('fragments'):
				case ('anchor'):
				case ('hash') :
				case ('hashtag'):
				case ('id'):
					if ( isset($url_components['fragment']) ){
						return $url_components['fragment'];
					}
					break;

				default :
					return $url;
					break;
			}
		}

		//Create a session and cookie
		public function set_global_session_cookie($name, $value, $types=array('global', 'session', 'cookie')){
			$string_value = (string) $value;
			if ( empty($string_value) ){
				$string_value = 'false';
			}

			if ( in_array('global', $types) ){
				$GLOBALS[$name] = $value;
			}

			if ( in_array('session', $types) ){
				$_SESSION[$name] = $value;
			}

			if ( in_array('cookie', $types) ){
				$_COOKIE[$name] = $string_value;
				if ( !headers_sent() ){
					setcookie(
						$name,
						$string_value,
						strtotime('January 1, 2035'), //Note: Do not let this cookie expire past 2038 or it instantly expires. http://en.wikipedia.org/wiki/Year_2038_problem
						COOKIEPATH,
						COOKIE_DOMAIN,
						is_ssl(), //Secure (HTTPS)
						true //HTTP only (not available in JS)
					);
				}
			}
		}

		//Fuzzy meta sub key finder (Used to query ACF nested repeater fields).
		//Example: 'key' => 'dates_%_start_date' (repeater > field)
		//Example: 'key' => 'dish_%_ingredients_%_ingredient' (repeater > repeater > field) Only the first repeater needs the _%_ but others won't hurt
		public function fuzzy_posts_where($where){
			$override = apply_filters('pre_nebula_fuzzy_posts_where', null, $where);
			if ( isset($override) ){return $override;}

			global $wpdb;

			if ( strpos($wpdb->remove_placeholder_escape($where), '_%_') > -1 ){
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
			return (bool) array_intersect($needles, $haystack);
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

		//Add up the filesizes of files in a directory (and it's sub-directories)
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

			return $total_size;
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
			while ( false !== $entry = $directory->read() ){ //@TODO "Nebula" 0: I don't like the assignment operator inside of this condition here. Re-write it.
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
		public function is_available($url=null, $allow_cache=true, $allow_remote_request=true){
			$override = apply_filters('pre_nebula_is_available', null, $url, $allow_cache, $allow_remote_request);
			if ( isset($override) ){return $override;}

			//Make sure the URL is valid
			if ( empty($url) || strpos($url, 'http') !== 0 ){
				trigger_error('Error: Requested URL is either empty or missing acceptable protocol.', E_USER_ERROR);
				return false;
			}

			$hostname = str_replace('.', '_', $this->url_components('hostname', $url)); //The hostname label for transients

			//Check transient first
			$site_available_buffer = get_transient('nebula_site_available_' . $hostname);
			if ( !empty($site_available_buffer) && $allow_cache ){ //If this hostname was found in a transient and specifically allowing a cached response.
				if ( $site_available_buffer === 'Available' ){
					set_transient('nebula_site_available_' . $hostname, 'Available', MINUTE_IN_SECONDS*20); //Re-up the transient with a 15 minute expiration
					return true; //This hostname has worked within the last 20 minutes
				}

				set_transient('nebula_site_available_' . $hostname, 'Unavailable', MINUTE_IN_SECONDS*10); //10 minute expiration
				return false; //This hostname has not worked within the last 10 minutes
			}

			//Make an actual request to the URL if: the transient was empty or specifically requested a non-cached response, and specifically allowing a lookup
			if ( $allow_remote_request ){
				$response = wp_remote_head($url); //Only get the head data for slight speed improvement

				if ( !is_wp_error($response) && $response['response']['code'] === 200 ){ //If the remote request was successful
					set_transient('nebula_site_available_' . $hostname, 'Available', MINUTE_IN_SECONDS*20); //20 minute expiration
					return true;
				}
			}

			if ( !$allow_remote_request ){
				return true; //Resource may not actually be available, but was asked specifically not to check.
			}

			set_transient('nebula_site_available_' . $hostname, 'Unavailable', MINUTE_IN_SECONDS*10); //10 minute expiration
			return false;
		}

		//Get a remote resource and if unavailable, don't re-check the resource for 5 minutes.
		public function remote_get($url, $args=null){
			$timer_name = str_replace(array('.', '/'), '_', $this->url_components('filename', $url));
			$timer_name = $this->timer('Remote Get (' . $timer_name . ')', 'start', 'Remote Get');

			//Must be a valid URL
			if ( empty($url) || strpos($url, 'http') !== 0 ){
				return new WP_Error('broke', 'Requested URL is either empty or missing acceptable protocol.');
			}

			$hostname = str_replace('.', '_', $this->url_components('hostname', $url));

			//Check if the resource was unavailable in the last 10 minutes
			if ( !$this->is_available($url, true, false) ){
				$this->timer($timer_name, 'end');
				return new WP_Error('unavailable', 'This resource was unavailable within the last 10 minutes.');
			}

			//Get the remote resource
			$response = wp_remote_get($url, $args);
			if ( is_wp_error($response) ){
				set_transient('nebula_site_available_' . $hostname, 'Unavailable', MINUTE_IN_SECONDS*10); //10 minute expiration
			}

			//Return the response
			set_transient('nebula_site_available_' . $hostname, 'Available', MINUTE_IN_SECONDS*20); //20 minute expiration
			$this->timer($timer_name, 'end');
			return $response;
		}

		//If this request is using AJAX or REST API. This is used to ignore non-essential functionality to speed up those requests.
		public function is_ajax_or_rest_request(){
			//Check for AJAX
			if ( wp_doing_ajax() ){
				return true;
			}

			//Check for the REST API
			if ( (defined('REST_REQUEST') && REST_REQUEST) || isset($_GET['rest_route']) ){
				return true;
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

			$red = $this->linear_channel($rgb['r'] + 1);
			$green = $this->linear_channel($rgb['g'] + 1);
			$blue = $this->linear_channel($rgb['b'] + 1);

			return 0.2126 * $red + 0.7152 * $green + 0.0722 * $blue;
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

		//Compare values using passed parameters
		public function compare_operator($a=null, $b=null, $c='=='){
			$override = apply_filters('pre_nebula_compare_operator', null, $a, $b, $c);
			if ( isset($override) ){return $override;}

			if ( empty($a) || empty($b) ){
				trigger_error('nebula_compare_operator requires values to compare.');
				return false;
			}

			switch ( $c ){
				case "=":
				case "==":
				case "===":
				case "e":
					return $a === $b;
				case ">=":
				case "=>":
				case "gte":
				case "ge":
					return $a >= $b;
				case "<=":
				case "=<":
				case "lte":
				case "le":
					return $a <= $b;
				case ">":
				case "gt":
					return $a > $b;
				case "<":
				case "lt":
					return $a < $b;
				default:
					trigger_error('nebula_compare_operator does not allow "' . $c . '".');
					return false;
			}
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
					$unique_id .= '_d' . rand(10000, 99999);
				}

				$this->server_timings[$unique_id] = array(
					'start' => microtime(true),
					'active' => true,
					'category' => $category
				);

				//Immediately stop one-off timing marks
				if ( $action === 'mark' || $action === 'once' ){
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

			//Add pre-Nebula WordPress time
			$this->server_timings['WordPress Core'] = array(
				'start' => $_SERVER['REQUEST_TIME_FLOAT'],
				'end' => $_SERVER['REQUEST_TIME_FLOAT']+$this->time_before_nebula,
				'time' => $this->time_before_nebula-$_SERVER['REQUEST_TIME_FLOAT']
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
				'start' => $_SERVER['REQUEST_TIME_FLOAT'],
				'end' => $_SERVER['REQUEST_TIME_FLOAT']+($resource_usage['ru_stime.tv_usec']),
				'time' => $resource_usage['ru_stime.tv_usec']/1000000
			);

			//User resource usage timing
			$this->server_timings['PHP User'] = array(
				'start' => $_SERVER['REQUEST_TIME_FLOAT'],
				'end' => $_SERVER['REQUEST_TIME_FLOAT']+($resource_usage['ru_utime.tv_usec']),
				'time' => $resource_usage['ru_utime.tv_usec']/1000000
			);

			//Add Nebula total
			$this->server_timings['Nebula [Total]'] = array(
				'start' => $this->time_before_nebula,
				'end' => microtime(true),
				'time' => microtime(true)-$this->time_before_nebula
			);

			//Total PHP execution time
			$this->server_timings['PHP [Total]'] = array(
				'start' => $_SERVER['REQUEST_TIME_FLOAT'],
				'end' => microtime(true),
				'time' => microtime(true)-$_SERVER['REQUEST_TIME_FLOAT']
			);

			return apply_filters('nebula_finalize_timings', $this->server_timings); //Allow functions/plugins to add/modifiy timings
		}

		//Get Nebula version information
		public function version($return=false){
			$override = apply_filters('pre_nebula_version', null, $return);
			if ( isset($override) ){return $override;}

			$return = str_replace(array(' ', '_', '-'), '', strtolower($return));

			if ( $return === 'child' && is_child_theme() ){
				return $this->child_version();
			}

			$nebula_theme_info = ( is_child_theme() )? wp_get_theme(str_replace('-child', '', get_template())) : wp_get_theme(); //Get the parent theme (regardless of if child theme is active)

			if ( $return === 'raw' ){ //Check this first to prevent needing to RegEx altogether
				return $nebula_theme_info->get('Version'); //Ex: 7.2.23.8475u
			}

			preg_match('/(?<primary>(?<major>\d+)\.(?<minor>\d+)\.(?<patch>\d+[a-z]?))\.?(?<build>\d+)?/i', $nebula_theme_info->get('Version'), $nebula_version);
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
				'utc' => strtotime($nebula_version_month . $nebula_version_day_formated . $nebula_version_year),
				'date' => $nebula_version_month . $nebula_version_day_formated . $nebula_version_year,
				'year' => $nebula_version_year,
				'month' => $nebula_version_month,
				'day' => $nebula_version_day,
			);

			switch ( $return ){
				case ('raw'): //Shouldn't ever reach this. See early return above.
					return $nebula_theme_info->get('Version'); //Ex: 7.2.19.8475u
					break;
				case ('version'):
				case ('full'):
					return $nebula_version_info['full']; //Ex: 7.2.23.8475
					break;
				case ('primary'):
					return $nebula_version_info['primary']; //Ex: 7.2.23
					break;
				case ('date'):
					return $nebula_version_info['date']; //Ex: July 23, 2019
					break;
				case ('time'):
				case ('utc'):
					return $nebula_version_info['utc']; //Ex: 1559275200
					break;
				default:
					return $nebula_version_info;
					break;
			}
		}

		//Get the child theme version information
		public function child_version(){
			if ( !is_child_theme() ){
				return $this->version('full'); //Return the parent theme version if child theme is not active
			}

			//Return a version format based on the last Sass process date if available
			if ( nebula()->get_option('scss') ){
				$build_number = (round((nebula()->get_data('scss_last_processed')-strtotime(date('Y-m-d', nebula()->get_data('scss_last_processed'))))/86400, 4)*10000)+1; //Add 1 to try to prevent trimming of trailing zeros
				$build_number = str_pad($build_number, '4', '1'); //Force a 4 digit number (by adding 1s to the right side)
				return date('y.n.j.' . $build_number, nebula()->get_data('scss_last_processed'));
			}

			//Otherwise rely on the version number in the child theme stylesheet
			$child_theme_info = wp_get_theme();
			return $child_theme_info->get('Version');
		}

		//Create Custom Properties
		public function create_hubspot_properties(){
			if ( nebula()->get_option('hubspot_portal') ){
				if ( nebula()->get_option('hubspot_api') ){
					//Get an array of all existing Hubspot CRM contact properties
					$existing_nebula_properties = $this->get_nebula_hubspot_properties();

					if ( empty($existing_nebula_properties) ){
						//Create the Nebula group of properties
						$content = '{
							"name": "nebula",
							"displayName": "Nebula",
							"displayOrder": 5
						}';

						$this->hubspot_curl('http://api.hubapi.com/contacts/v2/groups?portalId=' . nebula()->get_option('hubspot_portal'), $content);
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

					if ( count($properties_created) > 0 ){
						?>
						<div class="updated notice notice-warning">
							<p><strong>Nebula Hubspot properties created!</strong> <?php echo count($properties_created); ?> contact properties were created in Hubspot. Be sure to <a href="https://app.hubspot.com/property-settings/<?php echo nebula()->get_option('hubspot_portal'); ?>/contact" target="_blank">manually create any needed properties</a> specific to this website.</p>
							<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
						</div>
						<?php
					}
				} else {
					?>
					<div class="updated notice notice-warning">
						<p><strong>Hubspot API Key Missing!</strong> <a href="https://app.hubspot.com/hapikeys">Get your API Key</a> then <a href="themes.php?page=nebula_options&tab=apis&option=hubspot_api">enter it here</a> and re-save Nebula Options, or <a href="https://app.hubspot.com/property-settings/<?php echo nebula()->get_option('hubspot_portal'); ?>/contact" target="_blank">manually create contact properties</a>.</p>
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
			$sep = ( strpos($url, '?') === false )? '?' : '&';
			$get_url = $url . $sep . 'hapikey=' . nebula()->get_option('hubspot_api');

			if ( !empty($content) ){
				/*
					@TODO "Nebula" 0: 409 Conflict response happening. Was probably happening with cURL and just never noticed. -note: this message is from the old nvdb stuff. may not still apply here (and may be less of an issue since this only happens on options save now (instead of every pageload)
						- Because the fields already exist, Hubspot is responding with "409 Conflict".
						- This happens ~14 times since each property is sent individually.
						- I'm pretty sure the data is still transferring just fine.
						- Query Monitor is going red due to the 400-level response.
						- This is a Hubspot CRM issue, not WordPress or Nebula (as far as I can tell)
				*/

				$response = wp_remote_post($get_url, array(
					'headers' => array('Content-Type' => 'application/json'),
					'body' => $content,
				));
			} else {
				$response = wp_remote_get($get_url);
			}

			if ( !is_wp_error($response) ){
				return $response['body'];
			}

			return false;
		}
	}
}