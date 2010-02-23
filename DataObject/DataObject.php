<?php

	require_once('Dao/Dao.php');
	
	class DataObjectException extends Exception { }
	
	class DataObject {
	
		protected $data;
		protected $dao;

	
		/**
		 * Every data object holds a reference to it's dao.
		 *
		 * @param array $data The complete data in an associative array.
		 * @param Dao $dao The dao that created this object.
		 */
		public function __construct($data, $dao) {
			$this->data = $data;
			$this->dao = $dao;
		}


		/**
		 * Returns the dao from this object.
		 *
		 * @return Dao
		 */
		public function getDao() { return $this->dao; }


		/**
		 * Returns an associative array with the values.
		 *
		 * @param bool $phpNames If true the indices will be camelized, if false, the indices will be like the database names.
		 */
		public function getArray($phpNames = true) {
			$data = array();
			if (!$phpNames) return $this->data;
			foreach ($this->data as $name=>$value) {
				$data[$this->convertDbColumnToPhpName($name)] = $value;
			}
			return $data;
		}

		public function getValue($column) {
			if (!array_key_exists($column, $this->data)) {
				$this->triggerUndefinedPropertyWarning($column);
				return null;
			}
			return $this->data[$column];
		}
	
		public function setValue($column, $value) {
			if (!array_key_exists($column, $this->data)) {
				$this->triggerUndefinedPropertyWarning($column);
				return;
			}
			$value = self::coerce($value, $this->getColumnType($column), in_array($column, $this->dao->getNullColumns()));
			$this->data[$column] = $value;
		}
	
	
		protected function convertPhpNameToDbColumn($column) { return preg_replace('/([A-Z])/e', 'strtolower("_$1");', $column); }
		protected function convertDbColumnToPhpName($column) { return preg_replace('/_([a-z])/e', 'strtoupper("$1");', $column); }
		protected function convertGetMethodToDbColumn($method) {
			$method = substr($method, 3);
			$method = strtolower(substr($method, 0, 1)) . substr($method, 1);
			return $this->convertPhpNameToDbColumn($method);
		}
		protected function convertSetMethodToDbColumn($method) { return $this->convertGetMethodToDbColumn($method); }
	
		private function triggerUndefinedPropertyWarning($column) {
			$trace = debug_backtrace();
			trigger_error("Undefined column: $column in " . $trace[1]['file'] . ' on line ' . $trace[1]['line'], E_USER_WARNING);
		}
	
		/**
		 * Overloading the DataObject
		 */
		public function __isset($column) { return isset($this->data[$this->convertPhpNameToDbColumn($column)]); }
	
		public function __set($phpColumn, $value) {
			$column = $this->convertPhpNameToDbColumn($phpColumn);
			$this->setValue($column, $value);
		}
	
		public function __get($phpColumn) {
			$column = $this->convertPhpNameToDbColumn($phpColumn);
			return $this->getValue($column);
		}
	
		function __call($method, $param) {
			if (strpos($method, 'get') === 0 || strpos($method, 'set') === 0 || strpos($method, 'has') === 0 || strpos($method, 'is') === 0) {
				$trace = debug_backtrace();
				trigger_error("Deprecated method call: $method! Do not use getters or setters, but do directly access the properties! Call was in " . $trace[1]['file'] . ' on line ' . $trace[1]['line'], E_USER_WARNING);
			}
		
			if (strpos($method, 'is') === 0 || strpos($method, 'has') === 0) {
				$column = $this->convertPhpNameToDbColumn($method);
				return $this->getValue($column);
			} elseif (strpos($method, 'get') === 0) {
				$column = $this->convertGetMethodToDbColumn($method);
				return $this->getValue($column);
			} elseif (strpos($method, 'set') === 0) {
				$column = $this->convertSetMethodToDbColumn($method);
				return $this->setValue($column, $param[0]);
			} else {
				$trace = debug_backtrace();
				trigger_error("Call to undefined method $method in " . $trace[1]['file'] . ' on line ' . $trace[1]['line'], E_USER_ERROR);
			}
		}


		/**
		 * Returns the type of the column defined in the DAO
		 *
		 * @param string $column The column name
		 * @return INT
		 */
		protected function getColumnType($column) {
			$columnTypes = $this->dao->getColumnTypes();
			if (!array_key_exists($column, $columnTypes)) {
				$trace = debug_backtrace();
				trigger_error('No valid type found for column '.$column.' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_ERROR);
			}
	
			return $columnTypes[$column];
		}
	
	
		static function coerce($value, $type, $allowNull = false, $quiet = false) {
			if ($allowNull && $value === null) { return null; }
			$trace = debug_backtrace();
			switch ($type) {
				case Dao::BOOL:
					if (!$quiet && !is_bool($value) && $value != 1 && $value != 0) { trigger_error('The value of the type "BOOL" was not valid in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_WARNING); }
					return $value ? true : false;
					break;
				case Dao::INT:
					if (!$quiet && !is_int($value) && !is_numeric($value) && (strval(intval($value)) !== strval($value))) { trigger_error('The value of the type "INT" was not valid in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_WARNING); }
					return intval($value);
					break;
				case Dao::FLOAT:
					if (!$quiet && !is_float($value) && !is_numeric($value)) { trigger_error('The value of the type "FLOAT" was not valid in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_WARNING); }
					return floatval($value);
					break;
				case Dao::DATE:
				case Dao::DATE_WITH_TIME:
					if ($value instanceof Date) { return $value; }
					elseif (is_numeric($value)) { return new Date($value); }
					else {
						if (!$quiet && !empty($value)) trigger_error('The value of the type "DATE/DATE_WITH_TIME" '.$value.' was not valid in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_WARNING);
						return new Date();
					}
					break;
				case Dao::STRING:
					return strval($value);
					break;
				default: trigger_error('Unknown type in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_ERROR);
			}
		}
	
	}


?>
