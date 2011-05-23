<?php

/**
 * This file contains the MemcachedCache.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Cache
 */
/**
 * Including the Cache interface
 */
require_interface('Cache');

/**
 * The MemcachedCache is a wrapper for Memcached
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Cache
 */
class MemcachedCache implements Cache {

  /**
   * @var Memcached
   */
  private $memcached;

  /**
   * @param Memcached $memcached 
   */
  public function __construct(Memcached $memcached = null) {
    $this->memcached = $memcached;
  }

  /**
   * Creates an instance of Memcached and stores it.
   * @param string $host
   * @param int $port 
   * @return Memcached
   * @see $memcached
   */
  public function setupMemcached($host, $port) {
    $this->memcached = new Memcached();
    $this->memcached->addServer($host, $port);
    return $this->memcached;
  }

  /**
   * @param string $key
   * @param bool $found
   * @return mixed The value retrieved. Null if not found.
   */
  public function get($key, &$found = null) {
    if ( ! $this->memcached) throw new CacheException('No memcached defined.');
    $var = $this->memcached->get($key);

    if ($this->memcached->getResultCode() === Memcached::RES_NOTFOUND) {
      $var = null;
      $found = false;
    }
    else {
      $found = true;
    }

    return $var;
  }

  /**
   * @param string $key
   * @param mixed $var
   * @param int $expiration
   * @return bool True on success, false on failure
   */
  public function set($key, $var, $expiration = null) {
    if ( ! $this->memcached) throw new CacheException('No memcached defined.');
    return $this->memcached->set($key, $var, $expiration === null ? 0 : $expiration);
  }

  public function flush() {
    if ( ! $this->memcached) throw new CacheException('No memcached defined.');
    $this->memcached->flush();
  }

}