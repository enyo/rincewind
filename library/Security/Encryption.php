<?php

/**
 * This file contains the Encryption definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Security
 * @subpackage Encryption
 */

/**
 * Thrown when there was a problem en/decrypting
 */
class EncryptionException extends Exception {
  
}

/**
 * The Encryption interface is used for all encryption related methods.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Security
 * @subpackage Encryption
 */
abstract class Encryption {

  /**
   * @var string
   */
  protected $password;
  /**
   * @var string
   */
  protected $salt;

  /**
   * @param type $password
   * @param type $salt 
   */
  public function __construct($password, $salt) {
    $this->password = $password;
    $this->salt = $salt;
  }

  /**
   * 
   */
  abstract public function encrypt($string);

  /**
   * 
   */
  abstract public function decrypt($encryptedString);

  /**
   * Encodes a string with base64.
   * Makes sure base64 strings are url compatible and have no = signs.
   * 
   * + becomes -
   * / becomes _
   * 
   * @param string $string 
   */
  static public function base64Encode($string) {
    // The order is important here!
    return rtrim(str_replace(array('+', '/'), array('-', '_'), base64_encode($string)), '=');
  }

  /**
   * @param string $string 
   * @see base64Encode
   */
  static public function base64Decode($string) {
    return base64_decode(str_replace(array('-', '_'), array('+', '/'), $string));
  }

}