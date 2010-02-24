<?php


	/**
	 * @author     Matthias Loitsch <develop@matthias.loitsch.com>
	 * @copyright  Copyright (c) 2009, Matthias Loitsch
	 */

	require_once('Dao/SqlDao.php');
	require_once('Dao/PostgreSql/PostgreSqlResultIterator.php');
	
	
	class PostgreSqlDao extends SqlDao {
	
		
		protected function escapeString($string) {
			return pg_escape_string($this->db->getResource(), $string);
		}
	
		public function escapeColumn($column) { return '"' . $this->escapeString($column) . '"'; }
		public function exportString($text)   { return "'" . $this->escapeString($text) . "'"; }
	
		
		protected function getLastInsertId() {
			$id = $this->db->query("select currval('" . $this->escapeString($this->tableName) . "_id_seq') as id");
			$id = $id->fetchArray();
			return $id['id'];
		}
	
		protected function createIterator($result, $totalRowCount) {
			return new PostgreSqlResultIterator($result, $this, $totalRowCount);
		}
	
	
	}

?>
