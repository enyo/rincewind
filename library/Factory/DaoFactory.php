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
   *
   * @param Database $dataSource
   */
  public function __construct($dataSource) {
    $this->dataSource = $dataSource;
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
    // The Dao itself gets included by the autoload function.
    return new $className($this->dataSource);
  }

  /**
   * @param string $daoName
   * @return Dao
   */
  public function get($daoName) {
    return parent::get($daoName);
  }
}
