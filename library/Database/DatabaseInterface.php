<?php

/**
 * This file contains the abstract Database interface definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Database
 * */
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
 * */
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
   * Returns the database resource
   * @return mixed
   */
  public function getResource();

  /**
   * Returns the last error.
   * @return string
   */
  public function lastError();

  /**
   * Returns the id of the last inserted record.
   *
   * @return int
   */
  public function getLastInsertId();

  /**
   * Escapes a string so it can be used in a query.
   * IMPORTANT: You still have to put quotes around it.
   * Use exportString() for that.
   *
   * @param string $string
   * @return string
   * @see exportString()
   */
  public function escapeString($string);

  /**
   * Escapes the string, and puts quotes around it.
   *
   * @param string $string
   * @return string
   * @uses escapeString()
   */
  public function exportString($string);

  /**
   * Escapes a column so it can be used in a query.
   * IMPORTANT: You still have to put quotes around it.
   * Use exportColumn() for that.
   *
   * @param string $column
   * @return string
   * @see exportColumn()
   */
  public function escapeColumn($column);

  /**
   * Escapes a column and puts quotes around it.
   *
   * @param string $column
   * @return string
   * @uses escapeColumn()
   */
  public function exportColumn($column);

  /**
   * Escapes a table so it can be used in a query.
   * IMPORTANT: You still have to put quotes around it.
   * Use exportTable() for that.
   * 
   * @param string $table
   * @return string
   * @see exportTable()
   */
  public function escapeTable($table);

  /**
   * Escapes a table and puts quotes around it.
   *
   * @param string $table
   * @return string
   * @uses escapeTable()
   */
  public function exportTable($table);
}

