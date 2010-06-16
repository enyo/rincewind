<?php

/**
 * This file contains the PostgresqlUserDao definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/



/**
 * Loading the UserDaoInterface
 */
include dirname(dirname(__FILE__)) . '/UserDaoInterface.php';

/**
 * Loading the PostgresqlDao
 */
include_once dirname(__FILE__) . '/PostgresqlDao.php';


/**
 * The PostgresqlUserDao implements the UserDaoInterface.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/
class PostgresqlUserDao extends PostgresqlDao implements UserDaoInterface {

  /**
   * Returns the user by username
   *
   * @param string $username
   * @return Record
   */
  public function getByUsername($username) {
    return $this->get(array('username'=>$username));
  }

  /**
   * Returns a raw object with 'guest' as username
   *
   * @return Record
   */
  public function getGuest() {
    return $this->getRawRecord()->set('username', 'guest');
  }

}

