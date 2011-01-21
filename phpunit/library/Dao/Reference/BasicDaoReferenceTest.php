<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__) . '/../../../setup.php';

require_once LIBRARY_PATH . 'Dao/Dao.php';
require_once LIBRARY_PATH . 'Dao/Reference/BasicDaoReference.php';

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
    $daoReference = $this->getMockForAbstractClass('BasicDaoReference', array('Address', 'localKey', 'foreignKey', false));

    self::assertSame(false, $daoReference->export());
//    self::assertSame('Address', $daoReference->getDaoName());
    self::assertSame('localKey', $daoReference->getLocalKey());
    self::assertSame('foreignKey', $daoReference->getForeignKey());


    $daoReference = $this->getMockForAbstractClass('BasicDaoReference', array('Address'));

//    self::assertSame('Address', $daoReference->getDaoName());
    self::assertSame('id', $daoReference->getForeignKey());
    self::assertNull($daoReference->getSourceDao());
    self::assertNull($daoReference->getLocalKey());
  }


  /**
   * @covers DaoReference::setSourceDao
   */
  public function testSetSourceDao() {

    $daoReference = $this->getMockForAbstractClass('BasicDaoReference', array('Address'));

    self::assertNull($daoReference->getSourceDao());

    $daoReference->setSourceDao($this->dao);

    self::assertSame($this->dao, $daoReference->getSourceDao());
  }

  /**
   * @covers BasicDaoReference::createDao
   */
  public function testCreateDaoForwardsToSourceDao() {

    $daoReference = $this->getMockForAbstractClass('BasicDaoReference', array('Address'));

    $this->getMockForAbstractClass('Dao', array('createDao'), 'NotAbstractDao', false);
    $dao = $this->getMock('NotAbstractDao', array('createDao'), array(), '', false);

    $daoReference->setSourceDao($dao);


    $dao->expects($this->once())->method('createDao')->with('Address')->will($this->returnValue('OTHER DAO'));

    self::assertSame('OTHER DAO', $daoReference->createDao('Address'));
//    setSourceDao
  }


  /**
   * @covers BasicDaoReference::getForeignDao
   */
  public function testGetForeignDaoCallsCreateDaoIfNecessary() {

    $daoReference = $this->getMock('BasicDaoReference', array('createDao', 'getReferenced', 'coerce'), array('Address'));
    $daoReference->expects($this->once())->method('createDao')->with('Address')->will($this->returnValue('OTHER DAO'));
    self::assertSame('OTHER DAO', $daoReference->getForeignDao());

  }

  /**
   * @covers BasicDaoReference::getForeignDao
   */
  public function testGetForeignDaoSimplyReturnsForeignDaoIfItsADaoAlready() {

    $daoReference = $this->getMock('BasicDaoReference', array('getReferenced', 'coerce'), array($this->dao));
    $this->getMockForAbstractClass('Dao', array('createDao'), 'NotAbstractDao2', false);
    $sourceDao = $this->getMock('NotAbstractDao2', array('createDao'), array(), '', false);
    $sourceDao->expects($this->never())->method('createDao');
    $daoReference->setSourceDao($sourceDao);
//    $daoReference->expects($this->never())->method('createDao');
    self::assertSame($this->dao, $daoReference->getForeignDao());

  }

}
