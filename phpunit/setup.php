<?php

abstract class Log {

  static public $debugs = array();
  static public $infos = array();
  static public $warnings = array();
  static public $errors = array();
  static public $fatals = array();

  static public function debug($message) {
    self::$debug[] = $message;
  }

  static public function info($message) {
    self::$infos[] = $message;
  }

  static public function warning($message) {
    self::$warnings[] = $message;
  }

  static public function error($message) {
    self::$errors[] = $message;
  }

  static public function fatal($message) {
    self::$fatals[] = $message;
  }

}

define('TESTS_PATH', dirname(__FILE__) . '/');

define('RINCEWIND_PATH', dirname(dirname(__FILE__)) . '/library/');

define('REQUIRED_RINCEWIND_VERSION', 20306);
include RINCEWIND_PATH . 'rincewind.php';