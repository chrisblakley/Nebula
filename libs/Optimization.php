<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Optimization') ){
	trait Optimization {
		public function hooks(){
			add_filter('clean_url', array($this, 'defer_async_scripts'), 11, 1);
			add_filter('script_loader_tag', array($this, 'defer_async_additional_scripts'), 10);
			add_filter('nebula_lazy_load_assets', array($this, 'lazy_load_assets'), 10, 1);
			add_filter('style_loader_src', array($this, 'http2_server_push_header'), 99, 1);
			add_filter('script_loader_src', array($this, 'http2_server_push_header'), 99, 1);
			add_filter('script_loader_src', array($this, 'remove_script_version'), 15, 1);
			add_filter('style_loader_src', array($this, 'remove_script_version'), 15, 1);
			add_action('wp_print_scripts', array($this, 'dequeues'), 9999);
			add_filter('wp_default_scripts', array($this, 'remove_jquery_migrate'));
			add_action('admin_init', array($this, 'plugin_force_settings'));
			add_action('wp_print_scripts', array($this, 'remove_actions'), 9999);
			add_action('init', array($this, 'disable_wp_emojicons'));
			add_filter('wp_resource_hints', array($this, 'remove_emoji_prefetch'), 10, 2); //Remove dns-prefetch for emojis
			add_filter('tiny_mce_plugins', array($this, 'disable_emojicons_tinymce')); //Remove TinyMCE Emojis too
			add_filter('wpcf7_load_css', '__return_false'); //Disable CF7 CSS resources (in favor of Bootstrap and Nebula overrides)
		}

		public function register_script($handle=null, $src=null, $exec=null, $deps=array(), $ver=false, $in_footer=false){
			$path = ( !empty($exec) )? $src . '#' . $exec : $src;
			wp_register_script($handle, $path, $deps, $ver, $in_footer);
		}

		//Remove version query strings from registered/enqueued styles/scripts (to allow caching). Note when troubleshooting: Other plugins may be doing this too.
		//For debugging (see the "add_debug_query_arg" function in /libs/Scripts.php)
		public function remove_script_version($src){
			if ( $this->is_debug() ){
				return $src;
			}

			$src = rtrim(remove_query_arg('ver', $src), '?'); //Remove "?" if it is the last character after removing ?ver parameter
			$src = str_replace('?#', '#', $src); //Remove "?" if it is followed by "#" (when using #defer or #async with Nebula)

			return $src;
		}

		//Control which scripts use defer/async using a hash.
		public function defer_async_scripts($url){
			if ( strpos($url, '#defer') === false && strpos($url, '#async') === false ){
				return $url;
			}

			if ( strpos($url, '#defer') ){
				return str_replace('#defer', '', $url) . "' defer='defer"; //Add the defer attribute while removing the hash
			} elseif ( strpos($url, '#async') ){
				return str_replace('#async', '', $url) . "' async='async"; //Add the async attribute while removing the hash
			}
		}

		//Defer and Async specific scripts. This only works with registered/enqueued scripts!
		public function defer_async_additional_scripts($tag){
			$to_defer = array('jquery-migrate', 'jquery.form', 'contact-form-7', 'wp-embed'); //Scripts to defer. Strings can be anywhere in the filepath.
			$to_async = array(); //Scripts to async. Strings can be anywhere in the filepath.

			//Defer scripts
			if ( !empty($to_defer) ){
				foreach ( $to_defer as $script ){
					if ( strpos($tag, $script) ){
						return str_replace(' src', ' defer="defer" src', $tag);
					}
				}
			}

			//Async scripts
			if ( !empty($to_async) ){
				foreach ( $to_async as $script ){
					if ( strpos($tag, $script) ){
						return str_replace(' src', ' async="async" src', $tag);
					}
				}
			}

			return $tag;
		}

		//Prep assets for lazy loading. Be careful of dependencies!
		//Array should be built as: handle => condition
		public function lazy_load_assets($assets){
			$assets['styles'] = array(
				'wp-pagenavi' => '.wp-pagenavi',
			);

			$assets['scripts'] = array();

			return $assets;
		}

		//Use HTTP2 Server Push to push multiple CSS and JS resources at once
		public function http2_server_push_header($src){
			if ( !$this->is_admin_page() && $this->get_option('service_worker') && file_exists($this->sw_location(false)) && !is_customize_preview() ){ //If not in the admin section and if Service Worker is enabled (and file exists)
				$filetype = ( strpos($src, '.css') )? 'style' : 'script'; //Determine the resource type
				if ( strpos($src, $this->url_components('sld')) > 0 ){ //If local file
					if ( $this->get_browser() !== 'safari' ){ //Disable HTTP2 Server Push on Safari (at least for now)
						header('Link: <' . esc_url(str_replace($this->url_components('basedomain'), '', strtok($src, '#'))) . '>; rel=preload; as=' . $filetype, false); //Send the header for the HTTP2 Server Push (strtok to remove everything after and including "#")
					}
				}
			}

		    return $src;
		}

		//Determing if a page should be prepped using prefetch, preconnect, or prerender.
			//DNS-Prefetch = Resolve the DNS only to a domain.
			//Preconnect = Resolve both DNS and TCP to a domain.
			//Prefetch = Fully request a single resource and store it in cache until needed. Do not combine with preload!
			//Preload = Fully request a single resource before it is needed. Do not combine with prefetch!
			//Prerender = Render an entire page (useful for comment next page navigation). Use Audience > User Flow report in Google Analytics for better predictions.

			//Note: WordPress automatically uses dns-prefetch on enqueued resource domains.

			//To hook into the arrays use:
			/*
				add_filter('nebula_preconnect', 'my_preconnects');
				function my_preconnects($array){
					$array[] = '//example.com';
					return $array;
				}
			*/
		public function prebrowsing(){
			$override = apply_filters('pre_nebula_prebrowsing', null);
			if ( isset($override) ){return;}

			//DNS-Prefetch & Preconnect
			$default_preconnects = array();

			//Weather
			if ( $this->get_option('weather') ){
				$default_preconnects[] = '//query.yahooapis.com';
			}

			//GCSE on 404 pages
			if ( is_404() && $this->get_option('cse_id') ){
				$default_preconnects[] = '//www.googleapis.com';
			}

			//Disqus commenting
			if ( is_single() && $this->get_option('comments') && $this->get_option('disqus_shortname') ){
				$default_preconnects[] = '//' . $this->get_option('disqus_shortname') . '.disqus.com';
			}

			//Preconnect
			$preconnects = apply_filters('nebula_preconnect', $default_preconnects);
			foreach ( $preconnects as $preconnect ){
				echo '<link rel="preconnect" href="' . $preconnect . '" />';
			}

			//Prefetch
			$default_prefetches = array();
			$prefetches = apply_filters('nebula_prefetches', $default_prefetches);
			foreach ( $prefetches as $prefetch ){
				echo '<link rel="prefetch" href="' . $prefetch . '" />';
			}

			//Prerender
			//If an eligible page is determined after load, use the JavaScript nebulaPrerender(url) function.
			$prerender = false;
			if ( is_404() ){
				$prerender = ( !empty($error_404_exact_match) )? $error_404_exact_match : home_url('/');
			}

			if ( !empty($prerender) ){
				echo '<link id="prerender" rel="prerender" href="' . $prerender . '" />';
			}
		}

		//Dequeue certain scripts
		public function dequeues(){
			$override = apply_filters('pre_nebula_dequeues', null);
			if ( isset($override) ){return;}

			if ( !is_admin() ){
				//Removing CF7 styles in favor of Bootstrap + Nebula
				wp_deregister_style('contact-form-7');
				wp_dequeue_style('contact-form-7');

				//Page specific dequeues
				if ( is_front_page() ){
					wp_deregister_style('thickbox'); //WP Core Thickbox - Override if thickbox type gallery IS used on the homepage.
					wp_deregister_script('thickbox'); //WP Thickbox - Override if thickbox type gallery IS used on the homepage.
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
				remove_action('admin_notices', array(Yoast_Notification_Center::get(), 'display_notifications'));
				remove_action('all_admin_notices', array(Yoast_Notification_Center::get(), 'display_notifications'));
			}
		}

		//Override existing functions (typcially from plugins)
		public function remove_actions(){ //Note: Priorities much MATCH (not exceed) [default if undeclared is 10]
			if ( $this->is_admin_page() ){ //WP Admin
				if ( is_plugin_active('event-espresso/espresso.php') ){
					remove_filter('admin_footer_text', 'espresso_admin_performance'); //Event Espresso - Prevent adding text to WP Admin footer
					remove_filter('admin_footer_text', 'espresso_admin_footer'); //Event Espresso - Prevent adding text to WP Admin footer
				}
			} else { //Frontend
				//remove_action('wpseo_head', 'debug_marker', 2 ); //Remove Yoast comment [not working] (not sure if second comment could be removed without modifying class-frontend.php)

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
			if ( 'dns-prefetch' === $relation_type ) {
				$matches = preg_grep('/emoji/', $hints);
				return array_diff( $hints, $matches );
			}

			return $hints;
		}

		public function disable_emojicons_tinymce($plugins){
			if ( is_array($plugins) ){
				return array_diff($plugins, array('wpemoji'));
			} else {
				return array();
			}
		}

		//Lazy-load images
		public function lazy_load($src, $options){$this->lazy_img($src, $options);}
		public function lazy_img($src=false, $attributes=''){
			?>
			<samp class="nebula-lazy-position"></samp>
			<noscript class="nebula-lazy-img">
				<img src="<?php echo $src; ?>" <?php echo $attributes; ?> />
			</noscript>
			<?php
		}
	}
}