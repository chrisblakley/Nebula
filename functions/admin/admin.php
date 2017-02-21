<?php
/**
 * Admin
 *
 * @package     Nebula\Admin
 * @since       1.0.0
 * @author      Chris Blakley
 * @contributor Ruben Garcia
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'Nebula_Admin' ) ) {

    class Nebula_Admin {

        /**
         * @var         Nebula_Admin_Dashboard Nebula admin dashboard
         * @since       1.0.0
         */
        public $dashboard;

        /**
         * @var         Nebula_Admin_Users Nebula admin users
         * @since       1.0.0
         */
        public $users;

        public function __construct() {
            global $pagenow;

            // Admin classes
            require_once NEBULA_DIR . '/functions/admin/dashboard.php';
            require_once NEBULA_DIR . '/functions/admin/users.php';

            // Initialize admin classes
            $this->dashboard = new Nebula_Admin_Dashboard();
            $this->users = new Nebula_Admin_Users();

            //Force expire query transients when posts/pages are saved.
            add_action('save_post', array( $this, 'clear_transients' ) );

            //Disable auto curly quotes (smart quotes)
            remove_filter('the_content', 'wptexturize');
            remove_filter('the_excerpt', 'wptexturize');
            remove_filter('comment_text', 'wptexturize');
            add_filter('run_wptexturize', '__return_false');

            //Pull favicon from the theme folder (Front-end calls are in includes/metagraphics.php).
            add_action('admin_head', array( $this, 'admin_favicon' ) );

            //Add classes to the admin body
            add_filter('admin_body_class', array( $this, 'admin_body_classes' ) );

            //Disable Admin Bar (and WP Update Notifications) for everyone but administrators (or specific users)
            if ( nebula_option('admin_bar', 'disabled') ){
                show_admin_bar(false);

                add_action('wp_print_scripts', array( $this, 'dequeue_admin_bar' ), 9999);
                add_action('wp_print_styles', array( $this, 'dequeue_admin_bar' ), 9999);
                add_action('init', array( $this, 'admin_only_features' ) ); //TODO "Nebula" 0: Possible to remove and add directly remove action here
                add_filter('wp_head', array( $this, 'remove_admin_bar_style_frontend' ), 99);
            } else {
                add_action('wp_before_admin_bar_render', array( $this, 'remove_admin_bar_logo' ), 0);

                //Create custom menus within the WordPress Admin Bar
                add_action('admin_bar_menu',  array( $this, 'admin_bar_menus' ), 800);

                //Remove core WP admin bar head CSS and add our own
                add_action('get_header',  array( $this, 'remove_admin_bar_bump' ) ); //TODO "Nebula" 0: Possible to remove and add directly remove action here

                //Override some styles and add custom functionality
                add_action('wp_head', array( $this, 'admin_bar_style_script_overrides' ), 11);
            }

            //Disable Wordpress Core update notifications in WP Admin
            if ( nebula_option('wp_core_updates_notify', 'disabled') ){
                add_filter('pre_site_transient_update_core', '__return_null');
            }

            //Show update warning on Wordpress Core/Plugin update admin pages
            if ( nebula_option('plugin_update_warning') ){
                if ( $pagenow === 'plugins.php' || $pagenow === 'update-core.php' ){
                    add_action('admin_notices', array( $this, 'update_warning' ) );
                }
            }

            //Nebula Theme Update Checker
            add_action('admin_init', array( $this, 'theme_json' ) );

            //When checking for theme updates, store the next and current Nebula versions from the response. Hook is inside the theme-update-checker.php library.
            add_action('nebula_theme_update_check', array( $this, 'theme_update_version_store' ), 10, 2);

            //Send an email to the current user and site admin that Nebula has been updated.
            add_action('upgrader_process_complete', array( $this, 'theme_update_automation' ), 10, 2); //Action 'upgrader_post_install' also exists.

            //Control session time (for the "Remember Me" checkbox)
            add_filter('auth_cookie_expiration', array( $this, 'session_expire' ) );

            //Disable the logged-in monitoring modal
            remove_action('admin_enqueue_scripts', 'wp_auth_check_load');

            //Custom login screen
            add_action('login_head', array( $this, 'login_ga' ) );

            //Change link of login logo to live site
            add_filter('login_headerurl', array( $this, 'custom_login_header_url' ) );

            //Change alt of login image
            add_filter('login_headertitle', array( $this, 'new_wp_login_title' ) );

            //Nebula Admin Notices
            if ( nebula_option('admin_notices') ){
                add_action('admin_notices',  array( $this, 'admin_notices' ) );
            }

            //Check if a post slug has a number appended to it (indicating a duplicate post).
            //add_filter('wp_unique_post_slug', array( $this, 'unique_slug_warning_ajax' ), 10, 4); //@TODO "Nebula" 0: This echos when submitting posts from the front end! is_admin_page() does not prevent that...

            //Search theme or plugin files via Developer Information Metabox
            add_action('wp_ajax_search_theme_files', array( $this, 'search_theme_files' ) );
            add_action('wp_ajax_nopriv_search_theme_files', array( $this, 'search_theme_files' ) );

            //Change default values for the upload media box
            //These can also be changed by navigating to .../wp-admin/options.php
            add_action('after_setup_theme', array( $this, 'custom_media_display_settings' ) );

            //Add ID column on post/page listings
            add_filter('manage_posts_columns', array( $this, 'id_columns_head' ) );
            add_filter('manage_pages_columns', array( $this, 'id_columns_head' ) );

            //ID column content on post/page listings
            add_action('manage_posts_custom_column', array( $this, 'id_columns_content' ) , 15, 3);
            add_action('manage_pages_custom_column', array( $this, 'id_columns_content' ) , 15, 3);

            //Remove most Yoast SEO columns
            $post_types = get_post_types(array('public' => true), 'names');
            if ( is_array($post_types) && $post_types !== array() ){
                foreach ( $post_types as $post_type ){
                    add_filter('manage_edit-' . $post_type . '_columns', array( $this, 'remove_yoast_columns' ) ); //@TODO "Nebula" 0: This does not always work.
                }
            }

            //Duplicate post
            add_action('admin_action_duplicate_post_as_draft', array( $this, 'duplicate_post_as_draft' ) );

            //Add the duplicate link to action list for post_row_actions (This works for custom post types too).
            //Additional post types with the following: add_filter('{post type name}_row_actions', 'rd_duplicate_post_link', 10, 2);
            add_filter('post_row_actions', array( $this, 'rd_duplicate_post_link' ), 10, 2);
            add_filter('page_row_actions', array( $this, 'rd_duplicate_post_link' ), 10, 2);

            //Show File URL column on Media Library listings
            add_filter('manage_media_columns', array( $this, 'muc_column' ) );

            add_action('manage_media_custom_column', array( $this, 'muc_value' ), 10, 2);

            //Enable editor style for the TinyMCE WYSIWYG editor.
            add_editor_style('stylesheets/css/tinymce.css');

            //Enable All Settings page for only Developers who are Admins
            if ( is_dev(true) && current_user_can('manage_options') ){
                add_action('admin_menu', array( $this, 'all_settings_link' ) );
            }

            //Clear caches when plugins are activated if W3 Total Cache is active
            add_action('admin_init', array( $this, 'clear_all_w3_caches' ) );

            //Admin Footer Enhancements
            //Left Side
            add_filter('admin_footer_text', array( $this, 'change_admin_footer_left' ) );

            //Right Side
            add_filter('update_footer', array( $this, 'change_admin_footer_right' ), 11);

            //Internal Search Keywords Metabox and Custom Field
            add_action('load-post.php', array( $this, 'post_meta_boxes_setup' ) );
            add_action('load-post-new.php', array( $this, 'post_meta_boxes_setup' ) );
        }

        //Force expire query transients when posts/pages are saved.
        public function clear_transients(){
            if ( is_plugin_active('transients-manager/transients-manager.php') ){
                $transient_manager = new PW_Transients_Manager();
                $transient_manager->delete_transients_with_expirations();
            } else {
                delete_transient('nebula_autocomplete_menus'); //Autocomplete Search
                delete_transient('nebula_autocomplete_categories'); //Autocomplete Search
                delete_transient('nebula_autocomplete_tags'); //Autocomplete Search
                delete_transient('nebula_autocomplete_authors'); //Autocomplete Search
                delete_transient('nebula_everything_query'); //Advanced Search
                delete_transient('nebula_latest_post'); //Latest update
            }
        }

        //Pull favicon from the theme folder (Front-end calls are in includes/metagraphics.php).
        public function admin_favicon(){
            $cache_buster = ( is_debug() )? '?r' . mt_rand(1000, mt_getrandmax()) : '';
            echo '<link rel="shortcut icon" href="' . get_theme_file_uri('/images/meta/favicon.ico') . $cache_buster . '" />';
        }

        //Add classes to the admin body
        public function admin_body_classes($classes){
            global $current_user;
            $user_roles = $current_user->roles;
            $classes .= array_shift($user_roles);
            return $classes;
        }

        //Disable Admin Bar (and WP Update Notifications) for everyone but administrators (or specific users)
        public function dequeue_admin_bar(){
            wp_deregister_style('admin-bar');
            wp_dequeue_script('admin-bar');
        }

        public function admin_only_features(){
            remove_action('wp_footer', 'wp_admin_bar_render', 1000); //For the front-end
        }

        //CSS override for the frontend
        public function remove_admin_bar_style_frontend(){
            echo '<style type="text/css" media="screen">
                    html { margin-top: 0px !important; }
                    * html body { margin-top: 0px !important; }
                </style>';
        }

        public function remove_admin_bar_logo() {
            global $wp_admin_bar;
            $wp_admin_bar->remove_menu('wp-logo');
            $wp_admin_bar->remove_menu('wpseo-menu'); //Remove Yoast SEO from admin bar
        }

        //Create custom menus within the WordPress Admin Bar
        public function admin_bar_menus($wp_admin_bar){
            wp_reset_query(); //Make sure the query is always reset in case the current page has a custom query that isn't reset.

            $node_id = ( is_admin_page() )? 'view' : 'edit';
            $new_content_node = $wp_admin_bar->get_node($node_id);
            if ( $new_content_node ){
                $post_type_object = get_post_type_object(get_post_type());
                $new_content_node->title = ucfirst($node_id) . ' ' . ucwords($post_type_object->labels->singular_name) . ' <span class="nebula-admin-light" style="font-size: 10px; color: #a0a5aa; color: rgba(240, 245, 250, .6);">(ID: ' . get_the_id() . ')</span>';
                $wp_admin_bar->add_node($new_content_node);
            }

            //Add created date under View/Edit node
            //@TODO "Nebula" 0: get_the_author() is not working when in Admin
            $wp_admin_bar->add_node(array(
                'parent' => $node_id,
                'id' => 'nebula-created',
                'title' => '<i class="nebula-admin-fa fa fa-fw fa-calendar-o" style="font-family: \'FontAwesome\'; color: #a0a5aa; color: rgba(240, 245, 250, .6); margin-right: 5px;"></i> Created: ' . get_the_date() . ' <span class="nebula-admin-light" style="font-size: 10px; color: #a0a5aa; color: rgba(240, 245, 250, .6);">(' . get_the_author() . ')</span>',
                'href' => get_edit_post_link(),
                'meta' => array('target' => '_blank')
            ));

            //Add modified date under View/Edit node
            if ( get_the_modified_date() !== get_the_date() ){ //If the post has been modified
                $manage_author = ( get_the_modified_author() )? get_the_modified_author() : get_the_author();
                $wp_admin_bar->add_node(array(
                    'parent' => $node_id,
                    'id' => 'nebula-modified',
                    'title' => '<i class="nebula-admin-fa fa fa-fw fa-clock-o" style="font-family: \'FontAwesome\'; color: #a0a5aa; color: rgba(240, 245, 250, .6); margin-right: 5px;"></i> Modified: ' . get_the_modified_date() . ' <span class="nebula-admin-light" style="font-size: 10px; color: #a0a5aa; color: rgba(240, 245, 250, .6);">(' . $manage_author . ')</span>',
                    'href' => get_edit_post_link(),
                    'meta' => array('target' => '_blank')
                ));
            }

            //Post status (Publish, Draft, Private, etc)
            $wp_admin_bar->add_node(array(
                'parent' => $node_id,
                'id' => 'nebula-status',
                'title' => '<i class="nebula-admin-fa fa fa-fw fa-map-pin" style="font-family: \'FontAwesome\'; color: #a0a5aa; color: rgba(240, 245, 250, .6); margin-right: 5px;"></i> Status: ' . ucwords(get_post_status()),
                'href' => get_edit_post_link(),
                'meta' => array('target' => '_blank')
            ));

            if ( !empty($post_type_object) ){
                //Ancestor pages
                $ancestors = get_post_ancestors(get_the_id());
                if ( !empty($ancestors) ){
                    $wp_admin_bar->add_node(array(
                        'parent' => $node_id,
                        'id' => 'nebula-ancestors',
                        'title' => '<i class="nebula-admin-fa fa fa-fw fa-level-up" style="font-family: \'FontAwesome\'; color: #a0a5aa; color: rgba(240, 245, 250, .6); margin-right: 5px;"></i> Ancestor ' . ucwords($post_type_object->labels->name) . ' <small>(' . count($ancestors) . ')</small>',
                    ));

                    foreach ( $ancestors as $parent ){
                        $wp_admin_bar->add_node(array(
                            'parent' => 'nebula-ancestors',
                            'id' => 'nebula-parent-' . $parent,
                            'title' => '<i class="nebula-admin-fa fa fa-fw fa-file-o" style="font-family: \'FontAwesome\'; color: #a0a5aa; color: rgba(240, 245, 250, .6); margin-right: 5px;"></i> ' . get_the_title($parent),
                            'href' => ( is_admin_page() )? get_edit_post_link($parent) : get_permalink($parent),
                        ));
                    }
                }

                if ( !is_admin_page() ){ //@todo "Nebula" 0: Remove this conditional when this bug is fixed: https://core.trac.wordpress.org/ticket/18408
                    //Children pages
                    $child_pages = new WP_Query(array(
                        'post_type' => $post_type_object->labels->singular_name,
                        'posts_per_page' => -1,
                        'post_parent' => get_the_id(),
                        'order' => 'ASC',
                        'orderby' => 'menu_order'
                    ));
                    if ( $child_pages->have_posts() ){
                        $wp_admin_bar->add_node(array(
                            'parent' => $node_id,
                            'id' => 'nebula-children',
                            'title' => '<i class="nebula-admin-fa fa fa-fw fa-level-down" style="font-family: \'FontAwesome\'; color: #a0a5aa; color: rgba(240, 245, 250, .6); margin-right: 5px;"></i> Children ' . ucwords($post_type_object->labels->name) . ' <small>(' . $child_pages->found_posts . ')</small>',
                        ));

                        while ( $child_pages->have_posts() ){
                            $child_pages->the_post();
                            $wp_admin_bar->add_node(array(
                                'parent' => 'nebula-children',
                                'id' => 'nebula-child-' . get_the_id(),
                                'title' => '<i class="nebula-admin-fa fa fa-fw fa-file-o" style="font-family: \'FontAwesome\'; color: #a0a5aa; color: rgba(240, 245, 250, .6); margin-right: 5px;"></i> ' . get_the_title(),
                                'href' => ( is_admin_page() )? get_edit_post_link() : get_permalink(),
                            ));
                        }
                    }

                    wp_reset_postdata();
                }
            }

            $wp_admin_bar->add_node(array(
                'id' => 'nebula',
                'title' => '<i class="nebula-admin-fa fa fa-fw fa-star" style="font-family: \'FontAwesome\'; color: #a0a5aa; color: rgba(240, 245, 250, .6); margin-right: 5px;"></i> Nebula',
                'href' => 'https://gearside.com/nebula/?utm_campaign=documentation&utm_medium=admin+bar&utm_source=nebula',
                'meta' => array('target' => '_blank')
            ));

            if ( nebula_option('scss') ){
                $scss_last_processed = ( nebula_data('scss_last_processed') )? date('l, F j, Y - g:i:sa', nebula_data('scss_last_processed')) : 'Never';
                $wp_admin_bar->add_node(array(
                    'parent' => 'nebula',
                    'id' => 'nebula-options-scss',
                    'title' => '<i class="nebula-admin-fa fa fa-fw fa-paint-brush" style="font-family: \'FontAwesome\'; color: #a0a5aa; color: rgba(240, 245, 250, .6); margin-right: 5px;"></i> Re-process All SCSS Files',
                    'href' => esc_url(add_query_arg('sass', 'true')),
                    'meta' => array('title' => 'Last: ' . $scss_last_processed)
                ));
            }

            if ( nebula_option('visitors_db') ){
                $wp_admin_bar->add_node(array(
                    'parent' => 'nebula',
                    'id' => 'nebula-visitor-db',
                    'title' => '<i class="nebula-admin-fa fa fa-fw fa-database" style="font-family: \'FontAwesome\'; color: #a0a5aa; color: rgba(240, 245, 250, .6); margin-right: 5px;"></i> Nebula Visitors DB',
                    'href' => get_admin_url() . 'themes.php?page=nebula_visitors_data',
                    'meta' => array('target' => '_blank')
                ));
            }

            if ( nebula_option('google_optimize_id') ){
                $wp_admin_bar->add_node(array(
                    'parent' => 'nebula',
                    'id' => 'google-optimize',
                    'title' => '<i class="nebula-admin-fa fa fa-fw fa-google" style="font-family: \'FontAwesome\'; color: #a0a5aa; color: rgba(240, 245, 250, .6); margin-right: 5px;"></i> Google Optimize',
                    'href' => 'https://optimize.google.com/optimize/home/',
                    'meta' => array('target' => '_blank')
                ));
            }

            $wp_admin_bar->add_node(array(
                'parent' => 'nebula',
                'id' => 'nebula-options',
                'title' => '<i class="nebula-admin-fa fa fa-fw fa-cog" style="font-family: \'FontAwesome\'; color: #a0a5aa; color: rgba(240, 245, 250, .6); margin-right: 5px;"></i> Options',
                'href' => get_admin_url() . 'themes.php?page=nebula_options'
            ));

            $wp_admin_bar->add_node(array(
                'parent' => 'nebula-options',
                'id' => 'nebula-options-help',
                'title' => '<i class="nebula-admin-fa fa fa-fw fa-question" style="font-family: \'FontAwesome\'; color: #a0a5aa; color: rgba(240, 245, 250, .6); margin-right: 5px;"></i> Help & Documentation',
                'href' => 'https://gearside.com/nebula/documentation/options/?utm_campaign=documentation&utm_medium=admin+bar&utm_source=help',
                'meta' => array('target' => '_blank')
            ));

            $wp_admin_bar->add_node(array(
                'parent' => 'nebula',
                'id' => 'nebula-github',
                'title' => '<i class="nebula-admin-fa fa fa-fw fa-github" style="font-family: \'FontAwesome\'; color: #a0a5aa; color: rgba(240, 245, 250, .6); margin-right: 5px;"></i> Nebula Github',
                'href' => 'https://github.com/chrisblakley/Nebula',
                'meta' => array('target' => '_blank')
            ));

            $wp_admin_bar->add_node(array(
                'parent' => 'nebula-github',
                'id' => 'nebula-github-issues',
                'title' => 'Issues',
                'href' => 'https://github.com/chrisblakley/Nebula/issues',
                'meta' => array('target' => '_blank')
            ));

            $wp_admin_bar->add_node(array(
                'parent' => 'nebula-github',
                'id' => 'nebula-github-changelog',
                'title' => 'Changelog',
                'href' => 'https://github.com/chrisblakley/Nebula/commits/master',
                'meta' => array('target' => '_blank')
            ));
        }

        //Remove core WP admin bar head CSS and add our own
        public function remove_admin_bar_bump(){
            remove_action('wp_head', '_admin_bar_bump_cb');
        }

        //Override some styles and add custom functionality
        public function admin_bar_style_script_overrides(){
            if ( is_admin_bar_showing() ){ ?>
                <style type="text/css">
                    html {margin-top: 32px !important; transition: margin-top 0.5s linear;}
                    * html body {margin-top: 32px !important;}

                    #wpadminbar {transition: top 0.5s linear;}
                    .admin-bar-inactive #wpadminbar {top: -32px; overflow: hidden;}
                    #wpadminbar i {-webkit-font-smoothing: antialiased;}

                    @media screen and (max-width: 782px){
                        html {margin-top: 46px !important;}
                        * html body {margin-top: 46px !important;}

                        .admin-bar-inactive #wpadminbar {top: -46px; overflow: hidden;}
                    }

                    @media screen and (max-width: 600px){
                        #wpadminbar {top: -46px;}
                    }

                    html.admin-bar-inactive {margin-top: 0 !important;}
                </style>

                <script>
                    //Admin Bar Toggle
                    jQuery(document).on('keydown', function(e){
                        if ( e.altKey && e.which === 65 ){ //Alt+A
                            jQuery('html').toggleClass('admin-bar-inactive');
                        }
                    });
                </script>
            <?php }
        }

        //Show update warning on Wordpress Core/Plugin update admin pages
        public function update_warning(){
            echo "<div class='nebula_admin_notice error'><p><strong>WARNING:</strong> Updating Wordpress core or plugins may cause irreversible errors to your website!</p><p>Contact <a href='http://www.pinckneyhugo.com/'>Pinckney Hugo Group</a> if there are questions about updates: (315) 478-6700</p></div>";
        }

        //Nebula Theme Update Checker
        public function theme_json(){
            $override = apply_filters('pre_nebula_theme_json', false);
            if ( $override !== false ){return;}

            $nebula_data = get_option('nebula_data');

            //Always keep current_version up-to-date.
            if ( strtotime($nebula_data['current_version_date'])-strtotime(nebula_version('date')) < 0 ){
                nebula_update_data('current_version', nebula_version('raw'));
                nebula_update_data('current_version_date', nebula_version('date'));
            }

            if ( $nebula_data['version_legacy'] === 'true' ){
                //Check for unsupported version: if newer version of Nebula has a "u" at the end of the version number, disable automated updates.
                $remote_version_info = get_option('external_theme_updates-Nebula-master');
                if ( !empty($remote_version_info->checkedVersion) && strpos($remote_version_info->checkedVersion, 'u') && str_replace('u', '', $remote_version_info->checkedVersion) !== str_replace('u', '', nebula_version('full')) ){
                    nebula_update_data('version_legacy', 'true');
                    nebula_update_data('current_version', nebula_version('raw'));
                    nebula_update_data('current_version_date', nebula_version('date'));
                    nebula_update_data('next_version', 'INCOMPATIBLE');
                }
            } elseif ( current_user_can('manage_options') && is_child_theme() ){
                //@TODO "Nebula" 0: does this need to happen every admin pageload? Maybe add a transient?
                require(get_template_directory() . '/includes/libs/theme-update-checker.php'); //Initialize the update checker library.
                $theme_update_checker = new ThemeUpdateChecker(
                    'Nebula-master', //This should be the directory slug of the parent theme.
                    'https://raw.githubusercontent.com/chrisblakley/Nebula/master/includes/data/nebula_theme.json'
                );
            }
        }

        //When checking for theme updates, store the next and current Nebula versions from the response. Hook is inside the theme-update-checker.php library.
        public function theme_update_version_store($themeUpdate, $installedVersion){
            nebula_update_data('next_version', $themeUpdate->version);
            nebula_update_data('current_version', nebula_version('full'));
            nebula_update_data('current_version_date', nebula_version('date'));

            if ( strpos($themeUpdate->version, 'u') && str_replace('u', '', $themeUpdate->version) !== str_replace('u', '', nebula_version('full')) ){ //If Github version has "u", disable automated updates.
                nebula_update_data('version_legacy', 'true');
            } elseif ( nebula_data('version_legacy') === 'true' ){ //Else, reset the option to false (this triggers when a legacy version has been manually updated to support automated updates again).
                nebula_update_data('version_legacy', 'false');
                nebula_update_data('theme_update_notification', 'disabled');
            }
        }

        //Send an email to the current user and site admin that Nebula has been updated.
        public function nebula_theme_update_automation($upgrader_object, $options){
            $override = apply_filters('pre_nebula_theme_update_automation', false);
            if ( $override !== false ){return;}

            if ( $options['type'] === 'theme' && in_array_r('Nebula-master', $options['themes']) ){
                $this->theme_update_email(); //Send email with update information
                nebula_update_data('version_legacy', 'false');
            }
        }

        public function theme_update_email(){
            $prev_version = nebula_data('current_version');
            $prev_version_commit_date = nebula_data('current_version_date');
            $new_version = nebula_data('next_version');

            if ( $prev_version !== $new_version ){
                global $wpdb;
                $current_user = wp_get_current_user();
                $to = $current_user->user_email;

                //Carbon copy the admin if update was done by another user.
                $admin_user_email = nebula_option('contact_email', nebula_option('admin_email'));
                if ( !empty($admin_user_email) && $admin_user_email !== $current_user->user_email ){
                    $headers[] = 'Cc: ' . $admin_user_email;
                }

                $subject = 'Nebula updated to ' . $new_version . ' for ' . html_entity_decode(get_bloginfo('name')) . '.';
                $message = '<p>The parent Nebula theme has been updated from version <strong>' . $prev_version . '</strong> (Committed: ' . $prev_version_commit_date . ') to <strong>' . $new_version . '</strong> for ' . get_bloginfo('name') . ' (' . home_url() . ') by ' . $current_user->display_name . ' on ' . date('F j, Y') . ' at ' . date('g:ia') . '.<br/><br/>To revert, find the previous version in the <a href="https://github.com/chrisblakley/Nebula/commits/master" target="_blank">Nebula Github repository</a>, download the corresponding .zip file, and upload it replacing /themes/Nebula-master/.</p>';

                //Set the content type to text/html for the email.
                add_filter('wp_mail_content_type', function($content_type){
                    return 'text/html';
                });

                wp_mail($to, $subject, $message, $headers);
            }
        }

        //Control session time (for the "Remember Me" checkbox)
        public function session_expire($expirein){
            return 2592000; //30 days (Default is 1209600 (14 days)
        }

        //Custom login screen
        public function login_ga(){
            if ( empty($_POST['signed_request']) ){
                echo "<script>(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');ga('create', '" . nebula_option('ga_tracking_id') . "', 'auto');</script>";
            }
        }

        //Change link of login logo to live site
        public function custom_login_header_url(){
            return home_url('/');
        }

        //Change alt of login image
        public function new_wp_login_title(){
            return get_option('blogname');
        }

        //Nebula Admin Notices
        public function admin_notices(){
            if ( current_user_can('manage_options') || is_dev() ){
                //Check PHP version
                $php_version_lifecycle = $this->php_version_support();
                if ( $php_version_lifecycle['lifecycle'] === 'security' ){
                    if ( $php_version_lifecycle['end']-time() < 2592000 ){ //1 month
                        echo '<div class="nebula-admin-notice notice notice-info"><p>PHP <strong>' . PHP_VERSION . '</strong> is nearing end of life. Security updates end on <strong title="In ' . human_time_diff($php_version_lifecycle['end']) . '">' . date('F j, Y', $php_version_lifecycle['end']) . '</strong>. <a href="http://php.net/supported-versions.php" target="_blank">PHP Version Support &raquo;</a></p></div>';
                    }
                } elseif ( $php_version_lifecycle['lifecycle'] === 'end' ){
                    echo '<div class="nebula-admin-notice error"><p>PHP <strong>' . PHP_VERSION . '</strong> no longer receives security updates! End of life occurred on <strong title="' . human_time_diff($php_version_lifecycle['end']) . ' ago">' . date('F j, Y', $php_version_lifecycle['end']) . '</strong>. <a href="http://php.net/supported-versions.php" target="_blank">PHP Version Support &raquo;</a></p></div>';
                }

                //Check for hard Debug Mode
                if ( WP_DEBUG ){
                    $debug_messages = '';
                    $notice_level = 'notice notice-info';
                    if ( WP_DEBUG ){
                        $debug_messages .= '<strong>WP_DEBUG</strong> is enabled. ';
                    }
                    if ( WP_DEBUG_LOG ){
                        $debug_messages .= '<strong>Debug logging</strong> (WP_DEBUG_LOG) to /wp-content/debug.log is enabled. ';
                    }
                    if ( WP_DEBUG_DISPLAY ){
                        $notice_level = 'error';
                        $debug_messages .= 'Debug errors and warnings <strong>are</strong> being displayed on the front-end (WP_DEBUG_DISPLAY)! ';
                    }
                    echo '<div class="nebula-admin-notice ' . $notice_level . '"><p>' . $debug_messages . ' <small>(Generally defined in wp-config.php)</small></p></div>';
                }

                //Check for Google Analytics Tracking ID
                if ( !nebula_option('ga_tracking_id') ){
                    echo '<div class="nebula-admin-notice error"><p><a href="themes.php?page=nebula_options">Google Analytics tracking ID</a> is currently not set!</p></div>';
                }

                //Check for "Discourage searching engines..." setting
                if ( get_option('blog_public') == 0 ){
                    echo '<div class="nebula-admin-notice error"><p><a href="options-reading.php">Search Engine Visibility</a> is currently disabled!</p></div>';
                }

                //Check for "Just Another WordPress Blog" tagline
                if ( strtolower(get_bloginfo('description')) === 'just another wordpress site' ){
                    echo '<div class="nebula-admin-notice error"><p><a href="options-general.php">Site Tagline</a> is still "Just Another WordPress Site"!</p></div>';
                }

                //Check if all SCSS files were processed manually.
                if ( nebula_option('scss', 'enabled') && (isset($_GET['sass']) || isset($_GET['scss'])) ){ //SCSS notice when Nebula Options is updated is in nebula_options.php
                    if ( is_dev() || nebula()->utilities->is_client() ){
                        echo '<div class="nebula-admin-notice notice notice-success"><p>All SCSS files have been manually processed.</p></div>';
                    } else {
                        echo '<div class="nebula-admin-notice error"><p>You do not have permissions to manually process all SCSS files.</p></div>';
                    }
                }

                //If Prototype mode is disabled, but Multiple Theme plugin is still activated
                if ( nebula_option('prototype_mode', 'disabled') && is_plugin_active('jonradio-multiple-themes/jonradio-multiple-themes.php') ){
                    echo '<div class="nebula-admin-notice error"><p><a href="themes.php?page=nebula_options">Prototype Mode</a> is disabled, but <a href="plugins.php">Multiple Theme plugin</a> is still active.</p></div>';
                }

                //If Enhanced Ecommerce Plugin is missing Google Analytics Tracking ID
                if ( is_plugin_active('enhanced-e-commerce-for-woocommerce-store/woocommerce-enhanced-ecommerce-google-analytics-integration.php') ){
                    $ee_ga_settings = get_option('woocommerce_enhanced_ecommerce_google_analytics_settings');
                    if ( empty($ee_ga_settings['ga_id']) ){
                        echo '<div class="nebula-admin-notice error"><p><a href="admin.php?page=wc-settings&tab=integration">WooCommerce Enhanced Ecommerce</a> is missing a Google Analytics ID!</p></div>';
                    }
                }

                //Check if the parent theme template is correctly referenced
                if ( is_child_theme() ){
                    $active_theme = wp_get_theme();
                    if ( !file_exists(dirname(get_stylesheet_directory()) . '/' . $active_theme->get('Template')) ){
                        echo '<div class="nebula-admin-notice error"><p>A child theme is active, but its parent theme directory <strong>' . $active_theme->get('Template') . '</strong> does not exist!<br/><em>The "Template:" setting in the <a href="' . get_stylesheet_uri() . '" target="_blank">style.css</a> file of the child theme must match the directory name (above) of the parent theme.</em></p></div>';
                    }
                }

                //Check if Relevanssi has built an index for search
                if ( is_plugin_active('relevanssi/relevanssi.php') && !get_option('relevanssi_indexed') ){
                    echo '<div class="nebula-admin-notice error"><p><a href="options-general.php?page=relevanssi%2Frelevanssi.php">Relevanssi</a> must build an index to search the site. This must be triggered manually.</p></div>';
                }

                //Check if Google Optimize is enabled. This alert is because the Google Optimize style snippet will add a whitescreen effect during loading and should be disabled when not actively experimenting.
                if ( nebula_option('google_optimize_id') ){
                    echo '<div class="nebula-admin-notice error"><p><a href="https://optimize.google.com/optimize/home/" target="_blank">Google Optimize</a> is enabled (via <a href="themes.php?page=nebula_options">Nebula Options</a>). Disable when not actively experimenting!</p></div>';
                }
            }

            //Check page slug against categories and tags. //@TODO "Nebula" 0: Consider adding other taxonomies here too
            global $pagenow;
            if ( $pagenow === 'post.php' || $pagenow === 'edit.php' ){
                global $post;

                if ( !empty($post) ){ //If the listing has results
                    foreach ( get_categories() as $category ){
                        if ( $category->slug == $post->post_name ){
                            echo '<div class="nebula-admin-notice error"><p>Page and category slug conflict: <strong>' . $category->slug . '</strong> - Consider changing this page slug.</p></div>';
                            return false;
                        }
                    }

                    foreach ( get_tags() as $tag ){
                        if ( $tag->slug == $post->post_name ){
                            echo '<div class="nebula-admin-notice error"><p>Page and tag slug conflict: <strong>' . $tag->slug . '</strong> - Consider changing this page slug.</p></div>';
                            return false;
                        }
                    }
                }
            }
        }

        //Check the current (or passed) PHP version against the PHP support timeline.
        public function php_version_support($php_version=PHP_VERSION){
            $override = apply_filters('pre_nebula_php_version_support', false, $php_version);
            if ( $override !== false ){return $override;}

            $php_timeline_json_file = get_template_directory() . '/includes/data/php_timeline.json';
            $php_timeline = get_transient('nebula_php_timeline');
            if ( (empty($php_timeline) || is_debug()) && nebula_is_available('https://raw.githubusercontent.com/chrisblakley/Nebula/master/includes/data/php_timeline.json') ){
                $response = wp_remote_get('https://raw.githubusercontent.com/chrisblakley/Nebula/master/includes/data/php_timeline.json');
                if ( !is_wp_error($response) ){
                    $php_timeline = $response['body'];
                } else {
                    set_transient('nebula_site_available_' . str_replace('.', '_', nebula_url_components('hostname', 'https://raw.githubusercontent.com/')), 'Unavailable', MINUTE_IN_SECONDS*5); //5 minute expiration
                }

                WP_Filesystem();
                global $wp_filesystem;
                if ( !empty($php_timeline) ){
                    $wp_filesystem->put_contents($php_timeline_json_file, $php_timeline); //Store it locally.
                    set_transient('nebula_php_timeline', $php_timeline, YEAR_IN_SECONDS/12); //1 month cache
                } else {
                    $php_timeline = $wp_filesystem->get_contents($php_timeline_json_file);
                }
            }

            $php_timeline = json_decode($php_timeline);
            foreach ( $php_timeline[0] as $php_timeline_version => $php_timeline_dates ){
                if ( version_compare(PHP_VERSION, $php_timeline_version) >= 0 ){
                    $output = array();
                    if ( !empty($php_timeline_dates->security) && time() < strtotime($php_timeline_dates->security) ){
                        $output['lifecycle'] = 'active';
                    } elseif ( !empty($php_timeline_dates->security) && (time() >= strtotime($php_timeline_dates->security) && time() < strtotime($php_timeline_dates->end)) ){
                        $output['lifecycle'] = 'security';
                    } elseif ( time() >= strtotime($php_timeline_dates->end) ) {
                        $output['lifecycle'] = 'end';
                    } else {
                        $output['lifecycle'] = 'unknown'; //An error of some kind has occurred.
                    }
                    $output['security'] = strtotime($php_timeline_dates->security);
                    $output['end'] = strtotime($php_timeline_dates->end);
                    return $output;
                    break;
                }
            }
        }

        //Check if a post slug has a number appended to it (indicating a duplicate post).
        public function unique_slug_warning_ajax($slug, $post_ID, $post_status, $post_type){
            if ( current_user_can('publish_posts') && is_admin_page() && (headers_sent() || nebula_is_ajax_request()) ){ //Should work with AJAX and without (as long as headers have been sent)
                echo '<script>
                    if ( typeof nebulaUniqueSlugChecker === "function" ){
                        nebulaUniqueSlugChecker("' . $post_type . '");
                    }
                </script>';
            }
            return $slug;
        }

        //Change default values for the upload media box
        public function custom_media_display_settings(){
            //update_option('image_default_align', 'center');
            update_option('image_default_link_type', 'none');
            //update_option('image_default_size', 'large');
        }

        //Add ID column on post/page listings
        public function id_columns_head($defaults){
            $defaults['id'] = 'ID';
            return $defaults;
        }

        //ID column content on post/page listings
        public function id_columns_content($column_name, $id){
            if ( $column_name === 'id' ){
                echo $id;
            }
        }

        //Remove most Yoast SEO columns
        public function remove_yoast_columns($columns){
            //unset($columns['wpseo-score']);
            unset($columns['wpseo-title']);
            unset($columns['wpseo-metadesc']);
            unset($columns['wpseo-focuskw']);
            return $columns;
        }

        //Duplicate post
        public function duplicate_post_as_draft(){
            global $wpdb;
            if ( !(isset($_GET['post']) || isset($_POST['post'])  || (isset($_REQUEST['action']) && $_REQUEST['action'] === 'duplicate_post_as_draft')) ){
                wp_die('No post to duplicate has been supplied!');
            }

            $post_id = ( isset($_GET['post'] )? $_GET['post'] : $_POST['post']); //Get the original post id
            $post = get_post( $post_id ); //Get all the original post data

            $current_user = wp_get_current_user();
            $new_post_author = $current_user->ID; //Set post author (default by current user). For original author change to: $new_post_author = $post->post_author;

            //If post data exists, create the post duplicate
            if ( isset($post) && $post != null ){

                //Insert the post by wp_insert_post() function
                $new_post_id = wp_insert_post(array(
                    'comment_status' => $post->comment_status,
                    'ping_status' => $post->ping_status,
                    'post_author' => $new_post_author,
                    'post_content' => $post->post_content,
                    'post_excerpt' => $post->post_excerpt,
                    'post_name' => $post->post_name,
                    'post_parent' => $post->post_parent,
                    'post_password' => $post->post_password,
                    'post_status' => 'draft',
                    'post_title' => $post->post_title . ' copy',
                    'post_type' => $post->post_type,
                    'to_ping' => $post->to_ping,
                    'menu_order' => $post->menu_order
                ));

                //Get all current post terms ad set them to the new post draft
                $taxonomies = get_object_taxonomies($post->post_type); //returns array of taxonomy names for post type, ex array("category", "post_tag");
                foreach ( $taxonomies as $taxonomy ){
                    $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
                    wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
                }

                //Duplicate all post meta
                $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
                if ( count($post_meta_infos) !== 0 ){
                    $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
                    foreach ( $post_meta_infos as $meta_info ){
                        $meta_key = $meta_info->meta_key;
                        $meta_value = addslashes($meta_info->meta_value);
                        $sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
                    }
                    $sql_query .= implode(" UNION ALL ", $sql_query_sel);
                    $wpdb->query($sql_query);
                }

                wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id)); //Redirect to the edit post screen for the new draft
                exit;
            } else {
                wp_die('Post creation failed, could not find original post: ' . $post_id);
            }
        }

        //Add the duplicate link to action list for post_row_actions (This works for custom post types too).
        public function rd_duplicate_post_link($actions, $post){
            if ( current_user_can('edit_posts') ){
                $actions['duplicate'] = '<a href="admin.php?action=duplicate_post_as_draft&amp;post=' . $post->ID . '" title="Duplicate this item" rel="permalink">Duplicate</a>';
            }
            return $actions;
        }

        //Show File URL column on Media Library listings
        public function muc_column($cols){
            $cols["media_url"] = "File URL";
            return $cols;
        }

        public function muc_value( $column_name, $id ){
            if ( $column_name === "media_url" ){
                echo '<input type="text" width="100%" value="' . wp_get_attachment_url($id) . '" readonly />';
            }
        }

        //All Settings page link
        public function all_settings_link(){
            add_theme_page('All Settings', 'All Settings', 'administrator', 'options.php');
        }

        //Clear caches when plugins are activated if W3 Total Cache is active
        public function clear_all_w3_caches(){
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
            if ( is_plugin_active('w3-total-cache/w3-total-cache.php') && isset($_GET['activate']) && $_GET['activate'] === 'true' ){
                if ( function_exists('w3tc_pgcache_flush') ){
                    w3tc_pgcache_flush();
                }
            }
        }

        //Admin footer left side
        public function change_admin_footer_left(){
            return pinckneyhugogroup() . ' &bull; <a href="https://www.google.com/maps/dir/Current+Location/760+West+Genesee+Street+Syracuse+NY+13204" target="_blank">760 West Genesee Street, Syracuse, NY 13204</a> &bull; (315) 478-6700';
        }

        //Admin footer right side
        public function change_admin_footer_right(){
            global $wp_version;
            $child = ( is_child_theme() )? ' <small>(Child)</small>' : '';
            return '<span><a href="https://codex.wordpress.org/WordPress_Versions" target="_blank">WordPress</a> <strong>' . $wp_version . '</strong></span>, <span title="Committed: ' . nebula_version('date') . '"><a href="https://gearside.com/nebula/?utm_campaign=documentation&utm_medium=footer&utm_source=version" target="_blank">Nebula</a> <strong class="nebula">' . nebula_version('version') . '</strong>' . $child . '</span>';
        }

        public function post_meta_boxes_setup(){
            add_action('add_meta_boxes', array( $this, 'add_post_keywords' ) );
            add_action('save_post', array( $this, 'save_post_class_meta' ), 10, 2);
        }

        //Internal Search Keywords Metabox and Custom Field
        public function add_post_keywords(){
            $builtin_types = array('post', 'page', 'attachment');
            $custom_types = get_post_types(array('_builtin' => false));

            foreach ( $builtin_types as $builtin_type ){
                add_meta_box('nebula-internal-search-keywords', 'Internal Search Keywords', array( $this, 'internal_search_keywords_meta_box' ), $builtin_type, 'side', 'default');
            }

            foreach( $custom_types as $custom_type ){
                if ( !in_array($custom_type, array('acf', 'wpcf7_contact_form')) ){
                    add_meta_box('nebula-internal-search-keywords', 'Internal Search Keywords', array( $this, 'internal_search_keywords_meta_box' ), $custom_type, 'side', 'default');
                }
            }
        }

        //Internal Search Keywords Metabox content
        function internal_search_keywords_meta_box($object, $box){
            wp_nonce_field(basename(__FILE__), 'nebula_internal_search_keywords_nonce');
            ?>
            <div>
                <p style="font-size: 12px; color: #444;">Use plurals since parts of words will return in search results (unless plural has a different spelling than singular; then add both).</p>
                <textarea id="nebula-internal-search-keywords" class="textarea" name="nebula-internal-search-keywords" placeholder="Additional keywords to help find this page..." style="width: 100%; min-height: 150px;"><?php echo get_post_meta($object->ID, 'nebula_internal_search_keywords', true); ?></textarea>
            </div>
            <?php
        }

        public function save_post_class_meta($post_id, $post){
            if ( !isset($_POST['nebula_internal_search_keywords_nonce']) || !wp_verify_nonce($_POST['nebula_internal_search_keywords_nonce'], basename(__FILE__)) ){
                return $post_id;
            }

            $post_type = get_post_type_object($post->post_type); //Get the post type object.
            if ( !current_user_can($post_type->cap->edit_post, $post_id) ){ //Check if the current user has permission to edit the post.
                return $post_id;
            }

            $new_meta_value = sanitize_text_field($_POST['nebula-internal-search-keywords']); //Get the posted data and sanitize it if needed.
            $meta_value = get_post_meta($post_id, 'nebula_internal_search_keywords', true); //Get the meta value of the custom field key.
            if ( $new_meta_value && empty($meta_value) ){ //If a new meta value was added and there was no previous value, add it.
                add_post_meta($post_id, 'nebula_internal_search_keywords', $new_meta_value, true);
            } elseif ( $new_meta_value && $meta_value != $new_meta_value ){ //If the new meta value does not match the old value, update it.
                update_post_meta($post_id, 'nebula_internal_search_keywords', $new_meta_value);
            } elseif ( $new_meta_value == '' && $meta_value ){ //If there is no new meta value but an old value exists, delete it.
                delete_post_meta($post_id, 'nebula_internal_search_keywords', $meta_value);
            }
        }

    }

}// End if class_exists check
