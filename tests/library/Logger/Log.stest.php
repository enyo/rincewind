<?php

require_once(dirname(dirname(__FILE__)) . '/setup.php');

require_once(LIBRARY_ROOT_PATH . 'Logger/Log.php');


class Log_Basic_Test extends Snap_UnitTestCase {


	public function setUp() {
	}

	public function tearDown() {
	}


  public function testEmptyContextName() {
    $this->willThrow('LogException');
    Log::addLogger(null, '');
  }

  public function testContextWithSpace() {
    $this->willThrow('LogException');
    Log::addLogger(null, ' abc');
  }

  public function testContextWithSpecialChar() {
    $this->willThrow('LogException');
    Log::addLogger(null, 'abc$');
  }

  public function testCorrectContext() {
    Log::addLogger(null, 'abc-def_123');
    return $this->assertTrue(true);
  }

  public function testGeneralContext() {
    Log::addLogger(null, Log::GENERAL);
    return $this->assertTrue(true);
  }

  public function testDefaultContext() {
    Log::addLogger(null);
    return $this->assertTrue(true);
  }


  public function testGettingLoggerBack() {
    Log::addLogger('BLABLA123 - LOGGER', 'test');
    return $this->assertIdentical('BLABLA123 - LOGGER', Log::getLogger('test'));
  }

  public function testGettingLoggerBackForGeneral() {
    Log::addLogger('123abc - LOGGER');
    return $this->assertIdentical('123abc - LOGGER', Log::getLogger());
  }

  public function testGettingUndefinedLogger() {
    return $this->assertNull(Log::getLogger('abc'));
  }

  public function testSanitizing() {
    Log::addLogger('some - LOGGER', 'aBcD');
    return $this->assertIdentical('some - LOGGER', Log::getLogger('AbCd'));
  }


}


class Log_UndefinedLogger_Test extends Snap_UnitTestCase {


	public function setUp() {
	}

	public function tearDown() {
	}


  public function testDebugDefault() {
    return $this->assertFalse(Log::debug('test'));
  }

  public function testDebug() {
    return $this->assertFalse(Log::debug('test', 'CONTEXT'));
  }

}


?>