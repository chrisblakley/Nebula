<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

trait Functions {
	public $twitter_widget_loaded;
	public $linkedin_widget_loaded;
	public $pinterest_widget_loaded;

	public function hooks(){
		global $pagenow;

		$this->twitter_widget_loaded = false;
		$this->linkedin_widget_loaded = false;
		$this->pinterest_widget_loaded = false;

		add_action('after_setup_theme', array($this, 'theme_setup'));
		add_filter('site_icon_image_sizes', array($this, 'site_icon_sizes'));
		add_filter('image_size_names_choose', array($this, 'image_size_human_names'));
		add_action('rest_api_init', array($this, 'rest_api_routes'));
		add_action('wp_head', array($this, 'add_back_post_feed'));
		add_action('init', array($this, 'set_default_timezone'), 1);

		if ( $this->get_option('console_css') ){
			add_action('wp_head', array($this, 'calling_card'));
		}

		add_action('wp_head', array($this, 'console_warnings'));

		if ( is_writable(get_template_directory()) ){
			if ( !file_exists($this->manifest_json_location(false)) || filemtime($this->manifest_json_location(false)) > (time()-DAY_IN_SECONDS) || $this->is_debug() ){
				add_action('init', array($this, 'manifest_json'));
			}
		}

		if ( $this->get_option('service_worker') && is_writable(get_home_path()) ){
			if ( file_exists($this->sw_location(false)) ){
				add_action('save_post', array($this, 'update_sw_js'));
			}
		}

		add_action('wp_loaded', array($this, 'favicon_cache'));
		add_action('nebula_head_open', array($this, 'google_optimize_style'));
		add_action('after_setup_theme', array($this, 'nav_menu_locations'));
		add_filter('nav_menu_link_attributes', array($this, 'add_menu_attributes'), 10, 3);

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

			if ( $pagenow === 'edit-comments.php' && $this->get_option('disqus_shortname') ){
				add_action('admin_notices', array($this, 'disqus_link'));
			}
		} else { //If WP core comments are enabled
			add_action('comment_form_before', array($this, 'enqueue_comments_reply'));
			add_action('wp_head', array($this, 'comment_author_cookie'));
		}

		add_action('admin_init', array($this, 'disable_trackbacks'));
		add_action('template_include', array($this, 'define_current_template'), 1000);
		add_action('wp_ajax_nebula_twitter_cache', array($this, 'twitter_cache'));
		add_action('wp_ajax_nopriv_nebula_twitter_cache', array($this, 'twitter_cache'));
		add_filter('get_search_form', array($this, 'search_form'), 100, 1);
		add_filter('the_password_form', array($this, 'password_form_simplify'));
		add_filter('the_posts', array($this, 'always_get_post_custom'));
		add_action('pre_get_posts', array($this, 'redirect_empty_search'));
		add_action('template_redirect', array($this, 'redirect_single_search_result'));

		add_action('wp_head', array($this, 'arbitrary_code_head'), 1000);
		add_action('nebula_body_open', array($this, 'arbitrary_code_body'), 1000);
		add_action('wp_footer', array($this, 'arbitrary_code_footer'), 1000);

		add_filter('single_template', array($this, 'single_category_template'));

		add_action('wp_ajax_nebula_infinite_load', array($this, 'infinite_load'));
		add_action('wp_ajax_nopriv_nebula_infinite_load', array($this, 'infinite_load'));

		add_action('wp_head', array($this, 'internal_suggestions'));
		add_filter('body_class', array($this, 'body_classes'));
		add_filter('post_class', array($this, 'post_classes'));
		add_action('nebula_body_open', array($this, 'skip_to_content_link'));
		add_filter('wp_get_attachment_url', array($this, 'wp_get_attachment_url_force_protocol'));
		add_filter('embed_oembed_html', array($this, 'oembed_modifiers'), 9999, 4);

		add_filter('acf/settings/google_api_key', array($this, 'acf_google_api_key')); //ACF hook
		add_filter('wpseo_metadesc', array($this, 'meta_description')); //Yoast hook
		add_filter('wpseo_twitter_card_type', array($this, 'allow_large_twitter_summary'), 10, 2); //Yoast hook

		if ( is_user_logged_in() ){
			add_filter('wpcf7_verify_nonce', '__return_true'); //Always verify CF7 nonce for logged-in users (this allows for it to detect user data)
		}
		add_filter('wpcf7_form_elements', array($this, 'cf7_autocomplete_attribute'));
		add_filter('wpcf7_special_mail_tags', array($this, 'cf7_custom_special_mail_tags'), 10, 3);

		add_action('shutdown', array($this, 'flush_rewrite_on_debug'));
	}

	//Check if the Nebula Companion plugin is installed and active
	public function is_companion_active(){
		include_once ABSPATH . 'wp-admin/includes/plugin.php'; //Needed to use is_plugin_active() outside of WP admin
		if ( is_plugin_active('nebula-companion/nebula-companion.php') || is_plugin_active('Nebula-Companion-master/nebula-companion.php') ){
			return true;
		}

		return false;
	}

	//Prep custom theme support
	public function theme_setup(){
		//Additions
		add_theme_support('post-thumbnails');
		add_theme_support('custom-logo'); //Custom logo support.
		add_theme_support('title-tag'); //Title tag support allows WordPress core to create the <title> tag.
		//add_theme_support('html5', array('comment-list', 'comment-form', 'search-form', 'gallery', 'caption'));
		add_theme_support('automatic-feed-links'); //Add default posts and comments RSS feed links to head

		add_theme_support('responsive-embeds');
		add_theme_support('wp-block-styles');
		add_theme_support('align-wide'); //Wide image alignment

		//Custom color palette to Gutenberg editor
		add_theme_support('editor-color-palette', array(
			array(
				'name' => 'Primary',
				'slug' => 'primary',
				'color' => get_theme_mod('nebula_primary_color', $this->get_color('$primary_color')),
			),
			array(
				'name' => 'Secondary',
				'slug' => 'secondary',
				'color' => get_theme_mod('nebula_secondary_color', $this->get_color('$secondary_color')),
			)
		));

		add_post_type_support('page', 'excerpt'); //Allow pages to have excerpts too

		header("X-UA-Compatible: IE=edge"); //Add IE compatibility header
		header('Developed-with-Nebula: https://gearside.com/nebula'); //Nebula header

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

		//Add new image sizes (Given human-readible names in another function below)
		//"max_size" custom image size is defined in /libs/Optimization.php
		add_image_size('square', 512, 512, 1);
		add_image_size('open_graph_large', 1200, 630, 1);
		add_image_size('open_graph_small', 600, 315, 1);
		add_image_size('twitter_large', 280, 150, 1);
		add_image_size('twitter_small', 200, 200, 1);
	}

	//Give custom Nebula image sizes human readable names
	public function image_size_human_names($sizes){
		return array_merge($sizes, array(
			'square' => 'Square',
			'open_graph_large' => 'Open Graph (Large)',
			'open_graph_small' => 'Open Graph (Small)',
			'twitter_large' => 'Twitter (Large)',
			'twitter_small' => 'Twitter (Small)',
		));
	}

	//Add custom meta icon (favicon) sizes when the site_icon is used via the Customizer
	public function site_icon_sizes($core_sizes){
		$nebula_sizes = array(16, 32, 70, 150, 180, 192, 310);
		$all_sizes = array_unique(array_merge($core_sizes, $nebula_sizes));
		return $all_sizes;
	}

	//Register REST API routes/endpoints
	public function rest_api_routes(){
		register_rest_route('nebula/v2', '/autocomplete_search/', array('methods' => 'GET', 'callback' => array($this, 'rest_autocomplete_search'))); //.../wp-json/nebula/v2/autocomplete_search?term=whatever&types=post|page
	}

	//Add the Posts RSS Feed back in
	public function add_back_post_feed(){
		echo '<link rel="alternate" type="application/rss+xml" title="RSS 2.0 Feed" href="' . get_bloginfo('rss2_url') . '" />';
	}

	//Set server timezone to match Wordpress
	public function set_default_timezone(){
		//@todo "Nebula" 0: Use null coalescing operator here if possible
		$timezone_option = get_option('timezone_string');
		if ( empty($timezone_option) ){
			$timezone_option = 'America/New_York';
		}

		date_default_timezone_set($timezone_option);
	}

	//Add the Nebula note to the browser console (if enabled)
	public function calling_card(){
		if ( (!$this->get_option('device_detection') || ($this->is_desktop() && !$this->is_browser('ie') && !$this->is_browser('edge'))) && !is_customize_preview() ){
			echo "<script>console.log('%c Created using Nebula " . esc_html($this->version('primary')) . "', 'padding: 2px 10px; background: #0098d7; color: #fff;');</script>";
		}
	}

	//Check for Nebula warnings
	public function check_warnings(){
		$this->timer('Check Warnings');

		if ( (current_user_can('manage_options') || $this->is_dev()) && $this->get_option('admin_notices') && !is_customize_preview() ){
			//Check object cache first
			$nebula_warnings = wp_cache_get('nebula_warnings');

			if ( is_array($nebula_warnings) || !empty($nebula_warnings) ){ //If it is an array (meaning it has run before but did not find anything) or if it is false
				return $nebula_warnings;
			}

			$nebula_warnings = array();

			//Admin warnings only
			if ( $this->is_admin_page() ){
				//Check page slug against taxonomy terms.
				global $pagenow;
				if ( $pagenow === 'post.php' || $pagenow === 'edit.php' ){
					global $post;

					if ( !empty($post) ){ //If the listing has results
						foreach ( get_taxonomies() as $taxonomy ){ //Loop through all taxonomies
							foreach ( get_terms($taxonomy, array('hide_empty' => false)) as $term ){ //Loop through all terms within each taxonomy
								if ( $term->slug === $post->post_name ){
									$nebula_warnings[] = array(
										'level' => 'error',
										'description' => 'Slug conflict with ' . ucwords(str_replace('_', ' ', $taxonomy)) . ': <strong>' . $term->slug . '</strong> - Consider changing this page slug.'
									);
									return false;
								}
							}
						}
					}
				}

				//Test the WordPress filesystem method
				$fs_method_transient = get_transient('nebula_fs_method');
				if ( empty($fs_method_transient) || $this->is_debug() ){
					if ( file_exists(get_template_directory() . '/style.css') ){
						WP_Filesystem();
						global $wp_filesystem;
						$test_file = $wp_filesystem->get_contents(get_template_directory() . '/style.css');

						if ( empty($test_file) ){
							$nebula_warnings[] = array(
								'level' => 'error',
								'description' => 'File system permissions error. Consider changing the FS_METHOD in wp-config.php.',
							);
						} else {
							set_transient('nebula_fs_method', true, YEAR_IN_SECONDS); //On success, set a transient. This transient never needs to expire (but it's fine if it does).
						}
					}
				}
			}

			//If the site is served via HTTPS but the Site URL is still set to HTTP
			if ( (is_ssl() || isset($_SERVER['HTTPS'])) && (strpos(get_option('home'), 'http://') !== false || strpos(get_option('siteurl'), 'http://') !== false) ){
				$nebula_warnings[] = array(
					'level' => 'error',
					'description' => '<a href="options-general.php">Website Address</a> settings are http but the site is served from https.',
					'url' => get_admin_url() . 'options-general.php'
				);
			}

			//If search indexing is disabled
			if ( get_option('blog_public') == 0 ){ //Stored as a string
				$nebula_warnings[] = array(
					'level' => 'error',
					'description' => '<a href="options-reading.php">Search Engine Visibility</a> is currently disabled!',
					'url' => get_admin_url() . 'options-reading.php'
				);
			} else {
				//If not pinging additional update services (blog must be public for this to be available)
				$ping_sites = get_option('ping_sites');
				if ( empty($ping_sites) || $ping_sites === 'http://rpc.pingomatic.com/' ){ //If it is empty or only has the default value
					$nebula_warnings[] = array(
						'level' => 'warn',
						'description' => 'Additional <a href="options-writing.php">Update Services</a> should be pinged. <a href="https://codex.wordpress.org/Update_Services#XML-RPC_Ping_Services" target="_blank" rel="noopener">Recommended update services &raquo;</a>',
						'url' => get_admin_url() . 'options-writing.php'
					);
				}
			}

			//Check PHP version
			$php_version_lifecycle = $this->php_version_support();
			if ( $php_version_lifecycle['lifecycle'] === 'security' ){
				if ( $php_version_lifecycle['end']-time() < MONTH_IN_SECONDS ){ //If end of life is within 1 month
					$nebula_warnings[] = array(
						'level' => 'warn',
						'description' => 'PHP <strong>' . PHP_VERSION . '</strong> <a href="http://php.net/supported-versions.php" target="_blank" rel="noopener">is nearing end of life</a>. Security updates end in ' . human_time_diff($php_version_lifecycle['end']) . ' on ' . date_i18n('F j, Y', $php_version_lifecycle['end']) . '.',
						'url' => 'http://php.net/supported-versions.php',
						'meta' => array('target' => '_blank', 'rel' => 'noopener')
					);
				}
			} elseif ( $php_version_lifecycle['lifecycle'] === 'end' ){
				$nebula_warnings[] = array(
					'level' => 'error',
					'description' => 'PHP ' . PHP_VERSION . ' <a href="http://php.net/supported-versions.php" target="_blank" rel="noopener">no longer receives security updates</a>! End of life occurred ' . human_time_diff($php_version_lifecycle['end']) . ' ago on ' . date_i18n('F j, Y', $php_version_lifecycle['end']) . '.',
					'url' => 'http://php.net/supported-versions.php',
					'meta' => array('target' => '_blank', 'rel' => 'noopener')
				);
			}

			//Check for hard Debug Mode
			if ( WP_DEBUG ){
				$nebula_warnings[] = array(
					'level' => 'warn',
					'description' => '<strong>WP_DEBUG</strong> is enabled <small>(Generally defined in wp-config.php)</small>'
				);

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
			}

			//Check for Google Analytics Tracking ID
			if ( !$this->get_option('ga_tracking_id') && !$this->get_option('gtm_id') ){
				$nebula_warnings[] = array(
					'level' => 'error',
					'description' => 'A <a href="themes.php?page=nebula_options&tab=analytics&option=ga_tracking_id">Google Analytics tracking ID</a> or <a href="themes.php?page=nebula_options&tab=analytics&option=gtm_id">Google Tag Manager ID</a> is strongly recommended!',
					'url' => get_admin_url() . 'themes.php?page=nebula_options&tab=analytics'
				);
			}

			//If Enhanced Ecommerce Plugin is missing Google Analytics Tracking ID
			if ( is_plugin_active('enhanced-e-commerce-for-woocommerce-store/woocommerce-enhanced-ecommerce-google-analytics-integration.php') ){
				$ee_ga_settings = get_option('woocommerce_enhanced_ecommerce_google_analytics_settings');
				if ( empty($ee_ga_settings['ga_id']) ){
					$nebula_warnings[] = array(
						'level' => 'error',
						'description' => '<a href="admin.php?page=wc-settings&tab=integration">WooCommerce Enhanced Ecommerce</a> is missing a Google Analytics ID!',
						'url' => get_admin_url() . 'admin.php?page=wc-settings&tab=integration'
					);
				}
			}

			//Child theme checks
			if ( is_child_theme() ){
				//Check if the parent theme template is correctly referenced
				$active_theme = wp_get_theme();
				if ( !file_exists(dirname(get_stylesheet_directory()) . '/' . $active_theme->get('Template')) ){
					$nebula_warnings[] = array(
						'level' => 'error',
						'description' => 'A child theme is active, but its parent theme directory <strong>' . $active_theme->get('Template') . '</strong> does not exist!<br/><em>The "Template:" setting in the <a href="' . get_stylesheet_uri() . '" target="_blank" rel="noopener">style.css</a> file of the child theme must match the directory name (above) of the parent theme.</em>'
					);
				}

				//Check if child theme is active, but missing img meta files
				if ( !is_dir(get_stylesheet_directory() . '/assets/img/meta') || !file_exists(get_stylesheet_directory() . '/assets/img/meta/favicon.ico') ){
					$nebula_warnings['child_meta_graphics'] = array(
						'level' => 'error',
						'description' => 'A child theme is active, but missing meta graphics. Create a <code>/assets/img/meta/</code> directory in the child theme (or copy it over from the Nebula parent theme).</em>'
					);
				}
			}

			//Check if Relevanssi has built an index for search
			if ( is_plugin_active('relevanssi/relevanssi.php') && !get_option('relevanssi_indexed') ){
				$nebula_warnings[] = array(
					'level' => 'error',
					'description' => '<a href="options-general.php?page=relevanssi%2Frelevanssi.php&tab=indexing">Relevanssi</a> must build an index to search the site. This must be triggered manually.',
					'url' => get_admin_url() . 'options-general.php?page=relevanssi%2Frelevanssi.php&tab=indexing'
				);
			}

			//Check if Google Optimize is enabled. This alert is because the Google Optimize style snippet will add a whitescreen effect during loading and should be disabled when not actively experimenting.
			if ( $this->get_option('google_optimize_id') ){
				$nebula_warnings[] = array(
					'level' => 'error',
					'description' => '<a href="https://optimize.google.com/optimize/home/" target="_blank" rel="noopener">Google Optimize</a> is enabled (via <a href="themes.php?page=nebula_options&tab=analytics&option=google_optimize_id">Nebula Options</a>). Disable when not actively experimenting!',
					'url' => 'https://optimize.google.com/optimize/home/'
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
					'description' => '<a href="options-general.php">Site Tagline</a> is still "Just Another WordPress Site"!',
					'url' => get_admin_url() . 'options-general.php'
				);
			}

			//Check if all SCSS files were processed manually.
			if ( $this->get_option('scss') && (isset($_GET['sass']) || isset($_GET['scss'])) ){
				if ( $this->is_dev() || $this->is_client() ){
					$nebula_warnings[] = array(
						'level' => 'log',
						'description' => 'All Sass files have been manually processed.'
					);
				} else {
					$nebula_warnings[] = array(
						'level' => 'error',
						'description' => 'You do not have permissions to manually process all Sass files.'
					);
				}
			}

			$all_nebula_warnings = apply_filters('nebula_warnings', $nebula_warnings); //Allow other functions to hook in to add warnings (like Ecommerce)

			//Check for improper hooks
			if ( is_null($all_nebula_warnings) ){
				$all_nebula_warnings = array(array(
					'level' => 'error',
					'description' => '<code>$nebula_warnings</code> array is null. When hooking into the <code>nebula_warnings</code> filter be sure that it is returned too!'
				));
			}

			//Sort by warning level
			if ( !empty($all_nebula_warnings) ){
				usort($all_nebula_warnings, function($itemA, $itemB){
					$priorities = array('error', 'warn', 'log');
					$a = array_search($itemA['level'], $priorities);
					$b = array_search($itemB['level'], $priorities);

					if ( $a === $b ){
						return 0;
					}

					return ( $a < $b )? -1 : 1;
				});
			}

			wp_cache_set('nebula_warnings', $all_nebula_warnings); //Store in object cache

			$this->timer('Check Warnings', 'end');
			return $all_nebula_warnings;
		}

		return array(); //Return empty array instead of false
	}

	//Log warnings in the console
	public function console_warnings($console_warnings=array()){
		if ( (current_user_can('manage_options') || $this->is_dev()) && $this->get_option('admin_notices') && !is_customize_preview() ){
			$warnings = $this->check_warnings();

			//If there are warnings, send them to the console.
			if ( !empty($warnings) ){
				echo '<script>';
				foreach( $warnings as $warning ){
					$category = ( !empty($warning['category']) )? $warning['category'] : 'Nebula';
					echo 'console.' . esc_html($warning['level']) . '("[' . esc_html($category) . '] ' . esc_html(addslashes(strip_tags($warning['description']))) . '");';
				}
				echo '</script>';
			}
		}
	}

	//Get the location URI of the Service Worker JavaScript file.
	//Override this in your child theme if changing the location or filename of the service worker.
	public function sw_location($uri=true){
		$override = apply_filters('pre_sw_location', null, $uri);
		if ( isset($override) ){return $override;}

		if ( !empty($uri) ){
			return get_site_url() . '/sw.js';
		}

		return get_home_path() . 'sw.js';
	}

	//Update variables within the service worker JavaScript file for install caching
	public function update_sw_js(){
		$this->timer('Update SW');

		$override = apply_filters('pre_nebula_update_swjs', null);
		if ( isset($override) ){return;}

		WP_Filesystem();
		global $wp_filesystem;
		$sw_js = $wp_filesystem->get_contents($this->sw_location(false));

		if ( !empty($sw_js) ){
			$find = array(
				"/(var THEME_NAME = ')(.+)(';)/m",
				"/(var NEBULA_VERSION = ')(.+)(';)(.+$)?/m",
				"/(var OFFLINE_URL = ')(.+)(';)/m",
				"/(var OFFLINE_IMG = ')(.+)(';)/m",
				"/(var OFFLINE_GA_DIMENSION = ')(.+)(';)/m",
				"/(var META_ICON = ')(.+)(';)/m",
				"/(var MANIFEST = ')(.+)(';)/m",
				"/(var HOME_URL = ')(.+)(';)/m",
				"/(var START_URL = ')(.+)(';)/m",
			);

			//$new_cache_name = "nebula-" . strtolower(get_option('stylesheet')) . "-" . mt_rand(10000, 99999);

			$replace = array(
				"$1" . strtolower(get_option('stylesheet')) . "$3",
				"$1" . 'v' . $this->version('full') . "$3 //" . date('l, F j, Y g:i:s A'),
				"$1" . home_url('/') . "offline/$3",
				"$1" . get_theme_file_uri('/assets/img') . "/offline.svg$3",
				"$1cd" . $this->ga_definition_index($this->get_option('cd_offline')) . "$3",
				"$1" . get_theme_file_uri('/assets/img/meta') . "/android-chrome-512x512.png$3",
				"$1" . $this->manifest_json_location() . "$3",
				"$1" . home_url('/') . "$3",
				"$1" . home_url('/') . "?utm_source=pwa$3",
			);

			$sw_js = preg_replace($find, $replace, $sw_js);
			$update_sw_js = $wp_filesystem->put_contents($this->sw_location(false), $sw_js);
			do_action('nebula_wrote_sw_js');
		}

		$this->timer('Update SW', 'end');
		return false;
	}

	//Manifest JSON file location
	public function manifest_json_location($uri=true){
		$override = apply_filters('pre_manifest_json_location', null, $uri);
		if ( isset($override) ){return $override;}

		if ( !empty($uri) ){
			return get_theme_file_uri('/inc/manifest.json');
		}

		return get_theme_file_path('/inc/manifest.json');
	}

	//Create/Write a manifest JSON file
	public function manifest_json(){
		$timer_name = $this->timer('Write Manifest JSON', 'start', 'Manifest');

		$override = apply_filters('pre_nebula_manifest_json', null);
		if ( isset($override) ){return;}

		$manifest_json = '{
			"name": "' . get_bloginfo('name') . ': ' . get_bloginfo('description') . '",
			"short_name": "' . get_bloginfo('name') . '",
			"description": "' . get_bloginfo('description') . '",
			"theme_color": "' . get_theme_mod('nebula_primary_color', $this->get_color('$primary_color')) . '",
			"background_color": "' . get_theme_mod('nebula_background_color', $this->get_color('$background_color')) . '",
			"gcm_sender_id": "' . $this->get_option('gcm_sender_id') . '",
			"scope": "/",
			"start_url": "' . home_url('/') . '?utm_source=homescreen",
			"display": "standalone",
			"orientation": "portrait",
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
				$dimensions = getimagesize($file); //Considering adding an @ to ignore notices when getimagesize fails
				if ( !empty($dimensions) ){
					$manifest_json .= '{
						"src": "' . get_theme_file_uri('/assets/img/meta') . '/' . $filename . '",
						"sizes": "' . $dimensions[0] . 'x' . $dimensions[1] . '",
						"type": "image/png"
					}, ';
				}
			}
		}

		$manifest_json = rtrim($manifest_json, ', ') . ']}';

		WP_Filesystem();
		global $wp_filesystem;
		$wp_filesystem->put_contents($this->manifest_json_location(false), $manifest_json);
		$this->timer($timer_name, 'end');
	}

	//Redirect to favicon to force-clear the cached version when ?favicon is added to the URL.
	public function favicon_cache(){
		if ( array_key_exists('favicon', $_GET) ){
			header('Location: ' . get_theme_file_uri('/assets/img/meta') . '/favicon.ico');
			exit;
		}
	}

	//Google Optimize Style Tag
	public function google_optimize_style(){
		if ( $this->get_option('google_optimize_id') ){ ?>
			<style>.async-hide {opacity: 0 !important} </style>
			<script>(function(a,s,y,n,c,h,i,d,e){s.className+=' '+y;h.end=i=function(){
			s.className=s.className.replace(RegExp(' ?'+y),'')};(a[n]=a[n]||[]).hide=h;
			setTimeout(function(){i();h.end=null},c);})(window,document.documentElement,
			'async-hide','dataLayer',2000,{'<?php echo $this->get_option('google_optimize_id'); ?>':true,});</script>
		<?php }
	}

	//Convenience function to return only the URL for specific thumbnail sizes of an ID.
	public function get_thumbnail_src($img=null, $size='full', $type='post'){
		if ( empty($img) ){
			return false;
		}

		//If HTML is passed, immediately parse it with HTML
		if ( strpos($img, '<img') !== false ){
			return ( preg_match('~\bsrc="([^"]++)"~', $img, $matches) )? $matches[1] : ''; //Pull the img src from the HTML tag itself
		}

		$id = intval($img); //Can now use the ID

		//If and ID was not passed, immediately return it (in case it is already an image URL)
		if ( $id === 0 || ($id === 1 && $img != 1) ){
			return $img;
		}

		$size = apply_filters('nebula_thumbnail_src_size', $size, $id);

		//If an attachment ID (or thumbnail ID) was passed
		if ( get_post_type($id) === 'attachment' || $type !== 'post' ){
			$image = wp_get_attachment_image_src(get_post_thumbnail_id($id), $size);

			if ( !empty($image[0]) ){
				return $image[0];
			}
		}

		//Otherwise get the HTML from the post ID (or if the attachment src did not work above)
		$img_tag = get_the_post_thumbnail($id, $size);
		if ( get_post_type($id) === 'attachment' ){
			$img_tag = wp_get_attachment_image($id, $size);
		}

		return ( preg_match('~\bsrc="([^"]++)"~', $img_tag, $matches) )? $matches[1] : ''; //Pull the img src from the HTML tag itself
	}

	//Sets the current post/page template to a variable.
	function define_current_template($template){
		$this->current_theme_template = str_replace(ABSPATH . 'wp-content', '', $template);
		return $template;
	}

	//Show different meta data information about the post. Typically used inside the loop.
	//Example: post_meta('by');
	public function post_meta($meta, $options=array()){
		$override = apply_filters('pre_post_meta', null, $meta, $options);
		if ( isset($override) ){return;}

		if ( $meta === 'date' || $meta === 'time' || $meta === 'on' || $meta === 'day' || $meta === 'when' ){
			echo $this->post_date($options);
		} elseif ( $meta === 'author' || $meta === 'by' ){
			echo $this->post_author($options);
		} elseif ( $meta === 'type' || $meta === 'cpt' || $meta === 'post_type' ){
			echo $this->post_type($options);
		} elseif ( $meta === 'categories' || $meta === 'category' || $meta === 'cat' || $meta === 'cats' || $meta === 'in' ){
			echo $this->post_categories($options);
		} elseif ( $meta === 'tags' || $meta === 'tag' ){
			echo $this->post_tags($options);
		} elseif ( $meta === 'dimensions' || $meta === 'size' ){
			echo $this->post_dimensions($options);
		} elseif ( $meta === 'exif' || $meta === 'camera' ){
			echo $this->post_exif($options);
		} elseif ( $meta === 'comments' || $meta === 'comment' ){
			echo $this->post_comments($options);
		} elseif ( $meta === 'social' || $meta === 'sharing' || $meta === 'share' ){
			$this->social(array('facebook', 'twitter', 'linkedin', 'pinterest'), 0);
		}
	}

	//Date post meta
	public function post_date($options=array()){
		$defaults = apply_filters('nebula_post_date_defaults', array(
			'label' => 'icon', //"icon" or "text"
			'type' => 'published', //"published", or "modified"
			'relative' => get_theme_mod('post_date_format'),
			'linked' => true,
			'day' => true,
			'format' => 'F j, Y',
		));

		$data = array_merge($defaults, $options);

		if ( $data['relative'] === 'disabled' ){
			return false;
		}

		//Apply the requested label
		$label = '';
		if ( $data['label'] == 'icon' ){
			$label = '<i class="nebula-post-date-label far fa-fw fa-calendar"></i> ';
		} elseif ( $data['label'] == 'text' ){
			$label = '<span class="nebula-post-date-label">' . esc_html(ucwords($data['type'])) . ' </span>';
		}

		//Use the publish or modified date per options
		$the_date = get_the_date('U');
		$modified_date_html = '';
		if ( $data['type'] === 'modified' ){
			$the_date = get_the_modified_date('U');
		}

		$relative_date = human_time_diff($the_date) . ' ago';

		if ( $data['relative'] ){
			return '<span class="posted-on meta-item post-date relative-date" title="' . date_i18n('F j, Y', $the_date) . '">' . $label . $relative_date . $modified_date_html . '</span>';
		}

		$day = ( $data['day'] )? date('d', $the_date) . '/' : ''; //If the day should be shown (otherwise, just month and year).

		if ( $data['linked'] && !isset($options['format']) ){
			return '<span class="posted-on meta-item post-date">' . $label . '<span class="entry-date" datetime="' . date('c', $the_date) . '" itemprop="datePublished" content="' . date('c', $the_date) . '"><a href="' . home_url('/') . date('Y/m', $the_date) . '/">' . date_i18n('F', $the_date) . '</a> <a href="' . home_url('/') . date('Y/m', $the_date) . '/' . $day . '">' . date('j', $the_date) . '</a>, <a href="' . home_url('/') . date('Y', $the_date) . '/">' . date('Y', $the_date) . '</a></span>' . $modified_date_html . '</span>';
		} else {
			return '<span class="posted-on meta-item post-date">' . $label . '<span class="entry-date" datetime="' . date('c', $the_date) . '" itemprop="datePublished" content="' . date('c', $the_date) . '">' . date_i18n($data['format'], $the_date) . '</span>' . $modified_date_html . '</span>';
		}
	}

	//Author post meta
	public function post_author($options=array()){
		$defaults = apply_filters('nebula_post_author_defaults', array(
			'label' => 'icon', //"icon" or "text"
			'linked' => true, //Link to author page
			'force' => false, //Override author_bios Nebula option
		));

		$data = array_merge($defaults, $options);

		//@todo "Nebula" 0: Include support for multi-authors: is_multi_author

		if ( ($this->get_option('author_bios') || $data['force']) && get_theme_mod('post_author', true) ){
			$label = '';
			if ( $data['label'] === 'icon' ){
				$label = '<i class="nebula-post-author-label fas fa-fw fa-user"></i> ';
			} elseif ( $data['label'] === 'text' ){
				$label = '<span class="nebula-post-author-label">Author </span>';
			}

			//Get the author metadata
			$author_id = get_the_author_meta('ID');
			if ( empty($author_id) ){ //Author ID can be empty outside of the loop
				global $post;
				$author_id = $post->post_author;
				$author_name = get_the_author_meta('display_name', $author_id);
			} else {
				$author_name = get_the_author();
			}

			if ( $data['linked'] && !$data['force'] ){
				return '<span class="posted-by" itemprop="author" itemscope itemtype="https://schema.org/Person">' . $label . '<span class="meta-item entry-author"><a href="' . get_author_posts_url($author_id) . '" itemprop="name">' . $author_name . '</a></span></span>';
			} else {
				return '<span class="posted-by" itemprop="author" itemscope itemtype="https://schema.org/Person">' . $label . '<span class="meta-item entry-author" itemprop="name">' . esc_html($author_name) . '</span></span>';
			}
		}
	}

	//Post type meta
	public function post_type($icon=true){
		if ( get_theme_mod('search_result_post_types', true) ){
			$post_icon_img = '<i class="fas fa-thumbtack"></i>';
			if ( $icon ){
				global $wp_post_types;
				$post_type = get_post_type();

				if ( $post_type === 'post' ){
					$post_icon_img = '<i class="fas fa-fw fa-thumbtack"></i>';
				} elseif ( $post_type === 'page' ){
					$post_icon_img = '<i class="fas fa-fw fa-file-alt"></i>';
				} else {
					$post_icon = $wp_post_types[$post_type]->menu_icon;
					if ( !empty($post_icon) ){
						if ( strpos('dashicons-', $post_icon) >= 0 ){
							$post_icon_img = '<i class="dashicons-before ' . $post_icon . '"></i>';
						} else {
							$post_icon_img = '<img src="' . $post_icon . '" style="width: 16px; height: 16px;" loading="lazy" />';
						}
					} else {
						$post_icon_img = '<i class="fas fa-thumbtack"></i>';
					}
				}
			}

			return '<span class="meta-item post-type">' . $post_icon_img . ucwords(get_post_type()) . '</span>';
		}
	}

	//Categories post meta
	public function post_cats($options=array()){return $this->post_categories($options);}
	public function post_categories($options=array()){
		$defaults = apply_filters('nebula_post_categories_defaults', array(
			'id' => get_the_ID(),
			'label' => 'icon', //"icon" or "text"
			'linked' => true, //Link to category archive
			'show_uncategorized' => true, //Show "Uncategorized" category
			'force' => false,
			'string' => false, //Return a string with no markup
		));

		$data = array_merge($defaults, $options);

		if ( get_theme_mod('post_categories', true) || $data['force'] ){
			$label = '';
			if ( $data['label'] === 'icon' ){
				$label = '<i class="nebula-post-categories-label fas fa-fw fa-bookmark"></i> ';
			} elseif ( $data['label'] === 'text' ){
				$label = '<span class="nebula-post-categories-label">' . __('Category', 'nebula') . '</span>';
			}

			if ( is_object_in_taxonomy(get_post_type(), 'category') ){
				$category_list = get_the_category_list(', ', '', $data['id']);

				if ( strip_tags($category_list) === 'Uncategorized' && !$data['show_uncategorized'] ){
					return false;
				}

				if ( !$data['linked'] ){
					$category_list = strip_tags($category_list);
				}

				if ( $data['string'] ){
					return strip_tags($category_list);
				}

				return '<span class="posted-in meta-item post-categories">' . $label . $category_list . '</span>';
			}
		}
	}

	//Tags post meta
	public function post_tags($options=array()){
		$defaults = apply_filters('nebula_post_tags_defaults', array(
			'id' => get_the_ID(),
			'label' => 'icon', //"icon" or "text"
			'linked' => true, //Link to tag archive
			'force' => false,
			'string' => false, //Return a string with no markup
		));

		$data = array_merge($defaults, $options);

		if ( get_theme_mod('post_tags', true) || $data['force'] ){
			$tag_list = get_the_tag_list('', ', ', '', $data['id']);
			if ( $tag_list ){
				$label = '';
				if ( $data['label'] === 'icon' ){
					$tag_plural = ( count(get_the_tags()) > 1 )? __('tags', 'nebula') : __('tag', 'nebula');
					$label = '<i class="nebula-post-tags-label fas fa-fw fa-' . $tag_plural . '"></i> ';
				} elseif ( $data['label'] === 'text' ){
					$label = '<span class="nebula-post-tags-label">' . ucwords($tag_plural) . ' </span>';
				}

				if ( !$data['linked'] ){
					$tag_list = strip_tags($tag_list);
				}

				if ( $data['string'] ){
					return strip_tags($tag_list);
				}

				return '<span class="posted-in meta-item post-tags">' . $label . $tag_list . '</span>';
			}
		}
	}

	//Image dimensions post meta
	public function post_dimensions($options=array()){
		if ( wp_attachment_is_image() ){
			$defaults = array(
				'icon' => true, //Show icon
				'linked' => true, //Link to attachment
			);

			$data = array_merge($defaults, $options);

			$the_icon = '';
			if ( $data['icon'] ){
				$the_icon = '<i class="fas fa-fw fa-expand"></i> ';
			}

			$metadata = wp_get_attachment_metadata();
			if ( $data['linked'] ){
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
			$the_icon = '<i class="fas fa-fw fa-camera-retro"></i> ';
		}

		$imgmeta = wp_get_attachment_metadata();
		if ( $imgmeta ){ //Check for Bad Data
			if ( $imgmeta['image_meta']['focal_length'] === 0 || $imgmeta['image_meta']['aperture'] === 0 || $imgmeta['image_meta']['shutter_speed'] === 0 || $imgmeta['image_meta']['iso'] === 0 ){
				$output = __('No valid EXIF data found', 'nebula');
			} else { //Convert the shutter speed retrieve from database to fraction
				if ( $imgmeta['image_meta']['shutter_speed'] > 0 && (1/$imgmeta['image_meta']['shutter_speed']) > 1 ){
					if ( (number_format((1/$imgmeta['image_meta']['shutter_speed']), 1)) == 1.3 || number_format((1/$imgmeta['image_meta']['shutter_speed']), 1) == 1.5 || number_format((1/$imgmeta['image_meta']['shutter_speed']), 1) == 1.6 || number_format((1/$imgmeta['image_meta']['shutter_speed']), 1) == 2.5 ){
						$pshutter = '1/' . number_format((1/$imgmeta['image_meta']['shutter_speed']), 1, '.', '') . ' ' . __('second', 'nebula');
					} else {
						$pshutter = '1/' . number_format((1/$imgmeta['image_meta']['shutter_speed']), 0, '.', '') . ' ' . __('second', 'nebula');
					}
				} else {
					$pshutter = $imgmeta['image_meta']['shutter_speed'] . ' ' . __('seconds', 'nebula');
				}

				$output = '<time datetime="' . date('c', $imgmeta['image_meta']['created_timestamp']) . '"><span class="month">' . date('F', $imgmeta['image_meta']['created_timestamp']) . '</span> <span class="day">' . date('j', $imgmeta['image_meta']['created_timestamp']) . '</span><span class="suffix">' . date('S', $imgmeta['image_meta']['created_timestamp']) . '</span> <span class="year">' . date('Y', $imgmeta['image_meta']['created_timestamp']) . '</span></time>, ';
				$output .= $imgmeta['image_meta']['camera'] . ', ';
				$output .= $imgmeta['image_meta']['focal_length'] . 'mm, ';
				$output .= '<span style="font-style: italic; font-family: "Trebuchet MS", "Candara", "Georgia", serif; text-transform: lowercase;">f</span>/' . $imgmeta['image_meta']['aperture'] . ', ';
				$output .= $pshutter . ', ';
				$output .= $imgmeta['image_meta']['iso'] . ' ISO';
			}
		} else {
			$output = __('No EXIF data found', 'nebula');
		}

		return '<span class="meta-item meta-exif">' . $the_icon . $output . '</span>';
	}

	//Comments post meta
	public function post_comments($options=array()){
		$defaults = array(
			'icon' => true, //Show icon
			'linked' => true, //Link to comment
			'empty' => true, //Show if 0 comments
			'force' => false
		);

		$data = array_merge($defaults, $options);

		if ( get_theme_mod('post_comment_count', true) || $data['force'] ){
			$comment_show = '';
			$comments_text = 'Comments';

			if ( get_comments_number() == 0 ){
				$comment_icon = 'far fa-comment';
				$comment_show = ( $data['empty'] )? '' : 'hidden'; //If comment link should show if no comments. True = show, False = hidden
			} elseif ( get_comments_number() == 1 ){
				$comment_icon = 'fas fa-comment';
				$comments_text = 'Comment';
			} elseif ( get_comments_number() > 1 ){
				$comment_icon = 'fas fa-comments';
			}

			$the_icon = '';
			if ( $data['icon'] ){
				$the_icon = '<i class="fa-fw ' . $comment_icon . '"></i> ';
			}

			if ( $data['linked'] ){
				$postlink = ( is_single() )? '' : get_the_permalink();
				return '<span class="meta-item posted-comments ' . $comment_show . '">' . $the_icon . '<a class="nebulametacommentslink" href="' . $postlink . '#nebulacommentswrapper">' . get_comments_number() . ' ' . $comments_text . '</a></span>';
			} else {
				return '<span class="meta-item posted-comments ' . $comment_show . '">' . $the_icon . get_comments_number() . ' ' . $comments_text . '</span>';
			}
		}
	}

	//Use this instead of the_excerpt(); and get_the_excerpt(); to have better control over the excerpt.
	//Inside the loop (or outside the loop for current post/page): nebula()->excerpt(array('words' => 20, 'ellipsis' => true));
	//Outside the loop: nebula()->excerpt(array('id' => 572, 'words' => 20, 'ellipsis' => true));
	//Custom text: nebula()->excerpt(array('text' => 'Lorem ipsum <strong>dolor</strong> sit amet.', 'more' => 'Continue &raquo;', 'words' => 3, 'ellipsis' => true, 'strip_tags' => true));
	public function excerpt($options=array()){
		$override = apply_filters('pre_nebula_excerpt', null, $options);
		if ( isset($override) ){return $override;}

		$defaults = apply_filters('nebula_excerpt_defaults', array(
			'id' => false,
			'text' => false,
			'paragraphs' => false, //Allow paragraph tags in the excerpt //@todo "Nebula" 0: currently not working
			'characters' => false,
			'words' => get_theme_mod('nebula_excerpt_length', 55),
			'length' => false, //Used for dynamic length, otherwise an alias of "words"
			'min' => 0, //Minimum length of dynamic sentence
			'ellipsis' => false,
			'url' => false,
			'more' => get_theme_mod('nebula_excerpt_more_text', __('Read More', 'nebula') . ' &raquo;'),
			'wp_more' => true, //Listen for the WP more tag
			'btn' => false, //Alias of "button"
			'button' => false,
			'strip_shortcodes' => true,
			'strip_tags' => true,
			'wrap_links' => false,
			'shorten_urls' => false, //Currently only works with wrap_links
		));

		$data = array_merge($defaults, $options);

		//Establish text
		if ( empty($data['text']) ){
			if ( !empty($data['id']) ){
				if ( is_object($data['id']) && get_class($data['id']) == 'WP_Post' ){ //If we already have a WP_Post class object
					$the_post = $data['id'];
				} elseif ( intval($data['id']) ){ //If an ID is passed
					$the_post = get_post(intval($data['id']));
				}
			} else {
				$the_post = get_post(get_the_ID());
			}

			if ( empty($the_post) ){
				return false;
			}

			if ( !empty($the_post->post_excerpt) ){
				$data['text'] = $the_post->post_excerpt;
			} else {
				$data['text'] = $the_post->post_content;
				if ( $data['wp_more'] ){
					$wp_more_split = get_extended($the_post->post_content); //Split the content on the WordPress <!--more--> tag
					$data['text'] = $wp_more_split['main'];

					if ( preg_match('/<!--more(.*?)?-->/', $the_post->post_content, $matches) ){ //Get the custom <!--more Keep Reading--> text. RegEx from: https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/post-template.php#L288
						if ( !empty($matches[1]) ){
							$data['more'] = strip_tags(wp_kses_no_null(trim($matches[1])));
						}
					}
				}
			}
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
			$allowable_tags = ( !empty($data['paragraphs']) )? 'p' : '';
			$data['text'] = strip_tags($data['text'], $allowable_tags);
		}

		//Apply string limiters (words or characters)
		if ( !empty($data['characters']) && intval($data['characters']) ){ //Characters
			$limited = $this->string_limit_chars($data['text'], intval($data['characters'])); //Returns array: $limited['text'] is the string, $limited['is_limited'] is boolean if it was limited or not.
			$data['text'] = trim($limited['text']);
		} elseif ( (!empty($data['words']) && intval($data['words'])) || (!empty($data['length']) && intval($data['length'])) ){ //Words (or Length)
			$word_limit = ( !empty($data['length']) && intval($data['length']) )? intval($data['length']) : intval($data['words']);
			$limited = $this->string_limit_words($data['text'], $word_limit); //Returns array: $limited['text'] is the string, $limited['is_limited'] is boolean if it was limited or not.
			$data['text'] = trim($limited['text']);
		}

		//Apply dynamic sentence length limiter
		if ( $data['length'] === 'dynamic' ){
			$last_punctuation = -1;
			foreach ( array('.', '?', '!') as $punctuation ){
				if ( strrpos($data['text'] . ' ', $punctuation . ' ') ){
					$this_punctuation = strrpos($data['text'] . ' ', $punctuation . ' ')+1; //Find the last punctuation (add a space to the end of the string in case it already ends at the punctuation). Add 1 to capture the punctuation, too.

					if ( $this_punctuation > $last_punctuation ){
						$last_punctuation = $this_punctuation;
					}
				}
			}

			if ( $last_punctuation >= $data['min'] ){
				$data['text'] = substr($data['text'], 0, $last_punctuation); //Remove everything after the last punctuation in the string.
			}
		}

		//Check here for links to wrap
		if ( $data['wrap_links'] ){
			$data['text'] = preg_replace('/(\(?(?:(http|https|ftp):\/\/)?(?:((?:[^\W\s]|\.|-|[:]{1})+)@{1})?((?:www.)?(?:[^\W\s]|\.|-)+[\.][^\W\s]{2,4}|localhost(?=\/)|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})(?::(\d*))?([\/]?[^\s\?]*[\/]{1})*(?:\/?([^\s\n\?\[\]\{\}\#]*(?:(?=\.)){1}|[^\s\n\?\[\]\{\}\.\#]*)?([\.]{1}[^\s\?\#]*)?)?(?:\?{1}([^\s\n\#\[\]]*))?([\#][^\s\n]*)?\)?)(?![^<]*<\/)/i', '<a class="nebula-excerpt-url" href="$1">$1</a>', $data['text']); //Capture any URL not within < and </ using a negative lookahead (so it plays nice in case strip_tags is false)
		}

		//Shorten visible URL text
		if ( $data['shorten_urls'] ){
			$data['text'] = preg_replace_callback('/(<a.+>)(.+)(<\/a>)/', function($matches){
				$output = $matches[1];
				if ( strlen($matches[2]) > 20 ){
					$short_url = str_replace(array('http://', 'https://'), '', $matches[2]);
					$url_directories = explode('/', $short_url);
					$short_url = $url_directories[0];
					if ( count($url_directories) > 1 ){
						$short_url .= '/...';
					}
					$output .= $short_url;
				} else {
					$output .= $matches[2];
				}
				$output .= $matches[3];
				return $output;
			}, $data['text']);
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

			//Button
			$btn_class = '';
			if ( $data['button'] || $data['btn'] ){
				$button = ( $data['button'] )? $data['button'] : $data['btn'];
				$btn_class = ( is_bool($button) )? 'btn btn-brand' : 'btn ' . $data['button'];

				$data['text'] .= '<br /><br />';
			}

			$data['text'] .= ' <a class="nebula_excerpt ' . $btn_class . '" href="' . $data['url'] . '">' . $data['more'] . '</a>';
		}

		return $data['text'];
	}

	//Get the word count of a post
	public function word_count($options=array()){
		$override = apply_filters('pre_nebula_word_count', null, $options);
		if ( isset($override) ){return $override;}

		$defaults = array(
			'id' => get_the_id(),
			'content' => false,
			'range' => false, //Show a range instead of exact count
		);

		$data = array_merge($defaults, $options);

		$content = ( !empty($data['content']) )? $data['content'] : get_post_field('post_content', $data['id']);
		$content = apply_filters('nebula_word_count', $content, $data['id']); //Allow additional content to be added to the word count (such as ACF fields)

		$word_count = intval(round(str_word_count(strip_tags($content))));

		if ( is_int($word_count) ){
			if ( !$data['range'] ){
				return $word_count;
			}

			$words_label = __('words', 'nebula');

			if ( $word_count < 10 ){
				$word_count_range = '<10 ' . $words_label;
			} elseif ( $word_count < 500 ){
				$word_count_range = '10 - 499 ' . $words_label;
			} elseif ( $word_count < 1000 ){
				$word_count_range = '500 - 999 ' . $words_label;
			} elseif ( $word_count < 1500 ){
				$word_count_range = '1,000 - 1,499 ' . $words_label;
			} elseif ( $word_count < 2000 ){
				$word_count_range = '1,500 - 1,999 ' . $words_label;
			} else {
				$word_count_range = '2,000+ ' . $words_label;
			}

			return $word_count_range;
		}

		return false;
	}

	//Determines the estimated time to read a post (in minutes).
	//Note: Does not account for ACF fields unless hooked into 'nebula_word_count' above
	public function estimated_reading_time($id=false){
		//@todo "Nebula" 0: Use null coalescing operator here if possible
		if ( empty($id) ){
			$id = get_the_ID();
		}

		$wpm = 250; //Words per minute reading speed
		$content = $this->word_count(array('id' => $id));

		return intval(round($content/$wpm));
	}

	//Use WP Pagenavi if active, or manually paginate.
	public function paginate(){
		if ( is_plugin_active('wp-pagenavi/wp-pagenavi.php') ){
			wp_pagenavi();
		} else {
			global $wp_query;
			$big = 999999999; //An unlikely integer
			echo '<div class="wp-pagination">';
				echo paginate_links(array(
					'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
					'format' => '?paged=%#%',
					'current' => max(1, get_query_var('paged')),
					'total' => $wp_query->max_num_pages
				));
			echo '</div>';
		}
	}

	//A consistent way to link to social network profiles
	public function social_link($network){return $this->social_url($network);}
	public function social_url($network){
		switch ( strtolower($network) ){
			case 'facebook':
			case 'fb':
				return esc_url($this->get_option('facebook_url'));

			case 'twitter':
				return $this->twitter_url(); //Use the provided function from Nebula Options

			case 'linkedin':
				return esc_url($this->get_option('linkedin_url'));

			case 'instagram':
			case 'ig':
				return esc_url($this->get_option('instagram_url'));

			case 'pinterest':
				return esc_url($this->get_option('pinterest_url'));

			case 'youtube':
				return esc_url($this->get_option('youtube_url'));
		}

		return false;
	}

	//Display non-native social buttons
	//This is a more optimized solution that does not require SDKs and does not load third-party resources, so these will also be a consistent size.
	public function share($networks=array('shareapi', 'facebook', 'twitter'), $id=false){
		$override = apply_filters('pre_nebula_share', null, $networks, $id);
		if ( isset($override) ){return;}

		//@todo "Nebula" 0: Use null coalescing operator here if possible
		if ( empty($id) ){
			$id = get_the_id();
		}

		$encoded_url = urlencode(get_permalink($id));
		$encoded_title = urlencode(get_the_title($id));

		//Convert $networks to lower case without dashes/spaces for more flexible string matching later.
		$networks = array_map(function($value){
			return str_replace(array(' ', '_', '-'), '', strtolower($value));
		}, $networks);

		echo '<div class="sharing-links">';

		//If the 'shareapi' cookie or session exists and 'shareapi' is requested, return *only* the Share API
		if ( (isset($_COOKIE['shareapi']) || isset($_SESSION['shareapi'])) && in_array($networks, array('shareapi')) ){
			$networks = array('shareapi');
			$_SESSION['shareapi'] = true; //Set a session in case the cookie is deleted
		}

		foreach ( $networks as $network ){
			//Share API
			if ( in_array($network, array('shareapi')) ){
				echo '<a class="nebula-share-btn nebula-share webshare" href="#">' . __('Share', 'nebula') . '</a>';
			}

			//Facebook
			if ( in_array($network, array('facebook', 'fb')) ){
				echo '<a class="nebula-share-btn facebook" href="http://www.facebook.com/sharer.php?u=' . $encoded_url . '&t=' . $encoded_title . '" target="_blank" rel="noopener">' . __('Share', 'nebula') . '</a>';
			}

			//Twitter
			if ( in_array($network, array('twitter')) ){
				echo '<a class="nebula-share-btn twitter" href="https://twitter.com/intent/tweet?text=' . $encoded_title . '&url=' . $encoded_url . '" target="_blank" rel="noopener">' . __('Tweet', 'nebula') . '</a>';
			}

			//LinkedIn
			if ( in_array($network, array('linkedin', 'li')) ){
				echo '<a class="nebula-share-btn linkedin" href="http://www.linkedin.com/shareArticle?mini=true&url=' . $encoded_url . '&title=' . $encoded_title . '" target="_blank" rel="noopener">' . __('Share', 'nebula') . '</a>';
			}

			//Pinterest
			if ( in_array($network, array('pinterest', 'pin')) ){
				echo '<a class="nebula-share-btn pinterest" href="http://pinterest.com/pin/create/button/?url=' . $encoded_url . '" target="_blank" rel="noopener">' . __('Share', 'nebula') . '</a>';
			}

			//Email
			if ( in_array($network, array('email')) ){
				echo '<a class="nebula-share-btn email" href="mailto:?subject=' . $encoded_title . '&body=' . $encoded_url . '" target="_blank" rel="noopener">' . __('Email', 'nebula') . '</a>';
			}
		}

		echo '</div><!--/sharing-links-->';
	}

	//Display Native Social Buttons
	public function social($networks=array('shareapi', 'facebook', 'twitter'), $counts=0){
		$override = apply_filters('pre_nebula_social', null, $networks, $counts);
		if ( isset($override) ){return;}

		if ( is_string($networks) ){ //if $networks is a string, create an array for the string.
			$networks = array($networks);
		} elseif ( is_int($networks) && ($networks === 1 || $networks === 0) ){ //If it is an integer of 1 or 0, then set it to $counts
			$counts = $networks;
			$networks = array('shareapi', 'facebook', 'twitter');
		} elseif ( !is_array($networks) ){
			$networks = array('shareapi', 'facebook', 'twitter');
		}

		//Convert $networks to lower case without dashes/spaces for more flexible string matching later.
		$networks = array_map(function($value){
			return str_replace(array(' ', '_', '-'), '', strtolower($value));
		}, $networks);

		echo '<div class="sharing-links">';

		//If the 'shareapi' cookie or session exists and 'shareapi' is requested, return *only* the Share API
		if ( (isset($_COOKIE['shareapi']) || isset($_SESSION['shareapi'])) && in_array($networks, array('shareapi')) ){
			$networks = array('shareapi');
			$_SESSION['shareapi'] = true; //Set a session in case the cookie is deleted
		}

		foreach ( $networks as $network ){
			//Share API
			if ( in_array($network, array('shareapi')) ){
				$this->share_api();
			}

			//Facebook
			if ( in_array($network, array('facebook', 'fb')) ){
				$this->facebook_share($counts);
			}

			//Twitter
			if ( in_array($network, array('twitter')) ){
				$this->twitter_tweet($counts);
			}

			//LinkedIn
			if ( in_array($network, array('linkedin', 'li')) ){
				$this->linkedin_share($counts);
			}

			//Pinterest
			if ( in_array($network, array('pinterest', 'pin')) ){
				$this->pinterest_pin($counts);
			}
		}

		echo '</div><!--/sharing-links-->';
	}

	//Social Button Functions

	public function share_api(){
		$override = apply_filters('pre_nebula_share_api', null);
		if ( isset($override) ){return;}
		?>
			<div class="nebula-social-button webshare">
				<a class="btn btn-secondary btn-sm" href="#" target="_blank"><i class="fas fa-fw fa-share"></i> <?php _e('Share', 'nebula'); ?></a>
			</div>
		<?php
	}

	public function facebook_share($counts=0, $url=false){
		$override = apply_filters('pre_nebula_facebook_share', null, $counts);
		if ( isset($override) ){return;}
		?>
		<div class="nebula-social-button facebook-share require-fbsdk">
			<div class="fb-share-button" data-href="<?php echo ( !empty($url) )? $url : get_page_link(); ?>" data-layout="<?php echo ( $counts !== 0 )? 'button_count' : 'button'; ?>" data-size="small" data-mobile-iframe="true"></div>
		</div>
	<?php }


	public function facebook_like($counts=0, $url=false){
		$override = apply_filters('pre_nebula_facebook_like', null, $counts);
		if ( isset($override) ){return;}
		?>
		<div class="nebula-social-button facebook-like require-fbsdk">
			<div class="fb-like" data-href="<?php echo ( !empty($url) )? $url : get_page_link(); ?>" data-layout="<?php echo ( $counts !== 0 )? 'button_count' : 'button'; ?>" data-action="like" data-show-faces="false" data-share="false"></div>
		</div>
	<?php }

	public function facebook_both($counts=0, $url=false){
		$override = apply_filters('pre_nebula_facebook_both', null, $counts);
		if ( isset($override) ){return;}
		?>
		<div class="nebula-social-button facebook-both require-fbsdk">
			<div class="fb-like" data-href="<?php echo ( !empty($url) )? $url : get_page_link(); ?>" data-layout="<?php echo ( $counts !== 0 )? 'button_count' : 'button'; ?>" data-action="like" data-show-faces="false" data-share="true"></div>
		</div>
	<?php }


	public function twitter_tweet($counts=0){
		$override = apply_filters('pre_nebula_twitter_tweet', null, $counts);
		if ( isset($override) ){return;}
		?>
		<div class="nebula-social-button twitter-tweet">
			<a href="https://twitter.com/share" class="twitter-share-button" <?php echo ( $counts !== 0 )? '': 'data-count="none"'; ?>><?php _e('Tweet', 'nebula'); ?></a>
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
			<a href="https://twitter.com/<?php echo str_replace('@', '', $username); ?>" class="twitter-follow-button" <?php echo ( $counts !== 0 )? '': 'data-show-count="false"'; ?> <?php echo ( !empty($username) )? '': 'data-show-screen-name="false"'; ?>><?php echo __('Follow', 'nebula') . ' ' . $username; ?></a>
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

	public function linkedin_share($counts=0){
		$override = apply_filters('pre_nebula_linkedin_share', null, $counts);
		if ( isset($override) ){return;}
		?>
		<div class="nebula-social-button linkedin-share">
			<?php $this->linkedin_widget_script(); ?>
			<script type="IN/Share" <?php echo ( $counts !== 0 )? 'data-counter="right"' : ''; ?>></script>
		</div>
		<?php
	}

	public function linkedin_follow($counts=0){
		$override = apply_filters('pre_nebula_linkedin_follow', null, $counts);
		if ( isset($override) ){return;}
		?>
		<div class="nebula-social-button linkedin-follow">
			<?php $this->linkedin_widget_script(); ?>
			<script type="IN/FollowCompany" data-id="1337" <?php echo ( $counts !== 0 )? 'data-counter="right"' : ''; ?>></script>
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
			$featured_image = $this->get_thumbnail_src(get_the_post_thumbnail(get_the_id(), 'full'));
		} else {
			$featured_image = get_template_directory_uri() . '/assets/img/meta/og-thumb.png'; //@TODO "Nebula" 0: This should probably be a square? Check the recommended dimensions.
		}
		?>
		<div class="nebula-social-button pinterest-pin">
			<a href="//www.pinterest.com/pin/create/button/?url=<?php echo get_page_link(); ?>&media=<?php echo $featured_image; ?>&description=<?php echo urlencode(get_the_title()); ?>" data-pin-do="buttonPin" data-pin-config="<?php echo ( $counts !== 0 )? 'beside' : 'none'; ?>" data-pin-color="red">
				<img src="//assets.pinterest.com/images/pidgets/pinit_fg_en_rect_red_20.png" loading="lazy" />
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
		if ( isset($override) ){return $override;}

		$timer_name = $this->timer('Video Meta (' . $id . ')', 'start', 'Video Meta');

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
			if ( $provider === 'youtube' ){
				if ( !$this->get_option('google_server_api_key') && $this->is_staff() ){
					trigger_error('No Google Youtube Iframe API key. Youtube videos may not be tracked!', E_USER_WARNING);
					echo '<script>console.warn("No Google Youtube Iframe API key. Youtube videos may not be tracked!");</script>';
					$video_metadata['error'] = 'No Google Youtube Iframe API key.';
				}

				$response = $this->remote_get('https://www.googleapis.com/youtube/v3/videos?id=' . $id . '&part=snippet,contentDetails,statistics&key=' . $this->get_option('google_server_api_key'));
				if ( is_wp_error($response) ){
					trigger_error('Youtube video is unavailable.', E_USER_WARNING);
					$video_metadata['error'] = 'Youtube video is unavailable.';
					return $video_metadata;
				}

				$video_json = $response['body'];
			} elseif ( $provider === 'vimeo' ){
				$response = $this->remote_get('http://vimeo.com/api/v2/video/' . $id . '.json');
				if ( is_wp_error($response) ){
					trigger_error('Vimeo video is unavailable.', E_USER_WARNING);
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
				if ( $provider === 'youtube' ){
					$video_metadata['error'] = 'A Youtube Data API error occurred. Make sure the Youtube Data API is enabled in the Google Developer Console and the server key is saved in Nebula Options.';
				} else {
					$video_metadata['error'] = 'A Vimeo API error occurred (A video with ID ' . $id . ' may not exist). Tracking will not be possible.';
				}
			}
			return $video_metadata;
		} elseif ( $provider === 'youtube' && !empty($video_json->error) ){
			if ( current_user_can('manage_options') || $this->is_dev() ){
				$video_metadata['error'] = 'Youtube API Error: ' . $video_json->error->message;
			}
			return $video_metadata;
		} elseif ( $provider === 'youtube' && empty($video_json->items) ){
			if ( current_user_can('manage_options') || $this->is_dev() ){
				$video_metadata['error'] = 'A Youtube video with ID ' . $id . ' does not exist.';
			}
			return $video_metadata;
		} elseif ( $provider === 'vimeo' && is_array($video_json) && empty($video_json[0]) ){
			$video_metadata['error'] = 'A Vimeo video with ID ' . $id . ' does not exist.';
		}

		//Build Data
		if ( $provider === 'youtube' ){
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
		} elseif ( $provider === 'vimeo' ){
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

		$this->timer($timer_name, 'end');
		return $video_metadata;
	}

	//Breadcrumbs
	public function breadcrumbs($options=array()){
		$override = apply_filters('pre_nebula_breadcrumbs', null);
		if ( isset($override) ){return;}

		global $post;
		$defaults = apply_filters('nebula_breadcrumb_defaults', array(
			'delimiter' => '/', //Delimiter between crumbs
			'home' => get_bloginfo('title'), //Text for the 'Home' link
			'home_link' => home_url('/'),
			'prefix' => 'off', //Prefix categories and tags with "text", "icon", or none with "off" (default)
			'current' => true, //Show/Hide the current title in the breadcrumb
			'before' => '<li class="current" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">', //Tag before the current crumb
			'after' => '</li>', //Tag after the current crumb
			'force' => false //Override the breadcrumbs with an array of specific links
		));

		$data = array_merge($defaults, $options);
		$data['delimiter_html'] = '<li class="delimiter">' . $data['delimiter'] . '</li>';
		$data['current_node'] = $data['before'] . '<a class="current-breadcrumb-link" href="' . get_the_permalink() . '" itemprop="item"><span itemprop="name">' . strip_tags(get_the_title()) . '</span></a>';
		$position = 1; //Incrementer for each node (for schema tags)

		if ( !empty($data['force']) ){ //If using forced override
			echo '<ol class="nebula-breadcrumbs" itemscope itemtype="http://schema.org/BreadcrumbList">';

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
						echo '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="' . $node_url . '" itemprop="item">';
					}

					echo '<span itemprop="name">' . $node_text . '</span>';

					if ( !empty($node_url) ){
						echo '</a><meta itemprop="position" content="' . $position . '" /></li>';
					}

					echo ' ' . $data['delimiter_html'] . ' ';
				}

				$position++;
			}

			if ( !empty($data['current']) ){
				echo $data['current_node'] . '<meta itemprop="position" content="' . $position . '" />' . $data['after'];
			}

			echo '</ol>';
		} elseif ( is_home() || is_front_page() ){
			echo '<ol class="nebula-breadcrumbs" itemscope itemtype="http://schema.org/BreadcrumbList"><li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="' . $data['home_link'] . '" itemprop="item"><span itemprop="name">' . $data['home'] . ' <span class="sr-only">' . get_bloginfo('title') . '</span></span></a><meta itemprop="position" content="' . $position . '" /></li></ol>';
			$position++;
			return false;
		} else {
			echo '<ol class="nebula-breadcrumbs" itemscope itemtype="http://schema.org/BreadcrumbList"><li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="' . $data['home_link'] . '" itemprop="item"><span itemprop="name">' . $data['home'] . ' <span class="sr-only">' . get_bloginfo('title') . '</span></span></a><meta itemprop="position" content="' . $position . '" /></li> ' . $data['delimiter_html'] . ' ';
			$position++;

			if ( is_category() ){
				$thisCat = get_category(get_query_var('cat'), false);
				if ( $thisCat->parent !== 0 ){
					$parents = get_ancestors($thisCat->parent, 'category', 'taxonomy');
					array_unshift($parents, $thisCat->parent);
					foreach ( array_reverse($parents) as $term_id ){
						$parent = get_term($term_id, 'category');
						echo '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="' . esc_url(get_term_link($parent->term_id, 'category')) . '" itemprop="item"><span itemprop="name">' . $parent->name . '</span></a><meta itemprop="position" content="' . $position . '" /></li> ' . $data['delimiter_html'] . ' ';
						$position++;
					}
				}

				$prefix = '';
				if ( $data['prefix'] === 'icon' ){
					$prefix = '<i class="fas fa-bookmark"></i>';
				} elseif ( $data['prefix'] === 'text' ){
					$prefix = 'Category: ';
				}

				echo apply_filters('nebula_breadcrumbs_category', $data['before'] . '<a class="current-breadcrumb-link" href="' . get_category_link($thisCat->term_id) . '" itemprop="item"><span itemprop="name">' . $prefix . single_cat_title('', false) . '</span></a><meta itemprop="position" content="' . $position . '" />' . $data['after'], $data);
				$position++;
			} elseif ( is_search() ){
				echo $data['before'] . 'Search results' . $data['after'];
			} elseif ( is_day() ){
				echo '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="' . get_year_link(get_the_time('Y')) . '" itemprop="item"><span itemprop="name">' . get_the_time('Y') . '</span></a><meta itemprop="position" content="' . $position . '" /></li> ' . $data['delimiter_html'] . ' ';
				$position++;

				echo '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="' . get_month_link(get_the_time('Y'),get_the_time('m')) . '" itemprop="item"><span itemprop="name">' . get_the_time('F') . '</span></a><meta itemprop="position" content="' . $position . '" /></li> ' . $data['delimiter_html'] . ' ';
				$position++;

				echo $data['before'] . get_the_time('d') . $data['after'];
			} elseif ( is_month() ){
				echo '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="' . get_year_link(get_the_time('Y')) . '" itemprop="item"><span itemprop="name">' . get_the_time('Y') . '</span></a><meta itemprop="position" content="' . $position . '" /></li> ' . $data['delimiter_html'] . ' ';
				$position++;

				echo $data['before'] . get_the_time('F') . $data['after'];
			} elseif ( is_year() ){
				echo $data['before'] . get_the_time('Y') . $data['after'];
			} elseif ( is_single() && !is_attachment() ){
				if ( get_post_type() !== 'post' ){
					$post_type = get_post_type_object(get_post_type());

					$slug = $post_type->rewrite;
					if ( is_string($post_type->has_archive) ){ //If the post type has a custom archive slug
						$slug['slug'] = $post_type->has_archive; //Replace slug with the custom archive slug string
					}

					echo '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="' . $data['home_link'] . $slug['slug'] . '/" itemprop="item"><span itemprop="name">' . $post_type->labels->singular_name . '</span></a><meta itemprop="position" content="' . $position . '" /></li>';
					$position++;

					if ( !empty($data['current']) ){
						echo ' ' . $data['delimiter_html'] . ' ' . $data['current_node'] . '<meta itemprop="position" content="' . $position . '" />' . $data['after'];
					}
				} else {
					$cat = get_the_category();
					if ( !empty($cat) ){
						$cat = $cat[0];

						echo '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="' . esc_url(get_term_link($cat->term_id, 'category')) . '" itemprop="item"><span itemprop="name">' . $cat->name . '</span></a><meta itemprop="position" content="' . $position . '" /></li> ' . $data['delimiter_html'] . ' ';
						$position++;

						if ( !empty($data['current']) ){
							echo $data['current_node'] . '<meta itemprop="position" content="' . $position . '" />' . $data['after'];
						}
					}
				}
			} elseif ( !is_single() && !is_page() && get_post_type() !== 'post' && !is_404() ){
				$post_type = get_post_type_object(get_post_type());
				echo $data['before'] . $post_type->labels->singular_name . $data['after'];
			} elseif ( is_attachment() ){ //@TODO "Nebula" 0: Check for gallery pages? If so, it should be Home > Parent(s) > Gallery > Attachment
				if ( !empty($post->post_parent) ){ //@TODO "Nebula" 0: What happens if the page parent is a child of another page?
					echo '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="' . get_permalink($post->post_parent) . '" itemprop="item"><span itemprop="name">' . strip_tags(get_the_title($post->post_parent)) . '</span></a><meta itemprop="position" content="' . $position . '" /></li> ' . $data['delimiter_html'] . ' ' . strip_tags(get_the_title());
					$position++;
				} else {
					echo strip_tags(get_the_title());
				}
			} elseif ( is_page() && !$post->post_parent ){
				if ( !empty($data['current']) ){
					echo $data['current_node'] . '<meta itemprop="position" content="' . $position . '" />' . $data['after'];
				}
			} elseif ( is_page() && $post->post_parent ){
				$parent_id = $post->post_parent;
				$breadcrumbs = array();

				while ( $parent_id ){
					$page = get_page($parent_id);
					$breadcrumbs[] = '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="' . get_permalink($page->ID) . '" itemprop="item"><span itemprop="name">' . strip_tags(get_the_title($page->ID)) . '</span></a><meta itemprop="position" content="' . $position . '" /></li>';
					$position++;
					$parent_id = $page->post_parent;
				}

				$breadcrumbs = array_reverse($breadcrumbs);
				for ( $i = 0; $i < count($breadcrumbs); $i++ ){
					echo $breadcrumbs[$i];
					if ( $i !== count($breadcrumbs)-1 ){
						echo ' ' . $data['delimiter_html'] . ' ';
					}
				}

				if ( !empty($data['current']) ){
					echo ' ' . $data['delimiter_html'] . ' ' . $data['current_node'] . '<meta itemprop="position" content="' . $position . '" />' . $data['after'];
				}
			} elseif ( is_tag() ){
				$prefix = '';
				if ( $data['prefix'] === 'icon' ){
					$prefix = '<i class="fas fa-tag"></i>';
				} elseif ( $data['prefix'] === 'text' ){
					$prefix = 'Tag: ';
				}

				echo apply_filters('nebula_breadcrumbs_tag', $data['before'] . $prefix . single_tag_title('', false) . $data['after'], $data);
				//echo $data['before'] . '<a class="current-breadcrumb-link" href="' . get_tag_link($thisTag->term_id) . '">'. $prefix . single_tag_title('', false) . '</a>' . $data['after']; //@todo "Nebula": Need to get $thisTag like $thisCat above
			} elseif ( is_author() ){
				//@TODO "Nebula" 0: Support for multi author? is_multi_author()

				global $author;
				$userdata = get_userdata($author);
				echo apply_filters('nebula_breadcrumbs_author', $data['before'] . $userdata->display_name . $data['after'], $data);
			} elseif ( is_404() ){
				echo apply_filters('nebula_breadcrumbs_error', $data['before'] . 'Error 404' . $data['after'], $data);
			}

			if ( get_query_var('paged') ){
				echo apply_filters('nebula_breadcrumbs_paged', ' (Page ' . get_query_var('paged') . ')', $data);
			}
			echo '</ol>';
		}
	}

	//Modified WordPress search form using Bootstrap components
	public function search_form($form=null, $button=true){
		$override = apply_filters('pre_nebula_search_form', null, $form);
		if ( isset($override) ){return $override;}

		$placeholder = ( get_search_query() )? get_search_query() : __('Search', 'nebula');

		$form = '<form id="searchform" class="form-group form-inline ignore-form" role="search" method="get" action="' . home_url('/') . '">
					<div class="input-group mb-2 mr-sm-2 mb-sm-0">
						<div class="input-group-prepend mb-2">
							<div class="input-group-text"><i class="fas fa-search"></i></div>
						</div>
						<label class="sr-only" for="s">Search</label>
						<input id="s" class="form-control ignore-form mb-2" type="text" name="s" value="' . get_search_query() . '" placeholder="' . $placeholder . '" role="search" />
					</div>';

		if ( !empty($button) ){
			$form .= '<button id="searchsubmit" class="btn btn-brand wp_search_submit mb-2" type="submit">' . __('Submit', 'nebula') . '</button>';
		}

		$form .= '</form>';

		return $form;
	}

	//Easily create markup for a Hero area search input
	public function hero_search($placeholder=false){
		if ( empty($placeholder) ){
			$placeholder = __('What are you looking for?', 'nebula');
		}

		$override = apply_filters('pre_nebula_hero_search', null, $placeholder);
		if ( isset($override) ){return $override;}

		$form = '<div id="nebula-hero-formcon">
				<form id="nebula-hero-search" class="form-group search ignore-form" method="get" action="' . home_url('/') . '" role="search">
					<div class="input-group">
						<i class="fas fa-search"></i>
						<label class="sr-only" for="nebula-hero-search-input">Autocomplete Search</label>
						<input id="nebula-hero-search-input" type="search" class="form-control open input search nofade ignore-form" name="s" placeholder="' . $placeholder . '" autocomplete="off" tabindex="0" x-webkit-speech />
					</div>
				</form>
			</div>';

		return $form;
	}

	//Infinite Load
	//Ajax call handle in nebula()->infinite_load();
	public function infinite_load_query($args=array('post_status' => 'publish', 'showposts' => 4), $loop=false){
		$timer_name = $this->timer('Infinite Load Query');

		$override = apply_filters('pre_nebula_infinite_load_query', null);
		if ( isset($override) ){return;}

		global $wp_query;
		if ( empty($args['paged']) ){
			$args['paged'] = 1;
			if ( get_query_var('paged') ){
				$args['paged'] = get_query_var('paged');
				?>
				<div class="infinite-start-note">
					<a href="<?php echo get_the_permalink(); ?>">&laquo; <?php _e('Back to page 1', 'nebula'); ?></a>
				</div>
				<?php
			} elseif ( !empty($wp_query->query['paged']) ){
				$args['paged'] = $wp_query->query['paged'];
				?>
				<div class="infinite-start-note">
					<a href="<?php echo get_the_permalink(); ?>">&laquo; <?php _e('Back to page 1', 'nebula'); ?></a>
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

		<script><?php //Must be in PHP so $args can be encoded. @todo "Nebula" 0: This must have to load in the footer if jQuery is set to the footer...? ?>
			jQuery(window).on('load', function(){
				var pageNumber = <?php echo $args['paged']; ?>+1;

				jQuery('.infinite-load-more').on('click', function(){
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
									jQuery('.loadmorecon').addClass('disabled').find('a').text('<?php __('No more', 'nebula'); ?> <?php echo $post_type_label; ?>.');
								}

								var newQueryStrings = '';
								if ( typeof document.URL.split('?')[1] !== 'undefined' ){
									newQueryStrings = '?' + document.URL.split('?')[1].replace(/[?&]paged=\d+/, '');
								}

								history.replaceState(null, document.title, nebula.post.permalink + 'page/' + pageNumber + newQueryStrings);
								nebula.dom.document.trigger('nebula_infinite_finish');
								ga('send', 'event', 'Infinite Query', 'Load More', 'Loaded page ' + pageNumber);
								pageNumber++;
							},
							error: function(XMLHttpRequest, textStatus, errorThrown){
								jQuery(document).trigger('nebula_infinite_finish');
								ga('send', 'event', 'Error', 'AJAX Error', 'Infinite Query Load More AJAX');
							},
							timeout: 60000
						});
					}
					return false;
				});
			});
		</script>
		<?php
		$this->timer($timer_name, 'end');
	}

	//Related Posts by term frequency
	public function related_posts($post_id=null, $args=array()){
		global $post, $wpdb;

		$post_id = intval($post_id);
		if ( !$post_id && $post->ID ){
			$post_id = $post->ID;
		}

		if ( !$post_id ){
			return false; //Post ID is required for this function
		}

		$defaults = array(
			'taxonomy' => 'post_tag',
			'post_type' => array('post'),
			'max' => 5
		);
		$options = wp_parse_args($args, $defaults);

		$related_post_ids = get_transient('nebula-related-' . $options['taxonomy'] . '-' . $post_id);
		if ( empty($related_post_ids) || nebula()->is_debug() ){
			$term_args = array(
				'fields' => 'ids',
				'orderby' => 'count', //Sort by frequency
				'order' => 'ASC' //Least popular to most popular
			);

			$orig_terms_set = wp_get_object_terms($post_id, $options['taxonomy'], $term_args);
			$orig_terms_set = array_map('intval', $orig_terms_set); //Make sure each returned term id to be an integer.
			$terms_to_iterate = $orig_terms_set; //Store a copy that we'll be reducing by one item for each iteration.

			$post_args = array(
				'fields' => 'ids',
				'post_type' => $options['post_type'],
				'post__not_in' => array($post_id),
				'posts_per_page' => 50 //Start with more than enough posts
			);

			$related_post_ids = array();

			//Loop through the terms to find posts that contain multiple terms (term1 AND term2 AND term3)
			while ( count($terms_to_iterate) > 1 ){
				$post_args['tax_query'] = array(
					array(
						'taxonomy' => $options['taxonomy'],
						'field' => 'id',
						'terms' => $terms_to_iterate,
						'operator' => 'AND'
					)
				);

				$posts = get_posts($post_args);
				foreach( $posts as $id ){
					$id = intval($id);
					if ( !in_array($id, $related_post_ids) ){
						$related_post_ids[] = $id;
					}
				}

				array_pop($terms_to_iterate); //Remove the least related post ID
			}

			$post_args['posts_per_page'] = $options['max']; //Reduce the number to our desired max
			$post_args['tax_query'] = array(
				array(
					'taxonomy' => $options['taxonomy'],
					'field' => 'id',
					'terms' => $orig_terms_set
				)
			);

			//Check for posts that contain any of the terms (to fill out the desired max)
			$posts = get_posts($post_args);
			foreach ( $posts as $count => $id ){
				$id = intval($id);
				if ( !in_array($id, $related_post_ids) ){
					$related_post_ids[] = $id;
				}

				if ( count($related_post_ids) > $options['max'] ){
					break; //We have enough related post IDs now, stop the loop.
				}
			}

			set_transient('nebula-related-' . $options['taxonomy'] . '-' . $post_id, $related_post_ids, DAY_IN_SECONDS);
		}

		if ( !$related_post_ids ){
			return false;
		}

		//Query for the related post IDs
		$query_options = array(
			'post__in' => $related_post_ids,
			'orderby' => 'post__in',
			'post_type' => $options['post_type'],
			'posts_per_page' => min(array(count($related_post_ids), $options['max'])),
		);

		return new WP_Query($query_options);
	}

	//Check for single category templates with the filename single-cat-slug.php or single-cat-id.php
	public function single_category_template($single_template){
		global $wp_query, $post;

		//Check for single template by category slug and ID
		foreach ( get_the_category() as $category ){
			if ( file_exists(get_stylesheet_directory() . '/single-cat-' . $category->slug . '.php') ){
				return get_stylesheet_directory() . '/single-cat-' . $category->slug . '.php';
			} elseif ( file_exists(get_stylesheet_directory() . '/single-cat-' . $category->term_id . '.php') ){
				return get_stylesheet_directory() . '/single-cat-' . $category->term_id . '.php';
			} elseif ( file_exists(get_template_directory() . '/single-cat-' . $category->term_id . '.php') ){
				return get_template_directory() . '/single-cat-' . $category->term_id . '.php';
			} elseif ( file_exists(get_template_directory() . '/single-cat-' . $category->term_id . '.php') ){
				return get_template_directory() . '/single-cat-' . $category->term_id . '.php';
			}
		}

		return $single_template;
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
		if ( isset($override) ){return $override;}

		if ( empty($date) || $date === 'now' ){
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

				if ( date('N', $days_off[$key]) === 6 ){ //If the date is a Saturday
					$days_off[$key] = strtotime(date('F j, Y', $days_off[$key]) . ' -1 day');
				} elseif ( date('N', $days_off[$key]) === 7 ){ //If the date is a Sunday
					$days_off[$key] = strtotime(date('F j, Y', $days_off[$key]) . ' +1 day');
				}

				if ( date('Ymd', $days_off[$key]) === date('Ymd', $date) ){
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
		//@todo "Nebula" 0: Use null coalescing operator here if possible
		if ( empty($day) ){
			$day = strtolower(date('l'));
		}

		if ( $this->is_business_open() ){
			return esc_html($this->get_option('business_hours_' . $day . '_close'));
		}

		return false;
	}

	//Get the relative time of day
	public function relative_time($format=null){
		$override = apply_filters('pre_nebula_relative_time', null, $format);
		if ( isset($override) ){return $override;}

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

	//Get the appropriate logo from the themes, plugin, or Customizer
	public function logo($location='header'){
		//Allow a theme or plugin to handle the logo itself. This assumes it does its own priorities or overrides for everything!
		$hooked_logo = apply_filters('nebula_logo', false);
		if ( !empty($hooked_logo) ){
			return $hooked_logo;
		}

		$logo = false;
		$logo_filename = apply_filters('nebula_logo_filename', 'logo'); //Allow themes and plugins to set the logo filename to use. No extension here!

		//Search the parent theme for the logo file (SVG or PNG)
		if ( file_exists(get_template_directory() . '/assets/img/' . $logo_filename . '.svg') && $location !== 'meta' ){
			$logo = get_template_directory_uri() . '/assets/img/' . $logo_filename . '.svg';
		} elseif ( file_exists(get_template_directory() . '/assets/img/' . $logo_filename . '.png') ){
			$logo = get_template_directory_uri() . '/assets/img/' . $logo_filename . '.png';
		}

		//Search the child theme for the logo file (SVG or PNG)
		if ( file_exists(get_stylesheet_directory() . '/assets/img/' . $logo_filename . '.svg') && $location !== 'meta' ){
			$logo = get_stylesheet_directory_uri() . '/assets/img/' . $logo_filename . '.svg';
		} elseif ( file_exists(get_stylesheet_directory() . '/assets/img/' . $logo_filename . '.png') ){
			$logo = get_stylesheet_directory_uri() . '/assets/img/' . $logo_filename . '.png';
		}

		//If full color Customizer logo exists
		if ( get_theme_mod('custom_logo') ){
			$logo = $this->get_thumbnail_src(get_theme_mod('custom_logo'));
		}

		//If it is the footer and the one-color logo (footer) is requested (checkbox)
		if ( $location === 'footer' && get_theme_mod('nebula_footer_single_color_logo') ){
			if ( get_theme_mod('one_color_logo') ){ //If one-color Customizer logo exists
				return $this->get_thumbnail_src(get_theme_mod('one_color_logo'));
			}
		}

		//If it is the home page and the one-color logo (home) is requested (checkbox)
		if ( is_front_page() && get_theme_mod('nebula_hero_single_color_logo') && $location !== 'meta' ){
			if ( get_theme_mod('one_color_logo') ){ //If one-color Customizer logo exists
				return $this->get_thumbnail_src(get_theme_mod('one_color_logo'));
			}
		}

		//If it a sub page and the one-color (sub) logo is requested (checkbox)
		if ( !is_front_page() && get_theme_mod('nebula_header_single_color_logo') && $location !== 'meta' ){
			if ( get_theme_mod('one_color_logo') ){ //If one-color Customizer logo exists
				return $this->get_thumbnail_src(get_theme_mod('one_color_logo'));
			}
		}

		return esc_url($logo);
	}

	//Print the PHG logo as text with or without hover animation.
	public function pinckney_hugo_group($options){ $this->pinckneyhugogroup($options); }
	public function phg($options){ $this->pinckneyhugogroup($options); }
	public function pinckneyhugogroup($options=array()){
		$defaults = array(
			'animate' => false,
			'white' => false,
			'linked' => true,
		);

		$data = array_merge($defaults, $options);

		$anim = ( $data['animate'] )? 'anim' : '';
		$white = ( $data['white'] )? 'white' : '';
		$html = ( $data['linked'] )? '<a ' : '<span ';

		$html .= 'class="phg ' . $anim . ' ' . $white . '"';

		if ( $data['linked'] ){
			$html .= ' href="http://www.pinckneyhugo.com?utm_campaign=nebula&utm_medium=nebula&utm_source=' . urlencode(get_bloginfo('name')) . '&utm_content=phg+link+function' . $this->get_user_info('user_email', array('prepend' => '&nv-email=')) . '" target="_blank" rel="noopener"';
		}

		$html .= '><span class="pinckney">Pinckney</span><span class="hugo">Hugo</span><span class="group">' . __('Group', 'nebula') . '</span>';

		if ( $data['linked'] ){
			$html .= '</a>';
		} else {
			$html .= '</span>';
		}

		return $html;
	}

	//Get a datapoint for a user
	public function get_user_info($datapoint, $options=array()){
		$defaults = array(
			'id' => get_current_user_id(),
			'datapoint' => $datapoint,
			'fresh' => false,
			'prepend' => '',
			'append' => '',
			'fallback' => false,
		);

		$data = array_merge($defaults, $options);

		if ( empty($data['id']) ){ //If there is no user or current user is not logged in
			return $data['fallback'];
		}

		//Get from object cache unless specifically requested fresh data
		if ( !$data['fresh'] ){
			$userdata = wp_cache_get('nebula_user_info');
		}
		if ( empty($userdata) ){
			$userdata = get_userdata($data['id']);
			wp_cache_set('nebula_user_info', $userdata); //Store in object cache
		}

		if ( !empty($data['datapoint']) ){
			$requested_data = $data['datapoint'];

			if ( !empty($userdata->$requested_data) ){
				return $data['prepend'] . $userdata->$requested_data . $data['append'];
			} else {
				return $data['fallback'];
			}
		}

		if ( !empty($userdata) ){
			return $userdata;
		}

		return $data['fallback'];
	}

	//Determine if the author should be the Company Name or the specific author's name.
	public function the_author($show_authors=1){
		$override = apply_filters('pre_nebula_the_author', null, $show_authors);
		if ( isset($override) ){return $override;}

		if ( !is_single() || $show_authors === 0 || !$this->get_option('author_bios') ){
			return $this->get_option('site_owner', get_bloginfo('name'));
		} else {
			//@TODO "Nebula" 0: Add support for multi-authors? is_multi_author()
			return ( get_the_author_meta('first_name') != '' )? get_the_author_meta('first_name') . ' ' . get_the_author_meta('last_name') : get_the_author_meta('display_name');
		}
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

	//Add navigation menu item attributes (and metadata)
	public function add_menu_attributes($atts, $item, $args){
		$atts['itemprop'] = 'url';
		return $atts;
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
		remove_menu_page( 'edit-comments.php' );
		//Note: Do not remove the Discussion settings page. The comment blacklist is still used for other things like CF7 forms.
	}

	public function hide_ataglance_comment_counts(){
		if ( $this->get_option('comments') ){
			echo '<style>li.comment-count, li.comment-mod-count {display: none;}</style>'; //Hide comment counts in "At a Glance" metabox
		}
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
		if ( $this->get_option('comments') ){
			echo '<script>';
				echo 'cookieAuthorName = "";';
				echo 'cookieAuthorEmail = "";';

				if ( isset($_COOKIE['comment_author_' . COOKIEHASH]) ){
					echo 'cookieAuthorName = "' . $_COOKIE['comment_author_' . COOKIEHASH] . '";';
					echo 'cookieAuthorEmail = "' . $_COOKIE['comment_author_email_' . COOKIEHASH] . '";';
				}
			echo '</script>';
		}
	}

	//Twitter cached feed
	public function twitter_cache($options=array()){
		$defaults = apply_filters('nebula_twitter_cache_defaults', array(
			'user' => 'Great_Blakes',
			'list' => null,
			'number' => 5,
			'retweets' => 1,
		));

		$data = array_merge($defaults, $options);

		if ( !empty($_POST['data']) ){
			if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }
			$data['user'] = ( isset($_POST['data']['user']) )? sanitize_text_field($_POST['data']['user']) : $defaults['user'];
			$data['list'] = ( isset($_POST['data']['list']) )? sanitize_text_field($_POST['data']['list']) : $defaults['list']; //Only used for list feeds
			$data['number'] = ( isset($_POST['data']['number']) )? sanitize_text_field($_POST['data']['number']) : $defaults['number'];
			$data['retweets'] = ( isset($_POST['data']['retweets']) )? sanitize_text_field($_POST['data']['retweets']) : $defaults['retweets']; //1: Yes, 0: No
		}

		$twitter_timing_id = $this->timer('Twitter Cache (' . $data['user'] . ')', 'start', 'Twitter Cache');

		error_reporting(0); //Prevent PHP errors from being cached.

		if ( !empty($data['list']) ){
			$feed = 'https://api.twitter.com/1.1/lists/statuses.json?slug=' . $data['list'] . '&owner_screen_name=' . $data['user'] . '&count=' . $data['number'] . '&include_rts=' . $data['retweets'] . '&tweet_mode=extended';
		} else {
			$feed = 'https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=' . $data['user'] . '&count=' . $data['number'] . '&include_rts=' . $data['retweets'] . '&tweet_mode=extended';
		}

		$bearer = $this->get_option('twitter_bearer_token', '');
		if ( empty($bearer) ){
			trigger_error('A Twitter bearer token is required to get tweets', E_USER_WARNING);

			if ( !empty($_POST['data']) ){
				echo false;
				wp_die();
			} else {
				return false;
			}
		}

		$tweets = get_transient('nebula_twitter_' . $data['user']);
		if ( empty($tweets) || $this->is_debug() ){
			$args = array('headers' => array('Authorization' => 'Bearer ' . $bearer));

			$response = $this->remote_get($feed, $args);
			if ( is_wp_error($response) ){
				return false;
			}

			$tweets = json_decode($response['body']);

			if ( empty($tweets) ){
				trigger_error('No tweets were retrieved. Verify all options are correct and that an active bearer token is being used.', E_USER_NOTICE);

				if ( !empty($_POST['data']) ){
					echo false;
					wp_die();
				} else {
					return false;
				}
			}

			//Add convenient data to the tweet object
			foreach ( $tweets as $tweet ){
				$tweet->tweet_url = 'http://twitter.com/' . $tweet->user->screen_name . '/status/' . $tweet->id; //Add Tweet URL

				//Convert times
				$tweet->time_ago = human_time_diff(strtotime($tweet->created_at)); //Relative time
				$tweet->time_formatted = date_i18n('l, F j, Y \a\t g:ia', strtotime($tweet->created_at)); //Human readable time
				$tweet->time_ago_raw = date('U')-strtotime($tweet->created_at);

				//Convert usernames, hashtags, and URLs into clickable links and add other markup
				$tweet->markup = preg_replace(array(
					"/(http\S+)/i", //URLs (must be first)
					"/@([a-z0-9_]+)/i", //Usernames
					"/#([a-z0-9_]+)/i", //Hashtags
					"/(\d+\/(\d+)?)$/i", //Series numbers
				), array(
					"<a class='tweet-embedded-link' href='$1' target='_blank' rel='noopener'>$1</a>",
					"<a class='tweet-embedded-username' href='https://twitter.com/$1' target='_blank' rel='noopener'>@$1</a>",
					"<a class='tweet-embedded-hashtag' href='https://twitter.com/hashtag/$1' target='_blank' rel='noopener'>#$1</a>",
					"<small class='tweet-embedded-series-number'>$1</small>",
				), trim($tweet->full_text));
			}

			set_transient('nebula_twitter_' . $data['user'], $tweets, MINUTE_IN_SECONDS*5); //5 minute expiration
		}

		$this->timer($twitter_timing_id, 'end');

		if ( !empty($_POST['data']) ){
			echo json_encode($tweets);
			wp_die();
		} else {
			return $tweets;
		}
	}

	//Replace text on password protected posts to be more minimal
	public function password_form_simplify(){
		$output = '<form class="ignore-form" action="' . esc_url(site_url('wp-login.php?action=postpass', 'login_post')) . '" method="post">
						<span>' . __('Password', 'nebula') . ': </span>
						<input type="password" class="ignore-form" name="post_password" size="20" autocomplete="current-password" />
						<input type="submit" name="Submit" value="' . __('Go', 'nebula') . '" />
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
					//$this->ga_send_event('Internal Search', 'Invalid', '(Empty query)');
				}

				$this->ga_send_exception('(Security) Invalid search query');
				header('Location: ' . home_url('/') . 'search/?invalid');
				exit;
			} else {
				return $query;
			}
		}
	}

	//Redirect if only single search result
	public function redirect_single_search_result(){
		if ( is_search() ){
			global $wp_query;

			if ( $wp_query->post_count == 1 && $wp_query->max_num_pages == 1 ){
				if ( isset($_GET['s']) ){
					//If the redirected post is the homepage, serve the regular search results page with one result (to prevent a redirect loop)
					if ( $wp_query->posts['0']->ID !== 1 && get_permalink($wp_query->posts['0']->ID) !== home_url() . '/' ){

						if ( !nebula()->is_bot() && strpos(nebula()->ga_parse_cookie(), '.') !== false ){ //If it isn't a bot and the GA CID is actually from the GA cookie (not generated by Nebula)
							//$this->ga_send_event('Internal Search', 'Single Result Redirect', $_GET['s']);
						}

						$_GET['s'] = str_replace(' ', '+', $_GET['s']);
						wp_redirect(get_permalink($wp_query->posts['0']->ID ) . '?rs=' . $_GET['s']);
						exit;
					}
				} else {
					if ( !nebula()->is_bot() && strpos(nebula()->ga_parse_cookie(), '.') !== false ){ //If it isn't a bot and the GA CID is actually from the GA cookie (not generated by Nebula)
						//$this->ga_send_event('Internal Search', 'Single Result Redirect');
					}

					wp_redirect(get_permalink($wp_query->posts['0']->ID) . '?rs');
					exit;
				}
			}
		}
	}

	//Autocomplete Search (REST endpoint)
	public function rest_autocomplete_search(){
		$timer_name = $this->timer('Autocomplete Search');

		if ( isset($_GET['term']) ){
			ini_set('memory_limit', '256M'); //@TODO "Nebula" 0: Ideally this would not be here.

			$term = sanitize_text_field(trim($_GET['term']));
			if ( empty($term) ){
				return false;
			}

			$types = array('any');
			if ( isset($_GET['types']) ){
				$types = json_decode(sanitize_text_field(trim($_GET['types'])));
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

			$ignore_post_types = apply_filters('nebula_autocomplete_ignore_types', array()); //Allow post types to be globally ignored from autocomplete search
			$ignore_post_ids = apply_filters('nebula_autocomplete_ignore_ids', array()); //Allow individual posts to be globally ignored from autocomplete search

			$suggestions = array();

			//Loop through the posts
			if ( $autocomplete_query->have_posts() ){
				while ( $autocomplete_query->have_posts() ){
					$autocomplete_query->the_post();
					if ( in_array(get_the_id(), $ignore_post_ids) || !get_the_title() ){ //Ignore results without titles
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
			if ( !$this->in_array_any(array('attachment'), $ignore_post_types) && $this->in_array_any(array('any', 'attachment'), $types) ){
				$attachments = get_posts(array('post_type' => 'attachment', 's' => $term, 'numberposts' => 10, 'post_status' => null));
				if ( $attachments ){
					$attachment_count = 0;
					foreach ( $attachments as $attachment ){
						if ( in_array($attachment->ID, $ignore_post_ids) || strpos(get_attachment_link($attachment->ID), '?attachment_id=') ){ //Skip if media item is not associated with a post.
							continue;
						}
						$suggestion = array();
						$attachment_meta = wp_get_attachment_metadata($attachment->ID);

						if ( isset($attachment_meta['file']) ){
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
			}

			//Find menu items
			if ( !$this->in_array_any(array('menu'), $ignore_post_types) && $this->in_array_any(array('any', 'menu'), $types) ){
				$menus = get_transient('nebula_autocomplete_menus');
				if ( empty($menus) || $this->is_debug() ){
					$menus = get_terms('nav_menu');
					set_transient('nebula_autocomplete_menus', $menus, WEEK_IN_SECONDS); //This transient is deleted when a post is updated or Nebula Options are saved.
				}

				foreach ( $menus as $menu ){
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

							if ( !empty($menu_item->url) ){
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
			}

			//Find categories
			if ( !$this->in_array_any(array('category', 'cat'), $ignore_post_types) && $this->in_array_any(array('any', 'category', 'cat'), $types) ){
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
			if ( !$this->in_array_any(array('tag'), $ignore_post_types) && $this->in_array_any(array('any', 'tag'), $types) ){
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
			if ( $this->get_option('author_bios') && !$this->in_array_any(array('author'), $ignore_post_types) && $this->in_array_any(array('any', 'author'), $types) ){
				$authors = get_transient('nebula_autocomplete_authors');
				if ( empty($authors) || $this->is_debug() ){
					$authors = get_users(array('role' => 'author')); //@TODO "Nebula" 0: This should get users who have made at least one post. Maybe get all roles (except subscribers) then if postcount >= 1?
					set_transient('nebula_autocomplete_authors', $authors, WEEK_IN_SECONDS); //This transient is deleted when a post is updated or Nebula Options are saved.
				}

				foreach ( $authors as $author ){
					$author_name = ( $author->first_name != '' )? $author->first_name . ' ' . $author->last_name : $author->display_name; //might need adjusting here
					if ( strtolower($author_name) === strtolower($term) ){ //todo: if similarity of author name and query term is higher than X. Return only 1 or 2.
						$suggestion = array();
						$suggestion['label'] = $author_name;
						$suggestion['link'] = get_author_posts_url($author->ID);
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
			$suggestion['label'] = ( sizeof($suggestions) >= 1 )? __('...more results for', 'nebula') . ' "' . $term . '"' : __('Press enter to search for', 'nebula') . ' "' . $term . '"';
			$suggestion['link'] = home_url('/') . '?s=' . str_replace(' ', '%20', $term);
			$suggestion['classes'] = ( sizeof($suggestions) >= 1 )? 'more-results search-link' : 'no-results search-link';
			$outputArray[] = $suggestion;

			$this->timer($timer_name, 'end');
			return $outputArray;
		}
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
			$this->ga_send_exception('(PHP) 404 Error for requested URL: ' . nebula()->url_components()); //Track 404 error pages as exceptions in Google Analytics

			$this->slug_keywords = array_filter(explode('/', $this->url_components('filepath')));
			$this->slug_keywords = end($this->slug_keywords);

			//Query the DB with clues from the requested URL
			$this->error_query = new WP_Query(array('post_status' => 'publish', 'posts_per_page' => 4, 's' => str_replace('-', ' ', $this->slug_keywords)));
			if ( is_plugin_active('relevanssi/relevanssi.php') ){
				relevanssi_do_query($this->error_query);
			}

			//Check for an exact match
			if ( !empty($this->error_query->posts) && $this->slug_keywords === $this->error_query->posts[0]->post_name ){
				$this->error_404_exact_match = $this->error_query->posts[0];
			}
		}
	}

	//Add custom body classes
	public function body_classes($classes){
		$this->timer('Nebula Body Classes');

		$spaces_and_dots = array(' ', '.');
		$underscores_and_hyphens = array('_', '-');

		//Check the Save Data header
		$classes[] = ( $this->is_save_data() )? 'save-data' : '';

		//Device
		$classes[] = strtolower($this->get_device('formfactor')); //Form factor (desktop, tablet, mobile)
		$classes[] = strtolower($this->get_device('full')); //Device make and model
		$classes[] = strtolower(str_replace($spaces_and_dots, $underscores_and_hyphens, $this->get_os('full'))); //Operating System name with version
		$classes[] = strtolower(str_replace($spaces_and_dots, $underscores_and_hyphens, $this->get_os('name'))); //Operating System name
		$classes[] = strtolower(str_replace($spaces_and_dots, $underscores_and_hyphens, $this->get_browser('full'))); //Browser name and version
		$classes[] = strtolower(str_replace($spaces_and_dots, $underscores_and_hyphens, $this->get_browser('name'))); //Browser name
		$classes[] = strtolower(str_replace($spaces_and_dots, $underscores_and_hyphens, $this->get_browser('engine'))); //Rendering engine

		//When installed to the homescreen, Chrome is detected as "Chrome Mobile". Supplement it with a "chrome" class.
		if ( $this->get_browser('name') === 'Chrome Mobile' ){
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

		//Alternate Bootstrap versions
		$classes[] = 'bs-' . $this->get_option('bootstrap_version');

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

		//Staff
		if ( $this->is_staff() ){
			$classes[] = 'is-staff';
			if ( $this->is_dev() ){
				$classes[] = 'staff-developer';
			} elseif ( $this->is_client() ){
				$classes[] = 'staff-client';
			}
		}

		//Post Information
		if ( !is_search() && !is_archive() && !is_front_page() && !is_404() ){
			global $post;
			if ( isset($post) ){
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
					$classes[] = 'cat-' . $category->slug;
				}
			}
		}

		//Singular
		if ( is_singular() ){
			$classes[] = 'singular';
		} else {
			$classes[] = 'hfeed'; //Adds `hfeed` to non singular pages.
		}

		//Customizer
		if ( is_customize_preview() ){
			$classes[] = 'customizer-preview';
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

		$this->timer('Nebula Body Classes', 'end');
		return $classes;
	}

	//Add additional classes to post wrappers
	public function post_classes($classes){
		$this->timer('Nebula Post Classes');

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
		if ( is_multi_author() ){
			$classes[] = 'multi-author';
		}

		//Remove "hentry" meta class on pages or if Author Bios are disabled
		if ( is_page() || !$this->get_option('author_bios') ){
			$classes = array_diff($classes, array('hentry'));
		}

		$this->timer('Nebula Post Classes', 'end');
		return $classes;
	}

	//G1 Screen Reader Skip to Content Link https://www.w3.org/TR/WCAG20-TECHS/G1
	public function skip_to_content_link(){
		echo '<a class="skip-to-content-link sr-only" href="#content-section">Skip to Content</a>';
	}

	//Make sure attachment URLs match the protocol (to prevent mixed content warnings).
	public function wp_get_attachment_url_force_protocol($url){
		$http = site_url(false, 'http');
		$https = site_url(false, 'https');

		if ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ){
			return str_replace($http, $https, $url);
		} else {
			return $url;
		}
	}

	//Modify oembeds when necessary
	public function oembed_modifiers($html, $url, $attr, $post_id){
		//Enable the JS API for Youtube videos
		if ( strstr($html, 'youtube.com/embed/') ){
			$html = str_replace('feature=oembed', 'feature=oembed&enablejsapi=1&rel=0', $html);
		}

		return $html;
	}

	//Add autocomplete attributes to CF7 form fields
	public function cf7_autocomplete_attribute($content){
		$content = $this->autocomplete_find_replace($content, array('name', 'full-name', 'fullname', 'your-name'), 'name');
		$content = $this->autocomplete_find_replace($content, array('first-name', 'firstname'), 'given-name');
		$content = $this->autocomplete_find_replace($content, array('last-name', 'lastname'), 'family-name');
		$content = $this->autocomplete_find_replace($content, array('email', 'your-email'), 'email');
		$content = $this->autocomplete_find_replace($content, 'phone', 'tel');
		$content = $this->autocomplete_find_replace($content, array('company', 'company-name', 'companyname'), 'organization');
		$content = $this->autocomplete_find_replace($content, array('address', 'street'), 'address-line1');
		$content = $this->autocomplete_find_replace($content, 'city', 'address-level2');
		$content = $this->autocomplete_find_replace($content, 'state', 'address-level1');
		$content = $this->autocomplete_find_replace($content, array('zip', 'zipcode', 'postalcode'), 'postal-code');

		return $content;
	}

	//Find field names and add the autocomplete attribute when found
	public function autocomplete_find_replace($content, $finds=array(), $autocomplete_value){
		if ( !empty($content) && !empty($finds) && !empty($autocomplete_value) ){
			if ( is_string($finds) ){
				$finds = array($finds); //Convert the string to an array
			}

			foreach ( $finds as $find ){
				$field_name_pos = strpos(strtolower($content), 'name="' . strtolower($find) . '"');
				if ( !empty($field_name_pos) ){
					$content = substr_replace($content, ' autocomplete="' . $autocomplete_value . '" ', $field_name_pos, 0);
				}
			}
		}

		return $content;
	}

	//Add custom special mail tags to Contact Form 7
	public function cf7_custom_special_mail_tags($output, $name, $html){
		$submission = WPCF7_Submission::get_instance();
		if ( !$submission ){
			return $output;
		}

		//Contact Email
		if ( $name === '_nebula_contact_email' ){
			return ( $this->get_option('contact_email') )? $this->get_option('contact_email') : get_option('admin_email', $this->get_user_info('user_email', array('id' => 1)));
		}

		//Notification Email
		if ( $name === '_nebula_notification_email' ){
			return ( $this->get_option('notification_email') )? $this->get_option('notification_email') : get_option('admin_email', $this->get_user_info('user_email', array('id' => 1)));
		}

		//Safe From Address
		if ( $name === '_nebula_safe_from' ){
			$site_owner = ( $this->get_option('site_owner') )? $this->get_option('site_owner') : get_bloginfo('name');
			return $site_owner . '<wordpress@' . $this->url_components('domain') . '>';
		}

		//Debug Info
		if ( $name === 'debuginfo' || $name === '_debuginfo' || $name === '_nebula_debuginfo' || $name === '_nebula_debug' ){
			$debug_data = 'Nebula ' . $this->version('full') . PHP_EOL;
			$debug_data .= $this->nebula_session_id() . PHP_EOL;

			//Logged-in User Info
			$user_id = (int) $submission->get_meta('current_user_id');
			if ( !empty($user_id) ){
				$user_info = get_userdata($user_id);

				$debug_data .= 'User: ' . $user_info->user_login . ' (' . $user_info->ID . ')' . PHP_EOL;
				$debug_data .= 'Name: ' . $user_info->display_name . PHP_EOL;
				$debug_data .= 'Email: ' . $user_info->user_email . PHP_EOL;

				if ( get_the_author_meta('phonenumber', $user_info->ID) ){
					$debug_data .= 'Phone: ' . get_the_author_meta('phonenumber', $user_info->ID) . PHP_EOL;
				}

				if ( get_the_author_meta('jobtitle', $user_info->ID) ){
					$debug_data .= 'Title: ' . get_the_author_meta('jobtitle', $user_info->ID) . PHP_EOL;
				}

				if ( get_the_author_meta('jobcompany', $user_info->ID) ){
					$debug_data .= 'Company: ' . get_the_author_meta('jobcompany', $user_info->ID) . PHP_EOL;
				}

				if ( get_the_author_meta('jobcompanywebsite', $user_info->ID) ){
					$debug_data .= 'Company Website: ' . get_the_author_meta('jobcompanywebsite', $user_info->ID) . PHP_EOL;
				}

				if ( get_the_author_meta('usercity', $user_info->ID) && get_the_author_meta('userstate', $user_info->ID) ){
					$debug_data .= get_the_author_meta('usercity', $user_info->ID) . ', ' . get_the_author_meta('userstate', $user_info->ID) . PHP_EOL;
				}

				$debug_data .= $this->user_role() . PHP_EOL; //Role
			}

			//Bot detection
			if ( $this->is_bot() ){
				$debug_data .= '<strong>Bot detected!</strong>' . PHP_EOL;
			}

			//WPML Language
			if ( defined('ICL_LANGUAGE_NAME') ){
				$debug_data .= 'WPML Language: ' . ICL_LANGUAGE_NAME . ' (' . ICL_LANGUAGE_CODE . ')' . PHP_EOL;
			}

			//Device information
			if ( isset($_SERVER['HTTP_USER_AGENT']) ){
				$debug_data .= $_SERVER['HTTP_USER_AGENT'] . PHP_EOL;
			}
			if ( $this->get_option('device_detection') ){
				$debug_data .= ucwords($this->get_device('formfactor'));

				$device = $this->get_device('full');
				if ( !empty($device) ){
					$debug_data .= ', ' . $device;
				}

				$debug_data .= ', ' . $this->get_os();
				$debug_data .= ', ' . $this->get_browser('full');
				$debug_data .= PHP_EOL;
			}

			//IP address
			$debug_data .= 'IP: ' . $this->get_ip_address();
			$notable_poi = $this->poi();
			if ( !empty($notable_poi) ){
				$debug_data .= ' [' . $notable_poi . ']';
			}
			$debug_data .= PHP_EOL;

			return apply_filters('nebula_cf7_debug_data', $debug_data);
		}

		return $output;
	}

	//Add Google API key to Advanced Custom Fields Google Map field type
	public function acf_google_api_key(){
		return nebula()->get_option('google_browser_api_key');
	}

	//Generage a meta description (either from Yoast, or via Nebula excerpt)
	//Hooked as a filter called from Yoast (which passes $metadesc), and also called directly
	public function meta_description($metadesc=null, $chars=160){
		if ( empty($metadesc) ){
			//@todo "Nebula" 0: Use null coalescing operator here if possible
			$nebula_metadesc = $this->excerpt(array('length' => 'dynamic', 'characters' => $chars, 'more' => '', 'ellipsis' => false, 'strip_tags' => true));
			if ( empty($nebula_metadesc) ){
				$nebula_metadesc = get_bloginfo('description');
			}

			return esc_attr(stripslashes($nebula_metadesc));
		}

		return $metadesc;
	}

	//Allow using large Twitter cards with Yoast (without upgrading)
	public function allow_large_twitter_summary($value){
		if ( $value === 'summary' ){ //&& get_the_post_thumbnail($post->ID, 'twitter_large')
			$value = 'summary_large_image';
		}

		return $value;
	}

	//Execute arbitrary code from the options
	public function arbitrary_code_head(){
		echo $this->get_option('arbitrary_code_head');
	}
	public function arbitrary_code_body(){
		echo $this->get_option('arbitrary_code_body');
	}
	public function arbitrary_code_footer(){
		echo $this->get_option('arbitrary_code_footer');
	}

	//Flush rewrite rules when using ?debug at shutdown
	public function flush_rewrite_on_debug(){
		if ( $this->is_debug() ){
			flush_rewrite_rules(); //Note: this is an expensive operation
		}
	}
}
