<?php

	require_once(dirname(dirname(__FILE__)) . '/setup.php');

	require_once(LIBRARY_ROOT_PATH . 'Database/MySql.php');

	function getMysqlConnection($username = null) {
		return new MySql(CONF_MYSQL_DBNAME, $username ? $username : CONF_MYSQL_USERNAME, CONF_MYSQL_HOST, CONF_MYSQL_PORT, CONF_MYSQL_PASSWORD);
	}


	require_once(dirname(__FILE__) . '/SqlTests.php');



	class Mysql_EstablishConnection_Test extends SqlDatabase_EstablishConnection_Test {
		protected function getDatabaseConnection($username = null) { return getMysqlConnection($username); }
	}

	class Mysql_General_Tests extends SqlDatabase_General_Tests {
		protected function getDatabaseConnection($username = null) { return getMysqlConnection($username); }
	}

	class Mysql_Result_Test extends SqlDatabase_Result_Test {
		protected function getDatabaseConnection($username = null) { return getMysqlConnection($username); }
	}

	class Mysql_Transaction_Test extends SqlDatabase_Transaction_Test {
		protected function getDatabaseConnection($username = null) { return getMysqlConnection($username); }
	}

	class Mysql_EscapeFunctions_Test extends SqlDatabase_EscapeFunctions_Test {
		protected function getDatabaseConnection($username = null) { return getMysqlConnection($username); }
	}



?>