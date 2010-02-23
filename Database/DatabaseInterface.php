<?php
class SqlException extends Exception {
}

interface DatabaseInterface {

	/**
	 * This function connects to the Database.
	 * The arguments to connect to the database are stored in the object.
	 * They should be specified in the __construct ()
	 */
	public function connect();

	/**
	 * This function closes the connection.
	 */
	public function close();

	/**
	 * This function looks if a connection is open.
	 * If not, it calls connect ()
	 */
	public function ensureConnection();

	/**
	 * Do a query
	 * @param: string -> The query
	 * @param: bool   -> (Optional) If true, the query won't be done, but only printed.
	 */
	public function query($query, $print_only = 'bool');

	/**
	 * Returns the last error.
	 */
	public function lastError();

	/**
	 * Prints error, and throws an Exception.
	 */
	public function error();

	/**
	 * Gets the current schema used as search_path
	 */
	public function getSchema();

	/**
	 * Gets the current database name
	 */
	public function getDatabaseName();

}
?>
