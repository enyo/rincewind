<?php

/**
 * This file contains the basic DataSource class.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package DataSource
 */

/**
 * The Exception base class for DataSourceException.
 *
 * The DataSourceException has no message, only error codes.
 *
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package DataSource
 * @subpackage DataSourceExceptions
 */
class DataSourceException extends Exception {

  /**
   * @var string
   */
  public $errorToken;
  /**
   * @var int
   */
  public $httpCode;

  public function __construct($errorToken = '', $httpCode = 200, $code = null, $previous = null) {
    parent::__construct('', $code, $previous);
    $this->errorToken = $errorToken;
    $this->httpCode = $httpCode;
  }

  public function getErrorToken() {
    return $this->errorToken;
  }

  public function getHttpCode() {
    return $this->httpCode;
  }

}

/**
 * Including DataSourceResult
 */
include dirname(__FILE__) . '/DataSourceResult.php';

/**
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package DataSource
 */
abstract class DataSource {

  /**
   * Whether the datasource returns the id, or the whole set on insert.
   *
   * @var bool
   */
  protected $returnsDataOnInsert = true;

  /**
   * @return bool
   * @uses $returnsDataOnInsert
   */
  public function returnsDataOnInsert() {
    return $this->returnsDataOnInsert;
  }

  /**
   * Whether the datasource returns the data or nothing after an update.
   *
   * @var bool
   */
  protected $returnsDataOnUpdate = false;

  /**
   * @return bool
   * @uses $returnsDataOnUpdate
   */
  public function returnsDataOnUpdate() {
    return $this->returnsDataOnUpdate;
  }

  /**
   * Returns a specific record.
   *
   * @param string $resource
   * @param mixed $id The primary key
   * @return string The file content
   */
  abstract public function get($resource, $id);

  /**
   * Returns a record, defined by attributes.
   *
   * @param string $resource
   * @param array $attributes Associative array
   * @return string The file content
   */
  abstract public function find($resource, $attributes);

  /**
   * Returns all objects found.
   *
   * @param string $resource
   * @param array $attributes Associative array
   * @param mixed $sort
   * @param int $offset
   * @param int $limit
   * @return string The file content
   */
  abstract public function getList($resource, $attributes = null, $sort = null, $offset = null, $limit = null, $retrieveTotalRowCount = false);

  /**
   * Inserts the object, and returns the id.
   *
   * @param string $resource
   * @param string $attributes Associative array
   * @return int The new id
   */
  abstract public function insert($resource, $attributes);

  /**
   * Updates the object.
   *
   * @param string $resource
   * @param int $id
   * @param array $attributes Associative array
   * @return string The file content
   */
  abstract public function update($resource, $id, $attributes);

  /**
   * Deletes the object
   *
   * @param string $resource
   * @param int $id
   */
  abstract public function delete($resource, $id);
}

