<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !trait_exists('Sass') ){
	trait Sass {
		public function hooks(){
			add_action('init', array($this, 'scss_controller'));
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
			if ( !is_writable(get_template_directory()) ){
				trigger_error('The template directory is not writable. Can not compile Sass files!', E_USER_NOTICE);
				return false;
			}

			if ( $this->get_option('scss') ){
				//Nebula SCSS locations
				$scss_locations = array(
					'parent' => array(
						'directory' => get_template_directory(),
						'uri' => get_template_directory_uri(),
						'imports' => array(get_template_directory() . '/assets/scss/partials/')
					)
				);

				//Child theme SCSS locations
				if ( is_child_theme() ){
					$scss_locations['parent']['imports'][] = get_stylesheet_directory() . '/assets/scss/partials/';

					$scss_locations['child'] = array(
						'directory' => get_stylesheet_directory(),
						'uri' => get_stylesheet_directory_uri(),
						'imports' => array(get_stylesheet_directory() . '/assets/scss/partials/')
					);
				}

				//Registered plugins SCSS locations
				foreach ( $this->plugins as $plugin_name => $plugin_features ){
					if ( isset($plugin_features['scss']) && is_array($plugin_features['scss']) ){
						$scss_locations[$plugin_name] = array(
							'directory' => $plugin_features['scss']['directory'],
							'uri' => $plugin_features['scss']['uri'],
							'imports' => $plugin_features['scss']['imports']
						);
					}
				}

				//Allow for additional Sass locations to be included. Imports can be an array of directories.
				$additional_scss_loactions = apply_filters('nebula_scss_locations', array());
				$all_scss_locations = array_merge($scss_locations, $additional_scss_loactions);

				//Check if all Sass files should be rendered
				$force_all = false;
				if ( (isset($_GET['sass']) || isset($_GET['scss']) || isset($_GET['settings-updated']) || $this->get_data('need_sass_compile') === 'true') && $this->is_staff() ){
					$force_all = true;
				}

				$this->update_data('need_sass_compile', 'true'); //Set this to true as we are compiling so we can use it as a flag to run some thing only once.

				//Find and render .scss files at each location
				foreach ( $all_scss_locations as $scss_location_name => $scss_location_paths ){
					$this->render_scss($scss_location_name, $scss_location_paths, $force_all);
				}

				$this->update_data('need_sass_compile', 'false'); //Set it to false after Sass is finished

				//If SCSS has not been rendered in 1 month, disable the option.
				if ( time()-$this->get_data('scss_last_processed') >= MONTH_IN_SECONDS ){
					$this->update_option('scss', 'disabled');
				}
			} elseif ( $this->is_dev() && !$this->is_admin_page() && (isset($_GET['sass']) || isset($_GET['scss'])) ){
				trigger_error('Sass can not compile because it is disabled in Nebula Functions.', E_USER_NOTICE);
			}
		}

		//Render scss files
		public function render_scss($location_name=false, $location_paths=false, $force_all=false){
			$override = apply_filters('pre_nebula_render_scss', null, $location_name, $location_paths, $force_all);
			if ( isset($override) ){return;}

			if ( $this->get_option('scss') && !empty($location_name) && !empty($location_paths) ){
				//Require SCSSPHP
				require_once(get_template_directory() . '/inc/vendor/scssphp/scss.inc.php'); //SCSSPHP is a compiler for SCSS 3.x
				$scss = new \Leafo\ScssPhp\Compiler();

				//Register import directories
				if ( !is_array($location_paths['imports']) ){
					$location_paths['imports'] = array($location_paths['imports']);
				}
				foreach ( $location_paths['imports'] as $imports_directory ){
					$scss->addImportPath($imports_directory);
				}

				//Set compiling options
				if ( $this->get_option('minify_css') && !$this->is_debug() ){
					$scss->setFormatter('Leafo\ScssPhp\Formatter\Compressed'); //Minify CSS (while leaving "/*!" comments for WordPress).
				} else {
					$scss->setFormatter('Leafo\ScssPhp\Formatter\Compact'); //Compact, but readable, CSS lines
					if ( $this->is_debug() ){
						$scss->setLineNumberStyle(\Leafo\ScssPhp\Compiler::LINE_COMMENTS); //Adds line number reference comments in the rendered CSS file for debugging.
					}
				}

				//Variables
				$nebula_scss_variables = array(
					'template_directory' => '"' . get_template_directory_uri() . '"',
					'stylesheet_directory' => '"' . get_stylesheet_directory_uri() . '"',
					'this_directory' => '"' . $location_paths['uri'] . '"',
					'primary_color' => get_theme_mod('nebula_primary_color', $this->sass_color('primary')), //From Customizer or child theme Sass variable
					'secondary_color' => get_theme_mod('nebula_secondary_color', $this->sass_color('secondary')), //From Customizer or child theme Sass variable
					'background_color' => get_theme_mod('nebula_background_color', $this->sass_color('background')), //From Customizer or child theme Sass variable

					//@todo "Nebula" 0: Having difficulty reseting the Customizer... Once it's used, it'll never fall back to the 2nd parameter above. Below works, but doesn't use the Customizer...
					//'primary_color' => $this->sass_color('primary'),
					//'secondary_color' => $this->sass_color('secondary'),
					//'background_color' => $this->sass_color('background'),
				);
				$additional_scss_variables = apply_filters('nebula_scss_variables', array());
				$all_scss_variables = array_merge($nebula_scss_variables, $additional_scss_variables);
				$scss->setVariables($nebula_scss_variables);

				//Imports/Partials (find the last modified time)
				$latest_import = 0;
				foreach ( glob($imports_directory . '*') as $import_file ){
					if ( filemtime($import_file) > $latest_import ){
						$latest_import = filemtime($import_file);
					}
				}

				//Combine Developer Stylesheets
				if ( $this->get_option('dev_stylesheets') ){
					$this->combine_dev_stylesheets($location_paths['directory'] . '/assets', $location_paths['uri'] . '/assets');
				}

				//Compile each SCSS file
				foreach ( glob($location_paths['directory'] . '/assets/scss/*.scss') as $file ){ //@TODO "Nebula" 0: Change to glob_r() but will need to create subdirectories if they don't exist.
					$file_path_info = pathinfo($file);

					//Skip file conditions (only if not forcing all)
					if ( empty($force_all) ){
						$is_wireframing_file = $file_path_info['filename'] == 'wireframing' && !$this->get_option('prototype_mode'); //If file is wireframing.scss but wireframing functionality is disabled, skip file.
						$is_dev_file = $file_path_info['filename'] == 'dev' && !$this->get_option('dev_stylesheets'); //If file is dev.scss but dev stylesheets functionality is disabled, skip file.
						$is_admin_file = !$this->is_admin_page() && in_array($file_path_info['filename'], array('login', 'admin', 'tinymce')); //If viewing front-end, skip WP admin files.
						if ( $is_wireframing_file || $is_dev_file || $is_admin_file ){
							continue;
						}
					}

					//If file exists, and has .scss extension, and doesn't begin with "_".
					if ( is_file($file) && $file_path_info['extension'] == 'scss' && $file_path_info['filename'][0] != '_' ){
						$css_filepath = ( $file_path_info['filename'] == 'style' )? $location_paths['directory'] . '/style.css': $location_paths['directory'] . '/assets/css/' . $file_path_info['filename'] . '.css'; //style.css to the root directory. All others to the /css directory in the /assets/scss directory.
						wp_mkdir_p($location_paths['directory'] . '/assets/css'); //Create the /css directory (in case it doesn't exist already).

						//If style.css has been edited after style.scss, save backup but continue compiling SCSS
						if ( (is_child_theme() && $location_name != 'parent' ) && ($file_path_info['filename'] == 'style' && file_exists($css_filepath) && $this->get_data('scss_last_processed') != '0' && $this->get_data('scss_last_processed')-filemtime($css_filepath) < -30) ){
							copy($css_filepath, $css_filepath . '.bak'); //Backup the style.css file to style.css.bak
							if ( $this->is_dev() || current_user_can('manage_options') ){
								global $scss_debug_ref;
								$scss_debug_ref = $location_name . ':';
								$scss_debug_ref .= ($this->get_data('scss_last_processed')-filemtime($css_filepath));
								add_action('wp_head', array($this, 'scss_console_warning')); //Call the console error note
							}
						}

						//If .css file doesn't exist, or is older than .scss file (or any partial), or is debug mode, or forced
						if ( !file_exists($css_filepath) || filemtime($file) > filemtime($css_filepath) || $latest_import > filemtime($css_filepath) || $this->is_debug() || $force_all ){
							ini_set('memory_limit', '512M'); //Increase memory limit for this script. //@TODO "Nebula" 0: Is this the best thing to do here? Other options?
							WP_Filesystem();
							global $wp_filesystem;
							$existing_css_contents = ( file_exists($css_filepath) )? $wp_filesystem->get_contents($css_filepath) : '';

							//If the correlating .css file doesn't contain a comment to prevent overwriting
							if ( !strpos(strtolower($existing_css_contents), 'scss disabled') ){
								$this_scss_contents = $wp_filesystem->get_contents($file); //Copy SCSS file contents
								$compiled_css = $scss->compile($this_scss_contents); //Compile the SCSS
								$enhanced_css = $this->scss_post_compile($compiled_css); //Compile server-side variables into SCSS
								$wp_filesystem->put_contents($css_filepath, $enhanced_css); //Save the rendered CSS.
								$this->update_data('scss_last_processed', time());
							}
						}
					}
				}
			}
		}

		//Log Sass .bak note in the browser console
		public function scss_console_warning(){
			global $scss_debug_ref;
			echo '<script>console.warn("Warning: Sass compiling is enabled, but it appears that style.css has been manually updated (Reference: ' . $scss_debug_ref . 's)! A style.css.bak backup has been made. If not using Sass, disable it in Nebula Options. Otherwise, make all edits in style.scss in the /assets/scss directory!");</script>';
		}

		//Combine developer stylesheets
		public function combine_dev_stylesheets($directory=null, $directory_uri=null){
			$override = apply_filters('pre_nebula_combine_dev_stylesheets', null, $directory, $directory_uri);
			if ( isset($override) ){return;}

			if ( empty($directory) ){
				trigger_error('Dev stylesheet directories must be specified for files to be combined.', E_USER_NOTICE);
				return false;
			}

			WP_Filesystem();
			global $wp_filesystem;

			$file_counter = 0;
			$automation_warning = "/**** Warning: This is an automated file! Anything added to this file manually will be removed! ****/\r\n\r\n";
			$dev_stylesheet_files = glob($directory . '/scss/dev/*css');
			$dev_scss_file = $directory . '/scss/dev.scss';

			if ( !empty($dev_stylesheet_files) || strlen($dev_scss_file) > strlen($automation_warning)+10 ){ //If there are dev SCSS (or CSS) files -or- if dev.scss needs to be reset
				$wp_filesystem->put_contents($directory . '/scss/dev.scss', $automation_warning); //Empty /assets/scss/dev.scss
			}
			foreach ( $dev_stylesheet_files as $file ){
				$file_path_info = pathinfo($file);
				if ( is_file($file) && in_array($file_path_info['extension'], array('css', 'scss')) ){
					$file_counter++;

					//Include partials in dev.scss //@todo "Nebula" 0: Find a way to prevent hard-coding these partial files. Maybe tap into the $location_paths['imports'] from above (need a specific order other than alphabetical?)?
					if ( $file_counter == 1 ){
						$import_partials = '';
						$import_partials .= "@import '../../../../Nebula-master/assets/scss/partials/variables';\r\n";
						$import_partials .= "@import '../partials/variables';\r\n";
						$import_partials .= "@import '../../../../Nebula-master/assets/scss/partials/mixins';\r\n";
						$import_partials .= "@import '../../../../Nebula-master/assets/scss/partials/helpers';\r\n";

						$wp_filesystem->put_contents($dev_scss_file, $automation_warning . $import_partials . "\r\n");
					}

					$this_scss_contents = $wp_filesystem->get_contents($file); //Copy file contents
					$empty_scss = ( $this_scss_contents == '' )? ' (empty)' : '';
					$dev_scss_contents = $wp_filesystem->get_contents($directory . '/scss/dev.scss');

					$dev_scss_contents .= "\r\n\r\n\r\n/*! ==========================================================================\r\n   " . 'File #' . $file_counter . ': ' . $directory_uri . "/scss/dev/" . $file_path_info['filename'] . '.' . $file_path_info['extension'] . $empty_scss . "\r\n   ========================================================================== */\r\n\r\n" . $this_scss_contents . "\r\n\r\n/* End of " . $file_path_info['filename'] . '.' . $file_path_info['extension'] . " */\r\n\r\n\r\n";

					$wp_filesystem->put_contents($directory . '/scss/dev.scss', $dev_scss_contents);
				}
			}
			if ( $file_counter > 0 ){
				add_action('wp_enqueue_scripts', function(){
					wp_enqueue_style('nebula-dev_styles-parent', get_template_directory_uri() . '/assets/css/dev.css?c=' . rand(1, 99999), array('nebula-main'), null);
					wp_enqueue_style('nebula-dev_styles-child', get_stylesheet_directory_uri() . '/assets/css/dev.css?c=' . rand(1, 99999), array('nebula-main'), null);
				});
			}
		}

		//Compile server-side variables into SCSS
		public function scss_post_compile($scss){
			$override = apply_filters('pre_nebula_scss_post_compile', null, $scss);
			if ( isset($override) ){return;}

			$scss = preg_replace("(" . str_replace('/', '\/', get_template_directory()) . ")", '', $scss); //Reduce theme path for SCSSPHP debug line comments
			$scss = preg_replace("(" . str_replace('/', '\/', get_stylesheet_directory()) . ")", '', $scss); //Reduce theme path for SCSSPHP debug line comments (For child themes)
			do_action('nebula_scss_post_compile');
			$scss .= PHP_EOL . '/* ' . date('l, F j, Y \a\t g:i:s A', time()) . ' */';

			//Run these once
			if ( $this->get_data('need_sass_compile') != 'false' ){
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

		//Pull certain colors from .../mixins/_variables.scss
		public function sass_color($color='primary', $theme='child'){
			$override = apply_filters('pre_sass_color', null, $color, $theme);
			if ( isset($override) ){return;}

			if ( is_child_theme() && $theme == 'child' ){
				$assets_directory = get_stylesheet_directory() . '/assets';
				$transient_name = 'nebula_scss_child_variables';
			} else {
				$assets_directory = get_template_directory() . '/assets';
				$transient_name = 'nebula_scss_variables';
			}

			$scss_variables = get_transient($transient_name);
			if ( empty($scss_variables) || $this->is_debug() ){
				$variables_file = $assets_directory . '/scss/partials/_variables.scss';
				if ( !file_exists($variables_file) ){
					return false;
				}

				WP_Filesystem();
				global $wp_filesystem;
				$scss_variables = $wp_filesystem->get_contents($variables_file);
				set_transient($transient_name, $scss_variables, HOUR_IN_SECONDS*12); //1 hour cache
			}

			switch ( str_replace(array('$', ' ', '_', '-'), '', $color) ){
				case 'primary':
				case 'primarycolor':
				case 'first':
				case 'main':
				case 'brand':
					$color_search = 'primary_color';
					break;
				case 'secondary':
				case 'secondarycolor':
				case 'second':
					$color_search = 'secondary_color';
					break;
				case 'tertiary':
				case 'tertiarycolor':
				case 'third':
					$color_search = 'tertiary_color';
					break;
				case 'background':
				case 'backgroundcolor':
				case 'bg':
					$color_search = 'background_color';
					break;
				default:
					return false;
					break;
			}

			preg_match('/\$' . $color_search . ': (\S*)(;| !default;)/', $scss_variables, $matches);
			return $matches[1];
		}
	}
}