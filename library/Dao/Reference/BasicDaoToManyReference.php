<?php

/**
 * BasicDaoToManyReference
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 */
/**
 * Including the basic dao reference.
 */
require_class('BasicDaoReference', dirname(__FILE__) . '/BasicDaoReference.php');

/**
 *
 * Every ToManyReferences should extend this BasicDaoToManyReference
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @see BasicDaoReference
 */
abstract class BasicDaoToManyReference extends BasicDaoReference {
  
}
