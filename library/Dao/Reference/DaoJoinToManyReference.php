<?php

/**
 * This file contains the DaoJoinManyReference definition that's used to describe
 * references to many other records in a datasource by joining.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 */
/**
 * Including DaoReference
 */
if ( ! class_exists('DaoReference', false)) include dirname(__FILE__) . '/DaoReference.php';

/**
 * A DaoToManyReference describes references to many resources by joining.
 *
 * Eg.: You have a table called "events", and a table called "attendees", where
 * the table "attendees" has a column "event_id".
 * In this case, you would add the reference attribute "attendees" to the table
 * events, and set it up as a DaoJoinManyReference.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @see DaoReference
 * @see DaoToOneReference
 */
class DaoJoinToManyReference extends DaoReference {

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
    $foreignDao = $this->getForeignDao();

    return $foreignDao->getIterator(array($this->getForeignKey()=>$record->get($this->getLocalKey())));
  }

  /**
   * @param mixed $value
   * @return mixed the coerced value.
   */
  public function coerce($value) {
    throw new DaoReferenceException('JoinManyReferences should never be exported.');
  }

}

