<?php

// runtest interface

// get the option
$options = Snap_Request::getLongOptions(array(
    'file'      => 'null',
    'test'      => 'null',
    'klass'     => 'null',
));

if (!$options['file']) {
    echo '';
    exit;
}

$file = $options['file'];
$test = $options['test'];
$klass = $options['klass'];

// decrypt if required
if (SNAP_WI_CRYPT) {
    $file = snap_decrypt($file, SNAP_WI_CRYPT);
}

// ensure file path matches test path prefix
$file = str_replace(array('..', '//'), array('.', '/'), $file);

if (!is_file($file)) {
    echo '';
    exit;
}

if (strpos($file, SNAP_WI_TEST_PATH) !== 0 || !preg_match('#'.SNAP_WI_TEST_MATCH.'#', $file)) {
    echo '';
    exit;
}

// file is safe, check test and klass
if (!$test || !$klass) {
    echo '';
    exit;
}

// now, test
// new reporter in phpserializer mode
$snap = new Snap_Tester('json');

// include the file, so that all base components are there
require_once($file);

// add the class now that it exists
$snap->addInput('local', $klass);

// run tests with an exact match on the test name
$snap->runTests('^'.$test.'$');

exit;
