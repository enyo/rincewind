<?php

require_once(dirname(dirname(__FILE__)) . '/setup.php');

require_once(LIBRARY_ROOT_PATH . 'Dao/Dao.php');


class Record_WithId_Test extends Snap_UnitTestCase {

  protected $dao;
  protected $record;

  protected $testId = 17;
  protected $testInteger = 77;
  protected $testTime = 123123;

  public function setUp() {
      $this->dao = $this->mock('DaoInterface')
      ->setReturnValue('getAttributes', array('id'=>Dao::INT, 'integer'=>Dao::INT, 'time'=>Dao::TIMESTAMP, 'name'=>Dao::STRING, 'nullAttribute'=>Dao::STRING, 'enum'=>array('a', 'b', 'c')))
      ->setReturnValue('getNullAttributes', array('nullAttribute'))
      ->listenTo('insert')
      ->listenTo('update')
      ->listenTo('delete')
      ->construct();
    $this->record = new Record(array('id'=>$this->testId, 'integer'=>$this->testInteger, 'time'=>$this->testTime, 'name'=>'matthias', 'nullAttribute'=>null, 'additionalAttributeString'=>'ADDCOLUMNSTTT', 'enum'=>'b'), $this->dao, $existsInDatabase = true);
  }

  public function tearDown() {
    unset($this->record);
  }

  public function testRecordId() {
    return $this->assertEqual($this->record->id, $this->testId);
  }

  public function testRecordInteger() {
    return $this->assertIdentical($this->record->integer, $this->testInteger);
  }

  public function testRecordTime() {
    return $this->assertIsA($this->record->time, 'Date');
  }

  public function testRecordTimestamp() {
    return $this->assertEqual($this->record->time->getTimestamp(), $this->testTime);
  }

  public function testRecordName() {
    return $this->assertEqual($this->record->name, 'matthias');
  }

  public function testRecordNullAttribute() {
    return $this->assertNull($this->record->nullAttribute);
  }

  public function testRecordEnum() {
    return $this->assertEqual($this->record->enum, 'b');
  }

  public function testSettingInteger() {
    $int = $this->testInteger + 877;
    $this->record->integer = $int;
    return $this->assertIdentical($this->record->integer, $int);
  }

  public function testSettingTime() {
    $ts = 91919191;
    $this->record->time = new Date($ts);
    return $this->assertIdentical($this->record->time->getTimestamp(), $ts);
  }

  public function testSettingString() {
    $name = 'Some new name';
    $this->record->name = $name;
    return $this->assertIdentical($this->record->name, $name);
  }

  public function testSettingEnumA() {
    $this->record->enum = 'a';
    return $this->assertIdentical($this->record->enum, 'a');
  }
  public function testSettingEnumB() {
    $this->record->enum = 'b';
    return $this->assertIdentical($this->record->enum, 'b');
  }
  public function testSettingEnumC() {
    $this->record->enum = 'c';
    return $this->assertIdentical($this->record->enum, 'c');
  }
  public function testSettingWrongEnum() {
    $this->willWarn();
    $this->record->enum = 'd';
    return $this->assertIdentical($this->record->enum, 'a'); // Gets reset to the default value since null is not allowed
  }
  public function testSettingEmptyEnum() {
    $this->willWarn();
    $this->record->enum = '';
    return $this->assertIdentical($this->record->enum, 'a'); // Gets reset to the default value since null is not allowed
  }



  public function testSettingNull() {
    $this->record->nullAttribute = null;
    return $this->assertNull($this->record->nullAttribute);
  }

  public function testSettingNullAfterValueWasAssigned() {
    $this->record->nullAttribute = 'test';
    $this->record->nullAttribute = null;
    return $this->assertNull($this->record->nullAttribute);
  }
  public function testSettingNullToValue() {
    $string = 'Some Random String';
    $this->record->nullAttribute = $string;
    return $this->assertIdentical($this->record->nullAttribute, $string);
  }


  public function testSetter() {
    $name = 'Some new name';
    $this->record->set('name', $name);
    return $this->assertIdentical($this->record->name, $name);
  }

  public function testGetter() {
    $name = 'Some new name';
    $this->record->set('name', $name);
    return $this->assertIdentical($this->record->get('name'), $name);
  }

  public function testChainingOfSetter() {
    return $this->assertIdentical($this->record->set('name', 'test'), $this->record);
  }


  public function testUpdate() {
    $this->record->save();
    return $this->assertCallCount($this->dao, 'update', 1);
  }

  public function testDelete() {
    $this->record->delete();
    return $this->assertCallCount($this->dao, 'delete', 1);
  }


  public function testDirectSetter() {
    $name = 'Some new name 123';
    $this->record->setDirectly('name', $name);
    return $this->assertIdentical($this->record->name, $name);
  }

  public function testDirectGetter() {
    $name = 'Some new name 123456';
    $this->record->set('name', $name);
    return $this->assertIdentical($this->record->getDirectly('name'), $name);
  }

}



class Record_WithoutId_Test extends Snap_UnitTestCase {

  protected $dao;
  protected $dataHash;

  protected $testId = 18;
  protected $testInteger = 78;
  protected $testTime = 123124;

  public function setUp() {
    $this->dao = $this->mock('DaoInterface')
      ->setReturnValue('getAttributes', array('id'=>Dao::INT, 'integer'=>Dao::INT, 'time'=>Dao::TIMESTAMP, 'name'=>Dao::STRING, 'nullAttribute'=>Dao::STRING))
      ->setReturnValue('getNullAttributes', array('nullAttribute'))
      ->listenTo('insert')
      ->listenTo('update')
      ->construct();

    $this->dataHash = array('id'=>null, 'integer'=>$this->testInteger, 'time'=>$this->testTime, 'name'=>'matthias', 'nullAttribute'=>null);
  }

  public function tearDown() {
    unset($this->record);
  }

  public function testInsertThatDoesntExistInDatabase() {
    $record = new Record($this->dataHash, $this->dao, $existsInDatabase = false);
    $record->save();
    return $this->assertCallCount($this->dao, 'insert', 1);
  }

  public function testInsertThatDoesExistInDatabase() {
    $record = new Record($this->dataHash, $this->dao, $existsInDatabase = true);
    $record->save();
    return $this->assertCallCount($this->dao, 'update', 1);
  }

  public function testInsertDefault() {
    $record = new Record($this->dataHash, $this->dao);
    $record->save();
    return $this->assertCallCount($this->dao, 'insert', 1, array(), 'Per default Records should not exist in database.');
  }

}



class Record_WithAdditionalValues_Test extends Snap_UnitTestCase {

  protected $additionalValue = 'ADSFSDKFLJ2r23kjl';

  public function setUp() {
      $this->dao = $this->mock('DaoInterface')
        ->setReturnValue('getAttributes',       array('id'=>Dao::INT))
        ->setReturnValue('getNullAttributes',       array())
        ->setReturnValue('getAdditionalAttributes', array('additionalAttributeString'=>Dao::STRING, 'additionalAttributeInt'=>Dao::INT))
        ->listenTo('insert')
        ->listenTo('update')
        ->construct();
      $this->record = new Record(array('id'=>null, 'additionalAttributeString'=>$this->additionalValue), $this->dao);
  }

  public function tearDown() {
    unset($this->record);
  }

  public function testGettingAdditionalValue() {
    return $this->assertEqual($this->record->additionalAttributeString, $this->additionalValue);
  }

  public function testGettingUnsetAdditionalValueIsNull() {
    return $this->assertNull($this->record->additionalAttributeInt);
  }

  public function testGettingAdditionalValueWithGetter() {
    return $this->assertEqual($this->record->get('additionalAttributeString'), $this->additionalValue);
  }

  public function testSettingAdditionalValueFailsButChains() {
    $this->willWarn();
    return $this->assertEqual($this->record->set('additionalAttributeString', 'test'), $this->record);
  }

}


class Record_ChangedAttributes_Test extends Snap_UnitTestCase {


  public function setUp() {
      $this->dao = $this->mock('DaoInterface')
        ->setReturnValue('getAttributes',           array('a'=>Dao::INT, 'b'=>Dao::STRING, 'c'=>Dao::BOOL))
        ->setReturnValue('getNullAttributes',       array())
        ->setReturnValue('getAdditionalAttributes', array())
        ->construct();
      $this->record = new Record(array('a'=>123, 'b'=>'test', 'c'=>true), $this->dao);
  }

  public function tearDown() {
    unset($this->record);
  }

  public function testSettingNone() {
    return $this->assertEqual($this->record->getChangedAttributes(), array());
  }

  public function testSettingOne() {
    $this->record->a = 321;
    return $this->assertEqual($this->record->getChangedAttributes(), array('a'=>true));
  }

  public function testSettingTwo() {
    $this->record->a = 321;
    $this->record->b = 'abc';
    return $this->assertEqual($this->record->getChangedAttributes(), array('a'=>true, 'b'=>true));
  }

  public function testSettingMultipleTimes() {
    $this->record->a = 321;
    $this->record->a = 456;
    $this->record->a = 789;
    $this->record->b = 'abc';
    $this->record->b = 'ddd';
    $this->record->c = true;
    $this->record->c = false;
    $this->record->c = true;
    return $this->assertEqual($this->record->getChangedAttributes(), array('a'=>true, 'b'=>true, 'c'=>true));
  }

  public function testSettingSameValue() {
    $this->record->a = 123;
    return $this->assertEqual($this->record->getChangedAttributes(), array('a'=>true), 'Even though the value didnt change, it should be in the changedAttributes since it has been set.');
  }


  public function testWithValues() {
    $this->record->a = 321;
    $this->record->b = 'abc';
    return $this->assertEqual($this->record->getChangedValues(), array('a'=>321, 'b'=>'abc'));
  }

  public function testWithValueOverwrites() {
    $this->record->a = 321;
    $this->record->a = 654;
    $this->record->c = true;
    $this->record->c = false;
    return $this->assertEqual($this->record->getChangedValues(), array('a'=>654, 'c'=>false));
  }




}



class Record_Load_Test extends Snap_UnitTestCase {


  public function setUp() {
      $this->dao = $this->mock('DaoInterface')
        ->setReturnValue('getAttributes',       array('a'=>Dao::INT, 'b'=>Dao::STRING, 'c'=>Dao::BOOL))
        ->setReturnValue('getNullAttributes',       array())
        ->setReturnValue('getAdditionalAttributes', array())
        ->setReturnValue('getData',       array('a'=>321, 'b'=>'test2', 'c'=>false))
        ->listenTo('getData', array(new Snap_Object_Expectation('Record')))
        ->construct();
      $this->record = new Record(array('a'=>0, 'b'=>'', 'c'=>false), $this->dao);
  }

  public function tearDown() {
    unset($this->record);
  }

  public function testLoadingObjectCallsDao() {
    $this->record->a = 7;
    $this->record->load();
    return $this->assertCallCount($this->dao, 'getData', 1, array(new Snap_Object_Expectation('Record')));
  }

  public function testLoadedValuesA() {
    $this->record->set('a', 321)->load();
    return $this->assertIdentical($this->record->a, 321);
  }

  public function testLoadedValuesB() {
    $this->record->set('a', 321)->load();
    return $this->assertIdentical($this->record->b, 'test2');
  }

  public function testLoadedValuesC() {
    $this->record->set('a', 321)->load();
    return $this->assertIdentical($this->record->c, false);
  }

  public function testChangedValuesAreResetAfterLoading() {
    $this->record->set('a', 321)->load();
    return $this->assertEqual($this->record->getChangedValues(), array());
  }

  public function testRecordExistsInDatabaseAfter() {
    $this->record->set('a', 321)->load();
    return $this->assertTrue($this->record->existsInDatabase());
  }


}




?>
