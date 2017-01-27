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
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'Nebula' ) ) {

    /**
     * Main Plugin_Name class
     *
     * @since       1.0.0
     */
    class Nebula {

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
                self::$instance->includes();
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
         * Include necessary files
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function includes() {
            // Includes classes
            // TODO: Folder should be includes
            require_once NEBULA_DIR . '/functions/admin/admin.php';
            require_once NEBULA_DIR . '/functions/automation.php';
            require_once NEBULA_DIR . '/functions/ecommerce.php';
            require_once NEBULA_DIR . '/functions/functions.php';
            require_once NEBULA_DIR . '/functions/optimization.php';
            require_once NEBULA_DIR . '/functions/options.php';
            require_once NEBULA_DIR . '/functions/prototyping.php';
            require_once NEBULA_DIR . '/functions/scripts.php';
            require_once NEBULA_DIR . '/functions/security.php';
            require_once NEBULA_DIR . '/functions/shortcodes.php';
            require_once NEBULA_DIR . '/functions/template-engine.php';
            require_once NEBULA_DIR . '/functions/template-functions.php';
            require_once NEBULA_DIR . '/functions/utilities/utilities.php';

            // Compatibility backwards
            require_once NEBULA_DIR . '/functions/legacy.php';
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

            self::$instance->plugins = array();

            self::$instance->options = new Nebula_Options();
            self::$instance->utilities = new Nebula_Utilities();

            // Initialize classes
            if ( is_admin() || is_admin_bar_showing() ) {
                self::$instance->admin = new Nebula_Admin();
            }
            if ( is_admin() ) {
                self::$instance->automation = new Nebula_Automation();
            }
            //Include functions for ecommerce websites
            if ( is_plugin_active('woocommerce/woocommerce.php') ) {
                self::$instance->ecommerce = new Nebula_Ecommerce(); // TODO: Should go in a Woocommerce plugin integration
            }
            self::$instance->functions = new Nebula_Functions();
            self::$instance->optimization = new Nebula_Optimization();

            self::$instance->prototyping = new Nebula_Prototyping();
            self::$instance->scripts = new Nebula_Scripts();
            self::$instance->security = new Nebula_Security();
            self::$instance->shortcodes = new Nebula_Shortcodes();
            self::$instance->template_engine = new Nebula_Template_Engine();
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

            if ( is_page_template('tpl-fullwidth.php') ){
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
function nebula() {
    return Nebula::instance();
}
add_action( 'init', 'nebula', 1 );

function nebula_register_plugin( $plugin_name, $plugin_dir ) {
    nebula()->register_plugin( $plugin_name, $plugin_dir );
}