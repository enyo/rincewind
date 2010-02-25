<?php


	require_once(dirname(dirname(__FILE__)) . '/setup.php');

	require_once(LIBRARY_ROOT_PATH . 'Database/MySql.php');
	require_once(LIBRARY_ROOT_PATH . 'Dao/MySql/MySqlDao.php');



	/**
	 * The test dao with all column types to be tested.
	 */
	class MySqlTestDao extends MySqlDao {
		protected $tableName = 'dao_test';
		
		protected $columnTypes = array(
			'id'=>Dao::INTEGER,
			'integer'=>Dao::INTEGER,
			'string'=>Dao::STRING,
			'timestamp'=>Dao::DATE_WITH_TIME,
			'float'=>Dao::FLOAT,
			'null_value'=>Dao::STRING,
			'default_value'=>Dao::INT
		);

		protected $nullColumns = array('null_value');

		protected $defaultValueColumns = array('default_value');

	}


	function getMySqlConnection() {
		return new MySql(CONF_MYSQL_DBNAME, CONF_MYSQL_USERNAME, CONF_MYSQL_HOST, CONF_MYSQL_PORT, CONF_MYSQL_PASSWORD);
	}





	require_once(dirname(__FILE__) . '/SqlDaoWithDatabaseTests.php');


	class MySqlDaoWithDatabaseTest extends SqlDaoWithDatabaseTest {

		protected function getDatabaseConnection() { return getMysqlConnection(); }
		protected function getDao() { return new MySqlTestDao($this->db); }

	}

?>