<?php

/**
 * The file for the Renderer class
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Renderer
 */

/**
 * Base class for Renderer Exceptions
 *
 * @author Matthias Loitsch <m@tthias.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Renderer
 */
class RendererException extends Exception {
  
}

/**
 * The renderer interface
 *
 * @author Matthias Loitsch <m@tthias.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Renderer
 */
interface Renderer {

  /**
   * Returns a model.
   * 
   * @return mixed
   */
  public function getModel();
  
  /**
   * Renders the data with the template.
   *
   * @param string $siteName
   * @param mixed $data
   * @param bool $output Whether it should return or output the rendered page.
   * @return string null if output = true
   */
  public function render($templateName, $data, $output = true);
}

