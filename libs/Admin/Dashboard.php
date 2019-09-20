<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Dashboard') ){
	trait Dashboard {
		public function hooks(){
			//Exclude AJAX requests
			if ( !$this->is_ajax_or_rest_request() ){
				//Remove unnecessary Dashboard metaboxes
				if ( $this->get_option('unnecessary_metaboxes') ){
					add_action('wp_dashboard_setup', array($this, 'remove_dashboard_metaboxes'));
				}

				add_action('wp_dashboard_setup', array($this, 'ataglance_metabox'));
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

				if ( $this->get_option('performance_metabox') || $this->is_dev() ){ //Devs always see the performance metabox
					add_action('wp_dashboard_setup', array($this, 'performance_metabox'));
				}

				if ( nebula()->get_option('design_reference_metabox') ){
					add_action('wp_dashboard_setup', array($this, 'design_metabox'));
				}

				if ( current_user_can('publish_pages') && $this->get_option('hubspot_portal') && $this->get_option('hubspot_api') ){ //Editor or above (and Hubspot API/Portal)
					add_action('wp_dashboard_setup', array($this, 'hubspot_metabox'));
				}
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
			wp_add_dashboard_widget('nebula_ataglance', '<img src="' . get_site_icon_url(32, get_theme_file_uri('/assets/img/meta') . '/favicon-32x32.png') . '" style="float: left; width: 20px; margin-right: 3px;" loading="lazy" />&nbsp;' . get_bloginfo('name'), array($this, 'dashboard_nebula_ataglance'));
		}

		public function dashboard_nebula_ataglance(){
			$this->timer('Nebula At-a-Glance Dashboard');
			global $wp_version;
			global $wp_post_types;

			echo '<ul class="nebula-fa-ul">';
			echo '<li><i class="fas fa-fw fa-globe"></i> <a href="' . home_url('/') . '" target="_blank" rel="noopener">' . home_url('/') . '</a></li>';

			//Address
			if ( $this->get_option('street_address') ){
				echo '<li><i class="fas fa-fw fa-map-marker"></i> <a href="https://www.google.com/maps/place/' . $this->full_address(1) . '" target="_blank" rel="noopener">' . $this->full_address() . '</a></li>';
			}

			//Open/Closed
			if ( $this->has_business_hours() ){
				$open_closed = ( $this->business_open() )? '<strong style="color: green;">Open</strong>' : '<strong>Closed</strong>';
				echo '<li><i class="far fa-fw fa-clock"></i> Currently ' . $open_closed . '</li>';
			}

			//WordPress Version
			echo '<li><i class="fab fa-fw fa-wordpress"></i> <a href="https://codex.wordpress.org/WordPress_Versions" target="_blank" rel="noopener">WordPress</a> <strong>' . $wp_version . '</strong></li>';

			//Nebula Version
			echo '<li><i class="far fa-fw fa-star"></i> <a href="https://gearside.com/nebula?utm_campaign=nebula&utm_medium=nebula&utm_source=' . urlencode(get_bloginfo('name')) . '&utm_content=at+a+glance+version' . $this->get_user_info('user_email', array('prepend' => '&nv-email=')) . '" target="_blank" rel="noopener">Nebula</a> <strong><a href="https://github.com/chrisblakley/Nebula/compare/master@{' . date('Y-m-d', $this->version('utc')) . '}...master" target="_blank">' . $this->version('raw') . '</a></strong> <small title="' . human_time_diff($this->version('utc')) . ' ago" style="cursor: help;">(Committed: ' . $this->version('date') . ')</small></li>';

			//Child Theme
			if ( is_child_theme() ){
				echo '<li><i class="fas fa-fw fa-child"></i><a href="themes.php">Child theme</a> active <small>(' . get_option('stylesheet') . ')</small></li>';
			}

			//Multisite (and Super Admin detection)
			if ( is_multisite() ){
				$network_admin_link = '';
				if ( is_super_admin() ){
					$network_admin_link = ' <small><a href="' . network_admin_url() . '">(Network Admin)</a></small></li>';
				}
				echo '<li><i class="fas fa-fw fa-cubes"></i> Multisite' . $network_admin_link;
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
						$post_icon_img = '<i class="fas fa-fw fa-thumbtack"></i>';
						break;
					case ('page'):
						$post_icon_img = '<i class="fas fa-fw fa-file-alt"></i>';
						break;
					case ('wp_block'):
						$post_icon_img = '<i class="far fa-fw fa-clone"></i>';
						break;
					case ('wpcf7_contact_form'):
						$post_icon_img = '<i class="fas fa-fw fa-envelope"></i>';
						break;
					default:
						$post_icon = $wp_post_types[$post_type]->menu_icon;
						$post_icon_img = '<i class="fas fa-fw fa-thumbtack"></i>';
						if ( !empty($post_icon) ){
							$post_icon_img = '<img src="' . $post_icon . '" style="width: 16px; height: 16px;" loading="lazy" />';
							if ( strpos('dashicons-', $post_icon) >= 0 ){
								$post_icon_img = '<i class="dashicons-before ' . $post_icon . '"></i>';
							}
						}
						break;
				}
				echo '<li>' . $post_icon_img . ' <a href="edit.php?post_type=' . $post_type . '"><strong>' . $count_posts->publish . '</strong> ' . $labels_plural . '</a></li>';
			}

			//Earliest post
			$earliest_post = get_transient('nebula_earliest_post');
			if ( empty($earliest_post) || $this->is_debug() ){
				$earliest_post = new WP_Query(array('post_type' => 'any', 'post_status' => 'publish', 'showposts' => 1, 'orderby' => 'publish_date', 'order' => 'ASC'));
				set_transient('nebula_earliest_post', $earliest_post, WEEK_IN_SECONDS); //This transient is deleted when posts are added/updated, so this could be infinitely long.
			}
			while ( $earliest_post->have_posts() ){ $earliest_post->the_post();
				echo '<li><i class="far fa-fw fa-calendar"></i> Earliest: <span title="' . human_time_diff(strtotime(get_the_date() . ' ' . get_the_time())) . ' ago" style="cursor: help;"><strong>' . get_the_date() . '</strong> @ <strong>' . get_the_time() . '</strong></span></li>';
			}
			wp_reset_postdata();

			//Last updated
			$latest_post = get_transient('nebula_latest_post');
			if ( empty($latest_post) || $this->is_debug() ){
				$latest_post = new WP_Query(array('post_type' => 'any', 'showposts' => 1, 'orderby' => 'modified', 'order' => 'DESC'));
				set_transient('nebula_latest_post', $latest_post, HOUR_IN_SECONDS*12); //This transient is deleted when posts are added/updated, so this could be infinitely long.
			}
			while ( $latest_post->have_posts() ){ $latest_post->the_post();
				echo '<li><i class="far fa-fw fa-calendar"></i> Updated: <span title="' . human_time_diff(strtotime(get_the_modified_date())) . ' ago" style="cursor: help;"><strong>' . get_the_modified_date() . '</strong> @ <strong>' . get_the_modified_time() . '</strong></span>
					<small style="display: block;"><i class="far fa-fw fa-file-alt"></i> <a href="' . get_permalink() . '">' . $this->excerpt(array('text' => get_the_title(), 'words' => 5, 'more' => false, 'ellipsis' => true)) . '</a> (' . get_the_author() . ')</small>
				</li>';
			}
			wp_reset_postdata();

			//Revisions
			$revision_count = ( WP_POST_REVISIONS == -1 )? 'all' : WP_POST_REVISIONS;
			$revision_style = ( $revision_count === 0 )? 'style="color: red;"' : '';
			$revisions_plural = ( $revision_count === 1 )? 'revision' : 'revisions';
			echo '<li><i class="fas fa-fw fa-history"></i> Storing <strong ' . $revision_style . '>' . $revision_count . '</strong> ' . $revisions_plural . '.</li>';

			//Plugins
			$all_plugins = get_transient('nebula_count_plugins');
			if ( empty($all_plugins) || $this->is_debug() ){
				$all_plugins = get_plugins();
				set_transient('nebula_count_plugins', $all_plugins, HOUR_IN_SECONDS*36);
			}
			$all_plugins_plural = ( count($all_plugins) === 1 )? 'Plugin' : 'Plugins';
			$active_plugins = get_option('active_plugins', array());
			echo '<li><i class="fas fa-fw fa-plug"></i> <a href="plugins.php"><strong>' . count($all_plugins) . '</strong> ' . $all_plugins_plural . '</a> installed <small>(' . count($active_plugins) . ' active)</small></li>';

			//Users
			$user_count = get_transient('nebula_count_users');
			if ( empty($user_count) || $this->is_debug() ){
				$user_count = count_users();
				set_transient('nebula_count_users', $user_count, HOUR_IN_SECONDS*36);
			}
			$users_icon = 'users';
			$users_plural = 'Users';
			if ( $user_count['total_users'] === 1 ){
				$users_plural = 'User';
				$users_icon = 'user';
			}
			echo '<li><i class="fas fa-fw fa-' . $users_icon . '"></i> <a href="users.php">' . $user_count['total_users'] . ' ' . $users_plural . '</a> <small>(' . $this->online_users('count') . ' currently active)</small></li>';

			//Comments
			if ( $this->get_option('comments') && $this->get_option('disqus_shortname') == '' ){
				$comments_count = wp_count_comments();
				$comments_plural = ( $comments_count->approved === 1 )? 'Comment' : 'Comments';
				echo '<li><i class="fas fa-fw fa-comments"></i> <strong>' . $comments_count->approved . '</strong> ' . $comments_plural . '</li>';
			} else {
				if ( !$this->get_option('comments') ){
					echo '<li><i class="far fa-fw fa-comment"></i> Comments disabled <small>(via <a href="themes.php?page=nebula_options&tab=functions&option=comments">Nebula Options</a>)</small></li>';
				} else {
					echo '<li><i class="far fa-fw fa-comments"></i> Using <a href="https://' . $this->get_option('disqus_shortname') . '.disqus.com/admin/moderate/" target="_blank" rel="noopener">Disqus comment system</a>.</li>';
				}
			}

			//Global Admin Bar
			if ( !$this->get_option('admin_bar') ){
				echo '<li><i class="far fa-fw fa-bars"></i> Admin Bar disabled <small>(for all users via <a href="themes.php?page=nebula_options&tab=functions&option=admin_bar">Nebula Options</a>)</small></li>';
			}

			echo '</ul>';

			do_action('nebula_ataglance');
			$this->timer('Nebula At-a-Glance Dashboard', 'end');
		}

		//Current User metabox
		public function current_user_metabox(){
			$headshotURL = esc_attr(get_the_author_meta('headshot_url', get_current_user_id()));
			$headshot_thumbnail = str_replace('.jpg', '-150x150.jpg', $headshotURL);

			if ( $headshot_thumbnail ){
				$headshot_html = '<img src="' . esc_attr($headshot_thumbnail) . '" style="float: left; max-width: 20px; border-radius: 100px;" loading="lazy" />&nbsp;';
			} else {
				$headshot_html = '<i class="fas fa-fw fa-user"></i>&nbsp;';
			}

			wp_add_dashboard_widget('nebula_current_user', $headshot_html . $this->get_user_info('display_name'), array($this, 'dashboard_current_user'));
		}

		public function dashboard_current_user(){
			$this->timer('Nebula Current User Dashboard');
			$user_info = get_userdata(get_current_user_id());

			echo '<ul class="nebula-fa-ul">';
			//Company
			$company = '';
			if ( get_the_author_meta('jobcompany', $user_info->ID) ){
				$company = get_the_author_meta('jobcompany', $user_info->ID);
				if ( get_the_author_meta('jobcompanywebsite', $user_info->ID) ){
					$company = '<a href="' . get_the_author_meta('jobcompanywebsite', $user_info->ID) . '?utm_campaign=nebula&utm_medium=nebula&utm_source=' . urlencode(get_bloginfo('name')) . '&utm_content=user+metabox+job+title" target="_blank" rel="noopener">' . $company . '</a>';
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
				echo '<li><i class="far fa-fw fa-building"></i> ' . $job_title . $company . '</li>';
			}

			//Location
			if ( get_the_author_meta('usercity', $user_info->ID) && get_the_author_meta('userstate', $user_info->ID) ){
				echo '<li><i class="fas fa-fw fa-map-marker"></i> <strong>' . get_the_author_meta('usercity', $user_info->ID) . ', ' . get_the_author_meta('userstate', $user_info->ID) . '</strong></li>';
			}

			//Email
			echo '<li><i class="far fa-fw fa-envelope"></i> Email: <strong>' . $user_info->user_email . '</strong></li>';

			if ( get_the_author_meta('phonenumber', $user_info->ID) ){
				echo '<li><i class="fas fa-fw fa-phone"></i> Phone: <strong>' . get_the_author_meta('phonenumber', $user_info->ID) . '</strong></li>';
			}

			echo '<li><i class="far fa-fw fa-user"></i> Username: <strong>' . $user_info->user_login . '</strong></li>';
			echo '<li><i class="fas fa-fw fa-info-circle"></i> ID: <strong>' . $user_info->ID . '</strong></li>';

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
			echo '<li><i class="fas fa-fw ' . $fa_role . '"></i> Role: <strong class="admin-user-info admin-user-role">' . $user_role . '</strong></li>';

			//User's posts
			$your_posts = get_transient('nebula_count_posts_user_' . $user_info->ID);
			if ( empty($your_posts) || $this->is_debug() ){
				$your_posts = count_user_posts($user_info->ID);
				set_transient('nebula_count_posts_user_' . $user_info->ID, $your_posts, DAY_IN_SECONDS); //24 hour cache
			}
			echo '<li><i class="fas fa-fw fa-thumbtack"></i> Your posts: <strong>' . $your_posts . '</strong></li>';

			if ( $this->get_option('device_detection') ){
				//Device
				if ( $this->is_desktop() ){
					if ( 1==2 ){//@todo "Nebula" 0: Check battery percentage (somehow... maybe cookie?) for laptop/desktop
						echo '<li><i class="fas fa-fw fa-laptop"></i> Device: <strong>Laptop</strong></li>';
					} else {
						echo '<li><i class="fas fa-fw fa-desktop"></i> Device: <strong>Desktop</strong></li>';
					}
				} elseif ( $this->is_tablet() ){
					echo '<li><i class="fas fa-fw fa-tablet-alt"></i> Device: <strong>' . $this->get_device('full') . '</strong> <small>(Tablet)</small></li>';
				} else {
					echo '<li><i class="fas fa-fw fa-mobile-alt"></i> Device: <strong>' . $this->get_device('full') . '</strong> <small>(Mobile)</small></li>';
				}

				//Operating System
				switch ( strtolower($this->get_os('name')) ){
					case 'windows':
						$os_icon = 'fab fa-windows';
						break;
					case 'mac':
					case 'ios':
						$os_icon = 'fab fa-apple';
						break;
					case 'linux':
						$os_icon = 'fab fa-linux';
						break;
					case 'android':
						$os_icon = 'fab fa-android';
						break;
					default:
						$os_icon = 'fas fa-power-off';
						break;
				}
				echo '<li><i class="fa-fw ' . $os_icon . '"></i> OS: <strong>' . $this->get_os('full') . '</strong></li>';

				//Browser
				switch ( str_replace(array('mobile', ' '), '', strtolower($this->get_browser('name'))) ){
					case 'edge':
						$browser_icon = 'fab fa-edge';
						break;
					case 'safari':
						$browser_icon = 'fab fa-safari';
						break;
					case 'internet explorer':
						$browser_icon = 'fab fa-internet-explorer';
						break;
					case 'firefox':
						$browser_icon = 'fab fa-firefox';
						break;
					case 'chrome':
					case 'chrome mobile':
						$browser_icon = 'fab fa-chrome';
						break;
					case 'opera':
						$browser_icon = 'fab fa-opera';
						break;
					default:
						$browser_icon = 'fas fa-globe';
						break;
				}
				echo '<li><i class="fa-fw ' . $browser_icon . '"></i> Browser: <strong>' . $this->get_browser('full') . '</strong></li>';
			}

			//IP Address
			echo '<li>';
			if ( $this->get_ip_address() === '72.43.235.106' ){
				echo '<img src="' . get_template_directory_uri() . '/assets/img/phg/phg-symbol.png" style="max-width: 14px;" loading="lazy" />';
			} else {
				echo '<i class="fas fa-fw fa-globe"></i>';
			}
			echo ' IP Address: <a href="http://whatismyipaddress.com/ip/' . $this->get_ip_address() . '" target="_blank" rel="noopener"><strong class="admin-user-info admin-user-ip">' . $this->get_ip_address() . '</strong></a>';
			echo '</li>';

			//Multiple locations
			if ( $this->user_single_concurrent($user_info->ID) > 1 ){
				echo '<li><i class="fas fa-fw fa-users"></i> Active in <strong>' . $this->user_single_concurrent($user_info->ID) . ' locations</strong>.</li>';
			}

			//User Admin Bar
			if ( !get_user_option('show_admin_bar_front', $user_info->ID) ){
				echo '<li><i class="fas fa-fw fa-bars"></i> Admin Bar disabled <small>(for just you via <a href="profile.php">User Profile</a>)</small></li>';
			}

			do_action('nebula_user_metabox');
			echo '</ul>';

			echo '<p><small><em><a href="profile.php"><i class="fas fa-fw fa-pencil-alt"></i> Manage your user information</a></em></small></p>';
			$this->timer('Nebula Current User Dashboard', 'end');
		}

		//Administrative metabox
		public function administrative_metabox(){
			wp_add_dashboard_widget('nebula_administrative', 'Administrative', array($this, 'dashboard_administrative'));
		}

		//Administrative metabox content
		public function dashboard_administrative(){
			$this->timer('Nebula Administrative Dashboard');
			$third_party_tools = $this->third_party_tools();

			echo '<div class="nebula-metabox-row"><div class="nebula-metabox-col">';
			echo '<ul class="nebula-fa-ul" style="margin-top: 0;">';
			foreach ( $third_party_tools['administrative'] as $tool ){
				echo '<li>' . $tool['icon'] . ' <a href="' . $tool['url'] . '" target="_blank" rel="noopener">' . $tool['name'] . '</a></li>';
			}

			do_action('nebula_administrative_metabox');

			echo '</ul>';
			echo '<p><small><em>Manage administrative links in <strong><a href="themes.php?page=nebula_options&tab=administration">Nebula Options</a></strong>.</em></small></p>';
			echo '</div>';

			echo '<div style="max-width: 50%;">';
			echo '<h3>Social</h3>';
			echo '<ul class="nebula-fa-ul">';
			foreach ( $third_party_tools['social'] as $tool ){
				echo '<li>' . $tool['icon'] . ' <a href="' . $tool['url'] . '" target="_blank" rel="noopener">' . $tool['name'] . '</a></li>';
			}

			do_action('nebula_social_metabox');

			echo '</ul>';
			echo '<p><small><em>Manage social links in <strong><a href="themes.php?page=nebula_options&filter=social">Nebula Options</a></strong>.</em></small></p>';
			echo '</div></div>';

			$this->timer('Nebula Administrative Dashboard', 'end');
		}

		//Pinckney Hugo Group metabox
		public function phg_metabox(){
			wp_add_dashboard_widget('nebula_phg', $this->pinckneyhugogroup(array('linked' => false)), array($this, 'dashboard_phg'));
		}

		//Pinckney Hugo Group metabox content
		public function dashboard_phg(){
			echo '<a href="http://www.pinckneyhugo.com?utm_campaign=nebula&utm_medium=nebula&utm_source=' . urlencode(get_bloginfo('name')) . '&utm_content=phg+dashboard+metabox+photo' . $this->get_user_info('user_email', array('prepend' => '&nv-email=')) . '" target="_blank" rel="noopener"><img src="' . get_template_directory_uri() . '/assets/img/phg/phg-building.jpg" style="width: 100%;" loading="lazy" /></a>';
			echo '<ul class="nebula-fa-ul">';
			echo '<li><i class="fas fa-fw fa-map-marker"></i> <a href="https://www.google.com/maps/place/760+West+Genesee+Street+Syracuse+NY+13204" target="_blank" rel="noopener">760 West Genesee Street, Syracuse, NY 13204</a></li>';
			echo '<li><i class="fas fa-fw fa-link"></i> <a href="http://www.pinckneyhugo.com?utm_campaign=nebula&utm_medium=nebula&utm_source=' . urlencode(get_bloginfo('name')) . '&utm_content=phg+dashboard+metabox+textlink' . $this->get_user_info('user_email', array('prepend' => '&nv-email=')) . '" target="_blank">PinckneyHugo.com</a></li>';
			echo '<li><i class="fas fa-fw fa-phone"></i> (315) 478-6700</li>';
			echo '</ul>';
		}

		//Extension skip list for both To Do Manager and Developer Metabox
		public function skip_extensions(){
			return array('.jpg', '.jpeg', '.png', '.gif', '.ico', '.tiff', '.psd', '.ai', '.apng', '.bmp', '.otf', '.ttf', '.ogv', '.flv', '.fla', '.mpg', '.mpeg', '.avi', '.mov', '.woff', '.eot', '.mp3', '.mp4', '.wmv', '.wma', '.aiff', '.zip', '.zipx', '.rar', '.exe', '.dmg', '.csv', '.swf', '.pdf', '.pdfx', '.pem', '.ppt', '.pptx', '.pps', '.ppsx', '.bak'); //Would it be faster to list allowed filetypes instead?
		}

		//TODO Metabox
		public function todo_metabox(){
			wp_add_dashboard_widget('todo_manager', '<i class="fas fa-fw fa-check-square"></i>&nbsp;To-Do Manager', array($this, 'todo_metabox_content'));
		}

		//TODO metabox content
		public function todo_metabox_content(){
			$this->timer('Nebula Todo Dashboard');
			do_action('nebula_todo_manager');

			$todo_items = get_transient('nebula_todo_items');
			if ( empty($todo_items) || $this->is_debug() ){
				$todo_items = array(
					'parent' => $this->todo_search_files(get_template_directory()),
				);

				if ( is_child_theme() ){
					$todo_items['child'] = $this->todo_search_files(get_stylesheet_directory());
				}

				$todo_items = apply_filters('nebula_todo_items', $todo_items); //Add locations to the Todo Manager
				set_transient('nebula_todo_items', $todo_items, MINUTE_IN_SECONDS*30); //30 minute cache
			}

			$file_count = 0;
			$instance_count = 0;
			?>
				<p class="todoresults_title">
					<strong>Active @todo Comments</strong> <a class="todo_help_icon" href="https://gearside.com/wordpress-dashboard-todo-manager/?utm_campaign=nebula&utm_medium=nebula&utm_source=<?php echo urlencode(get_bloginfo('name')); ?>&utm_content=todo+metabox<?php echo $this->get_user_info('user_email', array('prepend' => '&nv-email=')); ?>" target="_blank" rel="noopener"><i class="far fw fa-question-circle"></i> Documentation &raquo;</a>
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
						if ( ($todo['priority'] === 'empty' || $todo['priority'] > 0) ){ //Only count todos with a non-hidden priority
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
				echo '<p style="margin-top: 50px; text-align: center; font-size: 24px; line-height: 28px; opacity: 0.1;"><i class="far fa-smile" style="font-size: 32px;"></i><br />Nothing!</p>';
			}
			?>
				</div><!--/todo_results-->
				<p>
					<i class="far fa-fw fa-file-code"></i> Found <strong><?php echo $file_count; ?> files</strong> with <strong><?php echo $instance_count; ?> @todo comments</strong>.

					<?php if ( $this->get_option('github_url') ): ?>
						<br /><i class="fab fa-fw fa-github"></i> <a href="<?php echo $this->get_option('github_url'); ?>/issues?q=is%3Aissue+is%3Aopen+sort%3Aupdated-desc" target="_blank">Check the Github repository</a> for additional issues.
					<?php endif; ?>
				</p>
			<?php
			$this->timer('Nebula Todo Dashboard', 'end');
		}

		public function todo_search_files($directory=null){
			//@todo "Nebula" 0: Use null coalescing operator here
			if ( empty($directory) ){
				$directory = get_template_directory();
			}

			$these_todos = array();

			foreach ( $this->glob_r($directory . '/*') as $todo_file ){
				if ( is_file($todo_file) ){
					$todo_skipFilenames = array('README.md', 'debug_log', 'error_log', '/vendor', 'resources/');

					if ( !$this->contains($todo_file, $this->skip_extensions()) && !$this->contains($todo_file, $todo_skipFilenames) ){
						foreach ( file($todo_file) as $todo_lineNumber => $todo_line ){
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
			wp_add_dashboard_widget('phg_developer_info', 'Developer Information', array($this, 'dashboard_developer_info'));
		}

		//Developer Info Metabox content
		public function dashboard_developer_info(){
			$this->timer('Nebula Developer Dashboard');
			do_action('nebula_developer_info');
			echo '<ul class="nebula-fa-ul serverdetections">';

			//Domain
			//@todo "Nebula" 0: Use null coalescing operator here if possible
			$domain = $this->url_components('domain');
			if ( empty($domain) ){
				$domain = '<small>(None)</small>';
			}
			echo '<li><i class="fas fa-fw fa-info-circle"></i> <a href="http://whois.domaintools.com/' . $_SERVER['SERVER_NAME'] . '" target="_blank" rel="noopener" title="WHOIS Lookup">Domain</a>: <strong>' . $domain . '</strong></li>';

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

				echo '<li><i class="far fa-fw fa-hdd"></i> Host: <strong>' . top_domain_name(gethostname()) . '</strong>';
				if ( !empty($dnsrecord[0]['target']) ){
					echo ' <small>(' . top_domain_name($dnsrecord[0]['target']) . ')</small>';
				}
				echo '</li>';
			}

			//Server IP address (and connection security)
			$secureServer = '';
			if ( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] === 443 ){
				$secureServer = '<small class="secured-connection"><i class="fas fa-fw fa-lock"></i>Secured Connection</small>';
			}
			echo '<li><i class="fas fa-fw fa-upload"></i> Server IP: <strong><a href="http://whatismyipaddress.com/ip/' . $_SERVER['SERVER_ADDR'] . '" target="_blank" rel="noopener">' . $_SERVER['SERVER_ADDR'] . '</a></strong> ' . $secureServer . '</li>';

			//Server operating system
			if ( strpos(strtolower(PHP_OS), 'linux') !== false ){
				$php_os_icon = 'fa-linux';
			} elseif ( strpos(strtolower(PHP_OS), 'windows') !== false ){
				$php_os_icon = 'fa-windows';
			} else {
				$php_os_icon = 'fa-upload';
			}
			echo '<li><i class="fab fa-fw ' . $php_os_icon . '"></i> Server OS: <strong>' . PHP_OS . '</strong></li>';

			//Server software
			$server_software = $_SERVER['SERVER_SOFTWARE'];
			if ( strlen($server_software) > 10 ){
				$server_software = strtok($_SERVER['SERVER_SOFTWARE'], ' '); //Shorten to until the first space
			}
			echo '<li><i class="fas fa-fw fa-server"></i> Server Software: <strong title="' . $_SERVER['SERVER_SOFTWARE'] . '">' . $server_software . '</strong></li>';

			//MySQL version
			if ( function_exists('mysqli_get_client_version') ){
				$mysql_version = mysqli_get_client_version();
				echo '<li><i class="fas fa-fw fa-database"></i> MySQL Version: <strong title="Raw: ' . $mysql_version . '">' . floor($mysql_version/10000) . '.' . floor(($mysql_version%10000)/100) . '.' . ($mysql_version%10000)%100 . '</strong></li>';
			}

			//PHP version
			$php_version_color = 'inherit';
			$php_version_info = '';
			$php_version_cursor = 'normal';
			$php_version_lifecycle = $this->php_version_support();
			if ( $php_version_lifecycle['lifecycle'] === 'security' ){
				$php_version_color = '#ca8038';
				$php_version_info = 'This version is nearing end of life. Security updates end on ' . date_i18n('F j, Y', $php_version_lifecycle['end']) . '.';
				$php_version_cursor = 'help';
			} elseif ( $php_version_lifecycle['lifecycle'] === 'end' ){
				$php_version_color = '#ca3838';
				$php_version_info = 'This version no longer receives security updates! End of life occurred on ' . date_i18n('F j, Y', $php_version_lifecycle['end']) . '.';
				$php_version_cursor = 'help';
			}
			echo '<li><i class="fas fa-fw fa-wrench"></i> PHP Version: <strong style="color: ' . $php_version_color . '; cursor: ' . $php_version_cursor . ';" title="' . $php_version_info . '">' . PHP_VERSION . '</strong></li>';

			//PHP memory limit
			echo '<li><i class="fas fa-fw fa-cogs"></i> PHP Memory Limit: <strong>' . WP_MEMORY_LIMIT . '</strong></li>';

			//Theme directory size(s)
			if ( is_child_theme() ){
				$nebula_parent_size = get_transient('nebula_directory_size_parent_theme');
				if ( empty($nebula_parent_size) || $this->is_debug() ){
					$nebula_parent_size = $this->foldersize(get_template_directory());
					set_transient('nebula_directory_size_parent_theme', $nebula_parent_size, DAY_IN_SECONDS);
				}

				$nebula_child_size = get_transient('nebula_directory_size_child_theme');
				if ( empty($nebula_child_size) || $this->is_debug() ){
					$nebula_child_size = $this->foldersize(get_stylesheet_directory());
					set_transient('nebula_directory_size_child_theme', $nebula_child_size, DAY_IN_SECONDS);
				}

				echo '<li><i class="fas fa-code"></i> Parent theme directory size: <strong>' . round($nebula_parent_size/1048576, 2) . 'mb</strong> </li>';
				echo '<li><i class="fas fa-code"></i> Child theme directory size: <strong>' . round($nebula_child_size/1048576, 2) . 'mb</strong> </li>';
			} else {
				$nebula_size = get_transient('nebula_directory_size_theme');
				if ( empty($nebula_size) || $this->is_debug() ){
					$nebula_size = $this->foldersize(get_stylesheet_directory());
					set_transient('nebula_directory_size_theme', $nebula_size, DAY_IN_SECONDS);
				}
				echo '<li><i class="fas fa-code"></i> Theme directory size: <strong>' . round($nebula_size/1048576, 2) . 'mb</strong> </li>';
			}

			do_action('nebula_dev_dashboard_directories');

			//Uploads directory size (and max upload size)
			$upload_dir = wp_upload_dir();
			$uploads_size = get_transient('nebula_directory_size_uploads');
			if ( empty($uploads_size) || $this->is_debug() ){
				$uploads_size = $this->foldersize($upload_dir['basedir']);
				set_transient('nebula_directory_size_uploads', $uploads_size, HOUR_IN_SECONDS*36);
			}

			if ( function_exists('wp_max_upload_size') ){
				$upload_max = '<small>(Max upload: <strong>' . strval(round((int) wp_max_upload_size()/(1024*1024))) . 'mb</strong>)</small>';
			} else if ( ini_get('upload_max_filesize') ){
				$upload_max = '<small>(Max upload: <strong>' . ini_get('upload_max_filesize') . '</strong>)</small>';
			} else {
				$upload_max = '';
			}
			echo '<li><i class="fas fa-fw fa-images"></i> Uploads directory size: <strong>' . round($uploads_size/1048576, 2) . 'mb</strong> ' . $upload_max . '</li>';

			//Service Worker
			if ( $this->get_option('service_worker') ){
				if ( !is_ssl() ){
					echo '<li><i class="fas fa-fw fa-microchip" style="color: red;"></i> <strong>Not</strong> using service worker. No SSL.</li>';
				} elseif ( !file_exists($this->sw_location(false)) ){
					echo '<li><i class="fas fa-fw fa-microchip" style="color: red;"></i> <strong>Not</strong> using service worker. Service worker file does not exist.</li>';
				} else {
					echo '<li><i class="fas fa-fw fa-microchip"></i> Using service worker</li>';
				}
			}

			//Initial installation date
			function initial_install_date(){
				$nebula_initialized = nebula()->get_option('initialized'); //Keep this as nebula() because it is a nested function, so $this is scoped differently here.
				if ( !empty($nebula_initialized) && $nebula_initialized < getlastmod() ){
					$install_date = '<span title="' . human_time_diff($nebula_initialized) . ' ago" style="cursor: help;"><strong>' . date_i18n('F j, Y', $nebula_initialized) . '</strong> <small>@</small> <strong>' . date('g:ia', $nebula_initialized) . '</strong></span>';
				} else { //Use the last modified time of the admin page itself
					$install_date = '<span title="' . human_time_diff(getlastmod()) . ' ago" style="cursor: help;"><strong>' . date_i18n("F j, Y", getlastmod()) . '</strong> <small>@</small> <strong>' . date("g:ia", getlastmod()) . '</strong></span>';
				}
				return $install_date;
			}
			echo '<li><i class="far fa-fw fa-calendar"></i> Installed: ' . initial_install_date() . '</li>';

			$latest_file = $this->last_modified();
			echo '<li><i class="far fa-fw fa-calendar"></i> <span title="' . $latest_file['path'] . '" style="cursor: help;">Modified:</span> <span title="' . human_time_diff($latest_file['date']) . ' ago" style="cursor: help;"><strong>' . date_i18n("F j, Y", $latest_file['date']) . '</strong> <small>@</small> <strong>' . date("g:ia", $latest_file['date']) . '</strong></span></li>';

			//SCSS last processed date
			if ( $this->get_data('scss_last_processed') ){
				echo '<li><i class="fab fa-fw fa-sass"></i> Sass Processed: <span title="' . human_time_diff($this->get_data('scss_last_processed')) . ' ago" style="cursor: help;"><strong>' . date_i18n("F j, Y", $this->get_data('scss_last_processed')) . '</strong> <small>@</small> <strong>' . date("g:i:sa", $this->get_data('scss_last_processed')) . '</strong></span></li>';
			}
			echo '</ul>';

			//Directory search
			$directory_search_options = array('uploads' => '<option value="uploads">Uploads</option>');
			if ( is_child_theme() ){
				$directory_search_options['child'] = '<option value="child" selected="selected">Child Theme</option>';
				$directory_search_options['parent'] = '<option value="parent">Parent Theme</option>';
			} else {
				$directory_search_options['theme'] = '<option value="theme" selected="selected">Theme</option>';
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

			echo '<form id="theme" class="searchfiles"><i id="searchprogress" class="fas fa-fw fa-search"></i> <input class="findterm" type="text" placeholder="Search files" /><select class="searchdirectory">';
			foreach ( $all_directory_search_options as $name => $option_html ){
				echo $option_html;
			}
			echo '</select><input class="searchterm button button-primary" type="submit" value="Search" /></form>';
			echo '<div class="search_results"></div>';
			$this->timer('Nebula Developer Dashboard', 'end');
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

		//Search theme or plugin files via Developer Information Metabox
		public function search_theme_files(){
			if ( !wp_verify_nonce($_POST['nonce'], 'nebula_ajax_nonce') ){ wp_die('Permission Denied. Refresh and try again.'); }

			ini_set('max_execution_time', 120);
			ini_set('memory_limit', '512M');
			$searchTerm = htmlentities(stripslashes($_POST['data'][0]['searchData']));
			$requestedDirectory = strtolower(sanitize_text_field($_POST['data'][0]['directory']));

			if ( strlen($searchTerm) < 3 ){
				echo '<p><strong>Error:</strong> Minimum 3 characters needed to search!</p>';
				wp_die();
			}

			$uploadDirectory = wp_upload_dir();

			$search_directories = array(
				'theme' => get_template_directory(),
				'parent' => get_template_directory(),
				'child' => get_stylesheet_directory(),
				'plugins' => WP_PLUGIN_DIR,
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
				echo '<p><strong>Error:</strong> Please specify a directory to search!</p>';
				wp_die();
			}

			echo '<p class="resulttext">Search results for <strong>"' . $searchTerm . '"</strong> in the <strong>' . basename($dirpath) . '</strong> directory:</p><br />';

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
								$actualLine = $this->string_limit_chars(trim(htmlentities($line)), 200);
								$actualLine = ( !empty($actualLine['is_limited']) )? trim($actualLine['text']) . '...' : $actualLine['text'];

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
			wp_add_dashboard_widget('performance_metabox', '<i class="fas fa-fw fa-stopwatch"></i> Performance', array($this, 'performance_timing'));
		}

		public function performance_timing(){
			echo '<ul class="nebula-fa-ul">';

			//New WebPageTest API Method:
			//Documentation: https://sites.google.com/a/webpagetest.org/docs/advanced-features/webpagetest-restful-apis
			$wpt_status = 'Preparing test...';
			if ( $this->get_option('webpagetest_api') ){
				$webpagetest_response = get_transient('nebula_webpagetest_response');
				$wpt_status = 'Getting stored test results from WebPageTest.org';
				if ( empty($webpagetest_response) || $this->is_debug() || isset($_GET['sass']) ){
					$webpagetest_response = $this->remote_get('https://www.webpagetest.org/runtest.php?url=' . home_url('/') . '%3Fnoga&runs=3&fvonly=1&f=json&noopt=1&noimages=1&k=' . $this->get_option('webpagetest_api'));
					if ( !is_wp_error($webpagetest_response) ){
						$webpagetest_response = json_decode($webpagetest_response['body']);
						$wpt_status = 'Testing via WebPageTest.org';
						set_transient('nebula_webpagetest_response', $webpagetest_response, MINUTE_IN_SECONDS*10);
					}
				}

				if ( !empty($webpagetest_response) && !is_wp_error($webpagetest_response) ){
					$wpt_test_json_url = $webpagetest_response->data->jsonUrl;
					if ( !empty($wpt_test_json_url) ){
						echo '<script>var wptTestJSONURL = "' . $wpt_test_json_url . '";</script>'; //Pass this URL to JS for polling
					}
				}
			}

			echo '<li id="performance-testing-status"><i class="status-icon far fa-fw fa-comment-alt"></i> <span class="label">Status:</span> <small><span class="datapoint">' . $wpt_status . '</span></small></li>';

			//Prep for an iframe timer if needed
			$home_url = ( is_ssl() )? str_replace('http://', 'https://', home_url('/')) : home_url('/'); //Sometimes the home_url() still has http even when is_ssl() true
			echo '<div id="testloadcon" data-src="' . $home_url . '" style="pointer-events: none; opacity: 0; visibility: hidden; display: none;"></div>'; //For iframe timing

			//Server Load Time (TTFB)
			echo '<li id="performance-ttfb"><i class="far fa-fw fa-clock"></i> Server response time: <strong class="datapoint" title="Calculated via PHP render time">' . timer_stop(0, 3) . ' seconds</strong> <i class="timingwarning fas fa-exclamation-triangle"></i></li>';

			//DOM Load
			echo '<li id="performance-domload"><i class="fas fa-fw fa-clock"></i> DOM Load: <strong class="datapoint"><i class="fas fa-spinner fa-spin fa-fw"></i></strong> <i class="timingwarning fas fa-exclamation-triangle"></i></li>';

			//Fully Load Time
			echo '<li id="performance-fullyloaded"><i class="fas fa-fw fa-clock"></i> Fully Loaded: <a class="speedinsight" href="http://developers.google.com/speed/pagespeed/insights/?url=' . home_url('/') . '" target="_blank" rel="noopener" title="Time is specific to your current environment and therefore may be faster or slower than average."><strong class="datapoint"><i class="fas fa-spinner fa-spin fa-fw"></i></strong></a> <i class="timingwarning fas fa-exclamation-triangle"></i></li>';

			//File Size Footprint
			echo '<li id="performance-footprint" class="hidden"><i class="fas fa-fw fa-shoe-prints"></i> Footprint: <strong class="datapoint"><i class="fas fa-spinner fa-spin fa-fw"></i></strong> <i class="timingwarning fas fa-exclamation-triangle"></i></li>';

			//Total Requests
			echo '<li id="performance-requests" class="hidden"><i class="fas fa-fw fa-list-ol"></i> Total Requests: <strong class="datapoint"><i class="fas fa-spinner fa-spin fa-fw"></i></strong> <i class="timingwarning fas fa-exclamation-triangle"></i></li>';

			//Rating (SLOW, AVERAGE, FAST)
			if ( $this->is_dev() ){
				echo '<li id="performance-rating" class="hidden"><i class="fas fa-fw fa-award"></i> Rating: <strong class="datapoint"><i class="fas fa-spinner fa-spin fa-fw"></i></strong> <i class="timingwarning fas fa-exclamation-triangle"></i></li>';
			}

			echo '</ul>';
		}

		//Add a dashboard metabox for design reference
		public function design_metabox(){
			global $wp_meta_boxes;
			wp_add_dashboard_widget('nebula_design', '<i class="fas fa-fw fa-palette"></i> Design Reference', array($this, 'dashboard_nebula_design'));
		}

		public function dashboard_nebula_design(){
			$this->timer('Nebula Design Dashboard');
			if ( $this->get_option('design_reference_link') ){
				echo '<p><i class="fas fa-fw fa-file-image"></i> <a href="' . $this->get_option('design_reference_link') . '" target="_blank">Design File(s) &raquo;</a></p>';
			}

			$notable_colors = apply_filters('nebula_notable_colors', array('$primary_color', '$secondary_color')); //Allow other themes and plugins to designate notable colors

			$notable_colors_data = array();
			foreach ( $notable_colors as $notable_color ){
				$sass_color = $this->sass_color($notable_color);
				$customizer_color = get_theme_mod(str_replace('$', 'nebula_', $notable_color));
				$hex_color = ( !empty($sass_color) )? rtrim($sass_color, ';') : $customizer_color;

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

			?>
				<div class="nebula-metabox-row">
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
				</div>
			<?php

			if ( $this->get_option('additional_design_references') ){
				echo '<p><strong>Additional Notes:</strong><br />' . $this->get_option('additional_design_references') . '</p>';
			}

			$this->timer('Nebula Design Dashboard', 'end');
		}

		//Hubspot Contacts
		public function hubspot_metabox(){
			wp_add_dashboard_widget('hubspot_contacts', '<i class="fab fa-fw fa-hubspot"></i>&nbsp;Latest Hubspot Contacts', array($this, 'hubspot_contacts_content'));
		}

		//Hubspot Contacts metabox content
		public function hubspot_contacts_content(){
			$this->timer('Nebula Hubspot Dashboard');
			do_action('nebula_hubspot_contacts');

			$hubspot_contacts_json = get_transient('nebula_hubspot_contacts');
			if ( empty($hubspot_contacts_json) ){ //No ?debug option here (because multiple calls are made to this function). Clear with a force true when needed.
				$requested_properties = '&property=' . implode('&property=', apply_filters('nebula_hubspot_metabox_properties', array('firstname', 'lastname', 'full_name', 'email', 'createdate')));
				$response = $this->remote_get('https://api.hubapi.com/contacts/v1/lists/all/contacts/recent?hapikey=' . $this->get_option('hubspot_api') . '&count=4' . $requested_properties);
				if ( is_wp_error($response) ){
					return false;
				}

				$hubspot_contacts_json = $response['body'];
				set_transient('nebula_hubspot_contacts', $hubspot_contacts_json, MINUTE_IN_SECONDS*30);
			}

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

						<li><?php echo ( $has_name )? '<i class="fas fa-fw fa-user"></i> ' : '<i class="far fa-fw fa-envelope"></i> '; ?><strong><a href="<?php echo $contact->{'profile-url'}; ?>" target="_blank"><?php echo ( $has_name )? $contact_name : $contact_email; ?></a></strong></li>

						<?php if ( $has_name ): ?>
							<li><i class="far fa-fw fa-envelope"></i> <?php echo $contact_email; ?><br /></li>
						<?php endif; ?>

						<li><i class="far fa-fw fa-<?php echo ( date('Y-m-d', $contact->addedAt/1000) === date('Y-m-d') )? 'clock' : 'calendar'; ?>"></i> <span title="<?php echo date_i18n('F j, Y @ g:ia', $contact->addedAt/1000); ?>" style="cursor: help;"><?php echo human_time_diff($contact->addedAt/1000) . ' ago'; ?></span></li>

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
			$this->timer('Nebula Hubspot Dashboard', 'end');
		}
	}
}