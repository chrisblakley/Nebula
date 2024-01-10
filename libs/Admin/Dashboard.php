<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Dashboard') ){
	trait Dashboard {
		public function hooks(){
			//Exclude AJAX requests
			if ( !$this->is_background_request() ){
				//Remove unnecessary Dashboard metaboxes
				if ( $this->get_option('unnecessary_metaboxes') ){
					add_action('wp_dashboard_setup', array($this, 'remove_dashboard_metaboxes'));
				}

				if ( current_user_can('publish_posts') ){
					add_action('wp_dashboard_setup', array($this, 'ataglance_metabox'));
					add_action('wp_dashboard_setup', array($this, 'current_user_metabox'));
				}

				if ( current_user_can('edit_others_posts') ){
					add_action('wp_dashboard_setup', array($this, 'administrative_metabox'));

					if ( $this->get_option('todo_manager_metabox') && $this->is_dev() ){
						add_action('wp_dashboard_setup', array($this, 'todo_metabox'));
					}

					if ( $this->get_option('dev_info_metabox') && $this->is_dev() ){
						add_action('wp_dashboard_setup', array($this, 'dev_info_metabox'));
					}

					if ( $this->get_option('performance_metabox') || $this->is_dev() ){ //Devs always see the performance metabox
						add_action('wp_dashboard_setup', array($this, 'performance_metabox'));
					}

					if ( $this->get_option('design_reference_metabox') ){
						add_action('wp_dashboard_setup', array($this, 'design_metabox'));
					}

					if ( nebula()->get_option('github_url') && nebula()->get_option('github_pat') ){ //Requires a GitHub URL and Personal Access Token
						add_action('wp_dashboard_setup', array($this, 'github_metabox'));
					}

					if ( $this->get_option('hubspot_portal') && $this->get_option('hubspot_api') ){ //Editor or above (and Hubspot API/Portal)
						add_action('wp_dashboard_setup', array($this, 'hubspot_metabox'));
					}
				}
			}

			if ( current_user_can('edit_others_posts') ){
				add_action('wp_ajax_search_theme_files', array($this, 'search_theme_files'));
			}
		}

		//Remove unnecessary Dashboard metaboxes
		public function remove_dashboard_metaboxes(){
			$override = apply_filters('pre_remove_dashboard_metaboxes', null);
			if ( isset($override) ){return false;}

			//If necessary, dashboard metaboxes can be unset. To best future-proof, use remove_meta_box().
			//remove_meta_box('dashboard_primary', 'dashboard', 'side'); //Wordpress News
			remove_meta_box('dashboard_secondary', 'dashboard', 'side');
			remove_meta_box('dashboard_plugins', 'dashboard', 'normal');
			remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');
			remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
			remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
		}

		//WordPress Information metabox ("At a Glance" replacement)
		public function ataglance_metabox(){
			global $wp_meta_boxes;
			wp_add_dashboard_widget('nebula_ataglance', '<img src="' . get_site_icon_url(32, get_theme_file_uri('/assets/img/meta') . '/favicon-32x32.png') . '" style="float: left; width: 20px; margin-right: 3px;" loading="lazy" />&nbsp;' . get_bloginfo('name'), array($this, 'dashboard_nebula_ataglance'));
		}

		public function dashboard_nebula_ataglance(){
			$this->timer('Nebula At-a-Glance Dashboard Metabox');

			echo '<ul class="nebula-fa-ul">';

			//Data Loaded Time
			echo '<li><i class="fa-solid fa-fw fa-clock" title="This page last loaded"></i> <strong id="last-loaded" title="Just now" style="cursor: help;">' . date('l, F j, Y - g:i:sa') . '</strong></li>'; //The "Just Now" title text gets updated by JavaScript after load

			//Website URL
			echo '<li><i class="fa-solid fa-fw fa-globe"></i> <a href="' . home_url('/') . '" target="_blank" rel="noopener noreferrer">' . home_url('/') . '</a></li>';

			//Address
			if ( $this->get_option('street_address') ){
				echo '<li><i class="fa-solid fa-fw fa-map-marker"></i> <a href="https://www.google.com/maps/place/' . $this->full_address(1) . '" target="_blank" rel="noopener noreferrer">' . $this->full_address() . '</a></li>';
			}

			//Open/Closed
			if ( $this->has_business_hours() ){
				$open_closed = ( $this->business_open() )? '<strong style="color: green;">Open</strong>' : '<strong>Closed</strong>';
				echo '<li><i class="fa-regular fa-fw fa-clock"></i> Currently ' . $open_closed . '</li>';
			}

			//WordPress Version
			global $wp_version;
			echo '<li><i class="fa-brands fa-fw fa-wordpress"></i> <a href="https://codex.wordpress.org/WordPress_Versions" target="_blank" rel="noopener noreferrer">WordPress</a> <strong>' . $wp_version . '</strong></li>';

			//Nebula Version
			//Note: Ignore the time here (in the title attribute on hover) as it is only meant to reference the calendar date based on the "major", "minor", and "patch" number (and not interpret the "build" number)
			echo '<li><i class="fa-regular fa-fw fa-star"></i> <a href="https://nebula.gearside.com?utm_campaign=nebula&utm_medium=nebula&utm_source=' . urlencode(get_bloginfo('name')) . '&utm_content=at+a+glance+version" target="_blank" rel="noopener noreferrer">Nebula</a> <strong><a href="https://github.com/chrisblakley/Nebula/compare/main@{' . date('Y-m-d', $this->version('utc')) . '}...main" target="_blank">' . $this->version('realtime') . '</a></strong> <small title="' . $this->version('date') . '" style="cursor: help;">(Committed ' . human_time_diff($this->version('utc')) . ' ago)</small></li>';

			//Child Theme
			if ( is_child_theme() ){
				echo '<li><i class="fa-solid fa-fw fa-child"></i><a href="themes.php">Child theme</a> active <small>(' . get_option('stylesheet') . ' v' . $this->child_version() . ')</small></li>';
			}

			//Multisite (and Super Admin detection)
			if ( is_multisite() ){
				$network_admin_link = '';
				if ( is_super_admin() ){
					$network_admin_link = ' <small><a href="' . network_admin_url() . '">(Network Admin)</a></small></li>';
				}
				echo '<li><i class="fa-solid fa-fw fa-cubes"></i> Multisite' . $network_admin_link;
			}

			//GA Measurement ID
			if ( $this->get_option('ga_measurement_id') ){
				echo '<li><i class="fa-solid fa-fw fa-chart-area"></i> GA Measurment ID: <a href="' . $this->google_analytics_url() . '" target="_blank" rel="noreferrer noopener">' . $this->get_option('ga_measurement_id') . '</a></li>';
			}

			//GA Property ID
			if ( $this->get_option('ga_property_id') ){
				echo '<li><i class="fa-solid fa-fw fa-rectangle-list"></i> GA Property ID: <a href="' . $this->google_analytics_url() . '" target="_blank" rel="noreferrer noopener">' . $this->get_option('ga_property_id') . '</a></li>';
			}

			//GTM Container ID
			if ( $this->get_option('gtm_id') ){
				echo '<li><i class="fa-solid fa-fw fa-tags"></i> GTM Container ID: <a href="https://tagmanager.google.com/" target="_blank" rel="noreferrer noopener">' . $this->get_option('gtm_id') . '</a></li>';
			}

			//Post Types
			global $wp_post_types;
			foreach ( get_post_types() as $post_type ){
				//Only show post types that show_ui (unless forced with one of the arrays below)
				$force_show = array('wpcf7_contact_form'); //These will show even if their show_ui is false.
				$force_hide = array('attachment', 'acf', 'deprecated_log'); //These will be skipped even if their show_ui is true.
				if ( (!$wp_post_types[$post_type]->show_ui && !in_array($post_type, $force_show)) || in_array($post_type, $force_hide)){
					continue;
				}

				$cache_length = ( is_plugin_active('transients-manager/transients-manager.php') )? WEEK_IN_SECONDS : DAY_IN_SECONDS; //If Transient Monitor (plugin) is active, transients with expirations are deleted when posts are published/updated, so this could be infinitely long (as long as an expiration exists).
				$count_posts = nebula()->transient('nebula_count_posts_' . $post_type, function($data){
					$count_posts = wp_count_posts($data['post_type']);
					return $count_posts;
				}, array('post_type' => $post_type), $cache_length);

				$count = $count_posts->publish;
				switch ( $post_type ){
					case ( 'post' ):
						$post_icon_img = '<i class="fa-solid fa-fw fa-thumbtack"></i>';
						break;
					case ( 'page' ):
						$post_icon_img = '<i class="fa-solid fa-fw fa-file-alt"></i>';
						break;
					case ( 'wp_block' ):
						$post_icon_img = '<i class="fa-regular fa-fw fa-clone"></i>';
						break;
					case ( 'wpcf7_contact_form' ):
						$post_icon_img = '<i class="fa-solid fa-fw fa-envelope"></i>';
						break;
					case ( 'nebula_cf7_submits' ):
						$count = $count_posts->private; //These are all stored privately
					default:
						$post_icon = $wp_post_types[$post_type]->menu_icon;
						$post_icon_img = '<i class="fa-solid fa-fw fa-thumbtack"></i>';
						if ( !empty($post_icon) ){
							$post_icon_img = '<img src="' . $post_icon . '" style="width: 16px; height: 16px;" loading="lazy" />';
							if ( strpos('dashicons-', $post_icon) >= 0 ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
								$post_icon_img = '<i class="dashicons-before ' . $post_icon . '"></i>';
							}
						}
						break;
				}

				$labels_plural = ( $count === 1 )? $wp_post_types[$post_type]->labels->singular_name : $wp_post_types[$post_type]->labels->name;

				echo '<li>' . $post_icon_img . ' <a href="edit.php?post_type=' . $post_type . '"><strong>' . $count . '</strong> ' . $labels_plural . '</a></li>';
			}

			//Earliest post
			$earliest_post = nebula()->transient('nebula_earliest_post', function(){
				return new WP_Query(array('post_type' => 'any', 'post_status' => 'publish', 'showposts' => 1, 'orderby' => 'publish_date', 'order' => 'ASC'));
			}, YEAR_IN_SECONDS); //This transient is deleted when posts are added/updated, so this could be infinitely long (as long as an expiration exists).

			while ( $earliest_post->have_posts() ){ $earliest_post->the_post();
				echo '<li><i class="fa-regular fa-fw fa-calendar"></i> Earliest: <span title="' . get_the_date() . ' @ ' . get_the_time() . '" style="cursor: help;"><strong>' . human_time_diff(strtotime(get_the_date() . ' ' . get_the_time())) . ' ago</strong></span><small style="display: block;"><i class="fa-regular fa-fw fa-file-alt"></i> <a href="' . get_permalink() . '">' . $this->excerpt(array('text' => esc_html(get_the_title()), 'words' => 5, 'more' => false, 'ellipsis' => true)) . '</a> (' . get_the_author() . ')</small></li>';
			}
			wp_reset_postdata();

			//Last updated
			$latest_post = nebula()->transient('nebula_latest_post', function(){
				return new WP_Query(array('post_type' => 'any', 'showposts' => 1, 'orderby' => 'modified', 'order' => 'DESC'));
			}, WEEK_IN_SECONDS); //This transient is deleted when posts are added/updated, so this could be infinitely long.
			while ( $latest_post->have_posts() ){ $latest_post->the_post();
				echo '<li><i class="fa-regular fa-fw fa-calendar"></i> Updated: <span title="' . get_the_modified_date() . ' @ ' . get_the_modified_time() . '" style="cursor: help;"><strong>' . human_time_diff(strtotime(get_the_modified_date())) . ' ago</strong></span>
					<small style="display: block;"><i class="fa-regular fa-fw fa-file-alt"></i> <a href="' . get_permalink() . '">' . $this->excerpt(array('text' => esc_html(get_the_title()), 'words' => 5, 'more' => false, 'ellipsis' => true)) . '</a> (' . get_the_author() . ')</small>
				</li>';
			}
			wp_reset_postdata();

			//Revisions
			$revision_count = ( WP_POST_REVISIONS == -1 )? 'all' : WP_POST_REVISIONS;
			$revision_style = ( $revision_count === 0 )? 'style="color: red;"' : '';
			$revisions_plural = ( $revision_count === 1 )? 'revision' : 'revisions';
			echo '<li><i class="fa-solid fa-fw fa-history"></i> Storing <strong ' . $revision_style . '>' . $revision_count . '</strong> ' . $revisions_plural . '.</li>';

			//Plugins
			$all_plugins = nebula()->transient('nebula_count_plugins', function(){
				return get_plugins();
			}, WEEK_IN_SECONDS);
			$all_plugins_plural = ( count($all_plugins) === 1 )? 'Plugin' : 'Plugins';
			$active_plugins = get_option('active_plugins', array());
			echo '<li><i class="fa-solid fa-fw fa-plug"></i> <a href="plugins.php"><strong>' . count($all_plugins) . '</strong> ' . $all_plugins_plural . '</a> installed <small>(' . count($active_plugins) . ' active)</small></li>';

			//Must-Use Plugins
			if ( is_dir(WPMU_PLUGIN_DIR) && is_array(scandir(WPMU_PLUGIN_DIR)) ){ //Make sure this directory exists
				$mu_plugin_count = count(array_diff(scandir(WPMU_PLUGIN_DIR), array('..', '.'))); //Count the files in the mu-plugins directory (and remove the "." and ".." directories from scandir())
				if ( !empty($mu_plugin_count) && $mu_plugin_count >= 1 ){
					$mu_plugins_plural = ( $mu_plugin_count === 1 )? 'Must-Use Plugin' : 'Must-Use Plugins';
					echo '<li><i class="fa-solid fa-fw fa-plug"></i> <a href="plugins.php"><strong>' . $mu_plugin_count . '</strong> ' . $mu_plugins_plural . '</a></li>';
				}
			}

			//Users
			$user_count = nebula()->transient('nebula_count_users', function(){
				return count_users();
			}, WEEK_IN_SECONDS);
			$users_icon = 'users';
			$users_plural = 'Users';
			if ( $user_count['total_users'] === 1 ){
				$users_plural = 'User';
				$users_icon = 'user';
			}
			echo '<li><i class="fa-solid fa-fw fa-' . $users_icon . '"></i> <a href="users.php">' . $user_count['total_users'] . ' ' . $users_plural . '</a> <small>(' . $this->online_users('count') . ' currently active)</small></li>';

			//Comments
			if ( $this->get_option('comments') && $this->get_option('disqus_shortname') == '' ){
				$comments_count = wp_count_comments();
				$comments_plural = ( $comments_count->approved === 1 )? 'Comment' : 'Comments';
				echo '<li><i class="fa-solid fa-fw fa-comments"></i> <strong>' . $comments_count->approved . '</strong> ' . $comments_plural . '</li>';
			} else {
				if ( !$this->get_option('comments') ){
					echo '<li><i class="fa-regular fa-fw fa-comment"></i> Comments disabled <small>(via <a href="themes.php?page=nebula_options&tab=functions&option=comments">Nebula Options</a>)</small></li>';
				} else {
					echo '<li><i class="fa-regular fa-fw fa-comments"></i> Using <a href="https://' . $this->get_option('disqus_shortname') . '.disqus.com/admin/moderate/" target="_blank" rel="noopener noreferrer">Disqus comment system</a>.</li>';
				}
			}

			//Global Admin Bar
			if ( !$this->get_option('admin_bar') ){
				echo '<li><i class="fa-regular fa-fw fa-bars"></i> Admin Bar disabled <small>(for all users via <a href="themes.php?page=nebula_options&tab=functions&option=admin_bar">Nebula Options</a>)</small></li>';
			}

			echo '</ul>';

			do_action('nebula_ataglance');
			$this->timer('Nebula At-a-Glance Dashboard Metabox', 'end');
		}

		//Current User metabox
		public function current_user_metabox(){
			$headshotURL = esc_attr(get_the_author_meta('headshot_url', get_current_user_id()));
			$headshot_thumbnail = str_replace('.jpg', '-150x150.jpg', $headshotURL);

			if ( $headshot_thumbnail ){
				$headshot_html = '<img src="' . esc_attr($headshot_thumbnail) . '" style="float: left; max-width: 20px; border-radius: 100px;" loading="lazy" />&nbsp;';
			} else {
				$headshot_html = '<i class="fa-solid fa-fw fa-user"></i>&nbsp;';
			}

			wp_add_dashboard_widget('nebula_current_user', $headshot_html . $this->get_user_info('display_name'), array($this, 'dashboard_current_user'));
		}

		public function dashboard_current_user(){
			$this->timer('Nebula Current User Dashboard Metabox');
			$user_info = get_userdata(get_current_user_id());

			echo '<ul class="nebula-fa-ul">';
			//Company
			$company = '';
			if ( get_the_author_meta('jobcompany', $user_info->ID) ){
				$company = get_the_author_meta('jobcompany', $user_info->ID);
				if ( get_the_author_meta('jobcompanywebsite', $user_info->ID) ){
					$company = '<a class="this-user-company-name" href="' . get_the_author_meta('jobcompanywebsite', $user_info->ID) . '?utm_campaign=nebula&utm_medium=nebula&utm_source=' . urlencode(get_bloginfo('name')) . '&utm_content=user+metabox+job+title" target="_blank" rel="noopener noreferrer">' . $company . '</a>';
				}
			}

			//Job Title
			$job_title = '';
			if ( get_the_author_meta('jobtitle', $user_info->ID) ){
				$job_title = get_the_author_meta('jobtitle', $user_info->ID);
				if ( !empty($company) ){
					$job_title = $job_title . ' at ';
				}
			}
			if ( !empty($job_title) || !empty($company) ){
				echo '<li><i class="fa-regular fa-fw fa-building"></i> ' . $job_title . $company . '</li>';
			}

			//Location
			if ( get_the_author_meta('usercity', $user_info->ID) && get_the_author_meta('userstate', $user_info->ID) ){
				echo '<li><i class="fa-solid fa-fw fa-map-marker"></i> <strong>' . get_the_author_meta('usercity', $user_info->ID) . ', ' . get_the_author_meta('userstate', $user_info->ID) . '</strong></li>';
			}

			//Email
			echo '<li><i class="fa-regular fa-fw fa-envelope"></i> Email: <strong>' . $user_info->user_email . '</strong></li>';

			if ( get_the_author_meta('phonenumber', $user_info->ID) ){
				echo '<li><i class="fa-solid fa-fw fa-phone"></i> Phone: <strong>' . get_the_author_meta('phonenumber', $user_info->ID) . '</strong></li>';
			}

			echo '<li><i class="fa-regular fa-fw fa-user"></i> Username: <strong>' . $user_info->user_login . '</strong></li>';
			echo '<li><i class="fa-solid fa-fw fa-info-circle"></i> ID: <strong>' . $user_info->ID . '</strong></li>';

			//Role
			$fa_role = 'fa-user';
			$user_role = $this->user_role();
			if ( !empty($user_role) ){
				switch ( strtolower($user_role) ){
					case 'super admin': $fa_role = 'fa-key'; break;
					case 'administrator': $fa_role = 'fa-key'; break;
					case 'editor': $fa_role = 'fa-scissors'; break;
					case 'author': $fa_role = 'fa-pencil-alt-square'; break;
					case 'contributor': $fa_role = 'fa-send'; break;
					case 'subscriber': $fa_role = 'fa-ticket'; break;
					default: $fa_role = 'fa-user'; break;
				}
			}
			echo '<li><i class="fa-solid fa-fw ' . $fa_role . '"></i> Role: <strong class="admin-user-info admin-user-role">' . $user_role . '</strong></li>';

			//Posts by this user
			$your_posts = nebula()->transient('nebula_count_posts_user_' . $user_info->ID, function($data){
				return count_user_posts($data['id']);
			}, array('id' => $user_info->ID), DAY_IN_SECONDS);
			echo '<li><i class="fa-solid fa-fw fa-thumbtack"></i> Your posts: <strong>' . $your_posts . '</strong></li>';

			//Device
			if ( $this->is_desktop() ){
				echo '<li><i class="fa-solid fa-fw fa-desktop"></i> Device: <strong>Desktop/Laptop</strong></li>';
			} else {
				echo '<li><i class="fa-solid fa-fw fa-mobile-alt"></i> Device: <strong>' . ucwords($this->get_device('full')) . '</strong> <small>(Mobile)</small></li>';
			}

			//Browser
			switch ( str_replace(array('mobile', ' '), '', strtolower($this->get_browser('name'))) ){
				case 'edge':
					$browser_icon = 'fa-brands fa-edge';
					break;
				case 'safari':
					$browser_icon = 'fa-brands fa-safari';
					break;
				case 'firefox':
					$browser_icon = 'fa-brands fa-firefox';
					break;
				case 'chrome':
				case 'chrome mobile':
					$browser_icon = 'fa-brands fa-chrome';
					break;
				case 'opera':
					$browser_icon = 'fa-brands fa-opera';
					break;
				default:
					$browser_icon = 'fa-solid fa-globe';
					break;
			}
			echo '<li><i class="fa-fw ' . $browser_icon . '"></i> Browser: <strong>' . ucwords($this->get_browser('full')) . '</strong></li>';

			//IP Address
			echo '<li><i class="fa-solid fa-fw fa-globe"></i> Your IP Address: <a href="http://whatismyipaddress.com/ip/' . $this->get_ip_address() . '" target="_blank" rel="noopener noreferrer"><strong class="admin-user-info admin-user-ip" title="Anonymized IP Address">' . $this->get_ip_address() . '</strong></a> <small>(Anonymized)</small></li>';

			//Multiple locations
			if ( $this->user_single_concurrent($user_info->ID) > 1 ){
				echo '<li><i class="fa-solid fa-fw fa-users"></i> Active in <strong>' . $this->user_single_concurrent($user_info->ID) . ' locations</strong>.</li>';
			}

			//User Admin Bar
			if ( !get_user_option('show_admin_bar_front', $user_info->ID) ){
				echo '<li><i class="fa-solid fa-fw fa-bars"></i> Admin Bar disabled <small>(for just you via <a href="profile.php">User Profile</a>)</small></li>';
			}

			do_action('nebula_user_metabox');
			echo '</ul>';

			echo '<p><small><em><a href="profile.php"><i class="fa-solid fa-fw fa-pencil-alt"></i> Manage your user information</a></em></small></p>';
			$this->timer('Nebula Current User Dashboard Metabox', 'end');
		}

		//Administrative metabox
		public function administrative_metabox(){
			wp_add_dashboard_widget('nebula_administrative', 'Administrative', array($this, 'dashboard_administrative'));
		}

		//Administrative metabox content
		public function dashboard_administrative(){
			$this->timer('Nebula Administrative Dashboard Metabox');
			$third_party_resources = $this->third_party_resources();

			echo '<div class="nebula-metabox-row"><div class="nebula-metabox-col">';
			echo '<ul class="nebula-fa-ul" style="margin-top: 0;">';
			foreach ( $third_party_resources['administrative'] as $resource ){
				echo '<li>' . $resource['icon'] . ' <a href="' . $resource['url'] . '" target="_blank" rel="noopener noreferrer">' . $resource['name'] . '</a></li>';
			}

			do_action('nebula_administrative_metabox');

			echo '</ul>';
			echo '<p><small><em>Manage administrative links in <strong><a href="themes.php?page=nebula_options&tab=administration">Nebula Options</a></strong>.</em></small></p>';
			echo '</div>';

			echo '<div style="max-width: 50%;">';
			echo '<h3>Social</h3>';
			echo '<ul class="nebula-fa-ul">';
			foreach ( $third_party_resources['social'] as $resource ){
				echo '<li>' . $resource['icon'] . ' <a href="' . $resource['url'] . '" target="_blank" rel="noopener noreferrer">' . $resource['name'] . '</a></li>';
			}

			do_action('nebula_social_metabox');

			echo '</ul>';
			echo '<p><small><em>Manage social links in <strong><a href="themes.php?page=nebula_options&filter=social">Nebula Options</a></strong>.</em></small></p>';
			echo '</div></div>';

			$this->timer('Nebula Administrative Dashboard Metabox', 'end');
		}

		//Extension skip list for both To-Do Manager and Developer Metabox
		public function skip_extensions(){
			return array('.jpg', '.jpeg', '.png', '.gif', '.ico', '.tiff', '.psd', '.ai', '.apng', '.bmp', '.otf', '.ttf', '.ogv', '.flv', '.fla', '.mpg', '.mpeg', '.avi', '.mov', '.woff', '.eot', '.mp3', '.mp4', '.wmv', '.wma', '.aiff', '.zip', '.zipx', '.rar', '.exe', '.dmg', '.csv', '.swf', '.pdf', '.pdfx', '.pem', '.ppt', '.pptx', '.pps', '.ppsx', '.bak'); //Would it be faster to list allowed filetypes instead?
		}

		//TODO Metabox
		public function todo_metabox(){
			wp_add_dashboard_widget('todo_manager', '<i class="fa-solid fa-fw fa-check-square"></i>&nbsp;To-Do Manager', array($this, 'todo_metabox_content'));
		}

		//TODO metabox content
		public function todo_metabox_content(){
			$this->timer('Nebula To-Do Dashboard Metabox');
			do_action('nebula_todo_manager');

			$todo_items = nebula()->transient('nebula_todo_items', function(){
				$todo_items = array(
					'parent' => $this->todo_search_files(get_template_directory()),
				);

				if ( is_child_theme() ){
					$todo_items['child'] = $this->todo_search_files(get_stylesheet_directory());
				}

				do_action('qm/info', 'To-Do Scan Performed');

				return apply_filters('nebula_todo_items', $todo_items); //Add locations to the Todo Manager
			}, MINUTE_IN_SECONDS*30);

			$file_count = 0;
			$instance_count = 0;
			?>
				<p class="todoresults_title">
					<strong>Active @todo Comments</strong> <a class="todo_help_icon" href="https://gearside.com/wordpress-dashboard-todo-manager/?utm_campaign=nebula&utm_medium=nebula&utm_source=<?php echo urlencode(get_bloginfo('name')); ?>&utm_content=todo+metabox" target="_blank" rel="noopener noreferrer"><i class="fa-regular fw fa-question-circle"></i> Documentation &raquo;</a>
				</p>

				<div class="todo_results">
			<?php

			//Loop through array of to-do items and echo markup
			foreach ( $todo_items as $location => $todos ){
				if ( $location === 'parent' && !is_child_theme() ){
					$location = '';
				}

				$last_file = false;

				if ( !empty($todos) ){
					foreach ( $todos as $todo ){
						?>
						<?php if ( $last_file !== $todo['filepath'] ): ?>
							<?php if ( !empty($last_file) ): ?>
								</div><!-- /todofilewrap -->
							<?php endif; ?>

							<div class="todofilewrap todo-theme-<?php echo $location; ?>">
								<p class="todofilename"><?php echo str_replace($todo['directory'], '', dirname($todo['filepath'])); ?>/<strong><?php echo basename($todo['filepath']); ?></strong> <small>(<?php echo ucwords($location); ?>)</small></p>
						<?php endif; ?>

							<div class="linewrap todo-category-<?php echo strtolower(str_replace(' ', '_', $todo['category'])); ?> todo-priority-<?php echo $todo['priority']; ?>">
								<p class="todoresult">
									<?php if ( !empty($todo['category']) ): ?>
										<span class="todocategory"><?php echo $todo['category']; ?></span>
									<?php endif; ?>

									<a class="linenumber" href="#">Line <?php echo $todo['line_number']+1; ?></a> <span class="todomessage"><?php echo $todo['description']; ?></span>
								</p>
								<div class="precon">
									<pre class="actualline"><?php echo $todo['line']; ?></pre>
								</div>
							</div>
						<?php

						//Count files and instances
						if ( $todo['priority'] === 'empty' || $todo['priority'] > 0 ){ //Only count todos with a non-hidden priority
							if ( !is_child_theme() || (is_child_theme() && $location === 'child') ){ //Only count child todo comments if child theme is active
								if ( $last_file !== $todo['filepath'] ){
									$file_count++;
								}

								$instance_count++;
							}
						}

						$last_file = $todo['filepath'];
					}

					echo '</div><!-- /todofilewrap -->';
				}
			}

			if ( empty($instance_count) ){
				echo '<p style="margin-top: 50px; text-align: center; font-size: 24px; line-height: 28px; opacity: 0.1;"><i class="fa-regular fa-smile" style="font-size: 32px;"></i><br />Nothing!</p>';
			}
			?>
				</div><!--/todo_results-->
				<p>
					<i class="fa-regular fa-fw fa-file-code"></i> Found <strong><?php echo $file_count; ?> files</strong> with <strong><?php echo $instance_count; ?> @todo comments</strong>.

					<?php if ( $this->get_option('github_url') ): ?>
						<br /><i class="fa-brands fa-fw fa-github"></i> <a href="<?php echo $this->get_option('github_url'); ?>/issues?q=is%3Aissue+is%3Aopen+sort%3Aupdated-desc" target="_blank">Check the GitHub repository</a> for additional issues.
					<?php endif; ?>
				</p>
			<?php
			$this->timer('Nebula To-Do Dashboard Metabox', 'end');
		}

		public function todo_search_files($directory=null){
			//@todo "Nebula" 0: Use null coalescing operator here
			if ( empty($directory) ){
				$directory = get_template_directory();
			}

			ini_set('memory_limit', '512M'); //@todo Nebula 0: Remove these when possible...

			$these_todos = array();
			foreach ( $this->glob_r($directory . '/*') as $todo_file ){ //Loop through each file
				if ( is_file($todo_file) ){
					$todo_skipFilenames = array('README.md', 'debug_log', 'error_log', '/vendor', 'resources/'); //Skip certain filepaths and file names (more file extensions are skipped separately)

					if ( !$this->contains($todo_file, $this->skip_extensions()) && !$this->contains($todo_file, $todo_skipFilenames) ){ //Skip certain extensions and filenames
						foreach ( file($todo_file) as $todo_lineNumber => $todo_line ){ //Loop through each line in the file. Can the memory usage of this be reduced?
							preg_match("/(@todo)\s?(?'category'[\"\'\`].+?[\"\'\`])?\s?(?'priority'\d)?:\s(?'description'.+)/i", $todo_line, $todo_details); //Separate the todo comment into useable groups

							if ( !empty($todo_details) ){
								$these_todos[] = array(
									'directory' => $directory,
									'filepath' => $todo_file,
									'line_number' => $todo_lineNumber,
									'line' => trim(htmlentities($todo_line)),
									'priority' => ( $todo_details['priority'] === '' )? 'empty' : intval($todo_details['priority']),
									'category' => ( !empty($todo_details['category']) )? str_replace(array('"', "'", '`'), '', $todo_details['category']) : '',
									'description' => strip_tags(str_replace(array('-->', '?>', '*/'), '', $todo_details['description'])),
								);
							}
						}
					}
				}
			}

			return $these_todos;
		}

		//Developer Info Metabox
		public function dev_info_metabox(){
			wp_add_dashboard_widget('nebula_developer_info', 'Developer Information', array($this, 'dashboard_developer_info'));
		}

		//Developer Info Metabox content
		public function dashboard_developer_info(){
			$this->timer('Nebula Developer Dashboard Metabox');
			do_action('nebula_developer_info');
			echo '<ul class="nebula-fa-ul serverdetections">';

			echo '<li class="cookie_notification text_ad ads advertisement ads_content ads_div ads_google adsbygoogle adscontainer adsense-box nebula-adb-tester"></li>'; //Alert developers if their ad-blocker is still active

			//Environment Type
			if ( function_exists('wp_get_environment_type') ){ //New as of WP 5.5 (August 2020). Remove this conditional eventually.
				$environment_type = ucwords(wp_get_environment_type());
				$environment_type_icon = 'fa-industry'; //Assume "Production" by default
				if ( $environment_type === 'Staging' ){
					$environment_type_icon = 'fa-pencil-ruler';
				} elseif ( $environment_type === 'Development' ){
					$environment_type_icon = 'fa-hard-hat';
				} elseif ( $environment_type === 'Test' ){
					$environment_type_icon = 'fa-flask';
				}

				echo '<li><i class="fa-solid fa-fw ' . $environment_type_icon . '"></i> Environment Type: <strong>' . $environment_type . '</strong></li>';
			}

			//Domain
			//@todo "Nebula" 0: Use null coalescing operator here if possible
			$domain = $this->url_components('domain');
			if ( empty($domain) ){
				$domain = '<small>(None)</small>';
			}
			echo '<li><i class="fa-solid fa-fw fa-info-circle"></i> <a href="http://whois.domaintools.com/' . $this->super->server['SERVER_NAME'] . '" target="_blank" rel="noopener noreferrer" title="WHOIS Lookup">Domain</a>: <strong>' . $domain . '</strong></li>';

			//Host
			function top_domain_name($url){
				$alldomains = explode('.', $url);

				if ( count($alldomains) > 1 ){
					return $alldomains[count($alldomains)-2] . "." . $alldomains[count($alldomains)-1];
				}

				return $url;
			}

			if ( function_exists('gethostname') ){
				set_error_handler(function(){ /* ignore errors */ });
				$dnsrecord = ( dns_get_record(top_domain_name(gethostname()), DNS_NS) )? dns_get_record(top_domain_name(gethostname()), DNS_NS) : '';
				restore_error_handler();

				echo '<li><i class="fa-regular fa-fw fa-hdd"></i> Host: <strong>' . top_domain_name(gethostname()) . '</strong>';
				if ( !empty($dnsrecord[0]['target']) ){
					echo ' <small>(' . top_domain_name($dnsrecord[0]['target']) . ')</small>';
				}
				echo '</li>';
			}

			//Server IP address (and connection security)
			$secureServer = '';
			if ( (!empty($this->super->server['HTTPS']) && $this->super->server['HTTPS'] !== 'off') || $this->super->server['SERVER_PORT'] === 443 ){
				$secureServer = '<small class="secured-connection"><i class="fa-solid fa-fw fa-lock"></i>Secured Connection</small>';
			}
			$public_local_ip = ( preg_match('/^(127\.|192\.168|172\.|10\.)/', $this->super->server['SERVER_ADDR']) )? 'Local' : 'Public'; //Check if the server IP is likely local (private) or public (this is not perfectly exact)
			echo '<li><i class="fa-solid fa-fw fa-upload"></i> ' . $public_local_ip . ' Server IP: <strong><a href="http://whatismyipaddress.com/ip/' . $this->super->server['SERVER_ADDR'] . '" target="_blank" rel="noopener noreferrer">' . apply_filters('nebula_dashboard_server_ip', $this->super->server['SERVER_ADDR']) . '</a></strong> ' . $secureServer . '</li>';

			//Server Time Zone
			if ( date_default_timezone_get() === get_option('timezone_string') ){
				echo '<li><i class="fa-solid fa-fw fa-globe-americas"></i> Timezone: <strong>' . date_default_timezone_get() . '</strong></li>';
			} else {
				echo '<li><i class="fa-solid fa-fw fa-globe-americas"></i> Server Timezone: <strong>' . date_default_timezone_get() . '</strong></li>';
				echo '<li><i class="fa-solid fa-fw fa-globe-americas"></i> WordPress Timezone: <strong>' . get_option('timezone_string') . '</strong></li>';
			}

			//Server operating system
			if ( strpos(strtolower(PHP_OS), 'linux') !== false ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
				$php_os_icon = 'fa-brands fa-linux';
			} elseif ( strpos(strtolower(PHP_OS), 'windows') !== false ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
				$php_os_icon = 'fa-brands fa-windows';
			} else {
				$php_os_icon = 'fa-solid fa-upload';
			}
			echo '<li><i class="fa-fw ' . $php_os_icon . '"></i> Server OS: <strong>' . PHP_OS . '</strong></li>';

			//Server software
			$server_software = $this->super->server['SERVER_SOFTWARE'];
			if ( strlen($server_software) > 10 ){
				$server_software = strtok($this->super->server['SERVER_SOFTWARE'], ' '); //Shorten to until the first space
			}
			echo '<li><i class="fa-solid fa-fw fa-server"></i> Server Software: <strong title="' . $this->super->server['SERVER_SOFTWARE'] . '">' . $server_software . '</strong></li>';

			echo '<li><i class="fa-solid fa-fw fa-ethernet"></i> Server Protocol: <strong>' . $this->super->server['SERVER_PROTOCOL'] . '</strong></li>';

			//MySQL version
			global $wpdb;
			$mysql_version = mysqli_get_client_version();
			echo '<li><i class="fa-solid fa-fw fa-database"></i> MySQL Version: <strong title="Raw: ' . $mysql_version . '">' . floor($mysql_version/10000) . '.' . floor(($mysql_version%10000)/100) . '.' . ($mysql_version%10000)%100 . '</strong> <small>(' . get_class($wpdb->dbh) . ')</small></li>'; //PHP 7.4 use numeric separators here

			//PHP version
			$php_version_color = 'inherit';
			$php_version_info = '';
			$php_version_cursor = 'normal';
			$php_version_lifecycle = $this->php_version_support();
			if ( !empty($php_version_lifecycle) ){
				if ( $php_version_lifecycle['lifecycle'] === 'security' ){
					$php_version_color = '#ca8038'; //Warning
					$php_version_info = 'This version is nearing end of life. Security updates end on ' . date('F j, Y', $php_version_lifecycle['end']) . '.';
					$php_version_cursor = 'help';
				} elseif ( $php_version_lifecycle['lifecycle'] === 'end' ){
					$php_version_color = '#ca3838'; //Danger
					$php_version_info = 'This version no longer receives security updates! End of life occurred on ' . date('F j, Y', $php_version_lifecycle['end']) . '.';
					$php_version_cursor = 'help';
				}
			}
			echo '<li><i class="fa-solid fa-fw fa-wrench"></i> PHP Version: <strong style="color: ' . $php_version_color . '; cursor: ' . $php_version_cursor . ';" title="' . $php_version_info . '">' . PHP_VERSION . '</strong> <small>(SAPI: <strong>' . php_sapi_name() . '</strong>)</small></li>';

			//PHP memory limit
			echo '<li><i class="fa-solid fa-fw fa-cogs"></i> PHP Memory Limit: <strong>' . ini_get('memory_limit') . '</strong></li>';

			//Theme directory size(s)
			if ( is_child_theme() ){
				$nebula_parent_size = nebula()->transient('nebula_directory_size_parent_theme', function(){
					return $this->foldersize(get_template_directory());
				}, DAY_IN_SECONDS);

				$nebula_child_size = nebula()->transient('nebula_directory_size_child_theme', function(){
					return $this->foldersize(get_stylesheet_directory());
				}, DAY_IN_SECONDS);

				echo '<li><i class="fa-solid fa-code"></i> Parent theme directory size: <strong>' . $this->format_bytes($nebula_parent_size, 1) . '</strong> </li>';
				echo '<li><i class="fa-solid fa-code"></i> Child theme directory size: <strong>' . $this->format_bytes($nebula_child_size, 1) . '</strong> </li>';
			} else {
				$nebula_size = nebula()->transient('nebula_directory_size_theme', function(){
					return $this->foldersize(get_stylesheet_directory());
				}, DAY_IN_SECONDS);
				echo '<li><i class="fa-solid fa-code"></i> Theme directory size: <strong>' . $this->format_bytes($nebula_size, 1) . '</strong> </li>';
			}

			do_action('nebula_dev_dashboard_directories');

			//Uploads directory size (and max upload size)
			$uploads_size = nebula()->transient('nebula_directory_size_uploads', function(){
				$upload_dir = wp_upload_dir();
				return $this->foldersize($upload_dir['basedir']);
			}, HOUR_IN_SECONDS*36);

			//Here is how it will be after the next major version release:
			// $uploads_size = $this->transient('nebula_directory_size_uploads', function(){
			// 	$upload_dir = wp_upload_dir();
			// 	return $this->foldersize($upload_dir['basedir']);
			// }, HOUR_IN_SECONDS*36);

			if ( function_exists('wp_max_upload_size') ){
				$upload_max = '<small>(Max upload: <strong>' . $this->format_bytes(((int) wp_max_upload_size())) . '</strong>)</small>';
			} elseif ( ini_get('upload_max_filesize') ){
				$upload_max = '<small>(Max upload: <strong>' . ini_get('upload_max_filesize') . '</strong>)</small>';
			} else {
				$upload_max = '';
			}
			echo '<li><i class="fa-solid fa-fw fa-images"></i> Uploads directory size: <strong>' . $this->format_bytes($uploads_size, 1) . '</strong> ' . $upload_max . '</li>';

			//PHP Disk Space
			if ( function_exists('disk_total_space') && function_exists('disk_free_space') ){
				$disk_total_space = disk_total_space(ABSPATH);
				$disk_free_space = disk_free_space(ABSPATH);

				$disk_usage_color = 'inherit';
				if ( $disk_free_space < 5 ){
					$disk_usage_color = '#ca3838'; //Danger
				} elseif ( $disk_free_space < 10 ){
					$disk_usage_color = '#ca8038'; //Warning
				}

				echo '<li><i class="fa-solid fa-fw fa-hdd"></i> Disk Space Available: <strong style="color: ' . $disk_usage_color . ';">' . $this->format_bytes($disk_free_space, 1) . '</strong> <small>(Total space: <strong>' . $this->format_bytes($disk_total_space) . '</strong>)</small></li>';
			}

			//Link to Query Monitor Environment Panel
			//if ( is_plugin_active('query-monitor/query-monitor.php') ){
				//echo '<li><i class="fa-solid fa-fw fa-table"></i> <a href="#qm-environment">Additional Server Information <small>(Query Monitor)</small></a></li>'; //Not currently possible: https://github.com/johnbillion/query-monitor/issues/622
			//}

			//Log Files
			foreach ( $this->get_log_files('all', true) as $types ){ //Always get fresh data here
				foreach ( $types as $log_file ){
					echo '<li><i class="fa-regular fa-fw fa-file-alt"></i> <a href="' . $log_file['shortpath'] . '" target="_blank"><code title="' . $log_file['shortpath'] . '" style="cursor: help;">' . $log_file['name'] . '</code></a> File: <strong>' . $this->format_bytes($log_file['bytes']) . '</strong></li>';
				}
			}

			//Service Worker
			if ( $this->get_option('service_worker') ){
				if ( !is_ssl() ){
					echo '<li><i class="fa-solid fa-fw fa-microchip" style="color: red;"></i> <strong>Not</strong> using service worker. No SSL.</li>';
				} elseif ( !file_exists($this->sw_location(false)) ){
					echo '<li><i class="fa-solid fa-fw fa-microchip" style="color: red;"></i> <strong>Not</strong> using service worker. Service worker file does not exist.</li>';
				} else {
					echo '<li><i class="fa-solid fa-fw fa-microchip"></i> Using service worker</li>';
				}
			}

			//Initial installation date
			function initial_install_date(){
				$nebula_initialized = nebula()->get_option('initialized'); //Keep this as nebula() because it is a nested function, so $this is scoped differently here.
				if ( !empty($nebula_initialized) && $nebula_initialized < getlastmod() ){
					$install_date = '<span title="' . date('F j, Y', $nebula_initialized) . ' @ ' . date('g:ia', $nebula_initialized) . '" style="cursor: help;"><strong>' . human_time_diff($nebula_initialized) . ' ago</strong></span>';
				} else { //Use the last modified time of the admin page itself
					$install_date = '<span title="' . date("F j, Y", getlastmod()) . ' @ ' . date("g:ia", getlastmod()) . '" style="cursor: help;"><strong>' . human_time_diff(getlastmod()) . ' ago</strong></span>';
				}
				return $install_date;
			}
			echo '<li><i class="fa-regular fa-fw fa-calendar"></i> Installed: ' . initial_install_date() . '</li>';

			$latest_file = $this->last_modified();
			echo '<li><i class="fa-regular fa-fw fa-calendar"></i> <span title="' . $latest_file['path'] . '" style="cursor: help;">Modified:</span> <span title="' . date("F j, Y", $latest_file['date']) . ' @ ' . date("g:ia", $latest_file['date']) . '" style="cursor: help;"><strong>' . human_time_diff($latest_file['date']) . ' ago</strong></span></li>';

			//SCSS last processed date
			if ( $this->get_data('scss_last_processed') ){
				$sass_option = ( nebula()->get_option('scss') )? '' : ' <small><em><a href="themes.php?page=nebula_options&tab=functions&option=scss">Sass is currently <strong>disabled</strong> &raquo;</a></em></small>';
				echo '<li><i class="fa-brands fa-fw fa-sass"></i> Sass Processed: <span title="' . date("F j, Y", $this->get_data('scss_last_processed')) . ' @ ' . date("g:i:sa", $this->get_data('scss_last_processed')) . '" style="cursor: help;"><strong>' . human_time_diff($this->get_data('scss_last_processed')) . ' ago</strong></span> ' . $sass_option . '</li>';
			}

			echo '<li><i class="fa-brands fa-fw fa-wordpress"></i> <a href="site-health.php?tab=debug">WP Site Info &raquo;</a></li>'; //Link to WP Health Check Info page

			echo '</ul>';

			//Directory search
			$directory_search_options = array('uploads' => '<option value="uploads">Uploads</option>');
			if ( is_child_theme() ){
				$directory_search_options['child'] = '<option value="child" selected="selected">Child Theme</option>';
				$directory_search_options['parent'] = '<option value="parent">Parent Theme</option>';
			} else {
				$directory_search_options['theme'] = '<option value="theme" selected="selected">Theme</option>';
			}

			//Must-Use Plugins (if any exist)
			if ( is_dir(WPMU_PLUGIN_DIR) && is_array(scandir(WPMU_PLUGIN_DIR)) ){
				$directory_search_options['mu_plugins'] = '<option value="mu_plugins">Must-Use Plugins</option>';
			}

			//Add active plugins to search list
			$directory_search_options['all_plugins'] = '<option value="all_plugins">All Plugins</option>';
			$all_plugins = get_plugins();
			$active_plugins = get_option('active_plugins');
			foreach ( $active_plugins as $active_plugin ){
				if ( isset($all_plugins[$active_plugin]) ){
					$plugin_name = $all_plugins[$active_plugin]['Name'];
					$safe_plugin_name = str_replace(array(' ', '-', '/'), '_', strtolower($plugin_name));
					$directory_search_options[$safe_plugin_name] = '<option value="' . $safe_plugin_name . '">' . $plugin_name . '</option>';
				}
			}

			$all_directory_search_options = apply_filters('nebula_directory_search_options', $directory_search_options); //Allow other functions to hook in to add directories to search

			echo '<form id="theme" class="searchfiles"><i id="searchprogress" class="fa-solid fa-fw fa-magnifying-glass"></i> <input class="findterm" type="text" placeholder="Search files" autocorrect="off" autocapitalize="off" spellcheck="false" /><select class="searchdirectory">';
			foreach ( $all_directory_search_options as $name => $option_html ){
				echo $option_html;
			}
			echo '</select><input class="searchterm button button-primary button-disabled" type="submit" value="Search" title="Still loading... Please wait." /></form>';
			echo '<div class="search_results"></div>';
			$this->timer('Nebula Developer Dashboard Metabox', 'end');
		}

		//Get last modified filename and date from a directory
		public function last_modified($directory=null, $last_date=0, $child=false){
			global $latest_file;
			if ( empty($latest_file) ){
				$latest_file = array(
					'date' => false,
					'file' => false,
					'path' => false,
				);
			}

			//@todo "Nebula" 0: Use null coalescing operator here
			if ( empty($directory) ){
				$directory = get_template_directory();
			}
			$dir = $this->glob_r($directory . '/*');
			$skip_files = array('/cache/', '/includes/data/', 'manifest.json', '.bak'); //Files or directories to skip. Be specific!

			foreach ( $dir as $file ){
				if ( is_file($file) ){
					$mod_date = filemtime($file);
					if ( $mod_date > $last_date && !$this->contains($file, $skip_files) ){ //Does not check against skip_extensions() functions on purpose.
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
				$latest_child_file = $this->last_modified(get_stylesheet_directory(), $latest_file['date'], true);
				if ( $latest_child_file['date'] > $latest_file['date'] ){
					return $latest_child_file;
				}
			}

			return $latest_file;
		}

		//Search theme or plugin files via Developer Information Dashboard Metabox
		public function search_theme_files(){
			if ( !wp_verify_nonce($this->super->post['nonce'], 'nebula_ajax_nonce') ){ wp_die('Permission Denied. Refresh and try again.'); }

			//@todo Nebula 0: Remove these when possible...
			ini_set('max_execution_time', 120);
			ini_set('memory_limit', '512M');

			$searchTerm = htmlentities(stripslashes($this->super->post['searchData']));
			$requestedDirectory = strtolower(sanitize_text_field($this->super->post['directory']));

			if ( strlen($searchTerm) < 2 ){
				wp_die('<p><strong>Error:</strong> Minimum 2 characters needed to search!</p>');
			}

			$uploadDirectory = wp_upload_dir();

			$search_directories = array(
				'theme' => get_template_directory(),
				'parent' => get_template_directory(),
				'child' => get_stylesheet_directory(),
				'plugins' => WP_PLUGIN_DIR,
				'mu_plugins' => WPMU_PLUGIN_DIR,
				'all_plugins' => WP_PLUGIN_DIR,
				'uploads' => $uploadDirectory['basedir'],
			);

			$all_plugins = get_plugins();
			$active_plugins = get_option('active_plugins');
			foreach ( $active_plugins as $active_plugin ){
				if ( isset($all_plugins[$active_plugin]) ){
					$plugin_name = $all_plugins[$active_plugin]['Name'];
					$safe_plugin_name = str_replace(array(' ', '-', '/'), '_', strtolower($plugin_name));
					$plugin_folder = explode('/', $active_plugin);
					$search_directories[$safe_plugin_name] = WP_PLUGIN_DIR . '/' . $plugin_folder[0];
				}
			}

			$all_search_directories = apply_filters('nebula_search_directories', $search_directories); //Allow other functions to hook in to add directories

			$dirpath = $all_search_directories[$requestedDirectory];
			if ( empty($dirpath) ){
				wp_die('<p><strong>Error:</strong> Please specify a directory to search!</p>');
			}

			echo '<p class="resulttext">Search results for <strong>"' . $searchTerm . '"</strong> in the <strong>' . basename($dirpath) . '</strong> directory:</p><br />';

			$file_counter = 0;
			$instance_counter = 0;
			foreach ( $this->glob_r($dirpath . '/*') as $file ){
				$counted = 0;
				if ( is_file($file) ){
					if ( strpos(basename($file), $searchTerm) !== false ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
						echo '<p class="resulttext">' . str_replace($dirpath, '', dirname($file)) . '/<strong>' . basename($file) . '</strong></p>';
						$file_counter++;
						$counted = 1;
					}

					$skipFilenames = array('error_log');
					if ( !$this->contains(basename($file), $this->skip_extensions()) && !$this->contains(basename($file), $skipFilenames) ){
						foreach ( file($file) as $lineNumber => $line ){
							if ( stripos(stripslashes($line), $searchTerm) !== false ){
								$actualLineNumber = $lineNumber+1;
								$actualLine = $this->string_limit_chars(trim(htmlentities($line)), 200);
								$actualLine = ( !empty($actualLine['is_limited']) )? trim($actualLine['text']) . '...' : $actualLine['text'];
								$actualLine = ( !empty($actualLine) )? $actualLine : '[This line is not readable by Nebula.]'; //Lines in some file types are not readable

								echo '<div class="linewrap">
								<p class="resulttext">' . str_replace($dirpath, '', dirname($file)) . '/<strong>' . basename($file) . '</strong> on <a class="linenumber" href="#">line ' . $actualLineNumber . '</a>.</p>
								<div class="precon"><pre class="actualline">' . $actualLine . '</pre></div>
							</div>';
								$instance_counter++;
								if ( $counted === 0 ){
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
			echo ( $file_counter === 1 )? '.</p>': 's.</p>';
			wp_die();
		}

		//Performance Timing
		public function performance_metabox(){
			wp_add_dashboard_widget('performance_metabox', '<i id="performance-status-icon" class="fa-solid fa-fw fa-stopwatch"></i> <span id="performance-title">&nbsp;Performance</span>', array($this, 'performance_timing'));
		}

		public function performance_timing(){
			$this->timer('Nebula Performance Dashboard Metabox');

			//Prep for an iframe timer if needed
			$home_url = ( is_ssl() )? str_replace('http://', 'https://', home_url('/')) : home_url('/'); //Sometimes the home_url() still has http even when is_ssl() true
			echo '<div id="testloadcon" data-src="' . $home_url . '" style="pointer-events: none; opacity: 0; visibility: hidden; display: none;"></div>'; //For iframe timing

			echo '<img id="performance-screenshot" class="hidden" src="#" />';
			echo '<ul id="nebula-performance-metrics" class="nebula-fa-ul">';

			//Sub-status
			echo '<li id="performance-sub-status"><i class="fa-regular fa-fw fa-comment"></i> <span class="label">Status</span>: <strong>Preparing test...</strong></li>';

			//PHP-Measured Server Load Time (TTFB)
			echo '<li id="performance-ttfb"><i class="fa-regular fa-fw fa-clock"></i> <span class="label">PHP Response Time</span>: <strong class="datapoint" title="Calculated via PHP render time">' . timer_stop(0, 3) . ' seconds</strong></li>';
			echo '</ul>';

			echo '<p><small><a href="https://web.dev/lighthouse-performance/" target="_blank" rel="noopener noreferrer">Learn about user-centric performance metrics &raquo;</a></small></p>';
			$this->timer('Nebula Performance Dashboard Metabox', 'end');
		}

		//Add a dashboard metabox for design reference
		public function design_metabox(){
			global $wp_meta_boxes;
			wp_add_dashboard_widget('nebula_design', '<i class="fa-solid fa-fw fa-palette"></i> &nbsp;Design Reference', array($this, 'dashboard_nebula_design'));
		}

		public function dashboard_nebula_design(){
			$this->timer('Nebula Design Dashboard Metabox');
			if ( $this->get_option('design_reference_link') ){
				echo '<p><i class="fa-solid fa-fw fa-file-image"></i> <a href="' . $this->get_option('design_reference_link') . '" target="_blank">Design File(s) &raquo;</a></p>';
			}

			$notable_colors = apply_filters('nebula_notable_colors', array('$primary_color', '$secondary_color')); //Allow other themes and plugins to designate notable colors

			$notable_colors_data = array();
			foreach ( $notable_colors as $notable_color ){
				$sass_color = $this->sass_color($notable_color);
				$customizer_color = get_theme_mod(str_replace('$', 'nebula_', $notable_color));
				$hex_color = ( !empty($sass_color) )? rtrim($sass_color, ';') : $customizer_color;

				if ( !empty($hex_color) ){
					$notable_colors_data[$notable_color] = array(
						'name' => ucwords(str_replace(array('$', '_'), array('', ' '), $notable_color)),
						'sass' => $sass_color,
						'customizer' => $customizer_color,
						'hex' => $hex_color,
						'rgb' => $this->hex2rgb($hex_color),
						'ratios' => array(
							'white' => number_format($this->contrast($hex_color, '#ffffff'), 2, '.', ''),
							'black' => number_format($this->contrast($hex_color, '#000000'), 2, '.', ''),
						),
					);

					//Determine readable color
					if ( $notable_colors_data[$notable_color]['ratios']['white'] > $notable_colors_data[$notable_color]['ratios']['black'] ){
						$notable_colors_data[$notable_color]['readable'] = '#fff';
					} else {
						$notable_colors_data[$notable_color]['readable'] = '#000';
					}
				}
			}

			?>
				<div class="nebula-metabox-row">
					<?php if ( !empty($notable_colors_data) ): ?>
						<?php foreach ( $notable_colors_data as $notable_color_data ): ?>
							<div class="design-reference-col">
								<a class="color-block" href="https://www.colorhexa.com/<?php echo ltrim($notable_color_data['hex'], '#'); ?>" target="_blank" style="background-color: <?php echo $notable_color_data['hex']; ?>;">
									<span class="tee" style="color: <?php echo $notable_color_data['readable']; ?>;">T</span>
									<span class="color-contrast-ratio light"><?php echo $notable_color_data['ratios']['white']; ?> <i class="fa fa-<?php echo ( $notable_color_data['ratios']['white'] >= 4.5 )? 'check' : 'times'; ?>"></i></span>
									<span class="color-contrast-ratio dark"><?php echo $notable_color_data['ratios']['black']; ?> <i class="fa fa-<?php echo ( $notable_color_data['ratios']['black'] >= 4.5 )? 'check' : 'times'; ?>"></i></span>
								</a>
								<div>
									<strong><?php echo $notable_color_data['name']; ?></strong><br />
									Hex <?php echo $notable_color_data['hex']; ?><br />
									RGB <?php echo $notable_color_data['rgb']['r'] . ', ' . $notable_color_data['rgb']['g'] . ', ' . $notable_color_data['rgb']['b']; ?><br />
								</div>
							</div>
						<?php endforeach; ?>
					<?php else: ?>
						<p>Define a brand color (in the Customizer or Sass) to see contrast ratio information here.</p>
					<?php endif; ?>
				</div>
			<?php

			if ( $this->get_option('additional_design_references') ){
				echo '<p><strong>Additional Notes:</strong><br />' . $this->get_option('additional_design_references') . '</p>';
			}

			$this->timer('Nebula Design Dashboard Metabox', 'end');
		}

		//Add a GitHub metabox for recently updated issues/discussions
		public function github_metabox(){
			if ( nebula()->get_option('github_url') && nebula()->get_option('github_pat') ){
				$repo_name = str_replace('https://github.com/', '', nebula()->get_option('github_url'));
				global $wp_meta_boxes;
				wp_add_dashboard_widget('nebula_github', '<i class="fa-brands fa-fw fa-github"></i>&nbsp;' . $repo_name, array($this, 'dashboard_nebula_github'));
			}
		}

		public function dashboard_nebula_github(){
			nebula()->timer('Nebula Companion GitHub Dashboard');
			echo '<p><a href="' . nebula()->get_option('github_url') . '" target="_blank">GitHub Repository &raquo;</a></p>';

			$repo_name = str_replace('https://github.com/', '', nebula()->get_option('github_url'));
			$github_personal_access_token = nebula()->get_option('github_pat');

			//Commits
			$github_commit_json = get_transient('nebula_github_commits');
			if ( empty($github_commit_json) || nebula()->is_debug() ){
				$commits_response = nebula()->remote_get('https://api.github.com/repos/' . $repo_name . '/commits', array(
					'headers' => array(
						'Authorization' => 'token ' . $github_personal_access_token,
					)
				));

				if ( is_wp_error($commits_response) ){
					echo '<p>There was an error retrieving the GitHub commits...</p>';
					return false;
				}

			    $github_commit_json = $commits_response['body'];
				set_transient('nebula_github_commits', $github_commit_json, HOUR_IN_SECONDS*3); //3 hour expiration
			}

			$commits = json_decode($github_commit_json);

			if ( !empty($commits->message) ){
				?>
					<p>
						<strong>This repo is not available.</strong><br />
						If this is a private repo, the <strong>Client ID</strong> and <strong>Client Secret</strong> from your GitHub app must be added in <a href="themes.php?page=nebula_options&tab=functions&option=comments">Nebula Options</a> to retrieve issues.
					</p>
					<p>
						<a href="<?php echo nebula()->get_option('github_url'); ?>/commits/main" target="_blank">Commits &raquo;</a><br />
						<a href="<?php echo nebula()->get_option('github_url'); ?>/issues?q=is%3Aissue+is%3Aopen+sort%3Aupdated-desc" target="_blank">Issues &raquo;</a><br />
					</p>
				<?php
				return false;
			}

			echo '<div class="nebula-metabox-row"><div class="nebula-metabox-col">';
			echo '<strong>Latest Commits</strong><br />';

			//https://developer.github.com/v3/repos/commits/
			for ( $i=0; $i <= 2; $i++ ){ //Get 3 commits
				$commit_date_time = strtotime($commits[$i]->commit->committer->date);
				$commit_date_icon = ( date('Y-m-d', $commit_date_time) === date('Y-m-d') )? 'fa-clock' : 'fa-calendar';
				echo '<p>
					<i class="fa-regular fa-fw ' . $commit_date_icon . '"></i> <a href="' . $commits[$i]->html_url . '" target="_blank" title="' . date('F j, Y @ g:ia', $commit_date_time) . '">' . human_time_diff($commit_date_time) . ' ago</a><br />
					<small style="display: block;">' . nebula()->excerpt(array('text' => $commits[$i]->commit->message, 'words' => 15, 'ellipsis' => true, 'more' => false)) . '</small>
				</p>';
			}

			echo '<p><small><a href="' . nebula()->get_option('github_url') . '/commits/main" target="_blank">View all commits &raquo;</a></small></p>';
			echo '</div>';

			//Issues and Discussions
			echo '<div class="nebula-metabox-col">';
			echo '<strong>Recent Issues, Pull Requests, &amp; Discussions</strong><br />';

			$github_combined_posts = get_transient('nebula_github_posts');
			if ( empty($github_combined_posts) || nebula()->is_debug() ){
				//Get the Issues first https://developer.github.com/v3/issues/
				//Note: The Issues endpoint also returns pull requests (which is fine because we want that)
				$issues_response = nebula()->remote_get('https://api.github.com/repos/' . $repo_name . '/issues?state=open&sort=updated&direction=desc&per_page=3', array(
					'headers' => array(
						'Authorization' => 'token ' . $github_personal_access_token,
					)
				));

				if ( is_wp_error($issues_response) ){
					echo '<p>There was an error retrieving the GitHub issues...</p>';
					return false;
				}

			    $github_issues_json = json_decode($issues_response['body']);

				//Get the Discussions next
				//GraphQL API is available, but webhooks not ready yet per (Feb 2021): https://github.com/github/feedback/discussions/43
				// $discussions_response = nebula()->remote_get('https://api.github.com/repos/' . $repo_name . '/discussions?sort=updated&direction=desc&per_page=3', array(
				// 	'headers' => array(
				// 		'Authorization' => 'token ' . $github_personal_access_token,
				// 	)
				// ));

				//if ( is_wp_error($discussions_response) ){
					$discussions_response = array('body' => '{}'); //Ignore discussions errors
				//}

				$github_discussions_json = json_decode($discussions_response['body']);

				//Then combine the issues and discussions by most recent first
				$github_combined_posts = wp_json_encode($github_issues_json); //Replace this when discussions api is available

				set_transient('nebula_github_posts', $github_combined_posts, MINUTE_IN_SECONDS*30); //30 minute expiration
			}

			$github_combined_posts = json_decode($github_combined_posts);

			if ( !empty($github_combined_posts) ){
				echo '<ul>';
				for ( $i=0; $i <= 2; $i++ ){ //Get 3 issues
					$github_post_type = 'Unknown';
					if ( strpos($github_combined_posts[$i]->html_url, 'issue') > 0 ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
						$github_post_type = 'Issue';
					} elseif ( strpos($github_combined_posts[$i]->html_url, 'pull') > 0 ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
						$github_post_type = 'Pull Request';
					} elseif ( strpos($github_combined_posts[$i]->html_url, 'discussion') > 0 ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
						$github_post_type = 'Discussion';
					}

					$github_post_date_time = strtotime($github_combined_posts[$i]->updated_at);
					$github_post_date_icon = ( date('Y-m-d', $github_post_date_time) === date('Y-m-d') )? 'fa-clock' : 'fa-calendar';

					echo '<li>
						<p>
							<a href="' . $github_combined_posts[$i]->html_url . '" target="_blank">' . htmlentities($github_combined_posts[$i]->title) . '</a><br />
							<small><i class="fa-regular fa-fw ' . $github_post_date_icon . '"></i> <span title="' . date('F j, Y @ g:ia', $github_post_date_time) . '">' . $github_post_type . ' updated ' . human_time_diff($github_post_date_time) . ' ago</span></small>
						</p>
					</li>';
				}
				echo '</ul>';
			} else {
				echo '<p>No issues or discussions found.</p>';
			}

			echo '<p><small>View all <a href="' . nebula()->get_option('github_url') . '/issues?q=is%3Aissue+is%3Aopen+sort%3Aupdated-desc" target="_blank">issues</a>, <a href="' . nebula()->get_option('github_url') . '/pulls?q=is%3Apr+is%3Aopen+sort%3Aupdated-desc" target="_blank">pull requests</a>, or <a href="' . nebula()->get_option('github_url') . '/discussions" target="_blank">discussions &raquo;</a></small></p>';
			echo '</div></div>';
			nebula()->timer('Nebula Companion GitHub Dashboard', 'end');
		}

		//Hubspot Contacts
		public function hubspot_metabox(){
			wp_add_dashboard_widget('hubspot_contacts', '<i class="fa-brands fa-fw fa-hubspot"></i>&nbsp;Latest Hubspot Contacts', array($this, 'hubspot_contacts_content'));
		}

		//Hubspot Contacts metabox content
		public function hubspot_contacts_content(){
			$this->timer('Nebula Hubspot Dashboard Metabox');
			do_action('nebula_hubspot_contacts');

			$hubspot_contacts_json = nebula()->transient('nebula_hubspot_contacts', function(){
				$requested_properties = '&property=' . implode('&property=', apply_filters('nebula_hubspot_metabox_properties', array('firstname', 'lastname', 'full_name', 'email', 'createdate')));
				$response = $this->remote_get('https://api.hubapi.com/contacts/v1/lists/all/contacts/recent?hapikey=' . $this->get_option('hubspot_api') . '&count=4' . $requested_properties);
				if ( is_wp_error($response) ){
					return false;
				}

				return $response['body'];
			}, MINUTE_IN_SECONDS*30);

			$hubspot_contacts_json = json_decode($hubspot_contacts_json);
			if ( !empty($hubspot_contacts_json) ){
				if ( !empty($hubspot_contacts_json->contacts) ){
					foreach ( $hubspot_contacts_json->contacts as $contact ){
						//Get contact's email address
						$identities = $contact->{'identity-profiles'}[0]->identities;
						foreach ( $identities as $key => $value ){
							if ( strtolower($value->type) === 'email' ){
								$contact_email = $value->value;
							}
						}

						//Get contact's name
						$contact_name = false;
						$has_name = false;
						if ( !empty($contact->properties->firstname) && !empty($contact->properties->lastname) ){
							$contact_name = trim($contact->properties->firstname->value . ' ' . $contact->properties->lastname->value);
							$has_name = true;
						} elseif ( !empty($contact->properties->full_name) ){
							$contact_name = $contact->properties->full_name->value;
							$has_name = true;
						}

						echo '<ul class="hubspot_contact">';

						$before_contact = apply_filters('nebula_hubspot_metabox_before_contact', '', $contact);
						if ( !empty($before_contact) ){
							echo '<li>' . $before_contact . '</li>';
						}
						?>

						<li><?php echo ( $has_name )? '<i class="fa-solid fa-fw fa-user"></i> ' : '<i class="fa-regular fa-fw fa-envelope"></i> '; ?><strong><a href="<?php echo $contact->{'profile-url'}; ?>" target="_blank"><?php echo ( $has_name )? $contact_name : $contact_email; ?></a></strong></li>

						<?php if ( $has_name ): ?>
							<li><i class="fa-regular fa-fw fa-envelope"></i> <?php echo $contact_email; ?><br /></li>
						<?php endif; ?>

						<li><i class="fa-regular fa-fw fa-<?php echo ( date('Y-m-d', $contact->addedAt/1000) === date('Y-m-d') )? 'clock' : 'calendar'; ?>"></i> <span title="<?php echo date('F j, Y @ g:ia', $contact->addedAt/1000); ?>" style="cursor: help;"><?php echo human_time_diff($contact->addedAt/1000) . ' ago'; ?></span></li>

						<?php
						$after_contact = apply_filters('nebula_hubspot_metabox_after_contact', '', $contact);
						if ( !empty($after_contact) ){
							echo '<li>' . $after_contact . '</li>';
						}

						echo '</ul>';
					}
				} else {
					echo '<p><small>No contacts yet.</small></p>';
				}
			} else {
				echo '<p><small>Hubspot contacts unavailable.</small></p>';
			}

			echo '<p><small><a href="https://app.hubspot.com/sales/' . $this->get_option('hubspot_portal') . '/contacts/list/view/all/" target="_blank">View on Hubspot &raquo;</a></small></p>';
			$this->timer('Nebula Hubspot Dashboard Metabox', 'end');
		}
	}
}