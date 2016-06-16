<?php
/*
	A wireframing element is composed of a component which contains one or more fpo elements.
	For example:
	<?php fpo_component('Component Name'); ?>
	<?php fpo_component_start('Component Name'); ?>
	<p>This is an example FPO element. Elements inside of the component can be whatever HTML/PHP/JS that is needed!</p>
	<?php fpo_component_end(); ?>
*/

if ( nebula_option('prototype_mode', 'enabled') ){
	add_action('wp_enqueue_scripts', 'enqueue_nebula_wireframing');
	function enqueue_nebula_wireframing(){
		wp_register_style('nebula-wireframing', get_template_directory_uri() . '/stylesheets/css/wireframing.css', array('nebula-main'), null);
		nebula_register_script('nebula-wireframing', get_template_directory_uri() . '/js/wireframing.js', 'defer', array('nebula-main'), null, true);

		wp_enqueue_style('nebula-wireframing');
		wp_enqueue_script('nebula-wireframing');
	}

	//Set up redirects based on the ?phase query.
	if ( is_plugin_active('jonradio-multiple-themes/jonradio-multiple-themes.php') ){
		$mt_settings = get_option('jr_mt_settings');

		$mt_settings['query'] = array(
			'phase' => array(
				'wireframe' => nebula_option('wireframe_theme'), //Wireframe Theme
				'staging' => nebula_option('staging_theme'), //Staging Theme
				'production' => nebula_option('production_theme') //Production Theme
			)
		);

		$mt_settings['current'] = nebula_option('production_theme'); //"Everything Else" theme
		$mt_settings['ajax_all'] = ( nebula_option('staging_theme') )? nebula_option('staging_theme') : nebula_option('production_theme'); //Theme for AJAX functions. Staging (if exists) or Production
		$mt_settings['remember'] = array('query' => array('phase' => array('wireframe' => true, 'staging' => true)));
		$mt_settings['override'] = array('query' => array('phase' => array('production' => true)));
		update_option('jr_mt_settings', $mt_settings);
	}

	//Add wireframing body class
	add_filter('body_class', 'nebula_wireframing_body_classes');
	function nebula_wireframing_body_classes($classes){
	    $classes[] = 'nebula-wireframing';

		if ( nebula_dev_phase() == 'wireframe' ){
			$classes[] = 'nebula-wireframing-wireframe';
		} elseif ( nebula_dev_phase() == 'staging' ){
			$classes[] = 'nebula-wireframing-staging';
		} else {
			$classes[] = 'nebula-wireframing-production';
		}

		return $classes;
	}

	//Add a link to Nebula Wireframing on the Admin Bar
	add_action('admin_bar_menu', 'nebula_admin_bar_nebula_wireframing', 900);
	function nebula_admin_bar_nebula_wireframing($wp_admin_bar){
		if ( nebula_dev_phase() == 'wireframe' ){
			$wireframe_menu_title = ( !is_admin() )? ' (Wireframe)' : '';
		} elseif ( nebula_dev_phase() == 'staging' ){
			$wireframe_menu_title = ( !is_admin() )? ' (Staging)' : '';
		} else {
			$wireframe_menu_title = ( !is_admin() )? ' (Production)' : '';
		}

		$wp_admin_bar->add_node(array(
			'id' => 'nebula-prototype',
			'title' => '<i class="fa fa-fw fa-sitemap" style="font-family: \'FontAwesome\'; color: #a0a5aa; color: rgba(240, 245, 250, .6); margin-right: 5px;"></i> Prototype' . $wireframe_menu_title,
			'href' => get_admin_url() . 'themes.php?page=nebula_options'
		));

		if ( nebula_dev_phase() ){
			$permalink = ( is_admin() )? home_url() : get_permalink();

			if ( nebula_dev_phase() != 'wireframe' && nebula_option('wireframe_theme') ){
				$wp_admin_bar->add_node(array(
					'parent' => 'nebula-prototype',
					'id' => 'nebula-wireframe-activate',
					'title' => '<i class="nebula-admin-fa fa fa-fw fa-flag-o" style="font-family: \'FontAwesome\'; color: #a0a5aa; color: rgba(240, 245, 250, .6); margin-right: 5px;"></i> View Wireframe &raquo;',
					'href' => $permalink . '?phase=wireframe',
				));
			}

			if ( nebula_dev_phase() != 'staging' && nebula_option('staging_theme') ){
				$wp_admin_bar->add_node(array(
					'parent' => 'nebula-prototype',
					'id' => 'nebula-staging-activate',
					'title' => '<i class="nebula-admin-fa fa fa-fw fa-flag" style="font-family: \'FontAwesome\'; color: #a0a5aa; color: rgba(240, 245, 250, .6); margin-right: 5px;"></i> View Staging Site &raquo;',
					'href' => $permalink . '?phase=staging',
				));
			}

			if ( (nebula_dev_phase() != 'production' || is_admin()) ){
				$wp_admin_bar->add_node(array(
					'parent' => 'nebula-prototype',
					'id' => 'nebula-production-activate',
					'title' => '<i class="nebula-admin-fa fa fa-fw fa-flag-checkered" style="font-family: \'FontAwesome\'; color: #a0a5aa; color: rgba(240, 245, 250, .6); margin-right: 5px;"></i> View Production Site &raquo;',
					'href' => $permalink . '?phase=production',
				));
			}
		}

		$wp_admin_bar->add_node(array(
			'parent' => 'nebula-prototype',
			'id' => 'nebula-prototype-help',
			'title' => '<i class="nebula-admin-fa fa fa-fw fa-question" style="font-family: \'FontAwesome\'; color: #a0a5aa; color: rgba(240, 245, 250, .6); margin-right: 5px;"></i> Help & Documentation &raquo;',
			'href' => 'https://gearside.com/nebula/documentation/custom-functionality/prototype-mode/',
			'meta' => array('target' => '_blank')
		));

		$wp_admin_bar->remove_menu('wpseo-menu'); //SEO menu not important during prototyping
	}
}

//Detect which prototype phase is currently being viewed.
function nebula_dev_phase(){
	if ( !is_plugin_active('jonradio-multiple-themes/jonradio-multiple-themes.php') ){
		return false;
	}

	if ( isset($_GET['phase']) && $_GET['phase'] == 'wireframe' ){
		return 'wireframe';
	}

	if ( isset($_GET['phase']) && $_GET['phase'] == 'staging' ){
		return 'staging';
	}

	return 'production';
}

//Top header for each component
function fpo_component($component='Component', $icon='fa-cube', $open='-open'){
	if ( nebula_option('prototype_mode', 'disabled') ){
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
		<div class="component-comment-drawer nebulashadow bulging">
			<strong class="comment-header">0 Comments</strong>
			<p>Comment functionality coming soon.</p>
		</div>
	</div><!-- /fpo-component (' . $component . ') -->';
}

//Top header for each component (with opening .fpo div)
function fpo_component_start($component='Component', $icon='fa-cube'){
	if ( nebula_option('prototype_mode', 'disabled') ){
		return false;
	}
	fpo_component($component, $icon, '');
	echo '<div class="fpo clearfix">';
}

//Closes .fpo div (from fpo_component_start)
function fpo_component_end(){
	if ( nebula_option('prototype_mode', 'disabled') ){
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

//Placeholder background image
/* <div class="row" style="<?php fpo_bg_image(); ?>"> */
function fpo_bg_image($type='none', $color='#aaa'){
	$imgsrc = '';
	if ( $type == 'unsplash' || $type == 'photo' ){
		$imgsrc = unsplash_it(800, 600, 1);
	} elseif ( strpos($type, '#') !== false ){
		$color = $type;
	}

	if ( empty($imgsrc) ){
		$return = "background: url('data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' x=\'0px\' y=\'0px\' width=\'100%\' height=\'100%\'><line fill=\'none\' stroke=\'" . $color . "\' stroke-miterlimit=\'10\' x1=\'0\' y1=\'0\' x2=\'100%\' y2=\'100%\'/><line fill=\'none\' stroke=\'" . $color . "\' stroke-miterlimit=\'10\' x1=\'100%\' y1=\'0\' x2=\'0\' y2=\'100%\'/></svg>') no-repeat; border: 1px solid " . $color . ";";
	} else {
		$return = "background: url('" . $imgsrc . "') no-repeat; background-size: cover;";
	}

	echo $return;
}

//Placeholder image... Consider deprecating this function
function fpo_image($width='100%', $height='200px', $type='none', $color='#000', $styles='', $classes=''){
	if ( $width == 'bg' || $width == 'background' ){
		$height = ( $height == '200px' )? 'none' : $height; //$height is type in this case
		$type = ( $type == 'none' )? '#000' : $type; //$type is color in this case.
		return fpo_bg_image($height, $type);
	}

	if ( is_int($width) ){
		$width .= 'px';
	}

	if ( is_int($height) ){
		$height .= 'px';
	}

	$imgsrc = '';
	if ( $type == 'unsplash' || $type == 'photo' || $width == 'unsplash' || $width == 'photo' ){
		$imgsrc = unsplash_it(800, 600, 1);
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