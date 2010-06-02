<?php

/**
 * This file contains the Hash list iterator definition.
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
 * The DaoHashListIterator takes an array of data hashes, and lets you iterate over it, returning the corresponding
 * DataObjects by instantiating them with the dao and the given data hash.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @todo The Iterator should not fetch the objects in advance, but rather on demand.
 **/
class DaoHashListIterator extends DaoResultIterator {

  /**
   * @var result
   */
  private $hashList = false;

  /**
   * @param result $result
   * @param Dao $dao
   */
  public function __construct($hashList, $dao) {
    $this->hashList = $hashList;
    $this->length = count($hashList);
    $this->dao = $dao;
    $this->next();
  }

  /**
   * Sets the pointer to row 1.
   * @return SqlResultIterator Returns itself for chaining.
   */
  public function rewind() {
    if ($this->length > 0) {
      $this->currentKey = 0;
      $this->next();
    }
    return $this;
  }


  /**
   * Set the pointer to the next row.
   * @return SqlResultIterator Returns itself for chaining.
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
    return $this->key() > $this->count() ? null : $this->hashList[$this->key() - 1];
  }

}


?>
