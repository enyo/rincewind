<?php


/**
 * This file contains the basic DataSource class.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package DataSource
 **/

/**
 * The Exception base class for DataSourceException.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package DataSource
 * @subpackage DataSourceExceptions
 */
class DataSourceException extends Exception { };


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
  protected $returnsIdOnInsert = true;
  
  /**
   * @return bool
   * @uses $returnsIdOnInsert
   */
  public function returnsIdOnInsert() {
    return $this->returnsIdOnInsert;
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
  abstract public function getList($resource, $attributes = null, $sort = null, $offset = null, $limit = null);

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



