<?php

if ( !class_exists('Nebula') ){
	require_once get_template_directory() . '/nebula.php';
	nebula();
}

/*==========================
 Child Theme Functions
 • Files with the same name will override the parent (except for this functions.php file).
 • This functions.php file is loaded BEFORE the parent theme functions.php file.
 • Child style.css and main.js are loaded AFTER the parent style.css and nebula.js respectively.
 • Parent Directory: get_template_directory_uri()
 • Child Directory: get_stylesheet_directory_uri()
 • get_template_part() will determine the appropriate template file automatically.

 It is recommended not to override entire files in the Nebula functions directory. Instead, override the functions themselves (if needed) in functions.php, functions/nebula_child.php, or a new PHP file.
 To override functions, use the prefix "pre_" in an add_action() hook for the existing function name.
 add_action('pre_nebula_whatever', 'my_custom_function_name', 10, 2);
 function my_custom_function_name($parameter1, $parameter2){...}

 To disable a parent function entirely hook into it using:
 add_action('pre_nebula_whatever', '__return_empty_string');
 ===========================*/

require_once get_stylesheet_directory() . '/libs/nebula_child.php'; //Nebula Child

//Control what to append to Nebula asset version numbers when desired
add_filter('nebula_version_appended', function($version){
	if ( nebula()->is_bypass_cache() ){ //If debug mode is requested or any other reason to bypass cache
		return rand(10000, 99999); //Return a random number
	}

	//Feel free to customize this or even hard-code a number here as needed. Don't forget to return it!

	return $version;
});

add_action('wp_enqueue_scripts', 'register_nebula_child_assets', 327);
add_action('login_enqueue_scripts', 'register_nebula_child_assets', 327);
add_action('admin_enqueue_scripts', 'register_nebula_child_assets', 327);
function register_nebula_child_assets(){
	/*==========================
	 Deregister Parent Styles/Scripts
	 Use the handle registered in /Nebula-main/functions.php for the styles/scripts that should be removed.
	 Use both deregister and dequeue to completely remove the parent style/script
	 ===========================*/

	//Uncomment below to disable parent style.css
	//wp_deregister_style('nebula-main');
	//wp_dequeue_style('nebula-main');

	//Uncomment below to disable parent nebula.js (Be sure to copy over to main.js first)
	//wp_deregister_script('nebula-nebula');
	//wp_dequeue_script('nebula-nebula');

	/*==========================
	 Register Child Stylesheets
	 ===========================*/

	//wp_register_style($handle, $src, $dependencies, $version, $media);
	wp_register_style('nebula-child', get_stylesheet_directory_uri() . '/style.css', array('nebula-main'), filemtime(get_stylesheet_directory() . '/style.css'), 'all');
	if ( file_exists(get_stylesheet_directory() . '/assets/css/login.css') ){
		wp_register_style('nebula-child-login', get_stylesheet_directory_uri() . '/assets/css/login.css', array('nebula-login'), filemtime(get_stylesheet_directory() . '/assets/css/login.css'), 'all'); //Uncomment this if using it
	}
	wp_register_style('nebula-child-admin', get_stylesheet_directory_uri() . '/assets/css/admin.css', array('nebula-admin'), filemtime(get_stylesheet_directory() . '/assets/css/admin.css'), 'all');

	/*==========================
	 Register Child Scripts
	 ===========================*/

	//Use jsDelivr to pull common libraries: https://www.jsdelivr.com/
	//nebula()->register_script($handle, $src, $exec, $dependencies, $version, $in_footer);

	$nebula_child_script_dependencies = array('jquery-core', 'nebula-nebula', 'wp-hooks');
	if ( defined('ICL_LANGUAGE_NAME') ){
		$nebula_child_script_dependencies[] = 'wp-i18n'; //Allow JS strings to be translated as well https://make.wordpress.org/core/2018/11/09/new-javascript-i18n-support-in-wordpress/
	}

	nebula()->register_script('nebula-main', get_stylesheet_directory_uri() . '/assets/js/main.js', array('defer', 'module'), $nebula_child_script_dependencies, filemtime(get_stylesheet_directory() . '/assets/js/main.js'), true); //nebula.js (in the parent Nebula theme) is defined as a dependent here.
}

//Enqueue Child Styles & Scripts on the Front-End
add_action('wp_enqueue_scripts', function(){
	wp_enqueue_style('nebula-child'); //Stylesheets
	wp_enqueue_script('nebula-main'); //Scripts
}, 327);

//Enqueue Child Styles & Scripts on the Login page
add_action('login_enqueue_scripts', function(){
	if ( file_exists(get_stylesheet_directory() . '/assets/css/login.css') ){
		wp_enqueue_style('nebula-child-login');
	}
}, 327);

//Enqueue Child Styles & Scripts on Admin pages
add_action('admin_enqueue_scripts', function(){
	//Note: child theme admin.css is enqueued by WordPress core when that color scheme is selected per user
	//wp_enqueue_style('nebula-child-admin'); //Force it to be enqueued here if desired by uncommenting this line
}, 327);

//Close functions.php. DO NOT add anything after this closing tag!! ?>