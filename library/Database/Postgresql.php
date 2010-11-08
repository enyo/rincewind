<?php

/**
 * This file describes a Postgresql Database.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Database
 * */
/**
 * Loading the abstract database
 */
include dirname(__FILE__) . '/Database.php';

/**
 * Loading the PostgresqlResult
 */
include dirname(dirname(__FILE__)) . '/DatabaseResult/PostgresqlResult.php';

/**
 * The Postgresql Database implementation.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Database
 * */
class Postgresql extends Database {

  /**
   * The string used for comments.
   * @var string
   */
  protected $commentString = '-- ';
  /**
   * The character set used for connections.
   * @var string
   */
  protected $charsetName = 'UNICODE';

  /**
   * Begins a transaction
   */
  public function startTransaction() {
    $this->query('start transaction');
  }

  /**
   * Commits a transaction
   */
  public function commit() {
    $this->query('commit');
  }

  /**
   * Rolls back a transaction
   */
  public function rollback() {
    $this->query('rollback');
  }

  /**
   * This is the connect function that tries to create a postgresql connection, and set the correct character set.
   */
  protected function connect() {

    if ( ! function_exists("pg_connect")) {
      throw new DatabaseException("The function pg_connect is not available! Please install the postgresql php module.");
    }

    $connection_string = "";
    if ($this->host) {
      $connection_string .= "host='" . pg_escape_string($this->host) . "' ";
    }
    if ($this->port) {
      $connection_string .= "port='" . pg_escape_string($this->port) . "' ";
    }
    if ($this->dbname) {
      $connection_string .= "dbname='" . pg_escape_string($this->dbname) . "' ";
    }
    if ($this->user) {
      $connection_string .= "user='" . pg_escape_string($this->user) . "' ";
    }
    if ($this->password) {
      $connection_string .= "password='" . pg_escape_string($this->password) . "' ";
    }

    $this->resource = pg_connect($connection_string);

    if ( ! $this->resource) {
      throw new DatabaseException("Sorry, impossible to connect to the server with this connection string: '" . $this->getConnectionString() . "'.");
    }

    $this->connected = true;

    pg_set_client_encoding($this->resource, $this->charsetName);

    if ($this->searchPath) {
      $this->query('set search_path to "' . pg_escape_string($this->searchPath) . '"');
    }

    return true;
  }

  /**
   * Sets the charsetName for future queries.
   * If a connection already exists, pg_set_client_encoding() is called.
   *
   * @param string $charsetName If null the default is passed to postgresql.
   */
  public function setCharacterSet($charsetName = null) {
    if ($charsetName) $this->charsetName = $charsetName;
    if ($this->connected) {
      pg_set_client_encoding($this->resource, $this->charsetName);
    }
  }

  /**
   * Closes the database connection
   */
  protected function close() {
    if ($this->connected) {
      pg_close($this->resource);
      $this->connected = false;
    }
  }

  /**
   * Performs a query
   * @param string $query
   * @return PostgresqlResult
   */
  public function query($query) {
    $result = @pg_query($this->resource, $query);
    if ($result === false) {
      throw new DatabaseQueryException("There was a problem with the query.\nThe server responded: " . $this->lastError());
    }
    return new PostgresqlResult($result);
  }

  /**
   * Performs multiple queries at once. For postgresql this is just a wrapper for query.
   * 
   * @param string $query
   * @return void
   */
  public function multiQuery($query) {
    return $this->query($query);
  }

  /**
   * Returns the last sql error
   * 
   * @return string;
   */
  public function lastError() {
    if ($this->connected) {
      return pg_last_error($this->resource);
    }
    else {
      return '(No connection)';
    }
  }

  /**
   * Escapes the string for postgresql queries.
   * @param string $string
   * @return string The escaped string
   */
  public function escapeString($string) {
    return pg_escape_string($this->resource, (string) $string);
  }

  /**
   * @param string $text
   * @return string The escaped and quoted string.
   */
  public function exportString($text) {
    return "'" . $this->escapeString($text) . "'";
  }

  /**
   * @param string $column
   * @return string
   */
  public function exportColumn($column) {
    return '"' . $this->escapeColumn($column) . '"';
  }

  /**
   * @param string $resourceName
   * @return string The escaped and quoted table name.
   */
  public function exportTable($table) {
    return '"' . $this->escapeTable($table) . '"';
  }

  /**
   * Returns the id of the last inserted record for given table.
   *
   * @param string $table
   * @return int
   */
  public function getLastInsertId($table) {
    $id = $this->query('select currval(\'' . $this->escapeTable($table) . '_id_seq\') as id');
    $id = $id->fetchArray();
    return $id['id'];
  }

}
