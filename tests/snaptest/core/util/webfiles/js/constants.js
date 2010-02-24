YAHOO.namespace("SnapTest");
YAHOO.namespace("SnapTest.Constants");

(function() {

// define some var within a local scope
var loc = location.href;
loc = loc.replace(/#/, '');

var connect = (loc.match(/\?/)) ? '&' : '?';

loc = loc+connect;

YAHOO.SnapTest.Constants.FILE_LOADER = loc+"mode=getfiles";
YAHOO.SnapTest.Constants.TEST_LOADER = loc+"mode=loadtests";
YAHOO.SnapTest.Constants.TEST_RUNNER = loc+"mode=runtest";

YAHOO.SnapTest.Constants.TEST_CONTAINER = "test_container";
YAHOO.SnapTest.Constants.TEST_LIST = "test_list";
YAHOO.SnapTest.Constants.MESSAGE_CONTAINER = "app_status";
YAHOO.SnapTest.Constants.RUN_TESTS_BUTTON = "run_tests";
YAHOO.SnapTest.Constants.APP_CONTROLS = "app_controls";
YAHOO.SnapTest.Constants.NEXT_ERROR_BUTTON = "next_failure";
YAHOO.SnapTest.Constants.PREV_ERROR_BUTTON = "prev_failure";


})();