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
abstract class ThemeRenderer implements Renderer {

  /**
   * @var Theme
   */
  protected $theme;

  /**
   * @param Theme $theme 
   */
  public function __construct(Theme $theme) {
    $this->theme = $theme;
  }

  /**
   * @param string $templateName
   * @return string
   */
  protected function getTemplateUri($templateName) {
    return $this->theme->getTemplateUri($templateName);
  }

  /**
   * @return string
   */
  protected function getTemplatesPath() {
    return $this->theme->getTemplatesPath();
  }

}

