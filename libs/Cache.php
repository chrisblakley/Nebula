<?php

/**
 * Class Nebula_Cache
 */
interface Nebula_Cache {
	/**
	 * @param string $index
	 * @return string|null
	 */
	public function get($index);

	/**
	 * @param string $index
	 * @return bool
	 */
	public function is_cached($index);

	/**
	 * @param string $index
	 * @param string $value
	 * @return void
	 */
	public function set($index, $value);
}
