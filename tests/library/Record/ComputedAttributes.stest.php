<?php

require_once(dirname(dirname(__FILE__)) . '/setup.php');

require_once(LIBRARY_ROOT_PATH . 'Dao/Dao.php');


class TestRecord extends Record {

  public $computedCount = 0;

  protected function _computedAttribute() {
    $this->computedCount ++;
    return 'ABC123';
  }

}


class Record_Compute_Test extends Snap_UnitTestCase {


  public function setUp() {
    $this->dao = $this->mock('DaoInterface')
    ->setReturnValue('getAttributes', array())
    ->setReturnValue('getAdditionalAttributes', array())
    ->setReturnValue('getReferences', array())
    ->construct();

    $this->record = new TestRecord(array(), $this->dao);
  }

  public function tearDown() {
    unset($this->record);
  }

  public function testAccessValue() {
    return $this->assertIdentical($this->record->computedAttribute, 'ABC123');
  }

  public function testGetValue() {
    return $this->assertIdentical($this->record->get('computedAttribute'), 'ABC123');
  }

  public function testCallCount() {
    $this->record->computedAttribute;
    $this->record->computedAttribute;
    $this->record->computedAttribute;
    return $this->assertIdentical($this->record->computedCount, 1);
  }

  public function testSetDataClearsCacheCount() {
    $this->record->computedAttribute;
    $this->record->computedAttribute;
    $this->record->setData(array());
    $this->record->computedAttribute;
    $this->record->computedAttribute;
    return $this->assertIdentical($this->record->computedCount, 2);
  }

}


class TestRecord2 extends Record {


  protected $cacheDependencies = array(
    'first_name'=>array('fullName', 'computedAttribute'),
    'last_name'=>'fullName'
  );

  public $computedCount = 0;
  public $fullNameCount = 0;

  protected function _fullName() {
    $this->fullNameCount ++;
    return $this->firstName . ' ' . $this->lastName;
  }
  protected function _computedAttribute() {
    $this->computedCount ++;
    return 'A123';
  }

}

class Record_CacheDependencies_Test extends Snap_UnitTestCase {


  public function setUp() {
    $this->dao = $this->mock('DaoInterface')
    ->setReturnValue('getAttributes', array('first_name'=>Dao::STRING, 'last_name'=>Dao::STRING))
    ->setReturnValue('getAdditionalAttributes', array())
    ->setReturnValue('getNullAttributes', array())
    ->setReturnValue('getReferences', array())
    ->construct();

    $this->record = new TestRecord2(array('first_name'=>'John', 'last_name'=>'Doe'), $this->dao);
  }

  public function tearDown() {
    unset($this->record);
  }

  public function testFullName() {
    return $this->assertIdentical($this->record->fullName, 'John Doe');
  }

  public function testComputedAttribute() {
    return $this->assertIdentical($this->record->computedAttribute, 'A123');
  }

  public function testFullNameCache() {
    $this->record->fullName;
    $this->record->fullName;
    $this->record->fullName;
    return $this->assertIdentical($this->record->fullNameCount, 1);
  }

  public function testFullNameCacheClears() {
    $this->record->fullName;
    $this->record->fullName;
    $this->record->firstName = 'James';
    $this->record->fullName;
    return $this->assertIdentical($this->record->fullNameCount, 2);
  }

  public function testFullNameCacheClearsAndResets() {
    $this->record->fullName;
    $this->record->firstName = 'James';
    $this->record->fullName;
    return $this->assertIdentical($this->record->fullName, 'James Doe');
  }

  public function testMultipleDependencies() {
    $this->record->fullName;
    $this->record->computedAttribute;
    $this->record->firstName = 'James';
    $this->record->fullName;
    $this->record->computedAttribute;
    return $this->assertIdentical($this->record->computedCount, 2);
  }

  public function testDependencyDoesntAffectOthers() {
    $this->record->fullName;
    $this->record->computedAttribute;
    $this->record->lastName = 'Johnson';
    $this->record->fullName;
    $this->record->computedAttribute;
    return $this->assertIdentical($this->record->computedCount, 1);
  }


}



?>
