<?php

require_once(dirname(dirname(__FILE__)) . '/setup.php');

require_once(LIBRARY_ROOT_PATH . 'Dao/Dao.php');

require_once(dirname(__FILE__) . '/NonAbstractDao.php');


class RecordA { }

class RecordB { }

class DaoA extends NonAbstractDao {

  protected $recordClassName = 'RecordA';

}

class DaoB extends NonAbstractDao {

  protected $recordClassName = 'RecordB';

}



class Dao_DifferentRecords_Test extends Snap_UnitTestCase {

  protected $daoA;
  protected $daoB;

  public function setUp() {
    $this->daoA = new DaoA();
    $this->daoB = new DaoB();
  }

  public function tearDown() {}

  public function testA() {
    return $this->assertIsA($this->daoA->getRecordFromData(array()), 'RecordA');
  }

  public function testB() {
    return $this->assertIsA($this->daoB->getRecordFromData(array()), 'RecordB');
  }

}



?>
