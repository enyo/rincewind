<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__) . '/../../../setup.php';

require_once LIBRARY_PATH . 'Dao/Sql/SqlDaoBase.php';



class SpecificSqlDaoBase extends SqlDaoBase {

  protected $db = 'DB';
  protected $resourceName = 'Resource Name';
  protected $attributes = array('attribute'=>'type');
  protected $nullAttributes = array('null', 'attributes');
  protected $defaultValueAttributes = array('default', 'value', 'attributes');


}



/**
 * Test class for SqlDaoBase.
 */
class SqlDaoBaseInitializationTest extends PHPUnit_Framework_TestCase {

  protected $sqlDao;


  public function setUp() {
  }

  /**
   * @expectedException DaoException
   */
  public function testThrowsExceptionIfNoResourceNameProvided() {
    new SqlDaoBase(null);
  }

  /**
   * @expectedException DaoException
   */
  public function testThrowsExceptionIfNoAttributesProvided() {
    new SqlDaoBase(null, 'resource name');
  }


  public function testAllAttributesCanBePassedInConstructor() {
    $db = 'Database';
    $resourceName = 'Resource Name';
    $attributes = array('attribute'=>'type');
    $nullAttributes = array('null', 'attributes');
    $defaultValueAttributes = array('default', 'value', 'attributes');

    $sqlDao= new SqlDaoBase($db, $resourceName, $attributes, $nullAttributes, $defaultValueAttributes);

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

    $sqlDao= new SpecificSqlDaoBase($db);

    $this->assertEquals($db, $sqlDao->getDb());
    $this->assertEquals($attributes, $sqlDao->getAttributes());
    $this->assertEquals($nullAttributes, $sqlDao->getNullAttributes());
    $this->assertEquals($defaultValueAttributes, $sqlDao->getDefaultValueAttributes());
  }


}

