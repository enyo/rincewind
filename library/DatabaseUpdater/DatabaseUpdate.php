<?php

/**
 * This file contains the DatabaseUpdate class definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Database
 * @subpackage Updater
 */

/**
 * Holds all necessary information about a database update, and is used by the
 * DatabaseUpdater
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Database
 * @subpackage Updater
 */
class DatabaseUpdate {

  /**
   * @var string
   */
  public $filename;
  /**
   * @var int
   */
  public $number;
  /**
   * @var string sql | php
   */
  public $type;
  /**
   * @var string
   */
  public $username;
  /**
   * @var string
   */
  public $branch;

  public function __construct($filename, $number, $type, $username = null, $branch = null) {
    $this->filename = $filename;
    $this->number = $number;
    $this->type = $type;
    $this->username = $username;
    $this->branch = $branch;
  }

}
