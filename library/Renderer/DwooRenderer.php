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
  static public $acceptedContentTypes = array('text/html', '*');

  /**
   * @var string
   */
  protected $functionsPath;
  /**
   * @var string
   */
  protected $compiledPath;
  /**
   * @var string
   */
  protected $cachePath;

  /**
   * Includes the dwooAutoload php
   *
   * @param string $dwooAutoloadUri /URI/of/dwoo/dwooAutoload.php
   * @param string $compiledPath;
   * @param string $cachePath;
   * @param string $functionsPath Dwoo functions
   * @param string $templatesPath
   */
  public function __construct($dwooAutoloadUri, $compiledPath, $cachePath, $functionsPath, $templatesPath = null) {
    parent::__construct($templatesPath);
    include $dwooAutoloadUri;
    $this->functionsPath = $functionsPath;
    $this->compiledPath = $compiledPath;
    $this->cachePath = $cachePath;
  }

  /**
   * {@inheritdoc}
   */
  public function render($viewName, Model $model, NotificationCenter $notificationCenter, $output = true) {

    Profile::start('Renderer', 'Generate HTML');

    $templateName = $viewName . '.' . static::$templateFileExtension;

    $dwoo = new Dwoo($this->compiledPath, $this->cachePath);

    $dwoo->getLoader()->addDirectory($this->functionsPath);

    Profile::start('Renderer', 'Create template file.');
    $template = new Dwoo_Template_File($templateName);
    $template->setIncludePath($this->getTemplatesPath());
    Profile::stop();

    Profile::start('Renderer', 'Render');
    $dwooData = new Dwoo_Data();
    $dwooData->setData($model->getData());

    $dwooData->assign('errorMessages', $notificationCenter->getErrors());
    $dwooData->assign('successMessages', $notificationCenter->getSuccesses());

    $this->setHeader('Content-type: text/html', $output);
    // I do never output directly from dwoo to have the possibility to show an error page if there was a render error.
    $result = $rendered = $dwoo->get($template, $dwooData, null, false);
    if ($output) echo $result;

    Profile::stop();

    Profile::stop();

    return $output ? null : $rendered;
  }

}

