<?php


/**
 * Returned by a Server
 */
interface ServerResponse {

  /**
   * Returns the body of the response.
   * 
   * return string
   */
  public function getBody();
  
  /**
   * Whether or not the request failed.
   * 
   * return bool
   */
  public function didFail();
  
}