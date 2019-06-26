<?php

if ( !defined('ABSPATH') ){ exit; } //Exit if accessed directly

if ( !class_exists('Nebula') ){
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';

	//Require Nebula libraries
	require_once get_template_directory() . '/libs/Assets.php';
	require_once get_template_directory() . '/libs/Options/Options.php';
	require_once get_template_directory() . '/libs/Options/Customizer.php';
	require_once get_template_directory() . '/libs/Utilities/Utilities.php';
	require_once get_template_directory() . '/libs/Security.php';
	require_once get_template_directory() . '/libs/Optimization.php';
	require_once get_template_directory() . '/libs/Functions.php';
	require_once get_template_directory() . '/libs/Shortcodes.php';
	require_once get_template_directory() . '/libs/Gutenberg/Gutenberg.php';
	require_once get_template_directory() . '/libs/Widgets.php';
	require_once get_template_directory() . '/libs/Admin/Admin.php';
	require_once get_template_directory() . '/libs/Ecommerce.php';
	require_once get_template_directory() . '/libs/Aliases.php';
	require_once get_template_directory() . '/libs/Legacy/Legacy.php'; //Backwards compatibility

	//Main Nebula class
	class Nebula {
		use Assets { Assets::hooks as AssetsHooks; }
		use Options { Options::hooks as OptionsHooks; }
		use Customizer { Customizer::hooks as CustomizerHooks; }
		use Utilities { Utilities::hooks as UtilitiesHooks; }
		use Security { Security::hooks as SecurityHooks; }
		use Optimization { Optimization::hooks as OptimizationHooks; }
		use Functions { Functions::hooks as FunctionsHooks; }
		use Shortcodes { Shortcodes::hooks as ShortcodesHooks; }
		use Gutenberg { Gutenberg::hooks as GutenbergHooks; }
		use Widgets { Widgets::hooks as WidgetsHooks; }
		use Admin { Admin::hooks as AdminHooks; }
		use Ecommerce { Ecommerce::hooks as EcommerceHooks; }
		use Legacy { Legacy::hooks as LegacyHooks; }

		private static $instance;
		public $plugins = array();

		//Get active instance
		public static function instance(){
			if ( !self::$instance ){
				self::$instance = new Nebula();
				self::$instance->constants();
				self::$instance->variables();
				self::$instance->hooks();
			}

			return self::$instance;
		}

		//Setup plugin constants
		private function constants(){
			define('NEBULA_VER', $this->version('raw')); //Nebula version
			define('NEBULA_DIR', get_template_directory()); //Nebula path
			define('NEBULA_URL', get_template_directory_uri()); //Nebula URL
		}

		//Set variables
		private function variables(){
			$this->time_before_nebula = microtime(true); //Prep the time before Nebula begins

			global $content_width;
			//$content_width is a global variable used by WordPress for max image upload sizes and media embeds (in pixels).
			//If the content area is 960px wide, set $content_width = 940; so images and videos will not overflow.
			if ( !isset($content_width) ){
				$content_width = 710;
			}
		}

		//Run action and filter hooks
		private function hooks(){
			//Start a session
			add_action('init', array($this, 'session_start'), 1);

			//Adjust the content width when the full width page template is being used
			add_action('template_redirect', array($this, 'set_content_width'));

			$this->AssetsHooks(); //Register Assets hooks
			$this->OptionsHooks(); //Register Options hooks
			$this->UtilitiesHooks(); //Register Utilities hooks
			$this->SecurityHooks(); //Register Security hooks
			$this->OptimizationHooks(); //Register Optimization hooks
			$this->CustomizerHooks(); //Register Customizer hooks
			$this->FunctionsHooks(); //Register Functions hooks
			$this->ShortcodesHooks(); //Register Shortcodes hooks
			$this->GutenbergHooks(); //Register Gutenberg hooks
			$this->WidgetsHooks(); //Register Widgets hooks

			if ( $this->is_admin_page() || is_admin_bar_showing() || $this->is_login_page() ){
				$this->AdminHooks(); //Register Admin hooks
			}

			if ( is_plugin_active('woocommerce/woocommerce.php') ){
				$this->EcommerceHooks(); //Register Ecommerce hooks
			}
		}

		public function session_start(){
			if ( !$this->is_ajax_or_rest_request() && is_writable(session_save_path()) ){ //If not an AJAX/REST request and the session directory is writable
				if ( !session_id() ){
					session_start(); //This breaks the Theme Editor for some reason, so we don't do it on AJAX requests
				}

				if ( !isset($_SESSION['pagecount']) ){
					$_SESSION['pagecount'] = 1;
				} else {
					$_SESSION['pagecount']++;
				}
			}
		}

		public function set_content_width(){
			$override = apply_filters('pre_nebula_set_content_width', false);
			if ( $override !== false ){return $override;}

			global $content_width;

			if ( is_page_template('fullwidth.php') ){
				$content_width = 1040;
			}
		}
	}
}

//The main function responsible for returning Nebula instance
add_action('init', 'nebula', 1);
function nebula(){
	return Nebula::instance();
}