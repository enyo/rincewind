<?php

	class SqlException extends Exception { }

	class SqlConnectionException extends SqlException { }

	class SqlQueryException extends SqlException { }



	interface DatabaseInterface {
	

		/**
		 * Do a query
		 * @param: string -> The query
		 * @param: bool   -> (Optional) If true, the query won't be done, but only printed.
		 */
		public function query($query);

		public function multiQuery($query);


	
		/**
		 * @param string $string
		 */
		public function escapeString($string);
	
		/**
		 * @param string $column
		 */
		public function escapeColumn($column);
	
		/**
		 * @param string $table
		 */
		public function escapeTable($table);


		/**
		 * Returns the database resource
		 */
		public function getResource();


		/**
		 * Returns the last error.
		 */
		public function lastError();
	

	}

?>
