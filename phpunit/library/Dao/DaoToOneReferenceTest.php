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
    $recordData = array('id' => 1, 'name' => 'strasse');
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

  /**
   * @covers DaoToOneReference::getReferenced
   */
  public function testReferenceReturnsNullIfLocalAndForeignKeysNotSet() {
    $this->record->expects($this->exactly(2))->method('getDirectly')->with('address')->will($this->returnValue(null));

    $reference = new DaoToOneReference($this->dao, 'localKey', null);
    self::assertNull($reference->getReferenced($this->record, 'address'));
    $reference = new DaoToOneReference($this->dao, null, 'foreignKey');
    self::assertNull($reference->getReferenced($this->record, 'address'));
  }

  /**
   * @covers DaoToOneReference::getReferenced
   */
  public function testReferenceGetsTheReferencedRecordAndReturnsIt() {
    $this->getMockForAbstractClass('Dao', array('createDao'), 'NotAbstractDao3', false);
    $dao = $this->getMock('NotAbstractDao3', array('get'), array(), '', false);

    $this->record->expects($this->once())->method('getDirectly')->with('address')->will($this->returnValue(null));

    $this->record->expects($this->once())->method('get')->with('localKey')->will($this->returnValue(1234));

    $newRecord = $this->getMock('Record', array(), array(), '', false);
    $dao->expects($this->once())->method('get')->with(array('foreignKey'=>1234))->will($this->returnValue($newRecord));
    $newRecord->expects($this->once())->method('getArray')->will($this->returnValue(array('a', 'b')));

    // make sure the records gets the data set.
    $this->record->expects($this->once())->method('setDirectly')->with('address', array('a', 'b'));


    $reference = new DaoToOneReference($dao, 'localKey', 'foreignKey');
    self::assertSame($newRecord, $reference->getReferenced($this->record, 'address'));


  }


  /**
   * @covers DaoToOneReference::getReferenced
   */
  public function testReferenceReturnsNullIfLocalValueIsNull() {
    $this->getMockForAbstractClass('Dao', array('createDao'), 'NotAbstractDao4', false);
    $dao = $this->getMock('NotAbstractDao4', array('get'), array(), '', false);

    $this->record->expects($this->once())->method('getDirectly')->with('address')->will($this->returnValue(null));

    $this->record->expects($this->once())->method('get')->with('localKey')->will($this->returnValue(null));

    $reference = new DaoToOneReference($dao, 'localKey', 'foreignKey');
    self::assertNull(null, $reference->getReferenced($this->record, 'address'));
  }

}
