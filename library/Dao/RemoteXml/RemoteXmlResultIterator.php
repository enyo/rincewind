<?php


	/**
	 * @author     Matthias Loitsch <develop@matthias.loitsch.com>
	 * @copyright  Copyright (c) 2009, Matthias Loitsch
	 */

	require_once('Dao/DaoResultIterator.php');
	
	class RemoteXmlResultIterator extends DaoResultIterator {
	
		private $results = null;
		private $dao = null;
	
		protected $length = 0;
		protected $totalLength = 0;
	
		private $position = 0;
	
		public function __construct($results, $dao, $totalLength = false)
		{
			$this->results = $results;
			$this->length      = count($results);
			$this->totalLength = $this->length;
			if ($totalLength) { $this->totalLength = $totalLength; }
	
			$this->dao = $dao;
		}


		public function key() { return $this->position; }

		public function rewind() { $this->position = 0; }
		public function next() { $this->position ++; }
		public function valid() { return $this->position < $this->length; }
	
	
		public function current() {
			return $this->dao->getObjectFromDatabaseData($this->results[$this->position]);
		}
	
		public function count() { return $this->length; }
		public function countAll()    { return $this->totalLength; }
	
	}


?>