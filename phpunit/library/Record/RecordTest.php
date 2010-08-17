<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__) . '/../../setup.php';


/**
 * Test class for Record.
 */
class RecordTest extends PHPUnit_Framework_TestSuite {

  public static function suite() {

    $suite = new PHPUnit_Framework_TestSuite();

    $suite->addTestFile(dirname(__FILE__) . '/Record.CoerceTest.php');

    return $suite;
  }

}

