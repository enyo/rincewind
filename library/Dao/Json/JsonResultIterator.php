<?php


/**
 * This file contains the JsonResultIterator definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/

/**
 * Loading the DaoResultIterator
 */
include dirname(dirname(__FILE__)) . '/DaoResultIterator.php';

/**
 * This class implements the JsonResultIterator.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 */	
class JsonResultIterator extends DaoResultIterator {

	/**
	 * @var array
	 */
	private $data = false;


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
	 * @return JsonResultIterator Returns itself for chaining.
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
	 * @return JsonResultIterator Returns itself for chaining.
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