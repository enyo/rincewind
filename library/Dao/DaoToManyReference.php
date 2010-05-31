<?php

/**
 * This file contains the DaoToManyReference definition that's used to describe
 * references to many other records in a datasource.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/



/**
 * A DaoToManyReference describes references to many resources.
 *
 * The DaoToManyReference works exactly as the DaoReference, but works with
 * arrays that either contain ids, or data hashes.
 *
 * In a DaoToManyReference the localKey has to be of type Dao::SEQUENCE
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @see DaoReference
 **/
class DaoToManyReference extends DaoReference { }


?>