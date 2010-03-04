<?php

/**
 * THIS FILE IS DEPRECATED!!!
 * I HAVE TO UPDATE IT TO WORK
 * @package DatabaseResult
 */

/*

require_once ('DatabaseResult/AbstractDatabaseResult.php');



class PostgreSqlResult extends AbstractDatabaseResult
{


	protected $fetch_result_row = 0;


	public function fetchArray($row = NULL, $result_type = PGSQL_ASSOC)
	{
		parent::fetchArray ($row, $result_type);
		$return = @pg_fetch_array ($this->result, $row, $result_type);
		return ($return);
	}

	public function fetchResult($field, $row = NULL)
	{
		parent::fetchResult ($field, $row);
		if ($row === NULL) { $row = $this->getFetchResultRow (); }
		$return = @pg_fetch_result ($this->result, $row, $field);
		return ($return);
	}

	public function fetchRow($row = NULL)
	{
		parent::fetchRow ($row);
		$return = @pg_fetch_row ($this->result, $row);
		return ($return);
	}

	public function numRows()
	{
		parent::numRows ();
		$return = @pg_num_rows ($this->result);
		return ($return);
	}


	protected function getFetchResultRow()
	{
		return ($this->fetch_result_row ++);
	}

	public function reset()
	{
		pg_result_seek($this->result, 0);
	}


}
*/
?>