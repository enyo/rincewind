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
 * @see Dao
 **/
interface DaoInterface {

  /**
   * This is the method to get a DataObject from the datasource.
   * If you want to select more objects, call getIterator.
   * If you call get() without parameters, a "raw object" will be returned, containing
   * only default values, and null as id.
   * If the entry is not found, an exception is thrown.
   *
   * @param array|DataObject $map A map or DataObject containing the column assignments.
   * @param bool $exportValues When you want to have complete control over the $map
   *                           column names, you can set exportValues to false, so they
   *                           won't be processed.
   *                           WARNING: Be sure to escape them yourself if you do so.
   * @param string $resourceName You can specify a different resource (most probably a view)
   *                          to get data from.
   *                          If not set, $this->viewName will be used if present; if not
   *                          $this->resourceName is used.
   * @return DataObject
   * @see find()
   */
  public function get($map = null, $exportValues = true, $resourceName = null);


  /**
   * This function does the same as get(), but returns null if the data is not found.
   *
   * @param array|DataObject $map
   * @param bool $exportValues
   * @param string $resourceName
   * @return DataObject
   * @see get()
   */
  public function find($map = null, $exportValues = true, $resourceName = null);


  /**
   * The same as get(), but returns an array with the data.
   *
   * @param array|DataObject $map
   * @param bool $exportValues
   * @param string $resourceName
   * @return array
   * @see get()
   */
  public function getData($map, $exportValues = true, $resourceName = null);


  /**
   * The same as get, but returns an iterator to go through all the rows.
   *
   * @param array $map
   * @param string|array $sort can be an array with ASCENDING values, or a map like
   *                           this: array('login'=>Dao::DESC), or simply a string 
   *                           containing the column. This value will be passed to
   *                           generateSortString()
   * @param int $offset 
   * @param int $limit 
   * @param bool $exportValues
   * @param string $resourceName
   * @see get()
   * @return DaoResultIterator
   */
  public function getIterator($map, $sort = null, $offset = null, $limit = null, $exportValues = true, $resourceName = null);

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
   * Takes a DataObject and inserts it in the datasource.
   * @see DataObject
   * @param DataObject
   */
  public function insert($object);

  /**
   * Takes a DataObject and updates it in the datasource.
   * Daos always take the id column to find the right row.
   * @see DataObject
   * @param DataObject
   */
  public function update($object);

  /**
   * Takes a DataObject and delets it in the datasource. (Again the id is used)
   * @see DataObject
   * @param DataObject
   */
  public function delete($object);

  /**
   * Takes an id and deletes it in the datasource.
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
   * Returns the total number of entries
   *
   * @return int
   */
  public function getTotalCount();

  /**
   * Returns an object filled with an array of datasource values.
   * (Typically this array comes from DatabaseResult::fetchArray())
   *
   * @param array $data
   * @return DataObject
   */
  public function getObjectFromData($data);

}

?>
