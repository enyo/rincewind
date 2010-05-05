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
if (!class_exists('Logger')) include(dirname(__FILE__) . '/Logger.php');


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
   */
  public function doLog($message, $level) {
    return file_put_contents($this->fileUri, $message . "\n", FILE_APPEND);
  }


}


?>
