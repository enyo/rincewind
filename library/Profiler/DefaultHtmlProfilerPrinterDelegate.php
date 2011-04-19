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
  }

}