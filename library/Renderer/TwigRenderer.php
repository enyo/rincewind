<?php

/**
 * The file for the TwigRenderer class
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
 * The TwigRenderer is the Twig implementation of a Renderer.
 *
 * @author Matthias Loitsch <m@tthias.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Renderer
 */
class TwigRenderer extends BaseRenderer {

  /**
   * The file extension the renderer will handle
   * @var string
   */
  static public $templateFileExtension = 'html.twig';

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
  protected $cachePath;

  /**
   * Includes the dwooAutoload php
   *
   * @param string $twigAutoloaderUri /URI/of/twig/Autoloader.php
   * @param string $cachePath
   * @param string $templatesPath
   */
  public function __construct($twigAutoloaderUri, $cachePath, $templatesPath = null) {
    parent::__construct($templatesPath);

    require_once $twigAutoloaderUri;
    Twig_Autoloader::register();
    $this->cachePath = $cachePath;
  }

  /**
   * {@inheritdoc}
   */
  public function render($viewName, Model $model, MessageDelegate $messageDelegate, $output = true) {

    Profile::start('Renderer', 'Rendering HTML');

    $templateName = $viewName . '.' . static::$templateFileExtension;

    $loader = new Twig_Loader_Filesystem($this->templatesPath);
    $twig = new Twig_Environment($loader, array('cache' => $this->cachePath));


    $modelData = $model->getData();
    $modelData['errorMessages'] = $messageDelegate->getErrorMessages();
    $modelData['successMessages'] = $messageDelegate->getSuccessMessages();

    $this->setHeader('Content-type: text/html', $output);
    $result = $twig->render($templateName, $modelData);

    if ($output) echo $result;

    Profile::stop();

    return $output ? null : $rendered;
  }

}

