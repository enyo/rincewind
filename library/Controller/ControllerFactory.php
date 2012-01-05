<?php

/**
 * This file contains the ControllerFactory definition.
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Controller
 */

/**
 * Thrown when a Controller is not found.
 */
class ControllerFactoryException extends Exception {

}

/**
 * The ControllerFactory is the way to get a Controller for a site.
 * It handles dependency injection and whatnot.
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2009, Matthias Loitsch
 * @package Controller
 */
class ControllerFactory {

  /**
   * @var sfServiceContainer
   */
  protected $container;
  /**
   * Defines where the controllers reside.
   * @var string
   */
  protected $controllerRootPath;
  /**
   * @var array
   */
  protected $availableAutowiredServices = array();

  /**
   * @param sfServiceContainer $container
   * @param array $availableAutowiredServices A list of services the controller factory should autowire (dependency injection).
   * @param string $controllerRootPath if you do not set it in the constructor,
   *                                   make sure you set it via setControllerRootPath
   *                                   before calling get() because this will result
   *                                   in an error otherwise.
   */
  public function __construct($container, $availableAutowiredServices = array(), $controllerRootPath = null) {
    $this->container = $container;
    $this->availableAutowiredServices = $availableAutowiredServices;
    $this->setControllerRootPath($controllerRootPath);
  }

  /**
   * Has to be called before get()
   * @param string $path
   */
  public function setControllerRootPath($path) {
    $this->controllerRootPath = $path;
  }

  /**
   * Returns a specific controller, passing on all the objects the Factory got in the constructor.
   *
   * This method also takes care of dependency injections.
   *
   * @param string $controllerName
   * @param bool $skipInitialization If true, neither authorize(), initialize() nor validate() is called on the Controller.
   * @return Controller
   */
  public function get($controllerName, $skipInitialization = false) {
    if ( ! $this->controllerRootPath) trigger_error('The controller root path was not defined.', E_USER_ERROR);

    $className = $controllerName . 'Controller';

    $classUri = $this->controllerRootPath . $className . '.php';

    if ( ! file_exists($classUri)) throw new ControllerFactoryException('File for controller does not exist.');

    include $classUri;

    if ( ! class_exists($className, false)) throw new ControllerFactoryException('File for controller did exist, but class was not defined.');

    $siteController = $this->getController($className);

    // Dependency injections
    foreach ($this->availableAutowiredServices as $service) {
      if (property_exists($siteController, $service)) {
        $siteController->$service = $this->container->$service;
      }
    }

    return $siteController;
  }


  /**
   * @param string $className
   * @return Controller
   */
  protected function getController($className) {
    return new $className($this->container->notificationCenter, $this->container->router, $this->container->history);
  }

}