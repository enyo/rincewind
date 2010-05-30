<?php

require_once(dirname(dirname(__FILE__)) . '/setup.php');

require_once(LIBRARY_ROOT_PATH . 'Dao/Dao.php');



class FakeDao extends Dao {

  protected $mockDao;
  protected $dataObject;
  
  public function __construct($mockDao, $dataObject) {
    $this->mockDao = $mockDao;
    $this->dataObject = $dataObject;
    parent::__construct();
  }
  
  
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
    $this->addReference('address', $this->mockDao, 'address_id', 'THEFOREIGNKEY');
  }

}






class FakeDao_Reference_Test extends Snap_UnitTestCase {

  protected $dao;
  protected $dataObject;
  protected $mockDao;

  public function setUp() {
    $this->dataObject = new DataObject(array('SOMEDATAHASH'=>987), null);

    $this->mockDao = $this->mock('Dao')
      ->setReturnValue('get', $this->dataObject)
      ->setReturnValue('getObjectFromData', $this->dataObject)
    	->listenTo('get', array(new Snap_Equals_Expectation(array('THEFOREIGNKEY'=>123))))
      ->listenTo('getObjectFromData', array(new Snap_Equals_Expectation(array('SOMEDATAHASH'=>987))))
      ->construct();

    $this->dao = new FakeDao($this->mockDao, $this->dataObject);
  }

  public function tearDown() {}

  public function testReference() {
    $do = $this->dao->get();
    return $this->assertIdentical($do->address, $this->dataObject);
  }

  public function testForeignDaoGetsCalled() {
    $do = $this->dao->get();
    $do->address; // Getting the address so it gets remote fetched.
		return $this->assertCallCount($this->mockDao, 'get', 1, array(new Snap_Equals_Expectation(array('THEFOREIGNKEY'=>123))));
  }

  public function testHashGetsCached() {
    $do = $this->dao->get();
    $do->address; // Getting the address so it gets remote fetched.
    $do->address; // Getting the address twice
		return $this->assertCallCount($this->mockDao, 'get', 1, array(new Snap_Equals_Expectation(array('THEFOREIGNKEY'=>123))));
  }

  public function testHashGetsCachedAndReturned() {
    $do = $this->dao->get();
    // Getting the address so it gets remote fetched.
    // This should store the hash of the foreign DataObject in the local DataObject, to be reused next time.
    // So the new DataObject returned, should have the same data hash as the test DataObject in the constructor.
    $do->address; 
    $do->address; // Getting the address twice
		return $this->assertCallCount($this->mockDao, 'getObjectFromData', 1, array(new Snap_Equals_Expectation(array('SOMEDATAHASH'=>987))));
  }

}

?>
