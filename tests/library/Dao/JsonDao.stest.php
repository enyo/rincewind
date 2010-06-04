<?php

require_once(dirname(dirname(__FILE__)) . '/setup.php');

require_once(LIBRARY_ROOT_PATH . 'Dao/Json/JsonDao.php');
require_once(LIBRARY_ROOT_PATH . 'DataSource/FileDataSource.php');



/**
 * The test dao with all column types to be tested.
 */
class JsonTestDao extends JsonDao {
  protected $resourceName = 'dao_test_resource';
  
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
      $this->fileFactory = $this->mock('FileDataSource')
      // ->setReturnValue('getColumnTypes', array('id'=>Dao::INT, 'integer'=>Dao::INT, 'time'=>Dao::TIMESTAMP, 'name'=>Dao::STRING, 'null_column'=>Dao::STRING, 'enum'=>array('a', 'b', 'c')))
      // ->setReturnValue('getNullColumns', array('null_column'))
      ->setReturnValue('insert', 4)
      ->setReturnValue('view', '{"id":4,"integer":5,"string":"STRING TO TEST","timestamp":23423423,"float":23.23,"null_value":null,"default_value":1234}')
      ->setReturnValue('viewList', '[{"id":4,"integer":5,"string":"bla","timestamp":23423423,"float":23.23,"null_value":null,"default_value":1234},{"id":5,"integer":5,"string":"bla","timestamp":23423423,"float":23.23,"null_value":null,"default_value":1234}]')
      ->listenTo('insert', array(new Snap_Equals_Expectation('dao_test_resource')))
      ->listenTo('update', array(new Snap_Equals_Expectation('dao_test_resource')))
      ->listenTo('delete', array(new Snap_Equals_Expectation('dao_test_resource'), new Snap_Equals_Expectation(4)))
      ->listenTo('viewList', array(new Snap_Equals_Expectation('dao_test_resource'), new Snap_Equals_Expectation(array('integer'=>132456))))
      ->listenTo('view', array(new Snap_Equals_Expectation('dao_test_resource'), new Snap_Equals_Expectation(array('id'=>4))))
      ->listenTo('view', array(new Snap_Equals_Expectation('dao_test_resource'), new Snap_Equals_Expectation(array('string'=>'TEST', 'integer'=>17))))
      ->construct('x', 'x');
    $this->dao = new JsonTestDao($this->fileFactory);
  }

  public function tearDown() {
    unset($this->dao);
  }

  public function testViewCount() {
    $this->dao->getById(4);
    return $this->assertCallCount($this->fileFactory, 'view', 1, array(new Snap_Equals_Expectation('dao_test_resource'), new Snap_Equals_Expectation(array('id'=>4))));
  }

  public function testViewStringAndIntegerCount() {
    $this->dao->get(array('string'=>'TEST', 'integer'=>17));
    return $this->assertCallCount($this->fileFactory, 'view', 1, array(new Snap_Equals_Expectation('dao_test_resource'), new Snap_Equals_Expectation(array('string'=>'TEST', 'integer'=>17))));
  }

  public function testViewListCount() {
    $this->dao->getIterator(array('integer'=>132456));
    return $this->assertCallCount($this->fileFactory, 'viewList', 1, array(new Snap_Equals_Expectation('dao_test_resource'), new Snap_Equals_Expectation(array('integer'=>132456))));
  }

  public function testDeleteCount() {
    $this->dao->deleteById(4);
    return $this->assertCallCount($this->fileFactory, 'delete', 1, array(new Snap_Equals_Expectation('dao_test_resource'), new Snap_Equals_Expectation(4)));
  }

  public function testUpdateCount() {
    $object = $this->dao->getById(4);
    $object->set('string', 'Name')->save();
    return $this->assertCallCount($this->fileFactory, 'update', 1, array(new Snap_Equals_Expectation('dao_test_resource')));
  }

  public function testInsertCount() {
    $object = $this->dao->getById(4);
    $object->set('string', 'Name')->save();
    return $this->assertCallCount($this->fileFactory, 'update', 1, array(new Snap_Equals_Expectation('dao_test_resource')));
  }

  public function testIteratorLengthCount() {
    $iterator = $this->dao->getIterator(array('integer'=>132456));
    return $this->assertEqual($iterator->count(), 2, 'Length should have been two.');
  }

  public function testObject() {
    $object = $this->dao->getById(4);
    return $this->assertEqual($object->string, 'STRING TO TEST', 'String of the result is not correct.');
  }

}



class AddressDao extends JsonDao {
  protected $resourceName = 'dao_address_resource';
  
  protected $columnTypes = array(
    'id'=>Dao::INTEGER,
    'city_id'=>Dao::INTEGER
  );
  protected function setupReferences() {
    $this->addReference('city', 'CityDao', 'city_id', 'id');
  }
}
class CityDao extends JsonDao {
  protected $resourceName = 'dao_city_resource';
  
  protected $columnTypes = array(
    'id'=>Dao::INTEGER,
    'name'=>Dao::STRING
  );
}

class JsonReferencesDao extends JsonDao {
  protected $resourceName = 'dao_user_resource';
  
  protected $columnTypes = array(
    'id'=>Dao::INTEGER,
    'address_id'=>Dao::INTEGER,
    'address_ids'=>Dao::SEQUENCE
  );

  protected function setupReferences() {
    $this->addReference('address', 'AddressDao', 'address_id', 'id');
    $this->addToManyReference('addresses', 'AddressDao', 'address_ids', 'id');
  }
}


class JsonDao_References_Test extends Snap_UnitTestCase {

  protected $dao;
  protected $fileFactory;

  public function setUp() {
    $this->fileFactory = $this->mock('FileDataSource')
      ->setReturnValue('view', '{"id":4,"address_id":101,"address_ids":[1,2,3]}', array(new Snap_Identical_Expectation('dao_user_resource')))
      ->setReturnValue('view', '{"id":777,"city_id":5}', array(new Snap_Identical_Expectation('dao_address_resource'), new Snap_Equals_Expectation(array('id'=>101))))
      ->setReturnValue('view', '{"id":999,"name":"Vienna"}', array(new Snap_Identical_Expectation('dao_city_resource'), new Snap_Equals_Expectation(array('id'=>5))))
      ->listenTo('view')
      ->construct('x', 'x');
    $this->dao = new JsonReferencesDao($this->fileFactory);
  }

  public function tearDown() { }

  public function testChaining() {
    $user = $this->dao->get()->set('addressId', 101);
    $city = $user->address->city;
    return $this->assertIdentical($city->name, 'Vienna');
  }

  public function testViewCallCount() {
    $user = $this->dao->getById(987);
    $city = $user->address->city;
    return $this->assertCallCount($this->fileFactory, 'view', 3, array(), 'Should only call view once for the user.');
  }

}

class JsonDao_ReferencesAtOnce_Test extends Snap_UnitTestCase {

  protected $dao;
  protected $fileFactory;

  public function setUp() {
    $this->fileFactory = $this->mock('FileDataSource')
      ->setReturnValue('view', '{"id":4,"address_id":2,"address_ids":[1,2,3], "address": { "id": 4, "city_id": 5, "city": { "id": 9, "name": "Paris" } }}', array(new Snap_Identical_Expectation('dao_user_resource')))
      ->listenTo('view')
      ->construct('x', 'x');
    $this->dao = new JsonReferencesDao($this->fileFactory);
  }

  public function tearDown() { }

  public function testChaining() {
    $user = $this->dao->getById(987);
    $city = $user->address->city;
    return $this->assertIdentical($city->name, 'Paris');
  }

  public function testViewCallCount() {
    $user = $this->dao->getById(987);
    $city = $user->address->city;
    return $this->assertCallCount($this->fileFactory, 'view', 1, array(), 'Should only call view once for the user.');
  }

}
?>