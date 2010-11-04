<?php

/**
 * This file contains the DaoToOneReference definition that's used to describe
 * references to one record in a datasource.
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
 * A DaoToOneReference describes a reference to another record in another resource.
 *
 * Lets say you have a user, that points to an address id.
 * You can setup the $userDao, so it understands, that when you access $user->address
 * it should use the $addressDao, and get the address with $user->addressId as id.
 *
 * See the DaoReference class for more information.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @see DaoReference
 * @see DaoToManyReference
 */
class DaoToOneReference extends DaoReference {

  /**
   * Returns a Record for a specific reference.
   * A DataSource can directly return the DataHash, so it doesn't have to be fetched.
   *
   * @param Record $record
   * @param string $attributeName The attribute it's being accessed on
   * @return Record
   */
  public function getReferenced($record, $attributeName) {

    if ($data = $record->getDirectly($attributeName)) {
      if (is_array($data)) {
        // If the data hash exists already, just return the Record with it.
        return $this->cacheAndReturn($record, $attributeName, $this->getForeignDao()->getRecordFromData($data));
      }
      elseif (is_int($data)) {
        // If data is an integer, it must be the id. So just get the record set with the data.
        return $this->cacheAndReturn($record, $attributeName, $this->getForeignDao()->getRecordFromData(array('id' => (int) $data), true, false));
      }
      elseif (is_a($data, 'Record')) {
        // The record is cached. Just return it.
        return $data;
      }
      else {
        Log::warning(sprintf('The data hash for `%s` was set but incorrect.', $attributeName));
        return null;
      }
    }
    else {
      // Otherwise: get the data hash and return the Record.
      $localKey = $this->getLocalKey();
      $foreignKey = $this->getForeignKey();

      if ($localKey && $foreignKey) {
        $localValue = $record->get($localKey);
        if ($localValue === null) return null;
        return $this->cacheAndReturn($record, $attributeName, $this->getForeignDao()->get(array($foreignKey => $localValue)));
      }
      else {
        return null;
      }
    }
  }

  /**
   * Stores the referenced record in the source record and returns the referenced.
   * 
   * @param Record $record
   * @param string $attributeName
   * @param Record $value
   * @return Record
   */
  protected function cacheAndReturn($record, $attributeName, $value) {
    $record->setDirectly($attributeName, $value);
    return $value;
  }

  /**
   * @param mixed $value
   * @return mixed the coerced value.
   */
  public function coerce($value) {
    if (is_object($value) && is_a($value, 'Record')) return $value->get('id');
    return ($value === null) ? null : (int) $value;
  }

}

