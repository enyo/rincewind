<?php

	/**
	 * @author     Matthias Loitsch <develop@matthias.loitsch.com>
	 * @copyright  Copyright (c) 2009, Matthias Loitsch
	 */

	require_once('Dao/UserDaoInterface.php');
	require_once('Dao/PostgreSql/PostgreSqlDao.php');
	
	class PostgreSqlUserDao extends PostgreSqlDao implements UserDaoInterface {
	
		public function getByLogin($login) { return $this->get(array('login'=>$login)); }
	
		public function getGuest() {
			$guest = $this->getRawObject();
			$guest->login = 'guest';
			return $guest;
		}
	
	
	}

?>
