<?php

/**
 * This file contains the Router definition.
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Router
 */
/**
 * Including the Router
 */
require_class('DefaultRouter', 'Router');

/**
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2009, Matthias Loitsch
 * @package Utils
 * @package Router
 */
class SecurityRouter extends DefaultRouter {

  /**
   * @var Encryption
   */
  protected $urlEncryption;

  /**
   * @param Encryption $urlEncryption
   * @param bool $useRestfulUrls
   */
  public function __construct(Encryption $urlEncryption, $useRestfulUrls = true) {
    parent::__construct($useRestfulUrls);
    $this->urlEncryption = $urlEncryption;
  }


  /**
   * This implementation encrypts the message with the urlEncryption object.
   *
   * {@inheritdoc}
   */
  public function exportUrlMessage($message) {
    return $this->urlEncryption->encrypt($message);
  }
  /**
   * This implementation decrypts the message with the urlEncryption object.
   *
   * {@inheritdoc}
   */
  protected function importUrlMessage($message) {
    return $this->urlEncryption->decrypt($message);
  }

}