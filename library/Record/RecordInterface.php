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

