<?php

//Used to detect if plugins are active. Enables use of is_plugin_active($plugin)
require_once(ABSPATH . 'wp-admin/includes/plugin.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');

//Detect and prompt install of Recommended and Optional plugins
require_once(TEMPLATEPATH . '/includes/class-tgm-plugin-activation.php');

add_action('tgmpa_register', 'my_theme_register_required_plugins'); //@todo: uncomment this
function my_theme_register_required_plugins() {
    $plugins = array(
        array(
            'name'      => 'Admin Menu Tree Page View',
            'slug'      => 'admin-menu-tree-page-view',
            'required'  => true,
        ),
        array(
            'name'      => 'Custom Post Type UI',
            'slug'      => 'custom-post-type-ui',
            'required'  => false,
        ),
        array(
            'name'      => 'Contact Form 7',
            'slug'      => 'contact-form-7',
            'required'  => true,
        ),
        array(
            'name'      => 'Contact Form DB',
            'slug'      => 'contact-form-7-to-database-extension',
            'required'  => true,
        ),
        array(
            'name'      => 'Advanced Custom Fields',
            'slug'      => 'advanced-custom-fields',
            'required'  => false,
        ),
        array(
            'name'      => 'Regenerate Thumbnails',
            'slug'      => 'regenerate-thumbnails',
            'required'  => false,
        ),
        array(
            'name'      => 'W3 Total Cache',
            'slug'      => 'w3-total-cache',
            'required'  => false,
        ),
        array(
            'name'      => 'WP-PageNavi',
            'slug'      => 'wp-pagenavi',
            'required'  => true,
        ),
        array(
            'name'      => 'Custom Facebook Feed',
            'slug'      => 'custom-facebook-feed',
            'required'  => false,
        ),
        array(
            'name'      => 'Really Simple CAPTCHA',
            'slug'      => 'really-simple-captcha',
            'required'  => false,
        ),
        array(
            'name'      => 'TinyMCE Advanced',
            'slug'      => 'tinymce-advanced',
            'required'  => false,
        ),
        array(
            'name'      => 'WP Mail SMTP',
            'slug'      => 'wp-mail-smtp',
            'required'  => false,
        ),
        array(
            'name'      => 'WooCommerce',
            'slug'      => 'woocommerce',
            'required'  => false,
        ),
        array(
            'name'      => 'Wordpress SEO by Yoast',
            'slug'      => 'wordpress-seo',
            'required'  => false,
        ),
        array(
            'name'      => 'Relevanssi',
            'slug'      => 'relevanssi',
            'required'  => false,
        ),
        array(
            'name'      => 'UpdraftPlus Backup and Restoration',
            'slug'      => 'updraftplus',
            'required'  => false,
        ),
    );

    if ( file_exists(WP_PLUGIN_DIR . '/woocommerce') ) {
    	array_push($plugins, array(
    		'name'      => 'WooCommerce Google Analytics Integration',
    		'slug'      => 'woocommerce-google-analytics-integration',
    		'required'  => true
    	));
    }

    $config = array(
        'id'           => 'tgmpa',                 //Unique ID for hashing notices for multiple instances of TGMPA.
        'default_path' => '',                      //Default absolute path to pre-packaged plugins.
        'menu'         => 'tgmpa-install-plugins', //Menu slug.
        'has_notices'  => true,                    //Show admin notices or not.
        'dismissable'  => true,                    //If false, a user cannot dismiss the nag message.
        'dismiss_msg'  => '',                      //If 'dismissable' is false, this message will be output at top of nag.
        'is_automatic' => false,                   //Automatically activate plugins after installation or not.
        'message'      => '',                      //Message to output right before the plugins table.
        'strings'      => array(
            'page_title'                      => __( 'Install Recommended Plugins', 'tgmpa' ),
            'menu_title'                      => __( 'Install Plugins', 'tgmpa' ),
            'installing'                      => __( 'Installing Plugin: %s', 'tgmpa' ), // %s = plugin name.
            'oops'                            => __( 'Something went wrong with the plugin API.', 'tgmpa' ),
            'notice_can_install_required'     => _n_noop( 'Nebula recommends the following plugin: %1$s.', 'Nebula recommends the following plugins: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_can_install_recommended'  => _n_noop( 'The following optional plugin can be installed: %1$s.', 'The following optional plugins can be installed: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_cannot_install'           => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_can_activate_required'    => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_can_activate_recommended' => _n_noop( 'The following optional plugin is currently inactive: %1$s.', 'The following optinal plugins are currently inactive: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_cannot_activate'          => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_ask_to_update'            => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with Nebula: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with Nebula: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_cannot_update'            => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.', 'tgmpa' ), // %1$s = plugin name(s).
            'install_link'                    => _n_noop( 'Begin installing plugin', 'Begin installing plugins', 'tgmpa' ),
            'activate_link'                   => _n_noop( 'Begin activating plugin', 'Begin activating plugins', 'tgmpa' ),
            'return'                          => __( 'Return to Required Plugins Installer', 'tgmpa' ),
            'plugin_activated'                => __( 'Plugin activated successfully.', 'tgmpa' ),
            'complete'                        => __( 'All plugins installed and activated successfully. %s', 'tgmpa' ), // %s = dashboard link.
            'nag_type'                        => 'updated' // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
        )
    );


	if ( is_dev() || current_user_can('manage_options') ){
		tgmpa($plugins, $config);
	}

	/*
		Until there is support for Required, Recommended, AND Optional plugins:
		When updating the class file (in the /includes directory, be sure to edit the text on the following line to be 'Recommended' and 'Optional' in the installation table.
		$table_data[$i]['type'] = isset( $plugin['required'] ) && $plugin['required'] ? __( 'Recommended', 'tgmpa' ) : __( 'Optional', 'tgmpa' );
	*/
}


add_action('after_switch_theme', 'nebula_activation'); //When Nebula has been activated
if ( current_user_can('manage_options') && isset($_GET['nebula-initialization']) && $pagenow == 'themes.php' ){ //Or if initializing the theme without AJAX
	add_action('admin_notices', 'nebula_activation');
}
function nebula_activation(){
	$is_standard_initialization = ( isset($_GET['nebula-initialization']) ) ? true : false; //Detect if non-AJAX initialization is needed. //@TODO: is there any way to do this besides query strings?

	if ( $is_standard_initialization ){
		nebula_initialization(true);
	}
?>
	<?php if ( $is_standard_initialization ): ?>
		<div id='nebula-activate-success' class='updated'>
			<p>
				<strong class="nebula-activated-title">Nebula has been initialized!</strong><br/>
				<span class="nebula-activated-description">
					Settings have been updated. The home page has been updated and has been set as the static front page in <a href='options-reading.php'>Settings > Reading</a>.<br/>
					<strong>Next step:</strong> Configure <a href='themes.php?page=nebula_settings'>Nebula Settings</a>
				</span>
			</p>
		</div>
	<?php else: ?>
		<?php if ( nebula_is_initialized_before() ): ?>
			<div id='nebula-activate-success' class='updated'>
				<p>
					<strong class="nebula-activated-title">Nebula has been re-activated!</strong><br/>
					<?php if ( current_user_can('manage_options') ): ?>
						<span class="nebula-activated-description">To re-run the automated Nebula initialization process, <a id='run-nebula-initialization' href='themes.php?nebula-initialization=true' style='color: #dd3d36;' title='This will reset some Wordpress core settings and all Nebula settings!'>click here</a>.</span>
					<?php else: ?>
						You have re-activated Nebula. Contact the site administrator if the automated Nebula initialization processes need to be re-run.
					<?php endif; ?>
				</p>
			</div>
		<?php else: ?>
			<div id='nebula-activate-success' class='updated'>
				<p>
					<strong class="nebula-activated-title">Nebula has been activated!</strong><br/>
					<?php if ( current_user_can('manage_options') ): ?>
						<span class="nebula-activated-description">To run the automated Nebula initialization process, <a id='run-nebula-initialization' href='themes.php?nebula-initialization=true' style='color: #dd3d36;' title='This will reset some Wordpress core settings and all Nebula settings!'>click here</a>.</span>
					<?php else: ?>
						You have activated Nebula. Contact the site administrator to run the automated Nebula initialization processes.
					<?php endif; ?>
				</p>
			</div>
		<?php endif; ?>
	<?php endif; ?>
<?php
	return;
}


//Nebula Initialization (Triggered by either AJAX or manually)
add_action('wp_ajax_nebula_initialization', 'nebula_initialization');
function nebula_initialization($standard=null){
	if ( current_user_can('manage_options') ){

		nebula_initialization_email_prev_settings();

		nebual_initialization_create_homepage();
		nebula_initialization_default_settings();
		nebula_initialization_delete_plugins();
		nebula_initialization_set_install_date();

		if ( empty($standard) ){ //If AJAX initialization
			echo '1';
			exit;
		}
	}
}


//Send a list of existing settings to the user's email (to test, trigger the function on admin_init)
function nebula_initialization_email_prev_settings(){
	$email_admin_timeout = get_transient('nebula_email_admin_timeout');
	if ( !empty($email_admin_timeout) || !nebula_is_initialized_before() ){
		return;
	}

	global $wpdb;
	$current_user = wp_get_current_user();
	$to = $current_user->user_email;
	$headers[] = 'From: ' . get_bloginfo('name');

	//Carbon copy the admin if reset was done by another user.
	$admin_user_email = nebula_settings_conditional_text('nebula_contact_email', get_option('admin_email'));
	if ( $admin_user_email != $current_user->user_email ) {
		$headers[] = 'Cc: ' . $admin_user_email;
	}

	$subject = 'Wordpress theme settings reset for ' . get_bloginfo('name');
	$message = '<p>Wordpress theme settings have been reset for <strong>' . get_bloginfo('name') . '</strong> by <strong>' . $current_user->display_name . ' <' . $current_user->user_email . '></strong> on <strong>' . date('F j, Y') . '</strong> at <strong> ' . date('g:ia') . '</strong>.</p><p>Below is a record of the previous settings prior to the reset for backup purposes:</p>';
	$message .= '<table style="width: 100%;>';

	$options = $wpdb->get_results("SELECT * FROM $wpdb->options ORDER BY option_name");
	foreach ( $options as $option ) {
		if ( $option->option_name != '' ) {
			if ( is_serialized($option->option_value) ) {
				if ( is_serialized_string($option->option_value) ) {
					$value = maybe_unserialize($option->option_value);
					$options_to_update[] = $option->option_name;
				} else {
					$value = 'SERIALIZED DATA';
				}
			} else {
				$value = $option->option_value;
				$options_to_update[] = $option->option_name;
			}
			$message .= '<tr><td style="width: 40%; min-width: 330px;">';

			if ( strpos(esc_html($option->option_name), 'nebula') !== false ) {
				$message .= '<strong style="color: #0098d7;">' . esc_html($option->option_name) . '</strong>';
			} else {
				$message .= '<strong>' . esc_html($option->option_name) . '</strong>';
			}

			$message .=	'</td><td style="width: 60%;">';
			if ( strpos($value, "\n") !== false ) {
				$message .= '<textarea rows="5" style="width: 95%; resize: vertical;">' . esc_textarea($value) . '</textarea>';
			} else {
				$message .= '<input type="text" value="' . esc_attr($value) . '" style="width: 95%;" />';
			}
			$message .= '</td></tr>';
		}
	}
	$message .= '</table>';

	//Set the content type to text/html for the email. Don't forget to reset after wp_mail()!
	add_filter('wp_mail_content_type', 'set_html_content_type');
	function set_html_content_type() {
		return 'text/html';
	}
	wp_mail($to, $subject, $message, $headers);
	remove_filter('wp_mail_content_type', 'set_html_content_type'); //This resets the content type for the email.

	set_transient('nebula_email_admin_timeout', 'true', 60*15); //15 minute expiration
}


//Create Homepage
function nebual_initialization_create_homepage(){
	$nebula_home = array(
		'ID' => 1,
		'post_type' => 'page',
		'post_title' => 'Home',
		'post_name' => 'home',
		'post_content'   => "Nebula is a springboard WordPress theme framework for developers. Like other WordPress startup themes, it has custom functionality built-in (like shortcodes, styles, and JS/PHP functions), but unlike other themes the WP Nebula is not meant for the end-user.

Wordpress developers will find all source code not obfuscated, so everything may be customized and altered to fit the needs of the project. Additional comments have been added to help explain what is happening; not only is this framework great for speedy development, but it is also useful for learning advanced Wordpress techniques.",
		'post_status' => 'publish',
		'post_author' => 1,
		'page_template' => 'tpl-homepage.php'
	);
	wp_insert_post($nebula_home); //Insert the post into the database

	$nebula_homepage = get_page_by_title('Home');
	update_option('page_on_front', $nebula_homepage->ID); //Or set the second parameter to '1'.
	update_option('show_on_front', 'page');
}


//Nebula preferred default Wordpress settings
function nebula_initialization_default_settings(){
	global $wp_rewrite;

	//Update Nebula settings options
	update_option('nebula_overall', 'Enabled');
	update_option('nebula_domain_expiration_alert', 'Never');
	update_option('nebula_edited_yet', 'false');
	update_option('nebula_domain_expiration_alert', 'Default');

	update_option('nebula_site_owner', '');
	update_option('nebula_contact_email', '');
	update_option('nebula_ga_tracking_id', '');
	update_option('nebula_hostnames', '');
	update_option('nebula_keywords', '');
	update_option('nebula_news_keywords', '');
	update_option('nebula_phone_number', '');
	update_option('nebula_fax_number', '');
	update_option('nebula_latitude', '');
	update_option('nebula_longitude', '');
	update_option('nebula_street_address', '');
	update_option('nebula_locality', '');
	update_option('nebula_region', '');
	update_option('nebula_postal_code', '');
	update_option('nebula_country_name', '');

	update_option('nebula_business_hours_sunday_enabled', '');
	update_option('nebula_business_hours_sunday_open', '');
	update_option('nebula_business_hours_sunday_close', '');
	update_option('nebula_business_hours_monday_enabled', '');
	update_option('nebula_business_hours_monday_open', '');
	update_option('nebula_business_hours_monday_close', '');
	update_option('nebula_business_hours_tuesday_enabled', '');
	update_option('nebula_business_hours_tuesday_open', '');
	update_option('nebula_business_hours_tuesday_close', '');
	update_option('nebula_business_hours_wednesday_enabled', '');
	update_option('nebula_business_hours_wednesday_open', '');
	update_option('nebula_business_hours_wednesday_close', '');
	update_option('nebula_business_hours_thursday_enabled', '');
	update_option('nebula_business_hours_thursday_open', '');
	update_option('nebula_business_hours_thursday_close', '');
	update_option('nebula_business_hours_friday_enabled', '');
	update_option('nebula_business_hours_friday_open', '');
	update_option('nebula_business_hours_friday_close', '');
	update_option('nebula_business_hours_saturday_enabled', '');
	update_option('nebula_business_hours_saturday_open', '');
	update_option('nebula_business_hours_saturday_close', '');
	update_option('nebula_business_hours_closed', '');

	update_option('nebula_facebook_url', '');
	update_option('nebula_facebook_app_id', '');
	update_option('nebula_facebook_app_secret', '');
	update_option('nebula_facebook_access_token', '');
	update_option('nebula_facebook_page_id', '');
	update_option('nebula_facebook_admin_ids', '');
	update_option('nebula_google_plus_url', '');
	update_option('nebula_twitter_url', '');
	update_option('nebula_twitter_bearer_token', '');
	update_option('nebula_linkedin_url', '');
	update_option('nebula_youtube_url', '');
	update_option('nebula_instagram_url', '');

	update_option('nebula_wireframing', 'Default');
	update_option('nebula_admin_bar', 'Default');
	update_option('nebula_author_bios', 'Default');
	update_option('nebula_comments', 'Default');
	update_option('nebula_disqus_shortname', '');
	update_option('nebula_wp_core_updates_notify', 'Default');
	update_option('nebula_plugin_update_warning', 'Default');
	update_option('nebula_welcome_panel', 'Default');
	update_option('nebula_unnecessary_metaboxes', 'Default');
	update_option('nebula_dev_metabox', 'Default');
	update_option('nebula_todo_metabox', 'Default');
	update_option('nebula_domain_exp', 'Default');
	update_option('nebula_dev_stylesheets', 'Default');
	update_option('nebula_console_css', 'Default');
	update_option('nebula_cse_id', '');
	update_option('nebula_cse_api_key', '');

	update_option('nebula_dev_ip', '');
	update_option('nebula_dev_email_domain', '');
	update_option('nebula_client_ip', '');
	update_option('nebula_client_email_domain', '');
	update_option('nebula_cpanel_url', '');
	update_option('nebula_hosting_url', '');
	update_option('nebula_registrar_url', '');
	update_option('nebula_ga_url', '');
	update_option('nebula_google_webmaster_tools_url', '');
	update_option('nebula_google_adsense_url', '');
	update_option('nebula_mention_url', '');

	//Update certain Wordpress Core options
	update_option('blogdescription', ''); //Empty the site tagline
	update_option('timezone_string', 'America/New_York'); //Change Timezone
	update_option('start_of_week', 0); //Start of the week to Sunday
	update_option('permalink_structure', '/%postname%/'); //Set the permalink structure to be "pretty" style

	//Prevent unecessary queries with widgets
	add_option('widget_pages', array('_multiwidget' => 1));
	add_option('widget_calendar', array('_multiwidget' => 1));
	add_option('widget_tag_cloud', array('_multiwidget' => 1));
	add_option('widget_nav_menu', array('_multiwidget' => 1));

	//Update certain WordPress user meta values
	//@TODO "Nebula" 0: Check the "CSS Classes" checkbox under "Screen Options" on the Appearance > Menus page so they are always visible on each menu item.

	$wp_rewrite->flush_rules();
}


function nebula_initialization_delete_plugins(){
	//Remove Hello Dolly plugin if it exists
	if ( file_exists(WP_PLUGIN_DIR . '/hello.php') ) {
        delete_plugins(array('hello.php'));
    }
}


function nebula_is_initialized_before(){
	$nebula_initialized_option = get_option('nebula_initialized');
	$nebula_initialized_date = date_parse($nebula_initialized_option);

	//If the nebula_initialized option is empty -or- the parsed string error count is greater than 2 (known "errors" are accounted for) -or- the option has a PHP warning or error in it.
	if ( empty($nebula_initialized_option) || $nebula_initialized_date["error_count"] > 2 || contains(strtolower($nebula_initialized_option), array('fatal', 'warning', 'error', 'on line')) ){
		return false;
	}

	return true;
}

//add_action('admin_init', 'nebula_initialization_set_install_date'); //Uncomment this line to force an initialization date.
function nebula_initialization_set_install_date(){
	if ( 1==2 ) { //Set to true to force an initialization date (in case of some kind of accidental reset).
		$force_date = "May 24, 2014"; //Set the desired initialization date here. Format should be an easily convertable date like: "March 27, 2012"
		if ( strtotime($force_date) !== false ) { //Check if provided date string is valid
			update_option('nebula_initialized', strtotime($force_date));
			return false;
		}
	} else {
		if ( !nebula_is_initialized_before() ){
			update_option('nebula_initialized', date('U'));
		}
	}
}