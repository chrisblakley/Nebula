<?php

//Set server timezone to match Wordpress
date_default_timezone_set( get_option('timezone_string') );

//Declare the content width
if ( !isset($content_width) ) {
	$content_width = 960;
}

//Disable Pingbacks to prevent security issues
add_filter('xmlrpc_methods', disable_pingbacks($methods));
function disable_pingbacks($methods) {
   unset($methods['pingback.ping']);
   return $methods;
};


//Construct the data payload
function gaBuildData($error) {
	$v = 1; //Version
	$cid = gaParseCookie(); //Anonymous Client ID

	//Send event
	$data = array(
		'v' => $v,
		'tid' => $GLOBALS['ga'],
		'cid' => $cid,
		't' => 'event',
		'ec' => 'Error', //Category (Required)
		'ea' => 'PHP ' . $error['type'] . ' [' . $error['level'] . ']', //Action (Required)
		'el' => $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line'] //Label
	);
	gaSendData($data);

	//Send Exception hit
	$data = array(
		'v' => $v,
		'tid' => $GLOBALS['ga'],
		'cid' => $cid,
		't' => 'exception',
		'exd' => 'PHP ' . $error['type'] . ' [' . $error['level'] . ']', //Exception Description
		'exf' => 0 //Fatal Exception? (Boolean) //@TODO "Nebula" 0: Pull this data from the $error array (if 'type' contains 'fatal'). Doesn't matter until the handler supports fatal errors.
	);
	gaSendData($data);
}

//Handle the parsing of the _ga cookie or setting it to a unique identifier
function gaParseCookie() {
	if (isset($_COOKIE['_ga'])) {
		list($version,$domainDepth, $cid1, $cid2) = explode('.', $_COOKIE["_ga"], 4);
		$contents = array('version' => $version, 'domainDepth' => $domainDepth, 'cid' => $cid1 . '.' . $cid2);
		$cid = $contents['cid'];
	} else {
		$cid = gaGenerateUUID();
	}
	return $cid;
}

//Generate UUID v4 function - needed to generate a CID when one isn't available
function gaGenerateUUID() {
	return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		mt_rand(0, 0xffff), mt_rand(0, 0xffff), //32 bits for "time_low"
		mt_rand(0, 0xffff), //16 bits for "time_mid"
		mt_rand(0, 0x0fff) | 0x4000, //16 bits for "time_hi_and_version", Four most significant bits holds version number 4
		mt_rand(0, 0x3fff) | 0x8000, //16 bits, 8 bits for "clk_seq_hi_res", 8 bits for "clk_seq_low", Two most significant bits holds zero and one for variant DCE1.1
		mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff) //48 bits for "node"
	);
}

//Send Data to Google Analytics
//https://developers.google.com/analytics/devguides/collection/protocol/v1/devguide#event
function gaSendData($data) {
	$getString = 'https://ssl.google-analytics.com/collect';
	$getString .= '?payload_data&';
	$getString .= http_build_query($data);
	$result = wp_remote_get($getString);
	return $result;
}

//Check for direct access redirects, log them, then redirect without queries.
add_action('init', 'check_direct_access');
function check_direct_access() {
	if ( isset($_GET['directaccess']) || array_key_exists('directaccess', $_GET) ) {
		$attempted = ( $_GET['directaccess'] != '' ) ? $_GET['directaccess'] : 'Unknown' ;
		$data = array(
			'v' => 1,
			'tid' => $GLOBALS['ga'],
			'cid' => gaParseCookie(),
			't' => 'event',
			'ec' => 'Direct Template Access', //Category
			'ea' => $attempted, //Action
			'el' => '' //Label
		);
		gaSendData($data);
		header('Location: ' . home_url('/'));
	}
}

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

		$data = array(
			'v' => 1,
			'tid' => $GLOBALS['ga'],
			'cid' => gaParseCookie(),
			't' => 'event',
			'ec' => 'Google Page Speed', //Category
			'ea' => $currentURL, //Action
			'el' => $currentTitle //Label
		);
		gaSendData($data);
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
			console.log('%c Nebula by Pinckney Hugo Group ', 'padding: 2px 10px; background: #0098d7; color: #fff;');
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

//Pull favicon from the theme folder (Front-end calls are in includes/metagraphics.php).
add_action('admin_head', 'admin_favicon');
function admin_favicon() {
	echo '<link rel="shortcut icon" type="image/x-icon" href="' . get_template_directory_uri() . '/images/meta/favicon.ico" />';
}


//Remove Wordpress version info from head and feeds
add_filter('the_generator', 'complete_version_removal');
function complete_version_removal() {
	return '';
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
add_image_size('example', 32, 32, 1);


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

//Remove version query strings from styles/scripts (to allow caching)
add_filter('script_loader_src', 'nebula_remove_script_version', 15, 1);
add_filter('style_loader_src', 'nebula_remove_script_version', 15, 1);
function nebula_remove_script_version($src){
	return remove_query_arg('ver', $src);
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
	add_action('init', 'disable_comments_admin_bar');
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
}

//Check referrer in order to comment
add_action('check_comment_flood', 'check_referrer');
function check_referrer() {
	if ( !isset($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER'] == '' ) {
		wp_die('Please do not access this file directly.');
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



//@TODO "Nebula" 0: The next two functions are in progress to pull data from a URL.

//Get the full URL. Not intended for secure use ($_SERVER var can be manipulated by client/server).
function nebula_requested_url($host="HTTP_HOST") { //Can use "SERVER_NAME" as an alternative to "HTTP_HOST".
	$protocol = ( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ) ? 'https' : 'http';
	$full_url = $protocol . '://' . $_SERVER["$host"] . $_SERVER["PHP_SELF"];
	return $full_url;
}

//Separate a URL into it's components. @TODO "Nebula" 0: Incomplete!
function nebula_url_components($segment="all", $url=null) {
	if ( !$url ) {
		$url = nebula_requested_url();
	}

	$url_compontents = parse_url($url);
	$host = explode('.', $url_compontents['host']);

	$domain = '';

	switch ($segment) {
		case 'all' : return $url; break;

		case 'protocol' : return $url_compontents['scheme']; break;
		case 'scheme' : return $url_compontents['scheme']; break;

		case 'www' :
			if ( $host[0] == 'www' ) {
				return 'www';
			} else {
				return false;
			}
			break;

		case 'subdomain' : //@TODO "Nebula" 0: This would return the primary domain if www does not exist nor does an actual subdomain. Need to check against actual domain.
			if ( $host[0] != 'www' && $host[0] != $domain ) {
				return $host[0];
			} else {
				return false;
			}
			break;

		case 'domain' : return $domain; break; //@TODO "Nebula" 0: Need to compare to a list of TLDs. Maybe find an XML feed or something dynamic. Then remove the tld.
		case 'sld' : return $domain; break; //@TODO "Nebula" 0: same as above

		case 'tld' : return ''; break; //@TODO "Nebula" 0: Need to compare to a list of TLDs. Maybe find an XML feed or something dynamic.

		case 'host' : return $url_compontents['host']; break;
		case 'path' : return $url_compontents['path']; break;
		case 'file' : return basename($url_compontents['path']); break;

		case 'query' : return $url_compontents['query']; break;
		default : return $url; break;
	}
}





//Display a random stock photo from unsplash.it
function random_unsplash($width=800, $height=600, $raw=0) {
	$skipList = array(35, 312, 16, 403, 172, 268, 267, 349, 69, 103, 24, 140, 47, 219, 222, 184, 306, 70, 371, 385, 45, 211, 95, 83, 150, 233, 275, 343, 317, 278, 429, 383, 296, 292, 193, 299, 195, 298, 68, 148, 151, 129, 277, 333, 85, 48, 128, 365, 138, 155, 257, 37, 288, 407);
	$randID = random_number_between_but_not(0, 430, $skipList);
	if ( $raw ) {
		return 'http://unsplash.it/' . $width . '/' . $height . '?image=' . $randID;
	} else {
		return 'http://unsplash.it/' . $width . '/' . $height . '?image=' . $randID . '" title="Unsplash ID #' . $randID;
	}
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
			echo '<i class="fa fa-expand"></i><a href="' . wp_get_attachment_url() . '" >' . $metadata['width'] . ' &times; ' . $metadata['height'] . '</a>';
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
		echo '<span class="posted-comments ' . $comment_show . '"><i class="fa ' . $comment_icon . '"></i> <a class="nebulametacommentslink" href="#nebulacommentswrapper">' . get_comments_number() . ' ' . $comments_text . '</a></span>';
	}
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

	$string = string_limit_words($string, $length);

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

	$string = string_limit_words($string, $length);

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

//Text limiter by words
function string_limit_words($string, $word_limit){
	$limited[0] = $string;
	$limited[1] = 0;
	$words = explode(' ', $string, ($word_limit + 1));
	if(count($words) > $word_limit){
		array_pop($words);
		$limited[0] = implode(' ', $words);
		$limited[1] = 1;
	}
	return $limited;
}

if ( array_key_exists('varcheck', $_GET) ) {
	$varcheck = false;
	add_action('init', 'varcheck');
	function varcheck() {
		if ( !function_exists('locate_and_check_global_variables') && !$varcheck ) {
			echo '<p class="varcheck" style="display: none;">vars-will-be-checked-next-reload</p>';
			$varcheck = true;
		}
	}
}

//Word limiter by characters
function word_limit_chars($string, $charlimit, $continue=false){
	// 1 = "Continue Reading", 2 = "Learn More"
	if(strlen(strip_tags($string, '<p><span><a>')) <= $charlimit){
		$newString = strip_tags($string, '<p><span><a>');
	} else{
		$newString = preg_replace('/\s+?(\S+)?$/', '', substr(strip_tags($string, '<p><span><a>'), 0, ($charlimit + 1)));
		if($continue == 1){
			$newString = $newString . '&hellip;' . ' <a class="continuereading" href="'. get_permalink() . '">Continue reading <span class="meta-nav">&rarr;</span></a>';
		} elseif($continue == 2){
			$newString = $newString . '&hellip;' . ' <a class="continuereading" href="'. get_permalink() . '">Learn more &raquo;</a>';
		} else{
			$newString = $newString . '&hellip;';
		}
	}
	return $newString;
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


//Breadcrumbs
function the_breadcrumb() {
	global $post;
	$delimiter = '<span class="arrow">&rsaquo;</span>'; //Delimiter between crumbs
	$home = '<i class="fa fa-home"></i>'; //Text for the 'Home' link
	$showCurrent = 1; // 1 - show current post/page title in breadcrumbs, 0 - don't show
	$before = '<span class="current">'; //Tag before the current crumb
	$after = '</span>'; //Tag after the current crumb
	$dontCapThese = array('the', 'and', 'but', 'of', 'a', 'and', 'or', 'for', 'nor', 'on', 'at', 'to', 'from', 'by');
	$homeLink = home_url('/');

	if ( is_home() || is_front_page() ) {
		echo '<div id="bcrumbs"><nav class="breadcrumbs"><a href="' . $homeLink . '">' . $home . '</a></nav></div>';
		return false;
	} else {
		echo '<div id="bcrumbs"><nav class="breadcrumbs"><a href="' . $homeLink . '">' . $home . '</a> ' . $delimiter . ' ';
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
			echo $before . 'Archive by category "' . single_cat_title('', false) . '"' . $after;
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
				echo '<a href="' . $homeLink . '/' . $slug['slug'] . '/">' . $post_type->labels->singular_name . '</a>';
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
			$post_type = get_post_type_object(get_post_type());
			echo $before . $post_type->labels->singular_name . $after;
		} elseif ( is_attachment() ) {
			echo 'Uploads &raquo; ';
			echo get_the_title();
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
			echo $before . 'Posts tagged "' . single_tag_title('', false) . '"' . $after;
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
		echo '</nav></div><!--/bcrumbs-->';
	}
} //End Breadcrumbs


//Prevent empty search query error (Show all results instead)
add_action('pre_get_posts', 'redirect_empty_search');
function redirect_empty_search($query){
	global $wp_query;
	if ( isset($_GET['s']) && $wp_query->query && !array_key_exists('invalid', $_GET) ) {
		if ( $_GET['s'] == '' && $wp_query->query['s'] == '' ) {
			header('Location: ' . home_url('/') . 'search/?invalid');
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
				if ( $wp_query->posts['0']->ID != 1 && get_permalink( $wp_query->posts['0']->ID ) != home_url() . '/' ) {
					$_GET['s'] = str_replace(' ', '+', $_GET['s']);
					wp_redirect( get_permalink( $wp_query->posts['0']->ID ) . '?rs=' . $_GET['s'] ); //Change this back to ?s if Search Everything can fix the "?s=" issue.
					exit;
				}
            } else {
                wp_redirect( get_permalink( $wp_query->posts['0']->ID ) . '?rs' ); //Change this back to ?s if Search Everything can fix the "?s=" issue.
                exit;
            }
        }
    }
}


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


//PHP-Mobile-Detect - https://github.com/serbanghita/Mobile-Detect/wiki/Code-examples
//Before running conditions using this, you must have $detect = new Mobile_Detect(); before the logic. In this case we are using the global variable $GLOBALS["mobile_detect"].
//Logic can fire from "$GLOBALS["mobile_detect"]->isMobile()" or "$GLOBALS["mobile_detect"]->isTablet()" or "$GLOBALS["mobile_detect"]->is('AndroidOS')".
require_once TEMPLATEPATH . '/includes/Mobile_Detect.php'; //@TODO "Nebula" 0: try changing TEMPLATEPATH to get_template_directory()
$GLOBALS["mobile_detect"] = new Mobile_Detect();

add_filter('body_class', 'mobile_body_class');
function mobile_body_class($classes) {

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

	return $classes;
}

//Add body classes based on WP core browser detection
add_filter('body_class', 'browser_body_class');
function browser_body_class($classes) {
	global $is_lynx, $is_gecko, $is_IE, $is_opera, $is_NS4, $is_safari, $is_chrome, $is_iphone;

	//$browser = get_browser(null, true); //@TODO "Nebula" 0: Find a server this works on and then wrap in if $browser, then echo the version number too
	//@TODO "Nebula" 0: Also look into the function wp_check_browser_version().

    if ( $is_lynx ) {
    	$classes[] = 'lynx';
    } elseif ( $is_gecko ) {
    	$classes[] = 'gecko';
    } elseif ( $is_opera ) {
    	$classes[] = 'opera';
    } elseif ( $is_NS4 ) {
    	$classes[] = 'ns4';
    } elseif ( $is_safari ) {
    	$classes[] = 'safari';
    } elseif ( $is_chrome ) {
    	$classes[] = 'chrome';
    	/*
		if ( $browser ) {
	    	$classes[] = 'chrome21';
    	}
		*/
    } elseif ( $is_IE ) {
    	$classes[] = 'ie';
    } else {
    	$classes[] = 'unknown_browser';
    }

    if ( $is_iphone ) {
    	$classes[] = 'iphone';
    }

    return $classes;
}

//Add additional body classes including ancestor IDs and directory structures
add_filter('body_class', 'page_name_body_class');
function page_name_body_class($classes) {
	global $post;
	$segments = explode('/', trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' ));
	$parents = get_post_ancestors( $post->ID );
	foreach ( $parents as $parent ) {
		$classes[] = 'ancestor-id-' . $parent;
	}
	foreach ( $segments as $segment ) {
		$classes[] = $segment;
	}
	foreach ( get_the_category($post->ID) as $category ) {
		$classes[] = 'cat-' . $category->cat_ID . '-id';
	}
	$nebula_theme_info = wp_get_theme();
	$classes[] = 'nebula_' . str_replace('.', '-', $nebula_theme_info->get('Version'));
	return $classes;
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
	if ( $businessHours[$today]['enabled'] == '1' ) {
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

	if ( $zipcode != $current_weather['zip'] || isset($fresh) ) {
		$url = 'http://weather.yahooapis.com/forecastrss?p=' . $zipcode;
		$use_errors = libxml_use_internal_errors(true);
		$xml = simplexml_load_file($url);
		if ( !$xml ) {
			$xml = simplexml_load_file('http://gearside.com/wp-content/themes/gearside2014/includes/static-weather.xml'); //Set a static fallback to prevent PHP errors @TODO "Nebula" 0: Change hard-coded URL!
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);

		global $current_weather;
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
	}

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
	$GLOBALS['youtube_meta']['origin'] = baseDomain();
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


function baseDomain($str='') {
	if ( $str == '' ) {
		$str = home_url();
	}
    $url = @parse_url( $str );
    if ( empty( $url['host'] ) ) return;
    $parts = explode( '.', $url['host'] );
    $slice = ( strlen( reset( array_slice( $parts, -2, 1 ) ) ) == 2 ) && ( count( $parts ) > 2 ) ? 3 : 2;
    $protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';
    return $protocol . implode( '.', array_slice( $parts, ( 0 - $slice ), $slice ) );
}


//Traverse multidimensional arrays
function in_array_r($needle, $haystack, $strict = true) {
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }
    return false;
}

//Recursive Glob
function glob_r($pattern, $flags = 0) {
    $files = glob($pattern, $flags);
    foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
        $files = array_merge($files, glob_r($dir . '/' . basename($pattern), $flags));
    }
    return $files;
}

//Add up the filesizes of files in a directory (and it's sub-directories)
function foldersize($path) {
	$total_size = 0;
	$files = scandir($path);
	$cleanPath = rtrim($path, '/') . '/';
	foreach($files as $t) {
		if ($t<>"." && $t<>"..") {
			$currentFile = $cleanPath . $t;
			if (is_dir($currentFile)) {
				$size = foldersize($currentFile);
				$total_size += $size;
			} else {
				$size = filesize($currentFile);
				$total_size += $size;
			}
		}
	}
	return $total_size;
}

//Checks to see if an array contains a string.
function contains($str, array $arr) {
    foreach( $arr as $a ) {
        if ( stripos($str,$a) !== false ) {
        	return true;
        }
    }
    return false;
}

//Generate a random integer between two numbers with an exclusion array
//Call it like: random_number_between_but_not(1, 10, array(5, 6, 7, 8));
function random_number_between_but_not($min=null, $max=null, $butNot=null) {
    if ( $min > $max ) {
        return 'Error: min is greater than max.'; //@TODO "Nebula" 0: If min is greater than max, swap the variables.
    }
    if ( gettype($butNot) == 'array' ) {
        foreach( $butNot as $key => $skip ){
            if( $skip > $max || $skip < $min ){
                unset($butNot[$key]);
            }
        }
        if ( count($butNot) == $max-$min+1 ) {
            return 'Error: no number exists between ' . $min .' and ' . $max .'. Check exclusion parameter.';
        }
        while ( in_array(($randnum = rand($min, $max)), $butNot));
    } else {
        while (($randnum = rand($min, $max)) == $butNot );
    }
    return $randnum;
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


//Automatically convert HEX colors to RGB.
function hex2rgb($color) {
	if ( $color[0] == '#' ) {
		$color = substr($color, 1);
	}
	if ( strlen($color) == 6 ) {
		list($r, $g, $b) = array($color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]);
	} elseif ( strlen($color) == 3 ) {
		list($r, $g, $b) = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
	} else {
		return false;
	}
	$r = hexdec($r);
	$g = hexdec($g);
	$b = hexdec($b);
	return array('r' => $r, 'g' => $g, 'b' => $b);
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