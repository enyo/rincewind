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
class InvalidUrlException extends DispatcherException {
  
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
