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
 * The base renderer.
 * You should always extend this class unless you really know what you're doing.
 *
 * @author Matthias Loitsch <m@tthias.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Renderer
 */
abstract class BaseRenderer implements Renderer {

  /**
   * The file extension the renderer will handle.
   *
   * Set this to null if your renderer does not actually render templates (eg: JSON
   * or XML renderers).
   *
   * @var string
   */
  static public $templateFileExtension = 'html';

  /**
   * A list of content types this Renderer accepts.
   *
   * The content type * / * matches only if the browser sent it.
   * The content type * will always match last.
   *
   * @var string
   */
  static public $acceptedContentTypes = array('text/html', 'application/xhtml+xml', '*');

  /**
   * @var array
   */
  protected $headers = array();

  /**
   * Checks if this class can actually handle the view.
   *
   * @param string $viewName
   * @param string $templatesPath
   * @param string $requestedContentType
   */
  static function accepts($viewName, $templatesPath, $requestedContentType = null) {
    if (!static::$templateFileExtension || is_file($templatesPath . $viewName . '.' . static::$templateFileExtension)) {
      // The template file exists or this renderer does not use templates.
      if ($requestedContentType === null) return true;

      if (in_array($requestedContentType, static::$acceptedContentTypes)) return true;
    }

    return false;
  }

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

  /**
   * Stores the header so it can be applied later.
   *
   * @param string $header
   * @param bool $applyDirectly
   * @see applyHeaders()
   */
  protected function setHeader($header, $applyDirectly = false) {
    if ($applyDirectly) header($header);
    else $this->headers[] = $header;
  }

  /**
   * Actually applies the headers set by this renderer.
   */
  public function applyHeaders() {
    foreach ($this->headers as $header)
      header($header);
  }

}

