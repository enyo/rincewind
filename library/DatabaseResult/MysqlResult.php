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


  public function fetchArray() {
    $this->currentRowNumber ++;
    return ($this->result->fetch_assoc());
  }

  public function fetchResult($field) {
    $return = $this->fetchArray();
    $this->seek($this->currentRowNumber - 1);
    return ($return[$field]);
  }

  public function numRows() {
    return ($this->result->num_rows);
  }

  public function seek($rowNumber) {
    $this->currentRowNumber = $rowNumber;
    $this->result->data_seek($rowNumber); 
  }

  public function free() {
    $this->result->free();
  }

}

?>
