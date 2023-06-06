<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Automation') ){
	trait Automation {
		public function hooks(){
			if ( $this->is_nebula() ){ //Only if Nebula is the active parent theme
				global $pagenow;

				//Detect and prompt install of Recommended and Optional plugins using TGMPA
				//Configuration Documentation: http://tgmpluginactivation.com/configuration/
				if ( $this->is_admin_page() && ($this->is_dev(true) || current_user_can('manage_options')) ){ //If the WP admin is being viewed and this user is an admin or developer
					if ( ($this->get_option('bundled_plugins_notification') || !get_user_meta(get_current_user_id(), 'tgmpa_dismissed_notice_nebula')) || (isset($this->super->get['page']) && strpos($this->super->get['page'], 'tgmpa') !== false) ){ //If the option is enabled or the user has not dismissed the prompt, or if this page has been specifically requested //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
						require_once get_template_directory() . '/inc/vendor/class-tgm-plugin-activation.php';
						add_action('tgmpa_register', array($this, 'register_required_plugins'));
					} else {
						add_action('admin_menu', array($this, 'quicker_recommended_plugins_menu_link')); //Add a link without needing the entire library
					}
				}

				add_action('after_switch_theme', array($this, 'activation_notice'));

				if ( isset($this->super->get['nebula-initialization']) && $pagenow === 'themes.php' ){
					add_action('admin_init', array($this, 'initialization'));
				}

				if ( (isset($this->super->get['nebula-initialization']) || isset($this->super->get['initialization-success'])) && $pagenow === 'themes.php' ){
					add_action('admin_notices', array($this, 'activation'));
				}

				add_action('admin_init', array($this, 'set_dates'));

				//add_action('admin_init', array($this, 'force_settings' ), 9); //Uncomment this line to force an initialization date.
			}
		}

		//When the entire TGMP library is not available (if the user dismissed or disabled the prompt), still provide a link to that admin page. This is only needed when that prompt has been dismissed– otherwise the library creates this link.
		public function quicker_recommended_plugins_menu_link(){
			add_submenu_page(
				'plugins.php', //Parent Slug
				'Recommended Plugins', //Page Title (irrelevant here)
				'Recommended Plugins', //Menu Title
				'manage_options', //Capabilities
				'plugins.php?page=tgmpa-install-plugins&plugin_status=install', //Where it links to
			);
		}

		public function register_required_plugins(){
			global $pagenow;

			//If this user is on the Plugins or Themes (Appearance) admin pages or has not yet dismissed the TGMPA admin notice
			if ( $pagenow === 'plugins.php' || $pagenow === 'themes.php' || !get_user_meta(get_current_user_id(), 'tgmpa_dismissed_notice_nebula') ){
				$this->timer('Register Bundled Plugins');

				$bundled_plugins = array(
					array(
						'name' => 'Contact Form 7',
						'slug' => 'contact-form-7',
						'required' => true,
					),
					// array(
					// 	'name' => 'Advanced Contact Form 7 DB', //Need to find a new plugin, or wait for an update due to security vulnerability
					// 	'slug' => 'advanced-cf7-db',
					// 	'required' => true,
					// ),
					array(
						'name' => 'Contact Form 7 Database Addon – CFDB7', //Backup CF7 DB plugin until (if) the preferred plugin (advanced-cf7-db) is updated
						'slug' => 'contact-form-cfdb7',
						'required' => true
					),
					array(
						'name' => 'Advanced Custom Fields',
						'slug' => 'advanced-custom-fields',
						'required' => false,
					),
					array(
						'name' => 'EWWW Image Optimizer',
						'slug' => 'ewww-image-optimizer',
						'required' => false,
					),
					array(
						'name' => 'Regenerate Thumbnails',
						'slug' => 'regenerate-thumbnails',
						'required' => false,
					),
					// array(
					// 	'name' => 'W3 Total Cache',
					// 	'slug' => 'w3-total-cache',
					// 	'required' => false,
					// ),
					array(
						'name' => 'WP-PageNavi',
						'slug' => 'wp-pagenavi',
						'required' => true,
					),
					array(
						'name' => 'Multiple Themes',
						'slug' => 'jonradio-multiple-themes',
						'required' => false,
					),
					array(
						'name' => 'Responsive Lightbox',
						'slug' => 'responsive-lightbox',
						'required' => false,
					),
					array(
						'name' => 'WP Mail SMTP',
						'slug' => 'wp-mail-smtp',
						'required' => false,
					),
					array(
						'name' => 'WooCommerce',
						'slug' => 'woocommerce',
						'required' => false,
					),
					array(
						'name' => 'WordPress SEO by Yoast',
						'slug' => 'wordpress-seo',
						'required' => true,
					),
					array(
						'name' => 'ACF Content Analysis for Yoast SEO',
						'slug' => 'acf-content-analysis-for-yoast-seo',
						'required' => false,
					),
					array(
						'name' => 'Relevanssi',
						'slug' => 'relevanssi',
						'required' => true,
					),
					array(
						'name' => 'Transients Manager',
						'slug' => 'transients-manager',
						'required' => false,
					),
					array(
						'name' => 'UpdraftPlus Backup and Restoration',
						'slug' => 'updraftplus',
						'required' => false,
					),
					array(
						'name' => 'Wordfence Security', //Used for general security and limiting login attempts
						'slug' => 'wordfence',
						'required' => false,
					),
					array(
						'name' => 'Sucuri Security', //Used to monitor filesystem and log login attempts, other hardening tools
						'slug' => 'sucuri-scanner',
						'required' => false,
					),
					array(
						'name' => 'Query Monitor',
						'slug' => 'query-monitor',
						'required' => false,
					),
					array(
						'name' => 'All-in-One WP Migration',
						'slug' => 'all-in-one-wp-migration',
						'required' => false,
					),
					array(
						'name' => 'Redirection',
						'slug' => 'redirection',
						'required' => false,
					),
					array(
						'name' => 'Site Kit by Google',
						'slug' => 'google-site-kit',
						'required' => false,
					),
				);

				if ( is_plugin_active('woocommerce/woocommerce.php') ){
					$bundled_plugins[] = array(
						'name' => 'WooCommerce Google Analytics Integration',
						'slug' => 'woocommerce-google-analytics-integration',
						'required' => true,
					);
				}

				$all_bundled_plugins = apply_filters('nebula_bundled_plugins', $bundled_plugins); //Allow other themes and plugins to bundle additional plugins

				$config = array(
					'id' => 'nebula',
					'parent_slug' => 'plugins.php', //Where the "Install Plugins" submenu appears. Note: WordPress.org theme distribution requires this to be under "themes.php"
					'strings' => array(
						'menu_title' => 'Recommended Plugins',
						'page_title' => 'Recommended Plugins',
						'notice_can_install_required' => _n_noop(
							'This theme recommends the following plugin: %1$s.',
							'This theme recommends the following plugins: %1$s.',
							'tgmpa'
						),
						'notice_can_install_recommended' => _n_noop(
							'The following optional plugin may be needed for the theme: %1$s.',
							'The following optional plugins may be needed for the theme: %1$s.',
							'tgmpa'
						),
						'notice_can_activate_required' => _n_noop(
							'The following recommended plugin is currently inactive: %1$s.',
							'The following recommended plugins are currently inactive: %1$s.',
							'tgmpa'
						),
						'notice_can_activate_recommended' => _n_noop(
							'The following optional plugin is currently inactive: %1$s.',
							'The following optional plugins are currently inactive: %1$s.',
							'tgmpa'
						),
					)
				);

				tgmpa($all_bundled_plugins, $config);
				$this->timer('Register Bundled Plugins', 'end');
			}
		}

		//Make sure certain data is always set
		public function set_dates(){
			$first_activation = $this->get_data('first_activation');
			if ( empty($first_activation) ){
				$this->update_data('first_version', $this->version('raw'));
				$this->update_data('first_activation', time());
			}
		}

		public function activation_notice(){
			$this->set_dates();
			add_action('admin_notices', array($this, 'activation'));
			flush_rewrite_rules(); //Note: this is an expensive operation
		}

		public function activation(){
			$this->usage('theme_activation');
			$this->add_log('Theme activation', 6);

			//If not initialized before, set default options if they haven't been already
			if ( !$this->is_initialized_before() ){
				$this->initialization_nebula_defaults(false);
				$this->set_dates();
			}
			?>
			<?php if ( is_child_theme() ): ?>
				<div id="nebula-activate-success" class="updated">
					<p>
						<strong class="nebula-activated-title">Nebula child theme has been activated.</strong><br />
						<span class="nebula-activated-description">If menus were created in the parent theme (before initialization), they may need to be <a href="nav-menus.php">re-assigned to their corresponding locations</a>. Next step:</span>
					</p>
					<p><a class="button button-primary" href="themes.php?page=nebula_options">Configure Nebula Options</a></p>
				</div>
			<?php elseif ( (isset($this->super->get['nebula-initialization']) || isset($this->super->get['initialization-success'])) && current_user_can('manage_options') ): ?>
				<div id="nebula-activate-success" class="updated">
					<p>
						<strong class="nebula-activated-title">Nebula has been initialized!</strong><br />
						<span class="nebula-activated-description">Options have been updated. The home page has been updated and has been set as the static front page in <a href='options-reading.php'>Settings > Reading</a>. Next step:</span>
					</p>
					<p><a class="button button-primary" href="themes.php?page=nebula_options">Configure Nebula Options</a></p>
				</div>
			<?php else: ?>
				<?php $this->scss_controller(true); //Re-render all SCSS files. ?>

				<?php if ( $this->is_initialized_before() ): ?>
					<div id="nebula-activate-success" class="updated">
						<p>
							<strong class="nebula-activated-title">Nebula has been re-activated!</strong><br />
							<span class="nebula-activated-description">Re-run the automated Nebula initialization process if needed.</span>
						</p>
						<p>
							<?php if ( current_user_can('manage_options') ): ?>
								<a id="run-nebula-initialization" class="button button-primary" href="themes.php?nebula-initialization=true" title="This will reset some Wordpress core settings and all Nebula options!">Initialize Nebula</a>
							<?php else: ?>
								Contact the site administrator if the automated Nebula initialization processes need to be re-run.
							<?php endif; ?>
						</p>
					</div>
				<?php else: ?>
					<div id="nebula-activate-success" class="updated">
						<p>
							<strong class="nebula-activated-title">Nebula has been activated!</strong><br />
							<span class="nebula-activated-description">Next step: Run the automated Nebula initialization process. This initialization process will move and activate the Nebula child theme automatically (if it does not already exist).</span>
						</p>
						<p>
							<?php if ( current_user_can('manage_options') ): ?>
								<a id="run-nebula-initialization" class="button button-primary" href="themes.php?nebula-initialization=true" title="This will reset some Wordpress core settings and all Nebula options!">Initialize Nebula</a>
							<?php else: ?>
								Contact the site administrator to run the automated Nebula initialization processes.
							<?php endif; ?>
						</p>
					</div>
				<?php endif; ?>
			<?php endif;
		}

		//Nebula Full Initialization
		public function initialization(){
			if ( current_user_can('manage_options') ){
				$this->timer('Full Initialization');

				$this->usage('initialization');
				$this->add_log('Theme settings have been re-initialized.', 7);
				$this->full_automation();
				$this->initialization_email_prev_settings();

				if ( !$this->get_data('initialized') ){
					$this->update_data('initialized', time());
				}

				$activated_child = $this->initialization_activate_child_theme();

				$this->scss_controller(true); //Re-render all SCSS files.

				if ( $activated_child ){
					wp_redirect(admin_url('/themes.php?initialization-success'), 301); //Redirect to show new theme activated
				}

				$this->timer('Full Initialization', 'end');
			}
		}

		//Manually update all preferred Nebula and WP Core settings
		public function full_automation(){
			$this->initialization_nebula_defaults(true);
			$this->initialization_wp_core_preferred_settings();
			$this->initialization_create_homepage();
			$this->initialization_delete_plugins();
			$this->initialization_deactivate_widgets();
			$this->set_default_admin_color(get_current_user_id());
		}

		//Send a list of existing settings to the user's email (to test, trigger the function on admin_init)
		public function initialization_email_prev_settings(){
			if ( !$this->is_initialized_before() ){
				return;
			}

			$current_user = wp_get_current_user();

			$subject = 'Wordpress theme settings reset for ' . get_bloginfo('name');
			$message = '<p>Wordpress settings have been re-initialized for <strong>' . get_bloginfo('name') . '</strong> by <strong>' . $current_user->display_name . ' <' . $current_user->user_email . '></strong> on <strong>' . date('F j, Y') . '</strong> at <strong> ' . date('g:ia') . '</strong>.</p>';

			global $wpdb;
			$query_result = $wpdb->query('SELECT * FROM ' . $wpdb->options, ARRAY_A); //DB Query - Query all WP Options and return as an associative array
			$options_backup_file = get_template_directory() . '/inc/data/options_backup_' . date('Y-m-d\TH:i:s') . '.csv';
			$fp = fopen($options_backup_file, 'w');
			foreach ( $query_result as $row ){ //Loop through the array and write each row to the CSV file
				fputcsv($fp, $row);
			}
			fclose($fp);

			$attachments = array($options_backup_file);

			send_email_to_admins($subject, $message, $attachments);
			unlink($options_backup_file);
		}

		//Create Homepage
		public function initialization_create_homepage(){
			$current_front_page = get_option('page_on_front');
			$sample_page = get_page_by_title('Sample Page');
			if ( empty($current_front_page) || $current_front_page === $sample_page ){
				$new_homepage_id = ( !empty($sample_page) )? $sample_page : 0;
				wp_insert_post(array(
					'ID' => $new_homepage_id,
					'post_type' => 'page',
					'post_title' => 'Home',
					'post_name' => 'home',
					'post_status' => 'publish',
					'post_author' => get_current_user_id(),
				));

				update_option('page_on_front', get_page_by_title('Home'));
				update_option('show_on_front', 'page');
			}
		}

		//Nebula preferred default Wordpress settings
		public function initialization_nebula_defaults($force=false){
			$nebula_defaults_created_option = $this->get_data('defaults_created');

			//If defaults have not been created or if forcing defaults
			if ( empty($nebula_defaults_created_option) || !empty($force) ){
				//Update Nebula default data
				$nebula_data_defaults = $this->default_data();
				update_option('nebula_data', $nebula_data_defaults);

				//Update Nebula default options
				$nebula_options_defaults = $this->default_options();
				update_option('nebula_options', $nebula_options_defaults);
			}
		}

		public function initialization_wp_core_preferred_settings(){
			global $wp_rewrite;

			//Update certain Wordpress Core options
			update_option('blogdescription', ''); //Empty the site tagline
			update_option('timezone_string', 'America/New_York'); //Change Timezone
			update_option('start_of_week', 0); //Start of the week to Sunday
			update_option('permalink_structure', '/%postname%/'); //Set the permalink structure to be "pretty" style
			update_option('default_ping_status', 'closed'); //Close pingbacks and trackbacks by default

			//Prevent unecessary queries with widgets
			add_option('widget_pages', array('_multiwidget' => 1));
			add_option('widget_calendar', array('_multiwidget' => 1));
			add_option('widget_tag_cloud', array('_multiwidget' => 1));
			add_option('widget_nav_menu', array('_multiwidget' => 1));

			//Update certain WordPress user meta values
			$admin_users = get_users(array('role' => 'administrator'));
			foreach ( $admin_users as $user ) {
				update_user_option($user->ID, 'managenav-menuscolumnshidden', array(0 => 'xfn', 1 => 'description'), true); //Set "Screen Options" (values in this array are unchecked and hidden)
			}

			$wp_rewrite->flush_rules();
		}

		//Remove unnecessary plugins bundled with core WordPress
		public function initialization_delete_plugins(){
			//Remove Hello Dolly plugin if it exists
			if ( file_exists(WP_PLUGIN_DIR . '/hello.php') ){
				delete_plugins(array('hello.php'));
			}
		}

		//Deactivate default sidebar widgets.
		public function initialization_deactivate_widgets(){
			update_option('sidebars_widgets', array());
		}

		//Move and activate the Nebula child theme
		public function initialization_activate_child_theme(){
			$theme_name = 'Nebula-Child';
			$source = get_template_directory() . '/' . $theme_name;
			$destination = WP_CONTENT_DIR . '/themes/' . $theme_name;

			//Don't do anything if not an admin user or already using a child theme or if Nebula-Child already exists in the themes directory
			if ( !current_user_can('manage_options') || is_child_theme() || file_exists($destination) ){
				return false;
			}

			//Make sure child theme directory exists inside the parent theme
			if ( file_exists($source) ){
				$this->xcopy($source, $destination); //Copy to the themes directory

				//Activate the child theme
				if ( file_exists($destination) ){ //Make sure copy was successful
					$nebula_child_theme = wp_get_theme($theme_name);
					if ( $nebula_child_theme->exists() ){
						switch_theme($theme_name); //Activate the child theme
						return true; //This triggers a refresh to show the new active theme
					}
				}
			}

			return false;
		}

		public function is_initialized_before(){
			$nebula_initialized_option = $this->get_data('initialized');

			if ( empty($nebula_initialized_option) ){
				return false;
			}

			return true;
		}

		//Check if automated Nebula theme updates are allowed
		public function allow_theme_update(){
			//Check if automated updates have been disabled in Nebula Options
			if ( !$this->get_option('theme_update_notification') ){
				return false;
			}

			return true; //Automated Nebula updates are allowed
		}

		//Force an initialization date.
		public function force_settings(){
			//Force initialization date
			if ( 1 === 2 ){
				$force_date = 'May 24, 2014'; //Set the desired initialization date here. Format should be an easily convertible date like: "March 27, 2012"
				if ( strtotime($force_date) !== false ){ //Check if provided date string is valid
					$this->update_data('initialized', strtotime($force_date));
					return false;
				}
			} else {
				if ( !$this->is_initialized_before() ){
					$this->update_data('initialized', date('U'));
				}
			}

			//Re-allow remote Nebula version updates. Ideally this would be detected automatically and this condition would not be needed.
			if ( 1 === 2 ){
				$this->update_data('scss_last_processed', 0);
				$this->update_data('next_version', '');
				$this->update_data('current_version', $this->version('raw'));
				$this->update_data('current_version_date', $this->version('date'));
				$this->update_data('theme_update_notification', 'enabled');
				update_option('external_theme_updates-Nebula-main', '');
				$this->add_log('Forced initialization occurred.', 6);
			}
		}
	}
}