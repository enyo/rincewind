<?php

/**
 * File Based Unit Test Loader
 * Loads a specified set of tests by including a specified file
 * This is done by looking at the declared classes before and after
 * the include, and then scanning those classes for the runTests method
 */
class Snap_File_UnitTestLoader extends Snap_UnitTestLoader {

    /**
     * add a file and collect the tests for it
     * this adds the file and then looks for all classes with runTest method
     * @param string $files the file to load
     * @throws Snap_File_UnitTestLoader_LoadException
     */
    public function add($file) {

        if (!file_exists($file)) {
            throw new Snap_File_UnitTestLoader_LoadException('The test file '.$file.' was not found.');
        }
        
        // record the declated classes before include
        $classes = get_declared_classes();
        
        // include, and then scan for new classes
        include_once $file;
        $classes = array_diff(get_declared_classes(), $classes);
        
        // loop through the tests and if it does not have a runTest method, continue
        // otherwise, add that class as a valid test
        foreach ($classes as $class_name) {
            if (!method_exists($class_name, 'runTests')) {
                continue;
            }
            
            $this->addTest($class_name);
        }
    }

}

