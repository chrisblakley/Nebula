<?php

//Used to detect if plugins are active. Enabled use of is_plugin_active($plugin)
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
            'name'      => 'WP Smush.it',
            'slug'      => 'wp-smushit',
            'required'  => false,
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
            'name'      => 'Ultimate TinyMCE',
            'slug'      => 'ultimate-tinymce',
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
            'name'      => 'Theme Check',
            'slug'      => 'theme-check',
            'required'  => false,
        ),
    );

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
            'notice_can_install_required'     => _n_noop( 'WP Nebula recommends the following plugin: %1$s.', 'WP Nebula recommends the following plugins: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_can_install_recommended'  => _n_noop( 'The following optional plugin can be installed: %1$s.', 'The following optional plugins can be installed: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_cannot_install'           => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_can_activate_required'    => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_can_activate_recommended' => _n_noop( 'The following optional plugin is currently inactive: %1$s.', 'The following optinal plugins are currently inactive: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_cannot_activate'          => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_ask_to_update'            => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with WP Nebula: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with WP Nebula: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_cannot_update'            => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.', 'tgmpa' ), // %1$s = plugin name(s).
            'install_link'                    => _n_noop( 'Begin installing plugin', 'Begin installing plugins', 'tgmpa' ),
            'activate_link'                   => _n_noop( 'Begin activating plugin', 'Begin activating plugins', 'tgmpa' ),
            'return'                          => __( 'Return to Required Plugins Installer', 'tgmpa' ),
            'plugin_activated'                => __( 'Plugin activated successfully.', 'tgmpa' ),
            'complete'                        => __( 'All plugins installed and activated successfully. %s', 'tgmpa' ), // %s = dashboard link.
            'nag_type'                        => 'updated' // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
        )
    );

    tgmpa($plugins, $config);
	
	/* 
		Until there is support for Required, Recommended, AND Optional plugins:
		When updating the class file (in the /includes directory, be sure to edit the text on the following line to be 'Recommended' and 'Optional' in the installation table.
		$table_data[$i]['type'] = isset( $plugin['required'] ) && $plugin['required'] ? __( 'Recommended', 'tgmpa' ) : __( 'Optional', 'tgmpa' );
	*/
}

//When WP Nebula has been activated
add_action('after_switch_theme', 'nebulaActivation');
function nebulaActivation() {
	$theme = wp_get_theme();
	//Check if this is the initial activation, or if initialization has been ran before (and the user is just toggling themes)
	if ( $theme['Name'] == 'WP Nebula' && (get_post_meta(1, '_wp_page_template', 1) != 'tpl-homepage.php' || isset($_GET['nebula-reset'])) ) {

		//Create Homepage
		$nebula_home = array(
			'ID' => 1,
			'post_type' => 'page',
			'post_title' => 'Home',
			'post_name' => 'home',
			'post_content'   => "The WP Nebula is a springboard Wordpress theme for developers. Inspired by the HTML5 Boilerplate, this theme creates the framework for development. Like other Wordpress startup themes, it has custom functionality built-in (like shortcodes, styles, and JS/PHP functions), but unlike other themes the WP Nebula is not meant for the end-user.

Wordpress developers will find all source code not obfuscated, so everything may be customized and altered to fit the needs of the project. Additional comments have been added to help explain what is happening; not only is this framework great for speedy development, but it is also useful for learning advanced Wordpress techniques.",
			'post_status' => 'publish',
			'post_author' => 1,
			'page_template' => 'tpl-homepage.php'
		);
		
		//Insert the post into the database
		wp_insert_post($nebula_home);
		
		//Show the Activation Complete message
		add_action('admin_notices', 'nebulaActivateComplete');
		
		//Change some Wordpress settings
		add_action('init', 'nebulaWordpressSettings');
	
	}
	return;
}

//When Nebula "Reset" has been clicked
if ( current_user_can('manage_options') && isset($_GET['nebula-reset']) ) {
	nebulaActivation();
	nebulaChangeHomeSetting();
	add_action('init', 'nebulaWordpressSettings');
}

//If WP Nebula has been activated and other actions have heppened, but the user is still on the Themes settings page.
if ( current_user_can('manage_options') && isset($_GET['activated'] ) && $pagenow == 'themes.php' ) {
	$theme = wp_get_theme();
	if ( $theme['Name'] == 'WP Nebula' ) {
		add_action('admin_notices', 'nebulaActivateComplete');
	}
}

//Set the front page to static > Home.
function nebulaChangeHomeSetting(){
	$nebula_homepage = get_page_by_title('Home');
	update_option('page_on_front', $nebula_homepage->ID); //Or set this to ...(..., '1');
	update_option('show_on_front', 'page');
}

//Nebula preferred default Wordpress settings
function nebulaWordpressSettings() {
	global $wp_rewrite;
	
	remove_core_bundled_plugins();
	
	//Update Nebula Settings //@TODO: ADD THE REST!
	update_option('nebula_ga_tracking_id', '');
	update_option('nebula_admin_bar', 'disabled');
	
	//Empty the site tagline
	update_option('blogdescription', '');
	
	//Change Timezone
	update_option('timezone_string', 'America/New_York');
	
	//Start of the week to Sunday
	update_option('start_of_week', 0);

	//Set the permalink structure to be "pretty" style
	update_option('permalink_structure', '/%postname%/');
	$wp_rewrite->flush_rules();
}

add_action('admin_init', 'remove_core_bundled_plugins');
function remove_core_bundled_plugins(){
	//Remove Hello Dolly plugin if it exists
	if ( file_exists(WP_PLUGIN_DIR . '/hello.php') ) {
        delete_plugins(array('hello.php'));
    }
}

function nebulaActivateComplete(){
	if ( isset($_GET['nebula-reset']) ) {
		echo "<div id='nebula-activate-success' class='updated'><p><strong>WP Nebula has been reset!</strong><br/>You have reset WP Nebula. Settings have been updated! The Home page has been updated. It has been set as the static frontpage in <a href='options-reading.php'>Settings > Reading</a>.</p></div>";
	} elseif ( get_post_meta(1, '_wp_page_template', 1) == 'tpl-homepage.php' ) {
		echo "<div id='nebula-activate-success' class='updated'><p><strong>WP Nebula has been re-activated!</strong><br/>Settings have <strong>not</strong> been changed. The Home page already exists, so it has <strong>not</strong> been updated. Make sure it is set as the static front page in <a href='options-reading.php'>Settings > Reading</a>. <a href='themes.php?activated=true&nebula-reset=true' style='float: right; color: red;'>Re-initialize Nebula.</a></p></div>";
	} else {
		echo "<div id='nebula-activate-success' class='updated'><p><strong>WP Nebula has been activated!</strong><br/>Permalink structure has been updated. A new Home page has been created. It has been set as the static frontpage in <a href='options-reading.php'>Settings > Reading</a>.</p></div>";
	}
}