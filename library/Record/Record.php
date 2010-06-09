<?php

/**
 * This file contains the Record definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @subpackage Record
 **/



/**
 * Loading the Exceptions
 */
include dirname(__FILE__) . '/RecordExceptions.php';


/**
 * Loading the Record interface
 */
include dirname(__FILE__) . '/RecordInterface.php';


/**
 * The Record is the data representation of one row from a Database request done with a Dao.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @subpackage Record
 **/
class Record implements RecordInterface {

  /**
   * This array holds all the data from a record.
   * @var array
   */
  protected $data;


  /**
   * Contains a list of changed attributes (when set() is called)
   * Indices are the attribute names.
   * @var array
   */
  protected $changedAttributes;


  /**
   * This is the cache for computed properties.
   * Whenever a computed property is accessed, it first looks if it exists here.
   * @var array
   */
  protected $computedAttributesCache = array();

  /**
   * @var Dao
   */
  protected $dao;


  /**
   * Whether the Data exists in database or not. (RawRecords don't)
   * @var bool
   */
  protected $existsInDatabase;


  /**
   * Every record holds a reference to it's dao.
   *
   * @param array $data The complete data in an associative array.
   * @param Dao $dao The dao that created this record.
   */
  public function __construct($data, $dao, $existsInDatabase = false) {
    $this->setData($data);
    $this->dao = $dao;
    $this->existsInDatabase = !!$existsInDatabase;
  }


  /**
   * Returns the dao from this record.
   *
   * @return Dao
   */
  public function getDao() { return $this->dao; }


  /**
   * @param bool $existsInDatabase
   */
  public function setExistsInDatabase($existsInDatabase = true) {
    $this->existsInDatabase = !!$existsInDatabase;
  }


  /**
   * Sets a new data array, and resets the changedAttributes array.
   *
   * @param array $data
   */
  public function setData($data) {
    $this->changedAttributes = array();
    $this->data = $data;  
  }


  /**
   * When there is an id, update() is called on the dao.
   * If there is no id insert() is called, and the dao updates the data in this Record.
   * 
   * @return Record itself for chaining.
   */
  public function save() {
    if (!$this->existsInDatabase) { $this->getDao()->insert($this); }
    else                          { $this->getDao()->update($this); } 
    return $this;
  }

  /**
   * Calls delete($this) on the dao.
   * Throws an exception if no id is set.
   */
  public function delete() {
    if (!$this->id) throw new RecordException("Can't delete a record with no id.");
    $this->getDao()->delete($this);
  }


  /**
   * Uses the changed values (the values that have explicitly been set with set()) to updated
   * the data hash.
   * This method actually calls the dao->getData() function, and passes itself. It then updates
   * its own hash with the one returned.
   *
   * @return Record Returns itself for chaining.
   */
  public function load() {
    $this->setData($this->dao->getData($this));
    return $this;
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
      $data[$this->convertAttributeNameToPhpName($name)] = $value;
    }
    return $data;
  }

  /**
   * Returns an array of all attributes that have been explicitly set. The indices are the attribute names
   * @return array
   */
  public function getChangedAttributes() {
    return $this->changedAttributes;
  }

  /**
   * Returns an array of all values that have been explicitly set. The indices are the attribute names.
   * @return array
   */
  public function getChangedValues() {
    $values = array();
    foreach ($this->changedAttributes as $attributeName=>$t) {
      $values[$attributeName] = $this->get($attributeName);
    }
    return $values;
  }

  /**
   * Gets the value of the $data array and returns it.
   * If the value is a DATE or DATE_WITH_TIME type, it returns a Date Object.
   *
   * @param string $attributeName
   * @return mixed
   **/
  public function get($originalAttributeName) {
    $attributeName = $this->convertAttributeNameToDatasourceName($originalAttributeName);

    if (array_key_exists($attributeName, $this->dao->getAttributes())) {
      $value = $this->data[$attributeName];
    }
    elseif (array_key_exists($attributeName, $this->dao->getAdditionalAttributes())) {
      $value = array_key_exists($attributeName, $this->data) ? $this->data[$attributeName] : null;
    }
    elseif (array_key_exists($attributeName, $this->dao->getReferences())) {
      return $this->dao->getReference($this, $attributeName);
    }
    elseif (method_exists($this, '_' . $originalAttributeName)) {
      if (array_key_exists($originalAttributeName, $this->computedAttributesCache)) {
        return $this->computedAttributesCache[$originalAttributeName];
      }
      else {
        $value = call_user_func(array($this, '_' . $originalAttributeName));
        $this->computedAttributesCache[$originalAttributeName] = $value;
        return $value;
      }
    }
    else {
      $this->triggerUndefinedAttributeError($attributeName);
      return null;
    }
    $attributeType = $this->getAttributeType($attributeName);
    if ($value !== null && ($attributeType == Dao::DATE || $attributeType == Dao::DATE_WITH_TIME)) $value = new Date($value);
    return $value;
  }

  /**
   * You should NEVER use this function your app.
   * This is only a helper function for Daos to access the data directly.
   *
   * @param string $attributeName
   * @param mixed $value
   */
  public function getDirectly($attributeName) {
    $attributeName = $this->convertAttributeNameToDatasourceName($attributeName);
    return isset($this->data[$attributeName]) ? $this->data[$attributeName] : null;
  }
  

  /**
   * Sets the value in the $data array after calling coerce() on the value.
   *
   * @param string $attributeName
   * @param mixed $value
   * @return Record Returns itself for chaining.
   **/
  public function set($attributeName, $value) {
    $attributeName = $this->convertAttributeNameToDatasourceName($attributeName);

    if (!array_key_exists($attributeName, $this->dao->getAttributes())) {
      $this->triggerUndefinedAttributeError($attributeName);
      return $this;
    }
    $value = self::coerce($value, $this->getAttributeType($attributeName), in_array($attributeName, $this->dao->getNullAttributes()));
    $this->data[$attributeName] = $value;
    $this->changedAttributes[$attributeName] = true;
    return $this;
  }

  /**
   * You should NEVER use this function in your app.
   * This is only a helper function for Daos to access the data directly.
   *
   * @param string $attributeName
   * @param mixed $value
   */
  public function setDirectly($attributeName, $value) {
    $this->data[$this->convertAttributeNameToDatasourceName($attributeName)] = $value;
  }
  

  /**
   * Converts a php attribute name to the datasource name.
   * Per default this simply transforms theAttribute to the_attribute.
   * If you data source handles names differently, overwrite this methods, and change your Daos to use your
   * own implementation of Records.
   *
   * @param string $attributeName
   * @return string 
   */
  protected function convertAttributeNameToDatasourceName($attributeName) { return preg_replace('/([A-Z])/e', 'strtolower("_$1");', $attributeName); }
  /**
   * Converts a datasource attribute name to the php name.
   * Per default this simply transforms the_attribute to theAttribute.
   *
   * @param string $attributeName
   * @return string 
   */
  protected function convertAttributeNameToPhpName($attributeName) { return preg_replace('/_([a-z])/e', 'strtoupper("$1");', $attributeName); }

  protected function convertGetMethodToPhpColumn($method) {
    $method = substr($method, 3);
    return strtolower(substr($method, 0, 1)) . substr($method, 1);
  }
  protected function convertSetMethodToPhpColumn($method) { return $this->convertGetMethodToPhpColumn($method); }

  /**
   * Triggers an undefined attribute error.
   * @param string $attributeName
   */
  private function triggerUndefinedAttributeError($attributeName) {
    $trace = debug_backtrace();
    trigger_error("Undefined attribute: $attributeName in " . $trace[1]['file'] . ' on line ' . $trace[1]['line'], E_USER_ERROR);
  }

  /**
   * Overloading the Record
   */
  public function __isset($attributeName) { return isset($this->data[$this->convertAttributeNameToDatasourceName($attributeName)]); }

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
   * Returns the type of the attribute defined in the Dao.
   * If getAttributes() does not return the correct type, getAdditionalAttributes() is tried.
   *
   * @param string $attributeName The attribute name
   * @return INT
   */
  protected function getAttributeType($attributeName) {
    if (array_key_exists($attributeName, $this->dao->getAttributes())) {
      $attributeTypes = $this->dao->getAttributes();
    }
    elseif (array_key_exists($attributeName, $this->dao->getAdditionalAttributes())) {
      $attributeTypes = $this->dao->getAdditionalAttributes();
    }
    else {
      $trace = debug_backtrace();
      trigger_error('No valid type found for attribute `' . $attributeName . '` in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_ERROR);
      return;
    }
    return $attributeTypes[$attributeName];
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
        if (is_string($value) || is_numeric($value)) return (string) $value;
        else {
          return $allowNull ? null : '';
        }
        break;
      case Dao::SEQUENCE:
        if (is_array($value)) return $value;
        else {
          return $allowNull ? null : array();
        }
        break;
      default: trigger_error('Unknown type in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_ERROR);
    }
  }

}


?>
