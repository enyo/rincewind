<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__) . '/../../setup.php';

require_once LIBRARY_PATH . 'Dao/Dao.php';

/**
 * Test class for DaoReference.
 */
class DaoReferenceTest extends PHPUnit_Framework_TestCase {

  /**
   * @var Dao
   */
  protected $dao;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp() {
    $this->dao = $this->getMockForAbstractClass('Dao', array('createDao'), '', false);
  }

  public function testSetEverythingInConstructor() {
    $daoReference = $this->getMockForAbstractClass('DaoReference', array('AddressDao', 'localKey', 'foreignKey', $this->dao));

    self::assertSame($this->dao, $daoReference->getSourceDao());
    self::assertSame('AddressDao', $daoReference->getDaoClassName());
    self::assertSame('localKey', $daoReference->getLocalKey());
    self::assertSame('foreignKey', $daoReference->getForeignKey());


    $daoReference = $this->getMockForAbstractClass('DaoReference', array('AddressDao'));

    self::assertSame('AddressDao', $daoReference->getDaoClassName());
    self::assertSame('id', $daoReference->getForeignKey());
    self::assertNull($daoReference->getSourceDao());
    self::assertNull($daoReference->getLocalKey());
  }


  /**
   * @covers DaoReference::setSourceDao
   */
  public function testSetSourceDao() {

    $daoReference = $this->getMockForAbstractClass('DaoReference', array('AddressDao'));

    self::assertNull($daoReference->getSourceDao());

    $daoReference->setSourceDao($this->dao);

    self::assertSame($this->dao, $daoReference->getSourceDao());
  }

  /**
   * @covers DaoReference::createDao
   */
  public function testCreateDaoForwardsToSourceDao() {

    $daoReference = $this->getMockForAbstractClass('DaoReference', array('AddressDao'));

    $this->getMockForAbstractClass('Dao', array('createDao'), 'NotAbstractDao', false);
    $dao = $this->getMock('NotAbstractDao', array('createDao'), array(), '', false);

    $daoReference->setSourceDao($dao);


    $dao->expects($this->once())->method('createDao')->with('AddressDao')->will($this->returnValue('OTHER DAO'));

    self::assertSame('OTHER DAO', $daoReference->createDao('AddressDao'));
//    setSourceDao
  }
}
