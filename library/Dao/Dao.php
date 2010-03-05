<?php

/**
 * This file contains the abstract Dao definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/



/**
 * Loading the interface
 */
include dirname(__FILE__) . '/DaoInterface.php';

/**
 * Loading the DaoColumnAssignment Class
 */
include dirname(__FILE__) . '/DaoColumnAssignment.php';

/**
 * Loading the DataObject Class
 */
include dirname(dirname(__FILE__)) . '/DataObject/DataObject.php';

/**
 * Loading the Exceptions
 */
include_once dirname(__FILE__) . '/DaoExceptions.php';


/**
 * Loading the Date Class
 */
include_once dirname(dirname(__FILE__)) . '/Date/Date.php';




/**
 * This abstract base class for all Daos.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/
abstract class Dao implements DaoInterface {


	/**#@+
	 * Data types
	 *
	 * @var int
	 */
	const INT            = 1;
	const INTEGER        = self::INT;
	const FLOAT          = 2;
	const BOOL           = 3;
	const BOOLEAN        = self::BOOL;
	const TIMESTAMP      = 4;
	const DATE_WITH_TIME = self::TIMESTAMP;
	const DATE           = 5;
	const TEXT           = 6;
	const STRING         = self::TEXT;
	const IGNORE		 = -1;
	/**#@-*/


	/**#@+
	 * Sort types
	 *
	 * @var int
	 */
	const SORT_ASCENDING  = 0;
	const SORT_DESCENDING = 1;
	const ASC = self::SORT_ASCENDING;
	const DESC = self::SORT_DESCENDING;
	/**#@-*/


	/**
	 * @var bool
	 */
	const DO_NOT_EXPORT_VALUES = false;


	/**
	 * @see Logger
	 * @var Logger
	 */
	protected $logger = null;

	/**
	 * This is the table name this Dao works with.
	 * @var string
	 */
	protected $tableName = null;

	/**
	 * If no sort attribute is passed to a query method, this will be used.
	 *
	 * @see generateSortString()
	 * @var string|array
	 */
	protected $defaultSort = 'sort';


	/**
	 * This array/map must contain all column types. eg: array('id'=>Dao::INT)
	 *
	 * @var array
	 */
	protected $columnTypes = array();


	/**
	 * This works exactly the same as the column types, except that it only defines columns, that may additionally be returned by the
	 * database (for example in joins).
	 * Those values can *not* be set in the DataObjects afterwards, but are checked for their types when retrieved.
	 *
	 * @var array
	 */
	protected $additionalColumnTypes = array();


	/**
	 * If your database holds different column names than you want in you DataObjects, you can specify export & import mappings.
	 * (Don't confuse this with your php values. Meaning: if the database value is 'strangely_named', but you want to access your object
	 * like this: $object->perfectName, then you have to map it from strangely_named to perfect_name (not perfectName).)
	 * If no mapping is found in the imports, than a reverse lookup is done in exports, and vice versa, so for a normal conversion
	 * only the columnImportMapping (or columnExportMapping if you prefer) has to be set.
	 * E.g.: array('what_the_database_value_actually_is'=>'what_you_would_want_it_to_be');
	 *
	 * @var array
	 */
	protected $columnImportMapping = array();

	/**
	 * The same as columnImportMapping but the other way around.
	 *
	 * @var array
	 */
	protected $columnExportMapping = array();

	/**
	 * This is an array containing all columns that can be null. eg: $nullColumns = array('email', 'name');
	 *
	 * @var array
	 */
	protected $nullColumns = array();


	/**
	 * This is a list of columns that have default values in the database.
	 * This means that, if the values are NULL, and the entry is inserted in the database, they will not be
	 * passed, so that the database can automatically set the values.
	 * So when you call getRawObject() those values will be null.
	 * Per default the id is given.
	 *
	 * @var array
	 */
	protected $defaultValueColumns = array('id');



	/**
	 * Given the $sort parameter, it generates a sort String used in the query.
	 * If $sort is not provied, $defaultSort is used.
	 *
	 * @see $defaultSort
	 * @param string|array $sort
	 */
	abstract protected function generateSortString($sort);



	/**
	 * @param LoggerFactory $loggerFactory
	 */
	public function setLoggerFactory($loggerFactory) { $this->setLogger($loggerFactory->getLogger($this->tableName . 'Dao')); }

	/**
	 * @param Logger $logger
	 */
	public function setLogger($logger) { $this->logger = $logger; }

	/**
	 * @param string $message
	 */
	protected function log($message)   { if ($this->logger) $this->logger->log($message); }

	/**
	 * @param string $message
	 */
	protected function debug($message) { if ($this->logger) $this->logger->debug($message); }



	/**
	 * Returns the column types array
	 *
	 * @return array
	 */		
	public function getColumnTypes() { return $this->columnTypes; }

	/**
	 * Returns the null columns
	 * @return array
	 */		
	public function getNullColumns() { return $this->nullColumns; }

	/**
	 * Returns the default value columns
	 *
	 * @return array
	 */		
	public function getDefaultValueColumns() { return $this->defaultValueColumns; }


	/**
	 * Temporarly all getXXXIterator() calls get converted to getXXX().
	 * This is deprecated and only a transition! If you get warnings: Correct the code.
	 * The next release will not support getXXXIterator()
	 */
	public function __call($method, $param) {
		$trace = debug_backtrace();
		if (strpos($method, 'Iterator')) {
			$newMethod = str_replace('Iterator', '', $method);
			trigger_error(sprintf('Called %s() instead of $s() from %s on line %u.', $method, $newMethod, $trace[1]['file'], $trace[1]['line']), E_USER_WARNING);
			return call_user_func_array(array($this, $newMethod), $param);
		}
		trigger_error(sprintf('Call to undefined function: %s::%s() from %s on line %u.', get_class($this), $method, $trace[1]['file'], $trace[1]['line']), E_USER_ERROR);
	}



	/**
	 * If you have to do stuff after an insert, overwrite this function.
	 * It gets called by the Dao after doing an insert.
	 *
	 * @param DataObject $object
	 * @see DataObject
	 */
	protected function afterInsert($object) { }

	/**
	 * If you have to do stuff after an update, overwrite this function.
	 * It gets called by the Dao after doing an update.
	 *
	 * @param DataObject $object
	 * @see DataObject
	 */
	protected function afterUpdate($object) { }

	/**
	 * If you have to do stuff after a deletion, overwrite this function.
	 * It gets called by the Dao after deleting.
	 *
	 * @param DataObject $object
	 * @see DataObject
	 */
	protected function afterDelete($object) { }


	
	/**
	 * Checks if a column name exists.
	 *
	 * @param string $column
	 */
	protected function columnExists($column) {
		return isset($this->columnTypes[$column]);
	}


	/**
	 * This is a wrapper for get() and the id as parameter.
	 * @param int $id
	 * @return DataObject
	 * @see DataObject
	 */
	public function getById($id) {
		return $this->get(array('id'=>intval($id)));
	}

	/**
	 * Gets the object by id and deletes it.
	 * 
	 * @param int $id
	 */
	public function deleteById($id) {
		$object = $this->getById($id);
		$this->delete($object);
	}


	/**
	 * Returns all rows (but you can specify offset &amp; limit)
	 *
	 * @param string|array $sort
	 * @param int $offset
	 * @param int $limit
	 * @return DaoResultIterator
	 * @see DaoResultIterator
	 */
	public function getAll($sort = null, $offset = null, $limit = null) {
		return $this->getIterator(array(), $sort, $offset, $limit);
	}


	/**
	 * This returns an iterator on which asArrays() has been called.
	 * Calling this, is the same as:
	 * <code>
	 * <?php $dao->getAll()->asArrays(); ?>
	 * </code>
	 *
	 * @param string|array $sort
	 * @param int $offset
	 * @param int $limit
	 * @return DaoResultIterator
	 * @see getAll()
	 */
	public function getAllAsArray() {
		$args = func_get_args();
		return call_user_func(array($this, 'getAll'), $args)->asArrays();
	}


	/**
	 * Takes a column and sees if there is an import/export mapping for it.
	 * It then returns the correct column name, unescaped.
	 *
	 * @param string $column
	 * @return string
	 * @see $columnImportMapping
	 * @see $columnExportMapping
	 */
	protected function applyColumnImportMapping($column) {
		if (isset($this->columnImportMapping[$column]))        { return $this->columnImportMapping[$column]; }
		elseif (in_array($column, $this->columnExportMapping)) { return array_search($column, $this->columnExportMapping); }
		return $column;
	}

	/**
	 * Takes a column and sees if there is an import/export mapping for it.
	 * It then returns the correct column name, unescaped.
	 *
	 * @param string $column
	 * @return string
	 * @see $columnImportMapping
	 * @see $columnExportMapping
	 */
	protected function applyColumnExportMapping($column) {
		if (isset($this->columnExportMapping[$column]))        { return $this->columnExportMapping[$column]; }
		elseif (in_array($column, $this->columnImportMapping)) { return array_search($column, $this->columnImportMapping); }
		return $column;
	}

	/**
	 * Converts the database column name to a valid php name.
	 * This is done with applyColumnImportMapping()
	 *
	 * @param string $column
	 * @return string
	 * @see applyColumnImportMapping
	 */
	public function importColumn($column) {
		return $this->applyColumnImportMapping($column);
	}




	/**
	 * Returns true if the column can not be null.
	 *
	 * @return bool
	 */
	protected function notNull($column) {
		return !in_array($column, $this->nullColumns);
	}


	/**
	 * This is a helper class that defines if an array is a "vector" (has only integer indices starting from 0) or an associative array.
	 *
	 * @param array $var
	 * @return bool
	 */
	static function isVector($var) {
		return count(array_diff_key($var, range(0, count($var) - 1))) === 0;
	}






	/**
	 * This is the method to get a DataObject from the database.
	 * If you want to select more objects, call getIterator.
	 * If you call get() without parameters, a "raw object" will be returned, containing only default values, and null as id.
	 *
	 * @param array $map A map containing the column assignments.
	 * @param string|array $sort can be an array with ASCENDING values, or a map like this: array('login'=>Dao::DESC), or simply a string containing the column. This value will be passed to generateSortString()
	 * @param int $offset 
	 * @param bool $exportValues When you want to have complete control over the $map column names, you can set exportValues to false, so they won't be processed.
	 *           WARNING: Be sure to escape them yourself if you do so.
	 * @see generateQuery()
	 * @return DataObject
	 */
	public function get($map = null, $sort = null, $offset = null, $exportValues = true) {
		if (!$map) return $this->getRawObject();
		return $this->getFromQuery($this->generateQuery($map, $sort, $offset, $limit = 1, $exportValues));
	}

	/**
	 * Same as get() but returns an array with the data instead of an object
	 *
	 * @param array $map
	 * @param string|array $sort
	 * @param int $offset 
	 * @param bool $exportValues
	 * @see get()
	 * @see generateQuery()
	 * @return array
	 */
	protected function getData($map, $sort = null, $offset = null, $exportValues = true) {
		return $this->getFromQuery($this->generateQuery($map, $sort, $offset, $limit = 1, $exportValues), $returnData = true);
	}

	/**
	 * The same as get, but returns an iterator to go through all the rows.
	 *
	 * @param array $map
	 * @param string|array $sort
	 * @param int $offset 
	 * @param int $limit 
	 * @param bool $exportValues
	 * @see get()
	 * @see generateQuery()
	 * @return DaoResultIterator
	 */
	public function getIterator($map, $sort = null, $offset = null, $limit = null, $exportValues = true) {
		return $this->getIteratorFromQuery($this->generateQuery($map, $sort, $offset, $limit, $exportValues));
	}



	/**
	 * Returns a data object with the data in it.
	 * Override this function if you want a specifi DataObject, not the default one.
	 *
	 * @param array $data
	 * @return DataObject
	 */
	protected function getObjectFromPreparedData($data) {
		return new DataObject($data, $this);
	}

	/**
	 * Prepares the data, and gets a new object.
	 * This is the way you should get a DataObject from database data with.
	 *
	 * @param array $data The data returned from the database
	 * @see prepareDataForObject()
	 * @see getObjectFromPreparedData()
	 * @return DataObject
	 */
	public function getObjectFromDatabaseData($data) {
		return $this->getObjectFromPreparedData($this->prepareDataForObject($data));
	}

	/**
	 * Prepares the data, and updates the object
	 *
	 * @param array $data The data returned from the database
	 * @param DataObject $object The object to be updated
	 * @see prepareDataForObject()
	 * @return void
	 */
	public function updateObjectWithDatabaseData($data, $object) {
		$object->setData($this->prepareDataForObject($data));
	}



	/**
	 * Goes through the data array returned from the database, and converts the values that are necessary.
	 * Meaning: if some values are null, check if they are allowed to be null.
	 * This function also checks if every column in the columnTypes array has been transmitted
	 *
	 * @param array $data
	 * @return array
	 */
	protected function prepareDataForObject($data) {
		$neededValues = $this->columnTypes;
		foreach ($data as $column=>$value)
		{
			$column = $this->importColumn($column);
			if (array_key_exists($column, $this->columnTypes)) {
				unset($neededValues[$column]);
				if ($this->columnTypes[$column] != Dao::IGNORE) {
					$data[$column] = $this->importValue($value, $this->columnTypes[$column], $this->notNull($column));
				}
			}
			elseif (array_key_exists($column, $this->additionalColumnTypes)) {
				if ($this->additionalColumnTypes[$column] != Dao::IGNORE) {
					$data[$column] = $this->importValue($value, $this->additionalColumnTypes[$column], $this->notNull($column));
				}
			}
			else {
				$trace = debug_backtrace();
				trigger_error('The type for column ' . $column . ' ('.$this->tableName.') is not defined in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_WARNING);
				unset($data[$column]);
			}
		}
		foreach ($neededValues as $column=>$type) {
			if ($type != Dao::IGNORE) {
				if ($this->notNull($column)) {
					$trace = debug_backtrace();
					trigger_error('The column ' . $column . ' ('.$this->tableName.') was not transmitted from data source in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_WARNING);
					$data[$column] = DataObject::coerce(null, $type, false, $quiet = true);
				} else {
					$data[$column] = null;
				}
			}
		}
		return $data;			
	}


	/**
	 * Returns an object with all columns defined, but only set if necessary.
	 * nullColumns will be null as well as defaultValueColumns. All other columns will have a default value set with coerce().
	 * Be careful! This function will soon be protected, and should not be called anymore! Use get() (without map) instead (which will call
	 * getRawObject() for you).
	 * You can not depend on this function... it is subject to change.
	 *
	 * @see $nullColumns
	 * @see defaultValueColumns
	 * @see $columnTypes
	 * @see coerce()
	 * @see get()
	 * @return DataObject
	 */
	public function getRawObject() {
		$data = array();
		foreach ($this->columnTypes as $column=>$type) {
			if (in_array($column, $this->nullColumns) || in_array($column, $this->defaultValueColumns)) { $data[$column] = null; }
			elseif ($type != Dao::IGNORE) $data[$column] = DataObject::coerce(null, $type, $allowNull = false, $quiet = true);
		}
		return $this->getObjectFromPreparedData($data);
	}









	/**
	 * Imports an external value (either from database, or xml, etc...) into an expected PHP variable.
	 * If the column can be null, null will be returned.
	 *
	 * @param mixed $externalValue The value to be imported
	 * @param int $type The type (selected from Dao)
	 * @param bool $notNull Whether the value can be null or not
	 * @return mixed
	 */
	public function importValue($externalValue, $type, $notNull = true) {
		if (!$notNull && $externalValue === null) { return null; }
		$dateWithTime = false;
		try {
			switch ($type) {
				case Dao::BOOL:  return $this->importBool($externalValue); break;
				case Dao::INT:   return $this->importInteger($externalValue); break;
				case Dao::FLOAT: return $this->importFloat($externalValue); break;
				case Dao::TEXT:  return $this->importString($externalValue); break;
				case Dao::DATE_WITH_TIME: $dateWithTime = true; // No break
				case Dao::DATE: return $this->importDate($externalValue, $dateWithTime); break;
				default: throw new DaoException('Unknown type when importing a value.'); break;
			}
		}
		catch (Exception $e) {
			throw new Exception('There was an error processing the table "' . $this->tableName . '": ' . $e->getMessage());
		}
	}

	/**
	 * Converts a database value to a timestamp.
	 * Obviously every Database Dao has to implement that itself.
	 * If you do not count on implementing this just overwrite the function and throw a DaoNotSupportedException inside.
	 *
	 * @param string $string
	 * @param bool $withTime
	 * @return mixed
	 */
	abstract protected function convertDatabaseValueToTimestamp($string, $withTime);

	
	/**
	 * Calls convertDatabaseValueToTimestamp and returns a Date Object.
	 * If you do not count on implementing this just overwrite the function and throw a DaoNotSupportedException inside.
	 *
	 * @param string $value
	 * @param bool $withTime
	 */
	public function importDate($value, $withTime) {
		return new Date($this->convertDatabaseValueToTimestamp($value, $withTime));
	}

	/**
	 * Simple PHP conversion.
	 * If you do not count on implementing this just overwrite the function and throw a DaoNotSupportedException inside.
	 *
	 * @param mixed $value
	 * @return integer
	 */
	public function importInteger($value) {
		return (int) $value;
	}

	/**
	 * Simple PHP conversion.
	 * If you do not count on implementing this just overwrite the function and throw a DaoNotSupportedException inside.
	 *
	 * @param mixed $value
	 * @return float
	 */
	public function importFloat($value) {
		return (float) $value;
	}

	/**
	 * Simple PHP conversion.
	 * If you do not count on implementing this just overwrite the function and throw a DaoNotSupportedException inside.
	 *
	 * @param mixed $value
	 * @return string
	 */
	public function importString($value) {
		return (string) $value;
	}

	/**
	 * This function tries to convert strings integers and stuff into a bool.
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public function importBool($value) {
		if (!$value) return false;
		if (is_int($value)) return true;
		$value = strtolower($value);
		switch ($value) {
			case 'false': case 'f': case '0': return false; break;
			case 'true': case 't': case '1': return true; break;
			default: throw new DaoException("The boolean ($value) could not be converted."); break;
		}
	}




	/**
	 * Exports a PHP value into a value understood by the Database
	 *
	 * @param mixed $internalValue The value to be imported
	 * @param int $type The type (selected from Dao)
	 * @param bool $notNull Whether the value can be null or not
	 *
	 * @return mixed
	 */
	public function exportValue($internalValue, $type, $notNull = true) {
		if (!$notNull && $internalValue === NULL) {
			return $this->exportNull();
		}
		$dateWithTime = false;
		switch ($type) {
			case Dao::BOOL:   return $this->exportBool($internalValue); break;
			case Dao::INT:    return $this->exportInteger($internalValue); break;
			case Dao::FLOAT:  return $this->exportFloat($internalValue); break;
			case Dao::TEXT:   return $this->exportString($internalValue); break;
			case Dao::DATE_WITH_TIME: $dateWithTime = true; // No break
			case Dao::DATE:   return $this->exportDate($internalValue, $dateWithTime); break;
			case Dao::IGNORE: return $internalValue; break;
			default: throw new DaoException('Unhandled type when exporting a value.'); break;
		}
	}


	/**
	 * Has to determine the correct column (with import/export mappings), escape and quote it.
	 * (eg.: user'name becomes `user\'name`)
	 *
	 * @param string $column
	 * @return string
	 */
	abstract public function exportColumn($column);

	/**
	 * Escapes and quotes the string.
	 * (eg.: value becomes 'value')
	 *
	 * @param string $string
	 * @return string
	 */
	abstract public function exportString($string);

	/**
	 * Escapes and quotes a table name.
	 * If none provied $this->tableName will be used.
	 *
	 * @param string $string
	 * @return string
	 * @see $tableName
	 */
	abstract public function exportTable($table = null);


	/**
	 * @return mixed
	 */
	abstract public function exportNull();

	/**
	 * @return mixed
	 */
	abstract public function exportBool($bool);

	/**
	 * @return mixed
	 */
	abstract public function exportInteger($int);

	/**
	 * @return mixed
	 */
	abstract public function exportFloat($float);

	/**
	 * @return mixed
	 */
	abstract public function exportDate($date, $withTime);


}

?>
