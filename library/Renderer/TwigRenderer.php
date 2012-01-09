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
   * @var array
   */
  protected $extensions = array();

  /**
   * @var array
   */
  protected $globals = array();

  /**
   * Includes the dwooAutoload php
   *
   * @param string $twigAutoloaderUri /URI/of/twig/Autoloader.php
   * @param string $cachePath
   * @param string $templatesPath
   */
  public function __construct($twigAutoloaderUri, $cachePath = false, $templatesPath = null) {
    parent::__construct($templatesPath);

    require_once $twigAutoloaderUri;
    Twig_Autoloader::register();
    $this->cachePath = $cachePath;
  }

  /**
   * @param Twig_ExtensionInterface $extension
   */
  public function addExtension($extension) {
    $this->extensions[] = $extension;
  }

  /**
   * @param string $name
   * @param object
   */
  public function addGlobal($name, $global) {
    $this->globals[$name] = $global;
  }

  /**
   * {@inheritdoc}
   */
  public function render($viewName, Model $model, NotificationCenter $notificationCenter, $output = true) {

    Profile::start('Renderer', 'Rendering HTML');

    $templateName = $viewName . '.' . static::$templateFileExtension;

    $loader = new Twig_Loader_Filesystem($this->templatesPath);
    $twig = new Twig_Environment($loader, array('cache' => $this->cachePath));

    foreach ($this->extensions as $extension) {
      $twig->addExtension($extension);
    }
    foreach ($this->globals as $name => $global) {
      $twig->addGlobal($name, $global);
    }

    $twig->addGlobal('notifications', $notificationCenter);
//    $twig->addGlobal('controller', $);

    $this->setHeader('Content-type: text/html', $output);
    $result = $twig->render($templateName, $model->getData());

    if ($output) echo $result;

    Profile::stop();

    return $output ? null : $rendered;
  }

}

