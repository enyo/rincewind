<?php

/**
 *
 * @author Matias Meno <matias.meno@i-netcompany.com>
 * @copyright Copyright (c) 2011, I-Netcompany
 * @package NotificationCenter
 */
class NotificationCenter {

  /**
   * @var array
   */
  protected $errors = array();
  /**
   * @var array
   */
  protected $successes = array();

  /**
   * If you pass an array, every value will be added as message. This way
   * you can pass multiple messages at once.
   *
   * @param string|array $message
   */
  public function addError($message) {
    $this->errors = array_merge($this->errors, (array) $message);
  }

  /**
   * If you pass an array, every value will be added as message. This way
   * you can pass multiple messages at once.
   *
   * @param string|array $message
   */
  public function addSuccess($message) {
    $this->successes = array_merge($this->successes, (array) $message);
  }

  /**
   * @return array
   */
  public function getErrors() {
    return $this->errors;
  }

  /**
   * @return array
   */
  public function getSuccesses() {
    return $this->successes;
  }

  /**
   * @return string
   */
  public function getLastError() {
    if (count($this->errors) > 0) {
      return $this->errors[count($this->errors) - 1];
    }
    else {
      return null;
    }
  }

  /**
   * @return string
   */
  public function getLastSuccess() {
    if (count($this->successes) > 0) {
      return $this->successes[count($this->successes) - 1];
    }
    else {
      return null;
    }
  }

}

