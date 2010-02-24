<?php

class Snap_PassedTestAssertion {}

interface Snap_RunnableTestCaseInterface {
    public function runTests(Snap_UnitTestReporterInterface $reporter);
}

abstract class Snap_UnitTestCase implements Snap_RunnableTestCaseInterface {

    /**
     * constructor that is owned by the parent unit test
     **/
    public final function __construct() {}
    
    /**
     * destructor that is owned by the parent unit test
     **/
    public final function __destruct() {}
    
    abstract public function setUp();
    abstract public function tearDown();
    
    /**
     * a helper function for getting the tally of a method call from a mock object
     * used by the call count assertions in order to properly validate
     * the object tallied is a mock
     * @param Object $object an object containing an element ::mock
     * @param string $method the method to test for
     * @param array $params the parameters to test
     * @throws Snap_UnitTestException when object is not a properly designed mock
     * @return int
     **/
    protected function getTallyFromMock($object, $method, $params) {
        if (!isset($object->mock)
            || !is_object($object->mock)
            || !method_exists($object->mock, 'getTally')) {
        
            throw new Snap_UnitTestException('tally_on_non_mock', 'Tally was called on a non-mock object.');
        }
        
        return $object->mock->getTally($method, $params);
    }
    
    /**
     * Assert that a call count was met
     * @param $mock a mock object to test for
     * @param array a set of parameteres to create a signature with
     * @param string $msg user message on failure
     * @return boolean TRUE
     * @throws Snap_AssertCallCountException
     **/
    protected function assertCallCount($object, $method_name, $expected_count, $method_params = array(), $msg = '') {
        $actual_count = $this->getTallyFromMock($object, $method_name, $method_params);
        
        if ($expected_count != $actual_count) {
            $class_name = get_parent_class($object).'->'.$method_name;
            throw new Snap_AssertCallCountUnitTestException('assert_call_count', $msg, $class_name, $expected_count, $actual_count);
        }
        
        return new Snap_PassedTestAssertion();
    }
    
    /**
     * Assert that a minimum call count was met
     * @param $mock a mock object to test for
     * @param array a set of parameteres to create a signature with
     * @param string $msg user message on failure
     * @return boolean TRUE
     * @throws Snap_AssertCallCountException
     **/
    protected function assertMinimumCallCount($object, $method_name, $expected_count, $method_params = array(), $msg = '') {
        $actual_count = $this->getTallyFromMock($object, $method_name, $method_params);
        
        if ($expected_count < $actual_count) {
            $class_name = get_parent_class($object).'->'.$method_name;
            throw new Snap_AssertCallCountUnitTestException('assert_min_call_count', $msg, $class_name, $expected_count, $actual_count);
        }
        
        return new Snap_PassedTestAssertion();
    }
    
    /**
     * Assert that a maximum call count was met
     * @param $mock a mock object to test for
     * @param array a set of parameteres to create a signature with
     * @param string $msg user message on failure
     * @return boolean TRUE
     * @throws Snap_AssertCallCountException
     **/
    protected function assertMaximumCallCount($object, $method_name, $expected_count, $method_params = array(), $msg = '') {
        $actual_count = $this->getTallyFromMock($object, $method_name, $method_params);
        
        if ($expected_count > $actual_count) {
            $class_name = get_parent_class($object).'->'.$method_name;
            throw new Snap_AssertCallCountUnitTestException('assert_max_call_count', $msg, $class_name, $expected_count, $actual_count);
        }
        
        return new Snap_PassedTestAssertion();
    }

    /**
     * assert that the incoming value is TRUE
     * @param mixed $value
     * @param string $msg user message on failure
     * @return boolean TRUE
     * @throws AssertIdenticalUnitTestException
     **/
    protected function assertTrue($value, $msg = '') {
        if ($value !== TRUE) {
            throw new Snap_AssertIdenticalUnitTestException('assert_true', $msg, $value, TRUE);
        }
        
        return new Snap_PassedTestAssertion();
    }

    /**
     * assert that the incoming value is FALSE
     * @param mixed $value
     * @param string $msg user message on failure
     * @return boolean TRUE
     * @throws AssertIdenticalUnitTestException
     **/    
    protected function assertFalse($value, $msg = '') {
        if ($value !== FALSE) {
            throw new Snap_AssertIdenticalUnitTestException('assert_false', $msg, $value, FALSE);
        }
        
        return new Snap_PassedTestAssertion();
    }
    
    /**
     * assert that the incoming value is equal to the incomming expectation
     * @param mixed $expected the value it should be
     * @param mixed $actual the value actually testing
     * @param string $msg user message on failure
     * @return boolean TRUE
     * @throws AssertEqualUnitTestException
     **/
    protected function assertEqual($actual, $expected, $msg = '') {
        if ($expected != $actual) {
            throw new Snap_AssertEqualUnitTestException('assert_equal', $msg, $expected, $actual);
        }
        
        return new Snap_PassedTestAssertion();
    }
    
    /**
     * assert that the incoming value is not equal to the incomming expectation
     * @param mixed $expected the value it should be
     * @param mixed $actual the value actually testing
     * @param string $msg user message on failure
     * @return boolean TRUE
     * @throws AssertNotEqualUnitTestException
     **/
    protected function assertNotEqual($actual, $expected, $msg = '') {
        if ($expected == $actual) {
            throw new Snap_AssertNotEqualUnitTestException('assert_not_equal', $msg, $expected, $actual);
        }
        
        return new Snap_PassedTestAssertion();
    }
    
    /**
     * Assert that the incomming value is identical (===) to the incoming expectation
     * @param mixed $expected the value it should be
     * @param mixed $actual the value actually testing
     * @param string $msg user message on failure
     * @return boolean TRUE
     * @throws AssertIdenticalUnitTestException
     **/
    protected function assertIdentical($actual, $expected, $msg = '') {
        if ($expected !== $actual) {
            throw new Snap_AssertIdenticalUnitTestException('assert_identical', $msg, $expected, $actual);
        }
        
        return new Snap_PassedTestAssertion();
    }
    
    /**
     * Assert that the incomming value is not identical (!==) to the incoming expectation
     * @param mixed $expected the value it should be
     * @param mixed $actual the value actually testing
     * @param string $msg user message on failure
     * @return boolean TRUE
     * @throws AssertIdenticalUnitTestException
     **/
    protected function assertNotIdentical($actual, $expected, $msg = '') {
        if ($expected === $actual) {
            throw new Snap_AssertNotIdenticalUnitTestException('assert_not_identical', $msg, $expected, $actual);
        }
        
        return new Snap_PassedTestAssertion();
    }
    
    /**
     * assert that the incoming value is NULL
     * @param mixed $value the value to test
     * @param string $msg user message on failure
     * @return boolean TRUE
     * @throws AssertIdenticalUnitTestException
     **/
    protected function assertNull($value, $msg = '') {
        if ($value !== NULL) {
            throw new Snap_AssertIdenticalUnitTestException('assert_null', $msg, $value, NULL);
        }
        
        return new Snap_PassedTestAssertion();
    }

    /**
     * assert that the incoming value is not NULL
     * @param mixed $value the value to test
     * @param string $msg user message on failure
     * @return boolean TRUE
     * @throws AssertNotIdenticalUnitTestException
     **/
    protected function assertNotNull($value, $msg = '') {
        if ($value === NULL) {
            throw new Snap_AssertNotIdenticalUnitTestException('assert_not_null', $msg, $value, NULL);
        }
        
        return new Snap_PassedTestAssertion();
    }
    
    /**
     * assert that the incoming value is a class or subclass of the incoming class
     * @param object $object the object to test
     * @param mixed $classname the class or class name to test
     * @param string $msg the user message on failure
     * @return boolean TRUE
     * @throws AssertInstanceOfUnitTestException
     **/
    protected function assertIsA($object, $classname, $msg = '') {
        if (!($object instanceof $classname)) {
            throw new Snap_AssertInstanceOfUnitTestException('assert_isa', $msg, $object, $classname);
        }
        
        return new Snap_PassedTestAssertion();
    }
    
    /**
     * assert that the incoming value is matched by the regex provided
     * @param string $regex the regulr expression to test
     * @param mixed $value the value to test
     * @param string $msg user message on failure
     * @return boolean TRUE
     * @throws AssertRegexUnitTestException
     **/
    protected function assertRegex($value, $regex, $msg = '') {
        if (!preg_match($regex, $value)) {
            throw new Snap_AssertRegexUnitTestException('assert_matches', $msg, $value, $regex);
        }
        
        return new Snap_PassedTestAssertion();
    }
    
    /**
     * specify that calling this test will cause an exception of the specified class
     * @param string $exception_name the name of the exception expected
     **/
    protected function willThrow($exception_name) {
        $this->willThrow = $exception_name;
    }
    
    /**
     * specify that calling this test will cause a PHP error under normal circumstances
     **/
    protected function willError() {
        $this->willError = TRUE;
    }
    
    /**
     * returns the status if the currently running test is allowed to chuck a PHP error
     **/
    public function canError() {
        return $this->willError;
    }
    
    /**
     * Force an exception stating that the test is not yet complete.
     * until a test is actually believed to be complete, this failure should be called
     * @param string $message the message to provide on skipping
     * @throws Snap_TodoException
     **/
    protected function todo($message = NULL) {
        if ($message === null) {
            throw new Snap_TodoException('Test has not been fully implemented.');
        }
        throw new Snap_TodoException($message);
    }

    /**
     * Force an exception saying the test was explicitly skipped
     * this allows you to skip a test that may run low level PHP functions that cannot
     * be tested, for example, a DB call
     * @param string $message a message to provide on skipping
     * @throws Snap_SkipException
     **/
    protected function skip($message = NULL) {
        if ($message === NULL) {
            throw new Snap_SkipException('Test is skipped.');
        }
        throw new Snap_SkipException($message);
    }
    
    /**
     * factory method for creating a mock object
     * this is useful when you need to create an object and punch out public or
     * protected methods.  It will return a new Mock framework, which can change
     * return values and remove external dependencies
     * @param string $class_name the name of the class to mock
     * @return MockObject the new MockObject instance
     **/
    protected function mock($class_name) {
        return new Snap_MockObject($class_name);
    }
    
    /**
     * run the series of tests
     * takes the reporter, and reflects the current unit test
     * all tests begining with / ^test.* / will execute
     * the execution order is as follows:
     * setup()
     * runTest
     * tearDown()
     * if an error is encountered in setup or teardown, the result is scrapped
     * as a defective test
     * @param Snap_UnitTestReporter $reporter
     * @param string $match if provided, the prefix used will be matched instead of "test*"
     **/
    public function runTests(Snap_UnitTestReporterInterface $reporter, $match = '^test') {
    
        // reflect the class
        $reflected_class = new ReflectionClass($this);
        
        // get the public methods
        $public_methods = array();
        foreach ($reflected_class->getMethods() as $method) {
            if ($method->isConstructor()) {
                // special constructor stuff here
                continue;
            }
            if ($method->isPublic()) {
                $public_methods[] = $method->getName();
            }
        }
        
        // set the current test for error handling
        global $SNAP_Current_Test_Running;
        global $SNAP_Current_Reporter_Running;
        $SNAP_Current_Test_Running = $this;
        $SNAP_Current_Reporter_Running = $reporter;
        
        $old_error_handler = set_error_handler('SNAP_error_handler');
        
        foreach ($public_methods as $method) {
                
            if (!preg_match('/'.$match.'/i', $method)) {
                continue;
            }
            
            $this->willThrow = NULL;
            $this->willError = FALSE;
            $result = FALSE;
            
            // run setup
            try {
                $this->setUp();
            }
            catch (Exception $e) {
                // skip exceptions, even in setup are allowed
                if ($e instanceof Snap_SkipException) {
                    $reporter->recordTestSkip($e);
                }
                else {
                    $reporter->recordTestDefect($e);
                }
                continue;
            }
            
            // run method
            try {
                $result = $this->$method();
            }
            catch (Snap_UnitTestException $e) {
                if ($e instanceof Snap_TodoException) {
                    $reporter->recordTestTodo($e);
                }
                elseif ($e instanceof Snap_SkipException) {
                    $reporter->recordTestSkip($e);
                }
                else {
                    $reporter->recordTestException($e);
                }
                try {
                    $this->tearDown();
                }
                catch (Exception $e) {
                    $reporter->recordTestDefect($e);
                }
                
                continue;
            }
            catch (Exception $e) {
                if ((!isset($this->willThrow))
                    || (strlen($this->willThrow) <= 0)
                    || (!class_exists($this->willThrow))
                    || (!($e instanceof $this->willThrow))) {
                    
                    $reporter->recordUnhandledException($e);
                    
                    try {
                        $this->tearDown();
                    }
                    catch (Exception $e) {
                        $reporter->recordTestDefect($e);
                    }
                    
                    continue;

                }
                
                // valid exception, we count a success
                $result = new Snap_PassedTestAssertion();
            }
            
            // test we got a proper assertion
            if (!is_object($result) || (!($result instanceof Snap_PassedTestAssertion))) {
                try {
                    throw new Snap_UnitTestException('invalid_return_value', get_class($this) . '::' . $method . ' does not return an assertion.');
                }
                catch (Snap_UnitTestException $e) {
                    $reporter->recordTestException($e);
                    try {
                        $this->tearDown();
                    }
                    catch (Exception $e) {
                        $reporter->recordTestDefect($e);
                    }
                    
                    continue;
                }
            }
            
            // tear down
            try {
                $this->tearDown();
            }
            catch (Snap_UnitTestException $e) {
                $reporter->recordTestException($e);
                continue;
            }
            catch (Exception $e) {
                $reporter->recordTestDefect($e);
                continue;
            }
            
            $reporter->recordTestPass(get_class($this), $method);
            
        } // end foreach test
        
        // restore error handler
        restore_error_handler();
        // set_error_handler($old_error_handler);
        
        $reporter->recordTestCaseComplete(get_class($this));
    }

}

