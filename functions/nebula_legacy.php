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




//Old Nebula Excerpt that does not use the options array.
function nebula_the_excerpt($postID=0, $more=0, $length=55, $hellip=0){
	$override = apply_filters('pre_nebula_the_excerpt', false, $postID, $more, $length, $hellip);
	if ( $override !== false ){return $override;}

	if ( $postID && is_int($postID) ){
		$the_post = get_post($postID);
	} else {
		if ( $postID != 0 || is_string($postID) ){
			if ( $length == 0 || $length == 1 ){
				$hellip = $length;
			} else {
				$hellip = false;
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





//Retarget users based on prior conversions/leads (Modified to work with new DB storage method)
function nebula_retarget($category=false, $data=null, $strict=true, $return=false){
	$response = nebula_get_visitor_data($category);

	if ( $strict ){
		if ( $response == $data ){
			if ( !empty($return) ){
				return $response;
			}

			return true;
		}
	} else {
		if ( strpos($response, $data) !== false ){
			if ( !empty($return) ){
				return $response;
			}

			return true;
		}
	}

	return false;
}



