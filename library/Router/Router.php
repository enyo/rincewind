<?php

/**
 * This file contains the Router interface definition.
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, I-Netcompany
 * @package Router
 */

/**
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2009, Matthias Loitsch
 * @package Router
 */
interface Router {

  /**
   * @param string $controllerName
   * @param string $action
   * @param array $actionParameters
   */
  public function setCurrentRoute($controllerName, $action = null, array $actionParameters = array());

  /**
   * A call to this could look like this:
   *
   *     getUrl('address', 'delete', 2, array('confirmed' => true))
   *
   * This would result in:
   *     /address/delete/2?confirmed=true
   * or
   *     ?controller=address&action=delete/2&confirmed=true
   *
   * If you want to redirect to the current url, use:
   *     getUrl()
   * or if you want to pass get parameters to the current url, use:
   *     getUrl(null, array('confirmed' => true))
   *
   * @param string $targetControllerName if null, the current url is used.
   * @param string,... $action A list of possible action strings.
   * @param array $get
   */
  public function getUrl($targetControllerName = null);

  /**
   * The same as getUrl, but without session ID information, and with
   * http://linktosite/ prepended.
   *
   * @param string $targetControllerName if null, the current url is used.
   * @param string,... $action A list of possible action strings.
   * @param array $get
   * @see getUrl()
   */
  public function getLink($targetControllerName = null);

  /**
   * Uses getUrl to get the url, and redirect there.
   *
   * @param string $targetControllerName if null, the current url is used.
   * @param [string..] $action A list of possible action strings.
   * @param array $get
   * @uses getUrl()
   */
  public function redirect($targetControllerName = null);

  /**
   * The same as redirect, but adds an encrypted error as get variable.
   *
   * @param string $error
   * @param string $targetControllerName if null, the current url is used.
   * @param [string..] $action A list of possible action strings.
   * @param array $get
   */
  public function redirectWithError($error, $targetControllerName = null);

  /**
   * Returns an url error if set.
   * @return string null if none
   */
  public function getUrlError();

  /**
   * Returns an url success if set.
   * @return string null if none
   */
  public function getUrlSuccess();

  /**
   * The same as redirect, but adds an encrypted success as get variable.
   *
   * @param string $success
   * @param string $targetControllerName if null, the current url is used.
   * @param [string..] $action A list of possible action strings.
   * @param array $get
   */
  public function redirectWithSuccess($success, $targetControllerName = null);

  /**
   * Redirects to url, and exits.
   *
   * @param string $url
   */
  public function redirectToUrl($url);
}