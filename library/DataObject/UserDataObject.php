<?php

/**
 * This file contains the DataObject definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @subpackage DataObject
 **/

/**
 * Loading the DataObject
 */
include_once dirname(__FILE__) . '/DataObject.php';


/**
 * The UserDataObject is a special DataObject that provides a few additional methods typical for user handling.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @subpackage DataObject
 **/
class UserDataObject extends DataObject {

  /**
   * Encrypts the password
   * 
   * @param string $password
   * @return string Encrypted password
   */
  public function encryptPassword($password) {
    return md5($password);
  }

  /**
   * Checks if the passwords equal
   * 
   * @param string $password
   * @return bool
   */
  public function passwordEquals($password) {
    return $this->password == $this->encryptPassword($password);
  }

  /**
   * Checks if the user is a guest
   * 
   * @return bool
   */
  public function isGuest() {
    return $this->username == 'guest';
  }

}

?>
