<?php

/**
 * This file contains the MySqlUserDao definition.
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
 * Loading the MySqlDao
 */
include_once dirname(__FILE__) . '/MySqlDao.php';


/**
 * The MySqlUserDao implements the UserDaoInterface.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/
class MySqlUserDao extends MysqlDao implements UserDaoInterface {

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
