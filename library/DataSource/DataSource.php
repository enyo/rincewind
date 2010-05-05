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
   * Returns one object.
   *
   * @param string $resource
   * @param array $attributes Associative array
   * @return string The file content
   */
  abstract public function view($resource, $attributes);

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
  abstract public function viewList($resource, $attributes, $sort, $offset, $limit);

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



?>
