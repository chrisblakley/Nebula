<?php
/**
 * Nebula
 *
 * @package     Nebula
 * @since       1.0.0
 * @author      Ruben Garcia
 * @contributor Chris Blakley
 */

// Exit if accessed directly
if ( !defined('ABSPATH') ){ exit; }

if ( !class_exists('Nebula') ){

	require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');

	//Require Nebula libraries
	require_once get_template_directory() . '/libs/TemplateEngine.php';
	require_once get_template_directory() . '/libs/Scripts.php';
	require_once get_template_directory() . '/libs/Options.php';
	require_once get_template_directory() . '/libs/Utilities.php';
	require_once get_template_directory() . '/libs/Security.php';
	require_once get_template_directory() . '/libs/Optimization.php';
	require_once get_template_directory() . '/libs/Functions.php';
	require_once get_template_directory() . '/libs/Shortcodes.php';
	require_once get_template_directory() . '/libs/Admin.php';
    require_once get_template_directory() . '/libs/Ecommerce.php';
    require_once get_template_directory() . '/libs/Prototyping.php';
    require_once get_template_directory() . '/libs/Legacy.php'; //Backwards compatibility (Limited)

    /**
     * Main Nebula class
     *
     * @since       1.0.0
     */
    class Nebula {
		use TemplateEngine { TemplateEngine::hooks as TemplateEngineHooks;}
		use Scripts { Scripts::hooks as ScriptHooks; }
		use Options { Options::hooks as OptionsHooks; }
		use Utilities { Utilities::hooks as UtilitiesHooks; }
		use Security { Security::hooks as SecurityHooks; }
		use Optimization { Optimization::hooks as OptimizationHooks; }
		use Functions { Functions::hooks as FunctionsHooks; }
		use Shortcodes { Shortcodes::hooks as ShortcodesHooks; }
		use Admin { Admin::hooks as AdminHooks; }
		use Ecommerce { Ecommerce::hooks as EcommerceHooks; }
		use Prototyping { Prototyping::hooks as PrototypingHooks; }

        private static $instance;
        public $plugins;

        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      object self::$instance The one true Nebula
         */
        public static function instance() {
            if( !self::$instance ) {
                self::$instance = new Nebula();
                self::$instance->constants();
                self::$instance->variables();
                self::$instance->hooks();
            }

            return self::$instance;
        }

        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function constants() {
            // Nebula version
            define( 'NEBULA_VER', '1.0.0' );

            // Nebula path
            define( 'NEBULA_DIR', get_template_directory() );

            // Nebula URL
            define( 'NEBULA_URL', get_template_directory_uri() );
        }

        /**
         * Set variables
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function variables() {
            global $content_width;

            //$content_width is a global variable used by WordPress for max image upload sizes and media embeds (in pixels).
            //If the content area is 960px wide, set $content_width = 940; so images and videos will not overflow.
            if ( !isset($content_width) ){
                $content_width = 710;
            }
        }

        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function hooks() {
            //Start a session
            add_action('init', array( $this, 'session_start' ), 1);

            //Adjust the content width when the full width page template is being used
            add_action('template_redirect', array( $this, 'set_content_width' ) );

            $this->TemplateEngineHooks(); // Register TemplateEngine hooks
            $this->ScriptHooks(); // Register Script hooks
            $this->OptionsHooks(); // Register Options hooks
            $this->SecurityHooks(); // Register Security hooks
            $this->OptimizationHooks(); // Register Optimization hooks
            $this->FunctionsHooks(); // Register Functions hooks
            $this->ShortcodesHooks(); // Register Shortcodes hooks

			if ( nebula()->is_admin_page() || is_admin_bar_showing() ){
            	$this->AdminHooks(); // Register Admin hooks
			}

			if ( is_plugin_active('woocommerce/woocommerce.php') ){
            	$this->EcommerceHooks(); // Register Ecommerce hooks
			}

			if ( nebula()->option('prototype_mode') ){
            	$this->PrototypingHooks(); // Register Prototyping hooks
			}
        }

        public function session_start(){
            if ( !isset($_SESSION) ){
                session_start();
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

        /**
         * Register plugins
         *
         * @access      public
         * @since       1.0.0
         * @return      array
         */
        public function register_plugin( $plugin_name, $plugin_dir ) {
            $plugin_features = array(
                'path' =>  $plugin_dir,
                'stylesheets' =>  is_dir( $plugin_dir . 'stylesheets' ),
                'templates' =>  is_dir( $plugin_dir . 'templates' ),
            );

            self::$instance->plugins[$plugin_name] = $plugin_features;

            return $plugin_features;
        }
    }

}

/**
 * The main function responsible for returning Nebula instance
 *
 * @since       1.0.0
 * @return      Nebula The one true Nebula
 */
add_action('init', 'nebula', 1);
function nebula(){
    return Nebula::instance();
}
