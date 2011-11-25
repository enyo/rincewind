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
 * The Encryption class is used to en- or decrypt text.
 *
 * It gets configured in the constructor, and the en/decrypt methods can
 * be invoked without further configuring.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Security
 * @subpackage Encryption
 */
abstract class Encryption {

  /**
   * Used internally when no serialization has to be done.
   */
  const SERIALIZE_NONE = 'p';

  /**
   * Use json when serializing data for encryption.
   */
  const SERIALIZE_JSON = 'j';
  /**
   * Use php serialize when serializing data for encryption.
   */
  const SERIALIZE_PHP = 's';


  /**
   * @var string
   */
  protected $password;

  /**
   * @var string
   */
  protected $salt;

  /**
   * One of the SERIALIZE_* constants.
   * @var string
   */
  protected $serializationFormat;

  /**
   * @param type $password
   * @param type $salt
   */
  public function __construct($password, $salt, $serializationFormat = self::SERIALIZE_JSON) {
    $this->password = $password;
    $this->salt = $salt;
    if ($serializationFormat !== self::SERIALIZE_JSON && $serializationFormat !== self::SERIALIZE_PHP) throw new EncryptionException('Unknown serialization format: ' . $serializationFormat);
    $this->serializationFormat = $serializationFormat;
  }

  /**
   * @param mixed $data
   */
  abstract public function encrypt($data);

  /**
   * @param string $encryptedData
   * @return mixed $data
   */
  abstract public function decrypt($encryptedData);

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