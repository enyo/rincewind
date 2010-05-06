<?php

require_once(dirname(dirname(__FILE__)) . '/setup.php');

require_once(LIBRARY_ROOT_PATH . 'Logger/Logger.php');


class TestLogger extends Logger {
  
  public function doLog($message, $level, $context, $additionalInfo) {
    // Do nothing.
  }
  
  
}


class Logger_Level_Test extends Snap_UnitTestCase {

  protected $logger;

	public function setUp() {
	  $this->logger = new TestLogger();
	}

	public function tearDown() {
	  unset($this->logger);
	}

  public function testSettingFatalLevel() {
    $this->logger->setLevel(Logger::FATAL);
    return $this->assertIdentical($this->logger->getLevel(), Logger::FATAL);
  }

  public function testSettingDebugLevel() {
    $this->logger->setLevel(Logger::DEBUG);
    return $this->assertIdentical($this->logger->getLevel(), Logger::DEBUG);
  }

  // I'm going to assume that the other levels will work too.


  public function testWrongFatalLevel() {
    $this->logger->setLevel(Logger::FATAL);
    return $this->assertFalse($this->logger->error('test'), 'Only FATAL messages should log.');
  }

  public function testFatalLevel() {
    $this->logger->setLevel(Logger::FATAL);
    return $this->assertTrue($this->logger->fatal('test'), 'FATAL messages should log.');
  }

  public function testDebugLevel() {
    $this->logger->setLevel(Logger::DEBUG);
    return $this->assertTrue($this->logger->debug('test'), 'DEBUG messages should log.');
  }

  public function testDebugWithFatalLevel() {
    $this->logger->setLevel(Logger::DEBUG);
    return $this->assertTrue($this->logger->fatal('test'), 'FATAL messages should log.');
  }


}


?>