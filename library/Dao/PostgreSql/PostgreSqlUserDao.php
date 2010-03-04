<?php

/**
 * This file contains the PostgreSqlUserDao definition.
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
 * Loading the PostgreSqlDao
 */
include_once dirname(__FILE__) . '/PostgreSqlDao.php';


/**
 * The PostgreSqlUserDao implements the UserDaoInterface.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/
class PostgreSqlUserDao extends PostgreSqlDao implements UserDaoInterface {

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
