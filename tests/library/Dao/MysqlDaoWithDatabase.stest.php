<?php


require_once(dirname(dirname(__FILE__)) . '/setup.php');

require_once(LIBRARY_ROOT_PATH . 'Database/Mysql.php');
require_once(LIBRARY_ROOT_PATH . 'Dao/Mysql/MysqlDao.php');



/**
 * The test dao with all attributes to be tested.
 */
class MysqlTestDao extends MysqlDao {
  protected $resourceName = 'dao_test';
  
  protected $attributes = array(
    'id'=>Dao::INTEGER,
    'integer'=>Dao::INTEGER,
    'string'=>Dao::STRING,
    'timestamp'=>Dao::DATE_WITH_TIME,
    'float'=>Dao::FLOAT,
    'nullValue'=>Dao::STRING,
    'defaultValue'=>Dao::INT
  );

  protected $nullAttributes = array('nullValue');

  protected $defaultValueAttributes = array('defaultValue');

}






require_once(dirname(__FILE__) . '/AbstractSqlDaoWithDatabaseTests.php');


class MysqlDaoWithDatabaseTest extends AbstractSqlDaoWithDatabaseTest {

  protected function getDatabaseConnection() { return new Mysql(CONF_MYSQL_DBNAME, CONF_MYSQL_USERNAME, CONF_MYSQL_HOST, CONF_MYSQL_PORT, CONF_MYSQL_PASSWORD); }
  protected function getDao() { return new MysqlTestDao($this->db); }
  protected $iteratorClassName = 'MysqlResultIterator';

}





class AddressDao extends MysqlDao {
  protected $resourceName = 'addresses';
  protected $attributes = array('id'=>Dao::INT, 'cityId'=>Dao::INT);
  protected function setupReferences() {
    $this->addReference('city', new DaoToOneReference('CityDao', 'cityId'));
  }
}
class CityDao extends MysqlDao {
  protected $resourceName = 'cities';
  protected $attributes = array('id'=>Dao::INT, 'countryId'=>Dao::INT);
  protected function setupReferences() {
    $this->addReference('country', new DaoToOneReference('CountryDao', 'countryId'));
  }
}
class CountryDao extends MysqlDao {
  protected $resourceName = 'countries';
  protected $attributes = array('id'=>Dao::INT, 'name'=>Dao::STRING);
}


class MysqlDao_References_Test extends AbstractSqlDaoWithDatabase_References_Test {

  protected function getDatabaseConnection() { return new Mysql(CONF_MYSQL_DBNAME, CONF_MYSQL_USERNAME, CONF_MYSQL_HOST, CONF_MYSQL_PORT, CONF_MYSQL_PASSWORD); }
  protected function getAddressDao() { return new AddressDao($this->db); }
  protected function getCityDao() { return new CityDao($this->db); }
  protected function getCountryDao() { return new CountryDao($this->db); }

}





?>
