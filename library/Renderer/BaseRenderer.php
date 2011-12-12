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
   * The file extension the renderer will handle
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
   * Checks if this class can actually handle the view.
   *
   * @param string $viewName
   * @param string $templatesPath
   * @param string $requestedContentType
   */
  static function accepts($viewName, $templatesPath, $requestedContentType = null) {
    if (is_file($templatesPath . $viewName . '.' . static::$templateFileExtension)) {
      // The template file exists.

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

}

