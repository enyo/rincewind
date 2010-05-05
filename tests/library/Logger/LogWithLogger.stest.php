<?php

require_once(dirname(dirname(__FILE__)) . '/setup.php');

require_once(LIBRARY_ROOT_PATH . 'Logger/Log.php');
require_once(LIBRARY_ROOT_PATH . 'Logger/Logger.php');


class LogWithLogger_Basic_Test extends Snap_UnitTestCase {

  protected $logger;

	public function setUp() {
	  $this->logger = $this->mock('Logger')
	    ->listenTo('debug')
	    ->construct();
	}

	public function tearDown() {
	}


  public function testDebug() {
    Log::addLogger($this->logger, 'test');
    Log::debug('Message', 'test');
    return $this->assertCallCount($this->logger, 'debug', 1);
  }

  public function testDebugDefault() {
    Log::addLogger($this->logger);
    Log::debug('Message');
    return $this->assertCallCount($this->logger, 'debug', 1);
  }

}


?>