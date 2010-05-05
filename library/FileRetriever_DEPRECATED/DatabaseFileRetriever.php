<?php


  require_once('FileRetriever/SignedFileRetriever.php');

  /**
   * @author     Matthias Loitsch <develop@matthias.loitsch.com>
   * @copyright  Copyright (c) 2009, Matthias Loitsch
   *
   * The DatabaseFileRetriever is a SignedFileRetriever, with a view additional functions, to
   * handle typical insert/update/delete and select commands.
   * The form in which the data is returned (XML, JSON, plain text) has nothing to do with
   * the retriever, and should be handled by the Dao.
   */
  class DatabaseFileRetriever extends SignedFileRetriever {


    /**
     * @param string $table The table name
     * @param array $values An associative array containing the values to create the select query
     * @param int $offset
     * @param int $limit
     */
    public function select($table, $values, $sort = array(), $offset = 0, $limit = 0) {
      $get = array('table'=>$table);
      $post = $this->getPostArray('select', $values, $sort, $offset, $limit);
      return $this->getFile($get, $post);
    }

    /**
     * See select()
     */
    public function insert($table, $values) {
      $get = array('table'=>$table);
  
      $post = $this->getPostArray('insert', $values);
  
      return $this->getFile($get, $post);
    }
  
    /**
     * See select()
     */
    public function update($table, $values) {
      $get = array('table'=>$table);
  
      $post = $this->getPostArray('update', $values);
      return $this->getFile($get, $post);
    }

    /**
     * See select()
     */
    public function delete($table, $values) {
      $get = array('table'=>$table);
      $post = $this->getPostArray('delete', $values);
      return $this->getFile($get, $post);
    }


    /**
     * Genereates the typical post array
     */
    private function getPostArray($method, $values, $sort = array(), $offset = 0, $limit = 0) {
      return array('method'=>$method, 'values'=>$values, 'sort'=>$sort, 'offset'=>$offset, 'limit'=>$limit);
    }

  }
  
  

?>
