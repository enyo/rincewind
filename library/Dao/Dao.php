<?php

/**
 * This file contains the abstract Dao definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 * */
/**
 * Loading the interface
 */
include dirname(__FILE__) . '/DaoInterface.php';

/**
 * Loading the DaoAttributeAssignment Class
 */
include dirname(__FILE__) . '/DaoAttributeAssignment.php';

/**
 * Loading the Exceptions
 */
include dirname(__FILE__) . '/DaoExceptions.php';

/**
 * Loading the DaoKeyListIterator Class
 */
include dirname(__FILE__) . '/DaoKeyListIterator.php';

/**
 * Loading the DaoHashListIterator Class
 */
include dirname(__FILE__) . '/DaoHashListIterator.php';

/**
 * Loading the Record Class
 */
require_class('Record');

/**
 * Loading the Date Class
 */
require_class('Date');

/**
 * This abstract base class for all Daos.
 * IMPORTANT NOTE: The Dao (and Record for that matter) depend on the resource
 * having a primary `id` attribute!
 * If your resource layout does not provide this attribute, the framework will not work.
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
 *     protected $attributes = array(
 *       'id'=>Dao::INT,
 *       'username'=>Dao::STRING,
 *       'status'=>array('online', 'offline'), // This is an enum.
 *       'comment'=>Dao::TEXT,
 *       'creationTime'=>Dao::TIMESTAMP
 *     );
 *
 *     protected $nullAttributes = array('comment');
 *
 *     protected $defaultSort = array('creationTime'=>Dao::DESC, 'username'=>Dao::ASC);
 *
 *     public function getByUsername($username) {
 *       // No checking for SQL Injections has to be done here, since get() will do all of
 *       // that (including checking the type of the attribute username)
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
 * */
abstract class Dao implements DaoInterface {
  /*   * #@+
   * Data types.
   * If you write your own datatypes, make sure to start at 100.
   *
   * All types below 100 are reserved to the standard Dao values.
   *
   * @var string
   */
  const INT = 'Integer';
  const INTEGER = self::INT;
  const FLOAT = 'Float';
  const BOOL = 'Bool';
  const BOOLEAN = self::BOOL;
  const TIMESTAMP = 'DateWithTime';
  const DATE_WITH_TIME = self::TIMESTAMP;
  const DATE = 'Date';
  const TEXT = 'String';
  const STRING = self::TEXT;
  const SEQUENCE = 'Sequence';
  const SEQ = self::SEQUENCE;
  const REFERENCE = 'Reference';
  const REF = self::REFERENCE;
  const IGNORE = 'Ignore';
  /*   * #@- */


  /*   * #@+
   * Sort types
   *
   * @var int
   */
  const SORT_ASCENDING = 0;
  const SORT_DESCENDING = 1;
  const ASC = self::SORT_ASCENDING;
  const DESC = self::SORT_DESCENDING;
  /*   * #@- */


  /**
   * @var bool
   */
  const DO_NOT_EXPORT_VALUES = false;

  /**
   * Has to be called from any extended Dao to make sure everything gets setup properly
   *
   * The normal way of configuring your Dao is to extend the type of Dao you want
   * (SqlDao, FileDao, etc...) and configure it with parameters, instead of the
   * constructor call.
   *
   * The constructor checks if the resourceName and attributes array are set (either
   * with the constructor or as properties) and throws an Exception if not.
   *
   * Do not forget to call parent::__construct() when you overwrite this method!
   * 
   */
  public function __construct() {
    $this->attributes = array_merge($this->attributes, $this->additionalAttributes());
    $this->nullAttributes = array_merge($this->nullAttributes, $this->additionalNullAttributes());
    $this->defaultValueAttributes = array_merge($this->defaultValueAttributes, $this->additionalDefaultValueAttributes());
    $this->attributeImportMapping = array_merge($this->attributeImportMapping, $this->additionalAttributeImportMapping());

    if (!$this->resourceName) {
      throw new DaoException('No resource name provided in class ' . get_class($this) . '.');
    }
    if (count($this->attributes) === 0) {
      throw new DaoException('No attributes provied.');
    }
  }

  /**
   * Don't forget to call
   * array_merge(parent::additionalAttributes(), $attributes)
   * when you overwrite this method.
   * 
   * @return array
   */
  protected function additionalAttributes() {
    return array();
  }

  /**
   * Don't forget to call
   * array_merge(parent::additionalNullAttributes(), $attributes)
   * when you overwrite this method.
   * 
   * @return array
   */
  protected function additionalNullAttributes() {
    return array();
  }

  /**
   * Don't forget to call
   * array_merge(parent::additionalDefaultValueAttributes(), $attributes)
   * when you overwrite this method.
   * 
   * @return array
   */
  protected function additionalDefaultValueAttributes() {
    return array();
  }

  /**
   * @return array
   */
  protected function additionalAttributeImportMapping() {
    return array();
  }

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
   * Defines the class name of the record to be instantiated.
   * You can overwrite this if your Dao uses a specific Record.
   *
   * @var string
   * @see getRecordFromPreparedData()
   */
  protected $recordClassName = 'Record';
  /**
   * If no sort attribute is passed to a query method, this will be used.
   *
   * @see generateSortString()
   * @var string|array
   */
  protected $defaultSort = 'sort';
  /**
   * This array/map must contain all attribute types. eg: array('id'=>Dao::INT, 'firstName'=>Dao::STRING)
   * The index is always the php name you want to access on the Record.
   * The methods importAttributeName() and exportAttributeName() take care of converting these names into
   * the names for the datasource.
   *
   * @var array
   */
  protected $attributes = array();
  /**
   * This works exactly the same as the attributes, except that it only defines attributes, that may additionally be returned by the
   * datasource (for example in joins).
   * Those values can *not* be set in the Records afterwards, but are checked for their types when retrieved.
   * When trying to get an additional attribute out of a Record, that has not been retrieved from the datasource, the Record should
   * just return null and not error.
   * When trying to set an additional attribute the Record should trigger an error.
   *
   * @var array
   */
  protected $additionalAttributes = array();
  /**
   * A Cache object to store cached objects.
   * 
   * @var Cache
   */
  protected $cache = null;
  /**
   * Whether to use the cache or not.
   * 
   * @var bool
   */
  protected $useCache = false;
  /**
   * Seconds til the cache expires
   * @var int
   */
  protected $cacheExpire = 3600; // 1 hour
  /**
   * Is used to prefix the keys in cache.
   * To see the final key, look at generateCacheKey
   * @var string
   * @see generateCacheKey()
   */
  protected $cachePrefix = '';
  /**
   * The references array contains a list of cached DaoReference instances to
   * map certain attributes to other resources. They are all fetched by calling
   * getXXXReference().
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
   */
  protected $references = array();

  /**
   * Returns the reference for an attribute.
   *
   * If the reference has not yet been created, the appropriate method
   * (eg.: getUserReference()) will be called to get the reference, and the
   * reference will be stored for future use.
   *
   * If the attribute is not defined as Dao::REFERENCE, this method throws an
   * exception.
   *
   * @param string $attributeName
   * @return DaoReference
   * @uses $references
   */
  public function getReference($attributeName) {
    if (!isset($this->references[$attributeName])) {
      if ($this->getAttributeType($attributeName) !== Dao::REFERENCE) {
        Log::fatal("Can't create a reference for an attribute that is not of type Dao::REFERENCE.", 'Dao', array('Resource' => $this->getResourceName(), 'Attribute' => $attributeName));
        throw new DaoException("Can't create the reference for attribute $attributeName.");
      }

      $methodName = 'get' . ucfirst($attributeName) . 'Reference';

      if (!method_exists($this, $methodName)) {
        Log::fatal("Can't create a reference, because $methodName does not exist.", 'Dao', array('Resource' => $this->getResourceName(), 'Attribute' => $attributeName));
        throw new DaoException("The method $methodName does not exist to create the reference.");
      }

      $reference = $this->$methodName();
      if (!$reference || !is_a($reference, 'DaoReference'))
        trigger_error('The reference returned by ' . $this->resourceName . '/' . $methodName . ' is null or invalid.', E_USER_ERROR);
      $reference->setSourceDao($this);
      return $this->references[$attributeName] = $reference;
    }
    return $this->references[$attributeName];
  }

  /**
   * Creates a Dao.
   * You probably want to overwrite this method in your daos to use your
   * implementation of instantiating Daos.
   *
   * @param string $daoName E.g.: User
   * @return Dao
   */
  public function createDao($daoName) {
    $daoClassName = $daoName . 'Dao';
    return new $daoClassName();
  }

  /**
   * If your datasource holds different attribute names than you want in you Records, you can specify export & import mappings.
   * (Don't confuse this with your php values. Meaning: if the datasource value is 'strangely_named', but you want to access your record
   * like this: $record->perfectName, then you have to map it from strangely_named to perfect_name (not perfectName).)
   * E.g.: array('what_the_datasource_value_actually_is'=>'what_you_would_want_it_to_be');
   *
   * This variable is called attribute import mapping, but is used to export the attributes too.
   *
   * @var array
   */
  protected $attributeImportMapping = array();
  /**
   * This is an array containing all attributes that can be null. eg: $nullAttributes = array('email', 'name');
   *
   * @var array
   */
  protected $nullAttributes = array();
  /**
   * This is a list of attributes that have default values in the datasource.
   * This means that, if the values are NULL, and the entry is inserted in the datasource, they will not be
   * passed, so that the datasource can automatically set the values.
   * So when you call getRawRecord() those values will be null.
   * Per default the id is given.
   *
   * @var array
   */
  protected $defaultValueAttributes = array('id');

  /**
   * Calls find() and throws an error if null is returned, otherwise it just passes the Record
   * If you call get() without parameters, a "raw record" will be returned, containing
   * only default values, and null as id.
   *
   * @param int|array|Record $map A map or record containing the attribute assignments. If it's an integer it will be converted to array('id'=>INT)
   * @param bool $exportValues When you want to have complete control over the $map
   *                           attribute names, you can set exportValues to false, so they
   *                           won't be processed.
   *                           WARNING: Be sure to escape them yourself if you do so.
   * @param string $resourceName You can specify a different resource (most probably a view)
   *                          to get data from.
   *                          If not set, $this->viewName will be used if present; if not
   *                          $this->resourceName is used.
   * @return Record
   */
  public function get($map = null, $exportValues = true, $resourceName = null) {
    if (!$map)
      return $this->getRawRecord();
    $record = $this->find($map, $exportValues, $resourceName);
    if (!$record)
      throw new DaoNotFoundException('Did not find any record.');
    return $record;
  }

  /**
   * Returns the record, or null.
   * 
   * @param type $map
   * @param type $exportValues
   * @param type $resourceName 
   * @return Record
   */
  public function find($map, $exportValues = true, $resourceName = null) {
    $data = $this->findData($map, $exportValues, $resourceName);
    if (!$data)
      return null;
    return $this->getRecordFromPreparedData($data);
  }

  /**
   *
   * @param type $map
   * @param type $exportValues
   * @param type $resourceName
   * @return type 
   */
  public function getData($map = null, $exportValues = true, $resourceName = null) {
    $data = $this->findData($map, $exportValues, $resourceName);
    if (!$data)
      throw new DaoNotFoundException('Did not find any record.');
    return $data;
  }

  /**
   * This calls doFindData() to get the actual data, and then prepares and returns it for the record.
   * 
   * This is the actual method that takes care of memcaching.
   *
   * @param int|array|Record $map If it's an int, it will be converted to array('id'=>INT)
   * @param bool $exportValues
   * @param string $resourceName
   * @return array
   * @see doFindData()
   */
  public final function findData($map, $exportValues = true, $resourceName = null) {

    $id = null;

    if (is_int($map)) {
      $id = $map;
      $map = array('id' => $map);
    } elseif (is_array($map) && array_key_exists('id', $map)) {
      $id = $map['id'];
    } elseif (is_a($map, 'Record')) {
      $id = $map->id;
    }

    $cacheKey = null;
    if ($this->useCache && $this->cache && $id) {
      $cacheKey = $this->generateCacheKey($id);
      if ($recordData = $this->cache->get($cacheKey)) {
        // Exists in database, do not prepare data, because when the data comes
        // from the datasource, it gets prepared, and put in the record. This data
        // is directly put in the cache.
        return $recordData;
      }
    }

    $data = $this->doFindData($map, $exportValues, $resourceName);

    if (!$data)
      return null;

    $data = $this->prepareDataForRecord($data);

    if ($id && isset($data['id']) && $data['id'] != $id) {
      throw new DaoException('The id returned from the datasource was not the same as the id fetched.');
    }

    if ($cacheKey) {
      // It doesnt make sense to cache a record, if it never gets fetched by id.
      $this->cache->set($cacheKey, $data, $this->cacheExpire);
    }
    return $data;
  }

  /**
   * The same as get, but returns an iterator to go through all the rows.
   *
   * @param Record|array $map
   * @param array|string $sort
   * @param int $offset
   * @param int $limit
   * @param bool $exportValues
   * @param string $resourceName
   * @param array $additionalInfo Used to pass additional info to the data source if needed.
   *
   * @param bool $retrieveTotalRowCount 
   */
  public function getIterator($map, $sort = null, $offset = null, $limit = null, $exportValues = true, $resourceName = null, $retrieveTotalRowCount = false, $additionalInfo = null) {
    $result = $this->getIteratorResult($map, $sort, $offset, $limit, $exportValues, $resourceName, $retrieveTotalRowCount, $additionalInfo);
    return $this->createIterator($result);
  }

  /**
   * Creates an iterator from a result.
   * 
   * @return DaoResultIterator
   */
  abstract public function createIterator($result);

  /**
   * The key is: $cachePrefix . $resourceName . '_' . $id
   *
   * @param int|string $id 
   */
  protected function generateCacheKey($id) {
    return $this->cachePrefix . $this->resourceName . '_' . $id;
  }

  /**
   * This function actually gets the data.
   *
   * @param array|Record $map
   * @param bool $exportValues
   * @param string $resourceName
   * @return array
   * @see getData()
   */
  abstract protected function doFindData($map, $exportValues, $resourceName);

  /**
   * Forwards to doGetIteratorResult.
   * Overwrite this to do any iterator result caching.
   * 
   * @param Record|array $map
   * @param array|string $sort
   * @param int $offset
   * @param int $limit
   * @param bool $exportValues
   * @param string $resourceName
   * @param bool $retrieveTotalRowCount 
   * @param array $additionalInfo
   * @return result
   */
  public function getIteratorResult($map, $sort, $offset, $limit, $exportValues, $resourceName, $retrieveTotalRowCount, $additionalInfo) {
    return $this->doGetIteratorResult($map, $sort, $offset, $limit, $exportValues, $resourceName, $retrieveTotalRowCount, $additionalInfo);
  }

  /**
   * This method actually gets the data.
   *
   *
   * @param Record|array $map
   * @param array|string $sort
   * @param int $offset
   * @param int $limit
   * @param bool $exportValues
   * @param string $resourceName
   * @param bool $retrieveTotalRowCount 
   * @param array $additionalInfo
   * @return result
   */
  abstract protected function doGetIteratorResult($map, $sort, $offset, $limit, $exportValues, $resourceName, $retrieveTotalRowCount, $additionalInfo);

  /**
   * Given the $sort parameter, it generates a sort String used in the query.
   * If $sort is not provied, $defaultSort is used.
   *
   * @see $defaultSort
   * @param string|array $sort
   */
  abstract protected function generateSortString($sort);

  /**
   * Returns the attributes array
   * @return array
   */
  public function getAttributes() {
    return $this->attributes;
  }

  /**
   * Returns the type of an attribute (eg.: Dao::INT, etc..)
   * @param string $attributeName
   * @return int null if the attribute does not exist.
   */
  public function getAttributeType($attributeName) {
    if (isset($this->attributes[$attributeName]))
      return $this->attributes[$attributeName];
    else
      return null;
  }

  /**
   * Returns the resource name
   * @return string
   */
  public function getResourceName() {
    return $this->resourceName;
  }

  /**
   * Returns the additional attribute types array
   * @return array
   */
  public function getAdditionalAttributes() {
    return $this->additionalAttributes;
  }

  /**
   * Returns the null attributes
   * @return array
   */
  public function getNullAttributes() {
    return $this->nullAttributes;
  }

  /**
   * Returns the default value attributes
   *
   * @return array
   */
  public function getDefaultValueAttributes() {
    return $this->defaultValueAttributes;
  }

  /**
   * @return Cache
   */
  public function getCache() {
    return $this->cache;
  }

  /**
   * @param Cache $cache 
   */
  public function setCache($cache) {
    $this->cache = $cache;
  }

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
   * @param Record $record
   * @see Record
   */
  protected function afterInsert($record) {
    
  }

  /**
   * If you have to do stuff after an update, overwrite this function.
   * It gets called by the Dao after doing an update.
   *
   * @param Record $record
   * @see Record
   */
  protected function afterUpdate($record) {
    
  }

  /**
   * If you have to do stuff after a deletion, overwrite this function.
   * It gets called by the Dao after deleting.
   *
   * @param Record $record
   * @see Record
   */
  protected function afterDelete($record) {
    
  }

  /**
   * Checks if a attribute name exists as real or additional attribute.
   *
   * @param string $attributeName
   */
  protected function attributeExists($attributeName) {
    return isset($this->attributes[$attributeName]) || isset($this->additionalAttributes[$attributeName]);
  }

  /**
   * This is a wrapper for get() and the id as parameter.
   * @param int $id
   * @return Record
   * @see Record
   */
  public function getById($id) {
    return $this->get(array('id' => intval($id)));
  }

  /**
   * This is a wrapper for find() and the id as parameter.
   * @param int $id
   * @return Record
   * @see Record
   */
  public function findId($id) {
    return $this->find(array('id' => intval($id)));
  }

  /**
   * Gets the record by id and deletes it.
   * 
   * @param int $id
   */
  public function deleteById($id) {
    $record = $this->getById($id);
    $this->delete($record);
  }

  /**
   * Returns all rows (but you can specify offset &amp; limit)
   *
   * @param string|array $sort
   * @param int $offset
   * @param int $limit
   * @param bool $retrieveTotalRowCount
   * @return DaoResultIterator
   * @see DaoResultIterator
   */
  public function getAll($sort = null, $offset = null, $limit = null, $retrieveTotalRowCount = false) {
    return $this->getIterator(array(), $sort, $offset, $limit, true, null, $retrieveTotalRowCount);
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
   * Takes an attributeName and sees if there is an import/export mapping for it.
   * It then returns the correct attribute name, unescaped.
   *
   * @param string $attributeName
   * @return string
   * @see $attributeImportMapping
   */
  protected function applyAttributeImportMapping($attributeName) {
    if (isset($this->attributeImportMapping[$attributeName])) {
      return $this->attributeImportMapping[$attributeName];
    }
    return $attributeName;
  }

  /**
   * Takes an attributeName and sees if there is an import/export mapping for it.
   * It then returns the correct attributeName, unescaped.
   *
   * @param string $attributeName
   * @return string
   * @see $attributeImportMapping
   */
  protected function applyAttributeExportMapping($attributeName) {
    if (in_array($attributeName, $this->attributeImportMapping)) {
      return array_search($attributeName, $this->attributeImportMapping);
    }
    return $attributeName;
  }

  /**
   * Checks if there is an import mapping, applies it if there is, and converts the attribute to a php name.
   *
   * @param string $attributeName
   * @return string
   * @see applyAttributeImportMapping()
   * @see convertAttributeNameToPhpName()
   */
  public function importAttributeName($attributeName) {
    return $this->convertAttributeNameToPhpName($this->applyAttributeImportMapping($attributeName));
  }

  /**
   * Converts a php attribute name to the datasource name.
   * Per default this simply transforms theAttribute to the_attribute.
   * If you data source handles names differently, overwrite this methods.
   *
   * @param string $attributeName
   * @return string
   * @see convertAttributeNameToPhpName()
   */
  protected function convertAttributeNameToDatasourceName($attributeName) {
    return preg_replace('/([A-Z])/e', 'strtolower("_$1");', $attributeName);
  }

  /**
   * Converts a datasource attribute name to the php name.
   * Per default this simply transforms the_attribute to theAttribute.
   *
   * @param string $attributeName
   * @return string 
   * @see convertAttributeNameToDatasourceName()
   */
  protected function convertAttributeNameToPhpName($attributeName) {
    return preg_replace('/_([a-z])/e', 'strtoupper("$1");', $attributeName);
  }

  /**
   * Returns true if the attribute can not be null.
   *
   * @return bool
   */
  public function notNull($attributeName) {
    return!in_array($attributeName, $this->nullAttributes);
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
   * Returns the arrays containing the attributes, and values to perform an insert.
   * Values that are null are simply left out. So are Dao::IGNORE types.
   *
   * Attributes of type Dao::REFERENCE are left out, if the DaoReference has set
   * export to false.
   *
   * @param Record $record
   * @return array With exported $attributeName as index and exported $value as value.
   */
  protected function getInsertValues($record) {
    $attributes = array();
    $id = null;
    foreach ($this->attributes as $attributeName => $type) {
      if ($type === Dao::IGNORE)
        continue;
      if ($type === Dao::REFERENCE) {
        $reference = $this->getReference($attributeName);
        if (!$reference->export())
          continue;
        $value = $record->getDirectly($attributeName);
      }
      else {
        $value = $record->get($attributeName);
      }
      if ($value !== null) {
        $attributes[$this->exportAttributeName($attributeName)] = $this->exportValue($attributeName, $value, $type, $this->notNull($attributeName));
      }
    }
    return $attributes;
  }

  /**
   * Retruns a list of attributes that should be present in an update.
   * 
   * The returned array looks exactly the same as with getInsertAttributes.
   *
   * This should always be used to get the attributes since it takes into consideration
   * the DaoReferences and their export attributes.
   *
   * @param Record $record
   * @return array
   * @todo merge with getInsertAttributes() because they are nearly identical.
   */
  protected function getUpdateValues($record) {
    $attributes = array();
    foreach ($this->attributes as $attributeName => $type) {
      $addToAttributes = false;
      if ($attributeName !== 'id' && $type !== Dao::IGNORE) {
        if ($type === Dao::REFERENCE) {
          if ($this->getReference($attributeName)->export()) {
            $addToAttributes = true;
            $value = $record->getDirectly($attributeName);
          }
        } else {
          $addToAttributes = true;
          $value = $record->get($attributeName);
        }
      }
      if ($addToAttributes) {
        $attributes[$this->exportAttributeName($attributeName)] = $this->exportValue($attributeName, $value, $type, $this->notNull($attributeName));
      }
    }

    return $attributes;
  }

  /**
   * Returns a data record with the data in it.
   * Override this function if you need to instantiate your record differently.
   * one.
   *
   * @param array $data
   * @param bool $existsInDatabase
   * @return Record
   */
  protected function getRecordFromPreparedData($data, $existsInDatabase = true) {
    return new $this->recordClassName($data, $this, $existsInDatabase);
  }

  /**
   * Prepares the data, and gets a new record.
   * This is the way you should get a Record from datasource-data with.
   *
   * @param array $data The data returned from the datasource
   * @param bool $existsInDatabase
   * @param bool $prepareData If true, gprepareDataForRecord() is called before getting the record.
   * @see prepareDataForRecord()
   * @see getRecordFromPreparedData()
   * @return Record
   */
  public function getRecordFromData($data, $existsInDatabase = true, $prepareData = true) {
    if ($prepareData === true)
      $data = $this->prepareDataForRecord($data);
    return $this->getRecordFromPreparedData($data);
  }

  /**
   * Prepares the data, and updates the record
   *
   * @param array $data The data returned from the datasource
   * @param Record $record The record to be updated
   * @see prepareDataForRecord()
   * @return void
   */
  public function updateRecordWithData($data, $record) {
    $record->setData($this->prepareDataForRecord($data));
  }

  /**
   * Goes through the data array returned from the datasource, and converts the values that are necessary.
   * Meaning: if some values are null, check if they are allowed to be null.
   * This function also checks if every attribute in the attributes array has been transmitted
   *
   * @param array $data
   * @return array
   */
  protected function prepareDataForRecord($data) {
    $recordData = array();

    $neededValues = $this->attributes;

    if (!is_array($data))
      trigger_error('The data provided was not an array (' . $this->resourceName . ').', E_USER_ERROR);

    foreach ($data as $attributeName => $value) {
      $attributeName = $this->importAttributeName($attributeName);
      if (array_key_exists($attributeName, $this->attributes)) {
        unset($neededValues[$attributeName]);
        if ($this->attributes[$attributeName] !== Dao::IGNORE) {
          $recordData[$attributeName] = $this->importValue($attributeName, $value, $this->attributes[$attributeName], $this->notNull($attributeName));
        }
      } elseif (array_key_exists($attributeName, $this->additionalAttributes)) {
        if ($this->additionalAttributes[$attributeName] !== Dao::IGNORE) {
          $recordData[$attributeName] = $this->importValue($attributeName, $value, $this->additionalAttributes[$attributeName], $this->notNull($attributeName));
        }
      } else {
        $trace = debug_backtrace();
        trigger_error('The type for attribute "' . $attributeName . '" (resource: "' . $this->resourceName . '") is not defined', E_USER_WARNING);
      }
    }
    foreach ($neededValues as $attributeName => $type) {
      if ($type !== Dao::IGNORE && $type !== Dao::REFERENCE) {
        if ($this->notNull($attributeName)) {
          $trace = debug_backtrace();
          trigger_error('The attribute "' . $attributeName . '" (resource: "' . $this->resourceName . '") was not transmitted from data source', E_USER_WARNING);
          $recordData[$attributeName] = $this->coerce($attributeName, null, $type, false, $quiet = true);
        } else {
          $recordData[$attributeName] = null;
        }
      }
    }
    return $recordData;
  }

  /**
   * Returns a record with all attributes defined, but only set if necessary.
   * nullAttributes will be null as well as defaultValueAttributes. All other attributes will have a default value set with coerce().
   * Be careful! This function will soon be protected, and should not be called anymore! Use get() (without map) instead (which will call
   * getRawRecord() for you).
   * You can not depend on this function... it is subject to change.
   *
   * @see $nullAttributes
   * @see defaultValueAttributes
   * @see $attributes
   * @see coerce()
   * @see get()
   * @return Record
   */
  public function getRawRecord() {
    $data = array();
    foreach ($this->attributes as $attributeName => $type) {
      if (in_array($attributeName, $this->nullAttributes) || in_array($attributeName, $this->defaultValueAttributes)) {
        $data[$attributeName] = null;
      } elseif ($type != Dao::IGNORE)
        $data[$attributeName] = $this->coerce($attributeName, null, $type, $allowNull = false, $quiet = true);
    }
    return $this->getRecordFromPreparedData($data, $existsInDatabase = false);
  }

  /**
   * Imports an external value (either from datasource, or xml, etc...) into an
   * expected PHP variable.
   * If the attribute can be null, null will be returned; otherwise this calls
   * the appropriate method internally (importDate(), importString(), etc...)
   * and passes it 3 parameters: $externalValue, $type, $attributeName.
   *
   * @param string $attributeName
   * @param mixed $externalValue The value to be imported
   * @param int $type The type (selected from Dao)
   * @param bool $notNull Whether the value can be null or not
   * @return mixed
   */
  public function importValue($attributeName, $externalValue, $type, $notNull = true) {
    if (!$notNull && $externalValue === null) {
      return null;
    }

    try {
      $importMethod = 'import' . (is_array($type) ? 'Enum' : $type);
      if (!method_exists($this, $importMethod))
        throw new DaoException('The import method ' . $importMethod . ' does not exist.');
      return $this->$importMethod($externalValue, $type, $attributeName);
    } catch (DaoException $e) {
      throw new DaoException('There was an error importing the attribute "' . $attributeName . '" in resource "' . $this->resourceName . '": ' . $e->getMessage());
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
   */
  public function importDate($value) {
    return $this->convertRemoteValueToTimestamp($value, false);
  }

  /**
   * Calls convertRemoteValueToTimestamp and returns a Date Object.
   * If you do not count on implementing this just overwrite the function and throw a DaoNotSupportedException inside.
   *
   * @param string $value
   */
  public function importDateWithTime($value) {
    return $this->convertRemoteValueToTimestamp($value, true);
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
    if (!in_array($value, $list))
      throw new DaoException("The value provided is not defined in the enum.");
    return (string) $value;
  }

  /**
   * This function tries to convert strings integers and stuff into a bool.
   *
   * @param mixed $value
   * @return bool
   */
  public function importBool($value) {
    if (!$value)
      return false;
    if (is_int($value))
      return true;
    $value = strtolower($value);
    switch ($value) {
      case 'false': case 'f': case '0': return false;
        break;
      case 'true': case 't': case '1': return true;
        break;
      default: throw new DaoException("The boolean ($value) could not be converted.");
        break;
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
   * Just makes sure the reference is not null
   *
   * @param mixed $value
   * @return mixed
   */
  public function importReference($value, $type, $attributeName) {
    if ($value === null)
      throw new DaoException('Reference is marked as not null, but the value to import is null.');
    return $this->getReference($attributeName)->importValue($value);
  }

  /**
   * Exports a PHP value into a value understood by the Database
   *
   * This calls the appropriate export method (exportNull(), exportString(),
   * etc...).
   *
   * @param string $attributeName
   * @param mixed $internalValue The value to be imported
   * @param int $type The type (selected from Dao)
   * @param bool $notNull Whether the value can be null or not
   *
   * @return mixed
   */
  public function exportValue($attributeName, $internalValue, $type, $notNull = true) {
    if (!$notNull && $internalValue === NULL)
      return $this->exportNull();

    if ($type === Dao::IGNORE)
      return $this->exportNull();

    try {
      if (is_array($type)) {
        // Enum
        $exportMethod = 'exportEnum';
      } else {
        $exportMethod = 'export' . $type;
        if (!method_exists($this, $exportMethod))
          throw new DaoException('The export method ' . $exportMethod . ' does not exist.');
      }
      return $this->$exportMethod($internalValue, $attributeName, $type);
    } catch (DaoException $e) {
      throw new DaoException('There was an error exporting the attribute "' . $attributeName . '" in resource "' . $this->resourceName . '": ' . $e->getMessage());
    }
  }

  /**
   * Takes a php attribute name, converts it via import/export attribute mapping, converts it to the datasource name
   * and calls escapeAttributeName.
   * This is the correct way to insert attribute names in a map.
   *
   * @param string $attributeName
   * @return string
   * @see applyAttributeExportMapping()
   * @see convertAttributeNameToDatasourceName()
   * @see escapeAttributeName()
   */
  public function exportAttributeName($attributeName) {
    return $this->escapeAttributeName($this->applyAttributeExportMapping($this->convertAttributeNameToDatasourceName($attributeName)));
  }

  /**
   * Escapes a resource name.
   * If no resourceName is provied $this->resourceName will be used.
   * By default it simply calls escapeResourceName
   *
   * @param string $resourceName
   * @return string The escaped and quoted resource name.
   */
  public function exportResourceName($resourceName = null) {
    return $this->escapeResourceName($resourceName ? $resourceName : $this->resourceName);
  }

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
   * Should be used whenever an id gets exported.
   * 
   * At the moment this only supports integers, but I plan to support more id types in the future
   * 
   * @param int $id
   * @return int
   */
  public function exportId($id) {
    return $this->exportInteger($id);
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
   * @return int a timestamp
   */
  public function exportDate($date) {
    return $this->exportInteger($date->getTimestamp());
  }

  /**
   * @param Date $date
   * @return int a timestamp
   */
  public function exportDateWithTime($date) {
    return $this->exportInteger($date->getTimestamp());
  }

  /**
   * If the value is in the enum list, it calls exportString, and returns it.
   * Throws a DaoException if not.
   * @param string $value
   * @param array $list
   * @return string
   */
  public function exportEnum($value, $attributeName, $list) {
    if (!in_array($value, $list))
      throw new DaoWrongValueException("The value provided is not defined in the enum.");
    return $this->exportString($value);
  }

  /**
   * Exports a sequence. The generic Dao really can't handle this.
   *
   * This has to be implemented by each specific dao.
   *
   * @param mixed $value
   * @return mixed
   */
  public function exportSequence($value) {
    throw new DaoException('The generic Dao does not support exporting Sequences.');
  }

  /**
   * Calls exportValue on the reference.
   * @param mixed $value
   * @param string $attributeName
   * @return int
   */
  public function exportReference($value, $attributeName) {
    return $this->getReference($attributeName)->exportValue($value);
  }

  /**
   * When set() is called on a record, the record coerces the value into the correct
   * typ by calling this coerce() method.
   *
   * If the type is a reference, then the coerce method on the reference is called.
   *
   * @param string $attributeName
   * @param mixed $value
   * @param int $type one of Dao::INT, Dao::STRING, etc..
   * @param bool $allowNull
   * @param bool $quiet If true, no warnings are displayed
   * @return mixed
   */
  public function coerce($attributeName, $value, $type, $allowNull = false, $quiet = false) {
    if ($allowNull && $value === null) {
      return null;
    }
    try {
      if ($type === Dao::IGNORE) {
        Log::warning('Trying to coerce a value that is of type ignore!', 'Record', array('attributeName' => $attributeName, 'dao' => $this->getResourceName()));
        return null;
      }

      if (is_array($type)) {
        if (!count($type))
          trigger_error("Invalid enum for '" . $this->getResourceName() . ".$attributeName'.", E_USER_ERROR);
        $coerceMethod = 'coerceEnum';
      }
      else {
        $coerceMethod = 'coerce' . $type;
        if (!method_exists($this, $coerceMethod))
          throw new DaoException('The coerce method ' . $coerceMethod . ' does not exist.');
      }

      return $this->$coerceMethod($value, $attributeName, $type);
    } catch (DaoCoerceException $e) {
      $message = $e->getMessage();
      if (!$quiet && !empty($message))
        trigger_error($message . " (" . $this->getResourceName() . ".$attributeName)", E_USER_WARNING);
      return $allowNull ? null : $e->getFallbackValue();
    }
  }

  /**
   * At the moment this only supports integers, but I plan on supporting more.
   * 
   * If the value is an object, it returns the id of the object.
   * 
   * @param mixed $id
   * @return mixed 
   * @throws DaoCoerceException
   */
  public function coerceId($id) {
    if (is_object($id) && $id instanceof Record)
      return $id->get('id');

    if (is_int($id) || is_numeric($id))
      return (int) $id;

    throw new DaoCoerceException(null, "Invalid id provided.");
  }

  /**
   * @param mixed $value
   * @param string $attributeName
   * @param array $list
   * @return mixed 
   * @throws DaoCoerceException
   */
  public function coerceEnum($value, $attributeName, $list) {
    if (in_array($value, $list))
      return $value;

    throw new DaoCoerceException($list[0], "Invalid enum value provided.");
  }

  /**
   * @param mixed $value
   * @return bool
   * @throws DaoCoerceException
   */
  public function coerceBool($value) {
    if ($value === true || $value === 'true' || $value === '1' || $value === 1)
      return true;
    if ($value === false || $value === 'false' || $value === '0' || $value === 0)
      return false;

    throw new DaoCoerceException(true, "Invalid boolean provided.");
  }

  /**
   * @param mixed $value
   * @return int
   * @throws DaoCoerceException
   */
  public function coerceInteger($value) {
    if (is_int($value) || is_numeric($value)) {
      return (int) $value;
    }

    throw new DaoCoerceException(0, "Invalid integer provided.");
  }

  /**
   * @param mixed $value
   * @return float
   * @throws DaoCoerceException
   */
  public function coerceFloat($value) {
    if (is_float($value) || is_numeric($value))
      return (float) $value;

    throw new DaoCoerceException(0.0, "Invalid float provided.");
  }

  /**
   * @param mixed $value
   * @return int
   * @throws DaoCoerceException
   */
  public function coerceDate($value) {
    if ($value instanceof Date)
      return $value->getTimestamp();

    if (is_numeric($value))
      return (int) $value;

    throw new DaoCoerceException(time(), "Invalid date provided.");
  }

  /**
   * @param mixed $value
   * @return int
   * @throws DaoCoerceException
   * @uses coerceDate
   */
  public function coerceDateWithTime($value) {
    return $this->coerceDate($value);
  }

  /**
   * @param mixed $value
   * @return string
   * @throws DaoCoerceException
   */
  public function coerceString($value) {
    if (is_string($value) || is_numeric($value))
      return (string) $value;

    throw new DaoCoerceException('', "Invalid date provided.");
  }

  /**
   * @param mixed $value
   * @return array
   * @throws DaoCoerceException
   */
  public function coerceSequence($value) {
    if (is_array($value))
      return $value;

    throw new DaoCoerceException(array(), "Invalid sequence provided.");
  }

  /**
   * @param mixed $value
   * @param string $attributeName
   * @return mixed 
   * @throws DaoCoerceException because the Reference coerce function might throw it.
   */
  public function coerceReference($value, $attributeName) {
    return $this->getReference($attributeName)->coerce($value);
  }

  /**
   * Tries to interpret a $sort parameter, which can be one of following:
   *
   * - A string: It will be interpreted as one ascending attribute.
   * - An array containing strings: It will be cycled through and every string is interpreted as ascending attribute
   * - A map (associative array): It will be interpreted as attributeName=>sortType. E.g: array('name'=>Dao::DESC, 'age'=>Dao::ASC)
   *
   * @param string|array $sort
   * @return array An array containing all attributes to sort by, escaped, and ASC or DESC appended. E.g.: array('name DESC', 'age');
   */
  protected function interpretSortVariable($sort) {
    if (!is_array($sort)) {
      return $this->attributeExists($sort) ? array($this->exportAttributeName($sort)) : null;
    }

    if (count($sort) == 0)
      return null;

    $attributeArray = array();
    if (self::isVector($sort)) {
      foreach ($sort as $attributeName) {
        if ($this->attributeExists($attributeName))
          $attributeArray[] = $this->exportAttributeName($attributeName);
      }
    }
    else {
      foreach ($sort as $attributeName => $sort) {
        if ($this->attributeExists($attributeName))
          $attributeArray[] = $this->exportAttributeName($attributeName) . ($sort == Dao::DESC ? ' desc' : '');
      }
    }

    return $attributeArray;
  }

}

