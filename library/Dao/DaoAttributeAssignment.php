<?php

/**
 * This file contains the DaoAttributeAssignment definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 **/

/**
 * Sometimes an associative array is simply not enough to create a query.
 * The DaoAttributeAssignment helps adding a few functions.
 * When you select records with a Dao, you can either pass an associative array containing the value assignments or a list of DaoAttributeAssignments.
 * It is also possible to mix both (if the value is a DaoAttributeAssignment it is used as such)
 * An associative array is the same as an array of DaoAttributeAssignments where the operator is always =
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dao
 */
class DaoAttributeAssignment {
  /**
   * A list of operators available to use
   * @var array
   */
  protected $validOperators = array('=', '>', '<', '>=', '<=', '<>', ' like ');

  /**
   * The attribute name of the assignment
   * @var string
   */
  public $attributeName;

  /**
   * @var mixed
   */
  public $value;
  
  /**
   * @var string
   * @see $validOperators
   */
  public $operator;

  /**
   * All assignment info is passed in the constructor
   *
   * @param string $attributeName The attributeName (eg.: 'login')
   * @param string $value The value (eg.: 'tom')
   * @param string $operator Valid operators are: '=', '>', '<', '>=', '<=', '<>', ' like '
   */
  public function __construct($attributeName, $value, $operator = '=') {
    $this->attributeName = $attributeName;
    $this->value = $value;
    $this->operator = in_array($operator, $this->validOperators) ? $operator : '=';
  }
}

/**
 * This function is a shortcut for creating the DaoAttributeAssignment Object
 *
 * @param string $attributeName
 * @param mixed $value
 * @param string $operator
 * @see DaoAttributeAssignment::validOperators
 * @return DaoAttributeAssignment
 */
function createDaoAttributeAssignment($attributeName, $value, $operator = '=') { return new DaoAttributeAssignment($attributeName, $value, $operator); }
  
  
