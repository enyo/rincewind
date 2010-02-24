<?php

// snaptest index file
// creates the layout, includes the necessary components, etc

$urls = array(
    'css'           => Snap_Request::makeURL(array(
        'mode'  => 'resource',
        'file'  => 'css',
        )),
    'css-ie6'       => Snap_Request::makeURL(array(
        'mode'  => 'resource',
        'file'  => 'css-ie6',
        )),
    'css-ie7'       => Snap_Request::makeURL(array(
        'mode'  => 'resource',
        'file'  => 'css-ie7',
        )),
    'js'            => Snap_Request::makeURL(array(
        'mode'  => 'resource',
        'file'  => 'js',
        )),
);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Content-Language" content="en-us" />    
    <title>SnapTest Web Testing Console</title>

    <link rel="stylesheet" type="text/css" href="<?php echo $urls['css'];?>">
    
    <!--[if IE 6]>
        <link rel="stylesheet" type="text/css" href="<?php echo $urls['css-ie6'];?>">
    <![endif]-->
    <!--[if IE 7]>
        <link rel="stylesheet" type="text/css" href="<?php echo $urls['css-ie7'];?>">
    <![endif]-->
    
    <script type="text/javascript" src="<?php echo $urls['js'];?>"></script> 
</head>
<body id="snaptest" class="yui-skin-sam">
    <h1 id="title">SnapTest Web Console<abbr id="help" title="help">?</abbr></h1>
    <div id="help_contents">
        <p>The SnapTest Web Console provides an alternative to the standard command line interface
            used for running SnapTest. If you're reading this, then you definitely have things up
            and running. Check the tests you would like to run, and then click "run" at the bottom
            right corner of the page. <strong class="pass">Passed tests</strong>,
            <strong class="fail">failed tests</strong>, and <strong class="warning">test cases with
            both passes and fails</strong> are marked in the colors indicated. Tests you choose to
            not run will be hidden from view. When all tests have completed, use the "Previous"
            and "Next" button to step through any failed tests.
        </p>
    </div>
    <dl id="testing_parameters">
        <dt>Test Path:</dt>
        <dd><?php 
            if (SNAP_WI_CRYPT) {
                echo "??? <strong>(Full path obfuscation is on)</strong>";
            }
            else {
                echo SNAP_WI_TEST_PATH;
            }
        ?></dd>
        
        <dt>Test Match:</dt>
        <dd><?php echo SNAP_WI_TEST_MATCH ?></dd>
    </dl>
    <div id="expand_collapse_all">
        <a href="#" id="collapse_all">Collapse All</a>
        <a href="#" id="expand_all">Expand All</a>
    </div>
    <div id="test_container"></div>
    <div id="footer_spacer" class="clear"></div>
    <div id="footer_container">
        <div id="footer">
            <h1>SnapTest Web Testing Console</h1>
            <p id="app_status">Loading...</p>
            <div id="app_controls">
                <button id="run_tests" class="run_tests">Run</button>
                <button id="prev_failure" class="review_results">Prev</button>
                <button id="next_failure" class="review_results">Next</button>
            </div>
        </div>
    </div>
</body>
</html>