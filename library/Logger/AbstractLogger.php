<?php


  /**
   * @author     Matthias Loitsch <develop@matthias.loitsch.com>
   * @copyright  Copyright (c) 2009, Matthias Loitsch
   */


  require_once('Logger/LoggerInterface.php');
  
  
  
  /**
   * This is the base for a logger or a logger factory
   */
  abstract class AbstractLogger {

    /**
     * @var bool
     */
    protected $disabled = false;
    /**
     * @var bool
     */
    protected $isDebugging;


    /**
     * @var string
     */
    protected $path;
    /**
     * @var string
     */
    protected $fileName;
    /**
     * @var bool
     */
    protected $useIncludePath;
  
    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';
  
  

    /**
     * Disables the logger
     */
    public function disable() { $this->disabled = true; }

    /**
     * @return bool
     */
    public function isDisabled() { return $this->disabled; }

    /**
     * Set the logger to debug mode.
     */
    public function setToDebugMode() { $this->isDebugging = true; }

    /**
     * Stop debugging...
     */
    public function stopDebugging() { $this->isDebugging = false; }

    /**
     * @return bool
     */
    public function isDebugging() { return $this->isDebugging; }


    /**
     * The logger writes the date in every line it loggs.
     * This defines the format it should be in.
     *
     * @param string $format
     */
    public function setDateFormat($format) { $this->dateFormat = $format; }
  
  
    /**
     * Where to save the log info to.
     *
     * @param string $path
     * @param string $fileName
     * @param bool $useIncludePath
     */
    public function setFileUri($path, $fileName, $useIncludePath = false) {
      $this->path = empty($path) ? '' : $path . '/';
      $this->fileName = $fileName;
      $this->useIncludePath = $useIncludePath;
    }

  
  }
  

?>
