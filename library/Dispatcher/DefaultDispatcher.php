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
   * ControllerFactory
   * 
   * @var type 
   */
  private $controllerFactory;
  /**
   * @var string
   */
  private $defaultControllerName;
  /**
   * @var Sanitizer
   */
  private $actionSanitizer;

  /**
   * @param ControllerFactory $controllerFactory 
   * @param string $defaultControllerName
   */
  public function __construct($controllerFactory, Sanitizer $actionSanitizer, $defaultControllerName = 'Home') {
    $this->controllerFactory = $controllerFactory;
    $this->defaultControllerName = $defaultControllerName;
    $this->actionSanitizer = $actionSanitizer;
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

      $errorDuringRender = false;
      $controller->initData();

      try {
        // Try to dispatch to the actual action.
        $actionParameters = explode('/', isset($_GET['action']) ? $_GET['action'] : 'index');

        $action = $actionParameters[0];
        array_shift($actionParameters);

        if ($action{0} === '_') throw new DispatcherException('Tried to access method with underscore.', array('action' => $action));

        $action = $this->actionSanitizer->sanitize($action);
        
        try {
          // Check if the action is valid
          $reflectionClass = new ReflectionClass($controller);

          $actionMethod = $reflectionClass->getMethod($action);

          if ($action !== 'index' && (method_exists('Controller', $action) || ! $actionMethod->isPublic() || ($actionMethod->class !== get_class($controller)))) throw new Exception();
        }
        catch (Exception $e) {
          throw new DispatcherException('Tried to access invalid action.', array('Action' => $action));
        }

        $controller->setAction($action);

        $parameters = array();
        $stringParameters = array();

        $i = 0;
        foreach ($actionMethod->getParameters() as $parameter) {
          $actionParameter = isset($actionParameters[$i]) ? $actionParameters[$i] : null;

          if ($actionParameter === null) {
            if ( ! $parameter->isDefaultValueAvailable()) {
              throw new DispatcherException('Not all parameters supplied.');
            }
            // Well: there is no more additional query, and apparently the rest of the parameters are optional, so continue.
            continue;
          }
          if ($parameterTypeClass = $parameter->getClass()) {
            if ( ! $parameterTypeClass->isSubclassOf('RW_Type')) throw new Exception('Invalid parameter type.');
            $parameterTypeClassName = $parameterTypeClass->getName();
            $parameters[] = new $parameterTypeClassName($actionParameter);
          }
          else {
            $parameters[] = $actionParameter;
          }
          $stringParameters[] = $actionParameter;
          $i ++;
        }
        $controller->setActionParameters($stringParameters);

        if ( ! $skipControllerInitialization) $controller->initialize();

        try {
          // This actually calls the apropriate action.
          call_user_func_array(array($controller, $action), $parameters);
        }
        catch (Exception $e) {
          throw new DispatcherException($e->getMessage());
        }

        $controller->render(true);
      }
      catch (DispatcherException $e) {
        $errorDuringRender = true;
        $additionalInfo = $e->getAdditionalInfo();
        $additionalInfo['controllerName'] = $controllerName;
        Log::warning($e->getMessage(), 'Dispatcher', $additionalInfo);
      }
      catch (Exception $e) {
        $errorDuringRender = true;
        $additionalInfo = array();
        $additionalInfo['controllerName'] = $controllerName;
        $additionalInfo['exceptionThrown'] = get_class($e);
        $additionalInfo['error'] = $e->getMessage();
        Log::warning($e->getMessage(), 'Dispatcher', $additionalInfo);
      }

      if ($errorDuringRender) {
        $controller->renderError();
      }
    }
    catch (Exception $e) {
      die('<h1 class="error">' . $e->getMessage() . '</h1>');
    }
  }

}
