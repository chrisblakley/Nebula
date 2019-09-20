<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Admin') ){
	require_once get_template_directory() . '/libs/Admin/Automation.php';
	require_once get_template_directory() . '/libs/Admin/Dashboard.php';
	require_once get_template_directory() . '/libs/Admin/Users.php';

	trait Admin {
		use Automation { Automation::hooks as AutomationHooks;}
		use Dashboard { Dashboard::hooks as DashboardHooks;}
		use Users { Users::hooks as UsersHooks;}

		public function hooks(){
			global $pagenow;

			$this->AutomationHooks(); //Register Automation hooks
			$this->DashboardHooks(); //Register Dashboard hooks
			$this->UsersHooks(); //Register Users hooks

			//All admin pages (including AJAX requests)
			if ( $this->is_admin_page() ){
				add_filter('nebula_brain', array($this, 'admin_brain'));
				add_action('save_post', array($this, 'clear_transients'));
				add_action('profile_update', array($this, 'clear_transients'));
				add_action('upgrader_process_complete', array($this, 'theme_update_automation'), 10, 2); //Action 'upgrader_post_install' also exists.
				add_filter('auth_cookie_expiration', array($this, 'session_expire'));

				add_filter('wp_check_filetype_and_ext', array($this, 'allow_svg_uploads'), 10, 4);
				add_filter('upload_mimes', array($this, 'additional_upload_mime_types'));

				add_action('after_setup_theme', array($this, 'custom_media_display_settings'));
				add_action('admin_action_duplicate_post_as_draft', array($this, 'duplicate_post_as_draft'));
				add_filter('post_row_actions', array($this, 'rd_duplicate_post_link'), 10, 2);
				add_filter('page_row_actions', array($this, 'rd_duplicate_post_link'), 10, 2);
				add_action('admin_init', array($this, 'clear_all_w3_caches'));

				add_action('admin_init', array($this, 'theme_json'));
				add_filter('puc_request_update_result_theme-Nebula', array($this, 'theme_update_version_store'), 10, 2); //This hook is found in UpdateChecker.php in the filterUpdateResult() function.
				add_filter('site_transient_update_themes', array($this, 'force_nebula_theme_update'), 99, 1);
			}

			//Non-AJAX admin pages
			if ( $this->is_admin_page() && !$this->is_ajax_or_rest_request() ){
				add_action('admin_head', array($this, 'admin_favicon'));
				add_action('admin_head', array($this, 'admin_ga_pageview'));
				add_filter('admin_body_class', array($this, 'admin_body_classes'));

				remove_action('admin_enqueue_scripts', 'wp_auth_check_load'); //Disable the logged-in monitoring modal

				if ( $this->get_option('admin_notices') ){
					add_action('admin_notices', array($this, 'admin_notices'));
				}

				//add_filter('wp_unique_post_slug', array($this, 'unique_slug_warning_ajax' ), 10, 4); //@TODO "Nebula" 0: This echos when submitting posts from the front end! nebula()->is_admin_page() does not prevent that...

				//Add ID column to posts and pages
				add_filter('manage_posts_columns', array($this, 'id_column_head')); //Includes custom post types
				add_filter('manage_pages_columns', array($this, 'id_column_head'));

				//Loop through all post types to make ID column sortable
				add_action('init', function(){
					foreach ( get_post_types(array(), 'names') as $post_type ){
						add_filter('manage_edit-' . $post_type . '_sortable_columns', array($this, 'id_sortable_column'));
					}
				});

				//Output post IDs
				add_action('manage_posts_custom_column', array($this, 'id_column_content'), 10, 2);
				add_action('manage_pages_custom_column', array($this, 'id_column_content'), 10, 2);

				add_action('pre_get_posts', array($this, 'id_column_orderby')); //Handles the order when the ID column is sorted

				//Override some Yoast settings
				if ( is_plugin_active('wordpress-seo/wp-seo.php') ){
					if ( !$this->get_option('author_bios') ){
						add_action('admin_init', array($this, 'disable_yoast_author_indexing'));
					}

					//Remove most Yoast SEO columns
					$post_types = get_post_types(array('public' => true), 'names');
					if ( is_array($post_types) && $post_types !== array() ){
						foreach ( $post_types as $post_type ){
							add_filter('manage_edit-' . $post_type . '_columns', array($this, 'remove_yoast_columns')); //@TODO "Nebula" 0: This does not always work.
						}
					}
				}

				add_filter('manage_media_columns', array($this, 'muc_column'));
				add_action('manage_media_custom_column', array($this, 'muc_value'), 10, 2);

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
				add_filter('login_headertitle', array($this, 'new_wp_login_title'));
			}

			//Disable auto curly quotes (smart quotes)
			remove_filter('the_content', 'wptexturize');
			remove_filter('the_excerpt', 'wptexturize');
			remove_filter('comment_text', 'wptexturize');
			add_filter('run_wptexturize', '__return_false');

			//Disable Admin Bar (and WP Update Notifications) for everyone but administrators (or specific users)
			if ( !$this->get_option('admin_bar') && !$this->is_admin_page() ){ //If Admin Bar is disabled and viewing front-end (admin bar is always visible in Admin Dashboard)
				show_admin_bar(false);

				add_action('wp_print_scripts', array($this, 'dequeue_admin_bar'), 9999);
				add_action('wp_print_styles', array($this, 'dequeue_admin_bar'), 9999);
				add_action('init', array($this, 'admin_only_features')); //TODO "Nebula" 0: Possible to remove and add directly remove action here
				add_filter('wp_head', array($this, 'remove_admin_bar_style_frontend'), 99);
			} else { //Else the admin bar is enabled
				add_action('wp_before_admin_bar_render', array($this, 'remove_admin_bar_logo'), 0);
				add_action('admin_bar_menu', array($this, 'admin_bar_menus'), 800); //Add Nebula menus to Admin Bar
				add_action('get_header', array($this, 'remove_admin_bar_bump')); //TODO "Nebula" 0: Possible to remove and add directly remove action here
				add_action('wp_after_admin_bar_render', array($this, 'admin_bar_style_script_overrides'), 11);
				add_action('wp_head', array($this, 'admin_bar_warning_styles'), 11);
				add_action('admin_print_styles', array($this, 'admin_bar_warning_styles'), 11);
			}

			//Disable Wordpress Core update notifications in WP Admin
			if ( !$this->get_option('wp_core_updates_notify') ){
				add_filter('pre_site_transient_update_core', '__return_null');
			}

			//Show update warning on Wordpress Core/Plugin update admin pages
			if ( $this->get_option('plugin_update_warning') ){
				if ( $pagenow === 'plugins.php' || $pagenow === 'update-core.php' ){
					add_action('admin_notices', array($this, 'update_warning'));
				}
			}
		}

		//Add info to the brain variable for admin pages
		public function admin_brain($brain){
			$brain['site']['admin_url'] = get_admin_url();
			return $brain;
		}

		//Force expire query transients when posts/pages are saved.
		public function clear_transients(){
			if ( is_plugin_active('transients-manager/transients-manager.php') ){
				$transient_manager = new PW_Transients_Manager();
				$transient_manager->delete_transients_with_expirations();
			} else {
				$nebula_transients_to_delete = array(
					'nebula_autocomplete_menus', //Autocomplete Search
					'nebula_autocomplete_categories', //Autocomplete Search
					'nebula_autocomplete_tags', //Autocomplete Search
					'nebula_autocomplete_authors', //Autocomplete Search
					'nebula_everything_query', //Advanced Search
					'nebula_latest_post', //Latest update
				);

				$all_transients_to_delete = apply_filters('nebula_delete_transients_on_save', $nebula_transients_to_delete); //Allow other functions to hook in to delete transients on post save
				foreach ( $all_transients_to_delete as $transient_to_delete ){
					delete_transient($transient_to_delete);
				}
			}
		}

		//Pull favicon from the theme folder (Front-end calls are in includes/metagraphics.php).
		public function admin_favicon(){
			$cache_buster = ( $this->is_debug() )? '?r' . mt_rand(1000, mt_getrandmax()) : '';
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

		//Disable Admin Bar (and WP Update Notifications) for everyone but administrators (or specific users)
		public function dequeue_admin_bar(){
			wp_deregister_style('admin-bar');
			wp_dequeue_script('admin-bar');
		}

		public function admin_only_features(){
			remove_action('wp_footer', 'wp_admin_bar_render', 1000); //For the front-end
		}

		//Aggregate all third-party tools into a single array
		public function third_party_tools(){
			$third_party_tools = array(
				'administrative' => array(),
				'social' => array()
			);

			//Administrative
			if ( $this->get_option('hosting_url') ){
				$third_party_tools['administrative'][] = array(
					'name' => 'Hosting',
					'icon' => '<i class="nebula-admin-fa far fa-fw fa-hdd"></i>',
					'url' => $this->get_option('hosting_url')
				);
			}

			if ( $this->get_option('cpanel_url') ){
				$third_party_tools['administrative'][] = array(
					'name' => 'Server Control Panel',
					'icon' => '<i class="nebula-admin-fa fas fa-fw fa-cogs"></i>',
					'url' => $this->get_option('cpanel_url')
				);
			}

			if ( $this->get_option('registrar_url') ){
				$third_party_tools['administrative'][] = array(
					'name' => 'Domain Registrar',
					'icon' => '<i class="nebula-admin-fa fas fa-fw fa-globe"></i>',
					'url' => $this->get_option('registrar_url')
				);
			}

			if ( $this->get_option('github_url') ){
				$third_party_tools['administrative'][] = array(
					'name' => 'Github Repository',
					'icon' => '<i class="nebula-admin-fa fab fa-fw fa-github"></i>',
					'url' => $this->get_option('github_url')
				);
			}

			if ( $this->get_option('ga_tracking_id') ){
				$third_party_tools['administrative'][] = array(
					'name' => 'Google Analytics',
					'icon' => '<i class="nebula-admin-fa fas fa-fw fa-chart-area"></i>',
					'url' => 'https://analytics.google.com/analytics/web/'
				);
			}

			if ( $this->get_option('gtm_id') ){
				$third_party_tools['administrative'][] = array(
					'name' => 'Google Tag Manager',
					'icon' => '<i class="nebula-admin-fa fab fa-fw fa-google"></i>',
					'url' => 'https://tagmanager.google.com'
				);
			}

			if ( $this->get_option('google_optimize_id') ){
				$third_party_tools['administrative'][] = array(
					'name' => 'Google Optimize',
					'icon' => '<i class="nebula-admin-fa fas fa-fw fa-chart-pie"></i>',
					'url' => 'https://optimize.google.com/optimize/home'
				);
			}

			$third_party_tools['administrative'][] = array(
				'name' => 'Google Search Console',
				'icon' => '<i class="nebula-admin-fa fab fa-fw fa-google"></i>',
				'url' => 'https://www.google.com/webmasters/tools/home'
			);

			$third_party_tools['administrative'][] = array(
				'name' => 'Bing Webmaster Tools',
				'icon' => '<i class="nebula-admin-fa fab fa-fw fa-microsoft"></i>',
				'url' => 'https://www.bing.com/toolbox/webmaster'
			);

			if ( is_plugin_active('wordpress-seo/wp-seo.php') ){ //If Yoast SEO is active link to its sitemap
				$third_party_tools['administrative'][] = array(
					'name' => 'Yoast SEO Sitemap XML',
					'icon' => '<i class="nebula-admin-fa fas fa-fw fa-sitemap"></i>',
					'url' => home_url('/') . 'sitemap_index.xml'
				);
			}

			if ( $this->get_option('adwords_remarketing_conversion_id') ){
				$third_party_tools['administrative'][] = array(
					'name' => 'Google AdWords',
					'icon' => '<i class="nebula-admin-fa fas fa-fw fa-search-plus"></i>',
					'url' => 'https://adwords.google.com/home/'
				);
			}

			if ( $this->get_option('facebook_custom_audience_pixel_id') ){
				$third_party_tools['administrative'][] = array(
					'name' => 'Facebook Ads Manager',
					'icon' => '<i class="nebula-admin-fa fab fa-fw fa-facebook-official"></i>',
					'url' => 'https://www.facebook.com/ads/manager/account/campaigns'
				);
			}

			if ( $this->get_option('google_adsense_url') ){
				$third_party_tools['administrative'][] = array(
					'name' => 'Google AdSense',
					'icon' => '<i class="nebula-admin-fa fas fa-fw fa-money"></i>',
					'url' => 'https://www.google.com/adsense'
				);
			}

			if ( $this->get_option('amazon_associates_url') ){
				$third_party_tools['administrative'][] = array(
					'name' => 'Amazon Associates',
					'icon' => '<i class="nebula-admin-fa fab fa-fw fa-amazon"></i>',
					'url' => 'https://affiliate-program.amazon.com/home'
				);
			}

			$third_party_tools['administrative'][] = array(
				'name' => 'Google My Business',
				'icon' => '<i class="nebula-admin-fa far fa-fw fa-building"></i>',
				'url' => 'https://www.google.com/business/'
			);

			if ( $this->is_ecommerce() ){
				$third_party_tools['administrative'][] = array(
					'name' => 'Google Merchant Center',
					'icon' => '<i class="nebula-admin-fa fas fa-fw fa-store"></i>',
					'url' => 'https://www.google.com/retail/solutions/merchant-center/'
				);
			}

			if ( $this->get_option('facebook_app_id') ){
				$third_party_tools['administrative'][] = array(
					'name' => 'Facebook For Developers',
					'icon' => '<i class="nebula-admin-fa fab fa-fw fa-facebook"></i>',
					'url' => 'https://developers.facebook.com/'
				);
			}

			if ( $this->get_option('google_server_api_key') || $this->get_option('google_browser_api_key') ){
				$third_party_tools['administrative'][] = array(
					'name' => 'Google APIs',
					'icon' => '<i class="nebula-admin-fa fas fa-fw fa-code"></i>',
					'url' => 'https://console.developers.google.com/iam-admin/projects'
				);
			}

			if ( $this->get_option('cse_id') ){
				$third_party_tools['administrative'][] = array(
					'name' => 'Google Custom Search',
					'icon' => '<i class="nebula-admin-fa fas fa-fw fa-search"></i>',
					'url' => 'https://cse.google.com/cse/all'
				);
			}

			if ( $this->get_option('hubspot_api') || $this->get_option('hubspot_portal') ){
				$third_party_tools['administrative'][] = array(
					'name' => 'Hubspot',
					'icon' => '<i class="nebula-admin-fa fab fa-fw fa-hubspot"></i>',
					'url' => 'https://app.hubspot.com/reports-dashboard/' . $this->get_option('hubspot_portal')
				);
			}

			if ( $this->get_option('mention_url') ){
				$third_party_tools['administrative'][] = array(
					'name' => 'Mention',
					'icon' => '<i class="nebula-admin-fa fas fa-fw fa-star"></i>',
					'url' => 'https://web.mention.com'
				);
			}

			//Social
			if ( $this->get_option('facebook_url') ){
				$third_party_tools['social'][] = array(
					'name' => 'Facebook',
					'icon' => '<i class="nebula-admin-fa fab fa-fw fa-facebook-square"></i>',
					'url' => $this->get_option('facebook_url')
				);
			}

			if ( $this->get_option('twitter_username') ){
				$third_party_tools['social'][] = array(
					'name' => 'Twitter',
					'icon' => '<i class="nebula-admin-fa fab fa-fw fa-twitter-square"></i>',
					'url' => $this->twitter_url()
				);
			}

			if ( $this->get_option('linkedin_url') ){
				$third_party_tools['social'][] = array(
					'name' => 'LinkedIn',
					'icon' => '<i class="nebula-admin-fa fab fa-fw fa-linkedin"></i>',
					'url' => $this->get_option('linkedin_url')
				);
			}

			if ( $this->get_option('youtube_url') ){
				$third_party_tools['social'][] = array(
					'name' => 'Youtube',
					'icon' => '<i class="nebula-admin-fa fab fa-fw fa-youtube"></i>',
					'url' => $this->get_option('youtube_url')
				);
			}

			if ( $this->get_option('instagram_url') ){
				$third_party_tools['social'][] = array(
					'name' => 'Instagram',
					'icon' => '<i class="nebula-admin-fa fab fa-fw fa-instagram"></i>',
					'url' => $this->get_option('instagram_url')
				);
			}

			if ( $this->get_option('disqus_shortname') ){
				$third_party_tools['social'][] = array(
					'name' => 'Disqus',
					'icon' => '<i class="nebula-admin-fa far fa-fw fa-comments"></i>',
					'url' => 'https://' . $this->get_option('disqus_shortname') . '.disqus.com/admin/moderate/'
				);
			}

			return $third_party_tools;
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
				/** @var WP_Admin_Bar $wp_admin_bar */
				$wp_admin_bar->remove_menu('wp-logo');
				$wp_admin_bar->remove_menu('wpseo-menu'); //Remove Yoast SEO from admin bar
			}
		}

		//Create custom menus within the WordPress Admin Bar
		public function admin_bar_menus(WP_Admin_Bar $wp_admin_bar){
			if ( is_admin_bar_showing() ){
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
					$post_type_object = get_post_type_object(get_post_type());
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

					$new_content_node->title = ucfirst($node_id) . ' ' . ucwords($post_type_name) . ' <span class="nebula-admin-light">(ID: ' . $current_id . ')</span>';
					$wp_admin_bar->add_node($new_content_node);
				}

				//Add created date under View/Edit node
				//@TODO "Nebula" 0: get_the_author() is not working when in Admin
				if ( !empty($original_date) ){
					$wp_admin_bar->add_node(array(
						'parent' => $node_id,
						'id' => 'nebula-created',
						'title' => '<i class="nebula-admin-fa far fa-fw fa-calendar"></i> <span title="' . human_time_diff($original_date) . ' ago">Created: ' . date_i18n('F j, Y', $original_date) . '</span> <span class="nebula-admin-light">(' . $original_author . ')</span>',
						'href' => get_edit_post_link(),
						'meta' => array('target' => '_blank', 'rel' => 'noopener')
					));
				}

				//Add modified date under View/Edit node
				if ( get_post_meta($current_id, '_edit_last', true) ){ //If the post has been modified
					$wp_admin_bar->add_node(array(
						'parent' => $node_id,
						'id' => 'nebula-modified',
						'title' => '<i class="nebula-admin-fa far fa-fw fa-clock"></i> <span title="' . human_time_diff($modified_date) . ' ago">Modified: ' . date_i18n('F j, Y', $modified_date) . '</span> <span class="nebula-admin-light">(' . $modified_author . ')</span>',
						'href' => get_edit_post_link(),
						'meta' => array('target' => '_blank', 'rel' => 'noopener')
					));
				}

				//Post status (Publish, Draft, Private, etc)
				if ( !empty($status) ){
					$wp_admin_bar->add_node(array(
						'parent' => $node_id,
						'id' => 'nebula-status',
						'title' => '<i class="nebula-admin-fa fas fa-fw fa-map-marker"></i> Status: ' . ucwords($status),
						'href' => get_edit_post_link(),
						'meta' => array('target' => '_blank', 'rel' => 'noopener')
					));
				}

				//Theme template file
				if ( !empty($this->current_theme_template) ){
					$wp_admin_bar->add_node(array(
						'parent' => $node_id,
						'id' => 'nebula-template',
						'title' => '<i class="nebula-admin-fa far fa-fw fa-object-group"></i> Template: ' . basename($this->current_theme_template) . ' <span class="nebula-admin-light">(' . dirname($this->current_theme_template) . ')</span>',
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
							'title' => '<i class="nebula-admin-fa fas fa-fw fa-level-up-alt"></i> Ancestor ' . ucwords($post_type_object->labels->name) . ' <small>(' . count($ancestors) . ')</small>',
						));

						foreach ( $ancestors as $parent ){
							$wp_admin_bar->add_node(array(
								'parent' => 'nebula-ancestors',
								'id' => 'nebula-parent-' . $parent,
								'title' => '<i class="nebula-admin-fa far fa-fw fa-file"></i> ' . get_the_title($parent),
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
								'title' => '<i class="nebula-admin-fa fas fa-fw fa-level-down-alt"></i> Children ' . ucwords($post_type_object->labels->name) . ' <small>(' . $child_pages->found_posts . ')</small>',
							));

							while ( $child_pages->have_posts() ){
								$child_pages->the_post();
								$wp_admin_bar->add_node(array(
									'parent' => 'nebula-children',
									'id' => 'nebula-child-' . get_the_id(),
									'title' => '<i class="nebula-admin-fa fas fa-fw fa-file"></i> ' . get_the_title(),
									'href' => ( $this->is_admin_page() )? get_edit_post_link() : get_permalink(),
								));
							}
						}

						wp_reset_postdata();
					}
				}

				$nebula_warning_icon = '';
				$nebula_adminbar_icon = 'fa-star';

				$warnings = $this->check_warnings();

				//Remove "log" level warnings
				if ( !empty($warnings) ){
					foreach ( $warnings as $key => $warning ){
						if ( $warning['level'] === 'log' ){
							unset($warnings[$key]);
						}
					}

					$nebula_adminbar_icon = 'fa-exclamation-triangle';
				}

				$wp_admin_bar->add_node(array(
					'id' => 'nebula',
					'title' => '<i class="nebula-admin-fa fas fa-fw ' . $nebula_adminbar_icon . '"></i> Nebula',
					'href' => 'https://gearside.com/nebula/?utm_campaign=documentation&utm_medium=nebula&utm_source=' . urlencode(get_bloginfo('name')) . '&utm_content=admin+bar' . $this->get_user_info('user_email', array('prepend' => '&nv-email=')),
					'meta' => array(
						'target' => '_blank',
						'rel' => 'noopener',
						'class' => ( !empty($warnings) )? 'has-warning' : '',
					)
				));

				$wp_admin_bar->add_node(array(
					'parent' => 'nebula',
					'id' => 'nebula-version',
					'title' => 'v<strong>' . $this->version('raw') . '</strong> <span class="nebula-admin-light">(' . $this->version('date') . ')</span>',
					'href' => 'https://github.com/chrisblakley/Nebula/compare/master@{' . date('Y-m-d', $this->version('utc')) . '}...master',
				));

				//If there are warnings display them
				if ( !empty($warnings) ){
					$wp_admin_bar->add_node(array(
						'parent' => 'nebula',
						'id' => 'nebula-warnings',
						'title' => '<i class="nebula-admin-fa fas fa-fw fa-exclamation-triangle"></i> Warnings',
					));

					foreach( $warnings as $key => $warning ){
						$warning_icon = 'fa-exclamation-triangle';

						if ( $warning['level'] === 'error' ){
							$warning_icon = 'fa-exclamation-triangle';
						} elseif ( $warning['level'] === 'warn' ){
							$warning_icon = 'fa-info-circle';
						}

						$wp_admin_bar->add_node(array(
							'parent' => 'nebula-warnings',
							'id' => 'nebula-warning-' . $key,
							'title' => '<i class="nebula-admin-fa fas fa-fw ' . $warning_icon . '" style="margin-left: 5px;"></i> ' . strip_tags($warning['description']),
							'href' => ( !empty($warning['url']) )? $warning['url'] : '',
							'meta' => array(
								'target' => '_blank',
								'rel' => 'noopener',
								'class' => 'nebula-warning level-' . $warning['level'],
							)
						));
					}
				}

				if ( !empty($nebula_warning_icon) ){
					if (!isset($nebula_warning_href)) {
						$nebula_warning_href = '';
					}
					if (!isset($nebula_warning_description)) {
						$nebula_warning_description = '';
					}
					$wp_admin_bar->add_node(array(
						'parent' => 'nebula',
						'id' => 'nebula-warning',
						'title' => '<i class="nebula-admin-fa fas fa-fw fa-exclamation-triangle" style="color: #ca3838; margin-right: 5px;"></i> ' . $nebula_warning_description,
						'href' => get_admin_url() . $nebula_warning_href,
					));
				}

				$third_party_tools = $this->third_party_tools();

				if ( !empty($third_party_tools) ){
					$wp_admin_bar->add_node(array(
						'parent' => 'nebula',
						'id' => 'nebula-tools',
						'title' => '<i class="nebula-admin-fa fas fa-fw fa-wrench"></i> Tools',
					));

					if ( !empty($third_party_tools['administrative']) ){
						$wp_admin_bar->add_node(array(
							'parent' => 'nebula-tools',
							'id' => 'nebula-tools-administrive',
							'title' => 'Administrative',
						));

						foreach ( $third_party_tools['administrative'] as $tool ){
							$wp_admin_bar->add_node(array(
								'parent' => 'nebula-tools-administrive',
								'id' => 'nebula-tool-' . strtolower(str_replace(' ', '_', $tool['name'])),
								'title' => $tool['icon'] . ' ' . $tool['name'],
								'href' => $tool['url'],
								'meta' => array('target' => '_blank', 'rel' => 'noopener')
							));
						}
					}

					if ( !empty($third_party_tools['social']) ){
						$wp_admin_bar->add_node(array(
							'parent' => 'nebula-tools',
							'id' => 'nebula-tools-social',
							'title' => 'Social',
						));

						foreach ( $third_party_tools['social'] as $tool ){
							$wp_admin_bar->add_node(array(
								'parent' => 'nebula-tools-social',
								'id' => 'nebula-tool-' . strtolower(str_replace(' ', '_', $tool['name'])),
								'title' => $tool['icon'] . ' ' . $tool['name'],
								'href' => $tool['url'],
								'meta' => array('target' => '_blank', 'rel' => 'noopener')
							));
						}
					}
				}

				$wp_admin_bar->add_node(array(
					'parent' => 'nebula',
					'id' => 'nebula-options',
					'title' => '<i class="nebula-admin-fa fas fa-fw fa-cog"></i> Options',
					'href' => get_admin_url() . 'themes.php?page=nebula_options'
				));

				foreach ( $this->get_option_categories() as $category ){
					$wp_admin_bar->add_node(array(
						'parent' => 'nebula-options',
						'id' => 'nebula-options-' . $category['name'],
						'title' => '<i class="nebula-admin-fa fas fa-fw ' . $category['icon'] . '"></i> ' . $category['name'],
						'href' => get_admin_url() . 'themes.php?page=nebula_options&tab=' . $category['name'],
						'meta' => array('target' => '_blank', 'rel' => 'noopener')
					));
				}

				$wp_admin_bar->add_node(array(
					'parent' => 'nebula-options',
					'id' => 'nebula-options-help',
					'title' => '<i class="nebula-admin-fa far fa-fw fa-question-circle"></i> Help & Documentation',
					'href' => 'https://gearside.com/nebula/documentation/options/?utm_campaign=documentation&utm_medium=nebula&utm_source=' . urlencode(get_bloginfo('name')) . '&utm_content=admin+bar+help',
					'meta' => array('target' => '_blank', 'rel' => 'noopener')
				));

				$wp_admin_bar->add_node(array(
					'parent' => 'nebula',
					'id' => 'nebula-github',
					'title' => '<i class="nebula-admin-fa fab fa-fw fa-github"></i> Nebula Github',
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
					'href' => 'https://github.com/chrisblakley/Nebula/commits/master',
					'meta' => array('target' => '_blank', 'rel' => 'noopener')
				));

				if ( $this->get_option('scss') ){
					$scss_last_processed = ( $this->get_data('scss_last_processed') )? date_i18n('l, F j, Y - g:i:sa', $this->get_data('scss_last_processed')) : 'Never';
					$wp_admin_bar->add_node(array(
						'parent' => 'nebula',
						'id' => 'nebula-options-scss',
						'title' => '<i class="nebula-admin-fa fab fa-fw fa-sass"></i> Re-process All Sass Files',
						'href' => esc_url(add_query_arg('sass', 'true')),
						'meta' => array('title' => 'Last: ' . $scss_last_processed)
					));
				}
			}
		}

		//Colorize Nebula warning nodes in the admin bar
		public function admin_bar_warning_styles(){
			if ( is_admin_bar_showing() ){ ?>
				<style type="text/css">
					#wpadminbar .nebula-admin-fa {font-family: "Font Awesome 5 Solid", "Font Awesome 5 Free", "Font Awesome 5 Pro"; font-weight: 900;}
						#wpadminbar .nebula-admin-fa.fab {font-family: "Font Awesome 5 Brands", "Font Awesome 5 Free", "Font Awesome 5 Pro"; font-weight: 400;}
					#wpadminbar .svg-inline--fa {color: #a0a5aa; color: rgba(240, 245, 250, .6); margin-right: 5px;}
					#wpadminbar .nebula-admin-light {font-size: 10px; color: #a0a5aa; color: rgba(240, 245, 250, .6); line-height: inherit;}

					#wpadminbar:not(.mobile) .ab-top-menu > #wp-admin-bar-nebula.has-warning > .ab-item {background: #ca3838;}
						#wpadminbar:not(.mobile) .ab-top-menu > #wp-admin-bar-nebula.has-warning.hover > .ab-item,
						#wpadminbar:not(.mobile) .ab-top-menu > #wp-admin-bar-nebula.has-warning:hover > .ab-item {background: maroon; color: #fff; transition: all 0.25s ease;}

					#wpadminbar:not(.mobile) #wp-admin-bar-nebula-warnings {background: #ca3838;}
						#wpadminbar:not(.mobile) #wp-admin-bar-nebula-warnings > .ab-item,
						#wpadminbar:not(.mobile) #wp-admin-bar-nebula-warnings > svg {color: #fff;}
						#wpadminbar:not(.mobile) #wp-admin-bar-nebula-warnings .level-error svg {color: #ca3838;}
						#wpadminbar:not(.mobile) #wp-admin-bar-nebula-warnings .level-warn svg {color: #f6b83f;}
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
						if ( e.altKey && e.which === 65 ){ //Alt+A
							jQuery('html').toggleClass('admin-bar-inactive');
						}

						//Reprocess all Sass files
						if ( e.altKey && e.which === 82 ){ //Alt+R
							if ( typeof URL === "function" ){ //No IE support for URL API. Eventually remove this check.
								var url = new URL(window.location.href);
								url.searchParams.set('sass', 'true');
								location = url; //Reload with the new URL
							}
						}
					});
				</script>
			<?php }
		}

		//Show update warning on Wordpress Core/Plugin update admin pages
		public function update_warning(){
			echo "<div class='nebula_admin_notice error'><p><strong>WARNING:</strong> Updating Wordpress plugins may cause irreversible errors to your website!</p><p>Contact <a href='http://www.pinckneyhugo.com?utm_campaign=nebula&utm_medium=nebula&utm_source=" . urlencode(get_bloginfo('name')) . "&utm_content=update+warning" . $this->get_user_info('user_email', array('prepend' => '&nv-email=')) . "'>Pinckney Hugo Group</a> if there are questions about updates: (315) 478-6700</p></div>";
		}

		//Nebula Theme Update Checker
		public function theme_json(){
			$override = apply_filters('pre_nebula_theme_json', null);
			if ( isset($override) ){return;}

			$nebula_data = get_option('nebula_data');

			//Always keep current_version up-to-date.
			if ( empty($nebula_data['current_version']) || empty($nebula_data['current_version_date']) || strtotime($nebula_data['current_version_date'])-strtotime($this->version('date')) < 0 ){
				$this->update_data('current_version', $this->version('raw'));
				$this->update_data('current_version_date', $this->version('date'));
			}

			if ( !$this->allow_theme_update() ){
				//Check for unsupported version: if newer version of Nebula has a "u" at the end of the version number, disable automated updates.
				$remote_version_info = get_option('external_theme_updates-Nebula-master');
				if ( !empty($remote_version_info->checkedVersion) && strpos($remote_version_info->checkedVersion, 'u') && str_replace('u', '', $remote_version_info->checkedVersion) !== str_replace('u', '', $this->version('raw')) ){
					$this->update_data('version_legacy', 'true');
					$this->update_data('current_version', $this->version('raw'));
					$this->update_data('current_version_date', $this->version('date'));
					$this->update_data('next_version', 'INCOMPATIBLE');
				}
			} elseif ( current_user_can('update_themes') && is_child_theme() ){
				require_once get_template_directory() . '/inc/vendor/plugin-update-checker/plugin-update-checker.php';
				$theme_update_checker = Puc_v4_Factory::buildUpdateChecker(
					'https://raw.githubusercontent.com/chrisblakley/Nebula/master/inc/data/nebula_theme.json',
					get_template_directory() . '/functions.php',
					'Nebula' //The filter hook above must match this
				);
			}
		}

		//Force a re-install of the Nebula theme
		public function force_nebula_theme_update($updates){
			if ( current_user_can('update_themes') && is_child_theme() && $this->is_nebula() && isset($_GET['force-nebula-theme-update']) ){
				$parent_theme = wp_get_theme()->get('Template');

				$updates->response[$parent_theme] = array(
					'theme' => $parent_theme,
					'new_version' => $this->version('full'), //Does not need to be larger than current version
					'url' => 'https://github.com/chrisblakley/Nebula/commits/master',
					'package' => 'https://github.com/chrisblakley/Nebula/archive/master.zip'
				);
			}

			return $updates;
		}

		//When checking for theme updates, store the next and current Nebula versions from the response. Hook is inside the theme-update-checker.php library.
		public function theme_update_version_store($update){
			if ( $this->allow_theme_update() ){
				$this->update_data('next_version', $update->version);
				$this->update_data('current_version', $this->version('full'));
				$this->update_data('current_version_date', $this->version('date'));

				if ( strpos($update->version, 'u') && str_replace('u', '', $update->version) !== str_replace('u', '', $this->version('full')) ){ //If Github version has "u", disable automated updates.
					$this->update_data('version_legacy', 'true');
				} elseif ( $this->get_data('version_legacy') === 'true' ){ //Else, reset the option to false (this triggers when a legacy version has been manually updated to support automated updates again).
					$this->update_data('version_legacy', 'false');
					$this->update_data('theme_update_notification', 'disabled');
				}

				return $update;
			}

			return false;
		}

		//After theme update has been completed
		public function theme_update_automation($upgrader_object, $options){
			$override = apply_filters('pre_nebula_theme_update_automation', null);
			if ( isset($override) ){return;}

			if ( $this->allow_theme_update() ){
				if ( $options['type'] === 'theme' && $this->in_array_r('Nebula-master', $options['themes']) ){
					$prev_version = $this->get_data('current_version');
					$prev_version_commit_date = $this->get_data('current_version_date');
					$new_version = $this->get_data('next_version');
					$num_theme_updates = $this->get_data('num_theme_updates')+1;
					$current_user = wp_get_current_user();
					$this->usage('Automated Theme Update', array('d11' => 'From ' . $prev_version . ' to ' . $new_version, 'cm1' => $num_theme_updates));

					$this->theme_update_email($prev_version, $prev_version_commit_date, $new_version); //Send email with update information
					$this->update_data('version_legacy', 'false');
					$this->update_data('need_sass_compile', 'true'); //Compile all SCSS files on next pageview
					$this->update_data('num_theme_updates', $num_theme_updates);
					$this->update_data('last_automated_update_date', date('U'));
					$this->update_data('last_automated_update_user', $current_user->display_name);
					$this->update_data('check_new_options', 'true'); //Check for new Nebula Options on next pageview
				}
			} else {
				$this->update_data('version_legacy', 'true');
			}
		}

		//Send an email to the current user and site admin that Nebula has been updated.
		public function theme_update_email($prev_version, $prev_version_commit_date, $new_version){
			$nebula_update_email_sent = get_transient('nebula_update_email_sent');
			if ( (empty($nebula_update_email_sent) || $this->is_debug()) && $prev_version !== $new_version ){
				global $wpdb;
				$current_user = wp_get_current_user();
				$to = $current_user->user_email;
				$headers = array(); //Prep a headers array if needed

				//Carbon copy the admin if update was done by another user.
				$admin_user_email = $this->get_option('notification_email', $this->get_option('admin_email'));
				if ( !empty($admin_user_email) && $admin_user_email !== $current_user->user_email ){
					$headers[] = 'Cc: ' . $admin_user_email;
				}

				$subject = 'Nebula updated to ' . $new_version . ' for ' . html_entity_decode(get_bloginfo('name')) . '.';
				$message = '<p>The parent Nebula theme has been updated from version <strong>' . $prev_version . '</strong> (Committed: ' . $prev_version_commit_date . ') to <strong>' . $new_version . '</strong> for ' . get_bloginfo('name') . ' (' . home_url('/') . ') by ' . $current_user->display_name . ' on ' . date_i18n('F j, Y') . ' at ' . date('g:ia') . '.<br/><br/>To revert, find the previous version in the <a href="https://github.com/chrisblakley/Nebula/commits/master" target="_blank" rel="noopener">Nebula Github repository</a>, download the corresponding .zip file, and upload it replacing /themes/Nebula-master/.</p>';

				//Set the content type to text/html for the email.
				add_filter('wp_mail_content_type', function($content_type){
					return 'text/html';
				});

				//Send the email, and on success set a transient to prevent multiple emails
				if ( wp_mail($to, $subject, $message, $headers) ){
					set_transient('nebula_update_email_sent', true, MINUTE_IN_SECONDS*15);
				}
			}
		}

		//Control session time (for the "Remember Me" checkbox)
		public function session_expire($expirein){
			return 2592000; //30 days (Default is 1209600 (14 days)
		}

		//Send Google Analytics pageviews on the WP Admin and Login pages too
		public function admin_ga_pageview(){
			if ( empty($_POST['signed_request']) && $this->get_option('ga_tracking_id') ){
				?>
					<script>
						window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;
						ga('create', '<?php echo $this->get_option('ga_tracking_id'); ?>', 'auto'<?php echo ( $this->get_option('ga_wpuserid') && is_user_logged_in() )? ', {"userId": "' . get_current_user_id() . '"}': ''; ?>);
						ga('send', 'pageview');
					</script>
					<script async src='https://www.google-analytics.com/analytics.js'></script>
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
		public function admin_notices(){
			$warnings = $this->check_warnings();

			//If there are warnings display them
			if ( !empty($warnings) ){
				foreach( $warnings as $warning ){
					$category = ( !empty($warning['category']) )? '[' . $warning['category'] . ']' : '[Nebula]';

					if ( $warning['level'] === 'warn' ){
						$warning['level'] = 'warning';
					}

					if ( $warning['level'] === 'log' ){
						$warning['level'] = 'info';
					}

					echo '<div class="nebula-admin-notice notice notice-' . $warning['level'] . '"><p>' . $category . ' ' . $warning['description'] . '</p></div>'; //@TODO "Nebula" 0: Make these dismissable
				}
			}
		}

		//Check the current (or passed) PHP version against the PHP support timeline.
		public function php_version_support($php_version=PHP_VERSION){
			$override = apply_filters('pre_nebula_php_version_support', null, $php_version);
			if ( isset($override) ){return;}

			$php_timeline_json_file = get_template_directory() . '/inc/data/php_timeline.json';
			$php_timeline = get_transient('nebula_php_timeline');
			if ( (empty($php_timeline) || $this->is_debug()) ){
				$response = $this->remote_get('https://raw.githubusercontent.com/chrisblakley/Nebula/master/inc/data/php_timeline.json');
				if ( !is_wp_error($response) ){
					$php_timeline = $response['body'];
				}

				WP_Filesystem();
				global $wp_filesystem;
				if ( !empty($php_timeline) ){
					$wp_filesystem->put_contents($php_timeline_json_file, $php_timeline); //Store it locally.
					set_transient('nebula_php_timeline', $php_timeline, YEAR_IN_SECONDS/12); //1 month cache
				} else {
					$php_timeline = $wp_filesystem->get_contents($php_timeline_json_file);
				}
			}

			$php_timeline = json_decode($php_timeline);
			if ( !empty($php_timeline) ){
				preg_match('/^(?<family>\d\.\d)\.?/i', PHP_VERSION, $current_php_version); //Grab the major/minor version of this PHP
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

		//Check if a post slug has a number appended to it (indicating a duplicate post).
		public function unique_slug_warning_ajax($slug, $post_ID, $post_status, $post_type){
			if ( current_user_can('publish_posts') && $this->is_admin_page() && (headers_sent() || $this->is_ajax_request()) ){ //Should work with AJAX and without (as long as headers have been sent)
				echo '<script>
					if ( typeof nebulaUniqueSlugChecker === "function" ){
						nebulaUniqueSlugChecker("' . $post_type . '");
					}
				</script>';
			}
			return $slug;
		}

		//Allow SVG files to be uploaded to the Media Library
		public function allow_svg_uploads($data, $file, $filename, $mimes){
			$filetype = wp_check_filetype($filename, $mimes);

			return array(
				'ext' => $filetype['ext'],
				'type' => $filetype['type'],
				'proper_filename' => $data['proper_filename']
			);
		}
		public function additional_upload_mime_types($mime_types){
			$mime_types['svg'] = 'image/svg+xml';
			return $mime_types;
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

		//Prevent Yoast from publishing author sitemaps when Nebula author bios are disabled
		public function disable_yoast_author_indexing(){
			if ( is_plugin_active('wordpress-seo/wp-seo.php') ){
				WPSEO_Options::set('disable-author', true);
			}
		}

		//Duplicate post
		public function duplicate_post_as_draft(){
			global $wpdb;
			if ( !(isset($_GET['post']) || isset($_POST['post']) || (isset($_REQUEST['action']) && $_REQUEST['action'] === 'duplicate_post_as_draft')) ){
				wp_die('No post to duplicate has been supplied!');
			}

			$post_id = ( isset($_GET['post'] )? intval($_GET['post']) : intval($_POST['post'])); //Get the original post id
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
				$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
				if ( count($post_meta_infos) !== 0 ){
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
		public function rd_duplicate_post_link($actions, $post){
			if ( current_user_can('edit_posts') ){
				$actions['duplicate'] = '<a href="admin.php?action=duplicate_post_as_draft&amp;post=' . $post->ID . '" title="Duplicate this item" rel="permalink">Duplicate</a>';
			}
			return $actions;
		}

		//Show File URL column on Media Library listings
		public function muc_column($cols){
			$cols["media_url"] = "File URL";
			return $cols;
		}

		public function muc_value($column_name, $id){
			if ( $column_name === "media_url" ){
				echo '<input type="text" width="100%" value="' . wp_get_attachment_url($id) . '" readonly />';
			}
		}

		//All Settings page link
		public function all_settings_link(){
			add_theme_page('All Settings', 'All Settings', 'administrator', 'options.php');
		}

		//Clear caches when plugins are activated if W3 Total Cache is active
		public function clear_all_w3_caches(){
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
			if ( is_plugin_active('w3-total-cache/w3-total-cache.php') && isset($_SERVER['activate']) && $_SERVER['activate'] == 'true'){
				if ( function_exists('w3tc_pgcache_flush') ){
					w3tc_pgcache_flush();
				}
			}
		}

		//Admin footer left side
		public function change_admin_footer_left(){
			return $this->pinckneyhugogroup() . ' &bull; <a href="https://www.google.com/maps/dir/Current+Location/760+West+Genesee+Street+Syracuse+NY+13204" target="_blank" rel="noopener">760 West Genesee Street, Syracuse, NY 13204</a> &bull; (315) 478-6700';
		}

		//Admin footer right side
		public function change_admin_footer_right(){
			global $wp_version;
			$child = ( is_child_theme() )? ' <small>(Child)</small>' : '';
			return '<span><a href="https://codex.wordpress.org/WordPress_Versions" target="_blank" rel="noopener">WordPress</a> <strong>' . $wp_version . '</strong></span>, <span title="Committed: ' . $this->version('date') . '"><a href="https://gearside.com/nebula/?utm_campaign=documentation&utm_medium=nebula&utm_source=' . urlencode(get_bloginfo('name')) . '&utm_content=footer+version' . $this->get_user_info('user_email', array('prepend' => '&nv-email=')) . '" target="_blank" rel="noopener">Nebula</a> <strong class="nebula"><a href="https://github.com/chrisblakley/Nebula/compare/master@{' . date('Y-m-d', $this->version('utc')) . '}...master" target="_blank">' . $this->version('version') . '</a></strong>' . $child . '</span>';
		}

		public function post_meta_boxes_setup(){
			add_action('add_meta_boxes', array($this, 'nebula_add_post_metabox'));
			add_action('save_post', array($this, 'save_post_class_meta' ), 10, 2);
		}

		//Internal Search Keywords Metabox and Custom Field
		public function nebula_add_post_metabox(){
			$builtin_types = array('post', 'page', 'attachment');
			$custom_types = get_post_types(array('_builtin' => false));
			$avoid_types = array('acf', 'acf-field-group', 'wpcf7_contact_form');

			foreach ( $builtin_types as $builtin_type ){
				add_meta_box('nebula-post-data', 'Nebula Post Data', array($this, 'nebula_post_metabox' ), $builtin_type, 'side', 'default');
			}

			foreach( $custom_types as $custom_type ){
				if ( !in_array($custom_type, $avoid_types) ){
					add_meta_box('nebula-post-data', 'Nebula Post Data', array($this, 'nebula_post_metabox' ), $custom_type, 'side', 'default');
				}
			}
		}

		//Internal Search Keywords Metabox content
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

		public function save_post_class_meta($post_id, $post){
			if ( !isset($_POST['nebula_post_nonce']) || !wp_verify_nonce($_POST['nebula_post_nonce'], basename(__FILE__)) ){
				return $post_id;
			}

			$post_type = get_post_type_object($post->post_type); //Get the post type object.
			if ( !current_user_can($post_type->cap->edit_post, $post_id) ){ //Check if the current user has permission to edit the post.
				return $post_id;
			}

			$nebula_post_meta_fields = array('nebula_body_classes', 'nebula_post_classes', 'nebula_internal_search_keywords');
			foreach ( $nebula_post_meta_fields as $nebula_post_meta_field ){
				$new_meta_value = sanitize_text_field($_POST[$nebula_post_meta_field]); //Get the posted data and sanitize it if needed.
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
}