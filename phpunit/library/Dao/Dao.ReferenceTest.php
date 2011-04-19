<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__) . '/../../setup.php';

require_once RINCEWIND_PATH . 'Dao/Dao.php';
require_once RINCEWIND_PATH . 'Dao/Reference/BasicDaoReference.php';



$DAO_ABSTRACT_METHODS = array('setupReferences', 'generateSortString', 'convertRemoteValueToTimestamp', 'find', 'getData', 'getIterator', 'insert', 'update', 'delete', 'startTransaction', 'commit', 'rollback', 'getTotalCount');

/**
 * Test class for Dao.
 */
class Dao_ReferenceTest extends PHPUnit_Framework_TestCase {

  /**
   * @var Dao
   */
  private $dao;

  private $i = 0;

  public function setUp() {
    $methods = $GLOBALS['DAO_ABSTRACT_METHODS'];
    $methods[] = 'getUserReference';
    $this->dao = $this->getMock('Dao', $methods, array('resource_name', array('id' => Dao::INT, 'userId' => Dao::INT, 'user' => Dao::REFERENCE, 'other' => Dao::REFERENCE)));
  }

  /**
   * @covers Dao::getReference
   */
  public function testGettingReferenceNotMarkedAsReferenceThrowsException() {
    $this->setExpectedException('DaoException', 'Can\'t create the reference for attribute unexisting.');
    $this->dao->getReference('unexisting');
  }

  /**
   * @covers Dao::getReference
   */
  public function testGettingReferenceWithMissingGetReferenceMethodThrowsException() {
    $this->setExpectedException('DaoException', 'The method getOtherReference does not exist to create the reference.');
    $this->dao->getReference('other');
  }


  /**
   * @covers Dao::getReference
   */
  public function testReferenceGetsSourceDaoInjected() {
    $reference = $this->getMock('BasicDaoReference', array('setSourceDao', 'getReferenced', 'coerce'), array(), '', false);
    $this->dao->expects($this->once())->method('getUserReference')->will($this->returnValue($reference));
    // TODO: I don't know why, but self::identicalTo doesn't work here... neither does equaltTo, with a bigger maxdepth, because
    // then the new object has the reference, whereas the old one doesn't.
    $reference->expects($this->once())->method('setSourceDao')->with(self::equalTo($this->dao, 0, 1));
    $this->dao->getReference('user');
  }

  /**
   * @covers Dao::getReference
   */
  public function testGettingReference() {
    $reference = $this->getMockForAbstractClass('BasicDaoReference', array(), '', false);
    $this->dao->expects($this->once())->method('getUserReference')->will($this->returnValue($reference));
    self::assertEquals($reference, $this->dao->getReference('user'));
  }


  /**
   * @covers Dao::getReference
   */
  public function testGettingReferenceCachesTheReference() {
    $reference = $this->getMockForAbstractClass('BasicDaoReference', array(), '', false);
    $this->dao->expects($this->once())->method('getUserReference')->will($this->returnValue($reference));
    self::assertEquals($reference, $this->dao->getReference('user'));
    self::assertEquals($reference, $this->dao->getReference('user'));
  }


}

