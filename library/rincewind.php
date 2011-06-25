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
define('RINCEWIND_VERSION', 20303);

if ( ! defined('REQUIRED_RINCEWIND_VERSION')) trigger_error('You should set a REQUIRED_RINCEWIND_VERSION (' . RINCEWIND_VERSION . ').', E_USER_WARNING);

/**
 * @param int $version
 * @return string
 */
function RW_formatVersion($version) {
  $v0 = (int) ($version / 10000);
  $version -= $v0 * 10000;
  $v1 = (int) ($version / 100);
  $v2 = $version - $v1 * 100;
  return $v0 . '.' . $v1 . '.' . $v2;
}

/**
 * Triggers error if the required version is highe then the actual version.
 */
if (defined('REQUIRED_RINCEWIND_VERSION') && REQUIRED_RINCEWIND_VERSION > RINCEWIND_VERSION) {
  die('The required rincewind version (' . RW_formatVersion(REQUIRED_RINCEWIND_VERSION) . ') is higher than the actual version (' . RW_formatVersion(RINCEWIND_VERSION) . ').');
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
    $fileUri = dirname(__FILE__) . '/' . $interfaceOrClassName . '/' . $interfaceOrClassName . '.php';
  }
  elseif (strpos($fileUriOrService, '/') !== 0) {
    $fileUri = dirname(__FILE__) . '/' . $fileUriOrService . '/' . $interfaceOrClassName . '.php';
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
 * Now include the important static classes.
 */
/**
 * Loading the Log class.
 */
require_class('Log', 'Logger');

/**
 * Loading the Profile class.
 */
require_class('Profile', 'Profiler');
