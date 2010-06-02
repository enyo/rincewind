<?php


require_once(dirname(dirname(__FILE__)) . '/setup.php');

require_once(LIBRARY_ROOT_PATH . 'Dao/Dao.php');



class DaoHashListIterator_Basic_Test extends Snap_UnitTestCase {

  protected $dao;

  public function setUp() {
    $this->dao = $this->mock('Dao')
      ->setReturnValue('getObjectFromData', 'THE_OBJECT')
      ->listenTo('getObjectFromData', array(new Snap_Equals_Expectation(array('hash'=>1))))
      ->listenTo('getObjectFromData', array(new Snap_Equals_Expectation(array('hash'=>3))))
      ->listenTo('getObjectFromData', array(new Snap_Equals_Expectation(array('hash'=>5))))
      ->construct(null);
    $this->it = new DaoHashListIterator(array(array('hash'=>1), array('hash'=>3), array('hash'=>5)), $this->dao);
  }

  public function tearDown() { }

  public function testLength() {
    return $this->assertIdentical($this->it->count(), 3);
  }

  public function testGetObjectIsCalled() {
    $ob = $this->it->current();
    return $this->assertCallCount($this->dao, 'getObjectFromData', 1, array(new Snap_Equals_Expectation(array('hash'=>1))));
  }

  public function testGetIsCalledTwice() {
    $ob = $this->it->current();
    $this->it->next();
    $ob = $this->it->current();
    return $this->assertCallCount($this->dao, 'getObjectFromData', 1, array(new Snap_Equals_Expectation(array('hash'=>3))));
  }

  public function testGetIsCalledThrice() {
    $ob = $this->it->current();
    $this->it->next();
    $ob = $this->it->current();
    $this->it->next();
    $ob = $this->it->current();
    return $this->assertCallCount($this->dao, 'getObjectFromData', 1, array(new Snap_Equals_Expectation(array('hash'=>5))));
  }

  public function testGetIsCalledThriceAndReturned() {
    $ob = $this->it->current();
    $this->it->next()->current();
    $ob = $this->it->next()->current();
    return $this->assertIdentical($ob, 'THE_OBJECT');
  }

  public function testFourthIsNull() {
    $ob = $this->it->current();
    $this->it->next()->current();
    $this->it->next()->current();
    $ob = $this->it->next()->current();
    return $this->assertIdentical($ob, null);
  }

  public function testCorrectCallCountInForeach() {
    $i = 0;
    foreach ($this->it as $ob) { $i ++; }
    return $this->assertIdentical($i, 3);
  }

  public function testCorrectCallCount() {
    foreach ($this->it as $ob) {}
    return $this->assertCallCount($this->dao, 'getObjectFromData', 3);
  }


}




?>