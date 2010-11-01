<?php

/**
 * This file contains the DaoToManyReference definition that's used to describe
 * references to many other records in a datasource.
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
 * A DaoToManyReference describes references to many resources.
 *
 * The DaoToManyReference works exactly as the DaoReference, but works with
 * arrays that either contain ids, or data hashes.
 *
 * In a DaoToManyReference the localKey, if specified, has to be of type Dao::SEQUENCE.
 *
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @see DaoReference
 * @see DaoToOneReference
 */
class DaoToManyReference extends DaoReference {

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

    if ($data = $record->getDirectly($attribute)) {
      if (is_array($data)) {
        if (count($data) === 0 || is_array(reset($data))) {
          // The data hash is an array, either empty, or containing the hashes.
          return new DaoHashListIterator($data, $foreignDao);
        }
        elseif (is_int(reset($data)) && ($foreignKey = $this->getForeignKey())) {
          // The data hash is an array containing the ids, and there is a
          // foreign key to link them to.
          return new DaoKeyListIterator($data, $foreignDao, $foreignKey);
        }
      }
      Log::warning(sprintf('The data hash for `%s` was set but incorrect.', $attribute));
      return new DaoHashListIterator(array(), $foreignDao);
    }
    else {
      // Get the list of ids
      $localKey = $this->getLocalKey();
      $foreignKey = $this->getForeignKey();

      if ($localKey && $foreignKey) {
        $localValue = $record->get($localKey);

        return new DaoKeyListIterator($localValue ? $localValue : array(), $foreignDao, $foreignKey);
      }
      return new DaoKeyListIterator(array(), $foreignDao, $foreignKey);
    }
  }

  /**
   * @param mixed $value
   * @return mixed the coerced value.
   */
  public function coerce($value) {
    return ($value === null) ? null : (array) $value;
  }

}

