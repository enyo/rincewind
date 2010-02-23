<?php



require_once ('DatabaseResult/AbstractDatabaseResult.php');



class MySqlResult extends AbstractDatabaseResult {

	protected $fetchResultRow = 0;


	public function fetchArray($row = NULL, $result_type = MYSQL_ASSOC) {
		$return = $this->result->fetch_array($result_type);
		return ($return);
	}

	public function fetchResult($field, $row = NULL) {
		if ($row === NULL) { $row = $this->getFetchResultRow(); }
		$return = $this->fetchArray($row);
		return ($return[$field]);
	}

	public function fetchRow($row = NULL) {
		$return = $this->result->fetch_row();
		return ($return);
	}

	public function numRows() {
		$return = $this->result->num_rows;
		return ($return);
	}


	protected function getFetchResultRow() {
		return ($this->fetchResultRow ++);
	}

	public function reset() {
		$this->result->data_seek(0);
	}


}

?>
