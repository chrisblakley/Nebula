<?php

//Get flags where a parameter is declared in $atts that exists without a declared value
/* Usage:
	$flags = get_flags($atts);
	if (in_array('your_flag', $flags){
	    // Flag is present
	}
*/
function get_flags($atts){
	$flags = array();
	if ( is_array($atts) ){
		foreach ( $atts as $key => $value ){
			if ( $value != '' && is_numeric($key) ){
				array_push($flags, $value);
			}
		}
	}
	return $flags;
}

//Div
add_shortcode('div', 'div_shortcode');
function div_shortcode($atts, $content=''){
	extract(shortcode_atts(array("class" => '', "style" => '', "open" => '', "close" => ''), $atts));

	if ( $content ){
		$div = '<div class="nebula-div ' . $class . '" style="' . $style . '">' . $content . '</div>';
	} else {
		if ( $close ){
			$div = '</div>';
		} else {
			$div = '<div class="nebula-div nebula-div-open' . $class . '" style="' . $style . '">';
		}
	}
	return $div;
}

/*==========================
	Bootstrap Grid Shortcodes
 ===========================*/

//Container
if ( !shortcode_exists('container') ){
	add_shortcode('container', 'container_shortcode');
}
add_shortcode('bootstrap_container', 'container_shortcode');
function container_shortcode($atts, $content=''){
	extract(shortcode_atts( array('class' => '', 'style' => ''), $atts));
	return '<div class="nebula-container container ' . $class . '" style="' . $style . '">' . do_shortcode($content) . '</div>';
}

//Row
if ( !shortcode_exists('row') ){
	add_shortcode('row', 'row_shortcode');
}
add_shortcode('bootstrap_row', 'row_shortcode');
function row_shortcode($atts, $content=''){
	extract(shortcode_atts( array('class' => '', 'style' => ''), $atts));
	return '<div class="nebula-row row ' . $class . '" style="' . $style . '">' . do_shortcode($content) . '</div>';
}

//Columns
if ( !shortcode_exists('columns') && !shortcode_exists('column') && !shortcode_exists('cols') && !shortcode_exists('col') ){
	add_shortcode('column', 'column_shortcode');
	add_shortcode('columns', 'column_shortcode');
	add_shortcode('col', 'column_shortcode');
	add_shortcode('cols', 'column_shortcode');
}
add_shortcode('bootstrap_column', 'column_shortcode');
add_shortcode('bootstrap_columns', 'column_shortcode');
add_shortcode('bootstrap_col', 'column_shortcode');
add_shortcode('bootstrap_cols', 'column_shortcode');
function column_shortcode($atts, $content=''){
	extract(shortcode_atts(array('scale' => 'md', 'columns' => '', 'offset' => '', 'centered' => '', 'class' => '', 'style' => ''), $atts));

	$flags = get_flags($atts);
	$columns = str_replace(array('one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve'), array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'), $columns);

	if ( in_array('centered', $flags) ){
		$centered = 'col-centered';
		$key = array_search('centered', $flags);
		unset($flags[$key]);
	}

	if ( !empty($push) ){
		$push = 'offset_' . $scale . '_' . $push;
	}

	return '<div class="nebula-columns col-' . $scale . '-' . $columns . ' ' . $offset . ' ' . $centered . ' ' . $class . '" style="' . $style . '">' . do_shortcode($content) . '</div>';
}

//Divider
add_shortcode('divider', 'divider_shortcode');
add_shortcode('hr', 'divider_shortcode');
add_shortcode('line', 'divider_shortcode');
function divider_shortcode($atts){
	extract(shortcode_atts(array("space" => '0', "above" => '0', "below" => '0'), $atts));

	if ( $space ){
		$above = $space;
		$below = $space;
	}
	$divider = '<hr class="nebula-divider" style="margin-top: ' . $above . 'px; margin-bottom: ' . $below . 'px;"/>';
	return $divider;
}

//Icon
add_shortcode('icon', 'icon_shortcode');
function icon_shortcode($atts){
	extract(shortcode_atts(array('type' => '', 'color' => 'inherit', 'size' => 'inherit', 'class' => ''), $atts));

	if ( strpos($type, 'fa-') == false ){
	    $type = 'fa-' . $type;
	}
	$extra_style = ( !empty($color) )? 'color:' . $color . ';' :'';
	$extra_style .= ( !empty($size) )? 'font-size:' . $size . ';' :'';
	return '<i class="' . $class . ' nebula-icon-shortcode ' . 'fa fa-fw ' . $type . '" style="' . $extra_style . '"></i>';
}

//Button
add_shortcode('button', 'button_shortcode');
function button_shortcode($atts, $content=''){
	extract(shortcode_atts( array('size' => 'md', 'type' => 'brand', 'icon' => false, 'href' => '#', 'target' => false, 'class' => '', 'style' => ''), $atts));

	if ( $target ){
		$target = ' target="' . $target . '"';
	}

	if ( $icon ){
		if ( strpos($icon, 'fa-' ) == false){
			$icon = 'fa-' . $icon;
		}
		$icon = '<i class="fa fa-fw ' . $icon . '"></i> ';
	}

	if ( $size ){
		$size = str_replace(array('small', 'medium', 'large'), array('sm', 'md', 'lg'), $size);
		if ( strpos($size, 'btn-' ) == false){
			$size = 'btn-' . $size;
		}
	}

	return '<div class="nebula-button"><a class="btn btn-' . $type . ' ' . $size . ' ' . $class . '" href="' . $href . '"' . $target . '>' . $icon . $content . '</a></div>';
} //end button_shortcode()

//Space (aka Gap)
add_shortcode('space', 'space_shortcode');
add_shortcode('gap', 'space_shortcode');
function space_shortcode($atts){
	extract(shortcode_atts(array("height" => '20'), $atts));
	return '<div class="space" style=" height:' . $height . 'px;" ></div>';
}

//Clear (aka Clearfix)
add_shortcode('clear', 'clear_shortcode');
add_shortcode('clearfix', 'clear_shortcode');
function clear_shortcode(){
	return '<div class="clearfix" style="clear: both;"></div>';
}

//Map
add_shortcode('map', 'map_shortcode');
function map_shortcode($atts){
	extract(shortcode_atts(array("key" => '', "mode" => 'place', "q" => '', "center" => '', "origin" => '', "destination" => '', "waypoints" => '', "avoid" => '', "zoom" => '', "maptype" => 'roadmap', "language" => '',  "region" => '', "width" => '100%', "height" => '350', 'overlay' => false, "class" => '', "style" => ''), $atts));

	$flags = get_flags($atts);
	if ( in_array('overlay', $flags) ){
		$overlay = 'the-map-overlay';
	} else {
		$overlay = '';
	}

	if ( empty($key) ){
		$key = nebula_option('google_browser_api_key');
	}
	if ( !empty($q) ){
		$q = str_replace(' ', '+', $q);
		$q = '&q=' . $q;
	}
	if ( $mode == 'directions' ){
		if ( $origin != '' ){
			$origin = str_replace(' ', '+', $origin);
			$origin = '&origin=' . $origin;
		}
		if ( $destination != '' ){
			$destination = str_replace(' ', '+', $destination);
			$destination = '&destination=' . $destination;
		}
		if ( $waypoints != '' ){
			$waypoints = str_replace(' ', '+', $waypoints);
			$waypoints = '&waypoints=' . $waypoints;
		}
		if ( $avoid != '' ){
			$avoid = '&avoid=' . $avoid;
		}
	}
	if ( !empty($center) ){
		$center = '&center=' . $center;
	}
	if ( !empty($language) ){
		$language = '&language=' . $language;
	}
	if ( !empty($region) ){
		$region = '&region=' . $region;
	}
	if ( !empty($zoom) ){
		$zoom = '&zoom=' . $zoom;
	}

	$return = '<script>
		jQuery(document).ready(function(){
			jQuery(".the-map-overlay").on("click tap touch", function(){
				jQuery(this).removeClass("the-map-overlay");
			});
		});
	</script>';

	$return .= '<div class="google-map-overlay ' . $overlay . '"><iframe class="nebula-googlemap-shortcode googlemap ' . $class . '" width="' . $width . '" height="' . $height . '" frameborder="0" src="https://www.google.com/maps/embed/v1/' . $mode . '?key=' . $key . $q . $zoom . $center . '&maptype=' . $maptype . $language . $region . '" style="' . $style . '"></iframe></div>';

	return $return;
}

//Vimeo
add_shortcode('vimeo', 'vimeo_shortcode');
function vimeo_shortcode($atts){
	extract(shortcode_atts(array("id" => null, "height" => '', "width" => '', "autoplay" => '0', "badge" => '1', "byline" => '1', "color" => '00adef', "loop" => '0', "portrait" => '1', "title" => '1'), $atts));

	$vimeo_data = video_meta('vimeo', $id);
	$vimeo = '<div class="nebula-vimeo embed-responsive embed-responsive-16by9">';
	if ( !empty($vimeo_data) && empty($vimeo_data['error']) ){
		$vimeo .= '<iframe id="' . $vimeo_data['safetitle'] . '" class="vimeo embed-responsive-item" src="//player.vimeo.com/video/' . $id . '?api=1&player_id=' . $vimeo_data['safetitle'] . '" width="' . $width . '" height="' . $height . '" autoplay="' . $autoplay . '" badge="' . $badge . '" byline="' . $byline . '" color="' . $color . '" loop="' . $loop . '" portrait="' . $portrait . '" title="' . $title . '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
	} else {
		$vimeo .= '<iframe class="vimeo embed-responsive-item" src="//player.vimeo.com/video/' . $id . '" width="' . $width . ' height="' . $height . '" autoplay="' . $autoplay . '" badge="' . $badge . '" byline="' . $byline . '" color="' . $color . '" loop="' . $loop . '" portrait="' . $portrait . '" title="' . $title . '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';

		if ( is_dev() ){
			$vimeo .= '<script>console.warn("' . $vimeo_data['error'] . ' (via Vimeo shortcode)");</script>';
		}
	}
	$vimeo .= '</div>';

	return $vimeo;
}

//Youtube
add_shortcode('youtube', 'youtube_shortcode');
function youtube_shortcode($atts){
	extract(shortcode_atts(array("id" => null, "height" => '', "width" => '', "rel" => 0, "ignore_visibility" => '', "class" => ''), $atts));

	$flags = get_flags($atts);
	if ( in_array('ignore_visibility', $flags) ){
		$ignore_visibility = 'ignore-visibility';
	}

	$youtube_data = video_meta('youtube', $id);
	$youtube = '<div class="nebula-youtube embed-responsive embed-responsive-16by9">';
	if ( !empty($youtube_data) && empty($youtube_data['error']) ){
		//Note: removed &origin=' . youtube_meta($id, 'origin') . ' right before &rel= due to console warnings. Doesn't seem to be an issue.
		$youtube .= '<iframe id="' . $youtube_data['safetitle'] . '" class="youtube embed-responsive-item ' . $class . ' ' . $ignore_visibility . '" width="' . $width . '" height="' . $height . '" src="//www.youtube.com/embed/' . $youtube_data['id'] . '?wmode=transparent&enablejsapi=1&rel=' . $rel . '" frameborder="0" allowfullscreen=""></iframe>';
	} else {
		$youtube .= '<iframe class="no-api embed-responsive-item ' . $class . ' ' . $ignore_visibility . '" width="' . $width . '" height="' . $height . '" src="//www.youtube.com/embed/' . $id . '?wmode=transparent&enablejsapi=1&rel=' . $rel . '" frameborder="0" allowfullscreen=""></iframe>';

		if ( is_dev() ){
			$youtube .= '<script>console.warn("(' . $youtube_data['error'] . ' (via Youtube shortcode)");</script>';
		}
	}
	$youtube .= '</div>';

	return $youtube;
}

//Code
add_shortcode('code', 'code_shortcode');
function code_shortcode($atts, $content=''){
	extract(shortcode_atts(array('class' => '', 'style' => ''), $atts));
	$content = htmlspecialchars_decode($content);
	return '<code class="nebula-code ' . $class . '" style="' . $style . '" >' . htmlentities($content) . '</code>';
} //end code_shortcode()

//Pre
//To preserve indentation, use the Preformatted style in the WYSIWYG and wrap that in this [pre] shortcode (make sure the shortcode is not in the <pre> tag)
add_shortcode('pre', 'pre_shortcode');
function pre_shortcode($atts, $content=''){
	extract(shortcode_atts(array('lang' => '', 'language' => '', 'color' => '', 'force' => false, 'br' => false, 'class' => '', 'style' => ''), $atts));

	if ( empty($GLOBALS['pre']) ){
		echo '<link rel="stylesheet" type="text/css" href="' . get_template_directory_uri() . '/stylesheets/css/pre.css" />';
		$GLOBALS['pre'] = 1;
	}

	$flags = get_flags($atts);
	if ( !in_array('br', $flags) ){
		$content = preg_replace('#<br\s*/?>#', '', $content);
	}

	$pre_tag_open = '';
	$pre_tag_close = '';
	if ( strpos($content, '<pre') === false && $force == false ){
		$content = htmlspecialchars_decode($content);
		$content = htmlspecialchars($content);
		$pre_tag_open = '<pre class="nebula-code ' . $lang . '">';
		$pre_tag_close = '</pre>';
	}

	if ( empty($lang) && !empty($language) ){
		$lang = $language;
	}
	$vislang = visibleLanguage($lang);

	$return = '<div class="nebula-code-con clearfix ' . strtolower($lang) . '"><span class="nebula-code codetitle ' . strtolower($lang) . '">' . $vislang . '</span>' . $pre_tag_open . $content . $pre_tag_close . '</div>';

	return $return;
} //end pre_shortcode()

//Gist embedding
add_shortcode('gist', 'gist_shortcode');
function gist_shortcode($atts, $content=''){
	extract(shortcode_atts(array('lang' => '', 'language' => '', 'color' => '', 'file' => ''), $atts));

	if ( empty($GLOBALS['pre']) ){
		echo '<link rel="stylesheet" type="text/css" href="' . get_template_directory_uri() . '/stylesheets/css/pre.css" />';
		$GLOBALS['pre'] = 1;
	}

	if ( empty($lang) && !empty($language) ){
		$lang = $language;
	}
	$vislang = visibleLanguage($lang);

	if ( $file ){
		$file = '?file=' . $file;
	}

	$return = '<span class="nebula-gist nebula-code codetitle ' . strtolower($lang) . '" style="color: ' . $color . ';">' . $vislang . '</span><div class="nebula-code ' . strtolower($lang) . ' ' . $class . '" style="';
	if ( $color != '' ){
		$return .= 'border: 1px solid ' . $color . '; border-left: 5px solid ' . $color . ';';
	}
	$return .= $style . '" ><script type="text/javascript" src="'. $content . $file . '"></script></div>';

	return $return;
} //end gist_shortcode()

//Github embedding
add_shortcode('github', 'github_shortcode');
function github_shortcode($atts, $content=''){
	extract(shortcode_atts(array('lang' => '', 'language' => '', 'color' => '', 'file' => ''), $atts));

	if ( !empty($file) ){
		WP_Filesystem();
		global $wp_filesystem;
		$file_contents = $wp_filesystem->get_contents($file);

		if ( empty($GLOBALS['pre']) ){
			echo '<link rel="stylesheet" type="text/css" href="' . get_template_directory_uri() . '/stylesheets/css/pre.css" />';
			$GLOBALS['pre'] = 1;
		}

		if ( empty($lang) && !empty($language) ){
			$lang = $language;
		}
		$vislang = visibleLanguage($lang);

		$return = '<div class="nebula-code-con clearfix ' . strtolower($lang) . '"><span class="nebula-code codetitle ' . strtolower($lang) . '" style="color: ' . $color . ';">' . $vislang . '</span><pre class="nebula-code ' . $lang . ' ' . $class . '" style="';
		if ( $color != '' ){
			$return .= 'border: 1px solid ' . $color . '; border-left: 5px solid ' . $color . ';';
		}
		$return .= $style . '" >' . $file_contents . '</pre></div>';

		return $return;
	}
}

//Modify the language string into a proper visible language
function visibleLanguage($lang){
	$lang = strtolower(str_replace(array('"', "'", "&quot;", "&#039;"), '', $lang));
	$search = array('actionscript', 'apache', 'css', 'directive', 'html', 'js', 'javascript', 'jquery', 'mysql', 'php', 'regex', 'shortcode', 'sql');
	$replace = array('ActionScript', 'Apache', 'CSS', 'Directive', 'HTML', 'JavaScript', 'JavaScript', 'jQuery', 'MySQL', 'PHP', 'RegEx', 'Shortcode', 'SQL');

	return str_replace($search, $replace, $lang);
}

//Accordion
$GLOBALS['accordion'] = 0;
add_shortcode('accordion', 'accordion_shortcode');
function accordion_shortcode($atts, $content=''){
	extract(shortcode_atts(array('class' => '', 'style' => '', 'type' => 'single'), $atts));

	$return = '<div class="accordion ' . $class . ' ' . $type . '" style="' . $style . '">' . do_shortcode($content) . '</div>';
	if ( $GLOBALS['accordion'] == 0 ){
		$return .= "<script>jQuery(document).ready(function(){
			jQuery('.accordion-item').each(function(){
				if ( jQuery(this).hasClass('open') ){
					jQuery(this).children('.accordion-content-con').slideToggle();
					jQuery(this).toggleClass('accordion-collapsed accordion-expanded');
				}
			});
			jQuery('.accordion-toggle').on('click touch tap', function(){
				if ( jQuery(this).parent('.accordion-item').parent('.accordion').hasClass('multiple') ){
					jQuery(this).parent('.accordion-item').children('.accordion-content-con').slideToggle();
					jQuery(this).parent('.accordion-item').toggleClass('accordion-collapsed accordion-expanded');
				}
				if ( jQuery(this).parent('.accordion-item').parent('.accordion').hasClass('single') ){
					if ( jQuery(this).parent('.accordion-item').hasClass('accordion-collapsed') ){
						jQuery(this).parent('.accordion-item').parent('.accordion').find('.accordion-item.accordion-expanded').children('.accordion-content-con').slideUp();
						jQuery(this).parent('.accordion-item').parent('.accordion').find('.accordion-item.accordion-expanded').toggleClass('accordion-collapsed accordion-expanded');
						jQuery(this).parent('.accordion-item').children('.accordion-content-con').slideToggle();
					}
					if ( jQuery(this).hasClass('accordion-expanded') ){
						jQuery(this).parent('.accordion-item').children('.accordion-content-con').slideUp();
					}
					jQuery(this).parent('.accordion-item').toggleClass('accordion-collapsed accordion-expanded');
				}
				return false;
			});
		});</script>";
		$GLOBALS['accordion'] = 1;
	}

	return $return;
} //end accordion_shortcode()

//Accordion_Item
add_shortcode('accordion_item', 'accordion_item_shortcode');
function accordion_item_shortcode($atts, $content=''){
	extract(shortcode_atts(array('class' => '', 'style' => '', 'title' => '', 'default' => ''), $atts));

	$return = '<div class="accordion-item accordion-collapsed ' . $class . ' ' . $default . '" style="' . $style . '"><div class="accordion-toggle"><a href="#" class="accordion-heading">' . $title . '</a></div><div class="accordion-content-con"><div class="accordion-content">' . $content . '</div></div></div>';

	return $return;
} //end accordion_item_shortcode()

//Tooltip
add_shortcode('tooltip', 'tooltip_shortcode');
function tooltip_shortcode($atts, $content=''){
	extract(shortcode_atts(array('tip' => '', 'placement' => 'top', 'class' => '', 'style' => ''), $atts));
	return '<span class="nebula-tooltip ttip ' . $class . '" data-toggle="tooltip" data-placement="' . $placement . '" title="' . $tip . '" style="' . $style . '">' . $content . '</span>';
} //end tooltip_shortcode()

//Slider
add_shortcode('carousel', 'slider_shortcode');
add_shortcode('slider', 'slider_shortcode');
function slider_shortcode($atts, $content=''){
	extract(shortcode_atts(array('id' => false, 'indicators' => true), $atts));
	$flags = get_flags($atts);

	if ( !$id ){
		$id = 'nebula-slider-' . rand(1, 10000);
	} elseif ( strlen($id) > 0 && ctype_digit(substr($id, 0, 1)) ){
		$id = 'nebula-slider-' . $id;
	}

	if ( !empty($indicators) ){
		$indicators = 'auto-indicators';
	} else {
		$indicators = '';
	}

	$return = '<div id="' . $id . '" class="carousel slide ' . $indicators . '" data-ride="carousel">';
	$return .= parse_shortcode_content(do_shortcode($content));
	$return .= '<a class="left carousel-control" href="#' . $id . '" data-slide="prev"><span class="icon-prev"></span><span class="sr-only">Previous</span></a><a class="right carousel-control" href="#' . $id . '" data-slide="next"><span class="icon-next"></span><span class="sr-only">Next</span></a></div>';

	return $return;
} //end slider_shortcode()

//Slide
add_shortcode('carousel_item', 'slide_shortcode');
add_shortcode('slide', 'slide_shortcode');
function slide_shortcode($atts, $content=''){
	extract(shortcode_atts(array('link' => '', 'target' => ''), $atts));

	if ( empty($link) ){
		$linkopen = '';
		$linkclose = '';
	} else {
		if ( empty($target) ){
			$linkopen = '<a href="' . $link . '">';
		} else {
			$linkopen = '<a href="' . $link . '" target="' . $target . '">';
		}
		$linkclose = '</a>';
	}

	return '<div class="carousel-item">' . $linkopen . '<img src="' . $content . '">' . $linkclose . '</div>'; //need <div class="carousel-inner">
} //end slide_shortcode()

//Map parameters of nested shortcodes
function attribute_map($str, $att = null){
    $res = array();
    $reg = get_shortcode_regex();
    preg_match_all('~'.$reg.'~',$str, $matches);
    foreach($matches[2] as $key => $name){
        $parsed = shortcode_parse_atts($matches[3][$key]);
        $parsed = ( is_array($parsed) )? $parsed : array();

        if(array_key_exists($name, $res)){
            $arr = array();
            if(is_array($res[$name])){
                $arr = $res[$name];
            } else {
                $arr[] = $res[$name];
            }

            $arr[] = ( array_key_exists($att, $parsed) )? $parsed[$att] : $parsed;
            $res[$name] = $arr;

        } else {
            $res[$name] = ( array_key_exists($att, $parsed) )? $parsed[$att] : $parsed;
        }
    }

    return $res;
}

//Remove empty <p> tags from Wordpress content (for nested shortcodes)
function parse_shortcode_content($content){
	$content = trim(do_shortcode(shortcode_unautop($content))); //Parse nested shortcodes and add formatting.

	//Remove '' from the start of the string.
	if ( substr( $content, 0, 4 ) == '' ){
		$content = substr($content, 4);
	}

	//Remove '' from the end of the string.
	if ( substr($content, -3, 3 ) == ''){
		$content = substr( $content, 0, -3 );
	}

	//Remove any instances of ''.
	$content = str_replace(array('<p></p>'), '', $content);
	$content = str_replace(array('<p>  </p>'), '', $content);

	return $content;
}

//Move wpautop filter to AFTER shortcode is processed
//@TODO "Nebula" 0: The following may be adding a <br> tag after certain plugin functionality?
//remove_filter('the_content', 'wpautop');
//add_filter('the_content', 'wpautop' , 99);
//add_filter('the_content', 'shortcode_unautop', 100);

//Add Nebula Toolbar to TinyMCE
add_action('admin_init', 'add_shortcode_button');
function add_shortcode_button(){
    if ( current_user_can('edit_posts') ||  current_user_can('edit_pages') ){
         add_filter('mce_external_plugins', 'add_shortcode_plugin');
         add_filter('mce_buttons_3', 'register_shortcode_button');
       }

}
function register_shortcode_button($buttons){
    array_push($buttons, "nebulaaccordion", /* "nebulabio", */ "nebulabutton", "nebulaclear", "nebulacode", "nebuladiv", "nebulacolgrid", "nebulacontainer", "nebularow", "nebulacolumn", "nebulaicon", "nebulaline", "nebulamap", "nebulaspace", "nebulaslider", "nebulatooltip", "nebulavideo");
    return $buttons;
}
function add_shortcode_plugin($plugin_array){
	$plugin_array['nebulatoolbar'] = get_template_directory_uri() . '/js/shortcodes.js';
	return $plugin_array;
}