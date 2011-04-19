<?php

/**
 * This file has to be included before any other rincewind library gets included.
 * It provides basic functionality for the framework.
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 */

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
    if ($fileUriOrService === null) {
      $fileUri = dirname(__FILE__) . '/' . $className . '/' . $className . '.php';
    }
    elseif (strpos($fileUriOrService, '/') !== 0) {
      $fileUri = dirname(__FILE__) . '/' . $fileUriOrService . '/' . $className . '.php';
    }
    else {
      $fileUri = $fileUriOrService;
    }
    include $fileUri;
  }
}