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
   * @var array The list of actions to be evaluated later.
   */
  protected $actionProtocol = array();
  /**
   *
   * @var array
   */
  protected $timerStack = array();
  /**
   *
   * @var array
   */
  protected $timings = array();
  /**
   * @var float
   */
  protected $totalTimersDuration = 0.0;
  /**
   * @var ProfilerPrinterDelegate
   */
  protected $printerDelegate;

  /**
   * The profiler starts to profile as soon as it is instantiated.
   * @param ProfilerPrinterDelegate $printerDelegate
   */
  public function __construct($printerDelegate = null) {
    $this->startTime = microtime(true);
    $this->printerDelegate = $printerDelegate;
  }

  /**
   * Stores a start in protocol
   * 
   * @param string $context
   * @param string $section 
   */
  public function start($context, $section = null) {
    $this->actionProtocol[] = array('start', microtime(true), $context, $section);
  }

  /**
   * Stores a stop in protocol
   */
  public function stop() {
    $this->actionProtocol[] = array('stop', microtime(true));
  }

  /**
   * Starts a timer
   * @param string $context
   * @param string $section 
   */
  protected function startTimer($time, $context, $section) {
    if (count($this->timerStack) !== 0) {
      $lastTimer = end($this->timerStack);
      $lastTimer->pause($time);
    }
    $this->timerStack[] = new ProfilerTimer($context, $section, $time);
  }

  /**
   * Stops the last timer.
   */
  protected function stopTimer($time) {
    $lastTimer = array_pop($this->timerStack);
    $lastTimer->end($time);
    $this->totalTimersDuration += $lastTimer->getDuration();

    
    $context = $lastTimer->getContext();
    $section = $lastTimer->getSection();
    if ( ! array_key_exists($context, $this->timings)) $this->timings[$context] = array('duration' => 0.0, 'calls' => 0, 'sections'=>array());

    $this->timings[$context]['duration'] += $lastTimer->getDuration();
    $this->timings[$context]['calls'] ++;

    if ($section) {
      if ( ! array_key_exists($section, $this->timings[$context]['sections'])) $this->timings[$context]['sections'][$section] = array('duration' => 0.0, 'calls' => 0);
      $this->timings[$context]['sections'][$section]['duration'] += $lastTimer->getDuration();
      $this->timings[$context]['sections'][$section]['calls'] ++;
      ksort($this->timings[$context]['sections']);
    }

    if (count($this->timerStack) !== 0) {
      $previousTimer = end($this->timerStack);
      $previousTimer->resume($time);
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
    $this->evaluateProtocol();
  }

  /**
   * @return bool
   */
  public function didEnd() {
    return $this->endTime !== null;
  }

  /**
   * Goes through the protocol, and replays all the actions. This time for real.
   */
  protected function evaluateProtocol() {
    foreach ($this->actionProtocol as $action) {
      switch ($action[0]) {
        case 'start':
          $this->startTimer($action[1], $action[2], $action[3]);
          break;
        case 'stop':
          $this->stopTimer($action[1]);
          break;
      }
    }
    ksort($this->timings);
  }

  /**
   * @return array
   */
  public function getTimings() {
    return $this->timings;
  }

  /**
   * @return float
   */
  public function getTotalDuration() {
    return $this->endTime - $this->startTime;
  }

  /**
   * @return float
   */
  public function getTotalTimersDuration() {
    return $this->totalTimersDuration;
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
    if ( ! $this->printerDelegate) {
      require_class('DefaultHtmlProfilerPrinterDelegate', 'Profiler');
      $this->printerDelegate = new DefaultHtmlProfilerPrinterDelegate();
    }
    $this->printerDelegate->printProfiler($this);
  }

}
