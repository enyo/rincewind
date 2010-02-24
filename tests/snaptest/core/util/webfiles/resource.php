<?php

// resource.php
// manages the retrieval of rollup packages for resources

$packages = array(
    'js'    => array(
        SNAPTEST_WEBFILES . 'js' . DIRECTORY_SEPARATOR . 'yui' . DIRECTORY_SEPARATOR . 'utilities.js',
        SNAPTEST_WEBFILES . 'js' . DIRECTORY_SEPARATOR . 'yui' . DIRECTORY_SEPARATOR . 'container-min.js',
        SNAPTEST_WEBFILES . 'js' . DIRECTORY_SEPARATOR . 'yui' . DIRECTORY_SEPARATOR . 'json-min.js',
        SNAPTEST_WEBFILES . 'js' . DIRECTORY_SEPARATOR . 'yui' . DIRECTORY_SEPARATOR . 'logger-min.js',
        SNAPTEST_WEBFILES . 'js' . DIRECTORY_SEPARATOR . 'constants.js',
        SNAPTEST_WEBFILES . 'js' . DIRECTORY_SEPARATOR . 'displaymanager.js',
        SNAPTEST_WEBFILES . 'js' . DIRECTORY_SEPARATOR . 'fileloader.js',
        SNAPTEST_WEBFILES . 'js' . DIRECTORY_SEPARATOR . 'testloader.js',
        SNAPTEST_WEBFILES . 'js' . DIRECTORY_SEPARATOR . 'testrunner.js',
        SNAPTEST_WEBFILES . 'js' . DIRECTORY_SEPARATOR . 'snaptest.js',
    ),
    'css'   => array(
        SNAPTEST_WEBFILES . 'css' . DIRECTORY_SEPARATOR . 'yui' . DIRECTORY_SEPARATOR .'reset-fonts.css',
        SNAPTEST_WEBFILES . 'css' . DIRECTORY_SEPARATOR . 'yui' . DIRECTORY_SEPARATOR .'base-min.css',
        SNAPTEST_WEBFILES . 'css' . DIRECTORY_SEPARATOR . 'yui' . DIRECTORY_SEPARATOR .'container.css',
        SNAPTEST_WEBFILES . 'css' . DIRECTORY_SEPARATOR . 'yui' . DIRECTORY_SEPARATOR .'logger.css',
        SNAPTEST_WEBFILES . 'css' . DIRECTORY_SEPARATOR . 'snaptest.css',
    ),
    'css-ie6'           => array(SNAPTEST_WEBFILES . 'css' . DIRECTORY_SEPARATOR . 'snaptest-ie6.css'),
    'css-ie7'           => array(SNAPTEST_WEBFILES . 'css' . DIRECTORY_SEPARATOR . 'snaptest-ie7.css'),
    'corners.gif'       => array(SNAPTEST_WEBFILES . 'img' . DIRECTORY_SEPARATOR . 'corners.gif'),
    'corners-y.gif'     => array(SNAPTEST_WEBFILES . 'img' . DIRECTORY_SEPARATOR . 'corners-y.gif'),
    'corners-g.gif'     => array(SNAPTEST_WEBFILES . 'img' . DIRECTORY_SEPARATOR . 'corners-g.gif'),
    'corners-r.gif'     => array(SNAPTEST_WEBFILES . 'img' . DIRECTORY_SEPARATOR . 'corners-r.gif'),
    'corners-b.gif'     => array(SNAPTEST_WEBFILES . 'img' . DIRECTORY_SEPARATOR . 'corners-b.gif'),
    'edge.png'          => array(SNAPTEST_WEBFILES . 'img' . DIRECTORY_SEPARATOR . 'edge.png'),
    'sam-assets.png'    => array(SNAPTEST_WEBFILES . 'img' . DIRECTORY_SEPARATOR . 'sam-assets.png'),
);

$content_types = array(
    'js'                => 'text/javascript',
    'css'               => 'text/css',
    'css-ie6'           => 'text/css',
    'css-ie7'           => 'text/css',
    'corners.gif'       => 'image/gif',
    'corners-y.gif'     => 'image/gif',
    'corners-g.gif'     => 'image/gif',
    'corners-r.gif'     => 'image/gif',
    'corners-b.gif'     => 'image/gif',
    'edge.png'          => 'image/png',
    'sam-assets.png'    => 'image/png',
);

$replacements = array(
    'css'       => array(
        '{IMG}' => Snap_Request::makeURL(array(
            'mode'  => 'resource',
            'file'  => null,
        )),

        '../../../../assets/skins/sam/sprite.png' => Snap_Request::makeURL(array(
            'mode'  => 'resource',
            'file'  => 'sam-assets.png',
        )),
    ),
    'css-ie6'   => array(
        '{IMG}' => Snap_Request::makeURL(array(
            'mode'  => 'resource',
            'file'  => null,
        )),

        '../../../../assets/skins/sam/sprite.png' => Snap_Request::makeURL(array(
            'mode'  => 'resource',
            'file'  => 'sam-assets.png',
        )),
    ),
    'css-ie7'   => array(
        '{IMG}' => Snap_Request::makeURL(array(
            'mode'  => 'resource',
            'file'  => null,
        )),

        '../../../../assets/skins/sam/sprite.png' => Snap_Request::makeURL(array(
            'mode'  => 'resource',
            'file'  => 'sam-assets.png',
        )),
    ),
);

// get the option
$options = Snap_Request::getLongOptions(array(
    'file'      => 'null',
));

// no file
if (!$options['file']) {
    echo '';
    exit;
}

$file = $options['file'];

// no proper file
if (!isset($packages[$file])) {
    echo '';
    exit;
}

// send content type header
header('Content-type: '.(isset($content_types[$file]) ? $content_types[$file] : 'text/plain'));

// output every file in the package
$files = $packages[$file];
foreach ($files as $fname) {
    if (!isset($replacements[$file])) {
        readfile($fname);
    }
    else {
        echo str_replace(array_keys($replacements[$file]), array_values($replacements[$file]), file_get_contents($fname));
    }
}
