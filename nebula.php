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

    //Backwards compatibility
    require_once get_template_directory() . '/libs/Legacy.php';


	require_once get_template_directory() . '/libs/Admin.php'; //Only require this on admin pages or if admin bar is showing...

    require_once get_template_directory() . '/libs/Ecommerce.php'; //Only require this if WooCommerce is active...

    require_once get_template_directory() . '/libs/Prototyping.php'; //Only require this if nebula option "prototype_mode" is enabled...

    /**
     * Main Plugin_Name class
     *
     * @since       1.0.0
     */
    class Nebula {
		use TemplateEngine;
		use Scripts;
		use Options;
		use Utilities;
		use Security;
		use Optimization;
		use Functions;
		use Shortcodes;

		use Admin; //Only on admin pages or if admin bar is showing...

		use Ecommerce; //Only if WooCommerce is active...

		use Prototyping; //Only if nebula option "prototype_mode" is enabled...





        /**
         * @var         Nebula $instance The one true Plugin_Name
         * @since       1.0.0
         */
        private static $instance;

        /**
         * @var         Nebula_Admin Nebula admin
         * @since       1.0.0
         */
        public $admin;

        /**
         * @var         Nebula_Automation Nebula automation
         * @since       1.0.0
         */
        public $automation;

        /**
         * @var         Nebula_Ecommerce Nebula ecommerce
         * @since       1.0.0
         */
        public $ecommerce;

        /**
         * @var         Nebula_Functions Nebula functions
         * @since       1.0.0
         */
        public $functions;

        /**
         * @var         Nebula_Optimization Nebula optimization
         * @since       1.0.0
         */
        public $optimization;

        /**
         * @var         Nebula_Options Nebula options
         * @since       1.0.0
         */
        public $options;

        /**
         * @var         Nebula_Prototyping Nebula prototyping
         * @since       1.0.0
         */
        public $prototyping;

        /**
         * @var         Nebula_Scripts Nebula scripts
         * @since       1.0.0
         */
        public $scripts;

        /**
         * @var         Nebula_Security Nebula security
         * @since       1.0.0
         */
        public $security;

        /**
         * @var         Nebula_Shortcodes Nebula shortcodes
         * @since       1.0.0
         */
        public $shortcodes;

        /**
         * @var         Nebula_Template_Engine Nebula template engine
         * @since       1.0.0
         */
        public $template_engine;

        /**
         * @var         Nebula_Utilities Nebula utilities
         * @since       1.0.0
         */
        public $utilities;

        /**
         * @var         array Registered plugins
         * @since       1.0.0
         */
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

} // End if class_exists check

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



//Commenting this out along with the others- it can be called via nebula()->register_plugin() directly.

/*
function nebula_register_plugin( $plugin_name, $plugin_dir ) {
    nebula()->register_plugin( $plugin_name, $plugin_dir );
}
*/