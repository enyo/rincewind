<?php

/**
 * This file contains the SslEncryption definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Security
 * @subpackage Encryption
 */
/**
 * Including the interface
 */
require_class('Encryption', 'Security');

/**
 * The ssl implementation of Encrtypion.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Security
 * @subpackage Encryption
 */
class Ssl extends Encryption {

  /**
   * Used by openssl_encrypt
   * @var string
   */
  private $cipher;
  /**
   * @var string The maximum number of characters used as iv.
   */
  private $ivMaxChars;
  /**
   * Used by generateNonce to determine how many characters should be used as
   * nonce.
   * 
   * @var int
   */
  private $nonceChars;
  /**
   * @var int
   */
  private $cipherIvLength;

  /**
   *
   * @param string $cipher
   * @param string $password
   * @param string $salt
   * @param int $ivMaxChars null if you want a full iv. If you provide a number, the rest of the iv will be padded.
   * @param int $nonceChars 
   */
  public function __construct($cipher, $password, $salt, $ivMaxChars = 4, $nonceChars = 5) {
    parent::__construct($password, $salt);
    $this->cipher = $cipher;
    $this->cipherIvLength = openssl_cipher_iv_length($this->cipher);
    $this->ivMaxChars = $ivMaxChars;
    $this->nonceChars = $nonceChars;
  }

  /**
   * Returns a base64 encoded, openssl encrypted string with the encryption password and
   * a salt.
   * 
   * Do not rely on the way this is encrypted, just use it in conjunction with decrypt.
   * 
   * BEWARE of any changes in different release versions because you'll might be
   * unable to decrypt after a change.
   * 
   * @param string $string
   */
  public function encrypt($string) {
    $nonce = $this->generateNonce($this->nonceChars);

    $iv = $this->generateNonce($this->ivMaxChars ? (int) $this->ivMaxChars : $this->cipherIvLength, true);

    return $iv . '.' . Encryption::base64Encode(openssl_encrypt($this->salt . $string . $nonce, $this->cipher, $this->password, true, $this->padIv($iv, $this->cipherIvLength)));
  }

  /**
   * Verifies that a sigend + encrypted string is valid and returns the decrypted string.
   * 
   * This method...
   * 
   * 1. ...takes the iv from the beginning of the string
   * 2. ...does a base64_decode of the rest of the string
   * 3. ...checks that the ssl encryption is correct (decrypts the string with the correct cipher and password).
   * 4. ...checks that the salt is present and at the beginning of the the string.
   * 5. ...removes the random characters from the end of the string
   * 
   * 
   * BE CAREFUL!!!
   * 
   * Never let the message from the SecurityUtilsException be visible for the user.
   * This could result in a security risk.
   * The exception message is only for debugging purpose.
   * 
   * @param string $encryptedString
   * @return string
   * @throws EncryptionException
   */
  public function decrypt($encryptedString) {
    $encryptedString = explode('.', $encryptedString);
    if (count($encryptedString) !== 2) throw new EncryptionException('The encrypted string did not have an iv.');

    $iv = $encryptedString[0];
    $encryptedString = Encryption::base64Decode($encryptedString[1]);

    if ( ! $iv) throw new EncryptionException('IV was empty.');
    if ( ! $encryptedString) throw new EncryptionException('Encrypted string is not base64.');

    $iv = $this->padIv($iv, $this->cipherIvLength);

    $decrypted = openssl_decrypt($encryptedString, $this->cipher, $this->password, true, $iv);

    if ($decrypted === false) throw new EncryptionException('Encrypted string is not correctly openssl encrypted.');

    if (strpos($decrypted, $this->salt) !== 0) throw new EncryptionException('Encrypted string does not contain the salt.');

    $string = substr($decrypted, strlen($this->salt), strlen($decrypted) - strlen($this->salt) - $this->nonceChars);

    return $string;
  }

  /**
   * @param type $iv
   * @param type $length 
   * @return string padded iv.
   */
  private function padIv($iv, $length) {
    return str_pad($iv, $length, '-', STR_PAD_RIGHT);
  }

  /**
   * Generates a random set of characters
   * @param int $nonceChars
   * @param bool $onlyUrlSave if true, only url save characters are used.
   * @return string
   */
  private function generateNonce($nonceChars, $onlyUrlSave = false) {
    $charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';
    if ( ! $onlyUrlSave) $charset .= './*^!@#$%& ';
    $charsetLength = strlen($charset);

    $str = '';

    for ($i = 0; $i < $nonceChars; $i ++ ) {
      $str .= $charset[mt_rand(0, $charsetLength - 1)];
    }

    return $str;
  }

}