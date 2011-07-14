<?php

require_once dirname(__FILE__) . '/../../setup.php';

require_once RINCEWIND_PATH . 'Config/Config.php';



class SpecificConfig extends Config {

  public function load() {
    $this->config = array('a', 'b');
  }

  public function getConfigDirectly() {
    return $this->config;
  }

}

/**
 * Test class for Config.
 */
class ConfigTest extends PHPUnit_Framework_TestCase {

  protected function setUp() {
  }

  public function testReloadCallsClearAndLoad() {
    $this->getMockForAbstractClass('Config', array(), 'AAA_MockConfig');
    $config = $this->getMock('AAA_MockConfig', array('clear', 'load'), array());
    $config->expects($this->once())->method('clear');
    $config->expects($this->once())->method('load');

    $config->reload();
  }

  public function testClearDeletesConfig() {
    $config = new SpecificConfig();
    self::assertNull($config->getConfigDirectly());
    $config->load();
    self::assertSame(array('a', 'b'), $config->getConfigDirectly());
    $config->clear();
    self::assertNull($config->getConfigDirectly());
  }

}
