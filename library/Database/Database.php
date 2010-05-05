<?php

/**
 * This file contains the abstract Database class definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Database
 **/


/**
 * Loading the interface
 */
include dirname(__FILE__) . '/DatabaseInterface.php';


/**
 * If you implement a Database (eg: for mysql) extend this class.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Database
 **/
abstract class Database implements DatabaseInterface {

  /**
   * @var boolean
   */
  protected $connected  = false;

  /**
   * @var mixed The resource
   */
  protected $resource;

  /**
   * @var string
   */
  protected $host;

  /**
   * @var string
   */
  protected $port;

  /**
   * @var string
   */
  protected $dbname;

  /**
   * @var string
   */
  protected $user;

  /**
   * @var string
   */
  protected $password;

  /**
   * @var string
   */
  protected $search_path;


  /**
   * You have to submit all connection infos in the constructor.
   * The database connects as soon as instanciated.
   *
   * @param string $dbname
   * @param string $user
   * @param string $host
   * @param string $port
   * @param string $password
   */
  public function __construct($dbname, $user = null, $host = null, $port = null, $password = null) {
    $this->dbname       = $dbname;
    $this->host         = $host;
    $this->port         = (int) $port;
    $this->user         = $user;
    $this->password     = $password;
    $this->connect();
  }


  /**
   * Implement your database specific connection here.
   */
  abstract protected function connect();

  /**
   * Implement your database specific close implementation here.
   */
  abstract protected function close();

  /**
   * Checks if it's connected, and calls connect() if not.
   */
  public function ensureConnection() {
    if (!$this->connected) { $this->connect(); }
  }


  /**
   * @return mixed The resource returned by the connection (eg: pg_connect)
   */
  public function getResource() {
    return $this->resource;
  }




  /**
   * Some databases (eg: Mysql) do not support multiple queries by themselves.
   * This function takes care of this problem.
   * @param string $query
   * @return void
   */
  public function multiQuery($query) {
    $this->query($query);
  }

  /**
   * Returns a string with all the connection parameters.
   * The password is not shown.
   * @param $separator Is shown between the different values.
   */
  public function getConnectionString($separator = '/') {
    $path = array();

    if ($this->host)        { $path[] = '[host: ' . $this->host . ']'; }
    if ($this->port)        { $path[] = '[port: ' . $this->port . ']'; }
    if ($this->dbname)      { $path[] = '[dbname: ' . $this->dbname . ']'; }
    if ($this->search_path) { $path[] = '[search_path: ' . $this->search_path . ']'; }
    if ($this->user)        { $path[] = '[user: ' . $this->user . ']'; }
    if ($this->password)    { $path[] = '[pass: [not_shown]]'; }

    return implode($separator, $path);
  }

  /**
   * Calls close() to ensure the database connection gets closed when destroyed
   * @see close()
   */
  public function __destruct() {
    $this->close();
  }


  /**
   * Typically columns are just escaped as strings.
   * If your database handles that differently implement it!
   * @param string $column
   */
  public function escapeColumn($column) {
    return $this->escapeString($column);
  }

  /**
   * Typically tables are just escaped as strings.
   * If your database handles that differently implement it!
   * @param string $table
   */
  public function escapeTable($table) {
    return $this->escapeString($table);
  }



}


?>
