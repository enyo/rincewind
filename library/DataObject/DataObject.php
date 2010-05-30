<?php

/**
 * This file contains the DataObject definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @subpackage DataObject
 **/



/**
 * Loading the Exceptions
 */
include dirname(__FILE__) . '/DataObjectExceptions.php';


/**
 * Loading the DataObject interface
 */
include dirname(__FILE__) . '/DataObjectInterface.php';


/**
 * The DataObject is the data representation of one row from a Database request done with a Dao.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @subpackage DataObject
 **/
class DataObject implements DataObjectInterface {

  /**
   * This array holds all the data from a record.
   * @var array
   */
  protected $data;

  /**
   * @var Dao
   */
  protected $dao;


  /**
   * Every data object holds a reference to it's dao.
   *
   * @param array $data The complete data in an associative array.
   * @param Dao $dao The dao that created this object.
   */
  public function __construct($data, $dao) {
    $this->setData($data);
    $this->dao = $dao;
  }


  /**
   * Returns the dao from this object.
   *
   * @return Dao
   */
  public function getDao() { return $this->dao; }


  /**
   * Sets a new data array
   *
   * @param array $data
   */
  public function setData($data) {
    $this->data = $data;  
  }


  /**
   * When there is an id, update() is called on the dao.
   * If there is no id insert() is called, and the dao updates the data in this DataObject.
   * 
   * @return DataObject itself for chaining.
   */
  public function save() {
    if (!$this->id) { $this->getDao()->insert($this); }
    else            { $this->getDao()->update($this); } 
    return $this;
  }

  /**
   * Calls delete($this) on the dao.
   * Throws an exception if no id is set.
   */
  public function delete() {
    if (!$this->id) throw new DataObjectException("Can't delete an object with no id.");
    $this->getDao()->delete($this);
  }


  /**
   * Returns an associative array with the values.
   *
   * @param bool $phpNames If true the indices will be converted to php names, if false, the indices will be like the database names.
   * @return array
   */
  public function getArray($phpNames = true) {
    $data = array();
    if (!$phpNames) return $this->data;
    foreach ($this->data as $name=>$value) {
      $data[$this->convertDbColumnToPhpName($name)] = $value;
    }
    return $data;
  }

  /**
   * Gets the value of the $data array and returns it.
   * If the value is a DATE or DATE_WITH_TIME type, it returns a Date Object.
   *
   * @param string $column
   * @return mixed
   **/
  public function get($column) {
    $column = $this->convertPhpNameToDbColumn($column);

    if (array_key_exists($column, $this->dao->getColumnTypes())) {
      $value = $this->data[$column];
    }
    elseif (array_key_exists($column, $this->dao->getAdditionalColumnTypes())) {
      $value = array_key_exists($column, $this->data) ? $this->data[$column] : null;
    }
    elseif (array_key_exists($column, $this->dao->getReferences())) {
      return $this->dao->getReference($this, $column);
    }
    else {
      $this->triggerUndefinedPropertyError($column);
      return null;
    }
    $columnType = $this->getColumnType($column);
    if ($value !== null && ($columnType == Dao::DATE || $columnType == Dao::DATE_WITH_TIME)) $value = new Date($value);
    return $value;
  }
  /**
   * @deprecated Use get() instead
   **/
  public function getValue($column) { return $this->get($column); }

  /**
   * You should NEVER use this function your app.
   * This is only a helper function for Daos to access the data directly.
   *
   * @param string $column
   * @param mixed $value
   */
  public function getDirectly($column) {
    $column = $this->convertPhpNameToDbColumn($column);
    return isset($this->data[$column]) ? $this->data[$column] : null;
  }
  

  /**
   * Sets the value in the $data array after calling coerce() on the value.
   *
   * @param string $column
   * @param mixed $value
   * @return DataObject Returns itself for chaining.
   **/
  public function set($column, $value) {
    $column = $this->convertPhpNameToDbColumn($column);

    if (!array_key_exists($column, $this->dao->getColumnTypes())) {
      $this->triggerUndefinedPropertyError($column);
      return $this;
    }
    $value = self::coerce($value, $this->getColumnType($column), in_array($column, $this->dao->getNullColumns()));
    $this->data[$column] = $value;
    return $this;
  }
  /**
   * @deprecated Use set() instead
   **/
  public function setValue($column, $value) { return $this->set($column, $value); }

  /**
   * You should NEVER use this function in your app.
   * This is only a helper function for Daos to access the data directly.
   *
   * @param string $column
   * @param mixed $value
   */
  public function setDirectly($column, $value) {
    $this->data[$this->convertPhpNameToDbColumn($column)] = $value;
  }
  

  /**
   * Converts a php column name to the db name.
   * Per default this simply transforms theColumn to the_column.
   * If you data source handles names differently, overwrite this methods, and change your Daos to use your
   * own implementation of DataObjects.
   *
   * @param string $column
   * @return string 
   */
  protected function convertPhpNameToDbColumn($column) { return preg_replace('/([A-Z])/e', 'strtolower("_$1");', $column); }
  /**
   * Converts a db column name to the php name.
   * Per default this simply transforms the_column to theColumn.
   *
   * @param string $column
   * @return string 
   */
  protected function convertDbColumnToPhpName($column) { return preg_replace('/_([a-z])/e', 'strtoupper("$1");', $column); }

  protected function convertGetMethodToPhpColumn($method) {
    $method = substr($method, 3);
    return strtolower(substr($method, 0, 1)) . substr($method, 1);
  }
  protected function convertSetMethodToPhpColumn($method) { return $this->convertGetMethodToPhpColumn($method); }

  private function triggerUndefinedPropertyError($column) {
    $trace = debug_backtrace();
    trigger_error("Undefined column: $column in " . $trace[1]['file'] . ' on line ' . $trace[1]['line'], E_USER_ERROR);
  }

  /**
   * Overloading the DataObject
   */
  public function __isset($column) { return isset($this->data[$this->convertPhpNameToDbColumn($column)]); }

  public function __set($phpColumn, $value) {
    $this->set($phpColumn, $value);
  }

  public function __get($phpColumn) {
    return $this->get($phpColumn);
  }

  function __call($method, $param) {
    if (strpos($method, 'get') === 0 || strpos($method, 'set') === 0 || strpos($method, 'has') === 0 || strpos($method, 'is') === 0) {
      $trace = debug_backtrace();
      trigger_error("Deprecated method call: $method! Do not use getters or setters, but do directly access the properties! Call was in " . $trace[1]['file'] . ' on line ' . $trace[1]['line'], E_USER_WARNING);
    }
  
    if (strpos($method, 'is') === 0 || strpos($method, 'has') === 0) {
      return $this->get($method);
    } elseif (strpos($method, 'get') === 0) {
      return $this->get($this->convertGetMethodToPhpColumn($method));
    } elseif (strpos($method, 'set') === 0) {
      return $this->set($this->convertSetMethodToPhpColumn($method), $param[0]);
    } else {
      $trace = debug_backtrace();
      trigger_error("Call to undefined method $method in " . $trace[1]['file'] . ' on line ' . $trace[1]['line'], E_USER_ERROR);
    }
  }


  /**
   * Returns the type of the column defined in the Dao.
   * If getColumnTypes() does not return the correct type, getAdditionalColumnTypes is tried.
   *
   * @param string $column The column name
   * @return INT
   */
  protected function getColumnType($column) {
    if (array_key_exists($column, $this->dao->getColumnTypes())) {
      $columnTypes = $this->dao->getColumnTypes();
    }
    elseif (array_key_exists($column, $this->dao->getAdditionalColumnTypes())) {
      $columnTypes = $this->dao->getAdditionalColumnTypes();
    }
    else {
      $trace = debug_backtrace();
      trigger_error('No valid type found for column '.$column.' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_ERROR);
      return;
    }
    return $columnTypes[$column];
  }


  static function coerce($value, $type, $allowNull = false, $quiet = false) {
    if ($allowNull && $value === null) { return null; }
    $trace = debug_backtrace();
    if (is_array($type)) {
      // This is an enum.
      if (!count($type)) trigger_error('Invalid enum in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_ERROR);
      if (!in_array($value, $type)) {
        if (!$quiet) trigger_error('The value provided was not in the enum in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_WARNING);
        $value = $allowNull ? null : $type[0];
      }
      return $value;
    }
    switch ($type) {
      case Dao::BOOL:
        if     ($value === 'true' || $value === '1' || $value === 1) $value = true;
        elseif ($value === 'false' || $value === '0' || $value === 0) $value = false;
        elseif (!is_bool($value)) {
          if (!$quiet) trigger_error('The value of the type "BOOL" was not valid in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_WARNING);
          if ($allowNull) return null;
          else $value = true;
        }
        return $value ? true : false;
        break;
      case Dao::INT:
        if (!is_int($value) && !is_numeric($value) && (strval(intval($value)) !== strval($value))) {
          if (!$quiet) trigger_error('The value of the type "INT" was not valid in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_WARNING);
          if ($allowNull) return null;
        }
        return (int) $value;
        break;
      case Dao::FLOAT:
        if (!is_float($value) && !is_numeric($value)) {
          if (!$quiet) trigger_error('The value of the type "FLOAT" was not valid in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_WARNING);
          if ($allowNull) return null;
        }
        return (float) $value;
        break;
      case Dao::DATE:
      case Dao::DATE_WITH_TIME:
        if ($value instanceof Date) { return $value->getTimestamp(); }
        elseif (is_numeric($value)) { return (int) $value; }
        else {
          if (!$quiet && !empty($value)) trigger_error('The value of the type "DATE/DATE_WITH_TIME" '.$value.' was not valid in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_WARNING);
          if ($allowNull) return null;
          return time();
        }
        break;
      case Dao::STRING:
        return (string) $value;
        break;
      default: trigger_error('Unknown type in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_ERROR);
    }
  }

}


?>
