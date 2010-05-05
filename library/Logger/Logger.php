<?php

/**
 * This file contains the Logger definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Logger
 **/


if (!class_exists('LogException')) include(dirname(__FILE__) . '/LoggerExceptions.php');

/**
 * Loggers have to extend this class
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Logger
 **/
abstract class Logger {

  /**#@+
   * The different Log levels.
   *
   * @var int
   */
  const DEBUG = 0x11111;
  const INFO  = 0x01111;
  const WARN  = 0x00111;
  const ERROR = 0x00011;
  const FATAL = 0x00001;
  /**#@-*/

  /**
   * Log a debug message
   * 
   * @param string $message
   */
  abstract public function debug($message);

  /**
   * Log an info message
   * 
   * @param string $message
   */
  abstract public function info($message);

  /**
   * Log a warning
   * 
   * @param string $message
   */
  abstract public function warning($message);

  /**
   * Log an error
   * 
   * @param string $message
   */
  abstract public function error($message);

  /**
   * Log a fatal error
   * 
   * @param string $message
   */
  abstract public function fatal($message);


}


?>
