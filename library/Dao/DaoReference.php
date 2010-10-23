<?php

/**
 * This file contains the DaoReference definition that's used to describe
 * references in a datasource.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 */

/**
 * A DaoReference describes references between two resources.
 *
 * See DaoToOneReference or DaoToManyReference for more info.
 *
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @see DaoToOneReference
 * @see DaoToManyReference
 * @see Dao::setupReferences()
 * @see Dao::addReference()
 */
abstract class DaoReference {

  /**
   * The dao class name used to get the referenced foreign Record.
   * @var string
   */
  protected $daoName;
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
   * The Dao this reference is assigned to.
   * @var Dao
   */
  protected $sourceDao;

  /**
   * @param string|Dao $daoName
   * @param string $localKey
   * @param string $foreignKey
   * @param Dao $sourceDao The this reference is assigned to. If you don't set it in the
   *                       constructor, it has to be set with setSourceDao(). The addReference
   *                       function of the Dao does this for you.
   */
  public function __construct($daoName, $localKey = null, $foreignKey = 'id', $sourceDao = null) {
    $this->daoName = $daoName;
    $this->localKey = $localKey;
    $this->foreignKey = $foreignKey;
    $this->sourceDao = $sourceDao;
  }

  /**
   * Sets the source dao.
   * @param Dao $dao
   */
  public function setSourceDao($dao) {
    $this->sourceDao = $dao;
  }

  /**
   * @return Dao
   */
  public function getSourceDao() {
    return $this->sourceDao;
  }

  /**
   * @return string|Dao
   */
  public function getDaoName() {
    return $this->daoName;
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

  /**
   * Returns the foreign dao. If $daoName is already a dao, it is only
   * returned. If it's a string, createDao() is called.
   * @return Dao
   * @uses createDao()
   */
  public function getForeignDao() {
    $dao = $this->getDaoName();
    if (is_string($dao)) $dao = $this->createDao($dao);
    return $dao;
  }

  /**
   * Creates a Dao. This calls createDao internally on the sourceDao.
   *
   * @param string $daoName
   * @return Dao
   */
  public function createDao($daoName) {
    return $this->sourceDao->createDao($daoName);
  }

  /**
   * When a record is accessed on a reference attribute, it calls this method to get the actual records or record.
   *
   * @param Record $record The record the reference is accessed at.
   * @param string $attribute The attribute it's accessed on.
   * @return Record|DaoResultIterator
   */
  abstract public function getReferenced($record, $attribute);

  /**
   * Forces a value in a correct representation of the reference.
   *
   * This can be either an integer as id, or an array of integers, or the data
   * itself, etc...
   *
   * @param mixed $value
   * @return mixed the coerced value.
   */
  abstract public function coerce($value);
}

/**
 * Loading the DaoToManyReference
 */
include dirname(__FILE__) . '/DaoToManyReference.php';

/**
 * Loading the DaoToOneReference
 */
include dirname(__FILE__) . '/DaoToOneReference.php';

