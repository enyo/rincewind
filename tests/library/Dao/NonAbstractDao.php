<?php


/**
 * You can extend this dao if you only need to overwrite some of the abstract methods.
 */
class NonAbstractDao extends Dao {

  protected function generateSortString($sort)                         { return null; }
  protected function convertRemoteValueToTimestamp($string, $withTime) { return null; }
  public function exportResourceName($resource = null)                           { return null; }

  public function get($map = null, $exportValues = true, $resourceName = null) { }
  public function find($map = null, $exportValues = true, $resourceName = null) { }
  public function getData($map, $exportValues = true, $resourceName = null) { }
  public function getIterator($map, $sort = null, $offset = null, $limit = null, $exportValues = true, $resourceName = null, $retrieveTotalRowCount = false) { }
  public function insert($object) { }
  public function update($object) { }
  public function delete($object) { }
  public function beginTransaction() { }
  public function commit() { }
  public function rollback() { }
  public function getTotalCount() { }

}


?>
