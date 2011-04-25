<?php

/**
 * This file has to be included before any other rincewind library gets included.
 * It provides basic functionality for the framework.
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 */

/**
 *
 * @param string $interfaceOrClassName 
 * @param string $fileUriOrService 
 * @return string the File URI
 */
function _rw_determineFileUri($interfaceOrClassName, $fileUriOrService) {
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
    include _rw_determineFileUri($className, $fileUriOrService);
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
    include _rw_determineFileUri($interfaceName, $fileUriOrService);
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
