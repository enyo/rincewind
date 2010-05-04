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
 * The Exception base class for DataSourceException.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package File
 * @subpackage FileExceptions
 */
class DataSourceException extends Exception { };

/**
 * The Exception base class for FileDataSourceException.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package File
 * @subpackage FileExceptions
 */
class FileDataSourceException extends DataSourceException { };


/**
 * This factory is used to get files to use for a Dao.
 * It implements basic Dao functionality like view(), list(), update(),
 * insert() and delete(), and uses the FileFactory functions to transport
 * them.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package File
 */
abstract class FileDataSource {

  /**
   * This is the object used to actually get the files.
   * @var FileRetriever
   */
  protected $fileRetriever;

  /**
   * This is the base url to connect to the backend.
   * @var string
   */
  protected $baseUrl;

  /**
   * This is the port used to connect to the backend.
   * @var int
   */
  protected $port;

  /**
   * @param FileRetriever $fileRetriever
   * @param string $baseUrl
   * @param int $port
   */
  public function __construct($fileRetriever, $baseUrl, $port = 80) {
    $this->fileRetriever = $fileRetriever;
    $this->baseUrl = $baseUrl;
    $this->port = $port;
  }

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