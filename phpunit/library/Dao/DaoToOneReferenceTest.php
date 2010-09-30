<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__) . '/../../setup.php';

require_once LIBRARY_PATH . 'Dao/Dao.php';

/**
 * Test class for DaoToOneReference.
 */
class DaoToOneReferenceTest extends PHPUnit_Framework_TestCase {

  /**
   * @var Dao
   */
  protected $dao;

  /**
   * @var Record
   */
  protected $record;
  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp() {
    $this->record = $this->getMock('Record', array(), array(), '', false);
  }

  /**
   * @covers DaoToOneReference::getReferenced
   */
  public function testReferenceJustInstantiatesRecordIfDataPresent() {
    $this->getMockForAbstractClass('Dao', array('createDao'), 'NotAbstractDao', false);
    $this->dao = $this->getMock('NotAbstractDao', array('getRecordFromData'), array(), '', false);
    $recordData = array('id'=>1, 'name'=>'strasse');
    $this->record->expects($this->once())->method('getDirectly')->with('address')->will($this->returnValue($recordData));
    $this->dao->expects($this->once())->method('getRecordFromData')->with($recordData)->will($this->returnValue('RECORD'));

    $reference = new DaoToOneReference($this->dao, 'localKey', 'foreignKey');

    self::assertSame('RECORD', $reference->getReferenced($this->record, 'address'));
  }

  /**
   * @covers DaoToOneReference::getReferenced
   */
  public function testReferenceGetsByIdIfPresentDataIsInt() {
    $this->getMockForAbstractClass('Dao', array('createDao'), 'NotAbstractDao2', false);
    $this->dao = $this->getMock('NotAbstractDao2', array('getById'), array(), '', false);

    $this->record->expects($this->once())->method('getDirectly')->with('address')->will($this->returnValue(123));
    $this->dao->expects($this->once())->method('getById')->with(123)->will($this->returnValue('RECORD'));

    $reference = new DaoToOneReference($this->dao, 'localKey', 'foreignKey');

    self::assertSame('RECORD', $reference->getReferenced($this->record, 'address'));
  }



  /**
   * @covers DaoToOneReference::getReferenced
   */
  public function testReferenceFailsIfPresentDataInvalid() {
    $this->record->expects($this->once())->method('getDirectly')->with('address')->will($this->returnValue('FALSE VALUE'));

    $reference = new DaoToOneReference($this->dao, 'localKey', 'foreignKey');

    $this->setExpectedException('PHPUnit_Framework_Error', 'The data hash for `address` was set but incorrect.');
    self::assertNull($reference->getReferenced($this->record, 'address'));
  }



}
