<?php

/**
 * This file contains the DaoReference definition that's used to describe
 * references in a datasource.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/


/**
 * A DaoReference describes references between two resources.
 *
 * Lets say you have a user, that points to an address id.
 * You can setup the $userDao, so it understands, that when you access $user->address
 * it should use the $addressDao, and get the address with $user->addressId as id.
 *
 * The DaoReference is setup in the dao, in the $references array, where the index is
 * the access key (eg.: address). The Dao to be used to instantiate the referenced object,
 * the local key (eg.: address_id) and foreign key (eg.: id) are stored in the DaoReference.
 * 
 * Sometimes your data source returns the data hash of a reference directly to avoid
 * traffic overhead (this makes especially sense with FileSourceDaos like the JsonDao).
 * In that case you only need to specify the $daoClassName since the Dao does not have
 * to link / fetch the data hash itself, but only to instantiate a DataObject with the
 * given hash.
 * 
 * 
 *
 * Before setting up a DaoReference:
 * <code>
 * <?php
 *   $user = $userDao->getById(2);
 *   $address = $addressDao->getById($user->addressId);
 * ?>
 * </code>
 * 
 * After:
 * <code>
 * <?php
 *   $user = $userDao->getById(2);
 *   $address = $user->address;
 * ?>
 * </code>
 *
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/
class DaoReference {

  /**
   * The dao class name used to get the referenced foreign DataObject.
   * @var string
   */
  protected $daoClassName;

  /**
   * The local key. eg: address_id
   * @var string
   */
  protected $localKey;

  /**
   * The foreign key. eg: id
   * @var string
   */
  protected $foreignKey;


  /**
   * @param string|Dao $daoClassName
   * @param string $localKey
   * @param string $foreignKey
   */
  public function __construct($daoClassName, $localKey = null, $foreignKey = 'id') {
    $this->daoClassName = $daoClassName;
    $this->localKey = $localKey;
    $this->foreignKey = $foreignKey;
  }


  /**
   * @return string|Dao
   */
  public function getDaoClassName() {
    return $this->daoClassName;
  }

  /**
   * @return string
   */
  public function getForeignKey() {
    return $this->foreignKey;
  }

  /**
   * @return string
   */
  public function getLocalKey() {
    return $this->localKey;
  }

}


/**
 * Loading the DaoToManyReference
 */
include dirname(__FILE__) . '/DaoToManyReference.php';

?>