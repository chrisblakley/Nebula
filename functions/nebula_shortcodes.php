<?php

//Get flags where a parameter is declared in $atts that exists without a declared value
/* Usage:
	$flags = get_flags($atts);
	if (in_array('your_flag', $flags) {
	    // Flag is present
	}
*/
function get_flags($atts) {
	$flags = array();
	if (is_array($atts)) {
		foreach ($atts as $key => $value) {
			if ($value != '' && is_numeric($key)) {
				array_push($flags, $value);
			}
		}
	}
	return $flags;
}


add_shortcode('div', 'div_shortcode');
function div_shortcode($atts, $content=''){
	extract( shortcode_atts(array("class" => '', "style" => '', "open" => '', "close" => ''), $atts) );
	if ( $content ) {
		$div = '<div class="nebula-div ' . $class . '" style="' . $style . '">' . $content . '</div>';
	} else {
		if ( $close ) {
			$div = '</div><!-- /nebula-div -->';
		} else {
			$div = '<div class="nebula-div nebula-div-open' . $class . '" style="' . $style . '">';
		}
	}
	return $div;
}


//Gumby Grid Shortcodes

//Colgrid
if ( shortcode_exists( 'colgrid' ) ) {
	add_shortcode('gumby_colgrid', 'colgrid_shortcode');
} else {
	add_shortcode('gumby_colgrid', 'colgrid_shortcode');
	add_shortcode('colgrid', 'colgrid_shortcode');
}
function colgrid_shortcode($atts, $content=''){
	extract( shortcode_atts( array('grid' => '', 'class' => '', 'style' => ''), $atts) );
	$flags = get_flags($atts);
	$grid = array_values($flags);
	return '<section class="nebula-colgrid ' . $grid[0] . ' colgrid ' . $class . '" style="' . $style . '">' . do_shortcode($content) . '</section><!--/' . $grid[0] . ' colgrid-->';
} //end colgrid_grid()

//Container
if ( shortcode_exists( 'container' ) ) {
	add_shortcode('gumby_container', 'container_shortcode');
} else {
	add_shortcode('gumby_container', 'container_shortcode');
	add_shortcode('container', 'container_shortcode');
}
function container_shortcode($atts, $content=''){
	extract( shortcode_atts( array('class' => '', 'style' => ''), $atts) );
	return '<div class="nebula-container container ' . $class . '" style="' . $style . '">' . do_shortcode($content) . '</div><!--/container-->';
} //end container_grid()

//Row
if ( shortcode_exists('row') ) {
	add_shortcode('gumby_row', 'row_shortcode');
} else {
	add_shortcode('gumby_row', 'row_shortcode');
	add_shortcode('row', 'row_shortcode');
}
function row_shortcode($atts, $content=''){
	extract( shortcode_atts( array('class' => '', 'style' => ''), $atts) );
	$GLOBALS['col_counter'] = 0;
	return '<div class="nebula-row row ' . $class . '" style="' . $style . '">' . do_shortcode($content) . '</div><!--/row-->';
} //end row_grid()

//Columns
if ( shortcode_exists('columns') || shortcode_exists('column') || shortcode_exists('cols') || shortcode_exists('col') ) {
	add_shortcode('gumby_column', 'column_shortcode');
	add_shortcode('gumby_columns', 'column_shortcode');
	add_shortcode('gumby_col', 'column_shortcode');
	add_shortcode('gumby_cols', 'column_shortcode');
} else {
	add_shortcode('gumby_column', 'column_shortcode');
	add_shortcode('gumby_columns', 'column_shortcode');
	add_shortcode('gumby_col', 'column_shortcode');
	add_shortcode('gumby_cols', 'column_shortcode');
	add_shortcode('column', 'column_shortcode');
	add_shortcode('columns', 'column_shortcode');
	add_shortcode('col', 'column_shortcode');
	add_shortcode('cols', 'column_shortcode');
}
function column_shortcode($atts, $content=''){
	extract( shortcode_atts( array('columns' => '', 'push' => '', 'centered' => '', 'first' => false, 'last' => false, 'class' => '', 'style' => ''), $atts) );

	$flags = get_flags($atts);
	if ( in_array('centered', $flags) ) {
		$centered = 'centered';
		$key = array_search('centered', $flags);
		unset($flags[$key]);
	} elseif ( in_array('first', $flags) ) {
		$GLOBALS['col_counter'] = 1;
		$first = 'margin-left: 0;';
		$key = array_search('first', $flags);
	} elseif ( $GLOBALS['col_counter'] == 0 ) {
		$GLOBALS['col_counter'] = 1;
		$first = 'margin-left: 0;';
	} else {
		$GLOBALS['col_counter']++;
	}

	if ( in_array('last', $flags) ) {
		$GLOBALS['col_counter'] = 0;
		$key = array_search('last', $flags);
		unset($flags[$key]);
	}

	$columns = array_values($flags);

	if ( $push ) {
		$push = 'push_' . $push;
	}

	return '<div class="nebula-columns ' . $columns[0] . ' columns ' . $push . ' ' . $centered . ' ' . $class . '" style="' . $style . ' ' . $first . '">' . do_shortcode($content) . '</div>';

} //end column_grid()


//Divider
add_shortcode('divider', 'divider_shortcode');
add_shortcode('hr', 'divider_shortcode');
add_shortcode('line', 'divider_shortcode');
function divider_shortcode($atts){
	extract( shortcode_atts(array("space" => '0', "above" => '0', "below" => '0'), $atts) );
	if ( $space ) {
		$above = $space;
		$below = $space;
	}
	$divider = '<hr class="nebula-divider" style="margin-top: ' . $above . 'px; margin-bottom: ' . $below . 'px;"/>';
	return $divider;
}


//Icon
add_shortcode('icon', 'icon_shortcode');
function icon_shortcode($atts){
	extract( shortcode_atts(array('type'=>'', 'color'=>'inherit', 'size'=>'inherit', 'class'=>''), $atts) );
	if (strpos($type, 'fa-') !== false) {
	    $fa = 'fa ';
	}
	$extra_style = !empty($color) ? 'color:' . $color . ';' :'';
	$extra_style .= !empty($size) ? 'font-size:' . $size . ';' :'';
	return '<i class="' . $class . ' nebula-icon-shortcode ' . $fa . $type . '" style="' . $extra_style . '"></i>';
}


//Button
add_shortcode('button', 'button_shortcode');
function button_shortcode($atts, $content=''){
	extract( shortcode_atts( array('size' => 'medium', 'type' => 'primary', 'pretty' => false, 'metro' => false, 'icon' => false, 'side' => 'left', 'href' => '#', 'target' => false, 'class' => '', 'style' => ''), $atts) );

	$flags = get_flags($atts);
	if ( in_array('pretty', $flags) ) {
		$btnstyle = ' pretty';
	} elseif ( in_array('metro', $flags) ) {
		$btnstyle = ' metro';
	}

	if ( $icon ) {
		$side = 'icon-' . $side;
		if (strpos($icon, 'fa-') !== false) {
		    $icon_family = 'fa ';
		} else {
			$icon_family = 'entypo ';
		}
	} else {
		$icon = '';
	}

	if ( $target ) {
		$target = ' target="' . $target . '"';
	}

	return '<div class="nebula-button ' . $size . ' ' . $type . $btnstyle . ' btn '. $side . ' ' . $icon_family . ' ' . $icon . '"><a href="' . $href . '"' . $target . '>' . $content . '</a></div>';

} //end button_shortcode()


//Space (aka Gap)
add_shortcode('space', 'space_shortcode');
add_shortcode('gap', 'space_shortcode');
function space_shortcode($atts){
	extract( shortcode_atts(array("height" => '20'), $atts) );
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
	extract( shortcode_atts(array("key" => '', "mode" => 'place', "q" => '', "center" => '', "origin" => '', "destination" => '', "waypoints" => '', "avoid" => '', "zoom" => '', "maptype" => 'roadmap', "language" => '',  "region" => '', "width" => '100%', "height" => '300', 'overlay' => false, "class" => '', "style" => ''), $atts) );
	
	$flags = get_flags($atts);
	if ( in_array('overlay', $flags) ) {
		$overlay = 'the-map-overlay';
	} else {
		$overlay = '';
	}
	
	if ( $key == '' ) {
		$key = 'AIzaSyArNNYFkCtWuMJOKuiqknvcBCyfoogDy3E'; //@TODO "APIs" 2: Replace with your own key to avoid designating a key every time.
	}
	if ( $q != '' ) {
		$q = str_replace(' ', '+', $q);
		$q = '&q=' . $q;
	}
	if ( $mode == 'directions' ) {
		if ( $origin != '' ) {
			$origin = str_replace(' ', '+', $origin);
			$origin = '&origin=' . $origin;
		}
		if ( $destination != '' ) {
			$destination = str_replace(' ', '+', $destination);
			$destination = '&destination=' . $destination;
		}
		if ( $waypoints != '' ) {
			$waypoints = str_replace(' ', '+', $waypoints);
			$waypoints = '&waypoints=' . $waypoints;
		}
		if ( $avoid != '' ) {
			$avoid = '&avoid=' . $avoid;
		}
	}
	if ( $center != '' ) {
		$center = '&center=' . $center;
	}
	if ( $language != '' ) {
		$language = '&language=' . $language;
	}
	if ( $region != '' ) {
		$region = '&region=' . $region;
	}
	if ( $zoom != '' ) {
		$zoom = '&zoom=' . $zoom;
	}
	
	$return = '<script>
		jQuery(document).ready(function() {
			jQuery(".the-map-overlay").on("click", function(){
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
	extract( shortcode_atts(array("id" => null, "height" => '', "width" => '', "autoplay" => '0', "badge" => '1', "byline" => '1', "color" => '00adef', "loop" => '0', "portrait" => '1', "title" => '1'), $atts) );
	$protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';
	$width = 'width="' . $width . '"';
	$height = 'height="' . $height . '"';
	vimeo_meta($id);
	global $vimeo_meta;
	$vimeo = '<article class="vimeo video"><iframe id="' . $vimeo_meta['safetitle'] . '" class="vimeoplayer" src="' . $protocol . 'player.vimeo.com/video/' . $vimeo_meta['id'] . '?api=1&player_id=' . $vimeo_meta['safetitle'] . '" ' . $width . ' ' . $height . ' autoplay="' . $autoplay . '" badge="' . $badge . '" byline="' . $byline . '" color="' . $color . '" loop="' . $loop . '" portrait="' . $portrait . '" title="' . $title . '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></article>';
	return $vimeo;
}


//Youtube
add_shortcode('youtube', 'youtube_shortcode');
function youtube_shortcode($atts){
	extract( shortcode_atts(array("id" => null, "height" => '', "width" => '', "rel" => 0), $atts) );
	$protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';
	$width = 'width="' . $width . '"';
	$height = 'height="' . $height . '"';
	youtube_meta($id);
	global $youtube_meta;
	$youtube = '<article class="youtube video"><iframe id="' . $youtube_meta['safetitle'] . '" class="youtubeplayer" ' . $width . ' ' . $height . ' src="' . $protocol . 'www.youtube.com/embed/' . $youtube_meta['id'] . '?wmode=transparent&enablejsapi=1&origin=' . $youtube_meta['origin'] . '&rel=' . $rel . '" frameborder="0" allowfullscreen=""></iframe></article>';
	return $youtube;
}


//Code
add_shortcode('code', 'code_shortcode');
function code_shortcode($atts, $content=''){
	extract( shortcode_atts(array('class' => '', 'style' => ''), $atts) );
	$content = htmlspecialchars_decode($content);
	return '<code class="nebula-code ' . $class . '" style="' . $style . '" >' . htmlentities($content) . '</code>';
} //end code_shortcode()


//Pre
add_shortcode('pre', 'pre_shortcode');
$GLOBALS['pre'] = 0;
function pre_shortcode($atts, $content=''){
	extract( shortcode_atts(array('lang' => '', 'language' => '', 'color' => '', 'br' => false, 'class' => '', 'style' => ''), $atts) );

	if ( $GLOBALS['pre'] == 0 ) { //@TODO "Nebula" 0: Change this to a wordpress enqueue style or require_once so it only gets loaded one time.
		echo '<link rel="stylesheet" type="text/css" href="' . get_stylesheet_directory_uri() . '/css/pre.css" />';
		$GLOBALS['pre'] = 1;
	}

	$flags = get_flags($atts);
	if ( !in_array('br', $flags) ) {
		$content = preg_replace('#<br\s*/?>#', '', $content);
	}

	$content = htmlspecialchars_decode($content);
	$content = htmlspecialchars($content);

	if ( $lang == '' && $language != '' ) {
		$lang = $language;
	}
	$lang = str_replace(array('"', "'", "&quot;", "&#039;"), '', $lang);
	$search = array('actionscript', 'apache', 'css', 'directive', 'html', 'js', 'javascript', 'jquery', 'mysql', 'php', 'shortcode', 'sql');
	$replace = array('ActionScript', 'Apache', 'CSS', 'Directive', 'HTML', 'JavaScript', 'JavaScript', 'jQuery', 'MySQL', 'PHP', 'Shortcode', 'SQL');
	$vislang = str_replace($search, $replace, $lang);

	$return = '<span class="nebula-pre nebula-code codetitle ' . $lang . '" style="color: ' . $color . ';">' . $vislang . '</span><pre class="nebula-code ' . $lang . ' ' . $class . '" style="';
	if ( $color != '' ) {
		$return .= 'border: 1px solid ' . $color . '; border-left: 5px solid ' . $color . ';';
	}
	$return .= $style . '" >' . $content . '</pre>';

	return $return;
} //end pre_shortcode()


//Gist embedding
add_shortcode('gist', 'gist_shortcode');
function gist_shortcode($atts, $content=''){
	extract( shortcode_atts(array('lang' => '', 'language' => '', 'color' => '', 'file' => ''), $atts) );

	if ( $GLOBALS['pre'] == 0 ) { //@TODO "Nebula" 0: Change this to a wordpress enqueue style or require_once so it only gets loaded one time.
		echo '<link rel="stylesheet" type="text/css" href="' . get_stylesheet_directory_uri() . '/css/pre.css" />';
		$GLOBALS['pre'] = 1;
	}

	if ( $lang == '' && $language != '' ) {
		$lang = $language;
	}
	$lang = str_replace(array('"', "'", "&quot;", "&#039;"), '', $lang);
	$search = array('actionscript', 'apache', 'css', 'directive', 'html', 'js', 'javascript', 'jquery', 'mysql', 'php', 'shortcode', 'sql');
	$replace = array('ActionScript', 'Apache', 'CSS', 'Directive', 'HTML', 'JavaScript', 'JavaScript', 'jQuery', 'MySQL', 'PHP', 'Shortcode', 'SQL');
	$vislang = str_replace($search, $replace, $lang);

	if ( $file ) {
		$file = '?file=' . $file;
	}

	$return = '<span class="nebula-gist nebula-code codetitle ' . $lang . '" style="color: ' . $color . ';">' . $vislang . '</span><div class="nebula-code ' . $lang . ' ' . $class . '" style="';
	if ( $color != '' ) {
		$return .= 'border: 1px solid ' . $color . '; border-left: 5px solid ' . $color . ';';
	}
	$return .= $style . '" ><script type="text/javascript" src="'. $content . $file . '"></script></div>';

	return $return;
} //end gist_shortcode()


//Accordion
add_shortcode('accordion', 'accordion_shortcode');
function accordion_shortcode($atts, $content=''){
	extract( shortcode_atts(array('class' => '', 'style' => ''), $atts) );

	return '<div class="nebula-bio ' . $class . '" style="' . $style . '" >' . $content . '</code>';

} //end accordion_shortcode()


//Bio
add_shortcode('bio', 'bio_shortcode');
function bio_shortcode($atts, $content=''){
	extract( shortcode_atts(array('class' => '', 'style' => ''), $atts) );

	/*
		Parameters to use:
			Name
			Title
			Email
			Phone
			Extension
			vCard path
			Website
			Twitter
			Facebook
			Instagram
			LinkedIn
			Photo path
			Excerpt ($content)
	*/

	return '<div class="nebula-bio ' . $class . '" style="' . $style . '" >' . $content . '</code>';

} //end bio_shortcode()


//Tooltip
add_shortcode('tooltip', 'tooltip_shortcode');
function tooltip_shortcode($atts, $content=''){
	extract( shortcode_atts(array('tip' => '', 'class' => '', 'style' => ''), $atts) );
	return '<span class="nebula-tooltip ttip ' . $class . '" data-tooltip="' . $tip . '" style="' . $style . '">' . $content . '</span>';
} //end tooltip_shortcode()


//Slider
add_shortcode('slider', 'slider_shortcode');
function slider_shortcode($atts, $content=''){
	extract( shortcode_atts(array('id' => false, 'mode' => 'fade', 'delay' => '8000', 'speed' => '1000', 'frame' => false, 'titles' => false), $atts) );
	
	if ( !$id ) {
		$id = 'nebula-slider-' . rand(1, 10000);
	} elseif ( strlen($id) > 0 && ctype_digit(substr($id, 0, 1)) ) {
		$id = 'nebula-slider-' . $id;
	}
	
	$return = '<div id="' . $id . '" class="nebula-slider-con"><ul class="bxslider ' . $id . '" style="padding-left: 0;">';
	$return .= parse_shortcode_content(do_shortcode($content));
	$return .= '</ul></div><!--/nebula-shortcode-slider-con-->';
	
	$flags = get_flags($atts);
	
	if ( !in_array('frame', $flags) ) {
		$return .= '<style>
			#' . $id . ' .bx-wrapper .bx-viewport {box-shadow: none; -webkit-box-shadow: none; -moz-box-shadow: none; border: none; background: none;}
		</style>';
		if ( $mode == 'fade' ) {
			$return .= '<style>
				#' . $id . ' .bx-wrapper .bx-viewport .nebula-slide {width: auto !important;}
			</style>';
		}
	}
	
	if ( in_array('titles', $flags) ) {
		$titles= 'true';
	} else {
		$titles= 'false';
	}
	
	if ( !in_array('controls', $flags) ) {
		$controls = 'false';
		$auto = 'true';
	} else {
		$controls = 'true';
		if ( in_array('delay', $flags) ) {
			$auto = 'true';
		} else {
			$auto = 'false';
		}
	}
	
	$return .= '<script>
		jQuery(window).on("load", function() {
			setTimeout(function(){
				jQuery(".' . $id . '").bxSlider({
					mode: "' . $mode . '",
					speed: ' . $speed . ',
					captions: ' . $titles . ',
					pager: false,
					auto: ' . $auto . ',
					pause: ' . $delay . ',
					autoHover: true,
					adaptiveHeight: true,
					useCSS: true,
					controls: ' . $controls . '
				});
			}, 1000);
		});
	</script>';
	
	echo $return;
} //end slider_shortcode()


//Slide
add_shortcode('slide', 'slide_shortcode');
function slide_shortcode($atts, $content=''){
	extract( shortcode_atts(array('title' => '', 'link' => '', 'target' => ''), $atts) );

	if ( $title != '' ) {
		$alt_and_title = 'alt="' . $title . '" title="' . $title . '"';
	} else {
		$alt_and_title = '';
	}

	if ( $link == '' ) {
		$linkopen = '';
		$linkclose = '';
	} else {
		if ( $target == '' ) {
			$linkopen = '<a href="' . $link . '">';
		} else {
			$linkopen = '<a href="' . $link . '" target="' . $target . '">';
		}
		$linkclose = '</a>';
	}

	return '<li class="nebula-slide clearfix">' . $linkopen . '<img src="' . $content . '" ' . $alt_and_title . '"/>' . $linkclose . '</li>';
} //end slide_shortcode()



//Map parameters of nested shortcodes
function attribute_map($str, $att = null) {
    $res = array();
    $reg = get_shortcode_regex();
    preg_match_all('~'.$reg.'~',$str, $matches);
    foreach($matches[2] as $key => $name) {
        $parsed = shortcode_parse_atts($matches[3][$key]);
        $parsed = is_array($parsed) ? $parsed : array();

        if(array_key_exists($name, $res)) {
            $arr = array();
            if(is_array($res[$name])) {
                $arr = $res[$name];
            } else {
                $arr[] = $res[$name];
            }

            $arr[] = array_key_exists($att, $parsed) ? $parsed[$att] : $parsed;
            $res[$name] = $arr;

        } else {
            $res[$name] = array_key_exists($att, $parsed) ? $parsed[$att] : $parsed;
        }
    }

    return $res;
}

//Remove empty <p> tags from Wordpress content (for nested shortcodes)
function parse_shortcode_content($content) {
   /* Parse nested shortcodes and add formatting. */
    $content = trim( do_shortcode( shortcode_unautop( $content ) ) );
    /* Remove '' from the start of the string. */
    if ( substr( $content, 0, 4 ) == '' )
        $content = substr( $content, 4 );
    /* Remove '' from the end of the string. */
    if ( substr( $content, -3, 3 ) == '' )
        $content = substr( $content, 0, -3 );
    /* Remove any instances of ''. */
    $content = str_replace( array( '<p></p>' ), '', $content );
    $content = str_replace( array( '<p>  </p>' ), '', $content );
    return $content;
}
//move wpautop filter to AFTER shortcode is processed
remove_filter( 'the_content', 'wpautop' );
add_filter( 'the_content', 'wpautop' , 99);
add_filter( 'the_content', 'shortcode_unautop',100 );


//Add Nebula Toolbar to TinyMCE
add_action('init', 'add_shortcode_button');
function add_shortcode_button(){
    if ( current_user_can('edit_posts') ||  current_user_can('edit_pages') ){
         add_filter('mce_external_plugins', 'add_shortcode_plugin');
         add_filter('mce_buttons_3', 'register_shortcode_button');
       }

}
function register_shortcode_button($buttons){
    array_push($buttons, "nebulaaccordion", "nebulabio", "nebulabutton", "nebulaclear", "nebulacode", "nebuladiv", "nebulacolgrid", "nebulacontainer", "nebularow", "nebulacolumn", "nebulaicon", "nebulaline", "nebulamap", "nebulaspace", "nebulaslider", "nebulatooltip", "nebulavideo");
    return $buttons;
}
function add_shortcode_plugin($plugin_array) {
	$plugin_array['nebulatoolbar'] = get_template_directory_uri() . '/js/shortcodes.js';
	return $plugin_array;
}