<?php


require_once(dirname(dirname(__FILE__)) . '/setup.php');

require_once(LIBRARY_ROOT_PATH . 'Dao/Dao.php');


class Record_String_Test extends Snap_UnitTestCase {

  public function setUp() { }
  public function tearDown() { }

  public function testStringNull() {
    return $this->assertIdentical(Record::coerce(null, Dao::STRING, $allowNull = true), null);
  }
  public function testStringDefault() {
    return $this->assertIdentical(Record::coerce(null, Dao::STRING, $allowNull = false, $quiet = true), '');
  }
  public function testString() {
    return $this->assertIdentical(Record::coerce('some string', Dao::STRING, $allowNull = true), 'some string');
  }

  public function testWrongNull() {
    return $this->assertIdentical(Record::coerce(array(), Dao::STRING, $allowNull = true), null);
  }
  public function testInteger() {
    return $this->assertIdentical(Record::coerce(234, Dao::STRING, $allowNull = false), '234');
  }
  public function testFloat() {
    return $this->assertIdentical(Record::coerce(234.123, Dao::STRING, $allowNull = false), '234.123');
  }
  public function testIntegerAndAllowNull() {
    return $this->assertIdentical(Record::coerce(234, Dao::STRING, $allowNull = true), '234');
  }
  public function testFloatAndAllowNull() {
    return $this->assertIdentical(Record::coerce(234.123, Dao::STRING, $allowNull = true), '234.123');
  }
  public function testWrongNotNull() {
    return $this->assertIdentical(Record::coerce(array(), Dao::STRING, $allowNull = false), '');
  }

}


class Record_Int_Test extends Snap_UnitTestCase {

  public function setUp() { }
  public function tearDown() { }

  public function testIntNull() {
    return $this->assertIdentical(Record::coerce(null, Dao::INT, $allowNull = true), null);
  }
  public function testIntDefault() {
    return $this->assertIdentical(Record::coerce(null, Dao::INT, $allowNull = false, $quiet = true), 0);
  }
  public function testInt() {
    return $this->assertIdentical(Record::coerce(987654, Dao::INT, $allowNull = true), 987654);
  }
  public function testWrongIntNull() {
    $this->willWarn();
    return $this->assertIdentical(Record::coerce("abcd", Dao::INT, $allowNull = true), null);
  }
  public function testWrongIntNotNull() {
    $this->willWarn();
    return $this->assertIdentical(Record::coerce("abcd", Dao::INT, $allowNull = false), 0);
  }
}


class Record_Enum_Test extends Snap_UnitTestCase {

  public function setUp() { }
  public function tearDown() { }

  public function testEnumNull() {
    return $this->assertIdentical(Record::coerce(null, array('enum_a', 'enum_b', 'enum_c'), $allowNull = true), null);
  }
  public function testEnumDefault() {
    return $this->assertIdentical(Record::coerce(null, array('enum_a', 'enum_b', 'enum_c'), $allowNull = false, $quiet = true), 'enum_a');
  }
  public function testEnum() {
    return $this->assertIdentical(Record::coerce('enum_b', array('enum_a', 'enum_b', 'enum_c'), $allowNull = true), 'enum_b');
  }
  public function testWrongEnumError() {
    $this->willWarn();
    return $this->assertIdentical(Record::coerce('invalid enum', array('enum_a', 'enum_b', 'enum_c'), $allowNull = true), null);
  }
  public function testWrongEnumErrorNotNull() {
    $this->willWarn();
    return $this->assertIdentical(Record::coerce('invalid enum', array('enum_a', 'enum_b', 'enum_c'), $allowNull = false), 'enum_a');
  }
}


class Record_Bool_Test extends Snap_UnitTestCase {

  public function setUp() { }
  public function tearDown() { }

  public function testBoolNull() {
    return $this->assertIdentical(Record::coerce(null, Dao::BOOL, $allowNull = true), null);
  }
  public function testBoolDefault() {
    return $this->assertIdentical(Record::coerce(null, Dao::BOOL, $allowNull = false, $quiet = true), true);
  }

  public function testBoolTrue() {
    return $this->assertIdentical(Record::coerce(true, Dao::BOOL, $allowNull = true), true);
  }
  public function testBoolFalse() {
    return $this->assertIdentical(Record::coerce(false, Dao::BOOL, $allowNull = true), false);
  }
  public function testBoolStringTrue() {
    return $this->assertIdentical(Record::coerce('true', Dao::BOOL, $allowNull = true), true);
  }
  public function testBoolStringFalse() {
    return $this->assertIdentical(Record::coerce('false', Dao::BOOL, $allowNull = true), false);
  }
  public function testBoolIntTrue() {
    return $this->assertIdentical(Record::coerce(1, Dao::BOOL, $allowNull = true), true);
  }
  public function testBoolIntFalse() {
    return $this->assertIdentical(Record::coerce(0, Dao::BOOL, $allowNull = true), false);
  }
  public function testBoolStringIntTrue() {
    return $this->assertIdentical(Record::coerce('1', Dao::BOOL, $allowNull = true), true);
  }
  public function testBoolStringIntFalse() {
    return $this->assertIdentical(Record::coerce('0', Dao::BOOL, $allowNull = true), false);
  }


  public function testWrongBoolNull() {
    $this->willWarn();
    return $this->assertIdentical(Record::coerce("abcd", Dao::BOOL, $allowNull = true), null);
  }
  public function testWrongBoolNotNull() {
    $this->willWarn();
    return $this->assertIdentical(Record::coerce("abcd", Dao::BOOL, $allowNull = false), true);
  }
}



class Record_Float_Test extends Snap_UnitTestCase {

  public function setUp() { }
  public function tearDown() { }

  public function testNull() {
    return $this->assertIdentical(Record::coerce(null, Dao::FLOAT, $allowNull = true), null);
  }
  public function testDefault() {
    return $this->assertIdentical(Record::coerce(null, Dao::FLOAT, $allowNull = false, $quiet = true), 0.0);
  }
  public function testCorrect() {
    return $this->assertIdentical(Record::coerce(8484.84, Dao::FLOAT, $allowNull = true), 8484.84);
  }
  public function testWrongNull() {
    $this->willWarn();
    return $this->assertIdentical(Record::coerce("abcd", Dao::FLOAT, $allowNull = true), null);
  }
  public function testWrongNotNull() {
    $this->willWarn();
    return $this->assertIdentical(Record::coerce("abcd", Dao::FLOAT, $allowNull = false), 0.0);
  }
}


class Record_Date_Test extends Snap_UnitTestCase {

  public function setUp() { }
  public function tearDown() { }

  public function testNull() {
    return $this->assertIdentical(Record::coerce(null, Dao::DATE, $allowNull = true), null);
  }
  public function testDefault() {
    $time = time();
    $default = Record::coerce(null, Dao::DATE, $allowNull = false, $quiet = true);
    return $this->assertTrue(($default > $time - 1) && ($default < $time + 1));
  }
  public function testCorrect() {
    return $this->assertIdentical(Record::coerce(123456, Dao::DATE, $allowNull = true), 123456);
  }
  public function testWrongNull() {
    $this->willWarn();
    return $this->assertIdentical(Record::coerce("abcd", Dao::DATE, $allowNull = true), null);
  }
  public function testWrongNotNull() {
    $this->willWarn();
    $time = time();
    $default = Record::coerce("abcd", Dao::DATE, $allowNull = false);
    return $this->assertTrue(($default > $time - 1) && ($default < $time + 1));
  }
}



class Record_Sequence_Test extends Snap_UnitTestCase {

  public function setUp() { }
  public function tearDown() { }

  public function testNull() {
    return $this->assertIdentical(Record::coerce(null, Dao::SEQUENCE, $allowNull = true), null);
  }
  public function testDefault() {
    return $this->assertIdentical(Record::coerce(null, Dao::SEQUENCE, $allowNull = false, $quiet = true), array());
  }
  public function testCorrect() {
    return $this->assertIdentical(Record::coerce(array(1, 2, 5), Dao::SEQUENCE, $allowNull = true), array(1, 2, 5));
  }
  public function testWrongNull() {
    return $this->assertIdentical(Record::coerce("abcd", Dao::SEQUENCE, $allowNull = true), null);
  }
  public function testWrongNotNull() {
    return $this->assertIdentical(Record::coerce("abcd", Dao::SEQUENCE, $allowNull = false), array());
  }
}


?>
