<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Shortcodes') ){
	trait Shortcodes {
		public $shortcode_flags = array();

		public function hooks(){
			add_shortcode('widget', array($this, 'widget'));

			//Div
			add_shortcode('div', array($this, 'div_shortcode'));

			//Container
			if ( !shortcode_exists('container') ){
				add_shortcode('container', array($this, 'container_shortcode'));
			}
			add_shortcode('bootstrap_container', array($this, 'container_shortcode'));

			//Row
			if ( !shortcode_exists('row') ){
				add_shortcode('row', array($this, 'row_shortcode'));
			}
			add_shortcode('bootstrap_row', array($this, 'row_shortcode'));

			//Columns
			if ( !shortcode_exists('columns') && !shortcode_exists('column') && !shortcode_exists('cols') && !shortcode_exists('col') ){
				add_shortcode('column', array($this, 'column_shortcode'));
				add_shortcode('columns', array($this, 'column_shortcode'));
				add_shortcode('col', array($this, 'column_shortcode'));
				add_shortcode('cols', array($this, 'column_shortcode'));
			}
			add_shortcode('bootstrap_column', array($this, 'column_shortcode'));
			add_shortcode('bootstrap_columns', array($this, 'column_shortcode'));
			add_shortcode('bootstrap_col', array($this, 'column_shortcode'));
			add_shortcode('bootstrap_cols', array($this, 'column_shortcode'));

			//Divider
			add_shortcode('divider', array($this, 'divider_shortcode'));
			add_shortcode('hr', array($this, 'divider_shortcode'));
			add_shortcode('line', array($this, 'divider_shortcode'));

			//Icon
			add_shortcode('icon', array($this, 'icon_shortcode'));

			//Button
			add_shortcode('button', array($this, 'button_shortcode'));

			//Space (aka Gap)
			add_shortcode('space', array($this, 'space_shortcode'));
			add_shortcode('gap', array($this, 'space_shortcode'));

			//Clear (aka Clearfix)
			add_shortcode('clear', array($this, 'clear_shortcode'));
			add_shortcode('clearfix', array($this, 'clear_shortcode'));

			//Map
			add_shortcode('map', array($this, 'map_shortcode'));

			//Vimeo
			add_shortcode('vimeo', array($this, 'vimeo_shortcode'));

			//Youtube
			add_shortcode('youtube', array($this, 'youtube_shortcode'));

			//Code
			add_shortcode('code', array($this, 'code_shortcode'));

			//Pre
			//To preserve indentation, use the Preformatted style in the WYSIWYG and wrap that in this [pre] shortcode (make sure the shortcode is not in the <pre> tag)
			add_shortcode('pre', array($this, 'pre_shortcode'));

			//Gist embedding
			add_shortcode('gist', array($this, 'gist_shortcode'));

			//GitHub embedding
			add_shortcode('github', array($this, 'github_shortcode'));

			//Accordion
			add_shortcode('accordion', array($this, 'accordion_shortcode'));

			//Accordion_Item
			add_shortcode('accordion_item', array($this, 'accordion_item_shortcode'));

			//Tooltip
			add_shortcode('tooltip', array($this, 'tooltip_shortcode'));

			//Slider
			add_shortcode('carousel', array($this, 'slider_shortcode'));
			add_shortcode('slider', array($this, 'slider_shortcode'));

			//Slide
			add_shortcode('carousel_item', array($this, 'slide_shortcode'));
			add_shortcode('slide', array($this, 'slide_shortcode'));

			//Query
			add_shortcode('query', array($this, 'query_shortcode'));

			//Move wpautop filter to AFTER shortcode is processed
			//@TODO "Nebula" 0: The following may be adding a <br> tag after certain plugin functionality?
			//remove_filter('the_content', 'wpautop');
			//add_filter('the_content', 'wpautop' , 99);
			//add_filter('the_content', 'shortcode_unautop', 100);

			//Add Nebula Toolbar to TinyMCE
			add_action('admin_init', array($this, 'add_shortcode_button'));
		}

		//Call a widget via a shortcode
		//Use [widget widget_name="nebula_linked_image" image="http://placehold.it/300x300" url="http://google.com"]
		public function widget($atts){
			global $wp_widget_factory;

			//Get widget fields
			$instance = array();
			foreach ( $atts as $attribute => $value ){
				if ( $attribute !== 'widget_name' ){
					$instance[$attribute] = $value;
				}
			}

			extract(shortcode_atts(array(
				'widget_name' => false,
			), $atts));

			ob_start();

			//Call the widget directly via PHP: https://codex.wordpress.org/Template_Tags/the_widget
			the_widget(esc_html($widget_name), $instance, array(
				'widget_id' => 'arbitrary-instance-' . random_int(100000, 999999), //PHP 7.4 use numeric separators here
				'before_widget' => '',
				'after_widget' => '',
				'before_title' => '',
				'after_title' => ''
			));

			$output = ob_get_contents();
			ob_end_clean();

			return $output;
		}

		//Get flags where a parameter is declared in $atts that exists without a declared value
		/* Usage:
			$flags = get_flags($atts);
			if (in_array('your_flag', $flags){
				//Flag is present
			}
		*/
		public function get_flags($atts){
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

		public function div_shortcode($atts, $content=''){
			extract(shortcode_atts(array("class" => '', "style" => '', "open" => '', "close" => ''), $atts));

			if ( $content ){
				$div = '<div class="nebula-div ' . esc_attr($class) . '" style="' . esc_attr($style) . '">' . do_shortcode($content) . '</div>';
			} else {
				if ( $close ){
					$div = '</div>';
				} else {
					$div = '<div class="nebula-div nebula-div-open' . esc_attr($class) . '" style="' . esc_attr($style) . '">';
				}
			}
			return $div;
		}

		public function container_shortcode($atts, $content=''){
			extract(shortcode_atts( array('class' => '', 'style' => ''), $atts));
			return '<div class="nebula-container container ' . esc_attr($class) . '" style="' . esc_attr($style) . '">' . do_shortcode($content) . '</div>';
		}

		public function row_shortcode($atts, $content=''){
			extract(shortcode_atts( array('class' => '', 'style' => ''), $atts));
			return '<div class="nebula-row row ' . esc_attr($class) . '" style="' . esc_attr($style) . '">' . do_shortcode($content) . '</div>';
		}

		public function column_shortcode($atts, $content=''){
			extract(shortcode_atts(array('scale' => 'md', 'columns' => '', 'offset' => '', 'centered' => '', 'class' => '', 'style' => ''), $atts));

			$flags = $this->get_flags($atts);
			$columns = str_replace(array('one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve'), array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'), $columns);

			if ( in_array('centered', $flags) ){
				$centered = 'col-centered';
				$key = array_search('centered', $flags);
				unset($flags[$key]);
			}

			// $push is unused:
			if ( !empty($push) ){
				$push = 'offset_' . $scale . '_' . $push;
			}

			return '<div class="' . esc_attr('nebula-columns col-' . $scale . '-' . $columns . ' ' . $offset . ' ' . $centered . ' ' . $class) . '" style="' . esc_attr($style) . '">' . do_shortcode($content) . '</div>';
		}

		//Note: It is semantically incorrect for an <hr> to appear within a <p> tag, so be careful of WordPress wrapping this shortcode in a <p> tag.
		public function divider_shortcode($atts){
			extract(shortcode_atts(array("space" => '0', "above" => '0', "below" => '0'), $atts));

			$divider = '<hr class="nebula-divider" />';
			if ( $space ) {
				$above = $space;
				$below = $space;
				$divider = '<hr class="nebula-divider" style="' . esc_attr('margin-top: ' . $above . 'px; margin-bottom: ' . $below . 'px;') . '"/>';
			}

			return $divider;
		}

		public function icon_shortcode($atts){
			extract(shortcode_atts(array('type' => '', 'mode' => 'solid', 'color' => 'inherit', 'size' => 'inherit', 'class' => ''), $atts));

			//Prepend the fa- prefix to the icon name if not provided
			if ( strpos($type, 'fa-') === false ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
				$type = 'fa-' . $type;
			}

			//Choose the appropriate FA weight
			$mode = 'fa-solid';
			if ( $mode === 'regular' || $mode === 'r' || $mode === 'far' ){
				$mode = 'fa-regular';
			} elseif ( $mode === 'light' || $mode === 'l' || $mode === 'fal' ){
				$mode = 'fa-light';
			} elseif ( $mode === 'brand' || $mode === 'b' || $mode === 'fab' ){
				$mode = 'fa-brands';
			}

			$extra_style = ( !empty($color) )? 'color:' . $color . ';' :'';
			$extra_style .= ( !empty($size) )? 'font-size:' . $size . ';' :'';

			return '<i class="' . esc_attr($class . ' nebula-icon-shortcode ' . $mode . ' fa-fw ' . $type) . '" style="' . esc_attr($extra_style) . '"></i>';
		}

		public function button_shortcode($atts, $content=''){
			extract(shortcode_atts(array('size' => 'md', 'type' => 'brand', 'icon' => false, 'href' => '#', 'target' => false, 'class' => '', 'style' => ''), $atts));

			if ( $target ){
				$target = ' target="' . esc_attr($target) . '"';
			}

			if ( $icon ){
				if ( strpos($icon, 'fa-') === false){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
					$icon = 'fa-' . $icon;
				}
				$icon = '<i class="fa-solid fa-fw ' . esc_attr($icon) . '"></i> ';
			}

			if ( $size ){
				$size = str_replace(array('small', 'medium', 'large'), array('sm', 'md', 'lg'), $size);
				if ( strpos($size, 'btn-') === false){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
					$size = 'btn-' . $size;
				}
			}

			return '<div class="nebula-button"><a class="' . esc_attr('btn btn-' . $type . ' ' . $size . ' ' . $class) . '" href="' . esc_attr($href) . '"' . esc_attr($target) . '>' . $icon . $content . '</a></div>';
		}

		public function space_shortcode($atts){
			extract(shortcode_atts(array("height" => '20'), $atts));
			return '<div class="space" style="' . esc_attr('height:' . $height . 'px;') . '" ></div>';
		}

		public function clear_shortcode(){
			return '<div class="clearfix" style="clear: both;"></div>';
		}

		public function map_shortcode($atts){
			extract(shortcode_atts(array("key" => '', "mode" => 'place', "q" => '', "center" => '', "origin" => '', "destination" => '', "waypoints" => '', "avoid" => '', "zoom" => '', "maptype" => 'roadmap', "language" => '', "region" => '', "width" => '100%', "height" => '350', "class" => '', "style" => ''), $atts));
			$flags = $this->get_flags($atts);

			//@todo "Nebula" 0: Use null coalescing operator here if possible
			if ( empty($key) ){
				$key = $this->get_option('google_browser_api_key');
			}
			if ( !empty($q) ){
				$q = '&q=' . esc_url($q);
			}

			/** These are unused: $origin, $destination, $waypoints, $avoid */
			if ( $mode === 'directions' ){
				if ( $origin != '' ){
					$origin = '&origin=' . esc_url($origin);
				}
				if ( $destination != '' ){
					$destination = '&destination=' . esc_url($destination);
				}
				if ( $waypoints != '' ){
					$waypoints = '&waypoints=' . esc_url($waypoints);
				}
				if ( $avoid != '' ){
					$avoid = '&avoid=' . esc_url($avoid);
				}
			}
			if ( !empty($center) ){
				$center = '&center=' . esc_url($center);
			}
			if ( !empty($language) ){
				$language = '&language=' . esc_url($language);
			}
			if ( !empty($region) ){
				$region = '&region=' . esc_url($region);
			}
			if ( !empty($zoom) ){
				$zoom = '&zoom=' . esc_url($zoom);
			}

			$return = '<iframe class="nebula-googlemap-shortcode googlemap ' . esc_attr($class) . '" width="' . esc_attr($width) . '" height="' . esc_attr($height) . '" frameborder="0" src="https://www.google.com/maps/embed/v1/' . $mode . '?key=' . $key . $q . $zoom . $center . '&maptype=' . $maptype . $language . $region . '" style="border: 0; ' . esc_attr($style) . '" allowfullscreen loading="lazy">';

			return $return;
		}

		public function vimeo_shortcode($atts){
			extract(shortcode_atts(array("id" => null, "height" => '', "width" => '', "autoplay" => '0', "badge" => '1', "byline" => '1', "color" => '00adef', "loop" => '0', "portrait" => '1', "title" => '1'), $atts));

			$vimeo_data = $this->video_meta('vimeo', $id);
			$vimeo = '<div class="nebula-vimeo ratio ratio-16x9">';
			if ( !empty($vimeo_data) && empty($vimeo_data['error']) ){
				$vimeo .= '<iframe id="' . esc_attr($vimeo_data['safetitle']) . '" class="vimeo" src="//player.vimeo.com/video/' . esc_attr($id) . '?api=1&player_id=' . esc_attr($vimeo_data['safetitle'], 'url') . '" width="' . esc_attr($width) . '" height="' . esc_attr($height) . '" autoplay="' . esc_attr($autoplay) . '" badge="' . esc_attr($badge) . '" byline="' . esc_attr($byline) . '" color="' . esc_attr($color) . '" loop="' . esc_attr($loop) . '" portrait="' . esc_attr($portrait) . '" title="' . esc_attr($title) . '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen loading="lazy"></iframe>';
			} else {
				$vimeo .= '<iframe class="vimeo" src="//player.vimeo.com/video/' . esc_attr($id) . '" width="' . esc_attr($width) . '" height="' . esc_attr($height) . '" autoplay="' . esc_attr($autoplay) . '" badge="' . esc_attr($badge) . '" byline="' . esc_attr($byline) . '" color="' . esc_attr($color) . '" loop="' . esc_attr($loop) . '" portrait="' . esc_attr($portrait) . '" title="' . esc_attr($title) . '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen loading="lazy"></iframe>';

				if ( $this->is_dev() ){
					$vimeo .= '<script>console.warn("' . esc_html($vimeo_data['error']) . ' (via Vimeo shortcode)");</script>';
				}
			}
			$vimeo .= '</div>';

			return $vimeo;
		}

		public function youtube_shortcode($atts){
			extract(shortcode_atts(array("id" => null, "height" => '', "width" => '', "rel" => 0, "ignore_visibility" => '', "class" => ''), $atts));

			$flags = $this->get_flags($atts);
			if ( in_array('ignore_visibility', $flags) ){
				$ignore_visibility = 'ignore-visibility';
			}

			$youtube_data = $this->video_meta('youtube', $id);
			$youtube = '<div class="nebula-youtube ratio ratio-16x9">';
			if ( !empty($youtube_data) && empty($youtube_data['error']) ){
				//Note: removed &origin=' . youtube_meta($id, 'origin') . ' right before &rel= due to console warnings. Doesn't seem to be an issue.
				$youtube .= '<iframe id="' . esc_attr($youtube_data['safetitle']) . '" class="' . esc_attr('youtube ' . $class . ' ' . $ignore_visibility) . '" width="' . esc_attr($width) . '" height="' . esc_attr($height) . '" src="//www.youtube.com/embed/' . esc_attr($youtube_data['id'], 'url') . '?wmode=transparent&enablejsapi=1&rel=' . esc_attr($rel, 'url') . '" frameborder="0" allowfullscreen="" loading="lazy"></iframe>';
			} else {
				$youtube .= '<iframe class="' . esc_attr('no-api ' . $class . ' ' . $ignore_visibility) . '" width="' . esc_attr($width) . '" height="' . esc_attr($height) . '" src="//www.youtube.com/embed/' . esc_attr($id, 'url') . '?wmode=transparent&enablejsapi=1&rel=' . esc_attr($rel, 'url') . '" frameborder="0" allowfullscreen loading="lazy"></iframe>';
				if ( $this->is_dev() ){
					$youtube .= '<script>console.warn("(' . esc_attr($youtube_data['error']) . ' (via Youtube shortcode)");</script>';
				}
			}
			$youtube .= '</div>';

			return $youtube;
		}

		public function code_shortcode($atts, $content=''){
			extract(shortcode_atts(array('class' => '', 'style' => ''), $atts));
			$content = htmlspecialchars_decode($content);
			return '<code class="nebula-code ' . esc_attr($class) . '" style="' . esc_attr($style) . '" >' . htmlentities($content) . '</code>';
		}

		public function pre_shortcode($atts, $content=''){
			extract(shortcode_atts(array('lang' => '', 'language' => '', 'color' => '', 'force' => false, 'br' => false, 'class' => '', 'style' => ''), $atts));

			if ( empty($this->shortcode_flags['pre']) && !nebula()->is_background_request() ){
				echo '<link rel="stylesheet" type="text/css" href="' . get_template_directory_uri() . '/assets/css/pre.css" />';
				$this->shortcode_flags['pre'] = 1;
			}

			$flags = $this->get_flags($atts);
			if ( !in_array('br', $flags) ){
				$content = preg_replace('#<br\s*/?>#', '', $content);
			}

			$pre_tag_open = '';
			$pre_tag_close = '';
			if ( strpos($content, '<pre') === false && $force === false ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
				$content = htmlspecialchars_decode($content);
				$content = htmlspecialchars($content);
				$pre_tag_open = '<pre class="nebula-code ' . esc_attr($lang) . '">';
				$pre_tag_close = '</pre>';
			}

			if ( empty($lang) && !empty($language) ){
				$lang = $language;
			}
			$vislang = $this->visible_language($lang);

			$return = '<div class="nebula-code-con clearfix ' . esc_attr(strtolower($lang)) . '"><span class="nebula-code codetitle ' . esc_attr(strtolower($lang)) . '">' . $vislang . '</span>' . $pre_tag_open . $content . $pre_tag_close . '</div>';

			return $return;
		}

		public function gist_shortcode($atts, $content=''){
			extract(shortcode_atts(array('lang' => '', 'language' => '', 'color' => '', 'file' => '', 'class' => '', 'style' => ''), $atts));

			if ( empty($this->shortcode_flags['pre']) && !nebula()->is_background_request() ){
				echo '<link rel="stylesheet" type="text/css" href="' . get_template_directory_uri() . '/assets/css/pre.css" />';
				$this->shortcode_flags['pre'] = 1;
			}

			if ( empty($lang) && !empty($language) ){
				$lang = $language;
			}
			$vislang = $this->visible_language($lang);

			if ( $file ){
				$file = '?file=' . $file;
			}

			$return = '<span class="nebula-gist nebula-code codetitle ' . esc_attr(strtolower($lang)) . '" style="color: ' . esc_attr($color) . ';">' . $vislang . '</span><div class="' . esc_attr('nebula-code ' . strtolower($lang) . ' ' . $class) . '" style="';
			if ( $color != '' ){
				$return .= 'border: 1px solid ' . esc_attr($color) . '; border-left: 5px solid ' . esc_attr($color) . ';';
			}
			$return .= esc_attr($style) . '" ><script type="text/javascript" src="' . $content . $file . '"></script></div>';

			return $return;
		}

		//Is content unnecessary?
		public function github_shortcode($atts, $content=''){
			extract(shortcode_atts(array('lang' => '', 'language' => '', 'color' => '', 'file' => '', 'class' => '', 'style' => ''), $atts));

			if ( !empty($file) ){
				WP_Filesystem();
				global $wp_filesystem;
				$file_contents = $wp_filesystem->get_contents($file);

				if ( empty($this->shortcode_flags['pre']) && !nebula()->is_background_request() ){
					echo '<link rel="stylesheet" type="text/css" href="' . get_template_directory_uri() . '/assets/css/pre.css" />';
					$this->shortcode_flags['pre'] = 1;
				}

				if ( empty($lang) && !empty($language) ){
					$lang = $language;
				}
				$vislang = $this->visible_language($lang);

				$return = '<div class="nebula-code-con clearfix ' . esc_attr(strtolower($lang)) . '"><span class="nebula-code codetitle ' . strtolower($lang) . '" style="color: ' . $color . ';">' . $vislang . '</span><pre class="nebula-code ' . $lang . ' ' . $class . '" style="';
				if ( $color != '' ){
					$return .= 'border: 1px solid ' . esc_attr($color) . '; border-left: 5px solid ' . esc_attr($color) . ';';
				}
				$return .= esc_attr($style) . '" >' . $file_contents . '</pre></div>';

				return $return;
			}
		}

		//Modify the language string into a proper visible language
		public function visible_language($lang){
			$lang = strtolower(str_replace(array('"', "'", "&quot;", "&#039;"), '', $lang));
			$search = array('actionscript', 'apache', 'css', 'directive', 'html', 'js', 'javascript', 'jquery', 'mysql', 'php', 'regex', 'shortcode', 'sql');
			$replace = array('ActionScript', 'Apache', 'CSS', 'Directive', 'HTML', 'JavaScript', 'JavaScript', 'jQuery', 'MySQL', 'PHP', 'RegEx', 'Shortcode', 'SQL');

			return str_replace($search, $replace, $lang);
		}

		public function accordion_shortcode($attributes, $content=''){
			extract(shortcode_atts(array('class' => '', 'style' => ''), $attributes));
			$html = '<div class="accordion ' . esc_attr($class) . '" style="' . esc_attr($style) . '" role="tablist">' . do_shortcode($content) . '</div>';
			return $html;
		}

		public function accordion_item_shortcode($attributes, $content=''){
			extract(shortcode_atts(array('class' => '', 'style' => '', 'title' => '', 'default' => 'show'), $attributes));
			$unique_id = bin2hex(random_bytes(16));

			return '<div class="card">
				<div class="card-header">
					<h5 class="mb-0" id="heading' . $unique_id . '">
						<button class="btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#collapse' . $unique_id . '" aria-expanded="false" aria-controls="collapse' . $unique_id . '"><i class="fa-solid fa-plus"></i> ' . $title . '</button>
					</h5>
				</div>

				<div id="collapse' . $unique_id . '" aria-labelledby="heading' . $unique_id . '" class="collapse ' . esc_attr($default) . ' ' . esc_attr($class) . '">
					<p class="card-body">' . $content . '</p>
				</div>
			</div>';
		}

		public function tooltip_shortcode($atts, $content=''){
			extract(shortcode_atts(array('tip' => '', 'placement' => 'top', 'class' => '', 'style' => ''), $atts));
			return '<span class="nebula-tooltip ttip ' . esc_attr($class) . '" data-bs-toggle="tooltip" data-bs-placement="' . esc_attr($placement) . '" title="' . esc_attr($tip) . '" style="' . esc_attr($style) . '">' . esc_attr($content) . '</span>';
		}

		public function slider_shortcode($atts, $content=''){
			extract(shortcode_atts(array('id' => false, 'indicators' => true), $atts));
			$flags = $this->get_flags($atts);

			//@todo "Nebula" 0: Use null coalescing operator here if possible. Probably not possible but think about it.
			if ( !$id ){
				$id = 'nebula-slider-' . random_int(1, 10000); //PHP 7.4 use numeric separators here
			} elseif ( strlen($id) > 0 && ctype_digit(substr($id, 0, 1)) ){
				$id = 'nebula-slider-' . $id;
			}

			$indicators = '';
			if ( !empty($indicators) ){
				$indicators = 'auto-indicators';
			}

			$return = '<div id="' . esc_attr($id) . '" class="carousel slide ' . esc_attr($indicators) . '" data-ride="carousel">';
			$return .= $this->parse_shortcode_content(do_shortcode($content));
			$return .= '<a class="left carousel-control" href="#' . esc_attr($id) . '" data-slide="prev"><span class="icon-prev"></span><span class="visually-hidden">Previous</span></a><a class="right carousel-control" href="#' . esc_attr($id) . '" data-slide="next"><span class="icon-next"></span><span class="visually-hidden">Next</span></a></div>';

			return $return;
		}

		public function slide_shortcode($atts, $content=''){
			extract(shortcode_atts(array('link' => '', 'target' => ''), $atts));

			$linkopen = '';
			$linkclose = '';

			if ( !empty($link) ){
				$linkopen = '<a href="' . esc_attr($link) . '">';
				if ( !empty($target) ){
					$linkopen = '<a href="' . esc_attr($link) . '" target="' . esc_attr($target) . '">';
				}

				$linkclose = '</a>';
			}

			return '<div class="carousel-item">' . $linkopen . '<img src="' . esc_attr($content) . '" importance="low" loading="lazy">' . $linkclose . '</div>'; //need <div class="carousel-inner">
		}

		//Query Post Shortcode
		//[query args="post_type=post&category_name=home-garden&monthnum=10"]
		public function query_shortcode($attributes){
			extract(shortcode_atts(array('args' => ''), $attributes));

			//Convert to an array so that 'paged' can be replaced if it is already present (or added if it is not) then convert back to a query string
			parse_str($args, $args_arr);
			$args_arr['paged'] = ( get_query_var('paged') )? get_query_var('paged') : get_query_var('page', 0);
			$args = html_entity_decode(urldecode(http_build_query($args_arr)));

			query_posts($args); //Run the query

			ob_start(); //Output buffer because the loop echoes
			get_template_part('loop');
			$output = ob_get_contents(); //Get the loop contents (buffered)
			ob_end_clean();

			wp_reset_query();

			return $output;
		}

		//Remove empty <p> tags from Wordpress content (for nested shortcodes)
		public function parse_shortcode_content($content){
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
			$content = str_replace(array('<p> </p>'), '', $content);

			return $content;
		}

		public function add_shortcode_button(){
			if ( current_user_can('edit_posts') || current_user_can('edit_pages') ){
				add_filter('mce_external_plugins', array($this, 'add_shortcode_plugin'));
				add_filter('mce_buttons_3', array($this, 'register_shortcode_button'));
			}

		}

		public function register_shortcode_button($buttons){
			array_push($buttons, "nebulaaccordion", /* "nebulabio", */ "nebulabutton", "nebulaclear", "nebulacode", "nebuladiv", "nebulacolgrid", "nebulacontainer", "nebularow", "nebulacolumn", "nebulaicon", "nebulaline", "nebulamap", "nebulaspace", "nebulaslider", "nebulatooltip", "nebulavideo");
			return $buttons;
		}

		public function add_shortcode_plugin($plugin_array){
			$plugin_array['nebulatoolbar'] = get_template_directory_uri() . '/assets/js/shortcodes.js';
			return $plugin_array;
		}
	}
}