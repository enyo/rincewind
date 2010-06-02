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
 * Checking for the class is actually faster then include_once
 */
if (!class_exists('Dao', false)) include dirname(__FILE__) . '/Dao.php';



/**
 * The FileSourceDao is used to get a file somewhere, interpret it and act as a normal datasource.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/
abstract class FileSourceDao extends Dao {

  /**
   * @var mixed
   */
  protected $fileDataSource;

  /**
   * @param FileDataSource $fileDataSource The fileDataSource is used to create the requests.
   * @param string $tableName You can specify this as an attribute when writing a Dao implementation
   * @param array $columnTypes You can specify this as an attribute when writing a Dao implementation
   * @param array $nullColumns You can specify this as an attribute when writing a Dao implementation
   * @param array $defaultColumns You can specify this as an attribute when writing a Dao implementation
   */
  public function __construct($fileDataSource, $tableName = null, $columnTypes = null, $nullColumns = null, $defaultColumns = null) {
    parent::__construct($tableName, $columnTypes, $nullColumns, $defaultColumns);
    $this->fileDataSource = $fileDataSource;
  }


  /**
   * Returns the fileDataSource.
   *
   * @return FileDataSource
   */
  public function getFileDataSource() { return $this->fileDataSource; }

  /**
   * Creates an iterator from a data hash.
   *
   * @param array $data
   * @return FileResultIterator
   */
  abstract protected function createIterator($data);

  /**
   * Most file data connections will transfer time values as timestamps.
   *
   * @param string $databaseValue
   * @param bool $withTime
   */
  protected function convertRemoteValueToTimestamp($databaseValue, $withTime) { return (int) $databaseValue; }


  /**
   * Overwrite this if your file connection allows transaction.
   */
  public function beginTransaction() { throw new DaoNotSupportedException("Transactions are not implemented"); }

  /**
   * Overwrite this if your file connection allows transaction.
   */
  public function commit() { throw new DaoNotSupportedException("Transactions are not implemented"); }

  /**
   * Overwrite this if your file connection allows transaction.
   */
  public function rollback() { throw new DaoNotSupportedException("Transactions are not implemented"); }




  /**
   * This is the method to get a DataObject from the database.
   * If you want to select more objects, call getIterator.
   * If you call get() without parameters, a "raw object" will be returned, containing
   * only default values, and null as id.
   *
   * @param array|DataObject $map A map containing the column assignments.
   * @param bool $exportValues When you want to have complete control over the $map
   *                           column names, you can set exportValues to false, so they
   *                           won't be processed.
   *                           WARNING: Be sure to escape them yourself if you do so.
   * @param string $tableName You can specify a different table (most probably a view)
   *                          to get data from.
   *                          If not set, $this->viewName will be used if present; if not
   *                          $this->tableName is used.
   * @return DataObject
   */
  public function find($map = null, $exportValues = true, $tableName = null) {
    $content = $this->fileDataSource->view($this->exportTable($tableName ? $tableName : ($this->viewName ? $this->viewName : $this->tableName)), $exportValues ? $this->exportMap($map) : $map);

    $data = $this->interpretFileContent($content);

    if (!$data || !is_array($data)) return null;

    return $this->getObjectFromData($data);
  }

  /**
   * Same as get() but returns an array with the data instead of an object
   *
   * @param array|DataObject $map
   * @param bool $exportValues
   * @param string $tableName
   * @see get()
   * @return array
   */
  public function getData($map, $exportValues = true, $tableName = null) {
    $data = $this->interpretFileContent($this->fileDataSource->view($this->exportTable($tableName ? $tableName : ($this->viewName ? $this->viewName : $this->tableName)), $exportValues ? $this->exportMap($map) : $map));
    if (!$data || !is_array($data)) throw new DaoNotFoundException("The query did not return any results.");
    return $this->prepareDataForObject($data);
  }

  /**
   * Converts the content returned by the file factory into a usable data array.
   * @param string $content
   * @return array The data array
   */
  abstract protected function interpretFileContent($content);

  /**
   * The same as get, but returns an iterator to go through all the rows.
   *
   * @param array|DataObject $map
   * @param string|array $sort
   * @param int $offset 
   * @param int $limit 
   * @param bool $exportValues
   * @param string $tableName
   * @see get()
   * @return DaoResultIterator
   */
  public function getIterator($map, $sort = null, $offset = null, $limit = null, $exportValues = true, $tableName = null) {
    $content = $this->fileDataSource->viewList($this->exportTable($tableName ? $tableName : ($this->viewName ? $this->viewName : $this->tableName)), $exportValues ? $this->exportMap($map) : $map, $this->generateSortString($sort), $offset, $limit);

    $data = $this->interpretFileContent($content);

    return $this->getIteratorFromData($data);
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

    $attibutes = array_combine($columns, $values);

    $id = $this->fileDataSource->insert($this->tableName, $attributes);

    $this->updateObjectWithData($this->getData(array('id'=>$newId)), $object);
    
    $this->afterInsert($object);

    $object->setExistsInDatabase();

    return $object;
  }

  /**
   * Updates an object in the database using the id.
   *
   * @param DataObject $object
   * @return DataObject The updated object.
   */
  public function update($object) {
    $attributes = array();

    foreach ($this->columnTypes as $column=>$type) {
      if ($column != 'id' && $type != Dao::IGNORE) $attributes[$column] = $this->exportValue($object->getValue($column), $type, $this->notNull($column));
    }

    $this->fileDataSource->update($this->tableName, $object->id, $attributes);

    $this->afterUpdate($object);

    return $object;
  }

  /**
   * Deletes an object in the database using the id.
   *
   * @param DataObject $object
   */
  public function delete($object) {
    $this->fileDataSource->delete($this->tableName, $object->id);
    $this->afterDelete($object);
  }

  /**
   * Returns the total row count in the table the Dao is assigned to.
   *
   * @return int
   */
  public function getTotalCount() {
    return $this->fileDataSource->getTotalCount($this->tableName);
  }

  /**
   * This function exports every values of a map
   *
   * @param array|DataObject $map A map or DataObject containing the column assignments.
   * @return array The map with exported values.
   */
  protected function exportMap($map) {

    $map = $this->interpretMap($map);

    $assignments = array();

    foreach($map as $column=>$value) {
      if ($value instanceof DAOColumnAssignment) {
        throw new DaoNotSupportedException("DAOColumnAssignments don't work yet for FileDaos.");
      }

      if (!isset($this->columnTypes[$column])) {
        $trace = debug_backtrace();
        trigger_error('The type for column ' . $column . ' was not found in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_ERROR);
      }

      $type = $this->columnTypes[$column];
      $map[$this->exportColumn($column)] = $this->exportValue($value, $type, $this->notNull($column));
    }

    return $map;
  }


  /**
   * Returns an Iterator for data
   *
   * @param string $data
   * @return FileResultIterator
   */
  protected function getIteratorFromData($data) {
    return $this->createIterator($data, $this);
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

    return implode(' ', $columnArray);
  }


  /**
   * Takes a php column name, converts it via import/export column mapping and calls escapeColumn.
   * This is the correct way to insert column names in a map.
   *
   * @param string $column
   * @return string
   */
  public function exportColumn($column) {
    return $this->escapeColumn($this->applyColumnExportMapping($column));
  }


  /**
   * By default does nothing for FileDaos.
   * 
   * @param string $column
   * @return string
   */
  protected function escapeColumn($column) {
    return $column;
  }

  /**
   * Escapes a table name and potentially quotes a table name.
   * By default it simply calls escapeTable
   *
   * @param string $table
   * @return string The escaped and quoted table name.
   */
  public function exportTable($table = null) {
    return $this->escapeTable($table ? $table : $this->tableName);
  }

  /**
   * Does nothing by default.
   * 
   * @param string $table
   * @return string
   */
  protected function escapeTable($table = null) {
    return $table ? $table : $this->tableName;
  }

}

?>
