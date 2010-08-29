<?php

/**
 * This file contains the generic factory definition.
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Factory
 */

/**
 * The generic factory is used to provide common factory functions.
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Factory
 */
abstract class Factory {

  /**
   * @var array
   */
  protected $cache = array();

  /**
   * Either returns the cached object, or creates it with create(), caches it,
   * and returns it.
   *
   * @param string $resourceName
   * @return mixed
   * @see create()
   */
  public function get($resourceName) {
    if ($this->isCached($resourceName)) {
      $object = $this->getCached($resourceName);
    }
    else {
      $object = $this->create($resourceName);
      $this->cache($resourceName, $object);
    }
    return $object;
  }

  /**
   * Creates the object, and returns it.
   * Overwrite this in your implementations
   *
   * @param string $resourceName
   * @return mixed
   */
  abstract protected function create($resourceName);

  /**
   * @param string $resourceName
   * @return bool
   */
  protected function isCached($resourceName) {
    return isset($this->cache[$resourceName]);
  }

  /**
   * @param string $resourceName
   * @param mixed $object
   */
  protected function cache($resourceName, $object) {
    $this->cache[$resourceName] = $object;
  }

  /**
   * Returns a cached object. This method errors if you call it with a non
   * cached resource name!
   *
   * @param string $resourceName
   * @return mixed
   */
  protected function getCached($resourceName) {
    return $this->cache[$resourceName];
  }

}
