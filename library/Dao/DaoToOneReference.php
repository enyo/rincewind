<?php

/**
 * This file contains the DaoToOneReference definition that's used to describe
 * references to one record in a datasource.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/



/**
 * A DaoToOneReference describes a reference to another record in another resource.
 *
 * Lets say you have a user, that points to an address id.
 * You can setup the $userDao, so it understands, that when you access $user->address
 * it should use the $addressDao, and get the address with $user->addressId as id.
 *
 * The DaoReference is setup in the Dao with the method addReference(). The method
 * setupReferences() is used to contain those calls.
 *
 * When you access a reference on a Record, internally the Dao will instantiate the
 * reference Dao and get the Record where `foreign_key` is the same as `local_key`.
 * (If you want to have control over how the Dao is instantiated, look at Dao->createDao()).
 *
 * Sometimes your data source returns the data hash of a reference directly to avoid
 * traffic overhead (this makes especially sense with FileSourceDaos like the JsonDao).
 * In that case you only need to specify the $daoClassName since the Dao does not have
 * to link / fetch the data hash itself, but only to instantiate a Record with the
 * given hash.
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
 **/
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
  public function getData($record, $attribute) {

    $dao = $this->getDaoClassName();
    if (is_string($dao)) $dao = $this->createDao($dao);

    if ($data = $record->getDirectly($attribute)) {
      if (is_array($data)) {
        // If the data hash exists already, just return the Record with it.
        return $dao->getRecordFromData($data);
      }
      else {
        trigger_error(sprintf('The data hash for `%s` was set but incorrect.', $attribute), E_USER_WARNING);
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
        $return = $dao->get(array($foreignKey=>$localValue));
        $record->setDirectly($attribute, $return->getArray());
        return $return;
      }
      else {
        return null;
      }
    }

  }


}


