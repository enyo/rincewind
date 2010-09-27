<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__) . '/../../setup.php';

require_once LIBRARY_PATH . 'Dao/Dao.php';



$DAO_ABSTRACT_METHODS = array('setupReferences', 'generateSortString', 'convertRemoteValueToTimestamp', 'find', 'getData', 'getIterator', 'insert', 'update', 'delete', 'startTransaction', 'commit', 'rollback', 'getTotalCount');


class TestDaoClass { }


/**
 * Test class for Dao.
 */
class DaoBasicTest extends PHPUnit_Framework_TestCase {



  public function setUp() {
  }

  /**
   * @expectedException DaoException
   * @covers Dao::__construct
   */
  public function testThrowsExceptionIfNoResourceNameProvided() {
    $this->getMockForAbstractClass('Dao', array(null, array()));
  }

  /**
   * @expectedException DaoException
   * @covers Dao::__construct
   */
  public function testThrowsExceptionIfNoAttributesProvided() {
    $this->getMockForAbstractClass('Dao', array('resource_name'));
  }

  /**
   * @covers Dao::__construct
   * @covers Dao::setupReferences
   */
  public function testConstructorCallsSetupReferences() {
    $mock = $this->getMock('Dao', $GLOBALS['DAO_ABSTRACT_METHODS'], array(), '', false);
    $mock->expects($this->once())->method('setupReferences');
    $mock->__construct('resource_name', array('id'=>Dao::INT));
  }

  public function testAttributesPassedInConstructorAreStored() {
    $attributes = array('id'=>Dao::INT, 'name'=>Dao::STRING);
    $nullAttributes = array('id');
    $defaultValueAttributes = array('name');

    $resourceName = 'resource_name';
    $mock = $this->getMockForAbstractClass('Dao', array($resourceName, $attributes, $nullAttributes, $defaultValueAttributes));
    self::assertEquals($attributes, $mock->getAttributes());
    self::assertEquals($resourceName, $mock->getResourceName());
    self::assertEquals($nullAttributes, $mock->getNullAttributes());
    self::assertEquals($defaultValueAttributes, $mock->getDefaultValueAttributes());
  }

  /**
   * @covers Dao::exportValue
   */
  public function testExportValueCallsTheAppropriateExportMethods() {
    $methods = $GLOBALS['DAO_ABSTRACT_METHODS'];
    array_push($methods, 'exportNull', 'exportEnum', 'exportBool', 'exportInteger', 'exportFloat', 'exportString', 'exportDate', 'exportSequence');
    $mock = $this->getMock('Dao', $methods, array(), '', false);

    $mock->expects($this->once())->method('exportNull');
    $mock->exportValue(null, Dao::INT, false);

    $mock->expects($this->once())->method('exportEnum')->with('value1', array('value1', 'value2', 'value3'));
    $mock->exportValue('value1', array('value1', 'value2', 'value3'));

    $mock->expects($this->once())->method('exportBool')->with(true);
    $mock->exportValue(true, Dao::BOOL);

    $mock->expects($this->once())->method('exportInteger')->with(123);
    $mock->exportValue(123, Dao::INT);

    $mock->expects($this->once())->method('exportFloat')->with(123.12);
    $mock->exportValue(123.12, Dao::FLOAT);

    $mock->expects($this->once())->method('exportString')->with('some string');
    $mock->exportValue('some string', Dao::TEXT);



    $mock->expects($this->exactly(2))->method('exportDate')->with('some date');
    $mock->exportValue('some date', Dao::DATE_WITH_TIME);

    // TODO: This should check for true and false.
    // Right now it's not possible to setup to expectations for the same method......... wtf?
    // $mock->expects($this->once())->method('exportDate')->with('some date', false);
    $mock->exportValue('some date', Dao::DATE);



    $mock->expects($this->once())->method('exportSequence')->with(array(1, 2, 3, 4));
    $mock->exportValue(array(1, 2, 3, 4), Dao::SEQUENCE);



    self::assertEquals('SOME VALUE', $mock->exportValue('SOME VALUE', Dao::IGNORE));

    try {
      $mock->exportValue('some thing', 'Unknown type');
      self::fail('Unknwon types should result in exception.');
    }
    catch (DaoException $e) {
      self::assertEquals('Unhandled type when exporting a value.', $e->getMessage());
    }

  }


  /**
   * @covers Dao::createDao
   */
  public function testCreateDaoJustInstantiatesClassWithName() {
    $mock = $this->getMockForAbstractClass('Dao', array(), '', false);
    $object = $mock->createDao('TestDaoClass');
    self::assertEquals('TestDaoClass', get_class($object));
  }


  /**
   * @covers Dao::get
   */
  public function testGetForwardsToFind() {
    $mock = $this->getMock('Dao', $GLOBALS['DAO_ABSTRACT_METHODS'], array(), '', false);
    $map = array('id'=>4);
    $exportValues = false;
    $resourceName = 'some name';

    $mock->expects($this->once())->method('find')->with($map, $exportValues, $resourceName)->will($this->returnValue('RECORD OBJ'));

    self::assertEquals('RECORD OBJ', $mock->get($map, $exportValues, $resourceName));
  }


  /**
   * @covers Dao::get
   * @expectedException DaoNotFoundException
   * @expectedExceptionMessage Did not find any record.
   */
  public function testGetThrowsExceptionIfFindDoesntReturnAnything() {
    $mock = $this->getMock('Dao', $GLOBALS['DAO_ABSTRACT_METHODS'], array(), '', false);

    $mock->expects($this->once())->method('find')->will($this->returnValue(null));

    $mock->get(array('id'=>4));
  }


  /**
   * @covers Dao::getById
   */
  public function testGetByIdForwardsToGet() {
    $methods = $GLOBALS['DAO_ABSTRACT_METHODS'];
    array_push($methods, 'get');
    $mock = $this->getMock('Dao', $methods, array(), '', false);

    $mock->expects($this->once())->method('get')->with(array('id'=>99))->will($this->returnValue('REC'));

    self::assertEquals('REC', $mock->getById(99));
  }


  /**
   * @covers Dao::findId
   */
  public function testFindIdForwardsToFind() {
    $methods = $GLOBALS['DAO_ABSTRACT_METHODS'];

    $mock = $this->getMock('Dao', $methods, array(), '', false);

    $mock->expects($this->once())->method('find')->with(array('id'=>99))->will($this->returnValue('REC'));

    self::assertEquals('REC', $mock->findId(99));
  }

  /**
   * @covers Dao::deleteById
   */
  public function testDeleteByIdGetsTheObjectAndCallsDelete() {
    $methods = $GLOBALS['DAO_ABSTRACT_METHODS'];
    array_push($methods, 'getById');

    $mock = $this->getMock('Dao', $methods, array(), '', false);

    $mock->expects($this->once())->method('getById')->with(99)->will($this->returnValue('REC'));
    $mock->expects($this->once())->method('DELETE')->with('REC');

    $mock->deleteById(99);
  }


  /**
   * @covers Dao::getAll
   */
  public function testGetAllForwardsToGetIterator() {
    $methods = $GLOBALS['DAO_ABSTRACT_METHODS'];

    $mock = $this->getMock('Dao', $methods, array(), '', false);

    $sort = array('abc');
    $offset = 100;
    $limit = 999;

    $mock->expects($this->once())->method('getIterator')->with(array(), $sort, $offset, $limit)->will($this->returnValue('RECORDS'));

    self::assertEquals('RECORDS', $mock->getAll($sort, $offset, $limit));
  }




  /**
   * @covers Dao::notNull
   */
  public function testNotNullChecksTheNullAttributes() {
    $mock = $this->getMockForAbstractClass('Dao', array('resource', array('id'=>Dao::INT, 'null'=>Dao::STRING, 'notNull'=>Dao::STRING), array('null')));

    self::assertTrue($mock->notNull('id'));
    self::assertTrue($mock->notNull('notNull'));
    self::assertFalse($mock->notNull('null'));
  }

  
}

