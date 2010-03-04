<?php


	require_once ('DatabaseResult/DatabaseResultInterface.php');
	
	
	abstract class AbstractDatabaseResult implements DatabaseResultInterface {

		protected $result = false;

	
		public function __construct ($result) {
			$this->result = $result;
		}


		/**
		 * This is a shortcut for fetchArray or fetchResult.
		 *
		 * @param string $field If null, fetchArray is called, if passed fetchResult.
		 */
		public function fetch($field = null) {
			return $field ? $this->fetchResult($field) : $this->fetchArray();
		}


		public function reset() {
			$this->seek(0);
		}

	
	}



?>
