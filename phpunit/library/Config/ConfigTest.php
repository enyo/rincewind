<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__) . '/../../setup.php';

require_once LIBRARY_PATH . 'Config/Config.php';

/**
 * Test class for Config.
 */
class ConfigTest extends PHPUnit_Framework_TestCase {

  /**
   * @var Config
   */
  protected $config;

  protected function setUp() {
    $this->getMockForAbstractClass('Config', array(), 'AAA_MockConfig');
    $this->config = $this->getMock('AAA_MockConfig', array('clear', 'load'), array());
  }


  public function testReloadCallsClearAndLoad() {
    $this->config->expects($this->once())->method('clear');
    $this->config->expects($this->once())->method('load');

    $this->config->reload();
  }


}
