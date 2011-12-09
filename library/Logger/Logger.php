<?php

/**
 * This file contains the Logger definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Logger
 */
/**
 * Including the exceptions
 */
if ( ! class_exists('LoggerException', false)) include(dirname(__FILE__) . '/LoggerExceptions.php');

/**
 * Including the static class Log
 */
if ( ! class_exists('Log', false)) include(dirname(__FILE__) . '/Log.php');

/**
 * Loggers have to extend this class
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Logger
 */
abstract class Logger {
  /*   * #@+
   * The different Log levels.
   *
   * @var int
   */
  const DEBUG = 0x00001;
  const INFO = 0x00011;
  const WARNING = 0x00111;
  const ERROR = 0x01111;
  const FATAL = 0x11111;
  /*   * #@- */

  /**
   * A list of strings for the different levels, used in output.
   *
   * @var array
   */
  protected $levelStrings = array(
      Logger::DEBUG => 'DEBUG'
      , Logger::INFO => 'INFO '
      , Logger::WARNING => 'WARN '
      , Logger::ERROR => 'ERROR'
      , Logger::FATAL => 'FATAL'
  );
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
   * @return Logger itself for chaining.
   */
  public function setLevel($level) {
    $this->level = (int) $level;
    return $this;
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
   * @param string $context Optional context to log for.
   * @param array $additionalInfo An associative array with more info. Eg: array('content'=>'Some stuff')
   * @return bool true on success, false if nothing has been logged.
   */
  public function debug($message, $context = null, $additionalInfo = null) {
    return $this->log($message, self::DEBUG, $context, $additionalInfo);
  }

  /**
   * Log an info message
   *
   * @param string $message
   * @param string $context Optional context to log for.
   * @param array $additionalInfo An associative array with more info. Eg: array('content'=>'Some stuff')
   * @return bool true on success, false if nothing has been logged.
   */
  public function info($message, $context = null, $additionalInfo = null) {
    return $this->log($message, self::INFO, $context, $additionalInfo);
  }

  /**
   * Log a warning
   *
   * @param string $message
   * @param string $context Optional context to log for.
   * @param array $additionalInfo An associative array with more info. Eg: array('content'=>'Some stuff')
   * @return bool true on success, false if nothing has been logged.
   */
  public function warning($message, $context = null, $additionalInfo = null) {
    return $this->log($message, self::WARNING, $context, $additionalInfo);
  }

  /**
   * Alias for warning
   *
   * @param string $message
   * @param string $context Optional context to log for.
   * @param array $additionalInfo An associative array with more info. Eg: array('content'=>'Some stuff')
   * @return bool true on success, false if nothing has been logged.
   */
  public function warn($message, $context = null, $additionalInfo = null) {
    return $this->warning($message, $context, $additionalInfo);
  }

  /**
   * Log an error
   *
   * @param string $message
   * @param string $context Optional context to log for.
   * @param array $additionalInfo An associative array with more info. Eg: array('content'=>'Some stuff')
   * @return bool true on success, false if nothing has been logged.
   */
  public function error($message, $context = null, $additionalInfo = null) {
    return $this->log($message, self::ERROR, $context, $additionalInfo);
  }

  /**
   * Log a fatal error
   *
   * @param string $message
   * @param string $context Optional context to log for.
   * @param array $additionalInfo An associative array with more info. Eg: array('content'=>'Some stuff')
   * @return bool true on success, false if nothing has been logged.
   */
  public function fatal($message, $context = null, $additionalInfo = null) {
    return $this->log($message, self::FATAL, $context, $additionalInfo);
  }

  /**
   * The actual method that logs the message
   *
   * @param string $message
   * @param int $level
   * @param string $context Optional context to log for.
   * @param array $additionalInfo An associative array with more info. Eg: array('content'=>'Some stuff')
   * @return bool true on success, false if nothing has been logged.
   */
  protected function log($message, $level, $context = null, $additionalInfo = null) {
    if ($additionalInfo && ! is_array($additionalInfo)) {
      $additionalInfo = array('jsonInfo' => json_encode($additionalInfo));
    }
    if ( ! $this->shouldLog($level)) return false;
    $this->doLog($message, $level, $context, $additionalInfo);
    return true;
  }

  /**
   * The actual method that logs the message
   *
   * @param string $message
   * @param int $level
   * @param string $context
   * @param array $additionalInfo An associative array with more info. Eg: array('content'=>'Some stuff')
   */
  protected abstract function doLog($message, $level, $context, $additionalInfo);
}

