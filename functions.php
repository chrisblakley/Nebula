<?php
/**
 * Functions
 */

/*==========================
 Include Nebula Utility Functions
 ===========================*/

require_once('functions/nebula_options.php'); //Nebula Options
require_once('functions/nebula_utilities.php'); //Nebula Utilities

/*==========================
 Google Analytics Tracking ID
 ===========================*/

$GLOBALS['ga'] = nebula_option('ga_tracking_id', ''); //Change Google Analytics Tracking ID here or in Nebula Options (or both)!

/*==========================
 Include Remaining Nebula Functions Groups
 ===========================*/

require_once('functions/nebula_security.php'); //Nebula Security
require_once('functions/nebula_automation.php'); //Nebula Automations
require_once('functions/nebula_optimization.php'); //Nebula Optimization
require_once('functions/nebula_admin.php'); //Nebula Admin Functions
require_once('functions/nebula_user_fields.php'); //Nebula User Fields
require_once('functions/nebula_functions.php'); //Nebula Functions
require_once('functions/nebula_shortcodes.php'); //Nebula Shortcodes
//require_once('functions/nebula_widgets.php'); //Nebula Widgets
require_once('functions/nebula_prototyping.php'); //Nebula Wireframing (can be commented out after launch)
require_once('functions/nebula_legacy.php'); //Nebula Legacy (to maximize backwards compatibility)
//require_once('functions/nebula_inprogress.php'); //Nebula In Progress (Functions currently being developed. Recommended to remain commented out.)

if ( is_plugin_active('woocommerce/woocommerce.php') ){
	require_once('functions/nebula_ecommerce.php'); //Nebula Ecommerce
}

/*==========================
 Register All Stylesheets
 ===========================*/

add_action('wp_enqueue_scripts', 'register_nebula_styles');
add_action('login_enqueue_scripts', 'register_nebula_styles');
add_action('admin_enqueue_scripts', 'register_nebula_styles');
function register_nebula_styles(){
	//wp_register_style($handle, $src, $dependencies, $version, $media);

	if ( nebula_google_font_option() ){
		wp_register_style('nebula-google_font', nebula_google_font_option(), array(), null, 'all');
	}
	wp_register_style('nebula-bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha.2/css/bootstrap.min.css', null, '4.0.0a2', 'all');
	wp_register_style('nebula-font_awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css', null, '4.6.3', 'all');
	wp_register_style('nebula-mmenu', 'https://cdnjs.cloudflare.com/ajax/libs/jQuery.mmenu/5.6.5/css/jquery.mmenu.all.min.css', null, '5.6.5', 'all');
	wp_register_style('nebula-datatables', 'https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.12/css/jquery.dataTables.min.css', null, '1.10.12', 'all'); //Datatables is called via main.js only as needed.
	wp_register_style('nebula-chosen', 'https://cdnjs.cloudflare.com/ajax/libs/chosen/1.5.1/chosen.min.css', null, '1.5.1', 'all');
	wp_register_style('nebula-jquery_ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.css', null, '1.11.4', 'all');
	wp_register_style('nebula-main', get_template_directory_uri() . '/style.css', array('nebula-bootstrap', 'nebula-mmenu'), null, 'all');
	wp_register_style('nebula-login', get_template_directory_uri() . '/stylesheets/css/login.css', null, null);
	wp_register_style('nebula-admin', get_template_directory_uri() . '/stylesheets/css/admin.css', null, null);
}

/*==========================
 Register All Scripts
 ===========================*/

add_action('wp_enqueue_scripts', 'register_nebula_scripts');
add_action('login_enqueue_scripts', 'register_nebula_scripts');
add_action('admin_enqueue_scripts', 'register_nebula_scripts');
function register_nebula_scripts(){
	//Use CDNJS to pull common libraries: http://cdnjs.com/
	//nebula_register_script($handle, $src, $exec, $dependencies, $version, $in_footer);

	nebula_register_script('nebula-modernizr_dev', get_template_directory_uri() . '/js/libs/modernizr.dev.js', 'defer', null, '3.3.1', false);
	nebula_register_script('nebula-modernizr_local', get_template_directory_uri() . '/js/libs/modernizr.min.js', 'defer', null, '3.3.1', false);
	nebula_register_script('nebula-modernizr', 'https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js', 'defer', null, '2.8.3', false); //https://github.com/cdnjs/cdnjs/issues/6100
	nebula_register_script('nebula-jquery_ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js', 'defer', null, '1.11.4', true);
	nebula_register_script('nebula-mmenu', 'https://cdnjs.cloudflare.com/ajax/libs/jQuery.mmenu/5.6.5/js/jquery.mmenu.all.min.js', 'defer', null, '5.6.5', true);
	nebula_register_script('nebula-froogaloop', 'https://f.vimeocdn.com/js/froogaloop2.min.js', null, null, null, true); //Can this be deferred?
	nebula_register_script('nebula-headroom', 'https://cdnjs.cloudflare.com/ajax/libs/headroom/0.9.3/headroom.min.js', 'defer', null, '0.9.3', true);
	nebula_register_script('nebula-tether', 'https://cdnjs.cloudflare.com/ajax/libs/tether/1.3.2/js/tether.min.js', 'defer', null, '1.3.2', true); //This is not enqueued or dependent because it is called via main.js only as needed.
	nebula_register_script('nebula-bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha.2/js/bootstrap.min.js', 'defer', null, '4.0.0a2', true);
	nebula_register_script('nebula-datatables', 'https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.12/js/jquery.dataTables.min.js', 'defer', null, '1.10.12', true); //Datatables is called via main.js only as needed.
	nebula_register_script('nebula-chosen', 'https://cdnjs.cloudflare.com/ajax/libs/chosen/1.5.1/chosen.jquery.min.js', 'defer', null, '1.5.1', true);
	nebula_register_script('performance-timing', get_template_directory_uri() . '/js/libs/performance-timing.js', 'defer', null, null, false);
	nebula_register_script('nebula-main', get_template_directory_uri() . '/js/main.js', 'defer', array('nebula-bootstrap', 'jquery', 'nebula-jquery_ui'), null, true);
	nebula_register_script('nebula-login', get_template_directory_uri() . '/js/login.js', null, array('jquery'), null, true);
	nebula_register_script('nebula-admin', get_template_directory_uri() . '/js/admin.js', 'defer', null, null, true);

	global $wp_scripts, $wp_styles, $upload_dir, $nebula;
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
	$nebula = array(
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
				'gaid' => $GLOBALS['ga'],
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
		$nebula['session'] = json_decode($_SESSION['nebulaSession'], true); //Replace nebula.session with session data
	} else {
		$nebula['session'] = array(
			'ip' => $_SERVER['REMOTE_ADDR'],
			'id' => nebula_session_id(),
			'referrer' => ( isset($_SERVER['HTTP_REFERER']) )? $_SERVER['HTTP_REFERER'] : false,
			'notes' => false,
			'geolocation' => false,
			'flags' => array(
				'adblock' => false,
				'gablock' => false,
			),
		);
	}

	$user_info = get_userdata(get_current_user_id());

	//Check for user cookie here.
	if ( $_COOKIE['nebulaUser'] && json_decode($_COOKIE['nebulaUser'], true) ){ //If user cookie exists and is valid JSON
		$nebula['user'] = json_decode($_COOKIE['nebulaUser'], true); //Replace nebula.user with cookie data

		if ( session_id() == '' || !isset($_SESSION) ){ //If it is a new session
			$nebula['user']['sessions'] = array(
				'initial' => true,
				'first' => $nebula['user']['sessions']['first'], //is this right? not time()?
				'last' => $nebula['user']['sessions']['current'],
				'current' => time(),
				'count' => $nebula['user']['sessions']['count']++,
			);
		} else { //Else it is an existing session?
			$nebula['user']['sessions']['current'] = time();
			$nebula['user']['sessions']['initial'] = false;
		}
	} else {
		$nebula['user'] = array(
			'ip' => $_SERVER['REMOTE_ADDR'],
			'id' => get_current_user_id(), //Never use this for security checks!
			'role' => $user_info->roles[0], //Never use this for security checks!
			'sessions' => array(
				'initial' => true,
				'first' => time(),
				'last' => false,
				'current' => time(),
				'count' => 1
			),
			'cid' => ga_parse_cookie(),
			'vid' => false,
			'conversions' => false,
			'flags' => array(
				'fbconnect' => false,
			),
			'client' => array( //Client data is here inside user because the cookie is not transferred between clients.
				'bot' => nebula_is_bot(),
				'remote_addr' => $_SERVER['REMOTE_ADDR'],
				//'user_agent' => urlencode($_SERVER['HTTP_USER_AGENT']), //@TODO "Nebula" 0: This is causing some serious issues. Only half of it shows up causing the json_decode() above to be null. Try var dumping the user agent to see if a certain character is messing it up.
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
}

//Start a session
add_action('init', 'nebula_session_start', 1);
function nebula_session_start(){
	session_start();
}

//Force clear cache for debugging and load debug scripts
if ( is_debug() ){
	header("Expires: Fri, 28 Mar 1986 02:40:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

	add_action('wp_enqueue_scripts', 'enqueue_nebula_debug_scripts');
	function enqueue_nebula_debug_scripts(){
		wp_enqueue_script('performance-timing');
	}

	add_action('wp_footer', 'nebula_echo_db_queries');
	function nebula_echo_db_queries(){
		echo "<script>console.log('" . get_num_queries() . " DB Queries in " . timer_stop() . " seconds.');</script>";
	}
}

/*==========================
 Enqueue Styles & Scripts on the Front-End
 ===========================*/

add_action('wp_enqueue_scripts', 'enqueue_nebula_frontend');
function enqueue_nebula_frontend(){
	global $nebula;

	//Stylesheets
	if ( nebula_google_font_option() ){
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
	wp_enqueue_script('nebula-headroom'); //Can this be loaded dynamically as needed?
	wp_enqueue_script('nebula-bootstrap');
	wp_enqueue_script('nebula-main');

	//Localized objects (localized to jquery to appear in <head>)
	wp_localize_script('jquery', 'nebula', $nebula);

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

/*==========================
 Enqueue Styles & Scripts on the Login
 ===========================*/

add_action('login_enqueue_scripts', 'enqueue_nebula_login');
function enqueue_nebula_login(){
	global $nebula;

	//Stylesheets
	wp_enqueue_style('nebula-login');

	//Scripts
	wp_enqueue_script('jquery');
	wp_enqueue_script('nebula-modernizr');
	wp_enqueue_script('nebula-login');

	//Localized objects (localized to jquery to appear in <head>)
	wp_localize_script('jquery', 'nebula', $nebula);
}

/*==========================
 Enqueue Styles & Scripts on the Admin
 ===========================*/

add_action('admin_enqueue_scripts', 'enqueue_nebula_admin');
function enqueue_nebula_admin(){
	global $nebula;

	//Stylesheets
	wp_enqueue_style('nebula-open_sans');
	wp_enqueue_style('nebula-admin');
	wp_enqueue_style('nebula-font_awesome');

	//Scripts
	wp_enqueue_script('nebula-admin');

	//Localized objects (localized to jquery to appear in <head>)
	wp_localize_script('jquery', 'nebula', $nebula);
}

//If Nebula wireframing functions don't exist, return false.
if ( !function_exists('fpo') ){ function fpo(){ return false; } }
if ( !function_exists('fpo_component') ){ function fpo_component(){ return false; } }
if ( !function_exists('fpo_component_start') ){ function fpo_component_start(){ return false; } }
if ( !function_exists('fpo_component_end') ){ function fpo_component_end(){ return false; } }

/*====================================================
 Custom Theme Functions
 Add custom functions for the theme here so that /functions/* files can be easily updated with newer Nebula versions.
 =====================================================*/

//$content_width is a global variable used by WordPress for max image upload sizes and media embeds (in pixels).
//If the content area is 960px wide, set $content_width = 940; so images and videos will not overflow.
if ( !isset($content_width) ){
	$content_width = 710;
}

//Adjust the content width when the full width page template is being used
add_action('template_redirect', 'nebula_set_content_width');
function nebula_set_content_width(){
    $override = apply_filters('pre_nebula_set_content_width', false);
	if ( $override !== false ){return $override;}

    global $content_width;

    if ( is_page_template('tpl-fullwidth.php') ){
        $content_width = 1040;
    }
}

//Close functions.php. DO NOT add anything after this closing tag!! ?>