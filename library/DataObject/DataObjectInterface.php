<?php

/**
 * This file contains the DataObject interface definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @subpackage DataObject
 **/


/**
 * The DataObjectInterface
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @subpackage DataObject
 **/
interface DataObjectInterface {

  /**
   * Calls delete($this) on the dao.
   * Throws an exception if no id is set.
   *
   * @return void
   */
  public function delete();

  /**
   * Depending on the state of the DataObject does dao->update($this) or dao->insert($this)
   *
   * @return DataObject Itself for chaining
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
   * @return DataObject Itself for chaining
   */
  public function set($column, $value);



}

?>
