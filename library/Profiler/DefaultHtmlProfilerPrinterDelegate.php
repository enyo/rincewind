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
 * Very simple, very ugly ProfilerPrinter
 * 
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Profiler
 */
class DefaultHtmlProfilerPrinterDelegate implements ProfilerPrinterDelegate {
  const CONTEXT = 1;
  const SECTION = 2;

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
        border: 1px solid gray;
        }
      .rincewind-profiler-output tr:nth-child(odd) td {
        background: #f6f6f6;
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
      .rincewind-profiler-output tr.context.expensive td {
        color: #aa0000;
      }
      .rincewind-profiler-output td.context {
        font-weight: bold;
        padding-right: 20px;
        }
      .rincewind-profiler-output .total {
        color: red;
      }
      .rincewind-profiler-output .total td {
        border-top: 1px solid gray;
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
    echo '<tr class="labels"><th>Context</th><th>Section</th><th colspan="4">Duration (ms)</th><th>Callcount</th></tr>';

    foreach ($profiler->getTimings() as $contextName => $timing) {
      $this->printRow(self::CONTEXT, $contextName, $timing['duration'], $timing['duration'] / $profiler->getTotalDuration(), $timing['calls'], 'context ' . ($timing['duration'] >= $profiler->getTotalDuration() / 3 ? ' expensive' : ''));
      if (count($timing['sections']) !== 0) {
        $remainingCalls = $timing['calls'];
        $remainingDuration = $timing['duration'];

        foreach ($timing['sections'] as $sectionName => $sectionTiming) {
          $this->printRow(self::SECTION, $sectionName, $sectionTiming['duration'], $sectionTiming['duration'] / $timing['duration'], $sectionTiming['calls']);
          $remainingCalls -= $sectionTiming['calls'];
          $remainingDuration -= $sectionTiming['duration'];
        }

        if ($remainingCalls || $remainingDuration) {
          $this->printRow(self::SECTION, 'Unspecified', $remainingDuration, $remainingDuration / $timing['duration'], $remainingCalls, 'unspecified');
        }
      }
    }
    $notProfiledDuration = $profiler->getTotalDuration() - $profiler->getTotalTimersDuration();
    $this->printRow(self::CONTEXT, 'Not profiled', $notProfiledDuration, $notProfiledDuration / $profiler->getTotalDuration(), '');

    $this->printRow(self::CONTEXT, 'TOTAL', $profiler->getTotalDuration(), 1, '', 'total');

    echo '</table></div>';
  }

  protected function printRow($type, $name, $duration, $percentage, $calls, $trClass = '') {
    echo '<tr class="' . $trClass . '">';
    if ($type === self::CONTEXT) {
      echo '<td colspan="2" class="context">' . $name . '</td>';
    }
    else {
      echo '<td class="context"></td>';
      echo '<td class="section">' . $name . '</td>';
    }

    $formattedDuration = $this->formatDuration($duration);
    $formattedPercentage = $this->formatPercentage($percentage);
    
    $contextDuration = $type === self::CONTEXT ? $formattedDuration : '';
    $sectionDuration = $type === self::SECTION ? $formattedDuration : '';
    $contextPercentage = $type === self::CONTEXT ? $formattedPercentage : '';
    $sectionPercentage = $type === self::SECTION ? $formattedPercentage : '';


    echo '<td class="duration context-duration">' . $contextDuration . '</td>';
    echo '<td class="percentage context-percentage">' . $contextPercentage . '</td>';
    echo '<td class="duration section-duration">' . $sectionDuration . '</td>';
    echo '<td class="percentage section-percentage">' . $sectionPercentage . '</td>';
    echo '<td class="call-count">' . $calls . '</td>';
    echo '</tr>';
  }

  protected function formatDuration($seconds) {
    return number_format(round($seconds * 1000, 4), 4);
  }

  protected function formatPercentage($percentage) {
    return '(' . number_format(round($percentage * 100, 2), 2) . '%)';
  }

}