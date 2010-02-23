<?php



require_once ('Database/Database.php');
require_once ('DatabaseResult/MySqlResult.php');



class MySql extends Database {

	protected $commentString = '-- ';

	protected $charsetName   = 'utf8';
	
	
	/**
	 * You have to submit all connection infos.
	 */
	public function __construct($dbname, $user = null, $host = null, $port = null, $password = null) {

		$this->dbname       = $dbname;
		$this->host         = $host;
		$this->port         = $port;
		$this->user         = $user;
		$this->password     = $password;

	}

	public function beginTransaction() { $this->query('start transaction'); }
	public function commit() { $this->query('commit'); }
	public function rollback() { $this->query('rollback'); }


	public function connect() {
		if (!function_exists("mysql_connect")) { throw new SqlException("The function mysql_connect is not available! Please install the mysql php module."); }
		$this->resource = mysql_connect($this->host . ($this->port ? ':' . $this->port : ''), $this->user, $this->password);

		if (!$this->resource) {
			throw new SqlException("Sorry, impossible to connect to the server with this connection string: '" . $this->getConnectionString()."'.");
		}

		if ($this->dbname) { $this->selectDatabase($this->dbname); }

		$this->connected = true;
		
		$this->setCharacterSet();
		
		return true;
	}

	public function selectDatabase($dbname) {
		$this->dbname = $dbname;
		mysql_select_db($this->dbname, $this->resource);
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
			$this->query("SET CHARACTER SET '" . mysql_real_escape_string($this->charsetName, $this->resource) . "'");
		}
	}
	
	public function close() {
		if ($this->connected) {
			@mysql_close($this->resource);
			$this->connected = false;
		}
	}
	




	// Default Queries
	public function query($query, $print_only = false) {
		parent::query($query, $print_only);
		if (!$print_only)
		{
			$result = @mysql_query($query, $this->resource);
			if ($result === false) $this->error ($query);

			return new MySqlResult ($result);
		}
	}

	public function multiQuery($query, $printOnly = false) {
		$delimiter = ';';
		if (strpos($query, 'delimiter') === false)
		{
			$this->processMultiQueries($query, $delimiter, $printOnly);
		}
		else
		{
			preg_match('/(.*?)(?:(?:\n|\r|\n\r)\s*delimiter|\z)/ism', $query, $matches);
			$this->processMultiQueries($matches[1], $delimiter, $printOnly);
			preg_match_all('/(?:\A|\n|\r|\n\r)\s*delimiter\s+([^\s]*)(.*?)(?=(?:\n|\r|\n\r)\s*delimiter|\z)/ism', $query, $matches, PREG_SET_ORDER);

			foreach ($matches as $thisMatch)
			{
				$delimiter = $thisMatch[1];
				$this->processMultiQueries($thisMatch[2], $delimiter, $printOnly);
			}
		}
	}
	public function processMultiQueries($query, $delimiter, $printOnly)
	{
		// Remove the comments: (they could contain delimiters)
		$query = preg_replace('/[\v\A]\s*'.preg_quote($this->commentString).'(.*)/i', "\n", $query);
		if (!empty($query))
		{
			// Split the queries by delimiter.
			// TODO: do this the correct way.
			$queries = explode($delimiter . "\n", $query . "\n");
			
// 			$queryRegexp = '/((?:[^"\'`]*?|(["\'`]).*?(?:[^\\\\](?:\\\\\\\\)*)\2)*?)'.preg_quote($delimiter).'/ism';
			
			
// 			$realQuery = '';
			foreach($queries as $thisQuery)
			{
				$thisQuery = trim($thisQuery);
				if (!empty($thisQuery))
				{
					$this->query($thisQuery, $printOnly);
				}
			}


// 			$queryRegexp = '/((?:[^"\'`]*?|(["\'`])(?:.*?(?:[^\\\\](?:\\\\\\\\)*)|)\2)*?)'.preg_quote($delimiter).'/ism';
// 			$queryRegexp = '/((?:[^"\'`]*?|(["\'`])\2|(["\'`]).*?(?:[^\\\\](?:\\\\\\\\)*)\3)*?)'.preg_quote($delimiter).'/ism';
/*			preg_match_all($queryRegexp, $query, $matches, PREG_SET_ORDER);
			foreach ($matches as $thisQuery)
			{
				$thisQuery = trim($thisQuery[1]);
				if (!empty($thisQuery))
				{
					$this->query($thisQuery, $printOnly);
				}
			}*/
		}
	}


	public function lastError() {
		$return = @mysql_error($this->resource);
		return ($return);
	}
}

?>
