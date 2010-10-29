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
    $daoReference = $this->getMockForAbstractClass('DaoReference', array('Address', 'localKey', 'foreignKey', false));

    self::assertSame(false, $daoReference->export());
    self::assertSame('Address', $daoReference->getDaoName());
    self::assertSame('localKey', $daoReference->getLocalKey());
    self::assertSame('foreignKey', $daoReference->getForeignKey());


    $daoReference = $this->getMockForAbstractClass('DaoReference', array('Address'));

    self::assertSame('Address', $daoReference->getDaoName());
    self::assertSame('id', $daoReference->getForeignKey());
    self::assertNull($daoReference->getSourceDao());
    self::assertNull($daoReference->getLocalKey());
  }


  /**
   * @covers DaoReference::setSourceDao
   */
  public function testSetSourceDao() {

    $daoReference = $this->getMockForAbstractClass('DaoReference', array('Address'));

    self::assertNull($daoReference->getSourceDao());

    $daoReference->setSourceDao($this->dao);

    self::assertSame($this->dao, $daoReference->getSourceDao());
  }

  /**
   * @covers DaoReference::createDao
   */
  public function testCreateDaoForwardsToSourceDao() {

    $daoReference = $this->getMockForAbstractClass('DaoReference', array('Address'));

    $this->getMockForAbstractClass('Dao', array('createDao'), 'NotAbstractDao', false);
    $dao = $this->getMock('NotAbstractDao', array('createDao'), array(), '', false);

    $daoReference->setSourceDao($dao);


    $dao->expects($this->once())->method('createDao')->with('Address')->will($this->returnValue('OTHER DAO'));

    self::assertSame('OTHER DAO', $daoReference->createDao('Address'));
//    setSourceDao
  }


  /**
   * @covers DaoReference::getForeignDao
   */
  public function testGetForeignDaoCallsCreateDaoIfNecessary() {

    $daoReference = $this->getMock('DaoReference', array('createDao', 'getReferenced', 'coerce'), array('Address'));
    $daoReference->expects($this->once())->method('createDao')->with('Address')->will($this->returnValue('OTHER DAO'));
    self::assertSame('OTHER DAO', $daoReference->getForeignDao());

  }

  /**
   * @covers DaoReference::getForeignDao
   */
  public function testGetForeignDaoSimplyReturnsDaoClassNameIfItsADao() {

    $daoReference = $this->getMock('DaoReference', array('createDao', 'getReferenced', 'coerce'), array($this->dao));
    $daoReference->expects($this->never())->method('createDao');
    self::assertSame($this->dao, $daoReference->getForeignDao());

  }

}
