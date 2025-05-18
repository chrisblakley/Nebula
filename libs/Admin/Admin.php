<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

//This has to be done here because it has to be the first thing in the file and can't be conditionally loaded anymore because of the "use" token.
require_once get_template_directory() . '/inc/vendor/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

if ( !trait_exists('Admin') ){
	require_once get_template_directory() . '/libs/Admin/Automation.php';
	require_once get_template_directory() . '/libs/Admin/Dashboard.php';
	require_once get_template_directory() . '/libs/Admin/Users.php';

	trait Admin {
		use Automation {Automation::hooks as AutomationHooks;}
		use Dashboard {Dashboard::hooks as DashboardHooks;}
		use Users {Users::hooks as UsersHooks;}

		public function hooks(){
			global $pagenow;

			$this->AutomationHooks(); //Register Automation hooks
			$this->DashboardHooks(); //Register Dashboard hooks
			$this->UsersHooks(); //Register Users hooks

			//All admin pages (including AJAX requests)
			if ( $this->is_admin_page() ){
				add_filter('nebula_brain', array($this, 'admin_brain'));
				add_action('save_post', array($this, 'index_now_post'), 10, 2); //When a post is saved (or when *starting* a new post)

				add_action('save_post', array($this, 'clear_transients')); //When a post is saved (or when *starting* a new post)
				add_action('profile_update', array($this, 'clear_transients'));

				if ( isset($this->super->get['clear-transients']) ){
					add_action('init', array($this, 'clear_transients'));
				}

				add_action('admin_init', array($this, 'clear_all_w3_caches'));
				add_action('admin_init', array($this, 'nebula_allow_temp_qm'));

				add_action('upgrader_process_complete', array($this, 'theme_update_automation'), 10, 2); //Action 'upgrader_post_install' also exists.
				add_filter('auth_cookie_expiration', array($this, 'session_expire')); //This is the user auto-signout session length
				add_action('after_setup_theme', array($this, 'custom_media_display_settings'));

				add_filter('upload_mimes', array($this, 'additional_upload_mime_types'));
				add_filter('wp_check_filetype_and_ext', array($this, 'allow_svg_uploads'), 10, 4);

				add_action('_core_updated_successfully', array($this, 'log_core_wp_updates'), 10, 2); //This happens after successful WP core update
				add_action('activated_plugin', array($this, 'log_plugin_activated'), 10, 2);
				add_action('deactivated_plugin', array($this, 'log_plugin_deactivated'), 10, 2);
				add_action('upgrader_process_complete', array($this, 'log_plugin_updated'), 10, 2);

				if ( current_user_can('publish_posts') ){
					add_action('admin_action_duplicate_post_as_draft', array($this, 'duplicate_post_as_draft'));
					add_filter('post_row_actions', array($this, 'duplicate_post_link'), 10, 2);
					add_filter('page_row_actions', array($this, 'duplicate_post_link'), 10, 2);
				}

				if ( current_user_can('edit_others_posts') && $this->allow_theme_update() ){
					add_action('admin_init', array($this, 'theme_json'));
					add_filter('puc_request_update_result_theme-Nebula', array($this, 'theme_update_version_store'), 10, 2); //This hook is found in UpdateChecker.php in the filterUpdateResult() function.
					add_filter('site_transient_update_themes', array($this, 'force_nebula_theme_update'), 99, 1); //This is a core WP hook (not a plugin or library)
				}
			}

			//Non-AJAX admin pages
			if ( $this->is_admin_page() && !$this->is_background_request() ){
				add_action('admin_head', array($this, 'admin_favicon'));
				add_action('admin_head', array($this, 'admin_ga_pageview'));
				add_filter('admin_body_class', array($this, 'admin_body_classes'));

				remove_action('admin_enqueue_scripts', 'wp_auth_check_load'); //Disable the logged-in monitoring modal

				add_action('admin_notices', array($this, 'check_parent_theme_file_changes'));

				if ( current_user_can('edit_others_posts') && $this->is_warning_level('on') ){
					add_action('admin_notices', array($this, 'show_admin_notices'));
				}

				add_action('admin_init', array($this, 'additional_admin_color_schemes'));
				add_action('user_register', array($this, 'set_default_admin_color')); //New users will default to Brand admin color scheme

				//Add ID column to posts and pages
				if ( current_user_can('publish_posts') ){
					add_filter('manage_posts_columns', array($this, 'id_column_head')); //Includes custom post types
					add_filter('manage_pages_columns', array($this, 'id_column_head'));

					//Loop through all post types to make ID column sortable
					add_action('admin_head', function(){
						if ( str_contains(get_current_screen()->id, 'edit') ){
							foreach ( get_post_types(array(), 'names') as $post_type ){
								add_filter('manage_edit-' . $post_type . '_sortable_columns', array($this, 'id_sortable_column'));
							}
						}
					});
				}

				//Output post IDs
				add_action('manage_posts_custom_column', array($this, 'id_column_content'), 10, 2);
				add_action('manage_pages_custom_column', array($this, 'id_column_content'), 10, 2);

				add_action('pre_get_posts', array($this, 'id_column_orderby')); //Handles the order when the ID column is sorted

				//CF7 Submissions Columns
				if ( is_plugin_active('contact-form-7/wp-contact-form-7.php') && $this->get_option('store_form_submissions') ){ //If CF7 is installed and active and capturing submission data is enabled
					add_filter('views_edit-nebula_cf7_submits', array($this, 'cf7_submissions_status_list_cleanup'));
					add_filter('manage_posts_columns', array($this, 'cf7_submissions_columns_head'));
					add_filter('manage_edit-nebula_cf7_submits_sortable_columns', array($this, 'cf7_submissions_columns_sortable'));
					add_action('manage_posts_custom_column', array($this, 'cf7_submissions_columns_content' ), 15, 2);
					add_action('pre_get_posts', array($this, 'cf7_submissions_columns_orderby')); //Handles the order when a user column is sorted
					add_filter('display_post_states', array($this, 'cf7_submissions_remove_post_state'), 10, 2); //Hide the private post state text on listing pages
					add_filter('post_row_actions', array($this, 'cf7_submissions_remove_quick_actions'), 10, 2);
					add_action('edit_form_top', array($this, 'cf7_submissions_back_button'));
					add_action('edit_form_after_title', array($this, 'cf7_storage_output'), 10, 1); //This is where the actual submission details are output
					add_action('admin_footer-post.php', array($this, 'add_cf7_statuses_to_dropdown'));
					add_action('admin_menu', array($this, 'add_cf7_menu_badge_count'));
					add_filter('months_dropdown_results', '__return_empty_array'); //Remove the original date month dropdown
					add_action('restrict_manage_posts', array($this, 'cf7_submissions_filters'), 10, 1);
					add_action('manage_posts_extra_tablenav', array($this, 'cf7_submissions_actions'), 10, 1);
					add_filter('parse_query', array($this, 'cf7_submissions_parse_query'), 10, 1);
				}

				//Override some Yoast settings
				if ( is_plugin_active('wordpress-seo/wp-seo.php') ){
					//Move Yoast post metabox to the bottom
					add_action('wpseo_metabox_prio', array($this, 'lower_yoast_post_metabox'));

					//Prevent indexing of authors
					if ( !$this->get_option('author_bios') ){
						add_action('admin_init', array($this, 'disable_yoast_author_indexing'));
					}

					//Remove most Yoast SEO columns
					$post_types = get_post_types(array('public' => true), 'names');
					if ( is_array($post_types) && $post_types !== array() ){
						foreach ( $post_types as $post_type ){
							add_filter('manage_edit-' . $post_type . '_columns', array($this, 'remove_yoast_columns'), 500);
						}
					}
				}

				add_filter('relevanssi_index_custom_fields', array($this, 'add_nebula_to_relevanssi'), 10, 2);

				add_filter('manage_media_columns', array($this, 'muc_column'));
				add_action('manage_media_custom_column', array($this, 'muc_value'), 10, 2);

				//Extend the WP admin posts search to include custom fields
				add_action('posts_join', array($this, 'search_custom_post_meta_join'));
				add_action('posts_where', array($this, 'search_custom_post_meta_where'));
				add_action('posts_distinct', array($this, 'search_custom_post_meta_distinct'));

				if ( $this->is_dev(true) && current_user_can('manage_options') ){
					add_action('admin_menu', array($this, 'all_settings_link'));
				}

				add_filter('admin_footer_text', array($this, 'change_admin_footer_left'));
				add_filter('update_footer', array($this, 'change_admin_footer_right'), 11);
				add_action('load-post.php', array($this, 'post_meta_boxes_setup'));
				add_action('load-post-new.php', array($this, 'post_meta_boxes_setup'));

				add_action('debug_information', array($this, 'site_health_info'));
			}

			//Login Page
			if ( $this->is_login_page() ){
				add_action('login_head', array($this, 'admin_ga_pageview'));
				add_filter('login_headerurl', array($this, 'custom_login_header_url'));
				add_filter('login_headertext', array($this, 'new_wp_login_title'));
			}

			//Disable auto curly quotes (smart quotes)
			remove_filter('the_content', 'wptexturize');
			remove_filter('the_excerpt', 'wptexturize');
			remove_filter('comment_text', 'wptexturize');
			add_filter('run_wptexturize', '__return_false');

			//Disable Admin Bar (and WP Update Notifications) for everyone but administrators (or specific users)
			if ( (($this->is_dev(true) || current_user_can('manage_options')) && $this->get_option('admin_bar')) || $this->is_admin_page() ){ //If the admin bar Nebula option is allowing it to show (or viewing an admin page)
				add_action('wp_before_admin_bar_render', array($this, 'admin_bar_modifications'), 0);
				add_action('admin_bar_menu', array($this, 'admin_bar_menus'), 800); //Add Nebula menus to Admin Bar
				add_action('get_header', array($this, 'remove_admin_bar_bump')); //TODO "Nebula" 0: Possible to remove and add directly remove action here
				add_action('wp_after_admin_bar_render', array($this, 'admin_bar_style_script_overrides'), 11);
				add_action('wp_head', array($this, 'admin_bar_warning_styles'), 11);
				add_action('admin_print_styles', array($this, 'admin_bar_warning_styles'), 11);
			} else { //Otherwise the Nebula option for admin bar is set to disable it
				show_admin_bar(false);
				add_action('wp_print_scripts', array($this, 'dequeue_admin_bar'), 9999);
				add_action('wp_print_styles', array($this, 'dequeue_admin_bar'), 9999);
				add_action('init', array($this, 'admin_only_features')); //TODO "Nebula" 0: Possible to remove and add directly remove action here
				add_filter('wp_head', array($this, 'remove_admin_bar_style_frontend'), 99);
			}

			//Disable Wordpress Core update notifications in WP Admin
			if ( !$this->get_option('wp_core_updates_notify') ){
				add_filter('pre_site_transient_update_core', '__return_null');
			}
		}

		//Add info to the brain variable for admin pages
		public function admin_brain($brain){
			$brain['site']['admin_url'] = get_admin_url();
			return $brain;
		}

		//POST to IndexNow to inform some search engines of content update
		public function index_now_post($post_id, $post){
			if ( $this->is_minimal_mode() ){return null;}

			if ( !$this->get_option('index_now') ){
				return null;
			}

			if ( $post->post_status !== 'publish' ){
				return null;
			}

			$post_url = get_permalink($post_id);
			$index_now_key = $this->index_now_get_key();

			//Define the target search engine URL
			//https://www.indexnow.org/searchengines.json
			//Bing, for example: https://www.bing.com/indexnow/meta.json
			$search_engine_urls = ['https://www.bing.com/indexnow']; //As IndexNow support grows, add other desired search engines to this list

			//Make the POST requests
			foreach ( $search_engine_urls as $search_engine_url ){
				$response = wp_remote_post($search_engine_url, [
					'method' => 'POST',
					'headers' => ['Content-Type' => 'application/json; charset=utf-8'],
					'body' => json_encode(array(
						'host' => parse_url(get_home_url(), PHP_URL_HOST),
						'key' => $index_now_key,
						'keyLocation' => trailingslashit(get_home_url()) . 'nebula-index-now-' . $index_now_key . '.txt',
						'urlList' => [$post_url]
					)),
					'data_format' => 'body'
				]);

				if ( is_wp_error($response) ){
					do_action('qm/error', 'IndexNow POST error for ' . $search_engine_url);
				} else {
					$response_code = wp_remote_retrieve_response_code($response);
					if ( $response_code >= 400 ){ //Ideal response codes are 200 and 202. 4xx level response codes indicate a problem.
						do_action('qm/error', 'IndexNow response code ' . $response_code . ' from ' . $search_engine_url); //https://www.indexnow.org/documentation
					}
				}
			};
		}

		//Force expire query transients when posts/pages are saved
		public function clear_transients(){
			if ( $this->is_minimal_mode() ){return null;}
			$this->timer('Clear Transients');

			//May 2025: Only clearing these specific Nebula transients on post save now
			//if ( class_exists('AM_Transients_Manager') ){
			//	$transient_manager = new AM_Transients_Manager(); //"PW_" changed to "AM_" in December 2021
			//	$transient_manager->delete_transients_with_expirations();
			//} else {
				//Clear post/page information and related transients
				//Note: We purposefully do *not* clear nebula_analytics_* transients to preserve their data. They are self-managed, but can be manually cleared if desired.
				//Other transients we do *not* need to clear when posts are updated: "nebula_directory_indexing", "nebula_php_timeline", "nebula_spam_domain_public_list"
				$all_transients_to_delete = apply_filters('nebula_delete_transients_on_save', array( //Allow other functions to hook in to delete transients on post save
					'nebula_autocomplete_menus', //Stores menus for the autocomplete search
					'nebula_autocomplete_categories', //Stores categories for the autocomplete search
					'nebula_autocomplete_tags', //Stores tags for the autocomplete search
					'nebula_autocomplete_authors', //Stores authors for the autocomplete search
					'nebula_all_log_files', //Stores a list of all known log files
					'nebula_file_size_monitor_list', //Stores the list of files monitored by the File Size Monitor tool
					'nebula_theme_file_changes_check',
					'nebula_theme_modified_files', //Checks if parent theme files were modified
					'nebula_cf7_submits_badge', //Stores the number of CF7 submissions today
					'nebula_todo_items', //Stores the to-do comments in theme files
					'nebula_directory_size_child_theme', //Stores the file size of the child theme directory
					'nebula_directory_size_parent_theme', //Stores the file size of the parent theme directory
					'nebula_directory_size_uploads', //Stores the file size of the uploads directory
					'nebula_directory_size_plugins', //Stores the file size of the plugins directory
					'nebula_count_users', //Counts the number of WP users
					'nebula_count_plugins', //Counts the number of WP plugins
					'nebula_latest_post', //Stores the date of the latest post
					'nebula_earliest_post', //Stores the date of the earliest post
				));

				foreach ( $all_transients_to_delete as $transient_to_delete ){
					delete_transient($transient_to_delete);
				}
			//}

			$this->timer('Clear Transients', 'end');
		}

		//Toggle allowing Query Monitor temporarily for a specific IP
		public function nebula_allow_temp_qm(){
			if ( isset($this->super->get['nebula-temp-qm']) ){
				if ( $this->super->get['nebula-temp-qm'] === 'start' ){
					set_transient('nebula_temp_qm_ip', $this->get_ip_address(), HOUR_IN_SECONDS); //This only allows one at a time
				} elseif ( $this->super->get['nebula-temp-qm'] === 'stop' ){
					delete_transient('nebula_temp_qm_ip');
				}
			}
		}

		//Pull favicon from the theme folder (Front-end calls are in includes/metagraphics.php).
		public function admin_favicon(){
			if ( $this->is_minimal_mode() ){return null;}
			$cache_buster = ( $this->is_debug() )? '?r' . random_int(100000, 999999) : '';

 			if ( has_site_icon() ){ //Prefer the Customizer icons if they exist
				echo '<link rel="shortcut icon" type="image/png" sizes="16x16" href="' . get_site_icon_url(16, get_theme_file_uri('/assets/img/meta') . '/favicon-16x16.png') . $cache_buster . '" />';
				echo '<link rel="shortcut icon" type="image/png" sizes="32x32" href="' . get_site_icon_url(32, get_theme_file_uri('/assets/img/meta') . '/favicon-32x32.png') . $cache_buster . '" />';
 			} else {
				echo '<link rel="shortcut icon" type="image/png" sizes="16x16" href="' . get_theme_file_uri('/assets/img/meta') . '/favicon-16x16.png' . $cache_buster . '" />';
				echo '<link rel="shortcut icon" type="image/png" sizes="32x32" href="' . get_theme_file_uri('/assets/img/meta') . '/favicon-32x32.png' . $cache_buster . '" />';
				echo '<link rel="shortcut icon" href="' . get_theme_file_uri('/assets/img/meta/favicon.ico') . $cache_buster . '" />';
			}
		}

		//Add classes to the admin body
		//Remember: $classes here is a string!! (Not an array like on the front-end)
		public function admin_body_classes($classes){
			$classes .= ' nebula ';

			global $current_user;
			$user_roles = $current_user->roles;
			$classes .= array_shift($user_roles);

			//Staff
			if ( $this->is_staff() ){
				$classes .= ' is-staff';
				if ( $this->is_dev() ){
					$classes .= ' staff-developer';
				} elseif ( $this->is_client() ){
					$classes .= 'staff-client';
				}
			}

			return $classes;
		}

		//Add the Brand color scheme to the admin User options
		public function additional_admin_color_schemes(){
			if ( $this->is_minimal_mode() ){return null;}
			$color_scheme_name = get_bloginfo('name');
			if ( $this->get_option('site_owner') ){
				$color_scheme_name = $this->get_option('site_owner');
			}

			//Brand (Child Theme)
			if ( is_child_theme() && file_exists(get_stylesheet_directory() . '/assets/css/admin.css') ){
				wp_admin_css_color('nebula-brand', $color_scheme_name, get_stylesheet_directory_uri() . '/assets/css/admin.css', array(
					'#222',
					'#333',
					$this->get_color('primary_color', false, '#0098d7'),
					$this->get_color('secondary_color', false, '#95d600')
				));
			}
		}

		//Set the default admin color scheme to Brand for a specified user
		public function set_default_admin_color($user_id){
			if ( $this->is_minimal_mode() ){return null;}
			if ( is_child_theme() && file_exists(get_stylesheet_directory() . '/assets/css/admin.css') ){
				wp_update_user(array(
					'ID' => $user_id,
					'admin_color' => 'nebula-brand'
				));
			}
		}

		//Disable Admin Bar (and WP Update Notifications) for everyone but administrators (or specific users)
		public function dequeue_admin_bar(){
			wp_deregister_style('admin-bar');
			wp_dequeue_script('admin-bar');
		}

		public function admin_only_features(){
			remove_action('wp_footer', 'wp_admin_bar_render', 1000); //For the front-end
		}

		//Aggregate all third-party resources into a single array
		public function third_party_resources(){
			if ( $this->is_minimal_mode() ){return null;}
			$third_party_resources = wp_cache_get('nebula_third_party_resources', 'nebula');
			if ( is_array($third_party_resources) || !empty($third_party_resources) ){ //If it is an array (meaning it has run before but did not find anything) or if it is false
				return $third_party_resources;
			}

			$this->timer('Aggregating Links to Active Third-Party Tools');

			$third_party_resources = array(
				'administrative' => array(),
				'social' => array()
			);

			//Administrative
			if ( $this->get_option('cpanel_url') ){
				$third_party_resources['administrative'][] = array(
					'name' => 'Server Control Panel',
					'icon' => '<i class="nebula-admin-fa fa-solid fa-fw fa-cogs"></i>',
					'url' => $this->get_option('cpanel_url')
				);
			}

			if ( $this->get_option('hosting_url') ){
				$third_party_resources['administrative'][] = array(
					'name' => 'Hosting',
					'icon' => '<i class="nebula-admin-fa fa-regular fa-fw fa-hdd"></i>',
					'url' => $this->get_option('hosting_url')
				);
			}

			if ( $this->get_option('dns_url') ){
				$third_party_resources['administrative'][] = array(
					'name' => 'DNS',
					'icon' => '<i class="nebula-admin-fa fa-solid fa-fw fa-map-signs"></i>',
					'url' => $this->get_option('dns_url')
				);
			}

			if ( $this->get_option('registrar_url') ){
				$third_party_resources['administrative'][] = array(
					'name' => 'Domain Registrar',
					'icon' => '<i class="nebula-admin-fa fa-solid fa-fw fa-globe"></i>',
					'url' => $this->get_option('registrar_url')
				);
			}

			if ( $this->get_option('github_url') ){
				$third_party_resources['administrative'][] = array(
					'name' => 'GitHub Repository',
					'icon' => '<i class="nebula-admin-fa fa-brands fa-fw fa-github"></i>',
					'url' => $this->get_option('github_url')
				);
			}

			if ( $this->get_option('ga_measurement_id') ){
				$third_party_resources['administrative'][] = array(
					'name' => 'Google Analytics',
					'icon' => '<i class="nebula-admin-fa fa-solid fa-fw fa-chart-area"></i>',
					'url' => $this->google_analytics_url()
				);
			}

			if ( $this->get_option('gtm_id') ){
				$third_party_resources['administrative'][] = array(
					'name' => 'Google Tag Manager',
					'icon' => '<i class="nebula-admin-fa fa-brands fa-fw fa-google"></i>',
					'url' => 'https://tagmanager.google.com'
				);
			}

			$third_party_resources['administrative'][] = array(
				'name' => 'Google Search Console',
				'icon' => '<i class="nebula-admin-fa fa-brands fa-fw fa-google"></i>',
				'url' => 'https://search.google.com/search-console'
			);

			$third_party_resources['administrative'][] = array(
				'name' => 'Bing Webmaster Tools',
				'icon' => '<i class="nebula-admin-fa fa-brands fa-fw fa-microsoft"></i>',
				'url' => 'https://www.bing.com/toolbox/webmaster'
			);

			if ( is_plugin_active('wordpress-seo/wp-seo.php') ){ //If Yoast SEO is active link to its sitemap
				$third_party_resources['administrative'][] = array(
					'name' => 'Yoast SEO Sitemap',
					'icon' => '<i class="nebula-admin-fa fa-solid fa-fw fa-sitemap"></i>',
					'url' => home_url('/') . 'sitemap_index.xml'
				);
			} elseif ( is_plugin_active('autodescription/autodescription.php') ){ //If The SEO Framework is active link to its sitemap
				$third_party_resources['administrative'][] = array(
					'name' => 'The SEO Framework Sitemap',
					'icon' => '<i class="nebula-admin-fa fa-solid fa-fw fa-sitemap"></i>',
					'url' => home_url('/') . 'sitemap.xml'
				);
			} else { //Otherwise link to the core WordPress sitemap
				$third_party_resources['administrative'][] = array(
					'name' => 'WordPress Sitemap',
					'icon' => '<i class="nebula-admin-fa fa-solid fa-fw fa-sitemap"></i>',
					'url' => home_url('/') . 'wp-sitemap.xml'
				);
			}

			if ( $this->get_option('adwords_remarketing_conversion_id') ){
				$third_party_resources['administrative'][] = array(
					'name' => 'Google AdWords',
					'icon' => '<i class="nebula-admin-fa fa-solid fa-fw fa-magnifying-glass-plus"></i>',
					'url' => 'https://adwords.google.com/home/'
				);
			}

			if ( $this->get_option('facebook_custom_audience_pixel_id') ){
				$third_party_resources['administrative'][] = array(
					'name' => 'Facebook Ads Manager',
					'icon' => '<i class="nebula-admin-fa fa-brands fa-fw fa-facebook-official"></i>',
					'url' => 'https://www.facebook.com/ads/manager/account/campaigns'
				);
			}

			if ( $this->get_option('google_adsense_url') ){
				$third_party_resources['administrative'][] = array(
					'name' => 'Google AdSense',
					'icon' => '<i class="nebula-admin-fa fa-solid fa-fw fa-money"></i>',
					'url' => 'https://www.google.com/adsense'
				);
			}

			if ( $this->get_option('amazon_associates_url') ){
				$third_party_resources['administrative'][] = array(
					'name' => 'Amazon Associates',
					'icon' => '<i class="nebula-admin-fa fa-brands fa-fw fa-amazon"></i>',
					'url' => 'https://affiliate-program.amazon.com/home'
				);
			}

			$third_party_resources['administrative'][] = array(
				'name' => 'Google My Business',
				'icon' => '<i class="nebula-admin-fa fa-regular fa-fw fa-building"></i>',
				'url' => 'https://www.google.com/business/'
			);

			if ( $this->is_ecommerce() ){
				$third_party_resources['administrative'][] = array(
					'name' => 'Google Merchant Center',
					'icon' => '<i class="nebula-admin-fa fa-solid fa-fw fa-store"></i>',
					'url' => 'https://www.google.com/retail/solutions/merchant-center/'
				);
			}

			if ( $this->get_option('facebook_app_id') ){
				$third_party_resources['administrative'][] = array(
					'name' => 'Facebook For Developers',
					'icon' => '<i class="nebula-admin-fa fa-brands fa-fw fa-facebook"></i>',
					'url' => 'https://developers.facebook.com/'
				);
			}

			if ( $this->get_option('google_server_api_key') || $this->get_option('google_browser_api_key') ){
				$third_party_resources['administrative'][] = array(
					'name' => 'Google APIs',
					'icon' => '<i class="nebula-admin-fa fa-solid fa-fw fa-code"></i>',
					'url' => 'https://console.developers.google.com/iam-admin/projects'
				);
			}

			if ( $this->get_option('hubspot_api') || $this->get_option('hubspot_portal') ){
				$third_party_resources['administrative'][] = array(
					'name' => 'Hubspot',
					'icon' => '<i class="nebula-admin-fa fa-brands fa-fw fa-hubspot"></i>',
					'url' => 'https://app.hubspot.com/reports-dashboard/' . $this->get_option('hubspot_portal')
				);
			}

			if ( $this->get_option('mention_url') ){
				$third_party_resources['administrative'][] = array(
					'name' => 'Mention',
					'icon' => '<i class="nebula-admin-fa fa-solid fa-fw fa-star"></i>',
					'url' => 'https://web.mention.com'
				);
			}

			//Social
			if ( $this->get_option('facebook_url') ){
				$third_party_resources['social'][] = array(
					'name' => 'Facebook',
					'icon' => '<i class="nebula-admin-fa fa-brands fa-fw fa-facebook-square"></i>',
					'url' => $this->get_option('facebook_url')
				);
			}

			if ( $this->get_option('twitter_username') ){
				$third_party_resources['social'][] = array(
					'name' => 'Twitter',
					'icon' => '<i class="nebula-admin-fa fa-brands fa-fw fa-twitter-square"></i>',
					'url' => $this->twitter_url()
				);
			}

			if ( $this->get_option('linkedin_url') ){
				$third_party_resources['social'][] = array(
					'name' => 'LinkedIn',
					'icon' => '<i class="nebula-admin-fa fa-brands fa-fw fa-linkedin"></i>',
					'url' => $this->get_option('linkedin_url')
				);
			}

			if ( $this->get_option('youtube_url') ){
				$third_party_resources['social'][] = array(
					'name' => 'Youtube',
					'icon' => '<i class="nebula-admin-fa fa-brands fa-fw fa-youtube"></i>',
					'url' => $this->get_option('youtube_url')
				);
			}

			if ( $this->get_option('instagram_url') ){
				$third_party_resources['social'][] = array(
					'name' => 'Instagram',
					'icon' => '<i class="nebula-admin-fa fa-brands fa-fw fa-instagram"></i>',
					'url' => $this->get_option('instagram_url')
				);
			}

			if ( $this->get_option('disqus_shortname') ){
				$third_party_resources['social'][] = array(
					'name' => 'Disqus',
					'icon' => '<i class="nebula-admin-fa fa-regular fa-fw fa-comments"></i>',
					'url' => 'https://' . $this->get_option('disqus_shortname') . '.disqus.com/admin/moderate/'
				);
			}

			if ( $this->google_review_url() ){
				$third_party_resources['social'][] = array(
					'name' => 'Google Review Link',
					'icon' => '<i class="nebula-admin-fa fa-brands fa-fw fa-google"></i>',
					'url' => $this->google_review_url()
				);
			}

			$this->timer('Aggregating Links to Active Third-Party Tools', 'end');

			wp_cache_set('nebula_third_party_resources', $third_party_resources, 'nebula', MONTH_IN_SECONDS); //Store in object cache
			return $third_party_resources;
		}

		//CSS override for the frontend
		public function remove_admin_bar_style_frontend(){
			if ( is_admin_bar_showing() ){ ?>
				<style type="text/css" media="screen">
					html {margin-top: 0 !important;}
					* html body {margin-top: 0 !important;}
				</style>
			<?php }
		}

		public function admin_bar_modifications(){
			if ( $this->is_minimal_mode() ){return null;}

			if ( is_admin_bar_showing() ){
				global $wp_admin_bar;

				//Remove logos and other extraneous nodes from the WP admin bar
				$wp_admin_bar->remove_menu('wp-logo'); //Remove the WordPress logo
				$wp_admin_bar->remove_menu('wpseo-menu'); //Remove Yoast SEO from admin bar

				//Change "View" to "Preview" when posts are unpublished
				$post_node = $wp_admin_bar->get_node('preview');
				if ( $post_node ){ //If the node exists
					$post_node->title = str_replace('View', 'Preview', $post_node->title); //Change the text from "View" to "Preview"
					$wp_admin_bar->add_node($post_node); //Re-add the node to the admin bar
				}
			}
		}

		//Create custom menus within the WordPress Admin Bar
		public function admin_bar_menus(WP_Admin_Bar $wp_admin_bar){
			if ( is_admin_bar_showing() ){
				$this->timer('Nebula Admin Bar Menus');

				//Include the TTFB in the Nebula admin bar title if Query Monitor is not active
				$ttfb_description = '';
				if ( !empty($this->super->server['REQUEST_TIME_FLOAT']) ){
					if ( !is_plugin_active('query-monitor/query-monitor.php') || $this->is_minimal_mode() ){ //If QM is not active or show it during minimal mode
						$ttfb_description = ' <small>(<span class="nebula-ttfb-time">~' . number_format((microtime(true)-$this->super->server['REQUEST_TIME_FLOAT']), 2) . '</span>s)</small>'; //This subtracts current time from when PHP first started processing
					}
				}

				//Create the main top-level Nebula admin bar node
				$nebula_icon = 'fa-star role-other';
				if ( $this->is_dev() ){
					$nebula_icon = 'fa-user-astronaut role-dev';
				} elseif ( $this->is_staff() ){
					$nebula_icon = 'fa-satellite role-staff';
				}

				$wp_admin_bar->add_node(array(
					'id' => 'nebula',
					'title' => '<i class="nebula-admin-fa fa-solid ' . $nebula_icon . ' nebula-admin-bar-icon nebula-symbol"></i> Nebula' . $ttfb_description, //fa-user-astronaut fa-satellite fa-rocket
					'href' => 'https://nebula.gearside.com/?utm_campaign=documentation&utm_medium=admin_bar&utm_source=' . urlencode(site_url()) . '&utm_content=admin_bar_main',
					'meta' => array(
						'target' => '_blank',
						'rel' => 'noopener'
					)
				));

				//Stop here during minimal mode
				if ( $this->is_minimal_mode() ){return null;}

				//Prep for the Post admin bar top-level node
				wp_reset_query(); //Make sure the query is always reset in case the current page has a custom query that isn't reset.
				global $post;

				$current_id = get_the_id();
				if ( is_home() ){
					$current_id = get_option('page_for_posts');
				}

				$status = get_post_field('post_status', $current_id);
				$original_date = strtotime(get_post_field('post_date', $current_id));
				$original_author = get_the_author_meta('display_name', get_post_field('post_author', $current_id));
				$modified_date = strtotime(get_post_field('post_modified', $current_id));
				if ( get_post_meta($current_id, '_edit_last', true) ){
					$last_user = get_userdata(get_post_meta($current_id, '_edit_last', true));
					$modified_author = $last_user->display_name;
				}

				$node_id = ( $this->is_admin_page() )? 'view' : 'edit';
				$new_content_node = $wp_admin_bar->get_node($node_id);
				if ( $new_content_node ){
					//Note any assets that Nebula is deregistering on this post/page
					$info_icon = '';
					if ( !empty($this->deregistered_assets['styles']) || !empty($this->deregistered_assets['scripts']) ){
						$info_icon = ' <i class="nebula-admin-fa fa-solid fa-fw fa-info-circle deregistered-asset-info"></i>';

						$wp_admin_bar->add_node(array(
							'parent' => $node_id,
							'id' => 'nebula-deregisters',
							'title' => '<i class="nebula-admin-fa fa-solid fa-fw fa-ban"></i> Nebula is deregistering assets on this page!',
							'href' => admin_url('themes.php?page=nebula_options&tab=Advanced'),
							'meta' => array('target' => '_blank', 'rel' => 'noopener')
						));

						//Styles
						if ( !empty($this->deregistered_assets['styles']) ){
							foreach ( $this->deregistered_assets['styles'] as $handle ){
								$wp_admin_bar->add_node(array(
									'parent' => 'nebula-deregisters',
									'id' => 'nebula-deregisters-styles-' . $handle,
									'title' => '<span class="nebula-admin-light"><i class="nebula-admin-fa fa-brands fa-fw fa-css3-alt"></i> CSS:</span> ' . $handle,
									'href' => admin_url('themes.php?page=nebula_options&tab=Advanced'),
									'meta' => array('target' => '_blank', 'rel' => 'noopener')
								));
							}
						}

						//Scripts
						if ( !empty($this->deregistered_assets['scripts']) ){
							foreach ( $this->deregistered_assets['scripts'] as $handle ){
								$wp_admin_bar->add_node(array(
									'parent' => 'nebula-deregisters',
									'id' => 'nebula-deregisters-scripts-' . $handle,
									'title' => '<span class="nebula-admin-light"><i class="nebula-admin-fa fa-brands fa-fw fa-js"></i> JS:</span> ' . $handle,
									'href' => admin_url('themes.php?page=nebula_options&tab=Advanced'),
									'meta' => array('target' => '_blank', 'rel' => 'noopener')
								));
							}
						}
					}

					//Add a node for the current post information
					if ( get_post_type() != 'nebula_cf7_submits' ){ //Ignore certain post types
						$post_type_object = get_post_type_object(get_post_type());
						if ( !empty($post_type_object) ){ //Ignore non-posts like user profiles
							$post_type_name = $post_type_object->labels->singular_name;

							$current_id = get_the_id();
							if ( is_home() ){
								$current_id = get_option('page_for_posts');
							} elseif ( is_archive() ){
								$term_object = get_queried_object();
								if ( !empty($term_object->term_id) ){
									$current_id = $term_object->term_id;
									$post_type_name = $term_object->taxonomy;
								}
								$original_date = false;
								$status = false;
							}

							$new_content_node->title = ucfirst($node_id) . ' ' . ucwords($post_type_name) . ' <span class="nebula-admin-light">(ID: ' . $current_id . ')' . $info_icon . '</span>';
							$wp_admin_bar->add_node($new_content_node);
						}
					}
				}

				//Add created date under View/Edit node
				if ( !empty($original_date) ){
					$wp_admin_bar->add_node(array(
						'parent' => $node_id,
						'id' => 'nebula-created',
						'title' => '<i class="nebula-admin-fa fa-regular fa-fw fa-calendar"></i> <span title="' . human_time_diff($original_date) . ' ago">Created: ' . date('F j, Y', $original_date) . '</span> <span class="nebula-admin-light">(' . $original_author . ')</span>',
						'href' => get_edit_post_link(),
						'meta' => array('target' => '_blank', 'rel' => 'noopener')
					));
				}

				//Add modified date under View/Edit node
				if ( get_post_meta($current_id, '_edit_last', true) ){ //If the post has been modified
					$wp_admin_bar->add_node(array(
						'parent' => $node_id,
						'id' => 'nebula-modified',
						'title' => '<i class="nebula-admin-fa fa-regular fa-fw fa-clock"></i> <span title="' . human_time_diff($modified_date) . ' ago">Modified: ' . date('F j, Y', $modified_date) . '</span> <span class="nebula-admin-light">(' . $modified_author . ')</span>',
						'href' => get_edit_post_link(),
						'meta' => array('target' => '_blank', 'rel' => 'noopener')
					));
				}

				//Post status (Publish, Draft, Private, etc)
				if ( !empty($status) ){
					$wp_admin_bar->add_node(array(
						'parent' => $node_id,
						'id' => 'nebula-status',
						'title' => '<i class="nebula-admin-fa fa-solid fa-fw fa-map-marker"></i> Status: ' . ucwords($status),
						'href' => get_edit_post_link(),
						'meta' => array('target' => '_blank', 'rel' => 'noopener')
					));
				}

				//Theme template file
				if ( !empty($this->current_theme_template) ){
					$wp_admin_bar->add_node(array(
						'parent' => $node_id,
						'id' => 'nebula-template',
						'title' => '<i class="nebula-admin-fa fa-regular fa-fw fa-object-group"></i> Template: ' . basename($this->current_theme_template) . ' <span class="nebula-admin-light">(' . dirname($this->current_theme_template) . ')</span>',
						'href' => get_edit_post_link(),
						'meta' => array('target' => '_blank', 'rel' => 'noopener')
					));
				}

				//Asset Count
				if ( isset($this->super->globals['nebula_asset_counts']) ){
					$asset_counts = $this->super->globals['nebula_asset_counts'];
					$total_assets = $asset_counts['css']+$asset_counts['js'];

					$wp_admin_bar->add_node(array(
						'parent' => $node_id,
						'id' => 'nebula-asset-count',
						'title' => '<i class="nebula-admin-fa fa-solid fa-fw fa-cubes-stacked"></i> Assets: ' . $total_assets . ' <span class="nebula-admin-light">(' . $asset_counts['css'] . ' CSS, ' . $asset_counts['js'] . ' JS)</span>',
					));
				}

				if ( !empty($post_type_object) ){
					//Ancestor pages
					$ancestors = get_post_ancestors($current_id);
					if ( !empty($ancestors) ){
						$wp_admin_bar->add_node(array(
							'parent' => $node_id,
							'id' => 'nebula-ancestors',
							'title' => '<i class="nebula-admin-fa fa-solid fa-fw fa-level-up-alt"></i> Ancestor ' . ucwords($post_type_object->labels->name) . ' <small>(' . count($ancestors) . ')</small>',
						));

						foreach ( $ancestors as $parent ){
							$wp_admin_bar->add_node(array(
								'parent' => 'nebula-ancestors',
								'id' => 'nebula-parent-' . $parent,
								'title' => '<i class="nebula-admin-fa fa-regular fa-fw fa-file"></i> ' . esc_html(get_the_title($parent)),
								'href' => ( $this->is_admin_page() )? get_edit_post_link($parent) : get_permalink($parent),
							));
						}
					}

					if ( !$this->is_admin_page() ){ //@todo "Nebula" 0: Remove this conditional when this bug is fixed: https://core.trac.wordpress.org/ticket/18408
						//Children pages
						$child_pages = new WP_Query(array(
							'post_type' => $post_type_object->labels->singular_name,
							'posts_per_page' => -1,
							'post_parent' => $current_id,
							'order' => 'ASC',
							'orderby' => 'menu_order'
						));
						if ( $child_pages->have_posts() ){
							$wp_admin_bar->add_node(array(
								'parent' => $node_id,
								'id' => 'nebula-children',
								'title' => '<i class="nebula-admin-fa fa-solid fa-fw fa-level-down-alt"></i> Children ' . ucwords($post_type_object->labels->name) . ' <small>(' . $child_pages->found_posts . ')</small>',
							));

							while ( $child_pages->have_posts() ){
								$child_pages->the_post();
								$wp_admin_bar->add_node(array(
									'parent' => 'nebula-children',
									'id' => 'nebula-child-' . get_the_id(),
									'title' => '<i class="nebula-admin-fa fa-solid fa-fw fa-file"></i> ' . esc_html(get_the_title()),
									'href' => ( $this->is_admin_page() )? get_edit_post_link() : get_permalink(),
								));
							}
						}

						wp_reset_postdata();
					}
				}

				//Indicate when the page load is from a known cache
				if ( !empty($this->is_known_cache_hit()) ){
					$wp_admin_bar->add_node(array(
						'id' => 'nebula-cache-hit',
						'title' => '<i class="nebula-admin-fa fa-solid fa-fw fa-server"></i> Cached Load',
					));
					$wp_admin_bar->add_node(array(
						'parent' => 'nebula-cache-hit',
						'id' => 'nebula-cache-source',
						'title' => $this->is_known_cache_hit() . ' Cache Hit',
					));
				}

				//Show the warnings node when applicable
				if ( current_user_can('manage_options') && !$this->is_minimal_mode() ){
					$warnings = $this->check_warnings();

					if ( !empty($warnings) ){
						//Remove "log" level warnings so the admin bar menu only turns red for warnings and errors
						foreach ( $warnings as $key => $warning ){
							if ( $warning['level'] === 'log' || $warning['level'] === 'success' ){
								unset($warnings[$key]);
							}
						}

						//If we have any warnings left after cleanup, output them
						if ( !empty($warnings) && count($warnings) >= 1 ){
							$warning_icon = 'fa-info-circle';
							$warning_bg_class = 'has-warning';
							if ( array_filter($warnings, function($item){return $item['level'] == 'error'; }) ){ //If the warnings contain an error entry
								$warning_icon = 'fa-exclamation-triangle';
								$warning_bg_class = 'has-error';
							}

							$wp_admin_bar->add_node(array(
								'id' => 'nebula-warnings',
								'title' => '<i class="nebula-admin-fa fa-solid fa-fw ' . $warning_icon . '"></i> ' . count($warnings),
								'meta' => array(
									'class' => $warning_bg_class
								)
							));

							//Now loop through to display the individual warnings as sub-nodes
							foreach ( $warnings as $key => $warning ){
								$warning_icon = 'fa-exclamation-triangle';

								if ( $warning['level'] === 'error' ){
									$warning_icon = 'fa-exclamation-triangle';
								} elseif ( $warning['level'] === 'warning' ){
									$warning_icon = 'fa-info-circle';
								} elseif ( $warning['level'] === 'success' ){
									$warning_icon = 'fa-check';
								}

								$wp_admin_bar->add_node(array(
									'parent' => 'nebula-warnings',
									'id' => 'nebula-warning-' . $key,
									'title' => '<i class="nebula-admin-fa fa-solid fa-fw ' . $warning_icon . '" style="margin-left: 5px;"></i> ' . strip_tags($warning['description']),
									'href' => ( !empty($warning['url']) )? $warning['url'] : '',
									'meta' => array(
										'target' => '_blank',
										'rel' => 'noopener',
										'class' => 'nebula-warning level-' . $warning['level'],
									)
								));
							}
						}
					}
				}

				//Now add sub-nodes to the main Nebula admin bar item
				//Version number and date
				$wp_admin_bar->add_node(array(
					'parent' => 'nebula',
					'id' => 'nebula-version',
					'title' => 'v<strong>' . $this->version('raw') . '</strong> <span class="nebula-admin-light">(' . $this->version('date') . ')</span>',
					'href' => 'https://github.com/chrisblakley/Nebula/compare/main@{' . date('Y-m-d', $this->version('utc')) . '}...main',
				));

				if ( is_array($this->server_timings) ){
					$wp_admin_bar->add_node(array(
						'parent' => 'nebula',
						'id' => 'nebula-timing-categories',
						'title' => '<i class="nebula-admin-fa fa-solid fa-fw fa-stopwatch"></i> Timing Categories'
					));

					//This empty node ensures the submenu can open. There may be a better way to do this...
					$wp_admin_bar->add_node(array(
						'parent' => 'nebula-timing-categories',
						'id' => 'nebula-timing-category-heading',
						'title' => '<strong><i class="nebula-admin-fa fa-solid fa-fw fa-arrow-down-9-1"></i> Durations (Desc.)</strong>',
						'meta' => array(
							'title' => 'Manual timing groups. These are durations (not timestamps) of functionality using Nebula Timers. Remember: Due to overlap, it is impossible to truly time individual functionality. These timings represent best-effort ballparks. Only manually tracked timings will appear here!',
						)
					));
				}

				//Documentation Links
				$wp_admin_bar->add_node(array(
					'parent' => 'nebula',
					'id' => 'nebula-documentation',
					'title' => '<i class="nebula-admin-fa fa-solid fa-fw fa-file-alt"></i> Nebula Documentation',
					'href' => 'https://nebula.gearside.com/?utm_campaign=documentation&utm_medium=admin_bar&utm_source=' . urlencode(site_url()) . '&utm_content=admin_bar_documentation',
					'meta' => array(
						'target' => '_blank',
						'rel' => 'noopener',
					)
				));

				$wp_admin_bar->add_node(array(
					'parent' => 'nebula-documentation',
					'id' => 'nebula-documentation-functions',
					'title' => '<i class="nebula-admin-fa fa-solid fa-fw fa-file-alt"></i> Functions & Variables',
					'href' => 'https://nebula.gearside.com/documentation/functions/?utm_campaign=documentation&utm_medium=admin_bar&utm_source=' . urlencode(site_url()) . '&utm_content=admin_bar_functions',
					'meta' => array(
						'target' => '_blank',
						'rel' => 'noopener',
					)
				));

				$wp_admin_bar->add_node(array(
					'parent' => 'nebula-documentation',
					'id' => 'nebula-documentation-examples',
					'title' => '<i class="nebula-admin-fa fa-solid fa-fw fa-file-alt"></i> Examples & Tips',
					'href' => 'https://nebula.gearside.com/documentation/examples-tips/?utm_campaign=documentation&utm_medium=admin_bar&utm_source=' . urlencode(site_url()) . '&utm_content=admin_bar_examples',
					'meta' => array(
						'target' => '_blank',
						'rel' => 'noopener',
					)
				));

				$wp_admin_bar->add_node(array(
					'parent' => 'nebula-documentation',
					'id' => 'nebula-documentation-faq',
					'title' => '<i class="nebula-admin-fa fa-solid fa-fw fa-question"></i> FAQs',
					'href' => 'https://nebula.gearside.com/faq/?utm_campaign=documentation&utm_medium=admin_bar&utm_source=' . urlencode(site_url()) . '&utm_content=admin_bar_faq',
					'meta' => array(
						'target' => '_blank',
						'rel' => 'noopener',
					)
				));

				$third_party_resources = $this->third_party_resources();

				if ( !empty($third_party_resources) ){
					$wp_admin_bar->add_node(array(
						'parent' => 'nebula',
						'id' => 'nebula-resources',
						'title' => '<i class="nebula-admin-fa fa-solid fa-fw fa-toolbox"></i> Third-Party Resources',
					));

					if ( current_user_can('edit_others_posts') && !empty($third_party_resources['administrative']) ){
						$wp_admin_bar->add_node(array(
							'parent' => 'nebula-resources',
							'id' => 'nebula-resources-administrive',
							'title' => 'Administrative',
						));

						foreach ( $third_party_resources['administrative'] as $resource ){
							$wp_admin_bar->add_node(array(
								'parent' => 'nebula-resources-administrive',
								'id' => 'nebula-resource-' . strtolower(str_replace(' ', '_', $resource['name'])),
								'title' => $resource['icon'] . ' ' . $resource['name'],
								'href' => $resource['url'],
								'meta' => array('target' => '_blank', 'rel' => 'noopener')
							));
						}
					}

					if ( !empty($third_party_resources['social']) ){
						$wp_admin_bar->add_node(array(
							'parent' => 'nebula-resources',
							'id' => 'nebula-resources-social',
							'title' => 'Social',
						));

						foreach ( $third_party_resources['social'] as $resource ){
							$wp_admin_bar->add_node(array(
								'parent' => 'nebula-resources-social',
								'id' => 'nebula-resource-' . strtolower(str_replace(' ', '_', $resource['name'])),
								'title' => $resource['icon'] . ' ' . $resource['name'],
								'href' => $resource['url'],
								'meta' => array('target' => '_blank', 'rel' => 'noopener')
							));
						}
					}
				}

				if ( current_user_can('manage_options') ){
					$wp_admin_bar->add_node(array(
						'parent' => 'nebula',
						'id' => 'nebula-options',
						'title' => '<i class="nebula-admin-fa fa-solid fa-fw fa-cog"></i> Options',
						'href' => admin_url('themes.php?page=nebula_options')
					));

					foreach ( $this->get_option_categories() as $category ){
						$wp_admin_bar->add_node(array(
							'parent' => 'nebula-options',
							'id' => 'nebula-options-' . $category['name'],
							'title' => '<i class="nebula-admin-fa fa-solid fa-fw ' . $category['icon'] . '"></i> ' . $category['name'],
							'href' => admin_url('themes.php?page=nebula_options&tab=' . $category['name']),
							'meta' => array('target' => '_blank', 'rel' => 'noopener')
						));
					}

					$wp_admin_bar->add_node(array(
						'parent' => 'nebula-options',
						'id' => 'nebula-options-help',
						'title' => '<i class="nebula-admin-fa fa-regular fa-fw fa-question-circle"></i> Help & Documentation',
						'href' => 'https://nebula.gearside.com/documentation/options/?utm_campaign=documentation&utm_medium=admin_bar&utm_source=' . urlencode(site_url()) . '&utm_content=admin_bar_help',
						'meta' => array('target' => '_blank', 'rel' => 'noopener')
					));

					$wp_admin_bar->add_node(array(
						'parent' => 'nebula',
						'id' => 'nebula-github',
						'title' => '<i class="nebula-admin-fa fa-brands fa-fw fa-github"></i> Nebula Github',
						'href' => 'https://github.com/chrisblakley/Nebula',
						'meta' => array('target' => '_blank', 'rel' => 'noopener')
					));

					$wp_admin_bar->add_node(array(
						'parent' => 'nebula-github',
						'id' => 'nebula-github-issues',
						'title' => 'Issues',
						'href' => 'https://github.com/chrisblakley/Nebula/issues',
						'meta' => array('target' => '_blank', 'rel' => 'noopener')
					));

					$wp_admin_bar->add_node(array(
						'parent' => 'nebula-github',
						'id' => 'nebula-github-changelog',
						'title' => 'Changelog',
						'href' => 'https://github.com/chrisblakley/Nebula/commits/main',
						'meta' => array('target' => '_blank', 'rel' => 'noopener')
					));

					if ( $this->get_option('scss') ){
						$scss_last_processed_text = 'Sass has not yet been processed.';
						if ( $this->get_data('scss_last_processed') ){
							$scss_last_processed_relative = human_time_diff($this->get_data('scss_last_processed'));
							$scss_last_processed_absolute = date('l, F j, Y - g:i:sa', $this->get_data('scss_last_processed'));
							$scss_last_processed_text = 'Last processed ' . $scss_last_processed_relative . ' ago (' . $scss_last_processed_absolute . ')';
						}

						$wp_admin_bar->add_node(array(
							'parent' => 'nebula-utilities',
							'id' => 'nebula-scss-reprocess',
							'title' => '<i class="nebula-admin-fa fa-brands fa-fw fa-sass"></i> Re-process All Sass Files',
							'href' => esc_url(add_query_arg('sass', 'true')),
							'meta' => array('title' => 'Process all Sass files and reload the page. ' . $scss_last_processed_text)
						));
					}

					if ( !$this->is_admin_page() ){
						$wp_admin_bar->add_node(array(
							'parent' => 'nebula-utilities',
							'id' => 'nebula-audit',
							'title' => '<i class="nebula-admin-fa fa-solid fa-fw fa-list-alt"></i> Audit This Page',
							'href' => esc_url(add_query_arg('audit', 'true')),
							'meta' => array('title' => 'Checks the current page for common issues')
						));

						$wp_admin_bar->add_node(array(
							'parent' => 'nebula-utilities',
							'id' => 'nebula-scan',
							'title' => '<i class="nebula-admin-fa fa-solid fa-fw fa-microscope"></i> Scan This Page for Assets',
							'href' => esc_url(add_query_arg('nebula-scan', 'true')),
							'meta' => array('title' => 'Scans the current page for registered styles and scripts (for advanced optimization in Nebula Options > Advanced > Dequeues)')
						));
					}
				}

				$wp_admin_bar->add_node(array(
					'parent' => 'nebula',
					'id' => 'nebula-utilities',
					'title' => '<i class="nebula-admin-fa fa-solid fa-fw fa-tools"></i> Utilities & Tools',
				));

				if ( $this->is_dev() ){
					$wp_admin_bar->add_node(array(
						'parent' => 'nebula-utilities',
						'id' => 'nebula-minimal-mode',
						'title' => '<i class="nebula-admin-fa fa-solid fa-fw fa-minimize"></i> Reload with Minimal Functionality',
						'href' => esc_url(add_query_arg('minimal', 'true')),
						'meta' => array('title' => 'Append ?minimal to load this page with only minimal Nebula functionality')
					));
				}

				if ( current_user_can('edit_others_posts') ){
					$wp_admin_bar->add_node(array(
						'parent' => 'nebula-utilities',
						'id' => 'nebula-add-debug',
						'title' => '<i class="nebula-admin-fa fa-solid fa-fw fa-sync"></i> Reload &amp; Clear Caches',
						'href' => esc_url(add_query_arg('debug', 'true')),
						'meta' => array('title' => 'Append ?debug to force clear certain caches')
					));
				}

				//Temporarily enables or disable Query Monitor for this IP when not logged in
				if ( is_plugin_active('query-monitor/query-monitor.php') ){
					$nebula_temp_qm_ip = get_transient('nebula_temp_qm_ip');
					if ( empty($nebula_temp_qm_ip) || $this->get_ip_address() !== $nebula_temp_qm_ip ){
						$wp_admin_bar->add_node(array(
							'parent' => 'nebula-utilities',
							'id' => 'nebula-allow-qm',
							'title' => '<i class="nebula-admin-fa fa-solid fa-fw fa-toggle-off"></i> Allow QM for this IP for 1hr (' . $this->get_ip_address() . ')',
							'href' => esc_url(
								add_query_arg(array(
									'nebula-temp-qm' => 'start',
								), get_admin_url())
							)
						));
					} else {
						$wp_admin_bar->add_node(array(
							'parent' => 'nebula-utilities',
							'id' => 'nebula-allow-qm',
							'title' => '<i class="nebula-admin-fa fa-solid fa-fw fa-toggle-on"></i> Stop QM for this IP now',
							'href' => esc_url(
								add_query_arg(array(
									'nebula-temp-qm' => 'stop',
								), get_admin_url())
							)
						));
					}
				}

				//Show the Sass icon when scss files were processed during this load
				if ( $this->get_option('scss') ){
					if ( $this->was_sass_processed || !empty($this->sass_process_status) ){
						$sass_color = 'sass-success';
						$sass_icon = 'fa-check';
						$sass_number = $this->sass_files_processed_count;

						if ( !empty($this->sass_process_status) ){
							if ( str_contains($this->sass_process_status, 'not processed') || str_contains($this->sass_process_status, 'an error') ){
								$sass_color = 'sass-danger';
								$sass_icon = 'fa-xmark';
							} elseif ( str_contains($this->sass_process_status, 'throttled') ){
								$sass_color = 'sass-warn';
								$sass_icon = 'fa-stopwatch';
								$sass_number = '<em class="cooldown-wait" data-cooldown="10" data-units="s" data-parenthesis="true">(10s)</em><small class="cooldown-again hidden">&raquo;</small>'; //This should match the cooldown threshold from Sass.php
							}
						}

						$wp_admin_bar->add_node(array(
							'id' => 'nebula-sass-processed',
							'title' => '<i class="nebula-admin-fa fa-solid fa-fw ' . $sass_icon . '"></i> <i class="nebula-admin-fa fa-brands fa-fw fa-sass"></i> ' . $sass_number,
							'href' => esc_url(add_query_arg('sass', 'true')),
							'meta' => array(
								'title' => ( $sass_color != 'sass-success' )? $this->sass_process_status : '',
								'class' => $sass_color,
							)
						));

						//List each of the CSS files processed
						foreach ( $this->sass_files_processed as $index => $sass_file_processed ){
							$wp_admin_bar->add_node(array(
								'parent' => 'nebula-sass-processed',
								'id' => 'nebula-sass-file-processed-' . $index,
								'title' => preg_replace('#^.*?/wp-content#', '', $sass_file_processed), //Remove everything before and including /wp-content
								'href' => esc_url(str_replace(realpath(WP_CONTENT_DIR), content_url(), $sass_file_processed)), //Replace the server absolute path with the clickable url
								'meta' => array('target' => '_blank', 'rel' => 'noopener')
							));
						}
					}
				}

				$this->timer('Nebula Admin Bar Menus', 'end');
			}
		}

		//Admin Bar CSS
		//Colorize Nebula warning nodes in the admin bar
		public function admin_bar_warning_styles(){
			if ( $this->is_minimal_mode() ){return null;}

			if ( is_admin_bar_showing() ){ ?>
				<style type="text/css">
					#wpadminbar {
						#wp-admin-bar-root-default > li > .ab-item {transition: all 0.25s ease;
							.ab-icon,
							.ab-label {transition: all 0.25s ease;}
						}

						.nebula-admin-fa {font-family: "Font Awesome 6 Solid", "Font Awesome 6 Free", "Font Awesome 6 Pro"; font-weight: 900;
							&.fa-brands {font-family: "Font Awesome 6 Brands", "Font Awesome 6 Free", "Font Awesome 6 Pro"; font-weight: 400;}
						}

						.svg-inline--fa {color: #a0a5aa; color: rgba(240, 245, 250, 0.6); margin-right: 5px;}
						.nebula-admin-light {font-size: 10px; color: #a0a5aa; color: rgba(240, 245, 250, 0.6); line-height: inherit;}

						&:not(.mobile) {
							.nebula-admin-bar-icon,
							.nebula-symbol {color: #a7aaad;
								path {fill: #a7aaad;}
							}

							.nebula-symbol.role-dev {
								/* color: #ff2362; */
								background: linear-gradient(to right in oklch, #9622ed, #fa239e); /* Using midpoints of the Nebula colors so it isn't as harsh */
								-webkit-background-clip: text;
								-webkit-text-fill-color: transparent;
								color: inherit;

								path {fill: #ff2362;}
							}

							#wp-admin-bar-nebula {
								> .ab-item {transition: all 0.5s ease;}

								&:hover > .ab-item,
								&.hover > .ab-item {background: linear-gradient(to right in oklch, #5b22e8, #ff2362); color: #fff;
									.nebula-symbol {color: #fff !important; -webkit-text-fill-color: #fff;
										path {fill: #fff !important;}
									}
								}

								small {font-size: 0.7rem !important;}
								.nebula-ttfb-time {font-size: 0.7rem !important;}
							}

							#wp-admin-bar-nebula-timing-categories {
								#wp-admin-bar-nebula-timing-category-heading {font-weight: bold !important; text-decoration: underline;
									> .ab-item {cursor: help;}
								}
								strong {font-weight: bold !important;}

								.nebula-timing-category-item {
									.group-name {opacity: 0.6;}

									&.danger strong {color: #dc3545;}
									&.warning strong {color: #b95e00;}
									&.ignorable {opacity: 0.5;}
								}
							}

							#wp-admin-bar-nebula-warnings {
								&.has-error {background: #dc3545;}
								&.has-warning {background: #ffc107;
									&:not(:hover) > .ab-item {color: #000;}
								}

								.nebula-warning { /* Individual warning sub-node rows */
									&.level-error i {color: #dc3545;}
									&.level-warn i,
									&.level-warning i {color: #ffc107;}
								}
							}

							.deregistered-asset-info {color: #ffc107;}
							#wp-admin-bar-nebula-deregisters {
								> .ab-item {background: #ffc107; color: #000; transition: all 0.25s ease;}

								&.hover > .ab-item,
								&:hover > .ab-item {background: #ffc107;}
							}

							#wp-admin-bar-nebula-sass-processed {color: #fff;
								&.sass-success > .ab-item {color: #28a745;}
								&.sass-warn > .ab-item {color: #ffc107;}
								&.sass-danger > .ab-item {background: #dc3545;}
							}
						}
					}
				</style>
			<?php }
		}

		//Remove core WP admin bar head CSS and add our own
		public function remove_admin_bar_bump(){
			remove_action('wp_head', '_admin_bar_bump_cb');
		}

		//Embedded Admin Bar CSS
		//Override some styles and add custom functionality
		//Used on the front-end, but not in Admin area
		public function admin_bar_style_script_overrides(){
			if ( $this->is_minimal_mode() ){return null;}

			if ( !$this->is_admin_page(true) && is_admin_bar_showing() ){ ?>
				<style type="text/css">
					html {margin-top: 32px !important; transition: margin-top 0.5s linear;}
					* html body {margin-top: 32px !important;}

					#wpadminbar {transition: top 0.5s linear;
						.admin-bar-inactive & {top: -32px; overflow: hidden;}

						i,
						svg {-webkit-font-smoothing: antialiased;}

						.ab-sub-wrapper .fa-fw {width: 1.25em;}
						.svg-inline--fa {height: 1em;}
					}

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

				<script>
					//Nebula keyboard shortcuts for frontend Admin
					jQuery(document).on('keydown', function(e){
						//Admin Bar Toggle
						if ( e.altKey && e.keyCode === 65 ){ //Alt+A
							jQuery('html').toggleClass('admin-bar-inactive');
						}

						//Reprocess all Sass files
						if ( e.altKey && e.keyCode === 82 ){ //Alt+R
							var url = new URL(window.location.href);
							url.searchParams.set('sass', 'true');
							location = url; //Reload with the new URL
						}
					});
				</script>
			<?php }
		}

		//Nebula Theme Update Checker
		public function theme_json(){
			if ( $this->is_minimal_mode() ){return null;}
			$override = apply_filters('pre_nebula_theme_json', null);
			if ( isset($override) ){return;}

			$this->timer('Theme Update Checker');
			$nebula_data = get_option('nebula_data');

			//Always keep current_version up-to-date.
			if ( empty($nebula_data['current_version']) || empty($nebula_data['current_version_date']) || strtotime($nebula_data['current_version_date'])-strtotime($this->version('date')) < 0 ){
				$this->update_data('current_version', $this->version('raw'));
				$this->update_data('current_version_date', $this->version('date'));
			}

			if ( current_user_can('update_themes') && is_child_theme() ){
				//Can no longer conditionally require the PUC library here because it needs a "use" token which can only be done at the top of this file with no conditionals. https://github.com/YahnisElsts/plugin-update-checker/releases/tag/v5.0
				$theme_update_checker = PucFactory::buildUpdateChecker(
					'https://raw.githubusercontent.com/chrisblakley/Nebula/main/inc/data/nebula_theme.json',
					get_template_directory() . '/functions.php',
					'Nebula' //The filter hook above must match this
				);
			}

			$this->timer('Theme Update Checker', 'end');
		}

		//Force a re-install of the Nebula theme
		public function force_nebula_theme_update($updates){
			if ( empty($updates) ){ //If no updates are available at the time of checking, ignore it. This filter runs multiple times, so only need ones that includes updates.
				return $updates;
			}

			if ( isset($this->super->get['force-nebula-theme-update']) && current_user_can('update_themes') && $this->is_nebula() && is_child_theme() ){
				if ( empty(wp_cache_get('nebula_force_theme_update_log')) ){ //Only log this once per pageload
					$parent_theme = wp_get_theme()->get('Template');

					$updates->response[$parent_theme] = array(
						'theme' => $parent_theme,
						'new_version' => $this->version('full'), //Does not need to be larger than current version
						'url' => 'https://github.com/chrisblakley/Nebula/commits/main',
						'package' => 'https://github.com/chrisblakley/Nebula/archive/main.zip'
					);

					$log_force_theme_update = $this->add_log('Nebula theme re-install (forced via WP) of version: ' . $this->version('full'), 7);
					wp_cache_set('nebula_force_theme_update_log', $log_force_theme_update, 'nebula', MINUTE_IN_SECONDS); //Store boolean in object cache
				}
			}

			return $updates;
		}

		//When checking for theme updates, store the next and current Nebula versions from the response. Hook is inside the theme-update-checker.php library.
		public function theme_update_version_store($update){
			if ( !empty($update) && $this->allow_theme_update() ){ //Update is null if/when the update checker is somehow disabled at the server-level (like if the cron is deactivated)
				$this->update_data('next_version', $update->version);
				$this->update_data('current_version', $this->version('full'));
				$this->update_data('current_version_date', $this->version('date'));
			}

			return $update; //Always return $update from this hook to prevent errors in the library!
		}

		//Update the theme, output progress, and post-update tasks
		//Note: The "old" theme functions will be what runs during the update process and those hooks/actions will be used. The "new" theme version files are not used until the next page load after the update processing page.
		public function theme_update_automation($wp_upgrader, $hook_extra){
			$override = apply_filters('pre_nebula_theme_update_automation', null);
			if ( isset($override) ){return;}

			if ( $this->allow_theme_update() ){
				if ( $hook_extra['type'] === 'theme' && $this->in_array_r('Nebula-main', $hook_extra['themes']) ){
					$prev_version = $this->get_data('current_version');
					$prev_version_commit_date = $this->get_data('current_version_date');
					$new_version = $this->get_data('next_version');
					$num_theme_updates = $this->get_data('num_theme_updates')+1;
					$current_user = wp_get_current_user();

					$this->output_nebula_update_progress('Updating Nebula from ' . $prev_version . ' to ' . $new_version . ' (by ' . $current_user->display_name . ').');
					$this->usage('automated_theme_update', array('version_numbers' => 'From ' . $prev_version . ' to ' . $new_version));

					$log_success = $this->add_log('Nebula theme update (via WP) from ' . $prev_version . ' to ' . $new_version, 5);
					$log_progress_message = ( !empty($log_success) )? 'Annotated in <a href="/themes.php?page=nebula_options&tab=diagnostic#nebula_logs_metabox">Nebula diagnostic logs</a>!' : 'Skipped annotating in Nebula diagnostic logs.';
					$this->output_nebula_update_progress($log_progress_message);

					$this->output_nebula_update_progress('Sending admin notification email(s)...');
					$mail_success = $this->theme_update_email($prev_version, $prev_version_commit_date, $new_version); //Send email with update information
					$mail_progress_message = ( !empty($mail_success) )? 'Admin emails sent successfully!' : 'Admin email notifications have failed.';
					$this->output_nebula_update_progress($mail_progress_message);

					$this->update_data('need_sass_compile', 'true'); //Compile all SCSS files on next pageview
					$this->update_data('num_theme_updates', $num_theme_updates);
					$this->update_data('last_automated_update_date', date('U'));
					$this->update_data('last_automated_update_user', $current_user->display_name);
					$this->update_data('check_new_options', 'true'); //Check for new Nebula Options on next pageview

					//Reprocess Sass if enabled
					if ( $this->get_option('scss') ){
						$this->output_nebula_update_progress('Re-processing Sass files...');
						if ( $this->scss_controller(true) ){ //Re-render all SCSS files (which returns boolean)
							$this->output_nebula_update_progress('Sass processing was successful.');
						} else {
							$this->output_nebula_update_progress('Sass processing was unsuccessful. <strong><a href="' . admin_url('?sass=true') . '" target="_parent">Please manually process Sass after the update.</a></strong>');
						}
					}

					$this->output_nebula_update_progress('All Nebula update tasks have been completed.');
				}
			}
		}

		//Output progress statuses of the Nebula theme update
		public function output_nebula_update_progress($message=''){
			try {
				global $pagenow;
				if ( $pagenow === 'update.php' && !empty($message) ){ //During the actual update $pagenow is "update.php" not "update-core.php"
					echo '<p>' . $message . '</p>';
					do_action('qm/info', 'Nebula Theme Update Progress: ' . $message);
				}
			} catch(exception $error){
				//Ignore output errors to prevent interrupting update
				do_action('qm/info', $error);
			}
		}

		//Log when WordPress core is updated and notify administrators. This happens for manual and automatic updates.
		public function log_core_wp_updates($new_wp_version){
			global $wp_version; //This is still the old version number at this point
			$old_wp_version = $wp_version; //Rename the variable to reduce confusion (old vs. new)

			$this->add_log('WordPress core was updated from ' . $old_wp_version . ' to ' . $new_wp_version . '.', 7);
			$this->usage('wp_core_update', array('version_numbers' => 'WP Core Update from ' . $old_wp_version . ' to ' . $new_wp_version));

			$current_user = wp_get_current_user();
			$subject = 'WordPress core updated from ' . $old_wp_version . ' to ' . $new_wp_version . ' for ' . html_entity_decode(get_bloginfo('name')) . '.';
			$message = '<p>WordPress core has been updated to ' . $new_wp_version . ' for ' . get_bloginfo('name') . ' (' . home_url('/') . ') by ' . $current_user->display_name . ' on ' . date('F j, Y') . ' at ' . date('g:ia') . '. The previous version was ' . $old_wp_version . '.</p>';

			$this->send_email_to_admins($subject, $message);
		}

		//Log when a plugin is activated
		public function log_plugin_activated($plugin, $network_wide){
			$plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
			$plugin_name = $plugin_data['Name'];
			$plugin_version = $plugin_data['Version'];

			$this->add_log('Plugin activated: ' . $plugin_name . ' (Version: ' . $plugin_version . ')', 6);
		}

		//Log when a plugin is deactivated
		public function log_plugin_deactivated($plugin, $network_wide){
			$plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
			$plugin_name = $plugin_data['Name'];
			$plugin_version = $plugin_data['Version'];

			$this->add_log('Plugin deactivated: ' . $plugin_name . ' (Version: ' . $plugin_version . ')', 6);
		}

		//Log when a plugin is updated
		public function log_plugin_updated($upgrader_object, $options){
			if ( $options['type'] == 'plugin' && $options['action'] == 'update' ){
				// Loop through each plugin being updated
				foreach ($options['plugins'] as $plugin) {
					$plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
					$plugin_name = $plugin_data['Name'];
					$plugin_version = $plugin_data['Version'];

					$this->add_log('Plugin updated: ' . $plugin_name . ' (To version: ' . $plugin_version . ')', 4); //Level 4 so these will be removed when logs are cleaned
				}
			}
		}

		//Send an email to the current user and site admin that Nebula has been updated.
		public function theme_update_email($prev_version, $prev_version_commit_date, $new_version){
			if ( $prev_version !== $new_version ){
				$current_user = wp_get_current_user();

				$subject = 'Nebula updated to ' . $new_version . ' for ' . html_entity_decode(get_bloginfo('name')) . '.';
				$message = '<p>The parent Nebula theme has been updated from version <strong>' . $prev_version . '</strong> (Committed: ' . $prev_version_commit_date . ') to <strong>' . $new_version . '</strong> for ' . get_bloginfo('name') . ' (' . home_url('/') . ') by ' . $current_user->display_name . ' on ' . date('F j, Y') . ' at ' . date('g:ia') . '.<br/><br/>To revert, find the previous version in the <a href="https://github.com/chrisblakley/Nebula/commits/main" target="_blank" rel="noopener">Nebula GitHub repository</a>, download the corresponding .zip file, and upload it replacing /themes/Nebula-main/.</p>';

				return $this->send_email_to_admins($subject, $message);
			}

			return null;
		}

		//Send an email to the current user and site admin(s)
		public function send_email_to_admins($subject, $message, $attachments=false){
			$nebula_admin_email_sent = $this->transient('nebula_admin_email_sent', function($data){
				$current_user = wp_get_current_user();
				$to = $current_user->user_email;

				$headers = array(); //Prep a headers array if needed

				$carbon_copies = $this->get_notification_emails(false);
				$headers[] = 'Cc: ' . implode(',', $carbon_copies);

				//Set the content type to text/html for the email.
				add_filter('wp_mail_content_type', function($content_type){
					return 'text/html';
				});

				//Send the email, and on success set a transient to prevent multiple emails
				if ( wp_mail($to, $data['subject'], $data['message'], $headers, $data['attachments']) ){ //wp_mail() returns boolean
					do_action('qm/info', 'Admin email successfully sent');
					return true; //Success
				}

				do_action('qm/error', 'Admin email failed (non-fatal, but email was not sent)');
				return false; //Failed (non-fatal, but email was not sent)
			}, array('subject' => $subject, 'message' => $message, 'attachments' => $attachments, ), MINUTE_IN_SECONDS);

			return $nebula_admin_email_sent; //This is boolean
		}

		//Get the notification email address and all developer administrators
		public function get_notification_emails($include_current_user=true){
			$notification_emails = array();

			//Get the notification email address
			$admin_user_email = $this->get_option('notification_email', $this->get_option('admin_email'));
			if ( !empty($admin_user_email) ){
				$notification_emails[] = $admin_user_email;
			}

			//CC all developer administrators as well.
			$developer_domains = explode(',', preg_replace('/\s+/', '', $this->get_option('dev_email_domain', '')));
			$administrators = get_users(array('role' => 'administrator'));
			foreach ( $administrators as $administrator ){
				foreach ( $developer_domains as $developer_domain ){
					if ( str_contains($administrator->user_email, $developer_domain) ){
						$notification_emails[] = $administrator->user_email;
					}
				}
			}

			$notification_emails = array_unique($notification_emails); //Remove duplicate values

			//Remove current user from the array if desired
			if ( empty($include_current_user) ){
				$current_user = wp_get_current_user();
				$notification_emails = array_values(array_diff($notification_emails, array($current_user->user_email))); //Remove current user from array and re-index
			}

			//Filter out any non-strings and non-email addresses and return the array
			return array_filter($notification_emails, function($value){
				if ( is_string($value) && str_contains($value, '@') ){
					return true;
				}
			});
		}

		//Control session time (for the "Remember Me" checkbox)
		public function session_expire($expirein){
			return MONTH_IN_SECONDS; //Default is 1209600 (14 days)
		}

		//Send Google Analytics pageviews on the WP Admin and Login pages too
		//Note: This is essentially a simplified version of what happens in /inc/analytics.php on the front-end
		public function admin_ga_pageview(){
			if ( $this->is_minimal_mode() ){return null;}

			if ( empty($this->super->post['signed_request']) && $this->get_option('ga_measurement_id') ){
				$user_properties = array(); //For parameters that should persist across sessions
				$pageview_properties = array( //For parameters that should be associated with this particular page/session
					'send_page_view' => true, //This is the default value, but setting it here in case other systems want to modify it
					'nebula_referrer' => $this->referrer //This is the original referrer (not just the previous page)
				);

				//WordPress User ID
				if ( $this->get_option('ga_wpuserid') && is_user_logged_in() ){
					$pageview_properties['user_id'] = get_current_user_id(); //This property must be less than 256 characters (and cannot match the CID). Note: Pageview Property is correct (do not use a user property for this particular parameter)!
					$user_properties['wp_user'] = get_current_user_id(); //This is to track WP users even if they are logged out
					$pageview_properties['wp_id'] = get_current_user_id(); //This is to track WP user IDs of visitors who are currently logged in
				}

				//WP Role (regardless of logged-in state)
				$user_properties['user_role'] = $this->user_role(); //User-scoped role property
				$pageview_properties['role'] = $this->user_role(); //Event-scoped user role property

				if ( $this->is_staff() || $this->is_internal_referrer() ){
					$pageview_properties['traffic_type'] = 'internal'; //Pageview property is correct (not user property) for internal traffic
				}

				//Query Strings
				if ( !empty($this->url_components('query')) ){
					$pageview_properties['query_string'] = $this->url_components('query');
				}

				?>
					<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_html($this->get_option('ga_measurement_id', '')); ?>"></script>
					<script>
						window.dataLayer = window.dataLayer || [];
						function gtag(){dataLayer.push(arguments);}
						gtag('js', new Date());

						//Prep a JS object for User Properties
						nebula.userProperties = <?php echo wp_json_encode(apply_filters('nebula_ga_user_properties', $user_properties)); //Allow other functions to modify the PHP user properties ?>;

						//Prep a JS object for Pageview Properties
						nebula.pageviewProperties = <?php echo wp_json_encode(apply_filters('nebula_ga_pageview_properties', $pageview_properties)); //Allow other functions to modify the PHP pageview properties ?>;

						gtag('set', 'user_properties', nebula.userProperties); //Apply the User Properties
						gtag('config', '<?php echo esc_html($this->get_option('ga_measurement_id', '')); ?>', nebula.pageviewProperties); //This sends the page_view
					</script>
				<?php
			}
		}

		//Change link of login logo to live site
		public function custom_login_header_url(){
			return home_url('/');
		}

		//Change alt of login image
		public function new_wp_login_title(){
			return get_option('blogname');
		}

		//Nebula Admin Notices/Warnings/Notifications
		public function show_admin_notices(){
			if ( $this->is_minimal_mode() ){return null;}

			$this->timer('Admin Notices', 'start', '[Nebula] Warnings');

			$warnings = $this->check_warnings();

			//If there are warnings display them
			if ( !empty($warnings) ){
				foreach( $warnings as $warning ){
					$category = ( !empty($warning['category']) )? '[' . $warning['category'] . ']' : '[Nebula]';
					$dismissible_class = ( !empty($warning['dismissible']) )? 'is-dismissible' : '';

					if ( $warning['level'] === 'warning' ){
						$warning['level'] = 'warning';
					}

					if ( $warning['level'] === 'log' ){
						$warning['level'] = 'info';
					}

					echo '<div class="nebula-admin-notice notice notice-' . $warning['level'] . ' ' . $dismissible_class . '"><p><span class="nebula-warning-category">' . $category . '</span> ' . $warning['description'] . '</p></div>';
				}
			}

			//Now check for non-warnings for minor notes
			//Check if CF7 spam capture is disabled when attempting to view the spam submission list
			if ( get_post_type() == 'nebula_cf7_submits' && get_post_status() === 'spam' && !$this->get_option('cf7_spam_submission_capture') ){
				echo '<div class="nebula-admin-notice notice notice-error notice-emphasize"><p><i class="fa-solid fa-fw fa-triangle-exclamation"></i> <strong><a href="' . admin_url('themes.php?page=nebula_options&tab=functions&option=cf7_spam_submission_capture') . '">Nebula CF7 spam capture</a> is currently disabled!</strong> New spam submissions will not appear here.</p></div>';
			}

			$this->timer('Admin Notices', 'end');
		}

		//Check the current (or passed) PHP version against the PHP support timeline.
		public function php_version_support($php_version=PHP_VERSION){
			if ( $this->is_minimal_mode() ){return null;}
			$override = apply_filters('pre_nebula_php_version_support', null, $php_version);
			if ( isset($override) ){return;}

			$php_timeline = $this->transient('nebula_php_timeline', function(){
				$php_timeline_json_file = get_template_directory() . '/inc/data/php_timeline.json'; //This local JSON file will either be updated or used directly later
				global $wp_filesystem;
				WP_Filesystem();

				$response = $this->remote_get('https://raw.githubusercontent.com/chrisblakley/Nebula/main/inc/data/php_timeline.json'); //Get the latest JSON file from Nebula GitHub
				if ( !is_wp_error($response) && isset($response['body']) ){
					$php_timeline = $response['body'];
					if ( !empty($php_timeline) ){
						$wp_filesystem->put_contents($php_timeline_json_file, $php_timeline); //Update the local JSON file with the new remote file
						return $php_timeline;
					}
				}

				return $wp_filesystem->get_contents($php_timeline_json_file); //Otherwise use the existing local JSON file
			}, MONTH_IN_SECONDS);

			$php_timeline = json_decode($php_timeline);
			if ( !empty($php_timeline) ){
				preg_match('/^(?<family>\d\.\d)\.?/i', PHP_VERSION, $current_php_version); //Grab the major/minor version of this PHP

				if ( isset($php_timeline[0]->{$current_php_version['family']}) ){ //If this version of PHP is in the local JSON file
					$php_version_info = $php_timeline[0]->{$current_php_version['family']}; //Find this major/minor version "family" of PHP in the JSON

					if ( !empty($php_version_info) ){ //If a match for this PHP version "family" was found in the JSON data
						$output = array();

						if ( !empty($php_version_info->security) && time() < strtotime($php_version_info->security) ){
							$output['lifecycle'] = 'active';
						} elseif ( !empty($php_version_info->security) && (time() >= strtotime($php_version_info->security) && time() < strtotime($php_version_info->end)) ){
							$output['lifecycle'] = 'security';
						} elseif ( time() >= strtotime($php_version_info->end) ){
							$output['lifecycle'] = 'end';
						} else {
							$output['lifecycle'] = 'unknown'; //An error of some kind has occurred.
						}

						$output['security'] = strtotime($php_version_info->security);
						$output['end'] = strtotime($php_version_info->end);

						return $output;
					}
				}
			}

			return null;
		}

		//Allow SVG files to be uploaded to the Media Library
		public function additional_upload_mime_types($mime_types){
			$mime_types['svg'] = 'image/svg+xml';
			return $mime_types;
		}
		public function allow_svg_uploads($data, $file, $filename, $mimes){
			$filetype = wp_check_filetype($filename, $mimes);

			return array(
				'ext' => $filetype['ext'],
				'type' => $filetype['type'],
				'proper_filename' => $data['proper_filename']
			);
		}

		//Change default values for the upload media box
		public function custom_media_display_settings(){
			//update_option('image_default_align', 'center');
			update_option('image_default_link_type', 'none');
			//update_option('image_default_size', 'large');
		}

		public function id_column_head($columns){
			$columns['id'] = 'ID';
			return $columns;
		}

		public function id_sortable_column($columns){
			$columns['id'] = 'id';
			return $columns;
		}

		public function id_column_content($column_name, $id){
			if ( $column_name === 'id' ){
				echo intval($id);
			}
		}

		public function id_column_orderby($query){
			if ( $this->is_admin_page() ){
				$orderby = $query->get('orderby');

				if ( $orderby === 'id' ){
					$query->set('orderby', 'id');
				}
			}
		}

		//Remove most Yoast SEO columns
		public function remove_yoast_columns($columns){
			//unset($columns['wpseo-score']);
			unset($columns['wpseo-title']);
			unset($columns['wpseo-metadesc']);
			unset($columns['wpseo-focuskw']);
			return $columns;
		}

		//Lower the Yoast post metabox
		public function lower_yoast_post_metabox(){
			return 'low';
		}

		//Prevent Yoast from publishing author sitemaps when Nebula author bios are disabled
		public function disable_yoast_author_indexing(){
			if ( class_exists('WPSEO_Options') ){
				WPSEO_Options::set('disable-author', true);
			}
		}

		//Add the "duplicate" link to the post actions list (this works for custom post types too).
		public function duplicate_post_link($actions, $post){
			if ( $this->is_minimal_mode() ){return $actions;}

			if ( current_user_can('edit_posts') && $post->post_type !== 'nebula_cf7_submits' ){
				$actions['duplicate'] = '<a href="admin.php?action=duplicate_post_as_draft&post=' . $post->ID . '" title="Duplicate this item" rel="permalink">Duplicate</a>';
			}
			return $actions;
		}

		//Duplicate post
		public function duplicate_post_as_draft(){
			global $wpdb;

			if ( !(isset($this->super->get['post']) || isset($this->super->post['post']) || (isset($this->super->request['action']) && $this->super->request['action'] === 'duplicate_post_as_draft')) ){
				wp_die('No post to duplicate has been supplied!');
			}

			$post_id = ( isset($this->super->get['post'] )? intval($this->super->get['post']) : intval($this->super->post['post'])); //Get the original post id
			$post = get_post($post_id); //Get all the original post data

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
				$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id"); //DB Query
				if ( count($post_meta_infos) !== 0 ){
					$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
					foreach ( $post_meta_infos as $meta_info ){
						$meta_key = $meta_info->meta_key;
						$meta_value = addslashes($meta_info->meta_value);
						$sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
					}
					$sql_query .= implode(" UNION ALL ", $sql_query_sel);
					$wpdb->query($sql_query); //DB Query
				}

				wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id)); //Redirect to the edit post screen for the new draft
				exit;
			} else {
				wp_die('Post creation failed, could not find original post: ' . $post_id);
			}
		}

		//Show File URL column on Media Library listings
		public function muc_column($cols){
			$cols['media_url'] = 'File URL';
			return $cols;
		}

		public function muc_value($column_name, $id){
			if ( $column_name === 'media_url' ){
				echo '<input type="text" width="100%" value="' . wp_get_attachment_url($id) . '" readonly />';
			}
		}

		//Remove unnecessary statuses from the CF7 submission listing admin status list
		public function cf7_submissions_status_list_cleanup($views){
			unset($views['private']);
			unset($views['draft']);
			unset($views['mine']);
			//Note: "trash" will only appear when it contains submissions, so not explicitly removing it here
			return $views;
		}

		//Add columns to CF7 submission listings
		public function cf7_submissions_columns_head($columns){
			if ( $this->is_minimal_mode() ){return $columns;}

			if ( $this->is_admin_page() && get_post_type() == 'nebula_cf7_submits' ){
				$columns['formatted_date'] = 'Formatted Date';
				$columns['form_name'] = 'Form Name';
				$columns['page_title'] = 'Page Title';

				if ( $this->get_option('attribution_tracking') ){
					$columns['attribution'] = 'Attribution';
				}

				if ( get_post_status() == 'invalid' ){
					$columns['ga_cid'] = 'GA Client ID'; //Best way to "identify" multiples from the same user
				} elseif ( get_post_status() == 'spam' ){
					$columns['anonymized_ip'] = 'Anonymized IP';
				}

				$columns['notes'] = 'Internal Notes';
				$columns['cf7_version'] = 'CF7 Version';

				unset($columns['date']); //Replacing the WP date column with our own
				unset($columns['id']); //This ID is confusing since it is the submission ID
			}

			return $columns;
		}
		public function cf7_submissions_columns_sortable($columns){
			if ( $this->is_minimal_mode() ){return $columns;}

			if ( $this->is_admin_page() && get_post_type() == 'nebula_cf7_submits' ){
				$columns['formatted_date'] = 'date';
				$columns['form_name'] = 'form_name';
				$columns['page_title'] = 'page_title';
				$columns['notes'] = 'notes';
			}

			return $columns;
		}

		//Custom columns content to CF7 submission listings
		public function cf7_submissions_columns_content($column_name, $submission_id){
			if ( $this->is_minimal_mode() ){return null;}

			if ( $this->is_admin_page() && get_post_type() == 'nebula_cf7_submits' ){
				$submission_data = get_post($submission_id); //Remember: this $submission_id is the submission ID (not the form ID)!
				$form_data = json_decode($submission_data->post_content);

				if ( empty($form_data) ){
					$form_data = json_decode(strip_tags($submission_data->post_content)); //It may be spam, so strip the tags to try to validate the JSON
				}

				$form_id = ( is_object($form_data) && !empty($form_data->_wpcf7) )? $form_data->_wpcf7 : false; //CF7 Form ID
				$post_id = ( is_object($form_data) && !empty($form_data->_wpcf7_container_post) )? $form_data->_wpcf7_container_post : false; //The page the CF7 submission was from. @todo "Nebula" 0: Is this still working? Post Titles are all empty

				if ( $column_name === 'formatted_date' ){
					$today_text = '';
					$today_icon = '';
					$time_diff_icon = '<i class="fa-regular fa-fw fa-calendar"></i>'; //Default for longer than a day ago

					$time_diff = time()-strtotime(get_post_field('post_date', $submission_id));

					if ( $time_diff < HOUR_IN_SECONDS ){ //Within the hour
						$time_diff_icon = '<i class="fa-solid fa-fw fa-stopwatch"></i>';
						$today_icon = '<span class="cf7-submits-today-color">&rarr;</span> ';
						$today_text = ( $time_diff < MINUTE_IN_SECONDS*10 )? 'Just Now' : 'Past Hour';
					} elseif ( date('Y-n-j', strtotime(get_post_field('post_date', $submission_id))) == date('Y-n-j') ){ //Within today's calendar date
						$time_diff_icon = '<i class="fa-solid fa-fw fa-clock"></i>';
						$today_icon = '<span class="cf7-submits-today-color">&raquo;</span> ';
						$today_text = 'today';
					} elseif ( $time_diff < DAY_IN_SECONDS ){ //Within the last 24-hours
						$time_diff_icon = '<i class="fa-solid fa-fw fa-calendar-day"></i>';
						$today_icon = '<span>&rsaquo;</span> ';
					}

					$today_parens = ( !empty($today_text) )? ' <em>(' . ucwords($today_text) . ')</em>' : '';
					echo $today_icon . '<span class="' . str_replace(' ', '-', strtolower($today_text)) . '" title="' . ucwords($today_text) . '">' . date('l, F j, Y \a\t g:ia', strtotime(get_post_field('post_date', $submission_id))) . '</span><br /><small>' . $time_diff_icon . ' ' . human_time_diff(strtotime(get_post_field('post_date', $submission_id))) . ' ago' . $today_parens . '</small>';
				}

				if ( $column_name === 'form_name' ){
					if ( !empty($form_id) ){
						echo '<strong><a href="admin.php?page=wpcf7&post=' . $form_id . '&action=edit">' . get_the_title($form_id) . '</a></strong><br /><small><i class="fa-regular fa-fw fa-rectangle-list"></i> Form ID: ' . $form_id . '</small>';
					} else {
						echo '<span class="cf7-submits-possible-spam">Spam?<br /><small>The message could not be decoded.</small></span>';
					}
				}

				if ( $column_name === 'page_title' ){
					if ( !empty($post_id) ){
						echo '<a href="' . get_permalink($post_id) . '" target="_blank">' . get_the_title($post_id) . '</a><br /><small><i class="fa-regular fa-fw fa-window-maximize"></i> Post ID: ' . $post_id . '</small>';
					} elseif ( !empty($form_data->_detected_page_id) ){ //This is included on spam detected submissions
						echo '<span class="cf7-submits-possible-spam"><span>' . get_the_title($form_data->_detected_page_id) . '</span><br /><small>Post ID: ' . $form_data->_detected_page_id . '</small></span>';
					}
				}

				//Check for attribution data if it is being used
				if ( $this->get_option('attribution_tracking') && $column_name === 'attribution' && isset($submission_data) ){
					$decoded_submission_content = json_decode($submission_data->post_content);

					if ( !empty($decoded_submission_content->_nebula_attribution) ){
						if ( !empty(str_replace('{}', '', $decoded_submission_content->_nebula_attribution)) ){ //If we have attribution data
							$cleaned_attribution_output = str_replace(array(',', ':'), array(', ', ': '), $decoded_submission_content->_nebula_attribution);
							$truncated_output = ( strlen($cleaned_attribution_output) > 120 )? substr($cleaned_attribution_output, 0, 120) . '...' : $cleaned_attribution_output;
							echo '<span>' . $truncated_output . '</span>'; //Leave this as a span for the submission listing table
						}
					}
				}

				if ( $column_name === 'anonymized_ip' && !empty($form_data->_nebula_anonymized_ip) ){
					echo '<span><a href="https://whatismyipaddress.com/ip/' . $form_data->_nebula_anonymized_ip . '" target="_blank" rel="noopener noreferrer">' . $form_data->_nebula_anonymized_ip . '</a></span>';
				}

				if ( $column_name === 'ga_cid' && !empty($form_data->_nebula_ga_cid) ){
					echo '<span>' . $form_data->_nebula_ga_cid . '</span>';
				}

				if ( $column_name === 'notes' ){
					//Originally invalid submissions that were moved to the "successful" submissions listing status
					if ( get_post_status() == 'submission' && str_contains(strtolower(get_the_title($submission_id)), '(invalid)') ){
						echo '<p class="cf7-note-invalid"><i class="fa-solid fa-fw fa-triangle-exclamation"></i> <strong>Originally Invalid</strong><br /><small>This submission was originally invalid, but moved to this submissions list by a content manager. No email notification was sent out!</small></p>';
					}

					//Mail failed
					if ( str_contains(strtolower(get_the_title($submission_id)), 'mail fail') ){
						echo '<p class="cf7-note-failed"><i class="fa-solid fa-fw fa-triangle-exclamation"></i> <strong>Mail Failed</strong><br /><small>An administrator did not get an email notification of this submission!</small></p>';

						//If it failed within the last few days or if it is the latest submissions even beyond a week old, denote that in a transient
						global $wp_query;
						$latest_submission_id = ( !empty($wp_query->posts) )? $wp_query->posts[0]->ID : null;
						if ( strtotime(get_the_date('Y-m-d H:i:s', $submission_id)) >= strtotime('-2 days') || $submission_id == $latest_submission_id ){
							set_transient('smtp_status', 'Recent CF7 Fail Error', DAY_IN_SECONDS*5); //Remember this must contain the word "error" for Dashboard status check
						}
					}

					//Preserved submissions
					if ( !empty(get_post_field('nebula_cf7_submission_preserve', $submission_id)) ){
						echo '<p><i class="fa-solid fa-fw fa-shield-halved"></i> Preserved<br /><small>This submission will not be automatically deleted</small></p>';
					}

					//Internal Staff submissions
					if ( !empty($form_data->_nebula_staff) ){
						echo '<p class="cf7-note-internal"><i class="fa-solid fa-fw fa-clipboard-user"></i> Internal Staff<br /><small>This submission was by someone on the internal staff</small></p>';
					}

					//Check for caution indicators
					if ( get_post_status() != 'spam' && !empty($form_data->_nebula_spam_detection) ){
						echo '<p class="cf7-note-caution"><i class="fa-solid fa-fw fa-robot"></i> Possible Spam<br /><small>This submission may be spam</small></p>';
					} elseif ( empty($form_data->_nebula_form_flow) ){ //If the form flow data is missing it means they are not running JavaScript
						echo '<p class="cf7-note-caution"><i class="fa-solid fa-fw fa-code"></i> No JavaScript<br /><small>This user either has disabled JavaScript or is more likely a spambot</small></p>';
					} elseif ( ( !empty($form_data->_nebula_ga_cid) && str_contains($form_data->_nebula_ga_cid, '-') ) ){ //If this visitor is using an ad blocker
						echo '<p class="cf7-note-caution"><i class="fa-regular fa-fw fa-circle-user"></i> Ad-Blocker<br /><small>This user is either blocking analytics or possibly is a spambot</small></p>';
					}

					echo '<span>' . get_post_meta($submission_id, 'nebula_cf7_submission_notes', true) . '</span>';
				}

				if ( $column_name === 'cf7_version' && !empty($form_data->_wpcf7_version) ){
					echo '<span>' . $form_data->_wpcf7_version . '</span>';
				}

				echo '';
			}
		}
		public function cf7_submissions_columns_orderby($query){
			if ( $this->is_minimal_mode() ){return null;}

			if ( $this->is_admin_page() ){
				if ( $query->get('post_type') == 'nebula_cf7_submits' ){
					$query->set('orderby', 'ID'); //Default to sort by ID
					$query->set('order', 'DESC'); //Newest ID first

					$orderby = $query->get('orderby');

					if ( is_string($orderby) ){
						$orderby = strtolower($orderby);

						if ( $orderby === 'form_name' ){
							$query->set('orderby', 'form_name');
							$query->set('order', 'ASC');
						}

						if ( $orderby === 'page_title' ){
							$query->set('orderby', 'page_title');
							$query->set('order', 'ASC');
						}

						if ( $orderby === 'notes' ){
							$query->set('orderby', 'notes');
							$query->set('order', 'ASC');
						}
					}
				}
			}
		}

		//Add dropdown menu(s) for filtering CF7 submission listings
		public function cf7_submissions_filters($post_type){
			if ( $this->is_minimal_mode() ){return null;}

			if ( $this->is_admin_page() && $post_type == 'nebula_cf7_submits' ){
				//Get a list of CF7 forms
				$cf7_forms = array();
				if ( post_type_exists('wpcf7_contact_form') ){
					$cf7_filter_query = new WP_Query(array('post_type' => 'wpcf7_contact_form', 'post_per_page' => -1)); //Why is this empty when a filter is active? cf7_submissions_parse_query is conflicting!
					if ( $cf7_filter_query->have_posts() ){
						while ( $cf7_filter_query->have_posts() ){
							$cf7_filter_query->the_post();
							$cf7_forms[] = get_the_ID();
						}
						wp_reset_postdata();
					}
				}

				?>
				<label for="filter-by-form" class="screen-reader-text">Filter by form</label>
				<select id="filter-by-form" name="cf7_form_id">
					<option <?php echo ( empty($this->super->get['cf7_form_id']) )? 'selected="selected"' : ''; ?> value="0">All forms</option>
					<?php foreach ( $cf7_forms as $cf7_form ): ?>
						<option value="<?php echo $cf7_form; ?>" <?php echo ( !empty($this->super->get['cf7_form_id']) && $this->super->get['cf7_form_id'] == $cf7_form )? 'selected="selected"' : ''; ?>><?php echo get_the_title($cf7_form); ?></option>
					<?php endforeach; ?>
				</select>

				<?php $start_date = ( !empty($this->super->get['daterange_start']) )? $this->super->get['daterange_start'] : ''; ?>
				<label for="filter-by-daterange-start">Start Date:</label>
				<input type="date" id="filter-by-daterange-start" name="daterange_start" value="<?php echo $start_date; ?>" placeholder="Start Date" />

				<?php $end_date = ( !empty($this->super->get['daterange_end']) )? $this->super->get['daterange_end'] : ''; ?>
				<label for="filter-by-daterange-end">End Date:</label>
				<input type="date" id="filter-by-daterange-end" name="daterange_end" value="<?php echo $end_date; ?>" placeholder="End Date" />
				<?php
			}
		}

		//Handle the filters to only list desired CF7 submissions
		public function cf7_submissions_parse_query($query){
			if ( $this->is_minimal_mode() ){return $query;}

			if ( $query->query['post_type'] == 'nebula_cf7_submits' ){ //Only modify this specific query
				global $pagenow;
				$current_page = isset($this->super->get['post_type'])? $this->super->get['post_type'] : '';

				if ( $this->is_admin_page() && $current_page == 'nebula_cf7_submits' && $pagenow == 'edit.php' ){
					//Form ID filter
					if ( isset($this->super->get['cf7_form_id']) && $this->super->get['cf7_form_id'] != 0 ){
						$query->query_vars['meta_key'] = 'form_id';
						$query->query_vars['meta_value'] = $this->super->get['cf7_form_id'];
						$query->query_vars['meta_compare'] = '=';
					}

					//Date Range filter
					if ( isset($this->super->get['daterange_start']) || isset($this->super->get['daterange_end']) ){
						$start_date = ( isset($this->super->get['daterange_start']) )? $this->super->get['daterange_start'] : date('Y-m-d');
						$end_date = ( isset($this->super->get['daterange_end']) )? $this->super->get['daterange_end'] : date('Y-m-d');

						$query->query_vars['date_query'] = array(
							array(
								'after' => $start_date,
								'before' => $end_date,
								'inclusive' => true
							)
						);
					}

					//Invalid filter (to show only invalid)
					if ( isset($this->super->get['cf7_form_status']) && $this->super->get['cf7_form_status'] == 'invalid' ){
						$query->query_vars['post_status'] = 'invalid';
					}

					//Spam filter (to show only spam)
					if ( isset($this->super->get['cf7_form_status']) && $this->super->get['cf7_form_status'] == 'spam' ){
						$query->query_vars['post_status'] = 'spam';
					}
				}
			}

			return $query;
		}

		//Add buttons for additional actions with CF7 submissions
		public function cf7_submissions_actions($which){ //Which designates top or bottom
			if ( $this->is_minimal_mode() ){return null;}

			if ( $this->is_admin_page() && get_post_type() == 'nebula_cf7_submits' ){
				$filtered_id = ( !empty($this->super->get['cf7_form_id']) )? $this->super->get['cf7_form_id'] : '';

				//Determine where the export button should link to
				$export_text = 'Export <small>(WP Core)</small>';
				$export_url = 'export.php';
				if ( is_plugin_active('flamingo/flamingo.php') ){ //https://wordpress.org/plugins/flamingo/
					$export_text = 'Export <small>(Flamingo)</small>';
					$export_url = 'admin.php?page=flamingo_inbound';
				} elseif ( is_plugin_active('advanced-cf7-db/advanced-cf7-db.php') ){ //https://wordpress.org/plugins/advanced-cf7-db/
					$export_text = 'Export <small>(Advanced CF7 DB)</small>';
					$export_url = 'admin.php?page=contact-form-listing&cf7_id=' . $filtered_id;
				} elseif ( is_plugin_active('contact-form-cfdb7/contact-form-cfdb-7.php') ){ //https://wordpress.org/plugins/contact-form-cfdb7/
					$export_text = 'Export <small>(Contact Form CFDB7)</small>';
					$export_url = 'admin.php?page=cfdb7-list.php&fid=' . $filtered_id;
				} elseif ( is_plugin_active('wp-all-export-pro/wp-all-export-pro.php') ){
					$export_text = 'Export <small>(WP All Export)</small>';
					$export_url = 'admin.php?page=pmxe-admin-export';
				}

				echo '<a class="button" href="' . $export_url . '"><i class="fa-solid fa-fw fa-file-arrow-down"></i> ' . $export_text . '</a>';
			}
		}

		//Hide the "Private" post state usually appended to titles on CF7 submissions admin listings
		public function cf7_submissions_remove_post_state($post_states){
			if ( $this->is_admin_page() && get_post_type() == 'nebula_cf7_submits' ){
				return null;
			}

			return $post_states;
		}

		//Disable the quick actions on CF7 submissions admin listings
		public function cf7_submissions_remove_quick_actions($actions, $post){
			if ( $this->is_admin_page() && get_post_type() == 'nebula_cf7_submits' ){
				unset($actions['edit']);
				unset($actions['trash']);
				unset($actions['view']);
				unset($actions['inline hide-if-no-js']);
			}

			return $actions;
		}

		//Show a back button on CF7 submission detail pages
		public function cf7_submissions_back_button(){
			if ( $this->is_admin_page() && get_post_type() == 'nebula_cf7_submits' ){
				$cf7_status_listing_url = admin_url('edit.php?post_type=nebula_cf7_submits&post_status=' . get_post_status());

				$form_type_label = 'Submissions';
				if ( get_post_status() == 'invalid' ){
					$form_type_label = 'Invalid Submissions';
				} elseif ( get_post_status() == 'spam' ){
					$form_type_label = 'Spam';
				}

				echo '<a class="button" href="' . $cf7_status_listing_url . '">&laquo; Back to CF7 ' . ucwords($form_type_label) . '</a>';
			}
		}

		//Output the details of each CF7 submission
		public function cf7_storage_output($post){
			if ( $this->is_admin_page() && $post->post_type === 'nebula_cf7_submits' ){
				$form_data = json_decode($post->post_content);
				$is_spam = ( $post->post_status === 'spam' || empty($form_data) || empty($form_data->_wpcf7) );
				$is_invalid = ( $post->post_status === 'invalid' || str_contains($post->post_title, '(Invalid)') ); //If it was originally invalid or moved from the "invalid" status

				//Check for suspicious indicators of bot/spam submissions that were logged as actual submissions
				$is_caution = false;
				if ( !$is_spam ){
					//Check if the send mail failed
					if ( str_contains(strtolower(get_the_title($post->ID)), 'mail fail') ){
						echo '<div class="nebula-cf7-notice notice-mail-failed"><p><i class="fa-solid fa-fw fa-envelope"></i> <strong>Email notification failed.</strong> The email notification to administators has failed for this submission.</p></div>';

						//If it failed within the last few days, denote that in a transient
						if ( strtotime(get_the_date('Y-m-d H:i:s', $post->ID)) >= strtotime('-2 days') ){
							set_transient('smtp_status', 'Recent CF7 Fail Error', DAY_IN_SECONDS*5); //Remember this must contain the word "error" for Dashboard status check
						}
					}

					//Loop through the form data to look for HTML tags
					foreach ( $form_data as $key => $value ){
						if ( substr($key, 0, 1) != '_' && is_string($value) ){ //If it is not a metadata field and we have data
							//Check for unicode character encodings
							if ( preg_match('/^u[0-9a-fA-F]{4}/', $value) === 1 ){
								$is_caution = '<i class="fa-solid fa-fw fa-circle-question text-info"></i> <strong class="text-info">Caution:</strong> <strong>Unicode encodings were detected in this submission.</strong> This is a high likelyhood of spam!';
								break;
							}

							//Check for HTML hyperlink tags
							if ( preg_match("/<a.*href=.*>/i", $value) ){
								$is_caution = '<i class="fa-solid fa-fw fa-circle-question text-info"></i> <strong class="text-info">Caution:</strong> A hyperlink HTML tag was found in the submission. If users are not expected to be including HTML this could imply that this may be a bot or spam.';
								break;
							}
						}
					}

					if ( empty($is_caution) ){ //If the above checks did not find any problems, continue checking other aspects
						if ( empty($form_data->_nebula_ga_cid) || str_contains($form_data->_nebula_ga_cid, '-') ){
							$is_caution = '<i class="fa-solid fa-fw fa-circle-question text-info"></i> <strong class="text-info">Caution:</strong> This user has a non-native Google Analytics Client ID (' . $form_data->_nebula_ga_cid . '). This could mean <strong>the user has an ad-blocker active, or that it may be a bot or spam.</strong>';
						} elseif ( version_compare($form_data->_nebula_version, '11.10.29') >= 0 && empty($form_data->_nebula_form_flow) ){ //@todo "Nebula 0: After a while the version_compare part of the conditional can be removed. The _nebula_form_flow field was added on March 29, 2024.
							$is_caution = '<i class="fa-solid fa-fw fa-circle-question text-info"></i> <strong class="text-info">Caution:</strong> The Nebula Form Flow field is empty which could mean <strong>the user has disabled JavaScript, or that it may be a bot or spam</strong>.';
						} else {
							//Loop through the form data to look for HTML tags
							foreach ( $form_data as $key => $value ){
								if ( substr($key, 0, 1) != '_' && is_string($value) && preg_match("/<a.*href=.*>/i", $value) ){
									$is_caution = '<i class="fa-solid fa-fw fa-circle-question text-info"></i> <strong class="text-info">Caution:</strong> A hyperlink HTML tag was found in the submission. If users are not expected to be including HTML this could imply that this may be a bot or spam.';
									break;
								}
							}
						}
					}
				}

				if ( empty($form_data) ){
					echo '<p>This submission has been noted as potential spam due to invalid JSON.</p>';

					$formatted_invalid_json = str_replace(array('{', '":', ',"', '}'), array('{<br />', '": ', ',<br />"', '<br />}'), htmlspecialchars($post->post_content));
					echo '<table id="nebula-cf7-submission-spam" class="nebula-cf7-submissions nebula-cf7-submission-spam"><thead><tr><td>Original Data</td><td>&nbsp;</td></tr></thead><tbody><tr><td><strong>Invalid JSON</strong><br />Possibly spam?<br /><br /><small>Note: the JSON here may validate when copy/pasted, but the original data could not be decoded.</small></td><td>' . $formatted_invalid_json . '</td></tr></tbody></table>';

					$form_data = json_decode(strip_tags($post->post_content)); //Now strip the tags and try parsing it again in the normal table
				}

				if ( !empty($form_data) ){
					if ( !empty($form_data->_nebula_staff) ){
						$staff_type = str_replace('Client', 'Content Manager', $form_data->_nebula_staff); //This will always exist regardless of logged-in state
						$staff_email = ( !empty($form_data->_nebula_email) )? ' (' . $form_data->_nebula_email . ')' : ''; //This will only exist if the staff member is logged into WordPress (as opposed to IP detection).
						echo '<div class="nebula-cf7-notice notice-internal"><p><i class="fa-solid fa-fw fa-clipboard-user"></i> <strong>Internal Staff (' . $staff_type . ')</strong> This submission was made by a user on the staff' . $staff_email . '.</p></div>';
					}

					if ( $is_spam ){
						echo '<div class="nebula-cf7-notice notice-spam"><p><i class="fa-solid fa-fw fa-triangle-exclamation text-danger"></i> <strong class="text-danger">This submission has been noted as potential spam.</strong> Any HTML tags have been removed from the data. Do not visit any URLs that may appear in the data.</p></div>';
					} else {
						if ( $is_invalid ){
							echo '<div class="nebula-cf7-notice notice-invalid"><p><i class="fa-solid fa-fw fa-comment-slash"></i> <strong>This submission was determined to be invalid.</strong> Invalid fields are highlighted below. The user was shown a validation error message when attempting to submit this information (see below). The user may have fixed the invalid fields and attempted to submit again, or they may have abandoned the form without re-submitting.</p><p>Note: If the acceptance checkbox was not checked, form field input data will have been removed from this submission and will not appear below as it was not processed or stored.</p></div>';
						}

						//Check if this submission was associated with any other submissions
						if ( !empty($form_data->_nebula_ga_cid) && !$this->is_minimal_mode() ){
							$submission_history_query = new WP_Query(array(
								'post_type' => 'nebula_cf7_submits',
								'post_status' => array('submission', 'invalid'),
								'posts_per_page' => 15, //Limit the number of results
								'orderby' => 'ID', //Use ID to avoid timezone confusion (not working for some reason, so adjusting below)
								'order' => 'ASC', //Earliest to more recent (not working for some reason, so adjusting below)
								's' => $form_data->_nebula_ga_cid,
							));

							if ( $submission_history_query->have_posts() ){
								$submission_history_posts = $submission_history_query->posts;

								//Sort the posts array by ID from lowest to highest (since the orderby and order in the above query is not working)
								usort($submission_history_posts, function($a, $b){
									return $a->ID-$b->ID;
								});

								$invalid_count = 0;
								$success_count = 0;
								$the_submissions = array();

								foreach ( $submission_history_posts as $post ){ //Loop through the posts
									setup_postdata($post); //Set up WP post data

									$invalid_form_data = json_decode(strip_tags(get_the_content(null, false, $post->ID)));

									$submission_class = 'invalid-submission-item';
									$submission_label = 'Invalid Submission &raquo;';
									$submission_icon = '<i class="fa-solid fa-fw fa-xmark"></i>';

									if ( get_post_status($post->ID) == 'submission' ){ //Only if it was a successful submission originally (and not moved from another status)
										if ( str_contains(get_the_title($post->ID), '(Invalid)') ){
											$invalid_count++;
										} elseif ( str_contains(get_the_title($post->ID), '(Mail Failed)') ){
											$invalid_count++;
											//$success_count++;
											$submission_class = 'error-submission-item'; //Nebula still captures the form, but the email was not sent
											$submission_label = 'Failed Submission &raquo;';
											$submission_icon = '<i class="fa-solid fa-fw fa-triangle-exclamation"></i>';
										} else {
											$success_count++;
											$submission_class = 'successful-submission-item';
											$submission_label = 'Successful Submission &raquo;';
											$submission_icon = '<i class="fa-solid fa-fw fa-check"></i>';
										}
									} else {
										$invalid_count++;
									}

									//get_the_ID() is the "overall" post that is being viewed on the page
									//$post->ID is the post in this loop of all the submissions from this user
									if ( get_the_ID() == $post->ID ){ //If the post in the list is the submission we are viewing the full details of
										$submission_class .= ' this-submission';
										$submission_label = 'This ' . str_replace(' &raquo;', '', $submission_label);
										$submission_icon = ( get_post_status() == 'submission' && !str_contains(get_the_title(), '(Invalid)') )? '<i class="fa-solid fa-fw fa-circle-check"></i><i class="fa-solid fa-arrow-right"></i>' : '<i class="fa-solid fa-fw fa-circle-xmark"></i><i class="fa-solid fa-arrow-right"></i>';
									}

									$the_submissions[] = '<li class="' . get_post_status($post->ID) . '-submission-item ' . $submission_class . '" data-id="' . $post->ID . '"><a href="' . get_edit_post_link($post->ID) . '"><strong>' . $submission_icon . ' ' . $submission_label . '</strong></a> <small>(' . get_the_title($invalid_form_data->_wpcf7) . ' on ' . get_the_date('l, F j, Y \a\t g:i:sa', $post->ID) . ')</small></li>';
								}

								if ( count($the_submissions) >= 2 ){ //If this user has submitted a form more than once (successfully or not)
									if ( $invalid_count >= 1 ){ //If any of those submissions were invalid
										echo '<div class="nebula-cf7-notice notice-warning"><p><i class="fa-solid fa-fw fa-circle-xmark color-invalid"></i> <strong>User had ' . $invalid_count . ' invalid attempt(s)!</strong> This user attempted to submit forms at least <strong>' . $invalid_count . ' time(s)</strong>.<ol>' . implode($the_submissions) . '</ol></p></div>';
									} else { //Otherwise, all submissions were successful
										echo '<div class="nebula-cf7-notice notice-success"><p><i class="fa-solid fa-fw fa-check-double color-submission"></i> <strong>User submitted multiple times.</strong> This user has submitted at least <strong>' . count($the_submissions) . ' forms</strong> successfully.<ol>' . implode($the_submissions) . '</ol></p></div>';
									}
								}

								if ( $is_invalid ){
									if ( $success_count == 0 ){
										echo '<div class="nebula-cf7-notice notice-error"><p><i class="fa-solid fa-fw fa-user-xmark text-danger"></i> <strong class="text-danger">User may have abandoned after failure!</strong> This user may <strong>not</strong> have submitted successfully after receiving these validation errors.</p></div>'; //"May" because if the email address was the one that was invalid, that may cause the query to return an empty result (since the post title relies on this)
									} else {
										echo '<div class="nebula-cf7-notice notice-success"><p><i class="fa-solid fa-fw fa-circle-check text-success"></i> <strong class="text-success">User was eventually successful!</strong> This user was able to fix these validation errors and submit successfully after this. <a href="' . get_edit_post_link(get_the_ID()) . '">View the successful submission &raquo;</a></p></div>';
									}
								}

								wp_reset_postdata(); //Reset the global post data
							}
						}

						if ( $is_caution ){
							echo '<div class="nebula-cf7-notice notice-caution"><p>' . $is_caution . '</p></div>';
						}
					}

					echo '<h2 class="nebula-form-title">' . get_the_title($form_data->_wpcf7) . '</h2>'; //Output the name of the form

					$form_output = array(
						'real' => array(),
						'metadata' => array(),
					);

					//Triage each datapoint into their appropriate type
					foreach ( $form_data as $key => $value ){
						if ( substr($key, 0, 1) === '_' || substr($key, 0, 3) === 'hp-' ){
							$form_output['metadata'][$key] = $value;
						} else {
							$form_output['real'][$key] = $value;
						}
					}

					$invalid_fields = array();
					if ( $is_invalid ){
						foreach ( json_decode($form_output['real']['invalid_fields']) as $invalid_field_name => $invalid_details ){
							array_push($invalid_fields, $invalid_field_name);
						}
					}

					//Output each data type
					foreach ( $form_output as $data_type => $datapoints ){
						if ( $this->is_minimal_mode() && $data_type == 'metadata' ){
							continue; //Skip this datapoint
						}

						$table_class = '';
						if ( !empty($is_spam) ){
							$table_class = 'nebula-cf7-submission-spam';
						} elseif ( !empty($is_invalid) ){
							$table_class = 'nebula-cf7-submission-invalid';
						} elseif ( !empty($is_caution) ){
							$table_class = 'nebula-cf7-submission-caution';
						}

						?>
						<table id="nebula-cf7-submission-<?php echo $data_type; ?>" class="nebula-cf7-submissions <?php echo $table_class; ?>">
							<thead>
								<tr>
									<td>
										<?php
											$data_label = 'Field';
											if ( $data_type === 'metadata' ){
												$data_label = 'Metadata';
											}

											echo $data_label;
										?>
									</td>
									<td>
										<?php
											$value_label = 'Value';
											if ( $is_spam ){
												$value_label = 'Sanitized Data';
											}

											echo $value_label;
										?>
									</td>
								</tr>
							</thead>
							<tbody>
						<?php

						foreach ( $datapoints as $key => $value ){
							$classes = array();

							if ( in_array($key, $invalid_fields) ){
								$classes[] = 'invalid-field';
							}

							if ( is_object($value) || (isset($value[0]) && is_object($value[0])) ){ //If the value is an object or an array of objects (such as with the CF7 Spam Log)
								$value = json_encode($value, JSON_PRETTY_PRINT);
							} elseif ( is_array($value) ){
								$value = implode(', ', $value);
							}

							if ( $key === '_wpcf7' || $key === 'form_name' || $key === 'form_id' ){
								$value = '<a href="admin.php?page=wpcf7&post=' . $form_data->form_id . '&action=edit">' . $value . '</a>';
							}

							if ( $key === '_wpcf7_container_post' ){
								$value = '<a href="' . get_permalink($value) . '">' . $value . ' (' . get_the_title($value) . ')</a>';
							}

							if ( $key === '_nebula_referrer' || $key === '_nebula_landing_page' ){
								$value = '<a href="' . $value . '">' . $value . '</a>';
							}

							if ( $key === '_nebula_current_page' ){
								$current_page_url= $value;
								$current_page_method = '';

								if ( str_contains($value, ' (') ){
									$parts = explode(' (', $value, 2); //Use the space and open parenthesis to ensure it is the proper separator
									$current_page_url = $parts[0];
									$current_page_method = '(' . $parts[1]; //The detection method used to determine the page the form was submitted from (and add the parenthesis back in here)
								}

								$value = '<a href="' . $current_page_url . '">' . $current_page_url . '</a> <small>' . $current_page_method . '</small>';
							}

							if ( $key === '_nebula_date_formatted' ){
								$value = $value . ' (' . human_time_diff($form_data->_nebula_timestamp) . ' ago)';
							}

							if ( $key === '_nebula_username' || $key === '_nebula_user_id' ){
								$value = '<a href="user-edit.php?user_id=' . $form_data->_nebula_user_id . '">' . $value . '</a>';
							}

							if ( $key === '_nebula_session_id' ){
								$value = str_replace(array(':', ';'), array(': ', '<br />'), $value);
							}

							if ( $key === '_nebula_ga_cid' ){
								$value = '<a href="' . $this->google_analytics_url() . '" target="_blank" rel="noopener noreferrer">' . $value . '</a>'; //If a user explorer is ever added to GA4, link directly to that report. Possibly even to this individual CID.
							}

							if ( $key === '_nebula_attribution' ){
								$output_value = '<pre>' . json_encode(json_decode($value), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</pre>';

								$explanations = $this->lookup_query_parameter_definition($value); //Pass the string to the lookup function
								if ( !empty($explanations) ){
									$output_value .= '<div id="attribution-explanations"><strong>Attribution Explanations</strong><br/><ul>';
									foreach ( $explanations as $explanation ){
										$output_value .= '<li>' . esc_html($explanation) . '</li>';
									}
									$output_value .= '</ul></div>';
								}

								$value = $output_value; //Update the original value for output
							}

							if ( $key === '_nebula_anonymized_ip' ){
								$value = '<a href="https://whatismyipaddress.com/ip/' . $value . '/" target="_blank" rel="noopener noreferrer">' . $value . '</a>';
							}

							if ( $key === '_nebula_geo_coordinates' ){
								$value = '<a href="' . esc_url('https://maps.google.com?q=' . $value . '') . '" target="_blank" rel="noopener noreferrer">' . $value . '</a>';
							}

							if ( $key === '_nebula_user_agent' ){
								$value = '<a href="https://developers.whatismybrowser.com/useragents/parse/" target="_blank" rel="noopener noreferrer">' . $value . '</a>';
							}

							if ( substr($key, 0, 1) === '_' || substr($key, 0, 3) === 'hp-' ){ //@todo "Nebula" 0: Use str_starts_with in PHP8
								$classes[] = 'wpcf7-metadata';
							}

							if ( empty($value) || $value == '[]' || $value == '{}' ){
								$classes[] = 'no-data';
							}

							if ( $key === 'mail_failed' ){
								$classes[] = 'mail-failed';
							}

							//Convert objects to strings
							if ( is_object($value) ){
								$value = json_decode(wp_json_encode($value), true);
							}
							if ( is_array($value) ){
								$value = implode(',', $value);
							}

							if ( $post->post_status === 'spam' ){ //This is for spam submissions that have valid JSON
								$value = strip_tags($value); //Do not parse HTML on potential spam submissions
							}

							echo '<tr class="' . implode(' ', $classes) . '"><td><strong>' . $key . '</strong></td>';
							echo '<td>' . $value . '</td></tr>';
						}

						echo '</tbody></table>';
					}
				} else {
					echo '<p>This data could not be sanitized for displaying in an organized method.</p>';
				}
			}
		}

		//Add the various CF7 submission statuses to the Publish status dropdown in the WP editor
		//This is a hacky way to do this, but WordPress does not have a better option as of March 2024
		function add_cf7_statuses_to_dropdown(){
			if ( $this->is_minimal_mode() ){return null;}

			if ( $this->is_minimal_mode() ){return null;}
			global $post;

			if ( $post->post_type == 'nebula_cf7_submits' ){
				echo '<script>';
					echo "jQuery(function(){";
						echo "jQuery('select[name=\"post_status\"]').append('";
							$complete = '';
							if( $post->post_status == 'submission' ){
								$complete = 'selected=\"selected\"';
							}
							echo "<option value=\"submission\" " . $complete . ">Submission</option>";

							$complete = '';
							if( $post->post_status == 'invalid' ){
								$complete = 'selected=\"selected\"';
							}
							echo "<option value=\"invalid\" " . $complete . ">Invalid</option>";

							$complete = '';
							if( $post->post_status == 'spam' ){
								$complete = 'selected=\"selected\"';
							}
							echo "<option value=\"spam\" " . $complete . ">Spam</option>";
						echo "');";
					echo "});";
				echo '</script>';
			}
		}

		//Add a badge icon to the admin menu indicating the number of new submissions today
		function add_cf7_menu_badge_count(){
			if ( $this->is_minimal_mode() ){return null;}

			global $submenu;

			$fresh = ( isset( $screen->id ) && $screen->id == 'edit-nebula_cf7_submits' )? true : false; //Update the badge when viewing the submissions listing admin page

			$badge_number = $this->transient('nebula_cf7_submits_badge', function(){
				$cf7_submissions_query = new WP_Query(array(
					'post_type' => 'nebula_cf7_submits',
					'post_status' => 'submission',
					'posts_per_page' => -1,
					'date_query' => array(
						'after' => date('Y-m-d'), //Today only
						'before' => date('Y-m-d'),
						'inclusive' => true,
					)
				));

				return $cf7_submissions_query->found_posts; //Count the number of posts from the query
			}, MINUTE_IN_SECONDS*10, $fresh);

			if ( $badge_number > 0 ){
				//Loop through the top-level submenu items to find the appropriate item to append to
				foreach ( $submenu as $key => $menu ){
					if ( $key == 'wpcf7' ){
						//Now loop through that submenu's submenu items
						foreach ( $menu as $index => $item ){
							if ( $item[2] == 'edit.php?post_type=nebula_cf7_submits' ){
								$submenu[$key][$index][0] .= '<small class="menu-counter cf7-submits-today-bg">' . $badge_number . ' Today</small>';
								break; //Exit the loop
							}
						}
					}
				}
			}
		}

		//Test if SMTP is working
		public function check_smtp_status(){
			if ( $this->is_minimal_mode() ){return null;}

			// Retrieve SMTP settings
			$smtp_host = defined('SMTP_HOST') ? SMTP_HOST : ini_get('SMTP'); // Fallback to php.ini 'SMTP' setting
			$smtp_port = defined('SMTP_PORT') ? SMTP_PORT : (int)ini_get('smtp_port'); // Fallback to php.ini 'smtp_port'

			if ( empty($smtp_host) || empty($smtp_port) ){
				return 'Unknown'; // Cannot test SMTP if no host or port is defined
			}

			//Check for cached status (this also includes recent CF7 Mail Failed errors from elsewhere)
			$cached_status = get_transient('smtp_status');
			if ( $cached_status !== false ) {
				return $cached_status; // Return cached result
			}

			$status = 'Tests Passed'; //Does not guarantee it is working, only that these tests passed

			//Open a connection to the SMTP server
			$connection = @stream_socket_client('tcp://' . $smtp_host . ':' . $smtp_port, $error_code, $error_message, 5); //Perform SMTP check (timeout is 5 seconds). Note: This "@" will suppress warnings even though "@" does not suppress fatal errors in PHP8+ it is okay here because the "errors" are only at the warning level.
			if ( !$connection ){
				$status = 'Connection Error';
			} else {
				//Perform SMTP handshake
				fwrite($connection, "EHLO localhost\r\n");
				$response = fgets($connection, 512);
				if ( !str_contains($response, '220') && !str_contains($response, '250') ){
					$status = 'Handshake Error';
				}

				//Close the connection
				fwrite($connection, "QUIT\r\n");
				fclose($connection);
			}

			set_transient('smtp_status', $status, HOUR_IN_SECONDS*12); //Cache the result for 12 hours

			return $status;
		}

		//All Settings page link
		public function all_settings_link(){
			add_theme_page('All Settings', 'All Settings', 'administrator', 'options.php'); //This appears as a submenu in the "Appearance" menu
		}

		//Ensure Relevanssi indexes Nebula custom fields
		//Note: The custom field setting in Relevanssi Settings > Indexing (tab) must not be "none" for this to work (Nebula does check for this and warns as needed)
		public function add_nebula_to_relevanssi($custom_fields, $post_id){
			$custom_fields[] = 'nebula_internal_search_keywords'; //Add the Nebula internal search custom field to Relevanssi
			return $custom_fields;
		}

		//Clear caches when plugins are activated if W3 Total Cache is active
		public function clear_all_w3_caches(){
			if ( $this->is_minimal_mode() ){return null;}
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
			if ( function_exists('w3tc_pgcache_flush') && isset($this->super->server['activate']) && $this->super->server['activate'] == 'true'){
				w3tc_pgcache_flush();
			}
		}

		//Admin footer left side
		public function change_admin_footer_left(){
			//return '';
		}

		//Admin footer right side
		public function change_admin_footer_right(){
			if ( $this->is_minimal_mode() ){return null;}
			global $wp_version;
			$child = ( is_child_theme() )? ' <small>(Child)</small>' : '';

			$wordpress_version_output = '';
			$nebula_version_output = 'Thank you for using Nebula!';
			if ( current_user_can('publish_posts') ){
				$wordpress_version_output = '<span><a href="https://codex.wordpress.org/WordPress_Versions" target="_blank" rel="noopener">WordPress</a> <strong>' . $wp_version . '</strong></span>, ';
				$nebula_version_output = '<span title="Committed: ' . $this->version('date') . '"><a href="https://nebula.gearside.com/?utm_campaign=documentation&utm_medium=footer&utm_source=' . urlencode(site_url()) . '&utm_content=footer_version" target="_blank" rel="noopener">Nebula</a> <strong class="nebula"><a href="https://github.com/chrisblakley/Nebula/compare/main@{' . date('Y-m-d', $this->version('utc')) . '}...main" target="_blank">' . $this->version('version') . '</a></strong>' . $child . '</span>';
			}

			return $wordpress_version_output . $nebula_version_output;
		}

		public function post_meta_boxes_setup(){
			add_action('add_meta_boxes', array($this, 'nebula_add_post_metabox'));
			add_action('add_meta_boxes', array($this, 'nebula_add_cf7_metabox'));
			add_action('save_post', array($this, 'save_post_custom_meta'), 10, 2); //Use this to save all custom meta (internal search, classes, CF7 submission notes, etc.)
		}

		//Internal Search Keywords post metabox and Custom Field
		public function nebula_add_post_metabox(){
			if ( $this->is_minimal_mode() ){return null;}

			$builtin_types = array('post', 'page', 'attachment');
			$custom_types = get_post_types(array('_builtin' => false));
			$avoid_types = array('acf', 'acf-field-group', 'wpcf7_contact_form', 'nebula_cf7_submits');

			foreach ( $builtin_types as $builtin_type ){
				add_meta_box('nebula-post-data', 'Nebula Post Data', array($this, 'nebula_post_metabox' ), $builtin_type, 'side', 'default');
			}

			foreach( $custom_types as $custom_type ){
				if ( !in_array($custom_type, $avoid_types) ){
					add_meta_box('nebula-post-data', 'Nebula Post Data', array($this, 'nebula_post_metabox' ), $custom_type, 'side', 'default');
				}
			}
		}

		//Internal Search Keywords post metabox content
		function nebula_post_metabox($object, $box){
			wp_nonce_field(basename(__FILE__), 'nebula_post_nonce');
			?>
			<div>
				<p>
					<strong>Body Classes</strong>
					<input type="text" id="nebula-body-classes" class="large-text" name="nebula_body_classes" value="<?php echo get_post_meta($object->ID, 'nebula_body_classes', true); ?>" />
					<span class="howto">Additional classes to appear on the body tag of this post.</span>
				</p>

				<p>
					<strong>Post Classes</strong>
					<input type="text" id="nebula-post-classes" class="large-text" name="nebula_post_classes" value="<?php echo get_post_meta($object->ID, 'nebula_post_classes', true); ?>" />
					<span class="howto">Additional classes to appear on the post tag.</span>
				</p>

				<p>
					<strong>Internal Search Keywords</strong>
					<textarea id="nebula-internal-search-keywords" class="textarea large-text" name="nebula_internal_search_keywords" placeholder="Additional keywords to help find this page..." style="min-height: 100px;"><?php echo get_post_meta($object->ID, 'nebula_internal_search_keywords', true); ?></textarea>
					<span class="howto">Use plurals since parts of words will return in search results (unless plural has a different spelling than singular; then add both).</span>
				</p>
			</div>
			<?php
		}

		public function save_post_custom_meta($post_id, $post){
			if ( $this->is_minimal_mode() ){return null;}

			if ( !isset($this->super->post['nebula_post_nonce']) || !wp_verify_nonce($this->super->post['nebula_post_nonce'], basename(__FILE__)) ){
				return $post_id;
			}

			$post_type = get_post_type_object($post->post_type); //Get the post type object.
			if ( !current_user_can($post_type->cap->edit_post, $post_id) ){ //Check if the current user has permission to edit the post.
				return $post_id;
			}

			$nebula_post_meta_fields = array('nebula_body_classes', 'nebula_post_classes', 'nebula_internal_search_keywords', 'nebula_cf7_submission_preserve', 'nebula_cf7_submission_notes');
			foreach ( $nebula_post_meta_fields as $nebula_post_meta_field ){
				$meta_value = get_post_meta($post_id, $nebula_post_meta_field, true); //Get the meta value of the custom field key.

				if ( !empty($this->super->post[$nebula_post_meta_field]) ){
					$new_meta_value = sanitize_text_field($this->super->post[$nebula_post_meta_field]); //Get the posted data and sanitize it if needed.

					if ( $new_meta_value && empty($meta_value) ){ //If a new meta value was added and there was no previous value, add it.
						add_post_meta($post_id, $nebula_post_meta_field, $new_meta_value, true);
					} elseif ( $new_meta_value && $meta_value !== $new_meta_value ){ //If the new meta value does not match the old value, update it.
						update_post_meta($post_id, $nebula_post_meta_field, $new_meta_value);
					}
				} else {
					delete_post_meta($post_id, $nebula_post_meta_field, $meta_value);
				}
			}
		}

		//Nebula CF7 Submissions Metabox
		public function nebula_add_cf7_metabox(){
			if ( $this->is_minimal_mode() ){return null;}
			add_meta_box('nebula-post-data', 'Nebula CF7', array($this, 'nebula_cf7_metabox' ), 'nebula_cf7_submits', 'side', 'default');
		}

		//Nebula CF7 Submission custom fields
		public function nebula_cf7_metabox($object, $box){
			wp_nonce_field(basename(__FILE__), 'nebula_post_nonce');

			if ( $object->post_type == 'nebula_cf7_submits' ){
				if ( $object->post_status == 'spam' || $object->post_status == 'invalid' ){
					?>
					<label class="mt-2">
						<input type="checkbox" id="nebula-cf7-submission-preserve" name="nebula_cf7_submission_preserve" <?php checked(get_post_meta($object->ID, 'nebula_cf7_submission_preserve', true), 'on'); ?> /> Preserve
					</label>
					<span class="howto">This prevents this submission from being automatically deleted.</span>
					<?php
				}
			}

			?>
			<div>
				<p>
					<strong><i class="fa-solid fa-fw fa-pen-to-square"></i> CF7 Submission Notes</strong>
					<textarea id="nebula-cf7-submission-notes" class="textarea large-text" name="nebula_cf7_submission_notes" placeholder="Notes related to this Contact Form 7 submission..." style="min-height: 250px;"><?php echo get_post_meta($object->ID, 'nebula_cf7_submission_notes', true); ?></textarea>
					<span class="howto">Keep any notes here to help provide context and information related to this form submission.</span>
				</p>
			</div>
			<?php
		}

		//Extend the WP admin posts search to include custom fields
		public function search_custom_post_meta_join($join){
			if ( $this->is_minimal_mode() ){return $join;}
			global $pagenow, $wpdb;

			//Perform the filter when searching on the edit page (post listings)
			if ( is_admin() && $pagenow === 'edit.php' && !empty($this->super->get['s']) ){
				$join .= 'LEFT JOIN ' . $wpdb->postmeta . ' ON ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
			}

			return $join;
		}

		public function search_custom_post_meta_where($where){
			if ( $this->is_minimal_mode() ){return $where;}
			global $pagenow, $wpdb;

			//Perform the filter when searching on the edit page (post listings)
			if ( is_admin() && $pagenow === 'edit.php' && !empty($this->super->get['s']) ){
				$where = preg_replace(
					"/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
					"(" . $wpdb->posts . ".post_title LIKE $1) OR (" . $wpdb->postmeta . ".meta_value LIKE $1)",
					$where
				);

				$where.= " GROUP BY {$wpdb->posts}.id"; //Limit to unique results (avoid duplicates)
			}

			return $where;
		}

		//Limit to unique results (this may be redundant)
		public function search_custom_post_meta_distinct($where){
			if ( $this->is_minimal_mode() ){return $where;}
			global $pagenow;

			//Perform the filter when searching on the edit page (post listings)
			if ( is_admin() && $pagenow === 'edit.php' && !empty($this->super->get['s']) ){
				return "DISTINCT";
			}

			return $where;
		}

		public function site_health_info($debug_info){
			if ( $this->is_minimal_mode() ){return $debug_info;}
			$fields = array();

			/*
				Basically the same things that are in the metaboxes options file... I don't love that this is redundant... Maybe the "Diagnostic" nebula options just links to the site health page with Nebula expanded? If so, logs metabox would need a new home- maybe administrative?
				Maybe this just gets limited to basic stuff and the diagnostics can stay in nebula options... could even provide a link to detailed diagnostics from here... some of these are simple enough that maybe duplicating isnt even that big of a deal, though.
				The *RIGHT* way to do this would be to have 1 function that preps all of the data for all 3 of these "output locations" and then these functions just call that to build the array.
				- last nebula update
				- core wp update notifications
				- core wp admin notifications
				- initial nebula version at install
				- critical css
				- nebula logs
				- cf7 form capture? maybe only if spam capture is enabled?
				- admin bar
				- jquery override or no
				- google analytics consent
				- cookie notification text
				- dequeuing styles/scripts?
			*/

			$fields['is_multisite'] = array(
				'label' => 'Multisite?',
				'value' => ( is_multisite() )? 'Yes' : 'No',
			);

			global $wp_version;
			$fields['wordpress_version'] = array(
				'label' => 'WordPress Version',
				'value' => $wp_version,
			);

			$fields['nebula_version'] = array(
				'label' => 'Nebula Version',
				'value' => $this->version('full') . ' (Committed on ' . $this->version('date') . ')',
			);

			// $nebula_initialized = $this->get_option('initialized');
			// $fields['initialization_date'] = array(
			// 	'label' => 'Nebula Initialization Date',
			// 	'value' => date('F j, Y', $nebula_initialized) . ' at ' . date('g:ia', $nebula_initialized) . ' (' . human_time_diff($nebula_initialized) . ' ago)',
			// );

			$fields['sass'] = array(
				'label' => 'Sass Enabled?',
				'value' => ( $this->get_option('scss') )? 'Yes' : 'No',
			);

			// $nebula_last_sass = $this->get_option('scss_last_processed');
			// $fields['last_sass'] = array(
			// 	'label' => 'Sass Last Processed',
			// 	'value' => date('F j, Y', $nebula_last_sass) . ' at ' . date('g:ia', $nebula_last_sass) . ' (' . human_time_diff($nebula_last_sass) . ' ago)',
			// );

			$fields['ga_measurement_id'] = array(
				'label' => 'GA Measurement ID',
				'value' => $this->get_option('ga_measurement_id'),
			);

			$fields['gtm_id'] = array(
				'label' => 'GTM ID',
				'value' => $this->get_option('gtm_id'),
			);

			$fields['service_worker'] = array(
				'label' => 'Service Worker',
				'value' => ( $this->get_option('service_worker') )? 'Yes' : 'No',
			);

			//Add more here...

			//Now add the fields to the main parent item
			$debug_info['nebula'] = array(
				'label' => 'Nebula',
				'description' => 'Various Nebula options, functionalities, and data that can be a helpful reference. See more in the <a href="index.php">Developer Information metabox (WP Dashboard)</a> and <a href="themes.php?page=nebula_options&tab=diagnostic">Nebula Diagnostics (Nebula Options)</a>',
				'fields' => $fields,
			);

			return $debug_info;
		}

		//Compare hashes from the current Nebula parent theme to when it was built and committed to detect manual file changes
		public function check_parent_theme_file_changes(){
			if ( $this->is_minimal_mode() ){return null;}

			//Only run for administrators in WP Admin
			if ( is_admin() || current_user_can('update_themes') ){
				global $pagenow;

				$last_checked = get_transient('nebula_theme_file_changes_check');

				if ( $last_checked === false || $pagenow === 'update-core.php' || $pagenow === 'themes.php' ){ //If it has not been recently checked, or if viewing the WP Updates page or the themes page
					$hash_file = get_template_directory() . '/inc/data/hashes.json';

					//Make sure the hashes.json file exists
					if ( !file_exists($hash_file) ){
						return;
					}

					$stored_hashes = json_decode(file_get_contents($hash_file), true); //Get saved hashes
					$current_hashes = $this->generate_hashes(get_template_directory()); //Generate current hashes

					$modified_files = [];
					foreach ( $current_hashes as $file => $hash ){
						if ( !isset($stored_hashes[$file]) || $stored_hashes[$file] !== $hash ){
							$modified_files[] = $file;
						}
					}

					//Store a list of modified files in a transient
					$transient_duration = 6*HOUR_IN_SECONDS;

					if ( !empty($modified_files) ){
						set_transient('nebula_theme_modified_files', $modified_files, $transient_duration);
					} else {
						delete_transient('nebula_theme_modified_files'); //No files were modified, so delete the transient
					}

					set_transient('nebula_theme_file_changes_check', time(), $transient_duration);
				}
			}
		}

		//Scan through the theme directories to generate hashes to represent each file for comparisons
		public function generate_hashes($directory, &$results=[]){
			if ( $this->is_minimal_mode() ){return null;}

			$files = scandir($directory);

			foreach ( $files as $this_file ){
				//Skip certain files
				$skip_filenames = array('.', '..', 'hashes.json', 'manifest.json', '.gitignore', 'error_log', 'debug.log');
				if ( in_array(strtolower($this_file), array_map('strtolower', $skip_filenames), true) ){
					continue; //Skip unnecessary files
				}

				//Skip files with certain extensions
				//Note: We have to ignore .css files because they contain a timestamp of the Sass last processing time. The corresponding .scss files are still tracked. Considering removing the timestamp from the CSS files to avoid this problem...
				$skip_file_extensions = array('css', 'log', 'png', 'jpg', 'jpeg', 'webp', 'gif', 'ico', 'woff', 'woff2', 'ttf', 'md', 'mo', 'po');
				$this_file_extension = pathinfo($this_file, PATHINFO_EXTENSION);
				if ( in_array(strtolower($this_file_extension), array_map('strtolower', $skip_file_extensions), true) ){
					continue; //Skip this file
				}

				$full_path = $directory . DIRECTORY_SEPARATOR . $this_file;

				if ( is_dir($full_path) ){
					//Skip entire directories
					$skip_directories = array('.github', 'data', 'img', 'webfonts', 'Nebula-Child');
					if ( in_array(strtolower($this_file), array_map('strtolower', $skip_directories), true) ){
						continue; //Skip this directory
					}

					$this->generate_hashes($full_path, $results);
				} else {
					$theme_directory = realpath(get_template_directory()); //Get absolute path of the theme
					$full_path = realpath($full_path); //Ensure absolute path
					$relative_path = ltrim(str_replace($theme_directory, '', $full_path), DIRECTORY_SEPARATOR); //Ensure no leading slash

					$results[$relative_path] = hash_file('sha256', $full_path);
				}
			}

			return $results;
		}
	}
}