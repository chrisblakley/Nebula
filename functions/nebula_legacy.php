<?php

/*==========================
 This file includes functions that help backwards compatibility for previous versions.
 ===========================*/


//Renamed function (5/10/2016)
function the_breadcrumb(){
	nebula_breadcrumbs();
}


 //Update old options to new options
 add_action('admin_init', 'nebula_legacy_options');
 function nebula_legacy_options(){
	//Google Webmaster Tools is now Google Search Console
	if ( get_option('google_webmaster_tools_verification') && !get_option('google_search_console_verification') ){
		update_nebula_option('google_search_console_verification', get_option('google_webmaster_tools_verification'));
	}

	//Google Webmaster Tools is now Google Search Console
	if ( get_option('google_webmaster_tools_url') && !get_option('google_search_console_url') ){
		update_nebula_option('google_search_console_url', get_option('google_webmaster_tools_url'));
	}
 }