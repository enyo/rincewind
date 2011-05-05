<?php

/**
 * This file contains the Cache interface.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Cache
 */

/**
 * Thrown when something goes wrong in cache.
 */
class CacheException extends Exception {
  
}

/**
 * The cache interface defines basic cache functions.
 * Implement it with an underlying cache framework (eg: Memcache).
 * 
 * The implementing class has to make sure timeouts and such is handled correctly.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Cache
 */
interface Cache {

  /**
   * @param string $key
   * @param bool $found
   * @return mixed The value retrieved. Null if not found.
   */
  public function get($key, &$found = null);

  /**
   * @param string $key
   * @param mixed $var
   * @param int $expiration
   * @return bool True on success, false on failure
   */
  public function set($key, $var, $expiration = null);
}