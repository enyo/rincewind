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
 * The Controller is the base class for every Controller type.
 *
 * When a Controller gets initialized, the constructor calls
 * - authorize()
 * - initialize()
 * - validate()
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
  private $templateName;
  /**
   * Eg.: Register or login should not be kept in the history.
   * @var bool Defines if the site should stay in the history.
   */
  protected $keepInHistory = true;
  /**
   * @var Theme
   */
  protected $theme;
  /**
   * @var MessageUtils
   */
  private $messageDelegate;
  /**
   *
   * @var Data
   */
  protected $currentData;
  /**
   *
   * @var LocationDelegate
   */
  protected $locationDelegate;
  /**
   *
   * @var History
   */
  protected $history;

  /**
   * @param Theme $theme
   * @param MessageDelegate $messageDelegate
   * @param LocationDelegate $locationDelegate
   * @param History $history 
   */
  public function __construct(Theme $theme, MessageDelegate $messageDelegate, LocationDelegate $locationDelegate, History $history) {
    $this->theme = $theme;
    $this->messageDelegate = $messageDelegate;
    $this->history = $history;
    $this->setLocationDelegate($locationDelegate);
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
  public function getTemplateName() {
    if ($this->templateName) {
      $templateName = $this->templateName;
    } else {
      $templateName = $this->convertPhpNameToTemplateName($this->getName());
      if ($this->getAction() !== 'index') {
        $templateName .= '.' . $this->convertPhpNameToTemplateName($this->getAction());
      }
    }

    return $templateName;
  }

  /**
   * @param string $templateName 
   * @uses $templateName
   * @see getTemplateName()
   */
  public function setTemplateName($templateName) {
    $this->templateName = $templateName;
  }

  /**
   * @return bool
   */
  public function keepInHistory() {
    return $this->keepInHistory;
  }

  /**
   * Calls authorize, prepare and validate in this order.
   * The ControllerFactory calls initialize when creating a controller, if $skipInitialization == false
   *
   * If you skip it, make sure you call all methods yourself!
   *
   * If validate() throws a ErrorMessageException, the exception gets caught,
   * and the message added to the message list.
   *
   *
   * @see authorize()
   * @see prepare()
   * @see validate()
   * @see ErrorMessageException
   */
  final public function initialize() {
    if (($error = $this->locationDelegate->getUrlErrorMessage()) !== null) {
      $this->addErrorMessage($error);
    }

    if (($success = $this->locationDelegate->getUrlSuccessMessage()) !== null) {
      $this->addSuccessMessage($success);
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
   * @param LocationDelegate $locationDelegate 
   */
  public function setLocationDelegate(LocationDelegate $locationDelegate) {
    $locationDelegate->setController($this);
    $this->locationDelegate = $locationDelegate;
  }

  /**
   * @return LocationDelegate
   */
  public function getLocationDelegate() {
    return $this->locationDelegate;
  }

  /**
   * Wrapper for MessageUtils::addErrorMessage()
   *
   * @param string|array $message
   * @see MessageUtils::addErrorMessage()
   */
  public function addErrorMessage($message) {
    $this->messageDelegate->addErrorMessage($message);
  }

  /**
   * Wrapper for MessageUtils::addSuccessMessage()
   *
   * @param string|array $message
   * @see MessageUtils::addSuccessMessage()
   */
  public function addSuccessMessage($message) {
    $this->messageDelegate->addSuccessMessage($message);
  }

  /**
   * Wrapper for LocationDelegate
   * 
   * @param string $targetControllerName if null, the current url is used.
   * @param string,... $action A list of possible action strings.
   * @param array $get
   * @uses LocationDelegate::getUrl()
   */
  public function getUrl() {
    $params = func_get_args();
    return call_user_func_array(array($this->locationDelegate, 'getUrl'), func_get_args());
  }

  /**
   * Wrapper for LocationDelegate
   * 
   * @param string $targetControllerName if null, the current url is used.
   * @param string,... $action A list of possible action strings.
   * @param array $get
   * @uses LocationDelegate::getLink()
   */
  public function getLink() {
    $params = func_get_args();
    return call_user_func_array(array($this->locationDelegate, 'getLink'), func_get_args());
  }

  /**
   * Wrapper for LocationDelegate
   * 
   * @param string $targetControllerName if null, the current url is used.
   * @param [string..] $action A list of possible action strings.
   * @param array $get
   * @uses getUrl()
   */
  public function redirect() {
    $params = func_get_args();
    return call_user_func_array(array($this->locationDelegate, 'redirect'), func_get_args());
  }

  /**
   * Wrapper for LocationDelegate
   * 
   * @param string $error
   * @param string $targetControllerName if null, the current url is used.
   * @param [string..] $action A list of possible action strings.
   * @param array $get
   */
  public function redirectWithError() {
    $params = func_get_args();
    return call_user_func_array(array($this->locationDelegate, 'redirectWithError'), func_get_args());
  }

  /**
   * Wrapper for LocationDelegate
   * 
   * @param string $success
   * @param string $targetControllerName if null, the current url is used.
   * @param [string..] $action A list of possible action strings.
   * @param array $get
   */
  public function redirectWithSuccess() {
    $params = func_get_args();
    return call_user_func_array(array($this->locationDelegate, 'redirectWithSuccess'), func_get_args());
  }

  /**
   * Wrapper for LocationDelegate
   *
   * @param string $url
   * @uses $locationDelegate::redirectToUrl()
   */
  public function redirectToUrl($url) {
    $params = func_get_args();
    return call_user_func_array(array($this->locationDelegate, 'redirectToUrl'), func_get_args());
  }

  /**
   * Assign a value to the data object.
   * @param string $name
   * @param mixed $value 
   */
  protected function assign($name, $value) {
    $this->currentData->assign($name, $value);
  }

  /**
   * Gets the data object from the Theme object, then calls initData() and extendData() with it. So when you write
   * a specific Controller implementation, overwrite extendData() to put your own data inside.
   *
   * It then calls the processSite() method on the theme and returns the output.
   *
   * If $initalizationFailed is true, then extendDate is *not* called, and processSite
   * is called with the error template name.
   *
   * @param bool $output If true, directly output the result, do not return it.
   * @see extendData()
   * @see Theme
   * @see Theme::processSite()
   * @todo handle errors in url parsing better!
   */
  public function render($output = true) {
    $this->assign('errorMessages', $this->messageDelegate->getErrorMessages());
    $this->assign('successMessages', $this->messageDelegate->getSuccessMessages());

    $this->theme->processSite($this->getTemplateName(), $this->currentData, $output);
  }

  /**
   * Renders the error site.
   * @param int $errorCode
   * @param bool $output
   */
  public function renderError($errorCode = null, $output = true) {
    $templateName = 'errors/error';
    if ($errorCode) {
      $templateName .= '.' . $errorCode;
    }
    $this->setTemplateName($templateName);
    $this->render($output);
  }

  /**
   * eg: MyAccountController -> my_account
   * 
   * @return string
   */
  protected function convertPhpNameToTemplateName($name) {
    $templateName = lcfirst(str_replace('Controller', '', $name));
    return preg_replace('/([A-Z])/e', 'strtolower("_$1")', $templateName);
  }

  /**
   * Initializes the currentData object.
   * 
   * Here the basic information is put in a Dwoo_Data object.
   * 
   * @uses currentData
   */
  public function initData() {
    $this->currentData = $this->theme->getDataObject();
  }

  /**
   * This gets called after the current action has been executed.
   * 
   * @uses currentData
   */
  public function extendData() {
    
  }

}

