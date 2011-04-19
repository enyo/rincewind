<?php

/**
 * This file contains the DefaultHtmlProfilerPrinterDelegate class.
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Profiler
 */
/**
 * Including ProfilerPrinterDelegate
 */
require_interface('ProfilerPrinterDelegate', 'Profiler');

/**
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Profiler
 */
class DefaultHtmlProfilerPrinterDelegate implements ProfilerPrinterDelegate {

  /**
   * @param Profiler $profiler 
   */
  public function printProfiler($profiler) {
    echo '<style type="text/css">
      .rincewind-profiler-output {
        padding: 10px 15px;
        margin: 15px 0;
      }
      .rincewind-profiler-output table {
        font-family: courier, monospace;
        font-size: 12px;
        border-collapse: collapse;
        }
      .rincewind-profiler-output th {
        border-bottom: 1px solid gray;
        padding: 10px 6px;
        text-align: center;
        background: #f3f3f3;
      }
      .rincewind-profiler-output td {
        padding: 3px 6px;
        }
      .rincewind-profiler-output tr.context td {
        border: 1px solid silver;
        border-left: none;
        border-right: none;
        }
      .rincewind-profiler-output td.context {
        font-weight: bold;
        padding-right: 20px;
        }
      .rincewind-profiler-output .total {
        color: red;
      }
      .rincewind-profiler-output .total td {
        background: #f3f3f3;
        padding: 6px 6px;
      }
      .rincewind-profiler-output .duration, .rincewind-profiler-output .call-count {
        text-align: right;
        }
      .rincewind-profiler-output .context-duration {
        font-weight: bold;
        }
      .rincewind-profiler-output .unspecified .section {
        color: silver;
        }
    </style>';
    echo '<div class="rincewind-profiler-output"><table border="0">';
    echo '<tr class="labels"><th>Context</th><th>Section</th><th colspan="2">Duration (ms)</th><th>Callcount</th></tr>';
    foreach ($profiler->getTimings() as $contextName => $timing) {
      $this->printRow($contextName, null, $timing['duration'], null, $timing['calls'], 'context');
      if (count($timing['sections']) !== 0) {
        $remainingCalls = $timing['calls'];
        $remainingDuration = $timing['duration'];

        foreach ($timing['sections'] as $sectionName => $sectionTiming) {
          $this->printRow(null, $sectionName, null, $sectionTiming['duration'], $sectionTiming['calls']);
          $remainingCalls -= $sectionTiming['calls'];
          $remainingDuration -= $sectionTiming['duration'];
        }

        if ($remainingCalls || $remainingDuration) {
          $this->printRow(null, 'Unspecified', null, $remainingDuration, $remainingCalls, 'unspecified');
        }
      }
    }
    $this->printRow('Not profiled', null, $profiler->getTotalDuration() - $profiler->getTotalTimersDuration(), null, '');

    echo '<tr style="height: 10px;"></tr>';

    $this->printRow('TOTAL', null, $profiler->getTotalDuration(), null, '', 'total');

    echo '</table></div>';
  }

  protected function printRow($contextName, $sectionName, $contextDuration, $sectionDuration, $calls, $trClass = '') {
    echo '<tr class="' . $trClass . '">';
    echo '<td ' . ( ! $sectionName ? 'colspan="2"' : '') . ' class="context">' . $contextName . '</td>';
    if ($sectionName) echo '<td class="section">' . $sectionName . '</td>';
    echo '<td class="duration context-duration">' . $this->formatDuration($contextDuration) . '</td>';
    echo '<td class="duration section-duration">' . $this->formatDuration($sectionDuration) . '</td>';
    echo '<td class="call-count">' . $calls . '</td>';
    echo '</tr>';
  }

  protected function formatDuration($seconds) {
    if ($seconds === null) return '';
    return number_format(round($seconds * 1000, 4), 4);
  }

}