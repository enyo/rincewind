<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__) . '/../../setup.php';

/**
 * Test suite for Dao.
 */
class ProfilerTimerTest extends PHPUnit_Framework_TestSuite {

  public static function suite() {

    $suite = new PHPUnit_Framework_TestSuite();

    $suite->addTestFile(dirname(__FILE__) . '/ProfilerTimer.NaturalTest.php');
    $suite->addTestFile(dirname(__FILE__) . '/ProfilerTimer.FixedTest.php');

    return $suite;
  }

}

