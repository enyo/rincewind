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
if (!class_exists('Dao')) include dirname(__FILE__) . '/Dao.php';


/**
 * Loading the abstract Dao Class
 * Checking for the class is actually faster then include_once
 */
if (!class_exists('FileFactory')) include dirname(dirname(__FILE__)) . 'FileFactory/FileFactory.php';


/**
 * The FileDao is used to get a file somewhere, interpret it and act as a normal datasource.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/
abstract class FileDao extends Dao {

	/**
	 * @var mixed
	 */
	protected $fileFactory;

	/**
	 * @param DaoFileFactory $fileFactory The fileFactory is used to create the requests.
	 * @param string $tableName You can specify this as an attribute when writing a Dao implementation
	 * @param array $columnTypes You can specify this as an attribute when writing a Dao implementation
	 * @param array $nullColumns You can specify this as an attribute when writing a Dao implementation
	 * @param array $defaultColumns You can specify this as an attribute when writing a Dao implementation
	 */
	public function __construct($fileFactory, $tableName = null, $columnTypes = null, $nullColumns = null, $defaultColumns = null) {
		$this->fileFactory = $fileFactory;
		if ($tableName) $this->tableName = $tableName;
		if ($columnTypes) $this->columnTypes = $columnTypes;
		if ($nullColumns) $this->nullColumns = $nullColumns;
		if ($defaultColumns) $this->defaultColumns = $defaultColumns;
	}


	/**
	 * Returns the fileFactory.
	 *
	 * @return DaoFileFactory
	 */
	public function getFileFactory() { return $this->fileFactory; }

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
	protected function convertFileValueToTimestamp($databaseValue, $withTime) { return (int) $databaseValue; }


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
	 * @param array $map A map containing the column assignments.
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
	public function get($map = null, $exportValues = true, $tableName = null) {
		if (!$map) return $this->getRawObject();

		$content = $this->fileFactory->view($tableName ? $tableName : ($this->viewName ? $this->viewName : $this->tableName), $exportValues ? $this->exportMap($map) : $map);

		$data = $this->interpretFileContent($content);

		return $this->getObjectFromData($data);
	}

	/**
	 * Same as get() but returns an array with the data instead of an object
	 *
	 * @param array $map
	 * @param bool $exportValues
	 * @param string $tableName
	 * @see get()
	 * @return array
	 */
	protected function getData($map, $exportValues = true, $tableName = null) {
		return $this->interpretFileContent($this->fileFactory->view($tableName ? $tableName : ($this->viewName ? $this->viewName : $this->tableName), $exportValues ? $this->exportMap($map) : $map));
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
	 * @param array $map
	 * @param string|array $sort
	 * @param int $offset 
	 * @param int $limit 
	 * @param bool $exportValues
	 * @param string $tableName
	 * @see get()
	 * @return DaoResultIterator
	 */
	public function getIterator($map, $sort = null, $offset = null, $limit = null, $exportValues = true, $tableName = null) {
		$data = $this->interpretFileContent($this->fileFactory->list($tableName ? $tableName : ($this->viewName ? $this->viewName : $this->tableName), $exportValues ? $this->exportMap($map) : $map, $this->generateSortString($sort), $offset, $limit));
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

		$id = $this->fileFactory->insert($this->tableName, $attributes);

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
		$attibutes = array();

		foreach ($this->columnTypes as $column=>$type) {
			if ($column != 'id' && $type != Dao::IGNORE) $attibutes[$column] = $this->exportValue($object->getValue($column), $type, $this->notNull($column));
		}

		$this->fileFactory->update($this->tableName, $object->id, $attributes);

		$this->afterUpdate($object);

		return $object;
	}

	/**
	 * Deletes an object in the database using the id.
	 *
	 * @param DataObject $object
	 */
	public function delete($object) {
		$this->fileFactory->delete($this->tableName, $object->id);
		$this->afterDelete($object);
	}

	/**
	 * Returns the total row count in the table the Dao is assigned to.
	 *
	 * @return int
	 */
	public function getTotalCount() {
		return $this->fileFactory->getTotalCount($this->tableName);
	}

	/**
	 * This function exports every values of a map
	 *
	 * @param array $map A map containing the column assignments.
	 * @return array The map with exported values.
	 */
	protected function exportMap($map) {

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
			$map[$column] = $this->exportValue($value, $type, $this->notNull($column));
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



}

?>
