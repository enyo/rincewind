<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__) . '/../../setup.php';

require_once LIBRARY_PATH . 'Dao/Dao.php';



$DAO_ABSTRACT_METHODS = array('setupReferences', 'generateSortString', 'convertRemoteValueToTimestamp', 'find', 'getData', 'getIterator', 'insert', 'update', 'delete', 'startTransaction', 'commit', 'rollback', 'getTotalCount');


/**
 * Test class for Dao.
 */
class Dao_ReferenceTest extends PHPUnit_Framework_TestCase {

  /**
   * @var Dao
   */
  private $dao;

  public function setUp() {
    $this->dao = $this->getMockForAbstractClass('Dao', array('resource_name', array('id'=>Dao::INT, 'referenceId'=>Dao::INT)));
  }

  /**
   * @covers Dao::addReference
   */
  public function testReferenceGetsSourceDaoInjected() {
    $reference = $this->getMock('DaoReference', array('setSourceDao', 'getReferenced'), array(), '', false);
    // TODO: I don't know why, but self::identicalTo doesn't work here... neither does equaltTo, with a bigger maxdepth, because
    // then the new object has the reference, whereas the old one doesn't.
    $reference->expects($this->once())->method('setSourceDao')->with(self::equalTo($this->dao, 0, 1));
    $this->dao->addReference('referenceId', $reference);
  }

  /**
   * @covers Dao::getReference
   * @expectedException DaoWrongValueException
   */
  public function testGettingUnexistingReferenceThrowsException() {
    $this->dao->getReference('unexisting');
  }

  /**
   * @covers Dao::getReference
   */
  public function testGettingReference() {
    $reference = $this->getMockForAbstractClass('DaoReference', array(), '', false);
    $this->dao->addReference('referenceId', $reference);
    self::assertEquals($reference, $this->dao->getReference('referenceId'));
  }

  /**
   * @covers Dao::getReferences
   */
  public function testGettingAllReferences() {
    $reference = $this->getMockForAbstractClass('DaoReference', array(), '', false);
    $reference2 = $this->getMockForAbstractClass('DaoReference', array(), '', false);
    $this->dao->addReference('referenceId', $reference);
    $this->dao->addReference('reference2Id', $reference2);
    self::assertEquals(array('referenceId'=>$reference, 'reference2Id'=>$reference2), $this->dao->getReferences());
  }

}

