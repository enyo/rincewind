<?php

/**
 * This file contains the UsernameSanitizer definition.
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
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Sanitizer
 */
class UsernameSanitizer implements Sanitizer {

  /**
   * Makes the username lowercase and trims it.
   * 
   * @param string $string 
   */
  public function sanitize($string) {
    return trim(strtolower($string));
  }

}