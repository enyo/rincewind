<?php


	require_once(dirname(dirname(__FILE__)) . '/setup.php');

	require_once(LIBRARY_ROOT_PATH . 'Dao/Dao.php');


	class Dao_WithDatabase_Test extends Snap_UnitTestCase {

	    public function setUp() {
    	}

	    public function tearDown() {
	    }

	    public function testIntIdenticalInteger() {
	        return $this->assertIdentical(Dao::INT, Dao::INTEGER);
	    }

	    public function testBoolIdenticalBoolean() {
	        return $this->assertIdentical(Dao::BOOL, Dao::BOOLEAN);
	    }

	    public function testTimestampIdenticalDateWithTime() {
	        return $this->assertIdentical(Dao::TIMESTAMP, Dao::DATE_WITH_TIME);
	    }

	    public function testStringIdenticalText() {
	        return $this->assertIdentical(Dao::STRING, Dao::TEXT);
	    }
	}


?>