<?php
/*
	A wireframing element is composed of a component which contains one or more fpo elements.
	For example:
	<?php fpo_component('Component Name'); ?>
	<?php fpo_component_start('Component Name'); ?>
	<p>This is an example FPO element. Elements inside of the component can be whatever HTML/PHP/JS that is needed!</p>
	<?php fpo_component_end(); ?>
*/

//Add wireframing body class
add_filter('body_class', 'nebula_wireframing_body_classes');
function nebula_wireframing_body_classes($classes){
    if ( nebula_wireframing_enabled() ){
    	$classes[] = 'nebula-wireframing';
	}
	return $classes;
}

//Wireframe Top Bar
if ( !nebula_options_conditional('nebula_wireframing', 'disabled') ){
	add_action('nebula_body_open', 'wireframe_bar');
}
function wireframe_bar(){
	$current_user = wp_get_current_user();
	$current_user_name = ( $current_user->user_firstname != '' )? $current_user->user_firstname : $current_user->display_name;
	$greetings = array('Hello', 'Hi', 'Hey', 'Welcome');

	echo '<div id="wireframing-bar" class="container">
		<div class="row">
			<div class="sixteen columns">
				<ul class="two_up tiles">
					<li><a class="phg" href="http://www.pinckneyhugo.com/" target="_blank"><span class="pinckney">Pinckney</span><span class="hugo">Hugo</span><span class="group">Group</span></a></li>
					<li style="text-align: right;">';
					if ( is_user_logged_in() ){
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

	if ( !nebula_options_conditional('nebula_wireframing', 'disabled') ){
		return false;
	}

	if ( 1==2 ){ //@TODO "Nebula" 0: If there are more than one comments
		$comment_icon = 'fa-comments';
	} elseif ( 1==2 ){ //@TODO "Nebula" 0: If there is only one comment
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
	if ( !nebula_options_conditional('nebula_wireframing', 'disabled') ){
		return false;
	}
	fpo_component($component, $icon, '');
	echo '<div class="fpo clearfix">';
}

//Closes .fpo div (from fpo_component_start)
function fpo_component_end(){
	if ( !nebula_options_conditional('nebula_wireframing', 'disabled') ){
		return false;
	}
	echo '</div><!-- /fpo -->';
}

//Create a placeholder box as an FPO element
function fpo($title='FPO', $description='', $width='100%', $height="250px", $bg='#ddd', $icon='', $styles='', $classes=''){
	$safe_title = strtolower(str_replace(' ', '-', $title));

	if ( nebula_color_brightness($bg) < 128 ){
		$text_hex = '#fff';
		$text_rgb = '255';
	} else {
		$text_hex = '#000';
		$text_rgb = '0';
	}

	if ( $bg == 'placeholder' ){
		$bg = '';
		$placeholder = '<svg x="0px" y="0px" width="100%" height="100%" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 1px solid #aaa; z-index: 1;">
			<line fill="none" stroke="#aaa" stroke-miterlimit="10" x1="0" y1="0" x2="100%" y2="100%"/>
			<line fill="none" stroke="#aaa" stroke-miterlimit="10" x1="100%" y1="0" x2="0" y2="100%"/>
		</svg>';
	} else {
		$placeholder = '';
	}

	$icon_html = '';
	if ( $icon != '' ){
		if ( strpos($icon, 'fa-') === false ){
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

//Placeholder image... Consider deprecating this function
function fpo_image($width='100%', $height='200px', $type='none', $color='#000', $styles='', $classes=''){
	$imgsrc = '';
	if ( $type == 'unsplash' || $type == 'photo' || $width == 'unsplash' || $width == 'photo' ){
		$imgsrc = random_unsplash(800, 600, 1);
	} elseif ( strpos($type, '#') !== false ){
		$color = $type;
	}

	if ( !isset($color) || $color == '' ){
		$color='#000';
	}

	$return = '<div class="nebula-fpo-image ' . $classes . '" style="background: url(' . $imgsrc . ') no-repeat; background-size: 100% 100%; width: ' . $width . '; height: ' . $height . '; ' . $styles . '">';

	if ( $imgsrc == '' ){
		$return .= '<svg x="0px" y="0px" width="100%" height="100%" style="border: 1px solid ' . $color . ';">
			<line fill="none" stroke="' . $color . '" stroke-miterlimit="10" x1="0" y1="0" x2="100%" y2="100%"/>
			<line fill="none" stroke="' . $color . '" stroke-miterlimit="10" x1="100%" y1="0" x2="0" y2="100%"/>
		</svg>';
	}
	$return .= '</div>';
	echo $return;
}

function fpo_text($text=''){
	echo '<div class="fpo-text">' . $text . '</div>';
}