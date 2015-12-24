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

$GLOBALS['ga'] = nebula_option('nebula_ga_tracking_id', ''); //Change Google Analytics Tracking ID here or in Nebula Options (or both)!


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
require_once('functions/nebula_widgets.php'); //Nebula Widgets
require_once('functions/nebula_wireframing.php'); //Nebula Wireframing (can be commented out after launch)
//require_once('functions/nebula_inprogress.php'); //Nebula In Progress (Functions currently being developed. Recommended to remain commented out.)


/*==========================
 Register All Stylesheets
 ===========================*/

add_action('wp_enqueue_scripts', 'register_nebula_styles');
add_action('login_enqueue_scripts', 'register_nebula_styles');
add_action('admin_enqueue_scripts', 'register_nebula_styles');
function register_nebula_styles(){
	//wp_register_style($handle, $src, $dependencies, $version, $media);
	wp_register_style('nebula-google_font', nebula_google_font_option(), array(), null, 'all');
	wp_register_style('nebula-normalize', 'https://cdnjs.cloudflare.com/ajax/libs/normalize/3.0.3/normalize.min.css', array(), '3.0.3', 'all');
	wp_register_style('nebula-gumby', get_template_directory_uri() . '/stylesheets/libs/gumby.css', array(), '2.6.0', 'all');
	wp_register_style('nebula-gumby_cdn', 'https://cdnjs.cloudflare.com/ajax/libs/gumby/2.6.0/css/gumby.min.css', array(), '2.6.0', 'all'); //Only useful for 12 col primary
	wp_register_style('nebula-font_awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css', array(), '4.5.0', 'all');
	wp_register_style('nebula-animate_css', 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.4.0/animate.min.css', array(), '3.4.0', 'all');
	wp_register_style('nebula-mmenu', 'https://cdnjs.cloudflare.com/ajax/libs/jQuery.mmenu/5.5.3/core/css/jquery.mmenu.all.css', array(), '5.5.3', 'all');
	//wp_register_style('nebula-bxslider', 'https://cdnjs.cloudflare.com/ajax/libs/bxslider/4.2.5/jquery.bxslider.min.css', array(), '4.2.5', 'none'); //bxSlider is conditionally loaded via main.js when needed.
	wp_register_style('nebula-datatables', 'https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.10/css/jquery.dataTables.min.css', array(), '1.10.10', 'all');
	wp_register_style('nebula-chosen', 'https://cdnjs.cloudflare.com/ajax/libs/chosen/1.4.2/chosen.min.css', array(), '1.4.2', 'all');
	wp_register_style('nebula-jquery_ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.css', array(), '1.11.4', 'all');
	wp_register_style('nebula-main', get_template_directory_uri() . '/style.css', array('nebula-normalize', 'nebula-gumby', 'nebula-mmenu'), null, 'all');
	wp_register_style('nebula-login', get_template_directory_uri() . '/stylesheets/css/login.css', array(), null);
	wp_register_style('nebula-admin', get_template_directory_uri() . '/stylesheets/css/admin.css', array(), null);
	wp_register_style('nebula-wireframing', get_template_directory_uri() . '/stylesheets/css/wireframing.css', array('nebula-main'), null);
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
	nebula_register_script('nebula-jquery_old', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.3/jquery.min.js', null, array(), '1.11.3', true);
	nebula_register_script('nebula-modernizr_dev', get_template_directory_uri() . '/js/libs/modernizr.dev.js', 'defer', array(), '3.0.0a4', false);
	nebula_register_script('nebula-modernizr_local', get_template_directory_uri() . '/js/libs/modernizr.min.js', 'defer', array(), '2.8.3', false);
	nebula_register_script('nebula-modernizr', 'https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js', 'defer', array(), '2.8.3', false);
	nebula_register_script('nebula-jquery_ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js', 'defer', array(), '1.11.4', true);
	nebula_register_script('nebula-mmenu', 'https://cdnjs.cloudflare.com/ajax/libs/jQuery.mmenu/5.5.3/core/js/jquery.mmenu.min.all.js', null, array(), '5.5.3', true);
	//nebula_register_script('nebula-mmenu_debugger', get_template_directory_uri() . '/js/libs/jquery.mmenu.debugger.js', null, array('nebula-mmenu'), '5.3.1', true);
	nebula_register_script('nebula-doubletaptogo', get_template_directory_uri() . '/js/libs/doubletaptogo.js', 'defer', array(), null, true);
	//nebula_register_script('nebula-bxslider', 'https://cdnjs.cloudflare.com/ajax/libs/bxslider/4.2.5/jquery.bxslider.min.js', 'defer', array(), '4.2.5', true); //bxSlider is conditionally loaded via main.js when needed.
	nebula_register_script('nebula-froogaloop', 'https://f.vimeocdn.com/js/froogaloop2.min.js', null, array(), null, true);
	nebula_register_script('nebula-skrollr', 'https://cdnjs.cloudflare.com/ajax/libs/skrollr/0.6.30/skrollr.min.js', 'gumby-debug', array(), '0.6.30', true);
	nebula_register_script('nebula-headroom', 'https://cdnjs.cloudflare.com/ajax/libs/headroom/0.7.0/headroom.min.js', null, array(), '0.7.0', true);
	nebula_register_script('nebula-performance_timing', get_template_directory_uri() . '/js/libs/performance-timing.js', 'async', array(), null, true);
	nebula_register_script('nebula-respond', 'https://cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.min.js', 'defer', array(), '1.4.2', true);
	nebula_register_script('nebula-html5shiv', 'https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.min.js', 'defer', array(), '3.7.3', true);
	nebula_register_script('nebula-gumby', get_template_directory_uri() . '/js/libs/gumby.min.js', null, array(), '2.6.0', true); //CDN: //cdnjs.cloudflare.com/ajax/libs/gumby/2.6.0/js/libs/gumby.min.js //Note: CDN version does not have the extensions installed.
	nebula_register_script('nebula-twitter', get_template_directory_uri() . '/js/libs/twitter.js', null, array(), null, true);
	nebula_register_script('nebula-datatables', 'https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.10/js/jquery.dataTables.min.js', null, array(), '1.10.10', true);
	nebula_register_script('nebula-chosen', 'https://cdnjs.cloudflare.com/ajax/libs/chosen/1.4.2/chosen.jquery.min.js', null, array(), '1.4.2', true);
	nebula_register_script('nebula-moment', 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.6/moment-with-locales.min.js', null, array(), '2.10.6', true);
	nebula_register_script('nebula-maskedinput', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.maskedinput/1.4.1/jquery.maskedinput.min.js', null, array(), '1.4.1', true);
	nebula_register_script('nebula-adblockcheck', get_template_directory_uri() . '/js/libs/show_ads.js', null, array(), null, false); //Must be loaded in the head.
	nebula_register_script('nebula-main', get_template_directory_uri() . '/js/main.js', 'defer', array('nebula-gumby', 'jquery', 'nebula-jquery_ui'), null, true);
	nebula_register_script('nebula-login', get_template_directory_uri() . '/js/login.js', null, array('jquery'), null, true);
	nebula_register_script('nebula-wireframing', get_template_directory_uri() . '/js/wireframing.js', null, array('nebula-main'), null, true);
	nebula_register_script('nebula-admin', get_template_directory_uri() . '/js/admin.js', 'defer', array(), null, true);

	global $upload_dir, $nebula;
	$upload_dir = wp_upload_dir();

	$nebula = array(
		'site' => array(
			'name' => urlencode(get_bloginfo("name")),
			'template_directory' => get_template_directory_uri(),
			'stylesheet_directory' => get_stylesheet_directory_uri(),
			'home_url' => home_url(),
			'domain' => nebula_url_components('domain'),
			//'admin_email' => base64_encode(get_option('admin_email')), //Removed until actually needed somewhere.
			'ajax' => array(
				'url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('nebula_ajax_nonce'),
			),
			'upload_dir' => $upload_dir['baseurl'],
			'options' => array(
				'nebula_cse_id' => get_option('nebula_cse_id'),
				'nebula_google_browser_api_key' => get_option('nebula_google_browser_api_key'),
				//'nebula_google_maps_api' => get_option('nebula_google_maps_api'), //To date, the Google Maps API key is not needed for JS integrations.
				'facebook_url' => get_option('nebula_facebook_url'),
				'facebook_app_id' => get_option('nebula_facebook_app_id'),
				'twitter_url' => get_option('nebula_twitter_url'),
				'google_plus_url' => get_option('nebula_google_plus_url'),
				'linkedin_url' => get_option('nebula_linkedin_url'),
				'youtube_url' => get_option('nebula_youtube_url'),
				'instagram_url' => get_option('nebula_instagram_url'),
				'manage_options' => current_user_can('manage_options'),
				'debug' => is_debug(),
			),
		),
		'post' => array(
			'id' => get_the_id(),
			'title' => urlencode(get_the_title()),
			'author' => urlencode(get_the_author()),
			'year' => get_the_date('Y'),
		),
		'dom' => false,

	);

	if ( $_COOKIE['nebulaSession'] ){ //If cookie exists
		$nebula['session'] = json_decode(str_replace('\\', '', $_COOKIE['nebulaSession']), true); //Replace nebula.session with cookie data
	} else {
		$nebula['session'] = array(
			'ip' => $_SERVER['REMOTE_ADDR'],
			'id' => false,
			'referrer' => $_SERVER['HTTP_REFERER'],
			'history' => false,
			'notes' => false,
			'geolocation' => false,
		);
	}

	$user_info = get_userdata(get_current_user_id());

	//Check for user cookie here.
	if ( $_COOKIE['nebulaUser'] ){ //If cookie exists
		$nebula['user'] = json_decode(str_replace('\\', '', $_COOKIE['nebulaUser']), true); //Replace nebula.user with cookie data
	} else {
		$nebula['user'] = array(
			'ip' => $_SERVER['REMOTE_ADDR'],
			'id' => get_current_user_id(),
			'role' => $user_info->roles[0],
			'last' => time(),
			'conversions' => false,
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
				'capabilities' => array( //@TODO "Nebula" 0: Is this needed if we can just check against Modernizr?
					'speech' => 'unknown',
					'battery' => 'unknown',
					'network' => 'unknown',
					'adblock' => false,
					'select_text' => 'unknown',
					'clipboard' => array(
						'copy' => 'unknown',
						'paste' => 'unknown',
					),
				),
			),
		);
	}
}

//Force clear cache for debugging and load debug scripts
if ( is_debug() ){
	header("Expires: Fri, 28 Mar 1986 02:40:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

	if ( !function_exists('enqueue_nebula_debug_scripts') ){
		add_action('wp_enqueue_scripts', 'enqueue_nebula_debug_scripts');
		function enqueue_nebula_debug_scripts(){
			wp_enqueue_script('performance-timing');
		}
	}

	if ( !function_exists('nebula_echo_db_queries') ){
		add_action('shutdown', 'nebula_echo_db_queries');
		function nebula_echo_db_queries(){
			echo "<script>console.log('DB Queries: " . get_num_queries() . "');</script>";
		}
	}
}


/*==========================
 Enqueue Styles & Scripts on the Front-End
 ===========================*/

add_action('wp_enqueue_scripts', 'enqueue_nebula_frontend');
function enqueue_nebula_frontend(){
	global $nebula;

	//Stylesheets
	wp_enqueue_style('nebula-normalize');
	wp_enqueue_style('nebula-gumby');
	wp_enqueue_style('nebula-mmenu');
	//wp_enqueue_style('nebula-animate_css');
	wp_enqueue_style('nebula-jquery_ui');
	wp_enqueue_style('nebula-font_awesome');
	wp_enqueue_style('nebula-google_font');
	wp_enqueue_style('nebula-main');

	if ( !nebula_option('nebula_wireframing', 'disabled') ){
		wp_enqueue_style('nebula-wireframing');
		wp_enqueue_script('nebula-wireframing');
	}

	//Scripts
	wp_enqueue_script('jquery');
	wp_enqueue_script('nebula-jquery_ui');
	//wp_enqueue_script('swfobject');
	//wp_enqueue_script('hoverIntent');
	//wp_enqueue_script('nebula-modernizr_dev');
	wp_enqueue_script('nebula-modernizr');
	wp_enqueue_script('nebula-mmenu');
	//wp_enqueue_script('nebula-doubletaptogo');
	wp_enqueue_script('nebula-headroom');
	wp_enqueue_script('nebula-gumby');
	wp_enqueue_script('nebula-main');

	//Localized objects (localized to jquery to appear in <head>)
	wp_localize_script('jquery', 'nebula', $nebula);

	//Conditionals
	if ( is_debug() ){ //When ?debug query string is used
		wp_enqueue_script('nebula-performance_timing');
		//wp_enqueue_script('nebula-mmenu_debugger');
	}

	if ( nebula_is_browser('ie', '9', '<=') ){ //Old IE
		wp_enqueue_script('nebula-respond');
		wp_enqueue_script('nebula-html5shiv');
		//wp_deregister_script('jquery'); //Uncomment the next jQuery lines if WordPress bundles jQuery 2.0+ (currently bundles jQuery 1.11.2)
		//wp_enqueue_script('nebula-jquery_old'); //Uncomment the next jQuery lines if WordPress bundles jQuery 2.0+ (currently bundles jQuery 1.11.2)
	}

	if ( !nebula_is_bot() && !empty($GLOBALS['ga']) && nebula_option('nebula_cd_adblocker') ){
		wp_enqueue_script('nebula-adblockcheck'); //Detect if user is blocking ads. If the custom dimension is active removing this line will cause false positives!
	}

	if ( is_page_template('tpl-search.php') || is_page(9999) ){ //Form pages (that use selects) or Advanced Search Template. The Chosen library is also dynamically loaded in main.js.
		wp_enqueue_style('nebula-chosen');
		wp_enqueue_script('nebula-chosen');
	}

	if ( is_page(9999) ){ //Datatables pages. The Datatables library is also dynamically loaded in main.js
		wp_enqueue_style('nebula-datatables');
		wp_enqueue_script('nebula-datatables');
	}

	if ( is_page(9999) ){ //Twitter pages (conditional may need to change depending on type of page it's used on)
		wp_enqueue_script('nebula-twitter');
		//wp_enqueue_script('nebula-moment'); //Uncomment if using moment.js instead of Date.parse() for times.
	}

	if ( nebula_is_desktop() ){ //Desktop traffic only
		//wp_enqueue_script('nebula-skrollr');
	}
}


/*==========================
 Enqueue Styles & Scripts on the Login
 ===========================*/

add_action('login_enqueue_scripts', 'enqueue_nebula_login');
function enqueue_nebula_login(){
	global $nebula, $localize_bloginfo, $localize_clientinfo, $localize_nebula_options;

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
if ( !function_exists('nebula_set_content_width') ){
	add_action('template_redirect', 'nebula_set_content_width');
	function nebula_set_content_width(){
	    global $content_width;

	    if ( is_page_template('tpl-fullwidth.php') ){
	        $content_width = 1040;
	    }
	}
}

//Add new image sizes
//Certain sizes (like FB Open Graph sizes) are already added, so only add extra sizes that are needed.
//add_image_size('example', 32, 32, 1);


//Add/remove post formats as needed - http://codex.wordpress.org/Post_Formats
//add_theme_support('post-formats', array('aside', 'chat', 'status', 'gallery', 'link', 'image', 'quote', 'video', 'audio'));










//Google Analytics Experiments (Split Tests)
//Documentation: http://gearside.com/nebula/documentation/custom-functionality/split-tests-using-google-analytics-experiments-with-nebula/
//Add a new condition for each experiment group. There can be as many concurrent experiments as needed (just make sure there is no overlap!)
if ( !function_exists('nebula_ga_experiment_detection') ){
	add_action('nebula_head_open', 'nebula_ga_experiment_detection');
	function nebula_ga_experiment_detection(){

		//Example Experiment
		if ( is_page(9999) ){ //Use is_post(9999) for single posts. Change the ID to match the desired page/post! ?>
			<!-- Paste Google Analytics Experiment generated script here -->
		<?php }

	}
}


//Close functions.php. DO NOT add anything after this closing tag!! ?>