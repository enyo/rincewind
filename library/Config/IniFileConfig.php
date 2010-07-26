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
   * @var string
   */
  protected $configFileUri;

  /**
   * @var string
   */
  protected $defaultConfigFileUri;

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

    $this->configFileUri = $configFileUri;
    $this->defaultConfigFileUri = $defaultConfigFileUri;
  }


  /**
   * Loads and caches the config files
   */
  public function load() {
    if ($this->config) return;
    if ($this->defaultConfigFileUri) $this->config = $this->mergeConfigArrays(parse_ini_file($this->defaultConfigFileUri, $this->useSections), parse_ini_file($this->configFileUri, $this->useSections));
    else                             $this->config = parse_ini_file($this->configFileUri, $this->useSections);
  }


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


