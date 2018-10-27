<?php

/**
 * Class Nebula_Cache_Filesystem
 */
class Nebula_Cache_Filesystem implements Nebula_Cache {
	/** @var string $basePath */
	private $basePath = '';

	/** @var bool $forceSha256 */
	private $forceSha256 = false;

	/** @var string $key */
	private $key;

	/**
	 * Nebula_Cache_Filesystem constructor.
	 *
	 * @param string $key       A local secret value that is mixed in with keys
	 *                          in order to make the cache paths non-
	 *                          deterministic across various installs.
	 * @param string $basePath  The root cache directory.
	 * @param bool $forceSha256 Don't use BLAKE2b if it's available?
	 */
	public function __construct($key, $basePath = '', $forceSha256 = false){
		if ( !$basePath ){
			// Safe default
			$basePath = get_template_directory() . '/assets/cache';
			if ( !is_dir($basePath) ){
				mkdir($basePath, 0775);
			}
		}
		$this->basePath = $basePath;
		$this->key = $key;
		$this->forceSha256 = $forceSha256;
	}

	/**
	 * Get the real index.
	 *
	 * On modern systems, this will use the newer (faster, more secure) BLAKE2b hash function.
	 *
	 * On legacy systems, this will use HMAC-SHA512 truncated to 256 bits instead. SHA512 is faster on 64-bit hardware (most servers) than SHA256.
	 *
	 * @ref https://blake2.net
	 *
	 * @param string $index
	 * @return false|string
	 */
	public function getRealIndex($index){
		if (!$this->forceSha256) {
			if ( is_callable('sodium_crypto_generichash') && is_callable('sodium_bin2hex') ){
				return sodium_bin2hex(
					sodium_crypto_generichash($index, $this->key)
				);
			}
		}
		return substr(hash_hmac('sha512', $index, $this->key), 0, 64);
	}

	/**
	 * Read a value from the filesystem. Returns NULL if not cached.
	 *
	 * @param string $index
	 * @return string|null
	 */
	public function get($index){
		$index = $this->getRealIndex($index);
		$subA = substr($index, 0, 2);
		$subB = substr($index, 2, 2);

		$directory = $this->basePath . '/' . $subA  . '/' . $subB;
		if ( !is_dir($directory) ){
			// Directory doesn't exist? Automatic cache miss!
			return null;
		}
		$filename = $directory . '/' . substr($index, 4) . '.cache';
		if ( !is_readable($filename) ){
			// Cache miss
			return null;
		}
		$contents = file_get_contents($filename);
		if ( !is_string($contents) ){
			// Treat errors as cache misses
			return null;
		}
		return $contents;
	}

	/**
	 * @param string $index
	 * @return bool
	 */
	public function is_cached($index){
		$index = $this->getRealIndex($index);
		$subA = substr($index, 0, 2);
		$subB = substr($index, 2, 2);

		$directory = $this->basePath . '/' . $subA  . '/' . $subB;
		if ( !is_dir($directory) ){
			return false; //Directory doesn't exist? Automatic cache miss!
		}
		$filename = $directory . '/' . substr($index, 4) . '.cache';
		return file_exists($filename) && is_readable($filename);
	}

	/**
	 * Cache a value to the filesystem.
	 *
	 * @param string $index
	 * @param string $value
	 * @return void
	 */
	public function set($index, $value){
		$index = $this->getRealIndex($index);

		$subA = substr($index, 0, 2);
		$subB = substr($index, 2, 2);
		$directory = $this->basePath . '/' . $subA  . '/' . $subB;
		if ( !is_dir($directory) ){
			// Directory doesn't exist? Create it.
			mkdir($subA, 0775, true);
		}
		$filename = $directory . '/' . substr($index, 4) . '.cache';
		file_put_contents($filename, $value);
	}
}
