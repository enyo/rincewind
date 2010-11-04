<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__) . '/../../../setup.php';

require_once LIBRARY_PATH . 'Dao/Dao.php';
require_once LIBRARY_PATH . 'Dao/Reference/DaoToManyReference.php';

/**
 * Test class for DaoToManyReference.
 */
class DaoToManyReferenceTest extends PHPUnit_Framework_TestCase {

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
   * @covers DaoToManyReference::getReferenced
   */
  public function testReferenceReturnsDaoHashListIteratorIfDataPresent() {
    $this->getMockForAbstractClass('Dao', array('createDao'), 'AAA_NotAbstractDao', false);
    $dao = $this->getMock('AAA_NotAbstractDao', array('getRecordFromData'), array(), '', false);
    $recordData = array(array('id' => 1, 'name' => 'strasse'), array('id' => 2, 'name' => 'strasse2'));

    $this->record->expects($this->once())->method('getDirectly')->with('address')->will($this->returnValue($recordData));

    $reference = new DaoToManyReference($dao, 'localKey', 'foreignKey');

    $iterator = $reference->getReferenced($this->record, 'address');

    self::assertSame('DaoHashListIterator', get_class($iterator));
    self::assertSame($recordData, $iterator->getHashList());
    self::assertSame($dao, $iterator->getDao());
  }

  /**
   * @covers DaoToManyReference::getReferenced
   */
  public function testReferenceReturnsDaoIdListIteratorIfDataIsInt() {
    $this->getMockForAbstractClass('Dao', array('createDao'), 'AAA_NotAbstractDao2', false);
    $dao = $this->getMock('AAA_NotAbstractDao2', array('getById'), array(), '', false);

    $this->record->expects($this->once())->method('getDirectly')->with('address')->will($this->returnValue(array(1, 2, 3)));

    $reference = new DaoToManyReference($dao, 'localKey', 'foreignKey');

    $iterator = $reference->getReferenced($this->record, 'address');

    self::assertSame('DaoKeyListIterator', get_class($iterator));
    self::assertSame(array(1, 2, 3), $iterator->getKeyList());
    self::assertSame($dao, $iterator->getDao());
    self::assertSame('foreignKey', $iterator->getKeyName());
  }

  /**
   * @covers DaoToManyReference::getReferenced
   * @backupStaticAttributes enabled
   */
  public function testReferenceReturnsEmptyHashListIteratorAndLogsWarningIfPresentDataNotArray() {
    $this->record->expects($this->once())->method('getDirectly')->with('address')->will($this->returnValue('FALSE VALUE'));

    $dao = $this->getMockForAbstractClass('Dao', array('createDao'), '', false);

    $reference = new DaoToManyReference($dao, 'localKey', 'foreignKey');

    $iterator = $reference->getReferenced($this->record, 'address');
    self::assertSame('DaoHashListIterator', get_class($iterator));
    self::assertSame(array(), $iterator->getHashList());
    self::assertSame($dao, $iterator->getDao());
    self::assertSame(array('The data hash for `address` was set but incorrect.'), Log::$warnings);
  }

  /**
   * @covers DaoToManyReference::getReferenced
   * @backupStaticAttributes enabled
   */
  public function testReferenceReturnsEmptyHashListIteratorAndLogsWarningIfPresentDataArrayWithWrongValues() {
    $this->record->expects($this->once())->method('getDirectly')->with('address')->will($this->returnValue(array('hi', 'there')));

    $dao = $this->getMockForAbstractClass('Dao', array('createDao'), '', false);

    $reference = new DaoToManyReference($dao, 'localKey', 'foreignKey');

    $iterator = $reference->getReferenced($this->record, 'address');
    self::assertSame('DaoHashListIterator', get_class($iterator));
    self::assertSame(array(), $iterator->getHashList());
    self::assertSame($dao, $iterator->getDao());
    self::assertSame(array('The data hash for `address` was set but incorrect.'), Log::$warnings);
  }

  /**
   * @covers DaoToManyReference::getReferenced
   */
  public function testReferenceReturnsEmptyKeyListIteratorIfLocalAndForeignKeysNotSet() {
    $dao = $this->getMockForAbstractClass('Dao', array('createDao'), '', false);
    $this->record->expects($this->exactly(2))->method('getDirectly')->with('address')->will($this->returnValue(null));

    $reference = new DaoToManyReference($dao, 'localKey', null);

    $iterator = $reference->getReferenced($this->record, 'address');
    self::assertSame('DaoKeyListIterator', get_class($iterator));
    self::assertSame(array(), $iterator->getKeyList());
    self::assertSame($dao, $iterator->getDao());

    $iterator = $reference->getReferenced($this->record, 'address');
    self::assertSame('DaoKeyListIterator', get_class($iterator));
    self::assertSame(array(), $iterator->getKeyList());
    self::assertSame($dao, $iterator->getDao());
  }

  /**
   * @covers DaoToManyReference::getReferenced
   */
  public function testReferenceReturnsDaoIdListIteratorFromLocalKey() {
    $this->getMockForAbstractClass('Dao', array('createDao'), 'AAA_NotAbstractDao3', false);
    $dao = $this->getMock('AAA_NotAbstractDao3', array('get'), array(), '', false);

    $this->record->expects($this->once())->method('getDirectly')->with('address')->will($this->returnValue(null));

    $this->record->expects($this->once())->method('get')->with('localKey')->will($this->returnValue(array(1, 2, 3)));

    $reference = new DaoToManyReference($dao, 'localKey', 'foreignKey');

    $iterator = $reference->getReferenced($this->record, 'address');
    self::assertSame('DaoKeyListIterator', get_class($iterator));
    self::assertSame(array(1, 2, 3), $iterator->getKeyList());
    self::assertSame($dao, $iterator->getDao());
  }

  /**
   * @covers DaoToManyReference::getReferenced
   */
  public function testReferenceReturnsEmptyDaoIdListIteratorIfLocalKeyIsNull() {
    $this->getMockForAbstractClass('Dao', array('createDao'), 'AAA_NotAbstractDao4', false);
    $dao = $this->getMock('AAA_NotAbstractDao4', array('get'), array(), '', false);

    $this->record->expects($this->once())->method('getDirectly')->with('address')->will($this->returnValue(null));

    $this->record->expects($this->once())->method('get')->with('localKey')->will($this->returnValue(null));

    $reference = new DaoToManyReference($dao, 'localKey', 'foreignKey');

    $iterator = $reference->getReferenced($this->record, 'address');
    self::assertSame('DaoKeyListIterator', get_class($iterator));
    self::assertSame(array(), $iterator->getKeyList());
    self::assertSame($dao, $iterator->getDao());
  }
}
