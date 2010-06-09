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

  protected $attributes = array('address_id'=>Dao::INT);

  protected function setupReferences() {
    $this->addReference('address', $this->mockDao, 'address_id', 'THEFOREIGNKEY');
  }

}






class DaoReferences_Basic_Test extends Snap_UnitTestCase {

  protected $dao;
  protected $record;
  protected $mockDao;

  public function setUp() {
    $this->referencedRecord = new Record(array('SOMEDATAHASH'=>987), null);

    $this->mockDao = $this->mock('Dao')
      ->setReturnValue('get', $this->referencedRecord)
      ->setReturnValue('getRecordFromData', $this->referencedRecord)
    	->listenTo('get', array(new Snap_Equals_Expectation(array('THEFOREIGNKEY'=>123))))
      ->listenTo('getRecordFromData', array(new Snap_Equals_Expectation(array('SOMEDATAHASH'=>987))))
      ->listenTo('getRecordFromData', array(new Snap_Equals_Expectation(array('ALREADY_EXISTING_DATAHASH'=>789))))
      ->construct();

    $this->dao = new FakeDao($this->mockDao);
  }

  public function tearDown() {}

  public function testReference() {
    $do = new Record(array('address_id'=>1), $this->dao);
    return $this->assertIdentical($do->address, $this->referencedRecord);
  }

  public function testForeignDaoGetsCalled() {
    $do = new Record(array('address_id'=>123), $this->dao);
    $do->address; // Getting the address so it gets remote fetched.
    return $this->assertCallCount($this->mockDao, 'get', 1, array(new Snap_Equals_Expectation(array('THEFOREIGNKEY'=>123))));
  }

  public function testHashGetsCached() {
    $do = new Record(array('address_id'=>123), $this->dao);
    $do->address; // Getting the address so it gets remote fetched.
    $do->address; // Getting the address twice
		return $this->assertCallCount($this->mockDao, 'get', 1, array(new Snap_Equals_Expectation(array('THEFOREIGNKEY'=>123))));
  }

  public function testHashGetsCachedAndReturned() {
    $do = new Record(array('address_id'=>123), $this->dao);
    // Getting the address so it gets remote fetched.
    // This should store the hash of the foreign Record in the local Record, to be reused next time.
    // So the new Record returned, should have the same data hash as the test Record in the constructor.
    $do->address; 
    $do->address; // Getting the address twice
		return $this->assertCallCount($this->mockDao, 'getRecordFromData', 1, array(new Snap_Equals_Expectation(array('SOMEDATAHASH'=>987))));
  }

  public function testHashGetsReturnedDirectly() {
    $do = new Record(array('address_id'=>123, 'address'=>array('ALREADY_EXISTING_DATAHASH'=>789)), $this->dao);
    // Getting the address so the hash gets read and put in a data object
    $do->address; 
		return $this->assertCallCount($this->mockDao, 'getRecordFromData', 1, array(new Snap_Equals_Expectation(array('ALREADY_EXISTING_DATAHASH'=>789))));
  }



  public function testRecordIsFetchedIfKeyIsSetButNull() {
    $do = new Record(array('address_id'=>123, 'address'=>null), $this->dao);
    $do->address; // Getting the address so it gets remote fetched.
		return $this->assertCallCount($this->mockDao, 'get', 1, array(new Snap_Equals_Expectation(array('THEFOREIGNKEY'=>123))), 'Even though address was set, it should have called the dao to get the data since it was null.');
  }


  public function testNullIsReturnedAndWarnsIfHashIsIncorrect() {
    $this->willWarn();
    $do = new Record(array('address'=>'something'), $this->dao);
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
  protected $record;
  protected $mockDao;

  public function setUp() {
    $this->referencedRecord = new Record(array(), null);

    $this->mockDao = $this->mock('Dao')
      ->setReturnValue('getRecordFromData', $this->referencedRecord)
      ->listenTo('getRecordFromData', array(new Snap_Equals_Expectation(array('SOMEDATAHASH'=>987))))
      ->listenTo('getRecordFromData', array(new Snap_Equals_Expectation(array('ALREADY_EXISTING_DATAHASH'=>789))))
      ->construct();

    $this->dao = new FakeWithoutKeysDao($this->mockDao);
  }

  public function tearDown() {}

  public function testRecordPrintsWarningWhenHashNotSet() {
    $this->willWarn();
    $do = new Record(array(), $this->dao);
    return $this->assertNull($do->address);
  }

  public function testRecordGetsInstantiatedWithHash() {
    $do = new Record(array('address'=>array('SOMEDATAHASH'=>987)), $this->dao);
    $do->address;
		return $this->assertCallCount($this->mockDao, 'getRecordFromData', 1, array(new Snap_Equals_Expectation(array('SOMEDATAHASH'=>987))));
  }

  public function testReturnsNullIfHashIsNull() {
    $do = new Record(array('address'=>null), $this->dao);
    return $this->assertNull($do->address);
  }

}


?>
