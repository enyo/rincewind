<?php

require_once(dirname(dirname(__FILE__)) . '/setup.php');

require_once(LIBRARY_ROOT_PATH . 'Logger/FileLogger.php');


class FileLogger_Constructor_Test extends Snap_UnitTestCase {

	public function setUp() {
	}

	public function tearDown() {
	}

  public function testWritable() {
    $logger = new FileLogger(dirname(__FILE__) . '/test.log');
    return $this->assertIdentical($logger->getFileUri(), dirname(__FILE__) . '/test.log');
  }

  public function testNotWritableDir() {
    $this->willThrow('FileLoggerException');
    $logger = new FileLogger(dirname(__FILE__) . '/UnwritableDir/test.log');
    return $this->assertTrue(false, 'This should have thrown an exception. Maybe UnwritableDir is writable?');
  }

  public function testNotWritableFile() {
    $this->willThrow('FileLoggerException');
    $logger = new FileLogger(dirname(__FILE__) . '/unwritableFile.log');
    return $this->assertTrue(false, 'This should have thrown an exception. Maybe unwritableFile.log is writable?');
  }

}



class FileLogger_Write_Test extends Snap_UnitTestCase {

  protected $logger;
  protected $fileUri;

	public function setUp() {
		$this->fileUri = tempnam(dirname(__FILE__), 'LoggerWrite');
    $this->logger = new FileLogger($this->fileUri);
    $this->logger->setLevel(Logger::DEBUG);
	}

	public function tearDown() {
    unlink($this->fileUri);
	}

  public function testWrite() {
    $this->logger->error('Test 123');
    $fileContent = file_get_contents($this->fileUri);
    return $this->assertTrue(strpos($fileContent, 'Test 123') !== false, 'The text should have been written to the file.');
  }

  public function testNewlines() {
    $this->logger->error('Test 123');
    $this->logger->error('Test 123');
    $this->logger->error('Test 123');
    return $this->assertIdentical(count(file($this->fileUri)), 3, 'There should be 3 lines in the file.');
  }

}




class FileLogger_Level_Test extends Snap_UnitTestCase {

  protected $logger;
  protected $fileUri;

	public function setUp() {
		$this->fileUri = tempnam(dirname(__FILE__), 'LoggerLevel');
    $this->logger = new FileLogger($this->fileUri);
    $this->logger->setLevel(Logger::DEBUG);
	}

	public function tearDown() {
    unlink($this->fileUri);
	}

  public function testDebug() {
    $this->logger->debug('Test 123');
    $fileContent = file_get_contents($this->fileUri);
    return $this->assertTrue(strpos($fileContent, 'Test 123') !== false, 'The text should have been written to the file.');
  }

  public function testInfo() {
    $this->logger->info('Test 123');
    $fileContent = file_get_contents($this->fileUri);
    return $this->assertTrue(strpos($fileContent, 'Test 123') !== false, 'The text should have been written to the file.');
  }

  public function testWarning() {
    $this->logger->warning('Test 123');
    $fileContent = file_get_contents($this->fileUri);
    return $this->assertTrue(strpos($fileContent, 'Test 123') !== false, 'The text should have been written to the file.');
  }

  public function testError() {
    $this->logger->error('Test 123');
    $fileContent = file_get_contents($this->fileUri);
    return $this->assertTrue(strpos($fileContent, 'Test 123') !== false, 'The text should have been written to the file.');
  }

  public function testFatal() {
    $this->logger->error('Test 123');
    $fileContent = file_get_contents($this->fileUri);
    return $this->assertTrue(strpos($fileContent, 'Test 123') !== false, 'The text should have been written to the file.');
  }

  public function testWarningWithContext() {
    $this->logger->warning('Test 123', 'THE CONTEXT');
    $fileContent = file_get_contents($this->fileUri);
    return $this->assertTrue(strpos($fileContent, 'THE CONTEXT') !== false, 'The context should have been written to the file.');
  }


  public function testWarningWithAdditionalInfo() {
    $this->logger->warning('Test 123', 'THE CONTEXT', array('a'=>'abc123def'));
    $fileContent = file_get_contents($this->fileUri);
    return $this->assertTrue(strpos($fileContent, 'abc123def') !== false, 'The additional info should have been written to the file.');
  }

}


?>