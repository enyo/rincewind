<?php

require_once('DataObject/DataObject.php');

class UserDataObject extends DataObject
{

	public function encryptPassword($password) { return md5($password); }
	public function passwordEquals($password) { return $this->password == $this->encryptPassword($password); }
	public function isGuest() { return $this->login == 'guest'; }

}

?>
