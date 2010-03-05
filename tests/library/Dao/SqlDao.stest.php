<?php


	require_once(dirname(dirname(__FILE__)) . '/setup.php');

	require_once(LIBRARY_ROOT_PATH . 'Dao/SqlDao.php');


	class RawTestDataObject {
		public $data;
		public function __construct($data) { $this->data = $data; }
	}

	class RawTestDao extends SqlDao {
		protected $columnTypes = array(
			'id'=>Dao::INT,
			'possibly_null'=>Dao::STRING,
			'string_not_null'=>Dao::STRING
		);
		protected $nullColumns = array('possibly_null');
		protected function getObjectFromPreparedData($data) {
			return new RawTestDataObject($data);
		}
		public function exportColumn($column) {  }
		public function exportTable($table = null) {  }
		public function exportString($text) {  }
		protected function getLastInsertId() {  }
		protected function createIterator($result) { return $result; }

	}
	class SqlDao_RawObjects_Test extends Snap_UnitTestCase {

		protected $dao;

	    public function setUp() {
			$this->dao = new RawTestDao(null);
    	}

	    public function tearDown() {
			unset($this->dao);
	    }

		public function testGettingRawObject() {
			return $this->assertIsA($this->dao->get(), 'RawTestDataObject');
		}

		public function testIdIsNullOnRawObject() {
			return $this->assertNull($this->dao->get()->data['id'], 'The id in a raw object has to be null.');
		}

		public function testPossiblyNullIsNullOnRawObject() {
			return $this->assertNull($this->dao->get()->data['possibly_null'], 'Values that can be null have to be null in raw objects.');
		}

		public function testStringNotNullIsNotNullOnRawObject() {
			return $this->assertIdentical($this->dao->get()->data['string_not_null'], '');
		}

	}

	
	require_once(LIBRARY_ROOT_PATH . 'Database/DatabaseInterface.php');

	class SqlDao_Returns_Test extends Snap_UnitTestCase {
		protected $dao;

	    public function setUp() {
			$database = $this->mock('DatabaseInterface')
				->setReturnValue('query', 'The Result')
				->construct();
				
			$this->dao = new RawTestDao($database);
    	}

	    public function tearDown() {
			unset($this->dao);
	    }

		public function testGetAll() {
			// This should just return the result because the RawTestDao returns the result instead of an iterator.
			return $this->assertIdentical($this->dao->getAll(), 'The Result');
		}

	}


?>