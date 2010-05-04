<?php

require_once(dirname(dirname(__FILE__)) . '/setup.php');

require_once(LIBRARY_ROOT_PATH . 'Storage/GlobalStorage.php');


class GlobalStorage_Basic_Test extends Snap_UnitTestCase {


	public function setUp() {
	}

	public function tearDown() {
	}


  public function testInclude() {
    return $this->assertTrue(GlobalStorage::includeOnce(dirname(__FILE__) . '/testInclude.php'), 'The file should have been included');
  }

  public function testIncludeTwice() {
    GlobalStorage::includeOnce(dirname(__FILE__) . '/testInclude.php');
    return $this->assertFalse(GlobalStorage::includeOnce(dirname(__FILE__) . '/testInclude.php'), 'The file should not have been included the second time');
  }

}





?>