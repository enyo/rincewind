<?php

/**
 * Generic Framework Exception
 **/
class Snap_Exception extends Exception {}

/**
 * Exceptions for when a unit test loader does not work properly
 **/
class Snap_File_UnitTestLoader_LoadException extends Exception {}

/**
 * Generic Unit Test Exception
 * Basic test exception that defines something having gone wrong
 * during the unit test exception.
 */
class Snap_UnitTestException extends Snap_Exception {

    protected $user_message;

    /**
     * Constructor for the exception
     * @param string $code the short key code
     * @param string $message the message (long description)
     */
    public function __construct($code, $message = NULL) {
        parent::__construct($code);
        $this->setUserMessage($message);
    }
    
    /**
     * Set the user message
     * @param string $message the message to set
     */
    protected function setUserMessage($message) {
        $this->user_message = $message;
    }
    
    /**
     * Get the user message
     * @return string
     */
    public function getUserMessage() {
        return $this->user_message;
    }

}

/**
 * todo exception
 * thrown when a test method has not been implemented
 **/
class Snap_TodoException extends Snap_UnitTestException {
    public function __construct($message) {
        parent::__construct('todo', $message);
    }
}

/**
 * Skip test exception
 * Thrown when a test method is deliberately skipped
 **/
class Snap_SkipException extends Snap_UnitTestException {
    public function __construct($message) {
        parent::__construct('skipped', $message);
    }
}

/**
 * Call Count Exceptions
 * Used when an expected call count was not met or exceeded
 */
class Snap_AssertCallCountUnitTestException extends Snap_UnitTestException {

    /**
     * Constructor
     * @param string $code the short message
     * @param string $message a longer message
     * @param string $class_name the name of the class and method that failed the expectation
     * @param int $expected_count the number of calls expected
     * @param int $actual_count the number of actual calls made
     */
    public function __construct($code, $message, $class_name, $expected_count, $actual_count) {
    
    
        $reason = '';
        
        switch ($code) {
            case 'assert_call_count':
                $reason = 'exact';
                break;
            case 'assert_min_call_count':
                $reason = 'minimum';
                break;
            case 'assert_max_call_count':
                $reason = 'maximum';
                break;
        }
        
        $reason = 'Expected '.$reason.' count of '.$expected_count.' got '.$actual_count;
    
        if ($message) {
            $message = ' with user message: '.$message;
        }
        
        $output = 'Call Count assertion for '.$class_name.' failed. ['.$reason.']'.$message;
        
        parent::__construct($code, $output);
    
    }
}


/**
 * Instance Of Exception, used for reporting object discrepancies
 */
class Snap_AssertInstanceOfUnitTestException extends Snap_UnitTestException {

    /**
     * Constructor
     * @param string $code a short message
     * @param string $message a long explanatory message
     * @param object $object the object being tested
     * @param strng $classname the expected class name
     */
    public function __construct($code, $message, $object, $classname) {
        if (!is_object($object)) {
            $reason = 'Not an object';
        }
        else {
            $reason = get_class($object) .' is not a '.$classname;
        }
        
        if ($message) {
            $message = ' with user message: '.$message;
        }
        
        $output = 'IsA assertion failed. ['.$reason.']'.$message;
        
        parent::__construct($code, $output);
    }
}


/**
 * When a regular expression assertion fails
 */
class Snap_AssertRegexUnitTestException extends Snap_UnitTestException {

    /**
     * Constructor
     * @param string $code the short code message
     * @param string $message the longer dev-created message
     * @param string $value the value that was tested
     * @param string $regex the regex used
     */
    public function __construct($code, $message, $value, $regex) {
        if ($message) {
            $message = ' with user message: '.$message;
        }
        $output = 'Regular expression assertion failed. ['.$value.' does not match pattern '.$regex.']'.$message;
        
        parent::__construct($code, $output);
    }
}


/**
 * Used for testing when assertions were supposed to be the same
 */
class Snap_AssertIdenticalUnitTestException extends Snap_AssertCompareUnitTestException {

    /**
     * Constructor
     * @param string $code the short code message
     * @param string $message the longer dev-created message
     * @param mixed $outcome the actual outcome
     * @param mixed $should_be the result that it should have been
     */
    public function __construct($code, $message, $outcome, $should_be) {
        parent::__construct($code, $message, $outcome, $should_be, '!==');
    }
}

/**
 * Used for testing when assertions were supposed to be not the same
 */
class Snap_AssertNotIdenticalUnitTestException extends Snap_AssertCompareUnitTestException {

    /**
     * Constructor
     * @param string $code the short code message
     * @param string $message the longer dev-created message
     * @param mixed $outcome the actual outcome
     * @param mixed $should_be the result that it should have been
     */
    public function __construct($code, $message, $outcome, $should_be) {
        parent::__construct($code, $message, $outcome, $should_be, '!==');
    }
}


/**
 * Used for testing when assertions were supposed to be equal
 */
class Snap_AssertEqualUnitTestException extends Snap_AssertCompareUnitTestException {

    /**
     * Constructor
     * @param string $code the short code message
     * @param string $message the longer dev-created message
     * @param mixed $outcome the actual outcome
     * @param mixed $should_be the result that it should have been
     */
    public function __construct($code, $message, $outcome, $should_be) {
        parent::__construct($code, $message, $outcome, $should_be, '!=');
    }
}

/**
 * Used for testing when assertions are supposed to be not equal
 */
class Snap_AssertNotEqualUnitTestException extends Snap_AssertCompareUnitTestException {

    /**
     * Constructor
     * @param string $code the short code message
     * @param string $message the longer dev-created message
     * @param mixed $outcome the actual outcome
     * @param mixed $should_be the result that it should have been
     */
    public function __construct($code, $message, $outcome, $should_be) {
        parent::__construct($code, $message, $outcome, $should_be, '==');
    }
}


/**
 * Used for all basic comparisson assertions
 */
class Snap_AssertCompareUnitTestException extends Snap_UnitTestException {

    /**
     * Constructor
     * @param string $code the short code message
     * @param string $message the longer dev-created message
     * @param mixed $outcome the actual outcome
     * @param mixed $should_be the expected outcome
     * @param string $operator the operator used in the comparisson
     */
    public function __construct($code, $message, $outcome, $should_be, $operator) {
        
        $outcome = $this->captureVariable($outcome);
        $should_be = $this->captureVariable($should_be);
        
        switch (strtolower($code)) {
            case 'assert_true':
                $prefix = 'TRUE assertion got FALSE.';
                break;
            case 'assert_false':
                $prefix = 'FALSE assertion got TRUE.';
                break;
            case 'assert_equal':
                $prefix = 'Equal (==) assertion failed.';
                break;
            case 'assert_not_equal':
                $prefix = 'Not Equal (!=) assertion failed.';
                break;
            case 'assert_identical':
                $prefix = 'Identical (===) assertion failed.';
                break;
            case 'assert_not_identical':
                $prefix = 'Not Identical (!==) assertion failed.';
                break;
            case 'assert_null':
                $prefix = 'NULL assertion failed.';
                break;
            case 'assert_not_null':
                $prefix = 'Not NULL assertion failed.';
                break;
            default:
                $prefix = 'Unkown assertion.';
        }
        
        if ($message) {
            $message = ' with user message: '.$message;
        }
        
        $message = $prefix.' ['.$outcome.' '.$operator.' '.$should_be.']'.$message;
        
        parent::__construct($code, $message);
    }
    
    
    /**
     * Extracts the variable information from the outcome
     * @param mixed $outcome
     * @return string
     */
    protected function captureVariable($outcome) {
        ob_start();
        var_dump($outcome);
        $explain = ob_get_contents();
        ob_end_clean();
        $explain = trim(str_replace(array("\r\n", "\r"), "\n", $explain));
        
        $explain = (strpos($explain, "\n") === FALSE) ? $explain : trim(substr($explain, 0, strpos($explain, "\n"))).'...';
        
        return $explain;
    }

}


