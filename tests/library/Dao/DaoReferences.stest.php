<?php

require_once(dirname(dirname(__FILE__)) . '/setup.php');

require_once(LIBRARY_ROOT_PATH . 'Dao/Dao.php');

require_once(dirname(__FILE__) . '/NonAbstractDao.php');


class FakeDao extends NonAbstractDao {

  protected $mockDao;

  public function __construct($mockDao) {
    $this->mockDao = $mockDao;
    parent::__construct();
  }

  protected $columnTypes = array('address_id'=>Dao::INT);

  protected function setupReferences() {
    $this->addReference('address', $this->mockDao, 'address_id', 'THEFOREIGNKEY');
  }

}






class DaoReferences_Basic_Test extends Snap_UnitTestCase {

  protected $dao;
  protected $dataObject;
  protected $mockDao;

  public function setUp() {
    $this->referencedDataObject = new DataObject(array('SOMEDATAHASH'=>987), null);

    $this->mockDao = $this->mock('Dao')
      ->setReturnValue('get', $this->referencedDataObject)
      ->setReturnValue('getObjectFromData', $this->referencedDataObject)
    	->listenTo('get', array(new Snap_Equals_Expectation(array('THEFOREIGNKEY'=>123))))
      ->listenTo('getObjectFromData', array(new Snap_Equals_Expectation(array('SOMEDATAHASH'=>987))))
      ->listenTo('getObjectFromData', array(new Snap_Equals_Expectation(array('ALREADY_EXISTING_DATAHASH'=>789))))
      ->construct();

    $this->dao = new FakeDao($this->mockDao);
  }

  public function tearDown() {}

  public function testReference() {
    $do = new DataObject(array('address_id'=>1), $this->dao);
    return $this->assertIdentical($do->address, $this->referencedDataObject);
  }

  public function testForeignDaoGetsCalled() {
    $do = new DataObject(array('address_id'=>123), $this->dao);
    $do->address; // Getting the address so it gets remote fetched.
		return $this->assertCallCount($this->mockDao, 'get', 1, array(new Snap_Equals_Expectation(array('THEFOREIGNKEY'=>123))));
  }

  public function testHashGetsCached() {
    $do = new DataObject(array('address_id'=>123), $this->dao);
    $do->address; // Getting the address so it gets remote fetched.
    $do->address; // Getting the address twice
		return $this->assertCallCount($this->mockDao, 'get', 1, array(new Snap_Equals_Expectation(array('THEFOREIGNKEY'=>123))));
  }

  public function testHashGetsCachedAndReturned() {
    $do = new DataObject(array('address_id'=>123), $this->dao);
    // Getting the address so it gets remote fetched.
    // This should store the hash of the foreign DataObject in the local DataObject, to be reused next time.
    // So the new DataObject returned, should have the same data hash as the test DataObject in the constructor.
    $do->address; 
    $do->address; // Getting the address twice
		return $this->assertCallCount($this->mockDao, 'getObjectFromData', 1, array(new Snap_Equals_Expectation(array('SOMEDATAHASH'=>987))));
  }

  public function testHashGetsReturnedDirectly() {
    $do = new DataObject(array('address_id'=>123, 'address'=>array('ALREADY_EXISTING_DATAHASH'=>789)), $this->dao);
    // Getting the address so the hash gets read and put in a data object
    $do->address; 
		return $this->assertCallCount($this->mockDao, 'getObjectFromData', 1, array(new Snap_Equals_Expectation(array('ALREADY_EXISTING_DATAHASH'=>789))));
  }



  public function testDataObjectIsFetchedIfKeyIsSetButNull() {
    $do = new DataObject(array('address_id'=>123, 'address'=>null), $this->dao);
    $do->address; // Getting the address so it gets remote fetched.
		return $this->assertCallCount($this->mockDao, 'get', 1, array(new Snap_Equals_Expectation(array('THEFOREIGNKEY'=>123))), 'Even though address was set, it should have called the dao to get the data since it was null.');
  }


  public function testNullIsReturnedAndWarnsIfHashIsIncorrect() {
    $this->willWarn();
    $do = new DataObject(array('address'=>'something'), $this->dao);
		return $this->assertNull($do->address, 'Accessing a faulty hash should return null and warn.');
  }


}




class FakeWithoutKeysDao extends NonAbstractDao {

  protected $mockDao;

  public function __construct($mockDao) {
    $this->mockDao = $mockDao;
    parent::__construct();
  }

  protected function setupReferences() {
    $this->addReference('address', $this->mockDao);
  }

}



class DaoReferences_WithoutLocalAndForeignKey_Test extends Snap_UnitTestCase {

  protected $dao;
  protected $dataObject;
  protected $mockDao;

  public function setUp() {
    $this->referencedDataObject = new DataObject(array(), null);

    $this->mockDao = $this->mock('Dao')
      ->setReturnValue('getObjectFromData', $this->referencedDataObject)
      ->listenTo('getObjectFromData', array(new Snap_Equals_Expectation(array('SOMEDATAHASH'=>987))))
      ->listenTo('getObjectFromData', array(new Snap_Equals_Expectation(array('ALREADY_EXISTING_DATAHASH'=>789))))
      ->construct();

    $this->dao = new FakeWithoutKeysDao($this->mockDao);
  }

  public function tearDown() {}

  public function testDataObjectPrintsWarningWhenHashNotSet() {
    $this->willWarn();
    $do = new DataObject(array(), $this->dao);
    return $this->assertNull($do->address);
  }

  public function testDataObjectGetsInstantiatedWithHash() {
    $do = new DataObject(array('address'=>array('SOMEDATAHASH'=>987)), $this->dao);
    $do->address;
		return $this->assertCallCount($this->mockDao, 'getObjectFromData', 1, array(new Snap_Equals_Expectation(array('SOMEDATAHASH'=>987))));
  }

  public function testReturnsNullIfHashIsNull() {
    $do = new DataObject(array('address'=>null), $this->dao);
    return $this->assertNull($do->address);
  }

}


?>
