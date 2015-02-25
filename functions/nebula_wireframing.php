<?php


//Add wireframing body class
add_filter('body_class', 'nebula_wireframing_body_classes');
function nebula_wireframing_body_classes($classes) {
    $classes[] = 'nebula-wireframing';
    return $classes;
}


//Create a placeholder box as an FPO element
function fpo($title='FPO', $description='', $width='100%', $height="250px", $bg='#ddd', $icon='', $styles='', $classes='') {
	$safe_title = strtolower(str_replace(' ', '-', $title));

	if ( nebula_color_brightness($bg) < 128 ) {
		$text_hex = '#fff';
		$text_rgb = '255';
	} else {
		$text_hex = '#000';
		$text_rgb = '0';
	}

	if ( $bg == 'placeholder' ) {
		$bg = '';
		$placeholder = '<svg x="0px" y="0px" width="100%" height="100%" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 1px solid #aaa; z-index: 1;">
							<line fill="none" stroke="#aaa" stroke-miterlimit="10" x1="0" y1="0" x2="100%" y2="100%"/>
							<line fill="none" stroke="#aaa" stroke-miterlimit="10" x1="100%" y1="0" x2="0" y2="100%"/>
						</svg>';
	} else {
		$placeholder = '';
	}

	$icon_html = '';
	if ( $icon != '' ) {
		if ( strpos($icon, 'fa-') === false ) {
			$icon = 'fa-' . $icon;
		}
		$icon_html = '<i class="fa ' . $icon . '"></i>';
	}

	$return .= '<div class="nebula-fpo ' . $safe_title . ' valign ' . $classes . '" style="position: relative; text-align: center; width: ' . $width . '; height: ' . $height . '; padding: 10px; background: ' . $bg . '; ' . $styles . '">
			<div style="position: relative; z-index: 5;">
				<h3 style="font-size: 21px; color: ' . $text_hex . ';">' . $icon_html . ' ' . $title . '</h3>
				<p style="font-size: 14px; color: rgba(' . $text_rgb . ',' . $text_rgb . ',' . $text_rgb . ',0.6);">' . $description . '</p>
			</div>
			' . $placeholder . '
		</div>';

	echo $return;
}

//Placeholder image
function fpo_image($width='100%', $height='200px', $type='none', $color='#000', $styles='', $classes='') {
	$imgsrc = '';
	if ( $type == 'unsplash' || $type == 'photo' || $width == 'unsplash' || $width == 'photo' ) {
		$imgsrc = random_unsplash(800, 600, 1);
	} elseif ( strpos($type, '#') !== false ) {
		$color = $type;
	}

	$return = '<div class="nebula-fpo-image ' . $classes . '" style="background: url(' . $imgsrc . ') no-repeat; background-size: 100% 100%; width: ' . $width . '; height: ' . $height . '; ' . $styles . '">';

	if ( $imgsrc == '' ) {
		$return .= '<svg x="0px" y="0px" width="100%" height="100%" style="border: 1px solid ' . $color . ';">
					<line fill="none" stroke="' . $color . '" stroke-miterlimit="10" x1="0" y1="0" x2="100%" y2="100%"/>
					<line fill="none" stroke="' . $color . '" stroke-miterlimit="10" x1="100%" y1="0" x2="0" y2="100%"/>
				</svg>';
	}

	$return .= '</div>';

	echo $return;
}


//Placeholder form
function fpo_form($fields=array('Name', 'Email', 'Message'), $submit="Send", $action=null) {

	if ( !isset($action) ) {
		$action = get_template_directory_uri() . '/includes/mailer.php';
		echo '<script>
			jQuery(document).ready(function() {
				jQuery(document).on("submit", "#nebula-fpo-form", function(e){
					var contactData = [{
						"name": jQuery("#nebula-fpo-form input.name").val(),
						"email": jQuery("#nebula-fpo-form input.email").val(),
						"message": jQuery("#nebula-fpo-form textarea.message").val(),
					}];

					jQuery("#form-messages").html("<i class=\"fa fa-spinner fa-spin sending\"></i> Sending...");

					jQuery.ajax({
						type: "POST",
						url: "' . get_template_directory_uri() . '/includes/mailer.php",
						data: {
							data: contactData,
						},
						success: function(response){
							if ( response.indexOf("Thank you") > -1 ) {
								jQuery("#nebula-fpo-form input:not(#contact-submit), #nebula-fpo-form textarea").val("").trigger("keyup");
								jQuery("#nebula-fpo-form").slideUp();
							}
							jQuery("#form-messages").html(response);
						},
						error: function(MLHttpRequest, textStatus, errorThrown){
							jQuery("#form-messages").text(errorThrown);
						},
						timeout: 60000
					});
					e.preventDefault();
					return false;
				});
			});
		</script>';
	}

	$return = '<form id="nebula-fpo-form" name="nebula-fpo-form" class="nebula-fpo-form" method="POST" action="' . $action . '"><ul>';
	foreach ( $fields as $field ) {
		$safe_field = strtolower(str_replace(' ', '-', $field));
		if ( $field == 'Message' || $field == 'Comments' ) {
			$return .= '<li class="field"><span class="contact-form-heading">' . $field . '</span><textarea class="input textarea ' . $safe_field . '" placeholder="' . $field . '" style="resize: vertical; min-height: 150px;"></textarea></li>';
		} elseif ( $field == 'Email' ) {
			$return .= '<li class="field"><span class="contact-form-heading">' . $field . '</span><input class="input ' . $safe_field . '" type="email" placeholder="' . $field . '" /></li>';
		} else {
			$return .= '<li class="field"><span class="contact-form-heading">' . $field . '</span><input class="input ' . $safe_field . '" type="text" placeholder="' . $field . '" /></li>';
		}
	}
	$return .= '<li class="field" style="text-align: right;"><input class="submit primary btn medium" type="submit" value="' . $submit . '" style="max-width: 100px;"></li></ul></form><div id="form-messages"></div>';

	echo $return;

}


//Placeholder slider
//@TODO "Nebula" 0: Pass an object to set options.
function fpo_slider($slides=3) {
	$return = '<div class="nebula-fpo-slider"><ul class="bxslider fposlider">';
	if ( is_int($slides) ) {
		$i = 1;
		while ( $i <= $slides ) {
			$return .= '<li><img class="random-unsplash" src="' . random_unsplash(800, 400) . '" alt="Slide ' . $i . '" /></li>';
			$i++;
		}
	} else {
		foreach ( $slides as $slide ) {
			$return .= '<li><img src="' . $slide . '" /></li>';
		}
	}
	$return .= '</ul></div><!--/nebula-fpo-slider-->';

	$return .= '<script>
		jQuery(window).on("load", function() {
			setTimeout(function(){
				jQuery(".fposlider").bxSlider({
					mode: "fade",
					speed: 800,
					captions: false,
					pager: false,
					auto: false,
					pause: 8000,
					autoHover: true,
					adaptiveHeight: true,
					useCSS: true,
					controls: true
				});
			}, 1000);
		});
	</script>';
	echo $return;
}


//Placeholder video
function fpo_video($id='jtip7Gdcf0Q', $service='youtube', $width='100%', $height='315') {
	if ( $service == 'vimeo' || $service == 'Vimeo' ) {
		vimeo_meta($id);
		echo '<iframe id="' . $GLOBALS['vimeo_meta']['safetitle'] . '" class="vimeoplayer" src="http://player.vimeo.com/video/' . $GLOBALS['vimeo_meta']['id'] . '?api=1&player_id=' . $GLOBALS['vimeo_meta']['safetitle'] . '" width="' . $width . '" height="' . $height . '" autoplay="1" badge="1" byline="1" color="00adef" loop="0" portrait="1" title="1" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
	} else {
		youtube_meta($id);
		echo '<iframe id="' . $GLOBALS['youtube_meta']['safetitle'] . '" class="youtubeplayer" width="' . $width . '" height="' . $height . '" src="https://www.youtube.com/embed/' . $GLOBALS['youtube_meta']['id'] . '?wmode=transparent&enablejsapi=1&origin=' . $GLOBALS['youtube_meta']['origin'] . '" frameborder="0" allowfullscreen=""></iframe>';
	}
}










/*********
	New FPO Functions 11/21/14
**********/

if ( !nebula_settings_conditional('nebula_wireframing', 'disabled') ) {
	add_action('nebula_body_open', 'wireframe_bar');
}
function wireframe_bar(){
	$current_user = wp_get_current_user();
	$current_user_name = ( $current_user->user_firstname != '' ) ? $current_user->user_firstname : $current_user->display_name;
	$greetings = array('Hello', 'Hi', 'Hey', 'Welcome');

	echo '<div id="wireframing-bar" class="container">
		<div class="row">
			<div class="sixteen columns">
				<ul class="two_up tiles">
					<li><a class="phg" href="http://www.pinckneyhugo.com/" target="_blank"><span class="pinckney">Pinckney</span><span class="hugo">Hugo</span><span class="group">Group</span></a></li>
					<li style="text-align: right;">';
					if ( is_user_logged_in() ) {
						echo '<span>' . $greetings[array_rand($greetings)] . ', <a href="' . get_admin_url() . '"><strong>' . $current_user_name . '</strong></a>.</span>';
					} else {
						echo '<span><a href="' . wp_login_url(get_permalink()) . '"><strong>Login</strong></a></span>';
					}
					echo '</li>
				</ul>
			</div>
		</div>
	</div>';
}


//Top header for each component
function fpo_component($component='Component', $icon='fa-cube', $open='-open'){

	if ( 1==2 ) { //@TODO "Nebula" 0: If there are more than one comments
		$comment_icon = 'fa-comments';
	} elseif ( 1==2 ) { //@TODO "Nebula" 0: If there is only one comment
		$comment_icon = 'fa-comment';
	} else {
		$comment_icon = 'fa-comment-o';
	}

	echo '<div class="fpo-component-con fpo-component' . $open . '">
		<div class="component-name fpo-' . strtolower(str_replace(' ', '-', $component)) . '">
			<i class="component-icon fa ' . $icon . '"></i> <strong>' . $component . '</strong><a class="component-comment-toggle" href="#"><i class="component-icon fa ' . $comment_icon . '"></i></a>
		</div><!-- /component-name -->
		<div class="component-comment-drawer">
			<div class="nebulashadow bulging" style="height: 10px;"></div>
			<strong class="comment-header">0 Comments</strong>
			<p>Comment functionality coming soon.</p>
		</div>
	</div><!-- /fpo-component (' . $component . ') -->';

}

//Top header for each component (with opening .fpo div)
function fpo_component_start($component='Component', $icon='fa-cube'){
	fpo_component($component, $icon, '');
	echo '<div class="fpo clearfix">';
}

//Closes .fpo div (from fpo_component_start)
function fpo_component_end(){
	echo '</div><!-- /fpo -->';
}

//Placeholder breadcrumbs
//$crumbs parameter will need to be passed as an array('', '', '').
function fpo_breadcrumbs($crumbs=''){
	if ( $crumbs == '' ) {
		echo '<div class="fpo-bcrumbs">' . the_breadcrumb() . '</div>'; //@TODO "Nebula" 0: the_breadcrumb() needs to return, not echo...
	} else {
		echo '<div id="fpo-bcrumbs"><nav class="breadcrumbs"><a href="' . home_url('/') . '"><i class="fa fa-home"></i></a>';
		$crumb_count = count($crumbs)-1;
		$crumb_iteration = 0;
		foreach ( $crumbs as $crumb ) {
			if ( $crumb_iteration != $crumb_count ) {
				echo ' <span class="arrow">›</span> <a href="#">' . $crumbs[$crumb_iteration] . '</a>';
			} else {
				echo ' <span class="arrow">›</span> <span class="current">' . $crumbs[$crumb_iteration] . '</span>';
			}
			$crumb_iteration++;
		}
		echo '</nav></div>';
	}
}





function fpo_menu($name='header', $items=array()){
	if ( $items ) { //Should detect if $name is an array- if so, it's actually $items
		//Do stuff here
	} else {
		//echo '<div class="fpo fpo-menu"><nav>' . wp_get_nav_menu(array('theme_location' => $name, 'depth' => '9999')) . '</nav></div>'; //@TODO "Nebula" 0: wp_get_nav_menu() immediately echoes. We need one that returns. wp_get_nav_menu_items() returns, but needs menu ID (not theme locations)- maybe that's ok.
	}
	echo '<div class="fpo-menu">in progress...</div>';
}


function fpo_social_links($accounts=array('Facebook', 'Twitter', 'Google+', 'LinkedIn', 'Youtube', 'Instagram')){
	echo '<div class="fpo-social-links">';
	foreach ( $accounts as $account ) {
		switch ( strtolower($account) ) {
			case ( 'facebook' ) :
			case ( 'fb' ) :
				echo '<a class="fpo-social-link-item facebook" href="#"><i class="fa fa-facebook-square"></i></a>';
				break;
			case ( 'twitter' ) :
				echo '<a class="fpo-social-link-item twitter" href="#"><i class="fa fa-twitter-square"></i></a>';
				break;
			case ( 'google+' ) :
			case ( 'google plus' ) :
			case ( 'googleplus' ) :
			case ( 'google_plus' ) :
			case ( 'google-plus' ) :
			case ( 'gplus' ) :
			case ( 'g+' ) :
				echo '<a class="fpo-social-link-item google-plus" href="#"><i class="fa fa-google-plus-square"></i></a>';
				break;
			case ( 'linkedin' ) :
			case ( 'linked in' ) :
				echo '<a class="fpo-social-link-item linkedin" href="#"><i class="fa fa-linkedin-square"></i></a>';
				break;
			case ( 'youtube' ) :
				echo '<a class="fpo-social-link-item youtube" href="#"><i class="fa fa-youtube"></i></a>';
				break;
			case ( 'instagram' ) :
				echo '<a class="fpo-social-link-item instagram" href="#"><i class="fa fa-instagram"></i></a>';
				break;
			default :
				if ( strpos($account, 'fa-') > -1 ) {
					echo '<a class="fpo-social-link-item custom-fa-icon" href="#"><i class="fa ' . $account . '"></i></a>';
				} elseif ( strpos($account, 'icon-') > -1 ) {
					echo '<a class="fpo-social-link-item custom-entypo-icon" href="#"><i class="' . $account . '"></i></a>';
				} elseif ( strpos($account, 'http') > -1 ) {
					echo '<a class="fpo-social-link-item custom-image" href="#"><img src="' . $account . '" width="16" height="16" /></a>';
				}
				break;
		}
	}
	echo '</div>';
}


function fpo_social_share($accounts=array('Facebook', 'Twitter', 'Google+', 'LinkedIn', 'Youtube', 'Instagram', 'Email')){
	echo '<div class="fpo-social-share">';
	foreach ( $accounts as $account ) {
		switch ( strtolower($account) ) {
			case ( 'facebook' ) :
			case ( 'fb' ) :
			case ( 'like' ) :
				echo '<a class="fpo-social-share-item facebook" href="#">[Like]</a>';
				break;
			case ( 'twitter' ) :
			case ( 'tweet' ) :
				echo '<a class="fpo-social-share-item twitter" href="#">[Tweet]</a>';
				break;
			case ( 'google+' ) :
			case ( 'google plus' ) :
			case ( 'googleplus' ) :
			case ( 'google_plus' ) :
			case ( 'google-plus' ) :
			case ( 'gplus' ) :
			case ( 'g+' ) :
			case ( '+1' ) :
			case ( 'plus one' ) :
				echo '<a class="fpo-social-share-item google-plus" href="#">[+1]</a>';
				break;
			case ( 'linkedin' ) :
			case ( 'linked in' ) :
				echo '<a class="fpo-social-share-item linkedin" href="#">[LinkedIn]</a>';
				break;
			case ( 'pinterest' ) :
				echo '<a class="fpo-social-share-item pinterest" href="#">[Pinterest]</a>';
				break;
			case ( 'email' ) :
				echo '<a class="fpo-social-share-item email" href="#"><i class="fa fa-envelope"></i></a>';
				break;
			default :
				if ( strpos($account, 'fa-') > -1 ) {
					echo '<a class="fpo-social-share-item custom-fa-icon" href="#"><i class="fa ' . $account . '"></i></a>';
				} elseif ( strpos($account, 'icon-') > -1 ) {
					echo '<a class="fpo-social-share-item custom-entypo-icon" href="#"><i class="' . $account . '"></i></a>';
				} elseif ( strpos($account, 'http') > -1 ) {
					echo '<a class="fpo-social-share-item custom-image" href="#"><img src="' . $account . '" width="16" height="16" /></a>';
				}
				break;
		}
	}
	echo '</div>';
}


function fpo_text($text='') {
	echo '<div class="fpo-text">' . $text . '</div>';
}


//eCommerce suite, ad buckets, lightbox,