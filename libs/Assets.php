<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Assets') ){
	trait Assets {
		public function hooks(){
			//Register styles/scripts
			add_action('wp_enqueue_scripts', array($this, 'register_scripts'));
			add_action('login_enqueue_scripts', array($this, 'register_scripts'));
			add_action('admin_enqueue_scripts', array($this, 'register_scripts'));

			//Enqueue styles/scripts
			add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
			add_action('login_enqueue_scripts', array($this, 'login_enqueue_scripts'));
			add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));

			if ( $this->is_debug() || !empty($GLOBALS['wp_customize']) ){
				add_filter('style_loader_src', array($this, 'add_debug_query_arg'), 500, 1);
				add_filter('script_loader_src', array($this, 'add_debug_query_arg'), 500, 1);
			}

			add_action('login_head', array($this, 'nebula_login_logo'));

			add_action('wp_head', array($this, 'output_nebula_data'));
			add_action('admin_head', array($this, 'output_nebula_data'));
		}

		//Register scripts
		public function register_scripts(){
			//Stylesheets
			//wp_register_style($handle, $src, $dependencies, $version, $media);
			wp_register_style('nebula-font_awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css', null, '5.11.2', 'all');
			wp_register_style('nebula-mmenu', 'https://cdnjs.cloudflare.com/ajax/libs/jQuery.mmenu/7.3.3/jquery.mmenu.all.css', null, '7.3.3', 'all');
			wp_register_style('nebula-main', get_template_directory_uri() . '/style.css', array('nebula-bootstrap'), $this->version('full'), 'all');
			wp_register_style('nebula-login', get_template_directory_uri() . '/assets/css/login.css', null, $this->version('full'), 'all');
			wp_register_style('nebula-admin', get_template_directory_uri() . '/assets/css/admin.css', null, $this->version('full'), 'all');

			if ( $this->get_option('remote_font_url') ){
				wp_register_style('nebula-remote_font', esc_url($this->get_option('remote_font_url')), array(), null, 'all');
			}

			$this->bootstrap('css');
			wp_register_style('nebula-datatables', 'https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/css/jquery.dataTables.min.css', null, '1.10.19', 'all');
			wp_register_style('nebula-chosen', 'https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css', null, '1.8.7', 'all');
			wp_register_style('nebula-jquery_ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.structure.min.css', null, '1.12.1', 'all');
			wp_register_style('nebula-pre', get_template_directory_uri() . '/assets/css/pre.css', null, $this->version('full'), 'all');

			//Scripts
			//Use CDNJS to pull common libraries: http://cdnjs.com/
			//nebula()->register_script($handle, $src, $exec, $dependencies, $version, $in_footer);
			$this->jquery();
			if ( $this->is_admin_page() || $this->get_option('allow_bootstrap_js') ){
				$this->bootstrap('js');
			}
			$this->register_script('nebula-jquery_ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', array('defer', 'crossorigin'), null, '1.12.1', true);
			$this->register_script('nebula-mmenu', 'https://cdnjs.cloudflare.com/ajax/libs/jQuery.mmenu/7.3.3/jquery.mmenu.all.js', array('defer', 'crossorigin'), null, '7.3.3', true);
			$this->register_script('nebula-vimeo', 'https://player.vimeo.com/api/player.js', null, null, null, true); //https://github.com/cdnjs/cdnjs/issues/13383
			$this->register_script('nebula-datatables', 'https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/jquery.dataTables.min.js', array('defer', 'crossorigin'), null, '1.10.19', true);
			$this->register_script('nebula-chosen', 'https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js', array('defer', 'crossorigin'), null, '1.8.7', true);
			$this->register_script('nebula-moment', 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js', array('defer', 'crossorigin'), null, '2.24.0', true);
			$this->register_script('nebula-autotrack', 'https://cdnjs.cloudflare.com/ajax/libs/autotrack/2.4.1/autotrack.js', array('async', 'crossorigin'), null, '2.4.1', true);
			$this->register_script('nebula-nebula', get_template_directory_uri() . '/assets/js/nebula.js', array('defer'), array('jquery-core'), $this->version('full'), true);

			$this->register_script('nebula-nebula-module', get_template_directory_uri() . '/assets/js/nebula-module.js', array('defer', 'module'), array('jquery-core'), $this->version('full'), true); //This is for testing only! Not supported in IE11

			$this->register_script('nebula-login', get_template_directory_uri() . '/assets/js/login.js', null, array('jquery-core'), $this->version('full'), true);
			$this->register_script('nebula-admin', get_template_directory_uri() . '/assets/js/admin.js', array('defer'), array('jquery-core'), $this->version('full'), true);
		}

		//Register the requested jQuery file
		public function jquery(){
			if ( !$this->is_admin_page(true) && $this->get_option('jquery_version') !== 'wordpress' ){
				$footer = ( !apply_filters('nebula_prevent_jquery_footer', false) && $this->get_option('jquery_version') === 'footer' )? true : false; //Whether to load jQuery in the head or footer
				wp_deregister_script('jquery-core');

				//Decide whether to use jQuery version 2 or 3.
				//jQuery version 3 is asynchronous so beware of the `ready` and `load` not always triggering in that order.
				if ( 1==2 ){
					return $this->register_script('jquery-core', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js', array('crossorigin'), null, '2.2.4', $footer);
				} else {
					return $this->register_script('jquery-core', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js', array('crossorigin'), null, '3.4.1', $footer);
				}
			}
		}

		//Register or return the requested Bootstrap file.
		public function bootstrap($file=false){
			if ( !$this->is_admin_page() ){
				if ( $this->get_option('bootstrap_version') === 'bootstrap3' ){
					//Bootstrap 3 (IE8+ Support)
					if ( $file === 'css' ){
						return wp_register_style('nebula-bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css', null, '3.3.7', 'all');
					} elseif ( $file === 'js' ){
						return $this->register_script('nebula-bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js', array('defer', 'crossorigin'), array('jquery-core'), '3.3.7', true);
					} elseif ( $file === 'reboot' ){
						return false;
					} else {
						return 'v3';
					}
				} elseif ( $this->get_option('bootstrap_version') === 'bootstrap4a5' ){
					//Bootstrap 4 alpha 5 (IE9+ Support)
					if ( $file === 'css' ){
						return wp_register_style('nebula-bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha.5/css/bootstrap.min.css', null, '4.0.0a5', 'all');
					} elseif ( $file === 'js' ){
						return $this->register_script('nebula-bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha.5/js/bootstrap.min.js', array('defer', 'crossorigin'), array('jquery-core'), '4.0.0a5', true);
					} elseif ( $file === 'reboot' ){
						return 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha.5/css/bootstrap-reboot.min.css';
					} else {
						return 'v4a5';
					}
				} elseif ( $this->get_option('bootstrap_version') === 'grid' ){
					//Bootstrap Reboot and Grid only
					if ( $file === 'css' ){
						return wp_register_style('nebula-bootstrap', get_template_directory_uri() . '/assets/css/vendor/bootstrap-reboot-grid.css', null, '4.4.1', 'all'); //Served locally to combine multiple resources (Reboot and Grid)
					} elseif ( $file === 'js' ){
						return false;
					} else {
						return 'grid';
					}
				}
			}

			//Latest (IE10+)
			if ( $file === 'css' ){
				return wp_register_style('nebula-bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.min.css', null, '4.4.1', 'all');
			} elseif ( $file === 'js' ){
				return $this->register_script('nebula-bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/js/bootstrap.bundle.min.js', array('defer', 'crossorigin'), array('jquery-core'), '4.4.1', true);
			} elseif ( $file === 'reboot' ){
				return 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap-reboot.min.css';
			} else {
				return 'latest';
			}
		}

		//Build Nebula data object and output to the <head>
		public function output_nebula_data(){
			$this->timer('Output Nebula Data');

			global $wp_scripts, $wp_styles, $upload_dir;
			$upload_dir = wp_upload_dir();

			//Prep CSS/JS resources for JS object
			$nebula_assets = array(
				'styles' => array(),
				'scripts' => array()
			);

			$lazy_assets = $this->lazy_load_assets();

			//Prep lazy assets for JS loading later
			foreach ( $wp_styles->registered as $handle => $data ){
				if ( (strpos($handle, 'nebula-') !== false && strpos($handle, 'admin') === false && strpos($handle, 'login') === false) || array_key_exists($handle, $lazy_assets['styles']) ){ //If the handle contains "nebula-" but not "admin" or "login" -or- if the asset is prepped for lazy-loading
					$nebula_assets['styles'][str_replace('-', '_', $handle)] = $data->src;
				}
			}
			foreach ( $wp_scripts->registered as $handle => $data ){
				if ( (strpos($handle, 'nebula-') !== false && strpos($handle, 'admin') === false && strpos($handle, 'login') === false) || array_key_exists($handle, $lazy_assets['scripts']) ){ //If the handle contains "nebula-" but not "admin" or "login" -or- if the asset is prepped for lazy-loading
					$nebula_assets['scripts'][str_replace('-', '_', $handle)] = str_replace(array('#defer', '#async'), '', $data->src);
				}
			}

			if ( !empty($lazy_assets['styles']) && !$this->is_admin_page() ){
				//Preload imminent CSS assets
				foreach ( $lazy_assets['styles'] as $handle => $condition ){
					if ( !empty($handle) && !empty($wp_styles->registered[$handle]) && $condition === 'all' ){ //Lazy loaded assets must have a handle!
						echo '<link rel="preload" id="' . $handle . '-css-preload" href="' . $wp_styles->registered[$handle]->src . '" as="style" />' . PHP_EOL;
					}
				}

				//Add <noscript> so lazy CSS files can be loaded for users without JavaScript
				echo '<noscript>' . PHP_EOL;
				foreach ( $lazy_assets['styles'] as $handle => $condition ){
					if ( !empty($handle) && !empty($wp_styles->registered[$handle]) ){ //Lazy loaded assets must have a handle!
						echo '<link rel="stylesheet" id="' . $handle . '-css" href="' . $wp_styles->registered[$handle]->src . '" type="text/css" media="' . $wp_styles->registered[$handle]->args . '" />' . PHP_EOL;
					}
				}
				echo '</noscript>' . PHP_EOL;
			}

			//Preload imminent JS assets
			if ( !empty($lazy_assets['scripts']) && !$this->is_admin_page() ){
				foreach ( $lazy_assets['scripts'] as $handle => $condition ){
					if ( !empty($handle) && $condition === 'all' ){ //Lazy loaded assets must have a handle!
						echo '<link rel="preload" id="' . $handle . '-js-preload" href="' . str_replace(array('#defer', '#async'), '', $wp_scripts->registered[$handle]->src) . '" as="script">' . PHP_EOL;
					}
				}
			}

			//Be careful changing the following array as many JS functions use this data!
			$this->brain = array(
				'version' => array(
					'number' => $this->version('full'),
					'date' => $this->version('date')
				),
				'site' => array(
					'name' => get_bloginfo('name'),
					'charset' => get_bloginfo('charset'),
					'directory' => array(
						'root' => get_site_url(),
						'template' => array(
							'path' => get_template_directory(),
							'uri' => get_template_directory_uri(),
						),
						'stylesheet' => array(
							'path' => get_stylesheet_directory(),
							'uri' => get_stylesheet_directory_uri(),
						),
						'modules' => get_template_directory_uri() . '/assets/js/modules/',
						'uploads' => $upload_dir['baseurl'],
					),
					'home_url' => home_url(),
					'sw_url' => esc_url($this->sw_location()),
					'domain' => $this->url_components('domain'),
					'protocol' => $this->url_components('protocol'),
					'language' => get_bloginfo('language'),
					'ajax' => array(
						'url' => admin_url('admin-ajax.php'),
						'nonce' => wp_create_nonce('nebula_ajax_nonce'),
					),
					'ecommerce' => false,
					'options' => array(
						'sw' => $this->get_option('service_worker'),
						'gaid' => esc_html($this->get_option('ga_tracking_id')),
						'nebula_cse_id' => esc_html($this->get_option('cse_id')),
						'nebula_google_browser_api_key' => esc_html($this->get_option('google_browser_api_key')),
						'facebook_url' => esc_url($this->get_option('facebook_url')),
						'facebook_app_id' => esc_html($this->get_option('facebook_app_id')),
						'twitter_username' => esc_html($this->get_option('twitter_username')),
						'twitter_url' => esc_url($this->twitter_url()),
						'linkedin_url' => esc_url($this->get_option('linkedin_url')),
						'youtube_url' => esc_url($this->get_option('youtube_url')),
						'instagram_url' => esc_url($this->get_option('instagram_url')),
						'pinterest_url' => esc_url($this->get_option('pinterest_url')),
						'adblock_detect' => $this->get_option('adblock_detect'),
						'manage_options' => current_user_can('manage_options'),
						'debug' => $this->is_debug(),
						'sidebar_expanders' => get_theme_mod('sidebar_accordion_expanders', true),
					),
					'resources' => array(
						'styles' => $nebula_assets['styles'],
						'scripts' => $nebula_assets['scripts'],
						'lazy' => $lazy_assets,
					),
					'timings' => false,
					'ecommerce' => false,
				),
				'post' => ( is_search() )? null : array( //Conditional prevents wrong ID being used on search results
					'id' => get_the_id(),
					'permalink' => get_the_permalink(),
					'title' => urlencode(get_the_title()),
					'excerpt' => esc_html($this->excerpt(array('words' => 100, 'more' => '', 'ellipsis' => false, 'strip_tags' => true))),
					'author' => urlencode(get_the_author()),
					'year' => get_the_date('Y'),
					'categories' => $this->post_categories(array('string' => true)),
					'tags' => $this->post_tags(array('string' => true)),
					'page' => ( get_query_var('paged') )? get_query_var('paged') : 1,
					'isFrontPage' => is_front_page(),
				),
				'dom' => null,
			);

			//Check for session data
			if ( isset($_SESSION['nebulaSession']) && json_decode($_SESSION['nebulaSession'], true) ){ //If session exists and is valid JSON
				$this->brain['session'] = json_decode($_SESSION['nebulaSession'], true); //Replace nebula.session with session data
			} else {
				$this->brain['session'] = array(
					'ip' => $this->get_ip_address(),
					'id' => $this->nebula_session_id(),
					'flags' => array(
						'adblock' => false,
						'gablock' => false,
					),
					'geolocation' => false
				);
			}

			//User Data
			$this->brain['user'] = array(
				'id' => get_current_user_id(),
				'name' => array(
					'first' => esc_html($this->get_user_info('first_name')),
					'last' => esc_html($this->get_user_info('last_name')),
					'full' => esc_html($this->get_user_info('display_name')),
				),
				'email' => $this->get_user_info('user_email'),
				'ip' => $this->get_ip_address(),
				'dnt' => !$this->is_analytics_allowed(),
				'cid' => esc_html($this->ga_parse_cookie()),
				'client' => array( //Client data is here inside user because the cookie is not transferred between clients.
					'bot' => $this->is_bot(),
					'remote_addr' => $this->get_ip_address(),
					'device' => array(
						'full' => $this->get_device('full'),
						'formfactor' => $this->get_device('formfactor'),
						'brand' => $this->get_device('brand'),
						'model' => $this->get_device('model'),
						'type' => $this->get_device('type'),
					),
					'os' => array(
						'full' => $this->get_os('full'),
						'name' => $this->get_os('name'),
						'version' => $this->get_os('version'),
					),
					'browser' => array(
						'full' => $this->get_browser('full'),
						'name' => $this->get_browser('name'),
						'version' => $this->get_browser('version'),
						'engine' => $this->get_browser('engine'),
						'type' => $this->get_browser('type'),
					),
				),
				'address' => false,
				'facebook' => false,
				'flags' => array(
					'fbconnect' => false,
				),
			);

			//Staff (This is not meant for security checks!)
			$this->brain['user']['staff'] = false;
			if ( $this->is_staff() ){
				$this->brain['user']['staff'] = 'staff';

				if ( $this->is_dev() ){
					$this->brain['user']['staff'] = 'developer';
				} elseif ( $this->is_client() ){
					$this->brain['user']['staff'] = 'client';
				}
			}

			$this->brain = apply_filters('nebula_brain', $this->brain); //Allow other functions to hook in to add/modify data
			$this->brain['user']['known'] = ( !empty($this->brain['user']['email']) )? true : false; //Move to companion plugin

			echo '<script type="text/javascript">var nebula = ' . json_encode($this->brain) . '</script>'; //Output the data to <head>
			$this->timer('Output Nebula Data', 'end');
		}

		//Enqueue frontend scripts
		public function enqueue_scripts($hook){
			//Stylesheets
			wp_enqueue_style('nebula-bootstrap');
			wp_enqueue_style('nebula-main');

			if ( $this->get_option('remote_font_url') ){
				wp_enqueue_style('nebula-remote_font');
			}

			//Scripts
			wp_enqueue_script('jquery-core');
			wp_enqueue_script('nebula-bootstrap');

			if ( $this->is_analytics_allowed() && $this->get_option('ga_tracking_id') ){
				wp_enqueue_script('nebula-autotrack');
			}

			wp_enqueue_script('nebula-nebula');

			//Preparing to switch over to modules. Not supported in IE11!
			if ( 1==2 ){
				wp_enqueue_script('nebula-nebula-module');
			}

			//Conditionals
			if ( is_page_template('tpl-search.php') ){ //Form pages (that use selects) or Advanced Search Template. The Chosen library is also dynamically loaded in nebula.js.
				wp_enqueue_style('nebula-chosen');
				wp_enqueue_script('nebula-chosen');
			}
		}

		//Enqueue login scripts
		public function login_enqueue_scripts($hook){
			//Stylesheets
			wp_enqueue_style('nebula-login');

			//Scripts
			wp_enqueue_script('jquery-core');
			wp_enqueue_script('nebula-login');
		}

		//Enqueue admin scripts
		public function admin_enqueue_scripts($hook){
			$current_screen = get_current_screen();

			//Exclude AJAX and REST requests
			if ( !$this->is_ajax_or_rest_request() ){
				//Stylesheets
				wp_enqueue_style('nebula-admin');
				wp_enqueue_style('nebula-font_awesome'); //Font Awesome 5 CSS method

				//Scripts
				wp_enqueue_script('nebula-admin');

				//Nebula Options page
				$current_screen = get_current_screen();
				if ( $current_screen->base === 'appearance_page_nebula_options' || $current_screen->base === 'options' ){
					//$this->append_dependency('nebula-admin', 'nebula-bootstrap');
					wp_enqueue_style('nebula-bootstrap');
					wp_enqueue_script('nebula-bootstrap');
					wp_enqueue_script('postbox'); //Enables metabox collapse toggling
				}

				//User Profile edit page
				if ( $current_screen->base === 'profile' ){
					wp_enqueue_style('thickbox');
					wp_enqueue_script('thickbox');
					wp_enqueue_script('media-upload');
				}
			}
		}

		public function nebula_login_logo(){
			//Login logo replacement
			$logo = get_theme_file_uri('/assets/img/logo.svg'); //Use parent directory logo by default
			if ( get_theme_mod('custom_logo') ){ //If the Customizer logo exists
				$logo = nebula()->get_thumbnail_src(get_theme_mod('custom_logo'));
			} elseif ( file_exists(get_stylesheet_directory() . '/assets/img/logo.svg') ){ //If SVG logo exists in child theme
				$logo = get_stylesheet_directory_uri() . '/assets/img/logo.svg';
			} elseif ( file_exists(get_stylesheet_directory() . '/assets/img/logo.png') ){ //If PNG logo exists in child theme
				$logo = get_stylesheet_directory_uri() . '/assets/img/logo.png';
			}

			echo '<style>div#login h1 a {background: url(' . $logo . ') center center no-repeat; width: auto; background-size: contain;}</style>';
		}

		//Add $dep (script handle) to the array of dependencies for $handle
		public function append_dependency($handle, $dep){
			global $wp_scripts;

			$script = $wp_scripts->query($handle, 'registered');
			if ( !$script ){
				return false;
			}

			if ( !in_array($dep, $script->deps) ){
				$script->deps[] = $dep;
			}

			return true;
		}

		//Get fresh resources when debugging
		public function add_debug_query_arg($src){
			return add_query_arg('debug', str_replace('.', '', $this->version('raw')) . '-' . rand(1000, 9999), $src);
		}
	}
}