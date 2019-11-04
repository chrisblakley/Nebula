<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Optimization') ){
	trait Optimization {
		public function hooks(){
			add_action('send_headers', array($this, 'nebula_http2_ob_start'));
			add_filter('style_loader_src', array($this, 'http2_server_push_header'), 2, 1);
			add_filter('script_loader_src', array($this, 'http2_server_push_header'), 2, 1);

			add_filter('wp_enqueue_scripts', array($this, 'defer_async_additional_scripts'));
			add_filter('script_loader_tag', array($this, 'defer_async_scripts'), 10, 2);

			add_action('wp_enqueue_scripts', array($this, 'dequeue_lazy_load_styles'));
			add_action('wp_footer', array($this, 'dequeue_lazy_load_scripts'));
			add_action('wp_enqueue_scripts', array($this, 'dequeues'), 9999);
			add_action('wp_enqueue_scripts', array($this, 'remove_actions'), 9999);

			add_action('send_headers', array($this, 'service_worker_scope'));
			add_action('admin_init', array($this, 'plugin_force_settings'));

			add_action('init', array($this, 'disable_wp_emojicons'));
			add_filter('wp_resource_hints', array($this, 'remove_emoji_prefetch'), 10, 2); //Remove dns-prefetch for emojis
			add_filter('tiny_mce_plugins', array($this, 'disable_emojicons_tinymce')); //Remove TinyMCE Emojis too
			add_filter('wpcf7_load_css', '__return_false'); //Disable CF7 CSS resources (in favor of Bootstrap and Nebula overrides)

			add_filter('wp_default_scripts', array($this, 'remove_jquery_migrate'));
			add_action('wp_enqueue_scripts', array($this, 'move_jquery_to_footer'));
			add_action('wp_head', array($this, 'listen_for_jquery_footer_errors'));
			add_action('wp_head', array($this, 'embed_critical_styles'));

			add_action('send_headers', array($this, 'server_timing_header'));
			add_action('wp_footer', array($this, 'output_console_debug_timings'));
			add_action('admin_footer', array($this, 'output_console_debug_timings'));

			add_filter('jpeg_quality', array($this, 'jpeg_quality'));
			add_filter('intermediate_image_sizes_advanced', array($this, 'create_max_width_size_proportionally'), 10, 2);
			add_filter('post_thumbnail_size', array($this, 'limit_thumbnail_size'), 10, 2);
			add_filter('nebula_thumbnail_src_size', array($this, 'limit_image_size'), 10, 2);
			add_filter('max_srcset_image_width', array($this, 'smaller_max_srcset_image_width'), 10, 2); //Limit width of content images

			add_action('wp_head', array($this, 'prebrowsing'));
			add_action('admin_head', array($this, 'prebrowsing'));
		}

		//Set the JPG compression for more optimized images (Note: Full Size images are not changed)
		public function jpeg_quality($arg){
			$new_quality = $this->get_option('jpeg_quality'); //Get the quality setting from Nebula Options
			if ( empty($new_quality) ){
				return 82; //Fallback to 82
			}

			return intval($new_quality);
		}

		//Create max image size for each uploaded image while maintaining aspect ratio
		//This is done regardless of if the option is enabled to make this size ready if the option becomes enabled later
		public function create_max_width_size_proportionally($sizes, $metadata){
			if ( !empty($metadata['width']) && !empty($metadata['height']) ){
				//Create a max size of 1200px wide
				$lg_width = $metadata['width'];
				$lg_height = $metadata['height'];
				if ( $metadata['width'] > 1200 ){
					$lg_width = 1200;
					$lg_height = ($metadata['height']*$lg_width)/$metadata['width']; //Original Height * Desired Width / Original Width = Desired Height
				}

				$sizes['max_size'] = array(
					'width' => $lg_width,
					'height' => $lg_height,
					'crop' => true
				);

				//Create a max size of 800px wide for use with the Save Data header
				$sm_width = $metadata['width'];
				$sm_height = $metadata['height'];
				if ( $metadata['width'] > 800 ){
					$sm_width = 800;
					$sm_height = ($metadata['height']*$sm_width)/$metadata['width']; //Original Height * Desired Width / Original Width = Desired Height
				}

				$sizes['max_size_less'] = array(
					'width' => $sm_width,
					'height' => $sm_height,
					'crop' => true
				);
			}

			return $sizes;
		}

		//Limit image size when being called
		public function limit_thumbnail_size($size, $id){
			return $this->limit_image_size($size, $id);
		}
		public function limit_image_size($size, $id=false){
			if ( $this->get_option('limit_image_dimensions') ){
				if ( is_string($size) && ($size === 'post-thumbnail' || $size === 'full') ){
					if ( $this->is_save_data() ){
						return 'max_size_less';
					}

					return 'max_size';
				}
			}

			return $size;
		}

		//Reduce the max srcset image width from 1600px to 1200px
		function smaller_max_srcset_image_width($size, $size_array){
			if ( $this->get_option('limit_image_dimensions') ){
				$size = ( $this->is_save_data() )? 800 : 1200; //If Save Data header is present (from user) use smaller max size
			}

			return $size;
		}

		//Check if the Save Data header exists (to use less data)
		public function use_less_data(){return $this->is_save_data();}
		public function is_lite(){return $this->is_save_data();}
		public function is_save_data(){
			if ( isset($_SERVER['HTTP_SAVE_DATA']) && stristr($_SERVER['HTTP_SAVE_DATA'], 'on') !== false ){
				return true;
			}

			return false;
		}

		//Allow scripts to be registered with additional attributes
		public function register_script($handle=null, $src=null, $attributes=array(), $deps=array(), $ver=false, $in_footer=false){
			wp_register_script($handle, $src, $deps, $ver, $in_footer);

			if ( !empty($attributes) ){
				$attributes = ( is_array($attributes) )? $attributes : array($attributes); //Make sure it is an array
				foreach ( $attributes as $attribute ){
					wp_script_add_data($handle, $attribute, true);
				}
			}
		}

		//Defer and Async specific scripts. This only works with registered/enqueued scripts!
		public function defer_async_additional_scripts(){
			$to_defer = apply_filters('nebula_defer_scripts', array('jquery-migrate', 'jquery.form', 'contact-form-7', 'wp-embed')); //Allow other functions to hook in to add defer to existing scripts
			$to_async = apply_filters('nebula_async_scripts', array()); //Allow other functions to hook in to add async to existing scripts

			//Defer scripts
			foreach ( $to_defer as $handle ){
				wp_script_add_data($handle, 'defer', true);
			}

			//Async scripts
			foreach ( $to_async as $handle ){
				wp_script_add_data($handle, 'async', true);
			}
		}

		//Add defer, async, and/or crossorigin attributes to scripts
		public function defer_async_scripts($tag, $handle){
			$crossorigin_exececution = wp_scripts()->get_data($handle, 'crossorigin');
			$defer_exececution = wp_scripts()->get_data($handle, 'defer');
			$async_exececution = wp_scripts()->get_data($handle, 'async');
			$module_execution = wp_scripts()->get_data($handle, 'module');

			//Add module type attribute if it is requested
			if ( !empty($module_execution) && strpos($tag, "type='module'") === false ){
				$tag = str_replace("type='text/javascript'", "type='module'", $tag); //Change the type='text/javascript' attribute to type='module'
			}

			//Add crossorigin attribute if it is requested and does not already exist
			if ( !empty($crossorigin_exececution) && strpos($tag, 'crossorigin=') === false ){
				$tag = str_replace(' src', ' crossorigin="anonymous" src', $tag); //Add the crossorigin attribute
			}

			//Ignore if neither defer nor async attribute is found
			if ( empty($defer_exececution) && empty($async_exececution) ){
				return $tag;
			}

			//Abort adding async/defer for scripts that have this script as a dependency...? Maybe not?
			/*
				foreach ( wp_scripts()->registered as $script ){
					if ( in_array($handle, $script->deps, true) ){
						return $tag;
					}
				}
			*/

			//Add defer attribute if it is requested and does not already exist
			if ( !empty($defer_exececution) && strpos($tag, 'defer=') === false ){
				$tag = str_replace(' src', ' defer="defer" src', $tag); //Add the defer attribute
			}

			//Add async attribute if it is requested and does not already exist
			if ( !empty($async_exececution) && strpos($tag, 'async=') === false ){
				$tag = str_replace(' src', ' async="async" src', $tag); //Add the async attribute
			}

			return $tag;
		}

		//Prep assets for lazy loading. Be careful of dependencies!
		//When lazy loading JS files, the window load listener may not trigger! Be careful!
		//Array should be built as: handle => condition
		public function lazy_load_assets(){
			$assets = array(
				'styles' => array(
					'nebula-font_awesome' => 'all',
					'wp-pagenavi' => '.wp-pagenavi',
				),
				'scripts' => array(),
			);

			return apply_filters('nebula_lazy_load_assets', $assets); //Allow other plugins/themes to lazy-load assets
		}

		//Dequeue styles prepped for lazy-loading
		public function dequeue_lazy_load_styles(){
			$lazy_load_assets = $this->lazy_load_assets();

			foreach ( $lazy_load_assets['styles'] as $handle => $condition ){
				wp_dequeue_style($handle);
			}
		}

		//Dequeue scripts prepped for lazy-loading
		public function dequeue_lazy_load_scripts(){
			if ( $this->is_admin_page() ){ //Do not modify scripts on admin pages (Gutenberg compatibility)
				return false;
			}

			$lazy_load_assets = $this->lazy_load_assets();
			foreach ( $lazy_load_assets['scripts'] as $handle => $condition ){
				wp_dequeue_script($handle);
			}
		}

		//Allow the service worker to control everything without needing to move it out of the theme
		public function service_worker_scope(){
			if ( $this->get_option('service_worker') ){
				header('Service-Worker-Allowed: /');
			}
		}

		//Start output buffering so headers can be sent later for HTTP2 Server Push
		public function nebula_http2_ob_start(){
			if ( !$this->is_admin_page(true, true) ){ //Exclude admin, login, and Customizer pages
				ob_start();
			}
		}

		//Use HTTP2 Server Push to push multiple CSS and JS resources at once
		//This uses a link preload header, so these resources must be used within a few seconds of window load.
		//@todo "Nebula" 0: This is occassionally triggering console warnings that the resources are not used within a few seconds of window load...
		public function http2_server_push_header($src){
			if ( !$this->is_admin_page(true, true) && $this->get_option('service_worker') && file_exists($this->sw_location(false)) ){ //If not in the admin section (including Customizer and login) and if Service Worker is enabled (and file exists)
				$filetype = ( strpos($src, '.css') )? 'style' : 'script'; //Determine the resource type (this is only used with CSS and JS)
				if ( strpos($src, $this->url_components('sld')) > 0 ){ //Only push local files
					header('Link: <' . esc_url(str_replace($this->url_components('basedomain'), '', strtok($src, '#'))) . '>; rel=preload; as=' . $filetype, false); //Send the header for the HTTP2 Server Push (strtok to remove everything after and including "#")
				}
			}

			return $src;
		}

		//Set Server Timing header
		public function server_timing_header(){
			if ( $this->is_dev() || isset($_GET['timings']) ){ //Only output server timings for developers, or if timings query string is present
				$this->finalize_timings();
				$server_timing_header_string = 'Server-Timing: ';

				//Loop through all times
				foreach ( $this->server_timings as $label => $data ){
					if ( !empty($data['time']) ){
						$time = $data['time'];
					} elseif ( intval($data) ){
						$time = intval($data);
					} else {
						continue;
					}

					//Ignore unfinished, 0 timings, or non-logging entries
					if ( $label === 'categories' || !empty($data['active']) || round($time*1000) <= 0 || (!empty($data['log']) && $data['log'] === false) ){
						continue;
					}

					$name = str_replace(array(' ', '(', ')', '[', ']'), '', strtolower($label));
					if ( $label === 'PHP [Total]' ){
						$name = 'total';
					}
					$server_timing_header_string .= $name . ';dur=' . round($time*1000) . ';desc="' . $label . '",';
				}

				header($server_timing_header_string);
			}
		}

		//Include server timings for developers
		public function output_console_debug_timings(){
			if ( $this->is_dev() || isset($_GET['timings']) ){ //Only output server timings for developers or if timings query string present
				$this->finalize_timings();

				foreach ( $this->server_timings as $label => $data ){
					if ( !empty($data['time']) ){
						$time = $data['time'];
					} elseif ( intval($data) ){
						$time = intval($data);
					} else {
						continue;
					}

					if ( $label === 'categories' || !empty($data['active']) || round($time*1000) <= 0 || (!empty($data['log']) && $data['log'] === false) ){
						continue;
					}

					$start_time = ( !empty($data['start']) )? round(($data['start']-$_SERVER['REQUEST_TIME_FLOAT'])*1000) : -1;

					$testTimes['[PHP] ' . $label] = array(
						'start' => $start_time, //Convert seconds to milliseconds
						'duration' => round($time*1000), //Convert seconds to milliseconds
						'elapsed' => ( is_float($start_time) )? $start_time+round($time*1000) : -1,
					);
				}

				//Sort by elapsed time
				uasort($testTimes, function($a, $b){
					return $a['elapsed'] - $b['elapsed'];
				});

				echo '<script type="text/javascript">nebula.site.timings = ' . json_encode($testTimes) . ';</script>'; //Output the data to <head>
			}
		}

		//Determing if a page should be prepped using prefetch, or preconnect. This is called on the front end and on Admin pages
			//DNS-Prefetch = Resolve the DNS only to a domain.
			//Preconnect = Resolve both DNS and TCP to a domain.
			//Prefetch = Fully request a single resource and store it in cache until needed. Do not combine with preload!
			//Preload = Fully request a single resource before it is needed. Do not combine with prefetch! These must be used within a few seconds of window load.
			//Note: Prerender is deprecated

			//Note: WordPress automatically uses dns-prefetch on enqueued resource domains.
			//Note: Additional preloading for lazy-loaded CSS happens in /libs/Scripts.php

			//To hook into the arrays use:
/*
			add_filter('nebula_preconnect', function($array){
				$array[] = '//example.com';
				return $array;
			});
*/

		public function prebrowsing(){
			$override = apply_filters('pre_nebula_prebrowsing', null);
			if ( isset($override) ){return;}

			$debug_comment = ( $this->is_dev() )? '<!-- Server-side -->' : '';

			/*==========================
			 DNS-Prefetch & Preconnect
			 Resolve DNS and TCP to a domain.
			 ===========================*/

			$default_preconnects = array();

			//Google fonts if used
			if ( $this->get_option('remote_font_url') ){
				if ( strpos($this->get_option('remote_font_url'), 'google') || strpos($this->get_option('remote_font_url'), 'gstatic') ){
					$default_preconnects[] = '//fonts.gstatic.com';
				} elseif ( strpos($this->get_option('remote_font_url'), 'typekit') ){
					$default_preconnects[] = '//use.typekit.net';
				}
			}

			//GCSE on 404 pages
			if ( is_404() && $this->get_option('cse_id') ){
				$default_preconnects[] = '//www.googleapis.com';
			}

			//Disqus commenting
			if ( is_single() && $this->get_option('comments') && $this->get_option('disqus_shortname') ){
				$default_preconnects[] = '//' . $this->get_option('disqus_shortname') . '.disqus.com';
			}

			//Loop through all of the preconnects
			$preconnects = apply_filters('nebula_preconnect', $default_preconnects);
			foreach ( $preconnects as $preconnect ){
				echo '<link rel="preconnect" href="' . $preconnect . '" crossorigin="anonymous" />' . $debug_comment;
			}

			/*==========================
			 Prefetch
			 Fully request a single resource and store it in cache until needed. Do not combine with preload!
			 ===========================*/

			$default_prefetches = array();

			//Subpages
			if ( !is_front_page() ){
				$default_prefetches[] = home_url('/'); //Prefetch the home page on subpages
			}

			//Search Results
			if ( is_search() ){
				global $wp_query;
				if ( !empty($wp_query->posts) && !empty($wp_query->posts['0']) ){ //If has search results
					$default_prefetches[] = get_permalink($wp_query->posts['0']->ID); //Prefetch the first search result
				}
			}

			//404 Pages
			if ( is_404() ){
				//If Nebula finds a match based on context clues, prefetch that too
				if ( !empty($this->error_404_exact_match) ){
					$default_prefetches[] = get_permalink($this->error_404_exact_match->ID);
				}

				//If has page suggestions prefetch the first one
				if ( !empty(nebula()->error_query) && nebula()->error_query->have_posts() ){
					$default_prefetches[] = get_permalink(nebula()->error_query->posts[0]->ID);
				}
			}

			//Loop through all of the prefetches
			$prefetches = apply_filters('nebula_prefetches', $default_prefetches); //Allow child themes and plugins to prefetch resources via Nebula too
			foreach ( $prefetches as $prefetch ){
				echo '<link rel="prefetch" href="' . $prefetch . '" crossorigin="anonymous" />' . $debug_comment;
			}

			/*==========================
			 Preload
			 Fully request a single resource before it is needed. Do not combine with prefetch! These must be used within a few seconds of window load.
			 ===========================*/

			$default_preloads = array();

			//Google fonts if used
			if ( $this->get_option('remote_font_url') ){
				//$default_preloads[] = $this->get_option('remote_font_url'); //Oct 2019: disabling this for now as it delays render in Chrome
			}

			//Loop through all of the preloads
			$preloads = apply_filters('nebula_preloads', $default_preloads); //Allow child themes and plugins to preload resources via Nebula too
			foreach ( $preloads as $preload ){
				$filetype = 'fetch';
				switch ( $preload ){
					case strpos($preload, '.css'):
						$filetype = 'style';
						break;
					case strpos($preload, '.js'):
						$filetype = 'script';
						break;
					case strpos($preload, 'fonts.googleapis'):
					case strpos($preload, '.woff'):
						$filetype = 'font';
						break;
					case strpos($preload, '.jpg'):
					case strpos($preload, '.jpeg'):
					case strpos($preload, '.png'):
					case strpos($preload, '.gif'):
						$filetype = 'image';
						break;
					case strpos($preload, '.mp4'):
					case strpos($preload, '.ogv'):
					case strpos($preload, '.mov'):
						$filetype = 'video';
						break;
				}

				echo '<link rel="preload" href="' . $preload . '" as="fetch" crossorigin="anonymous" />';
			}
		}

		//Dequeue certain scripts
		public function dequeues(){
			$override = apply_filters('pre_nebula_dequeues', null);
			if ( isset($override) ){return;}

			if ( !is_admin() ){
				//Removing CF7 styles in favor of Bootstrap + Nebula
				wp_dequeue_style('contact-form-7');
				wp_deregister_script('wp-embed'); //WP Core WP-Embed - Override this only if embedding external WordPress posts into this WordPress site. Other oEmbeds are NOT AFFECTED by this!

				//Page specific dequeues
				if ( is_front_page() ){
					wp_deregister_style('thickbox'); //WP Core Thickbox - Override this if thickbox type gallery IS used on the homepage.
					wp_deregister_script('thickbox'); //WP Thickbox - Override this if thickbox type gallery IS used on the homepage.
				}
			}
		}

		//If Nebula Options are set to load jQuery in the footer, move it there.
		public function move_jquery_to_footer(){
			//Let other plugins/themes add to list of pages/posts/whatever when to load jQuery in the <head>
			//Return true to load jQuery from the <head>
			if ( apply_filters('nebula_prevent_jquery_footer', false) ){
				return;
			}

			if ( !$this->is_admin_page(true) && $this->get_option('jquery_version') === 'footer' ){
				wp_script_add_data('jquery', 'group', 1);
				wp_script_add_data('jquery-core', 'group', 1);
				wp_script_add_data('jquery-migrate', 'group', 1);

			}

			return;
		}

		//Listen for "jQuery is not defined" errors to provide help
		public function listen_for_jquery_footer_errors(){
			//Let other plugins/themes add to list of pages/posts/whatever when to load jQuery in the <head>
			//Return true to load jQuery from the <head>
			if ( apply_filters('nebula_prevent_jquery_footer', false) ){
				return;
			}

			if ( !$this->is_admin_page(true) && $this->get_option('jquery_version') === 'footer' ){
				if ( $this->is_dev() ){
					?>
					<script>
						window.addEventListener('error', function(e){
							var errorMessages = ['jQuery is not defined', "Can't find variable: jQuery"];
							errorMessages.forEach(function(element, index){
								if ( e.message.indexOf(element) !== -1 || e.message.indexOf(element.replace('jQuery', '$')) !== -1 ){
									console.error('[Nebula] jquery.min.js has been moved to the footer so it may not be available at this time. Try moving it back to the head in Nebula Options or move this script tag to the footer.');
								}
							});
						});
					</script>
					<?php
				}
			}
		}

		//Remove jQuery Migrate, but keep jQuery
		public function remove_jquery_migrate($scripts){
			if ( $this->get_option('jquery_version') !== 'wordpress' ){
				$scripts->remove('jquery');
				$scripts->add('jquery', false, array('jquery-core'), null);
			}
		}

		//Embed critical CSS styles into the document <head> to improve perceived load time
		public function embed_critical_styles(){
			if ( $this->get_option('critical_css') ){
				$critical_css_files = apply_filters('nebula_critical_css', array(
					get_template_directory() . '/assets/css/critical.css',
					get_stylesheet_directory() . '/assets/css/critical.css',
				));

				if ( !empty($critical_css_files) ){
					echo '<style class="critical">';
						foreach ( $critical_css_files as $critical_css_file ){
							if ( file_exists($critical_css_file) ){
								include_once $critical_css_file;
								echo PHP_EOL;
							}
						}
					echo '</style>';
				}
			}
		}

		//Force settings within plugins
		public function plugin_force_settings(){
			$override = apply_filters('pre_nebula_plugin_force_settings', null);
			if ( isset($override) ){return;}

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
				if ( class_exists('Yoast_Notification_Center') ){
					remove_action('admin_notices', array(Yoast_Notification_Center::get(), 'display_notifications'));
					remove_action('all_admin_notices', array(Yoast_Notification_Center::get(), 'display_notifications'));
				}
			}
		}

		//Override existing functions (typcially from plugins)
		public function remove_actions(){ //Note: Priorities much MATCH (not exceed) [default if undeclared, it is 10]
			if ( $this->is_admin_page() ){ //WP Admin
				if ( is_plugin_active('event-espresso/espresso.php') ){
					remove_filter('admin_footer_text', 'espresso_admin_performance'); //Event Espresso - Prevent adding text to WP Admin footer
					remove_filter('admin_footer_text', 'espresso_admin_footer'); //Event Espresso - Prevent adding text to WP Admin footer
				}
			}
		}

		//Disable Emojis
		public function disable_wp_emojicons(){
			$override = apply_filters('pre_disable_wp_emojicons', null);
			if ( isset($override) ){return;}

			remove_action('admin_print_styles', 'print_emoji_styles');
			remove_action('wp_head', 'print_emoji_detection_script', 7);
			remove_action('admin_print_scripts', 'print_emoji_detection_script');
			remove_action('wp_print_styles', 'print_emoji_styles');
			remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
			remove_filter('the_content_feed', 'wp_staticize_emoji');
			remove_filter('comment_text_rss', 'wp_staticize_emoji');
		}
		public function remove_emoji_prefetch($hints, $relation_type){
			if ( $relation_type === 'dns-prefetch' ){
				$matches = preg_grep('/emoji/', $hints);
				return array_diff($hints, $matches);
			}

			return $hints;
		}
		public function disable_emojicons_tinymce($plugins){
			if ( is_array($plugins) ){
				return array_diff($plugins, array('wpemoji'));
			}

			return array();
		}

		//Lazy-load anything
		//This markup can be, and is used hard-coded in other places.
		//@todo "Nebula" 0: This is handled by Chrome 75+ natively. Will eventually deprecate this functionality.
		public function lazy_load($html=''){
			//Ignore lazy loading wrappers on AJAX requests
			if ( $this->is_ajax_or_rest_request() ){
				echo $html;
				return false;
			}

			?>
			<samp class="nebula-lazy-position" style="display: block;"></samp>
			<noscript class="nebula-lazy">
				<?php echo $html; ?>
			</noscript>
			<?php
		}

		//Lazy-load images
		//@todo "Nebula" 0: This is handled by Chrome 75+ natively. Will eventually deprecate this functionality.
		public function lazy_img($src=false, $attributes=''){
			$this->lazy_load('<img src="' . $src . '" ' . $attributes . ' importance="low" />');
		}

		//Lazy-load iframes
		//@todo "Nebula" 0: This is handled by Chrome 75+ natively. Will eventually deprecate this functionality.
		public function lazy_iframe($src=false, $attributes=''){
			$this->lazy_load('<iframe src="' . $src . '" ' . $attributes . ' importance="low"></iframe>');
		}
	}
}