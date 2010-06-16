<?php

  require_once('FileRetriever/FileRetrieverInterface.php');

  /**
   * @author     Matthias Loitsch <develop@matthias.loitsch.com>
   * @copyright  Copyright (c) 2009, Matthias Loitsch
   *
   * The FileRetriever
   */
  class FileRetriever implements FileRetrieverInterface {
  
    protected $address;
    protected $seed;

    /**
     * @var array Those are additional GET vars, that are always added a request.
     */
    protected $additionalGetVars = array();

    /**
     * @var array Those are additional POST vars, that are always added to a request.
     */   
    protected $additionalPostVars = array();

    /**
     * @var int The number of seconds to allow cURL functions to execute
     */
    protected $timeout = 60;

  

    /**
     * @param string $address
     * @param array $additionalGetVars One dimensional
     * @param array $additionalPostVars One dimensional
     */
    public function __construct($address = null, $additionalGetVars = array(), $additionalPostVars = array()) {
      $this->setAddress($address);
      $this->setAdditionalGetVars($additionalGetVars);
      $this->setAdditionalPostVars($additionalPostVars);
    }

    /**
     * @param int $seconds
     */
    public function setTimeout($seconds) { $this->timeout = $seconds; }

  
  
    public function setAddress($address)         { $this->address = $address; }
    public function setSeed($seed)               { $this->seed = $seed; }
    public function setAdditionalGetVars($vars)  { $this->additionalGetVars = $vars; }
    public function setAdditionalPostVars($vars) { $this->additionalPostVars = $vars; }


    /**
     * @param array $getVars One dimensional
     * @param array $postVars One dimensional
     */
    public function getFile($getVars = null, $postVars = null, $address = null) {

      $address = $address ? $address : $this->address;

      $getVars = array_merge($getVars ? $getVars : array(), $this->additionalGetVars);
      $postVars = array_merge($postVars ? $postVars : array(), $this->additionalPostVars);

      $url = $this->generateUrl($address, $getVars);
      $postString = $this->generateVariableString($postVars);
  

      $this->debug($address);
      $this->debug("  ↳ GET Vars: " . serialize($getVars));
      $this->debug("  ↳ POST Vars: " . serialize($postVars));
      $this->debug("  ↳ Full address: " . $url);
      $this->debug("    ↳ SubmittedPost: " . $postString);
  
      $curlHandle = curl_init();
  
      curl_setopt($curlHandle, CURLOPT_TIMEOUT, $this->timeout);
      // set url to post to
      curl_setopt($curlHandle, CURLOPT_URL, $url); 
      curl_setopt($curlHandle, CURLOPT_FAILONERROR, 1);
      // allow redirects
      curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, 0);
      // return into a variable
      curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($curlHandle, CURLOPT_POST, 1);
      curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $postString);
  
      $result = curl_exec($curlHandle);
  
      curl_close($curlHandle); 
    
      return $result;
    }
  
  
    protected function generateUrl($address, $getVars) {
      return $address . '?' . $this->generateVariableString($getVars);
    }


    protected function generateVariableString($array) {
      $string = '';
      foreach ($array as $index=>$value) {
        if (is_array($value)) { throw new FileRetrieverException(); }
        $string .= '&' . rawurlencode($index) . '=' . rawurlencode($value);
      }
      return $string;
    }




    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @param LoggerFactory $loggerFactory
     */
    public function setLoggerFactory($loggerFactory) { $this->setLogger($loggerFactory->getLogger('FileRetriever')); }
    /**
     * @param Logger $logger
     */
    public function setLogger($logger)               { $this->logger = $logger; }
    /**
     * @param string $message
     */
    public function log($message)                    { if ($this->logger) $this->logger->log($message); }
    /**
     * @param string $message
     */
    public function debug($message)                  { if ($this->logger) $this->logger->debug($message); }


  
  }
  


