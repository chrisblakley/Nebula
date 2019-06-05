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
				$this->timer('Device Detection');

				include_once get_template_directory() . '/inc/vendor/Spyc.php';
				include_once get_template_directory() . '/inc/vendor/device-detector/autoload.php'; //Note: Until the autoload.php conflict pull request is merged the autoload.php file will need the following line added: if (strpos($class, 'DeviceDetector\\') !== false) {

				$this->device = new DeviceDetector\DeviceDetector($_SERVER['HTTP_USER_AGENT']);
				$this->device->discardBotInformation(); //If called, getBot() will only return true if a bot was detected (speeds up detection a bit)
				$this->device->parse();

				$this->timer('Device Detection', 'end');
			}
		}

		//Boolean return if the user's device is mobile.
		public function is_mobile(){
			$override = apply_filters('pre_nebula_is_mobile', null);
			if ( isset($override) ){return $override;}

			if ( $this->get_option('device_detection') ){
				if ( isset($this->device) && $this->device->isMobile() ){
					return true;
				}
			}

			global $is_iphone;
			if ( wp_is_mobile() || $is_iphone ){
				return true;
			}

			return false;
		}

		//Boolean return if the user's device is a tablet.
		public function is_tablet(){
			$override = apply_filters('pre_nebula_is_tablet', null);
			if ( isset($override) ){return $override;}

			if ( $this->get_option('device_detection') ){
				if ( isset($this->device) && $this->device->isTablet() ){
					return true;
				}
			}

			return false;
		}

		//Boolean return if the user's device is a desktop.
		public function is_desktop(){
			$override = apply_filters('pre_nebula_is_desktop', null);
			if ( isset($override) ){return $override;}

			if ( $this->get_option('device_detection') ){
				if ( isset($this->device) && $this->device->isDesktop() ){
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
			if ( isset($override) ){return $override;}

			if ( $this->get_option('device_detection') ){
				$os = $this->device->getOs();
				switch ( strtolower($info) ){
					case 'full':
						return $os['name'] . ' ' . $os['version'];
					case 'name':
						return $os['name'];
					case 'version':
						return $os['version'];
					default:
						return false;
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
			}
		}

		//Check to see how the operating system version of the user's device compares to a passed version number.
		public function is_os($os=null, $version=null, $comparison='=='){
			$override = apply_filters('pre_nebula_is_os', null, $os, $version, $comparison);
			if ( isset($override) ){return $override;}

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

				$actual_os = $this->device->getOs();
				$actual_version = explode('.', $actual_os['version']);
				$version_parts = explode('.', $version);
				if ( strpos(strtolower($actual_os['name']), strtolower($os)) !== false ){
					if ( !empty($version) ){
						if ( $this->compare_operator($actual_version[0], $version_parts[0], $comparison) ){ //If major version matches
							if ( $version_parts[1] && $version_parts[1] !== 0 ){ //If minor version exists and is not 0
								if ( $this->compare_operator($actual_version[1], $version_parts[1], $comparison) ){ //If minor version matches
									return true;
								}
								return false;
							}
							return true;
						}
					}
					return true;
				}
			}

			return false;
		}

		//Returns the requested information of the model of the user's device.
		public function get_device($info='model'){
			$override = apply_filters('pre_nebula_get_device', null, $info);
			if ( isset($override) ){return $override;}

			if ( $this->get_option('device_detection') ){
				$info = str_replace(' ', '', $info);
				switch ( strtolower($info) ){
					case 'full':
						$brand_name = $this->device->getBrandName();
						$model = $this->device->getModel();
						if ( !empty($brand_name) && !empty($model) ){
							return $brand_name . ' ' . $model;
						}
						return false;
					case 'brand':
					case 'brandname':
					case 'make':
						return $this->device->getBrandName();
					case 'model':
					case 'version':
					case 'name':
						return $this->device->getModel();
					case 'type':
						return $this->device->getDeviceName();
						break;
					case 'formfactor':
						if ( $this->is_mobile() ){
							return 'mobile';
						} elseif ( $this->is_tablet() ){
							return 'tablet';
						}
						return 'desktop';
					default:
						return false;
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
					if ( wp_is_mobile() || $is_iphone ){
						return 'mobile';
					}
					return 'desktop';
				default:
					return false;
			}
		}

		//Returns the requested information of the browser being used.
		public function get_client($info){ return get_browser($info); }
		public function get_browser($info='name'){
			$override = apply_filters('pre_nebula_get_browser', null, $info);
			if ( isset($override) ){return $override;}

			//Hook in here to add custom checks to get_browser() calls
			$additional_checks = apply_filters('nebula_get_browser', false, $info);
			if ( !empty($additional_checks) ){
				return $additional_checks;
			}

			if ( $this->get_option('device_detection') ){
				$client = $this->device->getClient();
				switch ( strtolower($info) ){
					case 'full':
						return $client['name'] . ' ' . $client['version'];
					case 'name':
					case 'browser':
					case 'client':
						return $client['name'];
					case 'version':
						return $client['version'];
					case 'engine':
						return $client['engine'];
					case 'type':
						return $client['type'];
					default:
						return false;
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
					return false;
				case 'engine':
					if ( $is_gecko ){return 'gecko';}
					elseif ( $is_safari ){return 'webkit';}
					elseif ( $is_IE ){return 'trident';}
					return false;
				default:
					return false;
			}
		}

		//Check to see how the browser version compares to a passed version number.
		public function is_browser($browser=null, $version=null, $comparison='=='){
			$override = apply_filters('pre_nebula_is_browser', null, $browser, $version, $comparison);
			if ( isset($override) ){return $override;}

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

			if ( $this->get_option('device_detection') ){
				if ( empty($browser) ){
					trigger_error('nebula_is_browser requires a parameter of requested browser.');
					return false;
				}

				$actual_browser = $this->device->getClient();
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

			//Use basic detection (WordPress core) if Device Detect is not enabled. This only checks browser name (not name and version like above)
			if ( empty($version) && $this->get_browser() == strtolower($browser) ){
				return true;
			}

			return false;
		}

		//Check to see if the rendering engine matches a passed parameter.
		public function is_engine($engine=null){
			$override = apply_filters('pre_nebula_is_engine', null, $engine);
			if ( isset($override) ){return $override;}

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
			if ( isset($override) ){return $override;}

			if ( $this->get_option('device_detection') ){
				if ( isset($this->device) && $this->device->isBot() ){
					return true;
				}
			}

			$bot_regex = array('bot', 'crawl', 'spider', 'feed', 'slurp', 'tracker', 'http', 'favicon', 'curl', 'coda', 'netcraft');
			$all_bot_regex = apply_filters('nebula_bot_regex', $bot_regex);
			foreach( $all_bot_regex as $bot_regex ){
				if ( strpos(strtolower($_SERVER['HTTP_USER_AGENT']), $bot_regex) !== false ){
					return true;
				}
			}

			return false;
		}

		//Check if the current visitor is Googlebot (search indexing)
		function is_googlebot(){
			if ( strpos($_SERVER['HTTP_USER_AGENT'], 'Googlebot') ){
				$hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
				if ( preg_match('/\.googlebot|google\.com$/i', $hostname) ){
					return true;
				}
			}

			return false;
		}

	}
}