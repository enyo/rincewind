<?php

	require_once(dirname(dirname(__FILE__)) . '/setup.php');

	require_once(LIBRARY_ROOT_PATH . 'Database/MySql.php');

	function getMySqlConnection($username = null) {
		return new MySql(CONF_MYSQL_DBNAME, $username ? $username : CONF_MYSQL_USERNAME, CONF_MYSQL_HOST, CONF_MYSQL_PORT, CONF_MYSQL_PASSWORD);
	}


	require_once(dirname(__FILE__) . '/SqlTests.php');



	class MySql_EstablishConnection_Test extends SqlDatabase_EstablishConnection_Test {
		protected function getDatabaseConnection($username = null) { return getMySqlConnection($username); }
	}

	class MySql_General_Tests extends SqlDatabase_General_Tests {
		protected $resultType = 'MySqlResult';
		protected function getDatabaseConnection($username = null) { return getMySqlConnection($username); }
	}

	class MySql_Result_Test extends SqlDatabase_Result_Test {
		protected $resultType = 'MySqlResult';
		protected $phpInternalResultType = 'mysqli_result';
		protected function getDatabaseConnection($username = null) { return getMySqlConnection($username); }
	}

	class MySql_Transaction_Test extends SqlDatabase_Transaction_Test {
		protected function getDatabaseConnection($username = null) { return getMySqlConnection($username); }
	}

	class MySql_EscapeFunctions_Test extends SqlDatabase_EscapeFunctions_Test {
		protected function getDatabaseConnection($username = null) { return getMySqlConnection($username); }
	}



?>