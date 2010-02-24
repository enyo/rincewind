<?php

// loadtests module. Scans a file, and adds all tests found to an array

// get the option
$options = Snap_Request::getLongOptions(array(
    'file'      => 'null',
));

if (!$options['file']) {
    echo '';
    exit;
}

$file = $options['file'];
$file_original = $options['file'];

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

$analyzer = new Snap_FileAnalyzer();
$results = $analyzer->analyzeFile($file);

$output = array();
foreach ($results as $klassname => $classes) {
    if (!is_array($classes)) {
        $out = array();
        $out['file'] = $file_original;
        $out['error'] = $classes;
        $output[] = $out;
        continue;
    }
    
    foreach ($classes as $klass => $test) {
        $out = array();
        $out['file'] = $file_original;
        $out['klass'] = $klassname;
        $out['test'] = $test;
        $output[] = $out;
    }
}

echo json_encode($output);

exit;

