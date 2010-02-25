<?php

	/**
	 * @author     Matthias Loitsch <develop@matthias.loitsch.com>
	 * @copyright  Copyright (c) 2009, Matthias Loitsch
	 */


	require_once('Dao/DaoInterface.php');
	require_once('Dao/DaoColumnAssignment.php');
	require_once('Date/Date.php');
	require_once('DataObject/DataObject.php');
	
	class DaoException extends Exception { }
	class DaoWrongValueException extends DaoException { }
	class DaoNotSupportedException extends DaoException { }


	/**
	 * This is the base for all Daos.
	 */
	abstract class Dao implements DaoInterface {


		/**#@+
		 * Data types
		 *
		 * @var int
		 */
		const INT            = 1;
		const INTEGER        = 1;
		const FLOAT          = 2;
		const BOOL           = 3;
		const BOOLEAN        = 3;
		const TIMESTAMP      = 4;
		const DATE_WITH_TIME = self::TIMESTAMP;
		const DATE           = 5;
		const TEXT           = 6;
		const STRING         = 6;
		const IGNORE		 = -1;
		/**#@-*/
	
		const SORT_ASCENDING  = 0;
		const SORT_DESCENDING = 1;
		const ASC = self::SORT_ASCENDING;
		const DESC = self::SORT_DESCENDING;
	
	
		const DO_NOT_EXPORT_VALUES = false;
	


		protected $logger = null;
		protected $tableName = null;
	
		/**
		 * 
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
		 * database (for example in left joins).
		 * Those values can *not* be set in the dataobjects afterwards.
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
		 * @return array
		 */		
		public function getColumnTypes() { return $this->columnTypes; }

		/**
		 * @return array
		 */		
		public function getNullColumns()          { return $this->nullColumns; }

		/**
		 * @return array
		 */		
		public function getDefaultValueColumns() { return $this->defaultValueColumns; }
	
	
        /**
         * All getXXXIterator() calls get converted to getXXX()
         */
		public function __call($method, $param) {
			if (strpos($method, 'Iterator')) {
				return call_user_func_array(array($this, str_replace('Iterator', '', $method)), $param);
			}

			$trace = debug_backtrace();
			trigger_error(sprintf('Call to undefined function: %s::%s() from %s on line %u.', get_class($this), $method, $trace[1]['file'], $trace[1]['line']), E_USER_ERROR);
		}



		/**
		 * If you have to do stuff after an insert, ovewrite this function.
		 * It gets called by the Dao after doing an insert.
		 */
		protected function afterInsert($object) { }

		/**
		 * See afterInsert()
		 */
		protected function afterUpdate($object) { }

		/**
		 * See afterInsert()
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



		public function getById($id) { return $this->get(array('id'=>intval($id))); }

		public function getAll($sort = null, $offset = null, $limit = null) { return $this->getIterator(array(), $sort, $offset, $limit); }


		/**
		 * Gets the object by id, deletes it, and calls afterDelete()
		 */
		public function deleteById($id) {
			$object = $this->getById($id);
			$this->delete($object);
			$this->afterDelete($object);
		}


		public function getAllAsArray($sort = null, $offset = null, $limit = null) {
			$args = func_get_args();
			$i = call_user_func(array($this, 'getAll'), $args);
			$list = array();
			foreach ($i as $object) {
				$list[] = $object->getArray();
			}
			return $list;
		}

		public function escapeTable($tableName = null) { return $this->escapeColumn($tableName ? $tableName : $this->tableName); }
		abstract public function escapeColumn($column);
	
		public function exportColumn($column) {
			if (isset($this->columnExportMapping[$column]))        { $column = $this->columnExportMapping[$column]; }
			elseif (in_array($column, $this->columnImportMapping)) { $column = array_search($column, $this->columnImportMapping); }
			return $this->escapeColumn($column);
		}
		public function importColumn($column) {
			if (isset($this->columnImportMapping[$column]))        { $column = $this->columnImportMapping[$column]; }
			elseif (in_array($column, $this->columnExportMapping)) { $column = array_search($column, $this->columnExportMapping); }
			return $column;
		}
	
	
		protected function notNull($column) { return !in_array($column, $this->nullColumns); }
	
	
	
		static function isVector($var) { return count(array_diff_key($var, range(0, count($var) - 1))) === 0; }
	
	
	
	
	
	
		/**
		 * This is the method to get a DataObject from the database.
		 * If you want to select more objects, call getIterator.
		 *
		 * @var array $map A map containing the column assignments.
		 * @var mixed $sort can be an array with ASCENDING values, or a map like this: array('login'=>Dao::DESC), or simply a string containing the column. This value will be passed to generateSortString()
		 * @var int $offset 
		 * @var bool $exportValues When you want to have complete control over the $map column names, you can set exportValues to false, so they won't be processed.
		 *           WARNING: Be sure to escape them yourself if you do so.
		 * @return DataObject
		 */
		public function get($map, $sort = null, $offset = null, $exportValues = true) {
			return $this->getFromQuery($this->generateQuery($map, $sort, $offset, $limit = 1, $exportValues));
		}

		/**
		 * Same as get() but returns an array with the data instead of an object
		 */
		protected function getData($map, $sort = null, $offset = null, $exportValues = true) {
			return $this->getFromQuery($this->generateQuery($map, $sort, $offset, $limit = 1, $exportValues), $returnData = true);
		}

		public function getIterator($map, $sort = null, $offset = null, $limit = null, $exportValues = true) {
			return $this->getIteratorFromQuery($this->generateQuery($map, $sort, $offset, $limit, $exportValues));
		}

	
	
		/**
		 * Override this function if you want a specifi DataObject, not the default one
		 */
		protected function getObjectFromData($data, $columnTypes, $nullColumns) { return new DataObject($data, $this); }
	
		/**
		 * Prepares the data, and gets a new object
		 *
		 * @param array $data The data returned from the database
		 */
		public function getObjectFromDatabaseData($data) {
			return $this->getObjectFromData($this->prepareDataForObject($data), $this->columnTypes, $this->nullColumns);
		}

		/**
		 * Prepares the data, and updates the object
		 *
		 * @param array $data The data returned from the database
		 * @param DataObject $object The object to be updated
		 * @return void
		 */
		public function updateObjectWithDatabaseData($data, $object) {
			$object->setData($this->prepareDataForObject($data));
		}



		/**
		 * Goes through the data array returned from the databse, and converts the values that are necessary
		 *
		 * @param array $data
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



		public function getRawObject() {
			$data = array();
			foreach ($this->columnTypes as $column=>$type) {
				if (in_array($column, $this->nullColumns) || in_array($column, $this->defaultValueColumns)) { $data[$column] = null; }
				elseif ($type != Dao::IGNORE) $data[$column] = DataObject::coerce(null, $type, $allowNull = false, $quiet = true);
			}
			return $this->getObjectFromData($data, $this->columnTypes, $this->nullColumns);
		}
	
	
	
	
	
	
	
	
	
		/**
		 * Imports an external value (either from database, or xml, etc...) into an expected PHP variable
		 * @var mixed : the value to be imported
		 * @var INT : the type (selected from Dao)
		 * @var bool : whether the value can be null or not
		 * @return mixed
		 */
		public function importValue($externalValue, $type, $notNull = true) {
			if (!$notNull && $externalValue === NULL) { return NULL; }
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
	
		abstract protected function convertValueToTimestamp($string, $withTime);
	
	
		/** If you do not count on importing something, just overwrite the function and throw a DaoNotSupportedException inside. */
		public function importDate($value, $withTime) { return new Date($this->convertValueToTimestamp($value, $withTime)); }
		public function importInteger($value) { return intval($value); }
		public function importFloat($value)   { return floatval($value); }
		public function importString($value)  { return $value; }
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
		 * Exports a PHP value into a value understood by the DataAccessPoint
		 *
		 * @var mixed : the value to be imported
		 * @var int : the type (selected from Dao)
		 * @var bool : whether the value can be null or not
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
	
	
		/** Strings have to be properly escaped (eg: pg_escape_string) and surrounded by quotes */
		abstract public function exportString($string);
		public function exportNull() { return NULL; }
		public function exportBool($bool)     { return $bool ? 'true' : 'false'; }
		public function exportInteger($int)   { return intval($int); }
		public function exportFloat($float)   { return floatval($float); }
		public function exportDate($date, $withTime) {
			$exportString = 'Y-m-d' . ($withTime ? ' H:i:s' : '');
			return $date->format($exportString);
		}
	
	
	}

?>
