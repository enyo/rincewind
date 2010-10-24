<?php

/**
 * This file contains the Json Dao definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 */
/**
 * Loading the FileDao
 */
include dirname(__FILE__) . '/FileDao.php';

/**
 * The Exception base class for JsonDaoExceptions.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @subpackage Exceptions
 */
class JsonDaoException extends DaoException {

}

/**
 * The JsonDao implementation of a FileSourceDao
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 */
class JsonDao extends FileDao {

  /**
   * Intepretes the json content, and returns it.
   * @param string $content The file content
   * @return array
   */
  public function interpretFileContent($content) {
    $return = json_decode($content, true);
    if ($return === null) {
      Log::error('Json could not be decoded.', 'JsonDao', array('content' => $content));
      throw new JsonDaoException("Json could not be decoded.");
    }
    return $return;
  }

  /**
   * Doesn't work for JSON normally.
   */
  public function startTransaction() {
    throw new JsonDaoException('Transactions not implemented.');
  }

  /**
   * Returns an array.
   * 
   * If the value is already an array, it just returns it. Otherwise it puts the
   * value in an array.
   * 
   * @param mixed $value
   * @return array
   */
  public function exportSequence($value) {
    return is_array($value) ? $value : array($value);
  }

}

