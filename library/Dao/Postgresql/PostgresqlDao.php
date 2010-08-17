<?php

/**
 * This file contains the PostgresqlDao definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/



/**
 * Loading the SqlDao
 */
include dirname(dirname(__FILE__)) . '/SqlDao.php';


/**
 * Loading the PostgresqlResultIterator
 */
include dirname(__FILE__) . '/PostgresqlResultIterator.php';


/**
 * The PostgresqlDao implementation of a SqlDao
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/
class PostgresqlDao extends SqlDao {

  /**
   * Calls the Dao::exportAttributeName and adds quotes.
   * Takes a php attribute name, converts it via import/export attribute mapping, renames it, escapes it, and adds quotes.
   * This is the correct way to insert attribute names in a SQL query.
   *
   * @param string $attributeName
   * @return string
   */
  public function exportAttributeName($attributeName) {
    return '"' . $this->escapeAttributeName($attributeName) . '"';
  }

  /**
   * Escapes and quotes a table name.
   *
   * @param string $resourceName
   * @return string The escaped and quoted table name.
   */
  public function exportResourceName($resourceName = null) {
    return '"' . $this->escapeResourceName($resourceName) . '"';
  }

  /**
   * Escapes and quotes a string.
   *
   * @param string $text
   * @return string The escaped and quoted string.
   */
  public function exportString($text) {
    return "'" . $this->escapeString($text) . "'";
  }

  /**
   * Returns the id of the last inserted record.
   *
   * @return int
   */
  protected function getLastInsertId() {
    $id = $this->db->query('select currval("' . parent::exportResourceName() . '_seq")');
    $id = $id->fetchArray();
    return $id['id'];
  }

  /**
   * Creates an iterator for a postgresql result.
   *
   * @param postgresql_result $result
   * @return PostgresqlResultIterator
   */
  protected function createIterator($result) {
    return new PostgresqlResultIterator($result, $this);
  }


}
