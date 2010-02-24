<?php

interface Snap_UnitTestReporterInterface {
    public function __construct($test_count = NULL);
    public function createReport();
    public function recordTestPass($class, $method);
    public function recordTestCaseComplete($class);
    public function recordTestException(Snap_UnitTestException $e);
    public function recordTestTodo(Snap_UnitTestException $e);
    public function recordTestSkip(Snap_UnitTestException $e);
    public function recordUnhandledException(Exception $e);
    public function recordTestDefect(Exception $e);
    public function recordPHPError($errstr, $errfile, $errline, $trace);
    public function generateReport($reports);
    public function generateHeader();
    public function generateFooter();
    public function announceTestCount($test_count);
    public function announceTestPass($report);
    public function announceTestFail($report);
    public function announceTestDefect($report);
    public function announceTestSkip($report);
    public function announceTestTodo($report);
    public function announceTestCaseComplete($report);
}

class Snap_UnitTestReporter {

    /**
     * contains the lines of the report as they occur
     * @var array $reports
     */
    protected $reports;
    
    /**
     * report constructor, initializes all the variables
     */
    public function __construct($test_count = NULL) {
        $this->reports = array();
        $this->generateHeader();
        $this->announceTestCount($test_count);
    }
    
    public final function createReport() {
        $this->generateReport($this->reports);
    }
    
    /**
     * records a test passing and adds it to the queue
     * @param string Class name
     * @param string Method name
     **/
    public final function recordTestPass($class_name, $method_name) {
        $report = array(
            'type'      => 'pass',
            'function'  => $method_name,
            'class'     => $class_name,
        );
        $this->reports[] = $report;
        $this->announceTestPass($report);
    }
    
    public final function recordTestCaseComplete($class_name) {
        $report = array(
            'type'      => 'case',
            'class'     => $class_name,
        );
        $this->reports[] = $report;
        $this->announceTestCaseComplete($report);
    }

    /**
     * records a test exception and adds it to the report queue
     * @param UnitTestException $e
     */
    public final function recordTestException(Snap_UnitTestException $e) {
        $report = $this->record('fail', $e->getUserMessage(), $this->cullTrace($e->getTrace()));
        $this->addReport($report);
        $this->announceTestFail($report);
    }
    
    public final function recordTestTodo(Snap_UnitTestException $e) {
        $report = $this->record('todo', $e->getUserMessage(), $this->cullTrace($e->getTrace()));
        $this->addReport($report);        
        $this->announceTestTodo($report);
    }
    
    public final function recordTestSkip(Snap_UnitTestException $e) {
        $report = $this->record('skip', $e->getUserMessage(), $this->cullTrace($e->getTrace()));
        $this->addReport($report);        
        $this->announceTestSkip($report);
    }
    
    /**
     * records an unhandled exception and adds it to the report queue
     * @param Exception $e
     */
    public final function recordUnhandledException(Exception $e) {
        $report = $this->record('exception', 'Unhandled exception of type '.get_class($e).' with message: '.$e->getMessage(), $this->cullTrace($e->getTrace()));
        $this->addReport($report);
        $this->announceTestFail($report);
    }
    
    /**
     * records a test defect exception that occured in setup/teardown
     * @param UnitTestException $e
     */
    public final function recordTestDefect(Exception $e) {
        if (method_exists($e, 'getUserMessage')) {
            $report = $this->record('defect', $e->getUserMessage(), $this->cullTrace($e->getTrace()));
            $this->addReport($report);
        }
        else {
            $report = $this->record('defect', $e->getMessage(), $this->cullTrace($e->getTrace()));
            $this->addReport($report);
        }
        $this->announceTestDefect($report);
    }
    
    /**
     * records a PHP error encountered
     * @param string $errstr the php error string
     * @param string $errfile the php error file
     * @param int $errline the line of the php error
     * @param array $trace the backtrace of the error
     */
    public final function recordPHPError($errstr, $errfile, $errline, $trace) {

        $trace = $this->cullTrace($trace);
        
        // file trace is worthless
        $trace['file'] = $errfile;
        
        $report = $this->record('phperr', $errstr, $trace, $errline);
        $this->addReport($report);
        $this->announceTestFail($report);
    }
    
    /**
     * cull a trace, removing the unit test cruft and leaving the traced item
     * @param array $trace the trace array
     * @return array an array reduced to the occurance of the test/setup
     */
    protected final function cullTrace($trace) {
        $file = '';
        
        while (TRUE) {
            if (!isset($trace[0])) {
                break;
            }
        
            // drill up until you find a unit test: testXXXXX or setUp or tearDown
            if (isset($trace[0]['function']) && (!preg_match('/^(test.*)|(setUp)|(tearDown)$/i', $trace[0]['function']))) {
                if (isset($trace[0]['file'])) {
                    $file = $trace[0]['file'];
                }
                array_shift($trace);
                continue;
            }
            
            break;
        }
        
        if (!isset($trace[0])) {
            return array();
        }
        
        // restore the proper file
        $trace[0]['file'] = $file;
        
        return $trace[0];
    }
    
    /**
     * add a report to the output stack
     * @param string $output the output to add to the report
     */
    protected final function addReport($output) {
        $this->reports[] = $output;
    }
    
    /**
     * turn a trace and message into it's final output.
     * @param string $message the input message
     * @param array $origin the array origin for the message
     */
    protected function record($type, $message, $backtrace, $line = '') {
        $output = array(
            'type'      => $type,
            'message'   => $message,
            'function'  => (isset($backtrace['function'])) ? $backtrace['function'] : 'unknown',
            'class'     => (isset($backtrace['class'])) ? $backtrace['class'] : 'unknown',
            'file'      => (isset($backtrace['file'])) ? $backtrace['file'] : 'unknown',
            'line'      => $line,
        );
        
        return $output;
    }

}

