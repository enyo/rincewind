<?php

/**
 * This file contains the MysqlUserDao definition.
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
 * Loading the MysqlDao
 */
include_once dirname(__FILE__) . '/MysqlDao.php';


/**
 * The MysqlUserDao implements the UserDaoInterface.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/
class MysqlUserDao extends MysqlDao implements UserDaoInterface {

  /**
   * Returns the user by username
   *
   * @param string $username
   * @return DataObject
   */
  public function getByUsername($username) {
    return $this->get(array('username'=>$username));
  }

  /**
   * Returns a raw object with 'guest' as username
   *
   * @return DataObject
   */
  public function getGuest() {
    return $this->getRawObject()->set('username', 'guest');
  }

}

?>
