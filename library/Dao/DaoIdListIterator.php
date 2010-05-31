<?php

/**
 * This file contains the Id list iterator definition.
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
 * The IdListIterator takes an array of ids, and lets you iterate over it, returning the corresponding
 * DataObjects by calling the dao, and getting it.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @todo The Iterator should not fetch the objects in advance, but rather on demand.
 **/
class DaoIdListIterator extends DaoResultIterator {

  /**
   * @var result
   */
  private $idList = false;

  /**
   * @param result $result
   * @param Dao $dao
   */
  public function __construct($idList, $dao) {
    $this->idList = $idList;
    $this->length = count($idList);
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
    $this->currentData = $this->currentKey > $this->length ? null : $this->dao->getData(array('id'=>$this->idList[$this->currentKey - 1]));
    return $this;
  }

}


?>
