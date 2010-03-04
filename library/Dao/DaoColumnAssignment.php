<?php

/**
 * This file contains the DaoColumnAssignment definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/

/**
 * Sometimes an associative array is simply not enough to create an SQL query.
 * The DaoColumnAssignment helps adding a few functions.
 * When you select rows with a Dao, you can either pass an associative array containing the value assignments or a list of DaoColumnAssignments.
 * It is also possible to mix both (if the value is a DaoColumnAssignment it is used as such)
 * An associative array is the same as an array of DaoColumnAssignments where the operator is always =
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 */
class DaoColumnAssignment {
	protected $validOperators = array('=', '>', '<', '>=', '<=', '<>', ' like ');
	public $column;
	public $value;
	public $operator;

	/**
	 * All assignment info is passed in the constructor
	 *
	 * @param string $column The column (eg.: 'login')
	 * @param string $value The value (eg.: 'tom')
	 * @param string $operator Valid operators are: '=', '>', '<', '>=', '<=', '<>', ' like '
	 */
	public function __construct($column, $value, $operator = '=') {
		$this->column = $column;
		$this->value = $value;
		$this->operator = in_array($operator, $this->validOperators) ? $operator : '=';
	}
}

/**
 * This function is a shortcut for creating the DaoColumnAssignment Object
 *
 * @param string $column
 * @param mixed $value
 * @param string $operator
 * @see DaoColumnAssignment::validOperators
 * @return DaoColumnAssignment
 */
function DaoColumnAssignment($column, $value, $operator = '=') { return new DaoColumnAssignment($column, $value, $operator); }
	
	
?>