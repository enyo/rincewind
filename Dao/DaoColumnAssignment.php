<?php

	/**
	 * @author     Matthias Loitsch <develop@matthias.loitsch.com>
	 * @copyright  Copyright (c) 2009, Matthias Loitsch
	 */



	/**
	 * This is a column assignment.
	 * When you select rows with a Dao, you can either pass an associative array containing the value assignments or a list of DaoColumnAssignments.
	 * An associative array is the same as an array of DaoColumnAssignments where the operator is always =
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
	function DaoColumnAssignment($column, $value, $operator = '=') { return new DaoColumnAssignment($column, $value, $operator); }
	
	
?>