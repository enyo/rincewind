<?php


/**
 * This file contains the basic FileDataSource class.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package DataSource
 **/


/**
 * Loading the data source class
 */
include(dirname(__FILE__) . '/DataSource.php');



/**
 * Loading the file class
 */
if (!class_exists('File', false)) require(dirname(dirname(__FILE__)) . '/File/File.php');



/**
 * The Exception base class for FileDataSourceException.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package DataSource
 * @subpackage DataSourceExceptions
 */
class FileDataSourceException extends DataSourceException { };


/**
 * This data source is used to get files to use for a Dao.
 * It implements basic Dao functionality like view(), list(), update(),
 * insert() and delete(), and uses the FileFactory functions to transport
 * them.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package DataSource
 */
abstract class FileDataSource extends DataSource {

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

}



