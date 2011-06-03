<?php

/**
 * The Dispatcher file.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dispatcher
 */
class DispatcherException extends Exception {

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
 * The Dispatcher interface.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dispatcher
 */
interface Dispatcher {

  /**
   * Tries to interpret the URL and invoke the correct action on the correct controller.
   */
  public function dispatch();
}