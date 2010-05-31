<?php


require_once(dirname(dirname(__FILE__)) . '/setup.php');

require_once(LIBRARY_ROOT_PATH . 'Dao/Dao.php');


class DataObject_String_Test extends Snap_UnitTestCase {

  public function setUp() { }
  public function tearDown() { }

  public function testStringNull() {
    return $this->assertIdentical(DataObject::coerce(null, Dao::STRING, $allowNull = true), null);
  }
  public function testStringDefault() {
    return $this->assertIdentical(DataObject::coerce(null, Dao::STRING, $allowNull = false, $quiet = true), '');
  }
  public function testString() {
    return $this->assertIdentical(DataObject::coerce('some string', Dao::STRING, $allowNull = true), 'some string');
  }

  public function testWrongNull() {
    return $this->assertIdentical(DataObject::coerce(array(), Dao::STRING, $allowNull = true), null);
  }
  public function testInteger() {
    return $this->assertIdentical(DataObject::coerce(234, Dao::STRING, $allowNull = false), '234');
  }
  public function testFloat() {
    return $this->assertIdentical(DataObject::coerce(234.123, Dao::STRING, $allowNull = false), '234.123');
  }
  public function testIntegerAndAllowNull() {
    return $this->assertIdentical(DataObject::coerce(234, Dao::STRING, $allowNull = true), '234');
  }
  public function testFloatAndAllowNull() {
    return $this->assertIdentical(DataObject::coerce(234.123, Dao::STRING, $allowNull = true), '234.123');
  }
  public function testWrongNotNull() {
    return $this->assertIdentical(DataObject::coerce(array(), Dao::STRING, $allowNull = false), '');
  }

}


class DataObject_Int_Test extends Snap_UnitTestCase {

  public function setUp() { }
  public function tearDown() { }

  public function testIntNull() {
    return $this->assertIdentical(DataObject::coerce(null, Dao::INT, $allowNull = true), null);
  }
  public function testIntDefault() {
    return $this->assertIdentical(DataObject::coerce(null, Dao::INT, $allowNull = false, $quiet = true), 0);
  }
  public function testInt() {
    return $this->assertIdentical(DataObject::coerce(987654, Dao::INT, $allowNull = true), 987654);
  }
  public function testWrongIntNull() {
    $this->willWarn();
    return $this->assertIdentical(DataObject::coerce("abcd", Dao::INT, $allowNull = true), null);
  }
  public function testWrongIntNotNull() {
    $this->willWarn();
    return $this->assertIdentical(DataObject::coerce("abcd", Dao::INT, $allowNull = false), 0);
  }
}


class DataObject_Enum_Test extends Snap_UnitTestCase {

  public function setUp() { }
  public function tearDown() { }

  public function testEnumNull() {
    return $this->assertIdentical(DataObject::coerce(null, array('enum_a', 'enum_b', 'enum_c'), $allowNull = true), null);
  }
  public function testEnumDefault() {
    return $this->assertIdentical(DataObject::coerce(null, array('enum_a', 'enum_b', 'enum_c'), $allowNull = false, $quiet = true), 'enum_a');
  }
  public function testEnum() {
    return $this->assertIdentical(DataObject::coerce('enum_b', array('enum_a', 'enum_b', 'enum_c'), $allowNull = true), 'enum_b');
  }
  public function testWrongEnumError() {
    $this->willWarn();
    return $this->assertIdentical(DataObject::coerce('invalid enum', array('enum_a', 'enum_b', 'enum_c'), $allowNull = true), null);
  }
  public function testWrongEnumErrorNotNull() {
    $this->willWarn();
    return $this->assertIdentical(DataObject::coerce('invalid enum', array('enum_a', 'enum_b', 'enum_c'), $allowNull = false), 'enum_a');
  }
}


class DataObject_Bool_Test extends Snap_UnitTestCase {

  public function setUp() { }
  public function tearDown() { }

  public function testBoolNull() {
    return $this->assertIdentical(DataObject::coerce(null, Dao::BOOL, $allowNull = true), null);
  }
  public function testBoolDefault() {
    return $this->assertIdentical(DataObject::coerce(null, Dao::BOOL, $allowNull = false, $quiet = true), true);
  }

  public function testBoolTrue() {
    return $this->assertIdentical(DataObject::coerce(true, Dao::BOOL, $allowNull = true), true);
  }
  public function testBoolFalse() {
    return $this->assertIdentical(DataObject::coerce(false, Dao::BOOL, $allowNull = true), false);
  }
  public function testBoolStringTrue() {
    return $this->assertIdentical(DataObject::coerce('true', Dao::BOOL, $allowNull = true), true);
  }
  public function testBoolStringFalse() {
    return $this->assertIdentical(DataObject::coerce('false', Dao::BOOL, $allowNull = true), false);
  }
  public function testBoolIntTrue() {
    return $this->assertIdentical(DataObject::coerce(1, Dao::BOOL, $allowNull = true), true);
  }
  public function testBoolIntFalse() {
    return $this->assertIdentical(DataObject::coerce(0, Dao::BOOL, $allowNull = true), false);
  }
  public function testBoolStringIntTrue() {
    return $this->assertIdentical(DataObject::coerce('1', Dao::BOOL, $allowNull = true), true);
  }
  public function testBoolStringIntFalse() {
    return $this->assertIdentical(DataObject::coerce('0', Dao::BOOL, $allowNull = true), false);
  }


  public function testWrongBoolNull() {
    $this->willWarn();
    return $this->assertIdentical(DataObject::coerce("abcd", Dao::BOOL, $allowNull = true), null);
  }
  public function testWrongBoolNotNull() {
    $this->willWarn();
    return $this->assertIdentical(DataObject::coerce("abcd", Dao::BOOL, $allowNull = false), true);
  }
}



class DataObject_Float_Test extends Snap_UnitTestCase {

  public function setUp() { }
  public function tearDown() { }

  public function testNull() {
    return $this->assertIdentical(DataObject::coerce(null, Dao::FLOAT, $allowNull = true), null);
  }
  public function testDefault() {
    return $this->assertIdentical(DataObject::coerce(null, Dao::FLOAT, $allowNull = false, $quiet = true), 0.0);
  }
  public function testCorrect() {
    return $this->assertIdentical(DataObject::coerce(8484.84, Dao::FLOAT, $allowNull = true), 8484.84);
  }
  public function testWrongNull() {
    $this->willWarn();
    return $this->assertIdentical(DataObject::coerce("abcd", Dao::FLOAT, $allowNull = true), null);
  }
  public function testWrongNotNull() {
    $this->willWarn();
    return $this->assertIdentical(DataObject::coerce("abcd", Dao::FLOAT, $allowNull = false), 0.0);
  }
}


class DataObject_Date_Test extends Snap_UnitTestCase {

  public function setUp() { }
  public function tearDown() { }

  public function testNull() {
    return $this->assertIdentical(DataObject::coerce(null, Dao::DATE, $allowNull = true), null);
  }
  public function testDefault() {
    $time = time();
    $default = DataObject::coerce(null, Dao::DATE, $allowNull = false, $quiet = true);
    return $this->assertTrue(($default > $time - 1) && ($default < $time + 1));
  }
  public function testCorrect() {
    return $this->assertIdentical(DataObject::coerce(123456, Dao::DATE, $allowNull = true), 123456);
  }
  public function testWrongNull() {
    $this->willWarn();
    return $this->assertIdentical(DataObject::coerce("abcd", Dao::DATE, $allowNull = true), null);
  }
  public function testWrongNotNull() {
    $this->willWarn();
    $time = time();
    $default = DataObject::coerce("abcd", Dao::DATE, $allowNull = false);
    return $this->assertTrue(($default > $time - 1) && ($default < $time + 1));
  }
}



class DataObject_Sequence_Test extends Snap_UnitTestCase {

  public function setUp() { }
  public function tearDown() { }

  public function testNull() {
    return $this->assertIdentical(DataObject::coerce(null, Dao::SEQUENCE, $allowNull = true), null);
  }
  public function testDefault() {
    return $this->assertIdentical(DataObject::coerce(null, Dao::SEQUENCE, $allowNull = false, $quiet = true), array());
  }
  public function testCorrect() {
    return $this->assertIdentical(DataObject::coerce(array(1, 2, 5), Dao::SEQUENCE, $allowNull = true), array(1, 2, 5));
  }
  public function testWrongNull() {
    return $this->assertIdentical(DataObject::coerce("abcd", Dao::SEQUENCE, $allowNull = true), null);
  }
  public function testWrongNotNull() {
    return $this->assertIdentical(DataObject::coerce("abcd", Dao::SEQUENCE, $allowNull = false), array());
  }
}


?>