<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__) . '/../../setup.php';

require_once LIBRARY_PATH . 'Dao/Dao.php';

/**
 * Test class for DaoResultIterator.
 */
class DaoResultIteratorTest extends PHPUnit_Framework_TestCase {

  /**
   * @var DaoResultIterator
   */
  protected $daoResultIterator;


  /**
   * @var Dao
   */
  protected $dao;

  protected function setUp() {
    $this->dao = $this->getMockForAbstractClass('Dao', array(), '', false);
    $this->daoResultIterator = $this->getMockForAbstractClass('DaoResultIterator', array($this->dao));
  }


  public function testGetDao() {
    self::assertSame($this->dao, $this->daoResultIterator->getDao());
  }


}

