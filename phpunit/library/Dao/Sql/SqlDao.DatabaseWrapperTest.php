<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__) . '/../../../setup.php';

require_once RINCEWIND_PATH . 'Dao/Sql/SqlDaoBase.php';
require_once RINCEWIND_PATH . 'Database/DatabaseInterface.php';

/**
 * Test class for SqlDao. This testcase makes sure that all calls get forwarded correctly to the database object.
 */
class SqlDaoDatabaseWrapperTest extends PHPUnit_Framework_TestCase {

  protected $sqlDao;
  protected $db;

  public function setUp() {
    $this->db = $this->getMock('DatabaseInterface', array('escapeString', 'exportString', 'escapeColumn', 'exportColumn', 'escapeTable', 'exportTable', 'query', 'multiQuery', 'getResource', 'lastError', 'getLastInsertId', 'startTransaction'), array(), '', false);

    $this->sqlDao = new SqlDaoBase($this->db, 'tablename', array('id'=>Dao::INT));
  }

  public function testEscapeAndExportFunctionsGetForwardedProperly() {
    $this->db->expects($this->once())->method('escapeString')->with('string to escape')->will($this->returnValue('test'));
    $this->db->expects($this->once())->method('exportString')->with('string to export')->will($this->returnValue('test'));
    $this->db->expects($this->once())->method('escapeColumn')->with('attribute to escape')->will($this->returnValue('test'));
    $this->db->expects($this->once())->method('exportColumn')->with('attribute to export')->will($this->returnValue('test'));

    $this->sqlDao->escapeString('string to escape');
    $this->sqlDao->exportString('string to export');
    $this->sqlDao->escapeAttributeName('attribute to escape');
    $this->sqlDao->exportAttributeName('attribute to export');
  }
}

