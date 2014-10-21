<?php

//Disable auto curly quotes
remove_filter('the_content', 'wptexturize');
remove_filter('the_excerpt', 'wptexturize');
remove_filter('comment_text', 'wptexturize');


//Disable Admin Bar (and WP Update Notifications) for everyone but administrators (or specific users)
if ( nebula_settings_conditional('nebula_admin_bar', 'disabled') ) {
	add_action('wp_print_scripts', 'dequeue_admin_bar', 9999);
	add_action('wp_print_styles', 'dequeue_admin_bar', 9999);
	function dequeue_admin_bar() {
		wp_deregister_style('admin-bar');
		wp_dequeue_script('admin-bar');
	}

	add_action('init', 'admin_only_features');
	function admin_only_features() {
		remove_action('wp_footer', 'wp_admin_bar_render', 1000); //For the front-end

		//CSS override for the frontend
		add_filter('wp_head','remove_admin_bar_style_frontend', 99);
		function remove_admin_bar_style_frontend() {
			echo '<style type="text/css" media="screen">
			html { margin-top: 0px !important; }
			* html body { margin-top: 0px !important; }
			</style>';
		}
	}
}

//Disable Wordpress Core update notifications in WP Admin
if ( nebula_settings_conditional('nebula_wp_core_updates_notify', 'disabled') ) {
	add_filter('pre_site_transient_update_core', create_function('$a', "return null;"));
}

//Show update warning on Wordpress Core/Plugin update admin pages
if ( nebula_settings_conditional('nebula_phg_plugin_update_warning') ) {
	$filename = basename($_SERVER['REQUEST_URI']);
	if ( $filename == 'plugins.php' ) {
		add_action('admin_notices','plugin_warning');
		function plugin_warning(){
			echo "<div id='pluginwarning' class='error'><p><strong>WARNING:</strong> Updating plugins may cause irreversible errors to your website!</p><p>Contact <a href='http://www.pinckneyhugo.com'>Pinckney Hugo Group</a> if a plugin needs to be updated: " . nebula_tel_link('13154786700') . "</p></div>";
		}
	} elseif ( $filename == 'update-core.php') {
		add_action('admin_notices','plugin_warning');
		function plugin_warning(){
			echo "<div id='pluginwarning' class='error'><p><strong>WARNING:</strong> Updating Wordpress core or plugins may cause irreversible errors to your website!</p><p>Contact <a href='http://www.pinckneyhugo.com'>Pinckney Hugo Group</a> if a plugin needs to be updated: " . nebula_tel_link('13154786700') . "</p></div>";
		}
	}
} else {
	add_action('admin_head', 'warning_style_unset');
	function warning_style_unset(){
		echo '<style>.update-nag a, .update-core-php input#upgrade, .update-core-php input#upgrade-plugins, .update-core-php input#upgrade-plugins-2, .plugins-php .update-message a, .plugins-php .deactivate a {cursor: pointer !important;}</style>';
	}
}

//Control session time (for the "Remember Me" checkbox)
add_filter('auth_cookie_expiration', 'nebula_session_expire');
function nebula_session_expire($expirein) {
    return 2592000; //30 days (Default is 1209600 (14 days)
}

//Disable the logged-in monitoring modal
remove_action('admin_enqueue_scripts', 'wp_auth_check_load');

//Custom login screen
add_action('login_head', 'custom_login_css');
function custom_login_css() {
	//Only use BG image and animation on direct requests (disable for iframe logins after session timeouts).
	if( empty($_POST['signed_request']) ) {
	    echo "<script>(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');ga('create', '" . $GLOBALS['ga'] . "', 'auto');</script>";
	}
}


//Change link of logo to live site
add_filter('login_headerurl', 'custom_login_header_url');
function custom_login_header_url() {
    return home_url('/');
}


//Change alt of image
add_filter('login_headertitle', 'new_wp_login_title');
function new_wp_login_title() {
    return get_option('blogname');
}


//Welcome Panel
if ( nebula_settings_conditional('nebula_phg_welcome_panel') ) {
	remove_action('welcome_panel','wp_welcome_panel');
	add_action('welcome_panel','nebula_welcome_panel');
	function nebula_welcome_panel() {
		include(TEMPLATEPATH . '/includes/welcome.php');
	}
} else {
	remove_action('welcome_panel','wp_welcome_panel');
}


//Remove unnecessary Dashboard metaboxes
if ( nebula_settings_conditional('nebula_unnecessary_metaboxes') ) {
	add_action('wp_dashboard_setup', 'remove_dashboard_metaboxes');
	function remove_dashboard_metaboxes() {
		//If necessary, dashboard metaboxes can be unset. To best future-proof, use remove_meta_box().
		//global $wp_meta_boxes;
		//unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);

	    remove_meta_box('dashboard_primary', 'dashboard', 'side'); //Wordpress News
	    remove_meta_box('dashboard_secondary', 'dashboard', 'side');
	    remove_meta_box('dashboard_plugins', 'dashboard', 'normal');
	    remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');
	    remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
	}
}


function is_dev() {
	//Check if the current IP address matches any of the dev IP address from Nebula Settings
	$devIPs = explode(',', get_option('nebula_dev_ip'));
	foreach ( $devIPs as $devIP ) {
		if ( trim($devIP) == $_SERVER['REMOTE_ADDR'] ) {
			return true;
		}
	}

	//Check if the current user's email domain matches any of the dev email domains from Nebula Settings
	$current_user = wp_get_current_user();
	list($current_user_email, $current_user_domain) = explode('@', $current_user->user_email);

	$devEmails = explode(',', get_option('nebula_dev_email_domain'));
	foreach ( $devEmails as $devEmail ) {
		if ( trim($devEmail) == $current_user_domain ) {
			return true;
		}
	}

	return false;
}


//TODO Metabox
//This metabox tracks TODO messages throughout development.
//@TODO "Nebula" 0: I think this can be way more optimized. It also is dependent on JS to hide filenames w/ only 0 priority TODOs.
if ( nebula_settings_conditional('nebula_todo_metabox') ) {

	if ( is_dev() ) {
		add_action('wp_dashboard_setup', 'todo_metabox');
	}

	function todo_metabox() {
		global $wp_meta_boxes;
		wp_add_dashboard_widget('todo_manager', '@TODO Manager', 'dashboard_todo_manager');
	}

	function dashboard_todo_manager() {

		echo '<p class="todoresults_title"><strong>Active @TODO Comments</strong> <a class="todo_help_icon" href="#"><i class="fa fw fa-question-circle"></i> Syntax</a></p>';
		echo '<div class="todo_help_con">
			<p class="todo_help_desc">
				@TODO "Category" Priority: Write your message here<br/>
				Priority (0 = Hidden, 5 = Important) and Category are optional.<br/>
				Ex: @TODO "Example" 4: Lorem ipsum dolor sit amet.<br/>
				<a class="togglehiddentodos" href="#">Toggle Hidden TODOs</a>
			</p>
		</div>';

		echo '<div class="todo_results">';
		$todo_last_filename = '';
		$todo_dirpath = get_template_directory();
		$todo_file_counter = 0;
		$todo_instance_counter = 0;
		foreach ( glob_r($todo_dirpath . '/*') as $todo_file ) {
			$todo_counted = 0;
			$todo_hidden = 0;
			if ( is_file($todo_file) ) {
			    if ( strpos(basename($todo_file), '@TODO') !== false ) {
				    echo '<p class="resulttext">' . str_replace($todo_dirpath, '', dirname($todo_file)) . '/<strong>' . basename($todo_file) . '</strong></p>';
				    $todo_file_counter++;
				    $todo_counted = 1;
			    }

			    $todo_skipExtensions = array('jpg', 'jpeg', 'png', 'gif', 'ico', 'tiff', 'psd', 'ai', 'apng', 'bmp', 'otf', 'ttf', 'ogv', 'flv', 'fla', 'mpg', 'mpeg', 'avi', 'mov', 'woff', 'eot', 'mp3', 'mp4', 'wmv', 'wma', 'aiff', 'zip', 'zipx', 'rar', 'exe', 'dmg', 'swf', 'pdf', 'pdfx', 'pem');
			    $todo_skipFilenames = array('README.md', 'nebula_admin_functions.php', 'error_log', 'Mobile_Detect.php', 'class-tgm-plugin-activation.php');

			    if ( !contains(basename($todo_file), $todo_skipExtensions) && !contains(basename($todo_file), $todo_skipFilenames) ) {
				    foreach ( file($todo_file) as $todo_lineNumber => $todo_line ) {
						$todo_hidden = 0;

				        if ( stripos($todo_line, '@TODO') !== false ) {
				            $todo_actualLineNumber = $todo_lineNumber+1;

							$the_full_todo = substr($todo_line, strpos($todo_line, "@TODO"));
							$the_todo_meta = current(explode(":", $the_full_todo));

							//Get the priority
							preg_match_all('!\d+!', $the_todo_meta, $the_todo_ints);
							$todo_hidden = 0;
							if ( $the_todo_ints[0][0] != '' ) {
								switch ( true ) {
									case ( $the_todo_ints[0][0] >= 5 ) :
										$todo_hidden = 0;
										$the_todo_icon_color = '#d92827';
										break;
									case ( $the_todo_ints[0][0] == 4 ) :
										$todo_hidden = 0;
										$the_todo_icon_color = '#e38a2c';
										break;
									case ( $the_todo_ints[0][0] == 3 ) :
										$todo_hidden = 0;
										$the_todo_icon_color = '#dda65c';
										break;
									case ( $the_todo_ints[0][0] == 2 ) :
										$todo_hidden = 0;
										$the_todo_icon_color = '#d3bd9f';
										break;
									case ( $the_todo_ints[0][0] == 1 ) :
										$todo_hidden = 0;
										$the_todo_icon_color = '#ccc';
										break;
									case ( $the_todo_ints[0][0] == 0 ) :
										$todo_hidden = 1;
										$the_todo_icon_color = '#0098d7';
										break;
									default :
										$todo_hidden = 0;
										$the_todo_icon_color = '#999';
										break;
								}
							} else {
								$todo_hidden = 0;
							}

							if ( $todo_hidden == 1 ) {
								$todo_hidden_style = 'style="display: none;"';
								$todo_hidden_class = 'hidden_todo';
							} else {
								$todo_hidden_style = '';
								$todo_hidden_class = '';
							}

							//Get the category
							preg_match_all('/".*?"|\'.*?\'/', $the_todo_meta, $the_todo_quote_check);
							if ( $the_todo_quote_check[0][0] != '' ) {
								$the_todo_category = substr($the_todo_quote_check[0][0], 1, -1);
								$the_todo_category_html = '<span class="todocategory" style="background: ' . $the_todo_icon_color . ';">' . $the_todo_category . '</span>';
							} else {
								$the_todo_quote_check = '';
								$the_todo_category = '';
								$the_todo_category_html = '';
							}

							//Get the message
							$the_todo_message_full = substr($the_full_todo, strpos($the_full_todo, ":") + 1);
							$end_todo_message_strings = array('-->', '?>', '*/');
							$the_todo_message = explode($end_todo_message_strings[0], str_replace($end_todo_message_strings, $end_todo_message_strings[0], $the_todo_message_full));


							$todo_this_filename = str_replace($todo_dirpath, '', dirname($todo_file)) . '/' . basename($todo_file);
							if ( $todo_last_filename != $todo_this_filename ) {
								if ( $todo_last_filename != '' ) {
									echo '</div><!--/todofilewrap-->';
								}


								echo '<div class="todofilewrap">';
								echo '<p class="todofilename">' . str_replace($todo_dirpath, '', dirname($todo_file)) . '/<strong>' . basename($todo_file) . '</strong></p>';
							}

							echo '<div class="linewrap ' . $todo_hidden_class . '" ' . $todo_hidden_style . '>
									<p class="todoresult"> ' . $the_todo_category_html . ' <a class="linenumber" href="#">Line ' . $todo_actualLineNumber . '</a> <span class="todomessage">' . $the_todo_message[0] . '</span></p>
									<div class="precon"><pre class="actualline">' . trim(htmlentities($todo_line)) . '</pre></div>
								</div>';

							$todo_last_filename = $todo_this_filename;

							$todo_instance_counter++;
							if ( $todo_counted == 0 ) {
								$todo_file_counter++;
								$todo_counted = 1;
							}
				        }
				    }
			    }
			}
		}

		echo '</div><!--/todofilewrap-->';
		echo '</div><!--/todo_results-->';
	}
}


//Custom PHG Metabox
//If user's email address ends in @pinckneyhugo.com or if IP address matches the dev IP (set in Nebula Settings).
if ( nebula_settings_conditional('nebula_phg_metabox') ) {

	if ( is_dev() ) {
		add_action('wp_dashboard_setup', 'phg_dev_metabox');
	}

	function phg_dev_metabox() {
		global $wp_meta_boxes;
		wp_add_dashboard_widget('phg_developer_info', 'PHG Developer Info', 'dashboard_developer_info');
	}
	function dashboard_developer_info() {
		//Get last modified filename and date
		$dir = glob_r( get_template_directory() . '/*');
		$last_date = 0;
		$skip_files = array('dev.css');

		foreach($dir as $file) {
			if( is_file($file) ) {
				$mod_date = filemtime($file);
				if ( $mod_date > $last_date && !contains(basename($file), $skip_files) ) {
					$last_date = $mod_date;
					$last_filename = basename($file);
				}
			}
		}
		$nebula_size = foldersize(get_template_directory());
		$upload_dir = wp_upload_dir();
		$uploads_size = foldersize($upload_dir['basedir']);

		$secureServer = '';
		if ( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ) {
			$secureServer = '<small><i class="fa fa-lock fa-fw"></i>Secured Connection</small>';
		}

		function top_domain_name($url){
			$alldomains = explode(".", $url);
			return $alldomains[count($alldomains)-2] . "." . $alldomains[count($alldomains)-1];
		}
		$dnsrecord = ( function_exists('gethostname') ) ? dns_get_record(top_domain_name(gethostname()), DNS_NS) : '';

		function initial_install_date(){
			if ( get_option('nebula_initialized') != '' && (get_option('nebula_initialized') < getlastmod()) ) {
				$install_date = '<strong>' . date('F j, Y', get_option('nebula_initialized')) . '</strong> <small>@</small> <strong>' . date('g:ia', get_option('nebula_initialized')) . '</strong> <small>(Nebula Init)</small>';
			} else {
				$install_date = '<strong>' . date("F j, Y", getlastmod()) . '</strong> <small>@</small> <strong>' . date("g:ia", getlastmod()) . '</strong> <small>(WP Detect)</small>';
			}
			return $install_date;
		}

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
				    if ( result > 5 ) { jQuery(".slowicon").addClass("fa-warning"); }
				    jQuery(".serverdetections .fa-spin, #testloadcon, #testloadscript").remove();
				}
				</script>';

		echo '<ul class="serverdetections">';
			if ( WP_DEBUG ) {
				echo '<li style="color: red;"><i class="fa fa-exclamation-triangle fa-fw"></i> <strong>Warning:</strong> WP_DEBUG is Enabled!</li>';
			}
			echo '<li><i class="fa fa-info-circle fa-fw"></i> Domain: <strong>' . $_SERVER['SERVER_NAME'] . '</strong></li>';
			if ( function_exists('gethostname') ) {
				echo '<li><i class="fa fa-hdd-o fa-fw"></i> Hostname: <strong>' . top_domain_name(gethostname()) . '</strong> <small>(' . top_domain_name($dnsrecord[0]['target']) . ')</small></li>';
			}
			echo '<li><i class="fa fa-upload fa-fw"></i> Server IP: <strong><a href="http://whatismyipaddress.com/ip/' . $_SERVER['SERVER_ADDR'] . '" target="_blank">' . $_SERVER['SERVER_ADDR'] . '</a></strong> ' . $secureServer . ' <small>(' . $_SERVER['SERVER_SOFTWARE'] . ')</small></li>';
			echo '<li><i class="fa fa-wrench fa-fw"></i> PHP Version: <strong>' . phpversion() . '</strong></li>';
			echo '<li><i class="fa fa-database fa-fw"></i> MySQL Version: <strong>' . mysql_get_server_info() . '</strong></li>';
			echo '<li><i class="fa fa-code"></i> Theme directory size: <strong>' . round($nebula_size/1048576, 2) . 'mb</strong> </li>';
			echo '<li><i class="fa fa-picture-o"></i> Uploads directory size: <strong>' . round($uploads_size/1048576, 2) . 'mb</strong> </li>';
			echo '<li><i class="fa fa-clock-o fa-fw"></i> Homepage load time: <a href="http://developers.google.com/speed/pagespeed/insights/?url=' . home_url('/') . '" target="_blank" title="Time is specific to your current environment and therefore may be faster or slower than average."><strong class="loadtime" style="visibility: hidden;"><i class="fa fa-spinner fa-fw fa-spin"></i></strong></a> <i class="slowicon fa" style="color: maroon;"></i></li>';
			echo '<li><i class="fa fa-calendar-o fa-fw"></i> Initial Install: ' . initial_install_date() . '</li>';
			echo '<li><i class="fa fa-calendar fa-fw"></i> Last modified: <strong>' . date("F j, Y", $last_date) . '</strong> <small>@</small> <strong>' . date("g:ia", $last_date) . '</strong> <small>(' . $last_filename . ')</small></li>';
		echo '</ul>';

		echo '<i id="searchprogress" class="fa fa-search fa-fw"></i> <form id="theme" class="searchfiles"><input class="findterm" type="text" placeholder="Search files" /><select class="searchdirectory"><option value="theme">Theme</option><option value="plugins">Plugins</option><option value="uploads">Uploads</option></select><input class="searchterm button button-primary" type="submit" value="Search" /></form><br/>';

		echo '<div class="search_results"></div>';
	}
}

//Search theme or plugin files via PHG Metabox
add_action('wp_ajax_search_theme_files', 'search_theme_files');
add_action('wp_ajax_nopriv_search_theme_files', 'search_theme_files');
function search_theme_files() {
	if ( strlen($_POST['data'][0]['searchData']) < 3 ) {
		echo '<p><strong>Error:</strong> Minimum 3 characters needed to search!</p>';
		die();
	}

	if ( $_POST['data'][0]['directory'] == 'theme' ) {
		$dirpath = get_template_directory();
	} elseif ( $_POST['data'][0]['directory'] == 'plugins' ) {
		$dirpath = WP_PLUGIN_DIR;
	} elseif ( $_POST['data'][0]['directory'] == 'uploads' ) {
		$uploadDirectory = wp_upload_dir();
		$dirpath = $uploadDirectory['basedir'];
	} else {
		echo '<p><strong>Error:</strong> Please specify a directory to search!</p>';
		die();
	}

	echo '<p class="resulttext">Search results for <strong>"' . $_POST['data'][0]['searchData'] . '"</strong> in the <strong>' . $_POST['data'][0]['directory'] . '</strong> directory:</p><br/>';

	$file_counter = 0;
	$instance_counter = 0;
	foreach ( glob_r($dirpath . '/*') as $file ) {
		$counted = 0;
		if ( is_file($file) ) {
		    if ( strpos(basename($file), $_POST['data'][0]['searchData']) !== false ) {
			    echo '<p class="resulttext">' . str_replace($dirpath, '', dirname($file)) . '/<strong>' . basename($file) . '</strong></p>';
			    $file_counter++;
			    $counted = 1;
		    }

		    $skipExtensions = array('jpg', 'jpeg', 'png', 'gif', 'ico', 'tiff', 'psd', 'ai', 'apng', 'bmp', 'otf', 'ttf', 'ogv', 'flv', 'fla', 'mpg', 'mpeg', 'avi', 'mov', 'woff', 'eot', 'mp3', 'mp4', 'wmv', 'wma', 'aiff', 'zip', 'zipx', 'rar', 'exe', 'dmg', 'swf', 'pdf', 'pdfx', 'pem');
		    $skipFilenames = array('error_log');
		    if ( !contains(basename($file), $skipExtensions) && !contains(basename($file), $skipFilenames) ) {
			    foreach ( file($file) as $lineNumber => $line ) {
			        if ( stripos($line, $_POST['data'][0]['searchData']) !== false ) {
			            $actualLineNumber = $lineNumber+1;
						echo '<div class="linewrap">
								<p class="resulttext">' . str_replace($dirpath, '', dirname($file)) . '/<strong>' . basename($file) . '</strong> on <a class="linenumber" href="#">line ' . $actualLineNumber . '</a>.</p>
								<div class="precon"><pre class="actualline">' . trim(htmlentities($line)) . '</pre></div>
							</div>';
						$instance_counter++;
						if ( $counted == 0 ) {
							$file_counter++;
							$counted = 1;
						}
			        }
			    }
		    }
		}
	}
	echo '<br/><p class="resulttext">Found ';
	if ( $instance_counter ) {
		echo '<strong>' . $instance_counter . '</strong> instances in ';
	}
	echo '<strong>' . $file_counter . '</strong> file';
	if ( $file_counter == 1 ) {
		echo '.</p>';
	} else {
		echo 's.</p>';
	}
	exit();
}


//Only allow admins to modify Contact Forms //@TODO "Nebula" 0: Currently does not work because these constants are already defined!
//define('WPCF7_ADMIN_READ_CAPABILITY', 'manage_options');
//define('WPCF7_ADMIN_READ_WRITE_CAPABILITY', 'manage_options');


//Change default values for the upload media box
//These can also be changed by navigating to .../wp-admin/options.php
add_action('after_setup_theme', 'custom_media_display_settings');
function custom_media_display_settings() {
	//update_option('image_default_align', 'center');
	update_option('image_default_link_type', 'none');
	//update_option('image_default_size', 'large');
}


//Add ID column on post/page listings
add_filter('manage_posts_columns', 'id_columns_head');
add_action('manage_posts_custom_column', 'id_columns_content', 10, 2);
add_filter('manage_pages_columns', 'id_columns_head');
add_action('manage_pages_custom_column', 'id_columns_content', 10, 2);
function id_columns_head($defaults) {
    $defaults['id'] = 'ID';
    return $defaults;
}
function id_columns_content($column_name, $post_ID) {
    if ( $column_name == 'id' ){
		echo $post_ID;
	}
}


//Duplicate post
add_action( 'admin_action_duplicate_post_as_draft', 'duplicate_post_as_draft' );
function duplicate_post_as_draft(){
	global $wpdb;
	if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'duplicate_post_as_draft' == $_REQUEST['action'] ) ) ) {
		wp_die('No post to duplicate has been supplied!');
	}

	//Get the original post id
	$post_id = (isset($_GET['post']) ? $_GET['post'] : $_POST['post']);
	//Get all the original post data
	$post = get_post( $post_id );

	//Set post author (default by current user). For original author change to: $new_post_author = $post->post_author;
	$current_user = wp_get_current_user();
	$new_post_author = $current_user->ID;

	//If post data exists, create the post duplicate
	if (isset( $post ) && $post != null) {
		//New post data array
		$args = array(
			'comment_status' => $post->comment_status,
			'ping_status'    => $post->ping_status,
			'post_author'    => $new_post_author,
			'post_content'   => $post->post_content,
			'post_excerpt'   => $post->post_excerpt,
			'post_name'      => $post->post_name,
			'post_parent'    => $post->post_parent,
			'post_password'  => $post->post_password,
			'post_status'    => 'draft',
			'post_title'     => $post->post_title . ' copy',
			'post_type'      => $post->post_type,
			'to_ping'        => $post->to_ping,
			'menu_order'     => $post->menu_order
		);

		//Insert the post by wp_insert_post() function
		$new_post_id = wp_insert_post( $args );

		//Get all current post terms ad set them to the new post draft
		$taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
		foreach ($taxonomies as $taxonomy) {
			$post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
			wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
		}

		//Duplicate all post meta
		$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
		if (count($post_meta_infos)!=0) {
			$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
			foreach ($post_meta_infos as $meta_info) {
				$meta_key = $meta_info->meta_key;
				$meta_value = addslashes($meta_info->meta_value);
				$sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
			}
			$sql_query.= implode(" UNION ALL ", $sql_query_sel);
			$wpdb->query($sql_query);
		}

		//Redirect to the edit post screen for the new draft
		wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
		exit;
	} else {
		wp_die('Post creation failed, could not find original post: ' . $post_id);
	}
}

//Add the duplicate link to action list for post_row_actions (This works for custom post types too).
//Additional post types with the following: add_filter('{post type name}_row_actions', 'rd_duplicate_post_link', 10, 2);
add_filter( 'post_row_actions', 'rd_duplicate_post_link', 10, 2 );
add_filter('page_row_actions', 'rd_duplicate_post_link', 10, 2);
function rd_duplicate_post_link( $actions, $post ) {
	if (current_user_can('edit_posts')) {
		$actions['duplicate'] = '<a href="admin.php?action=duplicate_post_as_draft&amp;post=' . $post->ID . '" title="Duplicate this item" rel="permalink">Duplicate</a>';
	}
	return $actions;
}


//Show File URL column on Media Library listings
add_filter('manage_media_columns', 'muc_column');
function muc_column( $cols ) {
	$cols["media_url"] = "File URL";
	return $cols;
}
add_action('manage_media_custom_column', 'muc_value', 10, 2);
function muc_value( $column_name, $id ) {
	if ( $column_name == "media_url" ) {
		echo '<input type="text" width="100%" value="' . wp_get_attachment_url( $id ) . '" readonly />';
		//echo '<input type="text" width="100%" onclick="jQuery(this).select();" value="'. wp_get_attachment_url( $id ). '" readonly />'; //This selects the text on click
	}
}


//Enable editor-style.css for the WYSIWYG editor.
add_editor_style('css/editor-style.css');


//Clear caches when plugins are activated if W3 Total Cache is active
add_action('admin_init', 'clear_all_w3_caches');
function clear_all_w3_caches(){
	include_once(ABSPATH . 'wp-admin/includes/plugin.php');
	if ( is_plugin_active('w3-total-cache/w3-total-cache.php') && isset($_GET['activate']) && $_GET['activate'] == 'true' ) {
		if ( function_exists('w3tc_pgcache_flush') ) {
			w3tc_pgcache_flush();
		}
	}
}


//Nebula Help Tab
add_action('in_admin_header', 'nebula_help_tabs');
function nebula_help_tabs() {
	if ( $screen = get_current_screen() ) {
		$help_tabs = $screen->get_help_tabs();
		$screen->remove_help_tabs();

		$youarehere = '<i class="fa fa-arrow-circle-right" title="You are here."></i> '; //@TODO "Nebula" 0: Detect current page and place this variable accordingly.

		$screen->add_help_tab(array(
			'id' => 'nebula_help',
			'title' => 'Nebula',
			'content' => '
				<h2>Nebula Overview</h2>
				<p>' . $youarehere . '<strong><a class="nebula_help_link" href="' . get_admin_url() . '">Dashboard</a></strong> - Nebula help content coming soon.</p>
				<p><strong><a class="nebula_help_link" href="' . get_admin_url() . 'themes.php?page=nebula_settings">Settings</a></strong> - Nebula help content coming soon.</p>
				<p><strong>Shortcodes</strong> - Nebula help content coming soon.</p>
			',
		));

		if ( count($help_tabs) ) {
			foreach ( $help_tabs as $help_tab ) {
				$screen->add_help_tab($help_tab);
			}
		}
	}
}//nebula_help_tabs


//Admin Footer Enhancements
//Left Side
add_filter('admin_footer_text', 'change_admin_footer_left');
function change_admin_footer_left() {
    return '<a href="http://www.pinckneyhugo.com" style="color: #0098d7; font-size: 14px; padding-left: 23px;"><img src="' . get_template_directory_uri() . '/images/phg/phg-symbol.png" onerror="this.onerror=null; this.src=""' . get_template_directory_uri() . '/images/phg/phg-symbol.png" alt="Pinckney Hugo Group" style="position: absolute; margin-left: -20px; margin-top: 4px; max-width: 18px;"/> Pinckney Hugo Group</a> &bull; <a href="https://www.google.com/maps/dir/Current+Location/760+West+Genesee+Street+Syracuse+NY+13204" target="_blank">760 West Genesee Street, Syracuse, NY 13204</a> &bull; ' . nebula_tel_link('13154786700');
}
//Right Side
add_filter('update_footer', 'change_admin_footer_right', 11);
function change_admin_footer_right() {
    return 'WP Version: <strong>' . get_bloginfo('version') . '</strong> | Server IP: <strong>' . $_SERVER['SERVER_ADDR'] . '</strong>';
}