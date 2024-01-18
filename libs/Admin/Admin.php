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
				add_action('save_post', array($this, 'clear_transients')); //When a post is saved (or when *starting* a new post)
				add_action('profile_update', array($this, 'clear_transients'));
				add_action('upgrader_process_complete', array($this, 'theme_update_automation'), 10, 2); //Action 'upgrader_post_install' also exists.
				add_filter('auth_cookie_expiration', array($this, 'session_expire')); //This is the user auto-signout session length
				add_action('after_setup_theme', array($this, 'custom_media_display_settings'));

				add_filter('upload_mimes', array($this, 'additional_upload_mime_types'));
				add_filter('wp_check_filetype_and_ext', array($this, 'allow_svg_uploads'), 10, 4);

				add_action('_core_updated_successfully', array($this, 'log_core_wp_updates'), 10, 2); //This happens after successful WP core update

				if ( current_user_can('publish_posts') ){
					add_action('admin_action_duplicate_post_as_draft', array($this, 'duplicate_post_as_draft'));
					add_filter('post_row_actions', array($this, 'duplicate_post_link'), 10, 2);
					add_filter('page_row_actions', array($this, 'duplicate_post_link'), 10, 2);
				}

				add_action('admin_init', array($this, 'clear_all_w3_caches'));

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
						if ( strpos(get_current_screen()->id, 'edit') !== false ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
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
					add_filter('manage_posts_columns', array($this, 'cf7_submissions_columns_head'));
					add_filter('manage_edit-nebula_cf7_submits_sortable_columns', array($this, 'cf7_submissions_columns_sortable'));
					add_action('manage_posts_custom_column', array($this, 'cf7_submissions_columns_content' ), 15, 2);
					add_action('pre_get_posts', array($this, 'cf7_submissions_columns_orderby')); //Handles the order when a user column is sorted
					add_filter('display_post_states', array($this, 'cf7_submissions_remove_post_state'), 10, 2); //Hide the private post state text on listing pages
					add_filter('post_row_actions', array($this, 'cf7_submissions_remove_quick_actions'), 10, 2);
					add_action('edit_form_top', array($this, 'cf7_submissions_back_button'));
					add_action('edit_form_after_title', array($this, 'cf7_storage_output'), 10, 1);
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
				add_action('wp_before_admin_bar_render', array($this, 'remove_admin_bar_logo'), 0);
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

		//Force expire query transients when posts/pages are saved.
		public function clear_transients(){
			$this->timer('Clear Transients');

			if ( class_exists('AM_Transients_Manager') ){
				$transient_manager = new AM_Transients_Manager(); //"PW_" changed to "AM_" in December 2021
				$transient_manager->delete_transients_with_expirations();
			} else {
				//Clear post/page information and related transients
				$all_transients_to_delete = apply_filters('nebula_delete_transients_on_save', array( //Allow other functions to hook in to delete transients on post save
					'nebula_autocomplete_menus', //Autocomplete Search
					'nebula_autocomplete_categories', //Autocomplete Search
					'nebula_autocomplete_tags', //Autocomplete Search
					'nebula_autocomplete_authors', //Autocomplete Search
					'nebula_latest_post', //Latest update
					'nebula_all_log_files', //Log file scan
				));

				foreach ( $all_transients_to_delete as $transient_to_delete ){
					delete_transient($transient_to_delete);
				}
			}

			$this->timer('Clear Transients', 'end');
		}

		//Pull favicon from the theme folder (Front-end calls are in includes/metagraphics.php).
		public function admin_favicon(){
			$cache_buster = ( $this->is_debug() )? '?r' . random_int(100000, 999999) : '';
			echo '<link rel="shortcut icon" href="' . get_theme_file_uri('/assets/img/meta/favicon.ico') . $cache_buster . '" />';
		}

		//Add classes to the admin body
		public function admin_body_classes($classes){
			$classes .= ' nebula ';

			global $current_user;
			$user_roles = $current_user->roles;
			$classes .= array_shift($user_roles);

			return $classes;
		}

		//Add the Brand color scheme to the admin User options
		public function additional_admin_color_schemes(){
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
			$third_party_resources = wp_cache_get('nebula_third_party_resources');
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

			wp_cache_set('nebula_third_party_resources', $third_party_resources); //Store in object cache
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

		public function remove_admin_bar_logo() {
			if ( is_admin_bar_showing() ){
				global $wp_admin_bar;
				$wp_admin_bar->remove_menu('wp-logo');
				$wp_admin_bar->remove_menu('wpseo-menu'); //Remove Yoast SEO from admin bar
			}
		}

		//Create custom menus within the WordPress Admin Bar
		public function admin_bar_menus(WP_Admin_Bar $wp_admin_bar){
			if ( is_admin_bar_showing() ){
				$this->timer('Nebula Admin Bar Menus');

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
					$last_user = get_userdata(get_post_meta( $current_id, '_edit_last', true));
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
					$post_type_object = get_post_type_object(get_post_type());
					if ( !empty($post_type_object) ){ //Ignore non-posts like user profiles
						$post_type_name = $post_type_object->labels->singular_name;

						$current_id = get_the_id();
						if ( is_home() ){
							$current_id = get_option('page_for_posts');
						} elseif ( is_archive() ){
							$term_object = get_queried_object();
							$current_id = $term_object->term_id;
							$post_type_name = $term_object->taxonomy;
							$original_date = false;
							$status = false;
						}

						$new_content_node->title = ucfirst($node_id) . ' ' . ucwords($post_type_name) . ' <span class="nebula-admin-light">(ID: ' . $current_id . ')' . $info_icon . '</span>';
						$wp_admin_bar->add_node($new_content_node);
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

				$nebula_warning_icon = '';
				$nebula_adminbar_icon = 'fa-star';

				if ( current_user_can('manage_options') ){
					$warnings = $this->check_warnings();

					//Remove "log" level warnings so the admni bar menu only turns red for warnings and errors
					if ( !empty($warnings) ){
						foreach ( $warnings as $key => $warning ){
							if ( $warning['level'] === 'log' ){
								unset($warnings[$key]);
							}
						}

						$nebula_adminbar_icon = 'fa-exclamation-triangle';
					}
				}

				$wp_admin_bar->add_node(array(
					'id' => 'nebula',
					'title' => '<i class="nebula-admin-fa fa-solid fa-fw ' . $nebula_adminbar_icon . '"></i> Nebula',
					'href' => 'https://nebula.gearside.com/?utm_campaign=documentation&utm_medium=nebula&utm_source=' . urlencode(get_bloginfo('name')) . '&utm_content=admin+bar',
					'meta' => array(
						'target' => '_blank',
						'rel' => 'noopener',
						'class' => ( !empty($warnings) )? 'has-warning' : '',
					)
				));

				//Version number and date
				$wp_admin_bar->add_node(array(
					'parent' => 'nebula',
					'id' => 'nebula-version',
					'title' => 'v<strong>' . $this->version('raw') . '</strong> <span class="nebula-admin-light">(' . $this->version('date') . ')</span>',
					'href' => 'https://github.com/chrisblakley/Nebula/compare/main@{' . date('Y-m-d', $this->version('utc')) . '}...main',
				));

				//If there are warnings display them
				if ( current_user_can('edit_others_posts') && !empty($warnings) ){
					$wp_admin_bar->add_node(array(
						'parent' => 'nebula',
						'id' => 'nebula-warnings',
						'title' => '<i class="nebula-admin-fa fa-solid fa-fw fa-exclamation-triangle"></i> Warnings',
					));

					foreach( $warnings as $key => $warning ){
						$warning_icon = 'fa-exclamation-triangle';

						if ( $warning['level'] === 'error' ){
							$warning_icon = 'fa-exclamation-triangle';
						} elseif ( $warning['level'] === 'warning' ){
							$warning_icon = 'fa-info-circle';
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

				if ( current_user_can('edit_others_posts') && !empty($nebula_warning_icon) ){
					if (!isset($nebula_warning_href)) {
						$nebula_warning_href = '';
					}
					if (!isset($nebula_warning_description)) {
						$nebula_warning_description = '';
					}
					$wp_admin_bar->add_node(array(
						'parent' => 'nebula',
						'id' => 'nebula-warning',
						'title' => '<i class="nebula-admin-fa fa-solid fa-fw fa-exclamation-triangle" style="color: #ca3838; margin-right: 5px;"></i> ' . $nebula_warning_description,
						'href' => admin_url($nebula_warning_href),
					));
				}

				//Documentation Links
				$wp_admin_bar->add_node(array(
					'parent' => 'nebula',
					'id' => 'nebula-documentation',
					'title' => '<i class="nebula-admin-fa fa-solid fa-fw fa-file-alt"></i> Nebula Documentation',
					'href' => 'https://nebula.gearside.com/?utm_campaign=documentation&utm_medium=nebula&utm_source=' . urlencode(get_bloginfo('name')) . '&utm_content=admin+bar',
					'meta' => array(
						'target' => '_blank',
						'rel' => 'noopener',
					)
				));

				$wp_admin_bar->add_node(array(
					'parent' => 'nebula-documentation',
					'id' => 'nebula-documentation-functions',
					'title' => '<i class="nebula-admin-fa fa-solid fa-fw fa-file-alt"></i> Functions & Variables',
					'href' => 'https://nebula.gearside.com/documentation/functions/?utm_campaign=documentation&utm_medium=nebula&utm_source=' . urlencode(get_bloginfo('name')) . '&utm_content=admin+bar',
					'meta' => array(
						'target' => '_blank',
						'rel' => 'noopener',
					)
				));

				$wp_admin_bar->add_node(array(
					'parent' => 'nebula-documentation',
					'id' => 'nebula-documentation-examples',
					'title' => '<i class="nebula-admin-fa fa-solid fa-fw fa-file-alt"></i> Examples & Tips',
					'href' => 'https://nebula.gearside.com/documentation/examples-tips/?utm_campaign=documentation&utm_medium=nebula&utm_source=' . urlencode(get_bloginfo('name')) . '&utm_content=admin+bar',
					'meta' => array(
						'target' => '_blank',
						'rel' => 'noopener',
					)
				));

				$wp_admin_bar->add_node(array(
					'parent' => 'nebula-documentation',
					'id' => 'nebula-documentation-faq',
					'title' => '<i class="nebula-admin-fa fa-solid fa-fw fa-question"></i> FAQs',
					'href' => 'https://nebula.gearside.com/faq/?utm_campaign=documentation&utm_medium=nebula&utm_source=' . urlencode(get_bloginfo('name')) . '&utm_content=admin+bar',
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
						'href' => 'https://nebula.gearside.com/documentation/options/?utm_campaign=documentation&utm_medium=nebula&utm_source=' . urlencode(get_bloginfo('name')) . '&utm_content=admin+bar+help',
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

				if ( current_user_can('edit_others_posts') ){
					$wp_admin_bar->add_node(array(
						'parent' => 'nebula-utilities',
						'id' => 'nebula-add-debug',
						'title' => '<i class="nebula-admin-fa fa-solid fa-fw fa-sync"></i> Reload &amp; Clear Caches',
						'href' => esc_url(add_query_arg('debug', 'true')),
						'meta' => array('title' => 'Append ?debug to force clear certain caches')
					));
				}

				$this->timer('Nebula Admin Bar Menus', 'end');
			}
		}

		//Colorize Nebula warning nodes in the admin bar
		public function admin_bar_warning_styles(){
			if ( is_admin_bar_showing() ){ ?>
				<style type="text/css">
					#wpadminbar .nebula-admin-fa {font-family: "Font Awesome 6 Solid", "Font Awesome 6 Free", "Font Awesome 6 Pro"; font-weight: 900;}
						#wpadminbar .nebula-admin-fa.fa-brands {font-family: "Font Awesome 6 Brands", "Font Awesome 6 Free", "Font Awesome 6 Pro"; font-weight: 400;}
					#wpadminbar .svg-inline--fa {color: #a0a5aa; color: rgba(240, 245, 250, 0.6); margin-right: 5px;}
					#wpadminbar .nebula-admin-light {font-size: 10px; color: #a0a5aa; color: rgba(240, 245, 250, 0.6); line-height: inherit;}

					#wpadminbar:not(.mobile) .ab-top-menu > #wp-admin-bar-nebula.has-warning > .ab-item {background: #ca3838;}
						#wpadminbar:not(.mobile) .ab-top-menu > #wp-admin-bar-nebula.has-warning.hover > .ab-item,
						#wpadminbar:not(.mobile) .ab-top-menu > #wp-admin-bar-nebula.has-warning:hover > .ab-item {background: maroon; color: #fff; transition: all 0.25s ease;}

					#wpadminbar:not(.mobile) #wp-admin-bar-nebula-warnings {background: #ca3838;}
						#wpadminbar:not(.mobile) #wp-admin-bar-nebula-warnings > .ab-item,
						#wpadminbar:not(.mobile) #wp-admin-bar-nebula-warnings > svg {color: #fff;}
						#wpadminbar:not(.mobile) #wp-admin-bar-nebula-warnings .level-error svg {color: #ca3838;}
						#wpadminbar:not(.mobile) #wp-admin-bar-nebula-warnings .level-warning svg {color: #f6b83f;}

					#wpadminbar:not(.mobile) .deregistered-asset-info {color: #f6b83f;}
					#wpadminbar:not(.mobile) #wp-admin-bar-nebula-deregisters > .ab-item {background: #f6b83f; color: #000; transition: all 0.25s ease;}
						#wpadminbar:not(.mobile) #wp-admin-bar-nebula-deregisters.hover > .ab-item,
						#wpadminbar:not(.mobile) #wp-admin-bar-nebula-deregisters:hover > .ab-item {background: #f5a326;}
				</style>
			<?php }
		}

		//Remove core WP admin bar head CSS and add our own
		public function remove_admin_bar_bump(){
			remove_action('wp_head', '_admin_bar_bump_cb');
		}

		//Override some styles and add custom functionality
		//Used on the front-end, but not in Admin area
		public function admin_bar_style_script_overrides(){
			if ( !$this->is_admin_page(true) && is_admin_bar_showing() ){ ?>
				<style type="text/css">
					html {margin-top: 32px !important; transition: margin-top 0.5s linear;}
					* html body {margin-top: 32px !important;}

					#wpadminbar {transition: top 0.5s linear;}
					.admin-bar-inactive #wpadminbar {top: -32px; overflow: hidden;}
					#wpadminbar i, #wpadminbar svg {-webkit-font-smoothing: antialiased;}
						#wpadminbar .ab-sub-wrapper .fa-fw {width: 1.25em;}
						#wpadminbar .svg-inline--fa {height: 1em;}

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
					wp_cache_set('nebula_force_theme_update_log', $log_force_theme_update); //Store boolean in object cache
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

		//Send an email to the current user and site admin that Nebula has been updated.
		public function theme_update_email($prev_version, $prev_version_commit_date, $new_version){
			if ( $prev_version !== $new_version ){
				$current_user = wp_get_current_user();

				$subject = 'Nebula updated to ' . $new_version . ' for ' . html_entity_decode(get_bloginfo('name')) . '.';
				$message = '<p>The parent Nebula theme has been updated from version <strong>' . $prev_version . '</strong> (Committed: ' . $prev_version_commit_date . ') to <strong>' . $new_version . '</strong> for ' . get_bloginfo('name') . ' (' . home_url('/') . ') by ' . $current_user->display_name . ' on ' . date('F j, Y') . ' at ' . date('g:ia') . '.<br/><br/>To revert, find the previous version in the <a href="https://github.com/chrisblakley/Nebula/commits/main" target="_blank" rel="noopener">Nebula GitHub repository</a>, download the corresponding .zip file, and upload it replacing /themes/Nebula-main/.</p>';

				return $this->send_email_to_admins($subject, $message);
			}

			return false;
		}

		//Send an email to the current user and site admin(s)
		public function send_email_to_admins($subject, $message, $attachments=false){
			$nebula_admin_email_sent = nebula()->transient('nebula_admin_email_sent', function($data){
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
			$developer_domains = explode(',', preg_replace('/\s+/', '', $this->get_option('dev_email_domain')));
			$administrators = get_users(array('role' => 'administrator'));
			foreach ( $administrators as $administrator ){
				foreach ( $developer_domains as $developer_domain ){
					if ( strpos($administrator->user_email, $developer_domain) !== false ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
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
				if ( is_string($value) && strpos($value, '@') !== false ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
					return true;
				}
			});
		}

		//Control session time (for the "Remember Me" checkbox)
		public function session_expire($expirein){
			return MONTH_IN_SECONDS; //Default is 1209600 (14 days)
		}

		//Send Google Analytics pageviews on the WP Admin and Login pages too
		public function admin_ga_pageview(){
			if ( empty($this->super->post['signed_request']) && $this->get_option('ga_measurement_id') ){
				?>
					<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_html(nebula()->get_option('ga_measurement_id')); ?>"></script>
					<script>
						window.dataLayer = window.dataLayer || [];
						function gtag(){dataLayer.push(arguments);}
						gtag('js', new Date());

						gtag('config', '<?php echo esc_html(nebula()->get_option('ga_measurement_id')); ?>', {
							send_page_view: true,
							<?php if ( nebula()->get_option('ga_wpuserid') && is_user_logged_in() ): ?>
								user_id: '<?php echo get_current_user_id(); //This property must be less than 256 characters ?>'
							<?php endif; ?>
						});
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
			$this->timer('Admin Notices');

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

			$this->timer('Admin Notices', 'end');
		}

		//Check the current (or passed) PHP version against the PHP support timeline.
		public function php_version_support($php_version=PHP_VERSION){
			$override = apply_filters('pre_nebula_php_version_support', null, $php_version);
			if ( isset($override) ){return;}

			$php_timeline = nebula()->transient('nebula_php_timeline', function(){
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

			return false;
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

		//Add columns to CF7 submission listings
		public function cf7_submissions_columns_head($columns){
			if ( $this->is_admin_page() && get_post_type() == 'nebula_cf7_submits' ){
				$columns['formatted_date'] = 'Formatted Date';
				$columns['form_name'] = 'Form Name';
				$columns['page_title'] = 'Page Title';

				if ( $this->get_option('attribution_tracking') ){
					$columns['attribution'] = 'Attribution';
				}

				$columns['notes'] = 'Internal Notes';

				unset($columns['date']); //Replacing the WP date column with our own
				unset($columns['id']); //This ID is confusing since it is the submission ID
			}

			return $columns;
		}
		public function cf7_submissions_columns_sortable($columns){
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
			if ( $this->is_admin_page() && get_post_type() == 'nebula_cf7_submits' ){
				$submission_data = get_post($submission_id); //Remember: this $submission_id is the submission ID (not the form ID)!
				$form_data = json_decode($submission_data->post_content);
				$form_id = ( is_object($form_data) )? $form_data->_wpcf7 : false; //CF7 Form ID
				$post_id = ( is_object($form_data) )? $form_data->_wpcf7_container_post : false; //The page the CF7 submission was from. @todo "Nebula" 0: Is this still working? Post Titles are all empty

				if ( $column_name === 'formatted_date' ){
					if ( !empty($form_data) ){
						echo $form_data->_nebula_date_formatted . '<br /><small>' . human_time_diff($form_data->_nebula_timestamp) . ' ago</small>';
					}
				}

				if ( $column_name === 'form_name' ){
					if ( !empty($form_id) ){
						echo '<strong><a href="admin.php?page=wpcf7&post=' . $form_id . '&action=edit">' . get_the_title($form_id) . ' &raquo;</a></strong><br /><small>Form ID: ' . $form_id . '</small>';
					} else {
						echo '<span class="cf7-submits-possible-spam">Spam?<br /><small>The message could not be decoded.</small></span>';
					}
				}

				if ( $column_name === 'page_title' ){
					if ( !empty($post_id) ){
						echo '<a href="' . get_permalink($post_id) . '" target="_blank">' . get_the_title($post_id) . ' &raquo;</a><br /><small>Post ID: ' . $post_id . '</small>';
					}
				}

				//Check for attribution data if it is being used
				if ( $this->get_option('attribution_tracking') && $column_name === 'attribution' ){
					if ( !empty(str_replace('{}', '', json_decode($submission_data->post_content)->_nebula_attribution)) ){ //If we have attribution data
						echo '<span>' . json_decode($submission_data->post_content)->_nebula_attribution . '</span>';
					} elseif ( !empty(json_decode($submission_data->post_content)->_nebula_utms) ){ //If we have UTM data
						echo '<span>' . json_decode($submission_data->post_content)->_nebula_utms . '</span>';
					}
				}

				if ( $column_name === 'notes' ){
					echo '<span>' . get_post_meta($submission_id, 'nebula_cf7_submission_notes', true) . '</span>';
				}

				echo '';
			}
		}
		public function cf7_submissions_columns_orderby($query){
			if ( $this->is_admin_page() && get_post_type() == 'nebula_cf7_submits' ){
				$orderby = $query->get('orderby');

				if ( is_string($orderby) ){
					$orderby = strtolower($orderby);

					if ( $orderby === 'form_name' ){
						$query->set('orderby', 'form_name');
					}

					if ( $orderby === 'page_title' ){
						$query->set('orderby', 'page_title');
					}

					if ( $orderby === 'notes' ){
						$query->set('orderby', 'notes');
					}
				}
			}
		}

		//Add dropdown menu(s) for filtering CF7 submission listings
		public function cf7_submissions_filters($post_type){
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
				}
			}

			return $query;
		}

		//Add buttons for additional actions with CF7 submissions
		public function cf7_submissions_actions($which){ //Which designates top or bottom
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

				echo '<a class="button" href="' . $export_url . '">' . $export_text . '</a>';
			}
		}

		//Hide the "Private" post state usually appended to titles on CF7 submissions admin listings
		public function cf7_submissions_remove_post_state($post_states){
			if ( $this->is_admin_page() && get_post_type() == 'nebula_cf7_submits' ){
				return false;
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
				$previous_url = ( !empty($this->super->server['HTTP_REFERER']) && strpos($this->super->server['HTTP_REFERER'], 'post_type=nebula_cf7_submits') > -1 && strpos($this->super->server['HTTP_REFERER'], 'cf7_form_id=') > -1 )? $this->super->server['HTTP_REFERER'] : 'edit.php?post_type=nebula_cf7_submits'; //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
				echo '<a class="button" href="' . $previous_url . '">&laquo; Back to CF7 Submissions</a>';
			}
		}

		//Output the details of each CF7 submission
		public function cf7_storage_output($post){
			if ( $this->is_admin_page() && $post->post_type === 'nebula_cf7_submits' ){
				$form_data = json_decode($post->post_content);

				if ( !empty($form_data) ){
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

					//Output each data type
					foreach ( $form_output as $data_type => $datapoints ){
						?>
						<table id="nebula-cf7-submission-<?php echo $data_type; ?>" class="nebula-cf7-submissions">
							<thead>
								<tr>
									<td><?php echo ( $data_type === 'metadata' )? 'Metadata' : 'Field'; ?></td>
									<td>Value</td>
								</tr>
							</thead>
							<tbody>
						<?php

						foreach ( $datapoints as $key => $value ){
							$classes = array();

							if ( is_array($value) ){
								$value = implode(', ', $value);
							}

							if ( $key === '_wpcf7' || $key === 'form_name' || $key === 'form_id' ){
								$value = '<a href="admin.php?page=wpcf7&post=' . $form_data->form_id . '&action=edit">' . $value . ' &raquo;</a>';
							}

							if ( $key === '_wpcf7_container_post' ){
								$value = '<a href="' . get_permalink($value) . '">' . $value . ' (' . get_the_title($value) . ') &raquo;</a>';
							}

							if ( $key === '_nebula_date_formatted' ){
								$value = $value . ' (' . human_time_diff($form_data->_nebula_timestamp) . ' ago)';
							}

							if ( $key === '_nebula_username' || $key === '_nebula_user_id' ){
								$value = '<a href="user-edit.php?user_id=' . $form_data->_nebula_user_id . '">' . $value . ' &raquo;</a>';
							}

							if ( $key === '_nebula_session_id' ){
								$value = str_replace(array(':', ';'), array(': ', '<br />'), $value);
							}

							if ( $key === '_nebula_ga_cid' ){
								$value = '<a href="' . $this->google_analytics_url() . '" target="_blank" rel="noopener noreferrer">' . $value . ' &raquo;</a>'; //If a user explorer is ever added to GA4, link directly to that report. Possibly even to this individual CID.
							}

							if ( $key === '_nebula_user_agent' ){
								$value = '<a href="https://developers.whatismybrowser.com/useragents/parse/" target="_blank" rel="noopener noreferrer">' . $value . ' &raquo;</a>';
							}

							if ( substr($key, 0, 1) === '_' || substr($key, 0, 3) === 'hp-' ){ //@todo "Nebula" 0: Use str_starts_with in PHP8
								$classes[] = 'wpcf7-metadata';
							}

							if ( empty($value) || $value == '[]' || $value == '{}' ){
								$classes[] = 'no-data';
							}

							//Convert objects to strings
							if ( is_object($value) ){
								$value = json_decode(wp_json_encode($value), true);
							}
							if ( is_array($value) ){
								$value = implode(',', $value);
							}

							echo '<tr class="' . implode(' ', $classes) . '"><td><strong>' . $key . '</strong></td>';
							echo '<td>' . $value . '</td></tr>';
						}

						echo '</tbody></table>';
					}
				} else {
					$formatted_invalid_json = str_replace(array('{', '":', ',"', '}'), array('{<br />', '": ', ',<br />"', '<br />}'), $post->post_content);
					echo '<table id="nebula-cf7-submission-spam" class="nebula-cf7-submissions"><thead><tr><td>Field</td><td>Value</td></tr></thead><tbody><tr><td><strong>Invalid JSON</strong><br />Possibly spam?<br /><br /><small>Note: the JSON here may validate when copy/pasted, but the original data could not be decoded.</small></td><td>' . $formatted_invalid_json . '</td></tr></tbody></table>';
				}
			}
		}

		//All Settings page link
		public function all_settings_link(){
			add_theme_page('All Settings', 'All Settings', 'administrator', 'options.php');
		}

		//Ensure Relevanssi indexes Nebula custom fields
		//Note: The custom field setting in Relevanssi Settings > Indexing (tab) must not be "none" for this to work (Nebula does check for this and warns as needed)
		public function add_nebula_to_relevanssi($custom_fields, $post_id){
			$custom_fields[] = 'nebula_internal_search_keywords'; //Add the Nebula internal search custom field to Relevanssi
			return $custom_fields;
		}

		//Clear caches when plugins are activated if W3 Total Cache is active
		public function clear_all_w3_caches(){
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
			if ( function_exists('w3tc_pgcache_flush') && isset($this->super->server['activate']) && $this->super->server['activate'] == 'true'){
				w3tc_pgcache_flush();
			}
		}

		//Admin footer left side
		public function change_admin_footer_left(){
			//return '<a href="https://www.google.com/maps/dir/Current+Location/760+West+Genesee+Street+Syracuse+NY+13204" target="_blank" rel="noopener">760 West Genesee Street, Syracuse, NY 13204</a> &bull; (315) 478-6700';
		}

		//Admin footer right side
		public function change_admin_footer_right(){
			global $wp_version;
			$child = ( is_child_theme() )? ' <small>(Child)</small>' : '';

			$wordpress_version_output = '';
			$nebula_version_output = 'Thank you for using Nebula!';
			if ( current_user_can('publish_posts') ){
				$wordpress_version_output = '<span><a href="https://codex.wordpress.org/WordPress_Versions" target="_blank" rel="noopener">WordPress</a> <strong>' . $wp_version . '</strong></span>, ';
				$nebula_version_output = '<span title="Committed: ' . $this->version('date') . '"><a href="https://nebula.gearside.com/?utm_campaign=documentation&utm_medium=nebula&utm_source=' . urlencode(get_bloginfo('name')) . '&utm_content=footer+version" target="_blank" rel="noopener">Nebula</a> <strong class="nebula"><a href="https://github.com/chrisblakley/Nebula/compare/main@{' . date('Y-m-d', $this->version('utc')) . '}...main" target="_blank">' . $this->version('version') . '</a></strong>' . $child . '</span>';
			}

			return $wordpress_version_output . $nebula_version_output;
		}

		public function post_meta_boxes_setup(){
			add_action('add_meta_boxes', array($this, 'nebula_add_post_metabox'));
			add_action('add_meta_boxes', array($this, 'nebula_add_cf7_notes_metabox'));
			add_action('save_post', array($this, 'save_post_custom_meta'), 10, 2); //Use this to save all custom meta (internal search, classes, CF7 submission notes, etc.)
		}

		//Internal Search Keywords post metabox and Custom Field
		public function nebula_add_post_metabox(){
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
			if ( !isset($this->super->post['nebula_post_nonce']) || !wp_verify_nonce($this->super->post['nebula_post_nonce'], basename(__FILE__)) ){
				return $post_id;
			}

			$post_type = get_post_type_object($post->post_type); //Get the post type object.
			if ( !current_user_can($post_type->cap->edit_post, $post_id) ){ //Check if the current user has permission to edit the post.
				return $post_id;
			}

			$nebula_post_meta_fields = array('nebula_body_classes', 'nebula_post_classes', 'nebula_internal_search_keywords', 'nebula_cf7_submission_notes');
			foreach ( $nebula_post_meta_fields as $nebula_post_meta_field ){
				if ( !empty($this->super->post[$nebula_post_meta_field]) ){
					$new_meta_value = sanitize_text_field($this->super->post[$nebula_post_meta_field]); //Get the posted data and sanitize it if needed.
					$meta_value = get_post_meta($post_id, $nebula_post_meta_field, true); //Get the meta value of the custom field key.
					if ( $new_meta_value && empty($meta_value) ){ //If a new meta value was added and there was no previous value, add it.
						add_post_meta($post_id, $nebula_post_meta_field, $new_meta_value, true);
					} elseif ( $new_meta_value && $meta_value !== $new_meta_value ){ //If the new meta value does not match the old value, update it.
						update_post_meta($post_id, $nebula_post_meta_field, $new_meta_value);
					} elseif ( $new_meta_value === '' && $meta_value ){ //If there is no new meta value but an old value exists, delete it.
						delete_post_meta($post_id, $nebula_post_meta_field, $meta_value);
					}
				}
			}
		}

		//Nebula CF7 Submission Notes
		public function nebula_add_cf7_notes_metabox(){
			add_meta_box('nebula-post-data', 'Nebula CF7 Notes', array($this, 'nebula_cf7_notes_metabox' ), 'nebula_cf7_submits', 'side', 'default');
		}

		//Internal Search Keywords post metabox content
		function nebula_cf7_notes_metabox($object, $box){
			wp_nonce_field(basename(__FILE__), 'nebula_post_nonce');
			?>
			<div>
				<p>
					<strong>CF7 Submission Notes</strong>
					<textarea id="nebula-cf7-submission-notes" class="textarea large-text" name="nebula_cf7_submission_notes" placeholder="Notes related to this Contact Form 7 submission..." style="min-height: 250px;"><?php echo get_post_meta($object->ID, 'nebula_cf7_submission_notes', true); ?></textarea>
					<span class="howto">Keep any notes here to help provide context and information related to this form submission.</span>
				</p>
			</div>
			<?php
		}

		//Extend the WP admin posts search to include custom fields
		public function search_custom_post_meta_join($join){
			global $pagenow, $wpdb;

			//Perform the filter when searching on the edit page (post listings)
			if ( is_admin() && $pagenow === 'edit.php' && !empty($this->super->get['s']) ){
				$join .= 'LEFT JOIN ' . $wpdb->postmeta . ' ON ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
			}

			return $join;
		}

		public function search_custom_post_meta_where($where){
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
			global $pagenow;

			//Perform the filter when searching on the edit page (post listings)
			if ( is_admin() && $pagenow === 'edit.php' && !empty($this->super->get['s']) ){
				return "DISTINCT";
			}

			return $where;
		}
	}
}