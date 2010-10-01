<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__) . '/../../../setup.php';

require_once LIBRARY_PATH . 'Dao/Json/JsonDao.php';



/**
 * Test class for JsonDao.
 */
class JsonDaoTest extends PHPUnit_Framework_TestCase {

  /**
   * @var JsonDao
   */
  private $jsonDao;

  public function setUp() {
    $this->jsonDao = new JsonDao(null, 'some name', array('id'=>Dao::INT));
  }


  public function testExportSequence() {
    self::assertEquals(array(1, 2, 3), $this->jsonDao->exportSequence(array(1, 2, 3)));
    self::assertEquals(array('yo'), $this->jsonDao->exportSequence('yo'));
  }


  public function testStartTransactionThrowsException() {
    $this->setExpectedException('JsonDaoException', 'Transactions not implemented.');
    $this->jsonDao->startTransaction();
  }


  /**
   * @covers JsonDao::createIterator
   */
  public function testCreateIteratorReturnsJsonResultIterator() {
    $iterator = $this->jsonDao->createIterator(array('some'=>'data'));

    self::assertEquals(array('some'=>'data'), $iterator->getData());
    self::assertEquals($this->jsonDao, $iterator->getDao());
  }

  /**
   * @covers JsonDao::interpretFileContent
   */
  public function testInterpretFileContentDecodesJson() {
    $decoded = $this->jsonDao->interpretFileContent('{ "a": "bc" }');
  }


  /**
   * @covers JsonDao::interpretFileContent
   * @backupStaticAttributes enabled
   */
  public function testInterpretFileContentThrowsExceptionIfWrongJson() {
    $this->setExpectedException('JsonDaoException', 'Json could not be decoded.');
    $decoded = $this->jsonDao->interpretFileContent('{ a: "bc" }');
    self::assertEquals(array(''), Log::$errors);
  }

}

