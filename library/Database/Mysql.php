<?php

/**
 * This file describes a Mysql Database.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Database
 **/


/**
 * Loading the abstract database
 */
include dirname(__FILE__) . '/Database.php';

/**
 * Loading the MysqlResult
 */
include dirname(dirname(__FILE__)) . '/DatabaseResult/MysqlResult.php';


/**
 * The Mysql Database implementation.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Database
 **/
class Mysql extends Database {

  /**
   * The string used for comments.
   * @var string
   */
  protected $commentString = '-- ';

  /**
   * The character set used for connections.
   * @var string
   */
  protected $charsetName   = 'utf8';




  /**
   * Escapes the string for mysql queries.
   * @param string $string
   * @return string The escaped string
   */
  public function escapeString($string) {
    return $this->resource->real_escape_string((string) $string);
  }

  /**
   * Begins a transaction
   */
  public function beginTransaction() { $this->query('start transaction'); }

  /**
   * Commits a transaction
   */
  public function commit()           { $this->query('commit'); }

  /**
   * Rolls back a transaction
   */
  public function rollback()         { $this->query('rollback'); }


  /**
   * This is the connect function that tries to create a mysql connection, and set the correct character set.
   */
  protected function connect() {
    if (!function_exists("mysqli_connect")) { throw new SqlException("The function mysqli_connect is not available! Please install the mysqli php module."); }

    $this->resource = new mysqli($this->host, $this->user, $this->password, $this->dbname, $this->port);

    if ($this->resource->connect_error) {
      throw new SqlConnectionException("Sorry, impossible to connect to the server with this connection string: '" . $this->getConnectionString()."'. " . ' (#' . $this->resource->connect_errno . ' ' . $this->resource->connect_error);
    }

    $this->connected = true;
  
    $this->setCharacterSet();
  }


  /**
   * Sets the charsetName for future queries.
   * If a connection already exists, the mysql command set names is called.
   *
   * @param string $charsetName If null the default is passed to mysql.
   */
  public function setCharacterSet($charsetName = null) {
    if ($charsetName)   $this->charsetName = $charsetName;
    if ($this->connected) {
      $this->query("SET CHARACTER SET '" . $this->escapeString($this->charsetName) . "'");
    }
  }

  /**
   * Closes the database connection
   */
  protected function close() {
    if ($this->connected) {
      @$this->resource->close();
      $this->connected = false;
    }
  }





  /**
   * Performs a query
   * @param string $query
   * @return MysqlResult
   */
  public function query($query) {
    $result = @$this->resource->query($query);
    if ($result === false) {
      throw new SqlQueryException("There was a problem with the query.\nThe server responded: " . $this->lastError());
    }
    return new MysqlResult($result);
  }

  /**
   * Performs multiple queries at once, and frees the result afterwards.
   * @param string $query
   * @return void
   */
  public function multiQuery($query) {
    $result = @$this->resource->multi_query($query);
    if ($result === false) {
      throw new SqlQueryException("There was a problem with the query.\nThe server responded: " . $this->lastError());
    }

    // This is really strange:
    // When I don't iterate over the results, it seems that there's a mysql bug that doesn't free the result sets, so a normal query()
    // after that won't work.
    while($this->resource->more_results()) {
      $this->resource->next_result();
      $this->resource->use_result();
    }
  }



  /**
   * Returns the last sql error
   * @return string;
   */
  public function lastError() {
    return @$this->resource->error;
  }
}

?>
