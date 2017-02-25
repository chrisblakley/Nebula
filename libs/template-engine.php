<?php
/**
 * Template_Engine
 *
 * @package     Nebula\Template_Engine
 * @since       1.0.0
 * @author      Ruben Garcia
 * @contributor Chris Blakley
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'Nebula_Template_Engine' ) ) {

    class Nebula_Template_Engine {

        public $templates;

        public function __construct() {
            $this->templates = array();

            // Default wordpress query templates
            $query_templates = array(
                'archive',
                'index',
                '404',
                'author',
                'category',
                'tag',
                'taxonomy',
                'date',
                'home',
                'front_page',
                'page',
                'search',
                'single',
                'embed',
                'singular',
                'attachment',
            );

            // Add filter hook to any query template
            foreach( $query_templates as $query_template ) {
                add_filter( "{$query_template}_template_hierarchy", array( $this, 'template_hierarchy' ));
            }

            // Add new post type templates
            add_action( 'init',  array( $this, 'post_type_templates' ), 999 );

            // Search for plugin templates
            add_filter( 'template_include', array( $this, 'template_include' ) );
        }

        /**
         * Stores last wordpress searched templates into $this->templates
         *
         * @param $templates
         * @return mixed
         */
        public function template_hierarchy( $templates ) {
            $this->templates = $templates;

            return $templates;
        }

        public function post_type_templates() {
            foreach ( get_post_types( '', 'names' ) as $post_type ) {
                add_filter( "theme_{$post_type}_templates", array($this, 'plugins_templates'), 10, 4);
            }
        }

        /**
         * Registers plugin page templates
         *
         * @param $post_templates
         * @param \WP_Theme $wp_theme
         * @param $post
         * @param $post_type
         * @return mixed
         */
        public function plugins_templates( $post_templates, $wp_theme, $post, $post_type ) {
            $plugins_templates = array();

            foreach(nebula()->plugins as $nebula_plugin => $nebula_plugin_features) {
                if( $nebula_plugin_features['templates'] ) {
                    $files = (array) $this->scandir( $nebula_plugin_features['path'] . 'templates', 'php', 1 );

                    foreach ( $files as $file => $full_path ) {
                        if ( ! preg_match( '|Template Name:(.*)$|mi', file_get_contents( $full_path ), $header ) ) {
                            continue;
                        }

                        $types = array( 'page' );
                        if ( preg_match( '|Template Post Type:(.*)$|mi', file_get_contents( $full_path ), $type ) ) {
                            $types = explode( ',', _cleanup_header_comment( $type[1] ) );
                        }

                        foreach ( $types as $type ) {
                            $type = sanitize_key( $type );
                            if ( ! isset( $plugins_templates[ $type ] ) ) {
                                $plugins_templates[ $type ] = array();
                            }

                            $plugins_templates[ $type ][ $file ] = _cleanup_header_comment( $header[1] );
                        }
                    }

                    //@TODO "Nebula Template Engine" 0: Caching found plugin templates
                }
            }

            if( isset( $plugins_templates[$post_type] ) && ! empty( $plugins_templates[$post_type] ) ) {
                return array_merge( $post_templates, $plugins_templates[$post_type] );
            } else {
                return $post_templates;
            }
        }

        // Function taken from WP_Theme
        private function scandir( $path, $extensions = null, $depth = 0, $relative_path = '' ) {
            if ( ! is_dir( $path ) )
                return false;

            if ( $extensions ) {
                $extensions = (array) $extensions;
                $_extensions = implode( '|', $extensions );
            }

            $relative_path = trailingslashit( $relative_path );
            if ( '/' == $relative_path )
                $relative_path = '';

            $results = scandir( $path );
            $files = array();

            foreach ( $results as $result ) {
                if ( '.' == $result[0] )
                    continue;
                if ( is_dir( $path . '/' . $result ) ) {
                    if ( ! $depth || 'CVS' == $result )
                        continue;
                    $found = $this->scandir( $path . '/' . $result, $extensions, $depth - 1 , $relative_path . $result );
                    $files = array_merge_recursive( $files, $found );
                } elseif ( ! $extensions || preg_match( '~\.(' . $_extensions . ')$~', $result ) ) {
                    $files[ $relative_path . $result ] = $path . '/' . $result;
                }
            }

            return $files;
        }

        /**
         * Search the template in nebula registered plugins if not it child
         *
         * @param $template
         * @return string
         */
        public function template_include( $template  ) {
            if( is_child_theme() && substr( $template, 0, strlen(STYLESHEETPATH) ) === STYLESHEETPATH ) {
                // If template found comes from a child theme, then return
                return $template;
            } else {
                // Search in all registered plugins (in reversed order) template folder to check if template exists
                foreach(array_reverse(nebula()->plugins) as $nebula_plugin => $nebula_plugin_features) {
                    if($nebula_plugin_features['templates']) {
                        foreach ($this->templates as $template_name) {
                            if (file_exists($nebula_plugin_features['path'] . 'templates/' . $template_name)) {
                                // Returns first template found (last plugin template registered)
                                return $nebula_plugin_features['path'] . 'templates/' . $template_name;
                            }
                        }
                    }
                }
            }

            return $template;
        }

    }

}// End if class_exists check