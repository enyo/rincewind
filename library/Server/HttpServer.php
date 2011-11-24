<?php

/**
 * This file contains the HttpServer class definition
 */

/**
 * Including the HttpServerResponse
 */
require_class('HttpServerResponse', 'Server');

/**
 * The server class is used to configure a server and make requests to it.
 */
class HttpServer {

  /**
   * @var bool
   */
  protected $useHttps;
  /**
   * @var string Can be IP of course
   */
  protected $domain;
  /**
   *
   * @var string Will be appended to domain.
   */
  protected $path;
  /**
   * @var int
   */
  protected $port;
  /**
   * @var int in seconds
   */
  protected $timeout;

  /**
   * @param string $domain Without slash or protocol
   * @param string $path Without slash at the beginning, but a slash in the end.
   * @param int $port 
   * @param bool $useHttps
   * @param int $timeout in seconds
   */
  function __construct($domain, $path = '/', $port = 80, $useHttps = false, $timeout = 60) {
    $this->useHttps = $useHttps;
    $this->domain = $domain;
    $this->path = $path;
    $this->port = $port;
    $this->timeout = $timeout;
  }

  /**
   * @return bool
   */
  public function getUseHttps() {
    return $this->useHttps;
  }

  /**
   * @return string
   */
  public function getDomain() {
    return $this->domain;
  }

  /**
   * @return string
   */
  public function getPath() {
    return $this->path;
  }

  /**
   * @return int
   */
  public function getPort() {
    return $this->port;
  }

  /**
   * @return int
   */
  public function getTimeout() {
    return $this->timeout;
  }

  /**
   * Returns a complete url like this: https://domain/path/query
   * 
   * @param string $query 
   */
  public function getCompleteUrl($query = '') {
    $url = 'http' . ($this->useHttps ? 's' : '') . '://';
    $url .= $this->domain . $this->path . $query;
    return $url;
  }
  
  /**
   * Performs the request
   * @param string $query Eg: product/delete. No leading slash
   * @param array $getParameters Eg: array('id' => 4)
   * @param array $postParameters Same as get. When not null, this will be a post request.
   *                              This can either be a string with the post body, or an
   *                              array, in which case it will be submitted as
   *                              application/x-www-form-urlencoded
   * @param array $headers
   * @param bool $followLocation
   * @return HttpServerResponse 
   */
  public function request($query, $getParameters = null, $postParameters = null, $headers = null, $followLocation = true) {

    $curlHandle = curl_init();

    $url = $this->getCompleteUrl($query);

    if ($getParameters && is_array($getParameters) && count($getParameters) > 0) {
      $url .= '?' . http_build_query($getParameters);
    }


    $logInfo = array(
        'Port' => $this->port,
        'Headers' => $headers ? implode(', ', $headers) : 'NONE',
        'Post' => $postParameters ? $postParameters : 'NONE'
    );

    Log::info("Getting: $url", 'HttpServer', $logInfo);

    
    // No actually perform the request.
    
    curl_setopt($curlHandle, CURLOPT_URL, $url);
    if ($headers) {
      curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $headers);
    }
    curl_setopt($curlHandle, CURLOPT_PORT, $this->port);
    curl_setopt($curlHandle, CURLOPT_TIMEOUT, $this->timeout);
    curl_setopt($curlHandle, CURLOPT_FAILONERROR, false);
    curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, $followLocation);
    // return into a variable
    curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);

    if ($postParameters) {
      curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $postParameters);

      if (is_array($postParameters)) {
        // Submit as application/x-www-form-urlencoded
        curl_setopt($curlHandle, CURLOPT_POST, 1);
      }
    }


    $result = curl_exec($curlHandle);


    $info = curl_getinfo($curlHandle);


    Log::debug("Received file in " . $info['total_time'] . "s", 'HttpServer');


    curl_close($curlHandle);

    $errorCode = $info['http_code'] ? $info['http_code'] : 400;

    if (!$info['http_code'] || $info['http_code'] >= 400) {
      $errorTypes = array(400 => 'Bad Request', 500 => 'Internal Server Error');
      $logInfo['URL'] = $url;
      $logInfo['Error code'] = $errorCode;
      $logInfo['Response'] = (($result !== false && $result !== '') ? $result : 'NONE');
      Log::warning('File could not be downloaded.', 'HttpServer', $logInfo);
    }
    else {
      if ($info['size_download'] <= 1000) {
        Log::debug("Result: " . $result, 'HttpServer');
      }
      else {
        Log::debug("Shortened Result: " . substr($result, 0, 300) . ' [ ... ] ' . substr($result, -300), 'HttpServer');
      }
    }

    return new HttpServerResponse($result, $errorCode, $info['size_download']);
    
  }

}
