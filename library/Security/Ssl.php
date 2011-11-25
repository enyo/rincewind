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
   * @var bool
   */
  private $useMd5NotRandom;

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
   * @param bool $useMd5NotRandom If true, the IV will be extracted from md5 of the data.
   * @param int $nonceChars
   * @param int $cipherIvLength If null, the iv length gets calculated with openssl_cipher_iv_length()
   * @param string $serializationFormat
   */
  public function __construct($cipher, $password, $salt, $ivMaxChars = 4, $useMd5NotRandom = false, $nonceChars = 5, $cipherIvLength = null, $serializationFormat = self::SERIALIZE_JSON) {
    parent::__construct($password, $salt, $serializationFormat);
    $this->cipher = $cipher;
    $cipherIvLength = (int) $cipherIvLength;
    $this->cipherIvLength = $cipherIvLength ? (int) $cipherIvLength : openssl_cipher_iv_length($this->cipher);
    $this->ivMaxChars = $ivMaxChars === null ? null : (int) $ivMaxChars;
    $this->nonceChars = (int) $nonceChars;
    $this->useMd5NotRandom = $useMd5NotRandom;
  }

  /**
   * @return int
   */
  public function getCipherIvLength() {
    return $this->cipherIvLength;
  }

  /**
   * Returns a base64 encoded, openssl encrypted string with the encryption
   * password and
   * a salt.
   *
   * Do not rely on the way this is encrypted, just use it in conjunction with
   * decrypt.
   *
   * BEWARE of any changes in different release versions because you'll might be
   * unable to decrypt after a change.
   *
   * If the data is not a string, it gets serialized.
   *
   * @param mixed $data
   */
  public function encrypt($data) {
    if (is_string($data)) $data = self::SERIALIZE_NONE . '-' . $data; // plain
    elseif ($this->serializationFormat === self::SERIALIZE_JSON) $data = self::SERIALIZE_JSON . '-' . json_encode($data); // json
    elseif ($this->serializationFormat === self::SERIALIZE_PHP) $data = self::SERIALIZE_PHP . '-' . serialize($data); // serialized

    $nonce = $this->generateNonce($this->nonceChars);

    if ($this->useMd5NotRandom) {
      // Using the md5 of data as IV
      $iv = substr(md5($data), 0, $this->ivMaxChars);
    }
    else {
      // Complete random IV
      $iv = $this->generateNonce(is_int($this->ivMaxChars) ? $this->ivMaxChars : $this->cipherIvLength, true);
    }

    return ($iv ? $iv . '.' : '') . Encryption::base64Encode(openssl_encrypt($this->salt . $data . $nonce, $this->cipher, $this->password, true, $this->padIv($iv, $this->cipherIvLength)));
  }

  /**
   * Verifies that a sigend + encrypted string is valid and returns the
   * decrypted string.
   *
   * This method...
   *
   * 1. ...takes the iv from the beginning of the string
   * 2. ...does a base64_decode of the rest of the string
   * 3. ...checks that the ssl encryption is correct (decrypts the string with
   *       the correct cipher and password).
   * 4. ...checks that the salt is present and at the beginning of the the
   *       string.
   * 5. ...removes the random characters from the end of the string
   *
   *
   * BE CAREFUL!!!
   *
   * Never let the message from the EncryptionException be visible to the user.
   * This could result in a security risk.
   * The exception message is only for debugging purpose.
   *
   * If the data that has been encrypted wasn't a string, it gets serialized by
   * this method.
   *
   * @param string $encryptedData
   * @return mixed
   * @throws EncryptionException
   */
  public function decrypt($encryptedData) {
    $encryptedData = explode('.', $encryptedData);
    if (count($encryptedData) !== 1 && count($encryptedData) !== 2) throw new EncryptionException('The encrypted string did not have a correct iv.');

    if (count($encryptedData) === 1) {
      // No IV has been chosen
      $iv = '';
      $encryptedData = Encryption::base64Decode($encryptedData[0]);
    }
    elseif (count($encryptedData) === 2) {
      // IV present
      $iv = $encryptedData[0];
      if (!$iv) throw new EncryptionException('IV was empty.');
      $encryptedData = Encryption::base64Decode($encryptedData[1]);
    }

    if (!$encryptedData) throw new EncryptionException('Encrypted string is not base64.');

    $iv = $this->padIv($iv, $this->cipherIvLength);

    $decrypted = openssl_decrypt($encryptedData, $this->cipher, $this->password, true, $iv);

    if ($decrypted === false) throw new EncryptionException('Encrypted string is not correctly openssl encrypted.');

    $saltLength = strlen($this->salt);

    if (substr($decrypted, 0, $saltLength) !== $this->salt) throw new EncryptionException('Encrypted string does not contain the salt.');

    $dataInfoLength = 2;
    $dataInfo = substr($decrypted, $saltLength, $dataInfoLength);
    if ($dataInfo !== self::SERIALIZE_NONE . '-' && $dataInfo !== self::SERIALIZE_PHP . '-' && $dataInfo !== self::SERIALIZE_JSON . '-') throw new EncryptionException('Encrypted string does not contain data information.');

    $data = substr($decrypted, $saltLength + $dataInfoLength, strlen($decrypted) - $saltLength - $dataInfoLength - $this->nonceChars);

    switch ($dataInfo) {
      case self::SERIALIZE_PHP . '-':
        $data = @unserialize($data);
        break;
      case self::SERIALIZE_JSON . '-':
        $data = @json_decode($data, true);
        break;
    }

    return $data;
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
    if ($nonceChars === 0) return '';

    $charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';
    if (!$onlyUrlSave) $charset .= './*^!@#$%& ';
    $charsetLength = strlen($charset);

    $str = '';

    for ($i = 0; $i < $nonceChars; $i++) {
      $str .= $charset[mt_rand(0, $charsetLength - 1)];
    }

    return $str;
  }

}