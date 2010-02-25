<?php

	require_once(dirname(dirname(__FILE__)) . '/setup.php');

	require_once(LIBRARY_ROOT_PATH . 'Date/Date.php');



	class Date_BasicFunctionality_Test extends Snap_UnitTestCase {

	    public function setUp() {}

	    public function tearDown() {}


	    public function testConstructingWithoutTime() {
	    	$ts = time();
	    	$date = new Date();
	        return $this->assertTrue(($date->getTimestamp() >= $ts) && ($date->getTimestamp() <= ($ts + 1)), 'This test could have failed because youre computer froze. Try again to make sure it fails.');
	    }


	    public function testConstructingWithTimestamp() {
	    	$ts = 123123123;
	    	$date = new Date($ts);
	        return $this->assertEqual($date->getTimestamp(), $ts);
	    }

	    public function testConstructingWithString() {
	    	$date = new Date('1983-07-04 12:30:00');
	        return $this->assertEqual($date->getTimestamp(), 426166200);
	    }

		public function testGettingFormattedDate() {
			$ts = mktime(12, 30, 0, 7, 4, 1983);
			$date = new Date($ts);
			return $this->assertEqual($date->format('d.m.Y H:i:s'), '04.07.1983 12:30:00');
		}

		public function testSettingDefaultFormat() {
			$ts = mktime(12, 30, 0, 7, 4, 1983);
			$date = new Date($ts);
			$date->setFormat('d.m.Y H:i:s');
			return $this->assertEqual($date->format(), '04.07.1983 12:30:00');
		}

		public function testConvertingDateIntoString() {
			$ts = mktime(12, 30, 0, 7, 4, 1983);
			$date = new Date($ts);
			$date->setFormat('d.m.Y H:i:s');
			return $this->assertEqual((string) $date, '04.07.1983 12:30:00');
		}

	}




?>