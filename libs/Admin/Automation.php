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
					if ( ($this->get_option('bundled_plugins_notification') || !get_user_meta(get_current_user_id(), 'tgmpa_dismissed_notice_nebula')) || (isset($this->super->get['page']) && str_contains($this->super->get['page'], 'tgmpa')) ){ //If the option is enabled or the user has not dismissed the prompt, or if this page has been specifically requested
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

				add_action('init', array($this, 'schedule_cron_events')); //Add to the WP Cron to regularly delete CF7 Spam/Invalid submissions
				//add_action('nebula_delete_cf7_expired_hook', array($this, 'delete_cf7_expired')); //Moved to nebula.php so WP Cron can run it better. Delete this eventually.
			}
		}

		//When the entire TGMP library is not available (if the user dismissed or disabled the prompt), still provide a link to that admin page. This is only needed when that prompt has been dismissed- otherwise the library creates this link.
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
					array(
						'name' => 'Honeypot for Contact Form 7',
						'slug' => 'contact-form-7-honeypot',
						'required' => true,
					),
					array(
						'name' => 'Contact Form 7 Database Addon - CFDB7', //Backup CF7 DB plugin until (if) the preferred plugin (advanced-cf7-db) is updated
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
			wp_cache_flush(); //Clear the object cache
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
			if ( current_user_can('manage_options') || $this->is_cli() ){
				$this->timer('Full Initialization');
				$this->cli_output('Beginning Nebula Initialization');

				if ( !$this->is_initialized_before() ){
					$this->cli_output('Nebula has not been initialized before for this website');
					$this->update_data('initialized', time());
				} else {
					$this->cli_output('Nebula has been initialized before for this website. Proceeding anyway.', 'warning');
				}

				$this->usage('initialization');
				$this->add_log('Theme settings have been re-initialized.', 7);
				$this->full_automation();
				$this->initialization_email_prev_settings();

				$activated_child = $this->initialization_activate_child_theme();

				$this->scss_controller(true); //Re-render all SCSS files.

				if ( $activated_child && !$this->is_cli() ){
					wp_redirect(admin_url('/themes.php?initialization-success'), 301); //Redirect to show new theme activated
				}

				$this->cli_output('Nebula initialization complete', 'success');
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

			$this->cli_output('Emailing previous settings backup file to administrators');

			$user_output = '';
			if ( $this->is_cli() ){
				$user_output = 'WP-CLI';
			} else {
				$current_user = wp_get_current_user();
				$user_output = $current_user->display_name . ' <' . $current_user->user_email . '>';
			}

			$subject = 'Wordpress theme settings reset for ' . get_bloginfo('name');
			$message = '<p>Wordpress settings have been re-initialized for <strong>' . get_bloginfo('name') . '</strong> by <strong>' . $user_output . '</strong> on <strong>' . date('F j, Y') . '</strong> at <strong> ' . date('g:ia') . '</strong>.</p>';

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
			unlink($options_backup_file); //Delete the backup file after emailing
		}

		//Create Homepage
		public function initialization_create_homepage(){
			$this->cli_output('Creating a home page');

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

		//Create Nebula Options and Data with default values
		public function initialization_nebula_defaults($force=false){
			$this->cli_output('Creating Nebula Options with default values');

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

		//Nebula preferred default Wordpress settings
		public function initialization_wp_core_preferred_settings(){
			$this->cli_output('Updating preferred WordPress settings');

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

			$this->cli_output('Flushing rewrite rules');
			$wp_rewrite->flush_rules();
		}

		//Remove unnecessary plugins bundled with core WordPress
		public function initialization_delete_plugins(){
			//Remove Hello Dolly plugin if it exists
			if ( file_exists(WP_PLUGIN_DIR . '/hello.php') ){
				$this->cli_output('Deleting nonsensical plugins');
				delete_plugins(array('hello.php'));
			}
		}

		//Deactivate default sidebar widgets.
		public function initialization_deactivate_widgets(){
			$this->cli_output('Deactivating default sidebar widgets');
			update_option('sidebars_widgets', array());
		}

		//Move and activate the Nebula child theme
		public function initialization_activate_child_theme(){
			$this->cli_output('Installing the Nebula child theme');

			$theme_name = 'Nebula-Child';
			$source = get_template_directory() . '/' . $theme_name;
			$destination = WP_CONTENT_DIR . '/themes/' . $theme_name;

			//Don't do anything if not an admin user or already using a child theme or if Nebula-Child already exists in the themes directory
			if ( !current_user_can('manage_options') || is_child_theme() || file_exists($destination) ){
				return null;
			}

			//Make sure child theme directory exists inside the parent theme
			if ( file_exists($source) ){
				$this->xcopy($source, $destination); //Copy to the themes directory

				//Activate the child theme
				if ( file_exists($destination) ){ //Make sure copy was successful
					$nebula_child_theme = wp_get_theme($theme_name);
					if ( $nebula_child_theme->exists() ){
						$this->cli_output('Activating the Nebula Child theme');
						switch_theme($theme_name); //Activate the child theme
						return true; //This triggers a refresh to show the new active theme
					}
				}
			}

			return null;
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

		//Schedule cron jobs
		//Place the hook actions related to these in nebula.php
		public function schedule_cron_events(){
			//Add a cron job to regularly delete old invalid/spam posts from nebula_cf7_submits
			if ( !wp_next_scheduled('nebula_delete_cf7_expired_hook') ){
				wp_schedule_event(time(), 'hourly', 'nebula_delete_cf7_expired_hook'); //This initializes the "hook" action which in turn calls the respective function name
			}
		}

 		//Delete old spam CF7 submit posts and limit the number stored
		public function delete_cf7_expired(){ //This is the function that the action references
			//Query posts to be deleted by date
			$posts_to_delete = new WP_Query(array(
				'post_type' => 'nebula_cf7_submits',
				'post_status' => array('spam', 'invalid'),
				'date_query' => array(
					'before' => date('Y-m-d H:i:s', strtotime('-30 days')),
				),
				'fields' => 'ids',
				'posts_per_page' => -1,
				'meta_query' => array(
					array(
						'key' => 'nebula_cf7_submission_preserve',
						'value' => 'on',
						'compare' => 'NOT EXISTS', //Exclude posts that are being preserving
					),
				),
			));

			//Delete expired posts by date
			if ( $posts_to_delete->have_posts() ){
				while ( $posts_to_delete->have_posts() ){
					$posts_to_delete->the_post();

					if ( empty(get_post_meta(get_the_ID(), 'nebula_cf7_submission_preserve', true)) ){ //Only if we are not preserving this post
						wp_delete_post(get_the_ID(), true);
					}
				}

				wp_reset_postdata();
			}

			//Now limit the number of spam posts regardless of date
			$remaining_spam_posts = new WP_Query(array(
				'post_type' => 'nebula_cf7_submits',
				'post_status' => array('spam'),
				'posts_per_page' => -1,
			));

			$num_remaining_spam_posts = $remaining_spam_posts->post_count; //Count the number of spam and invalid posts

			//If there are more than 50 spam posts remaining, delete the oldest ones first
			if ( $num_remaining_spam_posts > 50 ){
				//Order remaining posts by date in ascending order
				$remaining_spam_posts = new WP_Query(array(
					'post_type' => 'nebula_cf7_submits',
					'post_status' => array('spam'),
					'posts_per_page' => $num_remaining_spam_posts-50, //This is the number we want to delete (remaining posts minus how many to keep)
					'orderby' => 'date',
					'order' => 'ASC',
					'meta_query' => array(
						array(
							'key' => 'nebula_cf7_submission_preserve',
							'value' => 'on',
							'compare' => 'NOT EXISTS', //Exclude posts that are being preserving
						),
					),
				));

				//Delete the oldest posts
				if ( $remaining_spam_posts->have_posts() ){
					while ( $remaining_spam_posts->have_posts() ){
						$remaining_spam_posts->the_post();

						if ( empty(get_post_meta(get_the_ID(), 'nebula_cf7_submission_preserve', true)) ){ //Only if we are not preserving this post
							wp_delete_post(get_the_ID(), true);
						}
					}

					wp_reset_postdata();
				}
			}
		}

		//Force an initialization date.
		public function force_settings(){
			//Force initialization date
			if ( 1 === 2 ){
				$force_date = 'May 24, 2014'; //Set the desired initialization date here. Format should be an easily convertible date like: "March 27, 2012"
				if ( strtotime($force_date) !== false ){ //Check if provided date string is valid
					$this->update_data('initialized', strtotime($force_date));
					return null;
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