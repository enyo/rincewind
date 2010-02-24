<?php
/**
 * Text Output Unit Test Reporter
 */
class Snap_AnsiText_UnitTestReporter extends Snap_UnitTestReporter implements Snap_UnitTestReporterInterface {
    
    protected $has_errors = FALSE;

    protected $pass_color = "\x1b[0;32m\x1b[40m";
    
    protected $error_color = "\x1b[0;33m\x1b[40m";
    
    protected $fail_color = "\x1b[0;31m\x1b[40m";
    protected $fail_title = "\x1b[1;31m\x1b[40m";
    
    protected $defect_color = "\x1b[0;31m\x1b[40m";
    protected $defect_title = "\x1b[1;31m\x1b[40m";
    
    protected $todo_color = "\x1b[0;33m\x1b[40m";
    protected $todo_title = "\x1b[1;33m\x1b[40m";
    
    protected $skip_color = "\x1b[0;37m\x1b[40m";
    protected $skip_title = "\x1b[1;37m\x1b[40m";
    
    protected $default_color = "\x1b[0;37m\x1b[40m";
    
    public function generateHeader() {}
    
    public function announceTestCount($test_count) {}
    
    public function announceTestPass($report) {
        echo $this->pass_color.'.'.$this->default_color;
        $this->flush();
    }
    
    public function announceTestFail($report) {
        echo $this->fail_color.'F'.$this->default_color;
        $this->flush();
    }
    
    public function announceTestDefect($report) {
        echo $this->fail_color.'D'.$this->default_color;
        $this->flush();
    }
    
    public function announceTestTodo($report) {
        echo $this->todo_color.'T'.$this->default_color;
        $this->flush();
    }
    
    public function announceTestSkip($report) {
        echo $this->skip_color.'S'.$this->default_color;
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

        echo $this->default_color;
        echo "\n";
        if (is_array($reports)) foreach ($reports as $report) {
            
            $title_color = $this->default_color;
            $msg_color = $this->default_color;
            
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
                $title_color = $this->defect_title;
                $msg_color = $this->defect_color;
                $defect++;
            }
            elseif ($report['type'] == 'phperr') {
                $title_color = $this->fail_title;
                $msg_color = $this->fail_color;
                $this->has_errors = TRUE;
            }
            elseif ($report['type'] == 'todo') {
                $title_color = $this->todo_title;
                $msg_color = $this->todo_color;
                $todo++;
            }
            elseif ($report['type'] == 'skip') {
                $title_color = $this->skip_title;
                $msg_color = $this->skip_color;
                $skip++;
            }
            else {
                $title_color = $this->fail_title;
                $msg_color = $this->fail_color;
                $fail++;
            }
            
            $output  = $title_color;
            $output .= (isset($report['message'])) ? $report['message'] : '[No Message Supplied]';
            $output .= "\n";
            $output .= $msg_color;

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
            
            $output .= $this->default_color;
            
            echo $output;
            $this->flush();
        }
        
        $tests = $pass + $fail + $defect;
        
        if (count($debugs) > 0) {
            echo $this->default_color;
            echo '______________________________________________________________________'."\n";
            echo "DEBUG:\n";
            echo implode("\n", $debugs);
        }
        
        $pass   = ($pass > 0)   ? $this->pass_color . $pass . $this->default_color : $this->default_color . '0';
        $defect = ($defect > 0) ? $this->defect_color . $defect . $this->default_color : $this->default_color . '0';
        $fail   = ($fail > 0)   ? $this->fail_color . $fail . $this->default_color : $this->default_color . '0';
        $skip   = ($skip > 0)   ? $this->skip_color . $skip . $this->default_color : $this->default_color . '0';
        $todo   = ($todo > 0)   ? $this->todo_color . $todo . $this->default_color : $this->default_color . '0';
        
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
            echo $this->error_color;
            echo "\n".'You have unchecked errors in your tests.  These errors should be'."\n";
            echo 'removed, or acknowledged with $this->willError() in their respective'."\n";
            echo 'tests.'."\n";
            echo $this->default_color;
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
