<?php
/**
 * Functions
 *
 * @package     Nebula\Functions
 * @since       1.0.0
 * @author      Chris Blakley
 * @contributor Ruben Garcia
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'Nebula_Functions' ) ) {

    class Nebula_Functions {

        //Social buttons vars
        public $twitter_widget_loaded;
        public $google_plus_widget_loaded;
        public $linkedin_widget_loaded;
        public $pinterest_widget_loaded;

        public function __construct() {
            global $pagenow;

            $this->twitter_widget_loaded = false;
            $this->google_plus_widget_loaded = false;
            $this->linkedin_widget_loaded = false;
            $this->pinterest_widget_loaded = false;

            //Prep custom theme support
            add_action('after_setup_theme', array( $this, 'theme_setup' ) );

            //Add the Posts RSS Feed back in
            add_action('wp_head', array( $this, 'add_back_post_feed' ) );

            //Set server timezone to match Wordpress
            add_action('init', array( $this, 'set_default_timezone' ), 1);
            add_action('admin_init', array( $this, 'set_default_timezone' ), 1);

            //Add the Nebula note to the browser console (if enabled)
            if ( nebula_option('console_css') ) {
                add_action('wp_head', array( $this, 'calling_card' ) );
            }

            //Check for warnings and send them to the console.
            add_action('wp_head', array( $this, 'console_warnings' ) );

            //Create/Write a manifest JSON file
            if ( is_writable(get_template_directory()) ){
                if ( !file_exists($this->manifest_json_location()) || filemtime($this->manifest_json_location()) > (time()-(60*60*24)) || is_debug() ){ //@todo: filemtime(nebula_manifest_json_location()) isn't changing after writing file...
                    add_action('init', array( $this, 'manifest_json' ) );
                    add_action('admin_init', array( $this, 'manifest_json' ) );
                }
            }

            //Redirect to favicon to force-clear the cached version when ?favicon is added.
            add_action('wp_loaded', array( $this, 'favicon_cache' ) );

            //Google Optimize Style Tag
            add_action('nebula_head_open', array( $this, 'google_optimize_style' ) );

            //Register WordPress Customizer
            add_action('customize_register', array( $this, 'customize_register' ) );

            //Register Widget Areas
            add_action('widgets_init', array( $this, 'widgets_register' ) );

            //Register the Navigation Menus
            add_action('after_setup_theme', array( $this, 'nav_menu_locations' ) );

            if ( nebula_option('comments', 'disabled') || nebula_option('disqus_shortname') ) { //If WP core comments are disabled -or- if Disqus is enabled
                //Remove the Activity metabox
                add_action('wp_dashboard_setup', array( $this, 'remove_activity_metabox' ) );

                //Remove Comments column
                add_filter('manage_posts_columns', array( $this, 'remove_pages_count_columns' ) );
                add_filter('manage_pages_columns', array( $this, 'remove_pages_count_columns' ) );
                add_filter('manage_media_columns', array( $this, 'remove_pages_count_columns' ) );

                //Close comments on the front-end
                add_filter('comments_open', array( $this, 'disable_comments_status' ), 20, 2);
                add_filter('pings_open', array( $this, 'disable_comments_status' ), 20, 2);

                //Remove comments menu from Admin Bar
                if ( nebula_option('admin_bar', 'enabled') ){
                    add_action('admin_bar_menu', array( $this, 'admin_bar_remove_comments' ), 900);
                }

                //Remove comments metabox and comments
                add_action('admin_menu', array( $this, 'disable_comments_admin' ) );
                add_filter('admin_head', array( $this, 'hide_ataglance_comment_counts' ) );

                //Disable support for comments in post types, Redirect any user trying to access comments page
                add_action('admin_init', array( $this, 'disable_comments_admin_menu_redirect' ) );

                //Link to Disqus on comments page (if using Disqus)
                if ( $pagenow == 'edit-comments.php' && nebula_option('disqus_shortname') ){
                    add_action('admin_notices', array( $this, 'disqus_link' ) );
                }
            } else { //If WP core comments are enabled
                //Enqueue threaded comments script only as needed
                add_action('comment_form_before', array( $this, 'enqueue_comments_reply' ) );
            }

            //Disable support for trackbacks in post types
            add_action('admin_init', array( $this, 'disable_trackbacks' ) );

            //Prefill form fields with comment author cookie
            add_action('wp_head', array( $this, 'comment_author_cookie' ) );

            //Twitter cached feed
            //This function can be called with AJAX or as a standard function.
            add_action('wp_ajax_nebula_twitter_cache', array( $this, 'twitter_cache' ) );
            add_action('wp_ajax_nopriv_nebula_twitter_cache', array( $this, 'twitter_cache' ) );

            //Replace text on password protected posts to be more minimal
            add_filter('the_password_form', array( $this, 'password_form_simplify' ) );

            //Always get custom fields with post queries
            add_filter('the_posts', array( $this, 'always_get_post_custom' ) );

            //Prevent empty search query error (Show all results instead)
            add_action('pre_get_posts', array( $this, 'redirect_empty_search' ) );

            //Redirect if only single search result
            add_action('template_redirect', array( $this, 'redirect_single_post' ) );

            //Autocomplete Search AJAX.
            add_action('wp_ajax_nebula_autocomplete_search', array( $this, 'autocomplete_search' ) );
            add_action('wp_ajax_nopriv_nebula_autocomplete_search', array( $this, 'autocomplete_search' ) );

            //Advanced Search
            add_action('wp_ajax_nebula_advanced_search', array( $this, 'advanced_search' ) );
            add_action('wp_ajax_nopriv_nebula_advanced_search', array( $this, 'advanced_search' ) );

            //Infinite Load AJAX Call
            add_action('wp_ajax_nebula_infinite_load', array( $this, 'infinite_load' ) );
            add_action('wp_ajax_nopriv_nebula_infinite_load', array( $this, 'infinite_load' ) );

            //404 page suggestions
            add_action('wp', array( $this, 'internal_suggestions' ) );

            //Add custom body classes
            add_filter('body_class', array( $this, 'body_classes' ) );

            //Add additional classes to post wrappers
            add_filter('post_class', array( $this, 'post_classes' ) );

            //Make sure attachment URLs match the protocol (to prevent mixed content warnings).
            add_filter('wp_get_attachment_url', array( $this, 'wp_get_attachment_url_force_protocol' ) );

            //Fix responsive oEmbeds
            //Uses Bootstrap classes: http://v4-alpha.getbootstrap.com/components/utilities/#responsive-embeds
            add_filter('embed_oembed_html', array( $this, 'embed_oembed_html' ), 9999, 4);
        }

        //Prep custom theme support
        public function theme_setup(){
            //Additions
            add_theme_support('post-thumbnails');
            add_theme_support('custom-logo'); //Custom logo support.
            add_theme_support('title-tag'); //Title tag support allows WordPress core to create the <title> tag.
            //add_theme_support('html5', array('comment-list', 'comment-form', 'search-form', 'gallery', 'caption'));
            add_theme_support('automatic-feed-links'); //Add default posts and comments RSS feed links to head

            add_post_type_support('page', 'excerpt'); //Allow pages to have excerpts too

            header("X-UA-Compatible: IE=edge"); //Add IE compatibility header
            header('Developed-with-Nebula: https://gearside.com/nebula'); //Nebula header

            //Add new image sizes
            add_image_size('open_graph_large', 1200, 630, 1);
            add_image_size('open_graph_small', 600, 315, 1);
            add_image_size('twitter_large', 280, 150, 1);
            add_image_size('twitter_small', 200, 200, 1);

            //Removals
            remove_theme_support('custom-background');
            remove_theme_support('custom-header');

            //Remove capital P core function
            remove_filter('the_title', 'capital_P_dangit', 11);
            remove_filter('the_content', 'capital_P_dangit', 11);
            remove_filter('comment_text', 'capital_P_dangit', 31);

            //Head information
            remove_action('wp_head', 'rsd_link'); //Remove the link to the Really Simple Discovery service endpoint and EditURI link (third-party editing APIs)
            remove_action('wp_head', 'wp_generator'); //Removes the WordPress XHTML Generator meta tag and WP version
            remove_action('wp_head', 'wp_shortlink_wp_head'); //Removes the shortlink tag in the head
            remove_action('wp_head', 'feed_links', 2); //Remove the links to the general feeds: Post and Comment Feed
            remove_action('wp_head', 'wlwmanifest_link'); //Remove the link to the Windows Live Writer manifest file
            remove_action('wp_head', 'feed_links_extra', 3); //Remove the links to the extra feeds such as category feeds
            remove_action('wp_head', 'index_rel_link'); //Remove index link (deprecated?)
            remove_action('wp_head', 'start_post_rel_link', 10, 0); //Remove start link
            remove_action('wp_head', 'parent_post_rel_link', 10, 0); //Remove previous link
            remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0); //Remove relational links for the posts adjacent to the current post
        }

        //Add the Posts RSS Feed back in
        public function add_back_post_feed(){
            echo '<link rel="alternate" type="application/rss+xml" title="RSS 2.0 Feed" href="' . get_bloginfo('rss2_url') . '" />';
        }

        //Set server timezone to match Wordpress
        public function set_default_timezone(){
            date_default_timezone_set(get_option('timezone_string', 'America/New_York'));
        }

        //Add the Nebula note to the browser console (if enabled)
        public function calling_card(){
            if ( !nebula_option('device_detection') || (nebula_is_desktop() && !nebula_is_browser('ie') && !nebula_is_browser('edge')) ){
                echo "<script>console.log('%c Created using Nebula ', 'padding: 2px 10px; background: #0098d7; color: #fff;');</script>";
            }
        }

        //Check for warnings and send them to the console.
        public function console_warnings($console_warnings=array()){
            if ( is_dev() && nebula_option('admin_notices') ){
                if ( empty($console_warnings) || is_string($console_warnings) ){
                    $console_warnings = array();
                }

                //If search indexing is disabled
                if ( get_option('blog_public') == 0 ){
                    if ( is_site_live() ){
                        $console_warnings[] = array('error', 'Search Engine Visibility is currently disabled!');
                    } elseif ( nebula_option('prototype_mode', 'disabled') ){
                        $console_warnings[] = array('warn', 'Search Engine Visibility is currently disabled.');
                    }
                }

                if ( is_site_live() && nebula_option('prototype_mode', 'enabled') ){
                    $console_warnings[] = array('warn', 'Prototype Mode is enabled!');
                }

                //If no Google Analytics tracking ID
                if ( !nebula_option('ga_tracking_id') ){
                    $console_warnings[] = array('error', 'No Google Analytics tracking ID!');
                }

                //If there are warnings, send them to the console.
                if ( !empty($console_warnings) ){
                    echo '<script>';
                    foreach( $console_warnings as $console_warning ){
                        if ( is_string($console_warning) ){
                            $console_warning = array($console_warning);
                        }
                        if ( !in_array($console_warning[0], array('log', 'warn', 'error')) ){
                            $warn_level = 'log';
                            $the_warning = $console_warning[0];
                        } else {
                            $warn_level = $console_warning[0];
                            $the_warning = $console_warning[1];
                        }
                        echo 'console.' . $warn_level . '("Nebula: ' . $the_warning . '");';
                    }
                    echo '</script>';
                }
            }
        }

        //Manifest JSON file location
        public function manifest_json_location(){
            return get_template_directory() . '/includes/manifest.json';
        }

        //Create/Write a manifest JSON file
        public function manifest_json(){
            $override = apply_filters('pre_nebula_manifest_json', false);
            if ( $override !== false ){return;}

            $manifest_json = '{
                "short_name": "' . get_bloginfo('name') . '",
                "name": "' . get_bloginfo('name') . ': ' . get_bloginfo('description') . '",
                "gcm_sender_id": "' . nebula_option('gcm_sender_id') . '",
                "icons": [{
                    "src": "' . get_theme_file_uri('/images/meta') . '/android-chrome-36x36.png",
                    "sizes": "36x36",
                    "type": "image/png",
                    "density": 0.75
                }, {
                    "src": "' . get_theme_file_uri('/images/meta') . '/android-chrome-48x48.png",
                    "sizes": "48x48",
                    "type": "image/png",
                    "density": 1.0
                }, {
                    "src": "' . get_theme_file_uri('/images/meta') . '/android-chrome-72x72.png",
                    "sizes": "72x72",
                    "type": "image/png",
                    "density": 1.5
                }, {
                    "src": "' . get_theme_file_uri('/images/meta') . '/android-chrome-96x96.png",
                    "sizes": "96x96",
                    "type": "image/png",
                    "density": 2.0
                }, {
                    "src": "' . get_theme_file_uri('/images/meta') . '/android-chrome-144x144.png",
                    "sizes": "144x144",
                    "type": "image/png",
                    "density": 3.0
                }, {
                    "src": "' . get_theme_file_uri('/images/meta') . '/android-chrome-192x192.png",
                    "sizes": "192x192",
                    "type": "image/png",
                    "density": 4.0
                }],
                "start_url": "' . home_url() . '",
                "display": "standalone",
                "orientation": "portrait"
            }';

            //@TODO "Nebula" 0: "start_url" with a query string is not working. Manifest is confirmed working, just not the query string.

            WP_Filesystem();
            global $wp_filesystem;
            $wp_filesystem->put_contents($this->manifest_json_location(), $manifest_json);
        }

        //Redirect to favicon to force-clear the cached version when ?favicon is added.
        public function favicon_cache(){
            if ( array_key_exists('favicon', $_GET) ){
                header('Location: ' . get_theme_file_uri('/images/meta') . '/favicon.ico');
            }
        }

        //Determing if a page should be prepped using prerender/prefetch (Can be updated w/ JS).
        //If an eligible page is determined after load, use the JavaScript nebulaPrerender(url) function.
        //Use the Audience > User Flow report in Google Analytics for better predictions.
        public function prerender(){
            $override = apply_filters('pre_nebula_prerender', false);
            if ( $override !== false ){return $override;}

            $prerender_url = false;
            if ( is_404() ){
                $prerender_url = home_url('/');
            }

            if ( !empty($prerender_url) ){
                echo '<link id="prerender" rel="prerender prefetch" href="' . $prerender_url . '">';
            }
        }

        //Google Optimize Style Tag
        public function google_optimize_style(){
            if ( nebula_option('google_optimize_id') ){ ?>
                <style>.async-hide { opacity: 0 !important} </style>
                <script>(function(a,s,y,n,c,h,i,d,e){s.className+=' '+y;h.end=i=function(){
                s.className=s.className.replace(RegExp(' ?'+y),'')};(a[n]=a[n]||[]).hide=h;
                setTimeout(function(){i();h.end=null},c);})(window,document.documentElement,
                'async-hide','dataLayer',2000,{'<?php echo nebula_option('google_optimize_id'); ?>':true,});</script>
            <?php }
        }

        //Convenience function to return only the URL for specific thumbnail sizes of an ID.
        public function get_thumbnail_src($id=null, $size='full'){
            if ( empty($id) ){
                return false;
            }

            if ( strpos($id, '<img') !== false || $size == 'full' ){
                $image = wp_get_attachment_image_src(get_post_thumbnail_id($id), $size);
                return $image[0];
            } else {
                return ( preg_match('~\bsrc="([^"]++)"~', get_the_post_thumbnail($id, $size), $matches) )? $matches[1] : ''; //Use Regex as a last resort if get_the_post_thumbnail() was passed.

            }
        }

        //Determine if the author should be the Company Name or the specific author's name.
        public function the_author($show_authors=1){
            $override = apply_filters('pre_nebula_the_author', false, $show_authors);
            if ( $override !== false ){return $override;}

            if ( !is_single() || $show_authors == 0 || nebula_option('author_bios', 'disabled') ){
                return nebula_option('site_owner', get_bloginfo('name'));
            } else {
                return ( get_the_author_meta('first_name') != '' )? get_the_author_meta('first_name') . ' ' . get_the_author_meta('last_name') : get_the_author_meta('display_name');
            }
        }

        //Register WordPress Customizer
        public function customize_register($wp_customize){
            //Site Title
            $wp_customize->get_setting('blogname')->transport = 'postMessage';
            $wp_customize->get_control('blogname')->priority = 20;

            $wp_customize->add_setting('nebula_hide_blogname', array('default' => 0));
            $wp_customize->add_control('nebula_hide_blogname', array(
                'label' => 'Hide site title',
                'section' => 'title_tagline',
                'priority' => 21,
                'type' => 'checkbox',
            ) );

            //Partial to site title
            $wp_customize->selective_refresh->add_partial('blogname', array(
                'settings' => array('blogname'),
                'selector' => '#site-title',
                'container_inclusive' => true,
            ));

            //Site Description
            $wp_customize->get_setting('blogdescription')->transport = 'postMessage';
            $wp_customize->get_control('blogdescription')->priority = 30;
            $wp_customize->get_control('blogdescription')->label = 'Site Description'; //Changes "Titletag" label to "Site Description"
            $wp_customize->add_setting('nebula_hide_blogdescription', array('default' => 0));
            $wp_customize->add_control('nebula_hide_blogdescription', array(
                'label'     => 'Hide site description',
                'section'   => 'title_tagline',
                'priority'  => 31,
                'type'      => 'checkbox',
            ) );

            //Partial to site description
            $wp_customize->selective_refresh->add_partial('blogdescription', array(
                'settings' => array('blogdescription'),
                'selector' => '#site-description',
                'container_inclusive' => true,
            ));

            //Colors section
            $wp_customize->add_section('colors', array(
                'title' => 'Colors',
                'priority' => 40,
            ));

            //Primary color
            $wp_customize->add_setting('nebula_primary_color', array('default' => '#0098d7'));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nebula_primary_color', array(
                'label' => 'Primary Color',
                'section' => 'colors',
                'priority' => 10
            )));

            //Secondary color
            $wp_customize->add_setting('nebula_secondary_color', array('default' => '#95d600'));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nebula_secondary_color', array(
                'label' => 'Secondary Color',
                'section' => 'colors',
                'priority' => 20
            )));

            //Background color
            $wp_customize->add_setting('nebula_background_color', array('default' => '#f6f6f6'));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nebula_background_color', array(
                'label' => 'Bakcground Color',
                'section' => 'colors',
                'priority' => 30
            )));

            //Hero header in front page
            $wp_customize->add_setting('nebula_hide_hero', array('default' => 0));
            $wp_customize->add_control('nebula_hide_hero', array(
                'label' => 'Hide header',
                'section' => 'static_front_page',
                'priority' => 1,
                'type' => 'checkbox',
            ) );

            //Hero title
            $wp_customize->add_setting('nebula_hero_title', array('default' => 'Nebula'));
            $wp_customize->add_control('nebula_hero_title', array(
                'label' => 'Title',
                'section' => 'static_front_page',
                'priority' => 2,
            ) );

            //Partial to hero title
            $wp_customize->selective_refresh->add_partial('nebula_hero_title', array(
                'settings' => array('nebula_hero_title'),
                'selector' => '#hero-section h1',
                'container_inclusive' => false,
            ));

            //Hero subtitle
            $wp_customize->add_setting('nebula_hero_subtitle', array('default'  => 'Advanced Starter WordPress Theme for Developers'));
            $wp_customize->add_control('nebula_hero_subtitle', array(
                'label' => 'Subtitle',
                'section' => 'static_front_page',
                'priority' => 3,
            ) );

            //Partial to hero subtitle
            $wp_customize->selective_refresh->add_partial('nebula_hero_subtitle', array(
                'settings' => array('nebula_hero_subtitle'),
                'selector' => '#hero-section h2',
                'container_inclusive' => false,
            ));

            //Search in front page
            $wp_customize->add_setting('nebula_hide_hero_search', array('default' => 0));
            $wp_customize->add_control('nebula_hide_hero_search', array(
                'label'  => 'Hide search input',
                'section' => 'static_front_page',
                'priority' => 4,
                'type' => 'checkbox',
            ) );

            //Partial to search in front page
            $wp_customize->selective_refresh->add_partial('nebula_hide_hero_search', array(
                'settings' => array('nebula_hide_hero_search'),
                'selector' => '#hero-section #nebula-hero-formcon',
                'container_inclusive' => false,
            ));

            //Footer section
            $wp_customize->add_section('footer', array(
                'title' => 'Footer',
                'priority' => 130,
            ));

            //Footer logo
            $wp_customize->add_setting('nebula_footer_logo', array('default' => null));
            $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'nebula_footer_logo', array(
                'label' => 'Footer Logo',
                'section' => 'footer',
                'settings' => 'nebula_footer_logo',
                'priority' => 10
            ) ) );

            //Partial to footer logo
            $wp_customize->selective_refresh->add_partial('nebula_footer_logo', array(
                'settings' => array('nebula_footer_logo'),
                'selector' => '#footer-section .footerlogo',
                'container_inclusive' => false,
            ));

            //Footer text
            $wp_customize->add_setting('nebula_footer_text', array('default' => '&amp;copy; ' . date('Y') . ' <a href="' . home_url() . '"><strong>Nebula</strong></a> ' . nebula_version('full') . ', <em>all rights reserved</em>.'));
            $wp_customize->add_control('nebula_footer_text', array(
                'label' => 'Footer text',
                'section' => 'footer',
                'priority' => 20,
            ) );

            $wp_customize->selective_refresh->add_partial('nebula_footer_text', array(
                'settings' => array('nebula_footer_text'),
                'selector' => '.copyright span',
                'container_inclusive' => false,
            ));

            //Search in footer
            $wp_customize->add_setting('nebula_hide_footer_search', array('default' => 0));
            $wp_customize->add_control('nebula_hide_footer_search', array(
                'label' => 'Hide search input',
                'section' => 'footer',
                'priority' => 30,
                'type' => 'checkbox',
            ) );

            //Partial to search in footer
            $wp_customize->selective_refresh->add_partial('nebula_hide_footer_search', array(
                'settings' => array('nebula_hide_footer_search'),
                'selector' => '#footer-section .footer-search',
                'container_inclusive' => false,
            ));
        }

        //Register Widget Areas
        public function widgets_register(){
            $override = apply_filters('pre_nebula_widgets_init', false);
            if ( $override !== false ){return;}

            //Sidebar 1
            register_sidebar(array(
                'name' => 'Primary Widget Area',
                'id' => 'primary-widget-area',
                'description' => 'The primary widget area', 'boilerplate',
                'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
                'after_widget' => '</li>',
                'before_title' => '<h3 class="widget-title">',
                'after_title' => '</h3>',
            ));

            //Sidebar 2
            register_sidebar(array(
                'name' => 'Secondary Widget Area',
                'id' => 'secondary-widget-area',
                'description' => 'The secondary widget area',
                'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
                'after_widget' => '</li>',
                'before_title' => '<h3 class="widget-title">',
                'after_title' => '</h3>',
            ));

            //Footer 1
            register_sidebar(array(
                'name' => 'First Footer Widget Area',
                'id' => 'first-footer-widget-area',
                'description' => 'The first footer widget area',
                'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
                'after_widget' => '</li>',
                'before_title' => '<h3 class="widget-title">',
                'after_title' => '</h3>',
            ));

            //Footer 2
            register_sidebar(array(
                'name' => 'Second Footer Widget Area',
                'id' => 'second-footer-widget-area',
                'description' => 'The second footer widget area',
                'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
                'after_widget' => '</li>',
                'before_title' => '<h3 class="widget-title">',
                'after_title' => '</h3>',
            ));

            //Footer 3
            register_sidebar(array(
                'name' => 'Third Footer Widget Area',
                'id' => 'third-footer-widget-area',
                'description' => 'The third footer widget area',
                'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
                'after_widget' => '</li>',
                'before_title' => '<h3 class="widget-title">',
                'after_title' => '</h3>',
            ));

            //Footer 4
            register_sidebar(array(
                'name' => 'Fourth Footer Widget Area',
                'id' => 'fourth-footer-widget-area',
                'description' => 'The fourth footer widget area',
                'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
                'after_widget' => '</li>',
                'before_title' => '<h3 class="widget-title">',
                'after_title' => '</h3>',
            ));
        }

        //Register the Navigation Menus
        public function nav_menu_locations(){
            $override = apply_filters('pre_nebula_nav_menu_locations', false);
            if ( $override !== false ){return;}

            register_nav_menus(array(
                'secondary' => 'Secondary Menu',
                'primary' => 'Primary Menu',
                'mobile' => 'Mobile Menu',
                'sidebar' => 'Sidebar Menu',
                'footer' => 'Footer Menu'
            ));
        }

        //Remove the Activity metabox
        public function remove_activity_metabox(){
            remove_meta_box('dashboard_activity', 'dashboard', 'normal');
        }

        //Remove Comments column
        public function remove_pages_count_columns($defaults){
            unset($defaults['comments']);
            return $defaults;
        }

        //Close comments on the front-end
        public function disable_comments_status(){
            return false;
        }

        //Remove comments menu from Admin Bar
        public function admin_bar_remove_comments($wp_admin_bar){
            $wp_admin_bar->remove_menu('comments');
        }

        //Remove comments metabox and comments
        public function disable_comments_admin(){
            remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
            remove_menu_page('edit-comments.php');
            remove_submenu_page('options-general.php', 'options-discussion.php');
        }

        public function hide_ataglance_comment_counts(){
            echo '<style>li.comment-count, li.comment-mod-count {display: none;}</style>'; //Hide comment counts in "At a Glance" metabox
        }

        //Disable support for comments in post types, Redirect any user trying to access comments page
        public function disable_comments_admin_menu_redirect(){
            global $pagenow;
            if ( $pagenow === 'edit-comments.php' || $pagenow === 'options-discussion.php' ){
                wp_redirect(admin_url());
                exit;
            }

            foreach ( get_post_types() as $post_type ){
                if ( post_type_supports($post_type, 'comments') ){
                    remove_post_type_support($post_type, 'comments');
                }
            }
        }

        //Link to Disqus on comments page (if using Disqus)
        public function disqus_link(){
            echo "<div class='nebula_admin_notice notice notice-info'><p>You are using the Disqus commenting system. <a href='https://" . nebula_option('disqus_shortname') . ".disqus.com/admin/moderate' target='_blank'>View the comment listings on Disqus &raquo;</a></p></div>";
        }

        //Enqueue threaded comments script only as needed
        public function enqueue_comments_reply(){
            if ( get_option('thread_comments') ){
                wp_enqueue_script('comment-reply');
            }
        }

        //Disable support for trackbacks in post types
        public function disable_trackbacks(){
            $post_types = get_post_types();
            foreach ( $post_types as $post_type ){
                remove_post_type_support($post_type, 'trackbacks');
            }
        }

        //Prefill form fields with comment author cookie
        public function comment_author_cookie(){
            echo '<script>';
            if ( isset($_COOKIE['comment_author_' . COOKIEHASH]) ){
                $commentAuthorName = $_COOKIE['comment_author_' . COOKIEHASH];
                $commentAuthorEmail = $_COOKIE['comment_author_email_' . COOKIEHASH];
                echo 'cookieAuthorName = "' . $commentAuthorName . '";';
                echo 'cookieAuthorEmail = "' . $commentAuthorEmail . '";';
            } else {
                echo 'cookieAuthorName = "";';
                echo 'cookieAuthorEmail = "";';
            }
            echo '</script>';
        }

        //Twitter cached feed
        public function twitter_cache($username='Great_Blakes', $listname=null, $numbertweets=5, $includeretweets=1){
            if ( $_POST['data'] ){
                if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }
                $username = ( $_POST['data']['username'] )? sanitize_text_field($_POST['data']['username']) : 'Great_Blakes';
                $listname = ( $_POST['data']['listname'] )? sanitize_text_field($_POST['data']['listname']) : null; //Only used for list feeds
                $numbertweets = ( $_POST['data']['numbertweets'] )? sanitize_text_field($_POST['data']['numbertweets']) : 5;
                $includeretweets = ( $_POST['data']['includeretweets'] )? sanitize_text_field($_POST['data']['includeretweets']) : 1; //1: Yes, 0: No
            }

            error_reporting(0); //Prevent PHP errors from being cached.

            if ( $listname ){
                $feed = 'https://api.twitter.com/1.1/lists/statuses.json?slug=' . $listname . '&owner_screen_name=' . $username . '&count=' . $numbertweets . '&include_rts=' . $includeretweets;
            } else {
                $feed = 'https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=' . $username . '&count=' . $numbertweets . '&include_rts=' . $includeretweets;
            }

            $bearer = nebula_option('twitter_bearer_token', '');

            $tweets = get_transient('nebula_twitter_' . $username);
            if ( empty($tweets) || is_debug() ){
                $args = array('headers' => array('Authorization' => 'Bearer ' . $bearer));
                if ( !nebula_is_available($feed) ){
                    return false;
                }
                $response = wp_remote_get($feed, $args);
                if ( is_wp_error($response) ){
                    set_transient('nebula_site_available_' . str_replace('.', '_', nebula_url_components('hostname', $feed)), 'Unavailable', MINUTE_IN_SECONDS*5); //5 minute expiration
                    return false;
                }

                $tweets = $response['body'];

                if ( !$tweets ){
                    echo false;
                    exit;
                }

                set_transient('nebula_twitter_' . $username, $tweets, MINUTE_IN_SECONDS*5); //5 minute expiration
            }

            if ( $_POST['data'] ){
                echo $tweets;
                wp_die();
            } else {
                return $tweets;
            }
        }

        //Replace text on password protected posts to be more minimal
        public function password_form_simplify(){
            $output  = '<form action="' . esc_url(site_url('wp-login.php?action=postpass', 'login_post')) . '" method="post">';
            $output .= '<span>Password: </span>';
            $output .= '<input name="post_password" type="password" size="20" />';
            $output .= '<input type="submit" name="Submit" value="Go" />';
            $output .= '</form>';
            return $output;
        }

        //Always get custom fields with post queries
        public function always_get_post_custom($posts){
            for ( $i = 0; $i < count($posts); $i++ ){
                $custom_fields = get_post_custom($posts[$i]->ID);
                $posts[$i]->custom_fields = $custom_fields;
            }
            return $posts;
        }

        //Prevent empty search query error (Show all results instead)
        public function redirect_empty_search($query){
            global $wp_query;
            if ( isset($_GET['s']) && $wp_query->query && !array_key_exists('invalid', $_GET) ){
                if ( $_GET['s'] == '' && $wp_query->query['s'] == '' && !is_admin_page() ){
                    ga_send_event('Internal Search', 'Invalid', '(Empty query)');
                    header('Location: ' . home_url('/') . 'search/?invalid');
                    exit;
                } else {
                    return $query;
                }
            }
        }

        //Redirect if only single search result
        public function redirect_single_post(){
            if ( is_search() ){
                global $wp_query;
                if ( $wp_query->post_count == 1 && $wp_query->max_num_pages == 1 ){
                    if ( isset($_GET['s']) ){
                        //If the redirected post is the homepage, serve the regular search results page with one result (to prevent a redirect loop)
                        if ( $wp_query->posts['0']->ID != 1 && get_permalink($wp_query->posts['0']->ID) != home_url() . '/' ){
                            ga_send_event('Internal Search', 'Single Result Redirect', $_GET['s']);
                            $_GET['s'] = str_replace(' ', '+', $_GET['s']);
                            wp_redirect(get_permalink($wp_query->posts['0']->ID ) . '?rs=' . $_GET['s']);
                            exit;
                        }
                    } else {
                        ga_send_event('Internal Search', 'Single Result Redirect');
                        wp_redirect(get_permalink($wp_query->posts['0']->ID) . '?rs');
                        exit;
                    }
                }
            }
        }

        //Autocomplete Search AJAX.
        public function autocomplete_search(){
            if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }

            ini_set('memory_limit', '256M'); //@todo "Nebula" 0: Ideally this would not be here.
            $term = sanitize_text_field(trim($_POST['data']['term']));
            if ( empty($term) ){
                return false;
                exit;
            }

            $types = array('any');
            $types = json_decode(sanitize_text_field(trim($_POST['types'])));

            //Test for close or exact matches. Use: $suggestion['classes'] .= nebula_close_or_exact($suggestion['similarity']);
            function nebula_close_or_exact($rating=0, $close_threshold=80, $exact_threshold=95){
                if ( $rating > $exact_threshold ){
                    return ' exact-match';
                } elseif ( $rating > $close_threshold ){
                    return ' close-match';
                }
                return '';
            }

            //Standard WP search (does not include custom fields)
            $query1 = new WP_Query(array(
                'post_type' => $types,
                'post_status' => 'publish',
                'posts_per_page' => 4,
                's' => $term,
            ));

            //Search custom fields
            $query2 = new WP_Query(array(
                'post_type' => $types,
                'post_status' => 'publish',
                'posts_per_page' => 4,
                'meta_query' => array(
                    array(
                        'value' => $term,
                        'compare' => 'LIKE'
                    )
                )
            ));

            //Combine the above queries
            $autocomplete_query = new WP_Query();
            $autocomplete_query->posts = array_unique(array_merge($query1->posts, $query2->posts), SORT_REGULAR);
            $autocomplete_query->post_count = count($autocomplete_query->posts);

            //Loop through the posts
            if ( $autocomplete_query->have_posts() ){
                while ( $autocomplete_query->have_posts() ){
                    $autocomplete_query->the_post();
                    if ( !get_the_title() ){ //Ignore results without titles
                        continue;
                    }
                    $post = get_post();

                    $suggestion = array();
                    similar_text(strtolower($term), strtolower(get_the_title()), $suggestion['similarity']); //Determine how similar the query is to this post title
                    $suggestion['label'] = get_the_title();
                    $suggestion['link'] = get_permalink();

                    $suggestion['classes'] = 'type-' . get_post_type() . ' id-' . get_the_id() . ' slug-' . $post->post_name . ' similarity-' . str_replace('.', '_', number_format($suggestion['similarity'], 2));
                    if ( get_the_id() == get_option('page_on_front') ){
                        $suggestion['classes'] .= ' page-home';
                    } elseif ( is_sticky() ){ //@TODO "Nebula" 0: If sticky post. is_sticky() does not work here?
                        $suggestion['classes'] .= ' sticky-post';
                    }
                    $suggestion['classes'] .= nebula_close_or_exact($suggestion['similarity']);
                    $suggestions[] = $suggestion;
                }
            }

            //Find media library items
            $attachments = get_posts(array('post_type' => 'attachment', 's' => $term, 'numberposts' => 10, 'post_status' => null));
            if ( $attachments ){
                $attachment_count = 0;
                foreach ( $attachments as $attachment ){
                    if ( strpos(get_attachment_link($attachment->ID), '?attachment_id=') ){ //Skip if media item is not associated with a post.
                        continue;
                    }
                    $suggestion = array();
                    $attachment_meta = wp_get_attachment_metadata($attachment->ID);
                    $path_parts = pathinfo($attachment_meta['file']);
                    $attachment_search_meta = ( get_the_title($attachment->ID) != '' )? get_the_title($attachment->ID) : $path_parts['filename'];
                    similar_text(strtolower($term), strtolower($attachment_search_meta), $suggestion['similarity']);
                    if ( $suggestion['similarity'] >= 50 ){
                        $suggestion['label'] = ( get_the_title($attachment->ID) != '' )? get_the_title($attachment->ID) : $path_parts['basename'];
                        $suggestion['classes'] = 'type-attachment file-' . $path_parts['extension'];
                        $suggestion['classes'] .= nebula_close_or_exact($suggestion['similarity']);
                        if ( in_array(strtolower($path_parts['extension']), array('jpg', 'jpeg', 'png', 'gif', 'bmp')) ){
                            $suggestion['link'] = get_attachment_link($attachment->ID);
                        } else {
                            $suggestion['link'] = wp_get_attachment_url($attachment->ID);
                            $suggestion['external'] = true;
                            $suggestion['classes'] .= ' external-link';
                        }
                        $suggestion['similarity'] = $suggestion['similarity']-0.001; //Force lower priority than posts/pages.
                        $suggestions[] = $suggestion;
                        $attachment_count++;
                    }
                    if ( $attachment_count >= 2 ){
                        break;
                    }
                }
            }

            //Find menu items
            $menus = get_transient('nebula_autocomplete_menus');
            if ( empty($menus) || is_debug() ){
                $menus = get_terms('nav_menu');
                set_transient('nebula_autocomplete_menus', $menus, WEEK_IN_SECONDS); //This transient is deleted when a post is updated or Nebula Options are saved.
            }
            foreach($menus as $menu){
                $menu_items = wp_get_nav_menu_items($menu->term_id);
                foreach ( $menu_items as $key => $menu_item ){
                    $suggestion = array();
                    similar_text(strtolower($term), strtolower($menu_item->title), $menu_title_similarity);
                    similar_text(strtolower($term), strtolower($menu_item->attr_title), $menu_attr_similarity);
                    if ( $menu_title_similarity >= 65 || $menu_attr_similarity >= 65 ){
                        if ( $menu_title_similarity >= $menu_attr_similarity ){
                            $suggestion['similarity'] = $menu_title_similarity;
                            $suggestion['label'] = $menu_item->title;
                        } else {
                            $suggestion['similarity'] = $menu_attr_similarity;
                            $suggestion['label'] = $menu_item->attr_title;
                        }
                        $suggestion['link'] = $menu_item->url;
                        $path_parts = pathinfo($menu_item->url);
                        $suggestion['classes'] = 'type-menu-item';
                        if ( $path_parts['extension'] ){
                            $suggestion['classes'] .= ' file-' . $path_parts['extension'];
                            $suggestion['external'] = true;
                        } elseif ( !strpos($suggestion['link'], nebula_url_components('domain')) ){
                            $suggestion['classes'] .= ' external-link';
                            $suggestion['external'] = true;
                        }
                        $suggestion['classes'] .= nebula_close_or_exact($suggestion['similarity']);
                        $suggestion['similarity'] = $suggestion['similarity']-0.001; //Force lower priority than posts/pages.
                        $suggestions[] = $suggestion;
                        break;
                    }
                }
            }

            //Find categories
            $categories = get_transient('nebula_autocomplete_categories');
            if ( empty($categories) || is_debug() ){
                $categories = get_categories();
                set_transient('nebula_autocomplete_categories', $categories, WEEK_IN_SECONDS); //This transient is deleted when a post is updated or Nebula Options are saved.
            }
            foreach ( $categories as $category ){
                $suggestion = array();
                $cat_count = 0;
                similar_text(strtolower($term), strtolower($category->name), $suggestion['similarity']);
                if ( $suggestion['similarity'] >= 65 ){
                    $suggestion['label'] = $category->name;
                    $suggestion['link'] = get_category_link($category->term_id);
                    $suggestion['classes'] = 'type-category';
                    $suggestion['classes'] .= nebula_close_or_exact($suggestion['similarity']);
                    $suggestions[] = $suggestion;
                    $cat_count++;
                }
                if ( $cat_count >= 2 ){
                    break;
                }
            }

            //Find tags
            $tags = get_transient('nebula_autocomplete_tags');
            if ( empty($tags) || is_debug() ){
                $tags = get_tags();
                set_transient('nebula_autocomplete_tags', $tags, WEEK_IN_SECONDS); //This transient is deleted when a post is updated or Nebula Options are saved.
            }
            foreach ( $tags as $tag ){
                $suggestion = array();
                $tag_count = 0;
                similar_text(strtolower($term), strtolower($tag->name), $suggestion['similarity']);
                if ( $suggestion['similarity'] >= 65 ){
                    $suggestion['label'] = $tag->name;
                    $suggestion['link'] = get_tag_link($tag->term_id);
                    $suggestion['classes'] = 'type-tag';
                    $suggestion['classes'] .= nebula_close_or_exact($suggestion['similarity']);
                    $suggestions[] = $suggestion;
                    $tag_count++;
                }
                if ( $tag_count >= 2 ){
                    break;
                }
            }

            //Find authors (if author bios are enabled)
            if ( nebula_option('author_bios', 'enabled') ){
                $authors = get_transient('nebula_autocomplete_authors');
                if ( empty($authors) || is_debug() ){
                    $authors = get_users(array('role' => 'author')); //@TODO "Nebula" 0: This should get users who have made at least one post. Maybe get all roles (except subscribers) then if postcount >= 1?
                    set_transient('nebula_autocomplete_authors', $authors, WEEK_IN_SECONDS); //This transient is deleted when a post is updated or Nebula Options are saved.
                }
                foreach ( $authors as $author ){
                    $author_name = ( $author->first_name != '' )? $author->first_name . ' ' . $author->last_name : $author->display_name; //might need adjusting here
                    if ( strtolower($author_name) == strtolower($term) ){ //todo: if similarity of author name and query term is higher than X. Return only 1 or 2.
                        $suggestion = array();
                        $suggestion['label'] = $author_name;
                        $suggestion['link'] = 'http://google.com/';
                        $suggestion['classes'] = 'type-user';
                        $suggestion['classes'] .= nebula_close_or_exact($suggestion['similarity']);
                        $suggestion['similarity'] = ''; //todo: save similarity to array too
                        $suggestions[] = $suggestion;
                        break;
                    }
                }
            }

            if ( sizeof($suggestions) >= 1 ){
                //Order by match similarity to page title (DESC).
                function autocomplete_similarity_compare($a, $b){
                    return $b['similarity'] - $a['similarity'];
                }
                usort($suggestions, "autocomplete_similarity_compare");

                //Remove any duplicate links (higher similarity = higher priority)
                $outputArray = array(); //This array is where unique results will be stored
                $keysArray = array(); //This array stores values to check duplicates against.
                foreach ( $suggestions as $suggestion ){
                    if ( !in_array($suggestion['link'], $keysArray) ){
                        $keysArray[] = $suggestion['link'];
                        $outputArray[] = $suggestion;
                    }
                }
            }

            //Link to search at the end of the list
            //@TODO "Nebula" 0: The empty result is not working for some reason... (Press Enter... is never appearing)
            $suggestion = array();
            $suggestion['label'] = ( sizeof($suggestions) >= 1 )? '...more results for "' . $term . '"' : 'Press enter to search for "' . $term . '"';
            $suggestion['link'] = home_url('/') . '?s=' . str_replace(' ', '%20', $term);
            $suggestion['classes'] = ( sizeof($suggestions) >= 1 )? 'more-results search-link' : 'no-results search-link';
            $outputArray[] = $suggestion;

            echo json_encode($outputArray, JSON_PRETTY_PRINT);
            wp_die();
        }

        //Advanced Search
        public function advanced_search(){
            if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }

            ini_set('memory_limit', '512M'); //Increase memory limit for this script.

            $everything_query = get_transient('nebula_everything_query');
            if ( empty($everything_query) ){
                $everything_query = new WP_Query(array(
                    'post_type' => array('any'),
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                    'nopaging' => true
                ));
                set_transient('nebula_everything_query', $everything_query, WEEK_IN_SECONDS); //This transient is deleted when a post is updated or Nebula Options are saved.
            }
            $posts = $everything_query->get_posts();

            foreach ( $posts as $post ){
                $author = null;
                if ( nebula_option('author_bios', 'enabled') ){ //&& $post->post_type != 'page' ?
                    $author = array(
                        'id' => $post->post_author,
                        'name' => array(
                            'first' => get_the_author_meta('first_name', $post->post_author),
                            'last' => get_the_author_meta('last_name', $post->post_author),
                            'display' => get_the_author_meta('display_name', $post->post_author),
                        ),
                        'url' => get_author_posts_url($post->post_author),
                    );
                }

                $these_categories = array();
                $event_categories = get_the_category($post->ID);
                foreach ( $event_categories as $event_category ){
                    $these_categories[] = $event_category->name;
                }

                $these_tags = array();
                $event_tags = wp_get_post_tags($post->ID);
                foreach ( $event_tags as $event_tag ){
                    $these_tags[] = $event_tag->name;
                }

                $custom_fields = array();
                foreach ( $post->custom_fields as $custom_field => $custom_value ){
                    if ( substr($custom_field, 0, 1) == '_' ){
                        continue;
                    }
                    $custom_fields[$custom_field] = $custom_value[0];
                }

                $full_size = wp_get_attachment_image_src($post->_thumbnail_id, 'full');
                $thumbnail = wp_get_attachment_image_src($post->_thumbnail_id, 'thumbnail');

                $output[] = array(
                    'type' => $post->post_type,
                    'id' => $post->ID,
                    'posted' => strtotime($post->post_date),
                    'modified' => strtotime($post->post_modified),
                    'author' => $author,
                    'title' => $post->post_title,
                    'description' => strip_tags($post->post_content), //@TODO "Nebula" 0: not correct!
                    'url' => get_the_permalink($post->ID),
                    'categories' => $these_categories,
                    'tags' => $these_tags,
                    'image' => array(
                        'full' => $thumbnail[0], //@TODO "Nebula" 0: Update to shorthand array after PHP v5.4 is common
                        'thumbnail' => $full_size[0], //@TODO "Nebula" 0: Update to shorthand array after PHP v5.4 is common
                    ),
                    'custom' => $custom_fields,
                );
            } //END $posts foreach

            //@TODO: if going to sort by text:
            /*
                usort($output, function($a, $b){
                    return strcmp($a['title'], $b['title']);
                });
            */

            //@TODO: If going to sort by number:
            /*
                usort($output, function($a, $b){
                    return $a['posted'] - $b['posted'];
                });
            */

            echo json_encode($output, JSON_PRETTY_PRINT);
            wp_die();
        }

        //Infinite Load AJAX Call
        public function infinite_load(){
            if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }

            $page_number = sanitize_text_field($_POST['page']);
            $args = $_POST['args'];
            $args['paged'] = $page_number;
            $loop = sanitize_text_field($_POST['loop']);

            $args = array_map('esc_attr', $args); //Sanitize the args array
            query_posts($args);

            if ( $loop == 'false' ){
                get_template_part('loop');
            } else {
                call_user_func($loop); //Custom loop callback function must be defined in a functions file (not a template file) for this to work.
            }

            wp_die();
        }

        //404 page suggestions
        public function internal_suggestions(){
            if ( is_404() ){
                global $slug_keywords;
                $slug_keywords = array_filter(explode('/', nebula_url_components('filepath')));
                $slug_keywords = end($slug_keywords);

                global $error_query;
                $error_query = new WP_Query(array('post_status' => 'publish', 'posts_per_page' => 4, 's' => str_replace('-', ' ', $slug_keywords)));
                if ( is_plugin_active('relevanssi/relevanssi.php') ){
                    relevanssi_do_query($error_query);
                }

                if ( !empty($error_query->posts) && $slug_keywords == $error_query->posts[0]->post_name ){
                    global $error_404_exact_match;
                    $error_404_exact_match = $error_query->posts[0];
                }
            }
        }

        //Add custom body classes
        public function body_classes($classes){
            $spaces_and_dots = array(' ', '.');
            $underscores_and_hyphens = array('_', '-');

            //Device
            $classes[] = strtolower(nebula()->utilities->device_detection->get_device('formfactor')); //Form factor (desktop, tablet, mobile)
            $classes[] = strtolower(nebula()->utilities->device_detection->get_device('full')); //Device make and model
            $classes[] = strtolower(str_replace($spaces_and_dots, $underscores_and_hyphens, nebula()->utilities->device_detection->get_os('full'))); //Operating System name with version
            $classes[] = strtolower(str_replace($spaces_and_dots, $underscores_and_hyphens, nebula()->utilities->device_detection->get_os('name'))); //Operating System name
            $classes[] = strtolower(str_replace($spaces_and_dots, $underscores_and_hyphens, nebula()->utilities->device_detection->get_browser('full'))); //Browser name and version
            $classes[] = strtolower(str_replace($spaces_and_dots, $underscores_and_hyphens, nebula()->utilities->device_detection->get_browser('name'))); //Browser name
            $classes[] = strtolower(str_replace($spaces_and_dots, $underscores_and_hyphens, nebula()->utilities->device_detection->get_browser('engine'))); //Rendering engine

            //When installed to the homescreen, Chrome is detected as "Chrome Mobile". Supplement it with a "chrome" class.
            if ( nebula()->utilities->device_detection->get_browser('name') == 'Chrome Mobile' ){
                $classes[] = 'chrome';
            }

            //IE versions outside conditional comments
            if ( nebula()->utilities->device_detection->is_browser('ie') ){
                if ( nebula()->utilities->device_detection->is_browser('ie', '10') ){
                    $classes[] = 'ie';
                    $classes[] = 'ie10';
                    $classes[] = 'lte-ie10';
                    $classes[] = 'lt-ie11';
                } elseif ( nebula()->utilities->device_detection->is_browser('ie', '11') ){
                    $classes[] = 'ie';
                    $classes[] = 'ie11';
                    $classes[] = 'lte-ie11';
                }
            }

            //User Information
            $current_user = wp_get_current_user();
            if ( is_user_logged_in() ){
                $classes[] = 'user-logged-in';
                $classes[] = 'user-' . $current_user->user_login;
                $user_info = get_userdata(get_current_user_id());
                if ( !empty($user_info->roles) ){
                    $classes[] = 'user-role-' . $user_info->roles[0];
                } else {
                    $classes[] = 'user-role-unknown';
                }
            } else {
                $classes[] = 'user-not-logged-in';
            }

            //Post Information
            if ( !is_search() && !is_archive() && !is_front_page() ){
                global $post;
                $segments = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
                $parents = get_post_ancestors($post->ID);
                foreach ( $parents as $parent ){
                    if ( !empty($parent) ){
                        $classes[] = 'ancestor-id-' . $parent;
                    }
                }
                foreach ( $segments as $segment ){
                    if ( !empty($segment) ){
                        $classes[] = 'ancestor-of-' . $segment;
                    }
                }
                foreach ( get_the_category($post->ID) as $category ){
                    $classes[] = 'cat-id-' . $category->cat_ID;
                }
            }
            $nebula_theme_info = wp_get_theme();
            $classes[] = 'nebula';
            $classes[] = 'nebula_' . str_replace('.', '-', nebula_version('full'));

            $classes[] = 'lang-' . strtolower(get_bloginfo('language'));
            if ( is_rtl() ){
                $classes[] = 'lang-dir-rtl';
            }

            //Time of Day
            if ( has_business_hours() ){
                $classes[] = ( business_open() )? 'business-open' : 'business-closed';
            }

            $relative_time = nebula_relative_time('description');
            foreach( $relative_time as $relative_desc ){
                $classes[] = 'time-' . $relative_desc;
            }
            if ( date('H') >= 12 ){
                $classes[] = 'time-pm';
            } else {
                $classes[] = 'time-am';
            }

            if ( nebula_option('latitude') && nebula_option('longitude') ){
                $lat = nebula_option('latitude');
                $lng = nebula_option('longitude');
                $gmt = intval(get_option('gmt_offset'));
                $zenith = 90+50/60; //Civil twilight = 96°, Nautical twilight = 102°, Astronomical twilight = 108°
                global $sunrise, $sunset;
                $sunrise = strtotime(date_sunrise(strtotime('today'), SUNFUNCS_RET_STRING, $lat, $lng, $zenith, $gmt));
                $sunset = strtotime(date_sunset(strtotime('today'), SUNFUNCS_RET_STRING, $lat, $lng, $zenith, $gmt));
                $length_of_daylight = $sunset-$sunrise;
                $length_of_darkness = 86400-$length_of_daylight;

                if ( time() >= $sunrise && time() <= $sunset ){
                    $classes[] = 'time-daylight';
                    if ( strtotime('now') < $sunrise+($length_of_daylight/2) ){
                        $classes[] = 'time-waxing-gibbous'; //Before solar noon
                        $classes[] = ( strtotime('now') < ($length_of_daylight/4)+$sunrise )? 'time-narrow' : 'time-wide';
                    } else {
                        $classes[] = 'time-waning-gibbous'; //After solar noon
                        $classes[] = ( strtotime('now') < ((3*$sunset)+$sunrise)/4 )? 'time-wide' : 'time-narrow';
                    }
                } else {
                    $classes[] = 'time-darkness';
                    $previous_sunset_modifier = ( date('H') < 12 )? 86400 : 0; //Times are in UTC, so if it is after actual midnight (before noon) we need to use the sunset minus 1 day in formulas
                    $solar_midnight = (($sunset-$previous_sunset_modifier)+($length_of_darkness/2)); //Calculate the appropriate solar midnight (either yesterday's or tomorrow's) [see above]
                    if ( strtotime('now') < $solar_midnight ){
                        $classes[] = 'time-waning-crescent'; //Before solar midnight
                        $classes[] = ( strtotime('now') < ($length_of_darkness/4)+($sunset-$previous_sunset_modifier) )? 'time-wide' : 'time-narrow';
                    } else {
                        $classes[] = 'time-waxing-crescent'; //After solar midnight
                        $classes[] = ( strtotime('now') < ($sunrise+$solar_midnight)/2 )? 'time-narrow' : 'time-wide';
                    }
                }

                $sunrise_sunset_length = 35; //Length of sunrise/sunset in minutes.
                if ( strtotime('now') >= $sunrise-(60*$sunrise_sunset_length) && strtotime('now') <= $sunrise+(60*$sunrise_sunset_length) ){ //X minutes before and after true sunrise
                    $classes[] = 'time-sunrise';
                }
                if ( strtotime('now') >= $sunset-(60*$sunrise_sunset_length) && strtotime('now') <= $sunset+(60*$sunrise_sunset_length) ){ //X minutes before and after true sunset
                    $classes[] = 'time-sunset';
                }
            }

            $classes[] = 'date-day-' . strtolower(date('l'));
            $classes[] = 'date-ymd-' . strtolower(date('Y-m-d'));
            $classes[] = 'date-month-' . strtolower(date('F'));

            return $classes;
        }

        //Add additional classes to post wrappers
        public function post_classes($classes){
            global $post;
            global $wp_query;

            if ( $wp_query->current_post === 0 ){ //If first post in a query
                $classes[] = 'first-post';
            }
            if ( is_sticky() ){
                $classes[] = 'sticky';
            }
            $classes[] = 'nebula-entry';

            if ( !is_page() ){
                $classes[] = 'date-day-' . strtolower(get_the_date('l'));
                $classes[] = 'date-ymd-' . strtolower(get_the_date('Y-m-d'));
                $classes[] = 'date-month-' . strtolower(get_the_date('F'));
            }

            if ( !empty($post) ){
                foreach ( get_the_category($post->ID) as $category ){
                    $classes[] = 'cat-id-' . $category->cat_ID;
                }
            }

            $classes[] = 'author-id-' . $post->post_author;

            //Remove "hentry" meta class on pages or if Author Bios are disabled
            if ( is_page() || nebula_option('author_bios', 'disabled') ){
                $classes = array_diff($classes, array('hentry'));
            }

            return $classes;
        }

        //Make sure attachment URLs match the protocol (to prevent mixed content warnings).
        public function wp_get_attachment_url_force_protocol($url){
            $http = site_url(false, 'http');
            $https = site_url(false, 'https');

            if ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ){
                return str_replace($http, $https, $url);
            } else {
                return $url;
            }
        }

        //Fix responsive oEmbeds
        //Uses Bootstrap classes: http://v4-alpha.getbootstrap.com/components/utilities/#responsive-embeds
        public function embed_oembed_html($html, $url, $attr, $post_id) {
            //Enable the JS API for Youtube videos
            if ( strstr($html, 'youtube.com/embed/') ){
                $html = str_replace('?feature=oembed', '?feature=oembed&enablejsapi=1', $html);
            }

            //Force an aspect ratio on certain oEmbeds
            if ( strpos($html, 'youtube') !== false || strpos($html, 'vimeo') !== false ){
                $html = '<div class="nebula-oembed-wrapper embed-responsive embed-responsive-16by9">' . $html . '</div>';
            } elseif ( strpos($html, 'vine') !== false ){
                $html = '<div class="nebula-oembed-wrapper embed-responsive embed-responsive-1by1" style="max-width: 710px; max-height: 710px;">' . $html . '</div>';
            }

            return $html;
        }

    }

}// End if class_exists check

function nebula_manifest_json_location(){
    return nebula()->functions->manifest_json_location();
}

function nebula_prerender(){
    return nebula()->functions->prerender();
}

//Convenience function to return only the URL for specific thumbnail sizes of an ID.
//Example: nebula_get_thumbnail_src(get_the_post_thumbnail($post->ID, 'twitter_large'))
//Example: nebula_get_thumbnail_src($post->ID, 'twitter_large');
function nebula_get_thumbnail_src($id=null, $size='full'){
    nebula()->functions->get_thumbnail_src( $id, $size );
}

//Determine if the author should be the Company Name or the specific author's name.
function nebula_the_author($show_authors=1){
    nebula()->functions->the_author( $show_authors );
}

//Print the PHG logo as text with or without hover animation.
if ( !function_exists('pinckneyhugogroup') ){
    function pinckney_hugo_group($anim){ pinckneyhugogroup($anim); }
    function phg($anim){ pinckneyhugogroup($anim); }
    function pinckneyhugogroup($anim=false, $white=false){
        if ( $anim ){
            $anim = 'anim';
        }
        if ( $white ){
            $white = 'anim';
        }
        return '<a class="phg ' . $anim . ' ' . $white . '" href="http://www.pinckneyhugo.com/" target="_blank"><span class="pinckney">Pinckney</span><span class="hugo">Hugo</span><span class="group">Group</span></a>';
    }
}
