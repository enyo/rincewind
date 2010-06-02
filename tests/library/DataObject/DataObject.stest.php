<?php

require_once(dirname(dirname(__FILE__)) . '/setup.php');

require_once(LIBRARY_ROOT_PATH . 'Dao/Dao.php');


class DataObject_WithId_Test extends Snap_UnitTestCase {

  protected $dao;
  protected $dataObject;

  protected $testId = 17;
  protected $testInteger = 77;
  protected $testTime = 123123;

  public function setUp() {
      $this->dao = $this->mock('DaoInterface')
      ->setReturnValue('getColumnTypes', array('id'=>Dao::INT, 'integer'=>Dao::INT, 'time'=>Dao::TIMESTAMP, 'name'=>Dao::STRING, 'null_column'=>Dao::STRING, 'enum'=>array('a', 'b', 'c')))
      ->setReturnValue('getNullColumns', array('null_column'))
      ->listenTo('insert')
      ->listenTo('update')
      ->listenTo('delete')
      ->construct();
    $this->dataObject = new DataObject(array('id'=>$this->testId, 'integer'=>$this->testInteger, 'time'=>$this->testTime, 'name'=>'matthias', 'null_column'=>null, 'additional_column_string'=>'ADDCOLUMNSTTT', 'enum'=>'b'), $this->dao);
  }

  public function tearDown() {
    unset($this->dataObject);
  }

  public function testDataObjectId() {
    return $this->assertEqual($this->dataObject->id, $this->testId);
  }

  public function testDataObjectInteger() {
    return $this->assertIdentical($this->dataObject->integer, $this->testInteger);
  }

  public function testDataObjectTime() {
    return $this->assertIsA($this->dataObject->time, 'Date');
  }

  public function testDataObjectTimestamp() {
    return $this->assertEqual($this->dataObject->time->getTimestamp(), $this->testTime);
  }

  public function testDataObjectName() {
    return $this->assertEqual($this->dataObject->name, 'matthias');
  }

  public function testDataObjectNullColumn() {
    return $this->assertNull($this->dataObject->nullColumn);
  }

  public function testDataObjectEnum() {
    return $this->assertEqual($this->dataObject->enum, 'b');
  }

  public function testSettingInteger() {
    $int = $this->testInteger + 877;
    $this->dataObject->integer = $int;
    return $this->assertIdentical($this->dataObject->integer, $int);
  }

  public function testSettingTime() {
    $ts = 91919191;
    $this->dataObject->time = new Date($ts);
    return $this->assertIdentical($this->dataObject->time->getTimestamp(), $ts);
  }

  public function testSettingString() {
    $name = 'Some new name';
    $this->dataObject->name = $name;
    return $this->assertIdentical($this->dataObject->name, $name);
  }

  public function testSettingEnumA() {
    $this->dataObject->enum = 'a';
    return $this->assertIdentical($this->dataObject->enum, 'a');
  }
  public function testSettingEnumB() {
    $this->dataObject->enum = 'b';
    return $this->assertIdentical($this->dataObject->enum, 'b');
  }
  public function testSettingEnumC() {
    $this->dataObject->enum = 'c';
    return $this->assertIdentical($this->dataObject->enum, 'c');
  }
  public function testSettingWrongEnum() {
    $this->willWarn();
    $this->dataObject->enum = 'd';
    return $this->assertIdentical($this->dataObject->enum, 'a'); // Gets reset to the default value since null is not allowed
  }
  public function testSettingEmptyEnum() {
    $this->willWarn();
    $this->dataObject->enum = '';
    return $this->assertIdentical($this->dataObject->enum, 'a'); // Gets reset to the default value since null is not allowed
  }



  public function testSettingNull() {
    $this->dataObject->nullColumn = null;
    return $this->assertNull($this->dataObject->nullColumn);
  }

  public function testSettingNullAfterValueWasAssigned() {
    $this->dataObject->nullColumn = 'test';
    $this->dataObject->nullColumn = null;
    return $this->assertNull($this->dataObject->nullColumn);
  }
  public function testSettingNullToValue() {
    $string = 'Some Random String';
    $this->dataObject->nullColumn = $string;
    return $this->assertIdentical($this->dataObject->nullColumn, $string);
  }


  public function testSetter() {
    $name = 'Some new name';
    $this->dataObject->set('name', $name);
    return $this->assertIdentical($this->dataObject->name, $name);
  }

  public function testGetter() {
    $name = 'Some new name';
    $this->dataObject->set('name', $name);
    return $this->assertIdentical($this->dataObject->get('name'), $name);
  }

  public function testChainingOfSetter() {
    return $this->assertIdentical($this->dataObject->set('name', 'test'), $this->dataObject);
  }


  public function testUpdate() {
    $this->dataObject->save();
    return $this->assertCallCount($this->dao, 'update', 1);
  }

  public function testDelete() {
    $this->dataObject->delete();
    return $this->assertCallCount($this->dao, 'delete', 1);
  }


  public function testDirectSetter() {
    $name = 'Some new name 123';
    $this->dataObject->setDirectly('name', $name);
    return $this->assertIdentical($this->dataObject->name, $name);
  }

  public function testDirectGetter() {
    $name = 'Some new name 123456';
    $this->dataObject->set('name', $name);
    return $this->assertIdentical($this->dataObject->getDirectly('name'), $name);
  }

}



class DataObject_WithoutId_Test extends Snap_UnitTestCase {

  protected $dao;
  protected $dataObject;

  protected $testId = 18;
  protected $testInteger = 78;
  protected $testTime = 123124;

    public function setUp() {
      $this->dao = $this->mock('DaoInterface')
        ->setReturnValue('getColumnTypes', array('id'=>Dao::INT, 'integer'=>Dao::INT, 'time'=>Dao::TIMESTAMP, 'name'=>Dao::STRING, 'null_column'=>Dao::STRING))
        ->setReturnValue('getNullColumns', array('null_column'))
      ->listenTo('insert')
      ->listenTo('update')
        ->construct();

      $this->dataObject = new DataObject(array('id'=>null, 'integer'=>$this->testInteger, 'time'=>$this->testTime, 'name'=>'matthias', 'null_column'=>null), $this->dao);
    }

    public function tearDown() {
      unset($this->dataObject);
    }

  public function testInsert() {
    $this->dataObject->save();
    return $this->assertCallCount($this->dao, 'insert', 1);
  }

}



class DataObject_WithAdditionalValues_Test extends Snap_UnitTestCase {

  protected $additionalValue = 'ADSFSDKFLJ2r23kjl';

  public function setUp() {
      $this->dao = $this->mock('DaoInterface')
        ->setReturnValue('getColumnTypes',       array('id'=>Dao::INT))
        ->setReturnValue('getNullColumns',       array())
        ->setReturnValue('getAdditionalColumnTypes', array('additional_column_string'=>Dao::STRING, 'additional_column_int'=>Dao::INT))
      ->listenTo('insert')
      ->listenTo('update')
        ->construct();
      $this->dataObject = new DataObject(array('id'=>null, 'additional_column_string'=>$this->additionalValue), $this->dao);
  }

  public function tearDown() {
    unset($this->dataObject);
  }

  public function testGettingAdditionalValue() {
    return $this->assertEqual($this->dataObject->additionalColumnString, $this->additionalValue);
  }

  public function testGettingUnsetAdditionalValueIsNull() {
    return $this->assertNull($this->dataObject->additionalColumnInt);
  }

  public function testGettingAdditionalValueWithGetter() {
    return $this->assertEqual($this->dataObject->get('additionalColumnString'), $this->additionalValue);
  }

  public function testSettingAdditionalValueFailsButChains() {
    $this->willWarn();
    return $this->assertEqual($this->dataObject->set('additionalColumnString', 'test'), $this->dataObject);
  }

}


class DataObject_ChangedColumns_Test extends Snap_UnitTestCase {


  public function setUp() {
      $this->dao = $this->mock('DaoInterface')
        ->setReturnValue('getColumnTypes',       array('a'=>Dao::INT, 'b'=>Dao::STRING, 'c'=>Dao::BOOL))
        ->setReturnValue('getNullColumns',       array())
        ->setReturnValue('getAdditionalColumnTypes', array())
        ->construct();
      $this->dataObject = new DataObject(array('a'=>123, 'b'=>'test', 'c'=>true), $this->dao);
  }

  public function tearDown() {
    unset($this->dataObject);
  }

  public function testSettingNone() {
    return $this->assertEqual($this->dataObject->getChangedColumns(), array());
  }

  public function testSettingOne() {
    $this->dataObject->a = 321;
    return $this->assertEqual($this->dataObject->getChangedColumns(), array('a'=>true));
  }

  public function testSettingTwo() {
    $this->dataObject->a = 321;
    $this->dataObject->b = 'abc';
    return $this->assertEqual($this->dataObject->getChangedColumns(), array('a'=>true, 'b'=>true));
  }

  public function testSettingMultipleTimes() {
    $this->dataObject->a = 321;
    $this->dataObject->a = 456;
    $this->dataObject->a = 789;
    $this->dataObject->b = 'abc';
    $this->dataObject->b = 'ddd';
    $this->dataObject->c = true;
    $this->dataObject->c = false;
    $this->dataObject->c = true;
    return $this->assertEqual($this->dataObject->getChangedColumns(), array('a'=>true, 'b'=>true, 'c'=>true));
  }

  public function testSettingSameValue() {
    $this->dataObject->a = 123;
    return $this->assertEqual($this->dataObject->getChangedColumns(), array('a'=>true), 'Even though the value didnt change, it should be in the changedColumns since it has been set.');
  }


  public function testWithValues() {
    $this->dataObject->a = 321;
    $this->dataObject->b = 'abc';
    return $this->assertEqual($this->dataObject->getChangedValues(), array('a'=>321, 'b'=>'abc'));
  }

  public function testWithValueOverwrites() {
    $this->dataObject->a = 321;
    $this->dataObject->a = 654;
    $this->dataObject->c = true;
    $this->dataObject->c = false;
    return $this->assertEqual($this->dataObject->getChangedValues(), array('a'=>654, 'c'=>false));
  }




}



class DataObject_Load_Test extends Snap_UnitTestCase {


  public function setUp() {
      $this->dao = $this->mock('DaoInterface')
        ->setReturnValue('getColumnTypes',       array('a'=>Dao::INT, 'b'=>Dao::STRING, 'c'=>Dao::BOOL))
        ->setReturnValue('getNullColumns',       array())
        ->setReturnValue('getAdditionalColumnTypes', array())
        ->setReturnValue('getData',       array('a'=>321, 'b'=>'test2', 'c'=>false))
        ->listenTo('getData', array(new Snap_Object_Expectation('DataObject')))
        ->construct();
      $this->dataObject = new DataObject(array('a'=>0, 'b'=>'', 'c'=>false), $this->dao);
  }

  public function tearDown() {
    unset($this->dataObject);
  }

  public function testLoadingObjectCallsDao() {
    $this->dataObject->a = 7;
    $this->dataObject->load();
    return $this->assertCallCount($this->dao, 'getData', 1, array(new Snap_Object_Expectation('DataObject')));
  }

  public function testLoadedValuesA() {
    $this->dataObject->set('a', 321)->load();
    return $this->assertIdentical($this->dataObject->a, 321);
  }

  public function testLoadedValuesB() {
    $this->dataObject->set('a', 321)->load();
    return $this->assertIdentical($this->dataObject->b, 'test2');
  }

  public function testLoadedValuesC() {
    $this->dataObject->set('a', 321)->load();
    return $this->assertIdentical($this->dataObject->c, false);
  }

  public function testChangedValuesAreResetAfterLoading() {
    $this->dataObject->set('a', 321)->load();
    return $this->assertEqual($this->dataObject->getChangedValues(), array());
  }



}




?>