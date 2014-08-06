<?php
/**
 * Functions
 */

/*========================== 
 Google Analytics Tracking ID
 ===========================*/
$GLOBALS['ga'] = nebula_settings_conditional_text('nebula_ga_tracking_id', ''); //@TODO: Change Google Analytics Tracking ID here


/*========================== 
 Nebula Stylesheets
 ===========================*/

//Register
//wp_register_style($handle, $src, $dependencies, $version, $media);
wp_register_style('nebula-normalize', get_template_directory_uri() . '/css/normalize.css', array(), '3.0.1');
wp_register_style('nebula-open_sans', '//fonts.googleapis.com/css?family=Open+Sans:400,300,600,700', array(), null);
wp_register_style('nebula-open_sans_local', get_template_directory_uri() . '/css/open-sans.css', array(), null);
wp_register_style('nebula-gumby', get_template_directory_uri() . '/css/gumby.css', array(), '2.6');
wp_register_style('nebula-font_awesome', get_template_directory_uri() . '/css/font-awesome.min.css', array(), '4.1');
wp_register_style('nebula-mmenu', get_template_directory_uri() . '/css/jquery.mmenu.all.css', array(), '4.3');
wp_register_style('nebula-datatables', get_template_directory_uri() . '/css/jquery.dataTables.css', array(), '1.10');
wp_register_style('nebula-main', get_stylesheet_directory_uri() . '/style.css', array('nebula-normalize', 'nebula-gumby', 'nebula-mmenu'), null);
wp_register_style('nebula-login', get_template_directory_uri() . '/css/login.css', array(), null);
wp_register_style('nebula-admin', get_template_directory_uri() . '/css/admin.css', array(), null);


/*========================== 
 Nebula Scripts
 ===========================*/

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

//Register
//wp_register_script($handle, $src, $dependencies, $version, $in_footer);
wp_register_script('nebula-modernizr_dev', get_template_directory_uri() . '/js/libs/modernizr.custom.64172.js?' . $GLOBALS['defer'], array(), '2.8.3', false);
wp_register_script('nebula-modernizr', get_template_directory_uri() . '/js/libs/modernizr.min.js?' . $GLOBALS['defer'], array(), '2.8.3', false);
wp_register_script('nebula-mmenu', get_template_directory_uri() . '/js/libs/jquery.mmenu.min.all.js', array(), '4.3', true);
wp_register_script('nebula-cssbs', get_template_directory_uri() . '/js/libs/css_browser_selector.js?' . $GLOBALS['async'], array(), '1.0', true);
wp_register_script('nebula-doubletaptogo', get_template_directory_uri() . '/js/libs/doubletaptogo.js?' . $GLOBALS['defer'], array(), null, true);
wp_register_script('nebula-froogaloop', get_template_directory_uri() . '/js/libs/froogaloop.min.js', array(), null, true);
wp_register_script('nebula-performance_timing', get_template_directory_uri() . '/js/libs/performance-timing.js?async', array(), null, true);
wp_register_script('nebula-respond', get_template_directory_uri() . '/js/libs/respond.js?' . $GLOBALS['defer'], array(), null, true); //Registerred, but called from footer.php (only when needed)
wp_register_script('nebula-html5shiv', get_template_directory_uri() . '/js/libs/html5shiv.js?' . $GLOBALS['defer'], array(), '3.7.2', true); //Registerred, but called from footer.php (only when needed)
wp_register_script('nebula-gumby', get_template_directory_uri() . '/js/libs/gumby.min.js?' . $GLOBALS['gumby_debug'], array(), '2.6', true);
wp_register_script('nebula-twitter', get_template_directory_uri() . '/js/libs/twitter.js', array(), null, true);
wp_register_script('nebula-datatables', get_template_directory_uri() . '/js/libs/jquery.dataTables.min.js', array(), '1.10', true);
wp_register_script('nebula-maskedinput', get_template_directory_uri() . '/js/libs/jquery.maskedinput.js', array(), null, true);
wp_register_script('nebula-main', get_template_directory_uri() . '/js/main.js?' . $GLOBALS['defer'], array('nebula-mmenu', 'nebula-gumby', 'jquery', 'jquery-ui-core', 'nebula-doubletaptogo'), null, true);
wp_register_script('nebula-login', get_template_directory_uri() . '/js/login.js', array('jquery'), null, true);
wp_register_script('nebula-admin', get_template_directory_uri() . '/js/admin.js?' . $GLOBALS['defer'], array(), null, true);


//Enqueue for frontend
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
	wp_enqueue_script('jquery-ui-core');
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
	
	if ( is_page(9999) ) { //Datatables pages
		wp_enqueue_style('nebula-datatables');
		wp_enqueue_script('nebula-datatables');
	}
	
	if ( is_page(9999) ) { //Twitter pages (conditional may need to change depending on type of page it's used on)
		wp_enqueue_script('nebula-twitter');
	}	
}

//Enqueue for WP Login
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

//Enqueue for WP Admin
add_action('admin_enqueue_scripts', 'enqueue_nebula_admin');
function enqueue_nebula_admin() {
	//Stylesheets
	wp_enqueue_style('nebula-open_sans');
	wp_enqueue_style('nebula-admin');
	wp_enqueue_style('nebula-font_awesome');
	
	//Scripts
	wp_enqueue_script('nebula-admin');
}


//Control which scripts use defer/async using a query string.
//Note: Not an ideal solution, but works until WP Core updates wp_enqueue_script(); to allow for deferring.
add_filter('clean_url', 'nebula_defer_async_scripts', 11, 1);
function nebula_defer_async_scripts($url) {
	if ( strpos($url, '.js?defer') === false && strpos($url, '.js?async') === false && strpos($url, '.js?gumby-debug') === false ) {
		return $url;
	}
	
	if ( strpos($url, '.js?defer') ) {
		return "$url' defer='defer";
	} elseif ( strpos($url, '.js?async') ) {
		return "$url' async='async";
	} elseif ( strpos($url, '.js?gumby-debug') ) {
		return "$url' gumby-debug='true";
	}
}


//Dequeue redundant files (from plugins)
//Important: Add a reason in comments to help future updates: Plugin Name - Reason
add_action('wp_print_scripts', 'nebula_dequeues', 9999);
add_action('wp_print_styles', 'nebula_dequeues', 9999);
function nebula_dequeues() {
	if ( !is_admin() ) {
		//Styles
		wp_deregister_style('open-sans'); //WP Core - We load Open Sans ourselves (or whatever font the project calls for)
		wp_deregister_style('admin-bar'); //WP Core - Even though these are admin-only resources, I'd rather them not add any interpreted load time
		wp_deregister_style('dashicons'); //WP Core - Even though these are admin-only resources, I'd rather them not add any interpreted load time NOT WORKING!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		wp_deregister_style('cff-font-awesome'); //Custom Facebook Feed - We enqueue the latest version of Font Awesome ourselves
		wp_deregister_style('se-link-styles'); //Search Everything - (As far as I know) We do not use any of their styles (I believe they are for additional settings)
		wp_deregister_style('contact-form-7'); //Contact Form 7 - Not sure specifically what it is styling specifically, so removing it unless we decide we need it.
		
		//Scripts
		if( !preg_match('/(?i)msie [2-8]/', $_SERVER['HTTP_USER_AGENT']) ) { //WP Core - Dequeue jQuery Migrate for browsers that don't need it.
			wp_deregister_script('jquery');
			wp_register_script('jquery', false, array('jquery-core'), '1.11.0'); //Just have to make sure this version reflects the actual jQuery version bundled with WP (click the jquery.js link in the source)
		}
		
		/* @TODO: Styles/Scripts to consider for dequeue
			- media-upload.min.js?
			- underscore.min.js?
		*/
	}
	
	//if ( get_the_ID() == 1 ) {
		//Page specific dequeues can go here
	//}
}

//Force settings within plugins
add_action('admin_init', 'nebula_plugin_force_settings');
function nebula_plugin_force_settings(){
	//Ultimate TinyMCE
	if ( file_exists(WP_PLUGIN_DIR . '/ultimate-tinymce') ) {
		$jwl_options_group4 = get_option('jwl_options_group4');
		unset($jwl_options_group4['jwl_dev_support']); //Prevent link on frontend.
		$jwl_options_group4['jwl_menu_location'] = 'Settings'; //Move the settings page under settings (instead of a top-level link in the admin sidebar)
		unset($jwl_options_group4['jwl_qr_code']); //Prevent enabling QR codes on the frontend
		unset($jwl_options_group4['jwl_qr_code_pages']); //Prevent enabling QR codes on the frontend
		$jwl_options_group4['jwl_disable_styles'] = '1'; //Disable annoying/distracting styles on plugin listing (and elsewhere).
		update_option('jwl_options_group4', $jwl_options_group4);
		add_user_meta(get_current_user_id(), 'jwl_ignore_notice_pro', 'true', true); //Prevent the Pro upgrade notice
	}
	//Search Everything
	if ( file_exists(WP_PLUGIN_DIR . '/search-everything') ) {
		$se_options = get_option('se_options');
	    $se_options['se_use_highlight'] = false; //Disable search keyword highlighting (to prevent interference with Nebula keyword highlighting)
	    update_option('se_options', $se_options);
	}
}

//Unset admin plugins from appearing on the frontend
//add_action('option_active_plugins', 'nebula_unset_admin_plugins_on_frontend'); //@TODO: Pretty sure this is causing a gigantic error... can't prove it, but I'm leaning towards deleting this function.
function nebula_unset_admin_plugins_on_frontend($plugins) {
	if ( !is_admin() ) {
		//var_dump($plugins);
		$admin_only_plugins = array_search('ultimate-tinymce/main.php' , $plugins); //@TODO: make this an array, then foreach through $admin_only_plugins as $admin_only_plugin //@TODO: This is not working...
		//$admin_only_plugins = array_search('contact-form-7/wp-contact-form-7.php' , $plugins); //This one works.
		/* @TODO: Add the following too:
			admin-menu-tree-page-view/index.php
			reveal-ids-for-wp-admin-25/reveal-ids-for-wp-admin-25.php
		*/
		if ( $admin_only_plugins ) {
			unset($plugins[$admin_only_plugins]);
		}
		//var_dump($plugins);
		return $plugins;
	}
}

//Override existing functions (typcially from plugins)
//Please add a comment with the reason for the override!
add_action('wp_print_scripts', 'nebula_remove_actions', 9999);
function nebula_remove_actions(){ //Note: Priorities much MATCH (not exceed) [default if undeclared is 10]
	if ( !is_admin() ) {
		//Frontend
		remove_action('wp_head', '_admin_bar_bump_cb'); //Admin bar <style> bump
		remove_action('get_footer', 'your_function'); //Ultimate TinyMCE fontend linkback
		//if ( get_the_ID() == 1 ) { remove_action('wp_footer', 'cff_js', 10); } //Custom Facebook Feed - Remove the feed from the homepage. @TODO: Update to any page/post type that should NOT have the Facebook Feed
	} else {
		//WP Admin
		remove_filter('admin_footer_text', 'espresso_admin_performance'); //Event Espresso - Prevent adding text to WP Admin footer
		remove_filter('admin_footer_text', 'espresso_admin_footer'); //Event Espresso - Prevent adding text to WP Admin footer
		remove_meta_box('espresso_news_dashboard_widget', 'dashboard', 'normal'); //Event Espresso - Remove Dashboard Metabox
	}
}


/*========================== 
 Server-Side Google Analytics
 ===========================*/
set_error_handler('nebula_error_handler');

//Custom error handler
function nebula_error_handler($error_level, $error_message, $error_file, $error_line, $error_contest) {
    /*
    	@TODO: Parse errors cannot be caught with this function. In order to make it work, you must auto prepend a file using php.ini or .htaccess
		.htaccess method: php_value auto_prepend_file "./includes/shutdown_tracker.php" Note: this hasn't worked for me. Beyond that, no testing has been done.
		
		@TODO: Need to come up with a way to print errors without triggering "Headers already sent" warnings!
    */
    $error = array(
        'type' => 'Unknown Error',
        'definition' => 'Unknown Error Level',
        'level' => $error_level,
        'message' => $error_message,
        'file' => $error_file,
        'line' => $error_line,
        'contest' => $error_contest
    );
    
    switch ( $error_level ) {
	    case E_ERROR: //(1) Fatal run-time errors. These indicate errors that can not be recovered from, such as a memory allocation problem. Execution of the script is halted. [Not supported by set_error_handler()]
	    case E_CORE_ERROR: //(16) Fatal errors that occur during PHP's initial startup. This is like an E_ERROR, except it is generated by the core of PHP. [Not supported by set_error_handler()]
	    case E_COMPILE_ERROR: //(64) Fatal compile-time errors. This is like an E_ERROR, except it is generated by the Zend Scripting Engine. [Not supported by set_error_handler()]
	    case E_PARSE: //(4) Compile-time parse errors. Parse errors should only be generated by the parser. [Not supported by set_error_handler()]
	    	$error['type'] = 'Fatal Error';
	    	$error['definition'] = 'Fatal run-time errors that can not be recovered from. Execution of the script is halted. Includes E_Error [1], E_CORE_ERROR [16], E_COMPILE_ERROR [64], and E_PARSE [4].';
	        gaBuildData($error);
	        nebula_print_error($error);
	        exit(1);
	        break;
	    case E_USER_ERROR: //(256) User-generated error message. This is like an E_ERROR, except it is generated in PHP code by using the PHP function trigger_error().
	    case E_RECOVERABLE_ERROR: //(4096) Catchable fatal error. It indicates that a probably dangerous error occurred, but did not leave the Engine in an unstable state. If the error is not caught by a user defined handle, the application aborts as it was an E_ERROR.
	        $error['type'] = 'Error';
	        $error['definition'] = 'Indicates a probably dangerous error occurred, but did not leave the Engine in an unstable state. Includes E_USER_ERROR [256], and E_RECOVERABLE_ERROR [4096].';
	        gaBuildData($error);
	        nebula_print_error($error);
	        exit(1);
	        break;
	    case E_WARNING: //(2) Run-time warnings (non-fatal errors). Execution of the script is not halted.
	    case E_CORE_WARNING: //(32) Warnings (non-fatal errors) that occur during PHP's initial startup. This is like an E_WARNING, except it is generated by the core of PHP. [Not supported by set_error_handler()]
	    case E_COMPILE_WARNING: //(218) Compile-time warnings (non-fatal errors). This is like an E_WARNING, except it is generated by the Zend Scripting Engine. [Not supported by set_error_handler()]
	    case E_USER_WARNING: //(256) User-generated error message. This is like an E_ERROR, except it is generated in PHP code by using the PHP function trigger_error().
	        $error['type'] = 'Warning';
	        $error['definition'] = 'Run-time warnings (non-fatal errors). Execution of the script is not halted. Includes E_WARNING [2], E_CORE_WARNING [32], E_COMPILE_WARNING [218], and E_USER_WARNING [256].';
	        //gaBuildData($error); //Disabled GA event tracking of Warnings (Maybe we should send to a log file instead)
	        if ( $GLOBALS["debug"] ) {
	        	nebula_print_error($error);
	        }
	        break;
	    case E_NOTICE: //(8) Run-time notices. Indicate that the script encountered something that could indicate an error, but could also happen in the normal course of running a script.
	    case E_USER_NOTICE: //(1024) User-generated notice message. This is like an E_NOTICE, except it is generated in PHP code by using the PHP function trigger_error().
	    case E_DEPRECATED: //(8192) Run
	    case E_USER_DEPRECATED: //(16384) 
	        $error['type'] = 'Notice';
	        $error['definition'] = 'Run-time notices. Indicate that the script encountered something that could indicate an error, but could also happen in the normal course of running a script. Includes E_NOTICE [8], E_USER_NOTICE [1024], E_DEPRECATED [8192], and E_USER_DEPRECATED [16384].';
	        //gaBuildData($error); //Disabled GA event tracking of Notices (Maybe we should send to a log file instead)
	        if ( $GLOBALS["debug"] ) {
	        	nebula_print_error($error);
	        }
	        break;
		case E_STRICT: //(2048) Enable to have PHP suggest changes to your code which will ensure the best interoperability and forward compatibility of your code.
			$error['type'] = 'Strict';
			$error['definition'] = 'Suggested changes which will ensure the best interoperability and forward compatibility. Includes E_STRICT [2048].';
	        //gaBuildData($error); //Disabled GA event tracking of Notices (Maybe we should send to a log file instead)
	        if ( $GLOBALS["debug"] ) {
	        	nebula_print_error($error);
	        }
	        break;
	    default:
	        nebula_print_error($error);
	        gaBuildData($error);
	        break;
    }
	
    return true; //Don't execute PHP internal error handler
}

function nebula_print_error($error) {
	echo '<p class="nebula-php-error ' . strtolower(str_replace(' ', '-', $error['type'])) . '"><small title="' . $error['definition'] . '">[' . $error['level'] . ']</small> <strong title="' . $error['definition'] . '">' . $error['type'] . '</strong>: ' . $error['message'] . ' in <strong>' . $error['file'] . '</strong> on <strong>line ' . $error['line'] . '</strong>.</p>';
}

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
		'exf' => 0 //Fatal Exception? (Boolean) //@TODO: Pull this data from the $error array (if 'type' contains 'fatal'). Doesn't matter until the handler supports fatal errors.
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



/*========================== 
 Nebula Settings
 ===========================*/
//Include Nebula Settings page
if ( is_admin() ) {
	include_once('includes/nebula-settings.php');	
}

//Uncomment to force override the Nebula Settings. This will disable changes made from the Settings page, and only allow edits from this functions file (to revert, comment out and choose "Enabled" in the Nebula Settings page):
/* update_option('nebula_overall', 'override'); */

//Store global strings as needed
add_action('init', 'global_nebula_vars');
add_action('admin_init', 'global_nebula_vars');
function global_nebula_vars(){
    $GLOBALS['admin_user'] = get_userdata(1);
    $GLOBALS['full_address'] = get_option('nebula_street_address') . ', ' . get_option('nebula_locality') . ', ' . get_option('nebula_region') . ' ' . get_option('nebula_postal_code');
    $GLOBALS['enc_address'] = get_option('nebula_street_address') . '+' . get_option('nebula_locality') . '+' . get_option('nebula_region') . '+' . get_option('nebula_postal_code');
    $GLOBALS['enc_address'] = str_replace(' ', '+', $GLOBALS['enc_address']);
}

//Determine if a function should be used based on several Nebula Settings conditions (for text inputs).
function nebula_settings_conditional_text($setting, $default = ''){
	if ( get_option('nebula_overall') == 'enabled' && get_option($setting) ) {
		return get_option($setting);
	} else {
		return $default;
	}
}

//Determine if a function should be used based on several Nebula Settings conditions (for text inputs).
function nebula_settings_conditional_text_bool($setting, $true = true, $false = false){
	if ( get_option('nebula_overall') == 'enabled' && get_option($setting) ) {
		return $true;
	} else {
		return $false;
	}
}

//Determine if a function should be used based on several Nebula Settings conditions (for select inputs).
function nebula_settings_conditional($setting, $default='enabled') {
	if ( get_option('nebula_overall') == 'override' || get_option('nebula_overall') == 'disabled' || (get_option('nebula_overall') == 'enabled' && get_option($setting) == 'default') || (get_option('nebula_overall') == 'enabled' && get_option($setting) == $default) ) {
		return 1;
	} else {
		return 0;
	}
}


/*==========================
 
 Wordpress Automations
 
 ===========================*/

//Used to detect if plugins are active. Enabled use of is_plugin_active($plugin)
require_once(ABSPATH . 'wp-admin/includes/plugin.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');

//Detect and prompt install of Recommended and Optional plugins
require_once dirname(__FILE__) . '/includes/class-tgm-plugin-activation.php';

add_action('tgmpa_register', 'my_theme_register_required_plugins');
function my_theme_register_required_plugins() {
    $plugins = array(
        array(
            'name'      => 'Admin Menu Tree Page View',
            'slug'      => 'admin-menu-tree-page-view',
            'required'  => true,
        ),
        array(
            'name'      => 'Custom Post Type UI',
            'slug'      => 'custom-post-type-ui',
            'required'  => false,
        ),
        array(
            'name'      => 'Contact Form 7',
            'slug'      => 'contact-form-7',
            'required'  => true,
        ),
        array(
            'name'      => 'Contact Form DB',
            'slug'      => 'contact-form-7-to-database-extension',
            'required'  => true,
        ),
        array(
            'name'      => 'Advanced Custom Fields',
            'slug'      => 'advanced-custom-fields',
            'required'  => false,
        ),
        array(
            'name'      => 'Regenerate Thumbnails',
            'slug'      => 'regenerate-thumbnails',
            'required'  => false,
        ),
        array(
            'name'      => 'W3 Total Cache',
            'slug'      => 'w3-total-cache',
            'required'  => false,
        ),
        array(
            'name'      => 'WP-PageNavi',
            'slug'      => 'wp-pagenavi',
            'required'  => true,
        ),
        array(
            'name'      => 'WP Smush.it',
            'slug'      => 'wp-smushit',
            'required'  => false,
        ),
        array(
            'name'      => 'Custom Facebook Feed',
            'slug'      => 'custom-facebook-feed',
            'required'  => false,
        ),
        array(
            'name'      => 'Really Simple CAPTCHA',
            'slug'      => 'really-simple-captcha',
            'required'  => false,
        ),
        array(
            'name'      => 'Ultimate TinyMCE',
            'slug'      => 'ultimate-tinymce',
            'required'  => false,
        ),
        array(
            'name'      => 'WP Mail SMTP',
            'slug'      => 'wp-mail-smtp',
            'required'  => false,
        ),
        array(
            'name'      => 'WooCommerce',
            'slug'      => 'woocommerce',
            'required'  => false,
        ),
        array(
            'name'      => 'Wordpress SEO by Yoast',
            'slug'      => 'wordpress-seo',
            'required'  => false,
        ),
        array(
            'name'      => 'Search Everything',
            'slug'      => 'search-everything',
            'required'  => false,
        ),
    );

    $config = array(
        'id'           => 'tgmpa',                 //Unique ID for hashing notices for multiple instances of TGMPA.
        'default_path' => '',                      //Default absolute path to pre-packaged plugins.
        'menu'         => 'tgmpa-install-plugins', //Menu slug.
        'has_notices'  => true,                    //Show admin notices or not.
        'dismissable'  => true,                    //If false, a user cannot dismiss the nag message.
        'dismiss_msg'  => '',                      //If 'dismissable' is false, this message will be output at top of nag.
        'is_automatic' => false,                   //Automatically activate plugins after installation or not.
        'message'      => '',                      //Message to output right before the plugins table.
        'strings'      => array(
            'page_title'                      => __( 'Install Recommended Plugins', 'tgmpa' ),
            'menu_title'                      => __( 'Install Plugins', 'tgmpa' ),
            'installing'                      => __( 'Installing Plugin: %s', 'tgmpa' ), // %s = plugin name.
            'oops'                            => __( 'Something went wrong with the plugin API.', 'tgmpa' ),
            'notice_can_install_required'     => _n_noop( 'WP Nebula recommends the following plugin: %1$s.', 'WP Nebula recommends the following plugins: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_can_install_recommended'  => _n_noop( 'The following optional plugin can be installed: %1$s.', 'The following optional plugins can be installed: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_cannot_install'           => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_can_activate_required'    => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_can_activate_recommended' => _n_noop( 'The following optional plugin is currently inactive: %1$s.', 'The following optinal plugins are currently inactive: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_cannot_activate'          => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_ask_to_update'            => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with WP Nebula: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with WP Nebula: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_cannot_update'            => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.', 'tgmpa' ), // %1$s = plugin name(s).
            'install_link'                    => _n_noop( 'Begin installing plugin', 'Begin installing plugins', 'tgmpa' ),
            'activate_link'                   => _n_noop( 'Begin activating plugin', 'Begin activating plugins', 'tgmpa' ),
            'return'                          => __( 'Return to Required Plugins Installer', 'tgmpa' ),
            'plugin_activated'                => __( 'Plugin activated successfully.', 'tgmpa' ),
            'complete'                        => __( 'All plugins installed and activated successfully. %s', 'tgmpa' ), // %s = dashboard link.
            'nag_type'                        => 'updated' // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
        )
    );

    tgmpa($plugins, $config);
	
	/* 
		Until there is support for Required, Recommended, AND Optional plugins:
		When updating the class file (in the /includes directory, be sure to edit the text on the following line to be 'Recommended' and 'Optional' in the installation table.
		$table_data[$i]['type'] = isset( $plugin['required'] ) && $plugin['required'] ? __( 'Recommended', 'tgmpa' ) : __( 'Optional', 'tgmpa' );
	*/
}

//When WP Nebula has been activated
add_action('after_switch_theme', 'nebulaActivation');
function nebulaActivation() {
	$theme = wp_get_theme();
	//Check if this is the initial activation, or if initialization has been ran before (and the user is just toggling themes)
	if ( $theme['Name'] == 'WP Nebula' && (get_post_meta(1, '_wp_page_template', 1) != 'tpl-homepage.php' || isset($_GET['nebula-reset'])) ) {

		//Create Homepage
		$nebula_home = array(
			'ID' => 1,
			'post_type' => 'page',
			'post_title' => 'Home',
			'post_name' => 'home',
			'post_content'   => "The WP Nebula is a springboard Wordpress theme for developers. Inspired by the HTML5 Boilerplate, this theme creates the framework for development. Like other Wordpress startup themes, it has custom functionality built-in (like shortcodes, styles, and JS/PHP functions), but unlike other themes the WP Nebula is not meant for the end-user.

Wordpress developers will find all source code not obfuscated, so everything may be customized and altered to fit the needs of the project. Additional comments have been added to help explain what is happening; not only is this framework great for speedy development, but it is also useful for learning advanced Wordpress techniques.",
			'post_status' => 'publish',
			'post_author' => 1,
			'page_template' => 'tpl-homepage.php'
		);
		
		//Insert the post into the database
		wp_insert_post($nebula_home);
		
		//Show the Activation Complete message
		add_action('admin_notices', 'nebulaActivateComplete');
		
		//Change some Wordpress settings
		add_action('init', 'nebulaWordpressSettings');
	
	}
	return;
}

//When Nebula "Reset" has been clicked
if ( current_user_can('manage_options') && isset($_GET['nebula-reset']) ) {
	nebulaActivation();
	nebulaChangeHomeSetting();
	add_action('init', 'nebulaWordpressSettings');
}

//If WP Nebula has been activated and other actions have heppened, but the user is still on the Themes settings page.
if ( current_user_can('manage_options') && isset($_GET['activated'] ) && $pagenow == 'themes.php' ) {
	$theme = wp_get_theme();
	if ( $theme['Name'] == 'WP Nebula' ) {
		add_action('admin_notices', 'nebulaActivateComplete');
	}
}

//Set the front page to static > Home.
function nebulaChangeHomeSetting(){
	$nebula_homepage = get_page_by_title('Home');
	update_option('page_on_front', $nebula_homepage->ID); //Or set this to ...(..., '1');
	update_option('show_on_front', 'page');
}

//Nebula preferred default Wordpress settings
function nebulaWordpressSettings() {
	global $wp_rewrite;
	
	remove_core_bundled_plugins();
	
	//Update Nebula Settings //@TODO: ADD THE REST!
	update_option('nebula_ga_tracking_id', '');
	update_option('nebula_admin_bar', 'disabled');
	
	//Empty the site tagline
	update_option('blogdescription', '');
	
	//Change Timezone
	update_option('timezone_string', 'America/New_York');
	
	//Start of the week to Sunday
	update_option('start_of_week', 0);

	//Set the permalink structure to be "pretty" style
	update_option('permalink_structure', '/%postname%/');
	$wp_rewrite->flush_rules();
}

add_action('admin_init', 'remove_core_bundled_plugins');
function remove_core_bundled_plugins(){
	//Remove Hello Dolly plugin if it exists
	if ( file_exists(WP_PLUGIN_DIR . '/hello.php') ) {
        delete_plugins(array('hello.php'));
    }
}

function nebulaActivateComplete(){
	if ( isset($_GET['nebula-reset']) ) {
		echo "<div id='nebula-activate-success' class='updated'><p><strong>WP Nebula has been reset!</strong><br/>You have reset WP Nebula. Settings have been updated! The Home page has been updated. It has been set as the static frontpage in <a href='options-reading.php'>Settings > Reading</a>.</p></div>";
	} elseif ( get_post_meta(1, '_wp_page_template', 1) == 'tpl-homepage.php' ) {
		echo "<div id='nebula-activate-success' class='updated'><p><strong>WP Nebula has been re-activated!</strong><br/>Settings have <strong>not</strong> been changed. The Home page already exists, so it has <strong>not</strong> been updated. Make sure it is set as the static front page in <a href='options-reading.php'>Settings > Reading</a>. <a href='themes.php?activated=true&nebula-reset=true' style='float: right; color: red;'>Re-initialize Nebula.</a></p></div>";
	} else {
		echo "<div id='nebula-activate-success' class='updated'><p><strong>WP Nebula has been activated!</strong><br/>Permalink structure has been updated. A new Home page has been created. It has been set as the static frontpage in <a href='options-reading.php'>Settings > Reading</a>.</p></div>";
	}
}


/*==========================
 
 Custom WP Admin Functions
 
 ===========================*/

//Disable auto curly quotes
remove_filter('the_content', 'wptexturize');
remove_filter('the_excerpt', 'wptexturize');
remove_filter('comment_text', 'wptexturize');


//Disable Admin Bar (and WP Update Notifications) for everyone but administrators (or specific users)
if ( nebula_settings_conditional('nebula_admin_bar', 'disabled') ) {
	add_action('init', 'admin_only_features');
	function admin_only_features() {
		remove_action('wp_footer', 'wp_admin_bar_render', 1000); //For the front-end
			
		//CSS override for the frontend
		add_filter('wp_head','remove_admin_bar_style_frontend', 99);
		function remove_admin_bar_style_frontend() {
			echo '<style type="text/css" media="screen">
			html { margin-top: 0px !important; }
			* html body { margin-top: 0px !important; }
			</style>';
		}
	}
}

//Disable Wordpress Core update notifications in WP Admin
if ( nebula_settings_conditional('nebula_wp_core_updates_notify') ) {
	add_filter('pre_site_transient_update_core', create_function('$a', "return null;"));
}

//Show update warning on Wordpress Core/Plugin update admin pages
if ( nebula_settings_conditional('nebula_phg_plugin_update_warning') ) {
	$filename = basename($_SERVER['REQUEST_URI']);
	if ( $filename == 'plugins.php' ) {
		add_action('admin_notices','plugin_warning');
		function plugin_warning(){
			echo "<div id='pluginwarning' class='error'><p><strong>WARNING:</strong> Updating plugins may cause irreversible errors to your website!</p><p>Contact <a href='http://www.pinckneyhugo.com'>Pinckney Hugo Group</a> if a plugin needs to be updated: " . nebula_tel_link('13154786700') . "</p></div>";
		}
	} elseif ( $filename == 'update-core.php') {
		add_action('admin_notices','plugin_warning');
		function plugin_warning(){
			echo "<div id='pluginwarning' class='error'><p><strong>WARNING:</strong> Updating Wordpress core or plugins may cause irreversible errors to your website!</p><p>Contact <a href='http://www.pinckneyhugo.com'>Pinckney Hugo Group</a> if a plugin needs to be updated: " . nebula_tel_link('13154786700') . "</p></div>";
		}
	}
} else {
	add_action('admin_head', 'warning_style_unset');
	function warning_style_unset(){
		echo '<style>.update-nag a, .update-core-php input#upgrade, .update-core-php input#upgrade-plugins, .update-core-php input#upgrade-plugins-2, .plugins-php .update-message a, .plugins-php .deactivate a {cursor: pointer !important;}</style>';
	}
}

//Control session time (for the "Remember Me" checkbox)
add_filter('auth_cookie_expiration', 'nebula_session_expire');
function nebula_session_expire($expirein) {
    return 2592000; //30 days (Default is 1209600 (14 days)
}

//Disable the logged-in monitoring modal
remove_action('admin_enqueue_scripts', 'wp_auth_check_load');

//Custom login screen
add_action('login_head', 'custom_login_css');
function custom_login_css() {
	//Only use BG image and animation on direct requests (disable for iframe logins after session timeouts).
	if( empty($_POST['signed_request']) ) {
	    echo '<script>window.userIP = "' . $_SERVER["REMOTE_ADDR"] . '";</script>';
	    echo "<script>(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');ga('create', '" . $GLOBALS['ga'] . "', 'auto');</script>";
	}
}


//Change link of logo to live site
add_filter('login_headerurl', 'custom_login_header_url');
function custom_login_header_url() {
    return home_url('/');
}


//Change alt of image
add_filter('login_headertitle', 'new_wp_login_title');
function new_wp_login_title() {
    return get_option('blogname');
}


//Welcome Panel
if ( nebula_settings_conditional('nebula_phg_welcome_panel') ) {
	remove_action('welcome_panel','wp_welcome_panel');
	add_action('welcome_panel','nebula_welcome_panel');
	function nebula_welcome_panel() {
		include('includes/welcome.php');
	}
} else {
	remove_action('welcome_panel','wp_welcome_panel');
}


//Remove unnecessary Dashboard metaboxes
if ( nebula_settings_conditional('nebula_unnecessary_metaboxes') ) {
	add_action('wp_dashboard_setup', 'remove_dashboard_metaboxes');
	function remove_dashboard_metaboxes() {
	    remove_meta_box('dashboard_primary', 'dashboard', 'side');
	    remove_meta_box('dashboard_secondary', 'dashboard', 'side');
	    remove_meta_box('dashboard_plugins', 'dashboard', 'normal');
	    remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');
	}
}


//Custom PHG Metabox
//If user's email address ends in @pinckneyhugo.com and is an administrator
if ( nebula_settings_conditional('nebula_phg_metabox') ) {
	$current_user = wp_get_current_user();
	list($current_user_email, $current_user_domain) = explode('@', $current_user->user_email); //@TODO: Undefined offset: 1   ...?
	if ( $current_user_domain == 'pinckneyhugo.com' && current_user_can('manage_options') ) {
		add_action('wp_dashboard_setup', 'phg_dev_metabox');
	}
	function phg_dev_metabox() {
		global $wp_meta_boxes;
		wp_add_dashboard_widget('custom_help_widget', 'PHG Developer Info', 'custom_dashboard_help');
	}
	function custom_dashboard_help() {
		//Get last modified filename and date
		$dir = glob_r( get_template_directory() . '/*');
		$last_date = 0;
		foreach($dir as $file) {
			if( is_file($file) ) {
				$mod_date = filemtime($file);
				if ( $mod_date > $last_date ) {
					$last_date = $mod_date;
					$last_filename = basename($file);
				}
			}
		}
		$nebula_size = foldersize(get_template_directory());
		$upload_dir = wp_upload_dir();
		$uploads_size = foldersize($upload_dir['basedir']);
		
		$secureServer = '';
		if ( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ) {
			$secureServer = '<small><i class="fa fa-lock fa-fw"></i>Secured Connection</small>';
		}
		
		function top_domain_name($url){
			$alldomains = explode(".", $url);
			return $alldomains[count($alldomains)-2] . "." . $alldomains[count($alldomains)-1];
		}
		
		$dnsrecord = dns_get_record(top_domain_name(gethostname()), DNS_NS);
		
		echo '<div id="testloadcon" style="pointer-events: none; opacity: 0; visibility: hidden; display: none;"></div>';
		echo '<script id="testloadscript">
				jQuery(window).on("load", function(){
					jQuery(".loadtime").css("visibility", "visible");
					beforeLoad = (new Date()).getTime();
					var iframe = document.createElement("iframe");
					iframe.style.width = "1200px";
					iframe.style.height = "0px";
					jQuery("#testloadcon").append(iframe);
					iframe.src = "' . home_url('/') . '";
					jQuery("#testloadcon iframe").on("load", function(){
						stopTimer();
					});
				});
				
				function stopTimer(){
				    var afterLoad = (new Date()).getTime();
				    var result = (afterLoad - beforeLoad)/1000;
				    jQuery(".loadtime").html(result + " seconds");
				    if ( result > 5 ) { jQuery(".slowicon").addClass("fa-warning"); }
				    jQuery(".serverdetections .fa-spin, #testloadcon, #testloadscript").remove();
				}
				</script>';
				
		echo '<ul class="serverdetections">';
			echo '<li><i class="fa fa-info-circle fa-fw"></i> Domain: <strong>' . $_SERVER['SERVER_NAME'] . '</strong></li>';
			echo '<li><i class="fa fa-hdd-o fa-fw"></i> Hostname: <strong>' . top_domain_name(gethostname()) . '</strong> <small>(' . top_domain_name($dnsrecord[0]['target']) . ')</small></li>';
			echo '<li><i class="fa fa-upload fa-fw"></i> Server IP: <strong><a href="http://whatismyipaddress.com/ip/' . $_SERVER['SERVER_ADDR'] . '" target="_blank">' . $_SERVER['SERVER_ADDR'] . '</a></strong> ' . $secureServer . ' <small>(' . $_SERVER['SERVER_SOFTWARE'] . ')</small></li>';
			echo '<li><i class="fa fa-gavel fa-fw"></i> PHP Version: <strong>' . phpversion() . '</strong></li>';
			echo '<li><i class="fa fa-database fa-fw"></i> MySQL Version: <strong>' . mysql_get_server_info() . '</strong></li>';
			echo '<li><i class="fa fa-code"></i> Theme directory size: <strong>' . round($nebula_size/1048576, 2) . 'mb</strong> </li>';
			echo '<li><i class="fa fa-picture-o"></i> Uploads directory size: <strong>' . round($uploads_size/1048576, 2) . 'mb</strong> </li>';
			echo '<li><i class="fa fa-clock-o fa-fw"></i> Homepage load time: <a href="http://developers.google.com/speed/pagespeed/insights/?url=' . home_url('/') . '" target="_blank"><strong class="loadtime" style="visibility: hidden;"><i class="fa fa-spinner fa-fw fa-spin"></i></strong></a> <i class="slowicon fa" style="color: maroon;"></i></li>';
			echo '<li><i class="fa fa-calendar-o fa-fw"></i> Initial Install: <strong>' . date("F j, Y", getlastmod()) . '</strong> <small>(Estimate)</small></li>'; //@TODO: Might just be the last WP update date
			echo '<li><i class="fa fa-calendar fa-fw"></i> Last modified: <strong>' . date("F j, Y", $last_date) . '</strong> <small>@</small> <strong>' . date("g:ia", $last_date) . '</strong> <small>(' . $last_filename . ')</small></li>';
		echo '</ul>';
	}
}


//Only allow admins to modify Contact Forms //@TODO: Currently does not work because these constants are already defined!
//define('WPCF7_ADMIN_READ_CAPABILITY', 'manage_options');
//define('WPCF7_ADMIN_READ_WRITE_CAPABILITY', 'manage_options');


//Change default values for the upload media box
//These can also be changed by navigating to .../wp-admin/options.php
add_action('after_setup_theme', 'custom_media_display_settings');
function custom_media_display_settings() {
	//update_option('image_default_align', 'center');
	update_option('image_default_link_type', 'none');
	//update_option('image_default_size', 'large');
}


//Add ID column on post listings
add_filter('manage_edit-post_columns', 'custom_set_posts_columns');
function custom_set_posts_columns($columns) {
	return array(
		'cb' => '<input type=”checkbox” />',
		'title' => 'Title',
		'author' => 'Author',
		'date' => 'Date',
		'id' => 'ID',
	);
}
add_action('manage_posts_custom_column', 'custom_set_posts_columns_value', 10, 2);
function custom_set_posts_columns_value($column, $post_id) {
	if ($column == 'id'){
		echo $post_id;
	}
}


//Duplicate post
add_action( 'admin_action_duplicate_post_as_draft', 'duplicate_post_as_draft' );
function duplicate_post_as_draft(){
	global $wpdb;
	if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'duplicate_post_as_draft' == $_REQUEST['action'] ) ) ) {
		wp_die('No post to duplicate has been supplied!');
	}
 
	//Get the original post id
	$post_id = (isset($_GET['post']) ? $_GET['post'] : $_POST['post']);
	//Get all the original post data
	$post = get_post( $post_id );
 
	//Set post author (default by current user). For original author change to: $new_post_author = $post->post_author;
	$current_user = wp_get_current_user();
	$new_post_author = $current_user->ID;
 
	//If post data exists, create the post duplicate
	if (isset( $post ) && $post != null) {
		//New post data array
		$args = array(
			'comment_status' => $post->comment_status,
			'ping_status'    => $post->ping_status,
			'post_author'    => $new_post_author,
			'post_content'   => $post->post_content,
			'post_excerpt'   => $post->post_excerpt,
			'post_name'      => $post->post_name,
			'post_parent'    => $post->post_parent,
			'post_password'  => $post->post_password,
			'post_status'    => 'draft',
			'post_title'     => $post->post_title . ' copy',
			'post_type'      => $post->post_type,
			'to_ping'        => $post->to_ping,
			'menu_order'     => $post->menu_order
		);
 
		//Insert the post by wp_insert_post() function
		$new_post_id = wp_insert_post( $args );
 
		//Get all current post terms ad set them to the new post draft
		$taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
		foreach ($taxonomies as $taxonomy) {
			$post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
			wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
		}
 
		//Duplicate all post meta
		$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
		if (count($post_meta_infos)!=0) {
			$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
			foreach ($post_meta_infos as $meta_info) {
				$meta_key = $meta_info->meta_key;
				$meta_value = addslashes($meta_info->meta_value);
				$sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
			}
			$sql_query.= implode(" UNION ALL ", $sql_query_sel);
			$wpdb->query($sql_query);
		}
 
		//Redirect to the edit post screen for the new draft
		wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
		exit;
	} else {
		wp_die('Post creation failed, could not find original post: ' . $post_id);
	}
}
 
//Add the duplicate link to action list for post_row_actions (This works for custom post types too).
//Additional post types with the following: add_filter('{post type name}_row_actions', 'rd_duplicate_post_link', 10, 2);
add_filter( 'post_row_actions', 'rd_duplicate_post_link', 10, 2 );
add_filter('page_row_actions', 'rd_duplicate_post_link', 10, 2);
function rd_duplicate_post_link( $actions, $post ) {
	if (current_user_can('edit_posts')) {
		$actions['duplicate'] = '<a href="admin.php?action=duplicate_post_as_draft&amp;post=' . $post->ID . '" title="Duplicate this item" rel="permalink">Duplicate</a>';
	}
	return $actions;
}


//Show File URL column on Media Library listings
add_filter('manage_media_columns', 'muc_column');
function muc_column( $cols ) {
	$cols["media_url"] = "File URL";
	return $cols;
}
add_action('manage_media_custom_column', 'muc_value', 10, 2);
function muc_value( $column_name, $id ) {
	if ( $column_name == "media_url" ) {
		echo '<input type="text" width="100%" value="' . wp_get_attachment_url( $id ) . '" readonly />';
		//echo '<input type="text" width="100%" onclick="jQuery(this).select();" value="'. wp_get_attachment_url( $id ). '" readonly />'; //This selects the text on click
	}
}


//Enable editor-style.css for the WYSIWYG editor.
add_editor_style('css/editor-style.css');


//Clear caches when plugins are activated if W3 Total Cache is active
add_action('admin_init', 'clear_all_w3_caches');
function clear_all_w3_caches(){
	include_once(ABSPATH . 'wp-admin/includes/plugin.php');
	if ( is_plugin_active('w3-total-cache/w3-total-cache.php') && isset($_GET['activate']) && $_GET['activate'] == 'true' ) {		
		if ( function_exists('w3tc_pgcache_flush') ) {
			w3tc_pgcache_flush();
		}
	}
}


//Admin Footer Enhancements
//Left Side
add_filter('admin_footer_text', 'change_admin_footer_left');
function change_admin_footer_left() {
    return '<a href="http://www.pinckneyhugo.com" style="color: #0098d7; font-size: 14px; padding-left: 23px;"><img src="' . get_bloginfo('template_directory') . '/images/phg/phg-symbol.png" onerror="this.onerror=null; this.src=""' . get_bloginfo('template_directory') . '/images/phg/phg-symbol.png" alt="Pinckney Hugo Group" style="position: absolute; margin-left: -20px; margin-top: 4px; max-width: 18px;"/> Pinckney Hugo Group</a> &bull; <a href="https://www.google.com/maps/dir/Current+Location/760+West+Genesee+Street+Syracuse+NY+13204" target="_blank">760 West Genesee Street, Syracuse, NY 13204</a> &bull; ' . nebula_tel_link('13154786700');
}
//Right Side
add_filter('update_footer', 'change_admin_footer_right', 11);
function change_admin_footer_right() {
    return 'WP Version: <strong>' . get_bloginfo('version') . '</strong> | Server IP: <strong>' . $_SERVER['SERVER_ADDR'] . '</strong>';
}




/*==========================
 
 Custom User Fields 
 
 ===========================*/

wp_enqueue_script('thickbox');
wp_enqueue_style('thickbox');
wp_enqueue_script('media-upload');
wp_enqueue_script('easy-author-image-uploader');

add_action('admin_init', 'easy_author_image_init');
function easy_author_image_init() {
	global $pagenow;
	if ( $pagenow == 'media-upload.php' || $pagenow == 'async-upload.php' ) {
		add_filter('gettext', 'q_replace_thickbox_button_text', 1, 3); //Replace the button text for the uploader
	}
}
function q_replace_thickbox_button_text($translated_text, $text, $domain) {
	if ( $text == 'Insert into Post' ) {
		$referer = strpos(wp_get_referer(), 'profile');
		if ( $referer != '' ) {
			return 'Choose this photo.';
		}
	}
	return $translated_text;
}

//Show the fields in the user admin page
if ( !user_can($current_user, 'subscriber') && !user_can($current_user, 'contributor') ) {
	add_action('show_user_profile', 'extra_profile_fields');
	add_action('edit_user_profile', 'extra_profile_fields');
}
function extra_profile_fields($user) { ?>
	<h3>Additional Information</h3>
	<table class="form-table">
		<tr class="headshot_button_con">
			<th>
				<label for="headshot_button"><span class="description">Headshot</span></label>
			</th>
			<?php $buttontext = ""; if( get_the_author_meta('headshot_url', $user->ID) ) {
				$buttontext = "Change headshot";  } else { $buttontext = "Upload new headshot";
			} ?>
			<td>
				<input id="headshot_button" type="button" class="button" value="<?php echo $buttontext; ?>" />
				<?php if ( get_the_author_meta('headshot_url', $user->ID) ) : ?>
					<input id="headshot_remove" type="button" class="button" value="Remove headshot" />
				<?php endif; ?>
				<br/><span class="description">Please select "Full Size" when choosing the headshot.</span>
			</td>
		</tr>
		<tr>
			<th>
				<label for="headshot_preview"><span class="description">Preview</span></label>
			</th>
			<td>
				<?php if ( get_the_author_meta('headshot_url', $user->ID) ) : ?>
					<div id="headshot_preview" style="min-height: 100px; max-width: 150px;">
						<img style="max-width:100%; border-radius: 100px; border: 5px solid #fff; box-shadow: 0px 0px 8px 0 rgba(0,0,0,0.2);" src="<?php echo esc_attr(get_the_author_meta('headshot_url', $user->ID)); ?>" />
					</div>					
				<?php else : ?>
					<div id="headshot_preview" style="height: 100px; width:100px; line-height:100px; border:2px solid #CCC; text-align:center; font-size:5em;">?</div>					
				<?php endif; ?>
				<span id="upload_success" style="display:block;"></span>
				
				<input type="hidden" name="headshot_url" id="headshot_url" value="<?php echo esc_attr(get_the_author_meta('headshot_url', $user->ID)); ?>" class="regular-text" />
			</td>
		</tr>
		<tr>
			<th><label for="jobtitle">Job Title</label></th>
			<td>
				<input type="text" name="jobtitle" id="jobtitle" value="<?php echo esc_attr(get_the_author_meta( 'jobtitle', $user->ID)); ?>" class="regular-text" /><br />
				<span class="description">&nbsp;</span>
			</td>
		</tr>
		<tr>
			<th><label for="phoneextension">Phone Number</label></th>
			<td>
				<input type="text" name="phonenumber" id="phonenumber" value="<?php echo esc_attr(get_the_author_meta( 'phonenumber', $user->ID)); ?>" class="regular-text" /><br />
				<span class="description">&nbsp;</span>
			</td>
		</tr>
	</table>
<?php }

//Save the field values to the DB
add_action('personal_options_update', 'save_extra_profile_fields');
add_action('edit_user_profile_update', 'save_extra_profile_fields');
function save_extra_profile_fields($user_id) {
	if ( !current_user_can('edit_user', $user_id) ) {
		return false;
	}
	update_usermeta($user_id, 'headshot', $_POST['headshot']);
	update_usermeta($user_id, 'headshot_url', $_POST['headshot_url']);
	update_usermeta($user_id, 'jobtitle', $_POST['jobtitle']);
	update_usermeta($user_id, 'phonenumber', $_POST['phonenumber']);
}


function nebula_facebook_link() {
	echo '<p class="facebook-connect-con"><i class="fa fa-facebook-square"></i> <a class="facebook-connect" href="#">Connect with Facebook</a></p>';
}


//Template for comments and pingbacks.
//@TODO: Add functionality from this into nebula_comment_theme, then update templates to no longer use this.
//Used as a callback by wp_list_comments() for displaying the comments.
function boilerplate_comment($comment, $args, $depth) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case '' :
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<article id="comment-<?php comment_ID(); ?>">
			<div class="comment-author vcard">
				<?php echo get_avatar($comment, 40); ?>
				<?php printf( '%s <span class="says">says:</span>', sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?>
			</div><!-- .comment-author .vcard -->
			<?php if ( $comment->comment_approved == '0' ) : ?>
				<em>Your comment is awaiting moderation.</em>
				<br />
			<?php endif; ?>
			<footer class="comment-meta commentmetadata"><a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
				<?php
					/* translators: 1: date, 2: time */
					printf( '%1$s at %2$s', get_comment_date(),  get_comment_time() ); ?></a><?php edit_comment_link('(Edit)', ' ');
				?>
			</footer><!-- .comment-meta .commentmetadata -->
			<div class="comment-body"><?php comment_text(); ?></div>
			<div class="reply">
				<?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
			</div><!-- .reply -->
		</article><!-- #comment-##  -->
	<?php
			break;
		case 'pingback'  :
		case 'trackback' :
	?>
	<li class="post pingback">
		<p>Pingback: <?php comment_author_link(); ?><?php edit_comment_link('(Edit)', ' '); ?></p>
	<?php
			break;
	endswitch;
}

function nebula_comment_theme($comment, $args, $depth) {
	$GLOBALS['comment'] = $comment;
	extract($args, EXTR_SKIP);
	
	if ( $args['style'] == 'div' ) {
		$tag = 'div';
		$add_below = 'comment';
	} else {
		$tag = 'li';
		$add_below = 'div-comment';
	}
	?>
	
	<<?php echo $tag; ?> <?php comment_class(empty($args['has_children']) ? '' : 'parent'); ?> id="comment-<?php comment_ID(); ?>">
	
		<div id="div-comment-<?php comment_ID(); ?>" class="comment-body">
			
			<div class="user-avatar">
				<?php
					$comment_id = get_comment_ID();
					$comment_author_id = get_comment($comment_id)->user_id;
					$comment_author_info = get_userdata($comment_author_id);
					if ( $comment_author_info ) {
						$comment_headshot = str_replace('.jpg', '-150x150.jpg' , $comment_author_info->headshot_url);
					}
				?>
				
				<?php if ( $comment_author_info ) : ?>
					<?php if ( $comment_author_id != 0 ) : ?><a href="<?php echo get_author_posts_url($comment_author_id); ?>"><?php endif; ?>
						<img src="<?php echo $comment_headshot; ?>" width="50" height="50" style="border-radius: 25px; border: 2px solid #fff; box-shadow: 0px 0px 6px 0 rgba(0,0,0,0.2);" />
					<?php if ( $comment_author_id != 0 ) : ?></a><?php endif; ?>
				<?php endif; ?>
			
			</div>
			
			<div class="comment-author">
				<cite class="fn">
					<?php if ( $comment_author_id != 0 ) : ?><a href="<?php echo get_author_posts_url($comment_author_id); ?>"><?php endif; ?>
						<strong><?php echo get_comment_author(); ?></strong>
					<?php if ( $comment_author_id != 0 ) : ?></a><?php endif; ?>
				</cite>
			</div>
			
			<?php if ($comment->comment_approved == '0') : //This does not seem to work yet. ?>
				<em class="comment-awaiting-moderation">Your comment is awaiting moderation.</em>
				<br/>
			<?php endif; ?>
			
			<div class="comment-meta commentmetadata"><a href="<?php echo htmlspecialchars(get_comment_link($comment->comment_ID)); ?>">
				<?php printf( 'on %1$s at %2$s', get_comment_date(), get_comment_time()); ?></a> 
				<?php if (current_user_can('edit_post')) : ?>
					<? edit_comment_link('<small><i class="icon-pencil"></i> Edit</small>','  ','' ); ?>
				<?php endif; ?>
			</div>
			
			<?php comment_text(); ?>
				
			<div class="reply">
				<?php 
					$comment_reply_args = array(
						'add_below' => 'comment',
					);
					comment_reply_link(array_merge($comment_reply_args, array('add_below' => $add_below, 'depth' => $depth, 'max_depth' => $args['max_depth'])));
				?>
			</div>
		
		</div><!--/commentbody-->
	
	<?php
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
			//global $wp_admin_bar; //@TODO: NULL
			//$wp_admin_bar->remove_menu('wp-logo');
			//$wp_admin_bar->remove_menu('comments');
			//remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 50); //@TODO: Not working
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


/*==========================
 
 Custom Functions 
 
 ===========================*/

//Set server timezone to match Wordpress
date_default_timezone_set( get_option('timezone_string') );

//Disable Pingbacks to prevent security issues
add_filter('xmlrpc_methods', function($methods) {
   unset( $methods['pingback.ping'] );
   return $methods;
});

//Add bloginfo variable for JavaScript
add_action('admin_head', 'js_bloginfo');
add_action('wp_head', 'js_bloginfo');
function js_bloginfo() {
	$upload_dir = wp_upload_dir();
	echo '<script>bloginfo = [];
	bloginfo["name"] = "' . get_bloginfo("name") . '";
	bloginfo["template_directory"] = "' . get_bloginfo("template_directory") . '";
	bloginfo["stylesheet_url"] = "' . get_bloginfo("stylesheet_url") . '";
	bloginfo["home_url"] = "' . home_url() . '";
	bloginfo["admin_email"] = "' . get_option("admin_email", $GLOBALS['admin_user']->user_email) . '";
	bloginfo["admin-ajax"] = "' . admin_url('admin-ajax.php') . '";
	bloginfo["upload_dir"] = "' . $upload_dir['baseurl'] . '"</script>';
}

//Add user variable for JavaScript
add_action('admin_head', 'js_clientinfo');
add_action('wp_head', 'js_clientinfo');
function js_clientinfo() {
	echo '<script>clientinfo = [];
	clientinfo["remote_addr"] = "' . $_SERVER['REMOTE_ADDR'] . '";</script>';
}

//Pull favicon from the theme folder (First is for Frontend, second is for Admin; default is same for both)
add_action('wp_head', 'theme_favicon');
function theme_favicon() {
	echo '<link rel="Shortcut Icon" type="image/x-icon" href="' . get_bloginfo('template_directory') . '/images/favicon.ico" />';
}
add_action('admin_head', 'admin_favicon');
function admin_favicon() {
	echo '<link rel="Shortcut Icon" type="image/x-icon" href="' . get_bloginfo('template_directory') . '/images/favicon.ico" />';
}


//Remove Wordpress version info from head and feeds
add_filter('the_generator', 'complete_version_removal');
function complete_version_removal() {
	return '';
}


//Allow pages to have excerpts too
add_post_type_support( 'page', 'excerpt' );


//Add thumbnail support
if ( function_exists( 'add_theme_support' ) ) :
	add_theme_support( 'post-thumbnails' );
endif;


//Add new image sizes
add_image_size( 'example', 32, 32, 1 );


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
	register_sidebar( array(
		'name' => 'Primary Widget Area',
		'id' => 'primary-widget-area',
		'description' => 'The primary widget area', 'boilerplate',
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	//Sidebar 2
	register_sidebar( array(
		'name' => 'Secondary Widget Area',
		'id' => 'secondary-widget-area',
		'description' => 'The secondary widget area',
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	//Footer 1
	register_sidebar( array(
		'name' => 'First Footer Widget Area',
		'id' => 'first-footer-widget-area',
		'description' => 'The first footer widget area',
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	//Footer 2
	register_sidebar( array(
		'name' => 'Second Footer Widget Area',
		'id' => 'second-footer-widget-area',
		'description' => 'The second footer widget area',
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	//Footer 3
	register_sidebar( array(
		'name' => 'Third Footer Widget Area',
		'id' => 'third-footer-widget-area',
		'description' => 'The third footer widget area',
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	//Footer 4
	register_sidebar( array(
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
add_filter( 'get_search_form', 'my_search_form' );
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
add_action( 'after_setup_theme', 'nav_menu_locations' );
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
	echo '<ul id="cform7-container" class="cform-disabled">
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
	</ul>';
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
	die();
}


//Show different meta data information about the post. Typically used inside the loop.
//Example: nebula_meta('on', 0); //The 0 in the second parameter here makes the day link to the month archive.
//Example: nebula_meta('by');
function nebula_meta($meta, $day=1) {
	if ( $meta == 'date' || $meta == 'time' || $meta == 'on' || $meta == 'day' || $meta == 'when' ) {
		$the_day = '';
		if ( $day ) {
			$the_day = get_the_date('d') . '/';
		}
		echo '<span class="posted-on"><i class="icon-calendar"></i> <span class="entry-date">' . '<a href="' . home_url() . '/' . get_the_date('Y') . '/' . get_the_date('m') . '/' . '">' . get_the_date('F') . '</a>' . ' ' . '<a href="' . home_url() . '/' . get_the_date('Y') . '/' . get_the_date('m') . '/' . $the_day . '">' . get_the_date('j') . '</a>' . ', ' . '<a href="' . home_url() . '/' . get_the_date('Y') . '/' . '">' . get_the_date('Y') . '</a>' . '</span></span>';
	} elseif ( $meta == 'author' || $meta == 'by' ) {
		echo '<span class="posted-by"><i class="icon-user"></i> <span class="entry-author">' . '<a href="' . get_author_posts_url( get_the_author_meta( 'ID' ) ) . '">' . get_the_author() . '</a></span></span>';
	} elseif ( $meta == 'categories' || $meta == 'category' || $meta == 'cat' || $meta == 'cats' || $meta == 'in' ) {
		if ( is_object_in_taxonomy(get_post_type(), 'category') ) {
			$post_categories = '<span class="posted-in post-categories"><i class="icon-bookmarks"></i> ' . get_the_category_list(', ') . '</span>';
		} else {
			$post_categories = '';
		}
		echo $post_categories;
	} elseif ( $meta == 'tags' || $meta == 'tag' ) {
		$tag_list = get_the_tag_list('', ', ');
		if ( $tag_list ) {
			$post_tags = '<span class="posted-in post-tags"><i class="icon-tag"></i> ' . $tag_list . '</span>';
		} else {
			$post_tags = '';
		}
		echo $post_tags;
	} elseif ( $meta == 'dimensions' || $meta == 'size' || $meta == 'image' || $meta == 'photo' ) {
		if ( wp_attachment_is_image() ) {
			$metadata = wp_get_attachment_metadata();
			echo '<i class="icon-resize-full"></i><a href="' . wp_get_attachment_url() . '" >' . $metadata['width'] . ' &times; ' . $metadata['height'] . '</a>';
		}		
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

//Adds links to the WP admin and to edit the current post as well as shows when the post was edited last and by which author
//Important! This function should be inside of a "if ( current_user_can('manage_options') )" condition so this information isn't shown to the public!
function nebula_manage($data) {
	if ( $data == 'edit' || $data == 'admin' ) {
		echo '<span class="nebula-manage-edit"><span class="post-admin"><i class="icon-tools"></i> <a href="' . get_admin_url() . '" target="_blank">Admin</a></span> <span class="post-edit"><i class="icon-pencil"></i> <a href="' . get_edit_post_link() . '">Edit</a></span></span>';
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
			echo ''; //@TODO: In progress
		}
	}
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


//Word limiter by characters
function word_limit_chars($string, $charlimit, $continue=false){
	// 1 = "Continue Reading", 2 = "Learn More"
	if(strlen(strip_tags($string, '<p><span><a>')) <= $charlimit){
		$newString = strip_tags($string, '<p><span><a>');
	} else{
		$newString = preg_replace('/\s+?(\S+)?$/', '', substr(strip_tags($string, '<p><span><a>'), 0, ($charlimit + 1)));
		if($continue == 1){
			$newString = $newString . '&hellip;' . ' <a class="continuereading" href="'. get_permalink() . '">' . __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'boilerplate' ) . '</a>';
		} elseif($continue == 2){
			$newString = $newString . '&hellip;' . ' <a class="continuereading" href="'. get_permalink() . '">' . __( 'Learn more &raquo;', 'boilerplate' ) . '</a>';
		} else{
			$newString = $newString . '&hellip;';
		}
	}
	return $newString;
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
  $showOnHome = 0; // 1 - show breadcrumbs on the homepage, 0 - don't show
  $delimiter = '<span class="arrow">&rsaquo;</span>'; // delimiter between crumbs
  $home = '<i class="fa fa-home"></i>'; // text for the 'Home' link
  $showCurrent = 1; // 1 - show current post/page title in breadcrumbs, 0 - don't show
  $before = '<span class="current">'; // tag before the current crumb
  $after = '</span>'; // tag after the current crumb
  $dontCapThese = array('the', 'and', 'but', 'of');
  global $post;
  $homeLink = get_bloginfo('url');
  if (is_home() || is_front_page()) {
    if ($showOnHome == 1) echo '<div id="bcrumbs"><nav class="breadcrumbs"><a href="' . $homeLink . '">' . $home . '</a></nav>';
  } else {
    echo '<div id="bcrumbs"><nav class="breadcrumbs"><a href="' . $homeLink . '">' . $home . '</a> ' . $delimiter . ' ';
 	if ( function_exists( 'is_pod_page' ) ) {
	    if(  is_pod_page() ) {
	      // This is a Pod page, so we'll  explode the URI and turn each virtual path into a crumb.
	      $url_parts = explode('/', $_SERVER['REQUEST_URI']);
	      $link;
	      // These are specific to LCS, but the principle is the same.
	      $skipThese = array('detail', 'concentration', 'minor', 'bachelor', 'masters', 'doctoral', 'other', 'certificate');
	      $i = 0;
			foreach ($url_parts as $key => $value) {
				// Pulling off the last one because it won't need a link or a delimiter.
				if ($key != (count($url_parts) - 1)) {
					if($value !='' && !in_array($value, $skipThese)){
						$pieces = explode('-', $value);
						$link_str = '';
						$link = ($i == 0) ? $link : $link  . '/' . $value;
						foreach($pieces as $key => $value){
							if(!in_array($value, $dontCapThese)){
								$link_str .= ucfirst($value) . ' ';
							} else{
								$link_str .= $value . ' ';
							}
						}
						echo '<a href="' . get_bloginfo('url') . $link . '/">' . $link_str . '</a> ' . $delimiter . ' ';
					}
					$i++;
				}
				// Finally we'll strip out the <a> tags
				if($key == (count($url_parts) - 1)) {
					$pieces = explode('-', $value);
					foreach($pieces as $key => $value){
						if(!in_array($value, $dontCapThese)){
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
      if ($thisCat->parent != 0) echo get_category_parents($thisCat->parent, TRUE, ' ' . $delimiter . ' ');
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
        if ($showCurrent == 1) echo ' ' . $delimiter . ' ' . $before . get_the_title() . $after;
      } else {
        $cat = get_the_category(); $cat = $cat[0];
        $cats = get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
        if ($showCurrent == 0) $cats = preg_replace("#^(.+)\s$delimiter\s$#", "$1", $cats);
        echo $cats;
        if ($showCurrent == 1) echo $before . get_the_title() . $after;
      }
    } elseif ( !is_single() && !is_page() && get_post_type() != 'post' && !is_404() ) {
      $post_type = get_post_type_object(get_post_type());
      echo $before . $post_type->labels->singular_name . $after;
    } elseif ( is_attachment() ) {
      echo 'Uploads &raquo; ';
      echo the_title();
    } elseif ( is_page() && !$post->post_parent ) {
      if ($showCurrent == 1) echo $before . get_the_title() . $after;
    } elseif ( is_page() && $post->post_parent ) {
      $parent_id  = $post->post_parent;
      $breadcrumbs = array();
      while ($parent_id) {
        $page = get_page($parent_id);
        $breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
        $parent_id  = $page->post_parent;
      }
      $breadcrumbs = array_reverse($breadcrumbs);
      for ($i = 0; $i < count($breadcrumbs); $i++) {
        echo $breadcrumbs[$i];
        if ($i != count($breadcrumbs)-1) echo ' ' . $delimiter . ' ';
      }
      if ($showCurrent == 1) echo ' ' . $delimiter . ' ' . $before . get_the_title() . $after;
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
      if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ' (';
      echo __('Page') . ' ' . get_query_var('paged');
      if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ')';
    }
    echo '</nav></div>';
  }
} // end the_breadcrumbs()


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
require_once 'includes/Mobile_Detect.php';
$GLOBALS["mobile_detect"] = new Mobile_Detect();

function mobile_classes() {
	$mobile_classes = '';
	if ( $GLOBALS["mobile_detect"]->isMobile() ) {
		$mobile_classes .= '  mobile ';
	} else {
		$mobile_classes .= '  no-mobile ';
	}
	if ( $GLOBALS["mobile_detect"]->isTablet() ) {
		$mobile_classes .= '  tablet ';
	}
	if ( $GLOBALS["mobile_detect"]->isiOS() ) {
		$mobile_classes .= '  ios ';
	}
	if ( $GLOBALS["mobile_detect"]->isAndroidOS() ) {
		$mobile_classes .= '  androidos ';
	}
	echo $mobile_classes;
}


//Add additional body classes including ancestor IDs and directory structures
function page_name_class($classes) {
	global $post;
	$segments = explode('/', trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' ));
	$parents = get_post_ancestors( $post->ID );
	foreach ( $parents as $parent ) {
		$classes[] = 'ancestor-id-' . $parent;
	}
	foreach ( $segments as $segment ) {
		$classes[] = $segment;
	}
	return $classes;
}
add_filter('body_class', 'page_name_class');


//Add category IDs to body/post classes.
//@TODO: Possibly combine this with the above ancestor ID classes
function category_id_class($classes) {
	global $post;
	foreach((get_the_category($post->ID)) as $category)
		$classes [] = 'cat-' . $category->cat_ID . '-id';
		return $classes;
}
add_filter('post_class', 'category_id_class');
add_filter('body_class', 'category_id_class');


function vimeo_meta($videoID) {
	global $vimeo_meta;
	$xml = simplexml_load_string(file_get_contents("http://vimeo.com/api/v2/video/" . $videoID . ".xml")); //@TODO: Will this work on a secure server?
	$vimeo_meta['id'] = $videoID;
	$vimeo_meta['title'] = $xml->video->title;
	$vimeo_meta['safetitle'] = str_replace(" ", "-", $vimeo_meta['title']);
	$vimeo_meta['description'] = $xml->video->description;
	$vimeo_meta['upload_date'] = $xml->video->upload_date;
	$vimeo_meta['thumbnail'] = $xml->video->thumbnail_large;
	$vimeo_meta['url'] = $xml->video->url;
	$vimeo_meta['user'] = $xml->video->user_name;
	$vimeo_meta['seconds'] = strval($xml->video->duration);
	$vimeo_meta['duration'] = intval(gmdate("i", $vimeo_meta['seconds'])) . gmdate(":s", $vimeo_meta['seconds']);
	return $vimeo_meta;
}


function youtube_meta($videoID) {
	global $youtube_meta;
	$xml = simplexml_load_string(file_get_contents("http://gdata.youtube.com/feeds/api/videos/" . $videoID)); //@TODO: Will this work on a secure server?
	$youtube_meta['origin'] = baseDomain();
	$youtube_meta['id'] = $videoID;
	$youtube_meta['title'] = $xml->title;
	$youtube_meta['safetitle'] = str_replace(" ", "-", $youtube_meta['title']);
	$youtube_meta['content'] = $xml->content;
	$youtube_meta['href'] = $xml->link['href'];
	$youtube_meta['author'] = $xml->author->name;
	$temp = $xml->xpath('//yt:duration[@seconds]');
    $youtube_meta['seconds'] = strval($temp[0]->attributes()->seconds);	
	$youtube_meta['duration'] = intval(gmdate("i", $youtube_meta['seconds'])) . gmdate(":s", $youtube_meta['seconds']);
	return $youtube_meta;
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
		//@TODO: Encode $message string here...?
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
		//@TODO: Maybe any numbers after "," "p" ";" or "w" could be added to the human-readable in brackets, like: (315) 555-1346 [323]
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
function hex2rgb( $colour ) {
	if ( $colour[0] == '#' ) {
		$colour = substr( $colour, 1 );
	}
	if ( strlen( $colour ) == 6 ) {
		list( $r, $g, $b ) = array( $colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5] );
	} elseif ( strlen( $colour ) == 3 ) {
		list( $r, $g, $b ) = array( $colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2] );
	} else {
		return false;
	}
	$r = hexdec( $r );
	$g = hexdec( $g );
	$b = hexdec( $b );
	return array( 'red' => $r, 'green' => $g, 'blue' => $b );
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


/*==========================
 
 Custom Shortcodes 
 
 ===========================*/

//Get flags where a parameter is declared in $atts that exists without a declared value
/* Usage:
	$flags = get_flags($atts);
	if (in_array('your_flag', $flags) {
	    // Flag is present
	}
*/
function get_flags($atts) {
	$flags = array();
	if (is_array($atts)) {
		foreach ($atts as $key => $value) {
			if ($value != '' && is_numeric($key)) {
				array_push($flags, $value);
			}
		}
	}
	return $flags;
}


add_shortcode('div', 'div_shortcode');
function div_shortcode($atts, $content=''){
	extract( shortcode_atts(array("class" => '', "style" => '', "open" => '', "close" => ''), $atts) );
	if ( $content ) {
		$div = '<div class="nebula-div ' . $class . '" style="' . $style . '">' . $content . '</div>';
	} else {
		if ( $close ) {
			$div = '</div><!-- /nebula-div -->';
		} else {
			$div = '<div class="nebula-div nebula-div-open' . $class . '" style="' . $style . '">';
		}
	}
	return $div;
}


//Gumby Grid Shortcodes

//Colgrid
if ( shortcode_exists( 'colgrid' ) ) {
	add_shortcode('gumby_colgrid', 'colgrid_shortcode');
} else {
	add_shortcode('gumby_colgrid', 'colgrid_shortcode');
	add_shortcode('colgrid', 'colgrid_shortcode');
}
function colgrid_shortcode($atts, $content=''){	
	extract( shortcode_atts( array('grid' => '', 'class' => '', 'style' => ''), $atts) );	
	$flags = get_flags($atts);
	$grid = array_values($flags);
	return '<section class="nebula-colgrid ' . $grid[0] . ' colgrid ' . $class . '" style="' . $style . '">' . do_shortcode($content) . '</section><!--/' . $grid[0] . ' colgrid-->';
} //end colgrid_grid()

//Container
if ( shortcode_exists( 'container' ) ) {
	add_shortcode('gumby_container', 'container_shortcode');
} else {
	add_shortcode('gumby_container', 'container_shortcode');
	add_shortcode('container', 'container_shortcode');
}
function container_shortcode($atts, $content=''){	
	extract( shortcode_atts( array('class' => '', 'style' => ''), $atts) );
	return '<div class="nebula-container container ' . $class . '" style="' . $style . '">' . do_shortcode($content) . '</div><!--/container-->';
} //end container_grid()

//Row
if ( shortcode_exists('row') ) {
	add_shortcode('gumby_row', 'row_shortcode');
} else {
	add_shortcode('gumby_row', 'row_shortcode');
	add_shortcode('row', 'row_shortcode');
}
function row_shortcode($atts, $content=''){	
	extract( shortcode_atts( array('class' => '', 'style' => ''), $atts) );
	$GLOBALS['col_counter'] = 0;
	return '<div class="nebula-row row ' . $class . '" style="' . $style . '">' . do_shortcode($content) . '</div><!--/row-->';
} //end row_grid()

//Columns
if ( shortcode_exists('columns') || shortcode_exists('column') || shortcode_exists('cols') || shortcode_exists('col') ) {
	add_shortcode('gumby_column', 'column_shortcode');
	add_shortcode('gumby_columns', 'column_shortcode');
	add_shortcode('gumby_col', 'column_shortcode');
	add_shortcode('gumby_cols', 'column_shortcode');
} else {
	add_shortcode('gumby_column', 'column_shortcode');
	add_shortcode('gumby_columns', 'column_shortcode');
	add_shortcode('gumby_col', 'column_shortcode');
	add_shortcode('gumby_cols', 'column_shortcode');
	add_shortcode('column', 'column_shortcode');
	add_shortcode('columns', 'column_shortcode');
	add_shortcode('col', 'column_shortcode');
	add_shortcode('cols', 'column_shortcode');
}
function column_shortcode($atts, $content=''){	
	extract( shortcode_atts( array('columns' => '', 'push' => '', 'centered' => '', 'first' => false, 'last' => false, 'class' => '', 'style' => ''), $atts) );
	
	$flags = get_flags($atts);
	if ( in_array('centered', $flags) ) {
		$centered = 'centered';
		$key = array_search('centered', $flags);
		unset($flags[$key]);
	} elseif ( in_array('first', $flags) ) {
		$GLOBALS['col_counter'] = 1;
		$first = 'margin-left: 0;';
		$key = array_search('first', $flags);
	} elseif ( $GLOBALS['col_counter'] == 0 ) {
		$GLOBALS['col_counter'] = 1;
		$first = 'margin-left: 0;';
	} else {
		$GLOBALS['col_counter']++;
	}
	
	if ( in_array('last', $flags) ) {
		$GLOBALS['col_counter'] = 0;
		$key = array_search('last', $flags);
		unset($flags[$key]);
	}
	
	$columns = array_values($flags);
	
	if ( $push ) {
		$push = 'push_' . $push;
	}
	
	return '<div class="nebula-columns ' . $columns[0] . ' columns ' . $push . ' ' . $centered . ' ' . $class . '" style="' . $style . ' ' . $first . '">' . do_shortcode($content) . '</div>';
	
} //end column_grid()


//Divider
add_shortcode('divider', 'divider_shortcode');
add_shortcode('hr', 'divider_shortcode');
add_shortcode('line', 'divider_shortcode');
function divider_shortcode($atts){
	extract( shortcode_atts(array("space" => '0', "above" => '0', "below" => '0'), $atts) );
	if ( $space ) {
		$above = $space;
		$below = $space;
	}
	$divider = '<hr class="nebula-divider" style="margin-top: ' . $above . 'px; margin-bottom: ' . $below . 'px;"/>';
	return $divider;
}


//Icon
add_shortcode('icon', 'icon_shortcode');
function icon_shortcode($atts){	
	extract( shortcode_atts(array('type'=>'', 'color'=>'inherit', 'size'=>'inherit', 'class'=>''), $atts) );		
	if (strpos($type, 'fa-') !== false) {
	    $fa = 'fa ';
	}
	$extra_style = !empty($color) ? 'color:' . $color . ';' :'';
	$extra_style .= !empty($size) ? 'font-size:' . $size . ';' :'';
	return '<i class="' . $class . ' nebula-icon-shortcode ' . $fa . $type . '" style="' . $extra_style . '"></i>';
}


//Button
add_shortcode('button', 'button_shortcode');
function button_shortcode($atts, $content=''){
	extract( shortcode_atts( array('size' => 'medium', 'type' => 'default', 'pretty' => false, 'metro' => false, 'icon' => false, 'side' => 'left', 'href' => '#', 'target' => false, 'class' => '', 'style' => ''), $atts) );

	if ( $pretty ) {
		$btnstyle = ' pretty';
	} elseif ( $metro ) {
		$btnstyle = ' metro';
	}

	if ( $icon ) {
		$side = 'icon-' . $side;
		if (strpos($icon, 'fa-') !== false) {
		    $icon_family = 'fa ';
		} else {
			$icon_family = 'entypo ';
		}
	} else {
		$icon = '';
		$size = '';
	}
	
	if ( $target ) {
		$target = ' target="' . $target . '"';
	}
	
	//Figure out if the extra classes and styles should go in the <div> or the <a>
	
	return '<div class="nebula-button ' . $size . ' ' . $type . $btnstyle . ' btn '. $side . ' ' . $icon_family . ' ' . $icon . '"><a href="' . $href . '"' . $target . '>' . $content . '</a></div>';

} //end button_shortcode()


//Space (aka Gap)
add_shortcode('space', 'space_shortcode');
add_shortcode('gap', 'space_shortcode');
function space_shortcode($atts){
	extract( shortcode_atts(array("height" => '20'), $atts) );  	
	return '<div class="space" style=" height:' . $height . 'px;" ></div>';
}


//Clear (aka Clearfix)
add_shortcode('clear', 'clear_shortcode');
add_shortcode('clearfix', 'clear_shortcode');
function clear_shortcode(){
	return '<div class="clearfix" style="clear: both;"></div>';
}


//Map
add_shortcode('map', 'map_shortcode');
function map_shortcode($atts){
	extract( shortcode_atts(array("key" => '', "mode" => 'place', "q" => '', "center" => '', "origin" => '', "destination" => '', "waypoints" => '', "avoid" => '', "zoom" => '', "maptype" => 'roadmap', "language" => '',  "region" => '', "width" => '100%', "height" => '250', "class" => '', "style" => ''), $atts) );  	
	if ( $key == '' ) {
		$key = 'AIzaSyArNNYFkCtWuMJOKuiqknvcBCyfoogDy3E'; //@TODO: Replace with your own key to avoid designating a key every time.
	}
	if ( $q != '' ) {
		$q = str_replace(' ', '+', $q);
		$q = '&q=' . $q;
	}
	if ( $mode == 'directions' ) {
		if ( $origin != '' ) {
			$origin = str_replace(' ', '+', $origin);
			$origin = '&origin=' . $origin;
		}
		if ( $destination != '' ) {
			$destination = str_replace(' ', '+', $destination);
			$destination = '&destination=' . $destination;
		}
		if ( $waypoints != '' ) {
			$waypoints = str_replace(' ', '+', $waypoints);
			$waypoints = '&waypoints=' . $waypoints;
		}
		if ( $avoid != '' ) {
			$avoid = '&avoid=' . $avoid;
		}
	}
	if ( $center != '' ) {
		$center = '&center=' . $center;
	}
	if ( $language != '' ) {
		$language = '&language=' . $language;
	}
	if ( $region != '' ) {
		$region = '&region=' . $region;
	}
	if ( $zoom != '' ) {
		$zoom = '&zoom=' . $zoom;
	}
	return '<iframe class="nebula-googlemap-shortcode googlemap ' . $class . '" width="' . $width . '" height="' . $height . '" frameborder="0" src="https://www.google.com/maps/embed/v1/' . $mode . '?key=' . $key . $q . $zoom . $center . '&maptype=' . $maptype . $language . $region . '" style="' . $style . '"></iframe>';
}


//Vimeo
add_shortcode('vimeo', 'vimeo_shortcode');
function vimeo_shortcode($atts){
	extract( shortcode_atts(array("id" => null, "height" => '', "width" => '', "autoplay" => '0', "badge" => '1', "byline" => '1', "color" => '00adef', "loop" => '0', "portrait" => '1', "title" => '1'), $atts) );  
	$protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';
	$width = 'width="' . $width . '"';
	$height = 'height="' . $height . '"';
	vimeo_meta($id);
	global $vimeo_meta;
	$vimeo = '<article class="vimeo video"><iframe id="' . $vimeo_meta['safetitle'] . '" class="vimeoplayer" src="' . $protocol . 'player.vimeo.com/video/' . $vimeo_meta['id'] . '?api=1&player_id=' . $vimeo_meta['safetitle'] . '" ' . $width . ' ' . $height . ' autoplay="' . $autoplay . '" badge="' . $badge . '" byline="' . $byline . '" color="' . $color . '" loop="' . $loop . '" portrait="' . $portrait . '" title="' . $title . '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></article>';
	return $vimeo;
}


//Youtube
add_shortcode('youtube', 'youtube_shortcode');
function youtube_shortcode($atts){
	extract( shortcode_atts(array("id" => null, "height" => '', "width" => '', "rel" => 0), $atts) ); 
	$protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';
	$width = 'width="' . $width . '"';
	$height = 'height="' . $height . '"';
	youtube_meta($id);
	global $youtube_meta;
	$youtube = '<article class="youtube video"><iframe id="' . $youtube_meta['safetitle'] . '" class="youtubeplayer" ' . $width . ' ' . $height . ' src="' . $protocol . 'www.youtube.com/embed/' . $youtube_meta['id'] . '?wmode=transparent&enablejsapi=1&origin=' . $youtube_meta['origin'] . '&rel=' . $rel . '" frameborder="0" allowfullscreen=""></iframe></article>';
	return $youtube;
}



//Pre
add_shortcode('pre', 'pre_shortcode');
$GLOBALS['pre'] = 0;
function pre_shortcode($atts, $content=''){
	extract( shortcode_atts(array('lang' => '', 'language' => '', 'color' => '', 'br' => false, 'class' => '', 'style' => ''), $atts) );  	
	
	if ( $GLOBALS['pre'] == 0 ) {
		echo '<link rel="stylesheet" type="text/css" href="' . get_stylesheet_directory_uri() . '/css/pre.css" />';
		$GLOBALS['pre'] = 1;
	}
	
	$flags = get_flags($atts);
	if ( !in_array('br', $flags) ) {
		$content = preg_replace('#<br\s*/?>#', '', $content);
	}
	
	$content = htmlspecialchars($content);
	
	if ( $lang == '' && $language != '' ) {
		$lang = $language;	
	}
	$search = array('actionscript', 'apache', 'css', 'directive', 'html', 'js', 'javascript', 'jquery', 'mysql', 'php', 'shortcode', 'sql');
	$replace = array('ActionScript', 'Apache', 'CSS', 'Directive', 'HTML', 'JavaScript', 'JavaScript', 'jQuery', 'MySQL', 'PHP', 'Shortcode', 'SQL');
	$vislang = str_replace($search, $replace, $lang);
	
	if ( $color != '' ) {
		return '<span class="nebula-pre pretitle ' . $lang . '" style="color: ' . $color . ';">' . $vislang . '</span><pre class="nebula-pre ' . $lang . ' ' . $class . '" style="border: 1px solid ' . $color . '; border-left: 5px solid ' . $color . ';' . $style . '" >' . $content . '</pre>';
	} else {
		return '<span class="nebula-pre pretitle ' . $lang . '">' . $vislang . '</span><pre class="nebula-pre ' . $lang . ' ' . $class . '" style="' . $style . '" >' . $content . '</pre>';
	}
} //end pre_shortcode()

//Code
add_shortcode('code', 'code_shortcode');
function code_shortcode($atts, $content=''){
	extract( shortcode_atts(array('class' => '', 'style' => ''), $atts) );  	
	
	//$content = htmlspecialchars($content);
	return '<code class="nebula-code ' . $class . '" style="' . $style . '" >' . $content . '</code>';

} //end code_shortcode()



//Accordion
add_shortcode('accordion', 'accordion_shortcode');
function accordion_shortcode($atts, $content=''){
	extract( shortcode_atts(array('class' => '', 'style' => ''), $atts) );  	
	
	return '<div class="nebula-bio ' . $class . '" style="' . $style . '" >' . $content . '</code>';

} //end accordion_shortcode()


//Bio
add_shortcode('bio', 'bio_shortcode');
function bio_shortcode($atts, $content=''){
	extract( shortcode_atts(array('class' => '', 'style' => ''), $atts) );  	
	
	/*
		Parameters to use:
			Name
			Title
			Email
			Phone
			Extension
			vCard path
			Website
			Twitter
			Facebook
			Instagram
			LinkedIn
			Photo path
			Excerpt ($content)
	*/
	
	return '<div class="nebula-bio ' . $class . '" style="' . $style . '" >' . $content . '</code>';

} //end bio_shortcode()


//Tooltip
add_shortcode('tooltip', 'tooltip_shortcode');
function tooltip_shortcode($atts, $content=''){
	extract( shortcode_atts(array('class' => '', 'style' => ''), $atts) );  	
	return '<div class="nebula-tooltip ' . $class . '" style="' . $style . '" >' . $content . '</code>';
} //end tooltip_shortcode()


//Slider
add_shortcode('slider', 'slider_shortcode');
function slider_shortcode($atts, $content=''){
	extract( shortcode_atts(array('id' => false, 'mode' => 'fade', 'delay' => '5000', 'speed' => '1000', 'easing' => 'easeInOutCubic', 'status' => false, 'frame' => false, 'titles' => false), $atts) );  	

	if ( !$id ) {
		$id = rand(1, 10000);
	}
	
	$flags = get_flags($atts);
	if ( in_array('frame', $flags) ) {
		$frame = 'nebulaframe';
	}
	
	$slideCount = preg_match_all('[/slide]', $content);
	if ( $slideCount == 0 ) {
		$slideCount = 1;	
	}
	$slideConWidth = $slideCount*100 . '%';
	$slideWidth = round(100/$slideCount, 3) . '%';
	
	$sliderCSS = '<style>#theslider-' . $id . ' {transition: all .5s ease 0s;}
					#theslider-' . $id . ' .sliderwrap {position: relative; overflow: hidden;}';
	if ( in_array('status', $flags) ) {
		$sliderCSS .= '#theslider-' . $id . ' .status {position: absolute; display: block; width: 100px; top: 5px; right: 5px; background: rgba(0,0,0,0.4); text-align: center; color: #fff; text-decoration: none; border-radius: 25px; z-index: 1500; cursor: default; opacity: 0; -webkit-transition: all 0.25s ease 0s; -moz-transition: all 0.25s ease 0s; -o-transition: all 0.25s ease 0s; transition: all 0.25s ease 0s;}
		.no-js #theslider-' . $id . ' .status {display: none;}
		#theslider-' . $id . ' .status.pause {opacity: 1; pointer-events: none;}
		#theslider-' . $id . ':hover .status.stop {opacity: 1;}
		#theslider-' . $id . ' .status.stop:hover,
		#theslider-' . $id . ' .status.stop.hover {cursor: pointer; background: rgba(0,0,0,0.7);}';
	} else {
		$sliderCSS .= '#theslider-' . $id . ' .status {display: none !important;}';
	}
	$sliderCSS .= '#theslider-' . $id . ' .slider-arrow {position: relative; display: inline-block; color: #fff;}
	.no-js #theslider-' . $id . ' .slider-arrow {display: none;}
	#theslider-' . $id . ' ul#theslides {position: relative; overflow: hidden; margin: 0; padding: 0;}
	#theslider-' . $id . ' ul#theslides li {position: absolute; top: 0; left: 0; width: 100%; height: auto; margin-bottom: -7px; padding: 0; opacity: 0; z-index: 0; transition: all 1s ease 0s;}
	#theslider-' . $id . ' ul#theslides li a {display: block; width: 100%; height: 100%;}
	#theslider-' . $id . ' ul#theslides li.active {position: relative; opacity: 1; z-index: 500;}
	.no-js #theslider-' . $id . ' .slider-nav-con {display: none;}
	#theslider-' . $id . ' .slider-nav-con {position: absolute; bottom: -50px; width: 100%; background: rgba(0,0,0,0.7); z-index: 1000; -moz-transition: all 0.25s ease 0s; -o-transition: all 0.25s ease 0s; transition: all 0.25s ease 0s;}
	#theslider-' . $id . ' #slider-nav {position: relative; display: table; margin: 0 auto; padding: 0; list-style: none;}
	#theslider-' . $id . ' #slider-nav li {display: inline-block; margin-right: 15px; padding: 0; text-align: center; vertical-align: middle;}
	#theslider-' . $id . ' #slider-nav li:last-child,
	#theslider-' . $id . ' #slider-nav li.last-child {margin-right: 0;}
	#theslider-' . $id . ' #slider-nav li a {display: table-cell; vertical-align: middle; padding: 5px 0; position: relative; height: 100%; color: #fff;}';
	
	$titles = array();
	$slideAttrs = attribute_map($content);	
	foreach ($slideAttrs as $key => $slideAttr) {
		array_push($titles, $slideAttr['title']);
		foreach ($slideAttr as $nested){
			if (isset($nested['title'])) {
				array_push($titles, $nested['title']);
			}
		}
	}	
		
	$titleCount = count($titles);
	$slideTitles = array();
	if ( $titleCount != $slideCount ) {
		$slideTitles[0]['activeUTF'] = '\u25CF';
		$slideTitles[0]['inactiveUTF'] = '\u25CB';
		$slideTitles[0]['activeHTML'] = '&#9679;';
		$slideTitles[0]['inactiveHTML'] = '&#9675;';
		$sliderCSS .= '#theslider-' . $id . ' #slider-nav li {margin-right: 10px;}
		#theslider-' . $id . ' #slider-nav li a.slider-arrow i {margin: 0 5px;}
		#theslider-' . $id . ' #slider-nav li.slide-nav-item a {font-size: 24px;}';
	} else {
		$customTitles = 1;
		$i = 0;
		while ( $i < $slideCount ) {
			$slideTitles[$i]['activeUTF'] = $titles[$i];
			$slideTitles[$i]['inactiveUTF'] = $titles[$i];
			$slideTitles[$i]['activeHTML'] = $titles[$i];
			$slideTitles[$i]['inactiveHTML'] = $titles[$i];
			$i++;
		}
	}
	
	$sliderCSS .= '#theslider-' . $id . ' #slider-nav li a:hover {color: #aaa;}
	#theslider-' . $id . ' #slider-nav li.active a {color: #fff; font-weight: bold;}
	#theslider-' . $id . ' #slider-nav li.active a:hover {color: #aaa;}</style>';
	
	$sliderHTML = '<div id="theslider-' . $id . '" class="container ' . $frame . '"><div class="row"><div class="sixteen columns sliderwrap">';
				                
	if ( in_array('status', $flags) ) {
		$sliderHTML .= '<a href="#" class="status"><i class="icon-pause"></i> <span>Paused</span></a>';
	}			                
				                
	$sliderHTML .= '<ul id="theslides">' . parse_shortcode_content(do_shortcode($content)) . '</ul>
				<div class="slider-nav-con">
					<ul id="slider-nav" class="clearfix">
						<li><a class="slider-arrow slider-left " href="#"><i class="icon-left-open"></i></a></li>';
	
	$i = 0;
	while ( $i < $slideCount ) {
		if ( !$customTitles ) {
			$sliderHTML .= '<li class="slide-nav-item"><a href="#">' . $slideTitles[0]['inactiveHTML'] . '</a></li>';
		} else {
			$sliderHTML .= '<li class="slide-nav-item"><a href="#">' . $slideTitles[$i]['inactiveHTML'] . '</a></li>';
		}
		$i++;
	}
	
	$sliderHTML .= '<li><a class="slider-arrow slider-right " href="#"><i class="icon-right-open"></i></a></li>
					</ul>
				</div></div></div></div>'; //Each through the li.slide-nav-item and pull the title from its corresponding slide by incrementing .eq()
		
	//<p> appearing here. apparently inside $sliderJS, but not attackable using str_replace()... ugh is that even causing the space?
	//Happens even when minified to one line...
	$sliderJS = '<script>jQuery(document).ready(function() {
						jQuery("#theslider-' . $id . ' #theslides li.slide-nav-item").each(function(i){
							jQuery(this).find("a").text(i);
						});
						strictPause = 0;
						autoSlider();
						jQuery("#theslider-' . $id . ' #theslides li").eq(0).addClass("active");';
	if ( !$customTitles ) {
		$sliderJS .= 'jQuery("#theslider-' . $id . ' #slider-nav li.slide-nav-item").eq(0).addClass("active").find("a").text("' . $slideTitles[0]['activeUTF'] . '");';
	} else {
		$sliderJS .= 'jQuery("#theslider-' . $id . ' #slider-nav li.slide-nav-item").eq(0).addClass("active");';
	}				
	$sliderJS .= 'function autoSlider() {
					        autoSlide = setInterval(function(){
					            theIndex = jQuery("#theslides li.active").index();
					            if ( strictPause == 0 ) {
					                activateSlider(theIndex, "next");
					            }
					        }, ' . $delay . ');
					    } //End autoSlider()
						jQuery("#theslider-' . $id . '").hover(function(){
					        clearInterval(autoSlide);
					        jQuery("#theslider-' . $id . ' #slider-nav").addClass("pause");
					        if ( !jQuery("#theslider-' . $id . ' .status").hasClass("stop") ) {
					        	jQuery("#theslider-' . $id . ' .status i").removeClass("icon-stop icon-play").addClass("icon-pause");
								jQuery("#theslider-' . $id . ' .status span").text("Paused");
						        jQuery("#theslider-' . $id . ' .status").addClass("pause");
					        }
					    }, function(){
					        if ( strictPause == 0 ) {
					            autoSlider();
					            jQuery("#theslider-' . $id . ' #slider-nav").removeClass("pause");
					            jQuery("#theslider-' . $id . ' .status").removeClass("pause");
					        }
					    });
					    //Navigation
					    jQuery("#theslider-' . $id . ' #slider-nav li.slide-nav-item a").on("click", function(){       
					        strictPause = 1;
					        jQuery("#theslider-' . $id . ' .status i").removeClass("icon-pause").addClass("icon-stop");
					        jQuery("#theslider-' . $id . ' .status").removeClass("pause").addClass("stop").find("span").text("Stopped");
					        jQuery("#theslider-' . $id . ' #slider-nav").removeClass("pause").addClass("stop");
					        theIndex = jQuery(this).parent().index();
					        activateSlider(theIndex-1, "goto");
					        return false;
					    });
						//Status
						jQuery("#theslider-' . $id . '").on("mouseenter", ".status.stop", function(){
							jQuery(this).find("i").removeClass("icon-stop").addClass("icon-play");
							jQuery(this).find("span").text("Resume");
						});
						jQuery("#theslider-' . $id . '").on("mouseleave", ".status.stop", function(){
							jQuery(this).find("i").removeClass("icon-play").addClass("icon-stop");
							jQuery(this).find("span").text("Stopped");
						});
						jQuery("#theslider-' . $id . '").on("click", ".status.stop", function(){
							strictPause = 0;
							jQuery("#theslider-' . $id . ' #slider-nav").removeClass("stop");
					        jQuery("#theslider-' . $id . ' .status").removeClass("pause stop");
					        return false;
						});
					    //Arrows
					    jQuery("#theslider-' . $id . ' .slider-arrow").on("click", function(){
					        strictPause = 1;
					        jQuery("#theslider-' . $id . ' .status i").removeClass("icon-pause").addClass("icon-stop");
					        jQuery("#theslider-' . $id . ' .status").addClass("stopped").find("span").text("Stopped");
					        jQuery("#theslider-' . $id . ' #slider-nav").removeClass("pause").addClass("stop");
					        jQuery("#theslider-' . $id . ' #slider-nav").removeClass("pause").addClass("stop");
					        theIndex = jQuery("#theslider-' . $id . ' #theslides li.active").index();
					        if ( jQuery(this).hasClass("slider-right") ) {
					            activateSlider(theIndex, "next");
					        } else {
					            activateSlider(theIndex, "prev");
					        }
					        return false;
					    });
					    function activateSlider(theIndex, buttoned) {
					        slideCount = jQuery("#theslider-' . $id . ' #theslides li").length;
					        activeHeight = jQuery("#theslider-' . $id . ' #theslides li.active img").height();
					        if ( buttoned == "next" ) {
					            newIndex = ( theIndex+1 >= slideCount ? 0 : theIndex+1 );
					        } else if ( buttoned == "prev" ) {
					            newIndex = ( theIndex-1 <= -1 ? slideCount-1 : theIndex-1 );
					        } else {
					            newIndex = theIndex;
					        }
							nextHeight = jQuery("#theslider-' . $id . ' #theslides li").eq(newIndex).find("img").height();	
							jQuery("#theslider-' . $id . ' #theslides li.active").removeClass("active");';
						    if ( !$customTitles ) {
								$sliderJS .= 'jQuery("#theslider-' . $id . ' #slider-nav li.slide-nav-item.active").removeClass("active").find("a").text("' . $slideTitles[0]['inactiveUTF'] . '");';
							} else {
								$sliderJS .= 'jQuery("#theslider-' . $id . ' #slider-nav li.slide-nav-item.active").removeClass("active");';
							}
					$sliderJS .= 'jQuery("#theslider-' . $id . ' #theslides li").eq(newIndex).addClass("active");';
						    if ( !$customTitles ) {
								$sliderJS .= 'jQuery("#theslider-' . $id . ' #slider-nav li.slide-nav-item").eq(newIndex).addClass("active").find("a").text("' . $slideTitles[0]['activeUTF'] . '");';
							} else {
								$sliderJS .= 'jQuery("#theslider-' . $id . ' #slider-nav li.slide-nav-item").eq(newIndex).addClass("active");';
							}
					$sliderJS .= 'if ( nextHeight >= activeHeight ) {
								jQuery("#theslider-' . $id . ' #theslides").delay(' . $speed/2 . ').animate({
									height: nextHeight,
								}, ' . $speed/2 . ', "' . $easing . '");
							} else {
								jQuery("#theslider-' . $id . ' #theslides").animate({
									height: nextHeight,
								}, ' . $speed/2 . ', "' . $easing . '");
							}
					    } //End activateSlider()
				    }); //End Document Ready
				    jQuery(window).on("load", function() {
					    jQuery("#theslider-' . $id . ' .slider-nav-con").css("bottom", "0");
				    }); //End Window Load</script>';
	
	return $sliderCSS . $sliderHTML . $sliderJS;

}


//Slide
add_shortcode('slide', 'slide_shortcode');
function slide_shortcode($atts, $content=''){
	extract( shortcode_atts(array('title' => '', 'link' => '', 'target' => ''), $atts) );  	
	
	if ( $title != '' ) {
		$alt = 'alt="' . $title . '"';
	} else {
		$title = '';
		$alt = '';
	}
	
	if ( $link == '' ) {
		$linkopen = '';
		$linkclose = '';
	} else {
		if ( $target == '' ) {
			$linkopen = '<a href="' . $link . '">';
		} else {
			$linkopen = '<a href="' . $link . '" target="' . $target . '">';
		}
		$linkclose = '</a>';
	}
		
	$target= '';
	
	return '<li class="nebula-slide clearfix">' . $linkopen . '<img src="' . $content . '" ' . $alt . '"/>' . $linkclose . '</li>'; //if title, echo it, else do not
} //end slide_shortcode()


//Map parameters of nested shortcodes
function attribute_map($str, $att = null) {
    $res = array();
    $reg = get_shortcode_regex();
    preg_match_all('~'.$reg.'~',$str, $matches);
    foreach($matches[2] as $key => $name) {
        $parsed = shortcode_parse_atts($matches[3][$key]);
        $parsed = is_array($parsed) ? $parsed : array();

        if(array_key_exists($name, $res)) {
            $arr = array();
            if(is_array($res[$name])) {
                $arr = $res[$name];
            } else {
                $arr[] = $res[$name];
            }

            $arr[] = array_key_exists($att, $parsed) ? $parsed[$att] : $parsed;
            $res[$name] = $arr;

        } else {
            $res[$name] = array_key_exists($att, $parsed) ? $parsed[$att] : $parsed;
        }
    }

    return $res;
}

//Remove empty <p> tags from Wordpress content (for nested shortcodes)
function parse_shortcode_content($content) {
   /* Parse nested shortcodes and add formatting. */
    $content = trim( do_shortcode( shortcode_unautop( $content ) ) );
    /* Remove '' from the start of the string. */
    if ( substr( $content, 0, 4 ) == '' )
        $content = substr( $content, 4 );
    /* Remove '' from the end of the string. */
    if ( substr( $content, -3, 3 ) == '' )
        $content = substr( $content, 0, -3 );
    /* Remove any instances of ''. */
    $content = str_replace( array( '<p></p>' ), '', $content );
    $content = str_replace( array( '<p>  </p>' ), '', $content );
    return $content;
}
//move wpautop filter to AFTER shortcode is processed
remove_filter( 'the_content', 'wpautop' );
add_filter( 'the_content', 'wpautop' , 99);
add_filter( 'the_content', 'shortcode_unautop',100 );


//Add Nebula Toolbar to TinyMCE
add_action('init', 'add_shortcode_button');
function add_shortcode_button(){
    if ( current_user_can('edit_posts') ||  current_user_can('edit_pages') ){  
         add_filter('mce_external_plugins', 'add_shortcode_plugin');  
         add_filter('mce_buttons_3', 'register_shortcode_button');  
       }    

}
function register_shortcode_button($buttons){
    array_push($buttons, "nebulaaccordion", "nebulabio", "nebulabutton", "nebulaclear", "nebulacode", "nebuladiv", "nebulacolgrid", "nebulacontainer", "nebularow", "nebulacolumn", "nebulaicon", "nebulaline", "nebulamap", "nebulapre", "nebulaspace", "nebulaslider", "nebulatooltip", "nebulavideo");
    return $buttons;
}
function add_shortcode_plugin($plugin_array) {  
	$plugin_array['nebulatoolbar'] = get_bloginfo('template_url') . '/js/shortcodes.js';
	return $plugin_array;  
}


//Close functions.php. Do not add anything after this closing tag!! ?>