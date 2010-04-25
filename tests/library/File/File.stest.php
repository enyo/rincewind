<?php

require_once(dirname(dirname(__FILE__)) . '/setup.php');

require_once(LIBRARY_ROOT_PATH . 'File/File.php');


class File_Sources_Test extends Snap_UnitTestCase {


	public function setUp() {
	}

	public function tearDown() {
	}

	public function testHTTP() {
		return $this->assertTrue(!!(File::SOURCE_REMOTE & File::SOURCE_HTTP), 'SOURCE_HTTP should be a subset of SOURCE_REMOTE');
	}
	public function testFTP() {
		return $this->assertTrue(!!(File::SOURCE_REMOTE & File::SOURCE_FTP), 'SOURCE_FTP should be a subset of SOURCE_REMOTE');
	}

	public function testFile() {
		return $this->assertTrue(!!(File::SOURCE_LOCAL & File::SOURCE_FILE), 'SOURCE_FILE should be a subset of SOURCE_LOCAL');
	}

	public function testForm() {
		return $this->assertTrue(!!(File::SOURCE_USER & File::SOURCE_FORM), 'SOURCE_FORM should be a subset of SOURCE_USER');
	}

	public function testRemoteAndLocalDiffer() {
		return $this->assertFalse(!!(File::SOURCE_REMOTE & File::SOURCE_LOCAL), 'SOURCE_LOCAL and SOURCE_REMOTE should not be the same set.');
	}

	public function testUserAndLocalDiffer() {
		return $this->assertFalse(!!(File::SOURCE_USER & File::SOURCE_LOCAL), 'SOURCE_LOCAL and SOURCE_USER should not be the same set.');
	}

	public function testUserAndRemoteDiffer() {
		return $this->assertFalse(!!(File::SOURCE_USER & File::SOURCE_REMOTE), 'SOURCE_REMOTE and SOURCE_USER should not be the same set.');
	}

}

?>