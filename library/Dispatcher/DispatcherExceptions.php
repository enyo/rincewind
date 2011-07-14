<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class DispatcherException extends Exception {
  
}

/**
 * The Dispatcher .
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dispatcher
 */
class DispatcherInfoException extends DispatcherException {

  /**
   * @var array
   */
  protected $additionalInfo;

  /**
   * @param string $message
   * @param array $additionalInfo 
   */
  public function __construct($message, array $additionalInfo = array()) {
    parent::__construct($message);
    $this->additionalInfo = $additionalInfo;
  }

  /**
   * @return array
   */
  public function getAdditionalInfo() {
    return $this->additionalInfo;
  }

}

/**
 * Thrown when the url is incorrect.
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Controller
 */
class ErrorCode extends DispatcherException {
  /**
   * @var int
   */
  const BAD_REQUEST = 400;
  /**
   * @var int
   */
  const UNAUTHORIZED = 401;
  /**
   * @var int
   */
  const PAYMENT_REQUIRED = 402;
  /**
   * @var int
   */
  const FORBIDDEN = 403;
  /**
   * @var int
   */
  const NOT_FOUND = 404;
  /**
   * @var int
   */
  const METHOD_NOT_ALLOWED = 405;
  /**
   * @var int
   */
  const INTERNAL_SERVER_ERROR = 500;
  /**
   * @var int
   */
  const NOT_IMPLEMENTED = 501;

  /**
   *
   * @param int $code See the constants defined in this class
   * @param string $message
   */
  public function __construct($code, $message = null, $previous = null) {
    parent::__construct($message, $code, $previous);
  }

}

/**
 * Can be thrown to add an error message.
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Controller
 */
class ErrorMessageException extends DispatcherException {
  
}
