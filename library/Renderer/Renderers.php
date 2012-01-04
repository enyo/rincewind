<?php

/**
 * The file for the Renderers class
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Renderer
 */

/**
 * RenderersException
 */
class RenderersException extends Exception {

}

/**
 * The renderers class holds a list of possible renderers and is able to create
 * the renderer necessary to render a view.
 *
 * @author Matthias Loitsch <m@tthias.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Renderer
 */
class Renderers {

  /**
   * @var sfServiceContainer
   */
  protected $container;

  /**
   * @var array
   */
  protected $renderersList;

  /**
   * @param sfServiceContainer $container
   */
  public function __construct(sfServiceContainer $container) {
    $this->container = $container;
  }

  /**
   * Adds a renderer to the pool
   *
   * @param string $className
   * @param string $fileUri
   * @param string $serviceName
   */
  public function registerRenderer($className, $fileUri, $serviceName = null) {
    $this->renderersList[] = array($className, $serviceName ? $serviceName : lcfirst($className));
    require_class($className, $fileUri);
  }

  /**
   * Returns the correct renderer for given attributes.
   *
   * @param string $viewName
   * @param string $templatesPath
   * @param array $requestedContentTypes
   * @return Renderer
   */
  public function getAppropriateRenderer($viewName, $templatesPath, $requestedContentTypes) {
    if (!$requestedContentTypes) $requestedContentTypes = array();
    array_push($requestedContentTypes, '*'); // This is the catch all content type.
    foreach ($requestedContentTypes as $contentType) {
      foreach ($this->renderersList as $rendererInfo) {
        list($rendererClassName, $rendererServiceName) = $rendererInfo;
        if (call_user_func($rendererClassName . '::accepts', $viewName, $templatesPath, $contentType)) {
          Log::debug('Using ' . $rendererClassName, 'Renderer');
          $renderer = $this->container->getService($rendererServiceName);
          $renderer->setTemplatesPath($templatesPath);
          return $renderer;
        }
      }
    }
    return null;
  }

  /**
   * Finds the appropriate renderer, and invokes it.
   *
   * @param string $viewName
   * @param Model $model
   * @param NotificationCenter $notificationCenter
   * @param string $templatesPath
   * @param array $requestedContentTypes
   * @param bool $output
   */
  public function render($viewName, Model $model, NotificationCenter $notificationCenter, $templatesPath, $requestedContentTypes, $output = true) {
    $renderer = $this->getAppropriateRenderer($viewName, $templatesPath, $requestedContentTypes);
    if (!$renderer) {
      Log::error('View could not be rendered because no renderer can handle it.', 'Renderer', array($viewName, $model, $templatesPath, $requestedContentTypes, $output));
      throw new RenderersException('No renderer can handle this request.');
    }
    return $renderer->render($viewName, $model, $notificationCenter, $output);
  }

  /**
   * This render method assumes that the model attribute is set and valid, and
   * there has been an error processing the action, not initializing the data.
   *
   * @param int $errorCode
   * @param Model $model
   * @param NotificationCenter $notificationCenter
   * @param string $templatesPath
   * @param array $requestedContentTypes
   * @param bool $output Whether it should return or output the rendered page.
   * @return string null if output = true
   */
  public function renderError($errorCode, Model $model, NotificationCenter $notificationCenter, $templatesPath, $requestedContentTypes, $output = true) {
    return $this->render('errors/error.' . $errorCode, $model, $notificationCenter, $templatesPath, $requestedContentTypes, $output);
  }

  /**
   * Gets called when there was a problem initializing the data.
   * The template this renders should not access any data in the model.
   *
   * @param NotificationCenter $notificationCenter
   * @param string $templatesPath
   * @param array $requestedContentTypes
   * @param bool $output
   */
  public function renderFatalError(NotificationCenter $notificationCenter, $templatesPath, $requestedContentTypes, $output = true) {
    return $this->render('errors/fatal_error', new Model(), $notificationCenter, $templatesPath, $requestedContentTypes, $output);
  }

}

