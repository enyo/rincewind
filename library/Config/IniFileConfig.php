<?php

/**
 * The basic implementation of the IniFileConfig
 *
 * @author Matthias Loitsch
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Config
 */

/**
 * Loading the base class
 */
if (!class_exists('Config', false)) include dirname(__FILE__) . '/Config.php';


/**
 * This is the IniFileConfig implementation of Config
 * It's written to parse default php.ini files to set the config.
 *
 * @package Config
 */
class IniFileConfig extends Config {
  
  /**
   * @var array
   */
  protected $config;
  
  /**
   * Parses the config file. If a defaultConfigFile is provided, they get merged, and the config file overwrites the default settings.
   * All variables do not have to exist in the defaultConfig file, but if a section in the defaultConfigFile does not exist, then
   * the section is ignored in the config file.
   *
   * @param string $configFileUri
   * @param string $defaultConfigFileUri
   * @param bool $useSections To overwrite the default
   * @param string $defaultSection To overwrite the default.
   */
  public function __construct($configFileUri, $defaultConfigFileUri = null, $useSections = null, $defaultSection = null) {
    if ($useSections !== null) $this->useSections = (bool) $useSections;
    if ($defaultSection !== null) $this->setDefaultSection($defaultSection);

    if ($defaultConfigFileUri) $this->config = $this->mergeConfigArrays(parse_ini_file($defaultConfigFileUri, $this->useSections), parse_ini_file($configFileUri, $this->useSections));
    else                       $this->config = parse_ini_file($configFileUri, $this->useSections);
  }
  
  /**
   * Just returns the appropriate indices.
   *
   * @param string $variable
   * @param string $section
   */
  public function get($variable, $section = null) {
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
   * @return array
   */
  public function getArray() { return $this->config; }
  
  /**
   * Merges to arrays
   *
   * @param array $defaultConfig
   * @param array $config
   */
  private function mergeConfigArrays($defaultConfig, $config) {
    if (!$this->useSections) {
      return array_merge($defaultConfig, $config);
    }
    else {
      foreach ($defaultConfig as $section=>$sectionVariables) {
        if (isset($config[$section])) {
          $sectionVariables = array_merge($sectionVariables, $config[$section]);
          $defaultConfig[$section] = $sectionVariables;
        }
      }
      return $defaultConfig;
    }
  }
  
}


