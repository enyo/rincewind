<?php

require_once(dirname(dirname(__FILE__)) . '/setup.php');

define('FILE_TEST_DIRECTORY', dirname(__FILE__));

require_once(LIBRARY_ROOT_PATH . 'File/File.php');

if (!is_writable(FILE_TEST_DIRECTORY)) die(FILE_TEST_DIRECTORY . ' is not writable for File tests.');


class File_General_Test extends Snap_UnitTestCase {


	public function setUp() {
	}

	public function tearDown() {
	}

	public function testGettingLocalFile() {
		try {
			File::create('abc', File::SOURCE_LOCAL);
			return $this->assertTrue(false, "This should have thrown an error.");
		}
		catch (Exception $e) {
			return $this->assertIsA($e, 'FileException', "Should have thrown a FileException.");
		}
	}

	public function testGettingRemoteFile() {
		try {
			File::create('abc', File::SOURCE_REMOTE);
			return $this->assertTrue(false, "This should have thrown an error.");
		}
		catch (Exception $e) {
			return $this->assertIsA($e, 'FileException', "Should have thrown a FileException.");
		}
	}

	public function testGettingUserFile() {
		try {
			File::create('abc', File::SOURCE_USER);
			return $this->assertTrue(false, "This should have thrown an error.");
		}
		catch (Exception $e) {
			return $this->assertIsA($e, 'FileException', "Should have thrown a FileException.");
		}
	}

}


class ResultingFile_File_Test extends Snap_UnitTestCase {

	protected $testFile;

	public function setUp() {
		$this->testFile = FILE_TEST_DIRECTORY . '/test.txt';
		file_put_contents($this->testFile, 'test123');
	}

	public function tearDown() {
		@unlink($this->testFile);
	}

	public function testGettingWrongLocalFile() {
		try {
			File::create($this->testFile . 'WRONG', File::SOURCE_FILE);
		}
		catch (Exception $e) {
			return $this->assertIsA($e, 'FileException', "Should have thrown a FileException.");
		}
	}

	public function testGettingLocalFile() {
		return $this->assertIsA(File::create($this->testFile, File::SOURCE_FILE), 'File', 'Class returned should be a File');
	}

	public function testSourceOfFile() {
		return $this->assertEqual(File::create($this->testFile, File::SOURCE_FILE)->getSource(), File::SOURCE_FILE, 'Source type should be SOURCE_FILE.');
	}

	public function testName() {
		$file = File::create($this->testFile, File::SOURCE_FILE);
		return $this->assertEqual($file->getName(), 'test.txt', 'The name of the file should be test.txt');
	}

	public function testSize() {
		$file = File::create($this->testFile, File::SOURCE_FILE);
		return $this->assertTrue($file->getSize() > 0, 'The size should be greater then 0');
	}


}


class FileFactory_HTTP_Test extends Snap_UnitTestCase {

	public function setUp() {
	}

	public function tearDown() {
	}

	public function testGettingWrongUriFile() {
		try {
			File::create('http://abcde/', File::SOURCE_HTTP);
		}
		catch (FileException $e) {
			return $this->assertEqual($e->getCode(), 400, "The error code is not correct");
		}
	}

	public function testGettingCorrectUriFile() {
		return $this->assertIsA(File::create(CONF_FILE_TEST_URI, File::SOURCE_HTTP), 'File', "Fetching website failed... Maybe you're not connected to the internet?");
	}

	public function testSourceOfFile() {
		return $this->assertEqual(File::create(CONF_FILE_TEST_URI, File::SOURCE_HTTP)->getSource(), File::SOURCE_HTTP, 'Source should be SOURCE_HTTP');
	}

	public function gettingContentOfFile() {
		$content = File::create(CONF_FILE_TEST_URI, File::SOURCE_HTTP)->getContent();
		return $this->assertTrue(!empty($content), 'Content should not be empty');
	}


}




?>