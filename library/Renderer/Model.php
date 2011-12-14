<?php

/**
 * The file for the Model class
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Renderer
 */

/**
 * ModelException
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Renderer
 */
class ModelException extends Exception {

}

/**
 * The model.
 *
 * This model holds published, and unpublished data.
 *
 * Published data can be presented to the user. This is data that can be serialized
 * as JSON, XML or plain text and be published. You should only put scalar variables
 * (bool, int, float, string) or arrays in this array.
 *
 * Unpublished data should never be shown to the user. It's used to render the view
 * but should not be included in the data sent to the user.
 *
 * CAUTION: Do not put sensitive data (eg: passwords) in either models! You should
 * not trust template authors to correctly handle this data objects, and should
 * assume that a template can potentially show both data objects.
 * This separation is more of a recommendation of what can and should be published.
 *
 * @author Matthias Loitsch <m@tthias.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Renderer
 */
class Model {
  /**
   * @var int
   */
  const PUBLISHABLE = 1;
  /**
   * @var int
   */
  const UNPUBLISHABLE = 2;


  /**
   * @var int
   */
  protected $defaultClearance = self::UNPUBLISHABLE;

  /**
   * Holds the published data.
   * @var array
   */
  protected $publishedData = array();

  /**
   * Holds the unpublished data.
   * @var array
   */
  protected $unpublishedData = array();

  /**
   * @return int either self::PUBLISHABLE or self::UNPUBLISHABLE
   */
  public function getDefaultClearance() {
    return $this->defaultClearance;
  }

  /**
   * @param int $defaultClearance either self::PUBLISHABLE or self::UNPUBLISHABLE
   */
  public function setDefaultClearance($defaultClearance) {
    if ($defaultClearance !== self::PUBLISHABLE && $defaultClearance !== self::UNPUBLISHABLE) throw new ModelException('Unknown visibility: ' . $defaultClearance);
    $this->defaultClearance = $defaultClearance;
  }

  /**
   * Returns both data objects merged if no clearance is provided.
   * 
   * @return int $clearance If provided, only one of them is returned.
   */
  public function getData($clearance = null) {
    switch ($clearance) {
      case self::PUBLISHABLE:
        return $this->publishedData;
        break;
      case self::UNPUBLISHABLE:
        return $this->unpublishedData;
        break;
    }

    return array_merge($this->unpublishedData, $this->publishedData);
  }

  /**
   * Sets a value.
   *
   * @param string $name
   * @param mixed $value
   * @param int $clearance null for $this->defaultClearance
   *
   */
  public function assign($name, $value, $clearance = null) {
    $clearance = ($clearance !== self::PUBLISHABLE && $clearance !== self::UNPUBLISHABLE) ? $this->defaultClearance : $clearance;
    switch ($clearance) {
      case self::PUBLISHABLE:
        $this->publishedData[$name] = $value;
        break;
      case self::UNPUBLISHABLE:
        $this->unpublishedData[$name] = $value;
        break;
    }
  }

  /**
   * Returns a value
   *
   * @param string $name
   */
  public function get($name, $clearance) {
    switch ($clearance) {
      case self::PUBLISHABLE:
        return $this->publishedData[$name];
        break;
      case self::UNPUBLISHABLE:
        return $this->unpublishedData[$name];
        break;
    }
  }

}

