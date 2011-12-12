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
   * @param string $view
   * @param mixed $model
   * @param bool $output Whether it should return or output the rendered page.
   * @return string null if output = true
   */
  public function render($view, $model, $output = true);

  /**
   * Renders the data with the template.
   * This render method assumes that the model attribute is set and valid, and
   * there has been an error processing the action, not initializing the data.
   *
   *
   * @param int $errorCode
   * @param mixed $model
   * @param bool $output Whether it should return or output the rendered page.
   * @return string null if output = true
   */
  public function renderError($errorCode, $model, $output = true);


  /**
   * Gets called when there was a problem initializing the data.
   * The template this renders should not access any data in the model.
   *
   * @param bool $output
   */
  public function renderFatalError($output = true);


  /**
   * @param string $path
   */
  public function setTemplatesPath($path);
}

