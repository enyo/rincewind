<?php

/**
 * This file contains the abstract DaoResultIterator definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/

/**
 * The Dao Result iterator is returned whenever a query returns more than one row.
 * It implements the default php Iterator Interface, so foreach() and stuff works on it.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/
abstract class DaoResultIterator implements Iterator { }


?>