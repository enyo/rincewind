<?php

/**
 * Serialized Reports Output, used in aggregation
 */
class Snap_JSON_UnitTestReporter extends Snap_UnitTestReporter implements Snap_UnitTestReporterInterface {

    public function generateHeader() {}
    
    public function announceTestCount($test_count) {}
    
    public function announceTestPass($report) {}
    
    public function announceTestFail($report) {}
    
    public function announceTestDefect($report) {}

    public function announceTestTodo($report) {}
    
    public function announceTestSkip($report) {}
    
    public function announceTestCaseComplete($report) {}
    
    public function generateReport($reports) {
        echo json_encode($reports);
    }
    
    public function generateFooter() {}
}
