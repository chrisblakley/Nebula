<?php

/**
 * Child Functions
 */

//nebula_child.php depends on the nebula_options.php functions, so it must be required first.
//require_once(get_template_directory() . '/libs/nebula_options.php'); //Nebula Options


if ( !class_exists('Nebula') ){
	require_once(get_template_directory() . '/nebula.php');
	nebula();
}

/*==========================
 Child Theme Functions
 • Files with the same name will override the parent (except for this functions.php file).
 • This functions.php file is loaded BEFORE the parent theme functions.php file.
 • Child style.css and child.js are loaded AFTER the parent style.css and main.js respectively.
 • Parent Directory: get_template_directory_uri()
 • Child Directory: get_stylesheet_directory_uri()
 • get_template_part() will determine the appropriate template file automatically.

 It is recommended not to override entire files in the Nebula functions directory. Instead, override the functions themselves (if needed) in functions.php, functions/nebula_child.php, or a new PHP file.
 To override functions, use the prefix "pre_" in an add_filter() hook for the existing function name.
 add_filter('pre_nebula_whatever', 'my_custom_function_name');
 function my_custom_function_name($parameter){...}

 To disable a parent function entirely hook into it using:
 add_filter('pre_nebula_whatever', '__return_empty_string');
 ===========================*/

require_once(get_stylesheet_directory() . '/libs/nebula_child.php'); //Nebula Child


/*==========================
 Deregister Parent Styles/Scripts
 Use the handle registerred in /Nebula-master/functions.php for the styles/scripts that should be removed.
 Use both deregister and dequeue to completely remove the parent style/script
 ===========================*/

add_action('wp_enqueue_scripts', 'deregister_nebula_parent_scripts', 327);
add_action('login_enqueue_scripts', 'deregister_nebula_parent_scripts', 327);
add_action('admin_enqueue_scripts', 'deregister_nebula_parent_scripts', 327);
function deregister_nebula_parent_scripts(){
	//Uncomment below to disable parent style.css
	//wp_deregister_style('nebula-main');
	//wp_dequeue_style('nebula-main');

	//Uncomment below to disable parent main.js (Be sure to copy over to child.js first)
	//wp_deregister_script('nebula-main');
	//wp_dequeue_script('nebula-main');
}


/*==========================
 Register Child Stylesheets
 ===========================*/

add_action('wp_enqueue_scripts', 'register_nebula_child_styles');
add_action('login_enqueue_scripts', 'register_nebula_child_styles');
add_action('admin_enqueue_scripts', 'register_nebula_child_styles');
function register_nebula_child_styles(){
	//wp_register_style($handle, $src, $dependencies, $version, $media);
	wp_register_style('nebula-child', get_stylesheet_directory_uri() . '/style.css', array('nebula-main'), null, 'all');
	wp_register_style('nebula-login-child', get_stylesheet_directory_uri() . '/assets/css/login.css', array('nebula-login'), null, 'all');
	wp_register_style('nebula-admin-child', get_stylesheet_directory_uri() . '/assets/css/admin.css', array('nebula-admin'), null, 'all');
}


/*==========================
 Register Child Scripts
 ===========================*/

add_action('wp_enqueue_scripts', 'register_nebula_child_scripts');
add_action('login_enqueue_scripts', 'register_nebula_child_scripts');
add_action('admin_enqueue_scripts', 'register_nebula_child_scripts');
function register_nebula_child_scripts(){
	//Use CDNJS to pull common libraries: http://cdnjs.com/
	//nebula()->register_script($handle, $src, $exec, $dependencies, $version, $in_footer);
	nebula()->register_script('nebula-child', get_stylesheet_directory_uri() . '/assets/js/child.js', 'defer', array('jquery-core', 'nebula-jquery_ui', 'nebula-main'), null, true); //main.js in the parent Nebula theme is defined as a dependant here.
}


/*==========================
 Enqueue Child Styles & Scripts on the Front-End
 ===========================*/

add_action('wp_enqueue_scripts', 'enqueue_nebula_child_frontend', 327);
function enqueue_nebula_child_frontend(){
	//Stylesheets
	wp_enqueue_style('nebula-child');

	//Scripts
	wp_enqueue_script('nebula-child');
}


/*==========================
 Enqueue Child Styles & Scripts on the Login
 ===========================*/

add_action('login_enqueue_scripts', 'enqueue_nebula_child_login', 327);
function enqueue_nebula_child_login(){
	//Stylesheets
	if ( file_exists(get_stylesheet_directory() . '/assets/css/login.css') ){
		wp_enqueue_style('nebula-login-child');
	}
}


/*==========================
 Enqueue Child Styles & Scripts on the Admin
 ===========================*/

add_action('admin_enqueue_scripts', 'enqueue_nebula_child_admin', 327);
function enqueue_nebula_child_admin(){
	//Stylesheets
	if ( file_exists(get_stylesheet_directory() . '/assets/css/admin.css') ){
		wp_enqueue_style('nebula-admin-child');
	}
}








//Close functions.php. DO NOT add anything after this closing tag!! ?>