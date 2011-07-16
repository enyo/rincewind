<?php

/**
 * The Dispatcher file.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dispatcher
 */

/**
 * DispatcherException gets thrown when the dispatcher tries to dispatch
 */
class DispatcherException extends Exception {
  
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