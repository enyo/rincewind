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
   * @param string $resourceName You can specify this as an attribute when writing a Dao implementation
   * @param array $attributes You can specify this as an attribute when writing a Dao implementation
   * @param array $nullAttributes You can specify this as an attribute when writing a Dao implementation
   * @param array $defaultValueAttributes You can specify this as an attribute when writing a Dao implementation
   */
  public function __construct($fileDataSource, $resourceName = null, $attributes = null, $nullAttributes = null, $defaultValueAttributes = null) {
    parent::__construct($resourceName, $attributes, $nullAttributes, $defaultValueAttributes);
    $this->fileDataSource = $fileDataSource;
  }


  /**
   * Returns the fileDataSource.
   *
   * @return FileDataSource
   */
  public function getFileDataSource() { return $this->fileDataSource; }


  /**
   * Creates a Dao.
   * You probably want to overwrite this method in your daos to use your implementation of instantiating Daos.
   *
   * @param string $daoClassName
   * @return FileSourceDao
   */
  public function createDao($daoClassName) {
    return new $daoClassName($this->getFileDataSource());
  }


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
   * This is the method to get a Record from the database.
   * If you want to select more records, call getIterator.
   * If you call get() without parameters, a "raw record" will be returned, containing
   * only default values, and null as id.
   *
   * @param array|Record $map A map containing the attributes.
   * @param bool $exportValues When you want to have complete control over the $map
   *                           attributes, you can set exportValues to false, so they
   *                           won't be processed.
   *                           WARNING: Be sure to escape them yourself if you do so.
   * @param string $resourceName You can specify a different resource (most probably a view)
   *                          to get data from.
   *                          If not set, $this->viewName will be used if present; if not
   *                          $this->resourceName is used.
   * @return Record
   */
  public function find($map = null, $exportValues = true, $resourceName = null) {
    $content = $this->fileDataSource->view($this->exportResourceName($resourceName ? $resourceName : ($this->viewName ? $this->viewName : $this->resourceName)), $exportValues ? $this->exportMap($map) : $map);

    $data = $this->interpretFileContent($content);

    if (!$data || !is_array($data)) return null;

    return $this->getRecordFromData($data);
  }

  /**
   * Same as get() but returns an array with the data instead of a record
   *
   * @param array|Record $map
   * @param bool $exportValues
   * @param string $resourceName
   * @see get()
   * @return array
   */
  public function getData($map, $exportValues = true, $resourceName = null) {
    $data = $this->interpretFileContent($this->fileDataSource->view($this->exportResourceName($resourceName ? $resourceName : ($this->viewName ? $this->viewName : $this->resourceName)), $exportValues ? $this->exportMap($map) : $map));
    if (!$data || !is_array($data)) throw new DaoNotFoundException("The query did not return any results.");
    return $this->prepareDataForRecord($data);
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
   * @param array|Record $map
   * @param string|array $sort
   * @param int $offset 
   * @param int $limit 
   * @param bool $exportValues
   * @param string $resourceName
   * @see get()
   * @return DaoResultIterator
   */
  public function getIterator($map, $sort = null, $offset = null, $limit = null, $exportValues = true, $resourceName = null) {
    $content = $this->fileDataSource->viewList($this->exportResourceName($resourceName ? $resourceName : ($this->viewName ? $this->viewName : $this->resourceName)), $exportValues ? $this->exportMap($map) : $map, $this->generateSortString($sort), $offset, $limit);

    $data = $this->interpretFileContent($content);

    return $this->getIteratorFromData($data);
  }




  /**
   * Inserts a record in the database, and updates the record with the new data (in
   * case some default values of the database have been set.)
   *
   * @param Record $record
   * @return Record The updated record.
   */
  public function insert($record) {

    list ($attributeNames, $values) = $this->generateInsertArrays($record);

    $attibutes = array_combine($attributeNames, $values);

    $id = $this->fileDataSource->insert($this->resourceName, $attributes);

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
    $attributes = array();

    foreach ($this->attributes as $attributeName=>$type) {
      if ($attributeName != 'id' && $type != Dao::IGNORE) $attributes[$attributeName] = $this->exportValue($record->get($attributeName), $type, $this->notNull($attributeName));
    }

    $this->fileDataSource->update($this->resourceName, $record->id, $attributes);

    $this->afterUpdate($record);

    return $record;
  }

  /**
   * Deletes a record in the database using the id.
   *
   * @param Record $record
   */
  public function delete($record) {
    $this->fileDataSource->delete($this->resourceName, $record->id);
    $this->afterDelete($record);
  }

  /**
   * Returns the total row count in the resource the Dao is assigned to.
   *
   * @return int
   */
  public function getTotalCount() {
    return $this->fileDataSource->getTotalCount($this->resourceName);
  }

  /**
   * This function exports every values of a map
   *
   * @param array|Record $map A map or Record containing the attributes.
   * @return array The map with exported values.
   */
  protected function exportMap($map) {

    $map = $this->interpretMap($map);

    $assignments = array();

    foreach($map as $attributeName=>$value) {
      if ($value instanceof DaoAttributeAssignment) {
        throw new DaoNotSupportedException("DaoAttributeAssignments don't work yet for FileDaos.");
      }

      if (!isset($this->attributes[$attributeName])) {
        $trace = debug_backtrace();
        trigger_error('The type for attribute ' . $attributeName . ' ('.$this->resourceName.') is not defined in ' . $trace[2]['file'] . ' on line ' . $trace[2]['line'], E_USER_ERROR);
      }

      $type = $this->attributes[$attributeName];
      $map[$this->exportAttributeName($attributeName)] = $this->exportValue($value, $type, $this->notNull($attributeName));
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
   * The sort is passed to interpretSortVariable to get a valid attributes list.
   *
   * @param string|array $sort
   */
  public function generateSortString($sort) {
    if (!$sort) {
      $sort = $this->defaultSort;
    }
    
    if ($sort) {
      $attributesArray = $this->interpretSortVariable($sort);
    }

    if (!$attributesArray) return '';

    return implode(' ', $attributesArray);
  }


  /**
   * By default does nothing for FileDaos.
   * 
   * @param string $attributeName
   * @return string
   */
  protected function escapeAttributeName($attributeName) {
    return $attributeName;
  }


  /**
   * Does nothing by default.
   * 
   * @param string $resourceName
   * @return string
   */
  protected function escapeResourceName($resourceName = null) {
    return $resourceName ? $resourceName : $this->resourceName;
  }

}

