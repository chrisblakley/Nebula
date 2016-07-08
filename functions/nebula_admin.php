<?php

//Force expire query transients when posts/pages are saved.
add_action('save_post', 'nebula_clear_transients');
function nebula_clear_transients(){
	//@TODO "Nebula" 0: Delete all transients with an expiration here.
	//if ( is_plugin_active('transients-manager/transients-manager.php') ){
		//$transient_manager = new PW_Transients_Manager();
		//$transient_manager->delete_transients_with_expirations();
	//} else {
		delete_transient('nebula_autocomplete_menus'); //Autocomplete Search
		delete_transient('nebula_autocomplete_categories'); //Autocomplete Search
		delete_transient('nebula_autocomplete_tags'); //Autocomplete Search
		delete_transient('nebula_autocomplete_authors'); //Autocomplete Search
		delete_transient('nebula_everything_query'); //Advanced Search
	//}
}

//Disable auto curly quotes (smart quotes)
remove_filter('the_content', 'wptexturize');
remove_filter('the_excerpt', 'wptexturize');
remove_filter('comment_text', 'wptexturize');
add_filter('run_wptexturize', '__return_false');


//Pull favicon from the theme folder (Front-end calls are in includes/metagraphics.php).
add_action('admin_head', 'admin_favicon');
function admin_favicon(){
	$cache_buster = ( is_debug() )? '?r' . mt_rand(1000, 99999) : '';
	echo '<link rel="shortcut icon" href="' . nebula_prefer_child_directory('/images/meta/favicon.ico') . $cache_buster . '" />';
}

//Add classes to the admin body
add_filter('admin_body_class', 'nebula_admin_body_classes');
function nebula_admin_body_classes($classes){
	global $current_user;
	$user_roles = $current_user->roles;
	$classes .= array_shift($user_roles);
	return $classes;
}

//Disable Admin Bar (and WP Update Notifications) for everyone but administrators (or specific users)
if ( nebula_option('admin_bar', 'disabled') ){
	show_admin_bar(false);

	add_action('wp_print_scripts', 'dequeue_admin_bar', 9999);
	add_action('wp_print_styles', 'dequeue_admin_bar', 9999);
	function dequeue_admin_bar(){
		wp_deregister_style('admin-bar');
		wp_dequeue_script('admin-bar');
	}

	add_action('init', 'admin_only_features');
	function admin_only_features(){
		remove_action('wp_footer', 'wp_admin_bar_render', 1000); //For the front-end

		//CSS override for the frontend
		add_filter('wp_head','remove_admin_bar_style_frontend', 99);
		function remove_admin_bar_style_frontend(){
			echo '<style type="text/css" media="screen">
			html { margin-top: 0px !important; }
			* html body { margin-top: 0px !important; }
			</style>';
		}
	}
} else {
	add_action('wp_before_admin_bar_render', 'remove_admin_bar_logo', 0);
	function remove_admin_bar_logo() {
		global $wp_admin_bar;
		$wp_admin_bar->remove_menu('wp-logo');
		$wp_admin_bar->remove_menu('wpseo-menu'); //Remove Yoast SEO from admin bar
	}

	//Create custom menus within the WordPress Admin Bar
	add_action('admin_bar_menu', 'nebula_admin_bar_menus', 800);
	function nebula_admin_bar_menus($wp_admin_bar){
		wp_reset_query(); //Make sure the query is always reset in case the current page has a custom query that isn't reset.

		$node_id = ( is_admin() )? 'view' : 'edit';
		$new_content_node = $wp_admin_bar->get_node($node_id);
		if ( $new_content_node ){
			$new_content_node->title = ucfirst($node_id) . ' Page <span class="nebula-admin-light" style="font-size: 10px; color: #a0a5aa; color: rgba(240, 245, 250, .6);">(ID: ' . get_the_id() . ')</span>';
			$wp_admin_bar->add_node($new_content_node);
		}

		//Add created date under View/Edit node
		//@TODO "Nebula" 0: get_the_author() is not working when in Admin
		$wp_admin_bar->add_node(array(
			'parent' => $node_id,
			'id' => 'nebula-created',
			'title' => '<i class="nebula-admin-fa fa fa-fw fa-calendar-o" style="font-family: \'FontAwesome\'; color: #a0a5aa; color: rgba(240, 245, 250, .6); margin-right: 5px;"></i> Created: ' . get_the_date() . ' <span class="nebula-admin-light" style="font-size: 10px; color: #a0a5aa; color: rgba(240, 245, 250, .6);">(' . get_the_author() . ')</span>',
			'href' => get_edit_post_link(),
			'meta' => array('target' => '_blank')
		));

		//Add modified date under View/Edit node
		if ( get_the_modified_date() != get_the_date() ){ //If the post has been modified
			$manage_author = ( get_the_modified_author() )? get_the_modified_author() : get_the_author();
			$wp_admin_bar->add_node(array(
				'parent' => $node_id,
				'id' => 'nebula-modified',
				'title' => '<i class="nebula-admin-fa fa fa-fw fa-clock-o" style="font-family: \'FontAwesome\'; color: #a0a5aa; color: rgba(240, 245, 250, .6); margin-right: 5px;"></i> Modified: ' . get_the_modified_date() . ' <span class="nebula-admin-light" style="font-size: 10px; color: #a0a5aa; color: rgba(240, 245, 250, .6);">(' . $manage_author . ')</span>',
				'href' => get_edit_post_link(),
				'meta' => array('target' => '_blank')
			));
		}

		/* @TODO "Nebula" 0: Other information to consider under the View/Edit node:
			- Status (Published, Draft, etc)
			- Visibility (Public)
			- Revisions (count)
		*/

		$wp_admin_bar->add_node(array(
			'id' => 'nebula',
			'title' => '<i class="nebula-admin-fa fa fa-fw fa-star" style="font-family: \'FontAwesome\'; color: #a0a5aa; color: rgba(240, 245, 250, .6); margin-right: 5px;"></i> Nebula',
			'href' => 'https://gearside.com/nebula/',
			'meta' => array('target' => '_blank')
		));

		$scss_last_processed = ( nebula_data('scss_last_processed') )? date('l, F j, Y - g:i:sa', nebula_data('scss_last_processed')) : 'Never';
		$wp_admin_bar->add_node(array(
			'parent' => 'nebula',
			'id' => 'nebula-options-scss',
			'title' => '<i class="nebula-admin-fa fa fa-fw fa-paint-brush" style="font-family: \'FontAwesome\'; color: #a0a5aa; color: rgba(240, 245, 250, .6); margin-right: 5px;"></i> Re-process All SCSS Files',
			'href' => esc_url(add_query_arg('sass', 'true')),
			'meta' => array('title' => 'Last: ' . $scss_last_processed)
		));

		$wp_admin_bar->add_node(array(
			'parent' => 'nebula',
			'id' => 'nebula-options',
			'title' => '<i class="nebula-admin-fa fa fa-fw fa-cog" style="font-family: \'FontAwesome\'; color: #a0a5aa; color: rgba(240, 245, 250, .6); margin-right: 5px;"></i> Options',
			'href' => get_admin_url() . 'themes.php?page=nebula_options'
		));

		$wp_admin_bar->add_node(array(
			'parent' => 'nebula-options',
			'id' => 'nebula-options-help',
			'title' => '<i class="nebula-admin-fa fa fa-fw fa-question" style="font-family: \'FontAwesome\'; color: #a0a5aa; color: rgba(240, 245, 250, .6); margin-right: 5px;"></i> Help & Documentation',
			'href' => 'https://gearside.com/nebula/documentation/options/',
			'meta' => array('target' => '_blank')
		));

		$wp_admin_bar->add_node(array(
			'parent' => 'nebula',
			'id' => 'nebula-github',
			'title' => '<i class="nebula-admin-fa fa fa-fw fa-github" style="font-family: \'FontAwesome\'; color: #a0a5aa; color: rgba(240, 245, 250, .6); margin-right: 5px;"></i> Nebula Github',
			'href' => 'https://github.com/chrisblakley/Nebula',
			'meta' => array('target' => '_blank')
		));

		$wp_admin_bar->add_node(array(
			'parent' => 'nebula-github',
			'id' => 'nebula-github-issues',
			'title' => 'Issues',
			'href' => 'https://github.com/chrisblakley/Nebula/issues',
			'meta' => array('target' => '_blank')
		));

		$wp_admin_bar->add_node(array(
			'parent' => 'nebula-github',
			'id' => 'nebula-github-changelog',
			'title' => 'Changelog',
			'href' => 'https://github.com/chrisblakley/Nebula/commits/master',
			'meta' => array('target' => '_blank')
		));
	}

	//Remove core WP admin bar head CSS and add our own
	add_action('get_header', 'remove_admin_bar_bump');
	function remove_admin_bar_bump(){
		remove_action('wp_head', '_admin_bar_bump_cb');
	}
	add_action('wp_head', 'mp6_override_toolbar_margin', 11);
	function mp6_override_toolbar_margin(){
		if ( is_admin_bar_showing() ){ ?>
			<style type="text/css">
				html {margin-top: 32px !important; transition: margin-top 0.5s linear;}
				* html body {margin-top: 32px !important;}

				#wpadminbar {transition: top 0.5s linear;}
					.admin-bar-inactive #wpadminbar {top: -32px; overflow: hidden;}
					#wpadminbar i {-webkit-font-smoothing: antialiased;}

				@media screen and (max-width: 782px){
					html {margin-top: 46px !important;}
					* html body {margin-top: 46px !important;}

					.admin-bar-inactive #wpadminbar {top: -46px; overflow: hidden;}
				}

				@media screen and (max-width: 600px){
					#wpadminbar {top: -46px;}
				}

				html.admin-bar-inactive {margin-top: 0 !important;}
			</style>
		<?php }
	}
}

//Disable Wordpress Core update notifications in WP Admin
if ( nebula_option('wp_core_updates_notify', 'disabled') ){
	add_filter('pre_site_transient_update_core', create_function('$a', "return null;"));
}

//Show update warning on Wordpress Core/Plugin update admin pages
if ( nebula_option('plugin_update_warning') ){
	if ( $pagenow == 'plugins.php' || $pagenow == 'update-core.php' ){
		add_action('admin_notices', 'nebula_update_warning');
		function nebula_update_warning(){
			echo "<div class='nebula_admin_notice error'><p><strong>WARNING:</strong> Updating Wordpress core or plugins may cause irreversible errors to your website!</p><p>Contact <a href='http://www.pinckneyhugo.com/'>Pinckney Hugo Group</a> if there are questions about updates: " . nebula_tel_link('13154786700') . "</p></div>";
		}
	}
}

//Nebula Theme Update Checker
add_action('admin_init', 'nebula_theme_json');
function nebula_theme_json(){
	$override = apply_filters('pre_nebula_theme_json', false);
	if ( $override !== false ){return;}

	//Make sure the version number is always up-to-date in options.
	if ( nebula_data('current_version') != nebula_version('raw') ){
		nebula_update_data('current_version', nebula_version('raw'));
		nebula_update_data('current_version_date', nebula_version('date'));
	}

	//If newer version of Nebula has a "u" at the end of the version number, disable automated updates.
	$remote_version_info = get_option('external_theme_updates-Nebula-master');

	//Check for an unsupported version
	if ( (strpos(nebula_version('raw'), 'u') || nebula_data('version_legacy') == 'true') || (!empty($remote_version_info->checkedVersion) && strpos($remote_version_info->checkedVersion, 'u') && str_replace('u', '', $remote_version_info->checkedVersion) != str_replace('u', '', nebula_version('full'))) ){
		nebula_update_data('version_legacy', 'true');
		nebula_update_data('current_version', nebula_version('raw'));
		nebula_update_data('current_version_date', nebula_version('date'));
		nebula_update_data('next_version', 'INCOMPATIBLE');
	} elseif ( current_user_can('manage_options') && is_child_theme() && nebula_option('theme_update_notification', 'enabled') ){
		require(get_template_directory() . '/includes/libs/theme-update-checker.php'); //Initialize the update checker library.
		$theme_update_checker = new ThemeUpdateChecker(
			'Nebula-master', //This should be the directory slug of the parent theme.
			'https://raw.githubusercontent.com/chrisblakley/Nebula/master/includes/data/nebula_theme.json'
		);
	}
}

//When checking for theme updates, store the next and current Nebula versions from the response. Hook is inside the theme-update-checker.php library.
add_action('nebula_theme_update_check', 'nebula_theme_update_version_store', 10, 2);
function nebula_theme_update_version_store($themeUpdate, $installedVersion){
	nebula_update_data('next_version', $themeUpdate->version);
	nebula_update_data('current_version', nebula_version('full'));
	nebula_update_data('current_version_date', nebula_version('date'));

	if ( strpos($themeUpdate->version, 'u') && str_replace('u', '', $themeUpdate->version) != str_replace('u', '', nebula_version('full')) ){ //If Github version has "u", disable automated updates.
		nebula_update_data('version_legacy', 'true');
	} elseif ( nebula_data('version_legacy') == 'true' ){ //Else, reset the option to false (this triggers when a legacy version has been manually updated to support automated updates again).
		nebula_update_data('version_legacy', 'false');
	}
}

//Send an email to the current user and site admin that Nebula has been updated.
add_action('upgrader_process_complete', 'nebula_theme_update_automation', 10, 2); //Action 'upgrader_post_install' also exists.
function nebula_theme_update_automation($upgrader_object, $options){
	$override = apply_filters('pre_nebula_theme_update_automation', false);
	if ( $override !== false ){return;}

	if ( $options['type'] == 'theme' && in_array_r('Nebula-master', $options['themes']) ){
		nebula_theme_update_email(); //Send email with update information
		nebula_update_data('version_legacy', 'false');
	}
}
function nebula_theme_update_email(){
	$prev_version = nebula_data('current_version');
	$prev_version_commit_date = nebula_data('current_version_date');
	$new_version = nebula_data('next_version');

	if ( $prev_version != $new_version ){
		global $wpdb;
		$current_user = wp_get_current_user();
		$to = $current_user->user_email;

		//Carbon copy the admin if update was done by another user.
		$admin_user_email = nebula_option('contact_email', nebula_option('admin_email'));
		if ( !empty($admin_user_email) && $admin_user_email != $current_user->user_email ){
			$headers[] = 'Cc: ' . $admin_user_email;
		}

		$subject = 'Nebula updated to ' . $new_version . ' for ' . get_bloginfo('name') . '.';
		$message = '<p>The parent Nebula theme has been updated from version <strong>' . $prev_version . '</strong> (Committed: ' . $prev_version_commit_date . ') to <strong>' . $new_version . '</strong> for ' . get_bloginfo('name') . ' (' . home_url() . ') by ' . $current_user->display_name . ' on ' . date('F j, Y') . ' at ' . date('g:ia') . '.<br/><br/>To revert, find the previous version in the <a href="https://github.com/chrisblakley/Nebula/commits/master" target="_blank">Nebula Github repository</a>, download the corresponding .zip file, and upload it replacing /themes/Nebula-master/.</p>';

		//Set the content type to text/html for the email.
		add_filter('wp_mail_content_type', function($content_type){
			return 'text/html';
		});

		wp_mail($to, $subject, $message, $headers);
	}
}

//Remove the examples directory
//Note: To re-enable the examples directory, enable the Nebula Examples Directory function, and then update the Nebula theme, or re-upload the examples directory.
add_action('upgrader_process_complete', 'nebula_remove_examples_directory');
add_action('admin_init', 'nebula_remove_examples_directory');
function nebula_remove_examples_directory(){
	$override = apply_filters('pre_nebula_remove_examples_directory', false);
	if ( $override !== false ){return;}

	if ( nebula_option('examples_directory', 'disabled') && current_user_can('manage_options') ){
		if ( file_exists(get_stylesheet_directory() . '/examples') || file_exists(get_template_directory() . '/Nebula-Child/examples') ){
			WP_Filesystem();
			global $wp_filesystem;

			if ( file_exists(get_stylesheet_directory() . '/examples') ){
				$wp_filesystem->rmdir(get_stylesheet_directory() . '/examples', true);
			}

			if ( file_exists(get_template_directory() . '/Nebula-Child/examples') ){
				$wp_filesystem->rmdir(get_template_directory() . '/Nebula-Child/examples', true);
			}
		}
	}
}

//Control session time (for the "Remember Me" checkbox)
add_filter('auth_cookie_expiration', 'nebula_session_expire');
function nebula_session_expire($expirein){
    return 2592000; //30 days (Default is 1209600 (14 days)
}

//Disable the logged-in monitoring modal
remove_action('admin_enqueue_scripts', 'wp_auth_check_load');

//Custom login screen
add_action('login_head', 'nebula_login_ga');
function nebula_login_ga(){
	if ( empty($_POST['signed_request']) ){
	    echo "<script>(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');ga('create', '" . $GLOBALS['ga'] . "', 'auto');</script>";
	}
}

//Change link of login logo to live site
add_filter('login_headerurl', 'custom_login_header_url');
function custom_login_header_url(){
    return home_url('/');
}

//Change alt of login image
add_filter('login_headertitle', 'new_wp_login_title');
function new_wp_login_title(){
    return get_option('blogname');
}

//Update user online status
add_action('init', 'nebula_users_status_init');
add_action('admin_init', 'nebula_users_status_init');
function nebula_users_status_init(){
	if ( is_user_logged_in() ){
		$logged_in_users = nebula_data('users_status');

		$unique_id = $_SERVER['REMOTE_ADDR'] . '.' . preg_replace("/[^a-zA-Z0-9\.]+/", "", $_SERVER['HTTP_USER_AGENT']);
		$current_user = wp_get_current_user();

		//@TODO "Nebula" 0: Technically, this should be sorted by user ID -then- unique id -then- the rest of the info. Currently, concurrent logins won't reset until they have ALL expired. This could be good enough, though.

		if ( !isset($logged_in_users[$current_user->ID]['last']) || $logged_in_users[$current_user->ID]['last'] < time()-600 ){ //If a last login time does not exist for this user -or- if the time exists but is greater than 10 minutes, update.
			$logged_in_users[$current_user->ID] = array(
				'id' => $current_user->ID,
				'username' => $current_user->user_login,
				'last' => time(),
				'unique' => array($unique_id),
			);
			nebula_update_data('users_status', $logged_in_users);
		} else {
			if ( !in_array($unique_id, $logged_in_users[$current_user->ID]['unique']) ){
				array_push($logged_in_users[$current_user->ID]['unique'], $unique_id);
				nebula_update_data('users_status', $logged_in_users);
			}
		}
	}
}

//Nebula Admin Notices
if ( nebula_option('admin_notices') ){
	add_action('admin_notices', 'nebula_admin_notices');
	function nebula_admin_notices(){
		if ( current_user_can('manage_options') || is_dev() ){
			//Check PHP version
			$php_version_lifecycle = nebula_php_version_support();
			if ( $php_version_lifecycle['lifecycle'] == 'security' ){
				if ( $php_version_lifecycle['end']-time() < 2592000 ){ //1 month
					echo '<div class="nebula-admin-notice notice notice-info"><p>PHP <strong>' . PHP_VERSION . '</strong> is nearing end of life. Security updates end on <strong title="In ' . human_time_diff($php_version_lifecycle['end']) . '">' . date('F j, Y', $php_version_lifecycle['end']) . '</strong>. <a href="http://php.net/supported-versions.php" target="_blank">PHP Version Support &raquo;</a></p></div>';
				}
			} elseif ( $php_version_lifecycle['lifecycle'] == 'end' ){
				echo '<div class="nebula-admin-notice error"><p>PHP <strong>' . PHP_VERSION . '</strong> no longer receives security updates! End of life occurred on <strong title="' . human_time_diff($php_version_lifecycle['end']) . ' ago">' . date('F j, Y', $php_version_lifecycle['end']) . '</strong>. <a href="http://php.net/supported-versions.php" target="_blank">PHP Version Support &raquo;</a></p></div>';
			}

			//Check for hard Debug Mode
			if ( WP_DEBUG ){
				$debug_messages = '';
				$notice_level = 'notice notice-info';
				if ( WP_DEBUG ){
					$debug_messages .= '<strong>WP_DEBUG</strong> is enabled. ';
				}
				if ( WP_DEBUG_LOG ){
					$debug_messages .= '<strong>Debug logging</strong> (WP_DEBUG_LOG) to /wp-content/debug.log is enabled. ';
				}
				if ( WP_DEBUG_DISPLAY ){
					$notice_level = 'error';
					$debug_messages .= 'Debug errors and warnings <strong>are</strong> being displayed on the front-end (WP_DEBUG_DISPLAY)! ';
				}
				echo '<div class="nebula-admin-notice ' . $notice_level . '"><p>' . $debug_messages . ' <small>(Generally defined in wp-config.php)</small></p></div>';
			}

			//Check for Google Analytics Tracking ID
			if ( nebula_option('ga_tracking_id') == '' && $GLOBALS['ga'] == '' ){
				echo '<div class="nebula-admin-notice error"><p><a href="themes.php?page=nebula_options">Google Analytics tracking ID</a> is currently not set!</p></div>';
			}

			//Check for "Discourage searching engines..." setting
			if ( get_option('blog_public') == 0 ){
				echo '<div class="nebula-admin-notice error"><p><a href="options-reading.php">Search Engine Visibility</a> is currently disabled!</p></div>';
			}

			//Check for "Just Another WordPress Blog" tagline
			if ( strtolower(get_bloginfo('description')) == 'just another wordpress site' ){
				echo '<div class="nebula-admin-notice error"><p><a href="options-general.php">Site Tagline</a> is still "Just Another WordPress Site"!</p></div>';
			}

			//Check if all SCSS files were processed manually.
			if ( nebula_option('scss', 'enabled') && (isset($_GET['sass']) || isset($_GET['scss'])) ){ //SCSS notice when Nebula Options is updated is in nebula_options.php
				if ( is_dev() || is_client() ){
					echo '<div class="nebula-admin-notice notice notice-success"><p>All SCSS files have been manually processed.</p></div>';
				} else {
					echo '<div class="nebula-admin-notice error"><p>You do not have permissions to manually process all SCSS files.</p></div>';
				}
			}

			if ( nebula_option('prototype_mode', 'disabled') && is_plugin_active('jonradio-multiple-themes/jonradio-multiple-themes.php') ){
				echo '<div class="nebula-admin-notice error"><p><a href="options-general.php">Prototype Mode</a> is disabled, but <a href="plugins.php">Multiple Theme plugin</a> is still active.</p></div>';
			}

			//Check if the parent theme template is correctly referenced
			if ( is_child_theme() ){
				$active_theme = wp_get_theme();
				if ( !file_exists(dirname(get_stylesheet_directory()) . '/' . $active_theme->get('Template')) ){
					echo '<div class="nebula-admin-notice error"><p>A child theme is active, but its parent theme directory <strong>' . $active_theme->get('Template') . '</strong> does not exist!<br/><em>The "Template:" setting in the <a href="' . get_stylesheet_uri() . '" target="_blank">style.css</a> file of the child theme must match the directory name (above) of the parent theme.</em></p></div>';
				}
			}

			//Check if Relevanssi has built an index for search
			if ( is_plugin_active('relevanssi/relevanssi.php') && !get_option('relevanssi_indexed') ){
				echo '<div class="nebula-admin-notice error"><p><a href="options-general.php?page=relevanssi%2Frelevanssi.php">Relevanssi</a> must build an index to search the site. This must be triggered manually.</p></div>';
			}
		}

		//Check page slug against categories and tags. //@TODO "Nebula" 0: Consider adding other taxonomies here too
		global $pagenow;
		if ( $pagenow == 'post.php' || $pagenow == 'edit.php' ){
			global $post;

			if ( !empty($post) ){ //If the listing has results
				foreach ( get_categories() as $category ){
				    if ( $category->slug == $post->post_name ){
				        echo '<div class="nebula-admin-notice error"><p>Page and category slug conflict: <strong>' . $category->slug . '</strong> - Consider changing this page slug.</p></div>';
				        return false;
				    }
				}

				foreach ( get_tags() as $tag ){
				    if ( $tag->slug == $post->post_name ){
				        echo '<div class="nebula-admin-notice error"><p>Page and tag slug conflict: <strong>' . $tag->slug . '</strong> - Consider changing this page slug.</p></div>';
				        return false;
				    }
				}
			}
		}
	}
}

//Check if a post slug has a number appended to it (indicating a duplicate post).
//add_filter('wp_unique_post_slug', 'nebula_unique_slug_warning_ajax', 10, 4); //@TODO "Nebula" 0: This echos when submitting posts from the front end! is_admin() does not prevent that...
function nebula_unique_slug_warning_ajax($slug, $post_ID, $post_status, $post_type){
	if ( current_user_can('publish_posts') && is_admin() && (headers_sent() || !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ){ //Should work with AJAX and without (as long as headers have been sent)
		echo '<script>
			if ( typeof nebulaUniqueSlugChecker == "function" ){
				nebulaUniqueSlugChecker("' . $post_type . '");
			}
		</script>';
	}
	return $slug;
}


/*==========================
	Dashboard
 ===========================*/

//Remove unnecessary Dashboard metaboxes
if ( nebula_option('unnecessary_metaboxes') ){
	add_action('wp_dashboard_setup', 'remove_dashboard_metaboxes');
	function remove_dashboard_metaboxes(){
		//If necessary, dashboard metaboxes can be unset. To best future-proof, use remove_meta_box().
	    remove_meta_box('dashboard_primary', 'dashboard', 'side'); //Wordpress News
	    remove_meta_box('dashboard_secondary', 'dashboard', 'side');
	    remove_meta_box('dashboard_plugins', 'dashboard', 'normal');
	    remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');
	    remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
	    remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
	}
}


//WordPress Information metabox ("At a Glance" replacement)
add_action('wp_dashboard_setup', 'nebula_ataglance_metabox');
function nebula_ataglance_metabox(){
	global $wp_meta_boxes;
	wp_add_dashboard_widget('nebula_ataglance', '<img src="' . nebula_prefer_child_directory('/images/meta') . '/favicon-32x32.png" style="float: left; width: 20px;" />&nbsp;' . get_bloginfo('name'), 'dashboard_nebula_ataglance');
}
function dashboard_nebula_ataglance(){
	global $wp_version;
	global $wp_post_types;

	echo '<ul>';
		echo '<li><i class="fa fa-globe fa-fw"></i> <a href="' . home_url('/') . '" target="_blank">' . home_url() . '</a></li>';

		//Address
		if ( nebula_option('street_address') ){
			echo '<li><i class="fa fa-map-marker fa-fw"></i> <a href="https://www.google.com/maps/place/' . nebula_full_address(1) . '" target="_blank">' . nebula_full_address() . '</a></li>';
		}

		//Open/Closed
		if ( has_business_hours() ){
			$open_closed = ( business_open() )? '<strong style="color: green;">Open</strong>' : '<strong>Closed</strong>';
			echo '<li><i class="fa fa-clock-o fa-fw"></i> Currently ' . $open_closed . '</li>';
		}

		//WordPress Version
		echo '<li><i class="fa fa-wordpress fa-fw"></i> <a href="https://codex.wordpress.org/WordPress_Versions" target="_blank">WordPress</a> <strong>' . $wp_version . '</strong></li>';

		//Nebula Version
		echo '<li><i class="fa fa-star fa-fw"></i> <a href="https://gearside.com/nebula" target="_blank">Nebula</a> <strong>' . nebula_version('version') . '</strong> <small title="' . human_time_diff(nebula_version('utc')) . ' ago">(Committed: ' . nebula_version('date') . ')</small></li>';

		//Child Theme
		if ( is_child_theme() ){
			echo '<li><i class="fa fa-child fa-fw"></i><a href="themes.php">Child theme</a> active.</li>';
		}

		if ( is_multisite() ){
			echo '<li><i class="fa fa-cubes fa-fw"></i>Multisite <a href="' . network_admin_url() . '">(Network Admin)</a></li>';
		}

		//Post Types
		foreach ( get_post_types() as $post_type ){
		    if ( in_array($post_type, array('attachment', 'revision', 'nav_menu_item', 'acf')) ){
			    continue;
		    }
			$count_pages = wp_count_posts($post_type);
			$labels_plural = ( $count_pages->publish == 1 )? $wp_post_types[$post_type]->labels->singular_name : $wp_post_types[$post_type]->labels->name;
			switch ( $post_type ){
				case ('post'):
					$post_icon_img = '<i class="fa fa-thumb-tack fa-fw"></i>';
					break;
				case ('page'):
					$post_icon_img = '<i class="fa fa-file-text fa-fw"></i>';
					break;
				case ('wpcf7_contact_form'):
					$post_icon_img = '<i class="fa fa-envelope fa-fw"></i>';
					break;
				default:
					$post_icon = $wp_post_types[$post_type]->menu_icon;
					if ( !empty($post_icon) ){
						if ( strpos('dashicons-', $post_icon) >= 0 ){
							$post_icon_img = '<i class="dashicons-before ' . $post_icon . '"></i>';
						} else {
							$post_icon_img = '<img src="' . $post_icon . '" style="width: 16px; height: 16px;" />';
						}
					} else {
						$post_icon_img = '<i class="fa fa-thumb-tack fa-fw"></i>';
					}
					break;
			}
			echo '<li>' . $post_icon_img . ' <a href="edit.php?post_type=' . $post_type . '"><strong>' . $count_pages->publish . '</strong> ' . $labels_plural . '</a></li>';
		}

		//Revisions
		$revision_count = ( WP_POST_REVISIONS == -1 )? 'all' : WP_POST_REVISIONS;
		$revision_style = ( $revision_count === 0 )? 'style="color: red;"' : '';
		$revisions_plural = ( $revision_count == 1 )? 'revision' : 'revisions';
		echo '<li><i class="fa fa-history fa-fw"></i> Storing <strong ' . $revision_style . '>' . $revision_count . '</strong> ' . $revisions_plural . '.</li>';

		//Plugins
		$all_plugins = get_plugins();
		$active_plugins = get_option('active_plugins', array());
		echo '<li><i class="fa fa-plug fa-fw"></i> <a href="plugins.php"><strong>' . count($all_plugins) . '</strong> Plugins</a> installed <small>(' . count($active_plugins) . ' active)</small></li>';

		//Users
		$user_count = count_users();
		$users_icon = 'users';
		$users_plural = 'Users';
		if ( $user_count['total_users'] == 1 ){
			$users_plural = 'User';
			$users_icon = 'user';
		}
		echo '<li><i class="fa fa-' . $users_icon . ' fa-fw"></i> <a href="users.php">' . $user_count['total_users'] . ' ' . $users_plural . '</a> <small>(' . nebula_online_users('count') . ' currently active)</small></li>';

		//Comments
		if ( nebula_option('comments', 'enabled') && nebula_option('disqus_shortname') == '' ){
			$comments_count = wp_count_comments();
			$comments_plural = ( $comments_count->approved == 1 )? 'Comment' : 'Comments';
			echo '<li><i class="fa fa-comments-o fa-fw"></i> <strong>' . $comments_count->approved . '</strong> ' . $comments_plural . '</li>';
		} else {
			if ( nebula_option('comments', 'disabled') ){
				echo '<li><i class="fa fa-comments-o fa-fw"></i> Comments disabled <small>(via <a href="themes.php?page=nebula_options">Nebula Options</a>)</small></li>';
			} else {
				echo '<li><i class="fa fa-comments-o fa-fw"></i> Using <a href="https://' . nebula_option('disqus_shortname') . '.disqus.com/admin/moderate/" target="_blank">Disqus comment system</a>.</li>';
			}
		}
	echo '</ul>';

	do_action('nebula_ataglance');

	echo '<p><em>Designed and Developed by ' . pinckneyhugogroup(1) . '</em></p>';
}


//Current User metabox
add_action('wp_dashboard_setup', 'nebula_current_user_metabox');
function nebula_current_user_metabox(){
	$user_info = get_userdata(get_current_user_id());
	$headshotURL = esc_attr(get_the_author_meta('headshot_url', get_current_user_id()));
	$headshot_thumbnail = str_replace('.jpg', '-150x150.jpg' , $headshotURL);

	if ( $headshot_thumbnail ){
		$headshot_html = '<img src="' . esc_attr($headshot_thumbnail) . '" style="float: left; max-width: 20px; border-radius: 100px;" />&nbsp;';
	} else {
		$headshot_html = '<i class="fa fa-user fa-fw"></i>&nbsp;';
	}

	global $wp_meta_boxes;
	wp_add_dashboard_widget('nebula_current_user', $headshot_html . $user_info->display_name, 'dashboard_current_user');
}
function dashboard_current_user(){
	$user_info = get_userdata(get_current_user_id());

	echo '<ul>';
		//Company
		if ( get_the_author_meta('jobcompany', $user_info->ID) ){
			$company = get_the_author_meta('jobcompany', $user_info->ID);
			if ( get_the_author_meta('jobcompanywebsite', $user_info->ID) ){
				$company = '<a href="' . get_the_author_meta('jobcompanywebsite', $user_info->ID) . '" target="_blank">' . $company . '</a>';
			}
		}

		//Job Title
		if ( get_the_author_meta('jobtitle', $user_info->ID) ){
			$job_title = get_the_author_meta('jobtitle', $user_info->ID);
			if ( !empty($company) ){
				$job_title = $job_title . ' at ';
			}
		}

		if ( !empty($job_title) || !empty($company) ){
			echo '<li><i class="fa fa-building fa-fw"></i> ' . $job_title . $company . '</li>';
		}

		//Location
		if ( get_the_author_meta('userlocation', $user_info->ID) ){
			echo '<li><i class="fa fa-map-marker fa-fw"></i> <strong>' . get_the_author_meta('userlocation', $user_info->ID) . '</strong></li>';
		}

		//Email
		echo '<li><i class="fa fa-envelope-o fa-fw"></i> Email: <strong>' . $user_info->user_email . '</strong></li>';

		if ( get_the_author_meta('phonenumber', $user_info->ID) ){
			echo '<li><i class="fa fa-phone fa-fw"></i> Phone: <strong>' . get_the_author_meta('phonenumber', $user_info->ID) . '</strong></li>';
		}

		echo '<li><i class="fa fa-user fa-fw"></i> Username: <strong>' . $user_info->user_login . '</strong></li>';
		echo '<li><i class="fa fa-info-circle fa-fw"></i> ID: <strong>' . $user_info->ID . '</strong></li>';

		//Role
		switch ($user_info->roles[0]){
		    case 'administrator': $fa_role = 'fa-key'; break;
		    case 'editor': $fa_role = 'fa-scissors'; break;
		    case 'author': $fa_role = 'fa-pencil-square'; break;
		    case 'contributor': $fa_role = 'fa-send'; break;
		    case 'subscriber': $fa_role = 'fa-ticket'; break;
		    default: $fa_role = 'fa-user'; break;
		}
		$super_role = ( is_multisite() && is_super_admin() )? 'Super Admin' : $user_info->roles[0];
		echo '<li><i class="fa ' . $fa_role . ' fa-fw"></i> Role: <strong class="admin-user-info admin-user-role">' . $super_role . '</strong></li>';

		//Developer
		if ( is_dev() ){
			echo '<li><i class="fa fa-gears fa-fw"></i> <strong>Developer</strong></li>';
		}

		//IP Address
		echo '<li>';
			if ( $_SERVER['REMOTE_ADDR'] == '72.43.235.106' ){
				echo '<img src="' . get_template_directory_uri() . '/images/phg/phg-symbol.png" style="max-width: 14px;" />';
			} else {
				echo '<i class="fa fa-laptop fa-fw"></i>';
			}
			echo 'IP Address: <a href="http://whatismyipaddress.com/ip/' . $_SERVER["REMOTE_ADDR"] . '" target="_blank"><strong class="admin-user-info admin-user-ip">' . $_SERVER["REMOTE_ADDR"] . '</strong></a>';
		echo '</li>';

		//Multiple locations
		if ( nebula_user_single_concurrent($user_info->ID) > 1 ){
			echo '<li><i class="fa fa-users fa-fw"></i> Active in <strong>' . nebula_user_single_concurrent($user_info->ID) . ' locations</strong>.</li>';
		}

		//User's posts
		echo '<li><i class="fa fa-thumb-tack fa-fw"></i> Your posts: <strong>' . count_user_posts($user_info->ID) . '</strong></li>';
	echo '</ul>';

	echo '<p><small><em><a href="profile.php">Manage your user information</a></em></small></p>';
}


//Administrative metabox
if ( current_user_can('manage_options') ){
	add_action('wp_dashboard_setup', 'nebula_administrative_metabox');
}
function nebula_administrative_metabox(){
	global $wp_meta_boxes;
	wp_add_dashboard_widget('nebula_administrative', 'Administrative', 'dashboard_administrative');
}
function dashboard_administrative(){
	echo '<ul>';
		if ( nebula_option('cpanel_url') ){
			echo '<li><i class="fa fa-gears fa-fw"></i> <a href="' . nebula_option('cpanel_url') . '" target="_blank">Server Control Panel</a></li>';
		}

		if ( nebula_option('hosting_url') ){
			echo '<li><i class="fa fa-hdd-o fa-fw"></i> <a href="' . nebula_option('hosting_url') . '" target="_blank">Hosting</a></li>';
		}

		if ( nebula_option('registrar_url') ){
			echo '<li><i class="fa fa-globe fa-fw"></i> <a href="' . nebula_option('registrar_url') . '" target="_blank">Domain Registrar</a></li>';
		}

		if ( nebula_option('ga_url') ){
			echo '<li><i class="fa fa-bar-chart-o fa-fw"></i> <a href="' . nebula_option('ga_url') . '" target="_blank">Google Analytics</a></li>';
		}

		if ( nebula_option('google_search_console_url') ){
			echo '<li><i class="fa fa-google fa-fw"></i> <a href="' . nebula_option('google_search_console_url') . '" target="_blank">Google Search Console</a></li>';
		}

		if ( nebula_option('google_adsense_url') ){
			echo '<li><i class="fa fa-bar-chart-o fa-fw"></i> <a href="' . nebula_option('google_adsense_url') . '" target="_blank">Google AdSense</a></li>';
		}

		if ( nebula_option('google_adwords_url') ){
			echo '<li><i class="fa fa-bar-chart-o fa-fw"></i> <a href="' . nebula_option('google_adwords_url') . '" target="_blank">Google AdWords</a></li>';
		}

		if ( nebula_option('mention_url') ){
			echo '<li><i class="fa fa-star fa-fw"></i> <a href="' . nebula_option('mention_url') . '" target="_blank">Mention</a></li>';
		}
	echo '</ul>';

	echo '<p><small><em>Manage administrative links in <strong><a href="themes.php?page=nebula_options">Nebula Options</a></strong>.</em></small></p>';
}


//Social metabox
add_action('wp_dashboard_setup', 'nebula_social_metabox');
function nebula_social_metabox(){
	global $wp_meta_boxes;
	wp_add_dashboard_widget('nebula_social', 'Social', 'dashboard_social');
}
function dashboard_social(){
	echo '<ul>';
		if ( nebula_option('facebook_url') ){
			echo '<li><i class="fa fa-facebook-square fa-fw"></i> <a href="' . nebula_option('facebook_url') . '" target="_blank">Facebook</a></li>';
		}

		if ( nebula_option('twitter_url') ){
			echo '<li><i class="fa fa-twitter-square fa-fw"></i> <a href="' . nebula_option('twitter_url') . '" target="_blank">Twitter</a></li>';
		}

		if ( nebula_option('google_plus_url') ){
			echo '<li><i class="fa fa-google-plus-square fa-fw"></i> <a href="' . nebula_option('google_plus_url') . '" target="_blank">Google+</a></li>';
		}

		if ( nebula_option('linkedin_url') ){
			echo '<li><i class="fa fa-linkedin-square fa-fw"></i> <a href="' . nebula_option('linkedin_url') . '" target="_blank">LinkedIn</a></li>';
		}

		if ( nebula_option('youtube_url') ){
			echo '<li><i class="fa fa-youtube-square fa-fw"></i> <a href="' . nebula_option('youtube_url') . '" target="_blank">Youtube</a></li>';
		}

		if ( nebula_option('instagram_url') ){
			echo '<li><i class="fa fa-instagram fa-fw"></i> <a href="' . nebula_option('instagram_url') . '" target="_blank">Instagram</a></li>';
		}

		if ( nebula_option('disqus_shortname') ){
			echo '<li><i class="fa fa-comments-o fa-fw"></i> <a href="https://' . nebula_option('disqus_shortname') . '.disqus.com/admin/moderate/" target="_blank">Disqus</a></li>';
		}
	echo '</ul>';

	if ( current_user_can('manage_options') ){
		echo '<p><small><em>Manage social links in <strong><a href="themes.php?page=nebula_options">Nebula Options</a></strong>.</em></small></p>';
	}
}


//Pinckney Hugo Group metabox
add_action('wp_dashboard_setup', 'nebula_phg_metabox');
function nebula_phg_metabox(){
	global $wp_meta_boxes;
	wp_add_dashboard_widget('nebula_phg', 'Pinckney Hugo Group', 'dashboard_phg');
}
function dashboard_phg(){
	echo '<a href="http://pinckneyhugo.com" target="_blank"><img src="' . get_template_directory_uri() . '/images/phg/phg-building.jpg" style="width: 100%;" /></a>';
	echo '<ul>';
		echo '<li>' . pinckneyhugogroup() . '</li>';
		echo '<li><i class="fa fa-map-marker fa-fw"></i> <a href="https://www.google.com/maps/place/760+West+Genesee+Street+Syracuse+NY+13204" target="_blank">760 West Genesee Street, Syracuse, NY 13204</a></li>';
		echo '<li><i class="fa fa-phone fa-fw"></i> (315) 478-6700</li>';
	echo '</ul>';
}



//Extension skip list for both TODO Manager and Developer Metabox
function skip_extensions(){
	return array('.jpg', '.jpeg', '.png', '.gif', '.ico', '.tiff', '.psd', '.ai',  '.apng', '.bmp', '.otf', '.ttf', '.ogv', '.flv', '.fla', '.mpg', '.mpeg', '.avi', '.mov', '.woff', '.eot', '.mp3', '.mp4', '.wmv', '.wma', '.aiff', '.zip', '.zipx', '.rar', '.exe', '.dmg', '.csv', '.swf', '.pdf', '.pdfx', '.pem', '.ppt', '.pptx', '.pps', '.ppsx', '.bak');
}

//TODO metabox
if ( is_dev() ){
	add_action('wp_dashboard_setup', 'todo_metabox');
}

function todo_metabox(){
	global $wp_meta_boxes;
	wp_add_dashboard_widget('todo_manager', 'To-Do Manager', 'dashboard_todo_manager');
}

function dashboard_todo_manager(){
	do_action('nebula_todo_manager');
	echo '<p class="todoresults_title"><strong>Active @todo Comments</strong> <a class="todo_help_icon" href="http://gearside.com/wordpress-dashboard-todo-manager/" target="_blank"><i class="fa fw fa-question-circle"></i> Documentation &raquo;</a></p><div class="todo_results">';

	global $todo_file_counter, $todo_instance_counter;
	$todo_file_counter = 0;
	$todo_instance_counter = 0;

	nebula_todo_files();

	echo '</div><!--/todo_results-->';
	echo '<p>Found <strong>' . $todo_file_counter . ' files</strong> with <strong>' . $todo_instance_counter . ' @todo comments</strong>.</p>';
}
function nebula_todo_files($todo_dirpath=null, $child=false){
	global $todo_file_counter, $todo_instance_counter;
	$todo_last_filename = false;

	if ( is_child_theme() && !$child ){
		nebula_todo_files(get_stylesheet_directory(), true);
	}

	if ( empty($todo_dirpath) ){
		$todo_dirpath = get_template_directory();
	}

	foreach ( glob_r($todo_dirpath . '/*') as $todo_file ){
		$todo_counted = false;
		if ( is_file($todo_file) ){
		    if ( strpos(basename($todo_file), '@todo') !== false ){
			    echo '<p class="resulttext">' . str_replace($todo_dirpath, '', dirname($todo_file)) . '/<strong>' . basename($todo_file) . '</strong></p>';
			    $todo_file_counter++;
			    $todo_counted = true;
		    }

		    $todo_skipFilenames = array('README.md', 'nebula_admin.php', 'error_log', 'includes/libs', 'examples/');
		    if ( !contains(basename($todo_file), skip_extensions()) && !contains($todo_file, $todo_skipFilenames) ){
			    foreach ( file($todo_file) as $todo_lineNumber => $todo_line ){
			        if ( stripos($todo_line, '@TODO') !== false ){
						$theme = '';
						if ( is_child_theme() ){
							if ( $child ){
								$theme = 'child';
								$theme_note = ' <small>(Child)</small>';
							} else {
								$theme = 'parent';
								$theme_note = ' <small>(Parent)</small>';
							}
						}

						$the_full_todo = substr($todo_line, strpos($todo_line, '@TODO'));
						$the_todo_meta = current(explode(':', $the_full_todo));

						//Get the priority
						preg_match_all('!\d+!', $the_todo_meta, $the_todo_ints);
						$todo_priority = 'empty';
						if ( !empty($the_todo_ints[0]) ){
							$todo_priority = $the_todo_ints[0][0];
						}

						//Get the category
						$the_todo_quote_check = '';
						$the_todo_cat = '';
						$the_todo_cat_html = '';
						preg_match_all('/".*?"|\'.*?\'/', $the_todo_meta, $the_todo_quote_check);
						if ( !empty($the_todo_quote_check[0][0]) ){
							$the_todo_cat = substr($the_todo_quote_check[0][0], 1, -1);
							$the_todo_cat_html = '<span class="todocategory">' . $the_todo_cat . '</span>';
						}

						//Get the message
						$the_todo_text_full = substr($the_full_todo, strpos($the_full_todo, ':')+1);
						$end_todo_text_strings = array('-->', '?>', '*/');
						$the_todo_text = explode($end_todo_text_strings[0], str_replace($end_todo_text_strings, $end_todo_text_strings[0], $the_todo_text_full));

						$todo_this_filename = str_replace($todo_dirpath, '', dirname($todo_file)) . '/' . basename($todo_file);
						if ( $todo_last_filename != $todo_this_filename ){
							if ( !empty($todo_last_filename) ){
								echo '</div><!--/todofilewrap-->';
							}
							echo '<div class="todofilewrap todo-theme-' . $theme . '"><p class="todofilename">' . str_replace($todo_dirpath, '', dirname($todo_file)) . '/<strong>' . basename($todo_file) . '</strong><span class="themenote">' . $theme_note . '</span></p>';
						}

						echo '<div class="linewrap todo-category-' . strtolower(str_replace(' ', '_', $the_todo_cat)) . ' todo-priority-' . strtolower(str_replace(' ', '_', $todo_priority)) . '"><p class="todoresult"> ' . $the_todo_cat_html . ' <a class="linenumber" href="#">Line ' . ($todo_lineNumber+1) . '</a> <span class="todomessage">' . strip_tags($the_todo_text[0]) . '</span></p><div class="precon"><pre class="actualline">' . trim(htmlentities($todo_line)) . '</pre></div></div>';

						$todo_last_filename = $todo_this_filename;

						if ( $child && ($todo_priority == 'empty' || $todo_priority > 0) ){ //Only count @todo files/comments on the child theme.
							$todo_instance_counter++;
							if ( !$todo_counted ){
								$todo_file_counter++;
								$todo_counted = true;
							}
						}
			        }
			    }
		    }
		}
	}
	echo '</div><!--/todofilewrap-->';
}


//Developer Info Metabox
//If user's email address ends in @pinckneyhugo.com or if IP address matches the dev IP (set in Nebula Options).
if ( is_dev() ){
	add_action('wp_dashboard_setup', 'dev_info_metabox');
}

function dev_info_metabox(){
	global $wp_meta_boxes;
	wp_add_dashboard_widget('phg_developer_info', 'Developer Information', 'dashboard_developer_info');
}
function dashboard_developer_info(){
	do_action('nebula_developer_info');
	echo '<ul class="serverdetections">';

		//Domain
		echo '<li><i class="fa fa-info-circle fa-fw"></i> <a href="http://whois.domaintools.com/' . $_SERVER['SERVER_NAME'] . '" target="_blank" title="WHOIS Lookup">Domain</a>: <strong>' . nebula_url_components('domain') . '</strong></li>';

		//Host
		function top_domain_name($url){
			$alldomains = explode(".", $url);
			return $alldomains[count($alldomains)-2] . "." . $alldomains[count($alldomains)-1];
		}
		if ( function_exists('gethostname') ){
			set_error_handler(function(){ /* ignore errors */ });
			$dnsrecord = ( dns_get_record(top_domain_name(gethostname()), DNS_NS) )? dns_get_record(top_domain_name(gethostname()), DNS_NS) : '';
			restore_error_handler();

			echo '<li><i class="fa fa-hdd-o fa-fw"></i> Host: <strong>' . top_domain_name(gethostname()) . '</strong>';
			if ( !empty($dnsrecord[0]['target']) ){
				echo ' <small>(' . top_domain_name($dnsrecord[0]['target']) . ')</small>';
			}
			echo '</li>';
		}

		//Server IP address (and connection security)
		$secureServer = '';
		if ( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ){
			$secureServer = '<small class="secured-connection"><i class="fa fa-lock fa-fw"></i>Secured Connection</small>';
		}
		echo '<li><i class="fa fa-upload fa-fw"></i> Server IP: <strong><a href="http://whatismyipaddress.com/ip/' . $_SERVER['SERVER_ADDR'] . '" target="_blank">' . $_SERVER['SERVER_ADDR'] . '</a></strong> ' . $secureServer . '</li>';

		//Server operating system
		if ( strpos(strtolower(PHP_OS), 'linux') !== false ){
			$php_os_icon = 'fa-linux';
		} else if ( strpos(strtolower(PHP_OS), 'windows') !== false ){
			$php_os_icon = 'fa-windows';
		} else {
			$php_os_icon = 'fa-upload';
		}
		echo '<li><i class="fa ' . $php_os_icon . ' fa-fw"></i> Server OS: <strong>' . PHP_OS . '</strong> <small>(' . $_SERVER['SERVER_SOFTWARE'] . ')</small></li>';

		//PHP version
		$php_version_color = 'inherit';
		$php_version_info = '';
		$php_version_cursor = 'normal';
		$php_version_lifecycle = nebula_php_version_support();
		if ( $php_version_lifecycle['lifecycle'] == 'security' ){
			$php_version_color = '#ca8038';
			$php_version_info = 'This version is nearing end of life. Security updates end on ' . date('F j, Y', $php_version_lifecycle['security']) . '.';
			$php_version_cursor = 'help';
		} elseif ( $php_version_lifecycle['lifecycle'] == 'end' ){
			$php_version_color = '#ca3838';
			$php_version_info = 'This version no longer receives security updates! End of life occurred on ' . date('F j, Y', $php_version_lifecycle['end']) . '.';
			$php_version_cursor = 'help';
		}
		$safe_mode = ( ini_get('safe_mode') )? '<small><strong><em>Safe Mode</em></strong></small>' : '';
		echo '<li><i class="fa fa-wrench fa-fw"></i> PHP Version: <strong style="color: ' . $php_version_color . '; cursor: ' . $php_version_cursor . ';" title="' . $php_version_info . '">' . PHP_VERSION . '</strong> ' . $safe_mode . '</li>';

		//PHP memory limit
		echo '<li><i class="fa fa-cogs fa-fw"></i> PHP Memory Limit: <strong>' . WP_MEMORY_LIMIT . '</strong> ' . $safe_mode . '</li>';

		//MySQL version
		if ( function_exists('mysqli_get_client_version') ){
			$mysql_version = mysqli_get_client_version();
			echo '<li><i class="fa fa-database fa-fw"></i> MySQL Version: <strong title="Raw: ' . $mysql_version . '">' . floor($mysql_version/10000) . '.' . floor(($mysql_version%10000)/100) . '.' . ($mysql_version%10000)%100 . '</strong></li>';
		}

		//Theme directory size(s)
		if ( is_child_theme() ){
			$nebula_parent_size = foldersize(get_template_directory());
			$nebula_child_size = foldersize(get_stylesheet_directory());
			echo '<li><i class="fa fa-code"></i> Parent theme directory size: <strong>' . round($nebula_parent_size/1048576, 2) . 'mb</strong> </li>';

			if ( nebula_option('prototype_mode', 'enabled') ){
				echo '<li><i class="fa fa-flag-checkered"></i> Production directory size: <strong>' . round($nebula_child_size/1048576, 2) . 'mb</strong> </li>';
			} else {
				echo '<li><i class="fa fa-code"></i> Child theme directory size: <strong>' . round($nebula_child_size/1048576, 2) . 'mb</strong> </li>';
			}
		} else {
			$nebula_size = foldersize(get_stylesheet_directory());
			echo '<li><i class="fa fa-code"></i> Theme directory size: <strong>' . round($nebula_size/1048576, 2) . 'mb</strong> </li>';
		}

		if ( nebula_option('prototype_mode', 'enabled') ){
			if ( nebula_option('wireframe_theme') ){
				$nebula_wireframe_size = foldersize(get_theme_root() . '/' . nebula_option('wireframe_theme'));
				echo '<li title="' . nebula_option('wireframe_theme') . '"><i class="fa fa-flag-o"></i> Wireframe directory size: <strong>' . round($nebula_wireframe_size/1048576, 2) . 'mb</strong> </li>';
			}

			if ( nebula_option('staging_theme') ){
				$nebula_staging_size = foldersize(get_theme_root() . '/' . nebula_option('staging_theme'));
				echo '<li title="' . nebula_option('staging_theme') . '"><i class="fa fa-flag"></i> Staging directory size: <strong>' . round($nebula_staging_size/1048576, 2) . 'mb</strong> </li>';
			}
		}

		//Uploads directory size (and max upload size)
		$upload_dir = wp_upload_dir();
		$uploads_size = foldersize($upload_dir['basedir']);
		if ( function_exists('wp_max_upload_size') ){
			$upload_max = '<small>(Max upload: <strong>' . strval(round((int) wp_max_upload_size()/(1024*1024))) . 'mb</strong>)</small>';
		} else if ( ini_get('upload_max_filesize') ){
			$upload_max = '<small>(Max upload: <strong>' . ini_get('upload_max_filesize') . '</strong>)</small>';
		} else {
			$upload_max = '';
		}
		echo '<li><i class="fa fa-picture-o"></i> Uploads directory size: <strong>' . round($uploads_size/1048576, 2) . 'mb</strong> ' . $upload_max . '</li>';

		//Homepage load time
		echo '<div id="testloadcon" style="pointer-events: none; opacity: 0; visibility: hidden; display: none;"></div>';
		echo '<script id="testloadscript">
				jQuery(window).on("load", function(){
					jQuery(".loadtime").css("visibility", "visible");
					beforeLoad = (new Date()).getTime();
					var iframe = document.createElement("iframe");
					iframe.style.width = "1200px";
					iframe.style.height = "0px";
					jQuery("#testloadcon").append(iframe);
					iframe.src = "' . home_url('/') . '";
					jQuery("#testloadcon iframe").on("load", function(){
						stopTimer();
					});
				});
				function stopTimer(){
				    var afterLoad = (new Date()).getTime();
				    var result = (afterLoad - beforeLoad)/1000;
				    jQuery(".loadtime").html(result + " seconds");
				    if ( result > 5 ){ jQuery(".slowicon").addClass("fa-warning"); }
				    jQuery(".serverdetections .fa-spin, #testloadcon, #testloadscript").remove();
				}
			</script>';
		echo '<li><i class="fa fa-clock-o fa-fw"></i> <span title="' . get_home_url() . '" style="cursor: help;">Homepage</span> load time: <a href="http://developers.google.com/speed/pagespeed/insights/?url=' . home_url('/') . '" target="_blank" title="Time is specific to your current environment and therefore may be faster or slower than average."><strong class="loadtime" style="visibility: hidden;"><i class="fa fa-spinner fa-fw fa-spin"></i></strong></a> <i class="slowicon fa" style="color: maroon;"></i></li>';

		//Initial installation date
		function initial_install_date(){
			$nebula_initialized = nebula_option('initialized');
			if ( !empty($nebula_initialized) && $nebula_initialized < getlastmod() ){
				$install_date = '<span title="' . human_time_diff($nebula_initialized) . ' ago"><strong>' . date('F j, Y', $nebula_initialized) . '</strong> <small>@</small> <strong>' . date('g:ia', $nebula_initialized) . '</strong></span> <small>(Nebula Init)</small>';
			} else { //Use the last modified time of the admin page itself
				$install_date = '<span title="' . human_time_diff(getlastmod()) . ' ago"><strong>' . date("F j, Y", getlastmod()) . '</strong> <small>@</small> <strong>' . date("g:ia", getlastmod()) . '</strong></span> <small>(WP Detect)</small>';
			}
			return $install_date;
		}
		echo '<li><i class="fa fa-calendar-o fa-fw"></i> Initial Install: ' . initial_install_date() . '</li>';

		$latest_file = nebula_last_modified();
		echo '<li><i class="fa fa-calendar fa-fw"></i> Last modified: <strong title="' . human_time_diff($latest_file['date']) . ' ago">' . date("F j, Y", $latest_file['date']) . '</strong> <small>@</small> <strong>' . date("g:ia", $latest_file['date']) . '</strong> <small title="' . $latest_file['path'] . '" style="cursor: help;">(' . $latest_file['file'] . ')</small></li>';

		//SCSS last processed date
		$scss_last_processed = ( nebula_data('scss_last_processed') )? '<span title="' . human_time_diff(nebula_data('scss_last_processed')) . ' ago"><strong>' . date("F j, Y", nebula_data('scss_last_processed')) . '</strong> <small>@</small> <strong>' . date("g:i:sa", nebula_data('scss_last_processed')) . '</strong></span>' : '<strong>Never</strong>';
		echo '<li><i class="fa fa-paint-brush fa-fw"></i> SCSS Last Processed: ' . $scss_last_processed . '</li>';
	echo '</ul>';

	//Directory search
	echo '<i id="searchprogress" class="fa fa-search fa-fw"></i> <form id="theme" class="searchfiles"><input class="findterm" type="text" placeholder="Search files" /><select class="searchdirectory">';
	if ( nebula_option('prototype_mode', 'enabled') ){
		echo '<option value="production">Production</option>';
		if ( nebula_option('staging_theme') ){
			echo '<option value="staging">Staging</option>';
		}
		if ( nebula_option('wireframe_theme') ){
			echo '<option value="wireframe">Wireframe</option>';
		}
		echo '<option value="parent">Parent Theme</option>';
	} elseif ( is_child_theme() ){
		echo '<option value="parent">Parent Theme</option><option value="child">Child Theme</option>';
	} else {
		echo '<option value="theme">Theme</option>';
	}
	echo '<option value="plugins">Plugins</option><option value="uploads">Uploads</option></select><input class="searchterm button button-primary" type="submit" value="Search" /></form><br />';
	echo '<div class="search_results"></div>';
}

//Get last modified filename and date from a directory
function nebula_last_modified($directory=null, $last_date=0, $child=false){
	global $latest_file;

	if ( empty($directory) ){
		$directory = get_template_directory();
	}
	$dir = glob_r($directory . '/*');
	$skip_files = array('dev.css', 'dev.scss', '/cache/', '/includes/data/', 'manifest.json', '.bak'); //Files or directories to skip. Be specific!

	foreach ( $dir as $file ){
		if ( is_file($file) ){
			$mod_date = filemtime($file);
			if ( $mod_date > $last_date && !contains($file, $skip_files) ){ //Does not check against skip_extensions() functions on purpose.
				$latest_file['date'] = $mod_date;
				$latest_file['file'] = basename($file);

				if ( is_child_theme() && $child ){
					$latest_file['path'] = 'Child: ';
				} elseif ( is_child_theme() && !$child ){
					$latest_file['path'] = 'Parent: ';
				}
				$latest_file['path'] .= str_replace($directory, '', dirname($file)) . '/' . $latest_file['file'];

				$last_date = $latest_file['date'];
			}
		}
	}

	if ( is_child_theme() && !$child ){
		$latest_child_file = nebula_last_modified(get_stylesheet_directory(), $latest_file['date'], true);
		if ( $latest_child_file['date'] > $latest_file['date'] ){
			return $latest_child_file;
		}
	}

	return $latest_file;
}

//Search theme or plugin files via Developer Information Metabox
add_action('wp_ajax_search_theme_files', 'search_theme_files');
add_action('wp_ajax_nopriv_search_theme_files', 'search_theme_files');
function search_theme_files(){
	if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce')){ die('Permission Denied.'); }

	ini_set('max_execution_time', 120);
	ini_set('memory_limit', '512M');
	$searchTerm = htmlentities(stripslashes($_POST['data'][0]['searchData']));

	if ( strlen($searchTerm) < 3 ){
		echo '<p><strong>Error:</strong> Minimum 3 characters needed to search!</p>';
		die();
	}

	if ( $_POST['data'][0]['directory'] == 'theme' ){
		$dirpath = get_template_directory();
	} elseif ( $_POST['data'][0]['directory'] == 'parent' ){
		$dirpath = get_template_directory();
	} elseif ( $_POST['data'][0]['directory'] == 'child' ){
		$dirpath = get_stylesheet_directory();
	} elseif ( $_POST['data'][0]['directory'] == 'wireframe' ){
		$dirpath = get_theme_root() . '/' . nebula_option('wireframe_theme');
	} elseif ( $_POST['data'][0]['directory'] == 'staging' ){
		$dirpath = get_theme_root() . '/' . nebula_option('staging_theme');
	} elseif ( $_POST['data'][0]['directory'] == 'production' ){
		if ( nebula_option('production_theme') ){
			$dirpath = get_theme_root() . '/' . nebula_option('production_theme');
		} else {
			$dirpath = get_stylesheet_directory();
		}
	} elseif ( $_POST['data'][0]['directory'] == 'plugins' ){
		$dirpath = WP_PLUGIN_DIR;
	} elseif ( $_POST['data'][0]['directory'] == 'uploads' ){
		$uploadDirectory = wp_upload_dir();
		$dirpath = $uploadDirectory['basedir'];
	} else {
		echo '<p><strong>Error:</strong> Please specify a directory to search!</p>';
		die();
	}

	echo '<p class="resulttext">Search results for <strong>"' . $searchTerm . '"</strong> in the <strong>' . $_POST['data'][0]['directory'] . '</strong> directory:</p><br />';

	$file_counter = 0;
	$instance_counter = 0;
	foreach ( glob_r($dirpath . '/*') as $file ){
		$counted = 0;
		if ( is_file($file) ){
		    if ( strpos(basename($file), $searchTerm) !== false ){
			    echo '<p class="resulttext">' . str_replace($dirpath, '', dirname($file)) . '/<strong>' . basename($file) . '</strong></p>';
			    $file_counter++;
			    $counted = 1;
		    }

			$skipFilenames = array('error_log');
		    if ( !contains(basename($file), skip_extensions()) && !contains(basename($file), $skipFilenames) ){
			    foreach ( file($file) as $lineNumber => $line ){
			        if ( stripos(stripslashes($line), $searchTerm) !== false ){
			            $actualLineNumber = $lineNumber+1;
						echo '<div class="linewrap">
								<p class="resulttext">' . str_replace($dirpath, '', dirname($file)) . '/<strong>' . basename($file) . '</strong> on <a class="linenumber" href="#">line ' . $actualLineNumber . '</a>.</p>
								<div class="precon"><pre class="actualline">' . trim(htmlentities($line)) . '</pre></div>
							</div>';
						$instance_counter++;
						if ( $counted == 0 ){
							$file_counter++;
							$counted = 1;
						}
			        }
			    }
		    }
		}
	}
	echo '<br /><p class="resulttext">Found ';
	if ( $instance_counter ){
		echo '<strong>' . $instance_counter . '</strong> instances in ';
	}
	echo '<strong>' . $file_counter . '</strong> file';
	echo ( $file_counter == 1 )? '.</p>': 's.</p>';
	exit();
}

//Change default values for the upload media box
//These can also be changed by navigating to .../wp-admin/options.php
add_action('after_setup_theme', 'custom_media_display_settings');
function custom_media_display_settings(){
	//update_option('image_default_align', 'center');
	update_option('image_default_link_type', 'none');
	//update_option('image_default_size', 'large');
}

//Add columns to user listings
add_filter('manage_users_columns', 'nebula_user_columns_head');
function nebula_user_columns_head($defaults){
    $defaults['company'] = 'Company';
    $defaults['status'] = 'Status';
    $defaults['id'] = 'ID';
    return $defaults;
}
add_action('manage_users_custom_column', 'nebula_user_columns_content', 15, 3);
function nebula_user_columns_content($value='', $column_name, $id){
    if ( $column_name == 'company' ){
		return get_the_author_meta('jobcompany', $id);
	}
    if ( $column_name == 'status' ){
		if ( nebula_is_user_online($id) ){
			$online_now = '<i class="fa fa-caret-right" style="color: green;"></i> <strong>Online Now</strong>';
			if ( nebula_user_single_concurrent($id) > 1 ){
				$online_now .= '<br/><small>(<strong>' . nebula_user_single_concurrent($id) . '</strong> locations)</small>';
			}
			return $online_now;
		} else {
			return ( nebula_user_last_online($id) )? '<small>Last Seen: <br /><em>' . date('M j, Y @ g:ia', nebula_user_last_online($id)) . '</em></small>' : '';
		}
	}
	if ( $column_name == 'id' ){
		return $id;
	}
}

//Add ID column on post/page listings
add_filter('manage_posts_columns', 'nebula_id_columns_head');
add_filter('manage_pages_columns', 'nebula_id_columns_head');
function nebula_id_columns_head($defaults){
    $defaults['id'] = 'ID';
    return $defaults;
}
add_action('manage_posts_custom_column', 'nebula_id_columns_content', 15, 3);
add_action('manage_pages_custom_column', 'nebula_id_columns_content', 15, 3);
function nebula_id_columns_content($column_name, $id){
    if ( $column_name == 'id' ){
		echo $id;
	}
}

//Remove most Yoast SEO columns
$post_types = get_post_types(array('public' => true), 'names');
if ( is_array($post_types) && $post_types !== array() ){
	foreach ( $post_types as $post_type ){
		add_filter('manage_edit-' . $post_type . '_columns', 'remove_yoast_columns'); //@TODO "Nebula" 0: This does not always work.
	}
}
function remove_yoast_columns($columns){
	//unset($columns['wpseo-score']);
	unset($columns['wpseo-title']);
	unset($columns['wpseo-metadesc']);
	unset($columns['wpseo-focuskw']);
    return $columns;
}

//Duplicate post
add_action('admin_action_duplicate_post_as_draft', 'duplicate_post_as_draft');
function duplicate_post_as_draft(){
	global $wpdb;
	if ( !(isset($_GET['post']) || isset($_POST['post'])  || (isset($_REQUEST['action']) && 'duplicate_post_as_draft' == $_REQUEST['action'])) ){
		wp_die('No post to duplicate has been supplied!');
	}

	$post_id = ( isset($_GET['post'] )? $_GET['post'] : $_POST['post']); //Get the original post id
	$post = get_post( $post_id ); //Get all the original post data

	$current_user = wp_get_current_user();
	$new_post_author = $current_user->ID; //Set post author (default by current user). For original author change to: $new_post_author = $post->post_author;

	//If post data exists, create the post duplicate
	if ( isset($post) && $post != null ){

		//Insert the post by wp_insert_post() function
		$new_post_id = wp_insert_post(array(
			'comment_status' => $post->comment_status,
			'ping_status' => $post->ping_status,
			'post_author' => $new_post_author,
			'post_content' => $post->post_content,
			'post_excerpt' => $post->post_excerpt,
			'post_name' => $post->post_name,
			'post_parent' => $post->post_parent,
			'post_password' => $post->post_password,
			'post_status' => 'draft',
			'post_title' => $post->post_title . ' copy',
			'post_type' => $post->post_type,
			'to_ping' => $post->to_ping,
			'menu_order' => $post->menu_order
		));

		//Get all current post terms ad set them to the new post draft
		$taxonomies = get_object_taxonomies($post->post_type); //returns array of taxonomy names for post type, ex array("category", "post_tag");
		foreach ( $taxonomies as $taxonomy ){
			$post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
			wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
		}

		//Duplicate all post meta
		$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
		if ( count($post_meta_infos) != 0 ){
			$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
			foreach ( $post_meta_infos as $meta_info ){
				$meta_key = $meta_info->meta_key;
				$meta_value = addslashes($meta_info->meta_value);
				$sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
			}
			$sql_query .= implode(" UNION ALL ", $sql_query_sel);
			$wpdb->query($sql_query);
		}

		wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id)); //Redirect to the edit post screen for the new draft
		exit;
	} else {
		wp_die('Post creation failed, could not find original post: ' . $post_id);
	}
}

//Add the duplicate link to action list for post_row_actions (This works for custom post types too).
//Additional post types with the following: add_filter('{post type name}_row_actions', 'rd_duplicate_post_link', 10, 2);
add_filter('post_row_actions', 'rd_duplicate_post_link', 10, 2);
add_filter('page_row_actions', 'rd_duplicate_post_link', 10, 2);
function rd_duplicate_post_link($actions, $post){
	if ( current_user_can('edit_posts') ){
		$actions['duplicate'] = '<a href="admin.php?action=duplicate_post_as_draft&amp;post=' . $post->ID . '" title="Duplicate this item" rel="permalink">Duplicate</a>';
	}
	return $actions;
}

//Show File URL column on Media Library listings
add_filter('manage_media_columns', 'muc_column');
function muc_column($cols){
	$cols["media_url"] = "File URL";
	return $cols;
}
add_action('manage_media_custom_column', 'muc_value', 10, 2);
function muc_value( $column_name, $id ){
	if ( $column_name == "media_url" ){
		echo '<input type="text" width="100%" value="' . wp_get_attachment_url($id) . '" readonly />';
	}
}

//Enable editor style for the TinyMCE WYSIWYG editor.
add_editor_style('stylesheets/css/tinymce.css');

//Enable All Settings page for only Developers who are Admins
if ( is_dev(true) ){
	add_action('admin_menu', 'all_settings_link');
	function all_settings_link(){
	    add_theme_page('All Settings', 'All Settings', 'administrator', 'options.php');
	}
}

//Clear caches when plugins are activated if W3 Total Cache is active
add_action('admin_init', 'clear_all_w3_caches');
function clear_all_w3_caches(){
	include_once(ABSPATH . 'wp-admin/includes/plugin.php');
	if ( is_plugin_active('w3-total-cache/w3-total-cache.php') && isset($_GET['activate']) && $_GET['activate'] == 'true' ){
		if ( function_exists('w3tc_pgcache_flush') ){
			w3tc_pgcache_flush();
		}
	}
}

//Admin Footer Enhancements
//Left Side
add_filter('admin_footer_text', 'change_admin_footer_left');
function change_admin_footer_left(){
    return pinckneyhugogroup() . ' &bull; <a href="https://www.google.com/maps/dir/Current+Location/760+West+Genesee+Street+Syracuse+NY+13204" target="_blank">760 West Genesee Street, Syracuse, NY 13204</a> &bull; ' . nebula_tel_link('13154786700');
}
//Right Side
add_filter('update_footer', 'change_admin_footer_right', 11);
function change_admin_footer_right(){
	global $wp_version;
	$child = ( is_child_theme() )? ' <small>(Child)</small>' : '';
    return '<span><a href="https://codex.wordpress.org/WordPress_Versions" target="_blank">WordPress</a> <strong>' . $wp_version . '</strong></span>, <span title="Committed: ' . nebula_version('date') . '"><a href="https://gearside.com/nebula" target="_blank">Nebula</a> <strong class="nebula">' . nebula_version('version') . '</strong>' . $child . '</span>';
}

//Internal Search Keywords Metabox and Custom Field
add_action('load-post.php', 'nebula_post_meta_boxes_setup');
add_action('load-post-new.php', 'nebula_post_meta_boxes_setup');
function nebula_add_post_meta_boxes(){
	$builtin_types = array('post', 'page', 'attachment');
	$custom_types = get_post_types(array('_builtin' => false));

	foreach ( $builtin_types as $builtin_type ){
		add_meta_box('nebula-internal-search-keywords', 'Internal Search Keywords', 'nebula_internal_search_keywords_meta_box', $builtin_type, 'side', 'default');
	}

	foreach( $custom_types as $custom_type ){
		if ( !in_array($custom_type, array('acf', 'wpcf7_contact_form')) ){
			add_meta_box('nebula-internal-search-keywords', 'Internal Search Keywords', 'nebula_internal_search_keywords_meta_box', $custom_type, 'side', 'default');
		}
	}
}
function nebula_internal_search_keywords_meta_box($object, $box){
	wp_nonce_field(basename(__FILE__), 'nebula_internal_search_keywords_nonce');
	?>
	<div>
		<p style="font-size: 12px; color: #444;">Use plurals since parts of words will return in search results (unless plural has a different spelling than singular; then add both).</p>
		<textarea id="nebula-internal-search-keywords" class="textarea" name="nebula-internal-search-keywords" placeholder="Additional keywords to help find this page..." style="width: 100%; min-height: 150px;"><?php echo get_post_meta($object->ID, 'nebula_internal_search_keywords', true); ?></textarea>
	</div>
<?php }
function nebula_post_meta_boxes_setup(){
	add_action('add_meta_boxes', 'nebula_add_post_meta_boxes');
	add_action('save_post', 'nebula_save_post_class_meta', 10, 2);
}
function nebula_save_post_class_meta($post_id, $post){
	if ( !isset($_POST['nebula_internal_search_keywords_nonce']) || !wp_verify_nonce($_POST['nebula_internal_search_keywords_nonce'], basename(__FILE__)) ){
		return $post_id;
	}

	$post_type = get_post_type_object($post->post_type); //Get the post type object.
	if ( !current_user_can($post_type->cap->edit_post, $post_id) ){ //Check if the current user has permission to edit the post.
		return $post_id;
	}

	$new_meta_value = sanitize_text_field($_POST['nebula-internal-search-keywords']); //Get the posted data and sanitize it if needed.
	$meta_value = get_post_meta($post_id, 'nebula_internal_search_keywords', true); //Get the meta value of the custom field key.
	if ( $new_meta_value && $meta_value == '' ){ //If a new meta value was added and there was no previous value, add it.
		add_post_meta($post_id, 'nebula_internal_search_keywords', $new_meta_value, true);
	} elseif ( $new_meta_value && $meta_value != $new_meta_value ){ //If the new meta value does not match the old value, update it.
		update_post_meta($post_id, 'nebula_internal_search_keywords', $new_meta_value);
	} elseif ( $new_meta_value == '' && $meta_value ){ //If there is no new meta value but an old value exists, delete it.
		delete_post_meta($post_id, 'nebula_internal_search_keywords', $meta_value);
	}
}