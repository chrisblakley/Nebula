<?php

//Set server timezone to match Wordpress
date_default_timezone_set(get_option('timezone_string'));


//Track Google Page Speed tests
add_action('wp_footer', 'track_google_pagespeed_checks');
function track_google_pagespeed_checks() {
	if ( strpos($_SERVER['HTTP_USER_AGENT'], 'Google Page Speed') !== false ) {
		$protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'https://' : 'http://';
		$currentURL = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		if ( strpos($currentURL, ".js") !== false ) {
			exit();
		} else {
			global $post;
			$currentTitle = get_the_title($post->ID);
		}

		ga_send_event('Google Page Speed', $currentURL, $currentTitle);
	}
}

//Add the calling card to the browser console
if ( nebula_settings_conditional('nebula_console_css') ) {
	add_action('wp_head', 'nebula_calling_card');
	function nebula_calling_card() {
		//@TODO "Nebula" 0: if chrome or firefox... (find what other browsers support this)
		echo "<script>
			if ( document.getElementsByTagName('html')[0].className.indexOf('lte-ie8') < 0 ) {
			console.log('%c', 'padding: 28px 119px; line-height: 35px; background: url(" . get_template_directory_uri() . "/images/phg/phg-logo.png) no-repeat; background-size: auto 60px;');
			console.log('%c Created using Nebula ', 'padding: 2px 10px; background: #0098d7; color: #fff;');
			}
		</script>";
	}
}


//Check for dev stylesheets
if ( nebula_settings_conditional('nebula_dev_stylesheets') ) {
	add_action('wp_enqueue_scripts', 'combine_dev_stylesheets');
	function combine_dev_stylesheets() {
		$file_counter = 0;
		file_put_contents(get_template_directory() . '/css/dev.css', '/**** Warning: This is an automated file! Anything added to this file manually will be removed! ****/'); //Empty /css/dev.css
		foreach ( glob(get_template_directory() . '/css/dev/*.css') as $file ) {
			$file_path_info = pathinfo($file);
			if ( is_file($file) && $file_path_info['extension'] == 'css' ) {
				$file_counter++;
				$this_css_filename = basename($file);
				$this_css_contents = file_get_contents($file); //Copy file contents
				$empty_css = ( $this_css_contents == '' ) ? ' (empty)' : '';
				$dev_css_contents = file_get_contents(get_template_directory() . '/css/dev.css');
				$dev_css_contents .= "/* ==========================================================================\r\n   " . get_template_directory_uri() . "/css/dev/" . $this_css_filename . $empty_css . "\r\n   ========================================================================== */\r\n\r\n" . $this_css_contents . "\r\n\r\n/* End of " . $this_css_filename . " */\r\n\r\n\r\n";
				file_put_contents(get_template_directory() . '/css/dev.css', $dev_css_contents);
			}
		}

		if ( $file_counter > 0 ) {
			wp_enqueue_style('nebula-dev_styles', get_template_directory_uri() . '/css/dev.css?c=' . $file_counter, array('nebula-main'), null);
		}
	}
}


//Redirect to favicon to force-clear the cached version when ?favicon is added.
add_action('wp_loaded', 'nebula_favicon_cache');
function nebula_favicon_cache(){
	if ( array_key_exists('favicon', $_GET) ) {
		header('Location: ' . get_template_directory_uri() . '/images/meta/favicon.ico');
	}
}

//Allow pages to have excerpts too
add_post_type_support('page', 'excerpt');


//Add Theme Support
if ( function_exists('add_theme_support') ) {
	add_theme_support('post-thumbnails');
}

//Remove Theme Support
if ( function_exists('remove_theme_support') ) {
	remove_theme_support('custom-background');
	remove_theme_support('custom-header');
}

//Add new image sizes
add_image_size('open_graph_large', 1200, 630, 1);
add_image_size('open_graph_small', 600, 315, 1);


//Dynamic Page Titles
add_filter('wp_title', 'filter_wp_title', 10, 2);
function filter_wp_title($title, $separator) {
	if ( is_feed() ) {
		return $title;
	}

	global $paged, $page;

	if ( is_search() ) {
		$title = 'Search results';
		if ( $paged >= 2 ) {
			$title .= ' ' . $separator . ' Page ' . $paged;
		}
		$title .= ' ' . $separator . ' ' . get_bloginfo('name', 'display');
		return $title;
	}

	$title .= get_bloginfo('name', 'display');

	$site_description = get_bloginfo('description', 'display');
	if ( $site_description && (is_home() || is_front_page()) ) {
		$title .= ' ' . $separator . ' ' . $site_description;
	}

	if ( $paged >= 2 || $page >= 2 ) {
		$title .= ' ' . $separator . ' Page ' . max($paged, $page);
	}

	return $title;
}


//Determine if the author should be the Company Name or the specific author's name.
function nebula_the_author($show_authors=1) {
	if ( !is_single() || $show_authors == 0 ) {
		return nebula_settings_conditional_text('nebula_site_owner', get_bloginfo('name'));
	} else {
		return ( get_the_author_meta('first_name') != '' ) ? get_the_author_meta('first_name') . ' ' . get_the_author_meta('last_name') : get_the_author_meta('display_name');;
	}
}


//List of HTTP status codes: http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
add_action('nebula_header', 'nebula_http_status');
function nebula_http_status($status=200, $redirect=0){
	if ( isset($_GET['http']) ) {
		$status = $_GET['http'];
	}

	$GLOBALS['http'] = intval($status);

	if ( is_int($GLOBALS['http']) && $GLOBALS['http'] != 0 && $GLOBALS['http'] != 200 ) {
		if ( $GLOBALS['http'] == '404' ) { //@TODO "Nebula" 0: Eventually consider removing the 404 page and using the http_status.php page.
			global $wp_query;
			$wp_query->set_404();
			status_header(404);
			if ( $redirect == 1 ) {
				header('Location: '); //@TODO "Nebula" 0: Redirect to a generic error page w/ the error query.
			} else {
				get_template_part('404');
			}
			die();
		} else {
			status_header(403);
			if ( $redirect == 1 ) {
				header('Location: '); //@TODO "Nebula" 0: Redirect to a generic error page w/ the error query.
			} else {
				get_template_part('http_status');
			}
			die();
		}
	}
}


add_action('widgets_init', 'nebula_widgets_init');
function nebula_widgets_init() {
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


//Override the default Wordpress search form
add_filter('get_search_form', 'my_search_form');
function my_search_form($form) {
    $form = '<form role="search" method="get" id="searchform" action="' . home_url( '/' ) . '" >
	    <div>
		    <input type="text" value="' . get_search_query() . '" name="s" id="s" />
		    <input type="submit" id="searchsubmit" class="wp_search_submit" value="'. esc_attr__( 'Search' ) .'" />
	    </div>
    </form>';
    return $form;
}


//Name the locations where Navigation Menus will be located (to avoid duplicate IDs)
add_action('after_setup_theme', 'nav_menu_locations');
function nav_menu_locations() {
	// Register nav menu locations
	register_nav_menus( array(
		'topnav' => 'Top Nav Menu',
		'header' => 'Header Menu [Primary]',
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


//Nebula backup contact form (if Contact Form 7 is not available)
add_action('wp_ajax_nebula_backup_contact_send', 'nebula_backup_contact_send');
add_action('wp_ajax_nopriv_nebula_backup_contact_send', 'nebula_backup_contact_send');
function nebula_backup_contact_send() {
	$to = array($GLOBALS['admin_user']->user_email, 'chrisb@pinckneyhugo.com'); //Could be an array of multiple email addresses
	$subject = 'Contact form submission via ' . get_bloginfo('name') . ' from ' . $_POST['data'][0]['name'];
	$message = $_POST['data'][0]['message'] + '\n\n\nThis message was sent by the backup contact form!';
	$headers = 'From: ' . $_POST['data'][0]['name'] . ' <' . $_POST['data'][0]['email'] . '>';
	wp_mail($to, $subject, $message, $headers);
	exit();
}


function nebula_backup_contact_form() {
	echo '<div id="cform7-container" class="cform-disabled">
		<div class="wpcf7" id="wpcf7-f384-o1" lang="en-US" dir="ltr">
			<div class="screen-reader-response"></div>
			<form class="wpcf7-form contact-form-backup">
				<ul>
					<li class="field">
						<span class="contact-form-heading">Name</span>
						<span class="wpcf7-form-control-wrap name">
							<input type="text" name="name" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required text input cform7-name fb-form-name" placeholder="Your Name*">
						</span>
					</li>
					<li class="field">
						<span class="contact-form-heading">Email</span>
						<span class="wpcf7-form-control-wrap email">
							<input type="email" name="email" size="40" class="wpcf7-form-control wpcf7-text wpcf7-email wpcf7-validates-as-required wpcf7-validates-as-email email input cform7-email" placeholder="Email Address*">
						</span>
					</li>
					<li class="field">
						<span class="contact-form-heading">Message</span>
						<span class="wpcf7-form-control-wrap message">
							<textarea name="message" cols="40" rows="10" class="wpcf7-form-control wpcf7-textarea wpcf7-validates-as-required textarea input cform7-message" placeholder="Enter your message here.*"></textarea>
						</span>
					</li>
					<li class="fieldzzzz">
						<input id="contact-submit" type="submit" value="Send" class="wpcf7-form-control wpcf7-submit submit">
					</li>
				</ul>
			</form>
		</div>
	</div>';
}


/*** If the project uses comments, remove the next set of functions (six), or force this conditional to be false! ***/
if ( nebula_settings_conditional('nebula_comments', 'disabled') ) {

	//Remove the Activity metabox
	add_action('wp_dashboard_setup', 'remove_activity_metabox');
	function remove_activity_metabox(){
		remove_meta_box('dashboard_activity', 'dashboard', 'normal');
	}

	//Remove Comments column
	add_filter('manage_posts_columns', 'remove_pages_count_columns');
	add_filter('manage_pages_columns', 'remove_pages_count_columns');
	add_filter('manage_media_columns', 'remove_pages_count_columns');
	function remove_pages_count_columns($defaults) {
		unset($defaults['comments']);
		return $defaults;
	}

	//Close comments on the front-end
	add_filter('comments_open', 'disable_comments_status', 20, 2);
	add_filter('pings_open', 'disable_comments_status', 20, 2);
	function disable_comments_status() {
		return false;
	}

	//Remove comments links from admin bar
	add_action('wp_loaded', 'disable_comments_admin_bar');
	function disable_comments_admin_bar() {
		if (is_admin_bar_showing()) {
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
	function disable_comments_admin() {
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
	function disable_comments_admin_menu_redirect() {
		global $pagenow;
		if ($pagenow === 'edit-comments.php' || $pagenow === 'options-discussion.php') {
			wp_redirect(admin_url());
			exit;
		}

		$post_types = get_post_types();
		foreach ($post_types as $post_type) {
			if(post_type_supports($post_type, 'comments')) {
				remove_post_type_support($post_type, 'comments');
				remove_post_type_support($post_type, 'trackbacks');
			}
		}
	}
} else {
	//Open comments on the front-end
	add_filter('comments_open', 'enable_comments_status', 20, 2);
	add_filter('pings_open', 'enable_comments_status', 20, 2);
	function enable_comments_status() {
		return true;
	}

	$filename = basename($_SERVER['REQUEST_URI']);
	if ( $filename == 'edit-comments.php' ) {
		add_action('admin_notices', 'disqus_link');
		function disqus_link(){
			if ( nebula_settings_conditional_text_bool('nebula_disqus_shortname') ) {
				echo "<div class='nebula_admin_notice updated'><p>You are using the Disqus commenting system. <a href='https://" . nebula_settings_conditional_text('nebula_disqus_shortname', '') . ".disqus.com/admin/moderate' target='_blank'>View the comment listings on Disqus &raquo;</a></p></div>";
			} else {
				echo "<div class='nebula_admin_notice error'><p>You are using the Disqus commenting system, <strong>BUT</strong> you have not set your shortname in <a href='themes.php?page=nebula_settings'>Nebula Settings</a>, so we can't send you directly to your comment listing! <a href='https://disqus.com/admin/moderate' target='_blank'>Go to Disqus &raquo;</a></p></div>";
			}
		}
	}
}



//Prefill form fields with comment author cookie
add_action('wp_head', 'comment_author_cookie');
function comment_author_cookie() {
	echo '<script>';
	if ( isset($_COOKIE['comment_author_' . COOKIEHASH]) ) {
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
function pinckney_hugo_group($anim) { pinckneyhugogroup($anim); }
function phg($anim) { pinckneyhugogroup($anim); }
function pinckneyhugogroup($anim=false, $white=false){
	if ( $anim ) {
		$anim = 'anim';
	}
	if ( $white ) {
		$white = 'anim';
	}
	echo '<a class="phg ' . $anim . ' ' . $white . '" href="http://www.pinckneyhugo.com/" target="_blank"><span class="pinckney">Pinckney</span><span class="hugo">Hugo</span><span class="group">Group</span></a>';
}









//Show different meta data information about the post. Typically used inside the loop.
//Example: nebula_meta('on', 0); //The 0 in the second parameter here makes the day link to the month archive.
//Example: nebula_meta('by');
function nebula_meta($meta, $secondary=1) {
	if ( $meta == 'date' || $meta == 'time' || $meta == 'on' || $meta == 'day' || $meta == 'when' ) {
		$the_day = '';
		if ( $secondary ) { //Secondary here is if the day should be shown
			$the_day = get_the_date('d') . '/';
		}
		echo '<span class="posted-on"><i class="fa fa-calendar"></i> <span class="entry-date">' . '<a href="' . home_url('/') . get_the_date('Y') . '/' . get_the_date('m') . '/' . '">' . get_the_date('F') . '</a>' . ' ' . '<a href="' . home_url() . '/' . get_the_date('Y') . '/' . get_the_date('m') . '/' . $the_day . '">' . get_the_date('j') . '</a>' . ', ' . '<a href="' . home_url() . '/' . get_the_date('Y') . '/' . '">' . get_the_date('Y') . '</a>' . '</span></span>';
	} elseif ( $meta == 'author' || $meta == 'by' ) {
		echo '<span class="posted-by"><i class="fa fa-user"></i> <span class="entry-author">' . '<a href="' . get_author_posts_url( get_the_author_meta( 'ID' ) ) . '">' . get_the_author() . '</a></span></span>';
	} elseif ( $meta == 'categories' || $meta == 'category' || $meta == 'cat' || $meta == 'cats' || $meta == 'in' ) {
		if ( is_object_in_taxonomy(get_post_type(), 'category') ) {
			$post_categories = '<span class="posted-in post-categories"><i class="fa fa-bookmark"></i> ' . get_the_category_list(', ') . '</span>';
		} else {
			$post_categories = '';
		}
		echo $post_categories;
	} elseif ( $meta == 'tags' || $meta == 'tag' ) {
		$tag_list = get_the_tag_list('', ', ');
		if ( $tag_list ) {
			$tag_icon = ( count(get_the_tags()) > 1 ) ? 'tags' : 'tag';
			$post_tags = '<span class="posted-in post-tags"><i class="fa fa-' . $tag_icon . '"></i> ' . $tag_list . '</span>';
		} else {
			$post_tags = '';
		}
		echo $post_tags;
	} elseif ( $meta == 'dimensions' || $meta == 'size' || $meta == 'image' || $meta == 'photo' ) {
		if ( wp_attachment_is_image() ) {
			$metadata = wp_get_attachment_metadata();
			echo '<i class="fa fa-expand"></i> <a href="' . wp_get_attachment_url() . '" >' . $metadata['width'] . ' &times; ' . $metadata['height'] . '</a>';
		}
	} elseif ( $meta == 'comments' || $meta == 'comment' ) {
		$comments_text = 'Comments';
		if ( get_comments_number() == 0 ) {
			$comment_icon = 'fa-comment-o';
			if ( $secondary ) { //Secondary here is if no comments should hide
				$comment_show = '';
			} else {
				$comment_show = 'hidden';
			}
		} elseif ( get_comments_number() == 1 ) {
			$comment_icon = 'fa-comment';
			$comments_text = 'Comment';
		} elseif ( get_comments_number() > 1 ) {
			$comment_icon = 'fa-comments';
		}
		$postlink = ( is_single() ) ? '' : get_the_permalink();
		echo '<span class="posted-comments ' . $comment_show . '"><i class="fa ' . $comment_icon . '"></i> <a class="nebulametacommentslink" href="' . $postlink . '#nebulacommentswrapper">' . get_comments_number() . ' ' . $comments_text . '</a></span>';
	} elseif ( $meta == 'social' || $meta == 'sharing' || $meta == 'share' ) {
		nebula_social(array('facebook', 'twitter', 'google+', 'linkedin', 'pinterest'), 0);
	}
}



function nebula_social($networks=array('facebook', 'twitter', 'google+'), $counts=0){
	if ( is_string($networks) ) { //if $networks is a string, create an array for the string.
		$networks = array($networks);
	} elseif ( is_int($networks) && ($networks == 1 || $networks == 0) ) { //If it is an integer of 1 or 0, then set it to $counts
		$counts = $networks;
		$networks = array('facebook', 'twitter', 'google+');
	} elseif ( !is_array($networks) ) {
		$networks = array('facebook', 'twitter', 'google+');
	}
	$networks = array_map('strtolower', $networks); //Convert $networks to lower case for more flexible string matching later.

	echo '<div class="sharing-links">';
	foreach ( $networks as $network ) {
		//Facebook
		if ( in_array($network, array('facebook', 'fb')) ) {
			nebula_facebook_share($counts);
		}

		//Twitter
		if ( in_array($network, array('twitter')) ) {
			nebula_twitter_tweet($counts);
		}

		//Google+
		if ( in_array($network, array('google_plus', 'google', 'googleplus', 'google+', 'g+', 'gplus', 'g_plus', 'google plus', 'google-plus', 'g-plus')) ) {
			nebula_google_plus($counts);
		}

		//LinkedIn
		if ( in_array($network, array('linkedin', 'li', 'linked-in', 'linked_in')) ) {
			nebula_linkedin_share($counts);
		}

		//Pinterest
		if ( in_array($network, array('pinterest', 'pin')) ) {
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
	if ( has_post_thumbnail() ) {
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




//Use this instead of the_excerpt(); and get_the_excerpt(); so we can have better control over the excerpt.
//Several ways to implement this:
	//Inside the loop (or outside the loop for current post/page): nebula_the_excerpt('Read more &raquo;', 20, 1);
	//Outside the loop: nebula_the_excerpt(572, 'Read more &raquo;', 20, 1);
function nebula_the_excerpt( $postID=0, $more=0, $length=55, $hellip=0 ) {
	if ( $postID && is_int($postID) ) {
		$the_post = get_post($postID);
	} else {
		if ( $postID != 0 || is_string($postID) ) {
			if ( $length == 0 || $length == 1 ) {
				$hellip = $length;
			} else {
				$hellip = false;
			}

			if ( is_int($more) ) {
				$length = $more;
			} else {
				$length = 55;
			}

			$more = $postID;
		}
		$postID = get_the_ID();
		$the_post = get_post($postID);
	}

	if ( $the_post->post_excerpt ) {
		$string = strip_tags(strip_shortcodes($the_post->post_excerpt), '');
	} else {
		$string = strip_tags(strip_shortcodes($the_post->post_content), '');
	}

	if ( $length == -1 || $length == '' || $length === null ) {
        $string = string_limit_words($string, strlen($string));
    } else {
        $string = string_limit_words($string, $length);
    }

	if ( $hellip ) {
		if ( $string[1] == 1 ) {
			$string[0] .= '&hellip; ';
		}
	}

	if ( isset($more) && $more != '' ) {
		$string[0] .= ' <a class="nebula_the_excerpt" href="' . get_permalink($postID) . '">' . $more . '</a>';
	}

	return $string[0];
}

function nebula_custom_excerpt($text=false, $length=55, $hellip=false, $link=false, $more=false) {
	$string = strip_tags(strip_shortcodes($text), '');

	if ( $length == -1 || $length == '' || $length == 'all' || $length === null ) {
        $string = string_limit_words($string, strlen($string));
    } else {
        $string = string_limit_words($string, $length);
    }

	if ( $hellip ) {
		if ( $string[1] == 1 ) {
			$string[0] .= '&hellip; ';
		}
	}

	if ( isset($link) && isset($more) && $more != '' ) {
		$string[0] .= ' <a class="nebula_custom_excerpt" href="' . $link . '">' . $more . '</a>';
	}

	return $string[0];
}


//Adds links to the WP admin and to edit the current post as well as shows when the post was edited last and by which author
//Important! This function should be inside of a "if ( current_user_can('manage_options') )" condition so this information isn't shown to the public!
function nebula_manage($data) {
	if ( $data == 'edit' || $data == 'admin' ) {
		echo '<span class="nebula-manage-edit"><span class="post-admin"><i class="fa fa-wrench"></i> <a href="' . get_admin_url() . '" target="_blank">Admin</a></span> <span class="post-edit"><i class="fa fa-pencil"></i> <a href="' . get_edit_post_link() . '">Edit</a></span></span>';
	} elseif ( $data == 'modified' || $data == 'mod' ) {
		if ( get_the_modified_author() ) {
			$manage_author = get_the_modified_author();
		} else {
			$manage_author = get_the_author();
		}
		echo '<span class="post-modified">Last Modified: <strong>' . get_the_modified_date() . '</strong> by <strong>' . $manage_author . '</strong></span>';
	} elseif ( $data == 'info' ) {
		if ( wp_attachment_is_image() ) {
			$metadata = wp_get_attachment_metadata();
			echo ''; //@TODO "Nebula" 0: In progress
		}
	}
}


//Speech recognition AJAX for navigating
add_action('wp_ajax_navigator', 'nebula_ajax_navigator');
add_action('wp_ajax_nopriv_navigator', 'nebula_ajax_navigator');
function nebula_ajax_navigator() {
	include('includes/navigator.php');
	//include('includes/navigat-holder.php');
	exit();
}


//Replace text on password protected posts to be more minimal
add_filter('the_password_form', 'nebula_password_form_simplify');
function nebula_password_form_simplify() {
    $output  = '<form action="' . esc_url(site_url('wp-login.php?action=postpass', 'login_post')) . '" method="post">';
	    $output .= '<span>Password: </span>';
	    $output .= '<input name="post_password" type="password" size="20" />';
	    $output .= '<input type="submit" name="Submit" value="Go" />';
    $output .= '</form>';
    return $output;
}


//Breadcrumbs
function the_breadcrumb() {
	global $post;
	$delimiter = '<span class="arrow">&rsaquo;</span>'; //Delimiter between crumbs
	$home = '<i class="fa fa-home"></i>'; //Text for the 'Home' link
	$showCurrent = 1; //1: Show current post/page title in breadcrumbs, 0: Don't show
	$before = '<span class="current">'; //Tag before the current crumb
	$after = '</span>'; //Tag after the current crumb
	$dontCapThese = array('the', 'and', 'but', 'of', 'a', 'and', 'or', 'for', 'nor', 'on', 'at', 'to', 'from', 'by', 'in');
	$homeLink = home_url('/');

	if ( $GLOBALS['http'] && is_int($GLOBALS['http']) ) {
		echo '<div class="breadcrumbcon"><nav class="breadcrumbs"><a href="' . $homeLink . '">' . $home . '</a> ' . $delimiter . ' ' . $before . 'Error ' . $GLOBALS['http'] . $after;
	} elseif ( is_home() || is_front_page() ) {
		echo '<div class="breadcrumbcon"><nav class="breadcrumbs"><a href="' . $homeLink . '">' . $home . '</a></nav></div>';
		return false;
	} else {
		echo '<div class="breadcrumbcon"><nav class="breadcrumbs"><a href="' . $homeLink . '">' . $home . '</a> ' . $delimiter . ' ';
		if ( function_exists('is_pod_page') ) {
			if ( is_pod_page() ) {
				$skipThese = array('wordsgohere'); //An array of words(?) to skip. //array('detail', 'concentration')

				//Explode the URI and turn each virtual path into a crumb.
				$url_parts = explode('/', $_SERVER['REQUEST_URI']);
				$link;
				$i = 0;
				foreach ( $url_parts as $key => $value ) {
					if ( $key != (count($url_parts)-1) ) {
						if( $value != '' && !in_array($value, $skipThese) ) {
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
					if ( $key == (count($url_parts)-1) ) {
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
		} elseif ( is_category() ) {
			$thisCat = get_category(get_query_var('cat'), false);
			if ( $thisCat->parent != 0 ) {
				echo get_category_parents($thisCat->parent, TRUE, ' ' . $delimiter . ' ');
			}
			echo $before . 'Category: ' . single_cat_title('', false) . $after;
		} elseif ( is_search() ) {
			echo $before . 'Search results for "' . get_search_query() . '"' . $after;
		} elseif ( is_day() ) {
			echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
			echo '<a href="' . get_month_link(get_the_time('Y'),get_the_time('m')) . '">' . get_the_time('F') . '</a> ' . $delimiter . ' ';
			echo $before . get_the_time('d') . $after;
		} elseif ( is_month() ) {
			echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
			echo $before . get_the_time('F') . $after;
		} elseif ( is_year() ) {
			echo $before . get_the_time('Y') . $after;
		} elseif ( is_single() && !is_attachment() ) {
			if ( get_post_type() != 'post' ) {
				$post_type = get_post_type_object(get_post_type());
				$slug = $post_type->rewrite;
				echo '<a href="' . $homeLink . $slug['slug'] . '/">' . $post_type->labels->singular_name . '</a>';
				if ( $showCurrent == 1 ) {
					echo ' ' . $delimiter . ' ' . $before . get_the_title() . $after;
				}
			} else {
				$cat = get_the_category();
				$cat = $cat[0];
				$cats = get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
				if ( $showCurrent == 0 ) {
					$cats = preg_replace("#^(.+)\s$delimiter\s$#", "$1", $cats);
				}
				echo $cats;
				if ( $showCurrent == 1 ) {
					echo $before . get_the_title() . $after;
				}
			}
		} elseif ( !is_single() && !is_page() && get_post_type() != 'post' && !is_404() ) {
			if ( is_archive() ) { //@TODO "Nebula" 0: Might not be perfect... This may never else out.
				$userdata = get_user_by('slug', get_query_var('author_name'));
				echo $before . $userdata->first_name . ' ' . $userdata->last_name . $after;
			} else { //What does this one do?
				$post_type = get_post_type_object(get_post_type());
				echo $before . $post_type->labels->singular_name . $after;
			}
		} elseif ( is_attachment() ) { //@TODO "Nebula" 0: Check for gallery pages? If so, it should be Home > Parent(s) > Gallery > Attachment




			if ( !empty($post->post_parent) ) { //@TODO "Nebula" 0: What happens if the page parent is a child of another page?
				echo '<a href="' . get_permalink($post->post_parent) . '">' . get_the_title($post->post_parent) . '</a>' . ' ' . $delimiter . ' ' . get_the_title();
			} else {
				echo get_the_title();
			}




		} elseif ( is_page() && !$post->post_parent ) {
			if ( $showCurrent == 1 ) {
				echo $before . get_the_title() . $after;
			}
		} elseif ( is_page() && $post->post_parent ) {
			$parent_id = $post->post_parent;
			$breadcrumbs = array();
			while ( $parent_id ) {
				$page = get_page($parent_id);
				$breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
				$parent_id  = $page->post_parent;
			}
			$breadcrumbs = array_reverse($breadcrumbs);
			for ( $i = 0; $i < count($breadcrumbs); $i++ ) {
				echo $breadcrumbs[$i];
				if ( $i != count($breadcrumbs)-1 ) {
					echo ' ' . $delimiter . ' ';
				}
			}
			if ( $showCurrent == 1 ) {
				echo ' ' . $delimiter . ' ' . $before . get_the_title() . $after;
			}
		} elseif ( is_tag() ) {
			echo $before . 'Tag: ' . single_tag_title('', false) . $after;
		} elseif ( is_author() ) {
			global $author;
			$userdata = get_userdata($author);
			echo $before . $userdata->display_name . $after;
		} elseif ( is_404() ) {
			echo $before . 'Error 404' . $after;
		}

		if ( get_query_var('paged') ) {
			if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) {
				echo ' (';
			}
			echo 'Page ' . get_query_var('paged');
			if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) {
				echo ')';
			}
		}
		echo '</nav></div><!--/breadcrumbcon-->';
	}
} //End Breadcrumbs


//Prevent empty search query error (Show all results instead)
add_action('pre_get_posts', 'redirect_empty_search');
function redirect_empty_search($query){
	global $wp_query;
	if ( isset($_GET['s']) && $wp_query->query && !array_key_exists('invalid', $_GET) ) {
		if ( $_GET['s'] == '' && $wp_query->query['s'] == '' && !is_admin() ) {
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
function redirect_single_post() {
    if ( is_search() ) {
        global $wp_query;
        if ($wp_query->post_count == 1 && $wp_query->max_num_pages == 1) {
            if ( isset($_GET['s']) ){
				//If the redirected post is the homepage, serve the regular search results page with one result (to prevent a redirect loop)
				if ( $wp_query->posts['0']->ID != 1 && get_permalink($wp_query->posts['0']->ID) != home_url() . '/' ) {
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
function addBackPostFeed() {
    echo '<link rel="alternate" type="application/rss+xml" title="RSS 2.0 Feed" href="'.get_bloginfo('rss2_url').'" />';
}


//Declare support for WooCommerce
if ( is_plugin_active('woocommerce/woocommerce.php') ) {
	add_theme_support('woocommerce');
	//Remove existing WooCommerce hooks to be replaced with our own
	remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
	remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
	//Replace WooCommerce hooks at our own declared locations
	add_action('woocommerce_before_main_content', 'custom_woocommerce_start', 10);
	add_action('woocommerce_after_main_content', 'custom_woocommerce_end', 10);
	function custom_woocommerce_start() {
		echo '<section id="WooCommerce">';
	}
	function custom_woocommerce_end() {
		echo '</section>';
	}
}


//Add custom body classes
add_filter('body_class', 'nebula_body_classes');
function nebula_body_classes($classes) {

	$spaces_and_dots = array(' ', '.');
	$underscores_and_hyphens = array('_', '-');

	//$classes[] = strtolower(str_replace($spaces_and_dots, $underscores_and_hyphens, nebula_device_detect())); //Add Device info to body classes //@TODO "Nebula" 0: Enable once better detection is set up.
	$classes[] = strtolower(str_replace($spaces_and_dots, $underscores_and_hyphens, nebula_os_detect())); //Add Operating System info to body classes
	$classes[] = strtolower(str_replace($spaces_and_dots, $underscores_and_hyphens, wp_browser_detect())); //Add Browser info to body classes
	$classes[] = str_replace($spaces_and_dots, $underscores_and_hyphens, $GLOBALS['browser_detect']['os']) . '_' . str_replace($spaces_and_dots, $underscores_and_hyphens, $GLOBALS['browser_detect']['os_number']); //Alternate OS detection with OS version too
	$classes[] = str_replace($spaces_and_dots, $underscores_and_hyphens, $GLOBALS['browser_detect']['browser_working']); //Rendering engine
	$classes[] = str_replace($spaces_and_dots, $underscores_and_hyphens, $GLOBALS['browser_detect']['browser_name']) . '_' . str_replace($spaces_and_dots, $underscores_and_hyphens, $GLOBALS['browser_detect']['browser_math_number']); //Browser name and major version number

	//Mobile
	if ( $is_iphone ) {
    	$classes[] = 'iphone';
    }
	if ( $GLOBALS["mobile_detect"]->isMobile() ) {
		$classes[] = 'mobile';
	} else {
		$classes[] = 'no-mobile';
	}
	if ( $GLOBALS["mobile_detect"]->isTablet() ) {
		$classes[] = 'tablet';
	}
	if ( $GLOBALS["mobile_detect"]->isiOS() ) {
		$classes[] = 'ios';
	}
	if ( $GLOBALS["mobile_detect"]->isAndroidOS() ) {
		$classes[] = 'androidos';
	}

	//User Information
	$current_user = wp_get_current_user();
	if ( is_user_logged_in() ) {
		$classes[] = 'user-' . $current_user->user_login;
		$user_info = get_userdata(get_current_user_id());
		$classes[] = 'user-role-' . $user_info->roles[0];
	}

	//Post Information
	global $post;
	$segments = explode('/', trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' ));
	$parents = get_post_ancestors( $post->ID );
	foreach ( $parents as $parent ) {
		$classes[] = 'ancestor-id-' . $parent;
	}
	foreach ( $segments as $segment ) {
		$classes[] = 'ancestor-of-' . $segment;
	}
	foreach ( get_the_category($post->ID) as $category ) {
		$classes[] = 'cat-' . $category->cat_ID . '-id';
	}
	$nebula_theme_info = wp_get_theme();
	$classes[] = 'nebula_' . str_replace('.', '-', $nebula_theme_info->get('Version'));

	//Time of Day
	$classes[] = ( currently_open() ) ? 'business-open' : 'business-closed';
	if ( contains(date('H'), array('00', '01', '02')) ) {
		$classes[] = 'time-early time-night';
	} elseif ( contains(date('H'), array('03', '04', '05')) ) {
		$classes[] = 'time-late time-night';
	} elseif ( contains(date('H'), array('06', '07', '08')) ) {
		$classes[] = 'time-early time-morning';
	} elseif ( contains(date('H'), array('09', '10', '11')) ) {
		$classes[] = 'time-late time-morning';
	} elseif ( contains(date('H'), array('12', '13', '14')) ) {
		$classes[] = 'time-early time-afternoon';
	} elseif ( contains(date('H'), array('15', '16', '17')) ) {
		$classes[] = 'time-late time-afternoon';
	} elseif ( contains(date('H'), array('18', '19', '20')) ) {
		$classes[] = 'time-early time-evening';
	} elseif ( contains(date('H'), array('21', '22', '23')) ) {
		$classes[] = 'time-late time-evening';
	}

	if ( nebula_settings_conditional_text_bool('nebula_latitude') && nebula_settings_conditional_text_bool('nebula_longitude') ) {
		$lat = nebula_settings_conditional_text('nebula_latitude');
		$lng = nebula_settings_conditional_text('nebula_longitude');
		$gmt = intval(get_option('gmt_offset'));
		$zenith = 90+50/60; //Civil twilight = 96°, Nautical twilight = 102°, Astronomical twilight = 108°
		$sunrise = strtotime($date . ' ' . date_sunrise(strtotime('today'), SUNFUNCS_RET_STRING, $lat, $lng, $zenith, $gmt));
		$sunset = strtotime($date . ' ' . date_sunset(strtotime('today'), SUNFUNCS_RET_STRING, $lat, $lng, $zenith, $gmt));
		if ( time() >= $sunrise && time() <= $sunset ) {
			$classes[] = 'time-daylight';
		} else {
			$classes[] = 'time-darkness';
		}
	}

	$classes[] = 'day-' . strtolower(date('l'));
	$classes[] = 'month-' . strtolower(date('F'));

	if ( $GLOBALS['http'] && is_int($GLOBALS['http']) ) {
		$classes[] = 'error' . $GLOBALS['http'];
	}

    return $classes;
}


//Add additional classes to post wrappers @TODO "Nebula" 0: Finish implementing this!
add_filter('post_class', 'nebula_post_classes');
function nebula_post_classes($classes) {
    global $wp_query;
    if ( $wp_query->current_post == 0 ) { //If first post in a query
        $classes[] = 'first-post';
    }
    if ( is_sticky() ) {
	    $classes[] = 'sticky';
    }
    return $classes;
}


//Make sure attachment URLs match the protocol (to prevent mixed content warnings).
add_filter('wp_get_attachment_url', 'wp_get_attachment_url_example');
function wp_get_attachment_url_example($url) {
    $http  = site_url(false, 'http');
    $https = site_url(false, 'https');

    if ( $_SERVER['HTTPS'] == 'on' ) {
        return str_replace( $http, $https, $url );
    } else {
        return $url;
    }
}


//Add more fields to attachments //@TODO "Nebula" 0: Enable this as needed. The below example adds a "License" field.
//add_filter('attachment_fields_to_edit', 'nebula_attachment_fields', 10, 2);
function nebula_attachment_fields($form_fields, $post) {
    $field_value = get_post_meta($post->ID, 'license', true);
    $form_fields['license'] = array(
        'value' => $field_value ? $field_value : '',
        'label' => 'License',
        'helps' => 'Specify the license type used for this image'
    );
    return $form_fields;
}
//add_action('edit_attachment', 'nebula_save_attachment_fields');
function nebula_save_attachment_fields($attachment_id) {
    $license = $_REQUEST['attachments'][$attachment_id]['license'];
    if ( isset($license) ) {
        update_post_meta($attachment_id, 'license', $license);
    }
}


//Check if the current time is within business hours.
function currently_open() {
	$businessHours = array();
	foreach ( array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday') as $weekday ) {
		$businessHours[$weekday] = array(
			"enabled" => get_option('nebula_business_hours_' . $weekday . '_enabled'),
			"open" => get_option('nebula_business_hours_' . $weekday . '_open'),
			"close" => get_option('nebula_business_hours_' . $weekday . '_close')
		);
	}
	$today = strtolower(date('l'));

	$days_off = explode(', ', get_option('nebula_business_hours_closed'));
	foreach($days_off as $key => $day_off){
		$days_off[$key] = strtotime($day_off . ' ' . date('Y'));

		if ( date('N', $days_off[$key]) == 6 ) { //If the date is a Saturday
			$days_off[$key] = strtotime(date('F j, Y', $days_off[$key]) . ' -1 day');
		} elseif ( date('N', $days_off[$key]) == 7 ) { //If the date is a Sunday
			$days_off[$key] = strtotime(date('F j, Y', $days_off[$key]) . ' +1 day');
		}

		if ( date('Y-m-d', $days_off[$key]) == date('Y-m-d', strtotime('today')) ) {
			return false;
		}
	}

	if ( $businessHours[$today]['enabled'] == '1' ) { //If the Nebula Settings checkmark is checked for this day of the week.
		$now = time();

		$openToday = strtotime($businessHours[$today]['open']);
		$closeToday = strtotime($businessHours[$today]['close']);

		if ( $now >= $openToday && $now <= $closeToday ) {
			return true;
		}
	}
	return false;
}

//Detect weather for Zip Code (using Yahoo! Weather)
function nebula_weather($zipcode=null, $data=null, $fresh=null){
	if ( $zipcode && is_string($zipcode) && !ctype_digit($zipcode) ) { //ctype_alpha($zipcode)
		$data = $zipcode;
		$zipcode = nebula_settings_conditional_text('nebula_postal_code', '13204');
	} elseif ( !$zipcode ) {
		$zipcode = nebula_settings_conditional_text('nebula_postal_code', '13204');
	}

	$cache_file = get_template_directory() . '/includes/cache/weather-' . $zipcode;
	$interval = 3600; //In seconds. 1 hour = 3600

	//@TODO "Nebula" 0: If file doesn't exist (because of the zipcode in name), then create the file.

	$url = 'http://weather.yahooapis.com/forecastrss?p=' . $zipcode;
	$modified = filemtime($cache_file);

	//var_dump(date('F j, Y', $modified));

	$now = time();

	global $current_weather;

	//If the cache file has not been modified -or- if the time since modified date is longer than the interval -or- if forced fresh data is passed -or- if the requested zipcode is not the current stored zipcode
	if ( !$modified || (($now-$modified) > $interval) || isset($fresh) || $zipcode != $current_weather['zip'] ) {
		$use_errors = libxml_use_internal_errors(true);
		$xml = simplexml_load_file($url);
		if ( !$xml ) {
			$xml = simplexml_load_file($cache_file);
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);

		if ( $xml ) {
			$cache_static = fopen($cache_file, 'w');
			fwrite($cache_static, $xml); //Trying to store SimpleXMLElement in the file only makes a few spaces... Strings work though. Maybe store the array in this or something?
			fclose($cache_static);
		}
	} else {
		$xml = file_get_contents($cache_file);

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

	//header('Cache-Control: no-cache, must-revalidate');
	//header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	//header('Content-type: application/json');

	if ( $data && isset($current_weather[$data]) ) {
		return $current_weather[$data];
	} elseif ( $data && !isset($current_weather[$data]) ) {
		return 'Error: Requested data "' . $data . '" is not defined.';
	} else {
		return $current_weather;
	}
}

function vimeo_meta($videoID) {
	$xml = simplexml_load_string(file_get_contents("http://vimeo.com/api/v2/video/" . $videoID . ".xml")); //@TODO "Nebula" 0: Use WP_Filesystem methods instead of file_get_contents
	if ( !$xml ) {
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


function youtube_meta($videoID) {
	$xml = simplexml_load_string(file_get_contents("https://gdata.youtube.com/feeds/api/videos/" . $videoID)); //@TODO "Nebula" 0: Use WP_Filesystem methods instead of file_get_contents
	if ( !$xml ) {
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
	if ( $GLOBALS["mobile_detect"]->isMobile() ) {
		if ( $postd ) {
			$search = array('#', 'p', 'w');
			$replace   = array('%23', ',', ';');
			$postd = str_replace($search, $replace, $postd);
			if ( strpos($postd, ',') === false || strpos($postd, ';') === false ) {
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
	if ( $GLOBALS["mobile_detect"]->isMobile() ) {
		$sep = ( $GLOBALS["mobile_detect"]->isiOS() ) ? '?' : ';';
		//@TODO "Nebula" 0: Encode $message string here...?
		return '<a class="nebula-sms-link" href="sms:' . nebula_phone_format($phone, 'tel') . $sep . 'body=' . $message . '">' . nebula_phone_format($phone, 'human') . '</a>';
	} else {
		return nebula_phone_format($phone, 'human');
	}
}

//Convert phone numbers into ten digital dial-able or to human-readable
function nebula_phone_format($number, $format=''){

	if ( $format == 'human' && (strpos($number, ')') == 4 || strpos($number, ')') == 6) ) {
		//Format is already human-readable
		return $number;
	} elseif ( $format == 'tel' && (strpos($number, '+1') == 0 && strlen($number) == 12) ) {
		//Format is already dialable
		return $number;
	}

	if ( (strpos($number, '+1') == 0 && strlen($number) == 12) || (strpos($number, '1') == 0 && strlen($number) == 11) || strlen($number) == 10 && $format != 'tel' ) {
		//Convert from dialable to human
		if ( strpos($number, '1') == 0 && strlen($number) == 11 ) {
			//13154786700
			$number = '(' . substr($number, 1, 3) . ') ' . substr($number, 4, 3) . '-' . substr($number, 7);
		} elseif ( strlen($number) == 10 ) {
			//3154786700
			$number = '(' . substr($number, 0, 3) . ') ' . substr($number, 3, 3) . '-' . substr($number, 6);
		} elseif ( strpos($number, '+1') == 0 && strlen($number) == 12 ) {
			//+13154786700
			$number = '(' . substr($number, 2, 3) . ') ' . substr($number, 5, 3) . '-' . substr($number, 8);
		} else {
			return 'Error: Unknown format.';
		}
		//@TODO "Nebula" 0: Maybe any numbers after "," "p" ";" or "w" could be added to the human-readable in brackets, like: (315) 555-1346 [323]
		//To do the above, set a remainder variable from above and add it to the return (if it exists). Maybe even add them to a span with a class so they can be hidden if undesired?
		return $number;
	} else {
		if ( strlen($number) < 7 ) {
			return 'Error: Too few digits.';
		} elseif ( strlen($number) < 10 ) {
			return 'Error: Too few digits (area code is required).';
		}
		//Convert from human to dialable
		if ( strpos($number, '1') != '0' ) {
			$number = '1 ' . $number;
		}

		if ( strpos($number,'x') !== false ) {
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
function footerWidgetCounter() {
	$footerWidgetCount = 0;
	if ( is_active_sidebar('First Footer Widget Area') ) {
		$footerWidgetCount++;
	}
	if ( is_active_sidebar('Second Footer Widget Area') ) {
		$footerWidgetCount++;
	}
	if ( is_active_sidebar('Third Footer Widget Area') ) {
		$footerWidgetCount++;
	}
	if ( is_active_sidebar('Fourth Footer Widget Area') ) {
		$footerWidgetCount++;
	}
	return $footerWidgetCount;
}



//Track PHP errors...
register_shutdown_function('shutdownFunction');
function shutDownFunction() {
	$error = error_get_last(); //Will return an error number, or null on normal end of script (without any errors).
	if ( $error['type'] == 1 || $error['type'] == 16 || $error['type'] == 64 || $error['type'] == 4 || $error['type'] == 256 || $error['type'] == 4096 ) {
		ga_send_event('Error', 'PHP Error', 'Fatal Error [' . $error['type'] . ']: ' . $error['message'] . ' in ' . $error['file'] . ' on ' . $error['line'] . '.');
	}
}

set_error_handler('nebula_error_handler');
function nebula_error_handler($error_level, $error_message, $error_file, $error_line, $error_contest) {
    switch ( $error_level ) {
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






