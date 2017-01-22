<?php

namespace Nebula {

    class Template_Engine {

        public $templates;

        public function __construct() {
            $this->templates = array();

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

            foreach($query_templates as $query_template) {
                add_filter( $query_template . '_template_hierarchy', array( $this, 'template_hierarchy' ));
            }

            add_filter( 'theme_page_templates', array( $this, 'plugins_templates' ), 10, 4 );

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
            global $nebula_plugins;

            foreach($nebula_plugins as $nebula_plugin => $nebula_plugin_features) {
                if($nebula_plugin_features['page-templates']) {
                    $files = (array) $this->scandir( $nebula_plugin_features['path'] . 'templates/page-templates', 'php', 1 );

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
                            if ( ! isset( $post_templates[ $type ] ) ) {
                                $post_templates[ $type ] = array();
                            }

                            $post_templates[ $type ][ $file ] = _cleanup_header_comment( $header[1] );
                        }
                    }

                    //@TODO "Nebula Template Engine" 0: Caching found plugin templates
                }
            }

            return $post_templates;
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
            global $nebula_plugins;

            if( is_child_theme() && substr( $template, 0, strlen(STYLESHEETPATH) ) === STYLESHEETPATH ) {
                // If template found comes from a child theme, then return
                return $template;
            } else {
                $plugin_template_paths = apply_filters( 'nebula_register_templates', array() );

                //@TODO "Nebula Template Engine" 0: We need to set a priority system between plugins

                foreach($nebula_plugins as $nebula_plugin => $nebula_plugin_features) {
                    if($nebula_plugin_features['templates']) {
                        foreach ($this->templates as $template_name) {
                            if (file_exists($nebula_plugin_features['path'] . '/' . $template_name)) {
                                return $nebula_plugin_features['path'] . '/' . $template_name;
                            }
                        }
                    }
                }
            }

            return $template;
        }
    }
}