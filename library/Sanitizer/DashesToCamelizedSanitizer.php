<?php

/**
 * This file contains the DashToCamelizedSanitizer definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Sanitizer
 */
/**
 * Including interface
 */
require_interface('Sanitizer');

/**
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Sanitizer
 */
class DashesToCamelizedSanitizer implements Sanitizer {

  /**
   * @var bool
   */
  private $ucFirst;

  /**
   * @param bool $ucFirst Forces the first character to be uppercase.
   */
  function __construct($ucFirst = false) {
    $this->ucFirst = $ucFirst;
  }

  /**
   * Converts strings like: my-account to myAccount
   * @param string $string 
   */
  public function sanitize($string) {
    $string = preg_replace('/-([a-z0-9])/e', 'strtoupper("$1")', $string);

    if ($this->ucFirst) $string = ucfirst($string);

    return $string;
  }

}