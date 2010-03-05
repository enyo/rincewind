<?php

	abstract class AbstractSqlDaoWithDatabaseTest extends Snap_UnitTestCase {

		abstract protected function getDatabaseConnection();
		abstract protected function getDao();

		protected $db;
		protected $dao;
		protected $iteratorClassName;
		protected $tableName = 'dao_test';

		protected $defaultValue = 7;

	    public function setUp() {
	    	$this->db = $this->getDatabaseConnection();
	    	$this->dao = $this->getDao();
	    	$this->db->query(sprintf("create temporary table `%s` (`id` int primary key, `integer` int not null, `string` varchar(100) not null, `timestamp` timestamp not null, `float` float not null, `null_value` varchar(100), `default_value` int default %d not null)", $this->tableName, $this->defaultValue));
    	}

	    public function tearDown() {
	    	unset($this->db);
	    	unset($this->dao);
	    }

	    public function testNullValuesArentInserted() {
	    	$object = $this->dao->getRawObject();
	    	$object->save();
	    	$data = $this->dao->getAll()->current();
	        return $this->assertNull($data->nullValue);
	    }

	    public function testDefaultValuesAreNullInRawObjects() {
	    	$object = $this->dao->getRawObject();
	        return $this->assertNull($object->defaultValue);
	    }

	    public function testDefaultValuesAreSetByTheDatabase() {
	    	$object = $this->dao->getRawObject();
	    	$object->save();
	        return $this->assertEqual($object->defaultValue, $this->defaultValue);
	    }

	    public function testObjectReturnedFromInsertIsIdentical() {
	    	$object = $this->dao->getRawObject();
	    	$object2 = $this->dao->insert($object);
	        return $this->assertIdentical($object, $object2, "The dao should return the same object after insert, not create a new one.");
	    }

	    public function testObjectReturnedFromUpdateIsIdentical() {
	    	$object = $this->dao->getRawObject();
	    	$object = $this->dao->insert($object);
	    	$object->string = 'TEST';
			$object2 = $this->dao->update($object);
	        return $this->assertIdentical($object, $object2, "The dao should return the same object after update, not create a new one.");
	    }

		public function testGetAllReturnsIterator() {
			return $this->assertIsA($this->dao->getAll(), $this->iteratorClassName);
		}

	}

?>