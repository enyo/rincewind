<?php


/**
 * This file contains the JsonResultIterator definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/

/**
 * Loading the DaoResultIterator
 */
include dirname(dirname(__FILE__)) . '/FileSourceResultIterator.php';

/**
 * This class implements the JsonResultIterator.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 */ 
class JsonResultIterator extends FileSourceResultIterator { }


?>
