<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__) . '/../../setup.php';

require_once LIBRARY_PATH . 'Dao/Dao.php';

/**
 * Test class for Record.
 */
class Record_LazyLoading_Test extends PHPUnit_Framework_TestCase {

  /**
   * @var Dao
   */
  protected $dao;

  public function setUp() {
    $m = $this->getMockForAbstractClass('Dao', array('createDao'), '', false);
    $this->dao = $this->getMock(get_class($m), array('getData', 'getAttributes'), array(), '', false);
  }

  public function testLoadPassesOnlyIdIfExistsInDatabase() {
    $record = new Record(array('id' => 4, 'name' => 'test'), $this->dao, TRUE);
    $this->dao->expects($this->any())->method('getAttributes')->will($this->returnValue(array('id' => Dao::INT, 'name' => Dao::STRING)));
    $this->dao->expects($this->once())->method('getData')->with(array('id' => 4));
    $record->load();
  }

  public function testLoadThrowsExceptionIfNothingSetExplicitlyAndDoesntExistInDatabase() {
    $record = new Record(array('id' => 4, 'name' => 'test'), $this->dao, false);
    $this->dao->expects($this->any())->method('getAttributes')->will($this->returnValue(array('id' => Dao::INT, 'name' => Dao::STRING)));
    $this->setExpectedException('RecordException', 'You tried to load a Record which had not attributes set.');
    $record->load();
  }

  public function testRecordCallsLoadIfAnAttributeWasNotSetYet() {
    $record = new Record(array('id' => 4), $this->dao, TRUE);
    $this->dao->expects($this->any())->method('getAttributes')->will($this->returnValue(array('id' => Dao::INT, 'name' => Dao::STRING)));
    $this->dao->expects($this->once())->method('getData')->with(array('id' => 4))->will($this->returnValue(array('id' => 4, 'name' => 'Test')));
    self::assertEquals('Test', $record->get('name'));
    self::assertEquals('Test', $record->get('name')); // Checking it doesn't load twice
  }

}

