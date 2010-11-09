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
 * Including BasicDaoReference
 */
if ( ! class_exists('BasicDaoReference', false)) include dirname(__FILE__) . '/BasicDaoReference.php';

/**
 * A DaoJoinTableToManyReference describes references to many resources joined by
 * a join table.
 *
 * Eg.: You have two tables, one called "events" and one called "users".
 * You then have a join table called "user_to_event_assignments", with two attributes:
 * "event_id" and "user_id".
 *
 * You can now setup a reference in the EventDao, so that you can simply access
 * all users assigned to an event like this:
 *
 * <code>
 *   foreach ($event->users as $user) {
 *     // do stuff
 *   }
 * </code>
 *
 * by creating the reference:
 *
 * <code>
 *   protected function getUserReference() {
 *     return new DaoJoinTableToManyReference('User', 'UserToEventAssignment', 'eventId', 'userId');
 *   }
 * </code>
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @see DaoReference
 * @see DaoToOneReference
 */
class DaoJoinTableToManyReference extends BasicDaoReference {

  /**
   * The dao used to join.
   * @var Dao
   */
  protected $joinDao;

  /**
   * The key in the join table, that points to the local key.
   * @var string
   */
  protected $joinToLocalKey;

  /**
   * The key in the join table, that points to the foreign key.
   * @var string
   */
  protected $joinToForeignKey;


  /**
   * Be careful about the order of the daos.
   *
   * DaoJoinTableToManyReference can't be exported
   *
   * @param string|Dao $foreignDaoName
   * @param string|Dao $joinDaoName
   * @param string $joinToLocalKey
   * @param string $joinToForeignKey
   * @param string $localKey
   * @param string $foreignKey
   */
  public function __construct($foreignDaoName, $joinDaoName, $joinToLocalKey, $joinToForeignKey, $localKey = 'id', $foreignKey = 'id') {
    parent::__construct($foreignDaoName, $localKey, $foreignKey, false);
    $this->joinDao = $joinDaoName;
    $this->joinToLocalKey = $joinToLocalKey;
    $this->joinToForeignKey = $joinToForeignKey;
  }

  /**
   * @return Dao
   */
  protected function getJoinDao() {
    return $this->joinDao = $this->createDao($this->joinDao);
  }

  /**
   * @return string
   */
  protected function getJoinToLocalKey() {
    return $this->joinToLocalKey;
  }

  /**
   * @return string
   */
  protected function getJoinToForeignKey() {
    return $this->joinToForeignKey;
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

    $joinDao = $this->getJoinDao();

    // Get all joins, that point to the local key.
    $joins = $joinDao->getIterator(array($this->getJoinToLocalKey() => $record->get($this->getLocalKey())));

    // Extract an array of foreign keys.
    $foreignKeys = array();
    foreach ($joins as $join) {
      $foreignKeys[] = $join->get($this->getJoinToForeignKey());
    }

    // And return the key list iterator.
    return new DaoKeyListIterator($foreignKeys, $this->getForeignDao(), $this->getForeignKey());
  }

  /**
   * @param mixed $value
   */
  public function  exportValue($value) {
    throw new DaoReferenceException('JoinManyReferences should never be exported.');
  }

  /**
   * @param mixed $value
   * @return mixed the coerced value.
   */
  public function coerce($value) {
    if (is_array($value)) {
      $newValue = array();
      foreach ($value as $id) {
        $newValue = (int) $id;
      }
    }
    else return array();
  }

}

