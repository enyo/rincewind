<?php

/**
 * Including ServerResponse interface
 */
require_interface('ServerResponse', 'Server');

/**
 * 
 */
class HttpServerResponse implements ServerResponse {

  /**
   * @var int
   */
  protected $httpCode;
  /**
   * @var string
   */
  protected $body;
  /**
   * @var array
   */
  protected $size;

  /**
   * @param string $body
   * @param int $httpCode 
   * @param int $size
   */
  public function __construct($body, $httpCode, $size) {
    $this->body = $body;
    $this->httpCode = $httpCode;
    $this->size = $size;
  }

  /**
   * Returns true if httpCode >= 400
   * @return bool
   */
  public function didFail() {
    return $this->httpCode >= 400;
  }

  /**
   * @return int
   */
  public function getHttpCode() {
    return $this->httpCode;
  }

  /**
   * @return int
   */
  public function getSize() {
    return $this->size;
  }

  /**
   * @return string
   */
  public function getBody() {
    return $this->body;
  }

}