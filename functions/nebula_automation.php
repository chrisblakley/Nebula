<?php

//Used to detect if plugins are active. Enables use of is_plugin_active($plugin)
require_once(ABSPATH . 'wp-admin/includes/plugin.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');

//Detect and prompt install of Recommended and Optional plugins
require_once(TEMPLATEPATH . '/includes/class-tgm-plugin-activation.php');

add_action('tgmpa_register', 'my_theme_register_required_plugins');
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
            'name'      => 'Search Everything',
            'slug'      => 'search-everything',
            'required'  => false,
        ),
        array(
            'name'      => 'UpdraftPlus Backup and Restoration',
            'slug'      => 'updraftplus',
            'required'  => false,
        ),
/*
        array(
            'name'      => 'Theme Check',
            'slug'      => 'theme-check',
            'required'  => false,
        ),
*/
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


	if ( is_dev() || get_current_user_id() == 1 ) {
		tgmpa($plugins, $config);
	}

	/*
		Until there is support for Required, Recommended, AND Optional plugins:
		When updating the class file (in the /includes directory, be sure to edit the text on the following line to be 'Recommended' and 'Optional' in the installation table.
		$table_data[$i]['type'] = isset( $plugin['required'] ) && $plugin['required'] ? __( 'Recommended', 'tgmpa' ) : __( 'Optional', 'tgmpa' );
	*/
}

//When Nebula has been activated
add_action('after_switch_theme', 'nebulaActivation');
function nebulaActivation() {
	$theme = wp_get_theme();
	$GLOBALS['nebula_initial_activate'] = 0;

	//Check if this is the initial activation, or if initialization has been ran before (and the user is just toggling themes)
	if ( (get_post_meta(1, '_wp_page_template', 1) != 'tpl-homepage.php' || isset($_GET['nebula-initialized'])) ) {
		$GLOBALS['nebula_initial_activate'] = 1;
		add_action('admin_notices', 'nebulaActivateComplete'); //Queue the activation complete message
	}

	return;
}

function nebulaActivateComplete(){
	set_nebula_initialized_date();

	if ( current_user_can('manage_options') ) :
		if ( isset($_GET['nebula-initialized']) ) : //If nebula has been initialized ?>
			<div id='nebula-activate-success' class='updated'>
				<p>
					<strong>Nebula has been initialized!</strong><br/>
					You have initialized Nebula. Settings have been updated! The Home page has been updated. It has been set as the static frontpage in <a href='options-reading.php'>Settings > Reading</a>.<br/>
					<strong>Next step:</strong> Configure <a href='themes.php?page=nebula_settings'>Nebula Settings</a>.
				</p>
			</div>
		<?php elseif ( $GLOBALS['nebula_initial_activate'] == 0 ) : //If Nebula is being re-activated. ?>
			<div id='nebula-activate-success' class='updated'>
				<p>
					<strong>Nebula has been re-activated!</strong><br/>
					Settings have <strong>not</strong> been changed. The Home page already exists, so it has <strong>not</strong> been updated. Make sure it is set as the static front page in <a href='options-reading.php'>Settings > Reading</a>.<br/><strong>Next step:</strong> Verify <a href='themes.php?page=nebula_settings'>Nebula Settings</a>.<a class='reinitializenebula' href='themes.php?activated=true&nebula-initialized=true' style='float: right; color: #dd3d36;' title='This will reset some Wordpress Settings and all Nebula Settings!'><i class='fa fa-exclamation-triangle'></i> Re-initialize Nebula.</a>
				</p>
			</div>
		<?php else : //If Nebula has been activated for the first time. ?>
			<div id='nebula-activate-success' class='updated'>
				<p>
					<strong>Nebula has been activated!</strong><br/>
					To run the automated Nebula Initialization process, <a class='reinitializenebula' href='themes.php?activated=true&nebula-initialized=true' style='color: #dd3d36;' title='This will reset some Wordpress Settings and all Nebula Settings!'>click here!</a>
				</p>
			</div>
		<?php endif;
	else : //If Nebula has been activated or reactivated by a non-admin. ?>
		<div id='nebula-activate-success' class='updated'>
			<p>
				<strong>Nebula has been activated!</strong><br/>
				You have activated Nebula. Contact the site administrator to run the automated Nebula initialization processes.
			</p>
		</div>
	<?php endif;
}

function nebula_initialization(){
	mail_existing_settings(); //Email the existing settings to the admin for backup/documentation purposes.

	//Create Homepage
	$nebula_home = array(
		'ID' => 1,
		'post_type' => 'page',
		'post_title' => 'Home',
		'post_name' => 'home',
		'post_content'   => "Nebula is a springboard Wordpress theme for developers. Inspired by the HTML5 Boilerplate, this theme creates the framework for development. Like other Wordpress startup themes, it has custom functionality built-in (like shortcodes, styles, and JS/PHP functions), but unlike other themes Nebula is not meant for the end-user.

Wordpress developers will find all source code not obfuscated, so everything may be customized and altered to fit the needs of the project. Additional comments have been added to help explain what is happening; not only is this framework great for speedy development, but it is also useful for learning advanced Wordpress techniques.",
		'post_status' => 'publish',
		'post_author' => 1,
		'page_template' => 'tpl-homepage.php'
	);
	wp_insert_post($nebula_home); //Insert the post into the database

	$nebula_homepage = get_page_by_title('Home');
	update_option('page_on_front', $nebula_homepage->ID); //Or set the second parameter to '1'.
	update_option('show_on_front', 'page');

	//Change some Wordpress settings
	default_nebula_settings(); //Removed add_action? Not sure if entirely necessary. Does 'after_switch_theme' trigger after init? Verify this works sometime.

	nebulaActivation(); //Re-queue the theme switch logic
}


//Send a list of existing settings to the user's email (to test, trigger the function on admin_init)
function mail_existing_settings(){
	global $wpdb;
	$current_user = wp_get_current_user();
	$to = $current_user->user_email;
	$headers[] = 'From: ' . get_bloginfo('name');

	//Carbon copy the admin if reset was done by another user.
	$admin_user_email = nebula_settings_conditional_text('nebula_contact_email', get_option('admin_email', $admin_user->user_email));
	if ( $admin_user_email != $current_user->user_email ) {
		$headers[] = 'Cc: ' . $admin_user_email; //@TODO "Nebula" 0: Email all admins?
	}

	$subject = 'Wordpress theme settings reset for ' . get_bloginfo('name');
	$message = '
		<p>Wordpress theme settings have been reset for <strong>' . get_bloginfo('name') . '</strong> by <strong>' . $current_user->display_name . ' <' . $current_user->user_email . '></strong> on <strong>' . date('F j, Y') . '</strong> at <strong> ' . date('g:ia') . '</strong>.</p><p>Below is a record of the previous settings prior to the reset for backup purposes:</p>';
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
}

//If Nebula has been activated and other actions have happened, but the user is still on the Themes settings page.
if ( current_user_can('manage_options') && isset($_GET['activated'] ) && $pagenow == 'themes.php' ) {
	add_action('admin_notices', 'nebulaActivateComplete');
}

//Set the front page to static > Home.
function nebulaChangeHomeSetting(){
	$nebula_homepage = get_page_by_title('Home');
	update_option('page_on_front', $nebula_homepage->ID); //Or set the second parameter to '1'.
	update_option('show_on_front', 'page');
}

//Nebula preferred default Wordpress settings
function nebulaWordpressSettings() {
	global $wp_rewrite;
	remove_core_bundled_plugins();
	set_nebula_initialized_date();


	//Update Nebula Settings
	update_option('nebula_overall', 'Enabled');
	update_option('nebula_domain_expiration_alert', 'Never');
	update_option('nebula_edited_yet', 'false');
	update_option('nebula_domain_expiration_alert', 'Default');

	update_option('nebula_site_owner', '');
	update_option('nebula_contact_email', '');
	update_option('nebula_ga_tracking_id', '');
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
	update_option('nebula_author_bio_pages', 'Default');
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

	//Update certain WordPress user meta values
	//@TODO "Nebula" 0: Check the "CSS Classes" checkbox under "Screen Options" on the Appearance > Menus page so they are always visible on each menu item.

	$wp_rewrite->flush_rules();
}

//Uninstall/Delete unnecessary bundled plugins
add_action('admin_init', 'remove_core_bundled_plugins');
function remove_core_bundled_plugins(){
	//Remove Hello Dolly plugin if it exists
	if ( file_exists(WP_PLUGIN_DIR . '/hello.php') ) {
        delete_plugins(array('hello.php'));
    }
}

function set_nebula_initialized_date(){
	if ( 1==2 ) { //Set to true to force an initialization date (in case of some kind of accidental reset).
		$force_date = "May 24, 2014"; //Set the desired initialization date here. Format should be an easily convertable date like: "March 27, 2012"
		if ( strtotime($force_date) !== false ) { //Check if provided date string is valid
			update_option('nebula_initialized', strtotime($force_date));
			return false;
		}
	} else {
		$nebula_initialized_date = date_parse(get_option('nebula_initialized'));

		//If the nebula_initialized option is not set -or- set as an empty string -or- the parsed string error count is greater than 2 (known "errors" are accounted for) -or- the option has a PHP warning or error in it.
		if ( get_option('nebula_initialized') === null || get_option('nebula_initialized') == '' || $nebula_initialized_date["error_count"] > 2 || contains(strtolower(get_option('nebula_initialized')), array('fatal', 'warning', 'error', 'on line')) ) {
			update_option('nebula_initialized', date('U'));
		}
	}
}