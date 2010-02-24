<?php

/**
 * File utility class
 * used for managing temp files, handles their cleanup and paths
 */
class Snap_FileUtil {

    /**
     * Constructor
     */
    public function __construct() {
        $this->files = array();
    }
    
    /**
     * resets the file utility class
     * it beforms a garbage collection, and then
     * resets the internal file array
     */
    public function reset() {
        $this->gc();
        $this->files = array();
    }
    
    /**
     * creates a file using tempnam and holds a reference to it
     * @return string the path to the temp file
     * @throws Snap_UnitTestException
     */
    public function makeFile() {
        $path = tempnam('/tmp', 'snap');
        if ($path === FALSE) {
            throw new Snap_UnitTestException('tmp_not_writable');
        }
        
        $this->files[] = $path;
        return $path;
    }
    
    /**
     * gets the list of all files made with makeFile()
     * @return array A list of paths to files created
     */
    public function getFileList() {
        return $this->files;
    }
    
    /**
     * runs the garbage collection of the class
     * all files in the class are unlinked silently
     */
    public function gc() {
        while ($file = array_pop($this->files)) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }
    }
    
    /**
     * destructor, calls garbage collection
     */
    public function __destruct() {
        $this->gc();
    }

}

