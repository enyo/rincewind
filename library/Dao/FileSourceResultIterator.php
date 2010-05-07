<?php


/**
 * This file contains the FileResultIterator definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/

/**
 * Loading the DaoResultIterator
 */
if (!class_exists('DaoResultIterator')) include dirname(__FILE__) . '/DaoResultIterator.php';

/**
 * This class implements the FileSourceResultIterator.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 */ 
abstract class FileSourceResultIterator extends DaoResultIterator {

  /**
   * @var array
   */
  protected $data = false;


  /**
   * @param array $data
   * @param Dao $dao
   */
  public function __construct($data, $dao) {
    $this->data = $data;
    $this->length = count($data);
    $this->dao = $dao;
    $this->next();
  }

  
  
  /**
   * Sets the pointer to entry 1.
   * @return FileResultIterator Returns itself for chaining.
   */
  public function rewind() {
    if ($this->length > 0) {
      $this->currentKey = 0;
      $this->next();
    }
    return $this;
  }


  /**
   * Set the pointer to the next row, and fetches the data to return in current.
   * @return FileResultIterator Returns itself for chaining.
   */
  public function next() {
    $this->currentKey ++;
    $idx = $this->currentKey - 1;
    if (!isset($this->data[$idx])) $this->currentData = null;
    else $this->currentData = $this->data[$idx];
    return $this;
  }


}


?>
