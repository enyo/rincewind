<?php

require_once dirname(__FILE__) . '/../../setup.php';

require_class('UsernameSanitizer', 'Sanitizer');

/**
 * Test class for UrlSanitizer.
 */
class UsernameSanitizerTest extends PHPUnit_Framework_TestCase {

  /**
   * @var UsernameSanitizer
   */
  protected $usernameSanitizer;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp() {
    $this->usernameSanitizer = new UsernameSanitizer();
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
    $test = '  m@TiAs.com';
    self::assertSame('m@tias.com', $this->usernameSanitizer->sanitize($test));
  }

}

