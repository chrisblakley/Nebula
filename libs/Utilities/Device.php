<?php

//Note: Do not use WP Cache API such as wp_cache_set() or wp_cache_get() here because can persist across devices!
//These functions are fast enough (since they mostly just read headers) that no caching is suitable

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Device') ){
	trait Device {
		public function hooks(){
			//Add hooks here
		}

		//Boolean return if the user's device is mobile.
		public function is_mobile(){
			$override = apply_filters('pre_nebula_is_mobile', null);
			if ( isset($override) ){return $override;}

			//Check User Agent Client Hints
			if ( isset($_SERVER['HTTP_SEC_CH_UA_MOBILE']) && $_SERVER['HTTP_SEC_CH_UA_MOBILE'] === '?1' ){ //if Sec-CH-UA-Mobile client hint is ?1
				return true;
			}

			global $is_iphone;
			if ( wp_is_mobile() || $is_iphone ){
				return true;
			}

			return false;
		}

		//Boolean return if the user's device is a desktop.
		public function is_desktop(){
			$override = apply_filters('pre_nebula_is_desktop', null);
			if ( isset($override) ){return $override;}

			if ( !wp_is_mobile() ){ //This does a basic check for mobile or tablet devices.
				return true;
			}

			//Check User Agent Client Hints
			if ( isset($_SERVER['HTTP_SEC_CH_UA_MOBILE']) && $_SERVER['HTTP_SEC_CH_UA_MOBILE'] === '?0' ){ //if Sec-CH-UA-Mobile client hint is ?0
				return true;
			}

			return false;
		}

		//Returns the requested information of the operating system of the user's device.
		public function get_os($info='name'){
			$override = apply_filters('pre_nebula_get_os', null, $info);
			if ( isset($override) ){return $override;}

			//Check User Agent Client Hints
			if ( isset($_SERVER['HTTP_SEC_CH_UA_PLATFORM']) ){ //if Sec-CH-UA-Platform client hint
				return str_replace(array('"', '\\'), '', $_SERVER['HTTP_SEC_CH_UA_PLATFORM']); //Strip quotes and escapes Ex: \"macOS\"
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
					return '';
			}

			return '';
		}

		//Returns the requested information of the model of the user's device.
		public function get_device($info='model'){
			$override = apply_filters('pre_nebula_get_device', null, $info);
			if ( isset($override) ){return $override;}

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
					if ( wp_is_mobile() || $is_iphone || $this->is_mobile() ){
						return 'mobile';
					}
					return 'desktop';
				default:
					return '';
			}

			//Could consider checking/parsing the HTTP_SEC_CH_UA header here. If so, strip out quotes and escapes- Ex: str_replace(array('"', '\\'), '', $_SERVER['HTTP_SEC_CH_UA_PLATFORM'])
			return '';
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

			global $is_gecko, $is_opera, $is_safari, $is_chrome, $is_edge;

			switch ( strtolower($info) ){
				case 'full':
				case 'name':
				case 'browser':
				case 'client':
					if ( $is_opera ){return 'opera';}
					elseif ( $is_safari ){return 'safari';}
					elseif ( $is_chrome ){return 'chrome';}
					elseif ( $is_edge ){return 'edge';}

					//Check User Agent Client Hints
					if ( isset($_SERVER['HTTP_SEC_CH_UA']) ){ //if Sec-CH-UA-Platform client hint
						$ch_ua = strtolower($_SERVER['HTTP_SEC_CH_UA']);

						if ( str_contains($ch_ua, 'chrome') ){
							return 'chrome';
						}

						if ( str_contains($ch_ua, 'safari') ){
							return 'safari';
						}

						if ( str_contains($ch_ua, 'firefox') ){
							return 'firefox';
						}

						if ( str_contains($ch_ua, 'edge') ){
							return 'edge';
						}

						if ( str_contains($ch_ua, 'opera') ){
							return 'opera';
						}
					}

					return '';
				case 'engine':
					if ( $is_gecko ){return 'gecko';}
					elseif ( $is_safari ){return 'webkit';}
					return '';
				default:
					return '';
			}

			return '';
		}

		//Check the browser
		public function is_browser($browser=null){
			$override = apply_filters('pre_nebula_is_browser', null, $browser);
			if ( isset($override) ){return $override;}

			//This only checks browser name
			if ( $this->get_browser() == strtolower($browser) ){
				return true;
			}

			//Check User Agent Client Hints
			if ( isset($_SERVER['HTTP_SEC_CH_UA']) ){ //if Sec-CH-UA-Platform client hint
				$ch_ua = strtolower($_SERVER['HTTP_SEC_CH_UA']);

				if ( str_contains($ch_ua, strtolower($browser)) ){
					return true;
				}
			}

			return false;
		}

		//Get geolocation info from common server headers if available
		//Note: many servers require the feature to be enabled before these headers are sent with requests
		public function get_geo_data($datapoint){
			$override = apply_filters('pre_nebula_get_geolocation', null, $datapoint);
			if ( isset($override) ){return $override;}

			if ( empty($datapoint) ){
				return null;
			}

			//If specifically requesting lat/long data, call the other function
			if ( preg_match('/coordinates|lat|long|lng/', strtolower($datapoint)) ){
				return $this->get_geo_coordinates();
			}

			//Aliases
			if ( str_contains($datapoint, 'state') || str_contains($datapoint, 'province') ){
				$datapoint = 'region';
			}
			if ( str_contains($datapoint, 'metro') || str_contains($datapoint, 'area') ){
				$datapoint = 'metro_code';
			}
			if ( str_contains($datapoint, 'postal') || str_contains($datapoint, 'zip') ){
				$datapoint = 'postal_code';
			}

			//Default headers, but allow other systems to modify this list
			$headers = apply_filters('nebula_geolocation_headers', array(
				'country' => array('HTTP_CF_IPCOUNTRY', 'GEOIP_COUNTRY_CODE', 'CloudFront-Viewer-Country'),
				'region' => array('HTTP_CF_REGION', 'HTTP_CF_REGION_CODE', 'GEOIP_REGION_NAME'), //State
				'city' => array('HTTP_CF_IPCITY', 'GEOIP_CITY'),
				'metro_code' => array('HTTP_CF_METRO_CODE'), //Area code
				'postal_code' => array('HTTP_CF_POSTAL_CODE', 'GEOIP_POSTAL_CODE'), //Zip code
			));

			if ( !empty($headers[$datapoint]) ){
				//Normalize $_SERVER keys to lowercase with underscores
				$normalized_server = array();
				foreach ( $this->super->server as $header => $value ){
					$normalized_header = strtolower(str_replace('-', '_', $header));
					$normalized_server[$normalized_header] = $value;
				}

				//Normalize header keys and look them up
				foreach ( $headers[$datapoint] as $header ){
					$normalized_header = strtolower(str_replace('-', '_', $header));
					if ( !empty($normalized_server[$normalized_header]) ){
						$value = $normalized_server[$normalized_header];
						$lower_value = strtolower((string)$value);

						if ( $lower_value !== 'xx' && $lower_value != 555 ){ //Ignore "unknown" response values
							return $value;
						}
					}
				}
			}

			return null;
		}

		//Get geo coordinates from server headers if available
		public function get_geo_coordinates(){
			$override = apply_filters('pre_nebula_get_geo_coordinates', null);
			if ( isset($override) ){return $override;}

			//Default headers for latitude and longitude, extendable via filter
			$headers = apply_filters('nebula_geo_coordinate_headers', array(
				'latitude' => array('HTTP_CF_IPLATITUDE'),
				'longitude' => array('HTTP_CF_IPLONGITUDE'),
			));

			//Normalize $_SERVER keys
			$normalized_server = array();
			foreach ( $this->super->server as $key => $value ){
				$normalized_key = strtolower(str_replace('-', '_', $key));
				$normalized_server[$normalized_key] = $value;
			}

			//Try to find a matching latitude
			$latitude = null;
			foreach ( $headers['latitude'] as $lat_header ){
				$normalized_header = strtolower(str_replace('-', '_', $lat_header));
				if ( !empty($normalized_server[$normalized_header]) ){
					$latitude = $normalized_server[$normalized_header];
					break;
				}
			}

			//Try to find a matching longitude
			$longitude = null;
			foreach ( $headers['longitude'] as $lng_header ){
				$normalized_header = strtolower(str_replace('-', '_', $lng_header));
				if ( !empty($normalized_server[$normalized_header]) ){
					$longitude = $normalized_server[$normalized_header];
					break;
				}
			}

			if ( isset($latitude) && isset($longitude) ){
				return $latitude . ', ' . $longitude;
			}

			return null;
		}

		//Check if this traffic is coming from an AI website
		//Note: This is a helper function only until Google Analytics introduces an "Organic AI" default channel group
		public function is_ai_channel(){
			$ai_tools = array('chatgpt.com', 'openai.com', 'gemini.google.com', 'deepseek.com', 'claude.ai', 'anthropic.com', 'mistral.ai', 'copilot.microsoft.com', 'perplexity', 'pi.ai', 'huggingface.co', 'stability.ai', 'midjourney.com', 'runwayml.com', 'grok.com', 'grok.x.com', 'janitorai.com');

			foreach( $ai_tools as $ai_tool ){
				//Check the referrer
				if ( !empty($this->super->server['HTTP_REFERER']) ){
					if ( str_contains($this->super->server['HTTP_REFERER'], $ai_tool ) ){
						return true;
					}
				}

				//Check the utm_source
				if( !empty($_GET['utm_source']) ){
					if ( str_contains($_GET['utm_source'], $ai_tool) ){
						return true;
					}
				}
			}

			return false;
		}

		//Check for bot/crawler traffic
		public function is_bot(){
			if ( !empty($this->get_bot_identity()) ){
				return true;
			}

			return false;
		}

		//Get the actual name of the bot visitor
		//When $broad is true, it will also include "likely" bots
		//UA lookup: http://www.useragentstring.com/pages/Crawlerlist/
		public function get_bot_identity($broad=true){
			if ( $this->is_minimal_mode() ){return null;}
			$override = apply_filters('pre_nebula_get_bot_identity', null);
			if ( isset($override) ){return $override;}

			//Memoize so this only has to check once
			static $result = null;
			if ( isset($result) ){
				return $result;
			}

			if ( $this->is_googlebot() ){
				return 'Googlebot';
			}

			if ( $this->is_gpt_bot() ){
				return 'GPT Bot';
			}

			if ( $this->is_slackbot() ){
				return 'Slack Bot';
			}

			//Now check for other generic bot-related strings
			if ( !empty($this->super->server['HTTP_USER_AGENT']) ){
				$user_agent = strtolower($this->super->server['HTTP_USER_AGENT']);
				$user_agent = str_replace('_', '-', $user_agent); //Normalize all underscores to hyphens

				$bot_regexes = array('silktide', 'netcraft', 'w3c-validator', 'redditbot', 'discordbot', 'screaming-frog', 'archive.org-bot', 'seositecheckup', 'gtmetrix', 'semrushbot', 'ahrefsbot', 'microsoft-office', 'structured-data-testing-tool', 'chrome-lighthouse', 'curl', 'slurp', 'spider', 'crawl', 'bot'); //Arrange from least common (most specific) to most common (least specific)
				$all_bot_regexes = apply_filters('nebula_bot_regex', $bot_regexes);

				//Loop through each of the bot regex patterns
				foreach( $all_bot_regexes as $bot_pattern ){
					if ( str_starts_with($bot_pattern, '/') && strrpos($bot_pattern, '/') > 0 ){ //If a full regex pattern is provided, use it
						if ( preg_match($bot_pattern, $user_agent) ){
							return $bot_pattern;
						}
					} else { //Otherwise a partial regex pattern was provided
						$safe_pattern = '/' . preg_quote($bot_pattern, '/') . '/i';
						if ( preg_match($safe_pattern, $user_agent) ){
							return $bot_pattern;
						}
					}
				}

				//If broad is requested, we will check likely bot signatures
				if ( $broad ){
					//No need to extend this array with a filter because the previous nebula_bot_regex will work and also supports regex
					$broad_bot_user_agents = array(
						'python-requests',
						'go-http-client',
						'curl/',
						'wget/',
						'scrapy',
						'httpclient',
						'scrapy',
					);

					foreach( $broad_bot_user_agents as $broad_bot_user_agent ){
						if ( str_contains($user_agent, $broad_bot_user_agent) ){
							return $broad_bot_user_agent;
						}
					}

					//Check request endpoints that are only meant for bots
					//Note that when these endpoints don't exist, the 404 template will be used which is where these bots will be designated in analytics and security logs
					$broad_bot_request_endpoints = array(
						//'/.', //Should I consider any access to a file that begins with "." to be bot related?
						'.well-known',
						'/ads.txt',
						'/robots.txt',
						'/sitemap.xml',
						'.git',
						'.env',
						'.aws',
						'autodiscover',
						'xmlrpc',
					);

					foreach( $broad_bot_request_endpoints as $broad_bot_request_endpoint ){
						if ( str_contains(strtolower($this->requested_url()), $broad_bot_request_endpoint) ){
							return $broad_bot_request_endpoint;
						}
					}
				}
			}

			return false;
		}

		//Check if the current visitor is Googlebot (search indexing)
		//Strict mode checks against the hostname as well
		function is_googlebot($strict=false){
			if ( $this->is_minimal_mode() ){return null;}

			if ( !empty($this->super->server['HTTP_USER_AGENT']) && str_contains(strtolower($this->super->server['HTTP_USER_AGENT']), 'googlebot') ){
				if ( !empty($strict) ){
					$hostname = gethostbyaddr($this->get_ip_address(false));
					if ( preg_match('/\.googlebot|google\.com$/i', $hostname) ){
						return true; //the UA and hostname are both Google
					}

					return false; //The UA is Google, but not the hostname. Return false because strict mode was desired.
				}

				return true; //Only the UA is Googlebot (this could be easily spoofed by savvy users)
			}

			return false; //This is not Googlebot
		}

		//Check if this visitor is a known GPT (or AI) bot. Remember, there will always be more that cannot easily be detected!
		function is_ai_bot(){return $this->is_gpt_bot();}
		function is_gpt_bot(){
			if ( $this->is_minimal_mode() ){return null;}

			if ( !empty($this->super->server['HTTP_USER_AGENT']) && str_contains($this->super->server['HTTP_USER_AGENT'], 'gptbot') ){
				return true;
			}

			return false;
		}

		//Check if the current visitor is Slackbot. Keep in mind that any device can spoof this user agent.
		function is_slackbot(){
			if ( $this->is_minimal_mode() ){return null;}

			if ( !empty($this->super->server['HTTP_USER_AGENT']) && str_contains($this->super->server['HTTP_USER_AGENT'], 'Slackbot') ){
				return true;
			}

			return false;
		}
	}
}