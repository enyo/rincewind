<?php

// snaptest web core initialization

// requires PHP 5.2+
if (version_compare(phpversion(), '5.0.0') < 0) {
    echo "\n";
    echo "SnapTest requires a PHP version >= 5.0.0\n";
    exit;
}

define('SNAPTEST_CLI_INTERFACE', FALSE);

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'constants.php';

$options = Snap_Request::getLongOptions(array(
    'mode'      => 'index',
    'key'       => '',
));

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'util' . DIRECTORY_SEPARATOR . 'webfiles' . DIRECTORY_SEPARATOR . 'functions.php';

Snap_Request::setURLBase(SNAP_WI_URL_PATH);

switch ($options['mode']) {
    case 'index':
        include SNAPTEST_WEBFILES . 'index.php';
        exit;
    
    case 'resource':
        include SNAPTEST_WEBFILES . 'resource.php';
        exit;
    
    case 'getfiles':
        include SNAPTEST_WEBFILES . 'getfiles.php';
        exit;
    
    case 'loadtests':
        include SNAPTEST_WEBFILES . 'loadtests.php';
        exit;
    
    case 'runtest':
        include SNAPTEST_WEBFILES . 'runtest.php';
        exit;
    
    default:
        die('not supported');
}
