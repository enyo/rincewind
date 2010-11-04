<?php

/**
 * This file contains the Record interface definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @subpackage Record
 **/


/**
 * The RecordInterface
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @subpackage Record
 **/
interface RecordInterface {

  /**
   * Calls delete($this) on the dao.
   * Throws an exception if no id is set.
   *
   * @return void
   */
  public function delete();

  /**
   * Depending on the state of the Record does dao->update($this) or dao->insert($this)
   *
   * @return Record Itself for chaining
   */
  public function save();

  /**
   * Loads the record from the datasource.
   *
   * This can have to effects:
   *
   * 1. If the $existsInDatabase value is set to false, it uses the changed
   * values (the values that have explicitly been set with set()) to load the
   * complete data hash.
   * In this case the method actually calls the dao->getData() function, and
   * passes its current hash. It then updates its own hash with the one returned.
   *
   * 2) If the $existsInDatabase value is set to true, it just calls dao->getData()
   * with it's id, and updates its hash.
   *
   * @return Record Itself for chaining
   * @uses $existsInDatabase
   * @uses $dao
   */
  public function load();

  /**
   * Returns the value of a column
   * @param string $column
   * @return mixed
   */
  public function get($column);

  /**
   * Sets the value of a column
   * @param string $column
   * @param mixed $value
   * @return Record Itself for chaining
   */
  public function set($column, $value);



}

