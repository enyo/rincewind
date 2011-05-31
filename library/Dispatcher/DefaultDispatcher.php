<?php

/**
 * The DefaultDispatcher
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dispatcher
 */
/**
 * Include the interface
 */
require_interface('Dispatcher');

/**
 * The DefaultDispatcher works with the controllerFactory.
 * 
 * It tries to instantiate the appropriate controller, and to call the right action on it.
 * If it works, the dispatcher is very happy about it.
 *
 * @author Matthias Loitsch <developer@ma.tthias.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Dispatcher
 */
class DefaultDispatcher implements Dispatcher {

  /**
   * SiteControllerFactory
   * 
   * @var type 
   */
  private $controllerFactory;
  /**
   * @var string
   */
  private $defaultControllerName;

  /**
   * @param SiteControllerFactory $controllerFactory 
   * @param string $defaultControllerName
   */
  public function __construct($controllerFactory, $defaultControllerName = 'Home') {
    $this->controllerFactory = $controllerFactory;
    $this->defaultControllerName = $defaultControllerName;
  }

  /**
   * Parses the url, and dispatches to the appropriate controller.
   * @param bool $skipControllerInitialization
   */
  public function dispatch($skipControllerInitialization = false) {
    try {

      $controllerName = isset($_GET['controller']) ? trim($_GET['controller']) : $this->defaultControllerName;

      $controllerName = ucfirst(preg_replace('/\-([a-z])/e', 'strtoupper("$1")', $controllerName));

      try {
        $controller = $this->controllerFactory->get($controllerName);
      }
      catch (ControllerFactoryException $e) {
        $controller = $this->controllerFactory->get($this->defaultControllerName);
      }

      if ( ! $skipControllerInitialization) $controller->initialize();

      $controller->render(true);
    }
    catch (Exception $e) {
      die('<h1 class="error">' . $e->getMessage() . '</h1>');
    }
  }

}
