<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Device') ){
	trait Device {
		public function hooks(){
			add_action('init', array($this, 'detect'));
		}

		/*==========================
		 User Agent Parsing Functions/Helpers
		 ===========================*/

		//Device Detection - https://github.com/matomo-org/device-detector
		public function detect(){
			if ( $this->get_option('device_detection') ){
					require_once(get_template_directory() . '/inc/vendor/device-detector/DeviceDetector.php'); //Be careful when updating this library. DeviceDetector.php requires modification to work without Composer!
					$GLOBALS["device_detect"] = new DeviceDetector\DeviceDetector($_SERVER['HTTP_USER_AGENT']);
					$GLOBALS["device_detect"]->discardBotInformation(); //If called, getBot() will only return true if a bot was detected (speeds up detection a bit)
					$GLOBALS["device_detect"]->parse();
			}
		}

		//Boolean return if the user's device is mobile.
		public function is_mobile(){
			$override = apply_filters('pre_nebula_is_mobile', null);
			if ( isset($override) ){return;}

			if ( $this->get_option('device_detection') ){
				if ( isset($GLOBALS["device_detect"]) && $GLOBALS["device_detect"]->isMobile() ){
					return true;
				}
			}

			global $is_iphone;
			if ( $is_iphone ){
				return true;
			}

			return false;
		}

		//Boolean return if the user's device is a tablet.
		public function is_tablet(){
			$override = apply_filters('pre_nebula_is_tablet', null);
			if ( isset($override) ){return;}

			if ( $this->get_option('device_detection') ){
				if ( isset($GLOBALS["device_detect"]) && $GLOBALS["device_detect"]->isTablet() ){
					return true;
				}
			}

			return false;
		}

		//Boolean return if the user's device is a desktop.
		public function is_desktop(){
			$override = apply_filters('pre_nebula_is_desktop', null);
			if ( isset($override) ){return;}

			if ( $this->get_option('device_detection') ){
				if ( isset($GLOBALS["device_detect"]) && $GLOBALS["device_detect"]->isDesktop() ){
					return true;
				}
			}

			if ( !wp_is_mobile() ){ //This does a basic check for mobile or tablet devices.
				return true;
			}

			return false;
		}

		//Returns the requested information of the operating system of the user's device.
		public function get_os($info='full'){
			$override = apply_filters('pre_nebula_get_os', null, $info);
			if ( isset($override) ){return;}

			if ( $this->get_option('device_detection') ){
				$os = $GLOBALS["device_detect"]->getOs();
				switch ( strtolower($info) ){
					case 'full':
						return $os['name'] . ' ' . $os['version'];
						break;
					case 'name':
						return $os['name'];
						break;
					case 'version':
						return $os['version'];
						break;
					default:
						return false;
						break;
				}
			}

			global $is_iphone;
			switch ( strtolower($info) ){
				case 'full':
				case 'name':
					if ( $is_iphone ){
						return 'ios';
					}
					break;
				default:
					return false;
					break;
			}
		}

		//Check to see how the operating system version of the user's device compares to a passed version number.
		public function is_os($os=null, $version=null, $comparison='=='){
			$override = apply_filters('pre_nebula_is_os', null, $os, $version, $comparison);
			if ( isset($override) ){return;}

			if ( $this->get_option('device_detection') ){
				if ( empty($os) ){
					trigger_error('nebula_is_os requires a parameter of requested operating system.');
					return false;
				}

				switch ( strtolower($os) ){
					case 'macintosh':
						$os = 'mac';
						break;
					case 'win':
						$os = 'windows';
						break;
				}

				$actual_os = $GLOBALS["device_detect"]->getOs();
				$actual_version = explode('.', $actual_os['version']);
				$version_parts = explode('.', $version);
				if ( strpos(strtolower($actual_os['name']), strtolower($os)) !== false ){
					if ( !empty($version) ){
						if ( $this->compare_operator($actual_version[0], $version_parts[0], $comparison) ){ //If major version matches
							if ( $version_parts[1] && $version_parts[1] !== 0 ){ //If minor version exists and is not 0
								if ( $this->compare_operator($actual_version[1], $version_parts[1], $comparison) ){ //If minor version matches
									return true;
								} else {
									return false;
								}
							} else {
								return true;
							}
						}
					} else {
						return true;
					}
				}
			}

			return false;
		}

		//Returns the requested information of the model of the user's device.
		public function get_device($info='model'){
			$override = apply_filters('pre_nebula_get_device', null, $info);
			if ( isset($override) ){return;}

			if ( $this->get_option('device_detection') ){
				$info = str_replace(' ', '', $info);
				switch ( strtolower($info) ){
					case 'full':
						return $GLOBALS["device_detect"]->getBrandName() . ' ' . $GLOBALS["device_detect"]->getModel();
						break;
					case 'brand':
					case 'brandname':
					case 'make':
						return $GLOBALS["device_detect"]->getBrandName();
						break;
					case 'model':
					case 'version':
					case 'name':
						return $GLOBALS["device_detect"]->getModel();
						break;
					case 'type':
						return $GLOBALS["device_detect"]->getDeviceName();
						break;
					case 'formfactor':
						if ( $this->is_mobile() ){
							return 'mobile';
						} elseif ( $this->is_tablet() ){
							return 'tablet';
						} else {
							return 'desktop';
						}
					default:
						return false;
						break;
				}
			}

			global $is_iphone;
			$info = str_replace(' ', '', $info);
			switch ( strtolower($info) ){
				case 'brand':
				case 'brandname':
				case 'make':
				case 'model':
				case 'name':
				case 'type':
					if ( $is_iphone ){
						return 'iphone';
					}
					break;
				case 'formfactor':
					if ( $is_iphone ){
						return 'mobile';
					}
					break;
				default:
					return false;
					break;
			}
		}

		//Check for the Tor browser
		//Nebula only calls this function if Device Detection option is enabled, but it can still be called manually.
		public function is_tor_browser(){
			$override = apply_filters('pre_is_tor_browser', null);
			if ( isset($override) ){return;}

			//Check session and cookies first
			if ( (isset($GLOBALS['tor']) && $GLOBALS['tor'] === true) || (isset($_SESSION['tor']) && $_SESSION['tor'] === true) || (isset($_COOKIE['tor']) && $_COOKIE['tor'] == 'true') ){
				$GLOBALS['tor'] = true;
				return true;
			}

			if ( (isset($GLOBALS['tor']) && $GLOBALS['tor'] === false) && (isset($_SESSION['tor']) && $_SESSION['tor'] === false) ){
				$GLOBALS['tor'] = false;
				return false;
			}

			//Scrape entire exit IP list
			if ( isset($_SERVER['REMOTE_ADDR']) ){
				$tor_list = get_transient('nebula_tor_list');
				if ( empty($tor_list) || $this->is_debug() ){ //If transient expired or is debug
					$response = $this->remote_get('https://check.torproject.org/cgi-bin/TorBulkExitList.py?ip=' . $_SERVER['SERVER_ADDR']);
					if ( !is_wp_error($response) ){
						$tor_list = $response['body'];
						set_transient('nebula_tor_list', $tor_list, HOUR_IN_SECONDS*48);
					}
				}

				//Parse the file
				if ( !empty($tor_list) ){
					foreach( explode("\n", $tor_list) as $line ){
						if ( !empty($line) && strpos($line, '#') === false ){
							if ( $line === $_SERVER['REMOTE_ADDR'] ){
								$this->set_global_session_cookie('tor', true);
								return true;
							}
						}
					}
				}
			}

			//Check individual exit point
			//Note: This would make a remote request to every new user. Commented out for optimization. Use the override filter to enable in a child theme.
			/*
			if ( $this->is_available('http://torproject.org') ){
				$remote_ip_octets = explode(".", $_SERVER['REMOTE_ADDR']);
				$server_ip_octets = explode(".", $_SERVER['SERVER_ADDR']);
				if ( gethostbyname($remote_ip_octets[3] . "." . $remote_ip_octets[2] . "." . $remote_ip_octets[1] . "." . $remote_ip_octets[0] . "." . $_SERVER['SERVER_PORT'] . "." . $remote_ip_octets[3] . "." . $remote_ip_octets[2] . "." . $remote_ip_octets[1] . "." . $remote_ip_octets[0] . ".ip-port.exitlist.torproject.org") === "127.0.0.2" ){
			        $this->set_global_session_cookie('tor', true);
					return true;
			    }
		    }
			*/

			$this->set_global_session_cookie('tor', false, array('global', 'session'));
			return false;
		}

		//Returns the requested information of the browser being used.
		public function get_client($info){ return get_browser($info); }
		public function get_browser($info='name'){
			$override = apply_filters('pre_nebula_get_browser', null, $info);
			if ( isset($override) ){return;}

			if ( $this->get_option('device_detection') ){
				$client = $GLOBALS["device_detect"]->getClient();
				if ( $this->is_tor_browser() ){
					$client = array(
						'name' => 'Tor',
						'version' => 0, //Not possible to detect
						'engine' => 'Gecko',
						'type' => 'browser',
					);
				}

				switch ( strtolower($info) ){
					case 'full':
						return $client['name'] . ' ' . $client['version'];
						break;
					case 'name':
					case 'browser':
					case 'client':
						return $client['name'];
						break;
					case 'version':
						return $client['version'];
						break;
					case 'engine':
						return $client['engine'];
						break;
					case 'type':
						return $client['type'];
						break;
					default:
						return false;
						break;
				}
			}

			global $is_gecko, $is_IE, $is_opera, $is_safari, $is_chrome;
			switch ( strtolower($info) ){
				case 'full':
				case 'name':
				case 'browser':
				case 'client':
					if ( $is_IE ){return 'internet explorer';}
					elseif ( $is_opera ){return 'opera';}
					elseif ( $is_safari ){return 'safari';}
					elseif ( $is_chrome ){return 'chrome';}
					break;
				case 'engine':
					if ( $is_gecko ){return 'gecko';}
					elseif ( $is_safari ){return 'webkit';}
					elseif ( $is_IE ){return 'trident';}
					break;
				default:
					return false;
					break;
			}
		}

		//Check to see how the browser version compares to a passed version number.
		public function is_browser($browser=null, $version=null, $comparison='=='){
			$override = apply_filters('pre_nebula_is_browser', null, $browser, $version, $comparison);
			if ( isset($override) ){return;}

			if ( $this->get_option('device_detection') ){
				if ( empty($browser) ){
					trigger_error('nebula_is_browser requires a parameter of requested browser.');
					return false;
				}

				switch ( strtolower($browser) ){
					case 'ie':
						$browser = 'internet explorer';
						break;
					case 'ie7':
						$browser = 'internet explorer';
						$version = '7';
						break;
					case 'ie8':
						$browser = 'internet explorer';
						$version = '8';
						break;
					case 'ie9':
						$browser = 'internet explorer';
						$version = '9';
						break;
					case 'ie10':
						$browser = 'internet explorer';
						$version = '10';
						break;
					case 'ie11':
						$browser = 'internet explorer';
						$version = '11';
						break;
				}

				$actual_browser = $GLOBALS["device_detect"]->getClient();
				$actual_version = explode('.', $actual_browser['version']);
				$version_parts = explode('.', $version);
				if ( strpos(strtolower($actual_browser['name']), strtolower($browser)) !== false ){
					if ( !empty($version) ){
						if ( $this->compare_operator($actual_version[0], $version_parts[0], $comparison) ){ //Major version comparison
							if ( !empty($version_parts[1]) ){ //If minor version exists and is not 0
								if ( $this->compare_operator($actual_version[1], $version_parts[1], $comparison) ){ //Minor version comparison
									return true;
								} else {
									return false;
								}
							} else {
								return true;
							}
						}
					} else {
						return true;
					}
				}
			}

			return false;
		}

		//Check to see if the rendering engine matches a passed parameter.
		public function is_engine($engine=null){
			$override = apply_filters('pre_nebula_is_engine', null, $engine);
			if ( isset($override) ){return;}

			if ( $this->get_option('device_detection') ){
				if ( empty($engine) ){
					trigger_error('is_engine requires a parameter of requested engine.');
					return false;
				}

				switch ( strtolower($engine) ){
					case 'ie':
					case 'internet explorer':
						$engine = 'trident';
						break;
					case 'web kit':
						$engine = 'webkit';
						break;
				}

				$actual_engine = $this->get_browser('engine');
				if ( strpos(strtolower($actual_engine), strtolower($engine)) !== false ){
					return true;
				}
			}

			return false;
		}

		//Check for bot/crawler traffic
		//UA lookup: http://www.useragentstring.com/pages/Crawlerlist/
		public function is_bot(){
			$override = apply_filters('pre_nebula_is_bot', null);
			if ( isset($override) ){return;}

			if ( $this->get_option('device_detection') ){
				if ( isset($GLOBALS["device_detect"]) && $GLOBALS["device_detect"]->isBot() ){
					return true;
				}
			}

			$bot_regex = array('bot', 'crawl', 'spider', 'feed', 'slurp', 'tracker', 'http', 'favicon', 'curl', 'coda', 'netcraft');
			$all_bot_regex = apply_filters('nebula_bot_regex', $bot_regex);
			foreach( $all_bot_regex as $bot_regex ){
				if ( strpos(strtolower($_SERVER['HTTP_USER_AGENT']), $bot_regex) !== false ){
					return true;
					break;
				}
			}

			return false;
		}
	}
}