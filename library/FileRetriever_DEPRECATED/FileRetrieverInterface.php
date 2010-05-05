<?php


  class FileRetrieverException extends Exception { }


  /**
   * @author     Matthias Loitsch <develop@matthias.loitsch.com>
   * @copyright  Copyright (c) 2009, Matthias Loitsch
   *
   * The interface for a normal FileRetriever
   */
  interface FileRetrieverInterface {

    /**
     * @param string $address This is used if no address is passed when calling getFile
     */
    public function setAddress($address);
  
    /**
     * @param array $getVars One dimensional
     * @param array $postVars One dimensional
     * @param bool $address If null, the stored address via setAddress is used.
     */
    public function getFile($getVars = array(), $postVars = array(), $address = null);

    public function setLoggerFactory($loggerFactory);
    public function setLogger($logger);
    public function log($text);
    public function debug($text);

  }



?>
