<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Optimization') ){
	trait Optimization {
		public $deregistered_assets;

		public function hooks(){
			$this->deregistered_assets = array('styles' => array(), 'scripts' => array());

			if ( !$this->is_background_request() && !is_customize_preview() ){
				add_action('send_headers', array($this, 'nebula_early_hints_ob_start'));
				add_action('wp_print_scripts', array($this, 'styles_early_hints_header'), 9998); //Run this last to get all enqueued scripts
				add_action('wp_print_scripts', array($this, 'scripts_early_hints_header'), 9999); //Run this last to get all enqueued scripts
			}

			//Optimizations for the front-end (Not WordPress Admin or BG requests)
			if ( !$this->is_background_request() ){
				if ( !$this->is_admin_page(true) ){
					add_filter('wp_enqueue_scripts', array($this, 'defer_async_additional_scripts')); //@todo "Nebula" 0: This may no longer be needed as of WP 6.3 for async and defer script attributes
					add_action('wp_enqueue_scripts', array($this, 'dequeue_lazy_load_styles'));
					add_action('wp_footer', array($this, 'dequeue_lazy_load_scripts'));
					add_filter('render_block_core/image', array($this, 'lazy_core_img_blocks'), 10, 3);

					add_action('wp_enqueue_scripts', array($this, 'deregister_jquery_migrate'));
					add_filter('wp_default_scripts', array($this, 'remove_jquery_migrate'));

					add_action('wp_enqueue_scripts', array($this, 'move_jquery_to_footer'));
					add_action('wp_head', array($this, 'listen_for_jquery_footer_errors'));
				}

				add_filter('script_loader_tag', array($this, 'modify_script_attributes'), 10, 3);
			}

			add_action('wp_head', array($this, 'prebrowsing'));

			//Dequeue assets depending on when they are hooked for output
			if ( !$this->is_background_request() ){
				add_action('wp_enqueue_scripts', array($this, 'scan_assets'), 9000);
				add_action('wp_enqueue_scripts', array($this, 'dequeues'), 9001); //Dequeue styles and scripts that are hooked into the wp_enqueue_scripts action
				add_action('wp_print_styles', array($this, 'dequeues'), 9001); //Dequeue styles that are hooked into the wp_print_styles action
				add_action('wp_print_scripts', array($this, 'dequeues'), 9001); //Dequeue scripts that are hooked into the wp_print_scripts action
				add_action('wp_enqueue_scripts', array($this, 'remove_actions'), 9001);
			}

			add_action('send_headers', array($this, 'service_worker_scope'));
			add_action('admin_init', array($this, 'plugin_force_settings'));

			add_action('init', array($this, 'disable_wp_emojicons'));
			add_filter('wp_resource_hints', array($this, 'remove_emoji_prefetch'), 10, 2); //Remove dns-prefetch for emojis
			add_filter('tiny_mce_plugins', array($this, 'disable_emojicons_tinymce')); //Remove TinyMCE Emojis too
			add_filter('wpcf7_load_css', '__return_false'); //Disable CF7 CSS resources (in favor of Bootstrap and Nebula overrides)

			add_action('wp_head', array($this, 'embed_critical_styles'));

			if ( !is_customize_preview() ){
				add_action('send_headers', array($this, 'server_timing_header'));
				add_action('wp_footer', array($this, 'output_console_debug_timings'));
				add_action('admin_footer', array($this, 'output_console_debug_timings'));
			}

			add_filter('jpeg_quality', array($this, 'jpeg_quality'));
			add_filter('intermediate_image_sizes_advanced', array($this, 'create_max_width_size_proportionally'), 10, 2);
			add_filter('post_thumbnail_size', array($this, 'limit_thumbnail_size'), 10, 2);
			add_filter('nebula_thumbnail_src_size', array($this, 'limit_image_size'), 10, 2);
			add_filter('max_srcset_image_width', array($this, 'smaller_max_srcset_image_width'), 10, 2); //Limit width of content images

			//Stop the QM timer as late as possible to be included in the list
			add_filter('wp_footer', array($this, 'stop_qm_timer'), 1);
			add_filter('login_footer', array($this, 'stop_qm_timer'), 1);
			add_filter('admin_footer', array($this, 'stop_qm_timer'), 1);
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
			if ( isset($this->super->server['HTTP_SAVE_DATA']) && stristr($this->super->server['HTTP_SAVE_DATA'], 'on') !== false ){
				return true;
			}

			return false;
		}

		//Allow scripts to be registered with additional attributes
		public function register_script($handle=null, $src=null, $attributes=array(), $deps=array(), $ver=false, $in_footer=false){
			wp_register_script($handle, $src, $deps, $ver, $in_footer); //@todo "Nebula" 0: Update this to use the new syntax as of WP 6.3

			if ( !empty($attributes) ){
				$attributes = ( is_array($attributes) )? $attributes : array($attributes); //Make sure it is an array
				foreach ( $attributes as $attribute ){
					$is_data_added = wp_script_add_data($handle, $attribute, true);
				}
			}
		}

		//Defer and Async specific scripts. This only works with registered/enqueued scripts!
		//@todo "Nebula" 0: This may no longer be needed as of WP 6.3 for async and defer script attributes
		public function defer_async_additional_scripts(){
			if ( !$this->is_admin_page(true) ){
				$to_defer = apply_filters('nebula_defer_scripts', array('jquery-migrate', 'jquery.form', 'contact-form-7', 'wp-embed')); //Allow other functions to hook in to add defer to existing scripts
				$to_async = apply_filters('nebula_async_scripts', array()); //Allow other functions to hook in to add async to existing scripts

				//Defer scripts
				if ( !empty($to_defer) && is_array($to_defer) ){
					foreach ( $to_defer as $handle ){
						wp_script_add_data($handle, 'defer', true);
					}
				}

				//Async scripts
				if ( !empty($to_async) && is_array($to_async) ){
					foreach ( $to_async as $handle ){
						wp_script_add_data($handle, 'async', true);
					}
				}
			}
		}

		//Add/modify defer, async, module, and/or crossorigin attributes to scripts
		public function modify_script_attributes($tag, $handle, $src){
			$crossorigin_exececution = wp_scripts()->get_data($handle, 'crossorigin');
			$defer_exececution = wp_scripts()->get_data($handle, 'defer'); //@todo "Nebula" 0: This may no longer be needed as of WP 6.3 for async and defer script attributes
			$async_exececution = wp_scripts()->get_data($handle, 'async'); //@todo "Nebula" 0: This may no longer be needed as of WP 6.3 for async and defer script attributes
			$module_execution = wp_scripts()->get_data($handle, 'module');

			//Add module type attribute if it is requested
			if ( !empty($module_execution) && strpos($tag, "type='module'") === false ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
				if ( strpos($tag, 'type=') ){ //If the type attribute already exists  //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
					$tag = preg_replace('/type=["\']text\/javascript["\']/', 'type=\'module\'', $tag); //Change the type='text/javascript' attribute to type='module' (the preg_replace regex pattern is used to be agnostic to what type of quotation mark WP core uses)
				} else {
					$tag = str_replace('script src', 'script type="module" src', $tag);
				}
			}

			//Add crossorigin attribute if it is requested and does not already exist
			if ( !empty($crossorigin_exececution) && strpos($tag, 'crossorigin=') === false ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
				$tag = str_replace(' src', ' crossorigin="anonymous" src', $tag); //Add the crossorigin attribute
			}

			//Ignore if neither defer nor async attribute is found
			//@todo "Nebula" 0: This may no longer be needed as of WP 6.3 for async and defer script attributes
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
			//@todo "Nebula" 0: This may no longer be needed as of WP 6.3 for async and defer script attributes
			$additional_handles_to_defer = apply_filters('nebula_defer_handles', array()); //Allow other plugins/themes to simply add defer attributes to scripts
			if ( (!empty($defer_exececution) || in_array($handle, $additional_handles_to_defer)) && strpos($tag, 'defer') === false ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
				$tag = str_replace(' src', ' defer src', $tag); //Add the defer attribute
			}

			//Add async attribute if it is requested and does not already exist
			//@todo "Nebula" 0: This may no longer be needed as of WP 6.3 for async and defer script attributes
			$additional_handles_to_async = apply_filters('nebula_async_handles', array('google-recaptcha')); //Allow other plugins/themes to simply add async attributes to scripts
			if ( (!empty($async_exececution) || in_array($handle, $additional_handles_to_async)) && strpos($tag, 'async') === false ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
				$tag = str_replace(' src', ' async src', $tag); //Add the async attribute
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
			if ( !empty($lazy_load_assets['styles']) && is_array($lazy_load_assets['styles']) ){
				foreach ( $lazy_load_assets['styles'] as $handle => $condition ){
					wp_dequeue_style($handle);
				}
			}
		}

		//Dequeue scripts prepped for lazy-loading
		public function dequeue_lazy_load_scripts(){
			if ( $this->is_admin_page() ){ //Do not modify scripts on admin pages (Gutenberg compatibility)
				return false;
			}

			$lazy_load_assets = $this->lazy_load_assets();
			if ( !empty($lazy_load_assets['scripts']) && is_array($lazy_load_assets['scripts']) ){
				foreach ( $lazy_load_assets['scripts'] as $handle => $condition ){
					wp_dequeue_script($handle);
				}
			}
		}

		//Allow the service worker to control everything without needing to move it out of the theme
		public function service_worker_scope(){
			if ( $this->get_option('service_worker') ){
				header('Service-Worker-Allowed: /');
			}
		}

		//Start output buffering so headers can be sent later for Early Hints
		public function nebula_early_hints_ob_start(){
			$this->timer('Headers Sent [Mark]', 'mark'); //Piggybacking on this action to mark this point in time

			if ( !$this->is_admin_page(true, true) && $this->get_option('service_worker') ){ //Exclude admin, login, and Customizer pages
				$this->timer('Headers Output Buffering');
				ob_start('nebula_ob_flushed');
			}
		}

		//Use Early Hints to push multiple CSS and JS resources at once
		//This uses a link preload header, so these resources must be used within a few seconds of window load.
		public function styles_early_hints_header(){
			if ( !$this->is_admin_page(true, true) && $this->get_option('service_worker') ){ //Exclude admin, login, and Customizer pages
				$timer_name = $this->timer('Early Hints Header (Styles)', 'start');
				global $wp_styles;

				foreach ( $wp_styles->queue as $handle ){
					if ( wp_style_is($handle, 'registered') && !empty($wp_styles->registered[$handle]->src) ){ //If this style is still registered (and src exists)
						$ver = ( !empty($wp_styles->registered[$handle]->ver) )? '?ver=' . $wp_styles->registered[$handle]->ver : '';
						$this->early_hints_file($wp_styles->registered[$handle]->src . $ver, 'style');
					}
				}

				$timer_name = $this->timer($timer_name, 'end');
			}
		}

		public function scripts_early_hints_header(){
			if ( !$this->is_admin_page(true, true) && $this->get_option('service_worker') ){ //Exclude admin, login, and Customizer pages
				$timer_name = $this->timer('Early Hints Header (Scripts)', 'start');
				global $wp_scripts;

				foreach ( $wp_scripts->queue as $handle ){
					if ( wp_script_is($handle, 'registered') && !empty($wp_scripts->registered[$handle]->src) ){ //If this script is still registered (and src exists)
						$ver = ( !empty($wp_scripts->registered[$handle]->ver) )? '?ver=' . $wp_scripts->registered[$handle]->ver : '';
						$this->early_hints_file($wp_scripts->registered[$handle]->src . $ver, 'script');
					}
				}

				$timer_name = $this->timer($timer_name, 'end');

				ob_end_flush(); //Clear the output buffering
			}
		}

		public function early_hints_file($src, $filetype){
			if ( !$this->is_admin_page(true, true) ){ //Exclude admin, login, and Customizer pages
				$crossorigin = ( strpos($src, get_site_url()) === false || $filetype === 'font' )? ' crossorigin=anonymous' : ''; //Add crossorigin attribute for remote assets and all fonts //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
				header('Link: <' . esc_url(str_replace($this->url_components('basedomain'), '', strtok($src, '#'))) . '>; rel=preload; as=' . $filetype . '; nopush' . $crossorigin, false); //Send the header for the Early Hint (strtok to remove everything after and including "#")
			}
		}

		//Set Server Timing header
		public function server_timing_header(){
			if ( $this->is_dev() || isset($this->super->get['timings']) ){ //Only output server timings for developers, or if timings query string is present
				$this->finalize_timings();
				$server_timing_header_string = 'Server-Timing: ';

				//Loop through all times
				if ( !empty($this->server_timings) && is_array($this->server_timings) ){
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
				}

				header($server_timing_header_string);
			}
		}

		//Include server timings for developers
		public function output_console_debug_timings(){
			if ( $this->is_dev() || isset($this->super->get['timings']) ){ //Only output server timings for developers or if timings query string present
				$this->finalize_timings();

				if ( !empty($this->server_timings) && is_array($this->server_timings) ){
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

						$start_time = ( !empty($data['start']) )? round(($data['start']-$this->super->server['REQUEST_TIME_FLOAT'])*1000) : -1;

						$testTimes['[PHP] ' . $label] = array(
							'start' => $start_time, //Convert seconds to milliseconds
							'duration' => round($time*1000) //Convert seconds to milliseconds
						);
					}
				}

				if ( !empty($testTimes) ){
					//Sort by start time
					uasort($testTimes, function($a, $b){
						return $a['start'] - $b['start'];
					});

					echo '<script type="text/javascript">nebula.site.timings = ' . wp_json_encode($testTimes) . ';</script>'; //Output the data to <head>
				}
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
				if ( strpos($this->get_option('remote_font_url'), 'google') || strpos($this->get_option('remote_font_url'), 'gstatic') ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
					$default_preconnects[] = '//fonts.gstatic.com';
				} elseif ( strpos($this->get_option('remote_font_url'), 'typekit') ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
					$default_preconnects[] = '//use.typekit.net';
				}
			}

			//Loop through all of the preconnects
			$preconnects = apply_filters('nebula_preconnect', $default_preconnects);
			if ( !empty($preconnects) && is_array($preconnects) ){
				foreach ( $preconnects as $preconnect ){
					echo '<link rel="preconnect" href="' . $preconnect . '" crossorigin="anonymous" />' . $debug_comment;
				}
			}

			/*==========================
			 Prefetch
			 Fully request a single resource and store it in cache until needed. Do not combine with preload!
			 ===========================*/

			$default_prefetches = array();

			//Subpages
			// if ( !is_front_page() ){
			// 	//$default_prefetches[] = home_url('/'); //Prefetch the home page on subpages. Disabled because this may be loading more than we want on initial pageload
			// }

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
				if ( !empty($this->error_query) && $this->error_query->have_posts() ){
					$default_prefetches[] = get_permalink($this->error_query->posts[0]->ID);
				}
			}

			//Loop through all of the prefetches
			$prefetches = apply_filters('nebula_prefetches', $default_prefetches); //Allow child themes and plugins to prefetch resources via Nebula too
			if ( !empty($prefetches) && is_array($prefetches) ){
				foreach ( $prefetches as $prefetch ){
					echo '<link rel="prefetch" href="' . $prefetch . '" crossorigin="anonymous" />' . $debug_comment;
				}
			}

			/*==========================
			 Preload
			 Fully request a single resource before it is needed. Do not combine with prefetch! These must be used within a few seconds of window load.
			 ===========================*/

			$default_preloads = array();

			//Google fonts if used
/*
			if ( $this->get_option('remote_font_url') ){
				$default_preloads[] = $this->get_option('remote_font_url'); //Oct 2019: disabling this for now as it delays render in Chrome
			}
*/

			//Loop through all of the preloads
			$preloads = apply_filters('nebula_preloads', $default_preloads); //Allow child themes and plugins to preload resources via Nebula too
			if ( !empty($preloads) && is_array($preloads) ){
				foreach ( $preloads as $preload ){
					switch ( $preload ){
						case strpos($preload, '.css'): //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
							$filetype = 'style';
							break;
						case strpos($preload, '.js'): //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
							$filetype = 'script';
							break;
						case strpos($preload, 'fonts.googleapis'): //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
						case strpos($preload, '.woff'): //Captures both .woff and .woff2 //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
							$filetype = 'font';
							break;
						case strpos($preload, '.jpg'): //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
						case strpos($preload, '.jpeg'): //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
						case strpos($preload, '.png'): //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
						case strpos($preload, '.gif'): //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
							$filetype = 'image';
							break;
						case strpos($preload, '.mp4'): //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
						case strpos($preload, '.ogv'): //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
						case strpos($preload, '.mov'): //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
							$filetype = 'video';
							break;
						default:
							$filetype = 'fetch';
							break;
					}

					echo '<link rel="preload" href="' . $preload . '" as="' . $filetype . '" crossorigin="anonymous" />';
				}
			}
		}

		//Scan the front-end styles and scripts to be able to deregister them from Nebula Options
		public function scan_assets(){
			if ( !is_admin() && current_user_can('manage_options') && (isset($this->super->get['nebula-scan']) || isset($this->super->get['sass']) || isset($this->super->get['debug']) || $this->get_option('audit_mode')) ){ //Only run on front-end for admin users. Also add a query string so this doesn't run every single pageload
				$this->timer('Scan Assets');

				if ( isset($this->super->get['nebula-scan']) && $this->super->get['nebula-scan'] === 'reset' ){ //Use this to reset and re-scan from scratch
					update_option('optimizable_registered_styles', array());
					update_option('optimizable_registered_scripts', array());
				}

				//Styles
				$already_known_styles = get_option('optimizable_registered_styles'); //This preserves the list between pages

				$all_registered_styles = array();
				global $wp_styles;
				foreach ( $wp_styles->registered as $style ){
					if ( strpos($style->src, 'wp-content') ){ //Limit the options to non-core styles //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
						$all_registered_styles[] = array(
							'handle' => $style->handle,
							'src' => $style->src
						);
					}
				}

				if ( is_array($already_known_styles) ){
					$all_registered_styles = array_merge($already_known_styles, $all_registered_styles); //De-dupe and combine arrays
				}

				$all_registered_styles = array_intersect_key($all_registered_styles, array_unique(array_map('serialize', $all_registered_styles))); //De-dupe the array
				update_option('optimizable_registered_styles', $all_registered_styles);

				//Scripts
				$already_known_scripts = get_option('optimizable_registered_scripts'); //This preserves the list between pages

				$all_registered_scripts = array();
				global $wp_scripts;
				foreach ( $wp_scripts->registered as $script ){
					if ( strpos($script->src, 'wp-content') ){ //Limit the options to non-core scripts //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
						$all_registered_scripts[] = array(
							'handle' => $script->handle,
							'src' => $script->src
						);
					}
				}

				if ( is_array($already_known_scripts) ){
					$all_registered_scripts = array_merge($already_known_scripts, $all_registered_scripts); //De-dupe and combine arrays
				}

				$all_registered_scripts = array_intersect_key($all_registered_scripts, array_unique(array_map('serialize', $all_registered_scripts))); //De-dupe the array
				update_option('optimizable_registered_scripts', $all_registered_scripts);

				$this->timer('Scan Assets', 'end');
			}
		}

		//Dequeue certain scripts
		public function dequeues(){
			$override = apply_filters('pre_nebula_dequeues', null);
			if ( isset($override) ){return;}

			if ( !is_admin() ){
				$current_action = current_action(); //Get the current WordPress action handle that was called (so we know which one we are "inside")
				$timer_name = $this->timer('Advanced Dequeues (' . $current_action . ')');

				$this->deregister('contact-form-7', 'style'); //Removing CF7 styles in favor of Bootstrap + Nebula
				$this->deregister('wp-embed', 'script'); //WP Core WP-Embed - Override this only if embedding external WordPress posts into this WordPress site. Other oEmbeds are NOT AFFECTED by this!
				$this->deregister('classic-theme-styles', 'style'); //WP 6.1 added "classic-themes.css" file. Get rid of it.

				//Page specific dequeues
				if ( is_front_page() ){
					$this->deregister('thickbox', 'style'); //WP Core Thickbox - Override this if thickbox type gallery IS used on the homepage.
					$this->deregister('thickbox', 'script'); //WP Thickbox - Override this if thickbox type gallery IS used on the homepage.
				}

				if ( !empty($current_action) ){
					//Ignore script dependencies on style-based hooks (enqueue_scripts and print_scripts)
					// if ( strpos($current_action, 'scripts') !== false ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
					//	//Disabled because some functionality still needs wp-polyfill even in modern browsers. Ugh. Ex: https://nebula.gearside.com/functions/infinite_load_query/
					// 	//Remove "wp-polyfill" but first need to remove that dependency from other scripts. In the future, this may no longer be needed... hopefully. Watch this issue: https://github.com/WordPress/gutenberg/issues/21616
					// 	$scripts = wp_scripts(); //Get all of the script dependencies
					// 	foreach ( $scripts->registered as $registered_script ){ //Loop through all registered scripts
					// 		foreach ( $registered_script->deps as $dep_key => $handle ){ //Loop through each of the dependencies
					// 			if ( $handle === 'wp-polyfill' ){ //If this dependency is "wp-polyfill"
					// 				unset($registered_script->deps[$dep_key]); //Remove this dependency
					// 			}
					// 		}
					// 	}
					// 	$this->deregister('wp-polyfill', 'script', false); //Now we can deregister "wp-polyfill" without breaking other assets
					// }

					//Dequeue styles based on selected Nebula options
					if ( $current_action !== 'wp_print_scripts' ){ //Check the last hook to run and skip dequeuing styles on the print scripts hook
						$styles_to_dequeue = $this->get_option('dequeue_styles');
						if ( !empty($styles_to_dequeue) ){
							$this->check_dequeue_rules($styles_to_dequeue, 'styles');
						}
					}

					//Dequeue scripts based on selected Nebula options
					if ( $current_action !== 'wp_print_styles' ){ //Check the last hook to run and skip dequeuing scripts on the print styles hook
						$scripts_to_dequeue = $this->get_option('dequeue_scripts');
						if ( !empty($scripts_to_dequeue) ){
							$this->check_dequeue_rules($scripts_to_dequeue, 'scripts');
						}
					}
				}

				$this->timer($timer_name, 'end');
			}
		}

		//Loop through the dequeue rules and deregister assets when matching
		public function check_dequeue_rules($assets = array(), $type=''){
			if ( !empty($type) ){
				foreach ( array_filter($assets) as $handle => $rules ){
					$rules = str_replace(' ', '', $rules); //Sanitize the text input

					if ( !empty($rules) ){
						//If dequeueing everywhere on front-end
						if ( $rules === '*' ){
							$this->deregister($handle, $type);
							continue; //No need to check anything further since this handle is dequeued on all pages. Go to the next handle.
						}

						//Loop through each of the rules for this handle
						foreach ( array_filter(explode(',', $rules)) as $rule ){
							//If an ID is used check it
							if ( intval($rule) && get_the_id() === intval($rule) ){
								$this->deregister($handle, $type);
								break; //No need to check at additional rules for this handle. Go to the next handle.
							}

							//Check if rule is an inverted function. Ex: "!is_front_page"
							$invert = false;
							if ( strpos($rule, '!') === 0 ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
								$invert = true;
								$rule = ltrim($rule, '!'); //Remove the "!" character since we have now detected it

								//Now check if the rule is simply an "inverted" ID
								if ( intval($rule) && get_the_id() !== intval($rule) ){
									$this->deregister($handle, $type);
									break; //No need to check at additional rules for this handle. Go to the next handle.
								}
							}

							$rule = str_replace('()', '', $rule); //If called as an executable function, remove the "()". Ex: is_front_page()

							//If the rule is a function name. Ex: "is_front_page"
							if ( function_exists($rule) ){
								$conditional_function = ( !empty($invert) )? !call_user_func($rule) : call_user_func($rule); //Check for not empty here because it is empty be default (above)

								if ( $conditional_function ){
									$this->deregister($handle, $type);
									break; //No need to check at additional rules for this handle. Go to the next handle.
								}
							}
						}
					}
				}
			}
		}

		//Deregister assets. Using this function will note it in the Nebula Admin Bar to make it easier to troubleshoot
		public function deregister($handle, $type, $indicate=true){
			if ( !empty($handle) ){
				//Styles
				if ( strpos(strtolower($type), 'style') !== false || strpos(strtolower($type), 'css') !== false ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
					//Check if this style was enqueued
					if ( $indicate && wp_style_is($handle, 'enqueued') ){
						$this->deregistered_assets['styles'][] = $handle; //Add it to the array to log in the admin bar
					}

					//Deregister the style either way
					wp_dequeue_style($handle);
					wp_deregister_style($handle);
					return true;
				}

				//Scripts
				//Check if this script was enqueued (and show note if so)
				if ( $indicate && wp_script_is($handle, 'enqueued') ){
					$this->deregistered_assets['scripts'][] = $handle;
				}

				//Deregister the script either way
				wp_dequeue_script($handle);
				wp_deregister_script($handle);
				return true;
			}
		}

		//Deregister jQuery Migrate
		//Eventually this should be able to be removed, right? Not able to in April 2021 (WP Core v5.7) - If removing this and jQuery is loaded from the <head> because of jQuery Migrate, then this is still needed.
		public function deregister_jquery_migrate(){
			if ( !$this->is_admin_page(true) && !is_admin_bar_showing() && $this->get_option('jquery_location') !== 'wordpress' ){
				wp_deregister_script('jquery'); //Deregister jQuery Migrate
			}
		}

		//Remove jQuery Migrate, and re-add jQuery
		//Eventually this should be able to be removed, right? Not able to in April 2021 (WP Core v5.7) - If removing this and jQuery is loaded from the <head> because of jQuery Migrate, then this is still needed.
		public function remove_jquery_migrate($scripts){
			if ( !$this->is_admin_page(true) && !is_admin_bar_showing() && $this->get_option('jquery_location') !== 'wordpress' ){
				$scripts->remove('jquery');
				$scripts->add('jquery', false, array('jquery-core'), null);
			}
		}

		//If Nebula Options are set to load jQuery in the footer, move it there.
		//Note: If any other registered script that is enqueued in the <head> has jQuery as a dependent, jQuery will be loaded in the head automatically
		public function move_jquery_to_footer(){
			//Let other plugins/themes add to list of pages/posts/whatever when to load jQuery in the <head>
			//Return true to load jQuery from the <head>
			if ( apply_filters('nebula_prevent_jquery_footer', false) ){
				return;
			}

			if ( !$this->is_admin_page(true) && !is_admin_bar_showing() && $this->get_option('jquery_location') === 'footer' ){
				//Group 1 is how WordPress designates scripts to load from the footer
				wp_script_add_data('jquery', 'group', 1);
				wp_script_add_data('jquery-core', 'group', 1);
			}
		}

		//Listen for "jQuery is not defined" errors to provide help
		public function listen_for_jquery_footer_errors(){
			//If jQuery was moved back to the head, do not listen for these errors
			if ( apply_filters('nebula_prevent_jquery_footer', false) ){
				return;
			}

			if ( !$this->is_admin_page(true) && $this->get_option('jquery_location') === 'footer' ){
				if ( $this->is_dev() ){
					?>
					<script>
						window.addEventListener('error', function(e){
							var errorMessages = ['jQuery is not defined', 'Cannot find variable: jQuery'];
							errorMessages.forEach(function(element, index){
								if ( e.message.indexOf(element) !== -1 || e.message.indexOf(element.replace('jQuery', '$')) !== -1 ){
									console.error('[Nebula] jquery.min.js has been moved to the footer so it may not be available at this time. Try moving it back to the head in Nebula Options or move this script tag to the footer.');
								}
							});
						}, {passive: true});
					</script>
					<?php
				}
			}
		}

		//Embed critical CSS styles into the document <head> to improve perceived load time
		public function embed_critical_styles(){
			if ( $this->get_option('critical_css') ){
				$this->timer('Embedding Critical CSS');

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

				$this->timer('Embedding Critical CSS', 'end');
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
				$wpseo['theme_description_found'] = false; //Not working because this keeps getting checked/tested at many various times in the plugin.
				$wpseo['theme_has_description'] = false; //Not working because this keeps getting checked/tested at many various times in the plugin.
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

		//End the overall timer for Nebula in Query Monitor
		public function stop_qm_timer(){
			do_action('qm/stop', 'Non-WP Core (Total)');
		}

		//Lazy-load anything
		//This markup can be, and is used hard-coded in other places. This is handled by Chrome 75+ natively.
		public function lazy_load($html=''){
			//Ignore lazy loading wrappers on AJAX requests
			if ( $this->is_background_request() ){
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
		public function lazy_img($src=false, $attributes=''){
			$this->lazy_load('<img src="' . $src . '" ' . $attributes . ' loading="lazy" />');
		}

		//Lazy-load iframes
		public function lazy_iframe($src=false, $attributes=''){
			$this->lazy_load('<iframe src="' . $src . '" ' . $attributes . ' loading="lazy"></iframe>');
		}

		//Lazy-load WP Core image blocks
		public function lazy_core_img_blocks($block_content, $block){
			if ($block['blockName'] === 'core/image') {
				$block_content = preg_replace('/<img(.*?)>/', '<img$1 loading="lazy">', $block_content);
			}

			return $block_content;
		}
	}

	function nebula_ob_flushed($buffer){
		nebula()->timer('Headers Output Buffering', 'end');
		return $buffer;
	}
}