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
		/** @var Nebula_Cache $cacheAdapter */
		protected static $cacheAdapter;

		/** @var HTMLPurifier $purifier */

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
				self::purify($str);
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
		 * Use ezyang/htmlpurifier to prevent XSS but still allow HTML
		 *
		 * @param string $str
		 * @return string
		 * @throws Exception
		 */
		public static function purify($str)
		{
			$cached = self::getCacheAdapter()->get($str);
			if ($cached) {
				return $cached;
			}

			/** @var HTMLPurifier $purifier */
			$purifier = self::getHtmlPurifier();
			$clean = $purifier->purify($str);

			// Cache the HTMLPurifier result (index, value) for future executions:
			self::getCacheAdapter()->set($str, $clean);
			return $clean;
		}

		/**
		 * @return HTMLPurifier
		 */
		public static function getHtmlPurifier()
		{
			if (!isset(self::$purifier)) {
				self::$purifier = new HTMLPurifier();
			}
			return self::$purifier;
		}

		/**
		 * Retrieve an item from the cache
		 *
		 * @param string $str
		 * @return string|null
		 * @return null|string
		 * @throws Exception
		 */
		public static function getPurifyCache($str)
		{
			return self::getCacheAdapter()->get($str);
		}

		/**
		 * @return Nebula_Cache
		 * @throws Exception
		 */
		public static function getCacheAdapter()
		{
			if (!isset(self::$cacheAdapter)) {
				self::setCacheAdapter();
			}
			return self::$cacheAdapter;
		}

		/**
		 * @return Nebula_Cache_Filesystem
		 * @throws Exception
		 */
		public static function getDefaultCacheAdapter()
		{
			$keyFile = get_template_directory() . '/assets/cache/keyfile.secret.php';
			if (is_readable($keyFile)) {
				// Key file exists. Load the key object from it.
				$key = include $keyFile;
			} else {
				// We need to ensure the cache directory exists
				$dir = get_template_directory() . '/assets/cache';
				if (!is_dir($dir)) {
					mkdir($dir, 0777);
				}
				$key = random_bytes(64);
				file_put_contents($keyFile, '<?php return pack("H*", "' . bin2hex($key) . '");');
			}
			return new Nebula_Cache_Filesystem($key);
		}

		/**
		 * @param Nebula_Cache|null $cache
		 * @throws Exception
		 */
		public static function setCacheAdapter(Nebula_Cache $cache = null)
		{
			if (is_null($cache)) {
				$cache = self::getDefaultCacheAdapter();
			}
			self::$cacheAdapter = $cache;
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
