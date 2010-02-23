<?php

	/**
	 * @author     Matthias Loitsch <develop@matthias.loitsch.com>
	 * @copyright  Copyright (c) 2009, Matthias Loitsch
	 */

	require_once('Dao/SqlDao.php');
	require_once('Dao/MySql/MySqlResultIterator.php');
	
	
	class MySqlDao extends SqlDao {
	
		protected function escapeString($string) {
			return mysql_real_escape_string($string, $this->db->getResource());
		}
		
		public function escapeColumn($column) { return '`' . $this->escapeString($column) . '`'; }
		public function exportString($text)   { return "'" . $this->escapeString($text) . "'"; }
		
		
		protected function getLastInsertId() {
			$id = $this->db->query("select LAST_INSERT_ID() as id");
			$id = $id->fetchArray();
			return $id['id'];
		}
	
		protected function createIterator($result, $totalRowCount) {
			return new MySqlResultIterator($result, $this, $totalRowCount);
		}
	
	
	}

?>
