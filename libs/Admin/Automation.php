<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Automation') ){
	trait Automation {
		public function hooks(){
			//@TODO "Nebula" 0: May want to only trigger this if Nebula is the active theme...

			global $pagenow;

			//Detect and prompt install of Recommended and Optional plugins using TGMPA
			//Configuration Documentation: http://tgmpluginactivation.com/configuration/
			if ( is_admin() && $this->is_dev(true) || current_user_can('manage_options') ){
				require_once(get_template_directory() . '/inc/vendor/class-tgm-plugin-activation.php');

				add_action('tgmpa_register', array($this, 'register_required_plugins'));
			}

			add_action('after_switch_theme', array($this, 'activation_notice'));

			if ( isset($_GET['nebula-initialization']) && $pagenow == 'themes.php' ){ //Or if initializing the theme without AJAX
				add_action('admin_notices', array($this, 'activation'));
			}

			add_action('wp_ajax_nebula_initialization', array($this, 'initialization'));
			add_action('admin_init', array($this, 'set_dates'));

			//add_action('admin_init', array($this, 'force_settings' ), 9); //Uncomment this line to force an initialization date.
		}

		public function register_required_plugins(){
			$plugins = array(
				array(
					'name'	  => 'Custom Post Type UI',
					'slug'	  => 'custom-post-type-ui',
					'required'  => false,
				),
				array(
					'name'	  => 'Contact Form 7',
					'slug'	  => 'contact-form-7',
					'required'  => true,
				),
				array(
					'name'	  => 'Save Contact Form 7',
					'slug'	  => 'save-contact-form-7',
					'required'  => true,
				),
				array(
					'name'	  => 'Advanced Custom Fields',
					'slug'	  => 'advanced-custom-fields',
					'required'  => false,
				),
				array(
					'name'	  => 'EWWW Image Optimizer',
					'slug'	  => 'ewww-image-optimizer',
					'required'  => false,
				),
				array(
					'name'	  => 'Regenerate Thumbnails',
					'slug'	  => 'regenerate-thumbnails',
					'required'  => false,
				),
				array(
					'name'	  => 'W3 Total Cache', //@TODO "Nebula" 0: Find a new caching plugin
					'slug'	  => 'w3-total-cache',
					'required'  => false,
				),
				array(
					'name'	  => 'WP-PageNavi',
					'slug'	  => 'wp-pagenavi',
					'required'  => true,
				),
				array(
					'name'	  => 'Multiple Themes',
					'slug'	  => 'jonradio-multiple-themes',
					'required'  => false,
				),
				array(
					'name'	  => 'TinyMCE Advanced',
					'slug'	  => 'tinymce-advanced',
					'required'  => false,
				),
				array(
					'name'	  => 'WP Mail SMTP',
					'slug'	  => 'wp-mail-smtp',
					'required'  => false,
				),
				array(
					'name'	  => 'WooCommerce',
					'slug'	  => 'woocommerce',
					'required'  => false,
				),
				array(
					'name'	  => 'Wordpress SEO by Yoast',
					'slug'	  => 'wordpress-seo',
					'required'  => true,
				),
				array(
					'name'	  => 'Relevanssi',
					'slug'	  => 'relevanssi',
					'required'  => true,
				),
				array(
					'name'	  => 'Transients Manager',
					'slug'	  => 'transients-manager',
					'required'  => false,
				),
				array(
					'name'	  => 'UpdraftPlus Backup and Restoration',
					'slug'	  => 'updraftplus',
					'required'  => false,
				),
				array(
					'name'	  => 'Wordfence Security',
					'slug'	  => 'wordfence',
					'required'  => false,
				),
				array(
					'name'	  => 'Query Monitor',
					'slug'	  => 'query-monitor',
					'required'  => false,
				),
				array(
					'name'	  => '404 to 301',
					'slug'	  => '404-to-301',
					'required'  => false,
				),
			);

			if ( file_exists(WP_PLUGIN_DIR . '/woocommerce') ){
				array_push($plugins, array(
					'name'	  => 'Enhanced Ecommerce Google Analytics Plugin for WooCommerce',
					'slug'	  => 'enhanced-e-commerce-for-woocommerce-store',
					'required'  => true
				));
			}

			$config = array(
				'id' => 'nebula',
				'strings' => array(
					'notice_can_install_recommended' => _n_noop(
						'The following optional plugin may be needed for the theme: %1$s.',
						'The following optional plugins may be needed for the theme: %1$s.',
						'tgmpa'
					),
					'notice_can_activate_recommended' => _n_noop(
						'The following optional plugin is currently inactive: %1$s.',
						'The following optional plugins are currently inactive: %1$s.',
						'tgmpa'
					),
				)
			);

			tgmpa($plugins, $config);
		}

		//Make sure certain data is always set
		public function set_dates(){
			$first_activation = $this->get_data('first_activation');
			if ( empty($first_activation) ){
				$this->update_data('first_activation', time());
			}
		}

		public function activation_notice(){
			$this->set_dates();
			add_action('admin_notices', array($this, 'activation'));
		}

		public function activation(){
			wp_remote_get('https://gearside.com/nebula/usage/index.php?r=' . home_url('/'));

			//Run express initialization (Nebula Options only)
			if ( !$this->is_initialized_before() ){
				$this->express_automation();
				$this->set_dates();
			}

			$is_ajax_initialization = !isset($_GET['nebula-initialization']); //Detect if non-AJAX initialization is needed. If this $_GET is true, it is not AJAX.
			if ( !$is_ajax_initialization ){
				$this->initialization(false); //@TODO "Nebula" 0: Wrap in a try/catch. In PHP7 fatal errors can be caught!
			}
			?>
			<?php if ( is_child_theme() ): ?>
				<div id='nebula-activate-success' class='updated'>
					<p>
						<strong class="nebula-activated-title">Nebula child theme has been activated.</strong><br />
						<span class="nebula-activated-description">
							Initialization can only be run on the parent theme. If menus were created in the parent theme, they may need to be <a href="nav-menus.php">re-assigned to their corresponding locations</a>.<br />
							<strong>Next step:</strong> Re-activate Nebula (Parent) to initialize, or configure <a href="themes.php?page=nebula_options">Nebula Options</a>
						</span>
					</p>
				</div>
			<?php elseif ( !$is_ajax_initialization && current_user_can('manage_options') ): ?>
				<div id='nebula-activate-success' class='updated'>
					<p>
						<strong class="nebula-activated-title">Nebula has been initialized!</strong><br />
						<span class="nebula-activated-description">
							Options have been updated. The home page has been updated and has been set as the static front page in <a href='options-reading.php'>Settings > Reading</a>.<br />
							<strong>Next step:</strong> Activate Nebula Child (below), or configure <a href='themes.php?page=nebula_options'>Nebula Options</a>
						</span>
					</p>
				</div>
			<?php else: ?>
				<?php $this->render_scss('all'); //Re-render all SCSS files. ?>

				<?php if ( $this->is_initialized_before() ): ?>
					<div id='nebula-activate-success' class='updated'>
						<p>
							<strong class="nebula-activated-title">Nebula has been re-activated!</strong><br />
							<?php if ( current_user_can('manage_options') ): ?>
								<span class="nebula-activated-description">To re-run the automated Nebula initialization process, <a id='run-nebula-initialization' href='themes.php?nebula-initialization=true' style='color: #dd3d36;' title='This will reset some Wordpress core settings and all Nebula options!'>click here</a>.</span>
							<?php else: ?>
								You have re-activated Nebula. Contact the site administrator if the automated Nebula initialization processes need to be re-run.
							<?php endif; ?>
						</p>
					</div>
				<?php else: ?>
					<div id='nebula-activate-success' class='updated'>
						<p>
							<strong class="nebula-activated-title">Nebula has been activated!</strong><br />
							<?php if ( current_user_can('manage_options') ): ?>
								<span class="nebula-activated-description">To run the automated Nebula initialization process, <a id='run-nebula-initialization' href='themes.php?nebula-initialization=true' style='color: #dd3d36;' title='This will reset some Wordpress core settings and all Nebula options!'>click here</a>. If planning on using a Nebula child theme, initialize <strong>before</strong> activating the child theme.</span>
							<?php else: ?>
								You have activated Nebula. Contact the site administrator to run the automated Nebula initialization processes.
							<?php endif; ?>
						</p>
					</div>
				<?php endif; ?>
			<?php endif; ?>
			<?php

			$this->update_data('initialized', time());
			return;
		}

		//Nebula Full Initialization (Triggered by either AJAX or manually)
		public function initialization($ajax=true){
			if ( current_user_can('manage_options') ){
				$this->express_automation();
				$this->full_automation();
				$this->initialization_email_prev_settings();

				if ( !$this->get_data('initialized') ){
					$this->update_data('initialized', time());
				}

				$this->render_scss('all'); //Re-render all SCSS files.

				if ( !empty($ajax) ){ //If AJAX initialization
					echo 'successful-nebula-init'; //AJAX listens for this string to determine sucess.
					wp_die();
				}
			}
		}

		//Automatically set default Nebula options on first Nebula activation
		public function express_automation(){
			$this->initialization_nebula_defaults();
		}

		//Manually update all preferred Nebula and WP Core settings
		public function full_automation(){
			$this->initialization_wp_core_preferred_settings();
			$this->initialization_create_homepage();
			$this->initialization_delete_plugins();
			$this->initialization_deactivate_widgets();
		}

		//Send a list of existing settings to the user's email (to test, trigger the function on admin_init)
		public function initialization_email_prev_settings(){
			$email_admin_timeout = get_transient('nebula_email_admin_timeout');
			if ( !empty($email_admin_timeout) || !$this->is_initialized_before() ){
				return;
			}

			global $wpdb;
			$current_user = wp_get_current_user();
			$to = $current_user->user_email;

			//Carbon copy the admin if reset was done by another user.
			$admin_user_email = $this->get_option('notification_email', $this->get_option('admin_email'));
			if ( $admin_user_email != $current_user->user_email ){
				$headers[] = 'Cc: ' . $admin_user_email;
			}

			$subject = 'Wordpress theme settings reset for ' . get_bloginfo('name');
			$message = '<p>Wordpress settings have been re-initialized for <strong>' . get_bloginfo('name') . '</strong> by <strong>' . $current_user->display_name . ' <' . $current_user->user_email . '></strong> on <strong>' . date('F j, Y') . '</strong> at <strong> ' . date('g:ia') . '</strong>.</p>';








			//@todo "Nebula" 0: Use WPDB here!
			$connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
			$sql = "SELECT * FROM $wpdb->options";
			$result = mysqli_query($connection, $sql);

			$options_backup_file = get_template_directory() . '/inc/data/options_backup_' . date('Y-m-d\TH:i:s') . '.csv';
			$fp = fopen($options_backup_file, 'w');
			while ( $row = mysqli_fetch_assoc($result) ){
				fputcsv($fp, $row);
			}
			fclose($fp);
			mysqli_close($connection);








			$attachments = array($options_backup_file);

			add_filter('wp_mail_content_type', function($content_type){
				return 'text/html';
			});
			wp_mail($to, $subject, $message, $headers, $attachments);
			unlink($options_backup_file);

			set_transient('nebula_email_admin_timeout', 'true', MINUTE_IN_SECONDS*15); //15 minute expiration
		}

		//Create Homepage
		public function initialization_create_homepage(){
			$current_front_page = get_option('page_on_front');
			$sample_page = get_page_by_title('Sample Page');
			if ( empty($current_front_page) || $current_front_page == $sample_page ){
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
		public function initialization_nebula_defaults(){
			//Update Nebula default data
			$nebula_data_defaults = $this->default_data();
			update_option('nebula_data', $nebula_data_defaults);

			//Update Nebula default options
			$nebula_options_defaults = $this->default_options();
			update_option('nebula_options', $nebula_options_defaults);
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

		public function is_initialized_before(){
			$nebula_initialized_option = $this->get_data('initialized');

			if ( empty($nebula_initialized_option) ){
				return false;
			}

			return true;
		}

		//Force an initialization date.
		public function force_settings(){
			//Force initialization date
			if ( 1==2 ){
				$force_date = "May 24, 2014"; //Set the desired initialization date here. Format should be an easily convertable date like: "March 27, 2012"
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
			if ( 1==2 ){
				$this->update_data('version_legacy', 'false');
				$this->update_data('scss_last_processed', 0);
				$this->update_data('next_version', '');
				$this->update_data('current_version', $this->version('raw'));
				$this->update_data('current_version_date', $this->version('date'));
				$this->update_data('theme_update_notification', 'enabled');
				update_option('external_theme_updates-Nebula-master', '');
			}
		}
	}
}