<?php

/*==========================
 This file includes functions that are currently being developed and tested. Once finished they will be moved to the appropriate function file.
 It is recommended that the require_once() for this file remain commented out from functions.php (or feel free to delete the entire line).
 ===========================*/


//Display WordPress debug messages for Devs who are admins with ?debug query string is used.
//@TODO "Nebula" 0: Check security issues of using this.
if ( is_debug() && current_user_can('manage_options') ) {
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





/*
//This would go in /functions/nebula_admin.php
//Found this PHP error log tracker dashboard metabox. Seems pretty cool. Research the possibility of including it more before implementing.
//http://sltaylor.co.uk/blog/wordpress-dashboard-widget-php-errors-log/
function slt_dashboardWidgets() {
	wp_add_dashboard_widget( 'slt-php-errors', 'PHP errors', 'slt_PHPErrorsWidget' );
}
add_action( 'wp_dashboard_setup', 'slt_dashboardWidgets' );
function slt_PHPErrorsWidget() {
	$logfile = '/home3/cblakley/public_html/error_log'; // Enter the server path to your logs file here
	$displayErrorsLimit = 100; // The maximum number of errors to display in the widget
	$errorLengthLimit = 300; // The maximum number of characters to display for each error
	$fileCleared = false;
	$userCanClearLog = current_user_can('manage_options');

	// Clear file?
	if ( $userCanClearLog && isset( $_GET["slt-php-errors"] ) && $_GET["slt-php-errors"]=="clear" ) {
		$handle = fopen( $logfile, "w" );
		fclose( $handle );
		$fileCleared = true;
	}

	// Read file
	if ( file_exists( $logfile ) ) {
		$errors = file( $logfile );
		$errors = array_reverse( $errors );
		if ( $fileCleared ) echo '<p><em>File cleared.</em></p>';
		if ( $errors ) {
			echo '<p>'.count( $errors ).' error';
			if ( $errors != 1 ) echo 's';
			echo '.';
			if ( $userCanClearLog ) echo ' [ <b><a href="'.get_bloginfo("url").'/wp-admin/?slt-php-errors=clear" onclick="return confirm(\'Are you sure?\');">CLEAR LOG FILE</a></b> ]';
			echo '</p>';
			echo '<div id="slt-php-errors" style="height:250px;overflow:scroll;padding:2px;background-color:#faf9f7;border:1px solid #ccc;">';
			echo '<ol style="padding:0;margin:0;">';
			$i = 0;
			foreach ( $errors as $error ) {
				echo '<li style="padding:2px 4px 6px;border-bottom:1px solid #ececec;">';
				$errorOutput = preg_replace( '/\[([^\]]+)\]/', '<b>[$1]</b>', $error, 1 );
				if ( strlen( $errorOutput ) > $errorLengthLimit ) {
					echo substr( $errorOutput, 0, $errorLengthLimit ).' [...]';
				} else {
					echo $errorOutput;
				}
				echo '</li>';
				$i++;
				if ( $i > $displayErrorsLimit ) {
					echo '<li style="padding:2px;border-bottom:2px solid #ccc;"><em>More than '.$displayErrorsLimit.' errors in log...</em></li>';
					break;
				}
			}
			echo '</ol></div>';
		} else {
			echo '<p>No errors currently logged.</p>';
		}
	} else {
		echo '<p><em>There was a problem reading the error log file.</em> The current template path is:</p><p>' . TEMPLATEPATH . '</p>';
	}
}
*/
