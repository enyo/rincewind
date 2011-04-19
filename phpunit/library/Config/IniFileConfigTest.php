<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(dirname(dirname(__FILE__))) . '/setup.php';

require_once RINCEWIND_PATH . 'Config/IniFileConfig.php';

/**
 * Test class for IniFileConfig.
 */
class IniFileConfigTest extends PHPUnit_Framework_TestCase {

  public function testConfigWithOneFile() {
    $config = new IniFileConfig(dirname(__FILE__) . '/default.conf', null, $useSections = true, 'section_a');
    self::assertEquals(array('section_a' => array('a1' => '1', 'a2' => '2'), 'section_b' => array('b1' => '3', 'b2' => '4')), $config->getArray(false));
    self::assertEquals(array('section_a.a1' => '1', 'section_a.a2' => '2', 'section_b.b1' => '3', 'section_b.b2' => '4'), $config->getArray(true));
  }

  public function testConfigWithSections() {
    $config = new IniFileConfig(dirname(__FILE__) . '/real.conf', dirname(__FILE__) . '/default.conf', $useSections = true, 'section_a');

    self::assertEquals(array('section_a' => array('a1' => '1', 'a2' => 'overwritten'), 'section_b' => array('b1' => '3', 'b2' => 'also overwritten')), $config->getArray());

    self::assertEquals(array('section_a.a1' => '1', 'section_a.a2' => 'overwritten', 'section_b.b1' => '3', 'section_b.b2' => 'also overwritten'), $config->getArray(true));

    self::assertEquals('overwritten', $config->get('a2'), 'The default section should be "section_a"');

    self::assertEquals('also overwritten', $config->get('b2', 'section_b'));

    // Now test setting the default section
    $config->setDefaultSection('section_b');
    self::assertEquals('3', $config->get('b1'), 'The default section should have been "section_b" now.');

    try {
      $config->get('b1', 'wrong_section');
      self::fail('Should have thrown an exception.');
    }
    catch (ConfigException $e) {

    }

    try {
      $config->get('wrong_variable', 'section_a');
      self::fail('Should have thrown an exception.');
    }
    catch (ConfigException $e) {
      
    }
  }

  public function testConfigWithoutSection() {
    $config = new IniFileConfig(dirname(__FILE__) . '/real.conf', dirname(__FILE__) . '/default.conf', $useSections = false);

    self::assertEquals(array('a1' => '1', 'a2' => 'overwritten', 'b1' => '3', 'b2' => 'also overwritten'), $config->getArray());

    self::assertEquals('overwritten', $config->get('a2'));

    self::assertEquals('also overwritten', $config->get('b2', 'section_b'));


    try {
      $config->setDefaultSection('section_b');
      self::fail('Setting a default section when not using sections should throw exception.');
    }
    catch (ConfigException $e) {

    }

    try {
      $config->get('wrong_variable');
      self::fail('Should have thrown an exception.');
    }
    catch (ConfigException $e) {

    }
  }

}

