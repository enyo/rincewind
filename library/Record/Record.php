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
   * Whether the record is fully loaded or just initialized partially.
   *
   * @var type
   */
  protected $isLoaded;

  /**
   * Every record holds a reference to it's dao.
   *
   * @param array $data The complete data in an associative array.
   * @param Dao $dao The dao that created this record.
   * @param bool $existsInDatabase If the record actually exists in the databse.
   * @param bool $isLoaded
   */
  public function __construct($data, $dao, $existsInDatabase = false, $isLoaded = false) {
    $this->setData($data);
    $this->dao = $dao;
    $this->existsInDatabase = !!$existsInDatabase;
    $this->isLoaded = !!$isLoaded;
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
   * @param bool $isLoaded
   */
  public function setIsLoaded($isLoaded = true) {
    $this->isLoaded = !!$isLoaded;
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
    $this->setIsLoaded();
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
    foreach ($this->dao->getPseudoAttributes() as $attributeName => $type) {
      unset($data[$attributeName]);
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
    $attributeType = $this->getAttributeType($attributeName);

    if (!$attributeType) {
      $this->triggerUndefinedAttributeError($attributeName);
      return null;
    }

    if ($attributeType === Dao::REFERENCE) {
      $reference = $this->dao->getReference($attributeName);
      $value = $reference->getReferenced($this, $attributeName);
    }
    elseif ($attributeType === Dao::COMPUTED) {
      $value = $this->getComputedAttribute($attributeName);
    }
    else {
      $value = $this->getDirectly($attributeName);
      if ($value !== null && ($attributeType === Dao::DATE || $attributeType === Dao::DATE_WITH_TIME)) $value = new Date($value, ($attributeType === Dao::DATE_WITH_TIME));
    }

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
   * This is only a helper function for Daos or References to access the data directly.
   *
   * This method makes sure the record has been properly loaded.
   *
   * @param string $attributeName
   * @param mixed $value
   */
  public function getDirectly($attributeName) {
    if (!array_key_exists($attributeName, $this->dao->getAttributes())) trigger_error('Tried to access invalid attribute: ' . $attributeName, E_USER_ERROR);

    if (!array_key_exists($attributeName, $this->data) && !$this->isLoaded) {
      // If this attribute is not a reference, and is hat not been set yet, that means the record has to be loaded.
      if (!$this->existsInDatabase()) throw new RecordException('The record did not have all attributes set properly! The attribute missing: ' . $attributeName);
      // Well, the record exists in database, and has not the complete hash set yet! So load it.
      $this->load();
    }

    return array_key_exists($attributeName, $this->data) ? $this->data[$attributeName] : null;
  }

  /**
   * Sets the value in the $data array after calling coerce() on the value.
   *
   * @param string $attributeName
   * @param mixed $value
   * @return Record Returns itself for chaining.
   */
  public function set($attributeName, $value) {
    $pseudoAttributes = $this->dao->getPseudoAttributes();
    if (!array_key_exists($attributeName, $this->dao->getAttributes()) || in_array($attributeName, $pseudoAttributes)) {
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
    $attributeType = $this->getAttributeType($attributeName);
    if (!$attributeType) return null;

    if ($attributeType === Dao::REFERENCE) {
      $reference = $this->get($attributeName);
      return $reference !== null;
    }
    elseif ($attributeType === Dao::COMPUTED) {
      return $this->get($attributeName) !== null;
    }
    else {
      if (!$this->isLoaded and !array_key_exists($attributeName, $this->data)) $this->load();
      return isset($this->data[$attributeName]);
    }
  }

  public function __set($phpColumn, $value) {
    $this->set($phpColumn, $value);
  }

  public function __get($phpColumn) {
    return $this->get($phpColumn);
  }

  /**
   * Returns the type of the attribute defined in the Dao.
   * If getAttributes() does not return the correct type, getAdditionalAttributes() is tried.
   *
   * @param string $attributeName The attribute name
   * @return INT
   */
  protected function getAttributeType($attributeName) {
    $attributeType = $this->dao->getAttributeType($attributeName);

    if (!$attributeType && method_exists($this, '_' . $attributeName)) {
      $attributeType = Dao::COMPUTED;
    }

    return $attributeType;
  }

  /**
   * String representation of the record
   */
  public function __toString() {
    return get_class($this) . ' #' . ($this->id ? $this->id : 'Unspecified');
  }

}

