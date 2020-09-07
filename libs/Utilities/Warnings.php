<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Warnings') ){
	trait Warnings {
		public function hooks(){
			$this->warnings = false;

			add_action('wp_head', array($this, 'console_warnings'));
			add_filter('nebula_warnings', array($this, 'advanced_warnings'));
			add_action('wp_footer', array($this, 'advanced_warning_output'), 9999); //Late execution as possible
		}

		//Determine the desired warning level
		public function is_warning_level($needed, $actual=false){
			$actual = ( !empty($actual) )? $actual : $this->get_option('warnings');

			if ( $actual === 'off' ){
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
				if ( !empty($warnings) ){
					echo '<script>';
					foreach( $warnings as $warning ){
						$category = ( !empty($warning['category']) )? $warning['category'] : 'Nebula';
						echo 'console.' . esc_html($warning['level']) . '("[' . esc_html($category) . '] ' . esc_html(addslashes(strip_tags($warning['description']))) . '");';
					}
					echo '</script>';
				}
			}
		}

		//Check for Nebula warnings
		public function check_warnings(){
			$this->timer('Check Warnings');

			if ( $this->is_auditing() || $this->is_warning_level('on') ){
				//Check object cache first
				$nebula_warnings = wp_cache_get('nebula_warnings');

				if ( is_array($nebula_warnings) || !empty($nebula_warnings) ){ //If it is an array (meaning it has run before but did not find anything) or if it is false
					return $nebula_warnings;
				}

				$nebula_warnings = array();

				//Admin warnings only
				if ( $this->is_admin_page() ){
					//Check page slug against taxonomy terms.
					global $pagenow;
					if ( $pagenow === 'post.php' || $pagenow === 'edit.php' ){
						global $post;

						if ( !empty($post) ){ //If the listing has results
							foreach ( get_taxonomies() as $taxonomy ){ //Loop through all taxonomies
								foreach ( get_terms($taxonomy, array('hide_empty' => false)) as $term ){ //Loop through all terms within each taxonomy
									if ( $term->slug === $post->post_name ){
										$nebula_warnings[] = array(
											'level' => 'error',
											'description' => '<i class="fas fa-fw fa-link"></i> Slug conflict with ' . ucwords(str_replace('_', ' ', $taxonomy)) . ': <strong>' . $term->slug . '</strong> - Consider changing this page slug.'
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
								$nebula_warnings[] = array(
									'level' => 'error',
									'description' => '<i class="fas fa-fw fa-server"></i> File system permissions error. Consider changing the FS_METHOD in wp-config.php.',
								);
							} else {
								set_transient('nebula_fs_method', true, YEAR_IN_SECONDS); //On success, set a transient. This transient never needs to expire (but it's fine if it does).
							}
						}
					}
				}

				//If the site is served via HTTPS but the Site URL is still set to HTTP
				if ( (is_ssl() || isset($_SERVER['HTTPS'])) && (strpos(home_url(), 'http://') !== false || strpos(get_option('siteurl'), 'http://') !== false) ){
					$nebula_warnings[] = array(
						'level' => 'error',
						'description' => '<i class="fas fa-fw fa-lock-open"></i> <a href="options-general.php">Website Address</a> settings are http but the site is served from https.',
						'url' => get_admin_url() . 'options-general.php'
					);
				}

				//If search indexing is disabled
				if ( get_option('blog_public') == 0 ){ //Stored as a string
					$nebula_warnings[] = array(
						'level' => 'error',
						'description' => '<i class="fab fa-fw fa-searchengin"></i> <a href="options-reading.php">Search Engine Visibility</a> is currently disabled! Some additional SEO checks were not performed.',
						'url' => get_admin_url() . 'options-reading.php'
					);
				} else {
					//Check for sitemap
					if ( is_plugin_active('wordpress-seo/wp-seo.php') ){ //Yoast
						if ( !$this->is_available(home_url('/') . 'sitemap_index.xml', false, true) ){
							$nebula_warnings[] = array(
								'level' => 'warn',
								'description' => '<i class="fas fa-fw fa-sitemap"></i> Missing sitemap XML. Yoast is enabled, but <a href="' . home_url('/') . 'sitemap_index.xml' . '" target="_blank">sitemap_index.xml</a> is unavailable.'
							);
						}
					} elseif ( is_plugin_active('autodescription/autodescription.php') ){ //The SEO Framework
						if ( !$this->is_available(home_url('/') . 'sitemap.xml', false, true) ){
							$nebula_warnings[] = array(
								'level' => 'warn',
								'description' => '<i class="fas fa-fw fa-sitemap"></i> Missing sitemap XML. The SEO Framework is enabled, but <a href="' . home_url('/') . 'sitemap.xml' . '" target="_blank">sitemap.xml</a> is unavailable.'
							);
						}
					} else {
						if ( !$this->is_available(home_url('/') . 'wp-sitemap.xml', false, true)  ){ //WordPress Core
							$nebula_warnings[] = array(
								'level' => 'warn',
								'description' => '<i class="fas fa-fw fa-sitemap"></i> Missing sitemap XML. WordPress core <a href="' . home_url('/') . 'wp-sitemap.xml' . '" target="_blank">sitemap_index.xml</a> is unavailable.'
							);

							//Check if the SimpleXML PHP module is installed on the server (required for WP core sitemap generation)
							if ( !function_exists('simplexml_load_string') ){
								$nebula_warnings[] = array(
									'level' => 'warn',
									'description' => '<i class="fas fa-fw fa-sitemap"></i> SimpleXML PHP module is not available. This is required for WordPress core sitemap generation.'
								);
							}
						}
					}

					//If not pinging additional update services (blog must be public for this to be available)
					if ( $this->is_warning_level('verbose') ){
						$ping_sites = get_option('ping_sites');
						if ( empty($ping_sites) || $ping_sites === 'http://rpc.pingomatic.com/' ){ //If it is empty or only has the default value
							$nebula_warnings[] = array(
								'level' => 'warn',
								'description' => '<i class="fas fa-fw fa-rss"></i> Additional <a href="options-writing.php">Update Services</a> should be pinged. <a href="https://codex.wordpress.org/Update_Services#XML-RPC_Ping_Services" target="_blank" rel="noopener">Recommended update services &raquo;</a>',
								'url' => get_admin_url() . 'options-writing.php'
							);
						}
					}
				}

				//Check PHP version
				$php_version_lifecycle = $this->php_version_support();
				if ( !empty($php_version_lifecycle) ){
					if ( $php_version_lifecycle['lifecycle'] === 'security' ){
						if ( $php_version_lifecycle['end']-time() < MONTH_IN_SECONDS ){ //If end of life is within 1 month
							$nebula_warnings[] = array(
								'level' => 'warn',
								'description' => '<i class="fab fa-fw fa-php"></i> PHP <strong>' . PHP_VERSION . '</strong> <a href="http://php.net/supported-versions.php" target="_blank" rel="noopener">is nearing end of life</a>. Security updates end in ' . human_time_diff($php_version_lifecycle['end']) . ' on ' . date_i18n('F j, Y', $php_version_lifecycle['end']) . '.',
								'url' => 'http://php.net/supported-versions.php',
								'meta' => array('target' => '_blank', 'rel' => 'noopener')
							);
						}
					} elseif ( $php_version_lifecycle['lifecycle'] === 'end' ){
						$nebula_warnings[] = array(
							'level' => 'error',
							'description' => '<i class="fab fa-fw fa-php"></i> PHP ' . PHP_VERSION . ' <a href="http://php.net/supported-versions.php" target="_blank" rel="noopener">no longer receives security updates</a>! End of life occurred ' . human_time_diff($php_version_lifecycle['end']) . ' ago on ' . date_i18n('F j, Y', $php_version_lifecycle['end']) . '.',
							'url' => 'http://php.net/supported-versions.php',
							'meta' => array('target' => '_blank', 'rel' => 'noopener')
						);
					}
				}

				//Check for hard Debug Mode
				if ( $this->is_warning_level('verbose') && WP_DEBUG ){
					$nebula_warnings[] = array(
						'level' => 'warn',
						'description' => '<i class="fas fa-fw fa-bug"></i> <strong>WP_DEBUG</strong> is enabled <small>(Generally defined in wp-config.php)</small>'
					);

					if ( WP_DEBUG_LOG ){
						$nebula_warnings[] = array(
							'level' => 'warn',
							'description' => '<i class="fas fa-fw fa-bug"></i> Debug logging (<strong>WP_DEBUG_LOG</strong>) to /wp-content/debug.log is enabled <small>(Generally defined in wp-config.php)</small>'
						);
					}

					if ( WP_DEBUG_DISPLAY ){
						$nebula_warnings[] = array(
							'level' => 'error',
							'description' => '<i class="fas fa-fw fa-bug"></i> Debug errors and warnings are being displayed on the front-end (<Strong>WP_DEBUG_DISPLAY</strong>) <small>(Generally defined in wp-config.php)</small>'
						);
					}
				}

				//Check for Google Analytics Tracking ID
				if ( !$this->get_option('ga_tracking_id') && !$this->get_option('gtm_id') ){
					$nebula_warnings[] = array(
						'level' => 'error',
						'description' => '<i class="fas fa-fw fa-chart-area"></i> A <a href="themes.php?page=nebula_options&tab=analytics&option=ga_tracking_id">Google Analytics tracking ID</a> or <a href="themes.php?page=nebula_options&tab=analytics&option=gtm_id">Google Tag Manager ID</a> is strongly recommended!',
						'url' => get_admin_url() . 'themes.php?page=nebula_options&tab=analytics'
					);
				}

				//If Enhanced Ecommerce Plugin is missing Google Analytics Tracking ID
				if ( is_plugin_active('enhanced-e-commerce-for-woocommerce-store/woocommerce-enhanced-ecommerce-google-analytics-integration.php') ){
					$ee_ga_settings = get_option('woocommerce_enhanced_ecommerce_google_analytics_settings');
					if ( empty($ee_ga_settings['ga_id']) ){
						$nebula_warnings[] = array(
							'level' => 'error',
							'description' => '<i class="fas fa-fw fa-chart-area"></i> <a href="admin.php?page=wc-settings&tab=integration">WooCommerce Enhanced Ecommerce</a> is missing a Google Analytics ID!',
							'url' => get_admin_url() . 'admin.php?page=wc-settings&tab=integration'
						);
					}
				}

				//Child theme checks
				if ( is_child_theme() ){
					//Check if the parent theme template is correctly referenced
					$active_theme = wp_get_theme();
					if ( !file_exists(dirname(get_stylesheet_directory()) . '/' . $active_theme->get('Template')) ){
						$nebula_warnings[] = array(
							'level' => 'error',
							'description' => '<i class="fas fa-fw fa-baby-carriage"></i> A child theme is active, but its parent theme directory <strong>' . $active_theme->get('Template') . '</strong> does not exist!<br/><em>The "Template:" setting in the <a href="' . get_stylesheet_uri() . '" target="_blank" rel="noopener">style.css</a> file of the child theme must match the directory name (above) of the parent theme.</em>'
						);
					}

					//Check if child theme is missing img meta files
					if ( is_dir(get_stylesheet_directory() . '/assets/img/meta') && file_exists(get_stylesheet_directory() . '/assets/img/meta/favicon.ico') ){
						//Check to ensure that child theme meta graphics are not identical to Nebula parent theme meta graphics
						foreach( glob(get_stylesheet_directory() . '/assets/img/meta/*.*') as $child_theme_meta_image ){
							$parent_theme_meta_image = str_replace(get_stylesheet_directory(), get_template_directory(), $child_theme_meta_image);

							//Check if the images are the same
							if ( file_exists($child_theme_meta_image) && file_exists($parent_theme_meta_image) && md5_file($child_theme_meta_image) === md5_file($parent_theme_meta_image) ){ //Compare the two files to see if they are identical
								$nebula_warnings['child_meta_graphics'] = array(
									'level' => 'error',
									'description' => '<i class="fas fa-fw fa-images"></i> Child theme meta graphics exist, but are identical to the Nebula meta graphics. Ensure that child theme meta graphics are unique to this website!</em>'
								);

								break; //Exit the loop as soon as we find a match
							}
						}
					} else {
						$nebula_warnings['child_meta_graphics'] = array(
							'level' => 'error',
							'description' => '<i class="far fa-fw fa-images"></i> A child theme is active, but missing meta graphics. Create a <code>/assets/img/meta/</code> directory in the child theme (or copy it over from the Nebula parent theme).</em>'
						);
					}
				}

				//Check if Relevanssi has built an index for search
				if ( is_plugin_active('relevanssi/relevanssi.php') && !get_option('relevanssi_indexed') ){
					$nebula_warnings[] = array(
						'level' => 'error',
						'description' => '<i class="fas fa-fw fa-search-plus"></i> <a href="options-general.php?page=relevanssi%2Frelevanssi.php&tab=indexing">Relevanssi</a> must build an index to search the site. This must be triggered manually.',
						'url' => get_admin_url() . 'options-general.php?page=relevanssi%2Frelevanssi.php&tab=indexing'
					);
				}

				//Check if Google Optimize is enabled
				if ( $this->get_option('google_optimize_id') ){
					$nebula_warnings[] = array(
						'level' => 'error',
						'description' => '<i class="far fa-fw fa-window-restore"></i> <a href="https://optimize.google.com/optimize/home/" target="_blank" rel="noopener">Google Optimize</a> is enabled (via <a href="themes.php?page=nebula_options&tab=analytics&option=google_optimize_id">Nebula Options</a>). Disable when not actively experimenting!',
						'url' => 'https://optimize.google.com/optimize/home/'
					);

					//Google Optimize requires Google Analytics
					if ( !$this->get_option('ga_tracking_id') && !$this->get_option('gtm_id') ){
						$nebula_warnings[] = array(
							'level' => 'error',
							'description' => '<i class="far fa-fw fa-window-restore"></i> <a href="themes.php?page=nebula_options&tab=analytics&option=google_optimize_id">Google Optimize ID</a> exists without a <a href="themes.php?page=nebula_options&tab=analytics&option=ga_tracking_id">Google Analytics Tracking ID</a> or <a href="themes.php?page=nebula_options&tab=analytics&option=gtm_id">GTM ID</a>.',
							'url' => get_admin_url() . 'themes.php?page=nebula_options&tab=analytics&option=ga_tracking_id'
						);
					}
				}

				//Service Worker checks
				if ( $this->get_option('service_worker') ){
					//Check for Service Worker JavaScript file when using Service Worker
					if ( !file_exists($this->sw_location(false)) ){
						$nebula_warnings[] = array(
							'level' => 'error',
							'description' => '<i class="far fa-fw fa-file"></i> Service Worker is enabled in <a href="themes.php?page=nebula_options&tab=functions&option=service_worker">Nebula Options</a>, but no Service Worker JavaScript file was found. Either use the <a href="https://github.com/chrisblakley/Nebula/blob/master/Nebula-Child/resources/sw.js" target="_blank">provided sw.js file</a> (by moving it to the root directory), or override the function <a href="https://nebula.gearside.com/functions/sw_location/?utm_campaign=documentation&utm_medium=admin+notice&utm_source=service+worker#override" target="_blank">sw_location()</a> to locate the actual JavaScript file you are using.'
						);
					}

					//Check for /offline page when using Service Worker
					if ( $this->is_warning_level('verbose') ){
						$offline_page = get_page_by_path('offline');
						if ( is_null($offline_page) ){
							$nebula_warnings[] = array(
								'level' => 'warn',
								'description' => '<i class="fas fa-fw fa-ethernet"></i> It is recommended to make an Offline page when using Service Worker. <a href="post-new.php?post_type=page">Manually add one</a>'
							);
						}
					}

					//Check for SSL when using Service Worker
					if ( !is_ssl() ){
						$nebula_warnings[] = array(
							'level' => 'warn',
							'description' => '<i class="fas fa-fw fa-lock-open"></i> Service Worker requires an SSL. Either update the site to https or <a href="themes.php?page=nebula_options&tab=functions&option=service_worker">disable Service Worker</a>.'
						);
					}
				}

				//Check for "Just Another WordPress Blog" tagline
				if ( strtolower(get_bloginfo('description')) === 'just another wordpress site' ){
					$nebula_warnings[] = array(
						'level' => 'warn',
						'description' => '<a href="options-general.php">Site Tagline</a> is still "Just Another WordPress Site"!',
						'url' => get_admin_url() . 'options-general.php'
					);
				}

				//Ensure a privacy policy is set with WordPress core
				if ( empty(get_privacy_policy_url()) ){
					$nebula_warnings[] = array(
						'level' => 'warn',
						'description' => '<i class="fas fa-fw fa-file-alt"></i> <a href="options-privacy.php">Privacy policy</a> is not setup with WordPress.',
						'url' => get_admin_url() . 'options-privacy.php'
					);
				}

				//Check if all SCSS files were processed manually.
				if ( $this->get_option('scss') && (isset($_GET['sass']) || isset($_GET['scss'])) ){
					if ( $this->is_dev() || $this->is_client() ){
						$nebula_warnings[] = array(
							'level' => 'log',
							'description' => '<i class="fab fa-fw fa-sass"></i> All Sass files have been manually processed.'
						);
					} else {
						$nebula_warnings[] = array(
							'level' => 'error',
							'description' => '<i class="fab fa-fw fa-sass"></i> You do not have permissions to manually process all Sass files.'
						);
					}
				}

				$all_nebula_warnings = apply_filters('nebula_warnings', $nebula_warnings); //Allow other functions to hook in to add warnings (like Ecommerce)

				//Check for improper hooks
				if ( is_null($all_nebula_warnings) ){
					$all_nebula_warnings = array(array(
						'level' => 'error',
						'description' => '<i class="fas fa-fw fa-skull"></i> <code>$nebula_warnings</code> array is null. When hooking into the <code>nebula_warnings</code> filter be sure that it is returned too!'
					));
				}

				//Sort by warning level
				if ( !empty($all_nebula_warnings) ){
					usort($all_nebula_warnings, function($itemA, $itemB){
						$priorities = array('error', 'warn', 'log');
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
			$this->timer('Nebula Advanced Warnings');

			//Only check these when auditing (not on all pageviews) to prevent undesired server load
			if ( $this->is_auditing() || $this->is_warning_level('strict') ){
				//Check contact email address
				if ( !$this->get_option('contact_email') ){
					$default_contact_email = get_option('admin_email', $this->get_user_info('user_email', array('id' => 1)));
					$email_domain = substr($default_contact_email, strpos($default_contact_email, "@")+1);
					if ( $email_domain !== $this->url_components('domain') ){
						$nebula_warnings[] = array(
							'level' => 'warn',
							'description' => '<i class="fas fa-fw fa-address-card"></i> <a href="themes.php?page=nebula_options&tab=metadata&option=contact_email">Default contact email domain</a> (<code>' . $email_domain . '</code>) does not match website (<code>' . $this->url_components('domain') . '</code>). This email address will appear in metadata, so please verify this is acceptable.',
							'url' => get_admin_url() . 'themes.php?page=nebula_options&tab=metadata&option=contact_email'
						);
					}
				}

				//Check if readme.html exists. If so, recommend deleting it.
				if ( file_exists(get_home_path() . '/readme.html') ){
					$nebula_warnings[] = array(
						'level' => 'warn',
						'description' => '<i class="far fa-fw fa-file-alt"></i> The WordPress core <a href="' . home_url('/') . 'readme.html">readme.html</a> file exists (which exposes version information) and should be deleted.',
						'url' => home_url('/') . 'readme.html'
					);
				}

				//Check if max upload size is different from max post size
				if ( ini_get('upload_max_filesize') !== ini_get('post_max_size') ){
					$nebula_warnings[] = array(
						'level' => 'warn',
						'description' => '<i class="far fa-fw fa-file-alt"></i> The <code>post_max_size</code> (' . ini_get('post_max_size') . ') is different from the <code>upload_max_filesize</code> (' . ini_get('upload_max_filesize') . ').',
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
						$disk_space_free = disk_free_space($path['directory'])/1073741824; //In GB

						if ( $disk_space_free < $path['critical'] ){
							$nebula_warnings[] = array(
								'level' => 'error',
								'description' => '<i class="fas fa-fw fa-hdd"></i> Available disk space in <strong>' . $path['directory'] . '</strong> critically low! Only <strong>' . round($disk_space_free, 2) . 'gb</strong> remaining.'
							);
						} elseif ( $disk_space_free < $path['low'] ){
							$nebula_warnings[] = array(
								'level' => 'warn',
								'description' => '<i class="far fa-fw fa-hdd"></i> Low disk space available in <strong>' . $path['directory'] . '</strong>. Only <strong>' . round($disk_space_free, 2) . 'gb</strong> remaining.'
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
							//var_dump($image_info); echo '<br>';
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

								//Check for Bootstrap JS functionality if bootstrap JS is disabled
								if ( !$this->get_option('allow_bootstrap_js') ){
									$looking_for['bootstrap_js'] = "/\.modal\(|\.bs\.|data-toggle=|data-target=|\.dropdown\(|\.tab\(|\.tooltip\(|\.carousel\(/i";
								}

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
													$nebula_warnings[] = array(
														'level' => 'warn',
														'description' => '<i class="fas fa-fw fa-bug"></i> Possible debug output in <strong>' . str_replace(get_stylesheet_directory(), '', dirname($filepath)) . '/' . basename($filepath) . '</strong> on <strong>line ' . ($line_number+1) . '</strong>.'
													);
												} elseif ( $category === 'bootstrap_js' ){
													$nebula_warnings[] = array(
														'level' => 'warn',
														'description' => '<i class="fab fa-fw fa-bootstrap"></i> <a href="themes.php?page=nebula_options&tab=functions&option=allow_bootstrap_js">Bootstrap JS is disabled</a>, but is possibly needed in <strong>' . str_replace(get_stylesheet_directory(), '', dirname($filepath)) . '/' . basename($filepath) . '</strong> on <strong>line ' . $line_number . '</strong>.',
														'url' => get_admin_url() . 'themes.php?page=nebula_options&tab=functions&option=allow_bootstrap_js'
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
							$nebula_warnings[] = array(
								'level' => 'warn',
								'description' => '<i class="far fa-fw fa-file"></i> ' . $word_count_warning,
								'url' => get_edit_post_link(get_the_id()),
								'meta' => array('target' => '_blank', 'rel' => 'noopener')
							);
						}
					}
				}

				//Check for Yoast or The SEO Framework plugins
				if ( !is_plugin_active('wordpress-seo/wp-seo.php') && !is_plugin_active('autodescription/autodescription.php') ){
					$nebula_warnings[] = array(
						'level' => 'warn',
						'description' => '<i class="fas fa-fw fa-search-plus"></i> A recommended SEO plugin is not active.',
						'url' => get_admin_url() . 'themes.php?page=tgmpa-install-plugins'
					);
				}
			}

			$nebula_warnings = apply_filters('nebula_advanced_warnings', $nebula_warnings);

			$this->timer('Nebula Advanced Warnings', 'end');
			return $nebula_warnings;
		}

		//Audit Output
		public function advanced_warning_output(){
			if ( $this->is_auditing() ){
				//Log when manually auditing pages individually
				if ( isset($_GET['audit']) ){
					$this->add_log('Nebula audit performed on ' . $this->requested_url(), 1);
				}

				$nebula_warnings = json_encode($this->warnings);
				?>
					<style>
						.nebula-audit .audit-desc {position: absolute; bottom: 0; right: 0; color: #fff; background: grey; font-size: 10px; padding: 3px 5px; z-index: 9999;}
							.nebula-audit .nebula-audit .audit-desc {right: auto; left: 0; top: 0; bottom: auto;}
								.nebula-audit .nebula-audit .nebula-audit .audit-desc {right: auto; left: 0; bottom: 0; top: auto;}
									.nebula-audit .nebula-audit .nebula-audit .nebula-audit .audit-desc {right: 0; left: auto; bottom: auto; top: 0;}
						.audit-error {position: relative; border: 2px solid red;}
							.audit-error .audit-desc {background: red;}
						.audit-warn {position: relative; border: 2px solid orange;}
							.audit-warn .audit-desc {background: orange;}
						.audit-notice {position: relative; border: 2px solid blue;}
							.audit-notice .audit-desc {background: blue;}
						#audit-results {position: relative; background: #444; color: #fff; padding: 50px;}
							#audit-results p,
							#audit-results li {color: #fff;}
							#audit-results a {color: #0098d7;}
								#audit-results a:hover {color: #95d600;}
					</style>
					<script>
						jQuery(window).on('load', function(){
							setTimeout(function(){
								jQuery('body').append(jQuery('<div id="audit-results"><p><strong>Nebula Audit Results:</strong></p><ul></ul></div>'));

								var entireDOM = jQuery('html').clone(); //Duplicate the entire HTML to run audits against
								entireDOM.find('#query-monitor-main, #qm, #wpadminbar, script, #audit-results').remove(); //Remove elements to ignore (must ignore scripts so this audit doesn't find itself)

								//Reporting Observer deprecations and interventions
								if ( typeof window.ReportingObserver !== undefined ){ //Chrome 68+
									var nebulaAuditModeReportingObserver = new ReportingObserver(function(reports, observer){
										for ( report of reports ){
											if ( report.body.sourceFile.indexOf('extension') < 0 ){ //Ignore browser extensions
												jQuery("#audit-results ul").append('<li>Reporting Observer (' + report.type + '): ' + report.body.message + ' in ' + report.body.sourceFile + ' on line ' + report.body.lineNumber + '</li>');
											}
										}
									}, {buffered: true});
									nebulaAuditModeReportingObserver.observe();
								}

								//@todo: consider checking WebPageTest timing if API key is available

								//Check protocol
								if ( window.location.href.indexOf('http://') === 0 ){
									jQuery("#audit-results ul").append('<li><i class="fas fa-fw fa-unlock-alt"></i> Non-secure http protocol</li>');
								} else if ( window.location.href.indexOf('https://') === 0 ){
									//check for non-secure resource requests here?
								}

								//Empty meta description
								if ( !entireDOM.find('meta[name="description"]').length ){
									jQuery("#audit-results ul").append('<li><i class="fas fa-fw fa-align-left"></i> Missing meta description</li>');
								} else {
									if ( !entireDOM.find('meta[name="description"]').attr('content').length ){
										jQuery("#audit-results ul").append('<li><i class="fas fa-fw fa-align-left"></i> Meta description tag exists but is empty</li>');
									} else {
										if ( entireDOM.find('meta[name="description"]').attr('content').length < 60 ){
											jQuery("#audit-results ul").append('<li><i class="fas fa-fw fa-align-left"></i> Short meta description</li>');
										}
									}
								}

								//Check title
								if ( !document.title.length ){
									jQuery("#audit-results ul").append('<li><i class="fas fa-fw fa-heading"></i> Missing page title</li>');
								} else {
									if ( document.title.length < 25 ){
										jQuery("#audit-results ul").append('<li><i class="fas fa-fw fa-heading"></i> Short page title</li>');
									}

									if ( document.title.indexOf('Home') > -1 ){
										jQuery("#audit-results ul").append('<li><i class="fas fa-fw fa-heading"></i> Improve page title keywords (remove "Home")</li>');
									}
								}

								//Check H1
								if ( !entireDOM.find('h1').length ){
									jQuery("#audit-results ul").append('<li><i class="fas fa-fw fa-heading"></i> Missing H1 tag</li>');

									if ( entireDOM.find('h1').length > 1 ){
										jQuery("#audit-results ul").append('<li><i class="fas fa-fw fa-heading"></i> Too many H1 tags</li>');
									}
								}

								//Check H2
								if ( !entireDOM.find('h2').length ){
									jQuery("#audit-results ul").append('<li><i class="fas fa-fw fa-heading"></i> Missing H2 tags</li>');
								} else if ( entireDOM.find('h2') <= 2 ){
									jQuery("#audit-results ul").append('<li><i class="fas fa-fw fa-heading"></i> Very few H2 tags</li>');
								}

								//Check that each <article> and <section> has a heading tag
								//https://www.w3.org/wiki/HTML/Usage/Headings/Missing
								entireDOM.find('article, section').each(function(){
									if ( !jQuery(this).find('h1, h2, h3, h4, h5, h6').length ){
										jQuery(this).addClass('nebula-audit audit-warn').append(jQuery('<div class="audit-desc"><i class="fas fa-fw fa-heading"></i> Missing heading tag in this ' + jQuery(this).prop("tagName").toLowerCase() + '</div>'));
										jQuery("#audit-results ul").append('<li><i class="fas fa-fw fa-heading"></i> Missing heading tag within a &lt;' + jQuery(this).prop("tagName").toLowerCase() + '&gt; tag.</li>');
									}
								});

								//Check for a #content-section (or whatever the target is) if Skip to Content button exists
								if ( jQuery('.skip-to-content-link').length ){
									var skipToContentTarget = jQuery('.skip-to-content-link').attr('href');

									if ( skipToContentTarget && !jQuery(skipToContentTarget).length ){
										jQuery("#audit-results ul").append('<li><i class="fas fa-fw fa-link"></i> Skip to Content link target (' + skipToContentTarget + ') does not exist.</li>');
									}
								}

								//Check for placeholder text (in the page content and metadata)
								var commonPlaceholderWords = ['lorem', 'ipsum', 'dolor', 'sit amet', 'consectetur', 'adipiscing', 'malesuada', 'vestibulum']; //Be careful of false positives due to parts of real words (Ex: "amet" in "parameter")
								jQuery.each(commonPlaceholderWords, function(i, word){
									if ( entireDOM.html().indexOf(word) > -1 ){
										jQuery("#audit-results ul").append('<li><i class="fas fa-fw fa-remove-format"></i> Placeholder text found ("' + word + '").</li>');
										return false;
									}
								});

								//Broken images
								jQuery('img').on('error', function(){
									if ( jQuery(this).parents('#wpadminbar').length ){
										return false;
									}

									jQuery(this).addClass('nebula-audit audit-error').append(jQuery('<div class="audit-desc"><i class="far fa-fw fa-image"></i> Broken image</div>'));
									jQuery("#audit-results ul").append('<li><i class="far fa-fw fa-image"></i> Broken image</li>');
								});

								//Images
								entireDOM.find('img').each(function(){
									if ( jQuery(this).parents('#wpadminbar, iframe, #map_canvas').length ){
										return false;
									}

									//Check img alt
									if ( !jQuery(this).is('[alt]') ){
										jQuery(this).wrap('<div class="nebula-audit audit-error"></div>').after('<div class="audit-desc"><i class="far fa-fw fa-image"></i> Missing ALT attribute</div>');
										jQuery("#audit-results ul").append('<li><i class="far fa-fw fa-image"></i> Missing ALT attribute</li>');
									}

									//Check image filesize. Note: cached files are 0
									if ( window.performance ){ //IE10+
										var iTime = performance.getEntriesByName(jQuery(this).attr('src'))[0];
										if ( iTime && iTime.transferSize >= 500000 ){
											jQuery(this).wrap('<div class="nebula-audit audit-warn"></div>').after('<div class="audit-desc"><i class="fas fa-fw fa-image"></i> Image filesize over 500kb</div>');
											jQuery("#audit-results ul").append('<li><i class="fas fa-fw fa-image"></i> Image filesize over 500kb</li>');
										}
									}

									//@todo "Nebula" 0: Use Fetch API with method: 'HEAD' as a more simple way of getting filesize.
									//response.headers.get('content-length')

									//Check image width
									if ( jQuery(this)[0].naturalWidth > 1200 ){
										jQuery(this).wrap('<div class="nebula-audit audit-warn"></div>').after('<div class="audit-desc"><i class="fas fa-fw fa-image"></i> Image wider than 1200px</div>');
										jQuery("#audit-results ul").append('<li><i class="fas fa-fw fa-image"></i> Image wider than 1200px</li>');
									}

									//Check image link
									if ( !jQuery(this).parents('a').length ){
										jQuery(this).wrap('<div class="nebula-audit audit-notice"></div>').after('<div class="audit-desc"><i class="fas fa-fw fa-unlink"></i> Unlinked Image</div>');
										jQuery("#audit-results ul").append('<li><i class="fas fa-fw fa-image"></i> Unlinked image</li>');
									}
								});

								//Videos
								entireDOM.find('video').each(function(){
									//Check video filesize. Note: cached files are 0
									if ( window.performance ){ //IE10+
										var vTime = performance.getEntriesByName(jQuery(this).find('source').attr('src'))[0];

										if ( vTime && vTime.transferSize >= 5000000 ){ //5mb+
											jQuery(this).wrap('<div class="nebula-audit audit-warn"></div>').after('<div class="audit-desc"><i class="fas fa-fw fa-file-video"></i> Video filesize over 5mb</div>');
											jQuery("#audit-results ul").append('<li><i class="fas fa-fw fa-file-video"></i> Video filesize over 5mb</li>');
										}
									}

									//@todo "Nebula" 0: Use Fetch API with method: 'HEAD' as a more simple way of getting filesize.
									//response.headers.get('content-length')

									//Check unmuted autoplay
									if ( jQuery(this).is('[autoplay]') && !jQuery(this).is('[muted]') ){
										jQuery(this).wrap('<div class="nebula-audit audit-warn"></div>').after('<div class="audit-desc"><i class="fas fa-fw fa-video"></i> Autoplay without muted attribute</div>');
										jQuery("#audit-results ul").append('<li><i class="fas fa-fw fa-video"></i> Videos set to autoplay without being muted will not autoplay in Chrome.</li>');
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
										jQuery(this).wrap('<div class="nebula-audit audit-notice"></div>').after('<div class="audit-desc"><i class="fas fa-fw fa-pencil-alt"></i> Many form fields</div>');
										jQuery("#audit-results ul").append('<li><i class="fas fa-fw fa-pencil-alt"></i> Many form fields</li>');
									}
								});

								//Check for modals inside of #body-wrapper
								if ( entireDOM.find('#body-wrapper .modal').length ){
									jQuery("#audit-results ul").append('<li><i class="far fa-fw fa-window-restore"></i> Modal found inside of #body-wrapper. Move modals to the footer outside of the #body-wrapper div.</li>');
								}

								<?php do_action('nebula_audits_js'); ?>

								var nebulaWarnings = <?php echo $nebula_warnings; ?> || {};
								jQuery.each(nebulaWarnings, function(i, warning){
									if ( warning.description.indexOf('Audit Mode') > 0 ){
										return true; //Skip
									}
									jQuery("#audit-results ul").append('<li>' + warning.description + '</li>');
								});

								<?php if ( !(is_home() || is_front_page()) ): ?>
									//Check breadcrumb schema tag
									if ( !jQuery('[itemtype*=BreadcrumbList]').length ){
										jQuery("#audit-results ul").append('<li><i class="fas fa-bread-slice"></i> Missing breadcrumb schema tag</li>');
									}
								<?php endif; ?>

								//Check issue count (do this last)
								if ( jQuery("#audit-results ul li").length <= 0 ){
									jQuery("#audit-results").append('<p><strong><i class="fas fa-fw fa-check"></i> No issues were found on this page.</strong> Be sure to check other pages (and run <a href="https://nebula.gearside.com/get-started/checklists/testing-checklist/" target="_blank">more authoritative tests</a>)!</p>');
								} else {
									jQuery("#audit-results").append('<p><strong><i class="fas fa-fw fa-times"></i> Found issues: ' + jQuery("#audit-results ul li").length + '<strong></p>');
								}
								jQuery("#audit-results").append('<p><small>Note: This does not check for @todo comments. Use the Nebula To-Do Manager in the WordPress admin dashboard to view.</small></p>');
							}, 1);
						});
					</script>
				<?php
			}
		}
	}
}