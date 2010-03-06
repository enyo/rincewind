<?php


	/**
	 * There should be tests without the database
	 */
	class MysqlDao_Basic_Test extends Snap_UnitTestCase {

	    public function setUp() {}

	    public function tearDown() {}

	    public function testSomething() {
	        return $this->skip();
	    }

	}

?>