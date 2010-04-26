<?php


/**
 * This file contains the basic FileFactory class.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package File
 **/

/**
 * Loading the file class
 */
if (!class_exists('File')) require('File/File.php');

/**
 * The Exception base class for DaoFileFactoryException.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package File
 * @subpackage FileExceptions
 */
class DaoFileFactoryException extends FileFactoryException { };


/**
 * This factory is used to get files to use for a Dao.
 * It implements basic Dao functionality like view(), list(), update(),
 * insert() and delete(), and uses the FileFactory functions to push them
 * to the datasource.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package File
 */
abstract class DaoFileFactory extends FileFactory {

	/**
	 * Returns ONE object.
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