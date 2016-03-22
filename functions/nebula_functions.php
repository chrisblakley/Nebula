<?php

//Send event to Google Analytics if JavaScript is disabled
add_action('init', 'nebula_no_js_event');
function nebula_no_js_event(){
	if ( !nebula_is_bot() && isset($_GET['nonce']) && isset($_GET['js']) && $_GET['js'] == 'false' ){
		if ( !wp_verify_nonce($_GET['nonce'], 'nebula_ajax_nonce')){ die('Permission Denied.'); }

		$title = ( get_the_title($_GET['id']) )? get_the_title($_GET['id']) : '(Unknown)';

		$dimension_array = array();
		if ( nebula_option('nebula_cd_sessionnotes') ){
			$dimension_index = nebula_option('nebula_cd_sessionnotes');
			$cd_number = substr($dimension_index, strpos($dimension_index, "dimension")+9);
			$dimension_array = array('cd' . $cd_number => 'JS Disabled');
		}

		ga_send_event('JavaScript Disabled', $_SERVER['HTTP_USER_AGENT'], $title, null, 1, $dimension_array);
		header('Location: ' . nebula_prefer_child_directory('/images/no-js.gif') . '?id=' . $_GET['id']); //Redirect and parameters here do nothing (deter false data).
		die; //Die as a precaution.
	}
}

//Send analytics data if GA is blocked by JavaScript
add_action('wp_ajax_nebula_ga_blocked', 'nebula_ga_blocked');
add_action('wp_ajax_nopriv_nebula_ga_blocked', 'nebula_ga_blocked');
function nebula_ga_blocked(){
	if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce')){ die('Permission Denied.'); }
	$post_id = $_POST['data'][0]['id'];
	$dimension_array = array();

	if ( nebula_option('nebula_cd_sessionid') ){
		$dimension_index = nebula_option('nebula_cd_sessionid');
		$cd_number = substr($dimension_index, strpos($dimension_index, "dimension")+9);
		$dimension_array['cd' . $cd_number] = nebula_session_id();
	}

	if ( nebula_option('nebula_cd_sessionnotes') ){
		$dimension_index = nebula_option('nebula_cd_sessionnotes');
		$cd_number = substr($dimension_index, strpos($dimension_index, "dimension")+9);
		$dimension_array['cd' . $cd_number] = 'GA Blocked';
	}

	ga_send_pageview(nebula_url_components('hostname'), nebula_url_components('path', get_permalink($post_id)), get_the_title($post_id), $dimension_array);
	ga_send_event('Google Analytics Blocked', $_SERVER['HTTP_USER_AGENT'], get_the_title($post_id));
}


//Set server timezone to match Wordpress
add_action('init', 'nebula_set_default_timezone', 1);
add_action('admin_init', 'nebula_set_default_timezone', 1);
function nebula_set_default_timezone(){
	date_default_timezone_set(nebula_option('timezone_string', 'America/New_York'));
}

//Add the calling card to the browser console
if ( nebula_option('nebula_console_css') ){
	add_action('wp_head', 'nebula_calling_card');
	function nebula_calling_card(){
		$console_log = "<script>";
		if ( nebula_is_desktop() && !nebula_is_browser('ie') && !nebula_is_browser('edge') ){
			//$console_log .= "console.log('%c', 'padding: 28px 119px; line-height: 35px; background: url(" . get_template_directory_uri() . "/images/phg/phg-logo.png) no-repeat; background-size: auto 60px;');"; //@TODO "Nebula" 0: This isn't working on many browsers...
			$console_log .= "console.log('%c Created using Nebula ', 'padding: 2px 10px; background: #0098d7; color: #fff;');";
		}
		$console_log .= "</script>";
		echo $console_log;
	}
}

//Check for warnings and send them to the console.
add_action('wp_head', 'nebula_console_warnings');
function nebula_console_warnings($console_warnings=array()){
	if ( is_dev() && nebula_option('nebula_admin_notices') ){
		//If search indexing is disabled
		if ( get_option('blog_public') == 0 ){
			if ( is_site_live() ){
				$console_warnings[] = array('error', 'Search Engine Visibility is currently disabled!');
			} elseif ( nebula_option('nebula_wireframing', 'disabled') ){
				$console_warnings[] = array('warn', 'Search Engine Visibility is currently disabled.');
			}
		}

		if ( is_site_live() && nebula_option('nebula_wireframing', 'enabled') ){
			$console_warnings[] = array('error', 'Wireframing mode is still enabled!');
		}

		//If no Google Analytics tracking ID
		if ( empty($GLOBALS['ga']) ){
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
	$GLOBALS['manifest_json'] = '/includes/manifest.json';
	if ( !file_exists(get_template_directory() . $GLOBALS['manifest_json']) || filemtime(get_template_directory() . $GLOBALS['manifest_json']) > (time()-(60*60*24)) || is_debug() ){
		add_action('init', 'nebula_manifest_json');
		add_action('admin_init', 'nebula_manifest_json');
	}
}
function nebula_manifest_json(){
	$override = apply_filters('pre_nebula_manifest_json', false);
	if ( $override !== false ){return;}

	$manifest_json = '{
	"short_name": "' . get_bloginfo('name') . '",
	"name": "' . get_bloginfo('name') . ': ' . get_bloginfo('description') . '",
	"gcm_sender_id": "' . nebula_option('nebula_gcm_sender_id') . '",
	"icons": [{
		"src": "' . get_template_directory_uri() . '/images/meta/apple-touch-icon-36x36.png",
		"sizes": "36x36",
		"type": "image/png",
		"density": "0.75"
	}, {
		"src": "' . get_template_directory_uri() . '/images/meta/apple-touch-icon-48x48.png",
		"sizes": "48x48",
		"type": "image/png",
		"density": "1.0"
	}, {
		"src": "' . get_template_directory_uri() . '/images/meta/apple-touch-icon-72x72.png",
		"sizes": "72x72",
		"type": "image/png",
		"density": "1.5"
	}, {
		"src": "' . get_template_directory_uri() . '/images/meta/favicon-96x96.png",
		"sizes": "96x96",
		"type": "image/png",
		"density": "2.0"
	}, {
		"src": "' . get_template_directory_uri() . '/images/meta/apple-touch-icon-144x144.png",
		"sizes": "144x144",
		"type": "image/png",
		"density": "3.0"
	}, {
		"src": "' . get_template_directory_uri() . '/images/meta/favicon-192x192.png",
		"sizes": "192x192",
		"type": "image/png",
		"density": "4.0"
	}],
	"start_url": "' . home_url() . '?hs=1",
	"display": "standalone",
	"orientation": "portrait"
}';

	//@TODO "Nebula" 0: "start_url" with a query string is not working. Manifest is confirmed working, just not the query string.

	WP_Filesystem();
	global $wp_filesystem;
	$wp_filesystem->put_contents(get_template_directory() . $GLOBALS['manifest_json'], $manifest_json);
}

//Redirect to favicon to force-clear the cached version when ?favicon is added.
add_action('wp_loaded', 'nebula_favicon_cache');
function nebula_favicon_cache(){
	if ( array_key_exists('favicon', $_GET) ){
		header('Location: ' . get_template_directory_uri() . '/images/meta/favicon.ico');
	}
}

//Determing if a page should be prepped using prerender/prefetch (Can be updated w/ JS).
//If an eligible page is determined after load, use the JavaScript nebulaPrerender(url) function.
//Use the Audience > User Flow report in Google Analytics for better predictions.
function nebula_prerender(){
	$prerender_url = false;
	if ( is_404() ){
		$prerender_url = home_url('/');
	} elseif ( is_front_page() ){
		$prerender_url = ''; //@TODO "Nebula" 0: Contact page or something?
	} elseif ( !is_front_page() ){
		$prerender_url = home_url('/');
	}

	if ( !empty($prerender_url) ){
		echo '<link id="prerender" rel="prerender prefetch" href="' . $prerender_url . '">';
	}
}

//Allow pages to have excerpts too
add_post_type_support('page', 'excerpt');

//Add/Remove Theme Support
add_theme_support('post-thumbnails');
add_theme_support('title-tag'); //Title tag support allows WordPress core to create the <title> tag.
add_theme_support('html5', array('comment-list', 'comment-form', 'search-form', 'gallery', 'caption'));
remove_theme_support('custom-background');
remove_theme_support('custom-header');

//Add new image sizes
add_image_size('open_graph_large', 1200, 630, 1);
add_image_size('open_graph_small', 600, 315, 1);

//Determine if the author should be the Company Name or the specific author's name.
function nebula_the_author($show_authors=1){
	$override = apply_filters('pre_nebula_the_author', false, $show_authors);
	if ( $override !== false ){return $override;}

	if ( !is_single() || $show_authors == 0 || nebula_option('nebula_author_bios', 'disabled') ){
		return get_option('nebula_site_owner', get_bloginfo('name'));
	} else {
		return ( get_the_author_meta('first_name') != '' )? get_the_author_meta('first_name') . ' ' . get_the_author_meta('last_name') : get_the_author_meta('display_name');
	}
}

//Register Widget Areas
add_action('widgets_init', 'nebula_widgets_init');
function nebula_widgets_init(){
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
add_action('after_setup_theme', 'nav_menu_locations');
function nav_menu_locations(){
	$override = apply_filters('pre_nav_menu_locations', false);
	if ( $override !== false ){return;}

	register_nav_menus(array(
		'secondary' => 'Secondary Menu',
		'primary' => 'Primary Menu',
		'mobile' => 'Mobile Menu',
		'sidebar' => 'Sidebar Menu',
		'footer' => 'Footer Menu'
	));
}

//Update user online status
add_action('init', 'nebula_users_status_init');
add_action('admin_init', 'nebula_users_status_init');
function nebula_users_status_init(){
	$logged_in_users = get_transient('users_status');
	$unique_id = $_SERVER['REMOTE_ADDR'] . '.' . preg_replace("/[^a-zA-Z0-9]+/", "", $_SERVER['HTTP_USER_AGENT']);
	$current_user = wp_get_current_user();

	//@TODO "Nebula" 0: Technically, this should be sorted by user ID -then- unique id -then- the rest of the info. Currently, concurrent logins won't reset until they have ALL expired. This could be good enough, though.

	if ( !isset($logged_in_users[$current_user->ID]['last']) || $logged_in_users[$current_user->ID]['last'] < time()-900 ){ //If a last login time does not exist for this user -or- if the time exists but is greater than 15 minutes, update.
		$logged_in_users[$current_user->ID] = array(
			'id' => $current_user->ID,
			'username' => $current_user->user_login,
			'last' => time(),
			'unique' => array($unique_id),
		);
		set_transient('users_status', $logged_in_users, 1800); //30 minutes
	} else {
		if ( !in_array($unique_id, $logged_in_users[$current_user->ID]['unique']) ){
			array_push($logged_in_users[$current_user->ID]['unique'], $unique_id);
			set_transient('users_status', $logged_in_users, 1800); //30 minutes
		}
	}
}

if ( nebula_option('nebula_comments', 'disabled') || get_option('nebula_disqus_shortname') ){ //If WP core comments are disabled -or- if Disqus is enabled
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
	if ( nebula_option('nebula_admin_bar', 'enabled') ){
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
	if ( $pagenow == 'edit-comments.php' && get_option('nebula_disqus_shortname') ){
		add_action('admin_notices', 'disqus_link');
		function disqus_link(){
			echo "<div class='nebula_admin_notice notice notice-info'><p>You are using the Disqus commenting system. <a href='https://" . get_option('nebula_disqus_shortname') . ".disqus.com/admin/moderate' target='_blank'>View the comment listings on Disqus &raquo;</a></p></div>";
		}
	}
} else { //If WP core comments are enabled
	//Open comments on the front-end
	add_filter('comments_open', 'enable_comments_status', 20, 2);
	add_filter('pings_open', 'enable_comments_status', 20, 2);
	function enable_comments_status(){
		return true;
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
		echo '<a class="phg ' . $anim . ' ' . $white . '" href="http://www.pinckneyhugo.com/" target="_blank"><span class="pinckney">Pinckney</span><span class="hugo">Hugo</span><span class="group">Group</span></a>';
	}
}

//Show different meta data information about the post. Typically used inside the loop.
//Example: nebula_meta('by');
function nebula_meta($meta){
	$override = apply_filters('pre_nebula_meta', false, $meta, $secondary);
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
	if ( $icon ){
		$the_icon = '<i class="fa fa-calendar-o"></i> ';
	}

	$the_day = '';
	if ( $day ){ //If the day should be shown (otherwise, just month and year).
		$the_day = get_the_date('d') . '/';
	}

	if ( $linked ){
		return '<span class="posted-on">' . $the_icon . '<span class="meta-item entry-date">' . '<a href="' . home_url('/') . get_the_date('Y/m') . '/' . '">' . get_the_date('F') . '</a>' . ' ' . '<a href="' . home_url('/') . get_the_date('Y/m') . '/' . $the_day . '">' . get_the_date('j') . '</a>' . ', ' . '<a href="' . home_url('/') . get_the_date('Y') . '/' . '">' . get_the_date('Y') . '</a>' . '</span></span>';
	} else {
		return '<span class="posted-on">' . $the_icon . '<span class="meta-item entry-date">' . get_the_date('F j, Y') . '</span></span>';
	}
}

//Author post meta
function nebula_post_author($icon=true, $linked=true, $force=false){
	if ( $icon ){
		$the_icon = '<i class="fa fa-user"></i> ';
	}

	if ( nebula_option('nebula_author_bios', 'enabled') || $force ){
		if ( $linked && !$force ){
			return '<span class="posted-by">' . $the_icon . '<span class="meta-item entry-author">' . '<a href="' . get_author_posts_url(get_the_author_meta('ID')) . '">' . get_the_author() . '</a></span></span>';
		} else {
			return '<span class="posted-by">' . $the_icon . '<span class="meta-item entry-author">' . get_the_author() . '</span></span>';
		}
	}
}

//Post type meta
function nebula_post_type($icon=true, $linked=true){
	if ( $icon ){
		global $wp_post_types;
		$post_type = get_post_type();

		if ( $post_type == 'post' ){
			$post_icon_img = '<i class="fa fa-thumb-tack"></i>';
		} elseif ( $post_type == 'page' ){
			$post_icon_img = '<i class="fa fa-file-text"></i>';
		} else { //@TODO "Nebula" 0: Test that this works with CPTs:
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

	if ( $icon ){
		$the_icon = '<i class="fa ' . $comment_icon . '"></i> ';
	} else {
		$the_icon = '';
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

function nebula_facebook_share($counts=0){
	$override = apply_filters('pre_nebula_facebook_share', false, $counts);
	if ( $override !== false ){echo $override; return;}
?>
	<div class="nebula-social-button facebook-share">
		<div class="fb-share-button" data-href="<?php echo get_page_link(); ?>" data-layout="<?php echo ( $counts != 0 )? 'button_count' : 'button'; ?>"></div>
	</div>
<?php }


function nebula_facebook_like($counts=0){
	$override = apply_filters('pre_nebula_facebook_like', false, $counts);
	if ( $override !== false ){echo $override; return;}
?>
	<div class="nebula-social-button facebook-like">
		<div class="fb-like" data-href="<?php echo get_page_link(); ?>" data-layout="<?php echo ( $counts != 0 )? 'button_count' : 'button'; ?>" data-action="like" data-show-faces="false" data-share="false"></div>
	</div>
<?php }

function nebula_facebook_both($counts=0){
	$override = apply_filters('pre_nebula_facebook_both', false, $counts);
	if ( $override !== false ){echo $override; return;}
?>
	<div class="nebula-social-button facebook-both">
		<div class="fb-like" data-href="<?php echo get_page_link(); ?>" data-layout="<?php echo ( $counts != 0 )? 'button_count' : 'button'; ?>" data-action="like" data-show-faces="false" data-share="true"></div>
	</div>
<?php }

$nebula_twitter_tweet = 0;
function nebula_twitter_tweet($counts=0){
	$override = apply_filters('pre_nebula_twitter_tweet', false, $counts);
	if ( $override !== false ){echo $override; return;}
?>
	<div class="nebula-social-button twitter-tweet">
		<a href="https://twitter.com/share" class="twitter-share-button" <?php echo ( $counts != 0 )? '': 'data-count="none"'; ?>>Tweet</a>
		<?php if ( $nebula_twitter_tweet == 0 ) : ?>
			<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
		<?php endif; ?>
	</div>
<?php
	$nebula_twitter_tweet = 1;
}

$nebula_google_plus = 0;
function nebula_google_plus($counts=0){
	$override = apply_filters('pre_nebula_google_plus', false, $counts);
	if ( $override !== false ){echo $override; return;}
?>
	<div class="nebula-social-button google-plus-plus-one">
		<div class="g-plusone" data-size="medium" <?php echo ( $counts != 0 )? '' : 'data-annotation="none"'; ?>></div>
		<?php if ( $nebula_google_plus == 0 ) : ?>
			<script src="https://apis.google.com/js/platform.js" async defer></script>
		<?php endif; ?>
	</div>
<?php
	$nebula_google_plus = 1;
}

$nebula_linkedin_share = 0;
function nebula_linkedin_share($counts=0){ //@TODO "Nebula" 0: Bubble counts are not showing up...
	$override = apply_filters('pre_nebula_linkedin_share', false, $counts);
	if ( $override !== false ){echo $override; return;}
?>
	<div class="nebula-social-button linkedin-share">
		<?php if ( $nebula_linkedin_share == 0 ) : ?>
			<script src="//platform.linkedin.com/in.js" type="text/javascript"> lang: en_US</script>
		<?php endif; ?>
		<script type="IN/Share" <?php echo ( $counts != 0 )? 'data-counter="right"' : ''; ?>></script>
	</div>
<?php
	$nebula_linkedin_share = 1;
}

$nebula_pinterest_pin = 0;
function nebula_pinterest_pin($counts=0){ //@TODO "Nebula" 0: Bubble counts are not showing up...
	$override = apply_filters('pre_nebula_pinterest_pin', false, $counts);
	if ( $override !== false ){echo $override; return;}

	if ( has_post_thumbnail() ){
		$featured_image = get_the_post_thumbnail();
	} else {
		$featured_image = get_template_directory_uri() . '/images/meta/og-thumb.png'; //@TODO "Nebula" 0: This should probably be a square? Check the recommended dimensions.
	}
?>
	<div class="nebula-social-button pinterest-pin">
		<a href="//www.pinterest.com/pin/create/button/?url=<?php echo get_page_link(); ?>&media=<?php echo $featured_image; ?>&description=<?php echo urlencode(get_the_title()); ?>" data-pin-do="buttonPin" data-pin-config="<?php echo ( $counts != 0 )? 'beside' : 'none'; ?>" data-pin-color="red">
			<img src="//assets.pinterest.com/images/pidgets/pinit_fg_en_rect_red_20.png" />
		</a>
		<?php if ( $nebula_pinterest_pin == 0 ) : ?>
			<script type="text/javascript" async defer src="//assets.pinterest.com/js/pinit.js"></script>
		<?php endif; ?>
	</div>
<?php
	$nebula_pinterest_pin = 1;
}

//Twitter cached feed
//This function can be called with AJAX or as a standard function.
add_action('wp_ajax_nebula_twitter_cache', 'nebula_twitter_cache');
add_action('wp_ajax_nopriv_nebula_twitter_cache', 'nebula_twitter_cache');
function nebula_twitter_cache($username='Great_Blakes', $listname=null, $number_tweets=5, $include_retweets=1){
	if ( $_POST['data'] ){
		if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce')){ die('Permission Denied.'); }
		$username = ( $_POST['data']['username'] )? $_POST['data']['username'] : 'Great_Blakes';
		$listname = ( $_POST['data']['listname'] )? $_POST['data']['listname'] : null; //Only used for list feeds
		$number_tweets = ( $_POST['data']['numbertweets'] )? $_POST['data']['numbertweets'] : 5;
		$include_retweets = ( $_POST['data']['includeretweets'] )? $_POST['data']['includeretweets'] : 1; //1: Yes, 0: No
	}

	error_reporting(0); //Prevent PHP errors from being cached.

	if ( $listname ){
		$feed = 'https://api.twitter.com/1.1/lists/statuses.json?slug=' . $listname . '&owner_screen_name=' . $username . '&count=' . $number_tweets . '&include_rts=' . $include_retweets;
	} else {
		$feed = 'https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=' . $username . '&count=' . $number_tweets . '&include_rts=' . $include_retweets;
	}

	$bearer = get_option('nebula_twitter_bearer_token', '');

	$tweets = get_transient('nebula_twitter_' . $username);
	if ( empty($tweets) || is_debug() ){
		$args = array('headers' => array('Authorization' => 'Bearer ' . $bearer));
		$response = wp_remote_get($feed, $args);
		$tweets = $response['body'];

		if ( !$tweets ){
			echo false;
			exit;
		}

		set_transient('nebula_twitter_' . $username, $tweets, 60*5); //5 minute expiration
	}

	error_reporting(1); //Re-enable PHP error reporting

	if ( $_POST['data'] ){
		echo $tweets;
		exit;
	} else {
		return $tweets;
	}
}

//Use this instead of the_excerpt(); and get_the_excerpt(); so we can have better control over the excerpt.
//Several ways to implement this:
	//Inside the loop (or outside the loop for current post/page): nebula_the_excerpt('Read more &raquo;', 20, 1);
	//Outside the loop: nebula_the_excerpt(572, 'Read more &raquo;', 20, 1);
function nebula_the_excerpt($postID=0, $more=0, $length=55, $hellip=0){
	$override = apply_filters('pre_nebula_the_excerpt', false, $postID, $more, $length, $hellip);
	if ( $override !== false ){return $override;}

	if ( $postID && is_int($postID) ){
		$the_post = get_post($postID);
	} else {
		if ( $postID != 0 || is_string($postID) ){
			if ( $length == 0 || $length == 1 ){
				$hellip = $length;
			} else {
				$hellip = false;
			}

			if ( is_int($more) ){
				$length = $more;
			} else {
				$length = 55;
			}

			$more = $postID;
		}
		$postID = get_the_ID();
		$the_post = get_post($postID);
	}

	if ( $the_post->post_excerpt ){
		$string = strip_tags(strip_shortcodes($the_post->post_excerpt), '');
	} else {
		$string = strip_tags(strip_shortcodes($the_post->post_content), '');
	}

	if ( $length == -1 || $length == '' || $length === null ){
        $string = string_limit_words($string, strlen($string));
    } else {
        $string = string_limit_words($string, $length);
    }

	if ( $hellip ){
		if ( $string[1] == 1 ){
			$string[0] .= '&hellip; ';
		}
	}

	if ( isset($more) && $more != '' ){
		$string[0] .= ' <a class="nebula_the_excerpt" href="' . get_permalink($postID) . '">' . $more . '</a>';
	}

	return $string[0];
}

//Pass custom text to a Nebula-style excerpt
function nebula_custom_excerpt($text=false, $length=55, $hellip=false, $link=false, $more=false){
	$override = apply_filters('pre_nebula_custom_excerpt', false, $text, $length, $hellip, $link, $more);
	if ( $override !== false ){return $override;}

	$string = strip_tags(strip_shortcodes($text), '');

	if ( $length == -1 || $length == '' || $length == 'all' || $length === null ){
        $string = string_limit_words($string, strlen($string));
    } else {
        $string = string_limit_words($string, $length);
    }

	if ( $hellip ){
		if ( $string[1] == 1 ){
			$string[0] .= '&hellip; ';
		}
	}

	if ( isset($link) && isset($more) && $more != '' ){
		$string[0] .= ' <a class="nebula_custom_excerpt" href="' . $link . '">' . $more . '</a>';
	}

	return $string[0];
}

//Adds links to the WP admin and to edit the current post as well as shows when the post was edited last and by which author
//Important! This function should be inside of a "if ( current_user_can('manage_options') )" condition so this information isn't shown to the public!
function nebula_manage($data){
	$override = apply_filters('pre_nebula_manage', false, $data);
	if ( $override !== false ){echo $override; return;}

	if ( $data == 'edit' || $data == 'admin' ){
		echo '<span class="nebula-manage-edit"><span class="post-admin"><i class="fa fa-wrench"></i> <a href="' . get_admin_url() . '" target="_blank">Admin</a></span> <span class="post-edit"><i class="fa fa-pencil"></i> <a href="' . get_edit_post_link() . '">Edit</a></span></span>';
	} elseif ( $data == 'modified' || $data == 'mod' ){
		$manage_author = ( get_the_modified_author() )? get_the_modified_author() : get_the_author();
		echo '<span class="post-modified">Last Modified: <strong>' . get_the_modified_date() . '</strong> by <strong>' . $manage_author . '</strong></span>';
	} elseif ( $data == 'info' ){
		if ( wp_attachment_is_image() ){
			$metadata = wp_get_attachment_metadata();
			echo ''; //@TODO "Nebula" 0: In progress
		}
	}
}

//Speech recognition AJAX for navigating
add_action('wp_ajax_navigator', 'nebula_ajax_navigator');
add_action('wp_ajax_nopriv_navigator', 'nebula_ajax_navigator');
function nebula_ajax_navigator(){
	if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce')){ die('Permission Denied.'); }
	include(get_template_directory() . '/includes/navigator.php');
	exit();
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
function the_breadcrumb(){
	$override = apply_filters('pre_the_breadcrumb', false);
	if ( $override !== false ){echo $override; return;}

	global $post;
	$delimiter = '<span class="arrow">&rsaquo;</span>'; //Delimiter between crumbs
	$home = '<i class="fa fa-home"></i>'; //Text for the 'Home' link
	$showCurrent = 1; //1: Show current post/page title in breadcrumbs, 0: Don't show
	$before = '<span class="current">'; //Tag before the current crumb
	$after = '</span>'; //Tag after the current crumb
	$dontCapThese = array('the', 'and', 'but', 'of', 'a', 'and', 'or', 'for', 'nor', 'on', 'at', 'to', 'from', 'by', 'in');
	$homeLink = home_url('/');

	if ( $GLOBALS['http'] && is_int($GLOBALS['http']) ){
		echo '<div class="breadcrumbcon"><nav class="breadcrumbs"><a href="' . $homeLink . '">' . $home . '</a> ' . $delimiter . ' ' . $before . 'Error ' . $GLOBALS['http'] . $after;
	} elseif ( is_home() || is_front_page() ){
		echo '<div class="breadcrumbcon"><nav class="breadcrumbs"><a href="' . $homeLink . '">' . $home . '</a></nav></div>';
		return false;
	} else {
		echo '<div class="breadcrumbcon"><nav class="breadcrumbs"><a href="' . $homeLink . '">' . $home . '</a> ' . $delimiter . ' ';
		if ( is_category() ){
			$thisCat = get_category(get_query_var('cat'), false);
			if ( $thisCat->parent != 0 ){
				echo get_category_parents($thisCat->parent, TRUE, ' ' . $delimiter . ' ');
			}
			echo $before . 'Category: ' . single_cat_title('', false) . $after;
		} elseif ( is_search() ){
			echo $before . 'Search results' . $after;
		} elseif ( is_day() ){
			echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
			echo '<a href="' . get_month_link(get_the_time('Y'),get_the_time('m')) . '">' . get_the_time('F') . '</a> ' . $delimiter . ' ';
			echo $before . get_the_time('d') . $after;
		} elseif ( is_month() ){
			echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
			echo $before . get_the_time('F') . $after;
		} elseif ( is_year() ){
			echo $before . get_the_time('Y') . $after;
		} elseif ( is_single() && !is_attachment() ){
			if ( get_post_type() != 'post' ){
				$post_type = get_post_type_object(get_post_type());
				$slug = $post_type->rewrite;
				echo '<a href="' . $homeLink . $slug['slug'] . '/">' . $post_type->labels->singular_name . '</a>';
				if ( $showCurrent == 1 ){
					echo ' ' . $delimiter . ' ' . $before . get_the_title() . $after;
				}
			} else {
				$cat = get_the_category();
				if ( !empty($cat) ){
					$cat = $cat[0];
					$cats = get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
					if ( $showCurrent == 0 ){
						$cats = preg_replace("#^(.+)\s$delimiter\s$#", "$1", $cats);
					}
					echo $cats;
					if ( $showCurrent == 1 ){
						echo $before . get_the_title() . $after;
					}
				}
			}
		} elseif ( !is_single() && !is_page() && get_post_type() != 'post' && !is_404() ){
			if ( is_archive() ){ //@TODO "Nebula" 0: Might not be perfect... This may never else out.
				$userdata = get_user_by('slug', get_query_var('author_name'));
				echo $before . $userdata->first_name . ' ' . $userdata->last_name . $after;
			} else { //What does this one do?
				$post_type = get_post_type_object(get_post_type());
				echo $before . $post_type->labels->singular_name . $after;
			}
		} elseif ( is_attachment() ){ //@TODO "Nebula" 0: Check for gallery pages? If so, it should be Home > Parent(s) > Gallery > Attachment
			if ( !empty($post->post_parent) ){ //@TODO "Nebula" 0: What happens if the page parent is a child of another page?
				echo '<a href="' . get_permalink($post->post_parent) . '">' . get_the_title($post->post_parent) . '</a>' . ' ' . $delimiter . ' ' . get_the_title();
			} else {
				echo get_the_title();
			}
		} elseif ( is_page() && !$post->post_parent ){
			if ( $showCurrent == 1 ){
				echo $before . get_the_title() . $after;
			}
		} elseif ( is_page() && $post->post_parent ){
			$parent_id = $post->post_parent;
			$breadcrumbs = array();
			while ( $parent_id ){
				$page = get_page($parent_id);
				$breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
				$parent_id  = $page->post_parent;
			}
			$breadcrumbs = array_reverse($breadcrumbs);
			for ( $i = 0; $i < count($breadcrumbs); $i++ ){
				echo $breadcrumbs[$i];
				if ( $i != count($breadcrumbs)-1 ){
					echo ' ' . $delimiter . ' ';
				}
			}
			if ( $showCurrent == 1 ){
				echo ' ' . $delimiter . ' ' . $before . get_the_title() . $after;
			}
		} elseif ( is_tag() ){
			echo $before . 'Tag: ' . single_tag_title('', false) . $after;
		} elseif ( is_author() ){
			global $author;
			$userdata = get_userdata($author);
			echo $before . $userdata->display_name . $after;
		} elseif ( is_404() ){
			echo $before . 'Error 404' . $after;
		}

		if ( get_query_var('paged') ){
			if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ){
				echo ' (';
			}
			echo 'Page ' . get_query_var('paged');
			if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ){
				echo ')';
			}
		}
		echo '</nav></div><!--/breadcrumbcon-->';
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

//Override the default Wordpress search form
//@TODO "Nebula" 0: Use this on templates like 404 (and maybe even advanced search?) and search redirect (in header). Then expand this a little bit.
add_filter('get_search_form', 'nebula_search_form');
function nebula_search_form($form){
    $form = '<form role="search" method="get" id="searchform" action="' . home_url('/') . '" >
	    <div>
		    <input type="text" value="' . get_search_query() . '" name="s" id="s" />
		    <input type="submit" id="searchsubmit" class="wp_search_submit" value="'. esc_attr__('Search') .'" />
	    </div>
    </form>';
    return $form;
}

//Prevent empty search query error (Show all results instead)
add_action('pre_get_posts', 'redirect_empty_search');
function redirect_empty_search($query){
	global $wp_query;
	if ( isset($_GET['s']) && $wp_query->query && !array_key_exists('invalid', $_GET) ){
		if ( $_GET['s'] == '' && $wp_query->query['s'] == '' && !is_admin() ){
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

//Easily create markup for a Hero area search input
function nebula_hero_search($placeholder='What are you looking for?'){
	$override = apply_filters('pre_nebula_hero_search', false, $placeholder);
	if ( $override !== false ){echo $override; return;}

	echo '<div id="nebula-hero-formcon">
		<form id="nebula-hero-search" class="nebula-search-iconable search" method="get" action="' . home_url('/') . '">
			<input type="search" class="nebula-search open input search nofade" name="s" placeholder="' . $placeholder . '" autocomplete="off" x-webkit-speech />
		</form>
	</div>';
}

//Autocomplete Search AJAX.
add_action('wp_ajax_nebula_autocomplete_search', 'nebula_autocomplete_search');
add_action('wp_ajax_nopriv_nebula_autocomplete_search', 'nebula_autocomplete_search');
function nebula_autocomplete_search(){
	if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce')){ die('Permission Denied.'); }

	ini_set('memory_limit', '256M');
	$_POST['data']['term'] = trim($_POST['data']['term']);
	if ( empty($_POST['data']['term']) ){
		return false;
		exit;
	}

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
	$q1 = new WP_Query(array(
	    'post_type' => array('any'),
		'post_status' => 'publish',
		'posts_per_page' => 4,
		's' => $_POST['data']['term'],
	));

	//Search custom fields
	$q2 = new WP_Query(array(
	    'post_type' => array('any'),
		'post_status' => 'publish',
		'posts_per_page' => 4,
		'meta_query' => array(
			array(
				'value' => $_POST['data']['term'],
				'compare' => 'LIKE'
			)
		)
	));

	//Combine the above queries
	$autocomplete_query = new WP_Query();
	$autocomplete_query->posts = array_unique(array_merge($q1->posts, $q2->posts), SORT_REGULAR);
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
			similar_text(strtolower($_POST['data']['term']), strtolower(get_the_title()), $suggestion['similarity']); //Determine how similar the query is to this post title
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
	$attachments = get_posts(array('post_type' => 'attachment', 's' => $_POST['data']['term'], 'numberposts' => 10, 'post_status' => null));
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
			similar_text(strtolower($_POST['data']['term']), strtolower($attachment_search_meta), $suggestion['similarity']);
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
		set_transient('nebula_autocomplete_menus', $menus, 60*60); //1 hour cache
	}
	foreach($menus as $menu){
		$menu_items = wp_get_nav_menu_items($menu->term_id);
		foreach ( $menu_items as $key => $menu_item ){
		    $suggestion = array();
		    similar_text(strtolower($_POST['data']['term']), strtolower($menu_item->title), $menu_title_similarity);
		    similar_text(strtolower($_POST['data']['term']), strtolower($menu_item->attr_title), $menu_attr_similarity);
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
		set_transient('nebula_autocomplete_categories', $categories, 60*60); //1 hour cache
	}
	foreach ( $categories as $category ){
		$suggestion = array();
		$cat_count = 0;
		similar_text(strtolower($_POST['data']['term']), strtolower($category->name), $suggestion['similarity']);
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
		set_transient('nebula_autocomplete_tags', $tags, 60*60); //1 hour cache
	}
	foreach ( $tags as $tag ){
		$suggestion = array();
		$tag_count = 0;
		similar_text(strtolower($_POST['data']['term']), strtolower($tag->name), $suggestion['similarity']);
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
	if ( nebula_option('nebula_author_bios', 'enabled') ){
		$authors = get_transient('nebula_autocomplete_authors');
		if ( empty($authors) || is_debug() ){
			$authors = get_users(array('role' => 'author')); //@TODO "Nebula" 0: This should get users who have made at least one post. Maybe get all roles (except subscribers) then if postcount >= 1?
			set_transient('nebula_autocomplete_authors', $authors, 60*60); //1 hour cache
		}
		foreach ( $authors as $author ){
			$author_name = ( $author->first_name != '' )? $author->first_name . ' ' . $author->last_name : $author->display_name; //might need adjusting here
			if ( strtolower($author_name) == strtolower($_POST['data']['term']) ){ //todo: if similarity of author name and query term is higher than X. Return only 1 or 2.
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
	$suggestion['label'] = ( sizeof($suggestions) >= 1 )? '...more results for "' . $_POST['data']['term'] . '"' : 'Press enter to search for "' . $_POST['data']['term'] . '"';
	$suggestion['link'] = home_url('/') . '?s=' . str_replace(' ', '%20', $_POST['data']['term']);
	$suggestion['classes'] = ( sizeof($suggestions) >= 1 )? 'more-results search-link' : 'no-results search-link';
	$outputArray[] = $suggestion;

	echo json_encode($outputArray, JSON_PRETTY_PRINT);
	exit;
}

//Advanced Search
add_action('wp_ajax_nebula_advanced_search', 'nebula_advanced_search');
add_action('wp_ajax_nopriv_nebula_advanced_search', 'nebula_advanced_search');
function nebula_advanced_search(){
	if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce')){ die('Permission Denied.'); }

	ini_set('memory_limit', '512M'); //Increase memory limit for this script.

	$everything_query = get_transient('nebula_everything_query');
	if ( empty($venue_query) ){
		$everything_query = new WP_Query(array(
			'post_type' => array('any'),
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'nopaging' => true
		));
		set_transient('nebula_everything_query', $everything_query, 60*60); //1 hour cache
	}
	$posts = $everything_query->get_posts();

	foreach ( $posts as $post ){
		$author = null;
		if ( nebula_option('nebula_author_bios', 'enabled') ){ //&& $post->post_type != 'page' ?
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
			'description' => strip_tags($post->post_content), //@todo: not correct!
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
	exit;
}

//Infinite Load
function nebula_infinite_load_query($args=array('showposts' => 4), $loop=false){
	$override = apply_filters('pre_nebula_infinite_load_query', false);
	if ( $override !== false ){return;}

	if ( empty($args['paged']) ){
		$args['paged'] = 1;
	}

	query_posts($args); //@TODO "Nebula" 0: Change to WP_Query? How do we still use loop.php? Need to modify global loop ($wp_query).
	//Maybe: $GLOBALS['wp_query'] = new WP_Query($args); (untested)?
	global $wp_query;

	if ( empty($args['post_type']) ){
		$post_type_label = 'posts';
	} else {
		$post_type_obj = get_post_type_object($args['post_type']);
		$post_type_label = lcfirst($post_type_obj->label);
	}
	?>

	<div id="infinite-posts-list" data-max-pages="<?php echo $wp_query->max_num_pages; ?>" data-max-posts="<?php echo $wp_query->found_posts; ?>">
	    <?php
		    if ( !$loop ){
	    		get_template_part('loop');
			} else {
	    		call_user_func($loop);
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
							jQuery("#infinite-posts-list").append('<div class="clearfix infinite-page infinite-page-' + (pageNumber-1) + '" style="display: none;">' + response + '</div>');
							jQuery('.infinite-page-' + (pageNumber-1)).slideDown({
			                    duration: 750,
			                    easing: 'easeInOutQuad',
			                    complete: function(){
				                    jQuery('.loadmorecon').removeClass('loading');
			                    }
			                });

							if ( pageNumber >= maxPages ){
								jQuery('.loadmorecon').addClass('disabled').find('a').text('No more <?php echo $post_type_label; ?>.');
							}

							//history.replaceState(null, document.title, nebula.post.permalink + 'page/' + (pageNumber-1)); //@TODO "Nebula" 0: Needs to preserve query strings!
							jQuery(document).trigger('nebula_infinite_finish');
							ga('set', gaCustomDimensions['timestamp'], localTimestamp());
							ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Infinite Load'));
						},
						error: function(MLHttpRequest, textStatus, errorThrown){
							jQuery(document).trigger('nebula_infinite_finish');
							ga('set', gaCustomDimensions['timestamp'], localTimestamp());
							ga('set', gaCustomDimensions['sessionNotes'], sessionNote('Infinite Load AJAX Error'));
							ga('send', 'event', 'Error', 'AJAX Error', 'Infinite Load AJAX');
						},
						timeout: 60000
					});
			        pageNumber++;
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
	if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce')){ die('Permission Denied.'); }
	$page_number = $_POST['page'];
	$args = $_POST['args'];
	$args['paged'] = $page_number;
	$loop = $_POST['loop'];

	query_posts($args);

	if ( $loop == 'false' ){
    	get_template_part('loop');
    } else {
    	call_user_func($loop); //Custom loop callback function must be defined in a functions file (not a template file) for this to work.
    }

    exit;
}

//Remove capital P core function
remove_filter('the_title', 'capital_P_dangit', 11);
remove_filter('the_content', 'capital_P_dangit', 11);
remove_filter('comment_text', 'capital_P_dangit', 31);

//Add default posts and comments RSS feed links to head
add_theme_support('automatic-feed-links');

//Remove extraneous <head> from Wordpress
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'index_rel_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'feed_links_extra', 3);
remove_action('wp_head', 'start_post_rel_link', 10, 0);
remove_action('wp_head', 'parent_post_rel_link', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);
remove_action('wp_head', 'feed_links', 2);
header(base64_decode('RGV2ZWxvcGVkLXdpdGgtTmVidWxhOiBodHRwOi8vZ2VhcnNpZGUuY29tL25lYnVsYQ' . '=='));

//Add the Posts RSS Feed back in
add_action('wp_head', 'addBackPostFeed');
function addBackPostFeed(){
    echo '<link rel="alternate" type="application/rss+xml" title="RSS 2.0 Feed" href="' . get_bloginfo('rss2_url') . '" />';
}

//Declare support for WooCommerce
if ( is_plugin_active('woocommerce/woocommerce.php') ){
	add_theme_support('woocommerce');
	//Remove existing WooCommerce hooks to be replaced with our own
	remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
	remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
	//Replace WooCommerce hooks at our own declared locations
	add_action('woocommerce_before_main_content', 'custom_woocommerce_start', 10);
	add_action('woocommerce_after_main_content', 'custom_woocommerce_end', 10);
	function custom_woocommerce_start(){
		echo '<section id="WooCommerce">';
	}
	function custom_woocommerce_end(){
		echo '</section>';
	}
}

//Add custom body classes
add_filter('body_class', 'nebula_body_classes');
function nebula_body_classes($classes){
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

	//IE versions outside conditional comments
	if ( nebula_is_browser('ie', '10') ){
		$classes[] = 'lte-ie10';
	} elseif ( nebula_is_browser('ie', '11') ){
		$classes[] = 'lte-ie11';
	}

	//User Information
	$current_user = wp_get_current_user();
	if ( is_user_logged_in() ){
		$classes[] = 'user-logged-in';
		$classes[] = 'user-' . $current_user->user_login;
		$user_info = get_userdata(get_current_user_id());
		$classes[] = 'user-role-' . $user_info->roles[0];
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

	$classes[] = 'lang-' . get_bloginfo('language');
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

	if ( get_option('nebula_latitude') && get_option('nebula_longitude') ){
		$lat = get_option('nebula_latitude');
		$lng = get_option('nebula_longitude');
		$gmt = intval(get_option('gmt_offset'));
		$zenith = 90+50/60; //Civil twilight = 96°, Nautical twilight = 102°, Astronomical twilight = 108°
		global $sunrise, $sunset;
		$sunrise = strtotime(date_sunrise(strtotime('today'), SUNFUNCS_RET_STRING, $lat, $lng, $zenith, $gmt));
		$sunset = strtotime(date_sunset(strtotime('today'), SUNFUNCS_RET_STRING, $lat, $lng, $zenith, $gmt));

		if ( time() >= $sunrise && time() <= $sunset ){
			$classes[] = 'time-daylight';
			$classes[] = ( strtotime('now') < $sunrise+(($sunset-$sunrise)/2) )? 'time-light-wax' : 'time-light-wane'; //Before or after solar noon
		} else {
			$classes[] = 'time-darkness';
			$previous_sunset_modifier = ( date('H') < 12 )? 86400 : 0;
			$wane_time = (($sunset-$previous_sunset_modifier)+((86400-($sunset-$sunrise))/2)); //if it is after midnight, then we need to get the previous sunset (not the next- else we're always before tomorrow's wane time)
			$classes[] = ( strtotime('now') < $wane_time )? 'time-dark-wax' : 'time-dark-wane'; //Before or after solar midnight
		}

		$sunrise_sunset_length = 45; //Length of sunrise/sunset in minutes. Default: 45
		if ( strtotime('now') >= $sunrise-60*$sunrise_sunset_length && strtotime('now') <= $sunrise+60*$sunrise_sunset_length ){ //X minutes before and after true sunrise
			$classes[] = 'time-sunrise';
		}
		if ( strtotime('now') >= $sunset-60*$sunrise_sunset_length && strtotime('now') <= $sunset+60*$sunrise_sunset_length ){ //X minutes before and after true sunset
			$classes[] = 'time-sunset';
		}
	}

	$classes[] = 'date-day-' . strtolower(date('l'));
	$classes[] = 'date-ymd-' . strtolower(date('Y-m-d'));
	$classes[] = 'date-month-' . strtolower(date('F'));

	if ( $GLOBALS['http'] && is_int($GLOBALS['http']) ){
		$classes[] = 'error' . $GLOBALS['http'];
	}

    return $classes;
}

//Add additional classes to post wrappers
add_filter('post_class', 'nebula_post_classes');
function nebula_post_classes($classes){
    global $wp_query;
    if ( $wp_query->current_post == 0 ){ //If first post in a query
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

	foreach ( get_the_category($post->ID) as $category ){
		$classes[] = 'cat-id-' . $category->cat_ID;
	}

	$classes[] = 'author-id-' . get_the_author_id();

    return $classes;
}

//Make sure attachment URLs match the protocol (to prevent mixed content warnings).
add_filter('wp_get_attachment_url', 'wp_get_attachment_url_example');
function wp_get_attachment_url_example($url){
    $http = site_url(false, 'http');
    $https = site_url(false, 'https');

    if ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ){
        return str_replace($http, $https, $url);
    } else {
        return $url;
    }
}

//Add more fields to attachments //@TODO "Nebula" 0: Enable this as needed. The below example adds a "License" field.
//add_filter('attachment_fields_to_edit', 'nebula_attachment_fields', 10, 2);
function nebula_attachment_fields($form_fields, $post){
    $field_value = get_post_meta($post->ID, 'license', true);
    $form_fields['license'] = array(
        'value' => $field_value ? $field_value : '',
        'label' => 'License',
        'helps' => 'Specify the license type used for this image'
    );
    return $form_fields;
}
//add_action('edit_attachment', 'nebula_save_attachment_fields');
function nebula_save_attachment_fields($attachment_id){
    $license = $_REQUEST['attachments'][$attachment_id]['license'];
    if ( isset($license) ){
        update_post_meta($attachment_id, 'license', $license);
    }
}

//Check if the passed time is within business hours.
function has_business_hours(){
	foreach ( array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday') as $weekday ){
		if ( nebula_option('nebula_business_hours_' . $weekday . '_enabled') || nebula_option('nebula_business_hours_' . $weekday . '_open') || nebula_option('nebula_business_hours_' . $weekday . '_close') ){
			return true;
		}
	}
	return false;
}

function is_business_open($date=null, $general=0){ return business_open($date, $general); }
function is_business_closed($date=null, $general=0){ return !business_open($date, $general); }
function business_open($date=null, $general=0){
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
		$businessHours[$weekday] = array(
			'enabled' => get_option('nebula_business_hours_' . $weekday . '_enabled'),
			'open' => get_option('nebula_business_hours_' . $weekday . '_open'),
			'close' => get_option('nebula_business_hours_' . $weekday . '_close')
		);
	}

	$days_off = array_filter(explode(', ', get_option('nebula_business_hours_closed')));
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
		if ( $general == 1 ){
			return true;
		}

		$openToday = date('Gi', strtotime($businessHours[$today]['open']));
		$closeToday = date('Gi', strtotime($businessHours[$today]['close']));
		if ( date('Gi', $date) >= $openToday && date('Gi', $date) <= $closeToday ){
			return true;
		}
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

//Detect weather for Zip Code (using Yahoo! Weather)
function nebula_weather($zipcode=null, $data=''){
	$override = apply_filters('pre_nebula_weather', false, $zipcode, $data);
	if ( $override !== false ){return $override;}

	if ( !empty($zipcode) && is_string($zipcode) && !ctype_digit($zipcode) ){ //ctype_alpha($zipcode)
		$data = $zipcode;
		$zipcode = get_option('nebula_postal_code', '13204');
	} elseif ( empty($zipcode) ){
		$zipcode = get_option('nebula_postal_code', '13204');
	}

	$weather_json = get_transient('nebula_weather_' . $zipcode);
	if ( empty($weather_json) ){ //No ?debug option here (because multiple calls are made to this function). Clear with a force true when needed.
		$yql_query = 'select * from weather.forecast where woeid in (select woeid from geo.places(1) where text=' . $zipcode . ')';

		WP_Filesystem();
		global $wp_filesystem;
		$weather_json = $wp_filesystem->get_contents('http://query.yahooapis.com/v1/public/yql?q=' . urlencode($yql_query) . '&format=json');

		set_transient('nebula_weather_' . $zipcode, $weather_json, 60*5); //5 minute expiration
	}
	$weather_json = json_decode($weather_json);

	if ( !$weather_json || empty($weather_json) ){
		trigger_error('A weather error occurred (Forecast for ' . $zipcode . ' may not exist).', E_USER_WARNING);
		return false;
	} elseif ( $data == '' ){
		return true;
	}

	switch ( $data ){
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
		case 'wind chill':
		case 'chill':
			return $weather_json->query->results->channel->wind->chill;
			break;
		case 'windspeed':
		case 'wind speed':
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
	return false;
}

function vimeo_meta($videoID, $meta=''){
	$override = apply_filters('pre_vimeo_meta', false, $videoID, $meta);
	if ( $override !== false ){return $override;}

	if ( $meta == 'id' ){
		return $videoID;
	}

	$vimeo_json = get_transient('nebula_vimeo_' . $videoID);
	if ( empty($vimeo_json) ){ //No ?debug option here (because multiple calls are made to this function). Clear with a force true when needed.
		WP_Filesystem();
		global $wp_filesystem;
		$vimeo_json = $wp_filesystem->get_contents('http://vimeo.com/api/v2/video/' . $videoID . '.json');

		set_transient('nebula_vimeo_' . $videoID, $vimeo_json, 60*60); //1 hour expiration
	}
	$vimeo_json = json_decode($vimeo_json);

	if ( !$vimeo_json ){
		trigger_error('A Vimeo API error occurred (A video with ID ' . $videoID . ' may not exist).', E_USER_WARNING);
		return false;
	} elseif ( empty($vimeo_json[0]) ){
		trigger_error('A Vimeo video with ID ' . $videoID . ' does not exist.', E_USER_WARNING);
		return false;
	} elseif ( $meta == '' ){
		return true;
	}

	switch ( $meta ){
		case 'json':
			return $vimeo_json[0];
			break;
		case 'title':
			return $vimeo_json[0]->title;
			break;
		case 'safetitle':
		case 'safe-title':
			return str_replace(array(" ", "'", '"'), array("-", "", ""), $vimeo_json[0]->title);
			break;
		case 'description':
		case 'content':
			return $vimeo_json[0]->description;
			break;
		case 'thumbnail':
			return $vimeo_json[0]->thumbnail_large;
			break;
		case 'author':
		case 'channeltitle':
		case 'channel':
		case 'user':
			return $vimeo_json[0]->user_name;
			break;
		case 'uploaded':
		case 'published':
		case 'date':
		case 'upload_date':
			return $vimeo_json[0]->upload_date;
			break;
		case 'href':
		case 'link':
		case 'url':
			return $vimeo_json[0]->url;
			break;
		case 'seconds':
		case 'duration':
		    $duration_seconds = strval($vimeo_json[0]->duration);
		    if ( $meta == 'seconds' ){
			    return $duration_seconds;
		    } else {
			    return intval(gmdate("i", $duration_seconds)) . gmdate(":s", $duration_seconds);
		    }
			break;
		default:
			break;
	}
	return false;
}

//Get Youtube Video metadata
function youtube_meta($videoID, $meta=''){
	$override = apply_filters('pre_youtube_meta', false, $videoID, $meta);
	if ( $override !== false ){return $override;}

	if ( !nebula_option('nebula_google_server_api_key') ){
		trigger_error('A Google server API key is required to use the youtube_meta function.', E_USER_WARNING);
		return false;
	}

	switch ( $meta ){
		case 'origin':
			return nebula_url_components('basedomain');
			break;
		case 'id':
			return $videoID;
			break;
		case 'href':
		case 'link':
		case 'url':
			return 'https://www.youtube.com/watch?v=' . $videoID;
			break;
		default:
			break;
	}

	$youtube_json = get_transient('nebula_youtube_' . $videoID);
	if ( empty($youtube_json) ){ //No ?debug option here (because multiple calls are made to this function). Clear with a force true when needed.
		if ( get_option('nebula_google_server_api_key') == '' ){
			if ( current_user_can('manage_options') || is_dev() ){
				trigger_error("A Google API Server Key is needed for Youtube Meta. Add one in Nebula Options (in the WordPress Admin).", E_USER_WARNING);
				return false;
			} else {
				trigger_error("Google API Server Key not found.", E_USER_WARNING);
				return false;
			}
		}

		WP_Filesystem();
		global $wp_filesystem;
		$youtube_json = $wp_filesystem->get_contents('https://www.googleapis.com/youtube/v3/videos?id=' . $videoID . '&part=snippet,contentDetails,statistics&key=' . get_option('nebula_google_server_api_key'));

		set_transient('nebula_youtube_' . $videoID, $youtube_json, 60*60); //1 hour expiration
	}
	$youtube_json = json_decode($youtube_json);

	if ( !$youtube_json ){
		trigger_error('A Youtube Data API error occurred.', E_USER_WARNING);
		return false;
	} elseif ( !empty($youtube_json->error) ){
		trigger_error('Youtube API Error: ' . $youtube_json->error->message, E_USER_WARNING);
		return false;
	} elseif ( empty($youtube_json->items) ){
		trigger_error('A Youtube video with ID ' . $videoID . ' does not exist.', E_USER_WARNING);
		return false;
	} elseif ( empty($meta) ){
		return true;
	}

	switch ( $meta ){
		case 'json':
			return $youtube_json->items[0];
			break;
		case 'title':
			return $youtube_json->items[0]->snippet->title;
			break;
		case 'safetitle':
		case 'safe-title':
			return str_replace(array(" ", "'", '"'), array("-", "", ""), $youtube_json->items[0]->snippet->title);
			break;
		case 'description':
		case 'content':
			return $youtube_json->items[0]->snippet->description;
			break;
		case 'thumbnail':
			return $youtube_json->items[0]->snippet->thumbnails->high->url;
			break;
		case 'author':
		case 'channeltitle':
		case 'channel':
		case 'user':
			return $youtube_json->items[0]->snippet->channelTitle;
			break;
		case 'uploaded':
		case 'published':
		case 'date':
		case 'upload_date':
			return $youtube_json->items[0]->snippet->publishedAt;
			break;
		case 'seconds':
		case 'duration':
			$start = new DateTime('@0'); //Unix epoch
		    $start->add(new DateInterval($youtube_json->items[0]->contentDetails->duration));
		    $duration_seconds = intval($start->format('H'))*60*60 + intval($start->format('i'))*60 + intval($start->format('s'));
		    if ( $meta == 'seconds' ){
			    return $duration_seconds;
		    } else {
			    return intval(gmdate("i", $duration_seconds)) . gmdate(":s", $duration_seconds);
		    }
			break;
		default:
			break;
	}
	return false;
}

//Create tel: link if on mobile, otherwise return unlinked, human-readable number
function nebula_tel_link($phone, $postd=''){
	$override = apply_filters('pre_nebula_tel_link', false, $phone, $postd);
	if ( $override !== false ){return $override;}

	if ( nebula_is_mobile() ){
		if ( $postd ){
			$search = array('#', 'p', 'w');
			$replace   = array('%23', ',', ';');
			$postd = str_replace($search, $replace, $postd);
			if ( strpos($postd, ',') === false || strpos($postd, ';') === false ){
				$postd = ',' . $postd;
			}
		}
		return '<a class="nebula-tel-link" href="tel:' . nebula_phone_format($phone, 'tel') . $postd . '">' . nebula_phone_format($phone, 'human') . '</a>';
	} else {
		return nebula_phone_format($phone, 'human');
	}
}

//Create sms: link if on mobile, otherwise return unlinked, human-readable number
function nebula_sms_link($phone, $message=''){
	$override = apply_filters('pre_nebula_sms_link', false, $phone, $message);
	if ( $override !== false ){return $override;}

	if ( nebula_is_mobile() ){
		$sep = ( nebula_is_os('ios') )? '?' : ';';
		//@TODO "Nebula" 0: Encode $message string here...?
		return '<a class="nebula-sms-link" href="sms:' . nebula_phone_format($phone, 'tel') . $sep . 'body=' . $message . '">' . nebula_phone_format($phone, 'human') . '</a>';
	} else {
		return nebula_phone_format($phone, 'human');
	}
}

//Convert phone numbers into ten digital dial-able or to human-readable
function nebula_phone_format($number, $format=''){
	$override = apply_filters('pre_nebula_phone_format', false, $number, $format);
	if ( $override !== false ){return $override;}

	if ( $format == 'human' && (strpos($number, ')') == 4 || strpos($number, ')') == 6) ){
		//Format is already human-readable
		return $number;
	} elseif ( $format == 'tel' && (strpos($number, '+1') == 0 && strlen($number) == 12) ){
		//Format is already dialable
		return $number;
	}

	if ( (strpos($number, '+1') == 0 && strlen($number) == 12) || (strpos($number, '1') == 0 && strlen($number) == 11) || strlen($number) == 10 && $format != 'tel' ){
		//Convert from dialable to human
		if ( strpos($number, '1') == 0 && strlen($number) == 11 ){
			//13154786700
			$number = '(' . substr($number, 1, 3) . ') ' . substr($number, 4, 3) . '-' . substr($number, 7);
		} elseif ( strlen($number) == 10 ){
			//3154786700
			$number = '(' . substr($number, 0, 3) . ') ' . substr($number, 3, 3) . '-' . substr($number, 6);
		} elseif ( strpos($number, '+1') == 0 && strlen($number) == 12 ){
			//+13154786700
			$number = '(' . substr($number, 2, 3) . ') ' . substr($number, 5, 3) . '-' . substr($number, 8);
		} else {
			return 'Error: Unknown format.';
		}
		//@TODO "Nebula" 0: Maybe any numbers after "," "p" ";" or "w" could be added to the human-readable in brackets, like: (315) 555-1346 [323]
		//To do the above, set a remainder variable from above and add it to the return (if it exists). Maybe even add them to a span with a class so they can be hidden if undesired?
		return $number;
	} else {
		if ( strlen($number) < 7 ){
			return 'Error: Too few digits.';
		} elseif ( strlen($number) < 10 ){
			return 'Error: Too few digits (area code is required).';
		}
		//Convert from human to dialable
		if ( strpos($number, '1') != '0' ){
			$number = '1 ' . $number;
		}

		if ( strpos($number,'x') !== false ){
			$postd = ';p' . substr($number, strpos($number, "x") + 1);
		} else {
			$postd = '';
		}
		$number = str_replace(array(' ', '-', '(', ')', '.', 'x'), '', $number);
		$number = substr($number, 0, 11);
		return '+' . $number . $postd;
	}
}

//Footer Widget Counter
function footerWidgetCounter(){
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

//Track PHP errors...
//Disabled for now. Will revisit when fatal errors can be caught/reported (PHP7?).
//register_shutdown_function('shutdownFunction');
function shutDownFunction(){
	$error = error_get_last(); //Will return an error number, or null on normal end of script (without any errors).
	if ( $error['type'] == 1 || $error['type'] == 16 || $error['type'] == 64 || $error['type'] == 4 || $error['type'] == 256 || $error['type'] == 4096 ){
		//ga_send_event('Error', 'PHP Error', 'Fatal Error [' . $error['type'] . ']: ' . $error['message'] . ' in ' . $error['file'] . ' on ' . $error['line'] . '.');
	}
}
//set_error_handler('nebula_error_handler');
function nebula_error_handler($error_level, $error_message, $error_file, $error_line, $error_contest){
    switch ( $error_level ){
        case E_WARNING:
        case E_CORE_WARNING:
        case E_COMPILE_WARNING:
        case E_USER_WARNING:
            //ga_send_event('Error', 'PHP Error', 'Warning [' . $error_level . ']: ' . $error_message . ' in ' . $error_file . ' on ' . $error_line . '.');
            break;
        case E_NOTICE:
        case E_USER_NOTICE:
        case E_DEPRECATED:
        case E_USER_DEPRECATED:
            //ga_send_event('Error', 'PHP Error', 'Notice ' . $error_level . ': ' . $error_message . ' in ' . $error_file . ' on ' . $error_line . '.'); //By default we do not track notices.
            break;
        case E_STRICT:
            //ga_send_event('Error', 'PHP Error', 'Strict ' . $error_level . ': ' . $error_message . ' in ' . $error_file . ' on ' . $error_line . '.'); //By default we do not track strict errors.
            break;
        default:
            //ga_send_event('Error', 'PHP Error', 'Unknown Error Level ' . $error_level . ': ' . $error_message . ' in ' . $error_file . ' on ' . $error_line . '.');
            break;
    }
    return false; //After reporting, 'false' allows the original error handler to print errors.
}