<?php

/**
 * This file contains the Json Dao definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/



/**
 * Loading the FileDao
 */
include dirname(dirname(__FILE__)) . '/FileSourceDao.php';

/**
 * Loading the Exceptions
 */
include_once dirname(__FILE__) . '/JsonDaoExceptions.php';

/**
 * Loading the JsonResultIterator
 */
include dirname(__FILE__) . '/JsonResultIterator.php';


/**
 * The JsonDao implementation of a FileSourceDao
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/
class JsonDao extends FileSourceDao {


  /**
   * Intepretes the json content, and returns it.
   * @param string $content The file content
   * @return array
   */
  public function interpretFileContent($content) {
    $return = json_decode($content, true);
    if ($return === null) {
      Log::error('Json could not be decoded.', 'JsonDao', array('content'=>$content));
      throw new JsonDaoException("Json could not be decoded.");
    }
    return $return;
  }

  /**
   * Creates an iterator for a json data hash.
   *
   * @param array $data
   * @return JsonResultIterator
   */
  public function createIterator($data) {
    return new JsonResultIterator($data, $this);
  }

  /**
   * Doesn't work for JSON normally.
   */
  public  function startTransaction() {
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

