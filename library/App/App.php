<?php

/*
 * @author Matias Meno <contact@matmeno.com>
 * @copyright Copyright (c) 2011, I-Netcompany
 * @package App
 */

/**
 * App really handles the setup and execution of the app.
 *
 * @author Matias Meno <contact@matmeno.com>
 * @copyright Copyright (c) 2011, I-Netcompany
 * @package App
 */
class App {

  protected $globalConfigFileUri = '{ROOT_PATH}includes/ini.php';
  protected $appConfigFileUri = '{APP_PATH}includes/ini.php';

  /**
   * @var sfServiceContainer
   */
  protected $container;

  /**
   *
   * @param type $globalIniFileUri
   * @param type $appIniFileUri
   * @return App itself for chaining
   */
  public function __construct() {
    $this->insertPathConstants();
  }

  public function setup() {

    $this->setupContainer();

    /**
     * Include the main ini file.
     */
    require $this->globalConfigFileUri;

    /**
     * Starting the session
     * Has to be after ini.boot since it defines where to store the session.
     *
     * I removed it from the ini.php since you don't want a session to be started for static files.
     */
    session_start();

    return $this;
//    if ($this->appConfigFileUri) require $this->appConfigFileUri;
  }

  protected function insertPathConstants() {
    $this->globalConfigFileUri = preg_replace('/\{([^\}]+)\}/ie', 'constant("$1")', $this->globalConfigFileUri);
    $this->appConfigFileUri = preg_replace('/\{([^\}]+)\}/ie', 'constant("$1")', $this->appConfigFileUri);
  }

  protected function setupContainer() {
    include RINCEWIND_PATH . 'SymfonyServiceContainer/sfServiceContainerAutoloader.php';

    sfServiceContainerAutoloader::register();

    $this->container = new sfServiceContainerBuilder();

    /**
     * Setup system config
     */
    require_class('IniFileConfig', 'Config');
    $config = new IniFileConfig(ROOT_PATH . '/includes/local.conf', ROOT_PATH . '/includes/default.conf', true);

    /**
     * Make the system config available as symfony parameters.
     */
    $this->container->setParameters($config->getArray(true));

    /**
     * Add the system config as service.
     */
    $this->container->setService('config', $config);


    $this->container->
        register('controllerFactory', 'ControllerFactory')->
        addArgument($this->container)->
        addArgument(array('config'))->
        addArgument(APP_PATH . 'controllers/')->
        setFile(RINCEWIND_PATH . 'Controller/ControllerFactory.php');

    /**
     * Theme
     */
    $this->container->
        register('theme', 'Theme')->
        addArgument(APP_PATH . 'themes/')->
        addArgument('default')->// TODO: this should actually be configurable.
        setFile(RINCEWIND_PATH . 'Theme/Theme.php');

    /**
     * Sanitizer
     */
    $this->container->
        register('actionFromUrlSanitizer', 'DashesToCamelizedSanitizer')->
        setFile(RINCEWIND_PATH . 'Sanitizer/DashesToCamelizedSanitizer.php');
    $this->container->
        register('actionToUrlSanitizer', 'CamelizedToDashesSanitizer')->
        setFile(RINCEWIND_PATH . 'Sanitizer/CamelizedToDashesSanitizer.php');

    $this->container->
        register('controllerNameFromUrlSanitizer', 'DashesToCamelizedSanitizer')->
        addArgument(true)->
        setFile(RINCEWIND_PATH . 'Sanitizer/DashesToCamelizedSanitizer.php');
    $this->container->setAlias('controllerNameToUrlSanitizer', 'actionToUrlSanitizer');

    /**
     * NotificationCenter
     */
    $this->container->
        register('notificationCenter', 'NotificationCenter')->
        setFile(RINCEWIND_PATH . 'NotificationCenter/NotificationCenter.php');

    /**
     * Router
     */
    $this->container->
        register('router', 'DefaultRouter')->
        setFile(RINCEWIND_PATH . 'Router/DefaultRouter.php');

    /**
     * History
     */
    $this->container->
        register('history', 'History')->
        setFile(RINCEWIND_PATH . 'History/History.php');

    /**
     * Renderers
     */
    $this->container->
        register('twigRenderer', 'TwigRenderer')->
        addArgument(RINCEWIND_PATH . '/Component/Twig/Autoloader.php')->
        addArgument(false)->
        setFile(RINCEWIND_PATH . 'Renderer/TwigRenderer.php');
    $this->container->
        register('jsonRenderer', 'JsonRenderer')->
        setFile(RINCEWIND_PATH . 'Renderer/JsonRenderer.php');
    $this->container->
        register('htmlRenderer', 'HtmlRenderer')->
        setFile(RINCEWIND_PATH . 'Renderer/HtmlRenderer.php');


    $this->container->
        register('renderers', 'Renderers')->
        addArgument($this->container)->
        setFile(RINCEWIND_PATH . 'Renderer/Renderers.php')->
        addMethodCall('registerRenderer', array('JsonRenderer', RINCEWIND_PATH . 'Renderer/JsonRenderer.php'))->
        addMethodCall('registerRenderer', array('TwigRenderer', RINCEWIND_PATH . 'Renderer/TwigRenderer.php'))->
        addMethodCall('registerRenderer', array('HtmlRenderer', RINCEWIND_PATH . 'Renderer/HtmlRenderer.php'));

    /**
     * Dispatcher
     */
    $this->container->
        register('dispatcher', 'DefaultDispatcher')->
        addArgument(new sfServiceReference('controllerFactory'))->
        addArgument(new sfServiceReference('renderers'))->
        addArgument(new sfServiceReference('theme'))->
        addArgument(new sfServiceReference('controllerNameFromUrlSanitizer'))->
        addArgument(new sfServiceReference('actionFromUrlSanitizer'))->
        addArgument(new sfServiceReference('notificationCenter'))->
        setFile(RINCEWIND_PATH . 'Dispatcher/DefaultDispatcher.php');
  }

  public function process() {

    try {

      /**
       * Session specific actions
       */
      if ($this->container->hasService('session')) $this->container->session->handleUserActions();
      $this->container->dispatcher->dispatch();
    }
    catch (Exception $e) {
      Log::fatal('Error during initialization.', 'Core', array('Exception' => get_class($e), 'message' => $e->getMessage()));
      die('<h1 class="error">' . $e->getMessage() . '</h1>');
    }

  }

}

