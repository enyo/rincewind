<?php

abstract class AbstractSqlDaoWithDatabaseTest extends Snap_UnitTestCase {

  abstract protected function getDatabaseConnection();
  abstract protected function getDao();

  protected $db;
  protected $dao;
  protected $iteratorClassName;
  protected $resourceName = 'dao_test';

  protected $defaultValue = 7;

  public function setUp() {
    $this->db = $this->getDatabaseConnection();
    $this->dao = $this->getDao();
    $this->db->query(sprintf("create temporary table `%s` (`id` int primary key, `integer` int not null, `string` varchar(100) not null, `timestamp` timestamp not null, `float` float not null, `null_value` varchar(100), `default_value` int default %d not null)", $this->resourceName, $this->defaultValue));
  }

  public function tearDown() {
    unset($this->db);
    unset($this->dao);
  }

  public function testNullValuesArentInserted() {
    $object = $this->dao->getRawObject();
    $object->save();
    $data = $this->dao->getAll()->current();
      return $this->assertNull($data->nullValue);
  }

  public function testDefaultValuesAreNullInRawObjects() {
    $object = $this->dao->getRawObject();
      return $this->assertNull($object->defaultValue);
  }

  public function testDefaultValuesAreSetByTheDatabase() {
    $object = $this->dao->getRawObject();
    $object->save();
      return $this->assertEqual($object->defaultValue, $this->defaultValue);
  }

  public function testObjectReturnedFromInsertIsIdentical() {
    $object = $this->dao->getRawObject();
    $object2 = $this->dao->insert($object);
      return $this->assertIdentical($object, $object2, "The dao should return the same object after insert, not create a new one.");
  }

  public function testObjectReturnedFromUpdateIsIdentical() {
    $object = $this->dao->getRawObject();
    $object = $this->dao->insert($object);
    $object->string = 'TEST';
    $object2 = $this->dao->update($object);
    return $this->assertIdentical($object, $object2, "The dao should return the same object after update, not create a new one.");
  }

  public function testGetAllReturnsIterator() {
    return $this->assertIsA($this->dao->getAll(), $this->iteratorClassName);
  }

  // Throws an exception if it's not working
  public function testSavingAndGetting() {
    $o = $this->dao->get()->set('id', 99);
    $this->dao->insert($o);
    $this->dao->getById(99);
    return $this->assertTrue(true);
  }

  // Throws an exception if it's not working
  public function testSavingAndGettingChained() {
    $this->dao->get()->set('id', 77)->save();
    $this->dao->getById(77);
    return $this->assertTrue(true);
  }

  public function testGetAllReturnsCorrectNumberIterator() {
    $o = $this->dao->get()->set('id', 1)->save();
    $this->dao->get()->set('id', 2)->save();
    $this->dao->get()->set('id', 3)->save();
    return $this->assertIdentical($this->dao->getAll()->count(), 3);
  }

  public function testGetThrowsException() {
    $this->willThrow('DaoNotFoundException');
    $this->dao->get(array('id'=>5));
  }

  public function testFindReturnsNull() {
    return $this->assertNull($this->dao->find(array('id'=>5)));
  }

  public function testLoadingRecords() {
    $this->dao->get()->set('id', 5)->set('string', 'testest')->save();
    $o = $this->dao->get()->set('id', 5)->set('string', 'testest')->load();
    return $this->assertIsA($o, 'DataObject');
  }

  public function testLoadingRecordsAndVerifyValues() {
    $this->dao->get()->set('id', 5)->set('string', 'testest')->save();
    $o = $this->dao->get()->set('id', 5)->set('string', 'testest')->load();
    return $this->assertIdentical($o->string, 'testest');
  }

  public function testLoadingRecordsThatDontExist() {
    $this->willThrow('DaoNotFoundException');
    $this->dao->get()->set('id', 5)->set('string', 'testest234')->save();
    $o = $this->dao->get()->set('id', 5)->set('string', 'testest')->load();
  }

}



abstract class AbstractSqlDaoWithDatabase_References_Test extends Snap_UnitTestCase {

  abstract protected function getDatabaseConnection();
  abstract protected function getAddressDao();
  abstract protected function getCityDao();
  abstract protected function getCountryDao();

  protected $db;
  protected $dao;
  protected $iteratorClassName;
  protected $addressesTable = 'addresses';
  protected $citiesTable    = 'cities';
  protected $countriesTable = 'countries';

  protected $address;

  public function setUp() {
    $this->db = $this->getDatabaseConnection();
    $this->addressDao = $this->getAddressDao();
    $this->cityDao    = $this->getCityDao();
    $this->countryDao = $this->getCountryDao();
    $this->db->query(sprintf("create temporary table `%s` (`id` int primary key, `city_id` int not null)", $this->addressesTable));
    $this->db->query(sprintf("create temporary table `%s` (`id` int primary key, `country_id` int not null)", $this->citiesTable));
    $this->db->query(sprintf("create temporary table `%s` (`id` int primary key, `name` varchar(100) not null)", $this->countriesTable));

    $country = $this->countryDao->get()->set('name', 'Australia')->save();
    $city = $this->cityDao->get()->set('countryId', $country->id)->save();
    $this->address = $this->addressDao->get()->set('cityId', $city->id)->save();
  }

  public function tearDown() { }

  public function testChainedReferences() {
    $name = $this->address->city->country->name;
    return $this->assertIdentical($name, 'Australia');
  }
}


?>