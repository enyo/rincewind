<?php

  /**
   * @author     Matthias Loitsch <develop@matthias.loitsch.com>
   * @copyright  Copyright (c) 2009, Matthias Loitsch
   */

  
  class LoggerException extends Exception { }
  
  
  
  
  interface LoggerInterface {
  
    /**
     * Every logger has to get a resource set in the constructre.
     * The resource tells what the logger is logging!
     *
     * @param string $resource
     */
    public function __construct($resource);
  
  
    /**
     * Sets the location logs should be saved to.
     *
     * @param string $path
     * @param string $fileName
     * @param bool $userIncludePath If set, file_put_contents will serach for the file in the include path
     */
    public function setFileUri($path, $fileName, $useIncludePath = false);
  
  
    /**
     * The log function actually logs.
     * 
     * @param string $text
     * @param bool $newLine Whether a new line should be started after the logging.
     */
    public function log($text, $newLine = true);
  
  
  
    /**
     * The debug function does the same as log, but only if the logger is in debug mode.
     * 
     * @param string $text
     * @param bool $newLine Whether a new line should be started after the logging.
     */
    public function debug($text, $newLine = true);
  
  
  }
  
  


?>
