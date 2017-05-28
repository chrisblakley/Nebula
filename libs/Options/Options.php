<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Options') ){
	trait Options {
		public function hooks(){
			//Register all Nebula Options
			add_action('current_screen', array($this, 'register_options'));

			//Add Nebula admin subpages
			add_action('admin_menu', array($this, 'admin_sub_menu'));
		}

		/*==========================
		 Global Nebula Options Conditional Functions
		 Options are for customizing Nebula functionality to the needs of each project.
		 Data is for values that are used by Nebula functionality to check for reference and conditions.
		 ===========================*/

		//Check for the option and return it or a fallback.
		public function get_option($option, $fallback=false){return $this->option($option, $fallback);}
		public function option($option, $fallback=false){
			$nebula_options = get_option('nebula_options');

			if ( empty($nebula_options[$option]) ){
				if ( !empty($fallback) ){
					return $fallback;
				}

				return false;
			}

			if ( filter_var($nebula_options[$option], FILTER_VALIDATE_BOOLEAN) == 1 ){
				return true;
			}

			return $nebula_options[$option];
		}

		//Update Nebula options outside of the Nebula Options page
		public function update_option($option, $value){
			$nebula_data = get_option('nebula_options');
			if ( $nebula_data[$option] != $value ){
				$nebula_data[$option] = $value;
				update_option('nebula_options', $nebula_data);
			}
		}

		//Retrieve non-option Nebula data
		public function get_data($option){
			$nebula_data = get_option('nebula_data');
			if ( empty($nebula_data[$option]) ){
				return false;
			}
			return $nebula_data[$option];
		}

		//Update data outside of the Nebula Options page
		public function update_data($option, $value){
			$nebula_data = get_option('nebula_data');
			if ( $nebula_data[$option] != $value ){
				$nebula_data[$option] = $value;
				update_option('nebula_data', $nebula_data);
			}
		}

		/*==========================
		 Specific Options Functions
		 When using in templates these simplify the syntax to be less confusing.
		 ===========================*/

		public function full_address($encoded=false){
			$nebula_options = get_option('nebula_options');

			if ( !$nebula_options['street_address'] ){
				return false;
			}

			$full_address = $nebula_options['street_address'] . ', ' . $nebula_options['locality'] . ', ' . $nebula_options['region'] . ' ' . $nebula_options['postal_code'];
			if ( $encoded ){
				$full_address = str_replace(array(', ', ' '), '+', $full_address);
			}
			return $full_address;
		}

		//Get the full Twitter URL for a user
		public function twitter_url($username=false){
			if ( empty($username) ){
				$username = nebula()->option('twitter_username');
			}

			if ( !empty($username) ){
				return 'https://twitter.com/' . $username;
			}

			return false;
		}

		//Register or return the requested Bootstrap file.
		public function bootstrap($file=false){
			if ( nebula()->option('bootstrap_version') === 'bootstrap3' ){
				//Bootstrap 3 (IE8+ Support)
				if ( $file === 'css' ){
					return wp_register_style('nebula-bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css', null, '3.3.7', 'all');
				} elseif ( $file === 'js' ){
					return nebula_register_script('nebula-bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js', 'defer', null, '3.3.7', true);
				} elseif ( $file === 'reboot' ){
					return false;
				} else {
					return 'v3';
				}
			} elseif ( nebula()->option('bootstrap_version') === 'bootstrap4a5' ){
				//Bootstrap 4 alpha 5 (IE9+ Support)
				if ( $file === 'css' ){
					return wp_register_style('nebula-bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha.5/css/bootstrap.min.css', null, '4.0.0a5', 'all');
				} elseif ( $file === 'js' ){
					return nebula()->register_script('nebula-bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha.5/js/bootstrap.min.js', 'defer', null, '4.0.0a5', true);
				} elseif ( $file === 'reboot' ){
					return 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha.5/css/bootstrap-reboot.min.css';
				} else {
					return 'v4a5';
				}
			}

			//Latest (IE10+)
			if ( $file === 'css' ){
				return wp_register_style('nebula-bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha.6/css/bootstrap.min.css', null, '4.0.0a6', 'all');
			} elseif ( $file === 'js' ){
				return nebula()->register_script('nebula-bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha.6/js/bootstrap.min.js', 'defer', null, '4.0.0a6', true);
			} elseif ( $file === 'reboot' ){
				return 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha.6/css/bootstrap-reboot.min.css';
			} else {
				return 'latest';
			}
		}

		//Prepare default data values
		public function default_data(){
			$nebula_data_defaults = array(
				'first_activation' => '',
				'initialized' => '',
				'scss_last_processed' => 0,
				'next_version' => '',
				'current_version' => nebula()->version('raw'),
				'current_version_date' => nebula()->version('date'),
				'version_legacy' => 'false',
				'users_status' => '',
			);
			return $nebula_data_defaults;
		}

		//Prepare default option values
		public function default_options(){
			$nebula_options_defaults = array(
				'edited_yet' => 'false',

				//Metadata Tab
				'business_type' => '',
				'site_owner' => '',
				'contact_email' => '',
				'notification_email' => '',
				'phone_number' => '',
				'fax_number' => '',
				'latitude' => '',
				'longitude' => '',
				'street_address' => '',
				'locality' => '',
				'region' => '',
				'postal_code' => '',
				'country_name' => '',
				'business_hours_sunday_enabled' => '',
				'business_hours_sunday_open' => '',
				'business_hours_sunday_close' => '',
				'business_hours_monday_enabled' => '',
				'business_hours_monday_open' => '',
				'business_hours_monday_close' => '',
				'business_hours_tuesday_enabled' => '',
				'business_hours_tuesday_open' => '',
				'business_hours_tuesday_close' => '',
				'business_hours_wednesday_enabled' => '',
				'business_hours_wednesday_open' => '',
				'business_hours_wednesday_close' => '',
				'business_hours_thursday_enabled' => '',
				'business_hours_thursday_open' => '',
				'business_hours_thursday_close' => '',
				'business_hours_friday_enabled' => '',
				'business_hours_friday_open' => '',
				'business_hours_friday_close' => '',
				'business_hours_saturday_enabled' => '',
				'business_hours_saturday_open' => '',
				'business_hours_saturday_close' => '',
				'business_hours_closed' => '',
				'facebook_url' => '',
				'facebook_page_id' => '',
				'facebook_admin_ids' => '',
				'facebook_app_secret' => '',
				'facebook_access_token' => '',
				'twitter_username' => '',
				'linkedin_url' => '',
				'youtube_url' => '',
				'instagram_url' => '',
				'pinterest_url' => '',
				'google_plus_url' => '',

				//Functions Tab
				'bootstrap_version' => 'latest',
				'prototype_mode' => 0,
				'wireframe_theme' => '',
				'staging_theme' => '',
				'production_theme' => '',
				'admin_bar' => 1,
				'admin_notices' => 1,
				'dev_info_metabox' => 1,
				'todo_manager_metabox' => 1,
				'author_bios' => 0,
				'comments' => 0,
				'device_detection' => 0,
				'ip_geolocation' => 0,
				'adblock_detect' => 0,
				'domain_blacklisting' => 0,
				'weather' => 0,
				'theme_update_notification' => 1,
				'wp_core_updates_notify' => 1,
				'plugin_update_warning' => 1,
				'unnecessary_metaboxes' => 1,
				'visitors_db' => 0,
				'scss' => 0,
				'minify_css' => 0,
				'dev_stylesheets' => 0,
				'console_css' => 1,

				//Analytics Tab
				'ga_tracking_id' => '',
				'ga_wpuserid' => 0,
				'ga_displayfeatures' => 0,
				'ga_linkid' => 0,
				'adwords_remarketing_conversion_id' => '',
				'google_optimize_id' => '',
				'hostnames' => '',
				'google_search_console_verification' => '',
				'facebook_custom_audience_pixel_id' => '',
				'cd_author' => '',
				'cd_businesshours' => '',
				'cd_categories' => '',
				'cd_tags' => '',
				'cd_contactmethod' => '',
				'cd_formtiming' => '',
				'cd_firstinteraction' => '',
				'cd_geolocation' => '',
				'cd_geoname' => '',
				'cd_geoaccuracy' => '',
				'cd_notablepoi' => '',
				'cd_relativetime' => '',
				'cd_scrolldepth' => '',
				'cd_sessionid' => '',
				'cd_timestamp' => '',
				'cd_windowtype' => '',
				'cd_userid' => '',
				'cd_fbid' => '',
				'cd_role' => '',
				'cd_videowatcher' => '',
				'cd_eventintent' => '',
				'cd_woocart' => '',
				'cd_woocustomer' => '',
				'cd_weather' => '',
				'cd_temperature' => '',
				'cd_publishyear' => '',
				'cd_wordcount' => '',
				'cd_adblocker' => '',
				'cd_querystring' => '',
				'cd_mqbreakpoint' => '',
				'cd_mqresolution' => '',
				'cd_mqorientation' => '',

				'cm_formimpressions' => '',
				'cm_formstarts' => '',
				'cm_formsubmissions' => '',
				'cm_notabledownloads' => '',
				'cm_engagedreaders' => '',
				'cm_videoplaytime' => '',
				'cm_videostarts' => '',
				'cm_videocompletions' => '',
				'cm_autocompletesearches' => '',
				'cm_autocompletesearchclicks' => '',
				'cm_pagevisible' => '',
				'cm_pagehidden' => '',
				'cm_wordcount' => '',
				'cm_maxscroll' => '',

				//APIs Tab
				'google_font_url' => '',
				'gcm_sender_id' => '',
				'google_server_api_key' => '',
				'google_browser_api_key' => '',
				'cse_id' => '',
				'hubspot_api' => '',
				'hubspot_portal' => '',
				'disqus_shortname' => '',
				'facebook_app_id' => '',
				'twitter_consumer_key' => '',
				'twitter_consumer_secret' => '',
				'twitter_bearer_token' => '',
				'instagram_user_id' => '',
				'instagram_access_token' => '',
				'instagram_client_id' => '',
				'instagram_client_secret' => '',

				//Administration Tab
				'dev_ip' => '',
				'dev_email_domain' => '',
				'client_ip' => '',
				'client_email_domain' => '',
				'notableiplist' => '',
				'cpanel_url' => '',
				'hosting_url' => '',
				'registrar_url' => '',
				'google_adsense_url' => '',
				'amazon_associates_url' => '',
				'mention_url' => '',
				'notes' => '',
			);
			return $nebula_options_defaults;
		}

		//Get the "user friendly" default value for a Nebula Option
		public function user_friendly_default($option){
			$nebula_options_defaults = nebula()->default_options();

			if ( $nebula_options_defaults[$option] === '' ){
				return 'None';
			}

			if ( empty($nebula_options_defaults[$option]) ){
				return 'Off';
			}

			if ( $nebula_options_defaults[$option] === 1 ){
				return 'On';
			}

			return ucwords($nebula_options_defaults[$option]);
		}

		//Register all Nebula Options
		public function register_options(){
			$current_screen = get_current_screen();
			if ( $current_screen->base === 'appearance_page_nebula_options' || $current_screen->base === 'options' ){
				register_setting('nebula_options', 'nebula_options');
			}
		}

		//Nebula admin subpages
		public function admin_sub_menu(){
			add_theme_page('Nebula Options', 'Nebula Options', 'manage_options', 'nebula_options', array($this, 'options_page')); //Nebula Options page
		}

		//Output the options page
		public function options_page(){
			require_once(get_template_directory() . '/libs/Options/Interface.php');
		}
	}
}