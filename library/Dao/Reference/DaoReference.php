<?php

/**
 * This file contains the DaoReference interface that's used to describe
 * references in a datasource.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 */

/**
 * The DaoReferenceException
 */
class DaoReferenceException extends Exception {

}

/**
 * A DaoReference describes references between two resources.
 *
 * To setup a reference, you have to define the attribute that should act as reference
 * as Dao::REFERENCE, and then create a method that's called getATTRIBUTEReference().
 * (eg.: getAddressReference()).
 * This method has to return a reference implementing the DaoReference interface.
 *
 * When you then access the reference (eg.: $user->address) for the first time,
 * internally the Record will call getReference('address') on its Dao (which
 * calls getAddressReference(), and caches the reference so the method is only
 * called once in the lifetime of a Dao) and then call getReferenced() on the
 * reference, passing itself (the record) and the attribute name of the reference.
 * The method getReferenced then returns a Record or a DaoResultIterator.
 *
 *
 * When you set an attribute that is a reference on a record, internally the
 * reference will be fetched, and coerce() is called on the reference, to make
 * sure it is in a valid format.
 *
 * When data is imported, the importValue() on the reference is called, to make
 * sure the imported value is in a correct format. (This makes sense if your
 * reference can handle IDs, or a complete datahash)
 *
 * The same goes for exporting: if a record is saved, the reference is asked
 * to be exported (and exportValue() is called), *if* the reference method
 * export() returns true. If not, the reference will never be saved to the
 * datasource.
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
interface DaoReference {

  /**
   * Sets the source dao.
   * @param Dao $dao
   */
  public function setSourceDao($dao);

  /**
   * @return bool
   */
  public function export();

  /**
   * Called by the Dao when exporting values, and export() returns true.
   * 
   * @param mixed $value
   */
  public function exportValue($value);

  /**
   * Called by the Dao when importing values.
   *
   * @param mixed $value
   */
  public function importValue($value);

  /**
   * When a record is accessed on a reference attribute, it calls this method to get the actual records or record.
   *
   * @param Record $record The record the reference is accessed at.
   * @param string $attribute The attribute it's accessed on.
   * @return Record|DaoResultIterator
   */
  public function getReferenced($record, $attribute);

  /**
   * Forces a value in a correct representation of the reference. When you call
   * set() on a Record, the record calls coerce() internally, and forwards it
   * to the Reference coerce() method if the attribute is of type Dao::REFERENCE.
   *
   * This can be either an integer as id, or an array of integers, or the data
   * itself, etc...
   *
   * @param mixed $value
   * @return mixed the coerced value.
   */
  public function coerce($value);

}
