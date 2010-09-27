<?php

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__) . '/../../setup.php';

/**
 * Test suite for Dao.
 */
class DaoTest extends PHPUnit_Framework_TestSuite {

  public static function suite() {

    $suite = new PHPUnit_Framework_TestSuite();

    $suite->addTestFile(dirname(__FILE__) . '/Dao.BasicTest.php');
    $suite->addTestFile(dirname(__FILE__) . '/Dao.ReferenceTest.php');

    return $suite;
  }

}

