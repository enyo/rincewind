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

  protected $attributes = array('addressIds'=>Dao::SEQUENCE);

  protected function setupReferences() {
    $this->addReference('addresses', new DaoToManyReference($this->mockDao, 'addressIds', 'THEFOREIGNKEY'));
  }

}



class DaoToManyReferences_Basic_Test extends Snap_UnitTestCase {

  protected $dao;
  protected $record;
  protected $mockDao;

  public function setUp() {
    $this->referencedRecord = new Record(array('SOMEDATAHASH'=>987), null);

    $this->mockDao = $this->mock('Dao')
      ->construct();

    $this->dao = new FakeDao($this->mockDao);
  }

  public function tearDown() {}

  public function testReference() {
    $do = new Record(array('addressIds'=>array(1, 3, 4)), $this->dao);
    return $this->assertIsA($do->addresses, 'DaoKeyListIterator');
  }

  public function testIteratorSize() {
    $do = new Record(array('addressIds'=>array(1, 3, 4)), $this->dao);
    $addresses = $do->addresses;
    return $this->assertIdentical($addresses->count(), 3);
  }

  public function testForRightDao() {
    $do = new Record(array('addressIds'=>array(1, 3, 4)), $this->dao);
    $addresses = $do->addresses;
    return $this->assertIdentical($addresses->getDao(), $this->mockDao);
  }

}





class FakeDaoWithHash extends NonAbstractDao {

  protected $mockDao;

  public function __construct($mockDao) {
    $this->mockDao = $mockDao;
    parent::__construct();
  }

  protected $attributes = array('address_ids'=>Dao::SEQUENCE);

  protected function setupReferences() {
    $this->addReference('addresses', new DaoToManyReference($this->mockDao));
  }

}



class DaoToManyReferencesWithHash_Basic_Test extends Snap_UnitTestCase {

  protected $dao;
  protected $record;
  protected $mockDao;

  public function setUp() {
    $this->referencedRecord = new Record(array('SOMEDATAHASH'=>987), null);

    $this->mockDao = $this->mock('Dao')
      ->construct();

    $this->dao = new FakeDaoWithHash($this->mockDao);
  }

  public function tearDown() {}

  public function testReference() {
    $do = new Record(array('addresses'=>array(array(), array(), array())), $this->dao);
    return $this->assertIsA($do->addresses, 'DaoHashListIterator');
  }

  public function testIteratorSize() {
    $do = new Record(array('addresses'=>array(array(), array(), array())), $this->dao);
    $addresses = $do->addresses;
    return $this->assertIdentical($addresses->count(), 3);
  }

  public function testForRightDao() {
    $do = new Record(array('addresses'=>array(array(), array(), array())), $this->dao);
    $addresses = $do->addresses;
    return $this->assertIdentical($addresses->getDao(), $this->mockDao);
  }


}


?>
