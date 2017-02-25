<?php

//Prep custom theme support
add_action('after_setup_theme', 'nebula_theme_setup');
function nebula_theme_setup(){
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
add_action('wp_head', 'addBackPostFeed');
function addBackPostFeed(){
    echo '<link rel="alternate" type="application/rss+xml" title="RSS 2.0 Feed" href="' . get_bloginfo('rss2_url') . '" />';
}

//Set server timezone to match Wordpress
add_action('init', 'nebula_set_default_timezone', 1);
add_action('admin_init', 'nebula_set_default_timezone', 1);
function nebula_set_default_timezone(){
	date_default_timezone_set(get_option('timezone_string', 'America/New_York'));
}

//Add the Nebula note to the browser console (if enabled)
if ( nebula_option('console_css') ){
	add_action('wp_head', 'nebula_calling_card');
	function nebula_calling_card(){
		if ( !nebula_option('device_detection') || (nebula_is_desktop() && !nebula_is_browser('ie') && !nebula_is_browser('edge')) ){
			echo "<script>console.log('%c Created using Nebula ', 'padding: 2px 10px; background: #0098d7; color: #fff;');</script>";
		}
	}
}

//Check for warnings and send them to the console.
add_action('wp_head', 'nebula_console_warnings');
function nebula_console_warnings($console_warnings=array()){
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

//Create/Write a manifest JSON file
if ( is_writable(get_template_directory()) ){
	if ( !file_exists(nebula_manifest_json_location()) || filemtime(nebula_manifest_json_location()) > (time()-(60*60*24)) || is_debug() ){ //@todo: filemtime(nebula_manifest_json_location()) isn't changing after writing file...
		add_action('init', 'nebula_manifest_json');
		add_action('admin_init', 'nebula_manifest_json');
	}
}
function nebula_manifest_json_location(){
	return get_template_directory() . '/inc/manifest.json';
}
function nebula_manifest_json(){
	$override = apply_filters('pre_nebula_manifest_json', false);
	if ( $override !== false ){return;}

	$manifest_json = '{
	"short_name": "' . get_bloginfo('name') . '",
	"name": "' . get_bloginfo('name') . ': ' . get_bloginfo('description') . '",
	"gcm_sender_id": "' . nebula_option('gcm_sender_id') . '",
	"icons": [{
		"src": "' . get_theme_file_uri('/assets/img/meta') . '/android-chrome-36x36.png",
		"sizes": "36x36",
		"type": "image/png",
		"density": 0.75
	}, {
		"src": "' . get_theme_file_uri('/assets/img/meta') . '/android-chrome-48x48.png",
		"sizes": "48x48",
		"type": "image/png",
		"density": 1.0
	}, {
		"src": "' . get_theme_file_uri('/assets/img/meta') . '/android-chrome-72x72.png",
		"sizes": "72x72",
		"type": "image/png",
		"density": 1.5
	}, {
		"src": "' . get_theme_file_uri('/assets/img/meta') . '/android-chrome-96x96.png",
		"sizes": "96x96",
		"type": "image/png",
		"density": 2.0
	}, {
		"src": "' . get_theme_file_uri('/assets/img/meta') . '/android-chrome-144x144.png",
		"sizes": "144x144",
		"type": "image/png",
		"density": 3.0
	}, {
		"src": "' . get_theme_file_uri('/assets/img/meta') . '/android-chrome-192x192.png",
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
	$wp_filesystem->put_contents(nebula_manifest_json_location(), $manifest_json);
}

//Redirect to favicon to force-clear the cached version when ?favicon is added.
add_action('wp_loaded', 'nebula_favicon_cache');
function nebula_favicon_cache(){
	if ( array_key_exists('favicon', $_GET) ){
		header('Location: ' . get_theme_file_uri('/assets/img/meta') . '/favicon.ico');
	}
}

//Determing if a page should be prepped using prerender/prefetch (Can be updated w/ JS).
//If an eligible page is determined after load, use the JavaScript nebulaPrerender(url) function.
//Use the Audience > User Flow report in Google Analytics for better predictions.
function nebula_prerender(){
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
add_action('nebula_head_open', 'nebula_google_optimize_style');
function nebula_google_optimize_style(){
	if ( nebula_option('google_optimize_id') ){ ?>
		<style>.async-hide { opacity: 0 !important} </style>
		<script>(function(a,s,y,n,c,h,i,d,e){s.className+=' '+y;h.end=i=function(){
		s.className=s.className.replace(RegExp(' ?'+y),'')};(a[n]=a[n]||[]).hide=h;
		setTimeout(function(){i();h.end=null},c);})(window,document.documentElement,
		'async-hide','dataLayer',2000,{'<?php echo nebula_option('google_optimize_id'); ?>':true,});</script>
	<?php }
}

//Convenience function to return only the URL for specific thumbnail sizes of an ID.
//Example: nebula_get_thumbnail_src(get_the_post_thumbnail($post->ID, 'twitter_large'))
//Example: nebula_get_thumbnail_src($post->ID, 'twitter_large');
function nebula_get_thumbnail_src($id=null, $size='full'){
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
function nebula_the_author($show_authors=1){
	$override = apply_filters('pre_nebula_the_author', false, $show_authors);
	if ( $override !== false ){return $override;}

	if ( !is_single() || $show_authors == 0 || nebula_option('author_bios', 'disabled') ){
		return nebula_option('site_owner', get_bloginfo('name'));
	} else {
		return ( get_the_author_meta('first_name') != '' )? get_the_author_meta('first_name') . ' ' . get_the_author_meta('last_name') : get_the_author_meta('display_name');
	}
}

//Register WordPress Customizer
add_action('customize_register', 'nebula_customize_register');
function nebula_customize_register($wp_customize){
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
add_action('widgets_init', 'nebula_widgets_register');
function nebula_widgets_register(){
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
add_action('after_setup_theme', 'nebula_nav_menu_locations');
function nebula_nav_menu_locations(){
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

if ( nebula_option('comments', 'disabled') || nebula_option('disqus_shortname') ){ //If WP core comments are disabled -or- if Disqus is enabled
	//Remove the Activity metabox
	add_action('wp_dashboard_setup', 'remove_activity_metabox');
	function remove_activity_metabox(){
		remove_meta_box('dashboard_activity', 'dashboard', 'normal');
	}

	//Remove Comments column
	add_filter('manage_posts_columns', 'remove_pages_count_columns');
	add_filter('manage_pages_columns', 'remove_pages_count_columns');
	add_filter('manage_media_columns', 'remove_pages_count_columns');
	function remove_pages_count_columns($defaults){
		unset($defaults['comments']);
		return $defaults;
	}

	//Close comments on the front-end
	add_filter('comments_open', 'disable_comments_status', 20, 2);
	add_filter('pings_open', 'disable_comments_status', 20, 2);
	function disable_comments_status(){
		return false;
	}

	//Remove comments menu from Admin Bar
	if ( nebula_option('admin_bar', 'enabled') ){
		add_action('admin_bar_menu', 'nebula_admin_bar_remove_comments', 900);
		function nebula_admin_bar_remove_comments($wp_admin_bar){
			$wp_admin_bar->remove_menu('comments');
		}
	}

	//Remove comments metabox and comments
	add_action('admin_menu', 'disable_comments_admin');
	function disable_comments_admin(){
		remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
		remove_menu_page('edit-comments.php');
		remove_submenu_page('options-general.php', 'options-discussion.php');

		add_filter('admin_head', 'hide_ataglance_comment_counts');
		function hide_ataglance_comment_counts(){
			echo '<style>li.comment-count, li.comment-mod-count {display: none;}</style>'; //Hide comment counts in "At a Glance" metabox
		}
	}

	//Disable support for comments in post types, Redirect any user trying to access comments page
	add_action('admin_init', 'disable_comments_admin_menu_redirect');
	function disable_comments_admin_menu_redirect(){
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
	if ( $pagenow == 'edit-comments.php' && nebula_option('disqus_shortname') ){
		add_action('admin_notices', 'disqus_link');
		function disqus_link(){
			echo "<div class='nebula_admin_notice notice notice-info'><p>You are using the Disqus commenting system. <a href='https://" . nebula_option('disqus_shortname') . ".disqus.com/admin/moderate' target='_blank'>View the comment listings on Disqus &raquo;</a></p></div>";
		}
	}
} else { //If WP core comments are enabled
	//Enqueue threaded comments script only as needed
	add_action('comment_form_before', 'nebula_enqueue_comments_reply');
	function nebula_enqueue_comments_reply(){
		if ( get_option('thread_comments') ){
			wp_enqueue_script('comment-reply');
		}
	}
}

//Disable support for trackbacks in post types
add_action('admin_init', 'nebula_disable_trackbacks');
function nebula_disable_trackbacks(){
	$post_types = get_post_types();
	foreach ( $post_types as $post_type ){
		remove_post_type_support($post_type, 'trackbacks');
	}
}

//Prefill form fields with comment author cookie
add_action('wp_head', 'comment_author_cookie');
function comment_author_cookie(){
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

//Show different meta data information about the post. Typically used inside the loop.
//Example: nebula_meta('by');
function nebula_meta($meta){
	$override = apply_filters('pre_nebula_meta', false, $meta);
	if ( $override !== false ){echo $override; return;}

	if ( $meta == 'date' || $meta == 'time' || $meta == 'on' || $meta == 'day' || $meta == 'when' ){
		echo nebula_post_date();
	} elseif ( $meta == 'author' || $meta == 'by' ){
		echo nebula_post_author();
	} elseif ( $meta == 'type' || $meta == 'cpt' || $meta == 'post_type' ){
		echo nebula_post_type();
	} elseif ( $meta == 'categories' || $meta == 'category' || $meta == 'cat' || $meta == 'cats' || $meta == 'in' ){
		echo nebula_post_categories();
	} elseif ( $meta == 'tags' || $meta == 'tag' ){
		echo nebula_post_tags();
	} elseif ( $meta == 'dimensions' || $meta == 'size' ){
		echo nebula_post_dimensions();
	} elseif ( $meta == 'exif' || $meta == 'camera' ){
		echo nebula_post_exif();
	} elseif ( $meta == 'comments' || $meta == 'comment' ){
		echo nebula_post_comments();
	} elseif ( $meta == 'social' || $meta == 'sharing' || $meta == 'share' ){
		nebula_social(array('facebook', 'twitter', 'google+', 'linkedin', 'pinterest'), 0);
	}
}

//Date post meta
function nebula_post_date($icon=true, $linked=true, $day=true){
	$the_icon = '';
	if ( $icon ){
		$the_icon = '<i class="fa fa-calendar-o"></i> ';
	}

	$the_day = '';
	if ( $day ){ //If the day should be shown (otherwise, just month and year).
		$the_day = get_the_date('d') . '/';
	}

	if ( $linked ){
		return '<span class="posted-on">' . $the_icon . '<span class="meta-item entry-date" datetime="' . get_the_time('c') . '" itemprop="datePublished" content="' . get_the_date('c') . '">' . '<a href="' . home_url('/') . get_the_date('Y/m') . '/' . '">' . get_the_date('F') . '</a>' . ' ' . '<a href="' . home_url('/') . get_the_date('Y/m') . '/' . $the_day . '">' . get_the_date('j') . '</a>' . ', ' . '<a href="' . home_url('/') . get_the_date('Y') . '/' . '">' . get_the_date('Y') . '</a>' . '</span></span>';
	} else {
		return '<span class="posted-on">' . $the_icon . '<span class="meta-item entry-date" datetime="' . get_the_time('c') . '" itemprop="datePublished" content="' . get_the_date('c') . '">' . get_the_date('F j, Y') . '</span></span>';
	}
}

//Author post meta
function nebula_post_author($icon=true, $linked=true, $force=false){
	$the_icon = '';
	if ( $icon ){
		$the_icon = '<i class="fa fa-user"></i> ';
	}

	if ( nebula_option('author_bios', 'enabled') || $force ){
		if ( $linked && !$force ){
			return '<span class="posted-by" itemprop="author" itemscope itemtype="https://schema.org/Person">' . $the_icon . '<span class="meta-item entry-author">' . '<a href="' . get_author_posts_url(get_the_author_meta('ID')) . '" itemprop="name">' . get_the_author() . '</a></span></span>';
		} else {
			return '<span class="posted-by" itemprop="author" itemscope itemtype="https://schema.org/Person">' . $the_icon . '<span class="meta-item entry-author" itemprop="name">' . get_the_author() . '</span></span>';
		}
	}
}

//Post type meta
function nebula_post_type($icon=true){
	$post_icon_img = '<i class="fa fa-thumb-tack"></i>';
	if ( $icon ){
		global $wp_post_types;
		$post_type = get_post_type();

		if ( $post_type == 'post' ){
			$post_icon_img = '<i class="fa fa-thumb-tack"></i>';
		} elseif ( $post_type == 'page' ){
			$post_icon_img = '<i class="fa fa-file-text"></i>';
		} else {
			$post_icon = $wp_post_types[$post_type]->menu_icon;
			if ( !empty($post_icon) ){
				if ( strpos('dashicons-', $post_icon) >= 0 ){
					$post_icon_img = '<i class="dashicons-before ' . $post_icon . '"></i>';
				} else {
					$post_icon_img = '<img src="' . $post_icon . '" style="width: 16px; height: 16px;" />';
				}
			} else {
				$post_icon_img = '<i class="fa fa-thumb-tack"></i>';
			}
		}
	}

	return '<span class="meta-item post-type">' . $post_icon_img . ucwords(get_post_type()) . '</span>';
}

//Categories post meta
function nebula_post_categories($icon=true){
	$the_icon = '';
	if ( $icon ){
		$the_icon = '<i class="fa fa-bookmark"></i> ';
	}

	if ( is_object_in_taxonomy(get_post_type(), 'category') ){
		return '<span class="posted-in meta-item post-categories">' . $the_icon . get_the_category_list(', ') . '</span>';
	}
	return '';
}

//Tags post meta
function nebula_post_tags($icon=true){
	$tag_list = get_the_tag_list('', ', ');
	if ( $tag_list ){
		$the_icon = '';
		if ( $icon ){
			$tag_plural = ( count(get_the_tags()) > 1 )? 'tags' : 'tag';
			$the_icon = '<i class="fa fa-' . $tag_plural . '"></i> ';
		}
		return '<span class="posted-in meta-item post-tags">' . $the_icon . $tag_list . '</span>';
	}
	return '';
}

//Image dimensions post meta
function nebula_post_dimensions($icon=true, $linked=true){
	if ( wp_attachment_is_image() ){
		$the_icon = '';
		if ( $icon ){
			$the_icon = '<i class="fa fa-expand"></i> ';
		}

		$metadata = wp_get_attachment_metadata();
		if ( $linked ){
			echo '<span class="meta-item meta-dimensions">' . $the_icon . '<a href="' . wp_get_attachment_url() . '" >' . $metadata['width'] . ' &times; ' . $metadata['height'] . '</a></span>';
		} else {
			echo '<span class="meta-item meta-dimensions">' . $the_icon . $metadata['width'] . ' &times; ' . $metadata['height'] . '</span>';
		}
	}
}

//Image EXIF post meta
function nebula_post_exif($icon=true){
	$the_icon = '';
	if ( $icon ){
		$the_icon = '<i class="fa fa-camera"></i> ';
	}

	$imgmeta = wp_get_attachment_metadata();
    if ( $imgmeta ){ //Check for Bad Data
        if ( $imgmeta['image_meta']['focal_length'] == 0 || $imgmeta['image_meta']['aperture'] == 0 || $imgmeta['image_meta']['shutter_speed'] == 0 || $imgmeta['image_meta']['iso'] == 0 ){
            $output = 'No valid EXIF data found';
        } else { //Convert the shutter speed retrieve from database to fraction
            if ( (1/$imgmeta['image_meta']['shutter_speed']) > 1 ){
                if ( (number_format((1/$imgmeta['image_meta']['shutter_speed']), 1)) == 1.3 || number_format((1/$imgmeta['image_meta']['shutter_speed']), 1) == 1.5 || number_format((1/$imgmeta['image_meta']['shutter_speed']), 1) == 1.6 || number_format((1/$imgmeta['image_meta']['shutter_speed']), 1) == 2.5 ){
                    $pshutter = '1/' . number_format((1/$imgmeta['image_meta']['shutter_speed']), 1, '.', '') . ' second';
                } else {
                    $pshutter = '1/' . number_format((1/$imgmeta['image_meta']['shutter_speed']), 0, '.', '') . ' second';
                }
            } else {
                $pshutter = $imgmeta['image_meta']['shutter_speed'] . " seconds";
            }

            $output = '<time datetime="' . date('c', $imgmeta['image_meta']['created_timestamp']) . '"><span class="month">' . date('F', $imgmeta['image_meta']['created_timestamp']) . '</span> <span class="day">' . date('j', $imgmeta['image_meta']['created_timestamp']) . '</span><span class="suffix">' . date('S', $imgmeta['image_meta']['created_timestamp']) . '</span> <span class="year">' . date('Y', $imgmeta['image_meta']['created_timestamp']) . '</span></time>' . ', ';
            $output .= $imgmeta['image_meta']['camera'] . ', ';
            $output .= $imgmeta['image_meta']['focal_length'] . 'mm' . ', ';
            $output .= '<span style="font-style: italic; font-family: Trebuchet MS, Candara, Georgia; text-transform: lowercase;">f</span>/' . $imgmeta['image_meta']['aperture'] . ', ';
            $output .= $pshutter . ', ';
            $output .= $imgmeta['image_meta']['iso'] .' ISO';
        }
    } else {
        $output = 'No EXIF data found';
    }

	return '<span class="meta-item meta-exif">' . $the_icon . $output . '</span>';
}

//Comments post meta
function nebula_post_comments($icon=true, $linked=true, $empty=true){
	$comments_text = 'Comments';
	if ( get_comments_number() == 0 ){
		$comment_icon = 'fa-comment-o';
		$comment_show = ( $empty )? '' : 'hidden'; //If comment link should show if no comments. True = show, False = hidden
	} elseif ( get_comments_number() == 1 ){
		$comment_icon = 'fa-comment';
		$comments_text = 'Comment';
	} elseif ( get_comments_number() > 1 ){
		$comment_icon = 'fa-comments';
	}

	$the_icon = '';
	if ( $icon ){
		$the_icon = '<i class="fa ' . $comment_icon . '"></i> ';
	}

	if ( $linked ){
		$postlink = ( is_single() )? '' : get_the_permalink();
		return '<span class="meta-item posted-comments ' . $comment_show . '">' . $the_icon . '<a class="nebulametacommentslink" href="' . $postlink . '#nebulacommentswrapper">' . get_comments_number() . ' ' . $comments_text . '</a></span>';
	} else {
		return '<span class="meta-item posted-comments ' . $comment_show . '">' . $the_icon . get_comments_number() . ' ' . $comments_text . '</span>';
	}
}

//Display Social Buttons
function nebula_social($networks=array('facebook', 'twitter', 'google+'), $counts=0){
	$override = apply_filters('pre_nebula_social', false, $networks, $counts);
	if ( $override !== false ){echo $override; return;}

	if ( is_string($networks) ){ //if $networks is a string, create an array for the string.
		$networks = array($networks);
	} elseif ( is_int($networks) && ($networks == 1 || $networks == 0) ){ //If it is an integer of 1 or 0, then set it to $counts
		$counts = $networks;
		$networks = array('facebook', 'twitter', 'google+');
	} elseif ( !is_array($networks) ){
		$networks = array('facebook', 'twitter', 'google+');
	}
	$networks = array_map('strtolower', $networks); //Convert $networks to lower case for more flexible string matching later.

	echo '<div class="sharing-links">';
	foreach ( $networks as $network ){
		//Facebook
		if ( in_array($network, array('facebook', 'fb')) ){
			nebula_facebook_share($counts);
		}

		//Twitter
		if ( in_array($network, array('twitter')) ){
			nebula_twitter_tweet($counts);
		}

		//Google+
		if ( in_array($network, array('google_plus', 'google', 'googleplus', 'google+', 'g+', 'gplus', 'g_plus', 'google plus', 'google-plus', 'g-plus')) ){
			nebula_google_plus($counts);
		}

		//LinkedIn
		if ( in_array($network, array('linkedin', 'li', 'linked-in', 'linked_in')) ){
			nebula_linkedin_share($counts);
		}

		//Pinterest
		if ( in_array($network, array('pinterest', 'pin')) ){
			nebula_pinterest_pin($counts);
		}
	}
	echo '</div><!-- /sharing-links -->';
}

/*
	Social Button Functions
	//@TODO "Nebula" 0: Eventually upgrade these to support vertical count bubbles as an option.
*/

function nebula_facebook_share($counts=0, $url=false){
	$override = apply_filters('pre_nebula_facebook_share', false, $counts);
	if ( $override !== false ){echo $override; return;}
?>
	<div class="nebula-social-button facebook-share require-fbsdk">
		<div class="fb-share-button" data-href="<?php echo ( !empty($url) )? $url : get_page_link(); ?>" data-layout="<?php echo ( $counts != 0 )? 'button_count' : 'button'; ?>"></div>
	</div>
<?php }


function nebula_facebook_like($counts=0, $url=false){
	$override = apply_filters('pre_nebula_facebook_like', false, $counts);
	if ( $override !== false ){echo $override; return;}
?>
	<div class="nebula-social-button facebook-like require-fbsdk">
		<div class="fb-like" data-href="<?php echo ( !empty($url) )? $url : get_page_link(); ?>" data-layout="<?php echo ( $counts != 0 )? 'button_count' : 'button'; ?>" data-action="like" data-show-faces="false" data-share="false"></div>
	</div>
<?php }

function nebula_facebook_both($counts=0, $url=false){
	$override = apply_filters('pre_nebula_facebook_both', false, $counts);
	if ( $override !== false ){echo $override; return;}
?>
	<div class="nebula-social-button facebook-both require-fbsdk">
		<div class="fb-like" data-href="<?php echo ( !empty($url) )? $url : get_page_link(); ?>" data-layout="<?php echo ( $counts != 0 )? 'button_count' : 'button'; ?>" data-action="like" data-show-faces="false" data-share="true"></div>
	</div>
<?php }

$nebula_twitter_widget_loaded = false;
function nebula_twitter_tweet($counts=0){
	$override = apply_filters('pre_nebula_twitter_tweet', false, $counts);
	if ( $override !== false ){echo $override; return;}
?>
	<div class="nebula-social-button twitter-tweet">
		<a href="https://twitter.com/share" class="twitter-share-button" <?php echo ( $counts != 0 )? '': 'data-count="none"'; ?>>Tweet</a>
		<?php twitter_widget_script(); ?>
	</div>
<?php
}

function nebula_twitter_follow($counts=0, $username=false){
	$override = apply_filters('pre_nebula_twitter_follow', false, $counts, $username);
	if ( $override !== false ){echo $override; return;}

	if ( empty($username) && !nebula_option('twitter_username') ){
		return false;
	} elseif ( empty($username) && nebula_option('twitter_username') ){
		$username = nebula_option('twitter_username');
	} elseif ( strpos($username, '@') === false ){
		$username = '@' . $username;
	}
?>
	<div class="nebula-social-button twitter-follow">
		<a href="<?php echo nebula_twitter_url($username); ?>" class="twitter-follow-button" <?php echo ( $counts != 0 )? '': 'data-show-count="false"'; ?> <?php echo ( !empty($username) )? '': 'data-show-screen-name="false"'; ?>>Follow <?php echo $username; ?></a>
		<?php twitter_widget_script(); ?>
	</div>
<?php
}

function twitter_widget_script(){
	if ( empty($nebula_twitter_widget_loaded) ){
		?>
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
		<?php
		$nebula_twitter_widget_loaded = true;
	}
}

$nebula_google_plus_widget_loaded = true;
function nebula_google_plus($counts=0){
	$override = apply_filters('pre_nebula_google_plus', false, $counts);
	if ( $override !== false ){echo $override; return;}
?>
	<div class="nebula-social-button google-plus-plus-one">
		<div class="g-plusone" data-size="medium" <?php echo ( $counts != 0 )? '' : 'data-annotation="none"'; ?>></div>
		<?php if ( empty($nebula_google_plus_widget_loaded) ) : ?>
			<script src="https://apis.google.com/js/platform.js" async defer></script>
			<?php $nebula_google_plus_widget_loaded = true; ?>
		<?php endif; ?>
	</div>
<?php
}

$nebula_linkedin_widget_loaded = false;
function nebula_linkedin_share($counts=0){
	$override = apply_filters('pre_nebula_linkedin_share', false, $counts);
	if ( $override !== false ){echo $override; return;}
?>
	<div class="nebula-social-button linkedin-share">
		<?php linkedin_widget_script(); ?>
		<script type="IN/Share" <?php echo ( $counts != 0 )? 'data-counter="right"' : ''; ?>></script>
	</div>
<?php
}

function nebula_linkedin_follow($counts=0){
	$override = apply_filters('pre_nebula_linkedin_follow', false, $counts);
	if ( $override !== false ){echo $override; return;}
?>
	<div class="nebula-social-button linkedin-follow">
		<?php linkedin_widget_script(); ?>
		<script type="IN/FollowCompany" data-id="1337" <?php echo ( $counts != 0 )? 'data-counter="right"' : ''; ?>></script>
	</div>
<?php
}

function linkedin_widget_script(){
	if ( empty($nebula_linkedin_widget_loaded) ){
		?>
		<script type="text/javascript" src="//platform.linkedin.com/in.js" async defer> lang: en_US</script>
		<?php
		$nebula_linkedin_widget_loaded = true;
	}
}

$nebula_pinterest_pin_widget_loaded = false;
function nebula_pinterest_pin($counts=0){ //@TODO "Nebula" 0: Bubble counts are not showing up...
	$override = apply_filters('pre_nebula_pinterest_pin', false, $counts);
	if ( $override !== false ){echo $override; return;}

	if ( has_post_thumbnail() ){
		$featured_image = get_the_post_thumbnail();
	} else {
		$featured_image = get_template_directory_uri() . '/assets/img/meta/og-thumb.png'; //@TODO "Nebula" 0: This should probably be a square? Check the recommended dimensions.
	}
?>
	<div class="nebula-social-button pinterest-pin">
		<a href="//www.pinterest.com/pin/create/button/?url=<?php echo get_page_link(); ?>&media=<?php echo $featured_image; ?>&description=<?php echo urlencode(get_the_title()); ?>" data-pin-do="buttonPin" data-pin-config="<?php echo ( $counts != 0 )? 'beside' : 'none'; ?>" data-pin-color="red">
			<img src="//assets.pinterest.com/images/pidgets/pinit_fg_en_rect_red_20.png" />
		</a>
		<?php if ( empty($nebula_pinterest_pin_widget_loaded) ): ?>
			<script type="text/javascript" async defer src="//assets.pinterest.com/js/pinit.js"></script>
			<?php $nebula_pinterest_pin_widget_loaded = true; ?>
		<?php endif; ?>
	</div>
<?php
}

//Twitter cached feed
//This function can be called with AJAX or as a standard function.
add_action('wp_ajax_nebula_twitter_cache', 'nebula_twitter_cache');
add_action('wp_ajax_nopriv_nebula_twitter_cache', 'nebula_twitter_cache');
function nebula_twitter_cache($username='Great_Blakes', $listname=null, $numbertweets=5, $includeretweets=1){
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
			nebula_set_unavailable($feed);
			return false;
		}

		$tweets = $response['body'];

		if ( !$tweets ){
			echo false;
			exit;
		}

		set_transient('nebula_twitter_' . $username, $tweets, MINUTE_IN_SECONDS*5);
	}

	if ( $_POST['data'] ){
		echo $tweets;
		wp_die();
	} else {
		return $tweets;
	}
}

//Use this instead of the_excerpt(); and get_the_excerpt(); to have better control over the excerpt.
	//Inside the loop (or outside the loop for current post/page): nebula_excerpt(array('length' => 20, 'ellipsis' => true));
	//Outside the loop: nebula_excerpt(array('id' => 572, 'length' => 20, 'ellipsis' => true));
	//Custom text: nebula_excerpt(array('text' => 'Lorem ipsum <strong>dolor</strong> sit amet.', 'more' => 'Continue &raquo;', 'length' => 3, 'ellipsis' => true, 'strip_tags' => true));
function nebula_excerpt($options=array()){
	$override = apply_filters('pre_nebula_excerpt', false, $options);
	if ( $override !== false ){return $override;}

	$defaults = array(
		'id' => false,
		'text' => false,
		'length' => 55,
		'ellipsis' => false,
		'url' => false,
		'more' => 'Read More &raquo;',
		'strip_shortcodes' => true,
		'strip_tags' => true,
	);

	$data = array_merge($defaults, $options);

	//Establish text
	if ( empty($data['text']) ){
		$the_post = ( !empty($data['id']) && is_int($data['id']) )? get_post($data['id']) : get_post(get_the_ID());
		if ( empty($the_post) ){
			return false;
		}
		$data['text'] = ( !empty($the_post->post_excerpt) )? $the_post->post_excerpt : $the_post->post_content;
	}

	//Strip Shortcodes
	if ( $data['strip_shortcodes'] ){
		$data['text'] = strip_shortcodes($data['text']);
	} else {
		$data['text'] = preg_replace('~(?:\[/?)[^/\]]+/?\]~s', ' ', $data['text']);
	}

	//Strip Tags
	if ( $data['strip_tags'] ){
		$data['text'] = strip_tags($data['text'], '');
	}

	//Length
	if ( !empty($data['length']) && is_int($data['length']) ){
		$limited = string_limit_words($data['text'], $data['length']); //Returns array: $limited[0] is the string, $limited[1] is boolean if it was limited or not.
		$data['text'] = $limited['text'];
	}

	//Ellipsis
	if ( $data['ellipsis'] && !empty($limited['is_limited']) ){
		$data['text'] .= '&hellip;';
	}

	//Link
	if ( !empty($data['more']) ){
		if ( empty($data['url']) ){ //If has "more" text, but no link URL
			$data['url'] = ( !empty($data['id']) )? get_permalink($data['id']) : get_permalink(get_the_id()); //Use the ID if available, or use the current ID.
		}

		$data['text'] .= ' <a class="nebula_excerpt" href="' . $data['url'] . '">' . $data['more'] . '</a>';
	}

	return $data['text'];
}

//Replace text on password protected posts to be more minimal
add_filter('the_password_form', 'nebula_password_form_simplify');
function nebula_password_form_simplify(){
    $output  = '<form action="' . esc_url(site_url('wp-login.php?action=postpass', 'login_post')) . '" method="post">';
	    $output .= '<span>Password: </span>';
	    $output .= '<input name="post_password" type="password" size="20" />';
	    $output .= '<input type="submit" name="Submit" value="Go" />';
    $output .= '</form>';
    return $output;
}

//Breadcrumbs
function nebula_breadcrumbs($options=array()){
	$override = apply_filters('pre_nebula_breadcrumbs', false);
	if ( $override !== false ){echo $override; return;}

	global $post;
	$defaults = array(
		'delimiter' => '&rsaquo;', //Delimiter between crumbs
		'home' => '<i class="fa fa-home"></i>', //Text for the 'Home' link
		'home_link' => home_url('/'),
		'current' => true, //Show/Hide the current title in the breadcrumb
		'before' => '<span class="current">', //Tag before the current crumb
		'after' => '</span>', //Tag after the current crumb
		'force' => false //Override the breadcrumbs with an array of specific links
	);

	$data = array_merge($defaults, $options);
	$delimiter_html = '<span class="arrow">' . $data['delimiter'] . '</span>';

	if ( !empty($data['force']) ){ //If using forced override
		echo '<div class="nebula-breadcrumbs" itemscope itemtype="http://schema.org/BreadcrumbList">';

		foreach ( $data['force'] as $node ){
			$node_text = ( !empty($node['text']) )? $node['text'] : $node[0];
			$node_url = false;
			if ( !empty($node['url']) ){
				$node_url = $node['url'];
			} else {
				if ( !empty($node[1]) ){
					$node_url = $node[1];
				}
			}

			if ( !empty($node_text) ){
				if ( !empty($node_url) ){
					echo '<a href="' . $node_url . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">';
				}
				echo $node_text;
				if ( !empty($node_url) ){
					echo '</a>';
				}
				echo ' ' . $delimiter_html . ' ';
			}
		}

		if ( !empty($data['current']) ){
			echo $data['before'] . get_the_title() . $data['after'];
		}

		echo '</div>';
	} elseif ( is_home() || is_front_page() ){
		echo '<div class="nebula-breadcrumbs" itemscope itemtype="http://schema.org/BreadcrumbList"><a href="' . $data['home_link'] . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">' . $data['home'] . '</a></div></div>';
		return false;
	} else {
		echo '<div class="nebula-breadcrumbs" itemscope itemtype="http://schema.org/BreadcrumbList"><a href="' . $data['home_link'] . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">' . $data['home'] . '</a> ' . $delimiter_html . ' ';
		if ( is_category() ){
			$thisCat = get_category(get_query_var('cat'), false);
			if ( $thisCat->parent != 0 ){
				echo get_category_parents($thisCat->parent, true, ' ' . $delimiter_html . ' ');
			}
			echo $data['before'] . 'Category: ' . single_cat_title('', false) . $data['after'];
		} elseif ( is_search() ){
			echo $data['before'] . 'Search results' . $data['after'];
		} elseif ( is_day() ){
			echo '<a href="' . get_year_link(get_the_time('Y')) . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">' . get_the_time('Y') . '</a> ' . $delimiter_html . ' ';
			echo '<a href="' . get_month_link(get_the_time('Y'),get_the_time('m')) . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">' . get_the_time('F') . '</a> ' . $delimiter_html . ' ';
			echo $data['before'] . get_the_time('d') . $data['after'];
		} elseif ( is_month() ){
			echo '<a href="' . get_year_link(get_the_time('Y')) . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">' . get_the_time('Y') . '</a> ' . $delimiter_html . ' ';
			echo $data['before'] . get_the_time('F') . $data['after'];
		} elseif ( is_year() ){
			echo $data['before'] . get_the_time('Y') . $data['after'];
		} elseif ( is_single() && !is_attachment() ){
			if ( get_post_type() != 'post' ){
				$post_type = get_post_type_object(get_post_type());
				$slug = $post_type->rewrite;
				echo '<a href="' . $data['home_link'] . $slug['slug'] . '/">' . $post_type->labels->singular_name . '</a>';
				if ( !empty($data['current']) ){
					echo ' ' . $delimiter_html . ' ' . $data['before'] . get_the_title() . $data['after'];
				}
			} else {
				$cat = get_the_category();
				if ( !empty($cat) ){
					$cat = $cat[0];
					$cats = get_category_parents($cat, true, ' ' . $delimiter_html . ' ');
					if ( empty($data['current']) ){
						$cats = preg_replace("#^(.+)\s" . $delimiter_html . "\s$#", "$1", $cats);
					}
					echo $cats;
					if ( !empty($data['current']) ){
						echo $data['before'] . get_the_title() . $data['after'];
					}
				}
			}
		} elseif ( !is_single() && !is_page() && get_post_type() != 'post' && !is_404() ){
			if ( is_archive() ){ //@TODO "Nebula" 0: Might not be perfect... This may never else out.
				$userdata = get_user_by('slug', get_query_var('author_name'));
				echo $data['before'] . $userdata->first_name . ' ' . $userdata->last_name . $data['after'];
			} else { //What does this one do?
				$post_type = get_post_type_object(get_post_type());
				echo $data['before'] . $post_type->labels->singular_name . $data['after'];
			}
		} elseif ( is_attachment() ){ //@TODO "Nebula" 0: Check for gallery pages? If so, it should be Home > Parent(s) > Gallery > Attachment
			if ( !empty($post->post_parent) ){ //@TODO "Nebula" 0: What happens if the page parent is a child of another page?
				echo '<a href="' . get_permalink($post->post_parent) . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">' . get_the_title($post->post_parent) . '</a>' . ' ' . $delimiter_html . ' ' . get_the_title();
			} else {
				echo get_the_title();
			}
		} elseif ( is_page() && !$post->post_parent ){
			if ( !empty($data['current']) ){
				echo $data['before'] . get_the_title() . $data['after'];
			}
		} elseif ( is_page() && $post->post_parent ){
			$parent_id = $post->post_parent;
			$breadcrumbs = array();
			while ( $parent_id ){
				$page = get_page($parent_id);
				$breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">' . get_the_title($page->ID) . '</a>';
				$parent_id  = $page->post_parent;
			}
			$breadcrumbs = array_reverse($breadcrumbs);
			for ( $i = 0; $i < count($breadcrumbs); $i++ ){
				echo $breadcrumbs[$i];
				if ( $i != count($breadcrumbs)-1 ){
					echo ' ' . $delimiter_html . ' ';
				}
			}
			if ( !empty($data['current']) ){
				echo ' ' . $delimiter_html . ' ' . $data['before'] . get_the_title() . $data['after'];
			}
		} elseif ( is_tag() ){
			echo $data['before'] . 'Tag: ' . single_tag_title('', false) . $data['after'];
		} elseif ( is_author() ){
			global $author;
			$userdata = get_userdata($author);
			echo $data['before'] . $userdata->display_name . $data['after'];
		} elseif ( is_404() ){
			echo $data['before'] . 'Error 404' . $data['after'];
		}

		if ( get_query_var('paged') ){
			echo ' (Page ' . get_query_var('paged') . ')';
		}
		echo '</div>';
	}
}

//Always get custom fields with post queries
add_filter('the_posts', 'nebula_always_get_post_custom');
function nebula_always_get_post_custom($posts){
    for ( $i = 0; $i < count($posts); $i++ ){
        $custom_fields = get_post_custom($posts[$i]->ID);
        $posts[$i]->custom_fields = $custom_fields;
    }
    return $posts;
}

//Prevent empty search query error (Show all results instead)
add_action('pre_get_posts', 'redirect_empty_search');
function redirect_empty_search($query){
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
add_action('template_redirect', 'redirect_single_post');
function redirect_single_post(){
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

//Modified WordPress search form using Bootstrap components
function nebula_search_form($placeholder=''){
	$override = apply_filters('pre_nebula_search_form', false, $placeholder);
	if ( $override !== false ){echo $override; return;}

	$value = $placeholder;
    if ( empty($placeholder) ){
		$placeholder = 'Search';
	    if ( get_search_query() ){
		    $value = get_search_query();
		    $placeholder = get_search_query();
	    }
    }

	$form = '<form id="searchform" class="form-inline" role="search" method="get" action="' . home_url('/') . '">
				<div class="input-group mb-2 mr-sm-2 mb-sm-0">
					<div class="input-group-addon"><i class="fa fa-search"></i></div>
					<input id="s" class="form-control" type="text" name="s" value="' . $value . '" placeholder="' . $placeholder . '">
				</div>

				<button id="searchsubmit" class="btn btn-brand wp_search_submit" type="submit">Submit</button>
			</form>';

    return $form;
}

//Easily create markup for a Hero area search input
function nebula_hero_search($placeholder='What are you looking for?'){
	$override = apply_filters('pre_nebula_hero_search', false, $placeholder);
	if ( $override !== false ){echo $override; return;}

	$form = '<div id="nebula-hero-formcon">
			<form id="nebula-hero-search" class="nebula-search-iconable search" method="get" action="' . home_url('/') . '">
				<input type="search" class="nebula-search open input search nofade" name="s" placeholder="' . $placeholder . '" autocomplete="off" x-webkit-speech />
			</form>
		</div>';
	return $form;
}

//Autocomplete Search AJAX.
add_action('wp_ajax_nebula_autocomplete_search', 'nebula_autocomplete_search');
add_action('wp_ajax_nopriv_nebula_autocomplete_search', 'nebula_autocomplete_search');
function nebula_autocomplete_search(){
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
add_action('wp_ajax_nebula_advanced_search', 'nebula_advanced_search');
add_action('wp_ajax_nopriv_nebula_advanced_search', 'nebula_advanced_search');
function nebula_advanced_search(){
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

//Infinite Load
function nebula_infinite_load_query($args=array('post_status' => 'publish', 'showposts' => 4), $loop=false){
	$override = apply_filters('pre_nebula_infinite_load_query', false);
	if ( $override !== false ){return;}

	global $wp_query;
	if ( empty($args['paged']) ){
		$args['paged'] = 1;
		if ( get_query_var('paged') ){
			$args['paged'] = get_query_var('paged');
			?>
			<div class="infinite-start-note">
				<a href="<?php echo get_the_permalink(); ?>">&laquo; Back to page 1</a>
			</div>
			<?php
		} elseif ( !empty($wp_query->query['paged']) ){
			$args['paged'] = $wp_query->query['paged'];
			?>
			<div class="infinite-start-note">
				<a href="<?php echo get_the_permalink(); ?>">&laquo; Back to page 1</a>
			</div>
			<?php
		}
	}

	query_posts($args);

	if ( empty($args['post_type']) ){
		$post_type_label = 'posts';
	} else {
		$post_type = ( is_array($args['post_type']) )? $args['post_type'][0] : $args['post_type'];
		$post_type_obj = get_post_type_object($args['post_type']);
		$post_type_label = lcfirst($post_type_obj->label);
	}
	?>

	<div id="infinite-posts-list" data-max-pages="<?php echo $wp_query->max_num_pages; ?>" data-max-posts="<?php echo $wp_query->found_posts; ?>">
		<?php
			$loop = sanitize_text_field($loop);
			if ( !$loop ){
				get_template_part('loop');
			} else {
				if ( function_exists($loop) ){
					call_user_func($loop);
				} elseif ( locate_template($loop . '.php') ){
					get_template_part($loop);
				} else {
					if ( is_dev() ){
						echo '<strong>Warning:</strong> The custom loop template or function ' . $loop . ' does not exist! Falling back to loop.php.';
					}
					get_template_part('loop');
				}
			}
		?>
	</div>

	<?php do_action('nebula_infinite_before_load_more'); ?>

	<div class="loadmorecon <?php echo ( $args['paged'] >= $wp_query->max_num_pages )? 'disabled' : ''; ?>">
		<a class="infinite-load-more" href="#"><?php echo ( $args['paged'] >= $wp_query->max_num_pages )? 'No more ' . $post_type_label . '.' : 'Load More'; ?></a>
		<div class="infinite-loading">
			<div class="a"></div> <div class="b"></div> <div class="c"></div>
		</div>
	</div>

	<script><?php //Must be in PHP so $args can be encoded. ?>
		jQuery(document).on('ready', function(){
			var pageNumber = <?php echo $args['paged']; ?>+1;

			jQuery('.infinite-load-more').on('click touch tap', function(){
				var maxPages = jQuery('#infinite-posts-list').attr('data-max-pages');
				var maxPosts = jQuery('#infinite-posts-list').attr('data-max-posts');

				if ( pageNumber <= maxPages ){
					jQuery('.loadmorecon').addClass('loading');
			        jQuery.ajax({
						type: "POST",
						url: nebula.site.ajax.url,
						data: {
							nonce: nebula.site.ajax.nonce,
							action: 'nebula_infinite_load',
							page: pageNumber,
							args: <?php echo json_encode($args); ?>,
							loop: <?php echo json_encode($loop); ?>,
						},
						success: function(response){
							jQuery("#infinite-posts-list").append('<div class="clearfix infinite-page infinite-page-' + (pageNumber-1) + ' sliding" style="display: none;">' + response + '</div>');
							jQuery('.infinite-page-' + (pageNumber-1)).slideDown({
			                    duration: 750,
			                    easing: 'easeInOutQuad',
			                    complete: function(){
				                    jQuery('.loadmorecon').removeClass('loading');
				                    jQuery('.infinite-page.sliding').removeClass('sliding');
				                    nebula.dom.document.trigger('nebula_infinite_slidedown_complete');
			                    }
			                });

							if ( pageNumber >= maxPages ){
								jQuery('.loadmorecon').addClass('disabled').find('a').text('No more <?php echo $post_type_label; ?>.');
							}

							var newQueryStrings = '';
							if ( typeof document.URL.split('?')[1] !== 'undefined' ){
								newQueryStrings = '?' + document.URL.split('?')[1].replace(/[?&]paged=\d+/, '');
							}

							history.replaceState(null, document.title, nebula.post.permalink + 'page/' + pageNumber + newQueryStrings);
							nebula.dom.document.trigger('nebula_infinite_finish');
							ga('set', gaCustomDimensions['timestamp'], localTimestamp());
							ga('send', 'event', 'Infinite Query', 'Load More', 'Loaded page ' + pageNumber);
							nv('increment', 'infinite_query_loads');
							pageNumber++;
						},
						error: function(XMLHttpRequest, textStatus, errorThrown){
							jQuery(document).trigger('nebula_infinite_finish');
							ga('set', gaCustomDimensions['timestamp'], localTimestamp());
							ga('send', 'event', 'Error', 'AJAX Error', 'Infinite Query Load More AJAX');
							nv('increment', 'ajax_error');
						},
						timeout: 60000
					});
				}
				return false;
			});
		});
	</script>
	<?php
}

//Infinite Load AJAX Call
add_action('wp_ajax_nebula_infinite_load', 'nebula_infinite_load');
add_action('wp_ajax_nopriv_nebula_infinite_load', 'nebula_infinite_load');
function nebula_infinite_load(){
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
add_action('wp', 'nebula_404_internal_suggestions');
function nebula_404_internal_suggestions(){
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
add_filter('body_class', 'nebula_body_classes');
function nebula_body_classes($classes){
	if ( !nebula_is_ajax_request() ){
		$spaces_and_dots = array(' ', '.');
		$underscores_and_hyphens = array('_', '-');

		//Device
		$classes[] = strtolower(nebula_get_device('formfactor')); //Form factor (desktop, tablet, mobile)
		$classes[] = strtolower(nebula_get_device('full')); //Device make and model
		$classes[] = strtolower(str_replace($spaces_and_dots, $underscores_and_hyphens, nebula_get_os('full'))); //Operating System name with version
		$classes[] = strtolower(str_replace($spaces_and_dots, $underscores_and_hyphens, nebula_get_os('name'))); //Operating System name
		$classes[] = strtolower(str_replace($spaces_and_dots, $underscores_and_hyphens, nebula_get_browser('full'))); //Browser name and version
		$classes[] = strtolower(str_replace($spaces_and_dots, $underscores_and_hyphens, nebula_get_browser('name'))); //Browser name
		$classes[] = strtolower(str_replace($spaces_and_dots, $underscores_and_hyphens, nebula_get_browser('engine'))); //Rendering engine

		//When installed to the homescreen, Chrome is detected as "Chrome Mobile". Supplement it with a "chrome" class.
		if ( nebula_get_browser('name') == 'Chrome Mobile' ){
			$classes[] = 'chrome';
		}

		//IE versions outside conditional comments
		if ( nebula_is_browser('ie') ){
			if ( nebula_is_browser('ie', '10') ){
				$classes[] = 'ie';
				$classes[] = 'ie10';
				$classes[] = 'lte-ie10';
				$classes[] = 'lt-ie11';
			} elseif ( nebula_is_browser('ie', '11') ){
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
		if ( !is_404() && !is_search() && !is_archive() && !is_front_page() ){
			global $post;
			if ( isset($post->ID) ){
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
}

//Add additional classes to post wrappers
add_filter('post_class', 'nebula_post_classes');
function nebula_post_classes($classes){
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
add_filter('wp_get_attachment_url', 'wp_get_attachment_url_example');
function wp_get_attachment_url_example($url){
    $http = site_url(false, 'http');
    $https = site_url(false, 'https');

    if ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ){
        return str_replace($http, $https, $url);
    } else {
        return $url;
    }
}

//Check if business hours exist in Nebula Options
function has_business_hours(){
	foreach ( array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday') as $weekday ){
		if ( nebula_option('business_hours_' . $weekday . '_enabled') || nebula_option('business_hours_' . $weekday . '_open') || nebula_option('business_hours_' . $weekday . '_close') ){
			return true;
		}
	}
	return false;
}

//Check if the requested datetime is within business hours.
//If $general is true this function returns true if the business is open at all on that day
function is_business_open($date=null, $general=false){ return business_open($date, $general); }
function is_business_closed($date=null, $general=false){ return !business_open($date, $general); }
function business_open($date=null, $general=false){
	$override = apply_filters('pre_business_open', false, $date, $general);
	if ( $override !== false ){return $override;}

	if ( empty($date) || $date == 'now' ){
		$date = time();
	} elseif ( strtotime($date) ){
		$date = strtotime($date . ' ' . date('g:ia', strtotime('now')));
	}
	$today = strtolower(date('l', $date));

	$businessHours = array();
	foreach ( array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday') as $weekday ){
		if ( isset($businessHours[$weekday]) ){
			$businessHours[$weekday] = array(
				'enabled' => nebula_option('business_hours_' . $weekday . '_enabled'),
				'open' => nebula_option('business_hours_' . $weekday . '_open'),
				'close' => nebula_option('business_hours_' . $weekday . '_close')
			);
		}
	}

	if ( empty($businessHours) ){
		return false;
	}

	$days_off = array_filter(explode(', ', nebula_option('business_hours_closed')));
    if ( !empty($days_off) ){
		foreach ( $days_off as $key => $day_off ){
			$days_off[$key] = strtotime($day_off . ' ' . date('Y', $date));

			if ( date('N', $days_off[$key]) == 6 ){ //If the date is a Saturday
				$days_off[$key] = strtotime(date('F j, Y', $days_off[$key]) . ' -1 day');
			} elseif ( date('N', $days_off[$key]) == 7 ){ //If the date is a Sunday
				$days_off[$key] = strtotime(date('F j, Y', $days_off[$key]) . ' +1 day');
			}

			if ( date('Ymd', $days_off[$key]) == date('Ymd', $date) ){
				return false;
			}
		}
	}

	if ( $businessHours[$today]['enabled'] == '1' ){ //If the Nebula Options checkmark is checked for this day of the week.
		if ( !empty($general) ){
			return true;
		}

		$openToday = date('Gi', strtotime($businessHours[$today]['open']));
		$closeToday = date('Gi', strtotime($businessHours[$today]['close'])-1); //Subtract one second to ensure midnight represents the same day
		if ( date('Gi', $date) >= $openToday && date('Gi', $date) <= $closeToday ){
			return true;
		}
	}

	return false;
}

//If the business is open, return the time that the business closes today
function business_open_until(){
    if ( is_business_open() ){
        return nebula_option('business_hours_' . $weekday . '_close');
    }

    return false;
}


//Get the relative time of day
function nebula_relative_time($format=null){
	$override = apply_filters('pre_nebula_relative_time', false, $format);
	if ( $override !== false ){return $override;}

	if ( contains(date('H'), array('00', '01', '02')) ){
		$relative_time = array(
			'description' => array('early', 'night'),
			'standard' => array(0, 1, 2),
			'military' => array(0, 1, 2),
			'ampm' => 'am'
		);
	} elseif ( contains(date('H'), array('03', '04', '05')) ){
		$relative_time = array(
			'description' => array('late', 'night'),
			'standard' => array(3, 4, 5),
			'military' => array(3, 4, 5),
			'ampm' => 'am'
		);
	} elseif ( contains(date('H'), array('06', '07', '08')) ){
		$relative_time = array(
			'description' => array('early', 'morning'),
			'standard' => array(6, 7, 8),
			'military' => array(6, 7, 8),
			'ampm' => 'am'
		);
	} elseif ( contains(date('H'), array('09', '10', '11')) ){
		$relative_time = array(
			'description' => array('late', 'morning'),
			'standard' => array(9, 10, 11),
			'military' => array(9, 10, 11),
			'ampm' => 'am'
		);
	} elseif ( contains(date('H'), array('12', '13', '14')) ){
		$relative_time = array(
			'description' => array('early', 'afternoon'),
			'standard' => array(12, 1, 2),
			'military' => array(12, 13, 14),
			'ampm' => 'pm'
		);
	} elseif ( contains(date('H'), array('15', '16', '17')) ){
		$relative_time = array(
			'description' => array('late', 'afternoon'),
			'standard' => array(3, 4, 5),
			'military' => array(15, 16, 17),
			'ampm' => 'pm'
		);
	} elseif ( contains(date('H'), array('18', '19', '20')) ){
		$relative_time = array(
			'description' => array('early', 'evening'),
			'standard' => array(6, 7, 8),
			'military' => array(18, 19, 20),
			'ampm' => 'pm'
		);
	} elseif ( contains(date('H'), array('21', '22', '23')) ){
		$relative_time = array(
			'description' => array('late', 'evening'),
			'standard' => array(9, 10, 11),
			'military' => array(21, 22, 23),
			'ampm' => 'pm'
		);
	}

	if ( !empty($format) ){
		return $relative_time[$format];
	} else {
		return $relative_time;
	}
}

//Detect location from IP address using https://freegeoip.net/
function nebula_ip_location($data=null, $ip_address=false){
	if ( nebula_option('ip_geolocation') && !nebula_is_ajax_request() ){
		if ( empty($ip_address) ){
			$ip_address = $_SERVER['REMOTE_ADDR'];

			if ( empty($data) ){
				return true; //If passed with no parameters, simply check if Nebula Option is enabled
			}
		}

		//Check cache first
		$ip_geo_data = wp_cache_get('nebula_ip_geolocation_' . str_replace('.', '_', $ip_address));
		if ( empty($ip_geo_data) ){
			//Check session next
			if ( !empty($_SESSION['nebula_ip_geolocation']) ){
				$ip_geo_data = $_SESSION['nebula_ip_geolocation'];
			}

			//Get new remote data
			if ( empty($_SESSION['nebula_ip_geolocation']) && nebula_is_available('http://freegeoip.net') ){
				$response = wp_remote_get('http://freegeoip.net/json/' . $ip_address);
				if ( is_wp_error($response) || !is_array($response) || strpos($response['body'], 'Rate limit') === 0 ){
					nebula_set_unavailable('http://freegeoip.net');
					return false;
				}

				$ip_geo_data = $response['body'];
				$_SESSION['nebula_ip_geolocation'] = $ip_geo_data;
			}

			wp_cache_set('nebula_ip_geolocation_' . str_replace('.', '_', $ip_address), $ip_geo_data); //Cache the result
		}

		if ( !empty($ip_geo_data) ){
			$ip_geo_data = json_decode($ip_geo_data);
			if ( !empty($ip_geo_data) ){
				switch ( str_replace(array(' ', '_', '-'), '', $data) ){
					case 'all':
					case 'object':
					case 'response':
						return $ip_geo_data;
					case 'country':
					case 'countryname':
						return $ip_geo_data->country_name;
						break;
					case 'countrycode':
						return $ip_geo_data->country_code;
						break;
					case 'region':
					case 'state':
					case 'regionname':
					case 'statename':
						return $ip_geo_data->region_name;
						break;
					case 'regioncode':
					case 'statecode':
						return $ip_geo_data->country_code;
						break;
					case 'city':
						return $ip_geo_data->city;
						break;
					case 'zip':
					case 'zipcode':
						return $ip_geo_data->zip_code;
						break;
					case 'lat':
					case 'latitude':
						return $ip_geo_data->latitude;
						break;
					case 'lng':
					case 'longitude':
						return $ip_geo_data->longitude;
						break;
					case 'geo':
					case 'coordinates':
						return $ip_geo_data->latitude . ',' . $ip_geo_data->longitude;
						break;
					case 'timezone':
						return $ip_geo_data->time_zone;
						break;
					default:
						return false;
						break;
				}
			}
		}
	}

	return false;
}

//Detect weather for Zip Code (using Yahoo! Weather)
//https://developer.yahoo.com/weather/
function nebula_weather($zipcode=null, $data=''){
	if ( nebula_option('weather') && !nebula_is_ajax_request() ){
		$override = apply_filters('pre_nebula_weather', false, $zipcode, $data);
		if ( $override !== false ){return $override;}

		if ( !empty($zipcode) && is_string($zipcode) && !ctype_digit($zipcode) ){ //ctype_alpha($zipcode)
			$data = $zipcode;
			$zipcode = nebula_option('postal_code', '13204');
		} elseif ( empty($zipcode) ){
			$zipcode = nebula_option('postal_code', '13204');
		}

		$weather_json = get_transient('nebula_weather_' . $zipcode);
		if ( empty($weather_json) ){ //No ?debug option here (because multiple calls are made to this function). Clear with a force true when needed.
			$yql_query = 'select * from weather.forecast where woeid in (select woeid from geo.places(1) where text=' . $zipcode . ')';

			if ( !nebula_is_available('http://query.yahooapis.com/v1/public/yql?q=' . urlencode($yql_query) . '&format=json') ){
				trigger_error('A Yahoo Weather API error occurred. Yahoo may be down, or forecast for ' . $zipcode . ' may not exist.', E_USER_WARNING);
				return false;
			}
			$response = wp_remote_get('http://query.yahooapis.com/v1/public/yql?q=' . urlencode($yql_query) . '&format=json');
			if ( is_wp_error($response) ){
				nebula_set_unavailable('http://query.yahooapis.com/v1/public/yql');
				trigger_error('A Yahoo Weather API error occurred. Yahoo may be down, or forecast for ' . $zipcode . ' may not exist.', E_USER_WARNING);
				return false;
			}

			$weather_json = $response['body'];
			set_transient('nebula_weather_' . $zipcode, $weather_json, MINUTE_IN_SECONDS*30);
		}
		$weather_json = json_decode($weather_json);

		if ( !$weather_json || empty($weather_json) || empty($weather_json->query->results) ){
			trigger_error('A Yahoo Weather API error occurred. Yahoo may be down, or forecast for ' . $zipcode . ' may not exist.', E_USER_WARNING);
			return false;
		} elseif ( $data == '' ){
			return true;
		}

		switch ( str_replace(' ', '', $data) ){
			case 'json':
				return $weather_json;
				break;
			case 'reported':
			case 'build':
			case 'lastBuildDate':
				return $weather_json->query->results->channel->lastBuildDate;
				break;
			case 'city':
				return $weather_json->query->results->channel->location->city;
				break;
			case 'state':
			case 'region':
				return $weather_json->query->results->channel->location->region;
				break;
			case 'country':
				return $weather_json->query->results->channel->location->country;
				break;
			case 'location':
				return $weather_json->query->results->channel->location->city . ', ' . $weather_json->query->results->channel->location->region;
				break;
			case 'latitude':
			case 'lat':
				return $weather_json->query->results->channel->item->lat;
				break;
			case 'longitude':
			case 'long':
			case 'lng':
				return $weather_json->query->results->channel->item->long;
				break;
			case 'geo':
			case 'geolocation':
			case 'coordinates':
				return $weather_json->query->results->channel->item->lat . ',' . $weather_json->query->results->channel->item->lat;
				break;
			case 'windchill':
			case 'chill':
				return $weather_json->query->results->channel->wind->chill;
				break;
			case 'windspeed':
				return $weather_json->query->results->channel->wind->speed;
				break;
			case 'sunrise':
				return $weather_json->query->results->channel->astronomy->sunrise;
				break;
			case 'sunset':
				return $weather_json->query->results->channel->astronomy->sunset;
				break;
			case 'temp':
			case 'temperature':
				return $weather_json->query->results->channel->item->condition->temp;
				break;
			case 'condition':
			case 'conditions':
			case 'current':
			case 'currently':
				return $weather_json->query->results->channel->item->condition->text;
				break;
			case 'forecast':
				return $weather_json->query->results->channel->item->forecast;
				break;
			default:
				break;
		}
	}

	return false;
}

//Get metadata from Youtube or Vimeo
function vimeo_meta($id, $meta=''){return video_meta('vimeo', $id);}
function youtube_meta($id, $meta=''){return video_meta('youtube', $id);}
function video_meta($provider, $id){
	$override = apply_filters('pre_video_meta', false, $provider, $id);
	if ( $override !== false ){return $override;}

	$video_metadata = array(
		'origin' => nebula_url_components('basedomain'),
		'id' => $id,
		'error' => false
	);

	if ( !empty($provider) ){
		$provider = strtolower($provider);
	} else {
		$video_metadata['error'] = 'Video provider is required.';
		return $video_metadata;
	}

	//Get Transients
	$video_json = get_transient('nebula_' . $provider . '_' . $id);
	if ( empty($video_json) ){ //No ?debug option here (because multiple calls are made to this function). Clear with a force true when needed.
		if ( $provider == 'youtube' ){
			if ( !nebula_option('google_server_api_key') && is_staff() ){
				echo '<script>console.warn("No Google Youtube Iframe API key. Youtube videos may not be tracked!");</script>';
				$video_metadata['error'] = 'No Google Youtube Iframe API key.';
			}

			if ( !nebula_is_available('https://www.googleapis.com/youtube/v3/videos?id=' . $id . '&part=snippet,contentDetails,statistics&key=' . nebula_option('google_server_api_key')) ){
				$video_metadata['error'] = 'Youtube video is unavailable.';
				return $video_metadata;
			}
			$response = wp_remote_get('https://www.googleapis.com/youtube/v3/videos?id=' . $id . '&part=snippet,contentDetails,statistics&key=' . nebula_option('google_server_api_key'));
			if ( is_wp_error($response) ){
				nebula_set_unavailable('https://www.googleapis.com/youtube/v3/videos');
				$video_metadata['error'] = 'Youtube video is unavailable.';
				return $video_metadata;
			}

			$video_json = $response['body'];
		} elseif ( $provider == 'vimeo' ){
			if ( !nebula_is_available('http://vimeo.com/api/v2/video/' . $id . '.json') ){
				$video_metadata['error'] = 'Vimeo video is unavailable.';
				return $video_metadata;
			}
			$response = wp_remote_get('http://vimeo.com/api/v2/video/' . $id . '.json');
			if ( is_wp_error($response) ){
				$video_metadata['error'] = 'Vimeo video is unavailable.';
				return $video_metadata;
			}

			$video_json = $response['body'];
		}

		set_transient('nebula_' . $provider . '_' . $id, $video_json, HOUR_IN_SECONDS*12);
	}
	$video_json = json_decode($video_json);

	//Check for errors
	if ( empty($video_json) ){
		if ( current_user_can('manage_options') || is_dev() ){
			if ( $provider == 'youtube' ){
				$video_metadata['error'] = 'A Youtube Data API error occurred. Make sure the Youtube Data API is enabled in the Google Developer Console and the server key is saved in Nebula Options.';
			} else {
				$video_metadata['error'] = 'A Vimeo API error occurred (A video with ID ' . $id . ' may not exist). Tracking will not be possible.';
			}
		}
		return $video_metadata;
	} elseif ( $provider == 'youtube' && !empty($video_json->error) ){
		if ( current_user_can('manage_options') || is_dev() ){
			$video_metadata['error'] = 'Youtube API Error: ' . $video_json->error->message;
		}
		return $video_metadata;
	} elseif ( $provider == 'youtube' && empty($video_json->items) ){
		if ( current_user_can('manage_options') || is_dev() ){
			$video_metadata['error'] = 'A Youtube video with ID ' . $id . ' does not exist.';
		}
		return $video_metadata;
	} elseif ( $provider == 'vimeo' && is_array($video_json) && empty($video_json[0]) ){
		$video_metadata['error'] = 'A Vimeo video with ID ' . $id . ' does not exist.';
	}

	//Build Data
	if ( $provider == 'youtube' ){
		$video_metadata['raw'] = $video_json->items[0];
		$video_metadata['title'] = $video_json->items[0]->snippet->title;
		$video_metadata['safetitle'] = str_replace(array(" ", "'", '"'), array("-", "", ""), $video_json->items[0]->snippet->title);
		$video_metadata['description'] = $video_json->items[0]->snippet->description;
		$video_metadata['thumbnail'] = $video_json->items[0]->snippet->thumbnails->high->url;
		$video_metadata['author'] = $video_json->items[0]->snippet->channelTitle;
		$video_metadata['date'] = $video_json->items[0]->snippet->publishedAt;
		$video_metadata['url'] = 'https://www.youtube.com/watch?v=' . $id;
		$start = new DateTime('@0'); //Unix epoch
	    $start->add(new DateInterval($video_json->items[0]->contentDetails->duration));
	    $duration_seconds = intval($start->format('H'))*60*60 + intval($start->format('i'))*60 + intval($start->format('s'));
	} elseif ( $provider == 'vimeo' ){
		$video_metadata['raw'] = $video_json[0];
		$video_metadata['title'] = $video_json[0]->title;
		$video_metadata['safetitle'] = str_replace(array(" ", "'", '"'), array("-", "", ""), $video_json[0]->title);
		$video_metadata['description'] = $video_json[0]->description;
		$video_metadata['thumbnail'] = $video_json[0]->thumbnail_large;
		$video_metadata['author'] = $video_json[0]->user_name;
		$video_metadata['date'] = $video_json[0]->upload_date;
		$video_metadata['url'] = $video_json[0]->url;
		$duration_seconds = strval($video_json[0]->duration);
	}
	$video_metadata['duration'] = array(
		'time' => intval(gmdate("i", $duration_seconds)) . gmdate(":s", $duration_seconds),
		'seconds' => $duration_seconds
	);

	return $video_metadata;
}

//Fix responsive oEmbeds
//Uses Bootstrap classes: http://v4-alpha.getbootstrap.com/components/utilities/#responsive-embeds
add_filter('embed_oembed_html', 'nebula_embed_oembed_html', 9999, 4);
function nebula_embed_oembed_html($html, $url, $attr, $post_id) {
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

//Footer Widget Counter
function footer_widget_counter(){
	$footerWidgetCount = 0;
	if ( is_active_sidebar('First Footer Widget Area') ){
		$footerWidgetCount++;
	}
	if ( is_active_sidebar('Second Footer Widget Area') ){
		$footerWidgetCount++;
	}
	if ( is_active_sidebar('Third Footer Widget Area') ){
		$footerWidgetCount++;
	}
	if ( is_active_sidebar('Fourth Footer Widget Area') ){
		$footerWidgetCount++;
	}
	return $footerWidgetCount;
}