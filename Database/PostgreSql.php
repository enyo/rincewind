<?php



require_once ('Database/Database.php');
require_once ('DatabaseResult/PostgreSqlResult.php');



class PostgreSql extends Database {

	public function __construct($dbname, $search_path, $user = false, $host = false, $port = false, $password = false) {

		$this->dbname       = $dbname;
		$this->search_path  = $search_path;
		$this->host         = $host;
		$this->port         = $port;
		$this->user         = $user;
		$this->password     = $password;

	}

	public function beginTransaction() { $this->query('begin transaction'); }
	public function commit() { $this->query('commit'); }
	public function rollback() { $this->query('rollback'); }


	public function connect() {
		if (!function_exists("pg_connect")) { throw new SqlException("The function pg_connect is not available! Please install the postgresql php module."); }

		$connection_string = "";
		if ($this->host)     { $connection_string .= "host='".pg_escape_string($this->host)."' "; }
		if ($this->port)     { $connection_string .= "port='".pg_escape_string($this->port)."' "; }
		if ($this->dbname)   { $connection_string .= "dbname='".pg_escape_string($this->dbname)."' "; }
		if ($this->user)     { $connection_string .= "user='".pg_escape_string($this->user)."' "; }
		if ($this->password) { $connection_string .= "password='".pg_escape_string($this->password)."' "; }

		$this->resource = @pg_connect($connection_string);

		if (!$this->resource) {
			throw new SqlException("Sorry, impossible to connect to the server with this connection string: '".$this->getConnectionString()."'.");
		}

		$this->connected = true;

		return true;
	}

	public function close()
	{
		if ($this->connected)
		{
			@pg_close($this->resource);
			$this->connected = false;
		}
	}
	




	// Default Queries
	public function query($query, $print_only = false)
	{
		parent::query($query, $print_only);
		if (!$print_only)
		{
			if ($this->search_path)
			{
				pg_query ($this->resource, "set search_path to \"" . $this->search_path . "\"");
			}
			$result = @pg_query($this->resource, $query);
			if ($result === false) $this->error ($query);

			return new PostgreSqlResult ($result);
		}
	}





	// Error Handling:

	public function lastError()
	{
		$return = @pg_last_error($this->resource);
		return ($return);
	}
}

?>
