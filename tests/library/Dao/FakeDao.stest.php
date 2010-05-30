<?php

require_once(dirname(dirname(__FILE__)) . '/setup.php');

require_once(LIBRARY_ROOT_PATH . 'Dao/Dao.php');


class FakeDao extends Dao {
  
  
  protected $columnTypes = array('address_id'=>Dao::INT);
  
  protected function generateSortString($sort)                         { return null; }
  protected function convertRemoteValueToTimestamp($string, $withTime) { return null; }
  public function exportColumn($column)                                { return null; }
  public function exportTable($table = null)                           { return null; }

  public function get($map = null, $exportValues = true, $tableName = null) {
    return new DataObject(array('address_id'=>123), $this);
  }
  public function getIterator($map, $sort = null, $offset = null, $limit = null, $exportValues = true, $tableName = null) { }
  public function insert($object) { }
  public function update($object) { }
  public function delete($object) { }
  public function beginTransaction() { }
  public function commit() { }
  public function rollback() { }
  public function getTotalCount() { }


  protected function setupReferences() {
    $this->addReference('address', 'AddressDao', 'address_id', 'THEFOREIGNKEY');
  }

  protected function getReferenceDao($daoReference) {
    $dao = new Snap_MockObject('Dao');
		$dao->setReturnValue('get', 'DAO:'.$daoReference->getDaoClassName())
  		->listenTo('get', array(new Snap_Equals_Expectation(array('THEFOREIGNKEY'=>123))));
    return $dao->construct();
  }
}






class FakeDao_Reference_Test extends Snap_UnitTestCase {

  protected $dao;

  public function setUp() {
    $this->dao = new FakeDao();
  }

  public function tearDown() {}

  public function testReference() {
    $do = $this->dao->get();
    return $this->assertIdentical($do->address, 'DAO:AddressDao');
  }

}

?>
