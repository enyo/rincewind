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
 * it should use the $addressDao, and get the address with $user->addressId.
 *
 * The DaoReference is setup in the dao, in the references array, where the index is
 * the access key (eg.: address). The Dao to be used to instantiate the referenced object,
 * and the local (eg.: address_id) and foreign key (eg.: id) are stored in the DaoReference.
 * 
 * The resulting code is then much easier.
 *
 * Before:
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
   * The dao used to get the referenced foreign DataObject.
   * @var Dao
   */
  protected $referenceDao;

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
   * @param Dao $referenceDao
   * @param string $localKey
   * @param string $foreignKey
   */
  public function __construct($referenceDao, $localKey, $foreignKey) {
    $this->referenceDao = $referenceDao;
    $this->localKey = $localKey;
    $this->foreignKey = $foreignKey;
  }


  public function getReferenceDao() {
    return $this->referenceDao;
  }

  public function getForeignKey() {
    return $this->foreignKey;
  }

  public function getLocalKey() {
    return $this->localKey;
  }

}


?>