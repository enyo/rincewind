<?php

/**
 * This file has to be included before any other rincewind library gets included.
 * It provides basic functionality for the framework.
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 */
/**
 * Defines the current rincewind version
 */
define('RINCEWIND_VERSION', '2.6.5-dev');

/**
 * Defines the path to the rincewind library.
 */
define('RINCEWIND_PATH', __DIR__ . '/');

if ( ! defined('REQUIRED_RINCEWIND_VERSION')) trigger_error('You should set a REQUIRED_RINCEWIND_VERSION (' . RINCEWIND_VERSION . ').', E_USER_WARNING);

abstract class RW {

  /**
   * @param int $version
   * @return string
   */
  public static function formatVersion($version) {
    $v0 = (int) ($version / 10000);
    $version -= $v0 * 10000;
    $v1 = (int) ($version / 100);
    $v2 = $version - $v1 * 100;
    return $v0 . '.' . $v1 . '.' . $v2;
  }

  /**
   * Checks if the provided file uri is writable (or if the parent directory is
   * writable if the file doesn't exist).
   * @param string $fileUri
   */
  public static function isWritable($fileUri) {
    return (file_exists($fileUri) && is_writable($fileUri)) || ( ! file_exists($fileUri) && is_writable(dirname($fileUri)));
  }

  /**
   * If the path is without leading /, then it will be appended to $root.
   * Otherwise it gets returned.
   *
   * @param string $path
   * @param string $root
   */
  public static function path($path, $root) {
    return strpos($path, '/') === 0 ? $path : $root . $path;
  }

}

/**
 * Triggers error if the required version is higher then the actual version.
 */
if (version_compare(RINCEWIND_VERSION, REQUIRED_RINCEWIND_VERSION) < 0) {
  die('The required rincewind version (' . REQUIRED_RINCEWIND_VERSION . ') is higher than the actual version (' . RINCEWIND_VERSION . ').');
}

/**
 * Includes the basic types.
 */
include dirname(__FILE__) . '/rincewind.types.php';

/**
 *
 * @param string $interfaceOrClassName
 * @param string $fileUriOrService
 * @return string the File URI
 */
function RW_determineFileUri($interfaceOrClassName, $fileUriOrService) {
  if ($fileUriOrService === null) {
    $fileUri = __DIR__ . '/' . $interfaceOrClassName . '/' . $interfaceOrClassName . '.php';
  }
  elseif (strpos($fileUriOrService, '/') !== 0) {
    $fileUri = __DIR__ . '/' . $fileUriOrService . '/' . $interfaceOrClassName . '.php';
  }
  else {
    $fileUri = $fileUriOrService;
  }
  return $fileUri;
}

/**
 * Includes a file only if class does not exist.
 *
 * @param string $className
 * @param string $fileUriOrService Can either be an absolute path to the file, or
 *                                 the relative path from the library root directory.
 *                                 If left empty the $className is assumed
 *                                 to be the service name
 */
function require_class($className, $fileUriOrService = null) {
  if ( ! class_exists($className, false)) {
    include RW_determineFileUri($className, $fileUriOrService);
  }
}

/**
 * Includes a file only if interface does not exist.
 *
 * @param string $interfaceName
 * @param string $fileUriOrService
 * @see require_class
 */
function require_interface($interfaceName, $fileUriOrService = null) {
  if ( ! interface_exists($interfaceName, false)) {
    include RW_determineFileUri($interfaceName, $fileUriOrService);
  }
}

/**
 * Now include the necessary static classes.
 */
/**
 * Loading the Log class.
 */
require_class('Log', 'Logger');

/**
 * Loading the Profile class.
 */
require_class('Profile', 'Profiler');
