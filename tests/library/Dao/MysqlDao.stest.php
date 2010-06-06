<?php

require_once(dirname(dirname(__FILE__)) . '/setup.php');


require_once(LIBRARY_ROOT_PATH . 'Dao/Mysql/MysqlDao.php');
require_once(LIBRARY_ROOT_PATH . 'Database/Mysql.php');



/**
 * The test dao with all attributes to be tested.
 */
class MysqlTestDao extends MysqlDao {
  protected $resourceName = 'test_resource_name';
  protected $attributes = array('id'=>Dao::INT, 'name'=>Dao::STRING, 'is_admin'=>Dao::BOOL);
}


class MysqlDao_Basic_Test extends Snap_UnitTestCase {

  protected $dao;

  public function setUp() {
    $this->db = $this->mock('Mysql')
      ->setReturnValue('escapeTable', 'ESCAPED_RESOURCE')
      ->setReturnValue('escapeString', 'ESCAPED_STRING')
      ->setReturnValue('escapeColumn', 'ESCAPED_COLUMN')
      ->listenTo('escapeTable', array(new Snap_Identical_Expectation('test_resource_name')))
      ->listenTo('escapeTable', array(new Snap_Identical_Expectation('some_other_resource')))
      ->listenTo('escapeString', array(new Snap_Identical_Expectation('some_string')))
      ->listenTo('escapeColumn', array(new Snap_Identical_Expectation('some_column')))
      ->construct(null);
    $this->dao = new MysqlTestDao($this->db);
    
  }

  public function tearDown() {}

  public function testexportResourceName() {
    return $this->assertIdentical($this->dao->exportResourceName(), '`ESCAPED_RESOURCE`');
  }

  public function testDefaultTableIsUsed() {
    $this->dao->exportResourceName();
    return $this->assertCallCount($this->db, 'escapeTable', 1, array(new Snap_Identical_Expectation('test_resource_name')));
  }

  public function testSomeOtherTableName() {
    $this->dao->exportResourceName('some_other_resource');
    return $this->assertCallCount($this->db, 'escapeTable', 1, array(new Snap_Identical_Expectation('some_other_resource')));
  }

  public function testExportString() {
    return $this->assertIdentical($this->dao->exportString('some_string'), "'ESCAPED_STRING'");
  }

  public function testDatabaseEscapesString() {
    $this->dao->exportString('some_string');
    return $this->assertCallCount($this->db, 'escapeString', 1, array(new Snap_Identical_Expectation('some_string')));
  }


  public function testExportAttribute() {
    return $this->assertIdentical($this->dao->exportAttributeName('some_column'), "`ESCAPED_COLUMN`");
  }

  public function testDatabaseEscapesAttribute() {
    $this->dao->exportAttributeName('some_column');
    return $this->assertCallCount($this->db, 'escapeColumn', 1, array(new Snap_Identical_Expectation('some_column')));
  }

}



?>