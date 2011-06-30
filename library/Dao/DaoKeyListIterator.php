<?php

/**
 * This file contains the Key list iterator definition.
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
 * The DaoKeyListIterator takes an array of keys, and lets you iterate over it, returning the corresponding
 * Records by calling the dao, and getting it.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 */
class DaoKeyListIterator extends DaoResultIterator {

  /**
   * @var array
   */
  private $keyList;
  /**
   * @var string
   */
  private $keyName;
  /**
   * @var array
   */
  private $cachedValues = array();

  /**
   * @param array $keyList
   * @param Dao $dao
   * @param string $key
   */
  public function __construct($keyList, $dao, $keyName = 'id') {
    $this->keyList = $keyList;
    $this->keyName = $keyName;
    $this->length = count($keyList);
    $this->dao = $dao;
    $this->next();
  }

  /**
   * Mainly used for testing purposes.
   * 
   * @return array
   */
  public function getKeyList() {
    return $this->keyList;
  }

  /**
   * Mainly used for testing purposes.
   *
   * @return string
   */
  public function getKeyName() {
    return $this->keyName;
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
   * @todo there should be exporting of the key... don't know if here!
   */
  protected function getCurrentData() {
    return $this->key() > $this->count() ? null : $this->dao->getData(array($this->keyName => $this->keyList[$this->key() - 1]));
  }

}

