<?php

/**
 * This file contains the FileDaoBase definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 */
/**
 * Loading the abstract Dao Class
 * Checking for the class is actually faster then include_once
 */
if ( ! class_exists('Dao', false)) include dirname(__FILE__) . '/../Dao.php';

/**
 * Loading the FileResultIterator
 */
include dirname(__FILE__) . '/FileResultIterator.php';


/**
 * The Exception base class for FileDaoExceptions.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @subpackage Exceptions
 */
class FileDaoException extends DaoException {

}

/**
 * The FileDaoBase is used to get a file somewhere, interpret it and act as a normal datasource.
 *
 * It's called FileDaoBase so you won't have any conflicts with a resource called 'files'.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 */
class FileDaoBase extends Dao {

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
  public function getFileDataSource() {
    return $this->fileDataSource;
  }

  /**
   * Creates a Dao.
   * You probably want to overwrite this method in your daos to use your
   * implementation of instantiating Daos.
   *
   * @param string $daoName
   * @return FileDao
   */
  public function createDao($daoName) {
    $daoClassName = $daoName . 'Dao';
    return new $daoClassName($this->getFileDataSource());
  }

  /**
   * Creates an iterator for a DataSource result.
   *
   * @param DataSourceResult $result
   * @return FileResultIterator
   */
  public function createIterator($result) {
    return new FileResultIterator($result, $this);
  }

  /**
   * Most file data connections will transfer time values as timestamps.
   *
   * @param string $databaseValue
   * @param bool $withTime
   */
  protected function convertRemoteValueToTimestamp($databaseValue, $withTime) {
    return (int) $databaseValue;
  }

  /**
   * Overwrite this if your file connection allows transaction.
   */
  public function beginTransaction() {
    throw new DaoNotSupportedException("Transactions are not implemented");
  }

  /**
   * Overwrite this if your file connection allows transaction.
   */
  public function commit() {
    throw new DaoNotSupportedException("Transactions are not implemented");
  }

  /**
   * Overwrite this if your file connection allows transaction.
   */
  public function rollback() {
    throw new DaoNotSupportedException("Transactions are not implemented");
  }

  /**
   * Uses the $fileDataSource to get the data.
   * @param array $map
   * @param bool $exportValues
   * @param string $resourceName
   * @return DataSourceResult
   * @uses $fileDataSource
   */
  protected function getFromDataSource($map, $exportValues, $resourceName) {
    if (count($map) === 1 && array_key_exists('id', $map)) {
      return $this->fileDataSource->get($this->exportResourceName($resourceName ? $resourceName : ($this->viewName ? $this->viewName : $this->resourceName)), $this->exportId($map['id']));
    }
    else {
      return $this->fileDataSource->find($this->exportResourceName($resourceName ? $resourceName : ($this->viewName ? $this->viewName : $this->resourceName)), $exportValues ? $this->exportMap($map) : $map);
    }
  }

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
  public function find($map, $exportValues = true, $resourceName = null) {

    $data = $this->getFromDataSource($map, $exportValues, $resourceName)->fetch();

    if ( ! $data || ! is_array($data)) return null;

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
    $data = $this->getFromDataSource($map, $exportValues, $resourceName)->fetch();

    if ( ! $data || ! is_array($data)) throw new DaoNotFoundException("The query did not return any results.");

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
   * @return DaoResultIterator
   * @see get()
   * @uses $fileDataSource
   */
  public function getIterator($map, $sort = null, $offset = null, $limit = null, $exportValues = true, $resourceName = null) {
    $result = $this->fileDataSource->getList($this->exportResourceName($resourceName ? $resourceName : ($this->viewName ? $this->viewName : $this->resourceName)), $exportValues ? $this->exportMap($map) : $map, $this->generateSortString($sort), $offset, $limit);

    return $this->createIterator($result);
  }

  /**
   * Inserts a record in the database, and updates the record with the new data (in
   * case some default values of the database have been set.)
   *
   * @param Record $record
   * @return Record The updated record.
   * @uses $fileDataSource
   * @uses $dataSourceReturnsIdOnInsert
   */
  public function insert($record) {

    $attributes = $this->getInsertValues($record);

    $result = $this->fileDataSource->insert($this->resourceName, $attributes);

    if ($this->fileDataSource->returnsDataOnInsert()) {
      $data = $result->fetch();
    }
    else {
      $data = $this->getData(array('id' => $result));
    }

    if ( ! $data || ! is_array($data)) throw new DaoException('The data returned from the datasource after insert was invalid (resource: ' . $this->getResourceName() . ').');

    $this->updateRecordWithData($data, $record);

    $this->afterInsert($record);

    $record->setExistsInDatabase();

    return $record;
  }

  /**
   * Updates a record in the database using the id.
   *
   * @param Record $record
   * @return Record The updated record.
   * @uses $fileDataSource
   */
  public function update($record) {

    $result = $this->fileDataSource->update($this->resourceName, $record->id, $this->getUpdateValues($record));

    if ($this->fileDataSource->returnsDataOnUpdate()) {
      $data = $result->fetch();
      if ( ! $data || ! is_array($data)) throw new DaoException('The data returned from the datasource after update was invalid (resource: ' . $this->getResourceName() . ').');
      $this->updateRecordWithData($data, $record);
    }


    $this->afterUpdate($record);

    return $record;
  }

  /**
   * Deletes a record in the database using the id.
   *
   * @param Record $record
   * @uses $fileDataSource
   */
  public function delete($record) {
    $this->fileDataSource->delete($this->resourceName, $record->id);
    $this->afterDelete($record);
  }

  /**
   * Returns the total row count in the resource the Dao is assigned to.
   *
   * @return int
   * @uses $fileDataSource
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

    if ( ! is_array($map)) throw new DaoWrongValueException("The passed map is not an array.");

    $assignments = array();

    foreach ($map as $attributeName => $value) {
      if ($value instanceof DaoAttributeAssignment) {
        throw new DaoNotSupportedException("DaoAttributeAssignments don't work yet for FileDaos.");
      }

      if ( ! isset($this->attributes[$attributeName])) {
        $trace = debug_backtrace();
        trigger_error('The type for attribute ' . $attributeName . ' (' . $this->resourceName . ') is not defined in ' . $trace[2]['file'] . ' on line ' . $trace[2]['line'], E_USER_ERROR);
      }

      $type = $this->attributes[$attributeName];
      $map[$this->exportAttributeName($attributeName)] = $this->exportValue($attributeName, $value, $type, $this->notNull($attributeName));
    }

    return $map;
  }

  /**
   * This method takes the $sort attribute and returns a typical 'order by ' SQL string.
   * If $sort is false then the $this->defaultSort is used if it exists.
   * The sort is passed to interpretSortVariable to get a valid attributes list.
   *
   * @param string|array $sort
   */
  public function generateSortString($sort) {
    if ( ! $sort) {
      $sort = $this->defaultSort;
    }

    if ($sort) {
      $attributesArray = $this->interpretSortVariable($sort);
    }

    if ( ! $attributesArray) return '';

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

  /**
   * Doesn't work for JSON normally.
   */
  public function startTransaction() {
    throw new FileDaoException('Transactions not implemented.');
  }

  /**
   * Returns an array.
   *
   * If the value is already an array, it just returns it. Otherwise it puts the
   * value in an array.
   *
   * @param mixed $value
   * @return array
   */
  public function exportSequence($value) {
    return is_array($value) ? $value : array($value);
  }

  /**
   * @return null
   */
  public function exportNull() {
    return null;
  }

}

