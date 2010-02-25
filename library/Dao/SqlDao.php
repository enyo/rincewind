<?php


	/**
	 * @author     Matthias Loitsch <develop@matthias.loitsch.com>
	 * @copyright  Copyright (c) 2009, Matthias Loitsch
	 */

	require_once('Dao/Dao.php');
	
	abstract class SqlDao extends Dao {
		protected $db;
	
		protected $results = array();
	
	
		public function __construct($db, $tableName = null, $columnTypes = null, $nullColumns = array()) {
			$this->db = $db;
			if ($tableName)   $this->tableName   = $tableName;
			if ($columnTypes) $this->columnTypes = $columnTypes;
			if ($nullColumns) $this->nullColumns = $nullColumns;
		}
	
	
	
	
		abstract protected function getLastInsertId();
		abstract protected function createIterator($result, $totalRowCount);
	
		protected function convertValueToTimestamp($string, $withTime) { return strtotime($string); }
	
	
	
		public function exportDate($date, $withTime) { return "'" . parent::exportDate($date, $withTime) . "'"; }
		public function exportNull()                 { return 'NULL'; }
	
	
	
	
		/**
		 * Transactions
		 */
		public function beginTransaction() { $this->db->beginTransaction(); }
		public function commit()           { $this->db->commit(); }
		public function rollback()         { $this->db->rollback(); }
	
	
	
		/**
		 * Inserts an object in the database.
		 *
		 * @param DataObject $object
		 * @param bool $withoutId If true, 'id' will be ignored in the query (so it will be set by auto_increment).
		 *                        This is deprecated. You should get a raw object, and the ID will be null, when you added
		 *                        the ID to defaultValueColumns.
		 * @return DataObject The updated object.
		 */
		public function insert($object, $withoutId = true) {
			$values = array();
			$columns = array();
			$id = null;
			foreach ($this->columnTypes as $column=>$type) {
				$value = $object->getValue($column);
				if ($value !== null && $type != Dao::IGNORE) {
					if ($column != 'id' || !$withoutId) {
						if ($column == 'id') $id = $object->id;
						$columns[] = $this->exportColumn($column);
						$values[]  = $this->exportValue($value, $type, $this->notNull($column));
					}
				}
			}
			$insertSql = "insert into " . $this->escapeTable($this->tableName) . " (" . implode(', ', $columns) . ") values (" . implode(', ', $values) . ");";
			
			$newId = $this->insertByQuery($insertSql, $id);

			
			$this->updateObjectWithDatabaseData($this->getData(array('id'=>$newId)), $object);
			
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
	
			$updateSql = "update " . $this->escapeTable($this->tableName) . " set " . implode(', ', $values) . " where id=" . $this->exportInteger($object->id);
	
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
			$this->db->query("delete from " . $this->escapeTable($this->tableName) . " where id=" . $this->exportValue($object->id, $this->columnTypes['id']));
			$this->afterDelete($object);
		}
	
	
	
	
		public function getTotalCount() {
			return $this->db->query("select count(id) as count from " . $this->escapeTable())->fetch('count');
		}


		protected function generateQuery($map, $sort = null, $offset = null, $limit = null, $exportValues = true) {
			if ($offset !== null && !is_int($offset) ||
				$limit !== null && !is_int($limit) ||
				!is_bool($exportValues)) {
				$trace = debug_backtrace();
				trigger_error('Wrong parameters in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_ERROR);
			}

			$assignments = array();
	
			foreach($map as $column=>$value)
			{
				if ($value instanceof DAOColumnAssignment)
				{
					$column   = $value->column;
					$operator = $value->operator;
					$value    = $value->value;
				}
				else { $operator = '='; }
	
				if (!isset($this->columnTypes[$column]))
				{
					$trace = debug_backtrace();
					trigger_error('The type for column ' . $column . ' was not found in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_ERROR);
				}
				$escapedValue = $value;
				if ($exportValues)
				{
					$type = $this->columnTypes[$column];
					$escapedValue = $this->exportValue($value, $type, $this->notNull($column));
				}
				$assignments[] = $this->exportColumn($column) . ($value === null ? ($operator == '=' ? ' is null' : ' is not null') : $operator . $escapedValue);
			}
	
			$sort = $this->generateSortString($sort);
	
			$query  = 'select * from ' . $this->escapeTable($this->tableName);
			if (count($assignments) > 0) $query .= ' where ' . implode(' and ', $assignments);
			$query .= " " . $sort;
			if ($offset !== null) { $query .= " offset " . intval($offset); }
			if ($limit  !== null) { $query .= " limit " . intval($limit); }
			return $query;
		}

	

		/**
		 * @param string $query
		 * @param bool $returnData If set to true, only the data as array is returned, not a DataObject.
		 */
		protected function getFromQuery($query, $returnData = false) {
			$result = $this->db->query($query);
			if ($result->numRows() == 0) { throw new DaoException("The query ($query) did not return any results."); }

			if ($returnData) return $result->fetchArray();

			return $this->getObjectFromDatabaseData($result->fetchArray());
		}
	
	
		protected function getIteratorFromQuery($query, $totalRowQuery = false) {
			$result = $this->db->query($query);
			if (!$totalRowQuery) { $totalRowCount = false; }
			else {
				$CountResult = $this->db->query($totalRowQuery);
				$totalRowCount = $CountResult->fetchArray();
				$totalRowCount = $totalRowCount['count'];
			}
			return $this->createIterator($result, $this, $totalRowCount);
		}
	
	
	
	
		/**
		 * Inserts and returns the new Object
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
		 * @param mixed $sort
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
		 * Tries to interpret a $sort paramater, which can ba one of following:
		 *
		 * - A string: It will be interpreted as one ascending column.
		 * - An array containing strings: It will be cycled through and every string is interpreted as ascending column
		 * - A map (associative array): It will be interpreted as columnName=>sortType. E.g: array('name'=>Dao::DESC, 'age'=>Dao::ASC)
		 *
		 * @param mixed $sort
		 * @return array An array containing all columns to sort by, escaped, and ASC or DESC appended. E.g.: array('name DESC', 'age');
		 */
		protected function interpretSortVariable($sort) {
			if (!is_array($sort)) {
				return $this->columnExists($sort) ? array($this->exportColumn($sort)) : null;
			}
	
			if (count($sort) == 0) return null;
	
			$columnArray = array();
			if (self::isVector($sort)) {
				foreach ($sort as $column) {
					if ($this->columnExists($column)) $columnArray[] = $this->exportColumn($column);
				}
			}
			else {
				foreach ($sort as $column=>$sort) {
					if ($this->columnExists($column)) $columnArray[] = $this->exportColumn($column) . ($sort == Dao::DESC ? ' desc' : '');
				}
			}
	
			return $columnArray;
		}
	
	
	}

?>
