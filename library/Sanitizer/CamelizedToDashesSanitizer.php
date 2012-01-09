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
class CamelizedToDashesSanitizer implements Sanitizer {

  /**
   * Converts strings like: MyAccount to my-account
   * @param string $string
   */
  public function sanitize($string) {
    $string = preg_replace('/([A-Z])/e', 'strtolower("-$1")', lcfirst($string));
    return $string;
  }

}