<?php

require_once(dirname(dirname(__FILE__)) . '/setup.php');

require_once(LIBRARY_ROOT_PATH . 'Dao/Dao.php');

require_once(dirname(__FILE__) . '/NonAbstractDao.php');


class FakeDao extends NonAbstractDao {

  protected $attributeImportMapping = array('bad_name'=>'good_name');

  protected $attributes = array(
    'someColumn'=>Dao::STRING,
    'goodName'=>Dao::STRING
  );

  protected function escapeAttributeName($name) { return $name; }

}



class Dao_Imports_Test extends Snap_UnitTestCase {

  protected $dao;
  protected $object;

  public function setUp() {
    $this->dao = new FakeDao(null);
    $this->object = $this->dao->getRecordFromData(array('some_column'=>'bla some', 'bad_name'=>'bla good'));
  }

  public function tearDown() {}


  public function testNormalAccess() {
    return $this->assertIdentical($this->object->someColumn, 'bla some');
  }

  public function testImportMappingAccess() {
    return $this->assertIdentical($this->object->goodName, 'bla good');
  }

}


class Dao_Exports_Test extends Snap_UnitTestCase {

  protected $dao;
  protected $object;

  public function setUp() {
    $this->dao = new FakeDao(null);
  }

  public function tearDown() {}


  public function testNormalExport() {
    return $this->assertIdentical($this->dao->exportAttributeName('someColumn'), 'some_column');
  }

  public function testExportMapping() {
    return $this->assertIdentical($this->dao->exportAttributeName('goodName'), 'bad_name');
  }

}


?>
