<?php

//Disable PHP Magic Quotes.
//Note: Even in PHP5.4+ and if get_magic_quotes_gpc() is false, this may STILL be needed. I don't know why.
//@TODO "Nebula" 0: Keep testing this like crazy to remove as soon as possible. This is needed for nebulaSession and nebulaUser cookies.
$process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
while ( list($key, $val) = each($process) ){
    foreach ( $val as $k => $v ){
        unset($process[$key][$k]);
        if ( is_array($v) ){
            $process[$key][stripslashes($k)] = $v;
            $process[] = &$process[$key][stripslashes($k)];
        } else {
            $process[$key][stripslashes($k)] = stripslashes($v);
        }
    }
}
unset($process);

//Extend registering scripts to include async/defer executions (used by the nebula_defer_async_scripts() funtion)
function nebula_register_script($handle=null, $src=null, $exec=null, $deps=array(), $ver=false, $in_footer=false){
	if ( !is_debug() ){
		$path = ( !empty($exec) )? $src . '?' . $exec : $src;
	} else {
		$path = $src;
	}
	wp_register_script($handle, $path, $deps, $ver, $in_footer);
}

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

//Defer and Async specific scripts. This only works with registered/enqueued scripts!
add_filter('script_loader_tag', 'nebula_defer_async_additional_scripts', 10);
function nebula_defer_async_additional_scripts($tag){
	$to_defer = array('jquery-migrate', 'jquery.form', 'contact-form-7', 'wp-embed'); //Scripts to defer. Strings can be anywhere in the filepath.
	$to_async = array(); //Scripts to async. Strings can be anywhere in the filepath.

	//Defer scripts
	if ( !empty($to_defer) ){
		foreach ( $to_defer as $script ){
			if ( strpos($tag, $script) ){
				return str_replace(' src', ' defer="defer" src', $tag);
			}
		}
	}

	//Async scripts
	if ( !empty($to_async) ){
		foreach ( $to_async as $script ){
			if ( strpos($tag, $script) ){
				return str_replace(' src', ' async="async" src', $tag);
			}
		}
	}

	return $tag;
}

//Remove version query strings from registered/enqueued styles/scripts (to allow caching)
add_filter('script_loader_src', 'nebula_remove_script_version', 15, 1);
add_filter('style_loader_src', 'nebula_remove_script_version', 15, 1);
function nebula_remove_script_version($src){
	return remove_query_arg('ver', $src);
}

//Dequeue certain scripts
//Important: Add a reason in comments to help future updates: Plugin Name - Reason
add_action('wp_print_scripts', 'nebula_dequeues', 9999);
add_action('wp_print_styles', 'nebula_dequeues', 9999);
function nebula_dequeues(){
	$override = apply_filters('pre_nebula_dequeues', false);
	if ( $override !== false ){return $override;}

	if ( !is_admin() ){
		//Styles
		wp_deregister_style('contact-form-7'); //Contact Form 7 - Not sure specifically what it is styling, so removing it unless we decide we need it.
		wp_dequeue_style('contact-form-7');

		//Page specific dequeues
		if ( is_front_page() ){
			wp_deregister_style('thickbox'); //WP Core Thickbox - Override if thickbox type gallery IS used on the homepage.
			wp_deregister_script('thickbox'); //WP Thickbox - Override if thickbox type gallery IS used on the homepage.
		}
	}
}

//Force settings within plugins
add_action('admin_init', 'nebula_plugin_force_settings');
function nebula_plugin_force_settings(){
	$override = apply_filters('pre_nebula_plugin_force_settings', false);
	if ( $override !== false ){return $override;}

	//Wordpress SEO (Yoast)
	if ( is_plugin_active('wordpress-seo/wp-seo.php') ){
		remove_submenu_page('wpseo_dashboard', 'wpseo_files'); //Remove the ability to edit files.
		$wpseo = get_option('wpseo');
		$wpseo['ignore_meta_description_warning'] = true; //Disable the meta description warning.
		$wpseo['ignore_tour'] = true; //Disable the tour.
		$wpseo['theme_description_found'] = false; //@TODO "Nebula" 0: Not working because this keeps getting checked/tested at many various times in the plugin.
		$wpseo['theme_has_description'] = false; //@TODO "Nebula" 0: Not working because this keeps getting checked/tested at many various times in the plugin.
		update_option('wpseo', $wpseo);

		//Disable update notifications
		remove_action('admin_notices', array(Yoast_Notification_Center::get(), 'display_notifications'));
		remove_action('all_admin_notices', array(Yoast_Notification_Center::get(), 'display_notifications'));
	}
}

//Override existing functions (typcially from plugins)
//Please add a comment with the reason for the override!
add_action('wp_print_scripts', 'nebula_remove_actions', 9999);
function nebula_remove_actions(){ //Note: Priorities much MATCH (not exceed) [default if undeclared is 10]
	if ( is_admin() ){ //WP Admin
		if ( is_plugin_active('event-espresso/espresso.php') ){
			remove_filter('admin_footer_text', 'espresso_admin_performance'); //Event Espresso - Prevent adding text to WP Admin footer
			remove_filter('admin_footer_text', 'espresso_admin_footer'); //Event Espresso - Prevent adding text to WP Admin footer
		}
	} else { //Frontend
		//remove_action('wpseo_head', 'debug_marker', 2 ); //Remove Yoast comment [not working] (not sure if second comment could be removed without modifying class-frontend.php)
	}
}

//Disable Emojis
add_action('init', 'disable_wp_emojicons');
function disable_wp_emojicons(){
	$override = apply_filters('pre_disable_wp_emojicons', false);
	if ( $override !== false ){return;}

	remove_action('admin_print_styles', 'print_emoji_styles');
	remove_action('wp_head', 'print_emoji_detection_script', 7);
	remove_action('admin_print_scripts', 'print_emoji_detection_script');
	remove_action('wp_print_styles', 'print_emoji_styles');
	remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
	remove_filter('the_content_feed', 'wp_staticize_emoji');
	remove_filter('comment_text_rss', 'wp_staticize_emoji');
}
add_filter('tiny_mce_plugins', 'disable_emojicons_tinymce'); //Remove TinyMCE Emojis too
function disable_emojicons_tinymce($plugins){
	if ( is_array($plugins) ){
		return array_diff($plugins, array('wpemoji'));
	} else {
		return array();
	}
}