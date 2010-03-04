<?php

/**
 * This file contains the DatabaseResultInterface definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package DatabaseResult
 **/

/**
 * All Database Results have to implement this interface.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package DatabaseResult
 **/
interface DatabaseResultInterface {

	/**
	 * Returns the result stored from the actual php command.
	 * @return result
	 **/
	public function getResult();

	/**
	 * Sets the internal pointer to specific row
	 * @param int $rowNumber
	 */
	public function seek($rowNumber);


	/**
	 * If $field is given, fetchResult is used.
	 * @param string $field
	 */
	public function fetch($field = null);


	/**
	 * Returns the current row as an associative array.
	 */
	public function fetchArray();


	/**
	 * Returns the value of one field in the current row.
	 *
	 * @param string $field
	 */
	public function fetchResult($field);


	/**
	 * Returns the number of rows
	 */
	public function numRows();


	/**
	 * Resets the pointer to 0
	 */
	public function reset();

	/**
	 * If possible / necessary free the result set.
	 */
	public function free();


}


?>
