<?php

/**
 * This file contains the Config class definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Config
 **/

/**
 * Loading all exceptions
 */
include dirname(__FILE__) . '/ConfigExceptions.php';

/**
 * This is the Config interface.
 * It doesn't define much, but Config classes inheriting this class have to support sections (as in php.ini files).
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Config
 */
abstract class Config {


  /**
   * Whether sections should be used or not.
   *
   * @var bool
   */
  protected $useSections = true;

  /**
   * The default section. If this is set, this section will be used, if no section is specified when getting a variable.
   *
   * @var string
   */
  protected $defaultSection = 'general';

  /**
   * This gets all variables and returns an associative array with the sections and variables.
   *
   * @return array Associative array like this: array('sectionName'=>array('varName'=>value))
   */
  abstract public function getArray();
  
  /**
   * Get a configuration value.
   *
   * @param string $variable
   * @param string $section If null, $this->defaultSection will be used if set.
   */
  abstract public function get($variable, $section = null);


  /**
   * @param string $section
   * @see $defaultSection
   */
  public function setDefaultSection($section) {
    if (!$this->useSections) throw new ConfigException("Sections have been disabled for this config object.");
    $this->defaultSection = $section;
  }


  /**
   * Sanitizes a token by removing special chars, and coverting spaces to underscores
   *
   * @param $token
   */ 
  protected function sanitizeToken($token) {
    return strtolower(str_replace(array('?', '!', '.'), '', str_replace(array(':', ' '), '_', $token)));
  }


}



