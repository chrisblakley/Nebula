<?php
/**
 * Sass
 *
 * @package     Nebula\Sass
 * @since       1.0.0
 * @author      Chris Blakley
 * @contributor Ruben Garcia
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'Nebula_Sass' ) ) {

    class Nebula_Sass {

        public function __construct() {
            /*==========================
                SCSS Compiling
             ===========================*/

            if ( nebula_option('scss', 'enabled') ){

                if ( is_writable(get_template_directory()) ){
                    add_action('init', array( $this, 'render_scss' ) );
                }

                add_action('init', array( $this, 'render_registered_scss' ), 99999); //Render registered plugins' Sass files (priority set to 99999 to run after all plugin registration)
            }
        }

        //Render scss files
        public function render_scss($child=false){
            $override = apply_filters('pre_nebula_render_scss', false, $child);
            if ( $override !== false ){return $override;}

            if ( nebula_option('scss', 'enabled') ){
                $compile_all = false;
                if ( isset($_GET['sass']) || isset($_GET['settings-updated']) && is_staff() ){
                    $compile_all = true;
                }

                $theme_directory = get_template_directory();
                $theme_directory_uri = get_template_directory_uri();
                if ( $child ){
                    $theme_directory = get_stylesheet_directory();
                    $theme_directory_uri = get_stylesheet_directory_uri();
                }

                $stylesheets_directory = $theme_directory . '/stylesheets';
                $stylesheets_directory_uri = $theme_directory_uri . '/stylesheets';

                require_once(get_template_directory() . '/includes/libs/scssphp/scss.inc.php'); //SCSSPHP is a compiler for SCSS 3.x
                $scss = new \Leafo\ScssPhp\Compiler();
                $scss->addImportPath($stylesheets_directory . '/scss/partials/');

                if ( nebula_option('minify_css', 'enabled') && !is_debug() ){
                    $scss->setFormatter('Leafo\ScssPhp\Formatter\Compressed'); //Minify CSS (while leaving "/*!" comments for WordPress).
                } else {
                    $scss->setFormatter('Leafo\ScssPhp\Formatter\Compact'); //Compact, but readable, CSS lines
                    if ( is_debug() ){
                        $scss->setLineNumberStyle(\Leafo\ScssPhp\Compiler::LINE_COMMENTS); //Adds line number reference comments in the rendered CSS file for debugging.
                    }
                }

                //Variables
                $scss->setVariables(array(
                    'template_directory' => '"' . get_template_directory_uri() . '"',
                    'stylesheet_directory' => '"' . get_stylesheet_directory_uri() . '"',
                    'primary_color' => get_theme_mod('nebula_primary_color', '#0098d7'), //From Customizer
                    'secondary_color' => get_theme_mod('nebula_secondary_color', '#95d600'), //From Customizer
                    'background_color' => get_theme_mod('nebula_background_color', '#f6f6f6'), //From Customizer
                ));

                //Partials
                $latest_partial = 0;
                foreach ( glob($stylesheets_directory . '/scss/partials/*') as $partial_file ){
                    if ( filemtime($partial_file) > $latest_partial ){
                        $latest_partial = filemtime($partial_file);
                    }
                }

                //Combine Developer Stylesheets
                if ( nebula_option('dev_stylesheets', 'enabled') ){
                    nebula_combine_dev_stylesheets($stylesheets_directory, $stylesheets_directory_uri);
                }

                //Compile each SCSS file
                foreach ( glob($stylesheets_directory . '/scss/*.scss') as $file ){ //@TODO "Nebula" 0: Change to glob_r() but will need to create subdirectories if they don't exist.
                    $file_path_info = pathinfo($file);

                    if ( $file_path_info['filename'] == 'wireframing' && nebula_option('prototype_mode', 'disabled') ){ //If file is wireframing.scss but wireframing functionality is disabled, skip file.
                        continue;
                    }
                    if ( $file_path_info['filename'] == 'dev' && nebula_option('dev_stylesheets', 'disabled') ){ //If file is dev.scss but dev stylesheets functionality is disabled, skip file.
                        continue;
                    }
                    if ( !is_admin() && in_array($file_path_info['filename'], array('login', 'admin', 'tinymce')) ){ //If viewing front-end, skip WP admin files.
                        continue;
                    }

                    if ( is_file($file) && $file_path_info['extension'] == 'scss' && $file_path_info['filename'][0] != '_' ){ //If file exists, and has .scss extension, and doesn't begin with "_".
                        $css_filepath = ( $file_path_info['filename'] == 'style' )? $theme_directory . '/style.css': $stylesheets_directory . '/css/' . $file_path_info['filename'] . '.css';
                        wp_mkdir_p($stylesheets_directory . '/css'); //Create the /css directory (in case it doesn't exist already).

                        //If style.css has been edited after style.scss, save backup but continue compiling SCSS
                        if ( ($child && is_child_theme()) && ($file_path_info['filename'] == 'style' && file_exists($css_filepath) && nebula_data('scss_last_processed') != '0' && nebula_data('scss_last_processed')-filemtime($css_filepath) < -30) ){
                            copy($css_filepath, $css_filepath . '.bak'); //Backup the style.css file to style.css.bak
                            if ( is_dev() || current_user_can('manage_options') ){
                                global $scss_debug_ref;
                                $scss_debug_ref = ( $child )? 'C' : 'P';
                                $scss_debug_ref .= (nebula_data('scss_last_processed')-filemtime($css_filepath));
                                add_action('wp_head', 'nebula_scss_console_warning'); //Call the console error note
                            }
                        }

                        if ( !file_exists($css_filepath) || filemtime($file) > filemtime($css_filepath) || $latest_partial > filemtime($css_filepath) || is_debug() || $compile_all ){ //If .css file doesn't exist, or is older than .scss file (or any partial), or is debug mode, or forced
                            ini_set('memory_limit', '512M'); //Increase memory limit for this script. //@TODO "Nebula" 0: Is this the best thing to do here? Other options?
                            WP_Filesystem();
                            global $wp_filesystem;
                            $existing_css_contents = ( file_exists($css_filepath) )? $wp_filesystem->get_contents($css_filepath) : '';

                            if ( !strpos(strtolower($existing_css_contents), 'scss disabled') ){ //If the correlating .css file doesn't contain a comment to prevent overwriting
                                $this_scss_contents = $wp_filesystem->get_contents($file); //Copy SCSS file contents
                                $compiled_css = $scss->compile($this_scss_contents); //Compile the SCSS
                                $enhanced_css = nebula_scss_post_compile($compiled_css); //Compile server-side variables into SCSS
                                $wp_filesystem->put_contents($css_filepath, $enhanced_css); //Save the rendered CSS.
                                nebula_update_data('scss_last_processed', time());
                            }
                        }
                    }
                }

                if ( !$child && is_child_theme() ){ //If not in the second (child) pass, and is a child theme.
                    nebula_render_scss(true); //Re-run on child theme stylesheets
                }

                //If SCSS has not been rendered in 1 month, disable the option.
                if ( time()-nebula_data('scss_last_processed') >= 2592000 ){
                    nebula_update_option('scss', 'disabled');
                }
            }
        }

        //@TODO "Nebula" 0: Update this so that it doesn't need to be a separate function than nebula_render_scss()
        public function render_registered_scss(){
            global $nebula_plugins;

            $override = apply_filters('pre_nebula_render_registered_scss', false);
            if ( $override !== false ){return $override;}

            if ( nebula_option('scss', 'enabled') ) {
                if( is_array($nebula_plugins) && !empty($nebula_plugins) ) {
                    $compile_all = false;
                    if ( isset($_GET['sass']) || isset($_GET['scss']) || isset($_GET['settings-updated']) && is_staff() ){
                        $compile_all = true;
                    }

                    foreach ( $nebula_plugins as $nebula_plugin => $nebula_plugin_features ){
                        if ( $nebula_plugin_features['stylesheets'] && is_writable($nebula_plugin_features['path'] . 'stylesheets') ){

                            require_once(get_template_directory() . '/includes/libs/scssphp/scss.inc.php'); //SCSSPHP is a compiler for SCSS 3.x
                            $scss = new \Leafo\ScssPhp\Compiler();
                            $scss->addImportPath($nebula_plugin_features['path'] . 'stylesheets/scss/partials/');

                            if ( nebula_option('minify_css', 'enabled') && !is_debug() ){
                                $scss->setFormatter('Leafo\ScssPhp\Formatter\Compressed'); //Minify CSS (while leaving "/*!" comments for WordPress).
                            } else {
                                $scss->setFormatter('Leafo\ScssPhp\Formatter\Compact'); //Compact, but readable, CSS lines
                                if ( is_debug() ){
                                    $scss->setLineNumberStyle(\Leafo\ScssPhp\Compiler::LINE_COMMENTS); //Adds line number reference comments in the rendered CSS file for debugging.
                                }
                            }

                            //Variables
                            $scss->setVariables(array(
                                'template_url' => '"' . get_template_directory_uri() . '"',
                                'template_directory' => '"' . get_template_directory() . '"',
                                'stylesheet_url' => '"' . get_stylesheet_directory_uri() . '"',
                                'stylesheet_directory' => '"' . get_stylesheet_directory() . '"',
                                'primary_color' => get_theme_mod('nebula_primary_color', '#0098d7'), //From Customizer
                                'secondary_color' => get_theme_mod('nebula_secondary_color', '#95d600'), //From Customizer
                                'background_color' => get_theme_mod('nebula_background_color', '#f6f6f6'), //From Customizer
                            ));

                            //Partials
                            $latest_partial = 0;
                            foreach ( glob($nebula_plugin_features['path'] . 'stylesheets/scss/partials/*') as $partial_file ){
                                if ( filemtime($partial_file) > $latest_partial ){
                                    $latest_partial = filemtime($partial_file);
                                }
                            }

                            //Combine Developer Stylesheets
                            if ( nebula_option('dev_stylesheets', 'enabled') ){
                                //@TODO "Nebula" 0: Make nebula_combine_dev_stylesheets work with plugins directories
                                //nebula_combine_dev_stylesheets($stylesheets_directory, $stylesheets_directory_uri);
                            }

                            //Compile each SCSS file
                            foreach ( glob($nebula_plugin_features['path'] . 'stylesheets/scss/*.scss') as $file ){ //@TODO "Nebula" 0: Change to glob_r() but will need to create subdirectories if they don't exist.
                                $file_path_info = pathinfo($file);

                                if ( $file_path_info['filename'] == 'wireframing' && nebula_option('prototype_mode', 'disabled') ){ //If file is wireframing.scss but wireframing functionality is disabled, skip file.
                                    continue;
                                }
                                if ( $file_path_info['filename'] == 'dev' && nebula_option('dev_stylesheets', 'disabled') ){ //If file is dev.scss but dev stylesheets functionality is disabled, skip file.
                                    continue;
                                }
                                if ( !is_admin() && in_array($file_path_info['filename'], array('login', 'admin', 'tinymce')) ){ //If viewing front-end, skip WP admin files.
                                    continue;
                                }

                                if ( is_file($file) && $file_path_info['extension'] == 'scss' && $file_path_info['filename'][0] != '_' ){ //If file exists, and has .scss extension, and doesn't begin with "_".
                                    $css_filepath = $nebula_plugin_features['path'] . 'stylesheets/css/' . $file_path_info['filename'] . '.css'; //For plugins, all css files will come in stylesheets/css directory
                                    wp_mkdir_p($nebula_plugin_features['path'] . 'stylesheets/css'); //Create the /css directory (in case it doesn't exist already).

                                    //If style.css has been edited after style.scss, save backup but continue compiling SCSS
                                    if ( ($file_path_info['filename'] == 'style' && file_exists($css_filepath) && nebula_data('scss_last_processed') != '0' && nebula_data('scss_last_processed')-filemtime($css_filepath) < 0) ){ //@todo "Nebula" 0: Getting a lot of false positives here
                                        copy($css_filepath, $css_filepath . '.bak'); //Backup the style.css file to style.css.bak
                                        if ( is_dev() || current_user_can('manage_options') ){
                                            global $scss_debug_ref;
                                            $scss_debug_ref = 'E'; //P for Parent, C for child and E for plugin (extension)
                                            $scss_debug_ref .= (nebula_data('scss_last_processed')-filemtime($css_filepath));
                                            add_action('wp_head', 'nebula_scss_console_warning'); //Call the console error note
                                        }
                                    }

                                    if ( !file_exists($css_filepath) || filemtime($file) > filemtime($css_filepath) || $latest_partial > filemtime($css_filepath) || is_debug() || $compile_all ){ //If .css file doesn't exist, or is older than .scss file (or any partial), or is debug mode, or forced
                                        ini_set('memory_limit', '512M'); //Increase memory limit for this script. //@TODO "Nebula" 0: Is this the best thing to do here? Other options?
                                        WP_Filesystem();
                                        global $wp_filesystem;
                                        $existing_css_contents = ( file_exists($css_filepath) )? $wp_filesystem->get_contents($css_filepath) : '';

                                        if ( !strpos(strtolower($existing_css_contents), 'scss disabled') ){ //If the correlating .css file doesn't contain a comment to prevent overwriting
                                            $this_scss_contents = $wp_filesystem->get_contents($file); //Copy SCSS file contents
                                            $compiled_css = $scss->compile($this_scss_contents); //Compile the SCSS
                                            $enhanced_css = nebula_scss_post_compile($compiled_css); //Compile server-side variables into SCSS
                                            $wp_filesystem->put_contents($css_filepath, $enhanced_css); //Save the rendered CSS.
                                            nebula_update_data('scss_last_processed', time());
                                        }
                                    }
                                }
                            }

                            //If SCSS has not been rendered in 1 month, disable the option.
                            if ( time()-nebula_data('scss_last_processed') >= 2592000 ){
                                nebula_update_option('scss', 'disabled');
                            }
                        }
                    }
                }
            }
        }

        //Log Sass .bak note in the browser console
        public function scss_console_warning(){
            global $scss_debug_ref;
            echo '<script>console.warn("Warning: Sass compiling is enabled, but it appears that style.css has been manually updated (Reference: ' . $scss_debug_ref . 's)! A style.css.bak backup has been made. If not using Sass, disable it in Nebula Options. Otherwise, make all edits in style.scss in the /stylesheets/scss directory!");</script>';
        }

        //Combine developer stylesheets
        public function combine_dev_stylesheets($directory=null, $directory_uri=null){
            $override = apply_filters('pre_nebula_combine_dev_stylesheets', false, $directory, $directory_uri);
            if ( $override !== false ){return $override;}

            if ( empty($directory) ){
                $directory = get_template_directory() . '/stylesheets';
                $directory_uri = get_template_directory_uri() . "/stylesheets";
            }

            WP_Filesystem();
            global $wp_filesystem;

            $file_counter = 0;
            $automation_warning = "/**** Warning: This is an automated file! Anything added to this file manually will be removed! ****/\r\n\r\n";
            $dev_stylesheet_files = glob($directory . '/scss/dev/*css');
            $dev_scss_file = $directory . '/scss/dev.scss';

            if ( !empty($dev_stylesheet_files) || strlen($dev_scss_file) > strlen($automation_warning)+10 ){ //If there are dev SCSS (or CSS) files -or- if dev.scss needs to be reset
                $wp_filesystem->put_contents($directory . '/scss/dev.scss', $automation_warning); //Empty /stylesheets/scss/dev.scss
            }
            foreach ( $dev_stylesheet_files as $file ){
                $file_path_info = pathinfo($file);
                if ( is_file($file) && in_array($file_path_info['extension'], array('css', 'scss')) ){
                    $file_counter++;

                    //Include partials in dev.scss
                    if ( $file_counter == 1 ){
                        $import_partials = '';
                        $import_partials .= "@import '../../../../Nebula-master/stylesheets/scss/partials/variables';\r\n";
                        $import_partials .= "@import '../partials/variables';\r\n";
                        $import_partials .= "@import '../../../../Nebula-master/stylesheets/scss/partials/mixins';\r\n";
                        $import_partials .= "@import '../../../../Nebula-master/stylesheets/scss/partials/helpers';\r\n";

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
                    wp_enqueue_style('nebula-dev_styles-parent', get_template_directory_uri() . '/stylesheets/css/dev.css?c=' . rand(1, 99999), array('nebula-main'), null);
                    wp_enqueue_style('nebula-dev_styles-child', get_stylesheet_directory_uri() . '/stylesheets/css/dev.css?c=' . rand(1, 99999), array('nebula-main'), null);
                });
            }
        }

        //Compile server-side variables into SCSS
        public function scss_post_compile($scss){
            $override = apply_filters('pre_nebula_scss_post_compile', false, $scss);
            if ( $override !== false ){return $override;}

            $scss = preg_replace("(" . str_replace('/', '\/', get_template_directory()) . ")", '', $scss); //Reduce theme path for SCSSPHP debug line comments
            $scss = preg_replace("(" . str_replace('/', '\/', get_stylesheet_directory()) . ")", '', $scss); //Reduce theme path for SCSSPHP debug line comments (For child themes)
            do_action('nebula_scss_post_compile');
            $scss .= "\r\n/* Processed on " . date('l, F j, Y \a\t g:ia', time()) . ' */';
            nebula_update_data('scss_last_processed', time());

            return $scss;
        }

        //Pull certain colors from .../mixins/_variables.scss
        public function sass_color($color='primary', $theme='child'){
            $override = apply_filters('pre_nebula_sass_color', false, $color, $theme);
            if ( $override !== false ){return $override;}

            if ( is_child_theme() && $theme == 'child' ){
                $stylesheets_directory = get_stylesheet_directory() . '/stylesheets';
                $transient_name = 'nebula_scss_child_variables';
            } else {
                $stylesheets_directory = get_template_directory() . '/stylesheets';
                $transient_name = 'nebula_scss_variables';
            }

            $scss_variables = get_transient($transient_name);
            if ( empty($scss_variables) || is_debug() ){
                $variables_file = $stylesheets_directory . '/scss/partials/_variables.scss';
                if ( !file_exists($variables_file) ){
                    return false;
                }

                WP_Filesystem();
                global $wp_filesystem;
                $scss_variables = $wp_filesystem->get_contents($variables_file);
                set_transient($transient_name, $scss_variables, 60*60); //1 hour cache
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
                default:
                    return false;
                    break;
            }

            preg_match('/\$' . $color_search . ': (\S*)(;| !default;)/', $scss_variables, $matches);
            return $matches[1];
        }

    }

}// End if class_exists check