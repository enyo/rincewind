<?php


	/**
	 * @author     Matthias Loitsch <develop@matthias.loitsch.com>
	 * @copyright  Copyright (c) 2009, Matthias Loitsch
	 */

	require_once('Dao/DaoResultIterator.php');
	
	
	class SqlResultIterator extends DaoResultIterator {
	
		private $result = false;
		private $dao = false;
		
		/**
		 * Contains the data of the current set.
		 *
		 * @var array
		 */
		private $currentData;

		private $totalLength = 0;

		private $length = 0;
	
	
		public function __construct($result, $dao, $totalRowCount = false) {
			$this->result = $result;
			$this->length = $result->numRows();
			if (!$totalRowCount) { $this->totalLength = $this->length; }
			else { $this->totalLength = $totalRowCount; }
			$this->dao = $dao;
			$this->next();
		}
	
		public function key() { throw new Exception('not implemented'); }
	
		public function rewind() { if ($this->length > 0) { $this->result->reset(); $this->next(); } }
	
	
		public function current() { return $this->dao->getObjectFromDatabaseData($this->currentData); }
		public function next()   { $this->currentData = $this->result->fetchArray(); }
		public function valid()  { return ($this->currentData != false); }
	
		public function count()    { return $this->length; }
		public function countAll() { return $this->totalLength; }
	
	}


?>