<?php


require_once ('Database/DatabaseInterface.php');


abstract class Database implements DatabaseInterface {

	public    $connected  = false;

	protected $resource;

	protected $host;
	protected $port;
	protected $dbname;
	protected $user;
	protected $password;
	protected $search_path;


	/**
	 * You have to submit all connection infos.
	 */
	public function __construct($dbname, $user = null, $host = null, $port = null, $password = null) {
		$this->dbname       = $dbname;
		$this->host         = $host;
		$this->port         = (int) $port;
		$this->user         = $user;
		$this->password     = $password;
		$this->connect();
	}


	abstract protected function connect();


	public function ensureConnection() {
		if (!$this->connected) { $this->connect(); }
	}



	public function getResource() {
		return $this->resource;
	}




	/**
	 * Some databases (eg: Mysql) do not support multiple queries by themselves.
	 * This function takes care of this problem.
	 */
	public function multiQuery($query) {
		$this->query($query);
	}


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


	public function __destruct() {
		$this->close();
	}

	abstract protected function close();



	public function escapeColumn($column) {
		return $this->escapeString($column);
	}

	public function escapeTable($table) {
		return $this->escapeString($table);
	}



}



?>
