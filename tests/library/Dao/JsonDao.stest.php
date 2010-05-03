<?php

require_once(dirname(dirname(__FILE__)) . '/setup.php');

require_once(LIBRARY_ROOT_PATH . 'Dao/Json/JsonDao.php');
require_once(LIBRARY_ROOT_PATH . 'FileFactory/DaoFileFactory.php');



/**
 * The test dao with all column types to be tested.
 */
class JsonTestDao extends JsonDao {
	protected $tableName = 'dao_test_table';
	
	protected $columnTypes = array(
		'id'=>Dao::INTEGER,
		'integer'=>Dao::INTEGER,
		'string'=>Dao::STRING,
		'timestamp'=>Dao::DATE_WITH_TIME,
		'float'=>Dao::FLOAT,
		'null_value'=>Dao::STRING,
		'default_value'=>Dao::INT
	);

	protected $nullColumns = array('null_value');

	protected $defaultValueColumns = array('default_value');

}


class JsonDao_FileFactory_Test extends Snap_UnitTestCase {

	protected $dao;
	protected $fileFactory;

  public function setUp() {
    	$this->fileFactory = $this->mock('DaoFileFactory')
			// ->setReturnValue('getColumnTypes', array('id'=>Dao::INT, 'integer'=>Dao::INT, 'time'=>Dao::TIMESTAMP, 'name'=>Dao::STRING, 'null_column'=>Dao::STRING, 'enum'=>array('a', 'b', 'c')))
			// ->setReturnValue('getNullColumns', array('null_column'))
			->setReturnValue('insert', 4)
			->setReturnValue('view', '{"id":4,"integer":5,"string":"STRING TO TEST","timestamp":23423423,"float":23.23,"null_value":null,"default_value":1234}')
			->setReturnValue('viewList', '[{"id":4,"integer":5,"string":"bla","timestamp":23423423,"float":23.23,"null_value":null,"default_value":1234},{"id":5,"integer":5,"string":"bla","timestamp":23423423,"float":23.23,"null_value":null,"default_value":1234}]')
			->listenTo('insert', array(new Snap_Equals_Expectation('dao_test_table')))
			->listenTo('update', array(new Snap_Equals_Expectation('dao_test_table')))
			->listenTo('delete', array(new Snap_Equals_Expectation('dao_test_table'), new Snap_Equals_Expectation(4)))
			->listenTo('viewList', array(new Snap_Equals_Expectation('dao_test_table'), new Snap_Equals_Expectation(array('integer'=>132456))))
			->listenTo('view', array(new Snap_Equals_Expectation('dao_test_table'), new Snap_Equals_Expectation(array('id'=>4))))
			->listenTo('view', array(new Snap_Equals_Expectation('dao_test_table'), new Snap_Equals_Expectation(array('string'=>'TEST', 'integer'=>17))))
			->construct();
		$this->dao = new JsonTestDao($this->fileFactory);
	}

	public function tearDown() {
		unset($this->dao);
	}

	public function testViewCount() {
		$this->dao->getById(4);
		return $this->assertCallCount($this->fileFactory, 'view', 1, array(new Snap_Equals_Expectation('dao_test_table'), new Snap_Equals_Expectation(array('id'=>4))));
	}

	public function testViewStringAndIntegerCount() {
		$this->dao->get(array('string'=>'TEST', 'integer'=>17));
		return $this->assertCallCount($this->fileFactory, 'view', 1, array(new Snap_Equals_Expectation('dao_test_table'), new Snap_Equals_Expectation(array('string'=>'TEST', 'integer'=>17))));
	}

	public function testViewListCount() {
		$this->dao->getIterator(array('integer'=>132456));
		return $this->assertCallCount($this->fileFactory, 'viewList', 1, array(new Snap_Equals_Expectation('dao_test_table'), new Snap_Equals_Expectation(array('integer'=>132456))));
	}

	public function testDeleteCount() {
		$this->dao->deleteById(4);
		return $this->assertCallCount($this->fileFactory, 'delete', 1, array(new Snap_Equals_Expectation('dao_test_table'), new Snap_Equals_Expectation(4)));
	}

	public function testUpdateCount() {
		$object = $this->dao->getById(4);
		$object->set('string', 'Name')->save();
		return $this->assertCallCount($this->fileFactory, 'update', 1, array(new Snap_Equals_Expectation('dao_test_table')));
	}

	public function testInsertCount() {
		$object = $this->dao->getById(4);
		$object->set('string', 'Name')->save();
		return $this->assertCallCount($this->fileFactory, 'update', 1, array(new Snap_Equals_Expectation('dao_test_table')));
	}

	public function testIteratorLengthCount() {
		$iterator = $this->dao->getIterator(array('integer'=>132456));
		return $this->assertEqual($iterator->count(), 2, 'Length should have been one.');
	}

	public function testObject() {
		$object = $this->dao->getById(4);
		return $this->assertEqual($object->string, 'STRING TO TEST', 'String of the result is not correct.');
	}

}


?>