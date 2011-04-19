<?php

/**
 * This file contains the Profiler definition.
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Profiler
 */
/**
 * Including the static class Profile
 */
require_class('Profile', 'Profiler');

/**
 * Including the Timer
 */
require_class('ProfilerTimer', 'Profiler');

/**
 * Normally you don't use an instance of Profiler to profile your app, but you set a Profiler instance
 * to the static Profile class.
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Profiler
 */
class Profiler {

  /**
   * @var float
   */
  protected $startTime;
  /**
   * @var float
   */
  protected $endTime;
  /**
   *
   * @var array
   */
  protected $timerStack = array();
  /**
   *
   * @var array
   */
  protected $times = array();
  /**
   * @var float
   */
  protected $totalTimersDuration = 0.0;
  /**
   * @var ProfilerPrinterDelegate
   */
  protected $printerDelegate;

  /**
   * The profiler starts to profile as soon as it is instanciated.
   * @param ProfilerPrinterDelegate $printerDelegate
   */
  public function __construct($printerDelegate = null) {
    $this->startTime = microtime(true);
    if ( ! $printerDelegate) {
      require_class('DefaultHtmlProfilerPrinterDelegate', 'Profiler');
      $printerDelegate = new DefaultHtmlProfilerPrinterDelegate();
    }
    $this->printerDelegate = $printerDelegate;
  }

  /**
   * Starts a timer
   * @param string $context
   * @param string $section 
   */
  public function start($context, $section) {
    if (count($this->timerStack) !== 0) {
      $lastTimer = end($this->timerStack);
      $lastTimer->pause();
    }
    $this->timerStack[] = new ProfilerTimer();
  }

  /**
   * Stops the last timer.
   */
  public function stop() {
    $lastTimer = array_pop($this->timerStack);
    $lastTimer->end();
    $this->totalTimersDuration += $lastTimer->getDuration();
    if (count($this->timerStack) !== 0) {
      $previousTimer = end($this->timerStack);
      $previousTimer->resume();
    }
  }

  /**
   * Ends the profiling process.
   */
  public function end() {
    for ($i = 0; $i < count($this->timerStack); $i ++ ) {
      $this->stop();
    }
    $this->endTime = microtime(true);
  }

  /**
   * @return bool
   */
  public function didEnd() {
    return $this->endTime !== null;
  }

  /**
   * @return ProfilePrinter
   */
  public function getPrinterDelegate() {
    return $this->printerDelegate;
  }

  /**
   * @return ProfilePrinter
   */
  public function setPrinterDelegate($printerDelegate) {
    $this->printerDelegate = $printerDelegate;
  }

  /**
   * Uses the printer delegate to print the results of the Profiler.
   */
  public function printResult() {
    if ( ! $this->didEnd()) $this->end();
    $this->printerDelegate->printProfiler($this);
  }

}
