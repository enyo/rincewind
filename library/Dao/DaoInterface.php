<?php
	
	/**
	 * @author     Matthias Loitsch <develop@matthias.loitsch.com>
	 * @copyright  Copyright (c) 2009, Matthias Loitsch
	 *
	 * The Dao interface
	 */
	interface DaoInterface {
	
		public function getById($id);
	
		public function getAll();
	
	
		public function insert($object);
		public function update($object);
		public function delete($object);
		public function deleteById($id);
	
	
		public function beginTransaction();
		public function commit();
		public function rollback();
	
	}

?>
