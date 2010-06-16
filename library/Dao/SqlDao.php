<?php

/**
 * This file contains the SQL Dao definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/

/**
 * Loading the abstract Dao Class
 */
if (!class_exists('Dao', false)) include dirname(__FILE__) . '/Dao.php';


/**
 * The SqlDao has all the main functionality for SQL databases.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/
abstract class SqlDao extends Dao {

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
  public function __construct($db, $resourceName = null, $attributes = null, $nullAttributes = null, $defaultValueAttributes = null) {
    parent::__construct($resourceName, $attributes, $nullAttributes, $defaultValueAttributes);
    $this->db = $db;
  }


  /**
   * Returns the database.
   *
   * @see Database
   * @return Database
   */
  public function getDb() { return $this->db; }

  /**
   * Creates a Dao.
   * You probably want to overwrite this method in your daos to use your implementation of instantiating Daos.
   *
   * @param string $daoClassName
   * @return SqlDao
   */
  protected function createDao($daoClassName) {
    return new $daoClassName($this->getDb());
  }

  /**
   * @return int The last id that has been inserted
   */
  abstract protected function getLastInsertId();

  /**
   * Creates an iterator from a php result.
   *
   * @param result $result
   * @return DaoResultIterator
   */
  abstract protected function createIterator($result);

  /**
   * For most SQL Database simply calling strtotime() will work.
   *
   * @param string $databaseValue
   * @param bool $withTime
   */
  protected function convertRemoteValueToTimestamp($databaseValue, $withTime) { return strtotime($databaseValue); }


  /**
   * Calls the internal db->escapeString function
   * 
   * @param string $string
   * @return string
   */
  protected function escapeString($string) {
    return $this->db->escapeString($string);
  }

  /**
   * Calls the internal db->escapeColumn function
   * 
   * @param string $attributeName
   * @return string
   */
  protected function escapeAttributeName($attributeName) {
    return $this->db->escapeColumn($attributeName);
  }
  
  /**
   * Calls the internal db->escapeResourceName function
   * 
   * @param string $resourceName
   * @return string
   */
  protected function escapeResourceName($resourceName = null) {
    return $this->db->escapeTable($resourceName ? $resourceName : $this->resourceName);
  }
  


  /**
   * Shorthand for: Dao.getDb()->beginTransaction()
   * Be careful: this starts a transaction on the database! So all Daos using the same database will be in the same transaction
   */
  public function beginTransaction() { $this->db->beginTransaction(); }

  /**
   * Shorthand for: Dao.getDb()->commit()
   * Be careful: this starts a transaction on the database! So all Daos using the same database will be in the same transaction
   */
  public function commit() { $this->db->commit(); }

  /**
   * Shorthand for: Dao.getDb()->rollback()
   * Be careful: this starts a transaction on the database! So all Daos using the same database will be in the same transaction
   */
  public function rollback() { $this->db->rollback(); }




  /**
   * This is the method to get a Record from the database.
   * If you want to select more records, call getIterator.
   * This function returns null if nothing has been found. If you want it to throw an exception (for
   * chaining) you can call get(), which is mostly a wrapper for find().
   * 
   *
   * @param array|Record $map A map or record containing the attributes.
   * @param bool $exportValues When you want to have complete control over the $map
   *                           attributes, you can set exportValues to false, so they
   *                           won't be processed.
   *                           WARNING: Be sure to escape them yourself if you do so.
   * @param string $resourceName You can specify a different resource (most probably a view)
   *                          to get data from.
   *                          If not set, $this->viewName will be used if present; if not
   *                          $this->resourceName is used.
   * @see generateQuery()
   * @see get()
   * @return Record
   */
  public function find($map = null, $exportValues = true, $resourceName = null) {
    return $this->getFromQuery($this->generateQuery($map, $sort = null, $offset = null, $limit = 1, $exportValues, $resourceName ? $resourceName : ($this->viewName ? $this->viewName : $this->resourceName)));
  }

  /**
   * Same as get() but returns an array with the data instead of a record
   *
   * @param array|Record $map
   * @param bool $exportValues
   * @param string $resourceName
   * @see get()
   * @see generateQuery()
   * @return array
   */
  public function getData($map, $exportValues = true, $resourceName = null) {
    $data = $this->getFromQuery($this->generateQuery($map, $sort = null, $offset = null, $limit = 1, $exportValues, $resourceName ? $resourceName : ($this->viewName ? $this->viewName : $this->resourceName)), $returnData = true);
    if (!$data) throw new DaoNotFoundException("The query did not return any results.");
    return $this->prepareDataForRecord($data);
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
   * @see get()
   * @see generateQuery()
   * @return DaoResultIterator
   */
  public function getIterator($map, $sort = null, $offset = null, $limit = null, $exportValues = true, $resourceName = null) {
    return $this->getIteratorFromQuery($this->generateQuery($map, $sort, $offset, $limit, $exportValues, $resourceName ? $resourceName : ($this->viewName ? $this->viewName : $this->resourceName)));
  }



  /**
   * Inserts a record in the database, and updates the record with the new data (in
   * case some default values of the database have been set.)
   *
   * @param Record $record
   * @return Record The updated record.
   */
  public function insert($record) {
    
    list ($columns, $values) = $this->generateInsertArrays($record);

    $insertSql = "insert into " . $this->exportResourceName() . " (" . implode(', ', $columns) . ") values (" . implode(', ', $values) . ");";
    
    $newId = $this->insertByQuery($insertSql, $record->id);

    $this->updateRecordWithData($this->getData(array('id'=>$newId)), $record);
    
    $this->afterInsert($record);

    $record->setExistsInDatabase();

    return $record;
  }

  /**
   * Updates a record in the database using the id.
   *
   * @param Record $record
   * @return Record The updated record.
   */
  public function update($record) {
    $values = array();
    foreach ($this->attributes as $column=>$type) {
      if ($column != 'id' && $type != Dao::IGNORE) $values[] = $this->exportAttributeName($column) . '=' . $this->exportValue($record->get($column), $type, $this->notNull($column));
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
    $this->db->query("delete from " . $this->exportResourceName() . " where id=" . $this->exportValue($record->id, $this->attributes['id']));
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
    if ($offset !== null && !is_int($offset) ||
      $limit !== null && !is_int($limit) ||
      !is_bool($exportValues)) {
      $trace = debug_backtrace();
      trigger_error('Wrong parameters in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_ERROR);
    }

    $map = $this->interpretMap($map);

    $assignments = array();

    foreach($map as $column=>$value) {
      if ($value instanceof DaoAttributeAssignment) {
        $column   = $value->attributeName;
        $operator = $value->operator;
        $value    = $value->value;
      }
      else { $operator = '='; }

      if (!isset($this->attributes[$column])) {
        $trace = debug_backtrace();
        trigger_error('The type for attribute ' . $column . ' was not found in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_ERROR);
      }
      $escapedValue = $value;
      if ($exportValues) {
        $type = $this->attributes[$column];
        $escapedValue = $this->exportValue($value, $type, $this->notNull($column));
      }
      $assignments[] = $this->exportAttributeName($column) . ($value === null ? ($operator == '=' ? ' is null' : ' is not null') : $operator . $escapedValue);
    }

    $sort = $this->generateSortString($sort);

    $query  = 'select * from ' . $this->exportResourceName($resourceName);
    if (count($assignments) > 0) $query .= ' where ' . implode(' and ', $assignments);
    $query .= " " . $sort;
    if ($offset !== null) { $query .= " offset " . intval($offset); }
    if ($limit  !== null) { $query .= " limit " . intval($limit); }
    return $query;
  }



  /**
   * Returns a Record or data array from a query.
   * Returns null if nothing found.
   *
   * @param string $query
   * @param bool $returnData If set to true, only the data as array is returned, not a Record.
   * @return Record|array
   */
  protected function getFromQuery($query, $returnData = false) {
    $result = $this->db->query($query);
    if ($result->numRows() == 0) return null;

    if ($returnData) return $result->fetchArray();

    return $this->getRecordFromData($result->fetchArray());
  }


  /**
   * Returns an Iterator for a query
   *
   * @param string $query
   * @return SqlResultIterator
   */
  protected function getIteratorFromQuery($query) {
    return $this->createIterator($this->db->query($query));
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
    if (!$sort) {
      $sort = $this->defaultSort;
    }
    
    if ($sort) {
      $columnArray = $this->interpretSortVariable($sort);
    }

    if (!$columnArray) return '';

    return ' order by ' . implode(', ', $columnArray);
  }


  /**
   * Returns a formatted date, and escaped with exportString.
   * @param Date $date
   * @param bool $withTime
   * @return string
   */
  public function exportDate($date, $withTime) {
    return $this->exportString($date->format('Y-m-d' . ($withTime ? ' H:i:s' : '')));
  }

}

