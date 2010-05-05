<?php

/**
 * This file contains the abstract Database interface definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Database
 **/

/**
 * Loading the exceptions
 */
include dirname(__FILE__) . '/DatabaseExceptions.php';


/**
 * The database interface.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Database
 **/
interface DatabaseInterface {


  /**
   * Perform query.
   * @param string The query
   * @return DatabaseResult
   */
  public function query($query);

  /**
   * Perform multiple queries at once.
   * Some databases (eg: mysql) have another command for that.
   * Postgresql theoratically does not need another function call. Call multiQuery anyway!
   * For Postgresql this call simply gets redirected to query()
   * @param string $query
   * @return void
   */
  public function multiQuery($query);



  /**
   * Escapes a string so it can be used in a query.
   * IMPORTANT: You still have to put quotes around it.
   * @param string $string
   * @return string
   */
  public function escapeString($string);

  /**
   * Escapes a column so it can be used in a query.
   * IMPORTANT: You still have to put quotes around it.
   * @param string $column
   * @return string
   */
  public function escapeColumn($column);

  /**
   * Escapes a table so it can be used in a query.
   * IMPORTANT: You still have to put quotes around it.
   * @param string $table
   * @return string
   */
  public function escapeTable($table);


  /**
   * Returns the database resource
   * @return mixed
   */
  public function getResource();


  /**
   * Returns the last error.
   * @return string
   */
  public function lastError();


}

?>
