<?php

require_once(dirname(dirname(__FILE__)) . '/setup.php');


require_once(LIBRARY_ROOT_PATH . 'Dao/Mysql/MysqlDao.php');
require_once(LIBRARY_ROOT_PATH . 'Database/Mysql.php');



/**
 * The test dao with all column types to be tested.
 */
class MysqlTestDao extends MysqlDao {
  protected $tableName = 'test_table_name';
  protected $columnTypes = array('id'=>Dao::INT, 'name'=>Dao::STRING, 'is_admin'=>Dao::BOOL);
}


class MysqlDao_Basic_Test extends Snap_UnitTestCase {

  protected $dao;

  public function setUp() {
    $this->db = $this->mock('Mysql')
      ->setReturnValue('escapeTable', 'ESCAPED_TABLE')
      ->setReturnValue('escapeString', 'ESCAPED_STRING')
      ->setReturnValue('escapeColumn', 'ESCAPED_COLUMN')
      ->listenTo('escapeTable', array(new Snap_Identical_Expectation('test_table_name')))
      ->listenTo('escapeTable', array(new Snap_Identical_Expectation('some_other_table')))
      ->listenTo('escapeString', array(new Snap_Identical_Expectation('some_string')))
      ->listenTo('escapeColumn', array(new Snap_Identical_Expectation('some_column')))
      ->construct(null);
    $this->dao = new MysqlTestDao($this->db);
    
  }

  public function tearDown() {}

  public function testExportTable() {
    return $this->assertIdentical($this->dao->exportTable(), '`ESCAPED_TABLE`');
  }

  public function testDefaultTableIsUsed() {
    $this->dao->exportTable();
    return $this->assertCallCount($this->db, 'escapeTable', 1, array(new Snap_Identical_Expectation('test_table_name')));
  }

  public function testSomeOtherTableName() {
    $this->dao->exportTable('some_other_table');
    return $this->assertCallCount($this->db, 'escapeTable', 1, array(new Snap_Identical_Expectation('some_other_table')));
  }

  public function testExportString() {
    return $this->assertIdentical($this->dao->exportString('some_string'), "'ESCAPED_STRING'");
  }

  public function testDatabaseEscapesString() {
    $this->dao->exportString('some_string');
    return $this->assertCallCount($this->db, 'escapeString', 1, array(new Snap_Identical_Expectation('some_string')));
  }


  public function testExportColumn() {
    return $this->assertIdentical($this->dao->exportColumn('some_column'), "`ESCAPED_COLUMN`");
  }

  public function testDatabaseEscapesColumn() {
    $this->dao->exportColumn('some_column');
    return $this->assertCallCount($this->db, 'escapeColumn', 1, array(new Snap_Identical_Expectation('some_column')));
  }

}




?>