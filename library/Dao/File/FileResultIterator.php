<?php

/**
 * This file contains the FileResultIterator definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 */
/**
 * Loading the DaoResultIterator
 */
if ( ! class_exists('DaoResultIterator', false)) include dirname(__FILE__) . '/DaoResultIterator.php';

/**
 * This class implements the FileResultIterator.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 */
class FileResultIterator extends DaoResultIterator {

  /**
   * @var array
   */
  protected $result = false;

  /**
   * @param DataSourceResult $result
   * @param Dao $dao
   */
  public function __construct($result, $dao) {
    parent::__construct($dao);
    $this->result = $result;
    $this->next();
  }

  /**
   * Wrapper for $result
   * @return int
   */
  public function countTotal() {
    return $this->result->countTotal();
  }

  /**
   * Wrapper for $result
   * @return int
   */
  public function count() {
    return $this->result->count();
  }

  /**
   * @return array
   * @uses $data
   */
  public function getData() {
    return $this->result->getData();
  }

  /**
   * Sets the pointer to entry 1.
   * @return FileResultIterator Returns itself for chaining.
   */
  public function rewind() {
    if ($this->count() > 0) {
      $this->currentKey = 0;
      $this->next();
    }
    return $this;
  }

  /**
   * Set the pointer to the next row.
   * @return FileResultIterator Returns itself for chaining.
   */
  public function next() {
    $this->currentKey ++;
    return $this;
  }

  /**
   * Fetches the current data.
   * @return array
   */
  protected function getCurrentData() {
    if ($this->key() > $this->count()) return null;
    $this->result->seek($this->key() - 1);
    return $this->result->fetch();
  }

}

