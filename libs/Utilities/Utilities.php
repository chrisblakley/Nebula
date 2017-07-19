<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Utilities') ){
	require_once get_template_directory() . '/libs/Utilities/Analytics.php';
	require_once get_template_directory() . '/libs/Utilities/Visitors.php';
	require_once get_template_directory() . '/libs/Utilities/Device.php';
	require_once get_template_directory() . '/libs/Utilities/Sass.php';

	trait Utilities {
		use Analytics { Analytics::hooks as AnalyticsHooks;}
		use Visitors { Visitors::hooks as VisitorsHooks;}
		use Device { Device::hooks as DeviceHooks;}
		use Sass { Sass::hooks as SassHooks;}

		public function hooks(){
			add_filter('posts_where' , array($this, 'fuzzy_posts_where'));
			$this->AnalyticsHooks(); //Register Analytics hooks
			$this->VisitorsHooks(); //Register Visitors hooks
			$this->DeviceHooks(); //Register Device hooks
			$this->SassHooks(); //Register Sass hooks
			register_shutdown_function(array($this, 'ga_log_fatal_errors'));
		}

		//Generate Nebula Session ID
		public function nebula_session_id(){
			$session_info = ( $this->is_debug() )? 'dbg.' : '';
			$session_info .= ( $this->get_option('prototype_mode') )? 'prt.' : '';

			if ( $this->is_client() ){
				$session_info .= 'cli.';
			} elseif ( $this->is_dev() ){
				$session_info .= 'dev.';
			}

			if ( is_user_logged_in() ){
				$user_info = get_userdata(get_current_user_id());
				$role_abv = 'ukn';
				if ( !empty($user_info->roles) ){
					$role_abv = substr($user_info->roles[0], 0, 3);
				}
				$session_info .= 'u:' . get_current_user_id() . '.r:' . $role_abv . '.';
			}

			$session_info .= ( $this->is_bot() )? 'bot.' : '';

			$wp_session_id = ( session_id() )? session_id() : '!' . uniqid();
			$ga_cid = $this->ga_parse_cookie();

			$site_live = '';
			if ( !$this->is_site_live() ){
				$site_live = '.n';
			}

			return time() . '.' . $session_info . 's:' . $wp_session_id . '.c:' . $ga_cid . $site_live;
		}

		//Detect Notable POI
		public function poi($ip=null){
			if ( empty($ip) ){
				$ip = $_SERVER['REMOTE_ADDR'];
			}

			if ( $this->get_option('notableiplist') ){
				$notable_ip_lines = explode("\n", $this->get_option('notableiplist'));
				foreach ( $notable_ip_lines as $line ){
					$ip_info = explode(' ', strip_tags($line), 2); //0 = IP Address or RegEx pattern, 1 = Name
					if ( ($ip_info[0][0] === '/' && preg_match($ip_info[0], $ip)) || $ip_info[0] == $ip ){ //If regex pattern and matches IP, or if direct match
						return str_replace(array("\r\n", "\r", "\n"), '', $ip_info[1]);
						break;
					}
				}
			} elseif ( isset($_GET['poi']) ){ //If POI query string exists
				return str_replace(array('%20', '+'), ' ', $_GET['poi']);
			}

			return false;
		}

		//Alias for a less confusing is_admin() function to try to prevent security issues
		public function is_admin_page(){
			return is_admin();
		}

		//Check if viewing the login page.
		public function is_login_page(){
			return in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'));
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
			if ( isset($override) ){return;}

			if ( empty($strict) ){
				$devIPs = explode(',', $this->get_option('dev_ip'));
				if ( !empty($devIPs) ){
					foreach ( $devIPs as $devIP ){
						$devIP = trim($devIP);

						if ( !empty($devIP) && $devIP[0] != '/' && $devIP == $_SERVER['REMOTE_ADDR'] ){
							return true;
						}

						if ( !empty($devIP) && $devIP[0] === '/' && preg_match($devIP, $_SERVER['REMOTE_ADDR']) ){
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
						if ( trim($devEmail) == $current_user_domain ){
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
			if ( isset($override) ){return;}

			if ( empty($strict) ){
				$clientIPs = explode(',', $this->get_option('client_ip'));
				if ( !empty($clientIPs) ){
					foreach ( $clientIPs as $clientIP ){
						$clientIP = trim($clientIP);

						if ( !empty($clientIP) && $clientIP[0] != '/' && $clientIP == $_SERVER['REMOTE_ADDR'] ){
							return true;
						}

						if ( !empty($clientIP) && $clientIP[0] === '/' && preg_match($clientIP, $_SERVER['REMOTE_ADDR']) ){
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
						if ( trim($clientEmail) == $current_user_domain ){
							return true;
						}
					}
				}
			}

			return false;
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
			if ( isset($override) ){return;}

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
			if ( isset($override) ){return;}

			if ( $this->get_option('hostnames') ){
				if ( strpos($this->get_option('hostnames'), $this->url_components('hostname', home_url())) >= 0 ){
					return true;
				}
				return false;
			}
			return true;
		}

		//If the request was made via AJAX
		public function is_ajax_request(){
			if ( !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ){
				return true;
			}

			return false;
		}

		//Valid Hostname Regex
		public function valid_hostname_regex($domains=null){
			$domains = ( $domains )? $domains : array($this->url_components('domain'));
			$settingsdomains = ( $this->get_option('hostnames') )? explode(',', $this->get_option('hostnames')) : array($this->url_components('domain'));
			$fulldomains = array_merge($domains, $settingsdomains, array('googleusercontent.com')); //Enter ONLY the domain and TLD. The wildcard subdomain regex is automatically added.
			$fulldomains = preg_filter('/^/', '.*', $fulldomains);
			$fulldomains = str_replace(array(' ', '.', '-'), array('', '\.', '\-'), $fulldomains); //@TODO "Nebula" 0: Add a * to capture subdomains. Final regex should be: \.*gearside\.com|\.*gearsidecreative\.com
			$fulldomains = array_unique($fulldomains);
			return implode("|", $fulldomains);
		}

		//Get the full URL. Not intended for secure use ($_SERVER var can be manipulated by client/server).
		public function requested_url($host="HTTP_HOST"){ //Can use "SERVER_NAME" as an alternative to "HTTP_HOST".
			$override = apply_filters('pre_nebula_requested_url', null, $host);
			if ( isset($override) ){return;}

			$protocol = ( is_ssl() )? 'https' : 'http';
			$full_url = $protocol . '://' . $_SERVER["$host"] . $_SERVER["REQUEST_URI"];
			return $full_url;
		}

		//Separate a URL into it's components.
		public function url_components($segment="all", $url=null){
			$override = apply_filters('pre_nebula_url_components', null, $segment, $url);
			if ( isset($override) ){return;}

			//If URL is not passed, get the current page URL.
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

					if ( $host[0] == 'www' ){
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

					if ( $host[0] != 'www' && $host[0] != $sld ){
						return $host[0];
					} else {
						return false;
					}
					break;

				case ('domain') : //In http://example.com the domain is "example.com"
					if ( $relative ){
						return false;
					}

					if( isset($domain[0]) ) {
						return $domain[0];
					}
					break;

				case ('basedomain'): //In http://example.com/something the basedomain is "http://example.com"
				case ('base_domain'):
				case ('origin') :
					if ( $relative ){
						return false;
					}

					if( isset($url_components['scheme']) ){
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
					if( isset($url_components['path']) ) {
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
				case ('extension'): //The extension only (without ".")
					if ( strpos(basename($url_components['path']), '.') !== false ){
						$file_parts = explode('.', $url_components['path']);
						return $file_parts[1];
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
					if( isset($url_components['query']) ){
						return $url_components['query'];
					}
					break;

				case ('fragment'):
				case ('fragments'):
				case ('anchor'):
				case ('hash') :
				case ('hashtag'):
				case ('id'):
					if( isset($url_components['fragment']) ){
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
					setcookie($name, $string_value, strtotime('January 1, 2035'), COOKIEPATH, COOKIE_DOMAIN); //Note: Do not let this cookie expire past 2038 or it instantly expires.
				}
			}
		}

		//Fuzzy meta sub key finder (Used to query ACF nested repeater fields).
		//Example: 'key' => 'dates_%_start_date',
		public function fuzzy_posts_where($where){
			$override = apply_filters('pre_nebula_fuzzy_posts_where', null, $where);
			if ( isset($override) ){return;}

			if ( strpos($where, '_%_') > -1 ){
				$where = preg_replace("/meta_key = ([\'\"])(.+)_%_/", "meta_key LIKE $1$2_%_", $where);
			}
			return $where;
		}

		//Text limiter by words
		public function string_limit_words($string, $word_limit){
			$override = apply_filters('pre_string_limit_words', null, $string, $word_limit);
			if ( isset($override) ){return;}

			$limited['text'] = $string;
			$limited['is_limited'] = false;
			$words = explode(' ', $string, ($word_limit+1));
			if ( count($words) > $word_limit ){
				array_pop($words);
				$limited['text'] = implode(' ', $words);
				$limited['is_limited'] = true;
			}
			return $limited;
		}

		//Word limiter by characters
		public function word_limit_chars($string, $charlimit, $continue=false){
			$override = apply_filters('pre_word_limit_chars', null, $string, $charlimit, $continue);
			if ( isset($override) ){return;}

			//1 = "Continue Reading", 2 = "Learn More"
			if ( strlen(strip_tags($string, '<p><span><a>')) <= $charlimit ){
				$newString = strip_tags($string, '<p><span><a>');
			} else {
				$newString = preg_replace('/\s+?(\S+)?$/', '', substr(strip_tags($string, '<p><span><a>'), 0, ($charlimit + 1)));
				if ( $continue == 1 ){
					$newString = $newString . '&hellip;' . ' <a class="continuereading" href="'. get_permalink() . '">Continue reading <span class="meta-nav">&rarr;</span></a>';
				} elseif( $continue == 2 ){
					$newString = $newString . '&hellip;' . ' <a class="continuereading" href="'. get_permalink() . '">Learn more &raquo;</a>';
				} else {
					$newString = $newString . '&hellip;';
				}
			}
			return $newString;
		}

		//Traverse multidimensional arrays
		public function contains($needle, $haystack){return $this->in_array_r($needle, $haystack, 'contains');}
		public function in_array_r($needle, $haystack, $strict=true){
			$override = apply_filters('pre_in_array_r', null, $needle, $haystack, $strict);
			if ( isset($override) ){return;}

			foreach ( $haystack as $item ){
				if ( $strict === true ){ //If strict, match the type and the value
					if ( $item === $needle ){
						return true;
					}
				} else {
					if ( $strict === 'contains' ){ //If strict is 'contains', check if the item contains the needle
						if ( stripos($item, $needle) !== false ){
							return true;
						}
					} elseif ( $item == $needle ){ //Otherwise check if the item matches the needle (regardless of type)
						return true;
					}
				}

				if ( is_array($item) && in_array_r($needle, $item, $strict) ){ //If the item is an array, recursively check that array
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
			if ( isset($override) ){return;}

			$files = glob($pattern, $flags);
			foreach ( glob(dirname($pattern) . '/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir ){
				$files = array_merge($files, $this->glob_r($dir . '/' . basename($pattern), $flags));
			}

			return $files;
		}

		//Add up the filesizes of files in a directory (and it's sub-directories)
		public function foldersize($path){
			$override = apply_filters('pre_foldersize', null, $path);
			if ( isset($override) ){return;}

			$total_size = 0;
			$files = scandir($path);
			$cleanPath = rtrim($path, '/') . '/';
			foreach ( $files as $file ){
				if ( $file <> "." && $file <> ".."){
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

		//Check if a value is a UTC Timestamp
		//This function only validates UTC timestamps between April 26, 1970 and May 18, 2033 to avoid conflicts (like phone numbers).
		public function is_utc_timestamp($timestamp){
			//If the timestamp contains any non-digit
			if ( preg_match('/\D/i', $timestamp) ){
				return false;
			}

			//If the timestamp is greater than May 18, 2033 (This function only supports up to this date to avoid conflicts with phone numbers. We'll have to figure out a new solution then.)
			if ( strlen($timestamp) == 10 && substr($timestamp, 0, 1) > 1 ){
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
		public function is_available($url=null, $nocache=false, $lookup_only=false){
			$override = apply_filters('pre_nebula_is_available', null, $url, $nocache, $lookup_only);
			if ( isset($override) ){return;}

			if ( empty($url) || strpos($url, 'http') !== 0 ){
				trigger_error('Error: Requested URL is either empty or missing acceptable protocol.', E_USER_ERROR);
				return false;
			}

			$hostname = str_replace('.', '_', $this->url_components('hostname', $url));

			$site_available_buffer = get_transient('nebula_site_available_' . $hostname);
			if ( !empty($site_available_buffer) && !$nocache ){
				if ( $site_available_buffer === 'Available' ){
					return true;
				}

				set_transient('nebula_site_available_' . $hostname, 'Unavailable', MINUTE_IN_SECONDS*10); //10 minute expiration
				return false;
			}

			if ( (empty($site_available_buffer) || $nocache) && !$lookup_only ){
				$response = wp_remote_get($url);
				if ( !is_wp_error($response) && $response['response']['code'] === 200 ){
					set_transient('nebula_site_available_' . $hostname, 'Available', MINUTE_IN_SECONDS*10); //10 minute expiration
					return true;
				}
			}

			if ( $lookup_only ){
				return true; //Resource may not actually be available, but was asked specifically not to check.
			}

			set_transient('nebula_site_available_' . $hostname, 'Unavailable', MINUTE_IN_SECONDS*10); //10 minute expiration
			return false;
		}

		//Get a remote resource and if unavailable, don't re-check the resource for 5 minutes.
		public function remote_get($url, $args=null){
			//Must be a valid URL
			if ( empty($url) || strpos($url, 'http') !== 0 ){
				return new WP_Error('broke', 'Requested URL is either empty or missing acceptable protocol.');
			}

			$hostname = str_replace('.', '_', $this->url_components('hostname', $url));

			//Check if the resource was unavailable in the last 10 minutes
			if ( !$this->is_available($url, false, true) ){
				return new WP_Error('unavailable', 'This resource was unavailable within the last 10 minutes.');
			}

			//Get the remote resource
			$response = wp_remote_get($url, $args);
			if ( is_wp_error($response) ){
				set_transient('nebula_site_available_' . $hostname, 'Unavailable', MINUTE_IN_SECONDS*10); //10 minute expiration
			}

			//Return the response
			set_transient('nebula_site_available_' . $hostname, 'Available', MINUTE_IN_SECONDS*10); //10 minute expiration
			return $response;
		}

		//Check the brightness of a color. 0=darkest, 255=lightest, 256=false
		public function color_brightness($hex){
			$override = apply_filters('pre_nebula_color_brightness', null, $hex);
			if ( isset($override) ){return;}

			if ( strpos($hex, '#') !== false ){
				preg_match("/#(?:[0-9a-fA-F]{3,6})/i", $hex, $hex_colors);

				if ( strlen($hex_colors[0]) == 4 ){
					$values = str_split($hex_colors[0]);
					$full_hex = '#' . $values[1] . $values[1] . $values[2] . $values[2] . $values[3] . $values[3];
				} else {
					$full_hex = $hex_colors[0];
				}

				$hex = str_replace('#', '', $full_hex);
				$hex_r = hexdec(substr($hex, 0, 2));
				$hex_g = hexdec(substr($hex, 2, 2));
				$hex_b = hexdec(substr($hex, 4, 2));

				return (($hex_r*299)+($hex_g*587)+($hex_b*114))/1000;
			}
			return 256;
		}

		//Compare values using passed parameters
		public function compare_operator($a=null, $b=null, $c='=='){
			$override = apply_filters('pre_nebula_compare_operator', null, $a, $b, $c);
			if ( isset($override) ){return;}

			if ( empty($a) || empty($b) ){
				trigger_error('nebula_compare_operator requires values to compare.');
				return false;
			}

			switch ( $c ){
				case "=":
				case "==":
				case "e":
					return $a == $b;
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

		//Get Nebula version information
		public function version($return=false){
			$override = apply_filters('pre_nebula_version', null, $return);
			if ( isset($override) ){return;}

			$return = str_replace(array(' ', '_', '-'), '', strtolower($return));
			$nebula_theme_info = ( is_child_theme() )? wp_get_theme(str_replace('-child', '', get_template())) : wp_get_theme();

			if ( $return === 'raw' ){ //Check this first to prevent needing to RegEx altogether
				return $nebula_theme_info->get('Version');
			}

			preg_match('/(?<primary>(?<large>\d+)\.(?<medium>\d+)\.(?<small>\d+[a-z]?))\.?(?<tiny>\d+)?/i', $nebula_theme_info->get('Version'), $nebula_version);
			$nebula_version['small'] = preg_replace('/\D/', '', $nebula_version['small']); //Remove letters from small number

			$nebula_version_year = ( $nebula_version['medium'] >= 8 )? 2012+$nebula_version['large']+1 : 2012+$nebula_version['large'];
			$nebula_months = array('May', 'June', 'July', 'August', 'September', 'October', 'November', 'December', 'January', 'February', 'March', 'April');
			$nebula_version_month = $nebula_months[$nebula_version['medium']];
			$nebula_version_day = ( empty($nebula_version['small']) )? '' : $nebula_version['small'];
			$nebula_version_day_formated = ( empty($nebula_version['small']) )? ' ' : ' ' . $nebula_version['small'] . ', ';

			$nebula_version_info = array(
				'full' => $nebula_version[0],
				'primary' => $nebula_version['primary'],
				'large' => $nebula_version['large'],
				'medium' => $nebula_version['medium'],
				'small' => $nebula_version['small'],
				'tiny' => $nebula_version['tiny'],
				'utc' => strtotime($nebula_version_month . $nebula_version_day_formated . $nebula_version_year),
				'date' => $nebula_version_month . $nebula_version_day_formated . $nebula_version_year,
				'year' => $nebula_version_year,
				'month' => $nebula_version_month,
				'day' => $nebula_version_day,
			);

			switch ( $return ){
				case ('raw'): //Shouldn't ever be
					return $nebula_theme_info->get('Version');
					break;
				case ('version'):
				case ('full'):
					return $nebula_version_info['full'];
					break;
				case ('primary'):
					return $nebula_version_info['primary'];
					break;
				case ('date'):
					return $nebula_version_info['date'];
					break;
				case ('time'):
				case ('utc'):
					return $nebula_version_info['utc'];
					break;
				default:
					return $nebula_version_info;
					break;
			}
		}
	}
}