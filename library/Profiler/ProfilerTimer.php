<?php

/**
 * This file contains the ProfilerTimer definition, which is the actual class that
 * times parts of the program.
 *
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Profiler
 */

/**
 * The actual timer that gets instantiated and stored every time the profiler gets invoked.
 * @author Matthias Loitsch <matthias@loitsch.com>
 * @copyright Copyright (c) 2010, Matthias Loitsch
 * @package Profiler
 */
class ProfilerTimer {

  /**
   * @var float
   */
  private $startTime;
  /**
   * @var float
   */
  private $endTime;
  /**
   * @var float
   */
  private $duration;
  /**
   * @var float
   */
  private $pauseStartTime;
  /**
   * @var float
   */
  private $totalPauseDuration = 0.0;

  /**
   * Sets the startTime
   */
  public function __construct() {
    $this->startTime = microtime(true);
  }

  /**
   * Sets the endtime, and calculates the duration
   */
  public function end() {
    $this->resume();
    $this->endTime = microtime(true);
    $this->duration = $this->endTime - $this->startTime;
  }

  /**
   * @return float
   */
  public function getStartTime() {
    return $this->startTime;
  }

  /**
   * @return float
   */
  public function getEndTime() {
    return $this->endTime;
  }

  /**
   * @return float
   */
  public function getDuration() {
    if ($this->duration === null) return null;
    return $this->duration - $this->totalPauseDuration;
  }

  /**
   * @return float
   */
  public function getPauseStartTime() {
    return $this->pauseStartTime;
  }

  /**
   * @return float
   */
  public function getTotalPauseDuration() {
    return $this->totalPauseDuration;
  }

  /**
   * Whether the timer is paused or not.
   * @return bool
   */
  public function isPaused() {
    return $this->pauseStartTime !== null;
  }

  /**
   * Pauses the timer
   */
  public function pause() {
    if ( ! $this->isPaused()) $this->pauseStartTime = microtime(true);
  }

  /**
   * Resumes the timer
   */
  public function resume() {
    if ($this->isPaused()) {
      $this->totalPauseDuration += microtime(true) - $this->pauseStartTime;
      $this->pauseStartTime = null;
    }
  }

}