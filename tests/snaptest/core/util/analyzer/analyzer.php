<?php

/**
 * Analyzes files and collects output from sub-processes
 * The File Analyzer tool has callbacks in place for handling multi-process
 * calls such as thread completion and thread failure. When all files have
 * been analyzed, this class acts as an aggregate, returning the list via
 * onComplete
 **/
class Snap_FileAnalyzer {
    protected $results = array();
    
    /**
     * Fired when Snap_Dispatcher completes a thread
     * @param $file the file that was read
     * @param $data the inspection of that file
     **/
    public function onThreadComplete($file, $data) {
        $matches = array();
        preg_match('/'.SNAPTEST_TOKEN_START.'([\s\S]*)'.SNAPTEST_TOKEN_END.'/', $data, $matches);

        $results = (isset($matches[1])) ? unserialize($matches[1]) : FALSE;
        $problem_output = substr($data, 0, strpos($data, SNAPTEST_TOKEN_START));
        
        if (!$results) {
            $data = str_replace(array(SNAPTEST_TOKEN_START, SNAPTEST_TOKEN_END), '', $data);
            return FALSE;
        }
        $this->results[$file] = $results;
    }
    
    /**
     * Fired when Snap_Dispatcher fails handling a thread
     * @param $file the file that was read
     * @param $data the data up to the point of failure
     **/
    public function onThreadFail($file, $data) {
        $this->results[$file] = trim($data);
    }
    
    /**
     * Fired when Snap_Dispatcher declares all threads complete
     * @return array
     **/
    public function onComplete() {
        return $this->results;
    }
    
    /**
     * Analyzes the file provided and gets a list of all valid tests
     * The analyzer includes the class, scans the declared class space
     * for new classes, inspects every included class, and then for each
     * class, inspects the methods to find any that match the test criteria.
     * The resulting array of file => classes => tests is then returned.
     * 
     * @param $file the file to inspect
     * @return array
     **/
    public function analyzeFile($file) {
        if (!file_exists($file)) {
            throw new Snap_File_UnitTestLoader_LoadException('The test file '.$file.' was not found.');
        }
    
        // record the declated classes before include
        $classes = get_declared_classes();
    
        // include, and then scan for new classes
        include_once $file;
        
        $classes = array_diff(get_declared_classes(), $classes);
    
        $output = array();
    
        // loop through the tests and if it does not have a runTest method, continue
        // otherwise, add that class as a valid test
        $methods = array();
        foreach ($classes as $class_name) {
            // skip classes that don't have a runTests method
            if (!method_exists($class_name, 'runTests')) {
                continue;
            }
        
            $methods = get_class_methods($class_name);
        
            // loop through the methods, adding the tests
            foreach ($methods as $method) {
                if (stripos($method, 'test') !== 0) {
                    continue;
                }
            
                if (!isset($output[$class_name])) {
                    $output[$class_name] = array();
                }
            
                $output[$class_name][] = $method;
            }
        }
    
        return $output;
    }
}