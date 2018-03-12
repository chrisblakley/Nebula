<?php

if ( !defined('ABSPATH') ){ exit; } //Exit if accessed directly

if ( !class_exists('Nebula') ){
	require_once(ABSPATH . 'wp-admin/includes/plugin.php');
	require_once(ABSPATH . 'wp-admin/includes/file.php');

	//Require Nebula libraries
	require_once get_template_directory() . '/libs/TemplateEngine.php';
	require_once get_template_directory() . '/libs/Scripts.php';
	require_once get_template_directory() . '/libs/Options/Options.php';
	require_once get_template_directory() . '/libs/Options/Customizer.php';
	require_once get_template_directory() . '/libs/Utilities/Utilities.php';
	require_once get_template_directory() . '/libs/Security.php';
	require_once get_template_directory() . '/libs/Optimization.php';
	require_once get_template_directory() . '/libs/Functions.php';
	require_once get_template_directory() . '/libs/Shortcodes.php';
	require_once get_template_directory() . '/libs/Widgets.php';
	require_once get_template_directory() . '/libs/Admin/Admin.php';
	require_once get_template_directory() . '/libs/Ecommerce.php';
	require_once get_template_directory() . '/libs/Aliases.php';
	require_once get_template_directory() . '/libs/Legacy/Legacy.php'; //Backwards compatibility

	//Main Nebula class
	class Nebula {
		use TemplateEngine { TemplateEngine::hooks as TemplateEngineHooks;}
		use Scripts { Scripts::hooks as ScriptHooks; }
		use Options { Options::hooks as OptionsHooks; }
		use Customizer { Customizer::hooks as CustomizerHooks; }
		use Utilities { Utilities::hooks as UtilitiesHooks; }
		use Security { Security::hooks as SecurityHooks; }
		use Optimization { Optimization::hooks as OptimizationHooks; }
		use Functions { Functions::hooks as FunctionsHooks; }
		use Shortcodes { Shortcodes::hooks as ShortcodesHooks; }
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

			$this->TemplateEngineHooks(); //Register TemplateEngine hooks
			$this->ScriptHooks(); //Register Script hooks
			$this->OptionsHooks(); //Register Options hooks
			$this->UtilitiesHooks(); //Register Utilities hooks
			$this->SecurityHooks(); //Register Security hooks
			$this->OptimizationHooks(); //Register Optimization hooks
			$this->CustomizerHooks(); //Register Customizer hooks
			$this->FunctionsHooks(); //Register Functions hooks
			$this->ShortcodesHooks(); //Register Shortcodes hooks
			$this->WidgetsHooks(); //Register Widgets hooks

			if ( $this->is_admin_page() || is_admin_bar_showing() || $this->is_login_page() ){
				$this->AdminHooks(); //Register Admin hooks
			}

			if ( is_plugin_active('woocommerce/woocommerce.php') ){
				$this->EcommerceHooks(); //Register Ecommerce hooks
			}
		}

		public function session_start(){
			if ( !isset($_SESSION) ){
				session_start();
				$_SESSION['pagecount'] = 0;
			}

			$_SESSION['pagecount']++;
		}

		public function set_content_width(){
			$override = apply_filters('pre_nebula_set_content_width', false);
			if ( $override !== false ){return $override;}

			global $content_width;

			if ( is_page_template('fullwidth.php') ){
				$content_width = 1040;
			}
		}

		/**
		 * Register plugins
		 *
		 * @access  public
		 * @since   1.0.0
		 *
		 * @param   string		  $plugin_name Plugin key. Must not exceed 20 characters and may
		 *									   only contain lowercase alphanumeric characters, dashes,
		 *									   and underscores. See sanitize_key().
		 * @param   array|string	$args {
		 *	  Array of arguments for registering a plugin in nebula. As string is considered to be the
		 *	  plugin path to automatically register sass locations and templates.
		 *
		 *	  @type string	$path	   Path to the plugin directory. See plugin_dir_path().
		 *
		 *	  @type array	 $scss	   {
		 *			  Array with scss locations
		 *
		 *			  @type string	$directory  Path to scss resources directory.
		 *
		 *			  @type string	$uri		URI to scss resources directory. See plugin_dir_url().
		 *
		 *			  @type string	$imports	Path to scss partials directory.
		 *	  }
		 *
		 *	  @type string	$templates  Path to the plugin templates directory.
		 * }
		 * @return  array
		 */
		public function register_plugin($plugin_name, $args){
			$plugin_name = sanitize_key($plugin_name); //Sanitize plugin name

			if ( empty($plugin_name) || strlen($plugin_name) > 20 ){
				_doing_it_wrong(__FUNCTION__, __('Nebula plugin names must be between 1 and 20 characters in length.'), NEBULA_VER);
				return new WP_Error('nebula_plugin_name_length_invalid', __('Nebula plugin names must be between 1 and 20 characters in length.'));
			}

			//If args is the plugin path, then automatically generates an array of arguments
			if ( gettype($args) === 'string' ){
				$args = array(
					'path' => $args
				);

				//Is assets/scss and assets/scss/partials exists, then register it to use in Sass
				if ( is_dir($args['path'] . 'assets/scss') && is_dir($args['path'] . 'assets/scss/partials') ){
					$args['scss'] = array(
						'directory' => $args['path'] . 'assets/scss',
						'uri' => plugin_dir_url($args['path'] . 'assets/scss'),
						'imports' => $args['path'] . 'assets/scss/partials'
					);
				}

				// If templates directory exists, then register it to use in TemplateEngine
				if ( is_dir($args['path'] . 'templates') ){
					$args['templates'] = $args['path'] . 'templates';
				}
			} else if ( !is_array($args) || empty($args) ){
				_doing_it_wrong(__FUNCTION__, __('Nebula plugin args must be an array of arguments or a string with the path to the plugin directory.'), NEBULA_VER);
				return new WP_Error('nebula_plugin_args_invalid', __('Nebula plugin args must be an array of arguments or a string with the path to the plugin directory.'));
			}

			self::$instance->plugins[$plugin_name] = $args;

			return $args;
		}
	}

}

//The main function responsible for returning Nebula instance
add_action('init', 'nebula', 1);
function nebula(){
	return Nebula::instance();
}