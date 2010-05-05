<?php

require_once(dirname(dirname(__FILE__)) . '/setup.php');

require_once(LIBRARY_ROOT_PATH . 'File/ImageFile.php');


class ImageFile_Basic_Test extends Snap_UnitTestCase {


	public function setUp() {
	}

	public function tearDown() {
	}

	public function testGettingFile() {
    return $this->assertIsA(ImageFile::create(dirname(__FILE__) . '/test.jpg', File::SOURCE_FILE), 'ImageFile');
	}

}

?>