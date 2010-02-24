<?php

	/**
	 * @author     Matthias Loitsch <develop@matthias.loitsch.com>
	 * @copyright  Copyright (c) 2009, Matthias Loitsch
	 */

	require_once('Dao/Dao.php');
	require_once('Dao/RemoteXml/RemoteXmlResultIterator.php');
	
	abstract class RemoteXmlDao extends Dao {
	
		protected $databaseFileRetriever;
		protected $results = array();


		public function beginTransaction() { throw new DaoException('Transactions are not supported by the RemoteXmlDao.'); }
		public function commit()           { throw new DaoException('Transactions are not supported by the RemoteXmlDao.'); }
		public function rollback()         { throw new DaoException('Transactions are not supported by the RemoteXmlDao.'); }
	
		public function escapeColumn($column)        { return $column; }
		public function exportNull()                 { return null; }
		public function exportBool($bool)            { return $bool ? 'true' : 'false'; }
		public function exportString($string)        { return $string; }

		protected function convertValueToTimestamp($string, $withTime) { return $string; }
	
	
		/**
		 * @param DatabaseFileRetriever $databaseFileRetriever
		 * @param string $tableName
		 * @param array $columnTypes
		 * @param array $nullColumns
		 */
		public function __construct($databaseFileRetriever, $tableName, $columnTypes, $nullColumns = array()) {
			$this->databaseFileRetriever = $databaseFileRetriever;
			$this->tableName   = $tableName;
			$this->columnTypes = $columnTypes;
			$this->nullColumns = $nullColumns;
		}
	
	
		/**
		 * Parsing an XML is kind of ugly.
		 */
		private function parseXml($xml) {
			$values = array();
			$tags = array();
			$parser = xml_parser_create();
			xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
			xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
			xml_parser_set_option($parser, XML_OPTION_SKIP_TAGSTART, 0);
			xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
			if (!xml_parse_into_struct($parser, $xml, $values, $tags)) {
				throw new DaoException('Could not parse XML for table ' .$this->tableName. '.');
			}
			xml_parser_free($parser);
	
			$rowCount = 0;
			$itemList = array();
	
			$error = false;
			$errorMessages = array();
	
			foreach ($tags as $key=>$value) {
				if ($key == $this->tableName . 'Entry') {
					$count = 0;
					$items = $value;
					// There's always the start and the end... therefore: every 2 items
					for ($i=0; $i < count($items); $i+=2)
					{
						if (!isset($items[$i]) || !isset($items[$i + 1])) { file_put_contents('/tmp/TTT', $xml); throw new DaoException('The XML file seems to be incorrect for table \'' .$this->tableName. '\'.'); }
						$offset = $items[$i] + 1;
						$len = $items[$i + 1] - $offset;
						$fields = array_slice($values, $offset, $len);
	
						$itemList[$count] = array();
						foreach ($fields as $thisField)
						{
							$itemList[$count][$thisField["tag"]] = @$thisField["value"];
						}
						$count ++;
					}
				}
				elseif ($key == $this->tableName . 'RowCount')
				{
					$rowCount = intval(@$values[$value[0]]['value']);
				}
				elseif ($key == 'errors')
				{
					$count = 0;
					$items = $value;
					// There's always the start and the end... therefore: every 2 items
					for ($i=0; $i < count($items); $i+=2)
					{
						if (!isset($items[$i]) || !isset($items[$i + 1])) { file_put_contents('/tmp/TTT', $xml); throw new DaoException('The XML file seems to be incorrect for table \'' . $this->tableName . '\'. (The errors section is not formatted correctly)'); }
						$offset = $items[$i] + 1;
						$len = $items[$i + 1] - $offset;
						$fields = array_slice($values, $offset, $len);
	
						foreach ($fields as $thisField) {
							$error = true;
							$errorMessages[] = @$thisField["value"];
							$this->log(@$thisField["value"]);
						}
						$count ++;
					}
				}
				elseif ($key == 'debug')
				{
					$count = 0;
					$items = $value;
					// There's always the start and the end... therefore: every 2 items
					for ($i=0; $i < count($items); $i+=2) {
						if (!isset($items[$i]) || !isset($items[$i + 1])) { throw new DaoException('The XML file seems to be incorrect for table \'' . $this->tableName . '\'. (The debug section is not formatted correctly)'); }
						$offset = $items[$i] + 1;
						$len = $items[$i + 1] - $offset;
						$fields = array_slice($values, $offset, $len);
	
						foreach ($fields as $thisField) {
							$this->debug(@$thisField["value"]);
						}
						$count ++;
					}
				}
			}
	
			if ($error) {
				throw new DaoException("Error while retrieving data for table '" . $this->tableName . "'.\n The server responded: '" . implode("\n", $errorMessages) . "'");
			}
	
			if (!$rowCount) { $rowCount = count($itemList); }
			return array($itemList, $rowCount);
	    }


		protected function getExportableArrayFromObject($object, $withoutId = true) {
			$values = array();
			foreach ($this->columnTypes as $column=>$type) {
				if (/*$object->getValue($column) !== null && */$type != Dao::IGNORE) {
					if ($column != 'id' || !$withoutId) {
						$values[$this->exportColumn($column)] = $this->exportValue($object->getValue($column), $type, $this->notNull($column));
					}
				}
			}
			return $values;
		}
	
		public function insert($object) {
			$values = $this->getExportableArrayFromObject($object);
	
			$xml = $this->databaseFileRetriever->insert($this->tableName, $values);
			list($results, $rowCount) = $this->parseXml($xml);
	
			if (count($results) == 0) { throw new DaoException('The inserted object did not come back from ' . $this->tableName . '!'); }
	
			return $this->getObjectFromDatabaseData($results[0]);
		}
	
		public function update($object) {
			$values = $this->getExportableArrayFromObject($object, $withoudId = false);
	
			$xml = $this->databaseFileRetriever->update($this->tableName, $values);
			list($results, $rowCount) = $this->parseXml($xml);
	
			if (count($results) == 0) { throw new DaoException('The updated object did not come back from ' . $this->tableName . '!'); }
	
			return $this->getObjectFromDatabaseData($results[0]);
		}
	
	
		public function getIterator($map, $sort = null, $offset = null, $limit = null, $exportValues = true) {
			parent::getIterator($map, $sort, $offset, $limit, $exportValues);
			$values = array();
			foreach($map as $column=>$value)
			{
				if ($value instanceof DaoColumnAssignment) { trigger_error('This is not supported yet!'); }
	
				if (!isset($this->columnTypes[$column])) {
					$trace = debug_backtrace();
					trigger_error('The type for column ' . $column . ' was not found in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_ERROR);
				}
	
				if ($exportValues) $escapedValue = $this->exportValue($value, $this->columnTypes[$column], $this->notNull($column));
				else               $escapedValue = $value;
	
				$values[$this->exportColumn($column)] = $escapedValue;
			}
			$xml = $this->databaseFileRetriever->select($this->tableName, $values, $this->standardizeSort($sort), $offset, $limit);
			list($results, $rowCount) = $this->parseXml($xml);
	
			return new RemoteXmlResultIterator($results, $this, $rowCount);
		}
	
	
		/**
		 * This method takes the $sort attribute and returns the array standardized in this manner:
		 * array('column'=>DaoInterface::ASC, 'column2'=>DaoInterface::DESC)
		 */
		public function standardizeSort($sort) {
			if (is_array($sort)) {
				if (count($sort) == 0) return array();
	
				$columnArray = array();
				if (self::isVector($sort)) {
					foreach ($sort as $column) {
						$columnArray[$this->escapeColumn($column)] = Dao::ASC;
					}
				}
				else {
					foreach ($sort as $column=>$direction) {
						$columnArray[$this->escapeColumn($column)] = ($direction == Dao::DESC) ? Dao::DESC : Dao::ASC;
					}
				}
				return $columnArray;
			}
			elseif (!$sort) return array();
			else return array($this->exportColumn($sort)=>Dao::ASC);
		}
	
	
	
		public function delete($object) {
			$this->databaseFileRetriever->delete($this->tableName, array('id'=>$this->exportValue($object->id, $this->columnTypes['id'])));
		}
	
	
		public function deleteByColumns($columns) {
			$values = array();
			foreach ($columns as $column=>$value) {
				$values[$this->exportColumn($column)] = $this->exportValue($value, $this->columnTypes[$column], $this->notNull($column));
			}
			$this->databaseFileRetriever->delete($this->tableName, $values);
		}
	
	}
	
?>
