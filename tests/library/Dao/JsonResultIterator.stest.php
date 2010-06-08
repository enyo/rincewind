<?php


require_once(dirname(dirname(__FILE__)) . '/setup.php');

require_once(LIBRARY_ROOT_PATH . 'Dao/Json/JsonDao.php');



class JsonResultIterator_Basic_Test extends Snap_UnitTestCase {

  protected $dao;

  public function setUp() {
    $this->dao = $this->mock('Dao')
      ->listenTo('getRecordFromData')
      ->listenTo('getRecordFromData', array(new Snap_Equals_Expectation(array('some1'=>'hash'))))
      ->listenTo('getRecordFromData', array(new Snap_Equals_Expectation(array('some2'=>'hash'))))
      ->listenTo('getRecordFromData', array(new Snap_Equals_Expectation(array('some3'=>'hash'))))
      ->construct(null);
    $this->it = new JsonResultIterator(array(array('some1'=>'hash'), array('some2'=>'hash'), array('some3'=>'hash')), $this->dao);
  }

  public function tearDown() { }

  public function testLength() {
    return $this->assertIdentical($this->it->count(), 3);
  }

  public function testNothingFetchedUntilNeeded() {
    return $this->assertCallCount($this->dao, 'getRecordFromData', 0);
  }

  public function testAllHashesInstantiated() {
    foreach ($this->it as $test) { }
    return $this->assertCallCount($this->dao, 'getRecordFromData', 3);
  }

  public function testFirstHash() {
    foreach ($this->it as $test) { }
    return $this->assertCallCount($this->dao, 'getRecordFromData', 1, array(new Snap_Equals_Expectation(array('some1'=>'hash'))));
  }

  public function testSecondHash() {
    foreach ($this->it as $test) { }
    return $this->assertCallCount($this->dao, 'getRecordFromData', 1, array(new Snap_Equals_Expectation(array('some2'=>'hash'))));
  }

  public function testThirdHash() {
    foreach ($this->it as $test) { }
    return $this->assertCallCount($this->dao, 'getRecordFromData', 1, array(new Snap_Equals_Expectation(array('some3'=>'hash'))));
  }


}




?>
