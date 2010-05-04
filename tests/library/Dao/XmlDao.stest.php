<?php

require_once(dirname(dirname(__FILE__)) . '/setup.php');

require_once(LIBRARY_ROOT_PATH . 'Dao/Xml/XmlDao.php');
require_once(LIBRARY_ROOT_PATH . 'DataSource/FileDataSource.php');



/**
 * The test dao with all column types to be tested.
 */
class XmlTestDao extends XmlDao {
	protected $tableName = 'products';
	
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


class XmlDao_FileFactory_Test extends Snap_UnitTestCase {

	protected $dao;
	protected $fileFactory;

  public function setUp() {
    	$this->fileFactory = $this->mock('FileDataSource')
			// ->setReturnValue('getColumnTypes', array('id'=>Dao::INT, 'integer'=>Dao::INT, 'time'=>Dao::TIMESTAMP, 'name'=>Dao::STRING, 'null_column'=>Dao::STRING, 'enum'=>array('a', 'b', 'c')))
			// ->setReturnValue('getNullColumns', array('null_column'))
			->setReturnValue('insert', 4)
			->setReturnValue('view', '<entries><productsEntry><id>4</id><integer>123123</integer><string>Some string</string><timestamp>999888</timestamp><float>34.4</float><default_value>111</default_value></productsEntry></entries>')
			->setReturnValue('viewList', '<entries><productsEntry><id>4</id><integer>123123</integer><string>Some string</string><timestamp>999888</timestamp><float>34.4</float><default_value>111</default_value></productsEntry><productsEntry><id>5</id><integer>321321</integer><string>Some other string</string><timestamp>999888</timestamp><float>34.4</float><default_value>111</default_value></productsEntry></entries>')
			->listenTo('insert', array(new Snap_Equals_Expectation('products')))
			->listenTo('update', array(new Snap_Equals_Expectation('products')))
			->listenTo('delete', array(new Snap_Equals_Expectation('products'), new Snap_Equals_Expectation(4)))
			->listenTo('viewList', array(new Snap_Equals_Expectation('products'), new Snap_Equals_Expectation(array('integer'=>132456))))
			->listenTo('view', array(new Snap_Equals_Expectation('products'), new Snap_Equals_Expectation(array('id'=>4))))
			->listenTo('view', array(new Snap_Equals_Expectation('products'), new Snap_Equals_Expectation(array('string'=>'TEST', 'integer'=>17))))
			->construct('x', 'x');
		$this->dao = new XmlTestDao($this->fileFactory);
	}

	public function tearDown() {
		unset($this->dao);
	}

	public function testViewCount() {
		$this->dao->getById(4);
		return $this->assertCallCount($this->fileFactory, 'view', 1, array(new Snap_Equals_Expectation('products'), new Snap_Equals_Expectation(array('id'=>4))));
	}

	public function testViewStringAndIntegerCount() {
		$this->dao->get(array('string'=>'TEST', 'integer'=>17));
		return $this->assertCallCount($this->fileFactory, 'view', 1, array(new Snap_Equals_Expectation('products'), new Snap_Equals_Expectation(array('string'=>'TEST', 'integer'=>17))));
	}

	public function testViewListCount() {
		$this->dao->getIterator(array('integer'=>132456));
		return $this->assertCallCount($this->fileFactory, 'viewList', 1, array(new Snap_Equals_Expectation('products'), new Snap_Equals_Expectation(array('integer'=>132456))));
	}

	public function testDeleteCount() {
		$this->dao->deleteById(4);
		return $this->assertCallCount($this->fileFactory, 'delete', 1, array(new Snap_Equals_Expectation('products'), new Snap_Equals_Expectation(4)));
	}

	public function testUpdateCount() {
		$object = $this->dao->getById(4);
		$object->set('string', 'Name')->save();
		return $this->assertCallCount($this->fileFactory, 'update', 1, array(new Snap_Equals_Expectation('products')));
	}

	public function testInsertCount() {
		$object = $this->dao->getById(4);
		$object->set('string', 'Name')->save();
		return $this->assertCallCount($this->fileFactory, 'update', 1, array(new Snap_Equals_Expectation('products')));
	}

	public function testIteratorLengthCount() {
		$iterator = $this->dao->getIterator(array('integer'=>123123));
		return $this->assertEqual($iterator->count(), 2, 'Length should have been two.');
	}

	public function testObject() {
		$object = $this->dao->getById(4);
		return $this->assertEqual($object->string, 'Some string', 'String of the result is not correct.');
	}

}


?>