<?php

/**
 * The file for the Model class
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Renderer
 */

/**
 * The model
 *
 * @author Matthias Loitsch <m@tthias.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Renderer
 */
class Model {

  /**
   * Holds the actual data.
   * @var array
   */
  protected $data = array();

  /**
   * @return array
   */
  public function getData() {
    return $this->data;
  }

  /**
   * Sets a value.
   *
   * @param string $name
   * @param mixed $value
   */
  public function assign($name, $value) {
    $this->data[$name] = $value;
  }

  /**
   * Returns a value
   *
   * @param string $name
   */
  public function get($name) {
    return $this->data[$name];
  }

}

