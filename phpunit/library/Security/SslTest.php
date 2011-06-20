<?php

require_once dirname(__FILE__) . '/../../setup.php';

require_class('Ssl', 'Security');

/**
 * Test class for Ssl.
 */
class SslTest extends PHPUnit_Framework_TestCase {

  /**
   * @var Ssl
   */
  protected $ssl;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp() {
    $this->ssl = new Ssl('AES-128-CFB8', '*%=XC=Hj!bUXKQP;;8y', '`/:p@:f8;');
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown() {
    
  }

  public function testEncryptedIsCorrectlyFormatted() {
    $encrypted = $this->ssl->encrypt('TEST');
    $strpos = strpos($encrypted, '.');
    self::assertTrue($strpos !== false && $strpos == 4, $encrypted);
    self::assertTrue(strlen($encrypted) > 8, $encrypted);
  }

  public function testEncryptUsesIV() {
    $firstEncrypted = $this->ssl->encrypt('TEST');
    $secondEncrypted = $this->ssl->encrypt('TEST');
    self::assertNotEquals($firstEncrypted, $secondEncrypted, 'The encrypt method should use an IV every time, thus the result should not be the same.');
  }

  public function testDecryptWorks() {
    $firstEncrypted = $this->ssl->encrypt('test@email.com');
    $secondEncrypted = $this->ssl->encrypt('test@email.com');
    self::assertNotEquals($firstEncrypted, $secondEncrypted, 'The two encrypted strings should have had different IVs, thus be different.');
    self::assertSame('test@email.com', $this->ssl->decrypt($firstEncrypted));
    self::assertSame('test@email.com', $this->ssl->decrypt($secondEncrypted));
  }

  public function testEncryptWithSpecifiedIVLenght() {
    $ssl = new Ssl('AES-128-CFB8', '*%=XC=Hj!bUXKQP;;8y', '`/:p@:f8;');
    $encrypted = $ssl->encrypt('TEST');
    self::assertSame(4, strpos($encrypted, '.'));
    $ssl = new Ssl('AES-128-CFB8', '*%=XC=Hj!bUXKQP;;8y', '`/:p@:f8;', 8);
    $encrypted = $ssl->encrypt('TEST');
    self::assertSame(8, strpos($encrypted, '.'));
    $ssl = new Ssl('AES-128-CFB8', '*%=XC=Hj!bUXKQP;;8y', '`/:p@:f8;', null);
    $encrypted = $ssl->encrypt('TEST');
    self::assertSame(16, strpos($encrypted, '.'));
  }

  public function testDecryptThrowsExceptionIfNoIv() {
    $this->setExpectedException('EncryptionException', 'The encrypted string did not have an iv.');
    self::assertSame('test@email.com', $this->ssl->decrypt('does not contain iv'));
  }

  public function testDecryptThrowsExceptionIfNoSalt() {
    self::markTestIncomplete();
  }

  public function testIvGetsCalculatedAutomaticallyButCanBeSet() {
    self::assertSame(16, $this->ssl->getCipherIvLength());
    $ssl = new Ssl('AES-128-CFB8', '*%=XC=Hj!bUXKQP;;8y', '`/:p@:f8;', 4, 5, 9);
    self::assertSame(9, $ssl->getCipherIvLength());
  }

}

