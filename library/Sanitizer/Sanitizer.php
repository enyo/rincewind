<?php

/**
 * This file contains the Sanitizer interface definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Sanitizer
 */

/**
 * The sanitizer interface.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Sanitizer
 */
interface Sanitizer {

  /**
   * Sanitizes the string
   * @param string $string
   * @return string The sanitized string.
   */
  public function sanitize($string);
}