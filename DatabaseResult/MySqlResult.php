<?php



require_once ('DatabaseResult/AbstractDatabaseResult.php');



class MySqlResult extends AbstractDatabaseResult {

	protected $fetch_result_row = 0;


	public function fetchArray($row = NULL, $result_type = MYSQL_ASSOC) {
		$return = mysql_fetch_array($this->result, $result_type);
		return ($return);
	}

	public function fetchResult($field, $row = NULL) {
		if ($row === NULL) { $row = $this->getFetchResultRow(); }
		$return = mysql_result($this->result, $row, $field);
		return ($return);
	}

	public function fetchRow($row = NULL) {
		$return = mysql_fetch_row($this->result);
		return ($return);
	}

	public function numRows() {
		$return = @mysql_num_rows ($this->result);
		return ($return);
	}


	protected function getFetchResultRow() {
		return ($this->fetch_result_row ++);
	}

	public function reset() {
		mysql_data_seek($this->result, 0);
	}


}

?>
