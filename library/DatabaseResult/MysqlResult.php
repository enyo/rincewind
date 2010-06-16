<?php

/**
 * This file contains the MysqlResult class definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Database
 * @subpackage DatabaseResult
 **/


/**
 * Loading the interface
 */
include dirname(__FILE__) . '/DatabaseResult.php';

/**
 * The Mysql DatabaseResult implementation.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Database
 * @subpackage DatabaseResult
 **/
class MysqlResult extends DatabaseResult {

  /**
   * @var int
   **/
  private $currentRowNumber = 0;


  /**
   * If you pass a row number, seek() is called.
   * @param int $rowNumber
   * @return array
   */
  public function fetchArray($rowNumber = null) {
    if ($rowNumber !== null) { $this->seek($rowNumber); }
    else { $this->currentRowNumber ++; }
    return ($this->result->fetch_assoc());
  }

  /**
   * Returns a specific field of the result set.
   * @param string $field
   * @return mixed
   */
  public function fetchResult($field) {
    $return = $this->fetchArray();
    $this->seek($this->currentRowNumber - 1);
    return ($return[$field]);
  }

  /**
   * @return int
   */
  public function numRows() {
    return ($this->result->num_rows);
  }

  /**
   * @param int $rowNumber
   */
  public function seek($rowNumber) {
    $rowNumber = (int) $rowNumber;
    $this->currentRowNumber = $rowNumber;
    $this->result->data_seek($rowNumber); 
  }

  public function free() {
    $this->result->free();
  }

}

