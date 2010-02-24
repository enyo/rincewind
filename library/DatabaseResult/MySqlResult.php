<?php

	require_once ('DatabaseResult/AbstractDatabaseResult.php');

	class MySqlResult extends AbstractDatabaseResult {

		private $currentRowNumber = 0;

		public function fetchArray() {
			$this->currentRowNumber ++;
			return ($this->result->fetch_assoc());
		}

		public function fetchResult($field) {
			$return = $this->fetchArray();
			$this->seek($this->currentRowNumber - 1);
			return ($return[$field]);
		}

		public function numRows() {
			return ($this->result->num_rows);
		}

		public function seek($rowNumber) {
			$this->currentRowNumber = $rowNumber;
			$this->result->data_seek($rowNumber);	
		}

	}

?>
