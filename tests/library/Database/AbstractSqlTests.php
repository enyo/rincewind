<?php


	abstract class AbstractSqlDatabase_EstablishConnection_Test extends Snap_UnitTestCase {

		abstract protected function getDatabaseConnection($username = null);

	    public function setUp() {}

	    public function tearDown() {}

	    public function testCreatingConnection() {
	    	$db = $this->getDatabaseConnection();
	        return $this->assertIsA($db->getResource(), 'MySQLi');
	    }

		public function testCreatingConnectionWithWrongUsername() {
			$this->willThrow('SqlConnectionException');
			$this->willWarn();
	    $db = $this->getDatabaseConnection('WRONG USER');
		}

	}




	abstract class AbstractSqlDatabase_General_Tests extends Snap_UnitTestCase {

		protected $db;

		abstract protected function getDatabaseConnection($username = null);

		protected $resultType;

		public function setUp() {
	    	$this->db = $this->getDatabaseConnection();
		}

		public function tearDown() {
			unset($this->db);
		}

		public function testWrongQuery() {
			$this->willThrow('SqlQueryException');
			$this->willWarn();
			$this->db->query("this is nonsense");	
		}

		public function testGoodQueries() {
			return $this->assertIsA($this->db->query("show tables"), $this->resultType);
		}

		public function testMultipleQueries() {
			return $this->assertNull($this->db->multiQuery("show tables; show tables;"));
		}


	}




	abstract class AbstractSqlDatabase_Result_Test extends Snap_UnitTestCase {

		protected $db;
		protected $tableName = 'test';

		abstract protected function getDatabaseConnection($username = null);

		protected $phpInternalResultType;

		public function setUp() {
	    	$this->db = $this->getDatabaseConnection();
			$this->db->query(sprintf("create temporary table `%s` (id int primary key, username varchar(100))", $this->tableName));
			$this->db->query(sprintf("insert into `%s` set id=1, username='user1'", $this->tableName));
			$this->db->query(sprintf("insert into `%s` set id=2, username='user2'", $this->tableName));
			$this->db->query(sprintf("insert into `%s` set id=3, username='user3'", $this->tableName));
		}

		public function tearDown() {
			unset($this->db);
		}

		public function testQueryReturnsResult() {
			return $this->assertIsA($this->db->query(sprintf("select * from `%s`", $this->tableName))->getResult(), $this->phpInternalResultType);
		}

		public function testQueryAfterMultiQuery() {
			$this->db->multiQuery(sprintf("insert into `%s` set id=4, username='user4'; insert into `%s` set id=5, username='user5';", $this->tableName, $this->tableName));
			$this->db->query(sprintf("insert into `%s` set id=6, username='user6'", $this->tableName));
			// This would have thrown an error if not possible.
			return $this->assertTrue(true);
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

		public function testFetchResult() {
			$result = $this->db->query(sprintf("select * from `%s` where id=2", $this->tableName));
			return $this->assertEqual($result->fetchResult('username'), 'user2');
		}

		public function testMultipleFetchResult() {
			$result = $this->db->query(sprintf("select * from `%s` order by id", $this->tableName));
			return $this->assertEqual($result->fetchResult('username'), $result->fetchResult('username'), "fetchResult() should not move the row pointer.");
		}

		public function testMultipleFetchArray() {
			$result = $this->db->query(sprintf("select * from `%s` order by id", $this->tableName));
			$row1 = $result->fetchArray();
			$row2 = $result->fetchArray();
			return $this->assertEqual($row2['username'], 'user2', "fetchArray() should advance the row pointer.");
		}


	}



	abstract class AbstractSqlDatabase_Transaction_Test extends Snap_UnitTestCase {

		protected $db;
		protected $tableName = 'test2';

		abstract protected function getDatabaseConnection($username = null);


		public function setUp() {
	    	$this->db = $this->getDatabaseConnection();
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



	abstract class AbstractSqlDatabase_EscapeFunctions_Test extends Snap_UnitTestCase {
		protected $db;

		protected $table = "TABLE-1\"2'3\x1a4\n5\r6\x00";
		protected $escapedTable = "TABLE-1\\\"2\\'3\\Z4\\n5\\r6\\0";

		protected $string = "STRING-1\"2'3\x1a4\n5\r6\x00";
		protected $escapedString = "STRING-1\\\"2\\'3\\Z4\\n5\\r6\\0";

		protected $column = "COLUMN-1\"2'3\x1a4\n5\r6\x00";
		protected $escapedColumn = "COLUMN-1\\\"2\\'3\\Z4\\n5\\r6\\0";


		abstract protected function getDatabaseConnection($username = null);

		public function setUp() {
	    	$this->db = $this->getDatabaseConnection();
		}

		public function tearDown() {
			unset($this->db);
		}


		public function testEscapeTable() {
			return $this->assertEqual($this->db->escapeTable($this->table), $this->escapedTable);
		}

		public function testEscapeColumn() {
			return $this->assertEqual($this->db->escapeColumn($this->column), $this->escapedColumn);
		}

		public function testEscapeString() {
			return $this->assertEqual($this->db->escapeString($this->string), $this->escapedString);
		}
	}





?>