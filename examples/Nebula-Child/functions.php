<?php
/**
 * Child Functions
 */

//nebula_child.php depends on the nebula_options.php functions, so it must be required first.
require_once(get_template_directory() . '/functions/nebula_options.php'); //Nebula Options


/*==========================
 Child Theme Functions
 • Files with the same name will override the parent (except for this functions.php file).
 • This functions.php file is loaded BEFORE the parent theme functions.php file.
 • Child style.css and child.js are loaded AFTER the parent style.css and main.js respectively.
 • Parent Directory: get_template_directory_uri()
 • Child Directory: get_stylesheet_directory_uri()

 It is recommended not to override entire files in the Nebula functions directory. Instead, override the functions themselves (if needed) in functions.php, functions/nebula_child.php, or a new PHP file.
 ===========================*/

require_once('functions/nebula_child.php'); //Nebula Child


/*==========================
 Register Child Stylesheets
 ===========================*/

add_action('wp_enqueue_scripts', 'register_nebula_child_styles');
add_action('login_enqueue_scripts', 'register_nebula_child_styles');
add_action('admin_enqueue_scripts', 'register_nebula_child_styles');
function register_nebula_child_styles(){
	//wp_register_style($handle, $src, $dependencies, $version, $media);
	wp_register_style('nebula-child', get_stylesheet_directory_uri() . '/style.css', array('nebula-main'), null, 'all');
}


/*==========================
 Register Child Scripts
 ===========================*/

add_action('wp_enqueue_scripts', 'register_nebula_child_scripts');
add_action('login_enqueue_scripts', 'register_nebula_child_scripts');
add_action('admin_enqueue_scripts', 'register_nebula_child_scripts');
function register_nebula_child_scripts(){
	//Use CDNJS to pull common libraries: http://cdnjs.com/
	//nebula_register_script($handle, $src, $exec, $dependencies, $version, $in_footer);
	nebula_register_script('nebula-child', get_stylesheet_directory_uri() . '/js/child.js', 'defer', array('nebula-main', 'jquery', 'nebula-jquery_ui'), null, true);
}


/*==========================
 Enqueue Child Styles & Scripts on the Front-End
 ===========================*/

add_action('wp_enqueue_scripts', 'enqueue_nebula_child_frontend');
function enqueue_nebula_child_frontend(){
	//Stylesheets
	wp_enqueue_style('nebula-child');

	//Scripts
	wp_enqueue_script('nebula-child');
}


/*==========================
 Enqueue Child Styles & Scripts on the Login
 ===========================*/

add_action('login_enqueue_scripts', 'enqueue_nebula_child_login');
function enqueue_nebula_child_login(){
	//Login styles and scripts here
}


/*==========================
 Enqueue Child Styles & Scripts on the Admin
 ===========================*/

add_action('admin_enqueue_scripts', 'enqueue_nebula_child_admin');
function enqueue_nebula_child_admin(){
	//Admin styles and scripts here
}








//Close functions.php. DO NOT add anything after this closing tag!! ?>