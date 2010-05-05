<?php


  /**
   * @author     Matthias Loitsch <develop@matthias.loitsch.com>
   * @copyright  Copyright (c) 2009, Matthias Loitsch
   */


  require_once('Logger/LoggerFactoryInterface.php');
  require_once('Logger/AbstractLogger.php');
  require_once('Logger/Logger.php');


  /**
   * The logger factory exists, so all information needed for a logger is stored in here, and the factory
   * can be passed to other objects (preferably via setLoggerFactory()).
   * Each object can then decide how many logger to use, and set the resource name individually, without
   * having to care about the location the file is going to be saved to.
   *
   * Most of the methods are the same as for the Logger, except of course log() and debug().
   * In general you set up the loggerFactory as you would a logger, and then get the logger out of it via getLogger()
   */
  class LoggerFactory extends AbstractLogger implements LoggerFactoryInterface {
    
    /**
     * @param string $path
     * @param string $fileName
     * @param bool $useIncludePath
     * @param bool $isDebugging
     */
    public function __construct($path = '', $fileName = '', $useIncludePath = false, $isDebugging = false) {
      $this->setFileUri($path, $fileName, $useIncludePath);
      if ($isDebugging) $this->setToDebugMode();
    }


    /**
     * Get a logger with the 
     * 
     * @param string $resource The resource to set the logger to.
     */
    public function getLogger($resource) {
      $logger = new Logger($resource, $this->path, $this->fileName, $this->useIncludePath, $this->isDebugging);
      $logger->setDateFormat($this->dateFormat);
      if ($this->disabled) { $logger->disable(); }
      return $logger;
    }
  
    public function apply($objectList) {
      foreach($objectList as $o) {
        $o->setLoggerFactory($this);
      }
    }
  
  
  }


?>
