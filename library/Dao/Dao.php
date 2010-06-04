<?php

/**
 * This file contains the abstract Dao definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/



/**
 * Loading the Log class.
 */
if (!class_exists('Log', false)) include('Logger/Log.php');

/**
 * Loading the interface
 */
include dirname(__FILE__) . '/DaoInterface.php';

/**
 * Loading the DaoColumnAssignment Class
 */
include dirname(__FILE__) . '/DaoColumnAssignment.php';

/**
 * Loading the DataObject Class
 */
include dirname(dirname(__FILE__)) . '/DataObject/DataObject.php';

/**
 * Loading the Exceptions
 */
include dirname(__FILE__) . '/DaoExceptions.php';

/**
 * Loading the DaoReference
 */
include dirname(__FILE__) . '/DaoReference.php';

/**
 * Loading the DaoKeyListIterator Class
 */
include dirname(__FILE__) . '/DaoKeyListIterator.php';

/**
 * Loading the DaoHashListIterator Class
 */
include dirname(__FILE__) . '/DaoHashListIterator.php';

/**
 * Loading the Date Class
 */
if (!class_exists('Date', false)) include dirname(dirname(__FILE__)) . '/Date/Date.php';




/**
 * This abstract base class for all Daos.
 * IMPORTANT NOTE: The Dao (and DataObject for that matter) depend on the resource
 * having a primary `id` column!
 * If your resource layout does not provide this column, the framework will not work.
 *
 * The typical usage of the Dao is as follows:
 * <code>
 * <?php
 *   $userDao = new UserDao($datasource); // UserDao extends MysqlDao for example
 *   $userDao->get()->set('username', 'user2000')->save(); // Insert new user
 *   $userDao->getById(4)->set('name', 'New Name')->save(); // Update existing user
 *   $userDao->getByUsername('user2000')->delete(); // Deletes the user.
 *   // getByUsername() has to be implemented by the UserDao
 * ?>
 * </code>
 *
 * To create a Dao for you resource, simply extend a Dao implementation (eg: MysqlDao)
 * and set the necessary properties.
 * Example:
 * <code>
 * <?php
 *   class UserDao extends MysqlDao {
 *
 *     protected $resourceName = 'users';
 *
 *     protected $columnTypes = array(
 *       'id'=>Dao::INT,
 *       'username'=>Dao::STRING,
 *       'status'=>array('online', 'offline'), // This is an enum.
 *       'comment'=>Dao::TEXT,
 *       'creation_time'=>Dao::TIMESTAMP
 *     );
 *
 *     protected $nullColumns = array('comment');
 *
 *     protected $defaultSort = array('creation_time'=>Dao::DESC, 'username'=>Dao::ASC);
 *
 *     public function getByUsername($username) {
 *       // No checking for SQL Injections has to be done here, since get() will do all of
 *       // that (including checking the type of the column username)
 *       return $this->get(array('username'=>$username));
 *     }
 *   }
 * ?>
 * </code>
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * @see Database
 **/
abstract class Dao implements DaoInterface {


  /**#@+
   * Data types
   *
   * @var int
   */
  const INT            = 1;
  const INTEGER        = self::INT;
  const FLOAT          = 2;
  const BOOL           = 3;
  const BOOLEAN        = self::BOOL;
  const TIMESTAMP      = 4;
  const DATE_WITH_TIME = self::TIMESTAMP;
  const DATE           = 5;
  const TEXT           = 6;
  const STRING         = self::TEXT;
  const SEQUENCE       = 7;
  const SEQ            = self::SEQUENCE;
  const IGNORE         = -1;
  /**#@-*/


  /**#@+
   * Sort types
   *
   * @var int
   */
  const SORT_ASCENDING  = 0;
  const SORT_DESCENDING = 1;
  const ASC = self::SORT_ASCENDING;
  const DESC = self::SORT_DESCENDING;
  /**#@-*/


  /**
   * @var bool
   */
  const DO_NOT_EXPORT_VALUES = false;



  /**
   * Has to be called from any extended Dao to make sure everything gets setup properly
   *
   * Configuring your Dao in the constructor is only to be able to test them.
   *
   * You should configure them as member variables when defining your Daos.
   *
   * @param string $resourceName You should specify this as an attribute when writing a Dao implementation
   * @param array $columnTypes You should specify this as an attribute when writing a Dao implementation
   * @param array $nullColumns You should specify this as an attribute when writing a Dao implementation
   * @param array $defaultColumns You should specify this as an attribute when writing a Dao implementation
   */
  public function __construct($resourceName = null, $columnTypes = null, $nullColumns = null, $defaultColumns = null) {
    if ($resourceName) $this->resourceName = $resourceName;
    if ($columnTypes) $this->columnTypes = $columnTypes;
    if ($nullColumns) $this->nullColumns = $nullColumns;
    if ($defaultColumns) $this->defaultColumns = $defaultColumns;
    $this->setupReferences();
  }



  /**
   * @see Logger
   * @var Logger
   */
  protected $logger = null;

  /**
   * This is the resource name this Dao works with.
   * @var string
   */
  protected $resourceName = null;

  /**
   * This viewName will be used in every get() (and getIterator) instead of the resourceName.
   * For updating and inserts resourceName is still used.
   * @var string
   */
  protected $viewName = null;

  /**
   * If no sort attribute is passed to a query method, this will be used.
   *
   * @see generateSortString()
   * @var string|array
   */
  protected $defaultSort = 'sort';


  /**
   * This array/map must contain all column types. eg: array('id'=>Dao::INT)
   *
   * @var array
   */
  protected $columnTypes = array();


  /**
   * This works exactly the same as the column types, except that it only defines columns, that may additionally be returned by the
   * datasource (for example in joins).
   * Those values can *not* be set in the DataObjects afterwards, but are checked for their types when retrieved.
   * When trying to get an additional column out of a DataObject, that has not been retrieved from the datasource, the DataObject should
   * just return null and not error.
   * When trying to set an additional column the DataObject should trigger an error.
   *
   * @var array
   */
  protected $additionalColumnTypes = array();



  /**
   * The references array contains a list of DaoReference instances to map certain columns to other resources.
   * You set them in the setupReferences method, that gets called in the constructor.
   *
   * This array would look like this then:
   * <code>
   * <?php
   *   $references == array(
   *     'address'=>new DaoReference('AddressDao', 'address_id', 'id')
   *   );
   * ?>
   * </code>
   *
   * @var array
   * @see DaoReference
   * @see setupReferences()
   * @see addReference()
   */
  protected $references = array();


  /**
   * Overwrite this in a specific Dao implementation to setup the references.
   * Setting the references directly to the member variables is not possible since a references is an object.
   * This function gets called from the constructor.
   * You should call addReference() inside this function.
   *
   * @see addReference()
   */
  protected function setupReferences() { }

  /**
   * Adds a reference definition
   *
   * @param string $identifier The name of the identifier (eg.: address)
   * @param string|Dao $daoClassName Eg.: 'AddressDao' or the dao directly
   * @param string $localKey Eg.: address_id
   * @param string $foreignKey Eg.: id
   * @see DaoReference
   */
  protected function addReference($identifier, $daoClassName, $localKey = null, $foreignKey = 'id') {
    if ($localKey && !$this->columnExists($localKey)) {
      trigger_error(sprintf('Local key `%s` does not exist in the %s Dao.', $localKey, $this->resourceName), E_USER_ERROR);
    }
    $this->references[$identifier] = new DaoReference($daoClassName, $localKey, $foreignKey);
  }

  /**
   * Adds a toMany reference definition
   *
   * @param string $identifier The name of the identifier (eg.: addresses)
   * @param string|Dao $daoClassName Eg.: AddressDao
   * @param string $localKey Eg.: address_ids
   * @param string $foreignKey Eg.: id
   * @see addReference
   */
  protected function addToManyReference($identifier, $daoClassName, $localKey = null, $foreignKey = 'id') {
    if ($localKey && (!$this->columnExists($localKey) || $this->columnTypes[$localKey] != Dao::SEQUENCE)) {
      trigger_error(sprintf('Local key `%s` does not exist or is not a Dao::SEQUENCE in the %s Dao.', $localKey, $this->resourceName), E_USER_ERROR);
    }
    $this->references[$identifier] = new DaoToManyReference($daoClassName, $localKey, $foreignKey);
  }

  /**
   * Returns the DataObject for a specific reference.
   * It then sets the hash in the DataObject so it can be retrieved next time.
   * If it is accessed after that, the already fetched DataHash is used.
   * A DataSource can directly return the DataHash, so it doesn't have to be fetched.
   *
   * @return DataObject
   */
  public function getReference($dataObject, $column) {
    if (!isset($this->references[$column])) throw new DaoWrongValueException("The column `$column` is not specified in references.");

    $reference = $this->references[$column];

    $dao = $reference->getDaoClassName();
    if (is_string($dao)) $dao = $this->createDao($dao);

    if ($reference instanceof DaoToManyReference) {
      // toMany reference
      if ($data = $dataObject->getDirectly($column)) {
        if (is_array($data)) {
          // The sequence of data hashes has been set already
          return new DaoHashListIterator($dataObject->getDirectly($column), $this);
        }
        trigger_error(sprintf('The data hash for `%s` was set but incorrect.', $column), E_USER_WARNING);
        return new DaoHashListIterator(array(), $this);
      }
      else {
        // Get the list of ids
        $localKey = $reference->getLocalKey();
        $foreignKey = $reference->getForeignKey();

        if ($localKey && $foreignKey) {
          $localValue = $dataObject->get($localKey);

          return new DaoKeyListIterator($localValue, $this, $foreignKey);
        }
        return new DaoKeyListIterator(array(), $this, $foreignKey);
      }
    }
    else {
      // toOne reference
      if ($data = $dataObject->getDirectly($column)) {
        if (is_array($data)) {
          // If the data hash exists already, just return the DataObject with it.
          return $dao->getObjectFromData($data);
        }
        else {
          trigger_error(sprintf('The data hash for `%s` was set but incorrect.', $column), E_USER_WARNING);
          return null;
        }
      }
      else {
        // Otherwise: get the data hash, store it in the DataObject that's referencing it, and
        // return the DataObject.
        $localKey = $reference->getLocalKey();
        $foreignKey = $reference->getForeignKey();

        if ($localKey && $foreignKey) {
          $localValue = $dataObject->get($localKey);
          if ($localValue === null) return null;
          $return = $dao->get(array($foreignKey=>$localValue));
          $dataObject->setDirectly($column, $return->getArray());
          return $return;
        }
        else {
          return null;
        }
      }
    }
  }

  /**
   * Creates a Dao.
   * You probably want to overwrite this method in your daos to use your implementation of instantiating Daos.
   *
   * @param string $daoClassName
   * @return Dao
   */
  protected function createDao($daoClassName) {
    return new $daoClassName();
  }


  /**
   * If your datasource holds different column names than you want in you DataObjects, you can specify export & import mappings.
   * (Don't confuse this with your php values. Meaning: if the datasource value is 'strangely_named', but you want to access your object
   * like this: $object->perfectName, then you have to map it from strangely_named to perfect_name (not perfectName).)
   * If no mapping is found in the imports, than a reverse lookup is done in exports, and vice versa, so for a normal conversion
   * only the columnImportMapping (or columnExportMapping if you prefer) has to be set.
   * E.g.: array('what_the_datasource_value_actually_is'=>'what_you_would_want_it_to_be');
   *
   * @var array
   */
  protected $columnImportMapping = array();

  /**
   * The same as columnImportMapping but the other way around.
   *
   * @var array
   */
  protected $columnExportMapping = array();

  /**
   * This is an array containing all columns that can be null. eg: $nullColumns = array('email', 'name');
   *
   * @var array
   */
  protected $nullColumns = array();


  /**
   * This is a list of columns that have default values in the datasource.
   * This means that, if the values are NULL, and the entry is inserted in the datasource, they will not be
   * passed, so that the datasource can automatically set the values.
   * So when you call getRawObject() those values will be null.
   * Per default the id is given.
   *
   * @var array
   */
  protected $defaultValueColumns = array('id');



  /**
   * Calls find() and throws an error if null is returned, otherwise it just passes the DataObject
   * If you call get() without parameters, a "raw object" will be returned, containing
   * only default values, and null as id.
   *
   * @param array|DataObject $map A map or dataObject containing the column assignments.
   * @param bool $exportValues When you want to have complete control over the $map
   *                           column names, you can set exportValues to false, so they
   *                           won't be processed.
   *                           WARNING: Be sure to escape them yourself if you do so.
   * @param string $resourceName You can specify a different resource (most probably a view)
   *                          to get data from.
   *                          If not set, $this->viewName will be used if present; if not
   *                          $this->resourceName is used.
   * @return DataObject
   */
  public function get($map = null, $exportValues = true, $resourceName = null) {
    if (!$map) return $this->getRawObject();
    $dataObject = $this->find($map, $exportValues, $resourceName);
    if (!$dataObject) throw new DaoNotFoundException('Did not find any object.');
    return $dataObject;
  }


  /**
   * Given the $sort parameter, it generates a sort String used in the query.
   * If $sort is not provied, $defaultSort is used.
   *
   * @see $defaultSort
   * @param string|array $sort
   */
  abstract protected function generateSortString($sort);


  /**
   * If the passed parameter is not an array, but a DataObject, then it gets the changed values out of it.
   *
   * @param array|DataObject $mapOrObject
   * @return array
   */
  protected function interpretMap($mapOrObject) {
    if (is_array($mapOrObject)) return $mapOrObject;
    if ($mapOrObject instanceof DataObject) return $mapOrObject->getChangedValues();
    throw new DaoWrongValueException("The passed map is neither an array nor a DataObject.");
  }


  /**
   * @param LoggerFactory $loggerFactory
   */
  public function setLoggerFactory($loggerFactory) { $this->setLogger($loggerFactory->getLogger($this->resourceName . 'Dao')); }

  /**
   * @param Logger $logger
   */
  public function setLogger($logger) { $this->logger = $logger; }

  /**
   * @param string $message
   */
  protected function log($message)   { if ($this->logger) $this->logger->log($message); }

  /**
   * @param string $message
   */
  protected function debug($message) { if ($this->logger) $this->logger->debug($message); }



  /**
   * Returns the column types array
   * @return array
   */   
  public function getColumnTypes() { return $this->columnTypes; }

  /**
   * Returns the additional column types array
   * @return array
   */   
  public function getAdditionalColumnTypes() { return $this->additionalColumnTypes; }

  /**
   * @return array
   * @see $references
   */
  public function getReferences() { return $this->references; }

  /**
   * Returns the null columns
   * @return array
   */   
  public function getNullColumns() { return $this->nullColumns; }

  /**
   * Returns the default value columns
   *
   * @return array
   */   
  public function getDefaultValueColumns() { return $this->defaultValueColumns; }


  /**
   * Temporarly all getXXXIterator() calls get converted to getXXX().
   * This is deprecated and only a transition! If you get warnings: Correct the code.
   * The next release will not support getXXXIterator()
   */
  public function __call($method, $param) {
    $trace = debug_backtrace();
    if (strpos($method, 'Iterator')) {
      $newMethod = str_replace('Iterator', '', $method);
      trigger_error(sprintf('Called %s() instead of %s() from %s on line %u.', $method, $newMethod, $trace[1]['file'], $trace[1]['line']), E_USER_WARNING);
      return call_user_func_array(array($this, $newMethod), $param);
    }
    trigger_error(sprintf('Call to undefined function: %s::%s() from %s on line %u.', get_class($this), $method, $trace[1]['file'], $trace[1]['line']), E_USER_ERROR);
  }



  /**
   * If you have to do stuff after an insert, overwrite this function.
   * It gets called by the Dao after doing an insert.
   *
   * @param DataObject $object
   * @see DataObject
   */
  protected function afterInsert($object) { }

  /**
   * If you have to do stuff after an update, overwrite this function.
   * It gets called by the Dao after doing an update.
   *
   * @param DataObject $object
   * @see DataObject
   */
  protected function afterUpdate($object) { }

  /**
   * If you have to do stuff after a deletion, overwrite this function.
   * It gets called by the Dao after deleting.
   *
   * @param DataObject $object
   * @see DataObject
   */
  protected function afterDelete($object) { }


  
  /**
   * Checks if a column name exists as real or additional column.
   *
   * @param string $column
   */
  protected function columnExists($column) {
    return isset($this->columnTypes[$column]) || isset($this->additionalColumnTypes[$column]);
  }


  /**
   * This is a wrapper for get() and the id as parameter.
   * @param int $id
   * @return DataObject
   * @see DataObject
   */
  public function getById($id) {
    return $this->get(array('id'=>intval($id)));
  }

  /**
   * Gets the object by id and deletes it.
   * 
   * @param int $id
   */
  public function deleteById($id) {
    $object = $this->getById($id);
    $this->delete($object);
  }


  /**
   * Returns all rows (but you can specify offset &amp; limit)
   *
   * @param string|array $sort
   * @param int $offset
   * @param int $limit
   * @return DaoResultIterator
   * @see DaoResultIterator
   */
  public function getAll($sort = null, $offset = null, $limit = null) {
    return $this->getIterator(array(), $sort, $offset, $limit);
  }


  /**
   * @deprecated
   * Use this instead:
   * <code>
   * <?php $dao->getAll()->asArrays(); ?>
   * </code>
   */
  public function getAllAsArrays() {
    $trace = debug_backtrace();
    trigger_error(sprintf('The method getAllAsArrays() is deprecated in %s on line %u.', $trace[0]['file'], $trace[0]['line']), E_USER_ERROR);
  }


  /**
   * Takes a column and sees if there is an import/export mapping for it.
   * It then returns the correct column name, unescaped.
   *
   * @param string $column
   * @return string
   * @see $columnImportMapping
   * @see $columnExportMapping
   */
  protected function applyColumnImportMapping($column) {
    if (isset($this->columnImportMapping[$column]))        { return $this->columnImportMapping[$column]; }
    elseif (in_array($column, $this->columnExportMapping)) { return array_search($column, $this->columnExportMapping); }
    return $column;
  }

  /**
   * Takes a column and sees if there is an import/export mapping for it.
   * It then returns the correct column name, unescaped.
   *
   * @param string $column
   * @return string
   * @see $columnImportMapping
   * @see $columnExportMapping
   */
  protected function applyColumnExportMapping($column) {
    if (isset($this->columnExportMapping[$column]))        { return $this->columnExportMapping[$column]; }
    elseif (in_array($column, $this->columnImportMapping)) { return array_search($column, $this->columnImportMapping); }
    return $column;
  }

  /**
   * Converts the datasource column name to a valid php name.
   * This is done with applyColumnImportMapping()
   *
   * @param string $column
   * @return string
   * @see applyColumnImportMapping
   */
  public function importColumn($column) {
    return $this->applyColumnImportMapping($column);
  }




  /**
   * Returns true if the column can not be null.
   *
   * @return bool
   */
  protected function notNull($column) {
    return !in_array($column, $this->nullColumns);
  }


  /**
   * This is a helper class that defines if an array is a "vector" (has only integer indices starting from 0) or an associative array.
   *
   * @param array $var
   * @return bool
   */
  static function isVector($var) {
    return count(array_diff_key($var, range(0, count($var) - 1))) === 0;
  }



  /**
   * Returns the arrays containing the columns, and values to perform an insert.
   * Values that are null are simply left out. So are Dao::IGNORE types.
   *
   * @param DataObject $object
   * @return array With $columns and $values as 0 and 1st index respectively.
   */
  protected function generateInsertArrays($object) {
    $values = array();
    $columns = array();
    $id = null;
    foreach ($this->columnTypes as $column=>$type) {
      $value = $object->getValue($column);
      if ($value !== null && $type != Dao::IGNORE) {
        $columns[] = $this->exportColumn($column);
        $values[]  = $this->exportValue($value, $type, $this->notNull($column));
      }
    }
    return array($columns, $values);
  }



  /**
   * Returns a data object with the data in it.
   * Override this function if you want a specific DataObject, not the default one.
   * Using your own DataObject is sometimes useful if your datasource has a strange
   * naming convention and you have to do different name conversion than the default
   * one.
   *
   * @param array $data
   * @param bool $existsInDatabase
   * @return DataObject
   */
  protected function getObjectFromPreparedData($data, $existsInDatabase = true) {
    return new DataObject($data, $this, $existsInDatabase);
  }

  /**
   * Prepares the data, and gets a new object.
   * This is the way you should get a DataObject from datasource data with.
   *
   * @param array $data The data returned from the datasource
   * @see prepareDataForObject()
   * @see getObjectFromPreparedData()
   * @return DataObject
   */
  public function getObjectFromData($data) {
    return $this->getObjectFromPreparedData($this->prepareDataForObject($data));
  }

  /**
   * Prepares the data, and updates the object
   *
   * @param array $data The data returned from the datasource
   * @param DataObject $object The object to be updated
   * @see prepareDataForObject()
   * @return void
   */
  public function updateObjectWithData($data, $object) {
    $object->setData($this->prepareDataForObject($data));
  }



  /**
   * Goes through the data array returned from the datasource, and converts the values that are necessary.
   * Meaning: if some values are null, check if they are allowed to be null.
   * This function also checks if every column in the columnTypes array has been transmitted
   *
   * @param array $data
   * @return array
   */
  protected function prepareDataForObject($data) {
    $neededValues = $this->columnTypes;
    foreach ($data as $column=>$value) {
      $column = $this->importColumn($column);
      if (array_key_exists($column, $this->columnTypes)) {
        unset($neededValues[$column]);
        if ($this->columnTypes[$column] != Dao::IGNORE) {
          $data[$column] = $this->importValue($value, $this->columnTypes[$column], $this->notNull($column));
        }
      }
      elseif (array_key_exists($column, $this->additionalColumnTypes)) {
        if ($this->additionalColumnTypes[$column] != Dao::IGNORE) {
          $data[$column] = $this->importValue($value, $this->additionalColumnTypes[$column], $this->notNull($column));
        }
      }
      elseif (isset($this->references[$column])) {
        if (!is_array($value)) {
          $trace = debug_backtrace();
          trigger_error('The value for column ' . $column . ' ('.$this->resourceName.') was not correct in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_WARNING);
          unset($data[$column]);
        }
        // Just let it untouched.
      }
      else {
        $trace = debug_backtrace();
        trigger_error('The type for column ' . $column . ' ('.$this->resourceName.') is not defined in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_WARNING);
        unset($data[$column]);
      }
    }
    foreach ($neededValues as $column=>$type) {
      if ($type != Dao::IGNORE) {
        if ($this->notNull($column)) {
          $trace = debug_backtrace();
          trigger_error('The column ' . $column . ' ('.$this->resourceName.') was not transmitted from data source in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_WARNING);
          $data[$column] = DataObject::coerce(null, $type, false, $quiet = true);
        } else {
          $data[$column] = null;
        }
      }
    }
    return $data;     
  }


  /**
   * Returns an object with all columns defined, but only set if necessary.
   * nullColumns will be null as well as defaultValueColumns. All other columns will have a default value set with coerce().
   * Be careful! This function will soon be protected, and should not be called anymore! Use get() (without map) instead (which will call
   * getRawObject() for you).
   * You can not depend on this function... it is subject to change.
   *
   * @see $nullColumns
   * @see defaultValueColumns
   * @see $columnTypes
   * @see coerce()
   * @see get()
   * @return DataObject
   */
  public function getRawObject() {
    $data = array();
    foreach ($this->columnTypes as $column=>$type) {
      if (in_array($column, $this->nullColumns) || in_array($column, $this->defaultValueColumns)) { $data[$column] = null; }
      elseif ($type != Dao::IGNORE) $data[$column] = DataObject::coerce(null, $type, $allowNull = false, $quiet = true);
    }
    return $this->getObjectFromPreparedData($data, $existsInDatabase = false);
  }









  /**
   * Imports an external value (either from datasource, or xml, etc...) into an expected PHP variable.
   * If the column can be null, null will be returned.
   *
   * @param mixed $externalValue The value to be imported
   * @param int $type The type (selected from Dao)
   * @param bool $notNull Whether the value can be null or not
   * @return mixed
   */
  public function importValue($externalValue, $type, $notNull = true) {
    if (!$notNull && $externalValue === null) { return null; }
    $dateWithTime = false;
    try {
      if (is_array($type)) {
        return $this->importEnum($externalValue, $type);
      }
      switch ($type) {
        case Dao::BOOL:  return $this->importBool($externalValue); break;
        case Dao::INT:   return $this->importInteger($externalValue); break;
        case Dao::FLOAT: return $this->importFloat($externalValue); break;
        case Dao::TEXT:  return $this->importString($externalValue); break;
        case Dao::DATE_WITH_TIME: $dateWithTime = true; // No break
        case Dao::DATE:     return $this->importDate($externalValue, $dateWithTime); break;
        case Dao::SEQUENCE: return $this->importSequence($externalValue); break;
        default: throw new DaoException('Unknown type when importing a value.'); break;
      }
    }
    catch (Exception $e) {
      throw new Exception('There was an error processing the resource "' . $this->resourceName . '": ' . $e->getMessage());
    }
  }

  /**
   * Converts a datasource value to a timestamp.
   * Obviously every Database Dao has to implement that itself.
   * If you do not count on implementing this just overwrite the function and throw a DaoNotSupportedException inside.
   *
   * @param string $string
   * @param bool $withTime
   * @return mixed
   */
  abstract protected function convertRemoteValueToTimestamp($string, $withTime);

  
  /**
   * Calls convertRemoteValueToTimestamp and returns a Date Object.
   * If you do not count on implementing this just overwrite the function and throw a DaoNotSupportedException inside.
   *
   * @param string $value
   * @param bool $withTime
   */
  public function importDate($value, $withTime) {
    return new Date($this->convertRemoteValueToTimestamp($value, $withTime));
  }

  /**
   * Simple PHP conversion.
   * If you do not count on implementing this just overwrite the function and throw a DaoNotSupportedException inside.
   *
   * @param mixed $value
   * @return integer
   */
  public function importInteger($value) {
    return (int) $value;
  }

  /**
   * Simple PHP conversion.
   * If you do not count on implementing this just overwrite the function and throw a DaoNotSupportedException inside.
   *
   * @param mixed $value
   * @return float
   */
  public function importFloat($value) {
    return (float) $value;
  }

  /**
   * Simple PHP conversion.
   * If you do not count on implementing this just overwrite the function and throw a DaoNotSupportedException inside.
   *
   * @param mixed $value
   * @return string
   */
  public function importString($value) {
    return (string) $value;
  }


  /**
   * Checks if the value is present in the enum. Throws a DaoException if not.
   *
   * @param string $value
   * @param array $list
   * @return string
   */
  public function importEnum($value, $list) {
    if (!in_array($value, $list)) throw new DaoException("The value provided is not defined in the enum.");
    return (string) $value;
  }

  /**
   * This function tries to convert strings integers and stuff into a bool.
   *
   * @param mixed $value
   * @return bool
   */
  public function importBool($value) {
    if (!$value) return false;
    if (is_int($value)) return true;
    $value = strtolower($value);
    switch ($value) {
      case 'false': case 'f': case '0': return false; break;
      case 'true': case 't': case '1': return true; break;
      default: throw new DaoException("The boolean ($value) could not be converted."); break;
    }
  }

  /**
   * This makes sure the sequence is an array
   *
   * @param mixed $value
   * @return array
   */
  public function importSequence($value) {
    return is_array($value) ? $value : array();
  }


  /**
   * Exports a PHP value into a value understood by the Database
   *
   * @param mixed $internalValue The value to be imported
   * @param int $type The type (selected from Dao)
   * @param bool $notNull Whether the value can be null or not
   *
   * @return mixed
   */
  public function exportValue($internalValue, $type, $notNull = true) {
    if (!$notNull && $internalValue === NULL) {
      return $this->exportNull();
    }
    if (is_array($type)) {
      return $this->exportEnum($internalValue, $type);
    }
    $dateWithTime = false;
    switch ($type) {
      case Dao::BOOL:   return $this->exportBool($internalValue); break;
      case Dao::INT:    return $this->exportInteger($internalValue); break;
      case Dao::FLOAT:  return $this->exportFloat($internalValue); break;
      case Dao::TEXT:   return $this->exportString($internalValue); break;
      case Dao::DATE_WITH_TIME: $dateWithTime = true; // No break
      case Dao::DATE:   return $this->exportDate($internalValue, $dateWithTime); break;
      case Dao::IGNORE: return $internalValue; break;
      default: throw new DaoException('Unhandled type when exporting a value.'); break;
    }
  }


  /**
   * Has to determine the correct column (with import/export mappings), escape and quote it.
   * (eg.: user'name becomes `user\'name`)
   *
   * @param string $column
   * @return string
   */
  abstract public function exportColumn($column);


  /**
   * Escapes and quotes a resource name.
   * If none provied $this->resourceName will be used.
   *
   * @param string $resourceName
   * @return string
   * @see $resourceName
   */
  abstract public function exportResourceName($resourceName = null);



  /**
   * If your strings need quoting, do that in here.
   *
   * @param string $string
   * @return string
   */
  public function exportString($string) {
    return (string) $string;
  }

  /**
   * @return string
   */
  public function exportNull() {
    return 'NULL';
  }

  /**
   * @param bool $bool
   * @return string
   */
  public function exportBool($bool) {
    return $bool ? 'true' : 'false';
  }

  /**
   * @param int $int
   * @return int
   */
  public function exportInteger($int) {
    return (int) $int;
  }

  /**
   * @param float $float
   * @return float
   */
  public function exportFloat($float) {
    return (float) $float;
  }

  /**
   * @param Date $date
   * @param bool $withTime
   * @return int a timestamp
   */
  public function exportDate($date, $withTime) {
    return $this->exportInteger($date->getTimestamp());
  }

  /**
   * If the value is in the enum list, it calls exportString, and returns it.
   * Throws a DaoException if not.
   * @param string $value
   * @param array $list
   * @return string
   */
  public function exportEnum($value, $list) {
    if (!in_array($value, $list)) throw new DaoWrongValueException("The value provided is not defined in the enum.");
    return $this->exportString($value);
  }



  /**
   * Tries to interpret a $sort parameter, which can be one of following:
   *
   * - A string: It will be interpreted as one ascending column.
   * - An array containing strings: It will be cycled through and every string is interpreted as ascending column
   * - A map (associative array): It will be interpreted as columnName=>sortType. E.g: array('name'=>Dao::DESC, 'age'=>Dao::ASC)
   *
   * @param string|array $sort
   * @return array An array containing all columns to sort by, escaped, and ASC or DESC appended. E.g.: array('name DESC', 'age');
   */
  protected function interpretSortVariable($sort) {
    if (!is_array($sort)) {
      return $this->columnExists($sort) ? array($this->exportColumn($sort)) : null;
    }

    if (count($sort) == 0) return null;

    $columnArray = array();
    if (self::isVector($sort)) {
      foreach ($sort as $column) {
        if ($this->columnExists($column)) $columnArray[] = $this->exportColumn($column);
      }
    }
    else {
      foreach ($sort as $column=>$sort) {
        if ($this->columnExists($column)) $columnArray[] = $this->exportColumn($column) . ($sort == Dao::DESC ? ' desc' : '');
      }
    }

    return $columnArray;
  }


}

?>
