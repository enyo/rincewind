<?php

// requires PHP 5.2+
if (version_compare(phpversion(), '5.0.0') < 0) {
    echo "\n";
    echo "SnapTest requires a PHP version >= 5.0.0\n";
    exit;
}

define('SNAPTEST_CLI_INTERFACE', TRUE);

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'constants.php';

$options = Snap_Request::getLongOptions(array(
    0           => '',
    'out'       => 'text',
    'php'       => 'php',
    'match'     => '^.*\.stest\.php$',
    'help'      => FALSE,
    'test'      => '',
    'analyze'   => FALSE,
));

$path       = str_replace('\ ', ' ', $options[0]);
$out_mode   = $options['out'];
$php        = $options['php'];
$xtn        = $options['match'];
$help       = $options['help'];
$test       = $options['test'];
$analyze    = $options['analyze'];

// help output if no path is specified
if ((!$path || $help) && (!$test)) {
    echo SNAP_usage();
    exit;
}

// okay, there is some sort of path, get a realpath for it
$path = realpath($path);

// analyze subprocess
if ($analyze) {
    $analyzer = new Snap_FileAnalyzer();
    $results = $analyzer->analyzeFile($path);
    echo SNAPTEST_TOKEN_START;
    echo serialize($results);
    echo SNAPTEST_TOKEN_END;
    echo SNAP_STREAM_ENDING_TOKEN;
    exit;
}

// test subprocess
if ($test) {
    // new reporter in phpserializer mode
    $snap = new Snap_Tester('phpserializer');
    
    // unencode
    $test = Snap_Request::decodeTestKey($test);
    
    // include the file, so that all base components are there
    require_once($test['file']);
    
    // add the class now that it exists
    $snap->addInput('local', $test['class']);
    
    // run tests with an exact match on the test name
    $snap->runTests('^'.$test['method'].'$');
    echo SNAP_STREAM_ENDING_TOKEN;
    exit;
}

// generate list of files to test
if (is_dir($path)) {
    $file_list = SNAP_recurse_directory($path, $xtn);
}
else {
    $file_list = array($path);
}

// start a dispatcher for multi-processing
$dispatcher = new Snap_Dispatcher($php, __FILE__);

// build master test list
$analyzer = new Snap_FileAnalyzer();
$master_test_list = $dispatcher->dispatch(array(
    'keys'          => $file_list,
    'dispatch'      => array(
        'analyze'       => TRUE,
        1               => '$key',
        ),
    'onThreadComplete'  => array($analyzer, 'onThreadComplete'),
    'onThreadFail'      => array($analyzer, 'onThreadFail'),
    'onComplete'        => array($analyzer, 'onComplete'),
));
unset($analyzer);

// build a master test key list
$master_test_key_list = array();
foreach ($master_test_list as $file => $classes) {
    if (!is_array($classes)) {
        die ("File $file could not be read due to a fatal error:\n".$classes."\n");
    }
    foreach ($classes as $klass => $tests) {
        foreach ($tests as $test) {
            $master_test_key_list[] = Snap_Request::makeTestKey($file, $klass, $test);
        }
    }
}
unset($master_test_list);

// create a test aggregator for $outmode
$reporter = new Snap_TestAggregator($out_mode, count($master_test_key_list));

// dispatch the tests
$dispatcher->dispatch(array(
    'keys'          => $master_test_key_list,
    'dispatch'      => array(
        'test'          => '$key',
        ),
    'onThreadComplete'  => array($reporter, 'onThreadComplete'),
    'onThreadFail'      => array($reporter, 'onThreadFail'),
    'onComplete'        => array($reporter, 'onComplete'),
));

exit;


