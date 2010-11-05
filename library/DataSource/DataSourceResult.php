<?php

/**
 * This file contains the DataSourceResult class definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package DataSource
 * @subpackage DataSourceResult
 */



/**
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package DataSource
 * @subpackage DataSourceResult
 */
class DataSourceResult {

  /**
   * @var array The actual data.
   */
  protected $data;


  /**
   * The current row number. The first row is 0.
   * @var int
   **/
  protected $currentRowNumber = 0;

  /**
   * @var int
   */
  protected $length;

  /**
   * @var int
   */
  protected $totalLength;

  /**
   * @param array $data Should be an array consisting of each record returned.
   * If it's only one record, it should also be in an array.
   */
  public function __construct ($data, $totalRowCount = null) {
    $this->data = $data;
    $this->length = count($data);
    $this->totalLength = (int) $totalRowCount;
  }


  /**
   * @return result
   */
  public function getData() {
    return $this->data;
  }

  public function count() {
    return $this->length;
  }

  public function countTotal() {
    return $this->totalLength ? $this->totalLength : $this->length;
  }


  /**
   * This is a shortcut for fetchArray or fetchResult.
   *
   * @param string $field If null, fetchArray is called, if passed fetchResult.
   * @return mixed
   */
  public function fetch($field = null) {
    return $field ? $this->fetchResult($field) : $this->fetchArray();
  }

  /**
   * @param string $field
   * @return array
   */
  public function fetchResult($field) {
    $array = $this->fetchArray();
    return $array[$field];
  }

  /**
   * @return array
   */
  public function fetchArray() {
    return $this->data[$this->currentRowNumber];
  }

  /*
   * @param int $rowNumber
   */
  public function seek($rowNumber) {
    $this->currentRowNumber = (int) $rowNumber;
  }

  /**
   * Sets the internal row pointer to row 0
   */
  public function reset() {
    $this->seek(0);
  }


}



