<?php

/**
 * The file for the DwooRenderer class
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Renderer
 */
/**
 * Including the Renderer
 */
require_class('BaseRenderer', 'Renderer');


/**
 * Including Dwoo
 */
include LIBRARY_PATH . '/ThirdParty/dwoo/dwooAutoload.php';

/**
 * The DwooRenderer is the dwoo implementation of a Renderer.
 *
 * @author Matthias Loitsch <m@tthias.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Renderer
 */
class DwooRenderer extends BaseRenderer {

  /**
   * @var string
   */
  protected $functionsPath;

  public function __construct($functionsPath, $templatesPath = null) {
    parent::__construct($templatesPath);
    $this->functionsPath = $functionsPath;
  }

  /**
   * Returns a data object to be filled with everything needed to process a site.
   *
   * @return Dwoo_Data
   */
  public function getModel() {
    return new Dwoo_Data();
  }

  /**
   * Renders the html.
   *
   * @param string $viewName
   * @param Dwoo_Data $model
   * @param bool $output
   * @return string
   * @see getTemplateUri()
   */
  public function render($viewName, $model, $output = true) {

    Profile::start('Theme', 'Generate HTML');

    $templateName = $viewName . '.html';

    $dwoo = new Dwoo();

    $dwoo->getLoader()->addDirectory($this->functionsPath);

    Profile::start('Theme', 'Create template file.');
    $template = new Dwoo_Template_File($viewName . '.html');
    $template->setIncludePath($this->getTemplatesPath());
    Profile::stop();

    Profile::start('Theme', 'Render');
    $rendered = $dwoo->get($template, $model, null, $output);
    Profile::stop();

    Profile::stop();

    return $output ? null : $rendered;
  }

  /**
   * Renders the error site.
   *
   * @param int $errorCode
   * @param mixed $model
   * @param bool $output
   */
  public function renderError($errorCode, $model, $output = true) {
    $viewName = 'errors/error';
    if ($errorCode) {
      $viewName .= '.' . $errorCode;
    }
    return $this->render($viewName, $model, $output);
  }

  /**
   * Renders a fatal error.
   * @param bool $output
   */
  public function renderFatalError($output = true) {
    return $this->render('errors/fatal_error', array(), $output);
  }

}

