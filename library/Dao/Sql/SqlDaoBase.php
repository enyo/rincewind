<?php

/**
 * This file contains the SQL Dao definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * */
/**
 * Loading the abstract Dao Class
 */
if ( ! class_exists('Dao', false)) include dirname(__FILE__) . '/../Dao.php';


/**
 * Loading the SqlResultIterator
 */
include dirname(__FILE__) . '/SqlResultIterator.php';

/**
 * The SqlDaoBase has all the main functionality for SQL databases.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * */
class SqlDaoBase extends Dao {

  /**
   * @var Database
   */
  protected $db;

  /**
   * @param Database $db
   * @param string $resourceName You should specify this as an attribute when writing a Dao implementation
   * @param array $attributes You should specify this as an attribute when writing a Dao implementation
   * @param array $nullAttributes You should specify this as an attribute when writing a Dao implementation
   * @param array $defaultValueAttributes You should specify this as an attribute when writing a Dao implementation
   */
  public function __construct($db) {
    parent::__construct();
    $this->db = $db;
  }

  /**
   * Returns the database.
   *
   * @see Database
   * @return Database
   */
  public function getDb() {
    return $this->db;
  }

  /**
   * Creates a Dao.
   * You probably want to overwrite this method in your daos to use your implementation of instantiating Daos.
   *
   * @param string $daoName
   * @return SqlDao
   */
  public function createDao($daoName) {
    $daoClassName = $daoName . 'Dao';
    return new $daoClassName($this->getDb());
  }

  /**
   * @return int The last id that has been inserted
   */
  protected function getLastInsertId() {
    return $this->db->getLastInsertId($this->resourceName);
  }

  /**
   * Creates an iterator for a sql result.
   *
   * @param result $result
   * @return SqlResultIterator
   */
  public function createIterator($result) {
    return new SqlResultIterator($result, $this);
  }

  /**
   * For most SQL Database simply calling strtotime() will work.
   *
   * @param string $databaseValue
   * @param bool $withTime
   */
  protected function convertRemoteValueToTimestamp($databaseValue, $withTime) {
    return strtotime($databaseValue);
  }

  /**
   * Shorthand for: Dao->getDb()->beginTransaction()
   * Be careful: this starts a transaction on the database! So all Daos using the same database will be in the same transaction.
   */
  public function startTransaction() {
    $this->db->startTransaction();
  }

  /**
   * Shorthand for: Dao.getDb()->commit()
   * Be careful: this starts a transaction on the database! So all Daos using the same database will be in the same transaction
   */
  public function commit() {
    $this->db->commit();
  }

  /**
   * Shorthand for: Dao.getDb()->rollback()
   * Be careful: this starts a transaction on the database! So all Daos using the same database will be in the same transaction
   */
  public function rollback() {
    $this->db->rollback();
  }

  /**
   * Down the road, this is the only function that actually gets the data out of the data source.
   *
   * get, find, getData, findData
   *
   * @param array|Record $map
   * @param bool $exportValues
   * @param string $resourceName
   * @see get()
   * @see find()
   * @see getData()
   * @see findData()
   * @return array
   */
  public function doFindData($map, $exportValues = true, $resourceName = null) {
    $data = $this->getDataFromQuery($this->generateQuery($map, $sort = null, $offset = null, $limit = 1, $exportValues, $resourceName ? $resourceName : ($this->viewName ? $this->viewName : $this->resourceName)));
    return $data ? $data : null;
  }

  /**
   * The same as get, but returns an iterator to go through all the rows.
   *
   * @param array|Record $map
   * @param string|array $sort
   * @param int $offset 
   * @param int $limit 
   * @param bool $exportValues
   * @param string $resourceName
   * @param bool $retrieveTotalRowCount
   * @see get()
   * @see generateQuery()
   * @return DaoResultIterator
   */
  protected function doGetIteratorResult($map, $sort, $offset, $limit, $exportValues, $resourceName, $retrieveTotalRowCount, $additionalInfo) {
    if ($retrieveTotalRowCount) throw new DaoException('Retrieving total row count is not yet implemented in the SqlDaoBase.');
    return $this->db->query($this->generateQuery($map, $sort, $offset, $limit, $exportValues, $resourceName ? $resourceName : ($this->viewName ? $this->viewName : $this->resourceName)));
  }

  /**
   * Inserts a record in the database, and updates the record with the new data (in
   * case some default values of the database have been set.)
   *
   * @param Record $record
   * @return Record The updated record.
   */
  public function insert($record) {

    $insertSql = $this->generateInsertQuery($record);

    $newId = $this->insertByQuery($insertSql, $record->id);

    $this->updateRecordWithData($this->getData(array('id' => $newId)), $record);

    $this->afterInsert($record);

    $record->setExistsInDatabase();

    return $record;
  }

  /**
   * Generates the query for insertion.
   * 
   * @param Record $record
   * @return string
   */
  protected function generateInsertQuery($record) {
    $values = $this->getInsertValues($record);
    $columns = array_keys($values);
    return "insert into " . $this->exportResourceName() . " (" . implode(', ', $columns) . ") values (" . implode(', ', $values) . ")";
  }

  /**
   * Updates a record in the database using the id.
   *
   * @param Record $record
   * @return Record The updated record.
   */
  public function update($record) {
    $values = array();
    foreach ($this->getUpdateValues($record) as $column => $value) {
      $values[] = $column . '=' . $value;
    }

    $updateSql = "update " . $this->exportResourceName() . " set " . implode(', ', $values) . " where id=" . $this->exportInteger($record->id);

    $this->db->query($updateSql);

    $this->afterUpdate($record);

    return $record;
  }

  /**
   * Deletes a record in the database using the id.
   *
   * @param Record $record
   */
  public function delete($record) {
    $this->db->query("delete from " . $this->exportResourceName() . " where id=" . $this->exportValue('id', $record->id, $this->attributes['id']));
    $this->afterDelete($record);
  }

  /**
   * Returns the total row count in the resource the Dao is assigned to.
   *
   * @return int
   */
  public function getTotalCount() {
    return $this->db->query("select count(id) as count from " . $this->exportResourceName())->fetch('count');
  }

  /**
   * This function generates the SQL query for the getters.
   *
   * @param array|Record $map A map or record containing the attributes.
   * @param string|array $sort can be an array with ASCENDING values, or a map like
   *                           this: array('login'=>Dao::DESC), or simply a string
   *                           containing the attribute. This value will be passed to
   *                           generateSortString()
   * @param int $offset 
   * @param int $limit 
   * @param bool $exportValues When you want to have complete control over the $map
   *                           attributes, you can set exportValues to false, so
   *                           they won't be processed.
   *                           WARNING: Be sure to escape them yourself if you do so.
   * @param string $resourceName You can pass a different resource name than the default
   *                          one. This is mostly used for views. 
   * @return string
   */
  protected function generateQuery($map, $sort = null, $offset = null, $limit = null, $exportValues = true, $resourceName = null) {
    if ($offset !== null && ! is_int($offset) ||
        $limit !== null && ! is_int($limit) ||
        ! is_bool($exportValues)) {
      $trace = debug_backtrace();
      trigger_error('Wrong parameters in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_ERROR);
    }

    if ( ! is_array($map)) throw new DaoWrongValueException("The passed map is not an array.");

    $assignments = array();

    foreach ($map as $column => $value) {
      if ($value instanceof DaoAttributeAssignment) {
        $column = $value->attributeName;
        $operator = $value->operator;
        $value = $value->value;
      }
      else {
        $operator = '=';
      }

      if ( ! isset($this->attributes[$column])) {
        $trace = debug_backtrace();
        trigger_error('The type for attribute ' . $column . ' was not found in ' . $trace[2]['file'] . ' on line ' . $trace[2]['line'], E_USER_ERROR);
      }
      $escapedValue = $value;
      if ($exportValues) {
        $type = $this->attributes[$column];
        $escapedValue = $this->exportValue($column, $value, $type, $this->notNull($column));
      }
      $assignments[] = $this->exportAttributeName($column) . ($value === null ? ($operator == '=' ? ' is null' : ' is not null') : $operator . $escapedValue);
    }

    $sort = $this->generateSortString($sort);

    $query = 'select * from ' . $this->exportResourceName($resourceName);
    if (count($assignments) > 0) $query .= ' where ' . implode(' and ', $assignments);
    $query .= " " . $sort;
    if ($offset !== null) {
      $query .= " offset " . intval($offset);
    }
    if ($limit !== null) {
      $query .= " limit " . intval($limit);
    }
    return $query;
  }

  /**
   * Returns a data array from a query.
   * Returns null if nothing found.
   *
   * @param string $query
   * @return Record|array
   */
  protected function getDataFromQuery($query) {
    $result = $this->db->query($query);
    if ($result->numRows() == 0) return null;

    return $result->fetchArray();
  }

  /**
   * Inserts and returns the new id
   *
   * @param string $query The query.
   * @param int $id If an insert is done with an id, you can pass it. If not, the last insert id is used.
   *
   * @return int The id.
   */
  protected function insertByQuery($query, $id = null) {
    $this->db->query($query);
    $id = $id ? $id : $this->getLastInsertId();
    return $id;
  }

  /**
   * This method takes the $sort attribute and returns a typical 'order by ' SQL string.
   * If $sort is false then the $this->defaultSort is used if it exists.
   * The sort is passed to interpretSortVariable to get a valid attribute list.
   *
   * @param string|array $sort
   */
  public function generateSortString($sort) {
    if ( ! $sort) {
      $sort = $this->defaultSort;
    }

    if ($sort) {
      $columnArray = $this->interpretSortVariable($sort);
    }

    if ( ! $columnArray) return '';

    return ' order by ' . implode(', ', $columnArray);
  }

  /**
   * Returns a formatted date, and escaped with exportString.
   * @param Date $date
   * @param bool $withTime
   * @return string
   */
  public function exportDate($date) {
    return $this->exportString($date->format('Y-m-d'));
  }

  /**
   * Returns a formatted date, and escaped with exportString.
   * @param Date $date
   * @param bool $withTime
   * @return string
   */
  public function exportDateWithTime($date) {
    return $this->exportString($date->format('Y-m-d H:i:s'));
  }

  /**
   * Wrapper for the db.
   *
   * @param string $string
   * @return string
   */
  public function escapeString($string) {
    return $this->db->escapeString($string);
  }

  /**
   * Wrapper for the db.
   *
   * @param string $string
   * @return string
   */
  public function exportString($string) {
    return $this->db->exportString($string);
  }

  /**
   * Wrapper for the db.
   *
   * @param string $attributeName
   * @return string
   */
  public function escapeAttributeName($attributeName) {
    return $this->db->escapeColumn($this->applyAttributeExportMapping($this->convertAttributeNameToDatasourceName($attributeName)));
  }

  /**
   * Wrapper for the db.
   *
   * @param string $string
   * @return string
   */
  public function exportAttributeName($attributeName) {
    return $this->db->exportColumn($this->applyAttributeExportMapping($this->convertAttributeNameToDatasourceName($attributeName)));
  }

  /**
   * Wrapper for the db.
   *
   * @param string $resourceName
   * @return string
   */
  public function escapeResourceName($resourceName = null) {
    return $this->db->escapeTable($resourceName ? $resourceName : $this->resourceName);
  }

  /**
   * Wrapper for the db.
   *
   * @param string $resourceName
   * @return string
   */
  public function exportResourceName($resourceName = null) {
    return $this->db->exportTable($resourceName ? $resourceName : $this->resourceName);
  }

}

