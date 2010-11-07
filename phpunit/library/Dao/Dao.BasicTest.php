<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__) . '/../../setup.php';

require_once LIBRARY_PATH . 'Dao/Dao.php';



$DAO_ABSTRACT_METHODS = array('setupReferences', 'generateSortString', 'convertRemoteValueToTimestamp', 'find', 'getData', 'getIterator', 'insert', 'update', 'delete', 'startTransaction', 'commit', 'rollback', 'getTotalCount');


class TestClassDao { }


/**
 * Test class for Dao.
 */
class Dao_BasicTest extends PHPUnit_Framework_TestCase {



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
    array_push($methods, 'exportNull', 'exportEnum', 'exportBool', 'exportInteger', 'exportFloat', 'exportString', 'exportDate', 'exportDateWithTime', 'exportSequence');
    $mock = $this->getMock('Dao', $methods, array('resource name', array('attr')));

    $mock->expects($this->exactly(2))->method('exportNull')->will($this->returnValue('NULL123')); // Will be called twice because of exporting Dao::IGNORE
    self::assertEquals('NULL123', $mock->exportValue('attributeName', null, Dao::INT, false));
    self::assertEquals('NULL123', $mock->exportValue('attributeName', 'SOME VALUE', Dao::IGNORE));

    $mock->expects($this->once())->method('exportEnum')->with('value1', array('value1', 'value2', 'value3'))->will($this->returnValue('enum 123'));
    self::assertEquals('enum 123', $mock->exportValue('attributeName', 'value1', array('value1', 'value2', 'value3')));

    $mock->expects($this->once())->method('exportBool')->with(true)->will($this->returnValue('bool 123'));
    self::assertEquals('bool 123', $mock->exportValue('attributeName', true, Dao::BOOL));

    $mock->expects($this->once())->method('exportInteger')->with(123)->will($this->returnValue('int 123'));
    self::assertEquals('int 123', $mock->exportValue('attributeName', 123, Dao::INT));

    $mock->expects($this->once())->method('exportFloat')->with(123.12)->will($this->returnValue('float 123'));
    self::assertEquals('float 123', $mock->exportValue('attributeName', 123.12, Dao::FLOAT));

    $mock->expects($this->once())->method('exportString')->with('some string')->will($this->returnValue('string 123'));
    self::assertEquals('string 123', $mock->exportValue('attributeName', 'some string', Dao::TEXT));



    $mock->expects($this->once())->method('exportDateWithTime')->with('some date')->will($this->returnValue('datewithtime 123'));
    self::assertEquals('datewithtime 123', $mock->exportValue('attributeName', 'some date', Dao::DATE_WITH_TIME));

    $mock->expects($this->once())->method('exportDate')->with('some date')->will($this->returnValue('date 123'));
    self::assertEquals('date 123', $mock->exportValue('attributeName', 'some date', Dao::DATE));



    $mock->expects($this->once())->method('exportSequence')->with(array(1, 2, 3, 4))->will($this->returnValue('seq 123'));
    self::assertEquals('seq 123', $mock->exportValue('attributeName', array(1, 2, 3, 4), Dao::SEQUENCE));




    try {
      $mock->exportValue('attributeName', 'some thing', 'Unknown type');
      self::fail('Unknwon types should result in exception.');
    }
    catch (DaoException $e) {
      self::assertEquals('There was an error exporting the attribute "attributeName" in resource "resource name": The export method exportUnknown type does not exist.', $e->getMessage());
    }

  }


  /**
   * @covers Dao::createDao
   */
  public function testCreateDaoJustInstantiatesClassWithName() {
    $mock = $this->getMockForAbstractClass('Dao', array(), '', false);
    $object = $mock->createDao('TestClass');
    self::assertEquals('TestClassDao', get_class($object));
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

