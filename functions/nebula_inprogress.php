<?php

/*==========================
 This file includes functions that are currently being developed and tested. Once finished they will be moved to the appropriate function file.
 It is recommended that the require_once() for this file remain commented out from functions.php (or feel free to delete the entire line).
 ===========================*/


//Display WordPress debug messages for Devs who are admins with ?debug query string is used.
//@TODO "Nebula" 0: Check security issues of using this.
if ( array_key_exists('debug', $_GET) && is_dev() && current_user_can('manage_options') ) {
	define('WP_DEBUG', true);
	define('WP_DEBUG_DISPLAY', true);
}




//Only allow admins to modify Contact Forms //@TODO "Nebula" 0: Currently does not work because these constants are already defined!
//define('WPCF7_ADMIN_READ_CAPABILITY', 'manage_options');
//define('WPCF7_ADMIN_READ_WRITE_CAPABILITY', 'manage_options');





/*
//For automatically setting certain "Screen Options" settings by default.
add_action('admin_init', 'set_user_metaboxes');
function set_user_metaboxes($user_id=NULL) {
    //css-classes-hide

    echo 'bacon. user meta keys: ';
    var_dump(  );
    //var_dump(meta_box_prefs($screen)); //$screen needs to be the admin screen id or something
}
*/





add_action('nebula_article_end', 'track_firefox_reader_view');
function track_firefox_reader_view() {
	/*
		@TODO "Nebula" 0: How do we target *only* Firefox Reader View?
			- Remove the pixel w/ JS?
			- Maybe an onerror inline trickery?
			- I don't think the image gets reloaded or re-rendered on Reader View, so this still might not work.
	*/

	$referrer = ( $_SERVER['HTTP_REFERER'] ) ? '&utmr=' . $_SERVER['HTTP_REFERER']: '';

	//Not working...
	echo '<img src="http://www.google-analytics.com/__utm.gif?utmac=' . $GLOBALS['ga'] . '&utmt=event&utmwv=1&utmdt=' . urlencode(get_the_title()) . '&utmhn=' . nebula_url_components('hostname') . '&utmp=' . nebula_url_components('filepath') . '&utmn=' . rand(pow(10, 10-1), pow(10, 10)-1) . $referrer . '&utme=5(Firefox%20Reader%20View*Testing*This%20is%20just%20a%20test)" />';
}