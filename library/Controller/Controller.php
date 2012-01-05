<?php

/**
 * This file contains the Controller definition.
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Controller
 */
/**
 * Including all exceptions
 */
include dirname(__FILE__) . '/ControllerExceptions.php';

/**
 * Including exceptions
 */
require_class('DispatcherException', dirname(__FILE__) . '/DispatcherExceptions.php');

/**
 * Including model
 */
require_class('Model', 'Renderer');

/**
 * The Controller is the base class for every Controller type.
 *
 * When a Controller gets initialized, the constructor calls
 * - authorize()
 * - prepare()
 * in that order.
 *
 * You then get the html output from the Controller, by calling getHtml()
 *
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2009, Matthias Loitsch
 * @package Controller
 */
abstract class Controller {

  /**
   * Contains the action name. Eg.: index
   * @var array
   */
  protected $action;
  /**
   * Containes the action parameters. Eg.: array('em@il.com', 1);
   * @var array
   */
  protected $actionParameters = array();
  /**
   * You can set this, to use another template than the default controller template.
   * @var string If not set, the controller and action names are used.
   */
  private $viewName;
  /**
   * Eg.: Register or login should not be kept in the history.
   * @var bool Defines if the site should stay in the history.
   */
  protected $keepInHistory = true;
  /**
   * @var NotificationCenter
   */
  private $notificationCenter;
  /**
   *
   * @var Model
   */
  protected $model;
  /**
   *
   * @var Router
   */
  protected $router;
  /**
   *
   * @var History
   */
  protected $history;

  /**
   * @param NotificationCenter $notificationCenter
   * @param Router $router
   * @param History $history
   */
  public function __construct(NotificationCenter $notificationCenter, Router $router, History $history) {
    $this->notificationCenter = $notificationCenter;
    $this->history = $history;
    $this->router = $router;
  }

  /**
   * Overwrite this function to return a title (html meta tag <title></title>)
   *
   * @return string
   */
  public function getTitle() {
    return 'Site Title';
  }

  /**
   * Returns the controller name without Controller at the end.
   * @return string
   */
  public function getName() {
    return str_replace('Controller', '', get_class($this));
  }

  /**
   * Returns the action
   * @return string
   */
  public function getAction() {
    return $this->action;
  }

  /**
   *
   * @param string $action
   */
  public function setAction($action) {
    $this->action = $action;
  }

  /**
   * @return array
   */
  public function getActionParameters() {
    return $this->actionParameters;
  }

  /**
   * @param array $actionParameters
   */
  public function setActionParameters(array $actionParameters) {
    $this->actionParameters = $actionParameters;
  }

  /**
   * Returns either $templateName or, if not set, the controller name + the action (eg.: product.show).
   *
   * @return string
   * @uses $templateName
   * @see setTemplateName()
   */
  public function getViewName() {
    if ($this->viewName) {
      $templateName = $this->viewName;
    } else {
      $templateName = $this->convertPhpNameToViewName($this->getName());
      if ($this->getAction() !== 'index') {
        $templateName .= '.' . $this->convertPhpNameToViewName($this->getAction());
      }
    }

    return $templateName;
  }

  /**
   * @param string $viewName
   * @uses $templateName
   * @see getTemplateName()
   */
  public function setViewName($viewName) {
    $this->viewName = $viewName;
  }

  /**
   * @return bool
   */
  public function keepInHistory() {
    return $this->keepInHistory;
  }

  /**
   * Calls authorize and prepare in this order.
   * The ControllerFactory calls initialize when creating a controller, if $skipInitialization == false
   *
   * If you skip it, make sure you call all methods yourself!
   *
   *
   * @see authorize()
   * @see prepare()
   * @see ErrorMessageException
   */
  final public function initialize() {
    $this->router->setCurrentRoute($this->getName(), $this->getAction(), $this->getActionParameters());

    if (($error = $this->router->getUrlError()) !== null) {
      $this->addError($error);
    }

    if (($success = $this->router->getUrlSuccess()) !== null) {
      $this->addSuccess($success);
    }

    try {
      $this->authorize();
      $this->prepare();
    } catch (DatabaseException $e) {
      Log::error($e->getMessage(), 'Controller', array('siteName' => $this->getSiteName()));
      throw new ControllerException('Database error.');
    }
    if ($this->keepInHistory()) {
      $this->history->addUrl($this->getUrl());
    }
  }

  /**
   * This is the first function called in initialize().
   * If the user needs special rights to view a page, this is the place to check for it.
   *
   * @see initialize()
   */
  public function authorize() {

  }

  /**
   * This is the second function called in initialize().
   * If the site needs some preparing (e.g.: Reading some GET vars, or setting main variables), this
   * is the place to do it.
   *
   * You should mainly put stuff in here, that makes sense for the whole controller, for all actions.
   *
   * @see initialize()
   */
  public function prepare() {

  }

  /**
   * @return Router
   */
  public function getRouter() {
    return $this->router;
  }

  /**
   * Wrapper for NotificationCenter::addError()
   *
   * @param string|array $message
   * @see NotificationCenter::addError()
   */
  public function addError($message) {
    $this->notificationCenter->addError($message);
  }

  /**
   * Wrapper for NotificationCenter::addSuccess()
   *
   * @param string|array $message
   * @see NotificationCenter::addSuccess()
   */
  public function addSuccess($message) {
    $this->notificationCenter->addSuccess($message);
  }

  /**
   * Wrapper for Router
   *
   * @param string $targetControllerName if null, the current url is used.
   * @param string,... $action A list of possible action strings.
   * @param array $get
   * @uses Router::getUrl()
   */
  public function getUrl() {
    $params = func_get_args();
    return call_user_func_array(array($this->router, 'getUrl'), func_get_args());
  }

  /**
   * Assign a value to the data object.
   * @param string $name
   * @param mixed $value
   * @param int $clearance
   */
  protected function assign($name, $value, $clearance = Model::UNPUBLISHABLE) {
    $this->model->assign($name, $value, $clearance);
  }


  /**
   * eg: MyAccountController -> my_account
   *
   * @return string
   */
  protected function convertPhpNameToViewName($name) {
    $templateName = lcfirst(str_replace('Controller', '', $name));
    return preg_replace('/([A-Z])/e', 'strtolower("_$1")', $templateName);
  }

  /**
   * @param Model $model
   */
  public function setModel($model) {
    $this->model = $model;
  }

  /**
   * @return Model
   */
  public function getModel() {
    return $this->model;
  }

  /**
   * Initializes the model with basic information.
   *
   * This should not be controller or action specific!
   * If something goes wrong during initModel(), renderFatalError() will be called
   * on the renderer.
   * Fatal errors are not pretty.
   *
   */
  public function initModel() {

  }

  /**
   * This gets called after the current action has been executed.
   *
   * @uses currentData
   */
  public function extendModel() {

  }

}

