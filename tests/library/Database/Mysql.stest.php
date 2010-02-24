<?php

	require_once(dirname(dirname(__FILE__)) . '/setup.php');

	require_once(LIBRARY_ROOT_PATH . 'Database/MySql.php');


	function getDatabaseConnection($username = null) {
		return new MySql(CONF_MYSQL_DBNAME, $username ? $username : CONF_MYSQL_USERNAME, CONF_MYSQL_HOST, CONF_MYSQL_PORT, CONF_MYSQL_PASSWORD);
	}


	class Mysql_EstablishConnection_Test extends Snap_UnitTestCase {

	    public function setUp() {}

	    public function tearDown() {}

	    public function testCreatingConnection() {
	    	$db = getDatabaseConnection();
	        return $this->assertIsA($db->getResource(), 'MySQLi');
	    }

		public function testCreatingConnectionWithWrongUsername() {
			$this->willThrow('SqlConnectionException');
			$this->willError();
	    	$db = getDatabaseConnection('WRONG USER');
		}

	}



	class Mysql_General_Tests extends Snap_UnitTestCase {

		protected $db;

		public function setUp() {
	    	$this->db = getDatabaseConnection();
		}

		public function tearDown() {
			unset($this->db);
		}

		public function testWrongQuery() {
			$this->willThrow('SqlQueryException');
			$this->willError();
			$this->db->query("this is nonsense");	
		}

		public function testGoodQueries() {
			return $this->assertIsA($this->db->query("show tables"), 'MySqlResult');
		}

	}




	class Mysql_Result_Test extends Snap_UnitTestCase {

		protected $db;
		protected $tableName = 'test';

		public function setUp() {
	    	$this->db = getDatabaseConnection();
			$this->db->query(sprintf("create temporary table `%s` (id int primary key, username varchar(100))", $this->tableName));
			$this->db->query(sprintf("insert into `%s` set id=1, username='user1'", $this->tableName));
			$this->db->query(sprintf("insert into `%s` set id=2, username='user2'", $this->tableName));
			$this->db->query(sprintf("insert into `%s` set id=3, username='user3'", $this->tableName));
		}

		public function tearDown() {
			unset($this->db);
		}

		public function testResultNumRows() {
			$result = $this->db->query(sprintf("select * from `%s`", $this->tableName));
			return $this->assertTrue($result->numRows() == 3);
		}

		public function testFetchArrayCount() {
			$result = $this->db->query(sprintf("select * from `%s` where id=1", $this->tableName));
			return $this->assertEqual(count($result->fetchArray()), 2);
		}

		public function testFetchArray() {
			$result = $this->db->query(sprintf("select * from `%s` where id=1", $this->tableName));
			$array = $result->fetchArray();
			return $this->assertEqual($array['username'], 'user1');
		}

	}



	class Mysql_Transaction_Test extends Snap_UnitTestCase {

		protected $db;
		protected $tableName = 'test2';

		public function setUp() {
	    	$this->db = getDatabaseConnection();
			$this->db->query(sprintf("create temporary table `%s` (`username` varchar(100)) engine=InnoDb", $this->tableName));
		}

		public function tearDown() {
			unset($this->db);
		}

		public function testRollback() {
			$this->db->beginTransaction();
			$this->db->query(sprintf("insert into `%s` set username='test1'", $this->tableName));
			$this->db->query(sprintf("insert into `%s` set username='test2'", $this->tableName));
			$this->db->query(sprintf("insert into `%s` set username='test3'", $this->tableName));
			$this->db->rollback();
			$result = $this->db->query(sprintf("select * from `%s`", $this->tableName));
			return $this->assertEqual($result->numRows(), 0);
		}

		public function testCommit() {
			$this->db->beginTransaction();
			$this->db->query(sprintf("insert into `%s` set username='".$this->tableName."'", $this->tableName));
			$this->db->query(sprintf("insert into `%s` set username='".$this->tableName."'", $this->tableName));
			$this->db->query(sprintf("insert into `%s` set username='test3'", $this->tableName));
			$this->db->commit();
			$result = $this->db->query(sprintf("select * from `%s`", $this->tableName));
			return $this->assertTrue($result->numRows() == 3);
		}

	}



	class Mysql_EscapeFunctions_Test extends Snap_UnitTestCase {
		protected $db;

		protected $table = 'te"s\'t';
		protected $escapedTable = 'te\"s\\\'t';

		public function setUp() {
	    	$this->db = getDatabaseConnection();
		}

		public function tearDown() {
			unset($this->db);
		}


		public function testEscapeTable() {
			return $this->assertEqual($this->db->escapeTable($this->table), $this->escapedTable);
		}

		public function testEscapeColumn() {
			return $this->assertEqual($this->db->escapeColumn($this->table), $this->escapedTable);
		}

		public function testEscapeString() {
			return $this->assertEqual($this->db->escapeString($this->table), $this->escapedTable);
		}
	}





?>