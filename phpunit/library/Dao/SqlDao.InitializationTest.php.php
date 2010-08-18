<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__) . '/../../setup.php';

require_once LIBRARY_PATH . 'Dao/SqlDao.php';



class SpecificSqlDao extends SqlDao {

  protected $db = 'DB';
  protected $resourceName = 'Resource Name';
  protected $attributes = array('attribute'=>'type');
  protected $nullAttributes = array('null', 'attributes');
  protected $defaultValueAttributes = array('default', 'value', 'attributes');


}



/**
 * Test class for SqlDao.
 */
class SqlDaoInitializationTest extends PHPUnit_Framework_TestCase {

  protected $sqlDao;


  public function setUp() {
  }

  /**
   * @expectedException DaoException
   */
  public function testThrowsExceptionIfNoResourceNameProvided() {
    new SqlDao(null);
  }

  /**
   * @expectedException DaoException
   */
  public function testThrowsExceptionIfNoAttributesProvided() {
    new SqlDao(null, 'resource name');
  }


  public function testAllAttributesCanBePassedInConstructor() {
    $db = 'Database';
    $resourceName = 'Resource Name';
    $attributes = array('attribute'=>'type');
    $nullAttributes = array('null', 'attributes');
    $defaultValueAttributes = array('default', 'value', 'attributes');

    $sqlDao= new SqlDao($db, $resourceName, $attributes, $nullAttributes, $defaultValueAttributes);

    $this->assertEquals($db, $sqlDao->getDb());
    $this->assertEquals($attributes, $sqlDao->getAttributes());
    $this->assertEquals($nullAttributes, $sqlDao->getNullAttributes());
    $this->assertEquals($defaultValueAttributes, $sqlDao->getDefaultValueAttributes());
  }


  public function testAllAttributesCanBeSetAsProperties() {
    $db = 'Database';
    $resourceName = 'Resource Name';
    $attributes = array('attribute'=>'type');
    $nullAttributes = array('null', 'attributes');
    $defaultValueAttributes = array('default', 'value', 'attributes');

    $sqlDao= new SpecificSqlDao($db);

    $this->assertEquals($db, $sqlDao->getDb());
    $this->assertEquals($attributes, $sqlDao->getAttributes());
    $this->assertEquals($nullAttributes, $sqlDao->getNullAttributes());
    $this->assertEquals($defaultValueAttributes, $sqlDao->getDefaultValueAttributes());
  }


}

