<?php

// turn on all errors.  everything. yes, everything
//error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE | E_CORE_ERROR | E_CORE_WARNING);

/**
 * Snap error handling function.  Takes care of PHP errors, and redirects to the current test
 * @param int $errno the error number
 * @param string $errstr the string of the error
 * @param string $errfile the file the error was in
 * @param int $errline the line triggered the error
 * @return TRUE
 */
function SNAP_error_handler($errno, $errstr, $errfile, $errline) {

    global $SNAP_Current_Reporter_Running;
    global $SNAP_Current_Test_Running;
    
    if ($SNAP_Current_Test_Running->canError()) {
        return TRUE;
    }
    
    $trace = debug_backtrace();

    // call a php error on the snap test object running
    $SNAP_Current_Reporter_Running->recordPHPError($errstr, $errfile, $errline, $trace);
    
    // let it go through to php log
    return TRUE;
}

/**
 * Main tester class, used for putting together input and output handlers
 */
class Snap_Tester {

    protected $output;
    protected $input;
    protected $tests;
    
    /**
     * Constructor, defines an output type
     * on creation, it sets the output handler and arranges
     * the array of tests to run
     * @param string $output the output handler name
     */
    public function __construct($output_type, $test_count = NULL) {
        $this->tests = array();
        $this->setOutput($output_type, $test_count);
    }
    
    /**
     * add tests via an input handler
     * @param string $input_handler the name of the input handler
     * @param array $params the list for the input handler, such as a list of files
     */
    public function addInput($input_handler, $params = array()) {
        if (is_scalar($params)) {
            $params = array($params);
        }
        
        $input_handler = strtolower($input_handler);
        
        if ($input_handler == 'local') {
            $this->addTests($params);
            return;
        }

        $c = $this->getTesterClass($input_handler, 'loader');
        $c = new $c();
        foreach($params as $item) {
            $c->add($item);
        }
        
        $this->addTests($c->getTests());
    }
    
    /**
     * Get the output class
     * @return Snap_UnitTestReporter
     **/
    public function getOutput() {
        return $this->output;
    }
    
    /**
     * run all loaded tests
     * the results of running all tests are then logged into the reporter
     * and the output is generated
     */
    public function runTests($match = NULL) {
    
        $this->tests = array_flip(array_flip($this->tests));
        foreach ($this->tests as $test_name) {
            $test = new $test_name();
            
            if ($match === NULL) {
                $test->runTests($this->getOutput());
            }
            else {
                $test->runTests($this->getOutput(), $match);
            }
        }

        $this->getOutput()->createReport();

        $this->getOutput()->generateFooter();
        
        return TRUE;
    }
    
    /**
     * set the output handler
     * @param string $output_type the name of an output handler
     */
    protected function setOutput($output_type, $test_count) {
        $output = $this->getTesterClass($output_type, 'reporter');
        $this->output = new $output($test_count);
    }
    
    /**
     * adds tests to the test stack
     * @param array $tests an array of tests to add
     */
    protected function addTests($tests) {
        $this->tests = array_merge($this->tests, $tests);
    }
    
    /**
     * resolves a tester to the proper name, serves as a factory method
     * @param string $name the name of the handler to load
     * @param string $type the type of handler, input or output
     * @return string a valid classname
     * @throws Snap_Exception
     */
    protected function getTesterClass($name, $type) {
        if ($type == 'reporter') {
            $suffix = 'UnitTestReporter';
        }
        else {
            $suffix = 'UnitTestLoader';
        }

        $class_name = 'Snap_'.ucwords(strtolower($name)).'_'.$suffix;

        // if class does not exist, include
        if (!class_exists($class_name)) {
            $path = SNAPTEST_CORE . $type . DIRECTORY_SEPARATOR . $type.'s'.DIRECTORY_SEPARATOR.strtolower($name).'.php';
            include $path;
        }

        // if class still does not exist, this is an error
        if (!class_exists($class_name)) {
            throw new Snap_Exception('Handler '.$class_name.' is not found, tried path '.$path);
        }

        return $class_name;
    }

}


// load all addons
// we scan ./addons within the current directory, pulling out all .php files
// and include_once on them. After each include, we scan $config
// and report on what was loaded.  This is then serialized and stored as a
// constant, so that any module can read it.
$handle = opendir(SNAPTEST_ROOT.'addons');
$addon_report = array();
while (FALSE !== ($file = readdir($handle))) {
    
    // skip files starting with .
    if (substr($file, 0, 1) == '.') {
        continue;
    }

    // skip directories
    if (is_dir($file)) {
        continue;
    }
    
    // skip things not ending in .php
    if (substr($file, -4) != '.php') {
        continue;
    }
    
    // valid addon, clear config and load
    $config = array();
    include_once SNAPTEST_ROOT.'addons'.DIRECTORY_SEPARATOR.$file;
    
    $addon_report[] = array(
        'name'          => $config['name'],
        'version'       => $config['version'],
        'author'        => $config['author'],
        'description'   => $config['description'],
    );
    
    // free mem in case of big config
    unset($config);
}
// do our define, then unset to clean up
define('SNAP_ADDONS', serialize($addon_report));
unset($handle);
unset($addon_report);

// ensure after init errors are proper
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
