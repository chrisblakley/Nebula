<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Sass') ){
	trait Sass {
		public function hooks(){
			$this->latest_scss_mtime = 0; //Prep a flag to determine the last modified SCSS file

			add_action('init', array($this, 'scss_controller'));
			add_action('nebula_body_open', array($this, 'output_sass_errors')); //Front-end
			add_action('admin_notices', array($this, 'output_sass_errors')); //Admin
			add_action('nebula_options_saved', array($this, 'touch_sass_stylesheet'));
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
		public function scss_controller(){
			if ( !is_writable(get_template_directory()) || !is_writable(get_template_directory() . '/style.css') ){
				trigger_error('The template directory or files are not writable. Can not compile Sass files!', E_USER_NOTICE);
				return false;
			}

			if ( $this->get_option('scss') && !$this->is_ajax_or_rest_request() ){
				//Nebula SCSS locations
				$scss_locations = array(
					'parent' => array(
						'directory' => get_template_directory(),
						'uri' => get_template_directory_uri(),
						'core' => get_template_directory() . '/assets/scss/',
						'imports' => array(get_template_directory() . '/assets/scss/partials/')
					)
				);

				//Child theme SCSS locations
				if ( is_child_theme() ){
					$scss_locations['parent']['imports'][] = get_stylesheet_directory() . '/assets/scss/partials/'; //@todo "Nebula" 0: Clarify here why parent theme needs to know child imports directory. This line is making an array of 2 import paths by appending the child partials directory to it... This may have been done before other themes/plugins could hook in and declare their own directories to Nebula

					$scss_locations['child'] = array(
						'directory' => get_stylesheet_directory(),
						'uri' => get_stylesheet_directory_uri(),
						'core' => get_template_directory() . '/assets/scss/',
						'imports' => array(get_stylesheet_directory() . '/assets/scss/partials/')
					);
				}

				//Allow for additional Sass locations to be included. Imports can be an array of directories.
				$all_scss_locations = apply_filters('nebula_scss_locations', $scss_locations);

				//Check if all Sass files should be rendered
				$force_all = false;
				if ( (isset($_GET['sass']) || isset($_GET['scss']) || isset($_GET['settings-updated']) || $this->get_data('need_sass_compile') === 'true') && $this->is_staff() ){
					$force_all = true;
				}

				//Check if partial files have been modified since last Sass process
				if ( empty($force_all) ){ //If already processing everything, don't need to check individual partial files
					$scss_last_processed = $this->get_data('scss_last_processed');
					if ( $this->get_data('scss_last_processed') != 0 ){
						foreach ( $all_scss_locations as $scss_location ){
							//Check core directory for "special" files
							if ( !empty($scss_location['core']) ){
								$critical_file = $scss_location['core'] . 'critical.scss';
								if ( file_exists($critical_file) && $scss_last_processed-filemtime($critical_file) < -30 ){
									$force_all = true; //If critical.scss file has been edited, reprocess everything.
									break;
								}
							}

							foreach ( $scss_location['imports'] as $imports_directory ){
								foreach ( glob($imports_directory . '*') as $import_file ){
									if ( $scss_last_processed-filemtime($import_file) < -30 ){
										$force_all = true; //If any partial file has been edited, reprocess everything.
										break 3; //Break out of all 3 foreach loops
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
				foreach ( $all_scss_locations as $scss_location_name => $scss_location_paths ){
					$this->render_scss($scss_location_name, $scss_location_paths, $force_all);
				}

				$this->update_data('need_sass_compile', 'false'); //Set it to false after Sass is finished

				if ( time()-$this->latest_scss_mtime >= MONTH_IN_SECONDS ){ //If the last style.scss modification hasn't happened within a month disable Sass.
					$this->update_option('scss', 0); //Once Sass is disabled this way, a developer would need to re-enable it in Nebula Options.
				}
			} elseif ( $this->is_dev() && !$this->is_admin_page() && (isset($_GET['sass']) || isset($_GET['scss'])) ){
				trigger_error('Sass can not compile because it is disabled in Nebula Functions.', E_USER_NOTICE);
			}
		}

		//Render scss files
		public function render_scss($location_name=false, $location_paths=false, $force_all=false){
			$override = apply_filters('pre_nebula_render_scss', null, $location_name, $location_paths, $force_all);
			if ( isset($override) ){return;}

			global $sass_errors;

			if ( $this->get_option('scss') && !empty($location_name) && !empty($location_paths) ){
				$this->timer('Sass (' . $location_name . ')', 'start', 'Sass');

				//Require SCSSPHP
				require_once get_template_directory() . '/inc/vendor/scssphp/scss.inc.php'; //SCSSPHP is a compiler for SCSS 3.x
				$this->scss = new \ScssPhp\ScssPhp\Compiler();

				//Register import directories
				if ( !is_array($location_paths['imports']) ){
					$location_paths['imports'] = array($location_paths['imports']); //Convert to an array if passes as a string
				}
				foreach ( $location_paths['imports'] as $imports_directory ){
					$this->scss->addImportPath($imports_directory);
				}

				//Set compiling options
				$this->scss->setFormatter('ScssPhp\ScssPhp\Formatter\Compressed'); //Minify CSS (while leaving "/*!" comments for WordPress).

				//Source Maps
				$this->scss->setSourceMap(1); //0 = No .map, 1 = Inline .map, 2 = Output .map file
				$this->scss->setSourceMapOptions(array(
					'sourceMapBasepath' => $_SERVER['DOCUMENT_ROOT'], //Difference between file & URL locations, removed from all source paths in .map
					'sourceRoot' => '/', //Added to source path locations if needed
				));

				//Variables
				$nebula_scss_variables = array(
					'parent_partials_directory' => get_template_directory() . '/assets/scss/partials/',
					'child_partials_directory' => get_stylesheet_directory() . '/assets/scss/partials/',
					'template_directory' => '"' . get_template_directory_uri() . '"',
					'stylesheet_directory' => '"' . get_stylesheet_directory_uri() . '"',
					'this_directory' => '"' . $location_paths['uri'] . '"',
					'primary_color' => $this->get_color('primary_color', false, '#0098d7'),
					'secondary_color' => $this->get_color('secondary_color', false, '#95d600'),
					'background_color' => $this->get_color('background_color', false, '#f6f6f6'),
				);

				$all_scss_variables = apply_filters('nebula_scss_variables', $nebula_scss_variables);
				$this->scss->setVariables($nebula_scss_variables);

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
				foreach ( glob($location_paths['directory'] . '/assets/scss/*.scss') as $scss_file ){ //@TODO "Nebula" 0: Change to glob_r() but will need to create subdirectories if they don't exist.
					$scss_file_path_info = pathinfo($scss_file);
					$debug_name = str_replace(WP_CONTENT_DIR, '', $scss_file_path_info['dirname']) . '/' . $scss_file_path_info['basename'];
					$this->timer('Sass File ' . $debug_name);

					//Skip file conditions (only if not forcing all)
					if ( empty($force_all) ){
						//@todo "Nebula" 0: Add hook here so other functions/plugins can add stipulations of when to skip files. Maybe an array instead?
						$is_admin_file = (!$this->is_admin_page() && !$this->is_login_page()) && in_array($scss_file_path_info['filename'], array('login', 'admin', 'tinymce')); //If viewing front-end, skip WP admin files.
						if ( $is_admin_file ){
							continue;
						}
					}

					//If file exists, and has .scss extension, and doesn't begin with "_".
					if ( is_file($scss_file) && $scss_file_path_info['extension'] === 'scss' && $scss_file_path_info['filename'][0] !== '_' ){
						$css_filepath = ( $scss_file_path_info['filename'] === 'style' )? $location_paths['directory'] . '/style.css': $location_paths['directory'] . '/assets/css/' . $scss_file_path_info['filename'] . '.css'; //style.css to the root directory. All others to the /css directory in the /assets/scss directory.
						wp_mkdir_p($location_paths['directory'] . '/assets/css'); //Create the /css directory (in case it doesn't exist already).

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
							ini_set('memory_limit', '512M'); //Increase memory limit for this script. //@TODO "Nebula" 0: Is this the best thing to do here? Other options?
							WP_Filesystem();
							global $wp_filesystem;
							$existing_css_contents = ( file_exists($css_filepath) )? $wp_filesystem->get_contents($css_filepath) : '';

							//If the correlating .css file doesn't contain a comment to prevent overwriting
							if ( !strpos(strtolower($existing_css_contents), 'scss disabled') ){
								$this_scss_contents = $wp_filesystem->get_contents($scss_file); //Copy SCSS file contents

								//Catch fatal compilation errors when PHP v7.0+ to provide additional information without crashing
								if ( version_compare(phpversion(), '7.0.0', '>=') ){ //@todo: remove this conditional once PHP7 is widely enough used.
									try {
										$compiled_css = $this->scss->compile($this_scss_contents, $scss_file); //Compile the SCSS
									} catch (\Throwable $error){
										$unprotected_array = (array) $error;
										$prefix = chr(0) . '*' . chr(0);

										$sass_errors[] = array(
											'file' => $scss_file,
											'message' => $unprotected_array[$prefix . 'message']
										);

										continue; //Skip the file that contains errors
									}
								} else {
									$compiled_css = $this->scss->compile($this_scss_contents, $scss_file); //Compile the SCSS
								}

								$enhanced_css = $this->scss_post_compile($compiled_css); //Compile server-side variables into SCSS
								$wp_filesystem->put_contents($css_filepath, $enhanced_css); //Save the rendered CSS.
								$this->update_data('scss_last_processed', time());
								$this->timer('Sass File ' . $debug_name, 'end');
							}
						}
					}
				}

				$this->timer('Sass (' . $location_name . ')', 'end');
			}
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
		public function get_sass_variable($variable='$primary_color', $filepath='child', $return='value'){
			$override = apply_filters('pre_get_sass_variable', null, $variable, $filepath, $return);
			if ( isset($override) ){return $override;}

			$this->timer('Sass Variable', 'start', 'Sass');

			//Use the passed variables file location, or choose from the passed theme
			if ( $filepath === 'parent' ){
				$filepath = get_template_directory() . '/assets/scss/partials/_variables.scss';
			} elseif ( $filepath === 'child' && is_child_theme() ){
				$filepath = get_stylesheet_directory() . '/assets/scss/partials/_variables.scss';
			}

			$variable_name = $this->normalize_color_name($variable);
			$transient_name = 'nebula_sass_variable_' . $variable_name; //Does this need to be more unique (to include the location too)? Cannot just append $filepath...

			$scss_variables = get_transient($transient_name);
			if ( empty($scss_variables) || $this->is_debug() ){
				if ( !file_exists($filepath) ){
					return false;
				}

				WP_Filesystem();
				global $wp_filesystem;
				$scss_variables = $wp_filesystem->get_contents($filepath);
				set_transient($transient_name, $scss_variables, HOUR_IN_SECONDS*12); //1 hour cache
			}

			preg_match('/(?<comment>\/\/|\/\*\s?)?\$' . $variable_name . ':\s?(?<value>.*)(;|\s?!default;)(.*$)?/m', $scss_variables, $matches);
			$this->timer('Sass Variable', 'end');

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
					return $this->linear_channel($kwargs['php_color_value'][1]);
				},
				array('php_color_value')
			);

			//Calculate the luminance for a color.
			$scss->registerFunction(
				'php_luminance',
				function($args, $kwargs) {
					return $this->luminance($kwargs['php_color'][1]);
				},
				array('php_color')
			);

			//Calculate the contrast ratio between two colors.
			$scss->registerFunction(
				'php_contrast',
				function($args, $kwargs) {
					return $this->contrast($kwargs['php_back'][1], $kwargs['php_front'][1]);
				},
				array('php_back', 'php_front')
			);
		}
	}
}