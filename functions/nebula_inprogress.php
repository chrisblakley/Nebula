<?php

/*==========================
 This file includes functions that are currently being developed and tested. Once finished they will be moved to the appropriate function file.
 It is recommended that the require_once() for this file remain commented out from functions.php (or feel free to delete the entire line).
 ===========================*/






//Display WordPress debug messages for Devs who are admins with ?debug query string is used.
//It appears WP_DEBUG can only be defined true in wp-config.php
//When it's true, it can be tested with: if ( WP_DEBUG ) ...
if ( is_debug() && current_user_can('manage_options') ){
	define('WP_DEBUG', true);
	define('WP_DEBUG_DISPLAY', true);
}




//Upload a file to the Media Library //@TODO "Nebula" 0: In progress
function nebula_upload_to_media_library($filepath){
	if ( !file_exists($filepath) || strlen(trim($filepath)) == 0 ){
		return;
	}
	$result = wp_handle_upload($filepath);
}














//Only allow admins to modify Contact Forms //@TODO "Nebula" 0: Currently does not work because these constants are already defined!
//define('WPCF7_ADMIN_READ_CAPABILITY', 'manage_options');
//define('WPCF7_ADMIN_READ_WRITE_CAPABILITY', 'manage_options');





/*
//For automatically setting certain "Screen Options" settings by default.
add_action('admin_init', 'set_user_metaboxes');
function set_user_metaboxes($user_id=NULL){
    //css-classes-hide

    echo 'bacon. user meta keys: ';
    var_dump(  );
    //var_dump(meta_box_prefs($screen)); //$screen needs to be the admin screen id or something
}
*/








/*
//This would go in /functions/nebula_admin.php
//Found this PHP error log tracker dashboard metabox. Seems pretty cool. Research the possibility of including it more before implementing.
//http://sltaylor.co.uk/blog/wordpress-dashboard-widget-php-errors-log/
function slt_dashboardWidgets(){
	wp_add_dashboard_widget( 'slt-php-errors', 'PHP errors', 'slt_PHPErrorsWidget' );
}
add_action( 'wp_dashboard_setup', 'slt_dashboardWidgets' );
function slt_PHPErrorsWidget(){
	$logfile = '/home3/cblakley/public_html/error_log'; // Enter the server path to your logs file here
	$displayErrorsLimit = 100; // The maximum number of errors to display in the widget
	$errorLengthLimit = 300; // The maximum number of characters to display for each error
	$fileCleared = false;
	$userCanClearLog = current_user_can('manage_options');

	// Clear file?
	if ( $userCanClearLog && isset( $_GET["slt-php-errors"] ) && $_GET["slt-php-errors"]=="clear" ){
		$handle = fopen( $logfile, "w" );
		fclose( $handle );
		$fileCleared = true;
	}

	// Read file
	if ( file_exists( $logfile ) ){
		$errors = file( $logfile );
		$errors = array_reverse( $errors );
		if ( $fileCleared ) echo '<p><em>File cleared.</em></p>';
		if ( $errors ){
			echo '<p>'.count( $errors ).' error';
			if ( $errors != 1 ) echo 's';
			echo '.';
			if ( $userCanClearLog ) echo ' [ <b><a href="'.get_bloginfo("url").'/wp-admin/?slt-php-errors=clear" onclick="return confirm(\'Are you sure?\');">CLEAR LOG FILE</a></b> ]';
			echo '</p>';
			echo '<div id="slt-php-errors" style="height:250px;overflow:scroll;padding:2px;background-color:#faf9f7;border:1px solid #ccc;">';
			echo '<ol style="padding:0;margin:0;">';
			$i = 0;
			foreach ( $errors as $error ){
				echo '<li style="padding:2px 4px 6px;border-bottom:1px solid #ececec;">';
				$errorOutput = preg_replace( '/\[([^\]]+)\]/', '<b>[$1]</b>', $error, 1 );
				if ( strlen( $errorOutput ) > $errorLengthLimit ){
					echo substr( $errorOutput, 0, $errorLengthLimit ).' [...]';
				} else {
					echo $errorOutput;
				}
				echo '</li>';
				$i++;
				if ( $i > $displayErrorsLimit ){
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




