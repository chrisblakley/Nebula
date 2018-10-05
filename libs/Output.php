<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

if ( !class_exists('Nebula_Output') ) {
    /**
     * Class Output
     *
     * Context-aware output escaper for Nebula.
     */
    class Nebula_Output
    {
        /**
         * @param string $str
         * @param string $context
         * @return string
         *
         * @throws Exception
         */
        public static function escape($str, $context = 'html_attr')
        {
            switch ($context) {
                case 'html':
                    return self::html($str);
                case 'attr':
                case 'html_attr':
                    return self::attr($str);
                case 'url':
                    return self::url($str);
                default:
                    return '';
            }
        }

        /**
         * Escape arbitrary data so that it's safe to output inside of an
         * HTML tag.
         *
         * @param string $str
         * @param bool $allowHtml
         * @return string
         *
         * @throws Exception
         */
        public static function html($str, $allowHtml = false)
        {
            if ($allowHtml) {
                /** @todo Hook into HTMLPurifier */
                throw new Exception('Not implemented. We need HTMLPurifier support.');
            }
            return self::attr($str);
        }

        /**
         * Escape arbitrary data so that it's safe to output inside of an
         * HTML attribute.
         *
         * Usage:
         *
         *     echo '<div data-foo="' . Nebula_Output::attr($unsafeString) . '">';
         *
         * @param string $str
         * @return string
         */
        public static function attr($str)
        {
            return htmlentities($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        /**
         * Escape arbitrary data so that it's safe to output inside of a URL.
         *
         * @param string $str
         * @return string
         */
        public static function url($str)
        {
            return urlencode($str);
        }
    }
}
