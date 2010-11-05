<?php

/**
 * This file contains the PostgresqlResult class definition.
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Database
 * @subpackage DatabaseResult
 * */
/**
 * Loading the interface
 */
include dirname(__FILE__) . '/DatabaseResult.php';

/**
 * The Postgresql DatabaseResult implementation.
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Database
 * @subpackage DatabaseResult
 * */
class PostgresqlResult extends DatabaseResult {

  /**
   * Returns the array of the current row (or the specified row) and seeks to the
   * next row.
   *
   * @param int $rowNumber
   * @return array
   */
  public function fetchArray($rowNumber = null) {
    $rowNumber = $rowNumber ? (int) $rowNumber : $this->currentRowNumber;
    if ($rowNumber >= $this->numRows()) return false;
    $this->seek($rowNumber + 1);
    return pg_fetch_assoc($this->result, $rowNumber);
  }

  /**
   * Returns a specific field of the current row.
   *
   * @param string $field
   * @return mixed
   */
  public function fetchResult($field) {
    if ($this->currentRowNumber >= $this->numRows()) return false;
    return pg_fetch_result($this->result, $this->currentRowNumber, $field);
  }

  /**
   * @return int
   */
  public function numRows() {
    return pg_num_rows($this->result);
  }

  /**
   * Doesn't call pg_result_seek() but simply sets the currentRowNumber to the
   * $rowNumber, because all functions explicitely specify which row to use.
   * 
   * @param int $rowNumber
   */
  public function seek($rowNumber) {
    $this->currentRowNumber = (int) $rowNumber;
  }

  public function free() {
    pg_free_result($this->result);
  }

}
