<?php

/**
 * This file contains the PostgresqlDao definition.
 * This file is not yet finished!
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/



/**
 * Loading the SqlDao
 */
include dirname(dirname(__FILE__)) . '/SqlDao.php';


/**
 * Loading the PostgresqlResultIterator
 */
include dirname(__FILE__) . '/PostgresqlResultIterator.php';


/**
 * The PostgresqlDao implementation of a SqlDao
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/
abstract class PostgresqlDao extends SqlDao {



}
