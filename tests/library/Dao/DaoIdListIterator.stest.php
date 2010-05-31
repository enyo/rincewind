<?php


require_once(dirname(dirname(__FILE__)) . '/setup.php');

require_once(LIBRARY_ROOT_PATH . 'Dao/Dao.php');



class DaoIdListIterator_Basic_Test extends Snap_UnitTestCase {

  protected $dao;

  public function setUp() {
    $this->dao = $this->mock('Dao')
      ->setReturnValue('getData', array('some'=>'hash'))
      ->setReturnValue('getObjectFromData', 'THE_OBJECT')
      ->listenTo('getData')
      ->listenTo('getData', array(new Snap_Equals_Expectation(array('id'=>1))))
      ->listenTo('getData', array(new Snap_Equals_Expectation(array('id'=>3))))
      ->listenTo('getData', array(new Snap_Equals_Expectation(array('id'=>5))))
      ->listenTo('getObjectFromData', array(new Snap_Equals_Expectation(array('some'=>'hash'))))
      ->construct(null);
    $this->it = new DaoIdListIterator(array(1, 3, 5), $this->dao);
  }

  public function tearDown() { }

  public function testLength() {
    return $this->assertIdentical($this->it->count(), 3);
  }

  public function testGetIsCalled() {
    $ob = $this->it->current();
    return $this->assertCallCount($this->dao, 'getData', 1, array(new Snap_Equals_Expectation(array('id'=>1))));
  }

  public function testRightObjectIsInstantiated() {
    $ob = $this->it->current();
    return $this->assertCallCount($this->dao, 'getObjectFromData', 1, array(new Snap_Equals_Expectation(array('some'=>'hash'))));
  }

  public function testGetIsCalledTwice() {
    $ob = $this->it->current();
    $this->it->next();
    $ob = $this->it->current();
    return $this->assertCallCount($this->dao, 'getData', 1, array(new Snap_Equals_Expectation(array('id'=>3))));
  }

  public function testGetIsCalledThrice() {
    $ob = $this->it->current();
    $this->it->next();
    $ob = $this->it->current();
    $this->it->next();
    $ob = $this->it->current();
    return $this->assertCallCount($this->dao, 'getData', 1, array(new Snap_Equals_Expectation(array('id'=>5))));
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

  // This test should not fail!
  // It's not an error though... it's just that the iterators should only fetch the data when necessary.
  public function testCorrectCallCount() {
    $this->todo();
    foreach ($this->it as $ob) {}
    return $this->assertCallCount($this->dao, 'getData', 3);
  }


}




?>