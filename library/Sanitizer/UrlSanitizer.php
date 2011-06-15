<?php

/**
 * This file contains the UrlSanitizer definition.
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
 * The Url sanitizer removes characters that aren't allowed in url.
 * The difference to the php rawurlencode() function is, that instead of changing
 * the characters to real url characters (like %20), it just replaces them. 
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Sanitizer
 */
class UrlSanitizer implements Sanitizer {

  /**
   * Replaces all invalid url characters with _
   * @param string $string 
   */
  public function sanitize($string) {
    // Get the nearest ascii alternative for foreign chars.
    $string = iconv("utf-8", "ascii//TRANSLIT", $string);
    // Replace all 's with nothing so It's becomes Its instead of It-s
    // also: iconv replaces ü as u"
    $string = str_replace('\'', '', $string);

    // Replace all other characters with - and make sure there is no - at the beginning
    // nor end.
    $string = trim(preg_replace('/([^a-z0-9_\-])/i', '-', $string), '-');

    // Remove double dashes
    $string = preg_replace('/-{2,}/', '-', $string);

    return $string;
  }

}