<?php

/**
 * This file contains all Database Exceptions.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Database
 * @subpackage Exceptions
 **/


/**
 * This is the root Exception for SQL Connections
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Database
 * @subpackage Exceptions
 */
class DatabaseException extends Exception { }

/**
 * If the database can not connect it throws this exception.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Database
 * @subpackage Exceptions
 */
class DatabaseConnectionException extends DatabaseException { }

/**
 * If a query fails, this exception gets thrown.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Database
 * @subpackage Exceptions
 */
class DatabaseQueryException extends DatabaseException { }

