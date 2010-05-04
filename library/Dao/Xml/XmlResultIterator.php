<?php


/**
 * This file contains the XmlResultIterator definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/

/**
 * Loading the DaoResultIterator
 */
include dirname(dirname(__FILE__)) . '/FileResultIterator.php';

/**
 * This class implements the XmlResultIterator.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 */	
class XmlResultIterator extends FileResultIterator { }


?>