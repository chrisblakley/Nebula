<?php

/*==========================
 This file includes functions that provide limited backwards compatibility for previous versions.
 ===========================*/

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Legacy') ){
	trait Legacy {
		public function hooks(){

		}

		//Renamed function
		public function prerender(){ $this->prebrowsing(); }
	}
}

/*==========================
 Procedural Functions
 ===========================*/

//Renamed function (5/10/2016)
function nebula_breadcrumbs(){ nebula()->breadcrumbs(); }
function the_breadcrumb(){ nebula()->breadcrumbs(); }

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

//Prefer a child theme directory or file. Not declaring a directory will return the theme directory.
//nebula_prefer_child_directory('/assets/img/logo.png');
//This was replaced by: get_theme_file_uri() and get_theme_file_path() in WordPress 4.7
function nebula_prefer_child_directory($directory='', $uri=true){
	if ( $directory[0] != '/' ){
		$directory = '/' . $directory;
	}

	if ( file_exists(get_stylesheet_directory() . $directory) ){
		if ( $uri ){
			return get_stylesheet_directory_uri() . $directory;
		}
		return get_stylesheet_directory() . $directory;
	}

	if ( $uri ){
		return get_template_directory_uri() . $directory;
	}
	return get_template_directory() . $directory;
}

global $wp_version;
if ( $wp_version < 4.7 ){
	function get_theme_file_uri(){
		return nebula_prefer_child_directory();
	}

	function get_theme_file_path(){
		return nebula_prefer_child_directory('', false);
	}
}

//Old Nebula Excerpt that does not use the options array.
function nebula_the_excerpt($postID=0, $more=0, $length=55, $hellip=0){
	$override = apply_filters('pre_nebula_the_excerpt', null, $postID, $more, $length, $hellip);
	if ( isset($override) ){return;}

	if ( $postID && is_int($postID) ){
		$the_post = get_post($postID);
	} else {
		if ( $postID != 0 || is_string($postID) ){
			$hellip = false;
			if ( $length == 0 || $length == 1 ){
				$hellip = $length;
			}

			$length = 55;
			if ( is_int($more) ){
				$length = $more;
			}

			$more = $postID;
		}
		$postID = get_the_ID();
		$the_post = get_post($postID);
	}

	$post_text = ( !empty($the_post->post_excerpt) )? $the_post->post_excerpt : $the_post->post_content;

	return nebula_excerpt(array(
		'length' => $length,
		'ellipsis' => $hellip,
		'url' => get_permalink($postID),
		'more' => $more,
		'text' => $post_text,
	));
}

function nebula_custom_excerpt($text=false, $length=55, $hellip=false, $link=false, $more=false){
	return nebula_excerpt(array(
		'text' => $text,
		'url' => $link,
		'more' => $more,
		'length' => $length,
		'ellipsis' => $hellip
	));
};

function nebula_google_font_option(){
	$nebula_options = get_option('nebula_options');
	if ( $nebula_options['remote_font_url'] ){
		return preg_replace("/(<link href=')|(' rel='stylesheet' type='text\/css'>)|(@import url\()|(\);)/", '', $nebula_options['remote_font_url']);
	} elseif ( $nebula_options['google_font_family'] ) {
		$google_font_family = preg_replace('/ /', '+', $nebula_options['google_font_family']);
		$google_font_weights = preg_replace('/ /', '', $nebula_options['google_font_weights']);
		$response = wp_remote_get('https://fonts.googleapis.com/css?family=' . $google_font_family . ':' . $google_font_weights);
		if ( is_wp_error($response) ){
			return false;
		}
		$google_font_contents = $response['body'];
		if ( $google_font_contents !== false ){
			return $google_font_contents;
		}
	}
	return false;
}