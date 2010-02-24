<?php


	interface DatabaseResultInterface {

		/**
		 * Sets the internal pointer to specific row
		 */
		public function seek($rowNumber);


		/**
		 * If $field is given, fetchResult is used.
		 *
		 * @param string $field
		 */
		public function fetch($field = null);


		/**
		 * Returns the current row as an associative array.
		 */
		public function fetchArray();


		/**
		 * Returns the value of one field in the current row.
		 *
		 * @param string $field
		 */
		public function fetchResult($field);
	
	
		/**
		 * Returns the number of rows
		 */
		public function numRows();


		/**
		 * Resets the pointer to 0
		 */
		public function reset();
	
	}


?>
