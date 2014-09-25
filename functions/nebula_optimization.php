<?php

//Control which scripts use defer/async using a query string.
//Note: Not an ideal solution, but works until WP Core updates wp_enqueue_script(); to allow for deferring.
add_filter('clean_url', 'nebula_defer_async_scripts', 11, 1);
function nebula_defer_async_scripts($url) {
	if ( strpos($url, '.js?defer') === false && strpos($url, '.js?async') === false && strpos($url, '.js?gumby-debug') === false ) {
		return $url;
	}
	
	if ( strpos($url, '.js?defer') ) {
		return "$url' defer='defer";
	} elseif ( strpos($url, '.js?async') ) {
		return "$url' async='async";
	} elseif ( strpos($url, '.js?gumby-debug') ) {
		return "$url' gumby-debug='true";
	}
}


//Dequeue redundant files (from plugins)
//Important: Add a reason in comments to help future updates: Plugin Name - Reason
add_action('wp_print_scripts', 'nebula_dequeues', 9999);
add_action('wp_print_styles', 'nebula_dequeues', 9999);
function nebula_dequeues() {
	if ( !is_admin() ) {
		//Styles
		wp_deregister_style('open-sans'); //WP Core - We load Open Sans ourselves (or whatever font the project calls for)
		wp_deregister_style('dashicons'); //WP Core - Even though these are admin-only resources, I'd rather them not add any interpreted load time NOT WORKING!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		wp_deregister_style('cff-font-awesome'); //Custom Facebook Feed - We enqueue the latest version of Font Awesome ourselves
		wp_deregister_style('se-link-styles'); //Search Everything - (As far as I know) We do not use any of their styles (I believe they are for additional settings)
		wp_deregister_style('contact-form-7'); //Contact Form 7 - Not sure specifically what it is styling, so removing it unless we decide we need it.
		
		//Scripts
		if ( !preg_match('/(?i)msie [2-8]/', $_SERVER['HTTP_USER_AGENT']) ) { //WP Core - Dequeue jQuery Migrate for browsers that don't need it.
			wp_deregister_script('jquery');
			wp_register_script('jquery', false, array('jquery-core'), '1.11.0'); //Just have to make sure this version reflects the actual jQuery version bundled with WP (click the jquery.js link in the source)
		}
		
		//Page specific dequeues
		if ( is_front_page() ) {
			//Styles
			//wp_deregister_style('wp-pagenavi'); //WP PageNavi - Uncomment if pagination does NOT appear on the homepage.
			wp_deregister_style('thickbox'); //WP Core Thickbox - Comment out if thickbox type gallery IS used on the homepage.
			//wp_deregister_style('cff'); //Custom Facebook Feed - Uncomment if the Custom Facebook Feed does NOT appear on the homepage.
			
			//Scripts
			wp_deregister_script('thickbox'); //WP Thickbox - Comment out if thickbox type gallery IS used on the homepage.
			//wp_deregister_script('cffscripts'); //Custom Facebook Feed - Uncomment if the Custom Facebook Feed does NOT appear on the homepage.
			//wp_deregister_script('contact-form-7'); //Contact Form 7 - Uncomment if Contact Form 7 does NOT appear on the homepage.
			
			
		}
		
		/* @TODO: Styles/Scripts to consider for dequeue from homepage
			- admin-bar.min.js
		*/
	}
}

//Force settings within plugins
add_action('admin_init', 'nebula_plugin_force_settings');
function nebula_plugin_force_settings(){
	//WP Edit (This plugin is not bundled with Nebula anymore [in favor of TinyMCE Advanced], but this can stay in case it is ever used).
	if ( file_exists(WP_PLUGIN_DIR . '/wp-edit') ) {
		$plugin_options_global = get_option('wp_edit_global');
		$plugin_options_global['disable_admin_links'] = 1;
		update_option('wp_edit_global', $plugin_options_global);
	}
	//Search Everything
	if ( file_exists(WP_PLUGIN_DIR . '/search-everything') ) {
		$se_options = get_option('se_options');
	    $se_options['se_use_highlight'] = false; //Disable search keyword highlighting (to prevent interference with Nebula keyword highlighting)
	    update_option('se_options', $se_options);
	}
	//Wordpress SEO (Yoast)
	if ( file_exists(WP_PLUGIN_DIR . '/wordpress-seo') ) {
		remove_submenu_page('wpseo_dashboard', 'wpseo_files'); //Remove the ability to edit files.
		$wpseo = get_option('wpseo');
		$wpseo['ignore_meta_description_warning'] = true; //Disable the meta description warning.
		$wpseo['ignore_tour'] = true; //Disable the tour.
		$wpseo['theme_description_found'] = false; //@TODO: Not working because this keeps getting checked/tested at many various times in the plugin.
		$wpseo['theme_has_description'] = false; //@TODO: Not working because this keeps getting checked/tested at many various times in the plugin.
		update_option('wpseo', $wpseo);
	}
}


//Override existing functions (typcially from plugins)
//Please add a comment with the reason for the override!
add_action('wp_print_scripts', 'nebula_remove_actions', 9999);
function nebula_remove_actions(){ //Note: Priorities much MATCH (not exceed) [default if undeclared is 10]
	if ( !is_admin() ) {
		//Frontend
		//remove_action('wpseo_head', 'debug_marker', 2 ); //Remove Yoast comment [not working] (not sure if second comment could be removed without modifying class-frontend.php)
		remove_action('wp_head', '_admin_bar_bump_cb'); //Admin bar <style> bump
		if ( get_the_ID() == 1 ) { remove_action('wp_footer', 'cff_js', 10); } //Custom Facebook Feed - Remove the feed from the homepage. @TODO: Update to any page/post type that should NOT have the Facebook Feed
	} else {
		//WP Admin
		remove_filter('admin_footer_text', 'espresso_admin_performance'); //Event Espresso - Prevent adding text to WP Admin footer
		remove_filter('admin_footer_text', 'espresso_admin_footer'); //Event Espresso - Prevent adding text to WP Admin footer
		remove_meta_box('espresso_news_dashboard_widget', 'dashboard', 'normal'); //Event Espresso - Remove Dashboard Metabox
		remove_meta_box('jwl_user_tinymce_dashboard_widget', 'dashboard', 'normal'); //WP Edit - Remove Dashboard Metabox (This plugin is not bundled with Nebula anymore [in favor of TinyMCE Advanced], but this can stay in case it is ever used).
		//remove_action('init', 'wpseo_description_test'); //Wordpress SEO (Yoast) - Remove Meta Description test (@TODO: Not Working - this function is called all over the place...)
	}
}
