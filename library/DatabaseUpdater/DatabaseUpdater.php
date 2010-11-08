<?php

/**
 * This file contains the Database updater class definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Database
 * @subpackage Updater
 */

/**
 * DatabaseUpdaterException.
 * 
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Database
 * @subpackage Updater
 */
class DatabaseUpdaterException extends Exception {

}

/**
 * Gets thrown when the database is not initialized yet.
 * 
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Database
 * @subpackage Updater
 */
class DatabaseNotInitializedException extends DatabaseUpdaterException {

}

/**
 * Including the DatabaseUpdate
 */
include dirname(__FILE__) . '/DatabaseUpdate.php';

/**
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Database
 * @subpackage Updater
 */
class DatabaseUpdater {

  /**
   * @var Database
   */
  protected $db;
  /**
   * The location of the update files.
   * 
   * @var string
   */
  protected $updatesPath;
  /**
   * @var Dao
   */
  protected $versionDao;
  /**
   * The number of digits a version number has (0004.sql)
   * 
   * @var int
   */
  protected $versionNumberDigits = 4;
  /**
   * Holds the list of that have been executed, or could be executed.
   * 
   * @var array
   */
  protected $executedUpdates = array();
  /**
   * When update is called, and the database is not setup yet, it will be if this
   * bool is true
   * 
   * @var bool
   */
  private $initializeIfNecessary;
  /**
   * Only the information about the updates gets stored.
   *
   * No updates are actually performed if this is true.
   * @var bool
   */
  private $simulate;
  /**
   * The file which contains the database initialization code.
   * 
   * @var string
   */
  private $initUpdateFilename = '0000.sql';

  /**
   * @param Database $db
   * @param Dao $versionDao
   * @param string $updatesPath The location of the updates
   * @param bool $initializeIfNecessary If the database is not initialized yet, if this is true, the 0000.sql file is used.
   * @param bool $simulate Doesn't execute the sql files if true
   */
  public function __construct($db, $versionDao, $updatesPath, $initializeIfNecessary = false, $simulate = true) {
    $this->db = $db;
    $this->versionDao = $versionDao;
    $this->updatesPath = $updatesPath;
    $this->initializeIfNecessary = $initializeIfNecessary;
    $this->simulate = $simulate;
  }

  /**
   * Goes through the update files, and updates them.
   */
  public function update() {
    try {
      $this->versionDao->find(array('number' => 1)); // Throws exception if database not setup.
    }
    catch (DatabaseQueryException $e) {
      if ($this->initializeIfNecessary) {
        $this->executeUpdate(new DatabaseUpdate($this->initUpdateFilename, 0, 'sql'));
      }
      else {
        throw new DatabaseNotInitializedException();
      }
    }

    $this->db->startTransaction();

    $updates = $this->getAllUpdates();

    foreach ($updates as $update) {
      try {
        $this->executeUpdate($update);
      }
      catch (Exception $e) {
        $this->db->rollback();
        throw $e;
      }
    }

    if (count($this->executedUpdates) > 0) {
      $this->db->commit();
    }
  }

  /**
   * Returns all updates, sorted by their update filename, as array.
   * @return array
   */
  public function getAllUpdates() {
    return array_map(array($this, 'interpretUpdateFilename'), $this->getAllUpdateFilenames());
  }

  /**
   * Turns
   *   0002.enyo.rewrite-of-stuff.sql
   * into
   *   array('number'=>2, 'username'=>'enyo', 'branch'=>'rewrite-of-stuff', 'type'=>'sql')
   * 
   * @param string $filename 
   * @return array containing the different parts of the update.
   */
  public function interpretUpdateFilename($filename) {
    $parts = explode('.', $filename);
    return new DatabaseUpdate($filename, (int) $parts[0], array_pop($parts), isset($parts[1]) ? $parts[1] : null, isset($parts[2]) ? $parts[2] : null);
  }

  /**
   * Returns all update files (except for 0000.sql), sorted by their names, as array.
   * 
   * @return array
   */
  public function getAllUpdateFilenames() {
    $files = array();
    foreach (glob($this->updatesPath . '*.sql') as $fileUri) {
      $files[] = basename($fileUri);
    }
    foreach (glob($this->updatesPath . '*.php') as $fileUri) {
      $files[] = basename($fileUri);
    }
    sort($files);
    $firstFile = array_shift($files);

    if ($firstFile !== $this->initUpdateFilename) throw new DatabaseUpdaterException('The file ' . $this->initUpdateFilename . ' seems to be missing!');

    return $files;
  }

  /**
   * Executes an update.
   *
   * This function also checks if the update hasn't already been inserted in the database.
   *
   * Checks if it's sql or php. If it's PHP the file only gets included.
   *
   * If $this->simulate == true, this function does nothing.
   *
   * @param DatabaseUpdate $update
   */
  protected function executeUpdate($update) {

    if ($update->number !== 0 && $this->updateIsDeprecated($update)) return;

    $this->executedUpdates[] = $update;

    if ($this->simulate) return;

    if ($update->type === 'sql') {

      try {
        $this->db->multiQuery(file_get_contents($this->updatesPath . $update->filename));
      }
      catch (DatabaseQueryException $e) {
        throw new DatabaseUpdaterException('Error in file: ' . $update->filename . "\n" . $e->getMessage());
      }
    }
    else {
      include $this->updatesPath . $update->filename;
    }

    if ($update->number != 0) {
      $this->versionDao->get()->set('number', $update->number)->set('username', $update->username)->set('branch', $update->branch)->save();
    }
  }

  /**
   * Checks if an update hasn't already been inserted in the database.
   *
   * @param DatabaseUpdate $update
   * @return bool true if the update has already been executed.
   */
  public function updateIsDeprecated($update) {
    return ! ! $this->versionDao->find(array('number' => $update->number, 'username' => $update->username, 'branch' => $update->branch));
  }

  /**
   * @return array
   * @uses $executedUpdates
   */
  public function getExecutedUpdates() {
    return $this->executedUpdates;
  }

}

