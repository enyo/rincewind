<?php

	/**
	 * This file contains the DatabaseResult class definition.
	 *
	 * @author Matthias Loitsch <developer@ma.tthias.com>
	 * @copyright Copyright (c) 2010, Matthias Loitsch
	 * @package DatabaseResult
	 **/


	/**
	 * Loading the interface
	 */
	include dirname(__FILE__) . '/DatabaseResultInterface.php';


	/**
	 * If you implement a DatabaseResult (eg: for mysql) extend this class.
	 *
	 * @author Matthias Loitsch <developer@ma.tthias.com>
	 * @copyright Copyright (c) 2010, Matthias Loitsch
	 * @package DatabaseResult
	 **/
	abstract class DatabaseResult implements DatabaseResultInterface {

		/**
		 * @var mixed The result from the query (eg: pg_query)
		 */
		protected $result = false;

		/**
		 * The php result from a query is passed in the constructor
		 *
		 * @param mixed $result
		 */
		public function __construct ($result) {
			$this->result = $result;
		}


		/**
		 * This is a shortcut for fetchArray or fetchResult.
		 *
		 * @param string $field If null, fetchArray is called, if passed fetchResult.
		 */
		public function fetch($field = null) {
			return $field ? $this->fetchResult($field) : $this->fetchArray();
		}

		/**
		 * Sets the internal row pointer to row 0
		 */
		public function reset() {
			$this->seek(0);
		}

	
	}



?>
