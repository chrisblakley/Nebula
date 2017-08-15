<?php

//Exit if accessed directly
if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Dashboard') ){
	trait Dashboard {
		public function hooks(){
			//Remove unnecessary Dashboard metaboxes
			if ( $this->get_option('unnecessary_metaboxes') ){
				add_action('wp_dashboard_setup', array($this, 'remove_dashboard_metaboxes' ));
			}

			add_action('wp_dashboard_setup', array($this, 'ataglance_metabox' ));
			add_action('wp_dashboard_setup', array($this, 'current_user_metabox'));

			if ( current_user_can('manage_options') ){
				add_action('wp_dashboard_setup', array($this, 'administrative_metabox'));
			}

			add_action('wp_dashboard_setup', array($this, 'phg_metabox'));

			if ( $this->get_option('todo_manager_metabox') && $this->is_dev() ){
				add_action('wp_dashboard_setup', array($this, 'todo_metabox'));
			}

			if ( $this->get_option('dev_info_metabox') && $this->is_dev() ){
				add_action('wp_dashboard_setup', array($this, 'dev_info_metabox'));
			}

			add_action('wp_ajax_search_theme_files', array($this, 'search_theme_files'));
			add_action('wp_ajax_nopriv_search_theme_files', array($this, 'search_theme_files'));
		}

		//Remove unnecessary Dashboard metaboxes
		public function remove_dashboard_metaboxes(){
			$override = apply_filters('pre_remove_dashboard_metaboxes', null);
			if ( isset($override) ){return false;}

			//If necessary, dashboard metaboxes can be unset. To best future-proof, use remove_meta_box().
			remove_meta_box('dashboard_primary', 'dashboard', 'side'); //Wordpress News
			remove_meta_box('dashboard_secondary', 'dashboard', 'side');
			remove_meta_box('dashboard_plugins', 'dashboard', 'normal');
			remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');
			remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
			remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
		}

		//WordPress Information metabox ("At a Glance" replacement)
		public function ataglance_metabox(){
			global $wp_meta_boxes;
			wp_add_dashboard_widget('nebula_ataglance', '<img src="' . get_site_icon_url(32, get_theme_file_uri('/assets/img/meta') . '/favicon-32x32.png') . '" style="float: left; width: 20px;" />&nbsp;' . get_bloginfo('name'), array($this, 'dashboard_nebula_ataglance'));
		}

		public function dashboard_nebula_ataglance(){
			global $wp_version;
			global $wp_post_types;

			echo '<ul>';
			echo '<li><i class="fa fa-fw fa-globe"></i> <a href="' . home_url('/') . '" target="_blank" rel="noopener">' . home_url('/') . '</a></li>';

			//Address
			if ( $this->get_option('street_address') ){
				echo '<li><i class="fa fa-fw fa-map-marker"></i> <a href="https://www.google.com/maps/place/' . $this->full_address(1) . '" target="_blank" rel="noopener">' . $this->full_address() . '</a></li>';
			}

			//Open/Closed
			if ( $this->has_business_hours() ){
				$open_closed = ( $this->business_open() )? '<strong style="color: green;">Open</strong>' : '<strong>Closed</strong>';
				echo '<li><i class="fa fa-fw fa-clock-o"></i> Currently ' . $open_closed . '</li>';
			}

			//WordPress Version
			echo '<li><i class="fa fa-fw fa-wordpress"></i> <a href="https://codex.wordpress.org/WordPress_Versions" target="_blank" rel="noopener">WordPress</a> <strong>' . $wp_version . '</strong></li>';

			//Nebula Version
			echo '<li><i class="fa fa-fw fa-star"></i> <a href="https://gearside.com/nebula" target="_blank" rel="noopener">Nebula</a> <strong><a href="https://github.com/chrisblakley/Nebula/compare/master@{' . date('Y-m-d', $this->version('utc')) . '}...master" target="_blank">' . $this->version('raw') . '</a></strong> <small title="' . human_time_diff($this->version('utc')) . ' ago">(Committed: ' . $this->version('date') . ')</small></li>';

			//Child Theme
			if ( is_child_theme() ){
				echo '<li><i class="fa fa-fw fa-child"></i><a href="themes.php">Child theme</a> active <small>(' . get_option('stylesheet') . ')</small></li>';
			}

			//Multisite (and Super Admin detection)
			if ( is_multisite() ){
				$network_admin_link = '';
				if ( is_super_admin() ){
					$network_admin_link = ' <small><a href="' . network_admin_url() . '">(Network Admin)</a></small></li>';
				}
				echo '<li><i class="fa fa-fw fa-cubes"></i> Multisite' . $network_admin_link;
			}

			//Post Types
			foreach ( get_post_types() as $post_type ){
				//Only show post types that show_ui (unless forced with one of the arrays below)
				$force_show = array('wpcf7_contact_form'); //These will show even if their show_ui is false.
				$force_hide = array('attachment', 'acf', 'deprecated_log'); //These will be skipped even if their show_ui is true.
				if ( (!$wp_post_types[$post_type]->show_ui && !in_array($post_type, $force_show)) || in_array($post_type, $force_hide)){
					continue;
				}

				$count_posts = get_transient('nebula_count_posts_' . $post_type);
				if ( empty($count_posts) || $this->is_debug() ){
					$count_posts = wp_count_posts($post_type);
					$cache_length = ( is_plugin_active('transients-manager/transients-manager.php') )? WEEK_IN_SECONDS : DAY_IN_SECONDS; //If Transient Monitor (plugin) is active, transients with expirations are deleted when posts are published/updated, so this could be infinitely long.
					set_transient('nebula_count_posts_' . $post_type, $count_posts, $cache_length);
				}

				$labels_plural = ( $count_posts->publish === 1 )? $wp_post_types[$post_type]->labels->singular_name : $wp_post_types[$post_type]->labels->name;
				switch ( $post_type ){
					case ('post'):
						$post_icon_img = '<i class="fa fa-fw fa-thumb-tack"></i>';
						break;
					case ('page'):
						$post_icon_img = '<i class="fa fa-fw fa-file-text"></i>';
						break;
					case ('wpcf7_contact_form'):
						$post_icon_img = '<i class="fa fa-fw fa-envelope"></i>';
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
							$post_icon_img = '<i class="fa fa-fw fa-thumb-tack"></i>';
						}
						break;
				}
				echo '<li>' . $post_icon_img . ' <a href="edit.php?post_type=' . $post_type . '"><strong>' . $count_posts->publish . '</strong> ' . $labels_plural . '</a></li>';
			}

			//Last updated
			$latest_post = get_transient('nebula_latest_post');
			if ( empty($latest_post) || $this->is_debug() ){
				$latest_post = new WP_Query(array('post_type' => 'any', 'showposts' => 1, 'orderby' => 'modified', 'order' => 'DESC'));
				set_transient('nebula_latest_post', $latest_post, HOUR_IN_SECONDS*12); //This transient is deleted when posts are added/updated, so this could be infinitely long.
			}
			while ( $latest_post->have_posts() ){ $latest_post->the_post();
				echo '<li><i class="fa fa-fw fa-calendar-o"></i> Updated: <strong>' . get_the_modified_date() . '</strong> @ <strong>' . get_the_modified_time() . '</strong>
					<small style="display: block; margin-left: 20px;"><i class="fa fa-fw fa-file-text-o"></i> <a href="' . get_permalink() . '">' . $this->excerpt(array('text' => get_the_title(), 'words' => 5, 'more' => false, 'ellipsis' => true)) . '</a> (' . get_the_author() . ')</small>
				</li>';
			}
			wp_reset_postdata();

			//Revisions
			$revision_count = ( WP_POST_REVISIONS == -1 )? 'all' : WP_POST_REVISIONS;
			$revision_style = ( $revision_count === 0 )? 'style="color: red;"' : '';
			$revisions_plural = ( $revision_count === 1 )? 'revision' : 'revisions';
			echo '<li><i class="fa fa-fw fa-history"></i> Storing <strong ' . $revision_style . '>' . $revision_count . '</strong> ' . $revisions_plural . '.</li>';

			//Plugins
			$all_plugins = get_transient('nebula_count_plugins');
			if ( empty($all_plugins) || $this->is_debug() ){
				$all_plugins = get_plugins();
				set_transient('nebula_count_plugins', $all_plugins, HOUR_IN_SECONDS*36);
			}
			$all_plugins_plural = ( count($all_plugins) === 1 )? 'Plugin' : 'Plugins';
			$active_plugins = get_option('active_plugins', array());
			echo '<li><i class="fa fa-fw fa-plug"></i> <a href="plugins.php"><strong>' . count($all_plugins) . '</strong> ' . $all_plugins_plural . '</a> installed <small>(' . count($active_plugins) . ' active)</small></li>';

			//Users
			$user_count = get_transient('nebula_count_users');
			if ( empty($user_count) || $this->is_debug() ){
				$user_count = count_users();
				set_transient('nebula_count_users', $user_count, HOUR_IN_SECONDS*36); //24 hour cache
			}
			$users_icon = 'users';
			$users_plural = 'Users';
			if ( $user_count['total_users'] === 1 ){
				$users_plural = 'User';
				$users_icon = 'user';
			}
			echo '<li><i class="fa fa-fw fa-' . $users_icon . '"></i> <a href="users.php">' . $user_count['total_users'] . ' ' . $users_plural . '</a> <small>(' . $this->online_users('count') . ' currently active)</small></li>';

			//Comments
			if ( $this->get_option('comments') && $this->get_option('disqus_shortname') == '' ){
				$comments_count = wp_count_comments();
				$comments_plural = ( $comments_count->approved === 1 )? 'Comment' : 'Comments';
				echo '<li><i class="fa fa-fw fa-comments-o"></i> <strong>' . $comments_count->approved . '</strong> ' . $comments_plural . '</li>';
			} else {
				if ( !$this->get_option('comments') ){
					echo '<li><i class="fa fa-fw fa-comments-o"></i> Comments disabled <small>(via <a href="themes.php?page=nebula_options&tab=functions&option=comments">Nebula Options</a>)</small></li>';
				} else {
					echo '<li><i class="fa fa-fw fa-comments-o"></i> Using <a href="https://' . $this->get_option('disqus_shortname') . '.disqus.com/admin/moderate/" target="_blank" rel="noopener">Disqus comment system</a>.</li>';
				}
			}

			//Global Admin Bar
			if ( !$this->get_option('admin_bar') ){
				echo '<li><i class="fa fa-fw fa-bars"></i> Admin Bar disabled <small>(for all users via <a href="themes.php?page=nebula_options&tab=functions&option=admin_bar">Nebula Options</a>)</small></li>';
			}

			//Nebula Visitors DB
			if ( $this->get_option('visitors_db') ){
				global $wpdb;
				echo '<li><i class="fa fa-fw fa-database"></i> <a href="themes.php?page=nebula_visitors_data">Nebula Visitors DB</a> has <strong>' . $wpdb->get_var("select count(*) from " . $wpdb->prefix . 'nebula_visitors') . '</strong> rows.</li>';
			}

			echo '</ul>';

			do_action('nebula_ataglance');

			echo '<p><em>Designed and Developed by ' . $this->pinckneyhugogroup(1) . '</em></p>';
		}

		//Current User metabox
		public function current_user_metabox(){
			$user_info = get_userdata(get_current_user_id());
			$headshotURL = esc_attr(get_the_author_meta('headshot_url', get_current_user_id()));
			$headshot_thumbnail = str_replace('.jpg', '-150x150.jpg' , $headshotURL);

			if ( $headshot_thumbnail ){
				$headshot_html = '<img src="' . esc_attr($headshot_thumbnail) . '" style="float: left; max-width: 20px; border-radius: 100px;" />&nbsp;';
			} else {
				$headshot_html = '<i class="fa fa-fw fa-user"></i>&nbsp;';
			}

			wp_add_dashboard_widget('nebula_current_user', $headshot_html . $user_info->display_name, array($this, 'dashboard_current_user'));
		}

		public function dashboard_current_user(){
			$user_info = get_userdata(get_current_user_id());

			echo '<ul>';
			//Company
			$company = '';
			if ( get_the_author_meta('jobcompany', $user_info->ID) ){
				$company = get_the_author_meta('jobcompany', $user_info->ID);
				if ( get_the_author_meta('jobcompanywebsite', $user_info->ID) ){
					$company = '<a href="' . get_the_author_meta('jobcompanywebsite', $user_info->ID) . '" target="_blank" rel="noopener">' . $company . '</a>';
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
				echo '<li><i class="fa fa-fw fa-building"></i> ' . $job_title . $company . '</li>';
			}

			//Location
			if ( get_the_author_meta('usercity', $user_info->ID) && get_the_author_meta('userstate', $user_info->ID) ){
				echo '<li><i class="fa fa-fw fa-map-marker"></i> <strong>' . get_the_author_meta('usercity', $user_info->ID) . ', ' . get_the_author_meta('userstate', $user_info->ID) . '</strong></li>';
			}

			//Email
			echo '<li><i class="fa fa-fw fa-envelope-o"></i> Email: <strong>' . $user_info->user_email . '</strong></li>';

			if ( get_the_author_meta('phonenumber', $user_info->ID) ){
				echo '<li><i class="fa fa-fw fa-phone"></i> Phone: <strong>' . get_the_author_meta('phonenumber', $user_info->ID) . '</strong></li>';
			}

			echo '<li><i class="fa fa-fw fa-user"></i> Username: <strong>' . $user_info->user_login . '</strong></li>';
			echo '<li><i class="fa fa-fw fa-info-circle"></i> ID: <strong>' . $user_info->ID . '</strong></li>';

			//Role
			$fa_role = 'fa-user';
			$super_role = 'Unknown';
			if ( !empty($user_info->roles) ){
				switch ( $user_info->roles[0] ){
					case 'administrator': $fa_role = 'fa-key'; break;
					case 'editor': $fa_role = 'fa-scissors'; break;
					case 'author': $fa_role = 'fa-pencil-square'; break;
					case 'contributor': $fa_role = 'fa-send'; break;
					case 'subscriber': $fa_role = 'fa-ticket'; break;
					default: $fa_role = 'fa-user'; break;
				}
				$super_role = ( is_multisite() && is_super_admin() )? 'Super Admin' : $user_info->roles[0];
			}
			echo '<li><i class="fa fa-fw ' . $fa_role . '"></i> Role: <strong class="admin-user-info admin-user-role">' . $super_role . '</strong></li>';

			//Developer
			if ( $this->is_dev() ){
				echo '<li><i class="fa fa-fw fa-gears"></i> <strong>Developer</strong></li>';
			}

			//User's posts
			$your_posts = get_transient('nebula_count_posts_user_' . $user_info->ID);
			if ( empty($your_posts) || $this->is_debug() ){
				$your_posts = count_user_posts($user_info->ID);
				set_transient('nebula_count_posts_user_' . $user_info->ID, $your_posts, DAY_IN_SECONDS); //24 hour cache
			}
			echo '<li><i class="fa fa-fw fa-thumb-tack"></i> Your posts: <strong>' . $your_posts . '</strong></li>';

			if ( $this->get_option('device_detection') ){
				//Device
				if ( $this->is_desktop() ){
					$battery_percentage = $this->get_visitor_datapoint('battery_percentage');
					if ( (!empty($battery_percentage) && str_replace('%', '', $battery_percentage) < 100) || $this->get_visitor_datapoint('battery_mode') === 'Battery' ){
						echo '<li><i class="fa fa-fw fa-laptop"></i> Device: <strong>Laptop</strong></li>';
					} else {
						echo '<li><i class="fa fa-fw fa-desktop"></i> Device: <strong>Desktop</strong></li>';
					}
				} elseif ( $this->is_tablet() ){
					echo '<li><i class="fa fa-fw fa-tablet"></i> Device: <strong>' . $this->get_device('full') . ' (Tablet)</strong></li>';
				} else {
					echo '<li><i class="fa fa-fw fa-mobile"></i> Device: <strong>' . $this->get_device('full') . ' (Mobile)</strong></li>';
				}

				//Operating System
				switch ( strtolower($this->get_os('name')) ){
					case 'windows':
						$os_icon = 'fa-windows';
						break;
					case 'mac':
					case 'ios':
						$os_icon = 'fa-apple';
						break;
					case 'linux':
						$os_icon = 'fa-linux';
						break;
					case 'android':
						$os_icon = 'fa-android';
						break;
					default:
						$os_icon = 'fa-picture-o';
						break;
				}
				echo '<li><i class="fa fa-fw ' . $os_icon . '"></i> OS: <strong>' . $this->get_os('full') . '</strong></li>';

				//Browser
				switch ( str_replace(array('mobile', ' '), '', strtolower($this->get_browser('name'))) ){
					case 'edge':
						$browser_icon = 'fa-edge';
						break;
					case 'safari':
						$browser_icon = 'fa-safari';
						break;
					case 'internet explorer':
						$browser_icon = 'fa-internet-explorer';
						break;
					case 'firefox':
						$browser_icon = 'fa-firefox';
						break;
					case 'chrome':
					case 'chrome mobile':
						$browser_icon = 'fa-chrome';
						break;
					case 'opera':
						$browser_icon = 'fa-opera';
						break;
					default:
						$browser_icon = 'fa-globe';
						break;
				}
				echo '<li><i class="fa fa-fw ' . $browser_icon . '"></i> Browser: <strong>' . $this->get_browser('full') . '</strong></li>';
			}

			//IP Address
			echo '<li>';
			if ( $_SERVER['REMOTE_ADDR'] === '72.43.235.106' ){
				echo '<img src="' . get_template_directory_uri() . '/assets/img/phg/phg-symbol.png" style="max-width: 14px;" />';
			} else {
				echo '<i class="fa fa-fw fa-globe"></i>';
			}
			echo ' IP Address: <a href="http://whatismyipaddress.com/ip/' . $_SERVER["REMOTE_ADDR"] . '" target="_blank" rel="noopener"><strong class="admin-user-info admin-user-ip">' . $_SERVER["REMOTE_ADDR"] . '</strong></a>';
			echo '</li>';

			//IP Location
			if ( $this->ip_location() ){
				$ip_location = $this->ip_location('all');

				if ( !empty($ip_location) ){
					echo '<li><i class="fa fa-fw fa-location-arrow"></i> IP Location: <i class="flag flag-' . strtolower($ip_location->country_code) . '"></i> <strong>' . $ip_location->city . ', ' . $ip_location->region_name . '</strong></li>';
				} else {
					echo '<li><i class="fa fa-fw fa-location-arrow"></i> IP Location: <em>GeoIP error or rate limit exceeded.</em></li>';
				}
			}

			//Weather
			if ( $this->get_option('weather') ){
				$ip_zip = '';
				if ( $this->get_visitor_datapoint('zip_code') ){
					$ip_zip = $this->get_visitor_datapoint('zip_code');
				} elseif ( $this->ip_location() ){
					$ip_zip = $this->ip_location('zip');
				}

				$temperature = $this->weather($ip_zip, 'temp');
				if ( !empty($temperature) ){
					echo '<li><i class="fa fa-fw fa-cloud"></i> Weather: <strong>' . $temperature . '&deg;F ' . $this->weather($ip_zip, 'conditions') . '</strong></li>';
				} else {
					echo '<li><i class="fa fa-fw fa-cloud"></i> Weather: <em>API error for zip code ' . $ip_zip . '.</em></li>';
				}
			}

			//Multiple locations
			if ( $this->user_single_concurrent($user_info->ID) > 1 ){
				echo '<li><i class="fa fa-fw fa-users"></i> Active in <strong>' . $this->user_single_concurrent($user_info->ID) . ' locations</strong>.</li>';
			}

			//User Admin Bar
			if ( !get_user_option('show_admin_bar_front', $user_info->ID) ){
				echo '<li><i class="fa fa-fw fa-bars"></i> Admin Bar disabled <small>(for just you via <a href="profile.php">User Profile</a>)</small></li>';
			}
			echo '</ul>';

			echo '<p><small><em><a href="profile.php"><i class="fa fa-fw fa-pencil"></i> Manage your user information</a></em></small></p>';
		}

		//Administrative metabox
		public function administrative_metabox(){
			wp_add_dashboard_widget('nebula_administrative', 'Administrative', array($this, 'dashboard_administrative'));
		}

		//Administrative metabox content
		public function dashboard_administrative(){
			echo '<ul>';
			if ( $this->get_option('hosting_url') ){
				echo '<li><i class="fa fa-fw fa-hdd-o"></i> <a href="' . $this->get_option('hosting_url') . '" target="_blank" rel="noopener">Hosting</a></li>';
			}

			if ( $this->get_option('cpanel_url') ){
				echo '<li><i class="fa fa-fw fa-gears"></i> <a href="' . $this->get_option('cpanel_url') . '" target="_blank" rel="noopener">Server Control Panel</a></li>';
			}

			if ( $this->get_option('registrar_url') ){
				echo '<li><i class="fa fa-fw fa-globe"></i> <a href="' . $this->get_option('registrar_url') . '" target="_blank" rel="noopener">Domain Registrar</a></li>';
			}

			if ( $this->get_option('ga_tracking_id') ){
				echo '<li><i class="fa fa-fw fa-area-chart"></i> <a href="https://analytics.google.com/analytics/web/" target="_blank" rel="noopener">Google Analytics</a></li>';
			}

			if ( $this->get_option('google_optimize_id') ){
				echo '<li><i class="fa fa-fw fa-pie-chart"></i> <a href="https://optimize.google.com/optimize/home" target="_blank" rel="noopener">Google Optimize</a></li>';
			}

			echo '<li><i class="fa fa-fw fa-google"></i> <a href="https://www.google.com/webmasters/tools/home" target="_blank" rel="noopener">Google Search Console</a></li>';

			if ( $this->get_option('adwords_remarketing_conversion_id') ){
				echo '<li><i class="fa fa-fw fa-search-plus"></i> <a href="https://adwords.google.com/home/" target="_blank" rel="noopener">Google AdWords</a></li>';
			}

			if ( $this->get_option('facebook_custom_audience_pixel_id') ){
				echo '<li><i class="fa fa-fw fa-facebook-official"></i> <a href="https://www.facebook.com/ads/manager/account/campaigns" target="_blank" rel="noopener">Facebook Ads Manager</a></li>';
			}

			if ( $this->get_option('google_adsense_url') ){
				echo '<li><i class="fa fa-fw fa-money"></i> <a href="https://www.google.com/adsense" target="_blank" rel="noopener">Google AdSense</a></li>';
			}

			if ( $this->get_option('amazon_associates_url') ){
				echo '<li><i class="fa fa-fw fa-amazon"></i> <a href="https://affiliate-program.amazon.com/home" target="_blank" rel="noopener">Amazon Associates</a></li>';
			}

			echo '<li><i class="fa fa-fw fa-building"></i> <a href="https://www.google.com/business/" target="_blank" rel="noopener">Google My Business</a></li>';

			if ( $this->get_option('google_server_api_key') || $this->get_option('google_browser_api_key') ){
				echo '<li><i class="fa fa-fw fa-code"></i> <a href="https://console.developers.google.com/iam-admin/projects" target="_blank" rel="noopener">Google APIs</a></li>';
			}

			if ( $this->get_option('cse_id') ){
				echo '<li><i class="fa fa-fw fa-search"></i> <a href="https://cse.google.com/cse/all" target="_blank" rel="noopener">Google Custom Search</a></li>';
			}

			if ( $this->get_option('hubspot_api') || $this->get_option('hubspot_portal') ){
				echo '<li><i class="fa fa-fw fa-users"></i> <a href="https://app.hubspot.com/reports-dashboard/' . $this->get_option('hubspot_portal') . '" target="_blank" rel="noopener">Hubspot</a></li>';
			}

			if ( $this->get_option('mention_url') ){
				echo '<li><i class="fa fa-fw fa-star"></i> <a href="https://web.mention.com" target="_blank" rel="noopener">Mention</a></li>';
			}

			do_action('nebula_administrative_metabox');
			echo '</ul>';
			echo '<p><small><em>Manage administrative links in <strong><a href="themes.php?page=nebula_options&tab=administration">Nebula Options</a></strong>.</em></small></p>';

			echo '<h3>Social</h3>';
			echo '<ul>';
			if ( $this->get_option('facebook_url') ){
				echo '<li><i class="fa fa-fw fa-facebook-square"></i> <a href="' . $this->get_option('facebook_url') . '" target="_blank" rel="noopener">Facebook</a></li>';
			}

			if ( $this->get_option('twitter_username') ){
				echo '<li><i class="fa fa-fw fa-twitter-square"></i> <a href="' . $this->twitter_url() . '" target="_blank" rel="noopener">Twitter</a></li>';
			}

			if ( $this->get_option('linkedin_url') ){
				echo '<li><i class="fa fa-fw fa-linkedin-square"></i> <a href="' . $this->get_option('linkedin_url') . '" target="_blank" rel="noopener">LinkedIn</a></li>';
			}

			if ( $this->get_option('youtube_url') ){
				echo '<li><i class="fa fa-fw fa-youtube-square"></i> <a href="' . $this->get_option('youtube_url') . '" target="_blank" rel="noopener">Youtube</a></li>';
			}

			if ( $this->get_option('instagram_url') ){
				echo '<li><i class="fa fa-fw fa-instagram"></i> <a href="' . $this->get_option('instagram_url') . '" target="_blank" rel="noopener">Instagram</a></li>';
			}

			if ( $this->get_option('google_plus_url') ){
				echo '<li><i class="fa fa-fw fa-google-plus-square"></i> <a href="' . $this->get_option('google_plus_url') . '" target="_blank" rel="noopener">Google+</a></li>';
			}

			if ( $this->get_option('disqus_shortname') ){
				echo '<li><i class="fa fa-fw fa-comments-o"></i> <a href="https://' . $this->get_option('disqus_shortname') . '.disqus.com/admin/moderate/" target="_blank" rel="noopener">Disqus</a></li>';
			}

			do_action('nebula_social_metabox');
			echo '</ul>';
			echo '<p><small><em>Manage social links in <strong><a href="themes.php?page=nebula_options&filter=social">Nebula Options</a></strong>.</em></small></p>';
		}

		//Pinckney Hugo Group metabox
		public function phg_metabox(){
			wp_add_dashboard_widget('nebula_phg', 'Pinckney Hugo Group', array($this, 'dashboard_phg'));
		}

		//Pinckney Hugo Group metabox content
		public function dashboard_phg(){
			echo '<a href="http://pinckneyhugo.com" target="_blank" rel="noopener"><img src="' . get_template_directory_uri() . '/assets/img/phg/phg-building.jpg" style="width: 100%;" /></a>';
			echo '<ul>';
			echo '<li>' . $this->pinckneyhugogroup() . '</li>';
			echo '<li><i class="fa fa-fw fa-map-marker"></i> <a href="https://www.google.com/maps/place/760+West+Genesee+Street+Syracuse+NY+13204" target="_blank" rel="noopener">760 West Genesee Street, Syracuse, NY 13204</a></li>';
			echo '<li><i class="fa fa-fw fa-phone"></i> (315) 478-6700</li>';
			echo '</ul>';
		}

		//Extension skip list for both To Do Manager and Developer Metabox
		public function skip_extensions(){
			return array('.jpg', '.jpeg', '.png', '.gif', '.ico', '.tiff', '.psd', '.ai',  '.apng', '.bmp', '.otf', '.ttf', '.ogv', '.flv', '.fla', '.mpg', '.mpeg', '.avi', '.mov', '.woff', '.eot', '.mp3', '.mp4', '.wmv', '.wma', '.aiff', '.zip', '.zipx', '.rar', '.exe', '.dmg', '.csv', '.swf', '.pdf', '.pdfx', '.pem', '.ppt', '.pptx', '.pps', '.ppsx', '.bak'); //Would it be faster to list allowed filetypes instead?
		}

		//TODO metabox
		public function todo_metabox(){
			wp_add_dashboard_widget('todo_manager', 'To-Do Manager', array($this, 'todo_metabox_content'));
		}

		//TODO metabox content
		public function todo_metabox_content(){
			do_action('nebula_todo_manager');
			echo '<p class="todoresults_title"><strong>Active @todo Comments</strong> <a class="todo_help_icon" href="http://gearside.com/wordpress-dashboard-todo-manager/" target="_blank" rel="noopener"><i class="fa fw fa-question-circle"></i> Documentation &raquo;</a></p><div class="todo_results">';

			$todo_counts = $this->todo_search_files();

			echo '</div><!--/todo_results-->';
			echo '<p>Found <strong>' . $todo_counts['files'] . ' files</strong> with <strong>' . $todo_counts['instances'] . ' @todo comments</strong>.</p>';
		}

		public function todo_search_files($options=array()){
			$defaults = array(
				'directory_path' => get_template_directory(),
				'child' => false, //If currently searching child theme files
				'files' => 0,
				'instances' => 0,
			);

			$data = array_merge($defaults, $options);

			$todo_last_filename = false;

			if ( is_child_theme() && !$data['child'] ){
				$current_counts = $this->todo_search_files(array('directory_path' => get_stylesheet_directory(), 'child' => true));
				$data['files'] = $current_counts['files'];
				$data['instances'] = $current_counts['instances'];
			}

			if ( empty($todo_dirpath) ){
				$todo_dirpath = get_template_directory();
			}

			foreach ( $this->glob_r($todo_dirpath . '/*') as $todo_file ){
				$todo_counted = false;
				if ( is_file($todo_file) ){
					if ( strpos(basename($todo_file), '@todo') !== false ){
						echo '<p class="resulttext">' . str_replace($todo_dirpath, '', dirname($todo_file)) . '/<strong>' . basename($todo_file) . '</strong></p>';
						$data['files']++;
						$todo_counted = true;
					}

					$todo_skipFilenames = array('README.md', 'debug_log', 'error_log', '/vendor', 'resources/');

					if ( !$this->contains($todo_file, $this->skip_extensions()) && !$this->contains($todo_file, $todo_skipFilenames) ){
						foreach ( file($todo_file) as $todo_lineNumber => $todo_line ){
							preg_match("/(@todo)\s?(?'category'[\"\'\`].+?[\"\'\`])?\s?(?'priority'\d)?:\s(?'description'.+)/i", $todo_line, $todo_details); //Separate the todo comment into useable groups

							if ( !empty($todo_details) ){
								$theme = '';
								$theme_note = '';
								if ( is_child_theme() ){
									$theme = 'parent';
									$theme_note = ' <small>(Parent)</small>';

									if ( $data['child'] ){
										$theme = 'child';
										$theme_note = ' <small>(Child)</small>';
									}
								}

								$todo_priority = ( $todo_details['priority'] === '' )? 'empty' : intval($todo_details['priority']); //Get the priority

								$todo_category = ( !empty($todo_details['category']) )? str_replace(array('"', "'", '`'), '', $todo_details['category']) : ''; //Get the category
								$todo_category_html = ( !empty($todo_category) )? '<span class="todocategory">' . $todo_category . '</span>' : '';
								$todo_description = strip_tags(str_replace(array('-->', '?>', '*/'), '', $todo_details['description'])); //Get the description

								$todo_this_filename = str_replace($todo_dirpath, '', dirname($todo_file)) . '/' . basename($todo_file);
								if ( $todo_last_filename !== $todo_this_filename ){
									if ( !empty($todo_last_filename) ){
										echo '</div><!--/todofilewrap-->';
									}

									echo '<div class="todofilewrap todo-theme-' . $theme . '"><p class="todofilename">' . str_replace($todo_dirpath, '', dirname($todo_file)) . '/<strong>' . basename($todo_file) . '</strong><span class="themenote">' . $theme_note . '</span></p>';
								}

								echo '<div class="linewrap todo-category-' . strtolower(str_replace(' ', '_', $todo_category)) . ' todo-priority-' . $todo_priority . '"><p class="todoresult"> ' . $todo_category_html . ' <a class="linenumber" href="#">Line ' . ($todo_lineNumber+1) . '</a> <span class="todomessage">' . $todo_description . '</span></p><div class="precon"><pre class="actualline">' . trim(htmlentities($todo_line)) . '</pre></div></div>';

								$todo_last_filename = $todo_this_filename;

								if ( ($todo_priority === 'empty' || $todo_priority > 0) ){ //Only count todos with a non-hidden priority
									if ( !is_child_theme() || (is_child_theme() && $data['child']) ){ //Only count child todo comments if child theme is active
										$data['instances']++;

										if ( !$todo_counted ){
											$data['files']++;
											$todo_counted = true;
										}
									}
								}
							}
						}
					}
				}
			}

			if ( $data['instances'] >= 1 ){
				echo '</div><!--/todofilewrap-->';
			}

			return array('files' => $data['files'], 'instances' => $data['instances']);
		}

		//Developer Info Metabox
		public function dev_info_metabox(){
			wp_add_dashboard_widget('phg_developer_info', 'Developer Information', array($this, 'dashboard_developer_info'));
		}

		//Developer Info Metabox content
		public function dashboard_developer_info(){
			do_action('nebula_developer_info');
			echo '<ul class="serverdetections">';

			//Domain
			echo '<li><i class="fa fa-fw fa-info-circle"></i> <a href="http://whois.domaintools.com/' . $_SERVER['SERVER_NAME'] . '" target="_blank" rel="noopener" title="WHOIS Lookup">Domain</a>: <strong>' . $this->url_components('domain') . '</strong></li>';

			//Host
			function top_domain_name($url){
				$alldomains = explode(".", $url);
				return $alldomains[count($alldomains)-2] . "." . $alldomains[count($alldomains)-1];
			}
			if ( function_exists('gethostname') ){
				set_error_handler(function(){ /* ignore errors */ });
				$dnsrecord = ( dns_get_record(top_domain_name(gethostname()), DNS_NS) )? dns_get_record(top_domain_name(gethostname()), DNS_NS) : '';
				restore_error_handler();

				echo '<li><i class="fa fa-fw fa-hdd-o"></i> Host: <strong>' . top_domain_name(gethostname()) . '</strong>';
				if ( !empty($dnsrecord[0]['target']) ){
					echo ' <small>(' . top_domain_name($dnsrecord[0]['target']) . ')</small>';
				}
				echo '</li>';
			}

			//Server IP address (and connection security)
			$secureServer = '';
			if ( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] === 443 ){
				$secureServer = '<small class="secured-connection"><i class="fa fa-fw fa-lock"></i>Secured Connection</small>';
			}
			echo '<li><i class="fa fa-fw fa-upload"></i> Server IP: <strong><a href="http://whatismyipaddress.com/ip/' . $_SERVER['SERVER_ADDR'] . '" target="_blank" rel="noopener">' . $_SERVER['SERVER_ADDR'] . '</a></strong> ' . $secureServer . '</li>';

			//Server operating system
			if ( strpos(strtolower(PHP_OS), 'linux') !== false ){
				$php_os_icon = 'fa-linux';
			} else if ( strpos(strtolower(PHP_OS), 'windows') !== false ){
				$php_os_icon = 'fa-windows';
			} else {
				$php_os_icon = 'fa-upload';
			}
			echo '<li><i class="fa fa-fw ' . $php_os_icon . '"></i> Server OS: <strong>' . PHP_OS . '</strong> <small>(' . $_SERVER['SERVER_SOFTWARE'] . ')</small></li>';

			//PHP version
			$php_version_color = 'inherit';
			$php_version_info = '';
			$php_version_cursor = 'normal';
			$php_version_lifecycle = $this->php_version_support();
			if ( $php_version_lifecycle['lifecycle'] === 'security' ){
				$php_version_color = '#ca8038';
				$php_version_info = 'This version is nearing end of life. Security updates end on ' . date('F j, Y', $php_version_lifecycle['end']) . '.';
				$php_version_cursor = 'help';
			} elseif ( $php_version_lifecycle['lifecycle'] === 'end' ){
				$php_version_color = '#ca3838';
				$php_version_info = 'This version no longer receives security updates! End of life occurred on ' . date('F j, Y', $php_version_lifecycle['end']) . '.';
				$php_version_cursor = 'help';
			}
			$safe_mode = ( ini_get('safe_mode') )? '<small><strong><em>Safe Mode</em></strong></small>' : '';
			echo '<li><i class="fa fa-fw fa-wrench"></i> PHP Version: <strong style="color: ' . $php_version_color . '; cursor: ' . $php_version_cursor . ';" title="' . $php_version_info . '">' . PHP_VERSION . '</strong> ' . $safe_mode . '</li>';

			//PHP memory limit
			echo '<li><i class="fa fa-fw fa-cogs"></i> PHP Memory Limit: <strong>' . WP_MEMORY_LIMIT . '</strong> ' . $safe_mode . '</li>';

			//MySQL version
			if ( function_exists('mysqli_get_client_version') ){
				$mysql_version = mysqli_get_client_version();
				echo '<li><i class="fa fa-fw fa-database"></i> MySQL Version: <strong title="Raw: ' . $mysql_version . '">' . floor($mysql_version/10000) . '.' . floor(($mysql_version%10000)/100) . '.' . ($mysql_version%10000)%100 . '</strong></li>';
			}

			//Theme directory size(s)
			if ( is_child_theme() ){
				$nebula_parent_size = get_transient('nebula_directory_size_parent_theme');
				if ( empty($nebula_parent_size) || $this->is_debug() ){
					$nebula_parent_size = $this->foldersize(get_template_directory());
					set_transient('nebula_directory_size_parent_theme', $nebula_parent_size, DAY_IN_SECONDS); //12 hour cache
				}

				$nebula_child_size = get_transient('nebula_directory_size_child_theme');
				if ( empty($nebula_child_size) || $this->is_debug() ){
					$nebula_child_size = $this->foldersize(get_template_directory());
					set_transient('nebula_directory_size_child_theme', $nebula_child_size, DAY_IN_SECONDS); //12 hour cache
				}

				echo '<li><i class="fa fa-code"></i> Parent theme directory size: <strong>' . round($nebula_parent_size/1048576, 2) . 'mb</strong> </li>';

				if ( $this->get_option('prototype_mode') ){
					echo '<li><i class="fa fa-flag-checkered"></i> Production directory size: <strong>' . round($nebula_child_size/1048576, 2) . 'mb</strong> </li>';
				} else {
					echo '<li><i class="fa fa-code"></i> Child theme directory size: <strong>' . round($nebula_child_size/1048576, 2) . 'mb</strong> </li>';
				}
			} else {
				$nebula_size = get_transient('nebula_directory_size_theme');
				if ( empty($nebula_size) || $this->is_debug() ){
					$nebula_size = $this->foldersize(get_stylesheet_directory());
					set_transient('nebula_directory_size_theme', $nebula_size, DAY_IN_SECONDS); //12 hour cache
				}
				echo '<li><i class="fa fa-code"></i> Theme directory size: <strong>' . round($nebula_size/1048576, 2) . 'mb</strong> </li>';
			}

			if ( $this->get_option('prototype_mode') ){
				if ( $this->get_option('wireframe_theme') ){
					$nebula_wireframe_size = $this->foldersize(get_theme_root() . '/' . $this->get_option('wireframe_theme'));
					echo '<li title="' . $this->get_option('wireframe_theme') . '"><i class="fa fa-flag-o"></i> Wireframe directory size: <strong>' . round($nebula_wireframe_size/1048576, 2) . 'mb</strong> </li>';
				}

				if ( $this->get_option('staging_theme') ){
					$nebula_staging_size = $this->foldersize(get_theme_root() . '/' . $this->get_option('staging_theme'));
					echo '<li title="' . $this->get_option('staging_theme') . '"><i class="fa fa-flag"></i> Staging directory size: <strong>' . round($nebula_staging_size/1048576, 2) . 'mb</strong> </li>';
				}
			}

			//Uploads directory size (and max upload size)
			$upload_dir = wp_upload_dir();
			$uploads_size = get_transient('nebula_directory_size_uploads');
			if ( empty($uploads_size) || $this->is_debug() ){
				$uploads_size = $this->foldersize($upload_dir['basedir']);
				set_transient('nebula_directory_size_uploads', $uploads_size, HOUR_IN_SECONDS*36); //24 hour cache
			}

			if ( function_exists('wp_max_upload_size') ){
				$upload_max = '<small>(Max upload: <strong>' . strval(round((int) wp_max_upload_size()/(1024*1024))) . 'mb</strong>)</small>';
			} else if ( ini_get('upload_max_filesize') ){
				$upload_max = '<small>(Max upload: <strong>' . ini_get('upload_max_filesize') . '</strong>)</small>';
			} else {
				$upload_max = '';
			}
			echo '<li><i class="fa fa-fw fa-picture-o"></i> Uploads directory size: <strong>' . round($uploads_size/1048576, 2) . 'mb</strong> ' . $upload_max . '</li>';

			//Load Times
			echo '<div id="testloadcon" style="pointer-events: none; opacity: 0; visibility: hidden; display: none;"></div>';
			echo '<script id="testloadscript">
				jQuery(window).on("load", function(){
					jQuery(".windowloadtime").css("visibility", "visible");
					var iframe = document.createElement("iframe");
					iframe.style.width = "1200px";
					iframe.style.height = "0px";
					jQuery("#testloadcon").append(iframe);
					iframe.src = "' . home_url('/') . '";
					jQuery("#testloadcon iframe").on("load", function(){
						var iframeResponseEnd = Math.round(iframe.contentWindow.performance.timing.responseEnd-iframe.contentWindow.performance.timing.navigationStart); //Navigation start until server response finishes
						var iframeDomReady = Math.round(iframe.contentWindow.performance.timing.domContentLoadedEventStart-iframe.contentWindow.performance.timing.navigationStart); //Navigation start until DOM ready
						var iframeWindowLoaded = Math.round(iframe.contentWindow.performance.timing.loadEventStart-iframe.contentWindow.performance.timing.navigationStart); //Navigation start until window load

						jQuery(".serverloadtime").html(iframeResponseEnd/1000 + " seconds").attr("title", "Calculated via JS performance timing"); //Server Response Time

						jQuery(".windowloadtime").html(iframeWindowLoaded/1000 + " seconds"); //Window Load Time
						if ( iframeWindowLoaded > 3000 ){
							jQuery(".windowslowicon").addClass("fa-warning");
						}

						jQuery(".serverdetections .fa-spin, #testloadcon, #testloadscript").remove();
					});
				});
			</script>';

			//Server Load Time
			echo '<li><i class="fa fa-fw fa-clock-o"></i> Server response time: <strong class="serverloadtime" title="Calculated via PHP render time">' . timer_stop(0, 3) . ' seconds</strong></li>';

			//Window Load Time
			echo '<li><i class="fa fa-fw fa-clock-o"></i> Window load time: <a href="http://developers.google.com/speed/pagespeed/insights/?url=' . home_url('/') . '" target="_blank" rel="noopener" title="Time is specific to your current environment and therefore may be faster or slower than average."><strong class="windowloadtime" style="visibility: hidden;"><i class="fa fa-spinner fa-fw fa-spin"></i></strong></a> <i class="windowslowicon fa" style="color: maroon;"></i></li>';

			//Initial installation date
			function initial_install_date(){
				$nebula_initialized = nebula()->get_option('initialized'); //Keep this as nebula() because it is a nested function, so $this is scoped differently here.
				if ( !empty($nebula_initialized) && $nebula_initialized < getlastmod() ){
					$install_date = '<span title="' . human_time_diff($nebula_initialized) . ' ago" style="cursor: help;"><strong>' . date('F j, Y', $nebula_initialized) . '</strong> <small>@</small> <strong>' . date('g:ia', $nebula_initialized) . '</strong></span>';
				} else { //Use the last modified time of the admin page itself
					$install_date = '<span title="' . human_time_diff(getlastmod()) . ' ago" style="cursor: help;"><strong>' . date("F j, Y", getlastmod()) . '</strong> <small>@</small> <strong>' . date("g:ia", getlastmod()) . '</strong></span>';
				}
				return $install_date;
			}
			echo '<li><i class="fa fa-fw fa-calendar-o"></i> Installed: ' . initial_install_date() . '</li>';

			$latest_file = $this->last_modified();
			echo '<li><i class="fa fa-fw fa-calendar"></i> <span title="' . $latest_file['path'] . '" style="cursor: help;">Modified:</span> <strong title="' . human_time_diff($latest_file['date']) . ' ago" style="cursor: help;">' . date("F j, Y", $latest_file['date']) . '</strong> <small>@</small> <strong>' . date("g:ia", $latest_file['date']) . '</strong></li>';

			//SCSS last processed date
			if ( $this->get_data('scss_last_processed') ){
				echo '<li><i class="fa fa-fw fa-paint-brush"></i> Sass Processed: <span title="' . human_time_diff($this->get_data('scss_last_processed')) . ' ago" style="cursor: help;"><strong>' . date("F j, Y", $this->get_data('scss_last_processed')) . '</strong> <small>@</small> <strong>' . date("g:i:sa", $this->get_data('scss_last_processed')) . '</strong></span></li>';
			}
			echo '</ul>';

			//Directory search
			echo '<i id="searchprogress" class="fa fa-fw fa-search"></i> <form id="theme" class="searchfiles"><input class="findterm" type="text" placeholder="Search files" /><select class="searchdirectory">';
			if ( $this->get_option('prototype_mode') ){
				echo '<option value="production">Production</option>';
				if ( $this->get_option('staging_theme') ){
					echo '<option value="staging">Staging</option>';
				}
				if ( $this->get_option('wireframe_theme') ){
					echo '<option value="wireframe">Wireframe</option>';
				}
				echo '<option value="parent">Parent Theme</option>';
			} elseif ( is_child_theme() ){
				echo '<option value="child">Child Theme</option><option value="parent">Parent Theme</option>';
			} else {
				echo '<option value="theme">Theme</option>';
			}
			echo '<option value="plugins">Plugins</option><option value="uploads">Uploads</option></select><input class="searchterm button button-primary" type="submit" value="Search" /></form><br />';
			echo '<div class="search_results"></div>';
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

			if ( empty($directory) ){
				$directory = get_template_directory();
			}
			$dir = $this->glob_r($directory . '/*');
			$skip_files = array('dev.css', 'dev.scss', '/cache/', '/includes/data/', 'manifest.json', '.bak'); //Files or directories to skip. Be specific!

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

		//Search theme or plugin files via Developer Information Metabox
		public function search_theme_files(){
			if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ die('Permission Denied.'); }

			ini_set('max_execution_time', 120);
			ini_set('memory_limit', '512M');
			$searchTerm = htmlentities(stripslashes($_POST['data'][0]['searchData']));

			if ( strlen($searchTerm) < 3 ){
				echo '<p><strong>Error:</strong> Minimum 3 characters needed to search!</p>';
				die();
			}

			if ( $_POST['data'][0]['directory'] === 'theme' ){
				$dirpath = get_template_directory();
			} elseif ( $_POST['data'][0]['directory'] === 'parent' ){
				$dirpath = get_template_directory();
			} elseif ( $_POST['data'][0]['directory'] === 'child' ){
				$dirpath = get_stylesheet_directory();
			} elseif ( $_POST['data'][0]['directory'] === 'wireframe' ){
				$dirpath = get_theme_root() . '/' . $this->get_option('wireframe_theme');
			} elseif ( $_POST['data'][0]['directory'] === 'staging' ){
				$dirpath = get_theme_root() . '/' . $this->get_option('staging_theme');
			} elseif ( $_POST['data'][0]['directory'] === 'production' ){
				if ( $this->get_option('production_theme') ){
					$dirpath = get_theme_root() . '/' . $this->get_option('production_theme');
				} else {
					$dirpath = get_stylesheet_directory();
				}
			} elseif ( $_POST['data'][0]['directory'] === 'plugins' ){
				$dirpath = WP_PLUGIN_DIR;
			} elseif ( $_POST['data'][0]['directory'] === 'uploads' ){
				$uploadDirectory = wp_upload_dir();
				$dirpath = $uploadDirectory['basedir'];
			} else {
				echo '<p><strong>Error:</strong> Please specify a directory to search!</p>';
				die();
			}

			echo '<p class="resulttext">Search results for <strong>"' . $searchTerm . '"</strong> in the <strong>' . $_POST['data'][0]['directory'] . '</strong> directory:</p><br />';

			$file_counter = 0;
			$instance_counter = 0;
			foreach ( $this->glob_r($dirpath . '/*') as $file ){
				$counted = 0;
				if ( is_file($file) ){
					if ( strpos(basename($file), $searchTerm) !== false ){
						echo '<p class="resulttext">' . str_replace($dirpath, '', dirname($file)) . '/<strong>' . basename($file) . '</strong></p>';
						$file_counter++;
						$counted = 1;
					}

					$skipFilenames = array('error_log');
					if ( !$this->contains(basename($file), $this->skip_extensions()) && !$this->contains(basename($file), $skipFilenames) ){
						foreach ( file($file) as $lineNumber => $line ){
							if ( stripos(stripslashes($line), $searchTerm) !== false ){
								$actualLineNumber = $lineNumber+1;
								echo '<div class="linewrap">
								<p class="resulttext">' . str_replace($dirpath, '', dirname($file)) . '/<strong>' . basename($file) . '</strong> on <a class="linenumber" href="#">line ' . $actualLineNumber . '</a>.</p>
								<div class="precon"><pre class="actualline">' . trim(htmlentities($line)) . '</pre></div>
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
	}
}