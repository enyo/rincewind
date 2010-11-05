<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__) . '/../../../setup.php';

require_once LIBRARY_PATH . 'Dao/File/FileDaoBase.php';



/**
 * Test class for JsonDaoBase.
 */
class FileDaoBaseTest extends PHPUnit_Framework_TestCase {

  /**
   * @var FileDaoBase
   */
  private $fileDao;

  public function setUp() {
    $this->fileDao = new FileDaoBase(null, 'some name', array('id'=>Dao::INT));
  }


  public function testExportSequence() {
    self::assertEquals(array(1, 2, 3), $this->fileDao->exportSequence(array(1, 2, 3)));
    self::assertEquals(array('yo'), $this->fileDao->exportSequence('yo'));
  }


  public function testStartTransactionThrowsException() {
    $this->setExpectedException('FileDaoException', 'Transactions not implemented.');
    $this->fileDao->startTransaction();
  }


  /**
   * @covers FileDaoBase::createIterator
   */
  public function testCreateIteratorReturnsFileResultIterator() {
    $iterator = $this->fileDao->createIterator('someResult');

    self::assertEquals($this->fileDao, $iterator->getDao());
    self::assertType('FileResultIterator', $iterator);
  }

}

