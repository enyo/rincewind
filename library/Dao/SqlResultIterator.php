<?php

/**
 * This file contains the SQL result iterator definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/

/**
 * Loading the DaoResultIterator
 */
if (!class_exists('DaoResultIterator', false)) include dirname(__FILE__) . '/DaoResultIterator.php';

/**
 * The SQL result iterator is the implementation of the DaoResultIterator.
 * It allows managing the result of a sql query.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @todo The Iterator should not fetch the rows in advance, but rather on demand.
 **/
class SqlResultIterator extends DaoResultIterator {

  /**
   * @var result
   */
  private $result = false;

  /**
   * @param result $result
   * @param Dao $dao
   */
  public function __construct($result, $dao) {
    $this->result = $result;
    $this->length = $result->numRows();
    $this->dao = $dao;
    $this->next();
  }

  /**
   * Sets the pointer to row 1.
   * @return SqlResultIterator Returns itself for chaining.
   */
  public function rewind() {
    if ($this->length > 0) {
      $this->result->reset();
      $this->currentKey = 0;
      $this->next();
    }
    return $this;
  }


  /**
   * Set the pointer to the next row, and fetches the data to return in current.
   * @return SqlResultIterator Returns itself for chaining.
   */
  public function next() {
    $this->currentKey ++;
    $this->currentData = $this->result->fetchArray();
    return $this;
  }

}


?>
