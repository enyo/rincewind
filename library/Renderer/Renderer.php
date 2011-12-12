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
   * Checks if this class can actually handle the view.
   * Overwrite this to implement your logic.
   *
   * @param string $viewName
   * @param string $templatesPath
   * @param string $requestedContentType
   */
  static public function accepts($viewName, $templatesPath, $requestedContentType = null);

  /**
   * Renders the data with the template.
   *
   * @param string $view
   * @param Model $model
   * @param bool $output Whether it should return or output the rendered page.
   * @return string null if output = true
   */
  public function render($view, Model $model, $output = true);

  /**
   * @return string
   */
  public function getTemplatesPath();

  /**
   * If you don't need/use templates, just implement an empty method.
   * @param string $path
   */
  public function setTemplatesPath($path);
}

