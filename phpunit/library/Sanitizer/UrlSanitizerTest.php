<?php

require_once dirname(__FILE__) . '/../../setup.php';

require_class('UrlSanitizer', 'Sanitizer');

/**
 * Test class for UrlSanitizer.
 */
class UrlSanitizerTest extends PHPUnit_Framework_TestCase {

  /**
   * @var UrlSanitizer
   */
  protected $urlSanitizer;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp() {
    $this->urlSanitizer = new UrlSanitizer();
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown() {
    
  }

  /**
   */
  public function testSanitizing() {
    $test = 'Šíleně žluťoučký Vašek úpěl olol! Älter Über Örtlich   Spaß';
    self::assertSame('Silene-zlutoucky-Vasek-upel-olol-Alter-Uber-Ortlich-Spass', $this->urlSanitizer->sanitize($test));
  }

}

