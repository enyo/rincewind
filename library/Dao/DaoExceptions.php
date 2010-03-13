<?php


/**
 * This file defines all DaoExceptions.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @subpackage Exceptions
 */


/**
 * The Exception base class for DaoExceptions.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @subpackage Exceptions
 */
class DaoException extends Exception { }

/**
 * The WrongValue Exception. To be honest: I don't know anymore what that exception's for.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @subpackage Exceptions
 */
class DaoWrongValueException extends DaoException { }

/**
 * The Exception if some column types are not supported.
 * When importing a value, and your Dao does not support it, throw this exception.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @subpackage Exceptions
 */
class DaoNotSupportedException extends DaoException { }


/**
 * The Exception if a query that should return a result gets nothing.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @subpackage Exceptions
 */
class DaoNotFoundException extends DaoException { }

?>