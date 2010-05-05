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
include dirname(__FILE__) . '/Dao.php';


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
   * @param string $tableName You can specify this as an attribute when writing a Dao implementation
   * @param array $columnTypes You can specify this as an attribute when writing a Dao implementation
   * @param array $nullColumns You can specify this as an attribute when writing a Dao implementation
   * @param array $defaultColumns You can specify this as an attribute when writing a Dao implementation
   */
  public function __construct($db, $tableName = null, $columnTypes = null, $nullColumns = null, $defaultColumns = null) {
    $this->db = $db;
    if ($tableName) $this->tableName = $tableName;
    if ($columnTypes) $this->columnTypes = $columnTypes;
    if ($nullColumns) $this->nullColumns = $nullColumns;
    if ($defaultColumns) $this->defaultColumns = $defaultColumns;
  }


  /**
   * Returns the database.
   *
   * @see Database
   * @return Database
   */
  public function getDb() { return $this->db; }

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
   * @param string $column
   * @return string
   */
  protected function escapeColumn($column) {
    return $this->db->escapeColumn($column);
  }
  
  /**
   * Calls the internal db->escapeTable function
   * 
   * @param string $table
   * @return string
   */
  protected function escapeTable($table = null) {
    return $this->db->escapeTable($table ? $table : $this->tableName);
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
   * This is the method to get a DataObject from the database.
   * If you want to select more objects, call getIterator.
   * If you call get() without parameters, a "raw object" will be returned, containing
   * only default values, and null as id.
   *
   * @param array $map A map containing the column assignments.
   * @param bool $exportValues When you want to have complete control over the $map
   *                           column names, you can set exportValues to false, so they
   *                           won't be processed.
   *                           WARNING: Be sure to escape them yourself if you do so.
   * @param string $tableName You can specify a different table (most probably a view)
   *                          to get data from.
   *                          If not set, $this->viewName will be used if present; if not
   *                          $this->tableName is used.
   * @see generateQuery()
   * @return DataObject
   */
  public function get($map = null, $exportValues = true, $tableName = null) {
    if (!$map) return $this->getRawObject();
    return $this->getFromQuery($this->generateQuery($map, $sort = null, $offset = null, $limit = 1, $exportValues, $tableName ? $tableName : ($this->viewName ? $this->viewName : $this->tableName)));
  }

  /**
   * Same as get() but returns an array with the data instead of an object
   *
   * @param array $map
   * @param bool $exportValues
   * @param string $tableName
   * @see get()
   * @see generateQuery()
   * @return array
   */
  protected function getData($map, $exportValues = true, $tableName = null) {
    return $this->getFromQuery($this->generateQuery($map, $sort = null, $offset = null, $limit = 1, $exportValues, $tableName ? $tableName : ($this->viewName ? $this->viewName : $this->tableName)), $returnData = true);
  }

  /**
   * The same as get, but returns an iterator to go through all the rows.
   *
   * @param array $map
   * @param string|array $sort
   * @param int $offset 
   * @param int $limit 
   * @param bool $exportValues
   * @param string $tableName
   * @see get()
   * @see generateQuery()
   * @return DaoResultIterator
   */
  public function getIterator($map, $sort = null, $offset = null, $limit = null, $exportValues = true, $tableName = null) {
    return $this->getIteratorFromQuery($this->generateQuery($map, $sort, $offset, $limit, $exportValues, $tableName ? $tableName : ($this->viewName ? $this->viewName : $this->tableName)));
  }



  /**
   * Inserts an object in the database, and updates the object with the new data (in
   * case some default values of the database have been set.)
   *
   * @param DataObject $object
   * @return DataObject The updated object.
   */
  public function insert($object) {
    
    list ($columns, $values) = $this->generateInsertArrays($object);

    $insertSql = "insert into " . $this->exportTable() . " (" . implode(', ', $columns) . ") values (" . implode(', ', $values) . ");";
    
    $newId = $this->insertByQuery($insertSql, $object->id);

    $this->updateObjectWithData($this->getData(array('id'=>$newId)), $object);
    
    $this->afterInsert($object);
    
    return $object;
  }

  /**
   * Updates an object in the database using the id.
   *
   * @param DataObject $object
   * @return DataObject The updated object.
   */
  public function update($object) {
    $values = array();
    foreach ($this->columnTypes as $column=>$type) {
      if ($column != 'id' && $type != Dao::IGNORE) $values[] = $this->exportColumn($column) . '=' . $this->exportValue($object->getValue($column), $type, $this->notNull($column));
    }

    $updateSql = "update " . $this->exportTable() . " set " . implode(', ', $values) . " where id=" . $this->exportInteger($object->id);

    $this->db->query($updateSql);

    $this->afterUpdate($object);

    return $object;
  }

  /**
   * Deletes an object in the database using the id.
   *
   * @param DataObject $object
   */
  public function delete($object) {
    $this->db->query("delete from " . $this->exportTable() . " where id=" . $this->exportValue($object->id, $this->columnTypes['id']));
    $this->afterDelete($object);
  }



  /**
   * Returns the total row count in the table the Dao is assigned to.
   *
   * @return int
   */
  public function getTotalCount() {
    return $this->db->query("select count(id) as count from " . $this->exportTable())->fetch('count');
  }


  /**
   * This function generates the SQL query for the getters.
   *
   * @param array $map A map containing the column assignments.
   * @param string|array $sort can be an array with ASCENDING values, or a map like
   *                           this: array('login'=>Dao::DESC), or simply a string
   *                           containing the column. This value will be passed to
   *                           generateSortString()
   * @param int $offset 
   * @param int $limit 
   * @param bool $exportValues When you want to have complete control over the $map
   *                           column names, you can set exportValues to false, so
   *                           they won't be processed.
   *                           WARNING: Be sure to escape them yourself if you do so.
   * @param string $tableName You can pass a different table name than the default
   *                          one. This is mostly used for views. 
   * @return string
   */
  protected function generateQuery($map, $sort = null, $offset = null, $limit = null, $exportValues = true, $tableName = null) {
    if ($offset !== null && !is_int($offset) ||
      $limit !== null && !is_int($limit) ||
      !is_bool($exportValues)) {
      $trace = debug_backtrace();
      trigger_error('Wrong parameters in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_ERROR);
    }

    $assignments = array();

    foreach($map as $column=>$value) {
      if ($value instanceof DAOColumnAssignment) {
        $column   = $value->column;
        $operator = $value->operator;
        $value    = $value->value;
      }
      else { $operator = '='; }

      if (!isset($this->columnTypes[$column])) {
        $trace = debug_backtrace();
        trigger_error('The type for column ' . $column . ' was not found in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_ERROR);
      }
      $escapedValue = $value;
      if ($exportValues) {
        $type = $this->columnTypes[$column];
        $escapedValue = $this->exportValue($value, $type, $this->notNull($column));
      }
      $assignments[] = $this->exportColumn($column) . ($value === null ? ($operator == '=' ? ' is null' : ' is not null') : $operator . $escapedValue);
    }

    $sort = $this->generateSortString($sort);

    $query  = 'select * from ' . $this->exportTable($tableName);
    if (count($assignments) > 0) $query .= ' where ' . implode(' and ', $assignments);
    $query .= " " . $sort;
    if ($offset !== null) { $query .= " offset " . intval($offset); }
    if ($limit  !== null) { $query .= " limit " . intval($limit); }
    return $query;
  }



  /**
   * Returns a DataObject or data array from a query.
   *
   * @param string $query
   * @param bool $returnData If set to true, only the data as array is returned, not a DataObject.
   * @return DataObject|array
   */
  protected function getFromQuery($query, $returnData = false) {
    $result = $this->db->query($query);
    if ($result->numRows() == 0) { throw new DaoNotFoundException("The query ($query) did not return any results."); }

    if ($returnData) return $result->fetchArray();

    return $this->getObjectFromData($result->fetchArray());
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
   * The sort is passed to interpretSortVariable to get a valid column list.
   *
   * @param string|array $sort
   */
  public function generateSortString($sort) {
    if (!$sort) {
      /* Legacy... */
      if (isset($this->defaultOrderByColumn)) { $sort = $this->defaultOrderByColumn; }
      else { $sort = $this->defaultSort; }
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

?>
