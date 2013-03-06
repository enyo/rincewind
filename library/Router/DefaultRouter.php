<?php

/**
 * This file contains the Router definition.
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Router
 */
/**
 * Including the Router
 */
require_interface('Router');

/**
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2009, Matthias Loitsch
 * @package Utils
 * @package Router
 */
class DefaultRouter implements Router {

  /**
   * @var string
   */
  protected $currentControllerName;

  /**
   * @var string
   */
  protected $currentAction;

  /**
   * @var string
   */
  protected $currentActionParameters;

  /**
   * @var bool
   */
  protected $useRestfulUrls;

  /**
   * The url root, in case your site is not running on / but in a folder
   * @var string
   * @see setUrlRoot()
   */
  protected $urlRoot = '/';

  /**
   * @param bool $useRestfulUrls
   */
  public function __construct($useRestfulUrls = true) {
    $this->useRestfulUrls = $useRestfulUrls;
  }

  /**
   * {@inheritdoc}
   */
  public function setCurrentRoute($controllerName, $action = null, array $actionParameters = array()) {
    $this->currentControllerName = $controllerName;
    $this->currentAction = $action;
    $this->currentActionParameters = $actionParameters;
  }

  /**
   * @param string $urlRoot
   */
  public function setUrlRoot($urlRoot) {
    $this->urlRoot = $urlRoot;
  }

  /**
   * Returns a name that can easily be read, but is URL valid.
   *
   * @param string $name
   * @return string
   */
  public function urlName($name) {
    setlocale(LC_CTYPE, "en_US.UTF-8");
    $name = iconv('utf-8', 'ascii//TRANSLIT', $name);
    $name = preg_replace('/[^a-zA-Z0-9\-\_\.]+/im', '-', $name);
    return $name;
  }

  /**
   * {@inheritdoc}
   */
  public function getUrl($targetControllerName = null) {
    $arguments = func_get_args();
    list($targetControllerName, $action, $actionParameters, $get) = $this->interpretUrlArguments($targetControllerName, $arguments);
    return $this->generateUrl($targetControllerName, $action, $actionParameters, $get);
  }

  /**
   * {@inheritdoc}
   */
  public function getLink($targetControllerName = null) {
    $arguments = func_get_args();
    list($targetControllerName, $action, $actionParameters, $get) = $this->interpretUrlArguments($targetControllerName, $arguments);
    return 'http://' . @$_SERVER['SERVER_NAME'] . $this->generateUrl($targetControllerName, $action, $actionParameters, $get);
  }

  /**
   * Interprets the arguments and returns the separate parts.
   *
   * @param string $targetControllerName
   * @param array $arguments
   * @return array
   */
  private function interpretUrlArguments($targetControllerName, array $arguments) {
    $action = null;
    $actionParameters = array();
    $get = array();

    $usingActiveUrl = false;
    if ($targetControllerName === null) {
      $usingActiveUrl = true;
      if (!$this->currentControllerName) {
        Log::warning("Trying to get current url but no current controller set.");
        $targetControllerName = "home";
      }
      else {
        $targetControllerName = $this->currentControllerName;
        $action = $this->currentAction;
        $actionParameters = $this->currentActionParameters;
      }
    }

    $argumentCount = count($arguments);
    if ($argumentCount > 1) {
      for ($i = 1; $i < $argumentCount; $i++) {
        $argument = $arguments[$i];
        if (is_array($argument)) {
          $get = $argument;
          if ($i !== $argumentCount - 1) trigger_error('Only the last argument can be an array of GET parameters.', E_USER_ERROR);
        }
        else {
          if ($usingActiveUrl) {
            // Well, if the active URL is chosen, you can't just add additional
            // action information.
            trigger_error('When using active URL, you can\'t pass additional action information.', E_USER_ERROR);
          }
          if ($i === 1) $action = rawurlencode((string) $argument);
          else $actionParameters[] = rawurlencode((string) $argument);
        }
      }
    }
    return array($targetControllerName, $action, $actionParameters, $get);
  }

  /**
   * Used by getUrl and getLink.
   * This method never adds the session ID to the url, because this is unsecure.
   *
   * @param string $targetControllerName
   * @param string $action
   * @param array $actionAndParameters
   * @param array $get
   * @param string $error
   * @param string $success
   */
  protected function generateUrl($targetControllerName, $action, array $actionParameters, array $get, $error = null, $success = null) {
    $targetControllerName = rawurlencode($targetControllerName);

    if (count($actionParameters) === 0 && $action === 'index') $action = null;

    $actionString = null;
    if ($action) {
      $actionString = $actionParameters;
      array_unshift($actionString, $action);
      $actionString = implode('/', array_map('rawurlencode', $actionString));
    }

    if ($error) {
      $error = $this->exportUrlMessage($error);
      $get['error'] = $error;
    }
    if ($success) {
      $success = $this->exportUrlMessage($success);
      $get['success'] = $success;
    }

    if ($this->useRestfulUrls) {
      $url = $this->urlRoot . rawurlencode($targetControllerName);
      if ($actionString) $url .= '/' . $actionString;
      if (count($get)) $url .= '?' . http_build_query($get);
    }
    else {
      $url = $this->urlRoot . '?controller=' . rawurlencode($targetControllerName);
      if ($actionString) $url .= '&action=' . $actionString;
      if (count($get)) $url .= '&' . http_build_query($get);
    }
    return $url;
  }

  /**
   * Prepares the message to be written in an url
   * Overwrite it to do something special like encryption.
   *
   * @param string $message
   * @return string
   */
  protected function exportUrlMessage($message) {
    return $message;
  }
  /**
   * Reads the message in again.
   * Overwrite it to do something special like decryption.
   *
   * @param string $message
   * @return string
   */
  protected function importUrlMessage($message) {
    return $message;
  }

  /**
   * {@inheritdoc}
   */
  public function redirect($targetControllerName = null) {
    $params = func_get_args();
    $url = call_user_func_array(array($this, 'getUrl'), $params);
    $this->redirectToUrl($url);
  }

  /**
   * {@inheritdoc}
   */
  public function redirectWithError($error, $targetControllerName = null) {
    $arguments = func_get_args();
    array_shift($arguments);
    list($targetControllerName, $action, $actionParameters, $get) = $this->interpretUrlArguments($targetControllerName, $arguments);
    $url = $this->generateUrl($targetControllerName, $action, $actionParameters, $get, $error);

    $this->redirectToUrl($url);
  }

  /**
   * {@inheritdoc}
   */
  public function redirectWithSuccess($success, $targetControllerName = null) {
    $arguments = func_get_args();
    array_shift($arguments);
    list($targetControllerName, $action, $actionParameters, $get) = $this->interpretUrlArguments($targetControllerName, $arguments);
    $url = $this->generateUrl($targetControllerName, $action, $actionParameters, $get);

    $this->redirectToUrl($url);
  }

  /**
   * Returns an url error if set.
   * @return string null if none
   */
  public function getUrlError() {
    if (isset($_GET['error'])) {
      try {
        $error = $this->importUrlMessage($_GET['error']);
        if (!empty($error)) return $error;
      }
      catch (Exception $e) {
        // Ignore fake error message.
        Log::info('Incorrect error message.', 'Controller', array('error', $_GET['error']));
      }
    }
    return null;
  }

  /**
   * Returns an url success if set.
   * @return string null if none
   */
  public function getUrlSuccess() {
    if (isset($_GET['success'])) {
      try {
        $success = $this->importUrlMessage($_GET['success']);
        if (!empty($success)) return $success;
      }
      catch (Exception $e) {
        // Ignore fake success message.
        Log::info('Incorrect success message.', 'Controller', $_GET['success']);
      }
    }
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function redirectToUrl($url) {
    header('Location: ' . $url);
    die();
  }

}