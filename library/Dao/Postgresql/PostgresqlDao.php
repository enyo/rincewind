<?php

/**
 * This file contains the PostgresqlDao definition.
 * This file is not yet finished!
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/


/*
	require_once('Dao/SqlDao.php');
	require_once('Dao/Postgresql/PostgresqlResultIterator.php');
	
	
	class PostgresqlDao extends SqlDao {
	
		
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
			return new PostgresqlResultIterator($result, $this, $totalRowCount);
		}
	
	
	}
*/
?>
