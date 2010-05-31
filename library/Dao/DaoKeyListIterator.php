<?php

/**
 * This file contains the Key list iterator definition.
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
 * The DaoKeyListIterator takes an array of keys, and lets you iterate over it, returning the corresponding
 * DataObjects by calling the dao, and getting it.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @todo The Iterator should not fetch the objects in advance, but rather on demand.
 **/
class DaoKeyListIterator extends DaoResultIterator {

  /**
   * @var array
   */
  private $keyList = false;

  /**
   * @var string
   */
  private $key;

  /**
   * @param array $keyList
   * @param Dao $dao
   * @param string $key
   */
  public function __construct($keyList, $dao, $key = 'id') {
    $this->keyList = $keyList;
    $this->key = $key;
    $this->length = count($keyList);
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
   * Set the pointer to the next row, and fetches the data to return in current.
   * @return SqlResultIterator Returns itself for chaining.
   */
  public function next() {
    $this->currentKey ++;
    $this->currentData = $this->currentKey > $this->length ? null : $this->dao->getData(array($this->key=>$this->keyList[$this->currentKey - 1]));
    return $this;
  }

}


?>
