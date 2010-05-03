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




?>