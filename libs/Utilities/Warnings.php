<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Warnings') ){
	trait Warnings {
		public $warnings = false;

		public function hooks(){
			if ( (is_user_logged_in() || $this->is_auditing()) && !$this->is_background_request() && !is_customize_preview() ){
				add_action('wp_head', array($this, 'console_warnings'));
				add_filter('nebula_warnings', array($this, 'advanced_warnings'));
				add_action('wp_footer', array($this, 'advanced_warning_output'), 9999); //Late execution as possible
				add_action('admin_bar_menu', array($this, 'qm_warnings'));
			}
		}

		//Determine the desired warning level
		public function is_warning_level($needed, $actual=false){
			$actual = ( !empty($actual) )? $actual : $this->get_option('warnings'); //Get the selected warning level from Nebula Options

			if ( $actual === 'off' ){ //If the site setting is off return false
				return false;
			} elseif ( $actual === 'on' ){
				return true;
			}

			$warning_level_order = array('critical', 'verbose', 'strict');

			if ( array_search(strtolower($needed), $warning_level_order) <= array_search(strtolower($actual), $warning_level_order) ){
				return true;
			}

			return false;
		}

		//Log warnings in the console
		public function console_warnings($console_warnings=array()){
			if ( (current_user_can('manage_options') || $this->is_dev()) && $this->is_warning_level('on') && !is_customize_preview() ){
				$this->warnings = $this->check_warnings();

				//If there are warnings, send them to the console.
				if ( !empty($this->warnings) ){
					echo '<script>';
					foreach( $this->warnings as $warning ){
						$category = ( !empty($warning['category']) )? $warning['category'] : 'Nebula';
						$log_level = ( $warning['level'] == 'warning' )? 'warn' : $warning['level'];
						echo 'console.' . esc_html($log_level) . '("[' . esc_html($category) . '] ' . esc_html(addslashes(strip_tags($warning['description']))) . '");';
					}
					echo '</script>';
				}
			}
		}

		//Report warnings to Query Monitor
		public function qm_warnings(){
			$this->warnings = $this->check_warnings();

			if ( !empty($this->warnings) ){
				foreach( $this->warnings as $warning ){
					$category = ( !empty($warning['category']) )? $warning['category'] : 'Nebula';
					do_action('qm/debug', '[' . $category . ']' . strip_tags($warning['description'])); //Use "debug" to log without coloring the admin bar (which is redundant since the Nebula admin bar menu designates those warnings)
				}
			}
		}

		//Check for Nebula warnings
		public function check_warnings(){
			if ( $this->is_ajax_request() ){
				return false;
			}

			if ( $this->is_auditing() || $this->is_warning_level('on') ){
				//Check object cache first
				$nebula_warnings = wp_cache_get('nebula_warnings');

				if ( is_array($nebula_warnings) || !empty($nebula_warnings) ){ //If it is an array (meaning it has run before but did not find anything) or if it is false
					return $nebula_warnings;
				}

				$this->timer('Check Warnings');
				$nebula_warnings = array(); //Prep the warnings array to fill

				//Admin warnings only
				if ( $this->is_admin_page() ){
					//Check page slug against taxonomy terms.
					global $pagenow;
					if ( $pagenow === 'post.php' || $pagenow === 'edit.php' ){
						global $post;

						if ( !empty($post) ){ //If the listing has results
							foreach ( get_taxonomies() as $taxonomy ){ //Loop through all taxonomies
								foreach ( get_terms($taxonomy, array('hide_empty' => false)) as $term ){ //Loop through all terms within each taxonomy
									if ( $term->slug === $post->post_name ){ //If this page slug matches a taxonomy term
										$nebula_warnings['slug_conflict'] = array(
											'level' => 'error',
											'dismissible' => true,
											'description' => '<i class="fa-solid fa-fw fa-link"></i> Slug conflict with ' . ucwords(str_replace('_', ' ', $taxonomy)) . ': <strong>' . $term->slug . '</strong> - Consider changing this page slug.'
										);
										return false;
									}
								}
							}
						}
					}

					//Test the WordPress filesystem method
					$fs_method_transient = get_transient('nebula_fs_method');
					if ( empty($fs_method_transient) || $this->is_debug() ){
						if ( file_exists(get_template_directory() . '/style.css') ){
							WP_Filesystem();
							global $wp_filesystem;
							$test_file = $wp_filesystem->get_contents(get_template_directory() . '/style.css');

							if ( empty($test_file) ){
								$nebula_warnings['file_permissions'] = array(
									'level' => 'error',
									'dismissible' => true,
									'description' => '<i class="fa-solid fa-fw fa-server"></i> File system permissions error. Consider changing the FS_METHOD in wp-config.php.',
								);
							} else {
								set_transient('nebula_fs_method', true); //On success, set a transient. No expiration.
							}
						}
					}
				}

				//If the site is served via HTTPS but the Site URL is still set to HTTP
				if ( (is_ssl() || isset($this->super->server['HTTPS'])) && (strpos(home_url(), 'http://') !== false || strpos(get_option('siteurl'), 'http://') !== false) ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
					$nebula_warnings['site_url_http'] = array(
						'level' => 'error',
						'dismissible' => true,
						'description' => '<i class="fa-solid fa-fw fa-lock-open"></i> <a href="options-general.php">Website Address</a> settings are http but the site is served from https.',
						'url' => admin_url('options-general.php')
					);
				}

				//If search indexing is disabled
				if ( get_option('blog_public') == 0 ){ //Stored as a string
					$nebula_warnings['search_visibility'] = array(
						'level' => 'error',
						'dismissible' => true,
						'description' => '<i class="fa-brands fa-fw fa-searchengin"></i> <a href="options-reading.php">Search Engine Visibility</a> is currently disabled! Therefore, additional SEO checks were not performed.',
						'url' => admin_url('options-reading.php')
					);
				} else {
					if ( $this->is_transients_enabled() ){ //Only check these if transients are not suspended
						//Check for sitemap
						$sitemap_transient = get_transient('nebula_check_sitemap');
						if ( empty($sitemap_transient) || $this->is_debug() ){
							$sitemap_warning = false;
							if ( is_plugin_active('wordpress-seo/wp-seo.php') ){ //Yoast
								if ( !$this->is_available(home_url('/') . 'sitemap_index.xml', false, true) ){
									$sitemap_warning = true;
									$nebula_warnings['missing_sitemap'] = array(
										'level' => 'warning',
										'dismissible' => true,
										'description' => '<i class="fa-solid fa-fw fa-sitemap"></i> Missing sitemap XML. Yoast is enabled, but <a href="' . home_url('/') . 'sitemap_index.xml" target="_blank">sitemap_index.xml</a> is unavailable.'
									);
								}
							} elseif ( is_plugin_active('autodescription/autodescription.php') ){ //The SEO Framework
								if ( !$this->is_available(home_url('/') . 'sitemap.xml', false, true) ){
									$sitemap_warning = true;
									$nebula_warnings['missing_sitemap'] = array(
										'level' => 'warning',
										'dismissible' => true,
										'description' => '<i class="fa-solid fa-fw fa-sitemap"></i> Missing sitemap XML. The SEO Framework is enabled, but <a href="' . home_url('/') . 'sitemap.xml" target="_blank">sitemap.xml</a> is unavailable.'
									);
								}
							} else {
								if ( !$this->is_available(home_url('/') . 'wp-sitemap.xml', false, true)  ){ //WordPress Core
									$sitemap_warning = true;
									$nebula_warnings['missing_sitemap'] = array(
										'level' => 'warning',
										'dismissible' => true,
										'description' => '<i class="fa-solid fa-fw fa-sitemap"></i> Missing sitemap XML. WordPress core <a href="' . home_url('/') . 'wp-sitemap.xml" target="_blank">sitemap_index.xml</a> is unavailable.'
									);

									//Check if the SimpleXML PHP module is installed on the server (required for WP core sitemap generation)
									if ( !function_exists('simplexml_load_string') ){
										$sitemap_warning = true;
										$nebula_warnings['simplexml'] = array(
											'level' => 'warning',
											'dismissible' => true,
											'description' => '<i class="fa-solid fa-fw fa-sitemap"></i> SimpleXML PHP module is not available. This is required for WordPress core sitemap generation.'
										);
									}
								}
							}

							//If there is no warning, only check periodically
							if ( !$sitemap_warning ){
								set_transient('nebula_check_sitemap', 'Sitemap Found', WEEK_IN_SECONDS);
							}
						}
					}

					//If not pinging additional update services (blog must be public for this to be available)
					if ( $this->is_warning_level('verbose') ){
						$ping_sites = get_option('ping_sites');
						if ( $ping_sites === 'http://rpc.pingomatic.com/' ){ //If it only has the default value (ignore empty value if that is done intentionally)
							$nebula_warnings['update_services'] = array(
								'level' => 'warning',
								'dismissible' => true,
								'description' => '<i class="fa-solid fa-fw fa-rss"></i> Additional <a href="options-writing.php">Update Services</a> should be pinged. <a href="https://codex.wordpress.org/Update_Services#XML-RPC_Ping_Services" target="_blank" rel="noopener">Recommended update services &raquo;</a>',
								'url' => admin_url('options-writing.php')
							);
						}
					}
				}

				//Check PHP version
				$php_version_lifecycle = $this->php_version_support();
				if ( !empty($php_version_lifecycle) ){
					if ( $php_version_lifecycle['lifecycle'] === 'security' ){
						if ( $php_version_lifecycle['end']-time() < MONTH_IN_SECONDS ){ //If end of life is within 1 month
							$nebula_warnings['php_lifecycle_main'] = array(
								'level' => 'warning',
								'dismissible' => true,
								'description' => '<i class="fa-brands fa-fw fa-php"></i> PHP <strong>' . PHP_VERSION . '</strong> <a href="http://php.net/supported-versions.php" target="_blank" rel="noopener">is nearing end of life</a>. Security updates end in ' . human_time_diff($php_version_lifecycle['end']) . ' on ' . date('F j, Y', $php_version_lifecycle['end']) . '.',
								'url' => 'http://php.net/supported-versions.php',
								'meta' => array('target' => '_blank', 'rel' => 'noopener')
							);
						}
					} elseif ( $php_version_lifecycle['lifecycle'] === 'end' ){
						$nebula_warnings['php_lifecycle_end'] = array(
							'level' => 'error',
							'dismissible' => false,
							'description' => '<i class="fa-brands fa-fw fa-php"></i> PHP ' . PHP_VERSION . ' <a href="http://php.net/supported-versions.php" target="_blank" rel="noopener">no longer receives security updates</a>! End of life occurred ' . human_time_diff($php_version_lifecycle['end']) . ' ago on ' . date('F j, Y', $php_version_lifecycle['end']) . '.',
							'url' => 'http://php.net/supported-versions.php',
							'meta' => array('target' => '_blank', 'rel' => 'noopener')
						);
					}
				}

				//Check specific directories for indexing (Apache directory listings)
				if ( $this->is_transients_enabled() ){ //Don't run these checks without transients because they are too time consuming to run every admin page load
					$directory_indexing = get_transient('nebula_directory_indexing');
					if ( empty($directory_indexing) || nebula()->is_debug() || nebula()->is_auditing() ){ //Use the transient unless ?debug or explicitly auditing
						$directories = array(includes_url(), content_url()); //Directories to test
						$found_problem = false;
						foreach ( $directories as $directory ){
							//Get the contents of the directory
							$directory_request = $this->remote_get($directory, array(
								'timeout' => 3,
								'limit_response_size' => KB_IN_BYTES*512 //Limit the response to 512kb
							));

							if ( !is_wp_error($directory_request) && !empty($directory_request) ){ //If not an error and response exists
								if ( $directory_request['response']['code'] <= 400 ){ //Check if the response code is less than 400 (in this case 400+ is good)
									if ( strpos(strtolower($directory_request['body']), 'index of') ){ //Check if the "Index of" text appears in the body content (bad) //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
										$nebula_warnings['directory_indexing'] = array(
											'level' => 'error',
											'dismissible' => false,
											'description' => '<i class="fa-regular fa-fw fa-list-alt"></i> Directory indexing is not disabled. Visitors can see file listings of directories!',
										);

										$found_problem = true;
										set_transient('nebula_directory_indexing', 'bad', WEEK_IN_SECONDS);
										break; //Exit loop since we found an issue
									}
								}
							}
						}

						//If we did not find a problem, set a longer transient
						if ( empty($found_problem) ){
							set_transient('nebula_directory_indexing', 'good'); //No expiration so it is not cleared when making new posts
						}
					} else {
						if ( $directory_indexing === 'bad' ){
							$nebula_warnings['directory_indexing'] = array(
								'level' => 'error',
								'dismissible' => true,
								'description' => '<i class="fa-regular fa-fw fa-list-alt"></i> <strong>Directory indexing not disabled</strong> (at the time last checked). Visitors may be able to see file listings of directories such as the <a href="' . includes_url() . '" target="_blank">Includes URL</a> or <a href="' . content_url() . '" target="_blank">Content URL</a> (and/or others)! <a href="' . home_url('/?audit=true') . '" target="_blank">Run an audit to re-scan &raquo;</a>',
							);
						}
					}
				}

				//Check individual files for anything unusual
				if ( nebula()->is_auditing() ){ //Only check all files when auditing
					$directories_to_scan = array(ABSPATH . '/wp-admin', ABSPATH . '/wp-includes', get_template_directory(), get_stylesheet_directory()); //Change this to simply ABSPATH to scan the entire WordPress directory
					foreach ( $directories_to_scan as $directory ){
						foreach ( $this->glob_r($directory . '/*') as $file ){
							if ( !$this->contains($file, array('/cache', '/uploads')) ){ //Skip certain directories
								if ( is_file($file) ){
									//If file was last modified before the year 2000
									if ( filemtime($file) < 946702800 ){ //PHP 7.4 use numeric separators here
										$nebula_warnings['unusual_filemtime'] = array(
											'level' => 'warning',
											'dismissible' => true,
											'description' => '<i class="fa-solid fa-fw fa-hourglass-start"></i> <strong>' . $file . '</strong> was last modified on ' . date('F j, Y', filemtime($file)) . '. This is somewhat unusual and should be looked into.'
										);
									}

									//If the file size is larger than 10mb
									if ( filesize($file) > MB_IN_BYTES*10 ){
										$filesize = ( function_exists('bcdiv') )? bcdiv(filesize($file), MB_IN_BYTES, 0) : number_format(filesize($file)/MB_IN_BYTES, 2);

										$nebula_warnings['large_file'] = array(
											'level' => 'warning',
											'dismissible' => true,
											'description' => '<i class="fa-solid fa-fw fa-file"></i> <strong>' . $file . '</strong> has a large filesize of ' . $filesize . 'mb.'
										);
									}
								}
							}
						}
					}
				} else { //Otherwise just check a few files
					foreach ( $this->get_log_files('all') as $types ){
						foreach ( $types as $log_file ){
							if ( $log_file['bytes'] > MB_IN_BYTES*25 ){
								$nebula_warnings[] = array( //No key on this one so they do not overwrite when multiple are present
									'level' => 'warning',
									'dismissible' => true,
									'description' => '<i class="fa-solid fa-fw fa-weight"></i> Large debug file: <strong>' . $log_file['shortpath'] . '</strong> (' . $this->format_bytes($log_file['bytes'], 1) . ') <small><a href="' . esc_url(add_query_arg('debug', 'true')) . '">Re-Scan?</a></small>',
								);
							}
						}
					}
				}

				//Check for hard Debug Mode
				//We do not check for WP_DEBUG_LOG as it can intentionally be used on live websites. Nebula does warn when the log file gets large and indicates when it exists in the Developer Info Dashboard Metabox.
				if ( $this->is_warning_level('verbose') && WP_DEBUG ){
					if ( wp_get_environment_type() === 'production' ){ //Do not warn in development environments
						$nebula_warnings['wp_debug'] = array(
							'level' => 'warning',
							'dismissible' => true,
							'description' => '<i class="fa-solid fa-fw fa-bug"></i> <strong>WP_DEBUG</strong> is enabled <small>(Generally defined in wp-config.php)</small>'
						);

						if ( WP_DEBUG_DISPLAY ){
							$nebula_warnings['wp_debug_display'] = array(
								'level' => 'error',
								'dismissible' => true,
								'description' => '<i class="fa-solid fa-fw fa-bug"></i> Debug errors and warnings are being displayed on the front-end (<Strong>WP_DEBUG_DISPLAY</strong>) <small>(Generally defined in wp-config.php)</small>'
							);
						}
					}
				}

				//Check if logging JavaScript errors
				if ( $this->get_option('js_error_log') ){
					$js_log_file = get_stylesheet_directory() . '/js_error.log';

					$nebula_warnings['js_error_log'] = array(
						'level' => 'warning',
						'dismissible' => false,
						'description' => '<i class="fa-solid fa-fw fa-hard-hat"></i> <strong><a href="themes.php?page=nebula_options&tab=administration" target="_blank">JS Error Logging</a></strong> is active: <strong>' . str_replace(ABSPATH, '', $js_log_file) . '</strong> (' . $this->format_bytes(filesize($js_log_file), 1) . ')',
						'url' => admin_url('themes.php?page=nebula_options&tab=administration')
					);
				}

				//Check for Safe Mode
				if ( $this->is_safe_mode() ){
					$nebula_warnings['safe_mode'] = array(
						'level' => 'error',
						'dismissible' => false,
						'description' => '<i class="fa-solid fa-fw fa-hard-hat"></i> <strong>Nebula Safe Mode</strong> is active <small>(' . WPMU_PLUGIN_DIR . '/nebula-safe-mode.php)</small>'
					);
				}

				//Check for Google Analytics Measurement ID
				if ( !$this->get_option('ga_measurement_id') && !$this->get_option('gtm_id') ){
					$nebula_warnings['ga_measurement_id'] = array(
						'level' => 'error',
						'dismissible' => true,
						'description' => '<i class="fa-solid fa-fw fa-chart-area"></i> A <a href="themes.php?page=nebula_options&tab=analytics&option=ga_measurement_id">Google Analytics tracking ID</a> or <a href="themes.php?page=nebula_options&tab=analytics&option=gtm_id">Google Tag Manager ID</a> is strongly recommended!',
						'url' => admin_url('themes.php?page=nebula_options&tab=analytics')
					);
				}

				//If Enhanced Ecommerce Plugin is missing Google Analytics Tracking ID
				if ( is_plugin_active('enhanced-e-commerce-for-woocommerce-store/woocommerce-enhanced-ecommerce-google-analytics-integration.php') ){
					$ee_ga_settings = get_option('woocommerce_enhanced_ecommerce_google_analytics_settings');
					if ( empty($ee_ga_settings['ga_id']) ){
						$nebula_warnings['enhanced_ecommerce'] = array(
							'level' => 'error',
							'dismissible' => true,
							'description' => '<i class="fa-solid fa-fw fa-chart-area"></i> <a href="admin.php?page=wc-settings&tab=integration">WooCommerce Enhanced Ecommerce</a> is missing a Google Analytics ID!',
							'url' => admin_url('admin.php?page=wc-settings&tab=integration')
						);
					}
				}

				//Child theme checks
				if ( is_child_theme() ){
					//Check if the parent theme template is correctly referenced
					$active_theme = wp_get_theme();
					if ( !file_exists(dirname(get_stylesheet_directory()) . '/' . $active_theme->get('Template')) ){
						$nebula_warnings['no_parent_theme'] = array(
							'level' => 'error',
							'dismissible' => false,
							'description' => '<i class="fa-solid fa-fw fa-baby-carriage"></i> A child theme is active, but its parent theme directory <strong>' . $active_theme->get('Template') . '</strong> does not exist!<br/><em>The "Template:" setting in the <a href="' . get_stylesheet_uri() . '" target="_blank" rel="noopener">style.css</a> file of the child theme must match the directory name (above) of the parent theme.</em>'
						);
					}

					//Check if child theme is missing img meta files
					if ( is_dir(get_stylesheet_directory() . '/assets/img/meta') && file_exists(get_stylesheet_directory() . '/assets/img/meta/favicon.ico') ){
						//Check to ensure that child theme meta graphics are not identical to Nebula parent theme meta graphics
						foreach( glob(get_stylesheet_directory() . '/assets/img/meta/*.*') as $child_theme_meta_image ){
							$parent_theme_meta_image = str_replace(get_stylesheet_directory(), get_template_directory(), $child_theme_meta_image);

							//Check if the images are the same
							if ( file_exists($child_theme_meta_image) && file_exists($parent_theme_meta_image) && md5_file($child_theme_meta_image) === md5_file($parent_theme_meta_image) ){ //Compare the two files to see if they are identical
								$child_filename = str_replace(get_stylesheet_directory(), '', $child_theme_meta_image);

								$nebula_warnings['child_meta_graphics'] = array(
									'level' => 'error',
									'dismissible' => true,
									'description' => '<i class="fa-solid fa-fw fa-images"></i> Child theme meta graphics exist, but are identical to the Nebula meta graphics (' . $child_filename . '). Ensure that child theme meta graphics are unique to this website!</em>'
								);

								break; //Exit the loop as soon as we find a match
							}
						}
					} else {
						$nebula_warnings['child_meta_graphics'] = array(
							'level' => 'error',
							'dismissible' => true,
							'description' => '<i class="fa-regular fa-fw fa-images"></i> A child theme is active, but missing meta graphics. Create a <code>/assets/img/meta/</code> directory in the child theme (or copy it over from the Nebula parent theme).</em>'
						);
					}
				}

				//Check if Relevanssi has built an index for search
				if ( is_plugin_active('relevanssi/relevanssi.php') ){
					if ( !get_option('relevanssi_indexed') ){
						$nebula_warnings['relevanssi_index'] = array(
							'level' => 'error',
							'dismissible' => true,
							'description' => '<i class="fa-solid fa-fw fa-magnifying-glass-plus"></i> <a href="options-general.php?page=relevanssi%2Frelevanssi.php&tab=indexing">Relevanssi</a> must build an index to search the site. This must be triggered manually.',
							'url' => admin_url('options-general.php?page=relevanssi%2Frelevanssi.php&tab=indexing')
						);
					}

					if ( get_option('relevanssi_index_fields') === 'none' ){
						$nebula_warnings['relevanssi_custom_fields'] = array(
							'level' => 'warning',
							'dismissible' => true,
							'description' => '<i class="fa-solid fa-fw fa-magnifying-glass-plus"></i> <a href="options-general.php?page=relevanssi%2Frelevanssi.php&tab=indexing">Relevanssi</a> is not set to search custom fields.',
							'url' => admin_url('options-general.php?page=relevanssi%2Frelevanssi.php&tab=indexing')
						);
					}
				}

				//Service Worker checks
				if ( $this->get_option('service_worker') ){
					//Check for Service Worker JavaScript file when using Service Worker
					if ( !file_exists($this->sw_location(false)) ){
						$nebula_warnings['sw_missing'] = array(
							'level' => 'error',
							'dismissible' => true,
							'description' => '<i class="fa-regular fa-fw fa-file"></i> Service Worker is enabled in <a href="themes.php?page=nebula_options&tab=functions&option=service_worker">Nebula Options</a>, but no Service Worker JavaScript file was found. Either use the <a href="https://github.com/chrisblakley/Nebula/blob/main/Nebula-Child/resources/sw.js" target="_blank">provided sw.js file</a> (by moving it to the root directory), or override the function <a href="https://nebula.gearside.com/functions/sw_location/?utm_campaign=documentation&utm_medium=admin+notice&utm_source=service+worker#override" target="_blank">sw_location()</a> to locate the actual JavaScript file you are using.'
						);
					}

					//Check for /offline page when using Service Worker
					if ( $this->is_warning_level('verbose') ){
						$offline_page = get_page_by_path('offline');
						if ( is_null($offline_page) ){
							$nebula_warnings['sw_offline'] = array(
								'level' => 'warning',
								'dismissible' => true,
								'description' => '<i class="fa-solid fa-fw fa-ethernet"></i> It is recommended to make an Offline page when using Service Worker. <a href="post-new.php?post_type=page">Manually add one</a>'
							);
						}
					}

					//Check for SSL when using Service Worker
					if ( !is_ssl() ){
						$nebula_warnings['sw_ssl'] = array(
							'level' => 'warning',
							'dismissible' => true,
							'description' => '<i class="fa-solid fa-fw fa-lock-open"></i> Service Worker requires an SSL. Either update the site to https or <a href="themes.php?page=nebula_options&tab=functions&option=service_worker">disable Service Worker</a>.'
						);
					}
				}

				//Check for "Just Another WordPress Blog" tagline
				if ( strtolower(get_bloginfo('description')) === 'just another wordpress site' ){
					$nebula_warnings['default_tagline'] = array(
						'level' => 'warning',
						'dismissible' => true,
						'description' => '<a href="options-general.php">Site Tagline</a> is still "Just Another WordPress Site"!',
						'url' => admin_url('options-general.php')
					);
				}

				//Ensure a privacy policy is set with WordPress core
				if ( empty(get_privacy_policy_url()) ){
					$nebula_warnings['missing_privacy_policy'] = array(
						'level' => 'warning',
						'dismissible' => true,
						'description' => '<i class="fa-solid fa-fw fa-file-alt"></i> <a href="options-privacy.php">Privacy policy</a> is not setup with WordPress.',
						'url' => admin_url('options-privacy.php')
					);
				} else {
					//Ensure a privacy policy is set with WordPress core
					if ( get_privacy_policy_url() == get_home_url() ){
						$nebula_warnings['privacy_policy_is_frontpage'] = array(
							'level' => 'warning',
							'dismissible' => true,
							'description' => '<i class="fa-solid fa-fw fa-file-alt"></i> <a href="options-privacy.php">Privacy policy</a> cannot be the Front Page.',
							'url' => admin_url('options-privacy.php')
						);
					}
				}

				//Check if all Sass files were processed
				if ( !empty($this->sass_process_status) ){
					$nebula_warnings['sass_status'] = array(
						'level' => 'log',
						'dismissible' => true,
						'description' => '<i class="fa-brands fa-fw fa-sass"></i> ' . $this->sass_process_status
					);
				}

				$all_nebula_warnings = apply_filters('nebula_warnings', $nebula_warnings); //Allow other functions to hook in to add warnings (like Ecommerce)

				//Check for improper hooks
				if ( is_null($all_nebula_warnings) ){
					$all_nebula_warnings = array(array(
						'level' => 'error',
						'dismissible' => true,
						'description' => '<i class="fa-solid fa-fw fa-skull"></i> <code>$nebula_warnings</code> array is null. When hooking into the <code>nebula_warnings</code> filter be sure that it is returned too!'
					));
				}

				//Sort by warning level
				if ( !empty($all_nebula_warnings) ){
					usort($all_nebula_warnings, function($itemA, $itemB){
						$priorities = array('error', 'warning', 'log');
						$a = array_search($itemA['level'], $priorities);
						$b = array_search($itemB['level'], $priorities);

						if ( $a === $b ){
							return 0;
						}

						return ( $a < $b )? -1 : 1;
					});
				}

				wp_cache_set('nebula_warnings', $all_nebula_warnings); //Store in object cache
				$this->timer('Check Warnings', 'end');
				return $all_nebula_warnings;
			}

			return array(); //Return empty array instead of false
		}

		//Add more advanced and resource-intensive warnings to the Nebula check when requested (either via Audit Mode or enabled in Nebula Options)
		public function advanced_warnings($nebula_warnings){
			$this->timer('Advanced Warnings');

			//Only check these when auditing (not on all pageviews) to prevent undesired server load
			if ( $this->is_auditing() || $this->is_warning_level('strict') ){
				//Check contact email address
				if ( !$this->get_option('contact_email') ){
					$default_contact_email = get_option('admin_email', $this->get_user_info('user_email', array('id' => 1)));
					$email_domain = substr($default_contact_email, strpos($default_contact_email, "@")+1);
					if ( $email_domain !== $this->url_components('domain') ){
						$nebula_warnings['default_email_domain'] = array(
							'level' => 'warning',
							'dismissible' => true,
							'description' => '<i class="fa-solid fa-fw fa-address-card"></i> <a href="themes.php?page=nebula_options&tab=metadata&option=contact_email">Default contact email domain</a> (<code>' . $email_domain . '</code>) does not match website (<code>' . $this->url_components('domain') . '</code>). This email address will appear in metadata, so please verify this is acceptable.',
							'url' => admin_url('themes.php?page=nebula_options&tab=metadata&option=contact_email')
						);
					}
				}

				//Check if readme.html exists and attempt to delete it if so
				if ( file_exists(ABSPATH . '/readme.html') ){ //WP 5.6+ adds a random string to the readme filename, so this will eventually no longer be needed
					$description = 'should be deleted.';
					if ( @unlink(ABSPATH . '/readme.html') ){ //Try to delete the file (ignoring errors)
						$description = 'has been successfully deleted.';
					}

					$nebula_warnings['wp_readme'] = array(
						'level' => 'warning',
						'dismissible' => true,
						'description' => '<i class="fa-regular fa-fw fa-file-alt"></i> The WordPress core <a href="' . home_url('/') . 'readme.html">readme.html</a> file exists (which exposes version information) and ' . $description,
						'url' => home_url('/') . 'readme.html'
					);
				}

				//Check if max upload size is different from max post size
				if ( ini_get('upload_max_filesize') !== ini_get('post_max_size') ){
					$nebula_warnings['max_upload'] = array(
						'level' => 'warning',
						'dismissible' => true,
						'description' => '<i class="fa-regular fa-fw fa-file-alt"></i> The <code>post_max_size</code> (' . ini_get('post_max_size') . ') is different from the <code>upload_max_filesize</code> (' . ini_get('upload_max_filesize') . ').',
					);
				}

				//Check for low disk space available in key directories
				$disk_paths = array(
					array('directory' => ABSPATH, 'low' => 10, 'critical' => 5), //WordPress root directory
					//array('directory' => session_save_path(), 'low' => 1, 'critical' => 0.5), //May or may not be the same as /tmp
					array('directory' => '/tmp', 'low' => 1, 'critical' => 0.5),
					array('directory' => get_temp_dir(), 'low' => 1, 'critical' => 0.5) //May or may not be the same as /tmp
				);

				$disk_paths = array_unique($disk_paths, SORT_REGULAR); //De-duplicate directories in the array

				foreach ( $disk_paths as $path ){
					if ( file_exists($path['directory']) ){
						$disk_space_free = disk_free_space($path['directory'])/GB_IN_BYTES; //In GB

						if ( $disk_space_free < $path['critical'] ){
							$nebula_warnings['disk_space_low'] = array(
								'level' => 'error',
								'dismissible' => true,
								'description' => '<i class="fa-solid fa-fw fa-hdd"></i> Available disk space in <strong>' . $path['directory'] . '</strong> critically low! Only <strong>' . round($disk_space_free, 2) . 'gb</strong> remaining.'
							);
						} elseif ( $disk_space_free < $path['low'] ){
							$nebula_warnings['disk_space_low'] = array(
								'level' => 'warning',
								'dismissible' => true,
								'description' => '<i class="fa-regular fa-fw fa-hdd"></i> Low disk space available in <strong>' . $path['directory'] . '</strong>. Only <strong>' . round($disk_space_free, 2) . 'gb</strong> remaining.'
							);
						}
					}
				}

				//Front-end (non-admin) page warnings only
				if ( !$this->is_admin_page() ){
					//Check each image within the_content()
					//If CMYK: https://stackoverflow.com/questions/8646924/how-can-i-check-if-a-given-image-is-cmyk-in-php
					//If not Progressive JPEG
					//If Quality setting is over 80%: https://stackoverflow.com/questions/2024947/is-it-possible-to-tell-the-quality-level-of-a-jpeg
					if ( 1==2 ){ //if post or page or custom post type? maybe not- just catch everything
						$post = get_post(get_the_ID());
						preg_match_all('/src="([^"]*)"/', $post->post_content, $matches); //Find images in the content... This wont work: I need the image path not url

						foreach ( $matches as $image_url ){
							//Check CMYK
							$image_info = getimagesize($image_url);
							if ( $image_info['channels'] == 4 ){
								echo 'it is cmyk<br><br>'; //ADD WARNING HERE
							} else {
								echo 'it is rgb<br><br>';
							}
						}
					}

					//Check within all child theme files for various issues
					foreach ( $this->glob_r(get_stylesheet_directory() . '/*') as $filepath ){
						if ( is_file($filepath) ){
							$skip_filenames = array('README.md', 'debug_log', 'error_log', '/vendor', 'resources/');
							if ( !$this->contains($filepath, $this->skip_extensions()) && !$this->contains($filepath, $skip_filenames) ){ //If the filename does not contain something we are ignoring
								//Prep an array of strings to look for
								if ( substr(basename($filepath), -3) == '.js' ){ //JavaScript files
									$looking_for['debug_output'] = "/console\./i";
								} elseif ( substr(basename($filepath), -4) == '.php' ){ //PHP files
									$looking_for['debug_output'] = "/var_dump\(|var_export\(|print_r\(/i";
								} elseif ( substr(basename($filepath), -5) == '.scss' ){ //Sass files
									continue; //Remove this to allow checking scss files
									$looking_for['debug_output'] = "/@debug/i";
								} else {
									continue; //Skip any other filetype
								}

								$looking_for = apply_filters('nebula_warnings_bad_file_content_patterns', $looking_for); //Allow the child theme (or plugins) to add patterns to look for

								//Search the file and output if found anything
								if ( !empty($looking_for) ){
									foreach ( file($filepath) as $line_number => $full_line ){ //Loop through each line of the file
										foreach ( $looking_for as $category => $regex ){ //Search through each string we are looking for from above
											if ( preg_match("/^\/\/|\/\*|#/", trim($full_line)) == true ){ //Skip lines that begin with a comment
												continue;
											}

											preg_match($regex, $full_line, $details); //Actually Look for the regex in the line

											if ( !empty($details) ){
												if ( $category === 'debug_output' ){
													$nebula_warnings['unintentional_output'] = array(
														'level' => 'warning',
														'dismissible' => true,
														'description' => '<i class="fa-solid fa-fw fa-bug"></i> Possible debug output in <strong>' . str_replace(get_stylesheet_directory(), '', dirname($filepath)) . '/' . basename($filepath) . '</strong> on <strong>line ' . ($line_number+1) . '</strong>.'
													);
												} elseif ( $category === 'custom' ){
													$nebula_warnings['unintentional_output'] = array(
														'level' => 'warning',
														'dismissible' => true,
														'description' => '<i class="fa-solid fa-fw fa-bug"></i> Possible unintentional output detected in <strong>' . str_replace(get_stylesheet_directory(), '', dirname($filepath)) . '/' . basename($filepath) . '</strong> on <strong>line ' . ($line_number+1) . '</strong>.'
													);
												}
											}
										}
									}
								}
							}
						}
					}

					//Only check these when actually auditing (not when only advanced warnings are enabled)
					if ( $this->is_auditing() ){
						//Check word count for SEO
						$word_count = $this->word_count();
						if ( $word_count < 1900 ){
							$word_count_warning = ( $word_count === 0 )? 'Word count audit is not looking for custom fields outside of the main content editor. <a href="https://nebula.gearside.com/functions/word_count/?utm_campaign=nebula&utm_medium=nebula&utm_source=' . urlencode(get_bloginfo('name')) . '&utm_content=word+count+audit+warning" target="_blank">Hook custom fields into the Nebula word count functionality</a> to properly audit.' : 'Word count (' . $word_count . ') is low for SEO purposes (Over 1,000 is good, but 1,900+ is ideal). <small>Note: Detected word count may not include custom fields!</small>';
							$nebula_warnings['word_count'] = array(
								'level' => 'warning',
								'dismissible' => true,
								'description' => '<i class="fa-regular fa-fw fa-file"></i> ' . $word_count_warning,
								'url' => get_edit_post_link(get_the_id()),
								'meta' => array('target' => '_blank', 'rel' => 'noopener')
							);
						}
					}
				}

				//Check for Yoast or The SEO Framework plugins
				if ( !is_plugin_active('wordpress-seo/wp-seo.php') && !is_plugin_active('autodescription/autodescription.php') ){
					$nebula_warnings['seo_plugin_missing'] = array(
						'level' => 'warning',
						'dismissible' => true,
						'description' => '<i class="fa-solid fa-fw fa-magnifying-glass-plus"></i> A recommended SEO plugin is not active.',
						'url' => admin_url('themes.php?page=tgmpa-install-plugins')
					);
				}
			}

			$nebula_warnings = apply_filters('nebula_advanced_warnings', $nebula_warnings);

			$this->timer('Advanced Warnings', 'end');
			return $nebula_warnings;
		}

		//Audit Output
		public function advanced_warning_output(){
			if ( $this->is_auditing() ){
				//Log when manually auditing pages individually
				if ( isset($this->super->get['audit']) ){
					$this->add_log('Nebula audit performed on ' . $this->requested_url(), 1);
				}

				$nebula_warnings = wp_json_encode($this->warnings);
				?>
					<style>
						::spelling-error {text-decoration: wavy red;} /* Coming in Chrome 121 //@todo "Nebula" 0: Does this require contenteditable and/or spellcheck="true"? If so, add it via JS */
						::grammar-error {text-decoration: wavy green;}

						.nebula-audit .audit-desc {position: absolute; bottom: 0; right: 0; color: #fff; background: grey; font-size: 10px; padding: 3px 5px; z-index: 9999;}
							.nebula-audit .nebula-audit .audit-desc {right: auto; left: 0; top: 0; bottom: auto;}
								.nebula-audit .nebula-audit .nebula-audit .audit-desc {right: auto; left: 0; bottom: 0; top: auto;}
									.nebula-audit .nebula-audit .nebula-audit .nebula-audit .audit-desc {right: 0; left: auto; bottom: auto; top: 0;}
						.audit-error {position: relative; border: 2px solid #dc3545;}
							.audit-error .audit-desc {background: #dc3545;}
						.audit-warn {position: relative; border: 2px solid #ffc107;}
							.audit-warn .audit-desc {background: #ffc107;}
						.audit-notice {position: relative; border: 2px solid #17a2b8;}
							.audit-notice .audit-desc {background: #17a2b8;}
						#audit-results {position: relative; background: #444; color: #fff; padding: 50px;}
							#audit-results p,
							#audit-results li {color: #fff;}
							#audit-results a {color: #0098d7;}
								#audit-results a:hover {color: #95d600;}
					</style>
					<script>
						jQuery(window).on('load', function(){
							setTimeout(function(){
								console.log('[Nebula Audit] Performing Nebula Audit...');

								new Promise(async (resolve) => {
									const fetchPromises = []; //Start an array that all fetches will use so we know when they are all completed

									jQuery('body').append(jQuery('<div id="audit-results"><p><strong>Nebula Audit Results:</strong></p><ul></ul></div>'));

									var entireDOM = jQuery('html').clone(); //Duplicate the entire HTML to run audits against
									entireDOM.find('#query-monitor-main, #qm, #wpadminbar, script, #audit-results').remove(); //Remove elements to ignore (must ignore scripts so this audit doesn't find itself)

									//Reporting Observer deprecations and interventions
									if ( 'ReportingObserver' in window ){ //Chrome 68+
										var nebulaAuditModeReportingObserver = new ReportingObserver(function(reports, observer){
											for ( let report of reports ){
												if ( report.body.sourceFile.includes('extension') ){ //Ignore browser extensions
													jQuery('#audit-results ul').append('<li>Reporting Observer (' + report.type + '): ' + report.body.message + ' in ' + report.body.sourceFile + ' on line ' + report.body.lineNumber + '</li>');
												}
											}
										}, {buffered: true});
										nebulaAuditModeReportingObserver.observe();
									}

									//Monitor Cumulative Layout Shift (CLS) with the Layout Instability API
									if ( 'PerformanceObserver' in window ){
										let auditedCls = 0;
										new PerformanceObserver(function(list){
											for ( let entry of list.getEntries() ){
												if ( !entry.hadRecentInput ){
													auditedCls += entry.value;

													for ( let source of entry.sources ){
														if ( source.node?.parentElement ){
															if ( entry.value > 0.001 && !jQuery(source.node.parentElement).parents('#wpadminbar').length && !jQuery(source.node.parentElement).parents('#audit-results').length ){
																var clsLevel = 'notice';
																if ( entry.value > 0.01 ){
																	clsLevel = 'warn';
																}
																if ( entry.value > 0.1 ){
																	clsLevel = 'error';
																}

																jQuery(source.node.parentElement).removeClass('audit-notice audit-warn').addClass('nebula-audit audit-' + clsLevel).append(jQuery('<div class="audit-desc">Layout Shift (' + entry.value.toFixed(3) + ')</div>'));
															}
														}
													}
												}
											}

											//Log the total if it is less than nominal
											if ( auditedCls > 0.1 ){ //Anything over 0.1 needs improvement
												jQuery('#audit-results ul li.significant-cls-warning').remove(); //Remove the previous bullets to only show the latest one
												jQuery('#audit-results ul').append('<li class="significant-cls-warning"><i class="fa-solid fa-fw fa-expand-arrows-alt"></i> Significant Cumulative Layout Shift (CLS): ' + auditedCls + '</li>');
											}
										}).observe({type: 'layout-shift', buffered: true});
									}

									//Check protocol
									if ( window.location.href.includes('http://') ){
										jQuery("#audit-results ul").append('<li><i class="fa-solid fa-fw fa-unlock-alt"></i> Non-secure http protocol</li>');
									} else if ( window.location.href.includes('https://') ){
										//check for non-secure resource requests here?
									}

									//Empty meta description
									if ( !entireDOM.find('meta[name="description"]').length ){
										jQuery("#audit-results ul").append('<li><i class="fa-solid fa-fw fa-align-left"></i> Missing meta description</li>');
									} else {
										if ( !entireDOM.find('meta[name="description"]').attr('content').length ){
											jQuery("#audit-results ul").append('<li><i class="fa-solid fa-fw fa-align-left"></i> Meta description tag exists but is empty</li>');
										} else {
											if ( entireDOM.find('meta[name="description"]').attr('content').length < 60 ){
												jQuery("#audit-results ul").append('<li><i class="fa-solid fa-fw fa-align-left"></i> Short meta description</li>');
											}
										}
									}

									//Check title
									if ( !document.title.length ){
										jQuery("#audit-results ul").append('<li><i class="fa-solid fa-fw fa-heading"></i> Missing page title</li>');
									} else {
										if ( document.title.length < 25 ){
											jQuery("#audit-results ul").append('<li><i class="fa-solid fa-fw fa-heading"></i> Short page title</li>');
										}

										if ( document.title.includes('Home') ){
											jQuery("#audit-results ul").append('<li><i class="fa-solid fa-fw fa-heading"></i> Improve page title keywords (remove "Home")</li>');
										}
									}

									//Check H1
									if ( !entireDOM.find('h1').length ){
										jQuery("#audit-results ul").append('<li><i class="fa-solid fa-fw fa-heading"></i> Missing H1 tag</li>');

										if ( entireDOM.find('h1').length > 1 ){
											jQuery("#audit-results ul").append('<li><i class="fa-solid fa-fw fa-heading"></i> Too many H1 tags</li>');
										}
									}

									//Check H2
									if ( !entireDOM.find('h2').length ){
										jQuery("#audit-results ul").append('<li><i class="fa-solid fa-fw fa-heading"></i> Missing H2 tags</li>');
									} else if ( entireDOM.find('h2').length <= 2 ){
										jQuery("#audit-results ul").append('<li><i class="fa-solid fa-fw fa-heading"></i> Very few H2 tags</li>');
									}

									//Check that each <article> and <section> has a heading tag
									//https://www.w3.org/wiki/HTML/Usage/Headings/Missing
									entireDOM.find('article, section').each(function(){
										if ( !jQuery(this).find('h1, h2, h3, h4, h5, h6').length ){
											jQuery(this).addClass('nebula-audit audit-warn').append(jQuery('<div class="audit-desc"><i class="fa-solid fa-fw fa-heading"></i> Missing heading tag in this ' + jQuery(this).prop('tagName').toLowerCase() + '</div>'));
											jQuery('#audit-results ul').append('<li><i class="fa-solid fa-fw fa-heading"></i> Missing heading tag within a &lt;' + jQuery(this).prop('tagName').toLowerCase() + '&gt; tag.</li>');
										}
									});

									//Check for a #content-section (or whatever the target is) if Skip to Content button exists
									if ( jQuery('#skip-to-content-link').length ){
										var skipToContentTarget = jQuery('#skip-to-content-link').attr('href');

										if ( skipToContentTarget && !jQuery(skipToContentTarget).length ){
											jQuery("#audit-results ul").append('<li><i class="fa-solid fa-fw fa-link"></i> Skip to Content link target (' + skipToContentTarget + ') does not exist.</li>');
										}
									}

									//Check for placeholder text (in the page content and metadata)
									var commonPlaceholderWords = ['lorem', 'ipsum', 'dolor', 'sit amet', 'consectetur', 'adipiscing', 'malesuada', 'vestibulum']; //Be careful of false positives due to parts of real words (Ex: "amet" in "parameter")
									jQuery.each(commonPlaceholderWords, function(i, word){
										if ( entireDOM.html().includes(word) ){
											jQuery('#audit-results ul').append('<li><i class="fa-solid fa-fw fa-remove-format"></i> Placeholder text found ("' + word + '").</li>');
											return false;
										}
									});

									//Broken images
									jQuery('img').on('error', function(){
										if ( jQuery(this).parents('#wpadminbar').length ){
											return false;
										}

										jQuery(this).addClass('nebula-audit audit-error').append(jQuery('<div class="audit-desc"><i class="fa-regular fa-fw fa-image"></i> Broken image</div>'));
										jQuery('#audit-results ul').append('<li><i class="fa-regular fa-fw fa-image"></i> Broken image</li>');
									});

									//Images
									entireDOM.find('img').each(function(){
										if ( jQuery(this).parents('#wpadminbar, iframe, #map_canvas').length ){
											return false;
										}

										let $oThis = jQuery(this);
										let src = $oThis.attr('src');

										//Check img alt
										if ( !$oThis.is('[alt]') ){
											$oThis.wrap('<div class="nebula-audit audit-error"></div>').after('<div class="audit-desc"><i class="fa-regular fa-fw fa-image"></i> Missing ALT attribute</div>');
											jQuery('#audit-results ul').append('<li><i class="fa-regular fa-fw fa-image"></i> Missing ALT attribute <small>(' + src + ')</small></li>');
										}

										//Check lazy loading attribute
										if ( !$oThis.is('[loading]') ){
											$oThis.wrap('<div class="nebula-audit audit-error"></div>').after('<div class="audit-desc"><i class="fa-regular fa-fw fa-image"></i> Image not lazy loaded</div>');
											jQuery('#audit-results ul').append('<li><i class="fa-regular fa-fw fa-image"></i> Image not lazy loaded <small>(' + src + ')</small></li>');
										}

										//Check image filesize via performance. Note: cached files are 0
										var iTime = performance.getEntriesByName($oThis.attr('src'))[0];
										if ( iTime && iTime.transferSize >= 500_000 ){
											$oThis.wrap('<div class="nebula-audit audit-warn"></div>').after('<div class="audit-desc"><i class="fa-solid fa-fw fa-image"></i> Image filesize over 500kb</div>');
											jQuery('#audit-results ul').append('<li><i class="fa-solid fa-fw fa-image"></i> (Via Performance Object) Image filesize over 500kb <small>(' + src + ')</small></li>');
										}

										//Check image file size via Fetch/Blob
										fetchPromises.push(fetch(src).then(function(response){
											return response.blob();
										}).then(function(blob){
											//Check file size
											if ( blob.size >= 500_000 ){
												$oThis.wrap('<div class="nebula-audit audit-warn"></div>').after('<div class="audit-desc"><i class="fa-solid fa-fw fa-image"></i> Image filesize over 500kb</div>');
												jQuery('#audit-results ul').append('<li><i class="fa-solid fa-fw fa-image"></i> (Via Fetch/Blob) Image filesize over 500kb <small>(' + src + ')</small></li>');
											}

											//Check for PNG files
											if ( blob.type = 'image/png' ){
												$oThis.wrap('<div class="nebula-audit audit-warn"></div>').after('<div class="audit-desc"><i class="fa-solid fa-fw fa-image"></i> PNG Image</div>');
												jQuery('#audit-results ul').append('<li><i class="fa-solid fa-fw fa-image"></i> PNG image used. Consider modern alternatives. <small>(' + src + ')</small></li>');
											}
										}));

										//Check image width
										if ( jQuery(this)[0].naturalWidth > 1200 ){
											jQuery(this).wrap('<div class="nebula-audit audit-warn"></div>').after('<div class="audit-desc"><i class="fa-solid fa-fw fa-image"></i> Image wider than 1200px</div>');
											jQuery('#audit-results ul').append('<li><i class="fa-solid fa-fw fa-image"></i> Image wider than 1200px</li>');
										}

										//Check image link
										if ( !jQuery(this).parents('a').length ){
											jQuery(this).wrap('<div class="nebula-audit audit-notice"></div>').after('<div class="audit-desc"><i class="fa-solid fa-fw fa-unlink"></i> Unlinked Image</div>');
											jQuery('#audit-results ul').append('<li><i class="fa-solid fa-fw fa-image"></i> Unlinked image</li>');
										}
									});

									//Videos
									entireDOM.find('video').each(function(){
										let $oThis = jQuery(this);
										let src = $oThis.attr('src');

										//Check lazy loading attribute
										if ( !$oThis.is('[loading]') ){
											$oThis.wrap('<div class="nebula-audit audit-error"></div>').after('<div class="audit-desc"><i class="fa-regular fa-fw fa-file-video"></i> Video not lazy loaded</div>');
											jQuery('#audit-results ul').append('<li><i class="fa-regular fa-fw fa-file-video"></i> Video not lazy loaded <small>(' + src + ')</small></li>');
										}

										//Check video filesize. Note: cached files are 0
										if ( window.performance ){
											var vTime = performance.getEntriesByName(jQuery(this).find('source').attr('src'))[0];
											if ( vTime && vTime.transferSize >= 5_000_000 ){ //5mb+
												jQuery(this).wrap('<div class="nebula-audit audit-warn"></div>').after('<div class="audit-desc"><i class="fa-solid fa-fw fa-file-video"></i> Video filesize over 5mb</div>');
												jQuery('#audit-results ul').append('<li><i class="fa-solid fa-fw fa-file-video"></i> (Via Performance Object) Video filesize over 5mb</li>');
											}
										}

										//Check video file size via Fetch/Blob
										fetchPromises.push(fetch(src).then(function(response){
											return response.blob();
										}).then(function(blob){
											//Check file size
											if ( blob.size >= 5_000_000 ){ //5mb+
												$oThis.wrap('<div class="nebula-audit audit-warn"></div>').after('<div class="audit-desc"><i class="fa-solid fa-fw fa-file-video"></i> Video filesize over 5mb</div>');
												jQuery('#audit-results ul').append('<li><i class="fa-solid fa-fw fa-file-video"></i> (Via Video Detection) Video filesize over 5mb <small>(' + src + ')</small></li>');
											}
										}));

										//Check unmuted autoplay
										if ( jQuery(this).is('[autoplay]') && !jQuery(this).is('[muted]') ){
											jQuery(this).wrap('<div class="nebula-audit audit-warn"></div>').after('<div class="audit-desc"><i class="fa-solid fa-fw fa-video"></i> Autoplay without muted attribute</div>');
											jQuery('#audit-results ul').append('<li><i class="fa-solid fa-fw fa-video"></i> Videos set to autoplay without being muted will not autoplay in Chrome.</li>');
										}
									});

									//Check Form Fields
									entireDOM.find('form').each(function(){
										if ( jQuery(this).find('input[name=s]').length ){
											return false;
										}

										if ( jQuery(this).parents('#wpadminbar, iframe').length ){
											return false;
										}

										var formFieldCount = 0;
										jQuery(this).find('input:visible, textarea:visible, select:visible').each(function(){
											formFieldCount++;
										});

										if ( formFieldCount > 6 ){
											jQuery(this).wrap('<div class="nebula-audit audit-notice"></div>').after('<div class="audit-desc"><i class="fa-solid fa-fw fa-pencil-alt"></i> Many form fields</div>');
											jQuery('#audit-results ul').append('<li><i class="fa-solid fa-fw fa-pencil-alt"></i> Many form fields</li>');
										}
									});

									<?php do_action('nebula_audits_js'); ?>

									await Promise.all(fetchPromises);
									resolve(); //Resolve the promise to move into the next part below
								}).then(() => {
									var nebulaWarnings = <?php echo $nebula_warnings; ?> || {};
									jQuery.each(nebulaWarnings, function(i, warning){
										if ( warning.description.indexOf('Audit Mode') > 0 ){
											return true; //Skip
										}
										jQuery('#audit-results ul').append('<li>' + warning.description + '</li>');
									});

									<?php if ( !(is_home() || is_front_page()) ): ?>
										//Check breadcrumb schema tag
										if ( !jQuery('[itemtype*=BreadcrumbList]').length ){
											jQuery('#audit-results ul').append('<li><i class="fa-solid fa-bread-slice"></i> Missing breadcrumb schema tag</li>');
										}
									<?php endif; ?>

									//Check issue count (do this last)
									if ( jQuery('#audit-results ul li').length <= 0 ){
										jQuery('#audit-results').append('<p><strong><i class="fa-solid fa-fw fa-check"></i> No issues were found on this page.</strong> Be sure to check other pages (and run <a href="https://nebula.gearside.com/get-started/checklists/testing-checklist/" target="_blank">more authoritative tests</a>)!</p>');
									} else {
										//Output each result to the console as well
										console.log('[Nebula Audit] Issues Found:', jQuery('#audit-results ul li').length);
										jQuery('#audit-results ul li').each(function(){
											console.log('[Nebula Audit] ' + jQuery(this).text());
										});

										jQuery('#audit-results').append('<p><strong><i class="fa-solid fa-fw fa-times"></i> Found issues: ' + jQuery('#audit-results ul li').length + '<strong></p>');
									}
									jQuery('#audit-results').append('<p><small>Note: This does not check for @todo comments. Use the Nebula To-Do Manager in the WordPress admin dashboard to view.</small></p>');

									});












							}, 1);
						});
					</script>
				<?php
			}
		}
	}
}