<?php

/**
 * This file contains the FileLogger definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Logger
 * */
/**
 * Include the Logger
 */
if ( ! class_exists('Logger', false)) include(dirname(__FILE__) . '/Logger.php');

/**
 * The OutputLogger is the default implementation for a logger logging to output.
 * This logger just writes to the output.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Logger
 * */
class OutputLogger extends Logger {

  /**
   * Writes the log message to output.
   *
   * @param string $message
   * @param int $level
   * @param string $context Can contain a context to log for.
   * @param array $additionalInfo An associative array with more info. Eg: array('content'=>'Some stuff')
   */
  public function doLog($message, $level, $context, $additionalInfo) {
    echo $this->formatMessage($message, $level, $context, $additionalInfo);
  }

  /**
   * Formats a message so it can be included in the file.
   *
   * If you overwrite it, don't forget the newline at the end.
   *
   * @param string $message
   * @param int $level
   * @param string $context Can contain a context to log for.
   * @param array $additionalInfo An associative array with more info. Eg: array('content'=>'Some stuff')
   */
  protected function formatMessage($message, $level, $context, $additionalInfo) {
    $line = $this->levelStrings[$level] . ($context ? ' [' . $context . ']' : '') . ': ' . $message;
    if ($additionalInfo) {
      $line .= ' (';
      foreach ($additionalInfo as $key => $value) {
        $line .= ' [' . $key . ' => ' . print_r($value, true) . '] ';
      }
      $line .= ')';
    }
    return $line . "\n";
  }

}

