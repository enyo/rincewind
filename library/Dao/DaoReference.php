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
 **/
abstract class DaoReference {

  /**
   * The dao class name used to get the referenced foreign Record.
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
   * The Dao this reference is assigned to.
   * @var Dao
   */
  protected $sourceDao;


  /**
   * @param string|Dao $daoClassName
   * @param string $localKey
   * @param string $foreignKey
   * @param Dao $sourceDao The this reference is assigned to. If you don't set it in the
   *                       constructor, it has to be set with setSourceDao(). The addReference
   *                       function of the Dao does this for you.
   */
  public function __construct($daoClassName, $localKey = null, $foreignKey = 'id', $sourceDao = null) {
    $this->daoClassName = $daoClassName;
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

  /**
   * Creates a Dao. This calls createDao internally on the sourceDao.
   *
   * @param string $daoClassName
   * @return Dao
   */
  public function createDao($daoClassName) {
    return $this->sourceDao->createDao($daoClassName);
  }


  /**
   * When a record is accessed on a reference attribute, it calls this method to get the actual records or record.
   *
   * @param Record $record The record the reference is accessed at.
   * @param string $attribute The attribute it's accessed on.
   * @return Record|DaoResultIterator
   */
  abstract public function getData($record, $attribute);

}


/**
 * Loading the DaoToManyReference
 */
include dirname(__FILE__) . '/DaoToManyReference.php';

/**
 * Loading the DaoToOneReference
 */
include dirname(__FILE__) . '/DaoToOneReference.php';

