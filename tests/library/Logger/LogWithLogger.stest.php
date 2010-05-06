<?php

require_once(dirname(dirname(__FILE__)) . '/setup.php');

require_once(LIBRARY_ROOT_PATH . 'Logger/Log.php');
require_once(LIBRARY_ROOT_PATH . 'Logger/Logger.php');


class LogWithLogger_CallForwarding_Test extends Snap_UnitTestCase {

  protected $logger;

	public function setUp() {
	  $this->logger = $this->mock('Logger')
	    ->listenTo('debug', array(new Snap_Identical_Expectation('The-message'), new Snap_Identical_Expectation(null)))
	    ->listenTo('info', array(new Snap_Identical_Expectation('The-message'), new Snap_Identical_Expectation(null)))
	    ->listenTo('warning', array(new Snap_Identical_Expectation('The-message'), new Snap_Identical_Expectation(null)))
	    ->listenTo('error', array(new Snap_Identical_Expectation('The-message'), new Snap_Identical_Expectation(null)))
	    ->listenTo('fatal', array(new Snap_Identical_Expectation('The-message'), new Snap_Identical_Expectation(null)))

	    ->listenTo('debug', array(new Snap_Identical_Expectation('The-message'), new Snap_Identical_Expectation('test')))
	    ->listenTo('info', array(new Snap_Identical_Expectation('The-message'), new Snap_Identical_Expectation('test')))
	    ->listenTo('warning', array(new Snap_Identical_Expectation('The-message'), new Snap_Identical_Expectation('test')))
	    ->listenTo('error', array(new Snap_Identical_Expectation('The-message'), new Snap_Identical_Expectation('test')))
	    ->listenTo('fatal', array(new Snap_Identical_Expectation('The-message'), new Snap_Identical_Expectation('test')))
	    ->construct();
	}

	public function tearDown() {
	}

  public function testDebug() {
    Log::addLogger($this->logger, 'test');
    Log::debug('The-message', 'test');
    return $this->assertCallCount($this->logger, 'debug', 1, array(new Snap_Identical_Expectation('The-message'), new Snap_Identical_Expectation('test')));
  }

  public function testDebugDefault() {
    Log::addLogger($this->logger);
    Log::debug('The-message');
    return $this->assertCallCount($this->logger, 'debug', 1, array(new Snap_Identical_Expectation('The-message'), new Snap_Identical_Expectation(null)));
  }

  public function testInfo() {
    Log::addLogger($this->logger, 'test');
    Log::info('The-message', 'test');
    return $this->assertCallCount($this->logger, 'info', 1, array(new Snap_Identical_Expectation('The-message'), new Snap_Identical_Expectation('test')));
  }

  public function testInfoDefault() {
    Log::addLogger($this->logger);
    Log::info('The-message');
    return $this->assertCallCount($this->logger, 'info', 1, array(new Snap_Identical_Expectation('The-message'), new Snap_Identical_Expectation(null)));
  }

  public function testWarning() {
    Log::addLogger($this->logger, 'test');
    Log::warning('The-message', 'test');
    return $this->assertCallCount($this->logger, 'warning', 1, array(new Snap_Identical_Expectation('The-message'), new Snap_Identical_Expectation('test')));
  }

  public function testWarningDefault() {
    Log::addLogger($this->logger);
    Log::warning('The-message');
    return $this->assertCallCount($this->logger, 'warning', 1, array(new Snap_Identical_Expectation('The-message'), new Snap_Identical_Expectation(null)));
  }

  public function testError() {
    Log::addLogger($this->logger, 'test');
    Log::error('The-message', 'test');
    return $this->assertCallCount($this->logger, 'error', 1, array(new Snap_Identical_Expectation('The-message'), new Snap_Identical_Expectation('test')));
  }

  public function testErrorDefault() {
    Log::addLogger($this->logger);
    Log::error('The-message');
    return $this->assertCallCount($this->logger, 'error', 1, array(new Snap_Identical_Expectation('The-message'), new Snap_Identical_Expectation(null)));
  }

  public function testFatal() {
    Log::addLogger($this->logger, 'test');
    Log::fatal('The-message', 'test');
    return $this->assertCallCount($this->logger, 'fatal', 1, array(new Snap_Identical_Expectation('The-message'), new Snap_Identical_Expectation('test')));
  }

  public function testFatalDefault() {
    Log::addLogger($this->logger);
    Log::fatal('The-message');
    return $this->assertCallCount($this->logger, 'fatal', 1, array(new Snap_Identical_Expectation('The-message'), new Snap_Identical_Expectation(null)));
  }

}



class LogWithLogger_CallForwardingWithAdditionalInfo_Test extends Snap_UnitTestCase {

  protected $logger;

	public function setUp() {
	  $this->logger = $this->mock('Logger')
	    ->listenTo('debug', array(new Snap_Identical_Expectation('The-message'), new Snap_Identical_Expectation('test'), new Snap_Equals_Expectation(array('a'=>'b'))))
	    ->listenTo('info', array(new Snap_Identical_Expectation('The-message'), new Snap_Identical_Expectation('test'), new Snap_Equals_Expectation(array('a'=>'b'))))
	    ->listenTo('warning', array(new Snap_Identical_Expectation('The-message'), new Snap_Identical_Expectation('test'), new Snap_Equals_Expectation(array('a'=>'b'))))
	    ->listenTo('error', array(new Snap_Identical_Expectation('The-message'), new Snap_Identical_Expectation('test'), new Snap_Equals_Expectation(array('a'=>'b'))))
	    ->listenTo('fatal', array(new Snap_Identical_Expectation('The-message'), new Snap_Identical_Expectation('test'), new Snap_Equals_Expectation(array('a'=>'b'))))
	    ->construct();
    Log::addLogger($this->logger, 'test');
	}

	public function tearDown() {
	}

  public function testDebug() {
    Log::debug('The-message', 'test', array('a'=>'b'));
    return $this->assertCallCount($this->logger, 'debug', 1, array(new Snap_Identical_Expectation('The-message'), new Snap_Identical_Expectation('test'), new Snap_Equals_Expectation(array('a'=>'b'))));
  }

  public function testInfo() {
    Log::info('The-message', 'test', array('a'=>'b'));
    return $this->assertCallCount($this->logger, 'info', 1, array(new Snap_Identical_Expectation('The-message'), new Snap_Identical_Expectation('test'), new Snap_Equals_Expectation(array('a'=>'b'))));
  }

  public function testWarning() {
    Log::warning('The-message', 'test', array('a'=>'b'));
    return $this->assertCallCount($this->logger, 'warning', 1, array(new Snap_Identical_Expectation('The-message'), new Snap_Identical_Expectation('test'), new Snap_Equals_Expectation(array('a'=>'b'))));
  }

  public function testError() {
    Log::error('The-message', 'test', array('a'=>'b'));
    return $this->assertCallCount($this->logger, 'error', 1, array(new Snap_Identical_Expectation('The-message'), new Snap_Identical_Expectation('test'), new Snap_Equals_Expectation(array('a'=>'b'))));
  }

  public function testFatal() {
    Log::fatal('The-message', 'test', array('a'=>'b'));
    return $this->assertCallCount($this->logger, 'fatal', 1, array(new Snap_Identical_Expectation('The-message'), new Snap_Identical_Expectation('test'), new Snap_Equals_Expectation(array('a'=>'b'))));
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