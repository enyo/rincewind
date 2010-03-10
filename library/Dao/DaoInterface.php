<?php

/**
 * This file contains the Dao interface definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/


/**
 * The DaoInterface
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/
interface DaoInterface {

	/**
	 * @param int $id
	 * @return DataObject
	 */
	public function getById($id);

	/**
	 * Retrieves all rows
	 * @return DaoResultIterator
	 */
	public function getAll();

	/**
	 * Takes a DataObject and inserts it in the database.
	 * @see DataObject
	 * @param DataObject
	 */
	public function insert($object);

	/**
	 * Takes a DataObject and updates it in the database.
	 * Daos always take the id column to find the right row.
	 * @see DataObject
	 * @param DataObject
	 */
	public function update($object);

	/**
	 * Takes a DataObject and delets it in the database. (Again the id is used)
	 * @see DataObject
	 * @param DataObject
	 */
	public function delete($object);

	/**
	 * Takes an id and deletes it in the database.
	 * @param int $id
	 */
	public function deleteById($id);


	/**
	 * Begins a transaction
	 */
	public function beginTransaction();

	/**
	 * Commits a transaction
	 */
	public function commit();

	/**
	 * Rolls back a transaction
	 */
	public function rollback();

	/**
	 * Returns all the column types.
	 * columnTypes is an associative array. Eg: array('id'=>Dao::INT)
	 *
	 * @return array
	 */
	public function getColumnTypes();

	/**
	 * Returns all the additional column types.
	 * columnTypes is an associative array. Eg: array('parent_name'=>Dao::STRING)
	 *
	 * @return array
	 */
	public function getAdditionalColumnTypes();

	/**
	 * Returns all the column that can be null.
	 * nullColumns is an associative array. Eg: array('id'=>Dao::INT)
	 *
	 * @return array
	 */
	public function getNullColumns();



	/**
	 * Returns an object filled with an array of database values.
	 * (Typically this array comes from DatabaseResult::fetchArray())
	 *
	 * @param array $data
	 * @return DataObject
	 */
	public function getObjectFromDatabaseData($data);

}

?>
