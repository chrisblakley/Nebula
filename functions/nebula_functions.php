<?php

//Set server timezone to match Wordpress
date_default_timezone_set(get_option('timezone_string')); //@TODO "Nebula" 0: date_default_timezone_set(): Timezone ID '' is invalid


//Track Google Page Speed tests
add_action('wp_footer', 'track_google_pagespeed_checks');
function track_google_pagespeed_checks(){
	if ( strpos($_SERVER['HTTP_USER_AGENT'], 'Google Page Speed') !== false ){
		$protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'https://' : 'http://';
		$currentURL = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		if ( strpos($currentURL, ".js") !== false ){
			exit();
		} else {
			global $post;
			$currentTitle = get_the_title($post->ID);
		}

		ga_send_event('Google Page Speed', $currentURL, $currentTitle);
	}
}


//Add the calling card to the browser console
if ( nebula_settings_conditional('nebula_console_css') ){
	add_action('wp_head', 'nebula_calling_card');
	function nebula_calling_card(){
		//@TODO "Nebula" 0: if chrome or firefox... (find what other browsers support this)
		$console_log = "<script>if ( document.getElementsByTagName('html')[0].className.indexOf('lte-ie8') < 0 ){";
		if ( !$GLOBALS["mobile_detect"]->isMobile() && !$GLOBALS["mobile_detect"]->isTablet() ){
			$console_log .= "console.log('%c', 'padding: 28px 119px; line-height: 35px; background: url(" . get_template_directory_uri() . "/images/phg/phg-logo.png) no-repeat; background-size: auto 60px;');";
		}
		$console_log .= "console.log('%c Created using Nebula ', 'padding: 2px 10px; background: #0098d7; color: #fff;');}</script>";

		echo $console_log;
	}
}


//Check for dev stylesheets
if ( nebula_settings_conditional('nebula_dev_stylesheets') ){
	if ( is_writable(get_template_directory() . '/css/dev.css') ){
		add_action('wp_enqueue_scripts', 'combine_dev_stylesheets');
	} else {
		//@TODO "Nebula" 0: Somehow need to notify that permission is denied to write files (thinking an HTML comment). Need to not do it before headers are sent, though.
	}
	function combine_dev_stylesheets(){
		$file_counter = 0;
		file_put_contents(get_template_directory() . '/css/dev.css', '/**** Warning: This is an automated file! Anything added to this file manually will be removed! ****/'); //Empty /css/dev.css
		foreach ( glob(get_template_directory() . '/css/dev/*.css') as $file ){
			$file_path_info = pathinfo($file);
			if ( is_file($file) && $file_path_info['extension'] == 'css' ){
				$file_counter++;
				$this_css_filename = basename($file);
				$this_css_contents = file_get_contents($file); //Copy file contents
				$empty_css = ( $this_css_contents == '' ) ? ' (empty)' : '';
				$dev_css_contents = file_get_contents(get_template_directory() . '/css/dev.css');
				$dev_css_contents .= "/* ==========================================================================\r\n   " . get_template_directory_uri() . "/css/dev/" . $this_css_filename . $empty_css . "\r\n   ========================================================================== */\r\n\r\n" . $this_css_contents . "\r\n\r\n/* End of " . $this_css_filename . " */\r\n\r\n\r\n";
				file_put_contents(get_template_directory() . '/css/dev.css', $dev_css_contents);
			}
		}

		if ( $file_counter > 0 ){
			wp_enqueue_style('nebula-dev_styles', get_template_directory_uri() . '/css/dev.css?c=' . $file_counter, array('nebula-main'), null);
		}
	}
}


//Redirect to favicon to force-clear the cached version when ?favicon is added.
add_action('wp_loaded', 'nebula_favicon_cache');
function nebula_favicon_cache(){
	if ( array_key_exists('favicon', $_GET) ){
		header('Location: ' . get_template_directory_uri() . '/images/meta/favicon.ico');
	}
}

//Allow pages to have excerpts too
add_post_type_support('page', 'excerpt');


//Add Theme Support
if ( function_exists('add_theme_support') ){
	add_theme_support('post-thumbnails');
}

//Remove Theme Support
if ( function_exists('remove_theme_support') ){
	remove_theme_support('custom-background');
	remove_theme_support('custom-header');
}

//Add new image sizes
add_image_size('open_graph_large', 1200, 630, 1);
add_image_size('open_graph_small', 600, 315, 1);


//Dynamic Page Titles
add_filter('wp_title', 'filter_wp_title', 10, 2);
function filter_wp_title($title, $separator){
	if ( is_feed() ){
		return $title;
	}

	global $paged, $page;

	if ( is_search() ){
		$title = 'Search results';
		if ( $paged >= 2 ){
			$title .= ' ' . $separator . ' Page ' . $paged;
		}
		$title .= ' ' . $separator . ' ' . get_bloginfo('name', 'display');
		return $title;
	}

	$title .= get_bloginfo('name', 'display');

	$site_description = get_bloginfo('description', 'display');
	if ( $site_description && (is_home() || is_front_page()) ){
		$title .= ' ' . $separator . ' ' . $site_description;
	}

	if ( $paged >= 2 || $page >= 2 ){
		$title .= ' ' . $separator . ' Page ' . max($paged, $page);
	}

	return $title;
}


//Determine if the author should be the Company Name or the specific author's name.
function nebula_the_author($show_authors=1){
	if ( !is_single() || $show_authors == 0 || !nebula_author_bios_enabled() ){
		return nebula_settings_conditional_text('nebula_site_owner', get_bloginfo('name'));
	} else {
		return ( get_the_author_meta('first_name') != '' ) ? get_the_author_meta('first_name') . ' ' . get_the_author_meta('last_name') : get_the_author_meta('display_name');
	}
}


//List of HTTP status codes: http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
add_action('nebula_header', 'nebula_http_status');
function nebula_http_status($status=200, $redirect=0){
	if ( isset($_GET['http']) ){
		$status = $_GET['http'];
	}

	$GLOBALS['http'] = intval($status);

	if ( is_int($GLOBALS['http']) && $GLOBALS['http'] != 0 && $GLOBALS['http'] != 200 ){
		if ( $GLOBALS['http'] == '404' ){ //@TODO "Nebula" 0: Eventually consider removing the 404 page and using the http_status.php page.
			global $wp_query;
			$wp_query->set_404();
			status_header(404);
			if ( $redirect == 1 ){
				header('Location: '); //@TODO "Nebula" 0: Redirect to a generic error page w/ the error query.
			} else {
				get_template_part('404');
			}
			die();
		} else {
			status_header(403);
			if ( $redirect == 1 ){
				header('Location: '); //@TODO "Nebula" 0: Redirect to a generic error page w/ the error query.
			} else {
				get_template_part('http_status');
			}
			die();
		}
	}
}


add_action('widgets_init', 'nebula_widgets_init');
function nebula_widgets_init(){
	//Sidebar 1
	register_sidebar(array(
		'name' => 'Primary Widget Area',
		'id' => 'primary-widget-area',
		'description' => 'The primary widget area', 'boilerplate',
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	//Sidebar 2
	register_sidebar(array(
		'name' => 'Secondary Widget Area',
		'id' => 'secondary-widget-area',
		'description' => 'The secondary widget area',
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	//Footer 1
	register_sidebar(array(
		'name' => 'First Footer Widget Area',
		'id' => 'first-footer-widget-area',
		'description' => 'The first footer widget area',
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	//Footer 2
	register_sidebar(array(
		'name' => 'Second Footer Widget Area',
		'id' => 'second-footer-widget-area',
		'description' => 'The second footer widget area',
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	//Footer 3
	register_sidebar(array(
		'name' => 'Third Footer Widget Area',
		'id' => 'third-footer-widget-area',
		'description' => 'The third footer widget area',
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	//Footer 4
	register_sidebar(array(
		'name' => 'Fourth Footer Widget Area',
		'id' => 'fourth-footer-widget-area',
		'description' => 'The fourth footer widget area',
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );
}


//Register the Navigation Menus
add_action('after_setup_theme', 'nav_menu_locations');
function nav_menu_locations(){
	register_nav_menus( array(
		'secondary' => 'Secondary Menu',
		'primary' => 'Primary Menu',
		'mobile' => 'Mobile Menu',
		'sidebar' => 'Sidebar Menu',
		'footer' => 'Footer Menu'
		)
	);
}


//Set email content type to be HTML by default
add_filter('wp_mail_content_type', 'nebula_email_content_type');
function nebula_email_content_type(){
    return "text/html";
}


/*** If the project uses comments, remove the next set of functions (six), or force this conditional to be false! ***/
if ( nebula_settings_conditional('nebula_comments', 'disabled') ){

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

	//Remove comments links from admin bar
	add_action('wp_loaded', 'disable_comments_admin_bar');
	function disable_comments_admin_bar(){
		if (is_admin_bar_showing()){
			//global $wp_admin_bar; //@TODO "Nebula" 0: NULL
			//$wp_admin_bar->remove_menu('wp-logo');
			//$wp_admin_bar->remove_menu('comments');
			//remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 50); //@TODO "Nebula" 0: Not working
			add_filter('admin_head', 'admin_bar_hide_comments');
			function admin_bar_hide_comments(){
				echo '<style>#wp-admin-bar-comments {display: none;}</style>'; //Temporary fix until PHP removal is possible.
			}
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

	//Disable support for comments and trackbacks in post types, Redirect any user trying to access comments page
	add_action('admin_init', 'disable_comments_admin_menu_redirect');
	function disable_comments_admin_menu_redirect(){
		global $pagenow;
		if ($pagenow === 'edit-comments.php' || $pagenow === 'options-discussion.php'){
			wp_redirect(admin_url());
			exit;
		}

		$post_types = get_post_types();
		foreach ($post_types as $post_type){
			if(post_type_supports($post_type, 'comments')){
				remove_post_type_support($post_type, 'comments');
				remove_post_type_support($post_type, 'trackbacks');
			}
		}
	}
} else {
	//Open comments on the front-end
	add_filter('comments_open', 'enable_comments_status', 20, 2);
	add_filter('pings_open', 'enable_comments_status', 20, 2);
	function enable_comments_status(){
		return true;
	}

	$filename = basename($_SERVER['REQUEST_URI']);
	if ( $filename == 'edit-comments.php' ){
		add_action('admin_notices', 'disqus_link');
		function disqus_link(){
			if ( nebula_settings_conditional_text_bool('nebula_disqus_shortname') ){
				echo "<div class='nebula_admin_notice updated'><p>You are using the Disqus commenting system. <a href='https://" . nebula_settings_conditional_text('nebula_disqus_shortname', '') . ".disqus.com/admin/moderate' target='_blank'>View the comment listings on Disqus &raquo;</a></p></div>";
			} else {
				echo "<div class='nebula_admin_notice error'><p>You are using the Disqus commenting system, <strong>BUT</strong> you have not set your shortname in <a href='themes.php?page=nebula_settings'>Nebula Settings</a>, so we can't send you directly to your comment listing! <a href='https://disqus.com/admin/moderate' target='_blank'>Go to Disqus &raquo;</a></p></div>";
			}
		}
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









//Show different meta data information about the post. Typically used inside the loop.
//Example: nebula_meta('on', 0); //The 0 in the second parameter here makes the day link to the month archive.
//Example: nebula_meta('by');
function nebula_meta($meta, $secondary=1){
	if ( $meta == 'date' || $meta == 'time' || $meta == 'on' || $meta == 'day' || $meta == 'when' ){
		$the_day = '';
		if ( $secondary ){ //Secondary here is if the day should be shown
			$the_day = get_the_date('d') . '/';
		}
		echo '<span class="posted-on"><i class="fa fa-calendar"></i> <span class="entry-date">' . '<a href="' . home_url('/') . get_the_date('Y') . '/' . get_the_date('m') . '/' . '">' . get_the_date('F') . '</a>' . ' ' . '<a href="' . home_url() . '/' . get_the_date('Y') . '/' . get_the_date('m') . '/' . $the_day . '">' . get_the_date('j') . '</a>' . ', ' . '<a href="' . home_url() . '/' . get_the_date('Y') . '/' . '">' . get_the_date('Y') . '</a>' . '</span></span>';
	} elseif ( $meta == 'author' || $meta == 'by' ){
		if ( nebula_author_bios_enabled() ){
			echo '<span class="posted-by"><i class="fa fa-user"></i> <span class="entry-author">' . '<a href="' . get_author_posts_url( get_the_author_meta( 'ID' ) ) . '">' . get_the_author() . '</a></span></span>';
		}
	} elseif ( $meta == 'categories' || $meta == 'category' || $meta == 'cat' || $meta == 'cats' || $meta == 'in' ){
		if ( is_object_in_taxonomy(get_post_type(), 'category') ){
			$post_categories = '<span class="posted-in post-categories"><i class="fa fa-bookmark"></i> ' . get_the_category_list(', ') . '</span>';
		} else {
			$post_categories = '';
		}
		echo $post_categories;
	} elseif ( $meta == 'tags' || $meta == 'tag' ){
		$tag_list = get_the_tag_list('', ', ');
		if ( $tag_list ){
			$tag_icon = ( count(get_the_tags()) > 1 ) ? 'tags' : 'tag';
			$post_tags = '<span class="posted-in post-tags"><i class="fa fa-' . $tag_icon . '"></i> ' . $tag_list . '</span>';
		} else {
			$post_tags = '';
		}
		echo $post_tags;
	} elseif ( $meta == 'dimensions' || $meta == 'size' ){
		if ( wp_attachment_is_image() ){
			$metadata = wp_get_attachment_metadata();
			echo '<span class="meta-dimensions"><i class="fa fa-expand"></i> <a href="' . wp_get_attachment_url() . '" >' . $metadata['width'] . ' &times; ' . $metadata['height'] . '</a></span>';
		}
	} elseif ( $meta == 'exif' || $meta == 'camera' ){
		$imgmeta = wp_get_attachment_metadata();
	    if ( $imgmeta ){ //Check for Bad Data
	        if ( $imgmeta['image_meta']['focal_length'] == 0 || $imgmeta['image_meta']['aperture'] == 0 || $imgmeta['image_meta']['shutter_speed'] == 0 || $imgmeta['image_meta']['iso'] == 0 ){
	            $output = 'No valid EXIF data found';
	        } else { //Convert the shutter speed retrieve from database to fraction
	            if ( (1/$imgmeta['image_meta']['shutter_speed']) > 1 ){
	                if ( (number_format((1/$imgmeta['image_meta']['shutter_speed']), 1)) == 1.3 || number_format((1/$imgmeta['image_meta']['shutter_speed']), 1) == 1.5 || number_format((1/$imgmeta['image_meta']['shutter_speed']), 1) == 1.6 || number_format((1/$imgmeta['image_meta']['shutter_speed']), 1) == 2.5 ){
	                    $pshutter = "1/" . number_format((1/$imgmeta['image_meta']['shutter_speed']), 1, '.', '') . " second";
	                } else {
	                    $pshutter = "1/" . number_format((1/$imgmeta['image_meta']['shutter_speed']), 0, '.', '') . " second";
	                }
	            } else {
	                $pshutter = $imgmeta['image_meta']['shutter_speed'] . " seconds";
	            }

	            $output = '<time datetime="' . date('c', $imgmeta['image_meta']['created_timestamp']) . '"><span class="month">' . date('F', $imgmeta['image_meta']['created_timestamp']).'</span> <span class="day">'.date('j', $imgmeta['image_meta']['created_timestamp']) . '</span><span class="suffix">' . date('S', $imgmeta['image_meta']['created_timestamp']) . '</span> <span class="year">' . date('Y', $imgmeta['image_meta']['created_timestamp']) . '</span></time>' . ', ';
	            $output .= $imgmeta['image_meta']['camera'] . ', ';
	            $output .= $imgmeta['image_meta']['focal_length'] . 'mm' . ', ';
	            $output .= '<span style="font-style:italic;font-family: Trebuchet MS,Candara,Georgia; text-transform:lowercase">f</span>/' . $imgmeta['image_meta']['aperture'] . ', ';
	            $output .= $pshutter . ', ';
	            $output .= $imgmeta['image_meta']['iso'] .' ISO';
	        }
	    }else {
	        $output = 'No EXIF data found';
	    }
		echo '<span class="meta-exif"><i class="fa fa-camera"></i> ' . $output . '</span>';
	} elseif ( $meta == 'comments' || $meta == 'comment' ){
		$comments_text = 'Comments';
		if ( get_comments_number() == 0 ){
			$comment_icon = 'fa-comment-o';
			if ( $secondary ){ //Secondary here is if no comments should hide
				$comment_show = '';
			} else {
				$comment_show = 'hidden';
			}
		} elseif ( get_comments_number() == 1 ){
			$comment_icon = 'fa-comment';
			$comments_text = 'Comment';
		} elseif ( get_comments_number() > 1 ){
			$comment_icon = 'fa-comments';
		}
		$postlink = ( is_single() ) ? '' : get_the_permalink();
		echo '<span class="posted-comments ' . $comment_show . '"><i class="fa ' . $comment_icon . '"></i> <a class="nebulametacommentslink" href="' . $postlink . '#nebulacommentswrapper">' . get_comments_number() . ' ' . $comments_text . '</a></span>';
	} elseif ( $meta == 'social' || $meta == 'sharing' || $meta == 'share' ){
		nebula_social(array('facebook', 'twitter', 'google+', 'linkedin', 'pinterest'), 0);
	}
}






/*
//Displays camera exif information for an attachment
function get_exif($att){
    $imgmeta = wp_get_attachment_metadata($att);
    if ( $imgmeta ){ //Check for Bad Data
        if ( $imgmeta['image_meta']['focal_length'] == 0 || $imgmeta['image_meta']['aperture'] == 0 || $imgmeta['image_meta']['shutter_speed'] == 0 || $imgmeta['image_meta']['iso'] == 0 ){
            $output = '';
        } else { //Convert the shutter speed retrieve from database to fraction
            if ( (1/$imgmeta['image_meta']['shutter_speed']) > 1 ){
                if ( (number_format((1/$imgmeta['image_meta']['shutter_speed']), 1)) == 1.3 || number_format((1/$imgmeta['image_meta']['shutter_speed']), 1) == 1.5 || number_format((1/$imgmeta['image_meta']['shutter_speed']), 1) == 1.6 || number_format((1/$imgmeta['image_meta']['shutter_speed']), 1) == 2.5 ){
                    $pshutter = "1/" . number_format((1/$imgmeta['image_meta']['shutter_speed']), 1, '.', '') . " second";
                } else {
                    $pshutter = "1/" . number_format((1/$imgmeta['image_meta']['shutter_speed']), 0, '.', '') . " second";
                }
            } else {
                $pshutter = $imgmeta['image_meta']['shutter_speed'] . " seconds";
            }

            $output = '<time datetime="' . date('c', $imgmeta['image_meta']['created_timestamp']) . '"><span class="month">' . date('F', $imgmeta['image_meta']['created_timestamp']).'</span> <span class="day">'.date('j', $imgmeta['image_meta']['created_timestamp']) . '</span><span class="suffix">' . date('S', $imgmeta['image_meta']['created_timestamp']) . '</span> <span class="year">' . date('Y', $imgmeta['image_meta']['created_timestamp']) . '</span></time>' . ', ';
            $output .= $imgmeta['image_meta']['camera'] . ', ';
            $output .= $imgmeta['image_meta']['focal_length'] . 'mm' . ', ';
            $output .= '<span style="font-style:italic;font-family: Trebuchet MS,Candara,Georgia; text-transform:lowercase">f</span>/' . $imgmeta['image_meta']['aperture'] . ', ';
            $output .= $pshutter . ', ';
            $output .= $imgmeta['image_meta']['iso'] .' ISO';
        }
    } else { //No Data Found
        $output = 'No data found';
    }
    return $output;
}
*/










function nebula_social($networks=array('facebook', 'twitter', 'google+'), $counts=0){
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

function nebula_facebook_share($counts=0){?>
	<div class="nebula-social-button facebook-share">
		<div class="fb-share-button" data-href="<?php echo get_page_link(); ?>" data-layout="<?php echo ( $counts != 0 ) ? 'button_count' : 'button'; ?>"></div>
	</div>
<?php }


function nebula_facebook_like($counts=0){ ?>
	<div class="nebula-social-button facebook-like">
		<div class="fb-like" data-href="<?php echo get_page_link(); ?>" data-layout="<?php echo ( $counts != 0 ) ? 'button_count' : 'button'; ?>" data-action="like" data-show-faces="false" data-share="false"></div>
	</div>
<?php }

function nebula_facebook_both($counts=0){ ?>
	<div class="nebula-social-button facebook-both">
		<div class="fb-like" data-href="<?php echo get_page_link(); ?>" data-layout="<?php echo ( $counts != 0 ) ? 'button_count' : 'button'; ?>" data-action="like" data-show-faces="false" data-share="true"></div>
	</div>
<?php }

$nebula_twitter_tweet = 0;
function nebula_twitter_tweet($counts=0){ ?>
	<div class="nebula-social-button twitter-tweet">
		<a href="https://twitter.com/share" class="twitter-share-button" <?php echo ( $counts != 0 ) ? '': 'data-count="none"'; ?>>Tweet</a>
		<?php if ( $nebula_twitter_tweet == 0 ) : ?>
			<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
		<?php endif; ?>
	</div>
<?php
	$nebula_twitter_tweet = 1;
}

$nebula_google_plus = 0;
function nebula_google_plus($counts=0){ ?>
	<div class="nebula-social-button google-plus-plus-one">
		<div class="g-plusone" data-size="medium" <?php echo ( $counts != 0 ) ? '' : 'data-annotation="none"'; ?>></div>
		<?php if ( $nebula_google_plus == 0 ) : ?>
			<script src="https://apis.google.com/js/platform.js" async defer></script>
		<?php endif; ?>
	</div>
<?php
	$nebula_google_plus = 1;
}

$nebula_linkedin_share = 0;
function nebula_linkedin_share($counts=0){ //@TODO "Nebula" 0: Bubble counts are not showing up... ?>
	<div class="nebula-social-button linkedin-share">
		<?php if ( $nebula_linkedin_share == 0 ) : ?>
			<script src="//platform.linkedin.com/in.js" type="text/javascript"> lang: en_US</script>
		<?php endif; ?>
		<script type="IN/Share" <?php echo ( $counts != 0 ) ? 'data-counter="right"' : ''; ?>></script>
	</div>
<?php
	$nebula_linkedin_share = 1;
}

$nebula_pinterest_pin = 0;
function nebula_pinterest_pin($counts=0){ //@TODO "Nebula" 0: Bubble counts are not showing up...
	if ( has_post_thumbnail() ){
		$featured_image = get_the_post_thumbnail();
	} else {
		$featured_image = get_template_directory_uri() . '/images/meta/og-thumb.png'; //@TODO "Nebula" 0: This should probably be a square? Check the recommended dimensions.
	}
?>
	<div class="nebula-social-button pinterest-pin">
		<a href="//www.pinterest.com/pin/create/button/?url=<?php echo get_page_link(); ?>&media=<?php echo $featured_image; ?>&description=<?php echo urlencode(get_the_title()); ?>" data-pin-do="buttonPin" data-pin-config="<?php echo ( $counts != 0 ) ? 'beside' : 'none'; ?>" data-pin-color="red">
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
		$username = ( $_POST['data']['username'] ) ? $_POST['data']['username'] : 'Great_Blakes';
		$listname = ( $_POST['data']['listname'] ) ? $_POST['data']['listname'] : null; //Only used for list feeds
		$number_tweets = ( $_POST['data']['numbertweets'] ) ? $_POST['data']['numbertweets'] : 5;
		$include_retweets = ( $_POST['data']['includeretweets'] ) ? $_POST['data']['includeretweets'] : 1; //1: Yes, 0: No
	}

	error_reporting(0); //Prevent PHP errors from being cached.

	if ( $listname ){
		$feed = 'https://api.twitter.com/1.1/lists/statuses.json?slug=' . $listname . '&owner_screen_name=' . $username . '&count=' . $number_tweets . '&include_rts=' . $include_retweets;
	} else {
		$feed = 'https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=' . $username . '&count=' . $number_tweets . '&include_rts=' . $include_retweets;
	}

	$bearer = nebula_settings_conditional_text('nebula_twitter_bearer_token', '');

	$tweets = get_transient('nebula_twitter_' . $username); //@TODO: The transient name should have the twitter name tied to it...
	if ( empty($tweets) || is_debug() ){
		$context = stream_context_create(array(
			'http' => array(
				'method'=>'GET',
				'header'=>"Authorization: Bearer " . $bearer
			)
		));
		$tweets = file_get_contents($feed, false, $context);

		if ( !$tweets ){
			echo false;
			exit;
		}

		set_transient('nebula_twitter_' . $username, $tweets, 60*5); //5 minute expiration
	}

	if ( $_POST['data'] ){
		echo $tweets;
		exit;
	} else {
		error_reporting(1);
		return $tweets;
	}
}


//Use this instead of the_excerpt(); and get_the_excerpt(); so we can have better control over the excerpt.
//Several ways to implement this:
	//Inside the loop (or outside the loop for current post/page): nebula_the_excerpt('Read more &raquo;', 20, 1);
	//Outside the loop: nebula_the_excerpt(572, 'Read more &raquo;', 20, 1);
function nebula_the_excerpt( $postID=0, $more=0, $length=55, $hellip=0 ){
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

function nebula_custom_excerpt($text=false, $length=55, $hellip=false, $link=false, $more=false){
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
	if ( $data == 'edit' || $data == 'admin' ){
		echo '<span class="nebula-manage-edit"><span class="post-admin"><i class="fa fa-wrench"></i> <a href="' . get_admin_url() . '" target="_blank">Admin</a></span> <span class="post-edit"><i class="fa fa-pencil"></i> <a href="' . get_edit_post_link() . '">Edit</a></span></span>';
	} elseif ( $data == 'modified' || $data == 'mod' ){
		if ( get_the_modified_author() ){
			$manage_author = get_the_modified_author();
		} else {
			$manage_author = get_the_author();
		}
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
	include('includes/navigator.php');
	//include('includes/navigat-holder.php');
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
		if ( function_exists('is_pod_page') ){
			if ( is_pod_page() ){
				$skipThese = array('wordsgohere'); //An array of words(?) to skip. //array('detail', 'concentration')

				//Explode the URI and turn each virtual path into a crumb.
				$url_parts = explode('/', $_SERVER['REQUEST_URI']);
				$link;
				$i = 0;
				foreach ( $url_parts as $key => $value ){
					if ( $key != (count($url_parts)-1) ){
						if( $value != '' && !in_array($value, $skipThese) ){
							$pieces = explode('-', $value);
							$link_str = '';
							$link = ($i == 0) ? $link : $link  . '/' . $value;
							foreach ( $pieces as $key => $value ){
								if ( !in_array($value, $dontCapThese) ){
									$link_str .= ucfirst($value) . ' ';
								} else{
									$link_str .= $value . ' ';
								}
							}
							echo '<a href="' . $homeLink . $link . '/">' . $link_str . '</a> ' . $delimiter . ' ';
						}
						$i++;
					}

					//Strip out the <a> tags
					if ( $key == (count($url_parts)-1) ){
						$pieces = explode('-', $value);
						foreach( $pieces as $key => $value ){
							if( !in_array($value, $dontCapThese) ){
								$txt_str .= ucfirst($value) . ' ';
							} else{
								$txt_str .= $value . ' ';
							}
						}
						echo $txt_str;
					}
				}
			}
		} elseif ( is_category() ){
			$thisCat = get_category(get_query_var('cat'), false);
			if ( $thisCat->parent != 0 ){
				echo get_category_parents($thisCat->parent, TRUE, ' ' . $delimiter . ' ');
			}
			echo $before . 'Category: ' . single_cat_title('', false) . $after;
		} elseif ( is_search() ){
			echo $before . 'Search results for "' . get_search_query() . '"' . $after;
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
} //End Breadcrumbs


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
//@TODO "Nebula" 0: Use this on template like 404 (and maybe even advanced search?) and search redirect (in header). Then expand this a little bit.
add_filter('get_search_form', 'nebula_search_form');
function nebula_search_form($form){
    $form = '<form role="search" method="get" id="searchform" action="' . home_url( '/' ) . '" >
	    <div>
		    <input type="text" value="' . get_search_query() . '" name="s" id="s" />
		    <input type="submit" id="searchsubmit" class="wp_search_submit" value="'. esc_attr__( 'Search' ) .'" />
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


function nebula_hero_search($placeholder='What are you looking for?'){
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
			$attachment_search_meta = ( get_the_title($attachment->ID) != '' ) ? get_the_title($attachment->ID) : $path_parts['filename'];
			similar_text(strtolower($_POST['data']['term']), strtolower($attachment_search_meta), $suggestion['similarity']);
			if ( $suggestion['similarity'] >= 50 ){
			    $suggestion['label'] = ( get_the_title($attachment->ID) != '' ) ? get_the_title($attachment->ID) : $path_parts['basename'];
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
		foreach ( $menu_items as $key => $menu_item ) {
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
	if ( nebula_author_bios_enabled() ){
		$authors = get_transient('nebula_autocomplete_authors');
		if ( empty($authors) || is_debug() ){
			$authors = get_users(array('role' => 'author')); //@TODO "Nebula" 0: This should get users who have made at least one post. Maybe get all roles (except subscribers) then if postcount >= 1?
			set_transient('nebula_autocomplete_authors', $authors, 60*60); //1 hour cache
		}
		foreach ( $authors as $author ){
			$author_name = ( $author->first_name != '' ) ? $author->first_name . ' ' . $author->last_name : $author->display_name; //might need adjusting here
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
	$suggestion['label'] = ( sizeof($suggestions) >= 1 ) ? '...more results for "' . $_POST['data']['term'] . '"' : 'Press enter to search for "' . $_POST['data']['term'] . '"';
	$suggestion['link'] = home_url('/') . '?s=' . str_replace(' ', '%20', $_POST['data']['term']);
	$suggestion['classes'] = ( sizeof($suggestions) >= 1 ) ? 'more-results search-link' : 'no-results search-link';
	$outputArray[] = $suggestion;

	echo json_encode($outputArray); //Return data in JSON

	exit;
}


add_action('wp_ajax_nebula_advanced_search', 'nebula_advanced_search');
add_action('wp_ajax_nopriv_nebula_advanced_search', 'nebula_advanced_search');
function nebula_advanced_search(){
	/*
		Search Term: $_POST['data']['term'] (string)
		Post Type: $_POST['data']['posttype'] (array)
		Categories & Tags: $_POST['data']['catstags'] (array) (prefixed with tag__ or category__ followed by the slug)
		Date From: $_POST['data']['datefrom'] (string: YYYY-MM-DD)
		Date To: $_POST['data']['dateto'] (string: YYYY-MM-DD)
	*/

	//Date Range Filter: http://stackoverflow.com/questions/8034697/wordpress-get-posts-by-date-range

	//Pull categories and tags from input.
	$categories = array();
	$tags = array();
	if ( $_POST['data']['catstags'] ){
		foreach ( $_POST['data']['catstags'] as $cattag ){
			if ( strpos($cattag, 'category__') > -1 ){
				$categories[] = substr($cattag, strpos($cattag, "category__")+10);
			} elseif ( strpos($cattag, 'tag__') > -1 ){
				$tags[] = substr($cattag, strpos($cattag, "tag__")+5);
			}
		}
	}
	array_map('intval', $categories);
	array_map('intval', $tags);

	$posttype = ( is_array($_POST['data']['posttype']) ) ? $_POST['data']['posttype'] : array('any'); //If post type field was not used, default to array('any').

	//@TODO: Get meta_query working to search custom fields too!

/*
	$args = array(
		'post_type' => $posttype,
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'category__and' => $categories,
		'tag__and' => $tags,
		's' => $_POST['data']['term'],
		//@TODO: Current meta_query only works as an AND... Need this to be an OR
		'meta_query' => array(
			array(
				'value' => $_POST['data']['term'],
				'compare' => 'LIKE'
			)
		)
	);
	$search = new WP_Query($args);
*/


	//I like this method because it merges the two queries using WP_Query (instead of get_posts), but it doesn't appear that all results are coming through (only 1 result for "remarketing" instead of 2).
	$q1 = new WP_Query(array(
	    'post_type' => $posttype,
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'category__and' => $categories,
		'tag__and' => $tags,
		's' => $_POST['data']['term'],
	));

	$q2 = new WP_Query(array(
	    'post_type' => $posttype,
		'post_status' => 'publish',
		'posts_per_page' => -1,
		//'category__and' => $categories,
		//'tag__and' => $tags,
		'meta_query' => array(
			array(
				'value' => $_POST['data']['term'],
				'compare' => 'LIKE'
			)
		)
	));

	$search = new WP_Query();
	$search->posts = array_unique(array_merge($q1->posts, $q2->posts), SORT_REGULAR);
	$search->post_count = count($search->posts);


	$your_query = ( $_POST['data']['term'] ) ? '"' . $_POST['data']['term'] . '"' : 'your query'; //@TODO: sanitize search term here
	$results_plural = ( $search->post_count == 1 ) ? 'result' : 'results';
	echo 'There are <strong>' . $search->post_count . ' ' . $results_plural . '</strong> for ' . $your_query . '.<br/><br/>';

	if ( $search->have_posts() ){
		while ( $search->have_posts() ){
			$search->the_post();

			if ( !get_the_title() ){
				continue;
			}

			?>

			<p style="padding-bottom: 15px; border-bottom: 1px dotted #ccc;">
				<a href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></a><br/>
				<span style="font-size: 12px;"><?php echo nebula_the_excerpt('', 10, 1); ?></span>
			</p>

			<?php
		}
	}

	exit;
}


//Check if user is using the debug query string.
function is_debug(){
	if ( array_key_exists('debug', $_GET) && is_dev() ){ //&& current_user_can('manage_options') maybe?
		return true;
	}
	return false;
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
header(base64_decode('RGV2ZWxvcGVkLXdpdGgtTmVidWxhOiBodHRwOi8vZ2VhcnNpZGUuY29tL25lYnVsYQ'.'=='));

//Add the Posts RSS Feed back in
add_action('wp_head', 'addBackPostFeed');
function addBackPostFeed(){
    echo '<link rel="alternate" type="application/rss+xml" title="RSS 2.0 Feed" href="'.get_bloginfo('rss2_url').'" />';
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

	//$classes[] = strtolower(str_replace($spaces_and_dots, $underscores_and_hyphens, nebula_device_detect())); //Add Device info to body classes //@TODO "Nebula" 0: Enable once better detection is set up.
	$classes[] = strtolower(str_replace($spaces_and_dots, $underscores_and_hyphens, nebula_os_detect())); //Add Operating System info to body classes
	$classes[] = strtolower(str_replace($spaces_and_dots, $underscores_and_hyphens, wp_browser_detect())); //Add Browser info to body classes
	$classes[] = str_replace($spaces_and_dots, $underscores_and_hyphens, $GLOBALS['browser_detect']['os']) . '_' . str_replace($spaces_and_dots, $underscores_and_hyphens, $GLOBALS['browser_detect']['os_number']); //Alternate OS detection with OS version too
	$classes[] = str_replace($spaces_and_dots, $underscores_and_hyphens, $GLOBALS['browser_detect']['browser_working']); //Rendering engine
	$classes[] = str_replace($spaces_and_dots, $underscores_and_hyphens, $GLOBALS['browser_detect']['browser_name']) . '_' . str_replace($spaces_and_dots, $underscores_and_hyphens, $GLOBALS['browser_detect']['browser_math_number']); //Browser name and major version number

	//Mobile
	if ( $GLOBALS["mobile_detect"]->isMobile() ){
		$classes[] = 'mobile';
	} else {
		$classes[] = 'no-mobile';
	}
	if ( $GLOBALS["mobile_detect"]->isTablet() ){
		$classes[] = 'tablet';
	}
	if ( $GLOBALS["mobile_detect"]->isiOS() ){
		$classes[] = 'ios';
	}
	if ( $GLOBALS["mobile_detect"]->isAndroidOS() ){
		$classes[] = 'androidos';
	}
	if ( $GLOBALS["mobile_detect"]->isIphone() ){
    	$classes[] = 'iphone';
    }


	//User Information
	$current_user = wp_get_current_user();
	if ( is_user_logged_in() ){
		$classes[] = 'user-' . $current_user->user_login;
		$user_info = get_userdata(get_current_user_id());
		$classes[] = 'user-role-' . $user_info->roles[0];
	}

	//Post Information
	if ( !is_search() && !is_archive() ){
		global $post;
		$segments = explode('/', trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' ));
		$parents = get_post_ancestors($post->ID);
		foreach ( $parents as $parent ){
			$classes[] = 'ancestor-id-' . $parent;
		}
		foreach ( $segments as $segment ){
			$classes[] = 'ancestor-of-' . $segment;
		}
		foreach ( get_the_category($post->ID) as $category ){
			$classes[] = 'cat-' . $category->cat_ID . '-id';
		}
	}
	$nebula_theme_info = wp_get_theme();
	$classes[] = 'nebula_' . str_replace('.', '-', $nebula_theme_info->get('Version'));

	//Time of Day
	$classes[] = ( currently_open() ) ? 'business-open' : 'business-closed';
	if ( contains(date('H'), array('23', '00', '01')) ){
		$classes[] = 'time-early time-night';
	} elseif ( contains(date('H'), array('02', '03', '04')) ){
		$classes[] = 'time-late time-night';
	} elseif ( contains(date('H'), array('05', '06', '07')) ){
		$classes[] = 'time-early time-morning';
	} elseif ( contains(date('H'), array('08', '09', '10')) ){
		$classes[] = 'time-late time-morning';
	} elseif ( contains(date('H'), array('11', '12', '13')) ){
		$classes[] = 'time-early time-midday';
	} elseif ( contains(date('H'), array('14', '15', '16')) ){
		$classes[] = 'time-late time-midday';
	} elseif ( contains(date('H'), array('17', '18', '19')) ){
		$classes[] = 'time-early time-evening';
	} elseif ( contains(date('H'), array('20', '21', '22')) ){
		$classes[] = 'time-late time-evening';
	}
	if ( date('H') >= 12 ){
		$classes[] = 'time-pm';
	} else {
		$classes[] = 'time-am';
	}

	if ( nebula_settings_conditional_text_bool('nebula_latitude') && nebula_settings_conditional_text_bool('nebula_longitude') ){
		$lat = nebula_settings_conditional_text('nebula_latitude');
		$lng = nebula_settings_conditional_text('nebula_longitude');
		$gmt = intval(get_option('gmt_offset'));
		$zenith = 90+50/60; //Civil twilight = 96°, Nautical twilight = 102°, Astronomical twilight = 108°
		$sunrise = strtotime(date_sunrise(strtotime('today'), SUNFUNCS_RET_STRING, $lat, $lng, $zenith, $gmt));
		$sunset = strtotime(date_sunset(strtotime('today'), SUNFUNCS_RET_STRING, $lat, $lng, $zenith, $gmt));
		if ( time() >= $sunrise && time() <= $sunset ){
			$classes[] = 'time-daylight';
		} else {
			$classes[] = 'time-darkness';
		}

		if ( strtotime('now') >= $sunrise-60*45 && strtotime('now') <= $sunrise+60*45 ){ //45 minutes before and after true sunrise
			$classes[] = 'time-sunrise';
		}
		if ( strtotime('now') >= $sunset-60*45 && strtotime('now') <= $sunset+60*45 ){ //45 minutes before and after true sunset
			$classes[] = 'time-sunset';
		}
	}

	$classes[] = 'day-' . strtolower(date('l'));
	$classes[] = 'month-' . strtolower(date('F'));

	if ( $GLOBALS['http'] && is_int($GLOBALS['http']) ){
		$classes[] = 'error' . $GLOBALS['http'];
	}

    return $classes;
}


//Add additional classes to post wrappers @TODO "Nebula" 0: Finish implementing this!
//add_filter('post_class', 'nebula_post_classes');
function nebula_post_classes($classes){
    global $wp_query;
    if ( $wp_query->current_post == 0 ){ //If first post in a query
        $classes[] = 'first-post';
    }
    if ( is_sticky() ){
	    $classes[] = 'sticky';
    }
    return $classes;
}


//Make sure attachment URLs match the protocol (to prevent mixed content warnings).
add_filter('wp_get_attachment_url', 'wp_get_attachment_url_example');
function wp_get_attachment_url_example($url){
    $http = site_url(false, 'http');
    $https = site_url(false, 'https');

    if ( $_SERVER['HTTPS'] == 'on' ){
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


//Check if the current time is within business hours.
function currently_open(){
	$businessHours = array();
	foreach ( array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday') as $weekday ){
		$businessHours[$weekday] = array(
			"enabled" => get_option('nebula_business_hours_' . $weekday . '_enabled'),
			"open" => get_option('nebula_business_hours_' . $weekday . '_open'),
			"close" => get_option('nebula_business_hours_' . $weekday . '_close')
		);
	}
	$today = strtolower(date('l'));

	$days_off = explode(', ', get_option('nebula_business_hours_closed'));
	foreach ( $days_off as $key => $day_off ){
		$days_off[$key] = strtotime($day_off . ' ' . date('Y'));

		if ( date('N', $days_off[$key]) == 6 ){ //If the date is a Saturday
			$days_off[$key] = strtotime(date('F j, Y', $days_off[$key]) . ' -1 day');
		} elseif ( date('N', $days_off[$key]) == 7 ){ //If the date is a Sunday
			$days_off[$key] = strtotime(date('F j, Y', $days_off[$key]) . ' +1 day');
		}

		if ( date('Y-m-d', $days_off[$key]) == date('Y-m-d', strtotime('today')) ){
			return false;
		}
	}

	if ( $businessHours[$today]['enabled'] == '1' ){ //If the Nebula Settings checkmark is checked for this day of the week.
		$now = time();

		$openToday = strtotime($businessHours[$today]['open']);
		$closeToday = strtotime($businessHours[$today]['close']);

		if ( $now >= $openToday && $now <= $closeToday ){
			return true;
		}
	}
	return false;
}


//Detect weather for Zip Code (using Yahoo! Weather)
function nebula_weather($zipcode=null, $data=null, $fresh=null){
	if ( $zipcode && is_string($zipcode) && !ctype_digit($zipcode) ){ //ctype_alpha($zipcode)
		$data = $zipcode;
		$zipcode = nebula_settings_conditional_text('nebula_postal_code', '13204');
	} elseif ( !$zipcode ){
		$zipcode = nebula_settings_conditional_text('nebula_postal_code', '13204');
	}

	global $current_weather;
	//$cache_file = get_template_directory() . '/includes/cache/weather-' . $zipcode;
	$url = 'http://weather.yahooapis.com/forecastrss?p=' . $zipcode;

	$weather = get_transient('nebula_weather_' . $zipcode);
	if ( empty($weather) || is_debug() ){
		$use_errors = libxml_use_internal_errors(true);
		$xml = simplexml_load_file($url);
		//@TODO "Nebula" 0: Need to come up with a way to pull default weather info in case yahooapis.com can't be reached.
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		set_transient('nebula_weather_' . $zipcode, $xml->asXML(), 60*60); //asXML() converts to string to it can be cached in the transient. 1 hour cache.
		$current_weather['cached'] = 'New';
	} else {
		$xml = simplexml_load_string($weather);
		$current_weather['cached'] = 'Cached';
	}

	$current_weather['conditions'] = $xml->channel->item->children('yweather', TRUE)->condition->attributes()->text;
	$current_weather['temp'] = $xml->channel->item->children('yweather', TRUE)->condition->attributes()->temp;
	$current_weather['city'] = $xml->channel->children('yweather', TRUE)->location->attributes()->city;
	$current_weather['state'] = $xml->channel->children('yweather', TRUE)->location->attributes()->region;
	$current_weather['city_state'] = $current_weather['city'] . ', ' . $current_weather['state'];
	$current_weather['zip'] = $zipcode;
	$current_weather['sunrise'] = $xml->channel->children('yweather', TRUE)->astronomy->attributes()->sunrise;
	$current_weather['sunset'] = $xml->channel->children('yweather', TRUE)->astronomy->attributes()->sunset;
	$current_weather["sunrise_seconds"] = strtotime($current_weather['sunrise'])-strtotime('today'); //Sunrise in seconds
	$current_weather["sunset_seconds"] = strtotime($current_weather['sunset'])-strtotime('today'); //Sunset in seconds
	$current_weather["noon_seconds"] = (($current_weather["sunset_seconds"]-$current_weather["sunrise_seconds"])/2)+$current_weather["sunrise_seconds"]; //Solar noon in seconds
	$current_weather['time_seconds'] = time()-strtotime("today");

	if ( $data && isset($current_weather[$data]) ){
		return $current_weather[$data];
	} elseif ( $data && !isset($current_weather[$data]) ){
		return 'Error: Requested data "' . $data . '" is not defined.';
	} else {
		return $current_weather;
	}
}


function vimeo_meta($videoID){
	return false; //@TODO "Nebula" 0: This function interferes with proper Relevanssi indexing. Look into an entirely alternate way besides simplexml maybe?

	$xml = simplexml_load_string(file_get_contents("http://vimeo.com/api/v2/video/" . $videoID . ".xml")); //@TODO "Nebula" 0: Use WP_Filesystem methods instead of file_get_contents
	if ( !$xml ){
		return 'A Vimeo API error occurred.';
	}
	$GLOBALS['vimeo_meta']['id'] = $videoID;
	$GLOBALS['vimeo_meta']['title'] = $xml->video->title;
	$GLOBALS['vimeo_meta']['safetitle'] = str_replace(" ", "-", $GLOBALS['vimeo_meta']['title']);
	$GLOBALS['vimeo_meta']['description'] = $xml->video->description;
	$GLOBALS['vimeo_meta']['upload_date'] = $xml->video->upload_date;
	$GLOBALS['vimeo_meta']['thumbnail'] = $xml->video->thumbnail_large;
	$GLOBALS['vimeo_meta']['url'] = $xml->video->url;
	$GLOBALS['vimeo_meta']['user'] = $xml->video->user_name;
	$GLOBALS['vimeo_meta']['seconds'] = strval($xml->video->duration);
	$GLOBALS['vimeo_meta']['duration'] = intval(gmdate("i", $GLOBALS['vimeo_meta']['seconds'])) . gmdate(":s", $GLOBALS['vimeo_meta']['seconds']);
	return $GLOBALS['vimeo_meta'];
}


function youtube_meta($videoID){
	return false; //@TODO "Nebula" 0: Youtube API is updated and no longer allows anonymous requests :(

	$xml = simplexml_load_string(file_get_contents("https://gdata.youtube.com/feeds/api/videos/" . $videoID)); //@TODO "Nebula" 0: Use WP_Filesystem methods instead of file_get_contents
	if ( !$xml ){
		return 'A Youtube API error occurred.';
	}
	$GLOBALS['youtube_meta']['origin'] = nebula_url_components('basedomain');
	$GLOBALS['youtube_meta']['id'] = $videoID;
	$GLOBALS['youtube_meta']['title'] = $xml->title;
	$GLOBALS['youtube_meta']['safetitle'] = str_replace(" ", "-", $GLOBALS['youtube_meta']['title']);
	$GLOBALS['youtube_meta']['content'] = $xml->content;
	$GLOBALS['youtube_meta']['href'] = $xml->link['href'];
	$GLOBALS['youtube_meta']['author'] = $xml->author->name;
	$temp = $xml->xpath('//yt:duration[@seconds]');
    $GLOBALS['youtube_meta']['seconds'] = strval($temp[0]->attributes()->seconds);
	$GLOBALS['youtube_meta']['duration'] = intval(gmdate("i", $GLOBALS['youtube_meta']['seconds'])) . gmdate(":s", $GLOBALS['youtube_meta']['seconds']);
	return $GLOBALS['youtube_meta'];
}




//Create tel: link if on mobile, otherwise return unlinked, human-readable number
function nebula_tel_link($phone, $postd=''){
	if ( $GLOBALS["mobile_detect"]->isMobile() ){
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
	if ( $GLOBALS["mobile_detect"]->isMobile() ){
		$sep = ( $GLOBALS["mobile_detect"]->isiOS() ) ? '?' : ';';
		//@TODO "Nebula" 0: Encode $message string here...?
		return '<a class="nebula-sms-link" href="sms:' . nebula_phone_format($phone, 'tel') . $sep . 'body=' . $message . '">' . nebula_phone_format($phone, 'human') . '</a>';
	} else {
		return nebula_phone_format($phone, 'human');
	}
}

//Convert phone numbers into ten digital dial-able or to human-readable
function nebula_phone_format($number, $format=''){

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
register_shutdown_function('shutdownFunction');
function shutDownFunction(){
	$error = error_get_last(); //Will return an error number, or null on normal end of script (without any errors).
	if ( $error['type'] == 1 || $error['type'] == 16 || $error['type'] == 64 || $error['type'] == 4 || $error['type'] == 256 || $error['type'] == 4096 ){
		ga_send_event('Error', 'PHP Error', 'Fatal Error [' . $error['type'] . ']: ' . $error['message'] . ' in ' . $error['file'] . ' on ' . $error['line'] . '.');
	}
}

set_error_handler('nebula_error_handler');
function nebula_error_handler($error_level, $error_message, $error_file, $error_line, $error_contest){
    switch ( $error_level ){
        case E_WARNING:
        case E_CORE_WARNING:
        case E_COMPILE_WARNING:
        case E_USER_WARNING:
            ga_send_event('Error', 'PHP Error', 'Warning [' . $error_level . ']: ' . $error_message . ' in ' . $error_file . ' on ' . $error_line . '.');
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
            ga_send_event('Error', 'PHP Error', 'Unknown Error Level ' . $error_level . ': ' . $error_message . ' in ' . $error_file . ' on ' . $error_line . '.');
            break;
    }
    return false; //After reporting, 'false' allows the original error handler to print errors.
}






