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
  protected $totalStartTime;
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
   * The profiler starts to profile as soon as it is instanciated.
   */
  public function __construct() {
    $this->totalStartTime = microtime(true);
  }

  public function start($context, $section) {
    if (count($this->timerStack) !== 0) {
      $lastTimer = end($this->timerStack);
      $lastTimer->pause();
    }
    $this->timerStack[] = new ProfilerTimer();
  }

  public function stop() {
    $lastTimer = end($this->timerStack);
    $lastTimer;
  }

}