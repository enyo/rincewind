<?php

require_once dirname(__FILE__) . '/../../setup.php';

require_class('Record');

/**
 * Test class for Record.
 */
class RecordCoerceTest extends PHPUnit_Framework_TestCase {

  /**
   *
   * @fixme Migrate to DAO
   */
  public function testCoerceWithInts() {
    return;
    $this->assertEquals(null, Record::coerce(null, 'someName', null, Dao::STRING, $allowNull = true));
    $this->assertEquals('', Record::coerce(null, 'someName', null, Dao::STRING, $allowNull = false, $quiet = true));
    $this->assertEquals('some string', Record::coerce(null, 'someName', 'some string', Dao::STRING, $allowNull = true));
    $this->assertEquals(null, Record::coerce(null, 'someName', array(), Dao::STRING, $allowNull = true));
    $this->assertEquals('234', Record::coerce(null, 'someName', 234, Dao::STRING, $allowNull = false));
    $this->assertEquals('234.123', Record::coerce(null, 'someName', 234.123, Dao::STRING, $allowNull = false));
    $this->assertEquals('234', Record::coerce(null, 'someName', 234, Dao::STRING, $allowNull = true), '234');
    $this->assertEquals('234.123', Record::coerce(null, 'someName', 234.123, Dao::STRING, $allowNull = true));
    $this->assertEquals('', Record::coerce(null, 'someName', array(), Dao::STRING, $allowNull = false));
  }

}

