<?php

/**
 * Serialized Reports Output, used in aggregation
 */
class Snap_PHPSerializer_UnitTestReporter extends Snap_UnitTestReporter implements Snap_UnitTestReporterInterface {

    public function generateHeader() {}
    
    public function announceTestCount($test_count) {}
    
    public function announceTestPass($report) {}
    
    public function announceTestFail($report) {}
    
    public function announceTestDefect($report) {}

    public function announceTestTodo($report) {}
    
    public function announceTestSkip($report) {}
    
    public function announceTestCaseComplete($report) {}
    
    public function generateReport($reports) {
        echo SNAPTEST_TOKEN_START . serialize($reports) . SNAPTEST_TOKEN_END;
    }
    
    public function generateFooter() {}
}
