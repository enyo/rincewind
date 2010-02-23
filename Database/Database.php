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
		if (!$this->connected) { $this->connect (); }
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
		if (!empty($query)) {
			$error  = '<h1 align="center" class="db_error">An error occured during this query:</h1><br>'."\n";	
			$error .= $query;
		}
		else
		{
			$error = '<h1 align="center" class="db_error">An error occured!</h1>'."\n";	
		}
		$error .= '<br><br>'."\n";
		$error .= '<h2 align="center" class="db_error">The server responded:</h2><br>'."\n";
		$error .= $this->lastError ()."\n";
		$error .= '<br><br>'."\n";
		$error .= '<h2 align="center" class="db_error">No values have been saved or selected.</h2>'."\n";
		echo $error;
		throw new SqlException ('Database Error');
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

}



?>
