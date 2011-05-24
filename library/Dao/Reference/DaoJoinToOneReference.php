<?php

/**
 * This file contains the DaoJoinToOneReference definition that's used to describe
 * references to many other records in a datasource by joining.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 */
/**
 * Including BasicDaoReference
 */
if ( ! class_exists('BasicDaoReference', false)) include dirname(__FILE__) . '/BasicDaoReference.php';

/**
 * A DaoJoinToOneReference describes references to many resources by joining.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @see DaoReference
 * @see DaoToOneReference
 */
class DaoJoinToOneReference extends BasicDaoReference {

  /**
   * Be careful, the order for foreign and local key are inversed.
   *
   * DaoJoinManyReferences can't be exported
   *
   * @param string|Dao $foreignDaoName
   * @param string $foreignKey
   * @param string $localKey
   */
  public function __construct($foreignDaoName, $foreignKey, $localKey = 'id') {
    parent::__construct($foreignDaoName, $localKey, $foreignKey, false);
  }

  /**
   * Returns a DaoIterator for a specific reference.
   * A DataSource can directly return the DataHash, so it doesn't have to be fetched.
   *
   * @param Record $record
   * @param string $attribute The attribute it's being accessed on
   * @return DaoIterator
   */
  public function getReferenced($record, $attribute) {
    return $this->getForeignDao()->find(array($this->getForeignKey() => $record->get($this->getLocalKey())));
  }

  /**
   * @param mixed $value
   */
  public function exportValue($value) {
    throw new DaoReferenceException('JoinToOneReferences should never be exported.');
  }

  /**
   * @param mixed $value
   * @return mixed the coerced value.
   * @throws DaoCoerceException
   */
  public function coerce($value) {
    return $this->getForeignDao()->coerceId($value);
  }

}

