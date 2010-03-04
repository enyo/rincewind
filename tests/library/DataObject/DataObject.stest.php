<?php

	require_once(dirname(dirname(__FILE__)) . '/setup.php');

	require_once(LIBRARY_ROOT_PATH . 'Dao/Dao.php');


	class DataObjectWithIdTest extends Snap_UnitTestCase {

		protected $dao;
		protected $dataObject;

		protected $testId = 17;
		protected $testInteger = 77;
		protected $testTime = 123123;

	    public function setUp() {
	    	$this->dao = $this->mock('DaoInterface')
	    		->setReturnValue('getColumnTypes', array('id'=>Dao::INT, 'integer'=>Dao::INT, 'time'=>Dao::TIMESTAMP, 'name'=>Dao::STRING, 'null_column'=>Dao::STRING))
	    		->setReturnValue('getNullColumns', array('null_column'))
				->listenTo('insert')
				->listenTo('update')
				->listenTo('delete')
	    		->construct();

	    	$this->dataObject = new DataObject(array('id'=>$this->testId, 'integer'=>$this->testInteger, 'time'=>$this->testTime, 'name'=>'matthias', 'null_column'=>null), $this->dao);
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



	class DataObjectWithoutIdTest extends Snap_UnitTestCase {

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


?>