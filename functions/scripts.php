<?php
/**
 * Scripts
 *
 * @package     Nebula\Scripts
 * @since       1.0.0
 * @author      Ruben Garcia
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'Nebula_Scripts' ) ) {

    class Nebula_Scripts {

        public $script_parameters;

        public function __construct() {
            // Register scripts
            add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );
            add_action( 'login_enqueue_scripts', array( $this, 'register_scripts' ) );

            // Enqueue frontend scripts
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

            // Enqueue login scripts
            add_action( 'login_enqueue_scripts', array( $this, 'login_enqueue_scripts' ) );

            // Enqueue admin scripts
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        }

        /**
         * Register scripts
         *
         * @since       1.0.0
         * @return      void
         */
        public function register_scripts() {
            // Stylesheets
            //wp_register_style($handle, $src, $dependencies, $version, $media);
            if ( nebula_option('google_font_url') ){
                wp_register_style('nebula-google_font', nebula_option('google_font_url'), array(), null, 'all');
            }
            nebula_bootrap_version('css');
            wp_register_style('nebula-font_awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', null, '4.7.0', 'all');
            wp_register_style('nebula-mmenu', 'https://cdnjs.cloudflare.com/ajax/libs/jQuery.mmenu/5.7.8/css/jquery.mmenu.all.css', null, '5.7.8', 'all');
            wp_register_style('nebula-datatables', 'https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.13/css/jquery.dataTables.min.css', null, '1.10.13', 'all'); //Datatables is called via main.js only as needed.
            wp_register_style('nebula-chosen', 'https://cdnjs.cloudflare.com/ajax/libs/chosen/1.6.2/chosen.min.css', null, '1.6.2', 'all');
            wp_register_style('nebula-jquery_ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css', null, '1.12.1', 'all');
            wp_register_style('nebula-main', NEBULA_URL . '/style.css', array('nebula-bootstrap', 'nebula-mmenu'), null, 'all');
            wp_register_style('nebula-login', NEBULA_URL . '/stylesheets/css/login.css', null, null);
            wp_register_style('nebula-admin', NEBULA_URL . '/stylesheets/css/admin.css', null, null);

            // Scripts
            //Use CDNJS to pull common libraries: http://cdnjs.com/
            //nebula_register_script($handle, $src, $exec, $dependencies, $version, $in_footer);
            nebula_bootrap_version('js');
            nebula_register_script('nebula-modernizr_dev', get_template_directory_uri() . '/js/libs/modernizr.dev.js', 'defer', null, '3.3.1', false);
            nebula_register_script('nebula-modernizr_local', get_template_directory_uri() . '/js/libs/modernizr.min.js', 'defer', null, '3.3.1', false);
            nebula_register_script('nebula-modernizr', 'https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js', 'defer', null, '2.8.3', false); //https://github.com/cdnjs/cdnjs/issues/6100
            nebula_register_script('nebula-jquery_ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', 'defer', null, '1.12.1', true);
            nebula_register_script('nebula-mmenu', 'https://cdnjs.cloudflare.com/ajax/libs/jQuery.mmenu/5.7.8/js/jquery.mmenu.all.min.js', 'defer', null, '5.7.8', true);
            nebula_register_script('nebula-froogaloop', 'https://f.vimeocdn.com/js/froogaloop2.min.js', null, null, null, true);
            nebula_register_script('nebula-tether', 'https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js', 'defer', null, '1.4.0', true); //This is not enqueued or dependent because it is called via main.js only as needed.
            nebula_register_script('nebula-datatables', 'https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.13/js/jquery.dataTables.min.js', 'defer', null, '1.10.13', true); //Datatables is called via main.js only as needed.
            nebula_register_script('nebula-chosen', 'https://cdnjs.cloudflare.com/ajax/libs/chosen/1.6.2/chosen.jquery.min.js', 'defer', null, '1.6.2', true);
            nebula_register_script('nebula-autotrack', 'https://cdnjs.cloudflare.com/ajax/libs/autotrack/1.1.0/autotrack.js', 'async', null, '1.1.0', true);
            nebula_register_script('performance-timing', get_template_directory_uri() . '/js/libs/performance-timing.js', 'defer', null, null, false);
            nebula_register_script('nebula-main', get_template_directory_uri() . '/js/main.js', 'defer', array('nebula-bootstrap', 'jquery', 'nebula-jquery_ui'), null, true);
            nebula_register_script('nebula-login', get_template_directory_uri() . '/js/login.js', null, array('jquery'), null, true);
            nebula_register_script('nebula-admin', get_template_directory_uri() . '/js/admin.js', 'defer', null, null, true);

            global $wp_scripts, $wp_styles, $upload_dir;
            $upload_dir = wp_upload_dir();

            //Prep Nebula styles for JS object
            $nebula_styles = array();
            foreach ( $wp_styles->registered as $handle => $data ){
                if ( strpos($handle, 'nebula-') !== false && strpos($handle, 'admin') === false && strpos($handle, 'login') === false ){ //If the handle contains "nebula-" but not "admin" or "login"
                    $nebula_styles[str_replace(array('nebula-', '-'), array('', '_'), $handle)] = str_replace(array('?defer', '?async'), '', $data->src);
                }
            }

            //Prep Nebula scripts for JS object
            $nebula_scripts = array();
            foreach ( $wp_scripts->registered as $handle => $data ){
                if ( strpos($handle, 'nebula-') !== false && strpos($handle, 'admin') === false && strpos($handle, 'login') === false ){ //If the handle contains "nebula-" but not "admin" or "login"
                    $nebula_scripts[str_replace(array('nebula-', '-'), array('', '_'), $handle)] = str_replace(array('?defer', '?async'), '', $data->src);
                }
            }

            //Be careful changing the following array as many JS functions use this data!
            $this->script_parameters = array(
                'site' => array(
                    'name' => get_bloginfo('name'),
                    'directory' => array(
                        'template' => array(
                            'path' => get_template_directory(),
                            'uri' => get_template_directory_uri(),
                        ),
                        'stylesheet' => array(
                            'path' => get_stylesheet_directory(),
                            'uri' => get_stylesheet_directory_uri(),
                        ),
                    ),
                    'home_url' => home_url(),
                    'domain' => nebula_url_components('domain'),
                    'protocol' => nebula_url_components('protocol'),
                    'language' => get_bloginfo('language'),
                    'ajax' => array(
                        'url' => admin_url('admin-ajax.php'),
                        'nonce' => wp_create_nonce('nebula_ajax_nonce'),
                    ),
                    'upload_dir' => $upload_dir['baseurl'],
                    'ecommerce' => false,
                    'options' => array(
                        'gaid' => nebula_option('ga_tracking_id'),
                        'nebula_cse_id' => nebula_option('cse_id'),
                        'nebula_google_browser_api_key' => nebula_option('google_browser_api_key'),
                        'facebook_url' => nebula_option('facebook_url'),
                        'facebook_app_id' => nebula_option('facebook_app_id'),
                        'twitter_url' => nebula_option('twitter_url'),
                        'google_plus_url' => nebula_option('google_plus_url'),
                        'linkedin_url' => nebula_option('linkedin_url'),
                        'youtube_url' => nebula_option('youtube_url'),
                        'instagram_url' => nebula_option('instagram_url'),
                        'manage_options' => current_user_can('manage_options'),
                        'debug' => is_debug(),
                        'visitors_db' => nebula_option('visitors_db'),
                        'hubspot_api' => ( nebula_option('hubspot_api') )? true : false,
                    ),
                    'resources' => array(
                        'css' => $nebula_styles,
                        'js' => $nebula_scripts,
                    ),
                ),
                'post' => ( is_search() )? null : array( //Conditional prevents wrong ID being used on search results
                    'id' => get_the_id(),
                    'permalink' => get_the_permalink(),
                    'title' => urlencode(get_the_title()),
                    'author' => urlencode(get_the_author()),
                    'year' => get_the_date('Y'),
                ),
                'dom' => null,
            );

            //Check for session data
            if ( isset($_SESSION['nebulaSession']) && json_decode($_SESSION['nebulaSession'], true) ){ //If session exists and is valid JSON
                $this->script_parameters['session'] = json_decode($_SESSION['nebulaSession'], true); //Replace nebula.session with session data
            } else {
                $this->script_parameters['session'] = array(
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'id' => nebula_session_id(),
                    'flags' => array(
                        'adblock' => false,
                        'gablock' => false,
                    ),
                );
            }

            $user_info = get_userdata(get_current_user_id());

            //User Data
            $this->script_parameters['user'] = array(
                'ip' => $_SERVER['REMOTE_ADDR'],
                'nid' => get_nebula_id(),
                'cid' => ga_parse_cookie(),
                'client' => array( //Client data is here inside user because the cookie is not transferred between clients.
                    'bot' => nebula_is_bot(),
                    'remote_addr' => $_SERVER['REMOTE_ADDR'],
                    'device' => array(
                        'full' => nebula_get_device('full'),
                        'formfactor' => nebula_get_device('formfactor'),
                        'brand' => nebula_get_device('brand'),
                        'model' => nebula_get_device('model'),
                        'type' => nebula_get_device('type'),
                    ),
                    'os' => array(
                        'full' => nebula_get_os('full'),
                        'name' => nebula_get_os('name'),
                        'version' => nebula_get_os('version'),
                    ),
                    'browser' => array(
                        'full' => nebula_get_browser('full'),
                        'name' => nebula_get_browser('name'),
                        'version' => nebula_get_browser('version'),
                        'engine' => nebula_get_browser('engine'),
                        'type' => nebula_get_browser('type'),
                    ),
                ),
            );
        }

        /**
         * Enqueue frontend scripts
         *
         * @since       1.0.0
         * @return      void
         */
        function enqueue_scripts( $hook ) {
            //Stylesheets
            if ( nebula_option('google_font_url') ){
                wp_enqueue_style('nebula-google_font');
            }
            wp_enqueue_style('nebula-bootstrap');
            wp_enqueue_style('nebula-mmenu');
            wp_enqueue_style('nebula-jquery_ui');
            wp_enqueue_style('nebula-font_awesome');
            wp_enqueue_style('nebula-main');

            //Scripts
            wp_enqueue_script('jquery');
            wp_enqueue_script('nebula-jquery_ui');
            //wp_enqueue_script('nebula-modernizr_dev');
            wp_enqueue_script('nebula-modernizr_local'); //@todo "Nebula" 0: Switch this back to CDN when version 3 is on CDNJS
            wp_enqueue_script('nebula-mmenu');
            wp_enqueue_script('nebula-bootstrap');
            wp_enqueue_script('nebula-autotrack');
            wp_enqueue_script('nebula-main');

            //Localized objects (localized to jquery to appear in <head>)
            wp_localize_script('jquery', 'nebula', $this->script_parameters);

            //Conditionals
            if ( is_debug() ){ //When ?debug query string is used
                wp_enqueue_script('nebula-performance_timing');
                //wp_enqueue_script('nebula-mmenu_debugger');
            }

            if ( is_page_template('tpl-search.php') ){ //Form pages (that use selects) or Advanced Search Template. The Chosen library is also dynamically loaded in main.js.
                wp_enqueue_style('nebula-chosen');
                wp_enqueue_script('nebula-chosen');
            }
        }

        /**
         * Enqueue login scripts
         *
         * @since       1.0.0
         * @return      void
         */
        function login_enqueue_scripts( $hook ) {
            //Stylesheets
            wp_enqueue_style('nebula-login');
            echo '<style>
                    div#login h1 a {background: url(' . nebula_prefer_child_directory('/images/logo.png') . ') center center no-repeat; width: auto; background-size: contain;}
                        .svg div#login h1 a {background: url(' . nebula_prefer_child_directory('/images/logo.svg') . ') center center no-repeat; background-size: contain;}
                </style>';

            //Scripts
            wp_enqueue_script('jquery');
            wp_enqueue_script('nebula-login');
        }

        /**
         * Enqueue admin scripts
         *
         * @since       1.0.0
         * @return      void
         */
        function admin_enqueue_scripts( $hook ) {
            $current_screen = get_current_screen();

            //Stylesheets
            wp_enqueue_style('nebula-admin');
            wp_enqueue_style('nebula-font_awesome');

            //Scripts
            wp_enqueue_script('nebula-admin');

            //Nebula Visitors Data page
            if ( $current_screen->base === 'appearance_page_nebula_visitors_data' ){
                wp_enqueue_style('nebula-datatables');
                wp_enqueue_script('nebula-datatables');
            }

            //User Profile edit page
            if ( $current_screen->base === 'profile' ){
                wp_enqueue_style('thickbox');
                wp_enqueue_script('thickbox');
                wp_enqueue_script('media-upload');
            }

            //Localized objects (localized to jquery to appear in <head>)
            wp_localize_script('jquery', 'nebula', $this->script_parameters);
        }

    }

}// End if class_exists check
