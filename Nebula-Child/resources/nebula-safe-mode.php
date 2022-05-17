<?php
/*
Plugin Name: Nebula Safe Mode
Plugin URI: https://nebula.gearside.com
Description: When this file is in the /wp-content/mu-plugins/ directory, the site will be in safe mode.
Version: 1.0.0
Author: Chris Blakley
Author URI: https://nebula.gearside.com
*/

/*==========================
 README

 Instructions
	- Move or copy this file into /wp-content/mu-plugins/ directory (create the directory if it is not already there)
	- As soon as this file exists in that directory, it becomes active and safe mode is considered on!
	- Modify the `is_nebula_safe_mode_allowed()` function to control who can view safe mode. By default all logged-in users can, or if the ?safe-mode query parameter exists.
	- Uncomment additional actions as needed.
		- Please read the descriptions even if you think you know what they do– some will significantly increase server-response time which would undermine performance testing!

 Warnings
	- This "plugin" file is loaded before other plugins and also before themes. Therefore, no Nebula functions are available here!
	- Delete this file when finished testing!

 Notes:
	- This file will work in the same fashion on non-Nebula websites, so it could be used to troubleshoot other WordPress sites as well.
 ===========================*/

//Prevent direct access to this file
if ( !defined('ABSPATH') ){
	exit;
}

//Initialize WP Core Functions (if not already)
if ( !function_exists('wp_get_current_user') ){
	include ABSPATH . 'wp-includes/pluggable.php';
}

//Determine who safe mode is allowed for. Customize the conditionals in this function or write your own to control who can view the website in safe mode.
function is_nebula_safe_mode_allowed(){
	//Never show safe mode to Googlebot (or anyone claiming to be Googlebot)
	if ( strpos($_SERVER['HTTP_USER_AGENT'], 'Googlebot') ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
		return false;
	}

	//If the ?safe-mode query string exists
	if ( !empty($_GET['safe-mode']) || array_key_exists('safe-mode', $_GET) ){
		return true;
	}

	//For any logged-in user
	// if ( is_user_logged_in() ){
	// 	return true;
	// }

	//For administrators only
	// if ( current_user_can('manage_options') ){
	// 	return true;
	// }

	//For specific User IDs
	// $allowed_user_ids = array(99999); //Modify these to match actual user ID(s)
	// if ( in_array(get_current_user_id(), $allowed_user_ids) ){
	// 	return true;
	// }

	//For specific IP addresses
	//Note: REMOTE_ADDR is not a perfectly accurate way to detect IP, but may be sufficient for this.
	// $allowed_ips = array('999.99.99.99'); //Modify these to match actual IP(s)
	// foreach ( $allowed_ips as $allowed_ip ){
	// 	if ( wp_privacy_anonymize_ip($allowed_ip) == wp_privacy_anonymize_ip($_SERVER['REMOTE_ADDR']) ){ //Anonymizing to avoid processing all visitors' IPs
	// 		return true;
	// 	}
	// }

	//For logged-in users with specific email domain(s)
	// if ( is_user_logged_in() ){
	// 	$current_user = wp_get_current_user();
	// 	if ( !empty($current_user->user_email) ){
	// 		$current_user_domain = explode('@', $current_user->user_email)[1];
	// 		$dev_email_domains = array('example.com'); //Modify these to match actual email domain(s)
	// 		foreach ( $dev_email_domains as $dev_email_domain ){
	// 			if ( $dev_email_domain === $current_user_domain ){
	// 				return true;
	// 			}
	// 		}
	// 	}
	// }

	return false;
}

//Temporarily bypass most plugins
add_action('option_active_plugins', 'nebula_bypass_plugins');
function nebula_bypass_plugins($plugins=array()){
	if ( is_nebula_safe_mode_allowed() ){
		//Enter plugins allowed to still function while in safe mode
		$allowed_plugins = array(
			'query-monitor/query-monitor.php',
			'wp-all-export-pro/wp-all-export-pro.php'
		);

		//Loop through all active WordPress plugins
		foreach ( $plugins as $key => $plugin ){
			//If this plugin is an allowed plugin, skip it
			if ( in_array($plugin, $allowed_plugins) ){
				continue;
			}

			//Otherwise bypass the plugin by "unsetting" it (tricking WP into thinking it is deactivated). This is only a temporary "deactivation".
			unset($plugins[$key]);
		}
	}

	add_action('admin_enqueue_scripts', 'nebula_prevent_activating_plugins');
	return $plugins;
}

//Prevent activating plugins during safe mode so previous settings are not affected.
function nebula_prevent_activating_plugins(){
	?>
		<script>
			window.addEventListener('load', function(){
				document.querySelectorAll('.activate a').forEach(function(element){
					element.addEventListener('click', function(e){
						e.preventDefault();
						alert('Activating plugins is not allowed during Nebula Safe Mode.');
						return false;
					});
				});
			});
		</script>
	<?php
}

//Temporarily use a default, core WordPress theme (if one is available)
//add_filter('stylesheet', 'nebula_bypass_theme');
//add_filter('template', 'nebula_bypass_theme');
function nebula_bypass_theme($themes){
	if ( is_nebula_safe_mode_allowed() ){
		//Feel free to return early with a string of the preferred temporary theme here

		add_action('admin_enqueue_scripts', 'nebula_prevent_activating_themes');

		//We do not use WP_DEFAULT_THEME because it is not defined soon enough. You would end up with a mix of multiple themes and likely fatal errors!
		$core_themes = array('twentytwentyone', 'twentytwenty', 'twentynineteen', 'twentyeighteen', 'twentyseventeen', 'twentysixteen', 'twentyfifteen', 'twentyfourteen', 'twentythirteen', 'twentytwelve', 'twentyeleven', 'twentyten'); //In order of preference
		$installed_themes = wp_get_themes(); //Note: it is "expensive" to loop through this array, so we avoid doing that completely.
		foreach ( $core_themes as $core_theme ){
			if ( array_key_exists($core_theme, $installed_themes) ){
				return $core_theme;
			}
		}
	}

	return $themes; //Otherwise use whatever theme is active (without bypassing anything)
}

//Prevent activating themes during safe mode so previous settings are not affected.
function nebula_prevent_activating_themes(){
	?>
		<script>
			window.addEventListener('load', function(){
				document.querySelectorAll('.theme-actions a').forEach(function(element){
					element.addEventListener('click', function(e){
						e.preventDefault();
						alert('Activating themes is not allowed during Nebula Safe Mode.');
						return false;
					});
				});
			});
		</script>
	<?php
}

//Attempt to clear browser caches. Note: This significantly increases server-response time! Do not leave this enabled when testing performance!
//add_action('send_headers', 'nebula_clear_site_data');
function nebula_clear_site_data(){
	header('Clear-Site-Data: "cache", "storage"'); //Only clear cache and storage here! Note: This significantly increases server-response time!
}

//Unregister any service workers and clear JavaScript caches
add_action('wp_enqueue_scripts', 'unregister_service_worker');
function unregister_service_worker(){
	?>
		<script>
			//Force unregister all existing service workers
			if ( 'serviceWorker' in navigator ){
				navigator.serviceWorker.getRegistrations().then(function(registrations){
					for ( let registration of registrations ){
						registration.unregister();
					}
				});
			}

			//Clear the caches
			if ( 'caches' in window ){
				caches.keys().then(function(names){
					for ( let name of names ){
						caches.delete(names[name]);
					}
				});
			}
		</script>
	<?php
}