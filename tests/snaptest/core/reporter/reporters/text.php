<?php
/**
 * Text Output Unit Test Reporter
 */
class Snap_Text_UnitTestReporter extends Snap_UnitTestReporter implements Snap_UnitTestReporterInterface {
    
    protected $has_errors = FALSE;
    
    public function generateHeader() {}
    
    public function announceTestCount($test_count) {}
    
    public function announceTestPass($report) {
        echo '.';
        $this->flush();
    }
    
    public function announceTestFail($report) {
        echo 'F';
        $this->flush();
    }
    
    public function announceTestDefect($report) {
        echo 'D';
        $this->flush();
    }
    
    public function announceTestTodo($report) {
        echo 'T';
        $this->flush();
    }
    
    public function announceTestSkip($report) {
        echo 'S';
        $this->flush();
    }
    
    public function announceTestCaseComplete($report) {}
    
    public function generateReport($reports) {
        $cases  = 0;
        $pass   = 0;
        $defect = 0;
        $fail   = 0;
        $error  = 0;
        $todo   = 0;
        $skip   = 0;
        
        $debugs = array();

        echo "\n";
        if (is_array($reports)) foreach ($reports as $report) {
            
            // passes
            if ($report['type'] == 'debug') {
                $debugs[] = $report['file'] . "\n" . $report['message'];
                continue;
            }
            elseif ($report['type'] == 'pass') {
                $pass++;
                continue;
            }
            elseif ($report['type'] == 'case') {
                $cases++;
                continue;
            }
            elseif ($report['type'] == 'defect') {
                $defect++;
            }
            elseif ($report['type'] == 'phperr') {
                $this->has_errors = TRUE;
            }
            elseif ($report['type'] == 'todo') {
                $todo++;
            }
            elseif ($report['type'] == 'skip') {
                $skip++;
            }
            else {
                $fail++;
            }
            
            $output  = (isset($report['message'])) ? $report['message'] : '[No Message Supplied]';
            $output .= "\n";

            if (!isset($report['skip_details'])) {
                $output .= '    in method: ';
                $output .= (isset($report['function'])) ? $report['function'] : 'unknown';
                $output .= "\n";
                    
                $output .= '    in class:  ';
                $output .= (isset($report['class'])) ? $report['class'] : 'unknown';
                $output .= "\n";
                    
                $output .= '    in file:   ';
                $output .= (isset($report['file'])) ? $report['file'] : 'unknown';
                $output .= (isset($report['line']) && strlen($report['line']) > 0) ? ' ('.$report['line'].')' : '';
                $output .= "\n";
            }
            
            echo $output;
            $this->flush();
        }
        
        $tests = $pass + $fail + $defect;
        
        if (count($debugs) > 0) {
            echo '______________________________________________________________________'."\n";
            echo "DEBUG:\n";
            echo implode("\n", $debugs);
        }
        
        echo '______________________________________________________________________'."\n";
        echo 'Total Cases:    '.$cases."\n";
        echo 'Total Tests:    '.$tests."\n";
        echo 'Total Pass:     '.$pass."\n";
        echo 'Total Defects:  '.$defect."\n";
        echo 'Total Failures: '.$fail."\n";
        echo 'Total Skips:    '.$skip."\n";
        echo 'Total Todo:     '.$todo."\n";
        
        $this->flush();
    }
    
    public function generateFooter() {
        if ($this->has_errors) {
            echo "\n".'You have unchecked errors in your tests.  These errors should be'."\n";
            echo 'removed, or acknowledged with $this->willError() in their respective'."\n";
            echo 'tests.'."\n";
        }
        
        $addons = unserialize(SNAP_ADDONS);
        if (count($addons) > 0) {
            echo "\nAddons Loaded:\n";
            foreach($addons as $addon) {
                echo '    '.$addon['name']."\n";
            }
        }
        
        $this->flush();
    }
    
    protected function flush() {
        if (!SNAP_CGI_MODE) {
            return;
        }
        
        @ob_flush();
        @flush();
    }
}
