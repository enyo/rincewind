<?php


require_once ('Database/DatabaseInterface.php');


abstract class Database implements DatabaseInterface {

	public    $connected  = false;

	protected $resource   = null;

	protected $host         = null;
	protected $port         = null;
	protected $dbname       = null;
	protected $user         = null;
	protected $password     = null;
	protected $search_path  = null;


	public function ensureConnection() {
		if (!$this->connected) { $this->connect(); }
	}




	public function query($query, $print_only = false) {
		if ($print_only) { echo '<br />', $query, '<br />'; }
		else             { $this->ensureConnection(); }
	}

	/**
	 * Some databases (eg: Mysql) do not support multiple queries by themselves.
	 * This function takes care of this problem.
	 */
	public function multiQuery($query, $printOnly = false) {
		$this->query($query, $printOnly);
	}



	public function error($query = false) {
		if (!empty($query)) { echo "A DATABASE ERROR OCCURED WITH THIS QUERY:\n$error\n"; }
		else { echo "A DATABASE ERROR OCCURED.\n"; }
		echo "\nThe server responded:\n" . $this->lastError() . "\n";
		throw new SqlException('Database Error');
	}

	public function getResource() {
		$this->ensureConnection();
		return $this->resource;
	}
	
	public function getSchema() {
		return $this->search_path;
	}
	
	public function getDatabaseName() {
		return $this->dbname;
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




	public function escapeColumn($column) {
		return $this->escapeString($column);
	}

	public function escapeTable($table) {
		return $this->escapeString($table);
	}



}



?>
