<?php

/**
 * This file contains the basic HttpDataSource class.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package DataSource
 * */
/**
 * Loading the data source class
 */
include(dirname(__FILE__) . '/DataSource.php');


/**
 * The Exception base class for HttpDataSourceException.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package DataSource
 * @subpackage DataSourceExceptions
 */
class HttpDataSourceException extends DataSourceException {
  
}

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
abstract class HttpDataSource extends DataSource {

  /**
   * This is the object used to actually get the files.
   * @var HttpServer
   */
  protected $httpServer;

  /**
   * @param HttpServer $httpServer
   */
  public function __construct($httpServer) {
    $this->httpServer = $httpServer;
  }

  /**
   * @return HttpServer
   */
  public function getHttpServer() {
    return $this->httpServer;
  }


  
}

