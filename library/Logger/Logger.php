<?php

/**
 * This file contains the Logger definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Logger
 **/


if (!class_exists('LoggerException')) include(dirname(__FILE__) . '/LoggerExceptions.php');

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
  const DEBUG   = 0x00001;
  const INFO    = 0x00011;
  const WARNING = 0x00111;
  const ERROR   = 0x01111;
  const FATAL   = 0x11111;
  /**#@-*/


  /**
   * The minimum level a message has to have to be logged.
   * Eg: If level is Logger::WARN then WARN, ERROR and FATAL will be logged.
   *
   * @var int
   */
  protected $level = self::WARNING;


  /**
   * Sets the level for logging.
   *
   * @param int $level
   * @see $level
   */
  public function setLevel($level) {
    $this->level = (int) $level;
  }

  /**
   * Returns the current level for logging.
   *
   * @return int
   * @see $level
   */
  public function getLevel() {
    return $this->level;
  }

  /**
   * Checks if it's ok to log for specified level
   *
   * @param int $messageLevel
   * @return bool
   */
  protected function shouldLog($messageLevel) {
    return ((int) $messageLevel & $this->level) === $this->level;
  }


  /**
   * Log a debug message
   * 
   * @param string $message
   */
  public function debug($message) { return $this->log($message, self::DEBUG); }

  /**
   * Log an info message
   * 
   * @param string $message
   */
  public function info($message) { return $this->log($message, self::INFO); }

  /**
   * Log a warning
   * 
   * @param string $message
   */
  public function warning($message) { return $this->log($message, self::WARNING); }


  /**
   * Alias for warning
   * 
   * @param string $message
   */
  public function warn($message) { return $this->warning($message); }


  /**
   * Log an error
   * 
   * @param string $message
   */
  public function error($message) { return $this->log($message, self::ERROR); }

  /**
   * Log a fatal error
   * 
   * @param string $message
   */
  public function fatal($message) { return $this->log($message, self::FATAL); }


  /**
   * The actual method that logs the message
   *
   * @param string $message
   * @param int $level
   */
  protected function log($message, $level) {
    if (!$this->shouldLog($level)) return false;
    $this->doLog($message, $level);
    return true;
  }


  /**
   * The actual method that logs the message
   *
   * @param string $message
   * @param int $level
   */
  protected abstract function doLog($message, $level);


}


?>
