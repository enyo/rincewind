<?php

/**
 * The file for the message delegate
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2011, Matthias Loitsch
 * @package Controller
 * @subpackage Message
 */

/**
 * The MessageDelegate ist used to store messages that should appear on screen.
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2011, Matthias Loitsch
 * @package Controller
 * @subpackage Message
 */
interface MessageDelegate {

  /**
   * If you pass an array, every value will be added as message. This way
   * you can pass multiple messages at once.
   *
   * @param string|array $message
   */
  public function addErrorMessage($message);

  /**
   * If you pass an array, every value will be added as message. This way
   * you can pass multiple messages at once.
   *
   * @param string|array $message
   */
  public function addSuccessMessage($message);

  /**
   * @return array
   */
  public function getErrorMessages();

  /**
   * @return array
   */
  public function getSuccessMessages();

}

