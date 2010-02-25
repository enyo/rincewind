<?php

	require_once(dirname(dirname(__FILE__)) . '/setup.php');

	require_once(LIBRARY_ROOT_PATH . 'DataObject/DataObject.php');
	require_once(LIBRARY_ROOT_PATH . 'Dao/DaoInterface.php');


	class DataObjectTest extends Snap_UnitTestCase {

		protected $dao;
		protected $dataObject;

	    public function setUp() {
	    	$this->dao = $this->mock('DaoInterface')
	    		->setReturnValue('getColumnTypes', array('id'=>Dao::INT, 'time'=>Dao::TIMESTAMP, 'name'=>Dao::STRING, 'null_column'=>Dao::STRING))
	    		->setReturnValue('getNullColumns', array('null_column'))
	    		->construct();

	    	$this->dataObject = new DataObject(array('id'=>1, 'time'=>123123, 'name'=>'matthias', 'null_column'=>null), $this->dao);
    	}

	    public function tearDown() {
	    	unset($this->dataObject);
	    }

		public function testDataObjectId() {
			return $this->assertEqual($this->dataObject->id, 1);
		}

		public function testDataObjectTime() {
			return $this->assertIsA($this->dataObject->time, 'Date');
		}

		public function testDataObjectTimestamp() {
			return $this->assertEqual($this->dataObject->time->getTimestamp(), 123123);
		}

		public function testDataObjectName() {
			return $this->assertEqual($this->dataObject->name, 'matthias');
		}

		public function testDataObjectNullColumn() {
			return $this->assertNull($this->dataObject->nullColumn);
		}

		public function testSettingTime() {
			$this->dataObject->time = new Date(91919191);
			return $this->assertEqual($this->dataObject->time->getTimestamp(), 91919191);
		}


	}

?>