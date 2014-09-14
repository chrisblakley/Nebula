<?php
/**
 * Functions
 */


/*========================== 
 Include Nebula Functions Groups
 ===========================*/

require_once('functions/nebula_settings_functions.php'); //Nebula Settings Functions
require_once('functions/nebula_automations.php'); //Nebula Automations
require_once('functions/nebula_optimization.php'); //Nebula Optimization
require_once('functions/nebula_admin_functions.php'); //Nebula Admin Functions
require_once('functions/nebula_user_fields.php'); //Nebula User Fields
require_once('functions/nebula_functions.php'); //Nebula Functions
require_once('functions/nebula_shortcodes.php'); //Nebula Shortcodes

//To force override the Nebula Settings, uncomment the line below.
//This will disable changes made from the Nebula Settings page, and only allow edits from the functions files themselves.
//(To revert, comment out and choose "Enabled" in the Nebula Settings page)
/* update_option('nebula_overall', 'override'); */


/*========================== 
 Google Analytics Tracking ID
 ===========================*/
$GLOBALS['ga'] = nebula_settings_conditional_text('nebula_ga_tracking_id', ''); //@TODO: Change Google Analytics Tracking ID here


/*========================== 
 Register All Stylesheets
 ===========================*/
add_action('wp_enqueue_scripts', 'register_nebula_styles');
add_action('login_enqueue_scripts', 'register_nebula_styles');
add_action('admin_enqueue_scripts', 'register_nebula_styles');
function register_nebula_styles() {	
	//wp_register_style($handle, $src, $dependencies, $version, $media);
	wp_register_style('nebula-normalize', '//cdnjs.cloudflare.com/ajax/libs/normalize/3.0.1/normalize.min.css', array(), '3.0.1');
	wp_register_style('nebula-open_sans', '//fonts.googleapis.com/css?family=Open+Sans:400,700', array(), null);
	wp_register_style('nebula-open_sans_local', get_template_directory_uri() . '/css/open-sans.css', array(), null);
	wp_register_style('nebula-gumby', get_template_directory_uri() . '/css/gumby.css', array(), '2.6');
	wp_register_style('nebula-gumby_cdn', '//cdnjs.cloudflare.com/ajax/libs/gumby/2.6.0/css/gumby.min.css', array(), '2.6.0'); //Only useful for 12 col primary, entypo is also re-enabled
	wp_register_style('nebula-font_awesome', '//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.1.0/css/font-awesome.min.css', array(), '4.1.0');
	wp_register_style('nebula-mmenu', '//cdnjs.cloudflare.com/ajax/libs/jQuery.mmenu/4.3.2/css/jquery.mmenu.all.min.css', array(), '4.3.2');
	//wp_register_style('nebula-bxslider', get_template_directory_uri() . '/css/jquery.bxslider.css', array(), '4.1.2'); //bxSlider is conditionally loaded via main.js when needed.
	wp_register_style('nebula-datatables', '//cdnjs.cloudflare.com/ajax/libs/datatables/1.10.1/css/jquery.dataTables.min.css', array(), '1.10');
	wp_register_style('nebula-main', get_stylesheet_directory_uri() . '/style.css', array('nebula-normalize', 'nebula-gumby', 'nebula-mmenu'), null);
	wp_register_style('nebula-login', get_template_directory_uri() . '/css/login.css', array(), null);
	wp_register_style('nebula-admin', get_template_directory_uri() . '/css/admin.css', array(), null);
}


/*========================== 
 Register All Scripts
 ===========================*/
add_action('wp_enqueue_scripts', 'register_nebula_scripts');
add_action('login_enqueue_scripts', 'register_nebula_scripts');
add_action('admin_enqueue_scripts', 'register_nebula_scripts');
function register_nebula_scripts() {	
	//Use CDNJS to pull common libraries: http://cdnjs.com/
	//wp_register_script($handle, $src, $dependencies, $version, $in_footer);
	wp_register_script('nebula-modernizr_dev', get_template_directory_uri() . '/js/libs/modernizr.custom.64172.js?' . $GLOBALS['defer'], array(), '2.8.3', false);
	wp_register_script('nebula-modernizr_local', get_template_directory_uri() . '/js/libs/modernizr.min.js?' . $GLOBALS['defer'], array(), '2.8.3', false);
	wp_register_script('nebula-modernizr', '//cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.2/modernizr.min.js?' . $GLOBALS['defer'], array(), '2.8.2', false);
	wp_register_script('nebula-jquery_ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/jquery-ui.min.js?' . $GLOBALS['defer'], array(), '1.11.0', true);
	wp_register_script('nebula-mmenu', '//cdnjs.cloudflare.com/ajax/libs/jQuery.mmenu/4.3.2/js/umd/jquery.mmenu.umd.all.min.js', array(), '4.3.2', true);
	wp_register_script('nebula-cssbs', get_template_directory_uri() . '/js/libs/css_browser_selector.js?' . $GLOBALS['async'], array(), '1.0', true);
	wp_register_script('nebula-doubletaptogo', get_template_directory_uri() . '/js/libs/doubletaptogo.js?' . $GLOBALS['defer'], array(), null, true);
	//wp_register_script('nebula-bxslider', get_template_directory_uri() . '/js/libs/jquery.bxslider.min.js?' . $GLOBALS['defer'], array(), '4.1.2', true); //bxSlider is conditionally loaded via main.js when needed.
	wp_register_script('nebula-froogaloop', get_template_directory_uri() . '/js/libs/froogaloop.min.js', array(), null, true);
	wp_register_script('nebula-performance_timing', get_template_directory_uri() . '/js/libs/performance-timing.js?async', array(), null, true);
	wp_register_script('nebula-respond', '//cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.min.js?' . $GLOBALS['defer'], array(), '1.4.2', true);
	wp_register_script('nebula-html5shiv', '//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.2/html5shiv.min.js?' . $GLOBALS['defer'], array(), '3.7.2', true);
	wp_register_script('nebula-gumby_local', get_template_directory_uri() . '/js/libs/gumby.min.js?' . $GLOBALS['gumby_debug'], array(), '2.6', true);
	wp_register_script('nebula-gumby', '//cdnjs.cloudflare.com/ajax/libs/gumby/2.6.0/js/libs/gumby.min.js?' . $GLOBALS['gumby_debug'], array(), '2.6', true);
	wp_register_script('nebula-twitter', get_template_directory_uri() . '/js/libs/twitter.js', array(), null, true);
	wp_register_script('nebula-datatables', '//cdnjs.cloudflare.com/ajax/libs/datatables/1.10.1/js/jquery.dataTables.min.js', array(), '1.10', true);
	wp_register_script('nebula-maskedinput', '//cdnjs.cloudflare.com/ajax/libs/jquery.maskedinput/1.3.1/jquery.maskedinput.min.js', array(), '1.3.1', true);
	wp_register_script('nebula-main', get_template_directory_uri() . '/js/main.js?' . $GLOBALS['defer'], array('nebula-gumby', 'jquery', 'nebula-jquery_ui'), null, true);
	wp_register_script('nebula-login', get_template_directory_uri() . '/js/login.js', array('jquery'), null, true);
	wp_register_script('nebula-admin', get_template_directory_uri() . '/js/admin.js?' . $GLOBALS['defer'], array(), null, true);
}


//Control how scripts are loaded, and force clear cache for debugging
if ( array_key_exists('debug', $_GET) ) {
	$GLOBALS["debug"] = true;
	$GLOBALS["defer"] = '';
	$GLOBALS["async"] = '';
	$GLOBALS["gumby_debug"] = 'gumby-debug';
	header("Expires: Fri, 28 Mar 1986 02:40:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	
	add_action('wp_enqueue_scripts', 'enqueue_nebula_debug_scripts');
	function enqueue_nebula_debug_scripts() {
		wp_enqueue_script('performance-timing');
	}
} else {
	$GLOBALS["debug"] = false;
	$GLOBALS["defer"] = 'defer';
	$GLOBALS["async"] = 'async';
	$GLOBALS["gumby_debug"] = 'defer';
}


/*========================== 
 Enqueue Styles & Scripts on the Front-End
 ===========================*/
add_action('wp_enqueue_scripts', 'enqueue_nebula_frontend');
function enqueue_nebula_frontend() {
	
	//Stylesheets
	wp_enqueue_style('nebula-normalize');
	wp_enqueue_style('nebula-open_sans');
	//wp_enqueue_style('nebula-open_sans_local');
	wp_enqueue_style('nebula-gumby');
	wp_enqueue_style('nebula-mmenu');
	wp_enqueue_style('nebula-font_awesome');
	wp_enqueue_style('nebula-main');

	
	//Scripts
	wp_enqueue_script('jquery');
	wp_enqueue_script('nebula-jquery_ui');
	//wp_enqueue_script('swfobject');
	//wp_enqueue_script('hoverIntent');
	wp_enqueue_script('nebula-modernizr_dev');
	//wp_enqueue_script('nebula-modernizr'); //@TODO: Switch to this modernizr when launching (if not using advanced polyfills)
	
	wp_enqueue_script('nebula-mmenu');
	//wp_enqueue_script('nebula-supplementr');
	//wp_enqueue_script('nebula-cssbs');
	//wp_enqueue_script('nebula-doubletaptogo');
	wp_enqueue_script('nebula-gumby');
	wp_enqueue_script('nebula-main');

	//Conditionals
	if ( $GLOBALS["debug"] ) {
		wp_enqueue_script('nebula-performance_timing');
	}
	
	if ( preg_match('/(?i)msie [2-9]/', $_SERVER['HTTP_USER_AGENT']) ) {
		wp_enqueue_script('nebula-respond');
		wp_enqueue_script('nebula-html5shiv');
	}
	
	if ( is_page(9999) ) { //Datatables pages
		wp_enqueue_style('nebula-datatables');
		wp_enqueue_script('nebula-datatables');
	}
	
	if ( is_page(9999) ) { //Twitter pages (conditional may need to change depending on type of page it's used on)
		wp_enqueue_script('nebula-twitter');
	}	
}


/*========================== 
 Enqueue Styles & Scripts on the Login
 ===========================*/
add_action('login_enqueue_scripts', 'enqueue_nebula_login');
function enqueue_nebula_login() {
	//Stylesheets
	wp_enqueue_style('nebula-login');
	
	//Scripts
	wp_enqueue_script('jquery');
	wp_enqueue_script('nebula-modernizr');
	//wp_enqueue_script('nebula-cssbs');
	wp_enqueue_script('nebula-login');
}


/*========================== 
 Enqueue Styles & Scripts on the Admin
 ===========================*/
add_action('admin_enqueue_scripts', 'enqueue_nebula_admin');
function enqueue_nebula_admin() {
	//Stylesheets
	wp_enqueue_style('nebula-open_sans');
	wp_enqueue_style('nebula-admin');
	wp_enqueue_style('nebula-font_awesome');
	
	//Scripts
	wp_enqueue_script('nebula-admin');
}


//Close functions.php. Do not add anything after this closing tag!! ?>