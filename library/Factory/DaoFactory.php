<?php

/**
 * This file contains the DaoFactory definition.
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @subpackage DaoFactory
 */
/**
 * Including the Factory base class.
 */
if ( ! class_exists('Factory', false)) include(dirname(__FILE__) . '/Factory.php');

/**
 * The Dao Factory returns and caches a specific Dao
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @subpackage DaoFactory
 */
class DaoFactory extends Factory {

  /**
   * @var Database
   */
  protected $dataSource;

  /**
   * @var string
   */
  protected $daosPath;

  /**
   * If set, it gets submitted to daos when instantiating.
   * @var Cache
   */
  protected $daoCache;

  /**
   *
   * @param Database $dataSource
   */
  public function __construct($dataSource, $daosPath, $daoCache = null) {
    $this->dataSource = $dataSource;
    $this->daosPath = $daosPath;
    $this->daoCache = $daoCache;
  }

  /**
   * Uses the stored database, and creates a new ShopDao, using the autoload
   * function to include the Dao itself.
   *
   * @param string $resourceName
   * @return Dao
   */
  protected function create($resourceName) {
    $className = $resourceName . 'Dao';

    require_class($className, $this->daosPath . $className . '.php');
    $dao = new $className($this->dataSource, $this->container->shopDaoFactory);
    if ($this->daoCache) {
      $dao->setCache($this->daoCache);
    }
    return $dao;
  }

  /**
   * @param string $daoName
   * @return Dao
   */
  public function get($daoName) {
    return parent::get($daoName);
  }
}
