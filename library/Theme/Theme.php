<?php

/**
 * The file for the Theme class
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Theme
 */

/**
 * Base class for Theme Exceptions
 *
 * @author Matthias Loitsch <m@tthias.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Theme
 */
class ThemeException extends Exception {
  
}

/**
 * The theme class is used to handle all theme specific settings.
 * It's also used to transform all templates in the site.
 *
 * @author Matthias Loitsch <m@tthias.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Theme
 */
class Theme {

  /**
   * Gets set in the constructor
   * 
   * @var string
   */
  protected $themesPath;
  /**
   * @var string
   * @see getToken()
   */
  protected $token;
  /**
   * @var array
   */
  protected $config;
  /**
   * @var string
   * @see getRootPath()
   */
  protected $rootPath;
  /**
   * The location of the config file, relative to the theme path.
   *
   * @var string
   */
  protected $configUri = 'config';

  /**
   * @param string $themesPath
   * @param string $token
   */
  public function __construct($themesPath, $token) {
    $this->themesPath = $themesPath;
    $this->token = $token;
  }

  /**
   * Returns the path to the theme.
   * This also caches the path.
   * This function is only used inside the theme, to get the root path. From outside, you simply call getPath()
   *
   * @return string
   */
  protected function getRootPath() {
    if (!$this->rootPath) {
      $this->rootPath = $this->themesPath . '/' . $this->getToken() . '/';
    }
    return $this->rootPath;
  }

  /**
   * Returns either the complete config array, or a specific config.
   * Calls loadConfig() to ensure the config is loaded.
   *
   * @param string $section
   * @param string $name
   * @return array|mixed
   * @see loadConfig()
   */
  public function getConfig($section = null, $name = null) {
    $this->loadConfig();

    if ($section && $name) {
      if (!isset($this->config[$section]) || !isset($this->config[$section][$name]))
        throw new RendererException("Unknown setting `$section`.`$name`.");
      return $this->config[$section][$name];
    }
    else
      return $this->config;
  }

  /**
   * If it's not set yet, looks up the id in the database, and gets the theme token.
   *
   * @return string
   */
  public function getToken() {
    return $this->token;
  }

  /**
   * @param string $token
   */
  public function setToken($token) {
    $this->token = $token;
  }

  /**
   * This returns the path to the theme if no resource is specified, or the path to a resource (images, stylesheets, etc..)
   * if specified.
   *
   * @param string $resource Eg: stylesheets, templates, etc...
   * @return string
   */
  public function getPath($resource = null) {
    if ($resource) {
      $path = $this->getRootPath();
      if ($resource === 'generated_images') {
        $path .= $this->getConfig('paths', 'images') . $this->getConfig('paths', 'generated_images');
      } else {
        $path .= $this->getConfig('paths', $resource);
      }
      return $path;
    } else {
      return $this->getRootPath();
    }
  }

  /**
   * This function loads all infos from the config file, and puts it in $this->config.
   * If $this->config is already there, does nothing.
   */
  protected function loadConfig() {
    if ($this->config)
      return;

    if (!is_dir($this->getRootPath())) {
      throw new RendererException("The theme folder " . $this->getRootPath() . " does not exist.");
    }
    if (!is_file($this->getRootPath() . $this->configUri)) {
      throw new RendererException("The config file (" . $this->getRootPath() . $this->configUri . ") was not found.");
    }
    $this->config = parse_ini_file($this->getRootPath() . $this->configUri, true);
  }

  /**
   * @return string
   */
  public function getTemplatesPath() {
    return $this->getPatch('templates');
  }

  /**
   * @param string $templateName
   * @return string The URI of the template, in the templates folder.
   */
  public function getTemplateUri($templateName) {
    return $this->getTemplatesPath() . $templateName . '.html';
  }

}

