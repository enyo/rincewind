<?php

/**
 * This file contains the Log definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Logger
 */
/**
 * Including the LoggerExceptions
 */
if ( ! class_exists('LogException', false)) include dirname(__FILE__) . '/LoggerExceptions.php';

/**
 * The Log class is abstract and only used with static function.
 * It's used to store loggers, and select the correct one for
 * a specific context.
 *
 * Example:
 *
 * <code>
 * <?php
 *   Log::addLogger($someLogger, 'Dao');
 *   Log::error('Some message', 'Dao');
 * ?>
 * </code>
 *
 * If you didn't set a logger for a specific context, it will be ignored unless you specified a Log::CATCHALL logger.
 *
 * If you don't pass a context, the context Log::GENERAL is used.
 *
 * You can pass multiple loggers for one context.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Logger
 */
abstract class Log {
  /**
   * The string for the general logger.
   *
   * @var string
   */
  const GENERAL = ' GENERAL ';

  /**
   * The string for the catchall logger.
   *
   * @var string
   */
  const CATCHALL = ' CATCHALL ';

  /**
   * Contains a list of lists of loggers for each context.
   * You can add a logger with Log::addLogger()
   *
   * @var array
   * @see addLogger
   */
  protected static $loggers = array();
  /**
   * This is a simple bool, to be able to check quickly if logging has been enabled or not.
   * If one logger gets added for any context this bool is set to true.
   * This is to prevent having to check an array of loggers, or sanitizing the context if
   * no logger has been added.
   *
   * @var bool
   */
  protected static $isEnabled = false;

  /**
   * Adds a logger in the loggers array to be used in a certain context.
   *
   * @param Logger $logger
   * @param string $context Only letters, numbers, underscore and dash is allowed to avoid errors.
   */
  public static function addLogger($logger, $context = self::GENERAL) {
    if ($context !== self::GENERAL && $context !== self::CATCHALL && (empty($context) || preg_replace('/[^a-z0-9\_\-]/im', '', $context) != $context)) throw new LogException("The context name '$context' is not allowed.");
    self::$isEnabled = true;
    $context = self::sanitizeContext($context);
    if ( ! array_key_exists($context, self::$loggers)) self::$loggers[$context] = array();
    self::$loggers[$context][] = $logger;
  }

  /**
   * Returns a list of logger for a specific context.
   * If no loggers have been added for a specific context, all loggers for the
   * Log::CATCHALL context are returned.
   *
   * @param string $context
   * @return array
   */
  public static function getLoggers($context = self::GENERAL) {
    if ( ! self::$isEnabled) return array();
    $context = self::sanitizeContext($context);
    if (isset(self::$loggers[$context])) return self::$loggers[$context];
    elseif (isset(self::$loggers[self::CATCHALL])) return self::$loggers[self::CATCHALL];
    else return array();
  }

  /**
   * Logs a debug message in the logger for the specific context
   *
   * @param string $message
   * @param string $context
   * @param array $additionalInfo An associative array with more info. Eg: array('content'=>'Some stuff')
   * @return bool true on success, false if no logger specified for the context or the logger below level.
   */
  public static function debug($message, $context = self::GENERAL, array $additionalInfo = array()) {
    return self::doLog('debug', $message, $context, $additionalInfo);
  }

  /**
   * Logs an info message in the logger for the specific context
   *
   * @param string $message
   * @param string $context
   * @param array $additionalInfo An associative array with more info. Eg: array('content'=>'Some stuff')
   * @return bool true on success, false if no logger specified for the context or the logger below level.
   */
  public static function info($message, $context = self::GENERAL, array $additionalInfo = array()) {
    return self::doLog('info', $message, $context, $additionalInfo);
  }

  /**
   * Logs a warning message in the logger for the specific context
   *
   * @param string $message
   * @param string $context
   * @param array $additionalInfo An associative array with more info. Eg: array('content'=>'Some stuff')
   * @return bool true on success, false if no logger specified for the context or the logger below level.
   */
  public static function warning($message, $context = self::GENERAL, array $additionalInfo = array()) {
    return self::doLog('warning', $message, $context, $additionalInfo);
  }

  /**
   * Logs an error message in the logger for the specific context
   *
   * @param string $message
   * @param string $context
   * @param array $additionalInfo An associative array with more info. Eg: array('content'=>'Some stuff')
   * @return bool true on success, false if no logger specified for the context or the logger below level.
   */
  public static function error($message, $context = self::GENERAL, array $additionalInfo = array()) {
    return self::doLog('error', $message, $context, $additionalInfo);
  }

  /**
   * Logs a fatal error message in the logger for the specific context
   *
   * @param string $message
   * @param string $context
   * @param array $additionalInfo An associative array with more info. Eg: array('content'=>'Some stuff')
   * @return bool true on success, false if no logger specified for the context or the logger below level.
   */
  public static function fatal($message, $context = self::GENERAL, array $additionalInfo = array()) {
    return self::doLog('fatal', $message, $context, $additionalInfo);
  }

  /**
   * Calls the specified method on each logger for the specified context provided.
   *
   * @param string $message debug | info | warning | error | fatal
   * @param string $method the method to call on the loggers.
   * @param string $context
   * @param array $additionalInfo
   * @return bool true on success, false if no logger specified for the context or the logger below level.
   */
  protected static function doLog($method, $message, $context, array $additionalInfo) {
    if (count($additionalInfo) === 0) $additionalInfo = null;
    $logged = false;
    foreach (self::getLoggers($context) as $logger) {
      $logged = $logger->$method($message, ($context === self::GENERAL || $context === self::CATCHALL) ? null : $context, $additionalInfo) || $logged;
    }
    return $logged;
  }

  /**
   * Sanitizes a context.
   *
   * @param string $context
   * @return string sanitized context.
   */
  protected static function sanitizeContext($context) {
    if ($context === self::GENERAL || $context === self::CATCHALL) return $context;
    return strtolower($context);
  }

}

