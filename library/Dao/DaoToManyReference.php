<?php

/**
 * This file contains the DaoToManyReference definition that's used to describe
 * references to many other records in a datasource.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/



/**
 * A DaoToManyReference describes references to many resources.
 *
 * The DaoToManyReference works exactly as the DaoReference, but works with
 * arrays that either contain ids, or data hashes.
 *
 * In a DaoToManyReference the localKey has to be of type Dao::SEQUENCE.
 *
 * The DaoToManyReference is setup in the Dao with the method addToManyReference(). The
 * method setupReferences() is used to contain those calls.
 *
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @see DaoReference
 * @see DaoToOneReference
 * @see Dao::setupReferences()
 * @see Dao::addReference()
 **/
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

    $dao = $this->getDaoClassName();
    if (is_string($dao)) $dao = $this->createDao($dao);

    if ($data = $record->getDirectly($attribute)) {
      if (is_array($data)) {
        // The sequence of data hashes has been set already
        return new DaoHashListIterator($record->getDirectly($attribute), $dao);
      }
      trigger_error(sprintf('The data hash for `%s` was set but incorrect.', $attribute), E_USER_WARNING);
      return new DaoHashListIterator(array(), $dao);
    }
    else {
      // Get the list of ids
      $localKey = $this->getLocalKey();
      $foreignKey = $this->getForeignKey();

      if ($localKey && $foreignKey) {
        $localValue = $record->get($localKey);

        return new DaoKeyListIterator($localValue, $dao, $foreignKey);
      }
      return new DaoKeyListIterator(array(), $dao, $foreignKey);
    }

  }

}


