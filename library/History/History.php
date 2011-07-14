<?php

/**
 * The file for the History
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package History
 */

/**
 * This class stores history information, so we can send a user back to any site in history.
 * The history is stored in the $_SESSION['history'] array, where the last index is the last visited url.
 * To store an url in the history, call addUrl()
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package History
 */
class History {

  /**
   * Number of urls to keep in history
   * @var int
   */
  protected $historyLength = 10;

  /*   * #@+
   * Different url positions to get.
   *
   * @var mixed
   */
  const FIRST = '__first__';
  const LAST = '__last__';
  const PREVIOUS = '__previous__';
  /*   * #@- */

  /**
   * Makes sure the session history array exists.
   */
  public function __construct() {
    // Make sure the SESSION array exists.
    if (!isset($_SESSION['history']) || !is_array($_SESSION['history'])) {
      $this->clear();
    }
  }

  /**
   * @param int $length
   * @see $historyLength
   */
  public function setHistoryLength($length) {
    $this->historyLength = (int) $length;
    $this->removeExcessUrls();
  }

  /**
   * @return int
   * @see $historyLength
   */
  public function getHistoryLength() {
    return $this->historyLength;
  }

  /**
   * @param string $url
   */
  public function addUrl($url) {

    if (end($_SESSION['history']) != $url) {
      // Don't insert urls twice.
      $_SESSION['history'][] = $url;
      $this->removeExcessUrls();
    }
  }

  /**
   * Ensures that only the allowed number of urls are stored in the history.
   * Be careful: This function doesn't test if the Session array exists.
   */
  protected function removeExcessUrls() {
    while (count($_SESSION['history']) > $this->historyLength) {
      array_shift($_SESSION['history']);
    }
  }

  /**
   * Returns the last visited url in history.
   * This is a wrapper for History::get(History::PREVIOUS);
   *
   * @return string
   * @see get()
   */
  public function getPreviousUrl() {
    return $this->get(self::PREVIOUS);
  }

  /**
   * Gets a history object.
   * This can be an index, to access some specific index of the history array, or one of:
   * History::FIRST, History::LAST, History::PREVIOUS
   *
   * Returns null if not available.
   *
   * @param mixed $position
   */
  public function get($position) {
    if (!isset($_SESSION['history']))
      return null;

    if (!is_int($position)) {
      switch ($position) {
        case self::FIRST:
          $position = 0;
          break;
        case self::LAST:
          if (count($_SESSION['history']) == 0)
            return null;
          $position = count($_SESSION['history']) - 1;
          break;
        case self::PREVIOUS:
          if (count($_SESSION['history']) < 2)
            return null;
          $position = count($_SESSION['history']) - 2;
          break;
        default:
          trigger_error('Unknown History position `' . $position . '`', E_USER_ERROR);
      }
    }

    return isset($_SESSION['history'][$position]) ? $_SESSION['history'][$position] : null;
  }

  /**
   * Returns an array containing the complete history.
   * The last index, is the latest url inserted.
   * Index 0 is the oldest url.
   * @return array
   */
  public function getAll() {
    return isset($_SESSION['history']) ? $_SESSION['history'] : array();
  }

  /**
   * Sets an empty array in the session.
   */
  public function clear() {
    $_SESSION['history'] = array();
  }

  /**
   * Prints the history for debugging
   */
  public function printHistory() {
    echo '<pre>';
    print_r($_SESSION['history']);
    echo '</pre>';
  }

}

