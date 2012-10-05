<?php

/**
 * This file contains the Config class definition.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Config
 * */
/**
 * Loading all exceptions
 */
include dirname(__FILE__) . '/ConfigExceptions.php';

/**
 * This is the Config class. It implements basic functionality such as loading a configuration, and providing getters.
 * It also supports sections.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Config
 */
abstract class Config {

  /**
   * @var Cache
   */
  protected $cache;
  /**
   * Is used to prefix the keys in cache.
   * @var string
   */
  protected $cachePrefix = 'config__';
  /**
   * Seconds til the cache expires
   * @var int
   */
  protected $cacheExpire = 36000; // 10 hours
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
    $saveVariable = $variable;
    $variable = $this->sanitizeToken($variable);

    $section = $section ? $section : $this->defaultSection;
    $saveSection = $section;
    $section = $this->sanitizeToken($section);

    if ($saveVariable != $variable || $saveSection != $section) {
      Log::warning('Passed variable or section not sanitized.', 'Deprecated', array('section' => $saveSection . ' -- ' . $section, 'variable' => $saveVariable . ' -- ' . $variable));
    }


    $value = $this->getCached($variable, $found, $section);
    if ($found) {
      return $value;
    }

    $this->load();

    if ($this->useSections) {
      if ( ! $section) throw new ConfigException('No section set.');

      if ( ! array_key_exists($section, $this->config)) throw new ConfigException("Section '$section' does not exist.");
      if ( ! array_key_exists($variable, $this->config[$section])) throw new ConfigException("Variable '$section.$variable' does not exist.");

      return $this->config[$section][$variable];
    }
    else {
      if ( ! array_key_exists($variable, $this->config)) throw new ConfigException("Variable '$variable' does not exist.");

      return $this->config[$variable];
    }
  }

  /**
   * @param string $variable
   * @param string $section
   * @return string
   */
  public function generateCacheKey($variable, $section = '') {
    return $this->cachePrefix . $variable . ($this->useSections ? '__' . $section : '');
  }


  /**
   * Caches the complete config
   */
  protected function cacheConfig() {
    if ($this->cache) {
      if ($this->useSections) {
        foreach ($this->config as $section => $variables) {
          foreach ($variables as $variable => $value) {
            $this->cache->set($this->generateCacheKey($variable, $section), $value, $this->cacheExpire);
          }
        }
      }
      else {
        foreach ($this->config as $variable => $value) {
          $this->cache->set($this->generateCacheKey($variable), $value, $this->cacheExpire);
        }
      }
    }
  }

  /**
   * Returns the cached value if present.
   * @param  string $variable
   * @param  bool $found Gets set to true if found
   * @param  string $section optional
   * @return mixed
   */
  public function getCached($variable, &$found, $section = null) {
    if (!$this->cache) {
      $found = false;
      return;
    }
    $key = $this->generateCacheKey($variable, $section);
    return $this->cache->get($key, $found);
  }


  /**
   * @return Cache
   */
  public function getCache() {
    return $this->cache;
  }

  /**
   * Calls doLoad() and cacheConfig().
   * Does nothing if the config is already loaded.
   * If you want to reload it, use reload() instead.
   * @return [type] [description]
   */
  public final function load() {
    if ($this->config) return;
    $this->doLoad();
    $this->cacheConfig();
  }

  /**
   * Use this to reload your configuration
   */
  public function reload() {
    $this->clear();
    $this->load();
  }


  /**
   * Implement this to actually load your configuration.
   * This function should put fill the `$this->config` array.
   */
  abstract protected function doLoad();

  /**
   * Sets $config to null;
   * @uses $config
   */
  public function clear() {
    $this->config = null;
  }

  /**
   * @param bool $oneDimensional If set to true, and the config uses sections, the sections will be used to prefix the names.
   * @return array
   */
  public function getArray($oneDimensional = false) {
    $this->load();
    if ($this->useSections && $oneDimensional) {
      $return = array();
      foreach ($this->config as $section => $variables) {
        foreach ($variables as $variable => $content) {
          $return[$section . '.' . $variable] = $content;
        }
      }
      return $return;
    }
    else {
      return $this->config;
    }
  }

  /**
   * @param string $section
   * @see $defaultSection
   */
  public function setDefaultSection($section) {
    if ( ! $this->useSections) throw new ConfigException("Sections have been disabled for this config object.");
    $this->defaultSection = $section;
  }

  /**
   * Sanitizes a token by removing special chars, and coverting spaces to underscores
   *
   * @param $token
   */
  public function sanitizeToken($token) {
    return strtolower(str_replace(array('?', '!', '.'), '', str_replace(array(':', ' '), '_', $token)));
  }

}

