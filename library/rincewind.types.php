<?php

/**
 * This file defines some types
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 */

/**
 * The rincewind type interface
 * 
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 */
interface RW_Type {

  /**
   * Sets the var, and validates!
   */
  public function __construct($var);

  /**
   * Returns the var.
   * @return mixed
   */
  public function get();
}

/**
 * Thrown when the type could not be validated.
 */
class RW_TypeException extends Exception {
  
}

/**
 * Extend this to implement the types.
 */
abstract class RW_TypeBase implements RW_Type {

  /**
   * The var
   */
  protected $var;

  /**
   * @param mixed$var 
   */
  public function __construct($var) {
    $this->var = $var;
    if ( ! $this->validate()) throw new RW_TypeException();
  }

  /**
   * Implement this to validate the type.
   * This also sets the correct type to $var. So if you implement RW_Int, make sure
   * this method sets an integer as var.
   * @return bool true on success false on failure
   */
  abstract protected function validate();

  /**
   * Returns the var
   * @return mixed
   */
  public function get() {
    return $this->var;
  }

}

/**
 * The rincewind int type
 * 
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 */
class RW_Int extends RW_TypeBase implements RW_Type {

  /**
   * Checks the type.
   */
  public function validate() {
    if (is_int($this->var)) {
      return true;
    }
    elseif (is_float($this->var)) {
      if ((float) (int) $this->var === $this->var) {
        $this->var = (int) $this->var;
        return true;
      }
    }
    elseif (is_string($this->var)) {
      if ((string) (int) $this->var === $this->var) {
        $this->var = (int) $this->var;
        return true;
      }
    }

    return false;
  }

}

/**
 * The rincewind float type
 * 
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 */
class RW_Float extends RW_TypeBase implements RW_Type {

  /**
   * Checks the type.
   */
  public function validate() {
    if (is_numeric($this->var)) {
      $this->var = (float) $this->var;
    }

    return false;
  }

}
