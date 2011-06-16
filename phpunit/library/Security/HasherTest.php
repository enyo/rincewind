<?php

require_once dirname(__FILE__) . '/../../setup.php';

require_class('Hasher', 'Security');

/**
 * Test class for DefaultSecurity.
 */
class HasherTest extends PHPUnit_Framework_TestCase {

  /**
   * @var Hasher
   */
  protected $hasher;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp() {
    $this->hasher = new Hasher('1234');
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown() {
    
  }

  /**
   */
  public function testHash() {
    $username = 'USERNAME';
    $password = 'PASSWORD *../';

    $hashedPassword = $this->hasher->hash($username, $password);
    self::assertSame($hashedPassword, '2df63690f4e665f3584bed37e314945a4acf59dbebe99e75b3ae1e1fd24e1142873ba98d2bc6a104ef0a1f9629782b6a52914a2d7b3f657b963a1b22489541b1');
 
    $otherHasher = new Hasher('43321');

    $hashedPasswordWithOtherSalt = $otherHasher->hash($username, $password);
    self::assertNotEquals($hashedPassword, $hashedPasswordWithOtherSalt, 'The same passwords with different salts should have different hashes.');
  }

}

