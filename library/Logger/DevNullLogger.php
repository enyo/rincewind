<?php

/**
 * This file contains the FileLogger definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Logger
 * */
/**
 * Include the Logger
 */
require_class('Logger');

/**
 * The DevNull Logger just trashes output.
 * If you want a catchall logger, but you want a specific context excluded, just
 * register a DevNullLogger for that context.
 * 
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Logger
 * */
class DevNullLogger extends Logger {

  /**
   * Does nothing.
   *
   * @param string $message
   * @param int $level
   * @param string $context Can contain a context to log for.
   * @param array $additionalInfo An associative array with more info. Eg: array('content'=>'Some stuff')
   */
  public function doLog($message, $level, $context, $additionalInfo) {
    return;
  }

}

