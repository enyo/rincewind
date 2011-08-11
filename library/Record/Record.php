<?php

/**
 * This file contains the Record definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @subpackage Record
 */
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
 */
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
   * @see $cacheDependencies
   * @var array
   */
  protected $computedAttributesCache = array();
  /**
   * This array contains all the dependencies on how caches are done for computed attributes.
   * The index is the name of the attribute and the value is either the name or an array of names
   * of the cache that has to be cleared.
   *
   * Eg.: array('first_name'=>'fullName', 'last_name'=>'fullName');
   *
   * When you now access fullName once, it gets cached. If now, either first_name or last_name
   * gets set, the fullName cache gets erased, and the next time you access it, it will be recalculated.
   * @var array
   */
  protected $cacheDependencies = array();
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
  public function getDao() {
    return $this->dao;
  }

  /**
   * @param bool $existsInDatabase
   */
  public function setExistsInDatabase($existsInDatabase = true) {
    $this->existsInDatabase = !!$existsInDatabase;
  }

  /**
   * @return bool Whether the record exists in the database or not.
   */
  public function existsInDatabase() {
    return $this->existsInDatabase;
  }

  /**
   * Sets a new data array, resets the changedAttributes array and clears the computed attributes cache.
   *
   * @param array $data
   * @return Record itself for chaining
   */
  public function setData($data) {
    if (!is_array($data)) trigger_error('Tried to set data that is not an array.', E_USER_ERROR);
    $this->changedAttributes = array();
    $this->clearComputedAttributesCache();
    $this->data = $data;
    return $this;
  }

  /**
   * When there is an id, update() is called on the dao.
   * If there is no id insert() is called, and the dao updates the data in this Record.
   * 
   * @return Record itself for chaining.
   */
  public function save() {
    if (!$this->existsInDatabase) {
      $this->getDao()->insert($this);
    }
    else {
      $this->getDao()->update($this);
    }
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
    $map = $this->existsInDatabase() ? array('id' => $this->get('id')) : $this->getChangedValues();
    if (count($map) === 0) throw new RecordException('You tried to load a Record which had not attributes set.');
    $this->setData($this->dao->getData($map));
    $this->setExistsInDatabase();
    return $this;
  }

  /**
   * Returns an associative array with the values.
   *
   * If $resolveReferences is true, this goes through all references, and gets the data from those records and so on.
   * Be careful not to create endless loops.
   * 
   * @param bool $resolveReferences
   * @return array
   */
  public function getArray($resolveReferences = false) {
    $data = $this->data;
    if ($resolveReferences) {
      foreach ($this->dao->getAttributes() as $attributeName => $type) {
        if ($type === Dao::REFERENCE) {
          $referenced = $this->get($attributeName);
          if ($referenced !== null) {
            // In this case, $referenced is either a Record, or a DaoResultIterator (which supports getArray as well)
            $data[$attributeName] = $referenced->getArray($resolveReferences);
          }
        }
      }
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
    foreach ($this->changedAttributes as $attributeName => $t) {
      $values[$attributeName] = $this->get($attributeName);
    }
    return $values;
  }

  /**
   * Gets the value of the $data array and returns it.
   * If the value is a DATE or DATE_WITH_TIME type, it returns a Date Object.
   * If the attribute name is not defined in the attributes list, the record checks if a function with
   * the same name but an underscore in front exists, and
   * _theAttributeName
   *
   * @param string $attributeName
   * @return mixed
   */
  public function get($attributeName) {

    $attributes = $this->dao->getAttributes();
    if (array_key_exists($attributeName, $attributes)) {
      if ($attributes[$attributeName] !== Dao::REFERENCE) {
        if (!array_key_exists($attributeName, $this->data)) {
          if (!$this->existsInDatabase()) throw new RecordException('The record did not have all attributes set properly! The attribute missing: ' . $attributeName);
          // Well, the record exists in database, and has not the complete hash set yet! So load it.
          $this->load();
        }

        $value = $this->data[$attributeName];
      }
      else {
        // It's a reference
        $reference = $this->dao->getReference($attributeName);
        return $reference->getReferenced($this, $attributeName);
      }
    }
    elseif (array_key_exists($attributeName, $this->dao->getAdditionalAttributes())) {
      $value = array_key_exists($attributeName, $this->data) ? $this->data[$attributeName] : null;
    }
    elseif (method_exists($this, '_' . $attributeName)) {
      return $this->getComputedAttribute($attributeName);
    }
    else {
      $this->triggerUndefinedAttributeError($attributeName);
      return null;
    }
    $attributeType = $this->getAttributeType($attributeName);
    if ($value !== null && ($attributeType === Dao::DATE || $attributeType === Dao::DATE_WITH_TIME)) $value = new Date($value, ($attributeType === Dao::DATE_WITH_TIME));
    return $value;
  }

  /**
   * Returns the value of a computed attribute.
   * If you try to access $yourRecord->fullName, it calls $yourRecord->_fullName() internally and returns the value.
   * Those computed attributes get cached.
   *
   * @param string $attributeName
   * @return mixed
   */
  protected function getComputedAttribute($attributeName) {
    if (array_key_exists($attributeName, $this->computedAttributesCache)) {
      return $this->computedAttributesCache[$attributeName];
    }
    else {
      $value = call_user_func(array($this, '_' . $attributeName));
      $this->computedAttributesCache[$attributeName] = $value;
      return $value;
    }
  }

  /**
   * Clears the computed attributes cache.
   * @param string $attributeName If present, only this attribute cache gets cleared.
   */
  protected function clearComputedAttributesCache($attributeName = null) {
    if ($attributeName) {
      unset($this->computedAttributesCache[$attributeName]);
    }
    else {
      $this->computedAttributesCache = array();
    }
  }

  /**
   * You should NEVER use this function your app.
   * This is only a helper function for Daos to access the data directly.
   *
   * @param string $attributeName
   * @param mixed $value
   */
  public function getDirectly($attributeName) {
    return isset($this->data[$attributeName]) ? $this->data[$attributeName] : null;
  }

  /**
   * Sets the value in the $data array after calling coerce() on the value.
   *
   * @param string $attributeName
   * @param mixed $value
   * @return Record Returns itself for chaining.
   */
  public function set($attributeName, $value) {
    if (!array_key_exists($attributeName, $this->dao->getAttributes())) {
      $this->triggerUndefinedAttributeError($attributeName);
      return $this;
    }
    $value = $this->dao->coerce($attributeName, $value, $this->getAttributeType($attributeName), in_array($attributeName, $this->dao->getNullAttributes()));
    $this->data[$attributeName] = $value;
    $this->changedAttributes[$attributeName] = true;

    // If there is a cache dependency for this attribute, clear the cache related to it.
    if (isset($this->cacheDependencies[$attributeName])) {
      $dependencies = $this->cacheDependencies[$attributeName];
      if (is_array($dependencies)) {
        foreach ($dependencies as $dependency) {
          $this->clearComputedAttributesCache($dependency);
        }
      }
      else {
        $this->clearComputedAttributesCache($dependencies);
      }
    }
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
    $this->data[$attributeName] = $value;
  }

  protected function convertGetMethodToPhpColumn($method) {
    $method = substr($method, 3);
    return strtolower(substr($method, 0, 1)) . substr($method, 1);
  }

  protected function convertSetMethodToPhpColumn($method) {
    return $this->convertGetMethodToPhpColumn($method);
  }

  /**
   * Triggers an undefined attribute error.
   * @param string $attributeName
   */
  private function triggerUndefinedAttributeError($attributeName) {
    $trace = debug_backtrace();
    trigger_error("Undefined attribute: " . $this->dao->getResourceName() . "/$attributeName in " . $trace[1]['file'] . ' on line ' . $trace[1]['line'], E_USER_ERROR);
  }

  /**
   * Overloading the Record
   */
  public function __isset($attributeName) {
    if ($this->dao->getAttributeType($attributeName) !== Dao::REFERENCE) {
      return isset($this->data[$attributeName]);
    }
    else {
      $reference = $this->get($attributeName);
      return $reference !== null;
    }
  }

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
    }
    elseif (strpos($method, 'get') === 0) {
      return $this->get($this->convertGetMethodToPhpColumn($method));
    }
    elseif (strpos($method, 'set') === 0) {
      return $this->set($this->convertSetMethodToPhpColumn($method), $param[0]);
    }
    else {
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

  /**
   * String representation of the record
   */
  public function __toString() {
    return get_class($this) . ' #' . ($this->id ? $this->id : 'Unspecified');
  }

}

