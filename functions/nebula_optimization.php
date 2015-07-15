<?php

//Add IE compatibility header
header("X-UA-Compatible: IE=edge");

//Control which scripts use defer/async using a query string.
//Note: Not an ideal solution, but works until WP Core updates wp_enqueue_script(); to allow for deferring.
add_filter('clean_url', 'nebula_defer_async_scripts', 11, 1);
function nebula_defer_async_scripts($url){
	if ( strpos($url, '.js?defer') === false && strpos($url, '.js?async') === false && strpos($url, '.js?gumby-debug') === false ){
		return $url;
	}

	if ( strpos($url, '.js?defer') ){
		return "$url' defer='defer";
	} elseif ( strpos($url, '.js?async') ){
		return "$url' async='async";
	} elseif ( strpos($url, '.js?gumby-debug') ){
		return "$url' gumby-debug='true";
	}
}

//Enqueue required scripts strictly as needed
add_action('comment_form_before', 'nebula_enqueue_comments_reply');
function nebula_enqueue_comments_reply(){
	if ( get_option('thread_comments') ){
		wp_enqueue_script('comment-reply');
	}
}

//Remove version query strings from styles/scripts (to allow caching)
add_filter('script_loader_src', 'nebula_remove_script_version', 15, 1);
add_filter('style_loader_src', 'nebula_remove_script_version', 15, 1);
function nebula_remove_script_version($src){
	return remove_query_arg('ver', $src);
}

//Dequeue redundant files (from plugins)
//Important: Add a reason in comments to help future updates: Plugin Name - Reason
add_action('wp_print_scripts', 'nebula_dequeues', 9999);
add_action('wp_print_styles', 'nebula_dequeues', 9999);
function nebula_dequeues(){
	if ( !is_admin() ){
		//Styles
		wp_deregister_style('open-sans'); //WP Core - We load Open Sans ourselves (or whatever font the project calls for)
		wp_deregister_style('cff-font-awesome'); //Custom Facebook Feed - We enqueue the latest version of Font Awesome ourselves
		wp_deregister_style('contact-form-7'); //Contact Form 7 - Not sure specifically what it is styling, so removing it unless we decide we need it.

		//Scripts
		if ( !preg_match('/(?i)msie [2-8]/', $_SERVER['HTTP_USER_AGENT']) ){ //WP Core - Dequeue jQuery Migrate for browsers that don't need it.
			wp_deregister_script('jquery');
			wp_register_script('jquery', false, array('jquery-core'), '1.11.0'); //Just have to make sure this version reflects the actual jQuery version bundled with WP (click the jquery.js link in the source)
		}

		//Page specific dequeues
		if ( is_front_page() ){
			//Styles
			wp_deregister_style('thickbox'); //WP Core Thickbox - Comment out if thickbox type gallery IS used on the homepage.

			//Scripts
			wp_deregister_script('thickbox'); //WP Thickbox - Comment out if thickbox type gallery IS used on the homepage.
		}
	}
}

//Force settings within plugins
add_action('admin_init', 'nebula_plugin_force_settings');
function nebula_plugin_force_settings(){
	//Wordpress SEO (Yoast)
	if ( file_exists(WP_PLUGIN_DIR . '/wordpress-seo') ){
		remove_submenu_page('wpseo_dashboard', 'wpseo_files'); //Remove the ability to edit files.
		$wpseo = get_option('wpseo');
		$wpseo['ignore_meta_description_warning'] = true; //Disable the meta description warning.
		$wpseo['ignore_tour'] = true; //Disable the tour.
		$wpseo['theme_description_found'] = false; //@TODO "Nebula" 0: Not working because this keeps getting checked/tested at many various times in the plugin.
		$wpseo['theme_has_description'] = false; //@TODO "Nebula" 0: Not working because this keeps getting checked/tested at many various times in the plugin.
		update_option('wpseo', $wpseo);
	}
}

//Override existing functions (typcially from plugins)
//Please add a comment with the reason for the override!
add_action('wp_print_scripts', 'nebula_remove_actions', 9999);
function nebula_remove_actions(){ //Note: Priorities much MATCH (not exceed) [default if undeclared is 10]
	if ( !is_admin() ){
		//Frontend
		//remove_action('wpseo_head', 'debug_marker', 2 ); //Remove Yoast comment [not working] (not sure if second comment could be removed without modifying class-frontend.php)
		remove_action('wp_head', '_admin_bar_bump_cb'); //Admin bar <style> bump
		if ( get_the_ID() == 1 ){ remove_action('wp_footer', 'cff_js', 10); } //Custom Facebook Feed - Remove the feed from the homepage. @TODO "Plugins" 2: Update to any page/post type that should NOT have the Facebook Feed
	} else {
		//WP Admin
		remove_filter('admin_footer_text', 'espresso_admin_performance'); //Event Espresso - Prevent adding text to WP Admin footer
		remove_filter('admin_footer_text', 'espresso_admin_footer'); //Event Espresso - Prevent adding text to WP Admin footer
		remove_meta_box('espresso_news_dashboard_widget', 'dashboard', 'normal'); //Event Espresso - Remove Dashboard Metabox
		//remove_action('init', 'wpseo_description_test'); //Wordpress SEO (Yoast) - Remove Meta Description test (@TODO "Nebula" 0: Not Working - this function is called all over the place...)
		//remove_action('admin_init', 'after_update_notice', 15); //Wordpress SEO (Yoast) - Remove "WordPress SEO by Yoast has been updated" box (@TODO "Nebula" 0: Not Working)

		//global $WPSEO_Admin_Init; //@TODO "Nebula" 0: Test this next time the box appears after an update.
		//remove_action('admin_init', array($WPSEO_Admin_Init, 'after_update_notice'), 15); //@TODO "Nebula" 0: Test this next time the box appears after an update... didnt work


	}
}