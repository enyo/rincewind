<?php

/**
 * This file contains the Postgresql Dao definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 */
/**
 * Loading the SqlDao Class
 */
if ( ! class_exists('SqlDao', false)) include dirname(__FILE__) . '/SqlDao.php';

/**
 * The PostgresqlDao has a few postgresql specific stuff, like automatically selecting the last insert id.
 *
 * You can use the generic SqlDao if you want though! The PostgresqlDao is just
 * faster and optimized for postgresql.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 */
class PostgresqlDao extends SqlDao {

  /**
   * Generates the query for insertion, but adds the RETURNING clause, so the id
   * gets returned automatically.
   *
   * @param Record $record
   * @return string
   */
  protected function generateInsertQuery($record) {
    $query = parent::generateInsertQuery($record);
    return $query . ' RETURNING id';
  }

  /**
   * Inserts and returns the new id
   *
   * @param string $query The query.
   * @param int $id If an insert is done with an id, you can pass it. If not, the last insert id is used.
   *
   * @return int The id.
   */
  protected function insertByQuery($query, $id = null) {
    $return = $this->db->query($query);
    return $id ? $id : (int) $return->fetchResult('id');
  }

}

