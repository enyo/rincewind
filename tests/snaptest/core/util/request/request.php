<?php

/**
 * Static helper class which contains useful functions for working with requests
 **/
class Snap_Request {
    
    static $base;
    
    /**
     * Unmangles a request from the shell script when PHP is in CGI Mode
     * Under CGI mode, any dots (.) are replaced with the constant
     * SNAP_MANGLE_STRING so that they are not escaped by the PHP
     * interpreter. This returns their original state.
     * @param $var the variable to unmangle
     * @return string
     **/
    public static function unmangleRequest($var) {
        return str_replace(SNAP_MANGLE_STRING, '.', $var);
    }
    
    /**
     * Mangles a string if needed (in CGI mode)
     * @param $var the string to magle
     * @return string
     **/
    public static function mangleRequest($var) {
        $var = str_replace(' ', '\ ', $var);
        
        if (SNAP_CGI_MODE) {
            return str_replace('.', SNAP_MANGLE_STRING, $var);
        }
        else {
            return $var;
        }
    }
    
    /**
     * Makes a test key for sub-processing
     * Test keys are used to collapse file, class, and method into a single
     * paramter for passing as a command line argument. This data needs to
     * be insulated from arbitrary characters and URL safe.
     * The array is serialized, and then base 64 encoded.
     * @param $file the name of the file to test
     * @param $class the name of the class to test
     * @param $method the name of the method to test
     * @return string
     **/
    public static function makeTestKey($file, $class, $method) {
        return base64_encode(serialize(array(
            'file' => $file,
            'class' => $class,
            'method' => $method,
        )));
    }
    
    /**
     * Decodes a key made with makeTestKey
     * @param $key the key to decode
     * @return array
     * @see Snap_Request::makeTestKey()
     **/
    public static function decodeTestKey($key) {
        return unserialize(base64_decode(trim($key)));
    }
    
    /**
     * Makes long options for a command line from an array
     * Converts an array into a string of long options. Any key/value
     * pairs are turned into --foo=bar pairings, and any numerical keys
     * become command line arguments added to the end.
     * @param $opts the array of options to convert
     * @return string
     **/
    public static function makeLongOptions($opts) {
        $opt_pieces = array();
        $opt_tail = array();
        foreach ($opts as $key => $value) {
            if (is_numeric($key)) {
                $opt_tail[$key] = Snap_Request::mangleRequest($value);
            }
            else {
                if (is_bool($value)) {
                    $opt_pieces[] = '--'. Snap_Request::mangleRequest($key);
                }
                else {
                    $opt_pieces[] = '--' . Snap_Request::mangleRequest($key) . '=' . Snap_Request::mangleRequest($value);
                }
            }
        }
    
        ksort($opt_tail);
    
        if (count($opt_pieces) && count($opt_tail)) {
            $separator = ' ';
        }
        else {
            $separator = '';
        }
    
        return implode(' ', $opt_pieces) . $separator . implode(' ', $opt_tail);
    }
    
    /**
     * Decodes a set of options from any number of sources
     * Options can come in from the command line or via the request global.
     * This function find where they are located and pulls them in. When
     * variables can't be found, the defaults in $request are used.
     * @param $request the default variables to pull in
     * @return array
     **/
    public function getLongOptions($request) {
        $sequentials = array();
        $arguments = array();
    
        // get our arguments off of $_REQUEST in CGI mode
        if (SNAP_CGI_MODE) {
            foreach ($_REQUEST as $key => $value) {
                if ($value === "") {
                    // a value of "" means it had no =, sequential
                    $sequentials[] = Snap_Request::unmangleRequest($key);
                }
                else {
                    // standard key/value pair
                    $key = Snap_Request::unmangleRequest($key);
                    $key = preg_replace('/^--(.*)/', '$1', $key);
                    $arguments[$key] = Snap_Request::unmangleRequest($value);
                }
            }
        }
        else {
            // CLI mode
            global $argv;
            if (!is_array($argv)) {
                break;
            }
        
            $prev = '';
            foreach ($argv as $idx => $arg) {
                if ($idx == 0) {
                    continue;
                }
                
                // if there is a \ as last char, link to $prev and go again
                if (strrpos($arg, '\\') + 1 === strlen($arg)) {
                    $prev = $arg.' ';
                    continue;
                }
                
                $arg = $prev.$arg;
                $prev = '';
            
                if (strpos($arg, '--') === FALSE) {
                    $sequentials[] = $arg;
                    continue;
                }
            
                $opt_pair = explode('=', substr($arg, 2), 2);
                $opt_name = $opt_pair[0];
                $opt_value = (isset($opt_pair[1])) ? $opt_pair[1] : TRUE;
                $arguments[$opt_name] = trim($opt_value, '"\'');
            }
        }

        foreach ($sequentials as $idx => $arg) {
            $arguments[$idx] = $arg;
        }
    
        // now, satisfy out output
        foreach ($request as $key => $default) {
            if (isset($arguments[$key]) && $arguments[$key]) {
                $request[$key] = $arguments[$key];
                continue;
            }
            if (isset($arguments[$key]) && is_bool($default)) {
                $request[$key] = TRUE;
                continue;
            }
        }
    
        return $request;
    }
    
    /**
     * Sets the URL Base
     * @param string $base the base URL
     **/
    public function setURLBase($base) {
        self::$base = $base;
    }
    
    /**
     * Make a URL used in the web interface
     * This takes the web interface URL constructs and creates a proper URL for
     * the key/value pairs
     * @param array $options the key/value pairs to attach
     * @return string a fully qualified URL
     **/
    public function makeURL($options) {
        $out = array();
        foreach ($options as $key => $value) {
            $out[] = $key.'='.$value;
        }
        return self::$base . '?' . implode('&', $out);
    }
}
