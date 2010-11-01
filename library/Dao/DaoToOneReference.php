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
   * It then sets the hash in the Record so it can be retrieved next time.
   * If it is accessed after that, the already fetched DataHash is used.
   * A DataSource can directly return the DataHash, so it doesn't have to be fetched.
   *
   * @param Record $record
   * @param string $attribute The attribute it's being accessed on
   * @return Record
   */
  public function getReferenced($record, $attribute) {

    $foreignDao = $this->getForeignDao();

    if ($data = $record->getDirectly($attribute)) {
      if (is_array($data)) {
        // If the data hash exists already, just return the Record with it.
        return $foreignDao->getRecordFromData($data);
      }
      elseif (is_int($data)) {
        // If data is an integer, it must be the id.
        return $foreignDao->getById($data);
      }
      else {
        Log::warning(sprintf('The data hash for `%s` was set but incorrect.', $attribute));
        return null;
      }
    }
    else {
      // Otherwise: get the data hash, store it in the Record that's referencing it, and
      // return the Record.
      $localKey = $this->getLocalKey();
      $foreignKey = $this->getForeignKey();

      if ($localKey && $foreignKey) {
        $localValue = $record->get($localKey);
        if ($localValue === null) return null;
        $return = $foreignDao->get(array($foreignKey => $localValue));
        $record->setDirectly($attribute, $return->getArray());
        return $return;
      }
      else {
        return null;
      }
    }
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

