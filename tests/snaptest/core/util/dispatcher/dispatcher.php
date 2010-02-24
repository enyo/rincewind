<?php

/**
 * Handles multiple processes for both speed and isolation in testing
 * The Snap Dispatch class provides the structure to run each PHP call in
 * its own process. The results of this process are then scraped off of the
 * process' STDIN and sent back to the appropriate callback on complete.
 * When all threads have completed, a global onComplete callback is
 * called.
 **/
class Snap_Dispatcher {
    
    /**
     * Constructor, defines PHP's location and the SnapTest path
     * @param $php the path to php
     * @param $snaptest_path the path to snaptest.php
     **/
    public function __construct($php, $snaptest_path) {
        $this->php = $php;
        $this->snaptest_path = $snaptest_path;
    }
    
    /**
     * Returns the SnapTest.php path
     * @return string
     **/
    protected function getSnaptestPath() {
        return $this->snaptest_path;
    }
    
    /**
     * Returns the path to PHP
     * Under some OSes, additional prefixes may be needed for running
     * PHP properly in the background. If $use_prefix is TRUE, then
     * those prefixes will be prepended to the PHP path.
     * @param $use_prefix should any OS prefixes be used, including calls to "nice"
     * @return string
     **/
    protected function getPHP($use_prefix = TRUE) {
        if (!$use_prefix) {
            return $this->php;
        }

        $php = $this->php;
        
        // cgi mode needs "quiet" flag
        if (SNAP_CGI_MODE) {
            $php .= ' -q';
        }

        return $php;
    }
    
    /**
     * Returns the Maximum number of Children to run at once
     * @return int
     **/
    protected function getMaxChildren() {
        return SNAP_MAX_CHILDREN;
    }
    
    /**
     * Adapter function for making long option strings
     * @param $options an array of options
     * @return string
     **/
    protected function makeLongOptions($options) {
        return Snap_Request::makeLongOptions($options);
    }
    
    /**
     * Opens a resource handle as a background process
     * This opens the PHP process, and returns the execution handle
     * back to the calling function. This is the guts of the fork()
     * @param $call an array of options
     * @return resource of type handle
     **/
    protected function createHandle($call) {
        // add our php path to the $call
        $call['php'] = $this->getPHP(FALSE);
    
        $options = $this->makeLongOptions($call);
    
        $exec = $this->getPHP() . ' ' . $this->getSnaptestPath() . ' ' . $options;
        
        $descriptors = array(
            0   => array('pipe', 'r'),
            1   => array('pipe', 'w'),
            2   => array('pipe', 'w'),
        );
        
        $process = proc_open($exec, $descriptors, $pipes);
        stream_set_blocking($pipes[1], 0);
        
        if (is_resource($process)) {
            fclose($pipes[0]);
            return array(
                'proc'  => $process,
                'read'  => $pipes[1],
                'err'   => $pipes[2],
            );
        }
        
        return FALSE;
    }
    
    /**
     * Checks to see if a stream is available for non-blocking read operations
     * if a stream is found, the key for the array stream is returned.
     * in order to find the stream easier in the calling method
     * @param $array an array of sockets to check
     * @param $timeout [default: 0] a timeout for stream_select
     **/
    protected function selectStream($array, $timeout = 0) {
        $selects = $array;
        
        if (stream_select($array, $a = NULL, $b = NULL, $timeout)) {
            $streams = array();
            foreach ($selects as $key => $stream) {
                if (in_array($stream, $array)) {
                    $streams[$key] = $stream;
                }
            }
            return $streams;
        }
        
        return FALSE;
    }

    /**
     * Dispatch a call for the specified options
     * In order for dispatch to work, a collection of options need
     * to be specified. These options build out the dispatch call, which is
     * then farmed out to the max children allowed in constants.php
     * @param $options the options for the dispatch call
     * @return mixed
     **/
    public function dispatch($options) {
        $key_list = $options['keys'];
        $procs = array();
        $reads = array();
        $threads_processing = FALSE;
        for ($i = 0; $i < $this->getMaxChildren(); $i++) {
            $procs[$i] = NULL;
        }
        
        while (count($key_list) || $threads_processing) {
            // loop through the procs, assign open slots
            for ($i = 0; $i < $this->getMaxChildren(); $i++) {
                if ($procs[$i] === NULL) {
                    // needs a process
                    // skip if no keys left to grab
                    if (count($key_list) <= 0) {
                        continue;
                    }
                    
                    $key = array_pop($key_list);
            
                    $dispatch = array();
                    foreach ($options['dispatch'] as $k => $v) {
                        if ($k == '$key') {
                            $k = $key;
                        }
                        if ($v == '$key') {
                            $v = $key;
                        }
                        $dispatch[$k] = $v;
                    }
                    
                    $handle = $this->createHandle($dispatch);
                    
                    if ($handle === FALSE) {
                        call_user_func_array($options['onThreadFail'], array($key, ''));
                        
                        $threads_processing = FALSE;
                        for ($i = 0; $i < $this->getMaxChildren(); $i++) {
                            if ($procs[$i] !== NULL) {
                                $threads_processing = TRUE;
                                break;
                            }
                        }
                        
                        continue;
                    }
                    
                    $handle['key'] = $key;
                    $procs[$i] = $handle;
                    $reads[$i] = $handle['read'];
                    $threads_processing = TRUE;
                }
            }
            
            // collect all streams ready for read
            $ready_streams = $this->selectStream($reads, 60);
            if ($ready_streams !== FALSE) {
                foreach ($ready_streams as $key => $stream) {
                    $handle = $procs[$key];
                    call_user_func_array($options['onThreadComplete'], array($handle['key'], stream_get_contents($stream)));
                    
                    fclose($handle['read']);
                    fclose($handle['err']);
                    proc_close($handle['proc']);
                
                    unset($reads[$key]);
                    unset($procs[$key]);
                    unset($ready_streams[$key]);
                    $procs[$key] = NULL;
                }
                
                // sweep to see if there are any threads processing
                $threads_processing = FALSE;
                for ($i = 0; $i < $this->getMaxChildren(); $i++) {
                    if ($procs[$i] !== NULL) {
                        $threads_processing = TRUE;
                        break;
                    }
                }
            }
            unset($ready_streams);
        } // end while there are keys left to process || threads processing
        
        // fire callback, all keys processed
        return call_user_func_array($options['onComplete'], array());
    }
}

