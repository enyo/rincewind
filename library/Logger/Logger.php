<?php


  /**
   * @author     Matthias Loitsch <develop@matthias.loitsch.com>
   * @copyright  Copyright (c) 2009, Matthias Loitsch
   */


  require_once('Logger/LoggerInterface.php');
  require_once('Logger/AbstractLogger.php');

  
  
  class Logger extends AbstractLogger implements LoggerInterface {

    /**
     * The resource defines what the logger is logging for.
     * It's just a string representation.
     *
     * @var string
     */
    protected $resource;

  
    /**
     * @param string $resource
     * @param string $path
     * @param string $fileName
     * @param bool $useIncludePath
     * @param bool $isDebugging
     */
    public function __construct($resource, $path = '', $fileName = '', $useIncludePath = false, $isDebugging = false) {
      $this->resource = $resource;
      $this->setFileUri($path, $fileName, $useIncludePath);
      if ($isDebugging) $this->setToDebugMode();
    }

    /**
     * Log!
     *
     * @param string $text
     * @param bool $newline Whether to start a newline after
     */
    public function log($text, $newLine = true) {
      if ($this->isDisabled()) return;

      $logLine = date($this->dateFormat) . ' ' . $this->resource . ': ' . $text . ($newLine ? "\n" : '');

      $flags = FILE_APPEND;
      if ($this->useIncludePath) { $flags |= FILE_USE_INCLUDE_PATH; }

      if (!file_put_contents($this->path . $this->fileName, $logLine, $flags)) throw new LoggerException('Unable to log.');
    }
  
  
    /**
     * The same as log, but only logs when in debug mode, and puts <debug> in front of the text.
     *
     * @param string $text
     * @param bool $newline
     */
    public function debug($text, $newLine = true) {
      if ($this->isDisabled() || !$this->isDebugging()) return;
      $this->log('<debug> ' . $text, $newLine);
    }
  
  
  
  }
  

?>
