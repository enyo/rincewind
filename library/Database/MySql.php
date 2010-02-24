<?php



require_once ('Database/Database.php');
require_once ('DatabaseResult/MySqlResult.php');



class MySql extends Database {

	protected $commentString = '-- ';

	protected $charsetName   = 'utf8';
	





	/**
	 * @param string $string
	 */
	public function escapeString($string) {
		return $this->resource->real_escape_string($string);
	}


	public function beginTransaction() { $this->query('start transaction'); }
	public function commit()           { $this->query('commit'); }
	public function rollback()         { $this->query('rollback'); }


	protected function connect() {
		if (!function_exists("mysqli_connect")) { throw new SqlException("The function mysqli_connect is not available! Please install the mysqli php module."); }

		$this->resource = new mysqli($this->host, $this->user, $this->password, $this->dbname, $this->port);

		if ($this->resource->connect_error) {
			throw new SqlConnectionException("Sorry, impossible to connect to the server with this connection string: '" . $this->getConnectionString()."'. " . ' (#' . $this->resource->connect_errno . ' ' . $this->resource->connect_error);
		}

		$this->connected = true;
		
		$this->setCharacterSet();
		
		return true;
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
	
	protected function close() {
		if ($this->connected) {
			@$this->resource->close();
			$this->connected = false;
		}
	}
	




	// Default Queries
	public function query($query) {
		$result = @$this->resource->query($query);
		if ($result === false) {
			throw new SqlQueryException('Database Error with this query: ' . $query . "\nThe server responded: " . $this->lastError());
		}
		return new MySqlResult($result);
	}

	public function multiQuery($query) {
		$result = @$this->resource->multi_query($query);
		if ($result === false) {
			throw new SqlQueryException('Database Error with this query: ' . $query . "\nThe server responded: " . $this->lastError());
		}
		return new MySqlResult($result);
	}




	public function lastError() {
		$return = @$this->resource->error;
		return ($return);
	}
}

?>
