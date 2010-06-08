<?php

/**
 * This file contains the UserDaoInterface definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/

/**
 * Loading the DaoInterface.
 * Since I can not be sure no other Dao has been loaded before I have to use include_once here.
 */
include_once dirname(__FILE__) . '/DaoInterface.php';


/**
 * The UserDaoInterface defines a few methods useful for user daos.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/
interface UserDaoInterface extends DaoInterface {

  /**
   * Return a user by login
   *
   * @param string $username
   * @return Record
   */
  public function getByUsername($username);

  /**
   * Get a raw object with username 'guest'
   *
   * @return Record
   */
  public function getGuest();

}
  
?>
