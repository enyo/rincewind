<?php


require_once(dirname(dirname(__FILE__)) . '/setup.php');

require_once(LIBRARY_ROOT_PATH . 'Dao/SqlDao.php');


class RawTestRecord {
  public $data;
  public function __construct($data) { $this->data = $data; }
}

class RawTestDao extends SqlDao {
  protected $attributes = array(
    'id'=>Dao::INT,
    'possiblyNull'=>Dao::STRING,
    'stringNotNull'=>Dao::STRING,
    'enum'=>array('enum_a', 'enum_b', 'enum_c')
  );
  protected $additionalAttributes = array(
    'additionalAttribute'=>Dao::STRING,
  );

  protected $nullAttributes = array('possiblyNull');
  protected function getRecordFromPreparedData($data) {
    return new RawTestRecord($data);
  }
  public function exportAttributeName($attribute) {  }
  public function exportResourceName($resource = null) {  }
  public function exportString($text) {  }
  protected function getLastInsertId() {  }
  protected function createIterator($result) { return $result; }

}
class SqlDao_RawRecords_Test extends Snap_UnitTestCase {

  protected $dao;

    public function setUp() {
    $this->dao = new RawTestDao(null);
    }

    public function tearDown() {
    unset($this->dao);
    }

  public function testGettingRawRecord() {
    return $this->assertIsA($this->dao->get(), 'RawTestRecord');
  }

  public function testIdIsNullOnRawRecord() {
    return $this->assertNull($this->dao->get()->data['id'], 'The id in a raw record has to be null.');
  }

  public function testPossiblyNullIsNullOnRawRecord() {
    return $this->assertNull($this->dao->get()->data['possiblyNull'], 'Values that can be null have to be null in raw records.');
  }

  public function testStringNotNullIsNotNullOnRawRecord() {
    return $this->assertIdentical($this->dao->get()->data['stringNotNull'], '');
  }

  public function testEnumIsFirstValueInRecord() {
    return $this->assertEqual($this->dao->get()->data['enum'], 'enum_a');
  }

}



class SqlDao_Getters_Test extends Snap_UnitTestCase {
  protected $dao;

  public function setUp() {
    $this->dao = new RawTestDao(null);
  }

  public function tearDown() {
    unset($this->dao);
  }

  public function testGetAttributes() {
    return $this->assertEqual($this->dao->getAttributes(), array('id'=>Dao::INT, 'possiblyNull'=>Dao::STRING, 'stringNotNull'=>Dao::STRING, 'enum'=>array('enum_a', 'enum_b', 'enum_c')));
  }

  public function testGetNullAttributes() {
    return $this->assertEqual($this->dao->getNullAttributes(), array('possiblyNull'));
  }

  public function testGetAdditionalAttributes() {
    return $this->assertEqual($this->dao->getAdditionalAttributes(), array('additionalAttribute'=>Dao::STRING));
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


require_once(LIBRARY_ROOT_PATH . 'DatabaseResult/DatabaseResultInterface.php');

class RawTestDao2 extends SqlDao {
  protected $resourceName = 'test_resource_name';
  protected $attributes = array(
    'id'=>Dao::INT,
    'name'=>Dao::STRING,
    'isAdmin'=>Dao::BOOL
  );
  protected function getRecordFromPreparedData($data) {
    return new RawTestRecord($data);
  }
  public function exportAttributeName($attributeName) { return $attributeName; }
  public function exportResourceName($resource = null) { return $resource; }
  public function exportString($text) { return $text; }
  protected function getLastInsertId() {  }
  protected function createIterator($result) { return $result; }

}

class SqlDao_Queries_Test extends Snap_UnitTestCase {

  protected $dao;

  public function setUp() {
    $this->result = $this->mock('DatabaseResultInterface')
      ->setReturnValue('numRows', 1)
      ->setReturnValue('fetchArray', array('id'=>1, 'name'=>'test', 'is_admin'=>true))
      ->construct(null);
    $this->db = $this->mock('DatabaseInterface')
      ->setReturnValue('escapeTable', 'ESCAPED_RESOURCE')
      ->setReturnValue('escapeString', 'ESCAPED_STRING')
      ->setReturnValue('escapeColumn', 'ESCAPED_COLUMN')
      ->setReturnValue('query', $this->result)
      ->listenTo('query', array(new Snap_Identical_Expectation('select * from test_resource_name where id=4  limit 1')))
      ->listenTo('query', array(new Snap_Identical_Expectation('select * from test_resource_name where id=7  limit 1')))
      ->listenTo('query', array(new Snap_Identical_Expectation('select * from test_resource_name where id=11 and name=TEST  limit 1')))
      ->construct(null);
    $this->dao = new RawTestDao2($this->db);
  }

  public function tearDown() {}

  public function testQuery() {
    $this->dao->getById(4);
    return $this->assertCallCount($this->db, 'query', 1, array(new Snap_Identical_Expectation('select * from test_resource_name where id=4  limit 1')));
  }

  public function testQueryWithRecordAsAttribute() {
    $record = new Record(array(), $this->dao);
    $record->id = 7;
    $this->dao->get($record);
    return $this->assertCallCount($this->db, 'query', 1, array(new Snap_Identical_Expectation('select * from test_resource_name where id=7  limit 1')));
  }

  public function testQueryWithRecordAsAttributes() {
    $record = new Record(array(), $this->dao);
    $record->id = 11;
    $record->name = 'TEST';
    $this->dao->get($record);
    return $this->assertCallCount($this->db, 'query', 1, array(new Snap_Identical_Expectation('select * from test_resource_name where id=11 and name=TEST  limit 1')));
  }

}

?>
