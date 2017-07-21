<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

trait Functions {
	public $twitter_widget_loaded;
	public $google_plus_widget_loaded;
	public $linkedin_widget_loaded;
	public $pinterest_widget_loaded;

	public function hooks(){
		global $pagenow;

		$this->twitter_widget_loaded = false;
		$this->google_plus_widget_loaded = false;
		$this->linkedin_widget_loaded = false;
		$this->pinterest_widget_loaded = false;

		add_action('init', array($this, 'nebula_http2_ob_start'));
		add_action('after_setup_theme', array($this, 'theme_setup'));
		add_filter('site_icon_image_sizes', array($this, 'site_icon_sizes'));
		add_action('wp_head', array($this, 'add_back_post_feed'));
		add_action('init', array($this, 'set_default_timezone'), 1);
		add_action('admin_init', array($this, 'set_default_timezone'), 1);

		if ( $this->get_option('console_css') ){
			add_action('wp_head', array($this, 'calling_card'));
		}

		add_action('wp_head', array($this, 'console_warnings'));
		add_action('wp_head', array($this, 'no_sass_customizer_styles'), 100);

		if ( is_writable(get_template_directory()) ){
			if ( !file_exists($this->manifest_json_location()) || filemtime($this->manifest_json_location()) > (time()-DAY_IN_SECONDS) || $this->is_debug() ){
				add_action('init', array($this, 'manifest_json'));
				add_action('admin_init', array($this, 'manifest_json'));
			}
		}

		if ( $this->get_option('service_worker') && is_writable(get_home_path()) ){
			if ( file_exists($this->sw_location(false)) ){
				add_action('save_post', array($this, 'update_sw_js'));
			}
		}

		add_action('wp_loaded', array($this, 'favicon_cache'));
		add_action('nebula_head_open', array($this, 'google_optimize_style'));
		add_action('widgets_init', array($this, 'widgets_register'));
		add_action('after_setup_theme', array($this, 'nav_menu_locations'));

		if ( !$this->get_option('comments') || $this->get_option('disqus_shortname') ){ //If WP core comments are disabled -or- if Disqus is enabled
			add_action('wp_dashboard_setup', array($this, 'remove_activity_metabox'));
			add_filter('manage_posts_columns', array($this, 'remove_pages_count_columns'));
			add_filter('manage_pages_columns', array($this, 'remove_pages_count_columns'));
			add_filter('manage_media_columns', array($this, 'remove_pages_count_columns'));
			add_filter('comments_open', '__return_false', 20, 2);
			add_filter('pings_open', '__return_false', 20, 2);

			if ( $this->get_option('admin_bar') ){
				add_action('admin_bar_menu', array($this, 'admin_bar_remove_comments' ), 900);
			}

			add_action('admin_menu', array($this, 'disable_comments_admin'));
			add_filter('admin_head', array($this, 'hide_ataglance_comment_counts'));
			add_action('admin_init', array($this, 'remove_comments_post_type_support'));

			if ( $pagenow == 'edit-comments.php' && $this->get_option('disqus_shortname') ){
				add_action('admin_notices', array($this, 'disqus_link'));
			}
		} else { //If WP core comments are enabled
			add_action('comment_form_before', array($this, 'enqueue_comments_reply'));
		}

		add_action('admin_init', array($this, 'disable_trackbacks'));
		add_action('wp_head', array($this, 'comment_author_cookie'));
		add_action('template_include', array($this, 'define_current_template'), 1000);
		add_action('wp_ajax_nebula_twitter_cache', array($this, 'twitter_cache'));
		add_action('wp_ajax_nopriv_nebula_twitter_cache', array($this, 'twitter_cache'));
		add_filter('get_search_form', array($this, 'search_form'), 100);
		add_filter('the_password_form', array($this, 'password_form_simplify'));
		add_filter('the_posts', array($this, 'always_get_post_custom'));
		add_action('pre_get_posts', array($this, 'redirect_empty_search'));
		add_action('template_redirect', array($this, 'redirect_single_post'));

		add_action('wp_ajax_nebula_autocomplete_search', array($this, 'autocomplete_search'));
		add_action('wp_ajax_nopriv_nebula_autocomplete_search', array($this, 'autocomplete_search'));

		add_action('wp_ajax_nebula_advanced_search', array($this, 'advanced_search'));
		add_action('wp_ajax_nopriv_nebula_advanced_search', array($this, 'advanced_search'));

		add_action('wp_ajax_nebula_infinite_load', array($this, 'infinite_load'));
		add_action('wp_ajax_nopriv_nebula_infinite_load', array($this, 'infinite_load'));

		add_action('wp', array($this, 'internal_suggestions'));
		add_filter('body_class', array($this, 'body_classes'));
		add_filter('post_class', array($this, 'post_classes'));
		add_action('nebula_body_open', array($this, 'skip_to_content_link'));
		add_filter('wp_get_attachment_url', array($this, 'wp_get_attachment_url_force_protocol'));
		add_filter('embed_oembed_html', array($this, 'oembed_modifiers'), 9999, 4);
	}

	//Start output buffering so headers can be sent later for HTTP2 Server Push
	public function nebula_http2_ob_start(){
	    if ( !$this->is_admin_page() ){
	    	ob_start();
	    }
	}

	//Prep custom theme support
	public function theme_setup(){
		//Additions
		add_theme_support('post-thumbnails');
		add_theme_support('custom-logo'); //Custom logo support.
		add_theme_support('title-tag'); //Title tag support allows WordPress core to create the <title> tag.
		//add_theme_support('html5', array('comment-list', 'comment-form', 'search-form', 'gallery', 'caption'));
		add_theme_support('automatic-feed-links'); //Add default posts and comments RSS feed links to head

		add_post_type_support('page', 'excerpt'); //Allow pages to have excerpts too

		header("X-UA-Compatible: IE=edge"); //Add IE compatibility header
		header('Developed-with-Nebula: https://gearside.com/nebula'); //Nebula header

		//Add new image sizes
		add_image_size('square', 512, 512, 1);
		add_image_size('open_graph_large', 1200, 630, 1);
		add_image_size('open_graph_small', 600, 315, 1);
		add_image_size('twitter_large', 280, 150, 1);
		add_image_size('twitter_small', 200, 200, 1);

		//Removals
		remove_theme_support('custom-background');
		remove_theme_support('custom-header');

		//Remove capital P core function
		remove_filter('the_title', 'capital_P_dangit', 11);
		remove_filter('the_content', 'capital_P_dangit', 11);
		remove_filter('comment_text', 'capital_P_dangit', 31);

		//Head information
		remove_action('wp_head', 'rsd_link'); //Remove the link to the Really Simple Discovery service endpoint and EditURI link (third-party editing APIs)
		remove_action('wp_head', 'wp_generator'); //Removes the WordPress XHTML Generator meta tag and WP version
		remove_action('wp_head', 'wp_shortlink_wp_head'); //Removes the shortlink tag in the head
		remove_action('wp_head', 'feed_links', 2); //Remove the links to the general feeds: Post and Comment Feed
		remove_action('wp_head', 'wlwmanifest_link'); //Remove the link to the Windows Live Writer manifest file
		remove_action('wp_head', 'feed_links_extra', 3); //Remove the links to the extra feeds such as category feeds
		remove_action('wp_head', 'index_rel_link'); //Remove index link (deprecated?)
		remove_action('wp_head', 'start_post_rel_link', 10, 0); //Remove start link
		remove_action('wp_head', 'parent_post_rel_link', 10, 0); //Remove previous link
		remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0); //Remove relational links for the posts adjacent to the current post
	}

	//Add custom meta icon (favicon) sizes when the site_icon is used via the Customizer
	public function site_icon_sizes($core_sizes){
		$nebula_sizes = array(16, 32, 70, 150, 180, 192, 310);
		$all_sizes = array_unique(array_merge($core_sizes, $nebula_sizes));
		return $sizes;
	}

	//Add the Posts RSS Feed back in
	public function add_back_post_feed(){
		echo '<link rel="alternate" type="application/rss+xml" title="RSS 2.0 Feed" href="' . get_bloginfo('rss2_url') . '" />';
	}

	//Set server timezone to match Wordpress
	public function set_default_timezone(){
		date_default_timezone_set(get_option('timezone_string', 'America/New_York'));
	}

	//Add the Nebula note to the browser console (if enabled)
	public function calling_card(){
		if ( !$this->get_option('device_detection') || ($this->is_desktop() && !$this->is_browser('ie') && !$this->is_browser('edge')) ){
			echo "<script>console.log('%c Created using Nebula " . $this->version('primary') . "', 'padding: 2px 10px; background: #0098d7; color: #fff;');</script>";
		}
	}

	//Check for Nebula warnings
	public function check_warnings(){
		if ( (current_user_can('manage_options') || $this->is_dev()) && $this->get_option('admin_notices') ){
			$nebula_warnings = array();

			//Admin warnings only
			if ( $this->is_admin_page() ){
				//Check page slug against categories and tags. //@TODO "Nebula" 0: Consider adding other taxonomies here too
				global $pagenow;
				if ( $pagenow === 'post.php' || $pagenow === 'edit.php' ){
					global $post;

					if ( !empty($post) ){ //If the listing has results
						foreach ( get_categories() as $category ){
							if ( $category->slug == $post->post_name ){
								$nebula_warnings[] = array(
									'level' => 'error',
									'description' => 'Page and category slug conflict: <strong>' . $category->slug . '</strong> - Consider changing this page slug.',
								);
								return false;
							}
						}

						foreach ( get_tags() as $tag ){
							if ( $tag->slug == $post->post_name ){
								$nebula_warnings[] = array(
									'level' => 'error',
									'description' => 'Page and tag slug conflict: <strong>' . $tag->slug . '</strong> - Consider changing this page slug.'
								);
								return false;
							}
						}
					}
				}
			}

			//If search indexing is disabled
			if ( get_option('blog_public') == 0 ){
				$nebula_warnings[] = array(
					'level' => 'error',
					'description' => '<a href="options-reading.php">Search Engine Visibility</a> is currently disabled!',
					'url' => 'options-reading.php'
				);
			}

			//If website is live and using Prototype Mode
			if ( $this->is_site_live() && $this->get_option('prototype_mode') ){
				$nebula_warnings[] = array(
					'level' => 'warn',
					'description' => '<a href="plugins.php">Prototype Mode</a> is enabled (' . ucwords($this->dev_phase()) . ')!'
				);
			}

			//If Prototype mode is disabled, but Multiple Theme plugin is still activated
			if ( !$this->get_option('prototype_mode') && is_plugin_active('jonradio-multiple-themes/jonradio-multiple-themes.php') ){
				$nebula_warnings[] = array(
					'level' => 'error',
					'description' => '<a href="plugins.php">Prototype Mode</a> is disabled, but <a href="plugins.php">Multiple Theme plugin</a> is still active.',
					'url' => 'plugins.php'
				);
			}

			//Check PHP version
			$php_version_lifecycle = $this->php_version_support();
			if ( $php_version_lifecycle['lifecycle'] === 'security' ){
				if ( $php_version_lifecycle['end']-time() < 2592000 ){ //1 month
					$nebula_warnings[] = array(
						'level' => 'warn',
						'description' => 'PHP <strong>' . PHP_VERSION . '</strong> <a href="http://php.net/supported-versions.php" target="_blank" rel="noopener">is nearing end of life</a>. Security updates end on ' . date('F j, Y', $php_version_lifecycle['end']) . ' <small>(in ' . human_time_diff($php_version_lifecycle['end']) . ')</small>'
					);
				}
			} elseif ( $php_version_lifecycle['lifecycle'] === 'end' ){
				$nebula_warnings[] = array(
					'level' => 'error',
					'description' => 'PHP ' . PHP_VERSION . ' <a href="http://php.net/supported-versions.php" target="_blank" rel="noopener">no longer receives security updates</a>! End of life occurred on ' . date('F j, Y', $php_version_lifecycle['end']) . ' <small>(' . human_time_diff($php_version_lifecycle['end']) . ' ago)</small>'
				);
			}

			//Check for hard Debug Mode
			if ( WP_DEBUG ){
				$nebula_warnings[] = array(
					'level' => 'warn',
					'description' => '<strong>WP_DEBUG</strong> is enabled <small>(Generally defined in wp-config.php)</small>'
				);
			}
			if ( WP_DEBUG_LOG ){
				$nebula_warnings[] = array(
					'level' => 'warn',
					'description' => 'Debug logging (<strong>WP_DEBUG_LOG</strong>) to /wp-content/debug.log is enabled <small>(Generally defined in wp-config.php)</small>'
				);
			}
			if ( WP_DEBUG_DISPLAY ){
				$nebula_warnings[] = array(
					'level' => 'error',
					'description' => 'Debug errors and warnings are being displayed on the front-end (<Strong>WP_DEBUG_DISPLAY</strong>) <small>(Generally defined in wp-config.php)</small>'
				);
			}

			//Check for Google Analytics Tracking ID
			if ( !$this->get_option('ga_tracking_id') ){
				$nebula_warnings[] = array(
					'level' => 'error',
					'description' => '<a href="themes.php?page=nebula_options&tab=analytics&option=ga_tracking_id">Google Analytics tracking ID</a> is currently not set!',
					'url' => 'themes.php?page=nebula_options&tab=analytics&option=ga_tracking_id'
				);
			}

			//If Enhanced Ecommerce Plugin is missing Google Analytics Tracking ID
			if ( is_plugin_active('enhanced-e-commerce-for-woocommerce-store/woocommerce-enhanced-ecommerce-google-analytics-integration.php') ){
				$ee_ga_settings = get_option('woocommerce_enhanced_ecommerce_google_analytics_settings');
				if ( empty($ee_ga_settings['ga_id']) ){
					$nebula_warnings[] = array(
						'level' => 'error',
						'description' => '<a href="admin.php?page=wc-settings&tab=integration">WooCommerce Enhanced Ecommerce</a> is missing a Google Analytics ID!',
						'url' => 'admin.php?page=wc-settings&tab=integration'
					);
				}
			}

			//Check if the parent theme template is correctly referenced
			if ( is_child_theme() ){
				$active_theme = wp_get_theme();
				if ( !file_exists(dirname(get_stylesheet_directory()) . '/' . $active_theme->get('Template')) ){
					$nebula_warnings[] = array(
						'level' => 'error',
						'description' => 'A child theme is active, but its parent theme directory <strong>' . $active_theme->get('Template') . '</strong> does not exist!<br/><em>The "Template:" setting in the <a href="' . get_stylesheet_uri() . '" target="_blank" rel="noopener">style.css</a> file of the child theme must match the directory name (above) of the parent theme.</em>'
					);
				}
			}

			//Check if Relevanssi has built an index for search
			if ( is_plugin_active('relevanssi/relevanssi.php') && !get_option('relevanssi_indexed') ){
				$nebula_warnings[] = array(
					'level' => 'error',
					'description' => '<a href="options-general.php?page=relevanssi%2Frelevanssi.php">Relevanssi</a> must build an index to search the site. This must be triggered manually.'
				);
			}

			//Check if Google Optimize is enabled. This alert is because the Google Optimize style snippet will add a whitescreen effect during loading and should be disabled when not actively experimenting.
			if ( $this->get_option('google_optimize_id') ){
				$nebula_warnings[] = array(
					'level' => 'error',
					'description' => '<a href="https://optimize.google.com/optimize/home/" target="_blank" rel="noopener">Google Optimize</a> is enabled (via <a href="themes.php?page=nebula_options&tab=analytics&option=google_optimize_id">Nebula Options</a>). Disable when not actively experimenting!'
				);
			}

			//Service Worker checks
			if ( $this->get_option('service_worker') ){
				//Check for Service Worker JavaScript file when using Service Worker
				if ( !file_exists($this->sw_location(false)) ){
					$nebula_warnings[] = array(
						'level' => 'error',
						'description' => 'Service Worker is enabled in <a href="themes.php?page=nebula_options&tab=functions&option=service_worker">Nebula Options</a>, but no Service Worker JavaScript file was found. Either use the <a href="https://github.com/chrisblakley/Nebula/blob/master/Nebula-Child/resources/sw.js" target="_blank">provided sw.js file</a> (by moving it to the root directory), or override the function <a href="https://gearside.com/nebula/functions/sw_location/?utm_campaign=documentation&utm_medium=admin+notice&utm_source=service+worker#override" target="_blank">sw_location()</a> to locate the actual JavaScript file you are using.'
					);
				}

				//Check for /offline page when using Service Worker
				$offline_page = get_page_by_path('offline');
				if ( is_null($offline_page) ){
					$nebula_warnings[] = array(
						'level' => 'warn',
						'description' => 'It is recommended to make an Offline page when using Service Worker. <a href="post-new.php?post_type=page">Manually add one</a>'
					);
				}

				//Check for SSL when using Service Worker
				if ( !is_ssl() ){
					$nebula_warnings[] = array(
						'level' => 'warn',
						'description' => 'Service Worker requires an SSL. Either update the site to https or <a href="themes.php?page=nebula_options&tab=functions&option=service_worker">disable Service Worker</a>.'
					);
				}
			}

			//Check for "Just Another WordPress Blog" tagline
			if ( strtolower(get_bloginfo('description')) === 'just another wordpress site' ){
				$nebula_warnings[] = array(
					'level' => 'warn',
					'description' => '<a href="options-general.php">Site Tagline</a> is still "Just Another WordPress Site"!'
				);
			}

			//Check if all SCSS files were processed manually.
			if ( $this->get_option('scss') && (isset($_GET['sass']) || isset($_GET['scss'])) ){
				if ( $this->is_dev() || $this->is_client() ){
					$nebula_warnings[] = array(
						'level' => 'log',
						'description' => 'All SCSS files have been manually processed.'
					);
				} else {
					$nebula_warnings[] = array(
						'level' => 'error',
						'description' => 'You do not have permissions to manually process all SCSS files.'
					);
				}
			}

			return $nebula_warnings;
		}

		return false;
	}

	//Log warnings in the console
	public function console_warnings($console_warnings=array()){
		$warnings = $this->check_warnings();

		//If there are warnings, send them to the console.
		if ( !empty($warnings) ){
			echo '<script>';
			foreach( $warnings as $warning ){
				echo 'console.' . $warning['level'] . '("[Nebula] ' . strip_tags($warning['description']) . '");';
			}
			echo '</script>';
		}
	}

	//Modify styles if using Customizer colors but have Sass disabled
	public function no_sass_customizer_styles(){
		?>
			<style>

				<?php if ( get_theme_mod('nebula_primary_color') && !nebula()->get_option('scss') ):?>
					#bigheadingcon {background: <?php echo get_theme_mod('nebula_primary_color'); ?>;}
				<?php endif; ?>

				<?php if (  get_theme_mod('nebula_hero_bg_image') && get_theme_mod('nebula_hero_overlay_opacity') != 1 ):?>
					#hero-section {background-image: url( "<?php echo get_theme_mod('nebula_hero_bg_image'); ?>");}
				<?php endif; ?>

			</style>
		<?php
	}

	//Get the location URI of the Service Worker JavaScript file.
	//Override this in your child theme if changing the location or filename of the service worker.
	public function sw_location($uri=true){
		$override = apply_filters('pre_sw_location', null);
		if ( isset($override) ){return;}

		if ( !empty($uri) ){
			return get_site_url() . '/sw.js';
		}

		return get_home_path() . 'sw.js';
	}

	//Get the name of the current service worker cache
	public function get_sw_cache_name(){
		$override = apply_filters('pre_nebula_get_sw_cache_name', null);
		if ( isset($override) ){return;}

		$sw_cache_name = get_transient('nebula_sw_cache_name');
		if ( empty($sw_cache_name) || $this->is_debug() ){
			if ( $this->get_option('service_worker') && file_exists($this->sw_location(false)) ){
				WP_Filesystem();
				global $wp_filesystem;
				$sw_js = $wp_filesystem->get_contents($this->sw_location(false));

				if ( !empty($sw_js) ){
					preg_match("/var CACHE_NAME = '(.+)';/", $sw_js, $cache_name);
					$sw_cache_name = $cache_name[1];

					set_transient('nebula_sw_cache_name', $sw_cache_name, YEAR_IN_SECONDS); //1 year cache (This doesn't really need to expire)
				}
			}
		}

		return $sw_cache_name;
	}

	//Update variables within the service worker JavaScript file for install caching
	public function update_sw_js(){
		$override = apply_filters('pre_nebula_update_swjs', null);
		if ( isset($override) ){return;}

		WP_Filesystem();
		global $wp_filesystem;
		$sw_js = $wp_filesystem->get_contents($this->sw_location(false));

		if ( !empty($sw_js) ){
			$find = array(
				"/(var CACHE_NAME = ')(.+)(';)(.+$)?/m",
				"/(var OFFLINE_URL = ')(.+)(';)/m",
				"/(var OFFLINE_IMG = ')(.+)(';)/m",
				"/(var META_ICON = ')(.+)(';)/m",
				"/(var MANIFEST = ')(.+)(';)/m",
				"/(var HOME_URL = ')(.+)(';)/m",
				"/(var START_URL = ')(.+)(';)/m",
			);

			$new_cache_name = "nebula-" . strtolower(get_option('stylesheet')) . "-" . mt_rand(10000, 99999);

			$replace = array(
				"$1" . $new_cache_name . "$3" . " //" . date('l, F j, Y g:i:s A'),
				"$1" . home_url('/') . "offline/" . "$3",
				"$1" . get_theme_file_uri('/assets/img') . "/offline.svg" . "$3",
				"$1" . get_theme_file_uri('/assets/img/meta') . "/android-chrome-512x512.png" . "$3",
				"$1" . $this->manifest_json_location() . "$3",
				"$1" . home_url('/') . "$3",
				"$1" . home_url('/') . "?utm_source=homescreen" . "$3",
			);

			$sw_js = preg_replace($find, $replace, $sw_js);
			$update_sw_js = $wp_filesystem->put_contents($this->sw_location(false), $sw_js);

			if ( !empty($update_sw_js) ){
				do_action('nebula_wrote_sw_js');
				set_transient('nebula_sw_cache_name', $new_cache_name, YEAR_IN_SECONDS); //1 year cache (This doesn't really need to expire since it is updated everytime a new one is generated)
				return true;
			}
		}

		return false;
	}

	//Manifest JSON file location
	public function manifest_json_location($uri=true){
		$override = apply_filters('pre_manifest_json_location', null);
		if ( isset($override) ){return;}

		if ( $uri ){
			return get_template_directory_uri() . '/inc/manifest.json';
		}

		return get_template_directory() . '/inc/manifest.json';
	}

	//Create/Write a manifest JSON file
	public function manifest_json(){
		$override = apply_filters('pre_nebula_manifest_json', null);
		if ( isset($override) ){return;}

		$manifest_json = '{
			"name": "' . get_bloginfo('name') . ': ' . get_bloginfo('description') . '",
			"short_name": "' . get_bloginfo('name') . '",
			"description": "' . get_bloginfo('description') . '",
			"theme_color": "' . get_theme_mod('nebula_primary_color', $this->sass_color('primary')) . '",
			"background_color": "#fff",
			"gcm_sender_id": "' . $this->get_option('gcm_sender_id') . '",
			"Scope": "/",
			"start_url": "' . home_url('/') . '?utm_source=homescreen",
			"display": "standalone",
			"orientation": "portrait",
			"splash_pages": null,
			"icons": [';
		if ( has_site_icon() ){
			$manifest_json .= '{
				"src": "' . get_site_icon_url(16, get_theme_file_uri('/assets/img/meta') . '/favicon-16x16.png') . '",
				"sizes": "16x16",
				"type": "image/png"
			}, {
				"src": "' . get_site_icon_url(32, get_theme_file_uri('/assets/img/meta') . '/favicon-32x32.png') . '",
				"sizes": "32x32",
				"type": "image/png"
			}, {
				"src": "' . get_site_icon_url(192, get_theme_file_uri('/assets/img/meta') . '/android-chrome-192x192.png') . '",
				"sizes": "192x192",
				"type": "image/png"
			}, {
				"src": "' . get_site_icon_url(512, get_theme_file_uri('/assets/img/meta') . '/android-chrome-512x512.png') . '",
				"sizes": "512x512",
				"type": "image/png"
			}';
		} else {
			//Loop through all meta images
			$files = glob(get_theme_file_path('/assets/img/meta') . '/*.png');
			foreach ( $files as $file ){
				$filename = $this->url_components('filename', $file);
				$dimensions = getimagesize($file);
				$manifest_json .= '{
					"src": "' . get_theme_file_uri('/assets/img/meta') . '/' . $filename . '",
					"sizes": "' . $dimensions[0] . 'x' . $dimensions[1] . '",
					"type": "image/png"
				}, ';
			}
		}

		$manifest_json = rtrim($manifest_json,', ') . ']}';

		WP_Filesystem();
		global $wp_filesystem;
		$wp_filesystem->put_contents($this->manifest_json_location(false), $manifest_json);
	}

	//Redirect to favicon to force-clear the cached version when ?favicon is added to the URL.
	public function favicon_cache(){
		if ( array_key_exists('favicon', $_GET) ){
			header('Location: ' . get_theme_file_uri('/assets/img/meta') . '/favicon.ico');
		}
	}

	//Google Optimize Style Tag
	public function google_optimize_style(){
		if ( $this->get_option('google_optimize_id') ){ ?>
			<style>.async-hide { opacity: 0 !important} </style>
			<script>(function(a,s,y,n,c,h,i,d,e){s.className+=' '+y;h.end=i=function(){
			s.className=s.className.replace(RegExp(' ?'+y),'')};(a[n]=a[n]||[]).hide=h;
			setTimeout(function(){i();h.end=null},c);})(window,document.documentElement,
			'async-hide','dataLayer',2000,{'<?php echo $this->get_option('google_optimize_id'); ?>':true,});</script>
		<?php }
	}

	//Convenience function to return only the URL for specific thumbnail sizes of an ID.
	public function get_thumbnail_src($id=null, $size='full', $type='post'){
		if ( empty($id) ){
			return false;
		}

		$image_id = ( get_post_type($id) === 'attachment' || $type !== 'post' )? $id : get_post_thumbnail_id($id); //If the thumbnail (or attachment) ID was passed instead of the post ID

		if ( strpos($id, '<img') !== false || $size == 'full' ){
			$image = wp_get_attachment_image_src($image_id, $size);
			return $image[0];
		} else {
			return ( preg_match('~\bsrc="([^"]++)"~', get_the_post_thumbnail($id, $size), $matches) )? $matches[1] : ''; //Use Regex as a last resort if get_the_post_thumbnail() was passed.
		}
	}

	//Sets the current post/page template to a variable.
	function define_current_template($template){
		$GLOBALS['current_theme_template'] = str_replace(ABSPATH . 'wp-content', '', $template);
		return $template;
	}

	//Show different meta data information about the post. Typically used inside the loop.
	//Example: post_meta('by');
	public function post_meta($meta){
		$override = apply_filters('pre_post_meta', null, $meta);
		if ( isset($override) ){return;}

		if ( $meta == 'date' || $meta == 'time' || $meta == 'on' || $meta == 'day' || $meta == 'when' ){
			echo $this->post_date();
		} elseif ( $meta == 'author' || $meta == 'by' ){
			echo  $this->post_author();
		} elseif ( $meta == 'type' || $meta == 'cpt' || $meta == 'post_type' ){
			echo  $this->post_type();
		} elseif ( $meta == 'categories' || $meta == 'category' || $meta == 'cat' || $meta == 'cats' || $meta == 'in' ){
			echo  $this->post_categories();
		} elseif ( $meta == 'tags' || $meta == 'tag' ){
			echo  $this->post_tags();
		} elseif ( $meta == 'dimensions' || $meta == 'size' ){
			echo  $this->post_dimensions();
		} elseif ( $meta == 'exif' || $meta == 'camera' ){
			echo  $this->post_exif();
		} elseif ( $meta == 'comments' || $meta == 'comment' ){
			echo  $this->post_comments();
		} elseif ( $meta == 'social' || $meta == 'sharing' || $meta == 'share' ){
			 $this->social(array('facebook', 'twitter', 'google+', 'linkedin', 'pinterest'), 0);
		}
	}

	//Date post meta
	public function post_date($icon=true, $linked=true, $day=true){
		$the_icon = '';
		if ( $icon ){
			$the_icon = '<i class="fa fa-calendar-o"></i> ';
		}

		$the_day = '';
		if ( $day ){ //If the day should be shown (otherwise, just month and year).
			$the_day = get_the_date('d') . '/';
		}

		if ( $linked ){
			return '<span class="posted-on">' . $the_icon . '<span class="meta-item entry-date" datetime="' . get_the_time('c') . '" itemprop="datePublished" content="' . get_the_date('c') . '">' . '<a href="' . home_url('/') . get_the_date('Y/m') . '/' . '">' . get_the_date('F') . '</a>' . ' ' . '<a href="' . home_url('/') . get_the_date('Y/m') . '/' . $the_day . '">' . get_the_date('j') . '</a>' . ', ' . '<a href="' . home_url('/') . get_the_date('Y') . '/' . '">' . get_the_date('Y') . '</a>' . '</span></span>';
		} else {
			return '<span class="posted-on">' . $the_icon . '<span class="meta-item entry-date" datetime="' . get_the_time('c') . '" itemprop="datePublished" content="' . get_the_date('c') . '">' . get_the_date('F j, Y') . '</span></span>';
		}
	}

	//Author post meta
	public function post_author($icon=true, $linked=true, $force=false){
		$the_icon = '';
		if ( $icon ){
			$the_icon = '<i class="fa fa-user"></i> ';
		}

		if ( $this->get_option('author_bios') || $force ){
			if ( $linked && !$force ){
				return '<span class="posted-by" itemprop="author" itemscope itemtype="https://schema.org/Person">' . $the_icon . '<span class="meta-item entry-author">' . '<a href="' . get_author_posts_url(get_the_author_meta('ID')) . '" itemprop="name">' . get_the_author() . '</a></span></span>';
			} else {
				return '<span class="posted-by" itemprop="author" itemscope itemtype="https://schema.org/Person">' . $the_icon . '<span class="meta-item entry-author" itemprop="name">' . get_the_author() . '</span></span>';
			}
		}
	}

	//Post type meta
	public function post_type($icon=true){
		$post_icon_img = '<i class="fa fa-thumb-tack"></i>';
		if ( $icon ){
			global $wp_post_types;
			$post_type = get_post_type();

			if ( $post_type == 'post' ){
				$post_icon_img = '<i class="fa fa-thumb-tack"></i>';
			} elseif ( $post_type == 'page' ){
				$post_icon_img = '<i class="fa fa-file-text"></i>';
			} else {
				$post_icon = $wp_post_types[$post_type]->menu_icon;
				if ( !empty($post_icon) ){
					if ( strpos('dashicons-', $post_icon) >= 0 ){
						$post_icon_img = '<i class="dashicons-before ' . $post_icon . '"></i>';
					} else {
						$post_icon_img = '<img src="' . $post_icon . '" style="width: 16px; height: 16px;" />';
					}
				} else {
					$post_icon_img = '<i class="fa fa-thumb-tack"></i>';
				}
			}
		}

		return '<span class="meta-item post-type">' . $post_icon_img . ucwords(get_post_type()) . '</span>';
	}

	//Categories post meta
	public function post_categories($icon=true){
		$the_icon = '';
		if ( $icon ){
			$the_icon = '<i class="fa fa-bookmark"></i> ';
		}

		if ( is_object_in_taxonomy(get_post_type(), 'category') ){
			return '<span class="posted-in meta-item post-categories">' . $the_icon . get_the_category_list(', ') . '</span>';
		}
		return '';
	}

	//Tags post meta
	public function post_tags($icon=true){
		$tag_list = get_the_tag_list('', ', ');
		if ( $tag_list ){
			$the_icon = '';
			if ( $icon ){
				$tag_plural = ( count(get_the_tags()) > 1 )? 'tags' : 'tag';
				$the_icon = '<i class="fa fa-' . $tag_plural . '"></i> ';
			}
			return '<span class="posted-in meta-item post-tags">' . $the_icon . $tag_list . '</span>';
		}
		return '';
	}

	//Image dimensions post meta
	public function post_dimensions($icon=true, $linked=true){
		if ( wp_attachment_is_image() ){
			$the_icon = '';
			if ( $icon ){
				$the_icon = '<i class="fa fa-expand"></i> ';
			}

			$metadata = wp_get_attachment_metadata();
			if ( $linked ){
				echo '<span class="meta-item meta-dimensions">' . $the_icon . '<a href="' . wp_get_attachment_url() . '" >' . $metadata['width'] . ' &times; ' . $metadata['height'] . '</a></span>';
			} else {
				echo '<span class="meta-item meta-dimensions">' . $the_icon . $metadata['width'] . ' &times; ' . $metadata['height'] . '</span>';
			}
		}
	}

	//Image EXIF post meta
	public function post_exif($icon=true){
		$the_icon = '';
		if ( $icon ){
			$the_icon = '<i class="fa fa-camera"></i> ';
		}

		$imgmeta = wp_get_attachment_metadata();
		if ( $imgmeta ){ //Check for Bad Data
			if ( $imgmeta['image_meta']['focal_length'] == 0 || $imgmeta['image_meta']['aperture'] == 0 || $imgmeta['image_meta']['shutter_speed'] == 0 || $imgmeta['image_meta']['iso'] == 0 ){
				$output = 'No valid EXIF data found';
			} else { //Convert the shutter speed retrieve from database to fraction
				if ( (1/$imgmeta['image_meta']['shutter_speed']) > 1 ){
					if ( (number_format((1/$imgmeta['image_meta']['shutter_speed']), 1)) == 1.3 || number_format((1/$imgmeta['image_meta']['shutter_speed']), 1) == 1.5 || number_format((1/$imgmeta['image_meta']['shutter_speed']), 1) == 1.6 || number_format((1/$imgmeta['image_meta']['shutter_speed']), 1) == 2.5 ){
						$pshutter = '1/' . number_format((1/$imgmeta['image_meta']['shutter_speed']), 1, '.', '') . ' second';
					} else {
						$pshutter = '1/' . number_format((1/$imgmeta['image_meta']['shutter_speed']), 0, '.', '') . ' second';
					}
				} else {
					$pshutter = $imgmeta['image_meta']['shutter_speed'] . " seconds";
				}

				$output = '<time datetime="' . date('c', $imgmeta['image_meta']['created_timestamp']) . '"><span class="month">' . date('F', $imgmeta['image_meta']['created_timestamp']) . '</span> <span class="day">' . date('j', $imgmeta['image_meta']['created_timestamp']) . '</span><span class="suffix">' . date('S', $imgmeta['image_meta']['created_timestamp']) . '</span> <span class="year">' . date('Y', $imgmeta['image_meta']['created_timestamp']) . '</span></time>' . ', ';
				$output .= $imgmeta['image_meta']['camera'] . ', ';
				$output .= $imgmeta['image_meta']['focal_length'] . 'mm' . ', ';
				$output .= '<span style="font-style: italic; font-family: Trebuchet MS, Candara, Georgia; text-transform: lowercase;">f</span>/' . $imgmeta['image_meta']['aperture'] . ', ';
				$output .= $pshutter . ', ';
				$output .= $imgmeta['image_meta']['iso'] .' ISO';
			}
		} else {
			$output = 'No EXIF data found';
		}

		return '<span class="meta-item meta-exif">' . $the_icon . $output . '</span>';
	}

	//Comments post meta
	public function post_comments($icon=true, $linked=true, $empty=true){
		$comments_text = 'Comments';
		if ( get_comments_number() == 0 ){
			$comment_icon = 'fa-comment-o';
			$comment_show = ( $empty )? '' : 'hidden'; //If comment link should show if no comments. True = show, False = hidden
		} elseif ( get_comments_number() == 1 ){
			$comment_icon = 'fa-comment';
			$comments_text = 'Comment';
		} elseif ( get_comments_number() > 1 ){
			$comment_icon = 'fa-comments';
		}

		$the_icon = '';
		if ( $icon ){
			$the_icon = '<i class="fa ' . $comment_icon . '"></i> ';
		}

		if ( $linked ){
			$postlink = ( is_single() )? '' : get_the_permalink();
			return '<span class="meta-item posted-comments ' . $comment_show . '">' . $the_icon . '<a class="nebulametacommentslink" href="' . $postlink . '#nebulacommentswrapper">' . get_comments_number() . ' ' . $comments_text . '</a></span>';
		} else {
			return '<span class="meta-item posted-comments ' . $comment_show . '">' . $the_icon . get_comments_number() . ' ' . $comments_text . '</span>';
		}
	}

	//Use this instead of the_excerpt(); and get_the_excerpt(); to have better control over the excerpt.
	//Inside the loop (or outside the loop for current post/page): nebula()->excerpt(array('words' => 20, 'ellipsis' => true));
	//Outside the loop: nebula()->excerpt(array('id' => 572, 'words' => 20, 'ellipsis' => true));
	//Custom text: nebula()->excerpt(array('text' => 'Lorem ipsum <strong>dolor</strong> sit amet.', 'more' => 'Continue &raquo;', 'words' => 3, 'ellipsis' => true, 'strip_tags' => true));
	public function excerpt($options=array()){
		$override = apply_filters('pre_nebula_excerpt', null, $options);
		if ( isset($override) ){return;}

		$defaults = array(
			'id' => false,
			'text' => false,
			'characters' => false,
			'chars' => false, //Alias of "characters"
			'words' => 55,
			'length' => false, //Alias of "words"
			'ellipsis' => false,
			'url' => false,
			'more' => 'Read More &raquo;',
			'strip_shortcodes' => true,
			'strip_tags' => true,
			'wrap_links' => false,
		);

		$data = array_merge($defaults, $options);

		//Establish text
		if ( empty($data['text']) ){
			$the_post = ( !empty($data['id']) && is_int($data['id']) )? get_post($data['id']) : get_post(get_the_ID());
			if ( empty($the_post) ){
				return false;
			}
			$data['text'] = ( !empty($the_post->post_excerpt) )? $the_post->post_excerpt : $the_post->post_content;
		}

		//Strip Newlines
		$data['text'] = str_replace(array("\r\n", "\r", "\n"), " ", $data['text']); //Replace newline characters (keep double quotes)
		$data['text'] = preg_replace('/\s+/', ' ', $data['text']); //Replace multiple spaces with single space

		//Strip Shortcodes
		if ( $data['strip_shortcodes'] ){
			$data['text'] = strip_shortcodes($data['text']);
		} else {
			$data['text'] = preg_replace('~(?:\[/?)[^/\]]+/?\]~s', ' ', $data['text']);
		}

		//Strip Tags
		if ( $data['strip_tags'] ){
			$data['text'] = strip_tags($data['text'], '');
		}

		//Characters (or Chars)
		if ( (!empty($data['characters']) && is_int($data['characters'])) || (!empty($data['chars']) && is_int($data['chars'])) ){
			$char_limit = ( !empty($data['characters']) )? $data['characters'] : $data['chars'];
			$chars = $this->word_limit_chars($data['text'], $char_limit); //Returns array: $chars[0] is the string, $chars[1] is boolean if it was limited or not.
			$data['text'] = $chars['text'];
		}

		//Words (or Length)
		if ( (!empty($data['words']) && is_int($data['words'])) || (!empty($data['length']) && is_int($data['length'])) ){
			$word_limit = ( !empty($data['length']) )? $data['length'] : $data['words'];
			$words = $this->string_limit_words($data['text'], $word_limit); //Returns array: $words[0] is the string, $words[1] is boolean if it was limited or not.
			$data['text'] = $words['text'];
		}

		//Check here for links to wrap
		if ( $data['wrap_links'] ){
			$data['text'] = preg_replace('/(\(?(?:(http|https|ftp):\/\/)?(?:((?:[^\W\s]|\.|-|[:]{1})+)@{1})?((?:www.)?(?:[^\W\s]|\.|-)+[\.][^\W\s]{2,4}|localhost(?=\/)|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})(?::(\d*))?([\/]?[^\s\?]*[\/]{1})*(?:\/?([^\s\n\?\[\]\{\}\#]*(?:(?=\.)){1}|[^\s\n\?\[\]\{\}\.\#]*)?([\.]{1}[^\s\?\#]*)?)?(?:\?{1}([^\s\n\#\[\]]*))?([\#][^\s\n]*)?\)?)(?![^<]*<\/)/i', '<a class="nebula-excerpt-url" href="$1">$1</a>', $data['text']); //Capture any URL not within < and </ using a negative lookahead (so it plays nice in case strip_tags is false)
		}

		//Ellipsis
		if ( $data['ellipsis'] && !empty($limited['is_limited']) ){
			$data['text'] .= '&hellip;';
		}

		//Link
		if ( !empty($data['more']) ){
			if ( empty($data['url']) ){ //If has "more" text, but no link URL
				$data['url'] = ( !empty($data['id']) )? get_permalink($data['id']) : get_permalink(get_the_id()); //Use the ID if available, or use the current ID.
			}

			$data['text'] .= ' <a class="nebula_excerpt" href="' . $data['url'] . '">' . $data['more'] . '</a>';
		}

		return $data['text'];
	}

	//Display Social Buttons
	public function social($networks=array('facebook', 'twitter', 'google+'), $counts=0){
		$override = apply_filters('pre_nebula_social', null, $networks, $counts);
		if ( isset($override) ){return;}

		if ( is_string($networks) ){ //if $networks is a string, create an array for the string.
			$networks = array($networks);
		} elseif ( is_int($networks) && ($networks == 1 || $networks == 0) ){ //If it is an integer of 1 or 0, then set it to $counts
			$counts = $networks;
			$networks = array('facebook', 'twitter', 'google+');
		} elseif ( !is_array($networks) ){
			$networks = array('facebook', 'twitter', 'google+');
		}
		$networks = array_map('strtolower', $networks); //Convert $networks to lower case for more flexible string matching later.

		echo '<div class="sharing-links">';
		foreach ( $networks as $network ){
			//Facebook
			if ( in_array($network, array('facebook', 'fb')) ){
				$this->facebook_share($counts);
			}

			//Twitter
			if ( in_array($network, array('twitter')) ){
				$this->twitter_tweet($counts);
			}

			//Google+
			if ( in_array($network, array('google_plus', 'google', 'googleplus', 'google+', 'g+', 'gplus', 'g_plus', 'google plus', 'google-plus', 'g-plus')) ){
				$this->google_plus($counts);
			}

			//LinkedIn
			if ( in_array($network, array('linkedin', 'li', 'linked-in', 'linked_in')) ){
				$this->linkedin_share($counts);
			}

			//Pinterest
			if ( in_array($network, array('pinterest', 'pin')) ){
				$this->pinterest_pin($counts);
			}
		}
		echo '</div><!-- /sharing-links -->';
	}

	/*
		Social Button Functions
		//@TODO "Nebula" 0: Eventually upgrade these to support vertical count bubbles as an option.
	*/

	public function facebook_share($counts=0, $url=false){
		$override = apply_filters('pre_nebula_facebook_share', null, $counts);
		if ( isset($override) ){return;}
		?>
		<div class="nebula-social-button facebook-share require-fbsdk">
			<div class="fb-share-button" data-href="<?php echo ( !empty($url) )? $url : get_page_link(); ?>" data-layout="<?php echo ( $counts != 0 )? 'button_count' : 'button'; ?>"></div>
		</div>
	<?php }


	public function facebook_like($counts=0, $url=false){
		$override = apply_filters('pre_nebula_facebook_like', null, $counts);
		if ( isset($override) ){return;}
		?>
		<div class="nebula-social-button facebook-like require-fbsdk">
			<div class="fb-like" data-href="<?php echo ( !empty($url) )? $url : get_page_link(); ?>" data-layout="<?php echo ( $counts != 0 )? 'button_count' : 'button'; ?>" data-action="like" data-show-faces="false" data-share="false"></div>
		</div>
	<?php }

	public function facebook_both($counts=0, $url=false){
		$override = apply_filters('pre_nebula_facebook_both', null, $counts);
		if ( isset($override) ){return;}
		?>
		<div class="nebula-social-button facebook-both require-fbsdk">
			<div class="fb-like" data-href="<?php echo ( !empty($url) )? $url : get_page_link(); ?>" data-layout="<?php echo ( $counts != 0 )? 'button_count' : 'button'; ?>" data-action="like" data-show-faces="false" data-share="true"></div>
		</div>
	<?php }


	public function twitter_tweet($counts=0){
		$override = apply_filters('pre_nebula_twitter_tweet', null, $counts);
		if ( isset($override) ){return;}
		?>
		<div class="nebula-social-button twitter-tweet">
			<a href="https://twitter.com/share" class="twitter-share-button" <?php echo ( $counts != 0 )? '': 'data-count="none"'; ?>>Tweet</a>
			<?php $this->twitter_widget_script(); ?>
		</div>
		<?php
	}

	public function twitter_follow($counts=0, $username=false){
		$override = apply_filters('pre_nebula_twitter_follow', null, $counts, $username);
		if ( isset($override) ){return;}

		if ( empty($username) && !$this->get_option('twitter_username') ){
			return false;
		} elseif ( empty($username) && $this->get_option('twitter_username') ){
			$username = $this->get_option('twitter_username');
		} elseif ( strpos($username, '@') === false ){
			$username = '@' . $username;
		}
		?>
		<div class="nebula-social-button twitter-follow">
			<a href="https://twitter.com/<?php echo str_replace('@', '', $username); ?>" class="twitter-follow-button" <?php echo ( $counts != 0 )? '': 'data-show-count="false"'; ?> <?php echo ( !empty($username) )? '': 'data-show-screen-name="false"'; ?>>Follow <?php echo $username; ?></a>
			<?php $this->twitter_widget_script(); ?>
		</div>
		<?php
	}

	public function twitter_widget_script(){
		if ( empty($this->twitter_widget_loaded) ){
			?>
			<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
			<?php
			$this->twitter_widget_loaded = true;
		}
	}

	public function google_plus($counts=0){
		$override = apply_filters('pre_nebula_google_plus', null, $counts);
		if ( isset($override) ){return;}
		?>
		<div class="nebula-social-button google-plus-plus-one">
			<div class="g-plusone" data-size="medium" <?php echo ( $counts != 0 )? '' : 'data-annotation="none"'; ?>></div>
			<?php if ( empty($this->google_plus_widget_loaded) ) : ?>
				<script src="https://apis.google.com/js/platform.js" async defer></script>
				<?php $this->google_plus_widget_loaded = true; ?>
			<?php endif; ?>
		</div>
		<?php
	}

	public function linkedin_share($counts=0){
		$override = apply_filters('pre_nebula_linkedin_share', null, $counts);
		if ( isset($override) ){return;}
		?>
		<div class="nebula-social-button linkedin-share">
			<?php $this->linkedin_widget_script(); ?>
			<script type="IN/Share" <?php echo ( $counts != 0 )? 'data-counter="right"' : ''; ?>></script>
		</div>
		<?php
	}

	public function linkedin_follow($counts=0){
		$override = apply_filters('pre_nebula_linkedin_follow', null, $counts);
		if ( isset($override) ){return;}
		?>
		<div class="nebula-social-button linkedin-follow">
			<?php $this->linkedin_widget_script(); ?>
			<script type="IN/FollowCompany" data-id="1337" <?php echo ( $counts != 0 )? 'data-counter="right"' : ''; ?>></script>
		</div>
		<?php
	}

	public function linkedin_widget_script(){
		if ( empty($this->linkedin_widget_loaded) ){
			?>
			<script type="text/javascript" src="//platform.linkedin.com/in.js" async defer> lang: en_US</script>
			<?php
			$this->linkedin_widget_loaded = true;
		}
	}

	public function pinterest_pin($counts=0){ //@TODO "Nebula" 0: Bubble counts are not showing up...
		$override = apply_filters('pre_nebula_pinterest_pin', null, $counts);
		if ( isset($override) ){return;}

		if ( has_post_thumbnail() ){
			$featured_image = $this->get_thumbnail_src(get_the_post_thumbnail($post->ID, 'full'));
		} else {
			$featured_image = get_template_directory_uri() . '/assets/img/meta/og-thumb.png'; //@TODO "Nebula" 0: This should probably be a square? Check the recommended dimensions.
		}
		?>
		<div class="nebula-social-button pinterest-pin">
			<a href="//www.pinterest.com/pin/create/button/?url=<?php echo get_page_link(); ?>&media=<?php echo $featured_image; ?>&description=<?php echo urlencode(get_the_title()); ?>" data-pin-do="buttonPin" data-pin-config="<?php echo ( $counts != 0 )? 'beside' : 'none'; ?>" data-pin-color="red">
				<img src="//assets.pinterest.com/images/pidgets/pinit_fg_en_rect_red_20.png" />
			</a>
			<?php if ( empty($this->pinterest_pin_widget_loaded) ): ?>
				<script type="text/javascript" async defer src="//assets.pinterest.com/js/pinit.js"></script>
				<?php $this->pinterest_pin_widget_loaded = true; ?>
			<?php endif; ?>
		</div>
		<?php
	}

	//Get metadata from Youtube or Vimeo
	public function vimeo_meta($id, $meta=''){return $this->video_meta('vimeo', $id);}
	public function youtube_meta($id, $meta=''){return $this->video_meta('youtube', $id);}
	public function video_meta($provider, $id){
		$override = apply_filters('pre_video_meta', null, $provider, $id);
		if ( isset($override) ){return;}

		$video_metadata = array(
			'origin' => $this->url_components('basedomain'),
			'id' => $id,
			'error' => false
		);

		if ( !empty($provider) ){
			$provider = strtolower($provider);
		} else {
			$video_metadata['error'] = 'Video provider is required.';
			return $video_metadata;
		}

		//Get Transients
		$video_json = get_transient('nebula_' . $provider . '_' . $id);
		if ( empty($video_json) ){ //No ?debug option here (because multiple calls are made to this function). Clear with a force true when needed.
			if ( $provider == 'youtube' ){
				if ( !$this->get_option('google_server_api_key') && $this->is_staff() ){
					echo '<script>console.warn("No Google Youtube Iframe API key. Youtube videos may not be tracked!");</script>';
					$video_metadata['error'] = 'No Google Youtube Iframe API key.';
				}

				$response = $this->remote_get('https://www.googleapis.com/youtube/v3/videos?id=' . $id . '&part=snippet,contentDetails,statistics&key=' . $this->get_option('google_server_api_key'));
				if ( is_wp_error($response) ){
					$video_metadata['error'] = 'Youtube video is unavailable.';
					return $video_metadata;
				}

				$video_json = $response['body'];
			} elseif ( $provider == 'vimeo' ){
				$response = $this->remote_get('http://vimeo.com/api/v2/video/' . $id . '.json');
				if ( is_wp_error($response) ){
					$video_metadata['error'] = 'Vimeo video is unavailable.';
					return $video_metadata;
				}

				$video_json = $response['body'];
			}

			set_transient('nebula_' . $provider . '_' . $id, $video_json, HOUR_IN_SECONDS*12); //12 hour expiration
		}
		$video_json = json_decode($video_json);

		//Check for errors
		if ( empty($video_json) ){
			if ( current_user_can('manage_options') || $this->is_dev() ){
				if ( $provider == 'youtube' ){
					$video_metadata['error'] = 'A Youtube Data API error occurred. Make sure the Youtube Data API is enabled in the Google Developer Console and the server key is saved in Nebula Options.';
				} else {
					$video_metadata['error'] = 'A Vimeo API error occurred (A video with ID ' . $id . ' may not exist). Tracking will not be possible.';
				}
			}
			return $video_metadata;
		} elseif ( $provider == 'youtube' && !empty($video_json->error) ){
			if ( current_user_can('manage_options') || $this->is_dev() ){
				$video_metadata['error'] = 'Youtube API Error: ' . $video_json->error->message;
			}
			return $video_metadata;
		} elseif ( $provider == 'youtube' && empty($video_json->items) ){
			if ( current_user_can('manage_options') || $this->is_dev() ){
				$video_metadata['error'] = 'A Youtube video with ID ' . $id . ' does not exist.';
			}
			return $video_metadata;
		} elseif ( $provider == 'vimeo' && is_array($video_json) && empty($video_json[0]) ){
			$video_metadata['error'] = 'A Vimeo video with ID ' . $id . ' does not exist.';
		}

		//Build Data
		if ( $provider == 'youtube' ){
			$video_metadata['raw'] = $video_json->items[0];
			$video_metadata['title'] = $video_json->items[0]->snippet->title;
			$video_metadata['safetitle'] = preg_replace('/(\W)/i', '', $video_json->items[0]->snippet->title);
			$video_metadata['description'] = $video_json->items[0]->snippet->description;
			$video_metadata['thumbnail'] = $video_json->items[0]->snippet->thumbnails->high->url;
			$video_metadata['author'] = $video_json->items[0]->snippet->channelTitle;
			$video_metadata['date'] = $video_json->items[0]->snippet->publishedAt;
			$video_metadata['url'] = 'https://www.youtube.com/watch?v=' . $id;
			$start = new DateTime('@0'); //Unix epoch
			$start->add(new DateInterval($video_json->items[0]->contentDetails->duration));
			$duration_seconds = intval($start->format('H'))*60*60 + intval($start->format('i'))*60 + intval($start->format('s'));
		} elseif ( $provider == 'vimeo' ){
			$video_metadata['raw'] = $video_json[0];
			$video_metadata['title'] = $video_json[0]->title;
			$video_metadata['safetitle'] = preg_replace('/(\W)/i', '', $video_json[0]->title);
			$video_metadata['description'] = $video_json[0]->description;
			$video_metadata['thumbnail'] = $video_json[0]->thumbnail_large;
			$video_metadata['author'] = $video_json[0]->user_name;
			$video_metadata['date'] = $video_json[0]->upload_date;
			$video_metadata['url'] = $video_json[0]->url;
			$duration_seconds = strval($video_json[0]->duration);
		}
		$video_metadata['duration'] = array(
			'time' => intval(gmdate("i", $duration_seconds)) . gmdate(":s", $duration_seconds),
			'seconds' => $duration_seconds
		);

		return $video_metadata;
	}

	//Breadcrumbs
	public function breadcrumbs($options=array()){
		$override = apply_filters('pre_nebula_breadcrumbs', null);
		if ( isset($override) ){return;}

		global $post;
		$defaults = array(
			'delimiter' => '&rsaquo;', //Delimiter between crumbs
			'home' => '<i class="fa fa-home"></i>', //Text for the 'Home' link
			'home_link' => home_url('/'),
			'current' => true, //Show/Hide the current title in the breadcrumb
			'before' => '<span class="current">', //Tag before the current crumb
			'after' => '</span>', //Tag after the current crumb
			'force' => false //Override the breadcrumbs with an array of specific links
		);

		$data = array_merge($defaults, $options);
		$delimiter_html = '<span class="arrow">' . $data['delimiter'] . '</span>';

		if ( !empty($data['force']) ){ //If using forced override
			echo '<div class="nebula-breadcrumbs" itemscope itemtype="http://schema.org/BreadcrumbList">';

			foreach ( $data['force'] as $node ){
				$node_text = ( !empty($node['text']) )? $node['text'] : $node[0];
				$node_url = false;
				if ( !empty($node['url']) ){
					$node_url = $node['url'];
				} else {
					if ( !empty($node[1]) ){
						$node_url = $node[1];
					}
				}

				if ( !empty($node_text) ){
					if ( !empty($node_url) ){
						echo '<a href="' . $node_url . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">';
					}
					echo $node_text;
					if ( !empty($node_url) ){
						echo '</a>';
					}
					echo ' ' . $delimiter_html . ' ';
				}
			}

			if ( !empty($data['current']) ){
				echo $data['before'] . get_the_title() . $data['after'];
			}

			echo '</div>';
		} elseif ( is_home() || is_front_page() ){
			echo '<div class="nebula-breadcrumbs" itemscope itemtype="http://schema.org/BreadcrumbList"><a href="' . $data['home_link'] . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">' . $data['home'] . '</a></div></div>';
			return false;
		} else {
			echo '<div class="nebula-breadcrumbs" itemscope itemtype="http://schema.org/BreadcrumbList"><a href="' . $data['home_link'] . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">' . $data['home'] . '</a> ' . $delimiter_html . ' ';
			if ( is_category() ){
				$thisCat = get_category(get_query_var('cat'), false);
				if ( $thisCat->parent != 0 ){
					echo get_category_parents($thisCat->parent, true, ' ' . $delimiter_html . ' ');
				}
				echo $data['before'] . 'Category: ' . single_cat_title('', false) . $data['after'];
			} elseif ( is_search() ){
				echo $data['before'] . 'Search results' . $data['after'];
			} elseif ( is_day() ){
				echo '<a href="' . get_year_link(get_the_time('Y')) . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">' . get_the_time('Y') . '</a> ' . $delimiter_html . ' ';
				echo '<a href="' . get_month_link(get_the_time('Y'),get_the_time('m')) . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">' . get_the_time('F') . '</a> ' . $delimiter_html . ' ';
				echo $data['before'] . get_the_time('d') . $data['after'];
			} elseif ( is_month() ){
				echo '<a href="' . get_year_link(get_the_time('Y')) . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">' . get_the_time('Y') . '</a> ' . $delimiter_html . ' ';
				echo $data['before'] . get_the_time('F') . $data['after'];
			} elseif ( is_year() ){
				echo $data['before'] . get_the_time('Y') . $data['after'];
			} elseif ( is_single() && !is_attachment() ){
				if ( get_post_type() != 'post' ){
					$post_type = get_post_type_object(get_post_type());
					$slug = $post_type->rewrite;
					echo '<a href="' . $data['home_link'] . $slug['slug'] . '/">' . $post_type->labels->singular_name . '</a>';
					if ( !empty($data['current']) ){
						echo ' ' . $delimiter_html . ' ' . $data['before'] . get_the_title() . $data['after'];
					}
				} else {
					$cat = get_the_category();
					if ( !empty($cat) ){
						$cat = $cat[0];
						$cats = get_category_parents($cat, true, ' ' . $delimiter_html . ' ');
						if ( empty($data['current']) ){
							$cats = preg_replace("#^(.+)\s" . $delimiter_html . "\s$#", "$1", $cats);
						}
						echo $cats;
						if ( !empty($data['current']) ){
							echo $data['before'] . get_the_title() . $data['after'];
						}
					}
				}
			} elseif ( !is_single() && !is_page() && get_post_type() != 'post' && !is_404() ){
				if ( is_archive() ){ //@TODO "Nebula" 0: Might not be perfect... This may never else out.
					$userdata = get_user_by('slug', get_query_var('author_name'));
					echo $data['before'] . $userdata->first_name . ' ' . $userdata->last_name . $data['after'];
				} else { //What does this one do?
					$post_type = get_post_type_object(get_post_type());
					echo $data['before'] . $post_type->labels->singular_name . $data['after'];
				}
			} elseif ( is_attachment() ){ //@TODO "Nebula" 0: Check for gallery pages? If so, it should be Home > Parent(s) > Gallery > Attachment
				if ( !empty($post->post_parent) ){ //@TODO "Nebula" 0: What happens if the page parent is a child of another page?
					echo '<a href="' . get_permalink($post->post_parent) . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">' . get_the_title($post->post_parent) . '</a>' . ' ' . $delimiter_html . ' ' . get_the_title();
				} else {
					echo get_the_title();
				}
			} elseif ( is_page() && !$post->post_parent ){
				if ( !empty($data['current']) ){
					echo $data['before'] . get_the_title() . $data['after'];
				}
			} elseif ( is_page() && $post->post_parent ){
				$parent_id = $post->post_parent;
				$breadcrumbs = array();
				while ( $parent_id ){
					$page = get_page($parent_id);
					$breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">' . get_the_title($page->ID) . '</a>';
					$parent_id  = $page->post_parent;
				}
				$breadcrumbs = array_reverse($breadcrumbs);
				for ( $i = 0; $i < count($breadcrumbs); $i++ ){
					echo $breadcrumbs[$i];
					if ( $i != count($breadcrumbs)-1 ){
						echo ' ' . $delimiter_html . ' ';
					}
				}
				if ( !empty($data['current']) ){
					echo ' ' . $delimiter_html . ' ' . $data['before'] . get_the_title() . $data['after'];
				}
			} elseif ( is_tag() ){
				echo $data['before'] . 'Tag: ' . single_tag_title('', false) . $data['after'];
			} elseif ( is_author() ){
				global $author;
				$userdata = get_userdata($author);
				echo $data['before'] . $userdata->display_name . $data['after'];
			} elseif ( is_404() ){
				echo $data['before'] . 'Error 404' . $data['after'];
			}

			if ( get_query_var('paged') ){
				echo ' (Page ' . get_query_var('paged') . ')';
			}
			echo '</div>';
		}
	}

	//Modified WordPress search form using Bootstrap components
	public function search_form(){
		$override = apply_filters('pre_nebula_search_form', null);
		if ( isset($override) ){return;}

		$placeholder = ( get_search_query() )? get_search_query() : 'Search';

		$form = '<form id="searchform" class="form-group form-inline ignore-form" role="search" method="get" action="' . home_url('/') . '">
					<div class="input-group mb-2 mr-sm-2 mb-sm-0">
						<div class="input-group-addon mb-2"><i class="fa fa-search"></i></div>
						<label class="sr-only" for="s">Search</label>
						<input id="s" class="form-control ignore-form mb-2" type="text" name="s" value="' . get_search_query() . '" placeholder="' . $placeholder . '" role="search" />
					</div>

					<button id="searchsubmit" class="btn btn-brand wp_search_submit mb-2" type="submit">Submit</button>
				</form>';

		return $form;
	}

	//Easily create markup for a Hero area search input
	public function hero_search($placeholder='What are you looking for?'){
		$override = apply_filters('pre_nebula_hero_search', null, $placeholder);
		if ( isset($override) ){return;}

		$form = '<div id="nebula-hero-formcon">
				<form id="nebula-hero-search" class="form-group search ignore-form" method="get" action="' . home_url('/') . '">
					<label class="sr-only" for="nebula-hero-search-input">Autocomplete Search</label>
					<input id="nebula-hero-search-input" type="search" class="form-control open input search nofade ignore-form" name="s" placeholder="' . $placeholder . '" autocomplete="off" role="search" tabindex="0" x-webkit-speech />
				</form>
			</div>';
		return $form;
	}

	//Infinite Load
	//Ajax call handle in nebula()->infinite_load();
	public function infinite_load_query($args=array('post_status' => 'publish', 'showposts' => 4), $loop=false){
		$override = apply_filters('pre_nebula_infinite_load_query', null);
		if ( isset($override) ){return;}

		global $wp_query;
		if ( empty($args['paged']) ){
			$args['paged'] = 1;
			if ( get_query_var('paged') ){
				$args['paged'] = get_query_var('paged');
				?>
				<div class="infinite-start-note">
					<a href="<?php echo get_the_permalink(); ?>">&laquo; Back to page 1</a>
				</div>
				<?php
			} elseif ( !empty($wp_query->query['paged']) ){
				$args['paged'] = $wp_query->query['paged'];
				?>
				<div class="infinite-start-note">
					<a href="<?php echo get_the_permalink(); ?>">&laquo; Back to page 1</a>
				</div>
				<?php
			}
		}

		query_posts($args);

		if ( empty($args['post_type']) ){
			$post_type_label = 'posts';
		} else {
			$post_type = ( is_array($args['post_type']) )? $args['post_type'][0] : $args['post_type'];
			$post_type_obj = get_post_type_object($args['post_type']);
			$post_type_label = lcfirst($post_type_obj->label);
		}
		?>

		<div id="infinite-posts-list" data-max-pages="<?php echo $wp_query->max_num_pages; ?>" data-max-posts="<?php echo $wp_query->found_posts; ?>">
			<?php
			$loop = sanitize_text_field($loop);
			if ( !$loop ){
				get_template_part('loop');
			} else {
				if ( function_exists($loop) ){
					call_user_func($loop);
				} elseif ( locate_template($loop . '.php') ){
					get_template_part($loop);
				} else {
					if ( $this->is_dev() ){
						echo '<strong>Warning:</strong> The custom loop template or function ' . $loop . ' does not exist! Falling back to loop.php.';
					}
					get_template_part('loop');
				}
			}
			?>
		</div>

		<?php do_action('nebula_infinite_before_load_more'); ?>

		<div class="loadmorecon <?php echo ( $args['paged'] >= $wp_query->max_num_pages )? 'disabled' : ''; ?>">
			<a class="infinite-load-more" href="#"><?php echo ( $args['paged'] >= $wp_query->max_num_pages )? 'No more ' . $post_type_label . '.' : 'Load More'; ?></a>
			<div class="infinite-loading">
				<div class="a"></div> <div class="b"></div> <div class="c"></div>
			</div>
		</div>

		<script><?php //Must be in PHP so $args can be encoded. ?>
			jQuery(function(){
				var pageNumber = <?php echo $args['paged']; ?>+1;

				jQuery('.infinite-load-more').on('click touch tap', function(){
					var maxPages = jQuery('#infinite-posts-list').attr('data-max-pages');
					var maxPosts = jQuery('#infinite-posts-list').attr('data-max-posts');

					if ( pageNumber <= maxPages ){
						jQuery('.loadmorecon').addClass('loading');
						jQuery.ajax({
							type: "POST",
							url: nebula.site.ajax.url,
							data: {
								nonce: nebula.site.ajax.nonce,
								action: 'nebula_infinite_load',
								page: pageNumber,
								args: <?php echo json_encode($args); ?>,
								loop: <?php echo json_encode($loop); ?>,
							},
							success: function(response){
								jQuery("#infinite-posts-list").append('<div class="clearfix infinite-page infinite-page-' + (pageNumber-1) + ' sliding" style="display: none;">' + response + '</div>');
								jQuery('.infinite-page-' + (pageNumber-1)).slideDown({
									duration: 750,
									easing: 'easeInOutQuad',
									complete: function(){
										jQuery('.loadmorecon').removeClass('loading');
										jQuery('.infinite-page.sliding').removeClass('sliding');
										nebula.dom.document.trigger('nebula_infinite_slidedown_complete');
									}
								});

								if ( pageNumber >= maxPages ){
									jQuery('.loadmorecon').addClass('disabled').find('a').text('No more <?php echo $post_type_label; ?>.');
								}

								var newQueryStrings = '';
								if ( typeof document.URL.split('?')[1] !== 'undefined' ){
									newQueryStrings = '?' + document.URL.split('?')[1].replace(/[?&]paged=\d+/, '');
								}

								history.replaceState(null, document.title, nebula.post.permalink + 'page/' + pageNumber + newQueryStrings);
								nebula.dom.document.trigger('nebula_infinite_finish');
								ga('send', 'event', 'Infinite Query', 'Load More', 'Loaded page ' + pageNumber);
								nv('increment', 'infinite_query_loads');
								pageNumber++;
							},
							error: function(XMLHttpRequest, textStatus, errorThrown){
								jQuery(document).trigger('nebula_infinite_finish');
								ga('send', 'event', 'Error', 'AJAX Error', 'Infinite Query Load More AJAX');
								nv('increment', 'ajax_error');
							},
							timeout: 60000
						});
					}
					return false;
				});
			});
		</script>
		<?php
	}

	//Check if business hours exist in Nebula Options
	public function has_business_hours(){
		foreach ( array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday') as $weekday ){
			if ( $this->get_option('business_hours_' . $weekday . '_enabled') || $this->get_option('business_hours_' . $weekday . '_open') || $this->get_option('business_hours_' . $weekday . '_close') ){
				return true;
			}
		}
		return false;
	}

	//Check if the requested datetime is within business hours.
	//If $general is true this function returns true if the business is open at all on that day
	public function is_business_open($date=null, $general=false){ return $this->business_open($date, $general); }
	public function is_business_closed($date=null, $general=false){ return !$this->business_open($date, $general); }
	public function business_open($date=null, $general=false){
		$override = apply_filters('pre_business_open', null, $date, $general);
		if ( isset($override) ){return;}

		if ( empty($date) || $date == 'now' ){
			$date = time();
		} elseif ( strtotime($date) ){
			$date = strtotime($date . ' ' . date('g:ia', strtotime('now')));
		}
		$today = strtolower(date('l', $date));

		$businessHours = array();
		foreach ( array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday') as $weekday ){
			$businessHours[$weekday] = array(
				'enabled' => $this->get_option('business_hours_' . $weekday . '_enabled'),
				'open' => $this->get_option('business_hours_' . $weekday . '_open'),
				'close' => $this->get_option('business_hours_' . $weekday . '_close')
			);
		}

		$days_off = array_filter(explode(', ', $this->get_option('business_hours_closed')));
		if ( !empty($days_off) ){
			foreach ( $days_off as $key => $day_off ){
				$days_off[$key] = strtotime($day_off . ' ' . date('Y', $date));

				if ( date('N', $days_off[$key]) == 6 ){ //If the date is a Saturday
					$days_off[$key] = strtotime(date('F j, Y', $days_off[$key]) . ' -1 day');
				} elseif ( date('N', $days_off[$key]) == 7 ){ //If the date is a Sunday
					$days_off[$key] = strtotime(date('F j, Y', $days_off[$key]) . ' +1 day');
				}

				if ( date('Ymd', $days_off[$key]) == date('Ymd', $date) ){
					return false;
				}
			}
		}

		if ( $businessHours[$today]['enabled'] == '1' ){ //If the Nebula Options checkmark is checked for this day of the week.
			if ( !empty($general) ){
				return true;
			}

			$openToday = date('Gi', strtotime($businessHours[$today]['open']));
			$closeToday = date('Gi', strtotime($businessHours[$today]['close'])-1); //Subtract one second to ensure midnight represents the same day
			if ( date('Gi', $date) >= $openToday && date('Gi', $date) <= $closeToday ){
				return true;
			}
		}

		return false;
	}

	//If the business is open, return the time that the business closes today
	public function business_open_until($day){
		if ( empty($day) ){
			$day = strtolower(date('l'));
		}

		if ( $this->is_business_open() ){
			return $this->get_option('business_hours_' . $day . '_close');
		}

		return false;
	}

	//Get the relative time of day
	public function relative_time($format=null){
		$override = apply_filters('pre_nebula_relative_time', null, $format);
		if ( isset($override) ){return;}

		if ( $this->contains(date('H'), array('00', '01', '02')) ){
			$relative_time = array(
				'description' => array('early', 'night'),
				'standard' => array(0, 1, 2),
				'military' => array(0, 1, 2),
				'ampm' => 'am'
			);
		} elseif ( $this->contains(date('H'), array('03', '04', '05')) ){
			$relative_time = array(
				'description' => array('late', 'night'),
				'standard' => array(3, 4, 5),
				'military' => array(3, 4, 5),
				'ampm' => 'am'
			);
		} elseif ( $this->contains(date('H'), array('06', '07', '08')) ){
			$relative_time = array(
				'description' => array('early', 'morning'),
				'standard' => array(6, 7, 8),
				'military' => array(6, 7, 8),
				'ampm' => 'am'
			);
		} elseif ( $this->contains(date('H'), array('09', '10', '11')) ){
			$relative_time = array(
				'description' => array('late', 'morning'),
				'standard' => array(9, 10, 11),
				'military' => array(9, 10, 11),
				'ampm' => 'am'
			);
		} elseif ( $this->contains(date('H'), array('12', '13', '14')) ){
			$relative_time = array(
				'description' => array('early', 'afternoon'),
				'standard' => array(12, 1, 2),
				'military' => array(12, 13, 14),
				'ampm' => 'pm'
			);
		} elseif ( $this->contains(date('H'), array('15', '16', '17')) ){
			$relative_time = array(
				'description' => array('late', 'afternoon'),
				'standard' => array(3, 4, 5),
				'military' => array(15, 16, 17),
				'ampm' => 'pm'
			);
		} elseif ( $this->contains(date('H'), array('18', '19', '20')) ){
			$relative_time = array(
				'description' => array('early', 'evening'),
				'standard' => array(6, 7, 8),
				'military' => array(18, 19, 20),
				'ampm' => 'pm'
			);
		} elseif ( $this->contains(date('H'), array('21', '22', '23')) ){
			$relative_time = array(
				'description' => array('late', 'evening'),
				'standard' => array(9, 10, 11),
				'military' => array(21, 22, 23),
				'ampm' => 'pm'
			);
		}

		if ( !empty($format) ){
			return $relative_time[$format];
		} else {
			return $relative_time;
		}
	}

	//Detect location from IP address using https://freegeoip.io/
	public function ip_location($data=null, $ip_address=false){
		if ( $this->get_option('ip_geolocation') ){
			if ( empty($ip_address) ){
				$ip_address = $_SERVER['REMOTE_ADDR'];

				if ( empty($data) ){
					return true; //If passed with no parameters, simply check if Nebula Option is enabled
				}
			}

			//Check cache first
			$ip_geo_data = wp_cache_get('nebula_ip_geolocation_' . str_replace('.', '_', $ip_address));
			if ( empty($ip_geo_data) ){
				//Check session next
				if ( !empty($_SESSION['nebula_ip_geolocation']) ){
					$ip_geo_data = $_SESSION['nebula_ip_geolocation'];
				}

				//Get new remote data
				if ( empty($_SESSION['nebula_ip_geolocation']) ){
					$response = $this->remote_get('http://freegeoip.io/json/' . $ip_address);
					if ( is_wp_error($response) || !is_array($response) || strpos($response['body'], 'Rate limit') === 0 ){
						return false;
					}

					$ip_geo_data = $response['body'];
					$_SESSION['nebula_ip_geolocation'] = $ip_geo_data;
				}

				wp_cache_set('nebula_ip_geolocation_' . str_replace('.', '_', $ip_address), $ip_geo_data); //Cache the result
			}

			if ( !empty($ip_geo_data) ){
				$ip_geo_data = json_decode($ip_geo_data);
				if ( !empty($ip_geo_data) ){
					switch ( str_replace(array(' ', '_', '-'), '', $data) ){
						case 'all':
						case 'object':
						case 'response':
							return $ip_geo_data;
						case 'country':
						case 'countryname':
							return $ip_geo_data->country_name;
							break;
						case 'countrycode':
							return $ip_geo_data->country_code;
							break;
						case 'region':
						case 'state':
						case 'regionname':
						case 'statename':
							return $ip_geo_data->region_name;
							break;
						case 'regioncode':
						case 'statecode':
							return $ip_geo_data->country_code;
							break;
						case 'city':
							return $ip_geo_data->city;
							break;
						case 'zip':
						case 'zipcode':
							return $ip_geo_data->zip_code;
							break;
						case 'lat':
						case 'latitude':
							return $ip_geo_data->latitude;
							break;
						case 'lng':
						case 'longitude':
							return $ip_geo_data->longitude;
							break;
						case 'geo':
						case 'coordinates':
							return $ip_geo_data->latitude . ',' . $ip_geo_data->longitude;
							break;
						case 'timezone':
							return $ip_geo_data->time_zone;
							break;
						default:
							return false;
							break;
					}
				}
			}
		}

		return false;
	}

	//Detect weather for Zip Code (using Yahoo! Weather)
	//https://developer.yahoo.com/weather/
	public function weather($zipcode=null, $data=''){
		if ( $this->get_option('weather') ){
			$override = apply_filters('pre_nebula_weather', null, $zipcode, $data);
			if ( isset($override) ){return;}

			if ( !empty($zipcode) && is_string($zipcode) && !ctype_digit($zipcode) ){ //ctype_alpha($zipcode)
				$data = $zipcode;
				$zipcode = $this->get_option('postal_code', '13204');
			} elseif ( empty($zipcode) ){
				$zipcode = $this->get_option('postal_code', '13204');
			}

			$weather_json = get_transient('nebula_weather_' . $zipcode);
			if ( empty($weather_json) ){ //No ?debug option here (because multiple calls are made to this function). Clear with a force true when needed.
				$yql_query = 'select * from weather.forecast where woeid in (select woeid from geo.places(1) where text=' . $zipcode . ')';

				$response = $this->remote_get('http://query.yahooapis.com/v1/public/yql?q=' . urlencode($yql_query) . '&format=json');
				if ( is_wp_error($response) ){
					trigger_error('A Yahoo Weather API error occurred. Yahoo may be down, or forecast for ' . $zipcode . ' may not exist.', E_USER_WARNING);
					return false;
				}

				$weather_json = $response['body'];
				set_transient('nebula_weather_' . $zipcode, $weather_json, MINUTE_IN_SECONDS*30); //30 minute expiration
			}
			$weather_json = json_decode($weather_json);

			if ( !$weather_json || empty($weather_json) || empty($weather_json->query->results) ){
				trigger_error('A Yahoo Weather API error occurred. Yahoo may be down, or forecast for ' . $zipcode . ' may not exist.', E_USER_WARNING);
				return false;
			} elseif ( $data == '' ){
				return true;
			}

			switch ( str_replace(' ', '', $data) ){
				case 'json':
					return $weather_json;
					break;
				case 'reported':
				case 'build':
				case 'lastBuildDate':
					return $weather_json->query->results->channel->lastBuildDate;
					break;
				case 'city':
					return $weather_json->query->results->channel->location->city;
					break;
				case 'state':
				case 'region':
					return $weather_json->query->results->channel->location->region;
					break;
				case 'country':
					return $weather_json->query->results->channel->location->country;
					break;
				case 'location':
					return $weather_json->query->results->channel->location->city . ', ' . $weather_json->query->results->channel->location->region;
					break;
				case 'latitude':
				case 'lat':
					return $weather_json->query->results->channel->item->lat;
					break;
				case 'longitude':
				case 'long':
				case 'lng':
					return $weather_json->query->results->channel->item->long;
					break;
				case 'geo':
				case 'geolocation':
				case 'coordinates':
					return $weather_json->query->results->channel->item->lat . ',' . $weather_json->query->results->channel->item->lat;
					break;
				case 'windchill':
				case 'chill':
					return $weather_json->query->results->channel->wind->chill;
					break;
				case 'windspeed':
					return $weather_json->query->results->channel->wind->speed;
					break;
				case 'sunrise':
					return $weather_json->query->results->channel->astronomy->sunrise;
					break;
				case 'sunset':
					return $weather_json->query->results->channel->astronomy->sunset;
					break;
				case 'temp':
				case 'temperature':
					return $weather_json->query->results->channel->item->condition->temp;
					break;
				case 'condition':
				case 'conditions':
				case 'current':
				case 'currently':
					return $weather_json->query->results->channel->item->condition->text;
					break;
				case 'forecast':
					return $weather_json->query->results->channel->item->forecast;
					break;
				default:
					break;
			}
		}

		return false;
	}

	//Print the PHG logo as text with or without hover animation.
	public function pinckney_hugo_group($anim){ $this->pinckneyhugogroup($anim); }
	public function phg($anim){ $this->pinckneyhugogroup($anim); }
	public function pinckneyhugogroup($anim=false, $white=false){
		if ( $anim ){
			$anim = 'anim';
		}
		if ( $white ){
			$white = 'anim';
		}
		return '<a class="phg ' . $anim . ' ' . $white . '" href="http://www.pinckneyhugo.com/" target="_blank" rel="noopener"><span class="pinckney">Pinckney</span><span class="hugo">Hugo</span><span class="group">Group</span></a>';
	}

	//Determine if the author should be the Company Name or the specific author's name.
	public function the_author($show_authors=1){
		$override = apply_filters('pre_nebula_the_author', null, $show_authors);
		if ( isset($override) ){return;}

		if ( !is_single() || $show_authors == 0 || !$this->get_option('author_bios') ){
			return $this->get_option('site_owner', get_bloginfo('name'));
		} else {
			return ( get_the_author_meta('first_name') != '' )? get_the_author_meta('first_name') . ' ' . get_the_author_meta('last_name') : get_the_author_meta('display_name');
		}
	}

	//Register Widget Areas
	public function widgets_register(){
		$override = apply_filters('pre_nebula_widgets_init', null);
		if ( isset($override) ){return;}

		//Sidebar
		register_sidebar(array(
			'name' => 'Sidebar',
			'id' => 'sidebar-widget-area',
			'description' => 'The sidebar widget area',
			'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
			'after_widget' => '</li>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>',
		));

		//Footer
		register_sidebar(array(
			'name' => 'Footer',
			'id' => 'footer-widget-area',
			'description' => 'The horizontal footer widget area',
			'before_widget' => '<div id="%1$s" class="col-md widget-container %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>',
		));
	}

	//Register the Navigation Menus
	public function nav_menu_locations(){
		$override = apply_filters('pre_nebula_nav_menu_locations', null);
		if ( isset($override) ){return;}

		register_nav_menus(array(
			'utility' => 'Utility Menu',
			'primary' => 'Primary Menu',
			'mobile' => 'Mobile Menu',
			'footer' => 'Footer Menu'
		));
	}

	//Remove the Activity metabox
	public function remove_activity_metabox(){
		remove_meta_box('dashboard_activity', 'dashboard', 'normal');
	}

	//Remove Comments column
	public function remove_pages_count_columns($defaults){
		unset($defaults['comments']);
		return $defaults;
	}

	//Remove comments menu from Admin Bar
	public function admin_bar_remove_comments($wp_admin_bar){
		$wp_admin_bar->remove_menu('comments');
	}

	//Remove comments metabox and comments
	public function disable_comments_admin(){
		remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
		//Note: Do not remove the Discussion settings page. The comment blacklist is still used for other things like CF7 forms.
	}

	public function hide_ataglance_comment_counts(){
		echo '<style>li.comment-count, li.comment-mod-count {display: none;}</style>'; //Hide comment counts in "At a Glance" metabox
	}

	//Disable support for comments in post types
	public function remove_comments_post_type_support(){
		foreach ( get_post_types() as $post_type ){
			if ( post_type_supports($post_type, 'comments') ){
				remove_post_type_support($post_type, 'comments');
			}
		}
	}

	//Link to Disqus on comments page (if using Disqus)
	public function disqus_link(){
		echo "<div class='nebula_admin_notice notice notice-info'><p>You are using the Disqus commenting system. <a href='https://" . $this->get_option('disqus_shortname') . ".disqus.com/admin/moderate' target='_blank' rel='noopener'>View the comment listings on Disqus &raquo;</a></p></div>";
	}

	//Enqueue threaded comments script only as needed
	public function enqueue_comments_reply(){
		if ( get_option('thread_comments') ){
			wp_enqueue_script('comment-reply');
		}
	}

	//Disable support for trackbacks in post types
	public function disable_trackbacks(){
		$post_types = get_post_types();
		foreach ( $post_types as $post_type ){
			remove_post_type_support($post_type, 'trackbacks');
		}
	}

	//Prefill form fields with comment author cookie
	public function comment_author_cookie(){
		echo '<script>';
		if ( isset($_COOKIE['comment_author_' . COOKIEHASH]) ){
			$commentAuthorName = $_COOKIE['comment_author_' . COOKIEHASH];
			$commentAuthorEmail = $_COOKIE['comment_author_email_' . COOKIEHASH];
			echo 'cookieAuthorName = "' . $commentAuthorName . '";';
			echo 'cookieAuthorEmail = "' . $commentAuthorEmail . '";';
		} else {
			echo 'cookieAuthorName = "";';
			echo 'cookieAuthorEmail = "";';
		}
		echo '</script>';
	}

	//Twitter cached feed
	public function twitter_cache($username='Great_Blakes', $listname=null, $numbertweets=5, $includeretweets=1){
		if ( $_POST['data'] ){
			if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }
			$username = ( $_POST['data']['username'] )? sanitize_text_field($_POST['data']['username']) : 'Great_Blakes';
			$listname = ( $_POST['data']['listname'] )? sanitize_text_field($_POST['data']['listname']) : null; //Only used for list feeds
			$numbertweets = ( $_POST['data']['numbertweets'] )? sanitize_text_field($_POST['data']['numbertweets']) : 5;
			$includeretweets = ( $_POST['data']['includeretweets'] )? sanitize_text_field($_POST['data']['includeretweets']) : 1; //1: Yes, 0: No
		}

		error_reporting(0); //Prevent PHP errors from being cached.

		if ( $listname ){
			$feed = 'https://api.twitter.com/1.1/lists/statuses.json?slug=' . $listname . '&owner_screen_name=' . $username . '&count=' . $numbertweets . '&include_rts=' . $includeretweets;
		} else {
			$feed = 'https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=' . $username . '&count=' . $numbertweets . '&include_rts=' . $includeretweets;
		}

		$bearer = $this->get_option('twitter_bearer_token', '');

		$tweets = get_transient('nebula_twitter_' . $username);
		if ( empty($tweets) || $this->is_debug() ){
			$args = array('headers' => array('Authorization' => 'Bearer ' . $bearer));

			$response = $this->remote_get($feed, $args);
			if ( is_wp_error($response) ){
				return false;
			}

			$tweets = $response['body'];

			if ( !$tweets ){
				echo false;
				exit;
			}

			set_transient('nebula_twitter_' . $username, $tweets, MINUTE_IN_SECONDS*5); //5 minute expiration
		}

		if ( $_POST['data'] ){
			echo $tweets;
			wp_die();
		} else {
			return $tweets;
		}
	}

	//Replace text on password protected posts to be more minimal
	public function password_form_simplify(){
		$output  = '<form class="ignore-form" action="' . esc_url(site_url('wp-login.php?action=postpass', 'login_post')) . '" method="post">
						<span>Password: </span>
						<input type="password" class="ignore-form" name="post_password" size="20" />
						<input type="submit" name="Submit" value="Go" />
					</form>';
		return $output;
	}

	//Always get custom fields with post queries
	public function always_get_post_custom($posts){
		for ( $i = 0; $i < count($posts); $i++ ){
			$custom_fields = get_post_custom($posts[$i]->ID);
			$posts[$i]->custom_fields = $custom_fields;
		}
		return $posts;
	}

	//Prevent empty search query error (Show all results instead)
	public function redirect_empty_search($query){
		global $wp_query;
		if ( isset($_GET['s']) && $wp_query->query && !array_key_exists('invalid', $_GET) ){
			if ( $_GET['s'] == '' && $wp_query->query['s'] == '' && !$this->is_admin_page() ){
				if ( !nebula()->is_bot() && strpos(nebula()->ga_parse_cookie(), '.') !== false ){ //If it isn't a bot and the GA CID is actually from the GA cookie (not generated by Nebula)
					ga_send_event('Internal Search', 'Invalid', '(Empty query)');
				}

				header('Location: ' . home_url('/') . 'search/?invalid');
				exit;
			} else {
				return $query;
			}
		}
	}

	//Redirect if only single search result
	public function redirect_single_post(){
		if ( is_search() ){
			global $wp_query;
			if ( $wp_query->post_count == 1 && $wp_query->max_num_pages == 1 ){
				if ( isset($_GET['s']) ){
					//If the redirected post is the homepage, serve the regular search results page with one result (to prevent a redirect loop)
					if ( $wp_query->posts['0']->ID != 1 && get_permalink($wp_query->posts['0']->ID) != home_url() . '/' ){

						if ( !nebula()->is_bot() && strpos(nebula()->ga_parse_cookie(), '.') !== false ){ //If it isn't a bot and the GA CID is actually from the GA cookie (not generated by Nebula)
							$this->ga_send_event('Internal Search', 'Single Result Redirect', $_GET['s']);
						}

						$_GET['s'] = str_replace(' ', '+', $_GET['s']);
						wp_redirect(get_permalink($wp_query->posts['0']->ID ) . '?rs=' . $_GET['s']);
						exit;
					}
				} else {
					if ( !nebula()->is_bot() && strpos(nebula()->ga_parse_cookie(), '.') !== false ){ //If it isn't a bot and the GA CID is actually from the GA cookie (not generated by Nebula)
						$this->ga_send_event('Internal Search', 'Single Result Redirect');
					}

					wp_redirect(get_permalink($wp_query->posts['0']->ID) . '?rs');
					exit;
				}
			}
		}
	}

	//Autocomplete Search AJAX.
	public function autocomplete_search(){
		if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }

		ini_set('memory_limit', '256M'); //@TODO "Nebula" 0: Ideally this would not be here.

		$term = sanitize_text_field(trim($_POST['data']['term']));
		if ( empty($term) ){
			return false;
			exit;
		}

		$types = array('any');
		if ( isset($_POST['types']) ){
			$types = json_decode(sanitize_text_field(trim($_POST['types'])));
		}

		//Standard WP search (does not include custom fields)
		$query1 = new WP_Query(array(
			'post_type' => $types,
			'post_status' => 'publish',
			'posts_per_page' => 4,
			's' => $term,
		));

		//Search custom fields
		$query2 = new WP_Query(array(
			'post_type' => $types,
			'post_status' => 'publish',
			'posts_per_page' => 4,
			'meta_query' => array(
				array(
					'value' => $term,
					'compare' => 'LIKE'
				)
			)
		));

		//Combine the above queries
		$autocomplete_query = new WP_Query();
		$autocomplete_query->posts = array_unique(array_merge($query1->posts, $query2->posts), SORT_REGULAR);
		$autocomplete_query->post_count = count($autocomplete_query->posts);

		//Loop through the posts
		if ( $autocomplete_query->have_posts() ){
			while ( $autocomplete_query->have_posts() ){
				$autocomplete_query->the_post();
				if ( !get_the_title() ){ //Ignore results without titles
					continue;
				}
				$post = get_post();

				$suggestion = array();
				similar_text(strtolower($term), strtolower(get_the_title()), $suggestion['similarity']); //Determine how similar the query is to this post title
				$suggestion['label'] = get_the_title();
				$suggestion['link'] = get_permalink();

				$suggestion['classes'] = 'type-' . get_post_type() . ' id-' . get_the_id() . ' slug-' . $post->post_name . ' similarity-' . str_replace('.', '_', number_format($suggestion['similarity'], 2));
				if ( get_the_id() == get_option('page_on_front') ){
					$suggestion['classes'] .= ' page-home';
				} elseif ( is_sticky() ){ //@TODO "Nebula" 0: If sticky post. is_sticky() does not work here?
					$suggestion['classes'] .= ' sticky-post';
				}
				$suggestion['classes'] .= $this->close_or_exact($suggestion['similarity']);
				$suggestions[] = $suggestion;
			}
		}

		//Find media library items
		if ( $this->in_array_any(array('any', 'attachment'), $types) ){
			$attachments = get_posts(array('post_type' => 'attachment', 's' => $term, 'numberposts' => 10, 'post_status' => null));
			if ( $attachments ){
				$attachment_count = 0;
				foreach ( $attachments as $attachment ){
					if ( strpos(get_attachment_link($attachment->ID), '?attachment_id=') ){ //Skip if media item is not associated with a post.
						continue;
					}
					$suggestion = array();
					$attachment_meta = wp_get_attachment_metadata($attachment->ID);
					$path_parts = pathinfo($attachment_meta['file']);
					$attachment_search_meta = ( get_the_title($attachment->ID) != '' )? get_the_title($attachment->ID) : $path_parts['filename'];
					similar_text(strtolower($term), strtolower($attachment_search_meta), $suggestion['similarity']);
					if ( $suggestion['similarity'] >= 50 ){
						$suggestion['label'] = ( get_the_title($attachment->ID) != '' )? get_the_title($attachment->ID) : $path_parts['basename'];
						$suggestion['classes'] = 'type-attachment file-' . $path_parts['extension'];
						$suggestion['classes'] .= $this->close_or_exact($suggestion['similarity']);
						if ( in_array(strtolower($path_parts['extension']), array('jpg', 'jpeg', 'png', 'gif', 'bmp')) ){
							$suggestion['link'] = get_attachment_link($attachment->ID);
						} else {
							$suggestion['link'] = wp_get_attachment_url($attachment->ID);
							$suggestion['external'] = true;
							$suggestion['classes'] .= ' external-link';
						}
						$suggestion['similarity'] = $suggestion['similarity']-0.001; //Force lower priority than posts/pages.
						$suggestions[] = $suggestion;
						$attachment_count++;
					}
					if ( $attachment_count >= 2 ){
						break;
					}
				}
			}
		}

		//Find menu items
		if ( $this->in_array_any(array('any', 'menu'), $types) ){
			$menus = get_transient('nebula_autocomplete_menus');
			if ( empty($menus) || $this->is_debug() ){
				$menus = get_terms('nav_menu');
				set_transient('nebula_autocomplete_menus', $menus, WEEK_IN_SECONDS); //This transient is deleted when a post is updated or Nebula Options are saved.
			}
			foreach($menus as $menu){
				$menu_items = wp_get_nav_menu_items($menu->term_id);
				foreach ( $menu_items as $key => $menu_item ){
					$suggestion = array();
					similar_text(strtolower($term), strtolower($menu_item->title), $menu_title_similarity);
					similar_text(strtolower($term), strtolower($menu_item->attr_title), $menu_attr_similarity);
					if ( $menu_title_similarity >= 65 || $menu_attr_similarity >= 65 ){
						if ( $menu_title_similarity >= $menu_attr_similarity ){
							$suggestion['similarity'] = $menu_title_similarity;
							$suggestion['label'] = $menu_item->title;
						} else {
							$suggestion['similarity'] = $menu_attr_similarity;
							$suggestion['label'] = $menu_item->attr_title;
						}
						$suggestion['link'] = $menu_item->url;
						$path_parts = pathinfo($menu_item->url);
						$suggestion['classes'] = 'type-menu-item';
						if ( $path_parts['extension'] ){
							$suggestion['classes'] .= ' file-' . $path_parts['extension'];
							$suggestion['external'] = true;
						} elseif ( !strpos($suggestion['link'], $this->url_components('domain')) ){
							$suggestion['classes'] .= ' external-link';
							$suggestion['external'] = true;
						}
						$suggestion['classes'] .= $this->close_or_exact($suggestion['similarity']);
						$suggestion['similarity'] = $suggestion['similarity']-0.001; //Force lower priority than posts/pages.
						$suggestions[] = $suggestion;
						break;
					}
				}
			}
		}

		//Find categories
		if ( $this->in_array_any(array('any', 'category', 'cat'), $types) ){
			$categories = get_transient('nebula_autocomplete_categories');
			if ( empty($categories) || $this->is_debug() ){
				$categories = get_categories();
				set_transient('nebula_autocomplete_categories', $categories, WEEK_IN_SECONDS); //This transient is deleted when a post is updated or Nebula Options are saved.
			}
			foreach ( $categories as $category ){
				$suggestion = array();
				$cat_count = 0;
				similar_text(strtolower($term), strtolower($category->name), $suggestion['similarity']);
				if ( $suggestion['similarity'] >= 65 ){
					$suggestion['label'] = $category->name;
					$suggestion['link'] = get_category_link($category->term_id);
					$suggestion['classes'] = 'type-category';
					$suggestion['classes'] .= $this->close_or_exact($suggestion['similarity']);
					$suggestions[] = $suggestion;
					$cat_count++;
				}
				if ( $cat_count >= 2 ){
					break;
				}
			}
		}

		//Find tags
		if ( $this->in_array_any(array('any', 'tag'), $types) ){
			$tags = get_transient('nebula_autocomplete_tags');
			if ( empty($tags) || $this->is_debug() ){
				$tags = get_tags();
				set_transient('nebula_autocomplete_tags', $tags, WEEK_IN_SECONDS); //This transient is deleted when a post is updated or Nebula Options are saved.
			}
			foreach ( $tags as $tag ){
				$suggestion = array();
				$tag_count = 0;
				similar_text(strtolower($term), strtolower($tag->name), $suggestion['similarity']);
				if ( $suggestion['similarity'] >= 65 ){
					$suggestion['label'] = $tag->name;
					$suggestion['link'] = get_tag_link($tag->term_id);
					$suggestion['classes'] = 'type-tag';
					$suggestion['classes'] .= $this->close_or_exact($suggestion['similarity']);
					$suggestions[] = $suggestion;
					$tag_count++;
				}
				if ( $tag_count >= 2 ){
					break;
				}
			}
		}

		//Find authors (if author bios are enabled)
		if ( $this->get_option('author_bios') && $this->in_array_any(array('any', 'author'), $types) ){
			$authors = get_transient('nebula_autocomplete_authors');
			if ( empty($authors) || $this->is_debug() ){
				$authors = get_users(array('role' => 'author')); //@TODO "Nebula" 0: This should get users who have made at least one post. Maybe get all roles (except subscribers) then if postcount >= 1?
				set_transient('nebula_autocomplete_authors', $authors, WEEK_IN_SECONDS); //This transient is deleted when a post is updated or Nebula Options are saved.
			}
			foreach ( $authors as $author ){
				$author_name = ( $author->first_name != '' )? $author->first_name . ' ' . $author->last_name : $author->display_name; //might need adjusting here
				if ( strtolower($author_name) == strtolower($term) ){ //todo: if similarity of author name and query term is higher than X. Return only 1 or 2.
					$suggestion = array();
					$suggestion['label'] = $author_name;
					$suggestion['link'] = 'http://google.com/';
					$suggestion['classes'] = 'type-user';
					$suggestion['classes'] .= $this->close_or_exact($suggestion['similarity']);
					$suggestion['similarity'] = ''; //todo: save similarity to array too
					$suggestions[] = $suggestion;
					break;
				}
			}
		}

		if ( sizeof($suggestions) >= 1 ){
			//Order by match similarity to page title (DESC).
			function autocomplete_similarity_compare($a, $b){
				return $b['similarity'] - $a['similarity'];
			}
			usort($suggestions, "autocomplete_similarity_compare");

			//Remove any duplicate links (higher similarity = higher priority)
			$outputArray = array(); //This array is where unique results will be stored
			$keysArray = array(); //This array stores values to check duplicates against.
			foreach ( $suggestions as $suggestion ){
				if ( !in_array($suggestion['link'], $keysArray) ){
					$keysArray[] = $suggestion['link'];
					$outputArray[] = $suggestion;
				}
			}
		}

		//Link to search at the end of the list
		//@TODO "Nebula" 0: The empty result is not working for some reason... (Press Enter... is never appearing)
		$suggestion = array();
		$suggestion['label'] = ( sizeof($suggestions) >= 1 )? '...more results for "' . $term . '"' : 'Press enter to search for "' . $term . '"';
		$suggestion['link'] = home_url('/') . '?s=' . str_replace(' ', '%20', $term);
		$suggestion['classes'] = ( sizeof($suggestions) >= 1 )? 'more-results search-link' : 'no-results search-link';
		$outputArray[] = $suggestion;

		echo json_encode($outputArray, JSON_PRETTY_PRINT);
		wp_die();
	}

	//Test for close or exact matches. Use: $suggestion['classes'] .= $this->close_or_exact($suggestion['similarity']); //Rename this function
	public function close_or_exact($rating=0, $close_threshold=80, $exact_threshold=95){
		if ( $rating > $exact_threshold ){
			return ' exact-match';
		} elseif ( $rating > $close_threshold ){
			return ' close-match';
		}
			return '';
		}

	//Advanced Search
	public function advanced_search(){
		if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }

		ini_set('memory_limit', '512M'); //Increase memory limit for this script.

		$everything_query = get_transient('nebula_everything_query');
		if ( empty($everything_query) ){
			$everything_query = new WP_Query(array(
				'post_type' => array('any'),
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'nopaging' => true
			));
			set_transient('nebula_everything_query', $everything_query, WEEK_IN_SECONDS); //This transient is deleted when a post is updated or Nebula Options are saved.
		}
		$posts = $everything_query->get_posts();

		foreach ( $posts as $post ){
			$author = null;
			if ( $this->get_option('author_bios') ){ //&& $post->post_type != 'page' ?
				$author = array(
					'id' => $post->post_author,
					'name' => array(
						'first' => get_the_author_meta('first_name', $post->post_author),
						'last' => get_the_author_meta('last_name', $post->post_author),
						'display' => get_the_author_meta('display_name', $post->post_author),
					),
					'url' => get_author_posts_url($post->post_author),
				);
			}

			$these_categories = array();
			$event_categories = get_the_category($post->ID);
			foreach ( $event_categories as $event_category ){
				$these_categories[] = $event_category->name;
			}

			$these_tags = array();
			$event_tags = wp_get_post_tags($post->ID);
			foreach ( $event_tags as $event_tag ){
				$these_tags[] = $event_tag->name;
			}

			$custom_fields = array();
			foreach ( $post->custom_fields as $custom_field => $custom_value ){
				if ( substr($custom_field, 0, 1) == '_' ){
					continue;
				}
				$custom_fields[$custom_field] = $custom_value[0];
			}

			$full_size = wp_get_attachment_image_src($post->_thumbnail_id, 'full');
			$thumbnail = wp_get_attachment_image_src($post->_thumbnail_id, 'thumbnail');

			$output[] = array(
				'type' => $post->post_type,
				'id' => $post->ID,
				'posted' => strtotime($post->post_date),
				'modified' => strtotime($post->post_modified),
				'author' => $author,
				'title' => $post->post_title,
				'description' => strip_tags($post->post_content), //@TODO "Nebula" 0: not correct!
				'url' => get_the_permalink($post->ID),
				'categories' => $these_categories,
				'tags' => $these_tags,
				'image' => array(
					'full' => $thumbnail[0], //@TODO "Nebula" 0: Update to shorthand array after PHP v5.4 is common
					'thumbnail' => $full_size[0], //@TODO "Nebula" 0: Update to shorthand array after PHP v5.4 is common
				),
				'custom' => $custom_fields,
			);
		} //END $posts foreach

		//@TODO "Nebula" 0: if going to sort by text:
		/*
			usort($output, function($a, $b){
				return strcmp($a['title'], $b['title']);
			});
		*/

		//@TODO "Nebula" 0: If going to sort by number:
		/*
			usort($output, function($a, $b){
				return $a['posted'] - $b['posted'];
			});
		*/

		echo json_encode($output, JSON_PRETTY_PRINT);
		wp_die();
	}

	//Infinite Load AJAX Call
	public function infinite_load(){
		if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }

		$page_number = sanitize_text_field($_POST['page']);
		$args = $_POST['args'];
		$args['paged'] = $page_number;
		$loop = sanitize_text_field($_POST['loop']);

		$args = array_map('esc_attr', $args); //Sanitize the args array
		query_posts($args);

		if ( $loop == 'false' ){
			get_template_part('loop');
		} else {
			call_user_func($loop); //Custom loop callback function must be defined in a functions file (not a template file) for this to work.
		}

		wp_die();
	}

	//404 page suggestions
	public function internal_suggestions(){
		if ( is_404() ){
			global $slug_keywords;
			$slug_keywords = array_filter(explode('/', $this->url_components('filepath')));
			$slug_keywords = end($slug_keywords);

			global $error_query;
			$error_query = new WP_Query(array('post_status' => 'publish', 'posts_per_page' => 4, 's' => str_replace('-', ' ', $slug_keywords)));
			if ( is_plugin_active('relevanssi/relevanssi.php') ){
				relevanssi_do_query($error_query);
			}

			if ( !empty($error_query->posts) && $slug_keywords == $error_query->posts[0]->post_name ){
				global $error_404_exact_match;
				$error_404_exact_match = $error_query->posts[0];
			}
		}
	}

	//Add custom body classes
	public function body_classes($classes){
		$spaces_and_dots = array(' ', '.');
		$underscores_and_hyphens = array('_', '-');

		//Device
		$classes[] = strtolower($this->get_device('formfactor')); //Form factor (desktop, tablet, mobile)
		$classes[] = strtolower($this->get_device('full')); //Device make and model
		$classes[] = strtolower(str_replace($spaces_and_dots, $underscores_and_hyphens, $this->get_os('full'))); //Operating System name with version
		$classes[] = strtolower(str_replace($spaces_and_dots, $underscores_and_hyphens, $this->get_os('name'))); //Operating System name
		$classes[] = strtolower(str_replace($spaces_and_dots, $underscores_and_hyphens, $this->get_browser('full'))); //Browser name and version
		$classes[] = strtolower(str_replace($spaces_and_dots, $underscores_and_hyphens, $this->get_browser('name'))); //Browser name
		$classes[] = strtolower(str_replace($spaces_and_dots, $underscores_and_hyphens, $this->get_browser('engine'))); //Rendering engine

		//When installed to the homescreen, Chrome is detected as "Chrome Mobile". Supplement it with a "chrome" class.
		if ( $this->get_browser('name') == 'Chrome Mobile' ){
			$classes[] = 'chrome';
		}

		//IE versions outside conditional comments
		if ( $this->is_browser('ie') ){
			if ( $this->is_browser('ie', '10') ){
				$classes[] = 'ie';
				$classes[] = 'ie10';
				$classes[] = 'lte-ie10';
				$classes[] = 'lt-ie11';
			} elseif ( $this->is_browser('ie', '11') ){
				$classes[] = 'ie';
				$classes[] = 'ie11';
				$classes[] = 'lte-ie11';
			}
		}

		//User Information
		$current_user = wp_get_current_user();
		if ( is_user_logged_in() ){
			$classes[] = 'user-logged-in';
			$classes[] = 'user-' . $current_user->user_login;
			$user_info = get_userdata(get_current_user_id());
			if ( !empty($user_info->roles) ){
				$classes[] = 'user-role-' . $user_info->roles[0];
			} else {
				$classes[] = 'user-role-unknown';
			}
		} else {
			$classes[] = 'user-not-logged-in';
		}

		//Post Information
		if ( !is_search() && !is_archive() && !is_front_page() ){
			global $post;
			$segments = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
			$parents = get_post_ancestors($post->ID);
			foreach ( $parents as $parent ){
				if ( !empty($parent) ){
					$classes[] = 'ancestor-id-' . $parent;
				}
			}
			foreach ( $segments as $segment ){
				if ( !empty($segment) ){
					$classes[] = 'ancestor-of-' . $segment;
				}
			}
			foreach ( get_the_category($post->ID) as $category ){
				$classes[] = 'cat-id-' . $category->cat_ID;
			}
		}

		//Homepage Hero (Customizer)
		if ( is_front_page() && !get_theme_mod('nebula_hero', true) ){
			$classes[] = 'no-hero';
		}

		$nebula_theme_info = wp_get_theme();
		$classes[] = 'nebula';
		$classes[] = 'nebula_' . str_replace('.', '-', $this->version('primary'));

		$classes[] = 'lang-' . strtolower(get_bloginfo('language'));
		if ( is_rtl() ){
			$classes[] = 'lang-dir-rtl';
		}

		//Time of Day
		if ( $this->has_business_hours() ){
			$classes[] = ( $this->business_open() )? 'business-open' : 'business-closed';
		}

		$relative_time = $this->relative_time('description');
		foreach( $relative_time as $relative_desc ){
			$classes[] = 'time-' . $relative_desc;
		}
		if ( date('H') >= 12 ){
			$classes[] = 'time-pm';
		} else {
			$classes[] = 'time-am';
		}

		if ( $this->get_option('latitude') && $this->get_option('longitude') ){
			$lat = $this->get_option('latitude');
			$lng = $this->get_option('longitude');
			$gmt = intval(get_option('gmt_offset'));
			$zenith = 90+50/60; //Civil twilight = 96°, Nautical twilight = 102°, Astronomical twilight = 108°
			global $sunrise, $sunset;
			$sunrise = strtotime(date_sunrise(strtotime('today'), SUNFUNCS_RET_STRING, $lat, $lng, $zenith, $gmt));
			$sunset = strtotime(date_sunset(strtotime('today'), SUNFUNCS_RET_STRING, $lat, $lng, $zenith, $gmt));
			$length_of_daylight = $sunset-$sunrise;
			$length_of_darkness = 86400-$length_of_daylight;

			if ( time() >= $sunrise && time() <= $sunset ){
				$classes[] = 'time-daylight';
				if ( strtotime('now') < $sunrise+($length_of_daylight/2) ){
					$classes[] = 'time-waxing-gibbous'; //Before solar noon
					$classes[] = ( strtotime('now') < ($length_of_daylight/4)+$sunrise )? 'time-narrow' : 'time-wide';
				} else {
					$classes[] = 'time-waning-gibbous'; //After solar noon
					$classes[] = ( strtotime('now') < ((3*$sunset)+$sunrise)/4 )? 'time-wide' : 'time-narrow';
				}
			} else {
				$classes[] = 'time-darkness';
				$previous_sunset_modifier = ( date('H') < 12 )? 86400 : 0; //Times are in UTC, so if it is after actual midnight (before noon) we need to use the sunset minus 1 day in formulas
				$solar_midnight = (($sunset-$previous_sunset_modifier)+($length_of_darkness/2)); //Calculate the appropriate solar midnight (either yesterday's or tomorrow's) [see above]
				if ( strtotime('now') < $solar_midnight ){
					$classes[] = 'time-waning-crescent'; //Before solar midnight
					$classes[] = ( strtotime('now') < ($length_of_darkness/4)+($sunset-$previous_sunset_modifier) )? 'time-wide' : 'time-narrow';
				} else {
					$classes[] = 'time-waxing-crescent'; //After solar midnight
					$classes[] = ( strtotime('now') < ($sunrise+$solar_midnight)/2 )? 'time-narrow' : 'time-wide';
				}
			}

			$sunrise_sunset_length = 35; //Length of sunrise/sunset in minutes.
			if ( strtotime('now') >= $sunrise-(60*$sunrise_sunset_length) && strtotime('now') <= $sunrise+(60*$sunrise_sunset_length) ){ //X minutes before and after true sunrise
				$classes[] = 'time-sunrise';
			}
			if ( strtotime('now') >= $sunset-(60*$sunrise_sunset_length) && strtotime('now') <= $sunset+(60*$sunrise_sunset_length) ){ //X minutes before and after true sunset
				$classes[] = 'time-sunset';
			}
		}

		$classes[] = 'date-day-' . strtolower(date('l'));
		$classes[] = 'date-ymd-' . strtolower(date('Y-m-d'));
		$classes[] = 'date-month-' . strtolower(date('F'));

		return $classes;
	}

	//Add additional classes to post wrappers
	public function post_classes($classes){
		global $post;
		global $wp_query;

		if ( $wp_query->current_post === 0 ){ //If first post in a query
			$classes[] = 'first-post';
		}
		if ( is_sticky() ){
			$classes[] = 'sticky';
		}
		$classes[] = 'nebula-entry';

		if ( !is_page() ){
			$classes[] = 'date-day-' . strtolower(get_the_date('l'));
			$classes[] = 'date-ymd-' . strtolower(get_the_date('Y-m-d'));
			$classes[] = 'date-month-' . strtolower(get_the_date('F'));
		}

		if ( !empty($post) ){
			foreach ( get_the_category($post->ID) as $category ){
				$classes[] = 'cat-id-' . $category->cat_ID;
			}
		}

		$classes[] = 'author-id-' . $post->post_author;

		//Remove "hentry" meta class on pages or if Author Bios are disabled
		if ( is_page() || !$this->get_option('author_bios') ){
			$classes = array_diff($classes, array('hentry'));
		}

		return $classes;
	}

	//G1 Screen Reader Skip to Content Link https://www.w3.org/TR/WCAG20-TECHS/G1
	public function skip_to_content_link(){
		echo '<a class="skip-to-content-link sr-only" href="#content-section" tabindex="0">Skip to Content</a>';
	}

	//Make sure attachment URLs match the protocol (to prevent mixed content warnings).
	public function wp_get_attachment_url_force_protocol($url){
		$http = site_url(false, 'http');
		$https = site_url(false, 'https');

		if ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ){
			return str_replace($http, $https, $url);
		} else {
			return $url;
		}
	}

	//Fix responsive oEmbeds
	//Uses Bootstrap classes: http://v4-alpha.getbootstrap.com/components/utilities/#responsive-embeds
	public function oembed_modifiers($html, $url, $attr, $post_id){
		//Enable the JS API for Youtube videos
		if ( strstr($html, 'youtube.com/embed/') ){
			$html = str_replace('feature=oembed', 'feature=oembed&enablejsapi=1&rel=0', $html);
		}

		//Force an aspect ratio on certain oEmbeds
		if ( strpos($html, 'youtube') !== false || strpos($html, 'vimeo') !== false ){
			$html = '<div class="nebula-oembed-wrapper embed-responsive embed-responsive-16by9">' . $html . '</div>';
		} elseif ( strpos($html, 'vine') !== false ){
			$html = '<div class="nebula-oembed-wrapper embed-responsive embed-responsive-1by1" style="max-width: 710px; max-height: 710px;">' . $html . '</div>';
		}

		return $html;
	}
}