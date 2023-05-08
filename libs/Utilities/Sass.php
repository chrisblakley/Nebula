<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Sass') ){
	trait Sass {
		public $scss;
		public $sass_process_status = '';
		public $sass_files_processed = 0;
		public $was_sass_processed = false;
		public $latest_scss_mtime = 0; //Prep a flag to determine the last modified SCSS file time

		public function hooks(){
			if ( $this->get_option('scss') && !$this->is_background_request() && !is_customize_preview() ){
				add_action('init', array($this, 'scss_controller'));
				add_action('nebula_body_open', array($this, 'output_sass_errors')); //Front-end
				add_action('admin_notices', array($this, 'output_sass_errors')); //Admin (Do not use Nebula Warnings utility for these errors)
				add_action('nebula_options_saved', array($this, 'touch_sass_stylesheet'));
			}
		}

		/*==========================
			Nebula Sass Compiling
			Add directories to be checked for .scss files by using the filter "nebula_scss_locations". Example:
			add_filter('nebula_scss_locations', 'my_plugin_scss_files');
			function my_plugin_scss_files($scss_locations){
				$scss_locations['my_plugin'] = array(
					'directory' => plugin_dir_path(__FILE__),
					'uri' => plugin_dir_url(__FILE__),
					'imports' => plugin_dir_path(__FILE__) . '/scss/partials/'
				);
				return $scss_locations;
			}
		 ===========================*/
		public function scss_controller($force_all = false){
			//Ensure Sass option is enabled
			if ( $this->get_option('scss') ){
				$this->timer('Sass (Total)', 'start', 'Sass');
				global $pagenow;

				$sass_throttle = get_transient('nebula_sass_throttle'); //This prevents Sass from compiling multiple times in quick succession
				if ( empty($sass_throttle) || $pagenow === 'update.php' || $this->is_debug() ){
					$this->sass_process_status = ( isset($this->super->get['sass']) )? 'Sass was not throttled (so okay to process).' : $this->sass_process_status;

					//Ignore background requests (except for automated theme updates) and bots
					if ( !$this->is_background_request() && !$this->is_bot() && $pagenow !== 'update-core.php' ){ //Ignore update-core.php (this listing page of updates)– that is different than update.php (which is the actual updater). Note: update.php is not considered a "background" request
						$this->sass_process_status = ( isset($this->super->get['sass']) )? 'Sass is enabled, and the request is okay to process.' : $this->sass_process_status;

						//Ignore fetch requests (like via Service Worker) - Only process Sass on certain requests SW will fetch with the sec-fetch-mode header as "cors" or "no-cors".
						if ( isset($this->super->server['HTTP_SEC_FETCH_MODE']) && !in_array($this->super->server['HTTP_SEC_FETCH_MODE'], array('navigate', 'nested-navigate', 'same-origin')) ){ //Maybe same-site too? Just avoid "cors" and "no-cors"
							$this->sass_process_status = ( isset($this->super->get['sass']) )? 'Sass was not processed. The fetch mode of "' . sanitize_text_field($this->super->server['HTTP_SEC_FETCH_MODE']) . '" was not suitable.' : $this->sass_process_status;
							$this->timer('Sass (Total)', 'end');
							return false;
						}

						//Check when Sass processing is allowed to happen
						if ( !current_user_can('publish_posts') ){ //If the role of this user is lower than necessary
							$this->sass_process_status = ( isset($this->super->get['sass']) )? 'Sass was not processed. It can only be processed by logged in users (per Nebula option).' : $this->sass_process_status;
							$this->timer('Sass (Total)', 'end');
							return false;
						}

						if ( !is_writable(get_template_directory()) || !is_writable(get_template_directory() . '/style.css') ){
							trigger_error('The template directory or files are not writable. Can not compile Sass files!', E_USER_NOTICE);
							$this->sass_process_status = ( isset($this->super->get['sass']) )? 'Sass was not processed. The template directory or files are not writable.' : $this->sass_process_status;
							$this->timer('Sass (Total)', 'end');
							return false;
						}

						//Nebula SCSS locations
						$scss_locations = array(
							'parent' => array(
								'directory' => get_template_directory(), //Root theme/plugin directory
								'uri' => get_template_directory_uri(), //This is for reference to the directory URL in for files within the CSS itself
								'core' => get_template_directory() . '/assets/scss/', //Where the .scss files are located
								'imports' => array(get_template_directory() . '/assets/scss/partials/'), //The directory where import partials are located
								'output' => get_template_directory() . '/assets/css/', //This is where the processed .css files will be placed
							)
						);

						//Child theme SCSS locations
						if ( is_child_theme() ){
							$scss_locations['child'] = array(
								'directory' => get_stylesheet_directory(), //Root theme/plugin directory
								'uri' => get_stylesheet_directory_uri(), //This is for reference to the directory URL in for files within the CSS itself
								'core' => get_stylesheet_directory() . '/assets/scss/', //Where the .scss files are located
								'imports' => array(get_stylesheet_directory() . '/assets/scss/partials/'), //The directory where import partials are located
								'output' => get_stylesheet_directory() . '/assets/css/', //This is where the processed .css files will be placed
							);
						}

						//Allow for additional Sass locations to be included. Imports can be an array of directories.
						$all_scss_locations = apply_filters('nebula_scss_locations', $scss_locations);

						//Check if all Sass files should be rendered
						if ( $this->is_staff() ){ //Forcing all Sass files to process via query string is only allowed by staff
							if ( isset($this->super->get['sass']) || isset($this->super->get['scss']) ){
								$force_all = true;
								$this->sass_process_status = ( isset($this->super->get['sass']) )? 'All Sass files were processed forcefully via query string.' : $this->sass_process_status;
								$this->add_log('Sass force re-process requested', 1); //Logging this one because it was specifically requested. The other conditions below are otherwise detected.
							}

							if ( !$force_all && (isset($this->super->get['settings-updated']) || $this->get_data('need_sass_compile') === 'true') ){
								$force_all = true;
								$this->sass_process_status = ( isset($this->super->get['sass']) )? 'All Sass files were processed forcefully after Nebula Options saved.' : $this->sass_process_status;
							}
						}

						//Check if partial files have been modified since last Sass process
						if ( empty($force_all) ){ //If already processing everything, do not need to check individual partial files
							$scss_last_processed = $this->get_data('scss_last_processed');
							if ( $this->get_data('scss_last_processed') != 0 ){
								foreach ( $all_scss_locations as $scss_location ){ //Loop through each location (parent, child, relevant plugins, etc.)
									//Check core directory for "special" files
									$critical_file = $scss_location['core'] . 'critical.scss';
									if ( file_exists($critical_file) && $scss_last_processed-filemtime($critical_file) < -30 ){
										$force_all = true; //If critical.scss file has been edited, reprocess everything.
										$this->sass_process_status = ( isset($this->super->get['sass']) )? 'All Sass files were processed forcefully because critical Sass was modified.' : $this->sass_process_status;
										break; //No need to continue looking at individual directories/files since we are not reprocessing everything anyway
									}

									foreach ( $scss_location['imports'] as $imports_directory ){ //Loop through all imports directories
										foreach ( glob($imports_directory . '*') as $import_file ){ //Loop through all partial files
											if ( $scss_last_processed-filemtime($import_file) < -30 ){
												$force_all = true; //If any partial file has been edited, reprocess everything.
												$this->sass_process_status = ( isset($this->super->get['sass']) )? 'All Sass files were processed forcefully because a partial file was modified (' . $import_file . ').' : $this->sass_process_status;
												break 3; //Break out of all 3 foreach loops since we are now reprocessing everything anyway
											}
										}
									}
								}
							}
						}

						$this->update_data('need_sass_compile', 'true'); //Set this to true as we are compiling so we can use it as a flag to run some thing only once.

						global $sass_errors;
						$sass_errors = array();

						//Find and render .scss files at each location
						$this->was_sass_processed = false; //This is changed to true only when Sass is actually processed
						foreach ( $all_scss_locations as $scss_location_name => $scss_location_paths ){
							$this->render_scss($scss_location_name, $scss_location_paths, $force_all); //Remember: this does not mean Sass is being processed– this function checks the files first and then processes only when necessary. Also remember that this is checking a location (not an individual file), so the return here would be true if any Sass file in this location was processed.
						}

						$this->update_data('need_sass_compile', 'false'); //Set it to false after Sass is finished. Do not wrap this in a "was_sass_processed" conditional! Or else it will constantly want to force-reprocess all Sass because 'need_sass_compile' will not get set to false (...for some reason? Just leave it alone).

						if ( $this->was_sass_processed ){
							set_transient('nebula_sass_throttle', time(), 15); //15 second cache to throttle Sass from being re-processed again immediately. This may work as an object cache, but there is at least 4-6 seconds between the two process times, so this transient works well. Maybe it can be shortened to 10 seconds in the future?
						}

						$this->sass_process_status = ( !isset($this->super->get['sass']) && $this->was_sass_processed )? $this->sass_files_processed . ' Sass file(s) have been processed.' : $this->sass_process_status; //Show this status if Sass was processed but not explicitly forced. Otherwise use the existing status

						if ( time()-$this->latest_scss_mtime >= MONTH_IN_SECONDS*3 ){ //If the last style.scss modification has not happened within 90 days disable Sass to optimize all future page loads (no need to check files at all)
							$this->update_option('scss', 0); //Once Sass is disabled this way, a developer would need to re-enable it in Nebula Options.
							$this->sass_process_status = 'Sass processing has been disabled to improve performance because style.scss has not been modified in 90 days.';
							$this->add_log('Sass processing has been disabled due to inactivity to improve performance.', 4); //The chances of someone noticing the above status is unlikely as it happens once after inactive development, so log this message explicitly
						}
					} elseif ( $this->is_dev() && !$this->is_admin_page() && (isset($this->super->get['sass']) || isset($this->super->get['scss'])) ){
						$this->sass_process_status = ( isset($this->super->get['sass']) )? 'Sass can not compile because it is disabled in Nebula Functions.' : $this->sass_process_status;
						trigger_error('Sass can not compile because it is disabled in Nebula Functions.', E_USER_NOTICE);
					}
				} else {
					$this->sass_process_status = ( isset($this->super->get['sass']) )? 'Sass is throttled between processing. <span id="sass-cooldown-wait">Please wait for <strong id="sass-cooldown">15 seconds</strong>. </span><a id="sass-cooldown-again" class="hidden" href="?sass=true">Click here to try again now.</a>' : $this->sass_process_status;
				}

				$this->timer('Sass (Total)', 'stop', 'Sass');
			}

			return $this->was_sass_processed;
		}

		//Render scss files
		public function render_scss($location_name=false, $location_paths=false, $force_all=false){
			$override = apply_filters('pre_nebula_render_scss', null, $location_name, $location_paths, $force_all);
			if ( isset($override) ){return;}

			global $sass_errors;

			if ( $this->get_option('scss') && !empty($location_name) && !empty($location_paths) ){
				$this->timer('Sass (' . $location_name . ')', 'start', 'Sass');

				//Require SCSSPHP
				require_once get_template_directory() . '/inc/vendor/scssphp/scss.inc.php'; //Run the autoloader. SCSSPHP is a compiler for SCSS 3.x
				$this->scss = new \ScssPhp\ScssPhp\Compiler();

				//Register import directories
				if ( !empty($location_paths['imports']) ){
					if ( !is_array($location_paths['imports']) ){
						$location_paths['imports'] = array($location_paths['imports']); //Convert to an array if passes as a string
					}
					foreach ( $location_paths['imports'] as $imports_directory ){
						$this->scss->addImportPath($imports_directory);
					}
				}

				//Set compiling options
				$this->scss->setOutputStyle(\ScssPhp\ScssPhp\OutputStyle::COMPRESSED); //Minify CSS (while leaving "/*!" comments for WordPress).

				//Source Maps
				$this->scss->setSourceMap(1); //0 = No .map, 1 = Inline .map, 2 = Output .map file
				$this->scss->setSourceMapOptions(array(
					'sourceMapBasepath' => ABSPATH, //Difference between file & URL locations, removed from all source paths in .map
					'sourceRoot' => '/', //Added to source path locations if needed
				));

				//Variables
				$nebula_scss_variables = array(
					'parent_partials_directory' => \ScssPhp\ScssPhp\ValueConverter::fromPhp(get_template_directory() . '/assets/scss/partials/'), //There must be a better way to call fromPhp() here...
					'child_partials_directory' => \ScssPhp\ScssPhp\ValueConverter::fromPhp(get_stylesheet_directory() . '/assets/scss/partials/'), //There must be a better way to call fromPhp() here...
					'template_directory' => \ScssPhp\ScssPhp\ValueConverter::fromPhp(get_template_directory_uri()), //There must be a better way to call fromPhp() here...
					'stylesheet_directory' => \ScssPhp\ScssPhp\ValueConverter::fromPhp(get_stylesheet_directory_uri()), //There must be a better way to call fromPhp() here...
					'this_directory' => \ScssPhp\ScssPhp\ValueConverter::fromPhp($location_paths['uri']), //There must be a better way to call fromPhp() here...
					'primary_color' => \ScssPhp\ScssPhp\ValueConverter::parseValue($this->get_color('primary_color', false, '#0098d7')), //There must be a better way to call parseValue() here...
					'secondary_color' => \ScssPhp\ScssPhp\ValueConverter::parseValue($this->get_color('secondary_color', false, '#95d600')), //There must be a better way to call parseValue() here...
					'background_color' => \ScssPhp\ScssPhp\ValueConverter::parseValue($this->get_color('background_color', false, '#f6f6f6')), //There must be a better way to call parseValue() here...
				);

				$all_scss_variables = apply_filters('nebula_scss_variables', $nebula_scss_variables);
				$this->scss->addVariables($nebula_scss_variables);

				//Imports/Partials (find the last modified time)
				$latest_import = 0;
				foreach ( glob($imports_directory . '*') as $import_file ){
					if ( filemtime($import_file) > $latest_import ){
						$latest_import = filemtime($import_file);

						if ( $latest_import > $this->latest_scss_mtime ){
							$this->latest_scss_mtime = $latest_import;
						}
					}
				}

				$this->add_custom_scssphp_functions($this->scss); //Add custom PHP functions that can be used in Sass

				do_action('nebula_before_sass_compile', $location_paths); //Allow modification of files before looping through to compile Sass

				//Compile each SCSS file
				foreach ( glob($location_paths['core'] . '*.scss') as $scss_file ){ //@TODO "Nebula" 0: Change to glob_r() but will need to create subdirectories if they don't exist.
					$scss_file_path_info = pathinfo($scss_file);
					$debug_name = str_replace(WP_CONTENT_DIR, '', $scss_file_path_info['dirname']) . '/' . $scss_file_path_info['basename'];
					$this->timer('Sass File (' . $debug_name . ')');

					//Skip file conditions (only if not forcing all)
					if ( empty($force_all) ){
						//@todo "Nebula" 0: Add hook here so other functions/plugins can add stipulations of when to skip files. Maybe an array instead?
						$is_admin_file = (!$this->is_admin_page() && !$this->is_login_page()) && in_array($scss_file_path_info['filename'], array('login', 'admin', 'tinymce')); //If viewing front-end, skip WP admin files.
						if ( $is_admin_file ){
							$this->timer('Sass File (' . $debug_name . ')', 'end');
							continue;
						}
					}

					//If file exists, and has .scss extension, and doesn't begin with "_".
					if ( is_file($scss_file) && $scss_file_path_info['extension'] === 'scss' && $scss_file_path_info['filename'][0] !== '_' ){
						//Determine the .css output filepath
						$output_directory = $location_paths['output']; //Default to the output directory
						if ( ($location_name == 'parent' || $location_name == 'child') ){
							if ( $scss_file_path_info['filename'] === 'style' ){
								$output_directory = $location_paths['directory'] . '/'; //Root directory for theme style.css
							}
						}

						$css_filepath = $output_directory . $scss_file_path_info['filename'] . '.css';

						wp_mkdir_p($location_paths['output']); //Create the output directory (in case it doesn't exist already)

						//Update the last SCSS file modification time (if later than the latest yet)
						if ( filemtime($scss_file) > $this->latest_scss_mtime ){
							$this->latest_scss_mtime = filemtime($scss_file);
						}

						//If style.css has been edited after style.scss, save backup but continue compiling SCSS
						if ( (is_child_theme() && $location_name !== 'parent' ) && ($scss_file_path_info['filename'] === 'style' && file_exists($css_filepath) && $this->get_data('scss_last_processed') != '0' && $this->get_data('scss_last_processed')-filemtime($css_filepath) < -30) ){
							copy($css_filepath, $css_filepath . '.bak'); //Backup the style.css file to style.css.bak
							if ( $this->is_dev() || current_user_can('manage_options') ){
								global $scss_debug_ref;
								$scss_debug_ref = $location_name . ':';
								$scss_debug_ref .= ($this->get_data('scss_last_processed')-filemtime($css_filepath));
								add_action('wp_head', array($this, 'scss_console_warning')); //Call the console error note
							}
						}

						//If .css file doesn't exist, or is older than .scss file (or any partial), or is debug mode, or forced
						if ( !file_exists($css_filepath) || filemtime($scss_file) > filemtime($css_filepath) || $latest_import > filemtime($css_filepath) || $this->is_debug() || $force_all ){
							ini_set('memory_limit', '512M'); //Increase memory limit for this script. //@todo Nebula 0: Remove this when possible...
							WP_Filesystem();
							global $wp_filesystem;
							$existing_css_contents = ( file_exists($css_filepath) )? $wp_filesystem->get_contents($css_filepath) : '';

							//If the correlating .css file doesn't contain a comment to prevent overwriting
							if ( !strpos(strtolower($existing_css_contents), 'scss disabled') ){ //@todo "Nebula" 0: Update strpos() to str_contains() in PHP8
								$this_scss_contents = $wp_filesystem->get_contents($scss_file); //Copy SCSS file contents

								//Catch fatal compilation errors when PHP v7.0+ to provide additional information without crashing
								try {
									$compiled_css = $this->scss->compileString($this_scss_contents, $scss_file)->getCss(); //Compile the SCSS
									$this->was_sass_processed = true;
									$this->sass_files_processed++;
								} catch (\Throwable $error){
									$unprotected_array = (array) $error;
									$prefix = chr(0) . '*' . chr(0);

									$sass_errors[] = array(
										'file' => $scss_file,
										'message' => $unprotected_array[$prefix . 'message']
									);

									do_action('qm/error', $error);

									continue; //Skip the file that contains errors
								}

								$enhanced_css = $this->scss_post_compile($compiled_css); //Compile server-side variables into SCSS
								$wp_filesystem->put_contents($css_filepath, $enhanced_css); //Save the rendered CSS.
								$this->update_data('scss_last_processed', time());
							}
						}
					}

					$this->timer('Sass File (' . $debug_name . ')', 'end');
				}

				$this->timer('Sass (' . $location_name . ')', 'end');
			}

			return $this->was_sass_processed;
		}

		//Touch the main parent Nebula stylesheet to extended the last Sass modification time date (when Nebula Options are saved)
		public function touch_sass_stylesheet(){
			$main_sass_file = get_template_directory() . '/assets/scss/style.scss'; //This is the parent stylesheet (which should always exist)

			if ( file_exists($main_sass_file) ){
				touch($main_sass_file);
			}
		}

		//Display any Sass compilation errors that occurred
		public function output_sass_errors(){
			global $sass_errors;

			if ( !empty($sass_errors) ){
				if ( $this->is_staff() || current_user_can('publish_pages') ){ //Staff or Editors
					foreach ( $sass_errors as $sass_error ){
						echo '<div class="nebula-admin-notice notice notice-error"><p><strong>[Sass Compilation Error]</strong> ' . $sass_error['message'] . ' in <strong>' . $sass_error['file'] . '</strong>. This file has been skipped and was not processed.</p></div>';
						$this->sass_process_status = ( isset($this->super->get['sass']) )? 'Sass processing encountered an error and file(s) were skipped.' : $this->sass_process_status;
					}
				}

				echo '<script>console.error("A sass compilation error occurred.");</script>'; //Log in JS console to avoid disturbing regular visitors
			}
		}

		//Log Sass .bak note in the browser console
		public function scss_console_warning(){
			global $scss_debug_ref;
			echo '<script>console.warn("Warning: Sass compiling is enabled, but it appears that style.css has been manually updated (Reference: ' . $scss_debug_ref . 's)! A style.css.bak backup has been made. If not using Sass, disable it in Nebula Options. Otherwise, make all edits in style.scss in the /assets/scss directory!");</script>';
		}

		//Compile server-side variables into SCSS
		public function scss_post_compile($scss){
			$override = apply_filters('pre_nebula_scss_post_compile', null, $scss);
			if ( isset($override) ){return $override;}

			if ( empty($scss) ){
				return $scss;
			}

			$scss = preg_replace("(" . str_replace('/', '\/', get_template_directory()) . ")", '', $scss); //Reduce theme path for SCSSPHP debug line comments
			$scss = preg_replace("(" . str_replace('/', '\/', get_stylesheet_directory()) . ")", '', $scss); //Reduce theme path for SCSSPHP debug line comments (For child themes)
			do_action('nebula_scss_post_compile_every');

			$scss .= PHP_EOL . '/* ' . date('l, F j, Y \a\t g:i:s A', time()) . ' */';

			//Run these once
			if ( $this->get_data('need_sass_compile') != 'false' ){
				do_action('nebula_scss_post_compile_once');

				$this->update_data('scss_last_processed', time());
				$this->update_data('need_sass_compile', 'false');

				//Update the Service Worker JavaScript file (to clear cache)
				if ( $this->get_option('service_worker') && is_writable(get_home_path()) ){
					if ( file_exists($this->sw_location(false)) ){
						$this->update_sw_js();
					}
				}
			}

			return $scss;
		}

		//Get a Sass variable from a theme
		//Note: Do not condition this based on the Sass option because it may be automatically deactivated in the future
		public function get_sass_variable($variable='$primary_color', $filepath='child', $return='value'){
			$override = apply_filters('pre_get_sass_variable', null, $variable, $filepath, $return);
			if ( isset($override) ){return $override;}

			//Use the passed variables file location, or choose from the passed theme
			if ( $filepath === 'parent' ){
				$filepath = get_template_directory() . '/assets/scss/partials/_variables.scss';
			} elseif ( $filepath === 'child' && is_child_theme() ){
				$filepath = get_stylesheet_directory() . '/assets/scss/partials/_variables.scss';
			}

			$variable_name = $this->normalize_color_name($variable);
			$transient_name = 'nebula_sass_variable_' . $variable_name; //Does this need to be more unique (to include the location too)? Cannot just append $filepath...

			$scss_variables = nebula()->transient($transient_name, function($data){
				$timer_name = $this->timer('Sass Variable (' . $data['variable'] . ')', 'start', 'Sass');

				if ( !file_exists($data['filepath']) ){
					$this->timer($timer_name, 'end');
					return false;
				}

				WP_Filesystem();
				global $wp_filesystem;
				$scss_variables = $wp_filesystem->get_contents($data['filepath']);
				$this->timer($timer_name, 'end');

				return $scss_variables;
			}, array('variable' => $variable, 'filepath' => $filepath), HOUR_IN_SECONDS*12);

			preg_match('/(?<comment>\/\/|\/\*\s?)?\$' . $variable_name . ':\s?(?<value>.*)(;|\s?!default;)(.*$)?/m', $scss_variables, $matches);

			//Return the entire line if requested
			if ( $return === 'all' ){
				return $matches;
			}

			if ( empty($matches['value']) ){
				return false; //Color was not found
			}

			//If the color is exists but is commented out ignore it
			if ( !empty($matches['comment']) ){
				return false; //This is breaking lots of things
			}

			//Remove "!default" from colors if it exists
			if ( $return === 'color' ){
				return trim(preg_replace('/(!default)/i', '', $matches['value']));
			}

			return trim($matches['value']);
		}

		//Pull the appropriate color or obtain it from a specific location ('customizer', 'child' or 'parent')
		//Note: Do not condition this based on the Sass option because it may be automatically deactivated in the future
		public function sass_color($color='primary', $specific_location=false, $default=false){return $this->get_color($color, $specific_location, $default);}
		public function get_color($color='primary', $specific_location=false, $default='black'){
			$override = apply_filters('pre_get_color', null, $color, $specific_location);
			if ( isset($override) ){return $override;}

			$color = $this->normalize_color_name($color);

			//Check the Customizer
			if ( empty($specific_location) || $specific_location === 'customizer' ){
				$theme_mod_color = get_theme_mod('nebula_' . $color);
				if ( !empty($theme_mod_color) ){
					return $theme_mod_color;
				}
			}

			//Check the child theme
			if ( empty($specific_location) || $specific_location === 'child' ){
				if ( is_child_theme() ){
					$child_theme_color = $this->get_sass_variable($color, 'child', 'color');

					if ( !empty($child_theme_color) ){
						return $child_theme_color;
					}
				}
			}

			//Check the parent theme
			if ( empty($specific_location) || $specific_location === 'parent' ){
				$parent_theme_color = $this->get_sass_variable($color, 'parent', 'color');

				if ( !empty($parent_theme_color) ){
					return $parent_theme_color;
				}
			}

			return $default; //Return the default color if provided, otherwise return black
		}

		//Normalize certain color names
		public function normalize_color_name($color){
			switch ( str_replace(array('$', ' ', '_', '-'), '', $color) ){
				case 'primary':
				case 'primarycolor':
				case 'first':
				case 'main':
				case 'brand':
					return 'primary_color';
				case 'secondary':
				case 'secondarycolor':
				case 'second':
					return 'secondary_color';
				default:
					return str_replace('$', '', $color);
			}
		}

		//Add custom PHP functions that Sass can access
		public function add_custom_scssphp_functions($scss){
			//Calculate the linear channel of a color
			$scss->registerFunction(
				'php_linear_channel',
				function($args, $kwargs){
					return \ScssPhp\ScssPhp\ValueConverter::parseValue($this->linear_channel($kwargs['php_color_value'][1])); //There must be a better way to call parseValue() here...
				},
				array('php_color_value')
			);

			//Calculate the luminance for a color.
			$scss->registerFunction(
				'php_luminance',
				function($args, $kwargs){
					return \ScssPhp\ScssPhp\ValueConverter::parseValue($this->luminance($kwargs['php_color'][1])); //There must be a better way to call parseValue() here...
				},
				array('php_color')
			);

			//Calculate the contrast ratio between two colors.
			$scss->registerFunction(
				'php_contrast',
				function($args, $kwargs){
					return \ScssPhp\ScssPhp\ValueConverter::parseValue($this->contrast($kwargs['php_back'][1], $kwargs['php_front'][1])); //There must be a better way to call parseValue() here...
				},
				array('php_back', 'php_front')
			);
		}
	}
}