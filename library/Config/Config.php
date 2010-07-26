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
   * The cached config array
   * @var array
   */
  protected $config;


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
   * Get a configuration value.
   *
   * @param string $variable
   * @param string $section If null, $this->defaultSection will be used if set.
   */
  public function get($variable, $section = null) {
    $this->load();

    $variable = $this->sanitizeToken($variable);


    $section = $section ? $section : $this->defaultSection;
    $section = $this->sanitizeToken($section);

    if ($this->useSections) {
      if (!$section) throw new ConfigException('No section set.');

      if (!isset($this->config[$section])) throw new ConfigException("Section '$section' does not exist.");
      if (!array_key_exists($variable, $this->config[$section])) throw new ConfigException("Variable '$section.$variable' does not exist.");

      return $this->config[$section][$variable];
    }
    else {
      if (!array_key_exists($variable, $this->config)) throw new ConfigException("Variable '$variable' does not exist.");

      return $this->config[$variable];
    }
    
  }


  /**
   * Implement this to load your configuration.
   * It should only load the configuration once! If you want to reload it, use reload() instead.
   */
  abstract public function load();


  /**
   * Use this to reload your configuration
   */
  public function reload() {
    $this->config = null;
    $this->load();
  }

  /**
   * @return array
   */
  public function getArray() { return $this->config; }


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



