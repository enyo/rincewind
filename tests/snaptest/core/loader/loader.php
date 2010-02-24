<?php

/**
 * Unit Test Loader base class
 * contains methods to implement in specific loaders
 */
abstract class Snap_UnitTestLoader {

    protected $tests = array();

    /**
     * Adds a specified test criteria to the test stack
     * @param mixed $item the item to add
     */
    abstract public function add($item);

    /**
     * Adds a test to the stack
     * @param string $test the class to add
     */
    protected final function addTest($test) {
        $this->tests[] = $test;
    }

    /**
     * Get the tests that have been loaded from the add method
     * @return array the array of test objects
     */
    public final function getTests() {
        return $this->tests;
    }

}

