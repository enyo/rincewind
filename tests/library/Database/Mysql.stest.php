<?php

	require_once(dirname(dirname(__FILE__)) . '/setup.php');

	require_once(LIBRARY_ROOT_PATH . 'Database/Mysql.php');

	function getMysqlConnection($username = null) {
		return new Mysql(CONF_MYSQL_DBNAME, $username ? $username : CONF_MYSQL_USERNAME, CONF_MYSQL_HOST, CONF_MYSQL_PORT, CONF_MYSQL_PASSWORD);
	}


	require_once(dirname(__FILE__) . '/AbstractSqlTests.php');



	class Mysql_EstablishConnection_Test extends AbstractSqlDatabase_EstablishConnection_Test {
		protected function getDatabaseConnection($username = null) { return getMysqlConnection($username); }
	}

	class Mysql_General_Tests extends AbstractSqlDatabase_General_Tests {
		protected $resultType = 'MysqlResult';
		protected function getDatabaseConnection($username = null) { return getMysqlConnection($username); }
	}

	class Mysql_Result_Test extends AbstractSqlDatabase_Result_Test {
		protected $resultType = 'MysqlResult';
		protected $phpInternalResultType = 'mysqli_result';
		protected function getDatabaseConnection($username = null) { return getMysqlConnection($username); }
	}

	class Mysql_Transaction_Test extends AbstractSqlDatabase_Transaction_Test {
		protected function getDatabaseConnection($username = null) { return getMysqlConnection($username); }
	}

	class Mysql_EscapeFunctions_Test extends AbstractSqlDatabase_EscapeFunctions_Test {
		protected function getDatabaseConnection($username = null) { return getMysqlConnection($username); }
	}



?>