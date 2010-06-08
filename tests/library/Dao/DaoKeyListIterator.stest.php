<?php


require_once(dirname(dirname(__FILE__)) . '/setup.php');

require_once(LIBRARY_ROOT_PATH . 'Dao/Dao.php');



class DaoKeyListIterator_Basic_Test extends Snap_UnitTestCase {

  protected $dao;

  public function setUp() {
    $this->dao = $this->mock('Dao')
      ->setReturnValue('getData', array('some'=>'hash'))
      ->setReturnValue('getRecordFromData', 'THE_OBJECT')
      ->listenTo('getData')
      ->listenTo('getData', array(new Snap_Equals_Expectation(array('key'=>1))))
      ->listenTo('getData', array(new Snap_Equals_Expectation(array('key'=>3))))
      ->listenTo('getData', array(new Snap_Equals_Expectation(array('key'=>5))))
      ->listenTo('getRecordFromData', array(new Snap_Equals_Expectation(array('some'=>'hash'))))
      ->construct(null);
    $this->it = new DaoKeyListIterator(array(1, 3, 5), $this->dao, 'key');
  }

  public function tearDown() { }

  public function testLength() {
    return $this->assertIdentical($this->it->count(), 3);
  }

  public function testGetIsCalled() {
    $ob = $this->it->current();
    return $this->assertCallCount($this->dao, 'getData', 1, array(new Snap_Equals_Expectation(array('key'=>1))));
  }

  public function testRightObjectIsInstantiated() {
    $ob = $this->it->current();
    return $this->assertCallCount($this->dao, 'getRecordFromData', 1, array(new Snap_Equals_Expectation(array('some'=>'hash'))));
  }

  public function testGetIsCalledTwice() {
    $ob = $this->it->current();
    $this->it->next();
    $ob = $this->it->current();
    return $this->assertCallCount($this->dao, 'getData', 1, array(new Snap_Equals_Expectation(array('key'=>3))));
  }

  public function testGetIsCalledThrice() {
    $ob = $this->it->current();
    $this->it->next();
    $ob = $this->it->current();
    $this->it->next();
    $ob = $this->it->current();
    return $this->assertCallCount($this->dao, 'getData', 1, array(new Snap_Equals_Expectation(array('key'=>5))));
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
    return $this->assertCallCount($this->dao, 'getData', 3);
  }


}




?>
