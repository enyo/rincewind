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

}


?>
