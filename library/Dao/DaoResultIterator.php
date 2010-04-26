<?php

/**
 * This file contains the abstract DaoResultIterator definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/

/**
 * The Dao Result iterator is returned whenever a query returns more than one row.
 * It implements the default php Iterator Interface, so foreach() and stuff works on it.
 *
 * Typical usage:
 * <code>
 * <?php
 * $users = $userDao->getAll(); // Returns the iterator
 * echo $users->count() . ' users returned.';
 * foreach ($users as $user) {
 *     // ...do stuff...
 * }
 * ?>
 * </code>
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/
abstract class DaoResultIterator implements Iterator {


	/**
	 * @var Dao
	 */
	protected $dao = false;


	/**
	 * Contains the data of the current set.
	 *
	 * @var array
	 */
	protected $currentData;


	/**
	 * The number of rows
	 *
	 * @var int
	 */
	protected $length = 0;

	/**
	 * Stores the current key (in this case: row number) of the iterator.
	 * @var integer
	 */
	protected $currentKey = 0;

	/**
	 * If set to true, instead of returning the DataObject, DataObject->getArray() is returned.
	 *
	 * @var bool
	 * @see asArrays()
	 */
	protected $returnDataObjectsAsArray = false;


	/**
	 * Returns the current key (row number).
	 * @return int
	 */
	public function key() {
		return $this->currentKey;
	}



	/**
	 * Return the current DataObject.
	 * If getAsArray() has been called, returns an array instead of the DataObject.
	 *
	 * @return DataObject|array
	 */
	public function current() {
		$dataObject = $this->dao->getObjectFromData($this->currentData);
		return $this->returnDataObjectsAsArray ? $dataObject->getArray() : $dataObject;
	}


	/**
	 * @param bool $returnDataObjectsAsArray
	 * @see $returnDataObjectsAsArray
	 * @return DaoResultIterator Returns itself for chaining.
	 */
	public function asArrays($returnDataObjectsAsArray = true) {
		$this->returnDataObjectsAsArray = !!$returnDataObjectsAsArray;
		return $this;
	}


	/**
	 * Check if the pointer is still valid.
	 *
	 * @return bool
	 */
	public function valid() {
		return ($this->currentData != false);
	}

	/**
	 * Return the number of rows.
	 *
	 * @return int
	 */
	public function count() {
		return $this->length;
	}

}


?>