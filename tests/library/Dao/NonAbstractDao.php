<?php


/**
 * You can extend this dao if you only need to overwrite some of the abstract methods.
 */
class NonAbstractDao extends Dao {

  protected function generateSortString($sort)                         { return null; }
  protected function convertRemoteValueToTimestamp($string, $withTime) { return null; }
  public function exportColumn($column)                                { return null; }
  public function exportTable($table = null)                           { return null; }

  public function get($map = null, $exportValues = true, $tableName = null) { }
  public function getIterator($map, $sort = null, $offset = null, $limit = null, $exportValues = true, $tableName = null) { }
  public function insert($object) { }
  public function update($object) { }
  public function delete($object) { }
  public function beginTransaction() { }
  public function commit() { }
  public function rollback() { }
  public function getTotalCount() { }

}


?>