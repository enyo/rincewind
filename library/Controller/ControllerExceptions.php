<?php

/**
 * This file contains all ControllerExceptions
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Controller
 */

/**
 * The ControllerException
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Controller
 */
class ControllerException extends Exception {
  
}

/**
 * Is thrown on errors.
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Controller
 */
class Error extends ControllerException {
  
}

/**
 * Can be thrown to add an error message.
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Controller
 */
class ErrorMessageException extends DispatcherException {

  /**
   * @param string $message
   * @param int $code
   * @param type $previous 
   */
  public function __construct($message, $code = null, $previous = null) {
    parent::__construct($message, $code, $previous);
  }

}

/**
 * Thrown when a http error code should be sent.
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
   * Throws a 400 ErrorCode exception
   */
  static function badRequest() {
    throw new ErrorCode(self::BAD_REQUEST);
  }

  /**
   * Throws a 403 ErrorCode exception
   */
  static function forbidden() {
    throw new ErrorCode(self::FORBIDDEN);
  }

  /**
   * Throws a 404 ErrorCode exception
   */
  static function notFound() {
    throw new ErrorCode(self::NOT_FOUND);
  }

  /**
   * Throws a 405 ErrorCode exception
   */
  static function methodNotAllowed() {
    throw new ErrorCode(self::METHOD_NOT_ALLOWED);
  }

  /**
   * Throws a 500 ErrorCode exception
   */
  static function internalServerError() {
    throw new ErrorCode(self::INTERNAL_SERVER_ERROR);
  }

  /**
   * Throws a 501 ErrorCode exception
   */
  static function notImplemented() {
    throw new ErrorCode(self::NOT_IMPLEMENTED);
  }

  /**
   *
   * @param int $code See the constants defined in this class
   * @param string $message
   */
  public function __construct($code, $message = null, $previous = null) {
    parent::__construct($message, $code, $previous);
  }

  /**
   * Writes the appropriate http header
   */
  public function writeHttpHeader() {
    $text = '';
    switch ($this->code) {
      case self::BAD_REQUEST:
        $text = 'Bad request';
        break;
      case self::FORBIDDEN:
        $text = 'Forbidden';
        break;
      case self::NOT_FOUND:
        $text = 'Not Found';
        break;
      case self::METHOD_NOT_ALLOWED:
        $text = 'Method Not Allowed';
        break;
      case self::INTERNAL_SERVER_ERROR:
        $text = 'Internal Server Error';
        break;
      case self::NOT_IMPLEMENTED:
        $text = 'Not Implemented';
        break;
    }
    header('HTTP/1.0 ' . $this->code . ' ' . $text);
  }

}
