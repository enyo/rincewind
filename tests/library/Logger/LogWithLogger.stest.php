<?php

require_once(dirname(dirname(__FILE__)) . '/setup.php');

require_once(LIBRARY_ROOT_PATH . 'Logger/Log.php');
require_once(LIBRARY_ROOT_PATH . 'Logger/Logger.php');


class LogWithLogger_CallForwarding_Test extends Snap_UnitTestCase {

  protected $logger;

	public function setUp() {
	  $this->logger = $this->mock('Logger')
	    ->listenTo('debug', array(new Snap_Identical_Expectation('The-message')))
	    ->listenTo('info', array(new Snap_Identical_Expectation('The-message')))
	    ->listenTo('warning', array(new Snap_Identical_Expectation('The-message')))
	    ->listenTo('error', array(new Snap_Identical_Expectation('The-message')))
	    ->listenTo('fatal', array(new Snap_Identical_Expectation('The-message')))
	    ->construct();
	}

	public function tearDown() {
	}

  public function testDebug() {
    Log::addLogger($this->logger, 'test');
    Log::debug('The-message', 'test');
    return $this->assertCallCount($this->logger, 'debug', 1, array(new Snap_Identical_Expectation('The-message')));
  }

  public function testDebugDefault() {
    Log::addLogger($this->logger);
    Log::debug('The-message');
    return $this->assertCallCount($this->logger, 'debug', 1, array(new Snap_Identical_Expectation('The-message')));
  }

  public function testInfo() {
    Log::addLogger($this->logger, 'test');
    Log::info('The-message', 'test');
    return $this->assertCallCount($this->logger, 'info', 1, array(new Snap_Identical_Expectation('The-message')));
  }

  public function testInfoDefault() {
    Log::addLogger($this->logger);
    Log::info('The-message');
    return $this->assertCallCount($this->logger, 'info', 1, array(new Snap_Identical_Expectation('The-message')));
  }

  public function testWarning() {
    Log::addLogger($this->logger, 'test');
    Log::warning('The-message', 'test');
    return $this->assertCallCount($this->logger, 'warning', 1, array(new Snap_Identical_Expectation('The-message')));
  }

  public function testWarningDefault() {
    Log::addLogger($this->logger);
    Log::warning('The-message');
    return $this->assertCallCount($this->logger, 'warning', 1, array(new Snap_Identical_Expectation('The-message')));
  }

  public function testError() {
    Log::addLogger($this->logger, 'test');
    Log::error('The-message', 'test');
    return $this->assertCallCount($this->logger, 'error', 1, array(new Snap_Identical_Expectation('The-message')));
  }

  public function testErrorDefault() {
    Log::addLogger($this->logger);
    Log::error('The-message');
    return $this->assertCallCount($this->logger, 'error', 1, array(new Snap_Identical_Expectation('The-message')));
  }

  public function testFatal() {
    Log::addLogger($this->logger, 'test');
    Log::fatal('The-message', 'test');
    return $this->assertCallCount($this->logger, 'fatal', 1, array(new Snap_Identical_Expectation('The-message')));
  }

  public function testFatalDefault() {
    Log::addLogger($this->logger);
    Log::fatal('The-message');
    return $this->assertCallCount($this->logger, 'fatal', 1, array(new Snap_Identical_Expectation('The-message')));
  }

}




class LogWithLogger_LoggerSelection_Test extends Snap_UnitTestCase {

  protected $catchallLogger;
  protected $defaultLogger;
  protected $testLogger;

	public function setUp() {
	  $this->catchallLogger = $this->mock('Logger')
	    ->listenTo('warning', array(new Snap_Identical_Expectation('The-message...')))
	    ->construct();
	  $this->defaultLogger = $this->mock('Logger')
	    ->listenTo('warning', array(new Snap_Identical_Expectation('The-message...')))
	    ->construct();
	  $this->testLogger = $this->mock('Logger')
	    ->listenTo('warning', array(new Snap_Identical_Expectation('The-message...')))
	    ->construct();

    Log::addLogger($this->catchallLogger, Log::CATCHALL);
    Log::addLogger($this->defaultLogger);
    Log::addLogger($this->testLogger, 'test');

	}

	public function tearDown() {
	}

  public function testTest() {
    Log::warning('The-message...', 'test');
    return $this->assertCallCount($this->testLogger, 'warning', 1, array(new Snap_Identical_Expectation('The-message...')));
  }
  public function testTest_defaultZero() {
    Log::warning('The-message...', 'test');
    return $this->assertCallCount($this->defaultLogger, 'warning', 0, array(new Snap_Identical_Expectation('The-message...')));
  }
  public function testTest_catchallZero() {
    Log::warning('The-message...', 'test');
    return $this->assertCallCount($this->catchallLogger, 'warning', 0, array(new Snap_Identical_Expectation('The-message...')));
  }



  public function testDefault() {
    Log::warning('The-message...');
    return $this->assertCallCount($this->defaultLogger, 'warning', 1, array(new Snap_Identical_Expectation('The-message...')));
  }
  public function testDefault_catchallZero() {
    Log::warning('The-message...');
    return $this->assertCallCount($this->catchallLogger, 'warning', 0, array(new Snap_Identical_Expectation('The-message...')));
  }



  public function testCatchall() {
    Log::warning('The-message...', 'NONSENSE');
    return $this->assertCallCount($this->catchallLogger, 'warning', 1, array(new Snap_Identical_Expectation('The-message...')));
  }


}


?>