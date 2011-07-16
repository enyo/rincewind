<?php

/**
 * The file for the Renderer class
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Renderer
 */
/**
 * Including Renderer interface
 */
require_interface('Renderer');

/**
 * The renderer interface
 *
 * @author Matthias Loitsch <m@tthias.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Renderer
 */
abstract class BaseRenderer implements Renderer {

  /**
   * @var string
   */
  protected $templatesPath;

  /**
   * @param string $templatesPath
   */
  public function __construct($templatesPath = null) {
    $this->templatesPath = $templatesPath;
  }

  /**
   * @return string
   */
  public function getTemplatesPath() {
    return $this->templatesPath;
  }

  /**
   * @param string $templatesPath 
   */
  public function setTemplatesPath($templatesPath) {
    $this->templatesPath = $templatesPath;
  }

}

