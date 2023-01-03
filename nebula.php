<?php

if ( !defined('ABSPATH') ){ exit; } //Exit if accessed directly

if ( !class_exists('Nebula') ){
	do_action('qm/start', 'Non-WP Core (Total)'); //This is as close to WP Core finishing as we can measure. This QM measurement includes Nebula, all plugins, and child theme functionality.

	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';

	//Require Nebula libraries
	//Cannot conditionally load these as they define hooks that are used by the class which cannot be conditionally defined
	require_once get_template_directory() . '/libs/Assets.php';
	require_once get_template_directory() . '/libs/Options/Options.php';
	require_once get_template_directory() . '/libs/Utilities/Utilities.php';
	require_once get_template_directory() . '/libs/Options/Customizer.php';
	require_once get_template_directory() . '/libs/Security.php';
	require_once get_template_directory() . '/libs/Optimization.php';
	require_once get_template_directory() . '/libs/Functions.php';
	require_once get_template_directory() . '/libs/Comments.php';
	require_once get_template_directory() . '/libs/Shortcodes.php';
	require_once get_template_directory() . '/libs/Gutenberg/Gutenberg.php';
	require_once get_template_directory() . '/libs/Widgets.php';
	require_once get_template_directory() . '/libs/Admin/Admin.php';
	require_once get_template_directory() . '/libs/Ecommerce.php';

	//Main Nebula class
	class Nebula {
		use Assets { Assets::hooks as AssetsHooks; }
		use Options { Options::hooks as OptionsHooks; }
		use Utilities { Utilities::hooks as UtilitiesHooks; }
		use Customizer { Customizer::hooks as CustomizerHooks; }
		use Security { Security::hooks as SecurityHooks; }
		use Optimization { Optimization::hooks as OptimizationHooks; }
		use Functions { Functions::hooks as FunctionsHooks; }
		use Comments { Comments::hooks as CommentsHooks; }
		use Shortcodes { Shortcodes::hooks as ShortcodesHooks; }
		use Gutenberg { Gutenberg::hooks as GutenbergHooks; }
		use Widgets { Widgets::hooks as WidgetsHooks; }
		use Admin { Admin::hooks as AdminHooks; }
		use Ecommerce { Ecommerce::hooks as EcommerceHooks; }

		//Designate all future properties here first (to avoid dynamic properties). Preferably, this is done in the trait to keep everything together.
		public $super = array();
		public $time_before_nebula = 0;

		//Get active instance
		private static $instance;
		public static function instance(){
			if ( !self::$instance ){
				self::$instance = new Nebula();
				self::$instance->variables();
				self::$instance->hooks();
			}

			return self::$instance;
		}

		//Setup plugin constants
		private function variables(){
			//Constants
			define('NEBULA_VER', $this->version('raw')); //Nebula version
			define('NEBULA_DIR', get_template_directory()); //Nebula path
			define('NEBULA_URL', get_template_directory_uri()); //Nebula URL

			//Super Globals
			//Call these like normal, but using nebula()->super->get['example']
			$this->super = new Super(
				$_SERVER,
				$_GET,
				$_POST,
				$_COOKIE,
				$GLOBALS, //@todo "Nebula" 0: In PHP 8.1 make sure this doesn't trigger a runtime error with how "protected" the $GLOBALS variable is becoming
				( isset($_SESSION) )? $_SESSION : null
			);

			//Variables
			$this->time_before_nebula = microtime(true); //Prep the time before Nebula begins
		}

		//Run action and filter hooks
		private function hooks(){
			$this->AssetsHooks(); //Register Assets hooks
			$this->OptionsHooks(); //Register Options hooks
			$this->UtilitiesHooks(); //Register Utilities hooks
			$this->SecurityHooks(); //Register Security hooks
			$this->OptimizationHooks(); //Register Optimization hooks
			$this->CustomizerHooks(); //Register Customizer hooks
			$this->FunctionsHooks(); //Register Functions hooks
			$this->CommentsHooks(); //Register Comments hooks
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
	}
}

//The main function responsible for returning Nebula instance
add_action('init', 'nebula', 1); //Must call this by function handle so the child theme can also call it
function nebula(){
	return Nebula::instance();
}

//Encapsulate superglobals into a class
class Super {
	public $server;
	public $get;
	public $post;
	public $session;
	public $cookie;
	public $globals;

	public function __construct($server, $get, $post, $cookie, $globals, $session){
		$this->server = $server;
		$this->get = $get;
		$this->post = $post;
		$this->session = $session;
		$this->cookie = $cookie;
		$this->globals = $globals;
	}
}