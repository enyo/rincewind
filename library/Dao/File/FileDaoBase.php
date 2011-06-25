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
   * @param HttpDataSource $fileDataSource The fileDataSource is used to create the requests.
   */
  public function __construct($fileDataSource) {
    parent::__construct();
    $this->fileDataSource = $fileDataSource;
  }

  /**
   * Returns the fileDataSource.
   *
   * @return HttpDataSource
   */
  public function getHttpDataSource() {
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
    return new $daoClassName($this->getHttpDataSource());
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
   * The connection to the datasource
   *
   * @param array|Record $map
   * @param bool $exportValues
   * @param string $resourceName
   * @see get()
   * @return array
   */
  public function doFindData($map, $exportValues = true, $resourceName = null) {
    $data = $this->getFromDataSource($map, $exportValues, $resourceName)->fetch();
    return $data && is_array($data) ? $data : null;
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
   * @param array $additionalInfo
   * @return DataSourceResult
   * @see get()
   * @uses $fileDataSource
   */
  public function doGetIteratorResult($map, $sort, $offset, $limit, $exportValues, $resourceName, $retrieveTotalRowCount, $additionalInfo) {
    return $this->fileDataSource->getList($this->exportResourceName($resourceName ? $resourceName : ($this->viewName ? $this->viewName : $this->resourceName)), $exportValues ? $this->exportMap($map) : $map, $this->generateSortString($sort), $offset, $limit, $retrieveTotalRowCount);
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

