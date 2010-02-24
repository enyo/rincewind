<?php



interface DatabaseResultInterface
{

	/**
	 * The default fetch_array command
	 * @param: row      -> (Optional) The row (record) number. 0 = first row. NULL = all rows
	 * @param: constant -> (Optional) Parameter that controls how the return value is initialized. 
	 */
	public function fetchArray($row = null, $result_type = null);


	/**
	 * Returns a result
	 * @param string $field
	 * @param int $row
	 */
	public function fetchResult($field, $row = null);


	/**
	 * Fetchs a row
	 * @param: see fetch array
	 */
	public function fetchRow($row = null);


	/**
	 * Returns the number of rows
	 */
	public function numRows();



	/**
	 * Resets the pointer to 0
	 */
	public function reset();

}



?>
