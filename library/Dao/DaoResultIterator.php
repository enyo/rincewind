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
	 * If set to true, instead of returning the DataObject, DataObject->getArray() is returned.
	 *
	 * @var bool
	 * @see asArrays()
	 */
	protected $returnDataObjectsAsArray = false;

	/**
	 * @param bool $returnDataObjectsAsArray
	 * @see $returnDataObjectsAsArray
	 * @return DaoResultIterator Returns itself for chaining.
	 */
	public function asArrays($returnDataObjectsAsArray = true) {
		$this->returnDataObjectsAsArray = !!$returnDataObjectsAsArray;
		return $this;
	}

}


?>