<?php


	require_once(dirname(dirname(__FILE__)) . '/setup.php');

	require_once(LIBRARY_ROOT_PATH . 'Dao/Dao.php');


	class SqlResultIterator_Test extends Snap_UnitTestCase {

	    public function setUp() {
    	}

	    public function tearDown() {
	    }

	    public function testCount() {
			return $this->todo();
	    }

	    public function testNext() {
			return $this->todo();
	    }

	}


?>