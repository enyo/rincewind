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
 * A DaoToOneReference describes a reference to another record in another resource.
 *
 * Lets say you have a user, that points to an address id.
 * You can setup the $userDao, so it understands, that when you access $user->address
 * it should use the $addressDao, and get the address with $user->addressId as id.
 *
 * The DaoReference is setup in the method Dao::addReference(). The method
 * Dao::setupReferences() is used to contain those calls.
 *
 * When you access a reference on a Record, internally the Dao will instantiate the
 * reference Dao and get the Record where `foreign_key` is the same as `local_key`.
 * (If you want to have control over how the Dao is instantiated, look at Dao->createDao()).
 *
 * Sometimes your data source returns the data hash of a reference directly to avoid
 * traffic overhead (this makes especially sense with FileSourceDaos like the JsonDao).
 * In that case you only need to specify the $daoName since the Dao does not have
 * to link / fetch the data hash itself, but only to instantiate a Record with the
 * given hash.
 *
 * If you are never only interested in the id itself, but always only in the object,
 * you can just setup a reference over the id itself. In this case, the attribute
 * should not have the name id of course. If you're free to change names then
 * just calling the `countryId` `country`, and setting up a reference to fetch
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
 * Once a DaoReference has been fetched by a dao, the data hash gets cached inside the Dao
 * so it doesn't get fetched more then once.
 *
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @see DaoReference
 * @see DaoToManyReference
 * @see Dao::setupReferences()
 * @see Dao::addReference()
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

