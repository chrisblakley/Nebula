<?php

//Used to detect if plugins are active. Enables use of is_plugin_active($plugin)
require_once(ABSPATH . 'wp-admin/includes/plugin.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');

//Detect and prompt install of Recommended and Optional plugins
require_once(TEMPLATEPATH . '/includes/libs/class-tgm-plugin-activation.php');
add_action('tgmpa_register', 'my_theme_register_required_plugins');
function my_theme_register_required_plugins(){
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

    if ( file_exists(WP_PLUGIN_DIR . '/woocommerce') ){
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

//When Nebula has been activated
add_action('after_switch_theme', 'nebula_activation_notice');
function nebula_activation_notice(){
	add_action('admin_notices', 'nebula_activation');
}
if ( isset($_GET['nebula-initialization']) && $pagenow == 'themes.php' ){ //Or if initializing the theme without AJAX
	add_action('admin_notices', 'nebula_activation');
}
function nebula_activation(){
	$is_standard_initialization = ( isset($_GET['nebula-initialization']) )? true : false; //Detect if non-AJAX initialization is needed.

	if ( $is_standard_initialization ){
		//@TODO "Nebula" 0: Wrap in a try/catch. In PHP7 fatal errors can be caught!
		nebula_initialization(true);
	}
?>
	<?php if ( $is_standard_initialization && current_user_can('manage_options') ): ?>
		<div id='nebula-activate-success' class='updated'>
			<p>
				<strong class="nebula-activated-title">Nebula has been initialized!</strong><br />
				<span class="nebula-activated-description">
					Options have been updated. The home page has been updated and has been set as the static front page in <a href='options-reading.php'>Settings > Reading</a>.<br />
					<strong>Next step:</strong> Configure <a href='themes.php?page=nebula_options'>Nebula Options</a>
				</span>
			</p>
		</div>
	<?php else: ?>
		<?php nebula_render_scss('all'); //Re-render all SCSS files. ?>

		<?php if ( nebula_is_initialized_before() ): ?>
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
						<span class="nebula-activated-description">To run the automated Nebula initialization process, <a id='run-nebula-initialization' href='themes.php?nebula-initialization=true' style='color: #dd3d36;' title='This will reset some Wordpress core settings and all Nebula options!'>click here</a>.</span>
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
		nebula_initialization_deactivate_widgets();
		nebula_initialization_set_install_date();
		nebula_render_scss('all'); //Re-render all SCSS files.

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
	$admin_user_email = get_option('nebula_contact_email', get_option('admin_email'));
	if ( $admin_user_email != $current_user->user_email ){
		$headers[] = 'Cc: ' . $admin_user_email;
	}

	$subject = 'Wordpress theme settings reset for ' . get_bloginfo('name');
	$message = '<p>Wordpress theme settings have been reset for <strong>' . get_bloginfo('name') . '</strong> by <strong>' . $current_user->display_name . ' <' . $current_user->user_email . '></strong> on <strong>' . date('F j, Y') . '</strong> at <strong> ' . date('g:ia') . '</strong>.</p><p>Below is a record of the previous settings prior to the reset for backup purposes:</p>';
	$message .= '<table style="width: 100%;>';

	$options = $wpdb->get_results("SELECT * FROM $wpdb->options ORDER BY option_name");
	foreach ( $options as $option ){
		if ( $option->option_name != '' ){
			if ( is_serialized($option->option_value) ){
				if ( is_serialized_string($option->option_value) ){
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

			if ( strpos(esc_html($option->option_name), 'nebula') !== false ){
				$message .= '<strong style="color: #0098d7;">' . esc_html($option->option_name) . '</strong>';
			} else {
				$message .= '<strong>' . esc_html($option->option_name) . '</strong>';
			}

			$message .=	'</td><td style="width: 60%;">';
			if ( strpos($value, "\n") !== false ){
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
	function set_html_content_type(){
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
		'post_content'   => "<h2>Out of the Box</h2>
<a href='http://gearside.com/nebula/' target='_blank'>Nebula</a> is a springboard WordPress theme framework for developers. Like other WordPress startup themes, it has custom functionality built-in (like shortcodes, styles, and JS/PHP functions), but unlike other themes Nebula is not meant for the end-user.

Wordpress developers will find all source code not obfuscated, so everything may be customized and altered to fit the needs of the project. Additional comments have been added to help explain what is happening; not only is this framework great for speedy development, but it is also useful for learning advanced Wordpress techniques.
<h2><a href='http://gearside.com/nebula/documentation/custom-functionality/'>Custom Functionality</a></h2>
Unlike most starter frameworks, Nebula uses a subtractive approach so many advanced feature-sets are included from the start and can be used as needed or removed entirely. This allows for a starting point for WordPress websites that is further ahead of other frameworks. Features like the <a href='https://gearside.com/nebula/documentation/bundled/page-suggestions/'>recommendation engine</a> for 404 pages and no-search-result pages, or <a href='https://gearside.com/nebula/documentation/custom-functionality/autocomplete-search/'>Autocomplete Search</a> are bundled without needing to write additional code.
<h2><a href='http://gearside.com/nebula/documentation/examples/'>Example Snippets</a></h2>
Nebula works as a great code repository for those learning web development. The <a href='https://github.com/chrisblakley/Nebula/tree/master/examples/includes' target='_blank'>Examples directory</a> has documentation and code snippets for custom functionality that can be cross-referenced against instructions on this website (or use the comments within the files themselves). Examples range from <a href='https://gearside.com/nebula/documentation/examples/basic-wp-query/'>basic WordPress queries</a> to advanced <a href='https://gearside.com/nebula/documentation/experimental/speech-recognition-api/'>Speech Recognition</a>.
<h2><a href='https://gearside.com/nebula/documentation/options/'>Options</a></h2>
Nebula has a <a href='https://gearside.com/nebula/documentation/options/'>simple options page</a> where data can be stored from the WordPress admin without needing access to the code templates. This page allows for certain features to be enabled/disabled easily as well as for common site utilities to be noted for easy-access from the WordPress dashboard. Nebula also includes a one-click <a href='https://gearside.com/nebula/documentation/custom-functionality/automated-initialization/'>automated initialization</a> that is accessed during theme activation. This will automatically modify WordPress settings to work best with Nebula!
<h2>Security</h2>
Built-in security functions are bundled with the Nebula framework. Security precautions are <a href='https://gearside.com/nebula/documentation/bundled/google-analytics-custom-event-tracking/'>logged in Google Analytics</a> to bring attention to what type of attempt has been prevented. These also block <a href='https://gearside.com/nebula/common-referral-spambots/'>known spambots and blacklisted domains</a> (and automatically update).
<h2>Plugins</h2>
Hand-selected plugins are bundled with Nebula to ensure the best feature set. Plugins are recommended after theme activation so that the most up-to-date versions are always included. Plugins associated with Nebula are constantly vetted to make sure they are the best available.
<h2><a href='http://gearside.com/nebula/documentation/utilities/'>Utilities</a></h2>
This website has utilities to use in real-time without needing to do any coding to implement them (though, they are still bundled with the Nebula install). This allows for things like <a href='https://gearside.com/nebula/documentation/utilities/twitter-bearer-token-generator/'>bearer token generation</a> or <a href='https://gearside.com/nebula/documentation/utilities/environment-feature-detection/'>environment detection</a> to be used without additional development time. A <a href='https://gearside.com/nebula/documentation/bundled/sass/'>SASS precompiler</a> is also bundled which allows developers to code stylesheets without needing to compile in an extra step. Those using IDEs like Coda and Sublime can continue working directly on the server if desired.
<h2>Learning Tool</h2>
Even if you aren't looking to use Nebula in its entirety as a theme, it is also a repository of useful WordPress snippets. There are examples that range from core WordPress implementations, third-party libraries and plugins examples, and a other clever ideas that will work with any WordPress theme! In each example (in addition to bite-sized code snippets) is a link to the Github source code of that actual page, so you can see exactly how that page is generated. Nebula is a great way to sharpen your skills within WordPress!",
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

	//Update Nebula options
	foreach ( $GLOBALS['nebula_options_fields'] as $nebula_option_name => $default_value ){
		update_option($nebula_option_name, $default_value);
	}

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
	//@TODO "Nebula" 0: Check the "CSS Classes" checkbox under "Screen Options" on the Appearance > Menus page so they are always visible on each menu item.

	$wp_rewrite->flush_rules();
}

//Remove unnecessary plugins bundled with core WordPress
function nebula_initialization_delete_plugins(){
	//Remove Hello Dolly plugin if it exists
	if ( file_exists(WP_PLUGIN_DIR . '/hello.php') ){
        delete_plugins(array('hello.php'));
    }
}

//Deactivate default sidebar widgets.
function nebula_initialization_deactivate_widgets(){
	update_option('sidebars_widgets', array());
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
	if ( 1==2 ){ //Set to true to force an initialization date (in case of some kind of accidental reset).
		$force_date = "May 24, 2014"; //Set the desired initialization date here. Format should be an easily convertable date like: "March 27, 2012"
		if ( strtotime($force_date) !== false ){ //Check if provided date string is valid
			update_option('nebula_initialized', strtotime($force_date));
			return false;
		}
	} else {
		if ( !nebula_is_initialized_before() ){
			update_option('nebula_initialized', date('U'));
		}
	}
}