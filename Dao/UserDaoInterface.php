<?php

	/**
	 * @author     Matthias Loitsch <develop@matthias.loitsch.com>
	 * @copyright  Copyright (c) 2009, Matthias Loitsch
	 */

	require_once("Dao/DaoInterface.php");
	
	interface UserDaoInterface extends DaoInterface {
		public function getByLogin($login);
		public function getGuest();
	}
	
?>
