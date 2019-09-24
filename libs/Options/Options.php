<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Options') ){
	require_once get_template_directory() . '/libs/Options/Metaboxes.php';

	trait Options {
		use Metaboxes { Metaboxes::hooks as MetaboxesHooks;}

		public function hooks(){
			$this->MetaboxesHooks(); //Register Options Metaboxes hooks

			add_action('current_screen', array($this, 'register_options'));
			add_action('admin_menu', array($this, 'admin_sub_menu'));
			add_action('nebula_options_saved', array($this, 'create_hubspot_properties'));
			add_action('init', array($this, 'check_for_new_options'));
		}

		/*==========================
		 Global Nebula Options Conditional Functions
		 Options are for customizing Nebula functionality to the needs of each project.
		 Data is for values that are used by Nebula functionality to check for reference and conditions.
		 ===========================*/

		//Get a list of all Nebula Options categories
		public function get_option_categories(){
			$categories = array(
				array('name' => 'Metadata', 'icon' => 'fa-tags'),
				array('name' => 'Functions', 'icon' => 'fa-sliders-h'),
				array('name' => 'Analytics', 'icon' => 'fa-chart-area'),
				array('name' => 'APIs', 'icon' => 'fa-key'),
				array('name' => 'Administration', 'icon' => 'fa-briefcase'),
			);

			return apply_filters('nebula_option_categories', $categories); //Allow other functions to hook in to add categories
		}

		//Check for the option and return it or a fallback.
		public function option($option, $fallback=false){return $this->get_option($option, $fallback);}
		public function get_option($option, $fallback=false){
			$nebula_options = get_option('nebula_options');

			if ( empty($nebula_options[$option]) ){
				if ( !empty($fallback) ){
					return $fallback;
				}

				return false;
			}

			if ( filter_var($nebula_options[$option], FILTER_VALIDATE_BOOLEAN) === 1 ){
				return true;
			}

			return $nebula_options[$option];
		}

		//Update Nebula options outside of the Nebula Options page
		public function set_option($option, $value){return $this->update_option($option, $value);}
		public function update_option($option, $value){
			if ( current_user_can('manage_options') ){
				$nebula_options = get_option('nebula_options');
				if ( empty($nebula_options[$option]) || $nebula_options[$option] !== $value ){
					$nebula_options[$option] = $value;
					update_option('nebula_options', $nebula_options);
				}
			}
		}

		//Retrieve non-option Nebula data
		public function data($option){return $this->get_data($option);}
		public function get_data($option){
			$nebula_data = get_option('nebula_data');

			if ( empty($nebula_data[$option]) ){
				return false;
			}

			return $nebula_data[$option];
		}

		//Update data outside of the Nebula Options page
		public function set_data($option, $value){return $this->update_data($option, $value);}
		public function update_data($option, $value){
			$nebula_data = get_option('nebula_data');
			if ( empty($nebula_data[$option]) || $nebula_data[$option] !== $value ){
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
			//@todo "Nebula" 0: Use null coalescing operator here if possible
			if ( empty($username) ){
				$username = $this->get_option('twitter_username');
			}

			if ( !empty($username) ){
				return esc_url('https://twitter.com/' . str_replace('@', '', $username));
			}

			return false;
		}

		//Prepare default data values
		public function default_data(){
			$nebula_data_defaults = array(
				'first_activation' => '',
				'initialized' => '',
				'defaults_created' => time(),
				'first_version' => '',
				'scss_last_processed' => 0,
				'next_version' => '',
				'current_version' => $this->version('raw'),
				'current_version_date' => $this->version('date'),
				'num_theme_updates' => 0,
				'last_automated_update_date' => '',
				'last_automated_update_user' => '',
				'version_legacy' => 'false',
				'users_status' => '',
				'check_new_options' => 'false',
				'need_sass_compile' => 'false',
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

				//Functions Tab
				'jquery_version' => 'wordpress',
				'bootstrap_version' => 'latest',
				'allow_bootstrap_js' => 1,
				'limit_image_dimensions' => 0,
				'jpeg_quality' => 82, //WordPress default is 90
				'admin_bar' => 1,
				'admin_notices' => 1,
				'dev_info_metabox' => 1,
				'todo_manager_metabox' => 1,
				'performance_metabox' => 1,
				'design_reference_metabox' => 0,
				'design_reference_link' => '',
				'additional_design_references' => '',
				'author_bios' => 0,
				'comments' => 0,
				'device_detection' => 0,
				'service_worker' => 0,
				'adblock_detect' => 0,
				'domain_blacklisting' => 0,
				'theme_update_notification' => 1,
				'wp_core_updates_notify' => 1,
				'plugin_update_warning' => 0,
				'unnecessary_metaboxes' => 1,
				'scss' => 0,
				'critical_css' => 0,
				'console_css' => 1,

				//Analytics Tab
				'ga_tracking_id' => '',
				'gtm_id' => '',
				'ga_wpuserid' => 0,
				'ga_displayfeatures' => 0,
				'ga_linkid' => 0,
				'ga_anonymize_ip' => 0,
				'adwords_remarketing_conversion_id' => '',
				'google_optimize_id' => '',
				'hostnames' => '',
				'google_search_console_verification' => '',
				'facebook_custom_audience_pixel_id' => '',
				'observe_dnt' => 0,
				'cd_gacid' => '',
				'cd_hitid' => '',
				'cd_hittime' => '',
				'cd_hittype' => '',
				'cd_hitinteractivity' => '',
				'cd_hitmethod' => '',
				'cd_savedata' => '',
				'cd_reducedmotion' => '',
				'cd_offline' => '',
				'cd_devicememory' => '',
				'cd_batterymode' => '',
				'cd_batterypercent' => '',
				'cd_network' => '',
				'cd_referrer' => '',
				'cd_navigationtype' => '',
				'cd_redirectcount' => '',
				'cd_author' => '',
				'cd_businesshours' => '',
				'cd_categories' => '',
				'cd_tags' => '',
				'cd_contactmethod' => '',
				'cd_formtiming' => '',
				'cd_formflow' => '',
				'cd_geolocation' => '',
				'cd_geoname' => '',
				'cd_wpmllang' => '',
				'cd_geoaccuracy' => '',
				'cd_notablepoi' => '',
				'cd_securitynote' => '',
				'cd_relativetime' => '',
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
				'cd_publishdate' => '',
				'cd_wordcount' => '',
				'cd_blocker' => '',
				'cd_querystring' => '',
				'cd_mqbreakpoint' => '',
				'cd_mqresolution' => '',
				'cd_mqorientation' => '',
				'cd_visibilitystate' => '',

				'cm_serverresponsetime' => '',
				'cm_domreadytime' => '',
				'cm_windowloadedtime' => '',
				'cm_batterylevel' => '',
				'cm_formimpressions' => '',
				'cm_formstarts' => '',
				'cm_formsubmissions' => '',
				'cm_notabledownloads' => '',
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
				'remote_font_url' => '',
				'gcm_sender_id' => '',
				'google_server_api_key' => '',
				'google_browser_api_key' => '',
				'cse_id' => '',
				'webpagetest_api' => '',
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
				'arbitrary_code_head' => '',
				'arbitrary_code_body' => '',
				'arbitrary_code_footer' => '',

				//Administration Tab
				'dev_ip' => '',
				'dev_email_domain' => '',
				'client_ip' => '',
				'client_email_domain' => '',
				'notableiplist' => '',
				'cpanel_url' => '',
				'hosting_url' => '',
				'registrar_url' => '',
				'github_url' => '',
				'google_adsense_url' => '',
				'amazon_associates_url' => '',
				'mention_url' => '',
				'notes' => '',
				'auto_update_test' => 1,
			);

			return apply_filters('nebula_default_options', $nebula_options_defaults);
		}

		//Check for new options after the theme update. If any are found use their default value.
		public function check_for_new_options(){
			if ( $this->get_data('check_new_options') === 'true' && current_user_can('manage_options') ){
				$nebula_options = get_option('nebula_options');
				$nebula_default_options = $this->default_options();
				$different_keys = array_diff_key($nebula_default_options, $nebula_options);
				foreach ( $different_keys as $different_key => $different_value ){
					if ( is_null($nebula_options[$different_key]) ){
						$this->update_option($different_key, $nebula_default_options[$different_key]);
					}
				}

				$this->update_data('check_new_options', 'false');
			}
		}

		//Get the "user friendly" default value for a Nebula Option
		public function user_friendly_default($option){
			$nebula_options_defaults = $this->default_options();

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
			//Nebula Options page
			add_theme_page(
				'Nebula Options', //Page Title
				'Nebula Options', //Menu Title
				'manage_options', //Capabilities
				'nebula_options', //Menu Slug (Unique)
				array($this, 'options_page') //Function
			);
		}

		//Output the options page interface
		public function options_page(){
			require_once get_template_directory() . '/libs/Options/Interface.php';
		}
	}
}