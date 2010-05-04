<?php

require_once(dirname(dirname(__FILE__)) . '/setup.php');

define('FILE_TEST_DIRECTORY', dirname(__FILE__));

require_once(LIBRARY_ROOT_PATH . 'File/FileRetriever.php');

if (!is_writable(FILE_TEST_DIRECTORY)) die(FILE_TEST_DIRECTORY . ' is not writable for File tests.');


class FileRetriever_File_Test extends Snap_UnitTestCase {

	protected $testFile;
	protected $fileRetriever;

	public function setUp() {
	  $this->fileRetriever = new FileRetriever();
		$this->testFile = FILE_TEST_DIRECTORY . '/test.txt';
		file_put_contents($this->testFile, 'test123');
	}

	public function tearDown() {
		@unlink($this->testFile);
	}

	public function testGettingWrongLocalFile() {
	  $this->willThrow('FileRetrieverException');
		$this->fileRetriever->create($this->testFile . 'WRONG', File::SOURCE_FILE);
	}

	public function testGettingLocalFile() {
		return $this->assertIsA($this->fileRetriever->create($this->testFile, File::SOURCE_FILE), 'File', 'Class returned should be a File');
	}

	public function testSourceOfFile() {
		return $this->assertEqual($this->fileRetriever->create($this->testFile, File::SOURCE_FILE)->getSource(), File::SOURCE_FILE, 'Source type should be SOURCE_FILE.');
	}


}


?>