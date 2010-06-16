<?php

/**
 * This file contains the FileLogger definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Logger
 **/


/**
 * Include the Logger
 */
if (!class_exists('Logger', false)) include(dirname(__FILE__) . '/Logger.php');


/**
 * The FileLoggerException
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Logger
 * @subpackage LoggerExceptions
 **/
class FileLoggerException extends LoggerException { }



/**
 * The FileLogger is the default implementation for a logger logging to files.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Logger
 **/
class FileLogger extends Logger {


  /**
   * The file to write to.
   *
   * @var string
   */
  protected $fileUri;


  /**
   * A list of strings for the different levels, used in the log file.
   *
   * @var array
   */
  protected $levelStrings = array(
      Logger::DEBUG  =>'DEBUG'
    , Logger::INFO   =>'INFO '
    , Logger::WARNING=>'WARN '
    , Logger::ERROR  =>'ERROR'
    , Logger::FATAL  =>'FATAL'
    );

  /**
   * @param string $fileUri Either the file, or if it doesn't exist the directory should be writable.
   */
  public function __construct($fileUri) {
    if (!file_exists($fileUri)) {
      if (!is_writable(dirname($fileUri))) {
        throw new FileLoggerException("The file '$fileUri' is not writable for logging.");
      }
    }
    elseif (!is_writable($fileUri)) {
      throw new FileLoggerException("The file '$fileUri' is not writable for logging.");
    }

    $this->fileUri = $fileUri;
  }


  /**
   * @return string
   */
  public function getFileUri() {
    return $this->fileUri;
  }


  /**
   * Logs the message to a file.
   *
   * @param string $message
   * @param int $level
   * @param string $context Can contain a context to log for.
   * @param array $additionalInfo An associative array with more info. Eg: array('content'=>'Some stuff')
   */
  public function doLog($message, $level, $context, $additionalInfo) {
    return file_put_contents($this->fileUri, $this->formatMessage($message, $level, $context, $additionalInfo), FILE_APPEND);
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
    $line = date('M d H:i:s ') . $this->levelStrings[$level] . ($context ? ' [' . $context . ']' : '') . ': ' .  $message;
    if ($additionalInfo) {
      $line .= ' (';
      foreach ($additionalInfo as $key=>$value) {
        $line .= ' [' . $key . ' => ' . print_r($value, true) . '] ';
      }
      $line .= ')';
    }
    return $line . "\n";
  }

}


