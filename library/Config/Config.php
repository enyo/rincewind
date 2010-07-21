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

    protected $section;
    
    /**
     * This gets all variables and returns an associative array with the sections and variables.
     *
     * @return array Associative array like this: array('sectionName'=>array('varName'=>value))
     */
    abstract public function getArray();
    
    /**
     * Get a configuration value.
     * Don't forget to unset $this->section when a section is passed here.
     *
     * @param string $variable
     * @param string $section If null, $this->section is used if set.
     */
    abstract public function get($variable, $section = null);

    
    /**
     * Set section for future get.
     * When get is called without a section argument, this section will be used.
     * If get is called with a section argument, the default section will be set to null.
     */
    public function setSection($section) {
      $this->section = $section;
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



