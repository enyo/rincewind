<?php

require_once(dirname(dirname(__FILE__)) . '/setup.php');

require_once(LIBRARY_ROOT_PATH . 'Config/IniFileConfig.php');



class IniFileConfig_OneFile_Test extends Snap_UnitTestCase {

  protected $config;

  public function setUp() {
    $this->config = new IniFileConfig(dirname(__FILE__) . '/default.conf', null, $useSections = true, 'section_a');
  }

  public function tearDown() {
  }

  public function testArray() {
    return $this->assertIdentical($this->config->getArray(), array('section_a'=>array('a1'=>'1', 'a2'=>'2'), 'section_b'=>array('b1'=>'3', 'b2'=>'4')));
  }
  
}




class IniFileConfig_WithSections_Test extends Snap_UnitTestCase {

  protected $config;

  public function setUp() {
    $this->config = new IniFileConfig(dirname(__FILE__) . '/real.conf', dirname(__FILE__) . '/default.conf', $useSections = true, 'section_a');
  }

  public function tearDown() {
  }


  public function testArray() {
    return $this->assertIdentical($this->config->getArray(), array('section_a'=>array('a1'=>'1', 'a2'=>'overwritten'), 'section_b'=>array('b1'=>'3', 'b2'=>'also overwritten')));
  }

  public function testDefaultSection() {
    return $this->assertIdentical($this->config->get('a2'), 'overwritten');
  }

  public function testSpecificSection() {
    return $this->assertIdentical($this->config->get('b2', 'section_b'), 'also overwritten');
  }

  public function testSettingDefaultSection() {
    $this->config->setDefaultSection('section_b');
    return $this->assertIdentical($this->config->get('b1'), '3');
  }

  public function testWrongSection() {
    $this->willThrow('ConfigException');
    $this->config->get('b1', 'some_section');
  }

  public function testWrongVariable() {
    $this->willThrow('ConfigException');
    $this->config->get('b1sdf', 'section_a');
  }


}



class IniFileConfig_WithoutSections_Test extends Snap_UnitTestCase {

  protected $config;

  public function setUp() {
    $this->config = new IniFileConfig(dirname(__FILE__) . '/real.conf', dirname(__FILE__) . '/default.conf', $useSections = false);
  }

  public function tearDown() {
  }


  public function testArray() {
    return $this->assertIdentical($this->config->getArray(), array('a1'=>'1', 'a2'=>'overwritten', 'b1'=>'3', 'b2'=>'also overwritten'));
  }

  public function testGetting() {
    return $this->assertIdentical($this->config->get('b2'), 'also overwritten');
  }

  public function testSettingDefaultSectionThrowsException() {
    $this->willThrow('ConfigException');
    $this->config->setDefaultSection('section_b');
  }

  public function testWrongVariable() {
    $this->willThrow('ConfigException');
    $this->config->get('b1sdf');
  }


}


