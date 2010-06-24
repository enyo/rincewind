<?php

require_once(dirname(dirname(__FILE__)) . '/setup.php');

require_once(LIBRARY_ROOT_PATH . 'Dao/DaoReference.php');


class AnyDaoReference extends DaoReference {
  public function getData($record, $attribute) { }
}


class DaoReference_Getters_Test extends Snap_UnitTestCase {

  protected $r;

  public function setUp() {
    $this->r = new AnyDaoReference('DAO', 'LOCALKEY', 'FOREIGNKEY');
  }

  public function tearDown() {}

  public function testDao() {
    return $this->assertIdentical($this->r->getDaoClassName(), 'DAO');
  }

  public function testLocalKey() {
    return $this->assertIdentical($this->r->getLocalKey(), 'LOCALKEY');
  }

  public function testForeignKey() {
    return $this->assertIdentical($this->r->getForeignKey(), 'FOREIGNKEY');
  }

}

?>
