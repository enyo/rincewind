<?php

/**
 * This file contains the PostgreSqlResultIterator definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/

/**
 * Loading the SqlDao
 */
include dirname(dirname(__FILE__)) . '/SqlResultIterator.php';

/**
 * The class does nothing different then the SqlResultIterator, but is used everytime a PostgreSql request is made.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 */
class PostgreSqlResultIterator extends SqlResultIterator { }

?>