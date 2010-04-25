<?php

require_once(dirname(dirname(__FILE__)) . '/setup.php');

define('FILE_TEST_DIRECTORY', dirname(__FILE__));

require_once(LIBRARY_ROOT_PATH . 'FileFactory/FileFactory.php');

if (!is_writable(FILE_TEST_DIRECTORY)) die(FILE_TEST_DIRECTORY . ' is not writable for File tests.');


class FileFactory_General_Test extends Snap_UnitTestCase {


	public function setUp() {
	}

	public function tearDown() {
	}

	public function testGettingLocalFile() {
		try {
			FileFactory::get('abc', File::SOURCE_LOCAL);
			return $this->assertTrue(false, "This should have thrown an error.");
		}
		catch (Exception $e) {
			return $this->assertIsA($e, 'FileFactoryException', "Should have thrown a FileFactoryException.");
		}
	}

	public function testGettingRemoteFile() {
		try {
			FileFactory::get('abc', File::SOURCE_REMOTE);
			return $this->assertTrue(false, "This should have thrown an error.");
		}
		catch (Exception $e) {
			return $this->assertIsA($e, 'FileFactoryException', "Should have thrown a FileFactoryException.");
		}
	}

	public function testGettingUserFile() {
		try {
			FileFactory::get('abc', File::SOURCE_USER);
			return $this->assertTrue(false, "This should have thrown an error.");
		}
		catch (Exception $e) {
			return $this->assertIsA($e, 'FileFactoryException', "Should have thrown a FileFactoryException.");
		}
	}

}


class FileFactory_WithFile_Test extends Snap_UnitTestCase {

	protected $testFile;

	public function setUp() {
		$this->testFile = FILE_TEST_DIRECTORY . '/test.txt';
		file_put_contents($this->testFile, 'test123');
	}

	public function tearDown() {
		unlink($this->testFile);
	}

	public function testGettingWrongLocalFile() {
		try {
			FileFactory::get($this->testFile . 'WRONG', File::SOURCE_FILE);
		}
		catch (Exception $e) {
			return $this->assertIsA($e, 'FileFactoryException', "Should have thrown a FileFactoryException.");
		}
	}

	public function testGettingLocalFile() {
		return $this->assertIsA(FileFactory::get($this->testFile, File::SOURCE_FILE), 'File', 'Class returned should be a File');
	}

	public function testName() {
		$file = FileFactory::get($this->testFile, File::SOURCE_FILE);
		return $this->assertEqual($file->getName(), 'test.txt', 'The name of the file should be test.txt');
	}

	public function testSize() {
		$file = FileFactory::get($this->testFile, File::SOURCE_FILE);
		return $this->assertTrue($file->getSize() > 0, 'The size should be greater then 0');
	}


}




?>