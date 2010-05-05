<?php


/**
 * This file contains the MysqlResultIterator definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/

/**
 * Loading the SqlResultIterator
 */
include dirname(dirname(__FILE__)) . '/SqlResultIterator.php';

/**
 * The class does nothing different then the SqlResultIterator, but is used everytime a Mysql request is made.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 */ 
class MysqlResultIterator extends SqlResultIterator { }


?>
