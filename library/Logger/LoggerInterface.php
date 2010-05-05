<?php

/**
 * This file contains the LoggerInterface definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Logger
 **/


if (!class_exists('LogException')) include(dirname(__FILE__) . '/LoggerExceptions.php');

/**
 * Loggers have to implement this LoggerInterface
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Logger
 **/
interface LoggerInterface {
  
  /**
   * The debug function does the same as log, but only if the logger is in debug mode.
   * 
   * @param string $message
   * @param bool $newLine Whether a new line should be started after the logging.
   */
  public function debug($message);


}


?>
