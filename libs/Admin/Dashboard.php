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
						add_action('wp_dashboard_setup', array($this, 'file_size_monitor_metabox'));
					}

					if ( $this->get_option('performance_metabox') || $this->is_dev() ){ //Devs always see the performance metabox
						add_action('wp_dashboard_setup', array($this, 'performance_metabox'));
					}

					if ( $this->get_option('design_reference_metabox') ){
						add_action('wp_dashboard_setup', array($this, 'design_metabox'));
					}

					if ( $this->get_option('github_url') && $this->get_option('github_pat') ){ //Requires a GitHub URL and Personal Access Token
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

		//Output the simplify class name when the simplify dashboard metaboxes option is enabled
		public function get_simplify_dashboard_class(){
			if ( $this->get_option('simplify_dashboard_metaboxes') ){
				return 'simplify';
			}

			return '';
		}

		//Remove unnecessary Dashboard metaboxes
		public function remove_dashboard_metaboxes(){
			$override = apply_filters('pre_remove_dashboard_metaboxes', null);
			if ( isset($override) ){return null;}

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
			if ( $this->is_minimal_mode() ){return null;}

			global $wp_meta_boxes;
			wp_add_dashboard_widget('nebula_ataglance', '<img src="' . get_site_icon_url(32, get_theme_file_uri('/assets/img/meta') . '/favicon-32x32.png') . '" style="float: left; width: 20px; margin-right: 3px;" loading="lazy" />&nbsp;' . get_bloginfo('name'), array($this, 'dashboard_nebula_ataglance'));
		}

		public function dashboard_nebula_ataglance(){
			$this->timer('Nebula At-a-Glance Dashboard Metabox', 'start', '[Nebula] Dashboard Metaboxes');

			echo '<ul class="nebula-fa-ul ' . $this->get_simplify_dashboard_class() . '">';

			//Data Loaded Time
			echo '<li class="essential"><i class="fa-solid fa-fw fa-clock" title="This page last loaded"></i> <strong class="relative-date-tooltip" title="Just now" style="cursor: help;">' . date('l, F j, Y - g:i:sa') . '</strong></li>'; //The "Just Now" title text gets updated by JavaScript after load

			//Page load time for this WP Dashboard request
			if ( !empty($this->super->server['REQUEST_TIME_FLOAT']) ){
				$response_time = (microtime(true)-$this->super->server['REQUEST_TIME_FLOAT']);
				echo '<li class=""><i class="fa-solid fa-fw fa-stopwatch"></i> Server Response Time: <strong><span class="nebula-ttfb-time">~' . number_format($response_time, 3) . '</span> seconds</strong></li>'; //Note: essential/text classes are added by JavaScript where accurate total time is processed
			}

			//Website URL
			echo '<li class="essential"><i class="fa-solid fa-fw fa-globe"></i> <a href="' . home_url('/') . '" target="_blank" rel="noopener noreferrer">' . home_url('/') . '</a></li>';

			//Address
			if ( $this->get_option('street_address') ){
				echo '<li><i class="fa-solid fa-fw fa-map-marker"></i> <a href="https://www.google.com/maps/place/' . $this->full_address(1) . '" target="_blank" rel="noopener noreferrer">' . $this->full_address() . '</a></li>';
			}

			//Open/Closed
			if ( $this->has_business_hours() ){
				$open_closed = ( $this->business_open() )? '<strong class="text-success">Open</strong>' : '<strong>Closed</strong>';
				echo '<li><i class="fa-regular fa-fw fa-clock"></i> Currently ' . $open_closed . '</li>';
			}

			//WordPress Version
			global $wp_version;
			echo '<li class="essential"><i class="fa-brands fa-fw fa-wordpress"></i> <a href="https://codex.wordpress.org/WordPress_Versions" target="_blank" rel="noopener noreferrer">WordPress</a> <strong>' . $wp_version . '</strong></li>';

			//Nebula Version
			$time_diff = human_time_diff($this->version('utc')) . ' ago';
			if ( date('Y-m-d', $this->version('utc')) === date('Y-m-d', strtotime('now')) ){ //If it was committed today
				$time_diff = 'today';
			}
			echo '<li class="essential"><i class="fa-regular fa-fw fa-star"></i> <a href="https://nebula.gearside.com?utm_campaign=documentation&utm_medium=dashboard&utm_source=' . urlencode(site_url()) . '&utm_content=at+a+glance+version" target="_blank" rel="noopener noreferrer">Nebula</a> <strong><a href="https://github.com/chrisblakley/Nebula/compare/main@{' . date('Y-m-d', $this->version('utc')) . '}...main" target="_blank">' . $this->version('realtime') . '</a></strong> <small title="' . $this->version('date') . '" style="cursor: help;">(Committed ' . $time_diff . ')</small></li>';

			//Check if parent theme files have been modified (this is in the Nebula metabox, but also happens in the developer info metabox)
			if ( !$this->get_option('dev_info_metabox') || !$this->is_dev() ){ //Prevents this from appearing twice in the dashboard. Only show this if the Developer Info Metabox is not being shown.
				$modified_files = get_transient('nebula_theme_modified_files');
				if ( !empty($modified_files) ){
					$file_count = count($modified_files);

					$title_attr = implode("\n", $modified_files); //Join file names with new lines for the title attribute
					$time_ago = human_time_diff(get_transient('nebula_theme_file_changes_check'), time());
					$title_attr .= "\n\n Last checked " . $time_ago . " ago";

					echo '<li><i class="fa-solid fa-square-binary"></i> <span class="text-caution cursor-help" title="' . esc_attr($title_attr) . '"><strong>' . $file_count . '</strong> Parent theme ' . $this->singular_plural($file_count, 'file has', 'files have') . ' been modified</span></li>';
				}
			}

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
				echo '<li class="essential"><i class="fa-solid fa-fw fa-cubes"></i> Multisite' . $network_admin_link;
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
			$post_type_all_stats = $this->get_post_type_view_totals();
			$post_type_stats = $post_type_all_stats['totals']; //The number of views for each post type
			$days_of_data = $post_type_all_stats['days']; //The number of days we have view data for
			$valid_post_types = array(); //These are the post types we want to count and use for earliest, latest, modified, etc.

			foreach ( get_post_types() as $post_type ){
				//Only show post types that show_ui (unless forced with one of the arrays below)
				$force_show = array('wpcf7_contact_form'); //These will show even if their show_ui is false.
				$force_hide = array('attachment', 'acf', 'deprecated_log'); //These will be skipped even if their show_ui is true.
				if ( (!$wp_post_types[$post_type]->show_ui && !in_array($post_type, $force_show)) || in_array($post_type, $force_hide)){
					continue;
				}

				$valid_post_types[] = $post_type; //Add this post type to the valid list

				//Check for server-stats for this post type
				$post_type_stat_description = '';
				if ( isset($post_type_stats[$post_type]) ){
					$view_count = $post_type_stats[$post_type];
					$label = ( $days_of_data >= 7 )? 'Weekly' : $days_of_data . '-day'; //If we don't have a full week, show the day count
					$post_type_stat_description = '<small> (' . $label . ' Views: ' . $view_count . ')</small>'; //$this->singular_plural($view_count, 'view')
				}

				$cache_length = ( is_plugin_active('transients-manager/transients-manager.php') )? WEEK_IN_SECONDS : DAY_IN_SECONDS; //If Transient Monitor (plugin) is active, transients with expirations are deleted when posts are published/updated, so this could be infinitely long (as long as an expiration exists).
				$count_posts = $this->transient('nebula_count_posts_' . $post_type, function($data){
					$count_posts = wp_count_posts($data['post_type']);
					return $count_posts;
				}, array('post_type' => $post_type), $cache_length);

				$post_count = $count_posts->publish;
				switch ( $post_type ){
					case ( 'post' ):
						$post_icon_img = '<i class="fa-solid fa-fw fa-thumbtack essential"></i>';
						break;
					case ( 'page' ):
						$post_icon_img = '<i class="fa-solid fa-fw fa-file-alt essential"></i>';
						break;
					case ( 'wp_block' ):
						$post_icon_img = '<i class="fa-regular fa-fw fa-clone"></i>';
						break;
					case ( 'wpcf7_contact_form' ):
						$post_icon_img = '<i class="fa-solid fa-fw fa-envelope"></i>';
						break;
					case ( 'nebula_cf7_submits' ):
						$post_count = $count_posts->private; //These are all stored privately
						break;
					default:
						$post_icon = $wp_post_types[$post_type]->menu_icon;
						$post_icon_img = '<i class="fa-solid fa-fw fa-thumbtack"></i>';

						if ( !empty($post_icon) ){
							if ( str_contains($post_icon, 'dashicons-') ){
								$post_icon_img = '<span class="not-fa-icon dashicons ' . $post_icon . '"></span>';
							} else if ( filter_var($post_icon, FILTER_VALIDATE_URL) ){
								$post_icon_img = '<img class="not-fa-icon " src="' . $post_icon . '" style="width: 16px; height: 16px;" loading="lazy" />';
							} else {
								$post_icon_img = esc_html($post_icon); //Fallback for non-url, non-dashicon cases
							}
						}

						break;
				}

				$labels_plural = $this->singular_plural($post_count, $wp_post_types[$post_type]->labels->singular_name, $wp_post_types[$post_type]->labels->name);
				$essential_count_class = ( $post_count >= 12 )? 'essential' : ''; //If the post count is high, show this post type in simplified view

				echo '<li>' . $post_icon_img . ' <a href="edit.php?post_type=' . $post_type . '"><strong class="' . $essential_count_class . '">' . number_format($post_count) . '</strong> ' . $labels_plural . '</a>' . $post_type_stat_description . '</li>';
			}

			//Earliest post
			$earliest_post = $this->transient('nebula_earliest_post', function($valid_post_types){
				return new WP_Query(array('post_type' => $valid_post_types, 'post_status' => 'publish', 'showposts' => 1, 'orderby' => 'publish_date', 'order' => 'ASC'));
			}, YEAR_IN_SECONDS); //This transient is deleted when posts are added/updated, so this could be infinitely long (as long as an expiration exists).

			while ( $earliest_post->have_posts() ){ $earliest_post->the_post();
				echo '<li><i class="fa-regular fa-fw fa-calendar"></i> Earliest: <span title="' . get_the_date() . ' @ ' . get_the_time() . '" style="cursor: help;"><strong>' . human_time_diff(strtotime(get_the_date() . ' ' . get_the_time())) . ' ago</strong></span><small style="display: block;"><i class="fa-regular fa-fw fa-file-alt"></i> <a href="' . get_permalink() . '">' . $this->excerpt(array('text' => esc_html(get_the_title()), 'words' => 5, 'more' => false, 'ellipsis' => true)) . '</a> (' . get_the_author() . ')</small></li>';
			}
			wp_reset_postdata();

			//Last updated
			$latest_post = $this->transient('nebula_latest_post', function($valid_post_types){
				return new WP_Query(array('post_type' => $valid_post_types, 'post_status' => 'publish', 'showposts' => 1, 'orderby' => 'modified', 'order' => 'DESC'));
			}, WEEK_IN_SECONDS); //This transient is deleted when posts are added/updated, so this could be infinitely long.
			while ( $latest_post->have_posts() ){ $latest_post->the_post();
				echo '<li class="essential"><i class="fa-regular fa-fw fa-calendar"></i> Updated: <span title="' . get_the_modified_date() . ' @ ' . get_the_modified_time() . '" style="cursor: help;"><strong>' . human_time_diff(strtotime(get_the_modified_date())) . ' ago</strong></span>
					<small style="display: block;"><i class="fa-regular fa-fw fa-file-alt"></i> <a href="' . get_permalink() . '">' . $this->excerpt(array('text' => esc_html(get_the_title()), 'words' => 5, 'more' => false, 'ellipsis' => true)) . '</a> (' . get_the_author() . ')</small>
				</li>';
			}
			wp_reset_postdata();

			//Revisions
			$revision_count = ( WP_POST_REVISIONS == -1 )? 'all' : WP_POST_REVISIONS;
			$revision_class = ( $revision_count === 0 )? 'class="text-danger"' : '';
			$revisions_plural = $this->singular_plural($revision_count, 'revision');
			echo '<li><i class="fa-solid fa-fw fa-history"></i> Storing <strong ' . $revision_class . '>' . $revision_count . '</strong> ' . $revisions_plural . '.</li>';

			//Plugins
			$all_plugins = $this->transient('nebula_count_plugins', function(){
				return get_plugins();
			}, WEEK_IN_SECONDS);
			$all_plugins_plural = $this->singular_plural(count($all_plugins), 'Plugin');

			$active_plugins = get_option('active_plugins', array());
			echo '<li class="essential"><i class="fa-solid fa-fw fa-plug"></i> <a href="plugins.php"><strong>' . count($all_plugins) . '</strong> ' . $all_plugins_plural . '</a> installed <small>(' . count($active_plugins) . ' active)</small></li>';

			//Must-Use Plugins
			if ( is_dir(WPMU_PLUGIN_DIR) && is_array(scandir(WPMU_PLUGIN_DIR)) ){ //Make sure this directory exists
				$mu_plugin_count = count(array_diff(scandir(WPMU_PLUGIN_DIR), array('..', '.'))); //Count the files in the mu-plugins directory (and remove the "." and ".." directories from scandir())
				if ( !empty($mu_plugin_count) && $mu_plugin_count >= 1 ){
					$mu_plugins_plural = $this->singular_plural($mu_plugin_count, 'Must-Use Plugin');
					echo '<li><i class="fa-solid fa-fw fa-plug"></i> <a href="plugins.php"><strong>' . $mu_plugin_count . '</strong> ' . $mu_plugins_plural . '</a></li>';
				}
			}

			//Users
			$user_count = $this->transient('nebula_count_users', function(){
				return count_users();
			}, WEEK_IN_SECONDS);
			$users_icon = 'users';
			$users_plural = 'Users';
			if ( $user_count['total_users'] === 1 ){
				$users_plural = 'User';
				$users_icon = 'user';
			}
			echo '<li class="essential"><i class="fa-solid fa-fw fa-' . $users_icon . '"></i> <a href="users.php">' . number_format($user_count['total_users']) . ' ' . $users_plural . '</a> <small>(' . $this->online_users('count') . ' currently active)</small></li>';

			//Failed Login Count
			if ( $this->get_login_counts('fail') > 0 ){
				$username_tooltip = implode("\n", $this->get_login_counts('fail', true));
				echo '<li class="essential text-danger" title="' . esc_attr($username_tooltip) . '"><i class="fa-solid fa-fw fa-user-xmark"></i> <strong>' . $this->get_login_counts('fail') . '</strong> Failed Logins <small>(Within the last week)</small>';
			}

			//Comments
			if ( $this->get_option('comments') && $this->get_option('disqus_shortname') == '' ){
				$comments_count = wp_count_comments();
				$comments_plural = $this->singular_plural($comments_count->approved, 'Comment');
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
				echo '<li class="essential"><i class="fa-regular fa-fw fa-bars"></i> Admin Bar disabled <small>(for all users via <a href="themes.php?page=nebula_options&tab=functions&option=admin_bar">Nebula Options</a>)</small></li>';
			}

			echo '<li class="expand-simplified-view essential"><a href="#">...Expand full list <i class="fa-solid fa-fw fa-caret-down"></i></a></li>';

			echo '</ul>';

			do_action('nebula_ataglance');
			$this->timer('Nebula At-a-Glance Dashboard Metabox', 'end');
		}

		//Current User metabox
		public function current_user_metabox(){
			if ( $this->is_minimal_mode() ){return null;}

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
			$this->timer('Nebula Current User Dashboard Metabox', 'start', '[Nebula] Dashboard Metaboxes');
			$user_info = get_userdata(get_current_user_id());

			echo '<ul class="nebula-fa-ul ' . $this->get_simplify_dashboard_class() . '">';
			//Company
			$company = '';
			if ( get_the_author_meta('jobcompany', $user_info->ID) ){
				$company = get_the_author_meta('jobcompany', $user_info->ID);
				if ( get_the_author_meta('jobcompanywebsite', $user_info->ID) ){
					$company = '<a class="this-user-company-name" href="' . get_the_author_meta('jobcompanywebsite', $user_info->ID) . '?utm_campaign=documentation&utm_medium=dashboard&utm_source=' . urlencode(site_url()) . '&utm_content=user_metabox_job_title" target="_blank" rel="noopener noreferrer">' . $company . '</a>';
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
				echo '<li><i class="fa-solid fa-fw fa-location-dot"></i> <strong>' . get_the_author_meta('usercity', $user_info->ID) . ', ' . get_the_author_meta('userstate', $user_info->ID) . '</strong></li>';
			}

			//Email
			echo '<li class="essential"><i class="fa-regular fa-fw fa-envelope"></i> Email: <strong>' . $user_info->user_email . '</strong></li>';

			if ( get_the_author_meta('phonenumber', $user_info->ID) ){
				echo '<li><i class="fa-solid fa-fw fa-phone"></i> Phone: <strong>' . get_the_author_meta('phonenumber', $user_info->ID) . '</strong></li>';
			}

			echo '<li class="essential"><i class="fa-regular fa-fw fa-user"></i> Username: <strong>' . $user_info->user_login . '</strong></li>';
			echo '<li class="essential"><i class="fa-solid fa-fw fa-info-circle"></i> ID: <strong>' . $user_info->ID . '</strong></li>';

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
			echo '<li class="essential"><i class="fa-solid fa-fw ' . $fa_role . '"></i> Role: <strong class="admin-user-info admin-user-role">' . $user_role . '</strong></li>';

			//Posts by this user
			$your_posts = $this->transient('nebula_count_posts_user_' . $user_info->ID, function($data){
				return count_user_posts($data['id']);
			}, array('id' => $user_info->ID), DAY_IN_SECONDS);
			echo '<li class="essential"><i class="fa-solid fa-fw fa-thumbtack"></i> Your posts: <strong>' . $your_posts . '</strong></li>';

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

			echo '<li id="current-user-color-scheme-light-preference" class="ignore-simplify"><i class="fa-solid fa-fw fa-sun"></i> Prefers <strong>Light Color Scheme</strong></li>';
			echo '<li id="current-user-color-scheme-dark-preference" class="ignore-simplify"><i class="fa-solid fa-fw fa-moon"></i> Prefers <strong>Dark Color Scheme</strong></li>';
			echo '<li id="current-user-contrast-more-preference" class="ignore-simplify"><i class="fa-solid fa-fw fa-circle-half-stroke"></i> Prefers <strong>More Contrast</strong></li>';
			echo '<li id="current-user-transparency-preference" class="ignore-simplify"><i class="fa-solid fa-fw fa-fill-drip"></i> Prefers <strong>Reduced Transparency</strong></li>';
			echo '<li id="current-user-motion-preference" class="ignore-simplify"><i class="fa-solid fa-fw fa-arrows-to-circle"></i> Prefers <Strong>Reduced Motion</strong></li>';
			echo '<li id="current-user-data-preference" class="ignore-simplify"><i class="fa-solid fa-fw fa-wifi"></i> Prefers <strong>Reduced Data</strong></li>';

			//IP Address
			echo '<li class="essential"><i class="fa-solid fa-fw fa-globe"></i> Your IP Address: <a href="http://whatismyipaddress.com/ip/' . $this->get_ip_address() . '" target="_blank" rel="noopener noreferrer"><strong class="admin-user-info admin-user-ip" title="Anonymized IP Address">' . $this->get_ip_address() . '</strong></a> <small>(Anonymized)</small></li>';

			//Server-provided geolocation data
			if ( $this->get_geo_data('country') ){
				echo '<li class="essential"><i class="fa-solid fa-fw fa-earth-americas"></i> <span title="Approximate device geolocation provided by request gateway">Country: <strong>' . $this->get_geo_data('country') . '</strong></span></li>';
			}

			if ( $this->get_geo_data('city') && $this->get_geo_data('region') ){
				echo '<li><i class="fa-solid fa-fw fa-city"></i> <span title="Approximate device geolocation provided by request gateway">City: <strong>' . $this->get_geo_data('city') . ', ' . $this->get_geo_data('region') . '</strong></span></li>';
			}

			$metro_code = $this->get_geo_data('metro_code');
			if ( !empty($metro_code) && $metro_code != 555 ){
				echo '<li><i class="fa-solid fa-fw fa-map"></i> <span title="Approximate device geolocation provided by request gateway">Area Code: <strong>' . $metro_code . '</strong></span></li>';
			}

			if ( $this->get_geo_data('postal_code') ){
				echo '<li><i class="fa-solid fa-fw fa-map-location-dot"></i> <span title="Approximate device geolocation provided by request gateway">Postal Code: <strong>' . $this->get_geo_data('postal_code') . '</strong></span></li>';
			}

			if ( $this->get_geo_coordinates() ){
				$coordinates = $this->get_geo_coordinates();
				echo '<li><i class="fa-solid fa-fw fa-location-crosshairs"></i> <span title="Approximate device geolocation provided by request gateway">Coordinates: <strong><a href="' . esc_url('https://maps.google.com?q=' . $coordinates) . '" target="_blank" rel="noopener noreferrer">' . $coordinates . '</a></strong></span></li>';
			}

			//Multiple locations
			if ( $this->user_single_concurrent($user_info->ID) > 1 ){
				echo '<li class="essential"><i class="fa-solid fa-fw fa-users"></i> Active in <strong>' . $this->user_single_concurrent($user_info->ID) . ' locations</strong>.</li>';
			}

			//User Admin Bar
			if ( !get_user_option('show_admin_bar_front', $user_info->ID) ){
				echo '<li><i class="fa-solid fa-fw fa-bars"></i> Admin Bar disabled <small>(for just you via <a href="profile.php">User Profile</a>)</small></li>';
			}

			do_action('nebula_user_metabox');

			echo '<li class="expand-simplified-view essential"><a href="#">...Expand full list <i class="fa-solid fa-fw fa-caret-down"></i></a></li>';
			echo '</ul>';

			echo '<p><small><em><a href="profile.php"><i class="fa-solid fa-fw fa-pencil-alt"></i> Manage your user information</a></em></small></p>';
			$this->timer('Nebula Current User Dashboard Metabox', 'end');
		}

		//Administrative metabox
		public function administrative_metabox(){
			if ( $this->is_minimal_mode() ){return null;}
			wp_add_dashboard_widget('nebula_administrative', '<i class="fa-solid fa-fw fa-paperclip"></i> &nbsp;Administrative', array($this, 'dashboard_administrative'));
		}

		//Administrative metabox content
		public function dashboard_administrative(){
			$this->timer('Nebula Administrative Dashboard Metabox', 'start', '[Nebula] Dashboard Metaboxes');
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
			if ( $this->is_minimal_mode() ){return null;}
			wp_add_dashboard_widget('todo_manager', '<i class="fa-solid fa-fw fa-check-square"></i>&nbsp;To-Do Manager', array($this, 'todo_metabox_content'));
		}

		//TODO metabox content
		public function todo_metabox_content(){
			$this->timer('Nebula To-Do Dashboard Metabox', 'start', '[Nebula] Dashboard Metaboxes');
			do_action('nebula_todo_manager');

			$todo_items = $this->transient('nebula_todo_items', function(){
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
					<strong>Active @todo Comments</strong> <a class="todo_help_icon" href="https://gearside.com/wordpress-dashboard-todo-manager/?utm_campaign=documentation&utm_medium=dashboard&utm_source=<?php echo urlencode(site_url()); ?>&utm_content=todo_metabox" target="_blank" rel="noopener noreferrer"><i class="fa-regular fw fa-question-circle"></i> Documentation &raquo;</a>
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
				echo '<p class="todo-nothing-found"><i class="fa-regular fa-fw fa-smile"></i> None!</p>';
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
			$directory ??= get_template_directory();

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
			if ( $this->is_minimal_mode() ){return null;}
			wp_add_dashboard_widget('nebula_developer_info', '<i class="fa-solid fa-fw fa-code"></i> &nbsp;Developer Information', array($this, 'dashboard_developer_info'));
		}

		//Developer Info Metabox content
		public function dashboard_developer_info(){
			$this->timer('Nebula Developer Dashboard Metabox', 'start', '[Nebula] Dashboard Metaboxes');
			do_action('nebula_developer_info');

			echo '<ul class="nebula-fa-ul serverdetections ' . $this->get_simplify_dashboard_class() . '">';

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
			$domain = $this->url_components('domain');
			$domain ??= '<small>(None)</small>';

			echo '<li class="essential"><i class="fa-solid fa-fw fa-info-circle"></i> <a href="http://whois.domaintools.com/' . $this->super->server['SERVER_NAME'] . '" target="_blank" rel="noopener noreferrer" title="WHOIS Lookup">Domain</a>: <strong>' . $domain . '</strong></li>';

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

			//Get the network gateway if it exists
			if ( $this->get_network_gateway() ){
				echo '<li><i class="fa-solid fa-fw fa-diagram-project"></i> Network Gateway: <strong>' . $this->get_network_gateway() . '</strong></li>';
			}

			//Server IP address (and connection security)
			$secure_server = '<small class="unsecured-connection essential"><i class="fa-solid fa-fw fa-unlock"></i>Unsecured Connection</small>';
			if ( (!empty($this->super->server['HTTPS']) && $this->super->server['HTTPS'] !== 'off') || $this->super->server['SERVER_PORT'] === 443 ){
				$secure_server = '<small class="secured-connection"><i class="fa-solid fa-fw fa-lock"></i>Secured Connection</small>';
			}
			$public_local_ip = ( preg_match('/^(127\.|192\.168|172\.|10\.)/', $this->super->server['SERVER_ADDR']) )? 'Local' : 'Public'; //Check if the server IP is likely local (private) or public (this is not perfectly exact)
			echo '<li><i class="fa-solid fa-fw fa-upload"></i> ' . $public_local_ip . ' Server IP: <strong><a href="http://whatismyipaddress.com/ip/' . $this->super->server['SERVER_ADDR'] . '" target="_blank" rel="noopener noreferrer">' . apply_filters('nebula_dashboard_server_ip', $this->super->server['SERVER_ADDR']) . '</a></strong> ' . $secure_server . '</li>';

			//Server Time Zone
			if ( !empty(get_option('timezone_string')) && date_default_timezone_get() === get_option('timezone_string') && wp_timezone_string() === get_option('timezone_string') ){
				echo '<li><i class="fa-solid fa-fw fa-globe-americas"></i> Timezone: <strong>' . date_default_timezone_get() . '</strong></li>';
			} else {
				echo '<li class="essential"><i class="fa-solid fa-fw fa-globe-americas"></i> Server Timezone: <strong>' . date_default_timezone_get() . '</strong></li>';
				echo '<li class="essential"><i class="fa-solid fa-fw fa-globe-americas"></i> WordPress Timezone Option: <strong>' . get_option('timezone_string') . '</strong></li>';
				echo '<li class="essential"><i class="fa-solid fa-fw fa-globe-americas"></i> WordPress Timezone String: <strong>' . wp_timezone_string() . '</strong></li>';
			}

			//Server operating system
			if ( str_contains(strtolower(PHP_OS), 'linux') ){
				$php_os_icon = 'fa-brands fa-linux';
			} elseif ( str_contains(strtolower(PHP_OS), 'windows') ){
				$php_os_icon = 'fa-brands fa-windows essential';
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
			$php_version_class = '';
			$php_version_info = '';
			$php_version_cursor = 'normal';
			$php_version_lifecycle = $this->php_version_support();
			if ( !empty($php_version_lifecycle) ){
				if ( $php_version_lifecycle['lifecycle'] === 'security' ){
					$php_version_class = 'text-caution essential'; //Warning (orange)
					$php_version_info = 'This version is nearing end of life. Security updates end on ' . date('F j, Y', $php_version_lifecycle['end']) . '.';
					$php_version_cursor = 'help';
				} elseif ( $php_version_lifecycle['lifecycle'] === 'end' ){
					$php_version_class = 'text-danger essential'; //Danger (red)
					$php_version_info = 'This version no longer receives security updates! End of life occurred on ' . date('F j, Y', $php_version_lifecycle['end']) . '.';
					$php_version_cursor = 'help';
				}
			}
			echo '<li class="essential"><i class="fa-solid fa-fw fa-wrench"></i> PHP Version: <a class="' . $php_version_class . '" href="https://www.php.net/supported-versions.php" target="_blank" rel="noopener noreferrer" style="cursor: ' . $php_version_cursor . ';" title="' . $php_version_info . '"><strong>' . PHP_VERSION . '</strong></a> <small>(SAPI: <strong>' . php_sapi_name() . '</strong>)</small></li>';

			//PHP memory limit
			echo '<li><i class="fa-solid fa-fw fa-memory"></i> PHP Memory Limit: <strong>' . ini_get('memory_limit') . '</strong></li>';

			//Persistent Object Caching (Memcached or Redis)
			$memory_cache_enabled = '<strong class="text-caution essential">Disabled</strong>';
			if ( extension_loaded('memcache') ){
				$memory_cache_enabled = '<strong>Memcache</strong>';
			} elseif ( extension_loaded('memcached') ){
				$memory_cache_enabled = '<strong>Memcached</strong>';
			} elseif ( extension_loaded('redis') ){
				$memory_cache_enabled = '<strong>Redis</strong>';
			}
			echo '<li title="This does not indicate that this memory cache extension is actually being used, just that it is loaded."><i class="fa-solid fa-fw fa-box"></i> Memory Cache Ext.: ' . $memory_cache_enabled . '</li>';

			$object_cache_hit_output = '';
			if ( is_object($this->super->globals['wp_object_cache']) ){
				$object_cache_hits = $this->super->globals['wp_object_cache']->cache_hits ?? null;
				$object_cache_misses = $this->super->globals['wp_object_cache']->cache_misses ?? null;

				if ( $object_cache_hits+$object_cache_misses > 0 ){
					$object_cache_hit_rate = round(($object_cache_hits/($object_cache_hits+$object_cache_misses))*100, 1);
					$object_cache_hit_rate_class = ( $object_cache_hit_rate < 80 )? 'text-caution' : '';

					$object_cache_hit_output = ' <small class="' . $object_cache_hit_rate_class . '">(Hit Rate: ' . $object_cache_hit_rate . '%)</small>';
				}
			}

			$using_wp_persistent_cache = ( wp_using_ext_object_cache() )? '<strong title="Caching will work across multiple pages">⚡ Persistent</strong>' : '<strong title="Caching will only happen on individual page request (not across multiple pages)">Non-Persistent</strong>';
			echo '<li><i class="fa-solid fa-fw fa-box"></i> WP Object Cache: ' . $using_wp_persistent_cache . $object_cache_hit_output . ' </li>';

			//Bytecode Caching aka Opcode Cache (Zend Opcache)
			$opcode_cache_name = '<strong class="text-danger essential">None</strong>';
			if ( extension_loaded('Zend OPcache') ){
				$opcode_cache_name = '<strong>Zend OPcache</strong>';
			} elseif ( extension_loaded('opcache') ){
				$opcode_cache_name = '<strong>OPcache</strong>';
			}

			if ( function_exists('opcache_get_status') ){
				$opcache_status = opcache_get_status();

				$opcache_hit_rate = '';
				$opcache_stats = null;
				if ( isset($opcache_status['statistics']) && is_array($opcache_status['statistics']) ){
					$opcache_stats = $opcache_status['statistics'];
				} else if ( isset($opcache_status['opcache_statistics']) && is_array($opcache_status['opcache_statistics']) ){
					$opcache_stats = $opcache_status['opcache_statistics'];
				}

				if ( $opcache_stats && isset($opcache_stats['opcache_hit_rate']) ){
					$opcache_hit_rate_class = ( $opcache_stats['opcache_hit_rate'] < 85 )? 'text-caution' : '';
					$opcache_hit_rate = ' <small class="' . $opcache_hit_rate_class . '" title="When the hit rate is low, PHP must recompile code more often, which increases page load time.">(Hit Rate: ' . round($opcache_stats['opcache_hit_rate'], 1) . '%)</small>';
				}

				if ( isset($opcache_status['opcache_enabled']) && $opcache_status['opcache_enabled'] == 0 ){
					echo '<li class="essential text-caution"><i class="fa-solid fa-fw fa-box"></i> Opcache Disabled</li>';
				}
			}

			echo '<li><i class="fa-solid fa-fw fa-box"></i> Opcode Cache: ' . $opcode_cache_name . $opcache_hit_rate . '</li>';

			if ( function_exists('sys_getloadavg') ){
				$load = sys_getloadavg();
				if ( is_array($load) && count($load) >= 3 ){
					list($load_1m, $load_5m, $load_15m) = $load;

					//Check each value against the warning threshold
					//Note: The thresholds are really dependent on how many CPU cores the server has. 1 process with 1 core = 100% utilization. However 1 process with 4 cores = 25% utilization.
					$cpu_cores = apply_filters('nebula_cpu_cores', 1); //Allow developers to designate the number of CPU cores their server has to better reflect warning states for load averages

					$caution_threshold = $cpu_cores*1; //100% utilization
					$warning_threshold = $cpu_cores*2; //200% utilization

					//Check each value against the thresholds and assign classes accordingly
					//Note: Removing text color classes using "-off" until better thresholding is possible
					$class_1m = '';
					if ( $load_1m > $warning_threshold ){
						$class_1m = 'essential text-danger-off';
					} elseif ( $load_1m > $caution_threshold ){
						$class_1m = 'essential text-caution-off';
					}

					$class_5m = '';
					if ( $load_5m > $warning_threshold ){
						$class_5m = 'essential text-danger-off';
					} elseif ( $load_5m > $caution_threshold ){
						$class_5m = 'essential text-caution-off';
					}

					$class_15m = '';
					if ( $load_15m > $warning_threshold ){
						$class_15m = 'essential text-danger-off';
					} elseif ( $load_15m > $caution_threshold ){
						$class_15m = 'essential text-caution-off';
					}

					//Increasing or decreasing load
					$load_icon = '<i class="fa-solid fa-fw fa-network-wired"></i>'; //Default for steady traffic
					if ( $load_1m > $load_5m && $load_5m > $load_15m ){
						$load_icon = '<i class="fa-solid fa-arrow-trend-up caution-color" title="Traffic increasing"></i>';
					}else if ( $load_1m < $load_5m && $load_5m < $load_15m ){
						$load_icon = '<i class="fa-solid fa-arrow-trend-down caution-color" title="Traffic decreasing"></i>';
					}

					echo '<li title="Average number of processes waiting for CPU (higher = busier). Caution thresholds depend on the amount of CPU cores (which can be set with a Nebula hook).">' . $load_icon . ' Load Avg: <span class="' . $class_1m . '" title="1 minute"><strong>' . number_format($load_1m, 2) . '</strong> <em>(1 min)</em></span>, <span class="' . $class_5m . '" title="5 minutes"><strong>' . number_format($load_5m, 2) . '</strong> <em>(5 min)</em></span>, <span class="' . $class_15m . '" title="15 minutes"><strong>' . number_format($load_15m, 2) . '</strong> <em>(15 min)</em></span></li>';
				}
			}

			//Count total WordPress updates available
			require_once ABSPATH . 'wp-admin/includes/update.php';
			$update_data = wp_get_update_data();
			$updates_count = $update_data['counts']['total'];
			if ( $updates_count > 0 ){
				$updates_class = '';

				if ( $updates_count > 10 ){ //If there are many updates available
					$updates_class = 'text-danger essential';
				} elseif ( $updates_count >= 1 ){ //If even 1 update is available
					$updates_class = 'essential';
				}

				$updates_count = '<a class="' . $updates_class . '" href="update-core.php">' . $updates_count . ' &raquo;</a>';
				echo '<li><i class="fa-regular fa-fw fa-circle-up"></i> Updates Available: <strong>' . $updates_count . '</strong></li>';
			}

			//Check SMTP mail status
			$smtp_status = $this->check_smtp_status();
			$smtp_status_output = ''; //Empty unless there is a problem
			if ( strpos(strtolower($smtp_status), 'error') != false ){
				$smtp_status_output = '<a href="edit.php?post_type=nebula_cf7_submits"><strong class="text-danger essential"><i class="fa-solid fa-fw fa-exclamation-triangle"></i> ' . $smtp_status . '</strong></a>';
			} elseif ( strtolower($smtp_status) == 'unknown' ){
				$smtp_status_output = '<a href="edit.php?post_type=nebula_cf7_submits"><em class="text-caution">Unable to Check</em></a>';
			}
			if ( !empty($smtp_status_output) ){
				echo '<li><i class="fa-solid fa-fw fa-envelope"></i> SMTP Status: ' . $smtp_status_output . '</li>';
			}

			//404 Counts
			$nebula_404_count = $this->get_404_count();
			$redirection_404_count = 0;
			if ( is_plugin_active('redirection/redirection.php') ){
				$redirection_404_count = $this->transient('redirection_404_count', function(){
					global $wpdb;

					//Count the rows in PHP (instead of MySQL) to avoid processing the entire DB table
					$results = $wpdb->get_col($wpdb->prepare(
						'SELECT http_code FROM ' . $wpdb->prefix . 'redirection_404
						WHERE http_code = 404
						AND created >= %s
						LIMIT 1000',
						date('Y-m-d H:i:s', strtotime('-24 hours'))
					));

					return count($results);
				}, HOUR_IN_SECONDS);
			}

			if ( !empty($nebula_404_count) || !empty($redirection_404_count) ){ //If either 404 counter has data, output it
				$redirection_404_description = '';
				$nebula_404_description = '';
				$need_404_labels = ( !empty($nebula_404_count) && !empty($redirection_404_count) )? true : false; //If both systems are active, we need to label the outputs

				if ( !empty($redirection_404_count) ){
					if ( $redirection_404_count >= 999 ){ //If we reached the limit from the above query, assume there are more that weren't counted
						$redirection_404_count = '<span class="text-danger"><i class="fa-solid fa-fw fa-exclamation-triangle"></i> 1,000+</span>';
					} elseif ( $redirection_404_count >= 500 ){
						$redirection_404_count = '<span class="text-caution">' . $redirection_404_count . '</span>';
					}

					$total_label = ( $need_404_labels )? ' total' : '';
					$redirection_404_description = '<strong title="Total count is from the Redirection plugin."><a href="tools.php?page=redirection.php&sub=404s&groupby=url">' . $redirection_404_count . $total_label . '</a></strong>';
				}

				if ( !empty($nebula_404_count) ){
					$user_label = ( $need_404_labels )? ' user' : '';
					$output_404_delimiter = ( !empty($redirection_404_count) )? ', ' : ''; //If we also have Redirection 404s we need a delimiter
					$nebula_404_description = $output_404_delimiter . '<span title="This Nebula count attempts to track only human 404 views.">' . number_format($nebula_404_count) . $user_label . '</span>';
				}

				echo '<li class="essential"><i class="fa-regular fa-fw fa-file-excel"></i> 404s: ' . $redirection_404_description . $nebula_404_description . ' <small>(Last 24 hours)</small></li>';
			}

			//Check if parent theme files have been modified (this is in the developer info metabox, but also happens in the Nebula metabox)
			$modified_files = get_transient('nebula_theme_modified_files');
			if ( !empty($modified_files) ){
				$file_count = count($modified_files);

				$title_attr = implode("\n", $modified_files); //Join file names with new lines for the title attribute
				$time_ago = human_time_diff(get_transient('nebula_theme_file_changes_check'), time());
				$title_attr .= "\n\n Last checked " . $time_ago . " ago";

				echo '<li><i class="fa-solid fa-square-binary"></i> <span class="essential text-caution cursor-help" title="' . esc_attr($title_attr) . '"><strong>' . $file_count . '</strong> Parent theme ' . $this->singular_plural($file_count, 'file has', 'files have') . ' been modified</span></li>';
			}

			//Theme directory size(s)
			if ( is_child_theme() ){
				$nebula_parent_size = $this->transient('nebula_directory_size_parent_theme', function(){
					return $this->foldersize(get_template_directory());
				}, DAY_IN_SECONDS);

				$nebula_child_size = $this->transient('nebula_directory_size_child_theme', function(){
					return $this->foldersize(get_stylesheet_directory());
				}, DAY_IN_SECONDS);

				echo '<li><i class="fa-solid fa-code"></i> Parent theme directory size: <strong>' . $this->format_bytes($nebula_parent_size, 1) . '</strong> </li>';
				echo '<li><i class="fa-solid fa-code"></i> Child theme directory size: <strong>' . $this->format_bytes($nebula_child_size, 1) . '</strong> </li>';
			} else {
				$nebula_size = $this->transient('nebula_directory_size_theme', function(){
					return $this->foldersize(get_stylesheet_directory());
				}, DAY_IN_SECONDS);
				echo '<li><i class="fa-solid fa-code"></i> Theme directory size: <strong>' . $this->format_bytes($nebula_size, 1) . '</strong> </li>';
			}

			//Plugins directory size (and count)
			$plugins_size = $this->transient('nebula_directory_size_plugins', function(){
				$plugins_dir = WP_CONTENT_DIR . '/plugins';
				return $this->foldersize($plugins_dir);
			}, HOUR_IN_SECONDS*36);
			$all_plugins = $this->transient('nebula_count_plugins', function(){
				return get_plugins();
			}, WEEK_IN_SECONDS);
			$active_plugins = get_option('active_plugins', array());
			echo '<li><i class="fa-solid fa-plug"></i> Plugins directory size: <strong>' . $this->format_bytes($plugins_size, 1) . '</strong> <small>(' . count($active_plugins) . ' active of ' . count($all_plugins) . ' installed)</small></li>';

			do_action('nebula_dev_dashboard_directories');

			//Uploads directory size (and max upload size)
			$uploads_size = $this->transient('nebula_directory_size_uploads', function(){
				$upload_dir = wp_upload_dir();
				return $this->foldersize($upload_dir['basedir']);
			}, HOUR_IN_SECONDS*36);

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

				if ( !empty($disk_total_space) ){ //Ignore when this results in 0 bytes total
					$disk_space_percent_used = round((($disk_total_space-$disk_free_space)/$disk_total_space)*100);

					$disk_usage_class = '';
					if ( $disk_free_space/GB_IN_BYTES < 10 || $disk_space_percent_used > 85 ){
						$disk_usage_class = 'text-caution'; //Warning

						if ( $disk_free_space/GB_IN_BYTES < 5 || $disk_space_percent_used > 95 ){
							$disk_usage_class = 'text-danger'; //Danger
						}
					}

					echo '<li><i class="fa-solid fa-fw fa-hdd"></i> Disk Space Available: <strong class="' . $disk_usage_class . '">' . $this->format_bytes($disk_free_space, 1) . '</strong> <small class="' . $disk_usage_class . '">(Using ' . $disk_space_percent_used . '% of <strong>' . $this->format_bytes($disk_total_space) . '</strong> total)</small></li>';
				}
			}

			//WP Database Size
			echo '<li><i class="fa-solid fa-fw fa-database"></i> WP Database Size: <strong>' . $this->format_bytes($this->get_database_size()) . '</strong></li>';

			//Link to Query Monitor Environment Panel
			//if ( is_plugin_active('query-monitor/query-monitor.php') ){
				//echo '<li><i class="fa-solid fa-fw fa-table"></i> <a href="#qm-environment">Additional Server Information <small>(Query Monitor)</small></a></li>'; //Not currently possible: https://github.com/johnbillion/query-monitor/issues/622
			//}

			//Log Files
			foreach ( $this->get_log_files('all', true) as $types ){ //Always get fresh data here
				foreach ( $types as $log_file ){
					if ( !empty($log_file['bytes']) && $log_file['bytes'] > 999 ){ //Only show the file if it has a size and is at least 1kb
						echo '<li class="essential"><i class="fa-regular fa-fw fa-file-alt"></i> <a href="' . $log_file['shortpath'] . '" target="_blank"><code title="' . $log_file['shortpath'] . '" style="cursor: help;">' . $log_file['name'] . '</code></a> File: <strong>' . $this->format_bytes($log_file['bytes']) . '</strong></li>';
					}
				}
			}

			//Fatal error count
			$fatal_error_count = $this->transient('fatal_error_count', function(){
				return $this->count_fatal_errors();
			}, HOUR_IN_SECONDS);

			if ( !empty($fatal_error_count) ){
				$fatal_error_count_description = '';

				if ( intval($fatal_error_count) ){ //If the result is a number (not a string which represents a problem)
					$fatal_error_count_description = ' <small>(last 7 days)</small>';
				}

				echo '<li class="essential text-danger"><i class="fa-solid fa-fw fa-bug"></i> Fatal Errors: <strong><a class="text-danger" href="' . ini_get('error_log') . '" target="_blank">' . $fatal_error_count . '</a></strong>' . $fatal_error_count_description . '</li>'; //The <a> tag is just to show the location of the error log file
			}

			//Service Worker
			if ( $this->get_option('service_worker') ){
				if ( !is_ssl() ){
					echo '<li><i class="fa-solid fa-fw fa-microchip" class="text-danger"></i> <strong>Not</strong> using service worker. No SSL.</li>';
				} elseif ( !file_exists($this->sw_location(false)) ){
					echo '<li><i class="fa-solid fa-fw fa-microchip" class="text-danger"></i> <strong>Not</strong> using service worker. Service worker file does not exist.</li>';
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
			echo '<li class="essential"><i class="fa-regular fa-fw fa-calendar"></i> <span title="' . $latest_file['path'] . '" style="cursor: help;">Modified:</span> <span title="' . date("F j, Y", $latest_file['date']) . ' @ ' . date("g:ia", $latest_file['date']) . '" style="cursor: help;"><strong>' . human_time_diff($latest_file['date']) . ' ago</strong></span></li>';

			//SCSS last processed date
			if ( $this->get_data('scss_last_processed') ){
				$sass_option = ( $this->get_option('scss') )? '' : ' <small><em><a href="themes.php?page=nebula_options&tab=functions&option=scss">Sass is currently <strong>disabled</strong> &raquo;</a></em></small>';
				echo '<li class="essential"><i class="fa-brands fa-fw fa-sass"></i> Sass Processed: <span title="' . date("F j, Y", $this->get_data('scss_last_processed')) . ' @ ' . date("g:i:sa", $this->get_data('scss_last_processed')) . '" style="cursor: help;"><strong>' . human_time_diff($this->get_data('scss_last_processed')) . ' ago</strong></span> ' . $sass_option . '</li>';
			}

			echo '<li><i class="fa-brands fa-fw fa-wordpress"></i> <a href="site-health.php?tab=debug">WP Site Info &raquo;</a></li>'; //Link to WP Health Check Info page

			echo '<li class="expand-simplified-view essential"><a href="#">...Expand full list <i class="fa-solid fa-fw fa-caret-down"></i></a></li>';

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
			foreach ( $all_plugins as $plugin => $plugin_data ){
				$plugin_name = $plugin_data['Name'];
				$safe_plugin_name = str_replace(array(' ', '-', '/'), '_', strtolower($plugin_name));
				$inactive_indicator = ( is_plugin_active($plugin) )? '' : ' (Inactive)';
				$directory_search_options[$safe_plugin_name] = '<option value="' . $safe_plugin_name . '">' . $plugin_name . $inactive_indicator . '</option>';
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

		//at launch, update the nebula options for developer info toggle to include file size monitor as well

		//File Size Monitor Metabox
		public function file_size_monitor_metabox(){
			if ( $this->is_minimal_mode() ){return null;}
			wp_add_dashboard_widget('nebula_file_size_monitor', '<i class="fa-solid fa-fw fa-weight-scale"></i>&nbsp;File Size Monitor', array($this, 'dashboard_file_size_monitor'));
		}

		public function dashboard_file_size_monitor(){
			$this->timer('Nebula File Size Monitor Dashboard Metabox', 'start', '[Nebula] Dashboard Metaboxes');

			$files_and_groups = $this->transient('nebula_file_size_monitor_list', function(){
				$file_limit = apply_filters('nebula_file_size_monitor_limit', 1500); //Allow others to increase the limit if desired
				$file_count = 0;

				//Ignored certain files and directories
				$ignored = apply_filters('nebula_file_size_monitor_ignored', array('resources', '.github', '.gitignore', '.git', 'screenshot.png', 'acf-json', 'img/meta')); //Allow others to ignore files or directories. Note: purposefully *not* ignoring /vendor directories because they may have files that load on the front-end and should be monitored.

				//File size budgets should match /inc/budget.json for consistency
				//This list of groups can also be modified by others as desired- which means groups and extensions can be added or moved, and budgets can be changed
				$groups = apply_filters('nebula_file_size_monitor_groups', array(
					'Images' => array(
						'extensions' => array('png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'ico', 'bmp', 'avif', 'eps', 'heic', 'tif'),
						'budget' => (KB_IN_BYTES*150),
						'linkable' => true
					),
					'Videos' => array(
						'extensions' => array('mp4', 'webm', 'mov', 'avi', 'mkv', 'ogg'),
						'budget' => (MB_IN_BYTES*2),
						'linkable' => true
					),
					'Audio' => array(
						'extensions' => array('mp3', 'wav', 'ogg', 'flac', 'm4a'),
						'budget' => (MB_IN_BYTES*1),
						'linkable' => true
					),
					'Scripts' => array(
						'extensions' => array('js', 'mjs', 'ts'),
						'budget' => (KB_IN_BYTES*160),
						'linkable' => true
					),
					'Styles' => array(
						'extensions' => array('css', 'scss', 'less', 'sass'),
						'budget' => (KB_IN_BYTES*160),
						'linkable' => true
					),
					'Fonts' => array(
						'extensions' => array('woff', 'woff2', 'ttf', 'otf', 'eot'),
						'budget' => (KB_IN_BYTES*200),
						'linkable' => true
					),
					'Documents' => array(
						'extensions' => array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt'),
						'budget' => (MB_IN_BYTES*10),
						'linkable' => true
					),
					'Data' => array(
						'extensions' => array('json', 'xml', 'csv', 'tsv', 'yml', 'yaml', 'ics', 'vcf'),
						'budget' => (MB_IN_BYTES*10),
						'linkable' => true
					),
					'Logs' => array(
						'extensions' => array('log', 'error_log'),
						'budget' => (MB_IN_BYTES*25),
						'linkable' => false
					),
					'Templating' => array('extensions' => array('php', 'html', 'htm'), 'linkable' => false),
					'Text' => array('extensions' => array('txt', 'md'), 'linkable' => true),
					'Localization' => array('extensions' => array('mo', 'po', 'pot'), 'linkable' => false),
					'Config' => array('extensions' => array('htaccess', 'env', 'ini', 'conf'), 'linkable' => false),
					'Archives' => array('extensions' => array('zip', 'tar', 'gz'), 'linkable' => false), //Not allowing links for archive files
					'Other' => array('extensions' => array(), 'linkable' => false)
				));



				//Default theme directory to scan
				$directory = get_template_directory();
				if ( is_child_theme() ){
					$directory = get_stylesheet_directory();
				}

				$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS));
				$files = array();

				foreach ( $rii as $file ){
					//Skip directories themselves
					if ( $file->isDir() ){
						continue;
					}

					//If we hit the limit, stop scanning files
					if ( $file_count >= $file_limit ){
						break;
					}

					$path = $file->getPathname();

					$relative_path = str_replace($directory, '', $path);
					$relative_path = ltrim(str_replace('\\', '/', $relative_path), '/');

					//Skip ignored files and directories
					foreach( $ignored as $ignore ){
						if ( str_contains($relative_path, $ignore) ){
							continue 2;
						}
					}

					$files[] = $this->get_file_info($path, $groups);
					$file_count++;
				}

				$all_log_files = $this->get_log_files(); //Get all of the log files Nebula detects

				//Normalize the log files into a simple array of filepath strings
				$normalized_additional_files = array();
				foreach( $all_log_files as $category => $log_files ){
					foreach( $log_files as $log_file ){
						$normalized_additional_files[] = $log_file['path'];
					}
				}

				$initial_theme_files_count = count($files);
				$all_additional_files = apply_filters('nebula_file_size_monitor', $normalized_additional_files); //Allow others to monitor files outside the child theme

				foreach ( $all_additional_files as $filepath ){
					//If we hit the limit, stop scanning files
					if ( $file_count >= $file_limit ){
						break;
					}

					if ( file_exists($filepath) ){
						//Skip files that are already monitored
						if( in_array($filepath, array_column($files, 'path')) ){
							continue;
						}

						$files[] = $this->get_file_info($filepath, $groups);
						$file_count++;
					}
				}

				usort($files, fn($a,$b)=>$b['size']-$a['size']);

				return array(
					'groups' => $groups,
					'files' => $files,
					'limit' => $file_limit,
					'scanned' => $file_count,
					'requested' => $initial_theme_files_count+count($all_additional_files),
					'timestamp' => time(),
				);
			}, HOUR_IN_SECONDS*4);

			$groups = $files_and_groups['groups'];
			$files = $files_and_groups['files'];

			$scan_date = 'at ' . date('g:ia', $files_and_groups['timestamp']);
			if ( date('Y-m-d', $files_and_groups['timestamp']) != date('Y-m-d') ){
				$scan_date = 'on ' . date('F j, Y', $files_and_groups['timestamp']);
			}

			$types = array_unique(array_column($files, 'type'));
			sort($types);
			$used_groups = array_unique(array_column($files, 'group'));
			sort($used_groups);

			//Check if specific directories were included in the file size monitor
			$has_uploads = false;
			$has_plugins = false;

			foreach ( $files as $file ){
				if ( !isset($file['path']) ){
					continue;
				}

				if ( !$has_uploads && str_contains($file['path'], '/uploads/') ){
					$has_uploads = true;
				}

				if ( !$has_plugins && str_contains($file['path'], '/plugins/') ){
					$has_plugins = true;
				}

				//Exit early if both are found
				if ( $has_uploads && $has_plugins ){
					break;
				}
			}

			echo '<p>This monitors theme files and standard log locations as well as <a href="//nebula.gearside.com/examples/file-size-monitor-dashboard-metabox/?utm_campaign=documentation&utm_medium=dashboard&utm_source=' . urlencode(site_url()) . '&utm_content=file_size_monitor_adding#adding" target="_blank" rel="noopener noreferrer">any files manually added</a>.</p>'; //@todo: link to nebula documentation for examples of how to add files to the monitor. show an example of how to add individual files as well as an example of how to add entire directories of files

			//Show a warning if scanning a high amount of files
			if ( $files_and_groups['scanned'] >= $files_and_groups['limit'] ){
				echo '<p class="high-file-count"><strong><i class="fa-solid fa-circle-exclamation"></i> Scan limit reached: only the first ' . number_format($files_and_groups['limit']) . ' files (' . number_format(($files_and_groups['limit']/$files_and_groups['requested'])*100, 1) . '%) were scanned.</strong> ' . number_format($files_and_groups['requested']) . ' files were added, but ' . number_format($files_and_groups['requested']-$files_and_groups['scanned']) . ' files were not scanned. This limit helps maintain performance. <a href="//nebula.gearside.com/examples/file-size-monitor-dashboard-metabox/?utm_campaign=documentation&utm_medium=dashboard&utm_source=' . urlencode(site_url()) . '&utm_content=file_size_monitor_limit#limit" target="_blank" rel="noopener noreferrer">This limit can be increased, but use caution.</a></p>';
			} elseif ( $files_and_groups['scanned'] >= ($files_and_groups['limit']*0.75) ){
				echo '<p class="high-file-count"><strong><i class="fa-solid fa-circle-exclamation"></i> You are currently monitoring ' . number_format(count($files)) . ' files</strong>, which is approaching the scan limit for performance reasons. <a href="//nebula.gearside.com/examples/file-size-monitor-dashboard-metabox/?utm_campaign=documentation&utm_medium=dashboard&utm_source=' . urlencode(site_url()) . '&utm_content=file_size_monitor_limit#limit" target="_blank" rel="noopener noreferrer">This limit can be increased, but please use caution.</a></p>';
			}

			//Output filter dropdowns
			$default_group = str_replace(' ', '', strtolower(apply_filters('nebula_file_size_monitor_default_selection', 'largest')));

			echo '<div class="filter-row">';
				//File Groups dropdown
				echo '<label for="filegroup-filter"><i class="fa-solid fa-fw fa-filter"></i> Filter: </label>';
				echo '<select id="filegroup-filter" class="initial-state">';
				echo '<option value="" ' . ( ( empty($default_group) || $default_group == 'all' || $default_group == 'allgroups' )? 'selected data-default="true"' : '' ) . '>All File Groups</option>';

				$priority_options = array(
					'largest' => 'Largest Files',
					'overbudget' => 'Over Budget',
					'nearbudget' => 'Approaching Budget',
					'recent' => 'Recently Modified',
					'security' => 'Security Concerns'
				);
				echo '<optgroup label="Priority">';
				foreach ( $priority_options as $value => $label ){
					$selected = ( $default_group == $value || $default_group == str_replace(' ', '', strtolower($label)) )? ' selected data-default="true"' : ''; //If the default selected matches this option
					echo '<option value="' . esc_attr($value) . '"' . $selected . '>' . esc_html($label) . '</option>';
				}
				echo '</optgroup>';

				echo '<optgroup label="File Groups">';
					foreach ( $used_groups as $group ){
						$selected = ( $default_group == str_replace(' ', '', strtolower($group)) )? ' selected data-default="true"' : ''; //If the default selected matches this option
						echo '<option value="' . esc_attr($group) . '" ' . $selected . '>' . esc_html($group) . '</option>';
					}
				echo '</optgroup>';
				echo '</select>';

				//File Type dropdown
				echo '<label class="sr-only" for="filetype-filter">File Type: </label>';
				echo '<select id="filetype-filter">';
				echo '<option value="" selected data-default="true">All File Types</option>';
				foreach ( $types as $type ){
					echo ( !empty($type) )? '<option value="' . esc_attr($type) . '">' . esc_html($type) . '</option>' : '';
				}
				echo '</select>';
			echo '</div>';

			echo '<div class="filter-row">';
				//Keyword search input
				echo '<label for="filekeyword-filter"><i class="fa-solid fa-fw fa-search"></i></label>';
				echo '<input id="filekeyword-filter" type="text" placeholder="Filter" />';

				//Try to limit the option text of these to 16-17 characters
				//Other naming ideas: Quick Searches, Search Presets, Quick Presets
				echo '<select id="keyword-helpers">
					<option selected default value="" disabled>Pre-Made Filters</option>

					<optgroup label="Contents">
						<option value="accessibility">Accessibility</option>
						<option value="concern-code-quality">Code Quality</option>
						<option value="contains-lorem">Contains Lorem</option>
						<option value="debug-output">Debug Output</option>
						<option value="contains-fatal">Fatal Errors</option>
						<option value="non-ascii">Non-ASCII Chars</option>
						<option value="space-indentation">Space Indents</option>
						<option value="speed-optimization">Speed Optimization</option>
						<option value="tech-debt">Tech Debt</option>
						<option value="concern-ux">User Experience</option>
					</optgroup>

					<optgroup label="Filesystem">
						<option value="empty-file">Empty Files</option>
						<option value="no-extension">No Extensions</option>
						<option value="old-file">Old Files</option>
						<option value="stale-log">Stale Logs</option>';

						if ( $has_plugins || $has_uploads ){
							echo '<option value="/themes/">Theme</option>';
						}

						if ( $has_plugins ){ //If the /plugins/ directory is included in any files monitored
							echo '<option value="/plugins/">Plugins</option>';
						}

						if ( $has_uploads ){ //If the /uploads/ directory is included in any files monitored
							echo '<option value="/uploads/">Uploads</option>';
						}
					echo '</optgroup>

					<optgroup label="Security">
						<option value="file-permissions">Bad Permissions</option>
						<option value="concern-filename">Filename Concerns</option>
						<option value="deprecated-function">Deprecations</option>
						<!-- <option value="remote-include">Remote Includes</option> -->
						<option value="suspicious-string">Suspicious Strings</option>
					</optgroup>
				</select>';

				echo '<a class="clear-keywords transparent" href="#"><i class="fa-solid fa-times"></i> Clear</a>';
			echo '</div>';

			//Output the table
			echo '<div class="table-wrapper ' . $this->get_simplify_dashboard_class() . '"><table>';
			echo '<thead><tr><th class="file-name">File Name</th><th class="file-group">Group</th><th class="file-size">Size<i class="fa-solid fa-caret-down"></i></th><th class="budget-percent hidden">% Budget</th><th class="hidden">Keywords</th></tr></thead>';
			echo '<tbody>';
			foreach ( $files as $index => $file ){
				//Row Classes
				$row_class = '';
				if ( $file['size'] == 0 ){
					$row_class .= ' empty-file';
				} elseif ( $file['size'] < KB_IN_BYTES ){
					$row_class .= ' tiny-file';
				}

				//Check size thresholds for large-file and huge-file classes
				if ( isset($file['budget']) && $file['budget'] > 0 ){
					if ( $file['size'] > $file['budget'] ){
						$row_class .= ' overbudget-file';

						if ( $file['size'] > ($file['budget']*2) ){
							$row_class .= ' overbudget double-budget-file';
						}
					} else if ( $file['size'] >= ($file['budget']*0.75) ){ //Files approaching the budget, but not yet over
						$row_class .= ' approaching-budget';
					}
				}

				//Icons
				$file_icon = '';
				if ( !empty($file['notes']) ){
					if ( str_contains($file['notes'], 'recently-modified') ){
						$file_icon .= '<i class="note-icon fa-regular fa-clock recently-modified" title="This file has been recently modified"></i>';
					}

					if ( str_contains($file['notes'], 'accessibility') ){
						$file_icon .= '<i class="note-icon fa-solid fa-universal-access a11y-issue-icon" title="This file has potential accessibility issues" style="color: #1C4F90;"></i>';
					}

					if ( str_contains($file['notes'], 'contains-todo') ){
						$file_icon .= '<i class="note-icon fa-regular fa-note-sticky contains-todo" title="This file contains an @todo comment"></i>';
					}

					if ( str_contains($file['notes'], 'stale-log') ){
						$file_icon .= '<i class="note-icon fa-solid fa-ghost stale-log" title="Stale log: no recent entries"></i>';
					}

					if ( str_contains($file['notes'], 'non-ascii-characters') ){
						$file_icon .= '<i class="note-icon fa-regular fa-keyboard non-ascii-characters" title="This file contains non-ascii characters"></i>';
					}

					if ( str_contains($file['notes'], 'contains-debug-output') ){
						$file_icon .= '<i class="note-icon fa-solid fa-wrench contains-debug-output" title="This file contains debug output!"></i>';
					}

					if ( str_contains($file['notes'], 'contains-fatal') ){
						$file_icon .= '<i class="note-icon fa-solid fa-skull-crossbones fatal-error" title="This log contains a fatal error entry"></i>';
					}

					if ( str_contains($file['notes'], 'security-concern') ){
						$file_icon .= '<i class="note-icon fa-solid fa-unlock security-concern" title="This file contains a security concern! ' . sanitize_html_class($file['notes']) . '"></i>';
					}
				}

				//Additional Info
				$additional_info = '';
				if ( $file['type'] == 'log' && !empty($file['lines']) ){
					$additional_info = ' <small class="entries line-count">(' . number_format($file['lines']) . ' entries)</small>';
				}

				//Links
				$file_link = '';
				if ( !empty($file['linkable']) && $file['size'] <= (MB_IN_BYTES*25) ){ //If the $file['linkable'] and the filesize is less than 25mb
					$file_uri = str_replace(ABSPATH, site_url('/'), $file['path']);
					$file_link = ' <a class="file-link" href="' . esc_url($file_uri) . '" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-fw fa-up-right-from-square"></i></a>';
				}

				//Budget
				$budget_percent = '';
				$budget_description = '';
				if ( !empty($file['budget']) ){
					$budget_percent = number_format(($file['size']*100)/$file['budget'], 1) . '%';
					$budget_description = $budget_percent . ' of ' . $this->format_bytes($file['budget']) . ' budget';
				}

				echo '<tr class="' . trim($row_class) . '" data-type="' . esc_attr($file['type']) . '" data-group="' . esc_attr($file['group']) . '" data-budget="' . esc_attr($this->format_bytes($file['budget'])) . '">';
				echo '<td class="file-name">' . ' <small>' . ($index+1) . '.</small> <span class="file-icons-group">' . $file_icon . '</span> <span title="' . esc_attr($file['path']) . '">' . esc_html($file['name']) . '</span>' . $additional_info . $file_link . '<small class="modified-info hidden"><br />(Modified ' . human_time_diff($file['modified'], time()) . ' ago)</small><small class="file-keywords hidden"><br /><i class="fa-solid fa-fw fa-turn-up fa-rotate-90"></i> ' . $file['group'] . ' ' . $file['notes'] . '</small></td>';
				echo '<td class="file-group">' . esc_html($file['group']) . '</td>';
				echo '<td class="file-size" data-file-size="' . $file['size'] . '" title="' . $budget_description . '">' . $this->format_bytes($file['size']) . '</td>';
				echo '<td class="budget-percent hidden">' . $budget_percent . '</td>';
				echo '<td class="file-path hidden">' . $file['path'] . '</td>';
				echo '</tr>';
			}
			echo '</tbody></table><div class="no-files-message hidden">No files match the selected criteria. <a class="reset-filters" href="#">Reset?</a></div></div>';

			echo '<table class="table-footer hidden"><tfoot><tr>
				<td>Total: <span class="total-file-size"></span></td>
				<td>Avg: <strong class="average-file-size"></strong></td>
				<td>Med: <strong class="median-file-size"></strong></td>
			</tr></tfoot></table>';

			echo '<div class="totals-row"><small>Showing <span class="total-showing">All</span> of ' . number_format(count($files)) . ' monitored files <small class="relative-date-tooltip" data-date="' . $files_and_groups['timestamp'] . '">(' . $scan_date . ')</small>. <a class="monitor-re-scan" href="' . admin_url('?clear-transients') . '">Re-Scan?</a></small></div>';
			echo '<p class="budget-description hidden">The budget for <strong class="filetype">These</strong> is <strong class="sizebudget">non-applicable</strong>. <a class="show-optimization-tips" href="#">Show Tips <i class="fa-solid fa-caret-down"></i></a></p>';
			?>
				<div id="nebula-optimization-tips" style="display: none;">
					<ul>
						<li class="tip hidden" data-group="images">Reduce image sizes to only the necessary dimensions. For hero/background images, consider limiting the dimensions and then scaling up using CSS.</li>
						<li class="tip hidden" data-group="images">Use appropriate image formats! WEBP is <em>usually</em> better than PNG and often better than JPG. SVG is typically great, but not if the vector has a lot of vertices.</li>
						<li class="tip hidden" data-group="images">Use a liberal amount of compression when saving.</li>
						<li class="tip hidden" data-group="images">Save JPG images as "Progressive".</li>
						<li class="tip hidden" data-group="images">Only use a batch optimizer <strong>after</strong> previous steps are completed! Bulk optimization tools will not resize images themselves.</li>
						<li class="tip hidden" data-group="images">Use native lazy loading (<code>loading="lazy"</code>) on most images!</li>

						<li class="tip hidden" data-group="fonts">Choose WOFF and WOFF2 formats when possible. TTF formats are not optimized for web usage.</li>
						<li class="tip hidden" data-group="fonts">Limit font weights to only what is absolutely necessary. <strong>Strongly consider using a variable font!</strong></li>
						<li class="tip hidden" data-group="fonts">Consider font display swap so the user can begin reading content while the page continues to load.</li>
						<li class="tip hidden" data-group="fonts">Determine if locally hosting font files is advantageous.</li>
						<li class="tip hidden" data-group="fonts">For icon font libraries, consider creating a kit that contains only the used icons.</li>

						<li class="tip hidden" data-group="styles">CSS files often block rendering, so scrutinize which files are absolutely necessary.</li>
						<li class="tip hidden" data-group="styles">For CSS from plugins, use advanced Nebula Options to conditionally deregister on unnecessary pages.</li>
						<li class="tip hidden" data-group="styles">CSS can be lazy loaded by adding the link element to the DOM with JavaScript. For features that are not seen immediately, consider using this technique.</li>
						<li class="tip hidden" data-group="styles">Minify CSS files when feasible. Preprocessors like Sass should automate this. If you are not using Nebula's built-in Sass preprocessor, strongly consider it.</li>

						<li class="tip hidden" data-group="scripts">JavaScript must be processed, so large JS files are much worse than other formats of the same size!</li>
						<li class="tip hidden" data-group="scripts">Use JS modules and conditionally/dynamically import only necessary files/functions.</li>
						<li class="tip hidden" data-group="scripts">Defer and async JavaScript files</li>
						<li class="tip hidden" data-group="scripts">Minify JavaScript when feasible.</li>

						<li class="tip hidden" data-group="videos">Videos should be heavily compressed.</li>
						<li class="tip hidden" data-group="videos">The WEBM format is designed for web use. MP4 files also optimize well.</li>
						<li class="tip hidden" data-group="videos">Show a poster image or façade until the window has loaded, then start loading the video itself.</li>
						<li class="tip hidden" data-group="videos">For decorative/background videos, be realistic with the duration.</li>
						<li class="tip hidden" data-group="videos">For content videos, consider hosting on a streaming service. However, that may require other unoptimized resources.</li>
						<li class="tip hidden" data-group="videos">Consider if videos are necessary for mobile at all; strongly consider avoiding them.</li>

						<li class="tip hidden" data-group="logs">On production websites, scrutinize which log files are necessary to be enabled.</li>
						<li class="tip hidden" data-group="logs">Regularly review log files to note corrective actions, and delete these before they get too large.</li>
						<li class="tip hidden" data-group="logs">Consider using a log rotation tool to cap filesizes so they don't get out of control.</li>

						<!-- <li class="tip general">Pay close attention to the overall file size footprint of each page.</li>
						<li class="tip general">Ensure caching and compression is enabled in .htaccess! Consider using the provided Nebula resource.</li>
						<li class="tip general">Files added to the WordPress Media Library in the /uploads/ directory often bypass developer review- regularly check for large files.</li> -->
					</ul>
				</div>
			<?php

			$this->timer('Nebula File Size Monitor Dashboard Metabox', 'end');
		}

		public function get_file_info($filepath, $groups, $directory=null){
			$file_size_content_scan_limit = KB_IN_BYTES*300; //The file size limit for reading the contents of files. It will read only the first X bytes of the file.
			$extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION) ?: '');
			$group = 'Other';

			foreach ( $groups as $label => $info ){
				if ( in_array($extension, $info['extensions']) ){
					$group = $label;
					break;
				}
			}

			$file_info = array(
				'name' => basename($filepath),
				'path' => $filepath,
				'type' => $extension,
				'group' => $group,
				'linkable' => $groups[$group]['linkable'] ?? false,
				'size' => filesize($filepath),
				'budget' => $groups[$group]['budget'] ?? 0,
				'modified' => filemtime($filepath)
			);

			$notes = array();

			//Monitor file sizes
			if ( filesize($filepath) > MB_IN_BYTES ){
				$notes[] = 'large-file-size';
			} elseif ( filesize($filepath) < (KB_IN_BYTES*10) ){
				$notes[] = 'tiny-file-size';
				$notes[] = 'trivial-file-size';
			} elseif ( filesize($filepath) === 0 ){
				$notes[] = 'empty-file-size';
				$notes[] = 'zero-file-size';
			}

			if ( $group === 'Logs' || $extension === 'log' || basename($filepath) === 'error_log' ){
				//Include entry counts (lines) for log files (when they aren't too large).
				$file_info['lines'] = 0;
				if ( filesize($filepath) <= MB_IN_BYTES*5 ){
					$file_info['lines'] = count(file($filepath, FILE_SKIP_EMPTY_LINES)); //This could be updated to loop through each line in the future which will support larger file sizes (which is how the Dev Info fatal error counter works).
				}

				//Check log files for fatal errors
				$contents = file_get_contents($filepath, false, null, 0, $file_size_content_scan_limit);
				if ( str_contains($contents, 'fatal') ){
					$notes[] = 'contains-fatal';
				}

				//Check log files that have *not* been updated in a very long time
				if ( filemtime($filepath) < time()-(MONTH_IN_SECONDS*6) ){
					$notes[] = 'stale-log';
				}
			}

			if ( in_array($group, array('Templating', 'Styles', 'Scripts')) ){
				$contents = file_get_contents($filepath, false, null, 0, $file_size_content_scan_limit);
				$contents = strtolower($contents);

				//Check for @todo comments and other potential tech debt
				$tech_debt_strings = array('@todo', 'fixme', 'style=', 'onclick=');
				foreach ( $tech_debt_strings as $tech_debt_string ){
					if ( str_contains($contents, $tech_debt_string) ){
						$notes[] = 'potential-tech-debt';
						$notes[] = 'contains-' . str_replace(array('@', '='), '', $tech_debt_string);
						break;
					}
				}

				//Check for spaces instead of tabs
				if ( str_contains($contents, '    ') ){
					$notes[] = 'contains-space-indentation';
				}

				//Check for debug output
				$debug_strings = array('var_dump', 'print_r', 'alert(', 'console.log('); //Ignore in-progress string check
				foreach ( $debug_strings as $debug_string ){
					if ( str_contains($contents, $debug_string) ){
						$notes[] = 'contains-debug-output';
						break;
					}
				}

				//Code Quality
				if ( preg_match('/(["\'])(?:\\\\\1|.)*?\1\s*[\.\+]\s*(["\'])(?:\\\\\2|.)*?\2/', $contents) ){ //Check for concatenation of two strings
					$notes[] = 'concern-code-quality';
					$notes[] = 'concern-concatenation';
				}
				if ( preg_match('/^\s*[^#\/\n]*\?.*?\?.*?:.*?:.*$/m', $contents) ){ //Nested ternary operators
					$notes[] = 'concern-code-quality';
					$notes[] = 'concern-nested-ternary';
				}
				if ( str_contains($contents, '.ajax(') ){ //jQuery AJAX (instead of JS Fetch)
					$notes[] = 'concern-code-quality contains-ajax';
					$notes[] = 'consider-alternatives';
				}
			}

			$notes[] = 'last-modified-' . date('Y-m-d', filemtime($filepath)); //Add the last modified date so it can be searched

			$file_modified_age = time()-filemtime($filepath);
			if ( $file_modified_age < DAY_IN_SECONDS*7 ){
				$notes[] = 'recently-modified';
				$notes[] = 'recent-file';
			} elseif ( $file_modified_age > YEAR_IN_SECONDS ){
				$notes[] = 'old-file';

				if ( $file_modified_age > YEAR_IN_SECONDS*10 ){ //Older than 10 years
					$notes[] = 'ten-year-old-file';
					$notes[] = 'decade-old-file';
					$notes[] = 'ancient-file';
				} elseif ( $file_modified_age > YEAR_IN_SECONDS*5 ){ //Older than 5 years
					$notes[] = 'five-year-old-file';
				} elseif ( $file_modified_age > YEAR_IN_SECONDS*2 ){ //Older than 2 years
					$notes[] = 'two-year-old-file';
				}
			}

			//Check for files with no extension
			if ( pathinfo($filepath, PATHINFO_EXTENSION) === '' ){
				$notes[] = 'no-extension';
			}

			if ( in_array($group, array('Templating', 'Text', 'Config', 'Other')) ){
				$contents = file_get_contents($filepath, false, null, 0, $file_size_content_scan_limit);
				$contents = strtolower($contents);

				//Check for placeholder text
				if ( str_contains($contents, 'lorem ipsum') ){
					$notes[] = 'contains-lorem-ipsum';
					$notes[] = 'contains-placeholder-text';
				}

				//Check templating files for accessibility
				if ( $group === 'Templating' ){
					//Check for placeholder alt attributes
					if ( str_contains($contents, 'alt="#"') ){
						$notes[] = 'accessibility';
						$notes[] = 'a11y';
						$notes[] = 'placeholder-alt-attribute';
					}

					//Check forms for labels
					if ( preg_match('/<form\b[^>]*>.*?(?!<label\b).*?<\/form>/is', $contents) ){
						$notes[] = 'accessibility';
						$notes[] = 'concern-missing-label';
					}

					//Check for blocking script tags without async/defer
					if ( str_contains($contents, '<script') && preg_match('/<script[^>]+src=["\'][^"\']+\.js["\'](?![^>]*\b(async|defer)\b)(?![^>]*type=["\']module["\'])[^>]*>$/im', $contents) ){
						$notes[] = 'concern-speed-optimization';
						$notes[] = 'concern-blocking-js';
					}

					//If the file contains img tags
					if ( str_contains($contents, '<img') ){
						//Check for missing lazy loading
						if ( preg_match('/<img\b(?![^>]*\bloading=["\']lazy["\'])[^>]*\/>/i', $contents) ){
							$notes[] = 'concern-speed-optimization';
							$notes[] = 'concern-no-lazy-load';
						}

						//Best-effort check for <img> elements with missing alt attributes (note: it may be acceptable if the element contains a role or is aria-hidden)
						if ( preg_match('/<img\b(?![^>]*\balt=)[^>\n]*\/>/i', $contents) ){ //Check <img> elements that are missing alt until a newline limit
							$notes[] = 'accessibility';
							$notes[] = 'a11y';
							$notes[] = 'missing-alt-attribute';
						}
					}
				}

				//Check for non-ASCII characters
				if ( preg_match('/[^\x00-\x7F]/', $contents) ){
					$notes[] = 'non-ascii-characters';
				}

				//Check for disabling cache
				if ( str_contains($contents, 'Header unset Cache-Control') || str_contains($contents, 'max-age=0') ){ //Check if we are disabling the cache
					$notes[] = 'concern-ux';
					$notes[] = 'concern-optimization';
				}

				//Check for deprecated functionality
				$deprecated_functions = array('mysql_connect(', 'ereg(', 'ga(', '_gaq.');
				foreach ( $deprecated_functions as $deprecated_function ){
					if ( str_contains($contents, $deprecated_function) ){
						$notes[] = 'security-concern';
						$notes[] = 'uses-deprecated-function';
						$notes[] = 'concern-deprecated-function';
						break;
					}
				}

				//Check if the file has dangerous permissions
				$file_permissions = fileperms($filepath) & 0777;
				if ( $file_permissions === 0777 || $file_permissions === 0666 ){
					$notes[] = 'security-concern';
					$notes[] = 'concern-file-permissions';
				}

				//Check for security concerns (do these last!)

				//Check for remote includes
				if ( preg_match('/\b(include|require)(_once)?\([\'"]http/', $contents) ){
					$notes[] = 'security-concern';
					$notes[] = 'concern-remote-include';
				}

				//Check file names for suspicious names/extensions
				$suspicious_names = apply_filters('nebula_suspicious_file_names', array('phpinfo', 'wp-config', '.exe', 'shell', 'swf', '.dll')); //Nebula flags questionable or unsafe development practices, but does not include signature-based detection. It is recommended to use dedicated security tools for that purpose.
				foreach ( $suspicious_names as $suspicious_name ){
					if ( str_contains(strtolower($filepath), $suspicious_name) ){
						$notes[] = 'security-concern';
						$notes[] = 'concern-filename';
						break;
					}
				}

				//Check within files for suspicious strings
				$suspicious_strings = array('eval(base64_decode', 'gzuncompress(', 'create_function(', 'shell_exec(', ' exec(', 'passthru(', 'popen(', 'proc_open(', 'phpinfo(', 'wp_create_user(');
				foreach ( $suspicious_strings as $suspicious_string ){
					if ( str_contains($contents, $suspicious_string) ){
						$notes[] = 'security-concern';
						$notes[] = 'concern-suspicious-string';
						$notes[] = 'concern-' . preg_replace('/[^a-z0-9\-]/', '', strtolower($suspicious_string));
						break;
					}
				}
			}

			$all_notes = apply_filters('nebula_file_size_monitor_notes', $notes, $file_info); //Allow others to check files for additional notes
			$file_info['notes'] = implode(' ', array_unique($all_notes));

			return $file_info;
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

			$directory ??= get_template_directory();
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
			foreach ( $all_plugins as $plugin => $plugin_data ){
				$plugin_name = $plugin_data['Name'];
				$safe_plugin_name = str_replace(array(' ', '-', '/'), '_', strtolower($plugin_name));
				$plugin_folder = explode('/', $plugin);
				$search_directories[$safe_plugin_name] = WP_PLUGIN_DIR . '/' . $plugin_folder[0];
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
					if ( str_contains(basename($file), $searchTerm) ){
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
			if ( $this->is_minimal_mode() ){return null;}
			wp_add_dashboard_widget('performance_metabox', '<i id="performance-status-icon" class="fa-solid fa-fw fa-stopwatch"></i> <span id="performance-title">&nbsp;Performance</span>', array($this, 'performance_timing'));
		}

		public function performance_timing(){
			$this->timer('Nebula Performance Dashboard Metabox', 'start', '[Nebula] Dashboard Metaboxes');

			//Prep for an iframe timer if needed
			$home_url = ( is_ssl() )? str_replace('http://', 'https://', home_url('/')) : home_url('/'); //Sometimes the home_url() still has http even when is_ssl() true
			echo '<div id="testloadcon" data-src="' . $home_url . '" style="pointer-events: none; opacity: 0; visibility: hidden; display: none;"></div>'; //For iframe timing

			echo '<img id="performance-screenshot" class="hidden" src="#" />';
			echo '<ul id="nebula-performance-metrics" class="nebula-fa-ul ' . $this->get_simplify_dashboard_class() . '">';

			//Sub-status
			echo '<li id="performance-sub-status" class="essential"><i class="fa-regular fa-fw fa-comment"></i> <span class="label">Status</span>: <strong>Preparing test...</strong></li>';
			echo '<li id="performance-sub-reason" class="essential hidden"><i class="fa-regular fa-fw fa-note-sticky"></i> <span class="label"></span></li>';

			echo '<li class="insert-here hidden" style="display: none;"></li>';
			echo '<li class="expand-simplified-view essential hidden" style="display: none;"><a href="#">...Expand full list <i class="fa-solid fa-fw fa-caret-down"></i></a></li>';

			//PHP-Measured Server Load Time (TTFB)
			echo '<li id="performance-ttfb"><i class="fa-regular fa-fw fa-clock"></i> <span class="essential label">PHP Response Time</span>: <strong class="datapoint nebula-ttfb-time" title="Calculated via PHP render time">~' . timer_stop(0, 3) . '</strong> <strong>seconds</strong></li>';
			echo '</ul>';

			echo '<p><small><a href="https://web.dev/lighthouse-performance/" target="_blank" rel="noopener noreferrer">Learn about user-centric performance metrics &raquo;</a></small></p>';
			$this->timer('Nebula Performance Dashboard Metabox', 'end');
		}

		//Add a dashboard metabox for design reference
		public function design_metabox(){
			if ( $this->is_minimal_mode() ){return null;}
			global $wp_meta_boxes;
			wp_add_dashboard_widget('nebula_design', '<i class="fa-solid fa-fw fa-palette"></i> &nbsp;Design Reference', array($this, 'dashboard_nebula_design'));
		}

		public function dashboard_nebula_design(){
			$this->timer('Nebula Design Dashboard Metabox', 'start', '[Nebula] Dashboard Metaboxes');
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
			if ( $this->is_minimal_mode() ){return null;}

			if ( $this->get_option('github_url') && $this->get_option('github_pat') ){
				$repo_name = str_replace('https://github.com/', '', $this->get_option('github_url'));
				global $wp_meta_boxes;
				wp_add_dashboard_widget('nebula_github', '<i class="fa-brands fa-fw fa-github"></i>&nbsp;' . $repo_name, array($this, 'dashboard_nebula_github'));
			}
		}

		public function dashboard_nebula_github(){
			$this->timer('Nebula Companion GitHub Dashboard', 'start', '[Nebula] Dashboard Metaboxes');
			echo '<p><a href="' . $this->get_option('github_url', '') . '" target="_blank">GitHub Repository &raquo;</a></p>';

			$repo_name = str_replace('https://github.com/', '', $this->get_option('github_url', ''));
			$github_personal_access_token = $this->get_option('github_pat', '');

			//Commits
			$github_commit_json = get_transient('nebula_github_commits');
			if ( empty($github_commit_json) || $this->is_debug() ){
				$commits_response = $this->remote_get('https://api.github.com/repos/' . $repo_name . '/commits', array(
					'headers' => array(
						'Authorization' => 'token ' . $github_personal_access_token,
					)
				));

				if ( is_wp_error($commits_response) ){
					echo '<p>There was an error retrieving the GitHub commits...</p>';
					return null;
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
						<a href="<?php echo $this->get_option('github_url', ''); ?>/commits/main" target="_blank">Commits &raquo;</a><br />
						<a href="<?php echo $this->get_option('github_url', ''); ?>/issues?q=is%3Aissue+is%3Aopen+sort%3Aupdated-desc" target="_blank">Issues &raquo;</a><br />
					</p>
				<?php
				return null;
			}

			echo '<div class="nebula-metabox-row"><div class="nebula-metabox-col">';
			echo '<strong>Latest Commits</strong><br />';

			//https://developer.github.com/v3/repos/commits/
			for ( $i=0; $i <= 2; $i++ ){ //Get 3 commits
				$commit_date_time = strtotime($commits[$i]->commit->committer->date);
				$commit_date_icon = ( date('Y-m-d', $commit_date_time) === date('Y-m-d') )? 'fa-clock' : 'fa-calendar';
				echo '<p>
					<i class="fa-regular fa-fw ' . $commit_date_icon . '"></i> <a href="' . $commits[$i]->html_url . '" target="_blank" title="' . date('F j, Y @ g:ia', $commit_date_time) . '">' . human_time_diff($commit_date_time) . ' ago</a><br />
					<small style="display: block;">' . $this->excerpt(array('text' => $commits[$i]->commit->message, 'words' => 15, 'ellipsis' => true, 'more' => false)) . '</small>
				</p>';
			}

			echo '<p><small><a href="' . $this->get_option('github_url', '') . '/commits/main" target="_blank">View all commits &raquo;</a></small></p>';
			echo '</div>';

			//Issues and Discussions
			echo '<div class="nebula-metabox-col">';
			echo '<strong>Recent Issues, Pull Requests, &amp; Discussions</strong><br />';

			$github_combined_posts = get_transient('nebula_github_posts');
			if ( empty($github_combined_posts) || $this->is_debug() ){
				//Get the Issues first https://developer.github.com/v3/issues/
				//Note: The Issues endpoint also returns pull requests (which is fine because we want that)
				$issues_response = $this->remote_get('https://api.github.com/repos/' . $repo_name . '/issues?state=open&sort=updated&direction=desc&per_page=3', array(
					'headers' => array(
						'Authorization' => 'token ' . $github_personal_access_token,
					)
				));

				if ( is_wp_error($issues_response) ){
					echo '<p>There was an error retrieving the GitHub issues...</p>';
					return null;
				}

				$github_issues_json = json_decode($issues_response['body']);

				//Get the Discussions next
				//GraphQL API is available, but webhooks not ready yet per (Feb 2021): https://github.com/github/feedback/discussions/43
				// $discussions_response = $this->remote_get('https://api.github.com/repos/' . $repo_name . '/discussions?sort=updated&direction=desc&per_page=3', array(
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
					if ( str_contains($github_combined_posts[$i]->html_url, 'issue') ){
						$github_post_type = 'Issue';
					} elseif ( str_contains($github_combined_posts[$i]->html_url, 'pull') ){
						$github_post_type = 'Pull Request';
					} elseif ( str_contains($github_combined_posts[$i]->html_url, 'discussion') ){
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

			echo '<p><small>View all <a href="' . $this->get_option('github_url', '') . '/issues?q=is%3Aissue+is%3Aopen+sort%3Aupdated-desc" target="_blank">issues</a>, <a href="' . $this->get_option('github_url', '') . '/pulls?q=is%3Apr+is%3Aopen+sort%3Aupdated-desc" target="_blank">pull requests</a>, or <a href="' . $this->get_option('github_url', '') . '/discussions" target="_blank">discussions &raquo;</a></small></p>';
			echo '</div></div>';
			$this->timer('Nebula Companion GitHub Dashboard', 'end');
		}

		//Hubspot Contacts
		public function hubspot_metabox(){
			if ( $this->is_minimal_mode() ){return null;}
			wp_add_dashboard_widget('hubspot_contacts', '<i class="fa-brands fa-fw fa-hubspot"></i>&nbsp;Latest Hubspot Contacts', array($this, 'hubspot_contacts_content'));
		}

		//Hubspot Contacts metabox content
		public function hubspot_contacts_content(){
			$this->timer('Nebula Hubspot Dashboard Metabox', 'start', '[Nebula] Dashboard Metaboxes');
			do_action('nebula_hubspot_contacts');

			$hubspot_contacts_json = $this->transient('nebula_hubspot_contacts', function(){
				$requested_properties = '&property=' . implode('&property=', apply_filters('nebula_hubspot_metabox_properties', array('firstname', 'lastname', 'full_name', 'email', 'createdate')));
				$response = $this->remote_get('https://api.hubapi.com/contacts/v1/lists/all/contacts/recent?hapikey=' . $this->get_option('hubspot_api', '') . '&count=4' . $requested_properties);
				if ( is_wp_error($response) ){
					return null;
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

			echo '<p><small><a href="https://app.hubspot.com/sales/' . $this->get_option('hubspot_portal', '') . '/contacts/list/view/all/" target="_blank">View on Hubspot &raquo;</a></small></p>';
			$this->timer('Nebula Hubspot Dashboard Metabox', 'end');
		}
	}
}