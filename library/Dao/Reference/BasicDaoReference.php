<?php

/**
 * This file contains the AbstractDaoReference which is the base of most
 * references.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 */
/**
 * Including the interface.
 */
if ( ! interface_exists('DaoReference', false)) include dirname(__FILE__) . '/DaoReference.php';

/**
 *
 * This reference object instantatiates the reference Dao
 * if you passed a string as dao (you should have a look at Dao->createDao() to
 * implement your method of Dao instantation... you'll probably want to use a
 * factory there), or uses the Dao you provided to get the referenced Record(s).
 *
 * It is not necessary to provide the local key in 2 cases:
 *
 * 1) You know the data source always returns the data hash(s) of a reference
 * directly to avoid traffic overhead (this makes especially sense with
 * FileSourceDaos like the JsonDao).
 * In this case you only need to specify the Dao since the Dao does not have to
 * link / fetch the data hash itself, but only to instantiate the Record(s) with
 * the given hash.
 *
 * 2) The reference you are defining is also the id it's referencing.
 * In this case you don't need to specify the local key, since the local key is
 * the reference attribute itself.
 *
 * There is no problem whatsoever in combining those 2.
 *
 *
 * If you are never only interested in the id itself, but always only in the object,
 * you can just setup a reference over the id itself. In this case, the attribute
 * should not have the name id of course. If you're free to change names then
 * just changing `countryId` to `country`, and setting up a reference to fetch
 * it automatically is the sweetest.
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
 * @see DaoToOneReference
 * @see DaoToManyReference
 * @see Dao::setupReferences()
 * @see Dao::addReference()
 */
abstract class BasicDaoReference implements DaoReference {

  /**
   * The foreign dao.
   * Never access this property directly.
   * Use the getter for it, because this might be a string.
   * 
   * @var string
   */
  private $foreignDao;
  /**
   * The local key. eg: address_id
   * @var string
   */
  protected $localKey;
  /**
   * The foreign key. eg: id
   * @var string
   */
  protected $foreignKey = 'id';
  /**
   * The Dao this reference is assigned to.
   * @var Dao
   */
  protected $sourceDao;
  /**
   * @var bool
   */
  protected $export;

  /**
   * @param string|Dao $foreignDaoName
   * @param string $localKey
   * @param string $foreignKey If null, the default 'id' is used.
   * @param bool $exportReference specifies if this reference should be sent to the
   *                              datasource when saving.
   */
  public function __construct($foreignDaoName, $localKey = null, $foreignKey = null, $export = false) {
    $this->foreignDao = $foreignDaoName;
    $this->localKey = $localKey;
    if ($foreignKey !== null) $this->foreignKey = $foreignKey;
    $this->export = $export;
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
   * @return bool
   */
  public function export() {
    return $this->export;
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
   * Returns the foreign dao.
   *
   * @return Dao
   * @uses $foreignDao
   * @uses createDao()
   */
  public function getForeignDao() {
    return $this->foreignDao = $this->createDao($this->foreignDao);
  }

  /**
   * Creates a Dao. This calls createDao internally on the sourceDao.
   * If it's already a dao, it's just returned.
   *
   * @param string|Dao $daoName
   * @return Dao
   */
  public function createDao($daoName) {
    if ($daoName instanceof Dao) return $daoName;
    else return $this->getSourceDao()->createDao($daoName);
  }

  /**
   * If it's a record, the id of the record is taken, and exportId() is called on
   * it from the records Dao.
   *
   * Otherwise, the $value is jsut exported with exportId() on the foreign Dao.
   *
   * @param mixed $value
   * @return mixed
   */
  public function exportValue($value) {
    if ($value instanceof Record) return $value->getDao()->exportId($value->get('id'));
    return $this->getForeignDao()->exportId($value);
  }

  /**
   * Just returns the value.
   * @param mixed $value
   * @return mixed
   */
  public function importValue($value) {
    return $value;
  }

}
