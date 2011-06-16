<?php

require_once dirname(__FILE__) . '/../../setup.php';

require_class('Encryption', 'Security');

/**
 * Test class for DefaultSecurity.
 */
class SecurityTest extends PHPUnit_Framework_TestCase {


  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp() {
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown() {
    
  }

  public function testBase64Encode() {
    $string = '!!?*!~Za_-c@#$2üäas!';
    self::assertSame('ISE/KiF+WmFfLWNAIyQyw7zDpGFzIQ==', base64_encode($string));
    self::assertSame('ISE_KiF-WmFfLWNAIyQyw7zDpGFzIQ', Encryption::base64Encode($string));

  }

  public function testBase64Decode() {
    self::assertSame('!!?*!~Za_-c@#$2üäas!', Encryption::base64Decode('ISE_KiF-WmFfLWNAIyQyw7zDpGFzIQ'));
//    self::assertSame('3_-4bbc2_-3', Security::sanitizeBase64('3/+4bbc2/+3=='));
  }
  
}

