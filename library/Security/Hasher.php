<?php

/**
 * This file contains the Hasher definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Security
 * @subpackage Hasher
 */

/**
 * The Hasher takes a string, and returns a hash.
 *
 * It is very important that a password is...
 * 
 * 1. ... salted with a system salt.
 * 2. ... salted with an individual salt.
 * 
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Security
 * @subpackage Hasher
 */
class Hasher {

  /**
   * @var string
   */
  protected $systemSalt;
  /**
   * @var string
   */
  protected $algorithm;

  /**
   * @param string $systemSalt Used to salt every hash, system wide.
   * @param string $algorithm 
   */
  function __construct($systemSalt, $algorithm = 'sha512') {
    $this->systemSalt = $systemSalt;
    $this->algorithm = $algorithm;
  }

  /**
   * @param string $text
   * @param string $salt Take the username for example, if you hash a password.
   */
  public function hash($text, $salt) {
    return hash($this->algorithm, $text . $this->systemSalt . $salt);
  }

}