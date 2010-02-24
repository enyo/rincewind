<?php

/**
 * Prints out the SNAP Usage Manual (and exposed options)
 * @return string
 **/
function SNAP_usage() {
    // get the available output modes in SNAPTEST_REPORTERS
    $handle = opendir(SNAPTEST_REPORTERS);
    $outmodes = array();
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
        
        $outmodes[] = str_replace('.php', '', $file);
    }
    unset($handle);
    
    $outmodes = "    * " . implode("\n    * ", $outmodes);
    
return <<<SNAPDOC

Usage: snaptest.sh [--out=outmode] [--php=phppath] [--help] <path>
Usage: php snaptest.php [--out=outmode] [--php=phppath] [--help] <path>

<path> :: The path to the test you want to run.

--out=outmode :: sets the output handler to 'outmode'. The
output mode must be located in <snaptest>/core/reporter/reporters/
The following are available:
$outmodes

--php=phppath :: set the php path for recursion. If not specified,
the call 'php' will be used, using whatever is in the current env
variable. If running snaptest.sh the shell will try and autodetect the
location for you.

--match=regex :: Specifies a PCRE regular expression to match. Files
that match this regular expression will be included by the test
harness. By default, SnapTest looks for the pattern ^.*\.stest\.php$

--help :: display this message

Additional information can always be found on the SnapTest home page:
http://www.snaptest.net

SNAPDOC;
}

/**
 * Recursively scans a directory, building an array of files
 * The array of files will match the pattern $xtn. Anything begining
 * with a dot (.) will be skipped
 * @param $path string a starting path, during recursion it's current path
 * @param $xtn string a regular expression to match for files
 * @return array
 **/
function SNAP_recurse_directory($path, $xtn) {
    if (!is_dir($path)) {
        return array($path);
    }
    
    $file_list = array();
    
    $handle = opendir($path);
    while (FALSE !== ($file = readdir($handle))) {
        if (substr($file, 0, 1) == '.') {
            continue;
        }

        if (substr($path, -1) == DIRECTORY_SEPARATOR) {
            $file = $path . $file;
        }
        else {
            $file = $path.DIRECTORY_SEPARATOR.$file;
        }
        
        // recursion on directory
        if (is_dir($file)) {
            $file_list = array_merge($file_list, SNAP_recurse_directory($file, $xtn));
            continue;
        }
        
        // is a file, check xtn
        if (!preg_match('#'.$xtn.'#', $file)) {
            continue;
        }
        
        // valid, add
        $file_list[] = $file;
    }
    
    return $file_list;
}




