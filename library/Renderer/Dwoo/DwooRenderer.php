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
 * The DwooRenderer is the dwoo implementation of a Renderer.
 *
 * @author Matthias Loitsch <m@tthias.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Renderer
 */
class DwooRenderer extends BaseRenderer {
  /**
   * The file extension the renderer will handle
   * @var string
   */
  static public $templateFileExtension = 'html';

  /**
   * A list of content types this Renderer accepts.
   * If it's the string * then any content type is accepted.
   *
   * @var string
   */
  static public $acceptedContentTypes = array('application/xhtml+xml', 'text/html', '*');

  /**
   * @var string
   */
  protected $functionsPath;

  /**
   * Includes the dwooAutoload php
   *
   * @param string $dwooAutoloadUri /URI/of/dwoo/dwooAutoload.php
   * @param string $functionsPath Dwoo functions
   * @param string $templatesPath
   */
  public function __construct($dwooAutoloadUri, $functionsPath, $templatesPath = null) {
    parent::__construct($templatesPath);
    include $dwooAutoloadUri;
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
   * @param Model $model
   * @param bool $output
   * @return string
   * @see getTemplateUri()
   */
  public function render($viewName, Model $model, $output = true) {

    Profile::start('Theme', 'Generate HTML');

    $templateName = $viewName . '.' . static::$templateFileExtension;

    $dwoo = new Dwoo();

    $dwoo->getLoader()->addDirectory($this->functionsPath);

    Profile::start('Theme', 'Create template file.');
    $template = new Dwoo_Template_File($templateName);
    $template->setIncludePath($this->getTemplatesPath());
    Profile::stop();

    Profile::start('Theme', 'Render');
    $dwooData = new Dwoo_Data();
    $dwooData->setData($model->getData());
    $rendered = $dwoo->get($template, $dwooData, null, $output);
    Profile::stop();

    Profile::stop();

    return $output ? null : $rendered;
  }

}

