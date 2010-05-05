<?php

/**
 * This file contains the definition for a Date.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Date
 **/


/**
 * The Date Exception
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Date
 * @subpackage Exceptions
 **/
class DateException extends Exception { }


/**
 * The Date object is used to store the date and time.
 * I tried extending the built in DateTime object, but overriding the constructor yielded unpleasant results.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Date
 */
class Date {

  /**
   * This format is used with date() when formatting the date.
   * Use setFormat() to overwrite it.
   *
   * @var string
   */
  protected $defaultFormat = 'Y-m-d H:i:s';
  
  /**
   * The time of the date object.
   *
   * @var int
   */
  protected $timestamp;

  /**
   * The time is specified in the constructor
   *
   * @param int|string $time Integer for a timestamp, String for strtotime() or null for current time.
   */
  public function __construct($time = null) {
    if (!$time)                                        $this->timestamp = time();
    elseif (is_numeric($time) && $time == (int) $time) $this->timestamp = (int) $time;
    else                                               $this->timestamp = strtotime($time);
    if (!$this->timestamp) throw new DateException('This date could not be converted: "' . $time . '"');
  }

  /**
   * Formats the timestamp, and returns it.
   *
   * @param string $format The format to be used (the string passed to date()). If empty $this->defaultFormat is used.
   * @return string The formatted string
   */
  public function format($format = null) {
    return date($format === null ? $this->defaultFormat : $format, $this->timestamp);
  }



  /**
   * Simply returns the timestamp.
   * @return int
   */
  public function getTimestamp() { return $this->timestamp; }


  /**
   * @param string $format Sets the defaultFormat.
   */
  public function setFormat($format) { $this->defaultFormat = $format; }

  /**
   * Calls format()
   *
   * @see format()
   */
  public function __toString() { return $this->format(); }

}


?>
