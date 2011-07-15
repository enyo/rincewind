<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class DispatcherException extends Exception {
  
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
